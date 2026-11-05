<?php

namespace Tests\Feature\CNS;

use App\Services\Traceability\TraceabilityApiIngestionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TraceabilityApiIngestionTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function quantity_comes_from_qty_real_kg_and_unit_is_kg(): void
    {
        $sj = 'TEST-TRACE-QTY-' . uniqid();
        $supplier = $this->supplier('SUP-QTY', 'Supplier Qty', 'POO RETAIL');

        $counts = app(TraceabilityApiIngestionService::class)->ingest(
            [$supplier],
            [$this->collection($sj, $supplier, 100.0)]
        );

        $delivery = DB::table('traceability_external_deliveries')
            ->where('api_reference', $sj . '-SUP-QTY')
            ->first();

        $this->assertNotNull($delivery);
        $this->assertSame(100.0, (float) $delivery->total_quantity_kg);

        $fragment = DB::table('traceability_external_poo_fragments')
            ->where('traceability_external_delivery_id', $delivery->id)
            ->first();

        $this->assertNotNull($fragment);
        $this->assertSame(100.0, (float) $fragment->quantity_kg);
        $this->assertSame('Kg', $fragment->unit);
    }

    /** @test */
    public function bulk_ingest_inserts_delivery_and_fragments(): void
    {
        $sj = 'TEST-TRACE-BULK-' . uniqid();
        $supplier = $this->supplier('SUP-BULK', 'Supplier Bulk', 'POO RETAIL');

        $counts = app(TraceabilityApiIngestionService::class)->ingest(
            [$supplier],
            [$this->collection($sj, $supplier, 50.0)],
            false,
            false,
            true
        );

        $delivery = DB::table('traceability_external_deliveries')
            ->where('api_reference', $sj . '-SUP-BULK')
            ->first();

        $this->assertNotNull($delivery);
        $this->assertSame(1, $counts['created']);

        $fragment = DB::table('traceability_external_poo_fragments')
            ->where('traceability_external_delivery_id', $delivery->id)
            ->first();

        $this->assertNotNull($fragment);
        $this->assertSame(50.0, (float) $fragment->quantity_kg);
        $this->assertSame('Kg', $fragment->unit);
    }

    /** @test */
    public function missing_qty_real_kg_is_zero_and_does_not_fall_back_to_qty_real(): void
    {
        $sj = 'TEST-TRACE-ZERO-' . uniqid();
        $supplier = $this->supplier('SUP-ZERO', 'Supplier Zero', 'POO RETAIL');
        $collection = $this->collection($sj, $supplier, 0.0);
        $collection['qty_real'] = 9999;

        app(TraceabilityApiIngestionService::class)->ingest([$supplier], [$collection]);

        $this->assertNull(
            DB::table('traceability_external_deliveries')->where('api_reference', $sj . '-SUP-ZERO')->first()
        );
    }

    /** @test */
    public function randomized_poo_names_only_use_suppliers_listed_in_surat_jalan(): void
    {
        $sj = 'TEST-TRACE-POOL-' . uniqid();
        $original = $this->supplier('SUP-POOL', 'Original Supplier', 'SUPPLIER');
        $sjSupplier = $this->supplier('SJ-POOL', 'SJ Pool Supplier', 'POO RETAIL');
        $apiOnly = $this->supplier('API-POOL', 'API Only Supplier', 'POO RETAIL');

        app(TraceabilityApiIngestionService::class)->ingest(
            [$original, $sjSupplier, $apiOnly],
            [
                $this->collection($sj, $original, 5000.0),
                $this->collection($sj . '-B', $sjSupplier, 10.0),
            ]
        );

        $delivery = DB::table('traceability_external_deliveries')
            ->where('api_reference', $sj . '-SUP-POOL')
            ->first();

        $this->assertNotNull($delivery);

        $names = DB::table('traceability_external_poo_fragments')
            ->where('traceability_external_delivery_id', $delivery->id)
            ->pluck('poo_name');

        $this->assertNotEmpty($names);
        foreach ($names as $name) {
            $this->assertStringStartsWith('SJ Pool Supplier', $name);
            $this->assertStringNotContainsString('API Only Supplier', $name);
        }
    }

    /** @test */
    public function single_fragment_supplier_delivery_is_remapped_to_non_supplier_poo(): void
    {
        $sj = 'TEST-TRACE-SUPPLIER-ONE-' . uniqid();
        $original = $this->supplier('SUP-ONE', 'Original Supplier One', 'SUPPLIER');
        $candidate = $this->supplier('SJ-ONE', 'SJ Candidate One', 'POO RETAIL');

        app(TraceabilityApiIngestionService::class)->ingest(
            [$original, $candidate],
            [
                $this->collection($sj, $original, 10.0),
                $this->collection($sj . '-C', $candidate, 10.0),
            ]
        );

        $delivery = DB::table('traceability_external_deliveries')
            ->where('api_reference', $sj . '-SUP-ONE')
            ->first();

        $this->assertNotNull($delivery);

        $fragment = DB::table('traceability_external_poo_fragments')
            ->where('traceability_external_delivery_id', $delivery->id)
            ->first();

        $this->assertNotNull($fragment);
        $this->assertNotSame('SUPPLIER', $fragment->poo_type);
        $this->assertSame('SJ Candidate One', $fragment->poo_name);
    }

    /** @test */
    public function site_and_location_fall_back_when_sj_does_not_provide_them(): void
    {
        $sj = 'TEST-TRACE-FALLBACK-' . uniqid();
        $supplier = $this->supplier('SUP-FB', 'Supplier Fallback', 'POO RETAIL');
        $collection = $this->collection($sj, $supplier, 10.0);
        $collection['gudang'] = null;

        app(TraceabilityApiIngestionService::class)->ingest([$supplier], [$collection]);

        $delivery = DB::table('traceability_external_deliveries')
            ->where('api_reference', $sj . '-SUP-FB')
            ->first();
        $fragment = DB::table('traceability_external_poo_fragments')
            ->where('traceability_external_delivery_id', $delivery->id)
            ->first();

        $this->assertSame('Gudang Test', $fragment->site);
        $this->assertSame('Kota Test', $fragment->city);
        $this->assertSame('Provinsi Test', $fragment->province);
    }

    /** @test */
    public function supplier_name_only_in_surat_jalan_is_not_skipped(): void
    {
        $sj = 'TEST-TRACE-NAMEONLY-' . uniqid();
        $supplierKey = 'NAME-' . substr(md5('Name Only Supplier'), 0, 12);
        $collection = [
            'supplier_id' => $supplierKey,
            'supplier_name' => 'Name Only Supplier',
            'supplier_alamat' => 'Jl. Name Only',
            'supplier_phone' => '08123456789',
            'supplier_gps' => '-7.1,112.1',
            'supplier_jenis' => 'POO RETAIL',
            'sj_kode' => $sj,
            'sj_date' => '2026-10-01',
            'qty_real_kg' => 25.0,
            'qty_real' => 25.0,
            'wo_code' => 'WO-NAME-1',
            'po_code' => 'PO-NAME-1',
            'plat' => 'B 1234 NAME',
            'driver' => 'Driver Name',
            'gudang' => 'Gudang Name',
            'pickup_id' => 1,
            'ttd' => null,
            'photo' => null,
        ];

        app(TraceabilityApiIngestionService::class)->ingest([], [$collection]);

        $delivery = DB::table('traceability_external_deliveries')
            ->where('api_reference', $sj . '-' . $supplierKey)
            ->first();

        $this->assertNotNull($delivery);
        $this->assertSame('Name Only Supplier', $delivery->supplier_name);

        $fragment = DB::table('traceability_external_poo_fragments')
            ->where('traceability_external_delivery_id', $delivery->id)
            ->first();

        $this->assertNotNull($fragment);
        $this->assertSame('Name Only Supplier', $fragment->poo_name);
        $this->assertSame('Kg', $fragment->unit);
    }

    private function supplier(string $id, string $name, string $jenis): array
    {
        return [
            'id' => $id,
            'name' => $name,
            'jenis' => $jenis,
            'kota_name' => 'Kota Test',
            'provinsi_name' => 'Provinsi Test',
            'gps' => '-7.1,112.1',
            'alamat' => 'Jl. ' . $name,
            'phone' => '08123456789',
            'nama_rek' => $name,
            'nomor_rek' => '123456789',
            'price' => 1000,
            'gudang_name' => 'Gudang Test',
            'active' => 1,
        ];
    }

    private function collection(string $sj, array $supplier, float $qtyKg): array
    {
        return [
            'supplier_id' => $supplier['id'],
            'supplier_name' => $supplier['name'],
            'supplier_alamat' => $supplier['alamat'],
            'supplier_phone' => $supplier['phone'],
            'supplier_gps' => $supplier['gps'],
            'supplier_jenis' => $supplier['jenis'],
            'sj_kode' => $sj,
            'sj_date' => '2026-10-01',
            'qty_real_kg' => $qtyKg,
            'qty_real' => $qtyKg,
            'wo_code' => 'WO-' . $sj,
            'po_code' => 'PO-' . $sj,
            'plat' => 'B 1234 TEST',
            'driver' => 'Driver Test',
            'gudang' => 'Gudang Test',
            'pickup_id' => 1,
            'ttd' => null,
            'photo' => null,
        ];
    }
}
