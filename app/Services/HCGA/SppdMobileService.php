<?php

namespace App\Services\HCGA;

use App\Services\Approval\SppdApprovalService;
use App\Services\GeneratorService;
use App\Services\OSRMService;
use App\Http\Controllers\HCGA\SppdController;
use App\Http\Controllers\HCGA\CarRentController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SppdMobileService
{
    /**
     * @var SppdApprovalService
     */
    protected $approvalService;

    public function __construct(SppdApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    /**
     * Dapatkan daftar pengajuan SPPD user (dengan filter & pagination ringkas)
     */
    public function getUserSppdList($userId, $status = null, $limit = 20)
    {
        $query = DB::table('sppd')
            ->where('Requester_ID', (int) $userId)
            ->orderBy('ID', 'DESC');

        if (!empty($status) && $status !== 'ALL') {
            $query->where('Status', $status);
        }

        $list = $query->limit($limit)->get();

        return $list->map(function ($row) {
            return [
                'id'              => (int) $row->ID,
                'sppd_no'         => $row->SPPD_No,
                'type'            => $row->Sppd_Type ?? 'NORMAL',
                'destination'     => $row->Destination,
                'route'           => $row->Route_Start . ' - ' . ($row->Route_Finish ?: $row->Destination),
                'program_name'    => $row->Program_Name,
                'schedule_date'   => $row->Schedule_Date,
                'date_start'      => $row->Date_Start,
                'date_finish'     => $row->Date_Finish,
                'estimated_cost'  => (float) $row->Estimated_Cost,
                'estimated_cost_formatted' => 'Rp ' . number_format((float) $row->Estimated_Cost, 0, ',', '.'),
                'status'          => $row->Status,
                'approver_name'   => $row->Approver_Name,
                'need_car'        => (int) ($row->Need_Car ?? 0),
                'approval_request_id' => $row->Approval_Request_ID,
                'created_at'      => $row->Created_Date,
            ];
        });
    }

    /**
     * Dapatkan daftar antrean approval SPPD untuk user tertentu
     */
    public function getApproverQueue($user, $limit = 20)
    {
        $userId = (int) $user->id;

        // Cek tugas PENDING / CLAIMED dari approval_request_task untuk user ini
        $taskRequestIds = DB::table('approval_request_task as t')
            ->join('approval_request as r', 'r.ID', '=', 't.Request_ID')
            ->where('r.Request_Type', 'SPPD')
            ->whereIn('t.Status', ['PENDING', 'CLAIMED'])
            ->where(function ($q) use ($userId) {
                $q->where(function ($claimed) use ($userId) {
                    $claimed->where('t.Status', 'CLAIMED')
                            ->where('t.Claimed_By', $userId);
                })
                ->orWhereExists(function ($sub) use ($userId) {
                    $sub->select(DB::raw(1))
                        ->from('approval_task_candidate as c')
                        ->whereRaw('c.Task_ID = t.ID')
                        ->where('c.User_Id', $userId)
                        ->where('c.Status', 'ACTIVE');
                });
            })
            ->pluck('r.Map_To')
            ->filter()
            ->unique()
            ->all();

        // Tambahkan juga jika ada penugasan langsung di kolom Approver_ID tabel sppd (backward compat)
        $directIds = DB::table('sppd')
            ->where('Approver_ID', $userId)
            ->whereIn('Status', ['SUBMITTED', 'APPROVAL_SUPERVISOR', 'APPROVAL_MANAGER', 'APPROVAL_VP', 'APPROVAL_CEO'])
            ->pluck('ID')
            ->all();

        // Administrator / HCGA verifier visibility: dapat melihat semua antrean pending
        $isAdmin = false;
        if (method_exists($user, 'hasRole') && $user->hasRole('ADM')) {
            $isAdmin = true;
        } elseif (($user->Position ?? '') === 'Administrator') {
            $isAdmin = true;
        }

        if ($isAdmin) {
            $adminIds = DB::table('sppd')
                ->whereIn('Status', ['SUBMITTED', 'APPROVAL_SUPERVISOR', 'APPROVAL_MANAGER', 'APPROVAL_VP', 'APPROVAL_CEO'])
                ->pluck('ID')
                ->all();
            $directIds = array_merge($directIds, $adminIds);
        }

        $mergedIds = array_values(array_unique(array_merge($taskRequestIds, $directIds)));

        if (empty($mergedIds)) {
            return collect([]);
        }

        $sppds = DB::table('sppd')
            ->whereIn('ID', $mergedIds)
            ->orderBy('ID', 'DESC')
            ->limit($limit)
            ->get();

        return $sppds->map(function ($row) {
            return [
                'id'              => (int) $row->ID,
                'sppd_no'         => $row->SPPD_No,
                'requester_name'  => $row->Requester_Name,
                'department'      => $row->Department,
                'type'            => $row->Sppd_Type ?? 'NORMAL',
                'destination'     => $row->Destination,
                'route'           => $row->Route_Start . ' - ' . ($row->Route_Finish ?: $row->Destination),
                'program_name'    => $row->Program_Name,
                'schedule_date'   => $row->Schedule_Date,
                'estimated_cost'  => (float) $row->Estimated_Cost,
                'estimated_cost_formatted' => 'Rp ' . number_format((float) $row->Estimated_Cost, 0, ',', '.'),
                'status'          => $row->Status,
                'created_at'      => $row->Created_Date,
            ];
        });
    }

    /**
     * Detail lengkap SPPD untuk mobile view / response
     */
    public function getDetail($sppdId, $user)
    {
        $sppd = DB::table('sppd')->where('ID', (int) $sppdId)->first();
        if (!$sppd) {
            return null;
        }

        $costs = DB::table('sppd_cost')->where('SPPD_ID', $sppd->ID)->get();
        $logs  = DB::table('sppd_log')->where('SPPD_ID', $sppd->ID)->orderBy('ID', 'DESC')->get();

        $carRents = DB::table('car_rent')
            ->where('SPPD_ID', $sppd->ID)
            ->orWhere('Car_Rent_No', $sppd->Car_Rent_No)
            ->get();

        // Resolusi titik asal (Route_Start) dari master location
        $originCoords = null;
        $originName = $sppd->Route_Start ?: 'Titik Asal';
        if (!empty($sppd->Route_Start)) {
            $locStart = DB::table('location')
                ->where('Location_ID', $sppd->Route_Start)
                ->orWhere('Name', $sppd->Route_Start)
                ->first();
            if ($locStart && is_numeric($locStart->Latitude) && is_numeric($locStart->Longitude) && (float)$locStart->Latitude != 0) {
                $originCoords = [
                    'lat'  => (float) $locStart->Latitude,
                    'lng'  => (float) $locStart->Longitude,
                    'name' => $locStart->Name ?: $sppd->Route_Start,
                    'code' => $locStart->Location_ID,
                ];
                $originName = $locStart->Name ?: $sppd->Route_Start;
            }
        }

        // Resolusi titik tujuan (Destination / Route_Finish)
        $destCoords = null;
        $destName = $sppd->Destination ?: ($sppd->Route_Finish ?: 'Titik Tujuan');
        if (!empty($sppd->Dest_Lat) && !empty($sppd->Dest_Lng) && is_numeric($sppd->Dest_Lat) && is_numeric($sppd->Dest_Lng) && (float)$sppd->Dest_Lat != 0) {
            $destCoords = [
                'lat'  => (float) $sppd->Dest_Lat,
                'lng'  => (float) $sppd->Dest_Lng,
                'name' => $destName,
                'code' => $sppd->Route_Finish ?: 'DEST',
            ];
        } elseif (!empty($sppd->Route_Finish)) {
            $locFinish = DB::table('location')
                ->where('Location_ID', $sppd->Route_Finish)
                ->orWhere('Name', $sppd->Route_Finish)
                ->first();
            if ($locFinish && is_numeric($locFinish->Latitude) && is_numeric($locFinish->Longitude) && (float)$locFinish->Latitude != 0) {
                $destCoords = [
                    'lat'  => (float) $locFinish->Latitude,
                    'lng'  => (float) $locFinish->Longitude,
                    'name' => $locFinish->Name ?: $sppd->Route_Finish,
                    'code' => $locFinish->Location_ID,
                ];
                $destName = $locFinish->Name ?: $sppd->Route_Finish;
            }
        }

        // Cek apakah user saat ini berhak melakukan approval
        $canApprove = false;
        $userId = (int) $user->id;

        if (in_array($sppd->Status, ['SUBMITTED', 'APPROVAL_SUPERVISOR', 'APPROVAL_MANAGER', 'APPROVAL_VP', 'APPROVAL_CEO'])) {
            if ((int) $sppd->Approver_ID === $userId) {
                $canApprove = true;
            } elseif (!empty($sppd->Approval_Request_ID)) {
                $pendingTask = DB::table('approval_request_task as t')
                    ->where('t.Request_ID', (int) $sppd->Approval_Request_ID)
                    ->whereIn('t.Status', ['PENDING', 'CLAIMED'])
                    ->where(function ($q) use ($userId) {
                        $q->where(function ($claimed) use ($userId) {
                            $claimed->where('t.Status', 'CLAIMED')
                                    ->where('t.Claimed_By', $userId);
                        })
                        ->orWhereExists(function ($sub) use ($userId) {
                            $sub->select(DB::raw(1))
                                ->from('approval_task_candidate as c')
                                ->whereRaw('c.Task_ID = t.ID')
                                ->where('c.User_Id', $userId)
                                ->where('c.Status', 'ACTIVE');
                        });
                    })
                    ->first();
                if ($pendingTask) {
                    $canApprove = true;
                }
            }

            // Administrator override
            if (!$canApprove) {
                if ((method_exists($user, 'hasRole') && $user->hasRole('ADM')) || (($user->Position ?? '') === 'Administrator')) {
                    $canApprove = true;
                }
            }
        }

        return [
            'sppd'          => $sppd,
            'costs'         => $costs,
            'logs'          => $logs,
            'car_rents'     => $carRents,
            'carRents'      => $carRents,
            'origin_coords' => $originCoords,
            'dest_coords'   => $destCoords,
            'origin_name'   => $originName,
            'dest_name'     => $destName,
            'can_approve'   => $canApprove,
            'canApprove'    => $canApprove,
        ];
    }

    /**
     * Proses pembuatan request SPPD baru dari Mobile / API
     */
    public function submitNewSppd(array $data, $user)
    {
        $sppdType = strtoupper(trim((string) ($data['Sppd_Type'] ?? 'NORMAL')));
        if (!in_array($sppdType, ['NORMAL', 'PERBANTUAN', 'RELOKASI'])) {
            $sppdType = 'NORMAL';
        }

        $scheduleDate = $data['Schedule_Date'] ?? date('Y-m-d');
        $dateStart    = $data['Date_Start'] ?: $scheduleDate;
        $dateFinish   = $data['Date_Finish'] ?: $scheduleDate;

        $dtStart = Carbon::parse($dateStart);
        $dtFinish = Carbon::parse($dateFinish);
        $spanDays = $dtStart->diffInDays($dtFinish) + 1;

        if ($sppdType === 'NORMAL' && $spanDays > 14) {
            throw new \InvalidArgumentException('SPPD Regular maksimal berdurasi 14 hari');
        }
        if ($sppdType === 'PERBANTUAN' && $spanDays <= 14) {
            throw new \InvalidArgumentException('Perbantuan Sementara harus berdurasi lebih dari 14 hari');
        }

        $routeStart   = trim($data['Route_Start'] ?? '');
        $routeFinish  = trim($data['Route_Finish'] ?? '');
        $destLat      = trim($data['Dest_Lat'] ?? '');
        $destLng      = trim($data['Dest_Lng'] ?? '');
        $destination  = trim($data['Destination'] ?? '');

        if ($routeStart === '') {
            throw new \InvalidArgumentException('Lokasi Asal (Route_Start) wajib dipilih');
        }

        if ($destination === '' && $routeFinish !== '') {
            $loc = DB::table('location')->where('Location_ID', $routeFinish)->first();
            $destination = $loc ? $loc->Name : $routeFinish;
        }

        if ($destination === '') {
            throw new \InvalidArgumentException('Tujuan Perjalanan Dinas wajib diisi');
        }

        // Kalkulasi rute
        list($distanceKm, $durationMin, $scope) = SppdController::computeRouteScope(
            $sppdType,
            $routeStart,
            $routeFinish,
            $destLat,
            $destLng,
            trim($data['Departure_Time'] ?? ''),
            trim($data['Return_Time'] ?? '')
        );

        // Biaya: dukung array costs maupun format Cost_0, Cost_1 dari form desktop
        $costs = [];
        $totalCost = 0;
        if (!empty($data['costs']) && is_array($data['costs'])) {
            foreach ($data['costs'] as $c) {
                $nom = (float) str_replace([',', '.'], ['', '.'], $c['Cost'] ?? 0);
                if ($nom > 0) {
                    $costs[] = [
                        'Cost'        => $nom,
                        'Description' => trim($c['Description'] ?? 'Biaya Operasional'),
                    ];
                    $totalCost += $nom;
                }
            }
        }

        // Cek juga format Cost_0, Cost_1 jika dikirim langsung
        foreach ($data as $key => $val) {
            if (preg_match('/^Cost_(\d+)$/', $key, $m)) {
                $desc = trim($data['Desc_' . $m[1]] ?? '');
                if (trim($val) === '') continue;
                $nom = (float) str_replace([',', '.'], ['', '.'], trim($val));
                if ($nom > 0) {
                    $costs[] = ['Cost' => $nom, 'Description' => $desc ?: 'Biaya Operasional'];
                    $totalCost += $nom;
                }
            }
        }

        if (empty($costs)) {
            $est = (float) ($data['Estimated_Cost'] ?? 0);
            if ($est > 0) {
                $costs[] = ['Cost' => $est, 'Description' => 'Estimasi Biaya'];
                $totalCost = $est;
            } else {
                throw new \InvalidArgumentException('Rincian biaya wajib diisi minimal 1 item');
            }
        }

        $sppdNo = SppdController::nextSppdNumber($sppdType);
        $nama   = $user->Name ?: $user->username;
        $nik    = $user->Emp_ID ?: '';
        $dept   = trim($data['Department'] ?? ($user->Department ?? ''));

        $needCar = !empty($data['Need_Car']) ? 1 : 0;

        DB::beginTransaction();
        try {
            $sppdId = DB::table('sppd')->insertGetId([
                'SPPD_No'        => $sppdNo,
                'Requester_ID'   => (int) $user->id,
                'Requester_Name' => $nama,
                'Requester_NIK'  => $nik,
                'Department'     => $dept,
                'Destination'    => $destination,
                'Route_Start'    => $routeStart,
                'Route_Finish'   => $routeFinish ?: $destination,
                'Description'    => trim($data['Description'] ?? ''),
                'Schedule_Date'  => $scheduleDate,
                'Schedule_Time'  => trim($data['Departure_Time'] ?? '') . ' - ' . trim($data['Return_Time'] ?? ''),
                'Departure_Time' => trim($data['Departure_Time'] ?? ''),
                'Return_Time'    => trim($data['Return_Time'] ?? ''),
                'Dest_Lat'       => $destLat ?: null,
                'Dest_Lng'       => $destLng ?: null,
                'Distance_Km'    => $distanceKm,
                'Duration_Min'   => $durationMin,
                'Entitlement_Scope' => $scope,
                'Program_Name'   => trim($data['Program_Name'] ?? 'Perjalanan Dinas'),
                'Estimated_Cost' => $totalCost,
                'Sppd_Type'      => $sppdType,
                'Date_Start'     => $dateStart,
                'Date_Finish'    => $dateFinish,
                'Origin_Site'    => $data['Origin_Site'] ?? null,
                'Target_Site'    => $data['Target_Site'] ?? null,
                'Reason'         => $data['Reason'] ?? null,
                'Status'         => 'SUBMITTED',
                'Created_By'     => $user->username,
                'Created_Date'   => now(),
                'Need_Car'       => $needCar,
                'Car_Jenis_Mobil'=> $data['Car_Jenis_Mobil'] ?? null,
                'Car_Tgl_Pinjam' => $data['Car_Tgl_Pinjam'] ?? null,
                'Car_Tgl_Kembali'=> $data['Car_Tgl_Kembali'] ?? null,
                'Car_Jam_Pinjam' => $data['Car_Jam_Pinjam'] ?? null,
                'Car_Jam_Kembali'=> $data['Car_Jam_Kembali'] ?? null,
            ]);

            foreach ($costs as $c) {
                DB::table('sppd_cost')->insert([
                    'SPPD_ID'     => $sppdId,
                    'Cost'        => $c['Cost'],
                    'Description' => $c['Description'],
                ]);
            }

            DB::table('sppd_log')->insert([
                'SPPD_ID'    => $sppdId,
                'Action'     => 'SUBMITTED',
                'Actor_ID'   => $user->id,
                'Actor_Name' => $nama,
                'Note'       => 'Request ' . $sppdType . ' diajukan via Mobile App',
                'Created_At' => now(),
            ]);

            // Hubungkan ke ApprovalWorkflowService
            $sppdRow = DB::table('sppd')->where('ID', $sppdId)->first();
            try {
                $this->approvalService->submit($sppdRow, $user);
            } catch (\Exception $apprEx) {
                Log::warning("Mobile SPPD Submit - ApprovalWorkflow warning: " . $apprEx->getMessage());
            }

            DB::commit();

            return DB::table('sppd')->where('ID', $sppdId)->first();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Proses Aksi Approval SPPD (Approve / Reject) dari Mobile / API
     */
    public function processApproval($sppdId, $actionType, $note, $user)
    {
        $sppd = DB::table('sppd')->where('ID', (int) $sppdId)->first();
        if (!$sppd) {
            throw new \InvalidArgumentException('Data SPPD tidak ditemukan');
        }

        $userId = (int) $user->id;
        $isApprove = ((string) $actionType === '1' || strtoupper((string) $actionType) === 'APPROVE');
        $nama = $user->Name ?: $user->username;
        $timestamp = date('Y-m-d H:i:s');
        $existingNote = $sppd->Approval_Note ? $sppd->Approval_Note . "\n" : '';

        DB::beginTransaction();
        try {
            if (!$isApprove) {
                // REJECT
                if (!empty($sppd->Approval_Request_ID)) {
                    try {
                        $workflow = app(\App\Services\Approval\ApprovalWorkflowService::class);
                        $currentTask = DB::table('approval_request_task')
                            ->where('Request_ID', (int) $sppd->Approval_Request_ID)
                            ->where('Status', 'PENDING')
                            ->orderBy('Stage_Order', 'ASC')
                            ->first();
                        if ($currentTask) {
                            $workflow->reject((int) $currentTask->ID, $note ?: 'Ditolak via Mobile', $userId);
                        }
                    } catch (\Exception $ex) {
                        Log::warning('ApprovalWorkflow reject failed: ' . $ex->getMessage());
                    }
                }

                $newNote = $existingNote . "$timestamp REJECTED by $user->username :: $note";
                DB::table('sppd')->where('ID', $sppd->ID)->update([
                    'Status'        => 'REJECTED',
                    'Approval_Note' => $newNote,
                    'Updated_By'    => $user->username,
                    'Updated_Date'  => now(),
                ]);

                DB::table('sppd_log')->insert([
                    'SPPD_ID'    => $sppd->ID,
                    'Action'     => 'REJECTED',
                    'Actor_ID'   => $userId,
                    'Actor_Name' => $nama,
                    'Note'       => $note ?: 'Request ditolak via Mobile',
                    'Created_At' => now(),
                ]);

                DB::commit();
                return ['status' => 'REJECTED', 'message' => 'Request SPPD berhasil ditolak'];
            }

            // APPROVE
            if (!empty($sppd->Approval_Request_ID)) {
                try {
                    $workflow = app(\App\Services\Approval\ApprovalWorkflowService::class);
                    $currentTask = DB::table('approval_request_task')
                        ->where('Request_ID', (int) $sppd->Approval_Request_ID)
                        ->where('Status', 'PENDING')
                        ->orderBy('Stage_Order', 'ASC')
                        ->first();
                    if ($currentTask) {
                        $workflow->approve((int) $currentTask->ID, $note ?: 'Disetujui via Mobile', $userId);
                    }
                } catch (\Exception $ex) {
                    Log::warning('ApprovalWorkflow approve failed: ' . $ex->getMessage());
                }
            }

            $newStatus = 'ACCEPTED';
            $newNote = $existingNote . "$timestamp ACCEPTED by $user->username :: $note";

            DB::table('sppd')->where('ID', $sppd->ID)->update([
                'Status'        => $newStatus,
                'Approval_Note' => $newNote,
                'Approved_At'   => now(),
                'Updated_By'    => $user->username,
                'Updated_Date'  => now(),
            ]);

            DB::table('sppd_log')->insert([
                'SPPD_ID'    => $sppd->ID,
                'Action'     => 'APPROVED',
                'Actor_ID'   => $userId,
                'Actor_Name' => $nama,
                'Note'       => $note ?: 'Request disetujui via Mobile',
                'Created_At' => now(),
            ]);

            // Auto-create car rent jika butuh mobil
            $carNo = null;
            if ($sppd->Need_Car) {
                $sppdRow = DB::table('sppd')->where('ID', $sppd->ID)->first();
                list($carErr, $carNo, $carInfo) = CarRentController::createFromSppd($sppdRow, $user->username);
                if ($carErr) {
                    Log::warning('Mobile SPPD Approval auto car rent failed: ' . $carErr);
                }
            }

            DB::commit();

            return [
                'status'      => $newStatus,
                'message'     => 'Request SPPD berhasil disetujui' . ($carNo ? " (Booking mobil #$carNo)" : ''),
                'car_rent_no' => $carNo,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

