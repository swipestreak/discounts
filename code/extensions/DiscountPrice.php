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



    public static function get_extra_config($class, $extension, $args) {
        $config = parent::get_extra_config($class, $extension, $args) ?: array();

        $fieldSpecs = static::field_specs();

        $config = array_merge(
            $config,
            array(
                'db' => $fieldSpecs
            )
        );
        return $config;
    }

    public static function calc_discounted_amount(Price $amount, $discount) {
        $new = new Money();
        $new->setCurrency('NZD');
        $new->setAmount(
            Zend_Locale_Math::round(
                Zend_Locale_Math::Sub($amount->getAmount(), $discount),
                2
            )
        );
        return $new;

    }

    /**
     * @param Price $amount
     * @param       $discountAmount
     * @return bool
     */
    public function alterAmount(Price $amount, $discountAmount, Price $originalAmount) {
        if (!$discountAmount) {
            return false;
        }
        $discounted = self::calc_discounted_amount($originalAmount, $discountAmount);

        $res = Zend_Locale_Math::Comp($amount->getAmount(), $discounted->getAmount());

        if ($res) {

            $amount->setValue($discounted->getAmount());
            return true;
        }
        return false;
    }

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

    public static function field_specs() {
        $fields = array(
            StreakDiscountTypePriceExtension::CurrencyColumnSuffix => self::CurrencyColumnSchema,
            StreakDiscountTypePriceExtension::ColumnSuffix => self::AmountColumnSchema
        );
        $discountCodes = array_unique(StreakDiscountTypePriceExtension::discount_codes(true));

        $fieldsSpecs = array();

        foreach ($discountCodes as $fieldName) {
            if ($fieldName) {
                foreach ($fields as $specName => $schema) {
                    $fieldsSpecs[$fieldName . $specName] = $schema;
                }
            }
        }
        return $fieldsSpecs;
    }

    public function discountCodes($discountOnly = true) {
        return StreakDiscountTypePriceExtension::discount_codes($discountOnly);
    }

    public function defaultColumnName() {
        return StreakDiscountTypePriceExtension::DefaultColumnName;
    }

    public function provideDiscountOptions(array &$discountOptions) {
        foreach (StreakDiscountTypePriceExtension::discount_types() as $discountType) {
            $label = $discountType->Title . '( $' . $discountType->Metric . ' )';

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
            'DiscountedPrice' => array(
                'title' => 'Discounted Price',
                'callback' => function ($record, $col) {
                    return new TextField(
                        'DiscountedPrice',
                        '',
                        $record->discountedPrice($record->Price)

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
