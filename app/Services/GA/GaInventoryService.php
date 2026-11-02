<?php

namespace App\Services\GA;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class GaInventoryService
{
    const TYPE_OPENING = 'OPENING';
    const TYPE_RECEIPT = 'RECEIPT';
    const TYPE_ISSUE = 'ISSUE';
    const TYPE_RETURN = 'RETURN';
    const TYPE_ADJUSTMENT = 'ADJUSTMENT';
    const TYPE_REVERSAL = 'REVERSAL';
    const TYPE_TRANSFER_OUT = 'TRANSFER_OUT';
    const TYPE_TRANSFER_IN = 'TRANSFER_IN';

    protected $allowedTypes = [
        self::TYPE_OPENING,
        self::TYPE_RECEIPT,
        self::TYPE_ISSUE,
        self::TYPE_RETURN,
        self::TYPE_ADJUSTMENT,
        self::TYPE_REVERSAL,
        self::TYPE_TRANSFER_OUT,
        self::TYPE_TRANSFER_IN,
    ];

    /**
     * Append a stock movement and carry the weighted-average balance forward.
     *
     * @param array $movement
     * @param mixed $user
     * @return object
     */
    public function record(array $movement, $user = null)
    {
        $type = strtoupper(trim((string) ($movement['Movement_Type'] ?? '')));
        if (!in_array($type, $this->allowedTypes, true)) {
            throw new InvalidArgumentException('Movement_Type tidak valid.');
        }

        $productCode = trim((string) ($movement['Product_Code'] ?? ''));
        if ($productCode === '') {
            throw new InvalidArgumentException('Product_Code wajib diisi.');
        }

        $location = trim((string) ($movement['Location'] ?? ''));
        $whId = trim((string) ($movement['WH_ID'] ?? ''));
        $bin = trim((string) ($movement['Bin'] ?? ''));
        $date = $this->normalizeDate($movement['Movement_Date'] ?? null);
        $qtyIn = round((float) ($movement['Qty_In'] ?? 0), 4);
        $qtyOut = round((float) ($movement['Qty_Out'] ?? 0), 4);
        $unitCost = round((float) ($movement['Unit_Cost'] ?? 0), 4);

        if ($qtyIn < 0 || $qtyOut < 0) {
            throw new InvalidArgumentException('Qty_In dan Qty_Out tidak boleh negatif.');
        }
        if ($qtyIn <= 0 && $qtyOut <= 0) {
            throw new InvalidArgumentException('Qty_In atau Qty_Out wajib lebih dari nol.');
        }
        if ($qtyIn > 0 && $qtyOut > 0) {
            throw new InvalidArgumentException('Satu movement hanya boleh memiliki Qty_In atau Qty_Out.');
        }

        $period = substr($date, 0, 7);
        if ($this->isPeriodLocked($period)) {
            throw new InvalidArgumentException('Periode stok ' . $period . ' sudah dikunci.');
        }

        $postingKey = $this->postingKey($movement, $type, $date);
        $createdBy = (string) ($movement['Created_By'] ?? ($user ? $user->username : 'system'));

        return DB::transaction(function () use ($movement, $type, $productCode, $location, $whId, $bin, $date, $qtyIn, $qtyOut, $unitCost, $postingKey, $createdBy) {
            $existing = DB::table('ga_stock_ledger')->where('Posting_Key', $postingKey)->first();
            if ($existing) {
                return $existing;
            }

            $previous = DB::table('ga_stock_ledger')
                ->where('Product_Code', $productCode)
                ->where('Location', $location)
                ->where('WH_ID', $whId)
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            if ($previous && $date < (string) $previous->Movement_Date && empty($movement['Allow_Backdate'])) {
                throw new InvalidArgumentException('Tanggal movement lebih lama dari saldo terakhir. Gunakan Allow_Backdate untuk koreksi.');
            }

            $previousQty = $previous ? (float) $previous->Running_Qty : 0.0;
            $previousValue = $previous ? (float) $previous->Running_Value : 0.0;
            $previousAvg = $previous ? (float) $previous->Avg_Cost : 0.0;
            $effectiveUnitCost = $unitCost;

            if ($type === self::TYPE_OPENING) {
                $runningQty = $previousQty + $qtyIn - $qtyOut;
                $runningValue = $previousValue + ($qtyIn * $effectiveUnitCost) - ($qtyOut * $effectiveUnitCost);
            } elseif ($qtyIn > 0) {
                if ($effectiveUnitCost <= 0 && $previousAvg > 0) {
                    $effectiveUnitCost = $previousAvg;
                }
                $runningQty = $previousQty + $qtyIn;
                $runningValue = $previousValue + ($qtyIn * $effectiveUnitCost);
            } else {
                if ($previousAvg > 0) {
                    $effectiveUnitCost = $previousAvg;
                }
                $runningQty = $previousQty - $qtyOut;
                $runningValue = $previousValue - ($qtyOut * $effectiveUnitCost);
            }

            $runningQty = round($runningQty, 4);
            $runningValue = round($runningValue, 4);
            $avgCost = $runningQty != 0 ? round($runningValue / $runningQty, 4) : 0.0;

            if ($runningQty < 0 && $type !== self::TYPE_OPENING && empty($movement['Allow_Negative'])) {
                throw new InvalidArgumentException('Stok tidak mencukupi untuk ' . $productCode . '. Saldo saat ini ' . $previousQty . '.');
            }

            $id = DB::table('ga_stock_ledger')->insertGetId([
                'Movement_Date' => $date,
                'Movement_Type' => $type,
                'Product_Code' => $productCode,
                'Location' => $location,
                'WH_ID' => $whId,
                'Bin' => $bin !== '' ? $bin : null,
                'Qty_In' => $qtyIn,
                'Qty_Out' => $qtyOut,
                'Unit_Cost' => round($effectiveUnitCost, 4),
                'Running_Qty' => $runningQty,
                'Running_Value' => $runningValue,
                'Avg_Cost' => $avgCost,
                'Source_Type' => $movement['Source_Type'] ?? null,
                'Source_ID' => $movement['Source_ID'] ?? null,
                'Source_Line_ID' => $movement['Source_Line_ID'] ?? null,
                'Posting_Key' => $postingKey,
                'Department' => $movement['Department'] ?? null,
                'Cost_Center' => $movement['Cost_Center'] ?? null,
                'Reason' => $movement['Reason'] ?? null,
                'Created_By' => $createdBy,
                'Created_At' => Carbon::now(),
            ]);

            return DB::table('ga_stock_ledger')->where('id', $id)->first();
        });
    }

    /**
     * Seed opening balances from the legacy supplies table.
     * Idempotent per product/location/warehouse/cutover date.
     *
     * @param string|null $date
     * @param string|null $location
     * @param string|null $whId
     * @param mixed $user
     * @return array
     */
    public function backfillOpeningBalance($date = null, $location = null, $whId = null, $user = null)
    {
        $date = $this->normalizeDate($date);
        $period = substr($date, 0, 7);
        if ($this->isPeriodLocked($period)) {
            throw new InvalidArgumentException('Periode stok ' . $period . ' sudah dikunci.');
        }

        $query = DB::table('supplies')
            ->select(
                'Product_Code',
                'Location',
                DB::raw("COALESCE(WH_ID, '') as WH_ID"),
                DB::raw('SUM(Qty) as Qty'),
                DB::raw('SUM(Qty * COALESCE(UnitPrice, 0)) as Value')
            )
            ->whereNotNull('Product_Code')
            ->where('Product_Code', '<>', '');

        if ($location !== null && $location !== '') {
            $query->where('Location', $location);
        }
        if ($whId !== null && $whId !== '') {
            $query->where('WH_ID', $whId);
        }

        $rows = $query
            ->groupBy('Product_Code', 'Location', DB::raw("COALESCE(WH_ID, '')"))
            ->get();

        $created = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $qty = round((float) $row->Qty, 4);
            if (abs($qty) < 0.00005) {
                $skipped++;
                continue;
            }

            $value = round((float) $row->Value, 4);
            $unitCost = $qty != 0 ? $value / $qty : 0;
            if ($unitCost < 0) {
                $unitCost = 0;
            }

            $rowLocation = (string) $row->Location;
            $rowWhId = (string) $row->WH_ID;
            $sourceLine = $row->Product_Code . '|' . $rowLocation . '|' . $rowWhId;
            $postingKey = sha1('OPENING|SUPPLIES|' . $sourceLine . '|' . $date);

            if (DB::table('ga_stock_ledger')->where('Posting_Key', $postingKey)->exists()) {
                $skipped++;
                continue;
            }

            $this->record([
                'Movement_Date' => $date,
                'Movement_Type' => self::TYPE_OPENING,
                'Product_Code' => $row->Product_Code,
                'Location' => $rowLocation,
                'WH_ID' => $rowWhId,
                'Qty_In' => $qty > 0 ? $qty : 0,
                'Qty_Out' => $qty < 0 ? abs($qty) : 0,
                'Unit_Cost' => $unitCost,
                'Source_Type' => 'OPENING',
                'Source_ID' => 'SUPPLIES',
                'Source_Line_ID' => $sourceLine,
                'Posting_Key' => $postingKey,
                'Reason' => 'Opening balance backfill from supplies.',
                'Allow_Negative' => true,
            ], $user);

            $created++;
        }

        return ['created' => $created, 'skipped' => $skipped, 'date' => $date];
    }

    /**
     * Current stock below the configured minimum, with product_code.Dimension
     * as the fallback minimum when no explicit minimum exists.
     *
     * @param array $filters
     * @return array
     */
    public function lowStockRows(array $filters = [])
    {
        $location = trim((string) ($filters['location'] ?? ''));
        $whId = trim((string) ($filters['wh_id'] ?? ''));
        $search = trim((string) ($filters['search'] ?? ''));
        $bindings = [];

        $subWhere = '';
        if ($location !== '') {
            $subWhere .= ' AND Location = ?';
            $bindings[] = $location;
        }
        if ($whId !== '') {
            $subWhere .= ' AND WH_ID = ?';
            $bindings[] = $whId;
        }

        $outerWhere = '';
        $outerBindings = [];
        if ($location !== '') {
            $outerWhere .= ' AND pc.Location = ?';
            $outerBindings[] = $location;
        }
        if ($search !== '') {
            $outerWhere .= ' AND (pc.Product_Code LIKE ? OR pc.Description LIKE ?)';
            $outerBindings[] = '%' . $search . '%';
            $outerBindings[] = '%' . $search . '%';
        }

        $bindings[] = $whId;
        $bindings = array_merge($bindings, $outerBindings);

        $sql = "SELECT * FROM (
                    SELECT pc.Product_Code, pc.Description, pc.Unit_Name, pc.Location, pc.Dimension,
                           COALESCE(ms.Min_Qty, pc.Dimension) AS Min_Qty,
                           COALESCE(st.On_Hand, 0) AS On_Hand,
                           (COALESCE(ms.Min_Qty, pc.Dimension) - COALESCE(st.On_Hand, 0)) AS Shortage
                    FROM product_code pc
                    LEFT JOIN (
                        SELECT Product_Code, Location, SUM(Qty) AS On_Hand
                        FROM supplies
                        WHERE 1=1" . $subWhere . "
                        GROUP BY Product_Code, Location
                    ) st ON st.Product_Code = pc.Product_Code AND st.Location = pc.Location
                    LEFT JOIN ga_item_minimum_stock ms
                        ON ms.Product_Code = pc.Product_Code
                       AND ms.Location = pc.Location
                       AND ms.WH_ID = ?
                       AND ms.Is_Active = 1
                    WHERE COALESCE(ms.Min_Qty, pc.Dimension) > 0" . $outerWhere . "
                    GROUP BY pc.Product_Code, pc.Description, pc.Unit_Name, pc.Location, pc.Dimension, ms.Min_Qty, st.On_Hand
                ) x
                WHERE x.On_Hand < x.Min_Qty
                ORDER BY x.Shortage DESC, x.Product_Code ASC
                LIMIT 1000";

        return DB::select($sql, $bindings);
    }

    /**
     * Minimum-stock rows configured by the user.
     *
     * @param array $filters
     * @return \Illuminate\Support\Collection
     */
    public function minimumStockRows(array $filters = [])
    {
        $location = trim((string) ($filters['location'] ?? ''));
        $whId = trim((string) ($filters['wh_id'] ?? ''));
        $search = trim((string) ($filters['search'] ?? ''));
        $productCode = trim((string) ($filters['product_code'] ?? ''));

        return DB::table('ga_item_minimum_stock as ms')
            ->leftJoin('product_code as pc', 'pc.Product_Code', '=', 'ms.Product_Code')
            ->select('ms.*', 'pc.Description', 'pc.Unit_Name')
            ->when($location !== '', function ($query) use ($location) {
                $query->where('ms.Location', $location);
            })
            ->when($whId !== '', function ($query) use ($whId) {
                $query->where('ms.WH_ID', $whId);
            })
            ->when($productCode !== '', function ($query) use ($productCode) {
                $query->where('ms.Product_Code', $productCode);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('ms.Product_Code', 'like', '%' . $search . '%')
                        ->orWhere('pc.Description', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('ms.Product_Code')
            ->orderBy('ms.Location')
            ->orderBy('ms.WH_ID')
            ->limit(1000)
            ->get();
    }

    /**
     * Save or update a minimum-stock setting.
     *
     * @param array $data
     * @param mixed $user
     * @return object
     */
    public function saveMinimumStock(array $data, $user = null)
    {
        $productCode = trim((string) ($data['Product_Code'] ?? ''));
        if ($productCode === '') {
            throw new InvalidArgumentException('Product Code wajib diisi.');
        }
        if (!DB::table('product_code')->where('Product_Code', $productCode)->exists()) {
            throw new InvalidArgumentException('Product Code tidak ditemukan.');
        }

        $location = trim((string) ($data['Location'] ?? ''));
        $whId = trim((string) ($data['WH_ID'] ?? ''));
        $minQty = round((float) ($data['Min_Qty'] ?? 0), 4);
        $targetQty = round((float) ($data['Target_Qty'] ?? 0), 4);
        if ($minQty < 0 || $targetQty < 0) {
            throw new InvalidArgumentException('Min_Qty dan Target_Qty tidak boleh negatif.');
        }

        $active = !empty($data['Is_Active']) ? 1 : 0;
        $now = Carbon::now();
        $username = (string) ($user ? $user->username : 'system');

        $existing = DB::table('ga_item_minimum_stock')
            ->where('Product_Code', $productCode)
            ->where('Location', $location)
            ->where('WH_ID', $whId)
            ->first();

        if ($existing) {
            DB::table('ga_item_minimum_stock')->where('id', $existing->id)->update([
                'Min_Qty' => $minQty,
                'Target_Qty' => $targetQty,
                'Is_Active' => $active,
                'Updated_By' => $username,
                'Updated_At' => $now,
            ]);
        } else {
            DB::table('ga_item_minimum_stock')->insert([
                'Product_Code' => $productCode,
                'Location' => $location,
                'WH_ID' => $whId,
                'Min_Qty' => $minQty,
                'Target_Qty' => $targetQty,
                'Is_Active' => $active,
                'Created_By' => $username,
                'Created_At' => $now,
            ]);
        }

        return DB::table('ga_item_minimum_stock')
            ->where('Product_Code', $productCode)
            ->where('Location', $location)
            ->where('WH_ID', $whId)
            ->first();
    }

    /**
     * Last ledger row at or before a cutoff date for one item/warehouse.
     *
     * @param string $productCode
     * @param string $location
     * @param string $whId
     * @param string|null $asOf
     * @return object|null
     */
    public function balanceAsOf($productCode, $location, $whId, $asOf = null)
    {
        $asOf = $this->normalizeDate($asOf);

        return DB::table('ga_stock_ledger')
            ->where('Product_Code', $productCode)
            ->where('Location', (string) $location)
            ->where('WH_ID', (string) $whId)
            ->where('Movement_Date', '<=', $asOf)
            ->orderByDesc('Movement_Date')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * One row per item/location/warehouse as of a cutoff date.
     *
     * @param array $filters
     * @return array
     */
    public function cutoffRows(array $filters = [])
    {
        $asOf = $this->normalizeDate($filters['as_of'] ?? null);
        $location = trim((string) ($filters['location'] ?? ''));
        $whId = trim((string) ($filters['wh_id'] ?? ''));
        $search = trim((string) ($filters['search'] ?? ''));
        $bindings = [$asOf];

        $sql = "SELECT l.*, pc.Description, pc.Unit_Name
                FROM ga_stock_ledger l
                INNER JOIN (
                    SELECT Product_Code, Location, WH_ID, MAX(id) AS Max_ID
                    FROM ga_stock_ledger
                    WHERE Movement_Date <= ?
                    GROUP BY Product_Code, Location, WH_ID
                ) x ON x.Max_ID = l.id
                LEFT JOIN product_code pc ON pc.Product_Code = l.Product_Code
                WHERE 1=1";

        if ($location !== '') {
            $sql .= ' AND l.Location = ?';
            $bindings[] = $location;
        }
        if ($whId !== '') {
            $sql .= ' AND l.WH_ID = ?';
            $bindings[] = $whId;
        }
        if ($search !== '') {
            $sql .= ' AND (l.Product_Code LIKE ? OR pc.Description LIKE ?)';
            $bindings[] = '%' . $search . '%';
            $bindings[] = '%' . $search . '%';
        }

        $sql .= ' ORDER BY l.Product_Code ASC LIMIT 2000';

        return DB::select($sql, $bindings);
    }

    /**
     * Laporan Year-to-Date (YTD) Stock:
     * Menghitung Saldo Awal (Beginning 1 Jan), Total Masuk YTD, Total Keluar YTD,
     * dan Saldo Akhir (Ending Balance & Value) per item/lokasi/gudang.
     */
    public function ytdStockRows(array $filters = [])
    {
        $year = (int) ($filters['year'] ?? date('Y'));
        if ($year < 2000 || $year > 2100) {
            $year = (int) date('Y');
        }

        $startDate = $year . '-01-01';
        $endDate = trim((string) ($filters['as_of'] ?? ''));
        if ($endDate === '' || !strtotime($endDate)) {
            $endDate = ($year == (int) date('Y')) ? date('Y-m-d') : ($year . '-12-31');
        }

        $location = trim((string) ($filters['location'] ?? ''));
        $whId = trim((string) ($filters['wh_id'] ?? ''));
        $search = trim((string) ($filters['search'] ?? ''));

        $subQuery = \Illuminate\Support\Facades\DB::table('ga_stock_ledger as l')
            ->leftJoin('product_code as pc', 'pc.Product_Code', '=', 'l.Product_Code')
            ->select('l.Product_Code', 'l.Location', 'l.WH_ID', 'pc.Description', 'pc.Unit_Name')
            ->distinct();

        if ($location !== '') {
            $subQuery->where('l.Location', $location);
        }
        if ($whId !== '') {
            $subQuery->where('l.WH_ID', $whId);
        }
        if ($search !== '') {
            $subQuery->where(function ($q) use ($search) {
                $q->where('l.Product_Code', 'LIKE', "%$search%")
                  ->orWhere('pc.Description', 'LIKE', "%$search%");
            });
        }

        $items = $subQuery->orderBy('l.Product_Code')->get();
        $results = [];

        foreach ($items as $item) {
            $lastBeforeYear = \Illuminate\Support\Facades\DB::table('ga_stock_ledger')
                ->where('Product_Code', $item->Product_Code)
                ->where('Location', $item->Location)
                ->where('WH_ID', $item->WH_ID)
                ->where('Movement_Date', '<', $startDate)
                ->orderBy('Movement_Date', 'DESC')
                ->orderBy('id', 'DESC')
                ->first();

            $beginningQty = $lastBeforeYear ? (float) $lastBeforeYear->Running_Qty : 0.0;
            $beginningValue = $lastBeforeYear ? (float) $lastBeforeYear->Running_Value : 0.0;

            $ytdMovement = \Illuminate\Support\Facades\DB::table('ga_stock_ledger')
                ->where('Product_Code', $item->Product_Code)
                ->where('Location', $item->Location)
                ->where('WH_ID', $item->WH_ID)
                ->whereBetween('Movement_Date', [$startDate, $endDate])
                ->select(
                    \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(Qty_In), 0) as Total_In'),
                    \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(Qty_Out), 0) as Total_Out')
                )
                ->first();

            $totalIn = $ytdMovement ? (float) $ytdMovement->Total_In : 0.0;
            $totalOut = $ytdMovement ? (float) $ytdMovement->Total_Out : 0.0;

            $lastAsOf = \Illuminate\Support\Facades\DB::table('ga_stock_ledger')
                ->where('Product_Code', $item->Product_Code)
                ->where('Location', $item->Location)
                ->where('WH_ID', $item->WH_ID)
                ->where('Movement_Date', '<=', $endDate)
                ->orderBy('Movement_Date', 'DESC')
                ->orderBy('id', 'DESC')
                ->first();

            $endingQty = $lastAsOf ? (float) $lastAsOf->Running_Qty : ($beginningQty + $totalIn - $totalOut);
            $endingValue = $lastAsOf ? (float) $lastAsOf->Running_Value : 0.0;
            $avgCost = $endingQty > 0 ? ($endingValue / $endingQty) : ($lastAsOf ? (float)$lastAsOf->Avg_Cost : 0.0);

            if ($beginningQty == 0 && $totalIn == 0 && $totalOut == 0 && $endingQty == 0) {
                continue;
            }

            $results[] = (object) [
                'Product_Code' => $item->Product_Code,
                'Description' => $item->Description ?? '-',
                'Unit_Name' => $item->Unit_Name ?? '-',
                'Location' => $item->Location ?? '-',
                'WH_ID' => $item->WH_ID ?? '-',
                'Year' => $year,
                'Beginning_Qty' => $beginningQty,
                'Beginning_Value' => $beginningValue,
                'YTD_In' => $totalIn,
                'YTD_Out' => $totalOut,
                'Ending_Qty' => $endingQty,
                'Ending_Value' => $endingValue,
                'Avg_Cost' => $avgCost,
                'As_Of' => $endDate,
            ];
        }

        return $results;
    }

    protected function isPeriodLocked($period)
    {
        return DB::table('ga_stock_period')
            ->where('Period', $period)
            ->where('Status', 'LOCKED')
            ->exists();
    }

    protected function normalizeDate($value)
    {
        if ($value === null || $value === '') {
            return Carbon::now()->toDateString();
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Exception $exception) {
            throw new InvalidArgumentException('Tanggal movement tidak valid.');
        }
    }

    protected function postingKey(array $movement, $type, $date)
    {
        if (!empty($movement['Posting_Key'])) {
            return (string) $movement['Posting_Key'];
        }

        if (!empty($movement['Source_Type']) && !empty($movement['Source_Line_ID'])) {
            return sha1($movement['Source_Type'] . '|' . ($movement['Source_ID'] ?? '') . '|' . $movement['Source_Line_ID'] . '|' . $type . '|' . $date);
        }

        return sha1(uniqid('ga-ledger-', true));
    }

    /**
     * Record receipt from Good Receive into GA ledger.
     */
    public function recordReceiptFromGoodReceive($grNo, $productCode, $qty, $unitCost, $location, $whId = '', $user = null)
    {
        $qty = round((float) $qty, 4);
        if ($qty <= 0) {
            return null;
        }

        $sourceLine = $grNo . '|' . $productCode . '|' . $location . '|' . $whId;
        return $this->record([
            'Movement_Date' => Carbon::now()->toDateString(),
            'Movement_Type' => self::TYPE_RECEIPT,
            'Product_Code' => $productCode,
            'Location' => (string) $location,
            'WH_ID' => (string) $whId,
            'Qty_In' => $qty,
            'Qty_Out' => 0,
            'Unit_Cost' => (float) $unitCost,
            'Source_Type' => 'GOOD_RECEIVE',
            'Source_ID' => (string) $grNo,
            'Source_Line_ID' => $sourceLine,
            'Reason' => 'Penerimaan barang dari GR ' . $grNo,
        ], $user);
    }

    /**
     * Record issue from Stock Request into GA ledger.
     */
    public function recordIssueFromStockRequest($srNo, $productCode, $qty, $location, $whId = '', $department = null, $user = null)
    {
        $qty = round((float) $qty, 4);
        if ($qty <= 0) {
            return null;
        }

        $sourceLine = $srNo . '|' . $productCode . '|' . $location . '|' . $whId;
        return $this->record([
            'Movement_Date' => Carbon::now()->toDateString(),
            'Movement_Type' => self::TYPE_ISSUE,
            'Product_Code' => $productCode,
            'Location' => (string) $location,
            'WH_ID' => (string) $whId,
            'Qty_In' => 0,
            'Qty_Out' => $qty,
            'Unit_Cost' => 0, // Auto-computed using WAC
            'Source_Type' => 'STOCK_REQUEST',
            'Source_ID' => (string) $srNo,
            'Source_Line_ID' => $sourceLine,
            'Department' => $department,
            'Reason' => 'Pengeluaran barang stock request ' . $srNo,
        ], $user);
    }

    /**
     * Reversal of an approved/issued stock request if items are not collected or cancelled.
     */
    public function reverseStockRequest($srNo, $reason = '', $user = null)
    {
        $srNo = trim((string) $srNo);
        $request = DB::table('stock_request')->where('SR_No', $srNo)->first();
        if (!$request) {
            throw new InvalidArgumentException('Stock request ' . $srNo . ' tidak ditemukan.');
        }

        if (in_array(strtoupper((string) $request->Status), ['CANCELLED', 'REVERSED'], true)) {
            throw new InvalidArgumentException('Stock request sudah dibatalkan/reversal sebelumnya.');
        }

        // Find all issue ledger entries for this SR
        $issues = DB::table('ga_stock_ledger')
            ->where('Source_Type', 'STOCK_REQUEST')
            ->where('Source_ID', $srNo)
            ->where('Movement_Type', self::TYPE_ISSUE)
            ->get();

        if ($issues->isEmpty()) {
            // Check stock_request_dtl to see if we can reverse even if ledger was missing
            $details = DB::table('stock_request_dtl')->where('SR_No', $srNo)->get();
            if ($details->isEmpty()) {
                throw new InvalidArgumentException('Tidak ada detail item yang dapat di-reversal untuk ' . $srNo);
            }

            return DB::transaction(function () use ($request, $details, $srNo, $reason, $user) {
                foreach ($details as $dtl) {
                    $qty = (float) ($dtl->Qty_Approved > 0 ? $dtl->Qty_Approved : $dtl->Qty);
                    if ($qty <= 0) continue;

                    // Restock back to supplies
                    DB::table('supplies')->where('Product_Code', $dtl->Product_Code)
                        ->where('Location', $request->Location)
                        ->increment('Qty', $qty);

                    // Record REVERSAL in GA stock ledger
                    $sourceLine = $srNo . '|' . $dtl->Product_Code . '|REVERSAL|' . $dtl->ID;
                    $this->record([
                        'Movement_Date' => Carbon::now()->toDateString(),
                        'Movement_Type' => self::TYPE_REVERSAL,
                        'Product_Code' => $dtl->Product_Code,
                        'Location' => (string) $request->Location,
                        'WH_ID' => (string) ($request->WH_Dest ?? ''),
                        'Qty_In' => $qty,
                        'Qty_Out' => 0,
                        'Unit_Cost' => (float) ($dtl->Prices ?? 0),
                        'Source_Type' => 'STOCK_REQUEST_REVERSAL',
                        'Source_ID' => $srNo,
                        'Source_Line_ID' => $sourceLine,
                        'Department' => $request->Department,
                        'Reason' => 'Reversal stock request ' . $srNo . ($reason !== '' ? ': ' . $reason : ''),
                    ], $user);
                }

                DB::table('stock_request')->where('SR_No', $srNo)->update([
                    'Status' => 'REVERSED',
                    'Remark' => DB::raw("CONCAT('REVERSED: " . addslashes($reason) . " \n', COALESCE(Remark, ''))")
                ]);

                return true;
            });
        }

        return DB::transaction(function () use ($request, $issues, $srNo, $reason, $user) {
            foreach ($issues as $issue) {
                // Return stock to supplies
                DB::table('supplies')->where('Product_Code', $issue->Product_Code)
                    ->where('Location', $issue->Location)
                    ->increment('Qty', (float) $issue->Qty_Out);

                // Record REVERSAL in ledger with exact original issue unit cost
                $sourceLine = $srNo . '|' . $issue->Product_Code . '|REVERSAL|' . $issue->id;
                $this->record([
                    'Movement_Date' => Carbon::now()->toDateString(),
                    'Movement_Type' => self::TYPE_REVERSAL,
                    'Product_Code' => $issue->Product_Code,
                    'Location' => $issue->Location,
                    'WH_ID' => $issue->WH_ID,
                    'Qty_In' => (float) $issue->Qty_Out,
                    'Qty_Out' => 0,
                    'Unit_Cost' => (float) $issue->Unit_Cost,
                    'Source_Type' => 'STOCK_REQUEST_REVERSAL',
                    'Source_ID' => $srNo,
                    'Source_Line_ID' => $sourceLine,
                    'Department' => $issue->Department,
                    'Cost_Center' => $issue->Cost_Center,
                    'Reason' => 'Reversal stock request ' . $srNo . ($reason !== '' ? ': ' . $reason : ''),
                ], $user);
            }

            DB::table('stock_request')->where('SR_No', $srNo)->update([
                'Status' => 'REVERSED',
                'Remark' => DB::raw("CONCAT('REVERSED: " . addslashes($reason) . " \n', COALESCE(Remark, ''))")
            ]);

            return true;
        });
    }

    /**
     * Handover confirmation for stock request (status only, moves no stock).
     */
    public function confirmHandover($srNo, array $data = [], $user = null)
    {
        $srNo = trim((string) $srNo);
        $request = DB::table('stock_request')->where('SR_No', $srNo)->first();
        if (!$request) {
            throw new InvalidArgumentException('Stock request ' . $srNo . ' tidak ditemukan.');
        }

        $handoverBy = !empty($data['Handover_By']) ? $data['Handover_By'] : ($user ? $user->username : 'system');
        $handoverTo = !empty($data['Handover_To']) ? $data['Handover_To'] : ($request->Request_By ?? '');
        $now = Carbon::now();
        $notes = !empty($data['Notes']) ? $data['Notes'] : '';

        $remark = "HANDOVER CONFIRMED on {$now->toDateTimeString()} by {$handoverBy} to {$handoverTo}. " . ($notes !== '' ? "Notes: {$notes}" : '');

        DB::table('stock_request')->where('SR_No', $srNo)->update([
            'Status' => 'HANDOVER_CONFIRMED',
            'Date_Pickup' => $now->toDateString(),
            'Remark' => DB::raw("CONCAT('" . addslashes($remark) . " \n', COALESCE(Remark, ''))")
        ]);

        return DB::table('stock_request')->where('SR_No', $srNo)->first();
    }

    /**
     * Transfer stock between locations / warehouses.
     */
    public function transferStock(array $data, $user = null)
    {
        $productCode = trim((string) ($data['Product_Code'] ?? ''));
        $fromLocation = trim((string) ($data['From_Location'] ?? ''));
        $toLocation = trim((string) ($data['To_Location'] ?? ''));
        $fromWhId = trim((string) ($data['From_WH_ID'] ?? ''));
        $toWhId = trim((string) ($data['To_WH_ID'] ?? ''));
        $qty = round((float) ($data['Qty'] ?? 0), 4);
        $transferNo = trim((string) ($data['Transfer_No'] ?? ('TRF/' . Carbon::now()->format('YmdHis'))));
        $reason = trim((string) ($data['Reason'] ?? 'Transfer stok antarlokasi/gudang'));

        if ($productCode === '' || $fromLocation === '' || $toLocation === '' || $qty <= 0) {
            throw new InvalidArgumentException('Product Code, From Location, To Location, dan Qty wajib diisi.');
        }
        if ($fromLocation === $toLocation && $fromWhId === $toWhId) {
            throw new InvalidArgumentException('Lokasi dan gudang asal tidak boleh sama dengan tujuan.');
        }

        return DB::transaction(function () use ($productCode, $fromLocation, $toLocation, $fromWhId, $toWhId, $qty, $transferNo, $reason, $user) {
            // Deduct from origin supplies
            $originSupply = DB::table('supplies')
                ->where('Product_Code', $productCode)
                ->where('Location', $fromLocation)
                ->where('Qty', '>=', $qty)
                ->first();

            if (!$originSupply) {
                throw new InvalidArgumentException("Stok tidak mencukupi di lokasi {$fromLocation} untuk {$productCode}.");
            }

            DB::table('supplies')->where('ID', $originSupply->ID)->decrement('Qty', $qty);

            // Add or increment in destination supplies
            $destSupply = DB::table('supplies')
                ->where('Product_Code', $productCode)
                ->where('Location', $toLocation)
                ->first();

            if ($destSupply) {
                DB::table('supplies')->where('ID', $destSupply->ID)->increment('Qty', $qty);
            } else {
                DB::table('supplies')->insert([
                    'Supplies_Type' => $originSupply->Supplies_Type,
                    'Location' => $toLocation,
                    'WH_ID' => $toWhId !== '' ? $toWhId : null,
                    'Product_Code' => $productCode,
                    'Product_Description' => $originSupply->Product_Description,
                    'Specification' => $originSupply->Specification,
                    'Qty' => $qty,
                    'Unit' => $originSupply->Unit,
                    'UnitPrice' => $originSupply->UnitPrice,
                    'Status' => 'GOOD',
                    'User_Trx' => $user ? $user->username : 'system',
                    'Remark' => "Transferred from {$fromLocation} via {$transferNo}",
                ]);
            }

            // Record TRANSFER_OUT
            $outLine = $transferNo . '|' . $productCode . '|OUT|' . $fromLocation;
            $outMovement = $this->record([
                'Movement_Date' => Carbon::now()->toDateString(),
                'Movement_Type' => self::TYPE_TRANSFER_OUT,
                'Product_Code' => $productCode,
                'Location' => $fromLocation,
                'WH_ID' => $fromWhId,
                'Qty_In' => 0,
                'Qty_Out' => $qty,
                'Unit_Cost' => 0,
                'Source_Type' => 'STOCK_TRANSFER',
                'Source_ID' => $transferNo,
                'Source_Line_ID' => $outLine,
                'Reason' => $reason . " (Ke {$toLocation})",
            ], $user);

            $transferUnitCost = $outMovement ? (float) $outMovement->Unit_Cost : (float) $originSupply->UnitPrice;

            // Record TRANSFER_IN
            $inLine = $transferNo . '|' . $productCode . '|IN|' . $toLocation;
            $inMovement = $this->record([
                'Movement_Date' => Carbon::now()->toDateString(),
                'Movement_Type' => self::TYPE_TRANSFER_IN,
                'Product_Code' => $productCode,
                'Location' => $toLocation,
                'WH_ID' => $toWhId,
                'Qty_In' => $qty,
                'Qty_Out' => 0,
                'Unit_Cost' => $transferUnitCost,
                'Source_Type' => 'STOCK_TRANSFER',
                'Source_ID' => $transferNo,
                'Source_Line_ID' => $inLine,
                'Reason' => $reason . " (Dari {$fromLocation})",
            ], $user);

            return [
                'transfer_no' => $transferNo,
                'transfer_out' => $outMovement,
                'transfer_in' => $inMovement,
            ];
        });
    }

    /**
     * Reusable Unit Register: Add unit.
     */
    public function registerReusableUnit(array $data, $user = null)
    {
        $unitCode = trim((string) ($data['Unit_Code'] ?? ''));
        $productCode = trim((string) ($data['Product_Code'] ?? ''));

        if ($unitCode === '' || $productCode === '') {
            throw new InvalidArgumentException('Unit Code dan Product Code wajib diisi.');
        }

        if (DB::table('ga_reusable_unit')->where('Unit_Code', $unitCode)->exists()) {
            throw new InvalidArgumentException('Unit Code ' . $unitCode . ' sudah terdaftar.');
        }

        $now = Carbon::now();
        $username = $user ? $user->username : 'system';

        $id = DB::table('ga_reusable_unit')->insertGetId([
            'Unit_Code' => $unitCode,
            'Product_Code' => $productCode,
            'Serial_Number' => $data['Serial_Number'] ?? null,
            'Asset_No' => $data['Asset_No'] ?? null,
            'Category' => $data['Category'] ?? 'GENERAL',
            'Condition_Status' => $data['Condition_Status'] ?? 'GOOD',
            'Loan_Status' => 'AVAILABLE',
            'Current_Location' => $data['Current_Location'] ?? '',
            'Current_WH_ID' => $data['Current_WH_ID'] ?? '',
            'Notes' => $data['Notes'] ?? null,
            'Created_By' => $username,
            'Created_At' => $now,
            'Updated_By' => $username,
            'Updated_At' => $now,
        ]);

        DB::table('ga_reusable_unit_history')->insert([
            'Unit_ID' => $id,
            'Action_Type' => 'REGISTER',
            'Reference_No' => $unitCode,
            'From_Status' => null,
            'To_Status' => 'AVAILABLE',
            'From_Location' => null,
            'To_Location' => $data['Current_Location'] ?? '',
            'Holder_Name' => null,
            'Remarks' => 'Pendaftaran unit reusable baru.',
            'Created_By' => $username,
            'Created_At' => $now,
        ]);

        return DB::table('ga_reusable_unit')->where('id', $id)->first();
    }

    /**
     * Reusable Unit Loan: Borrow a unit.
     */
    public function loanReusableUnit(array $data, $user = null)
    {
        $unitId = (int) ($data['Unit_ID'] ?? 0);
        $unit = DB::table('ga_reusable_unit')->where('id', $unitId)->first();
        if (!$unit) {
            throw new InvalidArgumentException('Unit reusable tidak ditemukan.');
        }
        if ($unit->Loan_Status !== 'AVAILABLE') {
            throw new InvalidArgumentException('Unit saat ini berstatus ' . $unit->Loan_Status . ' dan tidak dapat dipinjam.');
        }

        $borrowerName = trim((string) ($data['Borrower_Name'] ?? ''));
        if ($borrowerName === '') {
            throw new InvalidArgumentException('Nama peminjam wajib diisi.');
        }

        $dateOut = $this->normalizeDate($data['Date_Out'] ?? null);
        $dueDate = $this->normalizeDate($data['Due_Date'] ?? null);
        $loanNo = 'LN/GA/' . Carbon::now()->format('YmdHis') . '/' . $unit->id;
        $now = Carbon::now();
        $username = $user ? $user->username : 'system';

        return DB::transaction(function () use ($unit, $borrowerName, $dateOut, $dueDate, $loanNo, $now, $username, $data) {
            $loanId = DB::table('ga_reusable_loan')->insertGetId([
                'Loan_No' => $loanNo,
                'Unit_ID' => $unit->id,
                'Borrower_User_Id' => $data['Borrower_User_Id'] ?? null,
                'Borrower_Name' => $borrowerName,
                'Borrower_Department' => $data['Borrower_Department'] ?? null,
                'Borrower_Contact' => $data['Borrower_Contact'] ?? null,
                'Location' => $unit->Current_Location,
                'Date_Out' => $dateOut,
                'Due_Date' => $dueDate,
                'Status' => 'ACTIVE',
                'Condition_Out' => $unit->Condition_Status,
                'Purpose' => $data['Purpose'] ?? null,
                'Ticket_ID' => !empty($data['Ticket_ID']) ? (int) $data['Ticket_ID'] : null,
                'Created_By' => $username,
                'Created_At' => $now,
                'Updated_By' => $username,
                'Updated_At' => $now,
            ]);

            DB::table('ga_reusable_unit')->where('id', $unit->id)->update([
                'Loan_Status' => 'ON_LOAN',
                'Current_Holder_User_Id' => $data['Borrower_User_Id'] ?? null,
                'Current_Holder_Name' => $borrowerName,
                'Current_Holder_Department' => $data['Borrower_Department'] ?? null,
                'Updated_By' => $username,
                'Updated_At' => $now,
            ]);

            DB::table('ga_reusable_unit_history')->insert([
                'Unit_ID' => $unit->id,
                'Action_Type' => 'LOAN',
                'Reference_No' => $loanNo,
                'From_Status' => 'AVAILABLE',
                'To_Status' => 'ON_LOAN',
                'From_Location' => $unit->Current_Location,
                'To_Location' => $unit->Current_Location,
                'Holder_Name' => $borrowerName,
                'Remarks' => 'Peminjaman unit kepada ' . $borrowerName . '. Keperluan: ' . ($data['Purpose'] ?? '-'),
                'Created_By' => $username,
                'Created_At' => $now,
            ]);

            return DB::table('ga_reusable_loan')->where('id', $loanId)->first();
        });
    }

    /**
     * Reusable Unit Return: Return a borrowed unit.
     */
    public function returnReusableUnit(array $data, $user = null)
    {
        $loanId = (int) ($data['Loan_ID'] ?? 0);
        $loan = DB::table('ga_reusable_loan')->where('id', $loanId)->first();
        if (!$loan) {
            throw new InvalidArgumentException('Data peminjaman tidak ditemukan.');
        }
        if ($loan->Status === 'RETURNED') {
            throw new InvalidArgumentException('Peminjaman ini sudah dikembalikan sebelumnya.');
        }

        $conditionIn = strtoupper(trim((string) ($data['Condition_In'] ?? $loan->Condition_Out)));
        $notes = trim((string) ($data['Return_Notes'] ?? ''));
        $now = Carbon::now();
        $username = $user ? $user->username : 'system';

        return DB::transaction(function () use ($loan, $conditionIn, $notes, $now, $username) {
            DB::table('ga_reusable_loan')->where('id', $loan->id)->update([
                'Returned_At' => $now,
                'Received_By' => $username,
                'Status' => 'RETURNED',
                'Condition_In' => $conditionIn,
                'Return_Notes' => $notes !== '' ? $notes : null,
                'Updated_By' => $username,
                'Updated_At' => $now,
            ]);

            $newLoanStatus = ($conditionIn === 'DAMAGED') ? 'MAINTENANCE' : 'AVAILABLE';

            DB::table('ga_reusable_unit')->where('id', $loan->Unit_ID)->update([
                'Condition_Status' => $conditionIn,
                'Loan_Status' => $newLoanStatus,
                'Current_Holder_User_Id' => null,
                'Current_Holder_Name' => null,
                'Current_Holder_Department' => null,
                'Updated_By' => $username,
                'Updated_At' => $now,
            ]);

            DB::table('ga_reusable_unit_history')->insert([
                'Unit_ID' => $loan->Unit_ID,
                'Action_Type' => 'RETURN',
                'Reference_No' => $loan->Loan_No,
                'From_Status' => 'ON_LOAN',
                'To_Status' => $newLoanStatus,
                'From_Location' => $loan->Location,
                'To_Location' => $loan->Location,
                'Holder_Name' => null,
                'Remarks' => 'Pengembalian unit dari ' . $loan->Borrower_Name . '. Kondisi: ' . $conditionIn . ($notes !== '' ? ' (' . $notes . ')' : ''),
                'Created_By' => $username,
                'Created_At' => $now,
            ]);

            return DB::table('ga_reusable_loan')->where('id', $loan->id)->first();
        });
    }

    /**
     * List overdue loans.
     */
    public function overdueLoans()
    {
        $today = Carbon::now()->toDateString();
        return DB::table('ga_reusable_loan as l')
            ->join('ga_reusable_unit as u', 'u.id', '=', 'l.Unit_ID')
            ->where('l.Status', 'ACTIVE')
            ->where('l.Due_Date', '<', $today)
            ->select('l.*', 'u.Unit_Code', 'u.Product_Code')
            ->orderBy('l.Due_Date', 'ASC')
            ->get();
    }

    /**
     * Record stock adjustment with price and sync to legacy supplies + GA stock ledger.
     */
    public function recordStockAdjustment(array $data, $user = null)
    {
        $productCode = trim((string) ($data['Product_Code'] ?? ''));
        $location = trim((string) ($data['Location'] ?? ''));
        $whId = trim((string) ($data['WH_ID'] ?? ''));
        $type = strtoupper(trim((string) ($data['Type'] ?? ''))); // IN atau OUT
        $qty = round((float) ($data['Qty'] ?? 0), 4);
        $reason = trim((string) ($data['Reason'] ?? 'Stock Opname / Adjustment'));
        $username = $user ? $user->username : 'system';

        if ($productCode === '' || $location === '' || $qty <= 0 || !in_array($type, ['IN', 'OUT'], true)) {
            throw new InvalidArgumentException('Product_Code, Location, Type (IN/OUT), dan Qty (> 0) wajib diisi.');
        }

        return DB::transaction(function () use ($productCode, $location, $whId, $type, $qty, $reason, $username, $user, $data) {
            $supply = DB::table('supplies')
                ->where('Product_Code', $productCode)
                ->where('Location', $location)
                ->first();

            $systemQty = (float) DB::table('supplies')
                ->where('Product_Code', $productCode)
                ->where('Location', $location)
                ->sum('Qty');

            // Ambil WAC terkini dari ledger atau fallback harga supplies / product_code
            $lastBalance = $this->balanceAsOf($productCode, $location, $whId);
            $wac = $lastBalance ? (float) $lastBalance->Avg_Cost : 0;
            if ($wac <= 0 && $supply) {
                $wac = (float) $supply->UnitPrice;
            }
            if ($wac <= 0) {
                $prd = DB::table('product_code')->where('Product_Code', $productCode)->first();
                $wac = $prd ? (float) $prd->UnitPrice : 0;
            }

            // Jika ada input spesifik harga unit cost untuk Adjustment IN, prioritaskan input
            if ($type === 'IN' && !empty($data['Unit_Cost']) && (float) $data['Unit_Cost'] > 0) {
                $unitCost = round((float) $data['Unit_Cost'], 4);
            } else {
                $unitCost = round($wac, 4);
            }

            $totalValue = round($qty * $unitCost, 4);
            $adjustmentNo = 'ADJ/' . Carbon::now()->format('YmdHis') . '/' . mt_rand(100, 999);
            $now = Carbon::now();

            if ($type === 'OUT') {
                if ($systemQty < $qty) {
                    throw new InvalidArgumentException("Stok sistem ($systemQty) tidak mencukupi untuk adjustment keluar ($qty).");
                }
                $physicalQty = $systemQty - $qty;

                // Kurangi supplies
                DB::table('supplies')
                    ->where('Product_Code', $productCode)
                    ->where('Location', $location)
                    ->where('Qty', '>', 0)
                    ->limit(1)
                    ->decrement('Qty', $qty);

                // Catat ke GA Ledger
                $this->record([
                    'Movement_Date' => $now->toDateString(),
                    'Movement_Type' => self::TYPE_ADJUSTMENT,
                    'Product_Code' => $productCode,
                    'Location' => $location,
                    'WH_ID' => $whId,
                    'Qty_In' => 0,
                    'Qty_Out' => $qty,
                    'Unit_Cost' => $unitCost,
                    'Source_Type' => 'STOCK_ADJUSTMENT',
                    'Source_ID' => $adjustmentNo,
                    'Source_Line_ID' => $adjustmentNo . '|OUT|' . $productCode,
                    'Department' => $data['Department'] ?? 'GA',
                    'Reason' => $reason,
                ], $user);
            } else {
                $physicalQty = $systemQty + $qty;

                // Tambah atau update supplies
                if ($supply) {
                    DB::table('supplies')->where('ID', $supply->ID)->increment('Qty', $qty);
                } else {
                    $prd = DB::table('product_code')->where('Product_Code', $productCode)->first();
                    DB::table('supplies')->insert([
                        'Supplies_Type' => $prd ? $prd->Category : 'CONSUMABLE',
                        'Location' => $location,
                        'WH_ID' => $whId !== '' ? $whId : null,
                        'Product_Code' => $productCode,
                        'Product_Description' => $prd ? $prd->Description : $productCode,
                        'Specification' => $prd ? $prd->Specification : '',
                        'Qty' => $qty,
                        'Unit' => $prd ? $prd->Unit_Name : 'Pcs',
                        'UnitPrice' => $unitCost,
                        'Status' => 'GOOD',
                        'User_Trx' => $username,
                        'Remark' => "Stock Adjustment IN via {$adjustmentNo}",
                    ]);
                }

                // Catat ke GA Ledger
                $this->record([
                    'Movement_Date' => $now->toDateString(),
                    'Movement_Type' => self::TYPE_ADJUSTMENT,
                    'Product_Code' => $productCode,
                    'Location' => $location,
                    'WH_ID' => $whId,
                    'Qty_In' => $qty,
                    'Qty_Out' => 0,
                    'Unit_Cost' => $unitCost,
                    'Source_Type' => 'STOCK_ADJUSTMENT',
                    'Source_ID' => $adjustmentNo,
                    'Source_Line_ID' => $adjustmentNo . '|IN|' . $productCode,
                    'Department' => $data['Department'] ?? 'GA',
                    'Reason' => $reason,
                ], $user);
            }

            // Simpan audit header adjustment
            $adjId = DB::table('ga_stock_adjustment')->insertGetId([
                'Adjustment_No' => $adjustmentNo,
                'Adjustment_Date' => $now->toDateString(),
                'Product_Code' => $productCode,
                'Location' => $location,
                'WH_ID' => $whId,
                'Type' => $type,
                'Qty' => $qty,
                'System_Qty' => $systemQty,
                'Physical_Qty' => $physicalQty,
                'Unit_Cost' => $unitCost,
                'Total_Value' => $totalValue,
                'Reason' => $reason,
                'Status' => 'COMPLETED',
                'Department' => $data['Department'] ?? 'GA',
                'Created_By' => $username,
                'Created_At' => $now,
                'Updated_By' => $username,
                'Updated_At' => $now,
            ]);

            return DB::table('ga_stock_adjustment')->where('id', $adjId)->first();
        });
    }

    /**
     * Generate Draft Purchase Request from critical low-stock items.
     */
    public function generateDraftPurchaseRequest(array $productCodes, array $data = [], $user = null)
    {
        $productCodes = array_values(array_unique(array_filter($productCodes)));
        if (empty($productCodes)) {
            throw new InvalidArgumentException('Pilih minimal satu barang untuk membuat draft PR.');
        }

        $now = Carbon::now();
        $date = $now->toDateString();
        $location = !empty($data['Location']) ? (string) $data['Location'] : ($user && !empty($user->Location) ? $user->Location : 'HO');
        $department = !empty($data['Department']) ? (string) $data['Department'] : 'GA';
        $username = $user ? $user->username : 'system';
        $userFullName = $user && !empty($user->Name) ? $user->Name : $username;

        return DB::transaction(function () use ($productCodes, $data, $now, $date, $location, $department, $username, $userFullName) {
            $count = DB::table('purchase_request')->where('PR_Date', $date)->count() + 1;
            $prNo = 'PR/GA/' . $now->format('Ymd') . '/' . sprintf('%04d', $count);

            $totalBudget = 0;
            $itemsToInsert = [];

            foreach ($productCodes as $code) {
                $product = DB::table('product_code')->where('Product_Code', $code)->first();
                if (!$product) continue;

                // Ambil target atau selisih minimum stok
                $minStock = DB::table('ga_item_minimum_stock')
                    ->where('Product_Code', $code)
                    ->where('Location', $location)
                    ->first();

                $currentQty = (float) DB::table('supplies')
                    ->where('Product_Code', $code)
                    ->where('Location', $location)
                    ->sum('Qty');

                $targetQty = $minStock && $minStock->Target_Qty > 0 ? (float) $minStock->Target_Qty : (($minStock ? (float) $minStock->Min_Qty : 0) + 10);
                $orderQty = max(1, $targetQty - $currentQty);
                $unitPrice = (float) ($product->UnitPrice > 0 ? $product->UnitPrice : $product->Price);
                $subtotal = $orderQty * $unitPrice;
                $totalBudget += $subtotal;

                $itemsToInsert[] = [
                    'PR_No' => $prNo,
                    'Purchase_Category' => $product->Category ?: 'CONSUMABLE',
                    'Purchase_Type' => $product->Supplies_Type ?: 'GENERAL',
                    'Category' => $product->Main_Category ?: 'CONSUMABLE',
                    'Product_Code' => $product->Product_Code,
                    'Product_Desc' => $product->Description ?: $product->Product_Code,
                    'Specification' => $product->Specification ?: '',
                    'Qty' => $orderQty,
                    'Default_Price' => $unitPrice,
                    'Prices' => $unitPrice,
                    'Unit' => $product->Unit_Name ?: 'Pcs',
                    'Location' => $location,
                    'Remark' => 'Auto-generated dari GA Low Stock alert',
                ];
            }

            if (empty($itemsToInsert)) {
                throw new InvalidArgumentException('Tidak ada data barang yang valid untuk diproses.');
            }

            $prId = DB::table('purchase_request')->insertGetId([
                'PR_No' => $prNo,
                'PR_Date' => $date,
                'Location' => $location,
                'Department' => $department,
                'Department_Request' => $department,
                'Request_By' => $username,
                'User_Request' => $username,
                'User_Request_Name' => $userFullName,
                'Category' => 'CONSUMABLE',
                'Status' => 'DRAFT',
                'Processing_Status' => 'DRAFT',
                'Estimated_Budget' => $totalBudget,
                'Note' => $data['Note'] ?? 'Permintaan pembelian otomatis dari sistem GA Low Stock',
                'Created_Date' => $now,
                'Created_By' => $username,
            ]);

            foreach ($itemsToInsert as $item) {
                DB::table('purchase_request_dtl')->insert($item);
            }

            return DB::table('purchase_request')->where('ID', $prId)->first();
        });
    }
}
