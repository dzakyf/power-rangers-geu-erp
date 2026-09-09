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
            'Requisition_ID'       => 'required|integer',
            'Name'                 => 'required|string|max:255',
            'Gender'               => 'required|in:MALE,FEMALE',
            'Birth_Date'           => 'required|date|before:today',
            'Birth_Place'          => 'nullable|string|max:255',
            'KTP'                  => 'nullable|string|max:20',
            'Address'              => 'nullable|string',
            'City'                 => 'nullable|string|max:255',
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

        // ===== GATEKEEPING: Cek apakah Requisition_ID sudah APPROVED =====
        $requisitionId = $request->input('Requisition_ID');
        $requisition = DB::table('requisition')
            ->where('Task_ID', $requisitionId)
            ->first();

        if (!$requisition) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Requisition tidak ditemukan.',
            ], 422);
        }

        if ($requisition->Status !== 'APPROVED') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Requisition belum APPROVED. Status saat ini: ' . $requisition->Status,
            ], 422);
        }

        // ===== Tentukan source (public vs internal) =====
        $source = $request->input('source', 'public'); // default: public

        $location    = auth()->user()->Location ?? 'GEU-HO';
        $createdBy   = auth()->user()->username ?? 'SYSTEM';
        $ipAddress   = request()->ip();

        // Generate Task_ID (format: RECR/<Location>/<YYYYMMDDHHIISS>/<random>)
        $taskId = 'RECR/' . $location . '/' . date('Ymdhis') . '/' . substr(md5($createdBy . time()), 0, 6);

        // Handle file uploads (CV dan Foto)
        $cvPath   = null;
        $fotoPath = null;
        
        if ($request->hasFile('Attachment_CV')) {
            $cvFile = $request->file('Attachment_CV');
            $cvPath = $this->uploadFile($cvFile, 'recruitment/cv', $taskId);
        }
        
        if ($request->hasFile('Attachment_Foto')) {
            $fotoFile = $request->file('Attachment_Foto');
            $fotoPath = $this->uploadFile($fotoFile, 'recruitment/foto', $taskId);
        }

        // Data utama candidate
        $data = [
            'Task_ID'                    => $taskId,
            'Requisition_ID'             => $request->input('Requisition_ID'),
            'Recruitment_Type'           => 'GENERAL',
            'Name'                       => $request->input('Name'),
            'Gender'                     => $request->input('Gender'),
            'Birth_Date'                 => $request->input('Birth_Date'),
            'Birth_Place'                => $request->input('Birth_Place', ''),
            'KTP'                        => $request->input('KTP', ''),
            'Address'                    => $request->input('Address', ''),
            'City'                       => $request->input('City', ''),
            'Phone'                      => $request->input('Phone', ''),
            'Handphone'                  => $request->input('Handphone', ''),
            'Marital_Status'             => $request->input('Marital_Status', 'SINGLE'),
            'Last_Education'             => $request->input('Last_Education', ''),
            'Last_Education_From'        => $request->input('Last_Education_From', ''),
            'Email'                      => $request->input('Email', ''),
            'Religion'                   => $request->input('Religion', ''),
            'kewarganegaraan'            => $request->input('kewarganegaraan', ''),
            // File attachments
            'CV_File'                    => $cvPath,
            'Foto'                      => $fotoPath,
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

        $id = DB::table('recruitment')->insertGetId($data);

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
     * Buat record di tabel pendukung
     */
    private function createSupportingRecords($taskId, $recruitmentId)
    {
        // recruitment_detail
        DB::table('recruitment_detail')->insert([
            'Task_ID'   => $taskId,
            'created_at' => now(),
        ]);

        // recruitment_document
        DB::table('recruitment_document')->insert([
            'Task_ID'   => $taskId,
            'created_at' => now(),
        ]);

        // recruitment_akademic (4 default)
        $levels = ['SENIOR/VOCATIONAL SCHOOL', 'DIPLOMA', 'BACHELOR', 'MAGISTER'];
        foreach ($levels as $level) {
            DB::table('recruitment_akademic')->insert([
                'Task_ID'   => $taskId,
                'Education_Level' => $level,
                'created_at' => now(),
            ]);
        }

        // recruitment_language (4 default)
        for ($i = 0; $i < 4; $i++) {
            DB::table('recruitment_language')->insert([
                'Task_ID'   => $taskId,
                'created_at' => now(),
            ]);
        }

        // recruitment_organization (4 default)
        for ($i = 0; $i < 4; $i++) {
            DB::table('recruitment_organization')->insert([
                'Task_ID'   => $taskId,
                'created_at' => now(),
            ]);
        }

        // recruitment_driving (5 default)
        $sims = ['SIM A', 'SIM B', 'SIM B1', 'SIM B1 UMUM', 'SIM C'];
        foreach ($sims as $sim) {
            DB::table('recruitment_driving')->insert([
                'Task_ID'   => $taskId,
                'Description' => $sim,
                'created_at' => now(),
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
            DB::table('recruitment_health')->insert([
                'Task_ID'   => $taskId,
                'Description' => $h,
                'created_at' => now(),
            ]);
        }

        // recruitment_experiences (4 default)
        for ($i = 0; $i < 4; $i++) {
            DB::table('recruitment_experiences')->insert([
                'Task_ID'   => $taskId,
                'created_at' => now(),
            ]);
        }

        // recruitment_informal (4 default)
        for ($i = 0; $i < 4; $i++) {
            DB::table('recruitment_informal')->insert([
                'Task_ID'   => $taskId,
                'created_at' => now(),
            ]);
        }

        // recruitment_awards (4 default)
        for ($i = 0; $i < 4; $i++) {
            DB::table('recruitment_awards')->insert([
                'Task_ID'   => $taskId,
                'created_at' => now(),
            ]);
        }

        // recruitment_other_skill (4 default)
        for ($i = 0; $i < 4; $i++) {
            DB::table('recruitment_other_skill')->insert([
                'Task_ID'   => $taskId,
                'created_at' => now(),
            ]);
        }

        // recruitment_contact_point (4 default)
        for ($i = 0; $i < 4; $i++) {
            DB::table('recruitment_contact_point')->insert([
                'Task_ID'   => $taskId,
                'created_at' => now(),
            ]);
        }

        // recruitment_family (10 default)
        for ($i = 0; $i < 10; $i++) {
            DB::table('recruitment_family')->insert([
                'Task_ID'   => $taskId,
                'created_at' => now(),
            ]);
        }

        // recruitment_family_big (10 default)
        for ($i = 0; $i < 10; $i++) {
            DB::table('recruitment_family_big')->insert([
                'Task_ID'   => $taskId,
                'created_at' => now(),
            ]);
        }
    }
}
