<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use App\ShipmentInstruction;
use App\VesselSchedule;
use App\VesselScheduleDtl;
use App\VesselScheduleDtlLog;
use App\VesselScheduleLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;

class ShipmentInstructionController extends Controller
{
    public function indexShipmentInstruction()
    {
        return view('cns.export.si');
    }

    public function indexShipmentInstructionXml(Request $request)
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $vsds = VesselScheduleDtl::whereNotNull('SI_No')->get();
        $vsdData = [];
        foreach ($vsds as $key => $vsd) {
            if (!isset($vsdData[$vsd->SI_No])) {
                $vsdData[$vsd->SI_No] = [
                    'list' => [],
                    'tare' => 0,
                    'netto_kg' => 0,
                    'netto_mt' => 0,
                    'gross_kg' => 0,
                    'gross_mt' => 0
                ];
            }
            $vsdData[$vsd->SI_No]['list'][] = $vsd->Container . " (" . $vsd->Seal . ")";
            $vsdData[$vsd->SI_No]['tare'] += $vsd->Tare;
            $vsdData[$vsd->SI_No]['netto_kg'] += $vsd->Netto_KG;
            $vsdData[$vsd->SI_No]['netto_mt'] += $vsd->Netto_MT;
            $vsdData[$vsd->SI_No]['gross_kg'] += $vsd->Gross_KG;
            $vsdData[$vsd->SI_No]['gross_mt'] += $vsd->Gross_MT;
        }

        $shipments = ShipmentInstruction::whereBetween('SI_Date', [$request->tgl1, $request->tgl2])->where('Status', "CREATED")->get();
        foreach ($shipments as $key => $shipment) {
            echo "<row id=\"$shipment->id\" >";
            echo "<cell>" . ($key + 1) . "</cell>";
            echo "<cell>" . htmlspecialchars($shipment->Order_No) . "</cell>";
            echo "<cell>" . htmlspecialchars($shipment->SI_No) . "</cell>";
            echo "<cell>" . htmlspecialchars(date("d F Y", strtotime($shipment->SI_Date))) . "</cell>";
            if (isset($vsdData[$shipment->SI_No])) {
                echo "<cell>" . htmlspecialchars(implode(', ', $vsdData[$shipment->SI_No]['list'])) . "</cell>";
                echo "<cell>" . htmlspecialchars($vsdData[$shipment->SI_No]['tare']) . "</cell>";
                echo "<cell>" . htmlspecialchars($vsdData[$shipment->SI_No]['netto_kg']) . "</cell>";
                echo "<cell>" . htmlspecialchars($vsdData[$shipment->SI_No]['netto_mt']) . "</cell>";
                echo "<cell>" . htmlspecialchars($vsdData[$shipment->SI_No]['gross_kg']) . "</cell>";
                echo "<cell>" . htmlspecialchars($vsdData[$shipment->SI_No]['gross_mt']) . "</cell>";
            }else {
                echo "<cell></cell>";
                echo "<cell></cell>";
                echo "<cell></cell>";
                echo "<cell></cell>";
                echo "<cell></cell>";
                echo "<cell></cell>";
            }
            echo "</row>";
        }

        echo "</rows>";
    }

    public function printShipmentInstruction(Request $request)
    {

        // $gross = 0;
        // $netto = 0;
        // $mt = 0;
        // foreach ($vsds as $key => $value) {
        // $gross += $value->Gross_KG;
        // $netto += $value->Netto_KG;
        // $mt += round($value->Netto_KG/1000,2);
        // }
        // return view('cns.export.print.si', compact('shipment', 'vsds','companies'));
        // Controller

        // ── Ambil data (sesuaikan dengan model Anda) ──────────────────────
        $companies = [];
        $company = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        foreach ($company as $key => $value) {
            $companies[$value->Supplier_ID] = $value;
        }
        $shipment = ShipmentInstruction::where('SI_No', $request->Task)->first();
        $vsds = VesselScheduleDtl::where('SI_No', $request->Task)->get();
 
        // ── Hitung total ──────────────────────────────────────────────────
        $gross = $vsds->sum('Gross_KG');
        $netto = $vsds->sum('Netto_KG');
        $mt    = round($netto / 1000, 2);
 
        // ── Render HTML dari Blade view ───────────────────────────────────
        $html = view('cns.export.print.si', compact(
            'shipment', 'vsds', 'companies', 'gross', 'netto', 'mt'
        ))->render();
 
        // ── Header & Footer HTML ──────────────────────────────────────────
        $letterhead = public_path($companies[$shipment->Shipper]->Letterhead);
        $footerImg  = public_path($companies[$shipment->Shipper]->Footer);
 
        // Konversi ke base64 agar mPDF bisa render gambar
        $headerB64 = base64_encode(file_get_contents($letterhead));
        $footerB64 = base64_encode(file_get_contents($footerImg));
        $headerExt = strtolower(pathinfo($letterhead, PATHINFO_EXTENSION));
        $footerExt = strtolower(pathinfo($footerImg,  PATHINFO_EXTENSION));
 
        $headerHtml = '
            <div style="margin:0;padding:0;line-height:0;">
                <img src="data:image/' . $headerExt . ';base64,' . $headerB64 . '"
                     style="width:100%;display:block;">
            </div>';
 
        $footerHtml = '
            <div style="margin:0;padding:0;line-height:0;">
                <img src="data:image/' . $footerExt . ';base64,' . $footerB64 . '"
                     style="width:100%;display:block;">
            </div>';
 
        // ── Inisialisasi mPDF ─────────────────────────────────────────────
        $mpdf = new Mpdf([
            'format'        => 'A4',
            'tempDir'       => storage_path('app/mpdf'),
            'margin_top'    => 0,   // ← sesuaikan dengan tinggi header image (mm)
            'margin_bottom' => 0,   // ← sesuaikan dengan tinggi footer image (mm)
            'margin_left'   => 0,
            'margin_right'  => 0,
            // penting
            'margin_header' => 0,
            'margin_footer' => 0,
            // penting
            'setAutoTopMargin'    => 'stretch',
            'setAutoBottomMargin' => 'stretch',
        ]);
        
        $mpdf->splitTableBorderWidth = 1;
 
        $mpdf->SetHTMLHeader($headerHtml);
        $mpdf->SetHTMLFooter($footerHtml);
 
        $mpdf->WriteHTML($html);
 
        // ── Output ────────────────────────────────────────────────────────
        $filename = 'SI_' . ($shipment->Final_SI_No ?? $shipment->SI_No);
        $mpdf->SetTitle($filename);
 
        // 'I' = tampil di browser, 'D' = download, 'F' = simpan ke file
        return response($mpdf->Output($filename . '.pdf', 'S'), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    public function newShipmentInstruction()
    {
        $vss = VesselSchedule::get();
        foreach ($vss as $key => $value) {
            $container_count = VesselScheduleDtl::where("Order_No", $value->Order_No)->count();
            if ($value->Qty1 != $container_count) {
                unset($vss[$key]);
            }
        }
        $masProducts = DB::table('master_product_cns')->get();
        $companies = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        $companiesConsignee = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        $companiesVendor = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        return view('cns.export.si-new', compact('vss', 'companies', 'masProducts'));
    }

    public function getOrderDetail(Request $request)
    {
        $orderNo = $request->input('Order_No');
        $orderDetail = VesselSchedule::where('Order_No', $orderNo)->first();
        $orderDetails = VesselScheduleDtl::where('Order_No', $orderNo)->get();
        if ($orderDetail) {
            return response()->json([
                'Qty' => $orderDetail->Qty,
                'Qty1' => $orderDetail->Qty1,
                'Qty2' => $orderDetail->Qty2,
                'Vessel' => $orderDetail->Vessel,
                'Stuffing' => $orderDetail->Stuffing,
                'ETD' => $orderDetail->ETD,
                'ETA' => $orderDetail->ETA,
                'POL' => $orderDetail->POL,
                'POD' => $orderDetail->POD,
                'orderDetails' => $orderDetails
            ]);
        } else {
            return response()->json(['error' => 'Order not found'], 500);
        }
    }
    
    public function getSINo(Request $request)
    {
        $strtime = strtotime($request->date);
        $months = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
        $romawi = $months[date('n', $strtime) - 1];
        $counter = ShipmentInstruction::where("SI_Date", 'LIKE', date("Y-m", $strtime).'%')->count();
        $SINo = "SI/EXP/" . sprintf('%03d',$counter + 1) . "/" . $romawi . "/" . date("Y", $strtime);

        echo $SINo;
    }
    
    public function newShipmentInstructionSubmit(Request $request)
    {
        // Validate the request data
        $request->validate([
            'Order_No' => 'required|exists:vessel_schedules,Order_No',
            'Container1' => 'required',
        ]);

        // Process the form submission
        $orderNo = $request->input('Order_No');
        $containers = [];
        for ($i = 1; $i <= $request->input('Qty1'); $i++) {
            $container = $request->input("Container$i");
            if ($container) {
                $containers[] = $container;
            }
        }

        $vs = VesselSchedule::where("Order_No", $orderNo)->first();
        $vsd = VesselScheduleDtl::where('Task_ID', $vs->Task_ID)
            ->get()
            ->map(function ($item) {
                return $item->Container . ' (' . $item->Seal . ')';
            })
            ->implode(', ');

        $vs = $vs->toArray();
        $vs['Last_ID'] = $vs['id'];
        $vs['Action_By'] = auth()->user()->username;
        $vs['Action_Date'] = now();
        $vs['Action_Status'] = "CREATE SI";
        $vs['Container'] = $vsd;
        unset($vs['id']);
        VesselScheduleLog::create($vs);

        ShipmentInstruction::create([
            'SI_No' => $request->input('SI_No'),
            'Order_No' => $orderNo,
            'Qty' => $request->input('Qty1') . " x " . $request->input('Qty2'),
            'Qty1' => $request->input('Qty1'),
            'Qty2' => $request->input('Qty2'),
            'SI_Date' => $request->input('SI_Date'),
            'Shipper' => $request->input('Shipper') ?? '',
            'Consignee' => $request->input('Consignee') ?? '',
            'Notify' => $request->input('Notify') ?? '',
            'Destination_Letter' => $request->input('Destination_Letter') ?? '',
            'Delivery_Terms' => $request->input('Delivery_Terms') ?? '',
            'Contract_No' => $request->input('Contract_No') ?? '',
            'Voyage' => $request->input('Voyage') ?? '',
            'ETD' => $request->input('ETD'),
            'ETA' => $request->input('ETA'),
            'Stuffing' => $request->input('Stuffing'),
            'POL' => $request->input('POL') ?? "",
            'POD' => $request->input('POD') ?? "",
            'PEB_Date' => $request->input('PEB_Date'),
            'PEB_No' => $request->input('PEB_No') ?? '',
            'BL_No' => $request->input('BL_No') ?? '',
            'HS_Code' => $request->input('HS_Code'),
            'Product_Name' => $request->input('Product_Name'),
            'Status' => "CREATED",
            'Created_Date' => now(),
            'Created_By' => auth()->user()->username
        ]);

        foreach ($containers as $key => $value) {
            // $values = VesselScheduleDtl::where("ID", $value)->first();
            VesselScheduleDtl::where("ID", $value)->update([
                'SI_No' => $request->SI_No
            ]);
            // $values = json_decode(json_encode($values), true);
            // $values['Last_ID'] = $values['ID'];
            // unset($values['ID']);
            // $values['Action_By'] = auth()->user()->username;
            // $values['Action_Date'] = now();
            // $values['Action_Status'] = "CREATE SI (Detail)";
            // VesselScheduleDtlLog::create($values);
        }

        return response()->json(['status' => 'success', 'message' => 'Shipping Instruction submitted successfully']);
    }
    
    public function generateFinalShipmentInstruction(Request $request)
    {
        $counter = DB::table("shipment_instructions")->where('SI_Date', "LIKE", date("Y-m"). "%")->where('Status', "FINAL")->count();

        $SINo = "FINALSI/EXP/" . sprintf('%03d',$counter + 1) . "/" . $this->getRomawi(date("n")) . "/" . date("Y");
        ShipmentInstruction::where("SI_No", $request->Task)->update([
            'Final_SI_No' => $SINo,
            'Final_SI_Date' => $request->FSI_Date,
            'Status' => "FINAL",
            'Updated_Date' => now(),
            'Updated_By' => auth()->user()->username
        ]);

        // return response()->json(['success' => true, 'message' => 'Shipping Instruction finalized successfully']);
        echo "<script>alert('Done Generate to Final SI')</script>";
    }

    
    function getRomawi($bln)
    {
        switch ($bln) {
            case 1:
                return "I";
                break;
            case 2:
                return "II";
                break;
            case 3:
                return "III";
                break;
            case 4:
                return "IV";
                break;
            case 5:
                return "V";
                break;
            case 6:
                return "VI";
                break;
            case 7:
                return "VII";
                break;
            case 8:
                return "VIII";
                break;
            case 9:
                return "IX";
                break;
            case 10:
                return "X";
                break;
            case 11:
                return "XI";
                break;
            case 12:
                return "XII";
                break;
        }
    }

    public function detailShipmentInstruction(Request $request)
    {
        $si = ShipmentInstruction::where('id', $request->Task)->first();
        $dtls = VesselScheduleDtl::where('SI_No', $si->SI_No)->get();
        $masProducts = DB::table('master_product_cns')->get();
        $containers = VesselScheduleDtl::where('SI_No', $si->SI_No)->get();
        $companies = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        return view('cns.export.si-detail', compact('si', 'dtls', 'masProducts', 'containers', 'companies'));
    }

    public function detailShipmentInstructionSubmit(Request $request)
    {
        $si = ShipmentInstruction::where('id', $request->input('id'))->first();

        $vs = VesselSchedule::where("Order_No", $si->Order_No)->first();
        $vsd = VesselScheduleDtl::where('Task_ID', $vs->Task_ID)
            ->get()
            ->map(function ($item) {
                return $item->Container . ' (' . $item->Seal . ')';
            })
            ->implode(', ');

        $vs = $vs->toArray();
        $vs['Last_ID'] = $vs['id'];
        $vs['Action_By'] = auth()->user()->username;
        $vs['Action_Date'] = now();
        $vs['Action_Status'] = "UPDATE SI";
        $vs['Container'] = $vsd;
        unset($vs['id']);
        VesselScheduleLog::create($vs);

        $si->Shipper = $request->input('Shipper') ?? '';
        $si->Consignee = $request->input('Consignee') ?? '';
        $si->Notify = $request->input('Notify') ?? '';
        $si->Destination_Letter = $request->input('Destination_Letter') ?? '';
        $si->Delivery_Terms = $request->input('Delivery_Terms') ?? '';
        $si->Contract_No = $request->input('Contract_No') ?? '';
        $si->Voyage = $request->input('Voyage') ?? '';
        $si->Delivery_Terms = $request->input('Delivery_Terms') ?? '';
        $si->ETD = $request->input('ETD');
        $si->ETA = $request->input('ETA');
        $si->Stuffing = $request->input('Stuffing');
        $si->POL = $request->input('POL') ?? "";
        $si->POD = $request->input('POD') ?? "";
        $si->PEB_Date = $request->input('PEB_Date');
        $si->PEB_No = $request->input('PEB_No') ?? '';
        $si->BL_No = $request->input('BL_No') ?? '';
        $si->HS_Code = $request->input('HS_Code');
        $si->Product_Name = $request->input('Product_Name');
        $si->save();

        return response()->json(['success' => true, 'message' => 'Shipping Instruction updated successfully']);
    }
}
