<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Models\HCGA\CompanyGuideline;
use App\Services\HCGA\CompanyGuidelineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CompanyGuidelineCmsController extends Controller
{
    protected $guidelineService;

    public function __construct(CompanyGuidelineService $guidelineService)
    {
        $this->guidelineService = $guidelineService;
    }

    /**
     * Display CMS web index for Visi Misi, Core Value, and Peraturan Perusahaan
     */
    public function index()
    {
        $user = Auth::user();
        $types = CompanyGuideline::getTypes();

        return view('humanresources.company-guideline.index', [
            'user'  => $user,
            'types' => $types,
        ]);
    }

    /**
     * Provide dhtmlXGrid XML data for company guidelines
     */
    public function data(Request $request)
    {
        $items = $this->guidelineService->getCmsList($request);

        $xml = new \SimpleXMLElement('<rows/>');
        $getUsers = DB::table('users')->pluck('Name', 'username');

        foreach ($items as $item) {
            $row = $xml->addChild('row');
            $row->addAttribute('id', $item->id);

            // Columns matching dhtmlxgrid headers:
            // ID, Jenis, Kode, Judul, Urutan, Status, Lampiran, Diperbarui Oleh, Tanggal Update
            $row->addChild('cell', $item->id);
            $row->addChild('cell', htmlspecialchars($item->type));
            $row->addChild('cell', htmlspecialchars($item->code ?? '-'));
            $row->addChild('cell', htmlspecialchars($item->title));
            $row->addChild('cell', $item->sort_order);
            $row->addChild('cell', $item->is_active ? 'ACTIVE' : 'INACTIVE');
            $row->addChild('cell', $item->attachment_file ? 'Ada PDF' : '-');
            $row->addChild('cell', htmlspecialchars($getUsers[$item->updated_by ?? $item->created_by] ?? ($item->updated_by ?? $item->created_by ?? '-')));
            $row->addChild('cell', $item->updated_date ? date('d-m-Y H:i', strtotime($item->updated_date)) : ($item->created_date ? date('d-m-Y H:i', strtotime($item->created_date)) : '-'));
        }

        return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
    }

    /**
     * Show create modal form
     */
    public function create()
    {
        return view('humanresources.company-guideline.form', [
            'item'   => null,
            'types'  => CompanyGuideline::getTypes(),
            'action' => 'create',
        ]);
    }

    /**
     * Store new guideline item
     */
    public function store(Request $request)
    {
        $request->validate([
            'type'       => 'required|string|in:VISI,MISI,CORE_VALUE,PERATURAN',
            'title'      => 'required|string|max:255',
            'sort_order' => 'nullable|integer',
            'attachment' => 'nullable|file|max:20480', // 20MB
        ]);

        $item = $this->guidelineService->storeGuideline($request);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan!',
                'data'    => $item,
            ]);
        }

        return redirect()->back()->with('success', 'Data berhasil disimpan!');
    }

    /**
     * Show edit modal form
     */
    public function edit(Request $request)
    {
        $id = $request->query('id');
        $item = $this->guidelineService->findById($id);

        return view('humanresources.company-guideline.form', [
            'item'   => $item,
            'types'  => CompanyGuideline::getTypes(),
            'action' => 'edit',
        ]);
    }

    /**
     * Update guideline item
     */
    public function update(Request $request)
    {
        $request->validate([
            'id'         => 'required|integer',
            'type'       => 'required|string|in:VISI,MISI,CORE_VALUE,PERATURAN',
            'title'      => 'required|string|max:255',
            'sort_order' => 'nullable|integer',
            'attachment' => 'nullable|file|max:20480',
        ]);

        $item = $this->guidelineService->updateGuideline($request->input('id'), $request);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diperbarui!',
                'data'    => $item,
            ]);
        }

        return redirect()->back()->with('success', 'Data berhasil diperbarui!');
    }

    /**
     * Show detail view
     */
    public function detail(Request $request)
    {
        $id = $request->query('id');
        $item = $this->guidelineService->findById($id);

        return view('humanresources.company-guideline.detail', [
            'item' => $item,
        ]);
    }

    /**
     * Delete guideline item
     */
    public function destroy(Request $request)
    {
        $id = $request->input('id');
        $this->guidelineService->deleteGuideline($id);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus.',
        ]);
    }
}

