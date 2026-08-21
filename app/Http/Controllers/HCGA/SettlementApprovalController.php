<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SettlementApprovalController extends Controller
{
    public function index()
    {
        if (!SettlementController::isHcgaVerifier()) {
            abort(403, 'Hanya user HCGA yang dapat mengakses halaman ini');
        }
        return view('humanresources.settlement.approval.index');
    }

    public function data()
    {
        if (stristr($_SERVER["HTTP_ACCEPT"] ?? "", "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        if (!SettlementController::isHcgaVerifier()) {
            echo "<rows></rows>";
            return;
        }

        $nomor = 1;
        $Query = DB::table('settlement_sppd')
            ->where('Status', 'SUBMITTED')
            ->orderBy('ID', 'DESC')
            ->get();

        echo "<rows>";
        foreach ($Query as $Fetch) {
            $expenses = DB::table('settlement_sppd_expense')->where('Settlement_Sppd_ID', $Fetch->ID)->get();
            $total = 0;
            foreach ($expenses as $e) {
                $total += (float) $e->Cost;
            }

            echo "<row id=\"" . $Fetch->ID . "\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->STTL_No) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Requester_Name) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Department) . "</cell>";
            echo "<cell>" . count($expenses) . "</cell>";
            echo "<cell>" . number_format($total, 0, ',', '.') . "</cell>";
            echo "<cell>" . ($Fetch->Created_Date ? date('d-m-Y H:i', strtotime($Fetch->Created_Date)) : '-') . "</cell>";
            echo "</row>";
            $nomor++;
        }
        echo "</rows>";
    }

    public function detail(Request $request)
    {
        if (!SettlementController::isHcgaVerifier()) {
            abort(403, 'Hanya user HCGA yang dapat mengakses halaman ini');
        }

        $settlement = DB::table('settlement_sppd')->where('ID', $request->ID)->first();
        if (!$settlement) {
            abort(404);
        }

        $expenses = DB::table('settlement_sppd_expense')->where('Settlement_Sppd_ID', $settlement->ID)->get();
        $logs = DB::table('settlement_sppd_log')->where('Settlement_Sppd_ID', $settlement->ID)->orderBy('ID', 'ASC')->get();
        $invoice = $settlement->Invoice_File ? DB::table('meta_files')->where('ID', $settlement->Invoice_File)->first() : null;
        $canApprove = $settlement->Status === 'SUBMITTED' && SettlementController::isDeptHead();

        return view('humanresources.settlement.approval.detail', compact('settlement', 'expenses', 'logs', 'invoice', 'canApprove'));
    }

    public function approval(Request $request)
    {
        $user = Auth::user();

        if (!SettlementController::isDeptHead()) {
            return response()->json(['status' => 'error', 'message' => 'Hanya HCGA Dept Head yang berhak verifikasi'], 403);
        }

        $id           = $request->ID;
        $approvalType = $request->Approval ?? '1';
        $note         = trim($request->Note ?? '');

        $settlement = DB::table('settlement_sppd')->where('ID', $id)->first();
        if (!$settlement) {
            return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);
        }
        if ($settlement->Status !== 'SUBMITTED') {
            return response()->json([
                'status'  => 'error',
                'message' => "Request ini sudah diproses (status: $settlement->Status)",
            ], 422);
        }

        $nama = $user->Name ?: $user->username;
        $timestamp = date('Y-m-d H:i:s');
        $existingNote = $settlement->Approval_Note ? $settlement->Approval_Note . "\n" : '';
        $newNote = $existingNote . "$timestamp " . ($approvalType == 2 ? 'REJECTED' : 'APPROVED') . " by $user->username :: $note";
        $newStatus = $approvalType == 2 ? 'REJECTED' : 'APPROVED';

        DB::beginTransaction();
        try {
            DB::table('settlement_sppd')->where('ID', $settlement->ID)->update([
                'Status'         => $newStatus,
                'Approval_Note'  => $newNote,
                'Approved_At'    => $approvalType == 2 ? null : now(),
                'Validator_ID'   => $approvalType == 2 ? $settlement->Validator_ID : $user->id,
                'Validator_Name' => $approvalType == 2 ? $settlement->Validator_Name : $nama,
                'Updated_By'     => $user->username,
                'Updated_Date'   => now(),
            ]);

            DB::table('settlement_sppd_log')->insert([
                'Settlement_Sppd_ID' => $settlement->ID,
                'Action'             => $newStatus,
                'Actor_ID'           => $user->id,
                'Actor_Name'         => $nama,
                'Note'               => $note ?: ($approvalType == 2 ? 'Request settlement ditolak' : 'Request settlement disetujui'),
                'Created_At'         => now(),
            ]);

            DB::commit();

            Log::info('Settlement approval: id=' . $settlement->ID . ' newStatus=' . $newStatus . ' by=' . $user->username);

            return response()->json([
                'status'    => 'success',
                'message'   => 'Berhasil melakukan verifikasi',
                'newStatus' => $newStatus,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Settlement approval failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
