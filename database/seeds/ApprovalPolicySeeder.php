<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApprovalPolicySeeder extends Seeder
{
    /**
     * Contoh policy approval 3 tahap:
     *   Stage 1 : Level 3 di departemen pemohon (Manager departemen)
     *   Stage 2 : Level 2 di DIR (Vice President Director)
     *   Stage 3 : Level 1 di DIR (President Director / CEO)
     *
     * Policy dibuat DRAFT agar admin dapat meninjau step dan mengaktifkannya
     * melalui hrd-approval-policy. Ganti Request_Type sesuai modul yang
     * akan memakai approval engine.
     */
    public function run()
    {
        $code = 'SAMPLE-GENERAL-3-2-1';

        if (DB::table('approval_policy')->where('Policy_Code', $code)->exists()) {
            $this->command->info('ApprovalPolicySeeder: ' . $code . ' sudah ada, dilewati.');
            return;
        }

        $policyId = DB::table('approval_policy')->insertGetId([
            'Policy_Code' => $code,
            'Policy_Name' => 'Contoh Approval General 3-2-1',
            'Request_Type' => 'GENERAL',
            'Location' => 'GEU-HO',
            'Company_ID' => 'GEU',
            'Department' => null,
            'Min_Amount' => null,
            'Max_Amount' => null,
            'Conditions' => null,
            'Rejection_Mode' => 'RETURN_TO_CREATOR',
            'Rejection_Quorum' => null,
            'On_No_Higher_Level' => 'AUTO_APPROVE',
            'On_Vacancy' => 'FALLBACK',
            'Allow_Self_Approval' => 0,
            'Allow_Delegation' => 1,
            'Status' => 'DRAFT',
            'Version' => 1,
            'Created_By' => 'seeder',
            'Created_At' => now(),
        ]);

        $steps = [
            [
                'Stage_Order' => 1,
                'Level_Number' => 3,
                'Department' => null,
                'Approval_Mode' => 'ALL',
                'Quorum' => null,
                'Label' => 'Manager departemen pemohon',
            ],
            [
                'Stage_Order' => 2,
                'Level_Number' => 2,
                'Department' => 'DIR',
                'Approval_Mode' => 'ALL',
                'Quorum' => null,
                'Label' => 'Vice President Director',
            ],
            [
                'Stage_Order' => 3,
                'Level_Number' => 1,
                'Department' => 'DIR',
                'Approval_Mode' => 'ALL',
                'Quorum' => null,
                'Label' => 'President Director / CEO',
            ],
        ];

        foreach ($steps as $step) {
            DB::table('approval_policy_step')->insert([
                'Policy_ID' => $policyId,
                'Stage_Order' => $step['Stage_Order'],
                'Level_Number' => $step['Level_Number'],
                'Position_ID' => null,
                'Position_Code' => null,
                'Department' => $step['Department'],
                'Approval_Mode' => $step['Approval_Mode'],
                'Quorum' => $step['Quorum'],
                'Is_Required' => 1,
                'Label' => $step['Label'],
                'Created_At' => now(),
            ]);
        }

        $this->command->info('ApprovalPolicySeeder: ' . $code . ' dibuat sebagai DRAFT dengan 3 step.');
    }
}