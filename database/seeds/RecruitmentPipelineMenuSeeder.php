<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Role;
use App\Permission;
use App\User;
use App\Services\RbacService;

class RecruitmentPipelineMenuSeeder extends Seeder
{
    private const PARENT_MENU = 'SS00021'; // Recruitment
    private const MODULE = 'HRD';
    private const POSITION = 4;

    private const MENUS = [
        [
            'id'       => 'SI01033',
            'name'     => 'Technical Test',
            'path'     => 'hrd-recruitment-technical-test',
            'urutan'   => 19,
        ],
        [
            'id'       => 'SI01034',
            'name'     => 'Medical Check Up (MCU)',
            'path'     => 'hrd-recruitment-mcu',
            'urutan'   => 20,
        ],
        [
            'id'       => 'SI01035',
            'name'     => 'Offering Letter',
            'path'     => 'hrd-recruitment-offering',
            'urutan'   => 21,
        ],
    ];

    private const ACTIONS = ['view', 'create', 'edit', 'delete', 'approve'];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Memulai Seeding Menu Pipeline Recruitment (Technical Test, MCU, Offering Letter)...');

        $menuIds = [];

        foreach (self::MENUS as $menu) {
            $menuId = $menu['id'];
            $menuIds[] = $menuId;

            // 1. Insert / Update menu_lists
            DB::table('menu_lists')->updateOrInsert(
                ['Menu_ID' => $menuId],
                [
                    'Name'        => $menu['name'],
                    'Path_Loc'    => $menu['path'],
                    'Position'    => self::POSITION,
                    'Parent_Menu' => self::PARENT_MENU,
                    'Urutan'      => $menu['urutan'],
                    'Modules'     => self::MODULE,
                ]
            );
            $this->command->info("  -> Menu {$menu['name']} ({$menuId}) terdaftar.");

            // 2. Buat permissions Spatie
            $permissionNames = [];
            foreach (self::ACTIONS as $action) {
                $permName = "menu.{$menuId}.{$action}";
                Permission::firstOrCreate(
                    ['name' => $permName, 'guard_name' => 'web'],
                    ['menu_id' => $menuId, 'action' => $action]
                );
                $permissionNames[] = $permName;
            }

            // 3. Assign permissions ke role default
            $roles = ['Super Admin', 'Admin HRD', 'HCGA Manager'];
            foreach ($roles as $roleName) {
                $role = Role::where('name', $roleName)->first();
                if ($role) {
                    $role->givePermissionTo($permissionNames);
                }
            }
        }

        // 4. Update Sub_Menu pada users yang memiliki akses (ADM, HCGA, HRD)
        $this->appendSubMenusToUsers($menuIds);

        // 5. Sync RBAC
        try {
            app(RbacService::class)->syncAll();
            $this->command->info('  -> RbacService synced successfully.');
        } catch (\Throwable $e) {
            $this->command->warn('  -> RbacService sync warning: ' . $e->getMessage());
        }

        $this->command->info('Seeding Menu Pipeline Recruitment selesai!');
    }

    private function appendSubMenusToUsers(array $menuIds)
    {
        $users = User::where(function ($q) {
            $q->where('Priv_ID', 'ADM')
              ->orWhere('Department', 'HCGA')
              ->orWhere('Department', 'HRD');
        })->get();

        $updatedCount = 0;
        foreach ($users as $user) {
            $subMenus = array_filter(explode(',', $user->Sub_Menu ?? ''));
            $changed = false;

            foreach ($menuIds as $mId) {
                if (!in_array($mId, $subMenus)) {
                    $subMenus[] = $mId;
                    $changed = true;
                }
            }

            if ($changed) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['Sub_Menu' => implode(',', $subMenus)]);
                $updatedCount++;
            }
        }

        $this->command->info("  -> Berhasil mengupdate Sub_Menu untuk {$updatedCount} users.");
    }
}
