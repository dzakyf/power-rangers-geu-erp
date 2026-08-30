<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Services\GeneratorService;
use App\Services\UploadService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Safe\Exceptions\SimplexmlException;
use SimpleXMLElement;
use App\UCOStoragePelindo;

class UCOController extends Controller
{

    private $userService;
    private $generatorService;
    private $uploadService;
    public function __construct(UserService $u, GeneratorService $g, UploadService $up, \App\Services\WarehouseService $ws)
    {
        $this->userService = $u;
        $this->generatorService = $g;
        $this->uploadService = $up;
        $this->warehouseService = $ws;
    }

    public function indexInboundSource()
    {
        return view('uco.report-inbound-source');
    }

    public function indexInboundUCO()
    {
        return view('uco.inbound');
    }

    public function indexOutboundUCO()
    {
        return view('uco.outbound');
    }
    public function indexOutstockUCO()
    {
        return view('uco.outstock');
    }


    public function indexUnitCategory()
    {
        return view('uco.category');
    }

    public function createInboundUCO()
    {
        return view('uco.create-inbound');
    }

    public function updateDeliveryOrderPage()
    {
        return view('partner.delivery-order.update');
    }

    public function submitUpdateDeliveryOrder(Request $r)
    {
        $poId = $r->input('PO_Id');

        if (!$poId) {
            return response()->json(['status' => 'error', 'message' => 'PO_Id is required'], 400);
        }

        try {
            DB::transaction(function () use ($r, $poId) {
                $year  = Carbon::now()->year;
                $month = Carbon::now()->format('m');

                $updateData = [];

                if ($r->filled('Actual_Qty')) {
                    $updateData['Actual_Qty'] = $r->input('Actual_Qty');
                }

                if ($r->filled('Lat')) {
                    $updateData['Lat'] = $r->input('Lat');
                }

                if ($r->filled('Lng')) {
                    $updateData['Lng'] = $r->input('Lng');
                }

                if (!empty($updateData)) {
                    DB::table('pickup_order')->where('ID', $poId)->update($updateData);
                }

                // Persist the delivery note (Surat Jalan) status for the DO the
                // PO belongs to, so it no longer stays stuck on PROGRESS.
                if ($r->filled('DO_Status')) {
                    $doId = DB::table('pickup_order')->where('ID', $poId)->value('DO_Id');
                    if ($doId) {
                        DB::table('delivery_order')->where('ID', $doId)->update([
                            'DO_Status' => $r->input('DO_Status'),
                        ]);
                    }
                }

                // Signature (base64 image)
                if ($r->filled('Signature')) {
                    $b64    = preg_replace('/^data:image\/\w+;base64,/', '', $r->input('Signature'));
                    $binary = base64_decode($b64);
                    $sigDir = public_path("filevendor/{$year}/{$month}");
                    if (!is_dir($sigDir)) {
                        mkdir($sigDir, 0777, true);
                    }
                    $sigName = "signature_{$poId}_" . time() . ".png";
                    $sigPath = "{$year}/{$month}/{$sigName}";
                    file_put_contents(public_path("filevendor/{$sigPath}"), $binary);

                    DB::table('meta_files')->insert([
                        'Filename'    => $sigName,
                        'Path'        => $sigPath,
                        'Extension'   => 'png',
                        'Description' => 'Signature',
                        'Topic'       => 'delivery-order',
                        'Map_To'      => $poId,
                        'Created_At'  => now(),
                    ]);
                }

                // Receipt photo (binary file upload)
                if ($r->hasFile('Receipt_Photo') && $r->file('Receipt_Photo')->isValid()) {
                    $file    = $r->file('Receipt_Photo');
                    $ext     = $file->getClientOriginalExtension();
                    $imgDir  = public_path("filevendor/{$year}/{$month}");
                    if (!is_dir($imgDir)) {
                        mkdir($imgDir, 0777, true);
                    }
                    $imgName = "receipt_{$poId}_" . time() . "." . $ext;
                    $imgPath = "{$year}/{$month}/{$imgName}";
                    $file->move(public_path("filevendor/{$year}/{$month}"), $imgName);

                    DB::table('meta_files')->insert([
                        'Filename'    => $imgName,
                        'Path'        => $imgPath,
                        'Extension'   => $ext,
                        'Description' => 'Receipt_Photo',
                        'Topic'       => 'delivery-order',
                        'Map_To'      => $poId,
                        'Created_At'  => now(),
                    ]);
                }

                // Weighing photos (binary file upload from webcam)
                if ($r->hasFile('weighing_photos')) {
                    foreach ($r->file('weighing_photos') as $file) {
                        if ($file->isValid()) {
                            $ext = $file->getClientOriginalExtension();
                            $wName = "weighing_{$poId}_" . time() . "_" . uniqid() . '.' . $ext;
                            $wDir = public_path("filevendor/{$year}/{$month}");
                            if (!is_dir($wDir)) { mkdir($wDir, 0777, true); }
                            $file->move($wDir, $wName);
                            $wPath = "{$year}/{$month}/{$wName}";

                            DB::table('meta_files')->insert([
                                'Filename'    => $wName,
                                'Path'        => $wPath,
                                'Extension'   => $ext,
                                'Description' => 'Weighing_Photo',
                                'Topic'       => 'delivery-order',
                                'Map_To'      => $poId,
                                'Created_At'  => now(),
                            ]);
                        }
                    }
                }
            });

            return response()->json(['status' => 'success', 'message' => 'Delivery order berhasil diupdate.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }


    public function getUCOOutboundXml()
    {
        try {
            $data = DB::table('uco_inbound as ui')
                ->select(
                    'ui.*',
                    'users.name as User_Name'
                )
                ->join('users', 'ui.Picked_Up_By', '=', 'users.id')
                ->where('ui.Status', '=', 'OUTBOUND')
                ->get();

            $xml = new SimpleXMLElement('<rows/>');

            $counter = 1;

            foreach ($data as $d) {
                $row = $xml->addChild('row');
                $row->addAttribute('ID', $d->ID);

                $row->addChild('cell', $counter);
                $row->addChild('cell', htmlspecialchars($d->WO_No));
                $row->addChild('cell', htmlspecialchars($d->POO_Name));
                $row->addChild('cell', $d->Weight);
                $row->addChild('cell', htmlspecialchars($d->Unit));
                $row->addChild('cell', $d->User_Name);
                $row->addChild('cell', $d->Created_At);
                $row->addChild('cell', $d->Outbound_Date);

                $counter++;
            }

            return response($xml->asXML(), 200)
                ->header('Content-Type', 'application/xml');
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getUcoInboundXml()
    {
        try {
            $data = DB::table('uco_inbound as ui')
                ->select(
                    'ui.*',
                    'users.name as User_Name'
                )
                ->join('users', 'ui.Picked_Up_By', '=', 'users.id')
                ->where('ui.Status', '=', 'INBOUND')
                ->get();

            $xml = new SimpleXMLElement('<rows/>');

            $counter = 1;

            foreach ($data as $d) {
                $row = $xml->addChild('row');
                $row->addAttribute('ID', $d->ID);

                $row->addChild('cell', $counter);
                $row->addChild('cell', htmlspecialchars($d->WO_No));
                $row->addChild('cell', htmlspecialchars($d->POO_Name));
                $row->addChild('cell', $d->Weight);
                $row->addChild('cell', htmlspecialchars($d->Unit));
                $row->addChild('cell', $d->User_Name);
                $row->addChild('cell', $d->Created_At);
                $row->addChild('cell', $d->Inbound_Date);

                $counter++;
            }

            return response($xml->asXML(), 200)
                ->header('Content-Type', 'application/xml');
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function submitInboundUCO(Request $request)
    {
        $b = $request->all();
        $session = $this->userService->getCurrentSessionUserInfo();

        try {
            DB::beginTransaction();

            DB::table('uco_inbound')->insert([
                'POO_Name'   => $b['POO_Name'],
                'Weight'     => $b['Weight'],
                'Picked_Up_By'    => $session->id, // Created_By
                'Unit'       => $b['Unit'],
                'Inbound_Date'       => $b['Inbound_Date'],
            ]);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Data berhasil disimpan.',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    // request pick up ke Site GEU (dari mitra(express) ke site)
    public function requestPickupUCO()
    {
        return;
    }

    public function trackExportedStockIndex()
    {
        return view('cns.inventory.index');
    }

    public function trackExportedStockCreatePage()
    {
        return view('cns.inventory.create');
    }

    public function trackStoragePelindoData(Request $r)
    {
        $month = $r->query('month');

        // if ($r->filled('ID')) {
        //     return $this->trackExportedStockDataById($r);
        // }

        $rows = UCOStoragePelindo::when(
            $month,
            fn($q) =>
            $q->whereRaw("DATE_FORMAT(Transaction_Date, '%Y-%m') = ?", [$month])
        )
            ->leftJoin('meta_files as mf', function ($join) {
                $join->on('uco_storage_pelindo.ID', '=', 'mf.Map_To')
                    ->where('mf.Topic', '=', 'uco-storage-pelindo');
            })
            ->select('uco_storage_pelindo.*', 'uco_storage_pelindo.ID as Storage_Pelindo_Id', 'mf.Path as Attachment_Path')
            ->orderBy('Transaction_Date')->get();

        $opening = 0;
        if ($month) {
            $cutoff = Carbon::createFromFormat('Y-m', $month)->startOfMonth()->toDateString();
            $opening = UCOStoragePelindo::balanceAsOf(
                Carbon::createFromFormat('Y-m', $month)->startOfMonth()->subDay()->toDateString()
            );
        }

        $xml = new SimpleXMLElement('<rows/>');

        // First row: opening balance (accumulation from months before)
        $r = $xml->addChild('row');
        $r->addChild('cell', '');
        $r->addChild('cell', 'Opening Stock');
        $r->addChild('cell', '');
        $r->addChild('cell', '');
        $r->addChild('cell', '');
        $r->addChild('cell', '');
        $r->addChild('cell', '');
        $r->addChild('cell', $opening);
        $r->addChild('cell', '');
        $r->addChild('cell', '');
        $r->addChild('cell', '');

        // Data rows — running balance starts from $opening
        $counter = 1;
        $running = $opening;


        foreach ($rows as $row) {
            $inbound  = $row->Type === 'inbound'  ? $row->Quantity : 0;
            $outbound = $row->Type === 'outbound' ? $row->Quantity : 0;
            $running  = $running + $inbound - $outbound;

            $r = $xml->addChild('row');
            $r->addAttribute('ID', $row->Storage_Pelindo_Id);
            $r->addChild('cell', $counter++);
            $r->addChild('cell', Carbon::parse($row->Transaction_Date)->format('F'));
            $r->addChild('cell', Carbon::parse($row->Transaction_Date)->format('d-M-Y'));
            $r->addChild('cell', 'Pelindo');
            $r->addChild('cell', $row->Quantity_PTU);
            $r->addChild('cell', $inbound);
            $r->addChild('cell', $outbound);
            $r->addChild('cell', $running);
            $r->addChild('cell', $row->Quantity_Sounding);
            $r->addChild('cell', $row->Notes);
            $r->addChild('cell', $row->Created_At);
            $r->addChild('cell', $row->Attachment_Path
                ? 'Link^ ' . asset("filevendor/{$row->Attachment_Path}")
                : 'Belum ada attachment');
        }

        return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
    }

    public function trackExportedStockDataById(Request $r)
    {
        $q = $r->query();

        $attachment = false;

        $vs = DB::table('vessel_schedules as vs')
            ->join('vessel_schedule_dtls as vsdtl', 'vsdtl.Task_ID', '=', 'vs.Task_ID')
            ->select('vs.*', 'vs.Created_Date as Scheduled_At', 'vsdtl.*')
            ->where('vs.id', '=', $q['ID'])
            ->get();

        $xml = new SimpleXMLElement('<rows/>');


        $counter = 1;

        foreach ($vs as $vs) {
            $row = $xml->addChild('row');

            $row->addChild('cell', $counter);
            $row->addChild('cell', $vs->Task_ID);
            $row->addChild('cell', $vs->Order_No);
            $row->addChild('cell', Carbon::parse($vs->Scheduled_At)->format('F'));
            $row->addChild('cell', Carbon::parse($vs->Scheduled_At)->format('d/m/Y'));
            $row->addChild('cell', 'Pelindo');
            $row->addChild('cell', $vs->POL);
            $row->addChild('cell', $vs->Police_No);
            $row->addChild('cell', $vs->ATD);
            $row->addChild('cell', $vs->ATA);
            $row->addChild('cell', $vs->Gross_KG);
            $row->addChild('cell', $vs->Note);

            if (!$attachment) {
                $row->addChild('cell', "Belum ada lampiran.");
            } else {
                $row->addChild('cell', "Link^ $vs->Note");
            }

            // $row->addChild('cell', $vs->Updated_By);
            // $row->addChild('cell', $vs->Updated_Date);

            $counter++;
        }

        return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
    }

    public function trackExportedStockDataJSON(Request $r)
    {
        $row = UCOStoragePelindo::leftJoin('meta_files as mf', function ($join) {
            $join->on('uco_storage_pelindo.ID', '=', 'mf.Map_To')
                ->where('mf.Topic', '=', 'uco-storage-pelindo');
        })
            ->select('uco_storage_pelindo.*', 'mf.Path as Attachment_Path')
            ->where('uco_storage_pelindo.ID', '=', $r->query('ID'))
            ->first();

        if ($row) {
            $qc = DB::table('uco_quality_control')
                ->where('Source_ID', $r->query('ID'))
                ->where('Is_Deleted', '!=', 1)
                ->get()
                ->keyBy(fn($q) => strtolower($q->Type));
            $row->qc = $qc;
        }

        return response()->json($row ? [$row] : []);
    }

    public function submitStoragePelindo(Request $r)
    {
        DB::beginTransaction();
        try {
            $model = UCOStoragePelindo::create($r->only([
                'Type',
                'Quantity',
                'Quantity_PTU',
                'Quantity_Sounding',
                'Created_By',
                'Transaction_Date',
                'Reference_Number',
                'Notes',
            ]));

            if ($r->hasFile('Attachment') && $r->file('Attachment')->isValid()) {
                $file = $r->file('Attachment');
                $ext = $file->getClientOriginalExtension();
                $year = now()->format('Y');
                $month = now()->format('m');
                $uuid = \Ramsey\Uuid\Uuid::uuid4()->toString();
                $filename = $uuid . '.' . $ext;
                $path = "{$year}/{$month}/{$filename}";

                $dir = public_path("filevendor/{$year}/{$month}");
                if (!is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }
                $file->move($dir, $filename);

                DB::table('meta_files')->insert([
                    'Filename'    => $filename,
                    'Path'        => $path,
                    'Extension'   => $ext,
                    'Description' => 'Attachment',
                    'Topic'       => 'uco-storage-pelindo',
                    'Map_To'      => $model->ID,
                    'Created_At'  => now(),
                ]);
            }

            $this->upsertInventoryQC($r, $model->ID);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Record created successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function upsertInventoryQC(Request $r, $sourceId)
    {
        $date = Carbon::parse($r->input('Transaction_Date'))->toDateString();
        $types = ['TOP' => 'top_', 'MIDDLE' => 'middle_', 'BOTTOM' => 'bottom_'];

        $attachmentPath = null;
        $filename = null;
        $extension = null;
        if ($r->hasFile('QC_Attachment') && $r->file('QC_Attachment')->isValid()) {
            $file = $r->file('QC_Attachment');
            $ext = $file->getClientOriginalExtension();
            $year = now()->format('Y');
            $month = now()->format('m');
            $filename = \Ramsey\Uuid\Uuid::uuid4()->toString() . '.' . $ext;
            $attachmentPath = "{$year}/{$month}/{$filename}";

            $dir = public_path("filevendor/{$year}/{$month}");
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            $file->move($dir, $filename);
            $extension = $ext;
        }

        foreach ($types as $type => $prefix) {
            $values = [
                'FFA_As_Palmitic' => $r->input($prefix . 'FFA_As_Palmitic'),
                'FFA_As_Oleic'    => $r->input($prefix . 'FFA_As_Oleic'),
                'MC'              => $r->input($prefix . 'MC'),
                'IMP'             => $r->input($prefix . 'IMP'),
            ];

            $values = array_map(fn($v) => ($v === '' ? null : $v), $values);

            $hasValue = array_filter($values, fn($v) => $v !== null);

            $existing = DB::table('uco_quality_control')
                ->where('Source_ID', $sourceId)
                ->where('Type', $type)
                ->first();

            if (empty($hasValue)) {
                if ($existing) {
                    DB::table('uco_quality_control')->where('ID', $existing->ID)->update($values);
                }
                continue;
            }

            $payload = $values + ['Is_Deleted' => 0];

            if ($existing) {
                DB::table('uco_quality_control')->where('ID', $existing->ID)->update($payload);
                $qcId = $existing->ID;
            } else {
                $qcId = DB::table('uco_quality_control')->insertGetId($payload + [
                    'Created_At' => $date,
                    'Type'       => $type,
                    'Source_ID'  => $sourceId,
                ]);
            }

            if ($attachmentPath) {
                DB::table('meta_files')
                    ->where('Map_To', $qcId)
                    ->where('Topic', 'uco-quality-control')
                    ->delete();

                DB::table('meta_files')->insert([
                    'Filename'    => $filename,
                    'Path'        => $attachmentPath,
                    'Extension'   => $extension,
                    'Description' => 'QC Attachment',
                    'Created_At'  => now(),
                    'Topic'       => 'uco-quality-control',
                    'Map_To'      => $qcId,
                ]);
            }
        }
    }

    public function updateExportedStock(Request $r)
    {
        DB::beginTransaction();
        try {
            UCOStoragePelindo::where('ID', $r->id)
                ->update($r->only([
                    'Type',
                    'Quantity',
                    'Quantity_PTU',
                    'Quantity_Sounding',
                    'Created_By',
                    'Transaction_Date',
                    'Reference_Number',
                    'Notes',
                ]));

            $this->upsertInventoryQC($r, $r->id);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Record updated successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteExportedStock(Request $r)
    {
        DB::table('vessel_schedules')
            ->where('id', $r->id)
            ->delete();

        return response()->json(['success' => true, 'message' => 'Record updated successfully']);
    }

    public function getPoliceLicenseNumber()
    {
        $list = DB::table('vessel_schedule_dtls as vsdtl')
            ->join('vessel_schedules as vs', 'vs.Task_ID', '=', 'vsdtl.Task_ID')
            ->whereNotNull('vsdtl.Container')
            ->where('vsdtl.Police_No', '!=', '')
            ->select('vsdtl.Police_No as police_license', 'vs.Vessel as vessel_name')
            ->distinct()
            ->get();

        return response()->json(['status' => 'success', 'data' => $list]);
    }

    public function indexWorkOrder()
    {
        return view('partner.work-order.index');
    }

    public function getWorkOrder(Request $r)
    {
        $id     = $r->query('id');
        $format = $r->query('format', 'json');
        $status = $r->query('status');

        $query = DB::table('work_order as wo')
            ->leftJoin('master_poo as mp', 'mp.ID', '=', 'wo.POO_Id')
            ->leftJoin('users as u', 'mp.Created_By', '=', 'u.id')
            ->select(
                'wo.ID',
                'wo.POO_Id',
                'mp.Name as POO_Name',
                'mp.Price as POO_Price',
                'wo.Status',
                'wo.Code',
                DB::raw("DATE_FORMAT(wo.Work_Order_Date, '%Y-%m-%d') as Work_Order_Date"),
                'wo.Created_At',
                'wo.Updated_At',
                'mp.Created_By as POO_Created_By',
                'u.Name as POO_Created_By_Name',
                'mp.City as POO_City',
                'mp.Province as POO_Province',
                'mp.Nearest_Warehouse as POO_Nearest_Warehouse'
            );

        if ($id) {
            $query->where('wo.ID', $id);
        }

        if ($status) {
            $query->where('wo.Status', $status);
        }


        $rows = $query->get();

        if ($format === 'xml') {
            $xml     = new SimpleXMLElement('<rows/>');
            $counter = 1;

            foreach ($rows as $row) {
                $item = $xml->addChild('row');
                $item->addAttribute('ID', $row->ID);

                $item->addChild('cell', $counter);
                $item->addChild('cell', htmlspecialchars((string) $row->Code));
                $item->addChild('cell', htmlspecialchars((string) $row->ID));
                $item->addChild('cell', htmlspecialchars((string) $row->POO_Name));
                $item->addChild('cell', htmlspecialchars((string) $row->Status));
                $item->addChild('cell', htmlspecialchars((string) $row->Work_Order_Date));
                $item->addChild('cell', htmlspecialchars((string) $row->Created_At));
                $item->addChild('cell', htmlspecialchars((string) $row->Updated_At));

                $counter++;
            }

            return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
        }

        return response()->json(['data' => $rows]);
    }


    public function createWorkOrder(Request $r)
    {
        return view('partner.work-order.create');
    }

    public function submitWorkOrder(Request $r)
    {
        $bypass = $r->boolean('bypass');

        try {
            DB::beginTransaction();

            $woCount = DB::table('work_order')->count();
            $woId = DB::table('work_order')->insertGetId([
                'POO_Id'          => $r->input('POO_Id'),
                'Code'            => $this->generatorService->generateApplicationCode($woCount, 'WO'),
                'Status'          => $r->input('Status'),
                // WO Date is locked to today: never trust a client-supplied date.
                'Work_Order_Date' => now(),
            ]);

            if ($bypass) {
                $doId = $r->input('DO_Id');
                if (!$doId) {
                    throw new \Exception('Delivery Order harus dipilih untuk mode bypass.');
                }

                $poo = DB::table('master_poo')->where('ID', $r->input('POO_Id'))->first();
                if (!$poo) {
                    throw new \Exception('Point of Origin tidak ditemukan.');
                }

                $poCount = DB::table('pickup_order')->count();

                DB::table('pickup_order')->insert([
                    'Code'        => $this->generatorService->generateApplicationCode($poCount, 'PO'),
                    'Type_Pickup' => $r->input('Type_Pickup'),
                    'Coverage'    => $r->input('Coverage'),
                    'Site'        => $r->input('Site', $poo->City),
                    'Package'     => $r->input('Package'),
                    'Qty'         => $r->input('Qty'),
                    'Price'       => $poo->Price,
                    'Description' => $r->input('Description'),
                    'WO_Id'       => $woId,
                    'DO_Id'       => $doId,
                    'Created_At'  => now(),
                ]);
            }

            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'Work order berhasil dibuat.']);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function indexPickupRequest()
    {
        return view('partner.pickup.index');
    }

    public function getPickupRequest(Request $r)
    {

        $id = $r->query('id');
        $rows = $this->fetchPickupOrders($id, null, null, null, $r->query('delivery_order_id'));


        if ($r->query('format') === 'xml') {
            return $this->pickupOrdersAsXml($rows);
        }

        return $this->pickupOrdersAsJson($rows);
    }

    private function fetchPickupOrders(?string $id, ?string $whid = null, ?string $date_start = null, ?string $date_end = null, ?string $delivery_order_id = null)
    {

        $query = DB::table('pickup_order as po')
            ->join('work_order as wo', 'po.WO_Id', '=', 'wo.ID')
            ->leftJoin('master_poo as mp', 'wo.POO_Id', '=', 'mp.ID')
            ->leftJoin('users as u', 'mp.Created_By', '=', 'u.id')
            ->select('po.*', 'po.Code as PO_Code', 'wo.Code as WO_Code', 'mp.Name as POO_Name', 'mp.Full_Address as POO_Full_Address', 'mp.Lat', 'mp.Lng', 'mp.Location_Coordinate', 'mp.Created_By as POO_Created_By', 'u.Name as POO_Created_By_Name');

        if ($id) {
            $query->where('po.ID', $id);
        }

        if ($whid) {
            $query->where('mp.Nearest_Warehouse', $whid);
        }

        if ($date_start) {
            $query->where('po.Created_At', '>=', $date_start);
        }

        if ($date_end) {
            $query->where('po.Created_At', '<=', $date_end);
        }

        if ($delivery_order_id) {
            $query->where('po.DO_Id', $delivery_order_id);
        }

        return $query->orderBy('ID', 'DESC')->get();
    }

    function getWorkOrderData(Request $r) {}

    private function pickupOrdersAsJson($rows)
    {
        return response()->json(['data' => $rows]);
    }

    private function pickupOrdersAsXml($rows)
    {
        $xml     = new SimpleXMLElement('<rows/>');
        $counter = 1;

        foreach ($rows as $row) {
            $item = $xml->addChild('row');
            $item->addAttribute('ID', $row->ID);

            $item->addChild('cell', $counter);
            $item->addChild('cell', htmlspecialchars((string) $row->Code));
            $item->addChild('cell', htmlspecialchars((string) $row->Type_Pickup));
            $item->addChild('cell', htmlspecialchars((string) $row->Coverage));
            $item->addChild('cell', htmlspecialchars((string) $row->Site));
            $item->addChild('cell', htmlspecialchars((string) $row->Package));
            $item->addChild('cell', htmlspecialchars((string) $row->Qty));
            $item->addChild('cell', htmlspecialchars((string) $row->Price));
            $item->addChild('cell', htmlspecialchars((string) $row->Description));
            $item->addChild('cell', htmlspecialchars((string) $row->Created_At));

            $counter++;
        }

        return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
    }

    public function createPickupRequest(Request $r)
    {
        return view('partner.pickup.create');
    }

    public function submitCreatePickupRequest(Request $r)
    {
        $count = DB::table('pickup_order')->count();

        try {
            DB::transaction(function () use ($r, $count) {
                DB::table('pickup_order')->insert([
                    'Code'        => $this->generatorService->generateApplicationCode($count, 'PO'),
                    'Type_Pickup' => $r->input('Type_Pickup'),
                    'Coverage'    => $r->input('Coverage'),
                    'Site'        => $r->input('Site'),
                    'Package'     => $r->input('Package'),
                    'Qty'         => $r->input('Qty'),
                    'Price'       => $r->input('Price'),
                    'Description' => $r->input('Description'),
                    'WO_Id'        => $r->input('WO_Id'),
                    'Created_At'  => now(),
                ]);
            });

            return response()->json(['status' => 'success', 'message' => 'Pickup request berhasil dibuat.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function assignFleetToPickupRequest(Request $r)
    {
        $id = $r->input('id');

        if (!$id) {
            return response()->json(['status' => 'error', 'message' => 'ID is required'], 400);
        }

        try {
            DB::transaction(function () use ($r, $id) {
                DB::table('pickup_order')->where('id', $id)->update([
                    'Pickup_At' => $r->input('Pickup_At'),
                    'Fleet_Id'  => $r->input('Fleet_Id'),
                ]);
            });

            return response()->json(['status' => 'success', 'message' => 'Fleet berhasil di-assign.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function submitCreateDeliveryOrder(Request $r)
    {
        $poIds = $r->input('PO_Id', []);
        if (empty($poIds)) {
            return response()->json(['status' => 'error', 'message' => 'PO Number is required'], 400);
        }

        if (!is_array($poIds)) {
            $poIds = [$poIds];
        }

        $highest = DB::table('delivery_order')->count();
        try {
            DB::transaction(function () use ($r, $poIds, $highest) {
                $doId = DB::table('delivery_order')->insertGetId([
                    'Code'        => $this->generatorService->generateApplicationCode($highest, 'DO'),
                    'DO_Status'   => $r->input('DO_Status'),
                    'DO_Date'     => $r->input('DO_Date'),
                    'Start_Time'  => $r->input('Start_Time'),
                    'Finish_Time' => $r->input('Finish_Time'),
                    'Created_At'  => now(),

                ]);

                DB::table('pickup_order')
                    ->whereIn('ID', $poIds)
                    ->update(['DO_Id' => $doId]);
            });

            return response()->json(['status' => 'success', 'message' => 'Delivery order berhasil dibuat.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // add signature, uco photo receipt, real entry qty
    public function updateDeliveryOrderReceipt(Request $r)
    {
        $id = $r->input('DO_Id');

        if (!$id) {
            return response()->json(['status' => 'error', 'message' => 'ID is required'], 400);
        }

        try {
            DB::transaction(function () use ($r, $id) {
                $year  = Carbon::now()->year;
                $month = Carbon::now()->format('m');

                // Signature (base64 image)
                if ($r->filled('Signature')) {
                    $b64        = preg_replace('/^data:image\/\w+;base64,/', '', $r->input('Signature'));
                    $binary     = base64_decode($b64);
                    $sigName    = "signature_{$id}_" . time() . ".png";
                    $sigPath    = "$year/$month/$sigName";
                    Storage::disk('public')->put($sigPath, $binary);

                    DB::table('meta_files')->insert([
                        'Filename'    => $sigName,
                        'Path'        => $sigPath,
                        'Extension'   => 'png',
                        'Description' => 'Signature',
                        'Topic'       => 'delivery-order',
                        'Map_To'      => $id,
                        'Created_At'  => now(),
                    ]);
                }

                // UCO receipt photo (binary file upload)
                if ($r->hasFile('Receipt_Photo') && $r->file('Receipt_Photo')->isValid()) {
                    $file = $r->file('Receipt_Photo');
                    $path = $file->store("$year/$month", 'public');
                    $ext  = $file->getClientOriginalExtension();

                    DB::table('meta_files')->insert([
                        'Filename'    => basename($path),
                        'Path'        => $path,
                        'Extension'   => $ext,
                        'Description' => 'Receipt_Photo',
                        'Topic'       => 'DELIVERY_ORDER',
                        'Map_To'      => $id,
                        'Created_At'  => now(),
                    ]);
                }

                DB::table('delivery_order')->where('id', $id)->update([
                    'Actual_Qty' => $r->input('Qty'),
                    'DO_Status'  => 'DONE',
                ]);
            });

            return response()->json(['status' => 'success', 'message' => 'Delivery order berhasil diupdate.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }


    // update nota penjemputan
    public function updateDeliveryOrderNote(Request $r)
    {
        $id = $r->input('id');

        if (!$id) {
            return response()->json(['status' => 'error', 'message' => 'ID is required'], 400);
        }

        try {
            DB::transaction(function () use ($r, $id) {
                $year  = Carbon::now()->year;
                $month = Carbon::now()->format('m');

                if ($r->hasFile('Delivery_Note') && $r->file('Delivery_Note')->isValid()) {
                    $file = $r->file('Delivery_Note');
                    $path = $file->store("$year/$month", 'public');
                    $ext  = $file->getClientOriginalExtension();

                    DB::table('meta_files')->insert([
                        'Filename'    => basename($path),
                        'Path'        => $path,
                        'Extension'   => $ext,
                        'Description' => 'Delivery_Note',
                        'Topic'       => 'DELIVERY_ORDER',
                        'Map_To'      => $id,
                        'Created_At'  => now(),
                    ]);
                }

                DB::table('delivery_order')->where('id', $id)->update([
                    'Actual_Pickup_Cost' => $r->input('Actual_Pickup_Cost'),
                ]);
            });

            return response()->json(['status' => 'success', 'message' => 'Delivery note berhasil diupdate.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function indexDeliveryOrder()
    {
        return view('partner.delivery-order.index');
    }

    public function createDeliveryOrder()
    {
        return view('partner.delivery-order.create');
    }

    public function getDeliveryOrderData(Request $r)
    {
        $id     = $r->query('id');
        $status = $r->query('status');
        $rows = DB::table('delivery_order as do')
            ->join('pickup_order as po', 'po.DO_Id', '=', 'do.ID')
            ->join('work_order as wo', 'po.WO_Id', '=', 'wo.ID')
            ->leftJoin('master_poo as mp', 'wo.POO_Id', '=', 'mp.ID')
            ->select('do.ID', 'do.Code as DO_Code', 'do.DO_Date', 'do.Created_At', 'po.Code as PO_Code', 'do.DO_Status')
            ->distinct()
            ->when($id, fn($q) => $q->where('do.ID', $id))
            ->when($status, fn($q) => $q->where('do.DO_Status', $status))
            ->get();

        if ($r->query('format') === 'xml') {
            $xml     = new SimpleXMLElement('<rows/>');
            $counter = 1;

            foreach ($rows as $row) {
                $item = $xml->addChild('row');
                $item->addAttribute('ID', $row->ID);

                $item->addChild('cell', $counter);
                $item->addChild('cell', htmlspecialchars((string) $row->DO_Code));
                $item->addChild('cell', htmlspecialchars((string) ($row->Created_At ?? '')));
                $item->addChild('cell', htmlspecialchars((string) $row->PO_Code));
                $item->addChild('cell', htmlspecialchars((string) ($row->DO_Status ?? '')));
                $item->addChild('cell', htmlspecialchars((string) $row->DO_Date));

                $counter++;
            }

            return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
        }

        return response()->json(['data' => $rows]);
    }

    public function getDeliveryOrderRouteList(Request $r)
    {
        $query = DB::table("delivery_order as do")
            ->select("do.ID", "do.Code as DO_Code", "do.DO_Date", "do.DO_Status", "do.Created_At",
                DB::raw("(SELECT COUNT(*) FROM pickup_order po WHERE po.DO_Id = do.ID) as PO_Count"),
                DB::raw("(SELECT mp.Nearest_Warehouse FROM pickup_order po2 JOIN work_order wo2 ON po2.WO_Id = wo2.ID JOIN master_poo mp ON wo2.POO_Id = mp.ID WHERE po2.DO_Id = do.ID LIMIT 1) as Warehouse_ID"))
            ->whereExists(function($q) {
                $q->select(DB::raw(1))
                  ->from("pickup_order")
                  ->whereColumn("DO_Id", "do.ID");
            })
            ->orderBy("do.ID", "desc");

        if ($whId = $r->query("whid")) {
            $query->whereExists(function($q) use ($whId) {
                $q->select(DB::raw(1))
                  ->from("pickup_order as po")
                  ->join("work_order as wo", "po.WO_Id", "=", "wo.ID")
                  ->join("master_poo as mp", "wo.POO_Id", "=", "mp.ID")
                  ->whereColumn("po.DO_Id", "do.ID")
                  ->where("mp.Nearest_Warehouse", $whId);
            });
        }

        if ($start = $r->query("date_start")) {
            $query->where("do.DO_Date", ">=", $start);
        }

        if ($end = $r->query("date_end")) {
            $query->where("do.DO_Date", "<=", $end);
        }

        $rows = $query->get();

        if ($r->query("format") === "json") {
            return response()->json(["data" => $rows]);
        }

        $xml = new SimpleXMLElement("<rows></rows>");
        $no  = 1;

        foreach ($rows as $row) {
            $item = $xml->addChild("row");
            $item->addAttribute("ID", $row->ID);
            $item->addChild("cell", $no);
            $item->addChild("cell", $row->DO_Code);
            $item->addChild("cell", $row->DO_Date);
            $item->addChild("cell", $row->DO_Status ?? "-");
            $item->addChild("cell", $row->PO_Count ?? "0");
            $item->addChild("cell", $row->Warehouse_ID ?? "-");
            $no++;
        }

        return response($xml->asXML(), 200)->header("Content-Type", "application/xml");
    }


    public function createTicket(Request $r) {}

    public function getTicket(Request $r) {}

    public function indexPickupRouting()
    {
        return view('partner.pickup.routing.index');
    }

    public function getFleetData(Request $r)
    {
        $data = DB::table("master_fleet")->select("*")->get();
        return response()->json(["status" => "success", "data" => $data]);
    }

    public function getPickupListData(Request $r)
    {
        $rows = $this->fetchPickupOrders($r->query('id'), $r->query('whid'), $r->query('date_start'), $r->query('date_end'), $r->query('delivery_order_id'));

        if ($r->query('format') === 'json') {
            return response()->json(['data' => $rows]);
        }

        $xml = new SimpleXMLElement('<rows/>');
        $no  = 1;

        foreach ($rows as $row) {
            $item = $xml->addChild('row');
            $item->addAttribute('ID', $row->ID);
            $item->addChild('cell', $no);
            $item->addChild('cell', htmlspecialchars((string) ($row->Code ?? '')));
            $item->addChild('cell', htmlspecialchars((string) ($row->Type_Pickup ?? '')));
            $item->addChild('cell', htmlspecialchars((string) ($row->Coverage ?? '')));
            $item->addChild('cell', htmlspecialchars((string) ($row->Site ?? '')));
            $item->addChild('cell', htmlspecialchars((string) ($row->Package ?? '')));
            $item->addChild('cell', htmlspecialchars((string) ($row->Qty ?? '')));
            $item->addChild('cell', htmlspecialchars((string) ($row->Price ?? '')));
            $item->addChild('cell', htmlspecialchars((string) ($row->Description ?? '')));
            $item->addChild('cell', htmlspecialchars((string) ($row->Created_At ?? '')));
            $no++;
        }

        return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
    }

    public function getPickupOrderData(Request $r)
    {
        try {
            $status = $r->query('status', 'DONE');

            $data = DB::table('pickup_order as po')
                ->join('work_order as wo', 'po.WO_Id', '=', 'wo.ID')
                ->leftJoin('master_poo as mp', 'wo.POO_Id', '=', 'mp.ID')
                ->join('delivery_order as do', 'po.DO_Id', '=', 'do.ID')
                ->select(
                    'po.*',
                    'po.Code as PO_Code',
                    'wo.Code as WO_Code',
                    'mp.Name as POO_Name',
                    'do.DO_Date',
                )
                ->where('po.Status', $status)
                ->when($r->query('delivery_order_id'), fn($q) => $q->where('po.DO_Id', $r->query('delivery_order_id')))
                ->orderBy('po.Created_At', 'desc')
                ->get();

            $xml     = new SimpleXMLElement('<rows/>');
            $counter = 1;

            foreach ($data as $d) {
                $row = $xml->addChild('row');
                $row->addAttribute('ID', $d->ID);

                $row->addChild('cell', $counter);
                $row->addChild('cell', htmlspecialchars((string) $d->PO_Code));
                $row->addChild('cell', htmlspecialchars((string) $d->POO_Name));
                $row->addChild('cell', $d->Actual_Qty ?? $d->Qty ?? 0);
                $row->addChild('cell', 'kg');
                $row->addChild('cell', $d->Created_At);
                $row->addChild('cell', $d->Pickup_At ?? $d->DO_Date);

                $counter++;
            }

            return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }


    public function calculateRoute(Request $r)
    {
        $ids = $r->input('pickup_order_ids', []);
        if (empty($ids)) {
            return response()->json(['status' => 'error', 'message' => 'No orders selected'], 400);
        }

        $orders = $this->fetchPickupOrders(null)->whereIn('ID', $ids);

        $waypoints = [];
        $skipped = [];

        // Add warehouse as starting point if selected
        $whId = $r->input('warehouse_id');
        $warehouse = null;
        if ($whId) {
            $warehouse = DB::table('warehouse_partner')->where('id', $whId)->first(['lat', 'lng']);
            if ($warehouse && $warehouse->lat && $warehouse->lng) {
                $waypoints[] = [(float)$warehouse->lng, (float)$warehouse->lat];
            }
        }

            $allocationType = $r->input('cost_method') === 'lumpsum' ? 'LUMPSUM' : 'MANUAL';
        foreach ($orders as $order) {
            $coord = $this->getOrderCoordinate($order);
            if ($coord) {
                $waypoints[] = $coord;
            } else {
                $skipped[] = $order->POO_Name ?? $order->PO_Code ?? 'ID:' . $order->ID;
            }
        }

        // End back at warehouse
        if ($warehouse && $warehouse->lat && $warehouse->lng) {
            $waypoints[] = [(float)$warehouse->lng, (float)$warehouse->lat];
        }

        if (count($waypoints) < 2) {
            $msg = 'Need at least 2 valid coordinates.';
            if (!empty($skipped)) {
                $msg .= ' Pickup orders without valid coordinates: ' . implode(', ', $skipped) . '.';
                $msg .= ' Please update Location_Coordinate di master POO.';
            }
            return response()->json(['status' => 'error', 'message' => $msg], 400);
        }

        if (!empty($skipped)) {
            \Log::warning('Route calculation skipped orders without coordinates: ' . implode(', ', $skipped));
        }

        try {
            $osrm = app(\App\Services\OSRMService::class);
            $routeData = $osrm->getRoute($waypoints);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Routing failed: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'status' => 'success',
            'data'    => $routeData,
        ]);
    }
    public function submitRoute(Request $r)
    {
        $ids = $r->input('pickup_order_ids', []);
        if (empty($ids)) {
            return response()->json(['status' => 'error', 'message' => 'No orders selected'], 400);
        }


        $orders = $this->fetchPickupOrders(null)->whereIn('ID', $ids);

        $waypoints = [];
        $skipped = [];

        // Add warehouse as starting point if selected
        $whId = $r->input('warehouse_id');
        $warehouse = null;
        if ($whId) {
            $warehouse = DB::table('warehouse_partner')->where('id', $whId)->first(['lat', 'lng']);
            if ($warehouse && $warehouse->lat && $warehouse->lng) {
                $waypoints[] = [(float)$warehouse->lng, (float)$warehouse->lat];
            }
        }

            $allocationType = $r->input('cost_method') === 'lumpsum' ? 'LUMPSUM' : 'MANUAL';
        foreach ($orders as $order) {
            $coord = $this->getOrderCoordinate($order);
            if ($coord) {
                $waypoints[] = $coord;
            } else {
                $skipped[] = $order->POO_Name ?? $order->PO_Code ?? 'ID:' . $order->ID;
            }
        }

        // End back at warehouse
        if ($warehouse && $warehouse->lat && $warehouse->lng) {
            $waypoints[] = [(float)$warehouse->lng, (float)$warehouse->lat];
        }

        if (count($waypoints) < 2) {
            $msg = 'Need at least 2 valid coordinates.';
            if (!empty($skipped)) {
                $msg .= ' Pickup orders without valid coordinates: ' . implode(', ', $skipped) . '.';
                $msg .= ' Please update Location_Coordinate di master POO.';
            }
            return response()->json(['status' => 'error', 'message' => $msg], 400);
        }

        if (!empty($skipped)) {
            \Log::warning('Route submission skipped orders without coordinates: ' . implode(', ', $skipped));
        }

        try {
            $osrm = app(\App\Services\OSRMService::class);
            $routeData = $osrm->getRoute($waypoints);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Routing failed: ' . $e->getMessage()], 500);
        }

        $highest = DB::table('pickup_routes')->max('ID');
        $routeCode = $this->generatorService->generateApplicationCode($highest, 'RTE');

        DB::beginTransaction();
        try {
            $routeId = DB::table('pickup_routes')->insertGetId([
                'Code'                     => $routeCode,
                'Distance_Km'              => $routeData['distance_km'],
                'Duration_Min'             => $routeData['duration_min'],
                'Encoded_Polyline'         => $routeData['encoded_polyline'],
                'BBM_Ratio'                => $r->input('bbm_ratio'),
                'BBM_Consumption'          => $r->input('bbm_consumption'),
                'BBM_Price'               => $r->input('bbm_price'),
                'Total_Distance'           => $r->input('total_distance'),
                'Pickup_Waypoints'         => $r->input('pickup_count'),
                'Total_Weight'             => $r->input('total_weight'),
                'Toll_Estimation'          => $r->input('toll_estimation'),
                'Parking_Cost'             => $r->input('parking_cost'),
                'BBM_Alkon_Price'          => $r->input('alkon_bbm_cost'),
                'Driver_Operational_Price' => $r->input('driver_operational'),
                'Field_Operational_Price'  => $r->input('field_operational', 7000),
                'Total_Cost'               => $r->input('total_cost'),
                'Warehouse_ID'             => $whId,
            ]);

            $seq = 1;
            $allocationType = $r->input('cost_method') === 'lumpsum' ? 'LUMPSUM' : 'MANUAL';
            foreach ($orders as $order) {
                DB::table('pickup_route_waypoints')->insert([
                    'Route_Id'         => $routeId,
                    'Sequence'         => $seq,
                    'PO_Id'  => $order->ID,
                    'POO_Name'         => $order->POO_Name,
                    'Lat'         => $order->Lat,
                    'Lng'        => $order->Lng,
                ]);
                $seq++;
            }

            $allocationType = $r->input('cost_method') === 'lumpsum' ? 'LUMPSUM' : 'MANUAL';
            foreach ($orders as $order) {
                DB::table('pickup_order')->where('ID', $order->ID)->update([
                    'Fleet_Id'   => $r->input('fleet_id'),
                    'Total_Cost' => $r->input('total_cost'),
                    'APC'        => $r->input('apc', 0),
                    'Allocation_Type' => $allocationType,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        return response()->json([
            'status'           => 'success',
            'data'             => [
                'route_id'         => $routeId,
                'PO_Id'            => $ids[0],
                'distance_km'      => $routeData['distance_km'],
                'duration_min'     => $routeData['duration_min'],
                'encoded_polyline' => $routeData['encoded_polyline'],
                'waypoints'        => $waypoints,
            ]
        ]);
    }


    public function getSavedRouteList(Request $r)
    {
        $query = DB::table('pickup_routes as pr')
            ->selectRaw("pr.*, (SELECT GROUP_CONCAT(po.Code ORDER BY prw.Sequence SEPARATOR ', ') FROM pickup_route_waypoints prw JOIN pickup_order po ON po.ID = prw.PO_Id WHERE prw.Route_Id = pr.ID) as Waypoint_Codes")
            ->orderBy('pr.ID', 'desc');

        if ($start = $r->query('date_start')) {
            $query->where('pr.Created_At', '>=', $start);
        }

        if ($end = $r->query('date_end')) {
            $query->where('pr.Created_At', '<=', $end);
        }

        if ($status = $r->query('status')) {
            $query->where('pr.Status', $status);
        }


        if ($id = $r->query('id')) {
            $ids = explode(',', $id);
            $query->whereIn('pr.ID', $ids);
        }
        $rows = $query->get();

        if ($r->query('format') === 'json') {
            return response()->json(['data' => $rows]);
        }

        $xml = new SimpleXMLElement('<rows></rows>');
        $no  = 1;

        foreach ($rows as $row) {
            $item = $xml->addChild('row');
            $item->addAttribute('ID', $row->ID);
            $item->addChild('cell', $no);
            $item->addChild('cell', $row->Code);
            $item->addChild('cell', $row->Waypoint_Codes ?? '-');
            $item->addChild('cell', $row->Total_Cost);
            $item->addChild('cell', $row->Status ?? '-');
            $item->addChild('cell', $row->Created_At);
            $item->addChild('cell', $row->Distance_Km);
            $no++;
        }

        return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
    }

    public function getRouteDetail(Request $r)
    {
        $routeId = $r->query("route_id");
        if (!$routeId) {
            return response()->json(["status" => "error", "message" => "route_id is required"], 400);
        }

        $route = DB::table("pickup_routes")->where("ID", $routeId)->first();
        if (!$route) {
            return response()->json(["status" => "error", "message" => "Route not found"], 404);
        }

        $pos = DB::table("pickup_route_waypoints as prw")
            ->join("pickup_order as po", "po.ID", "=", "prw.PO_Id")
            ->leftJoin("delivery_order as do", "po.DO_Id", "=", "do.ID")
            ->leftJoin("work_order as wo", "po.WO_Id", "=", "wo.ID")
            ->leftJoin("master_poo as mp", "wo.POO_Id", "=", "mp.ID")
            ->select(
                "do.ID as do_id", "do.Code as do_code",
                "po.ID as po_id", "po.Code as po_code",
                "mp.Name as poo_name",
                "po.Qty",
                "po.Allocation_Type",
                "prw.Sequence"
            )
            ->where("prw.Route_Id", $routeId)
            ->orderBy("do.ID")
            ->orderBy("prw.Sequence")
            ->get();

        $deliveryOrders = [];
        foreach ($pos as $po) {
            $doKey = $po->do_id ?: "no_do";
            if (!isset($deliveryOrders[$doKey])) {
                $deliveryOrders[$doKey] = [
                    "do_id"   => $po->do_id,
                    "do_code" => $po->do_code ?? "No DO Assigned",
                    "pickup_orders" => [],
                ];
            }
            $deliveryOrders[$doKey]["pickup_orders"][] = [
                "po_id"           => $po->po_id,
                "po_code"         => $po->po_code,
                "poo_name"        => $po->poo_name,
                "qty"             => $po->Qty,
                "allocation_type" => $po->Allocation_Type,
            ];
        }

        return response()->json([
            "status" => "success",
            "data"   => [
                "route" => [
                    "id"          => $route->ID,
                    "code"        => $route->Code,
                    "status"      => $route->Status,
                    "total_cost"  => $route->Total_Cost,
                    "transfer_nominal" => $route->Transfer_Nominal,
                    "distance_km" => $route->Distance_Km,
                    "created_at"  => $route->Created_At,
                ],
                "delivery_orders" => array_values($deliveryOrders),
            ],
        ]);
    }





    public function actionSettlement(Request $r)
    {
        $action = $r->input('action'); // submit, approve, reject

        if ($action === 'submit') {
            $routeIds = $r->input('route_ids', '');
            if (empty($routeIds)) {
                return response()->json(['status' => 'error', 'message' => 'route_ids is required'], 400);
            }

            $sections = [
                ['key' => 'bbm',         'label' => 'BBM'],
                ['key' => 'tol',         'label' => 'TOL'],
                ['key' => 'parkir',      'label' => 'Parkir'],
                ['key' => 'op_driver',   'label' => 'Operasional Driver'],
                ['key' => 'op_lapangan', 'label' => 'Operasional Lapangan'],
                ['key' => 'non_receipt', 'label' => 'Biaya non receipt'],
            ];

            try {
                DB::transaction(function () use ($r, $routeIds, $sections) {
                    $session = $this->userService->getCurrentSessionUserInfo();
                    $year  = Carbon::now()->year;
                    $month = Carbon::now()->format('m');

                    foreach ($sections as $sec) {
                        $nominal = $r->input("{$sec['key']}_nominal");
                        $notes   = $r->input("{$sec['key']}_keterangan");

                        if (empty($nominal) && empty($notes)) {
                            continue;
                        }

                        $settlementId = DB::table('settlement')->insertGetId([
                            'Route_Id'    => $routeIds,
                            'Description' => $sec['label'],
                            'Nominal'     => $nominal,
                            'Notes'       => $notes,
                            'Created_By'  => $session->username,
                            'Created_At'  => now(),
                        ]);

                        $fileField = "{$sec['key']}_file";
                        $files = $r->file($fileField, []);

                        if (!is_array($files)) {
                            $files = $files ? [$files] : [];
                        }

                        foreach ($files as $file) {
                            if (!$file instanceof \Illuminate\Http\UploadedFile || !$file->isValid()) {
                                continue;
                            }

                            $ext      = $file->getClientOriginalExtension();
                            $uuid     = \Illuminate\Support\Str::uuid()->toString();
                            $destDir  = public_path("filevendor/{$year}/{$month}");
                            if (!is_dir($destDir)) {
                                mkdir($destDir, 0777, true);
                            }
                            $fileName = "{$uuid}.{$ext}";
                            $file->move($destDir, $fileName);

                            DB::table('meta_files')->insert([
                                'Filename'    => $fileName,
                                'Path'        => "{$year}/{$month}/{$fileName}",
                                'Extension'   => $ext,
                                'Description' => $sec['label'],
                                'Topic'       => 'settlement',
                                'Map_To'      => $settlementId,
                                'Created_At'  => now(),
                            ]);
                        }
                    }

                    $routeIdArray = array_filter(explode(',', $routeIds));
                    if (!empty($routeIdArray)) {
                        DB::table('pickup_routes')
                            ->whereIn('ID', $routeIdArray)
                            ->update(['Status' => 'SETTLEMENT_PENDING']);
                    }
                });

                return response()->json(['status' => 'success', 'message' => 'Settlement submitted, menunggu approval Finance.']);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
            }
        }

        // approve / reject
        $routeId = $r->input('route_id');
        if (!$routeId) {
            return response()->json(['status' => 'error', 'message' => 'route_id is required'], 400);
        }

        if ($action === 'approve') {
            DB::table('pickup_routes')->where('ID', $routeId)->update(['Status' => 'SETTLED']);
            return response()->json(['status' => 'success', 'message' => 'Settlement approved.']);
        }

        if ($action === 'reject') {
            $notes = $r->input('notes', '');
            DB::table('pickup_routes')->where('ID', $routeId)->update(['Status' => 'PENDING_SETTLEMENT']);
            return response()->json(['status' => 'success', 'message' => 'Settlement rejected, kembali ke PENDING_SETTLEMENT.']);
        }

        return response()->json(['status' => 'error', 'message' => 'Invalid action'], 400);
    }

    public function getSettlementDetail(Request $r)
    {
        $routeId = $r->query('route_id');
        if (!$routeId) {
            return response()->json(['status' => 'error', 'message' => 'route_id is required'], 400);
        }

        $route = DB::table('pickup_routes')->where('ID', $routeId)->first();
        if (!$route) {
            return response()->json(['status' => 'error', 'message' => 'Route not found'], 404);
        }

        $settlements = DB::table('settlement')
            ->whereRaw("FIND_IN_SET(?, Route_Id)", [$routeId])
            ->get();

        foreach ($settlements as $s) {
            $files = DB::table('meta_files')
                ->where('Topic', 'settlement')
                ->where('Map_To', $s->ID)
                ->get(['Path']);
            $s->files = $files->pluck('Path')->map(function($p) {
                return asset($p);
            });
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'route'       => $route,
                'settlements' => $settlements,
            ],
        ]);
    }

    public function indexSettlement()
    {
        return view('finance.ops.settlement.index');
    }

    public function indexSettlementApproval()
    {
        return view('finance.ops.settlement-approval.index');
    }

    public function indexAcquisition()
    {
        return view('finance.ops.acquisition.index');
    }

    public function getAcquisitionCostData(Request $r)
    {
        $month = $r->query('month');
        $status = $r->query('status'); // NOT_SUBMITTED, PENDING, APPROVED, REJECTED

        $query = DB::table('pickup_order as po')
            ->leftJoin('delivery_order as d', 'po.DO_Id', '=', 'd.ID')
            ->leftJoin('work_order as wo', 'po.WO_Id', '=', 'wo.ID')
            ->leftJoin('master_poo as mp', 'wo.POO_Id', '=', 'mp.ID')
            ->select(
                'po.ID',
                'po.Code as PO_Code',
                'd.Code as DO_Code',
                'mp.Name as POO_Name',
                'po.Qty',
                'po.Actual_Qty',
                'mp.Price as POO_Price',
                'po.Total_Cost',
                'po.Actual_Nominal_Transfer',
                'po.Acquisition_Status',
                'po.Acquisition_Submitted_At',
                'po.Acquisition_Approved_At',
                'po.Acquisition_Approved_By',
                'po.Acquisition_Notes'
            )
            ->where('d.DO_Status', 'DONE')
            ->where('po.Acquisition_Status', '!=', "");

        if ($status) {
            $query->where('po.Acquisition_Status', $status);
        }

        if ($month) {
            $query->whereRaw("DATE_FORMAT(po.Created_At, '%Y-%m') = ?", [$month]);
        }

        $data = $query->get();

        if ($r->query('format') === 'xml') {
            $xml = new SimpleXMLElement('<rows/>');
            foreach ($data as $i => $d) {
                $row = $xml->addChild('row');
                $row->addAttribute('ID', $d->ID);
                $row->addChild('cell', $i + 1);
                $row->addChild('cell', htmlspecialchars((string) $d->DO_Code));
                $row->addChild('cell', htmlspecialchars((string) $d->PO_Code));
                $row->addChild('cell', htmlspecialchars((string) $d->POO_Name));
                $row->addChild('cell', (string) $d->Qty);
                $row->addChild('cell', (string) ($d->Actual_Qty ?? ''));
                $row->addChild('cell', (string) ($d->POO_Price ?? 0));
                $row->addChild('cell', (string) $d->Total_Cost);
                $row->addChild('cell', (string) $d->Actual_Nominal_Transfer);
                $row->addChild('cell', htmlspecialchars((string) $d->Acquisition_Status));
                $row->addChild('cell', $d->Acquisition_Submitted_At ?? '');
            }
            return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
        }

        return response()->json(['data' => $data]);
    }

    public function submitAcquisitionCost(Request $r)
    {
        DB::beginTransaction();
        try {
            $po = DB::table('pickup_order')->where('ID', $r->po_id)->first();
            if (!$po) {
                return response()->json(['success' => false, 'message' => 'Pickup order not found'], 404);
            }

            DB::table('pickup_order')->where('ID', $r->po_id)->update([
                'Actual_Nominal_Transfer' => $r->actual_nominal_transfer,
                'Actual_Qty'              => $r->actual_qty,
                'Acquisition_Status'      => 'PENDING',
                'Acquisition_Submitted_At' => now(),
            ]);

            if ($r->hasFile('receipt_file') && $r->file('receipt_file')->isValid()) {
                $file = $r->file('receipt_file');
                $ext = $file->getClientOriginalExtension();
                $year = now()->format('Y');
                $month = now()->format('m');
                $uuid = \Ramsey\Uuid\Uuid::uuid4()->toString();
                $filename = $uuid . '.' . $ext;
                $path = "{$year}/{$month}/{$filename}";

                $dir = public_path("filevendor/{$year}/{$month}");
                if (!is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }
                $file->move($dir, $filename);

                DB::table('meta_files')->insert([
                    'Filename'    => $filename,
                    'Path'        => $path,
                    'Extension'   => $ext,
                    'Description' => 'Acquisition Cost Receipt',
                    'Topic'       => 'acquisition_cost',
                    'Map_To'      => $r->po_id,
                    'Created_At'  => now(),
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Acquisition cost submitted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function actionAcquisitionCost(Request $r)
    {
        $po = DB::table('pickup_order')->where('ID', $r->po_id)->first();
        if (!$po) {
            return response()->json(['success' => false, 'message' => 'Pickup order not found'], 404);
        }

        if ($r->action === 'approve') {
            DB::table('pickup_order')->where('ID', $r->po_id)->update([
                'Acquisition_Status'     => 'APPROVED',
                'Acquisition_Approved_At' => now(),
                'Acquisition_Approved_By' => auth()->user()->name ?? 'Finance',
            ]);
            return response()->json(['success' => true, 'message' => 'Acquisition cost approved']);
        } elseif ($r->action === 'reject') {
            if (!$r->filled('notes')) {
                return response()->json(['success' => false, 'message' => 'Rejection note is required'], 400);
            }
            DB::table('pickup_order')->where('ID', $r->po_id)->update([
                'Acquisition_Status' => 'REJECTED',
                'Acquisition_Notes'  => $r->notes,
            ]);
            return response()->json(['success' => true, 'message' => 'Acquisition cost rejected']);
        }

        return response()->json(['success' => false, 'message' => 'Invalid action'], 400);
    }

    public function getAcquisitionDetail(Request $r)
    {
        $id = $r->query('id');
        if (!$id) {
            return response()->json(['success' => false, 'message' => 'Parameter id diperlukan']);
        }

        $data = DB::table('pickup_order as po')
            ->leftJoin('delivery_order as d', 'po.DO_Id', '=', 'd.ID')
            ->leftJoin('work_order as wo', 'po.WO_Id', '=', 'wo.ID')
            ->leftJoin('master_fleet as mf', 'po.Fleet_Id', '=', 'mf.ID')
            ->leftJoin('users as driver', 'po.Fleet_Id', '=', 'driver.id')
            ->leftJoin('master_poo as mp', 'wo.POO_Id', '=', 'mp.ID')
            ->leftJoin('warehouse_partner as wp', 'mp.Nearest_Warehouse', '=', 'wp.id')
            ->leftJoin('meta_files as mf3', function ($join) {
                $join->on('po.ID', '=', 'mf3.Map_To')
                    ->where('mf3.Topic', '=', 'delivery-order')
                    ->where('mf3.Description', '=', 'Weighing_Photo');
            })
            ->leftJoin('meta_files as mf2', function ($join) {
                $join->on('po.ID', '=', 'mf2.Map_To')
                    ->where('mf2.Topic', '=', 'acquisition-cost-receipt');
            })
            ->select(
                'po.ID',
                'po.Code as PO_Code',
                'po.Qty',
                'po.Actual_Qty',
                'mp.Price as POO_Price',
                'po.Total_Cost',
                'po.Actual_Nominal_Transfer',
                'po.Acquisition_Status',
                'po.Acquisition_Submitted_At',
                'po.Acquisition_Approved_At',
                'po.Description',
                'd.Code as DO_Code',
                'wo.Code as WO_Code',
                'mf.Police_Number',
                'driver.Name as Driver_Name',
                'mp.Name as Customer_Name',
                'mp.PIC',
                'mp.Phone',
                'mp.Full_Address',
                'mp.Province',
                'mp.City',
                'mp.Bank_Name',
                'mp.Bank_Account_Number',
                'mp.Bank_Account_Name',
                'wp.warehouse_location',
                'mf2.Path as Receipt_Path',
                'mf3.Path as DO_Photo_Path'
            )
            ->where('po.ID', $id)
            ->first();

        if ($data && $data->Receipt_Path) {
            $data->Receipt_Url = url('filevendor/' . $data->Receipt_Path);
        } else {
            $data->Receipt_Url = null;
        }

        if ($data && $data->DO_Photo_Path) {
            $data->DO_Photo_Url = url('filevendor/' . $data->DO_Photo_Path);
        } else {
            $data->DO_Photo_Url = null;
        }

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function uploadAcquisitionReceipt(Request $r)
    {
        DB::beginTransaction();
        try {
            $user = $this->userService->getCurrentSessionUserInfo();
            $poId = $r->po_id;

            $po = DB::table('pickup_order')->where('ID', $poId)->first();
            if (!$po) {
                return response()->json(['success' => false, 'message' => 'Pickup order not found'], 404);
            }

            DB::table('pickup_order')->where('ID', $poId)->update([
                'Actual_Nominal_Transfer' => $r->actual_nominal,
                'Acquisition_Notes'       => $r->notes,
                'Acquisition_Approved_By' => $user->id,
                'Acquisition_Approved_At' => now(),
                'Acquisition_Status'      => 'APPROVED',
            ]);

            if ($r->hasFile('receipt_file') && $r->file('receipt_file')->isValid()) {
                $file     = $r->file('receipt_file');
                $ext      = $file->getClientOriginalExtension();
                $year     = now()->format('Y');
                $month    = now()->format('m');
                $uuid     = $this->generatorService->generateUUID();
                $filename = $uuid . '.' . $ext;
                $path     = "{$year}/{$month}/{$filename}";

                $dir = public_path("filevendor/{$year}/{$month}");
                if (!is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }
                $file->move($dir, $filename);

                DB::table('meta_files')->insert([
                    'Filename'    => $filename,
                    'Path'        => $path,
                    'Extension'   => $ext,
                    'Description' => 'Upload Bukti Transfer',
                    'Topic'       => 'acquisition-cost-receipt',
                    'Map_To'      => $poId,
                    'Created_At'  => now(),
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Upload berhasil']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateInstockPhoto(Request $r)
    {
        DB::beginTransaction();
        try {
            if ($r->filled('notes')) {
                DB::table('pickup_order')->where('ID', $r->po_id)
                    ->update(['Description' => $r->notes]);
            }

            $mapTo = $r->do_id ?? $r->po_id;

            if ($r->filled('webcam_photo')) {
                $this->uploadService->storeFile(
                    $r->webcam_photo,
                    'base64',
                    'instock_photo',
                    $mapTo,
                    'Instock Webcam Photo'
                );
            }

            if ($r->has('webcam_photos') && is_array($r->input('webcam_photos'))) {
                foreach ($r->input('webcam_photos') as $b64) {
                    if ($b64) {
                        $this->uploadService->storeFile($b64, 'base64', 'instock_photo', $mapTo, 'Instock Webcam Photo');
                    }
                }
            }

            if ($r->hasFile('upload_photo')) {
                $this->uploadService->storeFile(
                    $r->file('upload_photo'),
                    'upload',
                    'instock_photo',
                    $mapTo,
                    'Instock Upload Photo'
                );
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data updated successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getSavedRouteWaypointsList(Request $r)
    {
        $poIds = $r->query("po_ids");
        if (!$poIds) {
            return response()->json(["routed" => false, "data" => []]);
        }

        $ids = explode(",", $poIds);
        $routed = DB::table("pickup_route_waypoints")
            ->join("pickup_routes as pr", "pr.ID", "=", "pickup_route_waypoints.Route_Id")
            ->whereIn("PO_Id", $ids)
            ->select("pickup_route_waypoints.PO_Id", "pr.Code as Route_Code")
            ->get();

        return response()->json(["routed" => $routed->isNotEmpty(), "data" => $routed]);
    }


    public function getRouteMapData(Request $r)
    {
        $routeId = $r->query('route_id');
        if (!$routeId) {
            return response()->json(['data' => null], 400);
        }

        $route = DB::table('pickup_routes')->where('ID', $routeId)->first();
        if (!$route) {
            return response()->json(['data' => null], 404);
        }

        $waypoints = DB::table('pickup_route_waypoints')
            ->where('Route_Id', $routeId)
            ->orderBy('Sequence')
            ->get();

        $warehouse = null;
        if ($route->Warehouse_Id) {
            $warehouse = DB::table('warehouse_partner')
                ->where('id', $route->Warehouse_Id)
                ->first(['warehouse_task_id', 'warehouse_location', 'lat', 'lng']);
        }

        return response()->json(['data' => [
            'route'     => $route,
            'waypoints' => $waypoints,
            'warehouse' => $warehouse,
        ]]);
    }

    // === Instock PO Data (per SJ) ===
    public function getInstockPOData(Request $r)
    {
        $doId = $r->query('delivery_order_id');
        if (!$doId) {
            return response()->json(['success' => false, 'message' => 'delivery_order_id required'], 400);
        }

        $data = DB::table('pickup_order as po')
            ->join('work_order as wo', 'po.WO_Id', '=', 'wo.ID')
            ->leftJoin('master_poo as mp', 'wo.POO_Id', '=', 'mp.ID')
            ->select('po.ID', 'po.Code as PO_Code', 'mp.Name as POO_Name', 'po.Qty', 'po.Actual_Qty', 'po.Instock_Recommendation', 'mp.Price as POO_Price', 'mp.Nearest_Warehouse')
            ->where('po.DO_Id', $doId)
            ->get();

        $totalQty = $data->sum('Qty');
        $totalActual = $data->sum('Actual_Qty');

        if ($r->query('format') === 'json') {
            return response()->json(['data' => $data]);
        }

        $xml = new SimpleXMLElement('<rows/>');
        $no  = 1;

        foreach ($data as $d) {
            $pct = $totalQty > 0 ? round(($d->Qty / $totalQty) * 100, 2) : 0;
            $calculatedRec = ($totalQty > 0 && $totalActual > 0) ? round(($d->Qty / $totalQty) * $totalActual, 2) : 0;
            $rec = $d->Instock_Recommendation ?? $calculatedRec;

            $row = $xml->addChild('row');
            $row->addAttribute('ID', $d->ID);
            $row->addChild('cell', $no);
            $row->addChild('cell', htmlspecialchars((string) $d->PO_Code));
            $row->addChild('cell', htmlspecialchars((string) $d->POO_Name));
            $row->addChild('cell', (string) ($d->Qty ?? 0));
            $row->addChild('cell', 'L');
            $row->addChild('cell', (string) $pct);
            $row->addChild('cell', (string) ($d->Actual_Qty ?? ''));
            $row->addChild('cell', (string) $rec);
            $no++;
        }

        return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
    }

    // === Instock Dregs ===
    public function getInstockDregsData(Request $r)
    {
        $data = DB::table('instock_dregs')->orderBy('Created_At', 'desc')->get();

        $xml = new SimpleXMLElement('<rows/>');
        $no  = 1;

        foreach ($data as $d) {
            $row = $xml->addChild('row');
            $row->addAttribute('ID', $d->ID);
            $row->addChild('cell', $no);
            $row->addChild('cell', htmlspecialchars((string) $d->Instock_Date));
            $row->addChild('cell', (string) $d->Quantity);
            $row->addChild('cell', htmlspecialchars((string) ($d->Notes ?? '')));
            $row->addChild('cell', htmlspecialchars((string) $d->Created_At));
            $no++;
        }

        return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
    }

    public function updateRecommendation(Request $r)
    {
        $updates = $r->input('recommendations', []);
        foreach ($updates as $u) {
            if (isset($u['po_id'], $u['recommendation'])) {
                DB::table('pickup_order')->where('ID', $u['po_id'])->update(['Instock_Recommendation' => $u['recommendation']]);
            }
        }
        return response()->json(['success' => true, 'message' => 'Rekomendasi updated.']);
    }

    public function submitInstockDregs(Request $r)
    {
        DB::beginTransaction();
        try {
            $id = DB::table('instock_dregs')->insertGetId([
                'Instock_Date' => $r->input('Instock_Date'),
                'Quantity'    => $r->input('Quantity'),
                'Notes'       => $r->input('Notes'),
                'Created_At'  => now(),
            ]);

            if ($r->has('webcam_photos') && is_array($r->input('webcam_photos'))) {
                foreach ($r->input('webcam_photos') as $b64) {
                    if ($b64) {
                        $this->uploadService->storeFile($b64, 'base64', 'instock_dregs_photo', $id, 'Dregs Webcam Photo');
                    }
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Dregs saved.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // === Instock Supplies (from warehouse_partner_supplies) ===
    public function getInstockSuppliesData(Request $r)
    {
        $data = DB::table('warehouse_partner_supplies as wps')
            ->leftJoin('warehouse_partner as wp', 'wps.warehouse_task_id', '=', 'wp.warehouse_task_id')
            ->leftJoin('master_container as mc', 'wps.Container_ID', '=', 'mc.ID')
            ->select('wps.*', 'wp.warehouse_location', 'mc.Name as container_name')
            ->orderBy('wps.created_date', 'desc')
            ->get();

        $xml = new SimpleXMLElement('<rows/>');
        $no  = 1;

        foreach ($data as $d) {
            $row = $xml->addChild('row');
            $row->addAttribute('ID', $d->id);
            $row->addChild('cell', $no);
            $row->addChild('cell', htmlspecialchars((string) $d->received_date));
            $row->addChild('cell', htmlspecialchars((string) ($d->source ?? '')));
            $row->addChild('cell', htmlspecialchars((string) ($d->warehouse_location ?? '')));
            $row->addChild('cell', htmlspecialchars((string) ($d->container_name ?? '')));
            $row->addChild('cell', (string) ($d->received_qty ?? 0));
            $row->addChild('cell', (string) ($d->price ?? 0));
            $row->addChild('cell', (string) ($d->total ?? 0));
            $row->addChild('cell', (string) ($d->rejected_qty ?? 0));
            $no++;
        }

        return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
    }

    public function submitInstockSupply(Request $r)
    {
        try {
            if (!$r->filled('container_id') && !$r->filled('container_name')) {
                return response()->json(['success' => false, 'message' => 'Container wajib diisi.'], 422);
            }

            $qty = (float) $r->input('received_qty');
            $price = (float) $r->input('price');

            $warehouseTaskId = $r->input('warehouse_task_id');
            $whId = null;
            if ($warehouseTaskId) {
                $wp = DB::table('warehouse_partner')->where('warehouse_task_id', $warehouseTaskId)->first(['id']);
                $whId = $wp->id ?? null;
            }

            $cid = $this->prepareContainer([
                'container_id'     => $r->input('container_id'),
                'container_name'   => $r->input('container_name'),
                'container_volume' => $r->input('container_volume'),
            ], $whId, $qty);

            $data = [
                'received_date' => $r->input('received_date'),
                'pickup_type'  => 'PICKUP',
                'source'       => $r->input('source'),
                'received_qty' => $qty,
                'price'        => $price,
                'total'        => $qty * $price,
                'rejected_qty' => $r->input('rejected_qty') ?? 0,
                'Container_ID' => $cid,
                'created_date' => now(),
                'created_by'   => auth()->user()->username ?? 'instock',
            ];

            if ($r->filled('notes')) {
                $data['notes'] = $r->input('notes');
            }

            if ($r->filled('warehouse_task_id')) {
                $data['warehouse_task_id'] = $r->input('warehouse_task_id');
            }

            DB::table('warehouse_partner_supplies')->insert($data);

            if ($r->has('webcam_photos') && is_array($r->input('webcam_photos'))) {
                $mapTo = $r->input('warehouse_task_id') ?? $r->input('received_date');
                foreach ($r->input('webcam_photos') as $b64) {
                    if ($b64) {
                        $this->uploadService->storeFile($b64, 'base64', 'instock_photo_batch', $mapTo, 'Instock Batch Photo');
                    }
                }
            }

            return response()->json(['success' => true, 'message' => 'Instock supply added.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function previewInstockAggregation(Request $r)
    {
        $doIds = $r->input('do_ids', []);
        if (empty($doIds)) {
            return response()->json(['success' => false, 'message' => 'No Surat Jalan selected'], 400);
        }

        $data = DB::table('pickup_order as po')
            ->join('work_order as wo', 'po.WO_Id', '=', 'wo.ID')
            ->leftJoin('master_poo as mp', 'wo.POO_Id', '=', 'mp.ID')
            ->leftJoin('delivery_order as d', 'po.DO_Id', '=', 'd.ID')
            ->leftJoin('warehouse_partner as wp', 'mp.Nearest_Warehouse', '=', 'wp.id')
            ->select('po.ID', 'po.Code as PO_Code', 'po.DO_Id', 'd.Code as DO_Code', 'po.Actual_Qty', 'po.Qty', 'mp.Name as POO_Name', 'mp.Price as POO_Price', 'mp.Nearest_Warehouse', 'wp.warehouse_task_id', 'wp.warehouse_location')
            ->whereIn('po.DO_Id', $doIds)
            ->where('d.DO_Status', 'DONE')
            ->get();

        $groups = [];
        foreach ($data as $row) {
            $wh = $row->Nearest_Warehouse ?? 'Unknown';
            $display = $row->warehouse_location ?? 'Unknown';
            if ($row->warehouse_task_id) {
                $display = $row->warehouse_task_id . ' - ' . $display;
            }
            $qtyPlan = $row->Qty ?? 0;
            $qtyActual = $row->Actual_Qty ?? 0;
            $price = $row->POO_Price ?? 0;
            if (!isset($groups[$wh])) {
                $groups[$wh] = ['warehouse' => $wh, 'warehouse_display' => $display, 'do_codes' => [], 'qty_plan' => 0, 'qty_actual' => 0, 'total' => 0, 'po_details' => []];
            }
            $groups[$wh]['do_codes'][] = $row->DO_Code;
            $groups[$wh]['qty_plan'] += $qtyPlan;
            $groups[$wh]['qty_actual'] += $qtyActual;
            $groups[$wh]['total'] += $qtyActual * $price;
            $groups[$wh]['po_details'][] = [
                'po_id'          => $row->ID,
                'po_code'        => $row->PO_Code,
                'poo_name'       => $row->POO_Name,
                'do_code'        => $row->DO_Code,
                'qty_plan'       => $qtyPlan,
                'qty_actual'     => $qtyActual,
                'percentage'     => 0,
                'recommendation' => 0,
            ];
        }
        foreach ($groups as &$g) {
            $g['do_codes'] = array_unique(array_filter($g['do_codes']));
            $g['received_qty'] = $g['qty_actual'];
            $g['avg_price'] = $g['qty_actual'] > 0 ? round($g['total'] / $g['qty_actual'], 2) : 0;
            $g['containers'] = is_numeric($g['warehouse']) ? $this->getContainersForWarehouse($g['warehouse']) : [];
            foreach ($g['po_details'] as &$po) {
                $po['percentage'] = $g['qty_plan'] > 0 ? round(($po['qty_plan'] / $g['qty_plan']) * 100, 2) : 0;
                $po['recommendation'] = ($g['qty_plan'] > 0 && $g['qty_actual'] > 0) ? round(($po['qty_plan'] / $g['qty_plan']) * $g['qty_actual'], 2) : 0;
            }
        }

        return response()->json(['success' => true, 'data' => array_values($groups)]);
    }

    public function submitInstockBatch(Request $r)
    {
        $groups = $r->input('groups', []);
        if (is_string($groups)) {
            $groups = json_decode($groups, true) ?? [];
        }
        if (empty($groups)) {
            return response()->json(['success' => false, 'message' => 'No data to submit'], 400);
        }

        $notes = $r->input('notes');

        try {
            DB::beginTransaction();

            foreach ($groups as $g) {
                $qty = (float) ($g['received_qty'] ?? 0);
                $whId = $g['warehouse'] ?? null;
                $warehouseDisplay = $g['warehouse_display'] ?? ($whId ?? 'Unknown');
                $warehouse_task_id = '';
                if ($whId && is_numeric($whId)) {
                    $wp = DB::table('warehouse_partner')->where('id', $whId)->first(['warehouse_task_id']);
                    $warehouse_task_id = $wp->warehouse_task_id ?? '';
                }

                $containers = $g['containers'] ?? [];
                if (empty($containers)) {
                    throw new \Exception('Alokasi container wajib diisi untuk warehouse ' . $warehouseDisplay);
                }

                $totalQty = 0;
                foreach ($containers as $cont) {
                    $totalQty += (float) ($cont['qty'] ?? 0);
                }
                if (abs($totalQty - $qty) > 0.001) {
                    throw new \Exception('Total alokasi container (' . $totalQty . ' L) tidak sama dengan qty diterima (' . $qty . ' L) untuk warehouse ' . $warehouseDisplay);
                }

                foreach ($containers as $cont) {
                    $cQty = (float) ($cont['qty'] ?? 0);
                    $cPrice = (float) ($cont['price'] ?? ($g['avg_price'] ?? 0));
                    $cid = $this->prepareContainer($cont, is_numeric($whId) ? $whId : null, $cQty);

                    DB::table('warehouse_partner_supplies')->insertGetId([
                        'received_date'    => $r->input('received_date'),
                        'pickup_type'      => 'PICKUP',
                        'warehouse_task_id' => $warehouse_task_id,
                        'Container_ID'     => $cid,
                        'source'           => $r->input('source') ?? 'MITRA',
                        'received_qty'     => $cQty,
                        'price'            => $cPrice,
                        'total'            => $cQty * $cPrice,
                        'rejected_qty'     => $g['rejected_qty'] ?? 0,
                        'notes'            => $notes,
                        'created_date'     => now(),
                        'created_by'       => auth()->user()->username ?? 'instock',
                    ]);
                }
            }

            if ($r->has('webcam_photos') && is_array($r->input('webcam_photos'))) {
                foreach ($r->input('webcam_photos') as $b64) {
                    if ($b64) {
                        $this->uploadService->storeFile($b64, 'base64', 'instock_photo_batch', date('Y-m-d'), 'Instock Batch Photo');
                    }
                }
            }

            $recommendations = $r->input('recommendations');
            if ($recommendations && is_string($recommendations)) {
                $recommendations = json_decode($recommendations, true) ?? [];
            }
            if (!empty($recommendations)) {
                foreach ($recommendations as $poId => $rec) {
                    DB::table('pickup_order')->where('ID', $poId)->update(['Instock_Recommendation' => $rec]);
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => count($groups) . ' instock records created.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteInstockSupply(Request $r)
    {
        try {
            DB::table('warehouse_partner_supplies')->where('id', $r->input('id'))->delete();
            return response()->json(['success' => true, 'message' => 'Instock supply deleted.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    // === Container helpers for instock ===
    private function getContainerFill($containerId)
    {
        $in = DB::table('warehouse_partner_supplies')->where('Container_ID', $containerId)->sum('received_qty');
        $out = DB::table('outstock_loading')->where('Container_ID', $containerId)->sum('Actual_Qty');
        return max(0, $in - $out);
    }

    private function getContainersForWarehouse($warehouseId)
    {
        $containers = DB::table('master_container')
            ->where('Status', 'active')
            ->where('Warehouse_ID', $warehouseId)
            ->orderBy('Name')
            ->get(['ID', 'Name', 'Volume', 'Warehouse_ID']);

        $data = [];
        foreach ($containers as $c) {
            $fill = $this->getContainerFill($c->ID);
            $data[] = [
                'id'                 => $c->ID,
                'name'               => $c->Name,
                'volume'             => (float) $c->Volume,
                'current_fill'       => $fill,
                'remaining_capacity' => max(0, (float) $c->Volume - $fill),
            ];
        }
        return $data;
    }

    public function getInstockContainers(Request $r)
    {
        $warehouseId = $r->query('warehouse_id');
        if (!$warehouseId || !is_numeric($warehouseId)) {
            return response()->json(['success' => false, 'message' => 'warehouse_id required'], 400);
        }
        return response()->json(['success' => true, 'data' => $this->getContainersForWarehouse($warehouseId)]);
    }

    private function prepareContainer($container, $warehouseId, $qty)
    {
        $qty = (float) $qty;

        if (!empty($container['container_id'])) {
            $mc = DB::table('master_container')->where('ID', $container['container_id'])->where('Status', 'active')->first();
            if (!$mc) {
                throw new \Exception('Container tidak ditemukan.');
            }
            if ($warehouseId !== null && (int) $mc->Warehouse_ID !== (int) $warehouseId) {
                throw new \Exception('Container \'' . $mc->Name . '\' bukan milik warehouse terpilih.');
            }
            $cid = $mc->ID;
            $volume = (float) $mc->Volume;
        } else {
            $name = trim($container['container_name'] ?? '');
            if ($name === '') {
                throw new \Exception('Nama container baru wajib diisi.');
            }
            $volume = (float) ($container['container_volume'] ?? 0);
            if ($volume <= 0) {
                throw new \Exception('Volume container baru \'' . $name . '\' wajib diisi (dalam liter).');
            }
            $cid = DB::table('master_container')->insertGetId([
                'Name'        => $name,
                'Volume'      => $volume,
                'Warehouse_ID'=> $warehouseId,
                'Status'      => 'active',
                'Created_By'  => auth()->user()->username ?? 'instock',
                'Created_At'  => now(),
            ]);
        }

        $fill = $this->getContainerFill($cid);
        $remaining = $volume - $fill;
        if ($qty > $remaining) {
            throw new \Exception('Kapasitas container tidak cukup. Volume: ' . $volume . ' L, Terisi: ' . $fill . ' L, Sisa: ' . max(0, $remaining) . ' L, Qty: ' . $qty . ' L.');
        }

        return $cid;
    }


    public function getDeliveryOrderStatusList()
    {
        $statuses = DB::table('delivery_order')
            ->select('DO_Status')
            ->distinct()
            ->orderBy('DO_Status')
            ->pluck('DO_Status');
        return response()->json(['data' => $statuses]);
    }


    // === Outstock ===
    public function indexOutstock()
    {
        return view('partner.outstock.index');
    }

    public function getOutstockData(Request $r)
    {
        $data = DB::table('outstock')
            ->leftJoin('outstock_warehouses as ow', 'outstock.ID', '=', 'ow.Outstock_ID')
            ->leftJoin('warehouse_partner as wp', 'ow.Warehouse_ID', '=', 'wp.id')
            ->leftJoin('master_fleet as mf', 'outstock.Fleet_ID', '=', 'mf.ID')
            ->select('outstock.*', DB::raw('GROUP_CONCAT(DISTINCT wp.warehouse_location SEPARATOR ", ") as Warehouse_Names'), 'mf.Police_Number as Fleet_Number')
            ->groupBy('outstock.ID')
            ->orderBy('outstock.Created_At', 'desc')
            ->get();

        $xml = new SimpleXMLElement('<rows/>');
        $no = 1;
        foreach ($data as $d) {
            $row = $xml->addChild('row');
            $row->addAttribute('ID', $d->ID);
            $row->addChild('cell', $no);
            $row->addChild('cell', htmlspecialchars((string) $d->Code));
            $row->addChild('cell', htmlspecialchars((string) $d->Date));
            $row->addChild('cell', htmlspecialchars((string) ($d->Warehouse_Names ?? '')));
            $row->addChild('cell', htmlspecialchars((string) ($d->Fleet_Number ?? '')));
            $row->addChild('cell', htmlspecialchars((string) $d->Status));
            $no++;
        }
        return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
    }

    public function createOutstock()
    {
        return view('partner.outstock.create');
    }

    public function submitCreateOutstock(Request $r)
    {
        $r->validate([
            'Date'               => 'required|date',
            'Fleet_ID'           => 'required',
            'Warehouse_IDs'      => 'required|array|min:1',
            'Warehouse_Destination' => 'required',
        ]);

        $count = DB::table('outstock')->count();
        $code = $this->generatorService->generateApplicationCode($count, 'OST');

        try {
            $outstockId = DB::table('outstock')->insertGetId([
                'Code'               => $code,
                'Date'               => $r->input('Date'),
                'Vehicle_Category'   => $r->input('Vehicle_Category'),
                'Fleet_ID'           => $r->input('Fleet_ID'),
                'Warehouse_Origin'   => $r->input('Warehouse_IDs')[0] ?? null,
                'Warehouse_Destination'   => $r->input('Warehouse_Destination'),
                'Status'             => 'DRAFT',
                // Warehouse_Destination validated in submitCreateOutstock call
                'Notes'              => $r->input('Notes'),
                'Created_At'         => now(),
            ]);

            $whIds = $r->input('Warehouse_IDs', []);
            foreach ($whIds as $whId) {
                DB::table('outstock_warehouses')->insert([
                    'Outstock_ID' => $outstockId,
                    'Warehouse_ID' => $whId,
                ]);
            }

            return response()->json(['status' => 'success', 'message' => 'Outstock draft created.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function detailOutstock(Request $r)
    {
        $id = $r->query('id');
        if (!$id) {
            return redirect('partner-outstock');
        }

        $outstock = DB::table('outstock')
            ->leftJoin('warehouse_partner as wp', 'outstock.Warehouse_Origin', '=', 'wp.id')
            ->leftJoin('master_fleet as mf', 'outstock.Fleet_ID', '=', 'mf.ID')
            ->select('outstock.*', 'wp.warehouse_location as Warehouse_Name', 'mf.Police_Number as Fleet_Number')
            ->where('outstock.ID', $id)
            ->first();

        if (!$outstock) {
            return redirect('partner-outstock');
        }

        $whIds = DB::table('outstock_warehouses')
            ->where('Outstock_ID', $id)
            ->pluck('Warehouse_ID');

        $warehouses = DB::table('warehouse_partner')
            ->whereIn('id', $whIds)
            ->select('id', 'warehouse_location', 'warehouse_task_id')
            ->get();

        $loading = DB::table('outstock_loading')
            ->leftJoin('master_container as mc', 'outstock_loading.Container_ID', '=', 'mc.ID')
            ->select('outstock_loading.*', 'mc.Name as Container_Name', 'mc.Warehouse_ID')
            ->where('Outstock_ID', $id)
            ->get();

        // System Qty per container = pure stock ledger (stock-in - stock-out),
        // adjusted to the PRE-document state: rows belonging to THIS outstock
        // are added back so the readonly field never counts this doc against
        // itself, and missing opnames can no longer force it to zero.
        $ownDocQtyByContainer = DB::table('outstock_loading')
            ->where('Outstock_ID', $id)
            ->selectRaw('Container_ID, SUM(Actual_Qty) AS qty')
            ->groupBy('Container_ID')
            ->pluck('qty', 'Container_ID');

        foreach ($loading as $l) {
            $own = (float) ($ownDocQtyByContainer[$l->Container_ID] ?? 0);
            $l->System_Qty = $this->warehouseService->containerSystemQty($l->Container_ID) + $own;
        }

        $handover = DB::table('outstock_handover')
            ->where('Outstock_ID', $id)
            ->first();

        $containers = DB::table('master_container')
            ->where('Status', 'active')
            ->whereIn('Warehouse_ID', $whIds)
            ->select('ID', 'Name', 'Warehouse_ID')
            ->get();

        return view('partner.outstock.edit', compact('outstock', 'warehouses', 'whIds', 'loading', 'handover', 'containers'));
    }

    public function submitOutstockLoading(Request $r)
    {
        $outstockId = $r->input('outstock_id');
        $rows = $r->input('rows', []);

        try {
            DB::beginTransaction();

            // Snapshot the PRE-document system quantity: current ledger plus
            // whatever this document itself previously loaded (rows that are
            // about to be deleted). First submits and resubmits therefore
            // store the same stable baseline.
            $ownQtyByContainer = DB::table('outstock_loading')
                ->where('Outstock_ID', $outstockId)
                ->selectRaw('Container_ID, SUM(Actual_Qty) AS qty')
                ->groupBy('Container_ID')
                ->pluck('qty', 'Container_ID');

            $systemQtyByContainer = [];
            foreach ($rows as $row) {
                $cid = $row['container_id'];
                if (!array_key_exists($cid, $systemQtyByContainer)) {
                    $own = (float) ($ownQtyByContainer[$cid] ?? 0);
                    $systemQtyByContainer[$cid] = $this->warehouseService->containerSystemQty($cid) + $own;
                }
            }

            DB::table('outstock_loading')->where('Outstock_ID', $outstockId)->delete();

            foreach ($rows as $row) {
                DB::table('outstock_loading')->insert([
                    'Outstock_ID'  => $outstockId,
                    'Container_ID' => $row['container_id'],
                    'Date'         => $row['date'],
                    'System_Qty'   => $systemQtyByContainer[$row['container_id']],
                    'Actual_Qty'   => $row['actual_qty'] ?? 0,
                    'Notes'        => $row['notes'] ?? '',
                    'Created_At'   => now(),
                ]);
            }

            DB::table('outstock')->where('ID', $outstockId)->update(['Status' => 'LOADING']);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Loading data saved. Status: LOADING.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function uploadOutstockLoadingPhoto(Request $r)
    {
        $id = $r->input('id');
        if ($r->hasFile('photo') && $r->file('photo')->isValid()) {
            $file = $r->file('photo');
            $ext = $file->getClientOriginalExtension();
            $name = 'loading_' . $id . '_' . time() . '.' . $ext;
            $dir = public_path('filevendor/outstock');
            if (!is_dir($dir)) { mkdir($dir, 0777, true); }
            $file->move($dir, $name);

            DB::table('outstock_loading')->where('ID', $id)->update(['Photo_Path' => 'outstock/' . $name]);
            return response()->json(['success' => true, 'path' => 'outstock/' . $name]);
        }
        return response()->json(['success' => false, 'message' => 'No file uploaded'], 400);
    }

    public function submitOutstockHandover(Request $r)
    {
        $outstockId = $r->input('outstock_id');

        try {
            DB::beginTransaction();

            $outstock = DB::table('outstock')->where('ID', $outstockId)->first();
            if (!$outstock) {
                throw new \Exception('Outstock not found');
            }

            DB::table('outstock_handover')->where('Outstock_ID', $outstockId)->delete();

            DB::table('outstock_handover')->insert([
                'Outstock_ID'  => $outstockId,
                'Date'         => $r->input('Date'),
                'Actual_Qty'   => $r->input('Actual_Qty') ?? 0,
                'Destination'  => $r->input('Destination'),
                'Notes'        => $r->input('Notes'),
                'Created_At'   => now(),
            ]);

            DB::table('outstock')->where('ID', $outstockId)->update(['Status' => 'COMPLETED']);

            // Insert into warehouse_partner_stock_request for each warehouse
            $outstockWhs = DB::table('outstock_warehouses')
                ->where('Outstock_ID', $outstockId)
                ->pluck('Warehouse_ID');

            foreach ($outstockWhs as $whId) {
                $wp = DB::table('warehouse_partner')->where('id', $whId)->first(['warehouse_task_id', 'warehouse_location']);
                if (!$wp || !$wp->warehouse_task_id) continue;

                $loadingQty = DB::table('outstock_loading')
                    ->join('master_container as mc', 'outstock_loading.Container_ID', '=', 'mc.ID')
                    ->where('outstock_loading.Outstock_ID', $outstockId)
                    ->where('mc.Warehouse_ID', $whId)
                    ->sum('outstock_loading.Actual_Qty');

                if ($loadingQty <= 0) continue;

                $supplies = DB::table('warehouse_partner_supplies')
                    ->where('warehouse_task_id', $wp->warehouse_task_id)
                    ->where('created_date', '<=', ($r->input('Date') ?: now()) . ' 23:59:59')
                    ->get(['received_qty', 'total']);

                $avgPrice = 0;
                $totalQty = 0;
                foreach ($supplies as $s) {
                    $avgPrice = $totalQty > 0
                        ? ($avgPrice * $totalQty + $s->total) / ($totalQty + $s->received_qty)
                        : ($s->received_qty > 0 ? $s->total / $s->received_qty : 0);
                    $totalQty += $s->received_qty;
                }

                DB::table('warehouse_partner_stock_request')->insert([
                    'warehouse_task_id' => $wp->warehouse_task_id,
                    'proposed_qty'      => $loadingQty,
                    'request_qty'       => $loadingQty,
                    'request_date'      => $r->input('Date'),
                    'price'             => round($avgPrice, 2),
                    'total'             => round($loadingQty * $avgPrice, 2),
                    'reference'         => 'OUTSTOCK: ' . $outstock->Code . ' - ' . ($wp->warehouse_location ?? 'WH#' . $whId),
                    'remark'            => 'Auto-generated from outstock completion',
                    'created_date'      => now(),
                    'created_by'        => auth()->user()->username ?? 'outstock',
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Handover saved. Outstock completed.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function uploadOutstockHandoverPhoto(Request $r)
    {
        $id = $r->input('id');
        if ($r->hasFile('photo') && $r->file('photo')->isValid()) {
            $file = $r->file('photo');
            $ext = $file->getClientOriginalExtension();
            $name = 'handover_' . $id . '_' . time() . '.' . $ext;
            $dir = public_path('filevendor/outstock');
            if (!is_dir($dir)) { mkdir($dir, 0777, true); }
            $file->move($dir, $name);

            DB::table('outstock_handover')->where('ID', $id)->update(['Photo_Path' => 'outstock/' . $name]);
            return response()->json(['success' => true, 'path' => 'outstock/' . $name]);
        }
        return response()->json(['success' => false, 'message' => 'No file uploaded'], 400);
    }


    public function generateDeliveryNote(Request $r)
    {
        $id = $r->query('id');
        $warehouseId = $r->query('warehouse_id');

        if (!$id || !$warehouseId) {
            return response()->json(['success' => false, 'message' => 'id and warehouse_id required'], 400);
        }

        $outstock = DB::table('outstock')
            ->leftJoin('master_fleet as mf', 'outstock.Fleet_ID', '=', 'mf.ID')
            ->select('outstock.*', 'mf.Police_Number as Fleet_Number')
            ->where('outstock.ID', $id)
            ->first();

        $warehouse = DB::table('warehouse_partner')->where('id', $warehouseId)->first();

        $actualWeight = DB::table('outstock_loading')
            ->join('master_container as mc', 'outstock_loading.Container_ID', '=', 'mc.ID')
            ->where('outstock_loading.Outstock_ID', $id)
            ->where('mc.Warehouse_ID', $warehouseId)
            ->sum('outstock_loading.Actual_Qty');

        $templatePath = public_path('template-outstock-surat-jalan.docx');
        if (!file_exists($templatePath)) {
            return response()->json(['success' => false, 'message' => 'Template not found'], 500);
        }

        $template = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);
        $template->setValues([
            'effective_date'         => now()->format('d-m-Y'),
            'doc_id'                 => 'OPR/FORM/0001',
            'dn_code'                => $outstock->Code,
            'issued_date'            => $outstock->Date,
            'warehouse_destination'  => '-',
            'vehicle_type'           => '-',
            'vehicle_police_number'  => $outstock->Fleet_Number ?? '-',
            'driver_name'            => '-',
            'driver_phone'           => '-',
            'actual_weight'          => (string) $actualWeight,
            'warehouse_origin'       => $warehouse->warehouse_location ?? '-',
        ]);

        $safeCode = str_replace('/', '-', $outstock->Code);
        $filename = 'Surat_Jalan_' . $safeCode . '_' . $warehouse->warehouse_location . '.docx';
        $dir = public_path('filevendor/outstock');
        if (!is_dir($dir)) { mkdir($dir, 0775, true); }
        $filePath = $dir . '/' . $filename;
        $template->saveAs($filePath);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }



    private function getOrderCoordinate($order): ?array
    {
        $lat = $order->Lat ?? null;
        $lng = $order->Lng ?? null;

        // Fallback: parse Location_Coordinate if Lat/Lng are empty
        if (!$lat || !$lng) {
            $coord = $order->Location_Coordinate ?? "";
            if (preg_match("/^\\-?\\d+(?:\\.\\d+)?,\\-?\\d+(?:\\.\\d+)?$/", $coord)) {
                $parts = explode(",", $coord);
                $lat = trim($parts[0]);
                $lng = trim($parts[1]);
            }
        }

        if (!$lat || !$lng) {
            return null;
        }
        return [(float)$lng, (float)$lat];
    }
}