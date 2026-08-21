<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettlementSppdTables extends Migration
{
    public function up()
    {
        Schema::create('settlement_sppd', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('STTL_No', 100);
            $table->integer('Requester_ID');
            $table->string('Requester_Name', 150);
            $table->string('Requester_NIK', 50)->nullable();
            $table->string('Department', 150);
            $table->integer('Invoice_File')->nullable();
            $table->decimal('Advance_Nominal', 15, 2)->nullable();
            $table->string('Bank', 100)->nullable();
            $table->string('Status', 50)->default('SUBMITTED');
            $table->integer('Validator_ID')->nullable();
            $table->string('Validator_Name', 150)->nullable();
            $table->text('Approval_Note')->nullable();
            $table->timestamp('Approved_At')->nullable();
            $table->timestamp('Closed_At')->nullable();
            $table->string('Created_By', 100)->nullable();
            $table->timestamp('Created_Date')->nullable();
            $table->string('Updated_By', 100)->nullable();
            $table->timestamp('Updated_Date')->nullable();
            $table->index('STTL_No');
            $table->index('Status');
            $table->index('Validator_ID');
        });

        Schema::create('settlement_sppd_expense', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('Settlement_Sppd_ID');
            $table->string('Expense', 255)->nullable();
            $table->decimal('Cost', 15, 2)->default(0);
            $table->string('Description', 255)->nullable();
            $table->index('Settlement_Sppd_ID');
        });

        Schema::create('settlement_sppd_log', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('Settlement_Sppd_ID');
            $table->string('Action', 50);
            $table->integer('Actor_ID')->nullable();
            $table->string('Actor_Name', 150)->nullable();
            $table->text('Note')->nullable();
            $table->timestamp('Created_At')->nullable();
            $table->index('Settlement_Sppd_ID');
        });
    }

    public function down()
    {
        Schema::dropIfExists('settlement_sppd_log');
        Schema::dropIfExists('settlement_sppd_expense');
        Schema::dropIfExists('settlement_sppd');
    }
}
