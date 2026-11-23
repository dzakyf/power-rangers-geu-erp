<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SplitMiuInCnsQualityControlDetails extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('cns_quality_control_details')) {
            return;
        }

        Schema::table('cns_quality_control_details', function (Blueprint $table) {
            if (!Schema::hasColumn('cns_quality_control_details', 'Moisture')) {
                $table->decimal('Moisture', 10, 2)->nullable()->after('FFA_As_Oleic');
            }
            if (!Schema::hasColumn('cns_quality_control_details', 'Impurities')) {
                $table->decimal('Impurities', 10, 2)->nullable()->after('Moisture');
            }
            if (!Schema::hasColumn('cns_quality_control_details', 'Unsaponifiables')) {
                $table->decimal('Unsaponifiables', 10, 2)->nullable()->after('Impurities');
            }
            if (Schema::hasColumn('cns_quality_control_details', 'MIU')) {
                $table->dropColumn('MIU');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('cns_quality_control_details')) {
            return;
        }

        Schema::table('cns_quality_control_details', function (Blueprint $table) {
            if (!Schema::hasColumn('cns_quality_control_details', 'MIU')) {
                $table->decimal('MIU', 10, 2)->nullable()->after('FFA_As_Oleic');
            }
            foreach (['Unsaponifiables', 'Impurities', 'Moisture'] as $col) {
                if (Schema::hasColumn('cns_quality_control_details', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
