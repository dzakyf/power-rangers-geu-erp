<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Services\CNS\CnsPermissionService;

class NormalizeCnsPermissionFormat extends Migration
{
    private const ACTIONS = ['view', 'create', 'edit', 'delete', 'approve'];

    public function up()
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        $guardName = 'web';
        $menuSlugs = CnsPermissionService::allMenuSlugs();

        // 1. Consolidate legacy aliases first
        $legacyAliases = [
            'cns.shipment_instruction.approve' => 'cns.shipping_instruction.approve',
            'cns.final_shipment_instruction.approve' => 'cns.final_shipping_instruction.approve',
        ];

        foreach ($legacyAliases as $legacyName => $targetName) {
            $legacyPerm = DB::table('permissions')->where('name', $legacyName)->first();
            $targetPerm = DB::table('permissions')->where('name', $targetName)->first();

            if ($legacyPerm && $targetPerm) {
                $this->mergePermissions($legacyPerm->id, $targetPerm->id);
                DB::table('permissions')->where('id', $legacyPerm->id)->delete();
            } elseif ($legacyPerm && !$targetPerm) {
                DB::table('permissions')->where('id', $legacyPerm->id)->update([
                    'name' => $targetName,
                ]);
            }
        }

        // 2. Normalize all menu.{Menu_ID}.{action} to cns.{menu_name}.{action}
        foreach ($menuSlugs as $menuId => $slug) {
            foreach (self::ACTIONS as $action) {
                $oldName = "menu.{$menuId}.{$action}";
                $newName = "cns.{$slug}.{$action}";

                $oldPerm = DB::table('permissions')->where('name', $oldName)->first();
                $newPerm = DB::table('permissions')->where('name', $newName)->first();

                if ($oldPerm && $newPerm) {
                    $this->mergePermissions($oldPerm->id, $newPerm->id);
                    DB::table('permissions')->where('id', $oldPerm->id)->delete();
                    DB::table('permissions')->where('id', $newPerm->id)->update([
                        'menu_id' => $menuId,
                        'action' => $action,
                    ]);
                } elseif ($oldPerm && !$newPerm) {
                    DB::table('permissions')->where('id', $oldPerm->id)->update([
                        'name' => $newName,
                        'menu_id' => $menuId,
                        'action' => $action,
                    ]);
                } elseif (!$oldPerm && $newPerm) {
                    DB::table('permissions')->where('id', $newPerm->id)->update([
                        'menu_id' => $menuId,
                        'action' => $action,
                    ]);
                }
            }
        }

        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        }
    }

    public function down()
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        $menuSlugs = CnsPermissionService::allMenuSlugs();

        foreach ($menuSlugs as $menuId => $slug) {
            foreach (self::ACTIONS as $action) {
                $newName = "cns.{$slug}.{$action}";
                $oldName = "menu.{$menuId}.{$action}";

                $newPerm = DB::table('permissions')->where('name', $newName)->first();
                $oldPerm = DB::table('permissions')->where('name', $oldName)->first();

                if ($newPerm && !$oldPerm) {
                    DB::table('permissions')->where('id', $newPerm->id)->update([
                        'name' => $oldName,
                        'menu_id' => $menuId,
                        'action' => $action,
                    ]);
                }
            }
        }

        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        }
    }

    private function mergePermissions(int $fromId, int $toId): void
    {
        if (Schema::hasTable('role_has_permissions')) {
            $existingRoles = DB::table('role_has_permissions')
                ->where('permission_id', $toId)
                ->pluck('role_id')
                ->toArray();

            $fromRoles = DB::table('role_has_permissions')
                ->where('permission_id', $fromId)
                ->pluck('role_id')
                ->toArray();

            $missingRoles = array_diff($fromRoles, $existingRoles);
            foreach ($missingRoles as $roleId) {
                DB::table('role_has_permissions')->insert([
                    'permission_id' => $toId,
                    'role_id' => $roleId,
                ]);
            }

            DB::table('role_has_permissions')->where('permission_id', $fromId)->delete();
        }

        if (Schema::hasTable('model_has_permissions')) {
            $existingModels = DB::table('model_has_permissions')
                ->where('permission_id', $toId)
                ->get();

            $fromModels = DB::table('model_has_permissions')
                ->where('permission_id', $fromId)
                ->get();

            foreach ($fromModels as $row) {
                $exists = $existingModels->where('model_id', $row->model_id)
                    ->where('model_type', $row->model_type)
                    ->isNotEmpty();

                if (!$exists) {
                    DB::table('model_has_permissions')->insert([
                        'permission_id' => $toId,
                        'model_type' => $row->model_type,
                        'model_id' => $row->model_id,
                    ]);
                }
            }

            DB::table('model_has_permissions')->where('permission_id', $fromId)->delete();
        }
    }
}
