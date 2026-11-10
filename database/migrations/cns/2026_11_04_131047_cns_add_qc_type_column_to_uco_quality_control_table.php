<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CnsAddQcTypeColumnToUcoQualityControlTable extends Migration
{
    public function up()
    {
        Schema::table('uco_quality_control', function (Blueprint $table) {
            if (!Schema::hasColumn('uco_quality_control', 'QC_Category')) {
                $table->string('QC_Category', 50)->default('EXTERNAL')->after('Booking_ID');
                $table->index('QC_Category');
            }
            if (!Schema::hasColumn('uco_quality_control', 'QC_Type')) {
                $table->string('QC_Type', 50)->default('INBOUND')->after('QC_Category');
                $table->index('QC_Type');
            }
            if (!Schema::hasColumn('uco_quality_control', 'IV')) {
                $table->decimal('IV', 8, 2)->nullable()->after('IMP');
            }
            if (!Schema::hasColumn('uco_quality_control', 'Sulphur')) {
                $table->decimal('Sulphur', 8, 2)->nullable()->after('IV');
            }
            if (!Schema::hasColumn('uco_quality_control', 'Phosphorus')) {
                $table->decimal('Phosphorus', 8, 2)->nullable()->after('Sulphur');
            }
            if (!Schema::hasColumn('uco_quality_control', 'Total_Chlorides')) {
                $table->decimal('Total_Chlorides', 8, 2)->nullable()->after('Phosphorus');
            }
            if (!Schema::hasColumn('uco_quality_control', 'Total_Metals')) {
                $table->decimal('Total_Metals', 8, 2)->nullable()->after('Total_Chlorides');
            }
        });
    }

    public function down()
    {
        Schema::table('uco_quality_control', function (Blueprint $table) {
            $colsToDrop = [];
            foreach (['Total_Metals', 'Total_Chlorides', 'Phosphorus', 'Sulphur', 'IV'] as $col) {
                if (Schema::hasColumn('uco_quality_control', $col)) {
                    $colsToDrop[] = $col;
                }
            }
            if (Schema::hasColumn('uco_quality_control', 'QC_Type')) {
                $table->dropIndex(['QC_Type']);
                $colsToDrop[] = 'QC_Type';
            }
            if (Schema::hasColumn('uco_quality_control', 'QC_Category')) {
                $table->dropIndex(['QC_Category']);
                $colsToDrop[] = 'QC_Category';
            }

            if (!empty($colsToDrop)) {
                $table->dropColumn($colsToDrop);
            }
        });
    }
}
