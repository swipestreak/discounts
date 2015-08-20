<?php

class StreakDiscounts_ConfigExtension extends DataExtension {
    const FieldName = 'StreakSiteWideDiscountID';
    const RelationshipName = 'StreakSiteWideDiscount';

    private static $has_one = array(
        self::RelationshipName => 'StreakDiscountType'
    );

    public function provideConfigFields(FieldList $fields) {
        $fields->push(
            new Select2Field(
                self::FieldName,
                'Site wide discount',
                StreakDiscountType::get()->map()->toArray()
            )
        );
    }
}