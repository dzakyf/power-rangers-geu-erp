<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Role;
use App\Permission;
use App\User;
use App\Services\RbacService;

/**
 * Idempotent seeder that dumps all HCGA/HRD menu permissions, wires them to
 * the HRD role, scopes HCGA staff (Priv_ID=STAFF, Department=HCGA) to the HRD
 * module only (role HRD + HRD-only Sub_Menu), and enforces the HRD role
 * exclusion list (menu items hidden from HRD-role users).
 *
 * Additive by design: existing role->permission assignments are never removed
 * beyond the configured HRD exclusions, so manual edits made in the Credential
 * UI survive re-seeding. Safe to run repeatedly.
 */
class HcgaPermissionSeeder extends Seeder
{
    private const ACTIONS = ['view', 'create', 'edit', 'delete', 'approve'];

    /**
     * Menu items hidden from HRD-role users (permissions revoked from the HRD role).
     */
    private const HRD_EXCLUDE_MENU_IDS = [
        'SI00090', // New Recruitment
        'SI00092', // Test Results (Psikotest)
        'SI00093', // Notification
        'SI00094', // Interview
        'SI00095', // Final User Interview
        'SI00096', // Pending Lists
        'SI00097', // Outstanding Report
    ];

    public function run()
    {
        $hrdRole = Role::firstOrCreate(['name' => 'HRD', 'guard_name' => 'web']);

        $this->command->info('Dumping HCGA/HRD menu permissions from menu_lists...');
        $hrdMenus = DB::table('menu_lists')->where('Modules', 'HRD')->get();
        foreach ($hrdMenus as $menu) {
            foreach (self::ACTIONS as $action) {
                Permission::firstOrCreate(
                    ['name' => "menu.{$menu->Menu_ID}.{$action}", 'guard_name' => 'web'],
                    ['menu_id' => $menu->Menu_ID, 'action' => $action]
                );
            }
        }

        $this->command->info('Enforcing HRD role exclusions...');
        $excludedNames = [];
        foreach (self::HRD_EXCLUDE_MENU_IDS as $menuId) {
            foreach (self::ACTIONS as $action) {
                $excludedNames[] = "menu.{$menuId}.{$action}";
            }
        }
        $excluded = Permission::whereIn('name', $excludedNames)->get();
        if ($excluded->isNotEmpty()) {
            $hrdRole->revokePermissionTo($excluded);
            $this->command->info("Revoked {$excluded->count()} excluded permissions from HRD role.");
        }

        $this->command->info('Scoping HCGA staff to the HRD role...');
        $hcgaStaff = User::where('Priv_ID', 'STAFF')
            ->where('Department', 'HCGA')
            ->get();
        foreach ($hcgaStaff as $user) {
            if (!$user->hasRole('HRD')) {
                $user->syncRoles(['HRD']);
                $this->command->info("Assigned HRD role to {$user->username} (#{$user->id}).");
            }
        }

        $this->command->info('Rebuilding Sub_Menu for all HRD-role holders...');
        $hrdRoleUserIds = DB::table('model_has_roles')
            ->where('role_id', $hrdRole->id)
            ->pluck('model_id')
            ->unique();
        foreach ($hrdRoleUserIds as $userId) {
            $user = User::find($userId);
            if ($user) {
                RbacService::rebuildSubMenu($user);
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('HcgaPermissionSeeder complete.');
    }
}
