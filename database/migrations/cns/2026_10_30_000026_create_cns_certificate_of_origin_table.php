<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCnsCertificateOfOriginTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('certificate_of_origin')) {
            return; // already provisioned on the live database
        }

        Schema::create('certificate_of_origin', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('COO_No', 100);
            $table->string('Consignee', 100)->nullable();
            $table->string('Fin_Inv_No', 100)->nullable();
            $table->date('Fin_Inv_Date')->nullable();
            $table->string('Contract_No', 100)->nullable();
            $table->string('Method_Of_Dispatch', 100)->default('Sea');
            $table->string('Shipment_Type', 100)->default('CIF');
            $table->string('Vessel', 200)->nullable();
            $table->string('Voyage_No', 100)->nullable();
            $table->string('Port_Of_Loading', 200)->nullable();
            $table->date('Date_Of_Departure')->nullable();
            $table->string('Port_Of_Discharge', 200)->nullable();
            $table->string('Final_Destination', 200)->nullable();
            $table->string('Kind_Packages', 200)->nullable();
            $table->string('Good_Description', 255)->default('REFINED USED COOKING OIL');
            $table->string('HS_Code', 50)->default('15119020');
            $table->decimal('Net_Weight', 15, 3)->default(0);
            $table->string('Place_Of_Issue', 100)->default('Surabaya');
            $table->date('Issue_Date')->nullable();
            $table->text('Note')->nullable();
            $table->string('Status', 50)->default('CREATED');
            $table->string('Created_By', 100)->nullable();
            $table->dateTime('Created_Date')->nullable();
            $table->unique(['COO_No'], 'uniq_coo_no');
        });
    }

    public function down()
    {
        // Intentionally left empty (additive/guarded; never drops live tables).
    }
}
