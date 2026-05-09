<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MasterSupplierController extends Controller
{
    public function indexMasterSupplier()
    {
        return view('cns.master.supplier');
    }

    public function getMasterSupplierCollection(Request $request)
    {
        return DB::table("supplier")
            ->where("Location", auth()->user()->Location)
            ->when($request->type === 'CONSIGNEE', fn($q) => $q->where('Is_Consignee', 1))
            ->when($request->type === 'SUPPLIER', fn($q) => $q->where('Is_Supplier', 1))
            ->orderBy('Company_Name', 'ASC')
            ->get();
    }

    public function indexMasterSupplierXml(Request $request)
    {
        header("Content-type: text/xml");

        $nomor   = 1;
        $Fetchx  = $this->getMasterSupplierCollection($request);

        echo "<rows>";

        foreach ($Fetchx as $dapat) {
            echo "<row id=\"$dapat->ID\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($dapat->Supplier_ID) . "</cell>";
            echo "<cell>" . htmlspecialchars($dapat->Company_Name) . "</cell>";
            echo "<cell>" . htmlspecialchars($dapat->Is_Supplier ? 'Yes' : 'No') . "</cell>";
            echo "<cell>" . htmlspecialchars($dapat->Is_Supplier  ? 'Yes' : 'No') . "</cell>";
            echo "<cell>" . htmlspecialchars($dapat->Contact_Person) . "</cell>";
            echo "<cell>" . htmlspecialchars($dapat->City) . "</cell>";
            echo "<cell>" . htmlspecialchars($dapat->Phone) . "</cell>";
            echo "<cell>" . htmlspecialchars($dapat->Fax) . "</cell>";
            echo "<cell>" . htmlspecialchars($dapat->Email) . "</cell>";
            echo "<cell>" . htmlspecialchars($dapat->Sales_Name) . "</cell>";
            echo "<cell>" . htmlspecialchars($dapat->Sales_No ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($dapat->Payment) . "</cell>";
            // echo "<cell>" . htmlspecialchars($dapat->Supplier_Category) . "</cell>";
            // echo "<cell>" . htmlspecialchars($dapat->Supplier_Sub_Category) . "</cell>";
            // echo "<cell>" . htmlspecialchars($dapat->Supplier_Group_Category) . "</cell>";
            echo "<cell>" . htmlspecialchars($dapat->Address) . "</cell>";
            echo "<cell>" . htmlspecialchars($dapat->Contact_Person) . "</cell>";
            echo "<cell>" . htmlspecialchars($dapat->Letterhead  === null ? 'Tidak ada' : 'Ada') . "</cell>";
            echo "<cell>" . htmlspecialchars($dapat->Footer      === null ? 'Tidak ada' : 'Ada') . "</cell>";
            echo "<cell>" . htmlspecialchars($dapat->Logo        === null ? 'Tidak ada' : 'Ada') . "</cell>";
            echo "<cell>" . htmlspecialchars($dapat->Logo_Inverse === null ? 'Tidak ada' : 'Ada') . "</cell>";
            echo "<cell>" . htmlspecialchars($dapat->Stamps      === null ? 'Tidak ada' : 'Ada') . "</cell>";

            echo "</row>";

            $nomor++;
        }

        echo "</rows>";
    }

    public function newMasterSupplier()
    {
        return view('cns.master.supplier-new');
    }

    public function newMasterSupplierSubmit(Request $request)
    {

        $param = $request->param;
        $param = explode('&', $param);
        $param = array_filter($param, fn($p) => !str_starts_with($p, 'filter_type='));
        $params = [];
        foreach ($param as $key => $value) {
            $value = explode('=', $value);
            $params[$value[0]] = $value[1];
        }
        $ars = "";
        $ax = "";
        $isi = "";
        $arr = [];
        foreach ($params as $key => $value) {

            if ($key != 'ID' && $key != 'UpdateData') {
                $ars .= "$key = '$value',";
                $ax .= "$key,";
                $isi .= "'$value',";
                $arr[$key] = $value;
            }
        }

        $arr['Is_Supplier']   = $request->input('Is_Supplier',   1);
        $arr['Is_Consginee']  = $request->input('Is_Consginee',  0);
        $arr['Is_Supplier'] = $request->input('Is_Supplier', 0);

        $Saya = Auth::User()->username;
        $LSP = Auth::user()->Location;

        $ars = substr($ars, 0, -1);
        $ax = substr($ax, 0, -1);
        $isi = substr($isi, 0, -1);
        $cekakun = DB::table('supplier')->where('Location', $LSP)->orderBy('ID', 'desc')->count();
        $Supplier_ID = "GEU-COMP-" . ($arr['Is_Supplier'] ? '2' : '1') . sprintf("%04d", $cekakun + 1);

        if ($request->LetterHead != 'undefined') {
            $file = $request->LetterHead;
            $filename = "LetterHead_" . time() . "." . $file->getClientOriginalExtension();
            $file->move(public_path('filesupplier'), $filename);
            $arr['LetterHead'] = "filesupplier/" . $filename;
        }

        if ($request->Footer != 'undefined') {
            $file = $request->Footer;
            $filename = "Footer_" . time() . "." . $file->getClientOriginalExtension();
            $file->move(public_path('filesupplier'), $filename);
            $arr['Footer'] = "filesupplier/" . $filename;
        }

        if ($request->Logo != 'undefined') {
            $file = $request->Logo;
            $filename = "Logo_" . time() . "." . $file->getClientOriginalExtension();
            $file->move(public_path('filesupplier'), $filename);
            $arr['Logo'] = "filesupplier/" . $filename;
        }

        if ($request->Logo_Inverse != 'undefined') {
            $file = $request->Logo_Inverse;
            $filename = "LogoInverse_" . time() . "." . $file->getClientOriginalExtension();
            $file->move(public_path('filesupplier'), $filename);
            $arr['Logo_Inverse'] = "filesupplier/" . $filename;
        }

        if ($request->Stamps != 'undefined') {
            $file = $request->Stamps;
            $filename = "Stamps_" . time() . "." . $file->getClientOriginalExtension();
            $file->move(public_path('filesupplier'), $filename);
            $arr['Stamps'] = "filesupplier/" . $filename;
        }

        $arr['Supplier_ID'] = $Supplier_ID;
        $arr['Status'] = "NORMAL";
        $arr['Created_By'] = $Saya;
        $arr['Location'] = $LSP;

        DB::table('supplier')->insert($arr);

        // DB::insert(DB::raw("INSERT INTO supplier($ax,Status,Supplier_ID,Created_By,Location) VALUES($isi,'NORMAL','$Supplier_ID','$Saya','$LSP')"));
        return "success";
    }

    public function detailMasterSupplier(Request $request)
    {
        $supplier = DB::table("supplier")->where("ID", $request->ID)->first();
        return view('cns.master.supplier-detail', compact('supplier'));
    }

    public function detailMasterSupplierSubmit(Request $request)
    {

        $param = $request->param;
        $param = explode('&', $param);
        $param = array_filter($param, fn($p) => !str_starts_with($p, 'filter_type='));
        $params = [];
        foreach ($param as $key => $value) {
            $value = explode('=', $value);
            $params[$value[0]] = $value[1];
        }
        $ars = "";
        $ax = "";
        $isi = "";
        $arr = [];
        foreach ($params as $key => $value) {

            if ($key != 'ID' && $key != 'UpdateData' && $key != 'LetterHead' && $key != 'Footer' && $key != 'Logo' && $key != 'Logo_Inverse') {
                $ars .= "$key = '$value',";
                $ax .= "$key,";
                $isi .= "'$value',";
                $arr[$key] = $value;
            }
        }
        $Saya = Auth::User()->username;
        $LSP = Auth::user()->Location;

        if ($request->LetterHead != 'undefined') {
            $file = $request->LetterHead;
            $filename = "LetterHead_" . time() . "." . $file->getClientOriginalExtension();
            $file->move(public_path('filesupplier'), $filename);
            $arr['Letterhead'] = "filesupplier/" . $filename;
        }

        if ($request->Footer != 'undefined') {
            $file = $request->Footer;
            $filename = "Footer_" . time() . "." . $file->getClientOriginalExtension();
            $file->move(public_path('filesupplier'), $filename);
            $arr['Footer'] = "filesupplier/" . $filename;
        }

        if ($request->Logo != 'undefined') {
            $file = $request->Logo;
            $filename = "Logo_" . time() . "." . $file->getClientOriginalExtension();
            $file->move(public_path('filesupplier'), $filename);
            $arr['Logo'] = "filesupplier/" . $filename;
        }

        if ($request->Logo_Inverse != 'undefined') {
            $file = $request->Logo_Inverse;
            $filename = "LogoInverse_" . time() . "." . $file->getClientOriginalExtension();
            $file->move(public_path('filesupplier'), $filename);
            $arr['Logo_Inverse'] = "filesupplier/" . $filename;
        }

        if ($request->Stamps != 'undefined') {
            $file = $request->Stamps;
            $filename = "Stamps_" . time() . "." . $file->getClientOriginalExtension();
            $file->move(public_path('filesupplier'), $filename);
            $arr['Stamps'] = "filesupplier/" . $filename;
        }

        $arr['Is_Company']   = $request->input('Is_Company',  1);
        $arr['Is_Supplier']  = $request->input('Is_Supplier',  0);
        $arr['Is_Consignee'] = $request->input('Is_Consignee', 0);

        DB::table('supplier')->where('ID', $params['ID'])->update($arr);

        // DB::insert(DB::raw("INSERT INTO supplier($ax,Status,Supplier_ID,Created_By,Location) VALUES($isi,'NORMAL','$Supplier_ID','$Saya','$LSP')"));

        return "success";
    }
}
