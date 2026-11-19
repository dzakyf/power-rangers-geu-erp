<?php

namespace App\Services\HCGA;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AssetLegacyLogService
{
    /**
     * Get historical log entries for Asset Disposal.
     *
     * @param string $disposalNo
     * @return Collection
     */
    public function getDisposalLogs(string $disposalNo): Collection
    {
        if ($disposalNo === '') {
            return collect();
        }

        return DB::table('asset_disposal_log')
            ->where('Disposal_No', $disposalNo)
            ->orderBy('ID', 'DESC')
            ->get();
    }

    /**
     * Get historical log entries for Asset Fixing.
     *
     * @param string $id
     * @return Collection
     */
    public function getFixingLogs(string $id): Collection
    {
        if ($id === '') {
            return collect();
        }

        return DB::table('asset_fixing_log')
            ->where('ID', $id)
            ->orderBy('ID', 'DESC')
            ->get();
    }

    /**
     * Get historical log entries for Asset Moving.
     *
     * @param string $noTrx
     * @return Collection
     */
    public function getMovingLogs(string $noTrx): Collection
    {
        if ($noTrx === '') {
            return collect();
        }

        return DB::table('moving_asset')
            ->where('No_Trx', $noTrx)
            ->orderBy('ID', 'DESC')
            ->get();
    }

    /**
     * Get historical log entries for Asset Handover.
     *
     * @param string $handoverNo
     * @return Collection
     */
    public function getHandoverLogs(string $handoverNo): Collection
    {
        if ($handoverNo === '') {
            return collect();
        }

        return DB::table('asset_handover_log')
            ->where('Handover_No', $handoverNo)
            ->orderBy('ID', 'DESC')
            ->get();
    }
}
