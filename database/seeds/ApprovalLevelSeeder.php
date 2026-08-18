<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApprovalLevelSeeder extends Seeder
{
    public function run()
    {
        $requestTypes = [
            'ONBOARDING',
            'BRANDING_PROMOTION',
            'PROFIT_SHARING',
            'RENEWAL',
            'TERMINATION',
            'TRANSFER_MEMBERSHIP',
            'SURVEY',
        ];

        $levels = [
            ['Level_Number' => 1, 'Approver_Position' => 'SALES',    'Level_Name' => 'Approval Sales'],
            ['Level_Number' => 2, 'Approver_Position' => 'CFO',      'Level_Name' => 'Approval Finance'],
            ['Level_Number' => 3, 'Approver_Position' => 'MANAGER',  'Level_Name' => 'Approval Manager'],
            ['Level_Number' => 4, 'Approver_Position' => 'DIRECTOR', 'Level_Name' => 'Approval Direktur'],
        ];

        DB::transaction(function () use ($requestTypes, $levels) {
            DB::table('approval_level')->whereIn('Request_Type', $requestTypes)->delete();

            foreach ($requestTypes as $type) {
                foreach ($levels as $level) {
                    DB::table('approval_level')->insert(array_merge(
                        ['Request_Type' => $type],
                        $level,
                        ['Description' => null]
                    ));
                }
            }
        });

        $this->command->info('Seeded ' . (count($requestTypes) * count($levels)) . ' approval_level rows (SALES -> CFO -> MANAGER -> DIRECTOR).');
    }
}
