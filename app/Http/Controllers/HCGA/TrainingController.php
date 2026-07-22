<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TrainingController extends Controller
{
    public function index()
    {
        return view('humanresources.training.training-index');
    }

    public function indexXml()
    {
        if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml"); } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        $nomor = 1;

        echo "<rows>";

        $workCalendarShift = DB::table('training_hcga')
            ->get();

        foreach($workCalendarShift as $Fetch){
            if ($Fetch->Status == 'APPROVED') {
                //
            }
            echo "<row id=\"$Fetch->ID\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>".htmlspecialchars($Fetch->Training_Name)."</cell>";
            echo "<cell>".htmlspecialchars($Fetch->Training_Category)."</cell>";
            echo "<cell>".htmlspecialchars($Fetch->Training_Type)."</cell>";
            echo "<cell>".htmlspecialchars($Fetch->Training_Method)."</cell>";
            echo "<cell>".htmlspecialchars($Fetch->Training_Description)."</cell>";
            echo "<cell>".htmlspecialchars($Fetch->Training_Objective)."</cell>";
            echo "<cell>".htmlspecialchars($Fetch->Training_Competence)."</cell>";
            echo "<cell>".htmlspecialchars($Fetch->Start_Date)."</cell>";
            echo "<cell>".htmlspecialchars($Fetch->End_Date)."</cell>";
            echo "<cell>".htmlspecialchars($Fetch->Total_Cost)."</cell>";
            echo "<cell>".htmlspecialchars($Fetch->Currency)."</cell>";
            echo "<cell>".htmlspecialchars($Fetch->Status)."</cell>";
            echo "</row>";

            $nomor++;
        }

        echo "</rows>";
    }

    public function new()
    {
        $LSP = Auth::user()->Location;
        $Saya =  Auth::user()->username;
        $currency = DB::table('currency')->get();
        $trCategory = DB::table('master_training_hcga_budget')->get();
        $counter = DB::table('training_hcga')->where("Created_Date", 'LIKE', date("Y"). '%')->count() + 1;
        $Task_No = "TRN/" . $LSP . "/" . substr(md5($Saya), 0,5) . '/' . sprintf("%04d", $counter);
        
        return view('humanresources.training.training-new', compact('currency', 'trCategory', 'Task_No'));
    }

    public function newSubmit(Request $request)
    {
        $r = $request->all();
        unset($r['_token']);
        $r['Start_Date'] = date("Y-m-d", strtotime($r['Start_Date']));
        $r['End_Date'] = date("Y-m-d", strtotime($r['End_Date']));
        $r['Created_Date'] = now();
        $r['Created_By'] = auth()->user()->username;
        
        DB::table('training_hcga')->insert($r);

        echo "success";
    }

    public function detail(Request $request)
    {
        $currency = DB::table('currency')->get();
        $trCategory = DB::table('master_training_hcga_budget')->get();
        $trainign = DB::table('training_hcga')->where("ID", $request->ID)->first();
        
        
        return view('humanresources.training.training-detail', compact('currency', 'trCategory', 'training'));
    }

    public function detailSubmit(Request $request)
    {
        $r = $request->all();
        unset($r['_token']);
        unset($r['ID']);
        $r['Start_Date'] = date("Y-m-d", strtotime($r['Start_Date']));
        $r['End_Date'] = date("Y-m-d", strtotime($r['End_Date']));
        $r['Updated_Date'] = now();
        $r['Updated_By'] = auth()->user()->username;
        
        DB::table('training_hcga')->where('ID', $request->ID)->update($r);

        echo "success";
    }

    public function newUpdate(Request $request)
    {
        //
    }

    public function approval()
    {
        return view('humanresources.training.training-index');
    }

    public function approvalXml()
    {
        //
    }
}
