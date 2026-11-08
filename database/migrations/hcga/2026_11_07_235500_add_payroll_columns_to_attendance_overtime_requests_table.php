<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPayrollColumnsToAttendanceOvertimeRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('attendance_overtime_requests')) {
            Schema::table('attendance_overtime_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('attendance_overtime_requests', 'salary_id')) {
                    $table->string('salary_id', 50)->nullable()->after('approval_request_id');
                }
                if (!Schema::hasColumn('attendance_overtime_requests', 'salary_period')) {
                    $table->string('salary_period', 20)->nullable()->after('salary_id');
                }
                if (!Schema::hasColumn('attendance_overtime_requests', 'is_payroll_processed')) {
                    $table->tinyInteger('is_payroll_processed')->default(0)->after('salary_period');
                }
                if (!Schema::hasColumn('attendance_overtime_requests', 'legacy_overtime_id')) {
                    $table->integer('legacy_overtime_id')->nullable()->after('is_payroll_processed');
                }
                if (!Schema::hasColumn('attendance_overtime_requests', 'reference_note')) {
                    $table->string('reference_note', 255)->nullable()->after('legacy_overtime_id');
                }
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
        if (Schema::hasTable('attendance_overtime_requests')) {
            Schema::table('attendance_overtime_requests', function (Blueprint $table) {
                $cols = ['salary_id', 'salary_period', 'is_payroll_processed', 'legacy_overtime_id', 'reference_note'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('attendance_overtime_requests', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
}

