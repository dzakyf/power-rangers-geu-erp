<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class PermitLicenseMailService
{
    const SETTINGS_TABLE = 'permit_license_mail_settings';
    const RECIPIENTS_TABLE = 'permit_license_mail_recipients';
    const LOG_TABLE = 'permit_license_mail_log';
    const MAX_ATTEMPTS = 3;

    protected $sender;
    protected $emailSettings;

    public function __construct(EmailSettingsService $emailSettings)
    {
        $this->emailSettings = $emailSettings;
    }

    public function setSender(callable $sender)
    {
        $this->sender = $sender;
    }

    public function tablesReady()
    {
        return Schema::hasTable(self::SETTINGS_TABLE)
            && Schema::hasTable(self::RECIPIENTS_TABLE)
            && Schema::hasTable(self::LOG_TABLE)
            && $this->emailSettings->tableReady();
    }

    public function defaultSettings()
    {
        return (object) [
            'ID' => 0,
            'Subject_Template' => '[Pengingat] Masa Berlaku {permit_name}',
            'Intro_Text' => 'Berikut kami informasikan perizinan yang mendekati masa berlaku. Mohon segera ditindaklanjuti sebelum tanggal berakhir.',
            'Footer_Text' => 'Email ini dikirim otomatis oleh ERP GEU. Mohon tidak membalas email ini.',
            'Created_By' => null,
            'Updated_By' => null,
        ];
    }

    public function settings()
    {
        if (!Schema::hasTable(self::SETTINGS_TABLE)) {
            return $this->defaultSettings();
        }

        $row = DB::table(self::SETTINGS_TABLE)->orderBy('ID')->first();
        if (!$row) {
            return $this->defaultSettings();
        }

        $defaults = $this->defaultSettings();
        foreach ($defaults as $key => $value) {
            if (!property_exists($row, $key) || $row->$key === null) {
                $row->$key = $value;
            }
        }

        return $row;
    }

    public function isEnabled()
    {
        return $this->emailSettings->isEnabled() && count($this->activeRecipients()) > 0;
    }

    public function activeRecipients()
    {
        if (!Schema::hasTable(self::RECIPIENTS_TABLE)) {
            return [];
        }

        return DB::table(self::RECIPIENTS_TABLE)
            ->where('Is_Active', 1)
            ->orderBy('Name')
            ->orderBy('Email')
            ->get()
            ->filter(function ($row) {
                return filter_var(trim((string) $row->Email), FILTER_VALIDATE_EMAIL) !== false;
            })
            ->values()
            ->all();
    }

    public function allRecipients()
    {
        if (!Schema::hasTable(self::RECIPIENTS_TABLE)) {
            return collect();
        }

        return DB::table(self::RECIPIENTS_TABLE)
            ->orderBy('Is_Active', 'DESC')
            ->orderBy('Name')
            ->orderBy('Email')
            ->get();
    }

    public function updateSettings(array $data)
    {
        if (!Schema::hasTable(self::SETTINGS_TABLE)) {
            return false;
        }

        $existing = DB::table(self::SETTINGS_TABLE)->orderBy('ID')->first();
        $defaults = $this->defaultSettings();
        $payload = [
            'Subject_Template' => $this->nullable($data['Subject_Template'] ?? null) ?: $defaults->Subject_Template,
            'Intro_Text' => $this->nullable($data['Intro_Text'] ?? null),
            'Footer_Text' => $this->nullable($data['Footer_Text'] ?? null),
            'Updated_By' => $data['Updated_By'] ?? null,
            'updated_at' => now(),
        ];

        if ($existing) {
            DB::table(self::SETTINGS_TABLE)->where('ID', $existing->ID)->update($payload);
            return (int) $existing->ID;
        }

        $payload['Created_By'] = $data['Updated_By'] ?? null;
        $payload['created_at'] = now();

        return DB::table(self::SETTINGS_TABLE)->insertGetId($payload);
    }

    public function addRecipient($name, $email, $username = null)
    {
        if (!Schema::hasTable(self::RECIPIENTS_TABLE)) {
            return false;
        }

        $email = strtolower(trim((string) $email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $existing = DB::table(self::RECIPIENTS_TABLE)->where('Email', $email)->first();
        if ($existing) {
            DB::table(self::RECIPIENTS_TABLE)->where('ID', $existing->ID)->update([
                'Name' => $this->nullable($name),
                'Is_Active' => 1,
                'Updated_By' => $username,
                'updated_at' => now(),
            ]);

            return (int) $existing->ID;
        }

        try {
            return DB::table(self::RECIPIENTS_TABLE)->insertGetId([
                'Name' => $this->nullable($name),
                'Email' => $email,
                'Is_Active' => 1,
                'Created_By' => $username,
                'Updated_By' => $username,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $claimed = DB::table(self::RECIPIENTS_TABLE)->where('Email', $email)->first();
            if (!$claimed) {
                return false;
            }

            DB::table(self::RECIPIENTS_TABLE)->where('ID', $claimed->ID)->update([
                'Name' => $this->nullable($name),
                'Is_Active' => 1,
                'Updated_By' => $username,
                'updated_at' => now(),
            ]);

            return (int) $claimed->ID;
        }
    }

    public function toggleRecipient($id, $username = null)
    {
        if (!Schema::hasTable(self::RECIPIENTS_TABLE)) {
            return false;
        }

        $row = DB::table(self::RECIPIENTS_TABLE)->where('ID', (int) $id)->first();
        if (!$row) {
            return false;
        }

        DB::table(self::RECIPIENTS_TABLE)->where('ID', $row->ID)->update([
            'Is_Active' => (int) $row->Is_Active === 1 ? 0 : 1,
            'Updated_By' => $username,
            'updated_at' => now(),
        ]);

        return true;
    }

    public function deleteRecipient($id)
    {
        if (!Schema::hasTable(self::RECIPIENTS_TABLE)) {
            return false;
        }

        return DB::table(self::RECIPIENTS_TABLE)->where('ID', (int) $id)->delete() > 0;
    }

    public function recentLogs($limit = 10)
    {
        if (!Schema::hasTable(self::LOG_TABLE)) {
            return collect();
        }

        return DB::table(self::LOG_TABLE . ' as l')
            ->leftJoin('permit_licenses as p', 'p.ID', '=', 'l.Permit_ID')
            ->select('l.*', 'p.Name as Permit_Name')
            ->orderBy('l.ID', 'DESC')
            ->limit((int) $limit)
            ->get();
    }

    /**
     * Send one email per active recipient for a permit expiry date.
     * The mail log unique key makes repeated command runs idempotent.
     */
    public function queueExpiryReminder($permit, $expiryDate, $days = null)
    {
        if (!$permit || !Schema::hasTable(self::LOG_TABLE)) {
            return 0;
        }

        $recipients = $this->activeRecipients();
        if (!$this->isEnabled() || count($recipients) === 0) {
            return 0;
        }

        $sent = 0;
        foreach ($recipients as $recipient) {
            if ($this->sendReminderTo($permit, $expiryDate, $days, $recipient->Email)) {
                $sent++;
            }
        }

        return $sent;
    }

    public function retryFailedEmails($limit = 50)
    {
        if (!$this->isEnabled() || !Schema::hasTable(self::LOG_TABLE) || !Schema::hasTable('permit_licenses')) {
            return 0;
        }

        $pairs = DB::table(self::LOG_TABLE)
            ->whereIn('Status', ['QUEUED', 'FAILED'])
            ->where('Attempts', '<', self::MAX_ATTEMPTS)
            ->select('Permit_ID', 'Expiry_Date')
            ->distinct()
            ->orderBy('Permit_ID')
            ->limit((int) $limit)
            ->get();

        $sent = 0;
        foreach ($pairs as $pair) {
            $permit = DB::table('permit_licenses as p')
                ->leftJoin('permit_license_categories as c', 'c.ID', '=', 'p.Category_ID')
                ->select('p.*', 'c.Code as Category_Code', 'c.Name as Category_Name')
                ->where('p.ID', $pair->Permit_ID)
                ->first();

            if (!$permit) {
                continue;
            }

            $sent += $this->queueExpiryReminder($permit, $pair->Expiry_Date, $this->daysRemaining($permit));
        }

        return $sent;
    }

    public function previewHtml($permit = null)
    {
        $permit = $permit ?: $this->samplePermit();
        $settings = $this->settings();
        $days = $this->daysRemaining($permit);
        if ($days === null) {
            $days = 14;
        }
        $subject = $this->renderSubject($permit, $days, $settings);

        return $this->renderTemplate($permit, $days, $subject);
    }

    public function samplePermit()
    {
        return (object) [
            'ID' => 0,
            'Name' => 'Contoh Izin Operasional',
            'Issuer' => 'Dinas Penanaman Modal dan Pelayanan Terpadu',
            'Permit_Number' => '503/123/DPMPTSP/2026',
            'Issue_Date' => Carbon::today()->subYear()->toDateString(),
            'Validity_Type' => 'PERIOD',
            'Validity_Text' => null,
            'Valid_From' => Carbon::today()->subYear()->toDateString(),
            'Valid_To' => Carbon::today()->addDays(14)->toDateString(),
            'Reminder_Days' => 30,
            'PIC_Name' => 'Budi Santoso',
            'PIC_Contact' => '0812-3456-7890',
            'Category_Code' => '001',
            'Category_Name' => 'Permit & license',
        ];
    }

    protected function sendReminderTo($permit, $expiryDate, $days, $email)
    {
        $email = strtolower(trim((string) $email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $existing = DB::table(self::LOG_TABLE)
            ->where('Permit_ID', $permit->ID)
            ->where('Expiry_Date', $expiryDate)
            ->where('Email_To', $email)
            ->first();

        if ($existing && $existing->Status === 'SENT') {
            return false;
        }

        if ($existing && (int) $existing->Attempts >= self::MAX_ATTEMPTS) {
            return false;
        }

        if ($existing && $existing->Status === 'QUEUED'
            && !empty($existing->Updated_At)
            && Carbon::parse($existing->Updated_At)->gt(now()->subMinutes(10))) {
            return false;
        }

        $settings = $this->settings();
        $subject = $this->renderSubject($permit, $days, $settings);
        $html = $this->renderTemplate($permit, $days, $subject);

        if ($existing) {
            DB::table(self::LOG_TABLE)->where('ID', $existing->ID)->update([
                'Status' => 'QUEUED',
                'Attempts' => (int) $existing->Attempts + 1,
                'Error' => null,
                'Updated_At' => now(),
            ]);
            $logId = $existing->ID;
        } else {
            try {
                $logId = DB::table(self::LOG_TABLE)->insertGetId([
                    'Permit_ID' => $permit->ID,
                    'Expiry_Date' => $expiryDate,
                    'Email_To' => $email,
                    'Subject' => $subject,
                    'Status' => 'QUEUED',
                    'Attempts' => 1,
                    'Created_At' => now(),
                    'Updated_At' => now(),
                ]);
            } catch (\Throwable $e) {
                $claimed = DB::table(self::LOG_TABLE)
                    ->where('Permit_ID', $permit->ID)
                    ->where('Expiry_Date', $expiryDate)
                    ->where('Email_To', $email)
                    ->first();

                if (!$claimed || $claimed->Status === 'SENT' || (int) $claimed->Attempts >= self::MAX_ATTEMPTS) {
                    return false;
                }

                if ($claimed->Status === 'QUEUED'
                    && !empty($claimed->Updated_At)
                    && Carbon::parse($claimed->Updated_At)->gt(now()->subMinutes(10))) {
                    return false;
                }

                DB::table(self::LOG_TABLE)->where('ID', $claimed->ID)->update([
                    'Status' => 'QUEUED',
                    'Attempts' => (int) $claimed->Attempts + 1,
                    'Error' => null,
                    'Updated_At' => now(),
                ]);
                $logId = $claimed->ID;
            }
        }

        list($ok, $error) = $this->sendMail($email, $subject, $html);

        DB::table(self::LOG_TABLE)->where('ID', $logId)->update([
            'Status' => $ok ? 'SENT' : 'FAILED',
            'Error' => $ok ? null : $error,
            'Sent_At' => $ok ? now() : null,
            'Updated_At' => now(),
        ]);

        return $ok;
    }

    protected function renderSubject($permit, $days, $settings)
    {
        $subject = $settings->Subject_Template ?: $this->defaultSettings()->Subject_Template;

        return $this->replacePlaceholders($subject, $permit, $days);
    }

    protected function renderTemplate($permit, $days, $subject)
    {
        $settings = $this->settings();
        $defaults = $this->defaultSettings();
        $intro = $settings->Intro_Text ?: $defaults->Intro_Text;
        $footer = $settings->Footer_Text ?: $defaults->Footer_Text;

        return View::make('emails.permit-license-expiry-reminder', [
            'permit' => $permit,
            'days' => $days,
            'subject' => $subject,
            'intro' => $this->replacePlaceholders($intro, $permit, $days),
            'footer' => $this->replacePlaceholders($footer, $permit, $days),
            'actionUrl' => url('hrd-permit-license-index'),
            'statusLabel' => $this->statusLabel($permit, $days),
            'validityLabel' => $this->validityLabel($permit),
            'issueDateLabel' => $this->formatDate($permit->Issue_Date ?? null),
            'picLabel' => $this->picLabel($permit),
        ])->render();
    }

    protected function sendMail($to, $subject, $html)
    {
        if ($this->sender) {
            return call_user_func($this->sender, $to, $subject, $html);
        }

        return $this->emailSettings->send($to, $subject, $html);
    }

    protected function replacePlaceholders($text, $permit, $days)
    {
        $map = [
            '{permit_name}' => (string) ($permit->Name ?? '-'),
            '{permit_number}' => (string) ($permit->Permit_Number ?? '-'),
            '{issuer}' => (string) ($permit->Issuer ?? '-'),
            '{valid_to}' => $this->formatDate($permit->Valid_To ?? null),
            '{days_remaining}' => $days === null ? '-' : (string) $days,
            '{category}' => (string) ($permit->Category_Name ?? $permit->Category_Code ?? '-'),
        ];

        return str_replace(array_keys($map), array_values($map), (string) $text);
    }

    protected function daysRemaining($permit)
    {
        if (!$permit || empty($permit->Valid_To) || (isset($permit->Validity_Type) && $permit->Validity_Type === 'TEXT')) {
            return null;
        }

        try {
            $validTo = Carbon::parse($permit->Valid_To)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }

        return (int) Carbon::today()->diffInDays($validTo, false);
    }

    protected function statusLabel($permit, $days)
    {
        if ($days === null) {
            return 'SESUAI KETENTUAN';
        }
        if ($days < 0) {
            return 'KEDALUWARSA (' . abs($days) . ' hari)';
        }
        if ($days === 0) {
            return 'BERAKHIR HARI INI';
        }

        return 'AKAN BERAKHIR (' . $days . ' hari)';
    }

    protected function validityLabel($permit)
    {
        $type = $permit->Validity_Type ?? 'TEXT';
        if ($type === 'TEXT') {
            return !empty($permit->Validity_Text) ? $permit->Validity_Text : '-';
        }

        $validTo = !empty($permit->Valid_To) ? $this->formatDate($permit->Valid_To) : '-';
        if ($type === 'PERIOD') {
            $validFrom = !empty($permit->Valid_From) ? $this->formatDate($permit->Valid_From) : '-';
            return $validFrom . ' s/d ' . $validTo;
        }

        return 'Hingga ' . $validTo;
    }

    protected function picLabel($permit)
    {
        $pic = trim((string) ($permit->PIC_Name ?? ''));
        if (!empty($permit->PIC_Contact)) {
            $pic = trim($pic . ($pic !== '' ? ' / ' : '') . $permit->PIC_Contact);
        }

        return $pic !== '' ? $pic : '-';
    }

    protected function formatDate($value)
    {
        if (empty($value) || $value === '0000-00-00') {
            return '-';
        }

        try {
            return Carbon::parse($value)->format('d/m/Y');
        } catch (\Throwable $e) {
            return '-';
        }
    }

    protected function nullable($value)
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
