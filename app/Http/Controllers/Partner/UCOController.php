<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Services\GeneratorService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Safe\Exceptions\SimplexmlException;
use SimpleXMLElement;

class UCOController extends Controller
{

    private $userService;
    private $generatorService;
    public function __construct(UserService $u, GeneratorService $g)
    {
        $this->userService = $u;
        $this->generatorService = $g;
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

    public function trackExportedStockData(Request $r)
    {
        $q = $r->query();
        $attachment = false;

        if ($r->filled('ID')) {
            return $this->trackExportedStockDataById($r);
        }


        $month    = $r->query('month');    // e.g. "2026-04"
        $policeNo = $r->query('police_no');

        $vs = DB::table('vessel_schedules as vs')
            ->join('vessel_schedule_dtls as vsdtl', 'vsdtl.Task_ID', '=', 'vs.Task_ID')
            ->select('vs.*', 'vs.Created_Date as Scheduled_At', 'vsdtl.*')
            ->when($month,    fn($q) => $q->whereRaw("DATE_FORMAT(vs.Created_Date, '%Y-%m') = ?", [$month]))
            ->when($policeNo, fn($q) => $q->where(
                fn($q2) => $q2
                    ->where('vsdtl.Police_No', $policeNo)
                    ->orWhere('vsdtl.Container', $policeNo)
            ))
            ->get();

        $prevMonth = $month
            ? Carbon::createFromFormat('Y-m', $month)->subMonth()->format('Y-m')
            : Carbon::now()->subMonth()->format('Y-m');

        $openingStock = DB::table('vessel_schedules as vs')
            ->join('vessel_schedule_dtls as vsdtl', 'vsdtl.Task_ID', '=', 'vs.Task_ID')
            ->whereRaw("DATE_FORMAT(vs.Created_Date, '%Y-%m') = ?", [$prevMonth])
            ->when($policeNo, fn($q) => $q->where(
                fn($q2) => $q2
                    ->where('vsdtl.Police_No', $policeNo)
                    ->orWhere('vsdtl.Container', $policeNo)
            ))
            ->sum(DB::raw('vsdtl.Stock_In - vsdtl.Stock_Out'));

        $xml = new SimpleXMLElement('<rows/>');

        $counter = 1;
        $runningStock = $openingStock;

        foreach ($vs as $vs) {

            $row = $xml->addChild('row');
            $runningStock = $runningStock + $vs->Stock_In - $vs->Stock_Out;
            $currentStock = $runningStock;
            // dd($currentStock, $vs->Stock_In, $vs->Stock_Out);

            $row->addChild('cell', $counter);
            $row->addChild('cell', Carbon::parse($vs->Scheduled_At)->format('F'));
            $row->addChild('cell', Carbon::parse($vs->Scheduled_At)->format('d-M-Y'));

            $row->addChild('cell', 'Pelindo');
            $row->addChild('cell', $vs->Police_No);

            // $row->addChild('cell', $vs->Task_ID);
            // $row->addChild('cell', $vs->Order_No);

            // $row->addChild('cell', $vs->POL);
            $row->addChild('cell', $vs->Qty_SJ);
            $row->addChild('cell', $vs->Stock_In);
            $row->addChild('cell', $vs->Stock_Out);
            $row->addChild('cell', $currentStock);
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
        $vs = DB::table('vessel_schedules as vs')
            ->join('vessel_schedule_dtls as vsdtl', 'vsdtl.Task_ID', '=', 'vs.Task_ID')
            ->select(
                'vs.id',
                'vs.Created_Date as Date',
                'vsdtl.Police_No',
                'vsdtl.Note',
                'vsdtl.Status',
                'vsdtl.Qty_SJ',
                'vsdtl.Stock_In',
                'vsdtl.Stock_Out'
            )
            ->where('vsdtl.ID', '=', $r->query('ID'))
            ->get();

        return response()->json($vs);
    }

    public function submitExportedStock(Request $r)
    {
        $id = DB::table('vessel_schedule')
            ->insertGetId($r->only([
                'Order_No',
                'Qty',
                'Qty1',
                'Qty2',
                'Vessel',
                'POL',
                'POD',
                'Stuffing',
                'Checking_BC',
                'Closing_CY',
                'ETD',
                'ATD',
                'ETA',
                'ATA',
                'Note',
                'Updated_By',
                'Updated_Date',
            ]));

        return response()->json(['success' => true, 'message' => 'Record created successfully']);
    }

    public function updateExportedStock(Request $r)
    {
        DB::table('vessel_schedule_dtls')
            ->where('id', $r->id)
            ->update($r->only([
                "Date",
                "Police_No",
                "Note",
                "Status",
                "Qty_SJ",
                "Stock_In",
                "Stock_Out",
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
            ->select(
                'wo.ID',
                'wo.POO_Id',
                'mp.Name as POO_Name',
                'wo.Status',
                'wo.Code',
                DB::raw("DATE_FORMAT(wo.Work_Order_Date, '%Y-%m-%d') as Work_Order_Date"),
                'wo.Created_At',
                'wo.Updated_At'
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

        $count = DB::table('work_order')->count();

        try {
            DB::beginTransaction();

            DB::table('work_order')->insert([
                'POO_Id'          => $r->input('POO_Id'),
                'Code'            => $this->generatorService->generateApplicationCode($count, 'WO'),
                'Status'          => $r->input('Status'),
                'Work_Order_Date' => $r->input('Work_Order_Date'),
            ]);

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
        $rows = $this->fetchPickupOrders($id);

        if ($r->query('format') === 'xml') {
            return $this->pickupOrdersAsXml($rows);
        }

        return $this->pickupOrdersAsJson($rows);
    }

    private function fetchPickupOrders(?string $id)
    {
        $query = DB::table('pickup_order')->select('*');

        if ($id) {
            $query->where('id', $id);
        }

        return $query->get();
    }

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
        try {
            DB::transaction(function () use ($r) {
                DB::table('delivery_order')->insert([
                    'PO_Id'   => $r->input('PO_Id'),
                    'DO_Status'  => $r->input('DO_Status'),
                    'DO_Date' => $r->input('DO_Date'),
                    'Start_Time' => $r->input('Start_Time'),
                    'Finish_Time' => $r->input('Finish_Time'),
                    'Created_At' => now(),
                ]);
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
        $id   = $r->query('id');
        $rows = DB::table('delivery_order as do')
            ->leftJoin('pickup_order as po', 'po.ID', '=', 'do.PO_Id')
            ->leftJoin('work_order as wo', 'wo.ID', '=', 'po.WO_Id')
            ->leftJoin('master_poo as mp', 'mp.ID', '=', 'wo.POO_Id')
            ->select('do.id', 'do.Code', 'do.DO_Date', 'do.DO_Status', 'po.Site', 'mp.Name as POO_Name')
            ->when($id, fn($q) => $q->where('do.id', $id))
            ->get();

        if ($r->query('format') === 'xml') {
            $xml     = new SimpleXMLElement('<rows/>');
            $counter = 1;

            foreach ($rows as $row) {
                $item = $xml->addChild('row');
                $item->addAttribute('ID', $row->id);

                $item->addChild('cell', $counter);
                $item->addChild('cell', htmlspecialchars((string) $row->id));
                $item->addChild('cell', htmlspecialchars((string) $row->Code));
                $item->addChild('cell', htmlspecialchars((string) $row->DO_Date));
                $item->addChild('cell', htmlspecialchars((string) $row->Site));
                $item->addChild('cell', htmlspecialchars((string) $row->POO_Name));
                $item->addChild('cell', htmlspecialchars((string) $row->DO_Status));

                $counter++;
            }

            return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
        }

        return response()->json(['data' => $rows]);
    }


    public function createTicket(Request $r) {}

    public function getTicket(Request $r) {}
}
