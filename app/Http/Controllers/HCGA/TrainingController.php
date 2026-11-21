<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Models\HCGA\Training\HcgaTnaRequest;
use App\Models\HCGA\Training\HcgaTraining;
use App\Models\HCGA\Training\HcgaTrainingProgram;
use App\Models\HCGA\Training\HcgaTrainingTrainer;
use App\Services\Approval\TrainingApprovalService;
use App\Services\HCGA\Training\TrainingScheduleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TrainingController extends Controller
{
    protected $scheduleService;
    protected $approvalService;

    public function __construct(TrainingScheduleService $scheduleService, TrainingApprovalService $approvalService)
    {
        $this->scheduleService = $scheduleService;
        $this->approvalService = $approvalService;
    }

    public function index()
    {
        return view('humanresources.training.training-index');
    }

    public function indexXml()
    {
        header('Content-type: text/xml');
        echo "<?xml version='1.0' encoding='utf-8'?>\n<rows>";

        $rows = $this->scheduleService->listTrainings();
        $nomor = 1;
        foreach ($rows as $Fetch) {
            echo "<row id=\"{$Fetch->ID}\">";
            echo "<cell>{$nomor}</cell>";
            echo "<cell>" . htmlspecialchars((string) $Fetch->Task_No) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $Fetch->Training_Name) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) ($Fetch->batch_type ?? 'REGULAR')) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $Fetch->Training_Type) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $Fetch->Training_Method) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) ($Fetch->trainer_name ?? '-')) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $Fetch->Start_Date) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $Fetch->End_Date) . "</cell>";
            echo "<cell>" . number_format((float) ($Fetch->passing_grade ?? 75), 2) . "</cell>";
            echo "<cell>" . (int) ($Fetch->participants_count ?? 0) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $Fetch->Status) . "</cell>";
            echo "</row>";
            $nomor++;
        }

        echo "</rows>";
    }

    public function new(Request $request)
    {
        $user = Auth::user();
        $Task_No = $this->scheduleService->generateTaskNo($user);
        $trCategory = DB::table('master_training_hcga_category')->orderBy('Category')->get();
        $programs = HcgaTrainingProgram::where('status', 'ACTIVE')->orderBy('program_name')->get();
        $tnas = HcgaTnaRequest::withCount('items')->where('status', 'APPROVED')->orderBy('id', 'desc')->get();
        $trainers = HcgaTrainingTrainer::where('Status', 'ACTIVE')->orderBy('Trainer_Name')->get();
        $employees = DB::table('users')->select('username', 'name', 'Department')->orderBy('name')->get();
        $selectedTnaId = $request->input('tna_id');

        return view('humanresources.training.training-new', compact(
            'Task_No', 'trCategory', 'programs', 'tnas', 'trainers', 'employees', 'selectedTnaId'
        ));
    }

    public function tnaPayload(Request $request)
    {
        $request->validate([
            'tna_id' => 'required|integer|exists:hcga_tna_requests,id',
            'training_id' => 'nullable|integer',
        ]);

        $payload = $this->scheduleService->getTnaSchedulePayload(
            (int) $request->input('tna_id'),
            $request->filled('training_id') ? (int) $request->input('training_id') : null
        );

        return response()->json([
            'status' => 'success',
            'data' => $payload,
        ]);
    }

    public function newSubmit(Request $request)
    {
        $request->validate([
            'Training_Name' => 'required|string|max:255',
            'trainer_id' => 'required|integer|exists:master_training_hcga_trainer,ID',
            'Start_Date' => 'required|date',
            'End_Date' => 'required|date|after_or_equal:Start_Date',
        ]);

        $training = $this->scheduleService->createOrUpdate(
            $request->only([
                'Task_No', 'tna_id', 'program_id', 'trainer_id', 'trainer_name',
                'Training_Name', 'Training_Category', 'Training_Type', 'Training_Method',
                'Training_Description', 'Training_Objective', 'Training_Competence',
                'Start_Date', 'End_Date', 'passing_grade', 'Training_Location', 'Location',
            ]),
            $request->input('curriculums', []),
            $request->input('participants', []),
            Auth::user(),
            null,
            [
                'skip_tna_autopopulate' => filter_var($request->input('participants_explicit', false), FILTER_VALIDATE_BOOLEAN),
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Jadwal Training berhasil disimpan.',
            'data' => $training,
        ]);
    }

    public function detail(Request $request)
    {
        $id = (int) ($request->ID ?: $request->id);
        $training = HcgaTraining::with(['curriculums', 'participants.tnaItem', 'tna', 'program'])->findOrFail($id);
        $trCategory = DB::table('master_training_hcga_category')->orderBy('Category')->get();
        $programs = HcgaTrainingProgram::where(function ($q) use ($training) {
            $q->where('status', 'ACTIVE');
            if ($training->program_id) {
                $q->orWhere('id', $training->program_id);
            }
        })->orderBy('program_name')->get();
        $tnas = HcgaTnaRequest::withCount('items')
            ->where(function ($q) use ($training) {
                $q->where('status', 'APPROVED');
                if ($training->tna_id) {
                    $q->orWhere('id', $training->tna_id);
                }
            })
            ->orderBy('id', 'desc')
            ->get();
        $trainers = HcgaTrainingTrainer::where(function ($q) use ($training) {
            $q->where('Status', 'ACTIVE');
            if ($training->trainer_id) {
                $q->orWhere('ID', $training->trainer_id);
            }
        })->orderBy('Trainer_Name')->get();
        $employees = DB::table('users')->select('username', 'name', 'Department')->orderBy('name')->get();
        $lockedParticipantIds = $this->scheduleService->getLockedParticipantIds((int) $training->ID);
        $isClosed = strtoupper((string) $training->Status) === 'CLOSED';

        return view('humanresources.training.training-detail', compact(
            'training', 'trCategory', 'programs', 'tnas', 'trainers', 'employees', 'lockedParticipantIds', 'isClosed'
        ));
    }

    public function detailSubmit(Request $request)
    {
        $id = (int) ($request->ID ?: $request->id);
        $request->validate([
            'Training_Name' => 'required|string|max:255',
            'trainer_id' => 'required|integer|exists:master_training_hcga_trainer,ID',
            'Start_Date' => 'required|date',
            'End_Date' => 'required|date|after_or_equal:Start_Date',
            'tna_id' => 'nullable|integer|exists:hcga_tna_requests,id',
            'program_id' => 'nullable|integer|exists:hcga_training_programs,id',
            'removed_participant_ids' => 'nullable|array',
            'removed_participant_ids.*' => 'integer',
        ]);

        try {
            $training = $this->scheduleService->createOrUpdate(
                $request->only([
                    'tna_id', 'program_id', 'trainer_id', 'trainer_name',
                    'Training_Name', 'Training_Category', 'Training_Type', 'Training_Method',
                    'Training_Description', 'Training_Objective', 'Training_Competence',
                    'Start_Date', 'End_Date', 'passing_grade', 'Training_Location', 'Location',
                ]),
                $request->input('curriculums', []),
                $request->input('participants', []),
                Auth::user(),
                $id,
                [
                    'removed_participant_ids' => $request->input('removed_participant_ids', []),
                ]
            );
        } catch (\RuntimeException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Jadwal Training berhasil diperbarui.',
            'data' => $training,
        ]);
    }

    public function submitApproval(Request $request)
    {
        $id = (int) ($request->ID ?: $request->id);
        $training = HcgaTraining::findOrFail($id);
        $approvalReq = $this->approvalService->submitTraining($training, Auth::user());

        return response()->json([
            'status' => 'success',
            'message' => 'Jadwal Training berhasil diajukan ke Approval Inbox.',
            'approval_request_id' => $approvalReq->ID ?? null,
        ]);
    }

    public function close(Request $request)
    {
        $id = (int) ($request->ID ?: $request->id);
        $training = HcgaTraining::findOrFail($id);
        $training->Status = 'CLOSED';
        $training->Updated_By = Auth::user()->username ?? 'system';
        $training->Updated_Date = now();
        $training->save();

        return response()->json(['status' => 'success', 'message' => 'Training berhasil ditutup (CLOSED).']);
    }

    public function approval()
    {
        return redirect('hrd-approval-inbox');
    }

    public function approve(Request $request)
    {
        $rowId = (int) ($request->input('gr_id') ?: $request->input('ID') ?: $request->input('id'));
        $actionType = (string) $request->input('!nativeeditor_status', 'updated');

        if ($rowId <= 0) {
            return response('<data><action type="error" sid="' . $rowId . '" tid="' . $rowId . '">ID tidak valid</action></data>', 422, ['Content-Type' => 'text/xml']);
        }

        $training = HcgaTraining::find($rowId);
        if ($training) {
            $training->Status = $request->input('Status', $training->Status);
            $training->Updated_By = Auth::user()->username ?? 'system';
            $training->Updated_Date = now();
            $training->save();
        }

        return response('<data><action type="' . htmlspecialchars($actionType, ENT_QUOTES, 'UTF-8') . '" sid="' . $rowId . '" tid="' . $rowId . '"/></data>', 200, ['Content-Type' => 'text/xml']);
    }
}
