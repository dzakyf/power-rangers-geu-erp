<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\HCGA\AnnouncementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnnouncementCmsController extends Controller
{
    protected $announcementService;

    public function __construct(AnnouncementService $announcementService)
    {
        $this->announcementService = $announcementService;
    }

    /**
     * Display CMS web index for News and Announcements
     */
    public function index()
    {
        $user = Auth::user();
        $isHcga = $this->announcementService->isHcgaOrAdmin($user);

        return view('humanresources.announcement.index', [
            'isHcga' => $isHcga,
            'user'   => $user,
        ]);
    }

    /**
     * Provide dhtmlXGrid XML data for announcements
     */
    public function data(Request $request)
    {
        $announcements = $this->announcementService->getCmsList($request);

        $xml = new \SimpleXMLElement('<rows/>');
        $getUsers = DB::table('users')->pluck('Name', 'username');

        foreach ($announcements as $item) {
            $row = $xml->addChild('row');
            $row->addAttribute('id', $item->id);

            // Columns matching dhtmlxgrid headers
            $row->addChild('cell', $item->id);
            $row->addChild('cell', htmlspecialchars($item->category ?? 'ANNOUNCEMENT'));
            $row->addChild('cell', htmlspecialchars($item->title));
            $row->addChild('cell', htmlspecialchars($item->target_audience ?? 'ALL'));
            $row->addChild('cell', $item->is_pinned ? 'Pinned' : 'Normal');
            $row->addChild('cell', $item->is_final ? 'Published' : 'Draft');
            $row->addChild('cell', $item->attachment_file ? 'Yes' : 'No');
            $row->addChild('cell', $item->view_count ?? 0);
            $row->addChild('cell', $getUsers[$item->created_by] ?? $item->created_by);
            $row->addChild('cell', $item->created_date);
            $row->addChild('cell', $item->final_date ?? '-');
        }

        return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
    }

    /**
     * Show create form (modal or standalone)
     */
    public function create()
    {
        return view('humanresources.announcement.form', [
            'item'   => null,
            'action' => 'create',
        ]);
    }

    /**
     * Store new announcement
     */
    public function store(Request $request)
    {
        $request->validate([
            'Title'      => 'required|string|max:255',
            'Category'   => 'required|string|max:50',
            'Content'    => 'required|string',
            'Attachment' => 'nullable|file|max:20480', // 20MB
            'Thumbnail'  => 'nullable|image|max:5120',  // 5MB
        ]);

        $announcement = $this->announcementService->storeAnnouncement($request);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Announcement berhasil dipublikasikan/disimpan!',
                'data'    => $announcement,
            ]);
        }

        return redirect()->back()->with('success', 'Announcement berhasil disimpan!');
    }

    /**
     * Show detail / edit view
     */
    public function detail(Request $request)
    {
        $id = $request->get('id') ?: $request->get('Task');
        $item = $this->announcementService->getDetail($id, false);

        if (!$item) {
            abort(404, 'Data pengumuman tidak ditemukan.');
        }

        $user = Auth::user();
        $isHcga = $this->announcementService->isHcgaOrAdmin($user);

        return view('humanresources.announcement.detail', [
            'item'   => $item,
            'isHcga' => $isHcga,
            'user'   => $user,
        ]);
    }

    /**
     * Show edit modal form
     */
    public function edit(Request $request)
    {
        $id = $request->get('id') ?: $request->get('Task');
        $item = $this->announcementService->getDetail($id, false);

        if (!$item) {
            return response()->json(['error' => 'Data tidak ditemukan.'], 404);
        }

        return view('humanresources.announcement.form', [
            'item'   => $item,
            'action' => 'edit',
        ]);
    }

    /**
     * Update existing announcement
     */
    public function update(Request $request, $id = null)
    {
        $id = $id ?: $request->input('ID');

        $request->validate([
            'Title'      => 'required|string|max:255',
            'Category'   => 'required|string|max:50',
            'Content'    => 'required|string',
            'Attachment' => 'nullable|file|max:20480',
            'Thumbnail'  => 'nullable|image|max:5120',
        ]);

        $announcement = $this->announcementService->updateAnnouncement($id, $request);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Announcement berhasil diperbarui!',
                'data'    => $announcement,
            ]);
        }

        return redirect()->back()->with('success', 'Announcement berhasil diperbarui!');
    }

    /**
     * Delete announcement
     */
    public function destroy(Request $request, $id = null)
    {
        $id = $id ?: $request->input('id');
        $this->announcementService->deleteAnnouncement($id);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Announcement berhasil dihapus.',
            ]);
        }

        return redirect()->back()->with('success', 'Announcement berhasil dihapus.');
    }
}
