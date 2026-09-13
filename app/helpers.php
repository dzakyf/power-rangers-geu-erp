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
     * Partnership package label for a lead ("Mobil Listrik" / "Booth").
     * Priority: opportunity_partner.package_option, then opportunity.Hub_Type,
     * then the legacy Partnership_Interest int (1 = mobil, 2 = booth).
     * Returns "-" when the package is unknown (never a bogus "Opsi 0").
     */
    function leads_interest_label($packageOption = null, $hubType = null, $partnershipInterest = null): string
    {
        foreach ([$packageOption, $hubType] as $code) {
            if ($code === 'mobil' || $code === 'option2') return 'Mobil Listrik';
            if ($code === 'booth' || $code === 'option1') return 'Booth';
        }

        $pi = (int) $partnershipInterest;
        if ($pi === 1) return 'Mobil Listrik';
        if ($pi === 2) return 'Booth';

        return '-';
    }
}

if (!function_exists('user_signature_path')) {
    /**
     * Absolute path of the user's latest stored signature image, or null.
     */
    function user_signature_path($userId): ?string
    {
        if (!$userId) {
            return null;
        }

        $meta = \Illuminate\Support\Facades\DB::table('meta_files')
            ->where('Topic', 'user-signature')
            ->where('Map_To', (string) $userId)
            ->orderByDesc('ID')
            ->first();

        if (!$meta) {
            return null;
        }

        $path = public_path('filevendor/' . $meta->Path);

        return file_exists($path) ? $path : null;
    }
}

if (!function_exists('apply_stored_signature')) {
    /**
     * Embed the user's stored signature into a PhpWord TemplateProcessor.
     * Returns true when an image was applied.
     */
    function apply_stored_signature($doc, string $placeholder, $userId): bool
    {
        $path = user_signature_path($userId);

        if (!$path) {
            return false;
        }

        $doc->setImageValue($placeholder, [
            'path' => $path,
            'height' => 100,
            'ratio' => false,
        ]);

        return true;
    }
}
