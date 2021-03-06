<?php

/**
 * StreakDiscountType
 *
 * @property string $Code
 * @property string $Title
 * @property string $UsePriceColumn
 * @property string $UsePercentageColumn
 * @property SS_List $Members
 */
class StreakDiscountType extends DataObject {
    const OptionSetName = 'StreakDiscountOptions';

    private static $db = array(
        'Code' => 'Varchar(6)',
        'Title' => 'Varchar(32)',
        'Measure' => 'Decimal(5,2)'
    );
    private static $has_many = array(
        'Members' => 'Member',
        'Products' => 'Product',
        'Variations' => 'Variation'
    );

    private static $singular_name = 'Discount Type';

    private static $default_sort = 'Title';

    private static $summary_fields = array(
        'Code' => 'Short Code',
        'Title' => 'Title'
    );

    /**
     * Iterate through extensions to ask them to calculate the new amount.
     *
     * @param $forAmount
     * @return mixed|Price
     */
    public function discountedAmount($forAmount) {
        $price = new Price();

        if ($values = $this->extend('discountedAmount', $forAmount)) {
            $price = reset($values);
            if (!$price->getCurrency()) {
                $price->setCurrency(ShopConfig::current_shop_config()->BaseCurrency);
            }
        } else {
            if ($forAmount instanceof Money) {
                $price->setAmount($forAmount->getAmount());
                $price->setCurrency($forAmount->getCurrency());
            } else {
                $price->setAmount($forAmount);
                $price->setCurrency(ShopConfig::current_shop_config()->BaseCurrency);
            }
        }
        return $price;
    }

    public function getCMSFields() {
        $fields = parent::getCMSFields();

        $options = array();
        $this->extend('provideDiscountTypeOptions', $options, $value);

        $fields->addFieldToTab(
            'Root.Main',
            new OptionsetField(
                self::OptionSetName,
                'Discount Options',
                $options,
                $value
            )
        );

        if ($this->isInDB()) {
            $fields->addFieldToTab('Root.Main',
                new LiteralField('buildLink', '<a href="/dev/build?flush=1">Add fields to discountable objects</a>')
            );
        }

        return $fields;
    }

    public static function get_by_code($code) {
        return StreakDiscountType::get()->filter('Code', $code)->first();
    }

    /**
     * Return the name of all unique 'Discount Price' columns, optionally excluding
     * the main/default 'Price' column.
     *
     * @param bool|false $discountOnly
     * @return array
     */
    public static function get_columns($discountOnly = false) {
        $allColumns = static::get()->column(static::ColumnFieldName);
        return $discountOnly
            ? array_diff(
                $allColumns,
                array(
                    StreakDiscountTypePriceExtension::DefaultColumnName
                )
            )
            : $allColumns;
    }

}