<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder Policy Approval untuk Modul SPPD (Surat Perintah Perjalanan Dinas).
 */
class ApprovalSppdPolicySeeder extends Seeder
{
    public function run()
    {
        // 1. Regular SPPD Policy (Manager Departemen Pemohon -> Manager HCGA)
        $this->createPolicy(
            'SPPD-REGULAR-POLICY',
            'Approval Regular SPPD (Manager Dept + Manager HCGA)',
            'SPPD',
            [
                'Sppd_Type' => 'NORMAL',
            ],
            [
                [
                    'Stage_Order' => 1,
                    'Level_Number' => 3,
                    'Department' => null,
                    'Position_Code' => null,
                    'Label' => 'Manager Departemen Pemohon',
                ],
                [
                    'Stage_Order' => 2,
                    'Level_Number' => 3,
                    'Department' => 'HCGA',
                    'Position_Code' => 'MHCGA',
                    'Label' => 'Manager HCGA',
                ],
            ]
        );

        // 2. Perbantuan Sementara Policy (> 14 hari)
        // Manager Departemen Pemohon -> Manager HCGA -> VP Director
        $this->createPolicy(
            'SPPD-PERBANTUAN-POLICY',
            'Approval Perbantuan Sementara (Manager Dept + Manager HCGA + VP Director)',
            'SPPD',
            [
                'Sppd_Type' => 'PERBANTUAN',
            ],
            [
                [
                    'Stage_Order' => 1,
                    'Level_Number' => 3,
                    'Department' => null,
                    'Position_Code' => null,
                    'Label' => 'Manager Departemen Pemohon',
                ],
                [
                    'Stage_Order' => 2,
                    'Level_Number' => 3,
                    'Department' => 'HCGA',
                    'Position_Code' => 'MHCGA',
                    'Label' => 'Manager HCGA',
                ],
                [
                    'Stage_Order' => 3,
                    'Level_Number' => 2,
                    'Department' => null,
                    'Position_Code' => null,
                    'Label' => 'VP Director',
                ],
            ]
        );
    }

    protected function createPolicy(string $code, string $name, string $requestType, array $conditions, array $steps, string $department = null): void
    {
        $policy = DB::table('approval_policy')->where('Policy_Code', $code)->first();

        $policyData = [
            'Policy_Code' => $code,
            'Policy_Name' => $name,
            'Request_Type' => $requestType,
            'Location' => null,
            'Company_ID' => null,
            'Department' => $department,
            'Min_Amount' => null,
            'Max_Amount' => null,
            'Conditions' => !empty($conditions) ? json_encode($conditions) : null,
            'Rejection_Mode' => 'STOP',
            'Rejection_Quorum' => 1,
            'On_No_Higher_Level' => 'APPROVE',
            'On_Vacancy' => 'SKIP',
            'Allow_Self_Approval' => 0,
            'Allow_Delegation' => 1,
            'Status' => 'ACTIVE',
            'Version' => 1,
            'Effective_From' => '2026-01-01',
            'Effective_To' => null,
            'Created_By' => 'system',
            'Updated_By' => 'system',
            'Updated_At' => now(),
        ];

        if ($policy) {
            DB::table('approval_policy')->where('ID', $policy->ID)->update($policyData);
            $policyId = $policy->ID;
        } else {
            $policyData['Created_At'] = now();
            $policyId = DB::table('approval_policy')->insertGetId($policyData);
        }

        DB::table('approval_policy_step')->where('Policy_ID', $policyId)->delete();

        foreach ($steps as $step) {
            DB::table('approval_policy_step')->insert([
                'Policy_ID' => $policyId,
                'Stage_Order' => $step['Stage_Order'],
                'Level_Number' => $step['Level_Number'],
                'Position_ID' => $step['Position_ID'] ?? null,
                'Position_Code' => $step['Position_Code'] ?? null,
                'Department' => $step['Department'] ?? null,
                'Approval_Mode' => 'ALL',
                'Quorum' => null,
                'Is_Required' => 1,
                'Label' => $step['Label'] ?? null,
                'Created_At' => now(),
            ]);
        }
    }
}

