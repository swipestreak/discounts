<?php

/**
 * Takes care of module build, requiring default records and other common functionality.
 *
 * Can be used as classexists test in config to see if this module is excluded.
 */
class StreakDiscountModule extends Object {
    const DefaultCode = 'RETAIL';
    const DefaultTitle = 'Retail';

    private static $default_code = self::DefaultCode;

    private static $default_title = self::DefaultTitle;

    private static $records = array(
        self::DefaultCode => array(
            'Title' => self::DefaultTitle,
            StreakDiscountTypePriceExtension::ColumnFieldName => 'Price',
            StreakDiscountTypePercentageExtension::ColumnFieldName => null,
            'IsDev' => true,
            'IsTest' => true,
            'IsLive' => true
        ),
        'TRADE1' => array(
            'Title' => 'Trade 1',
            StreakDiscountTypePriceExtension::ColumnFieldName => 'TRADE1Price',
            StreakDiscountTypePercentageExtension::ColumnFieldName => null,
            'IsDev' => true,
            'IsTest' => true,
            'IsLive' => false
        ),
        'SALE10' => array(
            'Title' => 'Example Sale 10%',
            StreakDiscountTypePriceExtension::ColumnFieldName => null,
            StreakDiscountTypePercentageExtension::ColumnFieldName => 'SALE10Percentage',
            'IsDev' => true,
            'IsTest' => true,
            'IsLive' => false
        )
    );

    public function requireTable() {
        DB::dontRequireTable(__CLASS__);
    }

    public function XrequireDefaultRecords() {
        foreach ($this->config()->get('records') as $code => $record) {
            if (($record['IsDev'] && Director::isDev())
                || ($record['IsTest'] && Director::isTest())
                || ($record['IsLive'] && Director::isLive())) {

                if (!$discountType = StreakDiscountType::get_by_code($code)) {
                    $discountType = StreakDiscountType::create();
                    DB::alteration_message("Added discount type '$code'", "changed");
                }
                // if the record is using default code then update from config.
                if ($code == self::DefaultCode) {
                    $record['Code'] = $this->config()->get('default_code');
                } else {
                    $record['Code'] = $code;
                }

                $title = $record['Title'];
                // if the record is using default title then update from config as hasn't changed, if different
                // then leave alone
                if ($title == self::DefaultTitle) {
                    $record['Title'] = $this->config()->get('default_title');
                }

                $data = array_diff_key(
                    $record,
                    array_flip(array('IsDev', 'IsTest', 'IsLive'))
                );

                $discountType->update($data);
                $discountType->write();

            }
        }
    }
}