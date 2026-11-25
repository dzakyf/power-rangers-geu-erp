<?php

namespace App\Services\GA;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;

class TicketService
{
    const DOMAIN_GA = 'GA';
    const DOMAIN_IT = 'IT';

    const MODE_OPERATIONAL = 'OPERATIONAL';
    const MODE_WORK_ORDER_VOUCHER = 'WORK_ORDER_VOUCHER';
    const MODE_IT_SUPPORT = 'IT_SUPPORT';
    const MODE_IT_INCIDENT = 'IT_INCIDENT';
    const MODE_IT_DEVICE_REQUEST = 'IT_DEVICE_REQUEST';

    const ACTION_PREVENTIVE = 'PREVENTIVE';
    const ACTION_CORRECTIVE = 'CORRECTIVE';

    const STATUS_OPEN = 'OPEN';
    const STATUS_TRIAGED = 'TRIAGED';
    const STATUS_ASSIGNED = 'ASSIGNED';
    const STATUS_IN_PROGRESS = 'IN_PROGRESS';
    const STATUS_ON_HOLD = 'ON_HOLD';
    const STATUS_RESOLVED = 'RESOLVED';
    const STATUS_CLOSED = 'CLOSED';
    const STATUS_REJECTED = 'REJECTED';
    const STATUS_REOPENED = 'REOPENED';

    // Status alur IT Support & Helpdesk
    const STATUS_TROUBLESHOOTING = 'TROUBLESHOOTING';
    const STATUS_ESCALATED = 'ESCALATED';
    const STATUS_USER_CONFIRMED = 'USER_CONFIRMED';
    const STATUS_VALIDATED = 'VALIDATED';
    const STATUS_ROOT_CAUSE_ANALYSIS = 'ROOT_CAUSE_ANALYSIS';
    const STATUS_RECOVERY = 'RECOVERY';
    const STATUS_TESTING = 'TESTING';
    const STATUS_SYSTEM_NORMAL = 'SYSTEM_NORMAL';
    const STATUS_WAITING_MANAGER_APPROVAL = 'WAITING_MANAGER_APPROVAL';
    const STATUS_DEVICE_PREPARATION = 'DEVICE_PREPARATION';
    const STATUS_ASSIGNED_TO_USER = 'ASSIGNED_TO_USER';
    const STATUS_HANDOVER = 'HANDOVER';

    // Status alur GA Order Pekerjaan & Voucher Pembayaran
    const STATUS_WAITING_DEPT_HEAD = 'WAITING_DEPT_HEAD';
    const STATUS_GA_VERIFICATION = 'GA_VERIFICATION';
    const STATUS_WAITING_BUDGET_APPROVAL = 'WAITING_BUDGET_APPROVAL';
    const STATUS_READY_FOR_VOUCHER = 'READY_FOR_VOUCHER';
    const STATUS_VOUCHER_CREATED = 'VOUCHER_CREATED';
    const STATUS_PAID = 'PAID';

    protected $notifications;

    public function __construct(GaNotificationService $notifications)
    {
        $this->notifications = $notifications;
    }

    public function create(array $data, $user)
    {
        $this->validateCreate($data, $user);

        $location = trim((string) ($data['Location'] ?? $user->Location));
        $company = trim((string) ($data['Company_ID'] ?? $user->Company_ID));
        $priority = strtoupper(trim((string) ($data['Priority'] ?? 'NORMAL')));
        $source = strtoupper(trim((string) ($data['Source'] ?? 'FORM')));

        $domainType = strtoupper(trim((string) ($data['Domain_Type'] ?? self::DOMAIN_GA)));
        if (!in_array($domainType, [self::DOMAIN_GA, self::DOMAIN_IT], true)) {
            $domainType = self::DOMAIN_GA;
        }
        $workflowMode = strtoupper(trim((string) ($data['Workflow_Mode'] ?? self::MODE_OPERATIONAL)));
        $validModes = [
            self::MODE_OPERATIONAL,
            self::MODE_WORK_ORDER_VOUCHER,
            self::MODE_IT_SUPPORT,
            self::MODE_IT_INCIDENT,
            self::MODE_IT_DEVICE_REQUEST,
        ];
        if (!in_array($workflowMode, $validModes, true)) {
            $workflowMode = self::MODE_OPERATIONAL;
        }
        $actionType = strtoupper(trim((string) ($data['Action_Type'] ?? self::ACTION_CORRECTIVE)));
        if (!in_array($actionType, [self::ACTION_PREVENTIVE, self::ACTION_CORRECTIVE], true)) {
            $actionType = self::ACTION_CORRECTIVE;
        }

        $category = $this->resolveCategory($data['Category_ID'] ?? null, $location, $company);
        $subcategory = null;
        if ($category && !empty($category->Parent_ID)) {
            $subcategory = $category;
            $category = $this->resolveCategory($category->Parent_ID, $location, $company);
        }
        if (!empty($data['Subcategory_ID'])) {
            $subcategory = $this->resolveCategory($data['Subcategory_ID'], $location, $company);
        }

        if ($category && !empty($category->Default_Priority) && empty($data['Priority'])) {
            $priority = strtoupper((string) $category->Default_Priority);
        }

        $sla = $this->resolveSlaPolicy($location, $company, $category, $subcategory, $priority);
        $now = Carbon::now();
        list($responseDue, $resolveDue) = $this->computeDue($sla, $now);

        $reporter = $user;
        $onBehalfId = !empty($data['On_Behalf_Of_User_Id']) ? (int) $data['On_Behalf_Of_User_Id'] : null;
        if ($onBehalfId) {
            $onBehalf = DB::table('users')->where('id', $onBehalfId)->first();
            if (!$onBehalf) {
                throw new InvalidArgumentException('User yang diwakilkan tidak ditemukan.');
            }
            $reporter = $onBehalf;
        }

        $subject = trim((string) ($data['Ticket_Subject'] ?? ''));
        $description = trim((string) ($data['Description'] ?? ''));
        $ticketNo = $this->generateTicketNo($location, $domainType === self::DOMAIN_IT ? 'IT' : 'GA');

        return DB::transaction(function () use ($data, $user, $reporter, $onBehalfId, $location, $company, $priority, $source, $domainType, $workflowMode, $actionType, $category, $subcategory, $sla, $responseDue, $resolveDue, $now, $subject, $description, $ticketNo) {
            $ticketId = DB::table('ticket')->insertGetId([
                'Ticket_No' => $ticketNo,
                'Proposed_Date' => $now->toDateString(),
                'Ticket_Type' => $domainType === self::DOMAIN_IT ? 'IT' : 'GA',
                'Ticket_Category' => $category ? mb_substr((string) $category->Name, 0, 250) : 'General',
                'Ticket_Subject' => mb_substr($subject, 0, 250),
                'GEU_Express_ID' => mb_substr((string) ($data['GEU_Express_ID'] ?? $location), 0, 150),
                'Description' => mb_substr($description, 0, 250),
                'Ticket_Status' => self::STATUS_OPEN,
                'File_Document' => null,
                'Created_By' => (string) $user->username,
                'Created_Date' => $now,
                'Completion_Note' => null,
                'Module' => 'GA',
                'Location' => $location,
                'Company_ID' => $company,
                'Source' => $source,
                'Priority' => $priority,
                'Reporter' => mb_substr((string) ($reporter->Name ?? $reporter->username), 0, 150),
                'Department' => mb_substr((string) ($reporter->Department ?? $data['Department'] ?? ''), 0, 100),
            ]);

            $assetNo = trim((string) ($data['Asset_No'] ?? ''));
            $damageSeverity = strtoupper(trim((string) ($data['Damage_Severity'] ?? '')));

            DB::table('ga_ticket')->insert([
                'Ticket_ID' => $ticketId,
                'Location' => $location,
                'Asset_No' => $assetNo !== '' ? mb_substr($assetNo, 0, 50) : null,
                'Damage_Severity' => $damageSeverity !== '' ? mb_substr($damageSeverity, 0, 30) : null,
                'Company_ID' => $company,
                'Domain_Type' => $domainType,
                'Workflow_Mode' => $workflowMode,
                'Action_Type' => $actionType,
                'Reporter_User_Id' => (int) $reporter->id,
                'Reporter_Name' => mb_substr((string) ($reporter->Name ?? $reporter->username), 0, 150),
                'Reporter_Department' => mb_substr((string) ($reporter->Department ?? ''), 0, 100),
                'Reporter_Email' => mb_substr((string) ($reporter->email ?? ''), 0, 150),
                'Reporter_Phone' => mb_substr((string) ($data['Reporter_Phone'] ?? ''), 0, 50),
                'On_Behalf_Of_User_Id' => $onBehalfId,
                'Category_ID' => $category ? (int) $category->ID : null,
                'Subcategory_ID' => $subcategory ? (int) $subcategory->ID : null,
                'Priority' => $priority,
                'Source' => $source,
                'QR_Point_ID' => !empty($data['QR_Point_ID']) ? (int) $data['QR_Point_ID'] : null,
                'Sla_Policy_ID' => $sla ? (int) $sla->ID : null,
                'Response_Due_At' => $responseDue,
                'Resolve_Due_At' => $resolveDue,
                'Description' => $description,
                'Device_Spec' => !empty($data['Device_Spec']) ? trim((string) $data['Device_Spec']) : null,
                'Estimated_Amount' => isset($data['Estimated_Amount']) ? max(0, (float) $data['Estimated_Amount']) : 0,
                'Preferred_Vendor_ID' => !empty($data['Preferred_Vendor_ID']) ? (int) $data['Preferred_Vendor_ID'] : null,
                'Cost_Center' => mb_substr((string) ($data['Cost_Center'] ?? ($reporter->Department ?? '')), 0, 50),
                'Department' => mb_substr((string) ($data['Department'] ?? ($reporter->Department ?? '')), 0, 100),
                'Created_By' => (string) $user->username,
                'Created_At' => $now,
            ]);

            $this->insertHistory($ticketId, null, self::STATUS_OPEN, 'Tiket dibuat.', (string) $user->username, $now);

            if ($assetNo !== '') {
                app(\App\Services\HCGA\AssetLifecycleService::class)->markDamaged(
                    $assetNo,
                    $damageSeverity !== '' ? $damageSeverity : 'RUSAK RINGAN',
                    (string) $user->username
                );
            }

            $skipAutoAssign = !empty($data['Skip_Auto_Assign'])
                || in_array($workflowMode, [self::MODE_WORK_ORDER_VOUCHER, self::MODE_IT_DEVICE_REQUEST], true);
            $keepStatus = ($domainType === self::DOMAIN_IT);
            $assigned = false;

            if (!$skipAutoAssign) {
                if ($category && !empty($category->Default_Team_ID)) {
                    $this->applyAssignment($ticketId, 'TEAM', (int) $category->Default_Team_ID, null, null, (string) $user->username, 'Routing kategori default.', $now, $keepStatus);
                    $assigned = true;
                } else {
                    $rule = $this->findRoutingRule($location, $company, $category ? (int) $category->ID : null, $subcategory ? (int) $subcategory->ID : null, $priority);
                    if ($rule) {
                        $this->applyAssignment($ticketId, 'TEAM', (int) $rule->Team_ID, null, null, (string) $user->username, 'Routing otomatis.', $now, $keepStatus);
                        $assigned = true;
                    }
                }

                if (!$assigned && !empty($data['Assignee_User_Id'])) {
                    $this->applyAssignment($ticketId, 'USER', null, (int) $data['Assignee_User_Id'], null, (string) $user->username, 'Assignment saat pembuatan.', $now, $keepStatus);
                    $assigned = true;
                }
            }

            if (empty($data['Suppress_Create_Notification'])) {
                $statusText = ($assigned && !$keepStatus) ? 'ASSIGNED' : 'OPEN';
                $this->notifications->notifyUser(
                    (int) $reporter->id,
                    'Tiket ' . ($domainType === self::DOMAIN_IT ? 'IT' : 'GA') . ' dibuat: ' . $ticketNo,
                    'Tiket "' . $subject . '" telah dibuat dengan status ' . $statusText . '.',
                    $domainType === self::DOMAIN_IT ? 'it_ticket' : 'ga_ticket',
                    ['Ticket_ID' => $ticketId, 'Ticket_No' => $ticketNo]
                );
            }

            return $this->find($ticketId);
        });
    }

    public function triage($ticketId, array $data, $user)
    {
        $ticketId = (int) $ticketId;

        return DB::transaction(function () use ($ticketId, $data, $user) {
            $ticket = DB::table('ticket')->where('ID', $ticketId)->lockForUpdate()->first();
            $this->assertGaTicket($ticket);

            $from = strtoupper((string) $ticket->Ticket_Status);
            if (!in_array($from, [self::STATUS_OPEN, self::STATUS_TRIAGED, self::STATUS_REOPENED], true)) {
                throw new InvalidArgumentException('Tiket berstatus ' . $from . ' tidak dapat di-triage.');
            }

            $detail = DB::table('ga_ticket')->where('Ticket_ID', $ticketId)->first();
            $location = (string) ($detail->Location ?? $ticket->Location);
            $company = (string) ($detail->Company_ID ?? $ticket->Company_ID);

            $category = $this->resolveCategory($data['Category_ID'] ?? $detail->Category_ID, $location, $company);
            $subcategory = null;
            if ($category && !empty($category->Parent_ID)) {
                $subcategory = $category;
                $category = $this->resolveCategory($category->Parent_ID, $location, $company);
            }
            if (!empty($data['Subcategory_ID'])) {
                $subcategory = $this->resolveCategory($data['Subcategory_ID'], $location, $company);
            }

            $priority = strtoupper(trim((string) ($data['Priority'] ?? $detail->Priority ?? 'NORMAL')));
            $sla = $this->resolveSlaPolicy($location, $company, $category, $subcategory, $priority);
            $now = Carbon::now();
            list($responseDue, $resolveDue) = $this->computeDue($sla, $now);

            DB::table('ga_ticket')->where('Ticket_ID', $ticketId)->update([
                'Category_ID' => $category ? (int) $category->ID : null,
                'Subcategory_ID' => $subcategory ? (int) $subcategory->ID : null,
                'Priority' => $priority,
                'Sla_Policy_ID' => $sla ? (int) $sla->ID : null,
                'Response_Due_At' => $responseDue,
                'Resolve_Due_At' => $resolveDue,
                'Triage_Note' => mb_substr((string) ($data['Triage_Note'] ?? ''), 0, 2000),
                'Updated_By' => (string) $user->username,
                'Updated_At' => $now,
            ]);

            DB::table('ticket')->where('ID', $ticketId)->update([
                'Ticket_Category' => $category ? mb_substr((string) $category->Name, 0, 250) : $ticket->Ticket_Category,
                'Priority' => $priority,
                'Ticket_Status' => self::STATUS_TRIAGED,
            ]);

            $this->insertHistory($ticketId, $from, self::STATUS_TRIAGED, (string) ($data['Triage_Note'] ?? 'Triage tiket.'), (string) $user->username, $now);

            $assigned = false;
            if (!empty($data['Assignee_User_Id'])) {
                $this->applyAssignment($ticketId, 'USER', null, (int) $data['Assignee_User_Id'], null, (string) $user->username, 'Assignment saat triage.', $now);
                $assigned = true;
            } elseif (!empty($data['Assignee_Team_ID'])) {
                $this->applyAssignment($ticketId, 'TEAM', (int) $data['Assignee_Team_ID'], null, null, (string) $user->username, 'Assignment saat triage.', $now);
                $assigned = true;
            } else {
                $rule = $this->findRoutingRule($location, $company, $category ? (int) $category->ID : null, $subcategory ? (int) $subcategory->ID : null, $priority);
                if ($rule) {
                    $this->applyAssignment($ticketId, 'TEAM', (int) $rule->Team_ID, null, null, (string) $user->username, 'Routing otomatis setelah triage.', $now);
                    $assigned = true;
                }
            }

            return $this->find($ticketId);
        });
    }

    public function routeTicket($ticketId, $user)
    {
        $ticketId = (int) $ticketId;

        return DB::transaction(function () use ($ticketId, $user) {
            $ticket = DB::table('ticket')->where('ID', $ticketId)->lockForUpdate()->first();
            $this->assertGaTicket($ticket);

            $detail = DB::table('ga_ticket')->where('Ticket_ID', $ticketId)->first();
            $rule = $this->findRoutingRule(
                (string) ($detail->Location ?? $ticket->Location),
                (string) ($detail->Company_ID ?? $ticket->Company_ID),
                $detail->Category_ID ? (int) $detail->Category_ID : null,
                $detail->Subcategory_ID ? (int) $detail->Subcategory_ID : null,
                (string) ($detail->Priority ?? $ticket->Priority ?? 'NORMAL')
            );

            if (!$rule) {
                throw new InvalidArgumentException('Tidak ada routing rule yang cocok untuk tiket ini.');
            }

            $this->applyAssignment($ticketId, 'TEAM', (int) $rule->Team_ID, null, null, (string) $user->username, 'Routing otomatis.', Carbon::now());

            return $this->find($ticketId);
        });
    }

    public function assign($ticketId, array $data, $user, $keepStatus = false)
    {
        $ticketId = (int) $ticketId;
        $type = strtoupper(trim((string) ($data['Assignee_Type'] ?? 'USER')));

        return DB::transaction(function () use ($ticketId, $data, $user, $type, $keepStatus) {
            $ticket = DB::table('ticket')->where('ID', $ticketId)->lockForUpdate()->first();
            $this->assertGaTicket($ticket);

            if (in_array(strtoupper((string) $ticket->Ticket_Status), [self::STATUS_CLOSED, self::STATUS_REJECTED], true)) {
                throw new InvalidArgumentException('Tiket yang sudah CLOSED/REJECTED tidak dapat di-assign.');
            }

            $teamId = !empty($data['Assignee_Team_ID']) ? (int) $data['Assignee_Team_ID'] : null;
            $userId = !empty($data['Assignee_User_Id']) ? (int) $data['Assignee_User_Id'] : null;
            $supplierId = !empty($data['Assignee_Supplier_ID']) ? (int) $data['Assignee_Supplier_ID'] : null;

            if ($type === 'TEAM' && !$teamId) {
                throw new InvalidArgumentException('Team wajib dipilih untuk assignment tipe TEAM.');
            }
            if ($type === 'USER' && !$userId) {
                throw new InvalidArgumentException('User wajib dipilih untuk assignment tipe USER.');
            }
            if ($type === 'VENDOR' && !$supplierId) {
                throw new InvalidArgumentException('Vendor wajib dipilih untuk assignment tipe VENDOR.');
            }

            $this->applyAssignment($ticketId, $type, $teamId, $userId, $supplierId, (string) $user->username, (string) ($data['Reason'] ?? 'Assignment manual.'), Carbon::now(), $keepStatus);

            return $this->find($ticketId);
        });
    }

    public function transition($ticketId, $toStatus, array $data, $user)
    {
        $ticketId = (int) $ticketId;
        $toStatus = strtoupper(trim((string) $toStatus));

        return DB::transaction(function () use ($ticketId, $toStatus, $data, $user) {
            $ticket = DB::table('ticket')->where('ID', $ticketId)->lockForUpdate()->first();
            $this->assertGaTicket($ticket);

            $from = strtoupper((string) $ticket->Ticket_Status);
            $allowed = $this->allowedTransitions($from);
            if (!in_array($toStatus, $allowed, true)) {
                throw new InvalidArgumentException('Transisi status ' . $from . ' ke ' . $toStatus . ' tidak diizinkan.');
            }

            $note = trim((string) ($data['note'] ?? $data['Note'] ?? ''));
            if (in_array($toStatus, [self::STATUS_RESOLVED, self::STATUS_REJECTED], true) && $note === '') {
                throw new InvalidArgumentException('Catatan wajib diisi untuk status ' . $toStatus . '.');
            }

            $now = Carbon::now();
            $detail = DB::table('ga_ticket')->where('Ticket_ID', $ticketId)->lockForUpdate()->first();

            $headerUpdates = ['Ticket_Status' => $toStatus];
            if ($toStatus === self::STATUS_RESOLVED) {
                $headerUpdates['Completion_Note'] = mb_substr($note, 0, 150);
            }
            DB::table('ticket')->where('ID', $ticketId)->update($headerUpdates);

            $gaUpdates = [
                'Updated_By' => (string) $user->username,
                'Updated_At' => $now,
            ];

            if ($toStatus === self::STATUS_RESOLVED) {
                $gaUpdates['Resolved_At'] = $now;
            }
            if ($toStatus === self::STATUS_CLOSED) {
                $gaUpdates['Closed_At'] = $now;
            }
            if ($toStatus === self::STATUS_REJECTED) {
                $gaUpdates['Rejection_Reason'] = mb_substr($note, 0, 2000);
            }
            if ($toStatus === self::STATUS_ON_HOLD) {
                $gaUpdates['On_Hold_At'] = $now;
            }
            if ($toStatus === self::STATUS_REOPENED) {
                $gaUpdates['Reopen_Count'] = DB::raw('Reopen_Count + 1');
                $gaUpdates['Resolved_At'] = null;
                $gaUpdates['Closed_At'] = null;
                $gaUpdates['On_Hold_At'] = null;
                $sla = $detail && $detail->Sla_Policy_ID ? DB::table('ga_sla_policy')->where('ID', $detail->Sla_Policy_ID)->first() : null;
                list($responseDue, $resolveDue) = $this->computeDue($sla, $now);
                $gaUpdates['Response_Due_At'] = $responseDue;
                $gaUpdates['Resolve_Due_At'] = $resolveDue;
            }
            if ($from === self::STATUS_ON_HOLD && in_array($toStatus, [self::STATUS_ASSIGNED, self::STATUS_IN_PROGRESS, self::STATUS_TRIAGED], true)) {
                $holdMinutes = 0;
                if ($detail && !empty($detail->On_Hold_At)) {
                    $holdMinutes = max(0, Carbon::parse($detail->On_Hold_At)->diffInMinutes($now));
                }
                $gaUpdates['Total_Hold_Minutes'] = (int) ($detail->Total_Hold_Minutes ?? 0) + $holdMinutes;
                $gaUpdates['On_Hold_At'] = null;
                if ($holdMinutes > 0) {
                    if (!empty($detail->Response_Due_At)) {
                        $gaUpdates['Response_Due_At'] = Carbon::parse($detail->Response_Due_At)->addMinutes($holdMinutes);
                    }
                    if (!empty($detail->Resolve_Due_At)) {
                        $gaUpdates['Resolve_Due_At'] = Carbon::parse($detail->Resolve_Due_At)->addMinutes($holdMinutes);
                    }
                }
            }

            DB::table('ga_ticket')->where('Ticket_ID', $ticketId)->update($gaUpdates);
            $this->insertHistory($ticketId, $from, $toStatus, $note !== '' ? $note : null, (string) $user->username, $now);

            if ($detail && !empty($detail->Asset_No) && in_array($toStatus, [self::STATUS_RESOLVED, self::STATUS_CLOSED], true)) {
                app(\App\Services\HCGA\AssetLifecycleService::class)->markRestored(
                    (string) $detail->Asset_No,
                    (string) $user->username
                );
            }

            $fresh = $this->find($ticketId);
            if ($fresh && !empty($fresh->Reporter_User_Id)) {
                $this->notifications->notifyUser(
                    (int) $fresh->Reporter_User_Id,
                    'Status tiket GA ' . $fresh->Ticket_No . ' menjadi ' . $toStatus,
                    'Tiket "' . $fresh->Ticket_Subject . '" sekarang berstatus ' . $toStatus . ($note !== '' ? '. Catatan: ' . $note : '.'),
                    'ga_ticket',
                    ['Ticket_ID' => $ticketId, 'Ticket_No' => $fresh->Ticket_No, 'Status' => $toStatus]
                );
            }

            return $fresh;
        });
    }

    public function comment($ticketId, $comment, $isInternal, $user)
    {
        $ticketId = (int) $ticketId;
        $comment = trim((string) $comment);
        if ($comment === '') {
            throw new InvalidArgumentException('Komentar tidak boleh kosong.');
        }

        return DB::transaction(function () use ($ticketId, $comment, $isInternal, $user) {
            $ticket = DB::table('ticket')->where('ID', $ticketId)->lockForUpdate()->first();
            $this->assertGaTicket($ticket);

            $now = Carbon::now();
            $commentId = DB::table('ga_ticket_comment')->insertGetId([
                'Ticket_ID' => $ticketId,
                'Comment' => $comment,
                'Is_Internal' => $isInternal ? 1 : 0,
                'Created_By' => (string) $user->username,
                'Created_At' => $now,
            ]);

            $detail = DB::table('ga_ticket')->where('Ticket_ID', $ticketId)->first();
            if ($detail && empty($detail->First_Response_At) && (int) $detail->Reporter_User_Id !== (int) $user->id) {
                DB::table('ga_ticket')->where('Ticket_ID', $ticketId)->update([
                    'First_Response_At' => $now,
                    'Updated_At' => $now,
                ]);
            }

            $fresh = $this->find($ticketId);
            if ($fresh && !$isInternal && !empty($fresh->Reporter_User_Id)) {
                $this->notifications->notifyUser(
                    (int) $fresh->Reporter_User_Id,
                    'Komentar baru pada tiket GA ' . $fresh->Ticket_No,
                    $comment,
                    'ga_ticket',
                    ['Ticket_ID' => $ticketId, 'Ticket_No' => $fresh->Ticket_No]
                );
            }
            if ($fresh && $isInternal && !empty($fresh->Assignee_User_Id)) {
                $this->notifications->notifyUser(
                    (int) $fresh->Assignee_User_Id,
                    'Catatan internal tiket GA ' . $fresh->Ticket_No,
                    $comment,
                    'ga_ticket',
                    ['Ticket_ID' => $ticketId, 'Ticket_No' => $fresh->Ticket_No]
                );
            }

            return DB::table('ga_ticket_comment')->where('ID', $commentId)->first();
        });
    }

    public function find($ticketId)
    {
        return DB::table('ticket as t')
            ->join('ga_ticket as g', 'g.Ticket_ID', '=', 't.ID')
            ->leftJoin('ga_ticket_category as c', 'c.ID', '=', 'g.Category_ID')
            ->leftJoin('ga_ticket_category as sc', 'sc.ID', '=', 'g.Subcategory_ID')
            ->leftJoin('ga_team as team', 'team.ID', '=', 'g.Assignee_Team_ID')
            ->leftJoin('ga_sla_policy as sla', 'sla.ID', '=', 'g.Sla_Policy_ID')
            ->leftJoin('users as reporter', 'reporter.id', '=', 'g.Reporter_User_Id')
            ->leftJoin('users as assignee', 'assignee.id', '=', 'g.Assignee_User_Id')
            ->leftJoin('supplier as vendor', 'vendor.ID', '=', 'g.Assignee_Supplier_ID')
            ->leftJoin('supplier as pref_vendor', 'pref_vendor.ID', '=', 'g.Preferred_Vendor_ID')
            ->leftJoin('ga_payment_voucher as pv', 'pv.ID', '=', 'g.Payment_Voucher_ID')
            ->where('t.ID', (int) $ticketId)
            ->where('t.Module', 'GA')
            ->select([
                't.ID',
                't.Ticket_No',
                't.Proposed_Date',
                't.Ticket_Type',
                't.Ticket_Category',
                't.Ticket_Subject',
                't.Description',
                't.Ticket_Status',
                't.Priority',
                't.Location',
                'g.Asset_No',
                'g.Damage_Severity',
                't.Company_ID',
                't.Source',
                't.Reporter',
                't.Department',
                't.Created_By',
                't.Created_Date',
                't.Completion_Note',
                'g.ID as Ga_ID',
                'g.Domain_Type',
                'g.Workflow_Mode',
                'g.Action_Type',
                'g.Reporter_User_Id',
                'g.Reporter_Name',
                'g.Reporter_Department',
                'g.Reporter_Email',
                'g.Reporter_Phone',
                'g.On_Behalf_Of_User_Id',
                'g.Category_ID',
                'g.Subcategory_ID',
                'g.Sla_Policy_ID',
                'g.Response_Due_At',
                'g.Resolve_Due_At',
                'g.First_Response_At',
                'g.Resolved_At',
                'g.Closed_At',
                'g.On_Hold_At',
                'g.Total_Hold_Minutes',
                'g.Assignee_Type',
                'g.Assignee_Team_ID',
                'g.Assignee_User_Id',
                'g.Assignee_Supplier_ID',
                'g.Description as Ga_Description',
                'g.Triage_Note',
                'g.Rejection_Reason',
                'g.Diagnosis_Note',
                'g.Root_Cause',
                'g.Recovery_Note',
                'g.Testing_Status',
                'g.Testing_Note',
                'g.Testing_At',
                'g.Escalated_Flag',
                'g.Escalation_Note',
                'g.Escalated_To_Ticket_ID',
                'g.Device_Spec',
                'g.Device_Asset_No',
                'g.Device_Assigned_User_Id',
                'g.Handover_Note',
                'g.Handover_At',
                'g.PR_No',
                'g.Kb_Flag',
                'g.Kb_Title',
                'g.Kb_Solution',
                'g.User_Confirmed_At',
                'g.User_Confirmed_By',
                'g.User_Rating',
                'g.User_Feedback',
                'g.Monitoring_Note',
                'g.Backup_Verified',
                'g.Backup_Verified_At',
                'g.Backup_Note',
                'g.Dept_Approval_Request_ID',
                'g.Budget_Approval_Request_ID',
                'g.Approval_Revision_Count',
                'g.Estimated_Amount',
                'g.Verified_Amount',
                'g.GA_Verified_By',
                'g.GA_Verified_At',
                'g.GA_Verification_Note',
                'g.Preferred_Vendor_ID',
                'g.Payment_Voucher_ID',
                'g.Cost_Center',
                'g.Department as Ga_Department',
                'g.Reopen_Count',
                'g.QR_Point_ID',
                'c.Name as Category_Name',
                'sc.Name as Subcategory_Name',
                'team.Name as Team_Name',
                'sla.Name as Sla_Name',
                'reporter.Name as Reporter_User_Name',
                'assignee.Name as Assignee_Name',
                'vendor.Company_Name as Vendor_Name',
                'pref_vendor.Company_Name as Preferred_Vendor_Name',
                'pv.Voucher_No',
                'pv.Status as Voucher_Status',
                'pv.Amount as Voucher_Amount',
                'pv.Paid_Date as Voucher_Paid_Date',
            ])
            ->first();
    }

    public function query(array $filters, $user)
    {
        $query = $this->baseQuery();

        if (!empty($filters['domain_type'])) {
            $query->where('g.Domain_Type', strtoupper(trim((string) $filters['domain_type'])));
        }
        if (!empty($filters['workflow_mode'])) {
            $query->where('g.Workflow_Mode', strtoupper(trim((string) $filters['workflow_mode'])));
        }
        if (!empty($filters['action_type'])) {
            $query->where('g.Action_Type', strtoupper(trim((string) $filters['action_type'])));
        }

        if (!empty($filters['status'])) {
            $status = strtoupper(trim((string) $filters['status']));
            if ($status === 'ACTIVE') {
                $query->whereNotIn('t.Ticket_Status', [self::STATUS_CLOSED, self::STATUS_REJECTED, self::STATUS_PAID]);
            } else {
                $statuses = array_filter(array_map('trim', explode(',', $status)));
                $query->whereIn('t.Ticket_Status', $statuses);
            }
        }
        if (!empty($filters['priority'])) {
            $query->where('g.Priority', strtoupper(trim((string) $filters['priority'])));
        }
        if (!empty($filters['category_id'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('g.Category_ID', (int) $filters['category_id'])
                    ->orWhere('g.Subcategory_ID', (int) $filters['category_id']);
            });
        }
        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('t.Ticket_No', 'like', '%' . $search . '%')
                    ->orWhere('t.Ticket_Subject', 'like', '%' . $search . '%')
                    ->orWhere('g.Reporter_Name', 'like', '%' . $search . '%')
                    ->orWhere('pv.Voucher_No', 'like', '%' . $search . '%');
            });
        }
        if (!empty($filters['date_from'])) {
            $query->whereDate('t.Created_Date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('t.Created_Date', '<=', $filters['date_to']);
        }
        if (!empty($filters['location'])) {
            $query->where('t.Location', $filters['location']);
        }
        if (!empty($filters['company_id'])) {
            $query->where('t.Company_ID', $filters['company_id']);
        }
        if (!empty($filters['assignee'])) {
            $assignee = strtolower(trim((string) $filters['assignee']));
            if ($assignee === 'me') {
                $this->scopeToUser($query, $user);
            } elseif ($assignee === 'team') {
                $teamIds = $this->userTeamIds($user);
                if (empty($teamIds)) {
                    $query->whereRaw('1 = 0');
                } else {
                    $query->whereIn('g.Assignee_Team_ID', $teamIds);
                }
            }
        }

        return $query;
    }

    public function myAssignmentsQuery($user, array $filters = [])
    {
        $query = $this->baseQuery()
            ->whereNotIn('t.Ticket_Status', [self::STATUS_CLOSED, self::STATUS_REJECTED, self::STATUS_PAID]);
        $this->scopeToUser($query, $user);

        if (!empty($filters['domain_type'])) {
            $query->where('g.Domain_Type', strtoupper(trim((string) $filters['domain_type'])));
        }
        if (!empty($filters['workflow_mode'])) {
            $query->where('g.Workflow_Mode', strtoupper(trim((string) $filters['workflow_mode'])));
        }
        if (!empty($filters['action_type'])) {
            $query->where('g.Action_Type', strtoupper(trim((string) $filters['action_type'])));
        }
        if (!empty($filters['status'])) {
            $query->where('t.Ticket_Status', strtoupper(trim((string) $filters['status'])));
        }

        return $query;
    }

    public function history($ticketId)
    {
        return DB::table('ga_ticket_status_history')
            ->where('Ticket_ID', (int) $ticketId)
            ->orderBy('Changed_At', 'asc')
            ->orderBy('ID', 'asc')
            ->get();
    }

    public function comments($ticketId)
    {
        return DB::table('ga_ticket_comment as c')
            ->leftJoin('users as u', 'u.username', '=', 'c.Created_By')
            ->where('c.Ticket_ID', (int) $ticketId)
            ->orderBy('c.Created_At', 'asc')
            ->orderBy('c.ID', 'asc')
            ->select('c.*', 'u.Name as User_Name')
            ->get();
    }

    public function assignments($ticketId)
    {
        return DB::table('ga_ticket_assignment as a')
            ->leftJoin('ga_team as team', 'team.ID', '=', 'a.Team_ID')
            ->leftJoin('users as u', 'u.id', '=', 'a.User_Id')
            ->leftJoin('supplier as vendor', 'vendor.ID', '=', 'a.Supplier_ID')
            ->where('a.Ticket_ID', (int) $ticketId)
            ->orderBy('a.Assigned_At', 'desc')
            ->orderBy('a.ID', 'desc')
            ->select('a.*', 'team.Name as Team_Name', 'u.Name as User_Name', 'vendor.Company_Name as Vendor_Name')
            ->get();
    }

    public function workOrders($ticketId)
    {
        return DB::table('ga_work_order')
            ->where('Ticket_ID', (int) $ticketId)
            ->orderBy('Created_At', 'desc')
            ->get();
    }

    public function slaSummary($ticket)
    {
        $now = Carbon::now();
        $responseOverdue = false;
        $resolveOverdue = false;

        if ($ticket && empty($ticket->First_Response_At) && !empty($ticket->Response_Due_At)) {
            $responseOverdue = Carbon::parse($ticket->Response_Due_At)->lt($now);
        }
        if ($ticket && empty($ticket->Resolved_At) && !empty($ticket->Resolve_Due_At)) {
            $resolveOverdue = Carbon::parse($ticket->Resolve_Due_At)->lt($now);
        }

        return [
            'Response_Overdue' => $responseOverdue,
            'Resolve_Overdue' => $resolveOverdue,
            'Response_Due_At' => $ticket->Response_Due_At ?? null,
            'Resolve_Due_At' => $ticket->Resolve_Due_At ?? null,
        ];
    }

    public function scanSla($limit = 200)
    {
        $now = Carbon::now();
        $count = 0;

        $rows = DB::table('ticket as t')
            ->join('ga_ticket as g', 'g.Ticket_ID', '=', 't.ID')
            ->where('t.Module', 'GA')
            ->whereNotIn('t.Ticket_Status', [self::STATUS_CLOSED, self::STATUS_REJECTED, self::STATUS_RESOLVED])
            ->where(function ($q) use ($now) {
                $q->where(function ($q2) use ($now) {
                    $q2->whereNull('g.First_Response_At')->whereNotNull('g.Response_Due_At')->where('g.Response_Due_At', '<', $now);
                })->orWhere(function ($q2) use ($now) {
                    $q2->whereNull('g.Resolved_At')->whereNotNull('g.Resolve_Due_At')->where('g.Resolve_Due_At', '<', $now);
                });
            })
            ->select('t.ID', 't.Ticket_No', 't.Ticket_Subject', 'g.Response_Due_At', 'g.Resolve_Due_At', 'g.First_Response_At', 'g.Resolved_At', 'g.Reporter_User_Id')
            ->limit((int) $limit)
            ->get();

        foreach ($rows as $row) {
            if (empty($row->First_Response_At) && !empty($row->Response_Due_At) && Carbon::parse($row->Response_Due_At)->lt($now)) {
                if (!$this->slaEventExists((int) $row->ID, 'RESPONSE_BREACH')) {
                    $this->insertSlaEvent((int) $row->ID, 'RESPONSE_BREACH', $now, 'Reporter');
                    $count++;
                }
            }
            if (empty($row->Resolved_At) && !empty($row->Resolve_Due_At) && Carbon::parse($row->Resolve_Due_At)->lt($now)) {
                if (!$this->slaEventExists((int) $row->ID, 'RESOLVE_BREACH')) {
                    $this->insertSlaEvent((int) $row->ID, 'RESOLVE_BREACH', $now, 'Assignee');
                    $count++;
                }
            }
        }

        return $count;
    }

    public function generateTicketNo($location, $prefixCode = 'GA')
    {
        $code = strtoupper(preg_replace('/[^A-Za-z0-9\-]/', '', (string) $prefixCode));
        if ($code === '') {
            $code = 'GA';
        }
        $prefix = $code . '/' . preg_replace('/[^A-Za-z0-9\-]/', '', (string) $location) . '/' . Carbon::now()->format('YmdHis');
        for ($i = 0; $i < 20; $i++) {
            $candidate = $prefix . '-' . strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 6));
            if (!DB::table('ticket')->where('Ticket_No', $candidate)->exists()) {
                return $candidate;
            }
        }

        throw new \RuntimeException('Gagal membuat nomor tiket unik.');
    }

    public function allowedTransitions($status)
    {
        $map = [
            self::STATUS_OPEN => [self::STATUS_TRIAGED, self::STATUS_ASSIGNED, self::STATUS_REJECTED, self::STATUS_ON_HOLD],
            self::STATUS_TRIAGED => [self::STATUS_ASSIGNED, self::STATUS_IN_PROGRESS, self::STATUS_REJECTED, self::STATUS_ON_HOLD],
            self::STATUS_ASSIGNED => [self::STATUS_IN_PROGRESS, self::STATUS_ON_HOLD, self::STATUS_RESOLVED, self::STATUS_REJECTED],
            self::STATUS_IN_PROGRESS => [self::STATUS_ON_HOLD, self::STATUS_RESOLVED, self::STATUS_REJECTED],
            self::STATUS_ON_HOLD => [self::STATUS_TRIAGED, self::STATUS_ASSIGNED, self::STATUS_IN_PROGRESS, self::STATUS_REJECTED],
            self::STATUS_RESOLVED => [self::STATUS_CLOSED, self::STATUS_REOPENED],
            self::STATUS_CLOSED => [self::STATUS_REOPENED],
            self::STATUS_REJECTED => [self::STATUS_REOPENED],
            self::STATUS_REOPENED => [self::STATUS_TRIAGED, self::STATUS_ASSIGNED, self::STATUS_IN_PROGRESS, self::STATUS_RESOLVED, self::STATUS_REJECTED],
        ];

        return $map[$status] ?? [];
    }

    public function statusLabel($status)
    {
        $labels = [
            self::STATUS_OPEN => 'Open',
            self::STATUS_TRIAGED => 'Triaged',
            self::STATUS_ASSIGNED => 'Assigned',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_ON_HOLD => 'On Hold',
            self::STATUS_RESOLVED => 'Resolved',
            self::STATUS_CLOSED => 'Closed',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_REOPENED => 'Reopened',
            self::STATUS_TROUBLESHOOTING => 'Troubleshooting',
            self::STATUS_ESCALATED => 'Escalated',
            self::STATUS_USER_CONFIRMED => 'Konfirmasi User',
            self::STATUS_VALIDATED => 'Validasi Gangguan',
            self::STATUS_ROOT_CAUSE_ANALYSIS => 'Analisis Root Cause',
            self::STATUS_RECOVERY => 'Perbaikan / Recovery',
            self::STATUS_TESTING => 'Testing Sistem',
            self::STATUS_SYSTEM_NORMAL => 'Sistem Normal',
            self::STATUS_WAITING_MANAGER_APPROVAL => 'Menunggu Approval Manager',
            self::STATUS_DEVICE_PREPARATION => 'Persiapan Perangkat',
            self::STATUS_ASSIGNED_TO_USER => 'Assign ke User',
            self::STATUS_HANDOVER => 'Serah Terima',
            self::STATUS_WAITING_DEPT_HEAD => 'Approval Dept Head',
            self::STATUS_GA_VERIFICATION => 'GA Verification',
            self::STATUS_WAITING_BUDGET_APPROVAL => 'Approval Biaya',
            self::STATUS_READY_FOR_VOUCHER => 'Siap Buat Voucher',
            self::STATUS_VOUCHER_CREATED => 'Voucher Dibuat',
            self::STATUS_PAID => 'Dibayar (Paid)',
        ];

        return $labels[$status] ?? $status;
    }

    public function attachFiles($ticketId, array $files, $username)
    {
        $ticketId = (int) $ticketId;
        $allowedExt = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx'];
        $maxBytes = 5 * 1024 * 1024;
        $saved = [];

        $flat = [];
        array_walk_recursive($files, function ($item) use (&$flat) {
            if ($item instanceof UploadedFile) {
                $flat[] = $item;
            }
        });

        if (empty($flat)) {
            return $saved;
        }

        $year = date('Y');
        $month = date('m');
        $relDir = 'ga_ticket_attachments/' . $year . '/' . $month;
        $absDir = storage_path('app/' . $relDir);
        if (!is_dir($absDir)) {
            mkdir($absDir, 0755, true);
        }

        $now = Carbon::now();
        foreach ($flat as $file) {
            if (!$file->isValid()) {
                throw new InvalidArgumentException('Lampiran gagal diunggah.');
            }
            $ext = strtolower((string) $file->getClientOriginalExtension());
            if (!in_array($ext, $allowedExt, true)) {
                throw new InvalidArgumentException('Format lampiran ' . $ext . ' tidak diizinkan (pdf, jpg, jpeg, png, doc, docx, xls, xlsx).');
            }
            $size = (int) $file->getSize();
            if ($size > $maxBytes) {
                throw new InvalidArgumentException('Ukuran tiap lampiran maksimal 5 MB.');
            }

            $origName = mb_substr((string) $file->getClientOriginalName(), 0, 250);
            $mime = mb_substr((string) ($file->getClientMimeType() ?: 'application/octet-stream'), 0, 100);
            $filename = Str::uuid()->toString() . '.' . $ext;
            $file->move($absDir, $filename);

            $relPath = $relDir . '/' . $filename;
            $id = DB::table('ga_ticket_attachment')->insertGetId([
                'Ticket_ID'   => $ticketId,
                'File_Path'   => $relPath,
                'File_Name'   => $origName,
                'Mime_Type'   => $mime,
                'File_Size'   => $size,
                'Uploaded_By' => (string) $username,
                'Created_At'  => $now,
            ]);

            $saved[] = DB::table('ga_ticket_attachment')->where('ID', $id)->first();
        }

        return $saved;
    }

    public function attachments($ticketId)
    {
        return DB::table('ga_ticket_attachment')
            ->where('Ticket_ID', (int) $ticketId)
            ->orderBy('ID', 'asc')
            ->get();
    }

    public function findAttachment($attachmentId)
    {
        return DB::table('ga_ticket_attachment')->where('ID', (int) $attachmentId)->first();
    }

    public function recordHistory($ticketId, $from, $to, $note, $changedBy, Carbon $now = null)
    {
        $this->insertHistory((int) $ticketId, $from, $to, $note, (string) $changedBy, $now ?: Carbon::now());
    }

    public function assignRecord($ticketId, $type, $teamId, $userId, $supplierId, $assignedBy, $reason, Carbon $now = null, $keepStatus = false)
    {
        $this->applyAssignment($ticketId, $type, $teamId, $userId, $supplierId, $assignedBy, $reason, $now ?: Carbon::now(), $keepStatus);
    }

    public function progressSteps($ticket)
    {
        if (!$ticket) {
            return [];
        }

        $mode = strtoupper((string) ($ticket->Workflow_Mode ?? self::MODE_OPERATIONAL));
        $status = strtoupper((string) ($ticket->Ticket_Status ?? self::STATUS_OPEN));

        if ($mode === self::MODE_IT_SUPPORT) {
            $order = [
                ['key' => self::STATUS_OPEN,            'label' => 'Tiket Dibuat (Open)'],
                ['key' => self::STATUS_IN_PROGRESS,     'label' => 'Review IT'],
                ['key' => self::STATUS_TROUBLESHOOTING, 'label' => 'Diagnosa & Troubleshooting'],
                ['key' => self::STATUS_RESOLVED,        'label' => 'Penyelesaian'],
                ['key' => self::STATUS_USER_CONFIRMED,  'label' => 'Konfirmasi & Feedback'],
                ['key' => self::STATUS_CLOSED,          'label' => 'Closed & Monitoring'],
            ];
            $rankMap = [
                self::STATUS_OPEN => 0,
                self::STATUS_ON_HOLD => 1,
                self::STATUS_REOPENED => 1,
                self::STATUS_IN_PROGRESS => 1,
                self::STATUS_TROUBLESHOOTING => 2,
                self::STATUS_ESCALATED => 2,
                self::STATUS_RESOLVED => 3,
                self::STATUS_USER_CONFIRMED => 4,
                self::STATUS_CLOSED => 5,
            ];
            return $this->buildStepperState($order, $rankMap, $status);
        }

        if ($mode === self::MODE_IT_INCIDENT) {
            $order = [
                ['key' => self::STATUS_OPEN,                'label' => 'Laporan / Alert'],
                ['key' => self::STATUS_VALIDATED,           'label' => 'Validasi Gangguan'],
                ['key' => self::STATUS_ROOT_CAUSE_ANALYSIS, 'label' => 'Analisis Root Cause'],
                ['key' => self::STATUS_RECOVERY,            'label' => 'Perbaikan / Recovery'],
                ['key' => self::STATUS_TESTING,             'label' => 'Testing & Sistem Normal'],
                ['key' => self::STATUS_CLOSED,              'label' => 'Closed & Backup Log'],
            ];
            $rankMap = [
                self::STATUS_OPEN => 0,
                self::STATUS_ON_HOLD => 1,
                self::STATUS_REOPENED => 1,
                self::STATUS_VALIDATED => 1,
                self::STATUS_ROOT_CAUSE_ANALYSIS => 2,
                self::STATUS_RECOVERY => 3,
                self::STATUS_TESTING => 4,
                self::STATUS_SYSTEM_NORMAL => 4,
                self::STATUS_CLOSED => 5,
            ];
            return $this->buildStepperState($order, $rankMap, $status);
        }

        if ($mode === self::MODE_IT_DEVICE_REQUEST) {
            $order = [
                ['key' => self::STATUS_WAITING_MANAGER_APPROVAL, 'label' => 'Approval Manager'],
                ['key' => self::STATUS_OPEN,                     'label' => 'Antrean IT (Open)'],
                ['key' => self::STATUS_DEVICE_PREPARATION,       'label' => 'Persiapan Perangkat'],
                ['key' => self::STATUS_ASSIGNED_TO_USER,         'label' => 'Assign to User'],
                ['key' => self::STATUS_HANDOVER,                 'label' => 'Serah Terima'],
                ['key' => self::STATUS_CLOSED,                   'label' => 'Selesai (Closed)'],
            ];
            $rankMap = [
                self::STATUS_WAITING_MANAGER_APPROVAL => 0,
                self::STATUS_OPEN => 1,
                self::STATUS_DEVICE_PREPARATION => 2,
                self::STATUS_ON_HOLD => 2,
                self::STATUS_REOPENED => 2,
                self::STATUS_ASSIGNED_TO_USER => 3,
                self::STATUS_HANDOVER => 4,
                self::STATUS_USER_CONFIRMED => 4,
                self::STATUS_CLOSED => 5,
            ];
            return $this->buildStepperState($order, $rankMap, $status);
        }

        if ($mode === self::MODE_WORK_ORDER_VOUCHER) {
            $order = [
                ['key' => self::STATUS_WAITING_DEPT_HEAD,       'label' => 'Approval Dept Head'],
                ['key' => self::STATUS_GA_VERIFICATION,         'label' => 'GA Verification'],
                ['key' => self::STATUS_WAITING_BUDGET_APPROVAL, 'label' => 'Approval HCGA/FAT/BOD'],
                ['key' => self::STATUS_READY_FOR_VOUCHER,       'label' => 'Siap Buat Voucher'],
                ['key' => self::STATUS_VOUCHER_CREATED,         'label' => 'Voucher Pembayaran'],
                ['key' => self::STATUS_PAID,                    'label' => 'Dibayar Finance'],
            ];
            $rankMap = [
                self::STATUS_OPEN => 0,
                self::STATUS_WAITING_DEPT_HEAD => 0,
                self::STATUS_GA_VERIFICATION => 1,
                self::STATUS_WAITING_BUDGET_APPROVAL => 2,
                self::STATUS_READY_FOR_VOUCHER => 3,
                self::STATUS_VOUCHER_CREATED => 4,
                self::STATUS_PAID => 5,
                self::STATUS_CLOSED => 5,
            ];
            return $this->buildStepperState($order, $rankMap, $status, !empty($ticket->Approval_Revision_Count) && $status === self::STATUS_GA_VERIFICATION);
        }

        $order = [
            ['key' => self::STATUS_OPEN,        'label' => 'Open'],
            ['key' => self::STATUS_TRIAGED,     'label' => 'Triaged'],
            ['key' => self::STATUS_ASSIGNED,    'label' => 'Assigned'],
            ['key' => self::STATUS_IN_PROGRESS, 'label' => 'In Progress'],
            ['key' => self::STATUS_RESOLVED,    'label' => 'Resolved'],
            ['key' => self::STATUS_CLOSED,      'label' => 'Closed'],
        ];
        $rankMap = [
            self::STATUS_OPEN => 0,
            self::STATUS_TRIAGED => 1,
            self::STATUS_REOPENED => 1,
            self::STATUS_ASSIGNED => 2,
            self::STATUS_IN_PROGRESS => 3,
            self::STATUS_ON_HOLD => 3,
            self::STATUS_RESOLVED => 4,
            self::STATUS_CLOSED => 5,
        ];
        return $this->buildStepperState($order, $rankMap, $status);
    }

    protected function buildStepperState(array $order, array $rankMap, string $status, bool $warnActive = false): array
    {
        $isRejected = ($status === self::STATUS_REJECTED);
        $activeIdx = $rankMap[$status] ?? 0;
        $isTerminalDone = in_array($status, [self::STATUS_CLOSED, self::STATUS_PAID], true);

        $result = [];
        foreach ($order as $idx => $step) {
            $state = 'pending';
            if ($isRejected) {
                $state = $idx === 0 ? 'rejected' : 'pending';
            } elseif ($isTerminalDone || $idx < $activeIdx) {
                $state = 'done';
            } elseif ($idx === $activeIdx) {
                $state = $warnActive ? 'warning' : 'active';
            }
            $result[] = [
                'key'   => $step['key'],
                'label' => $step['label'],
                'state' => $state,
            ];
        }

        return $result;
    }

    protected function baseQuery()
    {
        return DB::table('ticket as t')
            ->join('ga_ticket as g', 'g.Ticket_ID', '=', 't.ID')
            ->leftJoin('ga_ticket_category as c', 'c.ID', '=', 'g.Category_ID')
            ->leftJoin('ga_ticket_category as sc', 'sc.ID', '=', 'g.Subcategory_ID')
            ->leftJoin('ga_team as team', 'team.ID', '=', 'g.Assignee_Team_ID')
            ->leftJoin('users as assignee', 'assignee.id', '=', 'g.Assignee_User_Id')
            ->leftJoin('supplier as pref_vendor', 'pref_vendor.ID', '=', 'g.Preferred_Vendor_ID')
            ->leftJoin('ga_payment_voucher as pv', 'pv.ID', '=', 'g.Payment_Voucher_ID')
            ->where('t.Module', 'GA')
            ->select([
                't.ID',
                't.Ticket_No',
                't.Ticket_Subject',
                't.Ticket_Status',
                't.Priority',
                't.Location',
                'g.Asset_No',
                'g.Damage_Severity',
                'g.Domain_Type',
                'g.Workflow_Mode',
                'g.Action_Type',
                't.Company_ID',
                't.Created_Date',
                't.Reporter',
                't.Department',
                'g.Category_ID',
                'g.Subcategory_ID',
                'g.Reporter_User_Id',
                'g.Reporter_Name',
                'g.Assignee_Type',
                'g.Assignee_Team_ID',
                'g.Assignee_User_Id',
                'g.Assignee_Supplier_ID',
                'g.Response_Due_At',
                'g.Resolve_Due_At',
                'g.First_Response_At',
                'g.Resolved_At',
                'g.Closed_At',
                'g.Estimated_Amount',
                'g.Verified_Amount',
                'g.Approval_Revision_Count',
                'g.Payment_Voucher_ID',
                'g.Kb_Flag',
                'g.Kb_Title',
                'g.Kb_Solution',
                'g.Root_Cause',
                'g.Device_Asset_No',
                'g.PR_No',
                'g.Priority as Ga_Priority',
                'c.Name as Category_Name',
                'sc.Name as Subcategory_Name',
                'team.Name as Team_Name',
                'assignee.Name as Assignee_Name',
                'pref_vendor.Company_Name as Preferred_Vendor_Name',
                'pv.Voucher_No',
                'pv.Status as Voucher_Status',
                'pv.Amount as Voucher_Amount',
            ])
            ->orderBy('t.Created_Date', 'desc')
            ->orderBy('t.ID', 'desc');
    }

    protected function scopeToUser($query, $user)
    {
        $teamIds = $this->userTeamIds($user);
        $query->where(function ($q) use ($user, $teamIds) {
            $q->where('g.Assignee_User_Id', (int) $user->id);
            if (!empty($teamIds)) {
                $q->orWhereIn('g.Assignee_Team_ID', $teamIds);
            }
        });
    }

    protected function userTeamIds($user)
    {
        return DB::table('ga_team_member')
            ->where('User_Id', (int) $user->id)
            ->where('Status', 'ACTIVE')
            ->pluck('Team_ID')
            ->all();
    }

    protected function validateCreate(array $data, $user)
    {
        if (trim((string) ($data['Ticket_Subject'] ?? '')) === '') {
            throw new InvalidArgumentException('Subjek tiket wajib diisi.');
        }
        if (trim((string) ($data['Description'] ?? '')) === '') {
            throw new InvalidArgumentException('Deskripsi tiket wajib diisi.');
        }

        $location = trim((string) ($data['Location'] ?? $user->Location));
        $company = trim((string) ($data['Company_ID'] ?? $user->Company_ID));
        if ($location === '' || $company === '') {
            throw new InvalidArgumentException('Location dan Company_ID wajib terisi.');
        }

        $priority = strtoupper(trim((string) ($data['Priority'] ?? 'NORMAL')));
        if (!in_array($priority, ['LOW', 'NORMAL', 'HIGH', 'URGENT'], true)) {
            throw new InvalidArgumentException('Priority tidak dikenal.');
        }

        $source = strtoupper(trim((string) ($data['Source'] ?? 'FORM')));
        if (!in_array($source, ['FORM', 'QR', 'ON_BEHALF', 'INSPECTION'], true)) {
            throw new InvalidArgumentException('Source tidak dikenal.');
        }
    }

    protected function resolveCategory($categoryId, $location, $company)
    {
        $categoryId = (int) $categoryId;
        if ($categoryId < 1) {
            return null;
        }

        $category = DB::table('ga_ticket_category')
            ->where('ID', $categoryId)
            ->where('Status', 'ACTIVE')
            ->where(function ($q) use ($location) {
                $q->whereNull('Location')->orWhere('Location', $location);
            })
            ->where(function ($q) use ($company) {
                $q->whereNull('Company_ID')->orWhere('Company_ID', $company);
            })
            ->first();

        if (!$category) {
            throw new InvalidArgumentException('Kategori tiket tidak ditemukan atau tidak aktif.');
        }

        return $category;
    }

    protected function resolveSlaPolicy($location, $company, $category, $subcategory, $priority)
    {
        if ($category && !empty($category->Sla_Policy_ID)) {
            $policy = DB::table('ga_sla_policy')
                ->where('ID', (int) $category->Sla_Policy_ID)
                ->where('Status', 'ACTIVE')
                ->first();
            if ($policy) {
                return $policy;
            }
        }

        $categoryIds = [];
        if ($subcategory) {
            $categoryIds[] = (int) $subcategory->ID;
        }
        if ($category) {
            $categoryIds[] = (int) $category->ID;
        }

        $query = DB::table('ga_sla_policy')
            ->where('Status', 'ACTIVE')
            ->where(function ($q) use ($location) {
                $q->whereNull('Location')->orWhere('Location', $location);
            })
            ->where(function ($q) use ($company) {
                $q->whereNull('Company_ID')->orWhere('Company_ID', $company);
            })
            ->where(function ($q) use ($categoryIds) {
                $q->whereNull('Category_ID');
                if (!empty($categoryIds)) {
                    $q->orWhereIn('Category_ID', $categoryIds);
                }
            })
            ->where(function ($q) use ($priority) {
                $q->whereNull('Priority')->orWhere('Priority', $priority);
            })
            ->orderByRaw('CASE WHEN Category_ID IS NULL THEN 1 ELSE 0 END')
            ->orderByRaw('CASE WHEN Priority IS NULL THEN 1 ELSE 0 END')
            ->orderBy('ID');

        return $query->first();
    }

    protected function computeDue($policy, Carbon $from)
    {
        if (!$policy) {
            return [null, null];
        }

        $response = !empty($policy->Response_Minutes) ? $from->copy()->addMinutes((int) $policy->Response_Minutes) : null;
        $resolve = !empty($policy->Resolve_Minutes) ? $from->copy()->addMinutes((int) $policy->Resolve_Minutes) : null;

        return [$response, $resolve];
    }

    protected function findRoutingRule($location, $company, $categoryId, $subcategoryId, $priority)
    {
        return DB::table('ga_routing_rule')
            ->where('Is_Active', 1)
            ->where(function ($q) use ($location) {
                $q->whereNull('Location')->orWhere('Location', $location);
            })
            ->where(function ($q) use ($company) {
                $q->whereNull('Company_ID')->orWhere('Company_ID', $company);
            })
            ->where(function ($q) use ($categoryId) {
                $q->whereNull('Category_ID');
                if ($categoryId) {
                    $q->orWhere('Category_ID', $categoryId);
                }
            })
            ->where(function ($q) use ($subcategoryId) {
                $q->whereNull('Subcategory_ID');
                if ($subcategoryId) {
                    $q->orWhere('Subcategory_ID', $subcategoryId);
                }
            })
            ->where(function ($q) use ($priority) {
                $q->whereNull('Priority')->orWhere('Priority', $priority);
            })
            ->orderByRaw('CASE WHEN Category_ID IS NULL THEN 1 ELSE 0 END')
            ->orderByRaw('CASE WHEN Subcategory_ID IS NULL THEN 1 ELSE 0 END')
            ->orderByRaw('CASE WHEN Priority IS NULL THEN 1 ELSE 0 END')
            ->orderBy('Sort_Order')
            ->orderBy('ID')
            ->first();
    }

    protected function applyAssignment($ticketId, $type, $teamId, $userId, $supplierId, $assignedBy, $reason, Carbon $now, $keepStatus = false)
    {
        DB::table('ga_ticket_assignment')
            ->where('Ticket_ID', (int) $ticketId)
            ->where('Is_Current', 1)
            ->update(['Is_Current' => 0]);

        DB::table('ga_ticket_assignment')->insert([
            'Ticket_ID' => (int) $ticketId,
            'Assignee_Type' => $type,
            'Team_ID' => $teamId,
            'User_Id' => $userId,
            'Supplier_ID' => $supplierId,
            'Assigned_By' => $assignedBy,
            'Assigned_At' => $now,
            'Reason' => mb_substr((string) $reason, 0, 2000),
            'Is_Current' => 1,
            'Created_At' => $now,
        ]);

        DB::table('ga_ticket')->where('Ticket_ID', (int) $ticketId)->update([
            'Assignee_Type' => $type,
            'Assignee_Team_ID' => $teamId,
            'Assignee_User_Id' => $userId,
            'Assignee_Supplier_ID' => $supplierId,
            'Updated_By' => $assignedBy,
            'Updated_At' => $now,
        ]);

        $ticket = DB::table('ticket')->where('ID', (int) $ticketId)->first();
        $from = $ticket ? strtoupper((string) $ticket->Ticket_Status) : null;
        if (!$keepStatus && $ticket && $from !== self::STATUS_ASSIGNED && !in_array($from, [self::STATUS_CLOSED, self::STATUS_REJECTED], true)) {
            DB::table('ticket')->where('ID', (int) $ticketId)->update(['Ticket_Status' => self::STATUS_ASSIGNED]);
            $this->insertHistory((int) $ticketId, $from, self::STATUS_ASSIGNED, (string) $reason, $assignedBy, $now);
        }

        $fresh = $this->find($ticketId);
        if ($fresh) {
            $topic = strtoupper((string) ($fresh->Domain_Type ?? 'GA')) === self::DOMAIN_IT ? 'it_ticket' : 'ga_ticket';
            $label = strtoupper((string) ($fresh->Domain_Type ?? 'GA')) === self::DOMAIN_IT ? 'IT' : 'GA';
            if ($userId) {
                $this->notifications->notifyUser(
                    (int) $userId,
                    'Tiket ' . $label . ' ditugaskan: ' . $fresh->Ticket_No,
                    'Anda ditugaskan menangani tiket "' . $fresh->Ticket_Subject . '".',
                    $topic,
                    ['Ticket_ID' => (int) $ticketId, 'Ticket_No' => $fresh->Ticket_No]
                );
            }
            if ($teamId) {
                $this->notifications->notifyTeam(
                    (int) $teamId,
                    'Tiket ' . $label . ' ditugaskan: ' . $fresh->Ticket_No,
                    'Tim Anda ditugaskan menangani tiket "' . $fresh->Ticket_Subject . '".',
                    $topic,
                    ['Ticket_ID' => (int) $ticketId, 'Ticket_No' => $fresh->Ticket_No]
                );
            }
        }
    }

    protected function insertHistory($ticketId, $from, $to, $note, $changedBy, Carbon $now)
    {
        DB::table('ga_ticket_status_history')->insert([
            'Ticket_ID' => (int) $ticketId,
            'From_Status' => $from,
            'To_Status' => $to,
            'Note' => $note,
            'Changed_By' => $changedBy,
            'Changed_At' => $now,
            'Created_At' => $now,
        ]);
    }

    protected function insertSlaEvent($ticketId, $type, Carbon $now, $notifiedTo)
    {
        DB::table('ga_ticket_sla_event')->insert([
            'Ticket_ID' => (int) $ticketId,
            'Event_Type' => $type,
            'Event_At' => $now,
            'Notified_To' => $notifiedTo,
            'Created_At' => $now,
        ]);
    }

    protected function slaEventExists($ticketId, $type)
    {
        return DB::table('ga_ticket_sla_event')
            ->where('Ticket_ID', (int) $ticketId)
            ->where('Event_Type', $type)
            ->exists();
    }

    protected function assertGaTicket($ticket)
    {
        if (!$ticket || (string) $ticket->Module !== 'GA') {
            throw new InvalidArgumentException('Tiket GA tidak ditemukan.');
        }
    }
}