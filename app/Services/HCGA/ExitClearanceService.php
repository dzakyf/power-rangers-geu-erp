<?php

namespace App\Services\HCGA;

use App\Services\Approval\ApprovalWorkflowService;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Dompdf\Dompdf;
use Dompdf\Options;

class ExitClearanceService
{
    protected $approvalWorkflow;
    protected $notificationService;
    protected static $flowSchemaEnsured = false;

    public function __construct(
        ApprovalWorkflowService $approvalWorkflow,
        ExitClearanceNotificationService $notificationService
    ) {
        $this->approvalWorkflow = $approvalWorkflow;
        $this->notificationService = $notificationService;
    }

    /**
     * Pastikan skema exit_clearances mendukung status PENDING_MANAGER_APPROVAL & kolom interview HCGA,
     * serta pulihkan baris yang ter-truncate ke string kosong akibat enum lama.
     */
    public function ensureFlowSchema(): void
    {
        if (self::$flowSchemaEnsured) {
            return;
        }
        self::$flowSchemaEnsured = true;

        try {
            if (!Schema::hasTable('exit_clearances')) {
                return;
            }

            if (DB::getDriverName() === 'mysql') {
                $col = DB::selectOne(
                    "SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'exit_clearances' AND COLUMN_NAME = 'Status'"
                );

                if ($col && stripos((string) $col->COLUMN_TYPE, 'PENDING_MANAGER_APPROVAL') === false) {
                    DB::statement("ALTER TABLE `exit_clearances` MODIFY COLUMN `Status` ENUM('DRAFT', 'PENDING_INTERVIEW', 'PENDING_MANAGER_APPROVAL', 'PENDING_HCGA_APPROVAL', 'COMPLETED', 'REJECTED') NOT NULL DEFAULT 'DRAFT'");
                }
            }

            if (!Schema::hasColumn('exit_clearances', 'Hcga_Interview_By')) {
                Schema::table('exit_clearances', function ($table) {
                    $table->unsignedInteger('Hcga_Interview_By')->nullable()->after('exit_interview_notes');
                });
            }
            if (!Schema::hasColumn('exit_clearances', 'Hcga_Interview_At')) {
                Schema::table('exit_clearances', function ($table) {
                    $table->timestamp('Hcga_Interview_At')->nullable()->after('Hcga_Interview_By');
                });
            }

            // Pulihkan baris yang sempat tersimpan dengan Status = '' karena enum lama
            DB::table('exit_clearances')
                ->where('Status', '')
                ->whereNotNull('exit_interview_notes')
                ->whereNull('Hcga_Verified_At')
                ->update(['Status' => 'PENDING_MANAGER_APPROVAL']);
        } catch (Exception $e) {
            Log::warning('ensureFlowSchema exit_clearances skipped: ' . $e->getMessage());
        }
    }

    /**
     * Dapatkan daftar pengajuan resign & exit clearance dengan filter granular otorisasi:
     * - Administrator: Melihat semua
     * - HCGA Verifier: Melihat pengajuan miliknya + pengajuan departemen lain yang statusnya sudah APPROVED (Clearance stage)
     * - Atasan Langsung / Manager Dept: Hanya melihat bawahan di departemen / bagan organisasinya + miliknya
     * - Karyawan Biasa: Hanya miliknya sendiri
     */
    public function getResignations(array $filters = [], $user = null)
    {
        $this->ensureFlowSchema();

        $userId = is_object($user) ? (int) $user->id : (int) $user;
        $userObj = is_object($user) ? $user : DB::table('users')->where('id', $userId)->first();

        $query = DB::table('employee_resignations as er')
            ->leftJoin('exit_clearances as ec', 'ec.Resignation_ID', '=', 'er.id')
            ->leftJoin('users as u', 'u.id', '=', 'er.User_ID')
            ->select(
                'er.*',
                'ec.id as exit_clearance_id',
                'ec.No_Trx as exit_clearance_no',
                'ec.Status as exit_clearance_status',
                'ec.Approval_Request_ID as exit_clearance_approval_request_id',
                'u.username as user_username',
                'u.email as user_email'
            )
            ->orderBy('er.id', 'desc');

        $isAdmin = !empty($filters['is_admin']);
        $isHcga = !empty($filters['is_hcga']);

        // Cari ID approval_request di mana user saat ini ditugaskan sebagai Approver atau Candidate di workflow
        $assignedApprovalRequestIds = DB::table('approval_request_task as t')
            ->leftJoin('approval_task_candidate as c', 'c.Task_ID', '=', 't.ID')
            ->where(function ($sub) use ($userId) {
                $sub->where('t.Claimed_By', $userId)
                    ->orWhere('t.Acted_By', $userId)
                    ->orWhere('c.User_Id', $userId);
            })
            ->pluck('t.Request_ID')
            ->filter()
            ->unique()
            ->toArray();

        if ($isAdmin) {
            // Full visibility untuk Administrator (ADM / Super Admin)
        } elseif ($isHcga) {
            // HCGA Verifier: dapat melihat pengajuan miliknya ATAU pengajuan departemen lain yang sudah disetujui atasan / tugas approval
            $query->where(function ($q) use ($userId, $assignedApprovalRequestIds) {
                $q->where('er.User_ID', $userId)
                  ->orWhere('er.Created_By', $userId)
                  ->orWhere('er.Departemen', 'HCGA')
                  ->orWhereIn('er.Status', ['APPROVED', 'COMPLETED']);

                if (!empty($assignedApprovalRequestIds)) {
                    $q->orWhereIn('er.Approval_Request_ID', $assignedApprovalRequestIds)
                      ->orWhereIn('ec.Approval_Request_ID', $assignedApprovalRequestIds);
                }
            });
        } else {
            // Atasan Langsung / Manager Departemen / Approver Workflow / Karyawan
            $managedDepartments = $this->getManagedDepartments($userObj);
            $subordinateUserIds = $this->getSubordinateUserIds($userObj);

            $query->where(function ($q) use ($userId, $managedDepartments, $subordinateUserIds, $assignedApprovalRequestIds) {
                // Selalu boleh melihat pengajuan diri sendiri
                $q->where('er.User_ID', $userId);

                // Pembuat pengajuan (misal diajukan oleh atasan/admin atas nama karyawan)
                $q->orWhere('er.Created_By', $userId);

                // User bertindak sebagai Approver atau Candidate pada Approval Workflow Engine
                if (!empty($assignedApprovalRequestIds)) {
                    $q->orWhereIn('er.Approval_Request_ID', $assignedApprovalRequestIds)
                      ->orWhereIn('ec.Approval_Request_ID', $assignedApprovalRequestIds);
                }

                // Pernah menyetujui pengajuan ini
                $q->orWhere('er.Approved_By', $userId);

                // Jika user adalah atasan bawahan menurut struktur organisasi / master_position
                if (!empty($subordinateUserIds)) {
                    $q->orWhereIn('er.User_ID', $subordinateUserIds);
                }

                // Jika user adalah Manager / Dept Head dari departemen tertentu
                if (!empty($managedDepartments)) {
                    $q->orWhereIn('er.Departemen', $managedDepartments);
                }
            });
        }

        if (!empty($filters['status'])) {
            $query->where('er.Status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $s = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($s) {
                $q->where('er.No_Trx', 'like', $s)
                    ->orWhere('er.Nama', 'like', $s)
                    ->orWhere('er.Jabatan', 'like', $s)
                    ->orWhere('er.Departemen', 'like', $s);
            });
        }

        return $query->get();
    }

    /**
     * Dapatkan daftar departemen yang dikepalai oleh user
     */
    public function getManagedDepartments($user): array
    {
        if (!$user) {
            return [];
        }

        $userId = (int) ($user->id ?? 0);
        $userDept = $user->Department ?? null;
        $priv = strtoupper(trim((string) ($user->Priv_ID ?? '')));

        $departments = [];

        // Jika user memiliki priv Manager / Head / VP
        if (in_array($priv, ['MANAGER', 'HEAD', 'VP', 'GM', 'DIR', 'BOD', 'CEO', 'DIRECTOR'], true) && !empty($userDept)) {
            $departments[] = $userDept;
        }

        // Cek juga dari master_hr_approval berdasarkan username
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
        if (!$user) {
            return [];
        }

        $myNik = (string) ($user->Emp_ID ?? '');
        $subordinateNiks = [];

        // 1. Ambil seluruh node milik user di organization_structure
        if (!empty($myNik)) {
            $orgNodes = DB::table('organization_structure')
                ->where('NIK', $myNik)
                ->get();

            $orgNodeIds = $orgNodes->pluck('ID')->filter()->toArray();
            $orgPositionIds = $orgNodes->pluck('Position_ID')->filter()->toArray();

            // Bawahan di organization_structure yang Reports_To_Position_ID mengacu ke node ID atau Position_ID
            if (!empty($orgNodeIds) || !empty($orgPositionIds)) {
                $directNiks = DB::table('organization_structure')
                    ->where(function ($q) use ($orgNodeIds, $orgPositionIds) {
                        if (!empty($orgNodeIds)) {
                            $q->whereIn('Reports_To_Position_ID', $orgNodeIds);
                        }
                        if (!empty($orgPositionIds)) {
                            $q->orWhereIn('Reports_To_Position_ID', $orgPositionIds);
                        }
                    })
                    ->whereNotNull('NIK')
                    ->where('NIK', '!=', $myNik)
                    ->pluck('NIK')
                    ->toArray();

                $subordinateNiks = array_merge($subordinateNiks, $directNiks);
            }

            // 2. Hirarki via master_position (Parent_Position_ID)
            $userPositions = array_filter([$user->Position ?? null]);
            $masterPosIds = $orgPositionIds;
            if (empty($masterPosIds) && !empty($userPositions)) {
                $masterPosIds = DB::table('master_position')
                    ->where(function ($q) use ($userPositions) {
                        $q->whereIn('Initial', $userPositions)
                          ->orWhereIn('Position', $userPositions);
                    })
                    ->pluck('ID')
                    ->toArray();
            }

            if (!empty($masterPosIds)) {
                $childPosInitials = DB::table('master_position')
                    ->whereIn('Parent_Position_ID', $masterPosIds)
                    ->pluck('Initial')
                    ->filter()
                    ->toArray();

                if (!empty($childPosInitials)) {
                    $childNiks = DB::table('employment')
                        ->whereIn('Position', $childPosInitials)
                        ->where('Status', 'ACTIVE')
                        ->where('NIK', '!=', $myNik)
                        ->pluck('NIK')
                        ->toArray();

                    $subordinateNiks = array_merge($subordinateNiks, $childNiks);
                }
            }
        }

        $subordinateNiks = array_values(array_unique(array_filter($subordinateNiks)));

        $subordinateUserIds = [];
        if (!empty($subordinateNiks)) {
            $subordinateUserIds = DB::table('users')
                ->whereIn('Emp_ID', $subordinateNiks)
                ->pluck('id')
                ->toArray();
        }

        return $subordinateUserIds;
    }

    /**
     * Cek apakah user adalah atasan langsung / manager departemen terkait untuk dokumen Exit Clearance tertentu
     */
    public function isManagerOfClearance($user, $clearance, $resignation = null): bool
    {
        if (!$user || !$clearance) {
            return false;
        }

        $userId = (int) ($user->id ?? 0);
        if ($userId <= 0 || (int) ($clearance->User_ID ?? 0) === $userId) {
            return false;
        }

        // 1. User adalah atasan langsung berdasarkan struktur organisasi
        $subordinateIds = array_map('intval', $this->getSubordinateUserIds($user));
        if (in_array((int) ($clearance->User_ID ?? 0), $subordinateIds, true)) {
            return true;
        }

        // 2. User adalah Manager / Dept Head dari departemen karyawan yang bersangkutan
        $managedDepts = array_map(function ($d) {
            return strtoupper(trim((string) $d));
        }, $this->getManagedDepartments($user));
        $clearanceDept = strtoupper(trim((string) ($clearance->Departemen ?? '')));
        if ($clearanceDept !== '' && in_array($clearanceDept, $managedDepts, true)) {
            return true;
        }

        // 3. User adalah atasan yang menyetujui pengajuan resign karyawan ini
        if (!$resignation && !empty($clearance->Resignation_ID)) {
            $resignation = DB::table('employee_resignations')->where('id', $clearance->Resignation_ID)->first();
        }
        if ($resignation && !empty($resignation->Approved_By) && (int) $resignation->Approved_By === $userId) {
            return true;
        }

        // 4. User ditugaskan secara spesifik sebagai candidate/approver di Stage 2 (Approval Atasan)
        if (!empty($clearance->Approval_Request_ID)) {
            $hasStage2Task = DB::table('approval_request_task as t')
                ->leftJoin('approval_task_candidate as c', 'c.Task_ID', '=', 't.ID')
                ->where('t.Request_ID', $clearance->Approval_Request_ID)
                ->where('t.Stage_Order', 2)
                ->whereIn('t.Status', ['PENDING', 'CLAIMED'])
                ->where(function ($sub) use ($userId) {
                    $sub->where('t.Claimed_By', $userId)
                        ->orWhere('c.User_Id', $userId);
                })
                ->exists();

            if ($hasStage2Task) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detail satu pengajuan resign beserta status clearance dan berkas arsipnya
     */
    public function getResignationDetail(int $resignationId)
    {
        $resignation = DB::table('employee_resignations')
            ->where('id', $resignationId)
            ->first();

        if (!$resignation) {
            return null;
        }

        $clearance = DB::table('exit_clearances')
            ->where('Resignation_ID', $resignationId)
            ->first();

        $checklist = [];
        $documents = [];
        if ($clearance) {
            $checklist = DB::table('exit_clearance_checklist_values')
                ->where('Exit_Clearance_ID', $clearance->id)
                ->get();

            $documents = DB::table('exit_clearance_documents')
                ->where('Exit_Clearance_ID', $clearance->id)
                ->orderBy('id', 'desc')
                ->get();
        }

        return [
            'resignation' => $resignation,
            'clearance' => $clearance,
            'checklist' => $checklist,
            'documents' => $documents,
        ];
    }

    /**
     * Submit Pengajuan Resign Awal oleh Karyawan atau Admin/Atasan
     */
    public function submitResignation(array $data, int $userId, ?int $createdBy = null)
    {
        return DB::transaction(function () use ($data, $userId, $createdBy) {
            $now = Carbon::now();
            $user = DB::table('users')->where('id', $userId)->first();
            if (!$user) {
                throw new Exception("Pengguna tidak ditemukan.");
            }

            $creatorId = $createdBy ?: $userId;

            // Generate No_Trx: RSG/YYYY/MM/XXXX
            $year = $now->format('Y');
            $month = $now->format('m');
            $count = DB::table('employee_resignations')
                ->whereYear('created_at', $year)
                ->count();
            $noTrx = sprintf("RSG/%s/%s/%04d", $year, $month, $count + 1);

            $userName = $data['Nama'] ?? ($user->Name ?? ($user->name ?? ($user->username ?? 'Karyawan')));

            $resignationId = DB::table('employee_resignations')->insertGetId([
                'No_Trx' => $noTrx,
                'User_ID' => $userId,
                'Nama' => $userName,
                'Jabatan' => $data['Jabatan'] ?? ($user->Position ?? null),
                'Divisi' => $data['Divisi'] ?? ($user->division ?? ($user->Area ?? null)),
                'Departemen' => $data['Departemen'] ?? ($user->Department ?? null),
                'Join_Date' => !empty($data['Join_Date']) ? $data['Join_Date'] : null,
                'Resign_Date' => $data['Resign_Date'] ?? $now->toDateString(),
                'Last_Working_Date' => $data['Last_Working_Date'],
                'Reason' => $data['Reason'],
                'Resignation_Letter_File' => $data['Resignation_Letter_File'] ?? null,
                'Last_Contract_File' => $data['Last_Contract_File'] ?? null,
                'Status' => 'PENDING_APPROVAL',
                'Created_By' => $creatorId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Submit ke Approval Workflow Engine (Policy: EMPLOYEE_RESIGNATION)
            try {
                $approval = $this->approvalWorkflow->submit(
                    [
                        'Request_No' => $noTrx,
                        'Description' => 'Pengajuan Resign: ' . $userName . ' (' . ($data['Jabatan'] ?? '') . ')',
                    ],
                    [
                        'Request_Type' => 'EMPLOYEE_RESIGNATION',
                        'Department' => $data['Departemen'] ?? ($user->Department ?? null),
                        'Location' => $user->Location ?? 'HEAD_OFFICE',
                        'Company_ID' => $user->Company_ID ?? 1,
                        'Amount' => 0,
                    ],
                    $userId
                );

                if (!empty($approval->ID)) {
                    DB::table('approval_request')->where('ID', $approval->ID)->update([
                        'Map_To' => $resignationId,
                    ]);

                    DB::table('employee_resignations')
                        ->where('id', $resignationId)
                        ->update(['Approval_Request_ID' => $approval->ID]);
                }
            } catch (Exception $e) {
                Log::warning('ApprovalWorkflow submission failed for Resignation: ' . $e->getMessage());
            }

            // Kirim Notifikasi Dual-Channel
            $resignationObj = DB::table('employee_resignations')->where('id', $resignationId)->first();
            $this->notificationService->notify(
                'RESIGN_SUBMITTED',
                $resignationObj,
                null,
                null,
                null
            );

            // Audit Trail: Pengajuan Resign Baru
            $this->logActivity([
                'Resignation_ID'  => $resignationId,
                'Action'          => 'RESIGNATION_SUBMITTED',
                'Title'           => 'Pengajuan Resign Baru',
                'Description'     => "Pengajuan pengunduran diri untuk {$userName} berhasil diajukan. Tanggal Terakhir Bekerja: {$data['Last_Working_Date']}.",
                'Previous_Status' => null,
                'New_Status'      => 'PENDING_APPROVAL',
                'Actor_ID'        => $creatorId,
                'Payload'         => [
                    'No_Trx'            => $noTrx,
                    'Reason'            => $data['Reason'] ?? null,
                    'Last_Working_Date' => $data['Last_Working_Date'],
                ],
            ]);

            return $resignationId;
        });
    }

    /**
     * Approval Pengajuan Resign oleh Atasan Langsung
     */
    public function approveResignation(int $resignationId, int $approverId, ?string $notes = '')
    {
        return DB::transaction(function () use ($resignationId, $approverId, $notes) {
            $resignation = DB::table('employee_resignations')->where('id', $resignationId)->first();
            if (!$resignation) {
                throw new Exception("Pengajuan resign tidak ditemukan.");
            }

            if ($resignation->Status !== 'PENDING_APPROVAL') {
                throw new Exception("Pengajuan resign tidak berada dalam status pending approval.");
            }

            $now = Carbon::now();

            // Update status resign menjadi APPROVED
            DB::table('employee_resignations')->where('id', $resignationId)->update([
                'Status' => 'APPROVED',
                'Approved_By' => $approverId,
                'Approved_At' => $now,
                'Updated_By' => $approverId,
                'updated_at' => $now,
            ]);

            // Buat draft Exit Clearance otomatis untuk diisi karyawan
            $year = $now->format('Y');
            $month = $now->format('m');
            $count = DB::table('exit_clearances')->whereYear('created_at', $year)->count();
            $clearanceNo = sprintf("EXC/%s/%s/%04d", $year, $month, $count + 1);

            $clearanceId = DB::table('exit_clearances')->insertGetId([
                'No_Trx' => $clearanceNo,
                'Resignation_ID' => $resignation->id,
                'User_ID' => $resignation->User_ID,
                'Nama' => $resignation->Nama,
                'Jabatan' => $resignation->Jabatan,
                'Divisi' => $resignation->Divisi,
                'Departemen' => $resignation->Departemen,
                'Join_Date' => $resignation->Join_Date,
                'Exit_Date' => $resignation->Last_Working_Date,
                'Status' => 'DRAFT',
                'Created_By' => $approverId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Simpan Surat Resign ke Dokumen Digital jika ada
            if (!empty($resignation->Resignation_Letter_File)) {
                $filePath = public_path($resignation->Resignation_Letter_File);
                $fileSize = file_exists($filePath) ? filesize($filePath) : null;
                $checksum = file_exists($filePath) ? hash_file('sha256', $filePath) : null;

                DB::table('exit_clearance_documents')->insert([
                    'Exit_Clearance_ID' => $clearanceId,
                    'Document_Type' => 'RESIGNATION_LETTER',
                    'Document_No' => $resignation->No_Trx,
                    'File_Name' => basename($resignation->Resignation_Letter_File),
                    'File_Path' => $resignation->Resignation_Letter_File,
                    'File_Size' => $fileSize,
                    'Mime_Type' => 'application/pdf',
                    'Sha256_Checksum' => $checksum,
                    'Created_By' => $resignation->User_ID,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // Inisialisasi 17 Item Checklist Standar Perusahaan
            $masterItems = DB::table('master_exit_clearance_items')
                ->where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->get();

            // Auto-populate data aset GA jika ada aset yang sedang dipegang
            $userAssets = DB::table('asset_list')
                ->where('User_Responsible', $resignation->Nama)
                ->orWhere('User_Responsible_NIK', (string) $resignation->User_ID)
                ->get();

            $assetNote = '';
            if ($userAssets->isNotEmpty()) {
                $assetCodes = $userAssets->pluck('Asset_No')->implode(', ');
                $assetNote = "Aset tercatat: " . $assetCodes;
            }

            foreach ($masterItems as $m) {
                $ket = null;
                if (stripos($m->item_name, 'Komputer') !== false || stripos($m->item_name, 'Laptop') !== false) {
                    $ket = $assetNote ?: null;
                }

                DB::table('exit_clearance_checklist_values')->insert([
                    'Exit_Clearance_ID' => $clearanceId,
                    'Item_ID' => $m->id,
                    'Item_Name' => $m->item_name,
                    'Status' => 'TIDAK_ADA',
                    'Keterangan' => $ket,
                    'Penanggung_Jawab' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // Kirim Notifikasi ke Karyawan
            $employeeUser = DB::table('users')->where('id', $resignation->User_ID)->first();
            $employeeEmail = $employeeUser ? ($employeeUser->email ?? null) : null;
            $clearanceObj = DB::table('exit_clearances')->where('id', $clearanceId)->first();

            $this->notificationService->notify(
                'RESIGN_APPROVED',
                $resignation,
                $clearanceObj,
                $resignation->User_ID,
                $employeeEmail,
                $notes
            );

            // Audit Trail: Approval Pengajuan Resign oleh Atasan & Pembuatan Draft Clearance
            $this->logActivity([
                'Resignation_ID'    => $resignationId,
                'Exit_Clearance_ID' => $clearanceId,
                'Action'            => 'RESIGNATION_APPROVED',
                'Title'             => 'Approval Pengajuan Resign oleh Atasan',
                'Description'       => "Pengajuan pengunduran diri disetujui oleh atasan. Formulir Exit Clearance draft ({$clearanceNo}) telah dibuat.",
                'Previous_Status'   => 'PENDING_APPROVAL',
                'New_Status'        => 'APPROVED',
                'Actor_ID'          => $approverId,
                'Payload'           => [
                    'Notes'        => $notes,
                    'Clearance_No' => $clearanceNo,
                ],
            ]);

            return $clearanceId;
        });
    }

    /**
     * Tolak Pengajuan Resign oleh Atasan Langsung
     */
    public function rejectResignation(int $resignationId, int $approverId, ?string $rejectionReason = '')
    {
        return DB::transaction(function () use ($resignationId, $approverId, $rejectionReason) {
            $resignation = DB::table('employee_resignations')->where('id', $resignationId)->first();
            if (!$resignation) {
                throw new Exception("Pengajuan resign tidak ditemukan.");
            }

            $now = Carbon::now();
            DB::table('employee_resignations')->where('id', $resignationId)->update([
                'Status' => 'REJECTED',
                'Rejection_Reason' => $rejectionReason,
                'Updated_By' => $approverId,
                'updated_at' => $now,
            ]);

            $employeeUser = DB::table('users')->where('id', $resignation->User_ID)->first();
            $employeeEmail = $employeeUser ? ($employeeUser->email ?? null) : null;

            $this->notificationService->notify(
                'RESIGN_REJECTED',
                $resignation,
                null,
                $resignation->User_ID,
                $employeeEmail,
                $rejectionReason
            );

            // Audit Trail: Penolakan Pengajuan Resign
            $this->logActivity([
                'Resignation_ID'  => $resignationId,
                'Action'          => 'RESIGNATION_REJECTED',
                'Title'           => 'Penolakan Pengajuan Resign',
                'Description'     => "Pengajuan pengunduran diri ditolak oleh atasan. Alasan: {$rejectionReason}",
                'Previous_Status' => 'PENDING_APPROVAL',
                'New_Status'      => 'REJECTED',
                'Actor_ID'        => $approverId,
                'Payload'         => [
                    'Rejection_Reason' => $rejectionReason,
                ],
            ]);

            return true;
        });
    }

    /**
     * Submit Formulir Exit Clearance oleh Karyawan (Bagian A, B, C, D & Checklist E)
     */
    public function submitExitClearance(int $clearanceId, array $payload, int $userId)
    {
        return DB::transaction(function () use ($clearanceId, $payload, $userId) {
            $clearance = DB::table('exit_clearances')->where('id', $clearanceId)->first();
            if (!$clearance) {
                throw new Exception("Form Exit Clearance tidak ditemukan.");
            }

            if ($clearance->User_ID != $userId && !auth()->user()->hasRole('Super Admin') && !auth()->user()->hasRole('ADM')) {
                throw new Exception("Anda tidak memiliki akses untuk mengisi formulir ini.");
            }

            $now = Carbon::now();

            // Simpan Kuesioner A, B, C, D & Deklarasi
            DB::table('exit_clearances')->where('id', $clearanceId)->update([
                'reasons_ratings' => !empty($payload['reasons_ratings']) ? json_encode($payload['reasons_ratings']) : null,
                'other_uncovered_reasons' => $payload['other_uncovered_reasons'] ?? null,
                'new_company_offers' => !empty($payload['new_company_offers']) ? json_encode($payload['new_company_offers']) : null,
                'company_ratings' => !empty($payload['company_ratings']) ? json_encode($payload['company_ratings']) : null,
                'division_feedbacks' => !empty($payload['division_feedbacks']) ? json_encode($payload['division_feedbacks']) : null,
                'company_suggestions' => $payload['company_suggestions'] ?? null,
                'employee_declaration' => !empty($payload['employee_declaration']),
                'Employee_Submitted_At' => $now,
                'Status' => 'PENDING_INTERVIEW', // Siap untuk sesi Exit Interview oleh HCGA
                'Updated_By' => $userId,
                'updated_at' => $now,
            ]);

            // Update 17 Item Checklist E
            if (!empty($payload['checklist']) && is_array($payload['checklist'])) {
                foreach ($payload['checklist'] as $valId => $itemData) {
                    DB::table('exit_clearance_checklist_values')
                        ->where('id', $valId)
                        ->where('Exit_Clearance_ID', $clearanceId)
                        ->update([
                            'Status' => $itemData['Status'] ?? 'TIDAK_ADA',
                            'Keterangan' => $itemData['Keterangan'] ?? null,
                            'Penanggung_Jawab' => $itemData['Penanggung_Jawab'] ?? null,
                            'updated_at' => $now,
                        ]);
                }
            }

            // Submit Workflow Approval (Policy: EXIT_CLEARANCE)
            try {
                $approval = $this->approvalWorkflow->submit(
                    [
                        'Request_No' => $clearance->No_Trx,
                        'Description' => 'Exit Clearance & Interview: ' . $clearance->Nama,
                    ],
                    [
                        'Request_Type' => 'EXIT_CLEARANCE',
                        'Department' => $clearance->Departemen,
                        'Location' => 'HEAD_OFFICE',
                        'Company_ID' => 1,
                        'Amount' => 0,
                    ],
                    $userId
                );

                if (!empty($approval->ID)) {
                    DB::table('approval_request')->where('ID', $approval->ID)->update([
                        'Map_To' => $clearanceId,
                    ]);

                    DB::table('exit_clearances')
                        ->where('id', $clearanceId)
                        ->update(['Approval_Request_ID' => $approval->ID]);
                }
            } catch (Exception $e) {
                Log::warning('ApprovalWorkflow submission failed for Exit Clearance: ' . $e->getMessage());
            }

            $resignation = DB::table('employee_resignations')->where('id', $clearance->Resignation_ID)->first();
            $this->notificationService->notify(
                'CLEARANCE_SUBMITTED',
                $resignation,
                $clearance,
                null,
                null
            );

            // Audit Trail: Pengisian Form Exit Clearance Selesai
            $this->logActivity([
                'Exit_Clearance_ID' => $clearanceId,
                'Resignation_ID'    => $clearance->Resignation_ID,
                'Action'            => 'CLEARANCE_SUBMITTED',
                'Title'             => 'Pengisian Formulir Exit Clearance',
                'Description'       => "Karyawan ({$clearance->Nama}) telah melengkapi kuesioner evaluasi dan checklist 17 butir pengembalian aset. Diteruskan ke HCGA untuk sesi Exit Interview.",
                'Previous_Status'   => 'DRAFT',
                'New_Status'        => 'PENDING_INTERVIEW',
                'Actor_ID'          => $userId,
                'Actor_Role'        => 'EMPLOYEE',
            ]);

            return true;
        });
    }

    /**
     * Input Hasil Exit Interview oleh Tim HCGA (Tahap 1 -> Diajukan ke Atasan Departemen Terkait)
     */
    public function recordInterviewByHcga(int $clearanceId, array $data, int $hcgaId)
    {
        $this->ensureFlowSchema();

        return DB::transaction(function () use ($clearanceId, $data, $hcgaId) {
            $clearance = DB::table('exit_clearances')->where('id', $clearanceId)->first();
            if (!$clearance) {
                throw new Exception("Exit Clearance tidak ditemukan.");
            }

            $hcgaUser = DB::table('users')->where('id', $hcgaId)->first();
            $isAdmin = ($hcgaUser && (in_array($hcgaUser->Position ?? '', ['Administrator']) || in_array(strtoupper(trim((string) ($hcgaUser->Priv_ID ?? ''))), ['ADMIN', 'SUPER ADMIN'])));
            $isHcga = ($hcgaUser && ($hcgaUser->Department ?? '') === 'HCGA');
            if (!$isAdmin && !$isHcga) {
                throw new Exception("Hanya tim HCGA atau Administrator yang berwenang mengisi hasil Exit Interview.");
            }

            $now = Carbon::now();

            $updateData = [
                'exit_interview_notes' => $data['exit_interview_notes'] ?? null,
                'Status'               => 'PENDING_MANAGER_APPROVAL', // Lanjut ke Tahap 2: Approval Atasan Departemen Terkait
                'Updated_By'           => $hcgaId,
                'updated_at'           => $now,
            ];

            if (Schema::hasColumn('exit_clearances', 'Hcga_Interview_By')) {
                $updateData['Hcga_Interview_By'] = $hcgaId;
            }
            if (Schema::hasColumn('exit_clearances', 'Hcga_Interview_At')) {
                $updateData['Hcga_Interview_At'] = $now;
            }

            DB::table('exit_clearances')->where('id', $clearanceId)->update($updateData);

            $resignation = DB::table('employee_resignations')->where('id', $clearance->Resignation_ID)->first();
            $this->notificationService->notify(
                'INTERVIEW_COMPLETED',
                $resignation,
                $clearance,
                $resignation ? $resignation->Approved_By : null,
                null
            );

            // Audit Trail: Sesi Exit Interview oleh HCGA Selesai
            $this->logActivity([
                'Exit_Clearance_ID' => $clearanceId,
                'Resignation_ID'    => $clearance->Resignation_ID,
                'Action'            => 'INTERVIEW_RECORDED',
                'Title'             => 'Sesi Exit Interview oleh HCGA Selesai',
                'Description'       => "Tim HCGA telah merekam hasil wawancara exit interview dan mengajukan berkas ke Atasan Departemen terkait untuk persetujuan.",
                'Previous_Status'   => 'PENDING_INTERVIEW',
                'New_Status'        => 'PENDING_MANAGER_APPROVAL',
                'Actor_ID'          => $hcgaId,
                'Actor_Role'        => 'HCGA',
                'Payload'           => [
                    'Interview_Notes' => $data['exit_interview_notes'] ?? null,
                ],
            ]);

            return true;
        });
    }

    /**
     * Approval Exit Clearance oleh Atasan Departemen Terkait (Tahap 2 -> Diteruskan ke Verifikasi Final HCGA)
     */
    public function approveByManager(int $clearanceId, array $data, int $managerId)
    {
        return DB::transaction(function () use ($clearanceId, $data, $managerId) {
            $clearance = DB::table('exit_clearances')->where('id', $clearanceId)->first();
            if (!$clearance) {
                throw new Exception("Exit Clearance tidak ditemukan.");
            }

            $now = Carbon::now();

            DB::table('exit_clearances')->where('id', $clearanceId)->update([
                'manager_notes'        => $data['manager_notes'] ?? null,
                'Manager_Interview_By' => $managerId,
                'Manager_Interview_At' => $now,
                'Status'               => 'PENDING_HCGA_APPROVAL', // Lanjut ke Tahap 3: Verifikasi Final HCGA
                'Updated_By'           => $managerId,
                'updated_at'           => $now,
            ]);

            $resignation = DB::table('employee_resignations')->where('id', $clearance->Resignation_ID)->first();
            $this->notificationService->notify(
                'MANAGER_APPROVED',
                $resignation,
                $clearance,
                null,
                null
            );

            // Audit Trail: Approval Atasan Departemen Selesai
            $this->logActivity([
                'Exit_Clearance_ID' => $clearanceId,
                'Resignation_ID'    => $clearance->Resignation_ID,
                'Action'            => 'MANAGER_APPROVED',
                'Title'             => 'Approval Exit Clearance oleh Atasan Departemen',
                'Description'       => "Atasan Departemen terkait telah menyetujui Exit Clearance dan meneruskan berkas ke tim HCGA untuk verifikasi final.",
                'Previous_Status'   => 'PENDING_MANAGER_APPROVAL',
                'New_Status'        => 'PENDING_HCGA_APPROVAL',
                'Actor_ID'          => $managerId,
                'Actor_Role'        => 'ATASAN',
                'Payload'           => [
                    'Manager_Notes' => $data['manager_notes'] ?? null,
                ],
            ]);

            return true;
        });
    }

    /**
     * Verifikasi & Final Approval oleh HCGA serta Penyimpanan Berkas Digital Otomatis
     */
    public function verifyAndApproveByHcga(int $clearanceId, array $data, int $hcgaId)
    {
        return DB::transaction(function () use ($clearanceId, $data, $hcgaId) {
            $clearance = DB::table('exit_clearances')->where('id', $clearanceId)->first();
            if (!$clearance) {
                throw new Exception("Exit Clearance tidak ditemukan.");
            }

            // Validasi kewenangan HCGA / Admin
            $hcgaUser = DB::table('users')->where('id', $hcgaId)->first();
            $isAdmin = ($hcgaUser && (in_array($hcgaUser->Position ?? '', ['Administrator']) || in_array(strtoupper(trim((string) ($hcgaUser->Priv_ID ?? ''))), ['ADMIN', 'SUPER ADMIN'])));
            $isHcga = ($hcgaUser && ($hcgaUser->Department ?? '') === 'HCGA');
            if (!$isAdmin && !$isHcga) {
                throw new Exception("Hanya tim HCGA atau Administrator yang berwenang melakukan verifikasi akhir Exit Clearance.");
            }

            $now = Carbon::now();

            DB::table('exit_clearances')->where('id', $clearanceId)->update([
                'hcga_notes' => $data['hcga_notes'] ?? null,
                'Hcga_Verified_By' => $hcgaId,
                'Hcga_Verified_At' => $now,
                'Status' => 'COMPLETED', // Selesai
                'Updated_By' => $hcgaId,
                'updated_at' => $now,
            ]);

            // Ambil data terbaru untuk generate dokumen arsip resmi
            $resignation = DB::table('employee_resignations')->where('id', $clearance->Resignation_ID)->first();
            $checklist = DB::table('exit_clearance_checklist_values')->where('Exit_Clearance_ID', $clearanceId)->get();
            $updatedClearance = DB::table('exit_clearances')->where('id', $clearanceId)->first();

            // Simpan Berkas Digital Final PDF ke Direktori Arsip
            try {
                $archiveDir = public_path('uploads/exit_clearance/archives');
                if (!is_dir($archiveDir)) {
                    mkdir($archiveDir, 0777, true);
                }

                $sanitizedNo = str_replace('/', '_', $updatedClearance->No_Trx);
                $fileName = "Final_Exit_Clearance_{$sanitizedNo}_" . time() . ".pdf";
                $filePath = $archiveDir . DIRECTORY_SEPARATOR . $fileName;
                $relativeFilePath = "uploads/exit_clearance/archives/" . $fileName;

                $options = new Options();
                $options->set('isRemoteEnabled', true);
                $options->set('isHtml5ParserEnabled', true);
                $dompdf = new Dompdf($options);

                $html = view('humanresources.exit-clearance.print-pdf', [
                    'clearance' => $updatedClearance,
                    'resignation' => $resignation,
                    'checklist' => $checklist,
                ])->render();

                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();

                file_put_contents($filePath, $dompdf->output());

                $fileSize = file_exists($filePath) ? filesize($filePath) : null;
                $checksum = file_exists($filePath) ? hash_file('sha256', $filePath) : null;

                // Catat ke tabel arsip dokumen digital
                DB::table('exit_clearance_documents')->insert([
                    'Exit_Clearance_ID' => $clearanceId,
                    'Document_Type' => 'FINAL_CLEARANCE_PDF',
                    'Document_No' => $updatedClearance->No_Trx,
                    'File_Name' => $fileName,
                    'File_Path' => $relativeFilePath,
                    'File_Size' => $fileSize,
                    'Mime_Type' => 'application/pdf',
                    'Sha256_Checksum' => $checksum,
                    'Created_By' => $hcgaId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } catch (Exception $e) {
                Log::warning("Gagal mengarsipkan berkas digital PDF Exit Clearance: " . $e->getMessage());
            }

            // Notifikasi Final Selesai ke Karyawan
            $employeeUser = DB::table('users')->where('id', $resignation->User_ID)->first();
            $employeeEmail = $employeeUser ? ($employeeUser->email ?? null) : null;

            $this->notificationService->notify(
                'FINAL_COMPLETED',
                $resignation,
                $updatedClearance,
                $resignation->User_ID,
                $employeeEmail
            );

            // Audit Trail: Verifikasi Final & Pengarsipan Berkas oleh HCGA
            $this->logActivity([
                'Exit_Clearance_ID' => $clearanceId,
                'Resignation_ID'    => $clearance->Resignation_ID,
                'Action'            => 'HCGA_VERIFIED',
                'Title'             => 'Verifikasi Final & Pengarsipan Berkas oleh HCGA',
                'Description'       => "Tim HCGA telah melakukan verifikasi akhir dan mengarsipkan dokumen resmi Exit Clearance ({$updatedClearance->No_Trx}).",
                'Previous_Status'   => 'PENDING_HCGA_APPROVAL',
                'New_Status'        => 'COMPLETED',
                'Actor_ID'          => $hcgaId,
                'Actor_Role'        => 'HCGA',
                'Payload'           => [
                    'Hcga_Notes' => $data['hcga_notes'] ?? null,
                ],
            ]);

            // Sinkronkan status ke tabel employment jika tanggal efektif resign telah terpenuhi
            $this->syncEmploymentResignationStatus($clearanceId);

            return true;
        });
    }

    /**
     * Sinkronisasi status pengunduran diri ke tabel employment, users, dan struktur organisasi.
     * Status employment berubah menjadi RESIGNED setelah tanggal pengunduran diri yang diajukan terpenuhi (Exit_Date <= today).
     */
    public function syncEmploymentResignationStatus(?int $clearanceId = null): array
    {
        $today = Carbon::today()->toDateString();

        $query = DB::table('exit_clearances as ec')
            ->join('employee_resignations as er', 'er.id', '=', 'ec.Resignation_ID')
            ->where('ec.Status', 'COMPLETED')
            ->select(
                'ec.id as clearance_id',
                'ec.No_Trx as clearance_no',
                'ec.Exit_Date',
                'ec.User_ID',
                'er.id as resignation_id',
                'er.Last_Working_Date',
                'er.Resign_Date',
                'er.Reason'
            );

        if ($clearanceId) {
            $query->where('ec.id', $clearanceId);
        } else {
            // Hanya periksa yang tanggal efektifnya sudah terpenuhi (Exit_Date <= today)
            $query->where(function ($q) use ($today) {
                $q->where(function ($sub) use ($today) {
                    $sub->whereNotNull('ec.Exit_Date')
                        ->where('ec.Exit_Date', '<=', $today);
                })->orWhere(function ($sub) use ($today) {
                    $sub->whereNull('ec.Exit_Date')
                        ->where('er.Last_Working_Date', '<=', $today);
                });
            });
        }

        $records = $query->get();
        $synced = [];

        foreach ($records as $item) {
            $effectiveDate = $item->Exit_Date ?: ($item->Last_Working_Date ?: $item->Resign_Date);
            $userId = (int) $item->User_ID;

            $user = DB::table('users')->where('id', $userId)->first();
            $nik = $user->Emp_ID ?? null;

            // Jika tanggal efektif belum terpenuhi (masih di masa depan)
            if ($effectiveDate > $today) {
                // Catat tanggal rencana resign di master employment tanpa mengubah status aktif
                if ($nik) {
                    DB::table('employment')->where('NIK', $nik)->update([
                        'Resigned_Date' => $effectiveDate,
                        'Resigned_Note' => $item->Reason ?? 'Pengunduran Diri (Exit Clearance Selesai)',
                        'Date_Out'      => $effectiveDate,
                        'Note_Out'      => 'SCHEDULED_RESIGN',
                    ]);

                    // Audit Trail: Pencatatan Rencana Resign di Master Employment
                    $this->logActivity([
                        'Exit_Clearance_ID' => $item->clearance_id,
                        'Resignation_ID'    => $item->resignation_id,
                        'Action'            => 'EMPLOYMENT_SYNC_SCHEDULED',
                        'Title'             => 'Pencatatan Rencana Resign di Master Employment',
                        'Description'       => "Tanggal efektif resign {$effectiveDate} dicatat pada master data employment (NIK: {$nik}). Status kepegawaian akan otomatis aktif sampai tanggal tersebut.",
                        'Previous_Status'   => 'ACTIVE',
                        'New_Status'        => 'SCHEDULED_RESIGN',
                        'Actor_ID'          => auth()->check() ? auth()->id() : null,
                        'Actor_Role'        => auth()->check() ? 'HCGA' : 'SYSTEM',
                        'Payload'           => [
                            'NIK'            => $nik,
                            'Effective_Date' => $effectiveDate,
                        ],
                    ]);
                }
                continue;
            }

            // Cek apakah sudah pernah diproses RESIGNED sebelumnya agar tidak duplikasi log & update
            if ($nik) {
                $currEmp = DB::table('employment')->where('NIK', $nik)->first();
                if ($currEmp && $currEmp->Status === 'RESIGNED') {
                    continue;
                }
            }

            // Tanggal pengunduran diri SUDAH TERPENUHI (effectiveDate <= today)
            // 1. Update tabel employment: Status menjadi RESIGNED
            if ($nik) {
                DB::table('employment')->where('NIK', $nik)->update([
                    'Status'            => 'RESIGNED',
                    'Employment_Status' => 'RESIGNED',
                    'Resigned_Date'     => $effectiveDate,
                    'Resigned_Note'     => $item->Reason ?? 'Pengunduran Diri (Exit Clearance Selesai)',
                    'Date_Out'          => $effectiveDate,
                    'Note_Out'          => 'RESIGNED',
                ]);
            }

            // 2. Nonaktifkan user account agar tidak login kembali
            DB::table('users')->where('id', $userId)->update([
                'Status' => 'INACTIVE',
            ]);

            // 3. Kosongkan slot di organization_structure (jadikan VACANT)
            if ($nik) {
                DB::table('organization_structure')
                    ->where('NIK', $nik)
                    ->update([
                        'NIK'  => null,
                        'Name' => 'VACANT',
                    ]);
            }

            // 4. Nonaktifkan assignment di Dynamic Approval Engine
            if (Schema::hasTable('position_assignment')) {
                DB::table('position_assignment')
                    ->where('User_Id', $userId)
                    ->update([
                        'Status' => 'INACTIVE',
                    ]);
            }

            // 5. Audit Trail: Status Employment Resmi Berubah Menjadi RESIGNED
            $this->logActivity([
                'Exit_Clearance_ID' => $item->clearance_id,
                'Resignation_ID'    => $item->resignation_id,
                'Action'            => 'EMPLOYMENT_STATUS_RESIGNED',
                'Title'             => 'Status Kepegawaian Diperbarui Menjadi RESIGNED',
                'Description'       => "Tanggal pengunduran diri ({$effectiveDate}) telah terpenuhi. Status di tabel employment telah diubah menjadi RESIGNED, akun sistem dinonaktifkan, dan slot struktur organisasi dijadikan VACANT.",
                'Previous_Status'   => 'ACTIVE',
                'New_Status'        => 'RESIGNED',
                'Actor_ID'          => auth()->check() ? auth()->id() : null,
                'Actor_Role'        => auth()->check() ? 'HCGA' : 'SYSTEM',
                'Payload'           => [
                    'NIK'            => $nik,
                    'Effective_Date' => $effectiveDate,
                ],
            ]);

            $synced[] = [
                'clearance_id'   => $item->clearance_id,
                'user_id'        => $userId,
                'nik'            => $nik,
                'effective_date' => $effectiveDate,
            ];
        }

        return $synced;
    }

    /**
     * Catat aktivitas ke tabel audit trail exit_clearance_logs
     */
    public function logActivity(array $data): void
    {
        try {
            $actorId = $data['Actor_ID'] ?? (auth()->check() ? auth()->id() : null);
            $actorName = $data['Actor_Name'] ?? null;
            if (!$actorName && $actorId) {
                $user = DB::table('users')->where('id', $actorId)->first();
                $actorName = $user ? ($user->Name ?? $user->username) : 'User #' . $actorId;
            } elseif (!$actorName && !$actorId) {
                $actorName = 'SYSTEM';
            }

            $actorRole = $data['Actor_Role'] ?? null;
            if (!$actorRole && $actorId) {
                $user = DB::table('users')->where('id', $actorId)->first();
                if ($user) {
                    if (($user->Position ?? '') === 'Administrator' || in_array(strtoupper(trim((string)($user->Priv_ID ?? ''))), ['ADMIN', 'SUPER ADMIN'])) {
                        $actorRole = 'ADMIN';
                    } elseif (($user->Department ?? '') === 'HCGA') {
                        $actorRole = 'HCGA';
                    } else {
                        $actorRole = 'ATASAN';
                    }
                }
            } elseif (!$actorRole) {
                $actorRole = 'SYSTEM';
            }

            $ip = null;
            $ua = null;
            if (function_exists('request') && request()) {
                $ip = request()->ip();
                $ua = request()->userAgent();
            }

            DB::table('exit_clearance_logs')->insert([
                'Exit_Clearance_ID' => $data['Exit_Clearance_ID'] ?? null,
                'Resignation_ID'    => $data['Resignation_ID'] ?? null,
                'Action'             => $data['Action'],
                'Title'              => $data['Title'] ?? $data['Action'],
                'Description'        => $data['Description'] ?? null,
                'Previous_Status'    => $data['Previous_Status'] ?? null,
                'New_Status'         => $data['New_Status'] ?? null,
                'Actor_ID'           => $actorId,
                'Actor_Name'         => $actorName,
                'Actor_Role'         => $actorRole,
                'Ip_Address'         => $data['Ip_Address'] ?? $ip,
                'User_Agent'         => $data['User_Agent'] ?? $ua,
                'Payload'            => !empty($data['Payload']) ? (is_string($data['Payload']) ? $data['Payload'] : json_encode($data['Payload'])) : null,
                'created_at'         => !empty($data['created_at']) ? $data['created_at'] : Carbon::now(),
            ]);
        } catch (\Exception $e) {
            Log::warning('Gagal mencatat audit log exit clearance: ' . $e->getMessage());
        }
    }

    /**
     * Backfill riwayat audit log otomatis untuk pengajuan eksisting jika tabel log masih kosong
     */
    public function backfillHistoricalLogs(int $clearanceId, int $resignationId): void
    {
        try {
            $resignation = DB::table('employee_resignations')->where('id', $resignationId)->first();
            $clearance = DB::table('exit_clearances')->where('id', $clearanceId)->first();

            if ($resignation) {
                // 1. Log Pengajuan Resign
                $creator = DB::table('users')->where('id', $resignation->Created_By ?? $resignation->User_ID)->first();
                $this->logActivity([
                    'Resignation_ID'    => $resignationId,
                    'Exit_Clearance_ID' => $clearanceId,
                    'Action'            => 'RESIGNATION_SUBMITTED',
                    'Title'             => 'Pengajuan Resign Baru',
                    'Description'       => "Pengajuan pengunduran diri untuk {$resignation->Nama} berhasil diajukan. Tanggal Terakhir Bekerja: {$resignation->Last_Working_Date}.",
                    'Previous_Status'   => null,
                    'New_Status'        => 'PENDING_APPROVAL',
                    'Actor_ID'          => $resignation->Created_By ?? $resignation->User_ID,
                    'Actor_Name'        => $creator ? ($creator->Name ?? $creator->username) : $resignation->Nama,
                    'Actor_Role'        => ($resignation->Created_By == $resignation->User_ID) ? 'EMPLOYEE' : 'ATASAN',
                    'created_at'        => $resignation->created_at,
                ]);

                // 2. Log Approval Resign oleh Atasan
                if (in_array($resignation->Status, ['APPROVED', 'COMPLETED']) || $resignation->Approved_At) {
                    $approver = $resignation->Approved_By ? DB::table('users')->where('id', $resignation->Approved_By)->first() : null;
                    $this->logActivity([
                        'Resignation_ID'    => $resignationId,
                        'Exit_Clearance_ID' => $clearanceId,
                        'Action'            => 'RESIGNATION_APPROVED',
                        'Title'             => 'Approval Pengajuan Resign oleh Atasan',
                        'Description'       => "Pengajuan resign telah disetujui oleh atasan.",
                        'Previous_Status'   => 'PENDING_APPROVAL',
                        'New_Status'        => 'APPROVED',
                        'Actor_ID'          => $resignation->Approved_By,
                        'Actor_Name'        => $approver ? ($approver->Name ?? $approver->username) : 'Atasan Langsung',
                        'Actor_Role'        => 'ATASAN',
                        'created_at'        => $resignation->Approved_At ?: $resignation->updated_at,
                    ]);
                } elseif ($resignation->Status === 'REJECTED') {
                    $rejector = $resignation->Updated_By ? DB::table('users')->where('id', $resignation->Updated_By)->first() : null;
                    $this->logActivity([
                        'Resignation_ID'    => $resignationId,
                        'Exit_Clearance_ID' => $clearanceId,
                        'Action'            => 'RESIGNATION_REJECTED',
                        'Title'             => 'Penolakan Pengajuan Resign',
                        'Description'       => "Pengajuan resign ditolak oleh atasan. Alasan: {$resignation->Rejection_Reason}",
                        'Previous_Status'   => 'PENDING_APPROVAL',
                        'New_Status'        => 'REJECTED',
                        'Actor_ID'          => $resignation->Updated_By,
                        'Actor_Name'        => $rejector ? ($rejector->Name ?? $rejector->username) : 'Atasan Langsung',
                        'Actor_Role'        => 'ATASAN',
                        'created_at'        => $resignation->updated_at,
                    ]);
                }
            }

            if ($clearance) {
                // 3. Log Pengisian Form Exit Clearance
                if ($clearance->Employee_Submitted_At || in_array($clearance->Status, ['PENDING_INTERVIEW', 'PENDING_MANAGER_APPROVAL', 'PENDING_HCGA_APPROVAL', 'COMPLETED'])) {
                    $employee = DB::table('users')->where('id', $clearance->User_ID)->first();
                    $this->logActivity([
                        'Resignation_ID'    => $resignationId,
                        'Exit_Clearance_ID' => $clearanceId,
                        'Action'            => 'CLEARANCE_SUBMITTED',
                        'Title'             => 'Pengisian Formulir Exit Clearance Selesai',
                        'Description'       => "Karyawan telah melengkapi kuesioner evaluasi dan checklist 17 butir pengembalian aset.",
                        'Previous_Status'   => 'DRAFT',
                        'New_Status'        => 'PENDING_INTERVIEW',
                        'Actor_ID'          => $clearance->User_ID,
                        'Actor_Name'        => $employee ? ($employee->Name ?? $employee->username) : $clearance->Nama,
                        'Actor_Role'        => 'EMPLOYEE',
                        'created_at'        => $clearance->Employee_Submitted_At ?: $clearance->updated_at,
                    ]);
                }

                // 4. Log Interview HCGA (atau legacy Atasan)
                $hcgaInterviewAt = $clearance->Hcga_Interview_At ?? null;
                $hcgaInterviewBy = $clearance->Hcga_Interview_By ?? null;
                if ($hcgaInterviewAt || (!empty($clearance->exit_interview_notes) && in_array($clearance->Status, ['PENDING_MANAGER_APPROVAL', 'PENDING_HCGA_APPROVAL', 'COMPLETED']))) {
                    $interviewer = $hcgaInterviewBy ? DB::table('users')->where('id', $hcgaInterviewBy)->first() : null;
                    $this->logActivity([
                        'Resignation_ID'    => $resignationId,
                        'Exit_Clearance_ID' => $clearanceId,
                        'Action'            => 'INTERVIEW_RECORDED',
                        'Title'             => 'Sesi Exit Interview oleh HCGA Selesai',
                        'Description'       => "Tim HCGA telah merekam hasil wawancara exit interview dan mengajukan ke Atasan Departemen.",
                        'Previous_Status'   => 'PENDING_INTERVIEW',
                        'New_Status'        => 'PENDING_MANAGER_APPROVAL',
                        'Actor_ID'          => $hcgaInterviewBy,
                        'Actor_Name'        => $interviewer ? ($interviewer->Name ?? $interviewer->username) : 'Tim HCGA',
                        'Actor_Role'        => 'HCGA',
                        'created_at'        => $hcgaInterviewAt ?: $clearance->updated_at,
                    ]);
                }

                // 5. Log Approval Atasan
                if ($clearance->Manager_Interview_At || in_array($clearance->Status, ['PENDING_HCGA_APPROVAL', 'COMPLETED'])) {
                    $approverAtasan = $clearance->Manager_Interview_By ? DB::table('users')->where('id', $clearance->Manager_Interview_By)->first() : null;
                    $this->logActivity([
                        'Resignation_ID'    => $resignationId,
                        'Exit_Clearance_ID' => $clearanceId,
                        'Action'            => 'MANAGER_APPROVED',
                        'Title'             => 'Approval Exit Clearance oleh Atasan Departemen',
                        'Description'       => "Atasan Departemen telah menyetujui Exit Clearance dan meneruskan ke HCGA untuk verifikasi final.",
                        'Previous_Status'   => 'PENDING_MANAGER_APPROVAL',
                        'New_Status'        => 'PENDING_HCGA_APPROVAL',
                        'Actor_ID'          => $clearance->Manager_Interview_By,
                        'Actor_Name'        => $approverAtasan ? ($approverAtasan->Name ?? $approverAtasan->username) : 'Atasan Langsung',
                        'Actor_Role'        => 'ATASAN',
                        'created_at'        => $clearance->Manager_Interview_At ?: $clearance->updated_at,
                    ]);
                }

                // 6. Log Verifikasi HCGA
                if ($clearance->Hcga_Verified_At || $clearance->Status === 'COMPLETED') {
                    $hcga = $clearance->Hcga_Verified_By ? DB::table('users')->where('id', $clearance->Hcga_Verified_By)->first() : null;
                    $this->logActivity([
                        'Resignation_ID'    => $resignationId,
                        'Exit_Clearance_ID' => $clearanceId,
                        'Action'            => 'HCGA_VERIFIED',
                        'Title'             => 'Verifikasi Final & Pengarsipan Berkas oleh HCGA',
                        'Description'       => "Tim HCGA telah melakukan verifikasi akhir dan mengarsipkan dokumen resmi Exit Clearance.",
                        'Previous_Status'   => 'PENDING_HCGA_APPROVAL',
                        'New_Status'        => 'COMPLETED',
                        'Actor_ID'          => $clearance->Hcga_Verified_By,
                        'Actor_Name'        => $hcga ? ($hcga->Name ?? $hcga->username) : 'Tim HCGA',
                        'Actor_Role'        => 'HCGA',
                        'created_at'        => $clearance->Hcga_Verified_At ?: $clearance->updated_at,
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Gagal backfill historical audit logs: ' . $e->getMessage());
        }
    }

    /**
     * Ambil riwayat audit trail untuk Exit Clearance / Resignation
     */
    public function getLogs(?int $clearanceId = null, ?int $resignationId = null)
    {
        if (!Schema::hasTable('exit_clearance_logs')) {
            return collect();
        }

        if ($clearanceId && $resignationId) {
            $count = DB::table('exit_clearance_logs')
                ->where(function ($q) use ($clearanceId, $resignationId) {
                    $q->where('Exit_Clearance_ID', $clearanceId)
                      ->orWhere('Resignation_ID', $resignationId);
                })
                ->count();

            if ($count === 0) {
                $this->backfillHistoricalLogs($clearanceId, $resignationId);
            }
        }

        $query = DB::table('exit_clearance_logs');

        if ($clearanceId && $resignationId) {
            $query->where(function ($q) use ($clearanceId, $resignationId) {
                $q->where('Exit_Clearance_ID', $clearanceId)
                  ->orWhere('Resignation_ID', $resignationId);
            });
        } elseif ($clearanceId) {
            $query->where('Exit_Clearance_ID', $clearanceId);
        } elseif ($resignationId) {
            $query->where('Resignation_ID', $resignationId);
        } else {
            return collect();
        }

        return $query->orderBy('id', 'desc')->get();
    }

    /**
     * Master Checklist Items
     */
    public function getMasterChecklistItems()
    {
        return DB::table('master_exit_clearance_items')
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();
    }
}
