<?php

namespace Tests\Feature\CNS;

use App\Services\ExternalApi\ApipiApiClient;
use App\Services\ExternalApi\ApipiAuthService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class TraceabilityBackfillPhotosTest extends TestCase
{
    use DatabaseTransactions;

    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dir = storage_path('app/testing/apipi-photos-' . uniqid());
        File::makeDirectory($this->dir, 0775, true);
        config(['services.apipi.cache_path' => $this->dir]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->dir);
        parent::tearDown();
    }

    private function delivery(array $attrs): int
    {
        return DB::table('traceability_external_deliveries')->insertGetId(array_merge([
            'supplier_name' => 'PT Test Supplier',
            'poo_type' => 'POO RETAIL',
            'city' => 'TEST CITY',
            'province' => 'TEST PROVINCE',
            'site' => 'TEST SITE',
            'delivery_date' => '2026-01-10',
            'total_quantity_kg' => 10,
            'api_reference' => 'GEU-SR-26-TST0001-123',
            'status' => 'LOCKED',
            'created_at' => now(),
            'updated_at' => now(),
        ], $attrs));
    }

    private function fragment(int $deliveryId, array $attrs = []): int
    {
        return DB::table('traceability_external_poo_fragments')->insertGetId(array_merge([
            'traceability_external_delivery_id' => $deliveryId,
            'fragment_sequence' => 1,
            'poo_name' => 'POO Test',
            'poo_type' => 'POO RETAIL',
            'quantity_kg' => 10,
            'unit' => 'Kg',
            'city' => 'TEST CITY',
            'province' => 'TEST PROVINCE',
            'site' => 'TEST SITE',
            'do_code' => 'GEU-SR-26-TST0001',
            'do_date' => '2026-01-10',
            'do_status' => 'DONE',
            'created_at' => now(),
            'updated_at' => now(),
        ], $attrs));
    }

    /** @test */
    public function it_backfills_from_cached_sj_details_without_calling_the_api()
    {
        $delivery = $this->delivery(['api_reference' => 'GEU-SR-26-TST0001-123']);
        $fragment = $this->fragment($delivery, ['do_code' => 'GEU-SR-26-TST0001']);

        $url = 'https://example.test/photo.jpg';
        file_put_contents($this->dir . '/surat_jalan_details.ndjson', json_encode([
            'id' => 9001,
            'kode' => 'GEU-SR-26-TST0001',
            'tgl' => '2026-01-10',
            'detail' => [
                'header' => ['id' => 9001, 'kode' => 'GEU-SR-26-TST0001', 'tgl' => '2026-01-10'],
                'details' => [
                    ['id' => 501, 'supplier_name' => 'PT Test Supplier', 'status' => 'done', 'foto' => null, '_first_photo' => $url],
                ],
            ],
        ]) . "\n");

        $failing = new class($this->createMock(ApipiAuthService::class)) extends ApipiApiClient {
            public function suratJalanDetail($id): array { throw new \RuntimeException('should not call api'); }
            public function detailPhotos($detailId): array { throw new \RuntimeException('should not call api'); }
        };
        $this->app->instance(ApipiApiClient::class, $failing);

        Artisan::call('traceability:backfill-photos', ['--delivery-id' => $delivery]);

        $d = DB::table('traceability_external_deliveries')->where('id', $delivery)->first();
        $f = DB::table('traceability_external_poo_fragments')->where('id', $fragment)->first();

        $this->assertSame([$url], json_decode($d->photos, true));
        $this->assertSame($url, $f->oil_documentation);
    }

    /** @test */
    public function it_falls_back_to_live_api_and_stores_all_photos_in_delivery()
    {
        $delivery = $this->delivery(['api_reference' => 'GEU-SR-26-TST0002-456']);
        $fragment = $this->fragment($delivery, ['do_code' => 'GEU-SR-26-TST0002']);

        // No cache entry for this SJ.
        file_put_contents($this->dir . '/surat_jalan_details.ndjson', '');

        $photos = [
            ['id' => 1, 'file_path' => 'https://example.test/a.jpg'],
            ['id' => 2, 'file_path' => 'https://example.test/b.jpg'],
            ['id' => 3, 'file_path' => 'https://example.test/c.jpg'],
        ];

        $fake = new class($this->createMock(ApipiAuthService::class), $photos) extends ApipiApiClient {
            private $photos;
            public function __construct($auth, $photos) { parent::__construct($auth); $this->photos = $photos; }
            public function suratJalan($page = 1, $limit = 100, $status = null, $from = null, $to = null): array
            {
                return ['data' => [['id' => 9100, 'kode' => 'GEU-SR-26-TST0002', 'tgl' => '2026-01-10']], 'pagination' => ['has_next' => false]];
            }
            public function suratJalanDetail($id): array
            {
                return ['header' => ['id' => 9100, 'kode' => 'GEU-SR-26-TST0002', 'tgl' => '2026-01-10'],
                        'details' => [['id' => 701, 'supplier_name' => 'PT Test Supplier', 'status' => 'done', 'foto' => null]]];
            }
            public function detailPhotos($detailId): array
            {
                return ['photos' => $this->photos];
            }
        };
        $this->app->instance(ApipiApiClient::class, $fake);

        Artisan::call('traceability:backfill-photos', ['--delivery-id' => $delivery, '--all-photos' => true]);

        $d = DB::table('traceability_external_deliveries')->where('id', $delivery)->first();
        $f = DB::table('traceability_external_poo_fragments')->where('id', $fragment)->first();

        $this->assertSame(array_column($photos, 'file_path'), json_decode($d->photos, true));
        $this->assertSame('https://example.test/a.jpg', $f->oil_documentation);
    }

    /** @test */
    public function dry_run_and_only_missing_do_not_overwrite_existing_photos()
    {
        $delivery = $this->delivery(['api_reference' => 'GEU-SR-26-TST0003-789', 'photos' => json_encode(['https://example.test/existing.jpg'])]);
        $fragment = $this->fragment($delivery, ['do_code' => 'GEU-SR-26-TST0003', 'oil_documentation' => 'https://example.test/existing.jpg']);

        file_put_contents($this->dir . '/surat_jalan_details.ndjson', json_encode([
            'id' => 9200, 'kode' => 'GEU-SR-26-TST0003', 'tgl' => '2026-01-10',
            'detail' => ['header' => ['id' => 9200, 'kode' => 'GEU-SR-26-TST0003'], 'details' => [['id' => 801, 'supplier_name' => 'PT Test Supplier', '_first_photo' => 'https://example.test/new.jpg']]],
        ]) . "\n");

        Artisan::call('traceability:backfill-photos', ['--delivery-id' => $delivery, '--dry-run' => true]);
        Artisan::call('traceability:backfill-photos', ['--delivery-id' => $delivery]);

        $d = DB::table('traceability_external_deliveries')->where('id', $delivery)->first();
        $f = DB::table('traceability_external_poo_fragments')->where('id', $fragment)->first();

        $this->assertSame(['https://example.test/existing.jpg'], json_decode($d->photos, true));
        $this->assertSame('https://example.test/existing.jpg', $f->oil_documentation);
    }
}
