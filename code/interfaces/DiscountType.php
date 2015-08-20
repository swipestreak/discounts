<?php

interface StreakDiscountTypeInterface {

    /**
     * Returns the amount with the type's metric applied.
     *
     * @param Price|number $amount
     * @return Price
     */
    public function discountedAmount($amount);
}