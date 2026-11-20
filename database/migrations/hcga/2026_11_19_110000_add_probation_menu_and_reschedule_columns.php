<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddProbationMenuAndRescheduleColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Tambah kolom reschedule_reason & rescheduled_at pada performance_review_cycles
        if (Schema::hasTable('performance_review_cycles')) {
            Schema::table('performance_review_cycles', function (Blueprint $table) {
                if (!Schema::hasColumn('performance_review_cycles', 'reschedule_reason')) {
                    $table->text('reschedule_reason')->nullable()->after('description');
                }
                if (!Schema::hasColumn('performance_review_cycles', 'rescheduled_at')) {
                    $table->timestamp('rescheduled_at')->nullable()->after('reschedule_reason');
                }
            });
        }

        // 2. Tambah kolom reschedule_reason & rescheduled_at pada performance_goals
        if (Schema::hasTable('performance_goals')) {
            Schema::table('performance_goals', function (Blueprint $table) {
                if (!Schema::hasColumn('performance_goals', 'reschedule_reason')) {
                    $table->text('reschedule_reason')->nullable()->after('notes');
                }
                if (!Schema::hasColumn('performance_goals', 'rescheduled_at')) {
                    $table->timestamp('rescheduled_at')->nullable()->after('reschedule_reason');
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
        if (Schema::hasTable('performance_goals')) {
            Schema::table('performance_goals', function (Blueprint $table) {
                foreach (['rescheduled_at', 'reschedule_reason'] as $col) {
                    if (Schema::hasColumn('performance_goals', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('performance_review_cycles')) {
            Schema::table('performance_review_cycles', function (Blueprint $table) {
                foreach (['rescheduled_at', 'reschedule_reason'] as $col) {
                    if (Schema::hasColumn('performance_review_cycles', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
}
