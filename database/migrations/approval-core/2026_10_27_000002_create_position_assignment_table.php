<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePositionAssignmentTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('position_assignment')) {
            return;
        }

        Schema::create('position_assignment', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->unsignedBigInteger('Position_ID')->index();
            $table->unsignedBigInteger('User_Id')->index();
            $table->string('Emp_ID', 50)->nullable()->index();
            $table->string('Location', 50)->index();
            $table->string('Company_ID', 50)->index();
            $table->string('Department', 100)->nullable()->index();
            $table->string('Assignment_Type', 20)->default('PRIMARY');
            $table->date('Start_Date')->nullable();
            $table->date('End_Date')->nullable();
            $table->string('Status', 20)->default('ACTIVE')->index();
            $table->string('Created_By', 100)->nullable();
            $table->timestamp('Created_At')->useCurrent();
            $table->string('Updated_By', 100)->nullable();
            $table->timestamp('Updated_At')->nullable();

            $table->index(['Position_ID', 'Status'], 'position_assignment_position_status_index');
            $table->index(['User_Id', 'Status'], 'position_assignment_user_status_index');
            $table->index(['Location', 'Company_ID', 'Status'], 'position_assignment_scope_index');
        });
    }

    public function down()
    {
        Schema::dropIfExists('position_assignment');
    }
}