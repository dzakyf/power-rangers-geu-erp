<?php

namespace App\Http\Controllers\HCGA\GA;

use App\Http\Controllers\Controller;
use App\Services\GA\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TicketController extends Controller
{
    use GaXml;

    protected $tickets;

    public function __construct(TicketService $tickets)
    {
        $this->tickets = $tickets;
    }

    public function index()
    {
        abort_unless(can_menu('SI01009', 'view'), 403);

        $categories = $this->categoryOptions();
        $teams = $this->teamOptions();

        return view('humanresources.ga.ticket.index', compact('categories', 'teams'));
    }

    public function data(Request $request)
    {
        abort_unless(can_menu('SI01009', 'view'), 403);

        $user = Auth::user();
        $filters = $request->only(['status', 'priority', 'category_id', 'search', 'date_from', 'date_to', 'location', 'company_id', 'assignee']);
        if (empty($filters['location'])) {
            $filters['location'] = $user->Location;
        }
        if (empty($filters['company_id'])) {
            $filters['company_id'] = $user->Company_ID;
        }

        $rows = $this->tickets->query($filters, $user)->limit(1000)->get();

        $this->xmlHeader();
        echo '<rows>';
        $no = 1;
        foreach ($rows as $row) {
            $status = strtoupper((string) $row->Ticket_Status);
            $assignee = $row->Assignee_Name ?: ($row->Team_Name ?: '-');
            echo '<row id="' . (int) $row->ID . '">';
            echo '<cell>' . $no . '</cell>';
            echo '<cell>' . $this->xml($row->Ticket_No) . '</cell>';
            echo '<cell>' . $this->xml($row->Ticket_Subject) . '</cell>';
            echo '<cell>' . $this->xml($row->Category_Name ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($row->Ga_Priority ?: $row->Priority) . '</cell>';
            echo '<cell>' . $this->xml($status) . '</cell>';
            echo '<cell>' . $this->xml($assignee) . '</cell>';
            echo '<cell>' . $this->xml($row->Response_Due_At ?: '-') . '</cell>';
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
        abort_unless(can_menu('SI01009', 'create'), 403);

        $categories = $this->categoryOptions();
        $subcategories = $this->subcategoryOptions();
        $teams = $this->teamOptions();
        $users = $this->userOptions();
        $qrPoints = DB::table('ga_qr_point')->where('Status', 'ACTIVE')->orderBy('Name')->get();
        $qrPointId = (int) $request->query('qr_point_id', 0);
        $qrPoint = $qrPointId
            ? DB::table('ga_qr_point')->where('ID', $qrPointId)->where('Status', 'ACTIVE')->first()
            : null;
        $partial = $request->query('partial');
        $isPartial = $request->ajax() || (is_scalar($partial) && (string) $partial === '1');
        $view = $isPartial
            ? 'humanresources.ga.ticket.create'
            : 'humanresources.ga.ticket.create-page';

        return view($view, compact('categories', 'subcategories', 'teams', 'users', 'qrPoints', 'qrPoint', 'qrPointId'));
    }

    public function store(Request $request)
    {
        abort_unless(can_menu('SI01009', 'create'), 403);

        $data = $request->validate([
            'Ticket_Subject' => 'required|string|max:250',
            'Description' => 'required|string|max:5000',
            'Category_ID' => 'nullable|integer',
            'Subcategory_ID' => 'nullable|integer',
            'Priority' => 'nullable|in:LOW,NORMAL,HIGH,URGENT',
            'Source' => 'nullable|in:FORM,QR,ON_BEHALF',
            'On_Behalf_Of_User_Id' => 'nullable|integer',
            'QR_Point_ID' => 'nullable|integer',
            'Reporter_Phone' => 'nullable|string|max:50',
            'Cost_Center' => 'nullable|string|max:50',
            'Department' => 'nullable|string|max:100',
            'Assignee_User_Id' => 'nullable|integer',
            'Location' => 'nullable|string|max:50',
            'Company_ID' => 'nullable|string|max:50',
        ]);

        try {
            $ticket = $this->tickets->create($data, Auth::user());

            return response()->json([
                'status' => 'success',
                'message' => 'Tiket berhasil dibuat.',
                'ticket_id' => (int) $ticket->ID,
                'ticket_no' => $ticket->Ticket_No,
            ]);
        } catch (\Throwable $exception) {
            Log::warning('GA ticket create failed', ['message' => $exception->getMessage()]);
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 422);
        }
    }

    public function detail(Request $request)
    {
        abort_unless(can_menu('SI01009', 'view'), 403);

        $ticketId = (int) $request->query('ID', 0);
        $ticket = $this->tickets->find($ticketId);
        abort_if(!$ticket, 404);

        $history = $this->tickets->history($ticketId);
        $comments = $this->tickets->comments($ticketId);
        $assignments = $this->tickets->assignments($ticketId);
        $workOrders = $this->tickets->workOrders($ticketId);
        $sla = $this->tickets->slaSummary($ticket);
        $allowedTransitions = $this->tickets->allowedTransitions(strtoupper((string) $ticket->Ticket_Status));
        $teams = $this->teamOptions();
        $users = $this->userOptions();
        $categories = $this->categoryOptions();
        $subcategories = $this->subcategoryOptions();
        $vendors = DB::table('supplier')->where('Status', 'ACTIVE')->orderBy('Company_Name')->limit(500)->get(['ID', 'Company_Name']);
        $partial = $request->query('partial');
        $isPartial = $request->ajax() || (is_scalar($partial) && (string) $partial === '1');
        $view = $isPartial
            ? 'humanresources.ga.ticket.detail'
            : 'humanresources.ga.ticket.detail-page';

        return view($view, compact('ticket', 'history', 'comments', 'assignments', 'workOrders', 'sla', 'allowedTransitions', 'teams', 'users', 'categories', 'subcategories', 'vendors'));
    }

    public function triage(Request $request)
    {
        abort_unless(can_menu('SI01009', 'edit'), 403);

        $data = $request->validate([
            'ID' => 'required|integer',
            'Category_ID' => 'nullable|integer',
            'Subcategory_ID' => 'nullable|integer',
            'Priority' => 'nullable|in:LOW,NORMAL,HIGH,URGENT',
            'Triage_Note' => 'nullable|string|max:2000',
            'Assignee_User_Id' => 'nullable|integer',
            'Assignee_Team_ID' => 'nullable|integer',
        ]);

        try {
            $ticket = $this->tickets->triage((int) $data['ID'], $data, Auth::user());
            return response()->json(['status' => 'success', 'message' => 'Tiket berhasil di-triage.', 'ticket' => $ticket]);
        } catch (\Throwable $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 422);
        }
    }

    public function assign(Request $request)
    {
        abort_unless(can_menu('SI01009', 'edit'), 403);

        $data = $request->validate([
            'ID' => 'required|integer',
            'Assignee_Type' => 'required|in:TEAM,USER,VENDOR',
            'Assignee_Team_ID' => 'nullable|required_if:Assignee_Type,TEAM|integer',
            'Assignee_User_Id' => 'nullable|required_if:Assignee_Type,USER|integer',
            'Assignee_Supplier_ID' => 'nullable|required_if:Assignee_Type,VENDOR|integer',
            'Reason' => 'nullable|string|max:2000',
        ]);

        try {
            $ticket = $this->tickets->assign((int) $data['ID'], $data, Auth::user());
            return response()->json(['status' => 'success', 'message' => 'Assignment berhasil.', 'ticket' => $ticket]);
        } catch (\Throwable $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 422);
        }
    }

    public function transition(Request $request)
    {
        abort_unless(can_menu('SI01009', 'edit'), 403);

        $data = $request->validate([
            'ID' => 'required|integer',
            'status' => 'required|string|max:30',
            'note' => 'nullable|string|max:5000',
        ]);

        try {
            $ticket = $this->tickets->transition((int) $data['ID'], $data['status'], $data, Auth::user());
            return response()->json(['status' => 'success', 'message' => 'Status tiket diperbarui.', 'ticket' => $ticket]);
        } catch (\Throwable $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 422);
        }
    }

    public function comment(Request $request)
    {
        abort_unless(can_menu('SI01009', 'edit'), 403);

        $data = $request->validate([
            'ID' => 'required|integer',
            'Comment' => 'required|string|max:5000',
            'Is_Internal' => 'nullable|boolean',
        ]);

        try {
            $comment = $this->tickets->comment((int) $data['ID'], $data['Comment'], !empty($data['Is_Internal']), Auth::user());
            return response()->json(['status' => 'success', 'message' => 'Komentar ditambahkan.', 'comment' => $comment]);
        } catch (\Throwable $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 422);
        }
    }

    public function myAssignments()
    {
        abort_unless(can_menu('SI01011', 'view'), 403);

        return view('humanresources.ga.ticket.my-assignments');
    }

    public function myAssignmentData()
    {
        abort_unless(can_menu('SI01011', 'view'), 403);

        $rows = $this->tickets->myAssignmentsQuery(Auth::user())->limit(1000)->get();

        $this->xmlHeader();
        echo '<rows>';
        $no = 1;
        foreach ($rows as $row) {
            echo '<row id="' . (int) $row->ID . '">';
            echo '<cell>' . $no . '</cell>';
            echo '<cell>' . $this->xml($row->Ticket_No) . '</cell>';
            echo '<cell>' . $this->xml($row->Ticket_Subject) . '</cell>';
            echo '<cell>' . $this->xml($row->Category_Name ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($row->Ga_Priority ?: $row->Priority) . '</cell>';
            echo '<cell>' . $this->xml($row->Ticket_Status) . '</cell>';
            echo '<cell>' . $this->xml($row->Resolve_Due_At ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($row->Created_Date) . '</cell>';
            echo '</row>';
            $no++;
        }
        echo '</rows>';
    }

    public function qr(Request $request)
    {
        $code = trim((string) $request->query('code', ''));
        $point = $code !== '' ? DB::table('ga_qr_point')->where('QR_Code', $code)->where('Status', 'ACTIVE')->first() : null;

        return view('humanresources.ga.ticket.qr', compact('point', 'code'));
    }

    public function slaScan()
    {
        abort_unless(can_menu('SI01009', 'approve'), 403);

        try {
            $count = $this->tickets->scanSla();
            return response()->json(['status' => 'success', 'message' => $count . ' event SLA dibuat.', 'count' => $count]);
        } catch (\Throwable $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 422);
        }
    }

    public function categoryOptionsJson()
    {
        abort_unless(can_menu('SI01009', 'view'), 403);

        return response()->json([
            'categories' => $this->categoryOptions(),
            'subcategories' => $this->subcategoryOptions(),
            'teams' => $this->teamOptions(),
            'users' => $this->userOptions(),
        ]);
    }

    protected function categoryOptions()
    {
        $user = Auth::user();

        return DB::table('ga_ticket_category')
            ->where('Status', 'ACTIVE')
            ->whereNull('Parent_ID')
            ->where(function ($q) use ($user) {
                $q->whereNull('Location')->orWhere('Location', $user->Location);
            })
            ->where(function ($q) use ($user) {
                $q->whereNull('Company_ID')->orWhere('Company_ID', $user->Company_ID);
            })
            ->orderBy('Name')
            ->get(['ID', 'Code', 'Name', 'Default_Priority', 'Default_Team_ID', 'Sla_Policy_ID']);
    }

    protected function subcategoryOptions()
    {
        $user = Auth::user();

        return DB::table('ga_ticket_category')
            ->where('Status', 'ACTIVE')
            ->whereNotNull('Parent_ID')
            ->where(function ($q) use ($user) {
                $q->whereNull('Location')->orWhere('Location', $user->Location);
            })
            ->where(function ($q) use ($user) {
                $q->whereNull('Company_ID')->orWhere('Company_ID', $user->Company_ID);
            })
            ->orderBy('Name')
            ->get(['ID', 'Parent_ID', 'Code', 'Name']);
    }

    protected function teamOptions()
    {
        $user = Auth::user();

        return DB::table('ga_team')
            ->where('Status', 'ACTIVE')
            ->where(function ($q) use ($user) {
                $q->whereNull('Location')->orWhere('Location', $user->Location);
            })
            ->where(function ($q) use ($user) {
                $q->whereNull('Company_ID')->orWhere('Company_ID', $user->Company_ID);
            })
            ->orderBy('Name')
            ->get(['ID', 'Code', 'Name', 'Team_Type']);
    }

    protected function userOptions()
    {
        return DB::table('users')
            ->whereNotNull('username')
            ->orderBy('Name')
            ->limit(500)
            ->get(['id', 'Name', 'username', 'Department', 'Location', 'Company_ID']);
    }
}
