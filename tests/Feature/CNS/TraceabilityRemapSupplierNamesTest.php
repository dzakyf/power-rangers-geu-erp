<?php

namespace Tests\Feature\CNS;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TraceabilityRemapSupplierNamesTest extends TestCase
{
    use DatabaseTransactions;

    private function delivery(array $attrs): int
    {
        return DB::table('traceability_external_deliveries')->insertGetId(array_merge([
            'supplier_name' => 'Supplier Test',
            'poo_type' => 'POO RETAIL',
            'city' => 'TEST CITY',
            'province' => 'TEST PROVINCE',
            'site' => 'TEST SITE',
            'delivery_date' => '2026-01-10',
            'total_quantity_kg' => 10,
            'api_reference' => 'TEST-REF-' . uniqid(),
            'status' => 'LOCKED',
            'created_at' => now(),
            'updated_at' => now(),
        ], $attrs));
    }

    private function fragment(int $deliveryId, array $attrs): int
    {
        return DB::table('traceability_external_poo_fragments')->insertGetId(array_merge([
            'traceability_external_delivery_id' => $deliveryId,
            'fragment_sequence' => 1,
            'poo_name' => 'Supplier Test',
            'poo_type' => 'POO RETAIL',
            'quantity_kg' => 10,
            'unit' => 'Kg',
            'city' => 'TEST CITY',
            'province' => 'TEST PROVINCE',
            'site' => 'TEST SITE',
            'do_code' => 'GEU-SR-26-TST0001',
            'do_date' => '2026-01-10',
            'do_status' => 'DONE',
            'price' => 1234,
            'total_price' => 12340,
            'created_at' => now(),
            'updated_at' => now(),
        ], $attrs));
    }

    /** @test */
    public function it_remaps_supplier_named_rows_to_a_real_sj_supplier_without_touching_cache()
    {
        $city = 'TESTCITY-' . strtoupper(substr(uniqid(), -6));
        $province = 'TESTPROV-' . strtoupper(substr(uniqid(), -6));
        $site = 'TESTSITE-' . strtoupper(substr(uniqid(), -6));

        $targetDelivery = $this->delivery([
            'supplier_name' => 'Supplier Test',
            'city' => $city,
            'province' => $province,
            'site' => $site,
        ]);
        $targetFragment = $this->fragment($targetDelivery, [
            'poo_name' => 'Supplier Test',
            'city' => $city,
            'province' => $province,
            'site' => $site,
            'do_code' => 'GEU-SR-26-TST0001',
        ]);

        // SJ-backed candidate.
        $sjDelivery = $this->delivery([
            'supplier_name' => 'Real SJ POO',
            'city' => $city,
            'province' => $province,
            'site' => $site,
            'api_reference' => 'GEU-SR-26-TST0002-999',
        ]);
        $this->fragment($sjDelivery, [
            'poo_name' => 'Real SJ POO',
            'city' => $city,
            'province' => $province,
            'site' => $site,
            'do_code' => 'GEU-SR-26-TST0002',
        ]);

        // Not SJ-backed, must never be selected even if it matches city/type.
        $nonSjDelivery = $this->delivery([
            'supplier_name' => 'Non SJ Supplier Rival',
            'city' => $city,
            'province' => $province,
            'site' => $site,
            'api_reference' => 'NO-SJ-' . uniqid(),
        ]);
        $this->fragment($nonSjDelivery, [
            'poo_name' => 'Non SJ Supplier Rival',
            'city' => $city,
            'province' => $province,
            'site' => $site,
            'do_code' => 'NO-SJ-CODE',
        ]);

        // Another SJ-backed row whose name still contains "supplier" must be excluded.
        $badSjDelivery = $this->delivery([
            'supplier_name' => 'Still Supplier Name',
            'city' => $city,
            'province' => $province,
            'site' => $site,
            'api_reference' => 'GEU-SR-26-TST0003-888',
        ]);
        $this->fragment($badSjDelivery, [
            'poo_name' => 'Still Supplier Name',
            'city' => $city,
            'province' => $province,
            'site' => $site,
            'do_code' => 'GEU-SR-26-TST0003',
        ]);

        $cachePath = config('services.apipi.cache_path') ?: storage_path('app/apipi/cache');
        $beforeHash = is_file($cachePath . '/suppliers.json') ? md5_file($cachePath . '/suppliers.json') : null;

        // Dry-run must not write.
        Artisan::call('traceability:remap-supplier-names', [
            '--name-like' => '%supplier%',
            '--dry-run' => true,
        ]);

        $this->assertSame('Supplier Test', DB::table('traceability_external_poo_fragments')->where('id', $targetFragment)->value('poo_name'));

        Artisan::call('traceability:remap-supplier-names', [
            '--name-like' => '%supplier%',
        ]);

        $fragment = DB::table('traceability_external_poo_fragments')->where('id', $targetFragment)->first();
        $delivery = DB::table('traceability_external_deliveries')->where('id', $targetDelivery)->first();

        $this->assertSame('Real SJ POO', $fragment->poo_name);
        $this->assertSame('Real SJ POO', $delivery->supplier_name);
        $this->assertSame('GEU-SR-26-TST0001', $fragment->do_code);
        $this->assertSame(1234.0, (float) $fragment->price);
        $this->assertSame(12340.0, (float) $fragment->total_price);

        $afterHash = is_file($cachePath . '/suppliers.json') ? md5_file($cachePath . '/suppliers.json') : null;
        $this->assertSame($beforeHash, $afterHash, 'suppliers.json must not be modified by the remap command.');
    }
}
