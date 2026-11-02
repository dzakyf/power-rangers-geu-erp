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

        $vsds = VesselScheduleDtl::active()->whereNotNull('SI_No')->get();
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

        $shipments = ShipmentInstruction::whereBetween('SI_Date', [$request->tgl1, $request->tgl2])->whereIn('Status', ['DRAFT', 'CREATED'])->get();
        foreach ($shipments as $key => $shipment) {
            echo "<row id=\"$shipment->id\" >";
            echo "<cell>" . ($key + 1) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $shipment->Order_No) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $shipment->SI_No) . "</cell>";
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
            echo "<cell>" . htmlspecialchars((string) $shipment->Status) . "</cell>";
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
        $vsds = VesselScheduleDtl::active()->where('SI_No', $request->Task)->get();
 
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
        $contracts = $this->activeSiContracts();
        $masProducts = DB::table('master_product_cns')->get();
        $companies = DB::table('supplier')
            ->where("Location", auth()->user()->Location)
            ->orderBy('Company_Name')
            ->get();

        $counter = ShipmentInstruction::where("SI_Date", 'LIKE', date("Y-m") . '%')->count();
        $SINo = "SI/EXP/" . sprintf('%03d', $counter + 1) . "/" . $this->getRomawi((int) date("n")) . "/" . date("Y");

        return view('cns.export.si-new', compact('contracts', 'companies', 'masProducts', 'SINo'));
    }

    public function getOrderDetail(Request $request)
    {
        $orderNo = $request->input('Order_No');
        $contractId = $request->input('Contract_ID');

        $query = VesselSchedule::where('Order_No', $orderNo);

        if ($contractId) {
            $query->where('Contract_ID', $contractId);
        }

        $orderDetail = $query->first();

        if (!$orderDetail) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        $orderDetails = VesselScheduleDtl::active()->where('Order_No', $orderNo)->get();

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
            'Contract_ID' => $orderDetail->Contract_ID,
            'Contract_No' => $orderDetail->Contract_No,
            'orderDetails' => $orderDetails,
        ]);
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
        $data = $request->validate([
            'Contract_ID' => 'required|integer',
            'SI_No' => 'required|string|max:150|unique:shipment_instructions,SI_No',
            'SI_Date' => 'required|date',
            'Contract_No' => 'nullable|string|max:150',
            'Delivery_Terms' => 'nullable|string|max:255',
            'Consignee' => 'nullable|string|max:50',
            'PEB_Date' => 'nullable|date',
        ]);

        $contract = DB::table('cns_buyer_contracts')
            ->where('ID', $data['Contract_ID'])
            ->where('Location', auth()->user()->Location)
            ->where('Status', 'ACTIVE_IN_PROGRESS')
            ->first();

        if (!$contract) {
            return response()->json(['message' => 'Kontrak tidak ditemukan atau belum aktif.'], 422);
        }

        $consignee = (string) $request->input('Consignee', '');

        if ($consignee === '' && $contract->Supplier_ID) {
            $consignee = (string) DB::table('supplier')
                ->where('ID', $contract->Supplier_ID)
                ->value('Supplier_ID');
        }

        ShipmentInstruction::create([
            'SI_No' => $data['SI_No'],
            'Order_No' => null,
            'Qty' => '',
            'Qty1' => '',
            'Qty2' => '',
            'SI_Date' => $data['SI_Date'],
            'Contract_ID' => $contract->ID,
            'Contract_No' => $request->input('Contract_No') ?: $contract->Contract_No,
            'Delivery_Terms' => $request->input('Delivery_Terms') ?: $contract->Incoterms,
            'Consignee' => $consignee,
            'Shipper' => (string) $request->input('Shipper', ''),
            'Notify' => (string) $request->input('Notify', ''),
            'Destination_Letter' => (string) $request->input('Destination_Letter', ''),
            'Shipped_By' => '',
            'Voyage' => '',
            'ETD' => null,
            'ETA' => null,
            'Stuffing' => null,
            'PEB_No' => (string) $request->input('PEB_No', ''),
            'PEB_Date' => $request->filled('PEB_Date') ? $request->input('PEB_Date') : null,
            'BL_No' => (string) $request->input('BL_No', ''),
            'POL' => '',
            'POD' => '',
            'HS_Code' => (string) $request->input('HS_Code', ''),
            'Product_Name' => (string) $request->input('Product_Name', ''),
            'Status' => 'DRAFT',
            'Created_Date' => now(),
            'Created_By' => auth()->user()->username,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Draft Shipping Instruction created successfully']);
    }

    public function generateFinalShipmentInstruction(Request $request)
    {
        $si = ShipmentInstruction::where('SI_No', $request->Task)->where('Status', 'CREATED')->first();

        if (!$si) {
            echo "<script>alert('SI harus berstatus CREATED sebelum finalisasi. Lengkapi container terlebih dahulu.')</script>";
            return;
        }

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

        if (!$si) {
            abort(404);
        }

        $contract = $si->Contract_ID
            ? DB::table('cns_buyer_contracts')->where('ID', $si->Contract_ID)->first()
            : null;

        $orders = collect();

        if ($si->Contract_ID) {
            $usedOrderNos = ShipmentInstruction::whereNotNull('Order_No')
                ->where('id', '!=', $si->id)
                ->whereIn('Status', ['DRAFT', 'CREATED'])
                ->pluck('Order_No')
                ->all();

            $orders = VesselSchedule::where('Contract_ID', $si->Contract_ID)
                ->where('Shipping_Method', 'CONTAINER')
                ->whereNotIn('Order_No', $usedOrderNos)
                ->orderBy('Order_No')
                ->get();
        }

        if ($si->Order_No) {
            $currentOrder = VesselSchedule::where('Order_No', $si->Order_No)->first();

            if ($currentOrder && !$orders->contains('Order_No', $si->Order_No)) {
                $orders->prepend($currentOrder);
            }
        }

        $containers = $si->Order_No
            ? VesselScheduleDtl::active()->where('Order_No', $si->Order_No)->get()
            : collect();

        $assigned = VesselScheduleDtl::active()->where('SI_No', $si->SI_No)->pluck('ID')->all();
        $masProducts = DB::table('master_product_cns')->get();
        $companies = DB::table('supplier')
            ->where("Location", auth()->user()->Location)
            ->orderBy('Company_Name')
            ->get();

        return view('cns.export.si-detail', compact('si', 'orders', 'containers', 'assigned', 'masProducts', 'companies', 'contract'));
    }

    public function detailShipmentInstructionSubmit(Request $request)
    {
        $si = ShipmentInstruction::where('id', $request->input('id'))->first();

        if (!$si) {
            abort(404);
        }

        $request->validate([
            'SI_Date' => 'required|date',
            'PEB_Date' => 'nullable|date',
        ]);

        $si->SI_Date = $request->input('SI_Date');
        $si->Shipper = (string) $request->input('Shipper', '');
        $si->Consignee = (string) $request->input('Consignee', '');
        $si->Notify = (string) $request->input('Notify', '');
        $si->Destination_Letter = (string) $request->input('Destination_Letter', '');
        $si->Delivery_Terms = (string) $request->input('Delivery_Terms', '');
        $si->Contract_No = (string) $request->input('Contract_No', '');
        $si->PEB_No = (string) $request->input('PEB_No', '');
        $si->PEB_Date = $request->filled('PEB_Date') ? $request->input('PEB_Date') : null;
        $si->BL_No = (string) $request->input('BL_No', '');
        $si->HS_Code = (string) $request->input('HS_Code', '');
        $si->Product_Name = (string) $request->input('Product_Name', '');
        $si->Updated_Date = now();
        $si->Updated_By = auth()->user()->username;

        $orderNo = $request->input('Order_No');
        $order = null;

        if ($orderNo) {
            $orderQuery = VesselSchedule::where('Order_No', $orderNo);

            if ($si->Contract_ID) {
                $orderQuery->where('Contract_ID', $si->Contract_ID);
            }

            $order = $orderQuery->first();

            if (!$order) {
                return response()->json(['message' => 'Order tidak ditemukan pada kontrak ini.'], 422);
            }

            $usedByOtherSi = ShipmentInstruction::where('Order_No', $orderNo)
                ->where('id', '!=', $si->id)
                ->whereIn('Status', ['DRAFT', 'CREATED'])
                ->exists();

            if ($usedByOtherSi) {
                return response()->json(['message' => 'Order ini sudah dipakai oleh Shipping Instruction lain.'], 422);
            }

            if (!$si->Contract_ID && $order->Contract_ID) {
                $si->Contract_ID = $order->Contract_ID;
            }
        }

        $qty1 = (string) $request->input('Qty1', '');
        $qty2 = (string) $request->input('Qty2', '');

        if ($order) {
            $qty1 = $qty1 !== '' ? $qty1 : (string) $order->Qty1;
            $qty2 = $qty2 !== '' ? $qty2 : (string) $order->Qty2;
        }

        $selected = [];

        for ($i = 1; $i <= (int) $qty1; $i++) {
            $container = $request->input("Container$i");

            if ($container) {
                $selected[] = $container;
            }
        }

        $selected = array_values(array_unique($selected));

        if ($order) {
            $validIds = VesselScheduleDtl::active()->where('Order_No', $orderNo)
                ->whereIn('ID', $selected)
                ->pluck('ID')
                ->all();

            if (count($validIds) !== count($selected)) {
                return response()->json(['message' => 'Container tidak sesuai dengan order yang dipilih.'], 422);
            }

            $si->Order_No = $orderNo;
            $si->Qty1 = $qty1;
            $si->Qty2 = $qty2;
            $si->Qty = $qty1 . ' x ' . $qty2;
            $si->Voyage = (string) $request->input('Voyage', $order->Vessel);
            $si->ETD = $request->filled('ETD') ? $request->input('ETD') : $order->ETD;
            $si->ETA = $request->filled('ETA') ? $request->input('ETA') : $order->ETA;
            $si->Stuffing = $request->filled('Stuffing') ? $request->input('Stuffing') : $order->Stuffing;
            $si->POL = (string) $request->input('POL', $order->POL);
            $si->POD = (string) $request->input('POD', $order->POD);

            if ($si->SI_No) {
                VesselScheduleDtl::active()->where('SI_No', $si->SI_No)->update(['SI_No' => null]);
            }

            if (!empty($selected)) {
                VesselScheduleDtl::active()->whereIn('ID', $selected)->update(['SI_No' => $si->SI_No]);
            }

            $si->Status = !empty($selected) ? 'CREATED' : 'DRAFT';

            if (!empty($selected)) {
                $this->writeSiScheduleLog($order, 'UPDATE SI', $selected);
            }
        }

        $si->save();

        return response()->json(['success' => true, 'message' => 'Shipping Instruction updated successfully', 'status' => $si->Status]);
    }

    private function activeSiContracts()
    {
        return DB::table('cns_buyer_contracts as c')
            ->leftJoin('supplier as s', 's.ID', '=', 'c.Supplier_ID')
            ->where('c.Location', auth()->user()->Location)
            ->where('c.Status', 'ACTIVE_IN_PROGRESS')
            ->orderBy('c.Contract_No')
            ->select([
                'c.ID',
                'c.Contract_No',
                'c.Incoterms',
                'c.Supplier_ID',
                'c.Buyer_Name',
                'c.Container_Count',
                'c.Container_Size',
                DB::raw('s.Supplier_ID as Consignee_Code'),
                DB::raw('s.Company_Name as Consignee_Name'),
            ])
            ->get();
    }

    private function writeSiScheduleLog(VesselSchedule $order, string $status, array $containerIds)
    {
        if (empty($containerIds)) {
            return;
        }

        $containers = VesselScheduleDtl::active()->whereIn('ID', $containerIds)
            ->get()
            ->map(function ($item) {
                return $item->Container . ' (' . $item->Seal . ')';
            })
            ->implode(', ');

        $log = $order->toArray();
        $log['Last_ID'] = $log['id'];
        unset($log['id']);
        $log['Action_By'] = auth()->user()->username;
        $log['Action_Date'] = now();
        $log['Action_Status'] = $status;
        $log['Container'] = $containers;

        VesselScheduleLog::create($log);
    }
}
