<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder Policy Approval untuk Modul Perubahan Status Karyawan (Mutasi & Promosi).
 *
 * Tahapan Approval:
 *  1. Dept. Head Asal (Manager Departemen Asal / Pemohon) - Level 3
 *  2. Dept. Head Tujuan (Manager Departemen Tujuan) - Level 3 (Kondisi: jika mutasi/perubahan dept)
 *  3. Verifikator HCGA (Manager HCGA) - Level 3, MHCGA
 *  4. Direktur / Mengetahui - Level 1 / 2, VPD / Director
 */
class ApprovalStatusChangePolicySeeder extends Seeder
{
    public function run()
    {
        // 1. Kebijakan Default Mutasi & Promosi Standar
        $this->createPolicy(
            'STATUS-CHANGE-STANDARD',
            'Approval Perubahan Status Karyawan (Dept Head Asal + Dept Head Tujuan + HCGA + Direktur)',
            'STATUS_CHANGE',
            [],
            [
                ['Stage_Order' => 1, 'Level_Number' => 3, 'Department' => null, 'Position_Code' => null, 'Label' => 'Dept. Head Asal'],
                ['Stage_Order' => 2, 'Level_Number' => 3, 'Department' => null, 'Position_Code' => 'MHCGA', 'Label' => 'Senior Manager HCGA'],
                ['Stage_Order' => 3, 'Level_Number' => 2, 'Department' => null, 'Position_Code' => 'VPD', 'Label' => 'Direktur'],
            ]
        );
    }

    protected function createPolicy(string $code, string $name, string $requestType, array $conditions, array $steps, string $department = null): void
    {
        $policy = DB::table('approval_policy')->where('Policy_Code', $code)->first();
        if ($policy) {
            DB::table('approval_policy')->where('ID', $policy->ID)->update([
                'Policy_Name'  => $name,
                'Request_Type' => $requestType,
                'Status'       => 'ACTIVE',
                'Conditions'   => json_encode($conditions),
                'Updated_At'   => now(),
            ]);
            $policyId = $policy->ID;
            DB::table('approval_policy_step')->where('Policy_ID', $policyId)->delete();
        } else {
            $policyId = DB::table('approval_policy')->insertGetId([
                'Policy_Code'         => $code,
                'Policy_Name'         => $name,
                'Request_Type'        => $requestType,
                'Location'            => null,
                'Company_ID'          => null,
                'Department'          => $department,
                'Min_Amount'          => null,
                'Max_Amount'          => null,
                'Conditions'          => json_encode($conditions),
                'Rejection_Mode'      => 'RETURN_TO_CREATOR',
                'Rejection_Quorum'    => null,
                'On_No_Higher_Level'  => 'AUTO_APPROVE',
                'On_Vacancy'          => 'FALLBACK',
                'Allow_Self_Approval' => 0,
                'Allow_Delegation'    => 1,
                'Status'              => 'ACTIVE',
                'Version'             => 1,
                'Created_By'          => 'seeder',
                'Created_At'          => now(),
            ]);
        }

        foreach ($steps as $step) {
            DB::table('approval_policy_step')->insert([
                'Policy_ID'     => $policyId,
                'Stage_Order'   => $step['Stage_Order'],
                'Level_Number'  => $step['Level_Number'],
                'Position_ID'   => null,
                'Position_Code' => $step['Position_Code'] ?? null,
                'Department'    => $step['Department'] ?? null,
                'Approval_Mode' => 'ANY',
                'Quorum'        => 1,
                'Is_Required'   => 1,
                'Label'         => $step['Label'] ?? null,
            ]);
        }
    }
}
