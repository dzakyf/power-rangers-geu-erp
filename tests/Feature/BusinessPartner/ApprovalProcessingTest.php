<?php

namespace Tests\Feature\BusinessPartner;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regression tests for Partner Management - Approval processing of the
 * RENEWAL / TERMINATION / TRANSFER_MEMBERSHIP flows.
 *
 * Verified defects locked in here:
 *   1. partner-termination-approval-approve routed to a handler that did not
 *      exist (routes/web.php:5036) -> every Approve/Reject 500ed.
 *   2. Approving only mutated approval_request/approval_log - the flow rows
 *      (partner_renewal / partner_termination / partner_transfer_membership)
 *      stayed PENDING forever, no contract extension, no offboarding.
 *   3. Flow submissions never set approval_request.Level_Id / Submitted_At
 *      (and renewal also never set Map_To), so renewal requests were
 *      invisible to approvers (the grid INNER JOINs approval_level on
 *      Level_Id).
 *   4. GET partner-transfer-membership-approval-create returned a view that
 *      does not exist (approval.transfer-membership-create).
 *   5. The log grid endpoints receive request_id= but the onboarding log
 *      builder only read appr_id -> the filter was ignored.
 *   6. processToNextLevel overwrote request Description on every approve.
 *
 * Side-effect policy (per client decision):
 *   RENEWAL final approve  -> partner_renewal APPROVED + extend
 *                             opportunity_partner.contract_end_at by
 *                             Renewed_Length months when set.
 *   TERMINATION final      -> partner_termination APPROVED +
 *                             opportunity_partner.status='TERMINATED' +
 *                             linked warehouses INACTIVE.
 *   TRANSFER final approve -> Result='APPROVED' + Effective_Transfer_At.
 *   Rejection at any level -> flow row marked REJECTED.
 *
 * The approval engine authorizes by users.Position vs
 * approval_level.Approver_Position; fixtures use Current_Level=3 so a
 * DIRECTOR user can reach the final level in one action. The `users` table
 * is MyISAM and not transactional - existing identities are reused
 * read-only, never inserted.
 *
 * All fixture tables are InnoDB and inserts happen inside the per-test
 * transaction opened by DatabaseTransactions.
 */
class ApprovalProcessingTest extends TestCase
{
    use DatabaseTransactions;

    private const APPROVE_URLS = [
        'RENEWAL' => '/partner-renewal-approval-approve',
        'TERMINATION' => '/partner-termination-approval-approve',
        'TRANSFER_MEMBERSHIP' => '/partner-transfer-membership-approval-approve',
    ];

    private const LEAD_ID = 'BP/TEST-APPROVAL/0001';

    /**
     * @var array<string,int>|null
     */
    private $userIdsByPosition;

    // ------------------------------------------------------------------
    // Fixtures
    // ------------------------------------------------------------------

    private function userIdAtPosition(string $position): ?int
    {
        if ($this->userIdsByPosition === null) {
            $this->userIdsByPosition = DB::table('users')
                ->whereIn('Position', ['SALES', 'DIRECTOR'])
                ->orderBy('id')
                ->get(['id', 'Position'])
                ->groupBy('Position')
                ->map(fn ($group) => $group->first()->id)
                ->all();
        }

        return $this->userIdsByPosition[$position] ?? null;
    }

    private function actingUser(string $position): User
    {
        $id = $this->userIdAtPosition($position);

        if (!$id) {
            $this->markTestSkipped("No user with Position={$position} exists in the test database.");
        }

        return User::query()->findOrFail($id);
    }

    private function seedOpportunity(?string $contractEndAt = null): int
    {
        return DB::table('opportunity_partner')->insertGetId([
            'full_name' => 'Approval Test Partner',
            'company_name' => 'Approval Test Co',
            'status' => 'ACTIVE',
            'lead_id' => self::LEAD_ID,
            'contract_end_at' => $contractEndAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedWarehouse(int $opportunityId): int
    {
        return DB::table('warehouse_partner')->insertGetId([
            'warehouse_task_id' => 'TEST-APPR-1',
            'warehouse_type' => 'booth',
            'Location' => 'Test',
            'warehouse_location' => 'Lokasi Approval Test',
            'warehouse_status' => 'ACTIVE',
            'partner_task_id' => self::LEAD_ID,
            'capacity' => 0,
            'Created_Date' => now(),
            'Created_By' => '__test_approval__',
        ]);
    }

    private function seedFlowRow(string $type, int $opportunityId, array $overrides = []): int
    {
        $rows = [
            'RENEWAL' => [
                'Opportunity_Id' => $opportunityId,
                'Created_At' => now(),
                'Status' => 'PENDING',
            ],
            'TERMINATION' => [
                'Opportunity_Id' => $opportunityId,
                'Type' => 'PENSIUN',
                'Created_At' => now(),
                'Status' => 'PENDING',
            ],
            'TRANSFER_MEMBERSHIP' => [
                'Opportunity_Id' => $opportunityId,
                'New_Mitra_Name' => 'Mitra Baru Test',
                'Type' => 'JUAL',
                'Reason' => 'alasan test',
            ],
        ];

        return DB::table($type === 'RENEWAL' ? 'partner_renewal'
            : ($type === 'TERMINATION' ? 'partner_termination' : 'partner_transfer_membership'))
            ->insertGetId(array_merge($rows[$type], $overrides));
    }

    private function seedRequest(string $type, int $opportunityId, array $overrides = []): int
    {
        $currentLevel = $overrides['Current_Level'] ?? 0;
        $levelId = DB::table('approval_level')
            ->where('Request_Type', $type)
            ->where('Level_Number', $currentLevel + 1)
            ->value('ID');

        return DB::table('approval_request')->insertGetId(array_merge([
            'Status' => 'NEED_APPROVAL',
            'Requester_Id' => $this->userIdAtPosition('SALES'),
            'Description' => 'Initial Review',
            'Request_Type' => $type,
            'Opportunity_Partner_Id' => $opportunityId,
            'Current_Level' => $currentLevel,
            'Level_Id' => $levelId,
            'Submitted_At' => now(),
            'Created_At' => now(),
        ], $overrides));
    }

    private function seedLog(int $requestId, int $levelId, string $action = 'IN_PROGRESS'): int
    {
        return DB::table('approval_log')->insertGetId([
            'Request_Id' => $requestId,
            'Approver_Id' => $this->userIdAtPosition('SALES'),
            'Action' => $action,
            'Notes' => 'seeded',
            'Level_Number' => 1,
            'Next_Level' => 1,
            'Level_Id' => $levelId,
            'Action_Date' => now(),
        ]);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function approve(string $type, int $requestId, string $action, User $user)
    {
        return $this->actingAs($user)->post(self::APPROVE_URLS[$type], [
            'id' => $requestId,
            'action' => $action,
        ]);
    }

    private function flowTable(string $type): string
    {
        return $type === 'RENEWAL' ? 'partner_renewal'
            : ($type === 'TERMINATION' ? 'partner_termination' : 'partner_transfer_membership');
    }

    // ------------------------------------------------------------------
    // Termination
    // ------------------------------------------------------------------

    /** @test */
    public function test_termination_final_approval_triggers_full_offboarding(): void
    {
        $opportunityId = $this->seedOpportunity();
        $warehouseId = $this->seedWarehouse($opportunityId);
        $terminationId = $this->seedFlowRow('TERMINATION', $opportunityId);
        $requestId = $this->seedRequest('TERMINATION', $opportunityId, [
            'Current_Level' => 3,
            'Map_To' => $terminationId,
        ]);

        $response = $this->approve('TERMINATION', $requestId, 'APPROVED', $this->actingUser('DIRECTOR'));

        $response->assertOk();
        $this->assertTrue($response->json('success'));

        $this->assertSame('APPROVED', DB::table('approval_request')->where('ID', $requestId)->value('Status'));
        $termination = DB::table('partner_termination')->where('ID', $terminationId)->first();
        $this->assertSame('APPROVED', $termination->Status, 'Termination row must be approved');
        $this->assertNotNull($termination->Approved_At);
        $this->assertSame('TERMINATED', DB::table('opportunity_partner')->where('id', $opportunityId)->value('status'));
        $this->assertSame('INACTIVE', DB::table('warehouse_partner')->where('id', $warehouseId)->value('warehouse_status'));
    }

    /** @test */
    public function test_termination_rejection_marks_flow_row_rejected(): void
    {
        $opportunityId = $this->seedOpportunity();
        $terminationId = $this->seedFlowRow('TERMINATION', $opportunityId);
        $requestId = $this->seedRequest('TERMINATION', $opportunityId, ['Map_To' => $terminationId]);

        $response = $this->approve('TERMINATION', $requestId, 'REJECTED', $this->actingUser('SALES'));

        $response->assertOk();
        $this->assertTrue($response->json('success'));

        $this->assertSame('REJECTED', DB::table('approval_request')->where('ID', $requestId)->value('Status'));
        $this->assertSame('REJECTED', DB::table('partner_termination')->where('ID', $terminationId)->value('Status'));
    }

    // ------------------------------------------------------------------
    // Transfer membership
    // ------------------------------------------------------------------

    /** @test */
    public function test_transfer_final_approval_marks_result_and_effective_date(): void
    {
        $opportunityId = $this->seedOpportunity();
        $transferId = $this->seedFlowRow('TRANSFER_MEMBERSHIP', $opportunityId);
        $requestId = $this->seedRequest('TRANSFER_MEMBERSHIP', $opportunityId, [
            'Current_Level' => 3,
            'Map_To' => $transferId,
        ]);

        $response = $this->approve('TRANSFER_MEMBERSHIP', $requestId, 'APPROVED', $this->actingUser('DIRECTOR'));

        $response->assertOk();
        $this->assertTrue($response->json('success'));

        $transfer = DB::table('partner_transfer_membership')->where('ID', $transferId)->first();
        $this->assertSame('APPROVED', $transfer->Result, 'Transfer result must be set on final approval');
        $this->assertNotNull($transfer->Effective_Transfer_At);
    }

    /** @test */
    public function test_transfer_create_page_renders_the_application_form(): void
    {
        $response = $this->actingAs($this->actingUser('SALES'))
            ->get('/partner-transfer-membership-approval-create');

        $response->assertOk();
        $response->assertViewIs('partner.renewal.transfer-membership-create-application');
    }

    // ------------------------------------------------------------------
    // Renewal
    // ------------------------------------------------------------------

    /** @test */
    public function test_renewal_final_approval_extends_contract(): void
    {
        $opportunityId = $this->seedOpportunity('2026-12-31 00:00:00');
        $renewalId = $this->seedFlowRow('RENEWAL', $opportunityId, [
            'Renewed_Length' => 2,
            'Status' => 'PENDING',
        ]);
        $requestId = $this->seedRequest('RENEWAL', $opportunityId, [
            'Current_Level' => 3,
            'Map_To' => $renewalId,
        ]);

        $response = $this->approve('RENEWAL', $requestId, 'APPROVED', $this->actingUser('DIRECTOR'));

        $response->assertOk();
        $this->assertTrue($response->json('success'));

        $renewal = DB::table('partner_renewal')->where('ID', $renewalId)->first();
        $this->assertSame('APPROVED', $renewal->Status, 'Renewal row must be approved');
        $this->assertNotNull($renewal->Approved_At);

        $contractEnd = DB::table('opportunity_partner')->where('id', $opportunityId)->value('contract_end_at');
        $this->assertSame(
            Carbon::parse('2026-12-31 00:00:00')->addMonths(2)->format('Y-m-d'),
            Carbon::parse($contractEnd)->format('Y-m-d'),
            'contract_end_at must be extended by Renewed_Length months'
        );
    }

    /** @test */
    public function test_renewal_submission_sets_level_mapto_and_submitted_at(): void
    {
        $opportunityId = $this->seedOpportunity();

        $response = $this->actingAs($this->actingUser('SALES'))
            ->post('/partner-renewal-submit-application', [
                'opportunity_partner_id' => $opportunityId,
                'file' => UploadedFile::fake()->create('renewal.pdf', 10),
            ]);

        $response->assertOk();

        $request = DB::table('approval_request')
            ->where('Request_Type', 'RENEWAL')
            ->where('Opportunity_Partner_Id', $opportunityId)
            ->orderBy('ID', 'desc')
            ->first();

        $this->assertNotNull($request);
        $this->assertNotNull($request->Level_Id, 'Submission must set Level_Id so approver grids list it');
        $this->assertNotNull($request->Submitted_At);
        $this->assertNotNull($request->Map_To, 'Submission must link the request to its partner_renewal row');

        $renewalRow = DB::table('partner_renewal')
            ->where('Opportunity_Id', $opportunityId)
            ->orderBy('ID', 'desc')
            ->first();
        $this->assertSame((int) $renewalRow->ID, (int) $request->Map_To);
    }

    /** @test */
    public function test_renewal_request_with_level_id_is_visible_to_sales_approver(): void
    {
        $opportunityId = $this->seedOpportunity();
        $requestId = $this->seedRequest('RENEWAL', $opportunityId);

        $response = $this->actingAs($this->actingUser('SALES'))
            ->get('/partner-profit-sharing-data?user_id=&type=RENEWAL');

        $response->assertOk();
        // The builder emits the request ID as the first cell (the view's
        // selectMitra reads that cell) - assert on it.
        $this->assertStringContainsString('<cell>'.$requestId.'</cell>', (string) $response->getContent());
    }

    /** @test */
    public function test_approve_preserves_request_description(): void
    {
        $opportunityId = $this->seedOpportunity();
        $requestId = $this->seedRequest('RENEWAL', $opportunityId, ['Description' => 'Initial Review']);

        $response = $this->approve('RENEWAL', $requestId, 'APPROVED', $this->actingUser('SALES'));

        $response->assertOk();
        $this->assertSame(
            'Initial Review',
            DB::table('approval_request')->where('ID', $requestId)->value('Description'),
            'Approving must not overwrite the original request description'
        );
    }

    // ------------------------------------------------------------------
    // Approval log endpoint
    // ------------------------------------------------------------------

    /** @test */
    public function test_log_endpoint_filters_by_request_id(): void
    {
        $opportunityId = $this->seedOpportunity();
        $requestA = $this->seedRequest('TERMINATION', $opportunityId);
        $requestB = $this->seedRequest('RENEWAL', $opportunityId);

        $this->seedLog($requestA, 17, 'IN_PROGRESS-A');
        $this->seedLog($requestB, 13, 'IN_PROGRESS-B');

        $response = $this->actingAs($this->actingUser('SALES'))
            ->get('/partner-approval-data-log?request_id='.$requestA.'&user_id=&type=TERMINATION');

        $response->assertOk();
        $body = (string) $response->getContent();

        $this->assertStringContainsString('IN_PROGRESS-A', $body);
        $this->assertStringNotContainsString('IN_PROGRESS-B', $body, 'Log grid must honor the request_id filter');
    }
}
