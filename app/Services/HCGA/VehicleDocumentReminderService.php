<?php

namespace App\Services\HCGA;

use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class VehicleDocumentReminderService
{
    const TOPIC = 'vehicle-document-reminder';

    const DOC_TYPES = [
        'STNK_TAHUNAN' => 'STNK Tahunan',
        'STNK_5_TAHUN' => 'STNK 5 Tahunan',
        'KEUR'         => 'KEUR / KIR',
    ];

    /** @var NotificationService */
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Query dasar kendaraan dari asset_list.
     */
    public function vehicleBaseQuery()
    {
        return DB::table('asset_list')
            ->where(function ($q) {
                $q->where('Asset_Sub_Category', 'VEHICLE')
                  ->orWhere(function ($sub) {
                      $sub->whereNotNull('Registration_No')
                          ->where('Registration_No', '!=', '');
                  });
            });
    }

    /**
     * Evaluasi status jatuh tempo dokumen kendaraan (STNK Tahunan, STNK 5 Tahunan, KEUR/KIR).
     */
    public function evaluateDocumentStatus(?string $dateStr, ?Carbon $today = null): array
    {
        $today = $today ? $today->copy()->startOfDay() : Carbon::today();
        $clean = trim((string) $dateStr);

        if ($clean === '' || $clean === '0000-00-00' || $clean === '-') {
            return [
                'status'         => 'UNSET',
                'label'          => 'Belum Diatur',
                'badge_class'    => 'bg-secondary text-white',
                'days_remaining' => null,
                'due_date'       => null,
                'threshold'      => null,
            ];
        }

        try {
            $dueDate = Carbon::parse($clean)->startOfDay();
        } catch (\Throwable $e) {
            return [
                'status'         => 'UNSET',
                'label'          => 'Format Invalid',
                'badge_class'    => 'bg-secondary text-white',
                'days_remaining' => null,
                'due_date'       => null,
                'threshold'      => null,
            ];
        }

        $days = $today->diffInDays($dueDate, false);
        $dueStr = $dueDate->toDateString();

        if ($days < 0) {
            return [
                'status'         => 'EXPIRED',
                'label'          => 'Expired (' . abs($days) . ' hr lalu)',
                'badge_class'    => 'bg-danger text-white',
                'days_remaining' => $days,
                'due_date'       => $dueStr,
                'threshold'      => -1,
            ];
        }

        if ($days === 0) {
            return [
                'status'         => 'EXPIRED',
                'label'          => 'Jatuh Tempo Hari Ini',
                'badge_class'    => 'bg-danger text-white',
                'days_remaining' => 0,
                'due_date'       => $dueStr,
                'threshold'      => 0,
            ];
        }

        if ($days <= 14) {
            return [
                'status'         => 'CRITICAL',
                'label'          => "H-{$days} (Kritis)",
                'badge_class'    => 'bg-danger text-white',
                'days_remaining' => $days,
                'due_date'       => $dueStr,
                'threshold'      => 14,
            ];
        }

        if ($days <= 30) {
            return [
                'status'         => 'WARNING',
                'label'          => "H-{$days} (Segera)",
                'badge_class'    => 'bg-warning text-dark',
                'days_remaining' => $days,
                'due_date'       => $dueStr,
                'threshold'      => 30,
            ];
        }

        if ($days <= 60) {
            return [
                'status'         => 'NOTICE',
                'label'          => "H-{$days} (Perhatian)",
                'badge_class'    => 'bg-info text-white',
                'days_remaining' => $days,
                'due_date'       => $dueStr,
                'threshold'      => 60,
            ];
        }

        return [
            'status'         => 'OK',
            'label'          => "Aktif ({$days} hr)",
            'badge_class'    => 'bg-success text-white',
            'days_remaining' => $days,
            'due_date'       => $dueStr,
            'threshold'      => null,
        ];
    }

    /**
     * Ambil ringkasan dan daftar pengingat dokumen kendaraan untuk dashboard/banner UI.
     */
    public function getReminderSummary(int $limit = 100): array
    {
        $today = Carbon::today();
        $vehicles = $this->vehicleBaseQuery()
            ->select(
                'ID',
                'Asset_No',
                'Registration_No',
                'Unit_Name',
                'Product_Brand',
                'Product_Description',
                'Asset_Type',
                'Asset_Location',
                'Asset_Location_Department',
                'User_Responsible',
                'STNK_Tahunan',
                'STNK_5_Tahun',
                'Jadwal_KIR',
                'Berkas_KIR',
                'Status'
            )
            ->orderBy('Registration_No', 'ASC')
            ->get();

        $counts = [
            'total_vehicles'  => $vehicles->count(),
            'expired'         => 0,
            'critical_14'     => 0,
            'warning_30'      => 0,
            'notice_60'       => 0,
            'attention_total' => 0,
        ];

        $items = [];

        foreach ($vehicles as $v) {
            $docs = [
                'STNK_TAHUNAN' => $v->STNK_Tahunan,
                'STNK_5_TAHUN' => $v->STNK_5_Tahun,
                'KEUR'         => $v->Jadwal_KIR,
            ];

            foreach ($docs as $docType => $dateVal) {
                $eval = $this->evaluateDocumentStatus($dateVal, $today);
                if (!in_array($eval['status'], ['EXPIRED', 'CRITICAL', 'WARNING', 'NOTICE'], true)) {
                    continue;
                }

                if ($eval['status'] === 'EXPIRED') {
                    $counts['expired']++;
                } elseif ($eval['status'] === 'CRITICAL') {
                    $counts['critical_14']++;
                } elseif ($eval['status'] === 'WARNING') {
                    $counts['warning_30']++;
                } elseif ($eval['status'] === 'NOTICE') {
                    $counts['notice_60']++;
                }
                $counts['attention_total']++;

                $items[] = [
                    'asset_no'        => $v->Asset_No,
                    'registration_no' => $v->Registration_No ?: '-',
                    'vehicle_name'    => $v->Unit_Name ?: ($v->Product_Description ?: $v->Product_Brand),
                    'jenis_kendaraan' => $v->Asset_Type ?: '-',
                    'department'      => $v->Asset_Location_Department ?: '-',
                    'location'        => $v->Asset_Location ?: '-',
                    'pic'             => $v->User_Responsible ?: '-',
                    'doc_type'        => $docType,
                    'doc_label'       => self::DOC_TYPES[$docType] ?? $docType,
                    'due_date'        => $eval['due_date'],
                    'days_remaining'  => $eval['days_remaining'],
                    'status'          => $eval['status'],
                    'label'           => $eval['label'],
                    'badge_class'     => $eval['badge_class'],
                    'threshold'       => $eval['threshold'],
                ];
            }
        }

        usort($items, function ($a, $b) {
            return ($a['days_remaining'] ?? 99999) <=> ($b['days_remaining'] ?? 99999);
        });

        if ($limit > 0 && count($items) > $limit) {
            $items = array_slice($items, 0, $limit);
        }

        return [
            'counts' => $counts,
            'items'  => $items,
        ];
    }

    /**
     * Sinkronkan notifikasi In-App secara idempoten ke tabel notification.
     */
    public function syncInAppReminders(bool $force = false): int
    {
        if (!Schema::hasTable('vehicle_document_reminder_log') || !Schema::hasTable('notification')) {
            return 0;
        }

        $summary = $this->getReminderSummary(500);
        $sentCount = 0;
        $now = Carbon::now();

        $hcgaRecipients = $this->resolveHcgaRecipientUserIds();

        foreach ($summary['items'] as $item) {
            $threshold = $item['threshold'];
            if ($threshold === null || empty($item['due_date'])) {
                continue;
            }

            if (!$force) {
                $alreadySent = DB::table('vehicle_document_reminder_log')
                    ->where('Asset_No', $item['asset_no'])
                    ->where('Doc_Type', $item['doc_type'])
                    ->where('Due_Date', $item['due_date'])
                    ->where('Threshold_Days', $threshold)
                    ->where('Channel', 'IN_APP')
                    ->exists();

                if ($alreadySent) {
                    continue;
                }
            }

            $title = sprintf(
                'Reminder %s: %s (%s)',
                $item['doc_label'],
                $item['registration_no'] !== '-' ? $item['registration_no'] : $item['asset_no'],
                $item['label']
            );
            $content = sprintf(
                'Dokumen %s untuk kendaraan %s (%s) jatuh tempo pada %s. PIC: %s.',
                $item['doc_label'],
                $item['vehicle_name'],
                $item['asset_no'],
                $item['due_date'],
                $item['pic']
            );
            $metadata = json_encode([
                'asset_no'        => $item['asset_no'],
                'registration_no' => $item['registration_no'],
                'doc_type'        => $item['doc_type'],
                'due_date'        => $item['due_date'],
                'status'          => $item['status'],
                'url'             => url('hrd-vehicle-management-index'),
            ]);

            $recipientIds = $this->resolveRecipientsForVehicle($item['pic'], $hcgaRecipients);
            if (empty($recipientIds)) {
                $this->notificationService->makeNotification($title, $content, self::TOPIC, null, null, 'SENT', $metadata);
            } else {
                foreach ($recipientIds as $uid) {
                    $this->notificationService->makeNotification($title, $content, self::TOPIC, null, (string) $uid, 'SENT', $metadata);
                }
            }

            DB::table('vehicle_document_reminder_log')->updateOrInsert(
                [
                    'Asset_No'       => $item['asset_no'],
                    'Doc_Type'       => $item['doc_type'],
                    'Due_Date'       => $item['due_date'],
                    'Threshold_Days' => $threshold,
                    'Channel'        => 'IN_APP',
                ],
                [
                    'Sent_To' => empty($recipientIds) ? 'ALL' : implode(',', $recipientIds),
                    'Sent_At' => $now,
                ]
            );

            $sentCount++;
        }

        return $sentCount;
    }

    /**
     * Kirim email reminder untuk dokumen kendaraan yang jatuh tempo (H-60, H-30, H-14, Expired).
     */
    public function sendEmailReminders(): int
    {
        if (!Schema::hasTable('vehicle_document_reminder_log')) {
            return 0;
        }

        $summary = $this->getReminderSummary(500);
        $sentCount = 0;
        $now = Carbon::now();
        $defaultEmails = $this->resolveHcgaEmails();

        foreach ($summary['items'] as $item) {
            $threshold = $item['threshold'];
            if ($threshold === null || empty($item['due_date'])) {
                continue;
            }

            $alreadySent = DB::table('vehicle_document_reminder_log')
                ->where('Asset_No', $item['asset_no'])
                ->where('Doc_Type', $item['doc_type'])
                ->where('Due_Date', $item['due_date'])
                ->where('Threshold_Days', $threshold)
                ->where('Channel', 'EMAIL')
                ->exists();

            if ($alreadySent) {
                continue;
            }

            $recipients = $this->resolveVehicleEmails($item['pic'], $defaultEmails);
            if (empty($recipients)) {
                continue;
            }

            $subject = sprintf(
                '[HCGA Vehicle Reminder] %s - %s (%s)',
                $item['doc_label'],
                $item['registration_no'] !== '-' ? $item['registration_no'] : $item['asset_no'],
                $item['label']
            );
            $body = implode("\n", [
                "Yth. Tim HCGA / PIC Kendaraan,",
                "",
                "Berikut pengingat masa berlaku dokumen kendaraan:",
                "- Asset No       : {$item['asset_no']}",
                "- No Polisi      : {$item['registration_no']}",
                "- Kendaraan      : {$item['vehicle_name']}",
                "- Jenis Dokumen  : {$item['doc_label']}",
                "- Jatuh Tempo    : {$item['due_date']}",
                "- Status         : {$item['label']}",
                "- Lokasi / Dept  : {$item['location']} / {$item['department']}",
                "- PIC            : {$item['pic']}",
                "",
                "Mohon segera melakukan perpanjangan dan memperbarui data pada menu Vehicle Management.",
            ]);

            try {
                Mail::raw($body, function ($message) use ($recipients, $subject) {
                    $message->to($recipients)->subject($subject);
                });

                DB::table('vehicle_document_reminder_log')->updateOrInsert(
                    [
                        'Asset_No'       => $item['asset_no'],
                        'Doc_Type'       => $item['doc_type'],
                        'Due_Date'       => $item['due_date'],
                        'Threshold_Days' => $threshold,
                        'Channel'        => 'EMAIL',
                    ],
                    [
                        'Sent_To' => implode(',', $recipients),
                        'Sent_At' => $now,
                    ]
                );

                $sentCount++;
            } catch (\Throwable $e) {
                Log::warning('Vehicle email reminder failed for ' . $item['asset_no'] . ': ' . $e->getMessage());
            }
        }

        return $sentCount;
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

    protected function resolveRecipientsForVehicle(?string $pic, array $hcgaRecipients): array
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

    protected function resolveVehicleEmails(?string $pic, array $defaultEmails): array
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
