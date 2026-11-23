<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSourceToCnsQualityControlsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('cns_quality_controls')) {
            Schema::table('cns_quality_controls', function (Blueprint $table) {
                if (!Schema::hasColumn('cns_quality_controls', 'SOURCE')) {
                    $table->string('SOURCE', 20)->default('QC')->after('Created_By');
                    $table->index('SOURCE');
                }
                if (!Schema::hasColumn('cns_quality_controls', 'SOURCE_ID')) {
                    $table->unsignedBigInteger('SOURCE_ID')->nullable()->after('SOURCE');
                    $table->index('SOURCE_ID');
                }
                $table->index(['SOURCE', 'SOURCE_ID'], 'cns_qc_source_idx');
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
        if (Schema::hasTable('cns_quality_controls')) {
            Schema::table('cns_quality_controls', function (Blueprint $table) {
                $table->dropIndex('cns_qc_source_idx');
                if (Schema::hasColumn('cns_quality_controls', 'SOURCE_ID')) {
                    $table->dropIndex(['SOURCE_ID']);
                    $table->dropColumn('SOURCE_ID');
                }
                if (Schema::hasColumn('cns_quality_controls', 'SOURCE')) {
                    $table->dropIndex(['SOURCE']);
                    $table->dropColumn('SOURCE');
                }
            });
        }
    }
}

