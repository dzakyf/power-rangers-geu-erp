<?php

namespace App\Services;

use App\User;
use App\Permission;
use App\Services\Rbac\PermissionRegistry;
use Illuminate\Support\Facades\DB;

class RbacService
{
    public static function rebuildSubMenu(User $user): void
    {
        $permissions = $user->getAllPermissions();

        $directMenuIds = [];

        foreach ($permissions as $perm) {
            $isView = false;
            $menuId = $perm->menu_id;

            if ($perm->action === 'view') {
                $isView = true;
            } elseif (preg_match('/\.view$/', $perm->name)) {
                $isView = true;
            }

            if (!$isView) {
                continue;
            }

            if (!$menuId) {
                if (preg_match('/^menu\.([A-Z0-9]+)\.view$/', $perm->name, $matches)) {
                    $menuId = $matches[1];
                } else {
                    $registry = app(PermissionRegistry::class);

                    foreach ($registry->all() as $provider) {
                        $prefix = preg_quote($provider->prefix(), '/');

                        if (preg_match("/^{$prefix}\.([a-z0-9_]+)\.view$/", $perm->name, $matches)) {
                            $menuId = $provider->menuIdForSlug($matches[1]);
                            break;
                        }
                    }
                }
            }

            if ($menuId) {
                $directMenuIds[$menuId] = true;
            }
        }

        if (empty($directMenuIds)) {
            DB::table('users')
                ->where('id', $user->id)
                ->update(['Sub_Menu' => '']);
            return;
        }

        // Fetch parent mappings from menu_lists to cascade to ancestors
        $menus = DB::table('menu_lists')
            ->select('Menu_ID', 'Parent_Menu')
            ->get()
            ->keyBy('Menu_ID');

        $finalMenuIds = [];
        foreach (array_keys($directMenuIds) as $mId) {
            $curr = $mId;
            $visited = [];
            while ($curr && isset($menus[$curr]) && !isset($visited[$curr])) {
                $finalMenuIds[$curr] = true;
                $visited[$curr] = true;
                $curr = $menus[$curr]->Parent_Menu;
            }
        }

        $menuIdsStr = implode(',', array_keys($finalMenuIds));

        DB::table('users')
            ->where('id', $user->id)
            ->update(['Sub_Menu' => $menuIdsStr]);
    }
}

