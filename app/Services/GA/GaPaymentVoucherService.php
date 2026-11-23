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

            $supplierId = !empty($data['Supplier_ID'])
                ? (int) $data['Supplier_ID']
                : (!empty($ga->Preferred_Vendor_ID) ? (int) $ga->Preferred_Vendor_ID : null);

            $supplier = $supplierId
                ? DB::table('supplier')->where('ID', $supplierId)->first()
                : null;

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
            $description = trim((string) ($data['Description'] ?? $ticket->Ticket_Subject));
            $workOrderId = !empty($data['Work_Order_ID']) ? (int) $data['Work_Order_ID'] : null;

            $now = Carbon::now();
            $voucherNo = $this->generateVoucherNo($now);
            $attachmentPath = null;

            if ($attachmentFile instanceof UploadedFile) {
                $attachmentPath = $this->storeVendorFile(
                    $attachmentFile,
                    'ga_voucher_att_' . $ticketId,
                    'ga-voucher-attachment',
                    $voucherNo,
                    'Lampiran Pendukung Voucher ' . $voucherNo
                );
            }

            $voucherId = (int) DB::table('ga_payment_voucher')->insertGetId([
                'Voucher_No'        => $voucherNo,
                'Ticket_ID'         => $ticketId,
                'Work_Order_ID'     => $workOrderId,
                'Location'          => trim((string) ($ticket->Location ?: $user->Location ?: 'GEU-HO')),
                'Company_ID'        => trim((string) ($ticket->Company_ID ?: $user->Company_ID ?: 'GEU')),
                'Department'        => trim((string) ($ticket->Department ?: $ga->Reporter_Department ?: $user->Department ?: 'HCGA')),
                'Cost_Center'       => trim((string) ($ga->Cost_Center ?? '')),
                'Payee_Type'        => $payeeType,
                'Supplier_ID'       => $supplier ? (int) $supplier->ID : null,
                'Payee_Name'        => mb_substr($payeeName, 0, 150),
                'Bank_Name'         => $bankName !== '' ? mb_substr($bankName, 0, 100) : null,
                'Bank_Account_No'   => $bankAccNo !== '' ? mb_substr($bankAccNo, 0, 80) : null,
                'Bank_Account_Name' => $bankAccName !== '' ? mb_substr($bankAccName, 0, 150) : null,
                'Payment_Method'    => $paymentMethod,
                'Amount'            => $amount,
                'Due_Date'          => $dueDate,
                'Description'       => $description,
                'Attachment_Path'   => $attachmentPath,
                'Status'            => 'FINANCE_SUBMITTED',
                'Created_By'        => (string) $user->username,
                'Created_At'        => $now,
                'Updated_By'        => (string) $user->username,
                'Updated_At'        => $now,
            ]);

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
                'Voucher Pembayaran ' . $voucherNo . ' (Rp ' . number_format($amount, 0, ',', '.') . ' - ' . $payeeName . ') dibuat & diteruskan ke Finance.',
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
            $ticketId = (int) $voucher->Ticket_ID;
            if ($ticketId > 0) {
                $ticket = DB::table('ticket')->where('ID', $ticketId)->lockForUpdate()->first();
                $ga = DB::table('ga_ticket')->where('Ticket_ID', $ticketId)->lockForUpdate()->first();
                if ($ticket && $ga) {
                    $from = strtoupper((string) $ticket->Ticket_Status);
                    DB::table('ticket')->where('ID', $ticketId)->update([
                        'Ticket_Status'   => TicketService::STATUS_PAID,
                        'Completion_Note' => mb_substr('Voucher ' . $voucher->Voucher_No . ' dibayar Finance (' . $paidDate . ')', 0, 150),
                    ]);
                    DB::table('ga_ticket')->where('Ticket_ID', $ticketId)->update([
                        'Resolved_At' => $ga->Resolved_At ?: $now,
                        'Updated_By'  => $username,
                        'Updated_At'  => $now,
                    ]);

                    $note = 'Pembayaran Voucher ' . $voucher->Voucher_No . ' telah dilunasi Finance pada ' . $paidDate
                        . ($paymentRef !== '' ? ' (Ref: ' . $paymentRef . ').' : '.');
                    $this->tickets->recordHistory(
                        $ticketId,
                        $from,
                        TicketService::STATUS_PAID,
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
