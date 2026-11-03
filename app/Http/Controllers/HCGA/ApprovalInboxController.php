<?php

namespace App\Http\Controllers\HCGA;

use App\Exceptions\ApprovalWorkflowException;
use App\Http\Controllers\Controller;
use App\Services\Approval\ApprovalAuthorizationService;
use App\Services\Approval\ApprovalWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ApprovalInboxController extends Controller
{
    protected $workflow;
    protected $authorization;

    public function __construct(ApprovalWorkflowService $workflow, ApprovalAuthorizationService $authorization)
    {
        $this->workflow = $workflow;
        $this->authorization = $authorization;
    }

    public function index()
    {
        $isAdmin = $this->authorization->isWildcardAdmin(Auth::user());

        return view('humanresources.approval.index', compact('isAdmin'));
    }

    public function data()
    {
        if (!headers_sent()) {
            header('Content-type: text/xml; charset=UTF-8');
        }

        $userId = (int) Auth::id();
        $isAdmin = $this->authorization->isWildcardAdmin(Auth::user());

        $query = DB::table('approval_request_task as t')
            ->join('approval_request as r', 'r.ID', '=', 't.Request_ID')
            ->join('users as u', 'u.id', '=', 'r.Requester_Id')
            ->select(
                't.ID',
                't.Request_ID',
                't.Stage_Order',
                't.Level_Number',
                't.Position_Code',
                't.Position_Name',
                't.Approval_Mode',
                't.Quorum',
                't.Status as Task_Status',
                't.Claimed_By',
                't.Claimed_At',
                't.Held_Reason',
                'r.Request_No',
                'r.Request_Type',
                'r.Status as Request_Status',
                'r.Submitted_At',
                'r.Location',
                'r.Company_ID',
                'u.Name as Requester_Name'
            )
            ->whereNotIn('r.Status', ['APPROVED', 'REJECTED', 'RETURNED', 'CANCELLED']);

        if ($isAdmin) {
            // Wildcard admin needs visibility of claimed tasks to release
            // claims manually. Normal approvers only see their own work.
            $query->whereIn('t.Status', ['PENDING', 'HELD', 'CLAIMED']);
        } else {
            $query->where(function ($subQuery) use ($userId) {
                $subQuery->whereIn('t.Status', ['PENDING', 'HELD'])
                    ->orWhere(function ($sub) use ($userId) {
                        $sub->where('t.Status', 'CLAIMED')->where('t.Claimed_By', $userId);
                    });
            })->whereExists(function ($candidateQuery) use ($userId) {
                $candidateQuery->select(DB::raw(1))
                    ->from('approval_task_candidate as c')
                    ->whereColumn('c.Task_ID', 't.ID')
                    ->where('c.User_Id', $userId)
                    ->where('c.Status', 'ACTIVE');
            });
        }

        $tasks = $query->orderBy('r.Submitted_At', 'ASC')
            ->orderBy('t.Stage_Order', 'ASC')
            ->orderBy('t.ID', 'ASC')
            ->get();

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<rows>';

        $no = 1;
        foreach ($tasks as $task) {
            $taskStatus = strtoupper((string) $task->Task_Status);
            if ($taskStatus === 'CLAIMED') {
                $taskStatus = 'CLAIMED BY YOU';
            }

            echo '<row id="' . (int) $task->ID . '">';
            echo '<cell>' . $no . '</cell>';
            echo '<cell>' . $this->xml($task->Request_No) . '</cell>';
            echo '<cell>' . $this->xml($task->Request_Type) . '</cell>';
            echo '<cell>' . $this->xml($task->Position_Code . ' - ' . $task->Position_Name) . '</cell>';
            echo '<cell>' . (int) $task->Stage_Order . '</cell>';
            echo '<cell>' . (int) $task->Level_Number . '</cell>';
            echo '<cell>' . $this->xml($task->Approval_Mode . ($task->Quorum ? ' (' . $task->Quorum . ')' : '')) . '</cell>';
            echo '<cell>' . $this->xml($taskStatus) . '</cell>';
            echo '<cell>' . $this->xml($task->Requester_Name) . '</cell>';
            echo '<cell>' . $this->xml($task->Location) . '</cell>';
            echo '<cell>' . $this->xml($task->Submitted_At) . '</cell>';
            echo '</row>';
            $no++;
        }

        echo '</rows>';
    }

    public function detail(Request $request)
    {
        $taskId = (int) $request->input('ID');
        $task = $this->taskQuery()->where('t.ID', $taskId)->first();

        if (!$task) {
            abort(404);
        }

        $this->assertCanViewTask($task);

        $candidates = DB::table('approval_task_candidate as c')
            ->join('users as u', 'u.id', '=', 'c.User_Id')
            ->select('c.*', 'u.Name as User_Name', 'u.username', 'u.Position', 'u.Department')
            ->where('c.Task_ID', $taskId)
            ->orderBy('c.Candidate_Type', 'ASC')
            ->get();

        $logs = DB::table('approval_log as l')
            ->leftJoin('users as u', 'u.id', '=', 'l.Approver_Id')
            ->select('l.*', 'u.Name as Approver_Name')
            ->where('l.Request_Id', (int) $task->Request_ID)
            ->orderBy('l.Action_Date', 'ASC')
            ->orderBy('l.ID', 'ASC')
            ->get();

        $userId = (int) Auth::id();
        $isAdmin = $this->authorization->isWildcardAdmin(Auth::user());
        $isMyClaim = ((int) $task->Claimed_By === $userId);
        $isPending = (strtoupper((string) $task->Task_Status) === 'PENDING');
        $isCandidate = DB::table('approval_task_candidate')
            ->where('Task_ID', $taskId)
            ->where('User_Id', $userId)
            ->where('Status', 'ACTIVE')
            ->exists();

        $canAct = ($isMyClaim || ($isPending && $isCandidate) || $isAdmin);
        $canClaim = ($isPending && ($isCandidate || $isAdmin));
        $canRelease = ($isAdmin && strtoupper((string) $task->Task_Status) === 'CLAIMED');

        return view('humanresources.approval.detail', compact(
            'task',
            'candidates',
            'logs',
            'isAdmin',
            'canAct',
            'canClaim',
            'canRelease',
            'isMyClaim'
        ));
    }

    public function claim(Request $request)
    {
        $taskId = (int) $request->input('Task_ID');
        if ($taskId <= 0) {
            return response()->json(['status' => 'error', 'message' => 'Task ID tidak valid.'], 422);
        }

        return $this->jsonWorkflow(function () use ($taskId) {
            $task = $this->workflow->claim($taskId, (int) Auth::id());

            return ['message' => 'Task berhasil di-claim.', 'task' => $task];
        });
    }

    public function act(Request $request)
    {
        $action = strtoupper(trim((string) $request->input('Action')));
        $notes = trim((string) $request->input('Notes'));
        $taskId = (int) $request->input('Task_ID');

        if ($taskId <= 0) {
            return response()->json(['status' => 'error', 'message' => 'Pilih task approval terlebih dahulu.'], 422);
        }

        return $this->jsonWorkflow(function () use ($taskId, $action, $notes) {
            $userId = (int) Auth::id();

            $task = DB::table('approval_request_task')->where('ID', $taskId)->first();
            if (!$task) {
                throw new ApprovalWorkflowException('Task approval tidak ditemukan.', 'TASK_NOT_FOUND', 404);
            }

            // Jika status masih PENDING, otomatis claim terlebih dahulu agar user tidak terblokir
            if (strtoupper((string) $task->Status) === 'PENDING') {
                $this->workflow->claim($taskId, $userId);
            }

            $task = $this->workflow->act($taskId, $userId, $action, $notes);

            $message = $action === 'APPROVE'
                ? 'Permohonan berhasil disetujui (Approved).'
                : 'Permohonan berhasil ditolak (Rejected).';

            return ['message' => $message, 'task' => $task];
        });
    }

    public function release(Request $request)
    {
        $taskId = (int) $request->input('Task_ID');
        $reason = trim((string) $request->input('Reason'));

        if ($taskId <= 0) {
            return response()->json(['status' => 'error', 'message' => 'Pilih task approval terlebih dahulu.'], 422);
        }

        return $this->jsonWorkflow(function () use ($taskId, $reason) {
            $task = $this->workflow->release($taskId, (int) Auth::id(), $reason);

            return ['message' => 'Claim berhasil di-release.', 'task' => $task];
        });
    }

    public function grantSelfException(Request $request)
    {
        $reason = trim((string) $request->input('Reason'));
        $wantsJson = $request->expectsJson() || $request->ajax();

        try {
            $exceptionId = $this->workflow->grantSelfApprovalException(
                (int) $request->input('Request_ID'),
                (int) ($request->input('User_Id') ?: Auth::id()),
                (int) Auth::id(),
                $reason,
                $request->input('Expires_At') ?: null
            );

            if ($wantsJson) {
                return response()->json([
                    'status' => 'ok',
                    'message' => 'Exception self-approval diberikan.',
                    'exception_id' => $exceptionId,
                ]);
            }

            return redirect('hrd-approval-inbox')->with('success', 'Exception self-approval diberikan.');
        } catch (ApprovalWorkflowException $exception) {
            if ($wantsJson) {
                return response()->json([
                    'status' => 'error',
                    'message' => $exception->getMessage(),
                    'code' => $exception->getErrorCode(),
                ], $exception->getStatusCode());
            }

            return redirect('hrd-approval-inbox')->with('error', $exception->getMessage());
        } catch (\Illuminate\Auth\Access\AuthorizationException $exception) {
            if ($wantsJson) {
                return response()->json([
                    'status' => 'error',
                    'message' => $exception->getMessage() ?: 'Anda tidak berwenang.',
                    'code' => 'FORBIDDEN',
                ], 403);
            }

            return redirect('hrd-approval-inbox')->with('error', $exception->getMessage() ?: 'Anda tidak berwenang.');
        }
    }

    public function addCandidate(Request $request)
    {
        $reason = trim((string) $request->input('Reason'));
        $wantsJson = $request->expectsJson() || $request->ajax();

        try {
            $this->workflow->addCandidate(
                (int) $request->input('Task_ID'),
                (int) $request->input('User_Id'),
                (int) Auth::id(),
                $reason
            );

            if ($wantsJson) {
                return response()->json(['status' => 'ok', 'message' => 'Kandidat approval berhasil ditambahkan.']);
            }

            return redirect('hrd-approval-inbox')->with('success', 'Kandidat approval berhasil ditambahkan.');
        } catch (ApprovalWorkflowException $exception) {
            if ($wantsJson) {
                return response()->json([
                    'status' => 'error',
                    'message' => $exception->getMessage(),
                    'code' => $exception->getErrorCode(),
                ], $exception->getStatusCode());
            }

            return redirect('hrd-approval-inbox')->with('error', $exception->getMessage());
        } catch (\Illuminate\Auth\Access\AuthorizationException $exception) {
            if ($wantsJson) {
                return response()->json([
                    'status' => 'error',
                    'message' => $exception->getMessage() ?: 'Anda tidak berwenang.',
                    'code' => 'FORBIDDEN',
                ], 403);
            }

            return redirect('hrd-approval-inbox')->with('error', $exception->getMessage() ?: 'Anda tidak berwenang.');
        }
    }

    public function escalation()
    {
        if (!Gate::allows('approval.policy.manage')) {
            abort(403, 'Hanya wildcard admin yang dapat membuka escalation queue.');
        }

        return view('humanresources.approval.escalation');
    }

    public function escalationData()
    {
        if (!Gate::allows('approval.policy.manage')) {
            abort(403);
        }

        if (!headers_sent()) {
            header('Content-type: text/xml; charset=UTF-8');
        }

        $held = DB::table('approval_request_task as t')
            ->join('approval_request as r', 'r.ID', '=', 't.Request_ID')
            ->join('users as u', 'u.id', '=', 'r.Requester_Id')
            ->select(
                't.ID',
                't.Request_ID',
                't.Stage_Order',
                't.Level_Number',
                't.Position_Code',
                't.Position_Name',
                't.Held_Reason',
                't.Held_At',
                'r.Request_No',
                'r.Request_Type',
                'u.Name as Requester_Name'
            )
            ->where('t.Status', 'HELD')
            ->whereNotIn('r.Status', ['APPROVED', 'REJECTED', 'RETURNED', 'CANCELLED'])
            ->orderBy('t.Held_At', 'ASC')
            ->get();

        $blocked = DB::table('approval_request as r')
            ->join('users as u', 'u.id', '=', 'r.Requester_Id')
            ->select(
                'r.ID as Request_ID',
                'r.Request_No',
                'r.Request_Type',
                'r.Current_Stage',
                'r.Current_Level',
                'r.Updated_At',
                'u.Name as Requester_Name'
            )
            ->where('r.Status', 'BLOCKED')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('approval_request_task as t')
                    ->whereColumn('t.Request_ID', 'r.ID')
                    ->where('t.Status', 'HELD');
            })
            ->orderBy('r.Updated_At', 'ASC')
            ->get();

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<rows>';

        $no = 1;
        foreach ($held as $task) {
            echo '<row id="task-' . (int) $task->ID . '">';
            echo '<cell>' . $no . '</cell>';
            echo '<cell>HELD TASK</cell>';
            echo '<cell>' . $this->xml($task->Request_No) . '</cell>';
            echo '<cell>' . $this->xml($task->Request_Type) . '</cell>';
            echo '<cell>' . $this->xml($task->Position_Code . ' - ' . $task->Position_Name . ' / Stage ' . $task->Stage_Order) . '</cell>';
            echo '<cell>' . $this->xml($task->Held_Reason) . '</cell>';
            echo '<cell>' . $this->xml($task->Requester_Name) . '</cell>';
            echo '<cell>' . $this->xml($task->Held_At) . '</cell>';
            echo '</row>';
            $no++;
        }

        foreach ($blocked as $request) {
            $reason = DB::table('approval_log')
                ->where('Request_Id', (int) $request->Request_ID)
                ->whereIn('Action', ['STAGE_HELD', 'WAIT_GROUP_BLOCKED', 'HELD'])
                ->orderBy('Action_Date', 'DESC')
                ->value('Notes');

            echo '<row id="request-' . (int) $request->Request_ID . '">';
            echo '<cell>' . $no . '</cell>';
            echo '<cell>BLOCKED REQUEST</cell>';
            echo '<cell>' . $this->xml($request->Request_No) . '</cell>';
            echo '<cell>' . $this->xml($request->Request_Type) . '</cell>';
            echo '<cell>' . $this->xml('Stage ' . $request->Current_Stage . ' / Level ' . $request->Current_Level) . '</cell>';
            echo '<cell>' . $this->xml($reason ?: 'No recipient available') . '</cell>';
            echo '<cell>' . $this->xml($request->Requester_Name) . '</cell>';
            echo '<cell>' . $this->xml($request->Updated_At) . '</cell>';
            echo '</row>';
            $no++;
        }

        echo '</rows>';
    }

    private function taskQuery()
    {
        return DB::table('approval_request_task as t')
            ->join('approval_request as r', 'r.ID', '=', 't.Request_ID')
            ->join('users as u', 'u.id', '=', 'r.Requester_Id')
            ->select(
                't.*',
                't.Status as Task_Status',
                'r.Request_No',
                'r.Request_Type',
                'r.Status as Request_Status',
                'r.Description',
                'r.Submitted_At',
                'r.Location',
                'r.Company_ID',
                'r.Department',
                'r.Amount',
                'r.Requester_Id',
                'r.Policy_ID',
                'r.Revision_No',
                'r.Parent_Request_ID',
                'u.Name as Requester_Name'
            );
    }

    private function assertCanViewTask($task): void
    {
        $userId = (int) Auth::id();

        if ((int) $task->Requester_Id === $userId) {
            return;
        }

        if ($this->authorization->isWildcardAdmin(Auth::user())) {
            return;
        }

        $isCandidate = DB::table('approval_task_candidate')
            ->where('Task_ID', (int) $task->ID)
            ->where('User_Id', $userId)
            ->exists();

        if (!$isCandidate) {
            abort(403, 'Anda tidak memiliki akses ke task approval ini.');
        }
    }

    private function jsonWorkflow(callable $callback)
    {
        try {
            return response()->json(array_merge(['status' => 'ok'], $callback()));
        } catch (ApprovalWorkflowException $exception) {
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage(),
                'code' => $exception->getErrorCode(),
                'context' => $exception->getContext(),
            ], $exception->getStatusCode());
        } catch (\Illuminate\Auth\Access\AuthorizationException $exception) {
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage() ?: 'Anda tidak berwenang.',
                'code' => 'FORBIDDEN',
            ], 403);
        }
    }

    private function xml($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}