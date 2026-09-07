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
 * 1..4). Month 1 must map to the calendar month in which the partner's
 * warehouse became operational (opportunity_partner.onboarding_stage =
 * 'ACTIVE' via onboarding_stage_updated_at), NOT to the projection Excel
 * upload date (MIN(projections.Created_At)). Calendar months before the
 * operational month must show 0 (the partner had not joined yet), and
 * pre-anchor months must not mirror post-anchor columns (Carbon 2's
 * absolute diffInMonths()).
 *
 * The fixture mimics the real flow: the Excel is uploaded during onboarding
 * SETUP (3 months before operations start) and the partner goes ACTIVE
 * later. WarehouseService::calculateFinancialStatement() is called exactly
 * like ProfitSharingController@dashboardProfitSharingXml does: one call per
 * calendar month with a single-month range.
 *
 * All fixture tables are InnoDB and the inserts happen inside the per-test
 * transaction opened by DatabaseTransactions, so nothing persists to the
 * dev database.
 */
class ProfitSharingProjectionAnchorTest extends TestCase
{
    use DatabaseTransactions;

    private const WAREHOUSE_TASK_ID = 'TEST-PS-1';
    private const LEAD_ID = 'BP/TEST-PROFITSHARING/0001';
    private const CREATED_BY = '__test_profitsharing__';

    /**
     * The month the warehouse becomes operational (2 months before now).
     */
    private function operationalMonth(): Carbon
    {
        return now()->startOfMonth()->subMonths(2);
    }

    /**
     * The month the projection Excel was uploaded during SETUP
     * (3 months before the operational month).
     */
    private function uploadMonth(): Carbon
    {
        return $this->operationalMonth()->copy()->subMonths(3);
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

    /**
     * Runs the statement exactly like the dashboard does: one single-month
     * range for the given calendar month.
     */
    private function statementFor(Carbon $month): array
    {
        return app(WarehouseService::class)->calculateFinancialStatement(
            self::WAREHOUSE_TASK_ID,
            $month->copy()->startOfMonth()->toDateString(),
            $month->copy()->endOfMonth()->toDateString()
        );
    }

    /** @test */
    public function test_first_projection_month_is_the_operational_month(): void
    {
        $this->seedScenario();

        $stmt = $this->statementFor($this->operationalMonth());

        $this->assertEquals(
            100,
            $stmt['projection_royalty_low'],
            'Projection Month 1 belongs to the month the warehouse became operational'
        );
    }

    /** @test */
    public function test_second_projection_month_follows_operational_month(): void
    {
        $this->seedScenario();

        $stmt = $this->statementFor($this->operationalMonth()->copy()->addMonth());

        $this->assertEquals(
            200,
            $stmt['projection_royalty_low'],
            'Projection Month 2 is the month after the operational month'
        );
    }

    /** @test */
    public function test_projection_is_zero_before_operational_month(): void
    {
        $this->seedScenario();

        $stmt = $this->statementFor($this->operationalMonth()->copy()->subMonth());

        $this->assertEquals(
            0,
            $stmt['projection_royalty_low'],
            'The partner had not joined yet - no projection may be shown'
        );
    }

    /** @test */
    public function test_projection_rolls_over_to_next_projection_year(): void
    {
        $this->seedScenario();

        $stmt = $this->statementFor($this->operationalMonth()->copy()->addMonths(12));

        $this->assertEquals(
            1300,
            $stmt['projection_royalty_low'],
            '12 months after Month 1 the projection continues with Year 2 Month 1'
        );
    }

    /** @test */
    public function test_projection_is_zero_for_partner_not_yet_active(): void
    {
        $this->seedScenario(false);

        $stmt = $this->statementFor($this->uploadMonth());

        $this->assertEquals(
            0,
            $stmt['projection_royalty_low'],
            'Without an operational (ACTIVE) date no projection may be shown'
        );
    }
}
