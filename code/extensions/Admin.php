<?php

class StreakDiscounts_AdminExtension extends Extension {
    /**
     * Adds the 'build' link to the CMS edit form after the gridsheet.
     *
     * @param Form $form
     */
    public function updateStreakAdminForm(Form $form) {
        $fields = $form->Fields();
        if ($gridField = $fields->fieldByName('StreakDiscountType')) {
            $fields->insertAfter(
                new LiteralField('buildLink', '<p><a class="build-link" href="/dev/build?flush=1"><strong>Add fields to disountable objects</strong></strong></a></p>'),
                'StreakDiscountType'
            );
        }
    }
}