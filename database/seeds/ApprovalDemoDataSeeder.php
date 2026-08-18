<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApprovalDemoDataSeeder extends Seeder
{
    protected $userId = 2360;          // testing1022@gmail.com
    protected $opportunityId = 64;     // Testing 1022 (opportunity_partner)

    public function run()
    {
        $user = DB::table('users')->where('id', $this->userId)->first();
        $opp  = DB::table('opportunity_partner')->where('id', $this->opportunityId)->first();

        if (!$user || !$opp) {
            $this->command->warn("Cannot seed demo approvals: user id {$this->userId} or opportunity id {$this->opportunityId} was not found.");
            return;
        }

        $now = now();

        DB::transaction(function () use ($now) {
            $this->cleanup();

            $levelId = function (string $type, int $n) {
                return DB::table('approval_level')
                    ->where('Request_Type', $type)
                    ->where('Level_Number', $n)
                    ->value('ID');
            };

            $approverId = function (string $position) {
                return DB::table('users')
                    ->where('Position', $position)
                    ->orderBy('id')
                    ->value('id');
            };

            $approvers = [
                'SALES'     => $approverId('SALES'),
                'CFO'       => $approverId('CFO'),
                'MANAGER'   => $approverId('MANAGER'),
                'DIRECTOR'  => $approverId('DIRECTOR'),
            ];

            // ---- 1. ONBOARDING (fully completed) ----
            $onbId = $this->insertRequest([
                'Status'                 => 'APPROVED',
                'Description'            => 'Onboarding disetujui',
                'Request_Type'           => 'ONBOARDING',
                'Opportunity_Partner_Id' => $this->opportunityId,
                'Current_Level'          => 4,
                'Level_Id'               => null,
            ]);

            $onbLevels = [
                1 => $levelId('ONBOARDING', 1),
                2 => $levelId('ONBOARDING', 2),
                3 => $levelId('ONBOARDING', 3),
                4 => $levelId('ONBOARDING', 4),
            ];
            $onbApprovers = [1 => $approvers['SALES'], 2 => $approvers['CFO'], 3 => $approvers['MANAGER'], 4 => $approvers['DIRECTOR']];
            $onbNotes = ['', '', '', 'Pengajuan disetujui'];

            for ($n = 1; $n <= 4; $n++) {
                DB::table('approval_log')->insert([
                    'Request_Id'    => $onbId,
                    'Approver_Id'   => $onbApprovers[$n],
                    'Action'        => 'APPROVED',
                    'Notes'         => $onbNotes[$n - 1],
                    'Level_Number'  => $n,
                    'Next_Level'    => $n + 1,
                    'Level_Id'      => $onbLevels[$n],
                    'Next_Level_Id' => $onbLevels[$n + 1] ?? null,
                    'Action_Date'   => $now,
                ]);
            }

            // Complete the onboarding stage timeline
            $stageLabels = [
                'DOCUMENT_CHECK' => 'Cek Dokumen',
                'MOU'            => 'Penandatanganan MOU',
                'INVOICE'        => 'Penerbitan Invoice DP',
                'PKS'            => 'Perjanjian Kerjasama',
                'SURVEY'         => 'Survey Lokasi',
                'SETUP'          => 'Installment',
                'ACTIVE'         => 'Aktif',
            ];
            foreach ($stageLabels as $stage => $label) {
                DB::table('partner_onboarding_log')->insert([
                    'Opportunity_Id' => $this->opportunityId,
                    'Stage'          => $stage,
                    'Stage_Label'    => $label,
                    'Status'         => 'completed',
                    'Completed_At'   => $now,
                    'Completed_By'   => $this->userId,
                    'Notes'          => 'Tahap selesai',
                    'Created_At'     => $now,
                    'Updated_At'     => $now,
                ]);
            }

            // ---- 2. PROFIT_SHARING (fresh) ----
            $this->insertRequest([
                'Description'            => 'Pengajuan royalti bulanan',
                'Request_Type'           => 'PROFIT_SHARING',
                'Opportunity_Partner_Id' => $this->opportunityId,
                'Current_Level'          => 0,
                'Level_Id'               => $levelId('PROFIT_SHARING', 1),
            ]);

            // ---- 3. RENEWAL (fresh) ----
            DB::table('partner_renewal')->insert([
                'Opportunity_Id'  => $this->opportunityId,
                'Contract_End_At' => $now->copy()->addYear(),
                'Renewed_Length'  => 1,
                'Created_At'      => $now,
                'Status'          => 'PENDING',
            ]);

            $renewalId = $this->insertRequest([
                'Description'            => 'Request perpanjangan kemitraan',
                'Request_Type'           => 'RENEWAL',
                'Opportunity_Partner_Id' => $this->opportunityId,
                'Current_Level'          => 0,
                'Level_Id'               => $levelId('RENEWAL', 1),
            ]);
            $this->insertInitialLog($renewalId, 'IN_PROGRESS', $now);

            // ---- 4. TERMINATION (fresh) ----
            $termId = DB::table('partner_termination')->insertGetId([
                'Opportunity_Id' => $this->opportunityId,
                'Type'           => 'PENSIUN',
                'Created_At'     => $now,
                'Status'         => 'PENDING',
            ]);

            $terminationId = $this->insertRequest([
                'Description'            => 'Pengajuan pengakhiran kemitraan',
                'Request_Type'           => 'TERMINATION',
                'Opportunity_Partner_Id' => $this->opportunityId,
                'Map_To'                 => $termId,
                'Current_Level'          => 0,
                'Level_Id'               => $levelId('TERMINATION', 1),
            ]);
            $this->insertInitialLog($terminationId, 'IN_PROGRESS', $now);

            // ---- 5. TRANSFER_MEMBERSHIP (fresh) ----
            $transferId = DB::table('partner_transfer_membership')->insertGetId([
                'Opportunity_Id' => (string) $this->opportunityId,
                'New_Mitra_Name' => 'Testing Penerima 1022',
                'Type'           => 'PERORANGAN',
                'Reason'         => 'Pengalihan kepemilikan kemitraan',
                'Applied_At'     => $now,
                'Contract_No'    => 'PTF-TEST-2026-0001',
            ]);

            $transferReqId = $this->insertRequest([
                'Description'            => 'Transfer kepemilikan kemitraan',
                'Request_Type'           => 'TRANSFER_MEMBERSHIP',
                'Opportunity_Partner_Id' => $this->opportunityId,
                'Map_To'                 => $transferId,
                'Current_Level'          => 0,
                'Level_Id'               => $levelId('TRANSFER_MEMBERSHIP', 1),
            ]);
            $this->insertInitialLog($transferReqId, 'IN_PROGRESS', $now);

            // ---- 6. SURVEY (fresh) ----
            $surveyId = DB::table('survey_location')->insertGetId([
                'Province'               => 'DKI Jakarta',
                'City'                   => 'Jakarta',
                'District'               => 'Kebayoran Baru',
                'Subdistrict'            => 'Senayan',
                'Address'                => 'Jl. Test No. 1',
                'Location_Name'          => 'Lokasi Testing 1022',
                'Status'                 => 'NEED_APPROVAL',
                'Code'                   => 'SL-TEST-2026-0001',
                'Opportunity_Partner_Id' => $this->opportunityId,
                'Total_Score'            => 0,
                'Created_At'             => $now,
                'Updated_At'             => $now,
            ]);

            $surveyReqId = $this->insertRequest([
                'Description'            => 'Persetujuan hasil survey lokasi',
                'Request_Type'           => 'SURVEY',
                'Opportunity_Partner_Id' => $this->opportunityId,
                'Map_To'                 => $surveyId,
                'Current_Level'          => 0,
                'Level_Id'               => $levelId('SURVEY', 1),
            ]);
            $this->insertInitialLog($surveyReqId, 'NEED_APPROVAL', $now);

            // ---- 7. BRANDING_PROMOTION (fresh) ----
            $promoId = DB::table('branding_promotion')->insertGetId([
                'Code'         => 'BR-TEST-2026-0001',
                'Department'   => 'Marketing',
                'Type'         => 'ONLINE',
                'Platform'     => 'GOOGLE_ADS',
                'Marketing_Id' => 'MKT-TEST-001',
                'Description'  => 'Promosi branding test',
                'Audience'     => 'Umum',
                'Estimation'   => 1000000.00,
                'Status'       => 'SUBMIT',
                'Created_At'   => $now,
            ]);

            $promoReqId = $this->insertRequest([
                'Description'  => 'Pengajuan promo branding',
                'Request_Type' => 'BRANDING_PROMOTION',
                'Map_To'       => $promoId,
                'Current_Level' => 0,
                'Level_Id'     => $levelId('BRANDING_PROMOTION', 1),
            ]);
            $this->insertInitialLog($promoReqId, 'IN_PROGRESS', $now);
        });

        $count = DB::table('approval_request')->where('Requester_Id', $this->userId)->count();
        $this->command->info("Done. {$count} approval_request rows seeded for user id {$this->userId} (testing1022@gmail.com).");
    }

    private function insertRequest(array $data): int
    {
        return DB::table('approval_request')->insertGetId(array_merge([
            'Status'       => 'NEED_APPROVAL',
            'Requester_Id' => $this->userId,
            'Submitted_At' => now(),
        ], $data));
    }

    private function insertInitialLog(int $requestId, string $action, $now): void
    {
        DB::table('approval_log')->insert([
            'Request_Id'    => $requestId,
            'Approver_Id'   => $this->userId,
            'Action'        => $action,
            'Notes'         => 'Initial Review',
            'Level_Number'  => 0,
            'Next_Level'    => 1,
            'Action_Date'   => $now,
        ]);
    }

    private function cleanup(): void
    {
        $reqIds = DB::table('approval_request')->where('Requester_Id', $this->userId)->pluck('ID');
        if ($reqIds->isNotEmpty()) {
            DB::table('approval_log')->whereIn('Request_Id', $reqIds)->delete();
        }
        DB::table('approval_request')->where('Requester_Id', $this->userId)->delete();

        DB::table('partner_renewal')->where('Opportunity_Id', $this->opportunityId)->delete();
        DB::table('partner_termination')->where('Opportunity_Id', $this->opportunityId)->delete();
        DB::table('partner_transfer_membership')->where('Opportunity_Id', (string) $this->opportunityId)->delete();
        DB::table('survey_location')->where('Opportunity_Partner_Id', $this->opportunityId)->where('Code', 'LIKE', '%TEST%')->delete();
        DB::table('branding_promotion')->where('Code', 'LIKE', '%TEST%')->delete();
        DB::table('partner_onboarding_log')->where('Opportunity_Id', $this->opportunityId)->delete();
    }
}
