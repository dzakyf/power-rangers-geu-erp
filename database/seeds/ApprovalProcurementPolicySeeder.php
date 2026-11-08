<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder Policy Approval untuk Modul Procurement / Purchase Request (PR).
 *
 * Rules:
 *  - PR-STANDARD-STAFF : Estimasi biaya <= 2.500.000
 *      Stage 1: Manager Dept Pemohon (Level 3)
 *      Stage 2: Manager HCGA (Level 3 / HCGA)
 *  - PR-STANDARD-HIGH  : Estimasi biaya > 2.500.000 atau Kategori ASSET
 *      Stage 1: Manager Dept Pemohon (Level 3)
 *      Stage 2: VP Dept / Atasan (Level 2)
 *      Stage 3: Manager HCGA (Level 3 / HCGA)
 *
 * Status dibuat ACTIVE agar langsung dapat digunakan di modul Procurement.
 */
class ApprovalProcurementPolicySeeder extends Seeder
{
    public function run()
    {
        $codeStaff = 'PR-STANDARD-STAFF';
        $codeHigh  = 'PR-STANDARD-HIGH';

        $this->createPolicy(
            $codeStaff,
            'Approval Procurement Standard (Manager Dept + Manager HCGA)',
            [
                ['field' => 'Estimated_Budget', 'operator' => '<=', 'value' => 2500000],
            ],
            [
                ['Stage_Order' => 1, 'Level_Number' => 3, 'Department' => null, 'Position_Code' => null, 'Label' => 'Manager Departemen Pemohon'],
                ['Stage_Order' => 2, 'Level_Number' => 3, 'Department' => 'HCGA', 'Position_Code' => 'MHCGA', 'Label' => 'Manager HCGA'],
            ]
        );

        $this->createPolicy(
            $codeHigh,
            'Approval Procurement High / Asset (Manager Dept + VP Dept + Manager HCGA)',
            [
                ['field' => 'Estimated_Budget', 'operator' => '>', 'value' => 2500000],
            ],
            [
                ['Stage_Order' => 1, 'Level_Number' => 3, 'Department' => null, 'Position_Code' => null, 'Label' => 'Manager Departemen Pemohon'],
                ['Stage_Order' => 2, 'Level_Number' => 1, 'Department' => 'DIR', 'Position_Code' => 'VPD', 'Label' => 'VP Director / Atasan'],
                ['Stage_Order' => 3, 'Level_Number' => 3, 'Department' => 'HCGA', 'Position_Code' => 'MHCGA', 'Label' => 'Manager HCGA'],
            ]
        );
    }

    protected function createPolicy(string $code, string $name, array $conditions, array $steps, string $department = null): void
    {
        $policy = DB::table('approval_policy')->where('Policy_Code', $code)->first();
        if ($policy) {
            DB::table('approval_policy')->where('ID', $policy->ID)->update([
                'Policy_Name' => $name,
                'Status' => 'ACTIVE',
                'Conditions' => json_encode($conditions),
                'Updated_At' => now(),
            ]);
            $policyId = $policy->ID;
            DB::table('approval_policy_step')->where('Policy_ID', $policyId)->delete();
        } else {
            $policyId = DB::table('approval_policy')->insertGetId([
                'Policy_Code' => $code,
                'Policy_Name' => $name,
                'Request_Type' => 'PROCUREMENT',
                'Location' => null,
                'Company_ID' => null,
                'Department' => $department,
                'Min_Amount' => null,
                'Max_Amount' => null,
                'Conditions' => json_encode($conditions),
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
        }

        foreach ($steps as $step) {
            DB::table('approval_policy_step')->insert([
                'Policy_ID' => $policyId,
                'Stage_Order' => $step['Stage_Order'],
                'Level_Number' => $step['Level_Number'],
                'Position_ID' => null,
                'Position_Code' => $step['Position_Code'] ?? null,
                'Department' => $step['Department'] ?? null,
                'Approval_Mode' => 'ALL',
                'Quorum' => null,
                'Is_Required' => 1,
                'Label' => $step['Label'],
                'Created_At' => now(),
            ]);
        }
    }
}

