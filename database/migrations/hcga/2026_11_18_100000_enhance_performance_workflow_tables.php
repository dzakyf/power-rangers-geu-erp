<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EnhancePerformanceWorkflowTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Tabel Pengaturan Fleksibel Performance Management (HCGA)
        if (!Schema::hasTable('performance_settings')) {
            Schema::create('performance_settings', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('setting_key', 100)->unique();
                $table->text('setting_value')->nullable();
                $table->string('description', 255)->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
            });

            $now = now();
            DB::table('performance_settings')->insert([
                [
                    'setting_key'   => 'allow_employee_self_planning',
                    'setting_value' => '1',
                    'description'   => 'Izinkan karyawan biasa menyusun KPI sendiri (1 = Ya, 0 = Hanya Atasan/HCGA)',
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ],
                [
                    'setting_key'   => 'default_pip_threshold_score',
                    'setting_value' => '60',
                    'description'   => 'Ambang batas skor minimum sebelum otomatis memicu PIP',
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ],
            ]);
        }

        // 2. Tabel Snapshot Realisasi KPI per Dokumen Review (Mendukung KPI Annual dievaluasi Semester 1 & 2)
        if (!Schema::hasTable('performance_review_kpi_items')) {
            Schema::create('performance_review_kpi_items', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('review_id');
                $table->unsignedBigInteger('goal_item_id');
                $table->enum('item_type', ['KPI', 'OBJECTIVE', 'KEY_RESULT'])->default('KPI');
                $table->string('title', 255);
                $table->string('kpi_unit', 50)->nullable();
                $table->decimal('target_value', 15, 2)->default(0.00);
                $table->decimal('actual_value', 15, 2)->default(0.00);
                $table->decimal('weight_percent', 5, 2)->default(0.00);
                $table->decimal('progress_percent', 5, 2)->default(0.00);
                $table->decimal('score', 8, 2)->default(0.00);
                $table->enum('scoring_type', ['HIGHER_BETTER', 'LOWER_BETTER', 'MILESTONE'])->default('HIGHER_BETTER');
                $table->text('comment')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->foreign('review_id')->references('id')->on('performance_reviews')->onDelete('cascade');
                $table->foreign('goal_item_id')->references('id')->on('performance_goal_items')->onDelete('cascade');
                $table->unique(['review_id', 'goal_item_id'], 'uq_review_goal_item');
            });
        }

        // 3. Tambah kolom pip_threshold_score di performance_review_cycles
        if (Schema::hasTable('performance_review_cycles') && !Schema::hasColumn('performance_review_cycles', 'pip_threshold_score')) {
            Schema::table('performance_review_cycles', function (Blueprint $table) {
                $table->decimal('pip_threshold_score', 5, 2)->nullable()->after('weight_competency');
            });
        }

        // 4. Tambah kolom parent_pip_id, hr_followup_action, hr_followup_notes di performance_pip_plans
        if (Schema::hasTable('performance_pip_plans')) {
            Schema::table('performance_pip_plans', function (Blueprint $table) {
                if (!Schema::hasColumn('performance_pip_plans', 'parent_pip_id')) {
                    $table->unsignedBigInteger('parent_pip_id')->nullable()->after('review_id');
                }
                if (!Schema::hasColumn('performance_pip_plans', 'hr_followup_action')) {
                    $table->string('hr_followup_action', 50)->nullable()->after('outcome_status');
                }
                if (!Schema::hasColumn('performance_pip_plans', 'hr_followup_notes')) {
                    $table->text('hr_followup_notes')->nullable()->after('hr_followup_action');
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
        if (Schema::hasTable('performance_pip_plans')) {
            Schema::table('performance_pip_plans', function (Blueprint $table) {
                if (Schema::hasColumn('performance_pip_plans', 'hr_followup_notes')) {
                    $table->dropColumn('hr_followup_notes');
                }
                if (Schema::hasColumn('performance_pip_plans', 'hr_followup_action')) {
                    $table->dropColumn('hr_followup_action');
                }
                if (Schema::hasColumn('performance_pip_plans', 'parent_pip_id')) {
                    $table->dropColumn('parent_pip_id');
                }
            });
        }

        if (Schema::hasTable('performance_review_cycles') && Schema::hasColumn('performance_review_cycles', 'pip_threshold_score')) {
            Schema::table('performance_review_cycles', function (Blueprint $table) {
                $table->dropColumn('pip_threshold_score');
            });
        }

        Schema::dropIfExists('performance_review_kpi_items');
        Schema::dropIfExists('performance_settings');
    }
}
