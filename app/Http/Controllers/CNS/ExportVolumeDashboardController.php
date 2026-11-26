<?php

namespace App\Http\Controllers\CNS;

use App\BuyerContractBooking;
use App\CnsShipmentCostAllocation;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExportVolumeDashboardController extends Controller
{
    /**
     * Display the Export Performance Dashboard (SI01150).
     */
    public function index(Request $request)
    {
        $filters = $this->extractFilters($request);

        $data = $this->fetchDashboardData(
            $filters['year'],
            $filters['month'],
            $filters['consignee'],
            $filters['country'],
            $filters['incoterm'],
            $filters['status']
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($data);
        }

        return view('cns.dashboard.export-volume', array_merge($data, [
            'selectedYear'      => $filters['year'],
            'selectedMonth'     => $filters['month'],
            'selectedConsignee' => $filters['consignee'],
            'selectedCountry'   => $filters['country'],
            'selectedIncoterm'  => $filters['incoterm'],
            'selectedStatus'    => $filters['status'],
        ]));
    }

    /**
     * AJAX endpoint for live filter refreshing.
     */
    public function dataAjax(Request $request)
    {
        $filters = $this->extractFilters($request);

        $data = $this->fetchDashboardData(
            $filters['year'],
            $filters['month'],
            $filters['consignee'],
            $filters['country'],
            $filters['incoterm'],
            $filters['status']
        );

        return response()->json($data);
    }

    /**
     * Export filtered shipment data to CSV (Excel compatible).
     */
    public function exportExcel(Request $request)
    {
        $filters = $this->extractFilters($request);

        $data = $this->fetchDashboardData(
            $filters['year'],
            $filters['month'],
            $filters['consignee'],
            $filters['country'],
            $filters['incoterm'],
            $filters['status']
        );

        $filename = 'Export_Volume_' . ($filters['year'] !== 'all' ? $filters['year'] : 'AllYears') . '_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($data) {
            $handle = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'No',
                'No. SI',
                'No. Final SI',
                'No. B/L',
                'Tanggal Keberangkatan',
                'Consignee',
                'Negara / Pelabuhan Tujuan',
                'Incoterm',
                'Volume (MT)',
                'Kontainer (Box)',
                'Status',
                'Checklist',
                'Value (USD)',
                'Cost (USD)',
                'Margin (USD)',
                'Margin (%)',
            ]);

            foreach ($data['shipments'] as $idx => $shipment) {
                $checklistLabel = '-';
                if ($shipment['checklist_status'] === 'completed') {
                    $checklistLabel = 'Completed (' . $shipment['completed_stages'] . '/' . $shipment['total_stages'] . ')';
                } elseif ($shipment['checklist_status'] === 'in_progress') {
                    $checklistLabel = 'In Progress (' . $shipment['completed_stages'] . '/' . $shipment['total_stages'] . ')';
                }

                fputcsv($handle, [
                    $idx + 1,
                    $shipment['si_no'],
                    $shipment['final_si_no'] ?: '-',
                    $shipment['bl_no'] ?: '-',
                    $shipment['export_date'] ?: '-',
                    $shipment['consignee_name'],
                    $shipment['destination'],
                    $shipment['incoterm'],
                    number_format($shipment['volume_mt'], 3, '.', ''),
                    $shipment['containers'],
                    $shipment['status'],
                    $checklistLabel,
                    number_format($shipment['value_usd'], 2, '.', ''),
                    number_format($shipment['cost_usd'], 2, '.', ''),
                    number_format($shipment['margin_usd'], 2, '.', ''),
                    number_format($shipment['margin_pct'], 1, '.', ''),
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Extract and normalize dashboard filter inputs.
     */
    private function extractFilters(Request $request): array
    {
        $currentYear = (int) date('Y');
        $currentMonth = (string) (int) date('n');
        $status = (string) $request->input('status', 'all');
        if (!in_array($status, ['all', 'completed', 'in_progress'], true)) {
            $status = 'all';
        }

        return [
            'year'      => (string) $request->input('year', (string) $currentYear),
            'month'     => (string) $request->input('month', $currentMonth),
            'consignee' => (string) $request->input('consignee', 'all'),
            'country'   => (string) $request->input('country', 'all'),
            'incoterm'  => (string) $request->input('incoterm', 'all'),
            'status'    => $status,
        ];
    }

    /**
     * Fetch source records and build dashboard payload.
     */
    private function fetchDashboardData(
        string $selectedYear,
        string $selectedMonth,
        string $selectedConsignee,
        string $selectedCountry,
        string $selectedIncoterm,
        string $selectedStatus = 'all'
    ): array {
        $source = $this->loadSourceData();

        return $this->buildDashboardPayload(
            $source,
            $selectedYear,
            $selectedMonth,
            $selectedConsignee,
            $selectedCountry,
            $selectedIncoterm,
            $selectedStatus
        );
    }

    /**
     * Load raw shipment, stuffing, booking checklist, and cost allocation data from database.
     */
    protected function loadSourceData(): array
    {
        $siTable = 'shipping_instructions';
        try {
            $siTable = Schema::hasTable('shipping_instructions') ? 'shipping_instructions' : 'shipment_instructions';
        } catch (\Throwable $e) {
            $siTable = 'shipping_instructions';
        }

        // 1. Fetch container & MT sums per SI from vessel_schedule_dtls
        $vsdData = [];
        try {
            if (Schema::hasTable('vessel_schedule_dtls')) {
                $vsds = DB::table('vessel_schedule_dtls')
                    ->where('Status', '!=', 'CANCELED')
                    ->whereNotNull('SI_No')
                    ->where('SI_No', '!=', '')
                    ->groupBy('SI_No')
                    ->select(
                        'SI_No',
                        DB::raw('COALESCE(SUM(Netto_MT), 0) as total_netto_mt'),
                        DB::raw('COUNT(DISTINCT Container) as total_containers')
                    )
                    ->get();

                foreach ($vsds as $v) {
                    $vsdData[$v->SI_No] = [
                        'netto_mt'   => (float) $v->total_netto_mt,
                        'containers' => (int) $v->total_containers,
                    ];
                }
            }
        } catch (\Throwable $e) {
            $vsdData = [];
        }

        // 2. Fetch master consignees from supplier table
        $consigneeMap = [];
        try {
            if (Schema::hasTable('supplier')) {
                $suppliers = DB::table('supplier')
                    ->select('Supplier_ID', 'Company_Name', 'ID')
                    ->get();
                foreach ($suppliers as $s) {
                    if ($s->Supplier_ID) {
                        $consigneeMap[$s->Supplier_ID] = $s->Company_Name;
                    }
                    $consigneeMap[(string) $s->ID] = $s->Company_Name;
                }
            }
        } catch (\Throwable $e) {
            $consigneeMap = [];
        }

        // 3. Fetch base shipments joined with vessel schedule for ATD/ETD & BC/Booking fallback
        $rows = collect([]);
        try {
            $hasVs = Schema::hasTable('vessel_schedules');
            $selects = [
                'si.id',
                'si.SI_No',
                'si.Final_SI_No',
                'si.Final_SI_Date',
                'si.SI_Date',
                'si.ETD as si_etd',
                'si.BL_No',
                'si.Consignee',
                'si.Destination_Port',
                'si.POD as si_pod',
                'si.Delivery_Terms',
                'si.Qty as si_qty',
                'si.Status',
                'si.Order_No as si_order_no',
            ];

            $query = DB::table("{$siTable} as si")
                ->whereIn('si.Status', ['DRAFT', 'CREATED', 'APPROVED', 'FINAL', 'COMPLETED']);

            if ($hasVs) {
                $query->leftJoin('vessel_schedules as vs', function ($join) {
                    $join->on('vs.Order_No', '=', 'si.Order_No')
                        ->whereNotNull('si.Order_No');
                });

                $selects = array_merge($selects, [
                    'vs.ETD as vs_etd',
                    'vs.ATD as vs_atd',
                    'vs.POD as vs_pod',
                    'vs.Incoterms as vs_incoterms',
                    Schema::hasColumn('vessel_schedules', 'Booking_No')
                        ? 'vs.Booking_No as vs_booking_no'
                        : DB::raw('NULL as vs_booking_no'),
                    Schema::hasColumn('vessel_schedules', 'BC_No')
                        ? 'vs.BC_No as vs_bc_no'
                        : DB::raw('NULL as vs_bc_no'),
                    Schema::hasColumn('vessel_schedules', 'Booking_ID')
                        ? 'vs.Booking_ID as vs_booking_id'
                        : DB::raw('NULL as vs_booking_id'),
                ]);
            } else {
                $selects = array_merge($selects, [
                    DB::raw('NULL as vs_etd'),
                    DB::raw('NULL as vs_atd'),
                    DB::raw('NULL as vs_pod'),
                    DB::raw('NULL as vs_incoterms'),
                    DB::raw('NULL as vs_booking_no'),
                    DB::raw('NULL as vs_bc_no'),
                    DB::raw('NULL as vs_booking_id'),
                ]);
            }

            $rows = $query->select($selects)
                ->orderBy('si.id', 'DESC')
                ->get();
        } catch (\Throwable $e) {
            $rows = collect([]);
        }

        // 4. Collect booking & SI identifiers for batch checklist + cost allocation lookup
        $bookingNos = [];
        $bookingIds = [];
        $siIds = [];
        foreach ($rows as $row) {
            if (!empty($row->id)) {
                $siIds[] = (int) $row->id;
            }
            foreach (['vs_booking_no', 'vs_bc_no', 'si_order_no'] as $field) {
                $val = isset($row->{$field}) ? trim((string) $row->{$field}) : '';
                if ($val !== '') {
                    $bookingNos[] = $val;
                }
            }
            if (!empty($row->vs_booking_id)) {
                $bookingIds[] = (int) $row->vs_booking_id;
            }
        }
        $bookingNos = array_values(array_unique($bookingNos));
        $bookingIds = array_values(array_unique($bookingIds));
        $siIds = array_values(array_unique($siIds));

        $bookings = collect();
        try {
            if ((!empty($bookingNos) || !empty($bookingIds)) && Schema::hasTable('cns_buyer_contract_bookings')) {
                $bookings = BuyerContractBooking::with('contract')
                    ->where(function ($q) use ($bookingNos, $bookingIds) {
                        if (!empty($bookingNos)) {
                            $q->whereIn('Booking_No', $bookingNos);
                        }
                        if (!empty($bookingIds)) {
                            !empty($bookingNos)
                                ? $q->orWhereIn('ID', $bookingIds)
                                : $q->whereIn('ID', $bookingIds);
                        }
                    })
                    ->get();
            }
        } catch (\Throwable $e) {
            $bookings = collect();
        }

        $progressByBookingId = [];
        try {
            if ($bookings->isNotEmpty()) {
                $progressByBookingId = app(ExportChecklistController::class)->buildProgressData($bookings);
            }
        } catch (\Throwable $e) {
            $progressByBookingId = [];
        }

        $allocBcNos = array_values(array_unique(array_filter(array_merge(
            $bookingNos,
            $bookings->pluck('Booking_No')->all()
        ))));

        $costAllocations = collect();
        try {
            if ((!empty($allocBcNos) || !empty($siIds)) && Schema::hasTable('cns_shipment_cost_allocations')) {
                $hasBcCol = Schema::hasColumn('cns_shipment_cost_allocations', 'bc_no');
                if ($hasBcCol || !empty($siIds)) {
                    $costAllocations = CnsShipmentCostAllocation::query()
                        ->where(function ($q) use ($allocBcNos, $siIds, $hasBcCol) {
                            $hasWhere = false;
                            if ($hasBcCol && !empty($allocBcNos)) {
                                $q->whereIn('bc_no', $allocBcNos);
                                $hasWhere = true;
                            }
                            if (!empty($siIds)) {
                                $hasWhere ? $q->orWhereIn('si_id', $siIds) : $q->whereIn('si_id', $siIds);
                            }
                        })
                        ->get($hasBcCol ? ['id', 'bc_no', 'si_id', 'total_cost_usd'] : ['id', 'si_id', 'total_cost_usd']);
                }
            }
        } catch (\Throwable $e) {
            $costAllocations = collect();
        }

        return [
            'rows'                => $rows,
            'vsdData'             => $vsdData,
            'consigneeMap'        => $consigneeMap,
            'bookings'            => $bookings,
            'progressByBookingId' => $progressByBookingId,
            'costAllocations'     => $costAllocations,
        ];
    }

    /**
     * Core aggregation and business logic for Export Performance Dashboard.
     */
    public function buildDashboardPayload(
        array $source,
        string $selectedYear = 'all',
        string $selectedMonth = 'all',
        string $selectedConsignee = 'all',
        string $selectedCountry = 'all',
        string $selectedIncoterm = 'all',
        string $selectedStatus = 'all'
    ): array {
        $selectedStatus = in_array($selectedStatus, ['all', 'completed', 'in_progress'], true)
            ? $selectedStatus
            : 'all';

        $rows = $source['rows'] ?? collect([]);
        $vsdData = $source['vsdData'] ?? [];
        $consigneeMap = $source['consigneeMap'] ?? [];
        $bookings = $source['bookings'] ?? collect([]);
        $progressByBookingId = $source['progressByBookingId'] ?? [];
        $costAllocations = $source['costAllocations'] ?? collect([]);

        // Index bookings by ID and Booking_No
        $bookingsById = [];
        $bookingsByNo = [];
        foreach ($bookings as $booking) {
            if (!empty($booking->ID)) {
                $bookingsById[(int) $booking->ID] = $booking;
            }
            $bNo = trim((string) ($booking->Booking_No ?? ''));
            if ($bNo !== '') {
                $bookingsByNo[$bNo] = $booking;
            }
        }

        // Index cost allocations by bc_no and si_id
        [$allocByBc, $allocBySiId] = $this->indexCostAllocations($costAllocations);

        // Normalize records and compile filter options
        $allShipments = [];
        $availableYears = [];
        $availableConsignees = [];
        $availableCountries = [];
        $availableIncoterms = [];

        foreach ($rows as $row) {
            // Effective departure date: ATD > si.ETD > vs.ETD > SI_Date
            $effectiveDate = ($row->vs_atd ?? null) ?: (($row->si_etd ?? null) ?: (($row->vs_etd ?? null) ?: ($row->SI_Date ?? null)));
            $timestamp = $effectiveDate ? strtotime($effectiveDate) : null;
            $year = $timestamp ? (int) date('Y', $timestamp) : 0;
            $month = $timestamp ? (int) date('n', $timestamp) : 0;

            if ($year > 2000) {
                $availableYears[$year] = $year;
            }

            // Resolve matching Booking Confirmation & Contract
            $booking = $this->resolveBookingForRow($row, $bookingsById, $bookingsByNo);
            $contract = $booking ? $booking->contract : null;
            $bcNo = $this->resolveBcNo($row, $booking);

            // Checklist progress
            $completedStages = 0;
            $totalStages = 0;
            $checklistStatus = '-';
            if ($booking) {
                $stages = $progressByBookingId[$booking->ID]['stages'] ?? [];
                $summary = ExportChecklistController::summarizeStages($stages);
                $completedStages = $summary['completed'];
                $totalStages = $summary['total'];
                $checklistStatus = ($totalStages > 0 && $completedStages === $totalStages)
                    ? 'completed'
                    : 'in_progress';
            }

            // Consignee
            $consigneeCode = trim((string) ($row->Consignee ?? ''));
            $consigneeName = $consigneeMap[$consigneeCode] ?? ($consigneeCode ?: 'Unknown Consignee');
            if ($consigneeName === 'Unknown Consignee' && $contract && !empty(trim((string) ($contract->Buyer_Name ?? '')))) {
                $consigneeName = trim((string) $contract->Buyer_Name);
            }
            if ($consigneeName !== 'Unknown Consignee') {
                $availableConsignees[$consigneeCode ?: $consigneeName] = $consigneeName;
            }

            // Destination Country / Port of Discharge
            $destination = trim((string) (($row->si_pod ?? null) ?: (($row->Destination_Port ?? null) ?: (($row->vs_pod ?? null) ?: ''))));
            if ($destination === '') {
                $destination = 'Unknown Port/Country';
            }
            if ($destination !== 'Unknown Port/Country') {
                $availableCountries[$destination] = $destination;
            }

            // Incoterm
            $incoterm = strtoupper(trim((string) (($row->Delivery_Terms ?? null) ?: (($row->vs_incoterms ?? null) ?: 'FOB'))));
            if ($incoterm === '') {
                $incoterm = 'FOB';
            }
            $availableIncoterms[$incoterm] = $incoterm;

            // Volume (MT) and Containers
            $volumeMt = 0.0;
            $containers = 0;
            if (isset($vsdData[$row->SI_No])) {
                $volumeMt = (float) $vsdData[$row->SI_No]['netto_mt'];
                $containers = (int) $vsdData[$row->SI_No]['containers'];
            }

            // Fallback volume from Qty if Netto_MT was not found in vessel_schedule_dtls
            if ($volumeMt <= 0.0 && !empty($row->si_qty)) {
                $cleaned = preg_replace('/[^0-9.]/', '', (string) $row->si_qty);
                $volumeMt = (float) $cleaned;
            }

            // Value, Cost & Margin (USD)
            $pricePerMt = (float) ($contract->Price_Per_MT ?? 0);
            $valueUsd = round($volumeMt * $pricePerMt, 2);
            $costUsd = $this->resolveCostUsd($bcNo, (int) ($row->id ?? 0), $allocByBc, $allocBySiId);
            $marginUsd = round($valueUsd - $costUsd, 2);
            $marginPct = $valueUsd > 0 ? round(($marginUsd / $valueUsd) * 100, 1) : 0.0;

            $allShipments[] = [
                'id'               => $row->id,
                'si_no'            => $row->SI_No,
                'final_si_no'      => $row->Final_SI_No ?? null,
                'bl_no'            => $row->BL_No ?? null,
                'bc_no'            => $bcNo !== '' ? $bcNo : '-',
                'contract_no'      => ($contract && !empty($contract->Contract_No)) ? (string) $contract->Contract_No : '-',
                'export_date'      => $effectiveDate ? date('Y-m-d', $timestamp) : '-',
                'export_date_fmt'  => $effectiveDate ? date('d M Y', $timestamp) : '-',
                'year'             => $year,
                'month'            => $month,
                'consignee_code'   => $consigneeCode,
                'consignee_name'   => $consigneeName,
                'destination'      => $destination,
                'incoterm'         => $incoterm,
                'volume_mt'        => $volumeMt,
                'containers'       => $containers,
                'status'           => $row->Status,
                'completed_stages' => $completedStages,
                'total_stages'     => $totalStages,
                'checklist_status' => $checklistStatus,
                'price_per_mt'     => round($pricePerMt, 2),
                'value_usd'        => $valueUsd,
                'cost_usd'         => $costUsd,
                'margin_usd'       => $marginUsd,
                'margin_pct'       => $marginPct,
            ];
        }

        // Sort filter choices
        krsort($availableYears);
        if (empty($availableYears)) {
            $availableYears[(int) date('Y')] = (int) date('Y');
        }
        asort($availableConsignees);
        asort($availableCountries);
        ksort($availableIncoterms);

        // Apply user filters
        $filteredShipments = array_filter($allShipments, function ($item) use (
            $selectedYear,
            $selectedMonth,
            $selectedConsignee,
            $selectedCountry,
            $selectedIncoterm,
            $selectedStatus
        ) {
            if ($selectedYear !== 'all' && (string) $item['year'] !== (string) $selectedYear) {
                return false;
            }
            if ($selectedMonth !== 'all' && (string) $item['month'] !== (string) $selectedMonth) {
                return false;
            }
            if ($selectedConsignee !== 'all') {
                if ($item['consignee_code'] !== $selectedConsignee && $item['consignee_name'] !== $selectedConsignee) {
                    return false;
                }
            }
            if ($selectedCountry !== 'all' && strcasecmp($item['destination'], $selectedCountry) !== 0) {
                return false;
            }
            if ($selectedIncoterm !== 'all' && strcasecmp($item['incoterm'], $selectedIncoterm) !== 0) {
                return false;
            }
            if ($selectedStatus !== 'all' && $item['checklist_status'] !== $selectedStatus) {
                return false;
            }

            return true;
        });

        // Compute KPI cards
        $totalVolumeMt = 0.0;
        $totalContainers = 0;
        $uniqueConsignees = [];
        $totalPending = 0;
        $totalValueUsd = 0.0;
        $totalCostUsd = 0.0;

        foreach ($filteredShipments as $s) {
            $totalVolumeMt += $s['volume_mt'];
            $totalContainers += $s['containers'];
            $uniqueConsignees[$s['consignee_name']] = true;
            $st = strtoupper((string) $s['status']);
            if ($st === 'DRAFT' || $st === 'CREATED') {
                $totalPending++;
            }
            $totalValueUsd += $s['value_usd'];
            $totalCostUsd += $s['cost_usd'];
        }

        $totalValueUsd = round($totalValueUsd, 2);
        $totalCostUsd = round($totalCostUsd, 2);
        $totalMarginUsd = round($totalValueUsd - $totalCostUsd, 2);
        $avgMarginPct = $totalValueUsd > 0 ? round(($totalMarginUsd / $totalValueUsd) * 100, 1) : 0.0;

        $kpis = [
            'total_volume_mt'  => round($totalVolumeMt, 2),
            'total_containers' => $totalContainers,
            'total_shipments'  => count($filteredShipments),
            'total_consignees' => count($uniqueConsignees),
            'total_pending'    => $totalPending,
            'total_value_usd'  => $totalValueUsd,
            'total_cost_usd'   => $totalCostUsd,
            'total_margin_usd' => $totalMarginUsd,
            'avg_margin_pct'   => $avgMarginPct,
        ];

        // Compute Today's Activity Stats
        $todayDate = date('Y-m-d');
        $todayShipments = 0;
        $todayContainers = 0;
        $todayVolumeMt = 0.0;
        $todayApproved = 0;

        foreach ($allShipments as $s) {
            if ($s['export_date'] === $todayDate) {
                $todayShipments++;
                $todayContainers += $s['containers'];
                $todayVolumeMt += $s['volume_mt'];
                if (in_array(strtoupper((string) $s['status']), ['APPROVED', 'FINAL', 'COMPLETED'], true)) {
                    $todayApproved++;
                }
            }
        }

        $todayStats = [
            'shipments'   => $todayShipments,
            'containers'  => $todayContainers,
            'volume_mt'   => round($todayVolumeMt, 2),
            'approved'    => $todayApproved,
            'last_update' => date('H:i:s'),
        ];

        // Aggregate Monthly Trend (12 Months for selected year)
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $monthlyVolume = array_fill(1, 12, 0.0);
        $monthlyContainers = array_fill(1, 12, 0);
        $monthlyValue = array_fill(1, 12, 0.0);
        $monthlyMargin = array_fill(1, 12, 0.0);

        foreach ($allShipments as $s) {
            if ($selectedYear !== 'all' && (string) $s['year'] !== (string) $selectedYear) {
                continue;
            }
            if ($selectedConsignee !== 'all') {
                if ($s['consignee_code'] !== $selectedConsignee && $s['consignee_name'] !== $selectedConsignee) {
                    continue;
                }
            }
            if ($selectedCountry !== 'all' && strcasecmp($s['destination'], $selectedCountry) !== 0) {
                continue;
            }
            if ($selectedIncoterm !== 'all' && strcasecmp($s['incoterm'], $selectedIncoterm) !== 0) {
                continue;
            }
            if ($selectedStatus !== 'all' && $s['checklist_status'] !== $selectedStatus) {
                continue;
            }

            $m = (int) $s['month'];
            if ($m >= 1 && $m <= 12) {
                $monthlyVolume[$m] += $s['volume_mt'];
                $monthlyContainers[$m] += $s['containers'];
                $monthlyValue[$m] += $s['value_usd'];
                $monthlyMargin[$m] += $s['margin_usd'];
            }
        }

        $monthlyTrend = [
            'categories' => $monthNames,
            'volume'     => array_map(function ($val) { return round($val, 2); }, array_values($monthlyVolume)),
            'containers' => array_values($monthlyContainers),
            'value_usd'  => array_map(function ($val) { return round($val, 2); }, array_values($monthlyValue)),
            'margin_usd' => array_map(function ($val) { return round($val, 2); }, array_values($monthlyMargin)),
        ];

        // Aggregate Incoterms (Donut Chart)
        $incotermTotals = [];
        foreach ($filteredShipments as $s) {
            $inco = $s['incoterm'];
            if (!isset($incotermTotals[$inco])) {
                $incotermTotals[$inco] = 0.0;
            }
            $incotermTotals[$inco] += $s['volume_mt'];
        }
        arsort($incotermTotals);

        $incotermChart = [
            'labels' => array_keys($incotermTotals),
            'series' => array_map(function ($v) { return round($v, 2); }, array_values($incotermTotals)),
        ];

        // Aggregate Top Consignees by Volume (Horizontal Bar)
        $consigneeTotals = [];
        foreach ($filteredShipments as $s) {
            $cName = $s['consignee_name'];
            if (!isset($consigneeTotals[$cName])) {
                $consigneeTotals[$cName] = 0.0;
            }
            $consigneeTotals[$cName] += $s['volume_mt'];
        }
        arsort($consigneeTotals);
        $topConsignees = array_slice($consigneeTotals, 0, 7, true);

        $topConsigneesChart = [
            'categories' => array_keys($topConsignees),
            'series'     => array_map(function ($v) { return round($v, 2); }, array_values($topConsignees)),
        ];

        // Aggregate Country / Destination Breakdown (Bar Chart)
        $countryTotals = [];
        foreach ($filteredShipments as $s) {
            $dest = $s['destination'];
            if (!isset($countryTotals[$dest])) {
                $countryTotals[$dest] = 0.0;
            }
            $countryTotals[$dest] += $s['volume_mt'];
        }
        arsort($countryTotals);
        $topCountries = array_slice($countryTotals, 0, 8, true);

        $countryChart = [
            'categories' => array_keys($topCountries),
            'series'     => array_map(function ($v) { return round($v, 2); }, array_values($topCountries)),
        ];

        // Aggregate Shipment Status Breakdown (Donut Chart)
        $statusTotals = [];
        foreach ($filteredShipments as $s) {
            $st = strtoupper((string) $s['status']) ?: 'DRAFT';
            if (!isset($statusTotals[$st])) {
                $statusTotals[$st] = 0;
            }
            $statusTotals[$st]++;
        }
        arsort($statusTotals);

        $statusChart = [
            'labels' => array_keys($statusTotals),
            'series' => array_values($statusTotals),
        ];

        return [
            'kpis'                => $kpis,
            'todayStats'          => $todayStats,
            'monthlyTrend'        => $monthlyTrend,
            'incotermChart'       => $incotermChart,
            'statusChart'         => $statusChart,
            'topConsigneesChart'  => $topConsigneesChart,
            'countryChart'        => $countryChart,
            'shipments'           => array_values($filteredShipments),
            'availableYears'      => $availableYears,
            'availableConsignees' => $availableConsignees,
            'availableCountries'  => $availableCountries,
            'availableIncoterms'  => $availableIncoterms,
        ];
    }

    private function resolveBookingForRow(object $row, array $bookingsById, array $bookingsByNo)
    {
        $vsBookingId = !empty($row->vs_booking_id) ? (int) $row->vs_booking_id : 0;
        if ($vsBookingId > 0 && isset($bookingsById[$vsBookingId])) {
            return $bookingsById[$vsBookingId];
        }

        foreach (['vs_booking_no', 'vs_bc_no', 'si_order_no'] as $field) {
            $key = isset($row->{$field}) ? trim((string) $row->{$field}) : '';
            if ($key !== '' && isset($bookingsByNo[$key])) {
                return $bookingsByNo[$key];
            }
        }

        return null;
    }

    private function resolveBcNo(object $row, $booking): string
    {
        if ($booking && !empty(trim((string) ($booking->Booking_No ?? '')))) {
            return trim((string) $booking->Booking_No);
        }

        foreach (['vs_booking_no', 'vs_bc_no', 'si_order_no'] as $field) {
            $val = isset($row->{$field}) ? trim((string) $row->{$field}) : '';
            if ($val !== '') {
                return $val;
            }
        }

        return '';
    }

    private function indexCostAllocations($costAllocations): array
    {
        $byBc = [];
        $bySiId = [];

        foreach ($costAllocations as $alloc) {
            $bcNo = trim((string) ($alloc->bc_no ?? ''));
            if ($bcNo !== '') {
                $byBc[$bcNo][] = $alloc;
            }
            $siId = (int) ($alloc->si_id ?? 0);
            if ($siId > 0) {
                $bySiId[$siId][] = $alloc;
            }
        }

        return [$byBc, $bySiId];
    }

    private function resolveCostUsd(string $bcNo, int $siId, array $allocByBc, array $allocBySiId): float
    {
        $matched = [];

        if ($bcNo !== '' && isset($allocByBc[$bcNo])) {
            foreach ($allocByBc[$bcNo] as $alloc) {
                $key = isset($alloc->id) ? 'id:' . $alloc->id : 'obj:' . spl_object_id($alloc);
                $matched[$key] = (float) ($alloc->total_cost_usd ?? 0);
            }
        }

        if ($siId > 0 && isset($allocBySiId[$siId])) {
            foreach ($allocBySiId[$siId] as $alloc) {
                $allocBc = trim((string) ($alloc->bc_no ?? ''));
                // Include allocations matching this BC or legacy allocations without bc_no
                if ($allocBc === '' || $bcNo === '' || $allocBc === $bcNo) {
                    $key = isset($alloc->id) ? 'id:' . $alloc->id : 'obj:' . spl_object_id($alloc);
                    $matched[$key] = (float) ($alloc->total_cost_usd ?? 0);
                }
            }
        }

        return round(array_sum($matched), 2);
    }
}
