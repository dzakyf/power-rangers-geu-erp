<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Role;
use App\Permission;
use App\Services\RbacService;

class GrantMitraModulePermissions extends Command
{
    protected $signature = 'mitra:grant-module-permissions';
    protected $description = 'Grant all Business Partner (MITRA module) menu permissions to the MITRA role';

    public function handle()
    {
        $role = Role::where('name', 'MITRA')->first();

        if (!$role) {
            $this->error('MITRA role not found.');
            return 1;
        }

        $this->info("Found MITRA role (id={$role->id})");

        $menuIds = DB::table('menu_lists')
            ->where('Modules', 'MITRA')
            ->pluck('Menu_ID');

        $this->info("Found {$menuIds->count()} MITRA module menus");

        if ($menuIds->isEmpty()) {
            $this->warn('No MITRA menus found. Nothing to grant.');
            return 0;
        }

        $actions = ['view', 'create', 'edit', 'delete', 'approve'];
        $permissionNames = [];

        foreach ($menuIds as $menuId) {
            foreach ($actions as $action) {
                $permissionNames[] = "menu.{$menuId}.{$action}";
            }
        }

        $this->info("Building " . count($permissionNames) . " permission names");

        $permissions = Permission::whereIn('name', $permissionNames)->get();

        $this->info("Found " . $permissions->count() . " existing permission records");

        $role->syncPermissions($permissions);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $users = $role->users;
        $this->info("Rebuilding Sub_Menu for {$users->count()} MITRA users...");

        foreach ($users as $user) {
            RbacService::rebuildSubMenu($user);
            $this->line("  Updated user #{$user->id} {$user->username}");
        }

        $this->info('Done! MITRA role now has all Business Partner module permissions.');
        return 0;
    }
}
