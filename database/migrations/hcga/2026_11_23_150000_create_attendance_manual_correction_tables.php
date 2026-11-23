<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceManualCorrectionTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Tabel Setting Perbaikan Absen Manual
        if (!Schema::hasTable('attendance_manual_correction_settings')) {
            Schema::create('attendance_manual_correction_settings', function (Blueprint $table) {
                $table->increments('id');
                $table->text('allowed_usernames')->nullable();
                $table->enum('default_mode', ['APPROVAL_REQUIRED', 'DIRECT_UPDATE'])->default('APPROVAL_REQUIRED');
                $table->boolean('allow_mode_selection')->default(true);
                $table->boolean('require_reason')->default(true);
                $table->boolean('require_evidence')->default(false);
                $table->integer('max_backdate_days')->nullable();
                $table->boolean('allow_self_direct_update')->default(false);
                $table->string('updated_by', 100)->nullable();
                $table->timestamps();
            });
        }

        // 2. Tabel Audit Log Perbaikan Absen Manual
        if (!Schema::hasTable('attendance_manual_audit_logs')) {
            Schema::create('attendance_manual_audit_logs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('correction_id')->nullable()->index();
                $table->string('action', 50)->index();
                $table->string('actor_username', 100)->index();
                $table->string('target_nik', 50)->nullable()->index();
                $table->date('scan_date')->nullable()->index();
                $table->string('mode', 30)->nullable();
                $table->text('before_data')->nullable();
                $table->text('after_data')->nullable();
                $table->string('leave_action', 150)->nullable();
                $table->string('post_cutoff_action', 50)->nullable();
                $table->text('reason')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        // 3. Perluasan kolom pada attendance_corrections
        if (Schema::hasTable('attendance_corrections')) {
            Schema::table('attendance_corrections', function (Blueprint $table) {
                if (!Schema::hasColumn('attendance_corrections', 'source_mode')) {
                    $table->string('source_mode', 30)->default('SELF_PORTAL')->after('correction_type');
                }
                if (!Schema::hasColumn('attendance_corrections', 'target_user_id')) {
                    $table->unsignedBigInteger('target_user_id')->nullable()->after('nik');
                }
                if (!Schema::hasColumn('attendance_corrections', 'before_snapshot')) {
                    $table->text('before_snapshot')->nullable()->after('reason');
                }
                if (!Schema::hasColumn('attendance_corrections', 'after_snapshot')) {
                    $table->text('after_snapshot')->nullable()->after('before_snapshot');
                }
                if (!Schema::hasColumn('attendance_corrections', 'cancelled_leave_id')) {
                    $table->unsignedInteger('cancelled_leave_id')->nullable()->after('approval_request_id');
                }
                if (!Schema::hasColumn('attendance_corrections', 'leave_refunded_days')) {
                    $table->decimal('leave_refunded_days', 5, 2)->nullable()->after('cancelled_leave_id');
                }
                if (!Schema::hasColumn('attendance_corrections', 'pending_cancel_leave_id')) {
                    $table->unsignedInteger('pending_cancel_leave_id')->nullable()->after('leave_refunded_days');
                }
                if (!Schema::hasColumn('attendance_corrections', 'is_post_cutoff_adjustment')) {
                    $table->boolean('is_post_cutoff_adjustment')->default(false)->after('pending_cancel_leave_id');
                }
                if (!Schema::hasColumn('attendance_corrections', 'post_cutoff_action')) {
                    $table->string('post_cutoff_action', 30)->default('NONE')->after('is_post_cutoff_adjustment');
                }
                if (!Schema::hasColumn('attendance_corrections', 'post_cutoff_status')) {
                    $table->string('post_cutoff_status', 30)->default('NONE')->after('post_cutoff_action');
                }
                if (!Schema::hasColumn('attendance_corrections', 'post_cutoff_notes')) {
                    $table->text('post_cutoff_notes')->nullable()->after('post_cutoff_status');
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
        Schema::dropIfExists('attendance_manual_audit_logs');
        Schema::dropIfExists('attendance_manual_correction_settings');
    }
}
