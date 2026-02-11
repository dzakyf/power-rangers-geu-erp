<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Http\Custom_Function;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

$src = $_POST['data'];
$LSP = Auth::user()->Location;
$saya =  Auth::user()->username;
$Nama =  Auth::user()->Name;

function CekVendorWin($ven, $taskid)
{
	$vendor = "V" . $ven . "_Win";
	$TaskID = $_POST['TaskID'];
	// $cekV = DB::select(DB::raw("SELECT COUNT(ID) AS jumlah FROM purchase_summary_detail WHERE PR_Related='$taskid' AND Task_ID='$TaskID' AND $vendor='1'"));

	$cekV  = DB::table('purchase_summary_detail')->selectRaw("count(ID) as jumlah")
		->where('PR_Related', '=', "$taskid")
		->where('Task_ID', '=', "$TaskID")
		->where("$vendor", 1)->get();
	foreach ($cekV as $key) {
		$jum = $key->jumlah;
	}


	$rowV = $jum;
	if ($rowV >= 1) {
		$jumlah = 1;
	} else {
		$jumlah = 0;
	}
	return $jumlah;
}


$taskid = $_POST['ID'];
$TaskID = $_POST['TaskID'];


$src = rtrim($src, "@");
$trx = explode("@", $src);
foreach ($trx as $v) {
	list($no, $code, $proposed, $systemprice, $price1, $total1, $rec1, $price2, $total2, $rec2, $price3, $total3, $rec3, $price4, $total4, $rec4, $price5, $total5, $rec5, $idpr, $desc) = explode("#", $v);
	$systemprice += 0;
	$totalnya = $total1 + $total2 + $total3 + $total4 + $total5;
	DB::update(DB::raw(str_replace("NaN", "", "UPDATE purchase_summary_detail
				SET Default_Price='$systemprice', Proposed='$proposed',
				V1_Propose='$price1',V1_TOP='$total1',V1_Win='$rec1',
				V2_Propose='$price2',V2_TOP='$total2',V2_Win='$rec2',
				V3_Propose='$price3',V3_TOP='$total3',V3_Win='$rec3',
				V4_Propose='$price4',V4_TOP='$total4',V4_Win='$rec4',
				V5_Propose='$price5',V5_TOP='$total5',V5_Win='$rec5',
				Recommended_Spec='" . addslashes($desc) . "',Specification='" . addslashes($desc) . "' 
				WHERE PR_Related='$idpr' AND ID=$no")));
}



DB::update(DB::raw("UPDATE purchase_request SET Processing_Status='$nstatus',Processing_Date=NOW(),PO_Status='$nstatus' WHERE PR_No='$taskid'"));


//$rows = DB::select(DB::raw("SELECT Purchase_Category FROM purchase_summary WHERE PR_No='$taskid'"));

$rows = DB::table('purchase_summary')->select("Purchase_Category")
	->where('PR_No', '=', "$taskid")
	->first();


$tb = " AND Task_ID='$TaskID'";
DB::insert(DB::raw("INSERT INTO purchase_log(Location,Task_ID,Category,Transaction,Status,User_Trx)values('$LSP','$TaskID','" . $rows->Purchase_Category . "','PO Processing','$nstatus','$saya')"));
echo "success";
