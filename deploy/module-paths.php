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
        'database/migrations/bdp',
        'resources/views/partner',
        'resources/views/home-businesspartner.blade.php',
        'tests/Feature/BusinessPartner',
        'tests/Unit/BusinessPartner',
    ],

    'finance' => [
        'app/Modules/Finance',
        'app/Http/Controllers/Finance',
        'app/Http/Controller/Accounting',
        'app/Http/Controllers/OperationalController.php',
        'app/Http/Controllers/ExpenditureController.php',
        'app/Services/Finance',
        'routes/modules/finance.php',
        'database/migrations/finance',
        'resources/views/finance',
        'resources/views/home-finance.blade.php',
        'tests/Feature/Finance',
        'tests/Unit/Finance',
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
        'resources/views/cns',
        'resources/views/home-cns.blade.php',
        'tests/Feature/CNS',
        'tests/Unit/CNS',
    ],

    'hcga' => [
        'app/Modules/HCGA',
        'app/Http/Controllers/HCGA',
        'app/Services/HCGA',
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
        'resources/views/humanresources',
        'resources/views/home-hrd.blade.php',
        'resources/views/assets/inventory-incoming-detail-update2.blade.php',
        'resources/views/assets/inventory-request-management-detail2-update.blade.php',
        'tests/Feature/HCGA',
        'tests/Unit/HCGA',
        'tests/Unit/Rbac/RecruitmentRoleResolutionTest.php',
    ],
];
