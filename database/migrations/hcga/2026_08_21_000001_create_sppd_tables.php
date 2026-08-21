<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSppdTables extends Migration
{
    public function up()
    {
        Schema::create('sppd', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('SPPD_No', 100);
            $table->integer('Requester_ID');
            $table->string('Requester_Name', 150);
            $table->string('Requester_NIK', 50)->nullable();
            $table->string('Department', 150);
            $table->string('Destination', 255);
            $table->string('Route_Start', 150);
            $table->string('Route_Finish', 150);
            $table->text('Description')->nullable();
            $table->date('Schedule_Date')->nullable();
            $table->string('Schedule_Time', 20)->nullable();
            $table->string('Program_Name', 255)->nullable();
            $table->decimal('Estimated_Cost', 15, 2)->default(0);
            $table->string('Status', 50)->default('SUBMITTED');
            $table->integer('Approver_ID')->nullable();
            $table->string('Approver_Name', 150)->nullable();
            $table->text('Approval_Note')->nullable();
            $table->timestamp('Approved_At')->nullable();
            $table->timestamp('Closed_At')->nullable();
            $table->string('Created_By', 100)->nullable();
            $table->timestamp('Created_Date')->nullable();
            $table->string('Updated_By', 100)->nullable();
            $table->timestamp('Updated_Date')->nullable();
            $table->index('SPPD_No');
            $table->index('Status');
            $table->index('Approver_ID');
        });

        Schema::create('sppd_cost', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('SPPD_ID');
            $table->decimal('Cost', 15, 2)->default(0);
            $table->string('Description', 255)->nullable();
            $table->index('SPPD_ID');
        });

        Schema::create('sppd_log', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('SPPD_ID');
            $table->string('Action', 50);
            $table->integer('Actor_ID')->nullable();
            $table->string('Actor_Name', 150)->nullable();
            $table->text('Note')->nullable();
            $table->timestamp('Created_At')->nullable();
            $table->index('SPPD_ID');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sppd_log');
        Schema::dropIfExists('sppd_cost');
        Schema::dropIfExists('sppd');
    }
}
