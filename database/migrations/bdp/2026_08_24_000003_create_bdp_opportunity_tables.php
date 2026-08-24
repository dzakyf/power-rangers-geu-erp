<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateBdpOpportunityTables extends Migration
{
    public function up()
    {
        Schema::create('opportunity', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->increments('ID');
            $table->string('Task_ID', 255);
            $table->string('Location', 50);
            $table->date('Date');
            $table->string('Full_Name', 255);
            $table->string('Phone_Number', 255);
            $table->Integer('Partnership_Interest');
            $table->Integer('Capital_Estimation');
            $table->dateTime('Created_Date')->useCurrent();
            $table->string('Created_By', 255);
            $table->string('Status', 255);
            $table->string('Reason', 255)->nullable();
            $table->string('registration_token', 64)->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->string('Updated_By', 255);
            $table->dateTime('Updated_Date')->nullable()->useCurrent();
            $table->dateTime('FU_To_Qualified_Date')->nullable();
            $table->dateTime('Qualified_To_Warm_Date')->nullable();
            $table->dateTime('Warm_To_Closed_Date')->nullable();
            $table->string('Response', 50)->nullable();
            $table->string('Qualification', 50)->nullable();
            $table->string('Classification', 50)->nullable();
            $table->string('test', 100)->nullable();
            $table->string('Source_Channel', 100)->nullable();
            $table->string('Sales_ID', 150)->nullable();
            $table->string('Hub_Type', 100)->nullable();
            $table->Integer('Recommended_To_Approve')->nullable();
            $table->unique(['registration_token'], 'opportunity_registration_token_unique');
        });
        DB::statement("ALTER TABLE `opportunity` MODIFY `ID` int NOT NULL AUTO_INCREMENT");
        Schema::create('opportunity_detail', function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->increments('ID');
            $table->string('Task_ID', 100);
            $table->string('PO_No', 87)->nullable();
            $table->string('PO_Initial', 20)->nullable();
            $table->string('Project_Type', 50)->nullable();
            $table->date('PO_Date');
            $table->string('Contract_No', 50)->nullable();
            $table->string('Reference_No', 100)->nullable();
            $table->string('Customer_No', 67)->nullable();
            $table->mediumText('Description')->nullable();
            $table->mediumText('Department')->nullable();
            $table->string('Alocation', 150)->nullable();
            $table->string('Subject', 200)->nullable();
            $table->mediumText('Note')->nullable();
            $table->double('Subtotal', 30, 2)->default(0);
            $table->double('Discount', 30, 2)->default(0);
            $table->double('PPN', 30, 2)->default(0);
            $table->double('Total', 30, 2)->default(0);
            $table->double('PPH', 30, 2)->default(0);
            $table->double('Uang_Muka', 30, 2)->default(0);
            $table->string('Termin', 40)->nullable();
            $table->string('Pencatatan', 30)->nullable();
            $table->string('PO_PIC', 44)->nullable();
            $table->string('Email', 45)->nullable();
            $table->Integer('Due_Days')->default(0);
            $table->timestamp('Created_Date')->nullable();
            $table->string('Created_By', 30)->nullable();
            $table->string('Path_File', 100)->nullable();
            $table->date('Start_Date')->nullable()->default('2020-05-29');
            $table->date('End_Date')->nullable()->default('2021-05-29');
            $table->decimal('saldo_awal', 30, 2)->default(0);
        });
        DB::statement("ALTER TABLE `opportunity_detail` MODIFY `ID` int NOT NULL AUTO_INCREMENT");
        Schema::create('opportunity_detail_addendum', function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->increments('ID');
            $table->string('Task_ID', 100);
            $table->string('PO_No', 87)->nullable();
            $table->string('PO_Initial', 20)->nullable();
            $table->string('Project_Type', 50)->nullable();
            $table->date('PO_Date');
            $table->string('Contract_No', 50)->nullable();
            $table->string('Reference_No', 100)->nullable();
            $table->string('Customer_No', 67)->nullable();
            $table->mediumText('Description')->nullable();
            $table->string('Department', 60)->nullable();
            $table->string('Alocation', 150)->nullable();
            $table->string('Subject', 200)->nullable();
            $table->mediumText('Note')->nullable();
            $table->double('Subtotal', 30, 2)->default(0);
            $table->double('Discount', 30, 2)->default(0);
            $table->double('PPN', 30, 2)->default(0);
            $table->double('Total', 30, 2)->default(0);
            $table->double('PPH', 30, 2)->default(0);
            $table->double('Uang_Muka', 30, 2)->default(0);
            $table->string('Termin', 40)->nullable();
            $table->string('PO_PIC', 44)->nullable();
            $table->string('Email', 45)->nullable();
            $table->Integer('Due_Days')->default(0);
            $table->timestamp('Created_Date')->nullable();
            $table->string('Created_By', 30)->nullable();
            $table->string('Path_File', 100)->nullable();
            $table->date('Start_Date')->nullable()->default('2020-05-29');
            $table->date('End_Date')->nullable()->default('2021-05-29');
            $table->string('Addendum_By', 30)->nullable();
            $table->dateTime('Addendum_Date')->nullable();
        });
        DB::statement("ALTER TABLE `opportunity_detail_addendum` MODIFY `ID` int NOT NULL AUTO_INCREMENT");
        Schema::create('opportunity_detail_attachment', function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->increments('ID');
            $table->string('Task_ID', 70)->nullable();
            $table->string('Attachment_Name', 100)->nullable();
            $table->string('Mandatory', 200)->nullable();
            $table->Integer('Qty')->nullable();
            $table->string('Source_Doc', 40)->nullable();
            $table->string('Created_By', 40)->nullable();
            $table->timestamp('Created_Date')->nullable();
        });
        DB::statement("ALTER TABLE `opportunity_detail_attachment` MODIFY `ID` int NOT NULL AUTO_INCREMENT");
        Schema::create('opportunity_detail_material', function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->increments('ID');
            $table->string('Task_ID', 70)->nullable();
            $table->string('Product_Code', 100)->nullable();
            $table->string('Description', 200)->nullable();
            $table->Integer('Qty')->nullable();
            $table->double('Price', 30, 2)->nullable();
            $table->string('Created_By', 40)->nullable();
            $table->timestamp('Created_Date')->nullable();
            $table->string('Req_ID', 50)->nullable();
            $table->dateTime('Req_Date')->nullable();
            $table->string('Req_By', 30)->nullable();
        });
        DB::statement("ALTER TABLE `opportunity_detail_material` MODIFY `ID` int NOT NULL AUTO_INCREMENT");
        Schema::create('opportunity_log', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->increments('ID');
            $table->string('Task_ID', 255);
            $table->string('Location', 50);
            $table->date('Date');
            $table->string('Full_Name', 255);
            $table->string('Phone_Number', 255);
            $table->Integer('Partnership_Interest');
            $table->Integer('Capital_Estimation');
            $table->dateTime('Created_Date');
            $table->string('Created_By', 255);
            $table->string('Status', 255);
            $table->string('Reason', 255)->nullable();
            $table->string('Updated_By', 255);
            $table->dateTime('Updated_Date')->nullable()->useCurrent();
            $table->dateTime('FU_To_Qualified_Date')->nullable();
            $table->dateTime('Qualified_To_Warm_Date')->nullable();
            $table->dateTime('Warm_To_Closed_Date')->nullable();
            $table->string('Response', 50)->nullable();
            $table->string('Qualification', 50)->nullable();
            $table->string('Classification', 50)->nullable();
            $table->string('Action_By', 150);
            $table->dateTime('Action_Date');
            $table->string('Action_Status', 255);
            $table->Integer('Last_ID')->nullable();
            $table->string('test', 100)->nullable();
            $table->string('Source_Channel', 100)->nullable();
            $table->string('Sales_ID', 150)->nullable();
            $table->text('Hub_Type')->nullable();
            $table->TinyInteger('Recommended_To_Approve')->nullable()->default(0);
        });
        DB::statement("ALTER TABLE `opportunity_log` MODIFY `ID` int NOT NULL AUTO_INCREMENT");
    }

    public function down()
    {
        Schema::dropIfExists('opportunity_log');
        Schema::dropIfExists('opportunity_detail_material');
        Schema::dropIfExists('opportunity_detail_attachment');
        Schema::dropIfExists('opportunity_detail_addendum');
        Schema::dropIfExists('opportunity_detail');
        Schema::dropIfExists('opportunity');
    }
}
