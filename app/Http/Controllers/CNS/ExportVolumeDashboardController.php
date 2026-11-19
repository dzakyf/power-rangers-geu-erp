<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use App\ShippingInstruction;
use App\VesselScheduleDtl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExportVolumeDashboardController extends Controller
{
    /**
     * Display the Export Volume Dashboard.
     */
    public function index(Request $request)
    {
        $currentYear = (int) date('Y');
        $currentMonth = (string) (int) date('n');
        $selectedYear = $request->input('year', (string) $currentYear);
        $selectedMonth = $request->input('month', $currentMonth);
        $selectedConsignee = $request->input('consignee', 'all');
        $selectedCountry = $request->input('country', 'all');
        $selectedIncoterm = $request->input('incoterm', 'all');

        $data = $this->fetchDashboardData(
            $selectedYear,
            $selectedMonth,
            $selectedConsignee,
            $selectedCountry,
            $selectedIncoterm
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($data);
        }

        return view('cns.dashboard.export-volume', array_merge($data, [
            'selectedYear'      => $selectedYear,
            'selectedMonth'     => $selectedMonth,
            'selectedConsignee' => $selectedConsignee,
            'selectedCountry'   => $selectedCountry,
            'selectedIncoterm'  => $selectedIncoterm,
        ]));
    }

    /**
     * AJAX endpoint for live filter refreshing.
     */
    public function dataAjax(Request $request)
    {
        $currentYear = (int) date('Y');
        $currentMonth = (string) (int) date('n');
        $selectedYear = $request->input('year', (string) $currentYear);
        $selectedMonth = $request->input('month', $currentMonth);
        $selectedConsignee = $request->input('consignee', 'all');
        $selectedCountry = $request->input('country', 'all');
        $selectedIncoterm = $request->input('incoterm', 'all');

        $data = $this->fetchDashboardData(
            $selectedYear,
            $selectedMonth,
            $selectedConsignee,
            $selectedCountry,
            $selectedIncoterm
        );

        return response()->json($data);
    }

    /**
     * Export filtered shipment data to CSV (Excel compatible).
     */
    public function exportExcel(Request $request)
    {
        $currentYear = (int) date('Y');
        $currentMonth = (string) (int) date('n');
        $selectedYear = $request->input('year', (string) $currentYear);
        $selectedMonth = $request->input('month', $currentMonth);
        $selectedConsignee = $request->input('consignee', 'all');
        $selectedCountry = $request->input('country', 'all');
        $selectedIncoterm = $request->input('incoterm', 'all');

        $data = $this->fetchDashboardData(
            $selectedYear,
            $selectedMonth,
            $selectedConsignee,
            $selectedCountry,
            $selectedIncoterm
        );

        $filename = 'Export_Volume_' . ($selectedYear !== 'all' ? $selectedYear : 'AllYears') . '_' . date('Ymd_His') . '.csv';

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
            ]);

            foreach ($data['shipments'] as $idx => $shipment) {
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
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Core aggregation query and business logic.
     */
    private function fetchDashboardData(
        string $selectedYear,
        string $selectedMonth,
        string $selectedConsignee,
        string $selectedCountry,
        string $selectedIncoterm
    ): array {
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

        // 3. Fetch base shipments joined with vessel schedule for ATD/ETD fallback
        $rows = collect([]);
        try {
            $query = DB::table("{$siTable} as si")
                ->whereIn('si.Status', ['DRAFT', 'CREATED', 'APPROVED', 'FINAL', 'COMPLETED'])
                ->leftJoin('vessel_schedules as vs', function ($join) {
                    $join->on('vs.Order_No', '=', 'si.Order_No')
                        ->whereNotNull('si.Order_No');
                })
                ->select(
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
                    'vs.ETD as vs_etd',
                    'vs.ATD as vs_atd',
                    'vs.POD as vs_pod',
                    'vs.Incoterms as vs_incoterms'
                )
                ->orderBy('si.id', 'DESC');

            $rows = $query->get();
        } catch (\Throwable $e) {
            $rows = collect([]);
        }

        // 4. Normalize records and compile filter options
        $allShipments = [];
        $availableYears = [];
        $availableConsignees = [];
        $availableCountries = [];
        $availableIncoterms = [];

        foreach ($rows as $row) {
            // Effective departure date: ATD > si.ETD > vs.ETD > SI_Date
            $effectiveDate = $row->vs_atd ?: ($row->si_etd ?: ($row->vs_etd ?: $row->SI_Date));
            $timestamp = $effectiveDate ? strtotime($effectiveDate) : null;
            $year = $timestamp ? (int) date('Y', $timestamp) : 0;
            $month = $timestamp ? (int) date('n', $timestamp) : 0;

            if ($year > 2000) {
                $availableYears[$year] = $year;
            }

            // Consignee
            $consigneeCode = trim((string) $row->Consignee);
            $consigneeName = $consigneeMap[$consigneeCode] ?? ($consigneeCode ?: 'Unknown Consignee');
            if ($consigneeName !== 'Unknown Consignee') {
                $availableConsignees[$consigneeCode ?: $consigneeName] = $consigneeName;
            }

            // Destination Country / Port of Discharge
            $destination = trim((string) ($row->si_pod ?: ($row->Destination_Port ?: ($row->vs_pod ?: ''))));
            if ($destination === '') {
                $destination = 'Unknown Port/Country';
            }
            if ($destination !== 'Unknown Port/Country') {
                $availableCountries[$destination] = $destination;
            }

            // Incoterm
            $incoterm = strtoupper(trim((string) ($row->Delivery_Terms ?: ($row->vs_incoterms ?: 'FOB'))));
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

            $allShipments[] = [
                'id'             => $row->id,
                'si_no'          => $row->SI_No,
                'final_si_no'    => $row->Final_SI_No,
                'bl_no'          => $row->BL_No,
                'export_date'    => $effectiveDate ? date('Y-m-d', $timestamp) : '-',
                'export_date_fmt'=> $effectiveDate ? date('d M Y', $timestamp) : '-',
                'year'           => $year,
                'month'          => $month,
                'consignee_code' => $consigneeCode,
                'consignee_name' => $consigneeName,
                'destination'    => $destination,
                'incoterm'       => $incoterm,
                'volume_mt'      => $volumeMt,
                'containers'     => $containers,
                'status'         => $row->Status,
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

        // 5. Apply user filters
        $filteredShipments = array_filter($allShipments, function ($item) use (
            $selectedYear,
            $selectedMonth,
            $selectedConsignee,
            $selectedCountry,
            $selectedIncoterm
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
            return true;
        });

        // 6. Compute KPI cards
        $totalVolumeMt = 0.0;
        $totalContainers = 0;
        $uniqueConsignees = [];

        foreach ($filteredShipments as $s) {
            $totalVolumeMt += $s['volume_mt'];
            $totalContainers += $s['containers'];
            $uniqueConsignees[$s['consignee_name']] = true;
        }

        $kpis = [
            'total_volume_mt'  => round($totalVolumeMt, 2),
            'total_containers' => $totalContainers,
            'total_shipments'  => count($filteredShipments),
            'total_consignees' => count($uniqueConsignees),
        ];

        // 7. Aggregate Monthly Trend (12 Months for selected year)
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $monthlyVolume = array_fill(1, 12, 0.0);
        $monthlyContainers = array_fill(1, 12, 0);

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

            $m = (int) $s['month'];
            if ($m >= 1 && $m <= 12) {
                $monthlyVolume[$m] += $s['volume_mt'];
                $monthlyContainers[$m] += $s['containers'];
            }
        }

        $monthlyTrend = [
            'categories' => $monthNames,
            'volume'     => array_map(function ($val) { return round($val, 2); }, array_values($monthlyVolume)),
            'containers' => array_values($monthlyContainers),
        ];

        // 8. Aggregate Incoterms (Donut Chart)
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

        // 9. Aggregate Top Consignees by Volume (Horizontal Bar)
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

        // 10. Aggregate Country / Destination Breakdown (Bar Chart)
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

        return [
            'kpis'                => $kpis,
            'monthlyTrend'        => $monthlyTrend,
            'incotermChart'       => $incotermChart,
            'topConsigneesChart'  => $topConsigneesChart,
            'countryChart'        => $countryChart,
            'shipments'           => array_values($filteredShipments),
            'availableYears'      => $availableYears,
            'availableConsignees' => $availableConsignees,
            'availableCountries'  => $availableCountries,
            'availableIncoterms'  => $availableIncoterms,
        ];
    }
}
