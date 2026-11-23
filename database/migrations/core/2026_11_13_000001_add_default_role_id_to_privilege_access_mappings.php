<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddDefaultRoleIdToPrivilegeAccessMappings extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('privilege_access_mappings')) {
            return;
        }

        if (!Schema::hasColumn('privilege_access_mappings', 'default_role_id')) {
            Schema::table('privilege_access_mappings', function (Blueprint $table) {
                $table->unsignedBigInteger('default_role_id')->nullable()->index();
            });
        }

        if (Schema::hasTable('roles') && Schema::hasColumn('privilege_access_mappings', 'default_role_id')) {
            $roles = DB::table('roles');

            if (Schema::hasColumn('roles', 'department')) {
                $roles->whereNull('department');
            }

            $globalRoles = $roles->pluck('id', 'name');

            DB::table('privilege_access_mappings')
                ->whereNull('default_role_id')
                ->whereNotNull('default_role')
                ->orderBy('id')
                ->chunkById(100, function ($rows) use ($globalRoles) {
                    foreach ($rows as $row) {
                        if (isset($globalRoles[$row->default_role])) {
                            DB::table('privilege_access_mappings')
                                ->where('id', $row->id)
                                ->update(['default_role_id' => $globalRoles[$row->default_role]]);
                        }
                    }
                });
        }
    }

    public function down()
    {
        if (Schema::hasTable('privilege_access_mappings') && Schema::hasColumn('privilege_access_mappings', 'default_role_id')) {
            Schema::table('privilege_access_mappings', function (Blueprint $table) {
                $table->dropColumn('default_role_id');
            });
        }
    }
}
