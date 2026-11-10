<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use App\PackingList;
use App\ShipmentInstruction;
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
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $vsdData = [];
        $vsds = VesselScheduleDtl::active()->whereNotNull('SI_No')->get();
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

        $shipments = PackingList::whereBetween('PL_Date',[$request->tgl1, $request->tgl2])->whereIn('Status', ["CREATED", "APPROVED"])->get();
        foreach ($shipments as $key => $shipment) {
            echo "<row id=\"$shipment->id\" >";
            echo "<cell>" . ($key + 1) . "</cell>";
            echo "<cell>" . htmlspecialchars($shipment->Packing_List_No) . "</cell>";
            echo "<cell>" . htmlspecialchars($shipment->Final_SI_No) . "</cell>";
            echo "<cell>" . htmlspecialchars(date("d F Y", strtotime($shipment->PL_Date))) . "</cell>";
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
            echo "<cell>" . htmlspecialchars($shipment->Status ?? 'CREATED') . "</cell>";
            echo "</row>";
        }

        echo "</rows>";
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

        $vsds = VesselScheduleDtl::active()->where('SI_No', $shipment->SI_No)->get();

        // ── Hitung total ──────────────────────────────────────────────────
        $gross = (float) $vsds->sum('Gross_KG');
        $netto = (float) $vsds->sum('Netto_KG');
        $mt    = round($netto / 1000, 2);

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

        if ($headerHtml !== '') {
            $mpdf->SetHTMLHeader($headerHtml);
        }

        if ($footerHtml !== '') {
            $mpdf->SetHTMLFooter($footerHtml);
        }
 
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

        $fsis = ShipmentInstruction::where("Status", "FINAL")->where("Final_SI_No", '!=', null)->get();

        $companies = DB::table('supplier')->where("Status", "NORMAL")->where("Location", auth()->user()->Location)->get();

        $masProducts = DB::table('master_product_cns')->get();

        $users = User::where('Status', 'ACTIVE')->orderBy('Name')->get();

        return view('cns.export.pl-new', compact('plno','fsis', 'companies', 'masProducts', 'users'));
    }

    public function newPackingListSubmit(Request $request)
    {
        $shipment = ShipmentInstruction::where("Final_SI_No", $request->Final_SI_No)->first();

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
        $shipment = ShipmentInstruction::where('Final_SI_No', $SINo)->first();
        if ($shipment) {
            $shipment->orderDetails = VesselScheduleDtl::active()->where("SI_No", $shipment->SI_No)->get();
            return response()->json($shipment);
        } else {
            return response()->json(['error' => 'Shipping Instruction not found'], 404);
        }
    }

    public function detailPackingList(Request $request)
    {
        $counter = PackingList::where('PL_Date', "LIKE", date("Y-m") . "%")->count();

        $plno = "PL-GEU/EXP-BC/" . sprintf('%03d',$counter + 1) . "/" . $this->getRomawi(date("n")) . "/" . date("Y");

        $fsis = ShipmentInstruction::where("Status", "FINAL")->where("Final_SI_No", '!=', null)->get();

        $companies = DB::table('supplier')->where("Status", "NORMAL")->where("Location", auth()->user()->Location)->get();

        $masProducts = DB::table('master_product_cns')->get();

        $pl = PackingList::where("ID", $request->Task)->first();

        $selectedSi = null;
        $containers = collect();
        if ($pl) {
            if (!empty($pl->Final_SI_No)) {
                $selectedSi = ShipmentInstruction::where('Final_SI_No', $pl->Final_SI_No)->first();
            }
            if (!$selectedSi && !empty($pl->SI_No)) {
                $selectedSi = ShipmentInstruction::where('SI_No', $pl->SI_No)->first();
            }
            if ($selectedSi) {
                $containers = VesselScheduleDtl::active()->where('SI_No', $selectedSi->SI_No)->get();
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
}
