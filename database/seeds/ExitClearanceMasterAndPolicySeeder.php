<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExitClearanceMasterAndPolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Seed 17 Standar Item Checklist Exit Clearance (Dokumen FR/HCGA/07-00)
        $standardItems = [
            [
                'sort_order' => 1,
                'item_name' => 'Semua Kunci (kunci gudang/kantor)',
                'category' => 'ASSET_INVENTORY',
                'default_pic_role' => 'GA',
            ],
            [
                'sort_order' => 2,
                'item_name' => 'Kode dan Password Akun aplikasi /web',
                'category' => 'IT_SECURITY',
                'default_pic_role' => 'IT',
            ],
            [
                'sort_order' => 3,
                'item_name' => 'Perlengkapan Komputer/IT/Laptop/Lainnya (jika ada)',
                'category' => 'ASSET_INVENTORY',
                'default_pic_role' => 'IT',
            ],
            [
                'sort_order' => 4,
                'item_name' => 'Peralatan telekomunikasi? Tab dan Kartu SIM telepon',
                'category' => 'ASSET_INVENTORY',
                'default_pic_role' => 'IT',
            ],
            [
                'sort_order' => 5,
                'item_name' => 'Kendaraan/ Perlengkapan transportasi/Molis/Lainnya',
                'category' => 'ASSET_INVENTORY',
                'default_pic_role' => 'GA',
            ],
            [
                'sort_order' => 6,
                'item_name' => 'Perlengkapan dan material lainnya',
                'category' => 'ASSET_INVENTORY',
                'default_pic_role' => 'GA',
            ],
            [
                'sort_order' => 7,
                'item_name' => 'Catatan Telepon/ Fax atau lainnya?',
                'category' => 'ASSET_INVENTORY',
                'default_pic_role' => 'ATASAN',
            ],
            [
                'sort_order' => 8,
                'item_name' => 'Melakukan serah terima pekerjaan dengan melengkapi Form Serah Terima Pekerjaan ?',
                'category' => 'HANDOVER',
                'default_pic_role' => 'ATASAN',
            ],
            [
                'sort_order' => 9,
                'item_name' => 'Data Vendor atau pihak ketiga yang berhubungan ?',
                'category' => 'HANDOVER',
                'default_pic_role' => 'ATASAN',
            ],
            [
                'sort_order' => 10,
                'item_name' => 'Melakukan pemutusan akses pribadi ke semua jaringan system IT?',
                'category' => 'IT_SECURITY',
                'default_pic_role' => 'IT',
            ],
            [
                'sort_order' => 11,
                'item_name' => 'Melakukan pemutusan terhadap kewenangan untuk tandatangan di akun bank?',
                'category' => 'HR_FINANCE',
                'default_pic_role' => 'FINANCE',
            ],
            [
                'sort_order' => 12,
                'item_name' => 'Mengumpulkan semua asset pribadi yang tercantum dalam Form Pelaporan Aset ?',
                'category' => 'ASSET_INVENTORY',
                'default_pic_role' => 'GA',
            ],
            [
                'sort_order' => 13,
                'item_name' => 'Mengembalikan semua material yang sesuai ke HR?',
                'category' => 'HR_FINANCE',
                'default_pic_role' => 'HR',
            ],
            [
                'sort_order' => 14,
                'item_name' => 'Membatalkan semua pelatihan yang sudah dijadwalkan tapi belum terlaksana?',
                'category' => 'HR_FINANCE',
                'default_pic_role' => 'HR',
            ],
            [
                'sort_order' => 15,
                'item_name' => 'Memenuhi semua tindakan pemutusan yang sesuai melalui system HR terlebih dahulu, sesuai batas waktu cut off untuk pembayaran terakhir?',
                'category' => 'HR_FINANCE',
                'default_pic_role' => 'HR',
            ],
            [
                'sort_order' => 16,
                'item_name' => 'Mengikuti Exit Interview secara pribadi dengan HR?',
                'category' => 'HR_FINANCE',
                'default_pic_role' => 'HR',
            ],
            [
                'sort_order' => 17,
                'item_name' => 'Seragam & ID Card',
                'category' => 'ASSET_INVENTORY',
                'default_pic_role' => 'HR',
            ],
        ];

        foreach ($standardItems as $item) {
            $exists = DB::table('master_exit_clearance_items')
                ->where('item_name', $item['item_name'])
                ->first();

            if (!$exists) {
                DB::table('master_exit_clearance_items')->insert(array_merge($item, [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        // 2. Policy: EMPLOYEE_RESIGNATION (Approval Atasan Langsung)
        $this->createPolicy(
            'POLICY-EMPLOYEE-RESIGNATION',
            'Approval Pengajuan Resign Karyawan (Atasan Langsung)',
            'EMPLOYEE_RESIGNATION',
            [],
            [
                [
                    'Stage_Order' => 1,
                    'Level_Number' => 3, // Level Atasan / Manager
                    'Department' => null,
                    'Position_Code' => null,
                    'Label' => 'Atasan Langsung / Dept Head',
                ]
            ]
        );

        // 3. Policy: EXIT_CLEARANCE (Tahap 1: Exit Interview oleh HCGA, Tahap 2: Approval Atasan Departemen Terkait, Tahap 3: Verifikasi Final HCGA)
        $this->createPolicy(
            'POLICY-EXIT-CLEARANCE',
            'Approval Exit Clearance & Interview (HCGA -> Atasan Departemen -> Verifikasi HCGA)',
            'EXIT_CLEARANCE',
            [],
            [
                [
                    'Stage_Order' => 1,
                    'Level_Number' => 3,
                    'Department' => 'HCGA',
                    'Position_Code' => null,
                    'Label' => 'HCGA (Exit Interview & Clearance)',
                ],
                [
                    'Stage_Order' => 2,
                    'Level_Number' => 3,
                    'Department' => null,
                    'Position_Code' => null,
                    'Label' => 'Atasan Langsung / Dept Head (Approval Departemen Terkait)',
                ],
                [
                    'Stage_Order' => 3,
                    'Level_Number' => 3,
                    'Department' => 'HCGA',
                    'Position_Code' => 'MHCGA',
                    'Label' => 'HCGA Manager (Final Clearance & Verification)',
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
