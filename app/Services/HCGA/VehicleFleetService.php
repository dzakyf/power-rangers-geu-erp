<?php

namespace App\Services\HCGA;

use App\Services\Approval\AssetFleetApprovalService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Service untuk alur Vehicle Fleet Management (Vehicle-Fleet-Management.drawio.png)
 * terintegrasi dengan ApprovalWorkflowService:
 * 1. Approval Data Kendaraan (Operasional / Direksi / Pool -> Data Aktif)
 * 2. Pengajuan Pajak/STNK -> ApprovalWorkflowService -> Voucher Pajak -> Bayar Pajak
 * 3. Approval Logbook & Fuel Tracking (ApprovalWorkflowService -> Data Valid)
 */
class VehicleFleetService
{
    /** @var AssetVehicleService */
    protected $assetVehicleService;

    /** @var AssetFleetApprovalService */
    protected $approvalService;

    public function __construct(
        AssetVehicleService $assetVehicleService,
        AssetFleetApprovalService $approvalService
    ) {
        $this->assetVehicleService = $assetVehicleService;
        $this->approvalService = $approvalService;
    }

    public function listTaxRequests(?int $currentUserId = null, ?string $currentUsername = null)
    {
        $rows = DB::table('vehicle_tax_request as t')
            ->leftJoin('asset_list as a', 't.Asset_No', '=', 'a.Asset_No')
            ->select('t.*', 'a.Registration_No', 'a.Product_Description', 'a.Vehicle_Usage_Status')
            ->orderBy('t.ID', 'DESC')
            ->get();

        $isAdminUser = strtolower(trim((string) $currentUsername)) === 'admin';

        foreach ($rows as $row) {
            $isWaitingApproval = $row->Status === 'APPROVAL_HCGA' && !empty($row->Approval_Request_ID);
            $row->can_approve = $isWaitingApproval && $currentUserId
                ? $this->approvalService->isActualCandidate((int) $row->Approval_Request_ID, $currentUserId)
                : false;
            $row->can_force_approve = $isWaitingApproval && $isAdminUser;
        }

        return $rows;
    }

    public function createTaxRequest(array $payload, string $username, int $requesterId = 0): object
    {
        $assetNo = trim((string) ($payload['Asset_No'] ?? ''));
        $jenis = strtoupper(trim((string) ($payload['Jenis_Pajak'] ?? 'STNK_TAHUNAN')));

        if ($assetNo === '') {
            throw new \InvalidArgumentException('Asset_No kendaraan wajib diisi.');
        }
        if (!in_array($jenis, ['STNK_TAHUNAN', 'STNK_5_TAHUNAN', 'KEUR'], true)) {
            throw new \InvalidArgumentException('Jenis pajak tidak valid.');
        }

        $duplicate = DB::table('vehicle_tax_request')
            ->where('Asset_No', $assetNo)
            ->where('Jenis_Pajak', $jenis)
            ->whereIn('Status', ['APPROVAL_HCGA', 'RETURNED', 'FINANCE_SUBMITTED'])
            ->exists();
        if ($duplicate) {
            throw new \RuntimeException("Pengajuan {$jenis} untuk kendaraan {$assetNo} masih aktif dan belum selesai diproses.");
        }

        $requesterId = $this->resolveUserId($requesterId, $username);
        $seq = DB::table('vehicle_tax_request')->count() + 1;
        $taxNo = 'TAX/' . date('Ym') . '-' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
        $estimasi = (float) ($payload['Estimasi_Biaya'] ?? 0);

        return DB::transaction(function () use ($taxNo, $assetNo, $jenis, $payload, $estimasi, $username, $requesterId) {
            $id = DB::table('vehicle_tax_request')->insertGetId([
                'Tax_Request_No'      => $taxNo,
                'Asset_No'            => $assetNo,
                'Jenis_Pajak'         => $jenis,
                'Tanggal_Jatuh_Tempo' => $payload['Tanggal_Jatuh_Tempo'] ?? null,
                'Estimasi_Biaya'      => $estimasi,
                'Status'              => 'APPROVAL_HCGA',
                'Created_By'          => $username,
                'Created_At'          => now(),
                'Updated_At'          => now(),
            ]);

            $approvalReq = $this->approvalService->submit(
                AssetFleetApprovalService::TYPE_VEHICLE_TAX,
                $id,
                $taxNo,
                "Pengajuan Pajak Kendaraan {$assetNo} ({$jenis})",
                $requesterId,
                ['Amount' => $estimasi]
            );

            DB::table('vehicle_tax_request')->where('ID', $id)->update([
                'Approval_Request_ID' => (int) $approvalReq->ID,
            ]);

            $this->approvalService->syncStatus(AssetFleetApprovalService::TYPE_VEHICLE_TAX, (int) $approvalReq->ID);

            return DB::table('vehicle_tax_request')->where('ID', $id)->first();
        });
    }

    public function approveTaxRequest(int $id, string $action, string $username, ?string $note = null, int $actorId = 0): object
    {
        $req = DB::table('vehicle_tax_request')->where('ID', $id)->first();
        if (!$req) {
            throw new \RuntimeException('Pengajuan pajak tidak ditemukan.');
        }
        if (empty($req->Approval_Request_ID)) {
            throw new \RuntimeException('Pengajuan pajak belum terhubung ke ApprovalWorkflowService.');
        }

        $actorId = $this->resolveUserId($actorId, $username);
        $this->approvalService->actOnRequest((int) $req->Approval_Request_ID, $actorId, $action, (string) ($note ?? ''));

        return DB::table('vehicle_tax_request')->where('ID', $id)->first();
    }

    public function forceApproveTaxRequest(int $id, int $adminId, string $reason): object
    {
        $req = DB::table('vehicle_tax_request')->where('ID', $id)->first();
        if (!$req) {
            throw new \RuntimeException('Pengajuan pajak tidak ditemukan.');
        }
        if (empty($req->Approval_Request_ID)) {
            throw new \RuntimeException('Pengajuan pajak belum terhubung ke ApprovalWorkflowService.');
        }

        $this->approvalService->forceApprove((int) $req->Approval_Request_ID, $adminId, $reason);

        return DB::table('vehicle_tax_request')->where('ID', $id)->first();
    }

    public function resubmitTaxRequest(int $id, array $payload, string $username, int $userId = 0): object
    {
        $req = DB::table('vehicle_tax_request')->where('ID', $id)->first();
        if (!$req) {
            throw new \RuntimeException('Pengajuan pajak tidak ditemukan.');
        }
        if (!in_array($req->Status, ['RETURNED', 'REJECTED'], true)) {
            throw new \RuntimeException('Hanya pengajuan berstatus RETURNED atau REJECTED yang dapat diajukan ulang.');
        }
        if (empty($req->Approval_Request_ID)) {
            throw new \RuntimeException('Pengajuan pajak belum memiliki Approval_Request_ID.');
        }

        $userId = $this->resolveUserId($userId, $username);

        return DB::transaction(function () use ($id, $req, $payload, $userId) {
            $update = ['Status' => 'APPROVAL_HCGA', 'Updated_At' => now()];
            foreach (['Jenis_Pajak', 'Tanggal_Jatuh_Tempo', 'Estimasi_Biaya'] as $col) {
                if (array_key_exists($col, $payload)) {
                    $update[$col] = $payload[$col];
                }
            }
            DB::table('vehicle_tax_request')->where('ID', $id)->update($update);

            $newReq = $this->approvalService->resubmit(
                (int) $req->Approval_Request_ID,
                $id,
                $userId,
                ['Amount' => (float) ($update['Estimasi_Biaya'] ?? $req->Estimasi_Biaya)]
            );

            DB::table('vehicle_tax_request')->where('ID', $id)->update([
                'Approval_Request_ID' => (int) $newReq->ID,
            ]);

            $this->approvalService->syncStatus(AssetFleetApprovalService::TYPE_VEHICLE_TAX, (int) $newReq->ID);

            return DB::table('vehicle_tax_request')->where('ID', $id)->first();
        });
    }

    public function markTaxPaid(
        int $id,
        string $paidDate,
        string $newDueDate,
        string $username,
        ?string $bukti = null,
        ?UploadedFile $buktiFile = null
    ): object {
        return DB::transaction(function () use ($id, $paidDate, $newDueDate, $username, $bukti, $buktiFile) {
            $req = DB::table('vehicle_tax_request')->where('ID', $id)->lockForUpdate()->first();
            if (!$req) {
                throw new \RuntimeException('Pengajuan pajak tidak ditemukan.');
            }

            $buktiValue = $bukti;
            if ($buktiFile instanceof UploadedFile) {
                if (!$buktiFile->isValid()) {
                    throw new \RuntimeException('File bukti bayar gagal diunggah.');
                }
                $ext = strtolower($buktiFile->getClientOriginalExtension());
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'pdf'], true)) {
                    throw new \InvalidArgumentException("Format file {$ext} tidak diizinkan (hanya jpg/jpeg/png/webp/pdf).");
                }
                if ($buktiFile->getSize() > 5 * 1024 * 1024) {
                    throw new \InvalidArgumentException('Ukuran file bukti bayar maksimal 5 MB.');
                }

                $year = date('Y');
                $month = date('m');
                $targetDir = public_path("filevendor/{$year}/{$month}");
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
                $newName = 'tax_receipt_' . $id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $buktiFile->move($targetDir, $newName);
                $relPath = "{$year}/{$month}/{$newName}";

                DB::table('meta_files')->insert([
                    'Filename'    => $newName,
                    'Path'        => $relPath,
                    'Extension'   => $ext,
                    'Description' => 'Bukti Bayar Pajak STNK/KEUR ' . $req->Tax_Request_No,
                    'Topic'       => 'vehicle-tax-receipt',
                    'Map_To'      => $req->Tax_Request_No,
                    'Created_At'  => now(),
                ]);

                $buktiValue = $relPath;
            }

            DB::table('vehicle_tax_request')->where('ID', $id)->update([
                'Status'        => 'PAID',
                'Tanggal_Bayar' => $paidDate,
                'Bukti_Bayar'   => $buktiValue,
                'Updated_At'    => now(),
            ]);

            if ($req->Jenis_Pajak === 'KEUR') {
                $this->assetVehicleService->updateKeur($req->Asset_No, $paidDate, $buktiValue, 'Pembayaran pajak/KEUR ' . $req->Tax_Request_No, $username);
            } else {
                $jenisStnk = $req->Jenis_Pajak === 'STNK_5_TAHUNAN' ? '5_TAHUNAN' : 'TAHUNAN';
                $this->assetVehicleService->updateStnk($req->Asset_No, $jenisStnk, $newDueDate, 'Pembayaran pajak ' . $req->Tax_Request_No, $username);
                DB::table('asset_list')->where('Asset_No', $req->Asset_No)->update([
                    'Tanggal_Berlaku_Pajak' => $newDueDate,
                    'Status'                => 'ACTIVE',
                ]);
            }

            return DB::table('vehicle_tax_request')->where('ID', $id)->first();
        });
    }

    public function getTaxRequestForVoucher(int $id): ?array
    {
        $req = DB::table('vehicle_tax_request as t')
            ->leftJoin('asset_list as a', 't.Asset_No', '=', 'a.Asset_No')
            ->where('t.ID', $id)
            ->select(
                't.*',
                'a.Registration_No',
                'a.Product_Description',
                'a.Product_Brand',
                'a.Model',
                'a.Asset_Location',
                'a.Asset_Location_Department',
                'a.User_Responsible'
            )
            ->first();

        if (!$req) {
            return null;
        }

        $approvalLogs = [];
        if (!empty($req->Approval_Request_ID)) {
            $approvalLogs = DB::table('approval_log as l')
                ->leftJoin('users as u', 'l.Approver_Id', '=', 'u.id')
                ->where('l.Request_Id', (int) $req->Approval_Request_ID)
                ->select('l.*', 'u.Name as Actor_Name', 'u.username as Actor_Username')
                ->orderBy('l.ID', 'ASC')
                ->get();
        }

        return [
            'taxRequest'   => $req,
            'approvalLogs' => $approvalLogs,
        ];
    }

    public function submitFleetVehicle(string $assetNo, string $username, int $requesterId = 0): object
    {
        $asset = DB::table('asset_list')->where('Asset_No', $assetNo)->first();
        if (!$asset) {
            throw new \RuntimeException("Kendaraan {$assetNo} tidak ditemukan.");
        }

        $requesterId = $this->resolveUserId($requesterId, $username);
        $approvalReq = $this->approvalService->submit(
            AssetFleetApprovalService::TYPE_VEHICLE_FLEET,
            (int) $asset->ID,
            $asset->Asset_No,
            "Approval Data Kendaraan Fleet {$asset->Asset_No} ({$asset->Registration_No})",
            $requesterId
        );

        DB::table('asset_list')->where('ID', $asset->ID)->update([
            'Fleet_Approval_Status'     => 'APPROVAL_HCGA',
            'Fleet_Approval_Request_ID' => (int) $approvalReq->ID,
        ]);

        return $approvalReq;
    }

    public function approveFleetVehicle(string $assetNo, string $action, string $username, int $actorId = 0, ?string $note = null): void
    {
        $asset = DB::table('asset_list')->where('Asset_No', $assetNo)->first();
        if (!$asset) {
            throw new \RuntimeException("Kendaraan {$assetNo} tidak ditemukan.");
        }

        if (!empty($asset->Fleet_Approval_Request_ID)) {
            $actorId = $this->resolveUserId($actorId, $username);
            $this->approvalService->actOnRequest((int) $asset->Fleet_Approval_Request_ID, $actorId, $action, (string) ($note ?? ''));
            return;
        }

        $action = strtoupper(trim($action));
        DB::table('asset_list')->where('Asset_No', $assetNo)->update([
            'Fleet_Approval_Status' => $action === 'APPROVE' ? 'APPROVED' : 'RETURNED',
            'Status'                => $action === 'APPROVE' ? 'ACTIVE' : 'INACTIVE',
            'Last_User'             => $username,
            'Last_Modified_Date'    => now(),
        ]);
    }

    public function submitLogbook(int $logbookId, string $username, int $requesterId = 0): object
    {
        $log = DB::table('vehicle_logbook')->where('id', $logbookId)->first();
        if (!$log) {
            throw new \RuntimeException('Logbook tidak ditemukan.');
        }

        $requesterId = $this->resolveUserId($requesterId, $username);
        $approvalReq = $this->approvalService->submit(
            AssetFleetApprovalService::TYPE_VEHICLE_LOGBOOK,
            (int) $logbookId,
            $log->Log_No ?? ('LOG-' . $logbookId),
            "Approval Logbook Kendaraan {$log->Asset_No}",
            $requesterId
        );

        DB::table('vehicle_logbook')->where('id', $logbookId)->update([
            'Approval_Status'     => 'PENDING',
            'Approval_Request_ID' => (int) $approvalReq->ID,
        ]);

        return $approvalReq;
    }

    public function approveLogbook(int $logbookId, string $action, string $username, ?string $note = null, int $actorId = 0): void
    {
        $action = strtoupper(trim($action));
        if (!in_array($action, ['APPROVE', 'REJECT'], true)) {
            throw new \InvalidArgumentException('Action harus APPROVE atau REJECT.');
        }

        $log = DB::table('vehicle_logbook')->where('id', $logbookId)->first();
        if (!$log) {
            throw new \RuntimeException('Logbook tidak ditemukan.');
        }

        if (!empty($log->Approval_Request_ID)) {
            $actorId = $this->resolveUserId($actorId, $username);
            $this->approvalService->actOnRequest((int) $log->Approval_Request_ID, $actorId, $action, (string) ($note ?? ''));
            return;
        }

        DB::table('vehicle_logbook')->where('id', $logbookId)->update([
            'Approval_Status' => $action === 'APPROVE' ? 'APPROVED' : 'RETURNED',
            'Approved_By'     => $username,
            'Approved_At'     => now(),
            'Approval_Note'   => $note,
        ]);
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
}
