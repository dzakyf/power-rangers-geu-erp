<?php

namespace App\Services\GA;

use App\Services\Approval\ApprovalWorkflowService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class WorkOrderService
{
    const STATUS_DRAFT = 'DRAFT';
    const STATUS_OPEN = 'OPEN';
    const STATUS_WAITING_APPROVAL = 'WAITING_APPROVAL';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_REJECTED = 'REJECTED';
    const STATUS_ASSIGNED = 'ASSIGNED';
    const STATUS_IN_PROGRESS = 'IN_PROGRESS';
    const STATUS_COMPLETED = 'COMPLETED';
    const STATUS_CANCELLED = 'CANCELLED';

    protected $workflow;
    protected $tickets;
    protected $notifications;

    public function __construct(ApprovalWorkflowService $workflow, TicketService $tickets, GaNotificationService $notifications)
    {
        $this->workflow = $workflow;
        $this->tickets = $tickets;
        $this->notifications = $notifications;
    }

    public function createFromTicket($ticketId, array $data, $user)
    {
        $ticketId = (int) $ticketId;
        $ticket = $this->tickets->find($ticketId);
        if (!$ticket) {
            throw new InvalidArgumentException('Tiket GA tidak ditemukan.');
        }
        if (in_array(strtoupper((string) $ticket->Ticket_Status), [TicketService::STATUS_CLOSED, TicketService::STATUS_REJECTED], true)) {
            throw new InvalidArgumentException('Tiket yang sudah CLOSED/REJECTED tidak dapat dibuatkan work order.');
        }

        $title = trim((string) ($data['Title'] ?? $ticket->Ticket_Subject));
        if ($title === '') {
            throw new InvalidArgumentException('Judul work order wajib diisi.');
        }

        $location = trim((string) ($data['Location'] ?? $ticket->Location));
        $company = trim((string) ($data['Company_ID'] ?? $ticket->Company_ID));
        if ($location === '' || $company === '') {
            throw new InvalidArgumentException('Location dan Company_ID work order wajib terisi.');
        }

        $now = Carbon::now();
        $woNo = $this->generateWoNo($location);

        $assigneeType = !empty($data['Assignee_Type']) ? strtoupper(trim((string) $data['Assignee_Type'])) : null;
        if ($assigneeType === null) {
            if (!empty($data['Team_ID'])) {
                $assigneeType = 'TEAM';
            } elseif (!empty($data['User_Id'])) {
                $assigneeType = 'USER';
            } elseif (!empty($data['Supplier_ID'])) {
                $assigneeType = 'VENDOR';
            }
        }
        $teamId = $assigneeType === 'TEAM' && !empty($data['Team_ID']) ? (int) $data['Team_ID'] : null;
        $userId = $assigneeType === 'USER' && !empty($data['User_Id']) ? (int) $data['User_Id'] : null;
        $supplierId = $assigneeType === 'VENDOR' && !empty($data['Supplier_ID']) ? (int) $data['Supplier_ID'] : null;

        $woId = DB::transaction(function () use ($ticketId, $ticket, $data, $user, $title, $location, $company, $now, $woNo, $assigneeType, $teamId, $userId, $supplierId) {
            $woId = DB::table('ga_work_order')->insertGetId([
                'WO_No' => $woNo,
                'Ticket_ID' => $ticketId,
                'Location' => $location,
                'Company_ID' => $company,
                'Title' => mb_substr($title, 0, 200),
                'Description' => (string) ($data['Description'] ?? $ticket->Ga_Description ?? ''),
                'Assignee_Type' => $assigneeType,
                'Team_ID' => $teamId,
                'User_Id' => $userId,
                'Supplier_ID' => $supplierId,
                'Planned_Start' => !empty($data['Planned_Start']) ? $data['Planned_Start'] : null,
                'Planned_End' => !empty($data['Planned_End']) ? $data['Planned_End'] : null,
                'Vendor_Cost' => !empty($data['Vendor_Cost']) ? (float) $data['Vendor_Cost'] : 0,
                'Cost_Center' => mb_substr((string) ($data['Cost_Center'] ?? $ticket->Cost_Center ?? ''), 0, 50),
                'Department' => mb_substr((string) ($data['Department'] ?? $ticket->Ga_Department ?? ''), 0, 100),
                'Status' => self::STATUS_DRAFT,
                'Created_By' => (string) $user->username,
                'Created_At' => $now,
            ]);

            $this->insertHistory($woId, null, self::STATUS_DRAFT, 'Work order dibuat.', (string) $user->username, $now);

            if (!empty($data['materials']) && is_array($data['materials'])) {
                foreach ($data['materials'] as $material) {
                    $this->insertMaterial($woId, $material);
                }
            }
            if (!empty($data['costs']) && is_array($data['costs'])) {
                foreach ($data['costs'] as $cost) {
                    $this->insertCost($woId, $cost, (string) $user->username, $now);
                }
            }

            // Ticket work starts when a work order is created from an assigned ticket.
            $ticketStatus = strtoupper((string) $ticket->Ticket_Status);
            if (in_array($ticketStatus, [TicketService::STATUS_ASSIGNED, TicketService::STATUS_TRIAGED, TicketService::STATUS_REOPENED], true)) {
                DB::table('ticket')->where('ID', $ticketId)->update(['Ticket_Status' => TicketService::STATUS_IN_PROGRESS]);
                DB::table('ga_ticket_status_history')->insert([
                    'Ticket_ID' => $ticketId,
                    'From_Status' => $ticketStatus,
                    'To_Status' => TicketService::STATUS_IN_PROGRESS,
                    'Note' => 'Work order ' . $woNo . ' dibuat.',
                    'Changed_By' => (string) $user->username,
                    'Changed_At' => $now,
                    'Created_At' => $now,
                ]);
            }

            if (!empty($data['Submit_Approval'])) {
                $this->submitForApproval($woId, $user);
            }

            return $woId;
        });

        $fresh = $this->find($woId);
        if ($fresh) {
            $metadata = ['WO_ID' => (int) $woId, 'WO_No' => $woNo, 'Ticket_ID' => $ticketId];
            if (!empty($fresh->User_Id)) {
                $this->notifications->notifyUser(
                    (int) $fresh->User_Id,
                    'Work order GA: ' . $woNo,
                    'Work order "' . $title . '" ditugaskan kepada Anda.',
                    'ga_work_order',
                    $metadata
                );
            }
            if (!empty($fresh->Team_ID)) {
                $this->notifications->notifyTeam(
                    (int) $fresh->Team_ID,
                    'Work order GA: ' . $woNo,
                    'Work order "' . $title . '" ditugaskan kepada tim Anda.',
                    'ga_work_order',
                    $metadata
                );
            }
        }

        return $fresh;
    }

    public function submitForApproval($woId, $user)
    {
        $woId = (int) $woId;

        return DB::transaction(function () use ($woId, $user) {
            $wo = DB::table('ga_work_order')->where('ID', $woId)->lockForUpdate()->first();
            if (!$wo) {
                throw new InvalidArgumentException('Work order tidak ditemukan.');
            }
            if (in_array(strtoupper((string) $wo->Status), [self::STATUS_WAITING_APPROVAL, self::STATUS_APPROVED, self::STATUS_COMPLETED, self::STATUS_CANCELLED], true)) {
                throw new InvalidArgumentException('Work order berstatus ' . $wo->Status . ' tidak dapat diajukan approval.');
            }

            $amount = $this->totalCost($woId);
            $context = [
                'Request_Type' => 'GA_WORK_ORDER',
                'Location' => trim((string) ($wo->Location ?: $user->Location)),
                'Company_ID' => trim((string) ($wo->Company_ID ?: $user->Company_ID)),
                'Department' => trim((string) ($wo->Department ?: $user->Department)),
                'Amount' => $amount,
            ];

            $request = $this->workflow->submit([
                'Request_No' => $wo->WO_No,
                'Description' => 'GA Work Order ' . $wo->WO_No . ' - ' . $wo->Title . ' (Rp ' . number_format($amount, 2, ',', '.') . ')',
            ], $context, (int) $user->id);

            $status = strtoupper((string) $request->Status) === 'APPROVED' ? self::STATUS_APPROVED : self::STATUS_WAITING_APPROVAL;
            $now = Carbon::now();
            DB::table('ga_work_order')->where('ID', $woId)->update([
                'Approval_Request_ID' => (int) $request->ID,
                'Status' => $status,
                'Updated_By' => (string) $user->username,
                'Updated_At' => $now,
            ]);
            $this->insertHistory($woId, (string) $wo->Status, $status, 'Diajukan ke approval engine.', (string) $user->username, $now);

            return $request;
        });
    }

    public function syncApprovalStatus($woId)
    {
        $woId = (int) $woId;
        $wo = DB::table('ga_work_order')->where('ID', $woId)->first();
        if (!$wo || empty($wo->Approval_Request_ID)) {
            return $this->find($woId);
        }

        $request = DB::table('approval_request')->where('ID', (int) $wo->Approval_Request_ID)->first();
        if (!$request) {
            return $this->find($woId);
        }

        $mapped = $this->mapApprovalStatus((string) $request->Status);
        if ($mapped && $mapped !== strtoupper((string) $wo->Status)) {
            $now = Carbon::now();
            DB::table('ga_work_order')->where('ID', $woId)->update([
                'Status' => $mapped,
                'Updated_At' => $now,
            ]);
            $this->insertHistory($woId, (string) $wo->Status, $mapped, 'Sinkronisasi status approval (' . $request->Status . ').', 'system', $now);
        }

        return $this->find($woId);
    }

    public function transition($woId, $toStatus, array $data, $user)
    {
        $woId = (int) $woId;
        $toStatus = strtoupper(trim((string) $toStatus));
        $note = trim((string) ($data['note'] ?? $data['Note'] ?? ''));

        $result = DB::transaction(function () use ($woId, $toStatus, $data, $user, $note) {
            $wo = DB::table('ga_work_order')->where('ID', $woId)->lockForUpdate()->first();
            if (!$wo) {
                throw new InvalidArgumentException('Work order tidak ditemukan.');
            }

            $from = strtoupper((string) $wo->Status);
            if (!in_array($toStatus, $this->allowedTransitions($from), true)) {
                throw new InvalidArgumentException('Transisi work order ' . $from . ' ke ' . $toStatus . ' tidak diizinkan.');
            }

            if ($toStatus === self::STATUS_COMPLETED && $note === '') {
                throw new InvalidArgumentException('Catatan hasil wajib diisi saat menyelesaikan work order.');
            }

            $now = Carbon::now();
            $updates = [
                'Status' => $toStatus,
                'Updated_By' => (string) $user->username,
                'Updated_At' => $now,
            ];
            if ($toStatus === self::STATUS_IN_PROGRESS && empty($wo->Actual_Start)) {
                $updates['Actual_Start'] = $now;
            }
            if ($toStatus === self::STATUS_COMPLETED) {
                $updates['Actual_End'] = $now;
                $updates['Result_Note'] = $note;
            }

            DB::table('ga_work_order')->where('ID', $woId)->update($updates);
            $this->insertHistory($woId, $from, $toStatus, $note !== '' ? $note : null, (string) $user->username, $now);

            $resolvedTicket = null;
            if ($toStatus === self::STATUS_COMPLETED && !empty($wo->Ticket_ID)) {
                $ticket = DB::table('ticket')->where('ID', (int) $wo->Ticket_ID)->lockForUpdate()->first();
                if ($ticket && !in_array(strtoupper((string) $ticket->Ticket_Status), [TicketService::STATUS_CLOSED, TicketService::STATUS_REJECTED, TicketService::STATUS_RESOLVED], true)) {
                    DB::table('ticket')->where('ID', (int) $ticket->ID)->update([
                        'Ticket_Status' => TicketService::STATUS_RESOLVED,
                        'Completion_Note' => mb_substr($note, 0, 150),
                    ]);
                    DB::table('ga_ticket')->where('Ticket_ID', (int) $ticket->ID)->update([
                        'Resolved_At' => $now,
                        'Updated_At' => $now,
                    ]);
                    DB::table('ga_ticket_status_history')->insert([
                        'Ticket_ID' => (int) $ticket->ID,
                        'From_Status' => $ticket->Ticket_Status,
                        'To_Status' => TicketService::STATUS_RESOLVED,
                        'Note' => 'Work order ' . $wo->WO_No . ' selesai: ' . $note,
                        'Changed_By' => (string) $user->username,
                        'Changed_At' => $now,
                        'Created_At' => $now,
                    ]);
                    $resolvedTicket = (object) [
                        'ID' => (int) $ticket->ID,
                        'Ticket_No' => (string) $ticket->Ticket_No,
                        'Ticket_Subject' => (string) $ticket->Ticket_Subject,
                    ];
                }
            }

            return [
                'wo' => $this->find($woId),
                'resolved_ticket' => $resolvedTicket,
            ];
        });

        if (!empty($result['resolved_ticket'])) {
            $this->notifyTicketResolvedByWorkOrder($result['wo'], $result['resolved_ticket'], $note, $user);
        }

        return $result['wo'];
    }

    /**
     * Beri tahu reporter dan tim GA bahwa work order selesai dan tiket menunggu closing.
     */
    protected function notifyTicketResolvedByWorkOrder($wo, $ticket, $note, $user)
    {
        if (!$wo) {
            return;
        }

        $detail = DB::table('ga_ticket')->where('Ticket_ID', (int) $ticket->ID)->first();

        $userIds = [];
        if ($detail && !empty($detail->Reporter_User_Id)) {
            $userIds[] = (int) $detail->Reporter_User_Id;
        }
        if ($detail && !empty($detail->Assignee_User_Id)) {
            $userIds[] = (int) $detail->Assignee_User_Id;
        }
        if (!empty($wo->User_Id)) {
            $userIds[] = (int) $wo->User_Id;
        }

        $teamIds = [];
        if ($detail && !empty($detail->Assignee_Team_ID)) {
            $teamIds[] = (int) $detail->Assignee_Team_ID;
        }
        if (!empty($wo->Team_ID)) {
            $teamIds[] = (int) $wo->Team_ID;
        }
        if ($detail && !empty($detail->Category_ID)) {
            $defaultTeamId = DB::table('ga_ticket_category')
                ->where('ID', (int) $detail->Category_ID)
                ->value('Default_Team_ID');
            if (!empty($defaultTeamId)) {
                $teamIds[] = (int) $defaultTeamId;
            }
        }

        $teamIds = array_values(array_unique(array_filter($teamIds)));
        if (!empty($teamIds)) {
            $teamUserIds = DB::table('ga_team_member')
                ->whereIn('Team_ID', $teamIds)
                ->where('Status', 'ACTIVE')
                ->whereNotNull('User_Id')
                ->pluck('User_Id')
                ->all();
            $userIds = array_merge($userIds, $teamUserIds);
        }

        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));
        if (empty($userIds)) {
            return;
        }

        $actor = !empty($user->username) ? (string) $user->username : 'sistem';
        $title = 'Work order ' . $wo->WO_No . ' selesai - tiket ' . $ticket->Ticket_No . ' menunggu closing';
        $content = 'Work order "' . $wo->Title . '" untuk tiket ' . $ticket->Ticket_No
            . ' telah diselesaikan oleh ' . $actor . '. Tiket otomatis berstatus RESOLVED. '
            . 'Tim GA perlu menutup tiket melalui Detail Tiket (RESOLVED -> CLOSED).'
            . ($note !== '' ? ' Catatan hasil: ' . $note : '');

        $this->notifications->notifyUsers($userIds, $title, $content, 'ga_work_order', [
            'WO_ID' => (int) $wo->ID,
            'WO_No' => $wo->WO_No,
            'Ticket_ID' => (int) $ticket->ID,
            'Ticket_No' => $ticket->Ticket_No,
            'Status' => TicketService::STATUS_RESOLVED,
            'Next_Action' => 'CLOSE_TICKET',
        ]);
    }

    public function addCost($woId, array $data, $user)
    {
        $woId = (int) $woId;
        $wo = DB::table('ga_work_order')->where('ID', $woId)->first();
        if (!$wo) {
            throw new InvalidArgumentException('Work order tidak ditemukan.');
        }
        if (in_array(strtoupper((string) $wo->Status), [self::STATUS_COMPLETED, self::STATUS_CANCELLED], true)) {
            throw new InvalidArgumentException('Work order yang sudah selesai/dibatalkan tidak dapat ditambah biaya.');
        }

        $now = Carbon::now();
        $costId = $this->insertCost($woId, $data, (string) $user->username, $now);
        $this->insertHistory($woId, (string) $wo->Status, (string) $wo->Status, 'Biaya ditambahkan.', (string) $user->username, $now);

        return DB::table('ga_work_order_cost')->where('ID', $costId)->first();
    }

    public function addMaterial($woId, array $data)
    {
        $woId = (int) $woId;
        $wo = DB::table('ga_work_order')->where('ID', $woId)->first();
        if (!$wo) {
            throw new InvalidArgumentException('Work order tidak ditemukan.');
        }

        $materialId = $this->insertMaterial($woId, $data);

        return DB::table('ga_work_order_material')->where('ID', $materialId)->first();
    }

    public function find($woId)
    {
        return DB::table('ga_work_order as w')
            ->leftJoin('ticket as t', 't.ID', '=', 'w.Ticket_ID')
            ->leftJoin('ga_team as team', 'team.ID', '=', 'w.Team_ID')
            ->leftJoin('users as assignee', 'assignee.id', '=', 'w.User_Id')
            ->leftJoin('supplier as vendor', 'vendor.ID', '=', 'w.Supplier_ID')
            ->where('w.ID', (int) $woId)
            ->select([
                'w.*',
                't.Ticket_No',
                't.Ticket_Subject',
                't.Ticket_Status',
                'team.Name as Team_Name',
                'assignee.Name as Assignee_Name',
                'vendor.Company_Name as Vendor_Name',
            ])
            ->first();
    }

    public function query(array $filters, $user)
    {
        $query = DB::table('ga_work_order as w')
            ->leftJoin('ticket as t', 't.ID', '=', 'w.Ticket_ID')
            ->leftJoin('ga_team as team', 'team.ID', '=', 'w.Team_ID')
            ->leftJoin('users as assignee', 'assignee.id', '=', 'w.User_Id')
            ->leftJoin('supplier as vendor', 'vendor.ID', '=', 'w.Supplier_ID')
            ->select([
                'w.*',
                't.Ticket_No',
                't.Ticket_Subject',
                't.Ticket_Status',
                'team.Name as Team_Name',
                'assignee.Name as Assignee_Name',
                'vendor.Company_Name as Vendor_Name',
            ])
            ->orderBy('w.Created_At', 'desc')
            ->orderBy('w.ID', 'desc');

        if (!empty($filters['status'])) {
            $query->where('w.Status', strtoupper(trim((string) $filters['status'])));
        }
        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('w.WO_No', 'like', '%' . $search . '%')
                    ->orWhere('w.Title', 'like', '%' . $search . '%')
                    ->orWhere('t.Ticket_No', 'like', '%' . $search . '%');
            });
        }
        if (!empty($filters['ticket_id'])) {
            $query->where('w.Ticket_ID', (int) $filters['ticket_id']);
        }
        if (!empty($filters['location'])) {
            $query->where('w.Location', $filters['location']);
        }
        if (!empty($filters['company_id'])) {
            $query->where('w.Company_ID', $filters['company_id']);
        }
        if (!empty($filters['assignee']) && strtolower((string) $filters['assignee']) === 'me') {
            $teamIds = DB::table('ga_team_member')->where('User_Id', (int) $user->id)->where('Status', 'ACTIVE')->pluck('Team_ID')->all();
            $query->where(function ($q) use ($user, $teamIds) {
                $q->where('w.User_Id', (int) $user->id);
                if (!empty($teamIds)) {
                    $q->orWhereIn('w.Team_ID', $teamIds);
                }
            });
        }

        return $query;
    }

    public function materials($woId)
    {
        return DB::table('ga_work_order_material')->where('WO_ID', (int) $woId)->orderBy('ID')->get();
    }

    public function costs($woId)
    {
        return DB::table('ga_work_order_cost')->where('WO_ID', (int) $woId)->orderBy('ID')->get();
    }

    public function history($woId)
    {
        return DB::table('ga_work_order_status_history')->where('WO_ID', (int) $woId)->orderBy('Changed_At')->orderBy('ID')->get();
    }

    public function totalCost($woId)
    {
        $wo = DB::table('ga_work_order')->where('ID', (int) $woId)->first();
        if (!$wo) {
            return 0.0;
        }

        $cost = (float) $wo->Vendor_Cost;
        $cost += (float) DB::table('ga_work_order_cost')->where('WO_ID', (int) $woId)->sum('Amount');
        $cost += (float) DB::table('ga_work_order_material')->where('WO_ID', (int) $woId)->sum('Total_Cost');

        return round($cost, 2);
    }

    /**
     * Hitung total biaya untuk banyak work order sekaligus (menghindari N+1).
     *
     * @param array $woIds
     * @return array [WO_ID => total]
     */
    public function totalCostMap(array $woIds)
    {
        $woIds = array_values(array_unique(array_filter(array_map('intval', $woIds))));
        if (empty($woIds)) {
            return [];
        }

        $costs = DB::table('ga_work_order_cost')
            ->whereIn('WO_ID', $woIds)
            ->groupBy('WO_ID')
            ->select('WO_ID', DB::raw('SUM(Amount) as Total'))
            ->pluck('Total', 'WO_ID');

        $materials = DB::table('ga_work_order_material')
            ->whereIn('WO_ID', $woIds)
            ->groupBy('WO_ID')
            ->select('WO_ID', DB::raw('SUM(Total_Cost) as Total'))
            ->pluck('Total', 'WO_ID');

        $map = [];
        foreach (DB::table('ga_work_order')->whereIn('ID', $woIds)->get(['ID', 'Vendor_Cost']) as $row) {
            $id = (int) $row->ID;
            $map[$id] = round(
                (float) $row->Vendor_Cost
                + (float) (isset($costs[$id]) ? $costs[$id] : 0)
                + (float) (isset($materials[$id]) ? $materials[$id] : 0),
                2
            );
        }

        return $map;
    }

    public function generateWoNo($location)
    {
        $prefix = 'WO/GA/' . preg_replace('/[^A-Za-z0-9\-]/', '', (string) $location) . '/' . Carbon::now()->format('YmdHis');
        for ($i = 0; $i < 20; $i++) {
            $candidate = $prefix . '-' . strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 6));
            if (!DB::table('ga_work_order')->where('WO_No', $candidate)->exists()) {
                return $candidate;
            }
        }

        throw new \RuntimeException('Gagal membuat nomor work order unik.');
    }

    public function allowedTransitions($status)
    {
        $map = [
            self::STATUS_DRAFT => [self::STATUS_OPEN, self::STATUS_WAITING_APPROVAL, self::STATUS_CANCELLED],
            self::STATUS_OPEN => [self::STATUS_ASSIGNED, self::STATUS_IN_PROGRESS, self::STATUS_CANCELLED],
            self::STATUS_WAITING_APPROVAL => [self::STATUS_OPEN, self::STATUS_CANCELLED],
            self::STATUS_APPROVED => [self::STATUS_OPEN, self::STATUS_ASSIGNED, self::STATUS_IN_PROGRESS, self::STATUS_CANCELLED],
            self::STATUS_REJECTED => [self::STATUS_DRAFT, self::STATUS_CANCELLED],
            self::STATUS_ASSIGNED => [self::STATUS_IN_PROGRESS, self::STATUS_CANCELLED],
            self::STATUS_IN_PROGRESS => [self::STATUS_COMPLETED, self::STATUS_CANCELLED],
            self::STATUS_COMPLETED => [],
            self::STATUS_CANCELLED => [],
        ];

        return $map[$status] ?? [];
    }

    protected function mapApprovalStatus($status)
    {
        $status = strtoupper(trim((string) $status));
        if ($status === 'APPROVED') {
            return self::STATUS_APPROVED;
        }
        if (in_array($status, ['REJECTED', 'RETURNED'], true)) {
            return self::STATUS_REJECTED;
        }
        if ($status === 'CANCELLED') {
            return self::STATUS_CANCELLED;
        }
        if (in_array($status, ['NEED_APPROVAL', 'IN_PROGRESS'], true)) {
            return self::STATUS_WAITING_APPROVAL;
        }

        return null;
    }

    protected function insertMaterial($woId, array $material)
    {
        $qty = !empty($material['Qty']) ? (float) $material['Qty'] : 0;
        $unitCost = !empty($material['Unit_Cost']) ? (float) $material['Unit_Cost'] : 0;
        $total = !empty($material['Total_Cost']) ? (float) $material['Total_Cost'] : ($qty * $unitCost);

        return DB::table('ga_work_order_material')->insertGetId([
            'WO_ID' => (int) $woId,
            'Stock_Request_No' => !empty($material['Stock_Request_No']) ? mb_substr((string) $material['Stock_Request_No'], 0, 60) : null,
            'Product_Code' => !empty($material['Product_Code']) ? mb_substr((string) $material['Product_Code'], 0, 60) : null,
            'Product_Desc' => !empty($material['Product_Desc']) ? mb_substr((string) $material['Product_Desc'], 0, 200) : null,
            'Qty' => $qty,
            'Unit' => !empty($material['Unit']) ? mb_substr((string) $material['Unit'], 0, 25) : null,
            'Unit_Cost' => $unitCost,
            'Total_Cost' => $total,
            'Created_At' => Carbon::now(),
        ]);
    }

    protected function insertCost($woId, array $cost, $createdBy, Carbon $now)
    {
        return DB::table('ga_work_order_cost')->insertGetId([
            'WO_ID' => (int) $woId,
            'Cost_Type' => mb_substr((string) ($cost['Cost_Type'] ?? 'OTHER'), 0, 30),
            'Description' => !empty($cost['Description']) ? mb_substr((string) $cost['Description'], 0, 200) : null,
            'Amount' => !empty($cost['Amount']) ? (float) $cost['Amount'] : 0,
            'Cost_Center' => !empty($cost['Cost_Center']) ? mb_substr((string) $cost['Cost_Center'], 0, 50) : null,
            'Department' => !empty($cost['Department']) ? mb_substr((string) $cost['Department'], 0, 100) : null,
            'Source_Type' => !empty($cost['Source_Type']) ? mb_substr((string) $cost['Source_Type'], 0, 30) : null,
            'Source_ID' => !empty($cost['Source_ID']) ? mb_substr((string) $cost['Source_ID'], 0, 60) : null,
            'Created_By' => $createdBy,
            'Created_At' => $now,
        ]);
    }

    protected function insertHistory($woId, $from, $to, $note, $changedBy, Carbon $now)
    {
        DB::table('ga_work_order_status_history')->insert([
            'WO_ID' => (int) $woId,
            'From_Status' => $from,
            'To_Status' => $to,
            'Note' => $note,
            'Changed_By' => $changedBy,
            'Changed_At' => $now,
            'Created_At' => $now,
        ]);
    }
}
