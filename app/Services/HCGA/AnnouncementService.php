<?php

namespace App\Services\HCGA;

use App\HcgaNewsAnnouncement;
use App\Models\HCGA\StorageDocumentVersion;
use App\Models\HCGA\StorageReadReceipt;
use App\Services\NotificationService;
use App\Services\UploadService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AnnouncementService
{
    protected $uploadService;
    protected $notificationService;

    public function __construct(UploadService $uploadService, NotificationService $notificationService)
    {
        $this->uploadService = $uploadService;
        $this->notificationService = $notificationService;
    }

    /**
     * Check if user is HCGA or Admin
     */
    public function isHcgaOrAdmin($user = null)
    {
        $user = $user ?: Auth::user();
        if (!$user) {
            return false;
        }

        $dept = strtoupper($user->Department ?? '');
        $priv = strtoupper($user->Priv_ID ?? '');
        $username = strtolower($user->username ?? '');

        if (
            in_array($username, ['admin', 'hcga', 'manager.hcga', 'hrd']) ||
            strpos($dept, 'HCGA') !== false ||
            strpos($dept, 'HRD') !== false ||
            strpos($priv, 'ADMIN') !== false ||
            ($user->MainPriv ?? null) == '1'
        ) {
            return true;
        }

        return false;
    }

    /**
     * Get announcement query for Datatables / dhtmlXGrid / CMS Listing
     */
    public function getCmsList(Request $request)
    {
        $query = HcgaNewsAnnouncement::query();

        if ($request->filled('category')) {
            $cat = $request->input('category');
            if ($cat === 'REGULATION') {
                $query->where('storage_module_type', 'REGULATION');
            } else {
                $query->where('category', $cat);
            }
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'FINAL') {
                $query->where('is_final', 1);
            } elseif ($request->input('status') === 'DRAFT') {
                $query->where('is_final', 0);
            }
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('summary', 'LIKE', "%{$search}%")
                  ->orWhere('content', 'LIKE', "%{$search}%")
                  ->orWhere('document_number', 'LIKE', "%{$search}%");
            });
        }

        return $query->orderBy('is_pinned', 'DESC')
                     ->orderBy('created_date', 'DESC')
                     ->get();
    }

    /**
     * Get announcements for Mobile / Employee feed
     */
    public function getMobileFeed(Request $request, $user = null)
    {
        $user = $user ?: Auth::user();
        $isHcga = $this->isHcgaOrAdmin($user);
        $userDept = $user->Department ?? null;

        $query = HcgaNewsAnnouncement::query();

        // Non-HCGA: only Published, Currently Effective (not SCHEDULED / EXPIRED), and matching Audience/Department
        if (!$isHcga) {
            $query->published()
                  ->currentlyEffective()
                  ->forAudience(false, $userDept);
        } else {
            if ($request->get('tab') === 'draft') {
                $query->where('is_final', 0);
            } elseif ($request->get('tab') === 'my') {
                $query->where('created_by', $user->username ?? '');
            } else {
                $query->published()->currentlyEffective();
            }
        }

        if ($request->filled('category') && $request->input('category') !== 'ALL') {
            $cat = strtoupper(trim((string) $request->input('category')));
            if ($cat === 'REGULATION') {
                $query->where(function ($q) {
                    $q->where('storage_module_type', 'REGULATION')
                      ->orWhereIn('category', ['SK_DIR', 'SOP', 'INTERNAL_MEMO', 'POLICY']);
                });
            } elseif ($cat === 'INFORMATION') {
                $query->where(function ($q) {
                    $q->where('storage_module_type', 'INFORMATION')
                      ->orWhere('category', 'INFORMATION');
                });
            } else {
                $query->where(function ($q) use ($cat) {
                    $q->where('category', $cat)
                      ->orWhere('storage_sub_type', $cat);
                });
            }
        }

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'LIKE', "%{$q}%")
                    ->orWhere('summary', 'LIKE', "%{$q}%")
                    ->orWhere('content', 'LIKE', "%{$q}%")
                    ->orWhere('document_number', 'LIKE', "%{$q}%");
            });
        }

        return $query->orderBy('is_pinned', 'DESC')
                     ->orderBy('created_date', 'DESC')
                     ->paginate(15);
    }

    /**
     * Find announcement by ID and increment views
     */
    public function getDetail($id, $trackView = true)
    {
        $item = HcgaNewsAnnouncement::with(['storageDocument', 'storageVersion'])->find($id);
        if ($item && $trackView) {
            $item->increment('view_count');
        }
        return $item;
    }

    /**
     * Build extra Storage metadata for Mobile Web & API detail view.
     */
    public function getMobileDetailPayload(HcgaNewsAnnouncement $item, $user = null)
    {
        $visibleVersions = collect();
        $userReceipt = null;

        if ($item->storage_document_id) {
            $visibleVersions = StorageDocumentVersion::where('document_id', $item->storage_document_id)
                ->where('version_status', StorageDocumentVersion::STATUS_SUPERSEDED)
                ->where('history_visibility', StorageDocumentVersion::VISIBILITY_EMPLOYEE_VISIBLE)
                ->orderBy('version_number', 'DESC')
                ->get();
        }

        if ($item->storage_version_id && $user && !empty($user->username)) {
            $userReceipt = StorageReadReceipt::where('version_id', $item->storage_version_id)
                ->where('username', $user->username)
                ->first();
        }

        return [
            'visible_versions' => $visibleVersions,
            'user_receipt'     => $userReceipt,
            'is_acknowledged'  => $userReceipt && !empty($userReceipt->acknowledged_at),
        ];
    }

    /**
     * Store new announcement
     */
    public function storeAnnouncement(Request $request, $user = null)
    {
        $user = $user ?: Auth::user();
        $username = $user->username ?? 'admin';

        $isFinal = ($request->input('Status') === 'Draft' || $request->input('Is_Final') == '0') ? 0 : 1;

        $thumbnailPath = null;
        if ($request->hasFile('Thumbnail')) {
            $thumbnailPath = $this->handleFileUpload($request->file('Thumbnail'), 'thumbnails');
        }

        $attachmentPath = null;
        $attachmentName = null;
        if ($request->hasFile('Attachment')) {
            $file = $request->file('Attachment');
            $attachmentName = $file->getClientOriginalName();
            $attachmentPath = $this->handleFileUpload($file, 'attachments');
        }

        $announcement = new HcgaNewsAnnouncement();
        $announcement->title = $request->input('Title');
        $announcement->summary = $request->input('Summary') ?: Str::limit(strip_tags($request->input('Content')), 160);
        $announcement->category = $request->input('Category', 'ANNOUNCEMENT');
        $announcement->content = $request->input('Content');
        $announcement->thumbnail = $thumbnailPath;
        $announcement->attachment_file = $attachmentPath;
        $announcement->attachment_name = $attachmentName;
        $announcement->target_audience = $request->input('Target_Audience', 'ALL');
        $announcement->is_perpetual = 1;
        $announcement->is_pinned = $request->has('Is_Pinned') && $request->input('Is_Pinned') == '1' ? 1 : 0;
        $announcement->is_final = $isFinal;
        $announcement->final_date = $isFinal ? Carbon::now() : null;
        $announcement->created_date = Carbon::now();
        $announcement->created_by = $username;
        $announcement->save();

        return $announcement;
    }

    /**
     * Update existing announcement
     */
    public function updateAnnouncement($id, Request $request, $user = null)
    {
        $user = $user ?: Auth::user();
        $username = $user->username ?? 'admin';

        $announcement = HcgaNewsAnnouncement::findOrFail($id);

        $wasFinal = (int) $announcement->is_final;
        $isFinal = ($request->input('Status') === 'Draft' || $request->input('Is_Final') == '0') ? 0 : 1;

        if ($request->hasFile('Thumbnail')) {
            $announcement->thumbnail = $this->handleFileUpload($request->file('Thumbnail'), 'thumbnails');
        } elseif ($request->input('Remove_Thumbnail') == '1') {
            $announcement->thumbnail = null;
        }

        if ($request->hasFile('Attachment')) {
            $file = $request->file('Attachment');
            $announcement->attachment_name = $file->getClientOriginalName();
            $announcement->attachment_file = $this->handleFileUpload($file, 'attachments');
        } elseif ($request->input('Remove_Attachment') == '1') {
            $announcement->attachment_file = null;
            $announcement->attachment_name = null;
        }

        $announcement->title = $request->input('Title');
        $announcement->summary = $request->input('Summary') ?: Str::limit(strip_tags($request->input('Content')), 160);
        $announcement->category = $request->input('Category', $announcement->category ?: 'ANNOUNCEMENT');
        $announcement->content = $request->input('Content');
        $announcement->target_audience = $request->input('Target_Audience', $announcement->target_audience ?: 'ALL');
        $announcement->is_pinned = $request->has('Is_Pinned') && $request->input('Is_Pinned') == '1' ? 1 : 0;
        $announcement->is_final = $isFinal;

        if (!$wasFinal && $isFinal) {
            $announcement->final_date = Carbon::now();
        }

        $announcement->updated_by = $username;
        $announcement->updated_date = Carbon::now();
        $announcement->save();

        return $announcement;
    }

    /**
     * Delete announcement
     */
    public function deleteAnnouncement($id)
    {
        $announcement = HcgaNewsAnnouncement::findOrFail($id);
        return $announcement->delete();
    }

    /**
     * Handle file uploads to public/filevendor/announcements
     */
    protected function handleFileUpload(UploadedFile $file, $subfolder = 'files')
    {
        $year = now()->format('Y');
        $month = now()->format('m');
        $uuid = Str::uuid()->toString();
        $ext = $file->getClientOriginalExtension();
        $filename = "{$uuid}.{$ext}";

        $relDir = "filevendor/announcements/{$subfolder}/{$year}/{$month}";
        $fullDir = public_path($relDir);

        if (!is_dir($fullDir)) {
            mkdir($fullDir, 0777, true);
        }

        $file->move($fullDir, $filename);

        return "{$relDir}/{$filename}";
    }
}
