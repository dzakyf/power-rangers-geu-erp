<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGpsSecurityColumnsToAttendantLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('attendant_log')) {
            Schema::table('attendant_log', function (Blueprint $table) {
                if (!Schema::hasColumn('attendant_log', 'Clock_In_Accuracy')) {
                    $table->decimal('Clock_In_Accuracy', 10, 2)->nullable()->after('Clock_In_Lng');
                }
                if (!Schema::hasColumn('attendant_log', 'Clock_Out_Accuracy')) {
                    $table->decimal('Clock_Out_Accuracy', 10, 2)->nullable()->after('Clock_Out_Lng');
                }
                if (!Schema::hasColumn('attendant_log', 'GPS_Anomaly_Flag')) {
                    $table->string('GPS_Anomaly_Flag', 50)->nullable()->default('CLEAN')->after('Notes');
                }
                if (!Schema::hasColumn('attendant_log', 'GPS_Anomaly_Reason')) {
                    $table->text('GPS_Anomaly_Reason')->nullable()->after('GPS_Anomaly_Flag');
                }
                if (!Schema::hasColumn('attendant_log', 'GPS_Meta_Json')) {
                    $table->text('GPS_Meta_Json')->nullable()->after('GPS_Anomaly_Reason');
                }
            });
        }

        if (Schema::hasTable('employment')) {
            Schema::table('employment', function (Blueprint $table) {
                if (!Schema::hasColumn('employment', 'Placement_Location_ID')) {
                    $table->string('Placement_Location_ID', 50)->nullable()->after('Location');
                    $table->index('Placement_Location_ID', 'idx_employment_placement_loc');
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
        if (Schema::hasTable('attendant_log')) {
            Schema::table('attendant_log', function (Blueprint $table) {
                $cols = [];
                foreach (['Clock_In_Accuracy', 'Clock_Out_Accuracy', 'GPS_Anomaly_Flag', 'GPS_Anomaly_Reason', 'GPS_Meta_Json'] as $col) {
                    if (Schema::hasColumn('attendant_log', $col)) {
                        $cols[] = $col;
                    }
                }
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        if (Schema::hasTable('employment') && Schema::hasColumn('employment', 'Placement_Location_ID')) {
            Schema::table('employment', function (Blueprint $table) {
                $table->dropIndex('idx_employment_placement_loc');
                $table->dropColumn('Placement_Location_ID');
            });
        }
    }
}
