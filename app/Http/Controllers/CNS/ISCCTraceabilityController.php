<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use App\Services\ISCCTraceabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ISCCTraceabilityController extends Controller
{
    const PHOTO_TOPIC = 'TRACEABILITY_EXTERNAL';
    const PHOTO_MAX = 3;

    public function index()
    {
        return view('cns.iscc-traceability.index');
    }

    public function data(Request $request)
    {
        $city = $request->get('city');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $source = $request->get('source');

        $rows = collect();

        if (!$source || $source === 'NATURAL') {
            $natural = DB::table('master_poo as mp')
                ->leftJoin('work_order as wo', 'wo.POO_Id', '=', 'mp.ID')
                ->leftJoin('pickup_order as po', 'po.WO_Id', '=', 'wo.ID')
                ->leftJoin('delivery_order as do', 'do.ID', '=', 'po.DO_Id')
                ->where('mp.Is_Active', 1)
                ->when($city, function ($q) use ($city) {
                    return $q->where('mp.City', $city);
                })
                ->when($dateFrom, function ($q) use ($dateFrom) {
                    return $q->whereDate('wo.Work_Order_Date', '>=', $dateFrom);
                })
                ->when($dateTo, function ($q) use ($dateTo) {
                    return $q->whereDate('wo.Work_Order_Date', '<=', $dateTo);
                })
                ->select(
                    'mp.ID as poo_id',
                    'mp.Name as poo_name',
                    'mp.Type as poo_type',
                    'mp.City as city',
                    'mp.Province as province',
                    'wo.Code as wo_code',
                    'wo.Work_Order_Date as wo_date',
                    'wo.Status as wo_status',
                    'po.Code as po_code',
                    'po.Pickup_At as po_date',
                    'po.Qty as po_qty',
                    'po.Status as po_status',
                    'do.Code as do_code',
                    'do.DO_Date as do_date',
                    'do.DO_Status as do_status'
                )
                ->get()
                ->map(function ($row) {
                    return [
                        'source' => 'NATURAL',
                        'fragment_id' => null,
                        'split' => 'Tidak',
                        'poo_name' => $row->poo_name,
                        'poo_type' => $row->poo_type,
                        'city' => $row->city,
                        'province' => $row->province,
                        'quantity_kg' => $row->po_qty,
                        'wo_code' => $row->wo_code,
                        'wo_date' => $row->wo_date,
                        'wo_status' => $row->wo_status,
                        'po_code' => $row->po_code,
                        'po_date' => $row->po_date,
                        'po_qty' => $row->po_qty,
                        'po_status' => $row->po_status,
                        'do_code' => $row->do_code,
                        'do_date' => $row->do_date,
                        'do_status' => $row->do_status,
                    ];
                });

            $rows = $rows->merge($natural);
        }

        if (!$source || $source === 'EXTERNAL') {
            $external = DB::table('traceability_external_poo_fragments as tep')
                ->join('traceability_external_deliveries as ted', 'ted.id', '=', 'tep.traceability_external_delivery_id')
                ->leftJoin(
                    DB::raw('(SELECT traceability_external_delivery_id, COUNT(*) AS frag_count FROM traceability_external_poo_fragments GROUP BY traceability_external_delivery_id) AS fcnt'),
                    'fcnt.traceability_external_delivery_id', '=', 'tep.traceability_external_delivery_id'
                )
                ->whereIn('ted.status', ['LOCKED', 'AUDITED'])
                ->when($city, function ($q) use ($city) {
                    return $q->where('tep.city', $city);
                })
                ->when($dateFrom, function ($q) use ($dateFrom) {
                    return $q->whereDate('ted.delivery_date', '>=', $dateFrom);
                })
                ->when($dateTo, function ($q) use ($dateTo) {
                    return $q->whereDate('ted.delivery_date', '<=', $dateTo);
                })
                ->select(
                    'tep.id as fragment_id',
                    'tep.poo_name',
                    'tep.poo_type',
                    'tep.quantity_kg',
                    'tep.city',
                    'tep.province',
                    'tep.wo_code',
                    'tep.wo_date',
                    'tep.wo_status',
                    'tep.po_code',
                    'tep.po_date',
                    'tep.po_qty',
                    'tep.po_status',
                    'tep.do_code',
                    'tep.do_date',
                    'tep.do_status',
                    'fcnt.frag_count'
                )
                ->get()
                ->map(function ($row) {
                    return [
                        'source' => 'EXTERNAL',
                        'fragment_id' => $row->fragment_id,
                        'split' => ($row->frag_count ?? 0) > 1 ? 'Ya' : 'Tidak',
                        'poo_name' => $row->poo_name,
                        'poo_type' => $row->poo_type ?? 'SUPPLIER',
                        'city' => $row->city,
                        'province' => $row->province,
                        'quantity_kg' => $row->quantity_kg,
                        'wo_code' => $row->wo_code,
                        'wo_date' => $row->wo_date,
                        'wo_status' => $row->wo_status,
                        'po_code' => $row->po_code,
                        'po_date' => $row->po_date,
                        'po_qty' => $row->po_qty,
                        'po_status' => $row->po_status,
                        'do_code' => $row->do_code,
                        'do_date' => $row->do_date,
                        'do_status' => $row->do_status,
                    ];
                });

            $rows = $rows->merge($external);
        }

        $rows = $rows->sortBy(function ($row) {
            return $row['city'] . '|' . $row['poo_name'];
        })->values();

        return response($this->buildXml($rows))->header('Content-Type', 'text/xml');
    }

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

        DB::table('traceability_external_poo_fragments')
            ->where('id', $fragmentId)
            ->update([
                'quantity_kg' => $request->input('quantity_kg'),
                'po_qty' => $request->input('quantity_kg'),
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

    protected function buildXml($rows)
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?><rows>';

        $i = 1;
        foreach ($rows as $row) {
            $xml .= '<row id="' . $i . '">';
            $xml .= '<cell>' . $i . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row['source']) . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row['poo_name']) . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row['poo_type'] ?? '') . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row['city'] ?? '') . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row['province'] ?? '') . '</cell>';
            $xml .= '<cell>' . htmlspecialchars((string) ($row['quantity_kg'] ?? '')) . '</cell>';
            $xml .= '<cell>' . htmlspecialchars((string) ($row['po_qty'] ?? '')) . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row['wo_code'] ?? '') . '</cell>';
            $xml .= '<cell>' . htmlspecialchars((string) ($row['wo_date'] ?? '')) . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row['wo_status'] ?? '') . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row['po_code'] ?? '') . '</cell>';
            $xml .= '<cell>' . htmlspecialchars((string) ($row['po_date'] ?? '')) . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row['po_status'] ?? '') . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row['do_code'] ?? '') . '</cell>';
            $xml .= '<cell>' . htmlspecialchars((string) ($row['do_date'] ?? '')) . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row['do_status'] ?? '') . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row['split'] ?? 'Tidak') . '</cell>';
            $xml .= '<cell>' . ($row['fragment_id'] ?? '') . '</cell>';
            $xml .= '</row>';
            $i++;
        }

        $xml .= '</rows>';

        return $xml;
    }

    public function utama()
    {
        return view('cns.iscc-traceability.utama');
    }

    public function utamaData(Request $request)
    {
        $rows = collect();

        $external = DB::table('traceability_external_deliveries')
            ->whereIn("Status", ["LOCKED", "AUDITED"])
            ->get()
            ->map(function ($row) {
                return [
                    'data' => $row->id,
                    'source' => 'EXTERNAL',
                    'supplier_name' => $row->supplier_name,
                    'city' => $row->city,
                    'province' => $row->province,
                    'delivery_date' => $row->delivery_date,
                    'total_quantity_kg' => $row->total_quantity_kg,
                    'status' => $row->status,
                    'reference' => $row->api_reference,
                ];
            });

        $rows = $rows->merge($external);

        $rows = $rows->sortBy(function ($row) {
            return $row['city'];
        })->values();

        return response($this->buildXmlUtama($rows))->header('Content-Type', 'text/xml');
    }

    public function utamaDetail(Request $request)
    {
        $id = $request->get('id');
        return view('cns.iscc-traceability.utama-detail', compact('id'));
    }

    public function utamaDetailData(Request $request)
    {
        $rows = collect();

        $external = DB::table('traceability_external_poo_fragments as tep')
            ->join('traceability_external_deliveries as ted', 'ted.id', '=', 'tep.traceability_external_delivery_id')
            ->leftJoin(
                DB::raw('(SELECT traceability_external_delivery_id, COUNT(*) AS frag_count FROM traceability_external_poo_fragments GROUP BY traceability_external_delivery_id) AS fcnt'),
                'fcnt.traceability_external_delivery_id', '=', 'tep.traceability_external_delivery_id'
            )
            ->whereIn('ted.status', ['LOCKED', 'AUDITED'])
            ->where("ted.id", $request->get('id'))
            ->select(
                'tep.id as fragment_id',
                'tep.poo_name',
                'tep.poo_type',
                'tep.quantity_kg',
                'tep.city',
                'tep.province',
                'tep.wo_code',
                'tep.wo_date',
                'tep.wo_status',
                'tep.po_code',
                'tep.po_date',
                'tep.po_qty',
                'tep.po_status',
                'tep.do_code',
                'tep.do_date',
                'tep.do_status',
                'fcnt.frag_count'
            )
            ->get()
            ->map(function ($row) {
                return [
                    'source' => 'EXTERNAL',
                    'fragment_id' => $row->fragment_id,
                    'split' => ($row->frag_count ?? 0) > 1 ? 'Ya' : 'Tidak',
                    'poo_name' => $row->poo_name,
                    'poo_type' => $row->poo_type ?? 'SUPPLIER',
                    'city' => $row->city,
                    'province' => $row->province,
                    'quantity_kg' => $row->quantity_kg,
                    'wo_code' => $row->wo_code,
                    'wo_date' => $row->wo_date,
                    'wo_status' => $row->wo_status,
                    'po_code' => $row->po_code,
                    'po_date' => $row->po_date,
                    'po_qty' => $row->po_qty,
                    'po_status' => $row->po_status,
                    'do_code' => $row->do_code,
                    'do_date' => $row->do_date,
                    'do_status' => $row->do_status,
                ];
            });

        $rows = $rows->merge($external);

        $rows = $rows->sortBy(function ($row) {
            return $row['city'] . '|' . $row['poo_name'];
        })->values();

        return response($this->buildXml($rows))->header('Content-Type', 'text/xml');
    }

    protected function buildXmlUtama($rows)
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?><rows>';

        $i = 1;
        foreach ($rows as $row) {
            $xml .= '<row id="' . $row['data'] . '">';
            $xml .= '<cell>' . $i . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row['source']) . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row['supplier_name']) . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row['city']) . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row['province']) . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row['delivery_date']) . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row['total_quantity_kg']) . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row['status']) . '</cell>';
            $xml .= '<cell>' . htmlspecialchars($row['reference']) . '</cell>';
            $xml .= '</row>';
            $i++;
        }

        $xml .= '</rows>';

        return $xml;
    }

    /**
     * Generate the per-fragment ISCC document from template_iscc.docx.
     */
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
        $total = $price * $qty;

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
            'created'          => (string) ($fragment->do_date ?? ''),
            'tfdate'           => (string) ($fragment->do_date ?? ''),
            'total'            => $this->num($qty),
            'price'            => $this->num($price),
            'total_price'      => $this->num($total),
            'totalprice'       => $this->num($total),
            'totalpricecalled' => $this->terbilang($total),
            'tricode'          => '',
        ]);

        // oil documentation image (first photo only)
        $photo = $this->firstPhoto($fragment);
        if ($photo && ($tmp = $this->downloadToTemp($photo, 'jpg'))) {
            $t->setImageValue('oil_doc', ['path' => $tmp, 'width' => 220, 'height' => 160]);
        } else {
            $t->setValue('oil_doc', '');
        }

        // signature image
        if (!empty($fragment->ttd) && ($tmp = $this->downloadToTemp($fragment->ttd, 'png'))) {
            $t->setImageValue('sign_pict', ['path' => $tmp, 'width' => 140, 'height' => 70]);
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

    protected function downloadToTemp(string $url, string $ext): ?string
    {
        try {
            $client = new \GuzzleHttp\Client(['timeout' => 30, 'verify' => false]);
            $res = $client->get($url);
            if ($res->getStatusCode() !== 200) { return null; }
            $dir = storage_path('app/temp');
            if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
            $path = $dir . '/iscc_img_' . md5($url) . '.' . $ext;
            file_put_contents($path, (string) $res->getBody());
            return is_file($path) ? $path : null;
        } catch (\Throwable $e) {
            return null;
        }
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
