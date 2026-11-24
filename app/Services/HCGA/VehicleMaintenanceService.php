<?php

namespace App\Services\HCGA;

use App\Services\Approval\AssetFleetApprovalService;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Service layer terpadu untuk modul Vehicle Maintenance:
 * - Full lifecycle: DRAFT -> WAITING_APPROVAL -> APPROVED -> IN_PROGRESS -> COMPLETED (+ RETURNED / REJECTED)
 * - Integrasi ApprovalWorkflowService melalui AssetFleetApprovalService
 * - Sinkronisasi status aset (MAINTENANCE / ACTIVE) & jadwal PM pada asset_list saat COMPLETED
 */
class VehicleMaintenanceService
{
    public const SUB_CATEGORY_KENDARAAN = 'VEHICLE';

    public const ACTIVE_STATUSES = [
        'DRAFT',
        'WAITING_APPROVAL',
        'APPROVED',
        'IN_PROGRESS',
        'APPROVAL_PIC',
        'APPROVAL_ATASAN',
        'APPROVAL_VP',
        'APPROVAL_HCGA_MANAGER',
    ];

    /** @var AssetFleetApprovalService */
    protected $approvalService;

    public function __construct(AssetFleetApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    /**
     * Daftar kendaraan dari asset_list (sub category VEHICLE atau memiliki Registration_No).
     */
    public function getVehicleAssets()
    {
        $select = [
            'Asset_No',
            'Product_Code',
            'Product_Description',
            'SN',
            'Registration_No',
            'Asset_Type',
            'Asset_Type as Jenis_Kendaraan',
            'Asset_Location',
            'Asset_Location_Department',
            'KM_Saat_Ini',
            'KM_Jadwal_PM',
            'Interval_PM',
            'Tanggal_Jadwal_PM',
            'Tanggal_PM_Terakhir',
            'Repair_Vendor',
            'Status',
            'User_Responsible',
        ];

        if (Schema::hasColumn('asset_list', 'Interval_KM_PM')) {
            $select[] = 'Interval_KM_PM';
        }

        return DB::table('asset_list')
            ->where(function ($q) {
                $q->where('Asset_Sub_Category', self::SUB_CATEGORY_KENDARAAN)
                    ->orWhere(function ($sub) {
                        $sub->whereNotNull('Registration_No')
                            ->where('Registration_No', '!=', '');
                    });
            })
            ->where(function ($q) {
                $q->whereNull('Status')->orWhere('Status', '!=', 'DISPOSED');
            })
            ->select($select)
            ->orderBy('Asset_No', 'ASC')
            ->get();
    }

    /**
     * Ambil odometer efektif tertinggi (KM) per Asset_No dari asset_list, vehicle_logbook, dan vehicle_fuel_log.
     *
     * @param array $assetNos
     * @return array<string, int>
     */
    public function getEffectiveOdometerMap(array $assetNos = []): array
    {
        $map = [];

        $assetQuery = DB::table('asset_list')->select('Asset_No', 'KM_Saat_Ini');
        if (!empty($assetNos)) {
            $assetQuery->whereIn('Asset_No', $assetNos);
        } else {
            $assetQuery->where(function ($q) {
                $q->where('Asset_Sub_Category', self::SUB_CATEGORY_KENDARAAN)
                    ->orWhere(function ($sub) {
                        $sub->whereNotNull('Registration_No')->where('Registration_No', '!=', '');
                    });
            });
        }

        foreach ($assetQuery->get() as $row) {
            $map[$row->Asset_No] = max(0, (int) ($row->KM_Saat_Ini ?? 0));
        }

        if (Schema::hasTable('vehicle_logbook')) {
            $logQuery = DB::table('vehicle_logbook')
                ->where('Status', '!=', 'CANCELLED')
                ->whereNotNull('End_KM')
                ->select('Asset_No', DB::raw('MAX(End_KM) as max_km'))
                ->groupBy('Asset_No');
            if (!empty($assetNos)) {
                $logQuery->whereIn('Asset_No', $assetNos);
            }
            foreach ($logQuery->get() as $row) {
                $km = (int) round((float) $row->max_km);
                if (!isset($map[$row->Asset_No]) || $km > $map[$row->Asset_No]) {
                    $map[$row->Asset_No] = $km;
                }
            }
        }

        if (Schema::hasTable('vehicle_fuel_log')) {
            $fuelQuery = DB::table('vehicle_fuel_log')
                ->whereNotNull('Current_KM')
                ->select('Asset_No', DB::raw('MAX(Current_KM) as max_km'))
                ->groupBy('Asset_No');
            if (!empty($assetNos)) {
                $fuelQuery->whereIn('Asset_No', $assetNos);
            }
            foreach ($fuelQuery->get() as $row) {
                $km = (int) round((float) $row->max_km);
                if (!isset($map[$row->Asset_No]) || $km > $map[$row->Asset_No]) {
                    $map[$row->Asset_No] = $km;
                }
            }
        }

        return $map;
    }

    /**
     * Ringkasan KPI dashboard pada halaman Vehicle Maintenance.
     */
    public function getDashboardKpi(string $dept, int $duePmCount = 0): array
    {
        $query = DB::table('vehicle_maintenance');
        if (!in_array($dept, ['HCGA', 'DIR', 'OPERATION'], true) && $dept !== '') {
            $query->where('Department', $dept);
        }

        $row = $query->selectRaw("
            COUNT(*) AS total_count,
            SUM(CASE WHEN Status IN ('WAITING_APPROVAL', 'APPROVAL_PIC', 'APPROVAL_ATASAN', 'APPROVAL_VP', 'APPROVAL_HCGA_MANAGER') THEN 1 ELSE 0 END) AS waiting_count,
            SUM(CASE WHEN Status = 'IN_PROGRESS' THEN 1 ELSE 0 END) AS in_progress_count,
            SUM(CASE WHEN Status = 'COMPLETED' THEN 1 ELSE 0 END) AS completed_count,
            SUM(CASE WHEN Status IN ('DRAFT', 'RETURNED') THEN 1 ELSE 0 END) AS draft_count
        ")->first();

        return [
            'total'       => (int) ($row->total_count ?? 0),
            'waiting'     => (int) ($row->waiting_count ?? 0),
            'in_progress' => (int) ($row->in_progress_count ?? 0),
            'completed'   => (int) ($row->completed_count ?? 0),
            'draft'       => (int) ($row->draft_count ?? 0),
            'due_pm'      => $duePmCount,
        ];
    }

    /**
     * Ambil data perawatan untuk grid dhtmlXGrid & export Excel.
     */
    public function listForGrid(string $dept)
    {
        $query = DB::table('vehicle_maintenance as vm')
            ->leftJoin('users as u', 'vm.User_Trx', '=', 'u.username')
            ->select(
                'vm.*',
                DB::raw('COALESCE(NULLIF(vm.Nama_Pengaju, ""), u.Name, vm.User_Trx) as Display_Pengaju'),
                DB::raw('COALESCE(NULLIF(vm.Jabatan, ""), u.Position, "-") as Display_Jabatan')
            )
            ->orderBy('vm.created_at', 'DESC')
            ->orderBy('vm.id', 'DESC');

        if (!in_array($dept, ['HCGA', 'DIR', 'OPERATION'], true) && $dept !== '') {
            $query->where('vm.Department', $dept);
        }

        return $query->get();
    }

    /**
     * Buat pengajuan perawatan kendaraan (DRAFT atau langsung WAITING_APPROVAL via ApprovalWorkflowService).
     */
    public function createMaintenance(array $payload, object $user, bool $asDraft = false): object
    {
        $assetKey = trim((string) ($payload['Asset_No'] ?? ''));
        if ($assetKey === '') {
            $assetKey = trim((string) ($payload['Product_Code'] ?? ''));
        }
        if ($assetKey === '') {
            throw new \InvalidArgumentException('Harap pilih kendaraan dari daftar terlebih dahulu!');
        }

        $jenis = strtolower(trim((string) ($payload['Jenis_Perawatan'] ?? 'preventif')));
        if (!in_array($jenis, ['preventif', 'korektif'], true)) {
            throw new \InvalidArgumentException('Jenis perawatan harus preventif atau korektif.');
        }

        $tipePreventif = trim((string) ($payload['Tipe_Service_Preventif'] ?? ''));
        $kerusakan = trim((string) ($payload['Kerusakan_Yang_Ditemui'] ?? ''));

        if ($jenis === 'preventif' && $tipePreventif === '') {
            throw new \InvalidArgumentException('Tipe Service Preventif wajib dipilih untuk perawatan preventif.');
        }
        if ($jenis === 'korektif' && $kerusakan === '') {
            throw new \InvalidArgumentException('Kerusakan Yang Ditemui wajib diisi untuk perawatan korektif.');
        }

        $asset = $this->findVehicleAsset($assetKey);
        if (!$asset) {
            throw new \RuntimeException('Kendaraan tidak ditemukan di asset_list!');
        }

        $estimasi = $this->parseCurrency($payload['Estimasi_Biaya'] ?? '0');
        $interval = (int) ($payload['Jadwal_Perawatan_Bulan'] ?? 0);
        if ($interval <= 0) {
            $interval = (int) ($asset->Interval_PM ?? 0);
        }

        $tglPengajuan = trim((string) ($payload['Tanggal_Pengajuan'] ?? ''));
        if ($tglPengajuan === '') {
            $tglPengajuan = date('Y-m-d');
        }

        $km = $this->parseInteger($payload['Kilometer_Saat_Ini'] ?? '0');
        if ($km <= 0) {
            $odoMap = $this->getEffectiveOdometerMap([$asset->Asset_No]);
            $km = $odoMap[$asset->Asset_No] ?? (int) ($asset->KM_Saat_Ini ?? 0);
        }

        $vendor = trim((string) ($payload['Vendor_Bengkel'] ?? ''));
        if ($vendor === '') {
            $vendor = (string) ($asset->Repair_Vendor ?? '');
        }

        $username = (string) ($user->username ?? 'system');
        $userId = (int) ($user->id ?? 0);
        $dept = (string) ($user->Department ?? 'HCGA');
        $noTrx = $this->generateNoTrx($jenis);

        $insertData = [
            'No_Trx'                 => $noTrx,
            'Jenis_Perawatan'        => $jenis,
            'Asset_No'               => $asset->Asset_No,
            'Product_Code'           => (string) ($asset->Product_Code ?? ''),
            'Product_Description'    => (string) ($asset->Product_Description ?? ''),
            'SN'                     => $asset->SN ?? '',
            'Plat_Nomor'             => $asset->Registration_No ?? '',
            'Kategori_Kendaraan'     => $asset->Asset_Type ?? '',
            'Kilometer_Saat_Ini'     => $km,
            'Tipe_Service_Preventif' => $jenis === 'preventif' ? $tipePreventif : '',
            'Jadwal_Perawatan_Bulan' => $jenis === 'preventif' ? $interval : 0,
            'Kerusakan_Yang_Ditemui' => $jenis === 'korektif' ? $kerusakan : '',
            'Penyebab_Diperkirakan'  => $jenis === 'korektif' ? trim((string) ($payload['Penyebab_Diperkirakan'] ?? '')) : '',
            'Komponen_Yang_Diganti'  => $jenis === 'korektif' ? trim((string) ($payload['Komponen_Yang_Diganti'] ?? '')) : '',
            'Kondisi_Awal'           => trim((string) ($payload['Kondisi_Awal'] ?? '')),
            'Keterangan_Lain'        => trim((string) ($payload['Keterangan_Lain'] ?? '')),
            'Estimasi_Biaya'         => $estimasi,
            'Vendor_Bengkel'         => $vendor,
            'Status'                 => $asDraft ? 'DRAFT' : 'WAITING_APPROVAL',
            'Department'             => $dept,
            'User_NIP'               => $user->NIP ?? $user->Emp_ID ?? $username,
            'User_Trx'               => $username,
            'created_at'             => now(),
            'updated_at'             => now(),
        ];

        if (Schema::hasColumn('vehicle_maintenance', 'Nama_Pengaju')) {
            $insertData['Nama_Pengaju'] = trim((string) ($payload['Nama_Pengaju'] ?? ($user->Name ?? $username)));
        }
        if (Schema::hasColumn('vehicle_maintenance', 'Jabatan')) {
            $insertData['Jabatan'] = trim((string) ($payload['Jabatan'] ?? ($user->Position ?? '')));
        }
        if (Schema::hasColumn('vehicle_maintenance', 'Tanggal_Pengajuan')) {
            $insertData['Tanggal_Pengajuan'] = $tglPengajuan;
        }
        if (Schema::hasColumn('vehicle_maintenance', 'Nomor_Formulir')) {
            $insertData['Nomor_Formulir'] = trim((string) ($payload['Nomor_Formulir'] ?? ''));
        }

        $recordId = DB::transaction(function () use ($insertData, $asDraft, $noTrx, $asset, $jenis, $estimasi, $userId, $dept) {
            $id = (int) DB::table('vehicle_maintenance')->insertGetId($insertData);

            if (!$asDraft) {
                $this->submitToApprovalEngine($id, $noTrx, $asset, $jenis, $estimasi, $userId, $dept);
            }

            return $id;
        });

        return DB::table('vehicle_maintenance')->where('id', $recordId)->first();
    }

    /**
     * Ajukan draft atau ajukan ulang pengajuan berstatus RETURNED / REJECTED ke ApprovalWorkflowService.
     */
    public function submitDraft(int $id, object $user): object
    {
        $rec = DB::table('vehicle_maintenance')->where('id', $id)->first();
        if (!$rec) {
            throw new \RuntimeException('Data perawatan kendaraan tidak ditemukan.');
        }

        if (!in_array($rec->Status, ['DRAFT', 'RETURNED', 'REJECTED'], true)) {
            throw new \RuntimeException('Hanya pengajuan berstatus DRAFT, RETURNED, atau REJECTED yang dapat diajukan.');
        }

        $asset = $this->findVehicleAsset($rec->Asset_No);
        $userId = (int) ($user->id ?? 0);
        $dept = (string) ($rec->Department ?: ($user->Department ?? 'HCGA'));

        DB::transaction(function () use ($rec, $asset, $userId, $dept) {
            if (!empty($rec->Approval_Request_ID) && in_array($rec->Status, ['RETURNED', 'REJECTED'], true)) {
                $newReq = $this->approvalService->resubmit(
                    (int) $rec->Approval_Request_ID,
                    (int) $rec->id,
                    $userId,
                    [
                        'Amount'     => (float) $rec->Estimasi_Biaya,
                        'Department' => $dept,
                    ]
                );

                DB::table('vehicle_maintenance')->where('id', $rec->id)->update([
                    'Approval_Request_ID' => (int) $newReq->ID,
                    'Status'              => 'WAITING_APPROVAL',
                    'updated_at'          => now(),
                ]);

                $this->syncFromApprovalRequest((int) $newReq->ID);
            } else {
                $this->submitToApprovalEngine(
                    (int) $rec->id,
                    (string) $rec->No_Trx,
                    $asset,
                    (string) $rec->Jenis_Perawatan,
                    (float) $rec->Estimasi_Biaya,
                    $userId,
                    $dept
                );
            }
        });

        return DB::table('vehicle_maintenance')->where('id', $id)->first();
    }

    /**
     * Eksekusi APPROVE / REJECT melalui ApprovalWorkflowService dan catat audit log.
     */
    public function actApproval(int $id, string $action, string $note, object $user): object
    {
        $rec = DB::table('vehicle_maintenance')->where('id', $id)->first();
        if (!$rec) {
            throw new \RuntimeException('Data perawatan kendaraan tidak ditemukan.');
        }

        if (empty($rec->Approval_Request_ID)) {
            throw new \RuntimeException('Pengajuan ini belum terhubung dengan Approval Workflow Engine.');
        }

        $actionUpper = strtoupper(trim($action));
        if (!in_array($actionUpper, ['APPROVE', 'REJECT'], true)) {
            throw new \InvalidArgumentException('Aksi harus APPROVE atau REJECT.');
        }

        $actorId = (int) ($user->id ?? 0);
        $username = (string) ($user->username ?? 'system');
        $approverName = (string) ($user->Name ?: $username);
        $dept = (string) ($user->Department ?? '');

        DB::transaction(function () use ($rec, $actorId, $actionUpper, $note, $username, $approverName, $dept) {
            $this->approvalService->actOnRequest(
                (int) $rec->Approval_Request_ID,
                $actorId,
                $actionUpper,
                $note
            );

            $timestamp = now()->format('Y-m-d H:i:s');
            $existingNote = $rec->Keterangan_Lain ? $rec->Keterangan_Lain . "\n" : '';
            $verb = $actionUpper === 'REJECT' ? 'REJECTED' : 'APPROVED';
            $newNote = trim($existingNote . "{$timestamp} {$verb} by {$approverName} ({$dept})" . ($note !== '' ? " :: {$note}" : ''));

            DB::table('vehicle_maintenance')->where('id', $rec->id)->update([
                'Keterangan_Lain' => $newNote,
                'updated_at'      => now(),
            ]);

            if (Schema::hasTable('vehicle_maintenance_approval')) {
                DB::table('vehicle_maintenance_approval')->insert([
                    'vehicle_maintenance_id' => $rec->id,
                    'approver_username'      => $username,
                    'approver_name'          => $approverName,
                    'approver_dept'          => $dept,
                    'level'                  => 'ENGINE',
                    'action'                 => $actionUpper,
                    'notes'                  => $note,
                    'approved_at'            => $timestamp,
                    'created_at'             => $timestamp,
                    'updated_at'             => $timestamp,
                ]);
            }
        });

        return DB::table('vehicle_maintenance')->where('id', $id)->first();
    }

    /**
     * Sinkronisasi status dari approval_request ke vehicle_maintenance (idempoten).
     */
    public function syncFromApprovalRequest(int $approvalRequestId): void
    {
        $req = DB::table('approval_request')->where('ID', $approvalRequestId)->first();
        if (!$req) {
            return;
        }

        $rec = DB::table('vehicle_maintenance')->where('Approval_Request_ID', $approvalRequestId)->first();
        if (!$rec && !empty($req->Map_To)) {
            $rec = DB::table('vehicle_maintenance')->where('id', (int) $req->Map_To)->first();
        }
        if (!$rec) {
            return;
        }

        // Jangan menimpa status eksekusi yang sudah berjalan atau selesai
        if (in_array($rec->Status, ['IN_PROGRESS', 'COMPLETED'], true)) {
            return;
        }

        $statusUpper = strtoupper((string) $req->Status);
        if ($statusUpper === 'APPROVED') {
            $targetStatus = 'APPROVED';
        } elseif ($statusUpper === 'REJECTED') {
            $targetStatus = 'REJECTED';
        } elseif ($statusUpper === 'RETURNED') {
            $targetStatus = 'RETURNED';
        } elseif ($statusUpper === 'CANCELLED') {
            $targetStatus = 'CANCELLED';
        } else {
            $targetStatus = 'WAITING_APPROVAL';
        }

        if ($rec->Status !== $targetStatus || (int) $rec->Approval_Request_ID !== $approvalRequestId) {
            DB::table('vehicle_maintenance')->where('id', $rec->id)->update([
                'Status'              => $targetStatus,
                'Approval_Request_ID' => $approvalRequestId,
                'updated_at'          => now(),
            ]);
        }
    }

    /**
     * Mulai eksekusi servis: APPROVED -> IN_PROGRESS dan asset_list.Status -> MAINTENANCE.
     */
    public function startExecution(int $id, string $startDate, object $user): object
    {
        $username = (string) ($user->username ?? 'system');
        $cleanDate = trim($startDate) !== '' ? trim($startDate) : date('Y-m-d');

        return DB::transaction(function () use ($id, $cleanDate, $username) {
            $rec = DB::table('vehicle_maintenance')->where('id', $id)->lockForUpdate()->first();
            if (!$rec) {
                throw new \RuntimeException('Data perawatan kendaraan tidak ditemukan.');
            }
            if ($rec->Status !== 'APPROVED') {
                throw new \RuntimeException('Hanya pengajuan berstatus APPROVED yang dapat dimulai proses servisnya.');
            }

            $upd = [
                'Status'     => 'IN_PROGRESS',
                'updated_at' => now(),
            ];
            if (Schema::hasColumn('vehicle_maintenance', 'Tanggal_Mulai_Service')) {
                $upd['Tanggal_Mulai_Service'] = $cleanDate;
            }
            if (Schema::hasColumn('vehicle_maintenance', 'Started_By')) {
                $upd['Started_By'] = $username;
            }

            DB::table('vehicle_maintenance')->where('id', $id)->update($upd);

            DB::table('asset_list')->where('Asset_No', $rec->Asset_No)->update([
                'Status'             => 'MAINTENANCE',
                'Last_User'          => $username,
                'Last_Modified_Date' => now(),
            ]);

            return DB::table('vehicle_maintenance')->where('id', $id)->first();
        });
    }

    /**
     * Selesaikan servis: IN_PROGRESS -> COMPLETED, simpan nota & biaya aktual,
     * kembalikan asset_list.Status -> ACTIVE, dan hitung ulang jadwal PM berikutnya.
     */
    public function completeExecution(int $id, array $payload, ?UploadedFile $notaFile, object $user): object
    {
        $username = (string) ($user->username ?? 'system');
        $endDate = trim((string) ($payload['Tanggal_Selesai_Service'] ?? ''));
        if ($endDate === '') {
            $endDate = date('Y-m-d');
        }

        $kmSelesai = $this->parseInteger($payload['Kilometer_Selesai'] ?? '0');
        $biayaAktual = $this->parseCurrency($payload['Biaya_Aktual'] ?? '0');
        $catatan = trim((string) ($payload['Catatan_Penyelesaian'] ?? ''));
        $vendorUpdate = trim((string) ($payload['Vendor_Bengkel'] ?? ''));

        return DB::transaction(function () use ($id, $endDate, $kmSelesai, $biayaAktual, $catatan, $vendorUpdate, $notaFile, $username) {
            $rec = DB::table('vehicle_maintenance')->where('id', $id)->lockForUpdate()->first();
            if (!$rec) {
                throw new \RuntimeException('Data perawatan kendaraan tidak ditemukan.');
            }
            if ($rec->Status !== 'IN_PROGRESS') {
                throw new \RuntimeException('Hanya perawatan berstatus IN_PROGRESS yang dapat diselesaikan.');
            }

            $notaPath = $rec->Bukti_Nota_Path ?? null;
            if ($notaFile instanceof UploadedFile) {
                $notaPath = $this->storeNotaFile($id, (string) $rec->No_Trx, $notaFile);
            }

            $effectiveKm = $kmSelesai > 0 ? $kmSelesai : (int) $rec->Kilometer_Saat_Ini;
            $effectiveVendor = $vendorUpdate !== '' ? $vendorUpdate : (string) ($rec->Vendor_Bengkel ?? '');

            $vmUpd = [
                'Status'     => 'COMPLETED',
                'updated_at' => now(),
            ];
            if ($effectiveVendor !== '') {
                $vmUpd['Vendor_Bengkel'] = $effectiveVendor;
            }
            if (Schema::hasColumn('vehicle_maintenance', 'Biaya_Aktual')) {
                $vmUpd['Biaya_Aktual'] = $biayaAktual;
            }
            if (Schema::hasColumn('vehicle_maintenance', 'Tanggal_Selesai_Service')) {
                $vmUpd['Tanggal_Selesai_Service'] = $endDate;
            }
            if (Schema::hasColumn('vehicle_maintenance', 'Kilometer_Selesai')) {
                $vmUpd['Kilometer_Selesai'] = $effectiveKm;
            }
            if (Schema::hasColumn('vehicle_maintenance', 'Bukti_Nota_Path')) {
                $vmUpd['Bukti_Nota_Path'] = $notaPath;
            }
            if (Schema::hasColumn('vehicle_maintenance', 'Catatan_Penyelesaian')) {
                $vmUpd['Catatan_Penyelesaian'] = $catatan;
            }
            if (Schema::hasColumn('vehicle_maintenance', 'Completed_By')) {
                $vmUpd['Completed_By'] = $username;
            }

            DB::table('vehicle_maintenance')->where('id', $id)->update($vmUpd);

            $this->syncAssetOnCompleted($rec, $endDate, $effectiveKm, $effectiveVendor, $username);

            return DB::table('vehicle_maintenance')->where('id', $id)->first();
        });
    }

    /**
     * Perbarui pengaturan jadwal PM pada master asset_list dari modal Atur Jadwal PM.
     */
    public function updateVehiclePmSchedule(string $assetNo, array $payload, object $user): object
    {
        $asset = DB::table('asset_list')->where('Asset_No', $assetNo)->first();
        if (!$asset) {
            throw new \RuntimeException("Kendaraan {$assetNo} tidak ditemukan.");
        }

        $intervalBulan = (int) ($payload['Interval_PM'] ?? 0);
        $intervalKm = (int) ($payload['Interval_KM_PM'] ?? 0);
        $tglJadwal = trim((string) ($payload['Tanggal_Jadwal_PM'] ?? ''));
        $kmJadwal = $this->parseInteger($payload['KM_Jadwal_PM'] ?? '0');
        $vendor = trim((string) ($payload['Repair_Vendor'] ?? ''));

        $upd = [
            'Interval_PM'        => $intervalBulan > 0 ? $intervalBulan : null,
            'Tanggal_Jadwal_PM'  => $tglJadwal !== '' ? $tglJadwal : null,
            'KM_Jadwal_PM'       => $kmJadwal > 0 ? $kmJadwal : null,
            'Last_Reminder_Sent' => null,
            'Last_User'          => (string) ($user->username ?? 'system'),
            'Last_Modified_Date' => now(),
        ];

        if ($vendor !== '') {
            $upd['Repair_Vendor'] = substr($vendor, 0, 40);
        }

        if (Schema::hasColumn('asset_list', 'Interval_KM_PM')) {
            $upd['Interval_KM_PM'] = $intervalKm > 0 ? $intervalKm : null;
        }

        DB::table('asset_list')->where('Asset_No', $assetNo)->update($upd);

        return DB::table('asset_list')->where('Asset_No', $assetNo)->first();
    }

    /**
     * Ambil detail lengkap record beserta timeline approval & info kandidat approver.
     */
    public function getDetailWithTimeline(int $id, ?object $currentUser = null): ?array
    {
        $data = DB::table('vehicle_maintenance as vm')
            ->leftJoin('users as u', 'vm.User_Trx', '=', 'u.username')
            ->where('vm.id', $id)
            ->select(
                'vm.*',
                DB::raw('COALESCE(NULLIF(vm.Nama_Pengaju, ""), u.Name, vm.User_Trx) as Nama_Pengaju_Display'),
                DB::raw('COALESCE(NULLIF(vm.Jabatan, ""), u.Position, "-") as Jabatan_Display')
            )
            ->first();

        if (!$data) {
            return null;
        }

        $data->Nama_Pengaju = $data->Nama_Pengaju_Display;
        $data->Jabatan = $data->Jabatan_Display;

        $timeline = $this->getApprovalTimeline($id, (int) ($data->Approval_Request_ID ?? 0));

        $canApprove = false;
        $canForceApprove = false;
        $userId = (int) ($currentUser->id ?? 0);
        $username = strtolower(trim((string) ($currentUser->username ?? '')));

        if (!empty($data->Approval_Request_ID) && $data->Status === 'WAITING_APPROVAL' && $userId > 0) {
            $canApprove = $this->approvalService->isActualCandidate((int) $data->Approval_Request_ID, $userId);
            $canForceApprove = ($username === 'admin');
        }

        return [
            'data'            => $data,
            'timeline'        => $timeline,
            'canApprove'      => $canApprove,
            'canForceApprove' => $canForceApprove,
        ];
    }

    /**
     * Daftar pengajuan WAITING_APPROVAL yang dapat di-approve oleh user yang login.
     */
    public function listPendingApprovalsForUser(object $user)
    {
        $userId = (int) ($user->id ?? 0);
        $isAdmin = strtolower(trim((string) ($user->username ?? ''))) === 'admin';

        $rows = DB::table('vehicle_maintenance as vm')
            ->leftJoin('users as u', 'vm.User_Trx', '=', 'u.username')
            ->where('vm.Status', 'WAITING_APPROVAL')
            ->whereNotNull('vm.Approval_Request_ID')
            ->select(
                'vm.*',
                DB::raw('COALESCE(NULLIF(vm.Nama_Pengaju, ""), u.Name, vm.User_Trx) as Nama_Pengaju'),
                DB::raw('COALESCE(NULLIF(vm.Jabatan, ""), u.Position, "-") as Jabatan'),
                DB::raw('COALESCE(u.Department, vm.Department) as Dept_Pengaju')
            )
            ->orderBy('vm.created_at', 'DESC')
            ->get();

        if ($isAdmin) {
            return $rows;
        }

        return $rows->filter(function ($row) use ($userId) {
            return $this->approvalService->isActualCandidate((int) $row->Approval_Request_ID, $userId);
        })->values();
    }

    /**
     * Ambil timeline approval gabungan dari approval_log (engine) & vehicle_maintenance_approval (legacy/audit).
     */
    public function getApprovalTimeline(int $maintenanceId, int $approvalRequestId = 0): array
    {
        $items = [];

        if ($approvalRequestId > 0 && Schema::hasTable('approval_log')) {
            $logs = DB::table('approval_log as al')
                ->leftJoin('users as u', 'al.Approver_Id', '=', 'u.id')
                ->where('al.Request_Id', $approvalRequestId)
                ->select(
                    'al.*',
                    'u.Name as Actor_Name',
                    'u.username as Actor_Username',
                    'u.Department as Actor_Dept',
                    'u.Position as Actor_Position'
                )
                ->orderBy('al.ID', 'ASC')
                ->get();

            foreach ($logs as $log) {
                $items[] = [
                    'source'   => 'ENGINE',
                    'actor'    => $log->Actor_Name ?: ($log->Actor_Username ?: 'System'),
                    'dept'     => $log->Actor_Dept ?: '-',
                    'stage'    => $log->Stage_Order ? ('Stage ' . $log->Stage_Order) : '-',
                    'action'   => $log->Action,
                    'notes'    => $log->Notes ?: '',
                    'acted_at' => $log->Action_Date,
                ];
            }
        }

        if (empty($items) && Schema::hasTable('vehicle_maintenance_approval')) {
            $legacy = DB::table('vehicle_maintenance_approval')
                ->where('vehicle_maintenance_id', $maintenanceId)
                ->orderBy('approved_at', 'ASC')
                ->get();

            foreach ($legacy as $row) {
                $items[] = [
                    'source'   => 'LEGACY',
                    'actor'    => $row->approver_name ?: $row->approver_username,
                    'dept'     => $row->approver_dept ?: '-',
                    'stage'    => 'Level ' . ($row->level ?? '-'),
                    'action'   => $row->action,
                    'notes'    => $row->notes ?: '',
                    'acted_at' => $row->approved_at,
                ];
            }
        }

        return $items;
    }

    protected function submitToApprovalEngine(int $id, string $noTrx, ?object $asset, string $jenis, float $estimasi, int $userId, string $dept): void
    {
        $plat = $asset->Registration_No ?? '';
        $nama = $asset->Product_Description ?? ($asset->Asset_No ?? '-');
        $desc = sprintf(
            'Perawatan Kendaraan %s (%s%s) - %s',
            $nama,
            $asset->Asset_No ?? '-',
            $plat !== '' ? ' / ' . $plat : '',
            ucfirst($jenis)
        );

        $approvalReq = $this->approvalService->submit(
            AssetFleetApprovalService::TYPE_VEHICLE_MAINTENANCE,
            $id,
            $noTrx,
            $desc,
            $userId,
            [
                'Amount'     => $estimasi,
                'Department' => $dept,
            ]
        );

        DB::table('vehicle_maintenance')->where('id', $id)->update([
            'Approval_Request_ID' => (int) $approvalReq->ID,
        ]);

        $this->syncFromApprovalRequest((int) $approvalReq->ID);
    }

    protected function syncAssetOnCompleted(object $rec, string $endDate, int $effectiveKm, string $vendor, string $username): void
    {
        $asset = DB::table('asset_list')->where('Asset_No', $rec->Asset_No)->first();
        if (!$asset) {
            return;
        }

        $upd = [
            'Status'             => 'ACTIVE',
            'KM_Saat_Ini'        => max((int) ($asset->KM_Saat_Ini ?? 0), $effectiveKm),
            'Last_User'          => $username,
            'Last_Modified_Date' => now(),
        ];

        if ($rec->Jenis_Perawatan === 'preventif') {
            $upd['Tanggal_PM_Terakhir'] = $endDate;
            $upd['PM_Task_ID'] = $rec->No_Trx;
            $upd['Last_Reminder_Sent'] = null;

            $intervalBulan = (int) ($rec->Jadwal_Perawatan_Bulan ?? 0);
            if ($intervalBulan <= 0) {
                $intervalBulan = (int) ($asset->Interval_PM ?? 0);
            }
            if ($intervalBulan > 0) {
                $upd['Interval_PM'] = $intervalBulan;
                $upd['Tanggal_Jadwal_PM'] = Carbon::parse($endDate)->addMonths($intervalBulan)->format('Y-m-d');
            }

            if ($vendor !== '') {
                $upd['Repair_Vendor'] = substr($vendor, 0, 40);
            }

            $intervalKm = Schema::hasColumn('asset_list', 'Interval_KM_PM')
                ? (int) ($asset->Interval_KM_PM ?? 0)
                : 0;

            if ($intervalKm > 0 && $effectiveKm > 0) {
                $upd['KM_Jadwal_PM'] = $effectiveKm + $intervalKm;
            } else {
                $targetLama = (int) ($asset->KM_Jadwal_PM ?? 0);
                $kmSebelum = (int) ($asset->KM_Saat_Ini ?? 0);
                if ($targetLama > 0 && $effectiveKm > 0 && $effectiveKm >= $targetLama && $targetLama > $kmSebelum) {
                    $jarak = $targetLama - $kmSebelum;
                    $upd['KM_Jadwal_PM'] = $effectiveKm + $jarak;
                }
            }
        }

        DB::table('asset_list')->where('Asset_No', $rec->Asset_No)->update($upd);
    }

    protected function storeNotaFile(int $id, string $noTrx, UploadedFile $file): string
    {
        if (!$file->isValid()) {
            throw new \RuntimeException('File bukti nota gagal diunggah.');
        }

        $ext = strtolower((string) $file->getClientOriginalExtension());
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'pdf'], true)) {
            throw new \InvalidArgumentException("Format file {$ext} tidak diizinkan (hanya jpg/jpeg/png/webp/pdf).");
        }
        if ($file->getSize() > 5 * 1024 * 1024) {
            throw new \InvalidArgumentException('Ukuran file bukti nota maksimal 5 MB.');
        }

        $year = date('Y');
        $month = date('m');
        $targetDir = public_path("filevendor/{$year}/{$month}");
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $fileName = 'vm_nota_' . $id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $file->move($targetDir, $fileName);
        $relPath = "filevendor/{$year}/{$month}/{$fileName}";

        if (Schema::hasTable('meta_files')) {
            DB::table('meta_files')->insert([
                'Filename'    => $fileName,
                'Path'        => "{$year}/{$month}/{$fileName}",
                'Extension'   => $ext,
                'Description' => 'Bukti Nota Servis Kendaraan ' . $noTrx,
                'Topic'       => 'vehicle-maintenance-nota',
                'Map_To'      => $noTrx,
                'Created_At'  => now(),
            ]);
        }

        return $relPath;
    }

    protected function findVehicleAsset(string $key): ?object
    {
        $select = [
            'Asset_No',
            'Product_Code',
            'Product_Description',
            'SN',
            'Registration_No',
            'Asset_Type',
            'Asset_Type as Jenis_Kendaraan',
            'Asset_Location',
            'Asset_Location_Department',
            'KM_Saat_Ini',
            'KM_Jadwal_PM',
            'Interval_PM',
            'Tanggal_Jadwal_PM',
            'Tanggal_PM_Terakhir',
            'Repair_Vendor',
        ];
        if (Schema::hasColumn('asset_list', 'Interval_KM_PM')) {
            $select[] = 'Interval_KM_PM';
        }

        $asset = DB::table('asset_list')->where('Asset_No', $key)->select($select)->first();
        if (!$asset) {
            $asset = DB::table('asset_list')->where('Product_Code', $key)->select($select)->first();
        }

        return $asset;
    }

    protected function generateNoTrx(string $jenis): string
    {
        $prefix = 'VM/' . date('Ymd') . '/' . $jenis . '/';
        $count = DB::table('vehicle_maintenance')->where('No_Trx', 'LIKE', $prefix . '%')->count() + 1;
        return $prefix . date('His') . '-' . str_pad((string) $count, 3, '0', STR_PAD_LEFT);
    }

    protected function parseCurrency($value): float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }
        $clean = str_replace(['Rp', 'rp', '.', ',', ' '], ['', '', '', '', ''], (string) $value);
        return (float) preg_replace('/[^0-9.]/', '', $clean);
    }

    protected function parseInteger($value): int
    {
        $clean = preg_replace('/[^0-9]/', '', (string) $value);
        return (int) $clean;
    }
}
