<?php
/**
 * RecruitmentNewSubmitController - Menyimpan candidate dari form input
 * 
 * Halaman input:
 * - /open-recruitment-employee (public/external) → recruitment-new-open.blade.php
 * - /recruitment-new (internal) → recruitment-new.blade.php
 * 
 * Setelah submit, data masuk ke grid /hrd-recruitment-management dengan status awal:
 * - Status: CREATED
 * - Registration_Status: CREATED
 * - Document_Status: PENDING (menunggu verifikasi)
 * 
 * Alur Recruitment yang benar:
 * CREATED → (Verifikasi Dokumen) → PSIKOTEST → INTERVIEW_HR → INTERVIEW_USER 
 * → TECHNICAL_TEST → MCU → OFFERING → HIRED
 */
namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class RecruitmentNewSubmitController extends Controller
{
    /**
     * Store new candidate - dipanggil dari /recruitment-new (public) dan /open-recruitment-employee (internal)
     * POST /recruitment-new-submit
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Requisition_ID'       => 'nullable',
            'Position'             => 'nullable|string|max:255',
            'Name'                 => 'required|string|max:255',
            'Gender'               => 'required|in:MALE,FEMALE',
            'Birth_Date'           => 'required|date|before:today',
            'Birth_Place'          => 'nullable|string|max:255',
            'KTP'                  => 'nullable|string|max:20',
            'Address'              => 'nullable|string',
            'Province'             => 'nullable|string|max:100',
            'City'                 => 'nullable|string|max:255',
            'District'             => 'nullable|string|max:100',
            'Sub_District'         => 'nullable|string|max:100',
            'ZipCode'              => 'nullable|string|max:10',
            'Phone'                => 'nullable|string|max:20',
            'Handphone'            => 'nullable|string|max:20',
            'Marital_Status'       => 'nullable|in:SINGLE,MARRIED,WIDOWER,WIDOW',
            'Last_Education'       => 'nullable|string|max:100',
            'Last_Education_From'  => 'nullable|string|max:255',
            'Email'                => 'nullable|email|max:255',
            'Religion'             => 'nullable|string|max:50',
            'kewarganegaraan'      => 'nullable|string|max:100',
            'Attachment_CV'        => 'nullable|file|max:5120', // max 5MB
            'Attachment_Foto'      => 'nullable|file|max:2048',  // max 2MB
            'source'               => 'nullable|in:public,internal',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // ===== Resolusi Position & Requisition_ID =====
        $requisitionId = $request->input('Requisition_ID');
        $positionName  = $request->input('Position');

        // Jika Position dikirim (berasal dari master position), coba cari Requisition aktif (APPROVED) dengan Job_Position yang sama dan masa aktif berlaku
        if (!empty($positionName)) {
            $activeReq = DB::table('requisition')
                ->where('Status', 'APPROVED')
                ->where(function($q) use ($positionName) {
                    $q->where('Job_Position', $positionName)
                      ->orWhere('Subject', 'like', "%{$positionName}%");
                })
                ->where(function($q) {
                    $q->whereNull('End_Date')
                      ->orWhere('End_Date', '>=', date('Y-m-d'));
                })
                ->orderBy('ID', 'DESC')
                ->first();

            if ($activeReq) {
                $requisitionId = $activeReq->Task_ID;
            }
        } elseif (!empty($requisitionId)) {
            // Jika Requisition_ID dikirim langsung (angka ID atau Task_ID)
            $requisition = DB::table('requisition')
                ->where('Task_ID', $requisitionId)
                ->orWhere('ID', $requisitionId)
                ->first();

            if ($requisition) {
                $requisitionId = $requisition->Task_ID;
                if (empty($positionName)) {
                    $positionName = $requisition->Job_Position;
                }
            }
        }

        // ===== Tentukan source (public vs internal) =====
        $source = $request->input('source', 'public'); // default: public

        $location    = auth()->user()->Location ?? 'GEU-HO';
        $createdBy   = auth()->user()->username ?? 'SYSTEM';
        $ipAddress   = request()->ip();

        // Generate Task_ID (format: RECR/<Location>/<YYYYMMDDHHIISS>/<random>)
        $taskId = 'RECR/' . $location . '/' . date('Ymdhis') . '/' . substr(md5($createdBy . time()), 0, 6);

        // File uploads (CV_File, KTP_File, Foto, dll)
        $cvPath   = null;
        $fotoPath = null;
        $ktpPath  = null;
        
        if ($request->hasFile('CV_File')) {
            $cvFile = $request->file('CV_File');
            $cvPath = $this->uploadFile($cvFile, 'recruitment/cv', $taskId);
        } elseif ($request->hasFile('Attachment_CV')) {
            $cvFile = $request->file('Attachment_CV');
            $cvPath = $this->uploadFile($cvFile, 'recruitment/cv', $taskId);
        }
        
        if ($request->hasFile('KTP_File')) {
            $ktpFile = $request->file('KTP_File');
            $ktpPath = $this->uploadFile($ktpFile, 'recruitment/ktp', $taskId);
        }

        if ($request->hasFile('Foto')) {
            $fotoFile = $request->file('Foto');
            $fotoPath = $this->uploadFile($fotoFile, 'recruitment/foto', $taskId);
        } elseif ($request->hasFile('Attachment_Foto')) {
            $fotoFile = $request->file('Attachment_Foto');
            $fotoPath = $this->uploadFile($fotoFile, 'recruitment/foto', $taskId);
        }

        // Data utama candidate
        $data = [
            'Task_ID'                    => $taskId,
            'Requisition_ID'             => $requisitionId,
            'Position'                   => $positionName,
            'Recruitment_Type'           => $request->input('Recruitment_Type', 'EXTERNAL'),
            'Name'                       => $request->input('Name'),
            'Gender'                     => $request->input('Gender'),
            'Birth_Date'                 => $request->input('Birth_Date'),
            'Birth_Place'                => $request->input('Birth_Place', '-'),
            'KTP'                        => $request->input('KTP', ''),
            'Address'                    => $request->input('Address', ''),
            'Province'                   => $request->input('Province', ''),
            'City'                       => $request->input('City', ''),
            'District'                   => $request->input('District', ''),
            'Sub_District'               => $request->input('Sub_District', ''),
            'ZipCode'                    => $request->input('ZipCode', ''),
            'Phone'                      => $request->input('Phone', $request->input('Handphone', '')),
            'Handphone'                  => $request->input('Handphone', ''),
            'Marital_Status'             => $request->input('Marital_Status', 'SINGLE'),
            'Last_Education'             => $request->input('Last_Education', ''),
            'Last_Education_From'        => $request->input('Last_Education_From', ''),
            'Email'                      => $request->input('Email', ''),
            'Religion'                   => $request->input('Religion', 'ISLAM'),
            'kewarganegaraan'            => $request->input('kewarganegaraan', 'WNI'),
            // File attachments
            'CV_File'                    => $cvPath,
            'Foto'                       => $fotoPath,
            // Lokasi dan audit
            'Location'                   => $location,
            'Created_By'                 => $createdBy,
            'Created_Date'               => now(),
            'Created_IP'                 => $ipAddress,
            // Status awal - candidate baru dibuat, menunggu verifikasi dokumen
            'Status'                     => 'CREATED',
            'Registration_Status'        => 'CREATED',
            'Registration_Status_Date'   => now(),
            // Document Status - awalnya PENDING (belum diverifikasi)
            'Document_Status'            => 'PENDING',
            'Document_Date'              => null,
            'Document_Note'              => null,
            // Stage status - semuanya PENDING kecuali yang sudah dilewati
            'Psikotest_Status'           => 'PENDING',
            'Psikotest_Date'             => null,
            'Psikotest_Score'            => null,
            'Psikotest_Result'           => null,
            'Psikotest_Note'             => null,
            'Psikotest_File'             => null,
            'Interview_Status'           => 'PENDING',
            'Interview_Date'             => null,
            'Interview_Result'           => null,
            'Interview_Note'             => null,
            'Interview_By'               => null,
            'Interview_Schedule'         => null,
            'User_Interview_Status'      => 'PENDING',
            'User_Interview_Date'        => null,
            'User_Interview_Result'      => null,
            'User_Interview_Note'        => null,
            'User_Interview_By'          => null,
            'User_Interview_Schedule'    => null,
            'Technical_Test_Status'      => 'PENDING',
            'Technical_Date'             => null,
            'Test_Score'                 => null,
            'Test_Result'                => null,
            'Test_Note'                  => null,
            'Technical_File'             => null,
            'Technical_Score'            => null,
            'MCU_Status'                 => 'PENDING',
            'MCU_Schedule'               => null,
            'MCU_Result'                 => null,
            'MCU_Note'                   => null,
            'Offering_Status'            => 'PENDING',
            'Offering_Number'            => null,
            'Offering_Note'              => null,
            // Lain-lain
            'Recruitment_ID'             => null, // diisi nanti jika ada recruitment master
            'source'                     => $source,
            'Salary_Accepted'            => null,
            'Start_Date'                 => null,
            'End_Date'                   => null,
            'Employment_Status'          => null,
            'Employment_Class'           => null,
            'Decline_Reason'             => null,
            'Reject_Note'                => null,
            'Reject_Date'                => null,
            'Modified_By'                => null,
            'Modified_Date'              => null,
            'Description'                => null,
        ];

        // Ambil daftar kolom yang ada di database tabel recruitment agar aman dari Unknown column
        $allowedColumns = Schema::getColumnListing('recruitment');
        $insertData = !empty($allowedColumns)
            ? array_intersect_key($data, array_flip($allowedColumns))
            : $data;

        $id = DB::table('recruitment')->insertGetId($insertData);

        // Jika CV atau KTP atau Foto diupload dan tabel recruitment_document punya kolom terkait, catat juga
        if ($cvPath || $ktpPath || $fotoPath) {
            $docUpdate = [];
            if ($cvPath && Schema::hasColumn('recruitment_document', 'CV')) {
                $docUpdate['CV'] = $cvPath;
            } elseif ($cvPath && Schema::hasColumn('recruitment_document', 'CV_File')) {
                $docUpdate['CV_File'] = $cvPath;
            }
            if ($ktpPath && Schema::hasColumn('recruitment_document', 'KTP')) {
                $docUpdate['KTP'] = $ktpPath;
            } elseif ($ktpPath && Schema::hasColumn('recruitment_document', 'KTP_File')) {
                $docUpdate['KTP_File'] = $ktpPath;
            }
            if ($fotoPath && Schema::hasColumn('recruitment_document', 'Foto')) {
                $docUpdate['Foto'] = $fotoPath;
            }
            if (!empty($docUpdate)) {
                DB::table('recruitment_document')->where('Task_ID', $taskId)->update($docUpdate);
            }
        }

        // Simpan data kontak referensi ke recruitment_contact_point
        $refName    = $request->input('Emergency_Contact_Name');
        $refContact = $request->input('Emergency_Contact_No');
        $refCompany = $request->input('Emergency_Address');
        $refRel     = $request->input('Emergency_Relation');

        if (!empty($refName) || !empty($refContact)) {
            $this->safeInsert('recruitment_contact_point', [
                'Task_ID'             => $taskId,
                'Name'                => $refName,
                'Contact_No'          => $refContact,
                'Phone_No'            => $refContact,
                'Employee_Department' => $refRel,
                'Position'            => $refRel,
                'Email'               => $refCompany,
            ]);
        }

        // Insert ke tabel pendukung
        $this->createSupportingRecords($taskId, $id);

        return response()->json([
            'status'  => 'success',
            'message' => 'Data New Recruitment Submitted! Candidate akan ditampilkan di grid setelah verifikasi dokumen.',
            'id'      => $id,
            'task_id' => $taskId,
            'data'    => [
                'status'              => 'CREATED',
                'document_status'     => 'PENDING',
                'next_action'         => 'Verifikasi Dokumen',
                'next_route'          => 'hrd-recruitment-verify-document',
            ],
        ]);
    }

    /**
     * Upload file ke storage
     */
    private function uploadFile($file, $directory, $taskId)
    {
        $basePath = 'uploads/recruitment/' . $taskId . '/' . $directory;
        
        // Pastikan directory ada
        $fullPath = public_path($basePath);
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0777, true);
        }
        
        // Generate nama file unik
        $extension = $file->getClientOriginalExtension();
        $filename  = $taskId . '_' . time() . '.' . $extension;
        $filePath  = $basePath . '/' . $filename;
        
        // Move file ke public
        $file->move($fullPath, $filename);
        
        return $filePath;
    }

    /**
     * Helper aman untuk insert row ke tabel pendukung jika tabel ada dan kolom disesuaikan
     */
    private function safeInsert($table, array $data)
    {
        try {
            if (Schema::hasTable($table)) {
                $columns = Schema::getColumnListing($table);
                if (!empty($columns)) {
                    $insertData = array_intersect_key($data, array_flip($columns));
                    DB::table($table)->insert($insertData);
                    return;
                }
            }
            // Fallback jika pengecekan schema gagal
            DB::table($table)->insert($data);
        } catch (\Exception $e) {
            // Log warning atau skip jika kolom/tabel berbeda pada legacy schema
            \Illuminate\Support\Facades\Log::warning("Safe insert failed for table {$table}: " . $e->getMessage());
        }
    }

    /**
     * Buat record di tabel pendukung
     */
    private function createSupportingRecords($taskId, $recruitmentId)
    {
        $now = now();

        // recruitment_detail
        $this->safeInsert('recruitment_detail', [
            'Task_ID'    => $taskId,
            'created_at' => $now,
        ]);

        // recruitment_document
        $this->safeInsert('recruitment_document', [
            'Task_ID'    => $taskId,
            'created_at' => $now,
        ]);

        // recruitment_akademic (4 default)
        $levels = ['SENIOR/VOCATIONAL SCHOOL', 'DIPLOMA', 'BACHELOR', 'MAGISTER'];
        foreach ($levels as $level) {
            $this->safeInsert('recruitment_akademic', [
                'Task_ID'         => $taskId,
                'Education_Level' => $level,
                'created_at'      => $now,
            ]);
        }

        // recruitment_language (4 default)
        for ($i = 0; $i < 4; $i++) {
            $this->safeInsert('recruitment_language', [
                'Task_ID'    => $taskId,
                'created_at' => $now,
            ]);
        }

        // recruitment_organization (4 default)
        for ($i = 0; $i < 4; $i++) {
            $this->safeInsert('recruitment_organization', [
                'Task_ID'    => $taskId,
                'created_at' => $now,
            ]);
        }

        // recruitment_driving (5 default)
        $sims = ['SIM A', 'SIM B', 'SIM B1', 'SIM B1 UMUM', 'SIM C'];
        foreach ($sims as $sim) {
            $this->safeInsert('recruitment_driving', [
                'Task_ID'     => $taskId,
                'Description' => $sim,
                'created_at'  => $now,
            ]);
        }

        // recruitment_health (4 default)
        $healths = [
            'Stay on Hospital',
            'Outside the hospital more than 7 days',
            'Penyakit lain yang sering mengganggu',
            'Abnormality',
        ];
        foreach ($healths as $h) {
            $this->safeInsert('recruitment_health', [
                'Task_ID'     => $taskId,
                'Description' => $h,
                'created_at'  => $now,
            ]);
        }

        // recruitment_experiences (4 default)
        for ($i = 0; $i < 4; $i++) {
            $this->safeInsert('recruitment_experiences', [
                'Task_ID'    => $taskId,
                'created_at' => $now,
            ]);
        }

        // recruitment_informal (4 default)
        for ($i = 0; $i < 4; $i++) {
            $this->safeInsert('recruitment_informal', [
                'Task_ID'    => $taskId,
                'created_at' => $now,
            ]);
        }

        // recruitment_awards (4 default)
        for ($i = 0; $i < 4; $i++) {
            $this->safeInsert('recruitment_awards', [
                'Task_ID'    => $taskId,
                'created_at' => $now,
            ]);
        }

        // recruitment_other_skill (4 default)
        for ($i = 0; $i < 4; $i++) {
            $this->safeInsert('recruitment_other_skill', [
                'Task_ID'    => $taskId,
                'created_at' => $now,
            ]);
        }

        // recruitment_contact_point (4 default)
        for ($i = 0; $i < 4; $i++) {
            $this->safeInsert('recruitment_contact_point', [
                'Task_ID'    => $taskId,
                'created_at' => $now,
            ]);
        }

        // recruitment_family (10 default)
        for ($i = 0; $i < 10; $i++) {
            $this->safeInsert('recruitment_family', [
                'Task_ID'    => $taskId,
                'created_at' => $now,
            ]);
        }

        // recruitment_family_big (10 default)
        for ($i = 0; $i < 10; $i++) {
            $this->safeInsert('recruitment_family_big', [
                'Task_ID'    => $taskId,
                'created_at' => $now,
            ]);
        }
    }
}
