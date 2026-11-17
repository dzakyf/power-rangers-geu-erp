<?php

namespace Tests\Feature\BusinessPartner;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regression tests for the Business Partner approval inbox (Onboarding and
 * Survey) after the Adminor/Bootstrap 5 migration.
 *
 * Locked defects:
 *   - getApprovalData() used an ungrouped OR so admin saw every request type
 *     and every status on the Onboarding page.
 *   - processOnboardingApproval() skipped all actions for an already-APPROVED
 *     request, so Reject silently reported success and changed nothing.
 *   - processToNextLevel() did not allow Administrator override.
 *   - forceApprove() could resurrect a REJECTED request.
 */
class ApprovalOnboardingTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::where('Position', 'Administrator')->firstOrFail();
    }

    private function sales(): User
    {
        return User::where('Position', 'SALES')->firstOrFail();
    }

    private function seedOpportunity(): int
    {
        return DB::table('opportunity_partner')->insertGetId([
            'full_name' => 'Approval Inbox Test',
            'company_name' => 'Approval Inbox Co',
            'status' => 'PREPARING',
            'lead_id' => 'BP/TEST-APPROVAL-INBOX/' . uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedSurvey(int $opportunityId = null): int
    {
        return DB::table('survey_location')->insertGetId([
            'Status' => 'NEED_APPROVAL',
            'City' => 'Jakarta',
            'Recommendation_Type' => 'INTERNAL',
            'Surveyed_At' => now(),
        ]);
    }

    private function levelId(string $type, int $levelNumber): ?int
    {
        return DB::table('approval_level')
            ->where('Request_Type', $type)
            ->where('Level_Number', $levelNumber)
            ->value('ID');
    }

    private function seedRequest(string $type, int $currentLevel, array $overrides = []): int
    {
        $assignable = [
            'Status' => 'NEED_APPROVAL',
            'Requester_Id' => $this->sales()->id,
            'Description' => 'Initial Review',
            'Request_Type' => $type,
            'Current_Level' => $currentLevel,
            'Level_Id' => $this->levelId($type, $currentLevel + 1),
            'Submitted_At' => now(),
            'Created_At' => now(),
        ];

        return DB::table('approval_request')->insertGetId(array_merge($assignable, $overrides));
    }

    private function approvalGridIds(User $user, string $type): array
    {
        $response = $this->actingAs($user)->get('/partner-approval-data?type=' . $type);
        $response->assertOk();

        $xml = simplexml_load_string($response->getContent());
        $this->assertNotFalse($xml, 'Approval grid must return valid XML');

        $ids = [];
        foreach ($xml->row as $row) {
            $ids[] = (string) $row['id'];
        }

        return $ids;
    }

    /** @test */
    public function admin_approval_grid_only_contains_pending_requests_of_the_requested_type(): void
    {
        $opportunityId = $this->seedOpportunity();
        $surveyId = $this->seedSurvey();

        $pendingOnboarding = $this->seedRequest('ONBOARDING', 0, [
            'Opportunity_Partner_Id' => $opportunityId,
        ]);
        $approvedOnboarding = $this->seedRequest('ONBOARDING', 0, [
            'Opportunity_Partner_Id' => $opportunityId,
            'Status' => 'APPROVED',
        ]);
        $surveyRequest = $this->seedRequest('SURVEY', 0, [
            'Opportunity_Partner_Id' => $opportunityId,
            'Map_To' => $surveyId,
        ]);

        $ids = $this->approvalGridIds($this->admin(), 'ONBOARDING');

        $this->assertContains((string) $pendingOnboarding, $ids);
        $this->assertNotContains((string) $approvedOnboarding, $ids, 'Final rows must not be actionable');
        $this->assertNotContains((string) $surveyRequest, $ids, 'SURVEY rows must not leak into ONBOARDING');
    }

    /** @test */
    public function sales_approval_grid_only_contains_pending_requests_at_their_level(): void
    {
        $opportunityId = $this->seedOpportunity();

        $levelOne = $this->seedRequest('ONBOARDING', 0, [
            'Opportunity_Partner_Id' => $opportunityId,
        ]);
        $levelTwo = $this->seedRequest('ONBOARDING', 1, [
            'Opportunity_Partner_Id' => $opportunityId,
        ]);

        $ids = $this->approvalGridIds($this->sales(), 'ONBOARDING');

        $this->assertContains((string) $levelOne, $ids);
        $this->assertNotContains((string) $levelTwo, $ids);
    }

    /** @test */
    public function administrator_can_approve_a_request_at_a_level_they_do_not_own(): void
    {
        $opportunityId = $this->seedOpportunity();
        $requestId = $this->seedRequest('ONBOARDING', 2, [
            'Opportunity_Partner_Id' => $opportunityId,
        ]);

        $response = $this->actingAs($this->admin())->post('/partner-onboarding-approval-submit', [
            'id' => $requestId,
            'action' => 'APPROVED',
        ]);

        $response->assertOk();
        $this->assertTrue($response->json('success'));

        $request = DB::table('approval_request')->where('ID', $requestId)->first();
        $this->assertSame('NEED_APPROVAL', $request->Status);
        $this->assertSame(3, (int) $request->Current_Level, 'Admin approval must advance to level 4');
    }

    /** @test */
    public function rejecting_an_already_approved_request_returns_an_error_and_keeps_its_status(): void
    {
        $opportunityId = $this->seedOpportunity();
        $requestId = $this->seedRequest('ONBOARDING', 0, [
            'Opportunity_Partner_Id' => $opportunityId,
            'Status' => 'APPROVED',
        ]);

        $response = $this->actingAs($this->admin())->post('/partner-onboarding-approval-submit', [
            'id' => $requestId,
            'action' => 'REJECTED',
        ]);

        $response->assertStatus(500);
        $this->assertSame('APPROVED', DB::table('approval_request')->where('ID', $requestId)->value('Status'));
    }

    /** @test */
    public function approval_log_grid_uses_a_javascript_variable_that_does_not_collide_with_the_dom_id(): void
    {
        $admin = $this->admin();

        foreach (['/partner-approval-index', '/partner-survey-approval-index'] as $path) {
            $response = $this->actingAs($admin)->get($path);
            $response->assertOk();
            $response->assertSee("ApprovalLogGrid = new dhtmlXGridObject('ProjectionGrid')", false);
            $response->assertDontSee('typeof ProjectionGrid ===', false);
        }
    }

    /** @test */
    public function force_approve_is_rejected_for_non_pending_requests(): void
    {
        $opportunityId = $this->seedOpportunity();
        $requestId = $this->seedRequest('ONBOARDING', 0, [
            'Opportunity_Partner_Id' => $opportunityId,
            'Status' => 'REJECTED',
        ]);

        $response = $this->actingAs($this->admin())->post('/partner-onboarding-force-approve', [
            'id' => $requestId,
        ]);

        $response->assertStatus(500);
        $this->assertSame('REJECTED', DB::table('approval_request')->where('ID', $requestId)->value('Status'));
    }
}
