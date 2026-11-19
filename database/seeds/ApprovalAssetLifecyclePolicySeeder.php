<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder Policy Approval untuk 3 alur Siklus Hidup Aset:
 * - ASSET_FIXING (Perbaikan Aset)
 * - ASSET_MOVING (Pemindahan Aset)
 * - ASSET_DISPOSAL (Pemusnahan Aset + Verifikasi Write-Off Finance)
 */
class ApprovalAssetLifecyclePolicySeeder extends Seeder
{
    public function run()
    {
        $this->upsertPolicy(
            'ASSET-FIXING-LOW',
            'Approval Perbaikan Aset <= 2.500.000 (Atasan + HCGA)',
            'ASSET_FIXING',
            null,
            2500000,
            [
                ['Stage_Order' => 1, 'Level_Number' => 3, 'Department' => null, 'Position_Code' => null, 'Label' => 'Manager Departemen Pemohon'],
                ['Stage_Order' => 2, 'Level_Number' => 3, 'Department' => 'HCGA', 'Position_Code' => 'MHCGA', 'Label' => 'Manager HCGA'],
            ]
        );

        $this->upsertPolicy(
            'ASSET-FIXING-HIGH',
            'Approval Perbaikan Aset > 2.500.000 (Atasan + VP + HCGA + Finance)',
            'ASSET_FIXING',
            2500000.01,
            null,
            [
                ['Stage_Order' => 1, 'Level_Number' => 3, 'Department' => null, 'Position_Code' => null, 'Label' => 'Manager Departemen Pemohon'],
                ['Stage_Order' => 2, 'Level_Number' => 1, 'Department' => 'DIR', 'Position_Code' => 'VPD', 'Label' => 'VP Director / Atasan'],
                ['Stage_Order' => 3, 'Level_Number' => 3, 'Department' => 'HCGA', 'Position_Code' => 'MHCGA', 'Label' => 'Manager HCGA'],
                ['Stage_Order' => 4, 'Level_Number' => 3, 'Department' => 'FIN', 'Position_Code' => 'MFA', 'Label' => 'Verifikasi Biaya Finance'],
            ]
        );

        $this->upsertPolicy(
            'ASSET-MOVING-STANDARD',
            'Approval Pemindahan Aset (Atasan Pemohon + HCGA + Direksi)',
            'ASSET_MOVING',
            null,
            null,
            [
                ['Stage_Order' => 1, 'Level_Number' => 3, 'Department' => null, 'Position_Code' => null, 'Label' => 'Atasan Pemohon (APPROVAL_UPPER)'],
                ['Stage_Order' => 2, 'Level_Number' => 3, 'Department' => 'HCGA', 'Position_Code' => 'MHCGA', 'Label' => 'GA / Manager HCGA (APPROVAL_GA)'],
                ['Stage_Order' => 3, 'Level_Number' => 1, 'Department' => 'DIR', 'Position_Code' => 'VPD', 'Label' => 'Direksi / BOD (APPROVAL_BOD)'],
            ]
        );

        $this->upsertPolicy(
            'ASSET-DISPOSAL-STANDARD',
            'Approval Pemusnahan Aset (Atasan + HCGA + Direksi + Write-Off Finance)',
            'ASSET_DISPOSAL',
            null,
            null,
            [
                ['Stage_Order' => 1, 'Level_Number' => 3, 'Department' => null, 'Position_Code' => null, 'Label' => 'Kadiv / Manager Departemen Pemohon'],
                ['Stage_Order' => 2, 'Level_Number' => 3, 'Department' => 'HCGA', 'Position_Code' => 'MHCGA', 'Label' => 'Manager HCGA'],
                ['Stage_Order' => 3, 'Level_Number' => 1, 'Department' => 'DIR', 'Position_Code' => 'VPD', 'Label' => 'Direktur / BOD'],
                ['Stage_Order' => 4, 'Level_Number' => 3, 'Department' => 'FIN', 'Position_Code' => 'MFA', 'Label' => 'Verifikasi Write-Off Finance'],
            ]
        );
    }

    protected function upsertPolicy(
        string $code,
        string $name,
        string $requestType,
        ?float $minAmount,
        ?float $maxAmount,
        array $steps
    ): void {
        $existing = DB::table('approval_policy')->where('Policy_Code', $code)->first();

        if ($existing) {
            DB::table('approval_policy')->where('ID', $existing->ID)->update([
                'Policy_Name'         => $name,
                'Request_Type'        => $requestType,
                'Min_Amount'          => $minAmount,
                'Max_Amount'          => $maxAmount,
                'Rejection_Mode'      => 'RETURN_TO_CREATOR',
                'On_No_Higher_Level'  => 'AUTO_APPROVE',
                'On_Vacancy'          => 'FALLBACK',
                'Allow_Self_Approval' => 0,
                'Allow_Delegation'    => 1,
                'Status'              => 'ACTIVE',
                'Updated_At'          => now(),
            ]);
            $policyId = (int) $existing->ID;
            DB::table('approval_policy_step')->where('Policy_ID', $policyId)->delete();
        } else {
            $policyId = (int) DB::table('approval_policy')->insertGetId([
                'Policy_Code'         => $code,
                'Policy_Name'         => $name,
                'Request_Type'        => $requestType,
                'Location'            => null,
                'Company_ID'          => null,
                'Department'          => null,
                'Min_Amount'          => $minAmount,
                'Max_Amount'          => $maxAmount,
                'Conditions'          => json_encode([]),
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
                'Position_Code' => $step['Position_Code'],
                'Department'    => $step['Department'],
                'Approval_Mode' => 'ALL',
                'Quorum'        => null,
                'Is_Required'   => 1,
                'Label'         => $step['Label'],
                'Created_At'    => now(),
            ]);
        }
    }
}
