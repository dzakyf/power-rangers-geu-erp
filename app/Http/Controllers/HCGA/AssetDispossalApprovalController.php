<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\HCGA\AssetLifecycleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssetDispossalApprovalController extends Controller
{
    public function index()
    {
        return view('humanresources.asset-dispossal.approval.index');
    }

    public function data()
    {
        if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml"); } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        $priv = Auth::user()->Priv_ID;
        $nik = Auth::user()->Emp_ID;

        $getDisposal = DB::table('asset_disposal')
                        ->whereIn("Status", ["CREATED", "UPDATED"]);
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

    public function detail(Request $request)
    {
        $ad = DB::table('asset_disposal')->where("Disposal_No", $request->Disposal_No)->first();
        return view('humanresources.asset-dispossal.approval.detail', compact('ad'));
    }

    public function store(Request $request)
    {
        $r = $request->all();

        $disposalNo = $r['Disposal_No'] ?? null;
        $status = $r['status'] ?? 'APPROVED';
        $approvalNote = $r['Approval_Note'] ?? '';
        $username = Auth::user()->username;

        if (!$disposalNo) {
            return response()->json([
                'status' => 'error',
                'message' => 'Disposal_No tidak ditemukan',
            ]);
        }

        $dataDisposal = DB::table('asset_disposal')->where('Disposal_No', $disposalNo)->first();
        if ($dataDisposal && !empty($dataDisposal->Approval_Request_ID)) {
            try {
                $action = (strtoupper((string) $status) === 'REJECTED') ? 'REJECT' : 'APPROVE';
                app(\App\Services\Approval\AssetLifecycleApprovalService::class)->actOnRequest(
                    (int) $dataDisposal->Approval_Request_ID,
                    (int) Auth::id(),
                    $action,
                    (string) $approvalNote
                );
                return response()->json([
                    'status'  => 'success',
                    'message' => "Berhasil memproses approval disposal $disposalNo",
                ]);
            } catch (\Throwable $e) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ], 422);
            }
        }

        $update = [
            'Status' => $status,
            'Approval_Note' => $approvalNote,
            'Updated_By' => $username,
            'Updated_Date' => now(),
        ];

        // Simpan tanda tangan persetujuan yang terisi
        $signatureFields = [
            'Pemohon_Name', 'Pemohon_Date',
            'Kadiv_Name', 'Kadiv_Date',
            'AstOps_Name', 'AstOps_Date',
            'VPO_Name', 'VPO_Date',
            'HCGA_Name', 'HCGA_Date',
            'Direktur_Name', 'Direktur_Date',
        ];
        foreach ($signatureFields as $field) {
            if (isset($r[$field])) {
                $update[$field] = ($r[$field] == '') ? null : $r[$field];
            }
        }

        DB::table('asset_disposal')->where('Disposal_No', $disposalNo)->update($update);

        $dataDisposal = DB::table('asset_disposal')->where('Disposal_No', $disposalNo)->first();

        if ($status === 'APPROVED' && $dataDisposal && !empty($dataDisposal->Asset_Serial_No)) {
            app(AssetLifecycleService::class)->markDisposed(
                $dataDisposal->Asset_Serial_No,
                $dataDisposal->Disposal_Actual_Date ?? null,
                $username
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => "Berhasil memproses approval disposal $disposalNo",
        ]);
    }
}