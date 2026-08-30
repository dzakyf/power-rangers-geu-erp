<?php

namespace Tests\Feature\Survey;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Integration tests: survey submit/update responses must return the computed
 * section scores so the "Analisis Surveyor -> Penilaian" table renders real
 * numbers after submit (never "undefined").
 *
 * Regression for: submitMasterDataSurvey() used to return only survey_id,
 * while the front-end renders from data.C_Total_Score / D_ / E_ / F_ /
 * Total_Score / Analysis_Result.
 *
 * Forms covered: survey-new (submit), survey-reply (submit), and the
 * "Lengkapi Data" flow (update, which already returned the totals).
 */
class SurveySubmitTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::where('username', 'admin')->firstOrFail();
    }

    private function seedPartner(): int
    {
        return DB::table('opportunity_partner')->insertGetId([
            'full_name'  => 'Survey Partner '.uniqid(),
            'company_name' => 'Survey Co',
            'status'     => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Scores: C (photo) x 2.5 each, D checklist, E environment, F uco.
     */
    private function fullAnswers(int $partnerId, array $overrides = []): array
    {
        return array_merge([
            'Opportunity_Partner_Id' => $partnerId,
            'Province'               => 'DKI Jakarta',
            'City'                   => 'Jakarta Selatan',
            'District'               => 'Kebayoran Baru',
            'Subdistrict'            => 'Senayan',
            'Address'                => 'Jl. Survey No. 1',
            'Location_Name'          => 'Hub Test',
            'Latitude'               => '-6.2',
            'Longitude'              => '106.8',
            'Surveyor_Name'          => 'Test Surveyor',
            'Location_Owner_Name'    => 'Owner Test',
            'Area'                   => '300',
            'Location_Type'          => 'ruko',
            'Survey_At'              => now()->toDateString(),

            // C: 4 photos, each "ada" (1) -> 4 x 2.5 = 10
            'C_c1' => 1, 'C_c2' => 1, 'C_c3' => 1, 'C_c4' => 1,
            // D: 9 rows; j1-8 each 6, j9 = 7 -> 8*6 + 7 = 55
            'D_c1' => 1, 'D_c2' => 1, 'D_c3' => 1, 'D_c4' => 1,
            'D_c5' => 1, 'D_c6' => 1, 'D_c7' => 1, 'D_c8' => 1,
            'D_c9' => 1,
            'D_c1_Notes' => 'ok', 'D_c2_Notes' => 'ok', 'D_c3_Notes' => 'ok',
            'D_c4_Notes' => 'ok', 'D_c5_Notes' => 'ok', 'D_c6_Notes' => 'ok',
            'D_c7_Notes' => 'ok', 'D_c8_Notes' => 'ok', 'D_c9_Notes' => 'ok',
            // E: values 1/2/3 -> 1/3/5; use 3,2,3,1 -> 5+3+5+1 = 14
            'E_p1' => 3, 'E_p2' => 2, 'E_p3' => 3, 'E_p4' => 1,
            // F: p5 col2 (ton) = 12 -> uco = 12
            'F_p1_col1' => 10, 'F_p1_col2' => 2,
            'F_p2_col1' => 20, 'F_p2_col2' => 3,
            'F_p3_col1' => 30, 'F_p3_col2' => 4,
            'F_p4_col1' => 40, 'F_p4_col2' => 3,
            'F_p5_col2' => 12,
        ], $overrides);
    }

    private function expectedTotals(array $a): array
    {
        $photo = 0;
        for ($i = 1; $i <= 4; $i++) {
            $photo += ($a["C_c$i"] ?? 0) * 2.5;
        }
        $checklist = 0;
        for ($j = 1; $j <= 9; $j++) {
            $count = ($a["D_c$j"] ?? 0);
            if ($count == 1 && $j !== 9) {
                $checklist += 6;
            } elseif ($count == 1) {
                $checklist += 7;
            }
        }
        $env = 0;
        for ($k = 1; $k <= 4; $k++) {
            $v = (int) ($a["E_p$k"] ?? 0);
            if ($v == 1) $env += 1;
            elseif ($v == 2) $env += 3;
            elseif ($v == 3) $env += 5;
        }
        $ton = (float) ($a['F_p5_col2'] ?? 0);
        if ($ton > 15) $uco = 15;
        elseif ($ton >= 10) $uco = 12;
        elseif ($ton >= 6) $uco = 8;
        elseif ($ton >= 4) $uco = 5;
        else $uco = 2;
        $total = $photo + $checklist + $env + $uco;
        return [$photo, $checklist, $env, $uco, $total];
    }

    // ------------------------------------------------------------------
    // Survey new (submit)
    // ------------------------------------------------------------------

    /** @test */
    public function submit_returns_totals_and_analysis()
    {
        $partnerId = $this->seedPartner();
        $answers = $this->fullAnswers($partnerId);
        [$photo, $checklist, $env, $uco, $total] = $this->expectedTotals($answers);

        $res = $this->actingAs($this->admin())
            ->postJson('/partner-survey-submit', $answers)
            ->assertOk()
            ->assertJson(['message' => 'success'])
            ->json();

        $data = $res['data'];
        $this->assertArrayHasKey('survey_id', $data);
        $this->assertEquals($photo, $data['C_Total_Score']);
        $this->assertEquals($checklist, $data['D_Total_Score']);
        $this->assertEquals($env, $data['E_Total_Score']);
        $this->assertEquals($uco, $data['F_Total_Score']);
        $this->assertEquals($total, $data['Total_Score']);
        $this->assertEquals('Layak', $data['Analysis_Result']); // total >= 80

        $row = DB::table('survey_location')->where('ID', $data['survey_id'])->first();
        $this->assertNotNull($row);
        $this->assertEquals($total, $row->Total_Score);
    }

    /** @test */
    public function submit_without_sections_defaults_to_zero()
    {
        $partnerId = $this->seedPartner();

        $res = $this->actingAs($this->admin())
            ->postJson('/partner-survey-submit', [
                'Opportunity_Partner_Id' => $partnerId,
                'Survey_At'              => now()->toDateString(),
            ])
            ->assertOk()
            ->assertJson(['message' => 'success'])
            ->json();

        $this->assertEquals(0, $res['data']['C_Total_Score']);
        $this->assertEquals(0, $res['data']['D_Total_Score']);
        $this->assertEquals(0, $res['data']['E_Total_Score']);
        $this->assertEquals(2, $res['data']['F_Total_Score']); // default branch: ton<=4 -> 2
        $this->assertEquals(2, $res['data']['Total_Score']);
        $this->assertEquals('Tidak Layak', $res['data']['Analysis_Result']);
    }

    // ------------------------------------------------------------------
    // Survey reply (same submit endpoint)
    // ------------------------------------------------------------------

    /** @test */
    public function survey_reply_submit_returns_totals()
    {
        $partnerId = $this->seedPartner();
        $answers = $this->fullAnswers($partnerId, [
            'Parent_Id'        => 1,
            'Suggestion_Type'  => 'REPLY',
        ]);
        [$photo, $checklist, $env, $uco, $total] = $this->expectedTotals($answers);

        $res = $this->actingAs($this->admin())
            ->postJson('/partner-survey-submit', $answers)
            ->assertOk()->json();

        $this->assertEquals($total, $res['data']['Total_Score']);
        $this->assertArrayHasKey('Analysis_Result', $res['data']);
    }

    // ------------------------------------------------------------------
    // Lengkapi Data (update endpoint)
    // ------------------------------------------------------------------

    /** @test */
    public function lengkapi_data_update_returns_totals()
    {
        $partnerId = $this->seedPartner();
        $surveyId = DB::table('survey_location')->insertGetId([
            'Opportunity_Partner_Id' => $partnerId,
            'Code'                   => 'SVY-TEST-'.uniqid(),
            'Status'                 => 'NEED_APPROVAL',
        ]);

        $answers = $this->fullAnswers($partnerId);
        $answers['ID'] = $surveyId;
        [$photo, $checklist, $env, $uco, $total] = $this->expectedTotals($answers);

        $res = $this->actingAs($this->admin())
            ->postJson('/partner-survey-update', $answers)
            ->assertOk()->json();

        $this->assertEquals($photo, $res['data']['C_Total_Score']);
        $this->assertEquals($checklist, $res['data']['D_Total_Score']);
        $this->assertEquals($env, $res['data']['E_Total_Score']);
        $this->assertEquals($uco, $res['data']['F_Total_Score']);
        $this->assertEquals($total, $res['data']['Total_Score']);
        $this->assertArrayHasKey('Analysis_Result', $res['data']);

        $row = DB::table('survey_location')->where('ID', $surveyId)->first();
        $this->assertEquals($total, $row->Total_Score);
    }
}
