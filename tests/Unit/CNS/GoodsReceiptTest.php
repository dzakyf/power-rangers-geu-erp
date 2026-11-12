<?php

namespace Tests\Unit\CNS;

use App\Services\CNS\GoodsReceiptService;
use App\Services\CNS\NonUcoInventoryService;
use Tests\TestCase;

class GoodsReceiptTest extends TestCase
{
    /** @test */
    public function goods_receipt_routes_are_registered()
    {
        $content = file_get_contents(base_path('routes/modules/cns.php'));

        $this->assertStringContainsString('$grPref = "$procPref-goods-receipt";', $content);
        $this->assertStringContainsString('Route::get("$grPref"', $content);
        $this->assertStringContainsString('Route::get("xml-$grPref"', $content);
        $this->assertStringContainsString('Route::get("$grPref-create"', $content);
        $this->assertStringContainsString('Route::post("$grPref-store"', $content);
        $this->assertStringContainsString('Route::get("$grPref-po-items"', $content);
        $this->assertStringContainsString('Route::get("$grPref-detail/{id}"', $content);
        $this->assertStringContainsString('Route::post("$grPref-approve/{id}"', $content);
        $this->assertStringContainsString('Route::post("$grPref-void/{id}"', $content);
        $this->assertStringContainsString('Route::get("$grPref-print/{id}"', $content);
    }

    /** @test */
    public function non_uco_inventory_routes_are_registered()
    {
        $content = file_get_contents(base_path('routes/modules/cns.php'));

        $this->assertStringContainsString('$cnsNonUcoPref = "cns-inventory-non-uco";', $content);
        $this->assertStringContainsString('Route::get("$cnsNonUcoPref"', $content);
        $this->assertStringContainsString('Route::get("xml-$cnsNonUcoPref"', $content);
        $this->assertStringContainsString('Route::get("$cnsNonUcoPref-balance"', $content);
        $this->assertStringContainsString('Route::post("$cnsNonUcoPref-manual-mutation"', $content);
    }

    /** @test */
    public function goods_receipt_views_render_key_structures()
    {
        // 1. Index View
        $indexContent = file_get_contents(resource_path('views/cns/goods-receipt/index.blade.php'));
        $this->assertStringContainsString('GOODS RECEIPT (PENERIMAAN BARANG NON-UCO)', $indexContent);
        $this->assertStringContainsString('GRGrid', $indexContent);
        $this->assertStringContainsString('filterStatus', $indexContent);
        $this->assertStringContainsString('cns-procurement-goods-receipt.create', $indexContent);
        $this->assertStringContainsString('approveSelectedGR', $indexContent);
        $this->assertStringContainsString('btnApproveGR', $indexContent);
        $this->assertStringContainsString('dhtmlx.confirm', $indexContent);
        $this->assertStringContainsString('dhtmlx.alert', $indexContent);

        // 2. Create View
        $createContent = file_get_contents(resource_path('views/cns/goods-receipt/create.blade.php'));
        $this->assertStringContainsString('FORM PENERIMAAN BARANG (GOODS RECEIPT)', $createContent);
        $this->assertStringContainsString('po_no', $createContent);
        $this->assertStringContainsString('delivery_note_no', $createContent);
        $this->assertStringContainsString('itemsTable', $createContent);
        $this->assertStringContainsString('Simpan sebagai DRAFT', $createContent);

        // 3. Detail View
        $detailContent = file_get_contents(resource_path('views/cns/goods-receipt/detail.blade.php'));
        $this->assertStringContainsString('DETAIL GOODS RECEIPT', $detailContent);
        $this->assertStringContainsString('modalVoid', $detailContent);
        $this->assertStringContainsString('cns-procurement-goods-receipt.print', $detailContent);

        // 4. Print View
        $printContent = file_get_contents(resource_path('views/cns/goods-receipt/print.blade.php'));
        $this->assertStringContainsString('BUKTI PENERIMAAN BARANG (GOODS RECEIPT SLIP)', $printContent);
        $this->assertStringContainsString('PT. GREEN ENERGI UTAMA', $printContent);
        $this->assertStringContainsString('sign-table', $printContent);

        // 5. Non-UCO Inventory View
        $nonUcoView = file_get_contents(resource_path('views/cns/inventory-non-uco/index.blade.php'));
        $this->assertStringContainsString('INVENTORY NON-UCO (MUTASI STOK MASUK & KELUAR)', $nonUcoView);
        $this->assertStringContainsString('NonUcoGrid', $nonUcoView);
        $this->assertStringContainsString('modalManualMutation', $nonUcoView);
    }

    /** @test */
    public function migrations_and_menu_entries_are_configured()
    {
        $nonUcoMigration = file_get_contents(database_path('migrations/cns/2026_11_06_223000_create_cns_inventory_non_uco_table.php'));
        $this->assertStringContainsString("Schema::create('cns_inventory_non_uco'", $nonUcoMigration);
        $this->assertStringContainsString("'item_name'", $nonUcoMigration);
        $this->assertStringContainsString("'category'", $nonUcoMigration);
        $this->assertStringContainsString("'unit'", $nonUcoMigration);

        $grMigration = file_get_contents(database_path('migrations/cns/2026_11_06_223100_create_cns_goods_receipts_tables.php'));
        $this->assertStringContainsString("Schema::create('cns_goods_receipts'", $grMigration);
        $this->assertStringContainsString("Schema::create('cns_goods_receipt_details'", $grMigration);

        $menuMigration = file_get_contents(database_path('migrations/cns/2026_11_06_223200_add_cns_goods_receipt_and_update_inventory_menu.php'));
        $this->assertStringContainsString('SI00337', $menuMigration);
        $this->assertStringContainsString('Goods Receipt', $menuMigration);
        $this->assertStringContainsString('Inventory Non-UCO', $menuMigration);

        $menuData = file_get_contents(database_path('seeds/data/menu_lists_cns.php'));
        $this->assertStringContainsString("'SI00337' => ['Goods Receipt', 'cns-procurement-goods-receipt'", $menuData);
        $this->assertStringContainsString("'SI00336' => ['Inventory Non-UCO', 'cns-inventory-non-uco'", $menuData);
    }

    /** @test */
    public function service_classes_are_instantiable()
    {
        $invService = new NonUcoInventoryService();
        $this->assertInstanceOf(NonUcoInventoryService::class, $invService);

        $grService = new GoodsReceiptService($invService);
        $this->assertInstanceOf(GoodsReceiptService::class, $grService);
    }
}
