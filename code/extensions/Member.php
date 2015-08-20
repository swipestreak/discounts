<?php
class StreakDiscountsMemberExtension extends DataExtension {
    const FieldName = 'StreakDiscountType';
    const RelatedClassName = self::FieldName;

    private static $db = array(

    );
    private static $has_one = array(
        self::FieldName => self::RelatedClassName
    );

    /**
     * Add discount type field to member
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields) {
        $fields->insertAfter(
            $field = new DropdownField(
                self::FieldName .  'ID',
                StreakDiscountType::config()->get('singular_name'),
                StreakDiscountType::get()->map()->toArray()
            ),
            'Email'
        );
        $field->setEmptyString('None');

    }
}