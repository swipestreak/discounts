<?php


class StreakDiscountableExtension extends DataExtension {

    private static $has_one = array(
        'StreakDiscountType' => 'StreakDiscountType'
    );

    /**
     * Returns the one or more active DiscountTypes (site, member, product) in order of preference (or an empty array if none found).
     *
*@return array
     */
    public function resolveDiscountTypes() {
        $discountTypes = array();

        if (StreakConfig::streak_config()->StreakSiteWideDiscountID) {
            $discountTypes['SiteWide'] = StreakDiscountType::get()->byID(
                StreakConfig::streak_config()->StreakSiteWideDiscountID
            );
        }

        if (Member::currentUserID()) {
            $discountTypes['Member'] = StreakDiscountType::get()->byID(
                Member::currentUser()->StreakDiscountTypeID
            );
        }

        if ($this->owner->StreakDiscountTypeID) {
            $discountTypes['Product'] = $this->owner->StreakDiscountType();
        }
        return array_filter($discountTypes);
    }

    public function discountedPrice($price = null) {
        $price = is_null($price) ? $this->owner->Price : $price;

        if ($discountTypes = $this->resolveDiscountTypes()) {

            foreach ($discountTypes as $discountType) {

                $column = $discountType->getDiscountColumnName();

                $discountModifier = $this->owner->$column ?: $discountType->Measure;

                if ($discountModifier) {
                    $amount = new Price();
                    $amount->setAmount($price);

                    $originalAmount = new Price();
                    $originalAmount->setAmount($price);

                    $this->owner->extend('alterAmount', $amount, $discountModifier, $amount);

                    $price = $amount->getAmount();
                }
            }
        }
        return $price;
    }

    /**
     * Choose Discounted price depending on current member's StreakDiscountType.
     *
     * @param Price $amount
     */
    public function updateAmount($amount) {
        // only if we are on a front-end page
        if (Controller::curr() instanceof Page_Controller) {
            if ($discountedPrice = $this->discountedPrice()) {
                $amount->setAmount($discountedPrice);
            }
        }
    }
}
