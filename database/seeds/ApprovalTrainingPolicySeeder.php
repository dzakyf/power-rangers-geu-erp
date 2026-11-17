<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApprovalTrainingPolicySeeder extends Seeder
{
    public function run()
    {
        $this->createPolicy(
            'TNA-DEFAULT-3-HCGA',
            'Approval TNA (Manager Dept -> Manager HCGA)',
            'TNA_REQUEST',
            [
                ['Stage_Order' => 1, 'Level_Number' => 3, 'Department' => null, 'Position_Code' => null, 'Label' => 'Manager Departemen Pemohon'],
                ['Stage_Order' => 2, 'Level_Number' => 3, 'Department' => null, 'Position_Code' => 'MHCGA', 'Label' => 'Manager HCGA'],
            ]
        );

        $this->createPolicy(
            'TRAINING-DEFAULT-HCGA',
            'Approval Request / Jadwal Training (Manager HCGA)',
            'TRAINING_REQUEST',
            [
                ['Stage_Order' => 1, 'Level_Number' => 3, 'Department' => null, 'Position_Code' => 'MHCGA', 'Label' => 'Manager HCGA'],
            ]
        );
    }

    protected function createPolicy(string $code, string $name, string $requestType, array $steps): void
    {
        if (DB::table('approval_policy')->where('Policy_Code', $code)->exists()) {
            $this->command->info("ApprovalTrainingPolicySeeder: {$code} sudah ada, dilewati.");
            return;
        }

        $policyId = DB::table('approval_policy')->insertGetId([
            'Policy_Code' => $code,
            'Policy_Name' => $name,
            'Request_Type' => $requestType,
            'Location' => null,
            'Company_ID' => null,
            'Department' => null,
            'Min_Amount' => null,
            'Max_Amount' => null,
            'Conditions' => json_encode([]),
            'Rejection_Mode' => 'RETURN_TO_CREATOR',
            'Rejection_Quorum' => null,
            'On_No_Higher_Level' => 'AUTO_APPROVE',
            'On_Vacancy' => 'FALLBACK',
            'Allow_Self_Approval' => 0,
            'Allow_Delegation' => 1,
            'Status' => 'ACTIVE',
            'Version' => 1,
            'Created_By' => 'seeder',
            'Created_At' => now(),
        ]);

        foreach ($steps as $step) {
            DB::table('approval_policy_step')->insert([
                'Policy_ID' => $policyId,
                'Stage_Order' => $step['Stage_Order'],
                'Level_Number' => $step['Level_Number'],
                'Position_ID' => null,
                'Position_Code' => $step['Position_Code'],
                'Department' => $step['Department'],
                'Approval_Mode' => 'ALL',
                'Quorum' => null,
                'Is_Required' => 1,
                'Label' => $step['Label'],
                'Created_At' => now(),
            ]);
        }

        $this->command->info("ApprovalTrainingPolicySeeder: {$code} dibuat dengan status ACTIVE.");
    }
}
