<?php

namespace App\Http\Controllers\CNS;

use App\BuyerContract;
use App\CnsSalesForecast;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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

        $payload = [
            'planned_mt' => $validated['planned_mt'],
            'notes'      => $validated['notes'] ?? null,
            'updated_by' => $user,
        ];
        if (Schema::hasColumn('cns_sales_forecasts', 'planned_value')) {
            $payload['planned_value'] = (float) ($validated['planned_value'] ?? 0);
        }

        $forecast = CnsSalesForecast::where('period_month', $validated['period_month'])->first();
        if ($forecast) {
            $forecast->update($payload);
        } else {
            $forecast = CnsSalesForecast::create(array_merge($payload, [
                'period_month' => $validated['period_month'],
                'created_by'   => $user,
            ]));
        }

        return response()->json([
            'message' => 'Plan penjualan bulanan berhasil disimpan.',
            'data'    => $forecast->fresh(),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $forecast = CnsSalesForecast::findOrFail($id);
        $validated = $this->validateForecast($request);

        $payload = [
            'period_month' => $validated['period_month'],
            'planned_mt'   => $validated['planned_mt'],
            'notes'        => $validated['notes'] ?? null,
            'updated_by'   => $this->currentUserName(),
        ];
        if (Schema::hasColumn('cns_sales_forecasts', 'planned_value')) {
            $payload['planned_value'] = (float) ($validated['planned_value'] ?? 0);
        }

        $forecast->update($payload);

        return response()->json([
            'message' => 'Plan penjualan bulanan berhasil diperbarui.',
            'data'    => $forecast->fresh(),
        ]);
    }

    public function destroy($id)
    {
        $forecast = CnsSalesForecast::findOrFail($id);
        $forecast->delete();

        return response()->json([
            'message' => 'Plan penjualan bulanan berhasil dihapus.',
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
            'period_month'  => ['required', 'string', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'planned_mt'    => 'required|numeric|min:0',
            'planned_value' => 'nullable|numeric|min:0',
            'notes'         => 'nullable|string|max:255',
        ], [
            'period_month.regex' => 'Periode bulan harus berformat YYYY-MM (contoh: 2026-11).',
        ]);
    }

    public function buildDashboardData(int $year): array
    {
        $prefix = $year . '-';

        $forecastsByMonth = $this->fetchMonthlyForecasts($prefix);
        $stuffingAgg = $this->fetchMonthlyStuffing($prefix);
        $pipeline = $this->fetchMonthlyBcPipeline($prefix, $stuffingAgg['by_booking']);
        $contractPipeline = $this->fetchContractPipeline($prefix);

        $monthly = [];
        $totalPlannedMt = 0.0;
        $totalStuffedMt = 0.0;
        $totalPlannedValue = 0.0;
        $totalStuffedValue = 0.0;

        foreach (self::MONTH_LABELS as $mm => $label) {
            $periodMonth = $prefix . $mm;
            $forecast = $forecastsByMonth[$periodMonth] ?? null;

            $plannedMt = $forecast ? (float) $forecast->planned_mt : 0.0;
            $stuffedMt = (float) ($stuffingAgg['by_month'][$periodMonth] ?? 0.0);
            $remainingMt = max(0.0, $plannedMt - $stuffedMt);
            $achievedPct = $plannedMt > 0 ? round(($stuffedMt / $plannedMt) * 100, 1) : 0.0;

            $plannedValue = $forecast ? (float) ($forecast->planned_value ?? 0.0) : 0.0;
            $stuffedValue = (float) ($pipeline['monthly_values'][$mm] ?? 0.0);
            $remainingValue = max(0.0, $plannedValue - $stuffedValue);
            $valueAchievedPct = $plannedValue > 0 ? round(($stuffedValue / $plannedValue) * 100, 1) : 0.0;

            $totalPlannedMt += $plannedMt;
            $totalStuffedMt += $stuffedMt;
            $totalPlannedValue += $plannedValue;
            $totalStuffedValue += $stuffedValue;

            $monthly[] = [
                'id'                 => $forecast ? (int) $forecast->id : null,
                'period_month'       => $periodMonth,
                'month_label'        => $label,
                'planned_mt'         => round($plannedMt, 3),
                'stuffed_mt'         => round($stuffedMt, 3),
                'remaining_mt'       => round($remainingMt, 3),
                'achieved_pct'       => $achievedPct,
                'planned_value'      => round($plannedValue, 2),
                'stuffed_value'      => round($stuffedValue, 2),
                'remaining_value'    => round($remainingValue, 2),
                'value_achieved_pct' => $valueAchievedPct,
                'notes'              => $forecast ? (string) ($forecast->notes ?? '') : '',
                'bc_count'           => $pipeline['monthly_totals'][$mm] ?? 0,
            ];
        }

        $overallPct = $totalPlannedMt > 0 ? round(($totalStuffedMt / $totalPlannedMt) * 100, 1) : 0.0;
        $overallValuePct = $totalPlannedValue > 0 ? round(($totalStuffedValue / $totalPlannedValue) * 100, 1) : 0.0;

        return [
            'year'              => $year,
            'monthly'           => $monthly,
            'pipeline'          => $pipeline,
            'contract_pipeline' => $contractPipeline,
            'kpis'              => [
                'total_planned_mt'    => round($totalPlannedMt, 2),
                'total_stuffed_mt'    => round($totalStuffedMt, 2),
                'overall_pct'         => $overallPct,
                'total_planned_value' => round($totalPlannedValue, 2),
                'total_stuffed_value' => round($totalStuffedValue, 2),
                'overall_value_pct'   => $overallValuePct,
                'total_bc'            => array_sum($pipeline['monthly_totals']),
            ],
        ];
    }

    /**
     * Build export pipeline progress per buyer contract and its Booking Confirmations (BC).
     */
    public function fetchContractPipeline(string $yearPrefix): array
    {
        $prefix = trim($yearPrefix);
        if ($prefix !== '' && substr($prefix, -1) !== '-') {
            $prefix .= '-';
        }

        $contracts = $this->loadContractsForPipeline();
        if ($contracts->isEmpty()) {
            return [];
        }

        $filteredContracts = $contracts->filter(function ($contract) use ($prefix) {
            if ($prefix === '') {
                return true;
            }

            $contractDate = trim((string) ($contract->Contract_Date ?? ''));
            $createdDate = trim((string) ($contract->Created_Date ?? ''));
            $dateRef = $contractDate !== '' ? $contractDate : $createdDate;

            if ($dateRef === '' || !preg_match('/^\d{4}-/', $dateRef)) {
                return true;
            }

            return strpos($dateRef, $prefix) === 0;
        })->values();

        if ($filteredContracts->isEmpty()) {
            return [];
        }

        $allBookings = collect();
        foreach ($filteredContracts as $contract) {
            $bookings = $contract->relationLoaded('bookings') ? $contract->bookings : collect();
            foreach ($bookings as $booking) {
                if (!$booking->relationLoaded('contract')) {
                    $booking->setRelation('contract', $contract);
                }
                $allBookings->push($booking);
            }
        }

        $progressByBookingId = $this->resolveProgressByBooking($allBookings);

        $rows = [];
        foreach ($filteredContracts as $contract) {
            $bookings = ($contract->relationLoaded('bookings') ? $contract->bookings : collect())->values();
            $bcCount = $bookings->count();

            if ($bcCount === 0) {
                $progressPct = (int) round((1 / 12) * 100);
                $bcs = [];
            } else {
                $weightPct = round(100 / $bcCount, 1);
                $bcProgressValues = [];
                $bcs = [];

                foreach ($bookings as $booking) {
                    $stages = $progressByBookingId[$booking->ID]['stages'] ?? [];
                    $summary = ExportChecklistController::summarizeStages($stages);
                    $bcPct = (int) ($summary['percent'] ?? 0);

                    $bcProgressValues[] = $bcPct;
                    $bcs[] = [
                        'booking_id'   => (int) $booking->ID,
                        'booking_no'   => (string) ($booking->Booking_No ?? '-'),
                        'label'        => (string) ($booking->Label ?? ''),
                        'weight_pct'   => $weightPct,
                        'progress_pct' => $bcPct,
                    ];
                }

                $progressPct = (int) round(array_sum($bcProgressValues) / $bcCount);
            }

            $status = (string) ($contract->Status ?? '');
            $statusLabel = method_exists($contract, 'statusLabel')
                ? (string) ($contract->statusLabel() ?? '')
                : $status;

            $rows[] = [
                'contract_id'     => (int) $contract->ID,
                'contract_no'     => (string) ($contract->Contract_No ?? ''),
                'buyer_name'      => trim((string) ($contract->Buyer_Name ?: 'Unknown Buyer')),
                'contract_qty_mt' => round((float) ($contract->Quantity_MT ?? 0), 3),
                'status'          => $status,
                'status_label'    => $statusLabel,
                'bc_count'        => $bcCount,
                'progress_pct'    => $progressPct,
                'bcs'             => $bcs,
            ];
        }

        return $rows;
    }

    protected function fetchMonthlyForecasts(string $yearPrefix): array
    {
        $forecastsByMonth = [];
        if (Schema::hasTable('cns_sales_forecasts')) {
            $rows = CnsSalesForecast::where('period_month', 'like', $yearPrefix . '%')->get();
            foreach ($rows as $row) {
                $forecastsByMonth[$row->period_month] = $row;
            }
        }

        return $forecastsByMonth;
    }

    protected function loadContractsForPipeline(): Collection
    {
        if (!Schema::hasTable('cns_buyer_contracts')) {
            return collect();
        }

        $query = BuyerContract::query()->orderByDesc('ID');
        if (Schema::hasTable('cns_buyer_contract_bookings')) {
            $query->with('bookings');
        }

        return $query->get();
    }

    protected function resolveProgressByBooking(Collection $bookings): array
    {
        if ($bookings->isEmpty()) {
            return [];
        }

        $checklistController = app(ExportChecklistController::class);
        if (get_class($checklistController) === ExportChecklistController::class && !$this->hasExportChecklistTables()) {
            return [];
        }

        return $checklistController->buildProgressData($bookings);
    }

    private function hasExportChecklistTables(): bool
    {
        $requiredTables = [
            'vessel_schedules',
            'vessel_schedule_dtls',
            'shipping_instructions',
            'packing_lists',
            'commercial_invoices',
            'weight_bridge_ticket',
            'sustainability_declarations',
            'delivery_notes',
            'annex_vii',
            'export_invoicing_requests',
        ];

        foreach ($requiredTables as $tbl) {
            if (!Schema::hasTable($tbl)) {
                return false;
            }
        }

        return true;
    }

    protected function fetchMonthlyStuffing(string $yearPrefix): array
    {
        if (!Schema::hasTable('vessel_schedules')) {
            return ['by_month' => [], 'by_booking' => []];
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
        $byMonth = [];
        $byBooking = [];

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

            if ($volumeMt > 0) {
                $byMonth[$monthKey] = ($byMonth[$monthKey] ?? 0.0) + $volumeMt;
            }

            $bookingId = !empty($vs->Booking_ID) ? (int) $vs->Booking_ID : null;
            if ($bookingId !== null) {
                if (!isset($byBooking[$bookingId])) {
                    $byBooking[$bookingId] = [
                        'month_key'  => $monthKey,
                        'stuffed_mt' => 0.0,
                    ];
                }
                $byBooking[$bookingId]['stuffed_mt'] += $volumeMt;
            }
        }

        return [
            'by_month'   => $byMonth,
            'by_booking' => $byBooking,
        ];
    }

    protected function fetchMonthlyBcPipeline(string $yearPrefix, array $stuffingByBooking): array
    {
        $monthlyTotals = array_fill_keys(array_keys(self::MONTH_LABELS), 0);
        $monthlyValues = array_fill_keys(array_keys(self::MONTH_LABELS), 0.0);

        if (!Schema::hasTable('cns_buyer_contract_bookings') || !Schema::hasTable('cns_buyer_contracts')) {
            return [
                'categories'     => array_values(self::MONTH_LABELS),
                'monthly_totals' => $monthlyTotals,
                'monthly_values' => $monthlyValues,
                'breakdown_rows' => [],
            ];
        }

        $bookings = DB::table('cns_buyer_contract_bookings as b')
            ->join('cns_buyer_contracts as c', 'c.ID', '=', 'b.Contract_ID')
            ->select(
                'b.*',
                'c.Contract_No',
                'c.Buyer_Name',
                DB::raw('COALESCE(c.Quantity_MT, 0) as Contract_Qty_MT'),
                DB::raw('COALESCE(c.Price_Per_MT, 0) as Price_Per_MT')
            )
            ->get();

        $contractsByMonth = [];

        foreach ($bookings as $b) {
            $bookingId = (int) $b->ID;
            $stuffingInfo = $stuffingByBooking[$bookingId] ?? null;
            $monthKey = $stuffingInfo ? $stuffingInfo['month_key'] : (strlen((string) ($b->Created_Date ?? '')) >= 7 ? substr((string) $b->Created_Date, 0, 7) : '');

            if (strpos($monthKey, $yearPrefix) !== 0) {
                continue;
            }

            $mm = substr($monthKey, 5, 2);
            if (!isset($monthlyTotals[$mm])) {
                continue;
            }

            $stuffedMt = $stuffingInfo ? (float) $stuffingInfo['stuffed_mt'] : 0.0;
            $pricePerMt = (float) ($b->Price_Per_MT ?? 0.0);
            $stuffedValue = round($stuffedMt * $pricePerMt, 2);

            $paymentApproved = !empty($b->Payment_Approved);
            $financeVerified = !empty($b->Finance_Payment_Verified);
            $paymentStatus = $paymentApproved
                ? 'Approved'
                : ($financeVerified ? 'Finance Verified' : 'Unpaid');

            $cid = (int) $b->Contract_ID;
            $groupKey = $monthKey . '|' . $cid;

            if (!isset($contractsByMonth[$groupKey])) {
                $contractsByMonth[$groupKey] = [
                    'period_month'    => $monthKey,
                    'month_label'     => self::MONTH_LABELS[$mm],
                    'contract_id'     => $cid,
                    'contract_no'     => (string) $b->Contract_No,
                    'buyer_name'      => trim((string) ($b->Buyer_Name ?: 'Unknown Buyer')),
                    'contract_qty_mt' => round((float) ($b->Contract_Qty_MT ?? 0), 3),
                    'price_per_mt'    => round($pricePerMt, 2),
                    'stuffed_mt'      => 0.0,
                    'stuffed_value'   => 0.0,
                    'progress_pct'    => 0.0,
                    'bc_count'        => 0,
                    'bcs'             => [],
                ];
            }

            $contractsByMonth[$groupKey]['stuffed_mt'] += $stuffedMt;
            $contractsByMonth[$groupKey]['stuffed_value'] += $stuffedValue;
            $contractsByMonth[$groupKey]['bc_count']++;
            $contractsByMonth[$groupKey]['bcs'][] = [
                'booking_id'         => $bookingId,
                'booking_no'         => (string) ($b->Booking_No ?? '-'),
                'label'              => (string) ($b->Label ?? ''),
                'stuffed_mt'         => round($stuffedMt, 3),
                'stuffed_value'      => $stuffedValue,
                'checklist_verified' => !empty($b->Checklist_Verified),
                'payment_status'     => $paymentStatus,
            ];

            $monthlyTotals[$mm]++;
            $monthlyValues[$mm] += $stuffedValue;
        }

        $breakdownRows = [];
        foreach ($contractsByMonth as $row) {
            $contractQty = $row['contract_qty_mt'];
            $row['stuffed_mt'] = round($row['stuffed_mt'], 3);
            $row['stuffed_value'] = round($row['stuffed_value'], 2);
            $row['progress_pct'] = $contractQty > 0
                ? round(($row['stuffed_mt'] / $contractQty) * 100, 1)
                : 0.0;
            $breakdownRows[] = $row;
        }

        usort($breakdownRows, function ($a, $b) {
            return strcmp($a['period_month'] . $a['contract_no'], $b['period_month'] . $b['contract_no']);
        });

        return [
            'categories'     => array_values(self::MONTH_LABELS),
            'monthly_totals' => $monthlyTotals,
            'monthly_values' => $monthlyValues,
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
