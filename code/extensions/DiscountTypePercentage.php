<?php

class StreakDiscountTypePercentageExtension extends StreakDiscountTypeExtension
{
    const ColumnFieldName = 'UsePercentageColumn';
    const ColumnSuffix = 'Percentage';
    const Symbol = '%';

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
            'UsePercentageColumn' => array(
                'title' => 'Percentage',
                'callback' => function($record) {
                    return new CheckboxField(
                        'UsePercentageColumn',
                        ''
                    );
                }
            ),
            'Metric' => array(
                'title' => 'Value (% only)',
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

}