<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountPayableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_payable', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('Task_ID', 60)->index();
            $table->string('Location', 50)->nullable()->index();
            $table->string('Department', 100)->nullable();
            $table->string('PIC', 100)->nullable();
            $table->string('Purchase_Category', 50)->nullable();
            $table->string('Purchase_Sub_Category', 50)->nullable();
            $table->string('Purchase_Type', 50)->nullable();
            $table->string('Reference', 100)->nullable();
            $table->text('Description')->nullable();
            $table->decimal('Subtotal', 18, 2)->nullable();
            $table->decimal('PPN', 18, 2)->nullable();
            $table->string('PPN_Type', 20)->nullable();
            $table->decimal('PPH', 18, 2)->nullable();
            $table->decimal('Total', 18, 2)->nullable();
            $table->decimal('Remain_Val', 18, 2)->nullable();
            $table->string('Status', 30)->index();
            $table->timestamp('Dateinsert')->useCurrent();
            $table->string('Created_By', 100)->nullable();
            $table->date('Date_Received')->nullable();
            $table->string('Received_By', 100)->nullable();
            $table->string('Inv_Type', 20)->nullable();
            $table->date('Due_Date')->nullable();
            $table->string('Inv_Vendor', 100)->nullable();
            $table->text('Subject_Payment')->nullable();
            $table->date('Inv_Vendor_Date')->nullable();
            $table->string('Vendor_ID', 50)->nullable();
            $table->string('Vendor_Name', 150)->nullable();
            $table->string('Finance_Ref', 100)->nullable();
            $table->timestamp('Finance_Received_Date')->nullable();
            $table->string('Finance_Received_By', 100)->nullable();
            $table->string('Manual', 20)->nullable();
            $table->decimal('Paid', 18, 2)->nullable()->default(0);
            $table->string('PIC_Phone', 50)->nullable();
            $table->text('Note')->nullable();
            $table->string('PIC_Paid', 20)->nullable();
            $table->timestamp('Last_Date_Payment')->nullable();
            $table->string('PO_References', 100)->nullable();
            $table->string('PPH_Type', 20)->nullable();
            $table->decimal('PPH23_4', 18, 2)->nullable();
            $table->decimal('PPH23_2', 18, 2)->nullable();
            $table->decimal('PPH23_15', 18, 2)->nullable();
            $table->decimal('PPH42', 18, 2)->nullable();
            $table->decimal('PPH_21_5', 18, 2)->nullable();
            $table->decimal('DP', 18, 2)->nullable();
            $table->decimal('Management_Fee', 18, 2)->nullable();
            $table->decimal('Delivery_Costs', 18, 2)->nullable();
            $table->string('Currency', 10)->nullable()->default('RP');
            $table->decimal('Currency_Kurs', 18, 2)->nullable();
            $table->decimal('Currency_Value', 18, 2)->nullable();
            $table->string('Revisi_Journ', 50)->nullable();
            $table->string('Status_Approval', 30)->nullable();
            $table->string('COA_Approval', 100)->nullable();
            $table->string('File_Document', 255)->nullable();
            $table->string('Bank_Name', 100)->nullable();
            $table->string('Bank_Acc', 100)->nullable();
            $table->string('Bank_Acc_Name', 150)->nullable();
            $table->string('Bank_Type', 20)->nullable();
            $table->string('Payment_Type', 30)->nullable();
            $table->string('Payment_Reference', 100)->nullable();
            $table->string('Payment_Acc_No', 100)->nullable();
            $table->text('Payment_Note')->nullable();
            $table->decimal('materai', 18, 2)->nullable();
            $table->unsignedBigInteger('Opportunity_Id')->nullable()->index();
            $table->text('Url_Transfer')->nullable();
            $table->timestamp('Last_Update')->nullable();
            $table->string('PR_No', 60)->nullable();
            $table->string('Receive', 20)->nullable();
            $table->string('Priority', 20)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account_payable');
    }
}
