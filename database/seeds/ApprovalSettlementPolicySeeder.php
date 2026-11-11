<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ApprovalSettlementPolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        // Cek apakah policy SETTLEMENT sudah ada
        $existing = DB::table('approval_policy')->where('Request_Type', 'SETTLEMENT')->first();
        if ($existing) {
            return;
        }

        // 1. Policy Settlement Standar (Total Biaya Real <= 2.500.000)
        // Stage 1: Atasan / Manager Departemen Pemohon
        // Stage 2: Manager HCGA
        $policyLowId = DB::table('approval_policy')->insertGetId([
            'Policy_Code'         => 'STTL-STANDARD-LOW',
            'Policy_Name'         => 'Approval Settlement SPPD <= 2.500.000 (Manager Dept + Manager HCGA)',
            'Request_Type'        => 'SETTLEMENT',
            'Location'            => null,
            'Company_ID'          => null,
            'Department'          => null,
            'Min_Amount'          => null,
            'Max_Amount'          => 2500000.00,
            'Conditions'          => null,
            'Rejection_Mode'      => 'RETURN_TO_CREATOR',
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
            [
                'Policy_ID'     => $policyLowId,
                'Stage_Order'   => 1,
                'Level_Number'  => 3,
                'Position_ID'   => null,
                'Position_Code' => null,
                'Department'    => null,
                'Approval_Mode' => 'ALL',
                'Quorum'        => null,
                'Is_Required'   => 1,
                'Label'         => 'Manager Departemen Pemohon',
                'Created_At'    => $now,
            ],
            [
                'Policy_ID'     => $policyLowId,
                'Stage_Order'   => 2,
                'Level_Number'  => 3,
                'Position_ID'   => null,
                'Position_Code' => 'MHCGA',
                'Department'    => 'HCGA',
                'Approval_Mode' => 'ALL',
                'Quorum'        => null,
                'Is_Required'   => 1,
                'Label'         => 'Manager HCGA',
                'Created_At'    => $now,
            ],
        ]);

        // 2. Policy Settlement Nominal Tinggi (Total Biaya Real > 2.500.000)
        // Stage 1: Atasan / Manager Departemen Pemohon
        // Stage 2: Manager HCGA
        // Stage 3: VP Director
        $policyHighId = DB::table('approval_policy')->insertGetId([
            'Policy_Code'         => 'STTL-STANDARD-HIGH',
            'Policy_Name'         => 'Approval Settlement SPPD > 2.500.000 (Manager Dept + Manager HCGA + VP Director)',
            'Request_Type'        => 'SETTLEMENT',
            'Location'            => null,
            'Company_ID'          => null,
            'Department'          => null,
            'Min_Amount'          => 2500000.01,
            'Max_Amount'          => null,
            'Conditions'          => null,
            'Rejection_Mode'      => 'RETURN_TO_CREATOR',
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
            [
                'Policy_ID'     => $policyHighId,
                'Stage_Order'   => 1,
                'Level_Number'  => 3,
                'Position_ID'   => null,
                'Position_Code' => null,
                'Department'    => null,
                'Approval_Mode' => 'ALL',
                'Quorum'        => null,
                'Is_Required'   => 1,
                'Label'         => 'Manager Departemen Pemohon',
                'Created_At'    => $now,
            ],
            [
                'Policy_ID'     => $policyHighId,
                'Stage_Order'   => 2,
                'Level_Number'  => 3,
                'Position_ID'   => null,
                'Position_Code' => 'MHCGA',
                'Department'    => 'HCGA',
                'Approval_Mode' => 'ALL',
                'Quorum'        => null,
                'Is_Required'   => 1,
                'Label'         => 'Manager HCGA',
                'Created_At'    => $now,
            ],
            [
                'Policy_ID'     => $policyHighId,
                'Stage_Order'   => 3,
                'Level_Number'  => 2,
                'Position_ID'   => null,
                'Position_Code' => null,
                'Department'    => null,
                'Approval_Mode' => 'ALL',
                'Quorum'        => null,
                'Is_Required'   => 1,
                'Label'         => 'VP Director',
                'Created_At'    => $now,
            ],
        ]);
    }
}

