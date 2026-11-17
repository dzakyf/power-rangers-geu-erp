<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\CNS\CnsPermissionService;
use App\Services\RbacService;
use App\User;

class FixCnsCrudAndAccessPermissions extends Migration
{
    private const ACTIONS = ['view', 'create', 'edit', 'delete', 'approve'];

    public function up()
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        $allMenuSlugs = CnsPermissionService::allMenuSlugs();
        $adminRoles = [];
        if (Schema::hasTable('roles')) {
            $adminRoles = DB::table('roles')
                ->whereIn('name', ['ADM', 'Admin', 'admin', 'Superadmin'])
                ->pluck('id')
                ->toArray();
        }

        foreach ($allMenuSlugs as $menuId => $slug) {
            foreach (self::ACTIONS as $action) {
                $permName = "cns.{$slug}.{$action}";

                $perm = DB::table('permissions')->where('name', $permName)->first();
                if (!$perm) {
                    $permId = DB::table('permissions')->insertGetId([
                        'name'       => $permName,
                        'guard_name' => 'web',
                        'menu_id'    => $menuId,
                        'action'     => $action,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $permId = $perm->id;
                    if ($perm->menu_id !== $menuId || $perm->action !== $action) {
                        DB::table('permissions')->where('id', $permId)->update([
                            'menu_id'    => $menuId,
                            'action'     => $action,
                            'updated_at' => now(),
                        ]);
                    }
                }

                // Grant all to admin roles
                if (Schema::hasTable('role_has_permissions')) {
                    foreach ($adminRoles as $roleId) {
                        $has = DB::table('role_has_permissions')
                            ->where('role_id', $roleId)
                            ->where('permission_id', $permId)
                            ->exists();
                        if (!$has) {
                            DB::table('role_has_permissions')->insert([
                                'role_id'       => $roleId,
                                'permission_id' => $permId,
                            ]);
                        }
                    }
                }
            }
        }

        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        }

        // Rebuild Sub_Menu for CNS users, admin, ADM
        if (Schema::hasTable('users')) {
            $users = User::where(function ($q) {
                $q->where('Main_Menu', 'CNS')
                    ->orWhere('username', 'admin')
                    ->orWhere('Priv_ID', 'ADM');
            })->get();

            foreach ($users as $user) {
                RbacService::rebuildSubMenu($user);
            }
        }
    }

    public function down()
    {
        // Reversible if needed
    }
}
