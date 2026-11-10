<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Align the CNS tables with what the CNS code/views expect.
 *
 * Additive only: adds missing columns and indexes; never drops/renames
 * existing columns. Guarded so it is safe to run on the live database.
 */
class AlignCnsSchemaWithCode extends Migration
{
    public function up()
    {
        if (Schema::hasTable('annex_vii')) {
            Schema::table('annex_vii', function (Blueprint $table) {
                if (!Schema::hasColumn('annex_vii', 'Annex_No'))           { $table->string('Annex_No', 100)->nullable(); }
                if (!Schema::hasColumn('annex_vii', 'arranger_name'))      { $table->string('arranger_name', 200)->nullable(); }
                if (!Schema::hasColumn('annex_vii', 'consignee_name'))     { $table->string('consignee_name', 200)->nullable(); }
                if (!Schema::hasColumn('annex_vii', 'actual_date'))        { $table->date('actual_date')->nullable(); }
                if (!Schema::hasColumn('annex_vii', 'carrier'))            { $table->string('carrier', 200)->nullable(); }
                if (!Schema::hasColumn('annex_vii', 'carrier1_transport')) { $table->string('carrier1_transport', 100)->nullable(); }
                if (!Schema::hasColumn('annex_vii', 'carrier2_transport')) { $table->string('carrier2_transport', 100)->nullable(); }
            });

            if (!$this->hasIndex('annex_vii', 'Annex_No')) {
                Schema::table('annex_vii', function (Blueprint $table) {
                    $table->index('Annex_No', 'annex_vii_annex_no_index');
                });
            }
        }

        if (Schema::hasTable('certificate_of_origin')) {
            Schema::table('certificate_of_origin', function (Blueprint $table) {
                if (!Schema::hasColumn('certificate_of_origin', 'Method_Of_Dispatch')) { $table->string('Method_Of_Dispatch', 100)->nullable()->default('Sea'); }
                if (!Schema::hasColumn('certificate_of_origin', 'Voyage_No'))          { $table->string('Voyage_No', 100)->nullable(); }
                if (!Schema::hasColumn('certificate_of_origin', 'Port_Of_Loading'))    { $table->string('Port_Of_Loading', 200)->nullable(); }
                if (!Schema::hasColumn('certificate_of_origin', 'Date_Of_Departure'))  { $table->date('Date_Of_Departure')->nullable(); }
                if (!Schema::hasColumn('certificate_of_origin', 'Port_Of_Discharge'))  { $table->string('Port_Of_Discharge', 200)->nullable(); }
                if (!Schema::hasColumn('certificate_of_origin', 'Kind_Packages'))      { $table->string('Kind_Packages', 200)->nullable(); }
                if (!Schema::hasColumn('certificate_of_origin', 'Net_Weight'))         { $table->decimal('Net_Weight', 15, 3)->nullable()->default(0); }
                if (!Schema::hasColumn('certificate_of_origin', 'Place_Of_Issue'))     { $table->string('Place_Of_Issue', 100)->nullable()->default('Surabaya'); }
                if (!Schema::hasColumn('certificate_of_origin', 'Issue_Date'))         { $table->date('Issue_Date')->nullable(); }
                if (!Schema::hasColumn('certificate_of_origin', 'Status'))             { $table->string('Status', 50)->nullable()->default('CREATED'); }
            });

            if (!$this->hasIndex('certificate_of_origin', 'COO_No')) {
                Schema::table('certificate_of_origin', function (Blueprint $table) {
                    $table->index('COO_No', 'certificate_of_origin_coo_no_index');
                });
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('annex_vii')) {
            if ($this->hasIndex('annex_vii', 'Annex_No')) {
                Schema::table('annex_vii', function (Blueprint $table) {
                    $table->dropIndex('annex_vii_annex_no_index');
                });
            }
            Schema::table('annex_vii', function (Blueprint $table) {
                foreach (['Annex_No', 'arranger_name', 'consignee_name', 'actual_date', 'carrier', 'carrier1_transport', 'carrier2_transport'] as $column) {
                    if (Schema::hasColumn('annex_vii', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('certificate_of_origin')) {
            if ($this->hasIndex('certificate_of_origin', 'COO_No')) {
                Schema::table('certificate_of_origin', function (Blueprint $table) {
                    $table->dropIndex('certificate_of_origin_coo_no_index');
                });
            }
            Schema::table('certificate_of_origin', function (Blueprint $table) {
                foreach (['Method_Of_Dispatch', 'Voyage_No', 'Port_Of_Loading', 'Date_Of_Departure', 'Port_Of_Discharge', 'Kind_Packages', 'Net_Weight', 'Place_Of_Issue', 'Issue_Date', 'Status'] as $column) {
                    if (Schema::hasColumn('certificate_of_origin', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }

    private function hasIndex($table, $column)
    {
        return (bool) DB::selectOne(
            "SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ? LIMIT 1",
            [$table, $column]
        );
    }
}
