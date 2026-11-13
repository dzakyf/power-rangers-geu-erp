<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExitClearanceLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('exit_clearance_logs')) {
            Schema::create('exit_clearance_logs', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('Exit_Clearance_ID')->nullable()->index();
                $table->unsignedInteger('Resignation_ID')->nullable()->index();
                $table->string('Action', 100);
                $table->string('Title', 255)->nullable();
                $table->text('Description')->nullable();
                $table->string('Previous_Status', 50)->nullable();
                $table->string('New_Status', 50)->nullable();
                $table->unsignedInteger('Actor_ID')->nullable();
                $table->string('Actor_Name', 150)->nullable();
                $table->string('Actor_Role', 100)->nullable();
                $table->string('Ip_Address', 45)->nullable();
                $table->text('User_Agent')->nullable();
                $table->json('Payload')->nullable();
                $table->timestamp('created_at')->useCurrent();
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
        Schema::dropIfExists('exit_clearance_logs');
    }
}

