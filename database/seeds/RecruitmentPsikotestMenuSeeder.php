<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Role;
use App\Permission;
use App\User;
use App\Services\RbacService;

class RecruitmentPsikotestMenuSeeder extends Seeder
{
    private const MENU_ID = 'SI01032';
    private const MENU_NAME = 'Psikotest';
    private const MENU_PATH = 'hrd-recruitment-psikotest';
    private const PARENT_MENU = 'SS00021'; // Recruitment
    private const POSITION = 4;
    private const URUTAN = 15; // Berada setelah Verifikasi Dokumen (Urutan 14)
    private const MODULE = 'HRD';

    private const ACTIONS = ['view', 'create', 'edit', 'delete', 'approve'];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Menambahkan menu Psikotest ke menu_lists...');

        // 1. Insert / Update menu_lists
        DB::table('menu_lists')->updateOrInsert(
            ['Menu_ID' => self::MENU_ID],
            [
                'Name'        => self::MENU_NAME,
                'Path_Loc'    => self::MENU_PATH,
                'Position'    => self::POSITION,
                'Parent_Menu' => self::PARENT_MENU,
                'Urutan'      => self::URUTAN,
                'Modules'     => self::MODULE,
            ]
        );

        // 2. Buat permissions Spatie
        $permissionNames = [];
        foreach (self::ACTIONS as $action) {
            $name = "menu." . self::MENU_ID . ".{$action}";
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['menu_id' => self::MENU_ID, 'action' => $action]
            );
            $permissionNames[] = $name;
        }

        // 3. Assign permissions ke Role
        $targetRoles = ['ADM', 'HRD', 'HCGA'];
        foreach ($targetRoles as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
            if ($role) {
                $role->givePermissionTo($permissionNames);
                $this->command->info("  -> Permissions di-assign ke role {$roleName}");
            }
        }

        // 4. Update Sub_Menu pada users yang memiliki akses
        $this->appendSubMenuToUsers(self::MENU_ID);

        // 5. Sync RBAC
        try {
            app(RbacService::class)->syncAll();
            $this->command->info('  -> RbacService synced successfully.');
        } catch (\Throwable $e) {
            $this->command->warn('  -> RbacService sync warning: ' . $e->getMessage());
        }

        $this->command->info('Seeding Menu Psikotest selesai!');
    }

    private function appendSubMenuToUsers($menuId)
    {
        $users = User::where(function ($q) {
            $q->where('Priv_ID', 'ADM')
              ->orWhere('Department', 'HCGA')
              ->orWhere('Department', 'HRD');
        })->get();

        foreach ($users as $user) {
            $subMenus = array_filter(explode(',', $user->Sub_Menu ?? ''));
            if (!in_array($menuId, $subMenus)) {
                $subMenus[] = $menuId;
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['Sub_Menu' => implode(',', $subMenus)]);
            }
        }
    }
}

