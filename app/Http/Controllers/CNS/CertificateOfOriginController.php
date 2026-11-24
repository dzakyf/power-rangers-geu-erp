<?php

namespace App\Http\Controllers\CNS;

use App\CertificateOfOrigin;
use App\CertificateOfOriginDtl;
use App\Http\Controllers\Controller;
use App\Services\CNS\CnsCodeGenerator;
use App\Support\DhtmlxGridPagination;
use App\WeightBridgeTicket;
use App\WeightBridgeTicketDtl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CertificateOfOriginController extends Controller
{
    public function indexCertificateOfOrigin()
    {
        return view('cns.export.coo');
    }

    public function indexCertificateOfOriginXml(Request $request)
    {
        $company = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        $companies = [];
        foreach ($company as $value) {
            $companies[$value->Supplier_ID] = $value->Company_Name;
        }

        $query = CertificateOfOrigin::query();

        $colMap = [
            1 => 'COO_No',
            2 => 'Fin_Inv_No',
            3 => 'Fin_Inv_Date',
            4 => 'Contract_No',
            6 => 'Final_Destination',
            7 => 'Dispatch_Method',
            8 => 'Shipment_Type',
            9 => 'HS_Code',
            10 => 'Good_Description',
            11 => 'Net_Weight',
            12 => 'Note',
        ];

        $paginated = DhtmlxGridPagination::paginateQuery($query, $request, [
            'col_map' => $colMap,
            'default_page_size' => 25,
        ]);

        $bodyXml = '';
        $nomor = $paginated['posStart'] + 1;

        foreach ($paginated['rows'] as $value) {
            $consigneeName = $companies[$value->Consignee] ?? $value->Consignee;
            $bodyXml .= "<row id=\"$value->ID\">";
            $bodyXml .= "<cell>$nomor</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->COO_No)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->Fin_Inv_No)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->Fin_Inv_Date)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->Contract_No)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($consigneeName)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->Final_Destination)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->Dispatch_Method ?? $value->Method_Of_Dispatch)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->Shipment_Type)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->HS_Code)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->Good_Description)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->Net_Weight)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->Note)."</cell>";
            $bodyXml .= "</row>\n";

            $nomor++;
        }

        return DhtmlxGridPagination::response($paginated['total'], $paginated['posStart'], $bodyXml);
    }

    public function printCertificateOfOrigin(Request $request)
    {
        $company = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        foreach ($company as $value) {
            $companies[$value->Supplier_ID] = $value;
        }
        $coo = CertificateOfOrigin::where('COO_No', $request->Task)->first();
        $coo->Details = CertificateOfOriginDtl::where('COO_No', $coo->COO_No)->get();
        return view('cns.export.print.coo', compact('coo', 'companies'));
    }

    public function newCertificateOfOrigin()
    {
        $companies = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        $wbts = DB::table('weight_bridge_ticket')->get();
        $CooNo = CnsCodeGenerator::nextCooNo();

        return view('cns.export.coo-new', compact('companies', 'CooNo', 'wbts'));
    }

    public function newCertificateOfOriginSubmit(Request $request)
    {
        $r = $request->all();
        $wbt = WeightBridgeTicket::where("WBT_No", $r['WBT_No'])->first();
        if (!$wbt) {
            echo "error";
            exit;
        }

        unset($r['_token']);
        unset($r['containers']);
        unset($r['seals']);

        $r['Packing_List_No'] = $wbt->Packing_List_No;
        $r['Final_SI_No'] = $wbt->Final_SI_No;
        $r['SI_No'] = $wbt->SI_No;

        $r['Created_By']   = auth()->user()->username;
        $r['Created_Date'] = now();
        $r['Status']       = 'CREATED';

        $containers = $request->containers ?? [];
        $seals      = $request->seals ?? [];

        DB::transaction(function () use ($r, $containers, $seals) {
            $r['COO_No'] = CnsCodeGenerator::nextCooNo(null, true);
            CertificateOfOrigin::create($r);

            foreach ($containers as $i => $container) {
                if (trim($container) === '') continue;
                CertificateOfOriginDtl::create([
                    'COO_No'       => $r['COO_No'],
                    'Container_No' => $container,
                    'Seal_No'      => $seals[$i] ?? '',
                ]);
            }
        });

        echo "success";
    }

    public function getCertificateOfOriginWBT(Request $request)
    {
        $json = [];
        $wbt = WeightBridgeTicket::where('WBT_No', $request->WBT_No)->first();
        if (!$wbt) {
            $json = [
                'status' => 'error',
                'message' => 'error',
            ];
        }

        $json = WeightBridgeTicketDtl::where("WBT_No", $request->WBT_No)->join('vessel_schedule_dtls', 'weight_bridge_ticket_dtl.VSD_ID', '=', 'vessel_schedule_dtls.id')->select('weight_bridge_ticket_dtl.*', 'vessel_schedule_dtls.Container as Container', 'vessel_schedule_dtls.Seal as Seal')->get();

        $json = [
            'status' => 'success',
            'message' => 'success',
            'data' => [
                'wbt' => $wbt,
                'details' => $json
            ]
        ];


        return response()->json($json);
    }

    public function detailCertificateOfOrigin(Request $request)
    {
        $companies = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        $coo = CertificateOfOrigin::where('COO_No', $request->Task)->first();
        $coo->Details = CertificateOfOriginDtl::where('COO_No', $coo->COO_No)->get();
        return view('cns.export.coo-detail', compact('companies', 'coo'));
    }

    public function detailCertificateOfOriginSubmit(Request $request)
    {
        $r = $request->all();
        unset($r['_token']);
        unset($r['containers']);
        unset($r['seals']);

        CertificateOfOrigin::where('COO_No', $r['COO_No'])->update($r);

        CertificateOfOriginDtl::where('COO_No', $r['COO_No'])->delete();

        $containers = $request->containers ?? [];
        $seals      = $request->seals ?? [];
        foreach ($containers as $i => $container) {
            if (trim($container) === '') continue;
            CertificateOfOriginDtl::create([
                'COO_No'       => $r['COO_No'],
                'Container_No' => $container,
                'Seal_No'      => $seals[$i] ?? '',
            ]);
        }

        echo "success";
    }
}
