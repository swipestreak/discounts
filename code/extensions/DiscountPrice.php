<?php

/**
 * Extension for Products and Variations to add a discount amount instead the price
 */
class StreakDiscountPriceExtension extends GridSheetModelExtension
{
    const ColumnFieldName = 'Code';
    const CurrencyColumnSchema = 'Varchar(3)';
    const AmountColumnSchema = 'Decimal(19,4)';
    const ModelClass = 'Product';
    const RelatedModelClass = 'Product';

    /**
     * Return extra fields added for discounts as part of db array. If current controller is
     * database admin then returns also fields to add from newly defined discount types.
     *
     * @param $class
     * @param $extension
     * @param $args
     * @return array
     */
    public static function get_extra_config($class, $extension, $args) {
        $config = parent::get_extra_config($class, $extension, $args) ?: array();

        $existingFieldsOnly = !Controller::curr() instanceof DatabaseAdmin;

        $fieldSpecs = static::field_specs($existingFieldsOnly);

        $config = array_merge(
            $config,
            array(
                'db' => $fieldSpecs
            )
        );
        return $config;
    }

    /**
     * Returns the least of the passed in price and the price with this discount applied, or null
     * if this discounted price is larger than the existing one. This can then be used in extension
     * call to find the least discounted price.
     *
     * @param Price $amount
     * @param  number $discountAmount
     * @return bool
     */
    public function provideLeastAmount(Price $amount, $discountAmount) {
        if (!$discountAmount) {
            return false;
        }
        $discounted = self::calc_discounted_amount($amount, $discountAmount);

        $res = Zend_Locale_Math::Comp($amount->getAmount(), $discounted->getAmount());

        if ($res) {
            return $discounted;
        }
        return null;
    }

    /**
     * Add fields for this discount to CMS form.
     *
     * @param FieldList $fields
     */
    public function updateProductCMSFields(FieldList $fields) {
        array_map(
            function ($fieldName) use (&$fields) {
                if ($fieldName) {
                    $fields->insertAfter(
                        new NumericField($fieldName, $this->fieldLabel($fieldName)),
                        'Price'
                    );
                }
            },
            array_keys(static::field_specs())
        );
    }

    /**
     * Return the names and datatypes of columns for this discount.
     *
     * @param bool $appliedOnly true to return only columns added to the extended model, false to return all columns
     *                          including ones that would be added next build
     * @return array
     */
    public static function field_specs($appliedOnly = true) {
        $fields = array(
            StreakDiscountTypePriceExtension::CurrencyColumnSuffix => self::CurrencyColumnSchema,
            StreakDiscountTypePriceExtension::ColumnSuffix => self::AmountColumnSchema
        );
        $discountCodes = array_unique(StreakDiscountTypePriceExtension::discount_codes(true));

        $fieldSpecs = array();

        foreach ($discountCodes as $fieldName) {
            if ($fieldName) {
                foreach ($fields as $specName => $schema) {
                    $fieldSpecs[$fieldName . $specName] = $schema;
                }
            }
        }
        $fieldList = DB::fieldList(static::ModelClass);
        if ($appliedOnly && $fieldSpecs) {
            $fieldSpecs = array_intersect_key(
                $fieldSpecs,
                $fieldList
            );
        }
        return $fieldSpecs;
    }

    public function defaultColumnName() {
        return StreakDiscountTypePriceExtension::DefaultColumnName;
    }

    /**
     * Adds an entry to the discountOptions map for this discount with [ID => Label] suitable
     * for use in a dropdown field.
     *
     * @param array $discountOptions
     */
    public function provideDiscountOptions(array &$discountOptions) {
        foreach (StreakDiscountTypePriceExtension::discount_types() as $discountType) {
            $label = $discountType->Title . '( $' . $discountType->Measure . ' )';

            $discountOptions += array($discountType->ID => $label);
        }
    }

    /**
     * For each field on DiscountType which is a 'Discount Price' field, adds a fieldSpec to be
     * included in editable grid field.
     *
     * @param array $fieldSpecs
     * @return bool true if provided columns, false otherwise
     */
    public function provideEditableColumns(array &$fieldSpecs) {
        $discountOptions = array();
        $this->owner->extend('provideDiscountOptions', $discountOptions);

        $fieldSpecs += array(
            'StreakDiscountTypeID' => array(
                'title' => 'Discount Type',
                'callback' => function($record, $col) use ($discountOptions) {
                    return Select2Field::create(
                        'StreakDiscountTypeID',
                        '',
                        $discountOptions
                    )->setEmptyString('No discount');
                }
            ),
            'DiscountByPrice' => array(
                'title' => 'Sale Price ($)',
                'callback' => function ($record, $col) {
                    /** @var StreakDiscountType $discountType */
                    $discountPrice = $record->Price;
                    if (($discountType = $record->StreakDiscountType()) && $discountType->exists()) {
                        if ($discountedPrice = $discountType->discountedAmount($record->Price)) {
                            $discountPrice = $discountedPrice->getAmount();
                        }
                    }
                    return new ReadonlyField(
                        'DiscountByPrice',
                        '',
                        $discountPrice
                    );
                }
            )
        );
    }

    /**
     * Called when a grid sheet is displaying a model related to another model. e.g. as a grid for a models ItemEditForm
     * in ModelAdmin.
     *
     * @param $relatedModelClass
     * @param array $relatedID
     * @param array $fieldSpecs
     * @return mixed
     */
    public function provideRelatedEditableColumns($relatedModelClass, $relatedID, array &$fieldSpecs) {

    }


    /**
     * Called for each new row in a grid when it is saved.
     *
     * @param $record
     * @return bool
     */
    public function gridSheetHandleNewRow(array &$record) {
        $updateData = $this->getUpdateColumns($this->owner->class, $record);
        $this->owner->update($updateData);
    }

    /**
     * Called to each existing row in a grid when it is saved.
     *
     * @param $record
     * @return bool
     */
    public function gridSheetHandleExistingRow(array &$record) {
        $updateData = $this->getUpdateColumns($this->owner->class, $record);
        $this->owner->update($updateData);
    }

}
