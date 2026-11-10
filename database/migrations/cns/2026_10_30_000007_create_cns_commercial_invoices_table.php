<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCnsCommercialInvoicesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('commercial_invoices')) {
            return; // already provisioned on the live database
        }

        Schema::create('commercial_invoices', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('SI_No', 150);
            $table->string('Inv_No', 150);
            $table->date('Inv_Date');
            $table->string('Shipper', 150)->nullable();
            $table->string('Consignee', 150);
            $table->string('Description', 255);
            $table->double('Qty', 30, 2)->default(0.00);
            $table->double('Unit_Price', 30, 2)->default(0.00);
            $table->double('Amount', 30, 2)->default(0.00);
            $table->double('Advance_Payment', 30, 2)->default(0.00);
            $table->double('Other', 30, 2)->default(0.00);
            $table->double('Total', 30, 2)->default(0.00);
            $table->string('Status', 50);
            $table->string('Created_By', 150);
            $table->dateTime('Created_Date')->default(\Illuminate\Support\Facades\DB::raw('CURRENT_TIMESTAMP'));

        });
    }

    public function down()
    {
        // Intentionally left empty (additive/guarded; never drops live tables).
    }
}
