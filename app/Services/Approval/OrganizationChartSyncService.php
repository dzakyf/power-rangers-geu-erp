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
            $position = DB::table('master_position')
                ->where('Position', $row->Position)
                ->where('Department', $row->Department)
                ->orderBy('ID')
                ->first();

            if (!$position) {
                $candidates = DB::table('master_position')
                    ->where('Position', $row->Position)
                    ->orderBy('ID')
                    ->get();
                if ($candidates->count() === 1) {
                    $position = $candidates->first();
                }
            }

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
     * Optional: create PRIMARY assignments from the NIK on each bagan node.
     * Not run by default because demo/fake NIK data can point at the wrong
     * user. Use the CLI command --assignments flag after validating data.
     */
    public function syncAssignments(): array
    {
        $created = 0;
        $skippedNoUser = 0;
        $skippedExisting = 0;

        $rows = DB::table('organization_structure')
            ->whereNotNull('Position_ID')
            ->whereNotNull('NIK')
            ->where('NIK', '<>', '')
            ->orderBy('ID')
            ->get();

        foreach ($rows as $row) {
            $user = DB::table('users')
                ->where('Emp_ID', $row->NIK)
                ->where('Status', 'ACTIVE')
                ->first();

            if (!$user) {
                $skippedNoUser++;
                continue;
            }

            $position = DB::table('master_position')
                ->where('ID', (int) $row->Position_ID)
                ->first();

            if (!$position) {
                $skippedNoUser++;
                continue;
            }

            $exists = DB::table('position_assignment')
                ->where('Position_ID', (int) $row->Position_ID)
                ->where('User_Id', (int) $user->id)
                ->where('Status', 'ACTIVE')
                ->exists();

            if ($exists) {
                $skippedExisting++;
                continue;
            }

            DB::table('position_assignment')->insert([
                'Position_ID' => (int) $row->Position_ID,
                'User_Id' => (int) $user->id,
                'Emp_ID' => $user->Emp_ID,
                'Location' => $position->Location ?: $user->Location,
                'Company_ID' => $position->Company_ID ?: $user->Company_ID,
                'Department' => $user->Department ?: $position->Department,
                'Assignment_Type' => 'PRIMARY',
                'Start_Date' => now()->toDateString(),
                'Status' => 'ACTIVE',
                'Created_By' => 'organization-chart-sync',
                'Created_At' => now(),
            ]);
            $created++;
        }

        return [
            'created' => $created,
            'skipped_no_user' => $skippedNoUser,
            'skipped_existing' => $skippedExisting,
        ];
    }

    public function syncStructure(): array
    {
        return [
            'linked' => $this->linkPositions(),
            'hierarchy_updated' => $this->syncHierarchy(),
        ];
    }
}