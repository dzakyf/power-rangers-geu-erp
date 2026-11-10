<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCnsSupplierTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('supplier')) {
            return; // already provisioned on the live database
        }

        Schema::create('supplier', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('Location', 30);
            $table->string('Supplier_ID', 30);
            $table->string('Company_Name', 100);
            $table->string('Contact_Person', 30)->nullable();
            $table->text('Address')->nullable();
            $table->text('Street')->nullable();
            $table->string('City', 40)->nullable();
            $table->string('Phone', 60)->nullable();
            $table->string('Fax', 30)->nullable();
            $table->string('HP', 60)->nullable();
            $table->string('Email', 60)->nullable();
            $table->string('NPWP', 50)->nullable();
            $table->string('Sales_Name', 60)->nullable();
            $table->string('Sales_Phone', 60)->nullable();
            $table->string('Payment', 60)->nullable();
            $table->text('Note')->nullable();
            $table->string('Status', 20)->nullable()->default('NORMAL');
            $table->string('Created_By', 20)->nullable();
            $table->timestamp('Created_Date')->nullable()->default(\Illuminate\Support\Facades\DB::raw('CURRENT_TIMESTAMP'));
            $table->string('Bank_Rupiah', 50)->nullable();
            $table->string('Bank_Rupiah_Account', 30)->nullable();
            $table->string('Bank_Rupiah_Name', 60)->nullable();
            $table->string('Bank_Foreign', 50)->nullable();
            $table->string('Bank_Foreign_Account', 30)->nullable();
            $table->string('Bank_Foreign_Name', 60)->nullable();
            $table->string('Bank_Foreign_Currency', 20)->nullable();
            $table->string('Supplier_Category', 30)->nullable();
            $table->string('Supplier_Sub_Category', 30)->nullable();
            $table->string('Supplier_Group_Category', 30)->nullable();
            $table->string('AP_Salary_Group', 30)->nullable();
            $table->string('Letterhead', 255)->nullable();
            $table->string('Footer', 255)->nullable();
            $table->string('Logo', 255)->nullable();
            $table->string('Logo_Inverse', 255)->nullable();
            $table->string('Stamps', 255)->nullable();
            $table->boolean('Is_Consignee')->default(0);
            $table->boolean('Is_Company')->nullable()->default(1);
            $table->boolean('Is_Supplier')->nullable()->default(0);
            $table->index(['Company_Name'], 'Company_Name');
        });
    }

    public function down()
    {
        // Intentionally left empty (additive/guarded; never drops live tables).
    }
}
