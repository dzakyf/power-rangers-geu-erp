<?php

namespace App\Services\CNS;

class CnsPermissionService
{
    /**
     * Map of CNS Menu_ID to snake_case menu_name slug.
     */
    private const MENU_MAP = [
        // Level 1: Module Root
        'MS00011' => 'folder_cns',

        // Level 2: Top-level Folders
        'SM00065' => 'folder_contract',
        'SM00056' => 'folder_shipping_management',
        'SM00057' => 'folder_stuffing',
        'SM00058' => 'folder_export_management',
        'SM00059' => 'folder_quality_control',
        'SM00060' => 'folder_sales_commercial',
        'SM00061' => 'folder_sustainability',
        'SM00062' => 'folder_procurement',
        'SM00063' => 'folder_inventory',
        'SM00064' => 'folder_master_data',
        'SM00066' => 'folder_access_control',
        'SM00080' => 'folder_dashboard',
        'SM00073' => 'folder_main_customer_compliance',
        'SM00069' => 'folder_finance_accounting',

        // Level 3: Sub Folders
        'SS00090' => 'folder_sub_buyer_contract',
        'SS00094' => 'folder_customer_compliance',
        'SS00080' => 'folder_sub_shipping_management',
        'SS00081' => 'folder_sub_stuffing',
        'SS00082' => 'folder_sub_export_management',
        'SS00097' => 'folder_cost_control',
        'SS00083' => 'folder_sub_quality_control',
        'SS00084' => 'folder_sub_sales_commercial',
        'SS00114' => 'folder_pricing_quotation',
        'SS00085' => 'folder_sub_sustainability',
        'SS00086' => 'folder_sub_procurement',
        'SS00087' => 'folder_inventory_uco',
        'SS00091' => 'folder_inventory_non_uco',
        'SS00088' => 'folder_sub_master_data',
        'SS00095' => 'folder_sub_access_control',
        'SS00150' => 'folder_sub_dashboard',
        'SS00099' => 'folder_sub_finance_accounting',

        // Level 4: Leaf Pages / Actions
        'SI00331' => 'buyer_contract',
        'SI00345' => 'offering_letter',
        'SI00346' => 'customer_compliance_kyc',
        'SI00304' => 'vessel_schedule',
        'SI00305' => 'stuffing',
        'SI00307' => 'shipping_instruction',
        'SI00308' => 'final_shipping_instruction',
        'SI00309' => 'packing_list',
        'SI00310' => 'commercial_invoice',
        'SI00311' => 'weight_bridge_ticket',
        'SI00313' => 'certificate_of_origin',
        'SI00314' => 'sustainability_declaration',
        'SI00315' => 'delivery_note',
        'SI00325' => 'annex_vii',
        'SI00350' => 'export_checklist',
        'SI00351' => 'cost_allocation_shipment',
        'SI00352' => 'product_allocation',
        'SI00354' => 'sales_forecast',
        'SI00316' => 'quality_control',
        'SI00318' => 'sales_order',
        'SI00330' => 'traceability',
        'SI00319' => 'purchase_request',
        'SI00322' => 'purchase_request_outstanding',
        'SI00321' => 'purchase_order',
        'SI01075' => 'goods_receipt',
        'SI00317' => 'inbound_movement',
        'SI00340' => 'tank_draining',
        'SI00343' => 'inbound_adjustment',
        'SI00344' => 'drain_adjustment',
        'SI00341' => 'inventory_kemasan',
        'SI00332' => 'inventory_non_uco',
        'SI00327' => 'master_company',
        'SI00328' => 'master_supplier',
        'SI00329' => 'master_consignee',
        'SI01074' => 'master_tank',
        'SI00342' => 'master_kemasan',
        'SI00347' => 'user_management',
        'SI00348' => 'role_management',
        'SI01150' => 'export_volume',
        'SI00353' => 'export_invoicing',
    ];

    public static function allMenuSlugs(): array
    {
        return self::MENU_MAP;
    }

    public static function getSlug(string $menuId): ?string
    {
        return self::MENU_MAP[$menuId] ?? null;
    }

    public static function getMenuId(string $slug): ?string
    {
        $flipped = array_flip(self::MENU_MAP);
        return $flipped[$slug] ?? null;
    }

    public static function isCnsMenu(string $menuId): bool
    {
        return isset(self::MENU_MAP[$menuId]);
    }

    /**
     * Build permission name: 'cns.{menu_name}.{action}'
     */
    public static function permissionName(string $menuId, string $action): string
    {
        $slug = self::getSlug($menuId);
        if (!$slug) {
            $slug = strtolower($menuId);
        }

        return "cns.{$slug}.{$action}";
    }
}
