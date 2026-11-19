<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ApprovalEmployeeDataUpdatePolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        $existing = DB::table('approval_policy')
            ->where('Request_Type', 'EMPLOYEE_DATA_UPDATE')
            ->first();

        if ($existing) {
            return;
        }

        $hcgaMgrPos = DB::table('master_position')
            ->where('Department', 'HCGA')
            ->where('Level', 3)
            ->where('Status', 'ACTIVE')
            ->first();

        $policyId = DB::table('approval_policy')->insertGetId([
            'Policy_Code'         => 'EMP-DATA-UPDATE-DEFAULT',
            'Policy_Name'         => 'Approval Perubahan Data Mandiri Karyawan (Manager HCGA)',
            'Request_Type'        => 'EMPLOYEE_DATA_UPDATE',
            'Location'            => null,
            'Company_ID'          => null,
            'Department'          => null,
            'Min_Amount'          => null,
            'Max_Amount'          => null,
            'Conditions'          => null,
            'Rejection_Mode'      => 'STOP',
            'Rejection_Quorum'    => 1,
            'On_No_Higher_Level'  => 'AUTO_APPROVE',
            'On_Vacancy'          => 'FALLBACK',
            'Allow_Self_Approval' => 0,
            'Allow_Delegation'    => 1,
            'Status'              => 'ACTIVE',
            'Version'             => 1,
            'Effective_From'      => '2026-01-01',
            'Effective_To'        => null,
            'Created_By'          => 'seeder',
            'Created_At'          => $now,
            'Updated_By'          => 'seeder',
            'Updated_At'          => $now,
        ]);

        DB::table('approval_policy_step')->insert([
            'Policy_ID'     => $policyId,
            'Stage_Order'   => 1,
            'Level_Number'  => 3,
            'Position_ID'   => $hcgaMgrPos ? (int) $hcgaMgrPos->ID : null,
            'Position_Code' => $hcgaMgrPos ? $hcgaMgrPos->Initial : null,
            'Department'    => 'HCGA',
            'Approval_Mode' => 'ANY',
            'Quorum'        => 1,
            'Is_Required'   => 1,
            'Label'         => 'Verifikasi & Persetujuan Manager HCGA',
            'Created_At'    => $now,
        ]);
    }
}
