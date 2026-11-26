<?php

namespace Tests\Unit\CNS;

use App\CnsGoodsReceipt;
use App\CnsGoodsReceiptDetail;
use App\Http\Controllers\CNS\GoodsReceiptController;
use App\Services\CNS\GoodsReceiptService;
use App\Services\CNS\NonUcoInventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Mockery;
use ReflectionMethod;
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
        $this->assertStringContainsString('Goods Receipt (Penerimaan Barang Non-UCO)', $indexContent);
        $this->assertStringContainsString('GRGrid', $indexContent);
        $this->assertStringContainsString('filterStatus', $indexContent);
        $this->assertStringContainsString('cns-procurement-goods-receipt.create', $indexContent);
        $this->assertStringContainsString('approveSelectedGR', $indexContent);
        $this->assertStringContainsString('btnApproveGR', $indexContent);
        $this->assertTrue(strpos($indexContent, 'swal') !== false || strpos($indexContent, 'dhtmlx.confirm') !== false);

        // 2. Create View
        $createContent = file_get_contents(resource_path('views/cns/goods-receipt/create.blade.php'));
        $this->assertStringContainsString('Form Penerimaan Barang (Goods Receipt)', $createContent);
        $this->assertStringContainsString('po_no', $createContent);
        $this->assertStringContainsString('delivery_note_no', $createContent);
        $this->assertStringContainsString('itemsTable', $createContent);
        $this->assertStringContainsString('Simpan sebagai DRAFT', $createContent);

        // 3. Detail View
        $detailContent = file_get_contents(resource_path('views/cns/goods-receipt/detail.blade.php'));
        $this->assertStringContainsString('Detail Goods Receipt', $detailContent);
        $this->assertStringContainsString('modalVoid', $detailContent);
        $this->assertStringContainsString('cns-procurement-goods-receipt.print', $detailContent);

        // 4. Print View
        $printContent = file_get_contents(resource_path('views/cns/goods-receipt/print.blade.php'));
        $this->assertStringContainsString('BUKTI PENERIMAAN BARANG (GOODS RECEIPT SLIP)', $printContent);
        $this->assertStringContainsString('PT. GREEN ENERGI UTAMA', $printContent);
        $this->assertStringContainsString('sign-table', $printContent);

        // 5. Non-UCO Inventory View
        $nonUcoView = file_get_contents(resource_path('views/cns/inventory-non-uco/index.blade.php'));
        $this->assertStringContainsString('Inventory Non-UCO', $nonUcoView);
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
        $this->assertStringContainsString("'SI01075' => ['Goods Receipt', 'cns-procurement-goods-receipt'", $menuData);
        $this->assertStringContainsString("'SI00332' => ['Inventory Non-UCO', 'cns-inventory-non-uco'", $menuData);
    }

    /** @test */
    public function service_classes_are_instantiable()
    {
        $invService = new NonUcoInventoryService();
        $this->assertInstanceOf(NonUcoInventoryService::class, $invService);

        $grService = new GoodsReceiptService($invService);
        $this->assertInstanceOf(GoodsReceiptService::class, $grService);
    }

    /** @test */
    public function flexibag_serial_number_is_supported_across_gr_layers()
    {
        $detailModel = new \App\CnsGoodsReceiptDetail();
        $this->assertContains('serial_number', $detailModel->getFillable());

        $migration = file_get_contents(database_path('migrations/cns/2026_11_19_203500_add_serial_number_to_cns_goods_receipt_details_table.php'));
        $this->assertStringContainsString("Schema::table('cns_goods_receipt_details'", $migration);
        $this->assertStringContainsString("'serial_number'", $migration);

        $controller = file_get_contents(app_path('Http/Controllers/CNS/GoodsReceiptController.php'));
        $this->assertStringContainsString('items.*.serial_number', $controller);
        $this->assertStringContainsString("stripos(\$itemName, 'flexibag')", $controller);

        $service = file_get_contents(app_path('Services/CNS/GoodsReceiptService.php'));
        $this->assertStringContainsString("'serial_number'", $service);
        $this->assertStringContainsString('SN: ', $service);

        $createView = file_get_contents(resource_path('views/cns/goods-receipt/create.blade.php'));
        $this->assertStringContainsString('serial-number-input', $createView);
        $this->assertStringContainsString('sn-box-input', $createView);
        $this->assertStringContainsString('renderSerialBoxes', $createView);
        $this->assertStringContainsString('/flexibag/i', $createView);

        $this->assertStringContainsString('count($parsed) !== $expectedQty', $controller);
        $this->assertStringContainsString('CnsKemasanSerialNumber::whereIn', $controller);

        $detailView = file_get_contents(resource_path('views/cns/goods-receipt/detail.blade.php'));
        $this->assertStringContainsString('Serial Number', $detailView);
        $this->assertStringContainsString('$item->serial_number', $detailView);

        $printView = file_get_contents(resource_path('views/cns/goods-receipt/print.blade.php'));
        $this->assertStringContainsString('Serial Number', $printView);
        $this->assertStringContainsString('$item->serial_number', $printView);
    }

    /** @test */
    public function flexibag_serial_photos_model_migration_and_views_are_configured()
    {
        $detailModel = new CnsGoodsReceiptDetail();
        $this->assertContains('serial_photos', $detailModel->getFillable());
        $casts = $detailModel->getCasts();
        $this->assertArrayHasKey('serial_photos', $casts);
        $this->assertSame('array', $casts['serial_photos']);

        $detailModel->serial_photos = [
            'uploads/goods-receipt/flexibag/fb_1.jpg',
            'uploads/goods-receipt/flexibag/fb_2.png',
        ];
        $this->assertIsArray($detailModel->serial_photos);
        $this->assertCount(2, $detailModel->serial_photos);

        $migrationPath = database_path('migrations/cns/2026_11_25_000001_add_serial_photos_to_cns_goods_receipt_details_table.php');
        $this->assertFileExists($migrationPath);
        $migration = file_get_contents($migrationPath);
        $this->assertStringContainsString("Schema::table('cns_goods_receipt_details'", $migration);
        $this->assertStringContainsString("Schema::hasColumn('cns_goods_receipt_details', 'serial_photos')", $migration);
        $this->assertStringContainsString("'serial_photos'", $migration);
        $this->assertStringContainsString("->after('serial_number')", $migration);

        $createView = file_get_contents(resource_path('views/cns/goods-receipt/create.blade.php'));
        $this->assertStringContainsString('sn-photo-input', $createView);
        $this->assertStringContainsString('sn-photo-preview', $createView);
        $this->assertStringContainsString('serial_photos', $createView);
        $this->assertStringContainsString('sn-unit-box', $createView);

        $detailView = file_get_contents(resource_path('views/cns/goods-receipt/detail.blade.php'));
        $this->assertStringContainsString('serial_photos', $detailView);
        $this->assertStringContainsString('cnsOpenDocPreview', $detailView);

        $service = file_get_contents(app_path('Services/CNS/GoodsReceiptService.php'));
        $this->assertStringContainsString("'serial_photos'", $service);
        $this->assertStringContainsString('storeFlexibagPhotos', $service);
    }

    /** @test */
    public function service_stores_ordered_flexibag_photos_to_public_uploads_directory()
    {
        $service = new GoodsReceiptService(new NonUcoInventoryService());
        $storeMethod = new ReflectionMethod(GoodsReceiptService::class, 'storeFlexibagPhotos');
        $storeMethod->setAccessible(true);

        $file1 = UploadedFile::fake()->image('fb1.jpg', 100, 100);
        $file2 = UploadedFile::fake()->image('fb2.png', 100, 100);

        $storedPaths = [];
        try {
            // Pass out-of-order keys to verify 1-to-1 ordering by unit index is preserved
            $storedPaths = $storeMethod->invoke($service, [
                1 => $file2,
                0 => $file1,
            ]);

            $this->assertCount(2, $storedPaths);
            $this->assertStringStartsWith('uploads/goods-receipt/flexibag/fb_', $storedPaths[0]);
            $this->assertStringStartsWith('uploads/goods-receipt/flexibag/fb_', $storedPaths[1]);
            $this->assertStringEndsWith('_fb1.jpg', $storedPaths[0]);
            $this->assertStringEndsWith('_fb2.png', $storedPaths[1]);
            $this->assertFileExists(public_path($storedPaths[0]));
            $this->assertFileExists(public_path($storedPaths[1]));

            // Also verify resolveItemSerialPhotos handles both controller structure and existing paths
            $resolveMethod = new ReflectionMethod(GoodsReceiptService::class, 'resolveItemSerialPhotos');
            $resolveMethod->setAccessible(true);

            $file3 = UploadedFile::fake()->image('fb3.jpg', 80, 80);
            $resolved = $resolveMethod->invoke($service, 0, ['item_name' => 'Flexibag'], [
                0 => ['serial_photos' => [0 => $file3]],
            ]);
            $this->assertCount(1, $resolved);
            $this->assertStringEndsWith('_fb3.jpg', $resolved[0]);
            $this->assertFileExists(public_path($resolved[0]));
            $storedPaths[] = $resolved[0];

            $preserved = $resolveMethod->invoke($service, 0, [
                'serial_photos' => ['uploads/goods-receipt/flexibag/existing.jpg'],
            ], []);
            $this->assertSame(['uploads/goods-receipt/flexibag/existing.jpg'], $preserved);
        } finally {
            foreach ($storedPaths as $relPath) {
                $fullPath = public_path($relPath);
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }
            }
        }
    }

    /** @test */
    public function controller_store_rejects_flexibag_when_photos_are_missing_or_invalid()
    {
        $mockService = Mockery::mock(GoodsReceiptService::class);
        $mockService->shouldNotReceive('createDraft');
        $controller = new GoodsReceiptController($mockService);

        // 1. qty_received = 2, 2 SNs, only 1 photo uploaded -> redirect back with error on serial_photos
        $requestMissing = Request::create('/cns-procurement-goods-receipt-store', 'POST', [
            'po_no'        => 'PO/CNS/202611/0001',
            'receipt_date' => '2026-11-25',
            'items'        => [
                [
                    'item_name'     => 'Flexibag 24000L',
                    'qty_ordered'   => 2,
                    'qty_received'  => 2,
                    'serial_number' => "FB-001\nFB-002",
                ],
            ],
        ], [], [
            'items' => [
                [
                    'serial_photos' => [
                        0 => UploadedFile::fake()->image('fb1.jpg'),
                    ],
                ],
            ],
        ]);
        $requestMissing->setLaravelSession($this->app['session.store']);
        $this->app->instance('request', $requestMissing);

        $responseMissing = $controller->store($requestMissing);
        $this->assertInstanceOf(RedirectResponse::class, $responseMissing);
        $errors = $responseMissing->getSession()->get('errors');
        $this->assertNotNull($errors);
        $this->assertTrue($errors->has('items.0.serial_photos'));

        // 2. Non-image file (PDF) for flexibag photo -> ValidationException
        $requestInvalidMime = Request::create('/cns-procurement-goods-receipt-store', 'POST', [
            'po_no'        => 'PO/CNS/202611/0001',
            'receipt_date' => '2026-11-25',
            'items'        => [
                [
                    'item_name'     => 'Flexibag 24000L',
                    'qty_ordered'   => 1,
                    'qty_received'  => 1,
                    'serial_number' => 'FB-001',
                ],
            ],
        ], [], [
            'items' => [
                [
                    'serial_photos' => [
                        0 => UploadedFile::fake()->create('not-an-image.pdf', 100, 'application/pdf'),
                    ],
                ],
            ],
        ]);
        $requestInvalidMime->setLaravelSession($this->app['session.store']);
        $this->app->instance('request', $requestInvalidMime);

        $this->expectException(ValidationException::class);
        $controller->store($requestInvalidMime);
    }

    /** @test */
    public function controller_store_forwards_valid_flexibag_photos_to_service()
    {
        $mockSchema = Mockery::mock();
        $mockSchema->shouldReceive('hasTable')
            ->with('cns_kemasan_serial_numbers')
            ->once()
            ->andReturn(false);

        $mockConn = Mockery::mock();
        $mockConn->shouldReceive('getSchemaBuilder')
            ->once()
            ->andReturn($mockSchema);

        DB::shouldReceive('connection')
            ->once()
            ->andReturn($mockConn);

        $fakeGr = new CnsGoodsReceipt(['gr_no' => 'GR/CNS/202611/0099']);

        $mockService = Mockery::mock(GoodsReceiptService::class);
        $mockService->shouldReceive('createDraft')
            ->once()
            ->withArgs(function ($data, $attachment, $actor, $itemFiles) {
                return isset($data['po_no']) && $data['po_no'] === 'PO/CNS/202611/0001'
                    && isset($itemFiles[0]['serial_photos'])
                    && count($itemFiles[0]['serial_photos']) === 2
                    && $itemFiles[0]['serial_photos'][0] instanceof UploadedFile
                    && $itemFiles[0]['serial_photos'][1] instanceof UploadedFile;
            })
            ->andReturn($fakeGr);

        $controller = new GoodsReceiptController($mockService);

        $request = Request::create('/cns-procurement-goods-receipt-store', 'POST', [
            'po_no'        => 'PO/CNS/202611/0001',
            'receipt_date' => '2026-11-25',
            'items'        => [
                [
                    'item_name'     => 'Flexibag 24000L',
                    'qty_ordered'   => 2,
                    'qty_received'  => 2,
                    'serial_number' => "FB-101\nFB-102",
                ],
            ],
        ], [], [
            'items' => [
                [
                    'serial_photos' => [
                        0 => UploadedFile::fake()->image('fb1.jpg'),
                        1 => UploadedFile::fake()->image('fb2.png'),
                    ],
                ],
            ],
        ]);
        $request->setLaravelSession($this->app['session.store']);
        $this->app->instance('request', $request);

        $response = $controller->store($request);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('GR/CNS/202611/0099', (string) $response->getSession()->get('success'));
    }

    /** @test */
    public function controller_store_allows_non_flexibag_items_without_photos()
    {
        $fakeGr = new CnsGoodsReceipt(['gr_no' => 'GR/CNS/202611/0100']);

        $mockService = Mockery::mock(GoodsReceiptService::class);
        $mockService->shouldReceive('createDraft')
            ->once()
            ->andReturn($fakeGr);

        $controller = new GoodsReceiptController($mockService);

        $request = Request::create('/cns-procurement-goods-receipt-store', 'POST', [
            'po_no'        => 'PO/CNS/202611/0002',
            'receipt_date' => '2026-11-25',
            'items'        => [
                [
                    'item_name'    => 'Drum Steel 200L',
                    'qty_ordered'  => 5,
                    'qty_received' => 5,
                ],
            ],
        ]);
        $request->setLaravelSession($this->app['session.store']);
        $this->app->instance('request', $request);

        $response = $controller->store($request);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('GR/CNS/202611/0100', (string) $response->getSession()->get('success'));
    }
}
