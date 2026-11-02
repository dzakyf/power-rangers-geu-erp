<?php

namespace App\Services\Approval;

use Illuminate\Support\Facades\DB;

class OrganizationChartSyncService
{
    /**
     * Link organization_structure rows to master_position.
     * Matching is exact on Position + Department. If that fails, a unique
     * match on Position alone is used.
     */
    public function linkPositions(): int
    {
        $linked = 0;
        $rows = DB::table('organization_structure')
            ->whereNull('Position_ID')
            ->whereNotNull('Position')
            ->where('Position', '<>', '')
            ->orderBy('ID')
            ->get();

        foreach ($rows as $row) {
            $position = $this->matchPosition($row);

            if (!$position) {
                continue;
            }

            DB::table('organization_structure')
                ->where('ID', (int) $row->ID)
                ->update(['Position_ID' => (int) $position->ID]);
            $linked++;
        }

        return $linked;
    }

    /**
     * organization_structure.Position menyimpan kode posisi
     * (master_position.Initial), bukan nama jabatan. Urutan pencocokan:
     * Initial + Department, Initial unik, nama Position + Department,
     * nama Position unik, lalu Initial_Code dari Position_Code.
     */
    protected function matchPosition($row)
    {
        $code = trim((string) $row->Position);
        $department = trim((string) $row->Department);

        if ($code === '') {
            return null;
        }

        $attempts = [
            ['Initial', true],
            ['Initial', false],
            ['Position', true],
            ['Position', false],
        ];

        foreach ($attempts as $attempt) {
            $column = $attempt[0];
            $withDepartment = $attempt[1];

            $query = DB::table('master_position')->where($column, $code);

            if ($withDepartment && $department !== '') {
                $query->where('Department', $department);
            }

            $matches = $query->orderBy('ID')->get();

            if ($matches->count() === 1) {
                return $matches->first();
            }

            if ($matches->count() > 1 && $department !== '') {
                $exact = $matches->first(function ($item) use ($department) {
                    return (string) $item->Department === $department;
                });

                if ($exact) {
                    return $exact;
                }
            }
        }

        if (!empty($row->Position_Code)) {
            $byCode = DB::table('master_position')
                ->where('Initial_Code', trim((string) $row->Position_Code))
                ->orderBy('ID')
                ->get();

            if ($byCode->count() === 1) {
                return $byCode->first();
            }
        }

        return null;
    }

    /**
     * Copy bagan hierarchy (Reports_To_Position_ID) into
     * master_position.Parent_Position_ID so approval fallback follows the
     * same reporting line as the organization chart.
     */
    public function syncHierarchy(): int
    {
        $rows = DB::table('organization_structure')
            ->whereNotNull('Position_ID')
            ->orderBy('ID')
            ->get()
            ->keyBy('ID');

        $updated = 0;
        foreach ($rows as $row) {
            $parentPositionId = null;
            if (!empty($row->Reports_To_Position_ID)) {
                $parent = $rows->get((int) $row->Reports_To_Position_ID);
                if ($parent && !empty($parent->Position_ID)) {
                    $parentPositionId = (int) $parent->Position_ID;
                }
            }

            DB::table('master_position')
                ->where('ID', (int) $row->Position_ID)
                ->update([
                    'Parent_Position_ID' => $parentPositionId,
                    'Updated_By' => 'organization-chart',
                    'Updated_Date' => now(),
                ]);
            $updated++;
        }

        return $updated;
    }


    /**
     * Buat position_assignment dari data kepegawaian (employment) + users.
     * Cukup satu input di employment: NIK -> users.Emp_ID dan
     * employment.Position -> master_position.Initial. Assignment manual tidak
     * lagi wajib agar approver dikenali.
     */
    public function syncAssignments(): array
    {
        $created = 0;
        $skippedNoUser = 0;
        $skippedExisting = 0;
        $skippedNoPosition = 0;

        $rows = DB::table('employment as e')
            ->leftJoin('users as u', 'u.Emp_ID', '=', 'e.NIK')
            ->where(function ($query) {
                $query->whereNull('e.Status')->orWhere('e.Status', 'ACTIVE');
            })
            ->select(
                'e.NIK',
                'e.Position',
                'e.Department',
                'e.Location',
                'u.id as User_Id',
                'u.Emp_ID as User_Emp_ID',
                'u.Status as User_Status',
                'u.Location as User_Location',
                'u.Company_ID as User_Company_ID',
                'u.Department as User_Department'
            )
            ->orderBy('e.ID')
            ->get();

        foreach ($rows as $row) {
            if (empty($row->User_Id) || strtoupper((string) $row->User_Status) !== 'ACTIVE') {
                $skippedNoUser++;
                continue;
            }

            $position = $this->matchEmploymentPosition($row);

            if (!$position) {
                $skippedNoPosition++;
                continue;
            }

            $exists = DB::table('position_assignment')
                ->where('Position_ID', (int) $position->ID)
                ->where('User_Id', (int) $row->User_Id)
                ->where('Status', 'ACTIVE')
                ->exists();

            if ($exists) {
                $skippedExisting++;
                continue;
            }

            DB::table('position_assignment')->insert([
                'Position_ID' => (int) $position->ID,
                'User_Id' => (int) $row->User_Id,
                'Emp_ID' => $row->NIK ?: $row->User_Emp_ID,
                'Location' => $position->Location ?: ($row->Location ?: $row->User_Location),
                'Company_ID' => $position->Company_ID ?: $row->User_Company_ID,
                'Department' => $row->Department ?: ($row->User_Department ?: $position->Department),
                'Assignment_Type' => 'PRIMARY',
                'Start_Date' => now()->toDateString(),
                'Status' => 'ACTIVE',
                'Created_By' => 'employment-sync',
                'Created_At' => now(),
            ]);
            $created++;
        }

        return [
            'created' => $created,
            'skipped_no_user' => $skippedNoUser,
            'skipped_existing' => $skippedExisting,
            'skipped_no_position' => $skippedNoPosition,
        ];
    }

    /**
     * Cocokkan employment.Position (kode posisi) ke master_position.
     */
    protected function matchEmploymentPosition($row)
    {
        $code = trim((string) $row->Position);
        $department = trim((string) $row->Department);

        if ($code === '') {
            return null;
        }

        $query = DB::table('master_position')
            ->where(function ($sub) use ($code) {
                $sub->where('Initial', $code)->orWhere('Position', $code);
            })
            ->where(function ($sub) {
                $sub->whereNull('Status')->orWhere('Status', 'ACTIVE');
            });

        if ($department !== '') {
            $query->where(function ($sub) use ($department) {
                $sub->whereNull('Department')
                    ->orWhere('Department', '')
                    ->orWhere('Department', $department);
            });
        }

        return $query->orderBy('ID')->first();
    }

    public function syncStructure(): array
    {
        return [
            'linked' => $this->linkPositions(),
            'hierarchy_updated' => $this->syncHierarchy(),
        ];
    }
}