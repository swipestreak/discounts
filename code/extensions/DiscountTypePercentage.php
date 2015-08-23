<?php

/**
 * @property Measure
 */
class StreakDiscountTypePercentageExtension extends StreakDiscountTypeExtension
    implements StreakDiscountTypeInterface
{
    const ColumnFieldName = 'UsePercentageColumn';
    const ColumnSuffix = 'Percentage';
    const Symbol = '%';
    const OneHundred = 100.0;

    private static $db = array(
        self::ColumnFieldName => self::ColumnFieldSchema
    );

    /**
     * If no column names set for DiscountPercentage then make values from the Code.
     */
    public function onBeforeWrite() {
        $option = $this()->StreakDiscountOptions;

        if ($option) {
            // option set so from CMS form field post
            $optionFieldName = StreakDiscountTypePercentageExtension::ColumnFieldName . 'Option';

            if ($option == $optionFieldName) {
                if (!$this()->{StreakDiscountTypePercentageExtension::ColumnFieldName}) {
                    $fieldName = $this()->Code . self::ColumnSuffix;
                    $this()->{StreakDiscountTypePercentageExtension::ColumnFieldName} = $fieldName;
                }
            } else {
                // was from CMS form post but option was different so clear this one
                $this()->{StreakDiscountTypePercentageExtension::ColumnFieldName} = null;
            }
        }
    }

    /**
     * Returns the amount minus percentage from Measure.
     *
     * @param Price $forAmount
     * @return Price
     */
    public function discountedAmount($forAmount) {
        if ($this->owner->UsePercentageColumn) {
            $price = new Price();
            if ($forAmount instanceof Money) {
                $price->setAmount($forAmount->getAmount());
                $price->setCurrency($forAmount->getCurrency());
            } else {
                $price->setAmount($forAmount);
                $price->setCurrency(ShopConfig::current_shop_config()->BaseCurrency);
            }
            // only recalculate if there is a percentage
            if ($this->owner->Measure != 0) {
                $original = $price->getAmount();

                $percentage = Zend_Locale_Math::Div($this->owner->Measure, self::OneHundred, 10);

                $difference = Zend_Locale_Math::Mul(
                    $original,
                    $percentage,
                    10
                );
                $price->setAmount(
                    Zend_Locale_Math::Sub(
                        $original,
                        $difference,
                        10
                    )
                );
            }
            return $price;
        }
        return null;
    }

    public function provideEditableColumns(array &$fieldSpecs) {

        $options = array();
        $value = null;

        $this->provideDiscountTypeOptions($options, $value);

        $fieldSpecs += array(
            'Code' => array(
                'title' => 'Short Code',
                'callback' => function($record) {
                    return new TextField(
                        'Code',
                        ''
                    );
                }
            ),
            'Title' => array(
                'title' => 'Title',
                'callback' => function($record) {
                    return new TextField(
                        'Title',
                        ''
                    );
                }
            ),
            'UsePercentageColumn' => array(
                'title' => 'Percentage',
                'callback' => function($record) {
                    return new CheckboxField(
                        'UsePercentageColumn',
                        ''
                    );
                }
            ),
            'Measure' => array(
                'title' => 'Value (% or $ amount)',
                'callback' => function($record, $col) {
                    return new NumericField($col, null, $record->$col);
                }
            ),
            'ID' => array(
                'callback' => function($record) {
                    return new HiddenField(
                        'ID'
                    );
                }
            )
        );
        return true;
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
        // TODO: Implement provideRelatedEditableColumns() method.
    }

    /**
     * Called for each new row in a grid when it is saved.
     *
     * @param $record
     * @return bool
     */
    public function gridSheetHandleNewRow(array &$row) {
        $this->owner->update(
            $this->getUpdateColumns($this->owner->class, $row)
        );
    }

    /**
     * Called to each existing row in a grid when it is saved.
     *
     * @param $record
     * @return bool
     */
    public function gridSheetHandleExistingRow(array &$row) {
        $this->owner->update(
            $this->getUpdateColumns($this->owner->class, $row)
        );
    }
}