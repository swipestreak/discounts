<?php

class StreakDiscountTypeExtension extends GridSheetModelExtension  {
    // specify in concrete class
    const ColumnFieldName = '';
    const ColumnFieldSchema = 'Varchar(32)';
    const DefaultColumnName = '';
    const CodeFieldName = 'Code';
    const Symbol = '';

    const ColumnSuffix = '';

    const ModelClass = 'StreakDiscountType';

    const UseColumnFieldName = '';

    /**
     * Remove the text field as we are using optionset to select which field to use
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields) {
        $fields->removeByName(static::ColumnFieldName);
    }

    /**
     * Return the name for this column as it would appear on a data object.
     */
    public function getDiscountColumnName() {
        return $this->owner->Code . static::ColumnSuffix;
    }



    public function provideDiscountTypeOptions(array &$options, &$value) {
        $options[static::ColumnFieldName . 'Option'] = $this->fieldLabel(static::ColumnFieldName);
        if ($this()->{static::ColumnFieldName}) {
            // should only be one column used so set that to value for selecter.
            $value = static::ColumnFieldName . 'Option';
        }
    }

    public function provideGridSheetData($modelClass, $isRelated) {
        if ($modelClass == self::ModelClass) {
            return StreakDiscountType::get();
        }
    }

    public static function discount_types($discountOnly = true) {
        return StreakDiscountType::get()
            ->filter(static::ColumnFieldName, true)
            ->distinct(static::CodeFieldName);


    }

    public static function discount_codes($discountOnly = true) {
        return StreakDiscountType::get()
            ->setQueriedColumns(array('ID', self::CodeFieldName))
            ->filter(static::ColumnFieldName, true)
            ->distinct(self::CodeFieldName)
            ->map('ID', self::CodeFieldName)
            ->toArray();
    }

    /**
     * Called when a grid sheet is displaying a model directly, e.g. as a model admin managed model.
     *
     * @param array $fieldSpecs
     * @return mixed
     */
    public function provideEditableColumns(array &$fieldSpecs) {
        // TODO: Implement provideEditableColumns() method.
    }

    /**
     * Called when a grid sheet is displaying a model related to another model. e.g. as a grid for a models ItemEditForm
     * in ModelAdmin.
     *
     * @param $relatedModelClass
     * @param $relatedID
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
        $updateData = $this->getUpdateColumns($this->owner->class, $row);

        $this->owner->update(
            $updateData
        );
    }

    /**
     * Called to each existing row in a grid when it is saved.
     *
     * @param $record
     * @return bool
     */
    public function gridSheetHandleExistingRow(array &$row) {
        $updateData = $this->getUpdateColumns($this->owner->class, $row);

        $this->owner->update(
            $updateData
        );
    }
}