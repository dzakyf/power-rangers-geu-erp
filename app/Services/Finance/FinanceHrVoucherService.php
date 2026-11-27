<?php

namespace App\Services\Finance;

use App\Services\Approval\ApprovalWorkflowService;
use App\Services\GA\GaPaymentVoucherService;
use App\Services\HCGA\AssetMaintenanceScheduleService;
use App\Services\HCGA\VehicleFleetService;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

/**
 * Orkestrator terpusat untuk siklus hidup Voucher & AP Manual:
 * - Pembuatan dari Hulu (Manual + Lookup PO/Vendor, Perawatan Aset, GA Order Pekerjaan, Pajak STNK/KEUR)
 * - Approval berjenjang via ApprovalWorkflowService (HR_VOUCHER)
 * - Verifikasi, Koreksi, Konversi Jalur (DIRECT <-> AP), Multi-Debet/Multi-Kredit COA, & Tenor Manual di Finance
 * - Pelunasan Fleksibel Treasury (Partial / Full) & Sinkronisasi ke legacy + jurnal final `ho`
 */
class FinanceHrVoucherService
{
    public const SOURCE_ASSET_MAINTENANCE = 'ASSET_MAINTENANCE';
    public const SOURCE_GA_WORK_ORDER     = 'GA_WORK_ORDER';
    public const SOURCE_VEHICLE_TAX       = 'VEHICLE_TAX';
    public const SOURCE_HRGA_MANUAL       = 'HRGA_MANUAL';
    public const WORKFLOW_REQUEST_TYPE    = 'HR_VOUCHER';

    /** @var FinanceCoaService */
    protected $coaService;

    /** @var FinanceVoucherLedgerService */
    protected $ledgerService;

    /** @var ApprovalWorkflowService */
    protected $workflowService;

    public function __construct(
        FinanceCoaService $coaService,
        FinanceVoucherLedgerService $ledgerService,
        ApprovalWorkflowService $workflowService = null
    ) {
        $this->coaService = $coaService;
        $this->ledgerService = $ledgerService;
        $this->workflowService = $workflowService ?: app(ApprovalWorkflowService::class);
    }

    /**
     * Cari data Purchase Order dari tabel `purchase_order` untuk referensi AP Manual HRGA.
     */
    public function searchPurchaseOrders(?string $keyword = null, int $limit = 30): array
    {
        $query = DB::table('purchase_order as po')
            ->leftJoin('supplier as s', function ($join) {
                $join->on(
                    DB::raw('CONVERT(po.Supplier USING utf8mb4) COLLATE utf8mb4_unicode_ci'),
                    '=',
                    DB::raw('CONVERT(s.Supplier_ID USING utf8mb4) COLLATE utf8mb4_unicode_ci')
                );
            })
            ->whereNotIn(DB::raw("UPPER(COALESCE(po.Status, ''))"), ['CANCEL', 'CANCELED', 'REJECTED', 'VOID']);

        $kw = trim((string) $keyword);
        if ($kw !== '') {
            $like = '%' . $kw . '%';
            $query->where(function ($q) use ($like) {
                $q->where('po.PO_No', 'LIKE', $like)
                    ->orWhere('po.PR_No', 'LIKE', $like)
                    ->orWhere('po.Supplier', 'LIKE', $like)
                    ->orWhere('s.Company_Name', 'LIKE', $like)
                    ->orWhere('po.Subject_Payment', 'LIKE', $like);
            });
        }

        return $query->select(
                'po.ID',
                'po.PO_No',
                'po.PR_No',
                'po.PO_Date',
                'po.Location',
                'po.Department',
                'po.Supplier as Vendor_ID',
                's.Company_Name as Vendor_Name',
                'po.Supplier_PIC as PIC_Vendor',
                'po.Term_Payment',
                'po.Pay_Term',
                'po.Subtotal',
                'po.Discount',
                'po.Advance_Value as DP',
                'po.PPN',
                'po.PPH',
                'po.Delivery_Fee as Delivery_Costs',
                'po.Management_Fee',
                'po.Total',
                'po.Total_Paid',
                'po.Subject_Payment',
                'po.Remark',
                'po.Status',
                's.Bank_Rupiah as Bank_Name',
                's.Bank_Rupiah_Account as Bank_Acc',
                's.Bank_Rupiah_Name as Bank_Acc_Name'
            )
            ->orderBy('po.ID', 'DESC')
            ->limit(max(1, min($limit, 100)))
            ->get()
            ->all();
    }

    /**
     * Cari master vendor aktif dari tabel `supplier`.
     */
    public function searchSuppliers(?string $keyword = null, int $limit = 30): array
    {
        $query = DB::table('supplier')
            ->where(function ($q) {
                $q->where('Status', 'ACTIVE')
                    ->orWhereNull('Status')
                    ->orWhere('Status', '');
            });

        $kw = trim((string) $keyword);
        if ($kw !== '') {
            $like = '%' . $kw . '%';
            $query->where(function ($q) use ($like) {
                $q->where('Supplier_ID', 'LIKE', $like)
                    ->orWhere('Company_Name', 'LIKE', $like)
                    ->orWhere('Contact_Person', 'LIKE', $like);
            });
        }

        return $query->select(
                'ID',
                'Supplier_ID',
                'Company_Name',
                'Contact_Person',
                'Bank_Rupiah as Bank_Name',
                'Bank_Rupiah_Account as Bank_Acc',
                'Bank_Rupiah_Name as Bank_Acc_Name'
            )
            ->orderBy('Company_Name', 'ASC')
            ->limit(max(1, min($limit, 100)))
            ->get()
            ->all();
    }

    /**
     * Buat pengajuan Voucher / AP Manual langsung dari modul Hulu HRGA.
     */
    public function createManualVoucher(array $data, $user, ?UploadedFile $attachmentFile = null): object
    {
        return DB::transaction(function () use ($data, $user, $attachmentFile) {
            $username = (string) ($user->username ?? 'system');
            $channel = strtoupper(trim((string) ($data['Requested_Channel'] ?? 'AP')));
            if (!in_array($channel, ['DIRECT', 'AP'], true)) {
                $channel = 'AP';
            }

            $amounts = $this->normalizeAmounts($data);
            if ($amounts['Total'] <= 0) {
                throw new InvalidArgumentException('Total nominal voucher harus lebih dari 0.');
            }

            $invType = strtoupper(trim((string) ($data['Inv_Type'] ?? 'SINGLE'))) === 'PARTIAL' ? 'PARTIAL' : 'SINGLE';
            $vendorId = trim((string) ($data['Vendor_ID'] ?? ''));
            $vendorName = trim((string) ($data['Vendor_Name'] ?? ''));
            if ($vendorName === '' && $vendorId !== '') {
                $vendorName = (string) DB::table('supplier')->where('Supplier_ID', $vendorId)->value('Company_Name');
            }
            if ($channel === 'AP' && $vendorName === '') {
                throw new InvalidArgumentException('Nama Vendor wajib diisi untuk pengajuan AP (Hutang Dagang).');
            }

            $reference = trim((string) ($data['Reference'] ?? ''));
            if ($reference !== '' && $vendorId !== '' && $invType !== 'PARTIAL') {
                $this->assertNoDuplicateVendorReference($vendorId, $reference);
            }

            $now = Carbon::now();
            $voucherNo = $this->generateVoucherNo('VCH/', $now);
            $attachmentPath = $attachmentFile instanceof UploadedFile
                ? $this->storeVendorFile($attachmentFile, 'voucher_att', 'voucher-attachment', $voucherNo, 'Lampiran ' . $voucherNo)
                : trim((string) ($data['Attachment_Path'] ?? ''));

            $dueDate = !empty($data['Due_Date']) ? date('Y-m-d', strtotime((string) $data['Due_Date'])) : date('Y-m-d');
            $tenors = $this->normalizeTenorsInput($data['Tenors'] ?? [], $amounts['Total'], $dueDate, $channel === 'AP');

            $voucherId = (int) DB::table('finance_hr_vouchers')->insertGetId([
                'Voucher_No'        => $voucherNo,
                'Source_Module'     => self::SOURCE_HRGA_MANUAL,
                'Source_ID'         => null,
                'Source_No'         => $voucherNo,
                'Location'          => mb_substr(trim((string) ($data['Location'] ?? ($user->Location ?? 'GEU-HO'))), 0, 25),
                'Company_ID'        => mb_substr(trim((string) ($data['Company_ID'] ?? ($user->Company_ID ?? 'GEU'))), 0, 25),
                'Department'        => mb_substr(trim((string) ($data['Department'] ?? ($user->Department ?? 'HCGA'))), 0, 50),
                'Expense_Category'  => mb_substr(trim((string) ($data['Expense_Category'] ?? 'OPERASIONAL')), 0, 80),
                'Requested_Channel' => $channel,
                'Final_Channel'     => null,
                'Is_Converted_To_AP'=> 0,
                'Payment_Method'    => trim((string) ($data['Payment_Method'] ?? 'BANK')) ?: 'BANK',
                'Inv_Type'          => $invType,
                'PO_References'     => mb_substr(trim((string) ($data['PO_References'] ?? '')), 0, 150) ?: null,
                'PR_No'             => mb_substr(trim((string) ($data['PR_No'] ?? '')), 0, 60) ?: null,
                'Vendor_ID'         => mb_substr($vendorId, 0, 40) ?: null,
                'Vendor_Name'       => mb_substr($vendorName, 0, 150) ?: null,
                'PIC_Vendor'        => mb_substr(trim((string) ($data['PIC_Vendor'] ?? $username)), 0, 80),
                'Bank_Type'         => mb_substr(trim((string) ($data['Bank_Type'] ?? 'DIRECT TRANSFER')), 0, 30),
                'Bank_Name'         => mb_substr(trim((string) ($data['Bank_Name'] ?? '')), 0, 80) ?: null,
                'Bank_Acc'          => mb_substr(trim((string) ($data['Bank_Acc'] ?? '')), 0, 60) ?: null,
                'Bank_Acc_Name'     => mb_substr(trim((string) ($data['Bank_Acc_Name'] ?? '')), 0, 150) ?: null,
                'Reference'         => mb_substr($reference, 0, 150) ?: null,
                'Inv_Vendor_Date'   => !empty($data['Inv_Vendor_Date']) ? date('Y-m-d', strtotime((string) $data['Inv_Vendor_Date'])) : date('Y-m-d'),
                'Date_Received'     => !empty($data['Date_Received']) ? date('Y-m-d', strtotime((string) $data['Date_Received'])) : date('Y-m-d'),
                'Due_Date'          => $dueDate,
                'Subject_Payment'   => mb_substr(trim((string) ($data['Subject_Payment'] ?? 'Pengajuan Voucher')), 0, 255),
                'Description'       => trim((string) ($data['Description'] ?? '')),
                'Attachment_Path'   => $attachmentPath ?: null,
                'Subtotal'          => $amounts['Subtotal'],
                'DP'                => $amounts['DP'],
                'Management_Fee'    => $amounts['Management_Fee'],
                'Delivery_Costs'    => $amounts['Delivery_Costs'],
                'PPN_Type'          => $amounts['PPN_Type'],
                'PPN'               => $amounts['PPN'],
                'PPH_Type'          => $amounts['PPH_Type'],
                'PPH'               => $amounts['PPH'],
                'Materai'           => $amounts['Materai'],
                'Total'             => $amounts['Total'],
                'Paid'              => 0.00,
                'Remain_Val'        => $amounts['Total'],
                'Status'            => 'WAITING_APPROVAL',
                'Created_By'        => mb_substr($username, 0, 50),
                'Updated_By'        => mb_substr($username, 0, 50),
                'Created_At'        => $now,
                'Updated_At'        => $now,
            ]);

            if (!empty($tenors)) {
                $this->replaceTenors($voucherId, $tenors);
            }

            $rawLines = $data['Lines'] ?? null;
            if (is_string($rawLines)) {
                $rawLines = json_decode($rawLines, true);
            }
            if (is_array($rawLines) && !empty($rawLines)) {
                $validatedLines = $this->ledgerService->validateBalancedLines($rawLines);
                $this->replaceLines($voucherId, $validatedLines['lines']);
            }

            $requesterId = $this->resolveUserId($user);
            $createdRow = $this->findById($voucherId);
            $approvalReq = $this->workflowService->submit([
                'Request_No'  => $voucherNo,
                'Description' => (string) ($createdRow->Subject_Payment ?: $voucherNo),
            ], [
                'Request_Type' => self::WORKFLOW_REQUEST_TYPE,
                'Location'     => (string) ($createdRow->Location ?: 'GEU-HO'),
                'Company_ID'   => (string) ($createdRow->Company_ID ?: 'GEU'),
                'Department'   => (string) ($createdRow->Department ?: 'HCGA'),
                'Amount'       => (float) $amounts['Total'],
            ], $requesterId);

            DB::table('approval_request')->where('ID', (int) $approvalReq->ID)->update([
                'Map_To' => $voucherId,
            ]);

            DB::table('finance_hr_vouchers')->where('ID', $voucherId)->update([
                'Approval_Request_ID' => (int) $approvalReq->ID,
                'Updated_At'          => $now,
            ]);

            if (strtoupper((string) $approvalReq->Status) === 'APPROVED') {
                $this->onWorkflowApproved($voucherId);
            }

            return $this->findById($voucherId);
        });
    }

    /**
     * Revisi & kirim ulang pengajuan HRGA Manual yang berstatus RETURNED.
     */
    public function resubmitReturnedVoucher(int $voucherId, array $data, $user, ?UploadedFile $attachmentFile = null): object
    {
        return DB::transaction(function () use ($voucherId, $data, $user, $attachmentFile) {
            $voucher = DB::table('finance_hr_vouchers')->where('ID', $voucherId)->lockForUpdate()->first();
            if (!$voucher) {
                throw new InvalidArgumentException('Voucher tidak ditemukan.');
            }
            if ($voucher->Source_Module !== self::SOURCE_HRGA_MANUAL) {
                throw new InvalidArgumentException('Hanya pengajuan Voucher / AP Manual yang dapat direvisi dari halaman ini.');
            }
            if (strtoupper((string) $voucher->Status) !== 'RETURNED') {
                throw new InvalidArgumentException('Hanya voucher berstatus RETURNED yang dapat dikirim ulang.');
            }

            $username = (string) ($user->username ?? 'system');
            $channel = strtoupper(trim((string) ($data['Requested_Channel'] ?? $voucher->Requested_Channel)));
            if (!in_array($channel, ['DIRECT', 'AP'], true)) {
                $channel = 'AP';
            }

            $amounts = $this->normalizeAmounts($data);
            if ($amounts['Total'] <= 0) {
                throw new InvalidArgumentException('Total nominal voucher harus lebih dari 0.');
            }

            $invType = strtoupper(trim((string) ($data['Inv_Type'] ?? $voucher->Inv_Type))) === 'PARTIAL' ? 'PARTIAL' : 'SINGLE';
            $vendorId = trim((string) ($data['Vendor_ID'] ?? $voucher->Vendor_ID));
            $vendorName = trim((string) ($data['Vendor_Name'] ?? $voucher->Vendor_Name));
            if ($channel === 'AP' && $vendorName === '') {
                throw new InvalidArgumentException('Nama Vendor wajib diisi untuk pengajuan AP.');
            }

            $reference = trim((string) ($data['Reference'] ?? $voucher->Reference));
            if ($reference !== '' && $vendorId !== '' && $invType !== 'PARTIAL') {
                $this->assertNoDuplicateVendorReference(
                    $vendorId,
                    $reference,
                    $voucherId,
                    !empty($voucher->Legacy_AP_ID) ? (int) $voucher->Legacy_AP_ID : null
                );
            }

            $dueDate = !empty($data['Due_Date']) ? date('Y-m-d', strtotime((string) $data['Due_Date'])) : ($voucher->Due_Date ?: date('Y-m-d'));
            $tenors = $this->normalizeTenorsInput($data['Tenors'] ?? [], $amounts['Total'], $dueDate, $channel === 'AP');

            $attachmentPath = $attachmentFile instanceof UploadedFile
                ? $this->storeVendorFile($attachmentFile, 'hr_voucher', 'hr-voucher', $voucher->Voucher_No, 'Revisi Lampiran ' . $voucher->Voucher_No)
                : $voucher->Attachment_Path;

            DB::table('finance_hr_vouchers')->where('ID', $voucherId)->update([
                'Department'        => mb_substr(trim((string) ($data['Department'] ?? $voucher->Department)), 0, 50),
                'Expense_Category'  => mb_substr(trim((string) ($data['Expense_Category'] ?? $voucher->Expense_Category)), 0, 80),
                'Requested_Channel' => $channel,
                'Payment_Method'    => trim((string) ($data['Payment_Method'] ?? $voucher->Payment_Method ?: 'BANK')),
                'Inv_Type'          => $invType,
                'PO_References'     => mb_substr(trim((string) ($data['PO_References'] ?? ($voucher->PO_References ?? ''))), 0, 150) ?: null,
                'PR_No'             => mb_substr(trim((string) ($data['PR_No'] ?? ($voucher->PR_No ?? ''))), 0, 60) ?: null,
                'Vendor_ID'         => mb_substr($vendorId, 0, 40) ?: null,
                'Vendor_Name'       => mb_substr($vendorName, 0, 150) ?: null,
                'PIC_Vendor'        => mb_substr(trim((string) ($data['PIC_Vendor'] ?? $voucher->PIC_Vendor)), 0, 80),
                'Bank_Type'         => mb_substr(trim((string) ($data['Bank_Type'] ?? $voucher->Bank_Type)), 0, 30),
                'Bank_Name'         => mb_substr(trim((string) ($data['Bank_Name'] ?? ($voucher->Bank_Name ?? ''))), 0, 80) ?: null,
                'Bank_Acc'          => mb_substr(trim((string) ($data['Bank_Acc'] ?? ($voucher->Bank_Acc ?? ''))), 0, 60) ?: null,
                'Bank_Acc_Name'     => mb_substr(trim((string) ($data['Bank_Acc_Name'] ?? ($voucher->Bank_Acc_Name ?? ''))), 0, 150) ?: null,
                'Reference'         => mb_substr($reference, 0, 150) ?: null,
                'Inv_Vendor_Date'   => !empty($data['Inv_Vendor_Date']) ? date('Y-m-d', strtotime((string) $data['Inv_Vendor_Date'])) : $voucher->Inv_Vendor_Date,
                'Date_Received'     => !empty($data['Date_Received']) ? date('Y-m-d', strtotime((string) $data['Date_Received'])) : $voucher->Date_Received,
                'Due_Date'          => $dueDate,
                'Subject_Payment'   => mb_substr(trim((string) ($data['Subject_Payment'] ?? $voucher->Subject_Payment)), 0, 255),
                'Description'       => trim((string) ($data['Description'] ?? $voucher->Description)),
                'Attachment_Path'   => $attachmentPath,
                'Subtotal'          => $amounts['Subtotal'],
                'DP'                => $amounts['DP'],
                'Management_Fee'    => $amounts['Management_Fee'],
                'Delivery_Costs'    => $amounts['Delivery_Costs'],
                'PPN_Type'          => $amounts['PPN_Type'],
                'PPN'               => $amounts['PPN'],
                'PPH_Type'          => $amounts['PPH_Type'],
                'PPH'               => $amounts['PPH'],
                'Materai'           => $amounts['Materai'],
                'Total'             => $amounts['Total'],
                'Paid'              => 0.00,
                'Remain_Val'        => $amounts['Total'],
                'Status'            => 'WAITING_APPROVAL',
                'Reject_Reason'     => null,
                'Updated_By'        => mb_substr($username, 0, 50),
                'Updated_At'        => date('Y-m-d H:i:s'),
            ]);

            $this->replaceTenors($voucherId, $tenors);

            $rawLines = $data['Lines'] ?? null;
            if (is_string($rawLines)) {
                $rawLines = json_decode($rawLines, true);
            }
            if (is_array($rawLines) && !empty($rawLines)) {
                $validatedLines = $this->ledgerService->validateBalancedLines($rawLines);
                $this->replaceLines($voucherId, $validatedLines['lines']);
            }

            if ($channel !== 'AP' && !empty($voucher->Legacy_AP_ID)) {
                DB::table('account_payable')->where('ID', (int) $voucher->Legacy_AP_ID)->update([
                    'Status'      => 'CANCELED',
                    'Last_Update' => date('Y-m-d H:i:s'),
                ]);
                DB::table('finance_hr_vouchers')->where('ID', $voucherId)->update([
                    'Legacy_AP_ID'      => null,
                    'Legacy_AP_Task_ID' => null,
                ]);
            }

            $resubmitted = $this->findById($voucherId);
            $requesterId = $this->resolveUserId($user);
            $wfContext = [
                'Request_Type' => self::WORKFLOW_REQUEST_TYPE,
                'Location'     => (string) ($resubmitted->Location ?: 'GEU-HO'),
                'Company_ID'   => (string) ($resubmitted->Company_ID ?: 'GEU'),
                'Department'   => (string) ($resubmitted->Department ?: 'HCGA'),
                'Amount'       => (float) $amounts['Total'],
            ];

            $oldReq = !empty($voucher->Approval_Request_ID)
                ? DB::table('approval_request')->where('ID', (int) $voucher->Approval_Request_ID)->first()
                : null;

            if ($oldReq && in_array(strtoupper((string) $oldReq->Status), ['RETURNED', 'REJECTED'], true)) {
                $newReq = $this->workflowService->resubmit((int) $oldReq->ID, $wfContext, $requesterId);
            } else {
                $newReq = $this->workflowService->submit([
                    'Request_No'  => $resubmitted->Voucher_No,
                    'Description' => (string) ($resubmitted->Subject_Payment ?: $resubmitted->Voucher_No),
                ], $wfContext, $requesterId);
            }

            DB::table('approval_request')->where('ID', (int) $newReq->ID)->update([
                'Map_To' => $voucherId,
            ]);

            DB::table('finance_hr_vouchers')->where('ID', $voucherId)->update([
                'Approval_Request_ID' => (int) $newReq->ID,
                'Updated_At'          => date('Y-m-d H:i:s'),
            ]);

            if (strtoupper((string) $newReq->Status) === 'APPROVED') {
                $this->onWorkflowApproved($voucherId);
            }

            return $this->findById($voucherId);
        });
    }

    /**
     * Sinkronisasi saat seluruh stage approval hulu HR_VOUCHER disetujui (APPROVED).
     * Idempoten: aman dipanggil dari ApprovalRequestUpdated listener maupun fallback langsung.
     */
    public function onWorkflowApproved(int $voucherId): ?object
    {
        return DB::transaction(function () use ($voucherId) {
            $voucher = DB::table('finance_hr_vouchers')->where('ID', $voucherId)->lockForUpdate()->first();
            if (!$voucher) {
                return null;
            }

            $statusUpper = strtoupper((string) $voucher->Status);
            if (in_array($statusUpper, ['CANCELLED', 'CANCELED', 'PAID', 'CLOSED', 'RECEIVED_FINANCE', 'RECEIVED_APPROVAL1', 'PARTIAL'], true)) {
                return $voucher;
            }

            $now = date('Y-m-d H:i:s');
            if ($statusUpper === 'WAITING_APPROVAL' || $statusUpper === 'RETURNED') {
                DB::table('finance_hr_vouchers')->where('ID', $voucherId)->update([
                    'Status'        => 'SUBMITTED',
                    'Reject_Reason' => null,
                    'Updated_At'    => $now,
                ]);
            }

            $fresh = $this->findById($voucherId);
            if (strtoupper((string) $fresh->Requested_Channel) === 'AP') {
                $apSync = $this->ledgerService->syncToAccountPayable($fresh, (string) ($fresh->Created_By ?: 'system'), 'PROPOSED');
                DB::table('finance_hr_vouchers')->where('ID', $voucherId)->update([
                    'Legacy_AP_ID'      => $apSync['ap_id'],
                    'Legacy_AP_Task_ID' => $apSync['ap_task_id'],
                    'Updated_At'        => $now,
                ]);
            }

            return $this->findById($voucherId);
        });
    }

    /**
     * Sinkronisasi saat approval hulu HR_VOUCHER menolak / mengembalikan pengajuan.
     */
    public function onWorkflowRejected(int $voucherId, string $reason, string $workflowStatus = 'RETURNED'): ?object
    {
        return DB::transaction(function () use ($voucherId, $reason) {
            $voucher = DB::table('finance_hr_vouchers')->where('ID', $voucherId)->lockForUpdate()->first();
            if (!$voucher) {
                return null;
            }

            $statusUpper = strtoupper((string) $voucher->Status);
            if (in_array($statusUpper, ['CANCELLED', 'CANCELED', 'PAID', 'CLOSED'], true)) {
                return $voucher;
            }

            $now = date('Y-m-d H:i:s');
            DB::table('finance_hr_vouchers')->where('ID', $voucherId)->update([
                'Status'        => 'RETURNED',
                'Reject_Reason' => mb_substr(trim($reason) ?: 'Ditolak pada proses approval.', 0, 1000),
                'Updated_At'    => $now,
            ]);

            if (!empty($voucher->Legacy_AP_ID)) {
                DB::table('account_payable')->where('ID', (int) $voucher->Legacy_AP_ID)->update([
                    'Status'      => 'RETURNED',
                    'Note'        => mb_substr(trim($reason) ?: 'Ditolak pada proses approval.', 0, 1000),
                    'Last_Update' => $now,
                ]);
            }

            return $this->findById($voucherId);
        });
    }

    /**
     * Batalkan voucher Manual yang masih WAITING_APPROVAL, SUBMITTED atau RETURNED.
     */
    public function cancelVoucher(int $voucherId, $user): object
    {
        return DB::transaction(function () use ($voucherId, $user) {
            $voucher = DB::table('finance_hr_vouchers')->where('ID', $voucherId)->lockForUpdate()->first();
            if (!$voucher) {
                throw new InvalidArgumentException('Voucher tidak ditemukan.');
            }
            if (!in_array(strtoupper((string) $voucher->Status), ['WAITING_APPROVAL', 'SUBMITTED', 'RETURNED'], true)) {
                throw new InvalidArgumentException('Voucher yang sudah diproses Finance tidak dapat dibatalkan.');
            }

            $now = date('Y-m-d H:i:s');
            DB::table('finance_hr_vouchers')->where('ID', $voucherId)->update([
                'Status'     => 'CANCELLED',
                'Updated_By' => mb_substr((string) ($user->username ?? 'system'), 0, 50),
                'Updated_At' => $now,
            ]);

            if (!empty($voucher->Approval_Request_ID)) {
                try {
                    $this->workflowService->cancel(
                        (int) $voucher->Approval_Request_ID,
                        $this->resolveUserId($user),
                        'Dibatalkan oleh pembuat voucher.'
                    );
                } catch (\Throwable $e) {
                    // Request mungkin sudah final (APPROVED/RETURNED); tetap aman melanjutkan pembatalan voucher.
                }
            }

            if (!empty($voucher->Legacy_AP_ID)) {
                DB::table('account_payable')->where('ID', (int) $voucher->Legacy_AP_ID)->update([
                    'Status'      => 'CANCELED',
                    'Last_Update' => $now,
                ]);
            }

            return $this->findById($voucherId);
        });
    }

    /**
     * Sinkronisasi atau pembuatan voucher terpusat dari modul sumber HR/GA:
     * ASSET_MAINTENANCE, GA_WORK_ORDER, VEHICLE_TAX.
     */
    public function createOrSyncFromSource(string $sourceModule, int $sourceId, array $data, string $username = 'system'): object
    {
        if (!Schema::hasTable('finance_hr_vouchers')) {
            throw new \RuntimeException('Tabel finance_hr_vouchers belum tersedia.');
        }

        $sourceModule = strtoupper(trim($sourceModule));
        $existing = DB::table('finance_hr_vouchers')
            ->where('Source_Module', $sourceModule)
            ->where('Source_ID', $sourceId)
            ->where('Status', '!=', 'CANCELLED')
            ->orderBy('ID', 'DESC')
            ->first();

        $channel = strtoupper(trim((string) ($data['Requested_Channel'] ?? 'DIRECT'))) === 'AP' ? 'AP' : 'DIRECT';
        $amounts = $this->normalizeAmounts($data);
        $now = date('Y-m-d H:i:s');
        $dueDate = !empty($data['Due_Date']) ? date('Y-m-d', strtotime((string) $data['Due_Date'])) : date('Y-m-d');

        $payload = [
            'Source_No'         => mb_substr(trim((string) ($data['Source_No'] ?? '')), 0, 80) ?: null,
            'Location'          => mb_substr(trim((string) ($data['Location'] ?? 'GEU-HO')), 0, 25),
            'Company_ID'        => mb_substr(trim((string) ($data['Company_ID'] ?? 'GEU')), 0, 25),
            'Department'        => mb_substr(trim((string) ($data['Department'] ?? 'HCGA')), 0, 50),
            'Expense_Category'  => mb_substr(trim((string) ($data['Expense_Category'] ?? $sourceModule)), 0, 80),
            'Requested_Channel' => $channel,
            'Payment_Method'    => trim((string) ($data['Payment_Method'] ?? 'BANK')) ?: 'BANK',
            'Inv_Type'          => strtoupper(trim((string) ($data['Inv_Type'] ?? 'SINGLE'))) === 'PARTIAL' ? 'PARTIAL' : 'SINGLE',
            'PO_References'     => mb_substr(trim((string) ($data['PO_References'] ?? '')), 0, 150) ?: null,
            'PR_No'             => mb_substr(trim((string) ($data['PR_No'] ?? '')), 0, 60) ?: null,
            'Vendor_ID'         => mb_substr(trim((string) ($data['Vendor_ID'] ?? '')), 0, 40) ?: null,
            'Vendor_Name'       => mb_substr(trim((string) ($data['Vendor_Name'] ?? '')), 0, 150) ?: null,
            'PIC_Vendor'        => mb_substr(trim((string) ($data['PIC_Vendor'] ?? '')), 0, 80) ?: null,
            'Bank_Type'         => mb_substr(trim((string) ($data['Bank_Type'] ?? 'DIRECT TRANSFER')), 0, 30),
            'Bank_Name'         => mb_substr(trim((string) ($data['Bank_Name'] ?? '')), 0, 80) ?: null,
            'Bank_Acc'          => mb_substr(trim((string) ($data['Bank_Acc'] ?? '')), 0, 60) ?: null,
            'Bank_Acc_Name'     => mb_substr(trim((string) ($data['Bank_Acc_Name'] ?? '')), 0, 150) ?: null,
            'Reference'         => mb_substr(trim((string) ($data['Reference'] ?? '')), 0, 150) ?: null,
            'Inv_Vendor_Date'   => !empty($data['Inv_Vendor_Date']) ? date('Y-m-d', strtotime((string) $data['Inv_Vendor_Date'])) : date('Y-m-d'),
            'Date_Received'     => !empty($data['Date_Received']) ? date('Y-m-d', strtotime((string) $data['Date_Received'])) : date('Y-m-d'),
            'Due_Date'          => $dueDate,
            'Subject_Payment'   => mb_substr(trim((string) ($data['Subject_Payment'] ?? 'Voucher ' . $sourceModule)), 0, 255),
            'Description'       => trim((string) ($data['Description'] ?? '')),
            'Attachment_Path'   => !empty($data['Attachment_Path']) ? (string) $data['Attachment_Path'] : null,
            'Subtotal'          => $amounts['Subtotal'],
            'DP'                => $amounts['DP'],
            'Management_Fee'    => $amounts['Management_Fee'],
            'Delivery_Costs'    => $amounts['Delivery_Costs'],
            'PPN_Type'          => $amounts['PPN_Type'],
            'PPN'               => $amounts['PPN'],
            'PPH_Type'          => $amounts['PPH_Type'],
            'PPH'               => $amounts['PPH'],
            'Materai'           => $amounts['Materai'],
            'Total'             => $amounts['Total'],
            'Remain_Val'        => $amounts['Total'],
            'Status'            => 'SUBMITTED',
            'Reject_Reason'     => null,
            'Updated_By'        => mb_substr($username, 0, 50),
            'Updated_At'        => $now,
        ];

        if ($existing) {
            if (in_array(strtoupper((string) $existing->Status), ['SUBMITTED', 'RETURNED'], true)) {
                DB::table('finance_hr_vouchers')->where('ID', (int) $existing->ID)->update($payload);
                $voucherId = (int) $existing->ID;
            } else {
                $voucherId = (int) $existing->ID;
            }
        } else {
            $voucherNo = !empty($data['Voucher_No'])
                ? trim((string) $data['Voucher_No'])
                : $this->generateVoucherNo('VCH/', Carbon::now());
            if (DB::table('finance_hr_vouchers')->where('Voucher_No', $voucherNo)->exists()) {
                $voucherNo = $this->generateVoucherNo('VCH/', Carbon::now());
            }

            $payload['Voucher_No'] = mb_substr($voucherNo, 0, 60);
            $payload['Source_Module'] = $sourceModule;
            $payload['Source_ID'] = $sourceId;
            $payload['Paid'] = 0.00;
            $payload['Created_By'] = mb_substr($username, 0, 50);
            $payload['Created_At'] = $now;

            $voucherId = (int) DB::table('finance_hr_vouchers')->insertGetId($payload);
        }

        if ($channel === 'AP' && !DB::table('finance_hr_voucher_tenors')->where('Voucher_ID', $voucherId)->exists()) {
            $tenors = $this->normalizeTenorsInput($data['Tenors'] ?? [], $amounts['Total'], $dueDate, true);
            $this->replaceTenors($voucherId, $tenors);
        }

        if ($channel === 'AP') {
            $rowNow = $this->findById($voucherId);
            if ($rowNow && in_array(strtoupper((string) $rowNow->Status), ['SUBMITTED', 'RETURNED'], true)) {
                $apSync = $this->ledgerService->syncToAccountPayable($rowNow, $username, 'PROPOSED');
                DB::table('finance_hr_vouchers')->where('ID', $voucherId)->update([
                    'Legacy_AP_ID'      => $apSync['ap_id'],
                    'Legacy_AP_Task_ID' => $apSync['ap_task_id'],
                    'Updated_At'        => $now,
                ]);
            }
        }

        return $this->findById($voucherId);
    }

    /**
     * Ambil atau hydrate voucher terpusat untuk sumber legacy yang sudah FINANCE_SUBMITTED.
     */
    public function getOrHydrateFromSource(string $sourceModule, int $sourceId, string $username = 'system'): object
    {
        $sourceModule = strtoupper(trim($sourceModule));
        $existing = DB::table('finance_hr_vouchers')
            ->where('Source_Module', $sourceModule)
            ->where('Source_ID', $sourceId)
            ->where('Status', '!=', 'CANCELLED')
            ->orderBy('ID', 'DESC')
            ->first();

        if ($existing) {
            return $existing;
        }

        if ($sourceModule === self::SOURCE_ASSET_MAINTENANCE) {
            $s = DB::table('asset_maintenance_schedule as s')
                ->leftJoin('asset_list as a', function ($join) {
                    $join->on(
                        DB::raw('CONVERT(s.Asset_No USING utf8mb4) COLLATE utf8mb4_unicode_ci'),
                        '=',
                        DB::raw('CONVERT(a.Asset_No USING utf8mb4) COLLATE utf8mb4_unicode_ci')
                    );
                })
                ->leftJoin('supplier as sup', function ($join) {
                    $join->on(
                        DB::raw('CONVERT(s.Supplier_ID USING utf8mb4) COLLATE utf8mb4_unicode_ci'),
                        '=',
                        DB::raw('CONVERT(sup.Supplier_ID USING utf8mb4) COLLATE utf8mb4_unicode_ci')
                    );
                })
                ->where('s.ID', $sourceId)
                ->select('s.*', 'a.Product_Description as Asset_Name', 'a.Asset_Location', 'sup.Company_Name as Vendor_Name', 'sup.Bank_Rupiah', 'sup.Bank_Rupiah_Account', 'sup.Bank_Rupiah_Name')
                ->first();

            if (!$s) {
                throw new InvalidArgumentException('Data jadwal perawatan aset tidak ditemukan.');
            }

            return $this->createOrSyncFromSource(self::SOURCE_ASSET_MAINTENANCE, $sourceId, [
                'Voucher_No'        => $s->Voucher_No ?: null,
                'Source_No'         => $s->Schedule_No,
                'Location'          => $s->Asset_Location ?: 'GEU-HO',
                'Department'        => 'HCGA',
                'Expense_Category'  => 'PERAWATAN ASET',
                'Requested_Channel' => 'DIRECT',
                'Vendor_ID'         => $s->Supplier_ID ?: '',
                'Vendor_Name'       => $s->Vendor_Name ?: ($s->PIC_Internal ?: 'Internal Maintenance'),
                'Bank_Name'         => $s->Bank_Rupiah ?: '',
                'Bank_Acc'          => $s->Bank_Rupiah_Account ?: '',
                'Bank_Acc_Name'     => $s->Bank_Rupiah_Name ?: '',
                'Reference'         => $s->Schedule_No,
                'Due_Date'          => $s->Next_Schedule_Date ?: date('Y-m-d'),
                'Subject_Payment'   => 'Perawatan Aset ' . $s->Asset_No . ' (' . ($s->Asset_Name ?: $s->Jenis_Perawatan) . ')',
                'Description'       => 'Schedule ' . $s->Schedule_No . ' | Frekuensi: ' . $s->Frekuensi,
                'Subtotal'          => (float) $s->Estimasi_Biaya,
                'Total'             => (float) $s->Estimasi_Biaya,
            ], $s->Created_By ?: $username);
        }

        if ($sourceModule === self::SOURCE_VEHICLE_TAX) {
            $t = DB::table('vehicle_tax_request as t')
                ->leftJoin('asset_list as a', function ($join) {
                    $join->on(
                        DB::raw('CONVERT(t.Asset_No USING utf8mb4) COLLATE utf8mb4_unicode_ci'),
                        '=',
                        DB::raw('CONVERT(a.Asset_No USING utf8mb4) COLLATE utf8mb4_unicode_ci')
                    );
                })
                ->where('t.ID', $sourceId)
                ->select('t.*', 'a.Registration_No', 'a.Product_Description', 'a.Asset_Location')
                ->first();

            if (!$t) {
                throw new InvalidArgumentException('Data pengajuan pajak kendaraan tidak ditemukan.');
            }

            return $this->createOrSyncFromSource(self::SOURCE_VEHICLE_TAX, $sourceId, [
                'Voucher_No'        => $t->Voucher_No ?: null,
                'Source_No'         => $t->Tax_Request_No,
                'Location'          => $t->Asset_Location ?: 'GEU-HO',
                'Department'        => 'HCGA',
                'Expense_Category'  => 'PAJAK KENDARAAN',
                'Requested_Channel' => 'DIRECT',
                'Vendor_Name'       => 'SAMSAT / DISHUB / BIRO JASA',
                'Reference'         => $t->Tax_Request_No,
                'Due_Date'          => $t->Tanggal_Jatuh_Tempo ?: date('Y-m-d'),
                'Subject_Payment'   => 'Pajak ' . $t->Jenis_Pajak . ' - ' . ($t->Registration_No ?: $t->Asset_No),
                'Description'       => trim((string) ($t->Product_Description ?: '')),
                'Subtotal'          => (float) $t->Estimasi_Biaya,
                'Total'             => (float) $t->Estimasi_Biaya,
            ], $t->Created_By ?: $username);
        }

        if ($sourceModule === self::SOURCE_GA_WORK_ORDER) {
            if (!Schema::hasTable('ga_payment_voucher')) {
                throw new InvalidArgumentException('Tabel ga_payment_voucher belum tersedia.');
            }
            $g = DB::table('ga_payment_voucher as v')
                ->leftJoin('ticket as tk', 'tk.ID', '=', 'v.Ticket_ID')
                ->leftJoin('supplier as s', 's.ID', '=', 'v.Supplier_ID')
                ->where('v.ID', $sourceId)
                ->select('v.*', 'tk.Ticket_No', 'tk.Ticket_Subject', 's.Supplier_ID as Supplier_Code')
                ->first();

            if (!$g) {
                throw new InvalidArgumentException('Data voucher GA tidak ditemukan.');
            }

            return $this->createOrSyncFromSource(self::SOURCE_GA_WORK_ORDER, $sourceId, [
                'Voucher_No'        => $g->Voucher_No,
                'Source_No'         => $g->Ticket_No ?: $g->Voucher_No,
                'Location'          => $g->Location ?: 'GEU-HO',
                'Company_ID'        => $g->Company_ID ?: 'GEU',
                'Department'        => $g->Department ?: 'HCGA',
                'Expense_Category'  => 'GA ORDER PEKERJAAN',
                'Requested_Channel' => 'DIRECT',
                'Payment_Method'    => strtoupper((string) $g->Payment_Method) === 'CASH' ? 'CASH' : 'BANK',
                'Vendor_ID'         => $g->Supplier_Code ?: (string) ($g->Supplier_ID ?: ''),
                'Vendor_Name'       => $g->Payee_Name,
                'Bank_Name'         => $g->Bank_Name,
                'Bank_Acc'          => $g->Bank_Account_No,
                'Bank_Acc_Name'     => $g->Bank_Account_Name,
                'Reference'         => $g->Ticket_No ?: $g->Voucher_No,
                'Due_Date'          => $g->Due_Date ?: date('Y-m-d'),
                'Subject_Payment'   => $g->Ticket_Subject ?: ($g->Description ?: $g->Voucher_No),
                'Description'       => $g->Description,
                'Attachment_Path'   => $g->Attachment_Path,
                'Subtotal'          => (float) $g->Amount,
                'Total'             => (float) $g->Amount,
            ], $g->Created_By ?: $username);
        }

        throw new InvalidArgumentException('Sumber modul voucher tidak dikenali.');
    }

    /**
     * Ambil payload lengkap untuk modal Review & Verifikasi Finance
     * (Header, Suggested/Saved COA Lines, Tenor, Riwayat Pembayaran).
     */
    public function getVoucherReviewPayload(int $voucherId): array
    {
        $voucher = $this->findById($voucherId);
        if (!$voucher) {
            throw new InvalidArgumentException('Voucher tidak ditemukan.');
        }

        $lines = DB::table('finance_hr_voucher_lines')
            ->where('Voucher_ID', $voucherId)
            ->orderBy('Line_Order', 'ASC')
            ->get()
            ->all();

        if (empty($lines)) {
            $lines = $this->coaService->buildSuggestedLines(
                (string) $voucher->Source_Module,
                [
                    'Subtotal'       => (float) $voucher->Subtotal,
                    'Delivery_Costs' => (float) $voucher->Delivery_Costs,
                    'Management_Fee' => (float) $voucher->Management_Fee,
                    'DP'             => (float) $voucher->DP,
                    'Materai'        => (float) $voucher->Materai,
                    'PPN'            => (float) $voucher->PPN,
                    'PPH'            => (float) $voucher->PPH,
                    'Total'          => (float) $voucher->Total,
                ],
                (string) ($voucher->Final_Channel ?: $voucher->Requested_Channel ?: 'DIRECT'),
                (string) ($voucher->Payment_Method ?: 'BANK'),
                null,
                (string) $voucher->Department
            );
        }

        $tenors = DB::table('finance_hr_voucher_tenors')
            ->where('Voucher_ID', $voucherId)
            ->orderBy('Tenor_Seq', 'ASC')
            ->get()
            ->all();

        if (empty($tenors)) {
            $tenors = [
                (object) [
                    'Tenor_Seq'   => 1,
                    'Tenor_Label' => 'Jatuh Tempo Utama',
                    'Due_Date'    => $voucher->Due_Date ?: date('Y-m-d'),
                    'Amount'      => (float) $voucher->Total,
                    'Paid_Amount' => (float) $voucher->Paid,
                    'Status'      => 'OPEN',
                    'Note'        => '',
                ],
            ];
        }

        $payments = DB::table('finance_hr_voucher_payments')
            ->where('Voucher_ID', $voucherId)
            ->orderBy('ID', 'DESC')
            ->get()
            ->all();

        $approvalTrail = $this->resolveApprovalTrail($voucher);

        return [
            'voucher'        => $voucher,
            'lines'          => $lines,
            'tenors'         => $tenors,
            'payments'       => $payments,
            'approval_trail' => $approvalTrail,
        ];
    }

    /**
     * Proses 1 tahap oleh Finance:
     * - Koreksi jalur (DIRECT vs AP — termasuk konversi non-AP menjadi AP)
     * - Koreksi rincian biaya, pajak, vendor, PO/Invoice, jatuh tempo / tenor manual
     * - Validasi multi-debet & multi-kredit COA seimbang
     * - Posting ke cash/bank atau account_payable + jurnal final ke `ho`
     */
    public function verifyAndProcessVoucher(int $voucherId, array $payload, $user, ?UploadedFile $proofFile = null): object
    {
        return DB::transaction(function () use ($voucherId, $payload, $user, $proofFile) {
            $voucher = DB::table('finance_hr_vouchers')->where('ID', $voucherId)->lockForUpdate()->first();
            if (!$voucher) {
                throw new InvalidArgumentException('Voucher tidak ditemukan.');
            }
            if (!in_array(strtoupper((string) $voucher->Status), ['SUBMITTED', 'RETURNED'], true)) {
                throw new InvalidArgumentException('Voucher ini sudah diproses Finance (status: ' . $voucher->Status . ').');
            }

            $username = (string) ($user->username ?? 'system');
            $finalChannel = strtoupper(trim((string) ($payload['Final_Channel'] ?? $voucher->Requested_Channel)));
            if (!in_array($finalChannel, ['DIRECT', 'AP'], true)) {
                throw new InvalidArgumentException('Final_Channel harus DIRECT atau AP.');
            }

            $paymentMethod = strtoupper(trim((string) ($payload['Payment_Method'] ?? $voucher->Payment_Method ?: 'BANK')));
            if (!in_array($paymentMethod, ['CASH', 'BANK'], true)) {
                $paymentMethod = 'BANK';
            }

            $amounts = $this->normalizeAmounts($payload, $voucher);
            if ($amounts['Total'] <= 0) {
                throw new InvalidArgumentException('Total nominal voucher harus lebih dari 0.');
            }

            $validatedCoa = $this->ledgerService->validateBalancedLines($payload['Lines'] ?? []);
            $lines = $validatedCoa['lines'];

            if (abs($validatedCoa['total_debet'] - $amounts['Total']) >= 0.01 && abs($validatedCoa['total_credit'] - $amounts['Total']) >= 0.01) {
                // Izinkan selisih hanya jika ada potongan PPh di sisi CREDIT (Total Debet = Total + PPh)
                $expectedDebetWithPph = round($amounts['Total'] + $amounts['PPH'], 2);
                if (abs($validatedCoa['total_debet'] - $expectedDebetWithPph) >= 0.01 && abs($validatedCoa['total_debet'] - $amounts['Total']) >= 0.01) {
                    throw new InvalidArgumentException(
                        'Total Jurnal Debet (Rp ' . number_format($validatedCoa['total_debet'], 0, ',', '.')
                        . ') tidak sesuai dengan total voucher (Rp ' . number_format($amounts['Total'], 0, ',', '.') . ').'
                    );
                }
            }

            $invType = strtoupper(trim((string) ($payload['Inv_Type'] ?? $voucher->Inv_Type ?: 'SINGLE'))) === 'PARTIAL' ? 'PARTIAL' : 'SINGLE';
            $vendorId = trim((string) ($payload['Vendor_ID'] ?? ($voucher->Vendor_ID ?? '')));
            $vendorName = trim((string) ($payload['Vendor_Name'] ?? ($voucher->Vendor_Name ?? '')));
            $reference = trim((string) ($payload['Reference'] ?? ($voucher->Reference ?? '')));

            if ($finalChannel === 'AP' && $vendorName === '') {
                throw new InvalidArgumentException('Nama Vendor wajib diisi jika diterbitkan sebagai Hutang Dagang (AP).');
            }
            if ($finalChannel === 'AP' && $reference !== '' && $vendorId !== '' && $invType !== 'PARTIAL') {
                $this->assertNoDuplicateVendorReference(
                    $vendorId,
                    $reference,
                    $voucherId,
                    !empty($voucher->Legacy_AP_ID) ? (int) $voucher->Legacy_AP_ID : null
                );
            }

            $dueDate = !empty($payload['Due_Date'])
                ? date('Y-m-d', strtotime((string) $payload['Due_Date']))
                : ($voucher->Due_Date ?: date('Y-m-d'));
            $postingDate = !empty($payload['Posting_Date'])
                ? date('Y-m-d', strtotime((string) $payload['Posting_Date']))
                : date('Y-m-d');

            $isConverted = ($voucher->Requested_Channel === 'DIRECT' && $finalChannel === 'AP') ? 1 : 0;
            $now = date('Y-m-d H:i:s');

            $updateData = [
                'Final_Channel'      => $finalChannel,
                'Is_Converted_To_AP' => $isConverted,
                'Payment_Method'     => $paymentMethod,
                'Inv_Type'           => $invType,
                'PO_References'      => mb_substr(trim((string) ($payload['PO_References'] ?? ($voucher->PO_References ?? ''))), 0, 150) ?: null,
                'PR_No'              => mb_substr(trim((string) ($payload['PR_No'] ?? ($voucher->PR_No ?? ''))), 0, 60) ?: null,
                'Vendor_ID'          => mb_substr($vendorId, 0, 40) ?: null,
                'Vendor_Name'        => mb_substr($vendorName, 0, 150) ?: null,
                'PIC_Vendor'         => mb_substr(trim((string) ($payload['PIC_Vendor'] ?? ($voucher->PIC_Vendor ?? ''))), 0, 80) ?: null,
                'Bank_Type'          => mb_substr(trim((string) ($payload['Bank_Type'] ?? ($voucher->Bank_Type ?? 'DIRECT TRANSFER'))), 0, 30),
                'Bank_Name'          => mb_substr(trim((string) ($payload['Bank_Name'] ?? ($voucher->Bank_Name ?? ''))), 0, 80) ?: null,
                'Bank_Acc'           => mb_substr(trim((string) ($payload['Bank_Acc'] ?? ($voucher->Bank_Acc ?? ''))), 0, 60) ?: null,
                'Bank_Acc_Name'      => mb_substr(trim((string) ($payload['Bank_Acc_Name'] ?? ($voucher->Bank_Acc_Name ?? ''))), 0, 150) ?: null,
                'Reference'          => mb_substr($reference, 0, 150) ?: null,
                'Inv_Vendor_Date'    => !empty($payload['Inv_Vendor_Date']) ? date('Y-m-d', strtotime((string) $payload['Inv_Vendor_Date'])) : $voucher->Inv_Vendor_Date,
                'Date_Received'      => !empty($payload['Date_Received']) ? date('Y-m-d', strtotime((string) $payload['Date_Received'])) : ($voucher->Date_Received ?: date('Y-m-d')),
                'Due_Date'           => $dueDate,
                'Subject_Payment'    => mb_substr(trim((string) ($payload['Subject_Payment'] ?? $voucher->Subject_Payment)), 0, 255),
                'Description'        => trim((string) ($payload['Description'] ?? $voucher->Description)),
                'Subtotal'           => $amounts['Subtotal'],
                'DP'                 => $amounts['DP'],
                'Management_Fee'     => $amounts['Management_Fee'],
                'Delivery_Costs'     => $amounts['Delivery_Costs'],
                'PPN_Type'           => $amounts['PPN_Type'],
                'PPN'                => $amounts['PPN'],
                'PPH_Type'           => $amounts['PPH_Type'],
                'PPH'                => $amounts['PPH'],
                'Materai'            => $amounts['Materai'],
                'Total'              => $amounts['Total'],
                'Finance_Note'       => trim((string) ($payload['Finance_Note'] ?? '')),
                'Reject_Reason'      => null,
                'Verified_By'        => mb_substr($username, 0, 50),
                'Verified_At'        => $now,
                'Updated_By'         => mb_substr($username, 0, 50),
                'Updated_At'         => $now,
            ];

            DB::table('finance_hr_vouchers')->where('ID', $voucherId)->update($updateData);
            $this->replaceLines($voucherId, $lines);
            $updatedVoucher = $this->findById($voucherId);

            if ($finalChannel === 'DIRECT') {
                $creditLine = $this->findPrimaryCreditLine($lines, $paymentMethod);
                $paymentCoaNo = $creditLine ? $creditLine['Acc_No'] : trim((string) ($payload['Payment_Acc_No'] ?? ''));
                $bankAccRef = trim((string) ($payload['Bank_Acc_Ref'] ?? $paymentCoaNo));
                $paymentRef = trim((string) ($payload['Payment_Ref'] ?? ($reference ?: $voucher->Voucher_No)));

                $proofPath = $proofFile instanceof UploadedFile
                    ? $this->storeVendorFile($proofFile, 'hr_voucher_paid_' . $voucherId, 'hr-voucher-paid', $voucher->Voucher_No, 'Bukti Bayar ' . $voucher->Voucher_No)
                    : null;

                $journalNo = $this->ledgerService->generateJournalNo('JDR-');
                $legacyCashNo = null;
                $legacyBankNo = null;

                if ($paymentMethod === 'CASH') {
                    $legacyCashNo = $this->ledgerService->syncToCashOut($updatedVoucher, $lines, $postingDate, $paymentCoaNo, $amounts['Total'], $username);
                } else {
                    $legacyBankNo = $this->ledgerService->syncToBankOut($updatedVoucher, $lines, $postingDate, $bankAccRef, $paymentCoaNo, $amounts['Total'], $username);
                }

                $this->ledgerService->postJournalToHo($updatedVoucher, $lines, $journalNo, $postingDate, $username);

                // Jika sebelumnya diajukan sebagai AP lalu Finance mengarahkan ke Direct Cash/Bank, batalkan draft AP-nya
                if (!empty($voucher->Legacy_AP_ID)) {
                    DB::table('account_payable')->where('ID', (int) $voucher->Legacy_AP_ID)->update([
                        'Status'       => 'CANCELED',
                        'Payment_Note' => 'Diarahkan Finance menjadi Direct ' . $paymentMethod . ' (' . ($legacyBankNo ?: $legacyCashNo) . ')',
                        'Last_Update'  => $now,
                    ]);
                }

                DB::table('finance_hr_voucher_payments')->insert([
                    'Voucher_ID'       => $voucherId,
                    'Tenor_ID'         => null,
                    'Payment_Date'     => $postingDate,
                    'Payment_Channel'  => $paymentMethod,
                    'Payment_Acc_No'   => $paymentCoaNo,
                    'Payment_Acc_Name' => $creditLine['Acc_Name'] ?? '',
                    'Bank_Acc_Ref'     => $paymentMethod === 'BANK' ? $bankAccRef : null,
                    'Amount'           => $amounts['Total'],
                    'Payment_Ref'      => mb_substr($paymentRef, 0, 100),
                    'Proof_Path'       => $proofPath,
                    'Legacy_Cash_No'   => $legacyCashNo,
                    'Legacy_Bank_No'   => $legacyBankNo,
                    'Journal_No'       => $journalNo,
                    'Note'             => trim((string) ($payload['Finance_Note'] ?? 'Pembayaran Langsung Finance')),
                    'Created_By'       => mb_substr($username, 0, 50),
                    'Created_At'       => $now,
                ]);

                DB::table('finance_hr_vouchers')->where('ID', $voucherId)->update([
                    'Paid'              => $amounts['Total'],
                    'Remain_Val'        => 0.00,
                    'Status'            => 'PAID',
                    'Legacy_Cash_No'    => $legacyCashNo,
                    'Legacy_Bank_No'    => $legacyBankNo,
                    'Legacy_AP_ID'      => null,
                    'Legacy_AP_Task_ID' => null,
                    'Journal_No'        => $journalNo,
                    'Updated_At'        => $now,
                ]);

                $this->syncSourceCompletion($updatedVoucher, $postingDate, $payload['New_Due_Date'] ?? null, $paymentRef, $proofPath, $username);
            } else {
                // Jalur AP (Hutang Dagang) dengan Tenor Manual -> masuk ke tabel account_payable
                $tenors = $this->normalizeTenorsInput($payload['Tenors'] ?? [], $amounts['Total'], $dueDate, true);
                $this->replaceTenors($voucherId, $tenors);

                $firstDue = $tenors[0]['Due_Date'] ?? $dueDate;
                DB::table('finance_hr_vouchers')->where('ID', $voucherId)->update([
                    'Due_Date'   => $firstDue,
                    'Paid'       => 0.00,
                    'Remain_Val' => $amounts['Total'],
                    'Status'     => 'RECEIVED_FINANCE',
                    'Updated_At' => $now,
                ]);
                $updatedVoucher = $this->findById($voucherId);

                $apSync = $this->ledgerService->syncToAccountPayable($updatedVoucher, $username, 'RECEIVED_FINANCE');
                $journalNo = $this->ledgerService->generateJournalNo('JAP-');
                $this->ledgerService->postJournalToHo($updatedVoucher, $lines, $journalNo, $postingDate, $username, $apSync['ap_task_id']);

                DB::table('finance_hr_vouchers')->where('ID', $voucherId)->update([
                    'Legacy_AP_ID'      => $apSync['ap_id'],
                    'Legacy_AP_Task_ID' => $apSync['ap_task_id'],
                    'Journal_No'        => $journalNo,
                    'Updated_At'        => $now,
                ]);
            }

            return $this->findById($voucherId);
        });
    }

    /**
     * Transisi tahap AP: RECEIVED_FINANCE -> RECEIVED_APPROVAL1 (selaras ap/request).
     */
    public function advanceApRequestStage(int $voucherId, $user): object
    {
        return DB::transaction(function () use ($voucherId, $user) {
            $voucher = DB::table('finance_hr_vouchers')->where('ID', $voucherId)->lockForUpdate()->first();
            if (!$voucher) {
                throw new InvalidArgumentException('Voucher tidak ditemukan.');
            }
            if (strtoupper((string) $voucher->Status) !== 'RECEIVED_FINANCE') {
                throw new InvalidArgumentException('Hanya voucher AP berstatus RECEIVED_FINANCE yang dapat diteruskan ke tahap Payment Request.');
            }

            $username = (string) ($user->username ?? 'system');
            $now = date('Y-m-d H:i:s');

            DB::table('finance_hr_vouchers')->where('ID', $voucherId)->update([
                'Status'     => 'RECEIVED_APPROVAL1',
                'Updated_By' => mb_substr($username, 0, 50),
                'Updated_At' => $now,
            ]);

            if (!empty($voucher->Legacy_AP_ID)) {
                DB::table('account_payable')->where('ID', (int) $voucher->Legacy_AP_ID)->update([
                    'Status'                => 'RECEIVED_APPROVAL1',
                    'Finance_Received_Date' => $now,
                    'Finance_Received_By'   => mb_substr($username, 0, 60),
                    'Last_Update'           => $now,
                ]);
            }

            return $this->findById($voucherId);
        });
    }

    /**
     * Pelunasan Fleksibel Treasury (Partial maupun Full) untuk Voucher berjalur AP.
     */
    public function recordFlexiblePayment(int $voucherId, array $payload, $user, ?UploadedFile $proofFile = null): object
    {
        return DB::transaction(function () use ($voucherId, $payload, $user, $proofFile) {
            $voucher = DB::table('finance_hr_vouchers')->where('ID', $voucherId)->lockForUpdate()->first();
            if (!$voucher) {
                throw new InvalidArgumentException('Voucher tidak ditemukan.');
            }
            if (strtoupper((string) ($voucher->Final_Channel ?? '')) !== 'AP') {
                throw new InvalidArgumentException('Pelunasan bertahap hanya berlaku untuk voucher berjalur Hutang Dagang (AP).');
            }
            if (!in_array(strtoupper((string) $voucher->Status), ['RECEIVED_FINANCE', 'RECEIVED_APPROVAL1', 'PARTIAL'], true)) {
                throw new InvalidArgumentException('Voucher AP tidak dalam status siap bayar (status saat ini: ' . $voucher->Status . ').');
            }

            $remain = round((float) $voucher->Remain_Val, 2);
            if ($remain <= 0) {
                throw new InvalidArgumentException('Voucher AP ini sudah lunas.');
            }

            $payAmount = round((float) ($payload['Amount'] ?? 0), 2);
            if ($payAmount <= 0) {
                throw new InvalidArgumentException('Nominal pembayaran harus lebih dari 0.');
            }
            if ($payAmount - $remain > 0.01) {
                throw new InvalidArgumentException(
                    'Nominal pembayaran (Rp ' . number_format($payAmount, 0, ',', '.')
                    . ') melebihi sisa hutang (Rp ' . number_format($remain, 0, ',', '.') . ').'
                );
            }
            if ($payAmount > $remain) {
                $payAmount = $remain;
            }

            $username = (string) ($user->username ?? 'system');
            $paymentDate = !empty($payload['Payment_Date']) ? date('Y-m-d', strtotime((string) $payload['Payment_Date'])) : date('Y-m-d');
            $channel = strtoupper(trim((string) ($payload['Payment_Channel'] ?? 'BANK'))) === 'CASH' ? 'CASH' : 'BANK';
            $paymentAccNo = trim((string) ($payload['Payment_Acc_No'] ?? ''));
            if ($paymentAccNo === '') {
                throw new InvalidArgumentException('Akun COA Kas/Bank sumber pembayaran wajib dipilih.');
            }

            // Cari COA Hutang AP dari baris CREDIT voucher
            $apLine = DB::table('finance_hr_voucher_lines')
                ->where('Voucher_ID', $voucherId)
                ->where('Line_Side', 'CREDIT')
                ->orderByRaw("CASE WHEN Component_Tag = 'AP' THEN 0 ELSE 1 END")
                ->orderBy('ID', 'ASC')
                ->first();
            $apLiabilityAccNo = $apLine ? (string) $apLine->Acc_No : '';
            if ($apLiabilityAccNo === '') {
                $mappings = $this->coaService->getEffectiveMappings((string) $voucher->Source_Module);
                $apLiabilityAccNo = isset($mappings['AP_LIABILITY']) ? (string) $mappings['AP_LIABILITY']->Acc_No : '';
            }
            if ($apLiabilityAccNo === '') {
                throw new InvalidArgumentException('Akun COA Hutang Dagang (AP) pada voucher tidak ditemukan.');
            }

            $proofPath = $proofFile instanceof UploadedFile
                ? $this->storeVendorFile($proofFile, 'hr_ap_pay_' . $voucherId, 'hr-voucher-ap-pay', $voucher->Voucher_No, 'Bukti Pelunasan AP ' . $voucher->Voucher_No)
                : null;

            $paymentRef = trim((string) ($payload['Payment_Ref'] ?? ''));
            $note = trim((string) ($payload['Note'] ?? ''));
            $bankAccRef = trim((string) ($payload['Bank_Acc_Ref'] ?? $paymentAccNo));
            $tenorId = !empty($payload['Tenor_ID']) ? (int) $payload['Tenor_ID'] : null;

            $ledgerRes = $this->ledgerService->recordApPaymentToLegacy($voucher, [
                'Amount'          => $payAmount,
                'Payment_Date'    => $paymentDate,
                'Payment_Channel' => $channel,
                'Payment_Acc_No'  => $paymentAccNo,
                'Bank_Acc_Ref'    => $bankAccRef,
                'Payment_Ref'     => $paymentRef,
                'Note'            => $note,
            ], $apLiabilityAccNo, $username);

            $now = date('Y-m-d H:i:s');
            DB::table('finance_hr_voucher_payments')->insert([
                'Voucher_ID'       => $voucherId,
                'Tenor_ID'         => $tenorId,
                'Payment_Date'     => $paymentDate,
                'Payment_Channel'  => $channel,
                'Payment_Acc_No'   => $paymentAccNo,
                'Payment_Acc_Name' => $ledgerRes['payment_acc_name'],
                'Bank_Acc_Ref'     => $channel === 'BANK' ? $bankAccRef : null,
                'Amount'           => $payAmount,
                'Payment_Ref'      => mb_substr($paymentRef, 0, 100) ?: null,
                'Proof_Path'       => $proofPath,
                'Legacy_Cash_No'   => $ledgerRes['legacy_cash_no'],
                'Legacy_Bank_No'   => $ledgerRes['legacy_bank_no'],
                'Journal_No'       => $ledgerRes['journal_no'],
                'Note'             => $note ?: null,
                'Created_By'       => mb_substr($username, 0, 50),
                'Created_At'       => $now,
            ]);

            $this->allocatePaymentToTenors($voucherId, $payAmount, $tenorId);

            $newPaid = round((float) $voucher->Paid + $payAmount, 2);
            $newRemain = max(0.0, round((float) $voucher->Total - $newPaid, 2));
            $newStatus = $newRemain <= 0.01 ? 'CLOSED' : 'PARTIAL';

            DB::table('finance_hr_vouchers')->where('ID', $voucherId)->update([
                'Paid'       => $newPaid,
                'Remain_Val' => $newRemain,
                'Status'     => $newStatus,
                'Updated_By' => mb_substr($username, 0, 50),
                'Updated_At' => $now,
            ]);

            if ($newStatus === 'CLOSED') {
                $this->syncSourceCompletion($voucher, $paymentDate, $payload['New_Due_Date'] ?? null, $paymentRef ?: $ledgerRes['journal_no'], $proofPath, $username);
            }

            return $this->findById($voucherId);
        });
    }

    /**
     * Reject / Kembalikan voucher dari Finance ke modul HR/GA asal (Status: RETURNED).
     */
    public function rejectVoucher(int $voucherId, string $reason, $user): object
    {
        $cleanReason = trim($reason);
        if ($cleanReason === '') {
            throw new InvalidArgumentException('Alasan penolakan / revisi wajib diisi.');
        }

        return DB::transaction(function () use ($voucherId, $cleanReason, $user) {
            $voucher = DB::table('finance_hr_vouchers')->where('ID', $voucherId)->lockForUpdate()->first();
            if (!$voucher) {
                throw new InvalidArgumentException('Voucher tidak ditemukan.');
            }
            if (!in_array(strtoupper((string) $voucher->Status), ['SUBMITTED', 'RECEIVED_FINANCE'], true)) {
                throw new InvalidArgumentException('Voucher berstatus ' . $voucher->Status . ' tidak dapat di-reject.');
            }

            $username = (string) ($user->username ?? 'system');
            $now = date('Y-m-d H:i:s');

            DB::table('finance_hr_vouchers')->where('ID', $voucherId)->update([
                'Status'        => 'RETURNED',
                'Reject_Reason' => $cleanReason,
                'Verified_By'   => mb_substr($username, 0, 50),
                'Verified_At'   => $now,
                'Updated_By'    => mb_substr($username, 0, 50),
                'Updated_At'    => $now,
            ]);

            if (!empty($voucher->Legacy_AP_ID)) {
                DB::table('account_payable')->where('ID', (int) $voucher->Legacy_AP_ID)->update([
                    'Status'      => 'RETURNED',
                    'Note'        => mb_substr('Ditolak Finance (' . $username . '): ' . $cleanReason, 0, 1000),
                    'Last_Update' => $now,
                ]);
            }

            if ($voucher->Source_Module === self::SOURCE_ASSET_MAINTENANCE && !empty($voucher->Source_ID)) {
                DB::table('asset_maintenance_schedule')->where('ID', (int) $voucher->Source_ID)->update([
                    'Status'        => 'RETURNED',
                    'Approval_Note' => mb_substr('Ditolak Finance (' . $username . '): ' . $cleanReason, 0, 500),
                    'Updated_At'    => $now,
                ]);
            } elseif ($voucher->Source_Module === self::SOURCE_VEHICLE_TAX && !empty($voucher->Source_ID)) {
                DB::table('vehicle_tax_request')->where('ID', (int) $voucher->Source_ID)->update([
                    'Status'        => 'RETURNED',
                    'Approval_Note' => mb_substr('Ditolak Finance (' . $username . '): ' . $cleanReason, 0, 500),
                    'Updated_At'    => $now,
                ]);
            } elseif ($voucher->Source_Module === self::SOURCE_GA_WORK_ORDER && !empty($voucher->Source_ID) && Schema::hasTable('ga_payment_voucher')) {
                DB::table('ga_payment_voucher')->where('ID', (int) $voucher->Source_ID)->update([
                    'Status'     => 'RETURNED',
                    'Updated_By' => $username,
                    'Updated_At' => $now,
                ]);
            }

            return $this->findById($voucherId);
        });
    }

    public function findById(int $id): ?object
    {
        return DB::table('finance_hr_vouchers')->where('ID', $id)->first();
    }

    /**
     * Ambil payload cetak voucher seragam (style Work Order) untuk seluruh jenis voucher.
     */
    public function getPrintPayload(int $voucherId): array
    {
        $payload = $this->getVoucherReviewPayload($voucherId);
        $v = $payload['voucher'];
        $titles = [
            self::SOURCE_ASSET_MAINTENANCE => ['title' => 'PAYMENT VOUCHER &mdash; PERAWATAN ASET', 'sub' => 'Jadwal Pemeliharaan & Perbaikan Aset'],
            self::SOURCE_GA_WORK_ORDER     => ['title' => 'PAYMENT VOUCHER &mdash; ORDER PEKERJAAN (GA)', 'sub' => 'Order Pekerjaan & Tagihan Vendor'],
            self::SOURCE_VEHICLE_TAX       => ['title' => 'PAYMENT VOUCHER &mdash; PAJAK STNK / KEUR', 'sub' => 'Perpanjangan Pajak & Dokumen Kendaraan'],
            self::SOURCE_HRGA_MANUAL       => ['title' => 'PAYMENT VOUCHER &mdash; PENGAJUAN ' . ($v->Requested_Channel === 'AP' ? 'AP (HUTANG DAGANG)' : 'PEMBAYARAN'), 'sub' => 'Voucher Manual / Referensi Purchase Order'],
        ];
        $meta = $titles[strtoupper((string) $v->Source_Module)] ?? ['title' => 'PAYMENT VOUCHER', 'sub' => 'Voucher Pembayaran'];

        $payload['print_title'] = $meta['title'];
        $payload['print_subtitle'] = $meta['sub'];
        $payload['print_approvers'] = $payload['approval_trail'] ?? [];

        $effectiveChannel = strtoupper((string) ($v->Final_Channel ?: $v->Requested_Channel));
        $statusUpper = strtoupper((string) $v->Status);
        $payload['show_verified_box'] = !empty($v->Verified_By)
            || in_array($statusUpper, ['SUBMITTED', 'RECEIVED_FINANCE', 'RECEIVED_APPROVAL1', 'PARTIAL', 'PAID', 'CLOSED'], true);
        $payload['show_treasury_box'] = ($effectiveChannel === 'DIRECT' && !in_array($statusUpper, ['WAITING_APPROVAL', 'RETURNED', 'CANCELLED'], true))
            || !empty($payload['payments'])
            || in_array($statusUpper, ['PAID', 'CLOSED', 'PARTIAL'], true);

        return $payload;
    }

    /**
     * Ambil daftar approval aktual (ApprovalWorkflowService) dari voucher atau dokumen asal.
     */
    protected function resolveApprovalTrail(object $voucher): array
    {
        $approvalReqId = !empty($voucher->Approval_Request_ID) ? (int) $voucher->Approval_Request_ID : 0;

        if ($approvalReqId <= 0 && !empty($voucher->Source_ID)) {
            $src = strtoupper((string) $voucher->Source_Module);
            if ($src === self::SOURCE_ASSET_MAINTENANCE) {
                $approvalReqId = (int) DB::table('asset_maintenance_schedule')
                    ->where('ID', (int) $voucher->Source_ID)
                    ->value('Approval_Request_ID');
            } elseif ($src === self::SOURCE_VEHICLE_TAX) {
                $approvalReqId = (int) DB::table('vehicle_tax_request')
                    ->where('ID', (int) $voucher->Source_ID)
                    ->value('Approval_Request_ID');
            } elseif ($src === self::SOURCE_GA_WORK_ORDER && Schema::hasTable('ga_payment_voucher')) {
                $ticketId = (int) DB::table('ga_payment_voucher')
                    ->where('ID', (int) $voucher->Source_ID)
                    ->value('Ticket_ID');
                if ($ticketId > 0) {
                    $approvalReqId = (int) DB::table('ga_work_order')
                        ->where('Ticket_ID', $ticketId)
                        ->whereNotNull('Approval_Request_ID')
                        ->orderBy('ID', 'DESC')
                        ->value('Approval_Request_ID');
                }
            }
        }

        if ($approvalReqId <= 0) {
            return [];
        }

        return $this->workflowService->buildApprovalTrail($approvalReqId);
    }

    protected function resolveUserId($user): int
    {
        if (is_object($user) && !empty($user->id)) {
            return (int) $user->id;
        }
        $username = is_object($user) ? (string) ($user->username ?? '') : '';
        if ($username !== '') {
            $id = (int) DB::table('users')->where('username', $username)->value('id');
            if ($id > 0) {
                return $id;
            }
        }
        $fallback = (int) DB::table('users')->where('username', 'admin')->value('id');
        return $fallback > 0 ? $fallback : 1;
    }

    /**
     * Daftar pengajuan Manual untuk halaman Hulu (`hrd-voucher-index`),
     * sinkron live dengan status & saldo di tabel `account_payable`.
     */
    public function listManualVouchers(array $filters = []): array
    {
        $query = DB::table('finance_hr_vouchers as v')
            ->leftJoin('account_payable as ap', 'ap.ID', '=', 'v.Legacy_AP_ID')
            ->where('v.Source_Module', self::SOURCE_HRGA_MANUAL);

        $status = strtoupper(trim((string) ($filters['status'] ?? '')));
        if ($status !== '' && $status !== 'ALL') {
            $query->whereRaw('COALESCE(ap.Status, v.Status) = ?', [$status]);
        }

        return $query->select(
                'v.*',
                DB::raw('COALESCE(ap.Status, v.Status) as Effective_Status'),
                DB::raw('COALESCE(ap.Paid, v.Paid) as Paid'),
                DB::raw('COALESCE(ap.Remain_Val, v.Remain_Val) as Remain_Val'),
                'ap.Status as Legacy_AP_Status'
            )
            ->orderBy('v.ID', 'DESC')
            ->limit(200)
            ->get()
            ->all();
    }

    /**
     * Daftar Voucher / AP Manual yang menunggu verifikasi Finance.
     */
    public function listPendingManualForFinance(): array
    {
        return DB::table('finance_hr_vouchers')
            ->where('Source_Module', self::SOURCE_HRGA_MANUAL)
            ->where('Status', 'SUBMITTED')
            ->orderBy('ID', 'DESC')
            ->limit(200)
            ->get()
            ->all();
    }

    /**
     * Daftar seluruh Voucher berjalur AP yang terhubung ke tabel `account_payable`
     * (diproses via menu legacy: finance-ap-receive -> finance-ap-request -> finance-ap-management).
     */
    public function listApVouchersForFinance(): array
    {
        $rows = DB::table('finance_hr_vouchers as v')
            ->leftJoin('account_payable as ap', 'ap.ID', '=', 'v.Legacy_AP_ID')
            ->where('v.Final_Channel', 'AP')
            ->whereIn('v.Status', ['RECEIVED_FINANCE', 'RECEIVED_APPROVAL1', 'PARTIAL', 'CLOSED', 'PAID'])
            ->select(
                'v.*',
                DB::raw('COALESCE(ap.Status, v.Status) as AP_Status'),
                DB::raw('COALESCE(ap.Paid, v.Paid) as Paid'),
                DB::raw('COALESCE(ap.Remain_Val, v.Remain_Val) as Remain_Val'),
                'ap.Last_Date_Payment as AP_Last_Date_Payment'
            )
            ->orderByRaw("CASE WHEN COALESCE(ap.Status, v.Status) IN ('CLOSED', 'PAID') THEN 1 ELSE 0 END ASC")
            ->orderBy('v.Due_Date', 'ASC')
            ->orderBy('v.ID', 'DESC')
            ->limit(200)
            ->get()
            ->all();

        $ids = array_map(function ($r) {
            return (int) $r->ID;
        }, $rows);

        $tenorsByVoucher = [];
        if (!empty($ids)) {
            $allTenors = DB::table('finance_hr_voucher_tenors')
                ->whereIn('Voucher_ID', $ids)
                ->orderBy('Tenor_Seq', 'ASC')
                ->get();
            foreach ($allTenors as $t) {
                $tenorsByVoucher[(int) $t->Voucher_ID][] = $t;
            }
        }

        foreach ($rows as $row) {
            $row->tenors = $tenorsByVoucher[(int) $row->ID] ?? [];
        }

        return $rows;
    }

    /**
     * Riwayat voucher HRGA Manual yang sudah selesai / dibatalkan / dikembalikan.
     */
    public function listHistoryManualForFinance(): array
    {
        return DB::table('finance_hr_vouchers')
            ->where('Source_Module', self::SOURCE_HRGA_MANUAL)
            ->whereIn('Status', ['PAID', 'CLOSED', 'RETURNED', 'CANCELLED'])
            ->orderBy('ID', 'DESC')
            ->limit(100)
            ->get()
            ->all();
    }

    /**
     * Map Requested_Channel berdasarkan (Source_Module, Source_ID) untuk memperkaya tabel Finance eksisting.
     */
    public function getChannelMapForSources(string $sourceModule, array $sourceIds): array
    {
        if (empty($sourceIds) || !Schema::hasTable('finance_hr_vouchers')) {
            return [];
        }

        $rows = DB::table('finance_hr_vouchers')
            ->where('Source_Module', strtoupper($sourceModule))
            ->whereIn('Source_ID', $sourceIds)
            ->where('Status', '!=', 'CANCELLED')
            ->orderBy('ID', 'DESC')
            ->get(['ID', 'Source_ID', 'Voucher_No', 'Requested_Channel', 'Final_Channel', 'Status']);

        $map = [];
        foreach ($rows as $r) {
            $sid = (int) $r->Source_ID;
            if (!isset($map[$sid])) {
                $map[$sid] = $r;
            }
        }

        return $map;
    }

    protected function normalizeAmounts(array $data, ?object $fallback = null): array
    {
        $subtotal = isset($data['Subtotal']) ? round((float) $data['Subtotal'], 2) : ($fallback ? (float) $fallback->Subtotal : 0.0);
        $dp = isset($data['DP']) ? round((float) $data['DP'], 2) : ($fallback ? (float) $fallback->DP : 0.0);
        $mgmtFee = isset($data['Management_Fee']) ? round((float) $data['Management_Fee'], 2) : ($fallback ? (float) $fallback->Management_Fee : 0.0);
        $delivery = isset($data['Delivery_Costs']) ? round((float) $data['Delivery_Costs'], 2) : ($fallback ? (float) $fallback->Delivery_Costs : 0.0);
        $ppnType = trim((string) ($data['PPN_Type'] ?? ($fallback ? $fallback->PPN_Type : '0')));
        $ppn = isset($data['PPN']) ? round((float) $data['PPN'], 2) : ($fallback ? (float) $fallback->PPN : 0.0);
        $pphType = trim((string) ($data['PPH_Type'] ?? ($fallback ? $fallback->PPH_Type : '0')));
        $pph = isset($data['PPH']) ? round((float) $data['PPH'], 2) : ($fallback ? (float) $fallback->PPH : 0.0);
        $materai = isset($data['Materai']) ? round((float) $data['Materai'], 2) : ($fallback ? (float) $fallback->Materai : 0.0);

        $computed = round(max(0.0, $subtotal + $delivery + $mgmtFee + $ppn + $materai + $dp - $pph), 2);
        $explicitTotal = isset($data['Total']) ? round((float) $data['Total'], 2) : 0.0;

        if ($subtotal <= 0 && $explicitTotal > 0 && $delivery <= 0 && $mgmtFee <= 0 && $ppn <= 0 && $materai <= 0 && $dp <= 0) {
            $subtotal = $explicitTotal;
            $computed = $explicitTotal;
        }

        $total = $explicitTotal > 0 ? $explicitTotal : $computed;

        return [
            'Subtotal'       => $subtotal,
            'DP'             => $dp,
            'Management_Fee' => $mgmtFee,
            'Delivery_Costs' => $delivery,
            'PPN_Type'       => $ppnType ?: '0',
            'PPN'            => $ppn,
            'PPH_Type'       => $pphType ?: '0',
            'PPH'            => $pph,
            'Materai'        => $materai,
            'Total'          => $total,
        ];
    }

    protected function normalizeTenorsInput($rawTenors, float $totalAmount, string $defaultDueDate, bool $requireExactSum): array
    {
        $clean = [];
        if (is_string($rawTenors)) {
            $decoded = json_decode($rawTenors, true);
            $rawTenors = is_array($decoded) ? $decoded : [];
        }

        if (is_array($rawTenors)) {
            foreach ($rawTenors as $idx => $t) {
                if (!is_array($t)) {
                    continue;
                }
                $amt = round((float) ($t['Amount'] ?? 0), 2);
                $due = trim((string) ($t['Due_Date'] ?? ''));
                if ($amt <= 0 && $due === '') {
                    continue;
                }
                if ($amt <= 0) {
                    throw new InvalidArgumentException('Tenor #' . ($idx + 1) . ': Nominal harus lebih dari 0.');
                }
                if ($due === '') {
                    throw new InvalidArgumentException('Tenor #' . ($idx + 1) . ': Tanggal jatuh tempo wajib diisi.');
                }
                $clean[] = [
                    'Tenor_Seq'   => count($clean) + 1,
                    'Tenor_Label' => mb_substr(trim((string) ($t['Tenor_Label'] ?? ('Termin ' . (count($clean) + 1)))), 0, 80),
                    'Due_Date'    => date('Y-m-d', strtotime($due)),
                    'Amount'      => $amt,
                    'Paid_Amount' => 0.00,
                    'Status'      => 'OPEN',
                    'Note'        => mb_substr(trim((string) ($t['Note'] ?? '')), 0, 255),
                ];
            }
        }

        if (empty($clean)) {
            if (!$requireExactSum) {
                return [];
            }
            return [
                [
                    'Tenor_Seq'   => 1,
                    'Tenor_Label' => 'Jatuh Tempo Penuh',
                    'Due_Date'    => date('Y-m-d', strtotime($defaultDueDate ?: date('Y-m-d'))),
                    'Amount'      => round($totalAmount, 2),
                    'Paid_Amount' => 0.00,
                    'Status'      => 'OPEN',
                    'Note'        => '',
                ],
            ];
        }

        if ($requireExactSum) {
            $sumTenors = 0.0;
            foreach ($clean as $t) {
                $sumTenors += $t['Amount'];
            }
            if (abs(round($sumTenors, 2) - round($totalAmount, 2)) >= 0.01) {
                throw new InvalidArgumentException(
                    'Total jadwal tenor (Rp ' . number_format($sumTenors, 0, ',', '.')
                    . ') harus sama dengan Total AP (Rp ' . number_format($totalAmount, 0, ',', '.') . ').'
                );
            }
        }

        return $clean;
    }

    protected function replaceLines(int $voucherId, array $lines): void
    {
        DB::table('finance_hr_voucher_lines')->where('Voucher_ID', $voucherId)->delete();
        $now = date('Y-m-d H:i:s');
        $rows = [];
        foreach ($lines as $idx => $ln) {
            $rows[] = [
                'Voucher_ID'                => $voucherId,
                'Line_Order'                => $idx + 1,
                'Trial_Balance_Header_No'   => $ln['Trial_Balance_Header_No'] ?: null,
                'Trial_Balance_Header_Name' => $ln['Trial_Balance_Header_Name'] ?: null,
                'Acc_No'                    => $ln['Acc_No'],
                'Acc_Name'                  => $ln['Acc_Name'] ?: null,
                'Line_Side'                 => $ln['Line_Side'],
                'Component_Tag'             => $ln['Component_Tag'] ?: 'OTHER',
                'Amount'                    => round((float) $ln['Amount'], 2),
                'Department'                => $ln['Department'] ?: null,
                'Remark'                    => mb_substr((string) ($ln['Remark'] ?? ''), 0, 255) ?: null,
                'Created_At'                => $now,
                'Updated_At'                => $now,
            ];
        }
        if (!empty($rows)) {
            DB::table('finance_hr_voucher_lines')->insert($rows);
        }
    }

    protected function replaceTenors(int $voucherId, array $tenors): void
    {
        DB::table('finance_hr_voucher_tenors')->where('Voucher_ID', $voucherId)->delete();
        if (empty($tenors)) {
            return;
        }
        $now = date('Y-m-d H:i:s');
        $rows = [];
        foreach ($tenors as $t) {
            $rows[] = [
                'Voucher_ID'  => $voucherId,
                'Tenor_Seq'   => $t['Tenor_Seq'],
                'Tenor_Label' => $t['Tenor_Label'],
                'Due_Date'    => $t['Due_Date'],
                'Amount'      => $t['Amount'],
                'Paid_Amount' => 0.00,
                'Status'      => 'OPEN',
                'Note'        => $t['Note'] ?: null,
                'Created_At'  => $now,
                'Updated_At'  => $now,
            ];
        }
        DB::table('finance_hr_voucher_tenors')->insert($rows);
    }

    protected function allocatePaymentToTenors(int $voucherId, float $payAmount, ?int $preferredTenorId): void
    {
        $remainingToAllocate = round($payAmount, 2);
        $tenors = DB::table('finance_hr_voucher_tenors')
            ->where('Voucher_ID', $voucherId)
            ->orderByRaw($preferredTenorId ? "CASE WHEN ID = {$preferredTenorId} THEN 0 ELSE 1 END ASC" : 'Tenor_Seq ASC')
            ->orderBy('Tenor_Seq', 'ASC')
            ->get();

        foreach ($tenors as $tenor) {
            if ($remainingToAllocate <= 0.001) {
                break;
            }
            $tenorRemain = round((float) $tenor->Amount - (float) $tenor->Paid_Amount, 2);
            if ($tenorRemain <= 0) {
                continue;
            }
            $alloc = min($remainingToAllocate, $tenorRemain);
            $newPaid = round((float) $tenor->Paid_Amount + $alloc, 2);
            $newStatus = ($tenor->Amount - $newPaid) <= 0.01 ? 'PAID' : 'PARTIAL';

            DB::table('finance_hr_voucher_tenors')->where('ID', (int) $tenor->ID)->update([
                'Paid_Amount' => $newPaid,
                'Status'      => $newStatus,
                'Updated_At'  => date('Y-m-d H:i:s'),
            ]);

            $remainingToAllocate = round($remainingToAllocate - $alloc, 2);
        }
    }

    protected function findPrimaryCreditLine(array $lines, string $paymentMethod): ?array
    {
        $preferredTag = $paymentMethod === 'CASH' ? 'CASH' : 'BANK';
        foreach ($lines as $ln) {
            if ($ln['Line_Side'] === 'CREDIT' && strtoupper((string) ($ln['Component_Tag'] ?? '')) === $preferredTag) {
                return $ln;
            }
        }
        foreach ($lines as $ln) {
            if ($ln['Line_Side'] === 'CREDIT' && strtoupper((string) ($ln['Component_Tag'] ?? '')) !== 'PPH') {
                return $ln;
            }
        }
        foreach ($lines as $ln) {
            if ($ln['Line_Side'] === 'CREDIT') {
                return $ln;
            }
        }
        return null;
    }

    protected function syncSourceCompletion(
        object $voucher,
        string $paidDate,
        ?string $newDueDate,
        string $paymentRef,
        ?string $proofPath,
        string $username
    ): void {
        if ($voucher->Source_Module === self::SOURCE_ASSET_MAINTENANCE && !empty($voucher->Source_ID)) {
            $row = DB::table('asset_maintenance_schedule')->where('ID', (int) $voucher->Source_ID)->first();
            if ($row && in_array($row->Status, ['FINANCE_SUBMITTED', 'RETURNED'], true)) {
                app(AssetMaintenanceScheduleService::class)->confirmScheduleAndNotify((int) $voucher->Source_ID);
            }
        } elseif ($voucher->Source_Module === self::SOURCE_VEHICLE_TAX && !empty($voucher->Source_ID)) {
            $row = DB::table('vehicle_tax_request')->where('ID', (int) $voucher->Source_ID)->first();
            if ($row && $row->Status !== 'PAID') {
                $due = $newDueDate ?: date('Y-m-d', strtotime('+1 year', strtotime($paidDate)));
                app(VehicleFleetService::class)->markTaxPaid(
                    (int) $voucher->Source_ID,
                    $paidDate,
                    $due,
                    $username,
                    $proofPath ?: $paymentRef,
                    null
                );
            }
        } elseif ($voucher->Source_Module === self::SOURCE_GA_WORK_ORDER && !empty($voucher->Source_ID) && Schema::hasTable('ga_payment_voucher')) {
            $row = DB::table('ga_payment_voucher')->where('ID', (int) $voucher->Source_ID)->first();
            if ($row && strtoupper((string) $row->Status) === 'FINANCE_SUBMITTED') {
                app(GaPaymentVoucherService::class)->markPaid(
                    (int) $voucher->Source_ID,
                    [
                        'Paid_Date'   => $paidDate,
                        'Payment_Ref' => $paymentRef ?: ($proofPath ?: $voucher->Voucher_No),
                    ],
                    $username,
                    null
                );
            }
        }
    }

    protected function assertNoDuplicateVendorReference(
        string $vendorId,
        string $reference,
        ?int $excludeVoucherId = null,
        ?int $excludeApId = null
    ): void {
        $dupHr = DB::table('finance_hr_vouchers')
            ->where('Vendor_ID', $vendorId)
            ->where('Reference', $reference)
            ->where('Inv_Type', '!=', 'PARTIAL')
            ->whereNotIn('Status', ['CANCELLED', 'CANCELED']);
        if ($excludeVoucherId) {
            $dupHr->where('ID', '!=', $excludeVoucherId);
        }
        if ($dupHr->exists()) {
            throw new InvalidArgumentException('No. Reference/Faktur (' . $reference . ') untuk vendor ini sudah ada di sistem.');
        }

        $dupAp = DB::table('account_payable')
            ->where('Vendor_ID', $vendorId)
            ->where('Inv_Vendor', $reference)
            ->where('Inv_Type', '!=', 'PARTIAL')
            ->whereNotIn('Status', ['CANCELED', 'CANCELLED']);
        if ($excludeApId) {
            $dupAp->where('ID', '!=', $excludeApId);
        }
        if ($dupAp->exists()) {
            throw new InvalidArgumentException('No. Reference/Faktur (' . $reference . ') untuk vendor ini sudah terdaftar di Account Payable.');
        }
    }

    protected function generateVoucherNo(string $prefix, Carbon $now): string
    {
        $fullPrefix = $prefix . $now->format('Ym') . '-';
        $latest = DB::table('finance_hr_vouchers')
            ->where('Voucher_No', 'LIKE', $fullPrefix . '%')
            ->orderBy('ID', 'DESC')
            ->lockForUpdate()
            ->value('Voucher_No');

        $next = 1;
        if ($latest && preg_match('/-(\d+)$/', (string) $latest, $m)) {
            $next = ((int) $m[1]) + 1;
        }

        return $fullPrefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
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

        if (Schema::hasTable('meta_files')) {
            DB::table('meta_files')->insert([
                'Filename'    => $newName,
                'Path'        => $relPath,
                'Extension'   => $ext,
                'Description' => mb_substr($description, 0, 250),
                'Topic'       => $topic,
                'Map_To'      => mb_substr($mapTo, 0, 100),
                'Created_At'  => now(),
            ]);
        }

        return $relPath;
    }
}
