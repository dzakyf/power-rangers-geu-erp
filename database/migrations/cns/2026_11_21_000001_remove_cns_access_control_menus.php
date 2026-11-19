<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RemoveCnsAccessControlMenus extends Migration
{
    private const ACCESS_MENU_IDS = ['SM00066', 'SS00095', 'SI00347', 'SI00348'];

    private const CNS_CHILD_MENU_IDS = ['SS00095', 'SI00347', 'SI00348'];

    public function up()
    {
        if (Schema::hasTable('menu_lists')) {
            DB::table('menu_lists')
                ->whereIn('Menu_ID', self::ACCESS_MENU_IDS)
                ->where('Modules', 'CNS')
                ->delete();
        }

        if (Schema::hasTable('users')) {
            $users = DB::table('users')
                ->whereNotNull('Sub_Menu')
                ->get(['id', 'Sub_Menu', 'Main_Menu', 'username', 'Priv_ID']);

            foreach ($users as $user) {
                $subMenus = array_values(array_filter(explode(',', (string) $user->Sub_Menu)));

                $filtered = array_values(array_filter($subMenus, function ($menuId) use ($user) {
                    $menuId = trim($menuId);

                    if (in_array($menuId, self::CNS_CHILD_MENU_IDS, true)) {
                        return false;
                    }

                    if ($menuId === 'SM00066') {
                        $isCnsUser = $user->Main_Menu === 'CNS'
                            || $user->username === 'admin'
                            || $user->Priv_ID === 'ADM';

                        return !$isCnsUser;
                    }

                    return true;
                }));

                if ($filtered !== $subMenus) {
                    DB::table('users')->where('id', $user->id)->update([
                        'Sub_Menu' => implode(',', $filtered),
                    ]);
                }
            }
        }

        if (Schema::hasTable('permissions')) {
            $permissionIds = DB::table('permissions')
                ->where(function ($query) {
                    $query->where('name', 'like', 'cns.user_management.%')
                        ->orWhere('name', 'like', 'cns.role_management.%')
                        ->orWhere('name', 'like', 'menu.SI00347.%')
                        ->orWhere('name', 'like', 'menu.SI00348.%');
                })
                ->pluck('id')
                ->all();

            if (!empty($permissionIds)) {
                if (Schema::hasTable('role_has_permissions')) {
                    DB::table('role_has_permissions')
                        ->whereIn('permission_id', $permissionIds)
                        ->delete();
                }

                DB::table('permissions')->whereIn('id', $permissionIds)->delete();
            }
        }

        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        }
    }

    public function down()
    {
        // CNS access control is intentionally not restored. RBAC is managed
        // centrally through the Credential module.
    }
}
