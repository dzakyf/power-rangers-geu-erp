<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnnexViiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('annex_vii', function (Blueprint $table) {
            $table->id();

            $table->string('Annex_No', 100)->nullable();
            $table->string('SI_No', 100)->nullable();
            $table->string('Order_No', 100)->nullable();
            $table->decimal('Qty1', 18, 2)->nullable();
            $table->decimal('Qty2', 18, 2)->nullable();
            $table->date('SI_Date')->nullable();

            $table->string('Shipper', 200)->nullable();
            $table->string('arranger_name', 200)->nullable();
            $table->string('arranger_address', 200)->nullable();
            $table->string('arranger_contact', 100)->nullable();
            $table->string('arranger_tel', 50)->nullable();
            $table->string('arranger_email', 150)->nullable();

            $table->string('Consignee', 200)->nullable();
            $table->string('consignee_name', 200)->nullable();
            $table->string('consignee_email', 150)->nullable();
            $table->string('consignee_telp', 50)->nullable();

            $table->decimal('actual_quantity', 18, 2)->nullable();
            $table->date('actual_date')->nullable();
            $table->date('ETD')->nullable();
            $table->date('ETA')->nullable();
            $table->date('Stuffing')->nullable();
            $table->string('POL', 100)->nullable();
            $table->string('POD', 100)->nullable();
            $table->date('PEB_Date')->nullable();
            $table->string('PEB_No', 100)->nullable();
            $table->string('BL_No', 100)->nullable();
            $table->string('HS_Code', 50)->nullable();
            $table->string('Contract_No', 100)->nullable();
            $table->string('Delivery_Terms', 100)->nullable();
            $table->string('Voyage', 100)->nullable();
            $table->string('Destination_Port', 100)->nullable();
            $table->string('Notify', 200)->nullable();
            $table->string('carrier', 200)->nullable();

            $table->string('carrier1_name', 200)->nullable();
            $table->string('carrier1_address', 200)->nullable();
            $table->string('carrier1_phone', 50)->nullable();
            $table->string('carrier1_email', 150)->nullable();
            $table->string('carrier1_transport', 100)->nullable();
            $table->date('carrier1_date')->nullable();
            $table->string('carrier1_licence', 100)->nullable();

            $table->string('carrier2_name', 200)->nullable();
            $table->string('carrier2_address', 200)->nullable();
            $table->string('carrier2_contact', 100)->nullable();
            $table->string('carrier2_tel', 50)->nullable();
            $table->string('carrier2_fax', 50)->nullable();
            $table->string('carrier2_email', 150)->nullable();
            $table->string('carrier2_transport', 100)->nullable();
            $table->date('carrier2_date')->nullable();
            $table->string('carrier2_licence', 100)->nullable();

            $table->string('carrier3_name', 200)->nullable();
            $table->string('carrier3_address', 200)->nullable();
            $table->string('carrier3_contact', 100)->nullable();
            $table->string('carrier3_tel', 50)->nullable();
            $table->string('carrier3_fax', 50)->nullable();
            $table->string('carrier3_email', 150)->nullable();
            $table->string('carrier3_transport', 100)->nullable();
            $table->date('carrier3_date')->nullable();
            $table->string('carrier3_licence', 100)->nullable();

            $table->string('Container1', 50)->nullable();
            $table->string('Container2', 50)->nullable();
            $table->string('Container3', 50)->nullable();
            $table->string('Container4', 50)->nullable();
            $table->string('Container5', 50)->nullable();

            $table->string('waste_generator_name', 200)->nullable();
            $table->string('waste_generator_address', 200)->nullable();
            $table->string('recovery_address', 200)->nullable();
            $table->string('recovery_contact', 100)->nullable();
            $table->string('recovery_tel', 50)->nullable();
            $table->string('recovery_email', 150)->nullable();

            $table->string('recovery_operation', 100)->nullable();
            $table->text('Product_Name')->nullable();
            $table->string('basel_annex', 100)->nullable();
            $table->string('oecd_code', 100)->nullable();
            $table->string('ec_list', 100)->nullable();
            $table->string('national_code', 100)->nullable();

            $table->string('export_country', 100)->nullable();
            $table->string('transit_country', 100)->nullable();
            $table->string('import_destination', 100)->nullable();

            $table->string('declarant_name', 150)->nullable();
            $table->date('declarant_date')->nullable();

            $table->string('created_by', 100)->nullable();
            $table->string('updated_by', 100)->nullable();

            $table->timestamps();

            $table->index('SI_No');
            $table->index('Order_No');
            $table->index('Annex_No');
            $table->index('Shipper');
            $table->index('Consignee');
        });
    }

    public function down()
    {
        Schema::dropIfExists('annex_vii');
    }
};