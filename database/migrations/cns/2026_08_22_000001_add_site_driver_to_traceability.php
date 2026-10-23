<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSiteDriverToTraceability extends Migration
{
    public function up()
    {
        Schema::table('traceability_external_poo_fragments', function (Blueprint $table) {
            $table->string('site', 120)->nullable()->after('province');
        });

        Schema::table('traceability_external_deliveries', function (Blueprint $table) {
            $table->string('driver_name', 200)->nullable()->after('plat_no');
        });
    }

    public function down()
    {
        Schema::table('traceability_external_poo_fragments', function (Blueprint $table) {
            $table->dropColumn('site');
        });
        Schema::table('traceability_external_deliveries', function (Blueprint $table) {
            $table->dropColumn('driver_name');
        });
    }
}
