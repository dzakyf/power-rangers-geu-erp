<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Models\HCGA\Training\HcgaTrainingParticipant;
use App\Services\HCGA\Training\TrainingCoachingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TrainingCoachingController extends Controller
{
    protected $coachingService;

    public function __construct(TrainingCoachingService $coachingService)
    {
        $this->coachingService = $coachingService;
    }

    public function index(Request $request)
    {
        $trainingId = $request->filled('training_id') ? (int) $request->training_id : null;
        $failedParticipants = $this->coachingService->getFailedParticipants($trainingId);
        return view('humanresources.training.coaching.index', compact('failedParticipants'));
    }

    public function form(Request $request)
    {
        $participant = HcgaTrainingParticipant::with(['training', 'coachings'])
            ->findOrFail((int) $request->participant_id);
        $mentors = DB::table('users')->select('username', 'name', 'Department')->orderBy('name')->get();
        return view('humanresources.training.coaching.form', compact('participant', 'mentors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'participant_id' => 'required|integer',
            'mentor_username' => 'required|string|max:150',
            'coaching_date' => 'required|date',
            'notes' => 'required|string',
            'follow_up_type' => 'required|in:COACHING_ONLY,REFRESHMENT',
            'refreshment_start_date' => 'nullable|date',
            'refreshment_end_date' => 'nullable|date',
        ]);

        $coaching = $this->coachingService->recordCoaching($request->all(), Auth::user());

        return response()->json([
            'status' => 'success',
            'message' => 'Coaching / Training Refreshment berhasil dicatat.',
            'data' => $coaching,
        ]);
    }
}
