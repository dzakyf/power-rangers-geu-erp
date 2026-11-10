<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCnsWeightBridgeTicketTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('weight_bridge_ticket')) {
            return; // already provisioned on the live database
        }

        Schema::create('weight_bridge_ticket', function (Blueprint $table) {
            $table->increments('id');
            $table->string('WBT_No', 150);
            $table->string('Packing_List_No', 150);
            $table->string('Final_SI_No', 150);
            $table->string('SI_No', 150);
            $table->string('Contract_No', 255);
            $table->string('Voyage', 255);
            $table->date('Departure_Date');
            $table->string('BL_No', 255);
            $table->string('Created_By', 150);
            $table->dateTime('Created_Date')->default(\Illuminate\Support\Facades\DB::raw('CURRENT_TIMESTAMP'));

        });
    }

    public function down()
    {
        // Intentionally left empty (additive/guarded; never drops live tables).
    }
}
