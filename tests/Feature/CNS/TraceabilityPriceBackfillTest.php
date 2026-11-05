<?php

namespace Tests\Feature\CNS;

use App\Console\Commands\BackfillTraceabilityPrice;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use ReflectionMethod;
use Tests\TestCase;

class TraceabilityPriceBackfillTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function bulk_update_assigns_each_price_to_its_own_fragment(): void
    {
        $deliveryId = DB::table('traceability_external_deliveries')->insertGetId([
            'supplier_name' => 'Price Test Supplier',
            'delivery_date' => '2026-10-01',
            'total_quantity_kg' => 3,
            'api_reference' => 'TEST-PRICE-' . uniqid(),
            'status' => 'LOCKED',
        ]);

        $first = DB::table('traceability_external_poo_fragments')->insertGetId([
            'traceability_external_delivery_id' => $deliveryId,
            'fragment_sequence' => 1,
            'poo_name' => 'Price Test One',
            'quantity_kg' => 1,
            'unit' => 'Kg',
        ]);
        $second = DB::table('traceability_external_poo_fragments')->insertGetId([
            'traceability_external_delivery_id' => $deliveryId,
            'fragment_sequence' => 2,
            'poo_name' => 'Price Test Two',
            'quantity_kg' => 2,
            'unit' => 'Kg',
        ]);

        $command = new BackfillTraceabilityPrice();
        $method = new ReflectionMethod($command, 'flushUpdates');
        $method->setAccessible(true);

        $method->invoke($command, [
            ['id' => $first, 'total_price' => 111.11, 'price' => 11.11],
            ['id' => $second, 'total_price' => 222.22, 'price' => 22.22],
        ]);

        $rows = DB::table('traceability_external_poo_fragments')
            ->whereIn('id', [$first, $second])
            ->orderBy('id')
            ->get(['id', 'price', 'total_price'])
            ->keyBy('id');

        $this->assertSame('11.11', $rows[$first]->price);
        $this->assertSame('111.11', $rows[$first]->total_price);
        $this->assertSame('22.22', $rows[$second]->price);
        $this->assertSame('222.22', $rows[$second]->total_price);
    }
}
