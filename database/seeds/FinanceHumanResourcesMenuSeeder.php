<?php

use App\Permission;
use App\Role;
use App\Services\RbacService;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder menu Finance Approval & Voucher (Human Resources & GA) di bawah modul FINANCE (MS00003).
 * Idempoten: aman dijalankan ulang setelah MenuListsSeeder.
 */
class FinanceHumanResourcesMenuSeeder extends Seeder
{
    private const ACTIONS = ['view', 'create', 'edit', 'delete', 'approve'];

    private const MENUS = [
        'SM00070' => ['Human Resources & GA', '#', 2, 'MS00003', 50],
        'SS00110' => ['Approval & Voucher', '#', 3, 'SM00070', 1],
        'SI01101' => ['Finance Approval & Voucher', 'finance-hr-approval-index', 4, 'SS00110', 1],
    ];

    public function run()
    {
        if ($this->command) {
            $this->command->info('Seeding Finance HR/GA Approval menus...');
        }

        foreach (self::MENUS as $menuId => $menu) {
            DB::table('menu_lists')->updateOrInsert(
                ['Menu_ID' => $menuId],
                [
                    'Name'        => $menu[0],
                    'Path_Loc'    => $menu[1],
                    'Position'    => $menu[2],
                    'Parent_Menu' => $menu[3],
                    'Urutan'      => $menu[4],
                    'Modules'     => 'FINANCE',
                ]
            );
        }

        $permissionNames = [];
        foreach (array_keys(self::MENUS) as $menuId) {
            foreach (self::ACTIONS as $action) {
                $name = "menu.{$menuId}.{$action}";
                Permission::firstOrCreate(
                    ['name' => $name, 'guard_name' => 'web'],
                    ['menu_id' => $menuId, 'action' => $action]
                );
                $permissionNames[] = $name;
            }
        }

        foreach (['ADM', 'FINANCE'] as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $existing = $role->permissions()->pluck('name')->flip();
            $missing = array_values(array_filter($permissionNames, function ($name) use ($existing) {
                return !$existing->has($name);
            }));

            if (!empty($missing)) {
                $role->givePermissionTo($missing);
            }
        }

        $roleIds = Role::whereIn('name', ['ADM', 'FINANCE'])->pluck('id');
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
    }
}
