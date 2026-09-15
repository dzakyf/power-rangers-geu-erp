<?php

namespace Tests\Feature\UcoLogistics;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Settlement -> financial-report journal posting.
 *   - BBM  -> 52001 (group 502, Direct Costs)
 *   - Parkir -> 52003 (group 502, Direct Costs)
 *   - other sections / multi-route / non-settled are skipped
 */
class SettlementJournalPostingTest extends TestCase
{
    use DatabaseTransactions;

    private function admin()
    {
        return \App\User::where('username', 'admin')->firstOrFail();
    }

    private function seedWarehouse(string $taskId): int
    {
        return DB::table('warehouse_partner')->insertGetId([
            'warehouse_task_id'  => $taskId,
            'warehouse_type'     => 'mobil',
            'Location'           => 'Test',
            'warehouse_location' => 'Loc '.$taskId,
            'warehouse_status'   => 'ACTIVE',
            'partner_task_id'    => 'PT-'.uniqid(),
            'Created_Date'       => now(),
            'Created_By'         => '__test_sj__',
        ]);
    }

    private function seedRoute(?int $warehouseId, string $status = 'SETTLEMENT_PENDING'): int
    {
        return DB::table('pickup_routes')->insertGetId([
            'Code'         => 'RTE-SJ-'.uniqid(),
            'Status'       => $status,
            'Warehouse_Id' => $warehouseId,
            'Created_At'   => now(),
        ]);
    }

    private function seedSettlement(string $routeId, string $description, float $nominal): int
    {
        return DB::table('settlement')->insertGetId([
            'Route_Id'    => $routeId,
            'Description' => $description,
            'Nominal'     => (string) $nominal,
            'Notes'       => 'note '.$description,
            'Created_By'  => '__test_sj__',
            'Created_At'  => now(),
        ]);
    }

    private function approveRoute(int $routeId)
    {
        return $this->actingAs($this->admin())
            ->post('/partner-pickup-finish-settlement', ['action' => 'approve', 'route_id' => $routeId]);
    }

    /** @test */
    public function approving_a_settlement_posts_bbm_and_parkir_to_direct_costs()
    {
        $wh = $this->seedWarehouse('TEST-SJ-A');
        $route = $this->seedRoute($wh);
        $bbm = $this->seedSettlement((string) $route, 'BBM', 100000);
        $parkir = $this->seedSettlement((string) $route, 'Parkir', 50000);
        $tol = $this->seedSettlement((string) $route, 'TOL', 25000);

        $this->approveRoute($route)->assertOk();

        $rows = DB::table('warehouse_partner_journal')
            ->whereIn('reference', ['SETTLEMENT-'.$bbm, 'SETTLEMENT-'.$parkir, 'SETTLEMENT-'.$tol])
            ->get();

        $this->assertCount(2, $rows, 'only mapped sections post');
        $byAccount = $rows->keyBy('account_no');
        $this->assertEquals(502, $byAccount['52001']->group_account);
        $this->assertEquals('TEST-SJ-A', $byAccount['52001']->warehouse_task_id);
        $this->assertEquals(100000.0, (float) $byAccount['52001']->nominal);
        $this->assertEquals(502, $byAccount['52003']->group_account);
        $this->assertEquals(50000.0, (float) $byAccount['52003']->nominal);
    }

    /** @test */
    public function re_approving_does_not_duplicate_journal_rows()
    {
        $wh = $this->seedWarehouse('TEST-SJ-B');
        $route = $this->seedRoute($wh);
        $this->seedSettlement((string) $route, 'BBM', 100000);

        $this->approveRoute($route)->assertOk();
        $this->approveRoute($route)->assertOk();

        $count = DB::table('warehouse_partner_journal')->where('reference', 'LIKE', 'SETTLEMENT-%')
            ->where('warehouse_task_id', 'TEST-SJ-B')->count();
        $this->assertEquals(1, $count);
    }

    /** @test */
    public function multi_route_settlements_are_skipped()
    {
        $wh = $this->seedWarehouse('TEST-SJ-C');
        $route = $this->seedRoute($wh);
        DB::table('settlement')->insertGetId([
            'Route_Id' => $route.',999', 'Description' => 'BBM', 'Nominal' => '100000',
            'Notes' => 'multi', 'Created_By' => '__test_sj__', 'Created_At' => now(),
        ]);

        $this->approveRoute($route)->assertOk();

        $count = DB::table('warehouse_partner_journal')->where('warehouse_task_id', 'TEST-SJ-C')->count();
        $this->assertEquals(0, $count, 'multi-route settlements cannot be attributed');
    }

    /** @test */
    public function posting_reflects_in_the_financial_report_direct_cost()
    {
        $wh = $this->seedWarehouse('TEST-SJ-D');
        $route = $this->seedRoute($wh);
        $this->seedSettlement((string) $route, 'BBM', 100000);
        $this->seedSettlement((string) $route, 'Parkir', 50000);

        $this->approveRoute($route)->assertOk();

        $stmt = app(\App\Services\WarehouseService::class)->calculateFinancialStatement(
            'TEST-SJ-D',
            now()->startOfMonth()->toDateString(),
            now()->endOfMonth()->toDateString()
        );

        $this->assertEquals(150000.0, (float) $stmt['direct_cost']);
    }

    /** @test */
    public function backfill_posts_settled_single_route_settlements_only()
    {
        $wh = $this->seedWarehouse('TEST-SJ-E');

        $settled = $this->seedRoute($wh, 'SETTLED');
        $bbm = $this->seedSettlement((string) $settled, 'BBM', 100000);
        $parkir = $this->seedSettlement((string) $settled, 'Parkir', 50000);
        $this->seedSettlement((string) $settled, 'TOL', 25000); // unmapped

        $pending = $this->seedRoute($wh, 'SETTLEMENT_PENDING');
        $this->seedSettlement((string) $pending, 'BBM', 999999); // not settled

        $multi = $this->seedRoute($wh, 'SETTLED');
        DB::table('settlement')->insertGetId([
            'Route_Id' => $multi.',1', 'Description' => 'BBM', 'Nominal' => '777',
            'Notes' => 'multi', 'Created_By' => '__test_sj__', 'Created_At' => now(),
        ]);

        Artisan::call('warehouse:backfill-settlement-journals', ['--apply' => true]);

        $refs = DB::table('warehouse_partner_journal')->where('warehouse_task_id', 'TEST-SJ-E')
            ->pluck('reference')->all();

        $this->assertContains('SETTLEMENT-'.$bbm, $refs);
        $this->assertContains('SETTLEMENT-'.$parkir, $refs);
        $this->assertEquals(2, count($refs), 'only settled + mapped + single-route rows post');

        // idempotent re-run
        Artisan::call('warehouse:backfill-settlement-journals', ['--apply' => true]);
        $this->assertEquals(2, DB::table('warehouse_partner_journal')->where('warehouse_task_id', 'TEST-SJ-E')->count());
    }
}
