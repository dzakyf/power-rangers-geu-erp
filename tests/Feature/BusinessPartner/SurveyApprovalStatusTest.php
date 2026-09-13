<?php

namespace Tests\Feature\BusinessPartner;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regression test for PARTNER approval-survey.
 *
 * When the 4th (final) approver approves a survey, the approval request flips
 * to APPROVED and the linked survey_location row must be marked approved. The
 * survey module's "completed" enum value is DONE_SURVEY (there is no APPROVED
 * value in the column), so the code must write DONE_SURVEY - writing
 * 'APPROVED' was silently truncated to '' by the non-strict MySQL mode.
 *
 * Route: POST partner-survey-approval-submit -> ApprovalController@processSurveyToNextProgress
 */
class SurveyApprovalStatusTest extends TestCase
{
    use DatabaseTransactions;

    /** @var User|null */
    private $director;

    private function director(): User
    {
        if ($this->director) {
            return $this->director;
        }

        $id = DB::table('users')->where('Position', 'DIRECTOR')->orderBy('id')->value('id');

        if (!$id) {
            $this->markTestSkipped('No DIRECTOR user exists in the test database.');
        }

        return $this->director = User::query()->findOrFail($id);
    }

    private function seedOpportunity(): int
    {
        return DB::table('opportunity_partner')->insertGetId([
            'full_name' => 'Survey Approval Test',
            'company_name' => 'Survey Approval Co',
            'status' => 'PREPARING',
            'lead_id' => 'BP/TEST-SURVEY-APPROVAL/0001',
            'onboarding_stage' => 'SURVEY',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedSurvey(int $opportunityId): int
    {
        return DB::table('survey_location')->insertGetId([
            'Opportunity_Partner_Id' => $opportunityId,
            'Code' => 'TEST/SVY/0001',
            'Status' => 'NEED_APPROVAL',
            'Surveyed_At' => now(),
        ]);
    }

    private function seedFinalLevelRequest(int $surveyId): int
    {
        // SURVEY level 4 = DIRECTOR (approval_level id 28).
        $levelId = DB::table('approval_level')
            ->where('Request_Type', 'SURVEY')
            ->where('Level_Number', 4)
            ->value('ID');

        return DB::table('approval_request')->insertGetId([
            'Status' => 'NEED_APPROVAL',
            'Requester_Id' => $this->director()->id,
            'Description' => 'Persetujuan hasil survey lokasi',
            'Request_Type' => 'SURVEY',
            'Map_To' => $surveyId,
            'Current_Level' => 3,
            'Level_Id' => $levelId,
            'Submitted_At' => now(),
            'Created_At' => now(),
        ]);
    }

    /** @test */
    public function final_survey_approval_marks_survey_done_survey(): void
    {
        $opportunityId = $this->seedOpportunity();
        $surveyId = $this->seedSurvey($opportunityId);
        $requestId = $this->seedFinalLevelRequest($surveyId);

        $response = $this->actingAs($this->director())
            ->post('/partner-survey-approval-submit', [
                'id' => $requestId,
                'action' => 'APPROVED',
            ]);

        $response->assertOk();
        $this->assertTrue($response->json('success'));

        $this->assertSame('APPROVED', DB::table('approval_request')->where('ID', $requestId)->value('Status'));
        $this->assertSame(
            'DONE_SURVEY',
            DB::table('survey_location')->where('ID', $surveyId)->value('Status'),
            'An approved survey must use the module\'s completed enum value (DONE_SURVEY)'
        );
    }

    /** @test */
    public function survey_approval_status_is_a_valid_enum_value(): void
    {
        $column = DB::select("SHOW COLUMNS FROM survey_location WHERE Field = 'Status'");
        $type = $column[0]->Type;

        // Guards the assumption behind reusing DONE_SURVEY (no schema change).
        $this->assertStringContainsString('DONE_SURVEY', $type);
    }
}
