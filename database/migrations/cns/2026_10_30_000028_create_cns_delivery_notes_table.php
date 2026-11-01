<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCnsDeliveryNotesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('delivery_notes')) {
            return; // already provisioned on the live database
        }

        Schema::create('delivery_notes', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('DN_No', 100);
            $table->string('Consignee', 100)->nullable();
            $table->string('Supplier_ISCC_Ref', 200)->nullable();
            $table->string('Contract_No', 100)->nullable();
            $table->string('Product', 255)->default('Used Cooking Oil (UCO) entirely of Vege-Oil Origin');
            $table->decimal('Total_Tonnage', 15, 3)->default(0);
            $table->date('Delivery_Date')->nullable();
            $table->string('Country_Of_Origin', 100)->default('Indonesia');
            $table->text('GHG_Value')->nullable();
            $table->text('Note')->nullable();
            $table->string('Status', 50)->default('CREATED');
            $table->string('Created_By', 100)->nullable();
            $table->dateTime('Created_Date')->nullable();
            $table->unique(['DN_No'], 'uniq_dn_no');
        });
    }

    public function down()
    {
        // Intentionally left empty (additive/guarded; never drops live tables).
    }
}
