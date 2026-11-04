<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterShiftAndRosterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Seed Master Shifts
        $shifts = [
            [
                'id'              => 1,
                'shift_code'      => 'OFFICE',
                'shift_name'      => 'Office Regular (08:00 - 17:00)',
                'start_time'      => '08:00:00',
                'end_time'        => '17:00:00',
                'break_minutes'   => 60,
                'working_minutes' => 480,
                'is_cross_day'    => 0,
                'is_off'          => 0,
                'status'          => 'ACTIVE',
                'created_by'      => 'SYSTEM',
                'created_at'      => now(),
            ],
            [
                'id'              => 2,
                'shift_code'      => 'SHIFT-1',
                'shift_name'      => 'Pagi (07:00 - 15:00)',
                'start_time'      => '07:00:00',
                'end_time'        => '15:00:00',
                'break_minutes'   => 60,
                'working_minutes' => 420,
                'is_cross_day'    => 0,
                'is_off'          => 0,
                'status'          => 'ACTIVE',
                'created_by'      => 'SYSTEM',
                'created_at'      => now(),
            ],
            [
                'id'              => 3,
                'shift_code'      => 'SHIFT-2',
                'shift_name'      => 'Sore (15:00 - 23:00)',
                'start_time'      => '15:00:00',
                'end_time'        => '23:00:00',
                'break_minutes'   => 60,
                'working_minutes' => 420,
                'is_cross_day'    => 0,
                'is_off'          => 0,
                'status'          => 'ACTIVE',
                'created_by'      => 'SYSTEM',
                'created_at'      => now(),
            ],
            [
                'id'              => 4,
                'shift_code'      => 'SHIFT-3',
                'shift_name'      => 'Malam (23:00 - 07:00)',
                'start_time'      => '23:00:00',
                'end_time'        => '07:00:00',
                'break_minutes'   => 60,
                'working_minutes' => 420,
                'is_cross_day'    => 1,
                'is_off'          => 0,
                'status'          => 'ACTIVE',
                'created_by'      => 'SYSTEM',
                'created_at'      => now(),
            ],
            [
                'id'              => 5,
                'shift_code'      => 'OFF',
                'shift_name'      => 'Libur / Rest Day',
                'start_time'      => '00:00:00',
                'end_time'        => '00:00:00',
                'break_minutes'   => 0,
                'working_minutes' => 0,
                'is_cross_day'    => 0,
                'is_off'          => 1,
                'status'          => 'ACTIVE',
                'created_by'      => 'SYSTEM',
                'created_at'      => now(),
            ],
        ];

        foreach ($shifts as $shift) {
            DB::table('master_shifts')->updateOrInsert(
                ['shift_code' => $shift['shift_code']],
                $shift
            );
        }

        // 2. Seed Sample Roster Pattern (Siklus 3 Shift 4 Grup / 6 Hari Kerja 2 Off)
        $patternId = DB::table('roster_patterns')->updateOrInsert(
            ['pattern_name' => 'Pola 3 Shift 8 Hari (2 Pagi - 2 Sore - 2 Malam - 2 Off)'],
            [
                'cycle_days'  => 8,
                'description' => 'Siklus rotasi shift standar operasional pabrik 24/7',
                'status'      => 'ACTIVE',
                'created_by'  => 'SYSTEM',
                'created_at'  => now(),
            ]
        );

        $pattern = DB::table('roster_patterns')->where('pattern_name', 'Pola 3 Shift 8 Hari (2 Pagi - 2 Sore - 2 Malam - 2 Off)')->first();

        if ($pattern) {
            $details = [
                ['pattern_id' => $pattern->id, 'day_sequence' => 1, 'shift_id' => 2], // Pagi
                ['pattern_id' => $pattern->id, 'day_sequence' => 2, 'shift_id' => 2], // Pagi
                ['pattern_id' => $pattern->id, 'day_sequence' => 3, 'shift_id' => 3], // Sore
                ['pattern_id' => $pattern->id, 'day_sequence' => 4, 'shift_id' => 3], // Sore
                ['pattern_id' => $pattern->id, 'day_sequence' => 5, 'shift_id' => 4], // Malam
                ['pattern_id' => $pattern->id, 'day_sequence' => 6, 'shift_id' => 4], // Malam
                ['pattern_id' => $pattern->id, 'day_sequence' => 7, 'shift_id' => 5], // OFF
                ['pattern_id' => $pattern->id, 'day_sequence' => 8, 'shift_id' => 5], // OFF
            ];

            foreach ($details as $dtl) {
                DB::table('roster_pattern_details')->updateOrInsert(
                    ['pattern_id' => $dtl['pattern_id'], 'day_sequence' => $dtl['day_sequence']],
                    $dtl
                );
            }
        }
    }
}

