<?php

namespace Tests\Feature\BusinessPartner;

use App\Services\Survey\SurveyScoringService;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SurveyDynamicFormTest extends TestCase
{
    use DatabaseTransactions;

    private function user(): User
    {
        $user = User::query()->where('Priv_ID', 'ADM')->orderBy('id')->first()
            ?: User::query()->orderBy('id')->first();

        if (!$user) {
            $this->markTestSkipped('No user exists in the test database.');
        }

        return $user;
    }

    private function activeItems()
    {
        $version = app(SurveyScoringService::class)->activeVersionWithItems();
        if (!$version) {
            $this->markTestSkipped('No active survey form version exists.');
        }

        return $version->items;
    }

    private function answers($items): array
    {
        $answers = [];
        foreach ($items as $item) {
            if ($item->Section_Code === 'E') {
                $answers[$item->Item_Code] = ['value' => 3];
            } elseif ($item->Section_Code === 'F') {
                $answers[$item->Item_Code] = ['source_value' => 1, 'uco_estimation' => 1];
            } else {
                $answers[$item->Item_Code] = ['value' => 1, 'notes' => 'test'];
            }
        }

        return $answers;
    }

    /** @test */
    public function it_submits_a_dynamic_survey_and_persists_answers(): void
    {
        $items = $this->activeItems();
        $response = $this->actingAs($this->user())->post('/partner-survey-submit', [
            'Parent_Id' => 999999,
            'Suggestion_Type' => 'REPLY',
            'Survey_At' => now()->toDateString(),
            'Province' => 'TEST',
            'City' => 'TEST',
            'District' => 'TEST',
            'Subdistrict' => 'TEST',
            'Address' => 'TEST',
            'Surveyor_Name' => 'TEST',
            'Location_Owner_Name' => 'TEST',
            'Area' => '10',
            'Location_Type' => 'TEST',
            'answers' => $this->answers($items),
        ]);

        $response->assertOk();
        $surveyId = $response->json('data.survey_id');
        $this->assertNotNull($surveyId);

        $survey = DB::table('survey_location')->where('ID', $surveyId)->first();
        $this->assertNotNull($survey->Survey_Form_Version_ID);
        $this->assertSame('Layak', $survey->Recommendation);
        $this->assertSame(count($items), DB::table('survey_answers')->where('Survey_Location_ID', $surveyId)->count());
        $saved = DB::table('survey_answers')->where('Survey_Location_ID', $surveyId)->get();
        $this->assertSame($items->count(), $saved->count());
        $this->assertGreaterThan(0, $saved->sum('Score'));
    }

    /** @test */
    public function the_form_builder_lists_versions_and_creates_a_draft_from_the_active_form(): void
    {
        $this->actingAs($this->user())->get('/partner-survey-form-index')->assertOk();

        $active = DB::table('survey_form_versions')->where('Status', 'ACTIVE')->orderByDesc('ID')->first();
        if (!$active) {
            $this->markTestSkipped('No active survey form version exists.');
        }

        $this->actingAs($this->user())
            ->post('/partner-survey-form-create-version', ['source_version' => $active->ID])
            ->assertRedirect();

        $draft = DB::table('survey_form_versions')->where('Status', 'DRAFT')->orderByDesc('ID')->first();
        $this->assertNotNull($draft);
        $this->assertNotSame($active->ID, $draft->ID);
        $this->assertSame(
            DB::table('survey_form_items')->where('Form_Version_ID', $active->ID)->count(),
            DB::table('survey_form_items')->where('Form_Version_ID', $draft->ID)->count()
        );
    }

    /** @test */
    public function survey_runtime_and_builder_views_render(): void
    {
        $user = $this->user();
        $this->actingAs($user)->get('/partner-survey-create')->assertOk();
        $this->actingAs($user)->get('/partner-survey-index')->assertOk();
        $this->actingAs($user)->get('/partner-survey-create-reply')->assertOk();

        $active = DB::table('survey_form_versions')->where('Status', 'ACTIVE')->orderByDesc('ID')->first();
        if ($active) {
            $this->actingAs($user)->get('/partner-survey-form-builder?version=' . $active->ID)->assertOk();
        }
    }

    /** @test */
    public function saving_the_active_form_creates_a_draft_without_mutating_the_active_version(): void
    {
        $active = DB::table('survey_form_versions')->where('Status', 'ACTIVE')->orderByDesc('ID')->first();
        if (!$active) {
            $this->markTestSkipped('No active survey form version exists.');
        }

        $activeItems = DB::table('survey_form_items')->where('Form_Version_ID', $active->ID)->get();
        $items = [];
        foreach ($activeItems as $item) {
            $config = json_decode($item->Score_Config, true) ?: [];
            $items['item_' . $item->ID] = [
                'id' => $item->ID,
                'section_code' => $item->Section_Code,
                'item_code' => $item->Item_Code,
                'input_type' => $item->Input_Type,
                'criteria' => $item->Criteria,
                'keterangan' => $item->Keterangan,
                'minimum_standard' => $item->Minimum_Standard,
                'parameter_evaluation' => $item->Parameter_Evaluation,
                'yes_score' => $config['yes'] ?? 0,
                'no_score' => $config['no'] ?? 0,
                'options' => $config['options'] ?? [],
                'file_required' => $item->File_Required,
                'notes_enabled' => $item->Notes_Enabled,
            ];
        }

        $response = $this->actingAs($this->user())->post('/partner-survey-form-save', [
            'version_id' => $active->ID,
            'name' => $active->Name,
            'pass_threshold' => 80,
            'conditional_threshold' => 70,
            'f_max' => 15,
            'f_buckets' => [['gte' => 0, 'score' => 15]],
            'items' => $items,
        ]);

        $response->assertRedirect();

        $draft = DB::table('survey_form_versions')->where('Status', 'DRAFT')->orderByDesc('ID')->first();
        $this->assertNotNull($draft);
        $this->assertNotSame($active->ID, $draft->ID);
        $this->assertSame($activeItems->count(), DB::table('survey_form_items')->where('Form_Version_ID', $draft->ID)->count());
        $this->assertSame($activeItems->count(), DB::table('survey_form_items')->where('Form_Version_ID', $active->ID)->count());
    }
}
