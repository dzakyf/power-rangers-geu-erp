<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCnsPurchaseRequestTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('purchase_request')) {
            return; // already provisioned on the live database
        }

        Schema::create('purchase_request', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('Location', 30)->nullable();
            $table->string('Process_By', 15)->default('HO');
            $table->string('Supplies_Type', 40);
            $table->string('Category', 50)->nullable();
            $table->string('Sub_Category', 100)->nullable();
            $table->string('PIC', 255)->nullable();
            $table->text('PIC_Address')->nullable();
            $table->string('PIC_Phone', 30)->nullable();
            $table->string('PR_No', 100)->nullable();
            $table->date('PR_Date')->nullable();
            $table->string('Request_By', 30)->nullable();
            $table->string('Department', 30);
            $table->string('Department_Request', 60)->nullable();
            $table->string('Dept_Authority', 60)->nullable();
            $table->string('Status', 40)->default('CREATED');
            $table->string('Reference', 30)->nullable();
            $table->mediumText('Note')->nullable();
            $table->timestamp('Created_Date')->default(\Illuminate\Support\Facades\DB::raw('CURRENT_TIMESTAMP'));
            $table->string('Created_By', 55)->nullable();
            $table->string('Purchase_Sub', 20)->nullable();
            $table->string('PO_Task_No', 50)->nullable();
            $table->string('PO_No', 50)->nullable();
            $table->string('Processing_Status', 50)->nullable();
            $table->dateTime('Processing_Date')->nullable();
            $table->string('PO_Status', 34)->default('NORMAL');
            $table->text('PO_Status_Note')->nullable();
            $table->dateTime('PO_Status_Date')->nullable();
            $table->string('PO_Status_By', 30)->nullable();
            $table->string('Priority_Status', 30)->default('UNDEFINED');
            $table->text('Priority_Note')->nullable();
            $table->string('Priority_By', 30)->nullable();
            $table->string('User_Request', 30)->nullable();
            $table->string('User_Request_Name', 40)->nullable();
            $table->double('Estimated_Budget', 30, 2)->default(0.00);
            $table->string('Approval_Department', 30)->nullable();
            $table->dateTime('Approval_Date')->nullable();
            $table->string('Approval_User', 30)->nullable();
            $table->integer('Creator_Level')->default(0);
            $table->string('Supplier_ID', 255);
            $table->string('Supplier_Name', 255);
            $table->string('Remark', 255);
            $table->index(['PR_No'], 'PR_No');
        });
    }

    public function down()
    {
        // Intentionally left empty (additive/guarded; never drops live tables).
    }
}
