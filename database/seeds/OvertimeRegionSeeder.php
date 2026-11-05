<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OvertimeRegionSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        DB::table('overtime_regions')->updateOrInsert(
            ['region_code' => 'WILAYAH_1'],
            [
                'region_name'       => 'Wilayah 1 (Jabodetabek)',
                'coverage_keywords' => 'DKI Jakarta, Jakarta, Bogor, Depok, Tangerang, Bekasi',
                'description'       => 'Wilayah operasional pusat dan cabang Jabodetabek',
                'status'            => 'ACTIVE',
                'created_by'        => 'system',
                'created_at'        => $now,
                'updated_at'        => $now,
            ]
        );

        DB::table('overtime_regions')->updateOrInsert(
            ['region_code' => 'WILAYAH_2'],
            [
                'region_name'       => 'Wilayah 2 (Daerah: Jatim, Solo, Bandung, Denpasar, dll)',
                'coverage_keywords' => 'Jawa Timur, Surabaya, Sidoarjo, Solo, Surakarta, Bandung, Denpasar, Bali, Samarinda, Semarang, Yogyakarta',
                'description'       => 'Wilayah operasional cabang daerah di luar Jabodetabek',
                'status'            => 'ACTIVE',
                'created_by'        => 'system',
                'created_at'        => $now,
                'updated_at'        => $now,
            ]
        );
    }
}
