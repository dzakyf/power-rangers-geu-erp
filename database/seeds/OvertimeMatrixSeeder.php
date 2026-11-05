<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OvertimeMatrixSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        // 1. Staff-SPV (Grading 8-5) - Wilayah 1 (Jabodetabek)
        $m1 = DB::table('overtime_rate_matrices')->updateOrInsert(
            ['matrix_code' => 'OT_STAFF_SPV_W1'],
            [
                'matrix_name'  => 'Staff - SPV (Grading 8-5) - Wilayah 1 Jabodetabek',
                'grade_group'  => 'Grading 8-5',
                'job_titles'   => json_encode(['Staff', 'SPV', 'Supervisor', 'Officer', 'Senior Staff']),
                'region_type'  => 'WILAYAH_1',
                'pricing_type' => 'TIERED_HOURLY',
                'max_hours'    => 8.00,
                'status'       => 'ACTIVE',
                'created_by'   => 'system',
                'created_at'   => $now,
                'updated_at'   => $now,
            ]
        );
        $m1Id = DB::table('overtime_rate_matrices')->where('matrix_code', 'OT_STAFF_SPV_W1')->value('id');

        DB::table('overtime_matrix_tiers')->where('matrix_id', $m1Id)->delete();
        DB::table('overtime_matrix_tiers')->insert([
            [
                'matrix_id'          => $m1Id,
                'tier_order'         => 1,
                'min_minutes'        => 1,
                'max_minutes'        => 240, // 4 jam pertama
                'tier_label'         => '4 Jam Pertama (Maks 4 Jam)',
                'base_rate'          => 100000.00,
                'next_per_hour_rate' => 0.00,
                'created_at'         => $now,
                'updated_at'         => $now,
            ],
            [
                'matrix_id'          => $m1Id,
                'tier_order'         => 2,
                'min_minutes'        => 241,
                'max_minutes'        => 480, // Maks 8 jam
                'tier_label'         => 'Per Jam Selanjutnya (Jam ke 5 - 8)',
                'base_rate'          => 100000.00,
                'next_per_hour_rate' => 15000.00,
                'created_at'         => $now,
                'updated_at'         => $now,
            ],
        ]);

        // 2. Staff-SPV (Grading 8-5) - Wilayah 2 (Jawa Timur, Samarinda, Denpasar, Solo, Bandung, dll)
        $m2 = DB::table('overtime_rate_matrices')->updateOrInsert(
            ['matrix_code' => 'OT_STAFF_SPV_W2'],
            [
                'matrix_name'  => 'Staff - SPV (Grading 8-5) - Wilayah 2 Daerah',
                'grade_group'  => 'Grading 8-5',
                'job_titles'   => json_encode(['Staff', 'SPV', 'Supervisor', 'Officer', 'Senior Staff']),
                'region_type'  => 'WILAYAH_2',
                'pricing_type' => 'TIERED_HOURLY',
                'max_hours'    => 8.00,
                'status'       => 'ACTIVE',
                'created_by'   => 'system',
                'created_at'   => $now,
                'updated_at'   => $now,
            ]
        );
        $m2Id = DB::table('overtime_rate_matrices')->where('matrix_code', 'OT_STAFF_SPV_W2')->value('id');

        DB::table('overtime_matrix_tiers')->where('matrix_id', $m2Id)->delete();
        DB::table('overtime_matrix_tiers')->insert([
            [
                'matrix_id'          => $m2Id,
                'tier_order'         => 1,
                'min_minutes'        => 1,
                'max_minutes'        => 240, // 4 jam pertama
                'tier_label'         => '4 Jam Pertama (Maks 4 Jam)',
                'base_rate'          => 70000.00,
                'next_per_hour_rate' => 0.00,
                'created_at'         => $now,
                'updated_at'         => $now,
            ],
            [
                'matrix_id'          => $m2Id,
                'tier_order'         => 2,
                'min_minutes'        => 241,
                'max_minutes'        => 480, // Maks 8 jam
                'tier_label'         => 'Per Jam Selanjutnya (Jam ke 5 - 8)',
                'base_rate'          => 70000.00,
                'next_per_hour_rate' => 10000.00,
                'created_at'         => $now,
                'updated_at'         => $now,
            ],
        ]);

        // 3. Jabatan WHL / Coor WH (Grading 8-7) - 50.000 per penugasan (flat)
        $m3 = DB::table('overtime_rate_matrices')->updateOrInsert(
            ['matrix_code' => 'OT_WHL_COOR_WH'],
            [
                'matrix_name'  => 'Jabatan WHL / Coor WH (Grading 8-7)',
                'grade_group'  => 'Grading 8-7',
                'job_titles'   => json_encode(['WHL', 'Coor WH', 'Coordinator WH', 'Warehouse Coordinator', 'Warehouse Leader']),
                'region_type'  => 'ALL',
                'pricing_type' => 'FLAT_TASK',
                'max_hours'    => 12.00,
                'status'       => 'ACTIVE',
                'created_by'   => 'system',
                'created_at'   => $now,
                'updated_at'   => $now,
            ]
        );
        $m3Id = DB::table('overtime_rate_matrices')->where('matrix_code', 'OT_WHL_COOR_WH')->value('id');

        DB::table('overtime_matrix_tiers')->where('matrix_id', $m3Id)->delete();
        DB::table('overtime_matrix_tiers')->insert([
            [
                'matrix_id'          => $m3Id,
                'tier_order'         => 1,
                'min_minutes'        => 1,
                'max_minutes'        => null,
                'tier_label'         => 'Per Penugasan (Flat)',
                'base_rate'          => 50000.00,
                'next_per_hour_rate' => 0.00,
                'created_at'         => $now,
                'updated_at'         => $now,
            ],
        ]);

        // 4. Jabatan Kru Gudang, Driver, OB (Grading 11), Non Grading dan QC Officer (Grading 8)
        $m4 = DB::table('overtime_rate_matrices')->updateOrInsert(
            ['matrix_code' => 'OT_OPERATIONAL_STAFF'],
            [
                'matrix_name'  => 'Kru Gudang, Driver, OB (Grading 11), Non Grading & QC Officer (Grading 8)',
                'grade_group'  => 'Grading 11 / Non Grading / QC (Grading 8)',
                'job_titles'   => json_encode(['Kru Gudang', 'Crew Gudang', 'Driver', 'Sopir', 'OB', 'Office Boy', 'QC Officer', 'Quality Control']),
                'region_type'  => 'ALL',
                'pricing_type' => 'MINUTE_RANGE',
                'max_hours'    => 12.00,
                'status'       => 'ACTIVE',
                'created_by'   => 'system',
                'created_at'   => $now,
                'updated_at'   => $now,
            ]
        );
        $m4Id = DB::table('overtime_rate_matrices')->where('matrix_code', 'OT_OPERATIONAL_STAFF')->value('id');

        DB::table('overtime_matrix_tiers')->where('matrix_id', $m4Id)->delete();
        DB::table('overtime_matrix_tiers')->insert([
            [
                'matrix_id'          => $m4Id,
                'tier_order'         => 1,
                'min_minutes'        => 1,
                'max_minutes'        => 90, // 0 - 1 jam 30 menit
                'tier_label'         => '0 - 1 Jam 30 Menit',
                'base_rate'          => 12500.00,
                'next_per_hour_rate' => 0.00,
                'created_at'         => $now,
                'updated_at'         => $now,
            ],
            [
                'matrix_id'          => $m4Id,
                'tier_order'         => 2,
                'min_minutes'        => 91,
                'max_minutes'        => 150, // 1 jam 31 menit - 2 jam 30 menit
                'tier_label'         => '1 Jam 31 Menit - 2 Jam 30 Menit',
                'base_rate'          => 20000.00,
                'next_per_hour_rate' => 0.00,
                'created_at'         => $now,
                'updated_at'         => $now,
            ],
            [
                'matrix_id'          => $m4Id,
                'tier_order'         => 3,
                'min_minutes'        => 151,
                'max_minutes'        => 270, // 2 jam 31 menit - 4 jam 30 menit
                'tier_label'         => '2 Jam 31 Menit - 4 Jam 30 Menit',
                'base_rate'          => 30000.00,
                'next_per_hour_rate' => 0.00,
                'created_at'         => $now,
                'updated_at'         => $now,
            ],
            [
                'matrix_id'          => $m4Id,
                'tier_order'         => 4,
                'min_minutes'        => 271,
                'max_minutes'        => 390, // 4 jam 31 menit - 6 jam 30 menit
                'tier_label'         => '4 Jam 31 Menit - 6 Jam 30 Menit',
                'base_rate'          => 40000.00,
                'next_per_hour_rate' => 0.00,
                'created_at'         => $now,
                'updated_at'         => $now,
            ],
            [
                'matrix_id'          => $m4Id,
                'tier_order'         => 5,
                'min_minutes'        => 391,
                'max_minutes'        => null, // > 6 jam 31 menit
                'tier_label'         => '> 6 Jam 31 Menit',
                'base_rate'          => 50000.00,
                'next_per_hour_rate' => 0.00,
                'created_at'         => $now,
                'updated_at'         => $now,
            ],
        ]);
    }
}

