<?php

use App\Permission;
use App\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HcgaTrainingMenuSeeder extends Seeder
{
    private const ACTIONS = ['view', 'create', 'edit', 'delete', 'approve'];

    private const PARENT_MENU_ID = 'SS00110';

    private const MENUS = [
        'SI01053' => ['TNA (Need Analysis)', 'hrd-tna-index', 4, 'SS00110', 1],
        'SI01054' => ['Module & Program', 'hrd-training-program-index', 4, 'SS00110', 2],
        'SI01055' => ['Jadwal Training', 'hrd-training-index', 4, 'SS00110', 3],
        'SI01056' => ['Evaluasi Training', 'hrd-training-evaluation-index', 4, 'SS00110', 4],
        'SI01057' => ['Summary & Sertifikat', 'hrd-training-summary-index', 4, 'SS00110', 5],
        'SI01058' => ['Coaching & Refreshment', 'hrd-training-coaching-index', 4, 'SS00110', 6],
    ];

    public function run()
    {
        // Parent Sub-Module Menu di bawah SM00020 (Human Resources)
        DB::table('menu_lists')->updateOrInsert(
            ['Menu_ID' => self::PARENT_MENU_ID],
            [
                'Name' => 'Training & TNA',
                'Path_Loc' => '#',
                'Position' => 3,
                'Parent_Menu' => 'SM00020',
                'Urutan' => 25,
                'Modules' => 'HRD',
            ]
        );

        foreach (self::MENUS as $menuId => $menu) {
            DB::table('menu_lists')->updateOrInsert(
                ['Menu_ID' => $menuId],
                [
                    'Name' => $menu[0],
                    'Path_Loc' => $menu[1],
                    'Position' => $menu[2],
                    'Parent_Menu' => $menu[3],
                    'Urutan' => $menu[4],
                    'Modules' => 'HRD',
                ]
            );
        }

        $allMenuIds = array_merge([self::PARENT_MENU_ID], array_keys(self::MENUS));
        $permissionNames = [];

        foreach ($allMenuIds as $menuId) {
            foreach (self::ACTIONS as $action) {
                $name = "menu.{$menuId}.{$action}";
                Permission::firstOrCreate([
                    'name' => $name,
                    'guard_name' => 'web',
                ]);
                $permissionNames[] = $name;
            }
        }

        // Berikan akses ke role ADM / HCGA jika tersedia
        $roles = Role::whereIn('name', ['ADM', 'SUPERADMIN', 'HCGA', 'HRD'])->get();
        foreach ($roles as $role) {
            $role->givePermissionTo($permissionNames);
        }

        $this->command->info('HcgaTrainingMenuSeeder: Menu SS00110 & SI01053..SI01058 berhasil di-seed.');
    }
}
