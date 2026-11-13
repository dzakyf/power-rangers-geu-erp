<?php

namespace App\Console\Commands;

use App\Services\HCGA\DisciplinarySanctionService;
use Illuminate\Console\Command;

class ExpireDisciplinarySanctionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sanctions:auto-expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis memperbarui status Surat Peringatan (SP) yang telah melewati masa berlaku menjadi EXPIRED';

    /**
     * Execute the console command.
     *
     * @param DisciplinarySanctionService $service
     * @return int
     */
    public function handle(DisciplinarySanctionService $service)
    {
        $this->info('Memulai pengecekan kadaluarsa Surat Peringatan (SP)...');

        $updatedCount = $service->autoExpireSanctions();

        $this->info("Berhasil memperbarui {$updatedCount} Surat Peringatan menjadi EXPIRED.");

        return 0;
    }
}

