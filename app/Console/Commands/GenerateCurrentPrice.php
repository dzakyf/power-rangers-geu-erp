<?php

namespace App\Console\Commands;

use App\MasterStockPrice;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateCurrentPrice extends Command
{
    protected $signature = 'generate:priceforstock';
    protected $description = 'Generate current price for current stock';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $lastRow = MasterStockPrice::orderBy('date_for', 'desc')->first();

        if (!$lastRow) {
            Log::info("Nothing to generate");
            return;
        }

        $lastDate = $lastRow->date_for;
        $today = date("Y-m-d");

        if ($lastDate >= $today) {
            Log::info("Date Exists");
            return;
        }

        $cursor = Carbon::parse($lastDate)->addDay();
        $end = Carbon::parse($today);

        while ($cursor <= $end) {
            $dateStr = $cursor->format("Y-m-d");

            $exists = MasterStockPrice::where('date_for', $dateStr)->exists();
            if (!$exists) {
                MasterStockPrice::create([
                    'location' => 'GEU-HO',
                    'date_for' => $dateStr,
                    'price' => $lastRow->price,
                    'created_date' => now(),
                    'created_by' => 'auto_sys',
                ]);
                Log::info("Generated price for {$dateStr}");
            }

            $cursor->addDay();
        }

        Log::info('Backfill complete up to ' . $today);
    }
}
