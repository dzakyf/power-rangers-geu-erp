<?php

namespace Tests\Feature\BusinessPartner;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regression tests for Support/Partner - Order Equipment "Harga Satuan".
 *
 * Requirement: the unit price is seeded from master_facility.price (its only
 * purpose is the initial value), the grid field is editable, and an edited
 * value is persisted per mitra in opportunity_partner_facility_dtl.Price and
 * stays visible on reload regardless of later master-price changes.
 *
 * Defects locked in here:
 *   1. The inventory XML forced master_facility.price whenever it was set,
 *      masking the saved per-mitra price (unique prices were stored but
 *      never shown).
 *   2. The booth grid declared Harga Satuan as read-only (`ron`), so it
 *      could not be edited.
 *   3. A submit that carried only the changed price cell stored a blank
 *      Total (0); the server now falls back to Qty x Price.
 *
 * Routes under test (routes/web.php:4734-4741, middleware web+log.access):
 *   GET  xml-partner-order-equipment-detail-booth-inventory?Task=<wh id>
 *   GET  xml-partner-order-equipment-detail-mobil-inventory?Task=<wh id>
 *   POST partner-order-equipment-detail-update?Task=<partner id>&wh=<wh id>
 *
 * The inventory endpoints echo XML (their header() calls are guarded by
 * !headers_sent()), so output is captured with ob_start(). The update
 * endpoint reads the raw request body, so tests post urlencoded content like
 * the dhtmlx dataProcessor does.
 *
 * All fixture tables are InnoDB and inserts happen inside the per-test
 * transaction opened by DatabaseTransactions. The `users` table is MyISAM -
 * an existing identity is reused read-only.
 */
class OrderEquipmentPriceTest extends TestCase
{
    use DatabaseTransactions;

    private const BOOTH_URL = '/xml-partner-order-equipment-detail-booth-inventory';
    private const MOBIL_URL = '/xml-partner-order-equipment-detail-mobil-inventory';
    private const UPDATE_URL = '/partner-order-equipment-detail-update';

    private const WH_ID = 'TEST-OE-WH-1';
    private const LEAD_ID = 'BP/TEST-ORDER-EQUIPMENT/0001';

    /**
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

    private function seedFacility(string $type, string $description, float $price): int
    {
        return DB::table('master_facility')->insertGetId([
            'type' => $type,
            'category' => 'inventory',
            'sub_category' => 'Test',
            'description' => $description,
            'asset_type' => 'Asset',
            'depreciation_year' => 10,
            'is_deleted' => 0,
            'shelf_life' => 5,
            'price' => $price,
        ]);
    }

    private function seedPartnerAndWarehouse(): int
    {
        $partnerId = DB::table('opportunity_partner')->insertGetId([
            'full_name' => 'Order Equipment Test Partner',
            'company_name' => 'Order Equipment Test Co',
            'status' => 'ACTIVE',
            'lead_id' => self::LEAD_ID,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('warehouse_partner')->insert([
            'warehouse_task_id' => self::WH_ID,
            'warehouse_type' => 'booth',
            'Location' => 'Test',
            'warehouse_location' => 'Lokasi Order Equipment Test',
            'warehouse_status' => 'ACTIVE',
            'partner_task_id' => self::LEAD_ID,
            'capacity' => 0,
            'Created_Date' => now(),
            'Created_By' => '__test_order_equipment__',
        ]);

        return $partnerId;
    }

    private function seedSavedPrice(int $partnerId, int $facilityId, float $price, float $qty = 1, float $total = 0): int
    {
        return DB::table('opportunity_partner_facility_dtl')->insertGetId([
            'Partner_ID' => $partnerId,
            'Facility_ID' => $facilityId,
            'warehouse_task_id' => self::WH_ID,
            'Opportunity_Facility_ID' => 0,
            'Qty' => $qty,
            'Price' => $price,
            'Total' => $total,
            'Order_Date' => now()->format('Y-m-d'),
            'Created_By' => '__test_order_equipment__',
            'Created_Date' => now(),
        ]);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function fetchInventory(string $url): string
    {
        ob_start();
        $response = $this->actingAs($this->testUser())->get($url.'?Task='.self::WH_ID);
        $body = (string) ob_get_clean();

        $response->assertOk();

        return $body;
    }

    /**
     * Returns the <cell> values of the grid row with the given facility id.
     */
    private function rowCells(string $xml, int $facilityId): array
    {
        if (!preg_match('/<row id="'.$facilityId.'"\s*>(.*?)<\/row>/s', $xml, $row)) {
            $this->fail("Row \"{$facilityId}\" not found in the inventory XML.");
        }

        preg_match_all('/<cell>(.*?)<\/cell>/s', $row[1], $cells);

        return $cells[1];
    }

    private function postGridUpdate(int $partnerId, string $body): void
    {
        ob_start();
        $response = $this->actingAs($this->testUser())->call(
            'POST',
            self::UPDATE_URL.'?Task='.$partnerId.'&wh='.self::WH_ID,
            [],
            [],
            [],
            [],
            $body
        );
        ob_get_clean();

        $response->assertOk();
    }

    // ------------------------------------------------------------------
    // Display precedence
    // ------------------------------------------------------------------

    /** @test */
    public function test_master_price_is_the_initial_value_when_no_saved_row(): void
    {
        $this->seedPartnerAndWarehouse();
        $facilityId = $this->seedFacility('booth', 'OE-MASTER-ONLY', 500000);

        $cells = $this->rowCells($this->fetchInventory(self::BOOTH_URL), $facilityId);

        $this->assertEquals(500000, (float) $cells[4], 'The master price must seed Harga Satuan when no mitra value exists');
    }

    /** @test */
    public function test_saved_mitra_price_takes_precedence_on_booth(): void
    {
        $partnerId = $this->seedPartnerAndWarehouse();
        $facilityId = $this->seedFacility('booth', 'OE-BOOTH-SAVED', 500000);
        $this->seedSavedPrice($partnerId, $facilityId, 750000);

        $cells = $this->rowCells($this->fetchInventory(self::BOOTH_URL), $facilityId);

        $this->assertEquals(750000, (float) $cells[4], 'The saved mitra price must win over the master price');
    }

    /** @test */
    public function test_saved_mitra_price_takes_precedence_on_mobil(): void
    {
        $partnerId = $this->seedPartnerAndWarehouse();
        $facilityId = $this->seedFacility('mobil', 'OE-MOBIL-SAVED', 300000000);
        $this->seedSavedPrice($partnerId, $facilityId, 123000);

        $cells = $this->rowCells($this->fetchInventory(self::MOBIL_URL), $facilityId);

        $this->assertEquals(123000, (float) $cells[5], 'The saved mitra price must win over the master price (mobil)');
    }

    /** @test */
    public function test_saved_zero_price_is_honored(): void
    {
        $partnerId = $this->seedPartnerAndWarehouse();
        $facilityId = $this->seedFacility('booth', 'OE-ZERO-SAVED', 500000);
        $this->seedSavedPrice($partnerId, $facilityId, 0);

        $cells = $this->rowCells($this->fetchInventory(self::BOOTH_URL), $facilityId);

        $this->assertEquals(0, (float) $cells[4], 'A saved per-mitra value is always honored, even when 0');
    }

    // ------------------------------------------------------------------
    // Submit persistence
    // ------------------------------------------------------------------

    /** @test */
    public function test_submitted_edited_price_is_persisted_with_total_fallback(): void
    {
        $partnerId = $this->seedPartnerAndWarehouse();
        $facilityId = $this->seedFacility('booth', 'OE-SUBMIT-PRICE', 500000);

        // dhtmlx dataProcessor payload: only the price cell carries a value,
        // the Total cell is blank (the grid recalculates it client-side).
        $body = 'ids='.$facilityId
            .'&'.$facilityId.'_c3=2'
            .'&'.$facilityId.'_c4=900000'
            .'&'.$facilityId.'_c5='
            .'&'.$facilityId.'_c6=2026-09-09'
            .'&'.$facilityId.'_!nativeeditor_status=updated';

        $this->postGridUpdate($partnerId, $body);

        $row = DB::table('opportunity_partner_facility_dtl')
            ->where('Facility_ID', $facilityId)
            ->where('Partner_ID', $partnerId)
            ->where('warehouse_task_id', self::WH_ID)
            ->first();

        $this->assertNotNull($row, 'The edited price must be persisted for the mitra');
        $this->assertEquals(900000, (float) $row->Price, 'The edited price must be saved verbatim');
        $this->assertEquals(2, (float) $row->Qty);
        $this->assertEquals(1800000, (float) $row->Total, 'A blank Total must fall back to Qty x Price');
    }
}
