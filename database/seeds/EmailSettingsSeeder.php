<?php

use App\Permission;
use App\Role;
use App\Services\RbacService;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EmailSettingsSeeder extends Seeder
{
    private const ACTIONS = ['view', 'create', 'edit', 'delete', 'approve'];
    private const MENU_IDS = ['SS00101', 'SI01008'];

    public function run()
    {
        $this->seedMenus();
        $this->seedPermissions();
        $this->seedSettings();
        $this->rebuildMenus();

        $this->command->info('EmailSettingsSeeder complete.');
    }

    private function seedMenus()
    {
        $this->upsertMenu([
            'Menu_ID' => 'SS00101',
            'Name' => 'Email Settings',
            'Path_Loc' => '#',
            'Position' => 3,
            'Parent_Menu' => 'SM00067',
            'Urutan' => 9,
            'Modules' => 'HRD',
        ]);

        $this->upsertMenu([
            'Menu_ID' => 'SI01008',
            'Name' => 'SMTP Configuration',
            'Path_Loc' => 'hrd-email-settings-index',
            'Position' => 4,
            'Parent_Menu' => 'SS00101',
            'Urutan' => 1,
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

    private function seedSettings()
    {
        if (!Schema::hasTable('email_settings')) {
            return;
        }

        if (DB::table('email_settings')->exists()) {
            return;
        }

        DB::table('email_settings')->insert([
            'Is_Enabled' => 0,
            'Smtp_Host' => null,
            'Smtp_Port' => 587,
            'Smtp_Encryption' => 'tls',
            'Smtp_Username' => null,
            'Smtp_Password' => null,
            'Mail_From_Address' => null,
            'Mail_From_Name' => 'ERP-ALERTER',
            'Created_By' => 'system',
            'Updated_By' => 'system',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
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
