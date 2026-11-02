<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Exception;

class RecruitmentCandidateService
{
    /**
     * Resolve active Requisition (PTK) by Position name.
     * Cek apakah terdapat PTK APPROVED dengan Job_Position sama dan masih berlaku (atau End_Date null).
     *
     * @param string $position
     * @param string|null $location
     * @return object|null
     */
    public function resolveActiveRequisition(string $position, ?string $location = null)
    {
        if (trim($position) === '') {
            return null;
        }

        $query = DB::table('requisition')
            ->where('Status', 'APPROVED')
            ->where(function($q) use ($position) {
                $q->where('Job_Position', $position)
                  ->orWhere('Subject', 'like', "%{$position}%");
            })
            ->where(function($q) {
                $q->whereNull('End_Date')
                  ->orWhere('End_Date', '>=', date('Y-m-d'));
            });

        if ($location) {
            $query->orderByRaw("CASE WHEN Location = ? THEN 0 ELSE 1 END", [$location]);
        }

        return $query->orderBy('ID', 'DESC')->first();
    }

    /**
     * Save grid columns preference for a user.
     *
     * @param string $username
     * @param string $colsParam
     * @return array
     */
    public function saveColumnsPreference(string $username, string $colsParam): array
    {
        if ($username === '') {
            return [
                'status' => 'error',
                'message' => 'Sesi user tidak valid.',
            ];
        }

        $allCols = function_exists('recruitment_data_columns') ? recruitment_data_columns() : [];
        $keys = array_values(array_intersect(explode(',', $colsParam), array_keys($allCols)));

        if (!count($keys)) {
            return [
                'status' => 'error',
                'message' => 'Pilih minimal 1 kolom.',
            ];
        }

        try {
            if (Schema::hasTable('user_grid_prefs')) {
                DB::table('user_grid_prefs')->updateOrInsert(
                    ['username' => $username, 'view' => 'recruitment-data'],
                    ['columns_json' => json_encode($keys), 'Updated_Date' => date('Y-m-d H:i:s')]
                );
            }
            return ['status' => 'ok'];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Batch create user accounts for selected candidates.
     *
     * @param array $ids
     * @return array
     */
    public function batchCreateUserAccounts(array $ids): array
    {
        if (empty($ids)) {
            return [
                'status' => 'error',
                'message' => 'Pilih minimal 1 kandidat.',
            ];
        }

        $candidates = DB::table('recruitment')->whereIn('ID', $ids)->get();
        if ($candidates->isEmpty()) {
            return [
                'status' => 'error',
                'message' => 'Data kandidat tidak ditemukan.',
            ];
        }

        $created = 0;
        $skipped = 0;
        $errors = [];

        foreach ($candidates as $c) {
            $taskId = trim($c->Task_ID ?? '');
            if (!$taskId) {
                $skipped++;
                continue;
            }

            $exists = DB::table('users')->where('username', $taskId)->exists();
            if ($exists) {
                $skipped++;
                continue;
            }

            try {
                DB::table('users')->insert([
                    'username' => $taskId,
                    'Emp_ID'   => $taskId,
                    'password' => bcrypt('12345678'),
                    'Name'     => $c->Name,
                    'email'    => $c->Email,
                    'Location' => $c->Location ?? 'GEU-HO',
                    'Position' => 'CANDIDATE',
                    'Status'   => 'ACTIVE',
                    'Sub_Menu' => 'CI00001,CI00002,CI00003,CI00004,CI00005',
                ]);
                $created++;
            } catch (Exception $e) {
                $errors[] = "Gagal create {$taskId}: " . $e->getMessage();
            }
        }

        $msg = "{$created} akun kandidat berhasil dibuat (Default password: 12345678).";
        if ($skipped > 0) {
            $msg .= " ({$skipped} dilewati karena akun sudah ada/Task ID kosong).";
        }

        return [
            'status' => 'ok',
            'created' => $created,
            'skipped' => $skipped,
            'message' => $msg,
            'errors' => $errors,
        ];
    }

    /**
     * Batch update positions and optional link to approved Requisition (PTK).
     *
     * @param array $ids
     * @param string|null $reqTaskId
     * @param string $customPosition
     * @return array
     */
    public function batchUpdatePosition(array $ids, ?string $reqTaskId = null, string $customPosition = ''): array
    {
        if (empty($ids)) {
            return [
                'status' => 'error',
                'message' => 'Pilih minimal 1 kandidat.',
            ];
        }

        $positionName = '';
        $reqTaskIdToSave = '';
        $deptToSave = null;
        $locToSave = null;

        if ($reqTaskId) {
            $ptk = DB::table('requisition')
                ->where('Task_ID', $reqTaskId)
                ->where('Status', 'APPROVED')
                ->first();

            if (!$ptk) {
                return [
                    'status' => 'error',
                    'message' => 'PTK / Requisition yang dipilih tidak valid atau belum APPROVED.',
                ];
            }
            $positionName = $ptk->Job_Position;
            $reqTaskIdToSave = $ptk->Task_ID;
            $deptToSave = $ptk->Department;
            $locToSave = $ptk->Location;
        } elseif (trim($customPosition) !== '') {
            $positionName = trim($customPosition);
            // Cek otomatis apakah posisi manual ini cocok dengan PTK aktif
            $autoPtk = $this->resolveActiveRequisition($positionName);
            if ($autoPtk) {
                $reqTaskIdToSave = $autoPtk->Task_ID;
                $deptToSave = $autoPtk->Department;
                $locToSave = $autoPtk->Location;
            }
        } else {
            return [
                'status' => 'error',
                'message' => 'Pilih Posisi PTK yang telah Approved atau ketik posisi baru.',
            ];
        }

        $updateData = [
            'Position' => $positionName,
        ];
        if ($reqTaskIdToSave !== '') {
            $updateData['Requisition_ID'] = $reqTaskIdToSave;
            if ($deptToSave) {
                $updateData['Department'] = $deptToSave;
            }
            if ($locToSave) {
                $updateData['Location'] = $locToSave;
            }
        }

        $affected = DB::table('recruitment')
            ->whereIn('ID', $ids)
            ->update($updateData);

        return [
            'status' => 'ok',
            'affected' => $affected,
            'message' => "Posisi untuk {$affected} kandidat berhasil diperbarui menjadi '{$positionName}'" . ($reqTaskIdToSave ? " (Terhubung ke PTK: {$reqTaskIdToSave})" : "") . ".",
        ];
    }
}
