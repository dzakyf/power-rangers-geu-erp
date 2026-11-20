<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PerformanceProbationDemoSeeder extends Seeder
{
    /**
     * Seeder Demo Lengkap Persis Contoh PDF FR/HCGA/14-00 (Samle-Form-Penilaian-Kontrak-Masa-Probation.pdf):
     * 1. Publish Versi 2 Master Soft Skill (18 Poin, 4 Grup Kategori, Skala 1-10)
     * 2. Demo Karyawan Probation (Ayuda Supriadi - Driver Kuzer) & Penilai (Hari Kusuma)
     * 3. Demo KPI Probation APPROVED (Target 145.2 ton, Realisasi 100 ton => 68.87%)
     * 4. Demo Lembar Review Probation (Total Soft Skill 141 => 78.33%, 4 Keterangan, Total 71.71%, EXTEND_CONTRACT)
     *
     * Jalankan:
     * php artisan db:seed --class=PerformanceProbationDemoSeeder
     */
    public function run()
    {
        $now = Carbon::now();

        // 1. Publish Versi 2 Master Soft Skill (Skala 1-10)
        DB::table('performance_competency_versions')->where('status', 'PUBLISHED')->update(['status' => 'ARCHIVED']);

        $v2 = DB::table('performance_competency_versions')->where('version_no', 2)->first();
        if (!$v2) {
            $v2Id = DB::table('performance_competency_versions')->insertGetId([
                'version_no'    => 2,
                'version_label' => 'Versi 2 (Standar FR/HCGA/14-00 Skala 1-10)',
                'status'        => 'PUBLISHED',
                'scale_min'     => 1,
                'scale_max'     => 10,
                'scale_labels'  => json_encode([
                    '1' => 'Sangat Kurang', '5' => 'Cukup', '7' => 'Baik', '8' => 'Baik Sekali', '10' => 'Sangat Baik'
                ]),
                'notes'         => 'Template 18 poin Soft Skill 4 grup kategori sesuai Form FR/HCGA/14-00.',
                'published_at'  => $now,
                'published_by'  => 1,
                'created_by'    => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
        } else {
            $v2Id = $v2->id;
            DB::table('performance_competency_versions')->where('id', $v2Id)->update([
                'status'       => 'PUBLISHED',
                'scale_min'    => 1,
                'scale_max'    => 10,
                'published_at' => $now,
                'updated_at'   => $now,
            ]);
        }

        DB::table('performance_settings')->updateOrInsert(
            ['setting_key' => 'competency_scale_max'],
            ['setting_value' => '10', 'updated_at' => $now]
        );

        // 18 Poin Soft Skill persis PDF FR/HCGA/14-00 + skor contoh (total = 141) & keterangan
        $items = [
            // [group, code, name, sort, sample_score, sample_comment]
            ['1. Kedisiplinan dan Loyalitas Kerja', 'SS-01', 'Memiliki kedisiplinan pada jam datang dan jam pulang, serta tidak mangkir kerja', 1, 7, null],
            ['1. Kedisiplinan dan Loyalitas Kerja', 'SS-02', 'Mampu mengedepankan loyalitas kepada perusahaan dengan tidak menolak tugas', 2, 8, 'Libur atau Hari Besar tetap beraktifitas'],

            ['2. Karakter dan Budaya', 'SS-03', 'Mampu mengelola dan memecahkan masalah disekitar baik dalam pekerjaan maupun tim', 3, 8, 'Mampu melakukan backup truk yang terperosok'],
            ['2. Karakter dan Budaya', 'SS-04', 'Mampu mengikuti segala peraturan, misi dan budaya perusahaan', 4, 8, null],
            ['2. Karakter dan Budaya', 'SS-05', 'Mampu mengatur pokok pikiran secara runtut dan efisien', 5, 8, null],
            ['2. Karakter dan Budaya', 'SS-06', 'Memiliki jiwa melayani, menolong dan tidak acuh', 6, 8, null],
            ['2. Karakter dan Budaya', 'SS-07', 'Mampu bertindak dan bekerja secara fokus', 7, 8, null],
            ['2. Karakter dan Budaya', 'SS-08', 'Mampu bertahan secara fisik dan psikis dalam bekerja', 8, 8, null],
            ['2. Karakter dan Budaya', 'SS-09', 'Mampu bertindak secara adaptif terhadap segala perubahan dan kondisi kerja', 9, 8, 'Mampu menyesuaikan perubahan jadwal'],
            ['2. Karakter dan Budaya', 'SS-10', 'Ada upaya untuk menyelesaikan tugas dan deadline kerja', 10, 8, 'Mampu melakukan efisiensi dalam BBM'],
            ['2. Karakter dan Budaya', 'SS-11', 'Bersedia menyelesaikan tugas sesuai standar', 11, 8, null],
            ['2. Karakter dan Budaya', 'SS-12', 'Bertanggung jawab sesuai dengan jabatan dan tanggung jawab yang diberikan', 12, 8, null],

            ['3. Komunikasi', 'SS-13', 'Mampu berkomunikasi secara efektif dan responsif', 13, 8, null],
            ['3. Komunikasi', 'SS-14', 'Mampu mendengar secara baik', 14, 8, null],
            ['3. Komunikasi', 'SS-15', 'Mampu menerima informasi secara baik', 15, 8, null],

            ['4. Pengetahuan dan Skill', 'SS-16', 'Memiliki pemahaman yang baik terhadap pekerjaannya maupun target kerja', 16, 7, null],
            ['4. Pengetahuan dan Skill', 'SS-17', 'Memiliki skill yang menunjang di dalam pekerjaannya', 17, 7, null],
            ['4. Pengetahuan dan Skill', 'SS-18', 'Memahami semua aturan, SOP dan bisnis proses dalam divisinya', 18, 8, null],
        ];
        // Total skor: 7+8 + 9+8+8+8+8+8+8+8+7+7 + 7+7+7 + 7+7+7 = 141 => 141/180 = 78.33%

        $compIdMap = [];
        foreach ($items as $it) {
            $existingComp = DB::table('performance_competencies')
                ->where('version_id', $v2Id)
                ->where('code', $it[1])
                ->first();

            if (!$existingComp) {
                $cid = DB::table('performance_competencies')->insertGetId([
                    'version_id'            => $v2Id,
                    'code'                  => $it[1],
                    'name'                  => $it[2],
                    'type'                  => 'CORE',
                    'category_group'        => $it[0],
                    'description'           => $it[2],
                    'behavioral_indicators' => $it[5],
                    'target_level'          => 8,
                    'sort_order'            => $it[3],
                    'is_active'             => true,
                    'created_at'            => $now,
                    'updated_at'            => $now,
                ]);
            } else {
                $cid = $existingComp->id;
            }
            $compIdMap[$it[1]] = ['id' => $cid, 'score' => $it[4], 'comment' => $it[5]];
        }

        // 2. Pastikan Periode Aktif & Siklus Probation ada
        $period = DB::table('performance_periods')->where('status', 'ACTIVE')->first();
        if (!$period) {
            $periodId = DB::table('performance_periods')->insertGetId([
                'period_code' => 'PM-' . date('Y'),
                'name'        => 'Performance Period ' . date('Y'),
                'start_date'  => date('Y') . '-01-01',
                'end_date'    => date('Y') . '-12-31',
                'status'      => 'ACTIVE',
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        } else {
            $periodId = $period->id;
        }

        $cycle = DB::table('performance_review_cycles')->where('cycle_code', 'PROB-DEMO-' . date('Y'))->first();
        if (!$cycle) {
            $cycleId = DB::table('performance_review_cycles')->insertGetId([
                'cycle_code'            => 'PROB-DEMO-' . date('Y'),
                'title'                 => 'Evaluasi Masa Percobaan (Demo FR/HCGA/14-00)',
                'period_id'             => $periodId,
                'review_type'           => 'PROBATION',
                'enable_self_review'    => false,
                'enable_manager_review' => true,
                'enable_peer_review'    => false,
                'weight_kpi'            => 70.00,
                'weight_competency'     => 30.00,
                'start_date'            => date('Y') . '-01-01',
                'end_date'              => date('Y') . '-12-31',
                'status'                => 'ONGOING',
                'created_at'            => $now,
                'updated_at'            => $now,
            ]);
        } else {
            $cycleId = $cycle->id;
        }

        // 3. Demo User Karyawan (Ayuda Supriadi) & Penilai (Hari Kusuma)
        $empUser = DB::table('users')->where('Emp_ID', 'DEMO-PROB-01')->first();
        if (!$empUser) {
            $empUserId = DB::table('users')->insertGetId([
                'username'   => 'ayuda.supriadi.demo',
                'Name'       => 'Ayuda Supriadi',
                'Emp_ID'     => 'DEMO-PROB-01',
                'Department' => 'OPERATIONAL',
                'Position'   => 'Driver Kuzer',
                'Status'     => 'ACTIVE',
                'password'   => bcrypt('demo12345'),
            ]);
        } else {
            $empUserId = $empUser->id;
        }

        $mgrUser = DB::table('users')->where('Emp_ID', 'DEMO-MGR-01')->first();
        if (!$mgrUser) {
            $mgrUserId = DB::table('users')->insertGetId([
                'username'   => 'hari.kusuma.demo',
                'Name'       => 'Hari Kusuma',
                'Emp_ID'     => 'DEMO-MGR-01',
                'Department' => 'OPERATIONAL',
                'Position'   => 'Site Coordinator',
                'Status'     => 'ACTIVE',
                'password'   => bcrypt('demo12345'),
            ]);
        } else {
            $mgrUserId = $mgrUser->id;
        }

        $probEndDate = Carbon::today()->addDays(10)->toDateString();
        if (!DB::table('employment')->where('NIK', 'DEMO-PROB-01')->exists()) {
            DB::table('employment')->insert([
                'NIK'                => 'DEMO-PROB-01',
                'Task_ID'            => 'DEMO-PROB-01',
                'Name'               => 'Ayuda Supriadi',
                'Department'         => 'OPERATIONAL',
                'Position'           => 'Driver Kuzer',
                'Location'           => 'Site Sidoarjo',
                'Date_in'            => Carbon::today()->subMonths(3)->addDays(10)->toDateString(),
                'Start_Date'         => Carbon::today()->subMonths(3)->addDays(10)->toDateString(),
                'probation_end_date' => $probEndDate,
                'Employment_Status'  => 'PROBATION',
                'Status'             => 'ACTIVE',
            ]);
        }

        // 4. Demo KPI Probation APPROVED (Target 145.2 ton, Realisasi 100 ton => 68.87%)
        $goal = DB::table('performance_goals')->where('goal_no', 'GOAL/PROB/DEMO/0001')->first();
        if (!$goal) {
            $goalId = DB::table('performance_goals')->insertGetId([
                'goal_no'              => 'GOAL/PROB/DEMO/0001',
                'scope_type'           => 'INDIVIDUAL',
                'is_addon'             => false,
                'goal_category'        => 'PROBATION',
                'probation_months'     => 3,
                'probation_start_date' => Carbon::today()->subMonths(3)->addDays(10)->toDateString(),
                'probation_end_date'   => $probEndDate,
                'period_id'            => $periodId,
                'user_id'              => $empUserId,
                'emp_id'               => 'DEMO-PROB-01',
                'department'           => 'OPERATIONAL',
                'position'             => 'Driver Kuzer',
                'goal_type'            => 'KPI',
                'level'                => 'INDIVIDUAL',
                'total_kpi_weight'     => 100.00,
                'overall_progress_percent' => 68.87,
                'status'               => 'APPROVED',
                'notes'                => 'Demo Target Kinerja Probation sesuai PDF FR/HCGA/14-00',
                'created_by'           => 1,
                'created_at'           => $now,
                'updated_at'           => $now,
            ]);

            $goalItemId = DB::table('performance_goal_items')->insertGetId([
                'goal_id'          => $goalId,
                'item_type'        => 'KPI',
                'title'            => 'Mampu menyelesaikan 17 perintah Mutasi dengan sempurna, dengan target movement 100 ton mampu diselesaikan hingga terakumulasi 145 ton',
                'kpi_unit'         => 'ton',
                'target_value'     => 145.20,
                'actual_value'     => 100.00,
                'weight_percent'   => 100.00,
                'progress_percent' => 68.87,
                'scoring_type'     => 'HIGHER_BETTER',
                'sort_order'       => 1,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        } else {
            $goalId = $goal->id;
            $goalItemId = DB::table('performance_goal_items')->where('goal_id', $goalId)->value('id');
        }

        // 5. Demo Lembar Review Probation persis PDF FR/HCGA/14-00
        DB::table('performance_reviews')->where('review_no', 'REV/PROB/DEMO/0001')->delete();
        $reviewId = DB::table('performance_reviews')->insertGetId([
                'review_no'             => 'REV/PROB/DEMO/0001',
                'cycle_id'              => $cycleId,
                'user_id'               => $empUserId,
                'goal_id'               => $goalId,
                'manager_id'            => $mgrUserId,
                'competency_version_id' => $v2Id,
                'probation_end_date'    => $probEndDate,
                'manager_score'         => 78.33,
                'kpi_score'             => 68.87,
                'competency_score'      => 78.33,
                'final_score'           => 71.71,
                'rating_grade'          => 'B',
                'recommendation'        => 'EXTEND_CONTRACT',
                'status'                => 'CALIBRATION',
                'manager_notes'         => 'Disarankan untuk di kontrak / diperpanjang kontraknya.',
                'created_at'            => $now,
                'updated_at'            => $now,
            ]);

            if ($goalItemId) {
                DB::table('performance_review_kpi_items')->insert([
                    'review_id'        => $reviewId,
                    'goal_item_id'     => $goalItemId,
                    'source_scope'     => 'PROBATION_INDIVIDUAL',
                    'item_type'        => 'KPI',
                    'title'            => 'Mampu menyelesaikan 17 perintah Mutasi dengan sempurna, dengan target movement 100 ton mampu diselesaikan hingga terakumulasi 145 ton',
                    'kpi_unit'         => 'ton',
                    'target_value'     => 145.20,
                    'actual_value'     => 100.00,
                    'weight_percent'   => 100.00,
                    'progress_percent' => 68.87,
                    'score'            => 68.87,
                    'scoring_type'     => 'HIGHER_BETTER',
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]);
            }

            $feedbackId = DB::table('performance_review_feedbacks')->insertGetId([
                'review_id'     => $reviewId,
                'reviewer_id'   => $mgrUserId,
                'reviewer_type' => 'MANAGER',
                'score'         => 78.33,
                'feedback'      => 'Disarankan untuk di kontrak / diperpanjang kontraknya.',
                'is_submitted'  => true,
                'submitted_at'  => $now,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);

            foreach ($compIdMap as $info) {
                DB::table('performance_review_competency_scores')->insert([
                    'feedback_id'   => $feedbackId,
                    'competency_id' => $info['id'],
                    'score'         => $info['score'],
                    'max_scale'     => 10,
                    'comment'       => $info['comment'],
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);
            }
    }
}
