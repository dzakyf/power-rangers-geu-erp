<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCnsShipmentInstructionsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('shipment_instructions')) {
            return; // already provisioned on the live database
        }

        Schema::create('shipment_instructions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('Order_No', 150);
            $table->string('SI_No', 150);
            $table->string('Final_SI_No', 150)->nullable();
            $table->date('Final_SI_Date')->nullable();
            $table->date('SI_Date');
            $table->string('Qty', 150);
            $table->string('Qty1', 150);
            $table->string('Qty2', 150);
            $table->string('Shipper', 50);
            $table->string('Consignee', 50);
            $table->string('Notify', 50);
            $table->string('Destination_Letter', 50);
            $table->string('Delivery_Terms', 255);
            $table->string('Contract_No', 150);
            $table->string('Shipped_By', 150);
            $table->string('Voyage', 150);
            $table->date('ETD');
            $table->date('ETA');
            $table->date('Stuffing');
            $table->string('PEB_No', 150);
            $table->date('PEB_Date');
            $table->string('BL_No', 150);
            $table->string('POL', 150);
            $table->string('POD', 150);
            $table->string('HS_Code', 150);
            $table->string('Status', 50);
            $table->string('Product_Name', 150);
            $table->string('Created_By', 150);
            $table->dateTime('Created_Date')->default(\Illuminate\Support\Facades\DB::raw('CURRENT_TIMESTAMP'));
            $table->string('Updated_By', 50)->nullable();
            $table->dateTime('Updated_Date')->nullable();

        });
    }

    public function down()
    {
        // Intentionally left empty (additive/guarded; never drops live tables).
    }
}
