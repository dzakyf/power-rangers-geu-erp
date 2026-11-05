<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUcoQualityControlTable extends Migration
{
    /**
     * Run the migrations.
     * Add all columns required for the Quality Control flow:
     * - MIU (Moisture, Impurities & Unsaponifiables)
     * - QC_Category (INTERNAL / EXTERNAL)
     * - QC_Type (INBOUND / OUTBOUND)
     * - IV, Sulphur, Phosphorus, Total_Chlorides, Total_Metals
     * - Contract_ID, Booking_ID
     */
    public function up()
    {
        if (!Schema::hasTable('uco_quality_control')) {
            return;
        }

        Schema::table('uco_quality_control', function (Blueprint $table) {
            if (!Schema::hasColumn('uco_quality_control', 'Kode_QC')) {
                $table->string('Kode_QC', 100)->nullable()->after('ID');
                $table->index('Kode_QC');
            }
            if (!Schema::hasColumn('uco_quality_control', 'Contract_ID')) {
                $table->unsignedBigInteger('Contract_ID')->nullable()->after('Source_ID');
                $table->index('Contract_ID');
            }
            if (!Schema::hasColumn('uco_quality_control', 'Booking_ID')) {
                $table->unsignedBigInteger('Booking_ID')->nullable()->after('Contract_ID');
                $table->index('Booking_ID');
            }
            if (!Schema::hasColumn('uco_quality_control', 'QC_Category')) {
                $table->string('QC_Category', 50)->default('EXTERNAL')->after('Booking_ID');
                $table->index('QC_Category');
            }
            if (!Schema::hasColumn('uco_quality_control', 'QC_Type')) {
                $table->string('QC_Type', 50)->default('INBOUND')->after('QC_Category');
                $table->index('QC_Type');
            }
            if (!Schema::hasColumn('uco_quality_control', 'MIU')) {
                $table->decimal('MIU', 8, 2)->nullable()->after('FFA_As_Oleic');
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

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (!Schema::hasTable('uco_quality_control')) {
            return;
        }

        Schema::table('uco_quality_control', function (Blueprint $table) {
            $colsToDrop = [];
            foreach (['Total_Metals', 'Total_Chlorides', 'Phosphorus', 'Sulphur', 'IV', 'MIU'] as $col) {
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
            if (Schema::hasColumn('uco_quality_control', 'Booking_ID')) {
                $table->dropIndex(['Booking_ID']);
                $colsToDrop[] = 'Booking_ID';
            }
            if (Schema::hasColumn('uco_quality_control', 'Contract_ID')) {
                $table->dropIndex(['Contract_ID']);
                $colsToDrop[] = 'Contract_ID';
            }

            if (!empty($colsToDrop)) {
                $table->dropColumn($colsToDrop);
            }
        });
    }
}

