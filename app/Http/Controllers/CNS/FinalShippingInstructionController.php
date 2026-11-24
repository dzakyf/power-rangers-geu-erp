<?php

namespace App\Http\Controllers\CNS;

use App\CommercialInvoice;
use App\Http\Controllers\Controller;
use App\PackingList;
use App\ShippingInstruction;
use App\Services\CNS\ShippingInstructionPdfService;
use App\Support\DhtmlxGridPagination;
use App\User;
use App\VesselSchedule;
use App\VesselScheduleDtl;
use App\VesselScheduleLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinalShippingInstructionController extends Controller
{
    protected $pdfService;

    public function __construct(ShippingInstructionPdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    public function indexFinalShipmentInstruction()
    {
        return $this->indexFinalShippingInstruction();
    }

    public function indexFinalShippingInstruction()
    {
        $approvers = cns_eligible_approvers();

        return view('cns.export.fsi', compact('approvers'));
    }

    public function indexFinalShipmentInstructionXml(Request $request)
    {
        return $this->indexFinalShippingInstructionXml($request);
    }

    public function indexFinalShippingInstructionXml(Request $request)
    {
        $query = ShippingInstruction::whereBetween("Final_SI_Date", [$request->tgl1, $request->tgl2])
            ->whereIn('Status', ["FINAL", "APPROVED"]);

        $colMap = [
            1 => 'Final_SI_No',
            2 => 'Final_SI_Date',
            3 => 'SI_No',
            10 => 'Status',
        ];

        $paginated = DhtmlxGridPagination::paginateQuery($query, $request, [
            'col_map' => $colMap,
            'default_page_size' => 25,
        ]);

        $shipments = $paginated['rows'];
        $siNos = $shipments->pluck('SI_No')->filter()->unique()->values()->all();
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

        $bodyXml = '';
        $nomor = $paginated['posStart'] + 1;

        foreach ($shipments as $shipment) {
            $bodyXml .= "<row id=\"$shipment->id\" >";
            $bodyXml .= "<cell>" . $nomor++ . "</cell>";
            $bodyXml .= "<cell>" . htmlspecialchars($shipment->Final_SI_No) . "</cell>";
            $bodyXml .= "<cell>" . htmlspecialchars($shipment->Final_SI_Date) . "</cell>";
            $bodyXml .= "<cell>" . htmlspecialchars($shipment->SI_No) . "</cell>";
            if (isset($vsdData[$shipment->SI_No])) {
                $bodyXml .= "<cell>" . htmlspecialchars(implode(', ', $vsdData[$shipment->SI_No]['list'])) . "</cell>";
                $bodyXml .= "<cell>" . htmlspecialchars($vsdData[$shipment->SI_No]['tare']) . "</cell>";
                $bodyXml .= "<cell>" . htmlspecialchars($vsdData[$shipment->SI_No]['netto_kg']) . "</cell>";
                $bodyXml .= "<cell>" . htmlspecialchars($vsdData[$shipment->SI_No]['netto_mt']) . "</cell>";
                $bodyXml .= "<cell>" . htmlspecialchars($vsdData[$shipment->SI_No]['gross_kg']) . "</cell>";
                $bodyXml .= "<cell>" . htmlspecialchars($vsdData[$shipment->SI_No]['gross_mt']) . "</cell>";
            } else {
                $bodyXml .= "<cell></cell>";
                $bodyXml .= "<cell></cell>";
                $bodyXml .= "<cell></cell>";
                $bodyXml .= "<cell></cell>";
                $bodyXml .= "<cell></cell>";
                $bodyXml .= "<cell></cell>";
            }
            $bodyXml .= "<cell>" . htmlspecialchars((string) $shipment->Status) . "</cell>";
            $bodyXml .= "</row>\n";
        }

        return DhtmlxGridPagination::response($paginated['total'], $paginated['posStart'], $bodyXml);
    }

    public function printFinalShipmentInstruction(Request $request)
    {
        return $this->printFinalShippingInstruction($request);
    }

    public function printFinalShippingInstruction(Request $request)
    {
        $shipment = ShippingInstruction::where('Final_SI_No', $request->Task)->first();

        abort_if(!$shipment, 404);

        $pdf = $this->pdfService->render($shipment, 'FSI');

        return response($pdf['content'], 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $pdf['filename'] . '"',
        ]);
    }

    public function newFinalShipmentInstruction()
    {
        return $this->newFinalShippingInstruction();
    }

    public function newFinalShippingInstruction()
    {
        $vss = VesselSchedule::get();
        $sis = ShippingInstruction::where('Status', "APPROVED")->get();
        $masProducts = DB::table('master_product_cns')->get();
        $companies = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        return view('cns.export.fsi-new', compact('vss', 'sis', 'companies', 'masProducts'));
    }

    public function newFinalShipmentInstructionSubmit(Request $request)
    {
        return $this->newFinalShippingInstructionSubmit($request);
    }

    public function newFinalShippingInstructionSubmit(Request $request)
    {
        $error = DB::transaction(function () use ($request) {
            $si = ShippingInstruction::where("SI_No", $request->SI_No)->lockForUpdate()->first();
            if (!$si || $si->Status !== 'APPROVED') {
                return 'Shipping Instructions harus berstatus APPROVED sebelum dibuatkan Final SI.';
            }
            if (!empty($si->Final_SI_No)) {
                return "Shipping Instructions ini sudah memiliki Final SI ({$si->Final_SI_No}).";
            }

            $SINo = ShippingInstruction::generateNextFinalSiNo($request->SI_Date);
            $fsiUpdate = [
                'Final_SI_No' => $SINo,
                'Final_SI_Date' => $request->SI_Date ?: date('Y-m-d'),
                'Status' => "FINAL",
                'Updated_Date' => now(),
                'Updated_By' => auth()->user()->username
            ];
            if ($request->has('Remarks')) {
                $fsiUpdate['Remarks'] = $request->input('Remarks');
            }
            ShippingInstruction::where("id", $si->id)->update($fsiUpdate);

            return null;
        }, 3);

        if ($error !== null) {
            echo "<script>alert(" . json_encode($error) . ")</script>";
            return;
        }

        echo "<script>alert('Done Generate to Final SI')</script>";
    }

    public function getSI(Request $request)
    {
        $SINo = $request->input('SI_No');
        $shipment = ShippingInstruction::where('SI_No', $SINo)->first();
        if ($shipment) {
            $vsd = VesselScheduleDtl::active()->where("SI_No", $SINo)->get();
            $shipment->orderDetails = $vsd;
            unset($shipment->id);
            return response()->json($shipment);
        } else {
            return response()->json(['error' => 'Shipping Instructions not found'], 404);
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
        return $this->detailFinalShippingInstruction();
    }

    public function detailFinalShippingInstruction()
    {
        $vss = VesselSchedule::get();
        $sis = ShippingInstruction::where('Status', "CREATED")->get();
        $masProducts = DB::table('master_product_cns')->get();
        $companies = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        $shipment = ShippingInstruction::where('ID', request()->query('Task'))->first();
        if ($shipment) {
            $vsd = VesselScheduleDtl::active()->where("SI_No", $shipment->SI_No)->get();
            $shipment->orderDetails = $vsd;
        }
        $users = User::where('Status', 'ACTIVE')->orderBy('Name')->get();
        return view('cns.export.fsi-detail', compact('vss', 'sis', 'companies', 'masProducts', 'shipment', 'users'));
    }

    public function detailFinalShipmentInstructionSubmit(Request $request)
    {
        return $this->detailFinalShippingInstructionSubmit($request);
    }

    public function detailFinalShippingInstructionSubmit(Request $request)
    {
        $id = $request->input('id');
        $si = null;
        if ($id) {
            $si = ShippingInstruction::where('id', $id)->first();
        }
        if (!$si && $request->filled('SI_No')) {
            $si = ShippingInstruction::where('SI_No', $request->input('SI_No'))->first();
        }

        if (!$si) {
            return response()->json(['error' => 'Shipping Instructions not found'], 404);
        }

        $request->validate([
            'approved_by' => 'nullable|exists:users,id',
        ]);

        $updateData = [
            'Updated_Date' => now(),
            'Updated_By' => auth()->user()->username,
        ];

        if ($request->has('approved_by')) {
            $updateData['approved_by'] = $request->approved_by;
        }

        if ($request->filled('Shipper')) {
            $updateData['Shipper'] = $request->Shipper;
        }
        if ($request->has('Shipper_Address')) {
            $updateData['Shipper_Address'] = $request->Shipper_Address;
        }
        if ($request->filled('Consignee')) {
            $updateData['Consignee'] = $request->Consignee;
        }
        if ($request->has('Consignee_Address')) {
            $updateData['Consignee_Address'] = $request->Consignee_Address;
        }
        if ($request->filled('Notify')) {
            $updateData['Notify'] = $request->Notify;
        }
        if ($request->has('Notify_Address')) {
            $updateData['Notify_Address'] = $request->Notify_Address;
        }
        if ($request->has('PEB_No')) {
            $updateData['PEB_No'] = $request->PEB_No;
        }
        if ($request->has('PEB_Date')) {
            $updateData['PEB_Date'] = $request->PEB_Date;
        }
        if ($request->has('BL_No')) {
            $updateData['BL_No'] = $request->BL_No;
        }
        if ($request->has('POL')) {
            $updateData['POL'] = $request->POL;
        }
        if ($request->has('POD')) {
            $updateData['POD'] = $request->POD;
        }
        if ($request->has('Remarks')) {
            $updateData['Remarks'] = $request->Remarks;
        }

        $si->update($updateData);

        return response("success:Done Update Final SI");
    }

    public function approveFinalShipmentInstruction(Request $request)
    {
        return $this->approveFinalShippingInstruction($request);
    }

    public function approveFinalShippingInstruction(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        $id = $request->input('id');
        $fsiNo = $request->input('Task') ?: $request->input('Final_SI_No') ?: $request->input('SI_No');

        $query = ShippingInstruction::query();
        if ($id) {
            $query->where('id', $id);
        } elseif ($fsiNo) {
            $query->where(function ($q) use ($fsiNo) {
                $q->where('Final_SI_No', $fsiNo)->orWhere('SI_No', $fsiNo);
            });
        } else {
            return response()->json(['status' => 'error', 'message' => 'Parameter ID atau nomor Final SI tidak ditemukan.'], 422);
        }

        $si = $query->first();
        if (!$si) {
            return response()->json(['status' => 'error', 'message' => 'Final Shipping Instructions tidak ditemukan.'], 404);
        }

        $approvedBy = $request->input('approved_by') ?: $si->approved_by;

        $canApprove = $user->username === 'admin'
            || $user->hasRole('ADM')
            || $user->hasRole('super-admin')
            || in_array(($user->Priv_ID ?? ''), ['ADM', 'CNS', 'SPV', 'MANAGER'])
            || $user->can('menu.SI00308.approve')
            || $user->can('cns.final_shipping_instruction.approve')
            || $user->can('cns.final_shipment_instruction.approve')
            || (!empty($approvedBy) && (int) $user->id === (int) $approvedBy);

        if (!$canApprove) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki hak akses untuk menyetujui (approve) Final Shipping Instructions.'
            ], 403);
        }

        if ($si->Status === 'APPROVED') {
            return response()->json(['status' => 'error', 'message' => 'Final Shipping Instructions ini sudah berstatus APPROVED.'], 422);
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

        $si->approved_by = $approver->id;
        $si->Status = 'APPROVED';
        $si->Updated_Date = now();
        $si->Updated_By = $user->username;
        $si->save();

        return response()->json([
            'status' => 'success',
            'message' => "Final Shipping Instructions " . ($si->Final_SI_No ?: $si->SI_No) . " berhasil disetujui (APPROVED).",
            'si_status' => $si->Status,
        ]);
    }

    public function generatePackingList(Request $request)
    {
        $task = trim((string) $request->Task);
        if ($task === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'Nomor Final Shipping Instructions tidak ditemukan.'
            ], 422);
        }

        $shipment = ShippingInstruction::where('Final_SI_No', $task)->first();
        if (!$shipment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Final Shipping Instructions tidak ditemukan.'
            ], 404);
        }

        if ($shipment->Status !== 'APPROVED') {
            return response()->json([
                'status' => 'error',
                'message' => 'Packing List hanya dapat dibuat untuk Final Shipping Instructions yang sudah berstatus APPROVED.'
            ], 422);
        }

        $ci = PackingList::where('Final_SI_No', $task)
            ->whereIn('Status', ['CREATED', 'APPROVED'])
            ->first();
        if ($ci) {
            return response()->json([
                'status' => 'error',
                'message' => "Packing List ({$ci->Packing_List_No}) sudah pernah dibuat untuk Final Shipping Instructions ini."
            ], 422);
        }

        $vs = VesselSchedule::where("Order_No", $shipment->Order_No)->first();
        if (!$vs) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vessel Schedule tidak ditemukan untuk Order No ' . ($shipment->Order_No ?: '-') . '.'
            ], 422);
        }

        $vsd = VesselScheduleDtl::active()->where('Task_ID', $vs->Task_ID)
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
        
        $shipmentData = json_decode(json_encode($shipment), true);
        unset($shipmentData['id']);
        $shipmentData["Packing_List_No"] = "PL-GEU/EXP-BC/" . sprintf('%03d',$counter + 1) . "/" . $this->getRomawi(date("n")) . "/" . date("Y");
        $shipmentData['PL_Date'] = $request->PL_Date;
        $shipmentData['approved_by'] = $shipment->approved_by ?? ($shipmentData['approved_by'] ?? null);
        $shipmentData['Created_By'] = auth()->user()->username;
        $shipmentData['Created_Date'] = now();
        $shipmentData['Updated_By'] = null;
        $shipmentData['Updated_Date'] = null;
        $shipmentData['Status'] = "CREATED";
        PackingList::create($shipmentData);

        return response()->json([
            'status' => 'success',
            'message' => "Packing List {$shipmentData['Packing_List_No']} berhasil dibuat.",
            'packing_list_no' => $shipmentData['Packing_List_No'],
        ]);
    }

    public function generateCommercialInvoice(Request $request)
    {
        $shipment = ShippingInstruction::where('Final_SI_No', $request->Task)->first();
        if (!$shipment) {
            echo "<script>alert('Shipping Instructions not found');</script>";
            return;
        }

        $ci = CommercialInvoice::where('SI_No', $request->Task)->first();
        if ($ci) {
            echo "<script>alert('Commercial Invoice already generated for this Shipping Instructions');</script>";
            return;
        }

        $vs = VesselSchedule::where("Order_No", $shipment->Order_No)->first();
        $vsd = VesselScheduleDtl::active()->where('Task_ID', $vs->Task_ID)
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

        $getVSD = VesselScheduleDtl::active()->where("Order_No", $vs['Order_No'])->where("SI_No", $shipment->SI_No)->sum('Netto_MT');
    
        $counter = CommercialInvoice::where('Inv_Date', "LIKE", date("Y-m") . "%")->count();
        
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
    }
}
