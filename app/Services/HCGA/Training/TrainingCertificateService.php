<?php

namespace App\Services\HCGA\Training;

use App\Models\HCGA\Training\HcgaTraining;
use App\Models\HCGA\Training\HcgaTrainingCertificate;
use App\Models\HCGA\Training\HcgaTrainingParticipant;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facade as QrCode;

class TrainingCertificateService
{
    public function generateCertificateNo(HcgaTraining $training): string
    {
        $prefix = 'CERT/HCGA/' . date('Y/m') . '/';
        $count = HcgaTrainingCertificate::where('certificate_no', 'like', $prefix . '%')->count() + 1;
        return $prefix . sprintf('%04d', $count);
    }

    /**
     * Terbitkan sertifikat untuk semua peserta yang lulus evaluasi (belum punya sertifikat).
     */
    public function issueForPassedParticipants(int $trainingId, $user): array
    {
        $training = HcgaTraining::findOrFail($trainingId);
        $passedParticipants = HcgaTrainingParticipant::where('training_id', $trainingId)
            ->where('is_passed', true)
            ->get();

        $issued = [];
        $issuer = $user->username ?? 'system';

        foreach ($passedParticipants as $participant) {
            $existing = HcgaTrainingCertificate::where('participant_id', $participant->id)->first();
            if ($existing) {
                $issued[] = $existing;
                continue;
            }

            $cert = HcgaTrainingCertificate::create([
                'training_id' => $training->ID,
                'participant_id' => $participant->id,
                'certificate_no' => $this->generateCertificateNo($training),
                'verification_token' => Str::random(40),
                'issued_date' => date('Y-m-d'),
                'issued_by' => $issuer,
            ]);
            $issued[] = $cert;
        }

        return $issued;
    }

    /**
     * Render PDF Sertifikat Digital dengan QR Code verifikasi menggunakan Dompdf.
     */
    public function renderPdfBytes(HcgaTrainingCertificate $certificate): string
    {
        $certificate->load(['training', 'participant']);
        $verifyUrl = url('/hrd-training-certificate-verify/' . $certificate->verification_token);

        $qrSvg = QrCode::format('svg')->size(110)->margin(1)->generate($verifyUrl);
        $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);

        $html = view('humanresources.training.summary.certificate-pdf', [
            'certificate' => $certificate,
            'training' => $certificate->training,
            'participant' => $certificate->participant,
            'qrBase64' => $qrBase64,
            'verifyUrl' => $verifyUrl,
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return $dompdf->output();
    }
}
