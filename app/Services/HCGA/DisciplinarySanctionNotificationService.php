<?php

namespace App\Services\HCGA;

use App\Services\EmailSettingsService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PHPMailer\PHPMailer\PHPMailer;

class DisciplinarySanctionNotificationService
{
    protected $emailSettings;

    public function __construct(EmailSettingsService $emailSettings)
    {
        $this->emailSettings = $emailSettings;
    }
    /**
     * Kirim notifikasi ke HCGA bahwa ada pengajuan laporan pelanggaran baru
     */
    public function notifyHcgaNewReport(int $sanctionId)
    {
        $sanction = DB::table('disciplinary_sanctions')->where('id', $sanctionId)->first();
        if (!$sanction) {
            return;
        }

        // Cari personil HCGA
        $hcgaUsers = DB::table('users')
            ->where('Department', 'HCGA')
            ->where('Status', 'ACTIVE')
            ->get();

        $message = sprintf(
            "Laporan Pelanggaran Karyawan: %s (%s) telah diajukan oleh %s untuk No. SP %s. Silakan lakukan review & klarifikasi.",
            $sanction->Nama,
            $sanction->Departemen,
            $sanction->Reporter_Nama,
            $sanction->No_Trx
        );

        foreach ($hcgaUsers as $hcga) {
            $this->createSystemNotification($hcga->id, 'Laporan Pelanggaran Baru (SP)', $message, route('hrd-disciplinary-sanction-detail', $sanctionId));
            if (!empty($hcga->email)) {
                $this->sendEmail($hcga->email, $hcga->Name ?? 'HCGA Team', 'Laporan Pelanggaran Disiplin Karyawan - ' . $sanction->No_Trx, $message);
            }
        }
    }

    /**
     * Kirim notifikasi ke Karyawan & Atasan mengenai penerbitan SP dan jadwal klarifikasi/Zoom jika ada
     */
    public function notifyEmployeeAndReporter(int $sanctionId)
    {
        $sanction = DB::table('disciplinary_sanctions')->where('id', $sanctionId)->first();
        if (!$sanction) {
            return;
        }

        $employee = DB::table('users')->where('id', $sanction->User_ID)->first();
        $reporter = DB::table('users')->where('id', $sanction->Reporter_User_ID)->first();

        $meetingInfo = "";
        if (!empty($sanction->Meeting_Type) && $sanction->Meeting_Type !== 'NONE') {
            $meetingInfo = sprintf(
                "\nJadwal Sidang / Klarifikasi (%s):\nWaktu: %s\nLokasi / Zoom: %s\n",
                $sanction->Meeting_Type,
                $sanction->Meeting_Date ? date('d M Y H:i', strtotime($sanction->Meeting_Date)) : '-',
                $sanction->Meeting_Location ?? '-'
            );
        }

        $message = sprintf(
            "Pemberitahuan Surat Sanksi / Peringatan (%s):\nNomor: %s\nTingkat Sanksi: %s (Berlaku hingga: %s)%s\nHarap segera login ke portal HCGA untuk membaca rincian dan menandatangani komitmen perbaikan.",
            $sanction->Nama,
            $sanction->No_Trx,
            $sanction->Sanction_Level,
            date('d M Y', strtotime($sanction->Valid_Until)),
            $meetingInfo
        );

        // Notifikasi ke Karyawan
        if ($employee) {
            $this->createSystemNotification($employee->id, 'Surat Peringatan & Komitmen Disiplin', $message, route('hrd-disciplinary-sanction-detail', $sanctionId));
            if (!empty($employee->email)) {
                $this->sendEmail($employee->email, $employee->Name ?? 'Karyawan', 'Surat Peringatan Resmi - ' . $sanction->No_Trx, nl2br($message));
            }
        }

        // Notifikasi ke Atasan Pelapor
        if ($reporter && $reporter->id !== $employee->id) {
            $this->createSystemNotification($reporter->id, 'Update Penerbitan Surat Peringatan', $message, route('hrd-disciplinary-sanction-detail', $sanctionId));
        }
    }

    /**
     * Buat notifikasi in-app ke tabel notification
     */
    protected function createSystemNotification(int $userId, string $title, string $message, string $url)
    {
        try {
            DB::table('notification')->insert([
                'User_ID'    => $userId,
                'Title'      => $title,
                'Message'    => $message,
                'Url'        => $url,
                'Is_Read'    => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        } catch (Exception $e) {
            // Ignored if table structure differs
        }
    }

    /**
     * Kirim email menggunakan PHPMailer & EmailSettingsService
     */
    protected function sendEmail(string $recipientEmail, string $recipientName, string $subject, string $body)
    {
        try {
            $settings = $this->emailSettings->settings();
            if (empty($settings->Smtp_Host) || empty($settings->Mail_From_Address)) {
                return false;
            }

            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = $settings->Smtp_Host;
            $mail->SMTPAuth   = !empty($settings->Smtp_Username);
            $mail->Username   = $settings->Smtp_Username;
            $mail->Password   = $settings->Smtp_Password ? Crypt::decryptString($settings->Smtp_Password) : '';
            $mail->SMTPSecure = $settings->Smtp_Encryption === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (int) $settings->Smtp_Port;
            $mail->setFrom($settings->Mail_From_Address, $settings->Mail_From_Name ?: 'HCGA GEU');

            $mail->addAddress($recipientEmail, $recipientName);
            $mail->isHTML(true);
            $mail->Subject = "[HCGA ERP] " . $subject;
            $mail->Body    = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            Log::info("Gagal mengirim email sanksi: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate Link WhatsApp siap kirim
     */
    public static function generateWhatsappUrl(string $phone, string $text): string
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (substr($cleanPhone, 0, 1) === '0') {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        }
        return 'https://api.whatsapp.com/send?phone=' . $cleanPhone . '&text=' . urlencode($text);
    }
}
