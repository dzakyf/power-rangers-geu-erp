<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Contoh policy untuk modul Requisition/PTK.
 *
 * Policy dipisahkan berdasarkan Position_Level dari posisi yang diminta:
 *  - PTK-STAFF   : posisi staff (Level 4 ke atas) -> Manager dept + Manager HCGA
 *  - PTK-MANAGER : posisi manager/atas (Level 1-3) -> Manager dept + VP dept
 *                  + Manager HCGA + VP Director
 *  - PTK-HCGA-*  : varian untuk pemohon dari departemen HCGA. Dipisahkan agar
 *                  Manager HCGA tidak muncul dua kali pada stage berbeda.
 *
 * Status sengaja DRAFT. Aktifkan melalui hrd-approval-policy setelah step
 * dan position assignment diperiksa. Request_Type yang dipakai adapter adalah
 * REQUISITION (lihat App\Services\Approval\RequisitionApprovalService).
 */
class ApprovalRequisitionPolicySeeder extends Seeder
{
    public function run()
    {
        $staffCode = 'PTK-STAFF-3-HCGA';
        $managerCode = 'PTK-MANAGER-3-2-HCGA-VPD';

        $this->createPolicy(
            $staffCode,
            'Approval PTK Staff (Manager Dept + Manager HCGA)',
            [
                ['field' => 'Position_Level', 'operator' => 'not_in', 'value' => [1, 2, 3]],
            ],
            [
                ['Stage_Order' => 1, 'Level_Number' => 3, 'Department' => null, 'Position_Code' => null, 'Label' => 'Manager departemen pemohon'],
                ['Stage_Order' => 2, 'Level_Number' => 3, 'Department' => null, 'Position_Code' => 'MHCGA', 'Label' => 'Manager HCGA'],
            ]
        );

        $this->createPolicy(
            $managerCode,
            'Approval PTK Manager (Manager Dept + VP Dept + Manager HCGA + VP Director)',
            [
                ['field' => 'Position_Level', 'operator' => 'in', 'value' => [1, 2, 3]],
            ],
            [
                ['Stage_Order' => 1, 'Level_Number' => 3, 'Department' => null, 'Position_Code' => null, 'Label' => 'Manager departemen pemohon'],
                ['Stage_Order' => 2, 'Level_Number' => 2, 'Department' => null, 'Position_Code' => null, 'Label' => 'VP departemen pemohon'],
                ['Stage_Order' => 3, 'Level_Number' => 3, 'Department' => null, 'Position_Code' => 'MHCGA', 'Label' => 'Manager HCGA'],
                ['Stage_Order' => 4, 'Level_Number' => 2, 'Department' => null, 'Position_Code' => 'VPD', 'Label' => 'Vice President Director'],
            ]
        );

        $this->createPolicy(
            'PTK-HCGA-STAFF',
            'Approval PTK Staff HCGA (Manager HCGA)',
            [
                ['field' => 'Position_Level', 'operator' => 'not_in', 'value' => [1, 2, 3]],
            ],
            [
                ['Stage_Order' => 1, 'Level_Number' => 3, 'Department' => 'HCGA', 'Position_Code' => 'MHCGA', 'Label' => 'Manager HCGA'],
            ],
            'HCGA'
        );

        $this->createPolicy(
            'PTK-HCGA-MANAGER',
            'Approval PTK Manager HCGA (Manager HCGA + VP Director)',
            [
                ['field' => 'Position_Level', 'operator' => 'in', 'value' => [1, 2, 3]],
            ],
            [
                ['Stage_Order' => 1, 'Level_Number' => 3, 'Department' => 'HCGA', 'Position_Code' => 'MHCGA', 'Label' => 'Manager HCGA'],
                ['Stage_Order' => 2, 'Level_Number' => 2, 'Department' => 'HCGA', 'Position_Code' => 'VPD', 'Label' => 'Vice President Director'],
            ],
            'HCGA'
        );
    }

    protected function createPolicy(string $code, string $name, array $conditions, array $steps, string $department = null): void
    {
        if (DB::table('approval_policy')->where('Policy_Code', $code)->exists()) {
            $this->command->info('ApprovalRequisitionPolicySeeder: ' . $code . ' sudah ada, dilewati.');
            return;
        }

        $policyId = DB::table('approval_policy')->insertGetId([
            'Policy_Code' => $code,
            'Policy_Name' => $name,
            'Request_Type' => 'REQUISITION',
            'Location' => null,
            'Company_ID' => null,
            'Department' => $department,
            'Min_Amount' => null,
            'Max_Amount' => null,
            'Conditions' => json_encode($conditions),
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

        foreach ($steps as $step) {
            DB::table('approval_policy_step')->insert([
                'Policy_ID' => $policyId,
                'Stage_Order' => $step['Stage_Order'],
                'Level_Number' => $step['Level_Number'],
                'Position_ID' => null,
                'Position_Code' => $step['Position_Code'],
                'Department' => $step['Department'],
                'Approval_Mode' => 'ALL',
                'Quorum' => null,
                'Is_Required' => 1,
                'Label' => $step['Label'],
                'Created_At' => now(),
            ]);
        }

        $this->command->info('ApprovalRequisitionPolicySeeder: ' . $code . ' dibuat sebagai DRAFT.');
    }
}
