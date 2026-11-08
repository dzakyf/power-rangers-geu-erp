<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePerformanceCompetencyAndPipTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Master Kamus Kompetensi (Competency Dictionary)
        if (!Schema::hasTable('performance_competencies')) {
            Schema::create('performance_competencies', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('code', 50)->unique();
                $table->string('name', 150);
                $table->enum('type', ['CORE', 'LEADERSHIP', 'FUNCTIONAL'])->default('CORE');
                $table->text('description')->nullable();
                $table->text('behavioral_indicators')->nullable(); // Panduan perilaku yang dinilai
                $table->integer('target_level')->default(3); // Skala 1 - 5
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. Pemetaan Penilaian Kompetensi pada Dokumen Review (Feedback Detail)
        if (!Schema::hasTable('performance_review_competency_scores')) {
            Schema::create('performance_review_competency_scores', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('feedback_id');
                $table->unsignedBigInteger('competency_id');
                $table->integer('score')->default(3); // 1 - 5
                $table->text('comment')->nullable();
                $table->timestamps();

                $table->foreign('feedback_id')->references('id')->on('performance_review_feedbacks')->onDelete('cascade');
                $table->foreign('competency_id')->references('id')->on('performance_competencies')->onDelete('cascade');
                $table->index(['feedback_id', 'competency_id'], 'idx_feed_comp');
            });
        }

        // 3. Dokumen PIP (Performance Improvement Plan)
        if (!Schema::hasTable('performance_pip_plans')) {
            Schema::create('performance_pip_plans', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('pip_no', 50)->unique();
                $table->unsignedBigInteger('review_id')->nullable(); // Terhubung dari review yang nilainya D / < 60
                $table->unsignedBigInteger('user_id'); // Karyawan yang mengikuti PIP
                $table->unsignedBigInteger('mentor_id')->nullable(); // Atasan langsung / Mentor pembimbing
                $table->date('start_date');
                $table->date('end_date');
                $table->integer('duration_days')->default(60); // Biasanya 30, 60, atau 90 hari
                $table->text('performance_issue_summary'); // Ringkasan kelemahan/masalah performa
                $table->text('expected_outcome'); // Target hasil perbaikan yang diharapkan
                $table->decimal('final_score', 8, 2)->nullable();
                $table->enum('outcome_status', [
                    'ONGOING',
                    'PASSED',
                    'EXTENDED',
                    'FAILED'
                ])->default('ONGOING');
                $table->enum('status', [
                    'DRAFT',
                    'ACTIVE',
                    'IN_EVALUATION',
                    'COMPLETED',
                    'CANCELLED'
                ])->default('DRAFT');
                $table->text('final_notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->foreign('review_id')->references('id')->on('performance_reviews')->onDelete('set null');
                $table->index(['user_id', 'status']);
            });
        }

        // 4. Milestone / Action Items dalam Rencana PIP
        if (!Schema::hasTable('performance_pip_actions')) {
            Schema::create('performance_pip_actions', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('pip_id');
                $table->string('action_title', 255);
                $table->text('expected_standard')->nullable(); // Standar pencapaian
                $table->date('due_date');
                $table->enum('status', ['PENDING', 'IN_PROGRESS', 'ACHIEVED', 'NOT_ACHIEVED'])->default('PENDING');
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('pip_id')->references('id')->on('performance_pip_plans')->onDelete('cascade');
                $table->index(['pip_id', 'status']);
            });
        }

        // 5. Seed Data Baku Kamus Kompetensi (Competency Matrix)
        $competencies = [
            [
                'code' => 'COMP-INTEGRITY',
                'name' => 'Integrity & Ethics',
                'type' => 'CORE',
                'description' => 'Menjunjung tinggi kejujuran, etika bisnis perusahaan, dan konsistensi perkataan dengan tindakan.',
                'behavioral_indicators' => 'Menjaga kerahasiaan data, transparan dalam bekerja, dan mematuhi SOP perusahaan.',
                'target_level' => 4,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'COMP-TEAMWORK',
                'name' => 'Teamwork & Collaboration',
                'type' => 'CORE',
                'description' => 'Mampu bekerja sama secara efektif lintas departemen untuk mencapai sasaran bersama.',
                'behavioral_indicators' => 'Mendukung rekan kerja, berbagi informasi secara proaktif, dan menghargai masukan orang lain.',
                'target_level' => 4,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'COMP-PROB-SOLV',
                'name' => 'Problem Solving & Decision Making',
                'type' => 'FUNCTIONAL',
                'description' => 'Kemampuan menganalisis akar masalah operasional dan mengambil solusi yang tepat dan terukur.',
                'behavioral_indicators' => 'Mengidentifikasi kendala sebelum menjadi krisis, menyusun alternatif solusi praktis.',
                'target_level' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'COMP-LEADERSHIP',
                'name' => 'Leadership & People Development',
                'type' => 'LEADERSHIP',
                'description' => 'Kemampuan membimbing, memberi delegasi terarah, dan mengembangkan potensi bawahan/tim.',
                'behavioral_indicators' => 'Memberikan feedback konstruktif, memotivasi tim kerja, dan memonitor target bawahan.',
                'target_level' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'COMP-EXECUTION',
                'name' => 'Execution & Result Oriented',
                'type' => 'CORE',
                'description' => 'Fokus pada pencapaian target kualitas dan tenggat waktu secara efisien dan konsisten.',
                'behavioral_indicators' => 'Menyelesaikan tanggung jawab tepat waktu, sigap menghadapi perubahan prioritas kerja.',
                'target_level' => 4,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($competencies as $c) {
            $exists = DB::table('performance_competencies')->where('code', $c['code'])->exists();
            if (!$exists) {
                DB::table('performance_competencies')->insert($c);
            }
        }

        // 6. Daftarkan Menu Navigation Baru ke menu_lists
        $menuCompetencyExists = DB::table('menu_lists')->where('Menu_ID', 'SI01041')->exists();
        if (!$menuCompetencyExists) {
            DB::table('menu_lists')->insert([
                'Menu_ID'     => 'SI01041',
                'Name'        => 'Competency Matrix',
                'Path_Loc'    => 'hrd-performance-competency-index',
                'Position'    => 4,
                'Parent_Menu' => 'SS00106',
                'Urutan'      => 4,
                'Modules'     => 'HRD',
            ]);
        }

        $menuPipExists = DB::table('menu_lists')->where('Menu_ID', 'SI01042')->exists();
        if (!$menuPipExists) {
            DB::table('menu_lists')->insert([
                'Menu_ID'     => 'SI01042',
                'Name'        => 'PIP Plan',
                'Path_Loc'    => 'hrd-performance-pip-index',
                'Position'    => 4,
                'Parent_Menu' => 'SS00106',
                'Urutan'      => 5,
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
        DB::table('menu_lists')->whereIn('Menu_ID', ['SI01041', 'SI01042'])->delete();
        Schema::dropIfExists('performance_pip_actions');
        Schema::dropIfExists('performance_pip_plans');
        Schema::dropIfExists('performance_review_competency_scores');
        Schema::dropIfExists('performance_competencies');
    }
}

