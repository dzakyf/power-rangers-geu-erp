<?php

namespace Tests\Unit\CNS;

use App\CnsInventoryKemasan;
use App\CnsKemasanSiteLimit;
use App\CnsMasterKemasan;
use App\Http\Controllers\CNS\MasterKemasanController;
use App\Services\CNS\KemasanInventoryService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\TestCase;

class MasterKemasanTest extends TestCase
{
    /** @test */
    public function model_has_expected_table_primary_key_and_columns()
    {
        $model = new CnsMasterKemasan();

        $this->assertSame('cns_master_kemasan', $model->getTable());
        $this->assertSame('Kemasan_ID', $model->getKeyName());
        $this->assertTrue($model->usesTimestamps());
        $this->assertSame(['Name', 'Volume', 'Is_Ampas', 'Max_Stock', 'Unit', 'Price'], $model->getFillable());

        $inventory = new CnsInventoryKemasan();
        $this->assertContains('site_id', $inventory->getFillable());
        $this->assertContains('site_name', $inventory->getFillable());
        $this->assertContains('out_type', $inventory->getFillable());
        $this->assertContains('qty_balance', $inventory->getFillable());
        $this->assertContains('excess_balance', $inventory->getFillable());

        $limit = new CnsKemasanSiteLimit();
        $this->assertSame('cns_kemasan_site_limits', $limit->getTable());
        $this->assertSame(['kemasan_id', 'site_id', 'max_stock'], $limit->getFillable());

        $relation = $inventory->packaging();
        $this->assertInstanceOf(CnsMasterKemasan::class, $relation->getRelated());
        $this->assertSame('packaging_id', $relation->getForeignKeyName());
        $this->assertSame('Kemasan_ID', $relation->getOwnerKeyName());
    }

    /** @test */
    public function controller_index_returns_view_and_store_requires_max_stock_unless_ampas()
    {
        $controller = new MasterKemasanController();
        $view = $controller->index();
        $this->assertSame('cns.master.kemasan', $view->name());

        $this->expectException(ValidationException::class);
        $controller->store(new Request(['Name' => 'Drum', 'Volume' => 200, 'Is_Ampas' => 0]));
    }

    /** @test */
    public function stock_bucket_calculation_handles_in_drop_penjualan_and_ampas()
    {
        $drum = new CnsMasterKemasan(['Name' => 'Drum', 'Volume' => 200, 'Is_Ampas' => false, 'Max_Stock' => 100]);
        $ampas = new CnsMasterKemasan(['Name' => 'Ampas', 'Volume' => null, 'Is_Ampas' => true, 'Max_Stock' => null]);

        // IN fills Qty up to Max_Stock (100), remainder goes to Excess (20)
        $this->assertSame([100.0, 20.0, 120.0, null], KemasanInventoryService::computeNextBalances(0, 0, 'IN', 120, $drum));

        // OUT DROP reduces Qty only (100 -> 90), Excess stays 20
        $this->assertSame([90.0, 20.0, 110.0, 'DROP'], KemasanInventoryService::computeNextBalances(100, 20, 'OUT', 10, $drum, 'DROP'));

        // Next IN tops Qty back up to 100 (+10) and puts +5 into Excess (20 -> 25)
        $this->assertSame([100.0, 25.0, 125.0, null], KemasanInventoryService::computeNextBalances(90, 20, 'IN', 15, $drum));

        // OUT PENJUALAN reduces Excess only (25 -> 10), Qty stays 100
        $this->assertSame([100.0, 10.0, 110.0, 'PENJUALAN'], KemasanInventoryService::computeNextBalances(100, 25, 'OUT', 15, $drum, 'PENJUALAN'));

        // Ampas ignores Max_Stock and Excess; both DROP and PENJUALAN reduce Qty
        $this->assertSame([150.0, 0.0, 150.0, null], KemasanInventoryService::computeNextBalances(0, 0, 'IN', 150, $ampas));
        $this->assertSame([120.0, 0.0, 120.0, 'PENJUALAN'], KemasanInventoryService::computeNextBalances(150, 0, 'OUT', 30, $ampas, 'PENJUALAN'));
        $this->assertSame([100.0, 0.0, 100.0, 'DROP'], KemasanInventoryService::computeNextBalances(120, 0, 'OUT', 20, $ampas, 'DROP'));

        // Legacy row without bucket columns falls back to current_balance in Qty
        $legacy = new CnsInventoryKemasan(['current_balance' => 45, 'qty_balance' => 0, 'excess_balance' => 0]);
        $this->assertSame([45.0, 0.0], KemasanInventoryService::extractBucketBalances($legacy));

        // Per-site Max_Stock override supersedes model default Max_Stock
        $this->assertSame([200.0, 10.0, 210.0, null], KemasanInventoryService::computeNextBalances(0, 0, 'IN', 210, $drum, null, 200.0));
        $this->assertArrayHasKey(1, KemasanInventoryService::SITES);
        $this->assertSame('Malang', KemasanInventoryService::SITES[1]);

        // Name containing 'ampas' is treated as Ampas even when Is_Ampas flag is false
        $ampasByName = new CnsMasterKemasan(['Name' => 'AMPAS', 'Volume' => 0, 'Is_Ampas' => false, 'Max_Stock' => null]);
        $this->assertTrue($ampasByName->isAmpas());
        $this->assertSame([50.0, 0.0, 50.0, null], KemasanInventoryService::computeNextBalances(0, 0, 'IN', 50, $ampasByName));

        // Report ordering places AMPAS at the end after non-Ampas items
        $jerigen = new CnsMasterKemasan(['Name' => 'Jerigen', 'Volume' => 5, 'Is_Ampas' => false, 'Max_Stock' => 10]);
        $jerigen->Kemasan_ID = 1;
        $tong = new CnsMasterKemasan(['Name' => 'Tong 200L plastik', 'Volume' => 200, 'Is_Ampas' => false, 'Max_Stock' => 100]);
        $tong->Kemasan_ID = 2;
        $ampasByName->Kemasan_ID = 3;
        $ordered = KemasanInventoryService::orderKemasansForReport(collect([$ampasByName, $jerigen, $tong]));
        $this->assertSame(['Jerigen', 'Tong 200L plastik', 'AMPAS'], $ordered->pluck('Name')->all());

        // Report totals compute per-site (row), per-kemasan (column), and grand totals
        $totals = KemasanInventoryService::computeReportTotals([
            [
                'site_id' => 1,
                'cells'   => [
                    1 => ['max_stock' => 10, 'qty' => 10, 'excess' => 46],
                    2 => ['max_stock' => 200, 'qty' => 150, 'excess' => 0],
                    3 => ['max_stock' => null, 'qty' => 25, 'excess' => 0],
                ],
            ],
            [
                'site_id' => 2,
                'cells'   => [
                    1 => ['max_stock' => 10, 'qty' => 8, 'excess' => 0],
                    2 => ['max_stock' => 250, 'qty' => 250, 'excess' => 15],
                    3 => ['max_stock' => null, 'qty' => 5, 'excess' => 0],
                ],
            ],
        ], $ordered);

        $this->assertSame(['max_stock' => 210.0, 'qty' => 185.0, 'excess' => 46.0], $totals['row_totals'][1]);
        $this->assertSame(['max_stock' => 260.0, 'qty' => 263.0, 'excess' => 15.0], $totals['row_totals'][2]);
        $this->assertSame(['max_stock' => 20.0, 'qty' => 18.0, 'excess' => 46.0], $totals['column_totals'][1]);
        $this->assertSame(['max_stock' => 450.0, 'qty' => 400.0, 'excess' => 15.0], $totals['column_totals'][2]);
        $this->assertSame(['max_stock' => 0.0, 'qty' => 30.0, 'excess' => 0.0], $totals['column_totals'][3]);
        $this->assertSame(['max_stock' => 470.0, 'qty' => 448.0, 'excess' => 61.0], $totals['grand_totals']);
    }

    /** @test */
    public function out_penjualan_rejects_insufficient_excess()
    {
        $drum = new CnsMasterKemasan(['Name' => 'Drum', 'Volume' => 200, 'Is_Ampas' => false, 'Max_Stock' => 100]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stok Excess tidak mencukupi');
        KemasanInventoryService::computeNextBalances(100, 5, 'OUT', 10, $drum, 'PENJUALAN');
    }

    /** @test */
    public function out_drop_rejects_insufficient_qty()
    {
        $drum = new CnsMasterKemasan(['Name' => 'Drum', 'Volume' => 200, 'Is_Ampas' => false, 'Max_Stock' => 100]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stok Qty tidak mencukupi');
        KemasanInventoryService::computeNextBalances(5, 20, 'OUT', 10, $drum, 'DROP');
    }

    /** @test */
    public function master_kemasan_view_has_expected_grid_and_form()
    {
        $path = resource_path('views/cns/master/kemasan.blade.php');
        $this->assertFileExists($path);
        $content = file_get_contents($path);

        $this->assertStringContainsString('MASTER KEMASAN', $content);
        $this->assertStringContainsString('Kemasan_ID,Name,Volume (L),Jenis,Max Stock,Unit,Price,Created At,Updated At', $content);
        $this->assertStringContainsString('name="Name"', $content);
        $this->assertStringContainsString('name="Volume"', $content);
        $this->assertStringContainsString('name="Is_Ampas"', $content);
        $this->assertStringContainsString('name="Max_Stock"', $content);
        $this->assertStringContainsString('name="Unit"', $content);
        $this->assertStringContainsString('name="Price"', $content);
        $this->assertStringContainsString("route('cns-master-kemasan.store')", $content);
        $this->assertStringContainsString("url('cns-master-kemasan-update')", $content);
        $this->assertStringContainsString("url('cns-master-kemasan-delete')", $content);
    }

    /** @test */
    public function inventory_kemasan_view_uses_tabs_report_matrix_and_site_controls()
    {
        $path = resource_path('views/cns/inventory-kemasan/index.blade.php');
        $content = file_get_contents($path);

        $this->assertStringContainsString('card-header-tabs', $content);
        $this->assertStringContainsString('data-bs-toggle="tab"', $content);
        $this->assertStringContainsString('switchKemasanTab', $content);
        $this->assertStringContainsString('data-bs-toggle="modal"', $content);
        $this->assertStringContainsString('Monitoring Mutasi', $content);
        $this->assertStringContainsString('Report Inventory Kemasan', $content);
        $this->assertStringContainsString('id="tab-monitoring"', $content);
        $this->assertStringContainsString('id="tab-report"', $content);
        $this->assertStringContainsString('report-kemasan-table', $content);
        $this->assertStringContainsString('<th>Max Stok</th>', $content);
        $this->assertStringContainsString('<th>Excess</th>', $content);
        $this->assertStringContainsString('<th rowspan="2">{{ $mk->Name }}</th>', $content);
        $this->assertStringContainsString('<th colspan="3">TOTAL</th>', $content);
        $this->assertStringContainsString('class="row-total"', $content);
        $this->assertStringContainsString('Atur Max Stok Site', $content);
        $this->assertStringContainsString('id="filterSite"', $content);
        $this->assertStringContainsString('name="site_id"', $content);
        $this->assertStringContainsString("route('cns-inventory-kemasan.site-limits')", $content);

        // Button Catat Mutasi Stok Kemasan is located inside Tab 2 (below the report table)
        $tab1Pos = strpos($content, 'id="tab-monitoring"');
        $tab2Pos = strpos($content, 'id="tab-report"');
        $btnPos  = strpos($content, 'data-target="#modalManualMutation"');
        $tblEnd  = strpos($content, '</table>', $tab2Pos);
        $this->assertNotFalse($tab1Pos);
        $this->assertNotFalse($tab2Pos);
        $this->assertNotFalse($btnPos);
        $this->assertGreaterThan($tab2Pos, $btnPos);
        $this->assertGreaterThan($tblEnd, $btnPos);

        $this->assertStringContainsString('Pilih Dari Master Kemasan', $content);
        $this->assertStringContainsString('value="pkg:{{ $mp->Kemasan_ID }}"', $content);
        $this->assertStringContainsString('value="manual:{{ $ei }}"', $content);
        $this->assertStringContainsString("params.push('packaging_id='", $content);
        $this->assertStringContainsString('name="out_type"', $content);
        $this->assertStringContainsString('Saldo Qty,Saldo Excess,Total Saldo', $content);
        $this->assertStringNotContainsString('KemasanGrid.attachHeader', $content);
        $this->assertStringNotContainsString('Master Packaging', $content);
    }

    /** @test */
    public function menu_and_migration_definitions_exist()
    {
        $menuData = file_get_contents(database_path('seeds/data/menu_lists_cns.php'));
        $this->assertStringContainsString("'SI00342' => ['Master Kemasan', 'cns-master-kemasan', 'CNS', 4, 'SS00088', 6]", $menuData);

        $baseMigration = file_get_contents(database_path('migrations/cns/2026_11_11_000001_create_cns_master_kemasan_table_and_menu.php'));
        $this->assertStringContainsString("bigIncrements('Kemasan_ID')", $baseMigration);
        $this->assertStringContainsString("string('Name', 150);", $baseMigration);
        $this->assertStringNotContainsString("->unique()", $baseMigration);

        $stockMigration = file_get_contents(database_path('migrations/cns/2026_11_11_000002_add_stock_limits_to_kemasan_tables.php'));
        $this->assertStringContainsString('Is_Ampas', $stockMigration);
        $this->assertStringContainsString('Max_Stock', $stockMigration);
        $this->assertStringContainsString('out_type', $stockMigration);
        $this->assertStringContainsString('qty_balance', $stockMigration);
        $this->assertStringContainsString('excess_balance', $stockMigration);

        $siteMigration = file_get_contents(database_path('migrations/cns/2026_11_11_000003_add_site_to_kemasan_and_create_site_limits_table.php'));
        $this->assertStringContainsString('site_id', $siteMigration);
        $this->assertStringContainsString('site_name', $siteMigration);
        $this->assertStringContainsString('cns_kemasan_site_limits', $siteMigration);
    }
}
