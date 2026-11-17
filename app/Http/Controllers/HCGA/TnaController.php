<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Models\HCGA\Training\HcgaTnaRequest;
use App\Models\HCGA\Training\HcgaTrainingProgram;
use App\Services\Approval\TrainingApprovalService;
use App\Services\HCGA\Training\TnaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TnaController extends Controller
{
    protected $tnaService;
    protected $approvalService;

    public function __construct(TnaService $tnaService, TrainingApprovalService $approvalService)
    {
        $this->tnaService = $tnaService;
        $this->approvalService = $approvalService;
    }

    public function index()
    {
        return view('humanresources.training.tna.index');
    }

    public function indexXml()
    {
        header('Content-type: text/xml');
        echo "<?xml version='1.0' encoding='utf-8'?>\n<rows>";
        $rows = $this->tnaService->listTna();
        $no = 1;
        foreach ($rows as $row) {
            echo "<row id=\"{$row->id}\">";
            echo "<cell>{$no}</cell>";
            echo "<cell>" . htmlspecialchars((string) $row->tna_no) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $row->department) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $row->fiscal_year) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $row->title) . "</cell>";
            echo "<cell>" . (int) $row->items_count . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $row->requested_by) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $row->status) . "</cell>";
            echo "</row>";
            $no++;
        }
        echo "</rows>";
    }

    public function create(Request $request)
    {
        $tna = $request->filled('id')
            ? HcgaTnaRequest::with('items')->findOrFail((int) $request->id)
            : null;
        $programs = HcgaTrainingProgram::where('status', 'ACTIVE')->orderBy('program_name')->get();
        $employees = DB::table('users')
            ->select('username', 'name', 'Department', 'Position')
            ->orderBy('name')
            ->get();
        $departments = DB::table('users')
            ->whereNotNull('Department')
            ->where('Department', '!=', '')
            ->distinct()
            ->orderBy('Department')
            ->pluck('Department');

        return view('humanresources.training.tna.form', compact('tna', 'programs', 'employees', 'departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'department' => 'required|string|max:150',
            'fiscal_year' => 'required|string|max:10',
            'title' => 'required|string|max:255',
        ]);

        $tna = $this->tnaService->createOrUpdate(
            $request->only(['department', 'fiscal_year', 'title', 'background']),
            $request->input('items', []),
            Auth::user(),
            $request->filled('id') ? (int) $request->id : null
        );

        return response()->json([
            'status' => 'success',
            'message' => 'TNA berhasil disimpan.',
            'data' => $tna,
        ]);
    }

    public function detail(Request $request)
    {
        $tna = HcgaTnaRequest::with(['items.program'])->findOrFail((int) $request->id);
        return view('humanresources.training.tna.detail', compact('tna'));
    }

    public function submitApproval(Request $request)
    {
        $tna = HcgaTnaRequest::findOrFail((int) $request->id);
        $approvalReq = $this->approvalService->submitTna($tna, Auth::user());

        return response()->json([
            'status' => 'success',
            'message' => 'TNA berhasil diajukan ke Approval Inbox.',
            'approval_request_id' => $approvalReq->ID ?? null,
        ]);
    }
}
