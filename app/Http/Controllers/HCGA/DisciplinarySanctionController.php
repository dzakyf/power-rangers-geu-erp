<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\HCGA\DisciplinarySanctionService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DisciplinarySanctionController extends Controller
{
    protected $sanctionService;

    public function __construct(DisciplinarySanctionService $sanctionService)
    {
        $this->sanctionService = $sanctionService;
    }

    /**
     * Pengecekan Hak Akses Administrator sesuai standar SPPD
     */
    public static function isAdministrator($user = null)
    {
        $user = $user ?: auth()->user();
        if (!$user) {
            return false;
        }

        return $user->hasRole('ADM')
            || $user->hasRole('Super Admin')
            || ($user->Position ?? '') === 'Administrator';
    }

    /**
     * Pengecekan apakah user adalah Verifikator HCGA
     */
    public static function isHcgaVerifier($user = null)
    {
        $user = $user ?: auth()->user();
        if (!$user) {
            return false;
        }

        return ($user->Department ?? '') === 'HCGA'
            && in_array(strtoupper(trim((string) ($user->Priv_ID ?? ''))), ['MANAGER', 'HEAD', 'VP', 'HCGA', 'STAFF'], true);
    }

    /**
     * Halaman Utama Index Daftar Surat Peringatan & Sanksi
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $isAdmin = self::isAdministrator($user);
        $isHcga = self::isHcgaVerifier($user);

        $managedDepts = $this->sanctionService->getManagedDepartments($user);
        $subordinateIds = $this->sanctionService->getSubordinateUserIds($user);
        $isAtasan = !empty($managedDepts) || !empty($subordinateIds);

        $filters = [
            'search'   => $request->input('search'),
            'status'   => $request->input('status'),
            'is_admin' => $isAdmin,
            'is_hcga'  => $isHcga,
        ];

        $sanctions = $this->sanctionService->getSanctions($filters, $user);

        return view('humanresources.disciplinary-sanction.index', compact(
            'sanctions',
            'isAdmin',
            'isHcga',
            'isAtasan'
        ));
    }

    /**
     * Form Pengajuan Laporan Pelanggaran Karyawan (oleh Atasan / HCGA)
     */
    public function create()
    {
        $user = auth()->user();
        $isAdmin = self::isAdministrator($user);
        $isHcga = self::isHcgaVerifier($user);
        $managedDepts = $this->sanctionService->getManagedDepartments($user);
        $subordinateIds = $this->sanctionService->getSubordinateUserIds($user);
        $isAtasan = !empty($managedDepts) || !empty($subordinateIds);

        // Hanya Atasan, HCGA, atau Admin yang dapat melaporkan pelanggaran
        if (!$isAdmin && !$isHcga && !$isAtasan) {
            return redirect()->route('hrd-disciplinary-sanction-index')
                ->with('error', 'Anda tidak memiliki hak akses untuk mengajukan laporan pelanggaran karyawan.');
        }

        // Ambil daftar karyawan yang dapat dilaporkan
        $query = DB::table('users')
            ->where('Status', 'ACTIVE')
            ->select('id', 'Name', 'username', 'Emp_ID', 'Position', 'Department', 'Area');

        if ($isAdmin || $isHcga) {
            $employees = $query->orderBy('Name', 'asc')->get();
        } else {
            $employees = $query->where(function ($q) use ($subordinateIds, $managedDepts) {
                if (!empty($subordinateIds)) {
                    $q->orWhereIn('id', $subordinateIds);
                }
                if (!empty($managedDepts)) {
                    $q->orWhereIn('Department', $managedDepts);
                }
            })->orderBy('Name', 'asc')->get();
        }

        return view('humanresources.disciplinary-sanction.create', compact('user', 'employees', 'isAdmin', 'isHcga', 'isAtasan'));
    }

    /**
     * Simpan Pengajuan Laporan Pelanggaran Baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'User_ID'             => 'required|integer',
            'Violation_Severity'  => 'required|in:RINGAN,SEDANG,BERAT',
            'Evidence_Basis'      => 'required|string',
            'Violation_Point_1'   => 'required|string',
            'Duration_Months'     => 'nullable|integer|min:1|max:12',
            'Evidence_File'       => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
        ]);

        $points = [];
        if ($request->filled('Violation_Point_1')) {
            $points[] = $request->input('Violation_Point_1');
        }
        if ($request->filled('Violation_Point_2')) {
            $points[] = $request->input('Violation_Point_2');
        }
        if ($request->filled('Violation_Point_3')) {
            $points[] = $request->input('Violation_Point_3');
        }

        $data = [
            'User_ID'              => $request->input('User_ID'),
            'Violation_Points'     => $points,
            'Evidence_Basis'       => $request->input('Evidence_Basis'),
            'Evidence_Description' => $request->input('Evidence_Description'),
            'Violation_Severity'   => $request->input('Violation_Severity'),
            'Duration_Months'      => $request->input('Duration_Months', 6),
            'Valid_From'           => $request->input('Valid_From', date('Y-m-d')),
        ];

        try {
            $sanctionId = $this->sanctionService->submitReport($data, auth()->id());

            // Simpan bukti lampiran awal jika diunggah
            if ($request->hasFile('Evidence_File')) {
                $file = $request->file('Evidence_File');
                $fileName = 'evidence_' . time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/disciplinary_sanctions/evidence'), $fileName);
                $relPath = 'uploads/disciplinary_sanctions/evidence/' . $fileName;

                DB::table('disciplinary_sanction_documents')->insert([
                    'Sanction_ID'     => $sanctionId,
                    'Document_Type'   => 'EVIDENCE_FILE',
                    'File_Name'       => $fileName,
                    'File_Path'       => $relPath,
                    'File_Size'       => filesize(public_path($relPath)),
                    'Sha256_Checksum' => hash_file('sha256', public_path($relPath)),
                    'Uploaded_By'     => auth()->id(),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }

            return redirect()->route('hrd-disciplinary-sanction-index')
                ->with('success', 'Laporan pelanggaran berhasil diajukan dan diteruskan ke Tim HCGA untuk verifikasi.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengajukan laporan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Tampilan Detail Surat Peringatan & Arsip Dokumen
     */
    public function detail($id)
    {
        $data = $this->sanctionService->getSanctionDetail((int) $id);
        if (!$data || !$data['sanction']) {
            return redirect()->route('hrd-disciplinary-sanction-index')->with('error', 'Data sanksi tidak ditemukan.');
        }

        $user = auth()->user();
        $isAdmin = self::isAdministrator($user);
        $isHcga = self::isHcgaVerifier($user);

        return view('humanresources.disciplinary-sanction.detail', array_merge($data, [
            'user'    => $user,
            'isAdmin' => $isAdmin,
            'isHcga'  => $isHcga,
        ]));
    }

    /**
     * Form Verifikasi & Penjadwalan Sidang Klarifikasi oleh HCGA
     */
    public function review($id)
    {
        $user = auth()->user();
        if (!self::isAdministrator($user) && !self::isHcgaVerifier($user)) {
            return redirect()->route('hrd-disciplinary-sanction-index')
                ->with('error', 'Hanya Verifikator HCGA atau Administrator yang dapat memverifikasi sanksi.');
        }

        $data = $this->sanctionService->getSanctionDetail((int) $id);
        if (!$data || !$data['sanction']) {
            return redirect()->route('hrd-disciplinary-sanction-index')->with('error', 'Data sanksi tidak ditemukan.');
        }

        $sanction = $data['sanction'];
        $recommendation = $this->sanctionService->calculateRecommendedSanction($sanction->User_ID, $sanction->Violation_Severity);

        return view('humanresources.disciplinary-sanction.review', compact('sanction', 'recommendation', 'data'));
    }

    /**
     * Proses Verifikasi & Jadwalkan Klarifikasi oleh HCGA
     */
    public function processReview(Request $request, $id)
    {
        $user = auth()->user();
        if (!self::isAdministrator($user) && !self::isHcgaVerifier($user)) {
            return redirect()->route('hrd-disciplinary-sanction-index')->with('error', 'Akses ditolak.');
        }

        $request->validate([
            'Violation_Severity'  => 'required|in:RINGAN,SEDANG,BERAT',
            'Sanction_Level'      => 'required|in:LISAN,SP1,SP2,SP3',
            'Duration_Months'     => 'required|integer|min:1|max:12',
            'Valid_From'          => 'required|date',
            'Meeting_Type'        => 'required|in:NONE,OFFLINE,ZOOM',
            'Meeting_Date'        => 'nullable|required_unless:Meeting_Type,NONE|date',
            'Meeting_Location'    => 'nullable|required_unless:Meeting_Type,NONE|string',
            'HCGA_Verifier_Notes' => 'nullable|string',
        ]);

        try {
            $this->sanctionService->verifyAndSchedule((int) $id, $request->all(), auth()->id());
            return redirect()->route('hrd-disciplinary-sanction-detail', $id)
                ->with('success', 'Verifikasi sanksi berhasil disimpan. Notifikasi telah dikirimkan ke karyawan & atasan terkait.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Gagal memverifikasi sanksi: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Karyawan Menandatangani Komitmen Perbaikan
     */
    public function signCommitment(Request $request, $id)
    {
        $request->validate([
            'Employee_Commitment' => 'required|string|min:10',
        ]);

        try {
            $this->sanctionService->employeeSignCommitment((int) $id, $request->input('Employee_Commitment'), auth()->id());
            return redirect()->route('hrd-disciplinary-sanction-detail', $id)
                ->with('success', 'Komitmen perbaikan Anda telah berhasil ditandatangani dan Surat Peringatan resmi telah aktif.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Gagal menandatangani komitmen: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * HCGA Mengunggah Dokumen Fisik / Scan Berita Acara
     */
    public function uploadDocument(Request $request, $id)
    {
        $request->validate([
            'Document_Type' => 'required|in:MEETING_MINUTES_SCAN,EMPLOYEE_SIGNED_SCAN,EVIDENCE_FILE',
            'Document_File' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $file = $request->file('Document_File');
        $fileName = 'sp_doc_' . time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('uploads/disciplinary_sanctions/documents'), $fileName);
        $relPath = 'uploads/disciplinary_sanctions/documents/' . $fileName;

        DB::table('disciplinary_sanction_documents')->insert([
            'Sanction_ID'     => (int) $id,
            'Document_Type'   => $request->input('Document_Type'),
            'File_Name'       => $fileName,
            'File_Path'       => $relPath,
            'File_Size'       => filesize(public_path($relPath)),
            'Sha256_Checksum' => hash_file('sha256', public_path($relPath)),
            'Uploaded_By'     => auth()->id(),
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return redirect()->back()->with('success', 'Berkas digital berhasil diunggah.');
    }

    /**
     * Unduh / Cetak Dokumen PDF Resmi FR/HCGA/10-00
     */
    public function downloadPdf($id)
    {
        $data = $this->sanctionService->getSanctionDetail((int) $id);
        if (!$data || !$data['sanction']) {
            return redirect()->back()->with('error', 'Dokumen sanksi tidak ditemukan.');
        }

        // Cek apakah berkas PDF sudah diarsip
        $officialDoc = DB::table('disciplinary_sanction_documents')
            ->where('Sanction_ID', $id)
            ->where('Document_Type', 'OFFICIAL_SP_PDF')
            ->orderBy('id', 'desc')
            ->first();

        if ($officialDoc && file_exists(public_path($officialDoc->File_Path))) {
            return response()->file(public_path($officialDoc->File_Path));
        }

        // Jika belum ada, render on the fly
        $relPath = $this->sanctionService->generateOfficialPdf((int) $id);
        return response()->file(public_path($relPath));
    }
}

