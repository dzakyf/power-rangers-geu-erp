<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_request', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('PR_No', 60)->index();
            $table->date('PR_Date')->nullable();
            $table->string('Category', 50)->nullable();
            $table->string('Sub_Category', 50)->nullable();
            $table->string('Supplies_Type', 50)->nullable();
            $table->string('Reference', 100)->nullable();
            $table->string('Department', 100)->nullable();
            $table->string('Status', 30)->index();
            $table->string('Created_By', 100)->nullable();
            $table->string('Location', 50)->index();
            $table->timestamp('Approval_Date')->nullable();
            $table->integer('Creator_Level')->default(1)->nullable();
            $table->text('Note')->nullable();
            $table->string('Approval_Department', 100)->nullable();
            $table->string('PO_No', 60)->nullable();
            $table->string('PO_Task_No', 60)->nullable();
            $table->string('Processing_Status', 50)->nullable();
            $table->timestamp('Processing_Date')->nullable();
            $table->decimal('Estimated_Budget', 15, 2)->nullable();
            $table->string('PIC', 100)->nullable();
            $table->text('PIC_Address')->nullable();
            $table->string('PIC_Phone', 50)->nullable();
            $table->string('Priority_Status', 20)->nullable();
            $table->text('Priority_Note')->nullable();
            $table->string('Priority_By', 100)->nullable();
            $table->string('User_Request', 50)->nullable();
            $table->string('User_Request_Name', 150)->nullable();
            $table->string('Supplier_ID', 50)->nullable();
            $table->string('Supplier_Name', 150)->nullable();
            $table->text('Remark')->nullable();
            $table->timestamp('Created_Date')->useCurrent();
            $table->string('PO_Status', 30)->nullable();
            $table->string('PO_Status_Request', 30)->nullable();
            $table->string('Priority', 20)->nullable();
            $table->string('Phone', 50)->nullable();
            $table->string('Purchase_Type', 50)->nullable();
            $table->string('Purchase_Category', 50)->nullable();
            $table->string('User_Trx', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_request');
    }
}
