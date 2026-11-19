<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdatePerformanceKpiPolicyAndReviewType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $policy = DB::table('approval_policy')->where('Policy_Code', 'PERF-KPI-POLICY')->first();
        if ($policy) {
            DB::table('approval_policy')
                ->where('ID', $policy->ID)
                ->update([
                    'Policy_Name' => 'Approval Penetapan Target KPI & OKR (Atasan Langsung + Manager HCGA)',
                    'Updated_At'  => now(),
                ]);

            DB::table('approval_policy_step')
                ->where('Policy_ID', $policy->ID)
                ->where('Stage_Order', 1)
                ->update([
                    'Level_Number'  => 99,
                    'Position_ID'   => null,
                    'Position_Code' => null,
                    'Department'    => null,
                    'Label'         => 'Atasan Langsung',
                ]);
        }

        if (Schema::hasTable('performance_review_cycles')) {
            DB::statement("ALTER TABLE performance_review_cycles MODIFY COLUMN review_type ENUM('ANNUAL', 'MID_YEAR', 'SEMESTER_1', 'SEMESTER_2', 'PROBATION', 'PROJECT') NOT NULL DEFAULT 'SEMESTER_1'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $policy = DB::table('approval_policy')->where('Policy_Code', 'PERF-KPI-POLICY')->first();
        if ($policy) {
            DB::table('approval_policy_step')
                ->where('Policy_ID', $policy->ID)
                ->where('Stage_Order', 1)
                ->update([
                    'Level_Number' => 3,
                    'Label'        => 'Manager Departemen Karyawan',
                ]);
        }
    }
}
