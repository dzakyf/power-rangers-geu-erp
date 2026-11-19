<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssetDispossalController extends Controller
{
    public function index()
    {
        return view('humanresources.asset-dispossal.index');
    }

    public function data(Request $request)
    {
        if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml"); } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        $priv = Auth::user()->Priv_ID;
        $nik = Auth::user()->Emp_ID;

        $getDisposal = DB::table('asset_disposal');
        if ($priv != 'HCGA' && $priv != 'ICT') {
            $getDisposal->where("User_NIK", $nik);
        }
        $getDisposal = $getDisposal->orderBy("ID", "desc")->get();
        $nomor = 1;
        echo "<rows>";
        foreach ($getDisposal as $value) {
            echo "<row id='$value->ID'>";
                echo "<cell>$nomor</cell>";
                echo "<cell>".htmlspecialchars($value->Disposal_No)."</cell>";
                echo "<cell>".htmlspecialchars($value->Disposal_Date)."</cell>";
                echo "<cell>".htmlspecialchars($value->User_Name)."</cell>";
                echo "<cell>".htmlspecialchars($value->User_Position ?? '')."</cell>";
                echo "<cell>".htmlspecialchars($value->Asset_Name)."</cell>";
                echo "<cell>".htmlspecialchars($value->Asset_Serial_No ?? '')."</cell>";
                echo "<cell>".htmlspecialchars($value->Asset_Condition ?? '')."</cell>";
                echo "<cell>".htmlspecialchars($value->Asset_Location ?? '')."</cell>";
                echo "<cell>".htmlspecialchars($value->Disposal_Reason ?? '')."</cell>";
                echo "<cell>".htmlspecialchars($value->Disposal_Actual_Date ?? '')."</cell>";
                echo "<cell>".htmlspecialchars($value->Status)."</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    public function new()
    {
        $LSP = Auth::user()->Location;
        $NIK = Auth::user()->Emp_ID;
        $Name = Auth::user()->Name;
        $Position = Auth::user()->Position;
        $counter = DB::table('asset_disposal')->where("Created_By", "LIKE", date("Y-m")."%")->count() + 1;
        $Disposal_No = "DA/GA/$LSP/".date("ymdhis") . "-" . $counter;
        $assets = DB::table('asset_list')
            ->where(function ($q) {
                $q->whereNull('Status')->orWhere('Status', '!=', 'DISPOSED');
            })
            ->select('Asset_No', 'Product_Description', 'SN', 'Kondisi_Aset', 'Asset_Location', 'Buy_Val', 'Product_Brand', 'Model')
            ->orderBy('Asset_No', 'ASC')
            ->limit(1000)
            ->get();
        return view('humanresources.asset-dispossal.create', compact("Disposal_No", 'NIK', 'Name', 'Position', 'assets'));
    }

    public function store(Request $request)
    {
        $r = $request->all();

        unset($r['_token']);
        unset($r['SubmitAssetDisposal']);

        $r['User_NIK'] = $r['User_NIK'] ?? Auth::user()->Emp_ID;
        $r['User_Name'] = $r['User_Name'] ?? Auth::user()->Name;
        $r['User_Position'] = $r['User_Position'] ?? Auth::user()->Position;
        $r['Created_By'] = Auth::user()->username;
        $r['Created_Date'] = now();
        $r['Status'] = "CREATED";

        // Kosongkan tanggal jika tidak terisi
        foreach (['Disposal_Date', 'Disposal_Actual_Date', 'Pemohon_Date', 'Kadiv_Date',
                  'AstOps_Date', 'VPO_Date', 'HCGA_Date', 'Direktur_Date'] as $dateField) {
            if (isset($r[$dateField]) && $r[$dateField] == '') {
                $r[$dateField] = null;
            }
        }

        $disposalId = DB::table('asset_disposal')->insertGetId($r);

        try {
            $assetNo = trim((string) ($r['Asset_No'] ?? ($r['Asset_Serial_No'] ?? '')));
            app(\App\Services\Approval\AssetLifecycleApprovalService::class)->submit(
                \App\Services\Approval\AssetLifecycleApprovalService::TYPE_ASSET_DISPOSAL,
                (int) $disposalId,
                (string) ($r['Disposal_No'] ?? ('DSP-' . $disposalId)),
                'Pemusnahan Aset ' . ($r['Asset_Name'] ?? $assetNo),
                (int) Auth::id(),
                ['Asset_No' => $assetNo, 'Amount' => (float) ($r['Book_Value'] ?? 0)]
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('AssetDisposal ApprovalWorkflow fallback: ' . $e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'message' => "Berhasil menginput data",
        ]);
    }

    public function detail(Request $request)
    {
        $ad = DB::table('asset_disposal')->where('Disposal_No', $request->Disposal_No)->first();
        return view('humanresources.asset-dispossal.detail', compact('ad'));
    }

    public function update(Request $request)
    {
        $r = $request->all();

        unset($r['ID']);
        unset($r['_token']);
        unset($r['SubmitAssetDisposal']);

        $r['Updated_By'] = Auth::user()->username;
        $r['Updated_Date'] = now();

        foreach (['Disposal_Date', 'Disposal_Actual_Date', 'Pemohon_Date', 'Kadiv_Date',
                  'AstOps_Date', 'VPO_Date', 'HCGA_Date', 'Direktur_Date'] as $dateField) {
            if (isset($r[$dateField]) && $r[$dateField] == '') {
                $r[$dateField] = null;
            }
        }

        DB::table('asset_disposal')->where('ID', $request->ID)->update($r);

        return response()->json([
            'status' => 'success',
            'message' => "Berhasil update data",
        ]);
    }

    public function log(Request $request)
    {
        $ID = (string) ($request->input('Disposal_No') ?? $request->input('ID') ?? '');
        $logs = app(\App\Services\HCGA\AssetLegacyLogService::class)->getDisposalLogs($ID);

        return view('humanresources.asset-dispossal.log', compact('ID', 'logs'));
    }
}