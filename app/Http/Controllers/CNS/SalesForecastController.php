<?php

namespace App\Http\Controllers\CNS;

use App\CnsSalesForecast;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SalesForecastController extends Controller
{
    private const MONTH_LABELS = [
        '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
        '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Agu',
        '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',
    ];

    public function index(Request $request)
    {
        $year = $this->resolveYear($request);
        $data = $this->buildDashboardData($year);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($data);
        }

        return view('cns.sales.sales-forecast', $data);
    }

    public function dataAjax(Request $request)
    {
        $year = $this->resolveYear($request);

        return response()->json($this->buildDashboardData($year));
    }

    public function store(Request $request)
    {
        $validated = $this->validateForecast($request);
        $user = $this->currentUserName();

        $forecast = CnsSalesForecast::where('period_month', $validated['period_month'])->first();
        if ($forecast) {
            $forecast->update([
                'planned_mt' => $validated['planned_mt'],
                'notes'      => $validated['notes'] ?? null,
                'updated_by' => $user,
            ]);
        } else {
            $forecast = CnsSalesForecast::create([
                'period_month' => $validated['period_month'],
                'planned_mt'   => $validated['planned_mt'],
                'notes'        => $validated['notes'] ?? null,
                'created_by'   => $user,
                'updated_by'   => $user,
            ]);
        }

        return response()->json([
            'message' => 'Plan volume penjualan bulanan berhasil disimpan.',
            'data'    => $forecast->fresh(),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $forecast = CnsSalesForecast::findOrFail($id);
        $validated = $this->validateForecast($request);

        $forecast->update([
            'period_month' => $validated['period_month'],
            'planned_mt'   => $validated['planned_mt'],
            'notes'        => $validated['notes'] ?? null,
            'updated_by'   => $this->currentUserName(),
        ]);

        return response()->json([
            'message' => 'Plan volume penjualan bulanan berhasil diperbarui.',
            'data'    => $forecast->fresh(),
        ]);
    }

    public function destroy($id)
    {
        $forecast = CnsSalesForecast::findOrFail($id);
        $forecast->delete();

        return response()->json([
            'message' => 'Plan volume penjualan bulanan berhasil dihapus.',
        ]);
    }

    private function resolveYear(Request $request): int
    {
        $year = (int) $request->input('year', date('Y'));
        return ($year >= 2020 && $year <= 2100) ? $year : (int) date('Y');
    }

    private function validateForecast(Request $request): array
    {
        return $request->validate([
            'period_month' => ['required', 'string', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'planned_mt'   => 'required|numeric|min:0',
            'notes'        => 'nullable|string|max:255',
        ], [
            'period_month.regex' => 'Periode bulan harus berformat YYYY-MM (contoh: 2026-11).',
        ]);
    }

    public function buildDashboardData(int $year): array
    {
        $prefix = $year . '-';

        $forecastsByMonth = [];
        if (Schema::hasTable('cns_sales_forecasts')) {
            $rows = CnsSalesForecast::where('period_month', 'like', $prefix . '%')->get();
            foreach ($rows as $row) {
                $forecastsByMonth[$row->period_month] = $row;
            }
        }

        $stuffedByMonth = $this->fetchMonthlyStuffedMt($prefix);
        $pipeline = $this->fetchMonthlyBcPipeline($prefix);

        $monthly = [];
        $totalPlannedMt = 0.0;
        $totalStuffedMt = 0.0;

        foreach (self::MONTH_LABELS as $mm => $label) {
            $periodMonth = $prefix . $mm;
            $forecast = $forecastsByMonth[$periodMonth] ?? null;
            $plannedMt = $forecast ? (float) $forecast->planned_mt : 0.0;
            $stuffedMt = (float) ($stuffedByMonth[$periodMonth] ?? 0.0);
            $remainingMt = max(0.0, $plannedMt - $stuffedMt);
            $achievedPct = $plannedMt > 0 ? round(($stuffedMt / $plannedMt) * 100, 1) : 0.0;

            $totalPlannedMt += $plannedMt;
            $totalStuffedMt += $stuffedMt;

            $monthly[] = [
                'id'           => $forecast ? (int) $forecast->id : null,
                'period_month' => $periodMonth,
                'month_label'  => $label,
                'planned_mt'   => round($plannedMt, 3),
                'stuffed_mt'   => round($stuffedMt, 3),
                'remaining_mt' => round($remainingMt, 3),
                'achieved_pct' => $achievedPct,
                'notes'        => $forecast ? (string) ($forecast->notes ?? '') : '',
                'bc_count'     => $pipeline['monthly_totals'][$mm] ?? 0,
            ];
        }

        $overallPct = $totalPlannedMt > 0 ? round(($totalStuffedMt / $totalPlannedMt) * 100, 1) : 0.0;

        return [
            'year'     => $year,
            'monthly'  => $monthly,
            'pipeline' => $pipeline,
            'kpis'     => [
                'total_planned_mt' => round($totalPlannedMt, 2),
                'total_stuffed_mt' => round($totalStuffedMt, 2),
                'overall_pct'      => $overallPct,
                'total_bc'         => array_sum($pipeline['monthly_totals']),
            ],
        ];
    }

    private function fetchMonthlyStuffedMt(string $yearPrefix): array
    {
        if (!Schema::hasTable('vessel_schedules')) {
            return [];
        }

        $vsdByOrder = [];
        if (Schema::hasTable('vessel_schedule_dtls')) {
            $vsdRows = DB::table('vessel_schedule_dtls')
                ->where('Status', '!=', 'CANCELED')
                ->whereNotNull('Order_No')
                ->where('Order_No', '!=', '')
                ->groupBy('Order_No')
                ->select('Order_No', DB::raw('COALESCE(SUM(Netto_MT), 0) as total_netto_mt'))
                ->get();

            foreach ($vsdRows as $row) {
                $vsdByOrder[$row->Order_No] = (float) $row->total_netto_mt;
            }
        }

        $schedules = DB::table('vessel_schedules')->get();
        $totals = [];

        foreach ($schedules as $vs) {
            $statusUpper = strtoupper(trim((string) ($vs->Status ?? '')));
            if (in_array($statusUpper, ['CANCELED', 'CANCELLED'], true)) {
                continue;
            }

            $dateRef = !empty($vs->Stuffing) ? (string) $vs->Stuffing : (!empty($vs->ETD) ? (string) $vs->ETD : '');
            $monthKey = strlen($dateRef) >= 7 ? substr($dateRef, 0, 7) : '';
            if (strpos($monthKey, $yearPrefix) !== 0) {
                continue;
            }

            $orderNo = (string) ($vs->Order_No ?? '');
            $volumeMt = isset($vsdByOrder[$orderNo])
                ? $vsdByOrder[$orderNo]
                : $this->parseNumeric($vs->Qty ?? 0);

            if ($volumeMt <= 0) {
                continue;
            }

            $totals[$monthKey] = ($totals[$monthKey] ?? 0.0) + $volumeMt;
        }

        return $totals;
    }

    private function fetchMonthlyBcPipeline(string $yearPrefix): array
    {
        $monthlyTotals = array_fill_keys(array_keys(self::MONTH_LABELS), 0);
        if (!Schema::hasTable('cns_buyer_contract_bookings') || !Schema::hasTable('cns_buyer_contracts')) {
            return [
                'categories'     => array_values(self::MONTH_LABELS),
                'series'         => [],
                'monthly_totals' => $monthlyTotals,
                'breakdown_rows' => [],
            ];
        }

        $bookings = DB::table('cns_buyer_contract_bookings as b')
            ->join('cns_buyer_contracts as c', 'c.ID', '=', 'b.Contract_ID')
            ->select('b.ID as booking_id', 'b.Contract_ID', 'b.Created_Date', 'c.Contract_No', 'c.Buyer_Name')
            ->get();

        $vsDateByBooking = [];
        if (Schema::hasTable('vessel_schedules')) {
            $vsRows = DB::table('vessel_schedules')
                ->whereNotNull('Booking_ID')
                ->get(['Booking_ID', 'Stuffing', 'ETD']);

            foreach ($vsRows as $vs) {
                $ref = !empty($vs->Stuffing) ? (string) $vs->Stuffing : (!empty($vs->ETD) ? (string) $vs->ETD : '');
                if ($ref !== '' && !isset($vsDateByBooking[$vs->Booking_ID])) {
                    $vsDateByBooking[$vs->Booking_ID] = $ref;
                }
            }
        }

        $contractMeta = [];
        $countsByContractMonth = [];
        $countsByBuyerMonth = [];

        foreach ($bookings as $b) {
            $dateRef = $vsDateByBooking[$b->booking_id] ?? (string) ($b->Created_Date ?? '');
            $monthKey = strlen($dateRef) >= 7 ? substr($dateRef, 0, 7) : '';
            if (strpos($monthKey, $yearPrefix) !== 0) {
                continue;
            }

            $mm = substr($monthKey, 5, 2);
            if (!isset($monthlyTotals[$mm])) {
                continue;
            }

            $cid = (int) $b->Contract_ID;
            $buyerName = trim((string) ($b->Buyer_Name ?: 'Unknown Buyer'));
            if (!isset($contractMeta[$cid])) {
                $contractMeta[$cid] = [
                    'contract_id' => $cid,
                    'contract_no' => (string) $b->Contract_No,
                    'buyer_name'  => $buyerName,
                ];
            }

            $countsByContractMonth[$cid][$mm] = ($countsByContractMonth[$cid][$mm] ?? 0) + 1;
            $countsByBuyerMonth[$buyerName][$mm] = ($countsByBuyerMonth[$buyerName][$mm] ?? 0) + 1;
            $monthlyTotals[$mm]++;
        }

        $breakdownRows = [];
        foreach ($contractMeta as $cid => $meta) {
            foreach (self::MONTH_LABELS as $mm => $label) {
                $cnt = $countsByContractMonth[$cid][$mm] ?? 0;
                if ($cnt <= 0) {
                    continue;
                }
                $tot = $monthlyTotals[$mm];
                $breakdownRows[] = [
                    'period_month'   => $yearPrefix . $mm,
                    'month_label'    => $label,
                    'contract_no'    => $meta['contract_no'],
                    'buyer_name'     => $meta['buyer_name'],
                    'bc_count'       => $cnt,
                    'total_month_bc' => $tot,
                    'pct'            => $tot > 0 ? round(($cnt / $tot) * 100, 1) : 0.0,
                ];
            }
        }

        // Aggregate chart series by Buyer, capped at Top 8 + "Lainnya"
        uasort($countsByBuyerMonth, function ($a, $b) {
            return array_sum($b) <=> array_sum($a);
        });

        $topBuyers = array_slice($countsByBuyerMonth, 0, 8, true);
        $otherBuyers = array_slice($countsByBuyerMonth, 8, null, true);
        if (!empty($otherBuyers)) {
            $otherMonthly = [];
            foreach (self::MONTH_LABELS as $mm => $label) {
                $sum = 0;
                foreach ($otherBuyers as $buyerCounts) {
                    $sum += $buyerCounts[$mm] ?? 0;
                }
                $otherMonthly[$mm] = $sum;
            }
            $topBuyers['Lainnya'] = $otherMonthly;
        }

        $series = [];
        foreach ($topBuyers as $buyerName => $monthCounts) {
            $dataCounts = [];
            $dataPcts = [];
            foreach (self::MONTH_LABELS as $mm => $label) {
                $cnt = $monthCounts[$mm] ?? 0;
                $tot = $monthlyTotals[$mm];
                $dataCounts[] = $cnt;
                $dataPcts[] = $tot > 0 ? round(($cnt / $tot) * 100, 1) : 0.0;
            }

            $series[] = [
                'name' => $buyerName,
                'data' => $dataCounts,
                'pcts' => $dataPcts,
            ];
        }

        return [
            'categories'     => array_values(self::MONTH_LABELS),
            'series'         => $series,
            'monthly_totals' => $monthlyTotals,
            'breakdown_rows' => $breakdownRows,
        ];
    }

    private function parseNumeric($val): float
    {
        $cleaned = preg_replace('/[^0-9.]/', '', (string) $val);
        return $cleaned === '' ? 0.0 : (float) $cleaned;
    }

    private function currentUserName(): string
    {
        $user = auth()->user();
        if ($user) {
            return (string) ($user->Name ?? $user->username ?? $user->email ?? 'system');
        }

        return (string) (session('User_ID') ?? 'system');
    }
}
