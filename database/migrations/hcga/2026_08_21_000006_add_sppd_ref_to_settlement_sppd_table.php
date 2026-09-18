<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSppdRefToSettlementSppdTable extends Migration
{
    public function up()
    {
        Schema::table('settlement_sppd', function (Blueprint $table) {
            if (!Schema::hasColumn('settlement_sppd', 'SPPD_ID')) {
                $table->integer('SPPD_ID')->nullable()->index();
            }
            if (!Schema::hasColumn('settlement_sppd', 'SPPD_No')) {
                $table->string('SPPD_No', 100)->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('settlement_sppd', function (Blueprint $table) {
            foreach (['SPPD_ID', 'SPPD_No'] as $column) {
                if (Schema::hasColumn('settlement_sppd', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
