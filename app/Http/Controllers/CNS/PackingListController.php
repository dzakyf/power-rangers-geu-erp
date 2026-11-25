<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use App\PackingList;
use App\ShippingInstruction;
use App\Support\DhtmlxGridPagination;
use App\User;
use App\VesselSchedule;
use App\VesselScheduleDtl;
use App\VesselScheduleLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;

class PackingListController extends Controller
{
    public function indexPackingList()
    {
        $approvers = cns_eligible_approvers();

        return view('cns.export.pl', compact('approvers'));
    }

    public function indexPackingListXml(Request $request)
    {
        $query = PackingList::whereBetween('PL_Date', [$request->tgl1, $request->tgl2])
            ->whereIn('Status', ["CREATED", "APPROVED"]);

        $colMap = [
            1 => 'Packing_List_No',
            2 => 'Final_SI_No',
            3 => 'PL_Date',
            10 => 'Status',
        ];

        $paginated = DhtmlxGridPagination::paginateQuery($query, $request, [
            'col_map' => $colMap,
            'default_page_size' => 25,
        ]);

        $shipments = $paginated['rows'];
        $siNos = $shipments->where('Is_Final', '!=', 1)->pluck('SI_No')->filter()->unique()->values()->all();
        $vsds = count($siNos)
            ? VesselScheduleDtl::active()->whereIn('SI_No', $siNos)->get()
            : collect();

        $vsdData = [];
        foreach ($vsds as $vsd) {
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

        $bcDataCache = [];
        $bodyXml = '';
        $nomor = $paginated['posStart'] + 1;

        foreach ($shipments as $shipment) {
            $refLabel = $shipment->Final_SI_No;
            $rowVsd = $vsdData[$shipment->SI_No] ?? null;

            if (!empty($shipment->Is_Final) && !empty($shipment->BC_No)) {
                if (!isset($bcDataCache[$shipment->BC_No])) {
                    $resolved = $this->resolveStuffingDataByBc($shipment->BC_No);
                    $bcContainers = $resolved['containers'];
                    $bcDataCache[$shipment->BC_No] = [
                        'list' => $bcContainers->map(function ($item) {
                            return $item->Container . ' (' . $item->Seal . ')';
                        })->all(),
                        'tare' => (float) $bcContainers->sum('Tare'),
                        'netto_kg' => (float) ($shipment->Total_Netto_KG ?? $resolved['totals']['netto_kg']),
                        'netto_mt' => round((float) ($shipment->Total_Netto_KG ?? $resolved['totals']['netto_kg']) / 1000, 2),
                        'gross_kg' => (float) ($shipment->Total_Gross_KG ?? $resolved['totals']['gross_kg']),
                        'gross_mt' => round((float) ($shipment->Total_Gross_KG ?? $resolved['totals']['gross_kg']) / 1000, 2),
                    ];
                }
                $rowVsd = $bcDataCache[$shipment->BC_No];
                $refLabel = $shipment->Final_SI_No ?: ('BC: ' . $shipment->BC_No);
            }

            $bodyXml .= "<row id=\"$shipment->id\" >";
            $bodyXml .= "<cell>" . $nomor++ . "</cell>";
            $bodyXml .= "<cell>" . htmlspecialchars($shipment->Packing_List_No) . "</cell>";
            $bodyXml .= "<cell>" . htmlspecialchars((string) $refLabel) . "</cell>";
            $bodyXml .= "<cell>" . htmlspecialchars(date("d F Y", strtotime($shipment->PL_Date))) . "</cell>";
            if ($rowVsd) {
                $bodyXml .= "<cell>" . htmlspecialchars(implode(', ', $rowVsd['list'])) . "</cell>";
                $bodyXml .= "<cell>" . htmlspecialchars($rowVsd['tare']) . "</cell>";
                $bodyXml .= "<cell>" . htmlspecialchars($rowVsd['netto_kg']) . "</cell>";
                $bodyXml .= "<cell>" . htmlspecialchars($rowVsd['netto_mt']) . "</cell>";
                $bodyXml .= "<cell>" . htmlspecialchars($rowVsd['gross_kg']) . "</cell>";
                $bodyXml .= "<cell>" . htmlspecialchars($rowVsd['gross_mt']) . "</cell>";
            } else {
                $bodyXml .= "<cell></cell>";
                $bodyXml .= "<cell></cell>";
                $bodyXml .= "<cell></cell>";
                $bodyXml .= "<cell></cell>";
                $bodyXml .= "<cell></cell>";
                $bodyXml .= "<cell></cell>";
            }
            $bodyXml .= "<cell>" . htmlspecialchars($shipment->Status ?? 'CREATED') . "</cell>";
            $bodyXml .= "</row>\n";
        }

        return DhtmlxGridPagination::response($paginated['total'], $paginated['posStart'], $bodyXml);
    }

    public function printPackingList(Request $request)
    {
        $shipment = PackingList::where('Packing_List_No', $request->Task)
            ->orWhere('id', $request->Task)
            ->first();
        if (!$shipment) {
            abort(404, 'Packing List tidak ditemukan');
        }

        // ── Ambil data (sesuaikan dengan model Anda) ──────────────────────
        $companies = [];
        $company = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        foreach ($company as $key => $value) {
            $companies[$value->Supplier_ID] = $value;
        }

        // Fallback jika ada supplier yang tidak cocok dengan Location user
        foreach (['Shipper', 'Consignee', 'Notify', 'Destination_Port'] as $partyField) {
            $suppId = $shipment->{$partyField};
            if ($suppId && !isset($companies[$suppId])) {
                $supp = DB::table('supplier')
                    ->where('Supplier_ID', $suppId)
                    ->orWhere('ID', $suppId)
                    ->first();
                if ($supp) {
                    $companies[$suppId] = $supp;
                }
            }
        }

        // Resolusi perusahaan untuk Header, Footer, Logo (prioritaskan Shipper, fallback ke Master Company)
        $companyProfile = null;
        if (!empty($shipment->Shipper)) {
            $companyProfile = $companies[$shipment->Shipper] ?? null;
            if (!$companyProfile) {
                $companyProfile = DB::table('supplier')
                    ->where('Supplier_ID', $shipment->Shipper)
                    ->orWhere('ID', $shipment->Shipper)
                    ->first();
            }
        }

        if (!$companyProfile) {
            $companyProfile = DB::table('supplier')
                ->where('Is_Company', 1)
                ->when(auth()->check() && !empty(auth()->user()->Location), function ($q) {
                    $q->where('Location', auth()->user()->Location);
                })
                ->first()
                ?? DB::table('supplier')->where('Is_Company', 1)->first()
                ?? DB::table('supplier')->first();
        }

        // ── Header, Footer & Logo Data URI ────────────────────────────────
        $headerHtml = '';
        $footerHtml = '';
        $logoDataUri = null;

        if ($companyProfile) {
            $letterheadPath = $companyProfile->Letterhead ?? $companyProfile->LetterHead ?? null;
            $headerDataUri = $this->imageToDataUri($letterheadPath);
            if ($headerDataUri) {
                $headerHtml = '
                    <div style="margin:0;padding:0;line-height:0;">
                        <img src="' . $headerDataUri . '"
                             style="width:100%;display:block;">
                    </div>';
            }

            $footerPath = $companyProfile->Footer ?? null;
            $footerDataUri = $this->imageToDataUri($footerPath);
            if ($footerDataUri) {
                $footerHtml = '
                    <div style="margin:0;padding:0;line-height:0;">
                        <img src="' . $footerDataUri . '"
                             style="width:100%;display:block;">
                    </div>';
            }

            $logoPath = $companyProfile->Logo ?? null;
            $logoDataUri = $this->imageToDataUri($logoPath);
        }

        if (!empty($shipment->Is_Final) && !empty($shipment->BC_No)) {
            $bcData = $this->resolveStuffingDataByBc($shipment->BC_No);
            $vsds = $bcData['containers'];
            $gross = (float) ($shipment->Total_Gross_KG ?? $bcData['totals']['gross_kg']);
            $netto = (float) ($shipment->Total_Netto_KG ?? $bcData['totals']['netto_kg']);
        } else {
            $vsds = VesselScheduleDtl::active()->where('SI_No', $shipment->SI_No)->get();
            $gross = (float) $vsds->sum('Gross_KG');
            $netto = (float) $vsds->sum('Netto_KG');
        }
        $mt = round($netto / 1000, 2);

        $approver = $shipment->approved_by
            ? User::find($shipment->approved_by)
            : null;

        $approverSignature = null;
        if ($approver) {
            $sigPath = user_signature_path($approver->id);
            if ($sigPath && file_exists($sigPath)) {
                $approverSignature = $this->imageToDataUri($sigPath);
            }
        }

        // ── Render HTML dari Blade view ───────────────────────────────────
        $html = view('cns.export.print.pl', compact(
            'shipment', 'vsds', 'companies', 'gross', 'netto', 'mt', 'logoDataUri', 'approver', 'approverSignature'
        ))->render();

        // ── Inisialisasi mPDF ─────────────────────────────────────────────
        $tempDir = storage_path('app/mpdf');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $mpdf = new Mpdf([
            'format'              => 'A4',
            'tempDir'             => $tempDir,
            'margin_top'          => $headerHtml !== '' ? 0 : 10,
            'margin_bottom'       => $footerHtml !== '' ? 0 : 10,
            'margin_left'         => 0,
            'margin_right'        => 0,
            'margin_header'       => 0,
            'margin_footer'       => 0,
            'setAutoTopMargin'    => $headerHtml !== '' ? 'stretch' : false,
            'setAutoBottomMargin' => $footerHtml !== '' ? 'stretch' : false,
        ]);

        $mpdf->splitTableBorderWidth = 1;

        // if ($headerHtml !== '') {
        //     $mpdf->SetHTMLHeader($headerHtml);
        // }

        // if ($footerHtml !== '') {
        //     $mpdf->SetHTMLFooter($footerHtml);
        // }
 
        $mpdf->WriteHTML($html);
 
        // ── Output ────────────────────────────────────────────────────────
        $filename = 'PL_' . $shipment->Packing_List_No;
        $mpdf->SetTitle($filename);
 
        // 'I' = tampil di browser, 'D' = download, 'F' = simpan ke file
        return response($mpdf->Output($filename . '.pdf', 'S'), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
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

    public function newPackingList()
    {
        
        $counter = PackingList::where('PL_Date', "LIKE", date("Y-m") . "%")->count();

        $plno = "PL-GEU/EXP-BC/" . sprintf('%03d',$counter + 1) . "/" . $this->getRomawi(date("n")) . "/" . date("Y");

        $fsis = ShippingInstruction::where("Status", "FINAL")->where("Final_SI_No", '!=', null)->get();

        $companies = DB::table('supplier')->where("Status", "NORMAL")->where("Location", auth()->user()->Location)->get();

        $masProducts = DB::table('master_product_cns')->get();

        $users = User::where('Status', 'ACTIVE')->orderBy('Name')->get();

        return view('cns.export.pl-new', compact('plno','fsis', 'companies', 'masProducts', 'users'));
    }

    public function newFinalPackingList(Request $request)
    {
        $counter = PackingList::where('PL_Date', "LIKE", date("Y-m") . "%")->count();
        $plno = "PL-GEU/EXP-BC/" . sprintf('%03d', $counter + 1) . "/" . $this->getRomawi(date("n")) . "/" . date("Y");

        $bcNumbers = VesselSchedule::selectRaw("DISTINCT COALESCE(NULLIF(BC_No, ''), NULLIF(Booking_No, ''), Order_No) as bc_no")
            ->whereRaw("COALESCE(NULLIF(BC_No, ''), NULLIF(Booking_No, ''), Order_No) IS NOT NULL")
            ->whereRaw("COALESCE(NULLIF(BC_No, ''), NULLIF(Booking_No, ''), Order_No) != ''")
            ->orderBy('bc_no')
            ->pluck('bc_no');

        $selectedBcNo = (string) $request->query('bc_no', '');
        if ($selectedBcNo === '' && $request->filled('vs_id')) {
            $vs = VesselSchedule::find($request->query('vs_id'));
            if ($vs) {
                $selectedBcNo = (string) ($vs->BC_No ?: ($vs->Booking_No ?: $vs->Order_No));
            }
        }

        $companies = DB::table('supplier')->where("Status", "NORMAL")->where("Location", auth()->user()->Location)->get();
        $masProducts = DB::table('master_product_cns')->get();
        $users = User::where('Status', 'ACTIVE')->orderBy('Name')->get();

        return view('cns.export.pl-final-new', compact('plno', 'bcNumbers', 'selectedBcNo', 'companies', 'masProducts', 'users'));
    }

    public function getStuffingByBc(Request $request)
    {
        $bcNo = trim((string) $request->input('bc_no', ''));
        if ($bcNo === '') {
            return response()->json(['error' => 'Nomor BC wajib dipilih'], 422);
        }

        $data = $this->resolveStuffingDataByBc($bcNo);
        if ($data['schedules']->isEmpty()) {
            return response()->json(['error' => 'Data Stuffing dengan Nomor BC tersebut tidak ditemukan'], 404);
        }

        return response()->json([
            'bc_no' => $bcNo,
            'header' => $data['header'],
            'containers' => $data['containers']->values(),
            'totals' => $data['totals'],
            'description' => $data['description'],
        ]);
    }

    public function newPackingListSubmit(Request $request)
    {
        $isFinalByBc = $request->boolean('Is_Final') && $request->filled('BC_No');

        if ($isFinalByBc) {
            $bcNo = trim((string) $request->input('BC_No'));
            $bcData = $this->resolveStuffingDataByBc($bcNo);
            if ($bcData['schedules']->isEmpty()) {
                return $request->expectsJson()
                    ? response()->json(['error' => 'Data Stuffing dengan Nomor BC tersebut tidak ditemukan'], 404)
                    : response('error', 404);
            }

            $firstSchedule = $bcData['schedules']->first();
            $si = $bcData['si'];

            $pl = new PackingList();
            $pl->Packing_List_No = $request->input('Packing_List_No');
            $pl->BC_No = $bcNo;
            $pl->Is_Final = 1;
            $pl->Final_SI_No = $request->input('Final_SI_No') ?: ($si->Final_SI_No ?? null);
            $pl->SI_No = $si->SI_No ?? ($firstSchedule->SI_No ?? '');
            $pl->Order_No = $request->input('Order_No') ?: ($firstSchedule->Order_No ?? '');
            $pl->SI_Date = $si->SI_Date ?? ($request->input('PL_Date') ?: date('Y-m-d'));
            $pl->Final_SI_Date = $si->Final_SI_Date ?? null;
            $pl->Qty = trim(($request->input('Qty1') ?? '') . ' x ' . ($request->input('Qty2') ?? ''));
            $pl->Qty1 = $request->input('Qty1') ?? (string) $bcData['totals']['container_count'];
            $pl->Qty2 = $request->input('Qty2') ?? '20 FT';
            $pl->PL_Date = $request->input('PL_Date') ?: date('Y-m-d');
            $pl->Shipper = $request->input('Shipper') ?? '';
            $pl->Consignee = $request->input('Consignee') ?? '';
            $pl->Notify = $request->input('Notify') ?? '';
            $pl->Destination_Port = $request->input('Destination_Port') ?? '';
            $pl->Delivery_Terms = $request->input('Delivery_Terms') ?? '';
            $pl->Contract_No = $request->input('Contract_No') ?? ($firstSchedule->Contract_No ?? '');
            $pl->Voyage = $request->input('Voyage') ?? ($firstSchedule->Vessel ?? '');
            $pl->ETD = $request->input('ETD') ?: ($firstSchedule->ETD ?: date('Y-m-d'));
            $pl->ETA = $request->input('ETA') ?: ($firstSchedule->ETA ?: date('Y-m-d'));
            $pl->Stuffing = $request->input('Stuffing') ?: ($firstSchedule->Stuffing ?: date('Y-m-d'));
            $pl->POL = $request->input('POL') ?? ($firstSchedule->POL ?? '');
            $pl->POD = $request->input('POD') ?? ($firstSchedule->POD ?? '');
            $pl->PEB_Date = $request->input('PEB_Date') ?: ($si->PEB_Date ?? date('Y-m-d'));
            $pl->PEB_No = $request->input('PEB_No') ?? '';
            $pl->BL_No = $request->input('BL_No') ?? '';
            $pl->HS_Code = $request->input('HS_Code') ?? '';
            $pl->Product_Name = $request->input('Product_Name') ?? '';
            $pl->Remarks = $request->input('Remarks');
            $pl->Description = $request->input('Description') ?: $bcData['description'];
            $pl->Total_Netto_KG = $request->filled('Total_Netto_KG')
                ? (float) $request->input('Total_Netto_KG')
                : $bcData['totals']['netto_kg'];
            $pl->Total_Gross_KG = $request->filled('Total_Gross_KG')
                ? (float) $request->input('Total_Gross_KG')
                : $bcData['totals']['gross_kg'];
            $pl->approved_by = $request->input('approved_by');
            $pl->Status = "CREATED";
            $pl->Created_Date = now();
            $pl->Created_By = auth()->user()->username;
            $pl->save();

            $vs = VesselSchedule::where("id", $firstSchedule->id)->first();
            if ($vs) {
                $vsd = $bcData['containers']->map(function ($item) {
                    return $item->Container . ' (' . $item->Seal . ')';
                })->implode(', ');

                $vsLog = json_decode(json_encode($vs), true);
                $vsLog['Last_ID'] = $vsLog['id'];
                unset($vsLog['id']);
                $vsLog['Container'] = $vsd;
                $vsLog['Action_By'] = auth()->user()->username;
                $vsLog['Action_Date'] = now();
                $vsLog['Action_Status'] = "CREATE FINAL PACKING LIST WITH NO: " . $request->input('Packing_List_No');
                VesselScheduleLog::create($vsLog);
            }

            return response("success");
        }

        $shipment = ShippingInstruction::where("Final_SI_No", $request->Final_SI_No)->first();

        if (!$shipment) {
            echo "error";
            exit;
        }

        $pl = new PackingList();
        $pl->Packing_List_No = $request->input('Packing_List_No');
        $pl->Final_SI_No = $request->input('Final_SI_No');
        $pl->SI_No = $shipment->SI_No;
        $pl->Order_No = $shipment->Order_No;
        $pl->SI_Date = $shipment->SI_Date;
        $pl->Final_SI_Date = $shipment->Final_SI_Date;
        $pl->Qty = $request->input('Qty1') . " x " . $request->input('Qty2');
        $pl->Qty1 = $request->input('Qty1');
        $pl->Qty2 = $request->input('Qty2');
        $pl->PL_Date = $request->input('PL_Date');
        $pl->Shipper = $request->input('Shipper') ?? '';
        $pl->Consignee = $request->input('Consignee') ?? '';
        $pl->Notify = $request->input('Notify') ?? '';
        $pl->Destination_Port = $request->input('Destination_Port') ?? '';
        $pl->Delivery_Terms = $request->input('Delivery_Terms') ?? '';
        $pl->Contract_No = $request->input('Contract_No') ?? '';
        $pl->Voyage = $request->input('Voyage') ?? '';
        $pl->ETD = $request->input('ETD');
        $pl->ETA = $request->input('ETA');
        $pl->Stuffing = $request->input('Stuffing');
        $pl->POL = $request->input('POL') ?? "";
        $pl->POD = $request->input('POD') ?? "";
        $pl->PEB_Date = $request->input('PEB_Date');
        $pl->PEB_No = $request->input('PEB_No') ?? '';
        $pl->BL_No = $request->input('BL_No') ?? '';
        $pl->HS_Code = $request->input('HS_Code');
        $pl->Product_Name = $request->input('Product_Name');
        $pl->Remarks = $request->input('Remarks');
        $pl->approved_by = $request->input('approved_by');
        $pl->Status = "CREATED";
        $pl->Created_Date = now();
        $pl->Created_By = auth()->user()->username;
        $pl->save();

        $vs = VesselSchedule::where("Order_No", $shipment->Order_No)->first();
        $vsd = VesselScheduleDtl::active()->where('Task_ID', $vs->Task_ID)
            ->get()
            ->map(function ($item) {
                return $item->Container . ' (' . $item->Seal . ')';
            })
            ->implode(', ');
            
        $vs = json_decode(json_encode($vs), true);
        $vs['Last_ID'] = $vs['id'];
        unset($vs['id']);
        $vs['Container'] = $vsd;

        $vs['Action_By'] = auth()->user()->username;
        $vs['Action_Date'] = now();
        $vs['Action_Status'] = "CREATE PACKING LIST WITH NO: " . $request->input('Packing_List_No');

        VesselScheduleLog::create($vs);

        echo "success";
    }

    public function getSI(Request $request)
    {
        $SINo = $request->input('SI_No');
        $shipment = ShippingInstruction::where('Final_SI_No', $SINo)->first();
        if ($shipment) {
            $shipment->orderDetails = VesselScheduleDtl::active()->where("SI_No", $shipment->SI_No)->get();
            return response()->json($shipment);
        } else {
            return response()->json(['error' => 'Shipping Instructions not found'], 404);
        }
    }

    public function detailPackingList(Request $request)
    {
        $counter = PackingList::where('PL_Date', "LIKE", date("Y-m") . "%")->count();

        $plno = "PL-GEU/EXP-BC/" . sprintf('%03d',$counter + 1) . "/" . $this->getRomawi(date("n")) . "/" . date("Y");

        $fsis = ShippingInstruction::where("Status", "FINAL")->where("Final_SI_No", '!=', null)->get();

        $companies = DB::table('supplier')->where("Status", "NORMAL")->where("Location", auth()->user()->Location)->get();

        $masProducts = DB::table('master_product_cns')->get();

        $pl = PackingList::where("ID", $request->Task)->first();

        $selectedSi = null;
        $containers = collect();
        if ($pl) {
            if (!empty($pl->Is_Final) && !empty($pl->BC_No)) {
                $bcData = $this->resolveStuffingDataByBc($pl->BC_No);
                $containers = $bcData['containers'];
                $selectedSi = $bcData['si'];
            } else {
                if (!empty($pl->Final_SI_No)) {
                    $selectedSi = ShippingInstruction::where('Final_SI_No', $pl->Final_SI_No)->first();
                }
                if (!$selectedSi && !empty($pl->SI_No)) {
                    $selectedSi = ShippingInstruction::where('SI_No', $pl->SI_No)->first();
                }
                if ($selectedSi) {
                    $containers = VesselScheduleDtl::active()->where('SI_No', $selectedSi->SI_No)->get();
                }
            }
        }

        $users = User::where('Status', 'ACTIVE')->orderBy('Name')->get();

        return view('cns.export.pl-detail', compact('plno','fsis', 'companies', 'masProducts', 'pl', 'selectedSi', 'containers', 'users'));
    }

    public function detailPackingListSubmit(Request $request)
    {
        $id = $request->input('id');
        $pl = null;
        if ($id) {
            $pl = PackingList::where('id', $id)->first();
        }
        if (!$pl && $request->filled('Packing_List_No')) {
            $pl = PackingList::where('Packing_List_No', $request->input('Packing_List_No'))->first();
        }

        if (!$pl) {
            return response()->json(['error' => 'Packing List not found'], 404);
        }

        $pl->Final_SI_No = $request->input('Final_SI_No', $pl->Final_SI_No);
        $pl->Order_No = $request->input('Order_No', $pl->Order_No);
        $pl->PL_Date = $request->input('PL_Date', $pl->PL_Date);
        if ($request->filled('Qty1') && $request->filled('Qty2')) {
            $pl->Qty = $request->input('Qty1') . " x " . $request->input('Qty2');
            $pl->Qty1 = $request->input('Qty1');
            $pl->Qty2 = $request->input('Qty2');
        }
        $pl->Shipper = $request->input('Shipper', $pl->Shipper);
        $pl->Consignee = $request->input('Consignee', $pl->Consignee);
        $pl->Notify = $request->input('Notify', $pl->Notify);
        $pl->Destination_Port = $request->input('Destination_Port', $pl->Destination_Port);
        $pl->Delivery_Terms = $request->input('Delivery_Terms', $pl->Delivery_Terms);
        $pl->Contract_No = $request->input('Contract_No', $pl->Contract_No);
        $pl->Voyage = $request->input('Voyage', $pl->Voyage);
        $pl->ETD = $request->input('ETD', $pl->ETD);
        $pl->ETA = $request->input('ETA', $pl->ETA);
        $pl->Stuffing = $request->input('Stuffing', $pl->Stuffing);
        $pl->POL = $request->input('POL', $pl->POL);
        $pl->POD = $request->input('POD', $pl->POD);
        $pl->PEB_Date = $request->input('PEB_Date', $pl->PEB_Date);
        $pl->PEB_No = $request->input('PEB_No', $pl->PEB_No);
        $pl->BL_No = $request->input('BL_No', $pl->BL_No);
        $pl->HS_Code = $request->input('HS_Code', $pl->HS_Code);
        $pl->Product_Name = $request->input('Product_Name', $pl->Product_Name);
        if ($request->has('Remarks')) {
            $pl->Remarks = $request->input('Remarks');
        }
        if ($request->has('Description')) {
            $pl->Description = $request->input('Description');
        }
        if ($request->filled('Total_Netto_KG')) {
            $pl->Total_Netto_KG = (float) $request->input('Total_Netto_KG');
        }
        if ($request->filled('Total_Gross_KG')) {
            $pl->Total_Gross_KG = (float) $request->input('Total_Gross_KG');
        }
        if ($request->has('approved_by')) {
            $pl->approved_by = $request->input('approved_by');
        }
        $pl->Updated_Date = now();
        $pl->Updated_By = auth()->user()->username;
        $pl->save();

        return response("success:Done Update Packing List");
    }

    public function approvePackingList(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        $id = $request->input('id');
        $plNo = $request->input('Task') ?: $request->input('Packing_List_No');

        $query = PackingList::query();
        if ($id) {
            $query->where('id', $id);
        } elseif ($plNo) {
            $query->where('Packing_List_No', $plNo);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Parameter ID atau nomor Packing List tidak ditemukan.'], 422);
        }

        $pl = $query->first();
        if (!$pl) {
            return response()->json(['status' => 'error', 'message' => 'Packing List tidak ditemukan.'], 404);
        }

        $approvedBy = $request->input('approved_by') ?: $pl->approved_by;

        $canApprove = $user->username === 'admin'
            || $user->hasRole('ADM')
            || $user->hasRole('super-admin')
            || in_array(($user->Priv_ID ?? ''), ['ADM', 'CNS', 'SPV', 'MANAGER'])
            || $user->can('menu.SI00309.approve')
            || $user->can('cns.packing_list.approve')
            || (!empty($approvedBy) && (int) $user->id === (int) $approvedBy);

        if (!$canApprove) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki hak akses untuk menyetujui (approve) Packing List.'
            ], 403);
        }

        if ($pl->Status === 'APPROVED') {
            return response()->json(['status' => 'error', 'message' => 'Packing List ini sudah berstatus APPROVED.'], 422);
        }

        if (empty($approvedBy)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Silakan pilih user Approved By terlebih dahulu.'
            ], 422);
        }

        $approver = User::find($approvedBy);
        if (!$approver) {
            return response()->json(['status' => 'error', 'message' => 'User approver tidak ditemukan.'], 422);
        }

        $sigPath = user_signature_path($approver->id);
        if (!$sigPath) {
            return response()->json([
                'status' => 'error',
                'message' => "User approver ({$approver->Name}) belum memiliki file signature (tanda tangan) di profilnya."
            ], 422);
        }

        if (empty($approver->signature) && file_exists($sigPath)) {
            $relPath = str_replace(public_path('filevendor/'), '', $sigPath);
            $approver->signature = $relPath;
            $approver->save();
        }

        $pl->approved_by = $approver->id;
        $pl->Status = 'APPROVED';
        $pl->Updated_Date = now();
        $pl->Updated_By = $user->username;
        $pl->save();

        return response()->json([
            'status' => 'success',
            'message' => "Packing List {$pl->Packing_List_No} berhasil disetujui (APPROVED).",
            'pl_status' => $pl->Status,
        ]);
    }

    private function imageToDataUri($path): ?string
    {
        $path = trim((string) $path);
        if ($path === '' || preg_match('#^https?://#i', $path)) {
            return null;
        }

        $fullPath = is_file($path) ? $path : public_path(ltrim($path, '/'));
        if (!is_file($fullPath) || !file_exists($fullPath)) {
            return null;
        }

        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        if ($extension === 'png') {
            if (class_exists('\\Imagick')) {
                try {
                    $image = new \Imagick($fullPath);
                    $image->setImageFormat('png');
                    $image->setInterlaceScheme(\Imagick::INTERLACE_NO);
                    $binary = $image->getImageBlob();
                    $image->clear();
                    $image->destroy();
                    if ($binary !== null && $binary !== '') {
                        return 'data:image/png;base64,' . base64_encode($binary);
                    }
                } catch (\Throwable $e) {
                }
            }


            $image = @imagecreatefrompng($fullPath);
            if ($image !== false) {
                imagealphablending($image, false);
                imagesavealpha($image, true);
                imageinterlace($image, false);
                ob_start();
                imagepng($image);
                $binary = ob_get_clean();
                imagedestroy($image);
                if ($binary !== false) {
                    return 'data:image/png;base64,' . base64_encode($binary);
                }
            }
        }

        $mime = $extension === 'jpg' ? 'jpeg' : $extension;
        $binary = @file_get_contents($fullPath);
        if ($binary === false) {
            return null;
        }

        return 'data:image/' . $mime . ';base64,' . base64_encode($binary);
    }

    private function resolveSchedulesByBc(string $bcNo)
    {
        return VesselSchedule::where(function ($q) use ($bcNo) {
            $q->where('BC_No', $bcNo)
                ->orWhere(function ($sub) use ($bcNo) {
                    $sub->where(function ($empty) {
                        $empty->whereNull('BC_No')->orWhere('BC_No', '');
                    })->where(function ($fallback) use ($bcNo) {
                        $fallback->where('Booking_No', $bcNo)->orWhere('Order_No', $bcNo);
                    });
                });
        })->orderBy('id')->get();
    }

    private function resolveStuffingDataByBc(string $bcNo): array
    {
        $schedules = $this->resolveSchedulesByBc($bcNo);
        if ($schedules->isEmpty()) {
            return [
                'schedules' => collect(),
                'containers' => collect(),
                'si' => null,
                'header' => [],
                'totals' => [
                    'container_count' => 0,
                    'tare' => 0,
                    'netto_kg' => 0,
                    'netto_mt' => 0,
                    'gross_kg' => 0,
                    'gross_mt' => 0,
                ],
                'description' => '',
            ];
        }

        $taskIds = $schedules->pluck('Task_ID')->filter()->unique()->values()->all();
        $orderNos = $schedules->pluck('Order_No')->filter()->unique()->values()->all();

        $containers = VesselScheduleDtl::active()
            ->where(function ($q) use ($taskIds, $orderNos) {
                if (count($taskIds)) {
                    $q->whereIn('Task_ID', $taskIds);
                }
                if (count($orderNos)) {
                    count($taskIds) ? $q->orWhereIn('Order_No', $orderNos) : $q->whereIn('Order_No', $orderNos);
                }
            })
            ->orderBy('id')
            ->get();

        $first = $schedules->first();
        $si = null;
        if (!empty($first->SI_No)) {
            $si = ShippingInstruction::where('SI_No', $first->SI_No)->first();
        }
        if (!$si && count($orderNos)) {
            $si = ShippingInstruction::whereIn('Order_No', $orderNos)->first();
        }

        $totalNettoKg = round((float) $containers->sum('Netto_KG'), 2);
        $totalGrossKg = round((float) $containers->sum('Gross_KG'), 2);
        $totals = [
            'container_count' => $containers->count(),
            'tare' => round((float) $containers->sum('Tare'), 2),
            'netto_kg' => $totalNettoKg,
            'netto_mt' => round($totalNettoKg / 1000, 2),
            'gross_kg' => $totalGrossKg,
            'gross_mt' => round($totalGrossKg / 1000, 2),
        ];

        $productName = (string) ($si->Product_Name ?? ($first->Product_Name ?? 'REFINED USED COOKING OIL'));
        $description = $this->buildFinalDescription($productName, $totalNettoKg, $totalGrossKg);

        $header = [
            'Order_No' => $first->Order_No ?? '',
            'SI_No' => $si->SI_No ?? ($first->SI_No ?? ''),
            'Final_SI_No' => $si->Final_SI_No ?? '',
            'SI_Date' => $si->SI_Date ?? '',
            'Shipper' => $si->Shipper ?? '',
            'Consignee' => $si->Consignee ?? '',
            'Notify' => $si->Notify ?? '',
            'Destination_Port' => $si->Destination_Port ?? '',
            'Delivery_Terms' => $si->Delivery_Terms ?? '',
            'Contract_No' => $si->Contract_No ?? ($first->Contract_No ?? ''),
            'Voyage' => $si->Voyage ?? ($first->Vessel ?? ''),
            'ETD' => $si->ETD ?? ($first->ETD ?? ''),
            'ETA' => $si->ETA ?? ($first->ETA ?? ''),
            'Stuffing' => $first->Stuffing ?? ($si->Stuffing ?? ''),
            'POL' => $first->POL ?? ($si->POL ?? ''),
            'POD' => $first->POD ?? ($si->POD ?? ''),
            'PEB_No' => $si->PEB_No ?? '',
            'PEB_Date' => $si->PEB_Date ?? '',
            'BL_No' => $si->BL_No ?? '',
            'HS_Code' => $si->HS_Code ?? '',
            'Product_Name' => $productName,
            'Qty1' => $containers->count() > 0 ? (string) $containers->count() : '',
            'Qty2' => $si->Qty2 ?? '20 FT',
        ];

        return [
            'schedules' => $schedules,
            'containers' => $containers,
            'si' => $si,
            'header' => $header,
            'totals' => $totals,
            'description' => $description,
        ];
    }

    private function buildFinalDescription(string $productName, float $nettoKg, float $grossKg): string
    {
        $lines = [];
        $trimmedProduct = trim($productName);
        if ($trimmedProduct !== '') {
            $lines[] = strtoupper($trimmedProduct);
        }
        $lines[] = 'NW: ' . number_format($nettoKg, 2, '.', ',') . ' KGS';
        $lines[] = 'GW: ' . number_format($grossKg, 2, '.', ',') . ' KGS';

        return implode("\n", $lines);
    }
}
