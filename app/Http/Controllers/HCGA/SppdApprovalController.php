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
        // if (!SppdController::isApprover() && !$this->userIsPbApprover()) {
        //     abort(403, 'Hanya approver yang dapat mengakses halaman ini');
        // }
        return view('humanresources.sppd.approval.index');
    }

    public function data()
    {
        if (stristr($_SERVER["HTTP_ACCEPT"] ?? "", "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        if (!SppdController::isApprover() && !$this->userIsPbApprover()) {
            echo "<rows></rows>";
            return;
        }

        $nomor = 1;
        $rows = collect();

        // NORMAL: pending for the HCGA manager (single approver)
        $normal = DB::table('sppd')
            ->where('Approver_ID', Auth::user()->id)
            ->where('Status', 'SUBMITTED')
            ->where('Sppd_Type', 'NORMAL')
            ->orderBy('ID', 'DESC')
            ->get();
        $rows = $rows->merge($normal);

        // PERBANTUAN: pending where current user is the org-resolved stage approver
        $pb = DB::table('sppd')
            ->where('Sppd_Type', 'PERBANTUAN')
            ->whereIn('Status', ['APPROVAL_SUPERVISOR', 'APPROVAL_MANAGER', 'APPROVAL_VP', 'APPROVAL_CEO'])
            ->orderBy('ID', 'DESC')
            ->get();
        foreach ($pb as $p) {
            if (SppdController::isPbStageApprover($p)) {
                $rows->push($p);
            }
        }

        echo "<rows>";
        foreach ($rows->sortByDesc('ID') as $Fetch) {
            $route = $Fetch->Route_Start . ' - ' . $Fetch->Route_Finish;
            echo "<row id=\"" . $Fetch->ID . "\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->SPPD_No) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Requester_Name) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Department) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Sppd_Type ?? 'NORMAL') . "</cell>";
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
        if (!SppdController::isApprover() && !$this->userIsPbApprover()) {
            abort(403, 'Hanya approver yang dapat mengakses halaman ini');
        }

        $sppd = DB::table('sppd')->where('ID', $request->ID)->first();
        if (!$sppd) {
            abort(404);
        }

        if (($sppd->Sppd_Type ?? 'NORMAL') === 'PERBANTUAN') {
            if (!SppdController::isPbStageApprover($sppd)) {
                abort(403, 'SPPD ini bukan untuk approval Anda');
            }
        } elseif ((int) $sppd->Approver_ID !== (int) Auth::user()->id) {
            abort(403, 'SPPD ini bukan untuk approval Anda');
        }

        $costs = DB::table('sppd_cost')->where('SPPD_ID', $sppd->ID)->get();
        $logs  = DB::table('sppd_log')->where('SPPD_ID', $sppd->ID)->orderBy('ID', 'ASC')->get();
        $canApprove = in_array($sppd->Status, ['SUBMITTED', 'APPROVAL_SUPERVISOR', 'APPROVAL_MANAGER', 'APPROVAL_VP', 'APPROVAL_CEO']);

        return view('humanresources.sppd.approval.detail', compact('sppd', 'costs', 'logs', 'canApprove'));
    }

    public function approval(Request $request)
    {
        $user = Auth::user();

        if (!SppdController::isApprover() && !$this->userIsPbApprover()) {
            return response()->json(['status' => 'error', 'message' => 'Tidak berhak melakukan approval'], 403);
        }

        $id           = $request->ID;
        $approvalType = $request->Approval ?? '1';
        $note         = trim($request->Note ?? '');

        $sppd = DB::table('sppd')->where('ID', $id)->first();
        if (!$sppd) {
            return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);
        }

        $isPb = ($sppd->Sppd_Type ?? 'NORMAL') === 'PERBANTUAN';

        if ($isPb) {
            if (!in_array($sppd->Status, ['APPROVAL_SUPERVISOR', 'APPROVAL_MANAGER', 'APPROVAL_VP', 'APPROVAL_CEO'])) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "SPPD ini sudah diproses (status: $sppd->Status)",
                ], 422);
            }
            if (!SppdController::isPbStageApprover($sppd)) {
                return response()->json(['status' => 'error', 'message' => 'SPPD ini bukan untuk approval Anda'], 403);
            }
        } else {
            if (!SppdController::isApprover()) {
                return response()->json(['status' => 'error', 'message' => 'Tidak berhak melakukan approval'], 403);
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
        }

        $nama = $user->Name ?: $user->username;
        $timestamp = date('Y-m-d H:i:s');
        $existingNote = $sppd->Approval_Note ? $sppd->Approval_Note . "\n" : '';

        DB::beginTransaction();
        try {
            if ($approvalType == 2) {
                $newNote = $existingNote . "$timestamp REJECTED by $user->username :: $note";
                DB::table('sppd')->where('ID', $sppd->ID)->update([
                    'Status'        => 'REJECTED',
                    'Approval_Note' => $newNote,
                    'Updated_By'    => $user->username,
                    'Updated_Date'  => now(),
                ]);
                DB::table('sppd_log')->insert([
                    'SPPD_ID'    => $sppd->ID,
                    'Action'     => 'REJECTED',
                    'Actor_ID'   => $user->id,
                    'Actor_Name' => $nama,
                    'Note'       => $note ?: 'Request ditolak',
                    'Created_At' => now(),
                ]);
                DB::commit();
                Log::info('SPPD rejected: id=' . $sppd->ID . ' by=' . $user->username);
                return response()->json(['status' => 'success', 'message' => 'Berhasil menolak request', 'newStatus' => 'REJECTED'], 200);
            }

            // APPROVE path
            if ($isPb) {
                $org = SppdController::getRequesterOrg($sppd->Requester_NIK);
                $level = $org ? SppdController::getRequesterLevel($org) : null;
                if (!$org || !$level) {
                    DB::rollBack();
                    return response()->json(['status' => 'error', 'message' => 'Profil organisasi pengaju tidak ditemukan'], 422);
                }
                $next = SppdController::pbAdvanceStage($org, $sppd->Status, $user->id);
                if (isset($next['ok']) && !$next['ok']) {
                    DB::rollBack();
                    return response()->json(['status' => 'error', 'message' => $next['message']], 422);
                }
                $newStatus = $next['status'];
                $newNote = $existingNote . "$timestamp APPROVED ($sppd->Status) by $user->username :: $note";
                DB::table('sppd')->where('ID', $sppd->ID)->update([
                    'Status'         => $newStatus,
                    'Approval_Note'  => $newNote,
                    'Approver_ID'    => $next['approver_id'],
                    'Approver_Name'  => $next['approver_name'],
                    'Approved_At'    => $newStatus === 'APPROVED' ? now() : null,
                    'Updated_By'     => $user->username,
                    'Updated_Date'   => now(),
                ]);
                DB::table('sppd_log')->insert([
                    'SPPD_ID'    => $sppd->ID,
                    'Action'     => $sppd->Status,
                    'Actor_ID'   => $user->id,
                    'Actor_Name' => $nama,
                    'Note'       => $note ?: "Disetujui pada tahap $sppd->Status",
                    'Created_At' => now(),
                ]);
            } else {
                $newStatus = 'ACCEPTED';
                $newNote = $existingNote . "$timestamp ACCEPTED by $user->username :: $note";
                DB::table('sppd')->where('ID', $sppd->ID)->update([
                    'Status'        => $newStatus,
                    'Approval_Note' => $newNote,
                    'Approved_At'   => now(),
                    'Updated_By'    => $user->username,
                    'Updated_Date'  => now(),
                ]);
                DB::table('sppd_log')->insert([
                    'SPPD_ID'    => $sppd->ID,
                    'Action'     => 'APPROVED',
                    'Actor_ID'   => $user->id,
                    'Actor_Name' => $nama,
                    'Note'       => $note ?: 'Request disetujui',
                    'Created_At' => now(),
                ]);
            }

            $carNo = null;
            if ($newStatus === 'APPROVED') {
                // Auto-create car_rent bila pengaju butuh mobil (idempoten, ikut transaksi ini).
                $sppdRow = DB::table('sppd')->where('ID', $sppd->ID)->first();
                list($carErr, $carNo, $carInfo) = CarRentController::createFromSppd($sppdRow, $user->username);
                if ($carErr) {
                    DB::rollBack();
                    Log::error('SPPD approval auto-create car_rent failed: ' . $carErr);
                    return response()->json(['status' => 'error', 'message' => 'SPPD disetujui tapi booking mobil gagal: ' . $carErr . '. Buat manual via hrd-car-rent-new?SPPD_ID=' . $sppd->ID], 422);
                }
            }

            DB::commit();

            Log::info('SPPD approval: id=' . $sppd->ID . ' newStatus=' . $newStatus . ' by=' . $user->username);

            return response()->json([
                'status'    => 'success',
                'message'   => $carNo ? 'Berhasil melakukan approval + booking mobil dibuat: ' . $carNo : 'Berhasil melakukan approval',
                'newStatus' => $newStatus,
                'Car_Rent_No' => $carNo,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SPPD approval failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    protected function userIsPbApprover()
    {
        if (!Auth::check()) {
            return false;
        }
        $rows = DB::table('sppd')
            ->where('Sppd_Type', 'PERBANTUAN')
            ->whereIn('Status', ['APPROVAL_SUPERVISOR', 'APPROVAL_MANAGER', 'APPROVAL_VP', 'APPROVAL_CEO'])
            ->get();
        foreach ($rows as $r) {
            if (SppdController::isPbStageApprover($r)) {
                return true;
            }
        }
        return false;
    }
}
