<?php

namespace Tests\Feature\BusinessPartner;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BrandingApprovalTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::where('Position', 'Administrator')->firstOrFail();
    }

    private function seedPromotion(array $overrides = []): int
    {
        return DB::table('branding_promotion')->insertGetId(array_merge([
            'Code' => 'PROM/TEST-APPROVAL/' . uniqid(),
            'Department' => 'Marketing',
            'Type' => 'ONLINE',
            'Platform' => 'GOOGLE_ADS',
            'Marketing_Id' => '1',
            'Description' => 'Branding approval test',
            'Audience' => 'Public',
            'Estimation' => 1000000,
            'Status' => 'SUBMIT',
            'Created_At' => now(),
        ], $overrides));
    }

    private function levelId(int $levelNumber): ?int
    {
        return DB::table('approval_level')
            ->where('Request_Type', 'BRANDING_PROMOTION')
            ->where('Level_Number', $levelNumber)
            ->value('ID');
    }

    private function seedRequest(int $promotionId, array $overrides = []): int
    {
        return DB::table('approval_request')->insertGetId(array_merge([
            'Status' => 'NEED_APPROVAL',
            'Requester_Id' => $this->admin()->id,
            'Description' => 'Initial Review',
            'Request_Type' => 'BRANDING_PROMOTION',
            'Map_To' => $promotionId,
            'Current_Level' => 0,
            'Level_Id' => $this->levelId(1),
            'Submitted_At' => now(),
        ], $overrides));
    }

    /** @test */
    public function branding_approval_page_uses_bootstrap_5_dropdown_and_safe_log_grid_variable(): void
    {
        $response = $this->actingAs($this->admin())->get('/partner-branding-approval-index');

        $response->assertOk();
        $response->assertSee('data-bs-toggle="dropdown"', false);
        $response->assertSee("ApprovalLogGrid = new dhtmlXGridObject('ProjectionGrid')", false);
        $response->assertDontSee('typeof ProjectionGrid ===', false);
    }

    /** @test */
    public function branding_approval_grid_only_contains_pending_requests(): void
    {
        $promotionId = $this->seedPromotion();
        $pendingId = $this->seedRequest($promotionId);
        $approvedId = $this->seedRequest($promotionId, ['Status' => 'APPROVED']);

        $response = $this->actingAs($this->admin())->get('/partner-approval-data?type=BRANDING_PROMOTION');
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
    public function administrator_can_approve_a_pending_branding_request(): void
    {
        $promotionId = $this->seedPromotion();
        $requestId = $this->seedRequest($promotionId);

        $response = $this->actingAs($this->admin())->post('/partner-branding-approval-submit', [
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
    public function administrator_can_reject_a_pending_branding_request(): void
    {
        $promotionId = $this->seedPromotion();
        $requestId = $this->seedRequest($promotionId);

        $response = $this->actingAs($this->admin())->post('/partner-branding-approval-submit', [
            'id' => $requestId,
            'action' => 'REJECTED',
        ]);

        $response->assertOk();
        $this->assertTrue($response->json('success'));
        $this->assertSame('REJECTED', DB::table('approval_request')->where('ID', $requestId)->value('Status'));
    }

    /** @test */
    public function rejecting_an_already_processed_branding_request_returns_an_error(): void
    {
        $promotionId = $this->seedPromotion();
        $requestId = $this->seedRequest($promotionId, ['Status' => 'APPROVED']);

        $response = $this->actingAs($this->admin())->post('/partner-branding-approval-submit', [
            'id' => $requestId,
            'action' => 'REJECTED',
        ]);

        $response->assertStatus(500);
        $this->assertSame('APPROVED', DB::table('approval_request')->where('ID', $requestId)->value('Status'));
    }

    /** @test */
    public function submitting_a_promotion_creates_an_approval_request(): void
    {
        $response = $this->actingAs($this->admin())->post('/partner-branding-promotion-submit', [
            'department' => 'Marketing',
            'type' => 'ONLINE',
            'platform' => 'GOOGLE_ADS',
            'marketing_id' => '1',
            'description' => 'Promotion submitted for approval',
            'audience' => 'Public',
            'estimation' => 1500000,
            'status' => 'SUBMIT',
        ]);

        $response->assertOk();

        $promotion = DB::table('branding_promotion')
            ->where('Description', 'Promotion submitted for approval')
            ->first();
        $this->assertNotNull($promotion, 'Submitted promotion must be persisted');

        $request = DB::table('approval_request')
            ->where('Request_Type', 'BRANDING_PROMOTION')
            ->where('Map_To', $promotion->ID)
            ->first();

        $this->assertNotNull($request, 'SUBMIT promotion must create an approval request');
        $this->assertSame('NEED_APPROVAL', $request->Status);
        $this->assertSame($this->admin()->id, (int) $request->Requester_Id);
    }

    /** @test */
    public function saving_a_draft_promotion_does_not_create_an_approval_request(): void
    {
        $response = $this->actingAs($this->admin())->post('/partner-branding-promotion-submit', [
            'department' => 'Marketing',
            'type' => 'ONLINE',
            'platform' => 'GOOGLE_ADS',
            'marketing_id' => '1',
            'description' => 'Promotion saved as draft',
            'audience' => 'Public',
            'estimation' => 1500000,
            'status' => 'DRAFT',
        ]);

        $response->assertOk();

        $promotion = DB::table('branding_promotion')
            ->where('Description', 'Promotion saved as draft')
            ->first();
        $this->assertNotNull($promotion, 'Draft promotion must be persisted');

        $this->assertFalse(
            DB::table('approval_request')
                ->where('Request_Type', 'BRANDING_PROMOTION')
                ->where('Map_To', $promotion->ID)
                ->exists(),
            'Draft promotion must not create an approval request'
        );
    }
}
