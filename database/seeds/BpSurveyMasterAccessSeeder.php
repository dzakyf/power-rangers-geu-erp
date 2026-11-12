<?php

use App\Permission;
use App\Role;
use App\Services\RbacService;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * Restores access to the Business Partner "Survey" (master survey) menu.
 *
 * The menu item SI00227 (partner-survey-index) is where survey data is
 * created/edited. HEAD, MANAGER and SALES can already access Approval Survey
 * (SI00228) but lost access to the master survey menu when Sub_Menu was
 * rebuilt from role permissions, so mirror the approval permissions to the
 * master menu and rebuild Sub_Menu for affected users.
 *
 * Idempotent: safe to run repeatedly.
 */
class BpSurveyMasterAccessSeeder extends Seeder
{
    private const MENU_ID = 'SI00227';

    private const ROLES = ['HEAD', 'MANAGER', 'SALES'];

    private const ACTIONS = ['view', 'create', 'edit', 'delete', 'approve'];

    public function run()
    {
        $permissionNames = [];
        foreach (self::ACTIONS as $action) {
            $name = 'menu.' . self::MENU_ID . '.' . $action;
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['menu_id' => self::MENU_ID, 'action' => $action]
            );
            $permissionNames[] = $name;
        }

        $permissions = Permission::whereIn('name', $permissionNames)->get();
        $affectedUserIds = [];

        foreach (self::ROLES as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if (!$role) {
                $this->command->warn("Role {$roleName} not found; skipped.");
                continue;
            }

            $existing = $role->permissions()->pluck('name')->flip();
            $missing = $permissions->filter(function ($permission) use ($existing) {
                return !$existing->has($permission->name);
            });

            if ($missing->isNotEmpty()) {
                $role->givePermissionTo($missing);
                $this->command->info("Granted " . $missing->count() . " survey permissions to {$roleName}.");
            }

            $userIds = DB::table('model_has_roles')
                ->where('role_id', $role->id)
                ->pluck('model_id')
                ->all();

            $affectedUserIds = array_merge($affectedUserIds, $userIds);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $affectedUserIds = array_values(array_unique($affectedUserIds));
        foreach ($affectedUserIds as $userId) {
            $user = User::find($userId);
            if ($user) {
                RbacService::rebuildSubMenu($user);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command->info('BpSurveyMasterAccessSeeder complete (' . count($affectedUserIds) . ' users refreshed).');
    }
}
