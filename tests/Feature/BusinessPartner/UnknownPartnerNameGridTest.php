<?php

namespace Tests\Feature\BusinessPartner;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regression guard for "Undefined index: PT-P".
 *
 * Warehouse list grids build a name-lookup array ($usname) keyed by user
 * username / Emp_ID and opportunity_partner.lead_id. A warehouse whose
 * partner_task_id matches none of those keys used to crash the whole grid
 * with `ErrorException: Undefined index: <partner_task_id>` (seen in the
 * dev DB as probe warehouse W-PROBE-1 / partner_task_id "PT-P").
 *
 * The controllers now render the partner code as a fallback instead of
 * throwing. This test proves an affected grid still renders a row with the
 * fallback value when such an orphan warehouse is present.
 *
 * NOTE: only endpoints that avoid the legacy `header()` call are exercised
 * here; endpoints calling header() cannot be hit through the PHPUnit CLI
 * request pipeline ("headers already sent" from the test printer).
 */
class UnknownPartnerNameGridTest extends TestCase
{
    use DatabaseTransactions;

    private const PROBE_PARTNER_ID = 'PT-PROBE-NO-MATCH';

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('warehouse_partner')->insert([
            'warehouse_task_id'  => 'W-PROBE-TEST',
            'geu_express_id'     => null,
            'warehouse_type'     => 'booth',
            'Location'           => 'Test',
            'warehouse_location' => 'Lokasi Probe Test',
            'warehouse_status'   => 'ACTIVE',
            'partner_task_id'    => self::PROBE_PARTNER_ID,
            'capacity'           => 100,
            'Created_Date'       => now(),
            'Created_By'         => 'admin',
        ]);
    }

    /**
     * Returns an authenticated admin (Priv_ID ADM) so the grids list ALL
     * warehouses (the non-MITRA branch of each controller), which is the
     * path that exposed the orphan row.
     */
    private function adminUser(): User
    {
        return User::where('username', 'admin')->firstOrFail();
    }

    /** @test */
    public function management_fee_grid_renders_unknown_partner_as_fallback(): void
    {
        // indexProfitSharingManagementFeeXml() uses echo() (no header()), so
        // capture its output instead of reading the (empty) response body.
        ob_start();
        $response = $this->actingAs($this->adminUser())
            ->get('/xml-partner-profit-sharing-management-fee');
        $body = (string) ob_get_clean();

        $response->assertOk();

        // The grid renders a row whose "Nama Mitra" cell shows the partner
        // code as fallback rather than crashing with Undefined index.
        $this->assertStringContainsString(self::PROBE_PARTNER_ID, $body);
        $this->assertStringContainsString('W-PROBE-TEST', $body);
    }
}
