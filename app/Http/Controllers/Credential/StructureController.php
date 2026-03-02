<?php

namespace App\Http\Controllers\Credential;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StructureController extends Controller
{
    public function index()
    {
        return view('credential.structure');
    }

    public function indexXml()
    {
        if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml"); } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        $LSP = Auth::user()->Location;
        $DOP = Auth::user()->DOP;
        $priv = Auth::user()->Priv_ID;
        $saya = Auth::user()->username;
        $nomor = 1;
        $Query2 = DB::table("employment")->get();

        foreach($Query2 as $Fetch2){
            $pegawai[$Fetch2->NIK] = $Fetch2;
        }
        echo "<rows>";

        $Query = DB::table("users")->where("Status","ACTIVE")->orderBy("Name","ASC")->get();

        foreach($Query as $Fetch){
            $peg = $pegawai[$Fetch->Emp_ID] ?? '';
            echo "<row id=\"$Fetch->id\" >";
                echo "<cell>$nomor</cell>";
                echo "<cell>".htmlspecialchars($Fetch->username)."</cell>";
                echo "<cell>".htmlspecialchars($peg->Name ?? '')."</cell>";
                echo "<cell>".htmlspecialchars($peg->NIK ?? '')."</cell>";
                echo "<cell>".htmlspecialchars($Fetch->Department)."</cell>";
                echo "<cell>".htmlspecialchars($peg->Category ?? '')."</cell>";
                echo "<cell>".htmlspecialchars($peg->Position ?? '')."</cell>";
                echo "<cell>".htmlspecialchars($peg->Alocation ?? '')."</cell>";
                echo "<cell>".htmlspecialchars($peg->Phone ?? '')."</cell>";
                echo "<cell>".htmlspecialchars($peg->Email ?? '')."</cell>";
                echo "<cell>".htmlspecialchars($peg->Status ?? '')."</cell>";
            echo "</row>";

        $nomor++;
        }

        echo "</rows>";
    }

    public function detail(Request $request)
    {
        $iduser = $request->ID;
        $dm = DB::table("users")->where("id",$iduser)->first();
        return view('credential.structure-detail', compact('dm'));
    }

    public function store(Request $request)
    {
        $iduser = $request->ID;
        $BP_Approval = $request->BP_Approval == 'on' ? 1 : 0;
        $Admin_Chat = $request->Admin_Chat == 'on' ? 1 : 0;

        DB::table("users")->where("id",$iduser)->update([
            "BP_Approval" => $BP_Approval,
            "Admin_Chat" => $Admin_Chat,
        ]);

        echo "success";
        // return response()->json(['message' => 'Credential updated successfully']);
    }
}
