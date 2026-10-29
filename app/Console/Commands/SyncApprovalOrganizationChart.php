<?php

namespace App\Console\Commands;

use App\Services\Approval\OrganizationChartSyncService;
use Illuminate\Console\Command;

class SyncApprovalOrganizationChart extends Command
{
    protected $signature = 'approval:sync-organization-chart {--assignments : Also create assignments from NIK on bagan nodes}';

    protected $description = 'Link organization_structure to master_position and sync bagan hierarchy into approval fallback';

    public function handle(OrganizationChartSyncService $sync)
    {
        $stats = $sync->syncStructure();

        $this->info(sprintf(
            '%s bagan node linked, %s master_position hierarchy updated.',
            $stats['linked'],
            $stats['hierarchy_updated']
        ));

        if ($this->option('assignments')) {
            $assignment = $sync->syncAssignments();
            $this->info(sprintf(
                '%s assignment created, %s skipped (no user), %s skipped (already active).',
                $assignment['created'],
                $assignment['skipped_no_user'],
                $assignment['skipped_existing']
            ));
        }

        return 0;
    }
}