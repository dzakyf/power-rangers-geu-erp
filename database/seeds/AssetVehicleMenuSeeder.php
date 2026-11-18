<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Role;
use App\Permission;
use App\User;
use App\Services\RbacService;

/**
 * Menu & RBAC permissions for HCGA Asset Lists & Vehicle Management.
 * Idempotent: rows and permissions are upserted, role grants are additive.
 */
class AssetVehicleMenuSeeder extends Seeder
{
    private const ACTIONS = ['view', 'create', 'edit', 'delete', 'approve'];

    private const MENUS = [
        'SS00104' => ['Asset & Vehicle Management', '#', 3, 'SM00067', 12],
        'SI01026' => ['Asset Lists', 'hrd-asset-lists', 4, 'SS00104', 1],
        'SI01027' => ['Vehicle Management', 'hrd-vehicle-management-index', 4, 'SS00104', 2],
    ];

    public function run()
    {
        $this->command->info('Seeding HCGA Asset & Vehicle Management menus...');

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

        $permissionNames = [];
        foreach (array_keys(self::MENUS) as $menuId) {
            foreach (self::ACTIONS as $action) {
                $name = "menu.{$menuId}.{$action}";
                $permissionNames[] = $name;
                Permission::firstOrCreate([
                    'name'       => $name,
                    'guard_name' => 'web',
                ]);
            }
        }

        $targetRoles = Role::whereIn('name', [
            'ADM',
            'HRD',
            'Super Admin',
            'Administrator',
            'HCGA Manager',
            'HCGA Staff',
            'GA Staff',
            'GA Manager',
        ])->get();

        foreach ($targetRoles as $role) {
            $role->givePermissionTo($permissionNames);
        }

        $roleIds = $targetRoles->pluck('id');
        if ($roleIds->isNotEmpty()) {
            $userIds = DB::table('model_has_roles')->whereIn('role_id', $roleIds)->pluck('model_id')->unique();
            foreach ($userIds as $userId) {
                $user = User::find($userId);
                if ($user) {
                    RbacService::rebuildSubMenu($user);
                }
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $this->command->info('Asset & Vehicle Management menus & permissions seeded.');
    }
}
