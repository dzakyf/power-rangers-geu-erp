<?php

namespace App\Services\Approval;

use App\Exceptions\ApprovalWorkflowException;
use Illuminate\Support\Facades\DB;

/**
 * Adapter antara modul Requisition/PTK lama dan approval engine berbasis policy.
 *
 * Sumber kebenaran approval ada di approval_request / approval_request_task.
 * Tabel requisition_approval tetap ditulis sebagai mirror agar laporan dan
 * screen lama yang masih membacanya tidak langsung rusak.
 */
class RequisitionApprovalService
{
    const REQUEST_TYPE = 'REQUISITION';

    protected $workflow;
    protected $policyResolver;

    public function __construct(ApprovalWorkflowService $workflow, ApprovalPolicyResolver $policyResolver)
    {
        $this->workflow = $workflow;
        $this->policyResolver = $policyResolver;
    }

    /**
     * Submit requisition yang sudah tersimpan ke approval engine.
     *
     * @param object $requisition baris requisition (harus sudah punya ID)
     * @param object $jobPosition baris master_position
     * @param object $user        user yang mengajukan (Auth::user())
     * @return object approval_request
     */
    public function submit($requisition, $jobPosition, $user)
    {
        $context = $this->buildContext($requisition, $jobPosition, $user);

        $request = $this->workflow->submit([
            'Request_No' => $requisition->Task_ID ?: null,
            'Description' => $this->description($requisition, $jobPosition),
        ], $context, (int) $user->id);

        DB::table('approval_request')->where('ID', (int) $request->ID)->update([
            'Map_To' => (int) $requisition->ID,
        ]);

        DB::table('requisition')->where('ID', (int) $requisition->ID)->update([
            'Approval_Request_ID' => (int) $request->ID,
            'Status' => $this->mapRequisitionStatus($request->Status),
        ]);

        $this->syncLegacyRows((int) $requisition->ID);
        $this->syncRequisitionStatus((int) $requisition->ID);

        return $this->workflow->getRequest((int) $request->ID);
    }

    /**
     * Preflight policy sebelum baris requisition di-insert.
     *
     * requisition/requisition_log memakai MyISAM, sehingga DB::transaction()
     * tidak dapat me-rollback baris requisition. Karena itu error policy harus
     * diketahui sebelum insert.
     */
    public function assertCanSubmit($requisition, $jobPosition, $user): array
    {
        $context = $this->buildContext($requisition, $jobPosition, $user);

        return $this->policyResolver->resolve($context);
    }

    /**
     * Bersihkan sisa submit yang gagal. Ini penting untuk tabel MyISAM yang
     * tidak ikut rollback saat transaksi dibatalkan.
     */
    public function cleanupFailedSubmission(string $taskId, int $requisitionId = null): void
    {
        $requestIds = DB::table('approval_request')
            ->where('Request_Type', self::REQUEST_TYPE)
            ->where(function ($query) use ($taskId, $requisitionId) {
                $query->where('Request_No', $taskId);
                if ($requisitionId) {
                    $query->orWhere('Map_To', $requisitionId);
                }
            })
            ->pluck('ID')
            ->all();

        if (!empty($requestIds)) {
            $taskIds = DB::table('approval_request_task')->whereIn('Request_ID', $requestIds)->pluck('ID')->all();
            if (!empty($taskIds)) {
                DB::table('approval_held_notification')->whereIn('Task_ID', $taskIds)->delete();
                DB::table('approval_task_candidate')->whereIn('Task_ID', $taskIds)->delete();
            }

            DB::table('approval_self_exception')->whereIn('Request_ID', $requestIds)->delete();
            DB::table('approval_request_task')->whereIn('Request_ID', $requestIds)->delete();
            DB::table('approval_log')->whereIn('Request_Id', $requestIds)->delete();
            DB::table('approval_request')->whereIn('ID', $requestIds)->delete();
        }

        DB::table('requisition_approval')->where('Task_ID', $taskId)->delete();
        DB::table('requisition_log')->where('Task_ID', $taskId)->delete();

        if ($requisitionId) {
            DB::table('requisition_log')->where('Last_ID', $requisitionId)->delete();
            DB::table('requisition')->where('ID', $requisitionId)->delete();
        }
    }

    /**
     * Daftar requisition yang menunggu user ini pada stage approval saat ini.
     * Satu requisition hanya ditampilkan satu kali meskipun user memegang
     * beberapa task pada stage yang sama.
     */
    public function pendingRequisitions(string $location, int $userId)
    {
        return DB::table('requisition as q')
            ->join('approval_request as r', 'r.ID', '=', 'q.Approval_Request_ID')
            ->where('r.Request_Type', self::REQUEST_TYPE)
            ->whereNotIn('r.Status', ['APPROVED', 'REJECTED', 'RETURNED', 'CANCELLED'])
            ->where('q.Location', $location)
            ->whereNotIn('q.Status', ['CLOSED', 'CANCELED'])
            ->whereExists(function ($sub) use ($userId) {
                $sub->select(DB::raw(1))
                    ->from('approval_request_task as t')
                    ->join('approval_task_candidate as c', 'c.Task_ID', '=', 't.ID')
                    ->whereColumn('t.Request_ID', 'r.ID')
                    ->whereColumn('t.Stage_Order', 'r.Current_Stage')
                    ->whereIn('t.Status', ['PENDING', 'CLAIMED'])
                    ->where(function ($task) use ($userId) {
                        $task->where('t.Status', 'PENDING')
                            ->orWhere(function ($claimed) use ($userId) {
                                $claimed->where('t.Status', 'CLAIMED')
                                    ->where('t.Claimed_By', $userId);
                            });
                    })
                    ->where('c.User_Id', $userId)
                    ->where('c.Status', 'ACTIVE');
            })
            ->select('q.*', 'r.Current_Stage', 'r.Status as Approval_Status')
            ->orderBy('q.ID', 'DESC')
            ->get();
    }

    /**
     * Task aktif milik user pada stage yang sedang berjalan.
     */
    public function pendingTasksForUser(int $requisitionId, int $userId): array
    {
        $requestId = (int) DB::table('requisition')
            ->where('ID', $requisitionId)
            ->value('Approval_Request_ID');

        if ($requestId < 1) {
            return [];
        }

        $request = $this->workflow->getRequest($requestId);
        if (!$request || in_array(strtoupper((string) $request->Status), ['APPROVED', 'REJECTED', 'RETURNED', 'CANCELLED'], true)) {
            return [];
        }

        return DB::table('approval_request_task as t')
            ->join('approval_task_candidate as c', function ($join) use ($userId) {
                $join->on('c.Task_ID', '=', 't.ID')
                    ->where('c.User_Id', '=', $userId)
                    ->where('c.Status', '=', 'ACTIVE');
            })
            ->where('t.Request_ID', $requestId)
            ->where('t.Stage_Order', (int) $request->Current_Stage)
            ->whereIn('t.Status', ['PENDING', 'CLAIMED'])
            ->where(function ($task) use ($userId) {
                $task->where('t.Status', 'PENDING')
                    ->orWhere(function ($claimed) use ($userId) {
                        $claimed->where('t.Status', 'CLAIMED')
                            ->where('t.Claimed_By', $userId);
                    });
            })
            ->select('t.*')
            ->orderBy('t.ID', 'ASC')
            ->get()
            ->all();
    }

    /**
     * Approve/reject semua task user pada stage berjalan.
     *
     * Catatan: engine mewajibkan task di-claim sebelum act(). Adapter ini
     * melakukan claim otomatis supaya dataProcessor lama cukup mengirim
     * satu aksi approve/reject.
     *
     * @return array task IDs yang diproses
     */
    public function actOnRequisition(int $requisitionId, int $userId, string $action, string $notes = ''): array
    {
        $action = strtoupper(trim($action));
        if (!in_array($action, ['APPROVE', 'REJECT'], true)) {
            throw new ApprovalWorkflowException('Action harus APPROVE atau REJECT.', 'ACTION_INVALID', 422);
        }

        return DB::transaction(function () use ($requisitionId, $userId, $action, $notes) {
            $requisition = DB::table('requisition')->where('ID', $requisitionId)->lockForUpdate()->first();
            if (!$requisition) {
                throw new ApprovalWorkflowException('Requisition tidak ditemukan.', 'REQUISITION_NOT_FOUND', 404);
            }
            if (empty($requisition->Approval_Request_ID)) {
                throw new ApprovalWorkflowException(
                    'Requisition ini masih memakai approval hardcoded lama.',
                    'APPROVAL_NOT_LINKED',
                    422
                );
            }

            $request = $this->workflow->getRequest((int) $requisition->Approval_Request_ID);
            if (!$request || in_array(strtoupper((string) $request->Status), ['APPROVED', 'REJECTED', 'RETURNED', 'CANCELLED'], true)) {
                throw new ApprovalWorkflowException('Request approval sudah tidak aktif.', 'REQUEST_NOT_ACTIVE', 422);
            }

            $tasks = $this->pendingTasksForUser($requisitionId, $userId);
            if (empty($tasks)) {
                throw new ApprovalWorkflowException(
                    'Tidak ada task approval aktif untuk Anda pada requisition ini.',
                    'TASK_NOT_ASSIGNED',
                    403
                );
            }

            $acted = [];
            foreach ($tasks as $task) {
                $current = DB::table('approval_request_task')->where('ID', (int) $task->ID)->first();
                if (!$current || !in_array(strtoupper((string) $current->Status), ['PENDING', 'CLAIMED'], true)) {
                    continue;
                }

                if (strtoupper((string) $current->Status) === 'PENDING') {
                    $this->workflow->claim((int) $current->ID, $userId);
                }

                $this->workflow->act((int) $current->ID, $userId, $action, $notes);
                $acted[] = (int) $current->ID;

                // Reject menghentikan stage/request; task lain tidak perlu diproses.
                if ($action === 'REJECT') {
                    break;
                }
            }

            if (empty($acted)) {
                throw new ApprovalWorkflowException('Tidak ada task yang dapat diproses.', 'TASK_NOT_ACTIVE', 422);
            }

            $this->syncLegacyRows($requisitionId);
            $this->syncRequisitionStatus($requisitionId);

            return $acted;
        });
    }

    /**
     * Sinkronkan status requisition dengan status approval_request.
     */
    public function syncRequisitionStatus(int $requisitionId): void
    {
        $requestId = (int) DB::table('requisition')
            ->where('ID', $requisitionId)
            ->value('Approval_Request_ID');

        if ($requestId < 1) {
            return;
        }

        $request = $this->workflow->getRequest($requestId);
        if (!$request) {
            return;
        }

        DB::table('requisition')->where('ID', $requisitionId)->update([
            'Status' => $this->mapRequisitionStatus($request->Status),
        ]);
    }

    /**
     * Tulis ulang requisition_approval sebagai mirror dari task engine.
     * Hanya dilakukan untuk requisition yang sudah punya Approval_Request_ID.
     */
    public function syncLegacyRows(int $requisitionId): void
    {
        $requisition = DB::table('requisition')->where('ID', $requisitionId)->first();
        if (!$requisition || empty($requisition->Approval_Request_ID) || empty($requisition->Task_ID)) {
            return;
        }

        $tasks = DB::table('approval_request_task')
            ->where('Request_ID', (int) $requisition->Approval_Request_ID)
            ->orderBy('Stage_Order', 'ASC')
            ->orderBy('ID', 'ASC')
            ->get();

        DB::table('requisition_approval')->where('Task_ID', $requisition->Task_ID)->delete();

        foreach ($tasks as $task) {
            $candidate = DB::table('approval_task_candidate as c')
                ->join('users as u', 'u.id', '=', 'c.User_Id')
                ->where('c.Task_ID', (int) $task->ID)
                ->where('c.Status', 'ACTIVE')
                ->orderBy('c.ID', 'ASC')
                ->select('u.username')
                ->first();

            $approvedBy = $candidate ? $candidate->username : 'SYSTEM';

            DB::table('requisition_approval')->insert([
                'Location' => $requisition->Location,
                'Task_ID' => $requisition->Task_ID,
                'Level' => (int) $task->Stage_Order,
                'Approved_By' => $approvedBy,
                'Approved_Status' => $this->mapLegacyApprovalStatus($task->Status),
                'Approved_Date' => $task->Acted_At ?: null,
                'Approved_Note' => $task->Notes ?: null,
            ]);
        }
    }

    protected function buildContext($requisition, $jobPosition, $user): array
    {
        $location = trim((string) ($requisition->Location ?: $user->Location));
        $company = trim((string) ($user->Company_ID ?: ($jobPosition->Company_ID ?? '')));
        // Departemen approval mengikuti departemen posisi yang diminta (target),
        // bukan departemen user yang mengajukan. Contoh: user ICT mengajukan posisi PROC,
        // approver-nya harus Manager PROC.
        $department = trim((string) ($requisition->Department ?: $user->Department));

        if ($location === '' || $company === '') {
            throw new ApprovalWorkflowException(
                'Location dan Company_ID user wajib terisi sebelum requisition dapat diajukan ke approval policy.',
                'CONTEXT_INVALID',
                422
            );
        }

        return [
            'Request_Type' => self::REQUEST_TYPE,
            'Location' => $location,
            'Company_ID' => $company,
            'Department' => $department,
            'Amount' => 0,
            'Requisition_ID' => (int) $requisition->ID,
            'Task_ID' => $requisition->Task_ID,
            'Position_ID' => (int) $jobPosition->ID,
            'Position_Code' => $jobPosition->Initial,
            'Position_Level' => (int) $jobPosition->Level,
            'Target_Department' => $requisition->Department,
        ];
    }

    protected function description($requisition, $jobPosition): string
    {
        $parts = array_filter([
            $requisition->Task_ID,
            $requisition->Subject ?: $jobPosition->Position,
            $requisition->Department,
        ]);

        return implode(' - ', $parts);
    }

    protected function mapRequisitionStatus($approvalStatus): string
    {
        $status = strtoupper((string) $approvalStatus);

        if ($status === 'APPROVED') {
            return 'APPROVED';
        }
        if ($status === 'REJECTED' || $status === 'RETURNED') {
            return 'REJECTED';
        }
        if ($status === 'CANCELLED') {
            return 'CANCELED';
        }

        return 'ON_PROGRESS';
    }

    protected function mapLegacyApprovalStatus($taskStatus): int
    {
        $status = strtoupper((string) $taskStatus);

        if ($status === 'APPROVED' || $status === 'AUTO_APPROVED') {
            return 1;
        }
        if ($status === 'REJECTED' || $status === 'CANCELLED') {
            return 2;
        }

        return 0;
    }
}
