<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class AddSiteToDeliveries extends Migration
{
    public function up()
    {
        Schema::table('traceability_external_deliveries', function (Blueprint $table) {
            $table->string('site', 120)->nullable()->after('province');
        });
    }
    public function down()
    {
        Schema::table('traceability_external_deliveries', function (Blueprint $table) {
            $table->dropColumn('site');
        });
    }
}
