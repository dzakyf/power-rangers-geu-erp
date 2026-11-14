<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddDepartmentToRoles extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('roles')) {
            return;
        }

        if (!Schema::hasColumn('roles', 'department')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->string('department', 20)->nullable()->after('guard_name');
            });
        }

        $indexes = collect(DB::select('SHOW INDEX FROM roles'))->pluck('Key_name')->all();

        if (in_array('roles_name_guard_name_unique', $indexes, true)) {
            DB::statement('ALTER TABLE `roles` DROP INDEX `roles_name_guard_name_unique`');
        }

        if (!Schema::hasColumn('roles', 'department_key')) {
            DB::statement("ALTER TABLE `roles` ADD COLUMN `department_key` VARCHAR(20) GENERATED ALWAYS AS (COALESCE(`department`, '')) STORED");
        }

        $indexes = collect(DB::select('SHOW INDEX FROM roles'))->pluck('Key_name')->all();

        if (!in_array('roles_name_department_guard_unique', $indexes, true)) {
            DB::statement('ALTER TABLE `roles` ADD UNIQUE KEY `roles_name_department_guard_unique` (`name`, `department_key`, `guard_name`)');
        }

        if (!in_array('roles_department_index', $indexes, true)) {
            DB::statement('ALTER TABLE `roles` ADD INDEX `roles_department_index` (`department`)');
        }
    }

    public function down()
    {
        if (!Schema::hasTable('roles')) {
            return;
        }

        $indexes = collect(DB::select('SHOW INDEX FROM roles'))->pluck('Key_name')->all();

        if (in_array('roles_name_department_guard_unique', $indexes, true)) {
            DB::statement('ALTER TABLE `roles` DROP INDEX `roles_name_department_guard_unique`');
        }

        if (in_array('roles_department_index', $indexes, true)) {
            DB::statement('ALTER TABLE `roles` DROP INDEX `roles_department_index`');
        }

        if (Schema::hasColumn('roles', 'department_key')) {
            DB::statement('ALTER TABLE `roles` DROP COLUMN `department_key`');
        }

        if (Schema::hasColumn('roles', 'department')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropColumn('department');
            });
        }

        $indexes = collect(DB::select('SHOW INDEX FROM roles'))->pluck('Key_name')->all();
        if (!in_array('roles_name_guard_name_unique', $indexes, true)) {
            DB::statement('ALTER TABLE `roles` ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`, `guard_name`)');
        }
    }
}
