<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Safe\Exceptions\SimplexmlException;
use SimpleXMLElement;

class UCOReportController extends Controller
{

    private $userService;
    public function __construct(UserService $u)
    {
        $this->userService = $u;
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
            ->when($policeNo, fn($q) => $q->where(fn($q2) => $q2
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
            ->when($policeNo, fn($q) => $q->where(fn($q2) => $q2
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
        DB::table('vessel_schedules')
            ->where('id', $r->id)
            ->update($r->only([
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
            ->where('vsdtl.Container', '!=', '')
            ->select('vsdtl.Container as police_license', 'vs.Vessel as vessel_name')
            ->distinct()
            ->get();

        return response()->json(['status' => 'success', 'data' => $list]);
    }
}
