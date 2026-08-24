<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateBdpSalesAssignmentTables extends Migration
{
    public function up()
    {
        Schema::create('sales_person', function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->increments('ID');
            $table->string('NIK', 40);
            $table->string('Sales_Teritory', 30)->nullable();
            $table->string('Sales_Teritory_ID', 30)->nullable();
            $table->string('Sales_Name', 40)->nullable();
            $table->string('Sales_NIP', 40)->nullable();
            $table->string('Location', 30)->nullable();
            $table->string('Sales_Position', 40)->nullable();
            $table->double('Sales_Target', 30, 2)->nullable();
            $table->double('Sales_Revenue', 30, 2)->nullable();
            $table->mediumText('Note')->nullable();
            $table->string('UserID', 45)->nullable();
            $table->timestamp('DateTime')->useCurrent();
        });
        DB::statement("ALTER TABLE `sales_person` MODIFY `ID` int NOT NULL AUTO_INCREMENT");
        DB::statement("ALTER TABLE `sales_person` MODIFY `DateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        Schema::create('opportunity_target', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->increments('ID');
            $table->string('Sales', 150);
            $table->string('Month', 2);
            $table->Integer('Year');
            $table->Integer('Target');
            $table->string('Created_By', 150);
            $table->dateTime('Created_Date')->useCurrent();
        });
        DB::statement("ALTER TABLE `opportunity_target` MODIFY `ID` int NOT NULL AUTO_INCREMENT");
        Schema::create('opportunity_sales', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->increments('ID');
            $table->string('Location', 150);
            $table->string('Sales_ID', 150);
            $table->string('Sales', 150);
            $table->string('Status', 50);
            $table->Integer('Notification_Status')->default(0);
            $table->string('Created_By', 150);
            $table->dateTime('Created_Date');
        });
        DB::statement("ALTER TABLE `opportunity_sales` MODIFY `ID` int NOT NULL AUTO_INCREMENT");
        Schema::create('opportunity_sales_dtl', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->increments('ID');
            $table->string('Sales_ID', 150);
            $table->string('Sales', 150);
            $table->string('Task_ID', 150);
            $table->string('Created_By', 150);
            $table->dateTime('Created_Date');
        });
        DB::statement("ALTER TABLE `opportunity_sales_dtl` MODIFY `ID` int NOT NULL AUTO_INCREMENT");
        Schema::create('opportunity_sales_log', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->increments('ID');
            $table->string('Location', 150);
            $table->string('Sales_ID', 150);
            $table->string('Sales', 150);
            $table->string('Status', 50);
            $table->Integer('Notification_Status')->default(0);
            $table->string('Created_By', 150);
            $table->dateTime('Created_Date');
            $table->string('Action_By', 150);
            $table->dateTime('Action_Date');
            $table->string('Action_Status', 255);
            $table->Integer('Last_ID')->nullable();
        });
        DB::statement("ALTER TABLE `opportunity_sales_log` MODIFY `ID` int NOT NULL AUTO_INCREMENT");
        Schema::create('opportunity_sales_dtl_log', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->increments('ID');
            $table->string('Sales_ID', 150);
            $table->string('Sales', 150);
            $table->string('Task_ID', 150);
            $table->string('Created_By', 150);
            $table->dateTime('Created_Date');
            $table->string('Action_By', 150);
            $table->dateTime('Action_Date');
            $table->string('Action_Status', 255);
            $table->Integer('Last_ID')->nullable();
        });
        DB::statement("ALTER TABLE `opportunity_sales_dtl_log` MODIFY `ID` int NOT NULL AUTO_INCREMENT");
        Schema::create('sales_performance_ranking_logs', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->bigIncrements('ID');
            $table->BigInteger('User_Id')->nullable();
            $table->text('Current_Tier')->nullable();
            $table->dateTime('Created_At')->nullable();
        });
        DB::statement("ALTER TABLE `sales_performance_ranking_logs` MODIFY `ID` bigint NOT NULL AUTO_INCREMENT");
    }

    public function down()
    {
        Schema::dropIfExists('sales_performance_ranking_logs');
        Schema::dropIfExists('opportunity_sales_dtl_log');
        Schema::dropIfExists('opportunity_sales_log');
        Schema::dropIfExists('opportunity_sales_dtl');
        Schema::dropIfExists('opportunity_sales');
        Schema::dropIfExists('opportunity_target');
        Schema::dropIfExists('sales_person');
    }
}
