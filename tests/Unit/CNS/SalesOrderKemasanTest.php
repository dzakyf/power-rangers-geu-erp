<?php

namespace Tests\Unit\CNS;

use App\CnsMasterKemasan;
use App\Http\Controllers\CNS\MasterKemasanController;
use App\Http\Controllers\CNS\SalesOrderController;
use App\SalesOrderCNS;
use App\SalesOrderCNSDtl;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use ReflectionMethod;
use Tests\TestCase;

class SalesOrderKemasanTest extends TestCase
{
    protected function invokeProtected(object $target, string $method, array $args)
    {
        $ref = new ReflectionMethod($target, $method);
        $ref->setAccessible(true);

        return $ref->invokeArgs($target, $args);
    }

    /** @test */
    public function master_kemasan_validate_payload_handles_optional_price()
    {
        $controller = new MasterKemasanController();

        $withPrice = $this->invokeProtected($controller, 'validatePayload', [
            new Request([
                'Name'      => 'Drum 200L',
                'Volume'    => 200,
                'Is_Ampas'  => 0,
                'Max_Stock' => 100,
                'Unit'      => 'Pcs',
                'Price'     => 75000,
            ]),
        ]);
        $this->assertSame('Pcs', $withPrice['Unit']);
        $this->assertSame(75000.0, $withPrice['Price']);

        $ampasWithoutPrice = $this->invokeProtected($controller, 'validatePayload', [
            new Request([
                'Name'     => 'Ampas Kering',
                'Volume'   => 0,
                'Is_Ampas' => 1,
            ]),
        ]);
        $this->assertSame('', $ampasWithoutPrice['Unit']);
        $this->assertSame(0.0, $ampasWithoutPrice['Price']);

        $this->expectException(ValidationException::class);
        $this->invokeProtected($controller, 'validatePayload', [
            new Request([
                'Name'      => 'Drum 200L',
                'Volume'    => 200,
                'Is_Ampas'  => 0,
                'Max_Stock' => 100,
                'Price'     => -50,
            ]),
        ]);
    }

    /** @test */
    public function sales_order_item_and_header_builders_parse_rows_and_compute_totals()
    {
        $controller = new SalesOrderController();

        $request = new Request([
            'soDate'         => '2026-11-12',
            'buyerPic'       => 'Budi',
            'buyerKota'      => 'Malang',
            'buyerProvinsi'  => 'Jawa Timur',
            'buyerPhone'     => '08123456789',
            'site'           => 'Malang',
            'siteAddress'    => 'Jl. Raya 1',
            'paymentTerm'    => 'Transfer',
            'description2'   => 'Jerigen 20L',
            'qty2'           => 3,
            'item_unit2'     => 'Pcs',
            'unit2'          => 15000,
            'description1'   => 'Drum 200L',
            'qty1'           => 2,
            'item_unit1'     => 'Drum',
            'unit1'          => 75000,
            'description3'   => '',
            'qty3'           => 1,
            'unit3'          => 5000,
            'taxAmount'      => 5000,
            'shippingAmount' => 10000,
            'otherAmount'    => 2500,
            'remarks'        => 'Catatan',
        ]);

        [$items, $subtotal] = $this->invokeProtected($controller, 'parseSubmittedItems', [$request, true]);

        $this->assertCount(2, $items);
        $this->assertSame('Drum 200L', $items[0]['description']);
        $this->assertSame(1, $items[0]['sort_order']);
        $this->assertSame('Drum', $items[0]['unit']);
        $this->assertSame(150000.0, $items[0]['total']);
        $this->assertSame('Jerigen 20L', $items[1]['description']);
        $this->assertSame(2, $items[1]['sort_order']);
        $this->assertSame('Pcs', $items[1]['unit']);
        $this->assertSame(45000.0, $items[1]['total']);
        $this->assertSame(195000.0, $subtotal);

        [$itemsWithoutUnitCol] = $this->invokeProtected($controller, 'parseSubmittedItems', [$request, false]);
        $this->assertArrayNotHasKey('unit', $itemsWithoutUnitCol[0]);

        $header = $this->invokeProtected($controller, 'buildHeaderPayload', [$request, '001/SO/XI/2026', $subtotal]);
        $this->assertSame('001/SO/XI/2026', $header['so_no']);
        $this->assertSame(195000.0, $header['subtotal']);
        $this->assertSame(212500.0, $header['total']);
    }

    /** @test */
    public function sales_order_new_and_detail_views_render_select2_kemasan_and_price_autofill()
    {
        $user = new User();
        $user->username = 'tester';
        $user->Location = 'Malang';
        $this->be($user);

        $kemasans = collect([
            new CnsMasterKemasan(['Kemasan_ID' => 1, 'Name' => 'Ampas Basah', 'Volume' => 0, 'Is_Ampas' => true, 'Unit' => 'Kg', 'Price' => 2000]),
            new CnsMasterKemasan(['Kemasan_ID' => 2, 'Name' => 'Drum', 'Volume' => 200, 'Is_Ampas' => false, 'Unit' => 'Pcs', 'Price' => 75000]),
        ]);

        $newHtml = view('cns.sales.input-order-new', [
            'socode'   => '001/SO/XI/2026',
            'kemasans' => $kemasans,
        ])->render();

        $this->assertStringContainsString('buildKemasanSelectHtml', $newHtml);
        $this->assertStringContainsString('$select.select2(', $newHtml);
        $this->assertStringContainsString('Ampas Basah', $newHtml);
        $this->assertStringContainsString('Drum', $newHtml);
        $this->assertStringContainsString('75000', $newHtml);
        $this->assertStringContainsString('item_unit${idx}', $newHtml);
        $this->assertStringContainsString('<th class="unit-col text-center">Unit</th>', $newHtml);
        $this->assertStringContainsString('colspan="5"', $newHtml);
        $this->assertStringContainsString('isAmpas ? rawName : `${rawName} ${item.Volume ?? 0}L`', $newHtml);
        $this->assertStringContainsString('cns-sales-order-new-submit', $newHtml);

        $salesOrder = (new SalesOrderCNS())->forceFill([
            'id'             => 42,
            'so_no'          => '001/SO/XI/2026',
            'so_date'        => '2026-11-12',
            'buyer_pic'      => 'Budi',
            'buyer_city'     => 'Malang',
            'buyer_province' => 'Jawa Timur',
            'buyer_phone'    => '08123',
            'site'           => 'Malang',
            'site_address'   => 'Jl. Raya',
            'payment_term'   => 'Transfer',
            'subtotal'       => 100000,
            'tax'            => 0,
            'shipping'       => 0,
            'other'          => 0,
            'total'          => 150000,
            'remarks'        => 'Test',
        ]);

        $details = collect([
            (new SalesOrderCNSDtl())->forceFill(['description' => 'Item Lama Custom', 'qty' => 2, 'unit' => 'Kg', 'unit_price' => 50000]),
        ]);

        $detailHtml = view('cns.sales.input-order-detail', [
            'salesOrder' => $salesOrder,
            'details'    => $details,
            'kemasans'   => $kemasans,
        ])->render();

        $this->assertStringContainsString('id="soId" value="42"', $detailHtml);
        $this->assertStringContainsString('id="totalAmount"', $detailHtml);
        $this->assertStringContainsString('id="subtotalDisplay">100,000.00<', $detailHtml);
        $this->assertStringContainsString('id="totalDisplay">Rp 150,000.00<', $detailHtml);
        $this->assertStringContainsString('var defaultItems =', $detailHtml);
        $this->assertStringContainsString('var masterKemasanOptions =', $detailHtml);
        $this->assertStringContainsString('var rowCount = 0;', $detailHtml);
        $this->assertStringNotContainsString('const defaultItems', $detailHtml);
        $this->assertStringNotContainsString('const masterKemasanOptions', $detailHtml);
        $this->assertStringNotContainsString('let rowCount', $detailHtml);
        $this->assertStringContainsString('Item Lama Custom', $detailHtml);
        $this->assertStringContainsString('"unit":"Kg"', $detailHtml);
        $this->assertStringContainsString('cns-sales-order-detail-submit', $detailHtml);
        $this->assertStringContainsString('$select.select2(', $detailHtml);
        $this->assertStringContainsString("find('option:selected').attr('data-unit')", $detailHtml);
    }

    /** @test */
    public function add_price_and_unit_migrations_exist_for_cns_master_kemasan()
    {
        $pricePath = database_path('migrations/cns/2026_11_12_000001_add_price_and_unit_to_cns_master_kemasan_table.php');
        $this->assertFileExists($pricePath);
        $priceContent = file_get_contents($pricePath);
        $this->assertStringContainsString("Schema::hasColumn('cns_master_kemasan', 'Price')", $priceContent);
        $this->assertStringContainsString("decimal('Price', 15, 2)", $priceContent);

        $unitPath = database_path('migrations/cns/2026_11_12_093355_cns_add_unit_to_cns_master_kemasan_table.php');
        $this->assertFileExists($unitPath);
        $unitContent = file_get_contents($unitPath);
        $this->assertStringContainsString("Schema::hasColumn('cns_master_kemasan', 'Unit')", $unitContent);
    }
}
