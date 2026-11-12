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
        $tanks = $this->getTanks();

        return view('cns.quality.index', compact('contracts', 'category', 'tanks'));
    }

    public function internalIndexPage()
    {
        $category = 'INTERNAL';
        $contracts = $this->getContracts();
        $tanks = $this->getTanks();

        return view('cns.quality.index', compact('contracts', 'category', 'tanks'));
    }

    public function qualityControlIndexPage()
    {
        $category = 'ALL';
        $contracts = $this->getContracts();
        $tanks = $this->getTanks();

        return view('cns.quality.index', compact('contracts', 'category', 'tanks'));
    }

    public function insertQualityControlPage()
    {
        $contracts = $this->getContracts();
        $tanks = $this->getTanks();
        $category = request()->query('category', 'EXTERNAL');

        return view('cns.quality.create', compact('contracts', 'category', 'tanks'));
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

    private function getTanks()
    {
        if (!Schema::hasTable('cns_master_tanks')) {
            return collect();
        }

        $query = DB::table('cns_master_tanks')
            ->where('status', 'ACTIVE');

        if (Schema::hasColumn('cns_master_tanks', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        return $query->orderBy('name', 'asc')->get();
    }

    public function insertQualityControl(Request $r)
    {
        $date = Carbon::parse($r->Created_At ?? $this->now)->toDateString();
        $qcCategory = strtoupper($r->input('QC_Category', 'EXTERNAL'));
        $qcType = strtoupper($r->input('QC_Type', 'INBOUND'));
        $contractId = ($qcType === 'OUTBOUND' && $r->filled('Contract_ID')) ? $r->Contract_ID : null;
        $bookingId = ($qcType === 'OUTBOUND' && $r->filled('Booking_ID')) ? $r->Booking_ID : null;
        $tankId = ($qcType === 'DAILY_QC' && $r->filled('Tank_ID')) ? $r->Tank_ID : null;

        if ($qcType === 'DAILY_QC' && empty($tankId)) {
            return response()->json(['status' => 'error', 'message' => 'Nama Tangki wajib dipilih untuk Daily QC'], 422);
        }

        $kodeQc = $r->input('Kode_QC');
        if (empty($kodeQc)) {
            if ($qcType === 'DAILY_QC') {
                $prefixType = 'DLY';
            } elseif ($qcType === 'OUTBOUND') {
                $prefixType = 'OUT';
            } else {
                $prefixType = 'IN';
            }
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

        DB::transaction(function () use ($r, $date, $qcType, $qcCategory, $contractId, $bookingId, $tankId, $kodeQc, $attachmentPath) {
            $headerPayload = [
                'Inspection_Date' => $date,
                'QC_Type'         => $qcType,
                'QC_Category'     => $qcCategory,
                'Contract_ID'     => $contractId,
                'Booking_ID'      => $bookingId,
                'Tank_ID'         => $tankId,
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
            $item->addChild('cell', $header->SOURCE);

            $fmtQc = function ($v, $suffix = '') {
                return ($v !== null && $v !== '') ? number_format((float) $v, 2, '.', ',') . $suffix : null;
            };

            // TOP (6..13)
            $item->addChild('cell', $top ? $fmtQc($top->FFA_As_Palmitic) : null);
            $item->addChild('cell', $top ? $fmtQc($top->FFA_As_Oleic)    : null);
            $item->addChild('cell', $top ? $fmtQc($top->MIU, '%')        : null);
            $item->addChild('cell', $top ? $fmtQc($top->Sulphur)         : null);
            $item->addChild('cell', $top ? $fmtQc($top->IV)              : null);
            $item->addChild('cell', $top ? $fmtQc($top->Total_Chlorides) : null);
            $item->addChild('cell', $top ? $fmtQc($top->Phosphorus)      : null);
            $item->addChild('cell', $top ? $fmtQc($top->Total_Metals)    : null);

            // MIDDLE (14..21)
            $item->addChild('cell', $middle ? $fmtQc($middle->FFA_As_Palmitic) : null);
            $item->addChild('cell', $middle ? $fmtQc($middle->FFA_As_Oleic)    : null);
            $item->addChild('cell', $middle ? $fmtQc($middle->MIU, '%')        : null);
            $item->addChild('cell', $middle ? $fmtQc($middle->Sulphur)         : null);
            $item->addChild('cell', $middle ? $fmtQc($middle->IV)              : null);
            $item->addChild('cell', $middle ? $fmtQc($middle->Total_Chlorides) : null);
            $item->addChild('cell', $middle ? $fmtQc($middle->Phosphorus)      : null);
            $item->addChild('cell', $middle ? $fmtQc($middle->Total_Metals)    : null);

            // BOTTOM (22..29)
            $item->addChild('cell', $bottom ? $fmtQc($bottom->FFA_As_Palmitic) : null);
            $item->addChild('cell', $bottom ? $fmtQc($bottom->FFA_As_Oleic)    : null);
            $item->addChild('cell', $bottom ? $fmtQc($bottom->MIU, '%')        : null);
            $item->addChild('cell', $bottom ? $fmtQc($bottom->Sulphur)         : null);
            $item->addChild('cell', $bottom ? $fmtQc($bottom->IV)              : null);
            $item->addChild('cell', $bottom ? $fmtQc($bottom->Total_Chlorides) : null);
            $item->addChild('cell', $bottom ? $fmtQc($bottom->Phosphorus)      : null);
            $item->addChild('cell', $bottom ? $fmtQc($bottom->Total_Metals)    : null);

            // AVERAGE (30..37)
            $item->addChild('cell', $avg ? $fmtQc($avg->FFA_As_Palmitic) : null);
            $item->addChild('cell', $avg ? $fmtQc($avg->FFA_As_Oleic)    : null);
            $item->addChild('cell', $avg ? $fmtQc($avg->MIU, '%')        : null);
            $item->addChild('cell', $avg ? $fmtQc($avg->Sulphur)         : null);
            $item->addChild('cell', $avg ? $fmtQc($avg->IV)              : null);
            $item->addChild('cell', $avg ? $fmtQc($avg->Total_Chlorides) : null);
            $item->addChild('cell', $avg ? $fmtQc($avg->Phosphorus)      : null);
            $item->addChild('cell', $avg ? $fmtQc($avg->Total_Metals)    : null);

            // 38: Attachment, 39: Contract_ID, 40: Booking_ID, 41: QC_Category, 42: Tank_ID
            $item->addChild('cell', $attachmentLink);
            $item->addChild('cell', $header->Contract_ID);
            $item->addChild('cell', $header->Booking_ID);
            $item->addChild('cell', $header->QC_Category);
            $item->addChild('cell', $header->Tank_ID ?? null);

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
        $qcType = strtoupper($r->input('QC_Type', $header->QC_Type ?? 'INBOUND'));
        $tankId = ($qcType === 'DAILY_QC' && $r->filled('Tank_ID')) ? $r->Tank_ID : null;

        if ($qcType === 'DAILY_QC' && empty($tankId)) {
            return response()->json(['status' => 'error', 'message' => 'Nama Tangki wajib dipilih untuk Daily QC'], 422);
        }

        DB::transaction(function () use ($r, $headerId, $qcType, $tankId) {
            $headerUpdate = [
                'QC_Type'    => $qcType,
                'Tank_ID'    => $tankId,
                'updated_at' => $this->now,
            ];
            if ($qcType === 'OUTBOUND') {
                $headerUpdate['Contract_ID'] = $r->filled('Contract_ID') ? $r->Contract_ID : null;
                $headerUpdate['Booking_ID'] = $r->filled('Booking_ID') ? $r->Booking_ID : null;
            } else {
                $headerUpdate['Contract_ID'] = null;
                $headerUpdate['Booking_ID'] = null;
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

    public function getMonthlyStats(Request $r)
    {
        $month = $r->input('month', Carbon::now()->format('Y-m'));
        try {
            $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth()->toDateString();
            $endDate = Carbon::createFromFormat('Y-m', $month)->endOfMonth()->toDateString();
        } catch (\Exception $e) {
            $startDate = Carbon::now()->startOfMonth()->toDateString();
            $endDate = Carbon::now()->endOfMonth()->toDateString();
            $month = Carbon::now()->format('Y-m');
        }

        $query = DB::table('cns_quality_controls as qc')
            ->join('cns_quality_control_details as d', 'd.Quality_Control_ID', '=', 'qc.id')
            ->where('qc.Is_Deleted', 0)
            ->where('d.Sample_Level', 'AVERAGE')
            ->whereBetween('qc.Inspection_Date', [$startDate, $endDate]);

        if ($r->filled('category') && strtoupper($r->category) !== 'ALL') {
            $query->where('qc.QC_Category', strtoupper($r->category));
        }

        if ($r->filled('type') && strtoupper($r->type) !== 'ALL') {
            $query->where('qc.QC_Type', strtoupper($r->type));
        }

        $raw = $query->selectRaw('
            MIN(d.FFA_As_Palmitic) as min_ffa_palmitic,
            MAX(d.FFA_As_Palmitic) as max_ffa_palmitic,
            MIN(d.FFA_As_Oleic) as min_ffa_oleic,
            MAX(d.FFA_As_Oleic) as max_ffa_oleic,
            MIN(d.MIU) as min_miu,
            MAX(d.MIU) as max_miu,
            MIN(d.Sulphur) as min_sulphur,
            MAX(d.Sulphur) as max_sulphur,
            MIN(d.IV) as min_iv,
            MAX(d.IV) as max_iv,
            MIN(d.Total_Chlorides) as min_chlorides,
            MAX(d.Total_Chlorides) as max_chlorides,
            MIN(d.Phosphorus) as min_phosphorus,
            MAX(d.Phosphorus) as max_phosphorus,
            MIN(d.Total_Metals) as min_metals,
            MAX(d.Total_Metals) as max_metals,
            COUNT(DISTINCT qc.id) as total_tests
        ')->first();

        $params = [
            [
                'key'   => 'ffa_palmitic',
                'name'  => 'FFA Palmitic',
                'unit'  => '%',
                'min'   => $raw && $raw->min_ffa_palmitic !== null ? (float) $raw->min_ffa_palmitic : null,
                'max'   => $raw && $raw->max_ffa_palmitic !== null ? (float) $raw->max_ffa_palmitic : null,
            ],
            [
                'key'   => 'ffa_oleic',
                'name'  => 'FFA Oleic',
                'unit'  => '%',
                'min'   => $raw && $raw->min_ffa_oleic !== null ? (float) $raw->min_ffa_oleic : null,
                'max'   => $raw && $raw->max_ffa_oleic !== null ? (float) $raw->max_ffa_oleic : null,
            ],
            [
                'key'   => 'miu',
                'name'  => 'MIU',
                'unit'  => '%',
                'min'   => $raw && $raw->min_miu !== null ? (float) $raw->min_miu : null,
                'max'   => $raw && $raw->max_miu !== null ? (float) $raw->max_miu : null,
            ],
            [
                'key'   => 'sulphur',
                'name'  => 'Sulfur',
                'unit'  => 'ppm',
                'min'   => $raw && $raw->min_sulphur !== null ? (float) $raw->min_sulphur : null,
                'max'   => $raw && $raw->max_sulphur !== null ? (float) $raw->max_sulphur : null,
            ],
            [
                'key'   => 'iv',
                'name'  => 'IV',
                'unit'  => 'g I2',
                'min'   => $raw && $raw->min_iv !== null ? (float) $raw->min_iv : null,
                'max'   => $raw && $raw->max_iv !== null ? (float) $raw->max_iv : null,
            ],
            [
                'key'   => 'chlorides',
                'name'  => 'Chlorides',
                'unit'  => 'ppm',
                'min'   => $raw && $raw->min_chlorides !== null ? (float) $raw->min_chlorides : null,
                'max'   => $raw && $raw->max_chlorides !== null ? (float) $raw->max_chlorides : null,
            ],
            [
                'key'   => 'phosphorus',
                'name'  => 'Phosphorus',
                'unit'  => 'ppm',
                'min'   => $raw && $raw->min_phosphorus !== null ? (float) $raw->min_phosphorus : null,
                'max'   => $raw && $raw->max_phosphorus !== null ? (float) $raw->max_phosphorus : null,
            ],
            [
                'key'   => 'metals',
                'name'  => 'Metals',
                'unit'  => 'ppm',
                'min'   => $raw && $raw->min_metals !== null ? (float) $raw->min_metals : null,
                'max'   => $raw && $raw->max_metals !== null ? (float) $raw->max_metals : null,
            ],
        ];

        return response()->json([
            'status'      => 'success',
            'month'       => $month,
            'total_tests' => $raw ? (int) $raw->total_tests : 0,
            'parameters'  => $params,
        ]);
    }
}
