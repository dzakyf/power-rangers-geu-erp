<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

class CleanupOfferingLetterApproveDirectPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        // 1. Dapatkan permission offering letter approve dan pastikan canonical id tunggal
        $perms = DB::table('permissions')
            ->where('name', 'cns.offering_letter.approve')
            ->where('guard_name', 'web')
            ->orderBy('id', 'asc')
            ->get();

        if ($perms->isEmpty()) {
            $canonicalId = DB::table('permissions')->insertGetId([
                'name'       => 'cns.offering_letter.approve',
                'guard_name' => 'web',
                'menu_id'    => 'SI00345',
                'action'     => 'approve',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $allPermIds = [$canonicalId];
        } else {
            $canonicalPerm = $perms->first();
            $canonicalId = $canonicalPerm->id;
            $allPermIds = $perms->pluck('id')->all();

            DB::table('permissions')->where('id', $canonicalId)->update([
                'menu_id'    => 'SI00345',
                'action'     => 'approve',
                'updated_at' => now(),
            ]);

            // Deduplikasi jika terdapat lebih dari satu baris permission
            if (count($allPermIds) > 1 && Schema::hasTable('role_has_permissions')) {
                $duplicateIds = array_diff($allPermIds, [$canonicalId]);
                foreach ($duplicateIds as $dupId) {
                    $existingRoleIds = DB::table('role_has_permissions')
                        ->where('permission_id', $dupId)
                        ->pluck('role_id');

                    foreach ($existingRoleIds as $roleId) {
                        $alreadyHas = DB::table('role_has_permissions')
                            ->where('role_id', $roleId)
                            ->where('permission_id', $canonicalId)
                            ->exists();

                        if (!$alreadyHas) {
                            DB::table('role_has_permissions')->insert([
                                'permission_id' => $canonicalId,
                                'role_id'       => $roleId,
                            ]);
                        }
                    }

                    DB::table('role_has_permissions')->where('permission_id', $dupId)->delete();
                }

                DB::table('permissions')->whereIn('id', $duplicateIds)->delete();
            }
        }

        // 2. Pastikan role admin memiliki permission canonical ini
        if (Schema::hasTable('roles') && Schema::hasTable('role_has_permissions')) {
            $adminRoleIds = DB::table('roles')
                ->whereIn('name', ['ADM', 'Admin', 'admin', 'Superadmin'])
                ->pluck('id')
                ->toArray();

            foreach ($adminRoleIds as $roleId) {
                $hasRolePerm = DB::table('role_has_permissions')
                    ->where('role_id', $roleId)
                    ->where('permission_id', $canonicalId)
                    ->exists();

                if (!$hasRolePerm) {
                    DB::table('role_has_permissions')->insert([
                        'permission_id' => $canonicalId,
                        'role_id'       => $roleId,
                    ]);
                }
            }
        }

        // 3. Salin hak akses approver saat ini di model_has_permissions ke role masing-masing user
        if (Schema::hasTable('model_has_permissions') && Schema::hasTable('model_has_roles') && Schema::hasTable('role_has_permissions')) {
            $userApprovers = DB::table('model_has_permissions')
                ->whereIn('permission_id', $allPermIds)
                ->where('model_type', 'App\\User')
                ->pluck('model_id')
                ->unique();

            foreach ($userApprovers as $userId) {
                $userRoleIds = DB::table('model_has_roles')
                    ->where('model_id', $userId)
                    ->where('model_type', 'App\\User')
                    ->pluck('role_id');

                foreach ($userRoleIds as $roleId) {
                    $hasRolePerm = DB::table('role_has_permissions')
                        ->where('role_id', $roleId)
                        ->where('permission_id', $canonicalId)
                        ->exists();

                    if (!$hasRolePerm) {
                        DB::table('role_has_permissions')->insert([
                            'permission_id' => $canonicalId,
                            'role_id'       => $roleId,
                        ]);
                    }
                }
            }

            // 4. Bersihkan direct permission dari model_has_permissions
            DB::table('model_has_permissions')
                ->whereIn('permission_id', $allPermIds)
                ->delete();
        }

        // 5. Reset cache Spatie
        if (class_exists(PermissionRegistrar::class)) {
            app()[PermissionRegistrar::class]->forgetCachedPermissions();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // One-way migration: role-based model is permanent
        if (class_exists(PermissionRegistrar::class)) {
            app()[PermissionRegistrar::class]->forgetCachedPermissions();
        }
    }
}
