<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\Approval\SettlementApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SettlementApprovalController extends Controller
{
    /**
     * @var SettlementApprovalService
     */
    protected $approvalService;

    public function __construct(SettlementApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    /**
     * Helper authorization: mengecek apakah user merupakan approver / HCGA verifier / admin
     */
    public static function isApprover($user = null): bool
    {
        $user = $user ?: Auth::user();
        if (!$user) return false;

        // Cek apakah user memiliki active task di approval_request_task
        $hasTask = DB::table('approval_request_task as t')
            ->join('approval_request as r', 'r.ID', '=', 't.Request_ID')
            ->leftJoin('approval_task_candidate as c', 'c.Task_ID', '=', 't.ID')
            ->where('r.Request_Type', 'SETTLEMENT')
            ->where('t.Status', 'PENDING')
            ->where(function ($q) use ($user) {
                $q->where('t.Claimed_By', $user->id)
                  ->orWhere('c.User_Id', $user->id);
            })
            ->exists();

        if ($hasTask) return true;

        if (self::isHcgaVerifier($user) || self::isAdministrator($user)) return true;

        $emp = DB::table('employment')->where('NIK', $user->Emp_ID)->first();
        if ($emp && !empty($emp->Position)) {
            $pos = DB::table('master_position')->where('Initial', $emp->Position)->first();
            if ($pos && (int) $pos->Level <= 3) return true;
        }

        return false;
    }

    public static function isHcgaVerifier($user = null): bool
    {
        $user = $user ?: Auth::user();
        if (!$user) return false;
        return in_array(strtoupper(trim((string) $user->Department)), ['HCGA', 'HRD', 'HUMAN CAPITAL', 'GA']);
    }

    public static function isAdministrator($user = null): bool
    {
        $user = $user ?: Auth::user();
        if (!$user) return false;
        $uname = strtolower(trim((string) $user->username));
        return in_array($uname, ['admin', 'administrator', 'superadmin']) || (int) ($user->is_admin ?? 0) === 1;
    }

    public function index()
    {
        if (!self::isApprover()) {
            abort(403, 'Anda tidak memiliki hak akses ke menu Approval Settlement.');
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

        $user = Auth::user();
        $nomor = 1;

        $isAdmin = self::isAdministrator($user);
        $isHcga  = self::isHcgaVerifier($user);

        $query = DB::table('settlement_sppd as s')
            ->leftJoin('approval_request as ar', 'ar.ID', '=', 's.Approval_Request_ID')
            ->leftJoin('approval_request_task as t', function ($join) {
                $join->on('t.Request_ID', '=', 'ar.ID')
                     ->where('t.Status', '=', 'PENDING');
            })
            ->leftJoin('approval_task_candidate as c', 'c.Task_ID', '=', 't.ID')
            ->select('s.*', 't.ID as Active_Task_ID', 't.Claimed_By as Active_Claimed_By', 't.Stage_Order as Active_Stage')
            ->distinct()
            ->orderBy('s.ID', 'DESC');

        if (!$isAdmin && !$isHcga) {
            $query->where(function ($q) use ($user) {
                $q->where('t.Claimed_By', $user->id)
                  ->orWhere('c.User_Id', $user->id);
            });
        }

        echo "<rows>";
        foreach ($query->get() as $Fetch) {
            $expenses = DB::table('settlement_sppd_expense')->where('Settlement_Sppd_ID', $Fetch->ID)->get();
            $total = 0;
            foreach ($expenses as $e) {
                $total += (float) $e->Cost;
            }
            if (isset($Fetch->Matrix_Amount) && (float) $Fetch->Matrix_Amount > 0) {
                $total += (float) $Fetch->Matrix_Amount;
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
        $user = Auth::user();
        $settlement = DB::table('settlement_sppd')->where('ID', $request->ID)->first();
        if (!$settlement) {
            abort(404, 'Data Settlement tidak ditemukan');
        }

        $expenses = DB::table('settlement_sppd_expense')->where('Settlement_Sppd_ID', $settlement->ID)->get();
        $logs = DB::table('settlement_sppd_log')->where('Settlement_Sppd_ID', $settlement->ID)->orderBy('ID', 'ASC')->get();
        $invoice = $settlement->Invoice_File ? DB::table('meta_files')->where('ID', $settlement->Invoice_File)->first() : null;

        // Evaluasi izin approval berdasarkan Approval Policy / Engine
        $canApprove = $this->approvalService->canUserApprove((int) $settlement->ID, $user);

        // Ambil task aktif jika terhubung ke ApprovalWorkflowService
        $currentTask = null;
        if (!empty($settlement->Approval_Request_ID)) {
            $currentTask = DB::table('approval_request_task as t')
                ->where('t.Request_ID', (int) $settlement->Approval_Request_ID)
                ->whereIn('t.Status', ['PENDING', 'CLAIMED'])
                ->orderBy('t.Stage_Order', 'ASC')
                ->first();
        }

        return view('humanresources.settlement.approval.detail', compact('settlement', 'expenses', 'logs', 'invoice', 'canApprove', 'currentTask'));
    }

    public function approval(Request $request)
    {
        $user = Auth::user();
        $id           = $request->ID;
        $approvalType = $request->Approval ?? '1'; // 1: APPROVE, 2: REJECT
        $action       = ($approvalType == 2) ? 'REJECT' : 'APPROVE';
        $note         = trim($request->Note ?? '');

        $settlement = DB::table('settlement_sppd')->where('ID', $id)->first();
        if (!$settlement) {
            return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);
        }

        if (in_array(strtoupper($settlement->Status), ['APPROVED', 'CLOSED', 'REJECT', 'REJECTED', 'CANCELED'])) {
            return response()->json([
                'status'  => 'error',
                'message' => "Request ini sudah diproses (status: $settlement->Status)",
            ], 422);
        }

        if (!$this->approvalService->canUserApprove((int) $settlement->ID, $user)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda tidak memiliki hak wewenang untuk menyetujui tahap ini.',
            ], 403);
        }

        $nama = $user->Name ?: $user->username;
        $timestamp = date('Y-m-d H:i:s');
        $existingNote = $settlement->Approval_Note ? $settlement->Approval_Note . "\n" : '';
        $newNote = $existingNote . "$timestamp " . ($action === 'REJECT' ? 'REJECTED' : 'APPROVED') . " by $user->username :: $note";
        $newStatus = ($action === 'REJECT') ? 'REJECTED' : 'APPROVED';

        DB::beginTransaction();
        try {
            // Jika terhubung dengan ApprovalWorkflowService, eksekusi melalui Approval Engine
            if (!empty($settlement->Approval_Request_ID)) {
                $workflow = app(\App\Services\Approval\ApprovalWorkflowService::class);
                $currentTask = DB::table('approval_request_task')
                    ->where('Request_ID', (int) $settlement->Approval_Request_ID)
                    ->whereIn('Status', ['PENDING', 'CLAIMED'])
                    ->orderBy('Stage_Order', 'ASC')
                    ->first();

                if ($currentTask) {
                    if (strtoupper($currentTask->Status) !== 'CLAIMED' || (int) $currentTask->Claimed_By !== (int) $user->id) {
                        try {
                            $workflow->claim((int) $currentTask->ID, (int) $user->id);
                        } catch (\Exception $clEx) {
                            Log::info('Task already claimed or claim bypass: ' . $clEx->getMessage());
                        }
                    }

                    $workflow->act(
                        (int) $currentTask->ID,
                        (int) $user->id,
                        $action,
                        $note ?: ($action === 'REJECT' ? 'Ditolak pada tahap approval' : 'Disetujui pada tahap approval')
                    );
                }

                $this->approvalService->syncApprovalStatus((int) $settlement->ID);
                $settlementFresh = DB::table('settlement_sppd')->where('ID', $settlement->ID)->first();
                $newStatus = $settlementFresh ? $settlementFresh->Status : $newStatus;
            } else {
                // Fallback direct update jika legacy settlement belum memiliki Approval_Request_ID
                DB::table('settlement_sppd')->where('ID', $settlement->ID)->update([
                    'Status'         => $newStatus,
                    'Approval_Note'  => $newNote,
                    'Approved_At'    => $action === 'REJECT' ? null : now(),
                    'Validator_ID'   => $action === 'REJECT' ? $settlement->Validator_ID : $user->id,
                    'Validator_Name' => $action === 'REJECT' ? $settlement->Validator_Name : $nama,
                    'Updated_By'     => $user->username,
                    'Updated_Date'   => now(),
                ]);
            }

            DB::table('settlement_sppd_log')->insert([
                'Settlement_Sppd_ID' => $settlement->ID,
                'Action'             => $action === 'REJECT' ? 'REJECTED' : 'APPROVED',
                'Actor_ID'           => $user->id,
                'Actor_Name'         => $nama,
                'Note'               => $note ?: ($action === 'REJECT' ? 'Request settlement ditolak' : 'Request settlement disetujui'),
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
