<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLegacyPermissionSyncColumns extends Migration
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
                if (!Schema::hasColumn('attendance_leave_requests', 'legacy_permission_id')) {
                    $table->unsignedBigInteger('legacy_permission_id')->nullable()->index()->after('approval_request_id');
                }
            });
        }

        if (Schema::hasTable('izin')) {
            Schema::table('izin', function (Blueprint $table) {
                if (!Schema::hasColumn('izin', 'modern_leave_id')) {
                    $table->unsignedBigInteger('modern_leave_id')->nullable()->index();
                }
                if (!Schema::hasColumn('izin', 'approval_request_id')) {
                    $table->unsignedBigInteger('approval_request_id')->nullable()->index();
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
        if (Schema::hasTable('attendance_leave_requests')) {
            Schema::table('attendance_leave_requests', function (Blueprint $table) {
                if (Schema::hasColumn('attendance_leave_requests', 'legacy_permission_id')) {
                    $table->dropColumn('legacy_permission_id');
                }
            });
        }

        if (Schema::hasTable('izin')) {
            Schema::table('izin', function (Blueprint $table) {
                if (Schema::hasColumn('izin', 'modern_leave_id')) {
                    $table->dropColumn('modern_leave_id');
                }
                if (Schema::hasColumn('izin', 'approval_request_id')) {
                    $table->dropColumn('approval_request_id');
                }
            });
        }
    }
}

