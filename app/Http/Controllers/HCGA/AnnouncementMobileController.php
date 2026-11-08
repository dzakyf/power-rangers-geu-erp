<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\HCGA\AnnouncementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementMobileController extends Controller
{
    protected $announcementService;

    public function __construct(AnnouncementService $announcementService)
    {
        $this->announcementService = $announcementService;
    }

    /**
     * Mobile Feed View (Accessible to all users; Admin/HCGA has CMS capabilities)
     */
    public function mobileIndex(Request $request)
    {
        $user = Auth::user();
        $isHcga = $this->announcementService->isHcgaOrAdmin($user);
        $announcements = $this->announcementService->getMobileFeed($request, $user);

        return view('humanresources.announcement.mobile.index', [
            'announcements' => $announcements,
            'isHcga'        => $isHcga,
            'user'          => $user,
            'currentTab'    => $request->get('tab', 'all'),
            'currentCat'    => $request->get('category', 'ALL'),
            'searchQuery'   => $request->get('q', ''),
        ]);
    }

    /**
     * Mobile Detail View (Accessible to all users)
     */
    public function mobileDetail($id)
    {
        $user = Auth::user();
        $isHcga = $this->announcementService->isHcgaOrAdmin($user);
        $announcement = $this->announcementService->getDetail($id, true);

        if (!$announcement) {
            abort(404, 'Berita atau Pengumuman tidak ditemukan.');
        }

        // If unpublished and user is not HCGA, deny access
        if (!$announcement->is_final && !$isHcga) {
            abort(403, 'Pengumuman ini masih berstatus draft.');
        }

        return view('humanresources.announcement.mobile.detail', [
            'item'   => $announcement,
            'isHcga' => $isHcga,
            'user'   => $user,
        ]);
    }

    /**
     * Mobile Create View (HCGA & Admin only)
     */
    public function mobileForm(Request $request, $id = null)
    {
        $user = Auth::user();
        $isHcga = $this->announcementService->isHcgaOrAdmin($user);

        if (!$isHcga) {
            abort(403, 'Akses ditolak. Fitur pengelolaan hanya untuk admin & HCGA.');
        }

        $item = null;
        if ($id) {
            $item = $this->announcementService->getDetail($id, false);
        }

        return view('humanresources.announcement.mobile.form', [
            'item'   => $item,
            'isHcga' => $isHcga,
            'user'   => $user,
        ]);
    }

    /**
     * Mobile Submit / Store / Update (HCGA & Admin only)
     */
    public function mobileSubmit(Request $request)
    {
        $user = Auth::user();
        $isHcga = $this->announcementService->isHcgaOrAdmin($user);

        if (!$isHcga) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Fitur kelola hanya untuk Admin & HCGA.'
            ], 403);
        }

        $request->validate([
            'Title'      => 'required|string|max:255',
            'Category'   => 'required|string|max:50',
            'Content'    => 'required|string',
            'Attachment' => 'nullable|file|max:20480',
            'Thumbnail'  => 'nullable|image|max:5120',
        ]);

        if ($request->filled('ID')) {
            $announcement = $this->announcementService->updateAnnouncement($request->input('ID'), $request, $user);
            $msg = 'Pengumuman / Berita berhasil diperbarui!';
        } else {
            $announcement = $this->announcementService->storeAnnouncement($request, $user);
            $msg = 'Pengumuman / Berita berhasil dipublikasikan!';
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'  => true,
                'message'  => $msg,
                'redirect' => route('announcement.mobile.detail', $announcement->id),
            ]);
        }

        return redirect()->route('announcement.mobile.detail', $announcement->id)->with('success', $msg);
    }

    /**
     * Mobile Delete (HCGA & Admin only)
     */
    public function mobileDelete(Request $request, $id)
    {
        $user = Auth::user();
        $isHcga = $this->announcementService->isHcgaOrAdmin($user);

        if (!$isHcga) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.'
            ], 403);
        }

        $this->announcementService->deleteAnnouncement($id);

        return response()->json([
            'success' => true,
            'message' => 'Pengumuman berhasil dihapus.'
        ]);
    }
}
