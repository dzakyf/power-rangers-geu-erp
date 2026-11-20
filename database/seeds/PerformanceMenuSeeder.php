<?php

use App\Permission;
use App\Role;
use App\Services\RbacService;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Performance Management menu entries (ordered 1..6 matching PERFORMANCE_WORKFLOW_GUIDE.md),
 * Spatie permissions (menu.{id}.{view,create,edit,delete,approve}), and role grants.
 *
 * Idempotent: rows and permissions are upserted, role grants are additive.
 */
class PerformanceMenuSeeder extends Seeder
{
    private const ACTIONS = ['view', 'create', 'edit', 'delete', 'approve'];

    private const MENUS = [
        'SI01038' => ['1. Periode & Dept PIC', 'hrd-performance-period-index', 4, 'SS00106', 1],
        'SI01040' => ['2. Master Soft Skill', 'hrd-performance-competency-index', 4, 'SS00106', 2],
        'SI01037' => ['3. KPI & OKR Goals', 'hrd-performance-kpi-index', 4, 'SS00106', 3],
        'SI01055' => ['4. Evaluasi Probation', 'hrd-performance-probation-index', 4, 'SS00106', 4],
        'SI01039' => ['5. Performance Review', 'hrd-performance-review-index', 4, 'SS00106', 5],
        'SI01041' => ['6. PIP Plan', 'hrd-performance-pip-index', 4, 'SS00106', 6],
    ];

    public function run()
    {
        $this->command->info('Seeding Performance Management menus & Spatie permissions...');

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
                Permission::firstOrCreate(
                    ['name' => $name, 'guard_name' => 'web'],
                    ['menu_id' => $menuId, 'action' => $action]
                );
                $permissionNames[] = $name;
            }
        }

        foreach (['ADM', 'HRD'] as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $existing = $role->permissions()->pluck('name')->flip();
            $missing = array_values(array_filter($permissionNames, function ($name) use ($existing) {
                return !$existing->has($name);
            }));

            if (!empty($missing)) {
                $role->givePermissionTo($missing);
                $this->command->info("Granted " . count($missing) . " Performance permissions to {$roleName}.");
            }
        }

        $this->command->info('Rebuilding Sub_Menu for ADM/HRD role holders...');
        $roleIds = Role::whereIn('name', ['ADM', 'HRD'])->pluck('id');
        $userIds = DB::table('model_has_roles')->whereIn('role_id', $roleIds)->pluck('model_id')->unique();
        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if ($user) {
                RbacService::rebuildSubMenu($user);
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('PerformanceMenuSeeder complete.');
    }
}
