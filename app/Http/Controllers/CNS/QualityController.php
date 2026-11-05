<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use SimpleXMLElement;

class QualityController extends Controller
{
    private $now;

    public function __construct()
    {
        $this->now = Carbon::now();
    }

    public function externalIndexPage()
    {
        $category = 'EXTERNAL';
        $contracts = $this->getContracts();

        return view('cns.quality.index', compact('contracts', 'category'));
    }

    public function internalIndexPage()
    {
        $category = 'INTERNAL';
        $contracts = $this->getContracts();

        return view('cns.quality.index', compact('contracts', 'category'));
    }

    public function qualityControlIndexPage()
    {
        $category = 'ALL';
        $contracts = $this->getContracts();

        return view('cns.quality.index', compact('contracts', 'category'));
    }

    public function insertQualityControlPage()
    {
        $contracts = $this->getContracts();
        $category = request()->query('category', 'EXTERNAL');

        return view('cns.quality.create', compact('contracts', 'category'));
    }

    public function bookings(Request $request)
    {
        $contractId = $request->query('contract_id');
        if (!$contractId) {
            return response()->json([]);
        }

        $bookings = DB::table('cns_buyer_contract_bookings')
            ->where('Contract_ID', $contractId)
            ->orderBy('Label')
            ->get();

        return response()->json($bookings->map(function ($b) {
            return [
                'id'         => (int) $b->ID,
                'label'      => $b->Label,
                'booking_no' => $b->Booking_No,
            ];
        }));
    }

    private function getContracts()
    {
        if (!Schema::hasTable('cns_buyer_contracts')) {
            return collect();
        }

        $query = DB::table('cns_buyer_contracts')
            ->orderBy('Contract_No');

        if (auth()->check() && !empty(auth()->user()->Location)) {
            $query->where('Location', auth()->user()->Location);
        }

        $contracts = (clone $query)->where('Status', 'ACTIVE_IN_PROGRESS')->get();
        if ($contracts->isEmpty()) {
            $contracts = $query->get();
        }

        return $contracts;
    }

    public function insertQualityControl(Request $r)
    {
        $date = Carbon::parse($r->Created_At ?? $this->now)->toDateString();
        $qcCategory = strtoupper($r->input('QC_Category', 'EXTERNAL'));
        $qcType = strtoupper($r->input('QC_Type', 'INBOUND'));
        $contractId = $r->filled('Contract_ID') ? $r->Contract_ID : null;
        $bookingId = $r->filled('Booking_ID') ? $r->Booking_ID : null;

        $kodeQc = $r->input('Kode_QC');
        if (empty($kodeQc)) {
            $prefixType = ($qcType === 'OUTBOUND') ? 'OUT' : 'IN';
            $prefixCat = ($qcCategory === 'INTERNAL') ? 'INT' : 'EXT';
            $datePart = Carbon::parse($date)->format('Ymd');
            $existingCount = DB::table('cns_quality_controls')
                ->whereDate('Inspection_Date', $date)
                ->whereNotNull('Kode_QC')
                ->count();
            $nextSeq = str_pad($existingCount + 1, 3, '0', STR_PAD_LEFT);
            $kodeQc = "QC/{$prefixType}/{$prefixCat}/{$datePart}/{$nextSeq}";
        }

        $attachmentPath = null;
        if ($r->hasFile('Attachment')) {
            $file = $r->file('Attachment');
            $year = $this->now->format('Y');
            $month = $this->now->format('m');
            $extension = $file->getClientOriginalExtension();
            $filename = (string) Str::uuid() . '.' . $extension;
            $destination = public_path("filevendor/{$year}/{$month}");

            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }

            $file->move($destination, $filename);
            $attachmentPath = "filevendor/{$year}/{$month}/{$filename}";
        }

        DB::transaction(function () use ($r, $date, $qcType, $qcCategory, $contractId, $bookingId, $kodeQc, $attachmentPath) {
            $headerPayload = [
                'Inspection_Date' => $date,
                'QC_Type'         => $qcType,
                'QC_Category'     => $qcCategory,
                'Contract_ID'     => $contractId,
                'Booking_ID'      => $bookingId,
                'Notes'           => $r->Description,
                'Is_Deleted'      => 0,
                'updated_at'      => $this->now,
            ];
            if ($attachmentPath) {
                $headerPayload['Attachment_Path'] = $attachmentPath;
            }

            $header = DB::table('cns_quality_controls')
                ->where('Kode_QC', $kodeQc)
                ->first();

            if ($header) {
                DB::table('cns_quality_controls')
                    ->where('id', $header->id)
                    ->update($headerPayload);
                $headerId = $header->id;
            } else {
                $headerId = DB::table('cns_quality_controls')->insertGetId(array_merge($headerPayload, [
                    'Kode_QC'    => $kodeQc,
                    'created_at' => $this->now,
                    'Created_By' => auth()->check() ? (auth()->user()->Username ?? auth()->user()->name) : null,
                ]));
            }

            $levelsMap = ['top' => 'TOP', 'middle' => 'MIDDLE', 'bottom' => 'BOTTOM'];
            $paramKeys = ['FFA_As_Palmitic', 'FFA_As_Oleic', 'MIU', 'Sulphur', 'IV', 'Total_Chlorides', 'Phosphorus', 'Total_Metals'];

            $paramVals = [];
            foreach ($paramKeys as $pk) {
                $paramVals[$pk] = [];
            }

            foreach ($levelsMap as $prefix => $level) {
                $detailPayload = [
                    'updated_at' => $this->now,
                ];

                foreach ($paramKeys as $pk) {
                    $fld = "{$prefix}_{$pk}";
                    $val = $r->filled($fld) ? $r->input($fld) : null;
                    if ($pk === 'MIU' && $val === null) {
                        $mc = $r->input("{$prefix}_MC");
                        $imp = $r->input("{$prefix}_IMP");
                        if ($mc !== null && $imp !== null) {
                            $val = round((float)$mc + (float)$imp, 2);
                        }
                    }
                    $detailPayload[$pk] = $val;
                    if ($val !== null && $val !== '') {
                        $paramVals[$pk][] = (float)$val;
                    }
                }

                DB::table('cns_quality_control_details')->updateOrInsert(
                    [
                        'Quality_Control_ID' => $headerId,
                        'Sample_Level'       => $level,
                    ],
                    array_merge($detailPayload, [
                        'created_at' => $this->now,
                    ])
                );
            }

            // AVERAGE row (4th detail row)
            $avgPayload = [
                'updated_at' => $this->now,
            ];
            foreach ($paramKeys as $pk) {
                $avgPayload[$pk] = count($paramVals[$pk]) > 0
                    ? round(array_sum($paramVals[$pk]) / count($paramVals[$pk]), 2)
                    : null;
            }

            DB::table('cns_quality_control_details')->updateOrInsert(
                [
                    'Quality_Control_ID' => $headerId,
                    'Sample_Level'       => 'AVERAGE',
                ],
                array_merge($avgPayload, [
                    'created_at' => $this->now,
                ])
            );
        });

        return response()->json(['status' => 'success', 'message' => 'Berhasil melakukan input']);
    }

    public function getQualityControlData(Request $r)
    {
        $query = DB::table('cns_quality_controls as qc')
            ->where('qc.Is_Deleted', 0);

        if ($r->filled('category') && strtoupper($r->category) !== 'ALL') {
            $query->where('qc.QC_Category', strtoupper($r->category));
        }

        if ($r->filled('type') && strtoupper($r->type) !== 'ALL') {
            $query->where('qc.QC_Type', strtoupper($r->type));
        }

        if ($r->filled('Date')) {
            $query->whereDate('qc.Inspection_Date', Carbon::parse($r->Date)->toDateString());
        }

        $headers = $query->orderBy('qc.Inspection_Date', 'desc')
            ->orderBy('qc.id', 'desc')
            ->get();

        $headerIds = $headers->pluck('id')->all();
        $details = DB::table('cns_quality_control_details')
            ->whereIn('Quality_Control_ID', $headerIds)
            ->get()
            ->groupBy('Quality_Control_ID');

        $xml = new SimpleXMLElement('<rows/>');
        $counter = 1;

        foreach ($headers as $header) {
            $dtl = $details->get($header->id, collect())->keyBy('Sample_Level');
            $top    = $dtl->get('TOP');
            $middle = $dtl->get('MIDDLE');
            $bottom = $dtl->get('BOTTOM');
            $avg    = $dtl->get('AVERAGE');

            $attachmentLink = $header->Attachment_Path ? $r->root() . '/' . $header->Attachment_Path : null;

            $item = $xml->addChild('row');
            $item->addAttribute('id', (string) $header->id);

            // 0: Counter, 1: Kode QC, 2: Tanggal, 3: Jenis QC, 4: Lab
            $item->addChild('cell', $counter);
            $item->addChild('cell', $header->Kode_QC ?? '-');
            $item->addChild('cell', $header->Inspection_Date ? Carbon::parse($header->Inspection_Date)->format('d-M-Y') : '-');
            $item->addChild('cell', $header->QC_Type);
            $item->addChild('cell', $header->QC_Category);

            // TOP (5..12)
            $item->addChild('cell', $top ? $top->FFA_As_Palmitic : null);
            $item->addChild('cell', $top ? $top->FFA_As_Oleic    : null);
            $item->addChild('cell', ($top && $top->MIU !== null) ? $top->MIU . '%' : null);
            $item->addChild('cell', $top ? $top->Sulphur         : null);
            $item->addChild('cell', $top ? $top->IV              : null);
            $item->addChild('cell', $top ? $top->Total_Chlorides : null);
            $item->addChild('cell', $top ? $top->Phosphorus      : null);
            $item->addChild('cell', $top ? $top->Total_Metals    : null);

            // MIDDLE (13..20)
            $item->addChild('cell', $middle ? $middle->FFA_As_Palmitic : null);
            $item->addChild('cell', $middle ? $middle->FFA_As_Oleic    : null);
            $item->addChild('cell', ($middle && $middle->MIU !== null) ? $middle->MIU . '%' : null);
            $item->addChild('cell', $middle ? $middle->Sulphur         : null);
            $item->addChild('cell', $middle ? $middle->IV              : null);
            $item->addChild('cell', $middle ? $middle->Total_Chlorides : null);
            $item->addChild('cell', $middle ? $middle->Phosphorus      : null);
            $item->addChild('cell', $middle ? $middle->Total_Metals    : null);

            // BOTTOM (21..28)
            $item->addChild('cell', $bottom ? $bottom->FFA_As_Palmitic : null);
            $item->addChild('cell', $bottom ? $bottom->FFA_As_Oleic    : null);
            $item->addChild('cell', ($bottom && $bottom->MIU !== null) ? $bottom->MIU . '%' : null);
            $item->addChild('cell', $bottom ? $bottom->Sulphur         : null);
            $item->addChild('cell', $bottom ? $bottom->IV              : null);
            $item->addChild('cell', $bottom ? $bottom->Total_Chlorides : null);
            $item->addChild('cell', $bottom ? $bottom->Phosphorus      : null);
            $item->addChild('cell', $bottom ? $bottom->Total_Metals    : null);

            // AVERAGE (29..36)
            $item->addChild('cell', $avg ? $avg->FFA_As_Palmitic : null);
            $item->addChild('cell', $avg ? $avg->FFA_As_Oleic    : null);
            $item->addChild('cell', ($avg && $avg->MIU !== null) ? $avg->MIU . '%' : null);
            $item->addChild('cell', $avg ? $avg->Sulphur         : null);
            $item->addChild('cell', $avg ? $avg->IV              : null);
            $item->addChild('cell', $avg ? $avg->Total_Chlorides : null);
            $item->addChild('cell', $avg ? $avg->Phosphorus      : null);
            $item->addChild('cell', $avg ? $avg->Total_Metals    : null);

            // 37: Attachment, 38: Contract_ID, 39: Booking_ID, 40: QC_Category
            $item->addChild('cell', $attachmentLink);
            $item->addChild('cell', $header->Contract_ID);
            $item->addChild('cell', $header->Booking_ID);
            $item->addChild('cell', $header->QC_Category);

            $counter++;
        }

        return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
    }

    public function editQualityControl(Request $r)
    {
        $kodeQc = $r->input('Kode_QC');
        $header = DB::table('cns_quality_controls')
            ->where('Kode_QC', $kodeQc)
            ->first();

        if (!$header) {
            return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);
        }

        $headerId = $header->id;

        DB::transaction(function () use ($r, $headerId) {
            $headerUpdate = [
                'updated_at' => $this->now,
            ];
            if (strtoupper($r->input('QC_Type', '')) === 'OUTBOUND') {
                $headerUpdate['Contract_ID'] = $r->filled('Contract_ID') ? $r->Contract_ID : null;
                $headerUpdate['Booking_ID'] = $r->filled('Booking_ID') ? $r->Booking_ID : null;
            }
            DB::table('cns_quality_controls')->where('id', $headerId)->update($headerUpdate);

            $levelsMap = ['top' => 'TOP', 'middle' => 'MIDDLE', 'bottom' => 'BOTTOM'];
            $paramKeys = ['FFA_As_Palmitic', 'FFA_As_Oleic', 'MIU', 'Sulphur', 'IV', 'Total_Chlorides', 'Phosphorus', 'Total_Metals'];

            $paramVals = [];
            foreach ($paramKeys as $pk) {
                $paramVals[$pk] = [];
            }

            foreach ($levelsMap as $prefix => $level) {
                $detailPayload = [
                    'updated_at' => $this->now,
                ];

                foreach ($paramKeys as $pk) {
                    $fld = "{$prefix}_{$pk}";
                    $val = $r->filled($fld) ? $r->input($fld) : null;
                    $detailPayload[$pk] = $val;
                    if ($val !== null && $val !== '') {
                        $paramVals[$pk][] = (float)$val;
                    }
                }

                DB::table('cns_quality_control_details')->updateOrInsert(
                    [
                        'Quality_Control_ID' => $headerId,
                        'Sample_Level'       => $level,
                    ],
                    array_merge($detailPayload, [
                        'created_at' => $this->now,
                    ])
                );
            }

            // AVERAGE row
            $avgPayload = [
                'updated_at' => $this->now,
            ];
            foreach ($paramKeys as $pk) {
                $avgPayload[$pk] = count($paramVals[$pk]) > 0
                    ? round(array_sum($paramVals[$pk]) / count($paramVals[$pk]), 2)
                    : null;
            }

            DB::table('cns_quality_control_details')->updateOrInsert(
                [
                    'Quality_Control_ID' => $headerId,
                    'Sample_Level'       => 'AVERAGE',
                ],
                array_merge($avgPayload, [
                    'created_at' => $this->now,
                ])
            );
        });

        return response()->json(['status' => 'success']);
    }

    public function deleteQualityControl(Request $r)
    {
        $query = DB::table('cns_quality_controls');

        if ($r->filled('Kode_QC')) {
            $query->where('Kode_QC', $r->Kode_QC);
        } elseif ($r->filled('Date')) {
            $date = Carbon::parse($r->Date)->toDateString();
            $query->whereDate('Inspection_Date', $date);
            if ($r->filled('QC_Category')) {
                $query->where('QC_Category', strtoupper($r->QC_Category));
            }
            if ($r->filled('QC_Type')) {
                $query->where('QC_Type', strtoupper($r->QC_Type));
            }
        }

        $query->update(['Is_Deleted' => 1]);

        return response()->json(['status' => 'success']);
    }

    public function printQualityControl(Request $r)
    {
        $code = $r->input('code');
        $date = Carbon::parse($r->input('date'))->toDateString();
        $qcType = strtoupper($r->input('qc_type', 'INBOUND'));
        $qcCategory = strtoupper($r->input('category', 'EXTERNAL'));

        $query = DB::table('cns_quality_controls')
            ->where('Is_Deleted', 0);

        if (!empty($code) && $code !== '-') {
            $query->where('Kode_QC', $code);
        } else {
            $query->whereDate('Inspection_Date', $date)
                ->where('QC_Category', $qcCategory)
                ->where('QC_Type', $qcType);
        }

        $header = $query->first();

        $top = null;
        $middle = null;
        $bottom = null;

        if ($header) {
            $details = DB::table('cns_quality_control_details')
                ->where('Quality_Control_ID', $header->id)
                ->get()
                ->keyBy('Sample_Level');

            $top = $details->get('TOP');
            $middle = $details->get('MIDDLE');
            $bottom = $details->get('BOTTOM');
            $codeVal = $header->Kode_QC ?? '-';
            $qcType = $header->QC_Type;
            $qcCategory = $header->QC_Category;
            $date = $header->Inspection_Date;
        } else {
            $codeVal = '-';
        }

        $templateFile = ($qcType === 'OUTBOUND' && file_exists(public_path('template-form-qc-outbound.docx')))
            ? public_path('template-form-qc-outbound.docx')
            : public_path('template-form-qc.docx');

        $template = new \PhpOffice\PhpWord\TemplateProcessor($templateFile);

        $template->setValue('code',                  $codeVal);
        $template->setValue('inspection_date',        Carbon::parse($date)->format('d F Y'));
        $template->setValue('inspection_date ',       Carbon::parse($date)->format('d F Y'));
        $template->setValue('inspector',              $r->input('inspector', '-'));
        $template->setValue('qc_type',                $qcType);
        $template->setValue('qc_category',            $qcCategory);

        $template->setValue('ffa_top_palmitic',       $top    ? $top->FFA_As_Palmitic    : '-');
        $template->setValue('ffa_top_oleic',          $top    ? $top->FFA_As_Oleic       : '-');
        $template->setValue('ffa_top_moisture',       '-');
        $template->setValue('ffa_top_impurities',     '-');
        $template->setValue('ffa_top_impurities ',    '-');
        $template->setValue('miu_top',                ($top && $top->MIU !== null) ? $top->MIU : '-');

        $template->setValue('ffa_mid_palmitic',       $middle ? $middle->FFA_As_Palmitic : '-');
        $template->setValue('ffa_mid_oleic',          $middle ? $middle->FFA_As_Oleic    : '-');
        $template->setValue('ffa_mid_moisture',       '-');
        $template->setValue('ffa_mid_impurities',     '-');
        $template->setValue('miu_mid',                ($middle && $middle->MIU !== null) ? $middle->MIU : '-');

        $template->setValue('ffa_bot_palmitic',       $bottom ? $bottom->FFA_As_Palmitic : '-');
        $template->setValue('ffa_bot_oleic',          $bottom ? $bottom->FFA_As_Oleic    : '-');
        $template->setValue('ffa_bot_moisture',       '-');
        $template->setValue('ffa_bot _impurities',    '-');
        $template->setValue('miu_bot',                ($bottom && $bottom->MIU !== null) ? $bottom->MIU : '-');

        $template->setValue('iv_top',                 $top    ? ($top->IV ?? '-') : '-');
        $template->setValue('sulphur_top',            $top    ? ($top->Sulphur ?? '-') : '-');
        $template->setValue('phosphorus_top',         $top    ? ($top->Phosphorus ?? '-') : '-');
        $template->setValue('chlorides_top',          $top    ? ($top->Total_Chlorides ?? '-') : '-');
        $template->setValue('metals_top',             $top    ? ($top->Total_Metals ?? '-') : '-');

        $template->setValue('iv_mid',                 $middle ? ($middle->IV ?? '-') : '-');
        $template->setValue('sulphur_mid',            $middle ? ($middle->Sulphur ?? '-') : '-');
        $template->setValue('phosphorus_mid',         $middle ? ($middle->Phosphorus ?? '-') : '-');
        $template->setValue('chlorides_mid',          $middle ? ($middle->Total_Chlorides ?? '-') : '-');
        $template->setValue('metals_mid',             $middle ? ($middle->Total_Metals ?? '-') : '-');

        $template->setValue('iv_bot',                 $bottom ? ($bottom->IV ?? '-') : '-');
        $template->setValue('sulphur_bot',            $bottom ? ($bottom->Sulphur ?? '-') : '-');
        $template->setValue('phosphorus_bot',         $bottom ? ($bottom->Phosphorus ?? '-') : '-');
        $template->setValue('chlorides_bot',          $bottom ? ($bottom->Total_Chlorides ?? '-') : '-');
        $template->setValue('metals_bot',             $bottom ? ($bottom->Total_Metals ?? '-') : '-');

        $filename = 'QC-' . $qcCategory . '-' . $qcType . '-' . $date . '.docx';
        $tempPath = storage_path('app/temp/' . $filename);

        if (!file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        $template->saveAs($tempPath);

        return response()->download($tempPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }
}
