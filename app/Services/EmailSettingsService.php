<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPMailer\PHPMailer\PHPMailer;

class EmailSettingsService
{
    const SETTINGS_TABLE = 'email_settings';

    protected $sender;

    public function setSender(callable $sender)
    {
        $this->sender = $sender;
    }

    public function tableReady()
    {
        return Schema::hasTable(self::SETTINGS_TABLE);
    }

    public function defaultSettings()
    {
        return (object) [
            'ID' => 0,
            'Is_Enabled' => 0,
            'Smtp_Host' => null,
            'Smtp_Port' => 587,
            'Smtp_Encryption' => 'tls',
            'Smtp_Username' => null,
            'Smtp_Password' => null,
            'Mail_From_Address' => null,
            'Mail_From_Name' => 'ERP-ALERTER',
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
        $settings = $this->settings();

        return (int) $settings->Is_Enabled === 1;
    }

    public function updateSettings(array $data, $password = null, $clearPassword = false)
    {
        if (!Schema::hasTable(self::SETTINGS_TABLE)) {
            return false;
        }

        $existing = DB::table(self::SETTINGS_TABLE)->orderBy('ID')->first();
        $payload = [
            'Is_Enabled' => !empty($data['Is_Enabled']) ? 1 : 0,
            'Smtp_Host' => $this->nullable($data['Smtp_Host'] ?? null),
            'Smtp_Port' => !empty($data['Smtp_Port']) ? (int) $data['Smtp_Port'] : null,
            'Smtp_Encryption' => $this->nullable($data['Smtp_Encryption'] ?? null),
            'Smtp_Username' => $this->nullable($data['Smtp_Username'] ?? null),
            'Mail_From_Address' => $this->nullable($data['Mail_From_Address'] ?? null),
            'Mail_From_Name' => $this->nullable($data['Mail_From_Name'] ?? null),
            'Updated_By' => $data['Updated_By'] ?? null,
            'updated_at' => now(),
        ];

        if ($password !== null && $password !== '') {
            $payload['Smtp_Password'] = Crypt::encryptString($password);
        } elseif ($clearPassword) {
            $payload['Smtp_Password'] = null;
        }

        if ($existing) {
            DB::table(self::SETTINGS_TABLE)->where('ID', $existing->ID)->update($payload);
            return (int) $existing->ID;
        }

        $payload['Created_By'] = $data['Updated_By'] ?? null;
        $payload['created_at'] = now();

        return DB::table(self::SETTINGS_TABLE)->insertGetId($payload);
    }

    public function sendTestEmail($email)
    {
        $subject = '[TEST] Konfigurasi SMTP ERP GEU';
        $html = '<p>Email ini dikirim untuk menguji konfigurasi SMTP pada ERP GEU.</p>'
            . '<p>Waktu pengiriman: ' . Carbon::now()->format('d/m/Y H:i:s') . '</p>'
            . '<p>Jika email ini diterima, konfigurasi SMTP sudah berjalan dengan baik.</p>';

        return $this->send($email, $subject, $html);
    }

    public function send($to, $subject, $html, array $attachments = [])
    {
        if ($this->sender) {
            return call_user_func($this->sender, $to, $subject, $html, $attachments);
        }

        $settings = $this->settings();
        $host = trim((string) ($settings->Smtp_Host ?: config('mail.host')));
        $port = (int) ($settings->Smtp_Port ?: config('mail.port', 587));
        $username = $settings->Smtp_Username ?: config('mail.username');
        $password = $this->decryptPassword($settings->Smtp_Password);
        if ($password === null || $password === '') {
            $password = config('mail.password');
        }
        $encryption = $settings->Smtp_Encryption ?: config('mail.encryption');
        $fromAddress = trim((string) ($settings->Mail_From_Address ?: config('mail.from.address')));
        $fromName = trim((string) ($settings->Mail_From_Name ?: config('mail.from.name')));

        if ($host === '' || $fromAddress === '') {
            return [false, 'SMTP host dan alamat pengirim belum diisi.'];
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $host;
            $mail->Port = $port;
            $mail->Timeout = 15;
            $mail->SMTPAuth = !empty($username);
            if (!empty($username)) {
                $mail->Username = $username;
                $mail->Password = $password;
            }
            if ($encryption === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } elseif ($encryption === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPAutoTLS = false;
            }
            $mail->CharSet = 'UTF-8';
            $mail->setFrom($fromAddress, $fromName !== '' ? $fromName : 'ERP-ALERTER');
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $html;
            $mail->AltBody = trim(strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $html)));

            if (!empty($attachments)) {
                foreach ($attachments as $att) {
                    if (is_array($att)) {
                        if (!empty($att['string']) && !empty($att['filename'])) {
                            $mail->addStringAttachment($att['string'], $att['filename'], 'base64', $att['type'] ?? 'application/pdf');
                        } elseif (!empty($att['path']) && is_file($att['path'])) {
                            $mail->addAttachment($att['path'], $att['filename'] ?? '');
                        }
                    } elseif (is_string($att) && is_file($att)) {
                        $mail->addAttachment($att);
                    }
                }
            }

            $mail->send();

            return [true, null];
        } catch (\Throwable $e) {
            $error = $e->getMessage();
            if (!empty($mail->ErrorInfo)) {
                $error .= ' | ' . $mail->ErrorInfo;
            }

            return [false, $error];
        }
    }

    protected function decryptPassword($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable $e) {
            return $value;
        }
    }

    protected function nullable($value)
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
