<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ISCCTraceabilityController extends Controller
{
    const PHOTO_TOPIC = 'TRACEABILITY_EXTERNAL';
    const PHOTO_MAX = 3;

    /* ----------------------------------------------------------------- views */

    public function index()
    {
        $sites = DB::table('traceability_external_poo_fragments')
            ->whereNotNull('site')->where('site', '<>', '')
            ->distinct()->orderBy('site')->pluck('site');
        return view('cns.iscc-traceability.index', compact('sites'));
    }

    public function utama()
    {
        return view('cns.iscc-traceability.utama');
    }

    public function utamaDetail(Request $request)
    {
        $id = $request->get('id');
        return view('cns.iscc-traceability.utama-detail', compact('id'));
    }

    /* ------------------------------------------------- server-side datatables */

    /**
     * Index grid (fragments) - EXTERNAL + NATURAL.
     */
    public function fragmentData(Request $request)
    {
        $source   = $request->get('source');
        $city     = $request->get('city');
        $site     = $request->get('site');
        $dateFrom = $request->get('date_from');
        $dateTo   = $request->get('date_to');

        $query = $this->fragmentUnion($source ?: 'ALL', $city, $dateFrom, $dateTo, $site);

        return $this->dataTables($request, $query, [
            'searchable' => ['poo_name', 'poo_type', 'city', 'province', 'wo_code', 'po_code', 'do_code', 'source'],
            'order'      => [
                1 => 'source', 2 => 'poo_name', 3 => 'poo_type', 4 => 'site', 5 => 'city', 6 => 'province',
                7 => 'plat_no', 8 => 'driver_name', 9 => 'quantity_kg', 10 => 'unit', 11 => 'price', 12 => 'total_price',
                13 => 'wo_code', 14 => 'wo_date', 15 => 'wo_status', 16 => 'po_code', 17 => 'po_date',
                18 => 'po_status', 19 => 'do_code', 20 => 'do_date', 21 => 'do_status',
            ],
            'defaultOrder' => 'poo_name',
            'map' => function ($r, $no) {
                return [
                    'no' => $no,
                    'source' => $r->source,
                    'poo_name' => $r->poo_name,
                    'poo_type' => $r->poo_type,
                    'site' => $r->site,
                    'city' => $r->city,
                    'province' => $r->province,
                    'plat_no' => $r->plat_no,
                    'driver_name' => $r->driver_name,
                    'quantity_kg' => $r->quantity_kg,
                    'unit' => $r->unit,
                    'price' => $r->price,
                    'total_price' => $r->total_price,
                    'wo_code' => $r->wo_code,
                    'wo_date' => $this->fmtDate($r->wo_date),
                    'wo_status' => $r->wo_status,
                    'po_code' => $r->po_code,
                    'po_date' => $this->fmtDate($r->po_date),
                    'po_status' => $r->po_status,
                    'do_code' => $r->do_code,
                    'do_date' => $this->fmtDate($r->do_date),
                    'do_status' => $r->do_status,
                    'fragment_id' => $r->fragment_id,
                ];
            },
        ]);
    }

    /**
     * Utama grid (deliveries).
     */
    public function deliveryData(Request $request)
    {
        $city     = $request->get('city');
        $dateFrom = $request->get('date_from');
        $dateTo   = $request->get('date_to');

        $query = DB::table('traceability_external_deliveries')
            ->whereIn('status', ['LOCKED', 'AUDITED'])
            ->when($city, function ($q) use ($city) { return $q->where('city', $city); })
            ->when($dateFrom, function ($q) use ($dateFrom) { return $q->whereDate('delivery_date', '>=', $dateFrom); })
            ->when($dateTo, function ($q) use ($dateTo) { return $q->whereDate('delivery_date', '<=', $dateTo); })
            ->select(
                DB::raw("'GREES' as source"),
                'id as delivery_id',
                'supplier_name',
                'city',
                'province',
                'delivery_date',
                'total_quantity_kg',
                'status',
                'api_reference as reference'
            );

        return $this->dataTables($request, $query, [
            'searchable' => ['supplier_name', 'city', 'province', 'api_reference'],
            'order' => [
                1 => 'source', 2 => 'supplier_name', 3 => 'city', 4 => 'province',
                5 => 'delivery_date', 6 => 'total_quantity_kg', 7 => 'status', 8 => 'api_reference',
            ],
            'defaultOrder' => 'delivery_date',
            'map' => function ($r, $no) {
                return [
                    'no' => $no,
                    'source' => $r->source,
                    'supplier_name' => $r->supplier_name,
                    'city' => $r->city,
                    'province' => $r->province,
                    'delivery_date' => $this->fmtDate($r->delivery_date),
                    'total_quantity_kg' => $r->total_quantity_kg,
                    'status' => $r->status,
                    'reference' => $r->reference,
                    'delivery_id' => $r->delivery_id,
                ];
            },
        ]);
    }

    /**
     * Fragment grid scoped to one delivery (utama detail).
     */
    public function deliveryFragmentData(Request $request)
    {
        $deliveryId = $request->get('id');

        $query = DB::table('traceability_external_poo_fragments as tep')
            ->join('traceability_external_deliveries as ted', 'ted.id', '=', 'tep.traceability_external_delivery_id')
            ->whereIn('ted.status', ['LOCKED', 'AUDITED'])
            ->where('ted.id', $deliveryId)
            ->select(
                DB::raw("'GREES' as source"),
                'tep.poo_name as poo_name',
                'tep.poo_type as poo_type',
                'tep.site as site',
                'tep.city as city',
                'tep.province as province',
                'ted.plat_no as plat_no',
                'ted.driver_name as driver_name',
                'tep.quantity_kg as quantity_kg',
                'tep.unit as unit',
                'tep.price as price',
                DB::raw('COALESCE(tep.total_price, tep.price * tep.quantity_kg) as total_price'),
                'tep.wo_code as wo_code',
                'tep.wo_date as wo_date',
                'tep.wo_status as wo_status',
                'tep.po_code as po_code',
                'tep.po_date as po_date',
                'tep.po_status as po_status',
                'tep.do_code as do_code',
                'tep.do_date as do_date',
                'tep.do_status as do_status',
                'tep.id as fragment_id'
            );

        return $this->dataTables($request, $query, [
            'searchable' => ['tep.poo_name', 'tep.poo_type', 'tep.city', 'tep.province', 'tep.wo_code', 'tep.po_code', 'tep.do_code'],
            'order' => [
                2 => 'poo_name', 3 => 'poo_type', 4 => 'site', 5 => 'city', 6 => 'province',
                7 => 'plat_no', 8 => 'driver_name', 9 => 'quantity_kg', 10 => 'unit', 11 => 'price', 12 => 'total_price',
                13 => 'wo_code', 14 => 'wo_date', 15 => 'wo_status', 16 => 'po_code', 17 => 'po_date',
                18 => 'po_status', 19 => 'do_code', 20 => 'do_date', 21 => 'do_status',
            ],
            'defaultOrder' => 'tep.id',
            'map' => function ($r, $no) {
                return [
                    'no' => $no,
                    'source' => $r->source,
                    'poo_name' => $r->poo_name,
                    'poo_type' => $r->poo_type,
                    'site' => $r->site,
                    'city' => $r->city,
                    'province' => $r->province,
                    'plat_no' => $r->plat_no,
                    'driver_name' => $r->driver_name,
                    'quantity_kg' => $r->quantity_kg,
                    'unit' => $r->unit,
                    'price' => $r->price,
                    'total_price' => $r->total_price,
                    'wo_code' => $r->wo_code,
                    'wo_date' => $this->fmtDate($r->wo_date),
                    'wo_status' => $r->wo_status,
                    'po_code' => $r->po_code,
                    'po_date' => $this->fmtDate($r->po_date),
                    'po_status' => $r->po_status,
                    'do_code' => $r->do_code,
                    'do_date' => $this->fmtDate($r->do_date),
                    'do_status' => $r->do_status,
                    'fragment_id' => $r->fragment_id,
                ];
            },
        ]);
    }

    /**
     * Excel export of the full filtered fragment set.
     */
    public function exportFragments(Request $request)
    {
        $source   = $request->get('source');
        $city     = $request->get('city');
        $site     = $request->get('site');
        $dateFrom = $request->get('date_from');
        $dateTo   = $request->get('date_to');
        $search   = $request->get('search');

        $query = $this->fragmentUnion($source ?: 'ALL', $city, $dateFrom, $dateTo, $site);

        if ($search !== null && $search !== '') {
            $query->where(function ($q) use ($search) {
                foreach (['poo_name', 'poo_type', 'city', 'province', 'wo_code', 'po_code', 'do_code', 'source'] as $col) {
                    $q->orWhere($col, 'like', "%{$search}%");
                }
            });
        }

        $rows = $query->orderBy('poo_name', 'asc')->get();

        $headers = ['No', 'Source', 'POO Name', 'Type', 'Site', 'City', 'Province', 'Plat Nomor', 'Driver Name', 'Qty (Kg)', 'Unit', 'Price', 'Total Price', 'WO Code', 'WO Date', 'WO Status', 'PO Code', 'PO Date', 'PO Status', 'Surat Jalan Code', 'Surat Jalan Date', 'Surat Jalan Status'];
        $data = [];
        $i = 1;
        foreach ($rows as $r) {
            $data[] = [$i++, $r->source, $r->poo_name, $r->poo_type, $r->site, $r->city, $r->province, $r->plat_no, $r->driver_name, $r->quantity_kg, $r->unit, $r->price, $r->total_price, $r->wo_code, $this->fmtDate($r->wo_date), $r->wo_status, $r->po_code, $this->fmtDate($r->po_date), $r->po_status, $r->do_code, $this->fmtDate($r->do_date), $r->do_status];
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($headers, null, 'A1');
        if ($data) { $sheet->fromArray($data, null, 'A2'); }

        $dir = storage_path('app/temp');
        if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
        $path = $dir . '/iscc_traceability_' . date('YmdHis') . '.xlsx';

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($path);

        return response()->download($path, 'ISCC_Traceability.xlsx')->deleteFileAfterSend(true);
    }

    /* --------------------------------------------------------------- helpers */

    /**
     * Build the fragment union (EXTERNAL + NATURAL) as a queryable subquery.
     */
    protected function fragmentUnion(string $source, ?string $city, ?string $dateFrom, ?string $dateTo, ?string $site = null)
    {
        $ext = DB::table('traceability_external_poo_fragments as tep')
            ->join('traceability_external_deliveries as ted', 'ted.id', '=', 'tep.traceability_external_delivery_id')
            ->whereIn('ted.status', ['LOCKED', 'AUDITED'])
            ->when($city, function ($q) use ($city) { return $q->where('tep.city', $city); })
            ->when($site, function ($q) use ($site) { return $q->where('tep.site', $site); })
            ->when($dateFrom, function ($q) use ($dateFrom) { return $q->whereDate('ted.delivery_date', '>=', $dateFrom); })
            ->when($dateTo, function ($q) use ($dateTo) { return $q->whereDate('ted.delivery_date', '<=', $dateTo); })
            ->select(
                DB::raw("'GREES' as source"),
                'tep.poo_name as poo_name',
                'tep.poo_type as poo_type',
                'tep.site as site',
                'tep.city as city',
                'tep.province as province',
                'ted.plat_no as plat_no',
                'ted.driver_name as driver_name',
                'tep.quantity_kg as quantity_kg',
                'tep.unit as unit',
                'tep.price as price',
                DB::raw('COALESCE(tep.total_price, tep.price * tep.quantity_kg) as total_price'),
                'tep.wo_code as wo_code',
                'tep.wo_date as wo_date',
                'tep.wo_status as wo_status',
                'tep.po_code as po_code',
                'tep.po_date as po_date',
                'tep.po_status as po_status',
                'tep.do_code as do_code',
                'tep.do_date as do_date',
                'tep.do_status as do_status',
                'tep.id as fragment_id'
            );

        $nat = DB::table('master_poo as mp')
            ->leftJoin('work_order as wo', 'wo.POO_Id', '=', 'mp.ID')
            ->leftJoin('pickup_order as po', 'po.WO_Id', '=', 'wo.ID')
            ->leftJoin('delivery_order as do', 'do.ID', '=', 'po.DO_Id')
            ->where('mp.Is_Active', 1)
            ->whereNotNull('wo.Code')->where('wo.Code', '<>', '')
            ->whereNotNull('po.Code')->where('po.Code', '<>', '')
            ->whereNotNull('do.Code')->where('do.Code', '<>', '')
            ->when($city, function ($q) use ($city) { return $q->where('mp.City', $city); })
            ->when($dateFrom, function ($q) use ($dateFrom) { return $q->whereDate('wo.Work_Order_Date', '>=', $dateFrom); })
            ->when($dateTo, function ($q) use ($dateTo) { return $q->whereDate('wo.Work_Order_Date', '<=', $dateTo); })
            ->select(
                DB::raw("'BDP' as source"),
                'mp.Name as poo_name',
                'mp.Type as poo_type',
                DB::raw('NULL as site'),
                'mp.City as city',
                'mp.Province as province',
                DB::raw('NULL as plat_no'),
                DB::raw('NULL as driver_name'),
                'po.Qty as quantity_kg',
                DB::raw('NULL as unit'),
                DB::raw('NULL as price'),
                DB::raw('NULL as total_price'),
                'wo.Code as wo_code',
                'wo.Work_Order_Date as wo_date',
                'wo.Status as wo_status',
                'po.Code as po_code',
                'po.Pickup_At as po_date',
                'po.Status as po_status',
                'do.Code as do_code',
                'do.DO_Date as do_date',
                'do.DO_Status as do_status',
                DB::raw("NULL as fragment_id")
            );

        $src = strtoupper((string) $source);
        $showBdp = (bool) config('services.traceability.show_bdp', false);

        if ($src === 'GREES') {
            return DB::query()->fromSub($ext, 'u');
        }

        if ($src === 'BDP') {
            // BDP hidden until real MITRA data exists (show_bdp flag); no site on NATURAL
            if (!$showBdp || $site) {
                return DB::query()->fromSub($nat, 'u')->whereRaw('1=0');
            }
            return DB::query()->fromSub($nat, 'u');
        }

        // ALL / empty
        if (!$showBdp || $site) {
            return DB::query()->fromSub($ext, 'u');
        }

        return DB::query()->fromSub($ext->unionAll($nat), 'u');
    }

    /**
     * Generic server-side DataTables response.
     */
    protected function dataTables(Request $request, $query, array $cfg)
    {
        $draw   = (int) $request->input('draw', 0);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);

        $total = (clone $query)->count();

        $search = $request->input('search.value');
        if ($search !== null && $search !== '') {
            $query->where(function ($q) use ($search, $cfg) {
                foreach (($cfg['searchable'] ?? []) as $col) {
                    $q->orWhere($col, 'like', "%{$search}%");
                }
            });
        }

        $filtered = (clone $query)->count();

        $colIdx = (int) $request->input('order.0.column', 0);
        $dir    = strtolower((string) $request->input('order.0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $orderCol = $cfg['order'][$colIdx] ?? ($cfg['defaultOrder'] ?? 'poo_name');
        $query->orderBy($orderCol, $dir);

        if ($length >= 0) {
            $query->offset($start)->limit($length);
        }

        $rows = $query->get();

        $data = [];
        $no = $start;
        foreach ($rows as $r) {
            $data[] = ($cfg['map'])($r, ++$no);
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data,
        ]);
    }

    /* ------------------------------------------------------------ edit/photos */

    public function edit($fragmentId)
    {
        $fragment = DB::table('traceability_external_poo_fragments as tep')
            ->join('traceability_external_deliveries as ted', 'ted.id', '=', 'tep.traceability_external_delivery_id')
            ->where('tep.id', $fragmentId)
            ->select('tep.*', 'ted.supplier_name', 'ted.delivery_date', 'ted.status as delivery_status')
            ->first();

        if (!$fragment) {
            abort(404, 'Fragment not found.');
        }

        $photos = DB::table('meta_files')
            ->where('Topic', self::PHOTO_TOPIC)
            ->where('Map_To', (string) $fragmentId)
            ->orderBy('ID', 'asc')
            ->get();

        return view('cns.iscc-traceability.edit', compact('fragment', 'photos'));
    }

    public function update(Request $request)
    {
        $fragmentId = $request->input('fragment_id');
        $fragment = DB::table('traceability_external_poo_fragments')->find($fragmentId);

        if (!$fragment) {
            return response()->json(['status' => 'error', 'message' => 'Fragment not found.'], 404);
        }

        $request->validate([
            'quantity_kg' => 'nullable|numeric|min:0',
            'oil_documentation' => 'nullable|string',
            'lat' => 'nullable|string|max:50',
            'lng' => 'nullable|string|max:50',
            'full_address' => 'nullable|string',
        ]);

        $quantity = $request->input('quantity_kg');
        $totalPrice = null;
        if ($quantity !== null && $fragment->price !== null) {
            $totalPrice = round((float) $fragment->price * (float) $quantity, 2);
        }

        DB::table('traceability_external_poo_fragments')
            ->where('id', $fragmentId)
            ->update([
                'quantity_kg' => $quantity,
                'po_qty' => $quantity,
                'total_price' => $totalPrice,
                'oil_documentation' => $request->input('oil_documentation'),
                'lat' => $request->input('lat'),
                'lng' => $request->input('lng'),
                'full_address' => $request->input('full_address'),
                'updated_at' => now(),
            ]);

        return response()->json(['status' => 'success', 'message' => 'Fragment updated.']);
    }

    public function uploadPhoto(Request $request)
    {
        $fragmentId = $request->input('fragment_id');
        $fragment = DB::table('traceability_external_poo_fragments')->find($fragmentId);

        if (!$fragment) {
            return response()->json(['status' => 'error', 'message' => 'Fragment not found.'], 404);
        }

        $existingCount = DB::table('meta_files')
            ->where('Topic', self::PHOTO_TOPIC)
            ->where('Map_To', (string) $fragmentId)
            ->count();

        $request->validate([
            'photos' => 'required|array|max:' . (self::PHOTO_MAX - $existingCount),
            'photos.*' => 'image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $year = now()->format('Y');
        $month = now()->format('m');
        $dir = public_path("filevendor/{$year}/{$month}");
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $uploaded = 0;
        foreach ($request->file('photos') as $file) {
            if (!$file->isValid()) {
                continue;
            }

            $ext = $file->getClientOriginalExtension();
            $name = 'trace_' . $fragmentId . '_' . time() . '_' . Str::random(8) . '.' . $ext;
            $file->move($dir, $name);

            DB::table('meta_files')->insert([
                'Filename' => $name,
                'Path' => "{$year}/{$month}/{$name}",
                'Extension' => $ext,
                'Description' => 'Traceability Oil Documentation',
                'Topic' => self::PHOTO_TOPIC,
                'Map_To' => (string) $fragmentId,
                'Created_At' => now(),
            ]);

            $uploaded++;
        }

        return response()->json(['status' => 'success', 'message' => "{$uploaded} photo(s) uploaded."]);
    }

    public function deletePhoto(Request $request)
    {
        $photoId = $request->input('photo_id');
        $photo = DB::table('meta_files')->find($photoId);

        if (!$photo || $photo->Topic !== self::PHOTO_TOPIC) {
            return response()->json(['status' => 'error', 'message' => 'Photo not found.'], 404);
        }

        $fullPath = public_path('filevendor/' . $photo->Path);
        if (file_exists($fullPath)) {
            @unlink($fullPath);
        }

        DB::table('meta_files')->where('ID', $photoId)->delete();

        return response()->json(['status' => 'success', 'message' => 'Photo deleted.']);
    }

    public function markAudited(Request $request)
    {
        $deliveryId = $request->input('delivery_id');
        $updated = DB::table('traceability_external_deliveries')
            ->where('id', $deliveryId)
            ->whereIn('status', ['LOCKED', 'PENDING'])
            ->update(['status' => 'AUDITED', 'updated_at' => now()]);

        if ($updated) {
            return response()->json(['status' => 'success', 'message' => 'Delivery marked as audited.']);
        }

        return response()->json(['status' => 'error', 'message' => 'Delivery not found.'], 404);
    }

    /* ------------------------------------------------------------- print doc */

    public function printDoc($fragmentId)
    {
        $fragment = DB::table('traceability_external_poo_fragments as tep')
            ->join('traceability_external_deliveries as ted', 'ted.id', '=', 'tep.traceability_external_delivery_id')
            ->where('tep.id', $fragmentId)
            ->select('tep.*', 'ted.plat_no', 'ted.ttd', 'ted.photos')
            ->first();

        if (!$fragment) {
            abort(404, 'Fragment not found.');
        }

        $qty   = (float) $fragment->quantity_kg;
        $price = (float) ($fragment->price ?? 0);
        $total = isset($fragment->total_price) && $fragment->total_price !== null
            ? (float) $fragment->total_price
            : $price * $qty;

        $templatePath = public_path('template_iscc.docx');
        if (!is_file($templatePath)) {
            abort(500, 'template_iscc.docx not found.');
        }

        $t = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);

        $t->setValues([
            'name'             => (string) ($fragment->poo_name ?? ''),
            'address'          => (string) ($fragment->full_address ?? ''),
            'city'             => (string) ($fragment->city ?? ''),
            'lat'              => (string) ($fragment->lat ?? ''),
            'lng'              => (string) ($fragment->lng ?? ''),
            'phone'            => (string) ($fragment->phone ?? ''),
            'account_no'       => (string) ($fragment->account_no ?? ''),
            'account_name'     => (string) ($fragment->account_name ?? ''),
            'type'             => (string) ($fragment->poo_type ?? ''),
            'kode'             => (string) ($fragment->do_code ?? ''),
            'po_no'            => (string) ($fragment->po_code ?? ''),
            'platno'           => (string) ($fragment->plat_no ?? ''),
            'created'          => (string) ($this->fmtDate($fragment->do_date) ?? ''),
            'tfdate'           => (string) ($this->fmtDate($fragment->do_date) ?? ''),
            'total'            => $this->num($qty),
            'price'            => $this->num($price),
            'total_price'      => $this->num($total),
            'totalprice'       => $this->num($total),
            'totalpricecalled' => $this->terbilang($total),
            'tricode'          => '',
        ]);

        $photo = $this->firstPhoto($fragment);
        $tmp = $photo ? $this->downloadToTemp($photo) : null;
        if ($tmp) {
            try {
                $t->setImageValue('oil_doc', ['path' => $tmp, 'width' => 220, 'height' => 160]);
            } catch (\Throwable $e) {
                $t->setValue('oil_doc', '');
            }
        } else {
            $t->setValue('oil_doc', '');
        }

        $tmpTtd = !empty($fragment->ttd) ? $this->downloadToTemp($fragment->ttd) : null;
        if ($tmpTtd) {
            try {
                $t->setImageValue('sign_pict', ['path' => $tmpTtd, 'width' => 140, 'height' => 70]);
            } catch (\Throwable $e) {
                $t->setValue('sign_pict', '');
            }
        } else {
            $t->setValue('sign_pict', '');
        }

        $outDir = storage_path('app/temp');
        if (!is_dir($outDir)) { @mkdir($outDir, 0775, true); }
        $outFile = $outDir . '/iscc_fragment_' . $fragmentId . '_' . time() . '.docx';
        $t->saveAs($outFile);

        $downloadName = 'ISCC_' . preg_replace('/[^A-Za-z0-9\-]/', '_', (string) $fragment->poo_name) . '_' . $fragmentId . '.docx';

        return response()->download($outFile, $downloadName)->deleteFileAfterSend(true);
    }

    protected function firstPhoto($fragment): ?string
    {
        if (!empty($fragment->photos)) {
            $arr = json_decode((string) $fragment->photos, true);
            if (is_array($arr) && !empty($arr[0])) { return (string) $arr[0]; }
        }
        if (!empty($fragment->oil_documentation)) {
            return (string) $fragment->oil_documentation;
        }
        return null;
    }

    /**
     * Download an image and return a local path in a PHPWord-supported format (JPEG).
     * Unsupported formats (e.g. webp) are converted to JPEG with a white background.
     * Returns null on failure (caller omits the image).
     */
    protected function downloadToTemp(string $url, ?string $ext = null): ?string
    {
        try {
            $client = new \GuzzleHttp\Client(['timeout' => 30, 'verify' => false]);
            $res = $client->get($url);
            if ($res->getStatusCode() !== 200) { return null; }
            $body = (string) $res->getBody();
            if ($body === '') { return null; }

            $dir = storage_path('app/temp');
            if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
            $base = $dir . '/iscc_img_' . md5($url);

            $info = @getimagesizefromstring($body);
            $mime = is_array($info) ? ($info['mime'] ?? null) : null;

            // already JPEG -> write directly
            if ($mime === 'image/jpeg') {
                $path = $base . '.jpg';
                file_put_contents($path, $body);
                return is_file($path) ? $path : null;
            }

            // try convert to JPEG (webp/avif/etc.)
            $path = $base . '.jpg';
            if ($this->convertToJpeg($body, $path)) {
                return is_file($path) ? $path : null;
            }

            // supported non-jpeg formats PHPWord can embed natively
            $map = ['image/png' => '.png', 'image/gif' => '.gif', 'image/bmp' => '.bmp'];
            if ($mime && isset($map[$mime])) {
                $p = $base . $map[$mime];
                file_put_contents($p, $body);
                return is_file($p) ? $p : null;
            }

            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Convert arbitrary image bytes to JPEG (white background). GD first, Imagick fallback.
     */
    protected function convertToJpeg(string $body, string $path): bool
    {
        if (function_exists('imagecreatefromstring')) {
            $img = @imagecreatefromstring($body);
            if ($img !== false) {
                $w = imagesx($img);
                $h = imagesy($img);
                $canvas = imagecreatetruecolor($w, $h);
                $white = imagecolorallocate($canvas, 255, 255, 255);
                imagefilledrectangle($canvas, 0, 0, $w, $h, $white);
                imagecopy($canvas, $img, 0, 0, 0, 0, $w, $h);
                $ok = imagejpeg($canvas, $path, 90);
                imagedestroy($canvas);
                imagedestroy($img);
                if ($ok) { return true; }
            }
        }

        if (extension_loaded('imagick')) {
            try {
                $im = new \Imagick();
                $im->readImageBlob($body);
                $im->setImageBackgroundColor('white');
                if (defined('\Imagick::ALPHACHANNEL_REMOVE')) {
                    $im->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE);
                }
                $im->setImageFormat('jpeg');
                $im->setImageCompressionQuality(90);
                $ok = $im->writeImage($path);
                $im->clear();
                if ($ok) { return true; }
            } catch (\Throwable $e) {
                return false;
            }
        }

        return false;
    }

    protected function fmtDate($v): ?string
    {
        return ($v === null || $v === '') ? null : date('d-M-Y', strtotime((string) $v));
    }

    protected function num($v): string
    {
        return number_format((float) $v, 2, ',', '.');
    }

    protected function terbilang($number): string
    {
        $n = (int) round((float) $number);
        $words = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        $spell = function ($x) use (&$spell, $words) {
            if ($x < 12) return $words[$x];
            if ($x < 20) return $spell($x - 10) . ' belas';
            if ($x < 100) return $spell(intdiv($x, 10)) . ' puluh' . ($x % 10 ? ' ' . $spell($x % 10) : '');
            if ($x < 200) return 'seratus' . ($x - 100 ? ' ' . $spell($x - 100) : '');
            if ($x < 1000) return $spell(intdiv($x, 100)) . ' ratus' . ($x % 100 ? ' ' . $spell($x % 100) : '');
            if ($x < 2000) return 'seribu' . ($x - 1000 ? ' ' . $spell($x - 1000) : '');
            if ($x < 1000000) return $spell(intdiv($x, 1000)) . ' ribu' . ($x % 1000 ? ' ' . $spell($x % 1000) : '');
            if ($x < 1000000000) return $spell(intdiv($x, 1000000)) . ' juta' . ($x % 1000000 ? ' ' . $spell($x % 1000000) : '');
            if ($x < 1000000000000) return $spell(intdiv($x, 1000000000)) . ' miliar' . ($x % 1000000000 ? ' ' . $spell($x % 1000000000) : '');
            return $spell(intdiv($x, 1000000000000)) . ' triliun' . ($x % 1000000000000 ? ' ' . $spell($x % 1000000000000) : '');
        };

        if ($n === 0) { return 'nol'; }
        return trim($spell($n)) . ' rupiah';
    }
}
