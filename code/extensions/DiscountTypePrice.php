<?php

class StreakDiscountTypePriceExtension extends StreakDiscountTypeExtension
{
    const ColumnFieldName = 'UsePriceColumn';

    // suffix for the two columns being added and selected on (if not just the column's themselves, e.g. may want
    // to implement cols with 'Currency' and 'Price'
    const CurrencyColumnSuffix = 'Currency';
    const ColumnSuffix = 'Price';
    const Symbol = '$';

    const DefaultColumnName = 'Price';

    private static $db = array(
        self::ColumnFieldName => self::ColumnFieldSchema
    );

    /**
     * If no column names set for DiscountPrice then make values using the Code.
     */
    public function onBeforeWrite() {
        $option = $this()->StreakDiscountOptions;

        if ($option) {
            // option set so from CMS form field post
            $optionFieldName = StreakDiscountTypePriceExtension::ColumnFieldName . 'Option';

            if ($option == $optionFieldName) {
                if (!$this()->{StreakDiscountTypePriceExtension::ColumnFieldName}) {
                    $fieldName = $this()->Code . self::ColumnSuffix;
                    $this()->{StreakDiscountTypePriceExtension::ColumnFieldName} = $fieldName;
                }
            } else {
                // was from CMS form post but option was different so clear this one
                $this()->{StreakDiscountTypePriceExtension::ColumnFieldName} = null;
            }
        }
    }

    public function provideEditableColumns(array &$fieldSpecs) {

        $options = array();
        $value = null;
        $fieldName = static::ColumnFieldName;

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
            'UsePriceColumn' => array(
                'title' => 'Price',
                'callback' => function($record) {
                    return new CheckboxField(
                        'UsePriceColumn',
                        ''
                    );
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
        xdebug_break();
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