<?php

namespace App\Http\Controllers\HCGA;

use App\Exceptions\ApprovalWorkflowException;
use App\Http\Controllers\Controller;
use App\Services\Approval\RequisitionApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Mpdf\Mpdf;

class RequisitionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $location = $user->Location;
        $priv = $user->Priv_ID;
        $username = $user->username;

        $baseQuery = DB::table('requisition')->where('Location', $location);
        if ($priv != 'HCGA' && $priv != 'ADM') {
            $baseQuery->where('Created_By', $username);
        }

        $allActive = (clone $baseQuery)
            ->whereNotIn('Status', ['CLOSED', 'CANCELED'])
            ->get();

        $totalActive = $allActive->count();
        $totalOnProgress = $allActive->whereIn('Status', ['CREATED', 'ON_PROGRESS', 'WAITING_APPROVAL', 'PENDING'])->count();
        $totalApproved = $allActive->where('Status', 'APPROVED')->count();
        $totalRejected = $allActive->where('Status', 'REJECTED')->count();
        $totalHeadcount = $allActive->sum('Qty');
        $urgentCount = $allActive->where('Priority', 'URGENT')->count();

        // Ambil daftar policy dan step approval REQUISITION dari tabel approval_policy & approval_policy_step
        $requisitionPolicies = DB::table('approval_policy')
            ->where('Request_Type', 'REQUISITION')
            ->orderByRaw("CASE WHEN Status = 'ACTIVE' THEN 0 ELSE 1 END")
            ->orderBy('ID', 'asc')
            ->get();

        foreach ($requisitionPolicies as $pol) {
            $pol->steps = DB::table('approval_policy_step')
                ->where('Policy_ID', $pol->ID)
                ->orderBy('Stage_Order', 'asc')
                ->orderBy('Level_Number', 'asc')
                ->orderBy('ID', 'asc')
                ->get();
        }

        return view('humanresources.recruitment.requisition', compact(
            'location',
            'username',
            'totalActive',
            'totalOnProgress',
            'totalApproved',
            'totalRejected',
            'totalHeadcount',
            'urgentCount',
            'requisitionPolicies'
        ));
    }

    public function indexXml()
    {
        //
    }

    /**
     * Tampilkan form pengajuan PTK baru (partial view)
     */
    public function create()
    {
        $user = Auth::user();
        $location = $user->Location;
        $username = $user->username;

        $positions = DB::table('master_position')->orderBy('Position', 'asc')->get();
        $workCalendar = DB::table('work_calendars')->get();
        $reqCount = DB::table('requisition')->where('Created_Date', 'LIKE', date('Y') . '%')->count() + 1;
        $taskId = 'REQ/' . $location . '/' . date('Ymdhis') . '-' . sprintf('%02d', $reqCount);
        $locations = DB::table('location')->get();

        $policies = DB::table('approval_policy')
            ->where('Request_Type', 'REQUISITION')
            ->orderByRaw("CASE WHEN Status = 'ACTIVE' THEN 0 ELSE 1 END")
            ->orderBy('ID', 'asc')
            ->get();

        foreach ($policies as $pol) {
            $pol->steps = DB::table('approval_policy_step')
                ->where('Policy_ID', $pol->ID)
                ->orderBy('Stage_Order', 'asc')
                ->orderBy('Level_Number', 'asc')
                ->orderBy('ID', 'asc')
                ->get();
        }

        return view('humanresources.recruitment.requisition-new', compact(
            'location',
            'username',
            'positions',
            'workCalendar',
            'taskId',
            'locations',
            'policies'
        ));
    }

    /**
     * Tampilkan detail PTK (partial view modal/container)
     */
    public function detail(Request $request)
    {
        $id = $request->get('ID') ?? $request->get('id');
        $taskId = $request->get('Task_ID') ?? $request->get('Task');

        $query = DB::table('requisition');
        if (!empty($id)) {
            $query->where('ID', $id);
        } elseif (!empty($taskId)) {
            $query->where('Task_ID', $taskId);
        } else {
            abort(404, 'Data PTK tidak ditemukan');
        }

        $ptk = $query->first();
        if (!$ptk) {
            return response('<div class="alert alert-danger m-3"><i class="fa fa-exclamation-triangle me-2"></i> Data PTK tidak ditemukan.</div>', 404);
        }

        // Master Position
        $position = DB::table('master_position')
            ->where('Position', $ptk->Job_Position)
            ->where('Department', $ptk->Department)
            ->first();

        // Work Calendar
        $workCalendar = DB::table('work_calendars')->where('ID', $ptk->Work_Schedule)->first();

        // Approval timeline/tasks
        $approvalTasks = [];
        $approvalRequest = null;
        $policySteps = [];
        $matchedPolicy = null;

        if (!empty($ptk->Approval_Request_ID)) {
            $approvalRequest = DB::table('approval_request')->where('ID', $ptk->Approval_Request_ID)->first();
            $approvalTasks = DB::table('approval_request_task as t')
                ->leftJoin('users as u_act', 'u_act.id', '=', 't.Acted_By')
                ->where('t.Request_ID', $ptk->Approval_Request_ID)
                ->select('t.*', 'u_act.username as Acted_By_Username', 'u_act.Name as Acted_By_Name')
                ->orderBy('t.Stage_Order', 'asc')
                ->orderBy('t.ID', 'asc')
                ->get();

            foreach ($approvalTasks as $task) {
                $candidates = DB::table('approval_task_candidate as c')
                    ->join('users as u', 'u.id', '=', 'c.User_Id')
                    ->where('c.Task_ID', $task->ID)
                    ->where('c.Status', 'ACTIVE')
                    ->pluck('u.username')
                    ->toArray();
                $task->Candidate_Usernames = !empty($candidates) ? implode(', ', $candidates) : '-';
            }

            if ($approvalRequest && !empty($approvalRequest->Policy_ID)) {
                $matchedPolicy = DB::table('approval_policy')->where('ID', $approvalRequest->Policy_ID)->first();
                $policySteps = DB::table('approval_policy_step')
                    ->where('Policy_ID', $approvalRequest->Policy_ID)
                    ->orderBy('Stage_Order', 'asc')
                    ->orderBy('Level_Number', 'asc')
                    ->get();
            }
        }

        // Jika belum punya Policy_ID dari request, cari policy REQUISITION dari tabel approval_policy
        if (empty($policySteps)) {
            $posLevel = $position ? (int) $position->Level : 4;
            $dept = $ptk->Department ?? '';
            $policies = DB::table('approval_policy')
                ->where('Request_Type', 'REQUISITION')
                ->orderByRaw("CASE WHEN Status = 'ACTIVE' THEN 0 ELSE 1 END")
                ->get();

            foreach ($policies as $pol) {
                if (!empty($pol->Department) && strcasecmp($pol->Department, $dept) !== 0) {
                    continue;
                }
                $conds = !empty($pol->Conditions) ? json_decode($pol->Conditions, true) : [];
                $isMatch = true;
                if (is_array($conds)) {
                    foreach ($conds as $c) {
                        if (($c['field'] ?? '') === 'Position_Level') {
                            $vals = (array) ($c['value'] ?? []);
                            $op = strtolower($c['operator'] ?? 'in');
                            if ($op === 'in' && !in_array($posLevel, $vals)) $isMatch = false;
                            if ($op === 'not_in' && in_array($posLevel, $vals)) $isMatch = false;
                        }
                    }
                }
                if ($isMatch) {
                    $matchedPolicy = $pol;
                    $policySteps = DB::table('approval_policy_step')
                        ->where('Policy_ID', $pol->ID)
                        ->orderBy('Stage_Order', 'asc')
                        ->orderBy('Level_Number', 'asc')
                        ->get();
                    break;
                }
            }
        }

        // Legacy logs jika ada
        $legacyLogs = DB::table('requisition_approval')
            ->where('Task_ID', $ptk->Task_ID)
            ->orderBy('ID', 'asc')
            ->get();

        return view('humanresources.recruitment.requisition-detail', compact(
            'ptk',
            'position',
            'workCalendar',
            'approvalRequest',
            'approvalTasks',
            'legacyLogs',
            'matchedPolicy',
            'policySteps'
        ));
    }

    public function print(Request $request)
    {
        // ── Ambil data (sesuaikan dengan model Anda) ──────────────────────
        $ptk = DB::table('requisition')->where("Task_ID", $request->Task)->first();
        $position = DB::table('master_position')->where('Position', $ptk->Job_Position)->where('Department', $ptk->Department)->orderBy('Position', 'asc')->get();
        $workCalendar = DB::table('work_calendars')->get();

        return view('humanresources.recruitment.prints.requisition', compact(
            'ptk', 'position', 'workCalendar'
        ));
 
        // ── Render HTML dari Blade view ───────────────────────────────────
        $html = view('humanresources.recruitment.prints.requisition', compact(
            'ptk', 'position', 'workCalendar'
        ))->render();
 
        // ── Header & Footer HTML ──────────────────────────────────────────
        // $letterhead = public_path('assets/images/large-logo.png');
        // $footerImg  = public_path($companies[$shipment->Shipper]->Footer);
 
        // Konversi ke base64 agar mPDF bisa render gambar
        // $headerB64 = base64_encode(file_get_contents($letterhead));
        // $footerB64 = base64_encode(file_get_contents($footerImg));
        // $headerExt = strtolower(pathinfo($letterhead, PATHINFO_EXTENSION));
        // $footerExt = strtolower(pathinfo($footerImg,  PATHINFO_EXTENSION));
 
        // $headerHtml = '
        //     <div style="margin:0;padding:0;line-height:0;">
        //         <img src="data:image/' . $headerExt . ';base64,' . $headerB64 . '"
        //              style="width:100%;display:block;">
        //     </div>';
 
        // $footerHtml = '
        //     <div style="margin:0;padding:0;line-height:0;">
        //         <img src="data:image/' . $footerExt . ';base64,' . $footerB64 . '"
        //              style="width:100%;display:block;">
        //     </div>';
 
        // ── Inisialisasi mPDF ─────────────────────────────────────────────
        $mpdf = new Mpdf([
            'format'        => 'A4',
            'tempDir'       => storage_path('app/mpdf'),
            'margin_top'    => 5,   // ← sesuaikan dengan tinggi header image (mm)
            'margin_bottom' => 0,   // ← sesuaikan dengan tinggi footer image (mm)
            'margin_left'   => 5,
            'margin_right'  => 5,
            // penting
            'margin_header' => 0,
            'margin_footer' => 0,
            // penting
            'setAutoTopMargin'    => 'stretch',
            'setAutoBottomMargin' => 'stretch',
        ]);
        
        $mpdf->splitTableBorderWidth = 1;
 
        // $mpdf->SetHTMLHeader($headerHtml);
        // $mpdf->SetHTMLFooter($footerHtml);
 
        $mpdf->WriteHTML($html);
 
        // ── Output ────────────────────────────────────────────────────────
        $filename = 'PTK_' . ($ptk->Task_ID);
        $mpdf->SetTitle($filename);
 
        // 'I' = tampil di browser, 'D' = download, 'F' = simpan ke file
        return response($mpdf->Output($filename . '.pdf', 'S'), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    protected $approval;

    public function __construct(RequisitionApprovalService $approval)
    {
        $this->approval = $approval;
    }

    public function store(Request $request)
    {
        $request->validate([
            'Job_Position'  => 'required|integer|exists:master_position,ID',
            'Placement'     => 'required|string|max:255',
            'Description'   => 'required|string',
            'Requirement'   => 'required|string',
            'Priority'      => 'required|in:NORMAL,HIGH,URGENT',
            'Allocation'    => 'required|in:NEW,REPLACEMENT',
            'Request_Type'  => 'required|string',
            'Request_Desc'  => 'nullable|required_if:Request_Type,OTHER|string|max:255',
            'Work_Schedule' => 'required|integer|exists:work_calendars,ID',
            'Qty'           => 'required|integer|min:1',
            'Start_Date'    => 'required|date',
            'Task_ID'       => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $LSP = $user->Location;
        $Saya = $user->username;

        $jobPosition = DB::table('master_position')->where('ID', $request->input('Job_Position'))->first();

        $startDate = date('Y-m-d', strtotime($request->input('Start_Date')));
        $endDate = date('Y-m-d', strtotime($startDate . ' +1 month'));

        $arr = [
            'Task_ID'          => $request->input('Task_ID'),
            'Job_Position'     => $jobPosition->Position,
            'Department'       => $jobPosition->Department,
            'Subject'          => $request->input('Subject') ?? '',
            'Placement'        => $request->input('Placement'),
            'Description'      => $request->input('Description'),
            'Requirement'      => $request->input('Requirement'),
            'Priority'         => $request->input('Priority'),
            'Allocation'       => $request->input('Allocation'),
            'Request_Type'     => $request->input('Request_Type'),
            'Request_Desc'     => $request->input('Request_Desc'),
            'Work_Schedule'    => $request->input('Work_Schedule'),
            'Qty'              => $request->input('Qty'),
            'Start_Date'       => $startDate,
            'End_Date'         => $endDate,
            'Created_By'       => $Saya,
            'Location'         => $LSP,
            'Created_Date'     => now(),
            'Status'           => 'CREATED',
            'Requisition_Type' => 'NON_PO',
        ];

        // Cegah Task_ID ganda (double submit)
        $dupTask = DB::table('requisition')->where('Task_ID', $request->input('Task_ID'))->first();
        if ($dupTask) {
            return 'exist';
        }

        // Cegah PTK terbuka ganda untuk posisi + departemen yang sama
        $openReq = DB::table('requisition')
            ->where('Job_Position', $jobPosition->Position)
            ->where('Department', $jobPosition->Department)
            ->where('Allocation', $request->input('Allocation'))
            ->whereNotIn('Status', ['CANCELED', 'CLOSED', 'REJECTED'])
            ->orderBy('ID', 'DESC')
            ->first();
        if ($openReq && strtotime($openReq->End_Date) >= strtotime(date('Y-m-d'))) {
            return 'exist';
        }

        // requisition dan requisition_log adalah MyISAM, jadi transaksi tidak
        // bisa rollback baris tersebut. Validasi policy dilakukan SEBELUM
        // insert agar error POLICY_NOT_FOUND tidak meninggalkan data PTK.
        $draftRequisition = (object) [
            'ID' => 0,
            'Task_ID' => $request->input('Task_ID'),
            'Location' => $LSP,
            'Department' => $jobPosition->Department,
        ];

        $reqId = null;

        try {
            $this->approval->assertCanSubmit($draftRequisition, $jobPosition, $user);

            DB::transaction(function () use ($arr, $jobPosition, $user, &$reqId) {
                $reqId = DB::table('requisition')->insertGetId($arr);

                $logArr = $arr;
                $logArr['Action_By'] = $user->username;
                $logArr['Action_Date'] = now();
                $logArr['Action_Status'] = 'CREATE FROM REQUISITION';
                $logArr['Last_ID'] = $reqId;

                DB::table('requisition_log')->insert($logArr);

                // Buat approval_request + task dari policy yang aktif.
                $requisition = DB::table('requisition')->where('ID', $reqId)->first();
                $this->approval->submit($requisition, $jobPosition, $user);
            });
        } catch (ApprovalWorkflowException $e) {
            if ($reqId !== null) {
                $this->approval->cleanupFailedSubmission($request->input('Task_ID'), (int) $reqId);
            }

            Log::warning('Requisition approval policy gagal: ' . $e->getMessage(), [
                'code' => $e->getErrorCode(),
                'Task_ID' => $request->input('Task_ID'),
            ]);

            return response()->json([
                'message' => $e->getMessage(),
                'code' => $e->getErrorCode(),
            ], $e->getStatusCode());
        } catch (\Throwable $e) {
            if ($reqId !== null) {
                $this->approval->cleanupFailedSubmission($request->input('Task_ID'), (int) $reqId);
            }

            Log::error('Requisition submit gagal: ' . $e->getMessage(), [
                'Task_ID' => $request->input('Task_ID'),
            ]);

            return response()->json([
                'message' => 'Requisition gagal diajukan: ' . $e->getMessage(),
            ], 500);
        }

        return 'success';
    }
}
