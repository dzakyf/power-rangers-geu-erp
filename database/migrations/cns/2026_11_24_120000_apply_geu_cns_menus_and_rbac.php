<?php

use App\Permission;
use App\Services\CNS\CnsPermissionService;
use App\Services\RbacService;
use App\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

class ApplyGeuCnsMenusAndRbac extends Migration
{
    private const ACTIONS = ['view', 'create', 'edit', 'delete', 'approve'];

    public function up()
    {
        if (!Schema::hasTable('menu_lists')) {
            return;
        }

        $rows = require database_path('seeds/data/menu_lists_cns.php');

        foreach ($rows as $menuId => [$name, $path, $module, $position, $parent, $urutan]) {
            $existing = DB::table('menu_lists')->where('Menu_ID', $menuId)->first();

            if ($existing && $existing->Modules !== 'CNS' && $existing->Modules !== '') {
                continue;
            }

            DB::table('menu_lists')->updateOrInsert(
                ['Menu_ID' => $menuId],
                [
                    'Name'        => $name,
                    'Path_Loc'    => $path,
                    'Modules'     => $module,
                    'Position'    => $position,
                    'Parent_Menu' => $parent,
                    'Urutan'      => $urutan,
                ]
            );
        }

        $staleMenuIds = DB::table('menu_lists')
            ->where('Modules', 'CNS')
            ->whereNotIn('Menu_ID', array_keys($rows))
            ->pluck('Menu_ID')
            ->all();

        if (!empty($staleMenuIds)) {
            DB::table('menu_lists')
                ->whereIn('Menu_ID', $staleMenuIds)
                ->where('Modules', 'CNS')
                ->delete();
        }

        if (Schema::hasTable('users')) {
            $users = DB::table('users')
                ->whereNotNull('Sub_Menu')
                ->get(['id', 'Sub_Menu']);

            foreach ($users as $user) {
                $subMenus = array_values(array_filter(explode(',', (string) $user->Sub_Menu)));
                $filtered = array_values(array_filter($subMenus, function ($menuId) use ($staleMenuIds) {
                    return !in_array(trim($menuId), $staleMenuIds, true);
                }));

                if ($filtered !== $subMenus) {
                    DB::table('users')->where('id', $user->id)->update([
                        'Sub_Menu' => implode(',', $filtered),
                    ]);
                }
            }
        }

        if (Schema::hasTable('permissions')) {
            foreach (CnsPermissionService::allMenuSlugs() as $menuId => $slug) {
                foreach (self::ACTIONS as $action) {
                    $name = CnsPermissionService::permissionName($menuId, $action);
                    $permission = Permission::firstOrCreate(
                        ['name' => $name, 'guard_name' => 'web'],
                        ['menu_id' => $menuId, 'action' => $action]
                    );

                    if ((string) $permission->menu_id !== (string) $menuId
                        || (string) $permission->action !== $action) {
                        $permission->menu_id = $menuId;
                        $permission->action = $action;
                        $permission->save();
                    }
                }
            }

            $orphanPermissionIds = DB::table('permissions as p')
                ->leftJoin('menu_lists as m', 'm.Menu_ID', '=', 'p.menu_id')
                ->where('p.name', 'like', 'cns.%')
                ->whereNotNull('p.menu_id')
                ->whereNull('m.ID')
                ->pluck('p.id')
                ->all();

            if (!empty($orphanPermissionIds)) {
                if (Schema::hasTable('role_has_permissions')) {
                    DB::table('role_has_permissions')
                        ->whereIn('permission_id', $orphanPermissionIds)
                        ->delete();
                }

                DB::table('permissions')->whereIn('id', $orphanPermissionIds)->delete();
            }
        }

        if (Schema::hasTable('permissions') && Schema::hasTable('model_has_roles')) {
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
        }

        if (class_exists(PermissionRegistrar::class)) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }

    public function down()
    {
        // Forward-only reconciliation. CNS RBAC/menu state is managed by the
        // hybrid geu snapshot and is not automatically reversible.
    }
}
