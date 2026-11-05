<?php

namespace App\Console\Commands;

use App\Services\ISCCTraceabilityService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportTraceabilityCsv extends Command
{
    protected $signature = 'traceability:import-csv
        {--dir=tables : Directory containing the exported CSV files}
        {--keep-existing : Keep existing traceability deliveries/fragments (default: purge first)}';

    protected $description = 'Import UCO pickup-app CSV exports into ISCC traceability (EXTERNAL) tables';

    protected $nonSupPoolByCity = [];
    protected $nonSupPoolByProvince = [];
    protected $supplierById = [];

    // BPS province codes present in the dataset
    const PROVINCES = [
        '12' => 'Sumatera Utara', '14' => 'Riau', '15' => 'Jambi', '16' => 'Sumatera Selatan',
        '18' => 'Lampung', '21' => 'Kepulauan Riau', '31' => 'DKI Jakarta', '32' => 'Jawa Barat',
        '33' => 'Jawa Tengah', '34' => 'DI Yogyakarta', '35' => 'Jawa Timur', '36' => 'Banten',
        '51' => 'Bali', '52' => 'Nusa Tenggara Barat', '53' => 'Nusa Tenggara Timur',
        '61' => 'Kalimantan Barat', '62' => 'Kalimantan Tengah', '63' => 'Kalimantan Selatan',
        '64' => 'Kalimantan Timur', '71' => 'Sulawesi Utara', '73' => 'Sulawesi Selatan',
        '74' => 'Sulawesi Tenggara', '82' => 'Maluku Utara',
    ];

    // BPS kabupaten/kota codes present in the dataset (validated against the export addresses)
    const CITIES = [
        '1212' => 'Kabupaten Deli Serdang', '1274' => 'Kota Binjai', '1275' => 'Kota Medan',
        '1471' => 'Kota Pekanbaru', '1571' => 'Kota Jambi', '1671' => 'Kota Palembang',
        '1805' => 'Kabupaten Lampung Tengah', '1871' => 'Kota Bandar Lampung', '2171' => 'Kota Batam',
        '3171' => 'Kota Jakarta Selatan', '3172' => 'Kota Jakarta Timur', '3173' => 'Kota Jakarta Pusat',
        '3174' => 'Kota Jakarta Barat', '3175' => 'Kota Jakarta Utara', '3201' => 'Kabupaten Bogor',
        '3202' => 'Kabupaten Sukabumi', '3203' => 'Kabupaten Cianjur', '3204' => 'Kabupaten Bandung',
        '3206' => 'Kabupaten Tasikmalaya', '3207' => 'Kabupaten Ciamis', '3208' => 'Kabupaten Kuningan',
        '3209' => 'Kabupaten Cirebon', '3210' => 'Kabupaten Majalengka', '3211' => 'Kabupaten Sumedang',
        '3212' => 'Kabupaten Indramayu', '3213' => 'Kabupaten Subang', '3214' => 'Kabupaten Purwakarta',
        '3215' => 'Kabupaten Karawang', '3216' => 'Kabupaten Bekasi', '3217' => 'Kabupaten Bandung Barat',
        '3218' => 'Kabupaten Pangandaran', '3271' => 'Kota Bogor', '3272' => 'Kota Sukabumi',
        '3273' => 'Kota Bandung', '3274' => 'Kota Cirebon', '3275' => 'Kota Bekasi', '3276' => 'Kota Depok',
        '3277' => 'Kota Cimahi', '3278' => 'Kota Tasikmalaya', '3279' => 'Kota Banjar',
        '3301' => 'Kabupaten Cilacap', '3302' => 'Kabupaten Banyumas', '3303' => 'Kabupaten Purbalingga',
        '3304' => 'Kabupaten Banjarnegara', '3305' => 'Kabupaten Kebumen', '3306' => 'Kabupaten Purworejo',
        '3307' => 'Kabupaten Wonosobo', '3308' => 'Kabupaten Magelang', '3309' => 'Kabupaten Boyolali',
        '3310' => 'Kabupaten Klaten', '3311' => 'Kabupaten Sukoharjo', '3312' => 'Kabupaten Wonogiri',
        '3313' => 'Kabupaten Karanganyar', '3314' => 'Kabupaten Sragen', '3315' => 'Kabupaten Grobogan',
        '3316' => 'Kabupaten Blora', '3318' => 'Kabupaten Pati', '3319' => 'Kabupaten Kudus',
        '3320' => 'Kabupaten Jepara', '3321' => 'Kabupaten Demak', '3322' => 'Kabupaten Semarang',
        '3323' => 'Kabupaten Temanggung', '3324' => 'Kabupaten Kendal', '3325' => 'Kabupaten Batang',
        '3326' => 'Kabupaten Pekalongan', '3327' => 'Kabupaten Pemalang', '3328' => 'Kabupaten Tegal',
        '3329' => 'Kabupaten Brebes', '3371' => 'Kota Magelang', '3372' => 'Kota Surakarta',
        '3373' => 'Kota Salatiga', '3374' => 'Kota Semarang', '3375' => 'Kota Pekalongan',
        '3376' => 'Kota Tegal', '3401' => 'Kabupaten Kulon Progo', '3402' => 'Kabupaten Bantul',
        '3403' => 'Kabupaten Gunungkidul', '3404' => 'Kabupaten Sleman', '3471' => 'Kota Yogyakarta',
        '3501' => 'Kabupaten Pacitan', '3502' => 'Kabupaten Ponorogo', '3503' => 'Kabupaten Trenggalek',
        '3504' => 'Kabupaten Tulungagung', '3505' => 'Kabupaten Blitar', '3506' => 'Kabupaten Kediri',
        '3507' => 'Kabupaten Malang', '3508' => 'Kabupaten Lumajang', '3509' => 'Kabupaten Jember',
        '3510' => 'Kabupaten Banyuwangi', '3511' => 'Kabupaten Bondowoso', '3512' => 'Kabupaten Situbondo',
        '3513' => 'Kabupaten Probolinggo', '3514' => 'Kabupaten Pasuruan', '3515' => 'Kabupaten Sidoarjo',
        '3516' => 'Kabupaten Mojokerto', '3517' => 'Kabupaten Jombang', '3518' => 'Kabupaten Nganjuk',
        '3519' => 'Kabupaten Madiun', '3520' => 'Kabupaten Magetan', '3521' => 'Kabupaten Ngawi',
        '3522' => 'Kabupaten Bojonegoro', '3523' => 'Kabupaten Tuban', '3524' => 'Kabupaten Lamongan',
        '3525' => 'Kabupaten Gresik', '3526' => 'Kabupaten Bangkalan', '3527' => 'Kabupaten Sampang',
        '3528' => 'Kabupaten Pamekasan', '3529' => 'Kabupaten Sumenep', '3571' => 'Kota Kediri',
        '3572' => 'Kota Blitar', '3573' => 'Kota Malang', '3574' => 'Kota Probolinggo',
        '3575' => 'Kota Pasuruan', '3576' => 'Kota Mojokerto', '3577' => 'Kota Madiun',
        '3578' => 'Kota Surabaya', '3579' => 'Kota Batu', '3602' => 'Kabupaten Lebak',
        '3603' => 'Kabupaten Tangerang', '3604' => 'Kabupaten Serang', '3671' => 'Kota Tangerang',
        '3672' => 'Kota Cilegon', '3673' => 'Kota Serang', '3674' => 'Kota Tangerang Selatan',
        '5101' => 'Kabupaten Jembrana', '5102' => 'Kabupaten Tabanan', '5103' => 'Kabupaten Badung',
        '5104' => 'Kabupaten Gianyar', '5105' => 'Kabupaten Klungkung', '5106' => 'Kabupaten Bangli',
        '5107' => 'Kabupaten Karangasem', '5108' => 'Kabupaten Buleleng', '5171' => 'Kota Denpasar',
        '5201' => 'Kabupaten Lombok Barat', '5204' => 'Kabupaten Sumbawa', '5207' => 'Kabupaten Sumbawa Barat',
        '5271' => 'Kota Mataram', '5371' => 'Kota Kupang', '6101' => 'Kabupaten Sambas',
        '6171' => 'Kota Pontianak', '6212' => 'Kabupaten Barito Timur', '6271' => 'Kota Palangka Raya',
        '6309' => 'Kabupaten Tabalong', '6310' => 'Kabupaten Tanah Bumbu', '6371' => 'Kota Banjarmasin',
        '6372' => 'Kota Banjarbaru', '6401' => 'Kabupaten Paser', '6402' => 'Kabupaten Kutai Barat',
        '6403' => 'Kabupaten Kutai Kartanegara', '6404' => 'Kabupaten Kutai Timur', '6405' => 'Kabupaten Berau',
        '6409' => 'Kabupaten Penajam Paser Utara', '6471' => 'Kota Balikpapan', '6472' => 'Kota Samarinda',
        '6474' => 'Kota Bontang', '7171' => 'Kota Manado', '7371' => 'Kota Makassar',
        '7471' => 'Kota Kendari', '8271' => 'Kota Ternate',
    ];

    const PICKUP_STATUS = ['4' => 'DONE', '3' => 'PROGRESS', '2' => 'PROGRESS', '1' => 'CANCELLED', '0' => 'CANCELLED'];

    const DAILY_CAP_KG = 1200; // max kg attributed to a single date when day-spreading
    const SUPPLIER_JENIS = 'SUPPLIER';

    public function handle()
    {
        ini_set('memory_limit', '1024M');
        $dir = rtrim($this->option('dir'), '/');
        $service = app(ISCCTraceabilityService::class);

        if (!$this->option('keep-existing')) {
            $this->warn('Purging existing traceability_external_deliveries / poo_fragments...');
            DB::table('traceability_external_poo_fragments')->delete();
            DB::table('traceability_external_deliveries')->delete();
        }

        $this->info('Loading CSV exports...');
        $suppliers = $this->loadCsvRows($dir, 'm_supplier');
        $workOrders = $this->loadCsvRows($dir, 't_work_order');
        $pickups = $this->loadCsvRows($dir, 't_pickup');
        $pickupDetails = $this->loadCsvRows($dir, 't_pickup_detail');
        $suratJalan = $this->loadCsvRows($dir, 't_surat_jalan');
        $sjDetails = $this->loadCsvRows($dir, 't_surat_jalan_detail');

        // drop soft-deleted rows (DELETED_AT set)
        foreach (['suppliers', 'workOrders', 'pickups', 'pickupDetails', 'suratJalan', 'sjDetails'] as $var) {
            $$var = array_filter($$var, function ($r) { return empty($r['DELETED_AT']); });
        }

        $this->info(sprintf(
            'Rows: suppliers=%d wo=%d pickup=%d pickup_detail=%d sj=%d sj_detail=%d',
            count($suppliers), count($workOrders), count($pickups), count($pickupDetails), count($suratJalan), count($sjDetails)
        ));

        // index by id
        $suppliers = array_column($suppliers, null, 'id');
        $workOrders = array_column($workOrders, null, 'id');
        $pickups = array_column($pickups, null, 'id');
        $pickupDetails = array_column($pickupDetails, null, 'id');
        $suratJalan = array_column($suratJalan, null, 'id');

        // only active suppliers
        $suppliers = array_filter($suppliers, function ($s) { return isset($s['active']) && $s['active'] === '1'; });

        $this->supplierById = $suppliers;

        // name pools for randomized POO identities (only non-SUPPLIER jenis)
        foreach ($suppliers as $s) {
            $isSup = trim($s['jenis'] ?? '') === self::SUPPLIER_JENIS;
            if ($isSup) { continue; }
            if ($s['kota_id'] ?? null) {
                $this->nonSupPoolByCity[$s['kota_id']][] = $s['id'];
            }
            if ($s['provinsi_id'] ?? null) {
                $this->nonSupPoolByProvince[$s['provinsi_id']][] = $s['id'];
            }
        }

        $skipCount = ['no_supplier' => 0, 'no_pd' => 0, 'no_sj' => 0, 'not_done' => 0, 'zero_qty' => 0, 'dup' => 0];

        // group sj_detail lines by (surat_jalan_id, supplier_id): each collection is
        // its own delivery (collections within the same month are NOT merged)
        $groups = [];
        foreach ($sjDetails as $sd) {
            if ($sd['status'] !== 'done') { $skipCount['not_done']++; continue; }
            $sj = $suratJalan[$sd['surat_jalan_id']] ?? null;
            if (!$sj) { $skipCount['no_sj']++; continue; }
            $pd = $pickupDetails[$sd['pickup_detail_id']] ?? null;
            if (!$pd) { $skipCount['no_pd']++; continue; }
            $supplier = $suppliers[$pd['supplier_id']] ?? null;
            if (!$supplier) { $skipCount['no_supplier']++; continue; }
            $qty = (float) ($sd['qty_real'] ?? 0);
            if ($qty <= 0) { $skipCount['zero_qty']++; continue; }

            $key = $sd['surat_jalan_id'] . ':' . $pd['supplier_id'];
            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'sj' => $sj,
                    'supplier' => $supplier,
                    'lines' => [],
                    'qty' => 0.0,
                    'wo_codes' => [],
                    'wo_dates' => [],
                    'fotos' => [],
                ];
            }
            $groups[$key]['lines'][] = $sd;
            $groups[$key]['qty'] = round($groups[$key]['qty'] + $qty, 2);
            if ($sd['foto']) { $groups[$key]['fotos'][] = trim($sd['foto']); }
            $wo = $workOrders[$pd['work_order_id']] ?? null;
            if ($wo) {
                if (trim($wo['kode'])) { $groups[$key]['wo_codes'][trim($wo['kode'])] = true; }
                if ($wo['tgl']) { $groups[$key]['wo_dates'][] = $wo['tgl']; }
            }
        }

        $this->info('Groups (deliveries = supplier x surat-jalan): ' . count($groups));

        // existing api_references for idempotency
        $existingRefs = DB::table('traceability_external_deliveries')->pluck('api_reference')->all();
        $existingRefs = array_flip($existingRefs);

        $deliveryCount = 0; $fragmentCount = 0; $skipped = 0;
        $fragmentsBatch = [];
        $byType = [];
        $splitDeliveries = 0; $dayGroups = 0;

        foreach ($groups as $key => $g) {
            $sj = $g['sj'];
            $sup = $g['supplier'];
            $total = $g['qty'];
            $apiRef = trim($sj['kode']) . '-' . $sup['id'];

            if (isset($existingRefs[$apiRef])) { $skipCount['dup']++; $skipped++; continue; }

            $isSupplierJenis = trim($sup['jenis'] ?? '') === self::SUPPLIER_JENIS;
            $label = $isSupplierJenis ? 'SUPPLIER' : $this->typeLabel($sup['kategori_name'] ?? '');
            $byType[$label] = ($byType[$label] ?? 0) + 1;

            $pk = $pickups[$sj['pickup_id']] ?? null;
            $woCodes = array_keys($g['wo_codes']); sort($woCodes);
            $woCodeTxt = implode(', ', $woCodes);
            $sjDate = $sj['tgl'] ?: null;
            $cityName = self::CITIES[$sup['kota_id']] ?? null;
            $provName = self::PROVINCES[$sup['provinsi_id']] ?? null;
            $fotoDoc = $this->joinField($g['fotos']);

            $deliveryId = DB::table('traceability_external_deliveries')->insertGetId([
                'supplier_name' => $sup['name'],
                'poo_type' => $label,
                'city' => $cityName,
                'province' => $provName,
                'delivery_date' => $sjDate,
                'total_quantity_kg' => $total,
                'api_reference' => $apiRef,
                'status' => 'LOCKED',
                'delivery_documentation' => $fotoDoc,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $deliveryCount++;

            // build (qty, fragment-date) pairs
            $pairs = []; // [qty, date]

            if (!$isSupplierJenis) {
                // non-SUPPLIER deliveries are shown as-is: single exact fragment
                $pairs[] = [$total, $sjDate];
            } else {
                // Stage A — day-spread: chunk the delivery across days (<= 5 t/day),
                // centered on the surat-jalan date (-2,-1,0,+1 for 4 days)
                $chunks = $this->spreadDays($total);
                $dayGroups += count($chunks);
                $n = count($chunks);
                foreach ($chunks as $i => $chunk) {
                    $offset = $i - intdiv($n, 2);
                    $date = $sjDate ? date('Y-m-d', strtotime($sjDate . ' ' . $offset . ' days')) : $sjDate;
                    // Stage B — range fragmentation within each day chunk (Pabrik 300-1000)
                    foreach ($service->splitQuantity($chunk, 'pabrik') as $q) {
                        $pairs[] = [$q, $date];
                    }
                }
                if (count($pairs) > 1) { $splitDeliveries++; }
            }

            // randomized POO identities are applied ONLY to actual splits
            $applyPicks = $isSupplierJenis && count($pairs) > 1;
            $pickIds = $applyPicks ? $this->pickNames($sup, count($pairs)) : [];

            foreach ($pairs as $i => [$qty, $date]) {
                $seq = $i + 1;
                $pick = $pickIds[$i] ?? null;
                $pid = $pick ? $this->pickSupplierById($pick) : null;
                $nameSrc = $pid ? $pid : $sup;
                $isRepeated = $pick && $this->nameRepeated($pickIds, $pick, $i);
                $pooName = trim($nameSrc['name'] ?? $sup['name']) . ($isRepeated ? ' (' . $seq . ')' : '');
                $pooType = $pid ? ($this->typeLabel($pid['kategori_name'] ?? '') ?? 'SUPPLIER') : $label;

                $fragmentsBatch[] = [
                    'traceability_external_delivery_id' => $deliveryId,
                    'fragment_sequence' => $seq,
                    'poo_name' => $pooName,
                    'poo_type' => $pooType,
                    'quantity_kg' => $qty,
                    'unit' => 'Kg',
                    'city' => $cityName,
                    'province' => $provName,
                    'full_address' => $nameSrc['alamat'] ?: $sup['alamat'],
                    'lat' => $this->parseLat($nameSrc['gps'] ?? ''),
                    'lng' => $this->parseLng($nameSrc['gps'] ?? ''),
                    'oil_documentation' => $fotoDoc,
                    'wo_code' => $woCodeTxt ?: null,
                    'wo_date' => $date ?: null,
                    'wo_status' => 'DONE',
                    'po_code' => $pk ? trim($pk['kode']) : null,
                    'po_date' => $date ?: null,
                    'po_qty' => $total,
                    'po_status' => $pk ? (self::PICKUP_STATUS[$pk['status']] ?? 'PROGRESS') : null,
                    'do_code' => trim($sj['kode']),
                    'do_date' => $date ?: null,
                    'do_status' => 'DONE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $fragmentCount++;

                if (count($fragmentsBatch) >= 500) {
                    DB::table('traceability_external_poo_fragments')->insert($fragmentsBatch);
                    $fragmentsBatch = [];
                }
            }
        }

        if ($fragmentsBatch) {
            DB::table('traceability_external_poo_fragments')->insert($fragmentsBatch);
        }

        $this->info('Split deliveries (jenis=SUPPLIER, >1 fragment): ' . $splitDeliveries);
        $this->info('Day groups created: ' . $dayGroups);

        $this->info('--- Import summary ---');
        $this->info(sprintf('Deliveries inserted: %d', $deliveryCount));
        $this->info(sprintf('Fragments inserted:  %d', $fragmentCount));
        $this->info(sprintf('Skipped (existing  api_reference): %d', $skipCount['dup']));
        $this->info('Type distribution (per delivery):');
        foreach ($byType as $label => $n) {
            $this->line(sprintf('   %-12s %d', $label, $n));
        }
        $this->info('Skipped-by-reason: ' . json_encode(array_filter($skipCount)));

        // sanity: mass balance
        $mismatch = DB::table('traceability_external_deliveries as ted')
            ->where('ted.status', 'LOCKED')
            ->join('traceability_external_poo_fragments as tep', 'tep.traceability_external_delivery_id', '=', 'ted.id')
            ->selectRaw('ted.id, ted.total_quantity_kg, SUM(tep.quantity_kg) as frag_sum')
            ->groupBy('ted.id', 'ted.total_quantity_kg')
            ->havingRaw('ABS(SUM(tep.quantity_kg) - ted.total_quantity_kg) > 0.01')
            ->count();
        $this->info('Mass-balance mismatches: ' . $mismatch);

        return 0;
    }

    protected function loadCsvRows(string $dir, string $table): array
    {
        $pattern = '/^' . preg_quote($table) . '_\d{12}\.csv$/i';
        $files = array_values(array_filter(scandir($dir), function ($f) use ($pattern) {
            return preg_match($pattern, $f) === 1;
        }));
        $files = array_map(function ($f) use ($dir) { return $dir . '/' . $f; }, $files);

        if (count($files) !== 1) {
            throw new \RuntimeException("Expected exactly 1 CSV for '{$table}', found " . count($files));
        }

        $rows = [];
        $handle = fopen($files[0], 'r');
        if (!$handle) {
            throw new \RuntimeException("Cannot open {$files[0]}");
        }

        $header = fgetcsv($handle);
        $header = array_map('trim', $header);

        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) !== count($header)) {
                $data = array_pad($data, count($header), null);
            }
            $rows[] = array_combine($header, array_slice($data, 0, count($header)));
        }
        fclose($handle);

        return $rows;
    }

    protected function typeKey(?string $category): string
    {
        $c = strtolower(trim((string) $category));

        if ($c === 'restoran' || $c === 'restaurant' || strpos($c, 'resto') !== false) return 'restaurant';
        if ($c === 'cafe' || $c === 'kafe' || $c === 'coffee' || strpos($c, 'cafe') !== false) return 'kafe';
        if ($c === 'hotel' || strpos($c, 'hotel') !== false) return 'hotel';
        if ($c === 'irt' || $c === 'rumah tangga') return 'irt';
        if ($c === 'warung' || strpos($c, 'warung') !== false) return 'warung';
        if ($c === 'lembaga' || strpos($c, 'lembaga') !== false) return 'lembaga';
        if ($c === 'sppg' || strpos($c, 'sppg') !== false) return 'sppg';
        if ($c === 'industri' || $c === 'perusahaan' || $c === 'pabrik' || strpos($c, 'industri') !== false || strpos($c, 'perusahaan') !== false) return 'pabrik';

        return 'pabrik'; // unknown -> pabrik range (user decision)
    }

    protected function typeLabel(?string $category): string
    {
        $key = $this->typeKey($category);
        $labels = [
            'restaurant' => 'Restoran', 'hotel' => 'Hotel', 'irt' => 'IRT', 'kafe' => 'Kafe',
            'warung' => 'Warung', 'lembaga' => 'Lembaga', 'sppg' => 'SPPG', 'pabrik' => 'Lainnya',
        ];

        return $labels[$key] ?? 'Lainnya';
    }

    protected function parseLat(string $gps): ?string
    {
        $parts = array_map('trim', explode(',', $gps));
        return isset($parts[0]) && is_numeric(str_replace(',', '.', $parts[0])) ? (string) trim($parts[0]) : null;
    }

    protected function parseLng(string $gps): ?string
    {
        $parts = array_map('trim', explode(',', $gps));
        $lng = $parts[1] ?? '';
        $lng = preg_replace('/[^\d.\-]/', '', $lng);
        return $lng !== '' && is_numeric($lng) ? $lng : null;
    }

    protected function joinField(array $values): ?string
    {
        $values = array_values(array_filter(array_map('trim', $values)));
        return $values ? implode(', ', $values) : null;
    }

    /**
     * Split a delivery total into daily chunks, each <= DAILY_CAP_KG (1,200 kg).
     * Sum of chunks always equals the input total (0.01 kg precision).
     * @return float[]
     */
    protected function spreadDays(float $total): array
    {
        $totalInt = (int) round($total * 100);
        $cap = self::DAILY_CAP_KG * 100;
        $n = max(1, (int) ceil($totalInt / $cap));
        $chunks = [];
        $remaining = $totalInt;

        for ($i = 0; $i < $n; $i++) {
            $rest = $n - $i - 1;
            if ($i === $n - 1) {
                $c = $remaining;
            } else {
                $low = max(1, $remaining - $rest * $cap);
                $high = min($cap, $remaining - $rest * 1);
                if ($high < $low) { $high = $low; }
                $c = mt_rand($low, $high);
            }
            $chunks[] = $c / 100;
            $remaining -= $c;
        }

        return $chunks;
    }

    /**
     * Deterministically pick N real supplier ids (jenis != SUPPLIER) to name
     * split POO fragments, preferring suppliers in the same city as the collection.
     * Falls back: same-province non-SUPPLIER -> repeats with suffix.
     * @return int[] length = $n
     */
    protected function pickNames(array $sup, int $n): array
    {
        $self = $sup['id'];
        $city = $sup['kota_id'] ?? '';
        $prov = $sup['provinsi_id'] ?? '';

        $pools = [];
        if ($city && isset($this->nonSupPoolByCity[$city])) {
            $pools[] = array_values(array_diff($this->nonSupPoolByCity[$city], [$self]));
        }
        if ($prov && isset($this->nonSupPoolByProvince[$prov])) {
            $pools[] = array_values(array_diff($this->nonSupPoolByProvince[$prov], [$self]));
        }

        mt_srand(crc32($sup['id'] . '|' . $city));
        $flat = [];
        foreach ($pools as $pool) {
            if (!$pool) { continue; }
            shuffle($pool);
            $flat = array_merge($flat, $pool);
        }
        $flat = array_values(array_unique($flat));

        if (!$flat) {
            $flat = [$self];
        }

        $out = [];
        for ($i = 0; $i < $n; $i++) {
            $out[] = $flat[$i % count($flat)];
        }

        return $out;
    }

    protected function pickSupplierById(?int $id): ?array
    {
        return isset($this->supplierById[$id]) ? $this->supplierById[$id] : null;
    }

    protected function nameRepeated(array $picks, int $pick, int $index): bool
    {
        for ($i = 0; $i < $index; $i++) {
            if ($picks[$i] === $pick) { return true; }
        }
        return false;
    }

}
