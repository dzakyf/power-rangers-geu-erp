<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCnsPurchaseSummaryCnsDetailTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('purchase_summary_cns_detail')) {
            return; // already provisioned on the live database
        }

        Schema::create('purchase_summary_cns_detail', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('Task_ID', 60)->nullable();
            $table->string('Purchase_Category', 100)->nullable();
            $table->string('Purchase_Sub_Category', 100)->nullable();
            $table->string('Purchase_Type', 100)->nullable();
            $table->string('Product_Code', 100)->nullable();
            $table->string('Product_Desc', 255);
            $table->double('Default_Price', 30, 2)->nullable();
            $table->double('Qty', 40, 2)->nullable();
            $table->double('Proposed', 50, 2)->nullable();
            $table->mediumText('Specification')->nullable();
            $table->string('Unit_Name', 100)->nullable();
            $table->mediumText('Recommended_Spec')->nullable();
            $table->double('V1_Propose', 50, 2)->nullable();
            $table->double('V1_TOP', 30, 2)->nullable();
            $table->integer('V1_Win')->nullable()->default(1);
            $table->double('V2_Propose', 50, 2)->nullable();
            $table->double('V2_TOP', 30, 2)->nullable();
            $table->integer('V2_Win')->nullable();
            $table->double('V3_Propose', 50, 2)->nullable();
            $table->double('V3_TOP', 30, 2)->nullable();
            $table->integer('V3_Win')->nullable();
            $table->double('V4_Propose', 50, 2)->nullable();
            $table->double('V4_TOP', 30, 2)->nullable();
            $table->integer('V4_Win')->nullable();
            $table->double('V5_Propose', 50, 2)->nullable();
            $table->double('V5_TOP', 30, 2)->nullable();
            $table->integer('V5_Win')->nullable();
            $table->mediumText('Note')->nullable();
            $table->timestamp('Created_Date')->default(\Illuminate\Support\Facades\DB::raw('CURRENT_TIMESTAMP'));
            $table->string('Created_By', 30)->nullable();
            $table->string('Location', 35)->nullable();
            $table->mediumText('PR_Related')->nullable();
            $table->string('Status_Request', 30)->default('NORMAL');
            $table->mediumText('Note_Request')->nullable();
            $table->double('Received', 50, 2)->nullable();

        });
    }

    public function down()
    {
        // Intentionally left empty (additive/guarded; never drops live tables).
    }
}
