<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPickupIdUnitColumns extends Migration
{
    public function up()
    {
        Schema::table('traceability_external_deliveries', function (Blueprint $table) {
            $table->unsignedBigInteger('pickup_id')->nullable()->after('delivery_date');
        });
        Schema::table('traceability_external_poo_fragments', function (Blueprint $table) {
            $table->string('unit', 60)->nullable()->after('quantity_kg');
        });
    }

    public function down()
    {
        Schema::table('traceability_external_deliveries', function (Blueprint $table) {
            $table->dropColumn('pickup_id');
        });
        Schema::table('traceability_external_poo_fragments', function (Blueprint $table) {
            $table->dropColumn('unit');
        });
    }
}
