<?php

namespace App\Http\Controllers\CNS;

use App\CNSDrainAdjustment;
use App\Http\Controllers\Controller;
use App\MasterTank;
use App\SalesOrderCNS;
use App\Services\CNS\CnsCodeGenerator;
use App\SludgeMovement;
use App\Support\DhtmlxGridPagination;
use App\TankDraining;
use App\UCOStoragePelindo;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TankDrainingController extends Controller
{
    public function index()
    {
        $tanks = MasterTank::active()->orderBy('name', 'asc')->get();
        [$sludgeStockRows, $sludgeTotals, $sludgeSources, $sludgeWarehouses] = $this->getSludgeStockSummary($tanks);
        return view('cns.inventory.tank-draining', compact('tanks', 'sludgeStockRows', 'sludgeTotals', 'sludgeSources', 'sludgeWarehouses'));
    }

    public function sludgeStockJson(Request $request)
    {
        $tanks = MasterTank::active()->orderBy('name', 'asc')->get();
        $filters = [
            'start_date' => $request->query('start_date'),
            'end_date'   => $request->query('end_date'),
            'tank_id'    => $request->query('tank_id'),
        ];
        [$rows, $totals, $sources, $warehouses] = $this->getSludgeStockSummary($tanks, $filters);

        return response()->json([
            'status'     => 'success',
            'data'       => $rows,
            'totals'     => $totals,
            'sources'    => $sources,
            'warehouses' => $warehouses,
        ]);
    }

    public function sludgeStockXml(Request $request)
    {
        $tanks = MasterTank::active()->orderBy('name', 'asc')->get();
        $filters = [
            'start_date' => $request->query('start_date'),
            'end_date'   => $request->query('end_date'),
            'tank_id'    => $request->query('tank_id'),
        ];
        [, , $sources] = $this->getSludgeStockSummary($tanks, $filters);

        $colKeyMap = [
            1 => 'draining_code',
            2 => 'draining_date',
            3 => 'tank_name',
            4 => 'tujuan_draining',
            5 => 'qty_sludge',
            6 => 'used_qty',
            7 => 'moved_qty',
            8 => 'sisa_qty',
        ];

        $headerFilters = $request->query('filter', []);
        if (is_array($headerFilters) && !empty($headerFilters)) {
            $sources = array_values(array_filter($sources, function ($src) use ($headerFilters, $colKeyMap) {
                foreach ($headerFilters as $colIdx => $rawValue) {
                    $term = trim((string) ($rawValue ?? ''));
                    $colIdx = (int) $colIdx;
                    if ($term === '' || !isset($colKeyMap[$colIdx])) {
                        continue;
                    }
                    $val = (string) ($src[$colKeyMap[$colIdx]] ?? '');
                    if (stripos($val, $term) === false) {
                        return false;
                    }
                }
                return true;
            }));
        }

        $paginateOptions = ['default_page_size' => 25];
        if ($request->filled('sort_col')) {
            $sortCol = (int) $request->query('sort_col');
            $sortDir = strtolower((string) $request->query('sort_dir', 'asc')) === 'desc' ? -1 : 1;
            if (isset($colKeyMap[$sortCol])) {
                $sortKey = $colKeyMap[$sortCol];
                $numericCols = [5, 6, 7, 8];
                $isNumeric = in_array($sortCol, $numericCols, true);
                $paginateOptions['sort_callback'] = function ($a, $b) use ($sortKey, $sortDir, $isNumeric) {
                    $va = $a[$sortKey] ?? '';
                    $vb = $b[$sortKey] ?? '';
                    $cmp = $isNumeric
                        ? ((float) $va <=> (float) $vb)
                        : strcasecmp((string) $va, (string) $vb);
                    return $cmp * $sortDir;
                };
            }
        }

        $paginated = DhtmlxGridPagination::paginateCollection($sources, $request, $paginateOptions);

        $bodyXml = '';
        $no = $paginated['posStart'] + 1;
        foreach ($paginated['rows'] as $src) {
            $bodyXml .= "<row id=\"{$src['draining_id']}\">\n";
            $bodyXml .= "<cell>{$no}</cell>\n";
            $bodyXml .= "<cell>" . htmlspecialchars($src['draining_code']) . "</cell>\n";
            $bodyXml .= "<cell>" . htmlspecialchars($src['draining_date']) . "</cell>\n";
            $bodyXml .= "<cell>" . htmlspecialchars($src['tank_name']) . "</cell>\n";
            $bodyXml .= "<cell>" . htmlspecialchars($src['tujuan_draining'] ?? '-') . "</cell>\n";
            $bodyXml .= "<cell>" . number_format((float) $src['qty_sludge'], 2, '.', ',') . " KG</cell>\n";
            $bodyXml .= "<cell>" . number_format((float) $src['used_qty'], 2, '.', ',') . " KG</cell>\n";
            $bodyXml .= "<cell>" . number_format((float) ($src['moved_qty'] ?? 0), 2, '.', ',') . " KG</cell>\n";
            $bodyXml .= "<cell>" . number_format((float) $src['sisa_qty'], 2, '.', ',') . " KG</cell>\n";
            $bodyXml .= "</row>\n";
            $no++;
        }

        return DhtmlxGridPagination::response($paginated['total'], $paginated['posStart'], $bodyXml);
    }

    public function storeSludgeMovement(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'draining_id'           => 'required|integer|min:1',
            'target_warehouse_id'   => 'required|integer|min:1',
            'target_warehouse_name' => 'required|string|max:150',
            'movement_date'         => 'required|date',
            'qty'                   => 'required|numeric|min:0.01',
            'notes'                 => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $qty = round((float) $request->qty, 2);

        return DB::transaction(function () use ($request, $qty) {
            $draining = TankDraining::where('id', (int) $request->draining_id)->lockForUpdate()->first();
            if (!$draining || (float) ($draining->qty_sludge ?? 0) <= 0) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Data draining sludge tidak ditemukan.',
                ], 404);
            }

            $available = $this->getAvailableTankSludgeQty($draining);
            if ($qty > $available) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Qty moving ({$qty} KG) melebihi sisa sludge draining ({$available} KG).",
                ], 422);
            }

            $movement = SludgeMovement::create([
                'movement_code'         => $this->generateNextSludgeMovementCode(true),
                'movement_date'         => $request->movement_date,
                'draining_id'           => $draining->id,
                'source_tank_id'        => $draining->tank_id,
                'source_tank_name'      => $draining->tank_name,
                'target_warehouse_id'   => (int) $request->target_warehouse_id,
                'target_warehouse_name' => trim((string) $request->target_warehouse_name),
                'qty'                   => $qty,
                'notes'                 => $request->notes,
                'created_by'            => Auth::check() ? Auth::user()->username : 'system',
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Moving sludge berhasil disimpan.',
                'data'    => $movement,
            ]);
        });
    }

    protected function generateNextSludgeMovementCode(bool $lock = false): string
    {
        return CnsCodeGenerator::nextSludgeMovementNo(null, $lock);
    }

    protected function getAvailableTankSludgeQty(TankDraining $draining): float
    {
        $usedSo = 0.0;
        try {
            if (Schema::hasTable('sales_order_cns') && Schema::hasColumn('sales_order_cns', 'draining_id')) {
                $q = DB::table('sales_order_cns as so')
                    ->join('sales_order_cns_dtl as dtl', 'dtl.so_no', '=', 'so.so_no')
                    ->where('so.draining_id', $draining->id)
                    ->where('so.so_type', 'SLUDGE')
                    ->whereRaw("UPPER(COALESCE(so.status, '')) != 'REJECTED'");
                if (Schema::hasColumn('sales_order_cns', 'sludge_movement_id')) {
                    $q->whereNull('so.sludge_movement_id');
                }
                $usedSo = (float) $q->sum('dtl.qty');
            }
        } catch (\Throwable $e) {
            $usedSo = 0.0;
        }

        $movedOut = 0.0;
        try {
            if (Schema::hasTable('cns_sludge_movements')) {
                $movedOut = (float) SludgeMovement::where('draining_id', $draining->id)->sum('qty');
            }
        } catch (\Throwable $e) {
            $movedOut = 0.0;
        }

        return max(0.0, round((float) $draining->qty_sludge - $usedSo - $movedOut, 2));
    }

    protected function getSludgeStockSummary($tanks = null, array $filters = []): array
    {
        $tanks = $tanks ?? MasterTank::active()->orderBy('name', 'asc')->get();
        $startDate = !empty($filters['start_date']) ? $filters['start_date'] : null;
        $endDate = !empty($filters['end_date']) ? $filters['end_date'] : null;
        $tankId = !empty($filters['tank_id']) ? (int) $filters['tank_id'] : null;

        if ($tankId) {
            $tanks = $tanks->where('id', $tankId)->values();
        }

        $drainings = collect();
        try {
            $q = TankDraining::where('qty_sludge', '>', 0)
                ->orderBy('draining_date', 'desc')
                ->orderBy('id', 'desc');
            if ($startDate) {
                $q->whereDate('draining_date', '>=', $startDate);
            }
            if ($endDate) {
                $q->whereDate('draining_date', '<=', $endDate);
            }
            if ($tankId) {
                $q->where('tank_id', $tankId);
            }
            $drainings = $q->get([
                'id',
                'draining_code',
                'draining_date',
                'tank_id',
                'tank_name',
                'qty_sludge',
                'target_type',
                'moving_target_type',
                'moving_target_tank_name',
                'moving_target_external',
                'sales_order_no',
            ]);
        } catch (\Throwable $e) {
            $drainings = collect();
        }

        $drainingIds = $drainings->pluck('id')->all();

        $usedByDraining = [];
        $soListByDraining = [];
        try {
            if (!empty($drainingIds) && Schema::hasTable('sales_order_cns') && Schema::hasColumn('sales_order_cns', 'draining_id')) {
                $soQ = DB::table('sales_order_cns as so')
                    ->join('sales_order_cns_dtl as dtl', 'dtl.so_no', '=', 'so.so_no')
                    ->whereIn('so.draining_id', $drainingIds)
                    ->where('so.so_type', 'SLUDGE')
                    ->whereRaw("UPPER(COALESCE(so.status, '')) != 'REJECTED'");
                if (Schema::hasColumn('sales_order_cns', 'sludge_movement_id')) {
                    $soQ->whereNull('so.sludge_movement_id');
                }
                $soRows = $soQ->select('so.draining_id', 'so.so_no', 'so.status', DB::raw('SUM(dtl.qty) as used_qty'))
                    ->groupBy('so.draining_id', 'so.so_no', 'so.status')
                    ->get();

                foreach ($soRows as $sr) {
                    $dId = (int) $sr->draining_id;
                    $qty = (float) $sr->used_qty;
                    $usedByDraining[$dId] = ($usedByDraining[$dId] ?? 0.0) + $qty;
                    $statusLabel = strtoupper((string) ($sr->status ?: 'PENDING'));
                    $soListByDraining[$dId][] = $sr->so_no . ' (' . number_format($qty, 2, '.', ',') . ' KG - ' . $statusLabel . ')';
                }
            }
        } catch (\Throwable $e) {
            $usedByDraining = [];
            $soListByDraining = [];
        }

        $movements = collect();
        $movedByDraining = [];
        $movingListByDraining = [];
        $usedByMovement = [];
        try {
            if (!empty($drainingIds) && Schema::hasTable('cns_sludge_movements')) {
                $movements = SludgeMovement::whereIn('draining_id', $drainingIds)
                    ->orderBy('movement_date', 'desc')
                    ->orderBy('id', 'desc')
                    ->get();
                foreach ($movements as $m) {
                    $dId = (int) $m->draining_id;
                    $movedByDraining[$dId] = ($movedByDraining[$dId] ?? 0.0) + (float) $m->qty;
                    $movingListByDraining[$dId][] = $m->movement_code . ' -> ' . $m->target_warehouse_name . ' (' . number_format((float) $m->qty, 2, '.', ',') . ' KG)';
                }

                $movementIds = $movements->pluck('id')->all();
                if (!empty($movementIds) && Schema::hasTable('sales_order_cns') && Schema::hasColumn('sales_order_cns', 'sludge_movement_id')) {
                    $usedByMovement = DB::table('sales_order_cns as so')
                        ->join('sales_order_cns_dtl as dtl', 'dtl.so_no', '=', 'so.so_no')
                        ->whereIn('so.sludge_movement_id', $movementIds)
                        ->where('so.so_type', 'SLUDGE')
                        ->whereRaw("UPPER(COALESCE(so.status, '')) != 'REJECTED'")
                        ->select('so.sludge_movement_id', DB::raw('SUM(dtl.qty) as used_qty'))
                        ->groupBy('so.sludge_movement_id')
                        ->pluck('used_qty', 'so.sludge_movement_id')
                        ->toArray();
                }
            }
        } catch (\Throwable $e) {
            $movements = collect();
            $movedByDraining = [];
            $usedByMovement = [];
        }

        $sourcesByTank = [];
        $sources = [];
        foreach ($drainings as $d) {
            $dId = (int) $d->id;
            $awal = round((float) $d->qty_sludge, 2);
            $used = round((float) ($usedByDraining[$dId] ?? 0.0), 2);
            $moved = round((float) ($movedByDraining[$dId] ?? 0.0), 2);
            $sisa = max(0.0, round($awal - $used - $moved, 2));
            $dateStr = $d->draining_date ? $d->draining_date->format('d/m/Y') : '-';

            $tujuanDraining = $d->target_type ?: '-';
            if ($d->target_type === 'MOVING') {
                $detail = $d->moving_target_type === 'TANK'
                    ? 'Tangki: ' . ($d->moving_target_tank_name ?? '-')
                    : 'Eksternal: ' . ($d->moving_target_external ?? '-');
                $tujuanDraining = 'MOVING (' . $detail . ')';
            } elseif ($d->target_type === 'PENJUALAN') {
                $detail = $d->sales_order_no ? 'SO: ' . $d->sales_order_no : 'Penjualan Umum';
                $tujuanDraining = 'PENJUALAN (' . $detail . ')';
            }

            $item = [
                'draining_id'     => $dId,
                'draining_code'   => (string) $d->draining_code,
                'draining_date'   => $dateStr,
                'tank_id'         => (int) $d->tank_id,
                'tank_name'       => (string) $d->tank_name,
                'tujuan_draining' => $tujuanDraining,
                'qty_sludge'    => $awal,
                'used_qty'      => $used,
                'moved_qty'     => $moved,
                'sisa_qty'      => $sisa,
                'so_list'       => $soListByDraining[$dId] ?? [],
                'moving_list'   => $movingListByDraining[$dId] ?? [],
            ];
            $sourcesByTank[(int) $d->tank_id][] = $item;
            $sources[] = $item;
        }

        $rows = [];
        $totalAwal = 0.0;
        $totalUsed = 0.0;
        $totalMoving = 0.0;
        $totalSisa = 0.0;

        foreach ($tanks as $tank) {
            $tankSources = $sourcesByTank[(int) $tank->id] ?? [];
            $awal = round(array_sum(array_column($tankSources, 'qty_sludge')), 2);
            $used = round(array_sum(array_column($tankSources, 'used_qty')), 2);
            $moved = round(array_sum(array_column($tankSources, 'moved_qty')), 2);
            $sisa = max(0.0, round($awal - $used - $moved, 2));

            $rows[] = [
                'tank_id'             => $tank->id,
                'tank_name'           => $tank->name,
                'total_sludge_awal'   => $awal,
                'total_sludge_used'   => $used,
                'total_sludge_moving' => $moved,
                'sisa_stock_sludge'   => $sisa,
                'sources'             => $tankSources,
            ];

            $totalAwal += $awal;
            $totalUsed += $used;
            $totalMoving += $moved;
            $totalSisa += $sisa;
        }

        $whMap = [];
        $totalSisaGudang = 0.0;
        foreach ($movements as $m) {
            $wId = (int) $m->target_warehouse_id;
            $wName = (string) $m->target_warehouse_name;
            $mQty = round((float) $m->qty, 2);
            $mUsed = round((float) ($usedByMovement[$m->id] ?? 0.0), 2);

            if (!isset($whMap[$wId])) {
                $whMap[$wId] = [
                    'warehouse_id'      => $wId,
                    'warehouse_name'    => $wName,
                    'total_moving_in'   => 0.0,
                    'total_so_used'     => 0.0,
                    'sisa_stock_gudang' => 0.0,
                ];
            }
            $whMap[$wId]['total_moving_in'] = round($whMap[$wId]['total_moving_in'] + $mQty, 2);
            $whMap[$wId]['total_so_used'] = round($whMap[$wId]['total_so_used'] + $mUsed, 2);
        }

        $warehouses = [];
        foreach ($whMap as $wh) {
            $wh['sisa_stock_gudang'] = max(0.0, round($wh['total_moving_in'] - $wh['total_so_used'], 2));
            $totalSisaGudang += $wh['sisa_stock_gudang'];
            $warehouses[] = $wh;
        }

        return [$rows, [
            'total_awal'        => round($totalAwal, 2),
            'total_used'        => round($totalUsed, 2),
            'total_moving'      => round($totalMoving, 2),
            'total_sisa'        => round($totalSisa, 2),
            'total_sisa_gudang' => round($totalSisaGudang, 2),
        ], $sources, $warehouses];
    }

    public function newDraining()
    {
        $drainingCode = $this->generateNextCode();
        $tanks = MasterTank::active()->orderBy('name', 'asc')->get();
        $salesOrders = SalesOrderCNS::orderBy('id', 'desc')->take(100)->get();

        return view('cns.inventory.tank-draining-new', compact('drainingCode', 'tanks', 'salesOrders'));
    }

    public function detailDraining(Request $request)
    {
        $id = $request->query('ID') ?: $request->query('id');
        $draining = TankDraining::findOrFail($id);
        $tanks = MasterTank::active()->orderBy('name', 'asc')->get();
        $salesOrders = SalesOrderCNS::orderBy('id', 'desc')->take(100)->get();

        $currentStock = $this->calculateTankBalance($draining->tank_name);
        $pendingDrainAdjustment = CNSDrainAdjustment::pending()
            ->where('tank_id', $draining->tank_id)
            ->orderBy('id', 'desc')
            ->first();

        return view('cns.inventory.tank-draining-detail', compact('draining', 'tanks', 'salesOrders', 'currentStock', 'pendingDrainAdjustment'));
    }

    public function xmlData(Request $request)
    {
        $startDate  = $request->query('start_date');
        $endDate    = $request->query('end_date');
        $tankId     = $request->query('tank_id');
        $targetType = $request->query('target_type');
        $search     = $request->query('search');

        $query = TankDraining::query();
        if (!$request->filled('sort_col')) {
            $query->orderBy('draining_date', 'desc')->orderBy('id', 'desc');
        }

        if ($startDate) {
            $query->whereDate('draining_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('draining_date', '<=', $endDate);
        }
        if ($tankId) {
            $query->where('tank_id', $tankId);
        }
        if ($targetType) {
            $query->where('target_type', $targetType);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('draining_code', 'like', "%{$search}%")
                  ->orWhere('tank_name', 'like', "%{$search}%")
                  ->orWhere('moving_target_tank_name', 'like', "%{$search}%")
                  ->orWhere('moving_target_external', 'like', "%{$search}%")
                  ->orWhere('sales_order_no', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $colMap = [
            1  => 'draining_code',
            2  => 'draining_date',
            3  => 'tank_name',
            4  => 'qty',
            5  => 'qty_sludge',
            6  => 'target_type',
            8  => 'notes',
            10 => 'created_by',
            11 => 'created_at',
        ];

        $filterMap = [
            1  => 'draining_code',
            2  => function ($q, $term) {
                $q->whereRaw("DATE_FORMAT(draining_date, '%d/%m/%Y') like ?", ["%{$term}%"]);
            },
            3  => 'tank_name',
            4  => 'qty',
            5  => 'qty_sludge',
            6  => 'target_type',
            7  => function ($q, $term) {
                $q->where(function ($sub) use ($term) {
                    $sub->where('moving_target_tank_name', 'like', "%{$term}%")
                        ->orWhere('moving_target_external', 'like', "%{$term}%")
                        ->orWhere('sales_order_no', 'like', "%{$term}%");
                });
            },
            8  => 'notes',
            10 => 'created_by',
            11 => function ($q, $term) {
                $q->whereRaw("DATE_FORMAT(created_at, '%d/%m/%Y %H:%i') like ?", ["%{$term}%"]);
            },
        ];

        $paginated = DhtmlxGridPagination::paginateQuery($query, $request, [
            'col_map'           => $colMap,
            'filter_map'        => $filterMap,
            'default_page_size' => 25,
        ]);

        $bodyXml = '';
        $no = $paginated['posStart'] + 1;
        foreach ($paginated['rows'] as $row) {
            $tujuanDetail = '-';
            if ($row->target_type === 'MOVING') {
                $tujuanDetail = $row->moving_target_type === 'TANK'
                    ? 'Tangki: ' . ($row->moving_target_tank_name ?? '-')
                    : 'Eksternal: ' . ($row->moving_target_external ?? '-');
            } elseif ($row->target_type === 'PENJUALAN') {
                $tujuanDetail = $row->sales_order_no ? 'SO: ' . $row->sales_order_no : 'Penjualan Umum';
            }

            $bastCell = '';
            if (!empty($row->bast_document_path)) {
                $bastUrl = route('cns-tank-draining.bast', ['id' => $row->id], false);
                $bastName = $row->bast_document_name ?: basename($row->bast_document_path);
                $bastCell = htmlspecialchars($bastUrl) . '^' . htmlspecialchars($bastName);
            }

            $bodyXml .= "<row id=\"{$row->id}\">\n";
            $bodyXml .= "<cell>{$no}</cell>\n";
            $bodyXml .= "<cell>" . htmlspecialchars($row->draining_code) . "</cell>\n";
            $bodyXml .= "<cell>" . ($row->draining_date ? $row->draining_date->format('d/m/Y') : '-') . "</cell>\n";
            $bodyXml .= "<cell>" . htmlspecialchars($row->tank_name) . "</cell>\n";
            $bodyXml .= "<cell>" . number_format((float) $row->qty, 2, '.', ',') . " KG</cell>\n";
            $bodyXml .= "<cell>" . number_format((float) ($row->qty_sludge ?? 0), 2, '.', ',') . " KG</cell>\n";
            $bodyXml .= "<cell>" . htmlspecialchars($row->target_type) . "</cell>\n";
            $bodyXml .= "<cell>" . htmlspecialchars($tujuanDetail) . "</cell>\n";
            $bodyXml .= "<cell>" . htmlspecialchars($row->notes ?? '-') . "</cell>\n";
            $bodyXml .= "<cell>{$bastCell}</cell>\n";
            $bodyXml .= "<cell>" . htmlspecialchars($row->created_by ?? '-') . "</cell>\n";
            $bodyXml .= "<cell>" . ($row->created_at ? $row->created_at->format('d/m/Y H:i') : '-') . "</cell>\n";
            $bodyXml .= "</row>\n";
            $no++;
        }

        return DhtmlxGridPagination::response($paginated['total'], $paginated['posStart'], $bodyXml);
    }

    public function generateCode()
    {
        return response()->json([
            'success' => true,
            'code'    => $this->generateNextCode(),
        ]);
    }

    public function getTankBalance($id)
    {
        $tank = MasterTank::findOrFail($id);
        $balance = $this->calculateTankBalance($tank->name);
        $pending = CNSDrainAdjustment::pending()
            ->where('tank_id', $tank->id)
            ->orderBy('id', 'desc')
            ->first();

        return response()->json([
            'success'                  => true,
            'tank_id'                  => $tank->id,
            'name'                     => $tank->name,
            'capacity'                 => (float) $tank->capacity,
            'balance'                  => $balance,
            'pending_drain_adjustment' => $pending ? [
                'id'              => $pending->id,
                'adjustment_code' => $pending->adjustment_code,
                'qty_sistem'      => (float) $pending->qty_sistem,
                'qty_timbang'     => (float) $pending->qty_timbang,
                'selisih_qty'     => (float) $pending->selisih_qty,
            ] : null,
        ]);
    }

    public function store(Request $request)
    {
        $validator = $this->validateDrainingRequest($request);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $username = Auth::user()->username ?? 'SYSTEM';

        $movingTargetTankName = null;
        if ($request->target_type === 'MOVING' && $request->moving_target_type === 'TANK' && $request->moving_target_tank_id) {
            $destTank = MasterTank::find($request->moving_target_tank_id);
            $movingTargetTankName = $destTank ? $destTank->name : null;
        }

        $salesOrderNo = null;
        if ($request->target_type === 'PENJUALAN' && $request->sales_order_id) {
            $so = SalesOrderCNS::find($request->sales_order_id);
            $salesOrderNo = $so ? $so->so_no : null;
        }

        $bastFileMeta = $this->storeBastDocument($request->file('bast_document'), $request->draining_code);

        DB::beginTransaction();
        try {
            $sourceTank = MasterTank::where('id', $request->tank_id)->lockForUpdate()->firstOrFail();

            if ($pendingAdj = CNSDrainAdjustment::pending()->where('tank_id', $sourceTank->id)->orderBy('id', 'desc')->first()) {
                DB::rollBack();
                Storage::disk('local')->delete($bastFileMeta['path']);
                return response()->json([
                    'success' => false,
                    'message' => "Tangki {$sourceTank->name} masih memiliki Drain Adjustment ({$pendingAdj->adjustment_code}) berstatus PENDING. Harap selesaikan approval terlebih dahulu sebelum melakukan draining.",
                ], 422);
            }

            $currentStock = $this->calculateTankBalance($sourceTank->name);
            $qtySludge = (float) ($request->qty_sludge ?? 0);
            $totalOut = (float) $request->qty + $qtySludge;

            if ($totalOut > $currentStock) {
                DB::rollBack();
                Storage::disk('local')->delete($bastFileMeta['path']);
                return response()->json([
                    'success' => false,
                    'message' => "Total Qty Draining + Sludge (" . number_format($totalOut, 2) . " KG) melebihi stok tangki {$sourceTank->name} yang tersedia (" . number_format($currentStock, 2) . " KG).",
                ], 422);
            }

            $drainingCode = $request->draining_code ?: $this->generateNextCode(true);

            $draining = TankDraining::create([
                'draining_code'          => $drainingCode,
                'draining_date'          => $request->draining_date,
                'tank_id'                => $sourceTank->id,
                'tank_name'              => $sourceTank->name,
                'qty'                    => $request->qty,
                'qty_sludge'             => $qtySludge,
                'target_type'            => $request->target_type,
                'moving_target_type'     => $request->target_type === 'MOVING' ? $request->moving_target_type : null,
                'moving_target_tank_id'  => $request->target_type === 'MOVING' && $request->moving_target_type === 'TANK' ? $request->moving_target_tank_id : null,
                'moving_target_tank_name'=> $movingTargetTankName,
                'moving_target_external' => $request->target_type === 'MOVING' && $request->moving_target_type === 'EXTERNAL' ? $request->moving_target_external : null,
                'sales_order_id'         => $request->target_type === 'PENJUALAN' ? $request->sales_order_id : null,
                'sales_order_no'         => $salesOrderNo,
                'notes'                  => $request->notes,
                'bast_document_path'     => $bastFileMeta['path'],
                'bast_document_name'     => $bastFileMeta['name'],
                'created_by'             => $username,
            ]);

            // Catat Outbound di UCOStoragePelindo untuk tangki asal
            $outboundNotes = "Tank Draining [{$drainingCode}] Tujuan {$request->target_type}";
            if ($request->target_type === 'MOVING') {
                $outboundNotes .= $request->moving_target_type === 'TANK' ? " ke {$movingTargetTankName}" : " ke {$request->moving_target_external}";
            } elseif ($salesOrderNo) {
                $outboundNotes .= " (SO: {$salesOrderNo})";
            }

            UCOStoragePelindo::create([
                'Type'             => 'outbound',
                'Quantity'         => $totalOut,
                'Asal_Warehouse'   => 'PELINDO',
                'Nama_Tangki'      => $sourceTank->name,
                'Created_By'       => $username,
                'Transaction_Date' => $request->draining_date,
                'Reference_Number' => $drainingCode,
                'Notes'            => $outboundNotes,
            ]);

            // Jika Moving ke tangki lain di master tangki, catat Inbound di UCOStoragePelindo untuk tangki tujuan
            if ($request->target_type === 'MOVING' && $request->moving_target_type === 'TANK' && $movingTargetTankName) {
                UCOStoragePelindo::create([
                    'Type'             => 'inbound',
                    'Quantity'         => $request->qty,
                    'Asal_Warehouse'   => 'PELINDO',
                    'Nama_Tangki'      => $movingTargetTankName,
                    'Created_By'       => $username,
                    'Transaction_Date' => $request->draining_date,
                    'Reference_Number' => $drainingCode,
                    'Notes'            => "Tank Draining [{$drainingCode}] Moving dari {$sourceTank->name}",
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data Tank Draining berhasil disimpan.',
                'data'    => $draining,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Storage::disk('local')->delete($bastFileMeta['path']);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $draining = TankDraining::findOrFail($id);

        $validator = $this->validateDrainingRequest($request, $draining);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $sourceTank = MasterTank::findOrFail($request->tank_id);

        if ($pendingAdj = CNSDrainAdjustment::pending()->where('tank_id', $sourceTank->id)->orderBy('id', 'desc')->first()) {
            return response()->json([
                'success' => false,
                'message' => "Tangki {$sourceTank->name} masih memiliki Drain Adjustment ({$pendingAdj->adjustment_code}) berstatus PENDING. Harap selesaikan approval terlebih dahulu sebelum melakukan draining.",
            ], 422);
        }

        // Hitung ketersediaan stok: saldo saat ini ditambah total keluar (qty + sludge) draining sebelumnya jika tangki sama
        $currentStock = $this->calculateTankBalance($sourceTank->name);
        $previousOut = (float) $draining->qty + (float) ($draining->qty_sludge ?? 0);
        $availableStock = ($sourceTank->id == $draining->tank_id) ? ($currentStock + $previousOut) : $currentStock;
        $qtySludge = (float) ($request->qty_sludge ?? 0);
        $totalOut = (float) $request->qty + $qtySludge;

        if ($totalOut > $availableStock) {
            return response()->json([
                'success' => false,
                'message' => "Total Qty Draining + Sludge (" . number_format($totalOut, 2) . " KG) melebihi kapasitas stok tangki {$sourceTank->name} yang tersedia (" . number_format($availableStock, 2) . " KG).",
            ], 422);
        }

        $username = Auth::user()->username ?? 'SYSTEM';

        $movingTargetTankName = null;
        if ($request->target_type === 'MOVING' && $request->moving_target_type === 'TANK' && $request->moving_target_tank_id) {
            $destTank = MasterTank::find($request->moving_target_tank_id);
            $movingTargetTankName = $destTank ? $destTank->name : null;
        }

        $salesOrderNo = null;
        if ($request->target_type === 'PENJUALAN' && $request->sales_order_id) {
            $so = SalesOrderCNS::find($request->sales_order_id);
            $salesOrderNo = $so ? $so->so_no : null;
        }

        $oldBastPath = $draining->bast_document_path;
        $newBastMeta = $request->hasFile('bast_document')
            ? $this->storeBastDocument($request->file('bast_document'), $request->draining_code)
            : null;

        DB::beginTransaction();
        try {
            // Hapus mutasi lama di UCOStoragePelindo
            UCOStoragePelindo::where('Reference_Number', $draining->draining_code)
                ->where('Notes', 'like', '%Tank Draining%')
                ->delete();

            $updatePayload = [
                'draining_code'          => $request->draining_code,
                'draining_date'          => $request->draining_date,
                'tank_id'                => $sourceTank->id,
                'tank_name'              => $sourceTank->name,
                'qty'                    => $request->qty,
                'qty_sludge'             => $qtySludge,
                'target_type'            => $request->target_type,
                'moving_target_type'     => $request->target_type === 'MOVING' ? $request->moving_target_type : null,
                'moving_target_tank_id'  => $request->target_type === 'MOVING' && $request->moving_target_type === 'TANK' ? $request->moving_target_tank_id : null,
                'moving_target_tank_name'=> $movingTargetTankName,
                'moving_target_external' => $request->target_type === 'MOVING' && $request->moving_target_type === 'EXTERNAL' ? $request->moving_target_external : null,
                'sales_order_id'         => $request->target_type === 'PENJUALAN' ? $request->sales_order_id : null,
                'sales_order_no'         => $salesOrderNo,
                'notes'                  => $request->notes,
                'updated_by'             => $username,
            ];

            if ($newBastMeta) {
                $updatePayload['bast_document_path'] = $newBastMeta['path'];
                $updatePayload['bast_document_name'] = $newBastMeta['name'];
            }

            $draining->update($updatePayload);

            // Re-insert Outbound untuk tangki asal
            $outboundNotes = "Tank Draining [{$request->draining_code}] Tujuan {$request->target_type}";
            if ($request->target_type === 'MOVING') {
                $outboundNotes .= $request->moving_target_type === 'TANK' ? " ke {$movingTargetTankName}" : " ke {$request->moving_target_external}";
            } elseif ($salesOrderNo) {
                $outboundNotes .= " (SO: {$salesOrderNo})";
            }

            UCOStoragePelindo::create([
                'Type'             => 'outbound',
                'Quantity'         => $totalOut,
                'Asal_Warehouse'   => 'PELINDO',
                'Nama_Tangki'      => $sourceTank->name,
                'Created_By'       => $username,
                'Transaction_Date' => $request->draining_date,
                'Reference_Number' => $request->draining_code,
                'Notes'            => $outboundNotes,
            ]);

            // Re-insert Inbound jika Moving ke tangki lain
            if ($request->target_type === 'MOVING' && $request->moving_target_type === 'TANK' && $movingTargetTankName) {
                UCOStoragePelindo::create([
                    'Type'             => 'inbound',
                    'Quantity'         => $request->qty,
                    'Asal_Warehouse'   => 'PELINDO',
                    'Nama_Tangki'      => $movingTargetTankName,
                    'Created_By'       => $username,
                    'Transaction_Date' => $request->draining_date,
                    'Reference_Number' => $request->draining_code,
                    'Notes'            => "Tank Draining [{$request->draining_code}] Moving dari {$sourceTank->name}",
                ]);
            }

            DB::commit();

            if ($newBastMeta && $oldBastPath && $oldBastPath !== $newBastMeta['path']) {
                Storage::disk('local')->delete($oldBastPath);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data Tank Draining berhasil diperbarui.',
                'data'    => $draining,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            if ($newBastMeta) {
                Storage::disk('local')->delete($newBastMeta['path']);
            }
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function bastDocument($id)
    {
        $draining = TankDraining::findOrFail($id);

        abort_if(
            empty($draining->bast_document_path)
            || !Storage::disk('local')->exists($draining->bast_document_path),
            404,
            'Dokumen BA tidak ditemukan.'
        );

        $path = Storage::disk('local')->path($draining->bast_document_path);
        $filename = $draining->bast_document_name ?: basename($path);

        return response()->file($path, [
            'Content-Disposition' => 'inline; filename="' . str_replace('"', '', $filename) . '"',
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $draining = TankDraining::findOrFail($id);

        DB::beginTransaction();
        try {
            // Revert mutasi stok di UCOStoragePelindo
            UCOStoragePelindo::where('Reference_Number', $draining->draining_code)
                ->where('Notes', 'like', '%Tank Draining%')
                ->delete();

            $draining->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data Tank Draining berhasil dihapus dan mutasi stok telah dikembalikan.',
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage(),
            ], 500);
        }
    }

    protected function generateNextCode(bool $lock = false): string
    {
        return CnsCodeGenerator::nextDrainingNo(null, $lock);
    }

    protected function validateDrainingRequest(Request $request, ?TankDraining $existing = null)
    {
        $id = $existing ? $existing->id : 'NULL';
        $bastRequired = (!$existing || empty($existing->bast_document_path)) ? 'required' : 'nullable';

        return Validator::make($request->all(), [
            'draining_code'          => "required|string|max:100|unique:cns_tank_drainings,draining_code,{$id},id,deleted_at,NULL",
            'draining_date'          => 'required|date',
            'tank_id'                => 'required|exists:cns_master_tanks,id',
            'qty'                    => 'required|numeric|min:0.01',
            'qty_sludge'             => 'nullable|numeric|min:0',
            'target_type'            => 'required|in:MOVING,PENJUALAN',
            'moving_target_type'     => 'required_if:target_type,MOVING|nullable|in:TANK,EXTERNAL',
            'moving_target_tank_id'  => 'required_if:moving_target_type,TANK|nullable|exists:cns_master_tanks,id|different:tank_id',
            'moving_target_external' => 'required_if:moving_target_type,EXTERNAL|nullable|string|max:255',
            'sales_order_id'         => 'nullable|exists:sales_order_cns,id',
            'notes'                  => 'nullable|string',
            'bast_document'          => "{$bastRequired}|file|mimes:pdf,jpg,jpeg,png|max:10240",
        ], [
            'moving_target_tank_id.different' => 'Tangki tujuan tidak boleh sama dengan tangki asal.',
            'draining_code.unique'            => 'Kode draining sudah pernah digunakan.',
            'bast_document.required'          => 'Dokumen BA draining wajib diupload.',
            'bast_document.mimes'             => 'Dokumen BA harus berformat PDF, JPG, JPEG, atau PNG.',
            'bast_document.max'               => 'Ukuran dokumen BAST maksimal 10 MB.',
        ]);
    }

    protected function storeBastDocument($file, string $drainingCode): array
    {
        $safeCode = preg_replace('/[^A-Za-z0-9_-]/', '_', $drainingCode);
        $ext = strtolower($file->getClientOriginalExtension());
        $filename = 'BAST_' . $safeCode . '_' . date('YmdHis') . '_' . Str::random(6) . '.' . $ext;
        $path = $file->storeAs('cns/tank-draining-bast', $filename, 'local');

        return [
            'path' => $path,
            'name' => $file->getClientOriginalName(),
        ];
    }

    protected function calculateTankBalance(string $tankName): float
    {
        return (float) UCOStoragePelindo::approved()->where('Nama_Tangki', $tankName)
            ->selectRaw("SUM(CASE WHEN Type = 'inbound' THEN Quantity ELSE -Quantity END) as balance")
            ->value('balance') ?: 0;
    }
}
