<?php

namespace Tests\Feature\BusinessPartner;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Generic BDP force-approve: authorized positions bypass the approval chain,
 * write a force-approve log, and run the same final side effects as normal
 * approval.
 */
class ApprovalForceApproveTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::where('Position', 'Administrator')->firstOrFail();
    }

    private function userWithPosition(string $position): User
    {
        $id = DB::table('users')->insertGetId([
            'username'          => strtolower($position) . '_' . uniqid(),
            'Name'              => $position . ' Test',
            'password'          => bcrypt('secret'),
            'Location'          => 'GEU-HO',
            'Position'          => $position,
            'Priv_ID'           => 'SALES',
            'Status'            => 'ACTIVE',
            'email_confirmed'   => 1,
            'PR_Approval'       => '',
            'BP_Approval'       => 0,
            'Blasting'          => 0,
            'Admin_Chat'        => 0,
            'Training_Approval' => 0,
            'created_at'        => now(),
            'update_at'         => now(),
        ]);

        return User::findOrFail($id);
    }

    private function seedApproval(string $type, array $extra = []): int
    {
        return DB::table('approval_request')->insertGetId(array_merge([
            'Status'        => 'NEED_APPROVAL',
            'Requester_Id'  => $this->admin()->id,
            'Description'   => 'Force approve test',
            'Request_Type'  => $type,
            'Current_Level' => 1,
            'Submitted_At'  => now(),
        ], $extra));
    }

    private function seedOpportunity(array $extra = []): int
    {
        return DB::table('opportunity_partner')->insertGetId(array_merge([
            'full_name'   => 'Force Approve Partner',
            'email'       => 'force-' . uniqid() . '@example.test',
            'lead_id'     => 'BP/FA/' . uniqid(),
            'register_as' => 'individual',
            'contact'     => '08123456789',
            'status'      => 'PREPARING',
            'created_at'  => now(),
            'updated_at'  => now(),
        ], $extra));
    }

    /** @test */
    public function administrator_can_force_approve_branding_promotion(): void
    {
        $requestId = $this->seedApproval('BRANDING_PROMOTION', ['Map_To' => 12345]);

        $this->actingAs($this->admin())
            ->postJson('/partner-approval-force-approve', ['id' => $requestId])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame('APPROVED', DB::table('approval_request')->where('ID', $requestId)->value('Status'));

        $log = DB::table('approval_log')->where('Request_Id', $requestId)->orderByDesc('ID')->first();
        $this->assertSame('APPROVED', $log->Action);
        $this->assertSame('Force approved (bypass)', $log->Notes);
    }

    /** @test */
    public function bod_and_superadmin_can_force_approve(): void
    {
        foreach (['BOD', 'SUPERADMIN'] as $position) {
            $requestId = $this->seedApproval('PROFIT_SHARING', ['Map_To' => 999]);

            $this->actingAs($this->userWithPosition($position))
                ->postJson('/partner-approval-force-approve', ['id' => $requestId])
                ->assertOk()
                ->assertJson(['success' => true]);

            $this->assertSame('APPROVED', DB::table('approval_request')->where('ID', $requestId)->value('Status'));
        }
    }

    /** @test */
    public function non_privileged_position_is_forbidden(): void
    {
        $requestId = $this->seedApproval('BRANDING_PROMOTION', ['Map_To' => 1]);
        $sales = User::where('Position', 'SALES')->firstOrFail();

        $this->actingAs($sales)
            ->postJson('/partner-approval-force-approve', ['id' => $requestId])
            ->assertStatus(403);

        $this->assertSame('NEED_APPROVAL', DB::table('approval_request')->where('ID', $requestId)->value('Status'));
    }

    /** @test */
    public function non_pending_request_is_rejected(): void
    {
        $requestId = $this->seedApproval('BRANDING_PROMOTION', [
            'Map_To' => 1,
            'Status' => 'REJECTED',
        ]);

        $this->actingAs($this->admin())
            ->postJson('/partner-approval-force-approve', ['id' => $requestId])
            ->assertStatus(422)
            ->assertJson(['success' => false]);

        $this->assertSame('REJECTED', DB::table('approval_request')->where('ID', $requestId)->value('Status'));
    }

    /** @test */
    public function onboarding_force_approve_creates_the_mitra_account(): void
    {
        $opportunityId = $this->seedOpportunity();
        $leadId = DB::table('opportunity_partner')->where('id', $opportunityId)->value('lead_id');
        $requestId = $this->seedApproval('ONBOARDING', [
            'Opportunity_Partner_Id' => $opportunityId,
        ]);

        $this->actingAs($this->admin())
            ->postJson('/partner-approval-force-approve', ['id' => $requestId])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('users', ['Emp_ID' => $leadId]);
        $this->assertSame('ACTIVE', DB::table('opportunity_partner')->where('id', $opportunityId)->value('status'));
    }

    /** @test */
    public function survey_force_approve_completes_the_survey_and_advances_onboarding(): void
    {
        $opportunityId = $this->seedOpportunity([
            'onboarding_stage' => 'SURVEY',
        ]);
        $surveyId = DB::table('survey_location')->insertGetId([
            'Status'                 => 'NEED_APPROVAL',
            'Opportunity_Partner_Id' => $opportunityId,
            'City'                   => 'Jakarta',
            'Surveyed_At'            => now(),
        ]);
        $requestId = $this->seedApproval('SURVEY', [
            'Map_To'                 => $surveyId,
            'Opportunity_Partner_Id' => $opportunityId,
        ]);

        $this->actingAs($this->admin())
            ->postJson('/partner-approval-force-approve', ['id' => $requestId])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame('DONE_SURVEY', DB::table('survey_location')->where('ID', $surveyId)->value('Status'));
        $this->assertSame('SETUP', DB::table('opportunity_partner')->where('id', $opportunityId)->value('onboarding_stage'));
    }

    /** @test */
    public function renewal_force_approve_updates_the_renewal_record(): void
    {
        $opportunityId = $this->seedOpportunity();
        $renewalId = DB::table('partner_renewal')->insertGetId([
            'Opportunity_Id' => $opportunityId,
            'Status'         => 'PENDING',
            'Renewed_Length' => 12,
            'Created_At'     => now(),
        ]);
        $requestId = $this->seedApproval('RENEWAL', [
            'Opportunity_Partner_Id' => $opportunityId,
            'Map_To'                 => $renewalId,
        ]);

        $this->actingAs($this->admin())
            ->postJson('/partner-approval-force-approve', ['id' => $requestId])
            ->assertOk();

        $renewal = DB::table('partner_renewal')->where('ID', $renewalId)->first();
        $this->assertSame('APPROVED', $renewal->Status);
        $this->assertNotNull($renewal->Approved_At);
    }

    /** @test */
    public function termination_force_approve_updates_the_termination_and_partner(): void
    {
        $opportunityId = $this->seedOpportunity();
        $terminationId = DB::table('partner_termination')->insertGetId([
            'Opportunity_Id' => $opportunityId,
            'Status'         => 'PENDING',
            'Created_At'     => now(),
        ]);
        $requestId = $this->seedApproval('TERMINATION', [
            'Opportunity_Partner_Id' => $opportunityId,
            'Map_To'                 => $terminationId,
        ]);

        $this->actingAs($this->admin())
            ->postJson('/partner-approval-force-approve', ['id' => $requestId])
            ->assertOk();

        $termination = DB::table('partner_termination')->where('ID', $terminationId)->first();
        $this->assertSame('APPROVED', $termination->Status);
        $this->assertNotNull($termination->Approved_At);
        $this->assertSame('TERMINATED', DB::table('opportunity_partner')->where('id', $opportunityId)->value('status'));
    }

    /** @test */
    public function transfer_membership_force_approve_updates_the_transfer_record(): void
    {
        $opportunityId = $this->seedOpportunity();
        $transferId = DB::table('partner_transfer_membership')->insertGetId([
            'Opportunity_Id' => (string) $opportunityId,
            'New_Mitra_Name' => 'New Owner',
            'Type'           => 'TRANSFER',
            'Reason'         => 'Test',
            'Applied_At'     => now(),
        ]);
        $requestId = $this->seedApproval('TRANSFER_MEMBERSHIP', [
            'Opportunity_Partner_Id' => $opportunityId,
            'Map_To'                 => $transferId,
        ]);

        $this->actingAs($this->admin())
            ->postJson('/partner-approval-force-approve', ['id' => $requestId])
            ->assertOk();

        $transfer = DB::table('partner_transfer_membership')->where('ID', $transferId)->first();
        $this->assertSame('APPROVED', $transfer->Result);
        $this->assertNotNull($transfer->Effective_Transfer_At);
    }

    /** @test */
    public function force_approve_is_available_on_every_bdp_approval_page(): void
    {
        $paths = [
            '/partner-approval-index',
            '/partner-profit-sharing-approval-index',
            '/partner-branding-approval-index',
            '/partner-renewal-approval-index',
            '/partner-termination-approval-index',
            '/partner-transfer-membership-approval-index',
            '/partner-survey-approval-index',
        ];

        foreach ($paths as $path) {
            $this->actingAs($this->admin())
                ->get($path)
                ->assertOk()
                ->assertSee('onclick="forceApproveRequest(', false);
        }
    }

    /** @test */
    public function force_approve_is_hidden_for_mitra_users(): void
    {
        $mitra = User::where('Position', 'MITRA')->firstOrFail();

        $paths = [
            '/partner-approval-index',
            '/partner-profit-sharing-approval-index',
            '/partner-branding-approval-index',
            '/partner-renewal-approval-index',
            '/partner-termination-approval-index',
            '/partner-transfer-membership-approval-index',
            '/partner-survey-approval-index',
        ];

        foreach ($paths as $path) {
            $this->actingAs($mitra)
                ->get($path)
                ->assertOk()
                ->assertDontSee('onclick="forceApproveRequest(', false);
        }
    }

    /** @test */
    public function legacy_onboarding_force_approve_endpoint_still_works(): void
    {
        $requestId = $this->seedApproval('BRANDING_PROMOTION', ['Map_To' => 1]);

        $this->actingAs($this->admin())
            ->postJson('/partner-onboarding-force-approve', ['id' => $requestId])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame('APPROVED', DB::table('approval_request')->where('ID', $requestId)->value('Status'));
    }

    /** @test */
    public function profit_sharing_force_approve_finalizes_the_request(): void
    {
        $requestId = $this->seedApproval('PROFIT_SHARING', ['Map_To' => 836]);

        $this->actingAs($this->admin())
            ->postJson('/partner-approval-force-approve', ['id' => $requestId])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame('APPROVED', DB::table('approval_request')->where('ID', $requestId)->value('Status'));
    }
}
