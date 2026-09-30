<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Partner pipeline-leads single source of truth (Model A).
 *
 * `Status` is the ordered pipeline stage:
 *   Fresh -> FU 1 -> FU 2 -> FU 3 -> Qualified -> Meeting 1 -> Meeting 2
 *         -> Warm -> Negotiation -> Deal        (terminal: Deal)
 *   Cancel (terminal) is only reachable from FU 3 / Meeting 1 / Meeting 2.
 *
 * Response is an independent attribute; Classification and Qualification are
 * derived from Status (persisted into their columns).
 */
class LeadsService
{
    public const STAGES = [
        'Fresh', 'FU 1', 'FU 2', 'FU 3', 'Qualified',
        'Meeting 1', 'Meeting 2', 'Warm', 'Negotiation', 'Deal', 'Cancel',
    ];

    public const CANCEL_STAGES = ['FU 3', 'Meeting 1', 'Meeting 2'];

    public const CANCEL_REASONS = ['No Response', 'Unqualified', 'Other'];

    public function metricExpressions(): array
    {
        $responded = "CASE WHEN opportunity.Response = 'Response' THEN 1 ELSE 0 END";

        return [
            'total_leads'        => 'COUNT(*)',
            'response'           => "SUM($responded)",
            'no_response'        => "COUNT(*) - SUM($responded)",
            'qualified'          => "SUM(CASE WHEN opportunity.Status IN ('Qualified','Meeting 1','Meeting 2','Warm','Negotiation','Deal') THEN 1 ELSE 0 END)",
            'unqualified'        => "SUM(CASE WHEN opportunity.Status = 'Cancel' AND opportunity.Reason = 'Unqualified' THEN 1 ELSE 0 END)",
            'unidentified'       => "COUNT(*) - SUM($responded)",
            'cold'               => "COUNT(*) - SUM($responded)",
            'meeting_1'          => "SUM(CASE WHEN opportunity.Status IN ('Meeting 1','Meeting 2','Warm','Negotiation','Deal') THEN 1 ELSE 0 END)",
            'meeting_2'          => "SUM(CASE WHEN opportunity.Status IN ('Meeting 2','Warm','Negotiation','Deal') THEN 1 ELSE 0 END)",
            'warm'               => "SUM(CASE WHEN opportunity.Status IN ('Warm','Negotiation','Deal') THEN 1 ELSE 0 END)",
            'negotiation'        => "SUM(CASE WHEN opportunity.Status IN ('Negotiation','Deal') THEN 1 ELSE 0 END)",
            'deal'               => "SUM(CASE WHEN opportunity.Status = 'Deal' THEN 1 ELSE 0 END)",
            'cancel_no_response' => "SUM(CASE WHEN opportunity.Status = 'Cancel' AND opportunity.Reason = 'No Response' THEN 1 ELSE 0 END)",
            'cancel_unqualified' => "SUM(CASE WHEN opportunity.Status = 'Cancel' AND opportunity.Reason = 'Unqualified' THEN 1 ELSE 0 END)",
            'cancel_total'       => "SUM(CASE WHEN opportunity.Status = 'Cancel' THEN 1 ELSE 0 END)",
        ];
    }

    public function funnel(array $filters = []): array
    {
        $selects = [];
        foreach ($this->metricExpressions() as $alias => $expr) {
            $selects[] = DB::raw("$expr as $alias");
        }

        return $this->cast($this->baseQuery($filters)->select($selects)->first());
    }

    public function funnelBySales(array $filters = []): Collection
    {
        $selects = [DB::raw('s.Sales as sales_ref')];
        foreach ($this->metricExpressions() as $alias => $expr) {
            $selects[] = DB::raw("$expr as $alias");
        }

        return $this->baseQuery($filters)
            ->leftJoin('opportunity_sales as s', 'opportunity.Sales_ID', '=', 's.Sales_ID')
            ->select($selects)
            ->groupBy('s.Sales')
            ->orderBy('s.Sales')
            ->get()
            ->map(fn ($row) => $this->cast($row));
    }

    /**
     * Numerator/denominator pairs per ratio key - single source for both the
     * ratio percentages and the grid hover tooltips.
     */
    public function ratioParts(array $m): array
    {
        $v = function ($k) use ($m) {
            return (float) ($m[$k] ?? 0);
        };

        $total     = $v('total_leads');
        $qualified = $v('qualified');
        $warm      = $v('warm');

        return [
            'total_leads'        => [$total, $total],
            'response'           => [$v('response'), $total],
            'no_response'        => [$v('no_response'), $total],
            'qualified'          => [$v('qualified'), $total],
            'unqualified'        => [$v('unqualified'), $total],
            'unidentified'       => [$v('unidentified'), $total],
            'cold'               => [$v('cold'), $total],
            'meeting_1'          => [$v('meeting_1'), $qualified],
            'meeting_2'          => [$v('meeting_2'), $qualified],
            'warm'               => [$v('warm'), $qualified],
            'negotiation'        => [$v('negotiation'), $warm],
            'deal'               => [$v('deal'), $warm],
            'cancel_no_response' => [$v('cancel_no_response'), $v('no_response')],
            'cancel_unqualified' => [$v('cancel_unqualified'), $v('response')],
            'cancel_total'       => [$v('cancel_total'), $total],
        ];
    }

    public function ratios(array $m): array
    {
        $out = [];
        foreach ($this->ratioParts($m) as $key => [$num, $den]) {
            $out[$key] = $den > 0 ? round(($num / $den) * 100, 0) : 0;
        }

        return $out;
    }

    public function deriveClassification(?string $status, ?string $cancelStage = null): ?string
    {
        $map = [
            'Fresh' => 'Fresh', 'FU 1' => 'FU 1', 'FU 2' => 'FU 2', 'FU 3' => 'FU 3',
            'Qualified' => 'FU 3', 'Meeting 1' => 'Meeting 1', 'Meeting 2' => 'Meeting 2',
            'Warm' => 'Meeting 2', 'Negotiation' => 'Meeting 2', 'Deal' => 'Meeting 2',
        ];

        if (isset($map[$status])) {
            return $map[$status];
        }

        return $status === 'Cancel' ? $cancelStage : null;
    }

    public function deriveQualification(?string $status, ?string $reason = null): ?string
    {
        if (in_array($status, ['Qualified', 'Meeting 1', 'Meeting 2', 'Warm', 'Negotiation', 'Deal'], true)) {
            return 'Qualified';
        }
        if ($status === 'Cancel' && $reason === 'Unqualified') {
            return 'Unqualified';
        }

        return null;
    }

    /**
     * Validate a stage selection. Returns an error message or null.
     */
    public function validateStage(?string $status, ?string $response, ?string $cancelStage, ?string $reason): ?string
    {
        $status       = ($status === '') ? null : $status;
        $response     = ($response === '') ? null : $response;
        $cancelStage  = ($cancelStage === '') ? null : $cancelStage;
        $reason       = ($reason === '') ? null : $reason;

        if (!in_array($status, self::STAGES, true)) {
            return 'Status tidak valid.';
        }
        if (!in_array($response, [null, 'Response', 'No Response'], true)) {
            return 'Response tidak valid.';
        }

        if ($status === 'Cancel') {
            if (!in_array($cancelStage, self::CANCEL_STAGES, true)) {
                return 'Cancel hanya dari FU 3, Meeting 1, atau Meeting 2.';
            }
            if (!in_array($reason, self::CANCEL_REASONS, true)) {
                return 'Alasan cancel tidak valid.';
            }
            if ($reason === 'No Response' && $response !== 'No Response') {
                return 'Alasan "No Response" memerlukan Response = No Response.';
            }
            if ($reason === 'Unqualified' && $response !== 'Response') {
                return 'Alasan "Unqualified" memerlukan Response = Response.';
            }

            return null;
        }

        if ($status === 'Fresh') {
            return null;
        }

        if ($response !== 'Response') {
            return 'Status "' . $status . '" memerlukan Response = Response.';
        }

        return null;
    }

    protected function baseQuery(array $filters)
    {
        $q = DB::table('opportunity');

        if (!empty($filters['sales_id'])) {
            $q->where('Sales_ID', $filters['sales_id']);
        }
        if (!empty($filters['location'])) {
            $q->where('Location', $filters['location']);
        }
        if (!empty($filters['date_start'])) {
            $q->whereDate('Created_Date', '>=', $filters['date_start']);
        }
        if (!empty($filters['date_end'])) {
            $q->whereDate('Created_Date', '<=', $filters['date_end']);
        }

        return $q;
    }

    protected function cast($row): array
    {
        if ($row === null) {
            return [];
        }

        $out = [];
        foreach ((array) $row as $key => $value) {
            $out[$key] = is_numeric($value) ? $value + 0 : $value;
        }

        return $out;
    }
}
