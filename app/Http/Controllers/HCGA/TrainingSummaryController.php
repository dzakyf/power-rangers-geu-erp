<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Models\HCGA\Training\HcgaTraining;
use App\Models\HCGA\Training\HcgaTrainingCertificate;
use App\Services\HCGA\Training\TrainingCertificateService;
use App\Services\HCGA\Training\TrainingEvaluationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrainingSummaryController extends Controller
{
    protected $evalService;
    protected $certService;

    public function __construct(TrainingEvaluationService $evalService, TrainingCertificateService $certService)
    {
        $this->evalService = $evalService;
        $this->certService = $certService;
    }

    public function index()
    {
        $trainings = HcgaTraining::withCount('participants')->orderBy('ID', 'desc')->get();
        return view('humanresources.training.summary.index', compact('trainings'));
    }

    public function detail(Request $request)
    {
        $trainingId = (int) $request->input('training_id');
        $summary = $this->evalService->getTrainingSummary($trainingId);
        return view('humanresources.training.summary.detail', $summary);
    }

    public function issueCertificates(Request $request)
    {
        $trainingId = (int) $request->input('training_id');
        $issued = $this->certService->issueForPassedParticipants($trainingId, Auth::user());

        return response()->json([
            'status' => 'success',
            'message' => count($issued) . ' Sertifikat Digital berhasil diterbitkan/diverifikasi untuk peserta lulus.',
        ]);
    }

    public function downloadCertificate($certificateId)
    {
        $certificate = HcgaTrainingCertificate::with(['training', 'participant'])->findOrFail((int) $certificateId);
        $pdfBytes = $this->certService->renderPdfBytes($certificate);
        $filename = str_replace(['/', '\\'], '-', $certificate->certificate_no) . '.pdf';

        return response($pdfBytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    public function verifyCertificate($token)
    {
        $certificate = HcgaTrainingCertificate::with(['training', 'participant'])
            ->where('verification_token', $token)
            ->first();

        return view('humanresources.training.summary.certificate-verify', compact('certificate'));
    }
}
