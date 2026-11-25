<?php

namespace App\Services\HCGA;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facade as QrCode;

class AssetQrService
{
    /**
     * Kolom operasional & inventaris yang aman ditampilkan pada PDF publik.
     * Sengaja mengecualikan data finansial/legal sensitif (Buy_Val, Leasing_*, Acc_No, BPKB_No).
     *
     * @var array
     */
    protected $publicOperationalColumns = [
        'ID',
        'Asset_No',
        'Asset_Category',
        'Asset_Sub_Category',
        'Asset_Type',
        'Product_Code',
        'Product_Description',
        'Product_Brand',
        'Sub_Product_Type',
        'Unit_Name',
        'Model',
        'SN',
        'Registration_No',
        'Asset_Location',
        'Asset_Location_Address',
        'Asset_Location_City',
        'Asset_Location_Department',
        'User_Responsible',
        'Status',
        'Kondisi_Aset',
        'Note',
    ];

    public function makeSignature(string $type, string $identifier): string
    {
        $payload = 'asset-qr|' . strtolower(trim($type)) . '|' . trim($identifier);
        $key = (string) config('app.key', 'hcga-asset-qr-fallback');

        return substr(hash_hmac('sha256', $payload, $key), 0, 20);
    }

    public function verifySignature(string $type, string $identifier, ?string $signature): bool
    {
        $sig = trim((string) $signature);
        if ($sig === '' || trim($identifier) === '') {
            return false;
        }

        $expected = $this->makeSignature($type, $identifier);

        return hash_equals($expected, $sig);
    }

    public function buildPublicLocationUrl(string $locationCode): string
    {
        $code = trim($locationCode);

        return url('public-asset-qr/location/' . rawurlencode($code))
            . '?sig=' . $this->makeSignature('location', $code);
    }

    public function buildPublicAssetUrl(string $assetNo): string
    {
        $code = trim($assetNo);

        return url('public-asset-qr/item/' . rawurlencode($code))
            . '?sig=' . $this->makeSignature('asset', $code);
    }

    public function generateQrSvg(string $targetUrl, int $size = 320): string
    {
        return (string) QrCode::format('svg')->size($size)->margin(1)->generate($targetUrl);
    }

    public function generateQrDataUri(string $targetUrl): string
    {
        return 'data:image/svg+xml;base64,' . base64_encode($this->generateQrSvg($targetUrl, 320));
    }

    public function logoRawPng(string $path, int $scale = 3): ?string
    {
        if (!is_file($path) || !function_exists('imagecreatefromstring')) {
            return null;
        }

        $src = @imagecreatefromstring(file_get_contents($path));
        if (!$src) {
            return null;
        }

        $w = imagesx($src);
        $h = imagesy($src);
        $dst = imagecreatetruecolor($w * $scale, $h * $scale);
        imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $w * $scale, $h * $scale, $w, $h);

        ob_start();
        imagepng($dst);
        $png = ob_get_clean();
        imagedestroy($src);
        imagedestroy($dst);

        return $png ?: null;
    }

    public function logoDataUri(string $path, int $scale = 3): ?string
    {
        $png = $this->logoRawPng($path, $scale);

        return $png !== null ? ('data:image/png;base64,' . base64_encode($png)) : null;
    }

    /**
     * Daftar lokasi/site (gudang_list + nilai unik Asset_Location di asset_list) beserta jumlah aset.
     */
    public function getLocationItems(): Collection
    {
        $counts = DB::table('asset_list')
            ->select('Asset_Location', DB::raw('COUNT(*) as total_assets'))
            ->whereNotNull('Asset_Location')
            ->where('Asset_Location', '!=', '')
            ->groupBy('Asset_Location')
            ->pluck('total_assets', 'Asset_Location')
            ->all();

        $items = [];
        $warehouses = DB::table('gudang_list')
            ->select('Gudang_ID', 'Description', 'Location', 'Address', 'City')
            ->orderBy('Gudang_ID', 'ASC')
            ->get();

        foreach ($warehouses as $wh) {
            $code = trim((string) ($wh->Gudang_ID ?? ''));
            if ($code === '') {
                continue;
            }
            $desc = trim((string) ($wh->Description ?? ''));
            $items[$code] = (object) [
                'code'           => $code,
                'name'           => $desc !== '' ? $desc : $code,
                'subtitle'       => trim((string) ($wh->Location ?? '')),
                'address'        => trim((string) ($wh->Address ?? '')),
                'city'           => trim((string) ($wh->City ?? '')),
                'asset_count'    => (int) ($counts[$code] ?? 0),
                'source'         => 'gudang_list',
            ];
        }

        foreach ($counts as $locCode => $total) {
            $code = trim((string) $locCode);
            if ($code !== '' && !isset($items[$code])) {
                $items[$code] = (object) [
                    'code'           => $code,
                    'name'           => $code,
                    'subtitle'       => 'Site / Asset Location',
                    'address'        => '',
                    'city'           => '',
                    'asset_count'    => (int) $total,
                    'source'         => 'asset_list',
                ];
            }
        }

        ksort($items, SORT_NATURAL | SORT_FLAG_CASE);

        return collect(array_values($items));
    }

    /**
     * Daftar aset individual untuk halaman cetak QR (dengan dukungan filter).
     */
    public function getAssetItems(array $filters = []): Collection
    {
        $query = DB::table('asset_list')
            ->select($this->publicOperationalColumns)
            ->whereNotNull('Asset_No')
            ->where('Asset_No', '!=', '')
            ->orderBy('Asset_No', 'ASC');

        $category = trim((string) ($filters['category'] ?? ''));
        if ($category !== '') {
            $query->where('Asset_Category', $category);
        }

        $subCategory = trim((string) ($filters['sub_category'] ?? ''));
        if ($subCategory !== '') {
            $query->where('Asset_Sub_Category', $subCategory);
        }

        $location = trim((string) ($filters['location'] ?? ''));
        if ($location !== '') {
            if ($location === '__UNASSIGNED__') {
                $query->where(function ($q) {
                    $q->whereNull('Asset_Location')->orWhere('Asset_Location', '');
                });
            } else {
                $query->where('Asset_Location', $location);
            }
        }

        $status = trim((string) ($filters['status'] ?? ''));
        if ($status !== '') {
            $query->where('Status', $status);
        }

        $kondisi = trim((string) ($filters['kondisi'] ?? ''));
        if ($kondisi !== '') {
            $query->where('Kondisi_Aset', $kondisi);
        }

        return $query->get();
    }

    /**
     * Render PDF publik (inline) berisi daftar aset pada suatu Asset Location / Site + lampiran gambar & PDF.
     */
    public function renderPublicLocationPdf(string $locationCode): array
    {
        $code = trim($locationCode);
        $warehouse = DB::table('gudang_list')->where('Gudang_ID', $code)->first();

        $assets = DB::table('asset_list')
            ->select($this->publicOperationalColumns)
            ->where('Asset_Location', $code)
            ->orderBy('Asset_No', 'ASC')
            ->get();

        $assetNos = $assets->pluck('Asset_No')->filter()->values()->all();
        $attachmentsByAsset = $this->getAttachmentsByAsset($assetNos);

        $assetMap = [];
        foreach ($assets as $asset) {
            $assetMap[(string) $asset->Asset_No] = $asset;
        }

        $logo = $this->logoDataUri(public_path('assets/images/small-logo.png'));
        $generatedAt = Carbon::now()->format('d/m/Y H:i');

        $html = view('humanresources.asset-lists.qr-public-location-pdf', [
            'locationCode'       => $code,
            'warehouse'          => $warehouse,
            'assets'             => $assets,
            'attachmentsByAsset' => $attachmentsByAsset,
            'logo'               => $logo,
            'generatedAt'        => $generatedAt,
        ])->render();

        $tempDir = $this->ensureMpdfTempDir();
        $tempFiles = [];

        try {
            $mpdf = new \Mpdf\Mpdf([
                'mode'          => 'utf-8',
                'format'        => 'A4',
                'orientation'   => 'L',
                'margin_left'   => 10,
                'margin_right'  => 10,
                'margin_top'    => 10,
                'margin_bottom' => 10,
                'tempDir'       => $tempDir,
            ]);

            $mpdf->SetTitle('Daftar Aset Lokasi - ' . $code);
            $mpdf->WriteHTML($html);

            $this->appendAttachments($mpdf, $attachmentsByAsset, $assetMap, $tempDir, $tempFiles);

            $safeCode = preg_replace('/[^A-Za-z0-9_-]/', '_', $code) ?: 'LOCATION';
            $filename = 'Asset-Location-' . $safeCode . '-' . Carbon::now()->format('YmdHis') . '.pdf';

            return [
                'binary'   => $mpdf->Output($filename, \Mpdf\Output\Destination::STRING_RETURN),
                'filename' => $filename,
            ];
        } finally {
            $this->cleanupTempFiles($tempFiles);
        }
    }

    /**
     * Render PDF publik (inline) berisi detail 1 aset operasional + lampiran gambar & PDF.
     */
    public function renderPublicAssetPdf(string $assetNo): ?array
    {
        $code = trim($assetNo);
        $asset = DB::table('asset_list')
            ->select($this->publicOperationalColumns)
            ->where('Asset_No', $code)
            ->first();

        if (!$asset) {
            return null;
        }

        $attachmentsByAsset = $this->getAttachmentsByAsset([$code]);
        $attachments = $attachmentsByAsset[$code] ?? [];

        $logo = $this->logoDataUri(public_path('assets/images/small-logo.png'));
        $generatedAt = Carbon::now()->format('d/m/Y H:i');

        $html = view('humanresources.asset-lists.qr-public-asset-pdf', [
            'asset'       => $asset,
            'attachments' => $attachments,
            'logo'        => $logo,
            'generatedAt' => $generatedAt,
        ])->render();

        $tempDir = $this->ensureMpdfTempDir();
        $tempFiles = [];

        try {
            $mpdf = new \Mpdf\Mpdf([
                'mode'          => 'utf-8',
                'format'        => 'A4',
                'orientation'   => 'P',
                'margin_left'   => 10,
                'margin_right'  => 10,
                'margin_top'    => 10,
                'margin_bottom' => 10,
                'tempDir'       => $tempDir,
            ]);

            $mpdf->SetTitle('Detail Aset - ' . $code);
            $mpdf->WriteHTML($html);

            $this->appendAttachments($mpdf, $attachmentsByAsset, [$code => $asset], $tempDir, $tempFiles);

            $safeCode = preg_replace('/[^A-Za-z0-9_-]/', '_', $code) ?: 'ASSET';
            $filename = 'Asset-' . $safeCode . '-' . Carbon::now()->format('YmdHis') . '.pdf';

            return [
                'binary'   => $mpdf->Output($filename, \Mpdf\Output\Destination::STRING_RETURN),
                'filename' => $filename,
            ];
        } finally {
            $this->cleanupTempFiles($tempFiles);
        }
    }

    /**
     * Ambil lampiran gambar & dokumen aset dari tabel meta_files.
     *
     * @param array $assetNos
     * @return array<string, array>
     */
    protected function getAttachmentsByAsset(array $assetNos): array
    {
        if (empty($assetNos)) {
            return [];
        }

        $rows = DB::table('meta_files')
            ->whereIn('Topic', ['asset-photo', 'asset-doc'])
            ->whereIn('Map_To', $assetNos)
            ->orderBy('Map_To', 'ASC')
            ->orderBy('ID', 'ASC')
            ->get();

        $grouped = [];
        foreach ($rows as $row) {
            $key = (string) ($row->Map_To ?? '');
            if ($key === '') {
                continue;
            }
            if (!isset($grouped[$key])) {
                $grouped[$key] = [];
            }
            $grouped[$key][] = $row;
        }

        return $grouped;
    }

    /**
     * Menggabungkan lampiran gambar & PDF ke halaman lanjutan mPDF.
     */
    protected function appendAttachments(\Mpdf\Mpdf $mpdf, array $attachmentsByAsset, array $assetMap, string $tempDir, array &$tempFiles): void
    {
        $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        foreach ($attachmentsByAsset as $assetNo => $files) {
            $asset = $assetMap[$assetNo] ?? null;
            $assetTitle = $asset && !empty($asset->Product_Description)
                ? ($assetNo . ' - ' . $asset->Product_Description)
                : $assetNo;

            foreach ($files as $file) {
                $relPath = ltrim((string) ($file->Path ?? ''), '/\\');
                if ($relPath === '') {
                    continue;
                }

                $fullPath = public_path('filevendor/' . $relPath);
                if (!is_file($fullPath)) {
                    continue;
                }

                $ext = strtolower(trim((string) ($file->Extension ?: pathinfo($fullPath, PATHINFO_EXTENSION))));
                $fileName = (string) ($file->Filename ?: basename($fullPath));

                if (in_array($ext, $imageExts, true)) {
                    $this->appendImageAttachmentPage($mpdf, $fullPath, $ext, $assetTitle, $fileName, $tempDir, $tempFiles);
                } elseif ($ext === 'pdf') {
                    $this->appendPdfAttachmentPages($mpdf, $fullPath, $assetTitle, $fileName);
                }
            }
        }
    }

    protected function appendImageAttachmentPage(\Mpdf\Mpdf $mpdf, string $fullPath, string $ext, string $assetTitle, string $fileName, string $tempDir, array &$tempFiles): void
    {
        $renderPath = $fullPath;

        if ($ext === 'webp') {
            if (!function_exists('imagecreatefromwebp')) {
                $this->appendNoticePage($mpdf, $assetTitle, $fileName, 'Format gambar WebP tidak dapat dirender pada ekstensi GD server saat ini.');
                return;
            }
            $img = @imagecreatefromwebp($fullPath);
            if (!$img) {
                $this->appendNoticePage($mpdf, $assetTitle, $fileName, 'Berkas gambar WebP gagal dibaca.');
                return;
            }
            $tmpPng = $tempDir . DIRECTORY_SEPARATOR . 'qr_att_' . uniqid('', true) . '.png';
            imagepng($img, $tmpPng);
            imagedestroy($img);
            $tempFiles[] = $tmpPng;
            $renderPath = $tmpPng;
        }

        $mpdf->AddPage('P', '', '', '', '', 10, 10, 10, 10);

        $safeTitle = htmlspecialchars($assetTitle, ENT_QUOTES, 'UTF-8');
        $safeFile = htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8');
        $safeSrc = htmlspecialchars($renderPath, ENT_QUOTES, 'UTF-8');

        $pageHtml = '<div style="font-family: Arial, sans-serif;">'
            . '<table style="width:100%; border-collapse:collapse; border-bottom:1.5pt solid #0d3b66; margin-bottom:6mm;">'
            . '<tr>'
            . '<td style="padding-bottom:2mm; font-size:10pt; font-weight:bold; color:#0d3b66;">Lampiran Gambar Aset</td>'
            . '<td style="padding-bottom:2mm; text-align:right; font-size:8pt; color:#555;">' . $safeTitle . '</td>'
            . '</tr>'
            . '<tr>'
            . '<td colspan="2" style="padding-bottom:2mm; font-size:8pt; color:#444;">Nama File: <strong>' . $safeFile . '</strong></td>'
            . '</tr>'
            . '</table>'
            . '<div style="text-align:center; padding-top:4mm;">'
            . '<img src="' . $safeSrc . '" style="max-width:185mm; max-height:235mm; border:0.5pt solid #ddd;" />'
            . '</div>'
            . '</div>';

        $mpdf->WriteHTML($pageHtml);
    }

    protected function appendPdfAttachmentPages(\Mpdf\Mpdf $mpdf, string $fullPath, string $assetTitle, string $fileName): void
    {
        try {
            $pageCount = $mpdf->SetSourceFile($fullPath);
            if ($pageCount < 1) {
                $this->appendNoticePage($mpdf, $assetTitle, $fileName, 'Dokumen PDF lampiran tidak memiliki halaman yang dapat digabungkan.');
                return;
            }

            for ($p = 1; $p <= $pageCount; $p++) {
                $tplId = $mpdf->ImportPage($p);
                $size = $mpdf->GetTemplateSize($tplId);
                $width = (float) ($size['width'] ?? 210);
                $height = (float) ($size['height'] ?? 297);
                $orientation = $width > $height ? 'L' : 'P';

                $mpdf->AddPageByArray([
                    'orientation'   => $orientation,
                    'sheet-size'    => [$width, $height],
                    'margin-left'   => 0,
                    'margin-right'  => 0,
                    'margin-top'    => 0,
                    'margin-bottom' => 0,
                ]);
                $mpdf->UseTemplate($tplId);
            }
        } catch (\Throwable $e) {
            $this->appendNoticePage(
                $mpdf,
                $assetTitle,
                $fileName,
                'Lampiran PDF tidak dapat digabungkan otomatis (kemungkinan menggunakan kompresi PDF 1.5+ yang tidak didukung parser FPDI).'
            );
        }
    }

    protected function appendNoticePage(\Mpdf\Mpdf $mpdf, string $assetTitle, string $fileName, string $reason): void
    {
        $mpdf->AddPage('P', '', '', '', '', 12, 12, 12, 12);
        $safeTitle = htmlspecialchars($assetTitle, ENT_QUOTES, 'UTF-8');
        $safeFile = htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8');
        $safeReason = htmlspecialchars($reason, ENT_QUOTES, 'UTF-8');

        $html = '<div style="font-family: Arial, sans-serif; border:1pt solid #d97706; background:#fffbeb; padding:8mm;">'
            . '<div style="font-size:11pt; font-weight:bold; color:#92400e; margin-bottom:2mm;">Catatan Lampiran Dokumen</div>'
            . '<div style="font-size:9pt; color:#333; margin-bottom:1.5mm;"><strong>Aset:</strong> ' . $safeTitle . '</div>'
            . '<div style="font-size:9pt; color:#333; margin-bottom:3mm;"><strong>File:</strong> ' . $safeFile . '</div>'
            . '<div style="font-size:8.5pt; color:#78350f;">' . $safeReason . '</div>'
            . '</div>';

        $mpdf->WriteHTML($html);
    }

    protected function ensureMpdfTempDir(): string
    {
        $tempDir = storage_path('app/mpdf');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        return $tempDir;
    }

    protected function cleanupTempFiles(array $tempFiles): void
    {
        foreach ($tempFiles as $path) {
            if (is_string($path) && is_file($path)) {
                @unlink($path);
            }
        }
    }
}
