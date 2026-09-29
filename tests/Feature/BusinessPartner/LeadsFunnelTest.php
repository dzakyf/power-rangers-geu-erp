<?php

namespace Tests\Feature\BusinessPartner;

use App\Services\LeadsService;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Locks the Model A pipeline-leads definition (LeadsService):
 *   Status = ordered stage; Response is an attribute;
 *   Cumulative funnel: Qualified <= Meeting 1 <= Meeting 2 <= Warm <= Negotiation <= Deal;
 *   Cancel = Status 'Cancel' (only from FU 3 / Meeting 1 / Meeting 2), reasons No Response/Unqualified/Other.
 */
class LeadsFunnelTest extends TestCase
{
    use DatabaseTransactions;

    private function testUser(): User
    {
        $user = User::query()->orderBy('id')->first();
        if (!$user) {
            $this->markTestSkipped('No user exists in the test database.');
        }

        return $user;
    }

    private function seedLead(string $location, array $overrides = []): int
    {
        return DB::table('opportunity')->insertGetId(array_merge([
            'Task_ID'              => 'LEADTEST-' . uniqid(),
            'Location'             => $location,
            'Date'                 => now()->toDateString(),
            'Full_Name'            => 'Funnel Test',
            'Phone_Number'         => '08' . random_int(100000000, 999999999),
            'Partnership_Interest' => 1,
            'Capital_Estimation'   => 1,
            'Created_Date'         => now(),
            'Created_By'           => '__test_funnel__',
            'Status'               => 'Fresh',
            'Updated_By'           => '__test_funnel__',
            'Response'             => 'No Response',
        ], $overrides));
    }

    private function seedScenario(string $loc): void
    {
        $this->seedLead($loc, ['Status' => 'Fresh',       'Response' => 'No Response']);
        $this->seedLead($loc, ['Status' => 'FU 1',        'Response' => 'Response']);
        $this->seedLead($loc, ['Status' => 'Qualified',   'Response' => 'Response']);
        $this->seedLead($loc, ['Status' => 'Meeting 1',   'Response' => 'Response']);
        $this->seedLead($loc, ['Status' => 'Meeting 2',   'Response' => 'Response']);
        $this->seedLead($loc, ['Status' => 'Warm',        'Response' => 'Response']);
        $this->seedLead($loc, ['Status' => 'Negotiation', 'Response' => 'Response']);
        $this->seedLead($loc, ['Status' => 'Deal',        'Response' => 'Response']);
        $this->seedLead($loc, ['Status' => 'Cancel', 'Cancel_Stage' => 'FU 3',      'Reason' => 'No Response', 'Response' => 'No Response']);
        $this->seedLead($loc, ['Status' => 'Cancel', 'Cancel_Stage' => 'Meeting 1', 'Reason' => 'Unqualified', 'Response' => 'Response']);
    }

    /** @test */
    public function funnel_metrics_follow_expected_cumulative_counts(): void
    {
        $loc = 'TEST-STAGE-' . uniqid();
        $this->seedScenario($loc);

        $m = app(LeadsService::class)->funnel(['location' => $loc]);

        $this->assertSame(10, $m['total_leads']);
        $this->assertSame(8, $m['response']);
        $this->assertSame(2, $m['no_response']);
        $this->assertSame(6, $m['qualified']);
        $this->assertSame(1, $m['unqualified']);
        $this->assertSame(2, $m['unidentified']);
        $this->assertSame(5, $m['meeting_1']);
        $this->assertSame(4, $m['meeting_2']);
        $this->assertSame(3, $m['warm']);
        $this->assertSame(2, $m['negotiation']);
        $this->assertSame(1, $m['deal']);
        $this->assertSame(1, $m['cancel_no_response']);
        $this->assertSame(1, $m['cancel_unqualified']);
        $this->assertSame(2, $m['cancel_total']);
    }

    /** @test */
    public function derived_fields_match_status(): void
    {
        $svc = app(LeadsService::class);

        $this->assertSame('Fresh', $svc->deriveClassification('Fresh'));
        $this->assertSame('FU 3', $svc->deriveClassification('Qualified'));
        $this->assertSame('Meeting 2', $svc->deriveClassification('Deal'));
        $this->assertSame('Meeting 1', $svc->deriveClassification('Cancel', 'Meeting 1'));

        $this->assertSame('Qualified', $svc->deriveQualification('Warm'));
        $this->assertSame('Unqualified', $svc->deriveQualification('Cancel', 'Unqualified'));
        $this->assertNull($svc->deriveQualification('Fresh'));
        $this->assertNull($svc->deriveQualification('Cancel', 'No Response'));
    }

    /** @test */
    public function validate_stage_accepts_valid_and_rejects_invalid(): void
    {
        $svc = app(LeadsService::class);

        $this->assertNull($svc->validateStage('Fresh', 'No Response', null, null));
        $this->assertNull($svc->validateStage('FU 1', 'Response', null, null));
        $this->assertNull($svc->validateStage('Deal', 'Response', null, null));
        $this->assertNull($svc->validateStage('Cancel', 'No Response', 'FU 3', 'No Response'));
        $this->assertNull($svc->validateStage('Cancel', 'Response', 'Meeting 1', 'Unqualified'));

        $this->assertNotNull($svc->validateStage('Cold', 'No Response', null, null), 'Cold no longer valid');
        $this->assertNotNull($svc->validateStage('Cancel', 'Response', 'FU 1', 'Other'), 'cancel not from FU 1');
        $this->assertNotNull($svc->validateStage('Cancel', 'Response', 'FU 3', null), 'cancel needs reason');
        $this->assertNotNull($svc->validateStage('FU 1', 'No Response', null, null), 'engaged needs Response');
        $this->assertNotNull($svc->validateStage('Cancel', 'Response', 'FU 3', 'No Response'), 'No Response reason needs Response=No Response');
    }

    /** @test */
    public function update_endpoint_enforces_stage_rules(): void
    {
        $loc = 'TEST-UPD-' . uniqid();
        $id = $this->seedLead($loc, ['Status' => 'FU 1', 'Response' => 'Response']);

        // invalid: cancel from FU 1
        $this->actingAs($this->testUser())
            ->post('/partner-leads-update?id=' . $id, [
                'Full_Name' => 'Funnel Test', 'Phone_Number' => '08123456789', 'Hub_Type' => 'mobil',
                'Response' => 'Response', 'Status' => 'Cancel', 'Cancel_Stage' => 'FU 1', 'Reason' => 'Other',
            ])
            ->assertStatus(422);

        // valid: cancel from FU 3
        $this->actingAs($this->testUser())
            ->post('/partner-leads-update?id=' . $id, [
                'Full_Name' => 'Funnel Test', 'Phone_Number' => '08123456789', 'Hub_Type' => 'mobil',
                'Response' => 'Response', 'Status' => 'Cancel', 'Cancel_Stage' => 'FU 3', 'Reason' => 'Other',
            ])
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $row = DB::table('opportunity')->where('ID', $id)->first();
        $this->assertSame('Cancel', $row->Status);
        $this->assertSame('FU 3', $row->Cancel_Stage);
        $this->assertSame('FU 3', $row->Classification);
    }
}
