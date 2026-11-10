<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCnsPurchaseSummaryCnsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('purchase_summary_cns')) {
            return; // already provisioned on the live database
        }

        Schema::create('purchase_summary_cns', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('Task_ID', 60)->nullable();
            $table->string('PR_No', 50)->nullable();
            $table->string('Purchase_Category', 100)->nullable();
            $table->string('Purchase_Sub_Category', 100)->nullable();
            $table->string('Purchase_Type', 100)->nullable();
            $table->date('Purchase_Date')->nullable();
            $table->string('Status', 30)->default('CREATED');
            $table->string('V1_ID', 30)->nullable();
            $table->string('V1_Cur', 4)->default('Rp');
            $table->integer('V1_Fee_Mgt')->default(0);
            $table->string('V1_VAT', 10)->default('0');
            $table->integer('V1_Fee_VAT')->default(0);
            $table->integer('V1_PPH')->default(0);
            $table->double('V1_DC', 30, 0)->default(0);
            $table->double('V1_Discount', 30, 0)->default(0);
            $table->mediumText('V1_Note')->nullable();
            $table->double('V1_Total', 50, 2)->default(0.00);
            $table->string('V2_ID', 30)->nullable();
            $table->string('V2_Cur', 4)->default('Rp');
            $table->integer('V2_Fee_Mgt')->default(0);
            $table->integer('V2_VAT')->default(0);
            $table->integer('V2_Fee_VAT')->default(0);
            $table->integer('V2_PPH')->default(0);
            $table->double('V2_DC', 30, 0)->default(0);
            $table->double('V2_Discount', 30, 0)->default(0);
            $table->mediumText('V2_Note')->nullable();
            $table->double('V2_Total', 50, 2)->default(0.00);
            $table->string('V3_ID', 30)->nullable();
            $table->string('V3_Cur', 4)->default('Rp');
            $table->integer('V3_Fee_Mgt')->default(0);
            $table->integer('V3_VAT')->default(0);
            $table->integer('V3_Fee_VAT')->default(0);
            $table->integer('V3_PPH')->default(0);
            $table->double('V3_DC', 30, 0)->default(0);
            $table->double('V3_Discount', 30, 0)->default(0);
            $table->mediumText('V3_Note')->nullable();
            $table->double('V3_Total', 50, 2)->default(0.00);
            $table->string('V4_ID', 30)->nullable();
            $table->string('V4_Cur', 4)->default('Rp');
            $table->integer('V4_Fee_Mgt')->default(0);
            $table->integer('V4_VAT')->default(0);
            $table->integer('V4_Fee_VAT')->default(0);
            $table->integer('V4_PPH')->default(0);
            $table->double('V4_DC', 30, 0)->default(0);
            $table->double('V4_Discount', 30, 0)->nullable()->default(0);
            $table->mediumText('V4_Note')->nullable();
            $table->double('V4_Total', 50, 2)->default(0.00);
            $table->string('V5_ID', 30)->nullable();
            $table->string('V5_Cur', 4)->default('Rp');
            $table->integer('V5_Fee_Mgt')->default(0);
            $table->integer('V5_VAT')->default(0);
            $table->integer('V5_Fee_VAT')->default(0);
            $table->integer('V5_PPH')->default(0);
            $table->double('V5_DC', 30, 0)->default(0);
            $table->double('V5_Discount', 30, 0)->default(0);
            $table->mediumText('V5_Note')->nullable();
            $table->double('V5_Total', 50, 2)->default(0.00);
            $table->timestamp('Created_Date')->default(\Illuminate\Support\Facades\DB::raw('CURRENT_TIMESTAMP'));
            $table->string('Created_By', 50)->nullable();
            $table->string('Location', 30)->default('PSS-HO');
            $table->mediumText('Note')->nullable();
            $table->string('Priority', 30)->default('NORMAL');
            $table->mediumText('Priority_Note')->nullable();
            $table->string('Priority_By', 30)->nullable();
            $table->string('Update_By', 30)->nullable();
            $table->string('Active_Progress', 30)->nullable();
            $table->dateTime('TimeOut_Progress')->nullable();
            $table->integer('print_count')->default(0);
            $table->integer('Qty_Request')->default(0);
            $table->integer('Qty_Acc')->default(0);
            $table->string('Received_Status', 10)->nullable();
            $table->mediumText('note_perbaikan')->nullable();

        });
    }

    public function down()
    {
        // Intentionally left empty (additive/guarded; never drops live tables).
    }
}
