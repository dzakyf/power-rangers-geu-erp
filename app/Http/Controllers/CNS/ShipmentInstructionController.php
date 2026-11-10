<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use App\ShipmentInstruction;
use App\Services\CNS\ShippingInstructionPdfService;
use App\User;
use App\VesselSchedule;
use App\VesselScheduleDtl;
use App\VesselScheduleDtlLog;
use App\VesselScheduleLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShipmentInstructionController extends Controller
{
    protected $pdfService;

    public function __construct(ShippingInstructionPdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    public function indexShipmentInstruction()
    {
        $approvers = cns_eligible_approvers();

        return view('cns.export.si', compact('approvers'));
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

        $shipments = ShipmentInstruction::whereBetween('SI_Date', [$request->tgl1, $request->tgl2])->whereIn('Status', ['DRAFT', 'CREATED', 'APPROVED'])->get();
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
        $shipment = ShipmentInstruction::where('SI_No', $request->Task)->first();

        abort_if(!$shipment, 404);

        $pdf = $this->pdfService->render($shipment, 'SI');

        return response($pdf['content'], 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $pdf['filename'] . '"',
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
            'Consignee_Address' => (string) $request->input('Consignee_Address', ''),
            'Shipper' => (string) $request->input('Shipper', ''),
            'Shipper_Address' => (string) $request->input('Shipper_Address', ''),
            'Notify' => (string) $request->input('Notify', ''),
            'Notify_Address' => (string) $request->input('Notify_Address', ''),
            'Destination_Port' => (string) $request->input('Destination_Port', ''),
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

    public function deleteShipmentInstruction(Request $request)
    {
        $id = $request->input('id') ?? $request->input('Task');

        if (!$id) {
            return response()->json(['status' => 'error', 'message' => 'ID Shipping Instruction tidak ditemukan.'], 400);
        }

        $si = ShipmentInstruction::where('id', $id)
            ->orWhere('SI_No', $id)
            ->first();

        if (!$si) {
            return response()->json(['status' => 'error', 'message' => 'Shipping Instruction tidak ditemukan.'], 404);
        }

        if ($si->Status === 'FINAL' || !empty($si->Final_SI_No)) {
            return response()->json(['status' => 'error', 'message' => 'Shipping Instruction yang sudah menjadi Final SI tidak dapat dihapus.'], 422);
        }

        if (!empty($si->SI_No)) {
            $hasPl = DB::table('packing_lists')->where('SI_No', $si->SI_No)->exists();
            $hasInv = DB::table('commercial_invoices')->where('SI_No', $si->SI_No)->exists();
            $hasWbt = DB::table('weight_bridge_ticket')->where('SI_No', $si->SI_No)->exists();
            $hasAnnex = DB::table('annex_vii')->where('SI_No', $si->SI_No)->exists();

            if ($hasPl || $hasInv || $hasWbt || $hasAnnex) {
                return response()->json(['status' => 'error', 'message' => 'Shipping Instruction tidak dapat dihapus karena sudah memiliki dokumen turunan.'], 422);
            }
        }

        DB::transaction(function () use ($si) {
            if ($si->SI_No) {
                $assignedContainerIds = VesselScheduleDtl::active()->where('SI_No', $si->SI_No)->pluck('ID')->all();
                if (!empty($assignedContainerIds) && $si->Order_No) {
                    $order = VesselSchedule::where('Order_No', $si->Order_No)->orderBy('id')->first();
                    if ($order) {
                        $this->writeSiScheduleLog($order, 'DELETE SI', $assignedContainerIds);
                    }
                }
                VesselScheduleDtl::active()->where('SI_No', $si->SI_No)->update(['SI_No' => null]);
            }

            $si->delete();
        });

        return response()->json(['status' => 'success', 'message' => 'Shipping Instruction berhasil dihapus.']);
    }

    public function generateFinalShipmentInstruction(Request $request)
    {
        $si = ShipmentInstruction::where('SI_No', $request->Task)->where('Status', 'APPROVED')->first();

        if (!$si) {
            echo "<script>alert('Shipping Instruction harus berstatus APPROVED sebelum dibuatkan Final SI.')</script>";
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
                ->whereIn('Status', ['DRAFT', 'CREATED', 'APPROVED'])
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
        $users = User::where('Status', 'ACTIVE')->orderBy('Name')->get();

        return view('cns.export.si-detail', compact('si', 'orders', 'containers', 'assigned', 'masProducts', 'companies', 'contract', 'users'));
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
            'approved_by' => 'nullable|exists:users,id',
        ]);

        $si->SI_Date = $request->input('SI_Date');
        if ($request->filled('approved_by')) {
            $si->approved_by = $request->input('approved_by');
        }
        $si->Shipper = (string) $request->input('Shipper', '');
        $si->Shipper_Address = (string) $request->input('Shipper_Address', '');
        $si->Consignee = (string) $request->input('Consignee', '');
        $si->Consignee_Address = (string) $request->input('Consignee_Address', '');
        $si->Notify = (string) $request->input('Notify', '');
        $si->Notify_Address = (string) $request->input('Notify_Address', '');
        $si->Destination_Port = (string) $request->input('Destination_Port', '');
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
                ->whereIn('Status', ['DRAFT', 'CREATED', 'APPROVED'])
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

            if ($si->Status !== 'APPROVED') {
                $si->Status = !empty($selected) ? 'CREATED' : 'DRAFT';
            }

            if (!empty($selected)) {
                $this->writeSiScheduleLog($order, 'UPDATE SI', $selected);
            }
        }

        $si->save();

        return response()->json(['success' => true, 'message' => 'Shipping Instruction updated successfully', 'status' => $si->Status]);
    }

    public function approveShipmentInstruction(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        $id = $request->input('id');
        $siNo = $request->input('Task') ?: $request->input('SI_No');

        $query = ShipmentInstruction::query();
        if ($id) {
            $query->where('id', $id);
        } elseif ($siNo) {
            $query->where('SI_No', $siNo);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Parameter ID atau SI_No tidak ditemukan.'], 422);
        }

        $si = $query->first();
        if (!$si) {
            return response()->json(['status' => 'error', 'message' => 'Shipment Instruction tidak ditemukan.'], 404);
        }

        $approvedBy = $request->input('approved_by') ?: $si->approved_by;

        $canApprove = $user->username === 'admin'
            || $user->hasRole('ADM')
            || $user->hasRole('super-admin')
            || in_array(($user->Priv_ID ?? ''), ['ADM', 'CNS', 'SPV', 'MANAGER'])
            || $user->can('menu.SI00307.approve')
            || $user->can('cns.shipment_instruction.approve')
            || (!empty($approvedBy) && (int) $user->id === (int) $approvedBy);

        if (!$canApprove) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki hak akses untuk menyetujui (approve) Shipment Instruction.'
            ], 403);
        }

        if ($si->Status === 'APPROVED') {
            return response()->json(['status' => 'error', 'message' => 'Shipment Instruction ini sudah berstatus APPROVED.'], 422);
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
            'message' => "Shipment Instruction {$si->SI_No} berhasil disetujui (APPROVED).",
            'si_status' => $si->Status,
        ]);
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
