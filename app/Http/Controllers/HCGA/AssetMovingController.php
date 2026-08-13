<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssetMovingController extends Controller
{
    public function index()
    {
        return view('humanresources.asset-moving.index');
    }

    public function data()
    {
        if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml"); } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");
        
        $nik = Auth::user()->Emp_ID;
        $saya = Auth::user()->Name;
        $nomor = 1;

        echo "<rows>";

        $Query = DB::table("moving_asset")->where('User_NIP', $nik)->orderBy("DateTrx","ASC")->get();

        foreach($Query as $Fetch){
            $gudangs = DB::table('gudang_list')->whereIn("Gudang_ID", [$Fetch->Move_To, $Fetch->Move_From])->get()->pluck('Description', 'Gudang_ID');
                echo "<row id=\"$Fetch->ID\">";
                echo "<cell>$nomor</cell>";
                echo "<cell>".htmlspecialchars($Fetch->No_Trx)."</cell>";
                echo "<cell>".htmlspecialchars($saya)."</cell>";
                echo "<cell>".htmlspecialchars($Fetch->Department)."</cell>";
                echo "<cell>".htmlspecialchars($Fetch->DateTrx)."</cell>";
                echo "<cell>".htmlspecialchars($Fetch->Asset_No)."</cell>";
                echo "<cell>".htmlspecialchars($Fetch->Product_Description)."</cell>";
                echo "<cell>".htmlspecialchars($Fetch->Asset_Description)."</cell>";
                echo "<cell>".htmlspecialchars($Fetch->Moving_Reason)."</cell>";
                echo "<cell>".htmlspecialchars($Fetch->Moving_Date)."</cell>";
                echo "<cell>".htmlspecialchars($Fetch->Receipent_Name)."</cell>";
                echo "<cell>".htmlspecialchars($Fetch->Receipent_Department)."</cell>";
                echo "</row>";

        $nomor++;
        }

        echo "</rows>";
    }
    
    public function create()
    {
        $assets = DB::table('asset_list')->select(["Asset_No", "Product_Description"])->where('WH_ID', Auth::user()->WH_ID)->get();
        $gudangs = DB::table('gudang_list')->get();
        return view('humanresources.asset-moving.create', compact('assets','gudangs'));
    }

    public function getUserWarehouse(Request $request)
    {
        $us = DB::table('users')->where("WH_ID", $request->wh)->select(['Name','NIK'])->get();

        return response()->json($us);
    }

    public function store(Request $request)
    {
        $Saya = Auth::user()->username;
        $data = [
            'No_Trx' => $request->No_Trx,
            'Position' => $request->Position,
            'DateTrx' => now(),
            'User_NIP' => $request->User_NIP,
            'UserTrx' => $Saya,
            'Asset_No' => $request->Asset_No,
            'Product_Description' => $request->Product_Description,
            'SN' => $request->SN,
            'Asset_Description' => $request->Asset_Description,
            'Last_Stat' => $request->Last_Stat,
            'Current_Location' => $request->Current_Location,
            'Moving_Reason' => $request->Moving_Reason,
            'Moving_Purpose' => $request->Moving_Purpose,
            'New_Location' => $request->New_Location,
            'Moving_Date' => $request->Moving_Date,
            'Receipent_Name' => $request->Receipent_Name,
            'Receipent_NIK' => $request->Receipent_NIK,
            'Receipent_Department' => $request->Receipent_Department,
            'Receipent_Position' => $request->Receipent_Position,
            'Status' => $request->Status,
        ];

        DB::table('asset_moving')->insert($data);

        return response()->json([
            'status' => 'success',
        ], 200);
    }

    public function detail(Request $request)
    {
        $assetMoving = DB::table('asset_moving')->where('ID', $request->ID)->first();
        return view('humanresources.asset-moving.detail', compact('assetMoving'));
    }
}
