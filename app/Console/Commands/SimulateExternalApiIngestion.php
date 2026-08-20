<?php

namespace App\Console\Commands;

use App\Services\ISCCTraceabilityService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SimulateExternalApiIngestion extends Command
{
    protected $signature = 'traceability:simulate-api
        {--supplier= : Supplier name (default: PT Demo Supplier)}
        {--qty= : Total quantity in Kg}
        {--type= : Supplier type (restaurant, hotel, irt, kafe, warung, lembaga, sppg, pabrik; default pabrik)}
        {--city= : City/Regency}
        {--province= : Province}
        {--date= : Delivery date (Y-m-d, default: today)}
        {--api-ref= : API reference (default: auto-generated)}';

    protected $description = 'Simulate external API delivery ingestion and fragment into synthetic POOs';

    public function handle()
    {
        $supplier = $this->option('supplier') ?? 'PT Demo Supplier';
        $qty = (float) ($this->option('qty') ?? 5000);
        $type = strtolower($this->option('type') ?? 'pabrik');
        $city = $this->option('city') ?? 'Jakarta Selatan';
        $province = $this->option('province') ?? 'DKI Jakarta';
        $date = $this->option('date') ?? now()->format('Y-m-d');
        $apiRef = $this->option('api-ref') ?? 'SIM-' . strtoupper(Str::random(8));

        $service = app(ISCCTraceabilityService::class);
        $ranges = $service::SPLIT_RANGES;
        if (!isset($ranges[$type])) {
            $this->error("Unknown type '{$type}'. Allowed: " . implode(', ', array_keys($ranges)));
            return 1;
        }

        if ($qty <= 0) {
            $this->error('Quantity must be greater than 0.');
            return 1;
        }

        $existing = DB::table('traceability_external_deliveries')
            ->where('api_reference', $apiRef)
            ->first();

        if ($existing) {
            $this->error("API reference '{$apiRef}' already exists (delivery #{$existing->id}). Duplicate ingestion rejected.");
            return 1;
        }

        $deliveryId = DB::table('traceability_external_deliveries')->insertGetId([
            'supplier_name' => $supplier,
            'poo_type' => $type,
            'city' => $city,
            'province' => $province,
            'delivery_date' => $date,
            'total_quantity_kg' => $qty,
            'api_reference' => $apiRef,
            'status' => 'PENDING',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->info("Delivery #{$deliveryId} ingested (ref: {$apiRef}, {$qty} kg, {$type}, {$city}).");

        try {
            $fragments = $service->TraceabilityFragmentationService($deliveryId);
        } catch (\Throwable $e) {
            $this->error("Fragmentation failed: {$e->getMessage()}");
            return 1;
        }

        $total = array_sum(array_column($fragments, 'quantity_kg'));
        $this->info("Fragmented into " . count($fragments) . " synthetic POOs (sum: {$total} kg).");
        $this->info("First fragment: {$fragments[0]['poo_name']} ({$fragments[0]['quantity_kg']} kg)");

        return 0;
    }
}
