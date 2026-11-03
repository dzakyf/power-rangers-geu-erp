<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder Policy Approval untuk Modul Absensi (Cuti/Izin & Koreksi Jam).
 */
class ApprovalAttendancePolicySeeder extends Seeder
{
    public function run()
    {
        $this->createPolicy(
            'ATT-LEAVE-POLICY',
            'Approval Pengajuan Cuti & Izin (Manager Dept + Manager HCGA)',
            'ATTENDANCE_LEAVE',
            [],
            [
                ['Stage_Order' => 1, 'Level_Number' => 3, 'Department' => null, 'Position_Code' => null, 'Label' => 'Manager Departemen Pemohon'],
                ['Stage_Order' => 2, 'Level_Number' => 3, 'Department' => 'HCGA', 'Position_Code' => 'MHCGA', 'Label' => 'Manager HCGA'],
            ]
        );

        $this->createPolicy(
            'ATT-CORR-POLICY',
            'Approval Pengajuan Koreksi Jam Absensi (Manager Dept + Manager HCGA)',
            'ATTENDANCE_CORRECTION',
            [],
            [
                ['Stage_Order' => 1, 'Level_Number' => 3, 'Department' => null, 'Position_Code' => null, 'Label' => 'Manager Departemen Pemohon'],
                ['Stage_Order' => 2, 'Level_Number' => 3, 'Department' => 'HCGA', 'Position_Code' => 'MHCGA', 'Label' => 'Manager HCGA'],
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
                'Approval_Mode' => 'ALL',
                'Quorum'        => null,
                'Is_Required'   => 1,
                'Label'         => $step['Label'],
            ]);
        }
    }
}
