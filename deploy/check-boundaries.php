#!/usr/bin/env php
<?php

/*
|--------------------------------------------------------------------------
| Cross-module boundary check
|--------------------------------------------------------------------------
|
| Usage: php deploy/check-boundaries.php <profile> [artifact-path]
|
| Fails when code in an enabled module (or core) still references a module
| that the profile strips out. Run it against a built artifact, not the
| monolith, so a module's own files are not reported.
|
*/

$root = dirname(__DIR__);
$profiles = require __DIR__.'/profiles.php';

$name = $argv[1] ?? null;
$target = $argv[2] ?? ($name ? $root.'/build/'.$name : null);

if ($name === null || !isset($profiles[$name]) || $target === null) {
    fwrite(STDERR, "usage: check-boundaries <profile> [artifact-path]\n");
    exit(1);
}

$enabled = $profiles[$name]['modules'];
$disabled = array_values(array_diff(
    ['bp', 'finance', 'cns', 'hcga', 'legacy'],
    $enabled
));

if ($disabled === []) {
    echo "No disabled modules for profile {$name}.\n";
    exit(0);
}

$needles = [
    'bp' => [
        'App\\Modules\\BusinessPartner',
        'App\\Http\\Controllers\\Partner',
        'App\\Services\\Partner',
        'App\\Console\\Commands\\ReconcileUcoInstock',
        'App\\Console\\Commands\\GrantMitraModulePermissions',
    ],
    'finance' => [
        'App\\Modules\\Finance',
        'App\\Http\\Controllers\\Finance',
        'App\\Services\\Finance',
    ],
    'legacy' => [
        'App\\Http\\Controllers\\ProcurementController',
        'App\\Http\\Controllers\\SupplierController',
        'App\\Http\\Controller\\Accounting',
    ],
    'cns' => [
        'App\\Modules\\CNS',
        'App\\Http\\Controllers\\CNS',
        'App\\Services\\CNS',
        'App\\Models\\CNS',
        'App\\Cns',
        'App\\VesselSchedule',
        'App\\BuyerContract',
        'App\\PackingList',
        'App\\ShippingInstruction',
        'App\\ShipmentInstruction',
        'App\\CommercialInvoice',
        'App\\WeightBridgeTicket',
        'App\\CertificateOfOrigin',
        'App\\AnnexVII',
        'App\\SalesOrderCNS',
        'App\\TankDraining',
        'App\\SludgeMovement',
    ],
    'hcga' => [
        'App\\Modules\\HCGA',
        'App\\Http\\Controllers\\HCGA',
        'App\\Services\\HCGA',
        'App\\Models\\HCGA',
        'App\\Services\\GA',
        'App\\Services\\Payroll',
        'App\\Services\\Approval\\AssetLifecycleApprovalService',
        'App\\Services\\Approval\\ExitClearanceApprovalService',
        'App\\Services\\Approval\\SettlementApprovalService',
        'App\\Services\\Approval\\SppdApprovalService',
        'App\\Services\\Approval\\TrainingApprovalService',
        'App\\Hcga',
    ],
];

$searchDirs = ['app', 'bootstrap', 'config', 'database', 'resources', 'routes', 'deploy'];
$extensions = ['php'];
$violations = [];

foreach ($searchDirs as $dir) {
    $path = $target.'/'.$dir;

    if (!is_dir($path)) {
        continue;
    }

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($files as $file) {
        if (!in_array(strtolower($file->getExtension()), $extensions, true)) {
            continue;
        }

        $code = file_get_contents($file->getPathname());

        foreach ($disabled as $module) {
            foreach ($needles[$module] as $needle) {
                if (strpos($code, $needle) !== false) {
                    $violations[] = sprintf(
                        '%s: references disabled module "%s" (%s)',
                        substr($file->getPathname(), strlen($target) + 1),
                        $module,
                        $needle
                    );
                }
            }
        }
    }
}

if ($violations !== []) {
    fwrite(STDERR, "Boundary violations for profile {$name}:\n");
    foreach (array_unique($violations) as $violation) {
        fwrite(STDERR, '  - '.$violation."\n");
    }
    exit(1);
}

echo "Boundaries OK for profile {$name}.\n";
