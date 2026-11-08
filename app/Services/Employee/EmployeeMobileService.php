<?php

namespace App\Services\Employee;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EmployeeMobileService
{
    /**
     * Dapatkan data employment milik user login.
     * Mencocokkan via users.Emp_ID = employment.NIK atau employment.Task_ID
     */
    public function getMyEmployeeData($user = null)
    {
        $user = $user ?: Auth::user();
        if (!$user) {
            return null;
        }

        $emp = null;
        if (!empty($user->Emp_ID)) {
            $emp = DB::table('employment')
                ->where('NIK', $user->Emp_ID)
                ->orWhere('Task_ID', $user->Emp_ID)
                ->first();
        }

        if (!$emp && !empty($user->username)) {
            $emp = DB::table('employment')
                ->where('Created_By', $user->username)
                ->orWhere('Email', $user->email)
                ->first();
        }

        return $emp;
    }

    /**
     * Ambil data profil karyawan berdasarkan Task_ID
     */
    public function getEmployeeByTaskId($taskId)
    {
        return DB::table('employment')->where('Task_ID', $taskId)->first();
    }

    /**
     * Cek apakah user memiliki otoritas HCGA / Admin untuk melihat seluruh karyawan
     */
    public function isHcgaOrAdmin($user = null)
    {
        $user = $user ?: Auth::user();
        if (!$user) {
            return false;
        }

        $dept = strtoupper($user->Department ?? '');
        $priv = strtoupper($user->Priv_ID ?? '');
        $username = strtolower($user->username ?? '');

        if (in_array($username, ['admin', 'hcga', 'manager.hcga', 'hrd']) ||
            strpos($dept, 'HCGA') !== false ||
            strpos($dept, 'HRD') !== false ||
            strpos($priv, 'ADMIN') !== false ||
            $user->MainPriv == '1') {
            return true;
        }

        // Cek via helper can_menu jika tersedia
        if (function_exists('can_menu')) {
            if (can_menu('SI01001', 'view') || can_menu('SI01009', 'view')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Directory list untuk HCGA (dengan pagination, filter & search)
     */
    public function getEmployeeDirectory($params = [], $perPage = 15)
    {
        $query = DB::table('employment')
            ->select(
                'ID',
                'Task_ID',
                'NIK',
                'Name',
                'Department',
                'Position',
                'Employment_Status',
                'Status',
                'Location',
                'Handphone',
                'Email',
                'Foto'
            );

        if (!empty($params['search'])) {
            $s = trim($params['search']);
            $query->where(function ($q) use ($s) {
                $q->where('Name', 'LIKE', "%{$s}%")
                  ->orWhere('NIK', 'LIKE', "%{$s}%")
                  ->orWhere('Position', 'LIKE', "%{$s}%")
                  ->orWhere('Department', 'LIKE', "%{$s}%");
            });
        }

        if (!empty($params['department'])) {
            $query->where('Department', $params['department']);
        }

        if (!empty($params['status'])) {
            $query->where('Status', $params['status']);
        }

        if (!empty($params['location'])) {
            $query->where('Location', $params['location']);
        }

        return $query->orderBy('Name', 'ASC')->paginate($perPage);
    }

    /**
     * Ambil data rincian tab (untuk lazy loading)
     */
    public function getTabData($taskId, $tabKey, $isHcga = false)
    {
        $data = [];
        switch ($tabKey) {
            case 'profile':
                $emp = DB::table('employment')->where('Task_ID', $taskId)->first();
                $detail = DB::table('employment_detail')->where('Task_ID', $taskId)->first();
                $data = [
                    'emp' => $emp,
                    'detail' => $detail,
                ];
                break;

            case 'family':
                $family = DB::table('employment_family')->where('Task_ID', $taskId)->get();
                $emergency = DB::table('employment_contact_point')->where('Task_ID', $taskId)->get();
                $data = [
                    'family' => $family,
                    'emergency' => $emergency,
                ];
                break;

            case 'education':
                $education = DB::table('employment_akademic')->where('Task_ID', $taskId)->get();
                $experience = DB::table('employment_experiences')->where('Task_ID', $taskId)->get();
                $skills = DB::table('employment_other_skill')->where('Task_ID', $taskId)->get();
                $data = [
                    'education' => $education,
                    'experience' => $experience,
                    'skills' => $skills,
                ];
                break;

            case 'benefit':
                $emp = DB::table('employment')->where('Task_ID', $taskId)->first();
                $insurances = DB::table('employment_insurances')->where('Task_ID', $taskId)->get();
                $leave = DB::table('employment_leave')->where('Task_ID', $taskId)->get();
                $data = [
                    'emp' => $emp,
                    'insurances' => $insurances,
                    'leave' => $leave,
                ];
                break;

            case 'document':
                $documents = DB::table('employment_document')->where('Task_ID', $taskId)->get();
                $history = DB::table('employment_history')->where('Task_ID', $taskId)->get();
                
                // ATURAN PENTING: User diri sendiri TIDAK BISA melihat recruitment
                $recruitment = null;
                if ($isHcga) {
                    $recruitment = DB::table('opportunity_detail')
                        ->where('Task_ID', function ($q) use ($taskId) {
                            $q->select('Registration_Status')->from('employment')->where('Task_ID', $taskId)->limit(1);
                        })->first();
                }

                $data = [
                    'documents' => $documents,
                    'history' => $history,
                    'recruitment' => $recruitment,
                    'is_hcga' => $isHcga,
                ];
                break;

            case 'requests':
                $requests = DB::table('employee_data_update_requests')
                    ->where('task_id', $taskId)
                    ->orderBy('id', 'DESC')
                    ->get();
                $data = ['requests' => $requests];
                break;
        }

        return $data;
    }

    /**
     * Buat pengajuan perubahan data diri
     */
    public function submitUpdateRequest($taskId, $data, $user)
    {
        $emp = DB::table('employment')->where('Task_ID', $taskId)->first();
        if (!$emp) {
            throw new \Exception('Data karyawan tidak ditemukan.');
        }

        $today = date('Ymd');
        $countToday = DB::table('employee_data_update_requests')
            ->whereDate('created_at', Carbon::today())
            ->count() + 1;
        $reqNo = 'REQ-UPD/' . $today . '/' . str_pad($countToday, 4, '0', STR_PAD_LEFT);

        $insertData = [
            'request_no'     => $reqNo,
            'task_id'        => $emp->Task_ID,
            'nik'            => $emp->NIK,
            'user_id'        => $user->username,
            'field_category' => $data['field_category'],
            'field_name'     => $data['field_name'],
            'old_value'      => $data['old_value'] ?? null,
            'new_value'      => $data['new_value'],
            'reason'         => $data['reason'] ?? null,
            'attachment'     => $data['attachment'] ?? null,
            'status'         => 'WAITING_APPROVAL',
            'created_at'     => Carbon::now(),
            'updated_at'     => Carbon::now(),
        ];

        $id = DB::table('employee_data_update_requests')->insertGetId($insertData);
        return DB::table('employee_data_update_requests')->where('id', $id)->first();
    }
}

