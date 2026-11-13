<?php

namespace App\Services\HCGA;

use App\Services\EmailSettingsService;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PHPMailer\PHPMailer\PHPMailer;

class ExitClearanceNotificationService
{
    protected $emailSettings;

    public function __construct(EmailSettingsService $emailSettings)
    {
        $this->emailSettings = $emailSettings;
    }

    /**
     * Kirim Notifikasi Dual-Channel: In-App System Notification & Email Alert
     *
     * @param string $eventType RESIGN_SUBMITTED, RESIGN_APPROVED, RESIGN_REJECTED, CLEARANCE_SUBMITTED, INTERVIEW_COMPLETED, FINAL_COMPLETED
     * @param object|array $resignation
     * @param object|array|null $clearance
     * @param int|null $targetUserId
     * @param string|null $targetEmail
     * @param string|null $customNotes
     */
    public function notify(string $eventType, $resignation, $clearance = null, ?int $targetUserId = null, ?string $targetEmail = null, ?string $customNotes = null)
    {
        $resignation = (object) $resignation;
        $clearance = $clearance ? (object) $clearance : null;

        $title = '';
        $message = '';
        $url = url('/hrd-exit-clearance-index');

        switch ($eventType) {
            case 'RESIGN_SUBMITTED':
                $title = "Pengajuan Resign Baru: {$resignation->Nama}";
                $message = "Karyawan {$resignation->Nama} ({$resignation->Jabatan}) mengajukan permohonan resign per tanggal efektif {$resignation->Last_Working_Date}. Menunggu persetujuan atasan langsung.";
                break;

            case 'RESIGN_APPROVED':
                $title = "Pengajuan Resign Disetujui: {$resignation->Nama}";
                $message = "Pengajuan resign untuk {$resignation->Nama} telah disetujui oleh atasan. Formulir Exit Clearance telah dibuat dan siap untuk diisi.";
                if ($clearance) {
                    $url = route('hrd-exit-clearance-form', $clearance->id);
                }
                break;

            case 'RESIGN_REJECTED':
                $title = "Pengajuan Resign Ditolak: {$resignation->Nama}";
                $message = "Pengajuan resign untuk {$resignation->Nama} telah ditolak oleh atasan. Alasan: " . ($customNotes ?: 'Tidak ada alasan khusus.');
                break;

            case 'CLEARANCE_SUBMITTED':
                $title = "Formulir Exit Clearance Siap Diinterview HCGA: {$resignation->Nama}";
                $message = "Karyawan {$resignation->Nama} telah mengisi kuesioner dan checklist inventaris. Silakan tim HCGA melaksanakan sesi wawancara Exit Interview tatap muka.";
                if ($clearance) {
                    $url = route('hrd-exit-clearance-view', $clearance->id);
                }
                break;

            case 'INTERVIEW_COMPLETED':
                $title = "Exit Interview HCGA Selesai, Menunggu Approval Atasan: {$resignation->Nama}";
                $message = "Tim HCGA telah mencatat hasil wawancara Exit Interview untuk {$resignation->Nama}. Berkas diajukan ke Atasan Departemen terkait untuk persetujuan.";
                if ($clearance) {
                    $url = route('hrd-exit-clearance-view', $clearance->id);
                }
                break;

            case 'MANAGER_APPROVED':
                $title = "Exit Clearance Disetujui Atasan, Menunggu Verifikasi HCGA: {$resignation->Nama}";
                $message = "Atasan Departemen telah menyetujui Exit Clearance untuk {$resignation->Nama}. Berkas diteruskan ke tim HCGA untuk verifikasi final.";
                if ($clearance) {
                    $url = route('hrd-exit-clearance-view', $clearance->id);
                }
                break;

            case 'FINAL_COMPLETED':
                $title = "Exit Clearance Selesai (Completed): {$resignation->Nama}";
                $message = "Seluruh proses Exit Clearance untuk {$resignation->Nama} telah diverifikasi dan disetujui oleh HCGA. Dokumen resmi FR/HCGA/07-00 telah diarsipkan.";
                if ($clearance) {
                    $url = route('hrd-exit-clearance-view', $clearance->id);
                }
                break;
        }

        // 1. Catat ke Tabel In-App System Notification (`notification`)
        try {
            DB::table('notification')->insert([
                'Title' => $title,
                'Content' => $message,
                'Expires_At' => Carbon::now()->addDays(7),
                'Topic' => 'EXIT_CLEARANCE',
                'Sender_Id' => auth()->id() ?? 1,
                'Recipient_Id' => $targetUserId, // null = broadcast
                'Metadata' => json_encode([
                    'event_type' => $eventType,
                    'resignation_no' => $resignation->No_Trx ?? null,
                    'clearance_no' => $clearance->No_Trx ?? null,
                    'url' => $url,
                ]),
                'Status' => 'SENT',
                'Created_At' => Carbon::now(),
            ]);
        } catch (Exception $e) {
            Log::warning("Gagal membuat notifikasi in-app Exit Clearance: " . $e->getMessage());
        }

        // 2. Kirim Email Alert via SMTP (PHPMailer / EmailSettingsService)
        if ($targetEmail) {
            $this->sendEmailAlert($targetEmail, $title, $message, $url, $resignation, $clearance);
        }
    }

    /**
     * Kirim Email Alert dengan format HTML Resmi
     */
    protected function sendEmailAlert(string $toEmail, string $subject, string $bodyContent, string $actionUrl, $resignation, $clearance = null)
    {
        try {
            $settings = $this->emailSettings->settings();
            if (empty($settings->Smtp_Host) || empty($settings->Mail_From_Address)) {
                Log::info("SMTP Email belum dikonfigurasi. Melewati pengiriman email ke: {$toEmail}");
                return false;
            }

            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $settings->Smtp_Host;
            $mail->SMTPAuth = !empty($settings->Smtp_Username);
            $mail->Username = $settings->Smtp_Username;
            $mail->Password = $settings->Smtp_Password ? \Illuminate\Support\Facades\Crypt::decryptString($settings->Smtp_Password) : '';
            $mail->SMTPSecure = $settings->Smtp_Encryption === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = (int) $settings->Smtp_Port;

            $mail->setFrom($settings->Mail_From_Address, $settings->Mail_From_Name ?: 'ERP HCGA Notification');
            $mail->addAddress($toEmail);
            $mail->isHTML(true);
            $mail->Subject = "[HCGA ERP] " . $subject;

            $htmlTemplate = "
            <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;'>
                <div style='background-color: #0d6efd; color: #fff; padding: 16px 20px;'>
                    <h3 style='margin: 0;'>Pemberitahuan Exit Clearance & Pengunduran Diri</h3>
                </div>
                <div style='padding: 20px;'>
                    <p style='font-size: 15px;'>Halo,</p>
                    <p style='font-size: 14px;'>{$bodyContent}</p>
                    
                    <div style='background-color: #f8f9fa; border-left: 4px solid #0d6efd; padding: 12px; margin: 16px 0; font-size: 13px;'>
                        <strong>Nama Karyawan:</strong> {$resignation->Nama}<br>
                        <strong>Jabatan / Dept:</strong> " . ($resignation->Jabatan ?? '-') . " / " . ($resignation->Departemen ?? '-') . "<br>
                        <strong>Tanggal Efektif Terakhir:</strong> " . date('d F Y', strtotime($resignation->Last_Working_Date)) . "<br>
                        " . ($clearance ? "<strong>No. Exit Clearance:</strong> {$clearance->No_Trx}<br>" : "") . "
                    </div>

                    <div style='margin-top: 25px; text-align: center;'>
                        <a href='{$actionUrl}' style='background-color: #0d6efd; color: #ffffff; text-decoration: none; padding: 10px 24px; border-radius: 4px; font-weight: bold; display: inline-block;'>Buka Dokumen di Sistem</a>
                    </div>
                </div>
                <div style='background-color: #f1f1f1; color: #777; padding: 12px 20px; font-size: 11px; text-align: center;'>
                    Email ini dikirim otomatis oleh ERP HCGA System PT Green Energi Utama. Jangan membalas email ini.
                </div>
            </div>";

            $mail->Body = $htmlTemplate;
            $mail->send();
            return true;
        } catch (Exception $e) {
            Log::warning("Gagal mengirim email alert Exit Clearance ke {$toEmail}: " . $e->getMessage());
            return false;
        }
    }
}
