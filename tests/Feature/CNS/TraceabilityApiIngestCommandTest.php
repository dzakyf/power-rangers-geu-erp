<?php

namespace Tests\Feature\CNS;

use App\Console\Commands\ApiIngestTraceability;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use ReflectionMethod;
use Tests\TestCase;

class TraceabilityApiIngestCommandTest extends TestCase
{
    /** @test */
    public function dry_run_skips_sj_when_details_are_null(): void
    {
        $dir = storage_path('app/testing/apipi-' . uniqid());

        try {
            File::makeDirectory($dir, 0775, true);

            file_put_contents($dir . '/suppliers.json', json_encode([
                ['id' => 1, 'name' => 'Supplier Test', 'jenis' => 'POO RETAIL'],
            ]));
            file_put_contents($dir . '/supplier_details.json', '[]');
            file_put_contents($dir . '/work_orders_lazy.json', '[]');
            file_put_contents($dir . '/surat_jalan_details.ndjson', json_encode([
                'id' => 999,
                'kode' => 'SJ-NULL-DETAIL',
                'tgl' => '2026-10-01',
                'qty_real_kg' => 100,
                'detail' => [
                    'header' => ['kode' => 'SJ-NULL-DETAIL', 'tgl' => '2026-10-01'],
                    'details' => null,
                ],
            ]) . "\n");

            config(['services.apipi.cache_path' => $dir]);

            $exit = Artisan::call('traceability:api-ingest', [
                '--from' => '2026-01-01',
                '--to' => '2026-12-31',
                '--dry-run' => true,
            ]);
            $output = Artisan::output();

            $this->assertSame(0, $exit);
            $this->assertStringContainsString('SJ missing details: 1', $output);
            $this->assertStringContainsString('skip SJ 999 SJ-NULL-DETAIL', $output);
            $this->assertStringContainsString('details is null/empty', $output);
            $this->assertStringContainsString('DRY RUN', $output);
        } finally {
            File::deleteDirectory($dir);
        }
    }

    /** @test */
    public function kg_allocation_is_proportional_and_totals_exactly(): void
    {
        $command = new ApiIngestTraceability();
        $method = new ReflectionMethod($command, 'allocateKg');
        $method->setAccessible(true);

        $valid = [
            ['weight' => 1.0],
            ['weight' => 3.0],
        ];

        $allocations = $method->invoke($command, 100.0, $valid, 4.0);

        $this->assertSame([25.0, 75.0], $allocations);
        $this->assertSame(100.0, array_sum($allocations));
    }
}
