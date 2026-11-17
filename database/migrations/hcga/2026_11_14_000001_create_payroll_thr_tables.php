<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePayrollThrTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('payroll_thr_batches')) {
            Schema::create('payroll_thr_batches', function (Blueprint $table) {
                $table->increments('id');
                $table->string('batch_no', 50)->unique();
                $table->string('salary_period', 10)->index(); // YYYY-MM
                $table->string('thr_event_name', 150);
                $table->date('reference_date');
                $table->string('religion_filter', 50)->default('ALL');
                $table->string('main_id_filter', 50)->nullable();
                $table->string('prorate_method', 30)->default('MONTH_FLOOR'); // MONTH_FLOOR | DAY_365
                $table->string('status', 20)->default('DRAFT')->index(); // DRAFT | LOCKED | POSTED
                $table->integer('total_employees')->default(0);
                $table->decimal('total_amount', 15, 2)->default(0);
                $table->text('note')->nullable();
                $table->string('created_by', 100)->nullable();
                $table->string('locked_by', 100)->nullable();
                $table->dateTime('locked_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('payroll_thr_items')) {
            Schema::create('payroll_thr_items', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('batch_id')->index();
                $table->string('nik', 50)->index();
                $table->string('employee_name', 150)->nullable();
                $table->string('department', 100)->nullable();
                $table->string('main_id', 50)->nullable();
                $table->string('religion', 50)->nullable();
                $table->date('date_in')->nullable();
                $table->date('resigned_date')->nullable();
                $table->integer('tenure_months')->default(0);
                $table->integer('tenure_days')->default(0);
                $table->decimal('prorate_ratio', 8, 4)->default(0);
                $table->decimal('base_salary_amount', 15, 2)->default(0);
                $table->decimal('thr_calculated_amount', 15, 2)->default(0);
                $table->decimal('thr_adjustment_amount', 15, 2)->default(0);
                $table->decimal('thr_final_amount', 15, 2)->default(0);
                $table->tinyInteger('is_eligible')->default(1);
                $table->string('ineligible_reason', 255)->nullable();
                $table->tinyInteger('is_payroll_processed')->default(0);
                $table->string('salary_id', 60)->nullable();
                $table->string('salary_period', 10)->index();
                $table->text('note')->nullable();
                $table->timestamps();

                $table->index(['nik', 'salary_period'], 'idx_thr_nik_period');
                $table->index(['batch_id', 'is_eligible'], 'idx_thr_batch_eligible');
            });
        }

        if (Schema::hasTable('salary') && !Schema::hasColumn('salary', 'THR')) {
            Schema::table('salary', function (Blueprint $table) {
                $table->decimal('THR', 15, 2)->default(0)->after('KJK');
            });
        }

        if (Schema::hasTable('payrol_master_lists')) {
            $exists = DB::table('payrol_master_lists')->where('Payroll_ID', 'PY000060')->exists();
            if (!$exists) {
                DB::table('payrol_master_lists')->insert([
                    'Payroll_ID'         => 'PY000060',
                    'Urutan'             => 29,
                    'Name'               => 'THR (TUNJANGAN HARI RAYA)',
                    'Kena_Potongan'      => 0,
                    'Default_PPH'        => 1,
                    'Type_Payroll'       => 'ALLOWANCE',
                    'Calculation_Method' => 'DYNAMIC',
                    'Formula_Desc'       => 'THR',
                    'Created_By'         => 'SYSTEM',
                    'Created_Date'       => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('salary') && Schema::hasColumn('salary', 'THR')) {
            Schema::table('salary', function (Blueprint $table) {
                $table->dropColumn('THR');
            });
        }

        Schema::dropIfExists('payroll_thr_items');
        Schema::dropIfExists('payroll_thr_batches');

        if (Schema::hasTable('payrol_master_lists')) {
            DB::table('payrol_master_lists')->where('Payroll_ID', 'PY000060')->delete();
        }
    }
}
