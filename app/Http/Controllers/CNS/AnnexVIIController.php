<?php

namespace App\Http\Controllers\CNS;

use App\AnnexVII;
use App\Http\Controllers\Controller;
use App\Services\CNS\CnsCodeGenerator;
use App\Support\DhtmlxGridPagination;
use Illuminate\Http\Request;
use App\ShippingInstruction;
use App\VesselSchedule;
use App\VesselScheduleDtl;
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
        $query = AnnexVII::query();

        if ($request->filled('tgl1') && $request->filled('tgl2')) {
            $query->whereBetween("created_at", [$request->tgl1 . ' 00:00:00', $request->tgl2 . " 23:59:59"]);
        }

        // Column filters from dhtmlx header
        if ($request->has('filters') && is_array($request->query('filters'))) {
            foreach ($request->query('filters') as $colIdx => $val) {
                $val = trim((string) $val);
                if ($val === '') continue;
                if ($colIdx == 1) $query->where('Annex_No', 'like', "%{$val}%");
                if ($colIdx == 2) $query->where('Final_SI_No', 'like', "%{$val}%");
                if ($colIdx == 3) $query->where('Order_No', 'like', "%{$val}%");
                if ($colIdx == 4) $query->where('Shipper', 'like', "%{$val}%");
                if ($colIdx == 5) $query->where('Consignee', 'like', "%{$val}%");
                if ($colIdx == 6) $query->where('SI_Date', 'like', "%{$val}%");
                if ($colIdx == 7) $query->where('actual_quantity', 'like', "%{$val}%");
                if ($colIdx == 8) $query->where('POL', 'like', "%{$val}%");
                if ($colIdx == 9) $query->where('POD', 'like', "%{$val}%");
                if ($colIdx == 10) $query->where('BL_No', 'like', "%{$val}%");
                if ($colIdx == 11) $query->where('HS_Code', 'like', "%{$val}%");
                if ($colIdx == 12) $query->where('Status', $val);
            }
        }

        $colMap = [
            1 => 'Annex_No',
            2 => 'Final_SI_No',
            3 => 'Order_No',
            4 => 'Shipper',
            5 => 'Consignee',
            6 => 'SI_Date',
            7 => 'actual_quantity',
            8 => 'POL',
            9 => 'POD',
            10 => 'BL_No',
            11 => 'HS_Code',
            12 => 'Status',
        ];

        $paginated = DhtmlxGridPagination::paginateQuery($query, $request, [
            'col_map' => $colMap,
            'default_page_size' => 25,
        ]);

        $data = $paginated['rows'];
        $total = $paginated['total'];
        $posStart = $paginated['posStart'];

        $bodyXml = "";
        $no = $posStart + 1;

        foreach ($data as $row) {
            $status = $row->Status ?? 'APPROVED';
            $bodyXml .= "<row id='" . $row->id . "'>";
            $bodyXml .= "<cell><![CDATA[" . $no++ . "]]></cell>";
            $bodyXml .= "<cell><![CDATA[" . $row->Annex_No . "]]></cell>";
            $bodyXml .= "<cell><![CDATA[" . $row->Final_SI_No . "]]></cell>";
            $bodyXml .= "<cell><![CDATA[" . $row->Order_No . "]]></cell>";
            $bodyXml .= "<cell><![CDATA[" . $row->Shipper . "]]></cell>";
            $bodyXml .= "<cell><![CDATA[" . $row->Consignee . "]]></cell>";
            $bodyXml .= "<cell><![CDATA[" . $row->SI_Date . "]]></cell>";
            $bodyXml .= "<cell><![CDATA[" . $row->actual_quantity . "]]></cell>";
            $bodyXml .= "<cell><![CDATA[" . $row->POL . "]]></cell>";
            $bodyXml .= "<cell><![CDATA[" . $row->POD . "]]></cell>";
            $bodyXml .= "<cell><![CDATA[" . $row->BL_No . "]]></cell>";
            $bodyXml .= "<cell><![CDATA[" . $row->HS_Code . "]]></cell>";
            $bodyXml .= "<cell><![CDATA[" . $status . "]]></cell>";
            $bodyXml .= "</row>\n";
        }

        return DhtmlxGridPagination::response($total, $posStart, $bodyXml);
    }

    public function newAnnex()
    {
        $usedFinalSiNos = AnnexVII::whereNotNull('SI_No')
            ->where('SI_No', '!=', '')
            ->pluck('SI_No')
            ->all();

        $shipments = ShippingInstruction::where('Status', 'APPROVED')
            ->whereNotNull('Final_SI_No')
            ->where('Final_SI_No', '!=', '')
            ->when(!empty($usedFinalSiNos), function ($query) use ($usedFinalSiNos) {
                $query->whereNotIn('Final_SI_No', $usedFinalSiNos);
            })
            ->groupBy('Final_SI_No')
            ->get();
        $comps = DB::table('supplier')->get();

        return view('cns.export.annex-new', compact('shipments', 'comps'));
    }

    public function getSI(Request $request)
    {
        $query = ShippingInstruction::where('Status', 'APPROVED')
            ->whereNotNull('Final_SI_No')
            ->where('Final_SI_No', '!=', '');

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

        if (AnnexVII::where('SI_No', $shipment->Final_SI_No)->exists()) {
            return response()->json(['error' => 'Final SI already has an Annex VII'], 422);
        }

        $shipment->SI_Date = $shipment->Final_SI_Date ?: $shipment->SI_Date;
        foreach (['SI_Date', 'ETD', 'ETA', 'PEB_Date'] as $dateField) {
            if (!empty($shipment->{$dateField})) {
                $shipment->{$dateField} = substr((string) $shipment->{$dateField}, 0, 10);
            }
        }

        $supplierIds = array_values(array_unique(array_filter([
            $shipment->Shipper,
            $shipment->Consignee,
        ])));
        $suppliers = empty($supplierIds)
            ? collect()
            : DB::table('supplier')->whereIn('Supplier_ID', $supplierIds)->get()->keyBy('Supplier_ID');

        $shipper = $suppliers->get($shipment->Shipper);
        $shipment->arranger_address = $shipper->Address ?? '';
        $shipment->arranger_contact = $shipper->Contact_Person ?? '';
        $shipment->arranger_tel = $shipper ? ($shipper->Phone ?: ($shipper->HP ?? '')) : '';
        $shipment->arranger_email = $shipper->Email ?? '';

        $consignee = $suppliers->get($shipment->Consignee);
        $shipment->consignee_email = $consignee->Email ?? '';
        $shipment->consignee_telp = $consignee ? ($consignee->Phone ?: ($consignee->HP ?? '')) : '';

        if (trim((string) $shipment->Contract_No) === '') {
            $vs = !empty($shipment->Order_No)
                ? VesselSchedule::where('Order_No', $shipment->Order_No)->first()
                : null;
            if ($vs && trim((string) $vs->Contract_No) !== '') {
                $shipment->Contract_No = $vs->Contract_No;
            } elseif ($vs && !empty($vs->Contract_ID)) {
                $shipment->Contract_No = (string) (DB::table('cns_buyer_contracts')->where('ID', $vs->Contract_ID)->value('Contract_No') ?? '');
            }
            if (trim((string) $shipment->Contract_No) === '') {
                $shipment->Contract_No = (string) (DB::table('packing_list')->where('Final_SI_No', $shipment->Final_SI_No)->value('Contract_No') ?? '');
            }
        }

        $pod = trim((string) $shipment->POD);
        if ($pod !== '') {
            $shipment->Destination_Port = $pod;
        } else {
            $destPort = trim((string) $shipment->Destination_Port);
            if ($destPort !== '') {
                $destSupplierName = DB::table('supplier')->where('Supplier_ID', $destPort)->value('Company_Name');
                if (!empty($destSupplierName)) {
                    $shipment->Destination_Port = $destSupplierName;
                }
            }
        }

        $details = VesselScheduleDtl::active()->where('SI_No', $shipment->SI_No)->get();
        $shipment->actual_quantity = $details->isNotEmpty()
            ? round((float) $details->sum('Netto_KG'), 2)
            : ($shipment->Qty ?? '');

        $containers = $details->pluck('Container')->filter(function ($val) {
            return trim((string) $val) !== '';
        })->values();
        for ($i = 1; $i <= 5; $i++) {
            $shipment->{"Container{$i}"} = $containers->get($i - 1, '');
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
            'SI_No' => 'nullable|string|max:100',
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

        $finalSiNo = trim((string) ($request->input('SI_No') ?: $request->input('Final_SI_No')));
        if ($finalSiNo === '') {
            return response()->json(['error' => 'Final SI No is required'], 422);
        }

        $approvedShipment = ShippingInstruction::where('Final_SI_No', $finalSiNo)
            ->where('Status', 'APPROVED')
            ->first();

        if (!$approvedShipment) {
            return response()->json(['error' => 'Selected Final SI is not APPROVED'], 422);
        }

        if (AnnexVII::where('SI_No', $finalSiNo)->exists()) {
            return response()->json(['error' => 'Final SI already has an Annex VII'], 422);
        }

        // prepare data: take only fillable keys from request
        $data = $request->only((new AnnexVII)->getFillable());
        $data['SI_No'] = $finalSiNo;

        // Add created_by/updated_by if auth available
        if (Auth::check()) {
            $data['created_by'] = Auth::user()->username ?? Auth::id();
            $data['updated_by'] = Auth::user()->username ?? Auth::id();
        }

        DB::transaction(function () use ($data) {
            $data['Annex_No'] = CnsCodeGenerator::nextAnnexNo(null, true);
            AnnexVII::create($data);
        });

        // return response()->json(['success' => true, 'id' => $annex->id, 'message' => 'Annex created']);
        echo "success";
    }
}
