<?php

namespace Tests\Feature\BusinessPartner;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regression tests for Keuangan -> Approval (profit sharing / royalty).
 *
 * The Adminor (Bootstrap 5) migration left this page using Bootstrap 4
 * dropdown attributes and a log grid whose JS variable collided with the
 * #ProjectionGrid element id.
 */
class ProfitSharingApprovalUiTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::where('Position', 'Administrator')->firstOrFail();
    }

    private function seedOpportunity(): int
    {
        return DB::table('opportunity_partner')->insertGetId([
            'full_name' => 'Profit Sharing Approval Test',
            'company_name' => 'Profit Sharing Co',
            'status' => 'ACTIVE',
            'lead_id' => 'BP/TEST-PS-APPROVAL/' . uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function levelId(int $levelNumber): ?int
    {
        return DB::table('approval_level')
            ->where('Request_Type', 'PROFIT_SHARING')
            ->where('Level_Number', $levelNumber)
            ->value('ID');
    }

    private function seedRequest(int $opportunityId, array $overrides = []): int
    {
        return DB::table('approval_request')->insertGetId(array_merge([
            'Status' => 'NEED_APPROVAL',
            'Requester_Id' => $this->admin()->id,
            'Description' => 'Initial Review',
            'Request_Type' => 'PROFIT_SHARING',
            'Opportunity_Partner_Id' => $opportunityId,
            'Current_Level' => 0,
            'Level_Id' => $this->levelId(1),
            'Submitted_At' => now(),
            'Created_At' => now(),
        ], $overrides));
    }

    private function seedAccountPayable(): int
    {
        return DB::table('account_payable')->insertGetId([
            'Task_ID' => 'TEST-AP-' . uniqid(),
            'Reference' => 'TEST/REF/' . uniqid(),
            'Status' => 'WAITING_PAYMENT',
            'PPH_Type' => '0',
            'Total' => 100,
            'Dateinsert' => now(),
        ]);
    }

    private function mitra(): ?User
    {
        return User::where('Position', 'MITRA')->first();
    }

    private function seedOpportunityForMitra(int $mitraId): int
    {
        return DB::table('opportunity_partner')->insertGetId([
            'full_name' => 'MITRA Profit Sharing Test',
            'status' => 'ACTIVE',
            'lead_id' => 'BP/TEST-PS-MITRA/' . uniqid(),
            'user_row_id' => $mitraId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** @test */
    public function keuangan_approval_page_uses_bootstrap_5_dropdown_and_safe_log_grid_variable(): void
    {
        $response = $this->actingAs($this->admin())->get('/partner-profit-sharing-approval-index');

        $response->assertOk();
        $response->assertSee('data-bs-toggle="dropdown"', false);
        $response->assertSee("ApprovalLogGrid = new dhtmlXGridObject('ProjectionGrid')", false);
        $response->assertDontSee('typeof ProjectionGrid ===', false);
    }

    /** @test */
    public function profit_sharing_approval_grid_only_contains_pending_requests(): void
    {
        $opportunityId = $this->seedOpportunity();
        $pendingId = $this->seedRequest($opportunityId);
        $approvedId = $this->seedRequest($opportunityId, ['Status' => 'APPROVED']);

        $response = $this->actingAs($this->admin())->get('/partner-approval-data?type=PROFIT_SHARING');
        $response->assertOk();

        $xml = simplexml_load_string($response->getContent());
        $this->assertNotFalse($xml);

        $ids = [];
        foreach ($xml->row as $row) {
            $ids[] = (string) $row['id'];
        }

        $this->assertContains((string) $pendingId, $ids);
        $this->assertNotContains((string) $approvedId, $ids);
    }

    /** @test */
    public function administrator_can_approve_a_pending_profit_sharing_request(): void
    {
        $opportunityId = $this->seedOpportunity();
        $requestId = $this->seedRequest($opportunityId);

        $response = $this->actingAs($this->admin())->post('/partner-profit-sharing-approve', [
            'id' => $requestId,
            'action' => 'APPROVED',
        ]);

        $response->assertOk();
        $this->assertTrue($response->json('success'));

        $request = DB::table('approval_request')->where('ID', $requestId)->first();
        $this->assertSame('NEED_APPROVAL', $request->Status);
        $this->assertSame(1, (int) $request->Current_Level);
    }

    /** @test */
    public function profit_sharing_approval_detail_handles_a_request_without_a_linked_payable(): void
    {
        $opportunityId = $this->seedOpportunity();
        $requestId = $this->seedRequest($opportunityId);

        $response = $this->actingAs($this->admin())
            ->get('/partner-profit-sharing-approval-detail?appr_id=' . $requestId);

        $response->assertOk();
        $this->assertNull($response->json('data.account_payable'));
        $this->assertNotNull($response->json('data.partner'));
    }

    /** @test */
    public function profit_sharing_approval_detail_returns_the_payable_linked_by_map_to(): void
    {
        $opportunityId = $this->seedOpportunity();
        $payableId = $this->seedAccountPayable();
        $requestId = $this->seedRequest($opportunityId, ['Map_To' => $payableId]);

        $response = $this->actingAs($this->admin())
            ->get('/partner-profit-sharing-approval-detail?appr_id=' . $requestId);

        $response->assertOk();
        $this->assertSame($payableId, (int) $response->json('data.account_payable.ID'));
    }

    /** @test */
    public function admin_royalty_list_only_returns_payables_linked_to_profit_sharing_requests(): void
    {
        $opportunityId = $this->seedOpportunity();
        $linkedPayable = $this->seedAccountPayable();
        $this->seedRequest($opportunityId, ['Map_To' => $linkedPayable]);

        $unrelatedPayable = $this->seedAccountPayable();

        $response = $this->actingAs($this->admin())->get('/partner-profit-sharing-royalty-data');
        $response->assertOk();

        $content = $response->getContent();
        $this->assertStringContainsString((string) $linkedPayable, $content);
        $this->assertStringNotContainsString((string) $unrelatedPayable, $content);
    }

    /** @test */
    public function mitra_royalty_list_uses_approval_request_to_link_payables_to_the_partner(): void
    {
        $mitra = $this->mitra();
        if (!$mitra) {
            $this->markTestSkipped('No MITRA user exists in the test database.');
        }

        $opportunityId = $this->seedOpportunityForMitra($mitra->id);
        $payableId = $this->seedAccountPayable();
        $requestId = $this->seedRequest($opportunityId, ['Map_To' => $payableId]);

        $response = $this->actingAs($mitra)->get('/partner-profit-sharing-royalty-data');
        $response->assertOk();

        $this->assertStringContainsString((string) $payableId, $response->getContent());
    }
}
