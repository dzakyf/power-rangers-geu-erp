<?php

if (!function_exists('can_menu')) {
    function can_menu(string $menuId, string $action = 'view'): bool
    {
        if (!auth()->check()) {
            return false;
        }

        return auth()->user()->can("menu.{$menuId}.{$action}");
    }
}

if (!function_exists('leads_interest_label')) {
    /**
     * Partnership interest label for a lead ("Opsi 1" = Mobil Listrik,
     * "Opsi 2" = Booth). Priority: opportunity_partner.package_option,
     * then opportunity.Hub_Type, then legacy Partnership_Interest int.
     */
    function leads_interest_label($packageOption = null, $hubType = null, $partnershipInterest = null): string
    {
        foreach ([$packageOption, $hubType] as $code) {
            if ($code === 'mobil' || $code === 'option2') return 'Opsi 1';
            if ($code === 'booth' || $code === 'option1') return 'Opsi 2';
        }

        $pi = (int) $partnershipInterest;
        if ($pi === 1) return 'Opsi 1';
        if ($pi === 2) return 'Opsi 2';

        return 'Opsi 0';
    }
}
