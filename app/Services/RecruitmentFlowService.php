<?php
/**
 * RecruitmentFlowService - Service layer untuk alur recruitment
 * 
 * Mengelola transisi status candidate melalui tahapan:
 * CREATED → UPLOADED → VERIFICATION → PSIKOTEST → INTERVIEW_HR → 
 * INTERVIEW_USER → TECHNICAL_TEST → MCU → OFFERING → HIRED
 * 
 * Status colors untuk visual indicator di grid:
 * - pending/CREATED   : Abu-abu (#e2e3e5)
 * - uploaded/SEEK     : Kuning (#fff3cd)
 * - verification/PSIKOTEST : Biru (#cce5ff)
 * - interview/technical  : Biru (#cce5ff)
 * - mcu/offering     : Hijau muda (#d4edda)
 * - hired            : Hijau gelap (#28a745)
 * - rejected/declined: Merah (#f8d7da)
 */
namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RecruitmentFlowService
{
    /**
     * Definisi tahapan rekrutmen dengan urutan yang benar
     * 
     * Urutan alur:
     * 1. CREATED (candidate baru input)
     * 2. UPLOADED (dokumen diupload)
     * 3. VERIFICATION (verifikasi dokumen)
     * 4. PSIKOTEST (test psikologi)
     * 5. INTERVIEW_HR (interview HR)
     * 6. INTERVIEW_USER (interview user/atasan)
     * 7. TECHNICAL_TEST (tes teknik/kompetensi)
     * 8. MCU (medical check up)
     * 9. OFFERING (offering letter)
     * 10. HIRED (diterima)
     */
    const STAGES = [
        'CREATED'           => ['next' => 'UPLOADED',     'label' => 'Input Candidate'],
        'UPLOADED'         => ['next' => 'VERIFICATION',  'label' => 'Upload Dokumen'],
        'VERIFICATION'     => ['next' => 'PSIKOTEST',     'label' => 'Verifikasi Dokumen'],
        'PSIKOTEST'        => ['next' => 'INTERVIEW_HR',  'label' => 'Psikotest'],
        'INTERVIEW_HR'     => ['next' => 'INTERVIEW_USER','label' => 'Interview HR'],
        'INTERVIEW_USER'   => ['next' => 'TECHNICAL_TEST','label' => 'Interview User'],
        'TECHNICAL_TEST'   => ['next' => 'MCU',           'label' => 'Tes Teknik'],
        'MCU'              => ['next' => 'OFFERING',      'label' => 'MCU (Medical Check Up)'],
        'OFFERING'         => ['next' => 'HIRED',         'label' => 'Offering Letter'],
        'HIRED'            => ['next' => null,            'label' => 'Hired (Completed)'],
        'REJECTED'         => ['next' => null,            'label' => 'Rejected'],
        'DECLINED'         => ['next' => null,            'label' => 'Declined'],
    ];

    /**
     * Color mapping untuk visual indicator di grid
     * 
     * Kategori warna:
     * - Abu-abu  : Pending/Baru (belum dimulai)
     * - Kuning   : Process/Uploaded (sedang dalam proses)
     * - Biru     : Interview/Test stages (sedang dievaluasi)
     * - Hijau    : Selesai/Diterima (completed)
     * - Merah    : Ditolak/Declined (failed)
     */
    const STAGE_COLORS = [
        // Pending stages (belum dimulai)
        'CREATED'       => ['bg' => '#e2e3e5', 'text' => '#383d41', 'label' => 'Baru'],
        'PENDING'       => ['bg' => '#e2e3e5', 'text' => '#383d41', 'label' => 'Pending'],
        
        // Process stages (sedang berjalan)
        'UPLOADED'     => ['bg' => '#fff3cd', 'text' => '#856404', 'label' => 'Uploaded'],
        'SEEK'         => ['bg' => '#fff3cd', 'text' => '#856404', 'label' => 'Process'],
        
        // Evaluation stages (sedang dievaluasi)
        'VERIFICATION' => ['bg' => '#cce5ff', 'text' => '#004085', 'label' => 'Verifikasi'],
        'PSIKOTEST'    => ['bg' => '#cce5ff', 'text' => '#004085', 'label' => 'Psikotest'],
        'INTERVIEW_HR' => ['bg' => '#cce5ff', 'text' => '#004085', 'label' => 'Interview HR'],
        'INTERVIEW_USER' => ['bg' => '#cce5ff', 'text' => '#004085', 'label' => 'Interview User'],
        'TECHNICAL_TEST' => ['bg' => '#cce5ff', 'text' => '#004085', 'label' => 'Tes Teknik'],
        'MCU'          => ['bg' => '#cce5ff', 'text' => '#004085', 'label' => 'MCU'],
        
        // Completion stages (selesai)
        'OFFERING'     => ['bg' => '#d4edda', 'text' => '#155724', 'label' => 'Offering'],
        'HIRED'        => ['bg' => '#28a745', 'text' => '#ffffff', 'label' => 'Hired ✓'],
        'CLOSED'       => ['bg' => '#d4edda', 'text' => '#155724', 'label' => 'Closed'],
        'ACTIVE'       => ['bg' => '#d4edda', 'text' => '#155724', 'label' => 'Active'],
        
        // Rejection stages (ditolak)
        'REJECTED'     => ['bg' => '#f8d7da', 'text' => '#721c24', 'label' => 'Rejected'],
        'REJECT'       => ['bg' => '#f8d7da', 'text' => '#721c24', 'label' => 'Ditolak'],
        'DECLINED'     => ['bg' => '#f8d7da', 'text' => '#721c24', 'label' => 'Declined'],
        'OFFER DECLINED' => ['bg' => '#f8d7da', 'text' => '#721c24', 'label' => 'Declined'],
        'INVITATION DECLINED' => ['bg' => '#f8d7da', 'text' => '#721c24', 'label' => 'Inv. Declined'],
        
        // Special statuses
        'CANCELED'     => ['bg' => '#e2e3e5', 'text' => '#383d41', 'label' => 'Canceled'],
        'BUSINESS_REVIEW' => ['bg' => '#fff3cd', 'text' => '#856404', 'label' => 'Review'],
    ];

    /**
     * Dapatkan semua tahapan dengan status saat ini
     */
    public static function getStagesWithStatus($recruitmentId)
    {
        $recruitment = DB::table('recruitment')->find($recruitmentId);
        if (!$recruitment) {
            return null;
        }

        $stages = [];
        foreach (self::STAGES as $stage => $config) {
            $stages[$stage] = [
                'name'       => $stage,
                'label'      => $config['label'],
                'is_current' => $recruitment->Status === $stage,
                'is_done'    => in_array($recruitment->Status, [
                    'PSIKOTEST', 'INTERVIEW_HR', 'INTERVIEW_USER', 
                    'TECHNICAL_TEST', 'MCU', 'OFFERING', 'HIRED'
                ]),
                'order'      => array_search($stage, array_keys(self::STAGES)),
            ];
        }

        return $stages;
    }

    /**
     * Dapatkan tahapan berikutnya dari status saat ini
     */
    public static function getNextStage($currentStatus)
    {
        if (!isset(self::STAGES[$currentStatus])) {
            return null;
        }
        return self::STAGES[$currentStatus]['next'];
    }

    /**
     * Dapatkan warna untuk status tertentu
     */
    public static function getStatusColor($status)
    {
        return self::STAGE_COLORS[$status] ?? self::STAGE_COLORS['PENDING'];
    }

    /**
     * Update status rekruitmen ke tahap berikutnya
     */
    public static function advanceStage($recruitmentId, $data = [])
    {
        $recruitment = DB::table('recruitment')->find($recruitmentId);
        if (!$recruitment) {
            return ['success' => false, 'message' => 'Recruitment not found'];
        }

        $nextStage = self::getNextStage($recruitment->Status);
        if (!$nextStage) {
            return ['success' => false, 'message' => 'Tidak ada tahapan berikutnya'];
        }

        $updateData = [
            'Status'        => $nextStage,
            'updated_at'    => now(),
            'updated_by'    => Auth::id(),
        ];

        if (!empty($data)) {
            $updateData = array_merge($updateData, $data);
        }

        DB::table('recruitment')
            ->where('id', $recruitmentId)
            ->update($updateData);

        return [
            'success'    => true,
            'message'    => 'Stage updated to ' . $nextStage,
            'next_stage' => $nextStage,
        ];
    }

    /**
     * Verifikasi dokumen kandidat
     * Pindahkan dari CREATED/UPLOADED ke VERIFICATION
     */
    public static function verifyDocuments($recruitmentId, $documentData = [])
    {
        $recruitment = DB::table('recruitment')->find($recruitmentId);
        if (!$recruitment) {
            return ['success' => false, 'message' => 'Recruitment not found'];
        }

        // Validasi: hanya bisa verifikasi jika status CREATED atau UPLOADED
        if (!in_array($recruitment->Status, ['CREATED', 'UPLOADED'])) {
            return ['success' => false, 'message' => 'Candidate belum dalam status yang dapat diverifikasi'];
        }

        $updateData = [
            'Status'              => 'VERIFICATION',
            'Registration_Status' => 'VERIFICATION',
            'Document_Status'     => 'ACCEPT',
            'Document_Date'       => now(),
            'Document_Note'       => $documentData['note'] ?? null,
            'Modified_By'         => Auth::user()->username ?? 'SYSTEM',
            'Modified_Date'       => now(),
        ];

        if (!empty($documentData)) {
            $updateData = array_merge($updateData, $documentData);
        }

        DB::table('recruitment')
            ->where('id', $recruitmentId)
            ->update($updateData);

        return [
            'success'    => true,
            'message'    => 'Dokumen kandidat diverifikasi. Kandidat dilanjutkan ke tahap Psikotest.',
            'next_stage' => 'PSIKOTEST',
        ];
    }

    /**
     * Setujui psikotest
     * Pindahkan dari VERIFICATION ke PSIKOTEST
     */
    public static function approvePsikotest($recruitmentId, $testData = [])
    {
        $recruitment = DB::table('recruitment')->find($recruitmentId);
        if (!$recruitment) {
            return ['success' => false, 'message' => 'Recruitment not found'];
        }

        // Validasi: hanya bisa approve jika status VERIFICATION
        if ($recruitment->Status !== 'VERIFICATION') {
            return ['success' => false, 'message' => 'Candidate belum selesai verifikasi dokumentasi'];
        }

        $updateData = [
            'Status'              => 'PSIKOTEST',
            'Registration_Status' => 'PSIKOTEST',
            'Psikotest_Status'    => 'ACCEPT',
            'Psikotest_Date'      => now(),
            'Psikotest_Score'     => $testData['score'] ?? null,
            'Psikotest_Result'    => $testData['result'] ?? null,
            'Psikotest_Note'      => $testData['note'] ?? null,
            'Psikotest_File'      => $testData['file'] ?? null,
            'Modified_By'         => Auth::user()->username ?? 'SYSTEM',
            'Modified_Date'       => now(),
        ];

        if (!empty($testData)) {
            $updateData = array_merge($updateData, $testData);
        }

        DB::table('recruitment')
            ->where('id', $recruitmentId)
            ->update($updateData);

        return [
            'success'    => true,
            'message'    => 'Psikotest kandidat disetujui. Kandidat dilanjutkan ke Interview HR.',
            'next_stage' => 'INTERVIEW_HR',
            'psikotest_score' => $testData['score'] ?? null,
        ];
    }

    /**
     * Setujui interview HR
     * Pindahkan dari PSIKOTEST ke INTERVIEW_HR
     */
    public static function approveInterviewHR($recruitmentId, $interviewData = [])
    {
        $recruitment = DB::table('recruitment')->find($recruitmentId);
        if (!$recruitment) {
            return ['success' => false, 'message' => 'Recruitment not found'];
        }

        if ($recruitment->Status !== 'PSIKOTEST') {
            return ['success' => false, 'message' => 'Candidate belum selesai psikotest'];
        }

        $updateData = [
            'Status'                => 'INTERVIEW_HR',
            'Registration_Status'   => 'INTERVIEW_HR',
            'Interview_Status'      => 'ACCEPT',
            'Interview_Date'        => now(),
            'Interview_Result'      => $interviewData['result'] ?? null,
            'Interview_Note'        => $interviewData['note'] ?? null,
            'Interview_By'          => Auth::user()->name ?? Auth::user()->username ?? 'SYSTEM',
            'Interview_Schedule'    => $interviewData['schedule'] ?? null,
            'Modified_By'           => Auth::user()->username ?? 'SYSTEM',
            'Modified_Date'         => now(),
        ];

        if (!empty($interviewData)) {
            $updateData = array_merge($updateData, $interviewData);
        }

        DB::table('recruitment')
            ->where('id', $recruitmentId)
            ->update($updateData);

        return [
            'success'    => true,
            'message'    => 'Interview HR disetujui. Kandidat dilanjutkan ke Interview User.',
            'next_stage' => 'INTERVIEW_USER',
        ];
    }

    /**
     * Setujui interview user
     * Pindahkan dari INTERVIEW_HR ke INTERVIEW_USER
     */
    public static function approveInterviewUser($recruitmentId, $interviewData = [])
    {
        $recruitment = DB::table('recruitment')->find($recruitmentId);
        if (!$recruitment) {
            return ['success' => false, 'message' => 'Recruitment not found'];
        }

        if ($recruitment->Status !== 'INTERVIEW_HR') {
            return ['success' => false, 'message' => 'Candidate belum selesai interview HR'];
        }

        $updateData = [
            'Status'                  => 'INTERVIEW_USER',
            'Registration_Status'     => 'INTERVIEW_USER',
            'User_Interview_Status'   => 'ACCEPT',
            'User_Interview_Date'     => now(),
            'User_Interview_Result'   => $interviewData['result'] ?? null,
            'User_Interview_Note'     => $interviewData['note'] ?? null,
            'User_Interview_By'       => Auth::user()->name ?? Auth::user()->username ?? 'SYSTEM',
            'User_Interview_Schedule' => $interviewData['schedule'] ?? null,
            'Modified_By'             => Auth::user()->username ?? 'SYSTEM',
            'Modified_Date'           => now(),
        ];

        if (!empty($interviewData)) {
            $updateData = array_merge($updateData, $interviewData);
        }

        DB::table('recruitment')
            ->where('id', $recruitmentId)
            ->update($updateData);

        return [
            'success'    => true,
            'message'    => 'Interview User disetujui. Kandidat dilanjutkan ke Tes Teknik.',
            'next_stage' => 'TECHNICAL_TEST',
        ];
    }

    /**
     * Setujui tes teknik
     * Pindahkan dari INTERVIEW_USER ke TECHNICAL_TEST
     */
    public static function approveTechnicalTest($recruitmentId, $testData = [])
    {
        $recruitment = DB::table('recruitment')->find($recruitmentId);
        if (!$recruitment) {
            return ['success' => false, 'message' => 'Recruitment not found'];
        }

        if ($recruitment->Status !== 'INTERVIEW_USER') {
            return ['success' => false, 'message' => 'Candidate belum selesai interview user'];
        }

        $updateData = [
            'Status'                => 'TECHNICAL_TEST',
            'Registration_Status'   => 'TECHNICAL_TEST',
            'Technical_Test_Status' => 'ACCEPT',
            'Technical_Date'        => now(),
            'Test_Score'            => $testData['score'] ?? null,
            'Test_Result'           => $testData['result'] ?? null,
            'Test_Note'             => $testData['note'] ?? null,
            'Technical_File'        => $testData['file'] ?? null,
            'Technical_Score'       => $testData['score'] ?? null,
            'Modified_By'           => Auth::user()->username ?? 'SYSTEM',
            'Modified_Date'         => now(),
        ];

        if (!empty($testData)) {
            $updateData = array_merge($updateData, $testData);
        }

        DB::table('recruitment')
            ->where('id', $recruitmentId)
            ->update($updateData);

        return [
            'success'    => true,
            'message'    => 'Tes Teknik disetujui. Kandidat dilanjutkan ke MCU.',
            'next_stage' => 'MCU',
        ];
    }

    /**
     * Setujui MCU
     * Pindahkan dari TECHNICAL_TEST ke MCU
     */
    public static function approveMCU($recruitmentId, $mcuData = [])
    {
        $recruitment = DB::table('recruitment')->find($recruitmentId);
        if (!$recruitment) {
            return ['success' => false, 'message' => 'Recruitment not found'];
        }

        if ($recruitment->Status !== 'TECHNICAL_TEST') {
            return ['success' => false, 'message' => 'Candidate belum selesai tes teknik'];
        }

        $updateData = [
            'Status'          => 'MCU',
            'Registration_Status' => 'MCU',
            'MCU_Status'      => 'ACCEPT',
            'MCU_Schedule'    => $mcuData['schedule'] ?? null,
            'MCU_Result'      => $mcuData['result'] ?? null,
            'MCU_Note'        => $mcuData['note'] ?? null,
            'Modified_By'     => Auth::user()->username ?? 'SYSTEM',
            'Modified_Date'   => now(),
        ];

        if (!empty($mcuData)) {
            $updateData = array_merge($updateData, $mcuData);
        }

        DB::table('recruitment')
            ->where('id', $recruitmentId)
            ->update($updateData);

        return [
            'success'    => true,
            'message'    => 'MCU disetujui. Kandidat siap diterima. Silakan terbitkan Offering Letter.',
            'next_stage' => 'OFFERING',
        ];
    }

    /**
     * Terbitkan offering letter
     * Pindahkan dari MCU ke OFFERING lalu HIRED
     */
    public static function issueOfferingLetter($recruitmentId, $offeringData = [])
    {
        $recruitment = DB::table('recruitment')->find($recruitmentId);
        if (!$recruitment) {
            return ['success' => false, 'message' => 'Recruitment not found'];
        }

        if ($recruitment->Status !== 'MCU') {
            return ['success' => false, 'message' => 'Candidate belum selesai MCU'];
        }

        // Generate nomor offering letter
        $year       = now()->format('Y');
        $lastOffering = DB::table('recruitment')
            ->where('Offering_Number', 'like', "OF-{$year}-%")
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastOffering) {
            $lastNo = (int) substr($lastOffering->Offering_Number, -4);
            $newNo  = str_pad($lastNo + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNo = '0001';
        }
        $offeringNumber = "OF-{$year}-{$newNo}";

        $updateData = [
            'Status'                  => 'HIRED',
            'Registration_Status'     => 'HIRED',
            'Offering_Status'         => 'ACCEPT',
            'Offering_Number'         => $offeringNumber,
            'Offering_Letter_Sent_At' => now(),
            'Offering_Letter_Sent_By' => Auth::user()->username ?? 'SYSTEM',
            'Salary_Accepted'         => $offeringData['salary'] ?? null,
            'Position'                => $offeringData['position'] ?? null,
            'Start_Date'              => $offeringData['start_date'] ?? now(),
            'Offering_Note'           => $offeringData['note'] ?? null,
            'Employment_Status'       => 'ACTIVE',
            'Employment_Status_Date'  => now(),
            'Modified_By'             => Auth::user()->username ?? 'SYSTEM',
            'Modified_Date'           => now(),
        ];

        if (!empty($offeringData)) {
            $updateData = array_merge($updateData, $offeringData);
        }

        DB::table('recruitment')
            ->where('id', $recruitmentId)
            ->update($updateData);

        return [
            'success'               => true,
            'message'               => 'Offering Letter berhasil diterbitkan. Kandidat resmi diterima (HIRED).',
            'next_stage'            => 'HIRED',
            'offering_letter_number' => $offeringNumber,
            'data'                  => [
                'offering_letter_number' => $offeringNumber,
            ],
        ];
    }

    /**
     * Reject candidate di tahap tertentu
     */
    public static function rejectCandidate($recruitmentId, $rejectData = [])
    {
        $recruitment = DB::table('recruitment')->find($recruitmentId);
        if (!$recruitment) {
            return ['success' => false, 'message' => 'Recruitment not found'];
        }

        $updateData = [
            'Status'            => 'REJECTED',
            'Reject_Date'       => now(),
            'Reject_Note'       => $rejectData['reason'] ?? null,
            'Modified_By'       => Auth::user()->username ?? 'SYSTEM',
            'Modified_Date'     => now(),
        ];

        if (!empty($rejectData)) {
            $updateData = array_merge($updateData, $rejectData);
        }

        DB::table('recruitment')
            ->where('id', $recruitmentId)
            ->update($updateData);

        return [
            'success' => true,
            'message' => 'Candidate rejected',
            'status'  => 'REJECTED',
        ];
    }

    /**
     * Kandidat menolak offering
     */
    public static function declineOffering($recruitmentId, $declineData = [])
    {
        $recruitment = DB::table('recruitment')->find($recruitmentId);
        if (!$recruitment) {
            return ['success' => false, 'message' => 'Recruitment not found'];
        }

        $updateData = [
            'Status'            => 'DECLINED',
            'Declined_Date'     => now(),
            'Decline_Reason'    => $declineData['reason'] ?? null,
            'Modified_By'       => Auth::user()->username ?? 'SYSTEM',
            'Modified_Date'     => now(),
        ];

        if (!empty($declineData)) {
            $updateData = array_merge($updateData, $declineData);
        }

        DB::table('recruitment')
            ->where('id', $recruitmentId)
            ->update($updateData);

        return [
            'success' => true,
            'message' => 'Candidate declined the offer',
            'status'  => 'DECLINED',
        ];
    }
}
