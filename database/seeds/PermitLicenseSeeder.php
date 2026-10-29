<?php

use App\Permission;
use App\Role;
use App\Services\RbacService;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PermitLicenseSeeder extends Seeder
{
    private const ACTIONS = ['view', 'create', 'edit', 'delete', 'approve'];
    private const MENU_IDS = ['SS00100', 'SI01007'];

    public function run()
    {
        $this->seedCategories();
        $this->seedMenus();
        $this->seedPermissions();
        $this->seedMailSettings();
        $this->rebuildMenus();

        $this->command->info('PermitLicenseSeeder complete.');
    }

    private function seedCategories()
    {
        $categories = [
            ['Code' => '001', 'Name' => 'Permit & license', 'Sort_Order' => 1],
            ['Code' => '002', 'Name' => 'Sewa menyewa', 'Sort_Order' => 2],
            ['Code' => '003', 'Name' => 'Sewa menyewa - Office', 'Sort_Order' => 3],
        ];

        foreach ($categories as $category) {
            $exists = DB::table('permit_license_categories')->where('Code', $category['Code'])->exists();
            if (!$exists) {
                DB::table('permit_license_categories')->insert([
                    'Code' => $category['Code'],
                    'Name' => $category['Name'],
                    'Sort_Order' => $category['Sort_Order'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function seedMenus()
    {
        $this->upsertMenu([
            'Menu_ID' => 'SS00100',
            'Name' => 'Permit & License',
            'Path_Loc' => '#',
            'Position' => 3,
            'Parent_Menu' => 'SM00067',
            'Urutan' => 7,
            'Modules' => 'HRD',
        ]);

        $this->upsertMenu([
            'Menu_ID' => 'SI01007',
            'Name' => 'Permit & License',
            'Path_Loc' => 'hrd-permit-license-index',
            'Position' => 4,
            'Parent_Menu' => 'SS00100',
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

    private function seedMailSettings()
    {
        if (!Schema::hasTable('permit_license_mail_settings')) {
            return;
        }

        if (DB::table('permit_license_mail_settings')->exists()) {
            return;
        }

        DB::table('permit_license_mail_settings')->insert([
            'Subject_Template' => '[Pengingat] Masa Berlaku {permit_name}',
            'Intro_Text' => 'Berikut kami informasikan perizinan yang mendekati masa berlaku. Mohon segera ditindaklanjuti sebelum tanggal berakhir.',
            'Footer_Text' => 'Email ini dikirim otomatis oleh ERP GEU. Mohon tidak membalas email ini.',
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