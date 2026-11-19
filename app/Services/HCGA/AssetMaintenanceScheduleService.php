<?php

namespace App\Services\HCGA;

use App\Services\Approval\AssetFleetApprovalService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Service untuk alur Jadwal Perawatan Aset (Perawatan-Assets.drawio.png):
 * Create Schedule -> ApprovalWorkflowService (HCGA Mgr) -> Voucher Service/Maintenance ke Finance
 * -> Schedule Tersimpan -> Execution -> Update Status & PM Aset.
 */
class AssetMaintenanceScheduleService
{
    /** @var AssetFleetApprovalService */
    protected $approvalService;

    public function __construct(AssetFleetApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }
    public const JENIS_PERAWATAN = [
        'PREVENTIVE_MAINTENANCE',
        'INSPECTION',
        'CLEANING',
        'SPARE_PART_REPLACEMENT',
    ];

    public const FREKUENSI_MONTHS = [
        'BULANAN'   => 1,
        '3_BULANAN' => 3,
        '6_BULANAN' => 6,
        'TAHUNAN'   => 12,
    ];

    public function listSchedules()
    {
        return DB::table('asset_maintenance_schedule as s')
            ->leftJoin('asset_list as a', 's.Asset_No', '=', 'a.Asset_No')
            ->leftJoin('supplier as sup', 's.Supplier_ID', '=', 'sup.Supplier_ID')
            ->select(
                's.*',
                'a.Product_Description as Asset_Name',
                'a.Asset_Location',
                'a.Status as Asset_Status',
                'sup.Company_Name as Vendor_Name'
            )
            ->orderBy('s.ID', 'DESC')
            ->get();
    }

    public function createSchedule(array $payload, string $username, int $requesterId = 0): object
    {
        $assetNo = trim((string) ($payload['Asset_No'] ?? ''));
        $jenis = strtoupper(trim((string) ($payload['Jenis_Perawatan'] ?? 'PREVENTIVE_MAINTENANCE')));
        $frekuensi = strtoupper(trim((string) ($payload['Frekuensi'] ?? 'BULANAN')));
        $nextDate = trim((string) ($payload['Next_Schedule_Date'] ?? date('Y-m-d')));

        if ($assetNo === '') {
            throw new \InvalidArgumentException('Asset_No wajib dipilih.');
        }
        if (!in_array($jenis, self::JENIS_PERAWATAN, true)) {
            throw new \InvalidArgumentException('Jenis perawatan tidak valid.');
        }
        if (!isset(self::FREKUENSI_MONTHS[$frekuensi])) {
            throw new \InvalidArgumentException('Frekuensi perawatan tidak valid.');
        }

        $assetExists = DB::table('asset_list')->where('Asset_No', $assetNo)->exists();
        if (!$assetExists) {
            throw new \RuntimeException("Aset {$assetNo} tidak ditemukan.");
        }

        $requesterId = $this->resolveUserId($requesterId, $username);
        $seq = DB::table('asset_maintenance_schedule')->count() + 1;
        $scheduleNo = 'MNT-SCH/' . date('Ym') . '-' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
        $isExternal = !empty($payload['Is_External_Vendor']) ? 1 : 0;
        $estimasi = (float) ($payload['Estimasi_Biaya'] ?? 0);

        return DB::transaction(function () use ($scheduleNo, $assetNo, $jenis, $frekuensi, $nextDate, $payload, $username, $isExternal, $estimasi, $requesterId) {
            $id = DB::table('asset_maintenance_schedule')->insertGetId([
                'Schedule_No'        => $scheduleNo,
                'Asset_No'           => $assetNo,
                'Jenis_Perawatan'    => $jenis,
                'Frekuensi'          => $frekuensi,
                'Next_Schedule_Date' => $nextDate,
                'PIC_Internal'       => $payload['PIC_Internal'] ?? $username,
                'Is_External_Vendor' => $isExternal,
                'Supplier_ID'        => $isExternal ? ($payload['Supplier_ID'] ?? null) : null,
                'Estimasi_Biaya'     => $estimasi,
                'Status'             => 'APPROVAL_HCGA',
                'Previous_Doc'       => $payload['Previous_Doc'] ?? null,
                'Created_By'         => $username,
                'Created_At'         => now(),
                'Updated_At'         => now(),
            ]);

            $approvalReq = $this->approvalService->submit(
                AssetFleetApprovalService::TYPE_MAINTENANCE_SCHEDULE,
                $id,
                $scheduleNo,
                "Jadwal Perawatan Aset {$assetNo} ({$jenis} / {$frekuensi})",
                $requesterId,
                ['Amount' => $estimasi]
            );

            DB::table('asset_maintenance_schedule')->where('ID', $id)->update([
                'Approval_Request_ID' => (int) $approvalReq->ID,
            ]);

            $this->approvalService->syncStatus(AssetFleetApprovalService::TYPE_MAINTENANCE_SCHEDULE, (int) $approvalReq->ID);

            return DB::table('asset_maintenance_schedule')->where('ID', $id)->first();
        });
    }

    public function approveByHcgaManager(int $id, string $action, string $username, ?string $note = null, int $actorId = 0): object
    {
        $schedule = DB::table('asset_maintenance_schedule')->where('ID', $id)->first();
        if (!$schedule) {
            throw new \RuntimeException('Schedule tidak ditemukan.');
        }
        if (empty($schedule->Approval_Request_ID)) {
            throw new \RuntimeException('Schedule belum terhubung ke ApprovalWorkflowService.');
        }

        $actorId = $this->resolveUserId($actorId, $username);
        $this->approvalService->actOnRequest((int) $schedule->Approval_Request_ID, $actorId, $action, (string) ($note ?? ''));

        return DB::table('asset_maintenance_schedule')->where('ID', $id)->first();
    }

    public function resubmitSchedule(int $id, array $payload, string $username, int $userId = 0): object
    {
        $schedule = DB::table('asset_maintenance_schedule')->where('ID', $id)->first();
        if (!$schedule) {
            throw new \RuntimeException('Schedule tidak ditemukan.');
        }
        if (!in_array($schedule->Status, ['RETURNED', 'REJECTED'], true)) {
            throw new \RuntimeException('Hanya schedule berstatus RETURNED atau REJECTED yang dapat diajukan ulang.');
        }
        if (empty($schedule->Approval_Request_ID)) {
            throw new \RuntimeException('Schedule belum memiliki Approval_Request_ID.');
        }

        $userId = $this->resolveUserId($userId, $username);

        return DB::transaction(function () use ($id, $schedule, $payload, $userId) {
            $update = ['Updated_At' => now()];
            foreach (['Jenis_Perawatan', 'Frekuensi', 'Next_Schedule_Date', 'PIC_Internal', 'Is_External_Vendor', 'Supplier_ID', 'Estimasi_Biaya'] as $col) {
                if (array_key_exists($col, $payload)) {
                    $update[$col] = $payload[$col];
                }
            }
            $update['Status'] = 'APPROVAL_HCGA';
            DB::table('asset_maintenance_schedule')->where('ID', $id)->update($update);

            $newReq = $this->approvalService->resubmit(
                (int) $schedule->Approval_Request_ID,
                $id,
                $userId,
                ['Amount' => (float) ($update['Estimasi_Biaya'] ?? $schedule->Estimasi_Biaya)]
            );

            DB::table('asset_maintenance_schedule')->where('ID', $id)->update([
                'Approval_Request_ID' => (int) $newReq->ID,
            ]);

            $this->approvalService->syncStatus(AssetFleetApprovalService::TYPE_MAINTENANCE_SCHEDULE, (int) $newReq->ID);

            return DB::table('asset_maintenance_schedule')->where('ID', $id)->first();
        });
    }

    protected function resolveUserId(int $userId, string $username): int
    {
        if ($userId > 0) {
            return $userId;
        }
        $id = (int) DB::table('users')->where('username', $username)->value('id');
        if ($id > 0) {
            return $id;
        }
        return (int) (auth()->id() ?: 1);
    }

    public function confirmScheduleAndNotify(int $id): object
    {
        $schedule = DB::table('asset_maintenance_schedule')->where('ID', $id)->first();
        if (!$schedule) {
            throw new \RuntimeException('Schedule tidak ditemukan.');
        }

        DB::table('asset_maintenance_schedule')->where('ID', $id)->update([
            'Status'     => 'SCHEDULED',
            'Updated_At' => now(),
        ]);

        return DB::table('asset_maintenance_schedule')->where('ID', $id)->first();
    }

    public function startExecution(int $id, string $username): object
    {
        return DB::transaction(function () use ($id, $username) {
            $schedule = DB::table('asset_maintenance_schedule')->where('ID', $id)->lockForUpdate()->first();
            if (!$schedule) {
                throw new \RuntimeException('Schedule tidak ditemukan.');
            }

            DB::table('asset_maintenance_schedule')->where('ID', $id)->update([
                'Status'     => 'IN_EXECUTION',
                'Updated_At' => now(),
            ]);

            DB::table('asset_list')->where('Asset_No', $schedule->Asset_No)->update([
                'Status'             => 'MAINTENANCE',
                'Last_User'          => $username,
                'Last_Modified_Date' => now(),
            ]);

            return DB::table('asset_maintenance_schedule')->where('ID', $id)->first();
        });
    }

    public function completeExecution(int $id, string $executedDate, string $username): object
    {
        return DB::transaction(function () use ($id, $executedDate, $username) {
            $schedule = DB::table('asset_maintenance_schedule')->where('ID', $id)->lockForUpdate()->first();
            if (!$schedule) {
                throw new \RuntimeException('Schedule tidak ditemukan.');
            }

            $months = self::FREKUENSI_MONTHS[$schedule->Frekuensi] ?? 1;
            $nextDate = Carbon::parse($executedDate)->addMonths($months)->toDateString();

            DB::table('asset_maintenance_schedule')->where('ID', $id)->update([
                'Status'             => 'COMPLETED',
                'Last_Executed_Date' => $executedDate,
                'Next_Schedule_Date' => $nextDate,
                'Updated_At'         => now(),
            ]);

            DB::table('asset_list')->where('Asset_No', $schedule->Asset_No)->update([
                'Status'              => 'ACTIVE',
                'Tanggal_PM_Terakhir' => $executedDate,
                'Tanggal_Jadwal_PM'   => $nextDate,
                'Interval_PM'         => $months,
                'Last_User'           => $username,
                'Last_Modified_Date'  => now(),
            ]);

            return DB::table('asset_maintenance_schedule')->where('ID', $id)->first();
        });
    }
}
