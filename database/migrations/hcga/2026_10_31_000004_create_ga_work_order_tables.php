<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGaWorkOrderTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('ga_work_order')) {
            Schema::create('ga_work_order', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->string('WO_No', 60)->unique();
                $table->integer('Ticket_ID')->nullable()->index();
                $table->string('Location', 50)->nullable()->index();
                $table->string('Company_ID', 50)->nullable()->index();
                $table->string('Title', 200);
                $table->text('Description')->nullable();
                $table->string('Assignee_Type', 20)->nullable();
                $table->unsignedBigInteger('Team_ID')->nullable()->index();
                $table->unsignedBigInteger('User_Id')->nullable()->index();
                $table->unsignedBigInteger('Supplier_ID')->nullable()->index();
                $table->date('Planned_Start')->nullable();
                $table->date('Planned_End')->nullable();
                $table->dateTime('Actual_Start')->nullable();
                $table->dateTime('Actual_End')->nullable();
                $table->decimal('Vendor_Cost', 18, 2)->default(0);
                $table->string('Cost_Center', 50)->nullable();
                $table->string('Department', 100)->nullable()->index();
                $table->string('Status', 30)->default('DRAFT')->index();
                $table->unsignedBigInteger('Approval_Request_ID')->nullable()->index();
                $table->text('Result_Note')->nullable();
                $table->string('Created_By', 100)->nullable();
                $table->timestamp('Created_At')->useCurrent();
                $table->string('Updated_By', 100)->nullable();
                $table->timestamp('Updated_At')->nullable();
            });
        }

        if (!Schema::hasTable('ga_work_order_material')) {
            Schema::create('ga_work_order_material', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->unsignedBigInteger('WO_ID')->index();
                $table->string('Stock_Request_No', 60)->nullable()->index();
                $table->string('Product_Code', 60)->nullable();
                $table->string('Product_Desc', 200)->nullable();
                $table->decimal('Qty', 18, 2)->default(0);
                $table->string('Unit', 25)->nullable();
                $table->decimal('Unit_Cost', 18, 2)->default(0);
                $table->decimal('Total_Cost', 18, 2)->default(0);
                $table->timestamp('Created_At')->useCurrent();
            });
        }

        if (!Schema::hasTable('ga_work_order_cost')) {
            Schema::create('ga_work_order_cost', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->unsignedBigInteger('WO_ID')->index();
                $table->string('Cost_Type', 30)->index();
                $table->string('Description', 200)->nullable();
                $table->decimal('Amount', 18, 2)->default(0);
                $table->string('Cost_Center', 50)->nullable();
                $table->string('Department', 100)->nullable();
                $table->string('Source_Type', 30)->nullable();
                $table->string('Source_ID', 60)->nullable();
                $table->string('Created_By', 100)->nullable();
                $table->timestamp('Created_At')->useCurrent();
            });
        }

        if (!Schema::hasTable('ga_work_order_status_history')) {
            Schema::create('ga_work_order_status_history', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->unsignedBigInteger('WO_ID')->index();
                $table->string('From_Status', 30)->nullable();
                $table->string('To_Status', 30);
                $table->text('Note')->nullable();
                $table->string('Changed_By', 100)->nullable();
                $table->dateTime('Changed_At');
                $table->timestamp('Created_At')->useCurrent();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ga_work_order_status_history');
        Schema::dropIfExists('ga_work_order_cost');
        Schema::dropIfExists('ga_work_order_material');
        Schema::dropIfExists('ga_work_order');
    }
}
