<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder Policy Approval untuk Modul Vehicle Maintenance (Perawatan Kendaraan).
 *
 * Rules:
 *  - VEHICLE-MAINT-STD  : Estimasi biaya <= Rp 2.500.000
 *      Stage 1: Manager Departemen Pemohon (Level 3)
 *      Stage 2: Manager HCGA (Level 3, Position MHCGA, Dept HCGA)
 *  - VEHICLE-MAINT-HIGH : Estimasi biaya > Rp 2.500.000
 *      Stage 1: Manager Departemen Pemohon (Level 3)
 *      Stage 2: VP Director / Direktur (Level 1, Position VPD, Dept DIR)
 *      Stage 3: Manager HCGA (Level 3, Position MHCGA, Dept HCGA)
 */
class ApprovalVehicleMaintenancePolicySeeder extends Seeder
{
    public function run()
    {
        $this->seedPolicy(
            'VEHICLE-MAINT-STD',
            'Approval Perawatan Kendaraan Standar (<= 2,5 Juta)',
            0,
            2500000,
            [
                [
                    'Stage_Order'   => 1,
                    'Level_Number'  => 3,
                    'Position_Code' => null,
                    'Department'    => null,
                    'Label'         => 'Manager Departemen Pemohon',
                ],
                [
                    'Stage_Order'   => 2,
                    'Level_Number'  => 3,
                    'Position_Code' => 'MHCGA',
                    'Department'    => 'HCGA',
                    'Label'         => 'Manager HCGA',
                ],
            ]
        );

        $this->seedPolicy(
            'VEHICLE-MAINT-HIGH',
            'Approval Perawatan Kendaraan Biaya Tinggi (> 2,5 Juta)',
            2500000.01,
            null,
            [
                [
                    'Stage_Order'   => 1,
                    'Level_Number'  => 3,
                    'Position_Code' => null,
                    'Department'    => null,
                    'Label'         => 'Manager Departemen Pemohon',
                ],
                [
                    'Stage_Order'   => 2,
                    'Level_Number'  => 1,
                    'Position_Code' => 'VPD',
                    'Department'    => 'DIR',
                    'Label'         => 'VP Director / Direktur',
                ],
                [
                    'Stage_Order'   => 3,
                    'Level_Number'  => 3,
                    'Position_Code' => 'MHCGA',
                    'Department'    => 'HCGA',
                    'Label'         => 'Manager HCGA',
                ],
            ]
        );
    }

    protected function seedPolicy(string $code, string $name, $minAmount, $maxAmount, array $steps): void
    {
        $existing = DB::table('approval_policy')->where('Policy_Code', $code)->first();

        if ($existing) {
            DB::table('approval_policy')->where('ID', $existing->ID)->update([
                'Policy_Name'         => $name,
                'Request_Type'        => 'VEHICLE_MAINTENANCE',
                'Min_Amount'          => $minAmount,
                'Max_Amount'          => $maxAmount,
                'Conditions'          => json_encode([]),
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
                'Request_Type'        => 'VEHICLE_MAINTENANCE',
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
