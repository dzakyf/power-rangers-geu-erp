<?php

namespace App\Services\HCGA;

use App\HcgaNewsAnnouncement;
use App\Models\HCGA\StorageDocument;
use App\Models\HCGA\StorageDocumentVersion;
use App\Models\HCGA\StorageReadReceipt;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StorageManagementService
{
    const CONFIG_REGULATION_APPROVAL = 'STORAGE_APPROVAL_MODE_REGULATION';
    const CONFIG_INFORMATION_APPROVAL = 'STORAGE_APPROVAL_MODE_INFORMATION';

    const APPROVAL_DIRECT = 'DIRECT';
    const APPROVAL_OPTIONAL = 'OPTIONAL';
    const APPROVAL_REQUIRED = 'REQUIRED';

    /**
     * Get configured approval modes from config_system.
     */
    public function getApprovalSettings()
    {
        $reg = DB::table('config_system')->where('Constraint', self::CONFIG_REGULATION_APPROVAL)->value('Value');
        $info = DB::table('config_system')->where('Constraint', self::CONFIG_INFORMATION_APPROVAL)->value('Value');

        return [
            StorageDocument::MODULE_REGULATION  => $this->normalizeApprovalMode($reg, self::APPROVAL_OPTIONAL),
            StorageDocument::MODULE_INFORMATION => $this->normalizeApprovalMode($info, self::APPROVAL_DIRECT),
        ];
    }

    public function getApprovalMode($moduleType)
    {
        $settings = $this->getApprovalSettings();
        return $settings[$moduleType] ?? self::APPROVAL_OPTIONAL;
    }

    public function saveApprovalSettings(array $data)
    {
        $reg = $this->normalizeApprovalMode($data['regulation_mode'] ?? null, self::APPROVAL_OPTIONAL);
        $info = $this->normalizeApprovalMode($data['information_mode'] ?? null, self::APPROVAL_DIRECT);

        foreach ([
            self::CONFIG_REGULATION_APPROVAL  => $this->approvalModeToNumeric($reg),
            self::CONFIG_INFORMATION_APPROVAL => $this->approvalModeToNumeric($info),
        ] as $constraint => $value) {
            DB::table('config_system')->updateOrInsert(
                ['Constraint' => $constraint],
                ['Value' => $value]
            );
        }

        return $this->getApprovalSettings();
    }

    protected function approvalModeToNumeric($mode)
    {
        if ($mode === self::APPROVAL_REQUIRED) {
            return 2;
        }
        if ($mode === self::APPROVAL_OPTIONAL) {
            return 1;
        }
        return 0;
    }

    protected function normalizeApprovalMode($mode, $default)
    {
        if ($mode === null || $mode === '') {
            return $default;
        }

        if (is_numeric($mode)) {
            $intVal = (int) round((float) $mode);
            if ($intVal === 2) {
                return self::APPROVAL_REQUIRED;
            }
            if ($intVal === 1) {
                return self::APPROVAL_OPTIONAL;
            }
            if ($intVal === 0) {
                return self::APPROVAL_DIRECT;
            }
        }

        $upper = strtoupper(trim((string) $mode));
        if (in_array($upper, [self::APPROVAL_DIRECT, self::APPROVAL_OPTIONAL, self::APPROVAL_REQUIRED], true)) {
            return $upper;
        }
        return $default;
    }

    /**
     * Resolve effective submission action against configured approval mode.
     */
    public function resolveSubmissionAction($moduleType, $requestedAction)
    {
        $requested = strtoupper(trim((string) ($requestedAction ?: 'PUBLISH')));
        if ($requested === 'DRAFT') {
            return 'DRAFT';
        }

        $mode = $this->getApprovalMode($moduleType);
        if ($mode === self::APPROVAL_REQUIRED) {
            return 'SUBMIT_APPROVAL';
        }
        if ($mode === self::APPROVAL_DIRECT) {
            return 'PUBLISH';
        }

        return $requested === 'SUBMIT_APPROVAL' ? 'SUBMIT_APPROVAL' : 'PUBLISH';
    }

    /**
     * List available departments from employment and master_position.
     */
    public function getAvailableDepartments()
    {
        $empDepts = DB::table('employment')
            ->whereNotNull('Department')
            ->where('Department', '!=', '')
            ->distinct()
            ->pluck('Department')
            ->all();

        $posDepts = [];
        if (DB::getSchemaBuilder()->hasTable('master_position')) {
            $posDepts = DB::table('master_position')
                ->whereNotNull('Department')
                ->where('Department', '!=', '')
                ->distinct()
                ->pluck('Department')
                ->all();
        }

        $merged = array_unique(array_filter(array_map(function ($d) {
            return strtoupper(trim((string) $d));
        }, array_merge($empDepts, $posDepts))));

        sort($merged);
        return array_values($merged);
    }

    /**
     * Summary counts for KPI cards on Storage index page.
     */
    public function getSummaryStats($moduleType)
    {
        $docs = StorageDocument::with('currentVersion')
            ->where('module_type', $moduleType)
            ->get();

        $stats = [
            'total'            => $docs->count(),
            'active'           => 0,
            'scheduled'        => 0,
            'expired'          => 0,
            'waiting_approval' => 0,
            'draft'            => 0,
            'revoked'          => 0,
        ];

        foreach ($docs as $doc) {
            $st = $doc->computeEffectiveStatus();
            if ($st === 'ACTIVE') {
                $stats['active']++;
            } elseif ($st === 'SCHEDULED') {
                $stats['scheduled']++;
            } elseif ($st === 'EXPIRED') {
                $stats['expired']++;
            } elseif ($st === 'WAITING_APPROVAL') {
                $stats['waiting_approval']++;
            } elseif ($st === 'REVOKED') {
                $stats['revoked']++;
            } else {
                $stats['draft']++;
            }
        }

        return $stats;
    }

    /**
     * Query documents for dhtmlXGrid XML feed.
     */
    public function getGridList($moduleType, Request $request)
    {
        $query = StorageDocument::with('currentVersion')
            ->where('module_type', $moduleType);

        if ($request->filled('sub_type')) {
            $query->where('sub_type', $request->input('sub_type'));
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('document_number', 'LIKE', "%{$search}%")
                  ->orWhere('owner_department', 'LIKE', "%{$search}%");
            });
        }

        $items = $query->orderBy('id', 'DESC')->get();

        $filterStatus = strtoupper(trim((string) $request->input('effective_status', '')));
        if ($filterStatus !== '') {
            $items = $items->filter(function ($doc) use ($filterStatus) {
                return $doc->computeEffectiveStatus() === $filterStatus;
            })->values();
        }

        $filterAudience = strtoupper(trim((string) $request->input('target_audience', '')));
        if ($filterAudience !== '') {
            $items = $items->filter(function ($doc) use ($filterAudience) {
                $ver = $doc->currentVersion;
                return $ver && $ver->target_audience === $filterAudience;
            })->values();
        }

        return $items;
    }

    /**
     * Get single document with all versions, read receipts summary, and approval logs.
     */
    public function getDocumentDetail($id)
    {
        $doc = StorageDocument::with(['versions.readReceipts', 'currentVersion', 'announcements'])->findOrFail($id);
        return $doc;
    }

    /**
     * Create a brand new document + version v1.
     */
    public function createDocument($moduleType, Request $request, $user = null)
    {
        $user = $user ?: Auth::user();
        $username = $user ? ($user->username ?? 'system') : 'system';
        $action = $this->resolveSubmissionAction($moduleType, $request->input('submit_action', 'PUBLISH'));

        return DB::transaction(function () use ($moduleType, $request, $user, $username, $action) {
            $now = Carbon::now();
            $docNumber = trim((string) $request->input('document_number', '')) ?: null;

            $doc = new StorageDocument();
            $doc->module_type = $moduleType;
            $doc->sub_type = $request->input('sub_type');
            $doc->document_number = $docNumber;
            $doc->title = $request->input('title');
            $doc->owner_department = $request->input('owner_department') ?: ($user->Department ?? null);
            $doc->current_version_number = 1;
            $doc->status = $action === 'DRAFT' ? StorageDocument::STATUS_DRAFT : StorageDocument::STATUS_WAITING_APPROVAL;
            $doc->created_by = $username;
            $doc->created_date = $now;
            $doc->save();

            $version = $this->buildVersionFromRequest($doc, 1, $request, $username, $now);
            $version->version_status = $action === 'DRAFT'
                ? StorageDocumentVersion::STATUS_DRAFT
                : StorageDocumentVersion::STATUS_WAITING_APPROVAL;
            $version->save();

            $doc->current_version_id = $version->id;
            $doc->save();

            if ($action === 'PUBLISH') {
                $this->finalizeVersionPublication($version, $username);
            } elseif ($action === 'SUBMIT_APPROVAL') {
                app(StorageApprovalService::class)->submitVersionForApproval($version, $user);
            }

            return $doc->fresh(['currentVersion', 'versions']);
        });
    }

    /**
     * Update an existing draft/rejected version or update metadata on a published document.
     */
    public function updateDocument($documentId, Request $request, $user = null)
    {
        $user = $user ?: Auth::user();
        $username = $user ? ($user->username ?? 'system') : 'system';

        return DB::transaction(function () use ($documentId, $request, $user, $username) {
            $doc = StorageDocument::with('currentVersion')->findOrFail($documentId);
            $version = $doc->currentVersion;

            if (!$version) {
                throw new \RuntimeException('Versi dokumen tidak ditemukan.');
            }

            $now = Carbon::now();
            $docNumber = trim((string) $request->input('document_number', '')) ?: null;

            $doc->sub_type = $request->input('sub_type', $doc->sub_type);
            $doc->document_number = $docNumber;
            $doc->title = $request->input('title', $doc->title);
            $doc->owner_department = $request->input('owner_department', $doc->owner_department);
            $doc->updated_by = $username;
            $doc->updated_date = $now;
            $doc->save();

            $this->applyRequestToVersion($doc, $version, $request, $username, $now);

            $isDraftOrRejected = in_array($version->version_status, [
                StorageDocumentVersion::STATUS_DRAFT,
                StorageDocumentVersion::STATUS_REJECTED,
            ], true);

            if ($isDraftOrRejected) {
                $action = $this->resolveSubmissionAction($doc->module_type, $request->input('submit_action', 'DRAFT'));
                if ($action === 'PUBLISH') {
                    $version->save();
                    $this->finalizeVersionPublication($version, $username);
                } elseif ($action === 'SUBMIT_APPROVAL') {
                    $version->version_status = StorageDocumentVersion::STATUS_WAITING_APPROVAL;
                    $version->save();
                    $doc->status = StorageDocument::STATUS_WAITING_APPROVAL;
                    $doc->save();
                    app(StorageApprovalService::class)->submitVersionForApproval($version, $user);
                } else {
                    $version->version_status = StorageDocumentVersion::STATUS_DRAFT;
                    $version->save();
                    $doc->status = StorageDocument::STATUS_DRAFT;
                    $doc->save();
                }
            } else {
                $version->save();
                if ($version->version_status === StorageDocumentVersion::STATUS_ACTIVE) {
                    if ((int) $version->publish_to_mobile === 1) {
                        $this->syncVersionToAnnouncement($version, $username);
                    } else {
                        $this->unpublishLinkedAnnouncements($doc->id, $username);
                    }
                }
            }

            return $doc->fresh(['currentVersion', 'versions']);
        });
    }

    /**
     * Create a new revision version (v2, v3, etc.) for an existing document.
     */
    public function createNewVersion($documentId, Request $request, $user = null)
    {
        $user = $user ?: Auth::user();
        $username = $user ? ($user->username ?? 'system') : 'system';

        return DB::transaction(function () use ($documentId, $request, $user, $username) {
            $doc = StorageDocument::with('currentVersion')->findOrFail($documentId);
            $action = $this->resolveSubmissionAction($doc->module_type, $request->input('submit_action', 'PUBLISH'));
            $now = Carbon::now();

            $maxVersion = (int) StorageDocumentVersion::where('document_id', $doc->id)->max('version_number');
            $nextNumber = $maxVersion + 1;

            $prevVersion = $doc->currentVersion;

            // Update document header info if supplied on revision
            if ($request->filled('document_number')) {
                $doc->document_number = trim((string) $request->input('document_number'));
            }
            if ($request->filled('sub_type')) {
                $doc->sub_type = $request->input('sub_type');
            }
            if ($request->filled('owner_department')) {
                $doc->owner_department = $request->input('owner_department');
            }
            $doc->updated_by = $username;
            $doc->updated_date = $now;
            $doc->save();

            $version = $this->buildVersionFromRequest($doc, $nextNumber, $request, $username, $now, $prevVersion);
            $version->changelog = trim((string) $request->input('changelog', ''));
            $version->previous_version_visibility = $request->input('previous_version_visibility', StorageDocumentVersion::VISIBILITY_ADMIN_ONLY);
            $version->announcement_sync_mode = $request->input('announcement_sync_mode', StorageDocumentVersion::SYNC_UPDATE_EXISTING);
            $version->archive_old_announcement = $request->has('archive_old_announcement')
                ? ($request->input('archive_old_announcement') == '1' ? 1 : 0)
                : 1;
            $version->version_status = $action === 'DRAFT'
                ? StorageDocumentVersion::STATUS_DRAFT
                : StorageDocumentVersion::STATUS_WAITING_APPROVAL;
            $version->save();

            if ($action === 'PUBLISH') {
                $this->finalizeVersionPublication($version, $username);
            } elseif ($action === 'SUBMIT_APPROVAL') {
                // Keep existing active version live until approved, only point document status if no active version exists
                if ($doc->status !== StorageDocument::STATUS_PUBLISHED) {
                    $doc->status = StorageDocument::STATUS_WAITING_APPROVAL;
                    $doc->save();
                }
                app(StorageApprovalService::class)->submitVersionForApproval($version, $user);
            } else {
                if ($doc->status !== StorageDocument::STATUS_PUBLISHED) {
                    $doc->current_version_id = $version->id;
                    $doc->current_version_number = $version->version_number;
                    $doc->save();
                }
            }

            return $version->fresh(['document']);
        });
    }

    /**
     * Activate a version (called by Direct Publish or Approval Callback).
     */
    public function finalizeVersionPublication(StorageDocumentVersion $version, $actor = 'system')
    {
        return DB::transaction(function () use ($version, $actor) {
            $version = StorageDocumentVersion::lockForUpdate()->findOrFail($version->id);
            $doc = StorageDocument::lockForUpdate()->findOrFail($version->document_id);

            $now = Carbon::now();

            // Supersede prior ACTIVE versions and apply selected visibility
            $prevVisibility = in_array($version->previous_version_visibility, [
                StorageDocumentVersion::VISIBILITY_ADMIN_ONLY,
                StorageDocumentVersion::VISIBILITY_EMPLOYEE_VISIBLE,
            ], true) ? $version->previous_version_visibility : StorageDocumentVersion::VISIBILITY_ADMIN_ONLY;

            StorageDocumentVersion::where('document_id', $doc->id)
                ->where('id', '!=', $version->id)
                ->where('version_status', StorageDocumentVersion::STATUS_ACTIVE)
                ->update([
                    'version_status'     => StorageDocumentVersion::STATUS_SUPERSEDED,
                    'history_visibility' => $prevVisibility,
                    'updated_by'         => $actor,
                    'updated_date'       => $now,
                ]);

            $version->version_status = StorageDocumentVersion::STATUS_ACTIVE;
            $version->published_at = $version->published_at ?: $now;
            $version->published_by = $version->published_by ?: $actor;
            if (!empty($version->approval_request_id)) {
                $version->approval_status = 'APPROVED';
            }
            $version->updated_by = $actor;
            $version->updated_date = $now;
            $version->save();

            $doc->current_version_id = $version->id;
            $doc->current_version_number = $version->version_number;
            $doc->title = $version->title;
            $doc->document_number = $version->document_number_snapshot ?: $doc->document_number;
            $doc->sub_type = $version->sub_type_snapshot ?: $doc->sub_type;
            $doc->owner_department = $version->owner_department_snapshot ?: $doc->owner_department;
            $doc->status = StorageDocument::STATUS_PUBLISHED;
            $doc->updated_by = $actor;
            $doc->updated_date = $now;
            $doc->save();

            if ((int) $version->publish_to_mobile === 1) {
                $this->syncVersionToAnnouncement($version, $actor);
            } else {
                $this->unpublishLinkedAnnouncements($doc->id, $actor);
            }

            return $version;
        });
    }

    /**
     * Synchronize an active Storage version into hcga_news_announcements.
     */
    public function syncVersionToAnnouncement(StorageDocumentVersion $version, $actor = 'system')
    {
        $doc = $version->document ?: StorageDocument::findOrFail($version->document_id);
        $now = Carbon::now();

        $category = $doc->module_type === StorageDocument::MODULE_REGULATION
            ? ($version->sub_type_snapshot ?: $doc->sub_type)
            : 'INFORMATION';

        $syncMode = $version->announcement_sync_mode ?: StorageDocumentVersion::SYNC_UPDATE_EXISTING;
        $existing = HcgaNewsAnnouncement::where('storage_document_id', $doc->id)
            ->orderBy('id', 'DESC')
            ->first();

        $payload = [
            'storage_document_id'  => $doc->id,
            'storage_version_id'   => $version->id,
            'storage_module_type'  => $doc->module_type,
            'storage_sub_type'     => $version->sub_type_snapshot ?: $doc->sub_type,
            'document_number'      => $version->document_number_snapshot ?: $doc->document_number,
            'version_label'        => $version->version_label,
            'title'                => $version->title,
            'category'             => $category,
            'summary'              => $version->summary ?: Str::limit(strip_tags($version->content), 160),
            'content'              => $version->content,
            'thumbnail'            => $version->thumbnail,
            'attachment_file'      => $version->attachment_file,
            'attachment_name'      => $version->attachment_name,
            'target_audience'      => $version->target_audience ?: 'ALL',
            'target_departments'   => $version->target_audience === 'DEPARTMENT' ? ($version->target_departments ?: []) : null,
            'effective_from'       => $version->effective_from,
            'effective_until'      => (int) $version->is_perpetual === 1 ? null : $version->effective_until,
            'is_perpetual'         => (int) $version->is_perpetual,
            'is_pinned'            => (int) $version->is_pinned,
            'require_read_receipt' => (int) $version->require_read_receipt,
            'is_final'             => 1,
        ];

        if ($syncMode === StorageDocumentVersion::SYNC_UPDATE_EXISTING && $existing) {
            $existing->fill($payload);
            $existing->final_date = $now;
            $existing->created_date = $now;
            $existing->updated_by = $actor;
            $existing->updated_date = $now;
            $existing->save();
            return $existing;
        }

        if ((int) $version->archive_old_announcement === 1) {
            HcgaNewsAnnouncement::where('storage_document_id', $doc->id)
                ->where('is_final', 1)
                ->update([
                    'is_final'     => 0,
                    'updated_by'   => $actor,
                    'updated_date' => $now,
                ]);
        }

        $announcement = new HcgaNewsAnnouncement();
        $announcement->fill($payload);
        $announcement->final_date = $now;
        $announcement->created_date = $now;
        $announcement->created_by = $version->created_by ?: $actor;
        $announcement->save();

        return $announcement;
    }

    protected function unpublishLinkedAnnouncements($documentId, $actor = 'system')
    {
        HcgaNewsAnnouncement::where('storage_document_id', $documentId)
            ->where('is_final', 1)
            ->update([
                'is_final'     => 0,
                'updated_by'   => $actor,
                'updated_date' => Carbon::now(),
            ]);
    }

    /**
     * Toggle visibility of a SUPERSEDED version to employees.
     */
    public function toggleVersionHistoryVisibility($versionId, $visibility, $user = null)
    {
        $user = $user ?: Auth::user();
        $username = $user ? ($user->username ?? 'system') : 'system';
        $normalized = $visibility === StorageDocumentVersion::VISIBILITY_EMPLOYEE_VISIBLE
            ? StorageDocumentVersion::VISIBILITY_EMPLOYEE_VISIBLE
            : StorageDocumentVersion::VISIBILITY_ADMIN_ONLY;

        $version = StorageDocumentVersion::findOrFail($versionId);
        $version->history_visibility = $normalized;
        $version->updated_by = $username;
        $version->updated_date = Carbon::now();
        $version->save();

        return $version;
    }

    /**
     * Revoke a document immediately and unpublish its announcements.
     */
    public function revokeDocument($documentId, $reason, $user = null)
    {
        $user = $user ?: Auth::user();
        $username = $user ? ($user->username ?? 'system') : 'system';

        return DB::transaction(function () use ($documentId, $reason, $username) {
            $now = Carbon::now();
            $doc = StorageDocument::findOrFail($documentId);
            $doc->status = StorageDocument::STATUS_REVOKED;
            $doc->revoked_reason = $reason;
            $doc->revoked_at = $now;
            $doc->revoked_by = $username;
            $doc->updated_by = $username;
            $doc->updated_date = $now;
            $doc->save();

            StorageDocumentVersion::where('document_id', $doc->id)
                ->where('version_status', StorageDocumentVersion::STATUS_ACTIVE)
                ->update([
                    'version_status' => StorageDocumentVersion::STATUS_REVOKED,
                    'updated_by'     => $username,
                    'updated_date'   => $now,
                ]);

            $this->unpublishLinkedAnnouncements($doc->id, $username);
            return $doc;
        });
    }

    /**
     * Permanently delete a Storage document, its versions, read receipts, and linked announcements.
     */
    public function deleteDocument($documentId)
    {
        return DB::transaction(function () use ($documentId) {
            $doc = StorageDocument::findOrFail($documentId);
            HcgaNewsAnnouncement::where('storage_document_id', $doc->id)->delete();
            StorageReadReceipt::where('document_id', $doc->id)->delete();
            StorageDocumentVersion::where('document_id', $doc->id)->delete();
            return $doc->delete();
        });
    }

    /**
     * Record read / explicit acknowledgement idempotently.
     */
    public function recordReadReceipt(
        $versionId,
        $user,
        $acknowledge = false,
        $source = 'MOBILE_WEB',
        $ipAddress = null,
        $announcementId = null
    ) {
        if (!$user || empty($user->username)) {
            return null;
        }

        $version = StorageDocumentVersion::find($versionId);
        if (!$version) {
            return null;
        }

        $now = Carbon::now();
        $receipt = StorageReadReceipt::where('version_id', $version->id)
            ->where('username', $user->username)
            ->first();

        if (!$receipt) {
            $emp = null;
            if (!empty($user->Emp_ID)) {
                $emp = DB::table('employment')->where('NIK', $user->Emp_ID)->first();
            }

            $receipt = new StorageReadReceipt();
            $receipt->document_id = $version->document_id;
            $receipt->version_id = $version->id;
            $receipt->announcement_id = $announcementId;
            $receipt->user_id = $user->id ?? null;
            $receipt->username = $user->username;
            $receipt->emp_nik = $user->Emp_ID ?? ($emp->NIK ?? null);
            $receipt->emp_name = $user->Name ?? ($emp->Name ?? $user->username);
            $receipt->department = $user->Department ?? ($emp->Department ?? null);
            $receipt->read_at = $now;
            $receipt->acknowledged_at = $acknowledge ? $now : null;
            $receipt->ip_address = $ipAddress;
            $receipt->source = $source;
            $receipt->save();
        } else {
            if ($announcementId && empty($receipt->announcement_id)) {
                $receipt->announcement_id = $announcementId;
            }
            if ($acknowledge && empty($receipt->acknowledged_at)) {
                $receipt->acknowledged_at = $now;
                $receipt->source = $source;
                if ($ipAddress) {
                    $receipt->ip_address = $ipAddress;
                }
            }
            $receipt->save();
        }

        return $receipt;
    }

    /**
     * Build read receipt stats & list for CMS detail view.
     */
    public function getReadReceiptReport(StorageDocumentVersion $version)
    {
        $receipts = StorageReadReceipt::where('version_id', $version->id)
            ->orderBy('acknowledged_at', 'DESC')
            ->orderBy('read_at', 'DESC')
            ->get();

        $empQuery = DB::table('employment');
        if (DB::getSchemaBuilder()->hasColumn('employment', 'Status_Karyawan')) {
            $empQuery->whereNotIn('Status_Karyawan', ['RESIGN', 'NON-AKTIF', 'TERMINATED']);
        }
        if ($version->target_audience === 'DEPARTMENT' && is_array($version->target_departments) && count($version->target_departments) > 0) {
            $empQuery->whereIn('Department', $version->target_departments);
        }
        $totalTarget = (int) $empQuery->count();

        $totalRead = $receipts->count();
        $totalAck = $receipts->filter(function ($r) {
            return !empty($r->acknowledged_at);
        })->count();

        $percentage = $totalTarget > 0 ? round(($totalAck / $totalTarget) * 100, 1) : 0;

        return [
            'receipts'           => $receipts,
            'total_target'       => $totalTarget,
            'total_read'         => $totalRead,
            'total_acknowledged' => $totalAck,
            'percentage'         => min(100, $percentage),
        ];
    }

    protected function buildVersionFromRequest(
        StorageDocument $doc,
        $versionNumber,
        Request $request,
        $username,
        Carbon $now,
        StorageDocumentVersion $prevVersion = null
    ) {
        $version = new StorageDocumentVersion();
        $version->document_id = $doc->id;
        $version->version_number = $versionNumber;
        $version->version_label = 'v' . $versionNumber;
        $version->created_by = $username;
        $version->created_date = $now;

        if ($prevVersion) {
            $version->thumbnail = $prevVersion->thumbnail;
            $version->attachment_file = $prevVersion->attachment_file;
            $version->attachment_name = $prevVersion->attachment_name;
            $version->attachment_mime = $prevVersion->attachment_mime;
            $version->attachment_size = $prevVersion->attachment_size;
        }

        $this->applyRequestToVersion($doc, $version, $request, $username, $now);
        return $version;
    }

    protected function applyRequestToVersion(
        StorageDocument $doc,
        StorageDocumentVersion $version,
        Request $request,
        $username,
        Carbon $now
    ) {
        $version->document_number_snapshot = $doc->document_number;
        $version->sub_type_snapshot = $doc->sub_type;
        $version->owner_department_snapshot = $doc->owner_department;

        $content = (string) $request->input('content', $version->content ?: '');
        $version->title = $request->input('title', $version->title ?: $doc->title);
        $version->summary = $request->input('summary') ?: Str::limit(strip_tags($content), 160);
        $version->content = $content;

        if ($request->has('changelog')) {
            $version->changelog = $request->input('changelog');
        }

        if ($request->hasFile('thumbnail')) {
            $version->thumbnail = $this->handleFileUpload($request->file('thumbnail'), $doc->module_type, 'thumbnails');
        } elseif ($request->input('remove_thumbnail') == '1') {
            $version->thumbnail = null;
        }

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $version->attachment_name = $file->getClientOriginalName();
            $version->attachment_mime = $file->getClientMimeType();
            $version->attachment_size = (int) $file->getSize();
            $version->attachment_file = $this->handleFileUpload($file, $doc->module_type, 'attachments');
        } elseif ($request->input('remove_attachment') == '1') {
            $version->attachment_file = null;
            $version->attachment_name = null;
            $version->attachment_mime = null;
            $version->attachment_size = null;
        }

        $validityMode = $request->input('validity_mode', StorageDocumentVersion::VALIDITY_PERPETUAL);
        $isPerpetual = $validityMode === StorageDocumentVersion::VALIDITY_PERPETUAL ? 1 : 0;
        $version->validity_mode = $isPerpetual ? StorageDocumentVersion::VALIDITY_PERPETUAL : StorageDocumentVersion::VALIDITY_FIXED_RANGE;
        $version->is_perpetual = $isPerpetual;
        $version->effective_from = $request->input('effective_from') ?: Carbon::today()->toDateString();
        $version->effective_until = $isPerpetual ? null : ($request->input('effective_until') ?: null);

        $audience = $request->input('target_audience', 'ALL') === 'DEPARTMENT' ? 'DEPARTMENT' : 'ALL';
        $depts = $request->input('target_departments', []);
        if (!is_array($depts)) {
            $depts = array_filter(explode(',', (string) $depts));
        }
        $depts = array_values(array_unique(array_filter(array_map(function ($d) {
            return strtoupper(trim((string) $d));
        }, $depts))));

        if ($audience === 'DEPARTMENT' && count($depts) === 0) {
            $audience = 'ALL';
        }

        $version->target_audience = $audience;
        $version->target_departments = $audience === 'DEPARTMENT' ? $depts : [];
        $version->is_pinned = $request->input('is_pinned') == '1' ? 1 : 0;
        $version->require_read_receipt = $request->input('require_read_receipt') == '1' ? 1 : 0;
        $version->publish_to_mobile = $request->has('publish_to_mobile')
            ? ($request->input('publish_to_mobile') == '1' ? 1 : 0)
            : (isset($version->publish_to_mobile) ? (int) $version->publish_to_mobile : 1);

        $version->updated_by = $username;
        $version->updated_date = $now;
    }

    protected function handleFileUpload(UploadedFile $file, $moduleType, $subfolder = 'attachments')
    {
        $typeDir = strtolower($moduleType);
        $year = now()->format('Y');
        $month = now()->format('m');
        $uuid = Str::uuid()->toString();
        $ext = $file->getClientOriginalExtension();
        $filename = "{$uuid}.{$ext}";

        $relDir = "filevendor/storage_management/{$typeDir}/{$subfolder}/{$year}/{$month}";
        $fullDir = public_path($relDir);

        if (!is_dir($fullDir)) {
            mkdir($fullDir, 0777, true);
        }

        $file->move($fullDir, $filename);

        return "{$relDir}/{$filename}";
    }
}
