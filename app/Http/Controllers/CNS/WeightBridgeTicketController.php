<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use App\PackingList;
use App\ShippingInstruction;
use App\Support\DhtmlxGridPagination;
use App\VesselSchedule;
use App\VesselScheduleDtl;
use App\WeightBridgeTicket;
use App\WeightBridgeTicketDtl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WeightBridgeTicketController extends Controller
{
    public function indexWeightBridgeTicket()
    {
        return view('cns.export.wbt');
    }

    public function indexWeightBridgeTicketXml(Request $request)
    {
        $query = WeightBridgeTicket::whereBetween('Departure_Date', [$request->tgl1, $request->tgl2]);

        $colMap = [
            1 => 'Packing_List_No',
            2 => 'Final_SI_No',
            3 => 'Contract_No',
            4 => 'Voyage',
            5 => 'Departure_Date',
            6 => 'BL_No',
            9 => 'Created_By',
            10 => 'Created_Date',
        ];

        $paginated = DhtmlxGridPagination::paginateQuery($query, $request, [
            'col_map' => $colMap,
            'default_page_size' => 25,
        ]);

        $wbt = $paginated['rows'];
        $siNos = $wbt->pluck('SI_No')->filter()->unique()->values()->all();
        $vsdsBySi = count($siNos)
            ? VesselScheduleDtl::active()->whereIn('SI_No', $siNos)->get()->groupBy('SI_No')
            : collect();

        $bodyXml = '';
        $nomor = $paginated['posStart'] + 1;

        foreach ($wbt as $value) {
            $vsd = $vsdsBySi->get($value->SI_No, collect());
            $nopols = [];
            $containers = [];
            foreach ($vsd as $v) {
                $containers[] = $v->Container . " (" . $v->Seal . ")";
                $nopols[] = trim((string) $v->Police_No) !== '' ? $v->Police_No : '-';
            }
            $containerText = count($containers) > 0 ? implode(',', $containers) : (string) $value->Container;
            $nopolText = count($nopols) > 0 ? implode(', ', $nopols) : '-';

            $bodyXml .= "<row id=\"$value->WBT_No\">";
            $bodyXml .= "<cell>".htmlspecialchars($nomor)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->Packing_List_No)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->Final_SI_No)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->Contract_No)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->Voyage)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars(date("d F Y", strtotime($value->Departure_Date)))."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->BL_No)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($containerText)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($nopolText)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->Created_By)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->Created_Date)."</cell>";
            $bodyXml .= "</row>\n";
            $nomor++;
        }

        return DhtmlxGridPagination::response($paginated['total'], $paginated['posStart'], $bodyXml);
    }

    public function printWeightBridgeTicket(Request $request)
    {
        $task = trim((string) $request->input('Task', ''));
        $wbt = null;
        if ($task !== '') {
            $wbt = WeightBridgeTicket::where("WBT_No", $task)->first();
            if (!$wbt && is_numeric($task)) {
                $wbt = WeightBridgeTicket::find($task);
            }
        }

        if (!$wbt) {
            abort(404, 'Weight Bridge Ticket tidak ditemukan');
        }

        // Lengkapi data yang mungkin belum tersimpan di WBT dari ShippingInstruction / PackingList
        $si = ShippingInstruction::where('SI_No', $wbt->SI_No)
            ->orWhere('Final_SI_No', $wbt->Final_SI_No)
            ->first();
        if ($si) {
            if (empty($wbt->BL_No)) {
                $wbt->BL_No = $si->BL_No ?? '';
            }
            if (empty($wbt->Departure_Date)) {
                $wbt->Departure_Date = $si->Departure_Date ?? '';
            }
            if (empty($wbt->Voyage)) {
                $wbt->Voyage = $si->Voyage ?? '';
            }
            if (empty($wbt->Contract_No)) {
                $wbt->Contract_No = $si->Contract_No ?? '';
            }
            if (empty($wbt->Shipper)) {
                $wbt->Shipper = $si->Shipper ?? '';
            }
        }

        if (empty($wbt->Shipper)) {
            $pl = PackingList::where('Packing_List_No', $wbt->Packing_List_No)->first();
            if ($pl && !empty($pl->Shipper)) {
                $wbt->Shipper = $pl->Shipper;
            }
        }

        $companies = [];
        $supplierQuery = DB::table('supplier');
        if (auth()->check() && !empty(auth()->user()->Location)) {
            $suppliers = $supplierQuery->where("Location", auth()->user()->Location)->get();
        } else {
            $suppliers = $supplierQuery->get();
        }
        foreach ($suppliers as $value) {
            $companies[$value->Supplier_ID] = $value;
        }

        if (!empty($wbt->Shipper) && !isset($companies[$wbt->Shipper])) {
            $shipperRow = DB::table('supplier')
                ->where('Supplier_ID', $wbt->Shipper)
                ->orWhere('ID', $wbt->Shipper)
                ->first();
            if ($shipperRow) {
                $companies[$wbt->Shipper] = $shipperRow;
            }
        }

        if (empty($wbt->Shipper) || !isset($companies[$wbt->Shipper])) {
            $fallback = DB::table('supplier')
                ->where('Is_Company', 1)
                ->when(auth()->check() && !empty(auth()->user()->Location), function ($q) {
                    $q->where('Location', auth()->user()->Location);
                })
                ->first()
                ?? DB::table('supplier')->where('Is_Company', 1)->whereNotNull('Letterhead')->first()
                ?? DB::table('supplier')->where('Is_Company', 1)->first()
                ?? DB::table('supplier')->whereNotNull('Letterhead')->first()
                ?? DB::table('supplier')->first();
            if ($fallback) {
                $key = $fallback->Supplier_ID ?: $fallback->ID;
                $wbt->Shipper = $key;
                $companies[$key] = $fallback;
            }
        }

        // Ambil detail foto timbangan kontainer
        $details = WeightBridgeTicketDtl::where("WBT_No", $wbt->WBT_No)
            ->leftJoin('vessel_schedule_dtls', 'weight_bridge_ticket_dtl.VSD_ID', '=', 'vessel_schedule_dtls.id')
            ->select('weight_bridge_ticket_dtl.*', 'vessel_schedule_dtls.Container as Container', 'vessel_schedule_dtls.Seal as Seal', 'vessel_schedule_dtls.Police_No as Police_No')
            ->get();

        // Jika belum ada foto di weight_bridge_ticket_dtl, fallback ke daftar kontainer dari vessel_schedule_dtls
        if ($details->isEmpty() && !empty($wbt->SI_No)) {
            $vsds = VesselScheduleDtl::active()->where("SI_No", $wbt->SI_No)->get();
            $synthetic = collect();
            foreach ($vsds as $v) {
                $synthetic->push((object)[
                    'Container' => $v->Container,
                    'Seal' => $v->Seal,
                    'Police_No' => $v->Police_No,
                    'Path_Loc' => '',
                ]);
            }
            $details = $synthetic;
        }

        $wbt->Details = $details;

        return view('cns.export.print.wbt', compact('wbt', 'companies'));
    }

    public function newWeightBridgeTicket()
    {
        $pls = PackingList::where('Status', "APPROVED")
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('weight_bridge_ticket')
                    ->whereColumn('weight_bridge_ticket.Packing_List_No', 'packing_lists.Packing_List_No');
            })
            ->get();
        return view('cns.export.wbt-new', compact('pls'));
    }

    public function getSIDetail(Request $request)
    {
        $pl = PackingList::where("Packing_List_No", $request->input("Packing_List_No"))->first();
        if ($pl) {
            $si = !empty($pl->SI_No)
                ? VesselScheduleDtl::active()->where('SI_No', $pl->SI_No)->get()
                : collect();

            if ($si->isEmpty() && !empty($pl->Order_No)) {
                $si = VesselScheduleDtl::active()->where('Order_No', $pl->Order_No)->get();
            }

            $pl->orderDetails = $si;
            return response()->json($pl);
        } else {
            return response()->json([]);
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

    public function newWeightBridgeTicketSubmit(Request $request)
    {
        $pl = PackingList::where("Packing_List_No", $request->Packing_List_No)
            ->where("Status", "APPROVED")
            ->first();
        if (!$pl || WeightBridgeTicket::where("Packing_List_No", $pl->Packing_List_No)->exists()) {
            echo "error";
            exit;
        }

        $getContainId = [];

        $r = $request->all();
        foreach ($r as $key => $value) {
            $exp = explode('Container', $key);
            if (count($exp) > 1) {
                $getContainId[] = $exp[1];
            }
        }

        $counter = WeightBridgeTicket::where("Created_Date", "LIKE", date("Y-m") . "%")->count();
        $wbtno = "WBT-GEU/" . sprintf("%03d", ($counter + 1)) . "/" . $this->getRomawi(date('n')) . "/" . date("Y");

        $wbt = WeightBridgeTicket::create([
            'WBT_No' => $wbtno,
            'Packing_List_No' => $pl->Packing_List_No,
            'Final_SI_No' => $pl->Final_SI_No,
            'SI_No' => $pl->SI_No,
            'Contract_No' => $pl->Contract_No,
            'Voyage' => $request->Voyage,
            'Departure_Date' => $request->Departure_Date,
            'BL_No' => $request->BL_No ?? '',
            'Created_By' => auth()->user()->username,
            'Created_Date' => now(),
        ]);

        foreach ($getContainId as $key => $value) {
            $vsd = VesselScheduleDtl::active()->where("id", $value)->first();
            $name = "";
            $ext = "";
            if ($r['Container' . $value] != 'undefined') {
                $file = $r['Container' . $value];
                $filename = "Container_" . strtoupper($vsd->Container) . "_" . time() . "." . $file->getClientOriginalExtension();
                $file->move(public_path('filesupplier'), $filename);
                $name = "filesupplier/" . $filename;
                $ext = $file->getClientOriginalExtension();
            }
            if ($name != '') {
                $wbtDetail = WeightBridgeTicketDtl::create([
                    'WBT_No' => $wbtno,
                    'VSD_ID' => $value,
                    'Path_Loc' => $name,
                    'Path_Type' => $ext,
                    'Created_By' => auth()->user()->username,
                    'Created_Date' => now(),
                ]);
            }
        }

        echo "success";
    }

    public function detailWeightBridgeTicket(Request $request)
    {
        $wbt = WeightBridgeTicket::where("WBT_No", $request->Task)->first();
        if (!$wbt) {
            return redirect()->back()->with('error', 'Data not found');
        }
        $pls = PackingList::where('Status', 'CREATED')
            ->when(!empty($wbt->Packing_List_No), function ($q) use ($wbt) {
                $q->orWhere('Packing_List_No', $wbt->Packing_List_No);
            })
            ->get();
        $wbt->Details = WeightBridgeTicketDtl::where("WBT_No", $wbt->WBT_No)
            ->join('vessel_schedule_dtls', 'weight_bridge_ticket_dtl.VSD_ID', '=', 'vessel_schedule_dtls.id')
            ->select('weight_bridge_ticket_dtl.*', 'vessel_schedule_dtls.Container as Container', 'vessel_schedule_dtls.Seal as Seal', 'vessel_schedule_dtls.Police_No as Police_No')
            ->get();

        if ($wbt->Details->isEmpty() && !empty($wbt->SI_No)) {
            $vsds = VesselScheduleDtl::active()->where("SI_No", $wbt->SI_No)->get();
            $wbt->Details = $vsds->map(function ($v) {
                return (object) [
                    'VSD_ID' => $v->ID,
                    'Container' => $v->Container,
                    'Seal' => $v->Seal,
                    'Police_No' => $v->Police_No,
                    'Path_Loc' => '',
                ];
            });
        }
        return view('cns.export.wbt-detail', compact('wbt', 'pls'));
    }

    public function detailWeightBridgeTicketSubmit(Request $request)
    {
        $wbt = WeightBridgeTicket::where("WBT_No", $request->WBT_No)->first();
        if (!$wbt) {
            echo "error";
            exit;
        }

        $pl = PackingList::where("Packing_List_No", $request->Packing_List_No)->first();
        if (!$pl) {
            echo "error";
            exit;
        }

        $getContainId = [];

        $r = $request->all();
        foreach ($r as $key => $value) {
            $exp = explode('Container', $key);
            if (count($exp) > 1) {
                $getContainId[] = $exp[1];
            }
        }

        $wbt->Packing_List_No = $request->Packing_List_No;
        $wbt->Final_SI_No = $request->Final_SI_No;
        $wbt->Contract_No = $request->Contract_No;
        $wbt->Voyage = $request->Voyage;
        $wbt->Departure_Date = $request->Departure_Date;
        $wbt->BL_No = $request->BL_No ?? '';
        $wbt->save();

        foreach ($getContainId as $key => $value) {
            $vsd = VesselScheduleDtl::active()->where("id", $value)->first();
            $name = "";
            $ext = "";
            if ($r['Container' . $value] != 'undefined') {
                $file = $r['Container' . $value];
                $filename = "Container_" . strtoupper($vsd->Container) . "_" . time() . "." . $file->getClientOriginalExtension();
                $file->move(public_path('filesupplier'), $filename);
                $name = "filesupplier/" . $filename;
                $ext = $file->getClientOriginalExtension();
            }
            if ($name != '') {
                $wbtDetail = WeightBridgeTicketDtl::where('WBT_No', $request->WBT_No)->where('VSD_ID', $value)->first();
                if (!$wbtDetail) {
                    $wbtDetail = WeightBridgeTicketDtl::create([
                        'WBT_No' => $request->WBT_No,
                        'VSD_ID' => $value,
                        'Path_Loc' => $name,
                        'Path_Type' => $ext,
                        'Created_By' => auth()->user()->username,
                        'Created_Date' => now(),
                    ]);
                } else {
                    $wbtDetail->Path_Loc = $name;
                    $wbtDetail->Path_Type = $ext;
                    $wbtDetail->save();
                }
            }
        }

        echo "success";
    }
}
