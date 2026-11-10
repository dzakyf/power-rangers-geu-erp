<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder Policy Approval Dinamis untuk Modul Payroll (PAYROLL).
 * Dapat dikonfigurasi ulang oleh Administrator melalui menu /hrd-approval-policy.
 */
class ApprovalPayrollPolicySeeder extends Seeder
{
    public function run()
    {
        $code = 'PAYROLL-STANDARD-POLICY';
        $name = 'Approval Batch Penggajian / Payroll (Manager HCGA -> Manager Finance -> VP Director)';
        $requestType = 'PAYROLL';

        $steps = [
            [
                'Stage_Order'   => 1,
                'Level_Number'  => 3,
                'Department'    => 'HCGA',
                'Position_Code' => 'MHCGA',
                'Label'         => 'Verifikasi Manager HCGA',
            ],
            [
                'Stage_Order'   => 2,
                'Level_Number'  => 3,
                'Department'    => 'FIN',
                'Position_Code' => 'MFA',
                'Label'         => 'Verifikasi Manager Finance & Accounting',
            ],
            [
                'Stage_Order'   => 3,
                'Level_Number'  => 1,
                'Department'    => 'DIR',
                'Position_Code' => 'VPD',
                'Label'         => 'Persetujuan Akhir VP Director / Direksi',
            ],
        ];

        $policy = DB::table('approval_policy')->where('Policy_Code', $code)->first();
        if (!$policy) {
            $policy = DB::table('approval_policy')
                ->where('Request_Type', $requestType)
                ->where('Status', 'ACTIVE')
                ->first();
        }

        if ($policy) {
            $policyId = $policy->ID;
            // Perbaiki Approval_Mode jika sebelumnya menggunakan nilai selain ALL/ANY/QUORUM
            DB::table('approval_policy_step')
                ->where('Policy_ID', $policyId)
                ->whereNotIn('Approval_Mode', ['ALL', 'ANY', 'QUORUM'])
                ->update(['Approval_Mode' => 'ALL', 'Quorum' => null]);

            $existingStepCount = DB::table('approval_policy_step')->where('Policy_ID', $policyId)->count();
            if ($existingStepCount > 0) {
                return;
            }
        } else {
            $policyId = DB::table('approval_policy')->insertGetId([
                'Policy_Code'         => $code,
                'Policy_Name'         => $name,
                'Request_Type'        => $requestType,
                'Location'            => null,
                'Company_ID'          => null,
                'Department'          => null,
                'Min_Amount'          => null,
                'Max_Amount'          => null,
                'Conditions'          => json_encode([]),
                'Rejection_Mode'      => 'RETURN_TO_CREATOR',
                'Rejection_Quorum'    => null,
                'On_No_Higher_Level'  => 'AUTO_APPROVE',
                'On_Vacancy'          => 'FALLBACK',
                'Allow_Self_Approval' => 1,
                'Allow_Delegation'    => 1,
                'Status'              => 'ACTIVE',
                'Version'             => 1,
                'Created_By'          => 'seeder',
                'Created_At'          => now(),
            ]);
        }

        foreach ($steps as $step) {
            $pos = null;
            if (!empty($step['Position_Code'])) {
                $pos = DB::table('master_position')
                    ->where('Initial', $step['Position_Code'])
                    ->first();
            }

            DB::table('approval_policy_step')->insert([
                'Policy_ID'     => $policyId,
                'Stage_Order'   => $step['Stage_Order'],
                'Level_Number'  => $step['Level_Number'],
                'Position_ID'   => $pos ? (int)$pos->ID : null,
                'Position_Code' => $step['Position_Code'],
                'Department'    => $step['Department'],
                'Approval_Mode' => 'ALL',
                'Quorum'        => null,
                'Is_Required'   => 1,
                'Label'         => $step['Label'],
            ]);
        }
    }
}
