<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeDataUpdateRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('employee_data_update_requests')) {
            Schema::create('employee_data_update_requests', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('request_no', 50)->unique();
                $table->string('task_id', 50)->index(); // employment.Task_ID
                $table->string('nik', 50)->index();
                $table->string('user_id', 50); // users.username
                $table->string('field_category', 50); // PROFILE, CONTACT, BANK, FAMILY, EDUCATION, etc.
                $table->string('field_name', 100);
                $table->text('old_value')->nullable();
                $table->text('new_value')->nullable();
                $table->text('reason')->nullable();
                $table->string('attachment', 255)->nullable();
                $table->string('status', 30)->default('WAITING_APPROVAL'); // WAITING_APPROVAL, APPROVED, REJECTED
                $table->unsignedBigInteger('approval_request_id')->nullable()->index();
                $table->string('approved_by', 50)->nullable();
                $table->dateTime('approved_at')->nullable();
                $table->text('approval_note')->nullable();
                $table->timestamps();
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
        Schema::dropIfExists('employee_data_update_requests');
    }
}

