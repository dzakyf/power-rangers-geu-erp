<?php

namespace App\Console\Commands;

use App\MasterStockPrice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateCurrentPrice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:priceforstock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate current price for current stock';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $get = MasterStockPrice::orderBy('created_date', 'desc')->first();
        if (!$get) {
            Log::info("Nothing to generate");
            return;
        }

        if ($get->date_for == date("Y-m-d")) {
            Log::info("Date Exists");
            return;
        }

        MasterStockPrice::create([
            'location' => 'GEU-HO',
            'date_for' => date("Y-m-d"),
            'price' => $get->price,
            'created_date' => now(),
            'created_by' => 'auto_sys',
        ]);

        Log::info('success to generate ' . date("Y-m-d"));
    }
}
