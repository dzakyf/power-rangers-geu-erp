<?php

namespace App\Services\Approval;

use App\Exceptions\ApprovalWorkflowException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApprovalWorkflowService
{
    protected $policyResolver;
    protected $candidateResolver;
    protected $notifications;
    protected $authorization;

    public function __construct(
        ApprovalPolicyResolver $policyResolver,
        ApprovalCandidateResolver $candidateResolver,
        ApprovalNotificationService $notifications,
        ApprovalAuthorizationService $authorization
    ) {
        $this->policyResolver = $policyResolver;
        $this->candidateResolver = $candidateResolver;
        $this->notifications = $notifications;
        $this->authorization = $authorization;
    }

    public function submit(array $data, array $context, int $requesterId = null, array $options = [])
    {
        $requesterId = $requesterId ?: (int) auth()->id();
        if ($requesterId < 1) {
            throw new ApprovalWorkflowException('Requester tidak valid.', 'REQUESTER_INVALID', 422);
        }

        $context = $this->policyResolver->normalizeContext($context);
        $resolved = $this->policyResolver->resolve($context);
        $policy = $resolved['policy'];
        $steps = $resolved['steps'];
        $this->policyResolver->validatePolicy($policy, $steps);

        return DB::transaction(function () use ($data, $context, $requesterId, $options, $policy, $steps, $resolved) {
            $now = Carbon::now();

            $requestId = DB::table('approval_request')->insertGetId([
                'Request_No' => $data['Request_No'] ?? null,
                'Status' => 'IN_PROGRESS',
                'Requester_Id' => $requesterId,
                'Description' => (string) ($data['Description'] ?? ''),
                'Request_Type' => $context['Request_Type'],
                'Submitted_At' => $now,
                'Created_At' => $now,
                'Current_Level' => 0,
                'Revision_No' => (int) ($options['Revision_No'] ?? 1),
                'Parent_Request_ID' => $options['Parent_Request_ID'] ?? null,
                'Policy_ID' => (int) $policy->ID,
                'Policy_Version' => (int) $policy->Version,
                'Policy_Snapshot' => json_encode($resolved['snapshot']),
                'Context' => json_encode($context),
                'Location' => $context['Location'],
                'Company_ID' => $context['Company_ID'],
                'Department' => $context['Department'],
                'Amount' => $context['Amount'],
                'Current_Stage' => 0,
                'Updated_At' => $now,
            ]);

            if (empty($data['Request_No'])) {
                DB::table('approval_request')->where('ID', $requestId)->update([
                    'Request_No' => 'APR/' . date('Y') . '/' . str_pad((string) $requestId, 6, '0', STR_PAD_LEFT),
                ]);
            }

            $this->log($requestId, $requesterId, 'SUBMITTED', 0, null, null, null, null, [
                'Request_Type' => $context['Request_Type'],
                'Policy_ID' => (int) $policy->ID,
                'Revision_No' => (int) ($options['Revision_No'] ?? 1),
            ]);

            if (empty($steps)) {
                $this->completeRequest($requestId, 'NO_POLICY_STEP');
                return $this->getRequest($requestId);
            }

            $firstStage = (int) $steps[0]->Stage_Order;
            $firstLevel = (int) $steps[0]->Level_Number;

            DB::table('approval_request')->where('ID', $requestId)->update([
                'Current_Stage' => $firstStage,
                'Current_Level' => $firstLevel,
                'Status' => 'IN_PROGRESS',
                'Updated_At' => Carbon::now(),
            ]);

            $this->createStageTasks($requestId, $policy, $steps, $firstStage, $context, $requesterId);
            $this->evaluateRequest($requestId);

            return $this->getRequest($requestId);
        });
    }

    public function resubmit(int $requestId, array $context = [], int $userId = null)
    {
        $old = $this->getRequest($requestId);
        if (!$old) {
            throw new ApprovalWorkflowException('Request tidak ditemukan.', 'REQUEST_NOT_FOUND', 404);
        }

        if (!in_array(strtoupper((string) $old->Status), ['RETURNED', 'REJECTED'], true)) {
            throw new ApprovalWorkflowException(
                'Hanya request RETURNED atau REJECTED yang dapat diajukan ulang.',
                'RESUBMIT_INVALID_STATUS',
                422,
                ['Status' => $old->Status]
            );
        }

        $oldContext = $this->decodeJson($old->Context, []);
        $context = array_merge($oldContext, $context);
        $context['Request_Type'] = $context['Request_Type'] ?? $old->Request_Type;

        if ($userId && (int) $userId !== (int) $old->Requester_Id) {
            $user = \App\User::find($userId);
            if (!$this->authorization->isWildcardAdmin($user)) {
                throw new ApprovalWorkflowException('Hanya requester atau wildcard admin yang dapat resubmit.', 'RESUBMIT_FORBIDDEN', 403);
            }
        }

        $data = [
            'Description' => $old->Description,
            'Request_Type' => $old->Request_Type,
        ];

        return $this->submit($data, $context, (int) $old->Requester_Id, [
            'Parent_Request_ID' => $old->ID,
            'Revision_No' => (int) $old->Revision_No + 1,
        ]);
    }

    public function claim(int $taskId, int $userId)
    {
        return DB::transaction(function () use ($taskId, $userId) {
            $task = DB::table('approval_request_task')->where('ID', $taskId)->lockForUpdate()->first();
            if (!$task) {
                throw new ApprovalWorkflowException('Task approval tidak ditemukan.', 'TASK_NOT_FOUND', 404);
            }

            $request = $this->getRequest((int) $task->Request_ID, true);
            if (!$request || in_array(strtoupper((string) $request->Status), ['APPROVED', 'REJECTED', 'RETURNED', 'CANCELLED'], true)) {
                throw new ApprovalWorkflowException('Request sudah tidak aktif.', 'REQUEST_NOT_ACTIVE', 422);
            }

            $status = strtoupper((string) $task->Status);
            if ($status === 'CLAIMED') {
                if ((int) $task->Claimed_By === $userId) {
                    return $task;
                }
                throw new ApprovalWorkflowException('Task sudah di-claim oleh approver lain.', 'CLAIM_CONFLICT', 409);
            }
            if (!in_array($status, ['PENDING', 'HELD'], true)) {
                throw new ApprovalWorkflowException('Task tidak dapat di-claim pada status ' . $status . '.', 'TASK_NOT_CLAIMABLE', 422);
            }
            $candidate = DB::table('approval_task_candidate')
                ->where('Task_ID', $taskId)
                ->where('User_Id', $userId)
                ->where('Status', 'ACTIVE')
                ->first();

            if (!$candidate) {
                throw new ApprovalWorkflowException('Anda bukan kandidat untuk task ini.', 'NOT_A_CANDIDATE', 403);
            }

            if ($task->Claimed_By !== null && (int) $task->Claimed_By !== $userId) {
                throw new ApprovalWorkflowException('Task sudah di-claim oleh approver lain.', 'CLAIM_CONFLICT', 409);
            }

            $this->assertSelfApprovalAllowed($request, $task, $userId);

            DB::table('approval_request_task')->where('ID', $taskId)->update([
                'Status' => 'CLAIMED',
                'Claimed_By' => $userId,
                'Claimed_At' => Carbon::now(),
                'Held_At' => null,
                'Held_Reason' => null,
                'Updated_At' => Carbon::now(),
            ]);

            $this->log(
                (int) $request->ID,
                $userId,
                'CLAIMED',
                (int) $task->Level_Number,
                (int) $task->Stage_Order,
                (int) $task->Position_ID,
                $task->Position_Code,
                $taskId,
                ['Candidate_Type' => $candidate->Candidate_Type]
            );

            return DB::table('approval_request_task')->where('ID', $taskId)->first();
        });
    }

    public function release(int $taskId, int $adminId, string $reason)
    {
        if (trim($reason) === '') {
            throw new ApprovalWorkflowException('Alasan release claim wajib diisi.', 'RELEASE_REASON_REQUIRED', 422);
        }

        $admin = \App\User::find($adminId);
        $this->authorization->assertCanReleaseClaim($admin);

        return DB::transaction(function () use ($taskId, $adminId, $reason) {
            $task = DB::table('approval_request_task')->where('ID', $taskId)->lockForUpdate()->first();
            if (!$task) {
                throw new ApprovalWorkflowException('Task approval tidak ditemukan.', 'TASK_NOT_FOUND', 404);
            }
            if (strtoupper((string) $task->Status) !== 'CLAIMED') {
                throw new ApprovalWorkflowException('Hanya task berstatus CLAIMED yang dapat di-release.', 'TASK_NOT_CLAIMED', 422);
            }

            DB::table('approval_request_task')->where('ID', $taskId)->update([
                'Status' => 'PENDING',
                'Claimed_By' => null,
                'Claimed_At' => null,
                'Released_By' => $adminId,
                'Released_At' => Carbon::now(),
                'Release_Reason' => $reason,
                'Updated_At' => Carbon::now(),
            ]);

            $request = $this->getRequest((int) $task->Request_ID);
            $this->log(
                (int) $task->Request_ID,
                $adminId,
                'CLAIM_RELEASED',
                (int) $task->Level_Number,
                (int) $task->Stage_Order,
                (int) $task->Position_ID,
                $task->Position_Code,
                $taskId,
                ['Reason' => $reason]
            );

            $candidates = DB::table('approval_task_candidate')
                ->where('Task_ID', $taskId)
                ->where('Status', 'ACTIVE')
                ->get()
                ->map(function ($row) {
                    return ['User_Id' => (int) $row->User_Id, 'Type' => $row->Candidate_Type];
                })
                ->all();

            $this->notifications->notifyTaskCandidates(
                DB::table('approval_request_task')->where('ID', $taskId)->first(),
                $request,
                $candidates
            );

            return DB::table('approval_request_task')->where('ID', $taskId)->first();
        });
    }

    public function act(int $taskId, int $userId, string $action, string $notes = '')
    {
        $action = strtoupper(trim($action));
        if (!in_array($action, ['APPROVE', 'REJECT'], true)) {
            throw new ApprovalWorkflowException('Action harus APPROVE atau REJECT.', 'ACTION_INVALID', 422);
        }

        return DB::transaction(function () use ($taskId, $userId, $action, $notes) {
            $task = DB::table('approval_request_task')->where('ID', $taskId)->lockForUpdate()->first();
            if (!$task) {
                throw new ApprovalWorkflowException('Task approval tidak ditemukan.', 'TASK_NOT_FOUND', 404);
            }

            $request = $this->getRequest((int) $task->Request_ID, true);
            if (!$request || in_array(strtoupper((string) $request->Status), ['APPROVED', 'REJECTED', 'RETURNED', 'CANCELLED'], true)) {
                throw new ApprovalWorkflowException('Request sudah tidak aktif.', 'REQUEST_NOT_ACTIVE', 422);
            }

            if (strtoupper((string) $task->Status) !== 'CLAIMED' || (int) $task->Claimed_By !== $userId) {
                throw new ApprovalWorkflowException('Task harus di-claim oleh Anda sebelum diambil keputusan.', 'TASK_NOT_OWNED', 403);
            }

            $this->assertSelfApprovalAllowed($request, $task, $userId);

            $candidate = DB::table('approval_task_candidate')
                ->where('Task_ID', $taskId)
                ->where('User_Id', $userId)
                ->where('Status', 'ACTIVE')
                ->first();

            if (!$candidate) {
                throw new ApprovalWorkflowException('Anda bukan kandidat aktif untuk task ini.', 'NOT_CANDIDATE', 403);
            }

            $onBehalfOf = null;
            if ($candidate && $candidate->Candidate_Type === 'DELEGATE' && $candidate->Delegation_ID) {
                $delegation = DB::table('approval_delegation')->where('ID', $candidate->Delegation_ID)->first();
                $onBehalfOf = $delegation ? (int) $delegation->Delegator_Id : null;
            }

            $newStatus = $action === 'APPROVE' ? 'APPROVED' : 'REJECTED';

            DB::table('approval_request_task')->where('ID', $taskId)->update([
                'Status' => $newStatus,
                'Acted_By' => $userId,
                'Acted_At' => Carbon::now(),
                'Action' => $action,
                'Notes' => $notes,
                'Updated_At' => Carbon::now(),
            ]);

            $this->log(
                (int) $request->ID,
                $userId,
                $action,
                (int) $task->Level_Number,
                (int) $task->Stage_Order,
                (int) $task->Position_ID,
                $task->Position_Code,
                $taskId,
                ['Notes' => $notes],
                false,
                $onBehalfOf
            );

            if ($action === 'REJECT') {
                $this->handleRejection($request, $task);
            } else {
                $this->evaluateRequest((int) $request->ID);
            }

            return DB::table('approval_request_task')->where('ID', $taskId)->first();
        });
    }

    /**
     * Helper praktis untuk claim dan approve task dalam satu pemanggilan.
     *
     * @param int $taskId
     * @param string $notes
     * @param int|null $userId
     * @return object
     */
    public function approve(int $taskId, string $notes = '', int $userId = null)
    {
        $userId = $userId ?: ((int) (auth()->id() ?: 1));
        $task = DB::table('approval_request_task')->where('ID', $taskId)->first();
        if ($task && (strtoupper((string) $task->Status) !== 'CLAIMED' || (int) $task->Claimed_By !== $userId)) {
            try {
                $this->claim($taskId, $userId);
            } catch (\Exception $e) {
                // Ignore jika task sudah di-claim sebelumnya
            }
        }
        return $this->act($taskId, $userId, 'APPROVE', $notes);
    }

    /**
     * Helper praktis untuk claim dan reject task dalam satu pemanggilan.
     *
     * @param int $taskId
     * @param string $notes
     * @param int|null $userId
     * @return object
     */
    public function reject(int $taskId, string $notes = '', int $userId = null)
    {
        $userId = $userId ?: ((int) (auth()->id() ?: 1));
        $task = DB::table('approval_request_task')->where('ID', $taskId)->first();
        if ($task && (strtoupper((string) $task->Status) !== 'CLAIMED' || (int) $task->Claimed_By !== $userId)) {
            try {
                $this->claim($taskId, $userId);
            } catch (\Exception $e) {
                // Ignore jika task sudah di-claim sebelumnya
            }
        }
        return $this->act($taskId, $userId, 'REJECT', $notes);
    }

    public function grantSelfApprovalException(int $requestId, int $userId, int $adminId, string $reason, $expiresAt = null)
    {
        if (trim($reason) === '') {
            throw new ApprovalWorkflowException('Alasan exception self-approval wajib diisi.', 'SELF_APPROVAL_REASON_REQUIRED', 422);
        }

        $admin = \App\User::find($adminId);
        $this->authorization->assertCanGrantSelfApproval($admin);

        $request = $this->getRequest($requestId);
        if (!$request) {
            throw new ApprovalWorkflowException('Request tidak ditemukan.', 'REQUEST_NOT_FOUND', 404);
        }

        if ((int) $request->Requester_Id !== (int) $userId) {
            throw new ApprovalWorkflowException(
                'Exception self-approval hanya dapat diberikan kepada requester request ini.',
                'SELF_APPROVAL_USER_INVALID',
                422
            );
        }

        return DB::transaction(function () use ($requestId, $userId, $adminId, $reason, $expiresAt) {
            $exceptionId = DB::table('approval_self_exception')->insertGetId([
                'Request_ID' => $requestId,
                'Task_ID' => null,
                'User_Id' => $userId,
                'Granted_By' => $adminId,
                'Permission_Used' => 'approval.self-approval.grant',
                'Reason' => $reason,
                'Expires_At' => $expiresAt,
                'Created_At' => Carbon::now(),
            ]);

            $this->log($requestId, $adminId, 'SELF_APPROVAL_EXCEPTION_GRANTED', 0, null, null, null, null, [
                'Exception_ID' => $exceptionId,
                'User_Id' => $userId,
                'Reason' => $reason,
                'Expires_At' => $expiresAt,
            ]);

            return $exceptionId;
        });
    }

    public function cancel(int $requestId, int $userId, string $reason = ''): void
    {
        DB::transaction(function () use ($requestId, $userId, $reason) {
            $request = $this->getRequest($requestId, true);
            if (!$request) {
                throw new ApprovalWorkflowException('Request tidak ditemukan.', 'REQUEST_NOT_FOUND', 404);
            }
            if (in_array(strtoupper((string) $request->Status), ['APPROVED', 'REJECTED', 'CANCELLED'], true)) {
                throw new ApprovalWorkflowException('Request sudah final.', 'REQUEST_FINAL', 422);
            }

            DB::table('approval_request')->where('ID', $requestId)->update([
                'Status' => 'CANCELLED',
                'Cancelled_At' => Carbon::now(),
                'Updated_At' => Carbon::now(),
            ]);

            $this->cancelRemainingTasks($requestId);
            $this->log($requestId, $userId, 'CANCELLED', 0, null, null, null, null, ['Reason' => $reason]);

            $fresh = $this->getRequest($requestId);
            if ($fresh) {
                event(new \App\Events\Approval\ApprovalRequestUpdated($fresh, 'CANCELLED', $reason));
            }
        });
    }

    public function addCandidate(int $taskId, int $userId, int $adminId, string $reason = ''): void
    {
        $admin = \App\User::find($adminId);
        $this->authorization->assertCanGrantSelfApproval($admin);

        DB::transaction(function () use ($taskId, $userId, $adminId, $reason) {
            $task = DB::table('approval_request_task')->where('ID', $taskId)->lockForUpdate()->first();
            if (!$task) {
                throw new ApprovalWorkflowException('Task approval tidak ditemukan.', 'TASK_NOT_FOUND', 404);
            }
            if (!in_array(strtoupper((string) $task->Status), ['PENDING', 'HELD'], true)) {
                throw new ApprovalWorkflowException('Kandidat hanya dapat ditambahkan pada task PENDING/HELD.', 'TASK_NOT_ASSIGNABLE', 422);
            }

            $targetUser = DB::table('users')->where('id', $userId)->first();
            if (!$targetUser || strtoupper((string) $targetUser->Status) !== 'ACTIVE') {
                throw new ApprovalWorkflowException('User tidak ditemukan atau tidak aktif.', 'CANDIDATE_INVALID', 422);
            }

            $taskRequest = DB::table('approval_request')->where('ID', (int) $task->Request_ID)->first();
            if ($taskRequest && (int) $taskRequest->Requester_Id === $userId) {
                throw new ApprovalWorkflowException('Requester tidak dapat ditugaskan sebagai approver permohonannya sendiri.', 'CANDIDATE_IS_REQUESTER', 422);
            }

            $exists = DB::table('approval_task_candidate')
                ->where('Task_ID', $taskId)
                ->where('User_Id', $userId)
                ->where('Status', 'ACTIVE')
                ->exists();

            if ($exists) {
                throw new ApprovalWorkflowException('User sudah menjadi kandidat pada task ini.', 'CANDIDATE_EXISTS', 422);
            }

            DB::table('approval_task_candidate')->insert([
                'Task_ID' => $taskId,
                'User_Id' => $userId,
                'Position_ID' => (int) $task->Position_ID,
                'Candidate_Type' => 'ADMIN',
                'Status' => 'ACTIVE',
                'Created_At' => Carbon::now(),
            ]);

            $request = $this->getRequest((int) $task->Request_ID);
            $this->notifications->notifyTaskCandidates(
                $task,
                $request,
                [['User_Id' => $userId, 'Type' => 'ADMIN']]
            );

            $this->log(
                (int) $task->Request_ID,
                $adminId,
                'CANDIDATE_ADDED',
                (int) $task->Level_Number,
                (int) $task->Stage_Order,
                (int) $task->Position_ID,
                $task->Position_Code,
                $taskId,
                ['User_Id' => $userId, 'Reason' => $reason]
            );
        });
    }

    protected function createStageTasks(int $requestId, $policy, array $steps, int $stageOrder, array $context, int $requesterId): void
    {
        $stageSteps = array_values(array_filter($steps, function ($step) use ($stageOrder) {
            return (int) $step->Stage_Order === $stageOrder;
        }));

        if (empty($stageSteps)) {
            return;
        }

        $request = $this->getRequest($requestId);
        $created = 0;
        $held = false;

        foreach ($stageSteps as $step) {
            $resolution = $this->candidateResolver->resolve($policy, $step, $context, $requesterId);

            if (empty($resolution['positions'])) {
                $stepPosCode = !empty($step->Position_Code) ? (string) $step->Position_Code : null;
                $stepLabel = !empty($step->Label) ? (string) $step->Label : ($stepPosCode ?: ('Stage ' . $stageOrder));
                if ($resolution['Action'] === 'HOLD' || $resolution['Action'] === 'BLOCK') {
                    $held = true;
                    $this->log($requestId, 0, 'STAGE_HELD', (int) $step->Level_Number, $stageOrder, !empty($step->Position_ID) ? (int) $step->Position_ID : null, $stepPosCode, null, [
                        'Reason' => $resolution['Reason'],
                        'Action' => $resolution['Action'],
                        'Label'  => $stepLabel,
                    ], true);
                } else {
                    $skipReason = 'Dilewati otomatis sistem (' . ($resolution['Reason'] ?: 'NO_HIGHER_LEVEL') . ')';
                    $stepMode = strtoupper((string) ($step->Approval_Mode ?? 'ALL'));
                    $taskId = null;

                    // Catat baris task AUTO_APPROVED untuk stage yang dilewati agar audit trail
                    // tetap mencatat posisi (mis. VP / VPD) beserta alasan dilewatinya.
                    if ($stepMode !== 'ANY' || count($stageSteps) === 1) {
                        $now = Carbon::now();
                        $taskId = DB::table('approval_request_task')->insertGetId([
                            'Request_ID'       => $requestId,
                            'Stage_Order'      => $stageOrder,
                            'Level_Number'     => (int) ($step->Level_Number ?? 0),
                            'Position_ID'      => (int) ($step->Position_ID ?? 0),
                            'Position_Code'    => $stepPosCode,
                            'Position_Name'    => mb_substr($stepLabel, 0, 150),
                            'Department'       => !empty($step->Department) ? (string) $step->Department : ($context['Department'] ?? null),
                            'Approval_Mode'    => $stepMode,
                            'Quorum'           => $step->Quorum ?? null,
                            'Status'           => 'AUTO_APPROVED',
                            'Acted_By'         => 0,
                            'Acted_At'         => $now,
                            'Action'           => 'AUTO_SKIP',
                            'Notes'            => $skipReason,
                            'Is_Self_Approval' => 0,
                            'Snapshot'         => json_encode([
                                'Step'         => (array) $step,
                                'Reason'       => $resolution['Reason'],
                                'Auto_Skipped' => true,
                            ]),
                            'Created_At'       => $now,
                            'Updated_At'       => $now,
                        ]);
                        $created++;
                    }

                    $this->log($requestId, 0, 'STAGE_AUTO_APPROVED', (int) $step->Level_Number, $stageOrder, !empty($step->Position_ID) ? (int) $step->Position_ID : null, $stepPosCode, $taskId, [
                        'Reason' => $stepLabel . ': ' . $skipReason,
                    ], true);
                }
                continue;
            }

            foreach ($resolution['positions'] as $item) {
                $position = $item['Position'];
                $existing = DB::table('approval_request_task')
                    ->where('Request_ID', $requestId)
                    ->where('Stage_Order', $stageOrder)
                    ->where('Position_ID', (int) $position->ID)
                    ->first();

                if ($existing) {
                    continue;
                }

                $slaHours = isset($step->Sla_Hours) && (int) $step->Sla_Hours > 0 ? (int) $step->Sla_Hours : 48;

                $taskId = DB::table('approval_request_task')->insertGetId([
                    'Request_ID' => $requestId,
                    'Stage_Order' => $stageOrder,
                    'Level_Number' => (int) $position->Level,
                    'Position_ID' => (int) $position->ID,
                    'Position_Code' => $position->Initial,
                    'Position_Name' => $position->Position,
                    'Department' => $position->Department,
                    'Approval_Mode' => strtoupper((string) $step->Approval_Mode),
                    'Quorum' => $step->Quorum,
                    'Status' => 'PENDING',
                    'Is_Self_Approval' => $this->hasCandidate($item['Candidates'], $requesterId) ? 1 : 0,
                    'Due_At' => Carbon::now()->addHours($slaHours),
                    'Snapshot' => json_encode([
                        'Step' => (array) $step,
                        'Position' => (array) $position,
                        'Candidates' => $item['Candidates'],
                        'Fallback' => $item['Fallback'],
                    ]),
                    'Created_At' => Carbon::now(),
                    'Updated_At' => Carbon::now(),
                ]);

                foreach ($item['Candidates'] as $candidate) {
                    $exists = DB::table('approval_task_candidate')
                        ->where('Task_ID', $taskId)
                        ->where('User_Id', $candidate['User_Id'])
                        ->where('Candidate_Type', $candidate['Type'])
                        ->exists();

                    if (!$exists) {
                        DB::table('approval_task_candidate')->insert([
                            'Task_ID' => $taskId,
                            'User_Id' => $candidate['User_Id'],
                            'Position_ID' => (int) $position->ID,
                            'Candidate_Type' => $candidate['Type'],
                            'Delegation_ID' => $candidate['Delegation_ID'] ?? null,
                            'Status' => 'ACTIVE',
                            'Created_At' => Carbon::now(),
                        ]);
                    }
                }

                $task = DB::table('approval_request_task')->where('ID', $taskId)->first();
                $action = $item['Action'];
                $reason = $item['Reason'];

                if ($action === 'AUTO_APPROVE') {
                    $this->autoApproveTask($task, $request, $reason ?: 'NO_HIGHER_LEVEL');
                } elseif ($action === 'HOLD' || $action === 'BLOCK') {
                    $held = true;
                    $this->holdTask($task, $request, ($action === 'BLOCK' ? 'BLOCKED:' : '') . ($reason ?: 'NO_CANDIDATE'));
                } else {
                    $this->notifications->notifyTaskCandidates($task, $request, $item['Candidates']);
                }

                $this->log($requestId, 0, 'TASK_CREATED', (int) $task->Level_Number, $stageOrder, (int) $task->Position_ID, $task->Position_Code, $taskId, [
                    'Candidates' => $item['Candidates'],
                    'Action' => $action,
                    'Fallback' => $item['Fallback'],
                ], true);

                $created++;
            }
        }

        if ($created === 0 && !$held) {
            $this->log($requestId, 0, 'STAGE_AUTO_APPROVED', 0, $stageOrder, null, null, null, [
                'Reason' => 'NO_TASK_FOR_STAGE',
            ], true);
        }
    }

    protected function evaluateRequest(int $requestId, int $depth = 0): void
    {
        if ($depth > 50) {
            throw new ApprovalWorkflowException('Approval stage loop terdeteksi.', 'STAGE_LOOP', 500);
        }

        $request = $this->getRequest($requestId);
        if (!$request || in_array(strtoupper((string) $request->Status), ['APPROVED', 'REJECTED', 'RETURNED', 'CANCELLED'], true)) {
            return;
        }

        $tasks = DB::table('approval_request_task')
            ->where('Request_ID', $requestId)
            ->where('Stage_Order', (int) $request->Current_Stage)
            ->orderBy('ID', 'ASC')
            ->get();

        if ($tasks->isEmpty()) {
            $stageHeld = DB::table('approval_log')
                ->where('Request_ID', $requestId)
                ->where('Stage_Order', (int) $request->Current_Stage)
                ->where('Action', 'STAGE_HELD')
                ->exists();

            if ($stageHeld) {
                DB::table('approval_request')->where('ID', $requestId)->update([
                    'Status' => 'BLOCKED',
                    'Updated_At' => Carbon::now(),
                ]);
                return;
            }

            $this->completeStage($request, (int) $request->Current_Stage);
            $this->evaluateRequest($requestId, $depth + 1);
            return;
        }

        $counts = [
            'APPROVED' => 0,
            'REJECTED' => 0,
            'PENDING' => 0,
            'HELD' => 0,
        ];

        foreach ($tasks as $task) {
            $status = strtoupper((string) $task->Status);
            if ($status === 'AUTO_APPROVED' || $status === 'APPROVED') {
                $counts['APPROVED']++;
            } elseif ($status === 'REJECTED') {
                $counts['REJECTED']++;
            } elseif ($status === 'HELD') {
                $counts['HELD']++;
            } elseif ($status === 'PENDING' || $status === 'CLAIMED') {
                $counts['PENDING']++;
            }
        }

        $first = $tasks->first();
        $mode = strtoupper((string) $first->Approval_Mode);
        $quorum = (int) $first->Quorum;

        $approved = $counts['APPROVED'];
        $stageComplete = false;

        if ($mode === 'ANY') {
            $stageComplete = $approved >= 1;
        } elseif ($mode === 'QUORUM') {
            $stageComplete = $approved >= max(1, $quorum);
        } else {
            $stageComplete = $approved > 0 && $approved === $tasks->count();
        }

        if ($stageComplete) {
            $this->completeStage($request, (int) $request->Current_Stage);
            $this->evaluateRequest($requestId, $depth + 1);
            return;
        }

        if ($counts['PENDING'] > 0) {
            DB::table('approval_request')->where('ID', $requestId)->update([
                'Status' => 'IN_PROGRESS',
                'Updated_At' => Carbon::now(),
            ]);
            return;
        }

        if ($counts['HELD'] > 0) {
            DB::table('approval_request')->where('ID', $requestId)->update([
                'Status' => 'BLOCKED',
                'Updated_At' => Carbon::now(),
            ]);
            return;
        }

        // No pending tasks and the stage is not approved: apply rejection policy.
        $this->handleStageFailure($request, $tasks);
    }

    protected function completeStage($request, int $stageOrder): void
    {
        $this->cancelRemainingTasks((int) $request->ID, $stageOrder);

        $this->log((int) $request->ID, 0, 'STAGE_COMPLETED', 0, $stageOrder, null, null, null, [], true);

        $steps = $this->stepsForRequest($request);
        $nextStage = null;
        $nextLevel = null;

        foreach ($steps as $step) {
            $stepStage = (int) $step['Stage_Order'];
            if ($stepStage > $stageOrder && ($nextStage === null || $stepStage < $nextStage)) {
                $nextStage = $stepStage;
                $nextLevel = (int) $step['Level_Number'];
            }
        }

        if ($nextStage === null) {
            $this->completeRequest((int) $request->ID, 'ALL_STAGES_COMPLETED');
            return;
        }

        DB::table('approval_request')->where('ID', $request->ID)->update([
            'Current_Stage' => $nextStage,
            'Current_Level' => $nextLevel,
            'Status' => 'IN_PROGRESS',
            'Updated_At' => Carbon::now(),
        ]);

        $policy = $this->policyForRequest($request);
        $context = $this->decodeJson($request->Context, []);
        $this->createStageTasks(
            (int) $request->ID,
            $policy,
            $this->stepsAsObjects($steps),
            $nextStage,
            $context,
            (int) $request->Requester_Id
        );

        $fresh = $this->getRequest((int) $request->ID);
        if ($fresh) {
            event(new \App\Events\Approval\ApprovalStageProgressed($fresh, $stageOrder, (int) $nextStage, $nextLevel !== null ? (int) $nextLevel : null));
        }
    }

    protected function handleRejection($request, $task): void
    {
        $policy = $this->policyForRequest($request);
        $mode = strtoupper((string) ($policy->Rejection_Mode ?? 'RETURN_TO_CREATOR'));

        if ($mode === 'STOP') {
            $this->rejectRequest((int) $request->ID, 'REJECTED_BY_APPROVER');
            return;
        }

        if ($mode === 'WAIT_GROUP') {
            $rejected = DB::table('approval_request_task')
                ->where('Request_ID', (int) $request->ID)
                ->where('Stage_Order', (int) $task->Stage_Order)
                ->where('Status', 'REJECTED')
                ->count();

            $quorum = (int) ($policy->Rejection_Quorum ?? 0);
            if ($quorum > 0 && $rejected >= $quorum) {
                $this->rejectRequest((int) $request->ID, 'GROUP_REJECT_QUORUM');
                return;
            }

            $this->evaluateRequest((int) $request->ID);
            return;
        }

        $this->returnRequest((int) $request->ID, 'REJECTED_RETURN_TO_CREATOR');
    }

    protected function handleStageFailure($request, $tasks): void
    {
        $policy = $this->policyForRequest($request);
        $mode = strtoupper((string) ($policy->Rejection_Mode ?? 'RETURN_TO_CREATOR'));

        if ($mode === 'WAIT_GROUP') {
            $rejected = 0;
            foreach ($tasks as $task) {
                if (strtoupper((string) $task->Status) === 'REJECTED') {
                    $rejected++;
                }
            }

            $quorum = (int) ($policy->Rejection_Quorum ?? 0);
            if ($quorum > 0 && $rejected >= $quorum) {
                $this->rejectRequest((int) $request->ID, 'GROUP_REJECT_QUORUM');
                return;
            }

            DB::table('approval_request')->where('ID', $request->ID)->update([
                'Status' => 'BLOCKED',
                'Updated_At' => Carbon::now(),
            ]);
            $this->log((int) $request->ID, 0, 'WAIT_GROUP_BLOCKED', 0, (int) $request->Current_Stage, null, null, null, [
                'Rejected' => $rejected,
                'Rejection_Quorum' => $quorum,
            ], true);

            return;
        }

        if ($mode === 'STOP') {
            $this->rejectRequest((int) $request->ID, 'STAGE_REJECTED');
            return;
        }

        $this->returnRequest((int) $request->ID, 'STAGE_RETURNED');
    }
    protected function returnRequest(int $requestId, string $reason): void
    {
        $request = $this->getRequest($requestId);
        if (!$request) {
            return;
        }

        DB::table('approval_request')->where('ID', $requestId)->update([
            'Status' => 'RETURNED',
            'Returned_At' => Carbon::now(),
            'Updated_At' => Carbon::now(),
        ]);

        $this->cancelRemainingTasks($requestId);
        $this->log($requestId, 0, 'RETURNED', 0, (int) $request->Current_Stage, null, null, null, [
            'Reason' => $reason,
        ], true);

        $this->notifications->notifyReturned($request, (int) $request->Requester_Id, $reason);

        $fresh = $this->getRequest($requestId);
        if ($fresh) {
            event(new \App\Events\Approval\ApprovalRequestUpdated($fresh, 'RETURNED', $reason));
        }
    }

    protected function rejectRequest(int $requestId, string $reason): void
    {
        $request = $this->getRequest($requestId);
        if (!$request) {
            return;
        }

        DB::table('approval_request')->where('ID', $requestId)->update([
            'Status' => 'REJECTED',
            'Completed_At' => Carbon::now(),
            'Updated_At' => Carbon::now(),
        ]);

        $this->cancelRemainingTasks($requestId);
        $this->log($requestId, 0, 'REJECTED', 0, (int) $request->Current_Stage, null, null, null, [
            'Reason' => $reason,
        ], true);

        $this->notifications->notifyRejected($request, (int) $request->Requester_Id, $reason);

        $fresh = $this->getRequest($requestId);
        if ($fresh) {
            event(new \App\Events\Approval\ApprovalRequestUpdated($fresh, 'REJECTED', $reason));
        }
    }

    /**
     * Force Approve resmi untuk transaksi Dynamic Engine (khusus akun yang diizinkan di helpers.php).
     */
    public function forceApprove(int $requestId, int $adminId, string $reason)
    {
        $admin = \App\User::find($adminId);
        if (!$admin || !$this->authorization->canForceApprove($admin)) {
            throw new ApprovalWorkflowException('Anda tidak memiliki akses untuk melakukan Force Approve.', 'FORCE_APPROVE_FORBIDDEN', 403);
        }

        $reason = trim($reason);
        if ($reason === '') {
            throw new ApprovalWorkflowException('Alasan Force Approve wajib diisi.', 'REASON_REQUIRED', 422);
        }

        return DB::transaction(function () use ($requestId, $adminId, $reason) {
            $request = $this->getRequest($requestId, true);
            if (!$request) {
                throw new ApprovalWorkflowException('Request tidak ditemukan.', 'REQUEST_NOT_FOUND', 404);
            }

            if (!in_array(strtoupper((string) $request->Status), ['IN_PROGRESS', 'BLOCKED', 'HELD', 'PENDING', 'WAITING_APPROVAL'], true)) {
                throw new ApprovalWorkflowException(
                    'Hanya request berstatus IN_PROGRESS, BLOCKED, HELD, atau PENDING yang dapat di-Force Approve.',
                    'FORCE_APPROVE_INVALID_STATUS',
                    422,
                    ['Status' => $request->Status]
                );
            }

            $now = Carbon::now();

            DB::table('approval_request_task')
                ->where('Request_ID', $requestId)
                ->whereIn('Status', ['PENDING', 'CLAIMED', 'HELD'])
                ->update([
                    'Status'     => 'AUTO_APPROVED',
                    'Acted_By'   => $adminId,
                    'Acted_At'   => $now,
                    'Action'     => 'FORCE_APPROVE',
                    'Notes'      => 'Force Approve: ' . $reason,
                    'Updated_At' => $now,
                ]);

            // Pastikan seluruh sisa stage pada Policy_Snapshot tetap tercatat di approval_request_task
            // dan approval_log sebagai FORCE_BYPASSED agar audit trail tetap utuh hingga VP/BOD.
            $existingStages = DB::table('approval_request_task')
                ->where('Request_ID', $requestId)
                ->where('Status', '!=', 'CANCELLED')
                ->pluck('Stage_Order')
                ->map(function ($s) {
                    return (int) $s;
                })
                ->all();
            $existingStageMap = array_flip($existingStages);

            $context = $this->decodeJson($request->Context, []);
            $steps = $this->stepsForRequest($request);
            usort($steps, function ($a, $b) {
                return ((int) ($a['Stage_Order'] ?? 0)) <=> ((int) ($b['Stage_Order'] ?? 0));
            });

            foreach ($steps as $step) {
                $stepStage = (int) ($step['Stage_Order'] ?? 0);
                if ($stepStage <= 0 || isset($existingStageMap[$stepStage])) {
                    continue;
                }
                $existingStageMap[$stepStage] = true;

                $resolvedPos = null;
                try {
                    $foundPositions = $this->candidateResolver->positionsForStep((object) $step, $context);
                    $resolvedPos = $foundPositions[0] ?? null;
                } catch (\Throwable $e) {
                    $resolvedPos = null;
                }

                $posId = $resolvedPos ? (int) $resolvedPos->ID : (int) ($step['Position_ID'] ?? 0);
                $posCode = $resolvedPos && !empty($resolvedPos->Initial)
                    ? (string) $resolvedPos->Initial
                    : (!empty($step['Position_Code']) ? (string) $step['Position_Code'] : null);
                $posName = !empty($step['Label'])
                    ? (string) $step['Label']
                    : ($resolvedPos && !empty($resolvedPos->Position) ? (string) $resolvedPos->Position : ($posCode ?: ('Approval Stage ' . $stepStage)));
                $dept = !empty($step['Department'])
                    ? (string) $step['Department']
                    : ($resolvedPos && !empty($resolvedPos->Department) ? (string) $resolvedPos->Department : ($request->Department ?: null));
                $bypassNote = 'Dilewati via Force Approve: ' . $reason;

                $bypassTaskId = DB::table('approval_request_task')->insertGetId([
                    'Request_ID'       => $requestId,
                    'Stage_Order'      => $stepStage,
                    'Level_Number'     => $resolvedPos ? (int) $resolvedPos->Level : (int) ($step['Level_Number'] ?? 0),
                    'Position_ID'      => $posId,
                    'Position_Code'    => $posCode,
                    'Position_Name'    => mb_substr($posName, 0, 150),
                    'Department'       => $dept,
                    'Approval_Mode'    => strtoupper((string) ($step['Approval_Mode'] ?? 'ALL')),
                    'Quorum'           => $step['Quorum'] ?? null,
                    'Status'           => 'AUTO_APPROVED',
                    'Acted_By'         => $adminId,
                    'Acted_At'         => $now,
                    'Action'           => 'FORCE_BYPASSED',
                    'Notes'            => $bypassNote,
                    'Is_Self_Approval' => 0,
                    'Snapshot'         => json_encode([
                        'Step'                     => $step,
                        'Position'                 => $resolvedPos ? (array) $resolvedPos : null,
                        'Bypassed_By_Force_Approve'=> true,
                    ]),
                    'Created_At'       => $now,
                    'Updated_At'       => $now,
                ]);

                $this->log($requestId, $adminId, 'STAGE_FORCE_BYPASSED', (int) ($step['Level_Number'] ?? 0), $stepStage, $posId > 0 ? $posId : null, $posCode, $bypassTaskId, [
                    'Bypass' => true,
                    'Reason' => $posName . ' — ' . $bypassNote,
                ], true);
            }

            $this->log($requestId, $adminId, 'FORCE_APPROVED', (int) $request->Current_Level, (int) $request->Current_Stage, null, null, null, [
                'Bypass' => true,
                'Reason' => $reason,
            ], true);

            $this->completeRequest($requestId, 'FORCE_APPROVED: ' . $reason);

            return $this->getRequest($requestId);
        });
    }

    /**
     * Bangun audit trail approval lengkap (menggabungkan Policy_Snapshot, approval_request_task, dan approval_log)
     * sehingga seluruh jenjang approval termasuk stage Force Approve, Auto-Skip, dan Upcoming tetap terbaca.
     */
    public function buildApprovalTrail(int $approvalRequestId): array
    {
        if ($approvalRequestId <= 0) {
            return [];
        }

        $request = $this->getRequest($approvalRequestId);
        if (!$request) {
            return [];
        }

        $reqStatus = strtoupper((string) $request->Status);
        $steps = $this->stepsForRequest($request);
        usort($steps, function ($a, $b) {
            return ((int) ($a['Stage_Order'] ?? 0)) <=> ((int) ($b['Stage_Order'] ?? 0));
        });

        $tasks = DB::table('approval_request_task as t')
            ->leftJoin('users as u', 'u.id', '=', 't.Acted_By')
            ->leftJoin('users as cu', 'cu.id', '=', 't.Claimed_By')
            ->where('t.Request_ID', $approvalRequestId)
            ->where('t.Status', '!=', 'CANCELLED')
            ->select(
                't.*',
                'u.Name as Actor_Name',
                'u.username as Actor_Username',
                'cu.Name as Claimer_Name',
                'cu.username as Claimer_Username'
            )
            ->orderBy('t.Stage_Order', 'ASC')
            ->orderBy('t.Level_Number', 'ASC')
            ->orderBy('t.ID', 'ASC')
            ->get();

        $taskIds = $tasks->pluck('ID')->map(function ($id) {
            return (int) $id;
        })->all();
        $candidatesByTask = [];
        if (!empty($taskIds)) {
            $candRows = DB::table('approval_task_candidate as c')
                ->join('users as u', 'u.id', '=', 'c.User_Id')
                ->whereIn('c.Task_ID', $taskIds)
                ->where('c.Status', 'ACTIVE')
                ->select('c.Task_ID', 'u.Name', 'u.username', 'c.Candidate_Type')
                ->orderBy('c.Candidate_Type', 'ASC')
                ->orderBy('u.Name', 'ASC')
                ->get();
            foreach ($candRows as $cr) {
                $tid = (int) $cr->Task_ID;
                $cName = trim((string) ($cr->Name ?: $cr->username));
                if ($cName !== '') {
                    $candidatesByTask[$tid][] = $cName;
                }
            }
        }

        $tasksByStage = [];
        foreach ($tasks as $t) {
            $tasksByStage[(int) $t->Stage_Order][] = $t;
        }

        $logs = DB::table('approval_log as l')
            ->leftJoin('users as u', 'u.id', '=', 'l.Approver_Id')
            ->where('l.Request_Id', $approvalRequestId)
            ->select('l.*', 'u.Name as User_Name', 'u.username as Username')
            ->orderBy('l.ID', 'DESC')
            ->get();

        $forceLog = null;
        $skipLogsByStage = [];
        foreach ($logs as $l) {
            $act = strtoupper((string) $l->Action);
            if ($act === 'FORCE_APPROVED' && !$forceLog) {
                $forceLog = $l;
            } elseif ($act === 'STAGE_AUTO_APPROVED' && !empty($l->Stage_Order) && !isset($skipLogsByStage[(int) $l->Stage_Order])) {
                $skipLogsByStage[(int) $l->Stage_Order] = $l;
            }
        }

        $statusLabels = [
            'APPROVED'       => 'Disetujui',
            'FORCE_APPROVED' => 'Force Approve',
            'SKIPPED_FORCE'  => 'Dilewati (Force Approve)',
            'SKIPPED'        => 'Dilewati Sistem',
            'AUTO_APPROVED'  => 'Auto Approved',
            'CLAIMED'        => 'Sedang Diproses',
            'PENDING'        => 'Menunggu Approval',
            'HELD'           => 'Ditahan / Vacant',
            'REJECTED'       => 'Ditolak',
            'UPCOMING'       => 'Menunggu Giliran',
            'NOT_REACHED'    => 'Tidak Tercapai',
        ];

        $trail = [];
        $coveredStages = [];

        $buildFromTask = function ($t, ?array $stepDef) use ($candidatesByTask, $statusLabels) {
            $rawSt = strtoupper((string) $t->Status);
            $act = strtoupper((string) ($t->Action ?? ''));
            $displaySt = $rawSt;
            if ($rawSt === 'AUTO_APPROVED') {
                if ($act === 'FORCE_APPROVE') {
                    $displaySt = 'FORCE_APPROVED';
                } elseif ($act === 'FORCE_BYPASSED') {
                    $displaySt = 'SKIPPED_FORCE';
                } elseif ($act === 'AUTO_SKIP') {
                    $displaySt = 'SKIPPED';
                }
            }

            $actor = trim((string) ($t->Actor_Name ?: $t->Actor_Username));
            if ($actor === '' && in_array($rawSt, ['CLAIMED'], true)) {
                $actor = trim((string) ($t->Claimer_Name ?: $t->Claimer_Username));
            }
            if ($actor === '' && in_array($rawSt, ['PENDING', 'HELD'], true)) {
                $candList = array_values(array_unique($candidatesByTask[(int) $t->ID] ?? []));
                if (!empty($candList)) {
                    $actor = 'Kandidat: ' . implode(', ', array_slice($candList, 0, 3)) . (count($candList) > 3 ? ' (+' . (count($candList) - 3) . ')' : '');
                }
            }
            if ($actor === '' && $displaySt === 'SKIPPED') {
                $actor = 'Sistem (Auto Skip)';
            } elseif ($actor === '' && $rawSt === 'AUTO_APPROVED') {
                $actor = 'Auto Approved (Sistem)';
            } elseif ($actor !== '' && in_array($displaySt, ['FORCE_APPROVED', 'SKIPPED_FORCE'], true)) {
                $actor .= ' (Force Approve)';
            }

            $label = !empty($stepDef['Label'])
                ? (string) $stepDef['Label']
                : ($t->Position_Name ?: ($t->Position_Code ?: ('Approval Stage ' . $t->Stage_Order)));
            $posCode = $t->Position_Code ?: ($stepDef['Position_Code'] ?? null);

            return (object) [
                'stage_order'   => (int) $t->Stage_Order,
                'level_number'  => (int) $t->Level_Number,
                'position_code' => $posCode,
                'label'         => $label,
                'status'        => $displaySt,
                'status_label'  => $statusLabels[$displaySt] ?? $displaySt,
                'raw_status'    => $rawSt,
                'action'        => $t->Action,
                'actor_name'    => $actor ?: '-',
                'acted_at'      => $t->Acted_At,
                'notes'         => $t->Notes,
            ];
        };

        foreach ($steps as $step) {
            $stageOrder = (int) ($step['Stage_Order'] ?? 0);
            if ($stageOrder <= 0) {
                continue;
            }
            $coveredStages[$stageOrder] = true;

            if (!empty($tasksByStage[$stageOrder])) {
                foreach ($tasksByStage[$stageOrder] as $t) {
                    $trail[] = $buildFromTask($t, $step);
                }
                continue;
            }

            $label = !empty($step['Label'])
                ? (string) $step['Label']
                : (!empty($step['Position_Code']) ? (string) $step['Position_Code'] : ('Approval Stage ' . $stageOrder));
            $posCode = !empty($step['Position_Code']) ? (string) $step['Position_Code'] : null;
            $levelNo = (int) ($step['Level_Number'] ?? 0);

            if (isset($skipLogsByStage[$stageOrder])) {
                $sl = $skipLogsByStage[$stageOrder];
                $trail[] = (object) [
                    'stage_order'   => $stageOrder,
                    'level_number'  => $levelNo,
                    'position_code' => $posCode ?: ($sl->Position_Code ?? null),
                    'label'         => $label,
                    'status'        => 'SKIPPED',
                    'status_label'  => $statusLabels['SKIPPED'],
                    'raw_status'    => 'AUTO_APPROVED',
                    'action'        => 'AUTO_SKIP',
                    'actor_name'    => 'Sistem (Auto Skip)',
                    'acted_at'      => $sl->Action_Date,
                    'notes'         => $sl->Notes ?: 'Posisi kosong / tidak ditemukan; dilewati otomatis oleh sistem.',
                ];
            } elseif ($reqStatus === 'APPROVED' && $forceLog) {
                $forceActor = trim((string) ($forceLog->User_Name ?: $forceLog->Username ?: ('User #' . $forceLog->Approver_Id)));
                $trail[] = (object) [
                    'stage_order'   => $stageOrder,
                    'level_number'  => $levelNo,
                    'position_code' => $posCode,
                    'label'         => $label,
                    'status'        => 'SKIPPED_FORCE',
                    'status_label'  => $statusLabels['SKIPPED_FORCE'],
                    'raw_status'    => 'AUTO_APPROVED',
                    'action'        => 'FORCE_BYPASSED',
                    'actor_name'    => $forceActor . ' (Force Approve)',
                    'acted_at'      => $forceLog->Action_Date,
                    'notes'         => 'Dilewati via Force Approve' . (!empty($forceLog->Notes) ? ': ' . $forceLog->Notes : ''),
                ];
            } elseif (in_array($reqStatus, ['REJECTED', 'RETURNED', 'CANCELLED'], true)) {
                $trail[] = (object) [
                    'stage_order'   => $stageOrder,
                    'level_number'  => $levelNo,
                    'position_code' => $posCode,
                    'label'         => $label,
                    'status'        => 'NOT_REACHED',
                    'status_label'  => $statusLabels['NOT_REACHED'],
                    'raw_status'    => 'NOT_REACHED',
                    'action'        => null,
                    'actor_name'    => '-',
                    'acted_at'      => null,
                    'notes'         => 'Pengajuan berhenti pada stage sebelumnya (' . $reqStatus . ').',
                ];
            } else {
                $trail[] = (object) [
                    'stage_order'   => $stageOrder,
                    'level_number'  => $levelNo,
                    'position_code' => $posCode,
                    'label'         => $label,
                    'status'        => 'UPCOMING',
                    'status_label'  => $statusLabels['UPCOMING'],
                    'raw_status'    => 'UPCOMING',
                    'action'        => null,
                    'actor_name'    => '-',
                    'acted_at'      => null,
                    'notes'         => 'Menunggu persetujuan tahap sebelumnya.',
                ];
            }
        }

        foreach ($tasksByStage as $stageOrder => $stageTasks) {
            if (isset($coveredStages[$stageOrder])) {
                continue;
            }
            foreach ($stageTasks as $t) {
                $trail[] = $buildFromTask($t, null);
            }
        }

        return $trail;
    }

    protected function completeRequest(int $requestId, string $reason): void
    {
        DB::table('approval_request')->where('ID', $requestId)->update([
            'Status' => 'APPROVED',
            'Completed_At' => Carbon::now(),
            'Updated_At' => Carbon::now(),
        ]);

        $this->log($requestId, 0, 'APPROVED', 0, null, null, null, null, [
            'Reason' => $reason,
        ], true);

        $fresh = $this->getRequest($requestId);
        if ($fresh) {
            $this->notifications->notifyApproved($fresh, (int) $fresh->Requester_Id, $reason);
            event(new \App\Events\Approval\ApprovalRequestUpdated($fresh, 'APPROVED', $reason));
        }
    }

    protected function cancelRemainingTasks(int $requestId, int $stageOrder = null): void
    {
        $query = DB::table('approval_request_task')
            ->where('Request_ID', $requestId)
            ->whereIn('Status', ['PENDING', 'CLAIMED', 'HELD']);

        if ($stageOrder !== null) {
            $query->where('Stage_Order', $stageOrder);
        }

        $query->update([
            'Status' => 'CANCELLED',
            'Updated_At' => Carbon::now(),
        ]);
    }

    protected function autoApproveTask($task, $request, string $reason): void
    {
        DB::table('approval_request_task')->where('ID', $task->ID)->update([
            'Status' => 'AUTO_APPROVED',
            'Acted_By' => 0,
            'Acted_At' => Carbon::now(),
            'Action' => 'AUTO_APPROVE',
            'Notes' => $reason,
            'Updated_At' => Carbon::now(),
        ]);

        $this->log((int) $task->Request_ID, 0, 'AUTO_APPROVED', (int) $task->Level_Number, (int) $task->Stage_Order, (int) $task->Position_ID, $task->Position_Code, (int) $task->ID, [
            'Reason' => $reason,
        ], true);

        $this->notifications->notifyAutoApproved($task, $request, $reason);
    }

    protected function holdTask($task, $request, string $reason): void
    {
        DB::table('approval_request_task')->where('ID', $task->ID)->update([
            'Status' => 'HELD',
            'Held_At' => Carbon::now(),
            'Held_Reason' => $reason,
            'Updated_At' => Carbon::now(),
        ]);

        $this->log((int) $task->Request_ID, 0, 'HELD', (int) $task->Level_Number, (int) $task->Stage_Order, (int) $task->Position_ID, $task->Position_Code, (int) $task->ID, [
            'Reason' => $reason,
        ], true);

        $this->notifications->notifyHeld($task, $request, $reason);
    }

    protected function assertSelfApprovalAllowed($request, $task, int $userId): void
    {
        if ((int) $request->Requester_Id !== $userId) {
            return;
        }

        $exception = DB::table('approval_self_exception')
            ->where('Request_ID', (int) $request->ID)
            ->where('User_Id', $userId)
            ->whereNull('Revoked_At')
            ->where(function ($query) use ($task) {
                $query->whereNull('Task_ID')->orWhere('Task_ID', (int) $task->ID);
            })
            ->where(function ($query) {
                $query->whereNull('Expires_At')->orWhere('Expires_At', '>', Carbon::now());
            })
            ->first();

        if (!$exception) {
            throw new ApprovalWorkflowException(
                'Self-approval dilarang. Diperlukan exception dari wildcard admin.',
                'SELF_APPROVAL_FORBIDDEN',
                403
            );
        }

        $this->log((int) $request->ID, $userId, 'SELF_APPROVAL_EXCEPTION_USED', (int) $task->Level_Number, (int) $task->Stage_Order, (int) $task->Position_ID, $task->Position_Code, (int) $task->ID, [
            'Exception_ID' => (int) $exception->ID,
        ]);
    }

    public function getRequest(int $requestId, bool $lock = false)
    {
        $query = DB::table('approval_request')->where('ID', $requestId);
        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    public function getTasks(int $requestId, int $stageOrder = null): array
    {
        $query = DB::table('approval_request_task')->where('Request_ID', $requestId);
        if ($stageOrder !== null) {
            $query->where('Stage_Order', $stageOrder);
        }

        return $query->orderBy('Stage_Order', 'ASC')->orderBy('ID', 'ASC')->get()->all();
    }

    public function policyForRequest($request)
    {
        // The snapshot is authoritative for an in-flight request. A policy
        // edited after submission must not change the current revision.
        $snapshot = $this->decodeJson($request->Policy_Snapshot, []);
        if (!empty($snapshot['Policy'])) {
            return (object) $snapshot['Policy'];
        }

        if (!empty($request->Policy_ID)) {
            $policy = $this->policyResolver->policyById((int) $request->Policy_ID);
            if ($policy) {
                return $policy;
            }
        }

        throw new ApprovalWorkflowException('Policy request tidak ditemukan.', 'POLICY_NOT_FOUND', 404);
    }

    public function stepsForRequest($request): array
    {
        $snapshot = $this->decodeJson($request->Policy_Snapshot, []);
        $steps = $snapshot['Steps'] ?? [];

        if (!empty($steps)) {
            return array_values($steps);
        }

        return array_map(function ($step) {
            return (array) $step;
        }, $this->policyResolver->steps((int) $request->Policy_ID));
    }

    protected function stepsAsObjects(array $steps): array
    {
        return array_map(function ($step) {
            return (object) $step;
        }, $steps);
    }

    protected function hasCandidate(array $candidates, int $userId): bool
    {
        foreach ($candidates as $candidate) {
            if ((int) $candidate['User_Id'] === $userId) {
                return true;
            }
        }

        return false;
    }

    protected function decodeJson($value, $default = [])
    {
        if (is_array($value)) {
            return $value;
        }
        if (empty($value)) {
            return $default;
        }

        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? $decoded : $default;
    }

    protected function log(
        int $requestId,
        int $approverId,
        string $action,
        int $levelNumber = 0,
        int $stageOrder = null,
        int $positionId = null,
        string $positionCode = null,
        int $taskId = null,
        array $metadata = [],
        bool $isSystem = false,
        int $onBehalfOf = null
    ): void {
        try {
            DB::table('approval_log')->insert([
                'Request_Id' => $requestId,
                'Approver_Id' => $approverId,
                'Action' => $action,
                'Notes' => $metadata['Reason'] ?? ($metadata['Notes'] ?? null),
                'Level_Number' => $levelNumber,
                'Action_Date' => Carbon::now(),
                'Task_ID' => $taskId,
                'Stage_Order' => $stageOrder,
                'Position_ID' => $positionId,
                'Position_Code' => $positionCode,
                'Metadata' => !empty($metadata) ? json_encode($metadata) : null,
                'Is_System' => $isSystem ? 1 : 0,
                'On_Behalf_Of' => $onBehalfOf,
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Gagal menulis approval_log', [
                'message' => $exception->getMessage(),
                'request_id' => $requestId,
                'action' => $action,
            ]);
        }
    }
}