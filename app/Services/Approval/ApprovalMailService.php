<?php

namespace App\Services\Approval;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service terpusat untuk memformat dan mengirim notifikasi email approval profesional & responsif.
 */
class ApprovalMailService
{
    /**
     * Dapatkan label dan URL detail berdasarkan Request_Type dan data request.
     */
    public static function resolveModuleInfo($request, $task = null): array
    {
        $type = strtoupper(trim((string) ($request->Request_Type ?? 'GENERAL')));
        $mapTo = $request->Map_To ?? null;

        $labels = [
            'REIMBURSEMENT'          => 'Klaim & Reimbursement',
            'SPPD'                   => 'Perjalanan Dinas (SPPD)',
            'REQUISITION'            => 'Permintaan Tenaga Kerja (PTK / Requisition)',
            'PROCUREMENT'            => 'Pengadaan Barang & Jasa (Procurement / PR)',
            'GA_WORK_ORDER'          => 'General Affairs Work Order (GA WO)',
            'ATTENDANCE_LEAVE'       => 'Pengajuan Cuti & Izin Karyawan',
            'ATTENDANCE_OVERTIME'    => 'Pengajuan Lembur / Overtime',
            'ATTENDANCE_CORRECTION'  => 'Koreksi Absensi Karyawan',
            'ATTENDANCE_CHANGE_SHIFT'=> 'Perubahan / Pertukaran Shift Kerja',
            'STATUS_CHANGE'          => 'Perubahan Status Kepegawaian',
            'EMPLOYEE_DATA_UPDATE'   => 'Perubahan Data Mandiri Karyawan',
            'GENERAL'                => 'Pengajuan Dokumen Persetujuan',
        ];

        $moduleLabel = $labels[$type] ?? ucwords(strtolower(str_replace('_', ' ', $type)));

        // URL langsung untuk approver
        $actionUrl = url('hrd-approval-inbox'); // fallback inbox terpadu
        if ($type === 'REIMBURSEMENT' && !empty($mapTo)) {
            $actionUrl = url('hrd-reimbursement-approval-detail') . '?ID=' . $mapTo;
        } elseif ($type === 'SPPD' && !empty($mapTo)) {
            $actionUrl = url('hrd-sppd-approval-detail') . '?ID=' . $mapTo;
        } elseif ($type === 'PROCUREMENT' && !empty($mapTo)) {
            $actionUrl = url('hrd-procurement-approval-detail') . '?id=' . $mapTo;
        } elseif ($type === 'EMPLOYEE_DATA_UPDATE') {
            $actionUrl = url('employee/directory');
        }

        return [
            'type'        => $type,
            'moduleLabel' => $moduleLabel,
            'actionUrl'   => $actionUrl,
        ];
    }

    /**
     * Kirim email ke kandidat approver ketika task baru dibuat / berpindah stage.
     */
    public function sendTaskNotification($task, $request, array $candidates): void
    {
        if (empty($candidates)) {
            return;
        }

        $userIds = array_values(array_unique(array_filter(array_map(function ($c) {
            return (int) ($c['User_Id'] ?? 0);
        }, $candidates))));

        if (empty($userIds)) {
            return;
        }

        $users = DB::table('users')
            ->whereIn('id', $userIds)
            ->where('Status', 'ACTIVE')
            ->get();

        $info = self::resolveModuleInfo($request, $task);
        $requester = DB::table('users')->where('id', (int) $request->Requester_Id)->first();
        $requesterName = $requester ? ($requester->Name ?: $requester->username) : 'Karyawan';
        $requesterNik  = $requester ? $requester->Emp_ID : '-';

        $reqNo = $request->Request_No ?: ('APR-' . str_pad((string)$request->ID, 6, '0', STR_PAD_LEFT));
        $stageOrder = $task->Stage_Order ?? 1;
        $positionName = $task->Position_Name ?: ($task->Position_Code ?: ('Level ' . ($task->Level_Number ?? '-')));

        foreach ($users as $user) {
            $email = trim($user->email ?? '');
            if (empty($email)) {
                continue;
            }

            $subject = "PERMINTAAN APPROVAL: [{$info['moduleLabel']}] {$reqNo} - Stage {$stageOrder}";
            $body = $this->renderModernTemplate([
                'title'          => "Permintaan Persetujuan Dokumen",
                'subtitle'       => "Terdapat permohonan baru yang membutuhkan verifikasi dan keputusan Anda.",
                'badgeText'      => "Menunggu Persetujuan (Stage {$stageOrder})",
                'badgeColor'     => "#2563eb",
                'recipientName'  => $user->Name ?: $user->username,
                'requestNo'      => $reqNo,
                'moduleLabel'    => $info['moduleLabel'],
                'requesterName'  => "{$requesterName} ({$requesterNik})",
                'department'     => $request->Department ?: '-',
                'location'       => $request->Location ?: ($request->Company_ID ?: '-'),
                'amount'         => !empty($request->Amount) ? ('Rp ' . number_format((float)$request->Amount, 0, ',', '.')) : null,
                'approvalStage'  => "Stage {$stageOrder} - {$positionName}",
                'description'    => $request->Description ?: '-',
                'actionUrl'      => $info['actionUrl'],
                'actionBtnText'  => "Buka & Proses Approval",
                'actionBtnColor' => "#2563eb",
            ]);

            $this->queueEmail($email, $subject, $body);
        }
    }

    /**
     * Kirim email hasil keputusan approval ke Requester (APPROVED, REJECTED, RETURNED).
     */
    public function sendStatusDecisionNotification($request, string $decision, string $reason = ''): void
    {
        $requester = DB::table('users')->where('id', (int) $request->Requester_Id)->first();
        if (!$requester || empty($requester->email)) {
            return;
        }

        $info = self::resolveModuleInfo($request);
        $reqNo = $request->Request_No ?: ('APR-' . str_pad((string)$request->ID, 6, '0', STR_PAD_LEFT));

        $decisionUpper = strtoupper(trim($decision));
        if ($decisionUpper === 'APPROVED') {
            $badgeText = "Disetujui (Approved)";
            $badgeColor = "#16a34a"; // green
            $title = "Pengajuan Anda Telah Disetujui";
            $subtitle = "Dokumen pengajuan Anda telah selesai disetujui oleh seluruh pihak terkait.";
            $btnColor = "#16a34a";
        } elseif ($decisionUpper === 'REJECTED') {
            $badgeText = "Ditolak (Rejected)";
            $badgeColor = "#dc2626"; // red
            $title = "Pengajuan Anda Ditolak";
            $subtitle = "Permohonan pengajuan Anda belum dapat disetujui.";
            $btnColor = "#dc2626";
        } elseif ($decisionUpper === 'RETURNED') {
            $badgeText = "Perlu Perbaikan (Returned)";
            $badgeColor = "#ea580c"; // orange
            $title = "Pengajuan Dikembalikan untuk Revisi";
            $subtitle = "Approver mengembalikan pengajuan Anda untuk dilakukan perbaikan berkas/data.";
            $btnColor = "#ea580c";
        } else {
            $badgeText = $decisionUpper;
            $badgeColor = "#64748b";
            $title = "Update Status Pengajuan";
            $subtitle = "Status pengajuan Anda telah diperbarui.";
            $btnColor = "#2563eb";
        }

        $subject = "STATUS APPROVAL [{$decisionUpper}]: {$info['moduleLabel']} {$reqNo}";
        $body = $this->renderModernTemplate([
            'title'          => $title,
            'subtitle'       => $subtitle,
            'badgeText'      => $badgeText,
            'badgeColor'     => $badgeColor,
            'recipientName'  => $requester->Name ?: $requester->username,
            'requestNo'      => $reqNo,
            'moduleLabel'    => $info['moduleLabel'],
            'requesterName'  => ($requester->Name ?: $requester->username) . " ({$requester->Emp_ID})",
            'department'     => $request->Department ?: '-',
            'location'       => $request->Location ?: ($request->Company_ID ?: '-'),
            'amount'         => !empty($request->Amount) ? ('Rp ' . number_format((float)$request->Amount, 0, ',', '.')) : null,
            'approvalStage'  => "Keputusan Final (" . $decisionUpper . ")",
            'description'    => $request->Description ?: '-',
            'notes'          => !empty($reason) ? $reason : null,
            'actionUrl'      => $info['actionUrl'],
            'actionBtnText'  => "Lihat Detail Pengajuan",
            'actionBtnColor' => $btnColor,
        ]);

        $this->queueEmail($requester->email, $subject, $body);
    }

    /**
     * Masukkan email ke antrean tabel mail_logs.
     */
    protected function queueEmail(string $to, string $subject, string $body): void
    {
        try {
            DB::table('mail_logs')->insert([
                'Email_To'      => $to,
                'Subject'       => $subject,
                'Contents'      => $body,
                'Status'        => 'UNSENT',
                'Inserted_Date' => now(),
                'Send_Date'     => now(),
            ]);
            Log::info("ApprovalMailService: Queued email '{$subject}' to {$to}");
        } catch (\Exception $e) {
            Log::error("ApprovalMailService queue error: " . $e->getMessage());
        }
    }

    /**
     * Template Email Responsif & Modern (Tampilan Clean Card dengan Palette Brand GEU).
     */
    protected function renderModernTemplate(array $d): string
    {
        $amountRow = '';
        if (!empty($d['amount'])) {
            $amountRow = "
            <tr>
                <td style=\"padding:10px 14px; border-bottom:1px solid #f1f5f9; color:#64748b; font-size:13px; font-weight:600; width:35%;\">Total Nominal</td>
                <td style=\"padding:10px 14px; border-bottom:1px solid #f1f5f9; color:#1e293b; font-size:14px; font-weight:700;\">{$d['amount']}</td>
            </tr>";
        }

        $notesRow = '';
        if (!empty($d['notes'])) {
            $notesRow = "
            <tr>
                <td style=\"padding:10px 14px; border-bottom:1px solid #f1f5f9; color:#64748b; font-size:13px; font-weight:600; width:35%;\">Catatan Keputusan</td>
                <td style=\"padding:10px 14px; border-bottom:1px solid #f1f5f9; color:#dc2626; font-size:13px; font-weight:600;\">" . nl2br(htmlspecialchars($d['notes'])) . "</td>
            </tr>";
        }

        return "
<!DOCTYPE html>
<html>
<head>
<meta charset=\"utf-8\">
<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
<title>{$d['title']}</title>
</head>
<body style=\"margin:0; padding:0; background-color:#f8fafc; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing:antialiased;\">

<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" style=\"background-color:#f8fafc; padding:30px 15px;\">
  <tr>
    <td align=\"center\">
      <table width=\"100%\" max-width=\"620\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" style=\"max-width:620px; background:#ffffff; border-radius:14px; overflow:hidden; box-shadow:0 4px 18px rgba(0,0,0,0.06); border:1px solid #e2e8f0;\">
        
        <!-- Header Banner -->
        <tr>
          <td style=\"background:linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%); padding:28px 32px; text-align:left;\">
            <table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
              <tr>
                <td>
                  <span style=\"display:inline-block; font-size:11px; font-weight:700; color:#bfdbfe; letter-spacing:1px; text-transform:uppercase; margin-bottom:4px;\">GREEN ENERGI UTAMA &bull; ERP SYSTEM</span>
                  <h1 style=\"margin:0 0 6px 0; color:#ffffff; font-size:20px; font-weight:700; letter-spacing:0.2px;\">{$d['title']}</h1>
                  <p style=\"margin:0; color:#e0e7ff; font-size:13px; line-height:1.4;\">{$d['subtitle']}</p>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Main Body -->
        <tr>
          <td style=\"padding:28px 32px;\">
            <p style=\"margin:0 0 16px 0; color:#334155; font-size:14px;\">Halo <b>" . htmlspecialchars($d['recipientName']) . "</b>,</p>

            <!-- Status Pill Box -->
            <div style=\"background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:12px 18px; margin-bottom:22px; display:flex; align-items:center; justify-content:space-between;\">
              <span style=\"color:#64748b; font-size:13px; font-weight:600;\">Status Pengajuan:</span>
              <span style=\"display:inline-block; background:{$d['badgeColor']}; color:#ffffff; font-size:12px; font-weight:700; padding:4px 12px; border-radius:20px;\">{$d['badgeText']}</span>
            </div>

            <!-- Detail Information Table -->
            <table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" style=\"border-collapse:collapse; background:#ffffff; border:1px solid #e2e8f0; border-radius:10px; overflow:hidden; margin-bottom:24px;\">
              <tr>
                <td style=\"padding:10px 14px; border-bottom:1px solid #f1f5f9; color:#64748b; font-size:13px; font-weight:600; width:35%;\">No. Dokumen</td>
                <td style=\"padding:10px 14px; border-bottom:1px solid #f1f5f9; color:#2563eb; font-size:13px; font-weight:700;\">" . htmlspecialchars($d['requestNo']) . "</td>
              </tr>
              <tr>
                <td style=\"padding:10px 14px; border-bottom:1px solid #f1f5f9; color:#64748b; font-size:13px; font-weight:600;\">Modul Pengajuan</td>
                <td style=\"padding:10px 14px; border-bottom:1px solid #f1f5f9; color:#1e293b; font-size:13px;\">" . htmlspecialchars($d['moduleLabel']) . "</td>
              </tr>
              <tr>
                <td style=\"padding:10px 14px; border-bottom:1px solid #f1f5f9; color:#64748b; font-size:13px; font-weight:600;\">Pemohon</td>
                <td style=\"padding:10px 14px; border-bottom:1px solid #f1f5f9; color:#1e293b; font-size:13px;\">" . htmlspecialchars($d['requesterName']) . "</td>
              </tr>
              <tr>
                <td style=\"padding:10px 14px; border-bottom:1px solid #f1f5f9; color:#64748b; font-size:13px; font-weight:600;\">Departemen / Lokasi</td>
                <td style=\"padding:10px 14px; border-bottom:1px solid #f1f5f9; color:#1e293b; font-size:13px;\">" . htmlspecialchars($d['department']) . " / " . htmlspecialchars($d['location']) . "</td>
              </tr>
              <tr>
                <td style=\"padding:10px 14px; border-bottom:1px solid #f1f5f9; color:#64748b; font-size:13px; font-weight:600;\">Tahap Approval</td>
                <td style=\"padding:10px 14px; border-bottom:1px solid #f1f5f9; color:#1e293b; font-size:13px; font-weight:600;\">" . htmlspecialchars($d['approvalStage']) . "</td>
              </tr>
              {$amountRow}
              <tr>
                <td style=\"padding:10px 14px; border-bottom:1px solid #f1f5f9; color:#64748b; font-size:13px; font-weight:600;\">Keperluan / Catatan</td>
                <td style=\"padding:10px 14px; border-bottom:1px solid #f1f5f9; color:#475569; font-size:13px; line-height:1.5;\">" . nl2br(htmlspecialchars($d['description'])) . "</td>
              </tr>
              {$notesRow}
            </table>

            <!-- Call to Action Button -->
            <table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" style=\"margin-top:10px; margin-bottom:15px;\">
              <tr>
                <td align=\"center\">
                  <a href=\"{$d['actionUrl']}\" style=\"display:inline-block; background:{$d['actionBtnColor']}; color:#ffffff; text-decoration:none; font-size:14px; font-weight:700; padding:12px 28px; border-radius:8px; box-shadow:0 3px 10px rgba(37,99,235,0.25); text-align:center;\">
                    {$d['actionBtnText']} &rarr;
                  </a>
                </td>
              </tr>
            </table>

            <p style=\"margin:18px 0 0 0; color:#94a3b8; font-size:12px; line-height:1.5; text-align:center;\">
              Bila tombol di atas tidak dapat diklik, salin tautan berikut ke peramban Anda:<br>
              <a href=\"{$d['actionUrl']}\" style=\"color:#2563eb; word-break:break-all;\">{$d['actionUrl']}</a>
            </p>
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style=\"background:#f8fafc; border-top:1px solid #e2e8f0; padding:20px 32px; text-align:center;\">
            <p style=\"margin:0 0 4px 0; color:#64748b; font-size:12px; font-weight:600;\">PT. Green Energi Utama &bull; Human Capital & General Affairs</p>
            <p style=\"margin:0; color:#94a3b8; font-size:11px;\">Email ini dikirim secara otomatis oleh ERP HCGA. Mohon tidak membalas email ini secara langsung.</p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>

</body>
</html>";
    }
}

