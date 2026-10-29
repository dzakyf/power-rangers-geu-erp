<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PermitLicenseReminderService
{
    const TOPIC = 'permit-license';
    const DEFAULT_REMINDER_DAYS = 30;

    protected $notificationService;
    protected $mailService;

    public function __construct(NotificationService $notificationService, PermitLicenseMailService $mailService)
    {
        $this->notificationService = $notificationService;
        $this->mailService = $mailService;
    }

    /**
     * Permits/licenses whose end date falls inside their reminder window.
     * Expired records are intentionally excluded; the grid shows them as
     * KEDALUWARSA, while this query powers the "akan berakhir" banner.
     */
    public function expiringPermits($limit = null)
    {
        if (!Schema::hasTable('permit_licenses')) {
            return collect();
        }

        $query = DB::table('permit_licenses as p')
            ->leftJoin('permit_license_categories as c', 'c.ID', '=', 'p.Category_ID')
            ->select('p.*', 'c.Code as Category_Code', 'c.Name as Category_Name')
            ->whereIn('p.Validity_Type', ['END_DATE', 'PERIOD'])
            ->whereNotNull('p.Valid_To')
            ->whereDate('p.Valid_To', '>=', Carbon::today()->toDateString())
            ->whereRaw('DATEDIFF(p.Valid_To, CURDATE()) <= COALESCE(p.Reminder_Days, ?)', [self::DEFAULT_REMINDER_DAYS])
            ->orderBy('p.Valid_To', 'ASC')
            ->orderBy('p.ID', 'ASC');

        if ($limit !== null && (int) $limit > 0) {
            $query->limit((int) $limit);
        }

        return $query->get();
    }

    public function expiredCount()
    {
        if (!Schema::hasTable('permit_licenses')) {
            return 0;
        }

        return (int) DB::table('permit_licenses')
            ->whereIn('Validity_Type', ['END_DATE', 'PERIOD'])
            ->whereNotNull('Valid_To')
            ->whereDate('Valid_To', '<', Carbon::today()->toDateString())
            ->count();
    }

    public function daysRemaining($permit)
    {
        if (!$permit || empty($permit->Valid_To)) {
            return null;
        }

        if (isset($permit->Validity_Type) && $permit->Validity_Type === 'TEXT') {
            return null;
        }

        try {
            $validTo = Carbon::parse($permit->Valid_To)->startOfDay();
        } catch (\Exception $e) {
            return null;
        }

        $today = Carbon::today();
        if ($validTo->lt($today)) {
            return -(int) $validTo->diffInDays($today);
        }

        return (int) $today->diffInDays($validTo);
    }

    public function statusFor($permit)
    {
        $days = $this->daysRemaining($permit);
        if ($days === null) {
            return ['label' => 'SESUAI KETENTUAN', 'days' => null, 'tone' => 'secondary'];
        }

        $reminderDays = isset($permit->Reminder_Days) && $permit->Reminder_Days !== null
            ? (int) $permit->Reminder_Days
            : self::DEFAULT_REMINDER_DAYS;

        if ($days < 0) {
            return ['label' => 'KEDALUWARSA (' . abs($days) . ' hari)', 'days' => $days, 'tone' => 'danger'];
        }

        if ($days <= $reminderDays) {
            return ['label' => 'AKAN BERAKHIR (' . $days . ' hari)', 'days' => $days, 'tone' => 'warning'];
        }

        return ['label' => 'AKTIF', 'days' => $days, 'tone' => 'success'];
    }

    public function validityLabel($permit)
    {
        if (!$permit) {
            return '-';
        }

        $type = isset($permit->Validity_Type) ? $permit->Validity_Type : 'TEXT';

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

    public function formatDate($value)
    {
        if (empty($value) || $value === '0000-00-00') {
            return '-';
        }

        try {
            return Carbon::parse($value)->format('d/m/Y');
        } catch (\Exception $e) {
            return '-';
        }
    }

    /**
     * Create one in-app notification per permit expiry date and recipient.
     * The reminder log unique key makes repeated page loads/cron runs safe.
     *
     * @return int number of permits that received a new notification
     */
    public function syncExpiryNotifications($sendEmail = false)
    {
        if (!Schema::hasTable('permit_licenses')
            || !Schema::hasTable('permit_license_reminder_log')
            || !Schema::hasTable('notification')) {
            return 0;
        }

        $notified = 0;
        $recipients = $this->notificationRecipients();

        foreach ($this->expiringPermits() as $permit) {
            try {
                $expiryDate = Carbon::parse($permit->Valid_To)->toDateString();
            } catch (\Exception $e) {
                continue;
            }

            $days = $this->daysRemaining($permit);

            $created = DB::transaction(function () use ($permit, $expiryDate, $recipients, $days) {
                $inserted = DB::table('permit_license_reminder_log')->insertOrIgnore([
                    'Permit_ID' => $permit->ID,
                    'Expiry_Date' => $expiryDate,
                    'Reminder_Days' => (int) ($permit->Reminder_Days ?: self::DEFAULT_REMINDER_DAYS),
                    'Reminder_Date' => Carbon::today()->toDateString(),
                    'Created_At' => now(),
                ]);

                if ((int) $inserted === 0) {
                    return false;
                }

                $title = Str::limit('Izin Akan Berakhir: ' . $permit->Name, 95);
                $content = 'Perizinan ' . $permit->Name
                    . (!empty($permit->Permit_Number) ? ' (' . $permit->Permit_Number . ')' : '')
                    . ' akan berakhir pada ' . $this->formatDate($permit->Valid_To)
                    . ($days !== null ? ' (' . $days . ' hari lagi)' : '')
                    . '. Segera lakukan perpanjangan.';

                $metadata = json_encode([
                    'permit_id' => (int) $permit->ID,
                    'permit_name' => $permit->Name,
                    'permit_number' => $permit->Permit_Number,
                    'valid_to' => $expiryDate,
                    'days_remaining' => $days,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                foreach ($recipients as $recipientId) {
                    $this->notificationService->makeNotification(
                        $title,
                        $content,
                        self::TOPIC,
                        null,
                        $recipientId,
                        'SENT',
                        $metadata
                    );
                }

                DB::table('permit_licenses')->where('ID', $permit->ID)->update([
                    'Last_Reminder_At' => now(),
                    'Last_Reminder_Expiry' => $expiryDate,
                    'updated_at' => now(),
                ]);

                return true;
            });

            if ($created) {
                $notified++;
            }

            if ($sendEmail) {
                try {
                    $this->mailService->queueExpiryReminder($permit, $expiryDate, $days);
                } catch (\Throwable $e) {
                    // Email failure must not block in-app notifications.
                }
            }
        }

        return $notified;
    }

    /**
     * Notify HCGA staff plus ADM/HRD role holders. Falls back to a global
     * notification when no matching user is found.
     */
    public function notificationRecipients()
    {
        if (!Schema::hasTable('users')) {
            return [null];
        }

        $userIds = DB::table('users')
            ->where(function ($query) {
                $query->where('Department', 'HCGA')
                    ->orWhereIn('id', function ($sub) {
                        $sub->select('model_has_roles.model_id')
                            ->from('model_has_roles')
                            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                            ->whereIn('roles.name', ['ADM', 'HRD']);
                    });
            })
            ->pluck('id')
            ->unique()
            ->values()
            ->all();

        return count($userIds) > 0 ? $userIds : [null];
    }
}
