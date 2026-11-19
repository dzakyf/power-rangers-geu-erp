<?php

use App\Permission;
use App\Services\CNS\CnsPermissionService;
use App\Services\RbacService;
use App\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

/**
 * Align cns.* permission metadata with the live CNS menu ids and refresh the
 * Sub_Menu cache for users whose roles hold CNS permissions.
 *
 * The HRD role is intentionally left untouched; it is excluded from CNS
 * Master Kemasan access for now.
 */
class SyncCnsPermissionMenuIds extends Migration
{
    private const ACTIONS = ['view', 'create', 'edit', 'delete', 'approve'];

    public function up()
    {
        if (!Schema::hasTable('permissions') || !Schema::hasTable('menu_lists')) {
            return;
        }

        foreach (CnsPermissionService::allMenuSlugs() as $menuId => $slug) {
            if (!DB::table('menu_lists')->where('Menu_ID', $menuId)->exists()) {
                continue;
            }

            foreach (self::ACTIONS as $action) {
                $name = CnsPermissionService::permissionName($menuId, $action);
                $permission = Permission::firstOrCreate(
                    ['name' => $name, 'guard_name' => 'web'],
                    ['menu_id' => $menuId, 'action' => $action]
                );

                if ((string) $permission->menu_id !== (string) $menuId || (string) $permission->action !== $action) {
                    $permission->menu_id = $menuId;
                    $permission->action = $action;
                    $permission->save();
                }
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Refresh Sub_Menu only for users whose roles actually hold CNS permissions.
        $userIds = DB::table('model_has_roles as mhr')
            ->join('role_has_permissions as rhp', 'rhp.role_id', '=', 'mhr.role_id')
            ->join('permissions as p', 'p.id', '=', 'rhp.permission_id')
            ->where('mhr.model_type', 'App\User')
            ->where('p.name', 'like', 'cns.%')
            ->distinct()
            ->pluck('mhr.model_id')
            ->all();

        foreach (User::whereIn('id', $userIds)->get() as $user) {
            RbacService::rebuildSubMenu($user);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down()
    {
        // Data alignment only; no safe automatic rollback.
    }
}
