<?php
/**
 * RecruitmentStageController - Controller utama untuk tahapan recruitment
 * Mengelola alur: Input Candidate → Verifikasi Dokumen → Psikotest → 
 * Interview HR → Interview User → Tes Teknik → MCU → Offering Letter
 * 
 * Routes:
 *   GET  /hrd-recruitment-verify-document/{id}       → verifyDocumentPage (BARU)
 *   POST /hrd-recruitment-verify-document            → verifyDocument
 *   POST /hrd-recruitment-approve-psikotest
 *   POST /hrd-recruitment-approve-interview-hr
 *   POST /hrd-recruitment-approve-interview-user
 *   POST /hrd-recruitment-approve-technical-test
 *   POST /hrd-recruitment-approve-mcu
 *   POST /hrd-recruitment-issue-offering-letter
 *   POST /hrd-recruitment-reject-document
 *   POST /hrd-recruitment-reject-psikotest
 *   POST /hrd-recruitment-reject-interview-hr
 *   POST /hrd-recruitment-reject-interview-user
 *   POST /hrd-recruitment-reject-technical-test
 *   POST /hrd-recruitment-reject-mcu
 *   POST /hrd-recruitment-decline-offering
 *   POST /hrd-recruitment-decline-invitation
 */
namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class RecruitmentStageController extends Controller
{
    /**
     * Daftar urutan tahapan recruitment yang benar
     * Urutan: Input → Verifikasi Dokumen → Psikotest → Interview HR → 
     *          Interview User → Tes Teknik → MCU → Offering Letter
     */
    const STAGE_ORDER = [
        'document'       => ['label' => 'Verifikasi Dokumen', 'status_field' => 'Document_Status', 'date_field' => 'Document_Date', 'status_value' => 'ACCEPT'],
        'psikotest'      => ['label' => 'Psikotest',           'status_field' => 'Psikotest_Status', 'date_field' => 'Psikotest_Date', 'status_value' => 'ACCEPT'],
        'interview_hr'   => ['label' => 'Interview HR',        'status_field' => 'Interview_Status', 'date_field' => 'Interview_Date', 'status_value' => 'ACCEPT'],
        'interview_user' => ['label' => 'Interview User',      'status_field' => 'User_Interview_Status', 'date_field' => 'User_Interview_Date', 'status_value' => 'ACCEPT'],
        'technical_test' => ['label' => 'Tes Teknik',          'status_field' => 'Technical_Test_Status', 'date_field' => 'Technical_Date', 'status_value' => 'ACCEPT'],
        'mcu'            => ['label' => 'MCU',                 'status_field' => 'MCU_Status', 'date_field' => 'MCU_Schedule', 'status_value' => 'ACCEPT'],
        'offering'       => ['label' => 'Offering Letter',     'status_field' => 'Offering_Status', 'date_field' => null, 'status_value' => 'ACCEPT'],
    ];

    /**
     * Status colors untuk visual indicator di grid
     */
    const STATUS_COLORS = [
        // Status tahapan
        'pending'    => ['bg' => '#e2e3e5', 'text' => '#383d41', 'label' => 'Pending'],
        'scheduled'  => ['bg' => '#fff3cd', 'text' => '#856404', 'label' => 'Scheduled'],
        'process'    => ['bg' => '#cce5ff', 'text' => '#004085', 'label' => 'Proses'],
        'done'       => ['bg' => '#d4edda', 'text' => '#155724', 'label' => 'Selesai'],
        'accepted'   => ['bg' => '#d4edda', 'text' => '#155724', 'label' => 'Accepted'],
        'reject'     => ['bg' => '#f8d7da', 'text' => '#721c24', 'label' => 'Ditolak'],
        'rejected'   => ['bg' => '#f8d7da', 'text' => '#721c24', 'label' => 'Ditolak'],
        'declined'   => ['bg' => '#f8d7da', 'text' => '#721c24', 'label' => 'Menolak'],
        'failed'     => ['bg' => '#f8d7da', 'text' => '#721c24', 'label' => 'Gagal'],
        
        // Status utama recruitment
        'CREATED'           => ['bg' => '#e2e3e5', 'text' => '#383d41', 'label' => 'Baru'],
        'UPLOADED'         => ['bg' => '#fff3cd', 'text' => '#856404', 'label' => 'Uploaded'],
        'PSIKOTEST'        => ['bg' => '#cce5ff', 'text' => '#004085', 'label' => 'Psikotest'],
        'INTERVIEW_HR'     => ['bg' => '#cce5ff', 'text' => '#004085', 'label' => 'Interview HR'],
        'INTERVIEW_USER'   => ['bg' => '#cce5ff', 'text' => '#004085', 'label' => 'Interview User'],
        'TECHNICAL_TEST'   => ['bg' => '#cce5ff', 'text' => '#004085', 'label' => 'Tes Teknik'],
        'MCU'              => ['bg' => '#cce5ff', 'text' => '#004085', 'label' => 'MCU'],
        'OFFERING'         => ['bg' => '#d4edda', 'text' => '#155724', 'label' => 'Offering'],
        'HIRED'            => ['bg' => '#d4edda', 'text' => '#155724', 'label' => 'Diterima'],
        'PENDING'          => ['bg' => '#e2e3e5', 'text' => '#383d41', 'label' => 'Pending'],
        'BUSINESS_REVIEW'  => ['bg' => '#fff3cd', 'text' => '#856404', 'label' => 'Review'],
        'CLOSED'           => ['bg' => '#d4edda', 'text' => '#155724', 'label' => 'Closed'],
        'REJECT'           => ['bg' => '#f8d7da', 'text' => '#721c24', 'label' => 'Ditolak'],
        'OFFER DECLINED'   => ['bg' => '#f8d7da', 'text' => '#721c24', 'label' => 'Declined'],
        'INVITATION DECLINED' => ['bg' => '#f8d7da', 'text' => '#721c24', 'label' => 'Inv. Declined'],
        'CANCELED'         => ['bg' => '#e2e3e5', 'text' => '#383d41', 'label' => 'Canceled'],
    ];

    /**
     * Urutan tahapan untuk ditampilkan di grid
     */
    const GRID_STAGE_ORDER = [
        ['no' => 1, 'name' => 'Seleksi Administrasi', 'status_field' => 'Document_Status', 'date_field' => 'Document_Date', 'status_value' => 'ACCEPT'],
        ['no' => 2, 'name' => 'Psikotest',            'status_field' => 'Psikotest_Status', 'date_field' => 'Psikotest_Date', 'status_value' => 'ACCEPT'],
        ['no' => 3, 'name' => 'Interview HR',        'status_field' => 'Interview_Status', 'date_field' => 'Interview_Date', 'status_value' => 'ACCEPT'],
        ['no' => 4, 'name' => 'Interview User',      'status_field' => 'User_Interview_Status', 'date_field' => 'User_Interview_Date', 'status_value' => 'ACCEPT'],
        ['no' => 5, 'name' => 'Tes Teknik',          'status_field' => 'Technical_Test_Status', 'date_field' => 'Technical_Date', 'status_value' => 'ACCEPT'],
        ['no' => 6, 'name' => 'MCU',                 'status_field' => 'MCU_Status', 'date_field' => 'MCU_Schedule', 'status_value' => 'ACCEPT'],
        ['no' => 7, 'name' => 'Offering Letter',     'status_field' => 'Offering_Status', 'date_field' => null, 'status_value' => 'ACCEPT'],
    ];

    /**
     * TAMPILKAN DAFTAR KANDIDAT YANG MEMERLUKAN VERIFIKASI DOKUMEN
     * GET /hrd-recruitment-verify-document-list
     */
    public function verifyDocumentList(Request $request)
    {
        $statusFilter = $request->get('status', 'ALL');
        $search = trim((string) $request->get('q', ''));
        $perPage = (int) $request->get('per_page', 15);
        if (!in_array($perPage, [10, 15, 25, 50, 100], true)) {
            $perPage = 15;
        }

        $query = DB::table('recruitment')
            ->whereIn('Status', ['CREATED', 'PENDING', 'UPLOADED'])
            ->where("Location", Auth::user()->Location)
            ->where(function($q) {
                $q->where("Requisition_ID", '!=', null)->orWhere("Requisition_ID", "!=", '');
            });

        if ($statusFilter !== 'ALL' && in_array($statusFilter, ['CREATED', 'PENDING', 'UPLOADED'])) {
            $query->where('Status', $statusFilter);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('Name', 'LIKE', "%{$search}%")
                  ->orWhere('Task_ID', 'LIKE', "%{$search}%")
                  ->orWhere('Position', 'LIKE', "%{$search}%")
                  ->orWhere('Requisition_ID', 'LIKE', "%{$search}%")
                  ->orWhere('Email', 'LIKE', "%{$search}%")
                  ->orWhere('Phone_Number', 'LIKE', "%{$search}%");
            });
        }

        $candidates = $query->orderBy('ID', 'desc')->paginate($perPage);

        // Ambil data dokumen pendukung
        $taskIds = $candidates->pluck('Task_ID')->filter()->toArray();
        $documents = DB::table('recruitment_document')
            ->whereIn('Task_ID', $taskIds)
            ->get()
            ->keyBy('Task_ID');

        $requisitionIds = $candidates->pluck('Requisition_ID')->filter()->toArray();
        $requisitions = DB::table('requisition')
            ->whereIn('Task_ID', $requisitionIds)
            ->get()
            ->keyBy('Task_ID');

        $counts = [
            'all'      => DB::table('recruitment')->whereIn('Status', ['CREATED', 'PENDING', 'UPLOADED'])->count(),
            'uploaded' => DB::table('recruitment')->where('Status', 'UPLOADED')->count(),
            'created'  => DB::table('recruitment')->where('Status', 'CREATED')->count(),
            'pending'  => DB::table('recruitment')->where('Status', 'PENDING')->count(),
        ];

        return view('humanresources.recruitment.recruitment-verify-document-list', [
            'candidates'    => $candidates,
            'documents'     => $documents,
            'requisitions'  => $requisitions,
            'statusFilter'  => $statusFilter,
            'search'        => $search,
            'perPage'       => $perPage,
            'counts'        => $counts,
        ]);
    }

    /**
     * TAMPILKAN HALAMAN VERIFIKASI DOKUMEN
     * GET /hrd-recruitment-verify-document/{id}
     * 
     * Halaman ini menampilkan informasi candidate dan dokumen yang perlu
     * diverifikasi oleh recruiter sebelum melanjutkan ke tahap psikotest.
     */
    public function verifyDocumentPage($id)
    {
        $recruitment = DB::table('recruitment')->where('ID', $id)->first();
        
        if (!$recruitment) {
            abort(404, 'Candidate not found');
        }

        // Hanya izinkan verifikasi jika status CREATED, PENDING, atau UPLOADED
        if (!in_array($recruitment->Status, ['CREATED', 'PENDING', 'UPLOADED'])) {
            abort(403, 'Candidate tidak dapat diverifikasi. Status saat ini: ' . $recruitment->Status);
        }

        // Ambil data pendukung
        $documents = DB::table('recruitment_document')
            ->where('Task_ID', $recruitment->Task_ID)
            ->first();

        $detail = DB::table('recruitment_detail')
            ->where('Task_ID', $recruitment->Task_ID)
            ->first();

        // Ambil data tambahan untuk ditampilkan
        $akademic = DB::table('recruitment_akademic')
            ->where('Task_ID', $recruitment->Task_ID)
            ->orderBy('ID', 'asc')
            ->get();

        $documentsList = DB::table('recruitment_document')
            ->where('Task_ID', $recruitment->Task_ID)
            ->first();

        return view('humanresources.recruitment.recruitment-verify-document', [
            'recruitment' => $recruitment,
            'documents'   => $documentsList,
            'detail'      => $detail,
            'akademic'    => $akademic,
        ]);
    }

    /**
     * Verifikasi dokumen kandidat
     * POST /hrd-recruitment-verify-document
     */
    public function verifyDocument(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'   => 'required|integer|exists:recruitment,ID',
            'note' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $id    = $request->input('id');
        $note  = $request->input('note');

        $recruitment = DB::table('recruitment')->where('ID', $id)->first();
        if (!$recruitment) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Candidate not found',
            ], 404);
        }

        // Validasi: hanya bisa verifikasi jika status CREATED atau PENDING
        if (!in_array($recruitment->Status, ['CREATED', 'PENDING', 'UPLOADED'])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Candidate tidak dapat diverifikasi. Status saat ini: ' . $recruitment->Status,
            ], 400);
        }

        // Update ke tahap selanjutnya: PSIKOTEST
        $updateData = [
            'Status'              => 'PSIKOTEST',
            'Registration_Status' => 'PSIKOTEST',
            'Document_Status'     => 'ACCEPT',
            'Document_Date'       => now(),
            'Modified_By'         => Auth::user()->username ?? 'SYSTEM',
            'Modified_Date'       => now(),
        ];

        // Catat catatan verifikasi jika ada
        if (!empty($note)) {
            $updateData['Document_Note'] = $note;
            $updateData['Registration_Status_Note'] = $note;
            $updateData['Notes'] = $note;
        }

        $this->updateRecruitmentSafe($id, $updateData);

        return response()->json([
            'status'  => 'success',
            'message' => 'Dokumen kandidat diverifikasi. Kandidat dilanjutkan ke tahap Psikotest.',
            'data'    => [
                'recruitment_id' => $id,
                'status'         => 'PSIKOTEST',
            ],
        ]);
    }

    /**
     * Tolak candidate pada tahap verifikasi dokumen
     * POST /hrd-recruitment-reject-document
     */
    public function rejectDocument(Request $request)
    {
        return $this->rejectCandidate($request, 'document');
    }

    /**
     * Setujui hasil Psikotest
     * POST /hrd-recruitment-approve-psikotest
     */
    public function approvePsikotest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'             => 'required|integer|exists:recruitment,ID',
            'score'          => 'nullable|numeric|min:0|max:100',
            'result'         => 'nullable|string|max:500',
            'note'           => 'nullable|string|max:500',
            'psikotest_file' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $id      = $request->input('id');
        $score   = $request->input('score');
        $result  = $request->input('result');
        $note    = $request->input('note');

        $recruitment = DB::table('recruitment')->where('ID', $id)->first();
        if (!$recruitment) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Candidate not found',
            ], 404);
        }

        // Validasi: hanya bisa approve jika status PSIKOTEST
        if ($recruitment->Status !== 'PSIKOTEST') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Candidate belum berada di tahap Psikotest. Status saat ini: ' . $recruitment->Status,
            ], 400);
        }

        $nextStatus = 'INTERVIEW_HR';

        $this->updateRecruitmentSafe($id, [
            'Status'              => $nextStatus,
            'Registration_Status' => $nextStatus,
            'Psikotest_Status'    => 'ACCEPT',
            'Psikotest_Date'      => now(),
            'Psikotest_Score'     => $score,
            'Psikotest_Result'    => $result,
            'Psikotes_Result'     => $result,
            'Psikotest_Note'      => $note,
            'Psikotest_File'      => $request->input('psikotest_file'),
            'Modified_By'         => Auth::user()->username ?? 'SYSTEM',
            'Modified_Date'       => now(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Psikotest kandidat disetujui. Kandidat dilanjutkan ke Interview HR.',
            'data'    => [
                'recruitment_id'    => $id,
                'status'            => $nextStatus,
                'psikotest_score'   => $score,
            ],
        ]);
    }

    /**
     * Tolak candidate pada tahap psikotest
     * POST /hrd-recruitment-reject-psikotest
     */
    public function rejectPsikotest(Request $request)
    {
        return $this->rejectCandidate($request, 'psikotest');
    }

    /**
     * Setujui Interview HR
     * POST /hrd-recruitment-approve-interview-hr
     */
    public function approveInterviewHR(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'       => 'required|integer|exists:recruitment,ID',
            'result'   => 'nullable|string|max:500',
            'note'     => 'nullable|string|max:500',
            'schedule' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $id       = $request->input('id');
        $result   = $request->input('result');
        $note     = $request->input('note');
        $schedule = $request->input('schedule');

        $recruitment = DB::table('recruitment')->where('ID', $id)->first();
        if (!$recruitment) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Candidate not found',
            ], 404);
        }

        if ($recruitment->Status !== 'INTERVIEW_HR') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Candidate belum berada di tahap Interview HR. Status saat ini: ' . $recruitment->Status,
            ], 400);
        }

        $nextStatus = 'INTERVIEW_USER';

        $this->updateRecruitmentSafe($id, [
            'Status'                => $nextStatus,
            'Registration_Status'   => $nextStatus,
            'Interview_Status'      => 'ACCEPT',
            'Interview_Date'        => now(),
            'Interview_Result'      => $result,
            'Interview_Note'        => $note,
            'Interview_Schedule'    => $schedule,
            'Interview_By'          => Auth::user()->name ?? Auth::user()->username ?? 'SYSTEM',
            'Modified_By'           => Auth::user()->username ?? 'SYSTEM',
            'Modified_Date'         => now(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Interview HR disetujui. Kandidat dilanjutkan ke Interview User.',
            'data'    => [
                'recruitment_id' => $id,
                'status'         => $nextStatus,
            ],
        ]);
    }

    /**
     * Tolak candidate pada tahap interview HR
     * POST /hrd-recruitment-reject-interview-hr
     */
    public function rejectInterviewHR(Request $request)
    {
        return $this->rejectCandidate($request, 'interview_hr');
    }

    /**
     * Setujui Interview User
     * POST /hrd-recruitment-approve-interview-user
     */
    public function approveInterviewUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'       => 'required|integer|exists:recruitment,ID',
            'result'   => 'nullable|string|max:500',
            'note'     => 'nullable|string|max:500',
            'schedule' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $id       = $request->input('id');
        $result   = $request->input('result');
        $note     = $request->input('note');
        $schedule = $request->input('schedule');

        $recruitment = DB::table('recruitment')->where('ID', $id)->first();
        if (!$recruitment) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Candidate not found',
            ], 404);
        }

        if ($recruitment->Status !== 'INTERVIEW_USER') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Candidate belum berada di tahap Interview User. Status saat ini: ' . $recruitment->Status,
            ], 400);
        }

        $nextStatus = 'TECHNICAL_TEST';

        $this->updateRecruitmentSafe($id, [
            'Status'                  => $nextStatus,
            'Registration_Status'     => $nextStatus,
            'User_Interview_Status'   => 'ACCEPT',
            'User_Interview_Date'     => now(),
            'User_Interview_Result'   => $result,
            'User_Interview_Note'     => $note,
            'User_Interview_By'       => Auth::user()->name ?? Auth::user()->username ?? 'SYSTEM',
            'User_Interview_Schedule' => $schedule,
            'Modified_By'             => Auth::user()->username ?? 'SYSTEM',
            'Modified_Date'           => now(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Interview User disetujui. Kandidat dilanjutkan ke Tes Teknik.',
            'data'    => [
                'recruitment_id' => $id,
                'status'         => $nextStatus,
            ],
        ]);
    }

    /**
     * Tolak candidate pada tahap interview user
     * POST /hrd-recruitment-reject-interview-user
     */
    public function rejectInterviewUser(Request $request)
    {
        return $this->rejectCandidate($request, 'interview_user');
    }

    /**
     * Setujui Tes Teknik
     * POST /hrd-recruitment-approve-technical-test
     */
    public function approveTechnicalTest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'             => 'required|integer|exists:recruitment,ID',
            'score'          => 'nullable|numeric|min:0|max:100',
            'result'         => 'nullable|string|max:500',
            'note'           => 'nullable|string|max:500',
            'technical_file' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $id            = $request->input('id');
        $score         = $request->input('score');
        $result        = $request->input('result');
        $note          = $request->input('note');

        $recruitment = DB::table('recruitment')->where('ID', $id)->first();
        if (!$recruitment) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Candidate not found',
            ], 404);
        }

        if ($recruitment->Status !== 'TECHNICAL_TEST') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Candidate belum berada di tahap Tes Teknik. Status saat ini: ' . $recruitment->Status,
            ], 400);
        }

        $nextStatus = 'MCU';

        $this->updateRecruitmentSafe($id, [
            'Status'                => $nextStatus,
            'Registration_Status'   => $nextStatus,
            'Technical_Test_Status' => 'ACCEPT',
            'Technical_Date'        => now(),
            'Test_Score'            => $score,
            'Test_Result'           => $result,
            'Test_Note'             => $note,
            'Technical_File'        => $request->input('technical_file'),
            'Technical_Score'       => $score,
            'Modified_By'           => Auth::user()->username ?? 'SYSTEM',
            'Modified_Date'         => now(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Tes Teknik disetujui. Kandidat dilanjutkan ke MCU.',
            'data'    => [
                'recruitment_id' => $id,
                'status'         => $nextStatus,
            ],
        ]);
    }

    /**
     * Tolak candidate pada tahap technical test
     * POST /hrd-recruitment-reject-technical-test
     */
    public function rejectTechnicalTest(Request $request)
    {
        return $this->rejectCandidate($request, 'technical_test');
    }

    /**
     * Setujui MCU (Medical Check Up)
     * POST /hrd-recruitment-approve-mcu
     */
    public function approveMCU(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'       => 'required|integer|exists:recruitment,ID',
            'result'   => 'nullable|string|max:500',
            'note'     => 'nullable|string|max:500',
            'schedule' => 'nullable|date',
            'mcu_file' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $id       = $request->input('id');
        $result   = $request->input('result');
        $note     = $request->input('note');
        $schedule = $request->input('schedule');

        $recruitment = DB::table('recruitment')->where('ID', $id)->first();
        if (!$recruitment) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Candidate not found',
            ], 404);
        }

        if ($recruitment->Status !== 'MCU') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Candidate belum berada di tahap MCU. Status saat ini: ' . $recruitment->Status,
            ], 400);
        }

        $nextStatus = 'OFFERING';

        $this->updateRecruitmentSafe($id, [
            'Status'          => $nextStatus,
            'Registration_Status' => $nextStatus,
            'MCU_Status'      => 'ACCEPT',
            'MCU_Schedule'    => $schedule,
            'MCU_Result'      => $result,
            'MCU_Note'        => $note,
            'MCU_File'        => $request->input('mcu_file') ?: ($recruitment->MCU_File ?? null),
            'Modified_By'     => Auth::user()->username ?? 'SYSTEM',
            'Modified_Date'   => now(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'MCU disetujui. Kandidat siap diterima. Silakan terbitkan Offering Letter.',
            'data'    => [
                'recruitment_id' => $id,
                'status'         => $nextStatus,
            ],
        ]);
    }

    /**
     * Tolak candidate pada tahap MCU
     * POST /hrd-recruitment-reject-mcu
     */
    public function rejectMCU(Request $request)
    {
        return $this->rejectCandidate($request, 'mcu');
    }

    /**
     * Terbitkan Offering Letter
     * POST /hrd-recruitment-issue-offering-letter
     */
    public function issueOfferingLetter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'            => 'required|integer|exists:recruitment,ID',
            'salary'        => 'required|numeric|min:0',
            'position'      => 'required|string|max:255',
            'start_date'    => 'required|date',
            'note'          => 'nullable|string|max:500',
            'offering_file' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $id         = $request->input('id');
        $salary     = $request->input('salary');
        $position   = $request->input('position');
        $startDate  = $request->input('start_date');
        $note       = $request->input('note');

        $recruitment = DB::table('recruitment')->where('ID', $id)->first();
        if (!$recruitment) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Candidate not found',
            ], 404);
        }

        if ($recruitment->Status !== 'OFFERING') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Candidate belum berada di tahap Offering. Status saat ini: ' . $recruitment->Status,
            ], 400);
        }

        // Generate nomor offering letter
        $year       = date('Y');
        $lastOffering = DB::table('recruitment')
            ->where('Offering_Number', 'like', "OF-{$year}-%")
            ->orderBy('ID', 'desc')
            ->first();
        
        if ($lastOffering) {
            $lastNo = (int) substr($lastOffering->Offering_Number, -4);
            $newNo  = str_pad($lastNo + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNo = '0001';
        }
        $offeringNumber = "OF-{$year}-{$newNo}";

        $this->updateRecruitmentSafe($id, [
            'Status'                  => 'HIRED',
            'Registration_Status'     => 'HIRED',
            'Offering_Status'         => 'ACCEPT',
            'Offering_Number'         => $offeringNumber,
            'Salary_Accepted'         => $salary,
            'Position'                => $position,
            'Start_Date'              => $startDate,
            'Offering_Note'           => $note,
            'Employment_Status'       => 'ACTIVE',
            'Employment_Status_Date'  => now(),
            'Modified_By'             => Auth::user()->username ?? 'SYSTEM',
            'Modified_Date'           => now(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Offering Letter berhasil diterbitkan. Kandidat resmi diterima (HIRED).',
            'data'    => [
                'recruitment_id'        => $id,
                'status'                => 'HIRED',
                'offering_letter_number' => $offeringNumber,
                'salary'                => $salary,
                'start_date'            => $startDate,
            ],
        ]);
    }

    /**
     * Kandidat menolak tawaran (Decline Offering)
     * POST /hrd-recruitment-decline-offering
     */
    public function declineOffering(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'     => 'required|integer|exists:recruitment,ID',
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $id     = $request->input('id');
        $reason = $request->input('reason');

        $recruitment = DB::table('recruitment')->where('ID', $id)->first();
        if (!$recruitment) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Candidate not found',
            ], 404);
        }

        $this->updateRecruitmentSafe($id, [
            'Status'              => 'OFFER DECLINED',
            'Registration_Status' => 'OFFER DECLINED',
            'Offering_Status'     => 'REJECT',
            'Decline_Reason'      => $reason,
            'Modified_By'         => Auth::user()->username ?? 'SYSTEM',
            'Modified_Date'       => now(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Kandidat menolak tawaran (Decline Offering).',
            'data'    => [
                'recruitment_id' => $id,
                'status'         => 'OFFER DECLINED',
                'reason'         => $reason,
            ],
        ]);
    }

    /**
     * Cancel/Menolak undangan candidate
     * POST /hrd-recruitment-decline-invitation
     */
    public function declineInvitation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'     => 'required|integer|exists:recruitment,ID',
            'reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $id     = $request->input('id');
        $reason = $request->input('reason');

        $recruitment = DB::table('recruitment')->where('ID', $id)->first();
        if (!$recruitment) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Candidate not found',
            ], 404);
        }

        $this->updateRecruitmentSafe($id, [
            'Status'              => 'INVITATION DECLINED',
            'Registration_Status' => 'INVITATION DECLINED',
            'Decline_Reason'      => $reason,
            'Modified_By'         => Auth::user()->username ?? 'SYSTEM',
            'Modified_Date'       => now(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Invitation candidate ditolak.',
            'data'    => [
                'recruitment_id' => $id,
                'status'         => 'INVITATION DECLINED',
            ],
        ]);
    }

    /**
     * Tolak candidate pada tahap tertentu
     */
    private function rejectCandidate(Request $request, $stage)
    {
        $validator = Validator::make($request->all(), [
            'id'     => 'required|integer|exists:recruitment,ID',
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $id     = $request->input('id');
        $reason = $request->input('reason');

        $recruitment = DB::table('recruitment')->where('ID', $id)->first();
        if (!$recruitment) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Candidate not found',
            ], 404);
        }

        $stageValidation = [
            'document'       => ['CREATED', 'PENDING', 'UPLOADED'],
            'psikotest'      => ['PSIKOTEST'],
            'interview_hr'   => ['INTERVIEW_HR'],
            'interview_user' => ['INTERVIEW_USER'],
            'technical_test' => ['TECHNICAL_TEST'],
            'mcu'            => ['MCU'],
            'offering'       => ['OFFERING'],
        ];

        $allowedStatuses = $stageValidation[$stage] ?? [];
        if (!in_array($recruitment->Status, $allowedStatuses)) {
            return response()->json([
                'status'  => 'error',
                'message' => "Candidate tidak dapat ditolak pada tahap {$stage}. Status saat ini: {$recruitment->Status}",
            ], 400);
        }

        $updateData = [
            'Status'              => 'REJECT',
            'Registration_Status' => 'REJECT',
            'Modified_By'         => Auth::user()->username ?? 'SYSTEM',
            'Modified_Date'       => now(),
        ];

        if (Schema::hasColumn('recruitment', 'Reject_Note')) {
            $updateData['Reject_Note'] = $reason;
        } elseif (Schema::hasColumn('recruitment', 'Registration_Status_Note')) {
            $updateData['Registration_Status_Note'] = $reason;
        } elseif (Schema::hasColumn('recruitment', 'Notes')) {
            $updateData['Notes'] = $reason;
        }

        if (Schema::hasColumn('recruitment', 'Reject_Date')) {
            $updateData['Reject_Date'] = now();
        }

        $this->updateRecruitmentSafe($id, $updateData);

        $stageLabel = ucfirst(str_replace('_', ' ', $stage));
        return response()->json([
            'status'  => 'success',
            'message' => "Candidate ditolak pada tahap {$stageLabel}.",
            'data'    => [
                'recruitment_id' => $id,
                'status'         => 'REJECT',
                'reason'         => $reason,
            ],
        ]);
    }

    /**
     * Dapatkan status tahapan kandidat
     * GET /hrd-recruitment-stages/{id}
     */
    public function getStages($id)
    {
        $recruitment = DB::table('recruitment')->where('ID', $id)->first();
        
        if (!$recruitment) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Recruitment not found',
            ], 404);
        }

        $stages = [];
        foreach (self::GRID_STAGE_ORDER as $stage) {
            $stages[] = [
                'name'         => $stage['name'],
                'status_field' => $stage['status_field'],
                'date_field'   => $stage['date_field'],
                'status_value' => $stage['status_value'],
                'is_done'      => $recruitment->{$stage['status_field']} === $stage['status_value'],
                'date'         => $recruitment->{$stage['date_field']} ?? null,
            ];
        }

        return response()->json([
            'status'  => 'success',
            'data'    => [
                'recruitment_id' => $id,
                'stages'         => $stages,
                'current_status' => $recruitment->Status,
            ],
        ]);
    }

    /**
     * Helper untuk melakukan update ke tabel recruitment dengan memfilter hanya kolom
     * yang benar-benar ada di tabel database (mencegah error Unknown Column).
     */
    private function updateRecruitmentSafe($id, array $data)
    {
        static $allowedColumns = null;
        if ($allowedColumns === null) {
            $allowedColumns = Schema::getColumnListing('recruitment');
        }

        $filtered = array_intersect_key($data, array_flip($allowedColumns));
        return DB::table('recruitment')->where('ID', $id)->update($filtered);
    }
}
