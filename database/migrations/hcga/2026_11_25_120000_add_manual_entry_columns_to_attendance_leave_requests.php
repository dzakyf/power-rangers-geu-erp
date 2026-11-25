<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddManualEntryColumnsToAttendanceLeaveRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('attendance_leave_requests')) {
            Schema::table('attendance_leave_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('attendance_leave_requests', 'source_mode')) {
                    $table->string('source_mode', 30)->default('SELF_PORTAL')->after('leave_type');
                }
                if (!Schema::hasColumn('attendance_leave_requests', 'target_user_id')) {
                    $table->unsignedBigInteger('target_user_id')->nullable()->after('nik');
                }
                if (!Schema::hasColumn('attendance_leave_requests', 'allow_negative_balance')) {
                    $table->boolean('allow_negative_balance')->default(false)->after('total_days');
                }
                if (!Schema::hasColumn('attendance_leave_requests', 'is_post_cutoff_adjustment')) {
                    $table->boolean('is_post_cutoff_adjustment')->default(false)->after('approval_request_id');
                }
                if (!Schema::hasColumn('attendance_leave_requests', 'post_cutoff_action')) {
                    $table->string('post_cutoff_action', 30)->default('NONE')->after('is_post_cutoff_adjustment');
                }
                if (!Schema::hasColumn('attendance_leave_requests', 'post_cutoff_status')) {
                    $table->string('post_cutoff_status', 30)->default('NONE')->after('post_cutoff_action');
                }
                if (!Schema::hasColumn('attendance_leave_requests', 'post_cutoff_notes')) {
                    $table->text('post_cutoff_notes')->nullable()->after('post_cutoff_status');
                }
                if (!Schema::hasColumn('attendance_leave_requests', 'updated_by')) {
                    $table->string('updated_by', 100)->nullable()->after('created_by');
                }
            });
        }

        if (Schema::hasTable('attendance_manual_audit_logs')) {
            Schema::table('attendance_manual_audit_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('attendance_manual_audit_logs', 'leave_request_id')) {
                    $table->unsignedInteger('leave_request_id')->nullable()->index()->after('correction_id');
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
        if (Schema::hasTable('attendance_manual_audit_logs')) {
            Schema::table('attendance_manual_audit_logs', function (Blueprint $table) {
                if (Schema::hasColumn('attendance_manual_audit_logs', 'leave_request_id')) {
                    $table->dropColumn('leave_request_id');
                }
            });
        }

        if (Schema::hasTable('attendance_leave_requests')) {
            Schema::table('attendance_leave_requests', function (Blueprint $table) {
                $cols = [
                    'source_mode',
                    'target_user_id',
                    'allow_negative_balance',
                    'is_post_cutoff_adjustment',
                    'post_cutoff_action',
                    'post_cutoff_status',
                    'post_cutoff_notes',
                ];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('attendance_leave_requests', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
}
