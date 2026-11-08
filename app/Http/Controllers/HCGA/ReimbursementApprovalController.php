<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\Approval\ReimbursementApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReimbursementApprovalController extends Controller
{
    /**
     * @var ReimbursementApprovalService
     */
    protected $approvalService;

    public function __construct(ReimbursementApprovalService $approvalService)
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

        // Check if user has active approval tasks in approval_request_task
        $hasTask = DB::table('approval_request_task as t')
            ->join('approval_request as r', 'r.ID', '=', 't.Request_ID')
            ->leftJoin('approval_task_candidate as c', 'c.Task_ID', '=', 't.ID')
            ->where('r.Request_Type', 'REIMBURSEMENT')
            ->where('t.Status', 'PENDING')
            ->where(function ($q) use ($user) {
                $q->where('t.Claimed_By', $user->id)
                  ->orWhere('c.User_Id', $user->id);
            })
            ->exists();

        if ($hasTask) return true;

        // Check organization hierarchy / HCGA verifier / Admin
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
            abort(403, 'Anda tidak memiliki hak akses ke menu Approval Reimbursement.');
        }
        return view('humanresources.reimbursement.approval.index');
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

        $query = DB::table('reimbursement as r')
            ->leftJoin('approval_request as ar', 'ar.ID', '=', 'r.Approval_Request_ID')
            ->leftJoin('approval_request_task as t', function ($join) {
                $join->on('t.Request_ID', '=', 'ar.ID')
                     ->where('t.Status', '=', 'PENDING');
            })
            ->leftJoin('approval_task_candidate as c', function ($join) {
                $join->on('c.Task_ID', '=', 't.ID')
                     ->where('c.Status', '=', 'ACTIVE');
            })
            ->select(
                'r.ID',
                'r.Reimbursement_No',
                'r.User_Responsibility_Name',
                'r.Department',
                'r.Description',
                'r.Total',
                'r.Status',
                'r.Reimbursement_Date',
                'r.submitted_date',
                'r.Created_Date',
                't.Label as Task_Label',
                't.Position_Name as Task_Position'
            )
            ->where(function ($w) {
                $w->whereIn('r.Status', ['APPROVAL_UPPER', 'APPROVAL_VP', 'APPROVAL_HCGA', 'APPROVAL_DIRECTOR', 'WAITING_APPROVAL', 'IN_PROGRESS', 'SUBMITTED'])
                  ->orWhereNull('r.Status');
            });

        if (!$isAdmin && !$isHcga) {
            $query->where(function ($w) use ($user) {
                $w->where('t.Claimed_By', $user->id)
                  ->orWhere('c.User_Id', $user->id)
                  ->orWhere('r.Approver_ID', $user->id);
            });
        }

        $items = $query->distinct()->orderBy('r.ID', 'DESC')->get();

        echo "<rows>";
        foreach ($items as $Fetch) {
            $stageApprover = $Fetch->Task_Label ?: ($Fetch->Task_Position ?: 'Menunggu Approval');

            echo "<row id=\"" . $Fetch->ID . "\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Reimbursement_No) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->User_Responsibility_Name) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Department) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Description) . "</cell>";
            echo "<cell>" . number_format((float) $Fetch->Total, 0, ',', '.') . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Status) . "</cell>";
            echo "<cell>" . htmlspecialchars($stageApprover) . "</cell>";
            echo "<cell>" . ($Fetch->submitted_date ? date('d-m-Y H:i', strtotime($Fetch->submitted_date)) : ($Fetch->Created_Date ? date('d-m-Y', strtotime($Fetch->Created_Date)) : '-')) . "</cell>";
            echo "</row>";
            $nomor++;
        }
        echo "</rows>";
    }

    public function detail(Request $request)
    {
        $user = Auth::user();
        $reimbursement = DB::table('reimbursement')->where('ID', $request->ID)->first();
        if (!$reimbursement) {
            abort(404, 'Data Reimbursement tidak ditemukan');
        }

        $dtls = DB::table('reimbursement_dtl')
            ->where('Reimbursement_No', $reimbursement->Reimbursement_No)
            ->orderBy('ID', 'ASC')
            ->get();

        $logs = DB::table('reimbursement_log')
            ->where('Cash_No', $reimbursement->Reimbursement_No)
            ->orderBy('ID', 'ASC')
            ->get();

        $attachment = $reimbursement->Attachment_File ? DB::table('meta_files')->where('ID', $reimbursement->Attachment_File)->first() : null;

        // Cek current approval task
        $currentTask = null;
        $canApprove = false;
        if (!empty($reimbursement->Approval_Request_ID)) {
            $currentTask = DB::table('approval_request_task as t')
                ->where('t.Request_ID', (int) $reimbursement->Approval_Request_ID)
                ->where('t.Status', 'PENDING')
                ->orderBy('t.Stage_Order', 'ASC')
                ->first();

            if ($currentTask) {
                if ((int) $currentTask->Claimed_By === (int) $user->id) {
                    $canApprove = true;
                } else {
                    $isCandidate = DB::table('approval_task_candidate')
                        ->where('Task_ID', $currentTask->ID)
                        ->where('User_Id', $user->id)
                        ->where('Status', 'ACTIVE')
                        ->exists();
                    if ($isCandidate) {
                        $canApprove = true;
                    }
                }
            }
        }

        if (self::isAdministrator($user) || self::isHcgaVerifier($user)) {
            $canApprove = true;
        }

        // Jika status sudah final, tidak bisa approve lagi
        if (in_array(strtoupper($reimbursement->Status), ['APPROVED', 'CLOSED', 'REJECT', 'REJECTED', 'CANCELED'])) {
            $canApprove = false;
        }

        return view('humanresources.reimbursement.approval.detail', compact('reimbursement', 'dtls', 'logs', 'currentTask', 'canApprove', 'attachment'));
    }

    public function approve(Request $request)
    {
        $user = Auth::user();
        $id     = $request->ID;
        $action = strtoupper(trim($request->Action ?? 'APPROVE')); // APPROVE or REJECT
        $note   = trim($request->Note ?? '');

        $rmb = DB::table('reimbursement')->where('ID', $id)->first();
        if (!$rmb) {
            return response()->json(['status' => 'error', 'message' => 'Data Reimbursement tidak ditemukan.'], 404);
        }

        if (in_array(strtoupper($rmb->Status), ['APPROVED', 'CLOSED', 'REJECT', 'REJECTED', 'CANCELED'])) {
            return response()->json(['status' => 'error', 'message' => 'Dokumen ini sudah dalam status final (' . $rmb->Status . ').'], 422);
        }

        DB::beginTransaction();
        try {
            if (!empty($rmb->Approval_Request_ID)) {
                // Gunakan ApprovalWorkflowService untuk resolve task
                $workflow = app(\App\Services\Approval\ApprovalWorkflowService::class);
                $currentTask = DB::table('approval_request_task')
                    ->where('Request_ID', (int) $rmb->Approval_Request_ID)
                    ->where('Status', 'PENDING')
                    ->orderBy('Stage_Order', 'ASC')
                    ->first();

                if ($currentTask) {
                    if (strtoupper($currentTask->Status) !== 'CLAIMED' || (int) $currentTask->Claimed_By !== (int) $user->id) {
                        try {
                            $workflow->claim((int) $currentTask->ID, (int) $user->id);
                        } catch (\Exception $claimEx) {
                            Log::info('Task auto-claim fallback: ' . $claimEx->getMessage());
                        }
                    }

                    $act = ($action === 'REJECT') ? 'REJECT' : 'APPROVE';
                    $workflow->act((int) $currentTask->ID, $act, (int) $user->id, $note);
                    $this->approvalService->syncApprovalStatus((int) $rmb->ID, $user->username);

                    // Kirim notifikasi email ke approver stage berikutnya jika request belum final
                    $freshRmb = DB::table('reimbursement')->where('ID', $rmb->ID)->first();
                    if ($freshRmb && !in_array(strtoupper($freshRmb->Status), ['APPROVED', 'CLOSED', 'REJECT', 'REJECTED', 'CANCELED'])) {
                        try {
                            $this->approvalService->notifyCurrentStageApprovers((int) $rmb->ID, 'Reimbursement Berpindah Tahap');
                        } catch (\Exception $mailEx) {
                            Log::warning('Email stage notification error: ' . $mailEx->getMessage());
                        }
                    }
                } else {
                    // Fallback direct update jika task tidak ditemukan
                    $newStatus = ($action === 'REJECT') ? 'REJECT' : 'APPROVED';
                    DB::table('reimbursement')->where('ID', $rmb->ID)->update([
                        'Status'        => $newStatus,
                        'Approver_ID'   => $user->id,
                        'Approver_Name' => $user->Name ?: $user->username,
                        'Approval_Note' => $note,
                        'Approved_At'   => now(),
                    ]);
                    DB::table('reimbursement_log')->insert([
                        'Location'     => $rmb->Location ?: 'GEU-HO',
                        'Cash_No'      => $rmb->Reimbursement_No,
                        'Transaction'  => 'REIMBURSEMENT_APPROVAL',
                        'Description'  => 'Manual Action ' . $newStatus . ' oleh ' . ($user->Name ?: $user->username) . ': ' . $note,
                        'Reference'    => $rmb->Reference_No ?: '',
                        'Status'       => $newStatus,
                        'Created_Date' => now(),
                        'Created_By'   => $user->username,
                    ]);
                }
            } else {
                // Direct fallback untuk pengajuan lama yang belum terdaftar di Approval Engine
                $newStatus = ($action === 'REJECT') ? 'REJECT' : 'APPROVED';
                DB::table('reimbursement')->where('ID', $rmb->ID)->update([
                    'Status'        => $newStatus,
                    'Approver_ID'   => $user->id,
                    'Approver_Name' => $user->Name ?: $user->username,
                    'Approval_Note' => $note,
                    'Approved_At'   => now(),
                ]);
                DB::table('reimbursement_log')->insert([
                    'Location'     => $rmb->Location ?: 'GEU-HO',
                    'Cash_No'      => $rmb->Reimbursement_No,
                    'Transaction'  => 'REIMBURSEMENT_APPROVAL',
                    'Description'  => 'Approval Action ' . $newStatus . ' oleh ' . ($user->Name ?: $user->username) . ': ' . $note,
                    'Reference'    => $rmb->Reference_No ?: '',
                    'Status'       => $newStatus,
                    'Created_Date' => now(),
                    'Created_By'   => $user->username,
                ]);
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Reimbursement berhasil di-' . strtolower($action) . '!',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reimbursement approval failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal memproses approval: ' . $e->getMessage()], 500);
        }
    }
}

