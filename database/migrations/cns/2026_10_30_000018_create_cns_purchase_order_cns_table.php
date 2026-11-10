<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCnsPurchaseOrderCnsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('purchase_order_cns')) {
            return; // already provisioned on the live database
        }

        Schema::create('purchase_order_cns', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('Task_ID', 60)->nullable();
            $table->string('Term_Payment', 255)->nullable();
            $table->string('Location', 30)->nullable();
            $table->string('Process_By', 30)->default('HO');
            $table->string('Purchase_Type', 100)->nullable();
            $table->string('Purchase_Category', 30)->nullable();
            $table->string('Purchase_Sub_Category', 50)->nullable();
            $table->string('PR_No', 50)->nullable();
            $table->string('PO_No', 50)->nullable();
            $table->mediumText('Subject_Payment')->nullable();
            $table->string('PO_No_KSPS', 50)->nullable();
            $table->date('PO_Date')->nullable();
            $table->string('PO_Method', 30)->nullable();
            $table->string('PO_Dest', 20)->nullable();
            $table->string('Department', 30)->nullable();
            $table->mediumText('PIC_Address')->nullable();
            $table->string('PIC_Phone', 30)->nullable();
            $table->string('PIC', 30)->nullable();
            $table->string('Supplier', 30)->nullable();
            $table->dateTime('ETD_B')->nullable();
            $table->string('Supplier_PIC', 30)->nullable();
            $table->string('Shipment_Method', 30)->nullable();
            $table->string('Pay_Meth', 25)->nullable();
            $table->string('Pay_Term', 30)->nullable();
            $table->double('Advance_Value', 20, 2)->nullable();
            $table->double('Subtotal', 20, 2)->nullable();
            $table->double('Discount', 30, 0)->nullable();
            $table->double('PPN', 20, 2)->nullable();
            $table->double('PPH', 20, 2)->nullable();
            $table->double('Delivery_Fee', 40, 0)->nullable();
            $table->double('Management_Fee', 30, 0)->default(0);
            $table->integer('Management_Fee_Exclude_PPN')->nullable();
            $table->double('Surcharge', 40, 0)->nullable();
            $table->double('Total', 20, 2)->nullable();
            $table->double('Total_Paid', 20, 2)->default(0.00);
            $table->string('Status', 25)->default('CREATED');
            $table->longText('Remark')->nullable();
            $table->dateTime('Date_Trx')->nullable();
            $table->string('User_Trx', 20)->nullable();
            $table->string('Status_Receive', 30)->default('WAITING');
            $table->dateTime('Received_Date')->nullable();
            $table->string('GR_No', 50)->nullable();
            $table->date('GR_Date')->nullable();
            $table->string('Last_Sign', 30)->nullable();
            $table->integer('print_count')->default(0);
            $table->string('tipeInv', 10)->nullable();
            $table->boolean('Closed_Inv')->default(0);
            $table->boolean('Confirmed')->default(0);
            $table->string('Confirmed_By', 60)->nullable();
            $table->dateTime('Confirmed_Date')->nullable();
            $table->string('Currency', 6)->default('RP');
            $table->date('TglKirimPO')->nullable();
            $table->date('TglPrediksiDatang')->nullable();
            $table->date('Invoice_Received_Date')->nullable();
            $table->mediumText('remark_optional')->nullable();
            $table->date('Delivery_Date');
            $table->string('Delivery_Site', 255);
            $table->string('Delivery_Term', 255);
            $table->string('Shipped_By', 255);
            $table->index(['PO_No'], 'PO_No');
            $table->index(['PO_No', 'Supplier'], 'PO_No_2');
        });
    }

    public function down()
    {
        // Intentionally left empty (additive/guarded; never drops live tables).
    }
}
