<?php

namespace App\Http\Controllers\HCGA\GA;

use App\Http\Controllers\Controller;
use App\Services\GA\TicketService;
use App\Services\GA\WorkOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WorkOrderController extends Controller
{
    use GaXml;

    protected $workOrders;
    protected $tickets;

    public function __construct(WorkOrderService $workOrders, TicketService $tickets)
    {
        $this->workOrders = $workOrders;
        $this->tickets = $tickets;
    }

    public function index()
    {
        abort_unless(can_menu('SI01010', 'view'), 403);

        return view('humanresources.ga.work-order.index');
    }

    public function data(Request $request)
    {
        abort_unless(can_menu('SI01010', 'view'), 403);

        $user = Auth::user();
        $filters = $request->only(['status', 'search', 'ticket_id', 'location', 'company_id', 'assignee']);
        if (empty($filters['location'])) {
            $filters['location'] = $user->Location;
        }
        if (empty($filters['company_id'])) {
            $filters['company_id'] = $user->Company_ID;
        }

        $rows = $this->workOrders->query($filters, $user)->limit(1000)->get();
        $costMap = $this->workOrders->totalCostMap($rows->pluck('ID')->all());

        $this->xmlHeader();
        echo '<rows>';
        $no = 1;
        foreach ($rows as $row) {
            $assignee = $row->Assignee_Name ?: ($row->Team_Name ?: ($row->Vendor_Name ?: '-'));
            echo '<row id="' . (int) $row->ID . '">';
            echo '<cell>' . $no . '</cell>';
            echo '<cell>' . $this->xml($row->WO_No) . '</cell>';
            echo '<cell>' . $this->xml($row->Ticket_No ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($row->Title) . '</cell>';
            echo '<cell>' . $this->xml($assignee) . '</cell>';
            echo '<cell>' . $this->xml($row->Status) . '</cell>';
            echo '<cell>' . $this->xml(number_format((float) $row->Vendor_Cost, 2, ',', '.')) . '</cell>';
            $totalCost = isset($costMap[(int) $row->ID]) ? $costMap[(int) $row->ID] : 0.0;
            echo '<cell>' . $this->xml(number_format($totalCost, 2, ',', '.')) . '</cell>';
            echo '<cell>' . $this->xml($row->Planned_Start ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($row->Planned_End ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($row->Created_At) . '</cell>';
            echo '</row>';
            $no++;
        }
        echo '</rows>';
    }

    public function create(Request $request)
    {
        abort_unless(can_menu('SI01010', 'create'), 403);

        $ticketId = (int) $request->query('ticket_id', 0);
        $ticket = $ticketId ? $this->tickets->find($ticketId) : null;
        $tickets = DB::table('ticket as t')
            ->join('ga_ticket as g', 'g.Ticket_ID', '=', 't.ID')
            ->where('t.Module', 'GA')
            ->whereNotIn('t.Ticket_Status', [TicketService::STATUS_CLOSED, TicketService::STATUS_REJECTED])
            ->orderBy('t.Created_Date', 'desc')
            ->limit(200)
            ->get(['t.ID', 't.Ticket_No', 't.Ticket_Subject', 't.Ticket_Status']);
        $teams = DB::table('ga_team')->where('Status', 'ACTIVE')->orderBy('Name')->get(['ID', 'Name', 'Team_Type']);
        $users = DB::table('users')->whereNotNull('username')->orderBy('Name')->limit(500)->get(['id', 'Name', 'username', 'Department']);
        $vendors = DB::table('supplier')->where('Status', 'ACTIVE')->orderBy('Company_Name')->limit(500)->get(['ID', 'Company_Name']);

        return view('humanresources.ga.work-order.create', compact('ticket', 'ticketId', 'tickets', 'teams', 'users', 'vendors'));
    }

    public function store(Request $request)
    {
        abort_unless(can_menu('SI01010', 'create'), 403);

        $data = $request->validate([
            'Ticket_ID' => 'required|integer',
            'Title' => 'required|string|max:200',
            'Description' => 'nullable|string|max:5000',
            'Assignee_Type' => 'nullable|in:TEAM,USER,VENDOR',
            'Team_ID' => 'nullable|required_if:Assignee_Type,TEAM|integer|min:1|exists:ga_team,ID',
            'User_Id' => 'nullable|required_if:Assignee_Type,USER|integer|min:1|exists:users,id',
            'Supplier_ID' => 'nullable|required_if:Assignee_Type,VENDOR|integer|min:1|exists:supplier,ID',
            'Planned_Start' => 'nullable|date',
            'Planned_End' => 'nullable|date|after_or_equal:Planned_Start',
            'Vendor_Cost' => 'nullable|numeric|min:0',
            'Cost_Center' => 'nullable|string|max:50',
            'Department' => 'nullable|string|max:100',
            'Submit_Approval' => 'nullable|boolean',
            'materials' => 'nullable|array',
            'costs' => 'nullable|array',
        ]);

        try {
            $wo = $this->workOrders->createFromTicket((int) $data['Ticket_ID'], $data, Auth::user());
            return response()->json([
                'status' => 'success',
                'message' => 'Work order berhasil dibuat.',
                'wo_id' => (int) $wo->ID,
                'wo_no' => $wo->WO_No,
            ]);
        } catch (\Throwable $exception) {
            Log::warning('GA work order create failed', ['message' => $exception->getMessage()]);
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 422);
        }
    }

    public function detail(Request $request)
    {
        abort_unless(can_menu('SI01010', 'view'), 403);

        $woId = (int) $request->query('ID', 0);
        $wo = $this->workOrders->syncApprovalStatus($woId);
        abort_if(!$wo, 404);

        $materials = $this->workOrders->materials($woId);
        $costs = $this->workOrders->costs($woId);
        $history = $this->workOrders->history($woId);
        $totalCost = $this->workOrders->totalCost($woId);
        $allowedTransitions = $this->workOrders->allowedTransitions(strtoupper((string) $wo->Status));

        return view('humanresources.ga.work-order.detail', compact('wo', 'materials', 'costs', 'history', 'totalCost', 'allowedTransitions'));
    }

    public function transition(Request $request)
    {
        abort_unless(can_menu('SI01010', 'edit'), 403);

        $data = $request->validate([
            'ID' => 'required|integer',
            'status' => 'required|string|max:30',
            'note' => 'nullable|string|max:5000',
        ]);

        try {
            $wo = $this->workOrders->transition((int) $data['ID'], $data['status'], $data, Auth::user());
            return response()->json(['status' => 'success', 'message' => 'Status work order diperbarui.', 'wo' => $wo]);
        } catch (\Throwable $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 422);
        }
    }

    public function submitApproval(Request $request)
    {
        abort_unless(can_menu('SI01010', 'approve'), 403);

        $data = $request->validate(['ID' => 'required|integer']);

        try {
            $approval = $this->workOrders->submitForApproval((int) $data['ID'], Auth::user());
            $wo = $this->workOrders->find((int) $data['ID']);
            return response()->json([
                'status' => 'success',
                'message' => 'Work order diajukan ke approval.',
                'approval_request_id' => (int) $approval->ID,
                'wo' => $wo,
            ]);
        } catch (\Throwable $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 422);
        }
    }

    public function syncApproval(Request $request)
    {
        abort_unless(can_menu('SI01010', 'view'), 403);

        $data = $request->validate(['ID' => 'required|integer']);

        try {
            $wo = $this->workOrders->syncApprovalStatus((int) $data['ID']);
            return response()->json(['status' => 'success', 'message' => 'Status approval disinkronkan.', 'wo' => $wo]);
        } catch (\Throwable $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 422);
        }
    }

    public function addCost(Request $request)
    {
        abort_unless(can_menu('SI01010', 'edit'), 403);

        $data = $request->validate([
            'ID' => 'required|integer',
            'Cost_Type' => 'required|string|max:30',
            'Description' => 'nullable|string|max:200',
            'Amount' => 'required|numeric|min:0',
            'Cost_Center' => 'nullable|string|max:50',
            'Department' => 'nullable|string|max:100',
        ]);

        try {
            $cost = $this->workOrders->addCost((int) $data['ID'], $data, Auth::user());
            return response()->json(['status' => 'success', 'message' => 'Biaya ditambahkan.', 'cost' => $cost]);
        } catch (\Throwable $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 422);
        }
    }
}
