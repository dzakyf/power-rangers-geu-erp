<?php

/*
|--------------------------------------------------------------------------
| Module Registry
|--------------------------------------------------------------------------
|
| Which modules this deployment composes. Order matters: "core" must come
| first so shared infrastructure is registered before module code that
| depends on it. A slim release sets MODULES=core,bp,finance so only that
| profile's routes/providers/migrations are registered.
|
*/

return [

    'enabled' => array_values(array_filter(array_map('trim', explode(',', (string) env('MODULES', 'core,credential,bp,finance,cns,hcga,legacy'))))),

    /*
    | Module => service provider class. Registered conditionally by
    | App\Providers\AppServiceProvider based on the enabled list.
    */
    'providers' => [
        'credential' => App\Modules\Credential\Providers\CredentialServiceProvider::class,
        'bp'      => App\Modules\BusinessPartner\Providers\BusinessPartnerServiceProvider::class,
        'finance' => App\Modules\Finance\Providers\FinanceServiceProvider::class,
        'cns'     => App\Modules\CNS\Providers\CNSServiceProvider::class,
        'hcga'    => App\Modules\HCGA\Providers\HCGAServiceProvider::class,
    ],

    /*
    | Module => route file basename under routes/modules/. Loaded inside the
    | existing log.access middleware group. "legacy" is the catch-all for the
    | pre-existing ERP modules that are not yet extracted.
    */
    'routes' => [
        'core'       => 'core',
        'credential' => 'credential',
        'bp'         => 'bp',
        'finance'    => 'finance',
        'cns'        => 'cns',
        'hcga'       => 'hcga',
        'legacy'     => 'legacy',
    ],

    /*
    | Module => migration directories under database/migrations. A module can
    | own more than one directory (e.g. core + approval-core). Modules with no
    | entry fall back to a directory named after the module.
    */
    'migration_dirs' => [
        'core'    => ['core', 'approval-core'],
        'bp'      => ['bdp'],
        'finance' => [],
        'cns'     => ['cns'],
        'hcga'    => ['hcga'],
        'legacy'  => ['legacy'],
    ],

    /*
    | menu_lists.Modules values owned by core itself (not by an optional
    | module). Optional modules contribute theirs via their permission
    | provider's menuModules() method.
    */
    'core_menus' => ['CANDIDATE'],

];
