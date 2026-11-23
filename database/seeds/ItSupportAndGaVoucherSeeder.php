<?php

use App\Permission;
use App\Role;
use App\Services\RbacService;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder master data untuk modul IT Support & Helpdesk serta
 * kebijakan approval 2-fase GA Order Pekerjaan & Voucher Pembayaran.
 *
 * Idempoten: aman dijalankan berulang kali.
 */
class ItSupportAndGaVoucherSeeder extends Seeder
{
    private const ACTIONS = ['view', 'create', 'edit', 'delete', 'approve'];

    private const MENUS = [
        'SS00153' => ['IT Support & Helpdesk', '', 3, 'SM00067', 1],
        'SI01158' => ['IT Helpdesk & Ticketing', 'hrd-it-ticket-index', 4, 'SS00153', 1],
        'SI01159' => ['My IT Assignments', 'hrd-it-my-assignment-index', 4, 'SS00153', 2],
    ];

    /** @var string */
    private $now;

    public function run()
    {
        $this->now = now();
        $this->info('Seeding IT Support & GA Voucher menus, teams, categories, and approval policies...');

        $this->seedMenusAndPermissions();
        $teamIds = $this->seedItTeams();
        $categoryIds = $this->seedItCategories($teamIds);
        $this->seedItRouting($categoryIds, $teamIds);
        $this->seedApprovalPolicies();

        $this->info('ItSupportAndGaVoucherSeeder complete.');
    }

    protected function seedMenusAndPermissions()
    {
        foreach (self::MENUS as $menuId => $menu) {
            DB::table('menu_lists')->updateOrInsert(
                ['Menu_ID' => $menuId],
                [
                    'Name'        => $menu[0],
                    'Path_Loc'    => $menu[1],
                    'Position'    => $menu[2],
                    'Parent_Menu' => $menu[3],
                    'Urutan'      => $menu[4],
                    'Modules'     => 'HRD',
                ]
            );
        }

        $allPermissionNames = [];
        foreach (array_keys(self::MENUS) as $menuId) {
            foreach (self::ACTIONS as $action) {
                $name = "menu.{$menuId}.{$action}";
                Permission::firstOrCreate(
                    ['name' => $name, 'guard_name' => 'web'],
                    ['menu_id' => $menuId, 'action' => $action]
                );
                $allPermissionNames[] = $name;
            }
        }

        $gaVoucherPermissions = [];
        foreach (['SS00102' => ['view'], 'SI01013' => self::ACTIONS] as $gaMenuId => $acts) {
            foreach ($acts as $action) {
                $name = "menu.{$gaMenuId}.{$action}";
                Permission::firstOrCreate(
                    ['name' => $name, 'guard_name' => 'web'],
                    ['menu_id' => $gaMenuId, 'action' => $action]
                );
                $gaVoucherPermissions[] = $name;
            }
        }

        $adminRoles = Role::where('guard_name', 'web')
            ->whereIn('name', ['ADM', 'HRD', 'HCGA'])
            ->get();
        foreach ($adminRoles as $role) {
            $this->grantMissingPermissions($role, array_merge($allPermissionNames, $gaVoucherPermissions));
        }

        $requesterPermissions = [
            'menu.SS00153.view',
            'menu.SI01158.view',
            'menu.SI01158.create',
            'menu.SS00102.view',
            'menu.SI01013.view',
            'menu.SI01013.create',
        ];
        $requesterRoles = Role::where('guard_name', 'web')
            ->whereIn('name', ['STAFF', 'MANAGER', 'HEAD', 'DIRECTOR', 'VP', 'MANAGER FINANCE'])
            ->get();
        foreach ($requesterRoles as $role) {
            $this->grantMissingPermissions($role, $requesterPermissions);
        }

        $roleIds = $adminRoles->pluck('id')->merge($requesterRoles->pluck('id'))->unique();
        if ($roleIds->isNotEmpty()) {
            $userIds = DB::table('model_has_roles')
                ->whereIn('role_id', $roleIds)
                ->pluck('model_id')
                ->unique();

            foreach ($userIds as $userId) {
                $user = User::find($userId);
                if ($user) {
                    RbacService::rebuildSubMenu($user);
                }
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    protected function grantMissingPermissions(Role $role, array $permissionNames)
    {
        $existing = $role->permissions()->pluck('name')->flip();
        $missing = array_values(array_filter($permissionNames, function ($name) use ($existing) {
            return !$existing->has($name);
        }));

        if (!empty($missing)) {
            $role->givePermissionTo($missing);
            $this->info('Granted ' . count($missing) . ' IT permissions to role ' . $role->name . ' (#' . $role->id . ').');
        }
    }

    protected function seedItTeams()
    {
        $teams = [
            'IT-SUPPORT' => ['IT Support & Helpdesk', 'INTERNAL', 'ICT'],
            'IT-INFRA'   => ['IT Infrastructure & System', 'INTERNAL', 'ICT'],
        ];

        $ids = [];
        foreach ($teams as $code => $team) {
            $row = DB::table('ga_team')->where('Code', $code)->first();
            if ($row) {
                $ids[$code] = (int) $row->ID;
                continue;
            }

            $ids[$code] = (int) DB::table('ga_team')->insertGetId([
                'Location'    => null,
                'Company_ID'  => null,
                'Code'        => $code,
                'Name'        => $team[0],
                'Team_Type'   => $team[1],
                'Supplier_ID' => null,
                'Department'  => $team[2],
                'Status'      => 'ACTIVE',
                'Created_By'  => 'seeder',
                'Created_At'  => $this->now,
            ]);
        }

        $itUserIds = DB::table('users')
            ->where('Status', 'ACTIVE')
            ->where(function ($q) {
                $q->whereIn('Department', ['IT', 'ICT'])
                    ->orWhereIn('username', ['admin', 'manager.it', 'staff.it', 'taufik.hidayat', 'doni.tata', 'asep.bustomi']);
            })
            ->where(function ($q) {
                $q->whereIn('Position', ['MIT', 'SINF', 'SSD', 'SFD', 'SITS', 'SBD', 'SFED', 'SNA', 'SCIF', 'ITF', 'VPD', 'Administrator'])
                    ->orWhereIn('username', ['admin', 'manager.it', 'staff.it', 'taufik.hidayat', 'doni.tata', 'asep.bustomi']);
            })
            ->pluck('id')
            ->all();

        foreach ($ids as $teamId) {
            foreach ($itUserIds as $userId) {
                DB::table('ga_team_member')->updateOrInsert(
                    ['Team_ID' => $teamId, 'User_Id' => (int) $userId],
                    [
                        'Supplier_ID'  => null,
                        'Role_In_Team' => 'MEMBER',
                        'Status'       => 'ACTIVE',
                        'Created_At'   => $this->now,
                    ]
                );
            }
        }

        return $ids;
    }

    protected function seedItCategories(array $teamIds)
    {
        $roots = [
            'IT-HELP' => ['Bantuan IT', 'NORMAL', 'IT-SUPPORT'],
            'IT-SYS'  => ['Gangguan Sistem', 'HIGH', 'IT-INFRA'],
            'IT-DEV'  => ['Pengajuan Perangkat IT', 'NORMAL', 'IT-SUPPORT'],
            'IT-PREV' => ['Preventive Maintenance IT', 'NORMAL', 'IT-SUPPORT'],
        ];

        $ids = [];
        foreach ($roots as $code => $category) {
            $ids[$code] = $this->upsertCategory(
                $code,
                $category[0],
                null,
                $category[1],
                $teamIds[$category[2]] ?? null
            );
        }

        $children = [
            'IT-HELP-HW'  => ['Hardware & PC/Laptop', 'IT-HELP', 'NORMAL', 'IT-SUPPORT'],
            'IT-HELP-SW'  => ['Software & Aplikasi', 'IT-HELP', 'NORMAL', 'IT-SUPPORT'],
            'IT-HELP-ACC' => ['Email & Akun User', 'IT-HELP', 'NORMAL', 'IT-SUPPORT'],
            'IT-HELP-PRN' => ['Printer & Peripheral', 'IT-HELP', 'LOW', 'IT-SUPPORT'],
            'IT-SYS-ERP'  => ['ERP / Aplikasi Internal', 'IT-SYS', 'HIGH', 'IT-INFRA'],
            'IT-SYS-NET'  => ['Jaringan & Internet', 'IT-SYS', 'URGENT', 'IT-INFRA'],
            'IT-SYS-SRV'  => ['Server & Database', 'IT-SYS', 'URGENT', 'IT-INFRA'],
            'IT-DEV-LAPTOP' => ['Laptop / PC', 'IT-DEV', 'NORMAL', 'IT-SUPPORT'],
            'IT-DEV-ACC'    => ['Aksesoris & Peripheral', 'IT-DEV', 'LOW', 'IT-SUPPORT'],
            'IT-DEV-LIC'    => ['Lisensi Software', 'IT-DEV', 'NORMAL', 'IT-SUPPORT'],
            'IT-PREV-MNT' => ['Maintenance Berkala IT', 'IT-PREV', 'NORMAL', 'IT-SUPPORT'],
            'IT-PREV-BKP' => ['Backup & Audit Sistem', 'IT-PREV', 'NORMAL', 'IT-SUPPORT'],
        ];

        foreach ($children as $code => $category) {
            $ids[$code] = $this->upsertCategory(
                $code,
                $category[0],
                $ids[$category[1]] ?? null,
                $category[2],
                $teamIds[$category[3]] ?? null
            );
        }

        return $ids;
    }

    protected function upsertCategory($code, $name, $parentId, $priority, $teamId)
    {
        $row = DB::table('ga_ticket_category')->where('Code', $code)->first();
        if ($row) {
            return (int) $row->ID;
        }

        return (int) DB::table('ga_ticket_category')->insertGetId([
            'Location'         => null,
            'Company_ID'       => null,
            'Parent_ID'        => $parentId,
            'Code'             => $code,
            'Name'             => $name,
            'Default_Priority' => $priority,
            'Default_Team_ID'  => $teamId,
            'Sla_Policy_ID'    => null,
            'Status'           => 'ACTIVE',
            'Created_By'       => 'seeder',
            'Created_At'       => $this->now,
        ]);
    }

    protected function seedItRouting(array $categoryIds, array $teamIds)
    {
        $rootRoutes = [
            'IT-HELP' => 'IT-SUPPORT',
            'IT-SYS'  => 'IT-INFRA',
            'IT-DEV'  => 'IT-SUPPORT',
            'IT-PREV' => 'IT-SUPPORT',
        ];

        $sort = 200;
        foreach ($rootRoutes as $categoryCode => $teamCode) {
            if (empty($categoryIds[$categoryCode]) || empty($teamIds[$teamCode])) {
                continue;
            }

            $exists = DB::table('ga_routing_rule')
                ->where('Category_ID', $categoryIds[$categoryCode])
                ->whereNull('Subcategory_ID')
                ->whereNull('Priority')
                ->exists();

            if (!$exists) {
                DB::table('ga_routing_rule')->insert([
                    'Location'       => null,
                    'Company_ID'     => null,
                    'Category_ID'    => $categoryIds[$categoryCode],
                    'Subcategory_ID' => null,
                    'Priority'       => null,
                    'Team_ID'        => $teamIds[$teamCode],
                    'Sort_Order'     => $sort,
                    'Is_Active'      => 1,
                    'Created_By'     => 'seeder',
                    'Created_At'     => $this->now,
                ]);
            }
            $sort++;
        }

        $childRoutes = [
            'IT-HELP-HW'    => ['IT-HELP', 'IT-SUPPORT'],
            'IT-HELP-SW'    => ['IT-HELP', 'IT-SUPPORT'],
            'IT-HELP-ACC'   => ['IT-HELP', 'IT-SUPPORT'],
            'IT-HELP-PRN'   => ['IT-HELP', 'IT-SUPPORT'],
            'IT-SYS-ERP'    => ['IT-SYS', 'IT-INFRA'],
            'IT-SYS-NET'    => ['IT-SYS', 'IT-INFRA'],
            'IT-SYS-SRV'    => ['IT-SYS', 'IT-INFRA'],
            'IT-DEV-LAPTOP' => ['IT-DEV', 'IT-SUPPORT'],
            'IT-DEV-ACC'    => ['IT-DEV', 'IT-SUPPORT'],
            'IT-DEV-LIC'    => ['IT-DEV', 'IT-SUPPORT'],
            'IT-PREV-MNT'   => ['IT-PREV', 'IT-SUPPORT'],
            'IT-PREV-BKP'   => ['IT-PREV', 'IT-SUPPORT'],
        ];

        $sort = 300;
        foreach ($childRoutes as $subCode => $cfg) {
            $parentCode = $cfg[0];
            $teamCode = $cfg[1];
            if (empty($categoryIds[$subCode]) || empty($teamIds[$teamCode])) {
                continue;
            }

            $exists = DB::table('ga_routing_rule')
                ->where('Subcategory_ID', $categoryIds[$subCode])
                ->exists();

            if (!$exists) {
                DB::table('ga_routing_rule')->insert([
                    'Location'       => null,
                    'Company_ID'     => null,
                    'Category_ID'    => $categoryIds[$parentCode] ?? $categoryIds[$subCode],
                    'Subcategory_ID' => $categoryIds[$subCode],
                    'Priority'       => null,
                    'Team_ID'        => $teamIds[$teamCode],
                    'Sort_Order'     => $sort,
                    'Is_Active'      => 1,
                    'Created_By'     => 'seeder',
                    'Created_At'     => $this->now,
                ]);
            }
            $sort++;
        }
    }

    protected function seedApprovalPolicies()
    {
        $this->upsertPolicy(
            'IT-DEVICE-REQUEST-MGR',
            'Approval Pengajuan Perangkat IT (Manager Departemen Pemohon)',
            'IT_DEVICE_REQUEST',
            null,
            null,
            [
                ['Stage_Order' => 1, 'Level_Number' => 3, 'Department' => null, 'Position_Code' => null, 'Label' => 'Manager Departemen Pemohon'],
            ]
        );

        $this->upsertPolicy(
            'GA-TICKET-DEPT-HEAD',
            'Approval Order Pekerjaan GA Fase 1 (Department Head Pemohon)',
            'GA_TICKET_DEPT_HEAD',
            null,
            null,
            [
                ['Stage_Order' => 1, 'Level_Number' => 3, 'Department' => null, 'Position_Code' => null, 'Label' => 'Department Head / Manager Pemohon'],
            ]
        );

        $this->upsertPolicy(
            'GA-TICKET-BUDGET-LOW',
            'Approval Biaya GA Ticketing < Rp 3 Juta (HCGA Manager)',
            'GA_TICKET_BUDGET',
            null,
            2999999.99,
            [
                ['Stage_Order' => 1, 'Level_Number' => 3, 'Department' => 'HCGA', 'Position_Code' => 'MHCGA', 'Label' => 'HCGA Manager'],
            ]
        );

        $this->upsertPolicy(
            'GA-TICKET-BUDGET-MID',
            'Approval Biaya GA Ticketing Rp 3 - 5 Juta (HCGA Mgr + FAT Mgr + VP FAT)',
            'GA_TICKET_BUDGET',
            3000000.00,
            5000000.00,
            [
                ['Stage_Order' => 1, 'Level_Number' => 3, 'Department' => 'HCGA', 'Position_Code' => 'MHCGA', 'Label' => 'HCGA Manager'],
                ['Stage_Order' => 2, 'Level_Number' => 3, 'Department' => 'FIN',  'Position_Code' => 'MFA',   'Label' => 'FAT Manager'],
                ['Stage_Order' => 3, 'Level_Number' => 2, 'Department' => 'FIN',  'Position_Code' => 'VPFAT', 'Label' => 'VP FAT'],
            ]
        );

        $this->upsertPolicy(
            'GA-TICKET-BUDGET-HIGH',
            'Approval Biaya GA Ticketing > Rp 5 Juta (HCGA Mgr + FAT Mgr + VP FAT + BOD)',
            'GA_TICKET_BUDGET',
            5000000.01,
            null,
            [
                ['Stage_Order' => 1, 'Level_Number' => 3, 'Department' => 'HCGA', 'Position_Code' => 'MHCGA', 'Label' => 'HCGA Manager'],
                ['Stage_Order' => 2, 'Level_Number' => 3, 'Department' => 'FIN',  'Position_Code' => 'MFA',   'Label' => 'FAT Manager'],
                ['Stage_Order' => 3, 'Level_Number' => 2, 'Department' => 'FIN',  'Position_Code' => 'VPFAT', 'Label' => 'VP FAT'],
                ['Stage_Order' => 4, 'Level_Number' => 1, 'Department' => 'DIR',  'Position_Code' => 'VPD',   'Label' => 'BOD / Vice President Director'],
            ]
        );
    }

    protected function upsertPolicy(
        string $code,
        string $name,
        string $requestType,
        ?float $minAmount,
        ?float $maxAmount,
        array $steps
    ) {
        $existing = DB::table('approval_policy')->where('Policy_Code', $code)->first();

        if ($existing) {
            DB::table('approval_policy')->where('ID', $existing->ID)->update([
                'Policy_Name'         => $name,
                'Request_Type'        => $requestType,
                'Min_Amount'          => $minAmount,
                'Max_Amount'          => $maxAmount,
                'Rejection_Mode'      => 'RETURN_TO_CREATOR',
                'On_No_Higher_Level'  => 'AUTO_APPROVE',
                'On_Vacancy'          => 'FALLBACK',
                'Allow_Self_Approval' => 0,
                'Allow_Delegation'    => 1,
                'Status'              => 'ACTIVE',
                'Updated_At'          => $this->now,
            ]);
            $policyId = (int) $existing->ID;
            DB::table('approval_policy_step')->where('Policy_ID', $policyId)->delete();
        } else {
            $policyId = (int) DB::table('approval_policy')->insertGetId([
                'Policy_Code'         => $code,
                'Policy_Name'         => $name,
                'Request_Type'        => $requestType,
                'Location'            => null,
                'Company_ID'          => null,
                'Department'          => null,
                'Min_Amount'          => $minAmount,
                'Max_Amount'          => $maxAmount,
                'Conditions'          => json_encode([]),
                'Rejection_Mode'      => 'RETURN_TO_CREATOR',
                'Rejection_Quorum'    => null,
                'On_No_Higher_Level'  => 'AUTO_APPROVE',
                'On_Vacancy'          => 'FALLBACK',
                'Allow_Self_Approval' => 0,
                'Allow_Delegation'    => 1,
                'Status'              => 'ACTIVE',
                'Version'             => 1,
                'Created_By'          => 'seeder',
                'Created_At'          => $this->now,
            ]);
        }

        foreach ($steps as $step) {
            DB::table('approval_policy_step')->insert([
                'Policy_ID'     => $policyId,
                'Stage_Order'   => $step['Stage_Order'],
                'Level_Number'  => $step['Level_Number'],
                'Position_ID'   => null,
                'Position_Code' => $step['Position_Code'],
                'Department'    => $step['Department'],
                'Approval_Mode' => 'ALL',
                'Quorum'        => null,
                'Is_Required'   => 1,
                'Label'         => $step['Label'],
                'Created_At'    => $this->now,
            ]);
        }

        $this->info("Upserted approval policy {$code}.");
    }

    protected function info(string $message)
    {
        if ($this->command) {
            $this->command->info($message);
        }
    }
}
