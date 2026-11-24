<?php

namespace App\Http\Controllers\HCGA\GA;

use App\Http\Controllers\Controller;
use App\Services\GA\ItTicketService;
use App\Services\GA\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ItTicketController extends Controller
{
    use GaXml;

    /** @var ItTicketService */
    protected $itTickets;

    /** @var TicketService */
    protected $tickets;

    public function __construct(ItTicketService $itTickets, TicketService $tickets)
    {
        $this->itTickets = $itTickets;
        $this->tickets = $tickets;
    }

    public function index()
    {
        abort_unless(can_menu('SI01158', 'view'), 403);

        $user = Auth::user();
        $categories = $this->itCategoryOptions();
        $teams = $this->itTeamOptions();
        $summary = $this->itTickets->dashboardSummary($user, [
            'location'   => $user->Location,
            'company_id' => $user->Company_ID,
        ]);

        return view('humanresources.ga.it-ticket.index', compact('categories', 'teams', 'summary'));
    }

    public function summary(Request $request)
    {
        abort_unless(can_menu('SI01158', 'view'), 403);

        $user = Auth::user();
        $filters = $request->only(['location', 'company_id']);
        if (empty($filters['location'])) {
            $filters['location'] = $user->Location;
        }
        if (empty($filters['company_id'])) {
            $filters['company_id'] = $user->Company_ID;
        }

        return response()->json([
            'status'  => 'success',
            'summary' => $this->itTickets->dashboardSummary($user, $filters),
        ]);
    }

    public function data(Request $request)
    {
        abort_unless(can_menu('SI01158', 'view'), 403);

        $user = Auth::user();
        $filters = $request->only([
            'status',
            'priority',
            'category_id',
            'workflow_mode',
            'action_type',
            'search',
            'date_from',
            'date_to',
            'location',
            'company_id',
            'assignee',
        ]);
        $filters['domain_type'] = TicketService::DOMAIN_IT;
        if (empty($filters['location'])) {
            $filters['location'] = $user->Location;
        }
        if (empty($filters['company_id'])) {
            $filters['company_id'] = $user->Company_ID;
        }

        $rows = $this->tickets->query($filters, $user)->limit(1000)->get();

        $modeLabels = [
            TicketService::MODE_IT_SUPPORT        => 'Bantuan IT',
            TicketService::MODE_IT_INCIDENT       => 'Gangguan Sistem',
            TicketService::MODE_IT_DEVICE_REQUEST => 'Pengajuan Perangkat',
        ];

        $this->xmlHeader();
        echo '<rows>';
        $no = 1;
        foreach ($rows as $row) {
            $status = strtoupper((string) $row->Ticket_Status);
            $mode = strtoupper((string) ($row->Workflow_Mode ?? TicketService::MODE_IT_SUPPORT));
            $actionType = strtoupper((string) ($row->Action_Type ?? TicketService::ACTION_CORRECTIVE));
            $assignee = $row->Assignee_Name ?: ($row->Team_Name ?: '-');

            echo '<row id="' . (int) $row->ID . '">';
            echo '<cell>' . $no . '</cell>';
            echo '<cell>' . $this->xml($row->Ticket_No) . '</cell>';
            echo '<cell>' . $this->xml($modeLabels[$mode] ?? $mode) . '</cell>';
            echo '<cell>' . $this->xml($actionType) . '</cell>';
            echo '<cell>' . $this->xml($row->Ticket_Subject) . '</cell>';
            echo '<cell>' . $this->xml($row->Ga_Priority ?: $row->Priority) . '</cell>';
            echo '<cell>' . $this->xml($status) . '</cell>';
            echo '<cell>' . $this->xml($assignee) . '</cell>';
            echo '<cell>' . $this->xml($row->Resolve_Due_At ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($row->Created_Date) . '</cell>';
            echo '<cell>' . $this->xml($row->Reporter_Name ?: $row->Reporter) . '</cell>';
            echo '</row>';
            $no++;
        }
        echo '</rows>';
    }

    public function create(Request $request)
    {
        abort_unless(can_menu('SI01158', 'create'), 403);

        $categories = $this->itCategoryOptions();
        $subcategories = $this->itSubcategoryOptions();
        $users = $this->userOptions();
        $defaultMode = strtoupper((string) $request->query('mode', TicketService::MODE_IT_SUPPORT));
        $partial = $request->query('partial');
        $isPartial = $request->ajax() || (is_scalar($partial) && (string) $partial === '1');
        $view = $isPartial
            ? 'humanresources.ga.it-ticket.create'
            : 'humanresources.ga.it-ticket.create-page';

        return view($view, compact('categories', 'subcategories', 'users', 'defaultMode'));
    }

    public function store(Request $request)
    {
        abort_unless(can_menu('SI01158', 'create'), 403);

        $data = $request->validate([
            'Ticket_Subject'        => 'required|string|max:250',
            'Description'           => 'required|string|max:5000',
            'Workflow_Mode'         => 'required|in:IT_SUPPORT,IT_INCIDENT,IT_DEVICE_REQUEST',
            'Action_Type'           => 'required|in:PREVENTIVE,CORRECTIVE',
            'Category_ID'           => 'nullable|integer',
            'Subcategory_ID'        => 'nullable|integer',
            'Priority'              => 'nullable|in:LOW,NORMAL,HIGH,URGENT',
            'On_Behalf_Of_User_Id'  => 'nullable|integer',
            'Reporter_Phone'        => 'nullable|string|max:50',
            'Department'            => 'nullable|string|max:100',
            'Cost_Center'           => 'nullable|string|max:50',
            'Location'              => 'nullable|string|max:50',
            'Company_ID'            => 'nullable|string|max:50',
            'Device_Spec'           => 'nullable|string|max:5000',
            'attachments'           => 'nullable|array|max:5',
            'attachments.*'         => 'file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
        ]);

        try {
            $files = $request->file('attachments', []);
            if (!is_array($files)) {
                $files = $files ? [$files] : [];
            }

            $ticket = $this->itTickets->create($data, Auth::user(), $files);

            return response()->json([
                'status'    => 'success',
                'message'   => 'Tiket IT berhasil dibuat.',
                'ticket_id' => (int) $ticket->ID,
                'ticket_no' => $ticket->Ticket_No,
            ]);
        } catch (\Throwable $exception) {
            Log::warning('IT ticket create failed', ['message' => $exception->getMessage()]);
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 422);
        }
    }

    public function detail(Request $request)
    {
        abort_unless(can_menu('SI01158', 'view'), 403);

        $ticketId = (int) $request->query('ID', 0);
        $ticket = $this->tickets->find($ticketId);
        abort_if(!$ticket || strtoupper((string) ($ticket->Domain_Type ?? '')) !== TicketService::DOMAIN_IT, 404);

        if (!empty($ticket->Dept_Approval_Request_ID) && strtoupper((string) $ticket->Ticket_Status) === TicketService::STATUS_WAITING_MANAGER_APPROVAL) {
            $ticket = $this->itTickets->syncApprovalStatus((int) $ticket->Dept_Approval_Request_ID) ?: $ticket;
        }

        $history = $this->tickets->history($ticketId);
        $comments = $this->tickets->comments($ticketId);
        $assignments = $this->tickets->assignments($ticketId);
        $attachments = $this->tickets->attachments($ticketId);
        $sla = $this->tickets->slaSummary($ticket);
        $mode = strtoupper((string) ($ticket->Workflow_Mode ?? TicketService::MODE_IT_SUPPORT));
        $status = strtoupper((string) $ticket->Ticket_Status);
        $allowedTransitions = $this->itTickets->allowedTransitions($mode, $status);
        $progressSteps = $this->tickets->progressSteps($ticket);
        $approvalTimeline = $this->itTickets->getApprovalTimeline($ticket);
        $relatedIncident = !empty($ticket->Escalated_To_Ticket_ID)
            ? $this->tickets->find((int) $ticket->Escalated_To_Ticket_ID)
            : null;
        $teams = $this->itTeamOptions();
        $users = $this->userOptions();
        $assets = ($mode === TicketService::MODE_IT_DEVICE_REQUEST)
            ? DB::table('asset_list')
                ->where(function ($q) {
                    $q->whereNull('Status')->orWhere('Status', '!=', 'DISPOSED');
                })
                ->orderBy('Asset_No')
                ->limit(500)
                ->get(['Asset_No', 'Product_Description', 'Asset_Location', 'User_Responsible', 'Status'])
            : collect();

        $partial = $request->query('partial');
        $isPartial = $request->ajax() || (is_scalar($partial) && (string) $partial === '1');
        $view = $isPartial
            ? 'humanresources.ga.it-ticket.detail'
            : 'humanresources.ga.it-ticket.detail-page';

        return view($view, compact(
            'ticket',
            'history',
            'comments',
            'assignments',
            'attachments',
            'sla',
            'allowedTransitions',
            'progressSteps',
            'approvalTimeline',
            'relatedIncident',
            'teams',
            'users',
            'assets'
        ));
    }

    public function transition(Request $request)
    {
        abort_unless(can_menu('SI01158', 'edit'), 403);

        $data = $request->validate([
            'ID'              => 'required|integer',
            'status'          => 'required|string|max:30',
            'note'            => 'nullable|string|max:5000',
            'Diagnosis_Note'  => 'nullable|string|max:5000',
            'Root_Cause'      => 'nullable|string|max:5000',
            'Recovery_Note'   => 'nullable|string|max:5000',
            'Testing_Note'    => 'nullable|string|max:5000',
            'Kb_Flag'         => 'nullable|boolean',
            'Kb_Title'        => 'nullable|string|max:200',
            'Kb_Solution'     => 'nullable|string|max:5000',
            'Monitoring_Note' => 'nullable|string|max:5000',
            'Backup_Verified' => 'nullable|boolean',
            'Backup_Note'     => 'nullable|string|max:5000',
        ]);

        try {
            $ticket = $this->itTickets->transition((int) $data['ID'], $data['status'], $data, Auth::user());
            return response()->json(['status' => 'success', 'message' => 'Status tiket IT diperbarui.', 'ticket' => $ticket]);
        } catch (\Throwable $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 422);
        }
    }

    public function assign(Request $request)
    {
        abort_unless(can_menu('SI01158', 'edit'), 403);

        $data = $request->validate([
            'ID'               => 'required|integer',
            'Assignee_Type'    => 'required|in:TEAM,USER',
            'Assignee_Team_ID' => 'nullable|required_if:Assignee_Type,TEAM|integer',
            'Assignee_User_Id' => 'nullable|required_if:Assignee_Type,USER|integer',
            'Reason'           => 'nullable|string|max:2000',
        ]);

        try {
            $ticket = $this->tickets->assign((int) $data['ID'], $data, Auth::user(), true);
            return response()->json(['status' => 'success', 'message' => 'Assignment tiket IT berhasil.', 'ticket' => $ticket]);
        } catch (\Throwable $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 422);
        }
    }

    public function comment(Request $request)
    {
        abort_unless(can_menu('SI01158', 'view'), 403);

        $data = $request->validate([
            'ID'          => 'required|integer',
            'Comment'     => 'required|string|max:5000',
            'Is_Internal' => 'nullable|boolean',
        ]);

        try {
            $isInternal = !empty($data['Is_Internal']) && can_menu('SI01158', 'edit');
            $comment = $this->tickets->comment((int) $data['ID'], $data['Comment'], $isInternal, Auth::user());
            return response()->json(['status' => 'success', 'message' => 'Komentar ditambahkan.', 'comment' => $comment]);
        } catch (\Throwable $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 422);
        }
    }

    public function escalate(Request $request)
    {
        abort_unless(can_menu('SI01158', 'edit'), 403);

        $data = $request->validate([
            'ID'               => 'required|integer',
            'Escalation_Note'  => 'required|string|max:5000',
            'Spawn_Incident'   => 'nullable|boolean',
            'Assignee_Team_ID' => 'nullable|integer',
            'Assignee_User_Id' => 'nullable|integer',
        ]);

        try {
            $ticket = $this->itTickets->escalate((int) $data['ID'], $data, Auth::user());
            return response()->json(['status' => 'success', 'message' => 'Tiket berhasil dieskalasi.', 'ticket' => $ticket]);
        } catch (\Throwable $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 422);
        }
    }

    public function feedback(Request $request)
    {
        abort_unless(can_menu('SI01158', 'view'), 403);

        $data = $request->validate([
            'ID'            => 'required|integer',
            'Is_Resolved'   => 'required|boolean',
            'User_Rating'   => 'nullable|integer|min:1|max:5',
            'User_Feedback' => 'nullable|string|max:5000',
        ]);

        try {
            $ticket = $this->itTickets->submitUserFeedback((int) $data['ID'], $data, Auth::user());
            return response()->json(['status' => 'success', 'message' => 'Konfirmasi & feedback Anda berhasil disimpan.', 'ticket' => $ticket]);
        } catch (\Throwable $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 422);
        }
    }

    public function handoverDevice(Request $request)
    {
        abort_unless(can_menu('SI01158', 'edit'), 403);

        $data = $request->validate([
            'ID'                      => 'required|integer',
            'action'                  => 'nullable|in:assign,handover',
            'Device_Asset_No'         => 'required|string|max:50',
            'Device_Assigned_User_Id' => 'nullable|integer',
            'Handover_Note'           => 'nullable|string|max:5000',
        ]);

        try {
            $ticket = $this->itTickets->handoverDevice((int) $data['ID'], $data, Auth::user());
            return response()->json(['status' => 'success', 'message' => 'Data perangkat / serah terima berhasil disimpan.', 'ticket' => $ticket]);
        } catch (\Throwable $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 422);
        }
    }

    public function escalateDevicePr(Request $request)
    {
        abort_unless(can_menu('SI01158', 'edit'), 403);

        $data = $request->validate([
            'ID'        => 'required|integer',
            'Item_Name' => 'required|string|max:200',
            'Qty'       => 'nullable|numeric|min:1',
            'Est_Price' => 'nullable|numeric|min:0',
            'Unit'      => 'nullable|string|max:30',
        ]);

        try {
            $pr = $this->itTickets->escalateDeviceToPr((int) $data['ID'], $data, Auth::user());
            return response()->json([
                'status'  => 'success',
                'message' => 'Purchase Request ' . ($pr['pr_no'] ?? '') . ' berhasil dibuat.',
                'pr'      => $pr,
            ]);
        } catch (\Throwable $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 422);
        }
    }

    public function syncApproval(Request $request)
    {
        abort_unless(can_menu('SI01158', 'view'), 403);

        $ticketId = (int) $request->input('ID', 0);
        $ticket = $this->tickets->find($ticketId);
        abort_if(!$ticket || strtoupper((string) ($ticket->Domain_Type ?? '')) !== TicketService::DOMAIN_IT, 404);

        if (!empty($ticket->Dept_Approval_Request_ID)) {
            $ticket = $this->itTickets->syncApprovalStatus((int) $ticket->Dept_Approval_Request_ID) ?: $ticket;
        }

        return response()->json(['status' => 'success', 'message' => 'Status approval disinkronkan.', 'ticket' => $ticket]);
    }

    public function kbSearch(Request $request)
    {
        abort_unless(can_menu('SI01158', 'view'), 403);

        $keyword = (string) $request->query('q', '');
        $rows = $this->itTickets->searchKnowledgeBase($keyword, 30);

        return response()->json(['status' => 'success', 'items' => $rows]);
    }

    public function myAssignments()
    {
        abort_unless(can_menu('SI01159', 'view'), 403);

        return view('humanresources.ga.it-ticket.my-assignments');
    }

    public function myAssignmentData()
    {
        abort_unless(can_menu('SI01159', 'view'), 403);

        $rows = $this->tickets->myAssignmentsQuery(Auth::user(), [
            'domain_type' => TicketService::DOMAIN_IT,
        ])->limit(1000)->get();

        $this->xmlHeader();
        echo '<rows>';
        $no = 1;
        foreach ($rows as $row) {
            echo '<row id="' . (int) $row->ID . '">';
            echo '<cell>' . $no . '</cell>';
            echo '<cell>' . $this->xml($row->Ticket_No) . '</cell>';
            echo '<cell>' . $this->xml($row->Workflow_Mode ?: 'IT_SUPPORT') . '</cell>';
            echo '<cell>' . $this->xml($row->Action_Type ?: 'CORRECTIVE') . '</cell>';
            echo '<cell>' . $this->xml($row->Ticket_Subject) . '</cell>';
            echo '<cell>' . $this->xml($row->Ga_Priority ?: $row->Priority) . '</cell>';
            echo '<cell>' . $this->xml($row->Ticket_Status) . '</cell>';
            echo '<cell>' . $this->xml($row->Resolve_Due_At ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($row->Created_Date) . '</cell>';
            echo '</row>';
            $no++;
        }
        echo '</rows>';
    }

    public function downloadAttachment(Request $request)
    {
        abort_unless(can_menu('SI01158', 'view'), 403);

        $attId = (int) $request->query('attachment_id', 0);
        $att = $this->tickets->findAttachment($attId);
        abort_if(!$att, 404);

        $ticket = $this->tickets->find((int) $att->Ticket_ID);
        abort_if(!$ticket || strtoupper((string) ($ticket->Domain_Type ?? '')) !== TicketService::DOMAIN_IT, 404);

        $fullPath = storage_path('app/' . ltrim((string) $att->File_Path, '/\\'));
        abort_if(!is_file($fullPath), 404);

        return response()->download($fullPath, $att->File_Name);
    }

    protected function itCategoryOptions()
    {
        $user = Auth::user();

        return DB::table('ga_ticket_category')
            ->where('Status', 'ACTIVE')
            ->whereNull('Parent_ID')
            ->where('Code', 'like', 'IT-%')
            ->where(function ($q) use ($user) {
                $q->whereNull('Location')->orWhere('Location', $user->Location);
            })
            ->where(function ($q) use ($user) {
                $q->whereNull('Company_ID')->orWhere('Company_ID', $user->Company_ID);
            })
            ->orderBy('Name')
            ->get(['ID', 'Code', 'Name', 'Default_Priority', 'Default_Team_ID', 'Sla_Policy_ID']);
    }

    protected function itSubcategoryOptions()
    {
        $user = Auth::user();

        return DB::table('ga_ticket_category as c')
            ->join('ga_ticket_category as p', 'p.ID', '=', 'c.Parent_ID')
            ->where('c.Status', 'ACTIVE')
            ->where('p.Status', 'ACTIVE')
            ->whereNotNull('c.Parent_ID')
            ->where('c.Code', 'like', 'IT-%')
            ->where('p.Code', 'like', 'IT-%')
            ->where(function ($q) use ($user) {
                $q->whereNull('c.Location')->orWhere('c.Location', $user->Location);
            })
            ->where(function ($q) use ($user) {
                $q->whereNull('c.Company_ID')->orWhere('c.Company_ID', $user->Company_ID);
            })
            ->where(function ($q) use ($user) {
                $q->whereNull('p.Location')->orWhere('p.Location', $user->Location);
            })
            ->where(function ($q) use ($user) {
                $q->whereNull('p.Company_ID')->orWhere('p.Company_ID', $user->Company_ID);
            })
            ->orderBy('c.Name')
            ->get(['c.ID', 'c.Parent_ID', 'c.Code', 'c.Name']);
    }

    protected function itTeamOptions()
    {
        $rows = DB::table('ga_team')
            ->where('Status', 'ACTIVE')
            ->where(function ($q) {
                $q->where('Code', 'like', 'IT-%')->orWhere('Department', 'ICT');
            })
            ->orderBy('Name')
            ->get(['ID', 'Code', 'Name', 'Team_Type']);

        if ($rows->isEmpty()) {
            return DB::table('ga_team')->where('Status', 'ACTIVE')->orderBy('Name')->get(['ID', 'Code', 'Name', 'Team_Type']);
        }

        return $rows;
    }

    protected function userOptions()
    {
        return DB::table('users')
            ->where('Status', 'ACTIVE')
            ->whereNotNull('username')
            ->orderBy('Name')
            ->limit(500)
            ->get(['id', 'Name', 'username', 'Department', 'Location', 'Company_ID']);
    }
}
