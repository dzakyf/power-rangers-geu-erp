<?php

namespace Tests\Unit\BusinessPartner;

use App\Services\Survey\SurveyScoringService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SurveyScoringServiceTest extends TestCase
{
    use DatabaseTransactions;

    private function version()
    {
        $version = app(SurveyScoringService::class)->activeVersionWithItems();
        if (!$version) {
            $this->markTestSkipped('No active survey form version exists.');
        }

        return $version;
    }

    private function perfectAnswers($version): array
    {
        $answers = [];
        foreach ($version->items as $item) {
            if ($item->Section_Code === 'C') {
                $answers[$item->Item_Code] = ['value' => 1];
            } elseif ($item->Section_Code === 'D') {
                $answers[$item->Item_Code] = ['value' => 1, 'notes' => 'ok'];
            } elseif ($item->Section_Code === 'E') {
                $answers[$item->Item_Code] = ['value' => 3];
            } elseif ($item->Section_Code === 'F') {
                $answers[$item->Item_Code] = ['source_value' => 10, 'uco_estimation' => 10];
            }
        }

        return $answers;
    }

    /** @test */
    public function it_scores_a_perfect_survey_from_the_active_form(): void
    {
        $version = $this->version();
        $result = app(SurveyScoringService::class)->calculate($version, $version->items, $this->perfectAnswers($version));

        $this->assertSame(100.0, $version->Max_Score);
        $this->assertSame($version->Max_Score, $result['max_score']);
        $this->assertSame(100.0, $result['total']);
        $this->assertSame(100.0, $result['percentage']);
        $this->assertSame('Layak', $result['recommendation']);
        $this->assertSame(15.0, $result['section_scores']['F']);
    }

    /** @test */
    public function it_scores_a_failing_survey_and_uses_configurable_thresholds(): void
    {
        $version = $this->version();
        $answers = [];
        foreach ($version->items as $item) {
            if ($item->Section_Code === 'E') {
                $answers[$item->Item_Code] = ['value' => 1];
            } elseif ($item->Section_Code === 'F') {
                $answers[$item->Item_Code] = ['source_value' => 0, 'uco_estimation' => 0];
            } else {
                $answers[$item->Item_Code] = ['value' => 0];
            }
        }

        $result = app(SurveyScoringService::class)->calculate($version, $version->items, $answers);

        $this->assertSame(6.0, $result['total']);
        $this->assertSame(6.0, $result['percentage']);
        $this->assertSame('Tidak Layak', $result['recommendation']);
    }

    /** @test */
    public function it_uses_the_f_bucket_for_the_sum_of_uco_estimation(): void
    {
        $version = $this->version();
        $answers = $this->perfectAnswers($version);
        $fCodes = $version->items->where('Section_Code', 'F')->pluck('Item_Code')->values();
        $answers[$fCodes[0]] = ['source_value' => 1, 'uco_estimation' => 5];
        $answers[$fCodes[1]] = ['source_value' => 1, 'uco_estimation' => 0];
        foreach ($fCodes->slice(2) as $code) {
            $answers[$code] = ['source_value' => 0, 'uco_estimation' => 0];
        }

        $result = app(SurveyScoringService::class)->calculate($version, $version->items, $answers);

        $this->assertSame(5.0, $result['section_metrics']['F']);
        $this->assertSame(5.0, $result['section_scores']['F']);
    }
}
