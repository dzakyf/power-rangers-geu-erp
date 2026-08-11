<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterHrApprovalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_hr_approval', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('Location', 50)->index();
            $table->string('Directorat', 100);
            $table->string('Department', 100)->index();
            $table->string('Name', 150);
            $table->string('username', 100)->index();
            $table->string('Level', 50);
            $table->text('Menu_List');
            $table->string('Status', 20)->default('ACTIVE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('master_hr_approval');
    }
}
