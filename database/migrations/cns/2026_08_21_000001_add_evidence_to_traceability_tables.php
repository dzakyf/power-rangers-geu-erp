<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEvidenceToTraceabilityTables extends Migration
{
    public function up()
    {
        Schema::table('traceability_external_deliveries', function (Blueprint $table) {
            $table->string('plat_no', 50)->nullable()->after('total_quantity_kg');
            $table->text('ttd')->nullable()->after('plat_no');
            $table->text('photos')->nullable()->after('ttd');
        });

        Schema::table('traceability_external_poo_fragments', function (Blueprint $table) {
            $table->string('phone', 50)->nullable()->after('lng');
            $table->string('account_no', 100)->nullable()->after('phone');
            $table->string('account_name', 200)->nullable()->after('account_no');
            $table->decimal('price', 15, 2)->nullable()->after('account_name');
        });
    }

    public function down()
    {
        Schema::table('traceability_external_deliveries', function (Blueprint $table) {
            $table->dropColumn(['plat_no', 'ttd', 'photos']);
        });
        Schema::table('traceability_external_poo_fragments', function (Blueprint $table) {
            $table->dropColumn(['phone', 'account_no', 'account_name', 'price']);
        });
    }
}
