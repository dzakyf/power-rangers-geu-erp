<?php

namespace Tests\Feature\BusinessPartner;

use App\Services\WarehouseService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regression tests for Finance > Dashboard Profit Sharing projections.
 *
 * Projections are uploaded as relative months (Month_1..Month_12 per Year
 * 1..4). The timeline is anchored to the month AFTER the latest upload:
 * Month 1 = (projection Created_At month) + 1. Calendar months before that
 * origin show 0. This is independent of the partner's onboarding stage.
 *
 * WarehouseService::calculateFinancialStatement() is called exactly like
 * ProfitSharingController@dashboardProfitSharingXml does: one call per
 * calendar month with a single-month range.
 */
class ProfitSharingProjectionAnchorTest extends TestCase
{
    use DatabaseTransactions;

    private const WAREHOUSE_TASK_ID = 'TEST-PS-1';
    private const LEAD_ID = 'BP/TEST-PROFITSHARING/0001';
    private const CREATED_BY = '__test_profitsharing__';

    /**
     * The month the projection Excel was uploaded.
     */
    private function uploadMonth(): Carbon
    {
        return now()->startOfMonth()->subMonths(3);
    }

    /**
     * The origin of the projection timeline: the month after the upload.
     */
    private function originMonth(): Carbon
    {
        return $this->uploadMonth()->copy()->addMonth();
    }

    /**
     * A distinct operational month, so the test proves the anchor is the
     * upload month (not the onboarding/ACTIVE month).
     */
    private function operationalMonth(): Carbon
    {
        return now()->startOfMonth()->subMonth();
    }

    private function seedScenario(bool $partnerActive = true): void
    {
        DB::table('opportunity_partner')->insert([
            'full_name' => 'Profit Sharing Test Partner',
            'company_name' => 'Profit Sharing Test Co',
            'status' => 'ACTIVE',
            'lead_id' => self::LEAD_ID,
            'onboarding_stage' => $partnerActive ? 'ACTIVE' : 'SETUP',
            'onboarding_stage_updated_at' => $partnerActive
                ? $this->operationalMonth()->copy()->addDays(10)->format('Y-m-d H:i:s')
                : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('warehouse_partner')->insert([
            'warehouse_task_id' => self::WAREHOUSE_TASK_ID,
            'warehouse_type' => 'booth',
            'Location' => 'Test',
            'warehouse_location' => 'Lokasi Profit Sharing Test',
            'warehouse_status' => 'ACTIVE',
            'partner_task_id' => self::LEAD_ID,
            'capacity' => 0,
            'Created_Date' => now(),
            'Created_By' => self::CREATED_BY,
        ]);

        // Year 1 projection (low): Month_N_Total = N * 100.
        $this->seedProjectionYear(1, [
            1 => 100, 2 => 200, 3 => 300, 4 => 400, 5 => 500, 6 => 600,
            7 => 700, 8 => 800, 9 => 900, 10 => 1000, 11 => 1100, 12 => 1200,
        ]);

        // Year 2 projection (low): only Month 1 has a value.
        $this->seedProjectionYear(2, [1 => 1300]);
    }

    private function seedProjectionYear(int $year, array $monthTotals): void
    {
        DB::table('projections')->insert(array_merge([
            'User_Id' => self::CREATED_BY,
            'warehouse_task_id' => self::WAREHOUSE_TASK_ID,
            'Hub_Type' => 'BOOTH',
            'Description' => 'Bagi Hasil Mitra',
            'Year' => $year,
            'Projection_Type' => 'low',
            'Created_At' => $this->uploadMonth()->format('Y-m-d H:i:s'),
        ], collect(range(1, 12))
            ->mapWithKeys(function (int $month) use ($monthTotals) {
                return ["Month_{$month}_Total" => $monthTotals[$month] ?? 0];
            })
            ->all()));
    }

    private function statementFor(Carbon $month): array
    {
        return app(WarehouseService::class)->calculateFinancialStatement(
            self::WAREHOUSE_TASK_ID,
            $month->copy()->startOfMonth()->toDateString(),
            $month->copy()->endOfMonth()->toDateString()
        );
    }

    /** @test */
    public function test_first_projection_month_is_the_month_after_upload(): void
    {
        $this->seedScenario();

        $stmt = $this->statementFor($this->originMonth());

        $this->assertEquals(
            100,
            $stmt['projection_royalty_low'],
            'Projection Month 1 belongs to the month after the upload'
        );
    }

    /** @test */
    public function test_second_projection_month_follows_the_origin(): void
    {
        $this->seedScenario();

        $stmt = $this->statementFor($this->originMonth()->copy()->addMonth());

        $this->assertEquals(
            200,
            $stmt['projection_royalty_low'],
            'Projection Month 2 is the month after Month 1'
        );
    }

    /** @test */
    public function test_projection_is_zero_before_the_month_after_upload(): void
    {
        $this->seedScenario();

        $stmt = $this->statementFor($this->uploadMonth());

        $this->assertEquals(
            0,
            $stmt['projection_royalty_low'],
            'The projection timeline has not started yet - no value may be shown'
        );
    }

    /** @test */
    public function test_projection_rolls_over_to_next_projection_year(): void
    {
        $this->seedScenario();

        $stmt = $this->statementFor($this->originMonth()->copy()->addMonths(12));

        $this->assertEquals(
            1300,
            $stmt['projection_royalty_low'],
            '12 months after Month 1 the projection continues with Year 2 Month 1'
        );
    }

    /** @test */
    public function test_projection_shows_regardless_of_onboarding_stage(): void
    {
        $this->seedScenario(false); // partner not ACTIVE, no operational date

        $stmt = $this->statementFor($this->originMonth());

        $this->assertEquals(
            100,
            $stmt['projection_royalty_low'],
            'Projections are anchored to the upload month, not the onboarding stage'
        );
    }
}
