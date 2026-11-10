<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCnsWeightBridgeTicketDtlTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('weight_bridge_ticket_dtl')) {
            return; // already provisioned on the live database
        }

        Schema::create('weight_bridge_ticket_dtl', function (Blueprint $table) {
            $table->increments('id');
            $table->string('WBT_No', 150);
            $table->integer('VSD_ID');
            $table->string('Path_Loc', 255);
            $table->string('Path_Type', 255);
            $table->string('Created_By', 150);
            $table->dateTime('Created_Date')->default(\Illuminate\Support\Facades\DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('Updated_Date')->nullable();

        });
    }

    public function down()
    {
        // Intentionally left empty (additive/guarded; never drops live tables).
    }
}
