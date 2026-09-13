<?php

namespace Tests\Feature\BusinessPartner;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regression tests for Partner > Asset Management detail.
 *
 * Requirements locked in here:
 *   1. Qty / Harga Satuan / Total Harga are read-only on the inventory grid.
 *   2. Those amounts never come from master_facility.price - they mirror the
 *      pre-existing order-equipment submission stored in
 *      opportunity_partner_facility_dtl (blank when nothing was submitted).
 *   3. The "Ceklist Ketersediaan" column is hidden on the Inventory, Access
 *      and Support grids (the availability check lives in partner-facility).
 *
 * Routes under test (routes/web.php:4721-4732, middleware web+log.access):
 *   GET xml-partner-asset-management-detail-booth-inventory?Task=<wh id>
 *   GET partner-asset-management-detail?ID=<wh id>
 *
 * The inventory endpoint echoes XML (header() is !headers_sent()-guarded), so
 * output is captured with ob_start(). All fixture tables are InnoDB and the
 * inserts happen inside the per-test transaction opened by DatabaseTransactions.
 */
class AssetManagementDisplayTest extends TestCase
{
    use DatabaseTransactions;

    private const INVENTORY_URL = '/xml-partner-asset-management-detail-booth-inventory';
    private const DETAIL_PAGE_URL = '/partner-asset-management-detail';

    private const WH_ID = 'TEST-AM-WH-1';
    private const LEAD_ID = 'BP/TEST-ASSET-MANAGEMENT/0001';

    /**
     * @var User|null
     */
    private $user;

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

    private function seedFacility(float $price): int
    {
        return DB::table('master_facility')->insertGetId([
            'type' => 'booth',
            'category' => 'inventory',
            'sub_category' => 'Test',
            'description' => 'AM Test Facility',
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
            'full_name' => 'Asset Management Test Partner',
            'company_name' => 'Asset Management Test Co',
            'status' => 'ACTIVE',
            'lead_id' => self::LEAD_ID,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('warehouse_partner')->insert([
            'warehouse_task_id' => self::WH_ID,
            'warehouse_type' => 'booth',
            'Location' => 'Test',
            'warehouse_location' => 'Lokasi Asset Management Test',
            'warehouse_status' => 'ACTIVE',
            'partner_task_id' => self::LEAD_ID,
            'capacity' => 0,
            'Created_Date' => now(),
            'Created_By' => '__test_asset_management__',
        ]);

        return $partnerId;
    }

    private function seedOrderEquipmentRow(int $partnerId, int $facilityId, float $qty, float $price, float $total): int
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

    private function fetchInventoryXml(): string
    {
        ob_start();
        $response = $this->actingAs($this->testUser())->get(self::INVENTORY_URL.'?Task='.self::WH_ID);
        $body = (string) ob_get_clean();

        $response->assertOk();

        return $body;
    }

    private function rowCells(string $xml, int $facilityId): array
    {
        if (!preg_match('/<row id="'.$facilityId.'"\s*>(.*?)<\/row>/s', $xml, $row)) {
            $this->fail("Row \"{$facilityId}\" not found in the inventory XML.");
        }

        preg_match_all('/<cell>(.*?)<\/cell>/s', $row[1], $cells);

        return $cells[1];
    }

    /** @test */
    public function test_inventory_amounts_come_from_the_order_equipment_submission(): void
    {
        $partnerId = $this->seedPartnerAndWarehouse();
        $facilityId = $this->seedFacility(500000);
        $this->seedOrderEquipmentRow($partnerId, $facilityId, 3, 750000, 2250000);

        $cells = $this->rowCells($this->fetchInventoryXml(), $facilityId);

        $this->assertEquals(3, (float) $cells[4], 'Qty must mirror the order-equipment submission');
        $this->assertEquals(750000, (float) $cells[5], 'Harga Satuan must mirror the order-equipment submission, not the master price');
        $this->assertEquals(2250000, (float) $cells[6], 'Total Harga must mirror the order-equipment submission');
    }

    /** @test */
    public function test_inventory_amounts_are_blank_without_an_order_equipment_submission(): void
    {
        $this->seedPartnerAndWarehouse();
        $facilityId = $this->seedFacility(500000);

        $cells = $this->rowCells($this->fetchInventoryXml(), $facilityId);

        $this->assertSame('', $cells[4], 'Qty must not be seeded from master data');
        $this->assertSame('', $cells[5], 'Harga Satuan must not fall back to the master price');
        $this->assertSame('', $cells[6], 'Total Harga must not be seeded from master data');
    }

    /** @test */
    public function test_detail_page_locks_amounts_and_hides_the_availability_checklist(): void
    {
        $this->seedPartnerAndWarehouse();

        $response = $this->actingAs($this->testUser())
            ->get(self::DETAIL_PAGE_URL.'?ID='.self::WH_ID);

        $response->assertOk();
        $body = (string) $response->getContent();

        // Inventory columns c4 (Qty), c5 (Harga Satuan), c6 (Total) read-only.
        $this->assertStringContainsString(
            'setColTypes("ro,ro,ro,ro,ron,ron,ron,ch,',
            $body,
            'Inventory Qty/Harga Satuan/Total must be read-only numeric'
        );

        // Ceklist Ketersediaan hidden on all three grids.
        $this->assertStringContainsString('setColumnHidden(7, true)', $body, 'Inventory availability column must be hidden');
        $this->assertSame(
            2,
            substr_count($body, 'setColumnHidden(3, true)'),
            'Access and Support availability columns must be hidden'
        );
    }
}
