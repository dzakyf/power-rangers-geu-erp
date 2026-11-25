<?php

namespace App\Http\Controllers\CNS;

use App\BuyerContract;
use App\CnsProductAllocation;
use App\Http\Controllers\Controller;
use App\Support\DhtmlxGridPagination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductAllocationController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->extractFilters($request);
        $data = $this->buildDashboardData($filters);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($data);
        }

        return view('cns.sales.product-allocation', array_merge($data, [
            'filters'  => $filters,
            'statuses' => BuyerContract::STATUSES,
        ]));
    }

    public function dataAjax(Request $request)
    {
        $filters = $this->extractFilters($request);

        return response()->json($this->buildDashboardData($filters));
    }

    public function indexAllocationXml(Request $request)
    {
        $filters = $this->extractFilters($request);
        $data = $this->buildDashboardData($filters);
        $rows = $data['contracts'];

        $total = count($rows);
        $posStart = max(0, (int) $request->input('posStart', 0));
        $count = max(1, (int) $request->input('count', 25));
        $sliced = array_slice($rows, $posStart, $count);

        $bodyXml = '';
        $no = $posStart + 1;
        foreach ($sliced as $row) {
            $badges = '';
            foreach ($row['allocations'] as $alloc) {
                $jsonAttr = htmlspecialchars(json_encode($alloc), ENT_QUOTES, 'UTF-8');
                $badges .= '<span class="alloc-badge">'
                    . '<span><strong>' . htmlspecialchars($alloc['product_name']) . '</strong> (' . htmlspecialchars($alloc['period_month']) . '): ' . number_format($alloc['planned_mt'], 2) . ' MT</span>'
                    . '<button type="button" class="text-primary btn-edit-alloc" data-alloc="' . $jsonAttr . '" title="Edit">&#9998;</button>'
                    . '<button type="button" class="text-danger btn-delete-alloc" data-id="' . (int) $alloc['id'] . '" title="Hapus">&times;</button>'
                    . '</span>';
            }
            if ($badges === '') {
                $badges = '<span class="text-muted small">Belum ada alokasi</span>';
            }

            $bodyXml .= '<row id="' . (int) $row['id'] . '">';
            $bodyXml .= '<cell>' . ($no++) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars((string) $row['contract_no']) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars((string) $row['buyer_name']) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars((string) $row['status_label']) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars((string) $row['loading_period']) . '</cell>';
            $bodyXml .= '<cell>' . number_format($row['contract_mt'], 2) . '</cell>';
            $bodyXml .= '<cell>' . number_format($row['planned_mt'], 2) . '</cell>';
            $bodyXml .= '<cell>' . number_format($row['shipped_mt'], 2) . '</cell>';
            $bodyXml .= '<cell>' . number_format($row['remaining_mt'], 2) . '</cell>';
            $bodyXml .= '<cell>' . number_format($row['fulfillment_pct'], 1) . '%</cell>';
            $bodyXml .= '<cell><![CDATA[' . $badges . ']]></cell>';
            $bodyXml .= "</row>\n";
        }

        return DhtmlxGridPagination::response($total, $posStart, $bodyXml);
    }

    public function indexUpcomingXml(Request $request)
    {
        $filters = $this->extractFilters($request);
        $data = $this->buildDashboardData($filters);
        $rows = $data['upcoming_shipments'];

        $total = count($rows);
        $posStart = max(0, (int) $request->input('posStart', 0));
        $count = max(1, (int) $request->input('count', 25));
        $sliced = array_slice($rows, $posStart, $count);

        $bodyXml = '';
        $no = $posStart + 1;
        foreach ($sliced as $row) {
            $bodyXml .= '<row id="' . (int) $row['id'] . '">';
            $bodyXml .= '<cell>' . ($no++) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars((string) $row['contract_no']) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars((string) $row['buyer_name']) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars((string) $row['vessel_order']) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars((string) $row['etd_fmt']) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars((string) $row['pod']) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars((string) $row['incoterms']) . '</cell>';
            $bodyXml .= '<cell>' . number_format($row['volume_mt'], 2) . '</cell>';
            $bodyXml .= '<cell>' . (int) $row['containers'] . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars((string) $row['status']) . '</cell>';
            $bodyXml .= "</row>\n";
        }

        return DhtmlxGridPagination::response($total, $posStart, $bodyXml);
    }

    public function store(Request $request)
    {
        $validated = $this->validateAllocation($request);
        $user = $this->currentUserName();

        $allocation = CnsProductAllocation::create(array_merge($validated, [
            'created_by' => $user,
            'updated_by' => $user,
        ]));

        return response()->json([
            'message' => 'Product allocation & volume plan berhasil disimpan.',
            'data'    => $allocation,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $allocation = CnsProductAllocation::findOrFail($id);
        $validated = $this->validateAllocation($request);

        $allocation->update(array_merge($validated, [
            'updated_by' => $this->currentUserName(),
        ]));

        return response()->json([
            'message' => 'Product allocation & volume plan berhasil diperbarui.',
            'data'    => $allocation->fresh(),
        ]);
    }

    public function destroy($id)
    {
        $allocation = CnsProductAllocation::findOrFail($id);
        $allocation->delete();

        return response()->json([
            'message' => 'Product allocation & volume plan berhasil dihapus.',
        ]);
    }

    public function exportExcel(Request $request)
    {
        $tab = $request->input('tab', 'allocation');
        $filters = $this->extractFilters($request);
        $data = $this->buildDashboardData($filters);

        $filename = ($tab === 'upcoming' ? 'Upcoming_Export_Volume_' : 'Product_Allocation_Planning_') . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($tab, $data) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            if ($tab === 'upcoming') {
                fputcsv($handle, [
                    'No',
                    'Contract No',
                    'Buyer',
                    'Vessel / Order No',
                    'ETD',
                    'POD',
                    'Incoterm',
                    'Volume (MT)',
                    'Containers',
                    'Status',
                ]);

                foreach ($data['upcoming_shipments'] as $idx => $row) {
                    fputcsv($handle, [
                        $idx + 1,
                        $row['contract_no'],
                        $row['buyer_name'],
                        $row['vessel_order'],
                        $row['etd_fmt'],
                        $row['pod'],
                        $row['incoterms'],
                        number_format($row['volume_mt'], 3, '.', ''),
                        $row['containers'],
                        $row['status'],
                    ]);
                }
            } else {
                fputcsv($handle, [
                    'No',
                    'Contract No',
                    'Buyer',
                    'Status',
                    'Loading Period',
                    'Contract Qty (MT)',
                    'Planned Qty (MT)',
                    'Shipped Qty (MT)',
                    'Remaining Qty (MT)',
                    'Fulfillment (%)',
                    'Products Allocated',
                ]);

                foreach ($data['contracts'] as $idx => $row) {
                    $products = collect($row['allocations'])
                        ->map(function ($a) {
                            return $a['product_name'] . ' (' . $a['period_month'] . ': ' . number_format($a['planned_mt'], 2) . ' MT)';
                        })
                        ->implode('; ');

                    fputcsv($handle, [
                        $idx + 1,
                        $row['contract_no'],
                        $row['buyer_name'],
                        $row['status_label'],
                        $row['loading_period'],
                        number_format($row['contract_mt'], 3, '.', ''),
                        number_format($row['planned_mt'], 3, '.', ''),
                        number_format($row['shipped_mt'], 3, '.', ''),
                        number_format($row['remaining_mt'], 3, '.', ''),
                        number_format($row['fulfillment_pct'], 1, '.', ''),
                        $products ?: '-',
                    ]);
                }
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function extractFilters(Request $request): array
    {
        return [
            'status'  => (string) $request->input('status', 'all'),
            'buyer'   => (string) $request->input('buyer', 'all'),
            'period'  => (string) $request->input('period', ''),
            'product' => (string) $request->input('product', 'all'),
        ];
    }

    private function validateAllocation(Request $request): array
    {
        return $request->validate([
            'contract_id'        => 'required|integer|exists:cns_buyer_contracts,ID',
            'product_name'       => 'required|string|max:100',
            'period_month'       => ['required', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'planned_mt'         => 'required|numeric|min:0',
            'planned_containers' => 'nullable|integer|min:0',
            'notes'              => 'nullable|string|max:255',
        ], [
            'period_month.regex' => 'Periode harus berformat YYYY-MM (contoh: 2026-11).',
        ]);
    }

    private function buildDashboardData(array $filters): array
    {
        $contractsRaw = Schema::hasTable('cns_buyer_contracts')
            ? DB::table('cns_buyer_contracts')->orderBy('ID', 'DESC')->get()
            : collect();

        $allocationsByContract = [];
        $availableProducts = [];
        if (Schema::hasTable('cns_product_allocations')) {
            $allocRows = DB::table('cns_product_allocations')->orderBy('period_month', 'ASC')->get();
            foreach ($allocRows as $a) {
                $availableProducts[$a->product_name] = $a->product_name;
                $allocationsByContract[$a->contract_id][] = [
                    'id'                 => (int) $a->id,
                    'contract_id'        => (int) $a->contract_id,
                    'product_name'       => $a->product_name,
                    'period_month'       => $a->period_month,
                    'planned_mt'         => (float) $a->planned_mt,
                    'planned_containers' => (int) $a->planned_containers,
                    'notes'              => (string) ($a->notes ?? ''),
                ];
            }
        }

        $bookingsCountByContract = [];
        if (Schema::hasTable('cns_buyer_contract_bookings')) {
            $bookings = DB::table('cns_buyer_contract_bookings')
                ->groupBy('Contract_ID')
                ->select('Contract_ID', DB::raw('COUNT(*) as total'))
                ->get();
            foreach ($bookings as $b) {
                $bookingsCountByContract[$b->Contract_ID] = (int) $b->total;
            }
        }

        $vsdByOrder = $this->fetchVsdVolumesByOrder();
        $vesselSchedules = Schema::hasTable('vessel_schedules')
            ? DB::table('vessel_schedules')->orderBy('ETD', 'ASC')->get()
            : collect();

        $shippedByContractId = [];
        $shippedByContractNo = [];
        $upcomingShipments = [];
        $today = date('Y-m-d');

        $contractMapById = $contractsRaw->keyBy('ID');
        $contractMapByNo = $contractsRaw->keyBy('Contract_No');

        foreach ($vesselSchedules as $vs) {
            $statusUpper = strtoupper(trim((string) ($vs->Status ?? '')));
            if (in_array($statusUpper, ['CANCELED', 'CANCELLED'], true)) {
                continue;
            }

            $orderNo = (string) ($vs->Order_No ?? '');
            $vsd = $vsdByOrder[$orderNo] ?? null;
            $volumeMt = $vsd ? $vsd['netto_mt'] : $this->parseNumeric($vs->Qty ?? 0);
            $containers = $vsd ? $vsd['containers'] : (int) ($vs->Container_Count ?? 0);

            $isShipped = !empty($vs->ATD) || $statusUpper === 'COMPLETED';
            $cid = isset($vs->Contract_ID) ? (int) $vs->Contract_ID : 0;
            $cno = trim((string) ($vs->Contract_No ?? ''));

            if ($isShipped) {
                if ($cid > 0) {
                    $shippedByContractId[$cid] = ($shippedByContractId[$cid] ?? 0.0) + $volumeMt;
                } elseif ($cno !== '') {
                    $shippedByContractNo[$cno] = ($shippedByContractNo[$cno] ?? 0.0) + $volumeMt;
                }
            } else {
                $contract = $cid > 0 ? ($contractMapById[$cid] ?? null) : ($cno !== '' ? ($contractMapByNo[$cno] ?? null) : null);
                $etd = !empty($vs->ETD) ? (string) $vs->ETD : null;
                if ($etd && $etd < $today && $statusUpper === 'COMPLETED') {
                    continue;
                }

                $buyerName = $contract ? $contract->Buyer_Name : '-';
                if ($filters['buyer'] !== 'all' && $buyerName !== $filters['buyer']) {
                    continue;
                }
                if ($filters['period'] !== '' && $etd && substr($etd, 0, 7) !== $filters['period']) {
                    continue;
                }

                $upcomingShipments[] = [
                    'id'           => (int) $vs->id,
                    'contract_id'  => $contract ? (int) $contract->ID : $cid,
                    'contract_no'  => $contract ? $contract->Contract_No : ($cno ?: '-'),
                    'buyer_name'   => $buyerName,
                    'vessel_order' => trim(($vs->Vessel ?? '-') . ($orderNo ? " ({$orderNo})" : '')),
                    'etd'          => $etd ?: '-',
                    'etd_fmt'      => $etd ? date('d M Y', strtotime($etd)) : '-',
                    'pod'          => (string) ($vs->POD ?? '-'),
                    'incoterms'    => (string) ($vs->Incoterms ?? ($contract->Incoterms ?? 'FOB')),
                    'volume_mt'    => round($volumeMt, 3),
                    'containers'   => $containers,
                    'status'       => $statusUpper ?: 'SCHEDULED',
                ];
            }
        }

        $availableBuyers = [];
        $contractRows = [];
        $kpiContractMt = 0.0;
        $kpiPlannedMt = 0.0;
        $kpiShippedMt = 0.0;
        $kpiRemainingMt = 0.0;
        $unscheduledBacklogMt = 0.0;

        foreach ($contractsRaw as $c) {
            $availableBuyers[$c->Buyer_Name] = $c->Buyer_Name;

            if ($filters['status'] !== 'all' && $c->Status !== $filters['status']) {
                continue;
            }
            if ($filters['buyer'] !== 'all' && $c->Buyer_Name !== $filters['buyer']) {
                continue;
            }

            $allocs = $allocationsByContract[$c->ID] ?? [];
            if ($filters['period'] !== '') {
                $allocs = array_values(array_filter($allocs, function ($a) use ($filters) {
                    return $a['period_month'] === $filters['period'];
                }));
            }
            if ($filters['product'] !== 'all') {
                $allocs = array_values(array_filter($allocs, function ($a) use ($filters) {
                    return $a['product_name'] === $filters['product'];
                }));
                if (empty($allocs)) {
                    continue;
                }
            }

            $contractMt = (float) $c->Quantity_MT;
            $plannedMt = array_sum(array_column($allocs, 'planned_mt'));
            $plannedContainers = array_sum(array_column($allocs, 'planned_containers'));
            $shippedMt = ($shippedByContractId[$c->ID] ?? 0.0) + ($shippedByContractNo[$c->Contract_No] ?? 0.0);
            $remainingMt = max(0.0, $contractMt - $shippedMt);
            $fulfillmentPct = $contractMt > 0 ? min(100.0, ($shippedMt / $contractMt) * 100.0) : 0.0;

            $kpiContractMt += $contractMt;
            $kpiPlannedMt += $plannedMt;
            $kpiShippedMt += $shippedMt;
            $kpiRemainingMt += $remainingMt;

            if ($c->Status === 'ACTIVE_IN_PROGRESS' && $remainingMt > 0) {
                $unscheduledBacklogMt += $remainingMt;
            }

            $loadingPeriod = ($c->Loading_Start && $c->Loading_End)
                ? date('d M Y', strtotime($c->Loading_Start)) . ' - ' . date('d M Y', strtotime($c->Loading_End))
                : '-';

            $contractRows[] = [
                'id'                 => (int) $c->ID,
                'contract_no'        => $c->Contract_No,
                'buyer_name'         => $c->Buyer_Name,
                'status'             => $c->Status,
                'status_label'       => BuyerContract::STATUSES[$c->Status] ?? $c->Status,
                'loading_period'     => $loadingPeriod,
                'contract_mt'        => round($contractMt, 3),
                'contract_containers'=> (int) $c->Container_Count . ' x ' . $c->Container_Size,
                'planned_mt'         => round($plannedMt, 3),
                'planned_containers' => $plannedContainers,
                'shipped_mt'         => round($shippedMt, 3),
                'remaining_mt'       => round($remainingMt, 3),
                'fulfillment_pct'    => round($fulfillmentPct, 1),
                'bookings_count'     => $bookingsCountByContract[$c->ID] ?? 0,
                'allocations'        => $allocs,
            ];
        }

        asort($availableBuyers);
        asort($availableProducts);

        $upcomingMt = array_sum(array_column($upcomingShipments, 'volume_mt'));
        $upcomingContainers = array_sum(array_column($upcomingShipments, 'containers'));

        return [
            'contracts'          => $contractRows,
            'upcoming_shipments' => $upcomingShipments,
            'contract_options'   => $contractsRaw->map(function ($c) {
                return [
                    'id'          => (int) $c->ID,
                    'contract_no' => $c->Contract_No,
                    'buyer_name'  => $c->Buyer_Name,
                    'quantity_mt' => (float) $c->Quantity_MT,
                ];
            })->values()->all(),
            'filter_options'     => [
                'buyers'   => array_values($availableBuyers),
                'products' => array_values($availableProducts),
            ],
            'kpis'               => [
                'total_contract_mt'      => round($kpiContractMt, 2),
                'total_planned_mt'       => round($kpiPlannedMt, 2),
                'total_shipped_mt'       => round($kpiShippedMt, 2),
                'total_remaining_mt'     => round($kpiRemainingMt, 2),
                'upcoming_mt'            => round($upcomingMt, 2),
                'upcoming_containers'    => $upcomingContainers,
                'upcoming_count'         => count($upcomingShipments),
                'unscheduled_backlog_mt' => round($unscheduledBacklogMt, 2),
            ],
        ];
    }

    private function fetchVsdVolumesByOrder(): array
    {
        if (!Schema::hasTable('vessel_schedule_dtls')) {
            return [];
        }

        $rows = DB::table('vessel_schedule_dtls')
            ->where('Status', '!=', 'CANCELED')
            ->whereNotNull('Order_No')
            ->where('Order_No', '!=', '')
            ->groupBy('Order_No')
            ->select(
                'Order_No',
                DB::raw('COALESCE(SUM(Netto_MT), 0) as total_netto_mt'),
                DB::raw('COUNT(DISTINCT Container) as total_containers')
            )
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $map[$row->Order_No] = [
                'netto_mt'   => (float) $row->total_netto_mt,
                'containers' => (int) $row->total_containers,
            ];
        }

        return $map;
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
