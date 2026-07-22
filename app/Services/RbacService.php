<?php

namespace App\Services;

use App\User;
use App\Permission;
use Illuminate\Support\Facades\DB;

class RbacService
{
    public static function rebuildSubMenu(User $user): void
    {
        $permissions = $user->getAllPermissions();

        $menuIds = $permissions
            ->where('action', 'view')
            ->pluck('menu_id')
            ->unique()
            ->filter()
            ->implode(',');

        DB::table('users')
            ->where('id', $user->id)
            ->update(['Sub_Menu' => $menuIds]);
    }
}
