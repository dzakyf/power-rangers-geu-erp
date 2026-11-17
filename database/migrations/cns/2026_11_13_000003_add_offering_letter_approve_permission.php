<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class AddOfferingLetterApprovePermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('permissions')) {
            Permission::firstOrCreate([
                'name'       => 'cns.offering_letter.approve',
                'guard_name' => 'web',
            ]);

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
        if (Schema::hasTable('permissions')) {
            Permission::where('name', 'cns.offering_letter.approve')
                ->where('guard_name', 'web')
                ->delete();

            app()[PermissionRegistrar::class]->forgetCachedPermissions();
        }
    }
}
