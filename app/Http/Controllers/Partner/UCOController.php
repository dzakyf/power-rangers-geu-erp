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
    public function __construct(UserService $u, GeneratorService $g, UploadService $up)
    {
        $this->userService = $u;
        $this->generatorService = $g;
        $this->uploadService = $up;
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

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Record created successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateExportedStock(Request $r)
    {
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

        return response()->json(['success' => true, 'message' => 'Record updated successfully']);
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
                'wo.Status',
                'wo.Code',
                DB::raw("DATE_FORMAT(wo.Work_Order_Date, '%Y-%m-%d') as Work_Order_Date"),
                'wo.Created_At',
                'wo.Updated_At',
                'mp.Created_By as POO_Created_By',
                'u.Name as POO_Created_By_Name'
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
                'Work_Order_Date' => $r->input('Work_Order_Date'),
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
                    'Site'        => $poo->City,
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

        return $query->get();
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
            ->select('do.ID', 'do.Code as DO_Code', 'do.DO_Date')
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
                $item->addChild('cell', htmlspecialchars((string) $row->DO_Date));

                $counter++;
            }

            return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
        }

        return response()->json(['data' => $rows]);
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

        $xml = new SimpleXMLElement('<rows></rows>');
        $no  = 1;

        foreach ($rows as $row) {
            $item = $xml->addChild('row');
            $item->addAttribute('ID', $row->ID);
            $item->addChild('cell', $no);
            $item->addChild('cell', $row->POO_Name);
            $item->addChild('cell', $row->POO_Full_Address);
            $item->addChild('cell', $row->PO_Code);
            $item->addChild('cell', $row->Qty);
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

        // Add warehouse as starting point if selected
        $whId = $r->input('warehouse_id');
        $warehouse = null;
        if ($whId) {
            $warehouse = DB::table('warehouse_partner')->where('id', $whId)->first(['lat', 'lng']);
            if ($warehouse && $warehouse->lat && $warehouse->lng) {
                $waypoints[] = [(float)$warehouse->lng, (float)$warehouse->lat];
            }
        }

        foreach ($orders as $order) {
            if (!$order->Lat || !$order->Lng) continue;
            $waypoints[] = [(float)$order->Lng, (float)$order->Lat];
        }

        // End back at warehouse
        if ($warehouse && $warehouse->lat && $warehouse->lng) {
            $waypoints[] = [(float)$warehouse->lng, (float)$warehouse->lat];
        }

        if (count($waypoints) < 2) {
            return response()->json(['status' => 'error', 'message' => 'Need at least 2 valid coordinates'], 400);
        }

        try {
            $osrm = new \App\Services\OSRMService();
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

        // Add warehouse as starting point if selected
        $whId = $r->input('warehouse_id');
        $warehouse = null;
        if ($whId) {
            $warehouse = DB::table('warehouse_partner')->where('id', $whId)->first(['lat', 'lng']);
            if ($warehouse && $warehouse->lat && $warehouse->lng) {
                $waypoints[] = [(float)$warehouse->lng, (float)$warehouse->lat];
            }
        }

        foreach ($orders as $order) {
            if (!$order->Lat || !$order->Lng) continue;
            $waypoints[] = [(float)$order->Lng, (float)$order->Lat];
        }

        // End back at warehouse
        if ($warehouse && $warehouse->lat && $warehouse->lng) {
            $waypoints[] = [(float)$warehouse->lng, (float)$warehouse->lat];
        }

        if (count($waypoints) < 2) {
            return response()->json(['status' => 'error', 'message' => 'Need at least 2 valid coordinates'], 400);
        }

        try {
            $osrm = new \App\Services\OSRMService();
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
                'APC'                      => $r->input('apc', 0),
            ]);

            $seq = 1;
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


    public function finishSettlement(Request $r)
    {
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
                $year  = Carbon::now()->year;
                $month = Carbon::now()->format('m');

                foreach ($sections as $sec) {
                    $nominal = $r->input("{$sec['key']}_nominal");
                    $notes   = $r->input("{$sec['key']}_keterangan");


                    if (empty($nominal) && empty($notes)) {
                        continue;
                    }

                    $settlementId = DB::table('settlement')->insertGetId([
                        'Route_Id'       => $routeIds,
                        'Description' => $sec['label'],
                        'Nominal'     => $nominal,
                        'Notes'       => $notes,
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
                        ->update(['Status' => 'SETTLED']);
                }
            });

            return response()->json(['status' => 'success', 'message' => 'Settlement berhasil disimpan.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    public function indexSettlement()
    {
        return view('finance.ops.settlement.index');
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
                'po.Total_Cost',
                'po.Actual_Nominal_Transfer',
                'po.Actual_Qty',
                'po.Acquisition_Status',
                'po.Acquisition_Submitted_At',
                'po.Acquisition_Approved_At',
                'po.Acquisition_Approved_By',
                'po.Acquisition_Notes'
            )
            ->where('d.DO_Status', 'DONE');

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
    public function updateInstockPhoto(Request $r)
    {
        DB::beginTransaction();
        try {
            if ($r->filled('notes')) {
                DB::table('pickup_order')->where('ID', $r->po_id)
                    ->update(['Description' => $r->notes]);
            }

            if ($r->filled('webcam_photo')) {
                $this->uploadService->storeFile(
                    $r->webcam_photo,
                    'base64',
                    'instock_photo',
                    $r->po_id,
                    'Instock Webcam Photo'
                );
            }

            if ($r->hasFile('upload_photo')) {
                $this->uploadService->storeFile(
                    $r->file('upload_photo'),
                    'upload',
                    'instock_photo',
                    $r->po_id,
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
}
