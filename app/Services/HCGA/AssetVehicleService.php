<?php

namespace App\Services\HCGA;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class AssetVehicleService
{
    /** @var VehicleDocumentReminderService */
    protected $reminderService;

    public function __construct(VehicleDocumentReminderService $reminderService)
    {
        $this->reminderService = $reminderService;
    }

    /**
     * Ambil data asset_list dengan kolom spesifik untuk grid 49 kolom.
     */
    public function getAssetGridData(array $filters = [])
    {
        $query = DB::table('asset_list')
            ->select(
                'ID',
                'Asset_No',
                'Asset_Category',
                'Asset_Sub_Category',
                'Asset_Type',
                'Product_Code',
                'Product_Description',
                'Product_Brand',
                'Sub_Product_Type',
                'Manufacture_Date',
                'Registration_No',
                'Buy_Date',
                'Buy_Val',
                'Asset_Location',
                'Asset_Location_Address',
                'Asset_Location_City',
                'Asset_Location_Department',
                'Leasing_Vendor',
                'Leasing_No',
                'Acc_No',
                'Unit_Name',
                'Model',
                'Source_Production',
                'Capacity',
                'Part_No',
                'SN',
                'Asset_Type as Jenis_Kendaraan',
                'BPKB_No',
                'Surat_Jalan_No',
                'Asuransi',
                'STNK_Tahunan',
                'STNK_5_Tahun',
                'Jadwal_KIR',
                'Berkas_KIR',
                'Subsid_Mypertamina',
                'Barcode_Kode',
                'Barcode_Kode_PSAK',
                'Tanggal_PM_Terakhir',
                'Tanggal_Jadwal_PM',
                'KM_Saat_Ini',
                'KM_Jadwal_PM',
                'Interval_PM',
                'Tanggal_Penghapusan',
                'User_Responsible',
                'User_Sebelumnya',
                'Link_QR',
                'QR_Aset',
                'Status',
                'Note'
            )
            ->orderBy('ID', 'ASC');

        if (!empty($filters['sub_category'])) {
            $query->where('Asset_Sub_Category', $filters['sub_category']);
        }
        if (!empty($filters['status'])) {
            $query->where('Status', $filters['status']);
        }

        return $query->get();
    }

    /**
     * Ambil daftar kendaraan beserta evaluasi status STNK Tahunan, STNK 5 Tahunan, & KEUR.
     */
    public function getVehicleGridData(string $docStatusFilter = 'ALL'): array
    {
        $today = Carbon::today();
        $vehicles = $this->reminderService->vehicleBaseQuery()
            ->select(
                'ID',
                'Asset_No',
                'Registration_No',
                'Unit_Name',
                'Product_Brand',
                'Product_Description',
                'Model',
                'Asset_Type',
                'Source_Production',
                'Capacity',
                'Part_No',
                'SN',
                'BPKB_No',
                'Asuransi',
                'Asset_Location',
                'Asset_Location_Department',
                'User_Responsible',
                'KM_Saat_Ini',
                'KM_Jadwal_PM',
                'Tanggal_Jadwal_PM',
                'STNK_Tahunan',
                'STNK_5_Tahun',
                'Jadwal_KIR',
                'Berkas_KIR',
                'Subsid_Mypertamina',
                'Status'
            )
            ->orderBy('Registration_No', 'ASC')
            ->orderBy('Asset_No', 'ASC')
            ->get();

        $rows = [];
        $filter = strtoupper(trim($docStatusFilter));

        foreach ($vehicles as $v) {
            $stnk1 = $this->reminderService->evaluateDocumentStatus($v->STNK_Tahunan, $today);
            $stnk5 = $this->reminderService->evaluateDocumentStatus($v->STNK_5_Tahun, $today);
            $keur  = $this->reminderService->evaluateDocumentStatus($v->Jadwal_KIR, $today);

            $worst = $this->pickWorstStatus([$stnk1, $stnk5, $keur]);

            if ($filter !== '' && $filter !== 'ALL') {
                if ($filter === 'ATTENTION') {
                    if (!in_array($worst['status'], ['EXPIRED', 'CRITICAL', 'WARNING', 'NOTICE'], true)) {
                        continue;
                    }
                } elseif ($worst['status'] !== $filter) {
                    continue;
                }
            }

            $rows[] = [
                'vehicle' => $v,
                'stnk_1'  => $stnk1,
                'stnk_5'  => $stnk5,
                'keur'    => $keur,
                'overall' => $worst,
            ];
        }

        return $rows;
    }

    protected function pickWorstStatus(array $evals): array
    {
        $priority = [
            'EXPIRED'  => 1,
            'CRITICAL' => 2,
            'WARNING'  => 3,
            'NOTICE'   => 4,
            'OK'       => 5,
            'UNSET'    => 6,
        ];

        usort($evals, function ($a, $b) use ($priority) {
            $pa = $priority[$a['status']] ?? 99;
            $pb = $priority[$b['status']] ?? 99;
            if ($pa === $pb) {
                return ($a['days_remaining'] ?? 99999) <=> ($b['days_remaining'] ?? 99999);
            }
            return $pa <=> $pb;
        });

        return $evals[0];
    }

    /**
     * Data master untuk form create asset baru.
     */
    public function getCreateFormData(): array
    {
        return [
            'categories' => DB::table('master_product_group')->orderBy('Product_Group', 'ASC')->get(),
            'suppliers'  => DB::table('supplier')->orderBy('Company_Name', 'ASC')->get(),
            'banks'      => DB::table('bank_acc')->where('Status', 'ACTIVE')->orderBy('Bank_Acc', 'ASC')->get(),
            'warehouses' => DB::table('gudang_list')->orderBy('Gudang_Name', 'ASC')->get(),
            'poList'     => DB::table('purchase_order')
                ->whereIn('Status_Receive', ['WAITING', 'PARTIAL', ''])
                ->orWhereNull('Status_Receive')
                ->orderBy('PO_No', 'DESC')
                ->limit(200)
                ->get(),
        ];
    }

    /**
     * Ambil detail lengkap aset beserta riwayat STNK, KIR, dan foto.
     */
    public function getAssetDetail(string $assetNo): ?array
    {
        $asset = DB::table('asset_list')->where('Asset_No', $assetNo)->first();
        if (!$asset) {
            return null;
        }

        $stnkLogs = DB::table('asset_stnk_log')->where('Asset_No', $assetNo)->orderBy('ID', 'DESC')->get();
        $kirLogs  = DB::table('asset_kir_log')->where('Asset_No', $assetNo)->orderBy('Ke', 'DESC')->get();
        $photos   = DB::table('meta_files')
            ->where('Topic', 'asset-photo')
            ->where('Map_To', $assetNo)
            ->orderBy('ID', 'ASC')
            ->get();

        $banks     = DB::table('bank_acc')->where('Status', 'ACTIVE')->orderBy('Bank_Acc', 'ASC')->get();
        $suppliers = DB::table('supplier')->orderBy('Company_Name', 'ASC')->get();

        $today = Carbon::today();

        return [
            'asset'     => $asset,
            'stnkLogs'  => $stnkLogs,
            'kirLogs'   => $kirLogs,
            'photos'    => $photos,
            'banks'     => $banks,
            'suppliers' => $suppliers,
            'docStatus' => [
                'stnk_1' => $this->reminderService->evaluateDocumentStatus($asset->STNK_Tahunan ?? null, $today),
                'stnk_5' => $this->reminderService->evaluateDocumentStatus($asset->STNK_5_Tahun ?? null, $today),
                'keur'   => $this->reminderService->evaluateDocumentStatus($asset->Jadwal_KIR ?? null, $today),
            ],
        ];
    }

    public function getSubCategories(string $category)
    {
        if ($category === '') {
            return collect();
        }

        return DB::table('master_product_sub_group')
            ->where('Product_Group', $category)
            ->orderBy('Sub_Group', 'ASC')
            ->get();
    }

    public function getSubSubCategories(string $category, string $subCategory)
    {
        if ($category === '' || $subCategory === '') {
            return collect();
        }

        return DB::table('master_product_sub_sub_group')
            ->where('Product_Group', $category)
            ->where('Sub_Group', $subCategory)
            ->orderBy('Sub_Sub_Group', 'ASC')
            ->get();
    }

    /**
     * Simpan asset baru (Manual atau dari PO) dalam transaksi.
     */
    public function createAsset(array $payload, string $username, string $location = 'GEU-HO'): array
    {
        return DB::transaction(function () use ($payload, $username, $location) {
            $assetCategory    = $payload['Asset_Category'] ?? '';
            $assetSubCategory = $payload['Asset_Sub_Category'] ?? '';
            $assetType        = $payload['Asset_Type'] ?? '';

            $prod1 = DB::table('master_product_group')->where('Product_Group', $assetCategory)->first();
            $prod2 = DB::table('master_product_sub_group')
                ->where('Product_Group', $assetCategory)
                ->where('Sub_Group', $assetSubCategory)
                ->first();
            $prod3 = DB::table('master_product_sub_sub_group')
                ->where('Product_Group', $assetCategory)
                ->where('Sub_Group', $assetSubCategory)
                ->where('Sub_Sub_Group', $assetType)
                ->first();

            $p1 = $prod1->Initial ?? 'FA';
            $p2 = $prod2->Initial ?? '01';
            $p3 = $prod3->Initial ?? '01';
            $prefix = "{$p1}-{$p2}-{$p3}-01-";

            $columnTypes = $this->getAssetColumnTypes();
            $skipKeys = ['_token', 'Task_ID', 'Created_Date', 'Created_By', 'Location', 'Incoming_Type', 'PO_No', 'PO_Item_Select', 'Item_Qty_Select', 'items'];

            $createdAssetNos = [];
            $isPo = isset($payload['Incoming_Type']) && $payload['Incoming_Type'] === 'PO' && !empty($payload['PO_No']);

            if ($isPo) {
                $poNo        = $payload['PO_No'];
                $productCode = $payload['PO_Item_Select'] ?? '';
                $items       = isset($payload['items']) && is_array($payload['items']) ? $payload['items'] : [];
                $qtyCount    = count($items) > 0 ? count($items) : (int) ($payload['Item_Qty_Select'] ?? 1);
                $grNo        = $payload['AP_No'] ?? ('GR/FA/' . $location . '/' . date('ymdHis'));
                $incoming    = date('Y-m-d');

                foreach ($items as $index => $itemData) {
                    $countExisting = DB::table('asset_list')->where('Asset_No', 'LIKE', "{$prefix}%")->count();
                    $assetNo = $prefix . sprintf("%'04d", $countExisting + 1 + $index);

                    $row = [
                        'Asset_No'           => $assetNo,
                        'PO_No'              => $poNo,
                        'Product_Code'       => $productCode,
                        'Asset_Category'     => $assetCategory,
                        'Asset_Sub_Category' => $assetSubCategory,
                        'Asset_Type'         => $assetType,
                        'Location'           => $location,
                        'Created_By'         => $username,
                        'Created_Date'       => now(),
                        'Status'             => 'NEW',
                    ];

                    foreach ($itemData as $k => $v) {
                        if (in_array($k, $skipKeys, true) || !isset($columnTypes[$k])) {
                            continue;
                        }
                        $row[$k] = $this->normalizeColumnValue($v, $columnTypes[$k]);
                    }

                    DB::table('asset_list')->insert($row);
                    $createdAssetNos[] = $assetNo;
                }

                $poDtlQuery = DB::table('purchase_order_dtl')->where('PO_No', $poNo);
                if ($productCode !== '') {
                    $poDtlQuery->where('Product_Code', $productCode);
                }
                $targetDtl = $poDtlQuery->first();

                if ($targetDtl) {
                    $newRec = ((int) ($targetDtl->Qty_Receive ?? 0)) + $qtyCount;
                    $newRem = max(0, ((int) ($targetDtl->Qty ?? 0)) - $newRec);
                    DB::table('purchase_order_dtl')->where('ID', $targetDtl->ID)->update([
                        'GR_No'       => $grNo,
                        'Qty_Receive' => $newRec,
                        'Qty_Remain'  => $newRem,
                    ]);
                } else {
                    DB::table('purchase_order_dtl')->where('PO_No', $poNo)->update(['GR_No' => $grNo]);
                }

                $allDetails = DB::table('purchase_order_dtl')->where('PO_No', $poNo)->get();
                $totalPOQty = $allDetails->sum('Qty');
                $totalPORec = $allDetails->sum('Qty_Receive');
                $statusReceive = 'PARTIAL';
                if ($totalPORec >= $totalPOQty && $totalPOQty > 0) {
                    $statusReceive = 'RECEIVED';
                } elseif ($totalPORec == 0) {
                    $statusReceive = 'WAITING';
                }

                DB::table('purchase_order')->where('PO_No', $poNo)->update([
                    'GR_No'          => $grNo,
                    'GR_Date'        => now(),
                    'Received_Date'  => $incoming,
                    'Status_Receive' => $statusReceive,
                ]);

                $poHeader = DB::table('purchase_order')->where('PO_No', $poNo)->first();
                if ($poHeader) {
                    DB::table('good_receive')->insert([
                        'GR_No'                 => $grNo,
                        'PR_No'                 => $poHeader->PR_No ?? '',
                        'PO_No'                 => $poNo,
                        'PO_No_KSPS'            => $poHeader->PO_No_KSPS ?? '',
                        'Reference'             => $payload['Reference'] ?? '',
                        'Remark'                => $payload['Remark'] ?? ($payload['Note'] ?? ''),
                        'Location'              => $location,
                        'Status'                => 'CREATED',
                        'Created_By'            => $username,
                        'Date_Created'          => now(),
                        'Purchase_Category'     => $poHeader->Purchase_Category ?? '',
                        'Purchase_Sub_Category' => $poHeader->Purchase_Sub_Category ?? '',
                        'Purchase_Type'         => $poHeader->Purchase_Type ?? '',
                        'Incoming_Date'         => $incoming,
                    ]);
                }
            } else {
                $countExisting = DB::table('asset_list')->where('Asset_No', 'LIKE', "{$prefix}%")->count();
                $assetNo = $prefix . sprintf("%'04d", $countExisting + 1);

                $row = [
                    'Asset_No'     => $assetNo,
                    'Location'     => $location,
                    'Created_By'   => $username,
                    'Created_Date' => now(),
                    'Status'       => 'NEW',
                ];

                foreach ($payload as $k => $v) {
                    if (in_array($k, $skipKeys, true) || !isset($columnTypes[$k])) {
                        continue;
                    }
                    $row[$k] = $this->normalizeColumnValue($v, $columnTypes[$k]);
                }

                DB::table('asset_list')->insert($row);
                $createdAssetNos[] = $assetNo;

                $grNoManual = $payload['AP_No'] ?? ('GR/FA/' . $location . '/' . date('ymdHis'));
                DB::table('good_receive')->insert([
                    'GR_No'                 => $grNoManual,
                    'PR_No'                 => 'Manual',
                    'PO_No'                 => 'Manual',
                    'PO_No_KSPS'            => 'Manual',
                    'Reference'             => $payload['Registration_No'] ?? ($payload['Part_No'] ?? 'Manual'),
                    'Remark'                => $payload['Specification'] ?? ($payload['Product_Description'] ?? 'Manual Input Asset'),
                    'Location'              => $location,
                    'Status'                => 'CREATED',
                    'Created_By'            => $username,
                    'Date_Created'          => now(),
                    'Purchase_Category'     => 'ASSET',
                    'Purchase_Sub_Category' => $assetCategory,
                    'Purchase_Type'         => $assetType,
                    'Incoming_Date'         => date('Y-m-d'),
                ]);
            }

            return $createdAssetNos;
        });
    }

    /**
     * Update data asset dari form detail.
     */
    public function updateAsset(int $id, array $payload, string $username): void
    {
        $columnTypes = $this->getAssetColumnTypes();
        $skip = ['_token', 'Task_ID', 'ID', 'Created_Date', 'Created_By', 'Location', 'Incoming_Type', 'PO_Item_Select', 'Item_Qty_Select'];

        $data = [];
        foreach ($payload as $k => $v) {
            if (in_array($k, $skip, true) || !isset($columnTypes[$k])) {
                continue;
            }
            $data[$k] = $this->normalizeColumnValue($v, $columnTypes[$k]);
        }

        $data['Last_User'] = $username;
        $data['Last_Modified_Date'] = now();

        DB::table('asset_list')->where('ID', $id)->update($data);
    }

    /**
     * Update STNK (Tahunan / 5 Tahunan) secara append-only ke asset_stnk_log + sync asset_list.
     */
    public function updateStnk(string $assetNo, string $jenis, string $tanggalBerlaku, ?string $catatan, string $username): void
    {
        if (!in_array($jenis, ['TAHUNAN', '5_TAHUNAN'], true)) {
            throw new \InvalidArgumentException('Jenis STNK tidak valid.');
        }

        DB::transaction(function () use ($assetNo, $jenis, $tanggalBerlaku, $catatan, $username) {
            $asset = DB::table('asset_list')->where('Asset_No', $assetNo)->first();
            if (!$asset) {
                throw new \RuntimeException('Asset tidak ditemukan.');
            }

            $col = ($jenis === '5_TAHUNAN') ? 'STNK_5_Tahun' : 'STNK_Tahunan';
            $sebelumnya = $asset->$col;
            if ($sebelumnya === '0000-00-00' || $sebelumnya === '') {
                $sebelumnya = null;
            }

            DB::table('asset_stnk_log')->insert([
                'Asset_No'           => $assetNo,
                'Jenis'              => $jenis,
                'Tanggal_Efektif'    => $tanggalBerlaku,
                'Tanggal_Sebelumnya' => $sebelumnya,
                'Catatan'            => (string) $catatan,
                'Updated_By'         => $username,
            ]);

            DB::table('asset_list')->where('Asset_No', $assetNo)->update([
                $col                 => $tanggalBerlaku,
                'Last_User'          => $username,
                'Last_Modified_Date' => now(),
            ]);
        });
    }

    /**
     * Update KEUR/KIR secara append-only ke asset_kir_log (+6 bulan) + sync asset_list.
     */
    public function updateKeur(string $assetNo, string $tanggalKir, ?string $berkas, ?string $catatan, string $username): string
    {
        return DB::transaction(function () use ($assetNo, $tanggalKir, $berkas, $catatan, $username) {
            $asset = DB::table('asset_list')->where('Asset_No', $assetNo)->first();
            if (!$asset) {
                throw new \RuntimeException('Asset tidak ditemukan.');
            }

            $ke = (int) DB::table('asset_kir_log')->where('Asset_No', $assetNo)->max('Ke') + 1;
            $next = Carbon::parse($tanggalKir)->addMonths(6)->toDateString();

            DB::table('asset_kir_log')->insert([
                'Asset_No'              => $assetNo,
                'Ke'                    => $ke,
                'Tanggal_KIR'           => $tanggalKir,
                'Jadwal_KIR_Berikutnya' => $next,
                'Berkas'                => (string) $berkas,
                'Catatan'               => (string) $catatan,
                'Created_By'            => $username,
            ]);

            DB::table('asset_list')->where('Asset_No', $assetNo)->update([
                'Jadwal_KIR'         => $next,
                'Berkas_KIR'         => ($berkas !== null && $berkas !== '') ? $berkas : $asset->Berkas_KIR,
                'Last_User'          => $username,
                'Last_Modified_Date' => now(),
            ]);

            return $next;
        });
    }

    /**
     * Upload foto aset ke meta_files (append-only) & sinkronkan cover asset_list.Photo.
     *
     * @param UploadedFile[] $files
     */
    public function uploadPhotos(string $assetNo, array $files, string $username): array
    {
        $asset = DB::table('asset_list')->where('Asset_No', $assetNo)->first();
        if (!$asset) {
            throw new \RuntimeException('Asset tidak ditemukan.');
        }

        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $maxBytes = 5 * 1024 * 1024;
        $uploaded = 0;
        $rejected = [];

        $year = date('Y');
        $month = date('m');
        $targetDir = public_path("filevendor/{$year}/{$month}");
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile || !$file->isValid()) {
                $rejected[] = ($file instanceof UploadedFile ? $file->getClientOriginalName() : 'berkas') . ' (gagal diunggah)';
                continue;
            }

            $ext = strtolower($file->getClientOriginalExtension());
            if (!in_array($ext, $allowed, true)) {
                $rejected[] = $file->getClientOriginalName() . " (format {$ext} tidak diizinkan)";
                continue;
            }

            if ($file->getSize() > $maxBytes) {
                $rejected[] = $file->getClientOriginalName() . ' (lebih dari 5 MB)';
                continue;
            }

            $newName = 'asset_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $assetNo) . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $file->move($targetDir, $newName);

            DB::table('meta_files')->insert([
                'Filename'    => $newName,
                'Path'        => "{$year}/{$month}/{$newName}",
                'Extension'   => $ext,
                'Description' => 'Asset Photo',
                'Topic'       => 'asset-photo',
                'Map_To'      => $assetNo,
                'Created_At'  => now(),
            ]);

            $uploaded++;
        }

        $this->syncCoverPhoto($assetNo, $username);

        return [
            'uploaded' => $uploaded,
            'rejected' => $rejected,
        ];
    }

    public function deletePhoto(int $photoId, string $assetNo, string $username): void
    {
        $foto = DB::table('meta_files')->where('ID', $photoId)->where('Topic', 'asset-photo')->first();
        if (!$foto) {
            throw new \RuntimeException('Foto tidak ditemukan.');
        }

        $targetAssetNo = $assetNo !== '' ? $assetNo : $foto->Map_To;
        $fullPath = public_path('filevendor/' . $foto->Path);
        if ($foto->Path && file_exists($fullPath)) {
            @unlink($fullPath);
        }

        DB::table('meta_files')->where('ID', $foto->ID)->delete();
        $this->syncCoverPhoto($targetAssetNo, $username);
    }

    protected function syncCoverPhoto(string $assetNo, string $username): void
    {
        $first = DB::table('meta_files')
            ->where('Topic', 'asset-photo')
            ->where('Map_To', $assetNo)
            ->orderBy('ID', 'ASC')
            ->first();

        DB::table('asset_list')->where('Asset_No', $assetNo)->update([
            'Photo'              => $first ? asset('filevendor/' . $first->Path) : null,
            'Last_User'          => $username,
            'Last_Modified_Date' => now(),
        ]);
    }

    protected function getAssetColumnTypes(): array
    {
        $types = [];
        foreach (DB::select('SHOW COLUMNS FROM asset_list') as $col) {
            $types[$col->Field] = $col->Type;
        }
        return $types;
    }

    protected function normalizeColumnValue($value, string $columnType)
    {
        if ($value === '' && preg_match('/^(date|datetime|timestamp|int|tinyint|smallint|mediumint|bigint|float|double|decimal)/', $columnType)) {
            return null;
        }
        return $value;
    }
}
