<?php

namespace App\Http\Controllers\CNS;

use App\DeliveryNote;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryNoteController extends Controller
{
    public function indexDeliveryNote()
    {
        return view('cns.export.dn');
    }

    public function indexDeliveryNoteXml()
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $companies = [];
        $supplierQuery = DB::table('supplier');
        if (auth()->check() && !empty(auth()->user()->Location)) {
            $company = $supplierQuery->where("Location", auth()->user()->Location)->get();
        } else {
            $company = $supplierQuery->get();
        }
        foreach ($company as $value) {
            $companies[$value->Supplier_ID] = $value->Company_Name;
        }

        $nomor = 1;
        $dns = DeliveryNote::get();
        foreach ($dns as $value) {
            $issuerName = $companies[$value->Shipper] ?? $value->Shipper;
            $consigneeName = $companies[$value->Consignee] ?? $value->Consignee;
            echo "<row id=\"$value->ID\">";
                echo "<cell>$nomor</cell>";
                echo "<cell>".htmlspecialchars($value->DN_No)."</cell>";
                echo "<cell>".htmlspecialchars($issuerName ?: '-')."</cell>";
                echo "<cell>".htmlspecialchars($value->Delivery_Date)."</cell>";
                echo "<cell>".htmlspecialchars($consigneeName)."</cell>";
                echo "<cell>".htmlspecialchars($value->Contract_No)."</cell>";
                echo "<cell>".htmlspecialchars($value->Product)."</cell>";
                echo "<cell>".htmlspecialchars($value->Total_Tonnage)."</cell>";
                echo "<cell>".htmlspecialchars($value->Country_Of_Origin)."</cell>";
                echo "<cell>".htmlspecialchars($value->Status)."</cell>";
            echo "</row>";

            $nomor++;
        }

        echo "</rows>";
    }

    public function printDeliveryNote(Request $request)
    {
        $task = trim((string) $request->input('Task', ''));
        $dn = DeliveryNote::where('DN_No', $task)->first();
        if (!$dn && is_numeric($task)) {
            $dn = DeliveryNote::find($task);
        }

        if (!$dn) {
            abort(404, 'Delivery Note tidak ditemukan');
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

        if (!empty($dn->Shipper) && !isset($companies[$dn->Shipper])) {
            $shipperRow = DB::table('supplier')->where('Supplier_ID', $dn->Shipper)->first();
            if ($shipperRow) {
                $companies[$dn->Shipper] = $shipperRow;
            }
        }
        if (!empty($dn->Consignee) && !isset($companies[$dn->Consignee])) {
            $consigneeRow = DB::table('supplier')->where('Supplier_ID', $dn->Consignee)->first();
            if ($consigneeRow) {
                $companies[$dn->Consignee] = $consigneeRow;
            }
        }

        $issuer = $companies[$dn->Shipper] ?? null;
        if (!$issuer) {
            $issuer = DB::table('supplier')->where('Is_Company', 1)->first() ?? DB::table('supplier')->first();
        }

        return view('cns.export.print.dn', compact('dn', 'companies', 'issuer'));
    }

    public function newDeliveryNote()
    {
        $query = DB::table('supplier');
        if (auth()->check() && !empty(auth()->user()->Location)) {
            $companies = $query->where("Location", auth()->user()->Location)->orderBy('Company_Name')->get();
        } else {
            $companies = $query->orderBy('Company_Name')->get();
        }

        $counter = DeliveryNote::whereYear('Delivery_Date', date('Y'))
            ->whereMonth('Delivery_Date', date('m'))
            ->count();
        $months = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
        $romawi = $months[date('n') - 1];
        $DnNo = sprintf('%03d', $counter + 1) . "/DN-GEU/$romawi/" . date('Y');

        return view('cns.export.dn-new', compact('companies', 'DnNo'));
    }

    public function newDeliveryNoteSubmit(Request $request)
    {
        $r = $request->all();
        unset($r['_token']);
        $r['Created_By'] = auth()->user()->username;
        $r['Created_Date'] = now();
        $r['Status'] = 'CREATED';

        DeliveryNote::create($r);

        echo "success";
    }

    public function detailDeliveryNote(Request $request)
    {
        $query = DB::table('supplier');
        if (auth()->check() && !empty(auth()->user()->Location)) {
            $companies = $query->where("Location", auth()->user()->Location)->orderBy('Company_Name')->get();
        } else {
            $companies = $query->orderBy('Company_Name')->get();
        }

        $task = trim((string) $request->input('Task', ''));
        $dn = DeliveryNote::where('DN_No', $task)->first();
        if (!$dn && is_numeric($task)) {
            $dn = DeliveryNote::find($task);
        }

        if ($dn && !empty($dn->Shipper) && !$companies->contains('Supplier_ID', $dn->Shipper)) {
            $extra = DB::table('supplier')->where('Supplier_ID', $dn->Shipper)->first();
            if ($extra) {
                $companies->push($extra);
            }
        }
        if ($dn && !empty($dn->Consignee) && !$companies->contains('Supplier_ID', $dn->Consignee)) {
            $extra = DB::table('supplier')->where('Supplier_ID', $dn->Consignee)->first();
            if ($extra) {
                $companies->push($extra);
            }
        }

        return view('cns.export.dn-detail', compact('companies', 'dn'));
    }

    public function detailDeliveryNoteSubmit(Request $request)
    {
        $r = $request->all();
        unset($r['_token']);

        DeliveryNote::where('DN_No', $r['DN_No'])->update($r);

        echo "success";
    }
}
