<?php

namespace App\Services\HCGA;

use App\Services\Approval\ApprovalWorkflowService;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DisciplinarySanctionService
{
    protected $approvalWorkflow;
    protected $notificationService;

    public function __construct(
        ApprovalWorkflowService $approvalWorkflow,
        DisciplinarySanctionNotificationService $notificationService
    ) {
        $this->approvalWorkflow = $approvalWorkflow;
        $this->notificationService = $notificationService;
    }

    /**
     * Dapatkan daftar Surat Peringatan berdasarkan filter & hak akses (Data Privacy)
     */
    public function getSanctions(array $filters, $user)
    {
        // 1. Jalankan auto-expire untuk sanksi yang telah melewati masa berlaku
        $this->autoExpireSanctions();

        $query = DB::table('disciplinary_sanctions as ds')
            ->leftJoin('users as u', 'ds.User_ID', '=', 'u.id')
            ->leftJoin('users as rep', 'ds.Reporter_User_ID', '=', 'rep.id')
            ->select([
                'ds.*',
                'u.email as employee_email',
                'rep.Name as reporter_user_name',
            ])
            ->orderBy('ds.id', 'desc');

        $isAdmin = !empty($filters['is_admin']);
        $isHcga = !empty($filters['is_hcga']);

        // Data Privacy: Jika bukan Admin dan bukan HCGA
        if (!$isAdmin && !$isHcga) {
            $managedDepts = $this->getManagedDepartments($user);
            $subordinateIds = $this->getSubordinateUserIds($user);

            $query->where(function ($q) use ($user, $subordinateIds, $managedDepts) {
                // Karyawan melihat miliknya sendiri
                $q->where('ds.User_ID', $user->id)
                  ->orWhere('ds.Reporter_User_ID', $user->id);

                // Atasan melihat pengajuan bawahannya
                if (!empty($subordinateIds)) {
                    $q->orWhereIn('ds.User_ID', $subordinateIds);
                }
                if (!empty($managedDepts)) {
                    $q->orWhereIn('ds.Departemen', $managedDepts);
                }
            });
        }

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($search) {
                $q->where('ds.No_Trx', 'like', $search)
                  ->orWhere('ds.Nama', 'like', $search)
                  ->orWhere('ds.NIK', 'like', $search)
                  ->orWhere('ds.Departemen', 'like', $search);
            });
        }

        if (!empty($filters['status'])) {
            $query->where('ds.Status', $filters['status']);
        }

        return $query->paginate(15);
    }

    /**
     * Otomatisasi Update Status Sanksi Kadaluarsa (Expired) jika melewati Valid_Until
     */
    public function autoExpireSanctions(): int
    {
        $today = Carbon::now()->toDateString();
        return DB::table('disciplinary_sanctions')
            ->where('Status', 'ACTIVE')
            ->where('Valid_Until', '<', $today)
            ->update([
                'Status' => 'EXPIRED',
                'updated_at' => Carbon::now(),
            ]);
    }

    /**
     * Detail satu transaksi Surat Peringatan beserta dokumen arsipnya
     */
    public function getSanctionDetail(int $sanctionId)
    {
        $sanction = DB::table('disciplinary_sanctions')
            ->where('id', $sanctionId)
            ->first();

        if (!$sanction) {
            return null;
        }

        $documents = DB::table('disciplinary_sanction_documents')
            ->where('Sanction_ID', $sanctionId)
            ->orderBy('id', 'desc')
            ->get();

        $activeSanctions = DB::table('disciplinary_sanctions')
            ->where('User_ID', $sanction->User_ID)
            ->where('id', '!=', $sanctionId)
            ->where('Status', 'ACTIVE')
            ->where('Valid_Until', '>=', Carbon::now()->toDateString())
            ->orderBy('id', 'desc')
            ->get();

        return [
            'sanction' => $sanction,
            'documents' => $documents,
            'active_sanctions' => $activeSanctions,
        ];
    }

    /**
     * Smart Default: Rekomendasikan Tingkat Sanksi berdasarkan riwayat sanksi aktif & bobot pelanggaran
     */
    public function calculateRecommendedSanction(int $userId, string $violationSeverity = 'RINGAN'): array
    {
        $today = Carbon::now()->toDateString();

        // Cari riwayat sanksi yang masih aktif
        $activeSanctions = DB::table('disciplinary_sanctions')
            ->where('User_ID', $userId)
            ->where('Status', 'ACTIVE')
            ->where('Valid_Until', '>=', $today)
            ->orderBy('id', 'desc')
            ->get();

        $hasSp2 = $activeSanctions->contains('Sanction_Level', 'SP2');
        $hasSp1 = $activeSanctions->contains('Sanction_Level', 'SP1');

        $recommendedLevel = 'SP1';
        $requiresMeeting = false;
        $reason = 'Pelanggaran baru pertama kali.';

        if ($hasSp2) {
            $recommendedLevel = 'SP3';
            $requiresMeeting = true;
            $reason = 'Karyawan memiliki SP2 aktif. Pelanggaran selanjutnya otomatis eskalasi ke SP3 (Terakhir).';
        } elseif ($hasSp1) {
            $recommendedLevel = 'SP2';
            $requiresMeeting = true;
            $reason = 'Karyawan memiliki SP1 aktif. Eskalasi otomatis ke SP2 dengan kewajiban pemanggilan klarifikasi.';
        } else {
            if ($violationSeverity === 'BERAT') {
                $recommendedLevel = 'SP3';
                $requiresMeeting = true;
                $reason = 'Pelanggaran berkategori Berat dapat langsung diberikan SP3 / Sidang Pelanggaran Fatal.';
            } elseif ($violationSeverity === 'SEDANG') {
                $recommendedLevel = 'SP2';
                $requiresMeeting = true;
                $reason = 'Pelanggaran berkategori Sedang langsung diarahkan ke SP2 dengan klarifikasi.';
            } else {
                $recommendedLevel = 'SP1';
                $requiresMeeting = false;
                $reason = 'Pelanggaran berkategori Ringan diberikan SP1 (atau Peringatan Lisan).';
            }
        }

        return [
            'recommended_level' => $recommendedLevel,
            'requires_meeting'  => $requiresMeeting,
            'reason'            => $reason,
            'active_count'      => $activeSanctions->count(),
            'active_sanctions'  => $activeSanctions,
        ];
    }

    /**
     * Simpan Pengajuan Laporan Pelanggaran oleh Atasan
     */
    public function submitReport(array $data, int $reporterUserId)
    {
        return DB::transaction(function () use ($data, $reporterUserId) {
            $now = Carbon::now();
            $targetUserId = (int) $data['User_ID'];
            $targetUser = DB::table('users')->where('id', $targetUserId)->first();
            if (!$targetUser) {
                throw new Exception("Karyawan yang dilaporkan tidak ditemukan.");
            }

            $reporter = DB::table('users')->where('id', $reporterUserId)->first();

            // Format No_Trx: SP/YYYY/MM/XXXX
            $year = $now->format('Y');
            $month = $now->format('m');
            $count = DB::table('disciplinary_sanctions')
                ->whereYear('created_at', $year)
                ->count();
            $noTrx = sprintf("SP/%s/%s/%04d", $year, $month, $count + 1);

            $severity = $data['Violation_Severity'] ?? 'RINGAN';
            $recommendation = $this->calculateRecommendedSanction($targetUserId, $severity);
            $sanctionLevel = $data['Sanction_Level'] ?? $recommendation['recommended_level'];

            $durationMonths = (int) ($data['Duration_Months'] ?? 6);
            $validFrom = !empty($data['Valid_From']) ? Carbon::parse($data['Valid_From']) : $now;
            $validUntil = (clone $validFrom)->addMonths($durationMonths);

            $sanctionId = DB::table('disciplinary_sanctions')->insertGetId([
                'No_Trx' => $noTrx,
                'Doc_ID' => 'FR/HCGA/10-00',
                'User_ID' => $targetUserId,
                'Nama' => $targetUser->Name ?? ($targetUser->username ?? 'Karyawan'),
                'NIK' => $targetUser->Emp_ID ?? null,
                'Jabatan' => $targetUser->Position ?? null,
                'Divisi' => $targetUser->Area ?? ($targetUser->division ?? null),
                'Departemen' => $targetUser->Department ?? null,
                'Reporter_User_ID' => $reporterUserId,
                'Reporter_Nama' => $reporter->Name ?? ($reporter->username ?? 'Atasan'),
                'Reporter_Jabatan' => $reporter->Position ?? null,
                'Violation_Points' => is_array($data['Violation_Points']) ? json_encode($data['Violation_Points']) : (string) $data['Violation_Points'],
                'Evidence_Basis' => $data['Evidence_Basis'] ?? 'Laporan',
                'Evidence_Description' => $data['Evidence_Description'] ?? null,
                'Violation_Severity' => $severity,
                'Sanction_Level' => $sanctionLevel,
                'Duration_Months' => $durationMonths,
                'Valid_From' => $validFrom->toDateString(),
                'Valid_Until' => $validUntil->toDateString(),
                'Meeting_Type' => $recommendation['requires_meeting'] ? 'OFFLINE' : 'NONE',
                'Status' => 'PENDING_HCGA',
                'Created_By' => $reporterUserId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Submit ke Approval Workflow Engine (Policy: DISCIPLINARY_SANCTION)
            try {
                $approval = $this->approvalWorkflow->submit(
                    [
                        'Request_No' => $noTrx,
                        'Description' => 'Laporan Sanksi Disiplin: ' . ($targetUser->Name ?? '') . ' (' . $sanctionLevel . ')',
                    ],
                    [
                        'Request_Type' => 'DISCIPLINARY_SANCTION',
                        'Department' => 'HCGA',
                        'Location' => $reporter->Location ?? 'HEAD_OFFICE',
                        'Company_ID' => $reporter->Company_ID ?? 1,
                        'Amount' => 0,
                    ],
                    $reporterUserId
                );

                if (!empty($approval->ID)) {
                    DB::table('approval_request')->where('ID', $approval->ID)->update([
                        'Map_To' => $sanctionId,
                    ]);

                    DB::table('disciplinary_sanctions')->where('id', $sanctionId)->update([
                        'Approval_Request_ID' => $approval->ID,
                    ]);
                }
            } catch (Exception $e) {
                // Log atau lanjutkan jika workflow engine berstatus standalone
            }

            // Notifikasi ke Tim HCGA
            $this->notificationService->notifyHcgaNewReport($sanctionId);

            return $sanctionId;
        });
    }

    /**
     * Verifikasi oleh HCGA & Penjadwalan Sidang / Klarifikasi Zoom
     */
    public function verifyAndSchedule(int $sanctionId, array $data, int $verifierUserId)
    {
        return DB::transaction(function () use ($sanctionId, $data, $verifierUserId) {
            $sanction = DB::table('disciplinary_sanctions')->where('id', $sanctionId)->first();
            if (!$sanction) {
                throw new Exception("Data sanksi tidak ditemukan.");
            }

            $now = Carbon::now();
            $durationMonths = (int) ($data['Duration_Months'] ?? $sanction->Duration_Months);
            $validFrom = !empty($data['Valid_From']) ? Carbon::parse($data['Valid_From']) : Carbon::parse($sanction->Valid_From);
            $validUntil = (clone $validFrom)->addMonths($durationMonths);

            $updateData = [
                'Violation_Severity'  => $data['Violation_Severity'] ?? $sanction->Violation_Severity,
                'Sanction_Level'      => $data['Sanction_Level'] ?? $sanction->Sanction_Level,
                'Duration_Months'     => $durationMonths,
                'Valid_From'          => $validFrom->toDateString(),
                'Valid_Until'         => $validUntil->toDateString(),
                'Meeting_Type'        => $data['Meeting_Type'] ?? 'NONE',
                'Meeting_Date'        => !empty($data['Meeting_Date']) ? $data['Meeting_Date'] : null,
                'Meeting_Location'    => $data['Meeting_Location'] ?? null,
                'HCGA_Verifier_ID'    => $verifierUserId,
                'HCGA_Verifier_Notes' => $data['HCGA_Verifier_Notes'] ?? null,
                'HCGA_Approved_At'    => $now,
                'Status'              => 'WAITING_EMPLOYEE',
                'updated_at'          => $now,
            ];

            DB::table('disciplinary_sanctions')->where('id', $sanctionId)->update($updateData);

            // Kirim Undangan Pertemuan / Notifikasi Terbit SP ke Karyawan & Atasan
            $this->notificationService->notifyEmployeeAndReporter($sanctionId);

            return true;
        });
    }

    /**
     * Karyawan Menandatangani Komitmen Perbaikan & Acknowledgement
     */
    public function employeeSignCommitment(int $sanctionId, string $commitment, int $employeeUserId)
    {
        return DB::transaction(function () use ($sanctionId, $commitment, $employeeUserId) {
            $sanction = DB::table('disciplinary_sanctions')->where('id', $sanctionId)->first();
            if (!$sanction) {
                throw new Exception("Data sanksi tidak ditemukan.");
            }

            if ((int) $sanction->User_ID !== $employeeUserId) {
                throw new Exception("Hanya karyawan bersangkutan yang dapat menandatangani komitmen perbaikan.");
            }

            $now = Carbon::now();
            DB::table('disciplinary_sanctions')->where('id', $sanctionId)->update([
                'Employee_Commitment' => $commitment,
                'Employee_Signed_At' => $now,
                'Status' => 'ACTIVE',
                'updated_at' => $now,
            ]);

            // Auto-Generate Dokumen PDF Resmi FR/HCGA/10-00
            $this->generateOfficialPdf($sanctionId);

            return true;
        });
    }

    /**
     * Generate Dokumen PDF Resmi FR/HCGA/10-00 dan Arsipkan secara Digital
     */
    public function generateOfficialPdf(int $sanctionId): string
    {
        $sanction = DB::table('disciplinary_sanctions')->where('id', $sanctionId)->first();
        if (!$sanction) {
            throw new Exception("Data sanksi tidak ditemukan.");
        }

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);

        // Decode violation points jika berupa JSON
        $points = [];
        if (!empty($sanction->Violation_Points)) {
            $decoded = json_decode($sanction->Violation_Points, true);
            if (is_array($decoded)) {
                $points = $decoded;
            } else {
                $points = explode("\n", $sanction->Violation_Points);
            }
        }

        $html = view('humanresources.disciplinary-sanction.print-pdf', [
            'sanction' => $sanction,
            'points' => $points,
        ])->render();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $output = $dompdf->output();

        // Simpan ke direktori uploads
        $dir = public_path('uploads/disciplinary_sanctions/archives');
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true, true);
        }

        $safeNo = str_replace('/', '_', $sanction->No_Trx);
        $fileName = 'SP_' . $safeNo . '_' . time() . '.pdf';
        $fullPath = $dir . DIRECTORY_SEPARATOR . $fileName;
        file_put_contents($fullPath, $output);

        $relativePath = 'uploads/disciplinary_sanctions/archives/' . $fileName;
        $fileSize = filesize($fullPath);
        $checksum = hash_file('sha256', $fullPath);

        // Catat ke tabel arsip dokumen
        DB::table('disciplinary_sanction_documents')->insert([
            'Sanction_ID' => $sanctionId,
            'Document_Type' => 'OFFICIAL_SP_PDF',
            'File_Name' => $fileName,
            'File_Path' => $relativePath,
            'File_Size' => $fileSize,
            'Sha256_Checksum' => $checksum,
            'Uploaded_By' => $sanction->User_ID,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return $relativePath;
    }

    /**
     * Dapatkan departemen yang dikelola oleh user (Manager / Head / VP)
     */
    public function getManagedDepartments($user): array
    {
        if (!$user) {
            return [];
        }

        $userDept = $user->Department ?? null;
        $priv = strtoupper(trim((string) ($user->Priv_ID ?? '')));
        $departments = [];

        if (in_array($priv, ['MANAGER', 'HEAD', 'VP', 'GM', 'DIR', 'BOD', 'CEO', 'DIRECTOR'], true) && !empty($userDept)) {
            $departments[] = $userDept;
        }

        $username = $user->username ?? null;
        if (!empty($username)) {
            $managedFromApproval = DB::table('master_hr_approval')
                ->where('username', $username)
                ->where('Status', 'ACTIVE')
                ->whereNotNull('Department')
                ->pluck('Department')
                ->toArray();

            foreach ($managedFromApproval as $d) {
                if (!empty($d) && !in_array($d, $departments)) {
                    $departments[] = $d;
                }
            }
        }

        return $departments;
    }

    /**
     * Dapatkan daftar ID user bawahan berdasarkan struktur organisasi (organization_structure)
     */
    public function getSubordinateUserIds($user): array
    {
        if (!$user || empty($user->Emp_ID)) {
            return [];
        }

        $myNik = (string) $user->Emp_ID;

        $myPositionId = DB::table('organization_structure')
            ->where('NIK', $myNik)
            ->value('Position_ID');

        if (!$myPositionId) {
            return [];
        }

        $subordinateNiks = DB::table('organization_structure')
            ->where('Reports_To_Position_ID', $myPositionId)
            ->whereNotNull('NIK')
            ->pluck('NIK')
            ->toArray();

        if (empty($subordinateNiks)) {
            return [];
        }

        return DB::table('users')
            ->whereIn('Emp_ID', $subordinateNiks)
            ->pluck('id')
            ->toArray();
    }
}

