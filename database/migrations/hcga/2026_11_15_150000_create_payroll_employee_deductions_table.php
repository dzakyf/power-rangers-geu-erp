<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollEmployeeDeductionsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('payroll_employee_deductions')) {
            Schema::create('payroll_employee_deductions', function (Blueprint $table) {
                $table->increments('id');
                $table->string('nik', 50);
                $table->string('salary_period', 7);
                $table->string('main_id', 100)->nullable();
                $table->enum('deduction_type', [
                    'SPPD_UNSETTLED',
                    'KASBON_SPPD_EXCESS',
                    'KASBON_MANUAL',
                    'REIMBURSE',
                    'OTHER',
                ]);
                $table->string('source_table', 50)->nullable();
                $table->unsignedBigInteger('source_id')->nullable();
                $table->string('reference_no', 100)->nullable();
                $table->string('description', 255);
                $table->decimal('original_amount', 15, 2)->default(0);
                $table->decimal('remaining_amount', 15, 2)->default(0);
                $table->decimal('deducted_amount', 15, 2)->default(0);
                $table->boolean('is_auto_pulled')->default(false);
                $table->boolean('is_included')->default(true);
                $table->enum('status', ['DRAFT', 'POSTED', 'CANCELLED'])->default('DRAFT');
                $table->string('payroll_task_id', 100)->nullable();
                $table->string('created_by', 100)->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->timestamps();

                $table->index(['nik', 'salary_period'], 'idx_ped_nik_period');
                $table->index(['source_table', 'source_id'], 'idx_ped_source');
                $table->index(['status'], 'idx_ped_status');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('payroll_employee_deductions');
    }
}
