<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SppdApprovalController extends Controller
{
    public function index()
    {
        if (!SppdController::isApprover()) {
            abort(403, 'Hanya approver yang dapat mengakses halaman ini');
        }
        return view('humanresources.sppd.approval.index');
    }

    public function data()
    {
        if (stristr($_SERVER["HTTP_ACCEPT"] ?? "", "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        if (!SppdController::isApprover()) {
            echo "<rows></rows>";
            return;
        }

        $nomor = 1;
        $Query = DB::table('sppd')
            ->where('Approver_ID', Auth::user()->id)
            ->where('Status', 'SUBMITTED')
            ->orderBy('ID', 'DESC')
            ->get();

        echo "<rows>";
        foreach ($Query as $Fetch) {
            $route = $Fetch->Route_Start . ' - ' . $Fetch->Route_Finish;
            echo "<row id=\"" . $Fetch->ID . "\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->SPPD_No) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Requester_Name) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Department) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Destination) . "</cell>";
            echo "<cell>" . htmlspecialchars($route) . "</cell>";
            echo "<cell>" . number_format((float) $Fetch->Estimated_Cost, 0, ',', '.') . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Status) . "</cell>";
            echo "<cell>" . ($Fetch->Created_Date ? date('d-m-Y H:i', strtotime($Fetch->Created_Date)) : '-') . "</cell>";
            echo "</row>";
            $nomor++;
        }
        echo "</rows>";
    }

    public function detail(Request $request)
    {
        if (!SppdController::isApprover()) {
            abort(403, 'Hanya approver yang dapat mengakses halaman ini');
        }

        $sppd = DB::table('sppd')->where('ID', $request->ID)->first();
        if (!$sppd) {
            abort(404);
        }
        if ((int) $sppd->Approver_ID !== (int) Auth::user()->id) {
            abort(403, 'SPPD ini bukan untuk approval Anda');
        }

        $costs = DB::table('sppd_cost')->where('SPPD_ID', $sppd->ID)->get();
        $logs  = DB::table('sppd_log')->where('SPPD_ID', $sppd->ID)->orderBy('ID', 'ASC')->get();
        $canApprove = $sppd->Status === 'SUBMITTED';

        return view('humanresources.sppd.approval.detail', compact('sppd', 'costs', 'logs', 'canApprove'));
    }

    public function approval(Request $request)
    {
        $user = Auth::user();

        if (!SppdController::isApprover()) {
            return response()->json(['status' => 'error', 'message' => 'Tidak berhak melakukan approval'], 403);
        }

        $id           = $request->ID;
        $approvalType = $request->Approval ?? '1';
        $note         = trim($request->Note ?? '');

        $sppd = DB::table('sppd')->where('ID', $id)->first();
        if (!$sppd) {
            return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);
        }
        if ((int) $sppd->Approver_ID !== (int) $user->id) {
            return response()->json(['status' => 'error', 'message' => 'SPPD ini bukan untuk approval Anda'], 403);
        }
        if ($sppd->Status !== 'SUBMITTED') {
            return response()->json([
                'status'  => 'error',
                'message' => "SPPD ini sudah diproses (status: $sppd->Status)",
            ], 422);
        }

        $nama = $user->Name ?: $user->username;
        $timestamp = date('Y-m-d H:i:s');
        $existingNote = $sppd->Approval_Note ? $sppd->Approval_Note . "\n" : '';
        $newNote = $existingNote . "$timestamp " . ($approvalType == 2 ? 'REJECTED' : 'ACCEPTED') . " by $user->username :: $note";
        $newStatus = $approvalType == 2 ? 'REJECTED' : 'ACCEPTED';

        DB::beginTransaction();
        try {
            DB::table('sppd')->where('ID', $sppd->ID)->update([
                'Status'        => $newStatus,
                'Approval_Note' => $newNote,
                'Approved_At'   => $approvalType == 2 ? null : now(),
                'Updated_By'    => $user->username,
                'Updated_Date'  => now(),
            ]);

            DB::table('sppd_log')->insert([
                'SPPD_ID'    => $sppd->ID,
                'Action'     => $newStatus,
                'Actor_ID'   => $user->id,
                'Actor_Name' => $nama,
                'Note'       => $note ?: ($approvalType == 2 ? 'Request ditolak' : 'Request disetujui'),
                'Created_At' => now(),
            ]);

            DB::commit();

            Log::info('SPPD approval: id=' . $sppd->ID . ' newStatus=' . $newStatus . ' by=' . $user->username);

            return response()->json([
                'status'    => 'success',
                'message'   => 'Berhasil melakukan approval',
                'newStatus' => $newStatus,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SPPD approval failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
