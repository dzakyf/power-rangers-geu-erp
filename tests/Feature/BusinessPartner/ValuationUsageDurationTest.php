<?php

namespace Tests\Feature\BusinessPartner;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regression tests for Finance > Valuation (Valuasi / Summary Valuasi) —
 * the "Waktu Pakai (Bulan)" (usage duration) column.
 *
 * Commit 99a8f518 (2026-08-11) replaced the date-based usage duration with
 * the static master_facility.shelf_life config value. The usage duration
 * must be the number of whole months elapsed since the asset row's
 * opportunity_partner_facility_dtl.Order_Date, capped at the depreciation
 * period (depreciation_year * 12 months) with the remaining duration floored
 * at 0, and 0 when Order_Date is missing ('0000-00-00').
 *
 * Routes under test (routes/web.php:5092-5099, middleware web+log.access):
 *   GET xml-partner-valuation-detail           -> ValuationController@detailValuationXml
 *   GET xml-partner-valuation-detail-aggregate -> ValuationController@detailAggregateValuationXml
 *   GET xml-partner-valuation-summary          -> ValuationController@summaryValuationXml
 *
 * The endpoints emit dhtmlxgrid XML via echo(); output is captured with
 * ob_start()/ob_get_clean() (see UnknownPartnerNameGridTest).
 *
 * All fixture tables are InnoDB and the inserts happen inside the per-test
 * transaction opened by DatabaseTransactions, so nothing persists to the
 * dev database.
 */
class ValuationUsageDurationTest extends TestCase
{
    use DatabaseTransactions;

    private const DETAIL_URL = '/xml-partner-valuation-detail';
    private const AGGREGATE_URL = '/xml-partner-valuation-detail-aggregate';
    private const SUMMARY_URL = '/xml-partner-valuation-summary';

    private const WAREHOUSE_TASK_ID = 'TEST-VAL-1';
    private const LEAD_ID = 'BP/TEST-VALUATION/0001';

    /**
     * Deliberately bogus shelf_life: a regression back to the static
     * shelf_life value can never satisfy any assertion below.
     */
    private const BOGUS_SHELF_LIFE = 99;

    private const ASSET_TOTAL = 1200000;

    /**
     * Fixture user, created lazily on first use. The `users` table is
     * MyISAM and does not participate in the per-test rollback, so we
     * never insert users - we only reuse an existing identity.
     *
     * @var User|null
     */
    private $user;

    // ------------------------------------------------------------------
    // Fixtures
    // ------------------------------------------------------------------

    private function testUser(): User
    {
        if ($this->user) {
            return $this->user;
        }

        $user = User::query()->orderBy('id')->first();

        if (!$user) {
            $this->markTestSkipped('No user exists in the test database.');
        }

        return $this->user = $user;
    }

    /**
     * A user without any opportunity_partner row, required to isolate the
     * aggregate endpoint (it resolves the partner from the session user's
     * user_row_id and reads $partner[0]).
     */
    private function testUserWithoutOpportunityPartner(): User
    {
        $id = DB::table('users')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('opportunity_partner')
                    ->whereRaw('opportunity_partner.user_row_id = users.id');
            })
            ->orderBy('id')
            ->value('id');

        if (!$id) {
            $this->markTestSkipped('Every user already has an opportunity_partner row.');
        }

        return User::query()->findOrFail($id);
    }

    private function seedAsset(int $depreciationYear): int
    {
        return DB::table('master_facility')->insertGetId([
            'type' => 'Booth',
            'category' => 'Test',
            'sub_category' => 'Test',
            'description' => 'Valuation Test Asset',
            'asset_type' => 'Asset',
            'depreciation_year' => $depreciationYear,
            'is_deleted' => 0,
            'shelf_life' => self::BOGUS_SHELF_LIFE,
        ]);
    }

    /**
     * Seeds the partner account + warehouse; returns [opportunity id, lead id].
     */
    private function seedPartnerAndWarehouse(?int $userRowId = null): array
    {
        $opId = DB::table('opportunity_partner')->insertGetId([
            'full_name' => 'Valuation Test Partner',
            'company_name' => 'Valuation Test Co',
            'status' => 'ACTIVE',
            'lead_id' => self::LEAD_ID,
            'user_row_id' => $userRowId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('warehouse_partner')->insert([
            'warehouse_task_id' => self::WAREHOUSE_TASK_ID,
            'warehouse_type' => 'booth',
            'Location' => 'Test',
            'warehouse_location' => 'Lokasi Valuasi Test',
            'warehouse_status' => 'ACTIVE',
            'partner_task_id' => self::LEAD_ID,
            'capacity' => 0,
            'Created_Date' => now(),
            'Created_By' => '__test_valuation__',
        ]);

        return [$opId, self::LEAD_ID];
    }

    private function seedFacilityRow(int $opportunityId, int $facilityId, string $orderDate): int
    {
        return DB::table('opportunity_partner_facility_dtl')->insertGetId([
            'Partner_ID' => $opportunityId,
            'warehouse_task_id' => self::WAREHOUSE_TASK_ID,
            'Opportunity_Facility_ID' => 0,
            'Facility_ID' => $facilityId,
            'Qty' => 1,
            'Price' => self::ASSET_TOTAL,
            'Total' => self::ASSET_TOTAL,
            'Order_Date' => $orderDate,
            'Created_By' => '__test_valuation__',
        ]);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /**
     * Returns the <cell> values of the grid row with the given row id
     * (detail XML uses <row id="...">, aggregate XML uses <row ID="...">).
     */
    private function rowCells(string $xml, string $rowId): array
    {
        if (!preg_match('/<row [iI][dD]="'.preg_quote($rowId, '/').'">(.*?)<\/row>/s', $xml, $row)) {
            $this->fail("Row \"{$rowId}\" not found in the grid XML.");
        }

        preg_match_all('/<cell>(.*?)<\/cell>/s', $row[1], $cells);

        return $cells[1];
    }

    /**
     * An order date exactly $months calendar months before the current
     * month. startOfMonth() keeps the diffInMonths() arithmetic exact.
     */
    private function orderDateMonthsAgo(int $months): string
    {
        return now()->startOfMonth()->subMonths($months)->toDateString();
    }

    // ------------------------------------------------------------------
    // xml-partner-valuation-detail
    // ------------------------------------------------------------------

    /** @test */
    public function test_usage_duration_is_months_since_order_date(): void
    {
        [$opId, $leadId] = $this->seedPartnerAndWarehouse();
        $facilityId = $this->seedAsset(10); // 120 depreciation months
        $dtlId = $this->seedFacilityRow($opId, $facilityId, $this->orderDateMonthsAgo(3));

        ob_start();
        $response = $this->actingAs($this->testUser())
            ->get(self::DETAIL_URL.'?'.http_build_query(['partner' => $opId, 'warehouse' => self::WAREHOUSE_TASK_ID]));
        $body = (string) ob_get_clean();

        $response->assertOk();

        // Cells: 0 No, 1 Tanggal Pembelian, 2 Keterangan, 3 Qty, 4 Harga,
        // 5 Jumlah, 6 Ekonomis (Tahun), 7 % Penyusutan, 8 Penyusutan/bulan,
        // 9 Status, 10 Waktu Pakai (Bulan), 11 Nilai Yang Terpakai,
        // 12 Sisa Waktu Penyusutan, 13 Sisa Nilai Aset.
        $cells = $this->rowCells($body, (string) $dtlId);

        // Monthly depreciation = 1,200,000 * (1/10) / 12 = 10,000.
        $this->assertSame('3', $cells[10], 'Usage duration must be whole months since Order_Date');
        $this->assertSame('30000', $cells[11], 'Nilai Yang Terpakai = monthly depreciation * months used');
        $this->assertSame('117', $cells[12], 'Sisa Waktu Penyusutan = 120 - months used');
        $this->assertSame('1170000', $cells[13]);
    }

    /** @test */
    public function test_usage_duration_capped_at_depreciation_period(): void
    {
        [$opId, $leadId] = $this->seedPartnerAndWarehouse();
        $facilityId = $this->seedAsset(1); // 12 depreciation months
        $dtlId = $this->seedFacilityRow($opId, $facilityId, $this->orderDateMonthsAgo(20));

        ob_start();
        $response = $this->actingAs($this->testUser())
            ->get(self::DETAIL_URL.'?'.http_build_query(['partner' => $opId, 'warehouse' => self::WAREHOUSE_TASK_ID]));
        $body = (string) ob_get_clean();

        $response->assertOk();

        $cells = $this->rowCells($body, (string) $dtlId);

        // 20 months since order, but the asset is fully depreciated after 12:
        // monthly depreciation = 1,200,000 / 12 = 100,000.
        $this->assertSame('12', $cells[10], 'Usage duration must not exceed the depreciation period');
        $this->assertSame('1200000', $cells[11]);
        $this->assertSame('0', $cells[12], 'Sisa Waktu Penyusutan must never be negative');
        $this->assertSame('0', $cells[13]);
    }

    /** @test */
    public function test_usage_duration_is_zero_when_order_date_missing(): void
    {
        [$opId, $leadId] = $this->seedPartnerAndWarehouse();
        $facilityId = $this->seedAsset(10);
        $dtlId = $this->seedFacilityRow($opId, $facilityId, '0000-00-00');

        ob_start();
        $response = $this->actingAs($this->testUser())
            ->get(self::DETAIL_URL.'?'.http_build_query(['partner' => $opId, 'warehouse' => self::WAREHOUSE_TASK_ID]));
        $body = (string) ob_get_clean();

        $response->assertOk();

        $cells = $this->rowCells($body, (string) $dtlId);

        $this->assertSame('0', $cells[10], 'Missing Order_Date must yield 0 months of usage');
        $this->assertSame('0', $cells[11]);
        $this->assertSame('120', $cells[12]);
        $this->assertSame('1200000', $cells[13]);
    }

    // ------------------------------------------------------------------
    // xml-partner-valuation-detail-aggregate
    // ------------------------------------------------------------------

    /** @test */
    public function test_aggregate_usage_duration_is_months_since_order_date(): void
    {
        $user = $this->testUserWithoutOpportunityPartner();
        [$opId, $leadId] = $this->seedPartnerAndWarehouse($user->id);
        $facilityId = $this->seedAsset(10);
        $dtlId = $this->seedFacilityRow($opId, $facilityId, $this->orderDateMonthsAgo(2));

        // Unlike the other two endpoints this one returns a proper Response
        // instead of echoing, so the body comes from the response object.
        $response = $this->actingAs($user)->get(self::AGGREGATE_URL);

        $response->assertOk();

        $body = (string) $response->getContent();

        $cells = $this->rowCells($body, (string) $dtlId);

        $this->assertSame('2', $cells[10], 'Aggregate grid must also derive usage duration from Order_Date');
    }

    // ------------------------------------------------------------------
    // xml-partner-valuation-summary
    // ------------------------------------------------------------------

    /** @test */
    public function test_summary_usage_duration_is_months_since_order_date(): void
    {
        [$opId, $leadId] = $this->seedPartnerAndWarehouse();
        $facilityId = $this->seedAsset(10);
        $this->seedFacilityRow($opId, $facilityId, $this->orderDateMonthsAgo(4));

        ob_start();
        $response = $this->actingAs($this->testUser())->get(self::SUMMARY_URL);
        $body = (string) ob_get_clean();

        $response->assertOk();

        // Cells: 0 No, 1 Gudang ID, 2 Lokasi Gudang, 3 Nama Mitra,
        // 4 Kapasitas, 5 Status Gudang, 6 Total Investasi, 7 Total Aset,
        // 8 Nilai Yang Terpakai, 9 Sisa Nilai Aset.
        $cells = $this->rowCells($body, self::WAREHOUSE_TASK_ID);

        // Monthly depreciation = 10,000; 4 months used; 116 months remaining.
        $this->assertSame('40000', $cells[8], 'Summary Nilai Terpakai must use months since Order_Date');
        $this->assertSame('1160000', $cells[9]);
    }
}
