<?php

/*
|--------------------------------------------------------------------------
| Release profiles
|--------------------------------------------------------------------------
|
| Each profile is a module composition built from the same trunk. Adding a
| module to a later release is a new profile entry, never a new branch.
|
*/

return [
    'bdp' => [
        'version' => '1.0.0',
        'modules' => ['core', 'bp', 'finance'],
    ],
    'cns' => [
        'version' => null,
        'modules' => ['core', 'cns'],
    ],
    'hcga' => [
        'version' => null,
        'modules' => ['core', 'cns', 'hcga'],
    ],
    'full' => [
        'version' => null,
        'modules' => ['core', 'bp', 'finance', 'cns', 'hcga', 'legacy'],
    ],
];
