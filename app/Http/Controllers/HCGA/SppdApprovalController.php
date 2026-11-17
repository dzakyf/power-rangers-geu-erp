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
        if (!$this->canViewApprovalPage()) {
            abort(403, 'Hanya approver / verifier yang dapat mengakses halaman ini');
        }
        return view('humanresources.sppd.approval.index');
    }

    protected function canViewApprovalPage()
    {
        return SppdController::isApprover()
            || SppdController::isHcgaVerifier()
            || SppdController::isAdministrator()
            || $this->userIsPbApprover();
    }

    public function data()
    {
        if (stristr($_SERVER["HTTP_ACCEPT"] ?? "", "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>");

        if (!$this->canViewApprovalPage()) {
            echo "<rows></rows>";
            return;
        }

        $admin    = SppdController::isAdministrator();
        $isHcga   = SppdController::isHcgaVerifier();
        $user     = Auth::user();
        $nomor    = 1;

        if ($admin) {
            // Administrator: full visibility (all types / statuses)
            $rows = DB::table('sppd')->orderBy('ID', 'DESC')->get();
        } elseif ($isHcga) {
            // HCGA verifier / manager: monitoring seluruh SPPD yang sedang dalam proses approval
            $rows = DB::table('sppd')
                ->whereIn('Status', [
                    'SUBMITTED',
                    'APPROVAL_HCGA_MANAGER',
                    'APPROVAL_SUPERVISOR',
                    'APPROVAL_MANAGER',
                    'APPROVAL_VP',
                    'APPROVAL_BOD',
                    'APPROVAL_CEO',
                ])
                ->orderBy('ID', 'DESC')
                ->get();
        } else {
            // Approver berdasarkan Approval Policy (Manager Dept, BOD, dsb):
            // Hanya menampilkan SPPD yang saat ini benar-benar SEDANG MENUNGGU aksi approval dari user ini
            $rows = DB::table('sppd as s')
                ->where(function ($q) use ($user) {
                    $q->where(function ($eq) use ($user) {
                        $eq->whereNotNull('s.Approval_Request_ID')
                           ->whereExists(function ($sub) use ($user) {
                               $sub->select(DB::raw(1))
                                   ->from('approval_request_task as t')
                                   ->leftJoin('approval_task_candidate as c', 'c.Task_ID', '=', 't.ID')
                                   ->whereColumn('t.Request_ID', 's.Approval_Request_ID')
                                   ->whereIn('t.Status', ['PENDING', 'CLAIMED'])
                                   ->where(function ($tq) use ($user) {
                                       $tq->where('t.Claimed_By', $user->id)
                                          ->orWhere(function ($cq) use ($user) {
                                              $cq->where('c.User_Id', $user->id)
                                                 ->where('c.Status', 'ACTIVE');
                                          });
                                   });
                           });
                    })
                    ->orWhere(function ($lq) use ($user) {
                        $lq->whereNull('s.Approval_Request_ID')
                           ->where('s.Approver_ID', $user->id);
                    });
                })
                ->whereNotIn('s.Status', ['DRAFT', 'REJECTED', 'CLOSED', 'ACCEPTED', 'APPROVED'])
                ->orderBy('s.ID', 'DESC')
                ->get();

            $pb = DB::table('sppd')
                ->where('Sppd_Type', 'PERBANTUAN')
                ->whereIn('Status', ['APPROVAL_SUPERVISOR', 'APPROVAL_MANAGER', 'APPROVAL_VP', 'APPROVAL_CEO'])
                ->whereNotIn('ID', $rows->pluck('ID')->all())
                ->orderBy('ID', 'DESC')->get();

            foreach ($pb as $p) {
                if (SppdController::isPbStageApprover($p, $user)) {
                    $rows->push($p);
                }
            }
            $rows = $rows->sortByDesc('ID')->values();
        }

        $stageLabels = [
            'SUBMITTED'             => 'Manager HCGA',
            'APPROVAL_HCGA_MANAGER' => 'Manager HCGA',
            'APPROVAL_SUPERVISOR'   => 'Supervisor',
            'APPROVAL_MANAGER'      => 'Manager',
            'APPROVAL_VP'           => 'VP / BOD',
            'APPROVAL_BOD'          => 'BOD',
            'APPROVAL_CEO'          => 'CEO',
            'ACCEPTED'              => 'Approved',
            'APPROVED'              => 'Approved',
            'REJECTED'              => 'Rejected',
            'CLOSED'                => 'Closed',
        ];

        echo "<rows>";
        foreach ($rows as $Fetch) {
            $route = $Fetch->Route_Start . ' - ' . $Fetch->Route_Finish;
            $stage = ($stageLabels[$Fetch->Status] ?? $Fetch->Status) . ' / ' . ($Fetch->Approver_Name ?: '-');
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
            echo "<cell>" . htmlspecialchars($stage) . "</cell>";
            echo "<cell>" . ($Fetch->Created_Date ? date('d-m-Y H:i', strtotime($Fetch->Created_Date)) : '-') . "</cell>";
            echo "</row>";
            $nomor++;
        }
        echo "</rows>";
    }

    public function detail(Request $request)
    {
        if (!$this->canViewApprovalPage()) {
            abort(403, 'Hanya approver / verifier yang dapat mengakses halaman ini');
        }

        $sppd = DB::table('sppd')->where('ID', $request->ID)->first();
        if (!$sppd) {
            abort(404);
        }

        $adminOrVerifier = SppdController::isAdministrator() || SppdController::isHcgaVerifier();
        $isActingApprover = SppdController::isApprover(Auth::user(), $sppd);

        if (!$isActingApprover && !$adminOrVerifier) {
            abort(403, 'SPPD ini bukan untuk approval Anda');
        }

        $costs = DB::table('sppd_cost')->where('SPPD_ID', $sppd->ID)->get();
        $logs  = DB::table('sppd_log')->where('SPPD_ID', $sppd->ID)->orderBy('ID', 'ASC')->get();
        $canApprove = $isActingApprover
            && in_array($sppd->Status, ['SUBMITTED', 'APPROVAL_HCGA_MANAGER', 'APPROVAL_SUPERVISOR', 'APPROVAL_MANAGER', 'APPROVAL_VP', 'APPROVAL_BOD', 'APPROVAL_CEO']);

        // Pastikan flag Is_Over_Plafon terisi dinamis jika kolom belum ada/bernilai null
        if (!isset($sppd->Is_Over_Plafon) || $sppd->Is_Over_Plafon === null) {
            $sppd->Is_Over_Plafon = app(\App\Services\Approval\SppdApprovalService::class)->isOverPlafon($sppd);
        }

        return view('humanresources.sppd.approval.detail', compact('sppd', 'costs', 'logs', 'canApprove'));
    }

    public function approval(Request $request)
    {
        $user = Auth::user();

        if (!SppdController::isApprover($user) && !$this->userIsPbApprover()) {
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
            if (!SppdController::isPbStageApprover($sppd, $user)) {
                return response()->json(['status' => 'error', 'message' => 'SPPD ini bukan untuk approval Anda'], 403);
            }
        } else {
            if (!SppdController::isApprover($user, $sppd)) {
                return response()->json(['status' => 'error', 'message' => 'SPPD ini bukan untuk approval Anda'], 403);
            }
            if (!in_array($sppd->Status, ['SUBMITTED', 'APPROVAL_HCGA_MANAGER', 'APPROVAL_SUPERVISOR', 'APPROVAL_MANAGER', 'APPROVAL_VP', 'APPROVAL_BOD', 'APPROVAL_CEO'])) {
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
                // Eksekusi penolakan pada ApprovalWorkflowService bila terhubung
                if (!empty($sppd->Approval_Request_ID)) {
                    $workflow = app(\App\Services\Approval\ApprovalWorkflowService::class);
                    $currentTask = DB::table('approval_request_task as t')
                        ->leftJoin('approval_task_candidate as c', 'c.Task_ID', '=', 't.ID')
                        ->where('t.Request_ID', (int) $sppd->Approval_Request_ID)
                        ->whereIn('t.Status', ['PENDING', 'CLAIMED'])
                        ->where(function ($q) use ($user) {
                            $q->where('t.Claimed_By', (int) $user->id)
                              ->orWhere(function ($cq) use ($user) {
                                  $cq->where('c.User_Id', (int) $user->id)
                                     ->where('c.Status', 'ACTIVE');
                              });
                        })
                        ->orderBy('t.Stage_Order', 'ASC')
                        ->select('t.*')
                        ->first();

                    if (!$currentTask) {
                        DB::rollBack();
                        return response()->json([
                            'status'  => 'error',
                            'message' => 'Tidak ada antrean tugas penolakan untuk Anda pada SPPD ini.',
                        ], 422);
                    }

                    if (strtoupper((string) $currentTask->Status) !== 'CLAIMED' || (int) $currentTask->Claimed_By !== (int) $user->id) {
                        try {
                            $workflow->claim((int) $currentTask->ID, (int) $user->id);
                        } catch (\Exception $clEx) {
                            Log::info('Task claim bypass: ' . $clEx->getMessage());
                        }
                    }
                    $workflow->act((int) $currentTask->ID, (int) $user->id, 'REJECT', $note ?: 'Request ditolak');
                    app(\App\Services\Approval\SppdApprovalService::class)->syncApprovalStatus((int) $sppd->ID, $user->username);
                }

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
                // Eksekusi persetujuan pada ApprovalWorkflowService bila terhubung
                if (!empty($sppd->Approval_Request_ID)) {
                    $workflow = app(\App\Services\Approval\ApprovalWorkflowService::class);
                    $currentTask = DB::table('approval_request_task as t')
                        ->leftJoin('approval_task_candidate as c', 'c.Task_ID', '=', 't.ID')
                        ->where('t.Request_ID', (int) $sppd->Approval_Request_ID)
                        ->whereIn('t.Status', ['PENDING', 'CLAIMED'])
                        ->where(function ($q) use ($user) {
                            $q->where('t.Claimed_By', (int) $user->id)
                              ->orWhere(function ($cq) use ($user) {
                                  $cq->where('c.User_Id', (int) $user->id)
                                     ->where('c.Status', 'ACTIVE');
                              });
                        })
                        ->orderBy('t.Stage_Order', 'ASC')
                        ->select('t.*')
                        ->first();

                    if (!$currentTask) {
                        DB::rollBack();
                        return response()->json([
                            'status'  => 'error',
                            'message' => 'Tidak ada antrean tugas persetujuan untuk Anda pada SPPD ini (mungkin sudah disetujui sebelumnya atau telah berlanjut ke tahap berikutnya).',
                        ], 422);
                    }

                    if (strtoupper((string) $currentTask->Status) !== 'CLAIMED' || (int) $currentTask->Claimed_By !== (int) $user->id) {
                        try {
                            $workflow->claim((int) $currentTask->ID, (int) $user->id);
                        } catch (\Exception $clEx) {
                            Log::info('Task claim bypass: ' . $clEx->getMessage());
                        }
                    }

                    $workflow->act((int) $currentTask->ID, (int) $user->id, 'APPROVE', $note ?: 'Request disetujui');
                    app(\App\Services\Approval\SppdApprovalService::class)->syncApprovalStatus((int) $sppd->ID, $user->username);
                }

                $refreshedSppd = DB::table('sppd')->where('ID', $sppd->ID)->first();
                $newStatus = $refreshedSppd ? $refreshedSppd->Status : 'ACCEPTED';
                $newNote = $existingNote . "$timestamp APPROVED by $user->username :: $note";
                $updatePayload = [
                    'Approval_Note' => $newNote,
                    'Updated_By'    => $user->username,
                    'Updated_Date'  => now(),
                ];
                if (in_array($newStatus, ['ACCEPTED', 'APPROVED'])) {
                    $updatePayload['Approved_At'] = now();
                }
                DB::table('sppd')->where('ID', $sppd->ID)->update($updatePayload);
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
