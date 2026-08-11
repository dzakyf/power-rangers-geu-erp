<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApprovalLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('approval_log', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->unsignedBigInteger('Request_Id')->index();
            $table->unsignedBigInteger('Approver_Id')->index();
            $table->string('Action', 50);
            $table->text('Notes')->nullable();
            $table->integer('Level_Number');
            $table->integer('Next_Level')->nullable();
            $table->unsignedBigInteger('Level_Id')->nullable();
            $table->unsignedBigInteger('Next_Level_Id')->nullable();
            $table->timestamp('Action_Date')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approval_log');
    }
}
