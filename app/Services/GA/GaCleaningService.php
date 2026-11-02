<?php

namespace App\Services\GA;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class GaCleaningService
{
    const STATUS_ACTIVE = 'ACTIVE';
    const STATUS_INACTIVE = 'INACTIVE';

    const TASK_SCHEDULED = 'SCHEDULED';
    const TASK_IN_PROGRESS = 'IN_PROGRESS';
    const TASK_COMPLETED = 'COMPLETED';
    const TASK_MISSED = 'MISSED';
    const TASK_CANCELLED = 'CANCELLED';

    const INSPECTION_PASSED = 'PASSED';
    const INSPECTION_IMPROVEMENT = 'NEEDS_IMPROVEMENT';
    const INSPECTION_FAILED = 'FAILED';

    protected $notifications;
    protected $ticketService;

    public function __construct(GaNotificationService $notifications, TicketService $ticketService)
    {
        $this->notifications = $notifications;
        $this->ticketService = $ticketService;
    }

    /**
     * Daftarkan area/zona pembersihan baru.
     */
    public function registerArea(array $data, $user = null)
    {
        $name = trim((string) ($data['Area_Name'] ?? ''));
        $building = trim((string) ($data['Building'] ?? ''));
        $floor = trim((string) ($data['Floor'] ?? ''));

        if ($name === '' || $building === '' || $floor === '') {
            throw new InvalidArgumentException('Nama Area, Gedung, dan Lantai wajib diisi.');
        }

        $now = Carbon::now();
        $count = DB::table('ga_cleaning_area')->count() + 1;
        $code = !empty($data['Area_Code']) ? trim($data['Area_Code']) : 'CLN-AREA-' . sprintf('%04d', $count);

        $id = DB::table('ga_cleaning_area')->insertGetId([
            'Area_Code' => $code,
            'Area_Name' => $name,
            'Building' => $building,
            'Floor' => $floor,
            'Location' => trim((string) ($data['Location'] ?? '')),
            'Qr_Code' => !empty($data['Qr_Code']) ? trim($data['Qr_Code']) : $code,
            'Category' => trim((string) ($data['Category'] ?? 'OFFICE')),
            'Status' => $data['Status'] ?? self::STATUS_ACTIVE,
            'Remarks' => $data['Remarks'] ?? null,
            'Created_By' => $user ? $user->username : 'system',
            'Created_At' => $now,
            'Updated_At' => $now,
        ]);

        return DB::table('ga_cleaning_area')->where('id', $id)->first();
    }

    /**
     * Buat jadwal pembersihan rutin.
     */
    public function createSchedule(array $data, $user = null)
    {
        $areaId = (int) ($data['Area_ID'] ?? 0);
        $area = DB::table('ga_cleaning_area')->where('id', $areaId)->first();
        if (!$area) {
            throw new InvalidArgumentException('Area pembersihan tidak ditemukan.');
        }

        $now = Carbon::now();
        $count = DB::table('ga_cleaning_schedule')->count() + 1;
        $schedNo = 'CLN-SCH/' . $now->format('Ymd') . '/' . sprintf('%04d', $count);

        $id = DB::table('ga_cleaning_schedule')->insertGetId([
            'Schedule_No' => $schedNo,
            'Area_ID' => $areaId,
            'Frequency' => trim((string) ($data['Frequency'] ?? 'DAILY')),
            'Shift' => trim((string) ($data['Shift'] ?? 'MORNING')),
            'Planned_Time_Start' => $data['Planned_Time_Start'] ?? null,
            'Planned_Time_End' => $data['Planned_Time_End'] ?? null,
            'Assignee_Type' => trim((string) ($data['Assignee_Type'] ?? 'INTERNAL')),
            'Assignee_ID' => $data['Assignee_ID'] ?? null,
            'Assignee_Name' => trim((string) ($data['Assignee_Name'] ?? '')),
            'Status' => self::STATUS_ACTIVE,
            'Notes' => $data['Notes'] ?? null,
            'Created_By' => $user ? $user->username : 'system',
            'Created_At' => $now,
            'Updated_At' => $now,
        ]);

        return DB::table('ga_cleaning_schedule')->where('id', $id)->first();
    }

    /**
     * Generate task harian pembersihan dari jadwal aktif.
     */
    public function generateTasksForDate($date = null, $user = null)
    {
        $date = $date ? Carbon::parse($date)->toDateString() : Carbon::today()->toDateString();
        $schedules = DB::table('ga_cleaning_schedule')
            ->where('Status', self::STATUS_ACTIVE)
            ->get();

        $generated = [];
        foreach ($schedules as $sched) {
            $existing = DB::table('ga_cleaning_task')
                ->where('Schedule_ID', $sched->id)
                ->where('Task_Date', $date)
                ->where('Shift', $sched->Shift)
                ->first();

            if (!$existing) {
                $now = Carbon::now();
                $count = DB::table('ga_cleaning_task')->whereDate('Created_At', $now->toDateString())->count() + 1;
                $taskNo = 'CLN-TSK/' . $now->format('Ymd') . '/' . sprintf('%04d', $count);

                $taskId = DB::table('ga_cleaning_task')->insertGetId([
                    'Task_No' => $taskNo,
                    'Schedule_ID' => $sched->id,
                    'Area_ID' => $sched->Area_ID,
                    'Task_Date' => $date,
                    'Shift' => $sched->Shift,
                    'Executor_Type' => $sched->Assignee_Type,
                    'Executor_User_Id' => $sched->Assignee_ID,
                    'Executor_Name' => $sched->Assignee_Name,
                    'Status' => self::TASK_SCHEDULED,
                    'Notes' => $sched->Notes,
                    'Created_By' => $user ? $user->username : 'system',
                    'Created_At' => $now,
                    'Updated_At' => $now,
                ]);

                $generated[] = $taskId;
            }
        }

        return $generated;
    }

    /**
     * Scan QR Checkpoint pembersihan untuk memulai pekerjaan (Check-in),
     * dilengkapi foto kondisi awal (Photo Before).
     */
    public function startTaskWithQrScan($taskId, $qrCode, $photoBefore = null, $user = null)
    {
        // Kompatibilitas jika dipanggil 3 parameter ($taskId, $qrCode, $user)
        if (is_object($photoBefore) && $user === null) {
            $user = $photoBefore;
            $photoBefore = null;
        }

        $task = DB::table('ga_cleaning_task')->where('id', $taskId)->first();
        if (!$task) {
            throw new InvalidArgumentException('Task pembersihan tidak ditemukan.');
        }

        $area = DB::table('ga_cleaning_area')->where('id', $task->Area_ID)->first();
        if (!$area) {
            throw new InvalidArgumentException('Area task tidak valid.');
        }

        if (trim($area->Qr_Code) !== trim($qrCode) && trim($area->Area_Code) !== trim($qrCode)) {
            throw new InvalidArgumentException('QR Code tidak sesuai dengan area pembersihan yang ditugaskan.');
        }

        $now = Carbon::now();
        $update = [
            'Qr_Code_Scanned' => $qrCode,
            'Scan_In_Time' => $now,
            'Status' => self::TASK_IN_PROGRESS,
            'Executor_User_Id' => $user ? $user->username : $task->Executor_User_Id,
            'Executor_Name' => $user ? ($user->name ?? $user->username) : $task->Executor_Name,
            'Updated_At' => $now,
        ];

        if ($photoBefore) {
            $update['Photo_Before'] = $photoBefore;
        }

        DB::table('ga_cleaning_task')->where('id', $taskId)->update($update);

        return DB::table('ga_cleaning_task')->where('id', $taskId)->first();
    }

    /**
     * Selesaikan task pembersihan dengan checklist items dan foto kondisi akhir (Photo After).
     */
    public function completeTask($taskId, array $checklistResults = [], $notes = '', $photoAfter = null, $user = null)
    {
        // Kompatibilitas jika dipanggil 4 parameter ($taskId, $checklistResults, $notes, $user)
        if (is_object($photoAfter) && $user === null) {
            $user = $photoAfter;
            $photoAfter = null;
        }
        return DB::transaction(function () use ($taskId, $checklistResults, $notes, $photoAfter, $user) {
            $task = DB::table('ga_cleaning_task')->where('id', $taskId)->lockForUpdate()->first();
            if (!$task) {
                throw new InvalidArgumentException('Task pembersihan tidak ditemukan.');
            }

            $now = Carbon::now();

            // Simpan checklist results
            foreach ($checklistResults as $item) {
                $itemName = trim((string) ($item['Item_Name'] ?? ''));
                if ($itemName === '') {
                    continue;
                }

                DB::table('ga_cleaning_task_checklist')->insert([
                    'Task_ID' => $taskId,
                    'Item_Name' => $itemName,
                    'Result' => $item['Result'] ?? 'PASS',
                    'Photo_Proof' => $item['Photo_Proof'] ?? null,
                    'Remarks' => $item['Remarks'] ?? null,
                    'Created_At' => $now,
                    'Updated_At' => $now,
                ]);
            }

            $update = [
                'Completed_Time' => $now,
                'Status' => self::TASK_COMPLETED,
                'Notes' => $notes ?: $task->Notes,
                'Updated_At' => $now,
            ];

            if ($photoAfter) {
                $update['Photo_After'] = $photoAfter;
            }

            DB::table('ga_cleaning_task')->where('id', $taskId)->update($update);

            return DB::table('ga_cleaning_task')->where('id', $taskId)->first();
        });
    }

    /**
     * Catat hasil inspeksi/audit kebersihan oleh supervisor.
     */
    public function recordInspection(array $data, $user = null)
    {
        $areaId = (int) ($data['Area_ID'] ?? 0);
        $score = (float) ($data['Score'] ?? 100);
        $finding = trim((string) ($data['Finding_Notes'] ?? ''));

        $area = DB::table('ga_cleaning_area')->where('id', $areaId)->first();
        if (!$area) {
            throw new InvalidArgumentException('Area tidak ditemukan.');
        }

        $now = Carbon::now();
        $count = DB::table('ga_cleaning_inspection')->whereDate('Created_At', $now->toDateString())->count() + 1;
        $inspNo = 'CLN-INSP/' . $now->format('Ymd') . '/' . sprintf('%04d', $count);

        $resultStatus = self::INSPECTION_PASSED;
        if ($score < 60) {
            $resultStatus = self::INSPECTION_FAILED;
        } elseif ($score < 80) {
            $resultStatus = self::INSPECTION_IMPROVEMENT;
        }

        $followUpTicketNo = null;

        // Jika inspeksi FAILED dan diminta buat follow-up ticket
        if (!empty($data['Spawn_Follow_Up_Ticket']) && $resultStatus === self::INSPECTION_FAILED) {
            $subject = '[CLEANING ISSUE] Temuan Inspeksi ' . $area->Area_Name;
            $description = 'Hasil inspeksi skor: ' . $score . ".\nTemuan: " . $finding;

            // Cari kategori Kebersihan dari ga_ticket_category
            $cleaningCategory = DB::table('ga_ticket_category')
                ->where('Status', 'ACTIVE')
                ->where(function ($q) {
                    $q->where('Code', 'CLEANING')->orWhere('Name', 'LIKE', '%Kebersihan%');
                })
                ->first();

            $location = !empty($area->Location) ? $area->Location : ($user ? $user->Location : 'GEU-HO');
            $company = ($user && !empty($user->Company_ID)) ? $user->Company_ID : 'GEU';

            try {
                $createdTicket = $this->ticketService->create([
                    'Ticket_Subject' => $subject,
                    'Description' => $description,
                    'Location' => $location ?: 'GEU-HO',
                    'Company_ID' => $company ?: 'GEU',
                    'Priority' => 'HIGH',
                    'Source' => 'INSPECTION',
                    'Category_ID' => $cleaningCategory ? $cleaningCategory->ID : null,
                ], $user);

                $followUpTicketNo = $createdTicket ? $createdTicket->Ticket_No : null;
            } catch (\Exception $e) {
                // Fallback manual insert jika TicketService gagal
                $ticketNo = 'TCK/CLN/' . $now->format('Ymd') . '/' . sprintf('%04d', $count);
                $ticketId = DB::table('ticket')->insertGetId([
                    'Ticket_No' => $ticketNo,
                    'Proposed_Date' => $now->toDateString(),
                    'Location' => $location ?: 'GEU-HO',
                    'Company_ID' => $company ?: 'GEU',
                    'Ticket_Subject' => mb_substr($subject, 0, 250),
                    'Description' => mb_substr($description, 0, 250),
                    'Ticket_Status' => 'OPEN',
                    'Priority' => 'HIGH',
                    'Module' => 'GA',
                    'Ticket_Type' => 'GA',
                    'Ticket_Category' => 'Kebersihan',
                    'Reporter' => $user ? ($user->name ?? $user->username) : 'supervisor',
                    'Created_By' => $user ? $user->username : 'supervisor',
                    'Created_Date' => $now,
                ]);

                DB::table('ga_ticket')->insert([
                    'Ticket_ID' => $ticketId,
                    'Location' => $location ?: 'GEU-HO',
                    'Company_ID' => $company ?: 'GEU',
                    'Category_ID' => $cleaningCategory ? $cleaningCategory->ID : 6,
                    'Reporter_Name' => $user ? ($user->name ?? $user->username) : 'Supervisor GA',
                    'Priority' => 'HIGH',
                    'Source' => 'INSPECTION',
                    'Created_At' => $now,
                    'Updated_At' => $now,
                ]);

                $followUpTicketNo = $ticketNo;
            }
        }

        $id = DB::table('ga_cleaning_inspection')->insertGetId([
            'Inspection_No' => $inspNo,
            'Area_ID' => $areaId,
            'Task_ID' => $data['Task_ID'] ?? null,
            'Inspection_Date' => $data['Inspection_Date'] ?? $now->toDateString(),
            'Inspector_User_Id' => $user ? $user->username : 'supervisor',
            'Inspector_Name' => $user ? ($user->name ?? $user->username) : 'Supervisor GA',
            'Score' => $score,
            'Audit_Round' => 1,
            'Initial_Score' => $score,
            'Initial_Status' => $resultStatus,
            'Result_Status' => $resultStatus,
            'Finding_Notes' => $finding,
            'Follow_Up_Ticket_No' => $followUpTicketNo,
            'Created_At' => $now,
            'Updated_At' => $now,
        ]);

        // Catat log putaran 1 (Initial Inspection)
        DB::table('ga_cleaning_inspection_log')->insert([
            'Inspection_ID' => $id,
            'Inspection_No' => $inspNo,
            'Audit_Round' => 1,
            'Score' => $score,
            'Result_Status' => $resultStatus,
            'Notes' => $finding,
            'Inspector_User_Id' => $user ? $user->username : 'supervisor',
            'Inspector_Name' => $user ? ($user->name ?? $user->username) : 'Supervisor GA',
            'Created_At' => $now,
        ]);

        return DB::table('ga_cleaning_inspection')->where('id', $id)->first();
    }

    /**
     * Catat hasil inspeksi ulang (Re-Inspeksi / Re-Audit) pada No. Inspeksi yang sama.
     */
    public function recordReinspection($inspectionId, array $data, $user = null)
    {
        $inspection = DB::table('ga_cleaning_inspection')->where('id', (int) $inspectionId)->first();
        if (!$inspection) {
            throw new InvalidArgumentException('Data inspeksi tidak ditemukan.');
        }

        $score = (float) ($data['Score'] ?? 100);
        $notes = trim((string) ($data['Notes'] ?? $data['Finding_Notes'] ?? ''));

        $newStatus = self::INSPECTION_PASSED;
        if ($score < 60) {
            $newStatus = self::INSPECTION_FAILED;
        } elseif ($score < 80) {
            $newStatus = self::INSPECTION_IMPROVEMENT;
        }

        $now = Carbon::now();
        $nextRound = (int) $inspection->Audit_Round + 1;

        // Catat ke log histori audit
        DB::table('ga_cleaning_inspection_log')->insert([
            'Inspection_ID' => $inspection->id,
            'Inspection_No' => $inspection->Inspection_No,
            'Audit_Round' => $nextRound,
            'Score' => $score,
            'Result_Status' => $newStatus,
            'Notes' => $notes,
            'Inspector_User_Id' => $user ? $user->username : 'supervisor',
            'Inspector_Name' => $user ? ($user->name ?? $user->username) : 'Supervisor GA',
            'Created_At' => $now,
        ]);

        // Perbarui data induk inspeksi
        DB::table('ga_cleaning_inspection')->where('id', $inspection->id)->update([
            'Score' => $score,
            'Result_Status' => $newStatus,
            'Audit_Round' => $nextRound,
            'Reinspection_At' => $now,
            'Reinspection_Notes' => $notes,
            'Updated_At' => $now,
        ]);

        // Jika re-inspeksi berhasil PASSED dan memiliki tiket follow-up, update tiket ke RESOLVED jika masih OPEN
        if ($newStatus === self::INSPECTION_PASSED && !empty($inspection->Follow_Up_Ticket_No)) {
            $ticket = DB::table('ticket')->where('Ticket_No', $inspection->Follow_Up_Ticket_No)->first();
            if ($ticket && in_array(strtoupper((string) $ticket->Ticket_Status), ['OPEN', 'ASSIGNED', 'IN_PROGRESS', 'ON_HOLD', 'TRIAGED'], true)) {
                try {
                    $this->ticketService->resolve($ticket->ID, [
                        'Completion_Note' => 'Inspeksi ulang berhasil PASSED (Skor: ' . $score . '). Kondisi area sudah bersih sesuai standar.',
                    ], $user);
                } catch (\Exception $e) {
                    DB::table('ticket')->where('ID', $ticket->ID)->update([
                        'Ticket_Status' => 'RESOLVED',
                        'Completion_Note' => 'Inspeksi ulang berhasil PASSED (Skor: ' . $score . ').',
                    ]);
                }
            }
        }

        return DB::table('ga_cleaning_inspection')->where('id', $inspection->id)->first();
    }

    /**
     * Ambil histori log audit/re-inspeksi berdasarkan ID inspeksi.
     */
    public function getInspectionLogs($inspectionId)
    {
        return DB::table('ga_cleaning_inspection_log')
            ->where('Inspection_ID', (int) $inspectionId)
            ->orderBy('Audit_Round', 'ASC')
            ->get();
    }

    /**
     * Dapatkan daftar template checklist berdasarkan kategori area.
     */
    public function getChecklistTemplateForArea($areaIdOrCategory)
    {
        $category = $areaIdOrCategory;
        if (is_numeric($areaIdOrCategory)) {
            $area = DB::table('ga_cleaning_area')->where('id', (int) $areaIdOrCategory)->first();
            $category = $area ? $area->Category : 'OFFICE';
        }

        $category = strtoupper(trim((string) $category));

        $templates = DB::table('ga_cleaning_checklist_template')
            ->where('Area_Category', $category)
            ->where('Status', 'ACTIVE')
            ->orderBy('Sort_Order')
            ->get();

        if ($templates->isEmpty()) {
            // Fallback ke OFFICE jika kategori spesifik belum ada
            $templates = DB::table('ga_cleaning_checklist_template')
                ->where('Area_Category', 'OFFICE')
                ->where('Status', 'ACTIVE')
                ->orderBy('Sort_Order')
                ->get();
        }

        return $templates;
    }
}
