<?php

namespace App\Http\Controllers\HCGA\GA;

use App\Http\Controllers\Controller;
use App\Services\GA\GaPaymentVoucherService;
use App\Services\GA\GaTicketApprovalService;
use App\Services\GA\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TicketController extends Controller
{
    use GaXml;

    protected $tickets;

    /** @var GaTicketApprovalService */
    protected $approvals;

    /** @var GaPaymentVoucherService */
    protected $vouchers;

    public function __construct(
        TicketService $tickets,
        GaTicketApprovalService $approvals,
        GaPaymentVoucherService $vouchers
    ) {
        $this->tickets = $tickets;
        $this->approvals = $approvals;
        $this->vouchers = $vouchers;
    }

    public function index()
    {
        abort_unless(can_menu('SI01013', 'view'), 403);

        $categories = $this->categoryOptions();
        $teams = $this->teamOptions();

        return view('humanresources.ga.ticket.index', compact('categories', 'teams'));
    }

    public function data(Request $request)
    {
        abort_unless(can_menu('SI01013', 'view'), 403);

        $user = Auth::user();
        $filters = $request->only(['status', 'priority', 'category_id', 'workflow_mode', 'search', 'date_from', 'date_to', 'location', 'company_id', 'assignee']);
        $filters['domain_type'] = TicketService::DOMAIN_GA;
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
            $modeLabel = strtoupper((string) ($row->Workflow_Mode ?? TicketService::MODE_OPERATIONAL)) === TicketService::MODE_WORK_ORDER_VOUCHER
                ? 'Order & Voucher'
                : 'Operasional';
            $nominal = !empty($row->Verified_Amount) && (float) $row->Verified_Amount > 0
                ? (float) $row->Verified_Amount
                : (float) ($row->Estimated_Amount ?? 0);
            $nominalText = $nominal > 0 ? ('Rp ' . number_format($nominal, 0, ',', '.')) : '-';
            $assignee = $row->Assignee_Name ?: ($row->Team_Name ?: ($row->Preferred_Vendor_Name ?: '-'));
            echo '<row id="' . (int) $row->ID . '">';
            echo '<cell>' . $no . '</cell>';
            echo '<cell>' . $this->xml($row->Ticket_No) . '</cell>';
            echo '<cell>' . $this->xml($modeLabel) . '</cell>';
            echo '<cell>' . $this->xml($row->Ticket_Subject) . '</cell>';
            echo '<cell>' . $this->xml($row->Category_Name ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($row->Ga_Priority ?: $row->Priority) . '</cell>';
            echo '<cell>' . $this->xml($status) . '</cell>';
            echo '<cell>' . $this->xml($nominalText) . '</cell>';
            echo '<cell>' . $this->xml($row->Voucher_No ?: '-') . '</cell>';
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
        abort_unless(can_menu('SI01013', 'create'), 403);

        $categories = $this->categoryOptions();
        $subcategories = $this->subcategoryOptions();
        $teams = $this->teamOptions();
        $users = $this->userOptions();
        $vendors = $this->vendorOptions();
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

        return view($view, compact('categories', 'subcategories', 'teams', 'users', 'vendors', 'qrPoints', 'qrPoint', 'qrPointId'));
    }

    public function store(Request $request)
    {
        abort_unless(can_menu('SI01013', 'create'), 403);

        $data = $request->validate([
            'Ticket_Subject'       => 'required|string|max:250',
            'Description'          => 'required|string|max:5000',
            'Category_ID'          => 'nullable|integer',
            'Subcategory_ID'       => 'nullable|integer',
            'Priority'             => 'nullable|in:LOW,NORMAL,HIGH,URGENT',
            'Source'               => 'nullable|in:FORM,QR,ON_BEHALF',
            'Workflow_Mode'        => 'nullable|in:OPERATIONAL,WORK_ORDER_VOUCHER',
            'Estimated_Amount'     => 'nullable|numeric|min:0',
            'Preferred_Vendor_ID'  => 'nullable|integer',
            'On_Behalf_Of_User_Id' => 'nullable|integer',
            'QR_Point_ID'          => 'nullable|integer',
            'Reporter_Phone'       => 'nullable|string|max:50',
            'Cost_Center'          => 'nullable|string|max:50',
            'Department'           => 'nullable|string|max:100',
            'Assignee_User_Id'     => 'nullable|integer',
            'Location'             => 'nullable|string|max:50',
            'Company_ID'           => 'nullable|string|max:50',
            'attachments'          => 'nullable|array|max:5',
            'attachments.*'        => 'file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
        ]);

        try {
            $mode = strtoupper(trim((string) ($data['Workflow_Mode'] ?? TicketService::MODE_OPERATIONAL)));
            $data['Domain_Type'] = TicketService::DOMAIN_GA;
            $data['Workflow_Mode'] = $mode;
            if ($mode === TicketService::MODE_WORK_ORDER_VOUCHER) {
                $data['Suppress_Create_Notification'] = true;
            }

            $user = Auth::user();
            $ticket = $this->tickets->create($data, $user);

            $files = $request->file('attachments', []);
            if (!is_array($files)) {
                $files = $files ? [$files] : [];
            }
            if (!empty($files)) {
                $this->tickets->attachFiles((int) $ticket->ID, $files, (string) $user->username);
            }

            if ($mode === TicketService::MODE_WORK_ORDER_VOUCHER) {
                $ticket = $this->approvals->submitDeptHeadApproval((int) $ticket->ID, $user);
            }

            return response()->json([
                'status'        => 'success',
                'message'       => $mode === TicketService::MODE_WORK_ORDER_VOUCHER
                    ? 'Order Pekerjaan GA berhasil dibuat dan diajukan ke Approval Dept Head.'
                    : 'Tiket berhasil dibuat.',
                'ticket_id'     => (int) $ticket->ID,
                'ticket_no'     => $ticket->Ticket_No,
                'ticket_status' => $ticket->Ticket_Status,
            ]);
        } catch (\Throwable $exception) {
            Log::warning('GA ticket create failed', ['message' => $exception->getMessage()]);
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 422);
        }
    }

    public function detail(Request $request)
    {
        abort_unless(can_menu('SI01013', 'view'), 403);

        $ticketId = (int) $request->query('ID', 0);
        $ticket = $this->tickets->find($ticketId);
        abort_if(!$ticket || strtoupper((string) ($ticket->Domain_Type ?? 'GA')) === TicketService::DOMAIN_IT, 404);

        $mode = strtoupper((string) ($ticket->Workflow_Mode ?? TicketService::MODE_OPERATIONAL));
        $status = strtoupper((string) $ticket->Ticket_Status);
        if ($mode === TicketService::MODE_WORK_ORDER_VOUCHER) {
            if ($status === TicketService::STATUS_WAITING_DEPT_HEAD && !empty($ticket->Dept_Approval_Request_ID)) {
                $ticket = $this->approvals->syncDeptHeadApproval((int) $ticket->Dept_Approval_Request_ID) ?: $ticket;
            } elseif ($status === TicketService::STATUS_WAITING_BUDGET_APPROVAL && !empty($ticket->Budget_Approval_Request_ID)) {
                $ticket = $this->approvals->syncBudgetApproval((int) $ticket->Budget_Approval_Request_ID) ?: $ticket;
            }
            $status = strtoupper((string) $ticket->Ticket_Status);
        }

        $history = $this->tickets->history($ticketId);
        $comments = $this->tickets->comments($ticketId);
        $assignments = $this->tickets->assignments($ticketId);
        $attachments = $this->tickets->attachments($ticketId);
        $workOrders = $this->tickets->workOrders($ticketId);
        $sla = $this->tickets->slaSummary($ticket);
        $progressSteps = $this->tickets->progressSteps($ticket);
        $approvalTimelines = $this->approvals->getApprovalTimelines($ticket);
        $voucher = $this->vouchers->findByTicketId($ticketId);
        $tierPreview = $this->approvals->tierPreview((float) ($ticket->Verified_Amount ?: $ticket->Estimated_Amount ?: 0));
        $allowedTransitions = ($mode === TicketService::MODE_WORK_ORDER_VOUCHER)
            ? []
            : $this->tickets->allowedTransitions($status);
        $teams = $this->teamOptions();
        $users = $this->userOptions();
        $categories = $this->categoryOptions();
        $subcategories = $this->subcategoryOptions();
        $vendors = $this->vendorOptions();
        $partial = $request->query('partial');
        $isPartial = $request->ajax() || (is_scalar($partial) && (string) $partial === '1');
        $view = $isPartial
            ? 'humanresources.ga.ticket.detail'
            : 'humanresources.ga.ticket.detail-page';

        return view($view, compact(
            'ticket',
            'history',
            'comments',
            'assignments',
            'attachments',
            'workOrders',
            'sla',
            'progressSteps',
            'approvalTimelines',
            'voucher',
            'tierPreview',
            'allowedTransitions',
            'teams',
            'users',
            'categories',
            'subcategories',
            'vendors'
        ));
    }

    public function triage(Request $request)
    {
        abort_unless(can_menu('SI01013', 'edit'), 403);

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
        abort_unless(can_menu('SI01013', 'edit'), 403);

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
        abort_unless(can_menu('SI01013', 'edit'), 403);

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
        abort_unless(can_menu('SI01013', 'edit'), 403);

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
        abort_unless(can_menu('SI01013', 'approve'), 403);

        try {
            $count = $this->tickets->scanSla();
            return response()->json(['status' => 'success', 'message' => $count . ' event SLA dibuat.', 'count' => $count]);
        } catch (\Throwable $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 422);
        }
    }

    public function categoryOptionsJson()
    {
        abort_unless(can_menu('SI01013', 'view'), 403);

        return response()->json([
            'categories' => $this->categoryOptions(),
            'subcategories' => $this->subcategoryOptions(),
            'teams' => $this->teamOptions(),
            'users' => $this->userOptions(),
            'vendors' => $this->vendorOptions(),
        ]);
    }

    public function verifyGa(Request $request)
    {
        abort_unless(can_menu('SI01013', 'edit') || can_menu('SI01013', 'approve'), 403);

        $data = $request->validate([
            'ID'                   => 'required|integer',
            'Verified_Amount'      => 'required|numeric|min:1',
            'Preferred_Vendor_ID'  => 'nullable|integer',
            'GA_Verification_Note' => 'nullable|string|max:2000',
        ]);

        try {
            $ticket = $this->approvals->verifyAndSubmitBudgetApproval((int) $data['ID'], $data, Auth::user());

            return response()->json([
                'status'        => 'success',
                'message'       => 'Verifikasi GA berhasil disimpan dan dikirim ke approval biaya.',
                'ticket_id'     => (int) $ticket->ID,
                'ticket_status' => $ticket->Ticket_Status,
            ]);
        } catch (\Throwable $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 422);
        }
    }

    public function resubmitDeptHead(Request $request)
    {
        abort_unless(can_menu('SI01013', 'create') || can_menu('SI01013', 'edit'), 403);

        $data = $request->validate([
            'ID'                  => 'required|integer',
            'Ticket_Subject'      => 'nullable|string|max:250',
            'Description'         => 'nullable|string|max:5000',
            'Estimated_Amount'    => 'nullable|numeric|min:0',
            'Preferred_Vendor_ID' => 'nullable|integer',
        ]);

        try {
            $ticket = $this->approvals->resubmitDeptHead((int) $data['ID'], $data, Auth::user());

            return response()->json([
                'status'        => 'success',
                'message'       => 'Order pekerjaan diajukan ulang ke Dept Head.',
                'ticket_id'     => (int) $ticket->ID,
                'ticket_status' => $ticket->Ticket_Status,
            ]);
        } catch (\Throwable $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 422);
        }
    }

    public function syncApproval(Request $request)
    {
        abort_unless(can_menu('SI01013', 'view'), 403);

        $data = $request->validate([
            'ID' => 'required|integer',
        ]);

        $ticketId = (int) $data['ID'];
        $ticket = $this->tickets->find($ticketId);
        abort_if(!$ticket || strtoupper((string) ($ticket->Domain_Type ?? 'GA')) === TicketService::DOMAIN_IT, 404);

        try {
            $status = strtoupper((string) $ticket->Ticket_Status);
            if ($status === TicketService::STATUS_WAITING_DEPT_HEAD && !empty($ticket->Dept_Approval_Request_ID)) {
                $ticket = $this->approvals->syncDeptHeadApproval((int) $ticket->Dept_Approval_Request_ID) ?: $ticket;
            } elseif ($status === TicketService::STATUS_WAITING_BUDGET_APPROVAL && !empty($ticket->Budget_Approval_Request_ID)) {
                $ticket = $this->approvals->syncBudgetApproval((int) $ticket->Budget_Approval_Request_ID) ?: $ticket;
            } elseif (!empty($ticket->Budget_Approval_Request_ID)) {
                $ticket = $this->approvals->syncBudgetApproval((int) $ticket->Budget_Approval_Request_ID) ?: $ticket;
            } elseif (!empty($ticket->Dept_Approval_Request_ID)) {
                $ticket = $this->approvals->syncDeptHeadApproval((int) $ticket->Dept_Approval_Request_ID) ?: $ticket;
            }

            return response()->json([
                'status'        => 'success',
                'message'       => 'Status approval disinkronkan.',
                'ticket_status' => $ticket->Ticket_Status,
            ]);
        } catch (\Throwable $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 422);
        }
    }

    public function createVoucher(Request $request)
    {
        abort_unless(can_menu('SI01013', 'edit') || can_menu('SI01013', 'approve'), 403);

        $data = $request->validate([
            'ID'                => 'required|integer',
            'Payee_Type'        => 'nullable|in:VENDOR,INTERNAL,STAFF,OTHER',
            'Supplier_ID'       => 'nullable|integer',
            'Payee_Name'        => 'required|string|max:150',
            'Bank_Name'         => 'nullable|string|max:100',
            'Bank_Account_No'   => 'nullable|string|max:80',
            'Bank_Account_Name' => 'nullable|string|max:150',
            'Payment_Method'    => 'nullable|in:TRANSFER,CASH,GIRO',
            'Amount'            => 'required|numeric|min:1',
            'Due_Date'          => 'nullable|date',
            'Description'       => 'nullable|string|max:2000',
            'Work_Order_ID'     => 'nullable|integer',
            'attachment'        => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx',
        ]);

        try {
            $voucher = $this->vouchers->createVoucher(
                (int) $data['ID'],
                $data,
                Auth::user(),
                $request->file('attachment')
            );

            return response()->json([
                'status'     => 'success',
                'message'    => 'Voucher Pembayaran ' . $voucher->Voucher_No . ' berhasil dibuat dan diteruskan ke Finance.',
                'voucher_id' => (int) $voucher->ID,
                'voucher_no' => $voucher->Voucher_No,
            ]);
        } catch (\Throwable $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 422);
        }
    }

    public function printVoucher(Request $request)
    {
        $user = Auth::user();
        $isFinanceUser = $user && (
            strtoupper((string) ($user->Priv_ID ?? '')) === 'ADMIN'
            || strtolower((string) ($user->username ?? '')) === 'admin'
            || can_menu('SI01160', 'view')
            || in_array(strtoupper((string) ($user->Department ?? '')), ['FIN', 'FINANCE', 'FAT', 'FA'], true)
        );
        abort_unless(can_menu('SI01013', 'view') || can_menu('SI01014', 'view') || $isFinanceUser, 403);

        $voucherId = (int) $request->query('voucher_id', $request->query('id', 0));
        $ticketId = (int) $request->query('ticket_id', 0);

        $voucher = $voucherId > 0
            ? $this->vouchers->findById($voucherId)
            : ($ticketId > 0 ? $this->vouchers->findByTicketId($ticketId) : null);

        abort_if(!$voucher, 404);

        $ticket = $this->tickets->find((int) $voucher->Ticket_ID);
        abort_if(!$ticket, 404);

        $timelines = $this->approvals->getApprovalTimelines($ticket);

        return view('humanresources.ga.ticket.voucher-print', compact('voucher', 'ticket', 'timelines'));
    }

    public function downloadAttachment(Request $request)
    {
        abort_unless(can_menu('SI01013', 'view'), 403);

        $attId = (int) $request->query('attachment_id', 0);
        $att = $this->tickets->findAttachment($attId);
        abort_if(!$att, 404);

        $ticket = $this->tickets->find((int) $att->Ticket_ID);
        abort_if(!$ticket || strtoupper((string) ($ticket->Domain_Type ?? 'GA')) === TicketService::DOMAIN_IT, 404);

        $fullPath = storage_path('app/' . ltrim((string) $att->File_Path, '/\\'));
        abort_if(!is_file($fullPath), 404);

        return response()->download($fullPath, $att->File_Name);
    }

    protected function categoryOptions()
    {
        $user = Auth::user();

        return DB::table('ga_ticket_category')
            ->where('Status', 'ACTIVE')
            ->whereNull('Parent_ID')
            ->where(function ($q) {
                $q->whereNull('Code')->orWhere('Code', 'not like', 'IT-%');
            })
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
            ->where(function ($q) {
                $q->whereNull('Code')->orWhere('Code', 'not like', 'IT-%');
            })
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
            ->where(function ($q) {
                $q->whereNull('Code')->orWhere('Code', 'not like', 'IT-%');
            })
            ->where(function ($q) use ($user) {
                $q->whereNull('Location')->orWhere('Location', $user->Location);
            })
            ->where(function ($q) use ($user) {
                $q->whereNull('Company_ID')->orWhere('Company_ID', $user->Company_ID);
            })
            ->orderBy('Name')
            ->get(['ID', 'Code', 'Name', 'Team_Type']);
    }

    protected function vendorOptions()
    {
        return DB::table('supplier')
            ->where('Status', 'ACTIVE')
            ->orderBy('Company_Name')
            ->limit(500)
            ->get(['ID', 'Company_Name', 'Bank_Rupiah', 'Bank_Rupiah_Account', 'Bank_Rupiah_Name']);
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
