<?php

namespace App\Http\Controllers\CNS;

use App\AnnexVII;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\ShipmentInstruction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnnexVIIController extends Controller
{
    public function __construct()
    {
        // throw new \Exception('Not implemented');
    }

    public function indexAnnex()
    {
        return view('cns.export.annex-index');
    }

    public function indexAnnexXml(Request $request)
    {
        header("Content-type:text/xml");
        echo '<?xml version="1.0" encoding="UTF-8"?>';

        echo "<rows>";

        $data = AnnexVII::whereBetween("created_at", [$request->tgl1 . ' 00:00:00', $request->tgl2 . " 23:59:59"])->get();
        $no = 1;

        foreach($data as $row)
        {
            echo "<row id='".$row->id."'>";

            echo "<cell><![CDATA[".$no++."]]></cell>";
            echo "<cell><![CDATA[".$row->Annex_No."]]></cell>";
            echo "<cell><![CDATA[".$row->Final_SI_No."]]></cell>";
            echo "<cell><![CDATA[".$row->Order_No."]]></cell>";
            echo "<cell><![CDATA[".$row->Shipper."]]></cell>";
            echo "<cell><![CDATA[".$row->Consignee."]]></cell>";
            echo "<cell><![CDATA[".$row->SI_Date."]]></cell>";
            echo "<cell><![CDATA[".$row->actual_quantity."]]></cell>";
            echo "<cell><![CDATA[".$row->POL."]]></cell>";
            echo "<cell><![CDATA[".$row->POD."]]></cell>";
            echo "<cell><![CDATA[".$row->BL_No."]]></cell>";
            echo "<cell><![CDATA[".$row->HS_Code."]]></cell>";

            echo "</row>";
        }

        echo "</rows>";
    }

    public function newAnnex()
    {
        // $shipment = null;
        // // accept SI_No or id parameter to lookup shipment
        // if ($request->has('SI_No')) {
        //     $shipment = ShipmentInstruction::where('SI_No', $request->SI_No)->first();
        // } elseif ($request->has('id')) {
        //     $shipment = ShipmentInstruction::where('id', $request->id)->first();
        // }

        $shipments = ShipmentInstruction::where("Status", "FINAL")->groupBy('SI_No')->get();
        $comps = DB::table('supplier')->get();

        return view('cns.export.annex-new', compact('shipments', 'comps'));
    }

    public function getSI(Request $request)
    {
        $query = ShipmentInstruction::query();

        if ($request->has('Final_SI_No')) {
            $query->where('Final_SI_No', $request->Final_SI_No);
        } elseif ($request->has('id')) {
            $query->where('id', $request->id);
        } else {
            return response()->json(['error' => 'Final_SI_No or id parameter required'], 400);
        }

        $shipment = $query->first();

        if (!$shipment) {
            return response()->json(['error' => 'Shipping Instruction not found'], 404);
        }

        return response()->json($shipment);
    }

    public function getAnnexData(Request $r)
    {
        return;
    }

    public function submitAnnexData() {}

    public function detailAnnexData() {}

    public function deleteAnnexDatra() {}

    public function printAnnex()
    {
        return view('cns.export.print.annex-print');
    }

    public function printAnnexSample()
    {
        return view('cns.export.print.annex-print-sample');
    }
    
    /**
     * Store a new Annex VII record.
     */
    public function store(Request $request)
    {
        // Basic validation (adjust rules as needed)
        $validated = $request->validate([
            'Final_SI_No' => 'nullable|string|max:100',
            'Order_No' => 'nullable|string|max:100',
            'Qty1' => 'nullable|string|max:50',
            'Qty2' => 'nullable|string|max:50',
            'SI_Date' => 'nullable|date',
            'Shipper' => 'nullable|string',
            'Consignee' => 'nullable|string',
            'Product_Name' => 'nullable|string',
            'HS_Code' => 'nullable|string',
            'PEB_Date' => 'nullable|date',
            'declarant_date' => 'nullable|date',
        ]);

        $getRomawi = function($month) {
            $romawi = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
            return $romawi[(int)$month - 1] ?? '';
        };

        
        $counter = AnnexVII::where('created_at', "LIKE", date("Y"). "%")->count();

        $InvNo = sprintf('%03d',$counter + 1) . "/ANX/" . $getRomawi(date("n")) . "/" . date("Y");
        // prepare data: take only fillable keys from request
        $data = $request->only((new AnnexVII)->getFillable());

        // Add created_by/updated_by if auth available
        if (Auth::check()) {
            $data['created_by'] = Auth::user()->username ?? Auth::id();
            $data['updated_by'] = Auth::user()->username ?? Auth::id();
        }
        $data['Annex_No'] = $InvNo;

        $annex = AnnexVII::create($data);

        // return response()->json(['success' => true, 'id' => $annex->id, 'message' => 'Annex created']);
        echo "success";
    }
}
