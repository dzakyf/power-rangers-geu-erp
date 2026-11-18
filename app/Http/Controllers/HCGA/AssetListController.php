<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\HCGA\AssetVehicleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssetListController extends Controller
{
    /** @var AssetVehicleService */
    protected $assetService;

    public function __construct(AssetVehicleService $assetService)
    {
        $this->assetService = $assetService;
    }

    public function index()
    {
        return view('humanresources.asset-lists.index');
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
            'sub_category' => $request->input('sub_category'),
            'status'       => $request->input('status'),
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
            echo '<cell>' . $this->xml($dapat->Note ?? '') . '</cell>';
            echo '</row>';
            $nomor++;
        }
        echo '</rows>';
    }

    public function create()
    {
        $formData = $this->assetService->getCreateFormData();
        return view('humanresources.asset-lists.create', $formData);
    }

    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            $username = $user ? ($user->username ?? 'system') : 'system';
            $location = $user ? ($user->Location ?? 'GEU-HO') : 'GEU-HO';

            $created = $this->assetService->createAsset($request->all(), $username, $location);

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

    protected function xml($value): string
    {
        return htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
