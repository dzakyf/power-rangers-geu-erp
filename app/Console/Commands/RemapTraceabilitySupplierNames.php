<?php

namespace App\Console\Commands;

use App\Services\Traceability\TraceabilityNameResolver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemapTraceabilitySupplierNames extends Command
{
    protected $signature = 'traceability:remap-supplier-names
        {--name-like=%supplier% : Name pattern to remap}
        {--from= : Filter delivery_date >= YYYY-MM-DD}
        {--to= : Filter delivery_date <= YYYY-MM-DD}
        {--dry-run : Report only, do not write}
        {--chunk=200 : Update chunk size}';

    protected $description = 'Remap supplier/test-like POO names to real suppliers that released a surat jalan';

    public function handle()
    {
        ini_set('memory_limit', '1024M');

        $like = (string) $this->option('name-like');
        $from = $this->option('from');
        $to = $this->option('to');
        $dry = (bool) $this->option('dry-run');

        $query = DB::table('traceability_external_poo_fragments as f')
            ->join('traceability_external_deliveries as d', 'd.id', '=', 'f.traceability_external_delivery_id')
            ->where(function ($q) use ($like) {
                $q->where('f.poo_name', 'like', $like)
                  ->orWhere('d.supplier_name', 'like', $like);
            })
            ->when($from, function ($q) use ($from) { return $q->whereDate('d.delivery_date', '>=', $from); })
            ->when($to, function ($q) use ($to) { return $q->whereDate('d.delivery_date', '<=', $to); })
            ->orderBy('f.id')
            ->select(
                'f.id as fragment_id',
                'f.traceability_external_delivery_id as delivery_id',
                'f.poo_name',
                'f.poo_type',
                'f.city',
                'f.province',
                'f.site',
                'f.do_code',
                'f.quantity_kg',
                'f.price',
                'f.total_price',
                'd.supplier_name as delivery_name',
                'd.site as delivery_site'
            );

        $targets = $query->get();
        $this->info('Targets: ' . $targets->count());

        if ($targets->isEmpty()) {
            return 0;
        }

        $resolver = app(TraceabilityNameResolver::class);
        $usedNames = [];
        $updates = [];
        $skipped = 0;
        $chunkSize = max(1, (int) $this->option('chunk'));

        foreach ($targets as $target) {
            $candidate = $resolver->resolve([
                'id' => $target->fragment_id,
                'poo_type' => $target->poo_type,
                'city' => $target->city,
                'province' => $target->province,
                'site' => $target->delivery_site ?: $target->site,
            ], $usedNames);

            if (!$candidate) {
                $skipped++;
                $this->warn("  skip fragment {$target->fragment_id}: no SJ-backed candidate found");
                continue;
            }

            $usedNames[] = $candidate['name'];

            $fragmentUpdate = [
                'poo_name' => $candidate['name'],
                'poo_type' => $candidate['poo_type'],
                'city' => $candidate['city'],
                'province' => $candidate['province'],
                'full_address' => $candidate['full_address'],
                'lat' => $candidate['lat'],
                'lng' => $candidate['lng'],
                'phone' => $candidate['phone'],
                'account_no' => $candidate['account_no'],
                'account_name' => $candidate['account_name'],
                'updated_at' => now(),
            ];

            $updates[] = [
                'fragment_id' => $target->fragment_id,
                'delivery_id' => $target->delivery_id,
                'fragment' => $fragmentUpdate,
                'delivery' => [
                    'supplier_name' => $candidate['name'],
                    'updated_at' => now(),
                ],
            ];

            $this->line("  fragment {$target->fragment_id} ({$target->poo_name}) -> {$candidate['name']} [{$candidate['poo_type']}]");
        }

        if ($dry) {
            $this->info('DRY RUN: no data written.');
            $this->line('  candidates: ' . count($updates) . ' | skipped: ' . $skipped);
            return 0;
        }

        foreach (array_chunk($updates, $chunkSize) as $chunk) {
            DB::transaction(function () use ($chunk) {
                foreach ($chunk as $update) {
                    DB::table('traceability_external_poo_fragments')
                        ->where('id', $update['fragment_id'])
                        ->update($update['fragment']);

                    DB::table('traceability_external_deliveries')
                        ->where('id', $update['delivery_id'])
                        ->update($update['delivery']);
                }
            });
        }

        $remaining = DB::table('traceability_external_poo_fragments')
            ->where('poo_name', 'like', $like)
            ->count();

        $this->info('Remapped: ' . count($updates) . ' | skipped: ' . $skipped);
        $this->line('Remaining fragments with name like ' . $like . ': ' . $remaining);

        return 0;
    }
}
