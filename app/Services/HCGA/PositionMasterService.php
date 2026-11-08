<?php

namespace App\Services\HCGA;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PositionMasterService
{
    /**
     * Dapatkan daftar posisi untuk XML grid
     */
    public function getPositionsQuery()
    {
        return DB::table('master_position')
            ->orderBy('Level', 'ASC')
            ->orderBy('Position', 'ASC')
            ->get();
    }

    /**
     * Update baris master position dari dhtmlx grid
     */
    public function updatePositionRow($rowId, array $param, $syncExisting = false)
    {
        $directorat    = trim((string) ($param[$rowId . "_c1"] ?? ''));
        $department    = trim((string) ($param[$rowId . "_c2"] ?? ''));
        $subsection    = trim((string) ($param[$rowId . "_c3"] ?? ''));
        $position      = trim((string) ($param[$rowId . "_c4"] ?? ''));
        $initialCode   = trim((string) ($param[$rowId . "_c5"] ?? ''));
        $initial       = trim((string) ($param[$rowId . "_c6"] ?? ''));
        $level         = (int) ($param[$rowId . "_c7"] ?? 1);
        $parentId      = trim((string) ($param[$rowId . "_c8"] ?? ''));
        $location      = trim((string) ($param[$rowId . "_c9"] ?? 'GEU-HO'));
        $companyId     = trim((string) ($param[$rowId . "_c10"] ?? 'GEU'));
        $positionType  = trim((string) ($param[$rowId . "_c11"] ?? 'STRUCTURAL'));
        $status        = strtoupper(trim((string) ($param[$rowId . "_c12"] ?? 'ACTIVE')));
        $compensation  = strtoupper(trim((string) ($param[$rowId . "_c13"] ?? '')));

        if (!in_array($compensation, ['PAID', 'TIME_OFF'])) {
            $compensation = null;
        }

        $user = Auth::user()->username ?? 'system';

        $posExisting = DB::table('master_position')->where('ID', (int) $rowId)->first();

        $updateData = [
            'Directorat'            => $directorat,
            'Department'            => $department,
            'Subsection'            => $subsection,
            'Position'              => $position,
            'Initial_Code'          => $initialCode,
            'Initial'               => $initial,
            'Level'                 => $level,
            'Parent_Position_ID'    => $parentId !== '' ? (int) $parentId : null,
            'Location'              => $location ?: 'GEU-HO',
            'Company_ID'            => $companyId ?: 'GEU',
            'Position_Type'         => $positionType ?: 'STRUCTURAL',
            'Status'                => $status ?: 'ACTIVE',
            'overtime_compensation' => $compensation,
            'Updated_By'            => $user,
            'Updated_Date'          => Carbon::now(),
        ];

        DB::table('master_position')->where('ID', (int) $rowId)->update($updateData);

        // Jika opsi syncExisting aktif dan kompensasi ditentukan
        if ($syncExisting && $compensation) {
            $this->syncPositionCompensationToEmployees($posExisting ?: (object) $updateData, $compensation);
        }

        return "update";
    }

    /**
     * Tambah posisi baru
     */
    public function insertPositionRow($rowId, array $param)
    {
        $directorat    = trim((string) ($param[$rowId . "_c1"] ?? ''));
        $department    = trim((string) ($param[$rowId . "_c2"] ?? ''));
        $subsection    = trim((string) ($param[$rowId . "_c3"] ?? ''));
        $position      = trim((string) ($param[$rowId . "_c4"] ?? ''));
        $initialCode   = trim((string) ($param[$rowId . "_c5"] ?? ''));
        $initial       = trim((string) ($param[$rowId . "_c6"] ?? ''));
        $level         = (int) ($param[$rowId . "_c7"] ?? 1);
        $parentId      = trim((string) ($param[$rowId . "_c8"] ?? ''));
        $location      = trim((string) ($param[$rowId . "_c9"] ?? 'GEU-HO'));
        $companyId     = trim((string) ($param[$rowId . "_c10"] ?? 'GEU'));
        $positionType  = trim((string) ($param[$rowId . "_c11"] ?? 'STRUCTURAL'));
        $status        = strtoupper(trim((string) ($param[$rowId . "_c12"] ?? 'ACTIVE')));
        $compensation  = strtoupper(trim((string) ($param[$rowId . "_c13"] ?? '')));

        if (!in_array($compensation, ['PAID', 'TIME_OFF'])) {
            $compensation = null;
        }

        $user = Auth::user()->username ?? 'system';

        if ($position === '' && $initial === '') {
            return "cannot_empty";
        }

        $insertData = [
            'Directorat'            => $directorat,
            'Department'            => $department,
            'Subsection'            => $subsection,
            'Position'              => $position,
            'Initial_Code'          => $initialCode,
            'Initial'               => $initial,
            'Level'                 => $level,
            'Parent_Position_ID'    => $parentId !== '' ? (int) $parentId : null,
            'Location'              => $location ?: 'GEU-HO',
            'Company_ID'            => $companyId ?: 'GEU',
            'Position_Type'         => $positionType ?: 'STRUCTURAL',
            'Status'                => $status ?: 'ACTIVE',
            'overtime_compensation' => $compensation,
            'Created_By'            => $user,
            'Created_Date'          => Carbon::now(),
        ];

        DB::table('master_position')->insert($insertData);

        return "insert";
    }

    /**
     * Hapus posisi
     */
    public function deletePositionRow($rowId)
    {
        DB::table('master_position')->where('ID', (int) $rowId)->delete();
        return "delete";
    }

    /**
     * Sinkronkan kompensasi ke seluruh karyawan existing yang memiliki posisi ini
     */
    public function syncPositionCompensationToEmployees($positionObj, string $compensation)
    {
        if (!in_array($compensation, ['PAID', 'TIME_OFF'])) {
            return 0;
        }

        $posNames = array_filter([$positionObj->Position ?? null, $positionObj->Initial ?? null, $positionObj->Initial_Code ?? null]);
        if (empty($posNames)) {
            return 0;
        }

        // Cari karyawan di tabel employment yang memiliki posisi tersebut
        $affected = DB::table('employment')
            ->where(function ($query) use ($posNames) {
                foreach ($posNames as $p) {
                    $query->orWhere('Position', $p);
                }
            })
            ->update(['overtime_compensation' => $compensation]);

        // Sinkronkan juga ke users berdasarkan Position
        DB::table('users')
            ->where(function ($query) use ($posNames) {
                foreach ($posNames as $p) {
                    $query->orWhere('Position', $p);
                }
            })
            ->update(['overtime_compensation' => $compensation]);

        // Sinkronkan juga ke organization_structure jika ada node terkait
        if (!empty($positionObj->ID)) {
            DB::table('organization_structure')
                ->where('Position_ID', $positionObj->ID)
                ->update(['overtime_compensation' => $compensation]);
        }

        return $affected;
    }

    /**
     * Sinkronkan karyawan existing berdasarkan ID posisi dan pilihan kompensasi
     */
    public function syncExistingByPositionId(int $positionId, ?string $compensation = null)
    {
        $pos = DB::table('master_position')->where('ID', $positionId)->first();
        if (!$pos) {
            return ['status' => 'error', 'message' => 'Posisi tidak ditemukan'];
        }

        $targetCompensation = $compensation ?: $pos->overtime_compensation;
        if (!$targetCompensation) {
            // Jika NULL, berarti reset override agar karyawan mengikuti default master
            $posNames = array_filter([$pos->Position, $pos->Initial, $pos->Initial_Code]);
            DB::table('employment')->where(function ($q) use ($posNames) {
                foreach ($posNames as $p) {
                    $q->orWhere('Position', $p);
                }
            })->update(['overtime_compensation' => null]);

            DB::table('users')->where(function ($q) use ($posNames) {
                foreach ($posNames as $p) {
                    $q->orWhere('Position', $p);
                }
            })->update(['overtime_compensation' => null]);

            return [
                'status'   => 'success',
                'message'  => 'Override kompensasi karyawan berhasil di-reset mengikuti master posisi default.'
            ];
        }

        $count = $this->syncPositionCompensationToEmployees($pos, $targetCompensation);

        return [
            'status'   => 'success',
            'affected' => $count,
            'message'  => "Berhasil memperbarui kompensasi ({$targetCompensation}) pada {$count} karyawan existing dengan jabatan ini."
        ];
    }
}

