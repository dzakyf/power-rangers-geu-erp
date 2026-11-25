<?php

return array (
  'enabled' => 
  array (
    0 => 'core',
    1 => 'credential',
    2 => 'bp',
    3 => 'finance',
  ),
  'providers' => 
  array (
    'credential' => 'App\\Modules\\Credential\\Providers\\CredentialServiceProvider',
    'bp' => 'App\\Modules\\BusinessPartner\\Providers\\BusinessPartnerServiceProvider',
    'finance' => 'App\\Modules\\Finance\\Providers\\FinanceServiceProvider',
    'cns' => 'App\\Modules\\CNS\\Providers\\CNSServiceProvider',
    'hcga' => 'App\\Modules\\HCGA\\Providers\\HCGAServiceProvider',
  ),
  'routes' => 
  array (
    'core' => 'core',
    'credential' => 'credential',
    'bp' => 'bp',
    'finance' => 'finance',
    'cns' => 'cns',
    'hcga' => 'hcga',
    'legacy' => 'legacy',
  ),
  'migration_dirs' => 
  array (
    'core' => 
    array (
      0 => 'core',
      1 => 'approval-core',
    ),
    'bp' => 
    array (
      0 => 'bdp',
    ),
    'finance' => 
    array (
    ),
    'cns' => 
    array (
      0 => 'cns',
    ),
    'hcga' => 
    array (
      0 => 'hcga',
    ),
    'legacy' => 
    array (
      0 => 'legacy',
    ),
  ),
  'core_menus' => 
  array (
    0 => 'CANDIDATE',
  ),
);
