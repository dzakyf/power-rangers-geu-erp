<?php

namespace App\Http\Controllers;

use App\Services\CandidatePortalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CandidateController extends Controller
{
    /** @var CandidatePortalService */
    private $portal;

    public function __construct(CandidatePortalService $portal)
    {
        $this->middleware('auth');
        $this->portal = $portal;
    }

    /**
     * Guard access so only CANDIDATE or Admin users may access portal routes.
     */
    private function authorizePortal(Request $request): string
    {
        $user = Auth::user();
        if (!$this->portal->canAccessPortal($user)) {
            abort(403, 'Akses ditolak. Halaman ini khusus untuk akun Kandidat atau Admin.');
        }

        return $this->portal->resolveTaskId($request, $user);
    }

    public function dashboard(Request $request)
    {
        $taskId = $this->authorizePortal($request);
        $rec = DB::table('recruitment')->where('Task_ID', $taskId)->first();

        if (!$rec) {
            return view('humanresources.candidate.dashboard', [
                'rec'               => null,
                'activeTaskId'      => $taskId,
                'documentsCount'    => 0,
                'hasTest'           => false,
                'hasInterview'      => false,
                'hasFinalInterview' => false,
                'currentStatus'     => '-',
                'position'          => '-',
                'createdDate'       => null,
                'steps'             => [],
                'notifications'     => [],
                'isDefaultPassword' => Hash::check('12345678', Auth::user()->password),
            ]);
        }

        $docRecord = DB::table('recruitment_document')->where('Task_ID', $taskId)->first();
        $documentsCount = 0;
        if ($docRecord) {
            foreach (['Foto', 'CV', 'Ijazah', 'Sertifikat', 'KTP', 'NPWP'] as $col) {
                if (!empty($docRecord->$col)) {
                    $documentsCount++;
                }
            }
        }

        $currentStatus = $rec->Status ?? 'CREATED';
        $position = $rec->Position;
        $createdDate = $rec->Created_Date;

        $hasTest = ($rec->Psikotest_Status === 'ACCEPT' || $rec->Technical_Test_Status === 'ACCEPT');
        $hasInterview = ($rec->Interview_Status === 'ACCEPT');
        $hasFinalInterview = ($rec->User_Interview_Status === 'ACCEPT');

        $steps = $this->portal->buildSteps($rec);
        $notifications = $this->portal->getUpcomingSchedules($rec);
        $isDefaultPassword = Hash::check('12345678', Auth::user()->password);

        return view('humanresources.candidate.dashboard', compact(
            'rec',
            'taskId',
            'documentsCount',
            'hasTest',
            'hasInterview',
            'hasFinalInterview',
            'currentStatus',
            'position',
            'createdDate',
            'steps',
            'notifications',
            'isDefaultPassword'
        ))->with('activeTaskId', $taskId);
    }

    public function profile(Request $request)
    {
        $taskId = $this->authorizePortal($request);
        $rec = DB::table('recruitment')->where('Task_ID', $taskId)->first();
        $bundle = $this->portal->getProfileBundle($taskId);

        return view('humanresources.candidate.profile', array_merge([
            'rec'          => $rec,
            'activeTaskId' => $taskId,
        ], $bundle));
    }

    public function profileUpdate(Request $request)
    {
        $taskId = $this->authorizePortal($request);
        $section = $request->input('section', 'biodata');

        if ($section === 'biodata') {
            $request->validate([
                'Gender'              => 'nullable|in:L,P',
                'Birth_Date'          => 'nullable|date',
                'Birth_Place'         => 'nullable|string|max:100',
                'KTP'                 => 'nullable|digits:16',
                'Last_Education'      => 'nullable|string|max:50',
                'Last_Education_From' => 'nullable|string|max:255',
                'Religion'            => 'nullable|string|max:50',
                'Marital_Status'      => 'nullable|string|max:50',
                'kewarganegaraan'     => 'nullable|string|max:100',
                'Address'             => 'nullable|string|max:500',
                'City'                => 'nullable|string|max:255',
                'Phone'               => 'nullable|string|max:30',
                'Handphone'           => 'nullable|string|max:30',
                'Email'               => 'nullable|email|max:255',
            ]);

            $allowed = [
                'Gender', 'Birth_Date', 'Birth_Place', 'KTP',
                'Last_Education', 'Last_Education_From', 'Religion', 'Marital_Status',
                'kewarganegaraan', 'Address', 'City', 'Phone', 'Handphone', 'Email',
            ];

            $data = $request->only($allowed);
            DB::table('recruitment')->updateOrInsert(
                ['Task_ID' => $taskId],
                $data
            );

            return response()->json([
                'status'  => 'ok',
                'message' => 'Biodata utama berhasil diperbarui.',
            ]);
        }

        $sectionMap = [
            'academics' => [
                'table' => 'recruitment_akademic',
                'cols'  => ['Education_Level', 'College', 'Education_Major', 'Location', 'Certified_Year', 'GPA'],
                'label' => 'Riwayat Pendidikan Formal',
            ],
            'trainings' => [
                'table' => 'recruitment_informal',
                'cols'  => ['Training_Company', 'Location', 'Year_Training', 'Note'],
                'label' => 'Pendidikan & Pelatihan (Non-Formal)',
            ],
            'experiences' => [
                'table' => 'recruitment_experiences',
                'cols'  => ['Start_Date', 'End_Date', 'Position', 'Company_Name', 'Last_Salary', 'Note'],
                'label' => 'Pengalaman Kerja',
            ],
            'families' => [
                'table' => 'recruitment_family',
                'cols'  => ['Family_Status', 'Name', 'Gender', 'Birth_Date', 'Last_Education', 'Occupation'],
                'label' => 'Data Keluarga',
            ],
            'healths' => [
                'table' => 'recruitment_health',
                'cols'  => ['Description', 'Disease', 'Year_Health', 'Days'],
                'label' => 'Riwayat Kesehatan',
            ],
            'drivings' => [
                'table' => 'recruitment_driving',
                'cols'  => ['Description', 'Tipe', 'Note'],
                'label' => 'Driving Licence (SIM)',
            ],
            'contacts' => [
                'table' => 'recruitment_contact_point',
                'cols'  => ['Name', 'Employee_Department', 'Position', 'Contact_No', 'Email', 'Phone'],
                'label' => 'Kontak Darurat & Referensi',
            ],
            'insurances' => [
                'table' => 'recruitment_insurances',
                'cols'  => ['Employee_Name', 'On_Behalf', 'On_Behalf_Status', 'Insurances', 'Card_No', 'Polis_No', 'Polis_Type', 'Polis_Status', 'Date_Start', 'Date_End'],
                'label' => 'Data Asuransi',
            ],
        ];

        if (!isset($sectionMap[$section])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Bagian profil tidak dikenali.',
            ], 422);
        }

        $rows = $request->input('rows', []);
        if (!is_array($rows)) {
            $rows = [];
        }

        $cfg = $sectionMap[$section];
        $this->portal->replaceSubTableRows($cfg['table'], $taskId, $rows, $cfg['cols']);

        return response()->json([
            'status'  => 'ok',
            'message' => $cfg['label'] . ' berhasil disimpan.',
        ]);
    }

    public function documents(Request $request)
    {
        $taskId = $this->authorizePortal($request);
        $rec = DB::table('recruitment')->where('Task_ID', $taskId)->first();
        $docRecord = DB::table('recruitment_document')->where('Task_ID', $taskId)->first();

        $categories = ['FOTO', 'CV', 'IJAZAH', 'SERTIFIKAT', 'KTP', 'NPWP'];
        $labels = [
            'FOTO'       => 'Pas Foto',
            'CV'         => 'CV / Resume',
            'IJAZAH'     => 'Ijazah Terakhir',
            'SERTIFIKAT' => 'Sertifikat Pendukung',
            'KTP'        => 'Kartu Tanda Penduduk (KTP)',
            'NPWP'       => 'NPWP',
        ];

        $columnMap = [
            'FOTO'       => 'Foto',
            'CV'         => 'CV',
            'IJAZAH'     => 'Ijazah',
            'SERTIFIKAT' => 'Sertifikat',
            'KTP'        => 'KTP',
            'NPWP'       => 'NPWP',
        ];

        $docStatus = [];
        foreach ($categories as $cat) {
            $col = $columnMap[$cat];
            $filePath = null;
            if ($docRecord && !empty($docRecord->$col)) {
                $filePath = $docRecord->$col;
            } elseif ($cat === 'FOTO' && $rec && !empty($rec->Foto)) {
                $filePath = $rec->Foto;
            }
            $docStatus[$cat] = [
                'uploaded' => $filePath && file_exists(public_path($filePath)),
                'filePath' => $filePath,
            ];
        }

        return view('humanresources.candidate.documents', [
            'rec'          => $rec,
            'activeTaskId' => $taskId,
            'categories'   => $categories,
            'labels'       => $labels,
            'docStatus'    => $docStatus,
        ]);
    }

    public function documentUpload(Request $request)
    {
        $taskId = $this->authorizePortal($request);

        $request->validate([
            'category' => 'required|in:foto,cv,ijazah,sertifikat,ktp,npwp',
            'file'     => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $category = strtolower((string) $request->input('category'));
        $file = $request->file('file');

        $columnMap = [
            'foto'       => 'Foto',
            'cv'         => 'CV',
            'ijazah'     => 'Ijazah',
            'sertifikat' => 'Sertifikat',
            'ktp'        => 'KTP',
            'npwp'       => 'NPWP',
        ];

        $column = $columnMap[$category];
        $ext = strtolower($file->getClientOriginalExtension());
        $filename = $category . '.' . $ext;
        $path = 'uploads/recruitment/' . $taskId;

        $file->move(public_path($path), $filename);
        $filePath = $path . '/' . $filename;

        if ($category === 'foto') {
            DB::table('recruitment')->where('Task_ID', $taskId)->update(['Foto' => $filePath]);
        }

        $docRecord = DB::table('recruitment_document')->where('Task_ID', $taskId)->first();
        if ($docRecord) {
            DB::table('recruitment_document')->where('Task_ID', $taskId)->update([$column => $filePath]);
        } else {
            DB::table('recruitment_document')->insert([
                'Task_ID' => $taskId,
                $column   => $filePath,
            ]);
        }

        return response()->json([
            'status'  => 'ok',
            'message' => 'Dokumen ' . strtoupper($category) . ' berhasil diunggah.',
            'path'    => asset($filePath),
        ]);
    }

    public function assessment(Request $request)
    {
        $taskId = $this->authorizePortal($request);
        $rec = DB::table('recruitment')->where('Task_ID', $taskId)->first();

        return view('humanresources.candidate.assessment', [
            'rec'          => $rec,
            'activeTaskId' => $taskId,
        ]);
    }

    public function progress(Request $request)
    {
        $taskId = $this->authorizePortal($request);
        $rec = DB::table('recruitment')->where('Task_ID', $taskId)->first();

        if (!$rec) {
            return view('humanresources.candidate.progress', [
                'rec'                 => null,
                'activeTaskId'        => $taskId,
                'stages'              => [],
                'notifications'       => [],
                'confirmations'       => collect([]),
                'canDownloadOffering' => false,
                'signedOfferingPath'  => null,
            ]);
        }

        $statusDesc = function ($val) {
            if ($val === 'ACCEPT') return 'Lulus / Selesai';
            if ($val === 'REJECT') return 'Tidak Lolos';
            if ($val === 'SEEK') return 'Dalam Proses';
            if ($val === 'PENDING') return 'Menunggu Evaluasi';
            return $val ?: 'Belum Dimulai';
        };

        $stages = [
            [
                'no'         => 1,
                'stage'      => '1. Input Candidate',
                'date'       => $rec->Created_Date ?? null,
                'status'     => $rec->Status ? 'Completed' : '-',
                'note'       => 'Pendaftaran data dan berkas awal pelamar.',
                'raw_status' => 'ACCEPT',
            ],
            [
                'no'         => 2,
                'stage'      => '2. Verifikasi Dokumen',
                'date'       => $rec->Document_Date ?? null,
                'status'     => $statusDesc($rec->Document_Status ?? ($rec->Status === 'UPLOADED' ? 'PENDING' : null)),
                'note'       => $rec->Document_Note ?? ($rec->Registration_Status_Note ?? '-'),
                'raw_status' => $rec->Document_Status ?? 'PENDING',
            ],
            [
                'no'         => 3,
                'stage'      => '3. Psikotest',
                'date'       => $rec->Psikotest_Date ?? null,
                'status'     => $statusDesc($rec->Psikotest_Status),
                'note'       => $rec->Psikotes_Result ? 'Hasil: ' . $rec->Psikotes_Result : ($rec->Psikotest_Note ?? '-'),
                'raw_status' => $rec->Psikotest_Status,
            ],
            [
                'no'         => 4,
                'stage'      => '4. Interview HR',
                'date'       => ($rec->Interview_Schedule ?: $rec->Interview_Date) ?? null,
                'status'     => $statusDesc($rec->Interview_Status),
                'note'       => $rec->Interview_Result ? 'Skor: ' . $rec->Interview_Result . ' (' . ($rec->Hasil_Kategori ?? '-') . ')' : ($rec->Interview_Note ?? '-'),
                'raw_status' => $rec->Interview_Status,
            ],
            [
                'no'         => 5,
                'stage'      => '5. Interview User',
                'date'       => $rec->User_Interview_Date ?? null,
                'status'     => $statusDesc($rec->User_Interview_Status),
                'note'       => $rec->User_Interview_Note ?? '-',
                'raw_status' => $rec->User_Interview_Status,
            ],
            [
                'no'         => 6,
                'stage'      => '6. Tes Teknik',
                'date'       => $rec->Technical_Date ?? null,
                'status'     => $statusDesc($rec->Technical_Test_Status),
                'note'       => $rec->Technical_Test_Note ?? '-',
                'raw_status' => $rec->Technical_Test_Status,
            ],
            [
                'no'         => 7,
                'stage'      => '7. Medical Check-Up (MCU)',
                'date'       => $rec->MCU_Schedule ?? null,
                'status'     => $statusDesc($rec->MCU_Status),
                'note'       => $rec->MCU_Result ? 'Hasil: ' . $rec->MCU_Result : ($rec->MCU_Note ?? '-'),
                'raw_status' => $rec->MCU_Status,
            ],
            [
                'no'         => 8,
                'stage'      => '8. Offering Letter',
                'date'       => $rec->Start_Date ?? null,
                'status'     => in_array($rec->Status, ['HIRED', 'CLOSED']) ? 'Diterima' : $statusDesc($rec->Offering_Status),
                'note'       => in_array($rec->Status, ['HIRED', 'CLOSED']) ? 'Selamat! Anda telah resmi diterima.' : ($rec->Offering_Status === 'DECLINED' ? 'Penawaran Ditolak' : '-'),
                'raw_status' => $rec->Offering_Status,
            ],
        ];

        $notifications = $this->portal->getUpcomingSchedules($rec);
        $confirmations = $this->portal->getConfirmations($taskId);
        $canDownloadOffering = (
            $rec->Status === 'OFFERING'
            || in_array($rec->Offering_Status, ['PENDING', 'ACCEPT', 'REJECT', 'DECLINED'], true)
            || in_array($rec->Status, ['HIRED', 'CLOSED'], true)
        );
        $signedOfferingPath = $this->portal->getSignedOfferingPath($taskId);

        return view('humanresources.candidate.progress', compact(
            'rec',
            'stages',
            'notifications',
            'confirmations',
            'canDownloadOffering',
            'signedOfferingPath'
        ))->with('activeTaskId', $taskId);
    }

    /**
     * Non-sensitive progress update: schedule attendance confirmation or reschedule request.
     */
    public function confirmSchedule(Request $request)
    {
        $taskId = $this->authorizePortal($request);

        $request->validate([
            'stage_type'          => 'required|in:interview_hr,interview_user,psikotest,technical_test,mcu',
            'confirmation_status' => 'required|in:CONFIRMED,RESCHEDULE_REQUEST',
            'preferred_date'      => 'nullable|date',
            'candidate_note'      => 'nullable|string|max:1000',
        ]);

        $this->portal->saveConfirmation($taskId, [
            'stage_type'          => $request->input('stage_type'),
            'confirmation_status' => $request->input('confirmation_status'),
            'preferred_date'      => $request->input('preferred_date'),
            'candidate_note'      => $request->input('candidate_note'),
        ]);

        return response()->json([
            'status'  => 'ok',
            'message' => 'Konfirmasi jadwal berhasil dikirim ke tim HCGA.',
        ]);
    }

    /**
     * Non-sensitive progress update: candidate availability / notice period.
     */
    public function updateAvailability(Request $request)
    {
        $taskId = $this->authorizePortal($request);

        $request->validate([
            'notice_period'  => 'required|string|max:100',
            'candidate_note' => 'nullable|string|max:1000',
        ]);

        $this->portal->saveConfirmation($taskId, [
            'stage_type'          => 'availability',
            'confirmation_status' => 'UPDATED',
            'notice_period'       => $request->input('notice_period'),
            'candidate_note'      => $request->input('candidate_note'),
        ]);

        return response()->json([
            'status'  => 'ok',
            'message' => 'Informasi ketersediaan bergabung berhasil diperbarui.',
        ]);
    }

    /**
     * Download official Offering Letter PDF for the candidate.
     */
    public function downloadOfferingLetter(Request $request)
    {
        $taskId = $this->authorizePortal($request);
        $rec = DB::table('recruitment')->where('Task_ID', $taskId)->first();

        if (!$rec) {
            abort(404, 'Data kandidat tidak ditemukan.');
        }

        $allowed = (
            $rec->Status === 'OFFERING'
            || in_array($rec->Offering_Status, ['PENDING', 'ACCEPT', 'REJECT', 'DECLINED'], true)
            || in_array($rec->Status, ['HIRED', 'CLOSED'], true)
        );
        if (!$allowed) {
            abort(403, 'Offering Letter belum tersedia untuk tahap rekrutmen Anda saat ini.');
        }

        $pdfRequest = Request::create('/hrd-recruitment-offering-letter-pdf', 'GET', [
            'ID' => $rec->ID,
        ]);

        $controller = '\\App\\Http\\Controllers\\'.'HCGA\\OfferingController';

        if (!\App\Support\Modules::isEnabled('hcga') || !class_exists($controller)) {
            abort(404, 'Fitur offering letter tidak tersedia.');
        }

        return app($controller)->printPdf($pdfRequest);
    }

    /**
     * Upload signed & stamped Offering Letter from candidate (non-sensitive submission).
     */
    public function uploadSignedOffering(Request $request)
    {
        $taskId = $this->authorizePortal($request);
        $rec = DB::table('recruitment')->where('Task_ID', $taskId)->first();

        if (!$rec) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data kandidat tidak ditemukan.',
            ], 404);
        }

        $request->validate([
            'file'           => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'candidate_note' => 'nullable|string|max:1000',
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        $filename = 'offering_signed.' . $ext;
        $dir = 'uploads/recruitment/' . $taskId;
        $file->move(public_path($dir), $filename);
        $filePath = $dir . '/' . $filename;

        $this->portal->saveConfirmation($taskId, [
            'stage_type'           => 'offering',
            'confirmation_status'  => 'SIGNED_UPLOADED',
            'candidate_note'       => $request->input('candidate_note'),
            'signed_offering_file' => $filePath,
        ]);

        return response()->json([
            'status'  => 'ok',
            'message' => 'Offering Letter bertanda tangan & bermaterai berhasil diunggah.',
            'path'    => asset($filePath),
        ]);
    }

    public function passwordForm(Request $request)
    {
        $taskId = $this->authorizePortal($request);

        return view('humanresources.candidate.password', [
            'activeTaskId' => $taskId,
        ]);
    }

    public function passwordUpdate(Request $request)
    {
        $this->authorizePortal($request);

        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();
        if (!Hash::check($request->input('current_password'), $user->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Password saat ini tidak sesuai.',
            ], 422);
        }

        DB::table('users')
            ->where('id', $user->id)
            ->update(['password' => bcrypt($request->input('new_password'))]);

        return response()->json([
            'status'  => 'ok',
            'message' => 'Password berhasil diperbarui.',
        ]);
    }
}
