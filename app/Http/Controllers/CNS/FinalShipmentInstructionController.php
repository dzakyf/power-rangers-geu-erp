<?php

namespace App\Http\Controllers\CNS;

use App\CommercialInvoice;
use App\Http\Controllers\Controller;
use App\PackingList;
use App\ShipmentInstruction;
use App\VesselSchedule;
use App\VesselScheduleDtl;
use App\VesselScheduleLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;

class FinalShipmentInstructionController extends Controller
{
    public function indexFinalShipmentInstruction()
    {
        return view('cns.export.fsi');
    }

    public function indexFinalShipmentInstructionXml(Request $request)
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $vsdData = [];
        $vsds = VesselScheduleDtl::whereNotNull('SI_No')->get();
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

        $shipments = ShipmentInstruction::whereBetween("Final_SI_Date", [$request->tgl1, $request->tgl2])->where('Status', "FINAL")->get();
        foreach ($shipments as $key => $shipment) {
            echo "<row id=\"$shipment->id\" >";
            echo "<cell>" . ($key + 1) . "</cell>";
            echo "<cell>" . htmlspecialchars($shipment->Final_SI_No) . "</cell>";
            echo "<cell>" . htmlspecialchars($shipment->Final_SI_Date) . "</cell>";
            echo "<cell>" . htmlspecialchars($shipment->SI_No) . "</cell>";
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

    public function printFinalShipmentInstruction(Request $request)
    {
        // $company = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        // foreach ($company as $key => $value) {
        //     $companies[$value->Supplier_ID] = $value;
        // }
        // $shipment = ShipmentInstruction::where('Final_SI_No', $request->Task)->first();
        // $vsds = VesselScheduleDtl::where('SI_No', $shipment->SI_No)->get();
        // return view('cns.export.print.fsi', compact('shipment', 'vsds','companies'));
        
        // ── Ambil data (sesuaikan dengan model Anda) ──────────────────────
        $companies = [];
        $company = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        foreach ($company as $key => $value) {
            $companies[$value->Supplier_ID] = $value;
        }
        $shipment = ShipmentInstruction::where('Final_SI_No', $request->Task)->first();
        $vsds = VesselScheduleDtl::where('SI_No', $shipment->SI_No)->get();
 
        // ── Hitung total ──────────────────────────────────────────────────
        $gross = $vsds->sum('Gross_KG');
        $netto = $vsds->sum('Netto_KG');
        $mt    = round($netto / 1000, 2);
 
        // ── Render HTML dari Blade view ───────────────────────────────────
        $html = view('cns.export.print.fsi', compact(
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
 
        $mpdf->SetHTMLHeader($headerHtml);
        $mpdf->SetHTMLFooter($footerHtml);
 
        $mpdf->WriteHTML($html);
 
        // ── Output ────────────────────────────────────────────────────────
        $filename = 'FSI_' . ($shipment->Final_SI_No ?? $shipment->SI_No);
        $mpdf->SetTitle($filename);
 
        // 'I' = tampil di browser, 'D' = download, 'F' = simpan ke file
        return response($mpdf->Output($filename . '.pdf', 'S'), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    public function newFinalShipmentInstruction()
    {
        $vss = VesselSchedule::get();
        $sis = ShipmentInstruction::where('Status', "CREATED")->get();
        $masProducts = DB::table('master_product_cns')->get();
        $companies = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        return view('cns.export.fsi-new', compact('vss', 'sis', 'companies', 'masProducts'));
    }
    
    
    public function newFinalShipmentInstructionSubmit(Request $request)
    {
        $counter = DB::table("shipment_instructions")->where('SI_Date', "LIKE", date("Y-m"). "%")->where('Status', "FINAL")->count();

        $SINo = "FINALSI/EXP/" . sprintf('%03d',$counter + 1) . "/" . $this->getRomawi(date("n")) . "/" . date("Y");
        ShipmentInstruction::where("SI_No", $request->SI_No)->update([
            'Final_SI_No' => $SINo,
            'Final_SI_Date' => $request->SI_Date,
            'Status' => "FINAL",
            'Updated_Date' => now(),
            'Updated_By' => auth()->user()->username
        ]);

        // return response()->json(['success' => true, 'message' => 'Shipment Instruction finalized successfully']);
        echo "<script>alert('Done Generate to Final SI')</script>";
    }

    public function getSI(Request $request)
    {
        $SINo = $request->input('SI_No');
        $shipment = ShipmentInstruction::where('SI_No', $SINo)->first();
        if ($shipment) {
            $vsd = VesselScheduleDtl::where("SI_No", $SINo)->get();
            $shipment->orderDetails = $vsd;
            unset($shipment->id);
            return response()->json($shipment);
        } else {
            return response()->json(['error' => 'Shipment Instruction not found'], 404);
        }
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

    public function detailFinalShipmentInstruction()
    {
        $vss = VesselSchedule::get();
        $sis = ShipmentInstruction::where('Status', "CREATED")->get();
        $masProducts = DB::table('master_product_cns')->get();
        $companies = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        $shipment = ShipmentInstruction::where('ID', request()->query('Task'))->first();
        if ($shipment) {
            $vsd = VesselScheduleDtl::where("SI_No", $shipment->SI_No)->get();
            $shipment->orderDetails = $vsd;
        }
        return view('cns.export.fsi-detail', compact('vss', 'sis', 'companies', 'masProducts', 'shipment'));
    }

    public function generatePackingList(Request $request)
    {
        $shipment = ShipmentInstruction::where('Final_SI_No', $request->Task)->first();
        if (!$shipment) {
            // return response()->json(['error' => 'Shipment Instruction not found'], 404);
            echo "<script>alert('Shipment Instruction not found');</script>";
            return;
        }

        $ci = PackingList::where('Final_SI_No', $request->Task)->first();
        if ($ci) {
            // return response()->json(['error' => 'Commercial Invoice already generated for this Shipment Instruction'], 400);
            echo "<script>alert('Commercial Invoice already generated for this Shipment Instruction');</script>";
            return;
        }

        // $shipment->Status = "PACKING_LIST";
        // $shipment->save();

        $vs = VesselSchedule::where("Order_No", $shipment->Order_No)->first();
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
        $vs['Action_Status'] = "CREATE PL";
        $vs['Container'] = $vsd;
        unset($vs['id']);
        VesselScheduleLog::create($vs);

        $counter = PackingList::where('PL_Date', "LIKE", date("Y-m") . "%")->count();
        
        $shipment = json_decode(json_encode($shipment), true);
        $shipment["Packing_List_No"] = "PL-GEU/EXP-BC/" . sprintf('%03d',$counter + 1) . "/" . $this->getRomawi(date("n")) . "/" . date("Y");
        $shipment['PL_Date'] = $request->PL_Date;
        $shipment['Created_By'] = auth()->user()->username;
        $shipment['Created_Date'] = now();
        $shipment['Updated_By'] = null;
        $shipment['Updated_Date'] = null;
        $shipment['Status'] = "CREATED";
        PackingList::create($shipment);

        echo "<script>alert('Packing List generated successfully');</script>";

        // return response()->json(['message' => 'Packing List generated successfully']);
    }

    public function generateCommercialInvoice(Request $request)
    {
        $shipment = ShipmentInstruction::where('Final_SI_No', $request->Task)->first();
        if (!$shipment) {
            // return response()->json(['error' => 'Shipment Instruction not found'], 404);
            echo "<script>alert('Shipment Instruction not found');</script>";
            return;
        }

        $ci = CommercialInvoice::where('SI_No', $request->Task)->first();
        if ($ci) {
            // return response()->json(['error' => 'Commercial Invoice already generated for this Shipment Instruction'], 400);
            echo "<script>alert('Commercial Invoice already generated for this Shipment Instruction');</script>";
            return;
        }

        $vs = VesselSchedule::where("Order_No", $shipment->Order_No)->first();
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
        $vs['Action_Status'] = "CREATE CI";
        $vs['Container'] = $vsd;
        unset($vs['id']);
        VesselScheduleLog::create($vs);

        $getVSD = VesselScheduleDtl::where("Order_No", $vs['Order_No'])->where("SI_No", $shipment->SI_No)->sum('Netto_MT');
    
        $counter = CommercialInvoice::where('Inv_Date', "LIKE", date("Y-m") . "%")->count();
        
        // $ci = json_decode(json_encode($shipment), true);
        $ci = [];
        $ci['SI_No'] = $shipment->Final_SI_No;
        $ci["Inv_No"] = sprintf('%03d',$counter + 1) . "/INV-GEU/" . $this->getRomawi(date("n")) . "/" . date("Y");
        $ci['Inv_Date'] = date("Y-m-d");
        $ci['Shipper'] = $shipment->Shipper ?? '';
        $ci['Consignee'] = $shipment->Consignee ?? '';
        $ci['Description'] = "";
        $ci['Qty'] = $getVSD;
        $ci['Amount'] = 0;
        $ci['Advance_Payment'] = 0;
        $ci['Other'] = 0;
        $ci['Total'] = 0;
        $ci["Status"] = "CREATED";
        $ci['Created_By'] = auth()->user()->username;
        $ci['Created_Date'] = now();
        CommercialInvoice::insert($ci);

        echo "<script>alert('Commercial Invoice generated successfully');</script>";

        // return response()->json(['message' => 'Commercial Invoice generated successfully']);
    }
}
