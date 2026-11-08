<?php

namespace App\Services\HCGA;

use App\HcgaNewsAnnouncement;
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
            $query->where('category', $request->input('category'));
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
                  ->orWhere('content', 'LIKE', "%{$search}%");
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

        $query = HcgaNewsAnnouncement::query();

        // If not HCGA/Admin, only show Published and target_audience = ALL
        if (!$isHcga) {
            $query->published()->forAudience(false);
        } else {
            // HCGA can filter or view draft as well if tab=draft
            if ($request->get('tab') === 'draft') {
                $query->where('is_final', 0);
            } elseif ($request->get('tab') === 'my') {
                $query->where('created_by', $user->username ?? '');
            } else {
                // Default feed: all published
                $query->published();
            }
        }

        if ($request->filled('category') && $request->input('category') !== 'ALL') {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'LIKE', "%{$q}%")
                    ->orWhere('summary', 'LIKE', "%{$q}%")
                    ->orWhere('content', 'LIKE', "%{$q}%");
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
        $item = HcgaNewsAnnouncement::find($id);
        if ($item && $trackView) {
            $item->increment('view_count');
        }
        return $item;
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
