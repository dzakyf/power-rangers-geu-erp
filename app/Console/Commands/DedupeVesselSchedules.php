<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DedupeVesselSchedules extends Command
{
    protected $signature = 'cns:dedupe-vessel-schedules
                            {--force : Delete duplicate rows; without this flag only reports}';

    protected $description = 'Remove duplicate Vessel Schedule rows created by the old update bug';

    public function handle()
    {
        $groups = DB::table('vessel_schedules')
            ->select('Task_ID', 'Order_No', DB::raw('COUNT(*) as total'), DB::raw('MIN(id) as keep_id'))
            ->groupBy('Task_ID', 'Order_No')
            ->havingRaw('COUNT(*) > 1')
            ->orderBy('Order_No')
            ->get();

        if ($groups->isEmpty()) {
            $this->info('No duplicate vessel schedules found.');
            return 0;
        }

        $removed = 0;
        $skipped = 0;

        foreach ($groups as $group) {
            $rows = DB::table('vessel_schedules')
                ->where('Task_ID', $group->Task_ID)
                ->where('Order_No', $group->Order_No)
                ->orderBy('id')
                ->get();

            $keep = $rows->first();
            $duplicates = $rows->slice(1);
            $canonical = (array) $keep;
            unset($canonical['id']);

            $identical = true;
            foreach ($duplicates as $duplicate) {
                $candidate = (array) $duplicate;
                unset($candidate['id']);

                if ($candidate !== $canonical) {
                    $identical = false;
                    break;
                }
            }

            $ids = $duplicates->pluck('id')->all();

            if (!$identical) {
                $this->warn(sprintf(
                    'Skipped %s (%d duplicate rows): records differ and need manual review.',
                    $group->Order_No,
                    count($ids)
                ));
                $skipped += count($ids);
                continue;
            }

            $this->line(sprintf(
                '%s %s: keep id %d, remove id(s) %s',
                $this->option('force') ? 'Removing' : 'Would remove',
                $group->Order_No,
                $keep->id,
                implode(', ', $ids)
            ));

            if (!$this->option('force')) {
                $removed += count($ids);
                continue;
            }

            DB::transaction(function () use ($keep, $ids) {
                DB::table('vessel_schedule_logs')
                    ->whereIn('Last_ID', $ids)
                    ->update(['Last_ID' => $keep->id]);

                DB::table('vessel_schedules')
                    ->whereIn('id', $ids)
                    ->delete();
            });

            $removed += count($ids);
        }

        if ($this->option('force')) {
            $this->info(sprintf('Removed %d duplicate row(s). Skipped %d row(s).', $removed, $skipped));
        } else {
            $this->info(sprintf('Dry run: %d row(s) can be removed. Skipped %d row(s).', $removed, $skipped));
        }

        return 0;
    }
}
