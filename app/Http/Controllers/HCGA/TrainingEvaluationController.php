<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Models\HCGA\Training\HcgaTraining;
use App\Models\HCGA\Training\HcgaTrainingParticipant;
use App\Services\HCGA\Training\TrainingEvaluationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrainingEvaluationController extends Controller
{
    protected $evalService;

    public function __construct(TrainingEvaluationService $evalService)
    {
        $this->evalService = $evalService;
    }

    public function index()
    {
        $trainings = HcgaTraining::withCount('participants')->orderBy('ID', 'desc')->get();
        return view('humanresources.training.evaluation.index', compact('trainings'));
    }

    public function scoreForm(Request $request)
    {
        $trainingId = (int) $request->input('training_id');
        $training = HcgaTraining::with('participants')->findOrFail($trainingId);
        return view('humanresources.training.evaluation.score-form', compact('training'));
    }

    public function saveScores(Request $request)
    {
        $request->validate([
            'training_id' => 'required|integer',
            'scores' => 'required|array',
        ]);

        $updated = $this->evalService->saveParticipantScores(
            (int) $request->training_id,
            $request->input('scores', []),
            Auth::user()
        );

        return response()->json([
            'status' => 'success',
            'message' => count($updated) . ' penilaian peserta berhasil disimpan.',
        ]);
    }

    public function feedbackForm(Request $request)
    {
        $trainingId = (int) $request->input('training_id');
        $training = HcgaTraining::with('participants')->findOrFail($trainingId);
        $participantId = $request->input('participant_id');
        $selectedParticipant = $participantId
            ? HcgaTrainingParticipant::find($participantId)
            : $training->participants->where('employee_username', Auth::user()->username)->first();

        $trainerAspects = TrainingEvaluationService::TRAINER_ASPECTS;
        $materialAspects = TrainingEvaluationService::MATERIAL_ASPECTS;

        return view('humanresources.training.evaluation.feedback-form', compact(
            'training', 'selectedParticipant', 'trainerAspects', 'materialAspects'
        ));
    }

    public function saveFeedback(Request $request)
    {
        $request->validate([
            'training_id' => 'required|integer',
            'participant_id' => 'required|integer',
            'trainer_ratings' => 'required|array',
            'material_ratings' => 'required|array',
        ]);

        $this->evalService->submitFeedback(
            (int) $request->training_id,
            (int) $request->participant_id,
            $request->input('trainer_ratings', []),
            $request->input('material_ratings', []),
            $request->input('comments')
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Evaluasi Pemateri & Materi berhasil disimpan.',
        ]);
    }
}
