<?php
//error_reporting(E_ALL ^ E_NOTICE);
error_reporting(0);
ini_set('memory_limit', '-1');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
	header("Content-type: application/xhtml+xml");
} else {
	header("Content-type: text/xml");
}
echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");



$LSP = Auth::user()->Location;
$DOP = Auth::user()->DOP;
$priv = Auth::user()->Priv_ID;
$Saya = Auth::user()->username;
$nomor = 1;
$tanggal1 = $_GET['Tgl1'];
$tanggal2 = $_GET['Tgl2'];



// $Fetchx = DB::table("invoice")->where("Status1","'APPROVED'")->whereBetween("Inv_Date", ["'$tanggal1'","'$tanggal2'"])->orderBy("ID", "DESC")->get();

$Fetchx = DB::select(DB::raw("select * from `invoice` where `Status` = 'APPROVED' and `Inv_Date` between '$tanggal1' and '$tanggal2' order by `ID` desc"));

echo "<rows>";

foreach ($Fetchx as $Fetch) {

	$rec = 0;
	$otc = 0;
	$rapel = 0;
	$user = '';

	if(strpos($Fetch->Task_ID, "PRF-UNION-") !== false){
		$dataArray = explode(",", $Fetch->Note);
		$namaUser = [];
		foreach($dataArray as $key => $value) {
			$proformaDtl = DB::table("proforma_detail")->select('Termin')->where("Proforma_No",'LIKE',$value)->first();
			$proforma = DB::table("proforma")->select('Periode_Tagihan','Invoice_To','Total')->where("Proforma_No",'LIKE',$value)->first();
			if($proformaDtl->Termin == 'ONE TIME CHARGE') {
				$otc += $proforma->Total;
			} else {
				
				if($proforma->Periode_Tagihan < date("Ym", strtotime($Fetch->Inv_Date))) {
					$rapel += $proforma->Total;
				} else {
					$rec += $proforma->Total;
				}
			}
			if($proforma != null) {
					$namaUser [] = $proforma->Invoice_To;
			}
		}
		$user = implode(",",array_unique($namaUser));

	} else {
		$proformaDtl = DB::table("proforma_detail")->select('Termin')->where("Proforma_No",'LIKE',$Fetch->Task_ID)->first();
		$proforma = DB::table("proforma")->select('Periode_Tagihan','Invoice_To')->where("Proforma_No",'LIKE',$Fetch->Task_ID)->first();
		if($proformaDtl->Termin == 'ONE TIME CHARGE') {
			$otc = $Fetch->Subtotal + $Fetch->mgm_fee;
		} else {
			
			if($proforma->Periode_Tagihan < date("Ym", strtotime($Fetch->Inv_Date))) {
				$rapel = $Fetch->Subtotal + $Fetch->mgm_fee;
			} else {
				$rec = $Fetch->Subtotal + $Fetch->mgm_fee;
			}
		}
		$user = $proforma == null ? 'kosong' : $proforma->Invoice_To;

	}

	
	
	echo "<row id=\"$Fetch->Inv_No\">";
	echo "<cell>" . htmlspecialchars($nomor) . "</cell>";
	echo "<cell>" . htmlspecialchars($user) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Inv_KSPS) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Inv_Date) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Faktur_No) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Invoice_Subject) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->periode_txt) . "</cell>";
	echo "<cell>" . htmlspecialchars('Jasa') . "</cell>";
	echo "<cell>" . htmlspecialchars($rec) . "</cell>";
	echo "<cell>" . htmlspecialchars($otc) . "</cell>";
	echo "<cell>" . htmlspecialchars($rapel) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Subtotal+ $Fetch->mgm_fee) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->PPN) . "</cell>";
	echo "<cell>" . htmlspecialchars($Fetch->Total) . "</cell>";
	echo "</row>";
	$nomor++;
}


echo "</rows>";
