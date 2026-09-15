<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Posts UCO settlement lines into the partner financial-report journal
 * (warehouse_partner_journal) under Direct Costs (group_account 502).
 *
 * Only sections that already have a COA account are mapped; everything else
 * is skipped. Multi-route settlements cannot be attributed to one warehouse
 * and are skipped. Posting is idempotent per settlement row (reference).
 */
class SettlementJournalService
{
    public const SECTION_ACCOUNTS = [
        'BBM'    => '52001',
        'Parkir' => '52003',
    ];

    /**
     * Post every settlement of a single route. Returns a summary.
     */
    public function postForRoute(string $routeId): array
    {
        $routeId = trim((string) $routeId);

        if ($routeId === '' || strpos($routeId, ',') !== false) {
            return ['posted' => 0, 'skipped' => []];
        }

        $route = DB::table('pickup_routes')->where('ID', $routeId)->first();
        if (!$route) {
            return ['posted' => 0, 'skipped' => []];
        }

        $warehouseTaskId = $this->resolveWarehouseTaskId($route);
        $settlements = DB::table('settlement')->where('Route_Id', $routeId)->get();

        $summary = ['posted' => 0, 'skipped' => []];
        foreach ($settlements as $s) {
            $status = $this->postSettlement($s, $warehouseTaskId);
            if ($status === 'posted') {
                $summary['posted']++;
            } else {
                $summary['skipped'][] = ['id' => $s->ID, 'reason' => $status];
            }
        }

        return $summary;
    }

    /**
     * Post a single settlement row. Returns 'posted', 'would_post', or a skip reason.
     */
    public function postSettlement($settlement, ?string $warehouseTaskId, bool $dryRun = false): string
    {
        $reference = 'SETTLEMENT-' . $settlement->ID;

        if (DB::table('warehouse_partner_journal')->where('reference', $reference)->exists()) {
            return 'already_posted';
        }

        $accountNo = self::SECTION_ACCOUNTS[$settlement->Description] ?? null;
        if (!$accountNo) {
            return 'unmapped_section';
        }

        if (!$warehouseTaskId) {
            return 'no_warehouse';
        }

        $nominal = (float) $settlement->Nominal;
        if ($nominal <= 0) {
            return 'non_positive_nominal';
        }

        $account = DB::table('master_warehouse_account')->where('account_no', $accountNo)->first();
        if (!$account) {
            return 'account_not_found';
        }

        if ($dryRun) {
            return 'would_post';
        }

        DB::table('warehouse_partner_journal')->insert([
            'warehouse_task_id' => $warehouseTaskId,
            'journal_no'        => 'JVC-SET-' . $settlement->ID,
            'group_account'     => $account->group_account,
            'account_no'        => $account->account_no,
            'account_name'      => $account->account_name,
            'posting_date'      => $settlement->Created_At ? substr((string) $settlement->Created_At, 0, 10) : now()->toDateString(),
            'nominal'           => $nominal,
            'reference'         => $reference,
            'remark'            => (string) ($settlement->Notes ?? ''),
            'created_by'        => auth()->user()->username ?? 'system',
            'created_date'      => now(),
        ]);

        return 'posted';
    }

    /**
     * Resolve the warehouse task id for a route: the stored Warehouse_Id, or
     * derived via waypoints -> pickup_order -> work_order -> master_poo.
     */
    public function resolveWarehouseTaskId($route): ?string
    {
        if (!empty($route->Warehouse_Id)) {
            $task = DB::table('warehouse_partner')->where('id', $route->Warehouse_Id)->value('warehouse_task_id');
            if ($task) {
                return $task;
            }
        }

        $nearest = DB::table('pickup_route_waypoints as w')
            ->join('pickup_order as po', 'po.ID', '=', 'w.PO_Id')
            ->join('work_order as wo', 'po.WO_Id', '=', 'wo.ID')
            ->join('master_poo as mp', 'wo.POO_Id', '=', 'mp.ID')
            ->where('w.Route_Id', $route->ID)
            ->whereNotNull('mp.Nearest_Warehouse')
            ->orderBy('w.Sequence')
            ->value('mp.Nearest_Warehouse');

        if (!$nearest) {
            return null;
        }

        return DB::table('warehouse_partner')->where('id', $nearest)->value('warehouse_task_id');
    }
}
