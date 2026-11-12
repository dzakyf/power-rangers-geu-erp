<?php

namespace App\Services\Approval;

use App\Exceptions\ApprovalWorkflowException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service Adapter untuk menghubungkan SPPD dengan ApprovalWorkflowService.
 */
class SppdApprovalService
{
    const REQUEST_TYPE = 'SPPD';

    /**
     * @var ApprovalWorkflowService
     */
    protected $workflow;

    /**
     * @var ApprovalPolicyResolver
     */
    protected $policyResolver;

    public function __construct(ApprovalWorkflowService $workflow, ApprovalPolicyResolver $policyResolver)
    {
        $this->workflow = $workflow;
        $this->policyResolver = $policyResolver;
    }

    /**
     * Daftarkan pengajuan SPPD baru ke ApprovalWorkflowService.
     *
     * @param object|array $sppd
     * @param object $user
     * @return object|null approval_request
     */
    public function submit($sppd, $user)
    {
        $this->ensurePoliciesExist();

        $sppdObj = is_array($sppd) ? (object) $sppd : $sppd;
        $sppdId = (int) $sppdObj->ID;

        $nik = !empty($sppdObj->Requester_NIK) ? $sppdObj->Requester_NIK : ($user->Emp_ID ?? null);
        $isOverPlafon = $this->isOverPlafon($sppdObj, $nik);

        $context = $this->buildContext($sppdObj, $user, $isOverPlafon);

        $description = sprintf(
            'SPPD [%s%s] %s: %s (%s s/d %s) - Estimasi Rp %s',
            $sppdObj->Sppd_Type ?? 'NORMAL',
            $isOverPlafon ? ' (DI ATAS PLAFON)' : '',
            $sppdObj->SPPD_No,
            $sppdObj->Destination,
            $sppdObj->Date_Start ?: $sppdObj->Schedule_Date,
            $sppdObj->Date_Finish ?: $sppdObj->Schedule_Date,
            number_format((float) ($sppdObj->Estimated_Cost ?? 0), 0, ',', '.')
        );

        $approvalReq = $this->workflow->submit([
            'Request_No'  => $sppdObj->SPPD_No ?: null,
            'Description' => $description,
        ], $context, (int) $user->id);

        if (!$approvalReq || empty($approvalReq->ID)) {
            throw new ApprovalWorkflowException('Gagal membuat approval_request untuk SPPD.', 'SPPD_APPROVAL_SUBMIT_FAILED', 500);
        }

        DB::table('approval_request')->where('ID', (int) $approvalReq->ID)->update([
            'Map_To' => $sppdId,
        ]);

        $updateData = ['Approval_Request_ID' => (int) $approvalReq->ID];
        if (\Illuminate\Support\Facades\Schema::hasColumn('sppd', 'Is_Over_Plafon')) {
            $updateData['Is_Over_Plafon'] = $isOverPlafon ? 1 : 0;
        }

        DB::table('sppd')->where('ID', $sppdId)->update($updateData);

        $this->syncApprovalStatus($sppdId);

        return $this->workflow->getRequest((int) $approvalReq->ID);
    }

    /**
     * Bangun context policy approval SPPD.
     */
    public function buildContext($sppd, $user, $isOverPlafon = null): array
    {
        $nik = !empty($sppd->Requester_NIK) ? $sppd->Requester_NIK : ($user->Emp_ID ?? null);
        $emp = DB::table('employment')->where('NIK', $nik)->first();
        $pos = null;
        if ($emp && !empty($emp->Position)) {
            $pos = DB::table('master_position')->where('Initial', $emp->Position)->first();
        }

        $rawDept = trim((string) ($sppd->Department ?? ($user->Department ?? '')));
        $mDept = DB::table('master_department')
            ->where('Department', $rawDept)
            ->orWhere('Initial', $rawDept)
            ->first();
        $department = $mDept ? $mDept->Initial : ($emp->Department ?? $rawDept);

        $companyId  = $pos->Company_ID ?? ($user->Company_ID ?? 'GEU');
        $location   = $pos->Location ?? ($user->Location ?? 'GEU-HO');

        if ($isOverPlafon === null) {
            $isOverPlafon = $this->isOverPlafon($sppd, $nik);
        }

        return [
            'Request_Type'   => self::REQUEST_TYPE,
            'Location'       => $location,
            'Company_ID'     => $companyId,
            'Department'     => $department,
            'Amount'         => (float) ($sppd->Estimated_Cost ?? 0),
            'Sppd_Type'      => strtoupper(trim((string) ($sppd->Sppd_Type ?? 'NORMAL'))),
            'Is_Over_Plafon' => $isOverPlafon ? 1 : 0,
            'Over_Plafon'    => $isOverPlafon ? 'YES' : 'NO',
        ];
    }

    /**
     * Memeriksa apakah pengajuan SPPD memiliki rincian biaya yang melebihi batas plafon matriks regulasi.
     *
     * @param object $sppd
     * @param string|null $nik
     * @return bool
     */
    public function isOverPlafon($sppd, $nik = null): bool
    {
        $sppdType = strtoupper(trim((string) ($sppd->Sppd_Type ?? 'NORMAL')));
        // Berlaku khusus untuk SPPD jenis Reguler (NORMAL)
        if ($sppdType !== 'NORMAL') {
            return false;
        }

        $nik = $nik ?: (!empty($sppd->Requester_NIK) ? $sppd->Requester_NIK : null);
        $jobLevelKey = \App\Http\Controllers\HCGA\SppdController::getJobLevelForNik($nik);

        $allowance = DB::table('master_sppd_allowances')
            ->where('trip_type_key', 'tugas_kerja')
            ->where('job_level_key', $jobLevelKey)
            ->first();

        if (!$allowance) {
            return false;
        }

        $ticketMax = (float) ($allowance->ticket_max ?? 0);
        $hotelMaxPerNight = (float) ($allowance->reimburse_max_per_night ?? 0);

        // Hitung durasi hari dan malam
        $startStr = $sppd->Date_Start ?: $sppd->Schedule_Date;
        $finishStr = $sppd->Date_Finish ?: $sppd->Schedule_Date;
        $days = 1;
        if (!empty($startStr) && !empty($finishStr)) {
            $timeStart = strtotime($startStr);
            $timeFinish = strtotime($finishStr);
            if ($timeStart && $timeFinish && $timeFinish >= $timeStart) {
                $days = (int) round(($timeFinish - $timeStart) / 86400) + 1;
            }
        }
        $nights = max($days - 1, 0);
        $hotelMaxTotal = $hotelMaxPerNight * $nights;

        // Ambil rincian biaya dari tabel sppd_cost
        $costs = DB::table('sppd_cost')->where('SPPD_ID', $sppd->ID)->get();

        foreach ($costs as $c) {
            $nominal = (float) $c->Cost;
            $desc = strtolower(trim((string) $c->Description));

            // Cek tiket (tiket berangkat atau tiket pulang)
            if (strpos($desc, 'tiket') !== false) {
                if ($ticketMax > 0 && $nominal > $ticketMax) {
                    return true;
                }
            }

            // Cek penginapan / hotel
            if (strpos($desc, 'penginapan') !== false || strpos($desc, 'hotel') !== false) {
                if ($hotelMaxTotal > 0 && $nominal > $hotelMaxTotal) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Memastikan policy SPPD standar dan over-plafon terdaftar di tabel approval_policy.
     */
    public function ensurePoliciesExist(): void
    {
        try {
            $hasOverPolicy = DB::table('approval_policy')
                ->where('Policy_Code', 'SPPD-REGULAR-OVER-PLAFON-POLICY')
                ->exists();

            if (!$hasOverPolicy) {
                if (!class_exists(\ApprovalSppdPolicySeeder::class)) {
                    $seederPath = database_path('seeds/ApprovalSppdPolicySeeder.php');
                    if (file_exists($seederPath)) {
                        require_once $seederPath;
                    }
                }
                if (class_exists(\ApprovalSppdPolicySeeder::class)) {
                    $seeder = new \ApprovalSppdPolicySeeder();
                    $seeder->run();
                }
            }
        } catch (\Exception $e) {
            Log::warning('SppdApprovalService::ensurePoliciesExist exception: ' . $e->getMessage());
        }
    }

    /**
     * Sinkronkan status approval_request ke tabel sppd dan sppd_log.
     */
    public function syncApprovalStatus(int $sppdId, string $actor = 'system'): void
    {
        $sppd = DB::table('sppd')->where('ID', $sppdId)->first();
        if (!$sppd || empty($sppd->Approval_Request_ID)) {
            return;
        }

        $requestId = (int) $sppd->Approval_Request_ID;
        $apprReq = DB::table('approval_request')->where('ID', $requestId)->first();
        if (!$apprReq) {
            return;
        }

        $currentTask = DB::table('approval_request_task as t')
            ->where('t.Request_ID', $requestId)
            ->where('t.Status', 'PENDING')
            ->orderBy('t.Stage_Order', 'ASC')
            ->first();

        $approverId = null;
        $approverName = null;
        if ($currentTask) {
            if (!empty($currentTask->Claimed_By)) {
                $approverUser = DB::table('users')->where('id', (int) $currentTask->Claimed_By)->first();
                if ($approverUser) {
                    $approverId = (int) $approverUser->id;
                    $approverName = $approverUser->Name ?: $approverUser->username;
                }
            } else {
                $candidate = DB::table('approval_task_candidate as c')
                    ->join('users as u', 'u.id', '=', 'c.User_Id')
                    ->where('c.Task_ID', $currentTask->ID)
                    ->where('c.Status', 'ACTIVE')
                    ->select('u.id', 'u.Name', 'u.username')
                    ->first();
                if ($candidate) {
                    $approverId = (int) $candidate->id;
                    $approverName = $candidate->Name ?: $candidate->username;
                }
            }
            if (!$approverName && !empty($currentTask->Position_Name)) {
                $approverName = $currentTask->Position_Name;
            }
        }

        $newSppdStatus = $this->mapWorkflowStatusToSppd($apprReq->Status, $currentTask);

        $updateData = [
            'Status'        => $newSppdStatus,
            'Approver_ID'   => $approverId ?: $sppd->Approver_ID,
            'Approver_Name' => $approverName ?: $sppd->Approver_Name,
        ];

        if (in_array($apprReq->Status, ['APPROVED', 'COMPLETED'])) {
            $updateData['Status'] = 'ACCEPTED';
            $updateData['Approved_At'] = $apprReq->Completed_At ?: now();
        } elseif ($apprReq->Status === 'REJECTED') {
            $updateData['Status'] = 'REJECTED';
        }

        DB::table('sppd')->where('ID', $sppdId)->update($updateData);

        // Catat ke sppd_log jika status berubah
        if ($newSppdStatus !== $sppd->Status) {
            DB::table('sppd_log')->insert([
                'SPPD_ID'    => $sppdId,
                'Action'     => $newSppdStatus,
                'Actor_ID'   => auth()->id() ?: 0,
                'Actor_Name' => $actor,
                'Note'       => 'Status sinkron ke ' . $newSppdStatus . ' via Approval Engine',
                'Created_At' => now(),
            ]);
        }
    }

    /**
     * Petakan status internal ApprovalWorkflowService ke status modul SPPD.
     */
    protected function mapWorkflowStatusToSppd(string $workflowStatus, $currentTask = null): string
    {
        switch (strtoupper($workflowStatus)) {
            case 'APPROVED':
            case 'COMPLETED':
                return 'ACCEPTED';

            case 'REJECTED':
                return 'REJECTED';

            case 'IN_PROGRESS':
            case 'PENDING':
                if ($currentTask && isset($currentTask->Stage_Order)) {
                    if ((int) $currentTask->Stage_Order === 1) {
                        return 'SUBMITTED';
                    } elseif ((int) $currentTask->Stage_Order === 2) {
                        return 'APPROVAL_HCGA_MANAGER';
                    } elseif ((int) $currentTask->Stage_Order >= 3) {
                        return 'APPROVAL_VP';
                    }
                }
                return 'SUBMITTED';

            case 'CANCELLED':
                return 'REJECTED';

            default:
                return 'SUBMITTED';
        }
    }
}

