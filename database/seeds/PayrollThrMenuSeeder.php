<?php

use App\Permission;
use App\Role;
use App\Services\RbacService;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder Menu & Permissions untuk halaman Payroll THR Approval (/payroll-thr-approval).
 * Idempotent: dapat dijalankan berulang tanpa duplikasi.
 */
class PayrollThrMenuSeeder extends Seeder
{
    private const MENU_ID = 'SI01158';
    private const ACTIONS = ['view', 'create', 'edit', 'delete', 'approve'];

    public function run()
    {
        DB::table('menu_lists')->updateOrInsert(
            ['Menu_ID' => self::MENU_ID],
            [
                'Name'        => 'Payroll THR Approval',
                'Path_Loc'    => 'payroll-thr-approval',
                'Position'    => 4,
                'Parent_Menu' => 'SS00029',
                'Urutan'      => 11,
                'Modules'     => 'HRD',
            ]
        );

        $permissionNames = [];
        foreach (self::ACTIONS as $action) {
            $name = 'menu.' . self::MENU_ID . '.' . $action;
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['menu_id' => self::MENU_ID, 'action' => $action]
            );
            $permissionNames[] = $name;
        }

        // Berikan akses ke ADM serta role yang sudah memiliki akses Payroll THR (SI01054) atau Payroll Approval (SI00122)
        $targetRoleNames = DB::table('role_has_permissions as rhp')
            ->join('permissions as p', 'p.id', '=', 'rhp.permission_id')
            ->join('roles as r', 'r.id', '=', 'rhp.role_id')
            ->whereIn('p.name', ['menu.SI01054.view', 'menu.SI00122.approve'])
            ->pluck('r.name')
            ->unique()
            ->all();

        if (!in_array('ADM', $targetRoleNames, true)) {
            $targetRoleNames[] = 'ADM';
        }

        $touchedRoleIds = [];
        foreach ($targetRoleNames as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $existing = $role->permissions()->pluck('name')->flip();
            $missing = array_values(array_filter($permissionNames, function ($name) use ($existing) {
                return !$existing->has($name);
            }));

            if (!empty($missing)) {
                $role->givePermissionTo($missing);
            }
            $touchedRoleIds[] = $role->id;
        }

        if (!empty($touchedRoleIds) && class_exists(RbacService::class)) {
            $userIds = DB::table('model_has_roles')
                ->whereIn('role_id', $touchedRoleIds)
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

        if ($this->command) {
            $this->command->info('PayrollThrMenuSeeder complete (SI01158 -> payroll-thr-approval).');
        }
    }
}
