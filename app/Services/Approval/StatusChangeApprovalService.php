<?php

namespace App\Services\Approval;

use App\Exceptions\ApprovalWorkflowException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service Adapter antara modul Perubahan Status Karyawan (Mutasi & Promosi)
 * dan Engine Dynamic Approval Policy (ApprovalWorkflowService).
 */
class StatusChangeApprovalService
{
    const REQUEST_TYPE = 'STATUS_CHANGE';

    const STATUS_DRAFT       = 'DRAFT';
    const STATUS_SUBMITTED   = 'SUBMITTED';
    const STATUS_IN_APPROVAL = 'IN_APPROVAL';
    const STATUS_APPROVED    = 'APPROVED';
    const STATUS_REJECTED    = 'REJECTED';
    const STATUS_CANCELLED   = 'CANCELLED';

    protected $workflow;
    protected $policyResolver;

    public function __construct(ApprovalWorkflowService $workflow, ApprovalPolicyResolver $policyResolver)
    {
        $this->workflow = $workflow;
        $this->policyResolver = $policyResolver;
    }

    /**
     * Submit form perubahan status ke approval engine.
     *
     * @param object|array $statusChange Baris record status_changes
     * @param object $user User login pemohon
     * @return object Approval Request
     */
    public function submit($statusChange, $user)
    {
        $sc = is_array($statusChange) ? (object) $statusChange : $statusChange;
        $scId = (int) $sc->id;

        return DB::transaction(function () use ($sc, $scId, $user) {
            $context = $this->buildContext($sc, $user);

            $jenisLabel = $sc->jenis === 'PROMOTION' ? 'Promosi' : 'Mutasi';
            $description = "Pengajuan {$jenisLabel} Karyawan: {$sc->employee_name} ({$sc->nik}) - Form: {$sc->form_number}";

            $approvalReq = null;
            try {
                $approvalReq = $this->workflow->submit([
                    'Request_No'  => $sc->form_number,
                    'Description' => $description,
                ], $context, (int) $user->id);
            } catch (\Throwable $e) {
                // Fallback context: wildcard location & department jika policy spesifik dept/lokasi tidak cocok
                $context['Location'] = null;
                $context['Department'] = null;
                $approvalReq = $this->workflow->submit([
                    'Request_No'  => $sc->form_number,
                    'Description' => $description,
                ], $context, (int) $user->id);
            }

            $requestId = (int) $approvalReq->ID;

            DB::table('approval_request')->where('ID', $requestId)->update([
                'Map_To' => $scId,
            ]);

            $newStatus = $this->mapApprovalStatus((string) $approvalReq->Status);

            DB::table('status_changes')->where('id', $scId)->update([
                'status'              => $newStatus,
                'approval_request_id' => $requestId,
                'updated_at'          => Carbon::now(),
            ]);

            // Jika langsung disetujui (misal auto-approve), update employment
            if ($newStatus === self::STATUS_APPROVED) {
                $this->applyApprovedChanges($scId, $user->username ?? 'system');
            }

            return $approvalReq;
        });
    }

    /**
     * Sinkronisasi status approval_request ke status_changes.
     * Dipanggil saat melihat detail, webhook/event, atau aksi approval.
     */
    public function syncApprovalStatus(int $statusChangeId, $actorUsername = 'system')
    {
        $sc = DB::table('status_changes')->where('id', $statusChangeId)->first();
        if (!$sc || empty($sc->approval_request_id)) {
            return $sc;
        }

        $apprReq = DB::table('approval_request')->where('ID', (int) $sc->approval_request_id)->first();
        if (!$apprReq) {
            return $sc;
        }

        $mappedStatus = $this->mapApprovalStatus((string) $apprReq->Status);

        if ($mappedStatus !== strtoupper((string) $sc->status)) {
            DB::table('status_changes')->where('id', $statusChangeId)->update([
                'status'     => $mappedStatus,
                'updated_at' => Carbon::now(),
            ]);

            $sc->status = $mappedStatus;

            // Jika baru saja menjadi APPROVED, terapkan perubahan ke data karyawan & riwayat
            if ($mappedStatus === self::STATUS_APPROVED) {
                $this->applyApprovedChanges($statusChangeId, $actorUsername);
            }
        }

        return $sc;
    }

    /**
     * Terapkan efek perubahan status ke tabel employment dan catat ke employment_history.
     * Dipanggil HANYA saat form berstatus APPROVED (idempoten).
     */
    public function applyApprovedChanges(int $statusChangeId, string $actorUsername = 'system'): void
    {
        $sc = DB::table('status_changes')->where('id', $statusChangeId)->first();
        if (!$sc || empty($sc->nik)) {
            return;
        }

        $nik = trim((string) $sc->nik);
        $emp = DB::table('employment')->where('NIK', $nik)->orderBy('ID', 'ASC')->first();
        if (!$emp) {
            Log::warning("StatusChange applyApprovedChanges: Employment record with NIK '{$nik}' not found for status change ID {$statusChangeId}");
            return;
        }

        $now = Carbon::now();

        // 1. Cek apakah riwayat untuk form_number ini sudah ada (idempotensi)
        $existingHistory = DB::table('employment_history')
            ->where('NIK', $nik)
            ->where('Reference', $sc->form_number)
            ->first();

        if (!$existingHistory) {
            $catatan = 'Jenis: ' . ($sc->jenis ?: 'MUTATION');
            if ((string) $sc->current_position !== '' || (string) $sc->proposed_position !== '') {
                $catatan .= '; Jabatan: ' . ($sc->current_position ?: '-') . ' -> ' . ($sc->proposed_position ?: '-');
            }
            if ((string) $sc->current_department !== '' || (string) $sc->proposed_department !== '') {
                $catatan .= '; Departemen: ' . ($sc->current_department ?: '-') . ' -> ' . ($sc->proposed_department ?: '-');
            }
            if ((string) $sc->current_work_location !== '' || (string) $sc->proposed_work_location !== '') {
                $catatan .= '; Lokasi: ' . ($sc->current_work_location ?: '-') . ' -> ' . ($sc->proposed_work_location ?: '-');
            }
            if ($sc->current_salary != $sc->proposed_salary) {
                $catatan .= '; Salary: ' . number_format((float) $sc->current_salary, 0, ',', '.') . ' -> ' . number_format((float) $sc->proposed_salary, 0, ',', '.');
            }

            DB::table('employment_history')->insert([
                'Task_ID'      => $emp->Task_ID ?? '',
                'NIK'          => $nik,
                'Date_Process' => $now,
                'Activity'     => 'PERUBAHAN STATUS (' . ($sc->jenis ?: 'MUTATION') . ')',
                'Reference'    => $sc->form_number,
                'Notes'        => $catatan,
                'Start_Date'   => $sc->effective_date ?: date('Y-m-d'),
                'End_Date'     => '',
                'Remark'       => $sc->jenis === 'PROMOTION' ? 'Promosi' : 'Mutasi',
                'User_Trx'     => $actorUsername,
                'Date_Trx'     => $now,
            ]);
        }

        // 2. Perbarui data profil karyawan pada tabel employment
        $empUpdates = [];
        if (!empty($sc->proposed_position)) {
            $empUpdates['Position'] = $sc->proposed_position;
        }
        if (!empty($sc->proposed_department)) {
            $empUpdates['Department'] = $sc->proposed_department;
        }
        if (!empty($sc->proposed_division)) {
            $empUpdates['Directorat'] = $sc->proposed_division;
        }
        if (!empty($sc->proposed_work_location)) {
            $empUpdates['Location'] = $sc->proposed_work_location;
        }
        if (!empty($sc->proposed_employment_status)) {
            $empUpdates['Employment_Status'] = $sc->proposed_employment_status;
        }
        if (!empty($sc->proposed_grade)) {
            $empUpdates['Employment_Grade'] = $sc->proposed_grade;
        }
        if (!empty($sc->proposed_marital_dependents)) {
            $empUpdates['Marital_Status'] = $sc->proposed_marital_dependents;
        }
        if ($sc->proposed_salary !== null && (float) $sc->proposed_salary > 0) {
            $empUpdates['Salary_Accepted'] = (float) $sc->proposed_salary;
        }

        if (!empty($empUpdates)) {
            $existingNotes = (string) ($emp->Notes ?? '');
            $empUpdates['Conversion_Date'] = $now;
            $empUpdates['Notes'] = trim($existingNotes . ' Status change approved by [User: ' . $actorUsername . ']');
            DB::table('employment')->where('ID', $emp->ID)->update($empUpdates);
        }

        // 3. Perbarui profil user pada tabel users yang terhubung via Emp_ID / NIK
        $userUpdates = [];
        if (!empty($sc->proposed_position)) {
            $userUpdates['Position'] = $sc->proposed_position;
        }
        if (!empty($sc->proposed_department)) {
            $userUpdates['Department'] = $sc->proposed_department;
        }
        if (!empty($sc->proposed_work_location)) {
            $userUpdates['Location'] = $sc->proposed_work_location;
        }

        if (!empty($userUpdates)) {
            $userUpdates['update_at'] = $now;
            DB::table('users')->where('Emp_ID', $nik)->update($userUpdates);
        }

        // 4. Perbarui atau sinkronkan ke tabel organization_structure (Bagan Organisasi)
        $this->syncOrganizationStructure($emp, $sc, $nik, $actorUsername);

        Log::info("StatusChange applyApprovedChanges success for NIK '{$nik}' [Form: {$sc->form_number}]");
    }

    /**
     * Sinkronkan perubahan posisi dan data karyawan ke tabel organization_structure.
     */
    protected function syncOrganizationStructure($emp, $sc, string $nik, string $actorUsername = 'system'): void
    {
        if (empty($nik)) {
            return;
        }

        $proposedPos  = trim((string) ($sc->proposed_position ?: ''));
        $proposedDept = trim((string) ($sc->proposed_department ?: ($emp->Department ?? '')));
        $proposedDiv  = trim((string) ($sc->proposed_division ?: ($emp->Directorat ?? '')));

        // Cek data lama karyawan di tabel organization_structure (sebelum update)
        $existingOrg = DB::table('organization_structure')->where('NIK', $nik)->first();
        $oldPos  = $existingOrg ? trim((string) $existingOrg->Position) : trim((string) ($sc->current_position ?: ($emp->Position ?? '')));
        $oldDept = $existingOrg ? trim((string) $existingOrg->Department) : trim((string) ($sc->current_department ?: ($emp->Department ?? '')));

        // Ambil data master_position untuk mengetahui Position_ID, Level, dan Nama Lengkap Jabatan
        $mp = null;
        if ($proposedPos !== '') {
            $mpQuery = DB::table('master_position')->where(function ($sub) use ($proposedPos) {
                $sub->where('Initial', $proposedPos)
                    ->orWhere('Position', $proposedPos);
            });

            if ($proposedDept !== '') {
                $mpExact = (clone $mpQuery)->where('Department', $proposedDept)->first();
                if ($mpExact) {
                    $mp = $mpExact;
                }
            }

            if (!$mp) {
                $mp = $mpQuery->first();
            }
        }

        $empName = trim((string) ($emp->Name ?? ($sc->employee_name ?? '')));
        $posId   = $mp ? (int) $mp->ID : null;
        $level   = $mp && !empty($mp->Level) ? (int) $mp->Level : 4;
        $posCode = $mp && !empty($mp->Initial_Code) ? (string) $mp->Initial_Code : $proposedPos;

        // Tentukan atasan (Reports_To_Position_ID) secara dinamis berdasarkan hierarki posisi
        $reportsToOrgId = null;

        // 1. Prioritas 1: Jika posisi baru sama dengan posisi karyawan lain di departemen yang sama di organization_structure
        if ($proposedPos !== '') {
            $siblingOrgQuery = DB::table('organization_structure')
                ->where('Position', $proposedPos)
                ->where('NIK', '<>', $nik)
                ->whereNotNull('Reports_To_Position_ID')
                ->where('Reports_To_Position_ID', '>', 0);

            if ($proposedDept !== '') {
                $siblingSameDept = (clone $siblingOrgQuery)->where('Department', $proposedDept)->first();
                if ($siblingSameDept) {
                    $reportsToOrgId = (int) $siblingSameDept->Reports_To_Position_ID;
                }
            }

            if (!$reportsToOrgId) {
                $siblingAny = $siblingOrgQuery->first();
                if ($siblingAny) {
                    $reportsToOrgId = (int) $siblingAny->Reports_To_Position_ID;
                }
            }
        }

        // 2. Prioritas 2: Jika belum dapat, cari lewat master_position.Parent_Position_ID
        if (!$reportsToOrgId && $mp && !empty($mp->Parent_Position_ID)) {
            $parentMp = DB::table('master_position')->where('ID', (int) $mp->Parent_Position_ID)->first();
            if ($parentMp) {
                // Cari node atasan di organization_structure
                $parentOrgQuery = DB::table('organization_structure')->where(function ($sub) use ($parentMp) {
                    $sub->where('Position_ID', (int) $parentMp->ID)
                        ->orWhere('Position', $parentMp->Initial)
                        ->orWhere('Position', $parentMp->Position);
                });

                if ($proposedDept !== '') {
                    $parentOrgSameDept = (clone $parentOrgQuery)->where('Department', $proposedDept)->first();
                    if ($parentOrgSameDept) {
                        $reportsToOrgId = (int) $parentOrgSameDept->ID;
                    }
                }

                if (!$reportsToOrgId) {
                    $parentOrgAny = $parentOrgQuery->first();
                    if ($parentOrgAny) {
                        $reportsToOrgId = (int) $parentOrgAny->ID;
                    }
                }
            }
        }

        // Cek apakah ada posisi tujuan yang berstatus VACANT pada departemen/posisi yang sama
        $vacantTarget = null;
        if ($proposedPos !== '') {
            $vacantQuery = DB::table('organization_structure')
                ->where('Position', $proposedPos)
                ->where(function ($q) {
                    $q->whereNull('NIK')
                      ->orWhere('NIK', '')
                      ->orWhere('Name', 'like', '%VACANT%');
                });

            if ($proposedDept !== '') {
                $vacantTarget = (clone $vacantQuery)->where('Department', $proposedDept)->first();
            }
            if (!$vacantTarget) {
                $vacantTarget = $vacantQuery->first();
            }
        }

        // Siapkan data organisasi yang akan diperbarui
        $orgData = [
            'NIK'        => $nik,
            'Name'       => $empName,
            'Department' => $proposedDept,
            'Dept_Name'  => $proposedDept,
            'Directorat' => $proposedDiv,
            'Level'      => $level,
            'Is_System'  => 0,
        ];

        if ($posId) {
            $orgData['Position_ID'] = $posId;
        }
        if ($proposedPos !== '') {
            $orgData['Position']      = $proposedPos;
            $orgData['Position_Code'] = $posCode;
        }

        // Tentukan Reports_To_Position_ID
        if ($reportsToOrgId) {
            $orgData['Reports_To_Position_ID'] = $reportsToOrgId;
        } elseif ($vacantTarget && !empty($vacantTarget->Reports_To_Position_ID)) {
            $orgData['Reports_To_Position_ID'] = (int) $vacantTarget->Reports_To_Position_ID;
        }

        // --- SKENARIO 1: POSISI TUJUAN ADA YANG BERSTATUS VACANT ---
        if ($vacantTarget) {
            if ($existingOrg && (int) $existingOrg->ID === (int) $vacantTarget->ID) {
                // Karyawan sudah berada di baris tersebut
                DB::table('organization_structure')->where('ID', $existingOrg->ID)->update($orgData);
            } else {
                // Isi slot VACANT dengan profil karyawan ini
                DB::table('organization_structure')->where('ID', $vacantTarget->ID)->update($orgData);
                Log::info("OrganizationStructure vacant slot filled [Org ID: {$vacantTarget->ID}] by NIK '{$nik}'");

                // Jika karyawan sebelumnya punya baris lain di organization_structure, kelola baris lama tersebut
                if ($existingOrg && (int) $existingOrg->ID !== (int) $vacantTarget->ID) {
                    $this->handleVacatedOrgRow($existingOrg, $nik);
                }
            }
        }
        // --- SKENARIO 2: KARYAWAN SUDAH TERDAFTAR SEBELUMNYA DI TABEL ---
        elseif ($existingOrg) {
            // Cek apakah posisi karyawan berpindah (mutasi posisi)
            $isPositionChanged = ($oldPos !== '' && $proposedPos !== '' && $oldPos !== $proposedPos);

            if ($isPositionChanged) {
                // Cek apakah masih ada rekan lain yang menempati posisi lama di departemen lama
                $remainingCount = DB::table('organization_structure')
                    ->where('Position', $oldPos)
                    ->where('NIK', '<>', $nik)
                    ->whereNotNull('NIK')
                    ->where('NIK', '<>', '')
                    ->where('Name', 'not like', '%VACANT%')
                    ->when($oldDept !== '', function ($q) use ($oldDept) {
                        return $q->where('Department', $oldDept);
                    })
                    ->count();

                if ($remainingCount === 0) {
                    // Tidak ada karyawan lain di posisi lama -> Posisi lama harus tetap ada sebagai VACANT,
                    // dan karyawan menempati baris baru untuk posisi barunya.
                    DB::table('organization_structure')->where('ID', $existingOrg->ID)->update([
                        'NIK'   => '',
                        'Name'  => 'VACANT',
                        'Email' => '',
                    ]);
                    Log::info("OrganizationStructure position '{$oldPos}' [Org ID: {$existingOrg->ID}] set to VACANT as last employee NIK '{$nik}' moved out");

                    // Buat baris baru untuk karyawan pada posisi baru
                    $newOrgId = DB::table('organization_structure')->insertGetId($orgData);
                    Log::info("OrganizationStructure inserted for NIK '{$nik}' [Org ID: {$newOrgId}] on new position '{$proposedPos}'");
                } else {
                    // Masih ada 1 atau lebih karyawan di posisi lama -> Baris eksisting karyawan langsung diupdate ke posisi baru
                    DB::table('organization_structure')->where('ID', $existingOrg->ID)->update($orgData);
                    Log::info("OrganizationStructure updated for NIK '{$nik}' [Org ID: {$existingOrg->ID}] to position '{$proposedPos}' (old position still staffed by {$remainingCount} employees)");
                }
            } else {
                // Posisi tidak berubah, cukup update detail data (nama, departemen, dsb)
                DB::table('organization_structure')->where('ID', $existingOrg->ID)->update($orgData);
                Log::info("OrganizationStructure updated for NIK '{$nik}' [Org ID: {$existingOrg->ID}]");
            }
        }
        // --- SKENARIO 3: KARYAWAN BARU BELUM PUNYA BARIS DI ORGANISASI ---
        else {
            $newOrgId = DB::table('organization_structure')->insertGetId($orgData);
            Log::info("OrganizationStructure inserted for NIK '{$nik}' [Org ID: {$newOrgId}]");
        }
    }

    /**
     * Tangani baris organisasi lama saat karyawan mengisi slot VACANT di posisi lain.
     */
    protected function handleVacatedOrgRow($oldOrgRow, string $nik): void
    {
        $oldPos  = trim((string) $oldOrgRow->Position);
        $oldDept = trim((string) $oldOrgRow->Department);

        $remainingCount = DB::table('organization_structure')
            ->where('Position', $oldPos)
            ->where('ID', '<>', $oldOrgRow->ID)
            ->where('NIK', '<>', $nik)
            ->whereNotNull('NIK')
            ->where('NIK', '<>', '')
            ->where('Name', 'not like', '%VACANT%')
            ->when($oldDept !== '', function ($q) use ($oldDept) {
                return $q->where('Department', $oldDept);
            })
            ->count();

        if ($remainingCount === 0) {
            // Posisi lama menjadi VACANT agar slot jabatan tidak hilang dari bagan
            DB::table('organization_structure')->where('ID', $oldOrgRow->ID)->update([
                'NIK'   => '',
                'Name'  => 'VACANT',
                'Email' => '',
            ]);
            Log::info("OrganizationStructure old row [Org ID: {$oldOrgRow->ID}] set to VACANT");
        } else {
            // Posisi lama masih memiliki rekan lain, hapus duplikat baris lama agar tidak ada baris menggantung
            DB::table('organization_structure')->where('ID', $oldOrgRow->ID)->delete();
            Log::info("OrganizationStructure redundant old row [Org ID: {$oldOrgRow->ID}] deleted because {$remainingCount} employees remain in position '{$oldPos}'");
        }
    }

    /**
     * Dapatkan detail request approval dan daftar step/task beserta log review.
     */
    public function getApprovalDetails(int $statusChangeId): array
    {
        $sc = DB::table('status_changes')->where('id', $statusChangeId)->first();
        if (!$sc || empty($sc->approval_request_id)) {
            return [
                'request' => null,
                'tasks'   => [],
                'logs'    => [],
            ];
        }

        $requestId = (int) $sc->approval_request_id;
        $request = DB::table('approval_request')->where('ID', $requestId)->first();

        $tasks = DB::table('approval_request_task as t')
            ->leftJoin('users as u', 'u.id', '=', 't.Claimed_By')
            ->leftJoin('employment as ce_claim', 'ce_claim.NIK', '=', 'u.Emp_ID')
            ->leftJoin('users as au', 'au.id', '=', 't.Acted_By')
            ->leftJoin('employment as ce_act', 'ce_act.NIK', '=', 'au.Emp_ID')
            ->where('t.Request_ID', $requestId)
            ->select(
                't.*',
                'u.Name as Claimed_User_Name',
                'u.username as Claimed_By_Username',
                'ce_claim.Name as Claimed_Emp_Name',
                'au.Name as Acted_User_Name',
                'au.username as Acted_By_Username',
                'ce_act.Name as Acted_Emp_Name'
            )
            ->orderBy('t.Stage_Order', 'ASC')
            ->orderBy('t.ID', 'ASC')
            ->get()
            ->map(function ($t) {
                $snapshot = !empty($t->Snapshot) ? json_decode($t->Snapshot, true) : [];
                $t->Label = $snapshot['Step']['Label'] ?? ($t->Position_Name ?? ('Level ' . $t->Level_Number));

                $t->Claimed_By_Name = !empty($t->Claimed_By)
                    ? (trim((string) ($t->Claimed_Emp_Name ?: $t->Claimed_User_Name)) ?: $t->Claimed_By_Username)
                    : null;

                $t->Acted_By_Name = !empty($t->Acted_By)
                    ? (trim((string) ($t->Acted_Emp_Name ?: $t->Acted_User_Name)) ?: $t->Acted_By_Username)
                    : null;

                // Ambil kandidat approver pertama untuk ditampilkan jika task belum di-claim/approve
                $candidateUser = DB::table('approval_task_candidate as c')
                    ->join('users as cu', 'cu.id', '=', 'c.User_Id')
                    ->leftJoin('employment as ce', 'ce.NIK', '=', 'cu.Emp_ID')
                    ->where('c.Task_ID', $t->ID)
                    ->where('c.Status', 'ACTIVE')
                    ->select('cu.Name as User_Name', 'cu.username as Username', 'ce.Name as Emp_Name', 'c.Candidate_Type')
                    ->orderBy('c.ID', 'ASC')
                    ->first();

                $t->Candidate_Name = $candidateUser
                    ? (trim((string) ($candidateUser->Emp_Name ?: $candidateUser->User_Name)) ?: $candidateUser->Username)
                    : '';

                return $t;
            });

        $logs = DB::table('approval_log as l')
            ->leftJoin('users as u', 'u.id', '=', 'l.Approver_Id')
            ->where('l.Request_Id', $requestId)
            ->select('l.*', 'u.Name as User_Name', 'u.username as Username')
            ->orderBy('l.ID', 'DESC')
            ->get();

        return [
            'request' => $request,
            'tasks'   => $tasks,
            'logs'    => $logs,
        ];
    }

    /**
     * Dapatkan mapping tanda tangan / approver dinamis untuk dokumen print PDF.
     */
    public function getSignaturesForPrint(int $statusChangeId): array
    {
        $sc = DB::table('status_changes')->where('id', $statusChangeId)->first();
        $details = $this->getApprovalDetails($statusChangeId);
        $tasks = $details['tasks'];

        // Cari nama pengusul (proposed_by atau dari Requester_Id di approval_request)
        $proposerName = $sc && !empty($sc->proposed_by) ? trim((string) $sc->proposed_by) : '';
        if ($proposerName === '' && !empty($details['request']) && !empty($details['request']->Requester_Id)) {
            $reqUser = DB::table('users as u')
                ->leftJoin('employment as e', 'e.NIK', '=', 'u.Emp_ID')
                ->where('u.id', (int) $details['request']->Requester_Id)
                ->select('u.Name as User_Name', 'u.username as Username', 'e.Name as Emp_Name')
                ->first();
            if ($reqUser) {
                $proposerName = trim((string) ($reqUser->Emp_Name ?: $reqUser->User_Name)) ?: $reqUser->Username;
            }
        }
        // Bersihkan "(username)" di akhir nama pengusul jika ada agar rapi di cetakan
        $proposerClean = preg_replace('/\s*\([^)]+\)\s*$/', '', $proposerName);
        if (trim((string) $proposerClean) !== '') {
            $proposerName = trim((string) $proposerClean);
        }

        // Cari kandidat default dari master_position / employment jika task belum terbentuk
        $originDept = $sc ? trim((string) $sc->current_department) : '';
        $destDept   = $sc ? trim((string) $sc->proposed_department) : '';

        $defaultOriginHead = ($sc && !empty($sc->origin_department_head))
            ? $sc->origin_department_head
            : $this->resolveDeptHeadName($originDept);
        if ($defaultOriginHead === '' && $proposerName !== '') {
            $defaultOriginHead = $proposerName;
        }

        $defaultDestHead = '';
        if ($destDept !== '' && strcasecmp($destDept, $originDept) !== 0) {
            $defaultDestHead = ($sc && !empty($sc->destination_department_head))
                ? $sc->destination_department_head
                : $this->resolveDeptHeadName($destDept);
        } elseif ($sc && !empty($sc->destination_department_head)) {
            $defaultDestHead = $sc->destination_department_head;
        }

        $defaultHcga = ($sc && !empty($sc->hcga_verifier))
            ? $sc->hcga_verifier
            : ($this->resolvePositionHolderName('MHCGA', 'HCGA') ?: 'Arie S. Wirawan');

        $defaultDirector = ($sc && !empty($sc->director_approver))
            ? $sc->director_approver
            : ($this->resolvePositionHolderName('VPD', null, [1, 2]) ?: 'Dhimas Arif Wibowo');

        // Siapkan slot default
        $signs = [
            'proposer' => [
                'role'     => 'Diusulkan oleh',
                'position' => 'Pengusul',
                'name'     => $proposerName ?: '-',
                'status'   => 'SUBMITTED',
                'date'     => $sc ? $sc->created_at : null,
                'qr_data'  => null,
            ],
            'origin' => [
                'role'     => 'Departemen Asal',
                'position' => 'Dept. Head',
                'name'     => $defaultOriginHead,
                'status'   => 'PENDING',
                'date'     => null,
                'qr_data'  => null,
            ],
            'destination' => [
                'role'     => 'Departemen Tujuan',
                'position' => 'Dept. Head',
                'name'     => $defaultDestHead,
                'status'   => 'PENDING',
                'date'     => null,
                'qr_data'  => null,
            ],
            'hcga' => [
                'role'     => 'Diverifikasi',
                'position' => 'Senior Manager HCGA',
                'name'     => $defaultHcga,
                'status'   => 'PENDING',
                'date'     => null,
                'qr_data'  => null,
            ],
            'director' => [
                'role'     => 'Mengetahui',
                'position' => 'Direktur',
                'name'     => $defaultDirector,
                'status'   => 'PENDING',
                'date'     => null,
                'qr_data'  => null,
            ],
        ];

        $statusPriority = [
            'CANCELLED'     => 1,
            'SKIPPED'       => 1,
            'PENDING'       => 2,
            'CLAIMED'       => 3,
            'REJECTED'      => 4,
            'RETURNED'      => 4,
            'AUTO_APPROVED' => 5,
            'APPROVED'      => 6,
        ];

        foreach ($tasks as $task) {
            $label = strtolower((string) ($task->Label ?: $task->Position_Name));
            $posCode = strtoupper((string) ($task->Position_Code ?: ''));
            $level = (int) $task->Level_Number;
            $taskStatus = strtoupper((string) $task->Status);

            // Prioritas nama: Acted_By (eksekutor final) -> Claimed_By (sedang klaim) -> Candidate_Name (kandidat default)
            $actor = $task->Acted_By_Name ?: ($task->Claimed_By_Name ?: ($task->Candidate_Name ?? ''));

            $targetKey = null;
            if (strpos($label, 'asal') !== false || ($level === 3 && (int) $task->Stage_Order === 1)) {
                $targetKey = 'origin';
            } elseif (strpos($label, 'tujuan') !== false) {
                $targetKey = 'destination';
            } elseif (strpos($label, 'hcga') !== false || $posCode === 'MHCGA') {
                $targetKey = 'hcga';
            } elseif (strpos($label, 'direktur') !== false || $posCode === 'VPD' || $level <= 2) {
                $targetKey = 'director';
            }

            if ($targetKey) {
                $existingStatus = strtoupper((string) ($signs[$targetKey]['status'] ?? 'PENDING'));
                $hasAlreadySetTask = !empty($signs[$targetKey]['_task_set']);
                $currentPrio = $statusPriority[$existingStatus] ?? 0;
                $newPrio = $statusPriority[$taskStatus] ?? 0;

                // Jangan timpa slot yang sudah diisi task APPROVED/CLAIMED dengan task CANCELLED/PENDING
                if ($hasAlreadySetTask && $newPrio < $currentPrio) {
                    continue;
                }

                $signs[$targetKey]['_task_set'] = true;
                if (!empty($actor)) {
                    $signs[$targetKey]['name'] = $actor;
                }
                $signs[$targetKey]['claimed_by_name'] = $task->Claimed_By_Name ?? null;
                $signs[$targetKey]['acted_by_name'] = $task->Acted_By_Name ?? null;

                $isApproved = in_array($taskStatus, ['APPROVED', 'AUTO_APPROVED'], true);
                $signs[$targetKey]['status'] = $taskStatus;
                $signs[$targetKey]['date'] = $task->Acted_At;
                if ($isApproved) {
                    $approverDisplay = $signs[$targetKey]['name'] ?: 'System Auto-Approved';
                    $dateStr = $task->Acted_At ? date('d/m/Y H:i', strtotime($task->Acted_At)) : date('d/m/Y');
                    $signs[$targetKey]['qr_data'] = "DIGITALLY APPROVED\nBy: {$approverDisplay}\nRole: {$signs[$targetKey]['position']}\nDate: {$dateStr}";
                }
            }
        }

        return $signs;
    }

    /**
     * Cari nama kepala departemen (Level 3) dari tabel employment / users.
     */
    protected function resolveDeptHeadName(string $department): string
    {
        if ($department === '') {
            return '';
        }

        // Cek dari employment dengan posisi Level 3 pada departemen tersebut
        $posNames = DB::table('master_position')
            ->where('Department', $department)
            ->where('Level', 3)
            ->where('Status', 'ACTIVE')
            ->pluck('Position')
            ->all();

        $posInitials = DB::table('master_position')
            ->where('Department', $department)
            ->where('Level', 3)
            ->where('Status', 'ACTIVE')
            ->pluck('Initial')
            ->all();

        $allPos = array_values(array_filter(array_unique(array_merge($posNames, $posInitials))));

        if (!empty($allPos)) {
            $emp = DB::table('employment')
                ->where('Status', 'ACTIVE')
                ->whereIn('Position', $allPos)
                ->orderBy('ID', 'ASC')
                ->first();
            if ($emp && !empty($emp->Name)) {
                return trim((string) $emp->Name);
            }
        }

        // Fallback: cek dari master_hr_approval Level 3 untuk departemen tersebut
        $hrAppr = DB::table('master_hr_approval')
            ->where('Department', $department)
            ->where('Level', 3)
            ->where('Status', 'ACTIVE')
            ->orderBy('ID', 'ASC')
            ->first();

        if ($hrAppr && !empty($hrAppr->Name)) {
            return trim((string) $hrAppr->Name);
        }

        return '';
    }

    /**
     * Cari nama pemegang jabatan berdasarkan kode posisi (Initial) atau daftar Level.
     */
    protected function resolvePositionHolderName(string $posCode = null, string $department = null, array $levels = []): string
    {
        $posQuery = DB::table('master_position')->where('Status', 'ACTIVE');
        if ($posCode) {
            $posQuery->where(function ($q) use ($posCode) {
                $q->where('Initial', $posCode)->orWhere('Initial_Code', $posCode);
            });
        }
        if ($department) {
            $posQuery->where('Department', $department);
        }
        if (!empty($levels)) {
            $posQuery->whereIn('Level', $levels);
        }

        $positions = $posQuery->get(['Position', 'Initial']);
        $matches = [];
        foreach ($positions as $p) {
            if (!empty($p->Position)) $matches[] = $p->Position;
            if (!empty($p->Initial)) $matches[] = $p->Initial;
        }

        if (!empty($matches)) {
            $emp = DB::table('employment')
                ->where('Status', 'ACTIVE')
                ->whereIn('Position', array_unique($matches))
                ->orderBy('ID', 'ASC')
                ->first();
            if ($emp && !empty($emp->Name)) {
                return trim((string) $emp->Name);
            }
        }

        return '';
    }

    /**
     * Cek apakah user saat ini dapat melakukan tindakan approve/reject pada task aktif.
     */
    public function pendingTaskForUser(int $statusChangeId, int $userId)
    {
        $sc = DB::table('status_changes')->where('id', $statusChangeId)->first();
        if (!$sc || empty($sc->approval_request_id)) {
            return null;
        }

        $request = $this->workflow->getRequest((int) $sc->approval_request_id);
        if (!$request || !in_array(strtoupper((string) $request->Status), ['SUBMITTED', 'IN_APPROVAL', 'PENDING'], true)) {
            return null;
        }

        $task = DB::table('approval_request_task as t')
            ->join('approval_task_candidate as c', function ($join) use ($userId) {
                $join->on('c.Task_ID', '=', 't.ID')
                    ->where('c.User_Id', '=', $userId)
                    ->where('c.Status', '=', 'ACTIVE');
            })
            ->where('t.Request_ID', (int) $request->ID)
            ->where('t.Stage_Order', (int) $request->Current_Stage)
            ->whereIn('t.Status', ['PENDING', 'CLAIMED'])
            ->where(function ($sub) use ($userId) {
                $sub->where('t.Status', 'PENDING')
                    ->orWhere(function ($claimed) use ($userId) {
                        $claimed->where('t.Status', 'CLAIMED')
                            ->where('t.Claimed_By', $userId);
                    });
            })
            ->select('t.*')
            ->orderBy('t.ID', 'ASC')
            ->first();

        if ($task) {
            $snapshot = !empty($task->Snapshot) ? json_decode($task->Snapshot, true) : [];
            $task->Label = $snapshot['Step']['Label'] ?? ($task->Position_Name ?? ('Level ' . $task->Level_Number));
        }

        return $task;
    }

    /**
     * Cek apakah ada task di stage aktif yang sebenarnya user merupakan kandidatnya,
     * tetapi sudah di-claim oleh user lain (untuk ditampilkan informasi lock di UI).
     */
    public function claimedByOtherForUser(int $statusChangeId, int $userId)
    {
        $sc = DB::table('status_changes')->where('id', $statusChangeId)->first();
        if (!$sc || empty($sc->approval_request_id)) {
            return null;
        }

        $request = $this->workflow->getRequest((int) $sc->approval_request_id);
        if (!$request || !in_array(strtoupper((string) $request->Status), ['SUBMITTED', 'IN_APPROVAL', 'PENDING'], true)) {
            return null;
        }

        $task = DB::table('approval_request_task as t')
            ->join('approval_task_candidate as c', function ($join) use ($userId) {
                $join->on('c.Task_ID', '=', 't.ID')
                    ->where('c.User_Id', '=', $userId)
                    ->where('c.Status', '=', 'ACTIVE');
            })
            ->leftJoin('users as u', 'u.id', '=', 't.Claimed_By')
            ->where('t.Request_ID', (int) $request->ID)
            ->where('t.Stage_Order', (int) $request->Current_Stage)
            ->where('t.Status', 'CLAIMED')
            ->whereNotNull('t.Claimed_By')
            ->where('t.Claimed_By', '!=', $userId)
            ->select('t.*', 'u.Name as Claimed_By_Name', 'u.username as Claimed_By_Username')
            ->orderBy('t.ID', 'ASC')
            ->first();

        if ($task) {
            $snapshot = !empty($task->Snapshot) ? json_decode($task->Snapshot, true) : [];
            $task->Label = $snapshot['Step']['Label'] ?? ($task->Position_Name ?? ('Level ' . $task->Level_Number));
        }

        return $task;
    }

    /**
     * Konversi status approval engine ke status lokal status_changes.
     */
    public function mapApprovalStatus(string $approvalStatus): string
    {
        $status = strtoupper(trim($approvalStatus));
        switch ($status) {
            case 'APPROVED':
                return self::STATUS_APPROVED;
            case 'REJECTED':
                return self::STATUS_REJECTED;
            case 'CANCELLED':
                return self::STATUS_CANCELLED;
            case 'IN_APPROVAL':
            case 'PENDING':
                return self::STATUS_IN_APPROVAL;
            case 'SUBMITTED':
                return self::STATUS_SUBMITTED;
            default:
                return self::STATUS_IN_APPROVAL;
        }
    }

    /**
     * Susun konteks untuk pencocokan approval policy.
     */
    protected function buildContext($sc, $user): array
    {
        $dept = !empty($sc->current_department) ? $sc->current_department : ($user->Department ?? null);
        $location = !empty($sc->current_work_location) ? $sc->current_work_location : ($user->Location ?? null);

        return [
            'Request_Type'        => self::REQUEST_TYPE,
            'Location'            => $location,
            'Company_ID'          => (int) ($user->Company_ID ?? 1),
            'Department'          => $dept,
            'Destination_Dept'    => $sc->proposed_department ?? null,
            'Jenis'               => $sc->jenis ?? 'MUTATION',
            'Amount'              => (float) ($sc->proposed_salary ?? 0),
        ];
    }
}
