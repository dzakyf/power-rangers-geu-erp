<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSlaColumnsToApprovalTables extends Migration
{
    public function up()
    {
        if (Schema::hasTable('approval_policy_step')) {
            Schema::table('approval_policy_step', function (Blueprint $table) {
                if (!Schema::hasColumn('approval_policy_step', 'Sla_Hours')) {
                    $table->integer('Sla_Hours')->nullable()->default(48)->after('Label');
                }
                if (!Schema::hasColumn('approval_policy_step', 'Escalation_Interval_Hours')) {
                    $table->integer('Escalation_Interval_Hours')->nullable()->default(24)->after('Sla_Hours');
                }
            });
        }

        if (Schema::hasTable('approval_request_task')) {
            Schema::table('approval_request_task', function (Blueprint $table) {
                if (!Schema::hasColumn('approval_request_task', 'Due_At')) {
                    $table->timestamp('Due_At')->nullable()->index()->after('Snapshot');
                }
                if (!Schema::hasColumn('approval_request_task', 'Escalated_At')) {
                    $table->timestamp('Escalated_At')->nullable()->index()->after('Due_At');
                }
                if (!Schema::hasColumn('approval_request_task', 'Last_Reminder_At')) {
                    $table->timestamp('Last_Reminder_At')->nullable()->after('Escalated_At');
                }
                if (!Schema::hasColumn('approval_request_task', 'Reminder_Count')) {
                    $table->integer('Reminder_Count')->default(0)->after('Last_Reminder_At');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('approval_request_task')) {
            Schema::table('approval_request_task', function (Blueprint $table) {
                foreach (['Reminder_Count', 'Last_Reminder_At', 'Escalated_At', 'Due_At'] as $col) {
                    if (Schema::hasColumn('approval_request_task', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('approval_policy_step')) {
            Schema::table('approval_policy_step', function (Blueprint $table) {
                foreach (['Escalation_Interval_Hours', 'Sla_Hours'] as $col) {
                    if (Schema::hasColumn('approval_policy_step', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
}
