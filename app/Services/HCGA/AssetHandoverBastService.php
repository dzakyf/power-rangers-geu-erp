<?php

namespace App\Services\HCGA;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Service terpusat pengelolaan dokumen BAST (Handover & Return)
 * dengan penyimpanan privat di storage/app/asset-handover-bast.
 */
class AssetHandoverBastService
{
    public const DOC_TYPE_HANDOVER = 'HANDOVER';
    public const DOC_TYPE_RETURN   = 'RETURN';

    public const STORAGE_DISK = 'local';
    public const STORAGE_ROOT = 'asset-handover-bast';

    public const ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
    public const ALLOWED_MIMES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/webp',
    ];
    public const MAX_FILE_BYTES = 10485760; // 10 MB

    public function normalizeDocType(?string $docType): string
    {
        $type = strtoupper(trim((string) $docType));
        if (!in_array($type, [self::DOC_TYPE_HANDOVER, self::DOC_TYPE_RETURN], true)) {
            throw new \InvalidArgumentException('Tipe dokumen BAST harus HANDOVER atau RETURN.');
        }

        return $type;
    }

    public function isAdminUser($user): bool
    {
        if (!$user) {
            return false;
        }
        $priv = strtoupper(trim((string) ($user->Priv_ID ?? '')));
        if (in_array($priv, ['HCGA', 'ICT'], true)) {
            return true;
        }
        if (method_exists($user, 'can')) {
            try {
                if ($user->can('approval.policy.manage')) {
                    return true;
                }
            } catch (\Throwable $e) {
                // Ignore if permission not registered
            }
        }

        return false;
    }

    public function canViewHandover($handover, $user): bool
    {
        if (!$handover || !$user) {
            return false;
        }
        if ($this->isAdminUser($user)) {
            return true;
        }

        $empId = trim((string) ($user->Emp_ID ?? ''));
        $username = trim((string) ($user->username ?? ''));
        if ($empId !== '') {
            $relatedNiks = array_filter([
                trim((string) ($handover->User_NIK ?? '')),
                trim((string) ($handover->Lead_NIK ?? '')),
                trim((string) ($handover->PIC_NIK ?? '')),
                trim((string) ($handover->Return_Lead_NIK ?? '')),
                trim((string) ($handover->Return_Receiver_NIK ?? '')),
            ]);
            if (in_array($empId, $relatedNiks, true)) {
                return true;
            }
        }

        if ($username !== '' && trim((string) ($handover->Created_By ?? '')) === $username) {
            return true;
        }

        $userId = (int) ($user->id ?? 0);
        if ($userId > 0) {
            $reqIds = array_values(array_filter([
                (int) ($handover->Approval_Request_ID ?? 0),
                (int) ($handover->Return_Approval_Request_ID ?? 0),
            ]));
            if (!empty($reqIds) && Schema::hasTable('approval_task_candidate')) {
                $isCandidate = DB::table('approval_request_task as t')
                    ->join('approval_task_candidate as c', 'c.Task_ID', '=', 't.ID')
                    ->whereIn('t.Request_ID', $reqIds)
                    ->where('c.User_Id', $userId)
                    ->where('c.Status', 'ACTIVE')
                    ->exists();
                if ($isCandidate) {
                    return true;
                }
            }
        }

        return false;
    }

    public function canDeleteDocument($handover, string $docType, $user): bool
    {
        if (!$this->canViewHandover($handover, $user)) {
            return false;
        }
        if ($this->isAdminUser($user)) {
            return true;
        }

        $status = strtoupper(trim((string) ($handover->Status ?? '')));
        if ($docType === self::DOC_TYPE_HANDOVER && in_array($status, ['APPROVED', 'RETURNED'], true)) {
            return false;
        }
        if ($docType === self::DOC_TYPE_RETURN && $status === 'RETURNED') {
            return false;
        }

        return true;
    }

    public function listDocuments(string $handoverNo, ?string $docType = null, $user = null): array
    {
        if (!Schema::hasTable('asset_handover_documents')) {
            return [];
        }

        $handover = DB::table('asset_handover')->where('Handover_No', $handoverNo)->first();

        $query = DB::table('asset_handover_documents')
            ->where('Handover_No', $handoverNo)
            ->whereNull('Deleted_At');

        if ($docType !== null && trim($docType) !== '') {
            $query->where('Doc_Type', $this->normalizeDocType($docType));
        }

        $rows = $query->orderBy('ID', 'DESC')->get();
        $result = [];

        foreach ($rows as $row) {
            $canDel = $user && $handover ? $this->canDeleteDocument($handover, (string) $row->Doc_Type, $user) : false;
            $result[] = [
                'id'            => (int) $row->ID,
                'handover_no'   => $row->Handover_No,
                'doc_type'      => $row->Doc_Type,
                'original_name' => $row->Original_Name,
                'extension'     => strtolower((string) $row->Extension),
                'mime_type'     => $row->Mime_Type,
                'file_size'     => (int) $row->File_Size,
                'file_size_fmt' => $this->formatBytes((int) $row->File_Size),
                'notes'         => $row->Notes,
                'uploaded_by'   => $row->Uploaded_By,
                'uploaded_at'   => $row->Uploaded_At,
                'can_delete'    => $canDel,
                'is_locked'     => !$canDel,
                'preview_url'   => url('hrd-asset-handover-bast-preview/' . (int) $row->ID),
                'download_url'  => url('hrd-asset-handover-bast-download/' . (int) $row->ID),
            ];
        }

        return $result;
    }

    public function getCountsByHandoverNos(array $handoverNos): array
    {
        $handoverNos = array_values(array_unique(array_filter($handoverNos)));
        if (empty($handoverNos) || !Schema::hasTable('asset_handover_documents')) {
            return [];
        }

        $rows = DB::table('asset_handover_documents')
            ->select('Handover_No', 'Doc_Type', DB::raw('COUNT(*) as total'))
            ->whereIn('Handover_No', $handoverNos)
            ->whereNull('Deleted_At')
            ->groupBy('Handover_No', 'Doc_Type')
            ->get();

        $counts = [];
        foreach ($rows as $row) {
            $no = (string) $row->Handover_No;
            $type = strtoupper((string) $row->Doc_Type);
            if (!isset($counts[$no])) {
                $counts[$no] = ['HANDOVER' => 0, 'RETURN' => 0];
            }
            $counts[$no][$type] = (int) $row->total;
        }

        return $counts;
    }

    public function hasBastDocument(string $handoverNo, string $docType): bool
    {
        if (!Schema::hasTable('asset_handover_documents')) {
            return false;
        }

        $type = $this->normalizeDocType($docType);

        return DB::table('asset_handover_documents')
            ->where('Handover_No', $handoverNo)
            ->where('Doc_Type', $type)
            ->whereNull('Deleted_At')
            ->exists();
    }

    public function assertHasBastForApproval(string $handoverNo, string $docType): void
    {
        // BAST utama kini digenerate otomatis oleh sistem (mPDF); lampiran fisik bersifat opsional.
        $this->normalizeDocType($docType);
    }

    /**
     * @param UploadedFile[] $files
     */
    public function uploadDocuments(string $handoverNo, string $docType, array $files, ?string $notes, $user): array
    {
        if (!Schema::hasTable('asset_handover_documents')) {
            throw new \RuntimeException('Tabel asset_handover_documents belum tersedia. Jalankan migration terlebih dahulu.');
        }

        $type = $this->normalizeDocType($docType);
        $handover = DB::table('asset_handover')->where('Handover_No', $handoverNo)->first();
        if (!$handover) {
            throw new \RuntimeException('Data Handover tidak ditemukan.');
        }

        if (!$this->canViewHandover($handover, $user)) {
            throw new \RuntimeException('Anda tidak memiliki izin mengunggah dokumen BAST untuk transaksi ini.');
        }

        $uploaded = [];
        $rejected = [];
        $username = (string) ($user->username ?? 'system');
        $subDir = self::STORAGE_ROOT . '/' . date('Y') . '/' . date('m');

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile || !$file->isValid()) {
                $name = $file instanceof UploadedFile ? $file->getClientOriginalName() : 'file';
                $rejected[] = "{$name} (gagal diunggah)";
                continue;
            }

            $origName = $file->getClientOriginalName();
            $ext = strtolower((string) $file->getClientOriginalExtension());
            $mime = strtolower((string) $file->getMimeType());
            $size = (int) $file->getSize();

            if (!in_array($ext, self::ALLOWED_EXTENSIONS, true) || ($mime !== '' && !in_array($mime, self::ALLOWED_MIMES, true))) {
                $rejected[] = "{$origName} (format tidak didukung; hanya PDF/JPG/PNG/WEBP)";
                continue;
            }

            if ($size <= 0 || $size > self::MAX_FILE_BYTES) {
                $rejected[] = "{$origName} (ukuran maksimal 10 MB)";
                continue;
            }

            $safeNo = preg_replace('/[^A-Za-z0-9_-]/', '_', $handoverNo);
            $storedName = 'bast_' . strtolower($type) . '_' . $safeNo . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $storedPath = Storage::disk(self::STORAGE_DISK)->putFileAs($subDir, $file, $storedName);

            $docId = DB::table('asset_handover_documents')->insertGetId([
                'Handover_No'   => $handoverNo,
                'Doc_Type'      => $type,
                'Original_Name' => mb_substr($origName, 0, 255),
                'Stored_Name'   => $storedName,
                'Storage_Path'  => $storedPath,
                'Mime_Type'     => $mime ?: null,
                'Extension'     => $ext,
                'File_Size'     => $size,
                'Notes'         => $notes !== null && trim($notes) !== '' ? mb_substr(trim($notes), 0, 500) : null,
                'Uploaded_By'   => $username,
                'Uploaded_At'   => now(),
            ]);

            $uploaded[] = [
                'id'            => (int) $docId,
                'original_name' => $origName,
                'doc_type'      => $type,
            ];
        }

        if (!empty($uploaded)) {
            $actionStatus = $type === self::DOC_TYPE_RETURN ? 'UPLOAD_BAST_RETURN' : 'UPLOAD_BAST_HANDOVER';
            $names = implode(', ', array_column($uploaded, 'original_name'));
            $this->writeAuditLog($handover, $username, $actionStatus, "Upload BAST ({$type}): {$names}");
        }

        return [
            'uploaded' => $uploaded,
            'rejected' => $rejected,
        ];
    }

    public function getAuthorizedDocument(int $docId, $user): object
    {
        $doc = DB::table('asset_handover_documents')
            ->where('ID', $docId)
            ->whereNull('Deleted_At')
            ->first();

        if (!$doc) {
            abort(404, 'Dokumen BAST tidak ditemukan.');
        }

        $handover = DB::table('asset_handover')->where('Handover_No', $doc->Handover_No)->first();
        if (!$this->canViewHandover($handover, $user)) {
            abort(403, 'Anda tidak berwenang mengakses dokumen BAST ini.');
        }

        if (empty($doc->Storage_Path) || !Storage::disk(self::STORAGE_DISK)->exists($doc->Storage_Path)) {
            abort(404, 'Berkas fisik dokumen BAST tidak ditemukan di server.');
        }

        return $doc;
    }

    public function streamDocument(int $docId, $user, bool $download = false)
    {
        $doc = $this->getAuthorizedDocument($docId, $user);
        $fullPath = Storage::disk(self::STORAGE_DISK)->path($doc->Storage_Path);
        $safeName = str_replace('"', '', (string) ($doc->Original_Name ?: $doc->Stored_Name));

        if ($download) {
            return response()->download($fullPath, $safeName);
        }

        $headers = [
            'Content-Disposition' => 'inline; filename="' . $safeName . '"',
        ];
        if (!empty($doc->Mime_Type)) {
            $headers['Content-Type'] = $doc->Mime_Type;
        }

        return response()->file($fullPath, $headers);
    }

    public function deleteDocument(int $docId, $user): void
    {
        $doc = DB::table('asset_handover_documents')
            ->where('ID', $docId)
            ->whereNull('Deleted_At')
            ->first();

        if (!$doc) {
            throw new \RuntimeException('Dokumen BAST tidak ditemukan.');
        }

        $handover = DB::table('asset_handover')->where('Handover_No', $doc->Handover_No)->first();
        if (!$handover) {
            throw new \RuntimeException('Data Handover tidak ditemukan.');
        }

        if (!$this->canDeleteDocument($handover, (string) $doc->Doc_Type, $user)) {
            throw new \RuntimeException('Dokumen BAST sudah terkunci pasca-approval. Hanya admin HCGA/ICT yang dapat menghapusnya.');
        }

        $username = (string) ($user->username ?? 'system');

        DB::transaction(function () use ($doc, $handover, $username) {
            DB::table('asset_handover_documents')
                ->where('ID', $doc->ID)
                ->update([
                    'Deleted_By' => $username,
                    'Deleted_At' => now(),
                ]);

            $actionStatus = $doc->Doc_Type === self::DOC_TYPE_RETURN ? 'DELETE_BAST_RETURN' : 'DELETE_BAST_HANDOVER';
            $this->writeAuditLog($handover, $username, $actionStatus, "Hapus BAST ({$doc->Doc_Type}): {$doc->Original_Name}");
        });

        if (!empty($doc->Storage_Path) && Storage::disk(self::STORAGE_DISK)->exists($doc->Storage_Path)) {
            Storage::disk(self::STORAGE_DISK)->delete($doc->Storage_Path);
        }
    }

    public function writeAuditLog(object $handover, string $actor, string $actionStatus, string $remarkNote = ''): void
    {
        if (!Schema::hasTable('asset_handover_log')) {
            return;
        }

        try {
            DB::table('asset_handover_log')->insert([
                'Handover_No'    => $handover->Handover_No,
                'Action_By'      => $actor,
                'Action_Date'    => now(),
                'Action_Status'  => $actionStatus,
                'Handover_Date'  => $handover->Handover_Date ?? null,
                'Status'         => $handover->Status ?? null,
                'User_Name'      => $handover->User_Name ?? null,
                'User_NIK'       => $handover->User_NIK ?? null,
                'PIC_Name'       => $handover->PIC_Name ?? null,
                'PIC_Department' => $handover->PIC_Department ?? null,
                'PIC_Phone'      => $handover->PIC_Phone ?? null,
                'Reference'      => $handover->Reference ?? null,
                'Remark'         => $remarkNote !== '' ? $remarkNote : ($handover->Remark ?? null),
                'Created_By'     => $handover->Created_By ?? $actor,
                'Created_Date'   => $handover->Created_Date ?? now(),
                'Updated_By'     => $actor,
                'Updated_Date'   => now(),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('AssetHandoverBastService: gagal menulis asset_handover_log: ' . $e->getMessage());
        }
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }

        return $bytes . ' B';
    }
}
