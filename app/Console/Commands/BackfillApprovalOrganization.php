<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillApprovalOrganization extends Command
{
    protected $signature = 'approval:backfill-organization {--dry-run : Show what would change without writing}';

    protected $description = 'Backfill master_position Location/Company/Parent and position_assignment from legacy users.Position data';

    public function handle()
    {
        $dryRun = (bool) $this->option('dry-run');
        $positions = DB::table('master_position')->orderBy('ID', 'ASC')->get();
        $assignmentsCreated = 0;
        $positionsUpdated = 0;

        foreach ($positions as $position) {
            $scope = DB::table('users')
                ->select('Location', 'Company_ID', DB::raw('COUNT(*) AS Total'))
                ->where('Status', 'ACTIVE')
                ->where(function ($query) use ($position) {
                    $query->where('Position', $position->Initial)
                        ->orWhere('Position', $position->Position);
                })
                ->groupBy('Location', 'Company_ID')
                ->orderByDesc('Total')
                ->first();

            $location = $position->Location ?: ($scope->Location ?? 'GEU-HO');
            $company = $position->Company_ID ?: ($scope->Company_ID ?? 'GEU');

            if (!$position->Location || !$position->Company_ID) {
                $this->line(sprintf(
                    '%s position #%s %s => Location=%s Company=%s',
                    $dryRun ? '[dry]' : '[set]',
                    $position->ID,
                    $position->Initial,
                    $location,
                    $company
                ));

                if (!$dryRun) {
                    DB::table('master_position')->where('ID', $position->ID)->update([
                        'Location' => $location,
                        'Company_ID' => $company,
                        'Updated_By' => 'system',
                        'Updated_Date' => now(),
                    ]);
                    $positionsUpdated++;
                }
            }

            if (empty($position->Parent_Position_ID) && (int) $position->Level > 1) {
                $parent = DB::table('master_position')
                    ->where('ID', '!=', $position->ID)
                    ->where('Level', '<', (int) $position->Level)
                    ->where(function ($query) {
                        $query->whereNull('Status')->orWhere('Status', 'ACTIVE');
                    })
                    ->where(function ($query) use ($location) {
                        $query->where('Location', $location)->orWhereNull('Location');
                    })
                    ->where(function ($query) use ($company) {
                        $query->where('Company_ID', $company)->orWhereNull('Company_ID');
                    })
                    ->orderBy('Level', 'DESC')
                    ->orderBy('ID', 'ASC')
                    ->first();

                if ($parent) {
                    $this->line(sprintf(
                        '%s parent #%s %s => #%s %s (Level %s)',
                        $dryRun ? '[dry]' : '[set]',
                        $position->ID,
                        $position->Initial,
                        $parent->ID,
                        $parent->Initial,
                        $parent->Level
                    ));

                    if (!$dryRun) {
                        DB::table('master_position')->where('ID', $position->ID)->update([
                            'Parent_Position_ID' => $parent->ID,
                            'Updated_By' => 'system',
                            'Updated_Date' => now(),
                        ]);
                    }
                }
            }

            $users = DB::table('users')
                ->where('Status', 'ACTIVE')
                ->where(function ($query) use ($position) {
                    $query->where('Position', $position->Initial)
                        ->orWhere('Position', $position->Position);
                })
                ->where('Location', $location)
                ->where(function ($query) use ($company) {
                    $query->where('Company_ID', $company)->orWhereNull('Company_ID');
                })
                ->get();

            foreach ($users as $user) {
                $exists = DB::table('position_assignment')
                    ->where('Position_ID', (int) $position->ID)
                    ->where('User_Id', (int) $user->id)
                    ->where('Status', 'ACTIVE')
                    ->exists();

                if ($exists) {
                    continue;
                }

                $this->line(sprintf(
                    '%s assignment position #%s %s <= user #%s %s',
                    $dryRun ? '[dry]' : '[set]',
                    $position->ID,
                    $position->Initial,
                    $user->id,
                    $user->username
                ));

                if (!$dryRun) {
                    DB::table('position_assignment')->insert([
                        'Position_ID' => (int) $position->ID,
                        'User_Id' => (int) $user->id,
                        'Emp_ID' => $user->Emp_ID,
                        'Location' => $location,
                        'Company_ID' => $company,
                        'Department' => $user->Department ?: $position->Department,
                        'Assignment_Type' => 'PRIMARY',
                        'Start_Date' => now()->toDateString(),
                        'Status' => 'ACTIVE',
                        'Created_By' => 'system',
                        'Created_At' => now(),
                    ]);
                }

                $assignmentsCreated++;
            }
        }

        $this->info(sprintf(
            '%s positions updated, %s assignments %s.',
            $positionsUpdated,
            $assignmentsCreated,
            $dryRun ? 'would be created' : 'created'
        ));

        return 0;
    }
}