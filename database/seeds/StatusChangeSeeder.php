<?php

use App\Permission;
use App\Role;
use App\Services\RbacService;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Menu & permission untuk Form Perubahan Status Karyawan.
 *
 * Satu form dipakai bersama oleh tiga menu:
 *   SI00102 Mutation          -> hrd-status-change-index?jenis=MUTATION
 *   SI00103 Promotion         -> hrd-status-change-index?jenis=PROMOTION
 *   SI00214 Perubahan Status  -> hrd-status-change-index (semua jenis)
 */
class StatusChangeSeeder extends Seeder
{
    private const ACTIONS = ['view', 'create', 'edit', 'delete', 'approve'];
    private const MENU_IDS = ['SI00102', 'SI00103', 'SI00214'];

    public function run()
    {
        $this->seedMenus();
        $this->seedPermissions();
        $this->rebuildMenus();

        $this->command->info('StatusChangeSeeder complete.');
    }

    private function seedMenus()
    {
        $this->upsertMenu([
            'Menu_ID' => 'SI00102',
            'Name' => 'Mutation',
            'Path_Loc' => 'hrd-status-change-index?jenis=MUTATION',
            'Position' => 4,
            'Parent_Menu' => 'SS00023',
            'Urutan' => 29,
            'Modules' => 'HRD',
        ]);

        $this->upsertMenu([
            'Menu_ID' => 'SI00103',
            'Name' => 'Promotion',
            'Path_Loc' => 'hrd-status-change-index?jenis=PROMOTION',
            'Position' => 4,
            'Parent_Menu' => 'SS00023',
            'Urutan' => 30,
            'Modules' => 'HRD',
        ]);

        $this->upsertMenu([
            'Menu_ID' => 'SI00214',
            'Name' => 'Perubahan Status',
            'Path_Loc' => 'hrd-status-change-index',
            'Position' => 4,
            'Parent_Menu' => 'SS00023',
            'Urutan' => 31,
            'Modules' => 'HRD',
        ]);
    }

    private function upsertMenu(array $menu)
    {
        $menuId = $menu['Menu_ID'];
        unset($menu['Menu_ID']);

        DB::table('menu_lists')->updateOrInsert(
            ['Menu_ID' => $menuId],
            $menu
        );
    }

    private function seedPermissions()
    {
        foreach (self::MENU_IDS as $menuId) {
            foreach (self::ACTIONS as $action) {
                Permission::firstOrCreate(
                    ['name' => "menu.{$menuId}.{$action}", 'guard_name' => 'web'],
                    ['menu_id' => $menuId, 'action' => $action]
                );
            }
        }

        $permissions = Permission::whereIn('menu_id', self::MENU_IDS)->get();

        foreach (['ADM', 'HRD', 'HCGA'] as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->givePermissionTo($permissions);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    private function rebuildMenus()
    {
        $roleIds = Role::whereIn('name', ['ADM', 'HRD', 'HCGA'])->pluck('id');
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
