<?php

namespace App\Services\HCGA;

use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

/**
 * Service Auto Maintenance Scheduling (Hybrid Reminder + Panel Due PM):
 * - Evaluasi jatuh tempo PM berbasis Tanggal (H-30, H-14, H-7, H-0, Overdue)
 * - Evaluasi jatuh tempo PM berbasis KM (sisa <= 1.000 KM / Overdue) sinkron dengan Logbook & Fuel Log
 * - Pengiriman reminder In-App & Email secara idempoten (vehicle_pm_reminder_log)
 */
class VehiclePmReminderService
{
    public const TOPIC = 'vehicle-pm-reminder';

    /** @var VehicleMaintenanceService */
    protected $maintenanceService;

    /** @var NotificationService */
    protected $notificationService;

    public function __construct(
        VehicleMaintenanceService $maintenanceService,
        NotificationService $notificationService
    ) {
        $this->maintenanceService = $maintenanceService;
        $this->notificationService = $notificationService;
    }

    /**
     * Evaluasi status jatuh tempo PM (gabungan Tanggal & KM) untuk satu kendaraan.
     */
    public function evaluatePmStatus(object $vehicle, int $effectiveKm, ?Carbon $today = null): array
    {
        $today = $today ? $today->copy()->startOfDay() : Carbon::today();
        $dateEval = $this->evaluateDateDue($vehicle->Tanggal_Jadwal_PM ?? null, $today);
        $kmEval = $this->evaluateKmDue($vehicle->KM_Jadwal_PM ?? null, $effectiveKm);

        $priority = [
            'OVERDUE'   => 1,
            'DUE_TODAY' => 2,
            'CRITICAL'  => 3,
            'WARNING'   => 4,
            'NOTICE'    => 5,
            'OK'        => 6,
            'UNSET'     => 7,
        ];

        $pDate = $priority[$dateEval['status']] ?? 99;
        $pKm = $priority[$kmEval['status']] ?? 99;
        $overall = ($pKm < $pDate) ? $kmEval : $dateEval;

        return [
            'date'         => $dateEval,
            'km'           => $kmEval,
            'overall'      => $overall,
            'effective_km' => $effectiveKm,
            'needs_action' => in_array($overall['status'], ['OVERDUE', 'DUE_TODAY', 'CRITICAL', 'WARNING', 'NOTICE'], true),
        ];
    }

    /**
     * Daftar kendaraan dengan status jadwal PM untuk panel Tab "Jadwal PM Jatuh Tempo".
     */
    public function getDuePmList(string $filter = 'ALL'): array
    {
        $vehicles = $this->maintenanceService->getVehicleAssets();
        $assetNos = $vehicles->pluck('Asset_No')->all();
        $odoMap = $this->maintenanceService->getEffectiveOdometerMap($assetNos);

        $activeMap = [];
        if (!empty($assetNos)) {
            $activeRows = DB::table('vehicle_maintenance')
                ->whereIn('Asset_No', $assetNos)
                ->whereIn('Status', VehicleMaintenanceService::ACTIVE_STATUSES)
                ->orderBy('id', 'DESC')
                ->get();

            foreach ($activeRows as $row) {
                if (!isset($activeMap[$row->Asset_No])) {
                    $activeMap[$row->Asset_No] = $row;
                }
            }
        }

        $today = Carbon::today();
        $filterUpper = strtoupper(trim($filter));
        $items = [];

        foreach ($vehicles as $v) {
            $effectiveKm = $odoMap[$v->Asset_No] ?? (int) ($v->KM_Saat_Ini ?? 0);
            $eval = $this->evaluatePmStatus($v, $effectiveKm, $today);
            $overallStatus = $eval['overall']['status'];

            if ($filterUpper === 'DUE' && !$eval['needs_action']) {
                continue;
            }
            if (!in_array($filterUpper, ['', 'ALL', 'DUE'], true) && $overallStatus !== $filterUpper) {
                continue;
            }

            $items[] = [
                'vehicle'      => $v,
                'effective_km' => $effectiveKm,
                'date_eval'    => $eval['date'],
                'km_eval'      => $eval['km'],
                'overall'      => $eval['overall'],
                'needs_action' => $eval['needs_action'],
                'active_vm'    => $activeMap[$v->Asset_No] ?? null,
            ];
        }

        $priority = [
            'OVERDUE'   => 1,
            'DUE_TODAY' => 2,
            'CRITICAL'  => 3,
            'WARNING'   => 4,
            'NOTICE'    => 5,
            'OK'        => 6,
            'UNSET'     => 7,
        ];

        usort($items, function ($a, $b) use ($priority) {
            $pa = $priority[$a['overall']['status']] ?? 99;
            $pb = $priority[$b['overall']['status']] ?? 99;
            if ($pa === $pb) {
                return strcmp((string) $a['vehicle']->Asset_No, (string) $b['vehicle']->Asset_No);
            }
            return $pa <=> $pb;
        });

        return $items;
    }

    /**
     * Proses cron harian: sinkronkan KM terbaru dan kirim reminder In-App & Email secara idempoten.
     */
    public function processDailyReminders(bool $force = false): array
    {
        $items = $this->getDuePmList('ALL');
        $syncedKm = 0;
        $inAppSent = 0;
        $emailSent = 0;
        $now = Carbon::now();

        $hcgaUserIds = $this->resolveHcgaRecipientUserIds();
        $hcgaEmails = $this->resolveHcgaEmails();

        foreach ($items as $item) {
            $v = $item['vehicle'];
            $effectiveKm = (int) $item['effective_km'];

            // Sinkronkan KM_Saat_Ini ke asset_list bila logbook/fuel mencatat angka lebih tinggi
            if ($effectiveKm > (int) ($v->KM_Saat_Ini ?? 0)) {
                DB::table('asset_list')->where('Asset_No', $v->Asset_No)->update([
                    'KM_Saat_Ini' => $effectiveKm,
                ]);
                $syncedKm++;
            }

            // Lewati reminder jika kendaraan sudah memiliki pengajuan maintenance aktif
            if (!empty($item['active_vm'])) {
                continue;
            }

            $triggers = $this->collectDueTriggers($item['date_eval'], $item['km_eval']);
            if (empty($triggers)) {
                continue;
            }

            $pic = (string) ($v->User_Responsible ?? '');
            $plat = $v->Registration_No ?: $v->Asset_No;
            $vehicleName = $v->Product_Description ?: $v->Asset_No;

            foreach ($triggers as $trig) {
                if ($this->sendInAppForTrigger($v, $plat, $vehicleName, $pic, $trig, $hcgaUserIds, $now, $force)) {
                    $inAppSent++;
                }
                if ($this->sendEmailForTrigger($v, $plat, $vehicleName, $pic, $trig, $hcgaEmails, $now, $force)) {
                    $emailSent++;
                }
            }

            DB::table('asset_list')->where('Asset_No', $v->Asset_No)->update([
                'Last_Reminder_Sent' => $now,
            ]);
        }

        return [
            'evaluated'  => count($items),
            'synced_km'  => $syncedKm,
            'in_app'     => $inAppSent,
            'email'      => $emailSent,
        ];
    }

    protected function evaluateDateDue(?string $dateStr, Carbon $today): array
    {
        $clean = trim((string) $dateStr);
        if ($clean === '' || $clean === '0000-00-00' || $clean === '-') {
            return [
                'status'         => 'UNSET',
                'label'          => 'Belum Diatur',
                'badge_class'    => 'bg-secondary text-white',
                'days_remaining' => null,
                'due_date'       => null,
                'threshold_key'  => null,
            ];
        }

        try {
            $due = Carbon::parse($clean)->startOfDay();
        } catch (\Throwable $e) {
            return [
                'status'         => 'UNSET',
                'label'          => 'Format Invalid',
                'badge_class'    => 'bg-secondary text-white',
                'days_remaining' => null,
                'due_date'       => null,
                'threshold_key'  => null,
            ];
        }

        $days = $today->diffInDays($due, false);
        $dueStr = $due->toDateString();

        if ($days < 0) {
            return [
                'status'         => 'OVERDUE',
                'label'          => 'Terlambat ' . abs($days) . ' hr',
                'badge_class'    => 'bg-danger text-white',
                'days_remaining' => $days,
                'due_date'       => $dueStr,
                'threshold_key'  => 'OVERDUE',
            ];
        }
        if ($days === 0) {
            return [
                'status'         => 'DUE_TODAY',
                'label'          => 'Jatuh Tempo Hari Ini',
                'badge_class'    => 'bg-danger text-white',
                'days_remaining' => 0,
                'due_date'       => $dueStr,
                'threshold_key'  => 'D-0',
            ];
        }
        if ($days <= 7) {
            return [
                'status'         => 'CRITICAL',
                'label'          => "H-{$days} (Kritis)",
                'badge_class'    => 'bg-danger text-white',
                'days_remaining' => $days,
                'due_date'       => $dueStr,
                'threshold_key'  => 'D-7',
            ];
        }
        if ($days <= 14) {
            return [
                'status'         => 'WARNING',
                'label'          => "H-{$days} (Segera)",
                'badge_class'    => 'bg-warning text-dark',
                'days_remaining' => $days,
                'due_date'       => $dueStr,
                'threshold_key'  => 'D-14',
            ];
        }
        if ($days <= 30) {
            return [
                'status'         => 'NOTICE',
                'label'          => "H-{$days} (Perhatian)",
                'badge_class'    => 'bg-info text-white',
                'days_remaining' => $days,
                'due_date'       => $dueStr,
                'threshold_key'  => 'D-30',
            ];
        }

        return [
            'status'         => 'OK',
            'label'          => "Terjadwal ({$days} hr)",
            'badge_class'    => 'bg-success text-white',
            'days_remaining' => $days,
            'due_date'       => $dueStr,
            'threshold_key'  => null,
        ];
    }

    protected function evaluateKmDue($targetKmRaw, int $effectiveKm): array
    {
        $targetKm = (int) ($targetKmRaw ?? 0);
        if ($targetKm <= 0) {
            return [
                'status'        => 'UNSET',
                'label'         => 'Belum Diatur',
                'badge_class'   => 'bg-secondary text-white',
                'km_remaining'  => null,
                'target_km'     => null,
                'threshold_key' => null,
            ];
        }

        $remaining = $targetKm - $effectiveKm;
        if ($remaining <= 0) {
            return [
                'status'        => 'OVERDUE',
                'label'         => 'Lewat ' . number_format(abs($remaining), 0, ',', '.') . ' KM',
                'badge_class'   => 'bg-danger text-white',
                'km_remaining'  => $remaining,
                'target_km'     => $targetKm,
                'threshold_key' => 'KM-OVERDUE',
            ];
        }
        if ($remaining <= 500) {
            return [
                'status'        => 'CRITICAL',
                'label'         => 'Sisa ' . number_format($remaining, 0, ',', '.') . ' KM',
                'badge_class'   => 'bg-danger text-white',
                'km_remaining'  => $remaining,
                'target_km'     => $targetKm,
                'threshold_key' => 'KM-1000',
            ];
        }
        if ($remaining <= 1000) {
            return [
                'status'        => 'WARNING',
                'label'         => 'Sisa ' . number_format($remaining, 0, ',', '.') . ' KM',
                'badge_class'   => 'bg-warning text-dark',
                'km_remaining'  => $remaining,
                'target_km'     => $targetKm,
                'threshold_key' => 'KM-1000',
            ];
        }

        return [
            'status'        => 'OK',
            'label'         => 'Sisa ' . number_format($remaining, 0, ',', '.') . ' KM',
            'badge_class'   => 'bg-success text-white',
            'km_remaining'  => $remaining,
            'target_km'     => $targetKm,
            'threshold_key' => null,
        ];
    }

    protected function collectDueTriggers(array $dateEval, array $kmEval): array
    {
        $triggers = [];

        if (!empty($dateEval['threshold_key']) && !empty($dateEval['due_date'])) {
            $triggers[] = [
                'trigger_type'  => 'DATE',
                'threshold_key' => $dateEval['threshold_key'],
                'due_reference' => (string) $dateEval['due_date'],
                'label'         => $dateEval['label'],
            ];
        }

        if (!empty($kmEval['threshold_key']) && !empty($kmEval['target_km'])) {
            $triggers[] = [
                'trigger_type'  => 'KM',
                'threshold_key' => $kmEval['threshold_key'],
                'due_reference' => (string) $kmEval['target_km'],
                'label'         => $kmEval['label'],
            ];
        }

        return $triggers;
    }

    protected function sendInAppForTrigger(
        object $vehicle,
        string $plat,
        string $vehicleName,
        string $pic,
        array $trig,
        array $hcgaUserIds,
        Carbon $now,
        bool $force
    ): bool {
        if (!Schema::hasTable('vehicle_pm_reminder_log') || !Schema::hasTable('notification')) {
            return false;
        }

        if (!$force && $this->alreadyLogged($vehicle->Asset_No, $trig['trigger_type'], $trig['threshold_key'], $trig['due_reference'], 'IN_APP')) {
            return false;
        }

        $title = sprintf('Reminder PM Kendaraan: %s (%s)', $plat, $trig['label']);
        $content = sprintf(
            'Kendaraan %s (%s) mendekati/melewati jadwal Preventive Maintenance (%s: %s). Segera buat pengajuan perawatan.',
            $vehicleName,
            $vehicle->Asset_No,
            $trig['trigger_type'] === 'KM' ? 'Target KM' : 'Tanggal Jadwal',
            $trig['due_reference']
        );
        $metadata = json_encode([
            'asset_no'      => $vehicle->Asset_No,
            'trigger_type'  => $trig['trigger_type'],
            'threshold_key' => $trig['threshold_key'],
            'due_reference' => $trig['due_reference'],
            'url'           => url('hrd-vehicle-maintenance-index'),
        ]);

        $recipients = $this->resolveRecipientUserIds($pic, $hcgaUserIds);
        if (empty($recipients)) {
            $this->notificationService->makeNotification($title, $content, self::TOPIC, null, null, 'SENT', $metadata);
        } else {
            foreach ($recipients as $uid) {
                $this->notificationService->makeNotification($title, $content, self::TOPIC, null, (string) $uid, 'SENT', $metadata);
            }
        }

        DB::table('vehicle_pm_reminder_log')->insert([
            'Asset_No'      => $vehicle->Asset_No,
            'Trigger_Type'  => $trig['trigger_type'],
            'Threshold_Key' => $trig['threshold_key'],
            'Due_Reference' => $trig['due_reference'],
            'Channel'       => 'IN_APP',
            'Recipient'     => empty($recipients) ? 'ALL' : substr(implode(',', $recipients), 0, 150),
            'Sent_At'       => $now,
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        return true;
    }

    protected function sendEmailForTrigger(
        object $vehicle,
        string $plat,
        string $vehicleName,
        string $pic,
        array $trig,
        array $hcgaEmails,
        Carbon $now,
        bool $force
    ): bool {
        if (!Schema::hasTable('vehicle_pm_reminder_log')) {
            return false;
        }

        if (!$force && $this->alreadyLogged($vehicle->Asset_No, $trig['trigger_type'], $trig['threshold_key'], $trig['due_reference'], 'EMAIL')) {
            return false;
        }

        $emails = $this->resolveRecipientEmails($pic, $hcgaEmails);
        if (empty($emails)) {
            return false;
        }

        $subject = sprintf('[HCGA Vehicle PM Reminder] %s - %s (%s)', $plat, $vehicleName, $trig['label']);
        $body = implode("\n", [
            'Yth. Tim HCGA / PIC Kendaraan,',
            '',
            'Berikut pengingat jadwal Preventive Maintenance (PM) kendaraan:',
            '- Asset No       : ' . $vehicle->Asset_No,
            '- Plat Nomor     : ' . $plat,
            '- Kendaraan      : ' . $vehicleName,
            '- Pemicu         : ' . ($trig['trigger_type'] === 'KM' ? 'Odometer (KM)' : 'Tanggal Jadwal'),
            '- Target Jadwal  : ' . $trig['due_reference'],
            '- Status         : ' . $trig['label'],
            '- PIC            : ' . ($pic !== '' ? $pic : '-'),
            '',
            'Silakan buka menu Vehicle Maintenance -> Tab Jadwal PM Jatuh Tempo untuk membuat pengajuan perawatan.',
        ]);

        try {
            Mail::raw($body, function ($message) use ($emails, $subject) {
                $message->to($emails)->subject($subject);
            });

            DB::table('vehicle_pm_reminder_log')->insert([
                'Asset_No'      => $vehicle->Asset_No,
                'Trigger_Type'  => $trig['trigger_type'],
                'Threshold_Key' => $trig['threshold_key'],
                'Due_Reference' => $trig['due_reference'],
                'Channel'       => 'EMAIL',
                'Recipient'     => substr(implode(',', $emails), 0, 150),
                'Sent_At'       => $now,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::warning('Vehicle PM email reminder failed for ' . $vehicle->Asset_No . ': ' . $e->getMessage());
            return false;
        }
    }

    protected function alreadyLogged(string $assetNo, string $triggerType, string $thresholdKey, string $dueRef, string $channel): bool
    {
        return DB::table('vehicle_pm_reminder_log')
            ->where('Asset_No', $assetNo)
            ->where('Trigger_Type', $triggerType)
            ->where('Threshold_Key', $thresholdKey)
            ->where('Due_Reference', $dueRef)
            ->where('Channel', $channel)
            ->exists();
    }

    protected function resolveHcgaRecipientUserIds(): array
    {
        return DB::table('users')
            ->whereIn('Department', ['HCGA', 'GA'])
            ->where(function ($q) {
                $q->where('Status', 'ACTIVE')->orWhereNull('Status');
            })
            ->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->all();
    }

    protected function resolveRecipientUserIds(?string $pic, array $hcgaRecipients): array
    {
        $ids = $hcgaRecipients;
        $cleanPic = trim((string) $pic);
        if ($cleanPic !== '' && $cleanPic !== '-') {
            $picUser = DB::table('users')
                ->where('Name', $cleanPic)
                ->orWhere('username', $cleanPic)
                ->value('id');
            if ($picUser) {
                $ids[] = (int) $picUser;
            }
        }
        return array_values(array_unique(array_filter($ids)));
    }

    protected function resolveHcgaEmails(): array
    {
        return DB::table('users')
            ->whereIn('Department', ['HCGA', 'GA'])
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->pluck('email')
            ->filter(function ($email) {
                return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
            })
            ->values()
            ->all();
    }

    protected function resolveRecipientEmails(?string $pic, array $defaultEmails): array
    {
        $emails = $defaultEmails;
        $cleanPic = trim((string) $pic);
        if ($cleanPic !== '' && $cleanPic !== '-') {
            $picEmail = DB::table('users')
                ->where(function ($q) use ($cleanPic) {
                    $q->where('Name', $cleanPic)->orWhere('username', $cleanPic);
                })
                ->whereNotNull('email')
                ->value('email');
            if ($picEmail && filter_var($picEmail, FILTER_VALIDATE_EMAIL)) {
                $emails[] = $picEmail;
            }
        }
        return array_values(array_unique($emails));
    }
}
