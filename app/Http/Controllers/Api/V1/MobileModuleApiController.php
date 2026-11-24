<?php

namespace App\Http\Controllers\Api\V1;

use App\Attendance;
use App\Http\Controllers\Controller;
use App\Services\Approval\ApprovalAuthorizationService;
use App\Services\Approval\ApprovalWorkflowService;
use App\Services\Attendance\AttendanceService;
use App\Services\Employee\EmployeeMobileService;
use App\Services\HCGA\AnnouncementService;
use App\Services\HCGA\CompanyGuidelineService;
use App\Services\HCGA\ReimbursementMobileService;
use App\Services\HCGA\SppdMobileService;
use App\Services\HCGA\StorageManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MobileModuleApiController extends Controller
{
    protected $attendanceService;
    protected $approvalWorkflow;
    protected $approvalAuth;
    protected $sppdService;
    protected $reimbursementService;
    protected $employeeService;
    protected $announcementService;
    protected $guidelineService;
    protected $storageService;

    public function __construct(
        AttendanceService $attendanceService,
        ApprovalWorkflowService $approvalWorkflow,
        ApprovalAuthorizationService $approvalAuth,
        SppdMobileService $sppdService,
        ReimbursementMobileService $reimbursementService,
        EmployeeMobileService $employeeService,
        AnnouncementService $announcementService,
        CompanyGuidelineService $guidelineService,
        StorageManagementService $storageService
    ) {
        $this->attendanceService = $attendanceService;
        $this->approvalWorkflow = $approvalWorkflow;
        $this->approvalAuth = $approvalAuth;
        $this->sppdService = $sppdService;
        $this->reimbursementService = $reimbursementService;
        $this->employeeService = $employeeService;
        $this->announcementService = $announcementService;
        $this->guidelineService = $guidelineService;
        $this->storageService = $storageService;
    }

    // =========================================================
    // 1. ATTENDANCE & TIMESHEET
    // =========================================================

    public function attendanceSummary(Request $request)
    {
        $user = $request->user();
        $today = date('Y-m-d');
        $empId = (string) ($user->Emp_ID ?? '');

        $todayEntry = $empId !== ''
            ? Attendance::where('NIK', $empId)->where('Scan_Date', $today)->first()
            : null;

        $schedule = $empId !== ''
            ? $this->attendanceService->getWorkScheduleForDate($empId, $today)
            : null;

        $locations = DB::table('attendance_locations')->where('status', 'ACTIVE')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'date' => $today,
                'today_entry' => $todayEntry,
                'schedule' => $schedule,
                'locations' => $locations,
            ],
        ]);
    }

    public function attendanceClockIn(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'notes' => 'nullable|string|max:255',
            'photo' => 'nullable|string',
        ]);

        $result = $this->attendanceService->clockIn($request->user(), $validated);

        return response()->json($result, ($result['success'] ?? false) ? 200 : 422);
    }

    public function attendanceClockOut(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'notes' => 'nullable|string|max:255',
            'photo' => 'nullable|string',
        ]);

        $result = $this->attendanceService->clockOut($request->user(), $validated);

        return response()->json($result, ($result['success'] ?? false) ? 200 : 422);
    }

    public function attendanceLeave(Request $request)
    {
        $validated = $request->validate([
            'leave_type' => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
        ]);

        $result = $this->attendanceService->submitLeaveRequest($request->user(), $validated, $request->file('attachment'));

        return response()->json($result, ($result['success'] ?? false) ? 200 : 422);
    }

    public function attendanceCorrection(Request $request)
    {
        $validated = $request->validate([
            'scan_date' => 'required|date',
            'clock_in' => 'nullable|string|max:10',
            'clock_out' => 'nullable|string|max:10',
            'reason' => 'required|string|max:500',
        ]);

        $result = $this->attendanceService->submitCorrectionRequest($request->user(), $validated, $request->file('attachment'));

        return response()->json($result, ($result['success'] ?? false) ? 200 : 422);
    }

    // =========================================================
    // 2. APPROVAL INBOX
    // =========================================================

    public function approvalInbox(Request $request)
    {
        $user = $request->user();
        $isAdmin = $this->approvalAuth->isWildcardAdmin($user);
        $category = strtoupper(trim((string) $request->input('category', 'ALL')));

        $query = DB::table('approval_request_task as t')
            ->join('approval_request as r', 'r.ID', '=', 't.Request_ID')
            ->leftJoin('users as u', 'u.id', '=', 'r.Requester_ID')
            ->leftJoin('approval_policy as p', 'p.ID', '=', 'r.Policy_ID')
            ->whereIn('t.Status', ['PENDING', 'CLAIMED']);

        if (!$isAdmin) {
            $query->whereExists(function ($sub) use ($user) {
                $sub->select(DB::raw(1))
                    ->from('approval_task_candidate as tc')
                    ->whereColumn('tc.Task_ID', 't.ID')
                    ->where('tc.User_ID', (int) $user->id);
            });
        }

        if ($category !== '' && $category !== 'ALL') {
            $query->where('r.Request_Type', $category);
        }

        $tasks = $query->select(
            't.ID as task_id',
            't.Request_ID as request_id',
            't.Stage_Order',
            't.Position_Name',
            't.Status as task_status',
            'r.Request_No',
            'r.Request_Type',
            'r.Description',
            'r.Amount',
            'r.Status as request_status',
            'r.Submitted_At',
            'p.Policy_Name',
            'u.Name as requester_name'
        )
        ->orderByDesc('r.Submitted_At')
        ->limit(50)
        ->get();

        return response()->json([
            'success' => true,
            'data' => $tasks,
        ]);
    }

    public function approvalAct(Request $request, int $taskId)
    {
        $validated = $request->validate([
            'action' => 'required|in:APPROVE,REJECT,approve,reject',
            'notes' => 'nullable|string|max:500',
        ]);

        $action = strtoupper($validated['action']);
        $notes = (string) ($validated['notes'] ?? '');

        $result = $this->approvalWorkflow->act($taskId, (int) $request->user()->id, $action, $notes);

        return response()->json([
            'success' => true,
            'message' => "Task #{$taskId} berhasil di-{$action}.",
            'data' => $result,
        ]);
    }

    // =========================================================
    // 3. SPPD
    // =========================================================

    public function sppdList(Request $request)
    {
        $status = $request->input('status');
        $limit = min((int) $request->input('limit', 20), 50);
        $list = $this->sppdService->getUserSppdList($request->user()->id, $status, $limit);

        return response()->json(['success' => true, 'data' => $list]);
    }

    public function sppdDetail(Request $request, $id)
    {
        $detail = $this->sppdService->getDetail($id, $request->user());
        if (!$detail) {
            return response()->json(['success' => false, 'message' => 'Data SPPD tidak ditemukan.'], 404);
        }

        return response()->json(['success' => true, 'data' => $detail]);
    }

    public function sppdApprovalQueue(Request $request)
    {
        $limit = min((int) $request->input('limit', 20), 50);
        $queue = $this->sppdService->getApproverQueue($request->user(), $limit);

        return response()->json(['success' => true, 'data' => $queue]);
    }

    public function sppdSubmit(Request $request)
    {
        $validated = $request->validate([
            'destination' => 'required|string|max:255',
            'purpose' => 'required|string|max:500',
            'departure_date' => 'required|date',
            'return_date' => 'required|date|after_or_equal:departure_date',
        ]);

        $result = $this->sppdService->submitNewSppd(array_merge($request->all(), $validated), $request->user());

        return response()->json([
            'success' => (bool) ($result['success'] ?? true),
            'message' => $result['message'] ?? 'SPPD berhasil diajukan.',
            'data' => $result,
        ], ($result['success'] ?? true) ? 200 : 422);
    }

    public function sppdProcessApproval(Request $request)
    {
        $validated = $request->validate([
            'sppd_id' => 'required|integer',
            'action' => 'required|in:APPROVE,REJECT,approve,reject',
            'note' => 'nullable|string|max:500',
        ]);

        $result = $this->sppdService->processApproval(
            $validated['sppd_id'],
            strtoupper($validated['action']),
            (string) ($validated['note'] ?? ''),
            $request->user()
        );

        return response()->json([
            'success' => (bool) ($result['success'] ?? true),
            'message' => $result['message'] ?? 'Approval SPPD diproses.',
            'data' => $result,
        ]);
    }

    // =========================================================
    // 4. REIMBURSEMENT
    // =========================================================

    public function reimbursementList(Request $request)
    {
        $status = strtoupper((string) $request->input('status', 'ALL'));
        $limit = min((int) $request->input('limit', 30), 50);
        $list = $this->reimbursementService->getUserReimbursementList((int) $request->user()->id, $status, $limit);

        return response()->json(['success' => true, 'data' => $list]);
    }

    public function reimbursementDetail(Request $request, $id)
    {
        $detail = $this->reimbursementService->getDetail($id, $request->user());
        if (!$detail) {
            return response()->json(['success' => false, 'message' => 'Data reimbursement tidak ditemukan.'], 404);
        }

        return response()->json(['success' => true, 'data' => $detail]);
    }

    public function reimbursementApprovalQueue(Request $request)
    {
        $limit = min((int) $request->input('limit', 30), 50);
        $queue = $this->reimbursementService->getApproverQueue($request->user(), $limit);

        return response()->json(['success' => true, 'data' => $queue]);
    }

    // =========================================================
    // 5. ESS (EMPLOYEE PROFILE & DIRECTORY)
    // =========================================================

    public function essProfile(Request $request)
    {
        $employee = $this->employeeService->getMyEmployeeData($request->user());
        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Data profil karyawan tidak ditemukan.'], 404);
        }

        return response()->json(['success' => true, 'data' => $employee]);
    }

    public function essDirectory(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 15), 50);
        $directory = $this->employeeService->getEmployeeDirectory($request->only(['q', 'department', 'location']), $perPage);

        return response()->json(['success' => true, 'data' => $directory]);
    }

    public function essSubmitUpdate(Request $request)
    {
        $validated = $request->validate([
            'task_id' => 'required',
            'field' => 'nullable|string|max:100',
        ]);

        $result = $this->employeeService->submitUpdateRequest($validated['task_id'], $request->all(), $request->user());

        return response()->json([
            'success' => (bool) ($result['success'] ?? true),
            'message' => $result['message'] ?? 'Permintaan pembaruan data berhasil dikirim.',
            'data' => $result,
        ]);
    }

    // =========================================================
    // 6. ANNOUNCEMENTS
    // =========================================================

    public function announcements(Request $request)
    {
        $feed = $this->announcementService->getMobileFeed($request, $request->user());

        return response()->json(['success' => true, 'data' => $feed]);
    }

    public function announcementDetail(Request $request, $id)
    {
        $user = $request->user();
        $isHcga = $this->announcementService->isHcgaOrAdmin($user);
        $detail = $this->announcementService->getDetail($id, true);
        if (!$detail) {
            return response()->json(['success' => false, 'message' => 'Pengumuman tidak ditemukan.'], 404);
        }

        if (!$detail->isAccessibleBy($isHcga, $user ? ($user->Department ?? null) : null)) {
            return response()->json(['success' => false, 'message' => 'Dokumen belum berlaku, kedaluwarsa, atau bukan untuk departemen Anda.'], 403);
        }

        if ($detail->storage_version_id && $user) {
            $this->storageService->recordReadReceipt(
                (int) $detail->storage_version_id,
                $user,
                false,
                'MOBILE_API',
                $request->ip(),
                (int) $detail->id
            );
        }

        $payload = $this->announcementService->getMobileDetailPayload($detail, $user);

        return response()->json([
            'success' => true,
            'data'    => $detail,
            'storage' => [
                'is_from_storage'  => $detail->isFromStorage(),
                'is_acknowledged'  => $payload['is_acknowledged'],
                'acknowledged_at'  => $payload['user_receipt'] ? $payload['user_receipt']->acknowledged_at : null,
                'visible_versions' => $payload['visible_versions'],
            ],
        ]);
    }

    public function announcementAcknowledge(Request $request, $id)
    {
        $user = $request->user();
        $isHcga = $this->announcementService->isHcgaOrAdmin($user);
        $detail = $this->announcementService->getDetail($id, false);
        if (!$detail) {
            return response()->json(['success' => false, 'message' => 'Pengumuman tidak ditemukan.'], 404);
        }

        if (!$detail->isAccessibleBy($isHcga, $user ? ($user->Department ?? null) : null)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        if (!$detail->storage_version_id) {
            return response()->json(['success' => false, 'message' => 'Pengumuman ini tidak terhubung ke Storage Management.'], 422);
        }

        $receipt = $this->storageService->recordReadReceipt(
            (int) $detail->storage_version_id,
            $user,
            true,
            'MOBILE_API',
            $request->ip(),
            (int) $detail->id
        );

        return response()->json([
            'success'         => true,
            'message'         => 'Konfirmasi baca berhasil dicatat.',
            'acknowledged_at' => $receipt ? $receipt->acknowledged_at : null,
        ]);
    }

    // =========================================================
    // 7. COMPANY GUIDELINES
    // =========================================================

    public function guidelines()
    {
        $grouped = $this->guidelineService->getActiveGuidelinesGrouped();

        return response()->json(['success' => true, 'data' => $grouped]);
    }

    public function guidelineDetail($id)
    {
        $detail = $this->guidelineService->findById($id);
        if (!$detail) {
            return response()->json(['success' => false, 'message' => 'Pedoman perusahaan tidak ditemukan.'], 404);
        }

        return response()->json(['success' => true, 'data' => $detail]);
    }
}
