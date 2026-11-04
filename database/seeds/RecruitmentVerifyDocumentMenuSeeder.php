<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Role;
use App\Permission;
use App\User;
use App\Services\RbacService;

class RecruitmentVerifyDocumentMenuSeeder extends Seeder
{
    private const MENU_ID = 'SI01031';
    private const MENU_NAME = 'Verifikasi Dokumen';
    private const MENU_PATH = 'hrd-recruitment-verify-document-list';
    private const PARENT_MENU = 'SS00021'; // Recruitment
    private const POSITION = 4;
    private const URUTAN = 14; // tepat setelah New Recruitment (Urutan 13)
    private const MODULE = 'HRD';

    private const ACTIONS = ['view', 'create', 'edit', 'delete', 'approve'];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Menambahkan menu Verifikasi Dokumen ke menu_lists...');

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

        // 3. Berikan permission ke Role ADM dan HRD
        foreach (['ADM', 'HRD'] as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $existing = $role->permissions()->pluck('name')->flip();
            $missing = array_values(array_filter($permissionNames, function ($name) use ($existing) {
                return !$existing->has($name);
            }));

            if (!empty($missing)) {
                $role->givePermissionTo($missing);
                $this->command->info("Granted " . count($missing) . " permissions to role {$roleName}.");
            }
        }

        // 4. Update langsung Sub_Menu untuk user yang memiliki akses Recruitment
        // User yang memiliki New Recruitment (SI00090) atau Talent Pool (SI00091) atau admin
        $users = DB::table('users')
            ->where(function ($q) {
                $q->where('Sub_Menu', 'like', '%SI00090%')
                  ->orWhere('Sub_Menu', 'like', '%SI00091%')
                  ->orWhere('username', 'admin');
            })
            ->get();

        $updatedUsers = 0;
        foreach ($users as $user) {
            $subMenus = array_filter(explode(',', (string)$user->Sub_Menu));
            if (!in_array(self::MENU_ID, $subMenus)) {
                $subMenus[] = self::MENU_ID;
                DB::table('users')->where('id', $user->id)->update([
                    'Sub_Menu' => implode(',', array_unique($subMenus))
                ]);
                $updatedUsers++;
            }
        }
        $this->command->info("Updated Sub_Menu for {$updatedUsers} users with SI01031.");

        $this->command->info('RecruitmentVerifyDocumentMenuSeeder selesai.');
    }
}
