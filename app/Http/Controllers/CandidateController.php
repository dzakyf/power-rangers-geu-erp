<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CandidateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Helper untuk menghitung jadwal aktif/mendatang untuk notifikasi kandidat
     */
    private function getUpcomingSchedules($rec)
    {
        $notifications = [];

        if (!$rec) {
            return $notifications;
        }

        // 1. Jadwal Interview HR / Umum
        $interviewDate = $rec->Interview_Schedule ?: $rec->Interview_Date;
        if ($interviewDate && $rec->Interview_Status !== 'ACCEPT' && $rec->Interview_Status !== 'REJECT') {
            $notifications[] = [
                'type' => 'interview_hr',
                'title' => 'Jadwal Interview HR',
                'schedule' => $interviewDate,
                'location' => $rec->Location ?? 'Kantor HCGA / Online Meeting',
                'note' => $rec->Interview_Note ?? 'Harap hadir tepat waktu sesuai jadwal yang telah ditentukan.',
                'badge' => 'Interview HR',
                'icon' => 'fa-calendar-check-o',
                'color' => 'primary'
            ];
        }

        // 2. Jadwal Interview User
        if ($rec->User_Interview_Date && $rec->User_Interview_Status !== 'ACCEPT' && $rec->User_Interview_Status !== 'REJECT') {
            $notifications[] = [
                'type' => 'interview_user',
                'title' => 'Jadwal Interview User',
                'schedule' => $rec->User_Interview_Date,
                'location' => $rec->Location ?? 'Kantor / Divisi Terkait',
                'note' => $rec->User_Interview_Note ?? 'Wawancara dengan User / Department Head.',
                'badge' => 'Interview User',
                'icon' => 'fa-users',
                'color' => 'info'
            ];
        }

        // 3. Jadwal Psikotest
        if ($rec->Psikotest_Date && $rec->Psikotest_Status !== 'ACCEPT' && $rec->Psikotest_Status !== 'REJECT') {
            $notifications[] = [
                'type' => 'psikotest',
                'title' => 'Jadwal Psikotest',
                'schedule' => $rec->Psikotest_Date,
                'location' => $rec->Location ?? 'Online / Ruang Asesmen HCGA',
                'note' => $rec->Psikotest_Note ?? 'Silakan persiapkan diri untuk pelaksanaan asesmen psikotes.',
                'badge' => 'Psikotest',
                'icon' => 'fa-pencil-square-o',
                'color' => 'warning'
            ];
        }

        // 4. Jadwal Tes Teknik
        if ($rec->Technical_Date && $rec->Technical_Test_Status !== 'ACCEPT' && $rec->Technical_Test_Status !== 'REJECT') {
            $notifications[] = [
                'type' => 'technical_test',
                'title' => 'Jadwal Tes Teknik',
                'schedule' => $rec->Technical_Date,
                'location' => $rec->Location ?? 'Divisi Terkait',
                'note' => $rec->Technical_Test_Note ?? 'Uji kompetensi teknis.',
                'badge' => 'Tes Teknik',
                'icon' => 'fa-cogs',
                'color' => 'info'
            ];
        }

        // 5. Jadwal MCU (Medical Check Up)
        if ($rec->MCU_Schedule && $rec->MCU_Status !== 'ACCEPT' && $rec->MCU_Status !== 'REJECT') {
            $notifications[] = [
                'type' => 'mcu',
                'title' => 'Jadwal Medical Check-Up (MCU)',
                'schedule' => $rec->MCU_Schedule,
                'location' => $rec->MCU_Location ?? 'Klinik / Laboratorium Rekanan',
                'note' => $rec->MCU_Note ?? 'Harap puasa minimal 10-12 jam sebelum pelaksanaan MCU jika diinstruksikan.',
                'badge' => 'Medical Check-Up',
                'icon' => 'fa-stethoscope',
                'color' => 'danger'
            ];
        }

        // 6. Offering Letter
        if ($rec->Status === 'OFFERING' || $rec->Offering_Status === 'PENDING') {
            $notifications[] = [
                'type' => 'offering',
                'title' => 'Penawaran Kerja (Offering Letter)',
                'schedule' => $rec->Start_Date,
                'location' => 'Kantor HCGA',
                'note' => 'Selamat! Anda telah masuk tahap Offering Letter. Harap konfirmasi penerimaan penawaran kerja.',
                'badge' => 'Offering',
                'icon' => 'fa-file-text-o',
                'color' => 'success'
            ];
        }

        return $notifications;
    }

    public function dashboard()
    {
        $taskId = Auth::user()->username;
        $rec = DB::table('recruitment')->where('Task_ID', $taskId)->first();

        if (!$rec) {
            return view('humanresources.candidate.dashboard', [
                'rec' => null,
                'documentsCount' => 0,
                'hasTest' => false,
                'hasInterview' => false,
                'hasFinalInterview' => false,
                'currentStatus' => '-',
                'position' => '-',
                'createdDate' => null,
                'steps' => [],
                'notifications' => [],
            ]);
        }

        $docRecord = DB::table('recruitment_document')->where('Task_ID', $taskId)->first();
        $documentsCount = 0;
        if ($docRecord) {
            foreach (['Foto', 'CV', 'Ijazah', 'Sertifikat', 'KTP', 'NPWP'] as $col) {
                if ($docRecord->$col) $documentsCount++;
            }
        }

        $currentStatus = $rec->Status ?? 'CREATED';
        $position = $rec->Position;
        $createdDate = $rec->Created_Date;

        $hasTest = ($rec->Psikotest_Status === 'ACCEPT' || $rec->Technical_Test_Status === 'ACCEPT');
        $hasInterview = ($rec->Interview_Status === 'ACCEPT');
        $hasFinalInterview = ($rec->User_Interview_Status === 'ACCEPT');

        // Menghitung status selesai untuk alur standar 8 tahapan
        $isDocDone = ($rec->Document_Status === 'ACCEPT');
        $isPsikoDone = ($rec->Psikotest_Status === 'ACCEPT');
        $isHrDone = ($rec->Interview_Status === 'ACCEPT');
        $isUserDone = ($rec->User_Interview_Status === 'ACCEPT');
        $isTechDone = ($rec->Technical_Test_Status === 'ACCEPT');
        $isMcuDone = ($rec->MCU_Status === 'ACCEPT');
        $isOfferingDone = ($rec->Offering_Status === 'ACCEPT' || in_array($currentStatus, ['HIRED', 'CLOSED']));

        $steps = [
            [
                'no' => 1,
                'label' => 'Input Candidate',
                'done' => true,
                'current' => ($currentStatus === 'CREATED' || $currentStatus === 'UPLOADED') && !$isDocDone,
                'date' => $createdDate,
            ],
            [
                'no' => 2,
                'label' => 'Verifikasi Dokumen',
                'done' => $isDocDone,
                'current' => in_array($currentStatus, ['UPLOADED', 'PENDING']) && !$isDocDone,
                'date' => $rec->Document_Date,
            ],
            [
                'no' => 3,
                'label' => 'Psikotest',
                'done' => $isPsikoDone,
                'current' => ($currentStatus === 'PSIKOTEST' && !$isPsikoDone),
                'date' => $rec->Psikotest_Date,
            ],
            [
                'no' => 4,
                'label' => 'Interview HR',
                'done' => $isHrDone,
                'current' => ($currentStatus === 'INTERVIEW_HR' && !$isHrDone),
                'date' => $rec->Interview_Schedule ?: $rec->Interview_Date,
            ],
            [
                'no' => 5,
                'label' => 'Interview User',
                'done' => $isUserDone,
                'current' => ($currentStatus === 'INTERVIEW_USER' && !$isUserDone),
                'date' => $rec->User_Interview_Date,
            ],
            [
                'no' => 6,
                'label' => 'Tes Teknik',
                'done' => $isTechDone,
                'current' => ($currentStatus === 'TECHNICAL_TEST' && !$isTechDone),
                'date' => $rec->Technical_Date,
            ],
            [
                'no' => 7,
                'label' => 'MCU',
                'done' => $isMcuDone,
                'current' => ($currentStatus === 'MCU' && !$isMcuDone),
                'date' => $rec->MCU_Schedule,
            ],
            [
                'no' => 8,
                'label' => 'Offering Letter',
                'done' => $isOfferingDone,
                'current' => ($currentStatus === 'OFFERING' && !$isOfferingDone),
                'date' => $rec->Start_Date,
            ],
        ];

        // Ambil notifikasi jadwal aktif
        $notifications = $this->getUpcomingSchedules($rec);

        return view('humanresources.candidate.dashboard', compact(
            'rec', 'documentsCount', 'hasTest', 'hasInterview',
            'hasFinalInterview', 'currentStatus', 'position', 'createdDate', 'steps', 'notifications'
        ));
    }

    public function profile()
    {
        $taskId = Auth::user()->username;
        $rec = DB::table('recruitment')->where('Task_ID', $taskId)->first();
        return view('humanresources.candidate.profile', compact('rec'));
    }

    public function profileUpdate(Request $request)
    {
        $request->validate([
            'Gender' => 'nullable|in:L,P',
            'Birth_Date' => 'nullable|date',
            'KTP' => 'nullable|digits:16',
            'Last_Education' => 'nullable|string|max:50',
            'Last_Education_From' => 'nullable|string|max:255',
            'Religion' => 'nullable|string|max:50',
            'Marital_Status' => 'nullable|string|max:50',
            'kewarganegaraan' => 'nullable|string|max:100',
            'Address' => 'nullable|string',
            'City' => 'nullable|string|max:255',
            'Phone' => 'nullable|numeric',
            'Handphone' => 'nullable|numeric',
            'Email' => 'nullable|email',
        ]);

        $taskId = Auth::user()->username;
        $allowed = [
            'Gender', 'Birth_Date', 'Birth_Place', 'KTP',
            'Last_Education', 'Last_Education_From', 'Religion', 'Marital_Status',
            'kewarganegaraan', 'Address', 'City', 'Phone', 'Handphone', 'Email'
        ];
        $data = $request->only($allowed);
        DB::table('recruitment')->updateOrInsert(
            ['Task_ID' => $taskId],
            $data
        );

        return 'success';
    }

    public function documents()
    {
        $taskId = Auth::user()->username;
        $rec = DB::table('recruitment')->where('Task_ID', $taskId)->first();
        $docRecord = DB::table('recruitment_document')->where('Task_ID', $taskId)->first();

        $categories = ['FOTO', 'CV', 'IJAZAH', 'SERTIFIKAT', 'KTP', 'NPWP'];
        $labels = [
            'FOTO' => 'Photo', 'CV' => 'CV / Resume', 'IJAZAH' => 'Diploma',
            'SERTIFIKAT' => 'Certificate', 'KTP' => 'ID Card', 'NPWP' => 'Tax ID',
        ];

        $columnMap = [
            'FOTO' => 'Foto', 'CV' => 'CV', 'IJAZAH' => 'Ijazah',
            'SERTIFIKAT' => 'Sertifikat', 'KTP' => 'KTP', 'NPWP' => 'NPWP',
        ];

        $docStatus = [];
        foreach ($categories as $cat) {
            $col = $columnMap[$cat];
            $filePath = null;
            if ($docRecord && $docRecord->$col) {
                $filePath = $docRecord->$col;
            } elseif ($cat === 'FOTO' && $rec && $rec->Foto) {
                $filePath = $rec->Foto;
            }
            $docStatus[$cat] = [
                'uploaded' => $filePath && file_exists(public_path($filePath)),
                'filePath' => $filePath,
            ];
        }

        return view('humanresources.candidate.documents', compact(
            'rec', 'categories', 'labels', 'docStatus'
        ));
    }

    public function documentUpload(Request $request)
    {
        $taskId = Auth::user()->username;
        $category = $request->input('category');
        $file = $request->file('file');

        if (!$file || !$category) {
            return 'error:Missing file or category';
        }

        $columnMap = [
            'foto' => 'Foto', 'cv' => 'CV', 'ijazah' => 'Ijazah',
            'sertifikat' => 'Sertifikat', 'ktp' => 'KTP', 'npwp' => 'NPWP',
        ];

        $column = $columnMap[$category] ?? null;
        if (!$column) {
            return 'error:Invalid category';
        }

        $ext = $file->getClientOriginalExtension();
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
                $column => $filePath,
            ]);
        }

        return 'success';
    }

    public function assessment()
    {
        $taskId = Auth::user()->username;
        $rec = DB::table('recruitment')->where('Task_ID', $taskId)->first();
        return view('humanresources.candidate.assessment', compact('rec'));
    }

    public function progress()
    {
        $taskId = Auth::user()->username;
        $rec = DB::table('recruitment')->where('Task_ID', $taskId)->first();

        if (!$rec) {
            return view('humanresources.candidate.progress', [
                'rec' => null,
                'stages' => [],
                'notifications' => [],
            ]);
        }

        $statusDesc = function($val) {
            if ($val === 'ACCEPT') return 'Lulus / Selesai';
            if ($val === 'REJECT') return 'Tidak Lolos';
            if ($val === 'SEEK') return 'Dalam Proses';
            if ($val === 'PENDING') return 'Menunggu Evaluasi';
            return $val ?: 'Belum Dimulai';
        };

        // Pemetaan 8 Tahap Sesuai Alur Rekrutmen Standar HCGA
        $stages = [
            [
                'no' => 1,
                'stage' => '1. Input Candidate',
                'date' => $rec->Created_Date ?? null,
                'status' => $rec->Status ? 'Completed' : '-',
                'note' => 'Pendaftaran data dan berkas awal pelamar.',
                'raw_status' => 'ACCEPT'
            ],
            [
                'no' => 2,
                'stage' => '2. Verifikasi Dokumen',
                'date' => $rec->Document_Date ?? null,
                'status' => $statusDesc($rec->Document_Status ?? ($rec->Status === 'UPLOADED' ? 'PENDING' : null)),
                'note' => $rec->Document_Note ?? ($rec->Registration_Status_Note ?? '-'),
                'raw_status' => $rec->Document_Status ?? 'PENDING'
            ],
            [
                'no' => 3,
                'stage' => '3. Psikotest',
                'date' => $rec->Psikotest_Date ?? null,
                'status' => $statusDesc($rec->Psikotest_Status),
                'note' => $rec->Psikotes_Result ? 'Hasil: ' . $rec->Psikotes_Result : ($rec->Psikotest_Note ?? '-'),
                'raw_status' => $rec->Psikotest_Status
            ],
            [
                'no' => 4,
                'stage' => '4. Interview HR',
                'date' => ($rec->Interview_Schedule ?: $rec->Interview_Date) ?? null,
                'status' => $statusDesc($rec->Interview_Status),
                'note' => $rec->Interview_Result ? 'Skor: ' . $rec->Interview_Result . ' (' . ($rec->Hasil_Kategori ?? '-') . ')' : ($rec->Interview_Note ?? '-'),
                'raw_status' => $rec->Interview_Status
            ],
            [
                'no' => 5,
                'stage' => '5. Interview User',
                'date' => $rec->User_Interview_Date ?? null,
                'status' => $statusDesc($rec->User_Interview_Status),
                'note' => $rec->User_Interview_Note ?? '-',
                'raw_status' => $rec->User_Interview_Status
            ],
            [
                'no' => 6,
                'stage' => '6. Tes Teknik',
                'date' => $rec->Technical_Date ?? null,
                'status' => $statusDesc($rec->Technical_Test_Status),
                'note' => $rec->Technical_Test_Note ?? '-',
                'raw_status' => $rec->Technical_Test_Status
            ],
            [
                'no' => 7,
                'stage' => '7. Medical Check-Up (MCU)',
                'date' => $rec->MCU_Schedule ?? null,
                'status' => $statusDesc($rec->MCU_Status),
                'note' => $rec->MCU_Result ? 'Hasil: ' . $rec->MCU_Result : ($rec->MCU_Note ?? '-'),
                'raw_status' => $rec->MCU_Status
            ],
            [
                'no' => 8,
                'stage' => '8. Offering Letter',
                'date' => $rec->Start_Date ?? null,
                'status' => in_array($rec->Status, ['HIRED', 'CLOSED']) ? 'Diterima' : $statusDesc($rec->Offering_Status),
                'note' => in_array($rec->Status, ['HIRED', 'CLOSED']) ? 'Selamat! Anda telah resmi diterima.' : ($rec->Offering_Status === 'DECLINED' ? 'Penawaran Ditolak' : '-'),
                'raw_status' => $rec->Offering_Status
            ],
        ];

        $notifications = $this->getUpcomingSchedules($rec);

        return view('humanresources.candidate.progress', compact('rec', 'stages', 'notifications'));
    }
}
