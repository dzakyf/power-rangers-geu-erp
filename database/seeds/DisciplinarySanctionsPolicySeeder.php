<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DisciplinarySanctionsPolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Seed Master Policy Sanksi Disiplin Karyawan
        $policies = [
            [
                'Violation_Category' => 'RINGAN',
                'Sanction_Level' => 'LISAN',
                'Default_Duration_Months' => 1,
                'Description' => 'Peringatan Lisan untuk ketidakteraturan absensi minor atau teguran tata tertib.',
                'Requires_Meeting' => 0,
                'Status' => 'ACTIVE',
            ],
            [
                'Violation_Category' => 'RINGAN',
                'Sanction_Level' => 'SP1',
                'Default_Duration_Months' => 6,
                'Description' => 'Surat Peringatan Pertama untuk pelanggaran SOP atau tata tertib ringan berulang.',
                'Requires_Meeting' => 0,
                'Status' => 'ACTIVE',
            ],
            [
                'Violation_Category' => 'SEDANG',
                'Sanction_Level' => 'SP2',
                'Default_Duration_Months' => 6,
                'Description' => 'Surat Peringatan Kedua jika masih ada SP1 aktif atau pelanggaran operasional cukup berat.',
                'Requires_Meeting' => 1,
                'Status' => 'ACTIVE',
            ],
            [
                'Violation_Category' => 'BERAT',
                'Sanction_Level' => 'SP3',
                'Default_Duration_Months' => 6,
                'Description' => 'Surat Peringatan Ketiga (Terakhir) untuk pelanggaran fatal/berat atau SP2 aktif.',
                'Requires_Meeting' => 1,
                'Status' => 'ACTIVE',
            ],
        ];

        foreach ($policies as $pol) {
            $existing = DB::table('disciplinary_sanction_policies')
                ->where('Violation_Category', $pol['Violation_Category'])
                ->where('Sanction_Level', $pol['Sanction_Level'])
                ->first();

            if (!$existing) {
                DB::table('disciplinary_sanction_policies')->insert(array_merge($pol, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        // 2. Daftarkan Approval Policy untuk Disciplinary Sanction (SP)
        $this->createPolicy(
            'POLICY-DISCIPLINARY-SANCTION',
            'Approval Surat Peringatan & Sanksi Disiplin Karyawan (HCGA)',
            'DISCIPLINARY_SANCTION',
            [],
            [
                [
                    'Stage_Order' => 1,
                    'Level_Number' => 2,
                    'Department' => 'HCGA',
                    'Position_Code' => null,
                    'Label' => 'Verifikasi & Klarifikasi HCGA',
                ],
                [
                    'Stage_Order' => 2,
                    'Level_Number' => 3,
                    'Department' => 'HCGA',
                    'Position_Code' => 'MHCGA',
                    'Label' => 'Pengesahan Head of HCGA',
                ],
            ]
        );
    }

    protected function createPolicy(string $code, string $name, string $requestType, array $conditions, array $steps): void
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
                'Department'          => null,
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

