<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQcMethodToCnsQualityControlsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('cns_quality_controls')) {
            return;
        }

        Schema::table('cns_quality_controls', function (Blueprint $table) {
            if (!Schema::hasColumn('cns_quality_controls', 'QC_Method')) {
                $table->string('QC_Method', 150)->nullable()->after('QC_Category');
            }
        });
    }

    public function down()
    {
        // Intentionally left empty (additive/guarded; never drops live columns).
    }
}
