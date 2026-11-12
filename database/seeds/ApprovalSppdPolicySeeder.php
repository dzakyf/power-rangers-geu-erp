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
        // 1. Regular SPPD Policy - Sesuai Plafon
        // Manager Departemen Pemohon -> Manager HCGA
        $this->createPolicy(
            'SPPD-REGULAR-POLICY',
            'Approval Regular SPPD Sesuai Plafon (Manager Dept + Manager HCGA)',
            'SPPD',
            [
                ['field' => 'Sppd_Type', 'operator' => '=', 'value' => 'NORMAL'],
                ['field' => 'Is_Over_Plafon', 'operator' => '=', 'value' => 0],
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

        // 2. Regular SPPD Policy - Di Atas Plafon (Over Plafon)
        // Manager Departemen Pemohon -> Manager HCGA -> BOD (Direksi)
        $this->createPolicy(
            'SPPD-REGULAR-OVER-PLAFON-POLICY',
            'Approval Regular SPPD Di Atas Plafon (Manager Dept + Manager HCGA + BOD)',
            'SPPD',
            [
                ['field' => 'Sppd_Type', 'operator' => '=', 'value' => 'NORMAL'],
                ['field' => 'Is_Over_Plafon', 'operator' => '=', 'value' => 1],
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
                    'Label' => 'BOD (Board of Directors)',
                ],
            ]
        );

        // 3. Perbantuan Sementara Policy (> 14 hari)
        // Manager Departemen Pemohon -> Manager HCGA -> VP Director / BOD
        $this->createPolicy(
            'SPPD-PERBANTUAN-POLICY',
            'Approval Perbantuan Sementara (Manager Dept + Manager HCGA + VP Director)',
            'SPPD',
            [
                ['field' => 'Sppd_Type', 'operator' => '=', 'value' => 'PERBANTUAN'],
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
                    'Label' => 'VP Director / BOD',
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
            'Rejection_Mode' => 'RETURN_TO_CREATOR',
            'Rejection_Quorum' => 1,
            'On_No_Higher_Level' => 'AUTO_APPROVE',
            'On_Vacancy' => 'FALLBACK',
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

