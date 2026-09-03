<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Role;
use App\Permission;
use App\User;
use App\Services\RbacService;

/**
 * Idempotent RBAC seeder.
 *
 * Safe to run repeatedly: existing roles and permissions are kept, only gaps
 * are filled. Existing manual role->permission assignments (e.g. MITRA's)
 * are never removed - ADM is extended additively via givePermissionTo().
 */
class RbacSeeder extends Seeder
{
    private const ACTIONS = ['view', 'create', 'edit', 'delete', 'approve'];

    /**
     * Modules managed by the credential-role editor and granted to ADM.
     */
    private const ADM_MODULES = ['FINANCE', 'HRD', 'MITRA', 'CNS', 'CANDIDATE', 'CREDENTIAL'];

    public function run()
    {
        $this->command->info('Creating roles from users.Priv_ID...');
        $privIds = DB::table('users')->whereNotNull('Priv_ID')->distinct()->pluck('Priv_ID');
        foreach ($privIds as $privId) {
            Role::firstOrCreate(
                ['name' => $privId, 'guard_name' => 'web']
            );
        }

        $this->command->info('Gap-filling menu permissions from menu_lists...');
        $menus = DB::table('menu_lists')->get();
        foreach ($menus as $menu) {
            foreach (self::ACTIONS as $action) {
                Permission::firstOrCreate(
                    ['name' => "menu.{$menu->Menu_ID}.{$action}", 'guard_name' => 'web'],
                    ['menu_id' => $menu->Menu_ID, 'action' => $action]
                );
            }
        }

        $this->command->info('Ensuring master.credential.role.edit...');
        Permission::firstOrCreate(
            ['name' => 'master.credential.role.edit', 'guard_name' => 'web'],
            ['menu_id' => 'master', 'action' => 'edit']
        );

        $masterPerm = Permission::where('name', 'master.credential.role.edit')->first();

        $this->command->info('Additively granting ADM its module set...');
        $admRole = Role::where('name', 'ADM')->first();
        if ($admRole) {
            $admMenuIds = DB::table('menu_lists')
                ->whereIn('Modules', self::ADM_MODULES)
                ->pluck('Menu_ID');

            $existingNames = $admRole->permissions()->pluck('name')->flip();

            // Additive only: givePermissionTo never detaches anything already
            // on the role, so manual extras survive the re-seed.
            $missing = Permission::whereIn('menu_id', $admMenuIds)->get()
                ->merge([$masterPerm])
                ->filter(function ($perm) use ($existingNames) {
                    return !$existingNames->has($perm->name);
                });

            if ($missing->isNotEmpty()) {
                $admRole->givePermissionTo($missing);
                $this->command->info("Added {$missing->count()} permissions to ADM.");
            } else {
                $this->command->info('ADM already up to date.');
            }
        }

        $this->command->info('Assigning users to roles by Priv_ID...');
        $users = User::whereNotNull('Priv_ID')->get();
        foreach ($users as $user) {
            // HCGA staff are scoped to the HRD module via the HRD role
            // (see HcgaPermissionSeeder); keep that exception stable across re-seeds.
            if ($user->Priv_ID === 'STAFF' && $user->Department === 'HCGA') {
                $role = Role::where('name', 'HRD')->first();
                if ($role && !$user->hasRole('HRD')) {
                    $user->assignRole($role);
                }
                continue;
            }

            $role = Role::where('name', $user->Priv_ID)->first();
            if ($role && !$user->hasRole($role->name)) {
                $user->assignRole($role);
            }
        }

        $this->command->info('Rebuilding Sub_Menu for all users holding roles...');
        $roleIds = Role::pluck('id');
        foreach (DB::table('model_has_roles')->whereIn('role_id', $roleIds)->pluck('model_id')->unique() as $userId) {
            $user = User::find($userId);
            if ($user) {
                RbacService::rebuildSubMenu($user);
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('RbacSeeder complete.');
    }
}
