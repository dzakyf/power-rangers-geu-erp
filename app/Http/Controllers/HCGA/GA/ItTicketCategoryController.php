<?php

namespace App\Http\Controllers\HCGA\GA;

use App\Http\Controllers\Controller;
use App\Services\GA\ItTicketCategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ItTicketCategoryController extends Controller
{
    use GaXml;

    /** @var ItTicketCategoryService */
    protected $categoryService;

    public function __construct(ItTicketCategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index()
    {
        abort_unless(can_menu('SI01161', 'view'), 403);

        $options = $this->categoryService->getDropdownOptions(Auth::user());

        return view('humanresources.ga.it-ticket.category-master', $options);
    }

    public function categoryData(Request $request)
    {
        abort_unless(can_menu('SI01161', 'view'), 403);

        $status = $request->query('status');
        $rows = $this->categoryService->listMainCategories($status ? (string) $status : null);

        $this->xmlHeader();
        echo '<rows>';
        $no = 1;
        foreach ($rows as $row) {
            $scopeLabel = $this->formatScopeLabel($row->Location ?? null, $row->Company_ID ?? null);
            $isSystem = $this->categoryService->isSystemDefaultCode($row->Code) ? '1' : '0';
            $subCountLabel = (int) ($row->Active_Subcategory_Count ?? 0) . ' aktif / ' . (int) ($row->Total_Subcategory_Count ?? 0) . ' total';

            echo '<row id="' . (int) $row->ID . '">';
            echo '<cell>' . $no . '</cell>';
            echo '<cell>' . $this->xml($row->Code) . '</cell>';
            echo '<cell>' . $this->xml($row->Name) . '</cell>';
            echo '<cell>' . $this->xml($scopeLabel) . '</cell>';
            echo '<cell>' . $this->xml($subCountLabel) . '</cell>';
            echo '<cell>' . $this->xml($row->Default_Priority ?: 'NORMAL') . '</cell>';
            echo '<cell>' . $this->xml($row->Team_Name ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($row->Sla_Name ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($row->Status) . '</cell>';
            echo '<cell>' . $this->xml($row->Location ?? '') . '</cell>';
            echo '<cell>' . $this->xml($row->Company_ID ?? '') . '</cell>';
            echo '<cell>' . ($row->Default_Team_ID ? (int) $row->Default_Team_ID : '') . '</cell>';
            echo '<cell>' . ($row->Sla_Policy_ID ? (int) $row->Sla_Policy_ID : '') . '</cell>';
            echo '<cell>' . $isSystem . '</cell>';
            echo '</row>';
            $no++;
        }
        echo '</rows>';
    }

    public function subcategoryData(Request $request)
    {
        abort_unless(can_menu('SI01161', 'view'), 403);

        $parentId = $request->filled('parent_id') ? (int) $request->query('parent_id') : null;
        $status = $request->query('status');
        $rows = $this->categoryService->listSubcategories($parentId, $status ? (string) $status : null);

        $this->xmlHeader();
        echo '<rows>';
        $no = 1;
        foreach ($rows as $row) {
            $scopeLabel = $this->formatScopeLabel($row->Location ?? null, $row->Company_ID ?? null);
            $parentDisplay = $row->Parent_Name ? ($row->Parent_Name . ' (' . $row->Parent_Code . ')') : '-';

            echo '<row id="' . (int) $row->ID . '">';
            echo '<cell>' . $no . '</cell>';
            echo '<cell>' . $this->xml($row->Code) . '</cell>';
            echo '<cell>' . $this->xml($row->Name) . '</cell>';
            echo '<cell>' . $this->xml($parentDisplay) . '</cell>';
            echo '<cell>' . $this->xml($scopeLabel) . '</cell>';
            echo '<cell>' . $this->xml($row->Default_Priority ?: 'NORMAL') . '</cell>';
            echo '<cell>' . $this->xml($row->Team_Name ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($row->Sla_Name ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($row->Status) . '</cell>';
            echo '<cell>' . ($row->Parent_ID ? (int) $row->Parent_ID : '') . '</cell>';
            echo '<cell>' . $this->xml($row->Location ?? '') . '</cell>';
            echo '<cell>' . $this->xml($row->Company_ID ?? '') . '</cell>';
            echo '<cell>' . ($row->Default_Team_ID ? (int) $row->Default_Team_ID : '') . '</cell>';
            echo '<cell>' . ($row->Sla_Policy_ID ? (int) $row->Sla_Policy_ID : '') . '</cell>';
            echo '</row>';
            $no++;
        }
        echo '</rows>';
    }

    public function saveCategory(Request $request)
    {
        $action = $request->filled('ID') ? 'edit' : 'create';
        abort_unless(can_menu('SI01161', $action), 403);

        $data = $request->validate([
            'ID'               => 'nullable|integer',
            'Code'             => 'required|string|max:50',
            'Name'             => 'required|string|max:150',
            'Scope_Mode'       => 'nullable|in:GLOBAL,SPECIFIC',
            'Location'         => 'nullable|string|max:50',
            'Company_ID'       => 'nullable|string|max:50',
            'Default_Priority' => 'nullable|in:LOW,NORMAL,HIGH,URGENT',
            'Default_Team_ID'  => 'nullable|integer',
            'Sla_Policy_ID'    => 'nullable|integer',
            'Status'           => 'nullable|in:ACTIVE,INACTIVE',
        ]);

        try {
            $saved = $this->categoryService->saveMainCategory($data, Auth::user());

            return response()->json([
                'status'  => 'success',
                'message' => 'Kategori IT berhasil disimpan.',
                'id'      => (int) $saved->ID,
                'record'  => $saved,
            ]);
        } catch (\Throwable $exception) {
            Log::warning('IT category save failed', ['message' => $exception->getMessage()]);
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 422);
        }
    }

    public function saveSubcategory(Request $request)
    {
        $action = $request->filled('ID') ? 'edit' : 'create';
        abort_unless(can_menu('SI01161', $action), 403);

        $data = $request->validate([
            'ID'               => 'nullable|integer',
            'Parent_ID'        => 'required|integer',
            'Code'             => 'required|string|max:50',
            'Name'             => 'required|string|max:150',
            'Scope_Mode'       => 'nullable|in:GLOBAL,SPECIFIC',
            'Location'         => 'nullable|string|max:50',
            'Company_ID'       => 'nullable|string|max:50',
            'Default_Priority' => 'nullable|in:LOW,NORMAL,HIGH,URGENT',
            'Default_Team_ID'  => 'nullable|integer',
            'Sla_Policy_ID'    => 'nullable|integer',
            'Status'           => 'nullable|in:ACTIVE,INACTIVE',
        ]);

        try {
            $saved = $this->categoryService->saveSubcategory($data, Auth::user());

            return response()->json([
                'status'  => 'success',
                'message' => 'Subkategori IT berhasil disimpan.',
                'id'      => (int) $saved->ID,
                'record'  => $saved,
            ]);
        } catch (\Throwable $exception) {
            Log::warning('IT subcategory save failed', ['message' => $exception->getMessage()]);
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 422);
        }
    }

    public function deactivate(Request $request)
    {
        abort_unless(can_menu('SI01161', 'delete'), 403);

        $data = $request->validate(['ID' => 'required|integer']);

        try {
            $record = $this->categoryService->deactivate((int) $data['ID'], Auth::user());

            return response()->json([
                'status'  => 'success',
                'message' => 'Data kategori IT berhasil dinonaktifkan.',
                'record'  => $record,
            ]);
        } catch (\Throwable $exception) {
            Log::warning('IT category deactivate failed', ['message' => $exception->getMessage()]);
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 422);
        }
    }

    public function options()
    {
        abort_unless(can_menu('SI01161', 'view'), 403);

        $options = $this->categoryService->getDropdownOptions(Auth::user());

        return response()->json([
            'status'              => 'success',
            'main_categories'     => $options['mainCategories'],
            'all_main_categories' => $options['allMainCategories'],
        ]);
    }

    protected function formatScopeLabel(?string $location, ?string $companyId): string
    {
        $loc = trim((string) $location);
        $comp = trim((string) $companyId);

        if ($loc === '' && $comp === '') {
            return 'Global';
        }

        return ($loc !== '' ? $loc : 'Semua Lokasi') . ' / ' . ($comp !== '' ? $comp : 'Semua Company');
    }
}
