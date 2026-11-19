<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder Policy Approval untuk 4 alur Asset & Fleet Management:
 * - ASSET_MAINTENANCE_SCHEDULE
 * - VEHICLE_TAX_REQUEST
 * - VEHICLE_FLEET
 * - VEHICLE_LOGBOOK
 */
class ApprovalAssetFleetPolicySeeder extends Seeder
{
    public function run()
    {
        $policies = [
            [
                'code' => 'ASSET-MAINT-SCHEDULE-HCGA',
                'name' => 'Approval Jadwal Perawatan Aset (Manager HCGA)',
                'type' => 'ASSET_MAINTENANCE_SCHEDULE',
            ],
            [
                'code' => 'VEHICLE-TAX-HCGA',
                'name' => 'Approval Pengajuan Pajak Kendaraan (Manager HCGA)',
                'type' => 'VEHICLE_TAX_REQUEST',
            ],
            [
                'code' => 'VEHICLE-FLEET-HCGA',
                'name' => 'Approval Data Kendaraan Fleet (Manager HCGA)',
                'type' => 'VEHICLE_FLEET',
            ],
            [
                'code' => 'VEHICLE-LOGBOOK-HCGA',
                'name' => 'Approval Logbook Kendaraan (Manager HCGA)',
                'type' => 'VEHICLE_LOGBOOK',
            ],
        ];

        foreach ($policies as $p) {
            $existing = DB::table('approval_policy')->where('Policy_Code', $p['code'])->first();
            if ($existing) {
                DB::table('approval_policy')->where('ID', $existing->ID)->update([
                    'Policy_Name'         => $p['name'],
                    'Request_Type'        => $p['type'],
                    'Rejection_Mode'      => 'RETURN_TO_CREATOR',
                    'On_No_Higher_Level'  => 'AUTO_APPROVE',
                    'On_Vacancy'          => 'FALLBACK',
                    'Allow_Self_Approval' => 0,
                    'Allow_Delegation'    => 1,
                    'Status'              => 'ACTIVE',
                    'Updated_At'          => now(),
                ]);
                $policyId = $existing->ID;
                DB::table('approval_policy_step')->where('Policy_ID', $policyId)->delete();
            } else {
                $policyId = DB::table('approval_policy')->insertGetId([
                    'Policy_Code'         => $p['code'],
                    'Policy_Name'         => $p['name'],
                    'Request_Type'        => $p['type'],
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
                    'Allow_Self_Approval' => 0,
                    'Allow_Delegation'    => 1,
                    'Status'              => 'ACTIVE',
                    'Version'             => 1,
                    'Created_By'          => 'seeder',
                    'Created_At'          => now(),
                ]);
            }

            DB::table('approval_policy_step')->insert([
                'Policy_ID'     => $policyId,
                'Stage_Order'   => 1,
                'Level_Number'  => 3,
                'Position_ID'   => null,
                'Position_Code' => 'MHCGA',
                'Department'    => 'HCGA',
                'Approval_Mode' => 'ALL',
                'Quorum'        => null,
                'Is_Required'   => 1,
                'Label'         => 'Manager HCGA',
                'Created_At'    => now(),
            ]);
        }
    }
}
