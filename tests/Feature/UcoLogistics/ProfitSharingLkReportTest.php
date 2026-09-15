<?php

namespace Tests\Feature\UcoLogistics;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Laporan Keuangan (profit-sharing LK) monthly report must show direct costs
 * for every calendar month, including months with fewer than 31 days.
 */
class ProfitSharingLkReportTest extends TestCase
{
    use DatabaseTransactions;

    private function admin()
    {
        return \App\User::where('username', 'admin')->firstOrFail();
    }

    private function seedWarehouse(string $taskId, string $type = 'booth'): int
    {
        return DB::table('warehouse_partner')->insertGetId([
            'warehouse_task_id'  => $taskId,
            'warehouse_type'     => $type,
            'Location'           => 'Test',
            'warehouse_location' => 'Loc '.$taskId,
            'warehouse_status'   => 'ACTIVE',
            'partner_task_id'    => 'PT-'.uniqid(),
            'Created_Date'       => now(),
            'Created_By'         => '__test_lk__',
        ]);
    }

    private function seedDirectCost(string $taskId, string $code, string $name, string $date, float $nominal): void
    {
        DB::table('warehouse_partner_journal')->insert([
            'warehouse_task_id' => $taskId,
            'journal_no'        => 'JVC-TEST-'.uniqid(),
            'group_account'     => 502,
            'account_no'        => $code,
            'account_name'      => $name,
            'posting_date'      => $date,
            'nominal'           => $nominal,
            'reference'         => '',
            'remark'            => '',
            'created_by'        => '__test_lk__',
            'created_date'      => now(),
        ]);
    }

    /** @test */
    public function lk_report_shows_direct_cost_for_a_30_day_month()
    {
        $this->seedWarehouse('TEST-LK-JUN', 'booth');
        $this->seedDirectCost('TEST-LK-JUN', '52001', 'Bensin', '2026-06-15', 150000);

        $body = $this->actingAs($this->admin())
            ->get('/xml-partner-profit-sharing-lk-monthly?Task=TEST-LK-JUN&date=2026-06&type=booth')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('150000', $body, 'June (30 days) direct cost must render');
    }

    /** @test */
    public function lk_report_shows_direct_cost_for_a_31_day_month()
    {
        $this->seedWarehouse('TEST-LK-JUL', 'booth');
        $this->seedDirectCost('TEST-LK-JUL', '52001', 'Bensin', '2026-07-15', 250000);

        $body = $this->actingAs($this->admin())
            ->get('/xml-partner-profit-sharing-lk-monthly?Task=TEST-LK-JUL&date=2026-07&type=booth')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('250000', $body);
    }

    /** @test */
    public function direct_cost_lines_match_the_coa_account_codes()
    {
        // Booth template direct-cost lines must use the COA codes/names.
        $lines = DB::table('master_warehouse_profit_loss')
            ->where('type', 'booth')->where('Rumus', '502')
            ->pluck('Description', 'Code');

        $this->assertEquals('Bensin', $lines['52001'] ?? null);
        $this->assertEquals('Charging', $lines['52002'] ?? null);
        $this->assertEquals('Parkir', $lines['52003'] ?? null);
    }
}
