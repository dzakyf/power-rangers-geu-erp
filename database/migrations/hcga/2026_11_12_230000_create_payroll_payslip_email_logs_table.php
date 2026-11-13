<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollPayslipEmailLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('payroll_payslip_email_logs')) {
            Schema::create('payroll_payslip_email_logs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('task_id', 100)->nullable()->index();
                $table->string('salary_period', 20)->index();
                $table->string('nik', 50)->index();
                $table->string('recipient_email', 150);
                $table->string('mode', 20)->default('FINAL')->comment('PREVIEW / FINAL');
                $table->string('status', 20)->comment('SENT / FAILED / SKIPPED');
                $table->text('error_message')->nullable();
                $table->string('sent_by', 50)->nullable();
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
        Schema::dropIfExists('payroll_payslip_email_logs');
    }
}

