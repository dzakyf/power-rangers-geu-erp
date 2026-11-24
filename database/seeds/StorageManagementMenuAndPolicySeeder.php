<?php

use App\Permission;
use App\Role;
use App\Services\RbacService;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder menu Storage Management (Regulasi & Informasi), Spatie permissions,
 * policy approval Manager HCGA (STORAGE_DOCUMENT), serta konfigurasi default.
 *
 * Idempoten: aman dijalankan berulang kali.
 */
class StorageManagementMenuAndPolicySeeder extends Seeder
{
    private const ACTIONS = ['view', 'create', 'edit', 'delete', 'approve'];

    private const MENUS = [
        'SS00155' => ['Storage Management', '#', 3, 'SM00067', 14],
        'SI01162' => ['Regulasi', 'hrd-storage-regulation-index', 4, 'SS00155', 1],
        'SI01163' => ['Informasi', 'hrd-storage-information-index', 4, 'SS00155', 2],
    ];

    /** @var string */
    private $now;

    public function run()
    {
        $this->now = now();
        $this->info('Seeding Storage Management menus, permissions, approval policy, and config_system...');

        $this->seedMenusAndPermissions();
        $this->seedApprovalPolicy();
        $this->seedConfigSystem();

        $this->info('StorageManagementMenuAndPolicySeeder complete.');
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

        $adminRoles = Role::where('guard_name', 'web')
            ->whereIn('name', ['ADM', 'HRD', 'HCGA'])
            ->get();

        foreach ($adminRoles as $role) {
            $existing = $role->permissions()->pluck('name')->all();
            $missing = array_diff($allPermissionNames, $existing);
            if (!empty($missing)) {
                $role->givePermissionTo($missing);
            }
        }

        $roleIds = $adminRoles->pluck('id')->unique();
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
    }

    protected function seedApprovalPolicy()
    {
        $code = 'STORAGE-DOC-MHCGA';
        $existing = DB::table('approval_policy')->where('Policy_Code', $code)->first();

        if ($existing) {
            DB::table('approval_policy')->where('ID', $existing->ID)->update([
                'Policy_Name'         => 'Approval Publikasi Dokumen Storage Management (HCGA Manager)',
                'Request_Type'        => 'STORAGE_DOCUMENT',
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
                'Policy_Name'         => 'Approval Publikasi Dokumen Storage Management (HCGA Manager)',
                'Request_Type'        => 'STORAGE_DOCUMENT',
                'Location'            => null,
                'Company_ID'          => null,
                'Department'          => null,
                'Min_Amount'          => null,
                'Max_Amount'          => null,
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

        DB::table('approval_policy_step')->insert([
            'Policy_ID'     => $policyId,
            'Stage_Order'   => 1,
            'Level_Number'  => 3,
            'Position_ID'   => null,
            'Position_Code' => 'MHCGA',
            'Department'    => 'HCGA',
            'Approval_Mode' => 'ALL',
            'Quorum'        => null,
            'Is_Required'   => 1,
            'Label'         => 'HCGA Manager',
            'Created_At'    => $this->now,
        ]);
    }

    protected function seedConfigSystem()
    {
        // config_system.Value is decimal: 0 = DIRECT, 1 = OPTIONAL, 2 = REQUIRED
        $configs = [
            'STORAGE_APPROVAL_MODE_REGULATION'  => 1,
            'STORAGE_APPROVAL_MODE_INFORMATION' => 0,
        ];

        foreach ($configs as $constraint => $value) {
            DB::table('config_system')->updateOrInsert(
                ['Constraint' => $constraint],
                ['Value' => $value]
            );
        }
    }

    protected function info(string $message)
    {
        if ($this->command) {
            $this->command->info($message);
        }
    }
}
