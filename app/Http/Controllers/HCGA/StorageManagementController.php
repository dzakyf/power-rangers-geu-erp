<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Models\HCGA\StorageDocument;
use App\Models\HCGA\StorageDocumentVersion;
use App\Services\HCGA\AnnouncementService;
use App\Services\HCGA\StorageManagementService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StorageManagementController extends Controller
{
    protected $storageService;
    protected $announcementService;

    public function __construct(
        StorageManagementService $storageService,
        AnnouncementService $announcementService
    ) {
        $this->storageService = $storageService;
        $this->announcementService = $announcementService;
    }

    protected function normalizeModuleType($moduleType)
    {
        $upper = strtoupper(trim((string) $moduleType));
        return $upper === StorageDocument::MODULE_INFORMATION
            ? StorageDocument::MODULE_INFORMATION
            : StorageDocument::MODULE_REGULATION;
    }

    protected function authorizeModule($moduleType, $action = 'view')
    {
        $menuId = $moduleType === StorageDocument::MODULE_INFORMATION ? 'SI01163' : 'SI01162';
        if (can_menu($menuId, $action) || $this->announcementService->isHcgaOrAdmin()) {
            return;
        }
        abort(403, 'Akses ditolak untuk modul Storage Management.');
    }

    public function regulationIndex()
    {
        return $this->renderIndex(StorageDocument::MODULE_REGULATION);
    }

    public function informationIndex()
    {
        return $this->renderIndex(StorageDocument::MODULE_INFORMATION);
    }

    protected function renderIndex($moduleType)
    {
        $this->authorizeModule($moduleType, 'view');

        return view('humanresources.storage.index', [
            'moduleType'      => $moduleType,
            'moduleTitle'     => $moduleType === StorageDocument::MODULE_REGULATION ? 'Storage Management - Regulasi' : 'Storage Management - Informasi',
            'moduleSubtitle'  => $moduleType === StorageDocument::MODULE_REGULATION
                ? 'Kelola SK-Dir, SOP, dan Internal Memo lengkap dengan riwayat versi, masa berlaku, dan sinkronisasi pengumuman mobile.'
                : 'Kelola dokumen Informasi perusahaan dengan ketetapan masa berlaku, target departemen, dan sinkronisasi pengumuman mobile.',
            'subTypes'        => StorageDocument::getSubTypes($moduleType),
            'stats'           => $this->storageService->getSummaryStats($moduleType),
            'approvalMode'    => $this->storageService->getApprovalMode($moduleType),
            'isHcga'          => $this->announcementService->isHcgaOrAdmin(),
        ]);
    }

    /**
     * dhtmlXGrid XML feed
     */
    public function data(Request $request)
    {
        $moduleType = $this->normalizeModuleType($request->input('module_type', StorageDocument::MODULE_REGULATION));
        $this->authorizeModule($moduleType, 'view');

        $items = $this->storageService->getGridList($moduleType, $request);
        $users = DB::table('users')->pluck('Name', 'username');

        $xml = new \SimpleXMLElement('<rows/>');
        foreach ($items as $doc) {
            $ver = $doc->currentVersion;
            $effectiveStatus = $doc->computeEffectiveStatus();

            $row = $xml->addChild('row');
            $row->addAttribute('id', $doc->id);

            $row->addChild('cell', $doc->id);
            $row->addChild('cell', htmlspecialchars(StorageDocument::getSubTypeLabel($doc->sub_type)));
            $row->addChild('cell', htmlspecialchars($doc->document_number ?: '-'));
            $row->addChild('cell', htmlspecialchars($doc->title));
            $row->addChild('cell', htmlspecialchars($ver ? $ver->version_label : ('v' . $doc->current_version_number)));
            $row->addChild('cell', htmlspecialchars($ver ? $ver->validity_label : '-'));
            $row->addChild('cell', htmlspecialchars($ver ? $ver->target_label : 'ALL'));
            $row->addChild('cell', htmlspecialchars($effectiveStatus));
            $row->addChild('cell', $ver && (int) $ver->publish_to_mobile === 1 ? 'Ya' : 'Tidak');
            $row->addChild('cell', htmlspecialchars($users[$doc->created_by] ?? $doc->created_by));
            $row->addChild('cell', $doc->updated_date ?: $doc->created_date);
        }

        return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
    }

    public function create(Request $request)
    {
        $moduleType = $this->normalizeModuleType($request->input('module_type', StorageDocument::MODULE_REGULATION));
        $this->authorizeModule($moduleType, 'create');

        return view('humanresources.storage.form', [
            'action'       => 'create',
            'moduleType'   => $moduleType,
            'doc'          => null,
            'version'      => null,
            'subTypes'     => StorageDocument::getSubTypes($moduleType),
            'departments'  => $this->storageService->getAvailableDepartments(),
            'approvalMode' => $this->storageService->getApprovalMode($moduleType),
        ]);
    }

    public function store(Request $request)
    {
        $moduleType = $this->normalizeModuleType($request->input('module_type', StorageDocument::MODULE_REGULATION));
        $this->authorizeModule($moduleType, 'create');

        $rules = [
            'sub_type'        => 'required|string|max:40',
            'document_number' => 'nullable|string|max:100',
            'title'           => 'required|string|max:255',
            'content'         => 'required|string',
            'validity_mode'   => 'required|in:FIXED_RANGE,PERPETUAL',
            'effective_from'  => 'required|date',
            'effective_until' => 'nullable|date|after_or_equal:effective_from',
            'attachment'      => 'nullable|file|max:25600',
            'thumbnail'       => 'nullable|image|max:5120',
        ];
        if ($request->input('validity_mode') === 'FIXED_RANGE') {
            $rules['effective_until'] = 'required|date|after_or_equal:effective_from';
        }
        $request->validate($rules);

        $doc = $this->storageService->createDocument($moduleType, $request, Auth::user());

        return response()->json([
            'success' => true,
            'message' => $doc->status === StorageDocument::STATUS_WAITING_APPROVAL
                ? 'Dokumen berhasil disimpan dan diajukan ke Manager HCGA untuk approval.'
                : ($doc->status === StorageDocument::STATUS_DRAFT ? 'Dokumen berhasil disimpan sebagai Draft.' : 'Dokumen berhasil dipublikasikan!'),
            'data'    => $doc,
        ]);
    }

    public function edit(Request $request)
    {
        $id = $request->input('id');
        $doc = StorageDocument::with('currentVersion')->findOrFail($id);
        $this->authorizeModule($doc->module_type, 'edit');

        return view('humanresources.storage.form', [
            'action'       => 'edit',
            'moduleType'   => $doc->module_type,
            'doc'          => $doc,
            'version'      => $doc->currentVersion,
            'subTypes'     => StorageDocument::getSubTypes($doc->module_type),
            'departments'  => $this->storageService->getAvailableDepartments(),
            'approvalMode' => $this->storageService->getApprovalMode($doc->module_type),
        ]);
    }

    public function update(Request $request)
    {
        $id = $request->input('id');
        $doc = StorageDocument::findOrFail($id);
        $this->authorizeModule($doc->module_type, 'edit');

        $rules = [
            'sub_type'        => 'required|string|max:40',
            'document_number' => 'nullable|string|max:100',
            'title'           => 'required|string|max:255',
            'content'         => 'required|string',
            'validity_mode'   => 'required|in:FIXED_RANGE,PERPETUAL',
            'effective_from'  => 'required|date',
            'effective_until' => 'nullable|date|after_or_equal:effective_from',
            'attachment'      => 'nullable|file|max:25600',
            'thumbnail'       => 'nullable|image|max:5120',
        ];
        if ($request->input('validity_mode') === 'FIXED_RANGE') {
            $rules['effective_until'] = 'required|date|after_or_equal:effective_from';
        }
        $request->validate($rules);

        $updated = $this->storageService->updateDocument($doc->id, $request, Auth::user());

        return response()->json([
            'success' => true,
            'message' => 'Dokumen berhasil diperbarui!',
            'data'    => $updated,
        ]);
    }

    public function detail(Request $request)
    {
        $id = $request->input('id');
        $doc = $this->storageService->getDocumentDetail($id);
        $this->authorizeModule($doc->module_type, 'view');

        $currentVersion = $doc->currentVersion;
        $readReport = $currentVersion
            ? $this->storageService->getReadReceiptReport($currentVersion)
            : ['receipts' => collect(), 'total_target' => 0, 'total_read' => 0, 'total_acknowledged' => 0, 'percentage' => 0];

        $approvalRequestIds = $doc->versions->pluck('approval_request_id')->filter()->unique()->values()->all();
        $approvalLogs = collect();
        $approvalRequests = collect();
        if (!empty($approvalRequestIds)) {
            $approvalRequests = DB::table('approval_request')
                ->whereIn('ID', $approvalRequestIds)
                ->orderBy('ID', 'DESC')
                ->get()
                ->keyBy('ID');

            $approvalLogs = DB::table('approval_log')
                ->whereIn('Request_ID', $approvalRequestIds)
                ->orderBy('ID', 'DESC')
                ->get();
        }

        return view('humanresources.storage.detail', [
            'doc'              => $doc,
            'currentVersion'   => $currentVersion,
            'effectiveStatus'  => $doc->computeEffectiveStatus(),
            'readReport'       => $readReport,
            'approvalRequests' => $approvalRequests,
            'approvalLogs'     => $approvalLogs,
            'approvalMode'     => $this->storageService->getApprovalMode($doc->module_type),
            'isHcga'           => $this->announcementService->isHcgaOrAdmin(),
        ]);
    }

    public function createVersion(Request $request)
    {
        $id = $request->input('id');
        $doc = StorageDocument::with('currentVersion')->findOrFail($id);
        $this->authorizeModule($doc->module_type, 'edit');

        return view('humanresources.storage.version-form', [
            'doc'          => $doc,
            'prevVersion'  => $doc->currentVersion,
            'nextNumber'   => ((int) StorageDocumentVersion::where('document_id', $doc->id)->max('version_number')) + 1,
            'subTypes'     => StorageDocument::getSubTypes($doc->module_type),
            'departments'  => $this->storageService->getAvailableDepartments(),
            'approvalMode' => $this->storageService->getApprovalMode($doc->module_type),
        ]);
    }

    public function storeVersion(Request $request)
    {
        $id = $request->input('document_id');
        $doc = StorageDocument::findOrFail($id);
        $this->authorizeModule($doc->module_type, 'edit');

        $rules = [
            'title'                       => 'required|string|max:255',
            'content'                     => 'required|string',
            'changelog'                   => 'required|string|max:2000',
            'validity_mode'               => 'required|in:FIXED_RANGE,PERPETUAL',
            'effective_from'              => 'required|date',
            'effective_until'             => 'nullable|date|after_or_equal:effective_from',
            'previous_version_visibility' => 'required|in:ADMIN_ONLY,EMPLOYEE_VISIBLE',
            'announcement_sync_mode'      => 'required|in:UPDATE_EXISTING,CREATE_NEW',
            'attachment'                  => 'nullable|file|max:25600',
            'thumbnail'                   => 'nullable|image|max:5120',
        ];
        if ($request->input('validity_mode') === 'FIXED_RANGE') {
            $rules['effective_until'] = 'required|date|after_or_equal:effective_from';
        }
        $request->validate($rules);

        $version = $this->storageService->createNewVersion($doc->id, $request, Auth::user());

        return response()->json([
            'success' => true,
            'message' => $version->version_status === StorageDocumentVersion::STATUS_WAITING_APPROVAL
                ? "Versi {$version->version_label} diajukan ke Manager HCGA untuk approval."
                : "Versi {$version->version_label} berhasil disimpan!",
            'data'    => $version,
        ]);
    }

    public function toggleVersionVisibility(Request $request)
    {
        $version = StorageDocumentVersion::with('document')->findOrFail($request->input('version_id'));
        $this->authorizeModule($version->document->module_type, 'edit');

        $visibility = $request->input('history_visibility', StorageDocumentVersion::VISIBILITY_ADMIN_ONLY);
        $updated = $this->storageService->toggleVersionHistoryVisibility($version->id, $visibility, Auth::user());

        return response()->json([
            'success' => true,
            'message' => 'Visibilitas riwayat versi berhasil diperbarui.',
            'data'    => $updated,
        ]);
    }

    public function revoke(Request $request)
    {
        $doc = StorageDocument::findOrFail($request->input('id'));
        $this->authorizeModule($doc->module_type, 'edit');

        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $this->storageService->revokeDocument($doc->id, $request->input('reason'), Auth::user());

        return response()->json([
            'success' => true,
            'message' => 'Dokumen berhasil dicabut (Revoked) dan pengumuman terkait telah dinonaktifkan.',
        ]);
    }

    public function destroy(Request $request)
    {
        $doc = StorageDocument::findOrFail($request->input('id'));
        $this->authorizeModule($doc->module_type, 'delete');

        $this->storageService->deleteDocument($doc->id);

        return response()->json([
            'success' => true,
            'message' => 'Dokumen Storage Management berhasil dihapus.',
        ]);
    }

    public function settingsModal(Request $request)
    {
        if (!$this->announcementService->isHcgaOrAdmin()) {
            abort(403, 'Hanya Admin/HCGA yang dapat mengubah pengaturan approval.');
        }

        return view('humanresources.storage.settings-modal', [
            'settings' => $this->storageService->getApprovalSettings(),
        ]);
    }

    public function updateSettings(Request $request)
    {
        if (!$this->announcementService->isHcgaOrAdmin()) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $request->validate([
            'regulation_mode'  => 'required|in:DIRECT,OPTIONAL,REQUIRED',
            'information_mode' => 'required|in:DIRECT,OPTIONAL,REQUIRED',
        ]);

        $settings = $this->storageService->saveApprovalSettings($request->only(['regulation_mode', 'information_mode']));

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan alur approval Storage Management berhasil disimpan.',
            'data'    => $settings,
        ]);
    }
}
