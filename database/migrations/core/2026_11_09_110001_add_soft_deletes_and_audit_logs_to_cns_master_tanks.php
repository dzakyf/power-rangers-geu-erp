<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoftDeletesAndAuditLogsToCnsMasterTanks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('cns_master_tanks')) {
            Schema::table('cns_master_tanks', function (Blueprint $table) {
                if (!Schema::hasColumn('cns_master_tanks', 'deleted_at')) {
                    $table->softDeletes()->after('updated_at');
                }
            });
        }

        if (!Schema::hasTable('cns_master_tank_logs')) {
            Schema::create('cns_master_tank_logs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('tank_id')->index();
                $table->string('tank_name', 100)->nullable();
                $table->string('action', 50); // CREATE, UPDATE, DELETE, RESTORE
                $table->text('details')->nullable();
                $table->text('old_values')->nullable();
                $table->text('new_values')->nullable();
                $table->string('performed_by', 100)->nullable();
                $table->timestamp('created_at')->nullable();

                $table->index(['tank_id', 'created_at']);
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
        Schema::dropIfExists('cns_master_tank_logs');

        if (Schema::hasTable('cns_master_tanks') && Schema::hasColumn('cns_master_tanks', 'deleted_at')) {
            Schema::table('cns_master_tanks', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
}
