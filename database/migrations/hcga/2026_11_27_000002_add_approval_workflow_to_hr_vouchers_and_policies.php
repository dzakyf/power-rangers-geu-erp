<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddApprovalWorkflowToHrVouchersAndPolicies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('finance_hr_vouchers') && !Schema::hasColumn('finance_hr_vouchers', 'Approval_Request_ID')) {
            Schema::table('finance_hr_vouchers', function (Blueprint $table) {
                $table->unsignedBigInteger('Approval_Request_ID')->nullable()->after('Status');
                $table->index('Approval_Request_ID', 'idx_fhv_approval_req_id');
            });
        }

        if (Schema::hasTable('approval_policy') && Schema::hasTable('approval_policy_step')) {
            $now = now();

            $this->upsertPolicy(
                'HR-VOUCHER-LOW',
                'Pengajuan Voucher & AP Manual <= Rp 3.000.000 (Manager Dept + Manager HCGA)',
                'HR_VOUCHER',
                null,
                3000000.00,
                [
                    ['Stage_Order' => 1, 'Level_Number' => 3, 'Department' => null,   'Position_Code' => null,    'Label' => 'Manager departemen pemohon'],
                    ['Stage_Order' => 2, 'Level_Number' => 3, 'Department' => 'HCGA', 'Position_Code' => 'MHCGA', 'Label' => 'Manager HCGA'],
                ],
                $now
            );

            $this->upsertPolicy(
                'HR-VOUCHER-HIGH',
                'Pengajuan Voucher & AP Manual > Rp 3.000.000 (Manager Dept + Manager HCGA + VP Director)',
                'HR_VOUCHER',
                3000000.01,
                null,
                [
                    ['Stage_Order' => 1, 'Level_Number' => 3, 'Department' => null,   'Position_Code' => null,    'Label' => 'Manager departemen pemohon'],
                    ['Stage_Order' => 2, 'Level_Number' => 3, 'Department' => 'HCGA', 'Position_Code' => 'MHCGA', 'Label' => 'Manager HCGA'],
                    ['Stage_Order' => 3, 'Level_Number' => 1, 'Department' => null,   'Position_Code' => 'VPD',   'Label' => 'Vice President Director'],
                ],
                $now
            );

            $this->upsertPolicy(
                'GA-WO-LOW',
                'GA Work Order <= Rp 3.000.000 (Manager Dept + Manager HCGA)',
                'GA_WORK_ORDER',
                null,
                3000000.00,
                [
                    ['Stage_Order' => 1, 'Level_Number' => 3, 'Department' => null,   'Position_Code' => null,    'Label' => 'Manager departemen pemohon'],
                    ['Stage_Order' => 2, 'Level_Number' => 3, 'Department' => 'HCGA', 'Position_Code' => 'MHCGA', 'Label' => 'Manager HCGA'],
                ],
                $now
            );

            $this->upsertPolicy(
                'GA-WO-HIGH',
                'GA Work Order > Rp 3.000.000 (Manager Dept + Manager HCGA + VP Director)',
                'GA_WORK_ORDER',
                3000000.01,
                null,
                [
                    ['Stage_Order' => 1, 'Level_Number' => 3, 'Department' => null,   'Position_Code' => null,    'Label' => 'Manager departemen pemohon'],
                    ['Stage_Order' => 2, 'Level_Number' => 3, 'Department' => 'HCGA', 'Position_Code' => 'MHCGA', 'Label' => 'Manager HCGA'],
                    ['Stage_Order' => 3, 'Level_Number' => 1, 'Department' => null,   'Position_Code' => 'VPD',   'Label' => 'Vice President Director'],
                ],
                $now
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('finance_hr_vouchers') && Schema::hasColumn('finance_hr_vouchers', 'Approval_Request_ID')) {
            Schema::table('finance_hr_vouchers', function (Blueprint $table) {
                $table->dropIndex('idx_fhv_approval_req_id');
                $table->dropColumn('Approval_Request_ID');
            });
        }

        if (Schema::hasTable('approval_policy') && Schema::hasTable('approval_policy_step')) {
            $ids = DB::table('approval_policy')
                ->whereIn('Policy_Code', ['HR-VOUCHER-LOW', 'HR-VOUCHER-HIGH'])
                ->pluck('ID')
                ->all();
            if (!empty($ids)) {
                DB::table('approval_policy_step')->whereIn('Policy_ID', $ids)->delete();
                DB::table('approval_policy')->whereIn('ID', $ids)->delete();
            }
        }
    }

    protected function upsertPolicy(
        string $code,
        string $name,
        string $requestType,
        ?float $minAmount,
        ?float $maxAmount,
        array $steps,
        $now
    ): void {
        $existing = DB::table('approval_policy')->where('Policy_Code', $code)->first();

        if ($existing) {
            DB::table('approval_policy')->where('ID', (int) $existing->ID)->update([
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
                'Updated_At'          => $now,
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
                'Conditions'          => null,
                'Rejection_Mode'      => 'RETURN_TO_CREATOR',
                'Rejection_Quorum'    => null,
                'On_No_Higher_Level'  => 'AUTO_APPROVE',
                'On_Vacancy'          => 'FALLBACK',
                'Allow_Self_Approval' => 0,
                'Allow_Delegation'    => 1,
                'Status'              => 'ACTIVE',
                'Version'             => 1,
                'Effective_From'      => null,
                'Effective_To'        => null,
                'Created_By'          => 'migration',
                'Created_At'          => $now,
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
                'Created_At'    => $now,
            ]);
        }
    }
}
