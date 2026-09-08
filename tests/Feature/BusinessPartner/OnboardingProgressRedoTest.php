<?php

namespace Tests\Feature\BusinessPartner;

use App\Http\Controllers\OnboardingController;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regression tests for Partner Management - Approval > progress stepper
 * (partner-approval-progress).
 *
 * Defect: re-doing an already-completed onboarding step (e.g. re-issuing the
 * DP invoice on a fully onboarded partner) regressed
 * opportunity_partner.onboarding_stage backwards and downgraded
 * already-completed partner_onboarding_log rows to 'in_progress'. The
 * stepper derives everything from that single linear stage index, so steps
 * after the redone one became disabled even though the data recorded every
 * step as completed, and the next stage flipped back to In_progress.
 *
 * Policy (per client decision):
 *   - onboarding_stage may only move strictly forward in the stage order and
 *     never changes once it reaches 'ACTIVE';
 *   - a 'completed' log row is never downgraded to 'in_progress';
 *   - re-completing a step still refreshes that step's own row.
 *
 * Routes under test (routes/web.php:4697, middleware web+log.access):
 *   POST partner-onboarding-log/update -> ApprovalController@updateOnboardingLog
 * plus OnboardingController@advanceOnboardingStage (invoked directly, as the
 * MOU/PKS signing and survey-approval flows do).
 *
 * All fixture tables are InnoDB and the inserts happen inside the per-test
 * transaction opened by DatabaseTransactions. The `users` table is MyISAM
 * and not transactional - an existing identity is reused read-only.
 */
class OnboardingProgressRedoTest extends TestCase
{
    use DatabaseTransactions;

    private const STAGE_ORDER = [
        'DOCUMENT_CHECK', 'MOU', 'INVOICE', 'PKS', 'SURVEY', 'SETUP', 'ACTIVE',
    ];

    private const STAGE_LABELS = [
        'DOCUMENT_CHECK' => 'Cek Dokumen',
        'MOU' => 'Penandatanganan MOU',
        'INVOICE' => 'Penerbitan Invoice DP',
        'PKS' => 'Perjanjian Kerjasama',
        'SURVEY' => 'Survey Lokasi',
        'SETUP' => 'Installment',
        'ACTIVE' => 'Aktif',
    ];

    private const PAST_DATE = '2026-08-18 07:45:41';

    /**
     * The `users` table is MyISAM and does not participate in the per-test
     * rollback, so we reuse an existing identity read-only.
     *
     * @var User|null
     */
    private $user;

    private function testUser(): User
    {
        if ($this->user) {
            return $this->user;
        }

        $user = User::query()->orderBy('id')->first();

        if (!$user) {
            $this->markTestSkipped('No user exists in the test database.');
        }

        return $this->user = $user;
    }

    private function seedPartner(string $stage): int
    {
        return DB::table('opportunity_partner')->insertGetId([
            'full_name' => 'Onboarding Redo Test Partner',
            'company_name' => 'Onboarding Redo Test Co',
            'status' => 'ACTIVE',
            'lead_id' => 'BP/TEST-ONBOARDING-REDO/0001',
            'onboarding_stage' => $stage,
            'onboarding_stage_updated_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedStageLog(int $opportunityId, string $stage, string $status): int
    {
        return DB::table('partner_onboarding_log')->insertGetId([
            'Opportunity_Id' => $opportunityId,
            'Stage' => $stage,
            'Stage_Label' => self::STAGE_LABELS[$stage] ?? $stage,
            'Status' => $status,
            'Completed_At' => $status === 'completed' ? self::PAST_DATE : null,
            'Created_At' => self::PAST_DATE,
            'Updated_At' => self::PAST_DATE,
        ]);
    }

    private function seedAllStagesCompleted(int $opportunityId): void
    {
        foreach (self::STAGE_ORDER as $stage) {
            $this->seedStageLog($opportunityId, $stage, 'completed');
        }
    }

    private function updateLog(int $opportunityId, string $stage, string $status)
    {
        return $this->actingAs($this->testUser())
            ->post('/partner-onboarding-log/update', [
                'Opportunity_Id' => $opportunityId,
                'Stage' => $stage,
                'Status' => $status,
                'Notes' => '',
            ]);
    }

    private function stageOf(int $opportunityId): ?string
    {
        return DB::table('opportunity_partner')->where('id', $opportunityId)->value('onboarding_stage');
    }

    private function logRow(int $opportunityId, string $stage)
    {
        return DB::table('partner_onboarding_log')
            ->where('Opportunity_Id', $opportunityId)
            ->where('Stage', $stage)
            ->first();
    }

    /** @test */
    public function test_redo_on_active_partner_keeps_stage_active_and_other_rows_completed(): void
    {
        $id = $this->seedPartner('ACTIVE');
        $this->seedAllStagesCompleted($id);

        // Redo step 3 (invoice issuance): the step re-completes...
        $this->updateLog($id, 'INVOICE', 'completed')->assertOk();

        $this->assertSame('ACTIVE', $this->stageOf($id), 'A redo must not regress the stage of a fully onboarded partner');
        $invoice = $this->logRow($id, 'INVOICE');
        $this->assertSame('completed', $invoice->Status);
        $this->assertSame(
            now()->format('Y-m-d'),
            \Illuminate\Support\Carbon::parse($invoice->Completed_At)->format('Y-m-d'),
            'Re-completing the redone step must refresh its completion timestamp'
        );

        // ...and the handler chain marks the next stage in_progress.
        $this->updateLog($id, 'PKS', 'in_progress')->assertOk();

        $pks = $this->logRow($id, 'PKS');
        $this->assertSame('completed', $pks->Status, 'A completed row must never be downgraded to in_progress');
        $this->assertSame(self::PAST_DATE, $pks->Completed_At, 'The untouched completed row keeps its original timestamp');
        $this->assertSame('ACTIVE', $this->stageOf($id));
    }

    /** @test */
    public function test_midflow_redo_does_not_move_stage_backwards(): void
    {
        $id = $this->seedPartner('PKS');
        $this->seedStageLog($id, 'INVOICE', 'in_progress');

        $this->updateLog($id, 'INVOICE', 'completed')->assertOk();

        $this->assertSame('PKS', $this->stageOf($id), 'Re-completing an earlier step must not move the stage backwards');
        $this->assertSame('completed', $this->logRow($id, 'INVOICE')->Status);
    }

    /** @test */
    public function test_forward_flow_still_advances_stage(): void
    {
        $id = $this->seedPartner('DOCUMENT_CHECK');

        $this->updateLog($id, 'MOU', 'in_progress')->assertOk();

        $this->assertSame('MOU', $this->stageOf($id), 'The forward onboarding flow must keep advancing the stage');
        $this->assertSame('in_progress', $this->logRow($id, 'MOU')->Status);
    }

    /** @test */
    public function test_advance_onboarding_stage_does_not_regress_active_partner(): void
    {
        $id = $this->seedPartner('ACTIVE');
        $this->seedStageLog($id, 'PKS', 'completed');

        $this->actingAs($this->testUser());
        app(OnboardingController::class)->advanceOnboardingStage($id, 'PKS', 'SURVEY', 'test');

        $this->assertSame('ACTIVE', $this->stageOf($id), 'advanceOnboardingStage must not pull an ACTIVE partner backwards');
        $pks = $this->logRow($id, 'PKS');
        $this->assertSame('completed', $pks->Status);
    }
}
