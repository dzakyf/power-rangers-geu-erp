<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPooTypeToTraceabilityTables extends Migration
{
    public function up()
    {
        Schema::table('traceability_external_deliveries', function (Blueprint $table) {
            $table->string('poo_type', 50)->nullable()->after('supplier_name');
        });

        Schema::table('traceability_external_poo_fragments', function (Blueprint $table) {
            $table->string('poo_type', 50)->nullable()->after('poo_name');
        });
    }

    public function down()
    {
        Schema::table('traceability_external_deliveries', function (Blueprint $table) {
            $table->dropColumn('poo_type');
        });

        Schema::table('traceability_external_poo_fragments', function (Blueprint $table) {
            $table->dropColumn('poo_type');
        });
    }
}
