<?php

namespace App\Services\Finance;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Service ledger akuntansi & sinkronisasi transaksi Finance ke tabel legacy:
 * - cash / cash_dtl / cash_log (CASH_OUT)
 * - bank / bank_dtl / bank_log (BANK_OUT)
 * - account_payable / account_payable_payment_log (AP pipeline)
 * - ho (Jurnal final perusahaan)
 */
class FinanceVoucherLedgerService
{
    /** @var FinanceCoaService */
    protected $coaService;

    public function __construct(FinanceCoaService $coaService)
    {
        $this->coaService = $coaService;
    }

    /**
     * Validasi dan perkaya baris COA multi-debet & multi-kredit agar seimbang (Debet == Credit).
     *
     * @param array $rawLines
     * @return array
     */
    public function validateBalancedLines(array $rawLines): array
    {
        $cleanLines = [];
        $totalDebet = 0.0;
        $totalCredit = 0.0;

        foreach ($rawLines as $idx => $line) {
            if (!is_array($line)) {
                continue;
            }
            $accNo = trim((string) ($line['Acc_No'] ?? ''));
            $side = strtoupper(trim((string) ($line['Line_Side'] ?? 'DEBET')));
            $amount = round((float) ($line['Amount'] ?? 0), 2);

            if ($accNo === '' && $amount <= 0) {
                continue;
            }
            if ($accNo === '') {
                throw new InvalidArgumentException('Baris COA #' . ($idx + 1) . ': Kode Akun COA wajib dipilih.');
            }
            if (!in_array($side, ['DEBET', 'CREDIT'], true)) {
                throw new InvalidArgumentException('Baris COA #' . ($idx + 1) . ': Sisi jurnal harus DEBET atau CREDIT.');
            }
            if ($amount <= 0) {
                throw new InvalidArgumentException('Baris COA #' . ($idx + 1) . ' (' . $accNo . '): Nominal harus lebih dari 0.');
            }

            $coa = $this->coaService->findCoaByAccNo($accNo);
            if (!$coa) {
                throw new InvalidArgumentException('Kode Akun COA (' . $accNo . ') tidak ditemukan di branch_coa.');
            }

            if ($side === 'DEBET') {
                $totalDebet += $amount;
            } else {
                $totalCredit += $amount;
            }

            $cleanLines[] = [
                'Line_Order'                => count($cleanLines) + 1,
                'Trial_Balance_Header_No'   => trim((string) ($line['Trial_Balance_Header_No'] ?? ($coa->Trial_Balance_Header_No ?? ''))),
                'Trial_Balance_Header_Name' => trim((string) ($line['Trial_Balance_Header_Name'] ?? ($coa->Trial_Balance_Header_Name ?? ''))),
                'Acc_No'                    => $coa->Acc_No,
                'Acc_Name'                  => $coa->Acc_Name ?: '',
                'Line_Side'                 => $side,
                'Component_Tag'             => strtoupper(trim((string) ($line['Component_Tag'] ?? 'OTHER'))) ?: 'OTHER',
                'Amount'                    => $amount,
                'Department'                => trim((string) ($line['Department'] ?? '')),
                'Remark'                    => trim((string) ($line['Remark'] ?? '')),
            ];
        }

        if (count($cleanLines) < 2) {
            throw new InvalidArgumentException('Jurnal minimal harus memiliki 2 baris (minimal 1 Debet dan 1 Kredit).');
        }

        $totalDebet = round($totalDebet, 2);
        $totalCredit = round($totalCredit, 2);

        if ($totalDebet <= 0 || $totalCredit <= 0) {
            throw new InvalidArgumentException('Total Debet dan Total Kredit harus lebih dari 0.');
        }

        if (abs($totalDebet - $totalCredit) >= 0.01) {
            throw new InvalidArgumentException(
                'Jurnal tidak seimbang! Total Debet (Rp ' . number_format($totalDebet, 2, ',', '.')
                . ') != Total Kredit (Rp ' . number_format($totalCredit, 2, ',', '.') . ').'
            );
        }

        return [
            'lines'        => $cleanLines,
            'total_debet'  => $totalDebet,
            'total_credit' => $totalCredit,
        ];
    }

    /**
     * Posting jurnal final seimbang ke tabel `ho` (bukan `branch_journal`).
     */
    public function postJournalToHo(
        object $voucher,
        array $lines,
        string $journalNo,
        string $postingDate,
        string $username,
        ?string $overrideTaskId = null
    ): void {
        $now = date('Y-m-d H:i:s');
        $postDate = date('Y-m-d', strtotime($postingDate ?: $now));
        $taskId = mb_substr((string) ($overrideTaskId ?: $voucher->Voucher_No), 0, 100);
        $location = mb_substr((string) ($voucher->Location ?: 'GEU-HO'), 0, 25);
        $docNo = mb_substr((string) ($voucher->Reference ?: $voucher->Voucher_No), 0, 200);
        $extDoc = mb_substr((string) ($voucher->PO_References ?: ($voucher->Source_No ?: '')), 0, 200);
        $partnerCode = mb_substr((string) ($voucher->Vendor_ID ?: ''), 0, 30);
        $partnerName = mb_substr((string) ($voucher->Vendor_Name ?: ''), 0, 60);
        $dept = mb_substr((string) ($voucher->Department ?: 'HCGA'), 0, 30);
        $createdBy = mb_substr($username, 0, 25);
        $userResp = mb_substr($username, 0, 30);

        $rows = [];
        foreach ($lines as $line) {
            $side = strtoupper((string) ($line['Line_Side'] ?? 'DEBET'));
            $amount = round((float) ($line['Amount'] ?? 0), 2);
            if ($amount <= 0) {
                continue;
            }
            $debet = $side === 'DEBET' ? $amount : 0.00;
            $credit = $side === 'CREDIT' ? $amount : 0.00;
            $remark = trim((string) ($line['Remark'] ?? ''));
            if ($remark === '') {
                $remark = (string) ($voucher->Subject_Payment ?: ($voucher->Description ?: $voucher->Voucher_No));
            }

            $rows[] = [
                'Task_ID'        => $taskId,
                'Location'       => $location,
                'Group_No'       => (string) ($line['Trial_Balance_Header_No'] ?? ''),
                'Posting_Date'   => $postDate,
                'Document_No'    => $docNo,
                'External_Doc'   => $extDoc,
                'Partner_Code'   => $partnerCode,
                'Partner_Name'   => $partnerName,
                'Department'     => $dept,
                'Journal_No'     => mb_substr($journalNo, 0, 60),
                'Account_No'     => mb_substr((string) $line['Acc_No'], 0, 50),
                'Account_Name'   => mb_substr((string) ($line['Acc_Name'] ?? ''), 0, 100),
                'Reference_No'   => mb_substr((string) $voucher->Voucher_No, 0, 100),
                'Reference_Desc' => (string) ($voucher->Subject_Payment ?: ($voucher->Description ?: '')),
                'Remark'         => $remark,
                'Debet'          => $debet,
                'Credit'         => $credit,
                'User_Resp'      => $userResp,
                'Created_By'     => $createdBy,
                'Created_Date'   => $now,
                'Task_Path'      => 'finance-voucher',
            ];
        }

        if (!empty($rows)) {
            DB::table('ho')->insert($rows);
        }
    }

    /**
     * Sinkron pembayaran langsung Kas ke `cash`, `cash_dtl`, dan `cash_log`.
     */
    public function syncToCashOut(
        object $voucher,
        array $lines,
        string $cashDate,
        string $cashAccNo,
        float $totalAmount,
        string $username
    ): string {
        $now = date('Y-m-d H:i:s');
        $dateOnly = date('Y-m-d', strtotime($cashDate ?: $now));
        $location = mb_substr((string) ($voucher->Location ?: 'GEU-HO'), 0, 20);
        $dept = mb_substr((string) ($voucher->Department ?: 'HCGA'), 0, 50);
        $userShort = mb_substr($username, 0, 20);
        $cashNo = $this->generateCashOutNo($location);

        DB::table('cash')->insert([
            'Location'     => $location,
            'Cash_No'      => $cashNo,
            'Voucher_No'   => mb_substr((string) $voucher->Voucher_No, 0, 50),
            'Cash_Type'    => 'CASH_OUT',
            'Currency'     => 'Rp',
            'Cash_Date'    => $dateOnly,
            'Department'   => $dept,
            'Description'  => mb_substr((string) ($voucher->Subject_Payment ?: ($voucher->Description ?: $voucher->Voucher_No)), 0, 500),
            'Status'       => 'CLOSED',
            'Total'        => round($totalAmount, 2),
            'Created_Date' => $now,
            'Created_By'   => $userShort,
            'Posting'      => 1,
            'Posting_Date' => $now,
            'Posting_By'   => mb_substr($username, 0, 30),
            'Reference'    => mb_substr((string) ($voucher->Reference ?: $voucher->Voucher_No), 0, 255),
        ]);

        foreach ($lines as $line) {
            if (strtoupper((string) ($line['Line_Side'] ?? '')) !== 'DEBET') {
                continue;
            }
            $amt = round((float) ($line['Amount'] ?? 0), 2);
            if ($amt <= 0) {
                continue;
            }
            DB::table('cash_dtl')->insert([
                'Cash_No'     => $cashNo,
                'Date_Cash'   => $dateOnly,
                'Reference'   => mb_substr((string) ($voucher->Reference ?: $voucher->Voucher_No), 0, 40),
                'Remark'      => (string) ($line['Remark'] ?: ($voucher->Subject_Payment ?: $voucher->Voucher_No)),
                'Acc_No'      => mb_substr((string) $line['Acc_No'], 0, 30),
                'Bal_Acc'     => mb_substr($cashAccNo, 0, 30),
                'Cash_Val'    => $amt,
                'Last_Update' => $now,
                'Created_By'  => mb_substr($username, 0, 30),
                'Department'  => mb_substr((string) ($line['Department'] ?: $dept), 0, 40),
            ]);
        }

        DB::table('cash_log')->insert([
            'Location'     => $location,
            'Cash_No'      => $cashNo,
            'Transaction'  => 'CASH OUT VOUCHER',
            'Description'  => (string) ($voucher->Subject_Payment ?: $voucher->Voucher_No),
            'Reference'    => mb_substr((string) $voucher->Voucher_No, 0, 50),
            'Status'       => 'CLOSED',
            'Created_Date' => $now,
            'Created_By'   => mb_substr($username, 0, 30),
        ]);

        return $cashNo;
    }

    /**
     * Sinkron pembayaran langsung Bank ke `bank`, `bank_dtl`, dan `bank_log`.
     */
    public function syncToBankOut(
        object $voucher,
        array $lines,
        string $bankDate,
        string $bankAccRef,
        string $bankCoaNo,
        float $totalAmount,
        string $username
    ): string {
        $now = date('Y-m-d H:i:s');
        $dateOnly = date('Y-m-d', strtotime($bankDate ?: $now));
        $location = mb_substr((string) ($voucher->Location ?: 'GEU-HO'), 0, 20);
        $dept = mb_substr((string) ($voucher->Department ?: 'HCGA'), 0, 100);
        $userShort = mb_substr($username, 0, 20);
        $bankNo = $this->generateBankOutNo($location);
        $accValue = mb_substr($bankAccRef !== '' ? $bankAccRef : $bankCoaNo, 0, 30);

        DB::table('bank')->insert([
            'Location'     => $location,
            'Bank_No'      => $bankNo,
            'Bank_Type'    => 'BANK_OUT',
            'Bank_Date'    => $dateOnly,
            'Bank_Acc'     => $accValue,
            'Currency'     => 'Rp',
            'Description'  => 'BANK OUT ' . mb_substr((string) ($voucher->Subject_Payment ?: $voucher->Voucher_No), 0, 400),
            'Status'       => 'CLOSED',
            'Total'        => round($totalAmount, 2),
            'Created_Date' => $now,
            'Created_By'   => $userShort,
            'Reference'    => mb_substr((string) ($voucher->Voucher_No ?: ($voucher->Reference ?: '')), 0, 50),
            'Posting'      => 1,
            'Posting_By'   => mb_substr($username, 0, 30),
            'Posting_Date' => $now,
            'Department'   => $dept,
        ]);

        foreach ($lines as $line) {
            if (strtoupper((string) ($line['Line_Side'] ?? '')) !== 'DEBET') {
                continue;
            }
            $amt = round((float) ($line['Amount'] ?? 0), 2);
            if ($amt <= 0) {
                continue;
            }
            DB::table('bank_dtl')->insert([
                'Bank_No'     => $bankNo,
                'Date_Bank'   => $dateOnly,
                'Reference'   => mb_substr((string) ($voucher->Reference ?: $voucher->Voucher_No), 0, 40),
                'Remark'      => (string) ($line['Remark'] ?: ($voucher->Subject_Payment ?: $voucher->Voucher_No)),
                'Acc_No'      => mb_substr((string) $line['Acc_No'], 0, 30),
                'Bal_Acc'     => mb_substr($bankCoaNo, 0, 30),
                'Bank_Val'    => $amt,
                'Last_Update' => $now,
                'Created_By'  => mb_substr($username, 0, 40),
                'Date_Out'    => $dateOnly,
                'Department'  => mb_substr((string) ($line['Department'] ?: $dept), 0, 100),
            ]);
        }

        DB::table('bank_log')->insert([
            'Location'     => $location,
            'Bank_No'      => $bankNo,
            'Bank_Acc'     => $accValue,
            'Transaction'  => 'BANK OUT VOUCHER',
            'Description'  => (string) ($voucher->Subject_Payment ?: $voucher->Voucher_No),
            'Reference'    => mb_substr((string) $voucher->Voucher_No, 0, 50),
            'Status'       => 'CLOSED',
            'Bank_Value'   => round($totalAmount, 2),
            'Created_Date' => $now,
            'Created_By'   => mb_substr($username, 0, 30),
        ]);

        return $bankNo;
    }

    /**
     * Sinkron Voucher berjalur AP ke `account_payable` sesuai pipeline:
     * manual.blade.php -> receive.blade.php (RECEIVED_FINANCE) -> request.blade.php -> management.blade.php
     */
    public function syncToAccountPayable(object $voucher, string $username, string $targetStatus = 'RECEIVED_FINANCE'): array
    {
        $now = date('Y-m-d H:i:s');
        $location = mb_substr((string) ($voucher->Location ?: 'GEU-HO'), 0, 20);
        $taskId = $voucher->Legacy_AP_Task_ID ?: $this->generateApTaskId($location, $username);
        $isReceivedFinance = strtoupper(trim($targetStatus)) === 'RECEIVED_FINANCE';
        $categoryMap = [
            'ASSET_MAINTENANCE' => 'PERAWATAN ASET',
            'GA_WORK_ORDER'     => 'ORDER PEKERJAAN',
            'VEHICLE_TAX'       => 'PAJAK KENDARAAN',
            'HRGA_MANUAL'       => 'VOUCHER AP',
        ];
        $purchaseCategory = $categoryMap[strtoupper((string) $voucher->Source_Module)] ?? 'VOUCHER';

        $payload = [
            'Task_ID'               => $taskId,
            'Location'              => $location,
            'Department'            => mb_substr((string) ($voucher->Department ?: 'HCGA'), 0, 50),
            'PIC'                   => mb_substr((string) ($voucher->PIC_Vendor ?: $voucher->Created_By), 0, 30),
            'Purchase_Category'     => mb_substr($purchaseCategory, 0, 60),
            'Purchase_Sub_Category' => mb_substr((string) ($voucher->Expense_Category ?: 'VOUCHER AP'), 0, 60),
            'Purchase_Type'         => $voucher->PO_References ? 'PO' : 'MANUAL',
            'Reference'             => mb_substr((string) ($voucher->Reference ?: $voucher->Voucher_No), 0, 150),
            'Description'           => (string) ($voucher->Description ?: $voucher->Subject_Payment),
            'Subtotal'              => round((float) $voucher->Subtotal, 2),
            'PPN'                   => round((float) $voucher->PPN, 2),
            'PPN_Type'              => mb_substr((string) ($voucher->PPN_Type ?: '0'), 0, 30),
            'PPH'                   => round((float) $voucher->PPH, 2),
            'Total'                 => round((float) $voucher->Total, 2),
            'Remain_Val'            => round((float) $voucher->Remain_Val, 2),
            'Status'                => $targetStatus,
            'Created_By'            => mb_substr((string) $voucher->Created_By, 0, 30),
            'Date_Received'         => $voucher->Date_Received ?: date('Y-m-d'),
            'Inv_Type'              => mb_substr((string) ($voucher->Inv_Type ?: 'SINGLE'), 0, 10),
            'Due_Date'              => $voucher->Due_Date ?: date('Y-m-d'),
            'Inv_Vendor'            => mb_substr((string) ($voucher->Reference ?: $voucher->Voucher_No), 0, 60),
            'Subject_Payment'       => (string) ($voucher->Subject_Payment ?: $voucher->Voucher_No),
            'Inv_Vendor_Date'       => $voucher->Inv_Vendor_Date ?: date('Y-m-d'),
            'Vendor_ID'             => mb_substr((string) ($voucher->Vendor_ID ?: ''), 0, 40),
            'Vendor_Name'           => mb_substr((string) ($voucher->Vendor_Name ?: ''), 0, 100),
            'Finance_Ref'           => mb_substr((string) $voucher->Voucher_No, 0, 60),
            'Finance_Received_Date' => $isReceivedFinance ? $now : null,
            'Finance_Received_By'   => $isReceivedFinance ? mb_substr($username, 0, 60) : null,
            'Manual'                => $voucher->PO_References ? '0' : '1',
            'Paid'                  => round((float) $voucher->Paid, 2),
            'Note'                  => (string) ($voucher->Finance_Note ?: ''),
            'PO_References'         => (string) ($voucher->PO_References ?: ''),
            'PR_No'                 => mb_substr((string) ($voucher->PR_No ?: ''), 0, 60),
            'PPH_Type'              => mb_substr((string) ($voucher->PPH_Type ?: '0'), 0, 30),
            'DP'                    => round((float) $voucher->DP, 2),
            'Management_Fee'        => round((float) $voucher->Management_Fee, 2),
            'Delivery_Costs'        => round((float) $voucher->Delivery_Costs, 2),
            'Currency'              => 'IDR',
            'Status_Approval'       => $isReceivedFinance ? 'APPROVED' : 'PENDING',
            'File_Document'         => mb_substr((string) ($voucher->Attachment_Path ?: ''), 0, 100),
            'Bank_Name'             => mb_substr((string) ($voucher->Bank_Name ?: ''), 0, 50),
            'Bank_Acc'              => mb_substr((string) ($voucher->Bank_Acc ?: ''), 0, 40),
            'Bank_Acc_Name'         => mb_substr((string) ($voucher->Bank_Acc_Name ?: ''), 0, 255),
            'Bank_Type'             => mb_substr((string) ($voucher->Bank_Type ?: 'DIRECT TRANSFER'), 0, 30),
            'materai'               => round((float) $voucher->Materai, 0),
            'Last_Update'           => $now,
        ];

        $pphType = strtoupper((string) ($voucher->PPH_Type ?: ''));
        if (in_array($pphType, ['PPH23_4', 'PPH23_2', 'PPH23_15', 'PPH42', 'PPH_21_5'], true)) {
            $payload[$pphType] = round((float) $voucher->PPH, 2);
        }

        if (!empty($voucher->Legacy_AP_ID) && DB::table('account_payable')->where('ID', (int) $voucher->Legacy_AP_ID)->exists()) {
            DB::table('account_payable')->where('ID', (int) $voucher->Legacy_AP_ID)->update($payload);
            $apId = (int) $voucher->Legacy_AP_ID;
        } else {
            $payload['Dateinsert'] = $now;
            $apId = (int) DB::table('account_payable')->insertGetId($payload);
        }

        return [
            'ap_id'      => $apId,
            'ap_task_id' => $taskId,
        ];
    }

    /**
     * Catat pelunasan fleksibel AP (Partial atau Full) oleh Treasury:
     * - Update account_payable (Paid, Remain_Val, Status PARTIAL/CLOSED)
     * - Insert account_payable_payment_log
     * - Sinkron ke cash/cash_dtl atau bank/bank_dtl
     * - Posting jurnal pelunasan (Debet AP Liability, Kredit Kas/Bank) ke tabel `ho`
     */
    public function recordApPaymentToLegacy(
        object $voucher,
        array $paymentData,
        string $apLiabilityAccNo,
        string $username
    ): array {
        $amount = round((float) $paymentData['Amount'], 2);
        $paymentDate = date('Y-m-d', strtotime((string) ($paymentData['Payment_Date'] ?? date('Y-m-d'))));
        $channel = strtoupper(trim((string) ($paymentData['Payment_Channel'] ?? 'BANK'))) === 'CASH' ? 'CASH' : 'BANK';
        $paymentCoaNo = trim((string) ($paymentData['Payment_Acc_No'] ?? ''));
        $bankAccRef = trim((string) ($paymentData['Bank_Acc_Ref'] ?? ''));
        $paymentRef = trim((string) ($paymentData['Payment_Ref'] ?? ''));
        $note = trim((string) ($paymentData['Note'] ?? ''));
        $now = date('Y-m-d H:i:s');

        $apLiabilityCoa = $this->coaService->findCoaByAccNo($apLiabilityAccNo);
        $payCoa = $this->coaService->findCoaByAccNo($paymentCoaNo);
        if (!$apLiabilityCoa || !$payCoa) {
            throw new InvalidArgumentException('COA Hutang AP atau COA Kas/Bank pelunasan tidak valid.');
        }

        $journalLines = [
            [
                'Trial_Balance_Header_No'   => $apLiabilityCoa->Trial_Balance_Header_No ?? '',
                'Trial_Balance_Header_Name' => $apLiabilityCoa->Trial_Balance_Header_Name ?? '',
                'Acc_No'                    => $apLiabilityCoa->Acc_No,
                'Acc_Name'                  => $apLiabilityCoa->Acc_Name,
                'Line_Side'                 => 'DEBET',
                'Component_Tag'             => 'AP',
                'Amount'                    => $amount,
                'Department'                => (string) ($voucher->Department ?: 'HCGA'),
                'Remark'                    => 'Pelunasan AP ' . $voucher->Voucher_No . ($note !== '' ? ' - ' . $note : ''),
            ],
            [
                'Trial_Balance_Header_No'   => $payCoa->Trial_Balance_Header_No ?? '',
                'Trial_Balance_Header_Name' => $payCoa->Trial_Balance_Header_Name ?? '',
                'Acc_No'                    => $payCoa->Acc_No,
                'Acc_Name'                  => $payCoa->Acc_Name,
                'Line_Side'                 => 'CREDIT',
                'Component_Tag'             => $channel,
                'Amount'                    => $amount,
                'Department'                => (string) ($voucher->Department ?: 'HCGA'),
                'Remark'                    => 'Pembayaran ' . $channel . ' ' . $voucher->Voucher_No . ($paymentRef !== '' ? ' (' . $paymentRef . ')' : ''),
            ],
        ];

        $journalNo = $this->generateJournalNo('JAPP-');
        $this->postJournalToHo($voucher, $journalLines, $journalNo, $paymentDate, $username, $voucher->Legacy_AP_Task_ID ?: $voucher->Voucher_No);

        $legacyCashNo = null;
        $legacyBankNo = null;
        if ($channel === 'CASH') {
            $legacyCashNo = $this->syncToCashOut($voucher, $journalLines, $paymentDate, $payCoa->Acc_No, $amount, $username);
        } else {
            $legacyBankNo = $this->syncToBankOut($voucher, $journalLines, $paymentDate, $bankAccRef, $payCoa->Acc_No, $amount, $username);
        }

        if (!empty($voucher->Legacy_AP_ID)) {
            $apRow = DB::table('account_payable')->where('ID', (int) $voucher->Legacy_AP_ID)->lockForUpdate()->first();
            if ($apRow) {
                $newPaid = round((float) $apRow->Paid + $amount, 2);
                $newRemain = max(0.0, round((float) $apRow->Total - $newPaid, 2));
                $newApStatus = $newRemain <= 0.01 ? 'CLOSED' : 'PARTIAL';

                DB::table('account_payable')->where('ID', (int) $apRow->ID)->update([
                    'Paid'              => $newPaid,
                    'Remain_Val'        => $newRemain,
                    'Status'            => $newApStatus,
                    'Payment_Type'      => $channel,
                    'Payment_Reference' => mb_substr($paymentRef !== '' ? $paymentRef : ($legacyBankNo ?: ($legacyCashNo ?: $journalNo)), 0, 200),
                    'Payment_Acc_No'    => mb_substr($payCoa->Acc_No, 0, 30),
                    'Payment_Note'      => trim((string) ($apRow->Payment_Note ? $apRow->Payment_Note . "\n" : '') . "[{$paymentDate}] {$username}: Rp " . number_format($amount, 0, ',', '.') . ($note !== '' ? " ({$note})" : '')),
                    'PIC_Paid'          => mb_substr($username, 0, 30),
                    'Last_Date_Payment' => $now,
                    'Last_Update'       => $now,
                ]);

                DB::table('account_payable_payment_log')->insert([
                    'Task_ID'           => mb_substr((string) $apRow->Task_ID, 0, 50),
                    'Payment_Type'      => $channel,
                    'Payment_Task_ID'   => mb_substr((string) ($legacyBankNo ?: ($legacyCashNo ?: $journalNo)), 0, 60),
                    'Payment_Reference' => mb_substr($paymentRef !== '' ? $paymentRef : $voucher->Voucher_No, 0, 70),
                    'Payment_Acc_No'    => mb_substr($payCoa->Acc_No, 0, 40),
                    'Payment_Value'     => $amount,
                    'Note'              => $note !== '' ? $note : ('Pelunasan AP ' . $voucher->Voucher_No),
                    'Reference'         => mb_substr((string) ($voucher->Reference ?: $voucher->Voucher_No), 0, 100),
                    'Created_By'        => mb_substr($username, 0, 30),
                    'Created_Date'      => $now,
                    'Vendor_ID'         => mb_substr((string) ($voucher->Vendor_ID ?: ''), 0, 50),
                    'Vendor_Name'       => mb_substr((string) ($voucher->Vendor_Name ?: ''), 0, 200),
                ]);
            }
        }

        return [
            'journal_no'      => $journalNo,
            'legacy_cash_no'  => $legacyCashNo,
            'legacy_bank_no'  => $legacyBankNo,
            'payment_acc_name'=> $payCoa->Acc_Name ?? '',
        ];
    }

    public function generateJournalNo(string $prefix = 'JRN-'): string
    {
        return $prefix . date('ymdHis') . '-' . strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
    }

    protected function generateCashOutNo(string $location): string
    {
        return 'CO/' . $location . '/' . date('ymd') . '-' . date('His');
    }

    protected function generateBankOutNo(string $location): string
    {
        return 'BO/' . $location . '/' . date('ymd') . '-' . date('His');
    }

    protected function generateApTaskId(string $location, string $username): string
    {
        return 'APM/' . $location . '/' . substr(md5($username . microtime(true)), 0, 3) . '/' . date('ymdhis');
    }
}
