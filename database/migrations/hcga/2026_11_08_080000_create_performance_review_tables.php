<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePerformanceReviewTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Tambah Policy Approval untuk PERFORMANCE_KPI
        $policyExists = DB::table('approval_policy')->where('Policy_Code', 'PERF-KPI-POLICY')->exists();
        if (!$policyExists) {
            $policyId = DB::table('approval_policy')->insertGetId([
                'Policy_Code'        => 'PERF-KPI-POLICY',
                'Policy_Name'        => 'Approval Penetapan Target KPI & OKR (Manager Dept + Manager HCGA)',
                'Request_Type'       => 'PERFORMANCE_KPI',
                'Status'             => 'ACTIVE',
                'Version'            => 1,
                'Min_Amount'         => 0,
                'Max_Amount'         => 0,
                'Allow_Delegation'   => 1,
                'Allow_Self_Approval'=> 0,
                'Rejection_Mode'     => 'TERMINATE',
                'Created_At'         => now(),
                'Updated_At'         => now(),
            ]);

            DB::table('approval_policy_step')->insert([
                [
                    'Policy_ID'     => $policyId,
                    'Stage_Order'   => 1,
                    'Level_Number'  => 3,
                    'Position_ID'   => null,
                    'Position_Code' => null,
                    'Department'    => null,
                    'Approval_Mode' => 'ALL',
                    'Quorum'        => null,
                    'Is_Required'   => 1,
                    'Label'         => 'Manager Departemen Karyawan',
                    'Created_At'    => now(),
                ],
                [
                    'Policy_ID'     => $policyId,
                    'Stage_Order'   => 2,
                    'Level_Number'  => 3,
                    'Position_ID'   => null,
                    'Position_Code' => 'MHCGA',
                    'Department'    => 'HCGA',
                    'Approval_Mode' => 'ALL',
                    'Quorum'        => null,
                    'Is_Required'   => 1,
                    'Label'         => 'Manager HCGA',
                    'Created_At'    => now(),
                ]
            ]);
        }

        // 2. Tabel Master Siklus / Jadwal Review Appraisal (Annual / Mid-Year 360)
        if (!Schema::hasTable('performance_review_cycles')) {
            Schema::create('performance_review_cycles', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('cycle_code', 50)->unique();
                $table->string('title', 150);
                $table->unsignedBigInteger('period_id');
                $table->enum('review_type', ['ANNUAL', 'MID_YEAR', 'PROBATION', 'PROJECT'])->default('ANNUAL');
                $table->boolean('enable_self_review')->default(true);
                $table->boolean('enable_manager_review')->default(true);
                $table->boolean('enable_peer_review')->default(false);
                $table->decimal('weight_kpi', 5, 2)->default(70.00); // 70% KPI & OKR
                $table->decimal('weight_competency', 5, 2)->default(30.00); // 30% Kompetensi & Budaya
                $table->date('start_date');
                $table->date('end_date');
                $table->enum('status', ['DRAFT', 'ONGOING', 'COMPLETED', 'ARCHIVED'])->default('DRAFT');
                $table->text('description')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->foreign('period_id')->references('id')->on('performance_periods')->onDelete('cascade');
                $table->index(['period_id', 'status']);
            });
        }

        // 3. Tabel Dokumen Evaluasi Karyawan (Appraisal Sheet Terhubung ke Goal)
        if (!Schema::hasTable('performance_reviews')) {
            Schema::create('performance_reviews', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('review_no', 50)->unique();
                $table->unsignedBigInteger('cycle_id');
                $table->unsignedBigInteger('user_id'); // Karyawan yang dinilai
                $table->unsignedBigInteger('goal_id')->nullable(); // Terhubung ke Goal KPI/OKR (tanpa input ulang)
                $table->unsignedBigInteger('manager_id')->nullable(); // Atasan penilai
                $table->decimal('self_score', 8, 2)->nullable();
                $table->decimal('manager_score', 8, 2)->nullable();
                $table->decimal('kpi_score', 8, 2)->default(0.00); // Diambil dari realisasi goal_items
                $table->decimal('competency_score', 8, 2)->default(0.00);
                $table->decimal('final_score', 8, 2)->nullable();
                $table->string('rating_grade', 10)->nullable(); // A, B+, B, C, D
                $table->enum('status', [
                    'SCHEDULED',
                    'SELF_REVIEW',
                    'MANAGER_REVIEW',
                    'CALIBRATION',
                    'FINALIZED'
                ])->default('SCHEDULED');
                $table->text('self_notes')->nullable();
                $table->text('manager_notes')->nullable();
                $table->text('strength_notes')->nullable();
                $table->text('improvement_notes')->nullable();
                $table->timestamps();

                $table->foreign('cycle_id')->references('id')->on('performance_review_cycles')->onDelete('cascade');
                $table->foreign('goal_id')->references('id')->on('performance_goals')->onDelete('set null');
                $table->index(['cycle_id', 'user_id', 'status']);
            });
        }

        // 4. Tabel Detail Penilaian Reviewer (Self, Manager, Peer)
        if (!Schema::hasTable('performance_review_feedbacks')) {
            Schema::create('performance_review_feedbacks', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('review_id');
                $table->unsignedBigInteger('reviewer_id');
                $table->enum('reviewer_type', ['SELF', 'MANAGER', 'PEER', 'SUBORDINATE'])->default('MANAGER');
                $table->decimal('score', 8, 2)->default(0.00);
                $table->text('feedback')->nullable();
                $table->boolean('is_submitted')->default(false);
                $table->timestamp('submitted_at')->nullable();
                $table->timestamps();

                $table->foreign('review_id')->references('id')->on('performance_reviews')->onDelete('cascade');
                $table->index(['review_id', 'reviewer_id']);
            });
        }

        // 5. Tambah Menu List ke menu_lists (Performance Review & Appraisal)
        $itemExists = DB::table('menu_lists')->where('Menu_ID', 'SI01040')->exists();
        if (!$itemExists) {
            DB::table('menu_lists')->insert([
                'Menu_ID'     => 'SI01040',
                'Name'        => 'Performance Review',
                'Path_Loc'    => 'hrd-performance-review-index',
                'Position'    => 4,
                'Parent_Menu' => 'SS00106',
                'Urutan'      => 3,
                'Modules'     => 'HRD',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('menu_lists')->where('Menu_ID', 'SI01040')->delete();
        Schema::dropIfExists('performance_review_feedbacks');
        Schema::dropIfExists('performance_reviews');
        Schema::dropIfExists('performance_review_cycles');
    }
}

