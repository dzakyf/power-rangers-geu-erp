<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\HCGA\AssetQrService;
use App\Services\HCGA\AssetVehicleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssetListController extends Controller
{
    /** @var AssetVehicleService */
    protected $assetService;

    /** @var AssetQrService */
    protected $qrService;

    public function __construct(AssetVehicleService $assetService, AssetQrService $qrService)
    {
        $this->assetService = $assetService;
        $this->qrService = $qrService;
    }

    public function index()
    {
        $summary = $this->assetService->getAssetSummaryStats();
        $filterOptions = $this->assetService->getFilterOptions();

        return view('humanresources.asset-lists.index', compact('summary', 'filterOptions'));
    }

    public function data(Request $request)
    {
        $httpAccept = $_SERVER['HTTP_ACCEPT'] ?? 'text/xml';
        if (!headers_sent() && stristr($httpAccept, 'application/xhtml+xml')) {
            header('Content-type: application/xhtml+xml');
        } elseif (!headers_sent()) {
            header('Content-type: text/xml');
        }
        echo "<?xml version='1.0' encoding='iso-8859-1' ?>\n";

        $rows = $this->assetService->getAssetGridData([
            'category'     => $request->input('category'),
            'sub_category' => $request->input('sub_category'),
            'location'     => $request->input('location'),
            'status'       => $request->input('status'),
            'kondisi'      => $request->input('kondisi'),
        ]);

        $nomor = 1;
        echo '<rows>';
        foreach ($rows as $dapat) {
            echo '<row id="' . (int) $dapat->ID . '">';
            echo '<cell>' . $nomor . '</cell>';
            echo '<cell>' . $this->xml($dapat->Asset_No ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->Asset_Category ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->Asset_Sub_Category ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->Asset_Type ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->Product_Code ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->Product_Description ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->Product_Brand ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->Sub_Product_Type ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->Manufacture_Date ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->Registration_No ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->Buy_Date ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->Buy_Val ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->Asset_Location ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->Asset_Location_Address ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->Asset_Location_City ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->Asset_Location_Department ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->Leasing_Vendor ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->Leasing_No ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->Acc_No ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->Unit_Name ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->Model ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->Source_Production ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->Capacity ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->Part_No ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->SN ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->Jenis_Kendaraan ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->BPKB_No ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->Surat_Jalan_No ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->Asuransi ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->STNK_Tahunan ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->STNK_5_Tahun ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->Jadwal_KIR ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->Berkas_KIR ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->Subsid_Mypertamina ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->Barcode_Kode ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->Barcode_Kode_PSAK ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->Tanggal_PM_Terakhir ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->Tanggal_Jadwal_PM ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->KM_Saat_Ini ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->KM_Jadwal_PM ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->Interval_PM ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->Tanggal_Penghapusan ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->User_Responsible ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->User_Sebelumnya ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->Link_QR ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->QR_Aset ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->Status ?? '') . '</cell>';
            echo '<cell>' . $this->xml($dapat->Kondisi_Aset ?? 'BAIK') . '</cell>';
            echo '<cell>' . $this->xml($dapat->Note ?? '') . '</cell>';
            echo '</row>';
            $nomor++;
        }
        echo '</rows>';
    }

    public function create()
    {
        try {
            $formData = $this->assetService->getCreateFormData();
            return view('humanresources.asset-lists.create', $formData);
        } catch (\Throwable $e) {
            Log::error('AssetListController::create error: ' . $e->getMessage());
            $msg = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            return response(
                '<div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">'
                . '<i class="fa fa-exclamation-triangle me-2"></i>'
                . '<strong>Gagal memuat form tambah asset:</strong> ' . $msg
                . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'
                . '</div>',
                500
            );
        }
    }

    public function store(Request $request)
    {
        $isPo = $request->input('Incoming_Type') === 'PO';
        $rules = [
            'Asset_Category'     => 'required|string|max:100',
            'Asset_Sub_Category' => 'required|string|max:100',
        ];
        if (!$isPo) {
            $rules['Product_Description'] = 'required|string|max:500';
        } else {
            $rules['PO_No'] = 'required|string|max:50';
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules, [
            'Asset_Category.required'      => 'Asset Category wajib dipilih.',
            'Asset_Sub_Category.required'  => 'Asset Sub Category wajib dipilih.',
            'Product_Description.required' => 'Product Description wajib diisi.',
            'PO_No.required'               => 'Nomor PO wajib dipilih.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $user = Auth::user();
            $username = $user ? ($user->username ?? 'system') : 'system';
            $location = $user ? ($user->Location ?? 'GEU-HO') : 'GEU-HO';

            $documents = $request->file('documents') ?: ($request->file('photos') ?: []);
            if (!is_array($documents)) {
                $documents = $documents ? [$documents] : [];
            }

            $created = $this->assetService->createAsset($request->all(), $username, $location, $documents);

            return response()->json([
                'status'  => 'success',
                'message' => 'Asset berhasil ditambahkan (' . implode(', ', $created) . ').',
                'assets'  => $created,
            ]);
        } catch (\Throwable $e) {
            Log::error('AssetListController::store error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function detail(Request $request)
    {
        $assetNo = trim((string) ($request->input('Task_ID') ?: $request->input('Asset_No', '')));
        if ($assetNo === '') {
            abort(404, 'Asset No tidak ditemukan.');
        }

        $detail = $this->assetService->getAssetDetail($assetNo);
        if (!$detail) {
            abort(404, 'Data asset tidak ditemukan.');
        }

        return view('humanresources.asset-lists.detail', $detail);
    }

    public function update(Request $request)
    {
        $id = (int) $request->input('ID', 0);
        if ($id <= 0) {
            return response()->json(['status' => 'error', 'message' => 'ID Asset tidak valid.'], 422);
        }

        try {
            $username = Auth::user()->username ?? 'system';
            $this->assetService->updateAsset($id, $request->all(), $username);

            return response()->json([
                'status'  => 'success',
                'message' => 'Data asset berhasil diperbarui.',
            ]);
        } catch (\Throwable $e) {
            Log::error('AssetListController::update error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function updateStnk(Request $request)
    {
        $request->validate([
            'Asset_No'        => 'required|string|max:60',
            'Jenis'           => 'required|in:TAHUNAN,5_TAHUNAN',
            'Tanggal_Berlaku' => 'required|date',
            'Catatan'         => 'nullable|string|max:255',
        ]);

        try {
            $username = Auth::user()->username ?? 'system';
            $this->assetService->updateStnk(
                $request->input('Asset_No'),
                $request->input('Jenis'),
                $request->input('Tanggal_Berlaku'),
                $request->input('Catatan'),
                $username
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'STNK berhasil diperbarui dan tercatat di riwayat.',
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function updateKeur(Request $request)
    {
        $request->validate([
            'Asset_No'    => 'required|string|max:60',
            'Tanggal_KIR' => 'required|date',
            'Berkas_KIR'  => 'nullable|string|max:255',
            'Catatan'     => 'nullable|string|max:255',
        ]);

        try {
            $username = Auth::user()->username ?? 'system';
            $nextDate = $this->assetService->updateKeur(
                $request->input('Asset_No'),
                $request->input('Tanggal_KIR'),
                $request->input('Berkas_KIR'),
                $request->input('Catatan'),
                $username
            );

            return response()->json([
                'status'    => 'success',
                'message'   => 'Data KEUR/KIR berhasil diperbarui. Jadwal berikutnya: ' . $nextDate,
                'next_date' => $nextDate,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function uploadPhoto(Request $request)
    {
        $assetNo = trim((string) $request->input('Asset_No', ''));
        if ($assetNo === '') {
            return response()->json(['status' => 'error', 'message' => 'Asset No tidak dikenali.'], 422);
        }

        $files = $request->file('photos');
        if (empty($files)) {
            return response()->json(['status' => 'error', 'message' => 'Tidak ada berkas yang dipilih.'], 422);
        }
        if (!is_array($files)) {
            $files = [$files];
        }

        try {
            $username = Auth::user()->username ?? 'system';
            $result = $this->assetService->uploadPhotos($assetNo, $files, $username);
            $msg = "{$result['uploaded']} foto berhasil diunggah.";
            if (!empty($result['rejected'])) {
                $msg .= ' Ditolak: ' . implode('; ', $result['rejected']);
            }

            return response()->json([
                'status'   => $result['uploaded'] > 0 ? 'success' : 'error',
                'message'  => $msg,
                'uploaded' => $result['uploaded'],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function deletePhoto(Request $request)
    {
        $photoId = (int) ($request->input('Photo_ID') ?: $request->input('photo_id', 0));
        $assetNo = trim((string) $request->input('Asset_No', ''));
        if ($photoId <= 0) {
            return response()->json(['status' => 'error', 'message' => 'Photo ID tidak valid.'], 422);
        }

        try {
            $username = Auth::user()->username ?? 'system';
            $this->assetService->deletePhoto($photoId, $assetNo, $username);

            return response()->json([
                'status'  => 'success',
                'message' => 'Foto berhasil dihapus.',
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function subCategory(Request $request)
    {
        $category = (string) $request->input('Category', '');
        $rows = $this->assetService->getSubCategories($category);

        $html = "<select id='Asset_Sub_Category' name='Asset_Sub_Category' class='form-select form-control' onchange=\"loadassettype(); if ((this.value.toLowerCase().includes('vehicle')) && typeof toggleVehicleFieldsNew === 'function') { toggleVehicleFieldsNew(); }\">";
        $html .= "<option value=''>-- Pilih Sub Category --</option>";
        foreach ($rows as $value) {
            $sub = htmlspecialchars($value->Sub_Group, ENT_QUOTES, 'UTF-8');
            $init = htmlspecialchars($value->Initial, ENT_QUOTES, 'UTF-8');
            $html .= "<option value='{$sub}'>{$sub} - {$init}</option>";
        }
        $html .= '</select>';

        return response($html);
    }

    public function subSubCategory(Request $request)
    {
        $category = (string) $request->input('Category', '');
        $subCat   = (string) $request->input('Sub_Category', '');
        $rows = $this->assetService->getSubSubCategories($category, $subCat);

        $html = "<select id='Asset_Type' name='Asset_Type' class='form-select form-control'>";
        $html .= "<option value=''>-- Pilih Asset Type --</option>";
        foreach ($rows as $value) {
            $type = htmlspecialchars($value->Sub_Sub_Group, ENT_QUOTES, 'UTF-8');
            $init = htmlspecialchars($value->Initial, ENT_QUOTES, 'UTF-8');
            $html .= "<option value='{$type}'>{$type} - {$init}</option>";
        }
        $html .= '</select>';

        return response($html);
    }

    public function getDataFromPO(Request $request)
    {
        if ($request->pono == '') {
            return response()->json([
                'status'  => 'empty',
                'message' => 'no PO',
            ]);
        }
        $get = DB::table('purchase_order as po')
            ->where('PO_No', $request->pono)
            ->leftJoin('supplier', 'po.Supplier', '=', 'supplier.Supplier_ID')
            ->select('po.PO_No', 'po.Subtotal', 'supplier.Company_Name')
            ->first();

        if ($get) {
            $get->Details = DB::table('purchase_order_dtl as prd')
                ->where('PO_No', $request->pono)
                ->leftJoin('product_code as pc', 'prd.Product_Code', '=', 'pc.Product_Code')
                ->select('prd.Category', 'prd.Product_Code', 'prd.SN', 'prd.Product_Desc', 'prd.Product_Type', 'prd.Purchase_Type', 'prd.Qty', 'prd.Total', 'pc.Specification', 'pc.Product_Brand', 'pc.Supplies_Type')
                ->get();
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Data PO',
            'data'    => $get,
        ]);
    }

    public function getDataFromWH(Request $request)
    {
        $gudang = DB::table('gudang_list')->where('Gudang_ID', $request->wh)->first();

        return response()->json([
            'status'  => 'success',
            'message' => 'Data WH',
            'data'    => $gudang,
        ]);
    }

    public function qrImage(Request $request)
    {
        $mode = strtolower(trim((string) $request->query('mode', 'asset'))) === 'location' ? 'location' : 'asset';
        $code = trim((string) $request->query('code', ''));
        abort_if($code === '', 404);

        $targetUrl = $mode === 'location'
            ? $this->qrService->buildPublicLocationUrl($code)
            : $this->qrService->buildPublicAssetUrl($code);

        $svg = $this->qrService->generateQrSvg($targetUrl, 320);

        return response($svg, 200, [
            'Content-Type'  => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function qrPrint(Request $request)
    {
        $mode = strtolower(trim((string) $request->input('mode', 'location'))) === 'asset' ? 'asset' : 'location';
        $layout = strtolower(trim((string) $request->input('layout', 'card'))) === 'compact' ? 'compact' : 'card';

        $activeFilters = [
            'category'     => trim((string) $request->input('category', '')),
            'sub_category' => trim((string) $request->input('sub_category', '')),
            'location'     => trim((string) $request->input('location', '')),
            'status'       => trim((string) $request->input('status', '')),
            'kondisi'      => trim((string) $request->input('kondisi', '')),
        ];

        $items = $mode === 'location'
            ? $this->qrService->getLocationItems()
            : $this->qrService->getAssetItems($activeFilters);

        $codesParam = $request->input('codes', []);
        if (!is_array($codesParam)) {
            $codesParam = trim((string) $codesParam) === '' ? [] : explode(',', (string) $codesParam);
        }
        $selectedCodes = array_values(array_unique(array_filter(array_map(function ($val) {
            return trim((string) $val);
        }, $codesParam), function ($val) {
            return $val !== '';
        })));

        $filterFlag = $request->input('filter');
        $hasFilter = $request->has('codes') || (is_scalar($filterFlag) && (string) $filterFlag === '1');

        $selectedMap = array_flip($selectedCodes);
        if ($hasFilter) {
            $selectedItems = $items->filter(function ($item) use ($mode, $selectedMap) {
                $key = $mode === 'location' ? (string) $item->code : (string) $item->Asset_No;
                return isset($selectedMap[$key]);
            })->values();
        } else {
            $selectedItems = $items;
        }

        $action = strtolower(trim((string) $request->input('action', '')));
        $download = $request->boolean('download') || $action === 'download';
        $generatePdf = $action === 'print' || $action === 'download' || $download;

        $filterOptions = $this->assetService->getFilterOptions();

        if (!$generatePdf) {
            return view('humanresources.asset-lists.qr-print', compact(
                'mode',
                'layout',
                'items',
                'selectedItems',
                'selectedCodes',
                'hasFilter',
                'activeFilters',
                'filterOptions'
            ));
        }

        if ($selectedItems->isEmpty()) {
            return view('humanresources.asset-lists.qr-print', [
                'mode'          => $mode,
                'layout'        => $layout,
                'items'         => $items,
                'selectedItems' => $selectedItems,
                'selectedCodes' => $selectedCodes,
                'hasFilter'     => $hasFilter,
                'activeFilters' => $activeFilters,
                'filterOptions' => $filterOptions,
                'warning'       => 'Pilih minimal satu item sebelum mencetak PDF QR.',
            ]);
        }

        $viewName = $layout === 'compact'
            ? 'humanresources.asset-lists.qr-print-pdf-compact'
            : 'humanresources.asset-lists.qr-print-pdf';

        $tempDir = storage_path('app/mpdf');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $margin = $layout === 'compact' ? 6 : 8;
        $mpdf = new \Mpdf\Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'orientation'   => 'P',
            'margin_left'   => $margin,
            'margin_right'  => $margin,
            'margin_top'    => $margin,
            'margin_bottom' => $margin,
            'tempDir'       => $tempDir,
        ]);

        $docTitle = ($mode === 'location' ? 'QR Lokasi Asset' : 'QR Asset Individual')
            . ($layout === 'compact' ? ' (Stiker)' : ' (Kartu)');
        $mpdf->SetTitle($docTitle);

        $logoRaw = $this->qrService->logoRawPng(public_path('assets/images/small-logo.png'));
        $logoSrc = null;
        if ($logoRaw !== null) {
            $mpdf->imageVars['hcga_logo'] = $logoRaw;
            $logoSrc = 'var:hcga_logo';
        }

        $cssHtml = view($viewName, [
            'mode'          => $mode,
            'selectedItems' => collect(),
            'qrImages'      => [],
            'logo'          => $logoSrc,
            'part'          => 'css',
        ])->render();
        $mpdf->WriteHTML($cssHtml, \Mpdf\HTMLParserMode::HEADER_CSS);

        foreach ($selectedItems->chunk(4) as $itemChunk) {
            $qrImages = [];
            foreach ($itemChunk as $item) {
                $code = $mode === 'location' ? (string) $item->code : (string) $item->Asset_No;
                $targetUrl = $mode === 'location'
                    ? $this->qrService->buildPublicLocationUrl($code)
                    : $this->qrService->buildPublicAssetUrl($code);
                $qrImages[$code] = $this->qrService->generateQrDataUri($targetUrl);
            }

            $bodyChunk = view($viewName, [
                'mode'          => $mode,
                'selectedItems' => $itemChunk->values(),
                'qrImages'      => $qrImages,
                'logo'          => $logoSrc,
                'part'          => 'body',
            ])->render();
            $mpdf->WriteHTML($bodyChunk, \Mpdf\HTMLParserMode::HTML_BODY);
        }

        $prefix = ($mode === 'location' ? 'QR-AssetLoc-' : 'QR-Asset-')
            . ($layout === 'compact' ? 'Sticker-' : 'Card-');
        $filename = $prefix . Carbon::now()->format('YmdHis') . '.pdf';
        $disposition = $download ? 'attachment' : 'inline';

        return response(
            $mpdf->Output($filename, \Mpdf\Output\Destination::STRING_RETURN),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => $disposition . '; filename="' . $filename . '"',
            ]
        );
    }

    public function publicLocationQrPdf(Request $request, $location)
    {
        $locCode = trim((string) $location);
        $sig = $request->query('sig');

        abort_if($locCode === '' || !$this->qrService->verifySignature('location', $locCode, $sig), 403, 'QR signature tidak valid.');

        $pdf = $this->qrService->renderPublicLocationPdf($locCode);

        return response($pdf['binary'], 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $pdf['filename'] . '"',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate',
            'X-Robots-Tag'        => 'noindex, nofollow',
        ]);
    }

    public function publicAssetQrPdf(Request $request, $assetNo)
    {
        $code = trim((string) $assetNo);
        $sig = $request->query('sig');

        abort_if($code === '' || !$this->qrService->verifySignature('asset', $code, $sig), 403, 'QR signature tidak valid.');

        $pdf = $this->qrService->renderPublicAssetPdf($code);
        abort_if(!$pdf, 404, 'Data asset tidak ditemukan.');

        return response($pdf['binary'], 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $pdf['filename'] . '"',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate',
            'X-Robots-Tag'        => 'noindex, nofollow',
        ]);
    }

    protected function xml($value): string
    {
        return htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
