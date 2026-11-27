<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFinanceHrVoucherTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('finance_coa_mapping')) {
            Schema::create('finance_coa_mapping', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->string('Source_Module', 40);
                $table->string('Component_Key', 40);
                $table->string('Trial_Balance_Header_No', 30)->nullable();
                $table->string('Trial_Balance_Header_Name', 200)->nullable();
                $table->string('Acc_No', 30);
                $table->string('Acc_Name', 200)->nullable();
                $table->string('Default_Side', 10)->default('DEBET');
                $table->tinyInteger('Is_Active')->default(1);
                $table->string('Created_By', 50)->nullable();
                $table->string('Updated_By', 50)->nullable();
                $table->timestamp('Created_At')->nullable();
                $table->timestamp('Updated_At')->nullable();

                $table->unique(['Source_Module', 'Component_Key'], 'uq_fcm_source_component');
                $table->index('Acc_No', 'idx_fcm_acc_no');
            });
        }

        if (!Schema::hasTable('finance_hr_vouchers')) {
            Schema::create('finance_hr_vouchers', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->string('Voucher_No', 60)->unique('uq_fhv_voucher_no');
                $table->string('Source_Module', 40);
                $table->unsignedBigInteger('Source_ID')->nullable();
                $table->string('Source_No', 80)->nullable();
                $table->string('Location', 25)->default('GEU-HO');
                $table->string('Company_ID', 25)->nullable()->default('GEU');
                $table->string('Department', 50)->nullable();
                $table->string('Expense_Category', 80)->nullable();
                $table->string('Requested_Channel', 20)->default('DIRECT');
                $table->string('Final_Channel', 20)->nullable();
                $table->tinyInteger('Is_Converted_To_AP')->default(0);
                $table->string('Payment_Method', 20)->nullable();
                $table->string('Inv_Type', 15)->default('SINGLE');
                $table->string('PO_References', 150)->nullable();
                $table->string('PR_No', 60)->nullable();
                $table->string('Vendor_ID', 40)->nullable();
                $table->string('Vendor_Name', 150)->nullable();
                $table->string('PIC_Vendor', 80)->nullable();
                $table->string('Bank_Type', 30)->nullable();
                $table->string('Bank_Name', 80)->nullable();
                $table->string('Bank_Acc', 60)->nullable();
                $table->string('Bank_Acc_Name', 150)->nullable();
                $table->string('Reference', 150)->nullable();
                $table->date('Inv_Vendor_Date')->nullable();
                $table->date('Date_Received')->nullable();
                $table->date('Due_Date')->nullable();
                $table->string('Subject_Payment', 255)->nullable();
                $table->text('Description')->nullable();
                $table->string('Attachment_Path', 255)->nullable();
                $table->decimal('Subtotal', 18, 2)->default(0);
                $table->decimal('DP', 18, 2)->default(0);
                $table->decimal('Management_Fee', 18, 2)->default(0);
                $table->decimal('Delivery_Costs', 18, 2)->default(0);
                $table->string('PPN_Type', 20)->nullable()->default('0');
                $table->decimal('PPN', 18, 2)->default(0);
                $table->string('PPH_Type', 25)->nullable()->default('0');
                $table->decimal('PPH', 18, 2)->default(0);
                $table->decimal('Materai', 18, 2)->default(0);
                $table->decimal('Total', 18, 2)->default(0);
                $table->decimal('Paid', 18, 2)->default(0);
                $table->decimal('Remain_Val', 18, 2)->default(0);
                $table->string('Status', 30)->default('SUBMITTED');
                $table->text('Finance_Note')->nullable();
                $table->text('Reject_Reason')->nullable();
                $table->string('Legacy_Cash_No', 50)->nullable();
                $table->string('Legacy_Bank_No', 50)->nullable();
                $table->unsignedBigInteger('Legacy_AP_ID')->nullable();
                $table->string('Legacy_AP_Task_ID', 60)->nullable();
                $table->string('Journal_No', 60)->nullable();
                $table->string('Verified_By', 50)->nullable();
                $table->dateTime('Verified_At')->nullable();
                $table->string('Created_By', 50);
                $table->string('Updated_By', 50)->nullable();
                $table->timestamp('Created_At')->nullable();
                $table->timestamp('Updated_At')->nullable();

                $table->index(['Source_Module', 'Source_ID'], 'idx_fhv_source');
                $table->index('Status', 'idx_fhv_status');
                $table->index('Legacy_AP_ID', 'idx_fhv_legacy_ap');
                $table->index('PO_References', 'idx_fhv_po_ref');
            });
        }

        if (!Schema::hasTable('finance_hr_voucher_lines')) {
            Schema::create('finance_hr_voucher_lines', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->unsignedBigInteger('Voucher_ID');
                $table->integer('Line_Order')->default(1);
                $table->string('Trial_Balance_Header_No', 30)->nullable();
                $table->string('Trial_Balance_Header_Name', 200)->nullable();
                $table->string('Acc_No', 30);
                $table->string('Acc_Name', 200)->nullable();
                $table->string('Line_Side', 10);
                $table->string('Component_Tag', 40)->nullable();
                $table->decimal('Amount', 18, 2)->default(0);
                $table->string('Department', 50)->nullable();
                $table->string('Remark', 255)->nullable();
                $table->timestamp('Created_At')->nullable();
                $table->timestamp('Updated_At')->nullable();

                $table->index('Voucher_ID', 'idx_fhvl_voucher_id');
                $table->index('Acc_No', 'idx_fhvl_acc_no');
            });
        }

        if (!Schema::hasTable('finance_hr_voucher_tenors')) {
            Schema::create('finance_hr_voucher_tenors', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->unsignedBigInteger('Voucher_ID');
                $table->integer('Tenor_Seq')->default(1);
                $table->string('Tenor_Label', 80)->nullable();
                $table->date('Due_Date');
                $table->decimal('Amount', 18, 2)->default(0);
                $table->decimal('Paid_Amount', 18, 2)->default(0);
                $table->string('Status', 20)->default('OPEN');
                $table->string('Note', 255)->nullable();
                $table->timestamp('Created_At')->nullable();
                $table->timestamp('Updated_At')->nullable();

                $table->index('Voucher_ID', 'idx_fhvt_voucher_id');
                $table->index('Due_Date', 'idx_fhvt_due_date');
            });
        }

        if (!Schema::hasTable('finance_hr_voucher_payments')) {
            Schema::create('finance_hr_voucher_payments', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->unsignedBigInteger('Voucher_ID');
                $table->unsignedBigInteger('Tenor_ID')->nullable();
                $table->date('Payment_Date');
                $table->string('Payment_Channel', 20);
                $table->string('Payment_Acc_No', 30);
                $table->string('Payment_Acc_Name', 200)->nullable();
                $table->string('Bank_Acc_Ref', 50)->nullable();
                $table->decimal('Amount', 18, 2)->default(0);
                $table->string('Payment_Ref', 100)->nullable();
                $table->string('Proof_Path', 255)->nullable();
                $table->string('Legacy_Cash_No', 50)->nullable();
                $table->string('Legacy_Bank_No', 50)->nullable();
                $table->string('Journal_No', 60)->nullable();
                $table->text('Note')->nullable();
                $table->string('Created_By', 50);
                $table->timestamp('Created_At')->nullable();

                $table->index('Voucher_ID', 'idx_fhvp_voucher_id');
                $table->index('Tenor_ID', 'idx_fhvp_tenor_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('finance_hr_voucher_payments');
        Schema::dropIfExists('finance_hr_voucher_tenors');
        Schema::dropIfExists('finance_hr_voucher_lines');
        Schema::dropIfExists('finance_hr_vouchers');
        Schema::dropIfExists('finance_coa_mapping');
    }
}
