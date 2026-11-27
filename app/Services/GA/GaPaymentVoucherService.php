<?php

namespace App\Services\GA;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Service manajemen Voucher Pembayaran GA Order Pekerjaan & integrasi pelunasan Finance.
 */
class GaPaymentVoucherService
{
    /** @var TicketService */
    protected $tickets;

    /** @var GaNotificationService */
    protected $notifications;

    public function __construct(TicketService $tickets, GaNotificationService $notifications)
    {
        $this->tickets = $tickets;
        $this->notifications = $notifications;
    }

    public function createVoucher(
        int $ticketId,
        array $data,
        $user,
        ?UploadedFile $attachmentFile = null
    ) {
        $result = DB::transaction(function () use ($ticketId, $data, $user, $attachmentFile) {
            $ticket = DB::table('ticket')->where('ID', $ticketId)->lockForUpdate()->first();
            $ga = DB::table('ga_ticket')->where('Ticket_ID', $ticketId)->lockForUpdate()->first();

            if (!$ticket || !$ga || (string) $ticket->Module !== 'GA') {
                throw new InvalidArgumentException('Tiket GA tidak ditemukan.');
            }
            if (strtoupper((string) ($ga->Workflow_Mode ?? '')) !== TicketService::MODE_WORK_ORDER_VOUCHER) {
                throw new InvalidArgumentException('Tiket ini bukan mode Order Pekerjaan & Voucher.');
            }
            if (strtoupper((string) $ticket->Ticket_Status) !== TicketService::STATUS_READY_FOR_VOUCHER) {
                throw new InvalidArgumentException('Voucher hanya dapat dibuat setelah approval biaya selesai (READY_FOR_VOUCHER).');
            }
            if (!empty($ga->Payment_Voucher_ID)) {
                throw new InvalidArgumentException('Tiket ini sudah memiliki Voucher Pembayaran.');
            }

            if (!empty($ga->Budget_Approval_Request_ID)) {
                $budgetStatus = strtoupper((string) DB::table('approval_request')
                    ->where('ID', (int) $ga->Budget_Approval_Request_ID)
                    ->value('Status'));
                if ($budgetStatus !== 'APPROVED') {
                    throw new InvalidArgumentException('Approval biaya belum berstatus APPROVED.');
                }
            }

            $approvedLimit = round((float) ($ga->Verified_Amount ?: $ga->Estimated_Amount), 2);
            $amount = isset($data['Amount']) && (float) $data['Amount'] > 0
                ? round((float) $data['Amount'], 2)
                : $approvedLimit;

            if ($amount <= 0) {
                throw new InvalidArgumentException('Nominal voucher harus lebih dari 0.');
            }
            if ($approvedLimit > 0 && $amount > $approvedLimit) {
                throw new InvalidArgumentException(
                    'Nominal voucher (Rp ' . number_format($amount, 0, ',', '.')
                    . ') tidak boleh melebihi nominal verifikasi yang disetujui (Rp '
                    . number_format($approvedLimit, 0, ',', '.') . ').'
                );
            }

            $prepared = $this->prepareVoucherPayload(
                $data,
                !empty($ga->Preferred_Vendor_ID) ? (int) $ga->Preferred_Vendor_ID : null,
                (string) $ticket->Ticket_Subject
            );

            $now = Carbon::now();
            $voucherNo = $this->generateVoucherNo($now);
            $attachmentPath = $attachmentFile instanceof UploadedFile
                ? $this->storeVendorFile(
                    $attachmentFile,
                    'ga_voucher_att_' . $ticketId,
                    'ga-voucher-attachment',
                    $voucherNo,
                    'Lampiran Pendukung Voucher ' . $voucherNo
                )
                : null;

            $voucherId = (int) DB::table('ga_payment_voucher')->insertGetId([
                'Voucher_No'        => $voucherNo,
                'Ticket_ID'         => $ticketId,
                'Work_Order_ID'     => $workOrderId = !empty($data['Work_Order_ID']) ? (int) $data['Work_Order_ID'] : null,
                'Location'          => trim((string) ($ticket->Location ?: $user->Location ?: 'GEU-HO')),
                'Company_ID'        => trim((string) ($ticket->Company_ID ?: $user->Company_ID ?: 'GEU')),
                'Department'        => trim((string) ($ticket->Department ?: $ga->Reporter_Department ?: $user->Department ?: 'HCGA')),
                'Cost_Center'       => trim((string) ($ga->Cost_Center ?? '')),
                'Payee_Type'        => $prepared['payee_type'],
                'Supplier_ID'       => $prepared['supplier_id'],
                'Payee_Name'        => mb_substr($prepared['payee_name'], 0, 150),
                'Bank_Name'         => $prepared['bank_name'] !== '' ? mb_substr($prepared['bank_name'], 0, 100) : null,
                'Bank_Account_No'   => $prepared['bank_acc_no'] !== '' ? mb_substr($prepared['bank_acc_no'], 0, 80) : null,
                'Bank_Account_Name' => $prepared['bank_acc_name'] !== '' ? mb_substr($prepared['bank_acc_name'], 0, 150) : null,
                'Payment_Method'    => $prepared['payment_method'],
                'Amount'            => $amount,
                'Due_Date'          => $prepared['due_date'],
                'Description'       => $prepared['description'],
                'Attachment_Path'   => $attachmentPath,
                'Status'            => 'FINANCE_SUBMITTED',
                'Created_By'        => (string) $user->username,
                'Created_At'        => $now,
                'Updated_By'        => (string) $user->username,
                'Updated_At'        => $now,
            ]);

            if (\Illuminate\Support\Facades\Schema::hasTable('finance_hr_vouchers')) {
                app(\App\Services\Finance\FinanceHrVoucherService::class)->createOrSyncFromSource(
                    \App\Services\Finance\FinanceHrVoucherService::SOURCE_GA_WORK_ORDER,
                    $voucherId,
                    [
                        'Voucher_No'        => $voucherNo,
                        'Source_No'         => (string) $ticket->Ticket_No,
                        'Location'          => trim((string) ($ticket->Location ?: $user->Location ?: 'GEU-HO')),
                        'Company_ID'        => trim((string) ($ticket->Company_ID ?: $user->Company_ID ?: 'GEU')),
                        'Department'        => trim((string) ($ticket->Department ?: $ga->Reporter_Department ?: $user->Department ?: 'HCGA')),
                        'Expense_Category'  => 'GA ORDER PEKERJAAN',
                        'Requested_Channel' => strtoupper(trim((string) ($data['Voucher_Channel'] ?? 'DIRECT'))) === 'AP' ? 'AP' : 'DIRECT',
                        'Payment_Method'    => $prepared['payment_method'] === 'CASH' ? 'CASH' : 'BANK',
                        'Vendor_ID'         => $prepared['supplier_id'] ? (string) DB::table('supplier')->where('ID', $prepared['supplier_id'])->value('Supplier_ID') : '',
                        'Vendor_Name'       => $prepared['payee_name'],
                        'Bank_Name'         => $prepared['bank_name'],
                        'Bank_Acc'          => $prepared['bank_acc_no'],
                        'Bank_Acc_Name'     => $prepared['bank_acc_name'],
                        'Reference'         => trim((string) ($data['Reference'] ?? $ticket->Ticket_No)),
                        'PO_References'     => trim((string) ($data['PO_References'] ?? '')),
                        'Due_Date'          => $prepared['due_date'] ?: date('Y-m-d'),
                        'Subject_Payment'   => (string) $ticket->Ticket_Subject,
                        'Description'       => $prepared['description'],
                        'Attachment_Path'   => $attachmentPath,
                        'Subtotal'          => $amount,
                        'Total'             => $amount,
                    ],
                    (string) $user->username
                );
            }

            DB::table('ticket')->where('ID', $ticketId)->update([
                'Ticket_Status' => TicketService::STATUS_VOUCHER_CREATED,
            ]);
            DB::table('ga_ticket')->where('Ticket_ID', $ticketId)->update([
                'Payment_Voucher_ID' => $voucherId,
                'Updated_By'         => (string) $user->username,
                'Updated_At'         => $now,
            ]);

            $this->tickets->recordHistory(
                $ticketId,
                TicketService::STATUS_READY_FOR_VOUCHER,
                TicketService::STATUS_VOUCHER_CREATED,
                'Voucher Pembayaran ' . $voucherNo . ' (Rp ' . number_format($amount, 0, ',', '.') . ' - ' . $prepared['payee_name'] . ') dibuat & diteruskan ke Finance.',
                (string) $user->username,
                $now
            );

            return [
                'voucher_id'  => $voucherId,
                'voucher_no'  => $voucherNo,
                'reporter_id' => (int) ($ga->Reporter_User_Id ?? 0),
                'ticket_no'   => (string) $ticket->Ticket_No,
            ];
        });

        if (!empty($result['reporter_id'])) {
            $this->notifications->notifyUser(
                $result['reporter_id'],
                'Voucher Pembayaran Dibuat: ' . $result['voucher_no'],
                'Voucher untuk tiket ' . $result['ticket_no'] . ' diteruskan ke Finance untuk pembayaran.',
                'ga_ticket',
                ['Ticket_ID' => $ticketId, 'Voucher_ID' => $result['voucher_id']]
            );
        }

        return $this->findById($result['voucher_id']);
    }

    /**
     * Buat Voucher Pembayaran ke Finance langsung dari Work Order GA yang sudah disetujui dan memiliki biaya.
     */
    public function createVoucherFromWorkOrder(
        int $woId,
        array $data,
        $user,
        ?UploadedFile $attachmentFile = null
    ) {
        $result = DB::transaction(function () use ($woId, $data, $user, $attachmentFile) {
            $wo = DB::table('ga_work_order')->where('ID', $woId)->lockForUpdate()->first();
            if (!$wo) {
                throw new InvalidArgumentException('Work order tidak ditemukan.');
            }

            $woStatus = strtoupper((string) $wo->Status);
            if (!in_array($woStatus, [
                WorkOrderService::STATUS_APPROVED,
                WorkOrderService::STATUS_ASSIGNED,
                WorkOrderService::STATUS_IN_PROGRESS,
                WorkOrderService::STATUS_COMPLETED,
            ], true)) {
                throw new InvalidArgumentException('Voucher hanya dapat dibuat setelah Work Order berstatus APPROVED atau sedang berjalan/selesai.');
            }

            if (!empty($wo->Approval_Request_ID)) {
                $approvalStatus = strtoupper((string) DB::table('approval_request')
                    ->where('ID', (int) $wo->Approval_Request_ID)
                    ->value('Status'));
                if ($approvalStatus !== 'APPROVED') {
                    throw new InvalidArgumentException('Approval Work Order belum berstatus APPROVED.');
                }
            }

            $existingVoucher = DB::table('ga_payment_voucher')
                ->where('Work_Order_ID', $woId)
                ->where('Status', '!=', 'CANCELLED')
                ->exists();
            if ($existingVoucher) {
                throw new InvalidArgumentException('Work order ini sudah memiliki Voucher Pembayaran.');
            }

            $totalCost = (float) $wo->Vendor_Cost
                + (float) DB::table('ga_work_order_cost')->where('WO_ID', $woId)->sum('Amount')
                + (float) DB::table('ga_work_order_material')->where('WO_ID', $woId)->sum('Total_Cost');
            $totalCost = round($totalCost, 2);

            if ($totalCost <= 0) {
                throw new InvalidArgumentException('Work order belum memiliki total biaya (> 0) untuk diajukan ke Finance.');
            }

            $amount = isset($data['Amount']) && (float) $data['Amount'] > 0
                ? round((float) $data['Amount'], 2)
                : $totalCost;

            if ($amount <= 0) {
                throw new InvalidArgumentException('Nominal voucher harus lebih dari 0.');
            }
            if ($amount > $totalCost) {
                throw new InvalidArgumentException(
                    'Nominal voucher (Rp ' . number_format($amount, 0, ',', '.')
                    . ') tidak boleh melebihi total biaya Work Order (Rp '
                    . number_format($totalCost, 0, ',', '.') . ').'
                );
            }

            $prepared = $this->prepareVoucherPayload(
                $data,
                !empty($wo->Supplier_ID) ? (int) $wo->Supplier_ID : null,
                trim((string) $wo->WO_No . ' - ' . $wo->Title)
            );

            $now = Carbon::now();
            $voucherNo = $this->generateVoucherNo($now);
            $attachmentPath = $attachmentFile instanceof UploadedFile
                ? $this->storeVendorFile(
                    $attachmentFile,
                    'ga_voucher_wo_' . $woId,
                    'ga-voucher-attachment',
                    $voucherNo,
                    'Lampiran Pendukung Voucher ' . $voucherNo
                )
                : null;

            $ticketId = (int) ($wo->Ticket_ID ?? 0);
            $ticket = $ticketId > 0 ? DB::table('ticket')->where('ID', $ticketId)->lockForUpdate()->first() : null;
            $ga = $ticketId > 0 ? DB::table('ga_ticket')->where('Ticket_ID', $ticketId)->lockForUpdate()->first() : null;

            $voucherId = (int) DB::table('ga_payment_voucher')->insertGetId([
                'Voucher_No'        => $voucherNo,
                'Ticket_ID'         => $ticketId,
                'Work_Order_ID'     => $woId,
                'Location'          => trim((string) ($wo->Location ?: ($ticket ? $ticket->Location : '') ?: $user->Location ?: 'GEU-HO')),
                'Company_ID'        => trim((string) ($wo->Company_ID ?: ($ticket ? $ticket->Company_ID : '') ?: $user->Company_ID ?: 'GEU')),
                'Department'        => trim((string) ($wo->Department ?: ($ticket ? $ticket->Department : '') ?: $user->Department ?: 'HCGA')),
                'Cost_Center'       => trim((string) ($wo->Cost_Center ?: ($ga ? $ga->Cost_Center : '') ?: '')),
                'Payee_Type'        => $prepared['payee_type'],
                'Supplier_ID'       => $prepared['supplier_id'],
                'Payee_Name'        => mb_substr($prepared['payee_name'], 0, 150),
                'Bank_Name'         => $prepared['bank_name'] !== '' ? mb_substr($prepared['bank_name'], 0, 100) : null,
                'Bank_Account_No'   => $prepared['bank_acc_no'] !== '' ? mb_substr($prepared['bank_acc_no'], 0, 80) : null,
                'Bank_Account_Name' => $prepared['bank_acc_name'] !== '' ? mb_substr($prepared['bank_acc_name'], 0, 150) : null,
                'Payment_Method'    => $prepared['payment_method'],
                'Amount'            => $amount,
                'Due_Date'          => $prepared['due_date'],
                'Description'       => $prepared['description'],
                'Attachment_Path'   => $attachmentPath,
                'Status'            => 'FINANCE_SUBMITTED',
                'Created_By'        => (string) $user->username,
                'Created_At'        => $now,
                'Updated_By'        => (string) $user->username,
                'Updated_At'        => $now,
            ]);

            if (\Illuminate\Support\Facades\Schema::hasTable('finance_hr_vouchers')) {
                app(\App\Services\Finance\FinanceHrVoucherService::class)->createOrSyncFromSource(
                    \App\Services\Finance\FinanceHrVoucherService::SOURCE_GA_WORK_ORDER,
                    $voucherId,
                    [
                        'Voucher_No'        => $voucherNo,
                        'Source_No'         => (string) $wo->WO_No,
                        'Location'          => trim((string) ($wo->Location ?: ($ticket ? $ticket->Location : '') ?: $user->Location ?: 'GEU-HO')),
                        'Company_ID'        => trim((string) ($wo->Company_ID ?: ($ticket ? $ticket->Company_ID : '') ?: $user->Company_ID ?: 'GEU')),
                        'Department'        => trim((string) ($wo->Department ?: ($ticket ? $ticket->Department : '') ?: $user->Department ?: 'HCGA')),
                        'Expense_Category'  => 'GA ORDER PEKERJAAN',
                        'Requested_Channel' => strtoupper(trim((string) ($data['Voucher_Channel'] ?? 'DIRECT'))) === 'AP' ? 'AP' : 'DIRECT',
                        'Payment_Method'    => $prepared['payment_method'] === 'CASH' ? 'CASH' : 'BANK',
                        'Vendor_ID'         => $prepared['supplier_id'] ? (string) DB::table('supplier')->where('ID', $prepared['supplier_id'])->value('Supplier_ID') : '',
                        'Vendor_Name'       => $prepared['payee_name'],
                        'Bank_Name'         => $prepared['bank_name'],
                        'Bank_Acc'          => $prepared['bank_acc_no'],
                        'Bank_Acc_Name'     => $prepared['bank_acc_name'],
                        'Reference'         => trim((string) ($data['Reference'] ?? $wo->WO_No)),
                        'PO_References'     => trim((string) ($data['PO_References'] ?? '')),
                        'Due_Date'          => $prepared['due_date'] ?: date('Y-m-d'),
                        'Subject_Payment'   => trim((string) $wo->WO_No . ' - ' . $wo->Title),
                        'Description'       => $prepared['description'],
                        'Attachment_Path'   => $attachmentPath,
                        'Subtotal'          => $amount,
                        'Total'             => $amount,
                    ],
                    (string) $user->username
                );
            }

            $historyNote = 'Voucher Pembayaran ' . $voucherNo . ' (Rp ' . number_format($amount, 0, ',', '.')
                . ' - ' . $prepared['payee_name'] . ') dibuat dari Work Order & diteruskan ke Finance.';

            DB::table('ga_work_order_status_history')->insert([
                'WO_ID'       => $woId,
                'From_Status' => (string) $wo->Status,
                'To_Status'   => (string) $wo->Status,
                'Note'        => $historyNote,
                'Changed_By'  => (string) $user->username,
                'Changed_At'  => $now,
                'Created_At'  => $now,
            ]);

            if ($ticket && $ga) {
                $fromStatus = strtoupper((string) $ticket->Ticket_Status);
                $toStatus = $fromStatus;

                if (strtoupper((string) ($ga->Workflow_Mode ?? '')) === TicketService::MODE_WORK_ORDER_VOUCHER
                    && empty($ga->Payment_Voucher_ID)
                ) {
                    $gaUpdates = [
                        'Payment_Voucher_ID' => $voucherId,
                        'Updated_By'         => (string) $user->username,
                        'Updated_At'         => $now,
                    ];
                    if ($fromStatus === TicketService::STATUS_READY_FOR_VOUCHER) {
                        $toStatus = TicketService::STATUS_VOUCHER_CREATED;
                        DB::table('ticket')->where('ID', $ticketId)->update([
                            'Ticket_Status' => $toStatus,
                        ]);
                    }
                    DB::table('ga_ticket')->where('Ticket_ID', $ticketId)->update($gaUpdates);
                }

                $this->tickets->recordHistory(
                    $ticketId,
                    $fromStatus,
                    $toStatus,
                    'WO ' . $wo->WO_No . ': ' . $historyNote,
                    (string) $user->username,
                    $now
                );
            }

            return [
                'voucher_id'  => $voucherId,
                'voucher_no'  => $voucherNo,
                'ticket_id'   => $ticketId,
                'reporter_id' => $ga ? (int) ($ga->Reporter_User_Id ?? 0) : 0,
                'wo_no'       => (string) $wo->WO_No,
            ];
        });

        if (!empty($result['reporter_id'])) {
            $this->notifications->notifyUser(
                $result['reporter_id'],
                'Voucher Pembayaran Dibuat: ' . $result['voucher_no'],
                'Voucher untuk Work Order ' . $result['wo_no'] . ' diteruskan ke Finance untuk pembayaran.',
                'ga_work_order',
                ['WO_ID' => $woId, 'Ticket_ID' => $result['ticket_id'], 'Voucher_ID' => $result['voucher_id']]
            );
        }

        return $this->findById($result['voucher_id']);
    }

    public function markPaid(
        int $voucherId,
        array $data,
        string $username,
        ?UploadedFile $proofFile = null
    ) {
        $result = DB::transaction(function () use ($voucherId, $data, $username, $proofFile) {
            $voucher = DB::table('ga_payment_voucher')->where('ID', $voucherId)->lockForUpdate()->first();
            if (!$voucher) {
                throw new InvalidArgumentException('Voucher pembayaran GA tidak ditemukan.');
            }
            if (strtoupper((string) $voucher->Status) !== 'FINANCE_SUBMITTED') {
                throw new InvalidArgumentException('Voucher ini sudah diproses (status saat ini: ' . $voucher->Status . ').');
            }

            $paidDate = !empty($data['Paid_Date'])
                ? date('Y-m-d', strtotime((string) $data['Paid_Date']))
                : date('Y-m-d');
            $paymentRef = trim((string) ($data['Payment_Ref'] ?? $data['Bukti_Bayar'] ?? ''));

            $proofPath = null;
            if ($proofFile instanceof UploadedFile) {
                $proofPath = $this->storeVendorFile(
                    $proofFile,
                    'ga_voucher_paid_' . $voucherId,
                    'ga-voucher-receipt',
                    (string) $voucher->Voucher_No,
                    'Bukti Pembayaran Voucher ' . $voucher->Voucher_No
                );
            }

            if ($paymentRef === '' && !$proofPath) {
                throw new InvalidArgumentException('Nomor referensi pembayaran atau file bukti bayar wajib diisi.');
            }

            $now = Carbon::now();
            DB::table('ga_payment_voucher')->where('ID', $voucherId)->update([
                'Status'             => 'PAID',
                'Paid_Date'          => $paidDate,
                'Payment_Ref'        => $paymentRef !== '' ? mb_substr($paymentRef, 0, 150) : null,
                'Payment_Proof_Path' => $proofPath,
                'Paid_By'            => $username,
                'Paid_At'            => $now,
                'Updated_By'         => $username,
                'Updated_At'         => $now,
            ]);

            $notifyIds = [];
            $note = 'Pembayaran Voucher ' . $voucher->Voucher_No . ' telah dilunasi Finance pada ' . $paidDate
                . ($paymentRef !== '' ? ' (Ref: ' . $paymentRef . ').' : '.');

            $workOrderId = (int) ($voucher->Work_Order_ID ?? 0);
            if ($workOrderId > 0) {
                $wo = DB::table('ga_work_order')->where('ID', $workOrderId)->lockForUpdate()->first();
                if ($wo) {
                    DB::table('ga_work_order_status_history')->insert([
                        'WO_ID'       => $workOrderId,
                        'From_Status' => (string) $wo->Status,
                        'To_Status'   => (string) $wo->Status,
                        'Note'        => $note,
                        'Changed_By'  => $username,
                        'Changed_At'  => $now,
                        'Created_At'  => $now,
                    ]);
                }
            }

            $ticketId = (int) $voucher->Ticket_ID;
            if ($ticketId > 0) {
                $ticket = DB::table('ticket')->where('ID', $ticketId)->lockForUpdate()->first();
                $ga = DB::table('ga_ticket')->where('Ticket_ID', $ticketId)->lockForUpdate()->first();
                if ($ticket && $ga) {
                    $from = strtoupper((string) $ticket->Ticket_Status);
                    $isVoucherMode = strtoupper((string) ($ga->Workflow_Mode ?? '')) === TicketService::MODE_WORK_ORDER_VOUCHER;
                    $toStatus = $isVoucherMode ? TicketService::STATUS_PAID : $from;

                    if ($isVoucherMode) {
                        DB::table('ticket')->where('ID', $ticketId)->update([
                            'Ticket_Status'   => TicketService::STATUS_PAID,
                            'Completion_Note' => mb_substr('Voucher ' . $voucher->Voucher_No . ' dibayar Finance (' . $paidDate . ')', 0, 150),
                        ]);
                        DB::table('ga_ticket')->where('Ticket_ID', $ticketId)->update([
                            'Resolved_At' => $ga->Resolved_At ?: $now,
                            'Updated_By'  => $username,
                            'Updated_At'  => $now,
                        ]);
                    }

                    $this->tickets->recordHistory(
                        $ticketId,
                        $from,
                        $toStatus,
                        $note,
                        $username,
                        $now
                    );

                    if (!empty($ga->Reporter_User_Id)) {
                        $notifyIds[] = (int) $ga->Reporter_User_Id;
                    }
                    if (!empty($ga->GA_Verified_By)) {
                        $verifierId = DB::table('users')->where('username', $ga->GA_Verified_By)->value('id');
                        if ($verifierId) {
                            $notifyIds[] = (int) $verifierId;
                        }
                    }
                }
            }

            if (!empty($voucher->Created_By)) {
                $creatorId = DB::table('users')->where('username', $voucher->Created_By)->value('id');
                if ($creatorId) {
                    $notifyIds[] = (int) $creatorId;
                }
            }

            return [
                'ticket_id'  => $ticketId,
                'voucher_no' => (string) $voucher->Voucher_No,
                'paid_date'  => $paidDate,
                'notify_ids' => $notifyIds,
            ];
        });

        if (!empty($result['notify_ids'])) {
            $this->notifications->notifyUsers(
                $result['notify_ids'],
                'Voucher Dibayar Finance: ' . $result['voucher_no'],
                'Voucher ' . $result['voucher_no'] . ' berstatus PAID pada ' . $result['paid_date'] . '.',
                'ga_ticket',
                ['Ticket_ID' => $result['ticket_id'], 'Voucher_ID' => $voucherId]
            );
        }

        return $this->findById($voucherId);
    }

    public function findById(int $voucherId)
    {
        return $this->baseQuery()->where('v.ID', $voucherId)->first();
    }

    public function findByTicketId(int $ticketId)
    {
        return $this->baseQuery()->where('v.Ticket_ID', $ticketId)->orderBy('v.ID', 'desc')->first();
    }

    public function findByWorkOrderId(int $woId)
    {
        return $this->baseQuery()->where('v.Work_Order_ID', $woId)->orderBy('v.ID', 'desc')->first();
    }

    protected function prepareVoucherPayload(array $data, ?int $defaultSupplierId, string $defaultDescription): array
    {
        $supplierId = !empty($data['Supplier_ID']) ? (int) $data['Supplier_ID'] : $defaultSupplierId;
        $supplier = $supplierId ? DB::table('supplier')->where('ID', $supplierId)->first() : null;

        $payeeType = strtoupper(trim((string) ($data['Payee_Type'] ?? ($supplier ? 'VENDOR' : 'OTHER'))));
        if (!in_array($payeeType, ['VENDOR', 'INTERNAL', 'STAFF', 'OTHER'], true)) {
            $payeeType = 'VENDOR';
        }

        $payeeName = trim((string) ($data['Payee_Name'] ?? ($supplier ? $supplier->Company_Name : '')));
        if ($payeeName === '') {
            throw new InvalidArgumentException('Nama penerima pembayaran / vendor wajib diisi.');
        }

        $bankName = trim((string) ($data['Bank_Name'] ?? ($supplier ? ($supplier->Bank_Rupiah ?? '') : '')));
        $bankAccNo = trim((string) ($data['Bank_Account_No'] ?? ($supplier ? ($supplier->Bank_Rupiah_Account ?? '') : '')));
        $bankAccName = trim((string) ($data['Bank_Account_Name'] ?? ($supplier ? ($supplier->Bank_Rupiah_Name ?? $supplier->Company_Name) : '')));

        $paymentMethod = strtoupper(trim((string) ($data['Payment_Method'] ?? 'TRANSFER')));
        if (!in_array($paymentMethod, ['TRANSFER', 'CASH', 'GIRO'], true)) {
            $paymentMethod = 'TRANSFER';
        }

        $dueDate = !empty($data['Due_Date']) ? date('Y-m-d', strtotime((string) $data['Due_Date'])) : null;
        $description = trim((string) ($data['Description'] ?? $defaultDescription));

        return [
            'supplier_id'    => $supplier ? (int) $supplier->ID : null,
            'payee_type'     => $payeeType,
            'payee_name'     => $payeeName,
            'bank_name'      => $bankName,
            'bank_acc_no'    => $bankAccNo,
            'bank_acc_name'  => $bankAccName,
            'payment_method' => $paymentMethod,
            'due_date'       => $dueDate,
            'description'    => $description,
        ];
    }

    public function pendingForFinance()
    {
        return $this->baseQuery()
            ->where('v.Status', 'FINANCE_SUBMITTED')
            ->orderBy('v.Created_At', 'desc')
            ->orderBy('v.ID', 'desc')
            ->get();
    }

    public function historyForFinance(int $limit = 40)
    {
        return $this->baseQuery()
            ->whereIn('v.Status', ['PAID', 'CANCELLED'])
            ->orderBy('v.Paid_At', 'desc')
            ->orderBy('v.ID', 'desc')
            ->limit($limit)
            ->get();
    }

    protected function baseQuery()
    {
        return DB::table('ga_payment_voucher as v')
            ->join('ticket as t', 't.ID', '=', 'v.Ticket_ID')
            ->join('ga_ticket as g', 'g.Ticket_ID', '=', 'v.Ticket_ID')
            ->leftJoin('supplier as s', 's.ID', '=', 'v.Supplier_ID')
            ->leftJoin('ga_work_order as wo', 'wo.ID', '=', 'v.Work_Order_ID')
            ->select([
                'v.*',
                't.Ticket_No',
                't.Ticket_Subject',
                't.Ticket_Status',
                't.Reporter',
                't.Created_Date as Ticket_Created_Date',
                'g.Estimated_Amount',
                'g.Verified_Amount',
                'g.GA_Verified_By',
                'g.GA_Verified_At',
                'g.GA_Verification_Note',
                'g.Reporter_Name',
                'g.Dept_Approval_Request_ID',
                'g.Budget_Approval_Request_ID',
                's.Company_Name as Supplier_Company_Name',
                's.Supplier_ID as Supplier_Code',
                'wo.WO_No as Work_Order_No',
            ]);
    }

    protected function generateVoucherNo(Carbon $now): string
    {
        $prefix = 'VCH/GA/' . $now->format('Ym') . '-';
        $latest = DB::table('ga_payment_voucher')
            ->where('Voucher_No', 'like', $prefix . '%')
            ->orderBy('ID', 'desc')
            ->lockForUpdate()
            ->value('Voucher_No');

        $next = 1;
        if ($latest && preg_match('/-(\d+)$/', (string) $latest, $m)) {
            $next = ((int) $m[1]) + 1;
        }

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    protected function storeVendorFile(
        UploadedFile $file,
        string $namePrefix,
        string $topic,
        string $mapTo,
        string $description
    ): string {
        if (!$file->isValid()) {
            throw new InvalidArgumentException('File yang diunggah tidak valid.');
        }

        $ext = strtolower((string) $file->getClientOriginalExtension());
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx'], true)) {
            throw new InvalidArgumentException('Format file .' . $ext . ' tidak diizinkan.');
        }
        if ((int) $file->getSize() > 5 * 1024 * 1024) {
            throw new InvalidArgumentException('Ukuran file maksimal 5 MB.');
        }

        $year = date('Y');
        $month = date('m');
        $targetDir = public_path("filevendor/{$year}/{$month}");
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $newName = $namePrefix . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $file->move($targetDir, $newName);
        $relPath = "{$year}/{$month}/{$newName}";

        DB::table('meta_files')->insert([
            'Filename'    => $newName,
            'Path'        => $relPath,
            'Extension'   => $ext,
            'Description' => mb_substr($description, 0, 250),
            'Topic'       => $topic,
            'Map_To'      => mb_substr($mapTo, 0, 100),
            'Created_At'  => now(),
        ]);

        return $relPath;
    }
}
