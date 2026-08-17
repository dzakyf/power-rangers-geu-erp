<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApprovalRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('approval_request', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('Status', 50)->index();
            $table->unsignedBigInteger('Requester_Id')->index();
            $table->text('Description');
            $table->string('Warehouse_Task_Id', 150)->nullable();
            $table->string('Periode', 7)->nullable();
            $table->string('Request_Type', 100)->index();
            $table->unsignedBigInteger('Opportunity_Partner_Id')->nullable()->index();
            $table->unsignedBigInteger('Map_To')->nullable()->index();
            $table->integer('Current_Level')->default(0);
            $table->unsignedBigInteger('Level_Id')->nullable();
            $table->timestamp('Submitted_At')->nullable();
            $table->timestamp('Created_At')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approval_request');
    }
}
