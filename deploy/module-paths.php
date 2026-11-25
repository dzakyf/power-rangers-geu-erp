<?php

/*
|--------------------------------------------------------------------------
| Module-owned paths
|--------------------------------------------------------------------------
|
| Globs (relative to the repository root) that belong to each module. Core
| is implicit: anything not claimed here stays in every profile. Paths are
| also what the build-strip removes from a profile that does not enable the
| module, so keep this list current as code is relocated into app/Modules.
|
*/

return [
    'credential' => [
        'app/Modules/Credential',
        'routes/modules/credential.php',
        'tests/Feature/Credential',
        'tests/Unit/Rbac',
    ],

    'bp' => [
        'app/Modules/BusinessPartner',
        'app/Http/Controllers/Partner',
        'app/Http/Controllers/MarketingController.php',
        'app/Opportunity*.php',
        'app/WarehousePartner*.php',
        'app/Services/Partner',
        'routes/modules/bp.php',
        'routes/modules/bp-public.php',
        'database/migrations/bdp',
        'resources/views/partner',
        'resources/views/projection',
        'resources/views/uco',
        'resources/views/home-businesspartner.blade.php',
        'docs/business-partner',
        'tests/Feature/BusinessPartner',
        'tests/Feature/PartnerRegistration',
        'tests/Feature/Survey',
        'tests/Feature/UcoInventory',
        'tests/Feature/UcoLogistics',
        'tests/Feature/Concerns/SeedsAftersalesFixtures.php',
        'tests/Unit/BusinessPartner',
    ],

    'finance' => [
        'app/Modules/Finance',
        'app/Http/Controllers/Finance',
        'app/Http/Controllers/OperationalController.php',
        'app/Http/Controllers/ExpenditureController.php',
        'app/Services/Finance',
        'routes/modules/finance.php',
        'routes/modules/finance-public.php',
        'database/migrations/finance',
        'resources/views/finance',
        'resources/views/home-finance.blade.php',
        'tests/Feature/Finance',
        'tests/Unit/Finance',
    ],

    'core' => [
        'routes/modules/core.php',
    ],

    'legacy' => [
        'app/Http/Controllers/ProcurementController.php',
        'app/Http/Controllers/SupplierController.php',
        'app/Http/Controller/Accounting',
        'routes/modules/legacy.php',
        'database/migrations/2026_08_11_000009_create_purchase_request_table.php',
        'public/mobile/procurement',
        'resources/views/sales',
        'resources/views/accounting',
        'resources/views/project',
        'resources/views/procurement',
        'resources/views/purchasing',
        'resources/views/dashboard',
    ],

    'cns' => [
        'app/Modules/CNS',
        'app/Http/Controllers/CNS',
        'app/Services/CNS',
        'app/Models/CNS',
        'app/Cns*.php',
        'app/VesselSchedule*.php',
        'app/BuyerContract*.php',
        'app/PackingList.php',
        'app/ShippingInstruction.php',
        'app/ShipmentInstruction.php',
        'app/CommercialInvoice.php',
        'app/WeightBridgeTicket*.php',
        'app/CertificateOfOrigin*.php',
        'app/AnnexVII.php',
        'app/SalesOrderCNS*.php',
        'app/TankDraining.php',
        'app/SludgeMovement.php',
        'routes/modules/cns.php',
        'database/migrations/cns',
        'database/migrations/2026_08_11_000011_create_annex_vii_table.php',
        'resources/views/cns',
        'public/js/cns-grid-pagination.js',
        'resources/views/home-cns.blade.php',
        'docs/cns',
        'tests/Feature/CNS',
        'tests/Unit/CNS',
    ],

    'hcga' => [
        'app/Modules/HCGA',
        'app/Http/Controllers/HCGA',
        'app/Services/HCGA',
        'app/Services/Attendance',
        'app/Models/HCGA',
        'app/Hcga*.php',
        'app/Services/Approval/AssetLifecycleApprovalService.php',
        'app/Services/Approval/ExitClearanceApprovalService.php',
        'app/Services/Approval/SettlementApprovalService.php',
        'app/Services/Approval/SppdApprovalService.php',
        'app/Services/Approval/TrainingApprovalService.php',
        'app/Services/GA',
        'app/Services/Payroll',
        'app/Console/Commands/GaInventoryOpeningBalance.php',
        'app/Console/Commands/PatchSppdTemplatesCommand.php',
        'routes/modules/hcga.php',
        'routes/modules/hcga-hr.php',
        'routes/modules/hcga-public.php',
        'database/migrations/hcga',
        'database/migrations/approval',
        'database/migrations/2026_07_29_000000_create_onboarding_materials_table.php',
        'database/migrations/2026_07_29_000001_add_progress_columns_to_recruitment_table.php',
        'database/migrations/2026_08_11_000005_create_master_hr_approval_table.php',
        'database/migrations/2026_09_10_000001_add_sppd_mode_to_car_rent_table.php',
        'database/migrations/2026_10_22_000001_create_vehicle_operations_tables.php',
        'database/migrations/2026_10_28_134558_create_status_changes_table.php',
        'database/migrations/2026_10_29_000001_add_jenis_to_status_changes_table.php',
        'resources/views/humanresources',
        'resources/views/assets',
        'resources/views/home-hrd.blade.php',
        'tests/Feature/HCGA',
        'tests/Unit/HCGA',
        'tests/Unit/Rbac/RecruitmentRoleResolutionTest.php',
    ],
];
