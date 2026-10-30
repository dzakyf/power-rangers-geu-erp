<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\EmailSettingsService;
use App\Services\PermitLicenseMailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class EmailSettingsController extends Controller
{
    const MENU_ID = 'SI01008';

    protected $emailSettings;
    protected $permitMail;

    public function __construct(EmailSettingsService $emailSettings, PermitLicenseMailService $permitMail)
    {
        $this->emailSettings = $emailSettings;
        $this->permitMail = $permitMail;
    }

    public function index()
    {
        $this->authorizeEmail('view');

        $settings = $this->emailSettings->settings();
        $recipients = $this->permitMail->allRecipients();
        $logs = $this->permitMail->recentLogs(10);
        $userEmails = $this->mailUserEmails();

        return view('credential.email-settings.index', compact(
            'settings',
            'recipients',
            'logs',
            'userEmails'
        ));
    }

    public function save(Request $request)
    {
        $this->authorizeEmail('edit');

        try {
            $validated = $request->validate([
                'Smtp_Host' => 'nullable|string|max:255',
                'Smtp_Port' => 'nullable|integer|min:1|max:65535',
                'Smtp_Encryption' => 'nullable|in:tls,ssl,none',
                'Smtp_Username' => 'nullable|string|max:255',
                'Smtp_Password' => 'nullable|string|max:255',
                'Mail_From_Address' => 'nullable|email|max:255',
                'Mail_From_Name' => 'nullable|string|max:255',
            ]);
        } catch (ValidationException $e) {
            return redirect('credential-email-settings-index')
                ->withErrors($e->validator)
                ->withInput($request->except('Smtp_Password'));
        }

        $enabled = $request->has('Is_Enabled');
        if ($enabled) {
            $errors = [];
            if (empty($validated['Smtp_Host'])) {
                $errors['Smtp_Host'] = 'SMTP host wajib diisi saat email diaktifkan.';
            }
            if (empty($validated['Smtp_Port'])) {
                $errors['Smtp_Port'] = 'SMTP port wajib diisi saat email diaktifkan.';
            }
            if (empty($validated['Mail_From_Address'])) {
                $errors['Mail_From_Address'] = 'Alamat pengirim wajib diisi saat email diaktifkan.';
            }
            if (count($this->permitMail->activeRecipients()) === 0) {
                $errors['recipients'] = 'Tambahkan minimal satu penerima email aktif sebelum mengaktifkan pengiriman.';
            }

            if (count($errors) > 0) {
                return redirect('credential-email-settings-index')
                    ->withErrors($errors)
                    ->withInput($request->except('Smtp_Password'));
            }
        }

        $validated['Is_Enabled'] = $enabled ? 1 : 0;
        $validated['Updated_By'] = Auth::user()->username;

        $saved = $this->emailSettings->updateSettings(
            $validated,
            $request->input('Smtp_Password'),
            $request->has('Clear_Smtp_Password')
        );

        if (!$saved) {
            return redirect('credential-email-settings-index')
                ->with('error', 'Tabel pengaturan email belum tersedia. Jalankan migrasi terlebih dahulu.');
        }

        return redirect('credential-email-settings-index')
            ->with('success', 'Konfigurasi email berhasil disimpan.');
    }

    public function recipientSave(Request $request)
    {
        $this->authorizeEmail('edit');

        $validated = $request->validate([
            'Name' => 'nullable|string|max:150',
            'Email' => 'required|email|max:150',
        ]);

        $saved = $this->permitMail->addRecipient(
            $validated['Name'] ?? null,
            $validated['Email'],
            Auth::user()->username
        );

        if (!$saved) {
            return redirect('credential-email-settings-index')
                ->with('error', 'Email penerima tidak valid atau tabel email belum tersedia.');
        }

        return redirect('credential-email-settings-index')
            ->with('success', 'Penerima email berhasil disimpan.');
    }

    public function recipientToggle(Request $request)
    {
        $this->authorizeEmail('edit');

        $toggled = $this->permitMail->toggleRecipient(
            (int) $request->input('ID', 0),
            Auth::user()->username
        );

        if (!$toggled) {
            return redirect('credential-email-settings-index')
                ->with('error', 'Penerima email tidak ditemukan.');
        }

        return redirect('credential-email-settings-index')
            ->with('success', 'Status penerima email diperbarui.');
    }

    public function recipientDelete(Request $request)
    {
        $this->authorizeEmail('edit');

        $deleted = $this->permitMail->deleteRecipient((int) $request->input('ID', 0));

        if (!$deleted) {
            return redirect('credential-email-settings-index')
                ->with('error', 'Penerima email tidak ditemukan.');
        }

        return redirect('credential-email-settings-index')
            ->with('success', 'Penerima email berhasil dihapus.');
    }

    public function test(Request $request)
    {
        $this->authorizeEmail('edit');

        $validated = $request->validate([
            'Test_Email' => 'required|email|max:150',
        ]);

        list($ok, $error) = $this->emailSettings->sendTestEmail($validated['Test_Email']);

        if ($ok) {
            return redirect('credential-email-settings-index')
                ->with('success', 'Test email berhasil dikirim ke ' . $validated['Test_Email'] . '.');
        }

        return redirect('credential-email-settings-index')
            ->with('error', 'Gagal mengirim test email: ' . $error);
    }

    protected function mailUserEmails()
    {
        if (!Schema::hasTable('users')) {
            return collect();
        }

        return DB::table('users')
            ->whereNotNull('email')
            ->where('email', '<>', '')
            ->where('Status', 'ACTIVE')
            ->where(function ($query) {
                $query->where('Department', 'HCGA')
                    ->orWhereIn('id', function ($sub) {
                        $sub->select('model_has_roles.model_id')
                            ->from('model_has_roles')
                            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                            ->whereIn('roles.name', ['ADM', 'HRD']);
                    });
            })
            ->select('Name', 'email')
            ->orderBy('Name')
            ->get()
            ->filter(function ($user) {
                return filter_var(trim((string) $user->email), FILTER_VALIDATE_EMAIL) !== false;
            })
            ->unique('email')
            ->values();
    }

    protected function authorizeEmail($action = 'view')
    {
        $user = Auth::user();

        if (!$user || (!can_menu(self::MENU_ID, $action) && !$user->hasRole('ADM'))) {
            abort(403, 'Anda tidak berhak mengakses menu Email Settings.');
        }
    }
}
