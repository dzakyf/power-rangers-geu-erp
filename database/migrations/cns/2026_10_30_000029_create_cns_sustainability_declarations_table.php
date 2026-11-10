<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCnsSustainabilityDeclarationsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('sustainability_declarations')) {
            return; // already provisioned on the live database
        }

        Schema::create('sustainability_declarations', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('SD_No', 50);
            $table->date('Issue_Date')->nullable();
            $table->string('Consignee', 100)->nullable();
            $table->string('Certificate_No', 100)->nullable();
            $table->string('Contract_No', 100)->nullable();
            $table->text('Dispatch_Address')->nullable();
            $table->boolean('Dispatch_Same_As_Supplier')->default(0);
            $table->text('Receipt_Address')->nullable();
            $table->boolean('Receipt_Same_As_Recipient')->default(0);
            $table->date('Dispatch_Date')->nullable();
            $table->string('Product_Type', 100)->default('Raw Material');
            $table->string('Raw_Material_Type', 255)->default('Used cooking oil (UCO) entirely of veg. origin');
            $table->text('Additional_Info')->nullable();
            $table->string('Country_Of_Origin', 100)->default('Indonesia');
            $table->decimal('Quantity', 15, 3)->default(0);
            $table->string('Quantity_Unit', 20)->default('metric tons');
            $table->boolean('ISCC_Compliant')->default(1);
            $table->boolean('EU_RED_Compliant')->default(1);
            $table->string('Chain_Of_Custody', 100)->default('Mass balance');
            $table->string('Scope_Art29', 3)->default('Yes');
            $table->string('Scope_Intermediate_Crop', 3)->default('Yes');
            $table->string('Scope_Low_ILUC', 3)->default('Yes');
            $table->string('Scope_Waste_Residue', 3)->default('Yes');
            $table->string('Waste_Permit_No', 200)->nullable();
            $table->string('Scope_Support_Received', 3)->default('No');
            $table->text('Support_Nature')->nullable();
            $table->string('GHG_Total_Default', 3)->default('No');
            $table->string('GHG_Eec', 50)->nullable();
            $table->string('GHG_El', 50)->nullable();
            $table->string('GHG_Ep', 50)->nullable();
            $table->string('GHG_Etd', 50)->nullable()->default('DDV');
            $table->string('GHG_Eu', 50)->nullable();
            $table->string('GHG_Esca', 50)->nullable();
            $table->string('GHG_Eccs', 50)->nullable();
            $table->string('GHG_Eccr', 50)->nullable();
            $table->decimal('Transport_Ship_KM', 10, 2)->nullable();
            $table->decimal('Transport_Train_KM', 10, 2)->nullable();
            $table->decimal('Transport_Road_KM', 10, 2)->nullable()->default(3.30);
            $table->boolean('GHG_DDV_Oil_Extraction')->default(1);
            $table->boolean('GHG_DDV_Soil_N2O')->default(1);
            $table->text('GHG_Esca_Bonus_Cap')->nullable();
            $table->string('GHG_NUTS2_Area', 100)->nullable();
            $table->string('GHG_El_Bonus', 3)->default('Yes');
            $table->boolean('Addon_FSS')->default(1);
            $table->boolean('Addon_EUDR')->default(1);
            $table->boolean('Addon_NonGMO_Feed')->default(1);
            $table->boolean('Addon_NonGMO_Technical')->default(1);
            $table->text('Note')->nullable();
            $table->string('Status', 50)->default('CREATED');
            $table->string('Created_By', 100)->nullable();
            $table->dateTime('Created_Date')->nullable();
            $table->unique(['SD_No'], 'uniq_sd_no');
        });
    }

    public function down()
    {
        // Intentionally left empty (additive/guarded; never drops live tables).
    }
}
