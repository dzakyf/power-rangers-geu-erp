<?php

namespace App\Http\Controllers\HCGA\GA;

use App\Http\Controllers\Controller;
use App\Services\GA\GaCleaningService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facade as QrCode;

class CleaningController extends Controller
{
    use GaXml;

    protected $cleaning;

    public function __construct(GaCleaningService $cleaning)
    {
        $this->cleaning = $cleaning;
    }

    public function index()
    {
        $areas = DB::table('ga_cleaning_area')->where('Status', 'ACTIVE')->get();
        return view('humanresources.ga.cleaning.index', compact('areas'));
    }

    public function areaData(Request $request)
    {
        $areas = DB::table('ga_cleaning_area')->orderBy('id', 'DESC')->get();

        $this->xmlHeader();
        echo '<rows>';
        $no = 1;
        foreach ($areas as $a) {
            echo '<row id="' . (int) $a->id . '">';
            echo '<cell>' . $no++ . '</cell>';
            echo '<cell>' . $this->xml($a->Area_Code) . '</cell>';
            echo '<cell>' . $this->xml($a->Area_Name) . '</cell>';
            echo '<cell>' . $this->xml($a->Building . ' - ' . $a->Floor) . '</cell>';
            echo '<cell>' . $this->xml($a->Category) . '</cell>';
            echo '<cell>' . $this->xml($a->Qr_Code ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($a->Status) . '</cell>';
            echo '</row>';
        }
        echo '</rows>';
    }

    public function saveArea(Request $request)
    {
        $validated = $request->validate([
            'Area_Code' => 'nullable|string|max:60',
            'Area_Name' => 'required|string|max:150',
            'Building' => 'required|string|max:100',
            'Floor' => 'required|string|max:50',
            'Category' => 'nullable|string|max:50',
            'Qr_Code' => 'nullable|string|max:100',
            'Location' => 'nullable|string|max:50',
            'Remarks' => 'nullable|string',
        ]);

        try {
            $area = $this->cleaning->registerArea($validated, Auth::user());
            return response()->json(['success' => true, 'message' => 'Area pembersihan berhasil disimpan.', 'data' => $area]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function scheduleData(Request $request)
    {
        $schedules = DB::table('ga_cleaning_schedule as s')
            ->leftJoin('ga_cleaning_area as a', 's.Area_ID', '=', 'a.id')
            ->select('s.*', 'a.Area_Name', 'a.Building', 'a.Floor')
            ->orderBy('s.id', 'DESC')
            ->get();

        $this->xmlHeader();
        echo '<rows>';
        $no = 1;
        foreach ($schedules as $s) {
            echo '<row id="' . (int) $s->id . '">';
            echo '<cell>' . $no++ . '</cell>';
            echo '<cell>' . $this->xml($s->Schedule_No) . '</cell>';
            echo '<cell>' . $this->xml($s->Area_Name . ' (' . $s->Building . ' Lt.' . $s->Floor . ')') . '</cell>';
            echo '<cell>' . $this->xml($s->Frequency) . '</cell>';
            echo '<cell>' . $this->xml($s->Shift) . '</cell>';
            echo '<cell>' . $this->xml($s->Assignee_Name ?: $s->Assignee_Type) . '</cell>';
            echo '<cell>' . $this->xml($s->Status) . '</cell>';
            echo '</row>';
        }
        echo '</rows>';
    }

    public function saveSchedule(Request $request)
    {
        $validated = $request->validate([
            'Area_ID' => 'required|integer',
            'Frequency' => 'required|string|max:30',
            'Shift' => 'required|string|max:30',
            'Assignee_Type' => 'required|string|max:30',
            'Assignee_Name' => 'nullable|string|max:150',
            'Notes' => 'nullable|string',
        ]);

        try {
            $sched = $this->cleaning->createSchedule($validated, Auth::user());
            return response()->json(['success' => true, 'message' => 'Jadwal pembersihan berhasil dibuat.', 'data' => $sched]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function generateTasks(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());
        try {
            $tasks = $this->cleaning->generateTasksForDate($date, Auth::user());
            return response()->json(['success' => true, 'message' => count($tasks) . ' task berhasil digenerate untuk tanggal ' . $date]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function taskData(Request $request)
    {
        $query = DB::table('ga_cleaning_task as t')
            ->leftJoin('ga_cleaning_area as a', 't.Area_ID', '=', 'a.id')
            ->select('t.*', 'a.Area_Name', 'a.Building', 'a.Floor', 'a.Qr_Code');

        if ($request->filled('date')) {
            $query->where('t.Task_Date', $request->input('date'));
        }
        if ($request->filled('status')) {
            $query->where('t.Status', $request->input('status'));
        }

        $tasks = $query->orderBy('t.id', 'DESC')->get();

        $this->xmlHeader();
        echo '<rows>';
        $no = 1;
        foreach ($tasks as $t) {
            $photoBeforeCell = !empty($t->Photo_Before) ? '<a href="' . asset($t->Photo_Before) . '" target="_blank" class="badge bg-info text-white badge-info"><i class="fa fa-image me-1 mr-1"></i> Lihat</a>' : '-';
            $photoAfterCell = !empty($t->Photo_After) ? '<a href="' . asset($t->Photo_After) . '" target="_blank" class="badge bg-success text-white badge-success"><i class="fa fa-image me-1 mr-1"></i> Lihat</a>' : '-';

            echo '<row id="' . (int) $t->id . '">';
            echo '<cell>' . $no++ . '</cell>';
            echo '<cell>' . $this->xml($t->Task_No) . '</cell>';
            echo '<cell>' . $this->xml($t->Area_Name) . '</cell>';
            echo '<cell>' . $this->xml($t->Task_Date) . '</cell>';
            echo '<cell>' . $this->xml($t->Shift) . '</cell>';
            echo '<cell>' . $this->xml($t->Executor_Name ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($t->Scan_In_Time ?: '-') . '</cell>';
            echo '<cell><![CDATA[' . $photoBeforeCell . ']]></cell>';
            echo '<cell>' . $this->xml($t->Completed_Time ?: '-') . '</cell>';
            echo '<cell><![CDATA[' . $photoAfterCell . ']]></cell>';
            echo '<cell>' . $this->xml($t->Status) . '</cell>';
            echo '</row>';
        }
        echo '</rows>';
    }

    public function startTask(Request $request)
    {
        $validated = $request->validate([
            'Task_ID' => 'required|integer',
            'Qr_Code' => 'required|string',
            'Photo_Before' => 'nullable|file|image|max:5120',
        ]);

        $photoBeforePath = null;
        if ($request->hasFile('Photo_Before')) {
            $file = $request->file('Photo_Before');
            if ($file->isValid()) {
                $ext = $file->getClientOriginalExtension();
                $filename = 'cln_before_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $subDir = "filevendor/cleaning/" . date('Y/m');
                $targetDir = public_path($subDir);
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
                $file->move($targetDir, $filename);
                $photoBeforePath = $subDir . '/' . $filename;
            }
        }

        try {
            $task = $this->cleaning->startTaskWithQrScan($validated['Task_ID'], $validated['Qr_Code'], $photoBeforePath, Auth::user());
            return response()->json(['success' => true, 'message' => 'Check-in mulai kerja berhasil via scan QR.', 'data' => $task]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function completeTask(Request $request)
    {
        $validated = $request->validate([
            'Task_ID' => 'required|integer',
            'Checklist' => 'nullable|array',
            'Notes' => 'nullable|string',
            'Photo_After' => 'nullable|file|image|max:5120',
        ]);

        $photoAfterPath = null;
        if ($request->hasFile('Photo_After')) {
            $file = $request->file('Photo_After');
            if ($file->isValid()) {
                $ext = $file->getClientOriginalExtension();
                $filename = 'cln_after_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $subDir = "filevendor/cleaning/" . date('Y/m');
                $targetDir = public_path($subDir);
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
                $file->move($targetDir, $filename);
                $photoAfterPath = $subDir . '/' . $filename;
            }
        }

        try {
            $task = $this->cleaning->completeTask($validated['Task_ID'], $validated['Checklist'] ?? [], $validated['Notes'] ?? '', $photoAfterPath, Auth::user());
            return response()->json(['success' => true, 'message' => 'Task pembersihan berhasil diselesaikan dengan foto kondisi akhir.', 'data' => $task]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function inspectionData(Request $request)
    {
        $inspections = DB::table('ga_cleaning_inspection as i')
            ->leftJoin('ga_cleaning_area as a', 'i.Area_ID', '=', 'a.id')
            ->select('i.*', 'a.Area_Name')
            ->orderBy('i.id', 'DESC')
            ->get();

        $this->xmlHeader();
        echo '<rows>';
        $no = 1;
        foreach ($inspections as $i) {
            $ticketCell = '-';
            if (!empty($i->Follow_Up_Ticket_No)) {
                $tRow = DB::table('ticket')->where('Ticket_No', $i->Follow_Up_Ticket_No)->first(['ID']);
                if ($tRow) {
                    $ticketCell = '<a href="' . url('hrd-ga-ticket-detail?ID=' . $tRow->ID) . '" target="_blank" class="badge bg-warning text-dark badge-warning"><i class="fa fa-ticket me-1 mr-1"></i>' . $this->xml($i->Follow_Up_Ticket_No) . '</a>';
                } else {
                    $ticketCell = '<span class="badge bg-warning text-dark badge-warning">' . $this->xml($i->Follow_Up_Ticket_No) . '</span>';
                }
            }

            $statusBadge = $i->Result_Status;
            $auditRoundText = ((int) $i->Audit_Round > 1) ? ' <span class="badge bg-info text-white badge-info">Round ' . (int) $i->Audit_Round . '</span>' : '';
            if ($i->Result_Status === 'PASSED') {
                $statusBadge = '<span class="badge bg-success text-white badge-success fw-bold font-weight-bold">PASSED</span>' . $auditRoundText;
            } elseif ($i->Result_Status === 'NEEDS_IMPROVEMENT') {
                $statusBadge = '<span class="badge bg-warning text-dark badge-warning fw-bold font-weight-bold">NEEDS_IMPROVEMENT</span>' . $auditRoundText;
            } elseif ($i->Result_Status === 'FAILED') {
                $statusBadge = '<span class="badge bg-danger text-white badge-danger fw-bold font-weight-bold">FAILED</span>' . $auditRoundText;
            }

            echo '<row id="' . (int) $i->id . '">';
            echo '<cell>' . $no++ . '</cell>';
            echo '<cell>' . $this->xml($i->Inspection_No) . '</cell>';
            echo '<cell>' . $this->xml($i->Area_Name) . '</cell>';
            echo '<cell>' . $this->xml($i->Inspection_Date) . '</cell>';
            echo '<cell>' . $this->xml($i->Inspector_Name) . '</cell>';
            echo '<cell>' . number_format($i->Score, 1) . '</cell>';
            echo '<cell><![CDATA[' . $statusBadge . ']]></cell>';
            echo '<cell><![CDATA[' . $ticketCell . ']]></cell>';
            echo '</row>';
        }
        echo '</rows>';
    }

    public function saveInspection(Request $request)
    {
        $validated = $request->validate([
            'Area_ID' => 'required|integer',
            'Score' => 'required|numeric|min:0|max:100',
            'Finding_Notes' => 'nullable|string',
            'Spawn_Follow_Up_Ticket' => 'nullable|boolean',
        ]);

        try {
            $insp = $this->cleaning->recordInspection($validated, Auth::user());
            return response()->json(['success' => true, 'message' => 'Inspeksi supervisor berhasil dicatat.', 'data' => $insp]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function inspectionDetail(Request $request)
    {
        $id = (int) $request->query('id');
        $insp = DB::table('ga_cleaning_inspection as i')
            ->leftJoin('ga_cleaning_area as a', 'i.Area_ID', '=', 'a.id')
            ->select('i.*', 'a.Area_Name', 'a.Building', 'a.Floor')
            ->where('i.id', $id)
            ->first();

        if (!$insp) {
            return response()->json(['success' => false, 'message' => 'Data inspeksi tidak ditemukan.'], 404);
        }

        $logs = $this->cleaning->getInspectionLogs($id);

        return response()->json([
            'success' => true,
            'data' => [
                'inspection' => $insp,
                'logs' => $logs,
            ],
        ]);
    }

    public function saveReinspection(Request $request)
    {
        $validated = $request->validate([
            'Inspection_ID' => 'required|integer',
            'Score' => 'required|numeric|min:0|max:100',
            'Notes' => 'required|string',
        ]);

        try {
            $insp = $this->cleaning->recordReinspection($validated['Inspection_ID'], $validated, Auth::user());
            return response()->json(['success' => true, 'message' => 'Inspeksi ulang berhasil dicatat. Status dan riwayat telah diperbarui.', 'data' => $insp]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Endpoint untuk merender gambar SVG QR Code Area Pembersihan.
     */
    public function areaQrImage(Request $request)
    {
        $code = trim((string) $request->query('code', ''));
        abort_if($code === '', 404);

        $svg = QrCode::format('svg')->size(300)->margin(1)->generate($code);

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * Halaman cetak stiker / label QR Area Pembersihan (siap print A4 & mPDF).
     */
    public function printAreaQr(Request $request)
    {
        $allAreas = DB::table('ga_cleaning_area')->where('Status', 'ACTIVE')->orderBy('Building')->orderBy('Floor')->orderBy('Area_Name')->get();

        $idsParam = $request->query('ids', []);
        if (!is_array($idsParam)) {
            $idsParam = trim((string) $idsParam) === '' ? [] : explode(',', (string) $idsParam);
        }
        $selectedIds = array_values(array_unique(array_filter(array_map('intval', $idsParam))));
        $hasFilter = $request->has('ids') || $request->has('filter');

        if ($hasFilter) {
            $selectedAreas = $allAreas->filter(function ($a) use ($selectedIds) {
                return in_array((int) $a->id, $selectedIds, true);
            })->values();
        } else {
            $selectedAreas = $allAreas;
        }

        $action = strtolower(trim((string) $request->query('action', '')));
        $download = $request->boolean('download') || $action === 'download';
        $generatePdf = $action === 'pdf' || $action === 'download' || $download;

        if ($generatePdf) {
            if ($selectedAreas->isEmpty()) {
                return redirect()->back()->with('warning', 'Pilih minimal satu area untuk dicetak.');
            }

            $qrImages = [];
            foreach ($selectedAreas as $area) {
                $code = $area->Qr_Code ?: $area->Area_Code;
                $svg = QrCode::format('svg')->size(300)->margin(1)->generate($code);
                $qrImages[(int) $area->id] = 'data:image/svg+xml;base64,' . base64_encode($svg);
            }

            $html = view('humanresources.ga.cleaning.print-pdf', compact('selectedAreas', 'qrImages'))->render();

            $tempDir = storage_path('app/mpdf');
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0775, true);
            }

            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'orientation' => 'P',
                'margin_left' => 8,
                'margin_right' => 8,
                'margin_top' => 8,
                'margin_bottom' => 8,
                'tempDir' => $tempDir,
            ]);

            $mpdf->SetTitle('Label QR Area Kebersihan GA');
            $mpdf->WriteHTML($html);

            $filename = 'QR-Cleaning-' . Carbon::now()->format('YmdHis') . '.pdf';
            $disposition = $download ? 'attachment' : 'inline';

            return response(
                $mpdf->Output($filename, \Mpdf\Output\Destination::STRING_RETURN),
                200,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => $disposition . '; filename="' . $filename . '"',
                ]
            );
        }

        return view('humanresources.ga.cleaning.print-qr', [
            'allAreas' => $allAreas,
            'selectedAreas' => $selectedAreas,
            'selectedIds' => $selectedIds,
            'hasFilter' => $hasFilter,
        ]);
    }

    public function taskChecklistTemplate(Request $request)
    {
        $taskId = (int) $request->query('task_id');
        $task = DB::table('ga_cleaning_task')->where('id', $taskId)->first();

        if (!$task) {
            return response()->json(['success' => false, 'message' => 'Tugas pembersihan tidak ditemukan.'], 404);
        }

        $area = DB::table('ga_cleaning_area')->where('id', (int) $task->Area_ID)->first();
        $templates = $this->cleaning->getChecklistTemplateForArea($task->Area_ID);

        return response()->json([
            'success' => true,
            'data' => [
                'task' => $task,
                'area' => $area,
                'templates' => $templates,
            ],
        ]);
    }
}

