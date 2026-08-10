<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Role;
use App\Permission;
use App\User;
use App\Services\RbacService;

class RbacSeeder extends Seeder
{
    public function run()
    {
        $actions = ['view', 'create', 'edit', 'delete', 'approve'];

        $this->command->info('Creating roles...');
        $privIds = DB::table('users')->whereNotNull('Priv_ID')->distinct()->pluck('Priv_ID');
        $roleIds = [];
        foreach ($privIds as $privId) {
            $role = Role::firstOrCreate(
                ['name' => $privId, 'guard_name' => 'web'],
                ['name' => $privId, 'guard_name' => 'web']
            );
            $roleIds[$privId] = $role->id;
        }

        $this->command->info('Creating permissions from menu_lists...');
        $menus = DB::table('menu_lists')->get();
        foreach ($menus as $menu) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(
                    ['name' => "menu.{$menu->Menu_ID}.{$action}", 'guard_name' => 'web'],
                    ['menu_id' => $menu->Menu_ID, 'action' => $action]
                );
            }
        }

        $this->command->info('Creating master.credential.role.edit permission...');
        Permission::firstOrCreate(
            ['name' => 'master.credential.role.edit', 'guard_name' => 'web'],
            ['menu_id' => 'master', 'action' => 'edit']
        );

        $this->command->info('Assigning permissions to ADM role (modules: FINANCE, HRD, MITRA, CNS)...');
        $admRole = Role::where('name', 'ADM')->first();
        if ($admRole) {
            $admModules = ['FINANCE', 'HRD', 'MITRA', 'CNS', 'CANDIDATE', 'CREDENTIAL'];
            $admMenuIds = DB::table('menu_lists')
                ->whereIn('Modules', $admModules)
                ->pluck('Menu_ID');

            $admPermissions = Permission::whereIn('menu_id', $admMenuIds)->get();
            $admRole->syncPermissions($admPermissions);

            $masterPerm = Permission::where('name', 'master.credential.role.edit')->first();
            if ($masterPerm) {
                $admRole->givePermissionTo($masterPerm);
            }

            $this->command->info('ADM now has ' . $admPermissions->count() . ' module permissions + master.');
        }

        $this->command->info('Assigning users to roles...');
        $users = User::whereNotNull('Priv_ID')->get();
        foreach ($users as $user) {
            $role = Role::where('name', $user->Priv_ID)->first();
            if ($role && !$user->hasRole($role->name)) {
                $user->assignRole($role);
            }
        }

        $this->command->info('Rebuilding Sub_Menu for ADM users...');
        $admUsers = User::role('ADM')->get();
        foreach ($admUsers as $user) {
            RbacService::rebuildSubMenu($user);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('RbacSeeder complete.');
    }
}
