<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\CNS\CnsPermissionService;

class CreateCnsAccessControlMenus extends Migration
{
    private const ACTIONS = ['view', 'create', 'edit', 'delete', 'approve'];

    public function up()
    {
        // 1. Remove the legacy power-rangers Master User menu (SI00334). Do not
        // touch SI00338: on power-rangers that id belongs to Credential.
        if (Schema::hasTable('menu_lists')) {
            DB::table('menu_lists')->where('Menu_ID', 'SI00334')->delete();
        }

        if (Schema::hasTable('users')) {
            $usersWithLegacy = DB::table('users')->where('Sub_Menu', 'like', '%SI00334%')->get();
            foreach ($usersWithLegacy as $u) {
                $subMenus = array_values(array_filter(explode(',', (string) $u->Sub_Menu), function ($item) {
                    return trim($item) !== 'SI00334';
                }));
                DB::table('users')->where('id', $u->id)->update([
                    'Sub_Menu' => implode(',', $subMenus),
                ]);
            }
        }

        // 2. Add new Access Control menus
        $newMenus = [
            'SM00066' => [
                'Name'        => 'Akses & Pengguna',
                'Path_Loc'    => '#',
                'Modules'     => 'CNS',
                'Position'    => 2,
                'Parent_Menu' => 'MS00011',
                'Urutan'      => 11,
            ],
            'SS00095' => [
                'Name'        => 'Akses & Pengguna',
                'Path_Loc'    => '#',
                'Modules'     => 'CNS',
                'Position'    => 3,
                'Parent_Menu' => 'SM00066',
                'Urutan'      => 1,
            ],
            'SI00347' => [
                'Name'        => 'Manajemen Pengguna',
                'Path_Loc'    => 'cns-user-management',
                'Modules'     => 'CNS',
                'Position'    => 4,
                'Parent_Menu' => 'SS00095',
                'Urutan'      => 1,
            ],
            'SI00348' => [
                'Name'        => 'Role & Hak Akses',
                'Path_Loc'    => 'cns-role-management',
                'Modules'     => 'CNS',
                'Position'    => 4,
                'Parent_Menu' => 'SS00095',
                'Urutan'      => 2,
            ],
        ];

        if (Schema::hasTable('menu_lists')) {
            foreach ($newMenus as $menuId => $menuData) {
                $exists = DB::table('menu_lists')->where('Menu_ID', $menuId)->exists();
                if (!$exists) {
                    DB::table('menu_lists')->insert(array_merge(['Menu_ID' => $menuId], $menuData));
                } else {
                    DB::table('menu_lists')->where('Menu_ID', $menuId)->update($menuData);
                }
            }

            // Sync with CNS users, admin, ADM
            if (Schema::hasTable('users')) {
                $cnsUsers = DB::table('users')
                    ->where(function ($q) {
                        $q->where('Main_Menu', 'CNS')
                          ->orWhere('username', 'admin')
                          ->orWhere('Priv_ID', 'ADM');
                    })
                    ->get();

                $menuKeys = array_keys($newMenus);
                foreach ($cnsUsers as $u) {
                    $subMenus = array_filter(explode(',', (string) $u->Sub_Menu));
                    $updated = false;
                    foreach ($menuKeys as $k) {
                        if (!in_array($k, $subMenus, true)) {
                            $subMenus[] = $k;
                            $updated = true;
                        }
                    }
                    if ($updated) {
                        DB::table('users')->where('id', $u->id)->update([
                            'Sub_Menu' => implode(',', $subMenus),
                        ]);
                    }
                }
            }
        }

        // 3. Register Spatie permissions for SI00347 and SI00348
        if (Schema::hasTable('permissions')) {
            $permissionIds = [];
            $targets = [
                'SI00347' => 'user_management',
                'SI00348' => 'role_management',
            ];

            foreach ($targets as $menuId => $slug) {
                foreach (self::ACTIONS as $action) {
                    $name = "cns.{$slug}.{$action}";
                    $existing = DB::table('permissions')->where('name', $name)->first();
                    if (!$existing) {
                        $permId = DB::table('permissions')->insertGetId([
                            'name'       => $name,
                            'guard_name' => 'web',
                            'menu_id'    => $menuId,
                            'action'     => $action,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $permissionIds[] = $permId;
                    } else {
                        DB::table('permissions')->where('id', $existing->id)->update([
                            'menu_id'    => $menuId,
                            'action'     => $action,
                            'updated_at' => now(),
                        ]);
                        $permissionIds[] = $existing->id;
                    }
                }
            }

            // Grant to ADM and admin role
            if (Schema::hasTable('roles') && Schema::hasTable('role_has_permissions')) {
                $adminRoles = DB::table('roles')->whereIn('name', ['ADM', 'Admin', 'admin', 'Superadmin'])->pluck('id');
                foreach ($adminRoles as $roleId) {
                    foreach ($permissionIds as $pId) {
                        $has = DB::table('role_has_permissions')
                            ->where('role_id', $roleId)
                            ->where('permission_id', $pId)
                            ->exists();
                        if (!$has) {
                            DB::table('role_has_permissions')->insert([
                                'role_id'       => $roleId,
                                'permission_id' => $pId,
                            ]);
                        }
                    }
                }
            }

            if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
                app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            }
        }
    }

    public function down()
    {
        $menuIds = ['SI00347', 'SI00348', 'SS00095', 'SM00066'];

        if (Schema::hasTable('menu_lists')) {
            DB::table('menu_lists')->whereIn('Menu_ID', $menuIds)->delete();
        }

        if (Schema::hasTable('users')) {
            $users = DB::table('users')->get();
            foreach ($users as $user) {
                $subMenus = array_values(array_filter(explode(',', (string) $user->Sub_Menu), function ($id) use ($menuIds) {
                    return !in_array(trim($id), $menuIds, true);
                }));
                DB::table('users')->where('id', $user->id)->update([
                    'Sub_Menu' => implode(',', $subMenus),
                ]);
            }
        }

        if (Schema::hasTable('permissions')) {
            $permNames = [];
            foreach (['user_management', 'role_management'] as $slug) {
                foreach (self::ACTIONS as $action) {
                    $permNames[] = "cns.{$slug}.{$action}";
                }
            }
            $permIds = DB::table('permissions')->whereIn('name', $permNames)->pluck('id');
            if (Schema::hasTable('role_has_permissions')) {
                DB::table('role_has_permissions')->whereIn('permission_id', $permIds)->delete();
            }
            if (Schema::hasTable('model_has_permissions')) {
                DB::table('model_has_permissions')->whereIn('permission_id', $permIds)->delete();
            }
            DB::table('permissions')->whereIn('name', $permNames)->delete();
        }
    }
}
