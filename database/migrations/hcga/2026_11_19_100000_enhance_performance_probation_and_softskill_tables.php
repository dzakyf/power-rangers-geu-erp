<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EnhancePerformanceProbationAndSoftskillTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Tabel Versioning Master Soft Skill / Kompetensi (v1, v2, Draft/Published/Archived)
        if (!Schema::hasTable('performance_competency_versions')) {
            Schema::create('performance_competency_versions', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('version_no')->default(1);
                $table->string('version_label', 100);
                $table->enum('status', ['DRAFT', 'PUBLISHED', 'ARCHIVED'])->default('DRAFT');
                $table->integer('scale_min')->default(1);
                $table->integer('scale_max')->default(5);
                $table->text('scale_labels')->nullable();
                $table->text('notes')->nullable();
                $table->timestamp('published_at')->nullable();
                $table->unsignedBigInteger('published_by')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->unique('version_no', 'uq_perf_comp_version_no');
                $table->index('status', 'idx_perf_comp_ver_status');
            });
        }

        // 2. Perluas tabel performance_competencies agar mendukung versi & grup kategori
        if (Schema::hasTable('performance_competencies')) {
            // Hapus unique index lama pada 'code' (bisa bernama 'performance_competencies_code_unique' atau 'code')
            $oldIndexes = DB::select("SHOW INDEX FROM performance_competencies WHERE Column_name = 'code' AND Non_unique = 0");
            foreach ($oldIndexes as $idx) {
                if ($idx->Key_name !== 'uq_perf_comp_ver_code') {
                    DB::statement("ALTER TABLE `performance_competencies` DROP INDEX `{$idx->Key_name}`");
                }
            }

            Schema::table('performance_competencies', function (Blueprint $table) {
                if (!Schema::hasColumn('performance_competencies', 'version_id')) {
                    $table->unsignedBigInteger('version_id')->nullable()->after('id');
                }
                if (!Schema::hasColumn('performance_competencies', 'category_group')) {
                    $table->string('category_group', 100)->nullable()->after('type');
                }
                if (!Schema::hasColumn('performance_competencies', 'sort_order')) {
                    $table->integer('sort_order')->default(0)->after('target_level');
                }
            });

            // Seed v1 PUBLISHED jika belum ada versi sama sekali
            $now = now();
            $v1 = DB::table('performance_competency_versions')->where('version_no', 1)->first();
            if (!$v1) {
                $v1Id = DB::table('performance_competency_versions')->insertGetId([
                    'version_no'    => 1,
                    'version_label' => 'Versi 1 (Standar Awal)',
                    'status'        => 'PUBLISHED',
                    'scale_min'     => 1,
                    'scale_max'     => 5,
                    'scale_labels'  => json_encode([
                        '1' => 'Sangat Kurang',
                        '2' => 'Kurang',
                        '3' => 'Cukup',
                        '4' => 'Baik',
                        '5' => 'Sangat Baik',
                    ]),
                    'notes'         => 'Versi awal kamus kompetensi & soft skill perusahaan.',
                    'published_at'  => $now,
                    'published_by'  => 1,
                    'created_by'    => 1,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);
            } else {
                $v1Id = $v1->id;
            }

            DB::table('performance_competencies')
                ->whereNull('version_id')
                ->update(['version_id' => $v1Id]);

            $newIndex = DB::select("SHOW INDEX FROM performance_competencies WHERE Key_name = 'uq_perf_comp_ver_code'");
            if (empty($newIndex)) {
                Schema::table('performance_competencies', function (Blueprint $table) {
                    $table->unique(['version_id', 'code'], 'uq_perf_comp_ver_code');
                });
            }
        }

        // 3. Tambah max_scale pada performance_review_competency_scores (snapshot skala saat dinilai)
        if (Schema::hasTable('performance_review_competency_scores')) {
            Schema::table('performance_review_competency_scores', function (Blueprint $table) {
                if (!Schema::hasColumn('performance_review_competency_scores', 'max_scale')) {
                    $table->integer('max_scale')->default(5)->after('score');
                }
            });
        }

        // 4. Tambah dukungan KPI Probation (3/6/12 bln) & KPI Tambahan (is_addon) pada performance_goals
        if (Schema::hasTable('performance_goals')) {
            Schema::table('performance_goals', function (Blueprint $table) {
                if (!Schema::hasColumn('performance_goals', 'is_addon')) {
                    $table->boolean('is_addon')->default(false)->after('scope_type');
                }
                if (!Schema::hasColumn('performance_goals', 'goal_category')) {
                    $table->enum('goal_category', ['REGULAR', 'PROBATION'])->default('REGULAR')->after('is_addon');
                }
                if (!Schema::hasColumn('performance_goals', 'probation_months')) {
                    $table->integer('probation_months')->nullable()->after('goal_category');
                }
                if (!Schema::hasColumn('performance_goals', 'probation_start_date')) {
                    $table->date('probation_start_date')->nullable()->after('probation_months');
                }
                if (!Schema::hasColumn('performance_goals', 'probation_end_date')) {
                    $table->date('probation_end_date')->nullable()->after('probation_start_date');
                }
            });
        }

        // 5. Tambah source_scope pada performance_review_kpi_items
        if (Schema::hasTable('performance_review_kpi_items')) {
            Schema::table('performance_review_kpi_items', function (Blueprint $table) {
                if (!Schema::hasColumn('performance_review_kpi_items', 'source_scope')) {
                    $table->string('source_scope', 30)->default('POSITION')->after('goal_item_id');
                }
            });
        }

        // 6. Tambah tracking versi kompetensi, probation, rekomendasi, & notifikasi pada performance_reviews
        if (Schema::hasTable('performance_reviews')) {
            Schema::table('performance_reviews', function (Blueprint $table) {
                if (!Schema::hasColumn('performance_reviews', 'competency_version_id')) {
                    $table->unsignedBigInteger('competency_version_id')->nullable()->after('manager_id');
                }
                if (!Schema::hasColumn('performance_reviews', 'probation_end_date')) {
                    $table->date('probation_end_date')->nullable()->after('competency_version_id');
                }
                if (!Schema::hasColumn('performance_reviews', 'recommendation')) {
                    $table->string('recommendation', 50)->nullable()->after('rating_grade');
                }
                if (!Schema::hasColumn('performance_reviews', 'conclusion_notes')) {
                    $table->text('conclusion_notes')->nullable()->after('recommendation');
                }
                if (!Schema::hasColumn('performance_reviews', 'reminder_h14_sent_at')) {
                    $table->timestamp('reminder_h14_sent_at')->nullable()->after('improvement_notes');
                }
                if (!Schema::hasColumn('performance_reviews', 'reminder_h7_sent_at')) {
                    $table->timestamp('reminder_h7_sent_at')->nullable()->after('reminder_h14_sent_at');
                }
                if (!Schema::hasColumn('performance_reviews', 'escalation_h3_sent_at')) {
                    $table->timestamp('escalation_h3_sent_at')->nullable()->after('reminder_h7_sent_at');
                }
            });
        }

        // 7. Tambah pengaturan global pada performance_settings
        if (Schema::hasTable('performance_settings')) {
            $now = now();
            $defaults = [
                [
                    'setting_key'   => 'competency_scale_min',
                    'setting_value' => '1',
                    'description'   => 'Nilai minimum skala penilaian Soft Skill / Kompetensi global',
                ],
                [
                    'setting_key'   => 'competency_scale_max',
                    'setting_value' => '5',
                    'description'   => 'Nilai maksimum skala penilaian Soft Skill / Kompetensi global (mis. 5 atau 10)',
                ],
                [
                    'setting_key'   => 'probation_reminder_days',
                    'setting_value' => '14,7',
                    'description'   => 'Hari pengingat sebelum probation_end_date ke atasan langsung (dipisah koma)',
                ],
                [
                    'setting_key'   => 'probation_escalation_days',
                    'setting_value' => '3',
                    'description'   => 'Hari eskalasi ke HCGA sebelum probation_end_date jika belum dinilai',
                ],
            ];

            foreach ($defaults as $item) {
                $exists = DB::table('performance_settings')->where('setting_key', $item['setting_key'])->exists();
                if (!$exists) {
                    DB::table('performance_settings')->insert(array_merge($item, [
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]));
                }
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
        if (Schema::hasTable('performance_reviews')) {
            Schema::table('performance_reviews', function (Blueprint $table) {
                $cols = [
                    'escalation_h3_sent_at',
                    'reminder_h7_sent_at',
                    'reminder_h14_sent_at',
                    'conclusion_notes',
                    'recommendation',
                    'probation_end_date',
                    'competency_version_id',
                ];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('performance_reviews', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('performance_review_kpi_items') && Schema::hasColumn('performance_review_kpi_items', 'source_scope')) {
            Schema::table('performance_review_kpi_items', function (Blueprint $table) {
                $table->dropColumn('source_scope');
            });
        }

        if (Schema::hasTable('performance_goals')) {
            Schema::table('performance_goals', function (Blueprint $table) {
                $cols = ['probation_end_date', 'probation_start_date', 'probation_months', 'goal_category', 'is_addon'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('performance_goals', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('performance_review_competency_scores') && Schema::hasColumn('performance_review_competency_scores', 'max_scale')) {
            Schema::table('performance_review_competency_scores', function (Blueprint $table) {
                $table->dropColumn('max_scale');
            });
        }

        if (Schema::hasTable('performance_competencies')) {
            $idx = DB::select("SHOW INDEX FROM performance_competencies WHERE Key_name = 'uq_perf_comp_ver_code'");
            if (!empty($idx)) {
                Schema::table('performance_competencies', function (Blueprint $table) {
                    $table->dropUnique('uq_perf_comp_ver_code');
                });
            }
            Schema::table('performance_competencies', function (Blueprint $table) {
                foreach (['sort_order', 'category_group', 'version_id'] as $col) {
                    if (Schema::hasColumn('performance_competencies', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        Schema::dropIfExists('performance_competency_versions');
    }
}
