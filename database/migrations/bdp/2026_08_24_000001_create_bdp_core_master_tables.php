<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateBdpCoreMasterTables extends Migration
{
    public function up()
    {
        Schema::create('master_customer_type', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->bigIncrements('id');
            $table->string('type', 100)->nullable();
        });
        DB::statement("ALTER TABLE `master_customer_type` MODIFY `id` bigint NOT NULL AUTO_INCREMENT");
        Schema::create('master_leads_classification', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->bigIncrements('id');
            $table->string('status', 100)->nullable();
        });
        DB::statement("ALTER TABLE `master_leads_classification` MODIFY `id` bigint NOT NULL AUTO_INCREMENT");
        Schema::create('master_leads_qualification', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->bigIncrements('id');
            $table->string('status', 100)->nullable();
        });
        DB::statement("ALTER TABLE `master_leads_qualification` MODIFY `id` bigint NOT NULL AUTO_INCREMENT");
        Schema::create('master_leads_reason', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->bigIncrements('id');
            $table->string('status', 100)->nullable();
        });
        DB::statement("ALTER TABLE `master_leads_reason` MODIFY `id` bigint NOT NULL AUTO_INCREMENT");
        Schema::create('master_leads_response', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->bigIncrements('id');
            $table->string('status', 100)->nullable();
        });
        DB::statement("ALTER TABLE `master_leads_response` MODIFY `id` bigint NOT NULL AUTO_INCREMENT");
        Schema::create('master_leads_source', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->bigIncrements('id');
            $table->string('source', 100)->nullable();
        });
        DB::statement("ALTER TABLE `master_leads_source` MODIFY `id` bigint NOT NULL AUTO_INCREMENT");
        Schema::create('master_leads_status', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->bigIncrements('id');
            $table->string('status', 100)->nullable();
        });
        DB::statement("ALTER TABLE `master_leads_status` MODIFY `id` bigint NOT NULL AUTO_INCREMENT");
        Schema::create('master_hub_type', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->bigIncrements('ID');
            $table->string('Display_Text', 100)->nullable();
            $table->string('System_Value', 100)->nullable();
        });
        DB::statement("ALTER TABLE `master_hub_type` MODIFY `ID` bigint NOT NULL AUTO_INCREMENT");
        Schema::create('master_cancellation_reason', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->bigIncrements('id');
            $table->string('status', 100)->nullable();
        });
        DB::statement("ALTER TABLE `master_cancellation_reason` MODIFY `id` bigint NOT NULL AUTO_INCREMENT");
        Schema::create('master_facility', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->increments('id');
            $table->string('type', 50);
            $table->string('category', 150);
            $table->string('sub_category', 150);
            $table->string('description', 255);
            $table->text('children')->nullable();
            $table->string('asset_type', 50);
            $table->Integer('depreciation_year')->nullable();
            $table->TinyInteger('is_deleted')->nullable()->default(0);
            $table->Integer('shelf_life')->nullable();
            $table->decimal('price', 15, 2)->nullable();
        });
        DB::statement("ALTER TABLE `master_facility` MODIFY `id` int NOT NULL AUTO_INCREMENT");
        Schema::create('master_fleet', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->bigIncrements('ID');
            $table->dateTime('Created_At')->nullable()->useCurrent();
            $table->dateTime('Updated_At')->nullable();
            $table->text('Police_Number')->nullable();
            $table->string('Warehouse_Id', 100)->nullable();
            $table->string('Fleet_Brand', 100)->nullable();
            $table->string('Description', 100)->nullable();
            $table->enum('Status', ['ACTIVE', 'INACTIVE'])->nullable()->default('ACTIVE');
        });
        DB::statement("ALTER TABLE `master_fleet` MODIFY `ID` bigint NOT NULL AUTO_INCREMENT");
        Schema::create('master_poo', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->bigIncrements('ID');
            $table->text('Type')->nullable();
            $table->text('Name')->nullable();
            $table->text('PIC')->nullable();
            $table->text('Code')->nullable();
            $table->text('Employee')->nullable();
            $table->text('Phone')->nullable();
            $table->text('Position')->nullable();
            $table->text('Category')->nullable();
            $table->text('UCO_Type')->nullable();
            $table->text('Price')->nullable();
            $table->text('Price_Unit')->nullable();
            $table->text('Province')->nullable();
            $table->text('City')->nullable();
            $table->text('Subdistrict')->nullable();
            $table->text('Village')->nullable();
            $table->text('Cycle')->nullable();
            $table->text('Full_Address')->nullable();
            $table->text('Location_Coordinate')->nullable();
            $table->text('Nearest_Warehouse')->nullable();
            $table->text('Bank_Account_Number')->nullable();
            $table->text('Bank_Account_Name')->nullable();
            $table->text('Bank_Name')->nullable();
            $table->text('Point')->nullable();
            $table->dateTime('Created_At')->nullable()->useCurrent();
            $table->dateTime('Updated_At')->nullable();
            $table->string('Lat', 100)->nullable();
            $table->string('Lng', 100)->nullable();
            $table->text('Created_By')->nullable();
            $table->TinyInteger('Is_Active')->nullable()->default(1);
        });
        DB::statement("ALTER TABLE `master_poo` MODIFY `ID` bigint NOT NULL AUTO_INCREMENT");
        Schema::create('master_stock_price', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->increments('id');
            $table->string('location', 150);
            $table->date('date_for');
            $table->double('price', 30, 2)->default(0);
            $table->dateTime('created_date')->useCurrent();
            $table->string('created_by', 150);
            $table->unique(['date_for'], 'master_stock_price_date_for_unique');
        });
        DB::statement("ALTER TABLE `master_stock_price` MODIFY `id` int NOT NULL AUTO_INCREMENT");
        Schema::create('config_kpi', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->bigIncrements('ID');
            $table->text('Work_Area')->nullable();
            $table->text('Indicator')->nullable();
            $table->Integer('Weight')->nullable();
            $table->text('Target')->nullable();
            $table->text('Departement')->nullable();
            $table->dateTime('Created_At')->useCurrent();
            $table->dateTime('Updated_At')->nullable();
            $table->date('Periode')->nullable();
            $table->Integer('Realization')->nullable();
        });
        DB::statement("ALTER TABLE `config_kpi` MODIFY `ID` bigint NOT NULL AUTO_INCREMENT");
        Schema::create('config_legal', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->bigIncrements('ID');
            $table->text('Document_Name')->nullable();
            $table->text('Description')->nullable();
            $table->text('Media_Url')->nullable();
            $table->dateTime('Created_At')->nullable()->useCurrent();
        });
        DB::statement("ALTER TABLE `config_legal` MODIFY `ID` bigint NOT NULL AUTO_INCREMENT");
        Schema::create('master_kodepos', function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->increments('ID');
            $table->Integer('ZipCode');
            $table->string('Province', 60);
            $table->string('City', 60);
            $table->string('District', 60);
            $table->string('Sub_District', 60);
            $table->string('Regional', 30);
            $table->string('CID_ID', 20);
            $table->string('tricode', 5);
            $table->mediumText('Product');
        });
        DB::statement("ALTER TABLE `master_kodepos` MODIFY `ID` int NOT NULL AUTO_INCREMENT");
    }

    public function down()
    {
        Schema::dropIfExists('master_kodepos');
        Schema::dropIfExists('config_legal');
        Schema::dropIfExists('config_kpi');
        Schema::dropIfExists('master_stock_price');
        Schema::dropIfExists('master_poo');
        Schema::dropIfExists('master_fleet');
        Schema::dropIfExists('master_facility');
        Schema::dropIfExists('master_cancellation_reason');
        Schema::dropIfExists('master_hub_type');
        Schema::dropIfExists('master_leads_status');
        Schema::dropIfExists('master_leads_source');
        Schema::dropIfExists('master_leads_response');
        Schema::dropIfExists('master_leads_reason');
        Schema::dropIfExists('master_leads_qualification');
        Schema::dropIfExists('master_leads_classification');
        Schema::dropIfExists('master_customer_type');
    }
}
