<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrivilegeAccessMappingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('privilege_access_mappings')) {
            Schema::create('privilege_access_mappings', function (Blueprint $table) {
                $table->increments('id');
                $table->string('mapping_type', 30)->default('POSITION'); // POSITION, DIRECTORAT, DEPARTMENT
                $table->string('target_key', 150)->index(); // Nama Posisi, Nama Direktorat, atau Dept
                $table->string('priv_id', 50)->index(); // Value untuk users.Priv_ID (e.g. STAFF, HEAD, MANAGER, VP, ADM, HCGA)
                $table->string('default_role', 100)->nullable(); // Role Spatie (e.g. HRD, FINANCE, OPS, STAFF)
                $table->text('default_sub_menu')->nullable(); // Default menu list string jika di-override
                $table->string('description', 255)->nullable();
                $table->string('status', 20)->default('ACTIVE');
                $table->string('created_by', 100)->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->timestamps();
            });
        }

        // Pastikan kolom NIK dan Employee_ID di recruitment ada untuk tracking onboarding
        if (Schema::hasTable('recruitment')) {
            Schema::table('recruitment', function (Blueprint $table) {
                if (!Schema::hasColumn('recruitment', 'NIK')) {
                    $table->string('NIK', 50)->nullable()->after('Task_ID');
                }
                if (!Schema::hasColumn('recruitment', 'Onboarded_At')) {
                    $table->dateTime('Onboarded_At')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('privilege_access_mappings');

        if (Schema::hasTable('recruitment')) {
            Schema::table('recruitment', function (Blueprint $table) {
                if (Schema::hasColumn('recruitment', 'Onboarded_At')) {
                    $table->dropColumn('Onboarded_At');
                }
                if (Schema::hasColumn('recruitment', 'NIK')) {
                    $table->dropColumn('NIK');
                }
            });
        }
    }
}
