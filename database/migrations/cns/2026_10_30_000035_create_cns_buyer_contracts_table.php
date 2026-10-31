<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCnsBuyerContractsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('cns_buyer_contracts')) {
            return;
        }

        Schema::create('cns_buyer_contracts', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('Contract_No', 100);
            $table->unsignedBigInteger('Supplier_ID')->nullable();
            $table->string('Buyer_Name', 150);
            $table->text('Buyer_Address');
            $table->string('Buyer_Phone', 60);
            $table->json('Buyer_Emails')->nullable();
            $table->decimal('Quantity_MT', 15, 3)->default(0);
            $table->integer('Container_Count')->default(0);
            $table->string('Container_Size', 20)->default('20 ft');
            $table->date('Loading_Start')->nullable();
            $table->date('Loading_End')->nullable();
            $table->string('Incoterms', 10)->default('FOB');
            $table->string('Price_Currency', 3)->default('USD');
            $table->decimal('Price_Per_MT', 18, 2)->default(0);
            $table->string('Status', 30)->default('UNDER_REVIEW');
            $table->string('Contract_File', 255)->nullable();
            $table->string('Contract_File_Name', 255)->nullable();
            $table->text('Note')->nullable();
            $table->string('Location', 30)->nullable();
            $table->string('Created_By', 100)->nullable();
            $table->dateTime('Created_Date')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('Updated_By', 100)->nullable();
            $table->dateTime('Updated_Date')->nullable();
            $table->unique('Contract_No');
            $table->index('Supplier_ID');
            $table->index('Status');
        });
    }

    public function down()
    {
        // Intentionally left empty (additive/guarded; never drops live tables).
    }
}
