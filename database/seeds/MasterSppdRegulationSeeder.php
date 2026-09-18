<?php

use Illuminate\Database\Seeder;
use App\MasterSppdJobLevel;
use App\MasterSppdTripType;
use App\MasterSppdAllowance;

class MasterSppdRegulationSeeder extends Seeder
{
    public function run()
    {
        $jsonPath = base_path('app/Helpers/matric.json');
        if (!file_exists($jsonPath)) {
            $this->command->error("File matric.json tidak ditemukan di {$jsonPath}");
            return;
        }

        $data = json_decode(file_get_contents($jsonPath), true);
        if (!$data) {
            $this->command->error("Format matric.json tidak valid");
            return;
        }

        // 1. Seed Job Levels
        if (isset($data['job_levels']) && is_array($data['job_levels'])) {
            foreach ($data['job_levels'] as $jobLevel) {
                MasterSppdJobLevel::updateOrCreate(
                    ['level' => $jobLevel['level']],
                    [
                        'grade' => $jobLevel['grade'] ?? '',
                        'label' => $jobLevel['label'] ?? '',
                    ]
                );
            }
        }

        // 2. Seed Trip Types & Allowances
        if (isset($data['trip_types']) && is_array($data['trip_types'])) {
            foreach ($data['trip_types'] as $typeKey => $tripType) {
                MasterSppdTripType::updateOrCreate(
                    ['type_key' => $typeKey],
                    [
                        'name'     => $tripType['name'] ?? $typeKey,
                        'duration' => $tripType['duration'] ?? null,
                        'note'     => $tripType['note'] ?? null,
                    ]
                );

                if (isset($tripType['allowances']) && is_array($tripType['allowances'])) {
                    foreach ($tripType['allowances'] as $levelKey => $allowance) {
                        $transportLand = isset($allowance['transport']['land']) ? json_encode($allowance['transport']['land']) : null;
                        $transportAir  = $allowance['transport']['air'] ?? null;
                        $ticketMax     = $allowance['transport']['ticket_max'] ?? null;
                        $reimburseMax  = $allowance['transport']['reimburse_max_per_night'] ?? null;
                        $hotelStar     = $allowance['accommodation']['hotel_star'] ?? null;
                        $dailyMeal     = $allowance['daily']['meal'] ?? null;
                        $dailyPocket   = $allowance['daily']['pocket'] ?? null;

                        $kosPerMonth     = $allowance['assistance']['kos_per_month'] ?? null;
                        $assistanceNote  = $allowance['assistance']['note'] ?? null;

                        $relocationOneTime = $allowance['relocation']['one_time_payment'] ?? null;
                        $relocationNote    = $allowance['relocation']['note'] ?? null;

                        $mobilizationCostPerWeek = $allowance['mobilization']['cost_per_week'] ?? null;
                        $mobilizationNote        = $allowance['mobilization']['note'] ?? null;

                        MasterSppdAllowance::updateOrCreate(
                            [
                                'trip_type_key' => $typeKey,
                                'job_level_key' => $levelKey,
                            ],
                            [
                                'transport_land'              => $transportLand,
                                'transport_air'               => $transportAir,
                                'ticket_max'                  => $ticketMax,
                                'reimburse_max_per_night'     => $reimburseMax,
                                'hotel_star'                  => $hotelStar,
                                'daily_meal'                  => $dailyMeal,
                                'daily_pocket'                => $dailyPocket,
                                'kos_per_month'               => $kosPerMonth,
                                'assistance_note'             => $assistanceNote,
                                'relocation_one_time_payment' => $relocationOneTime,
                                'relocation_note'             => $relocationNote,
                                'mobilization_cost_per_week'  => $mobilizationCostPerWeek,
                                'mobilization_note'           => $mobilizationNote,
                            ]
                        );
                    }
                }
            }
        }
    }
}
