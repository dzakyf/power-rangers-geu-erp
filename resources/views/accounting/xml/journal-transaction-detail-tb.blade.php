<?php
	//error_reporting(E_ALL ^ E_NOTICE);
error_reporting(0);
ini_set('memory_limit', '-1');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
  		header("Content-type: application/xhtml+xml"); } else {
  		header("Content-type: text/xml");
	}
	echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");


$LSP = Auth::user()->Location;
$saya = Auth::user()->username;
$nomor = 1;


function cekBookVal($acc){
	
	$QuerySUM = DB::select(DB::raw("SELECT Book_Val from branch_worksheet WHERE  Acc_No='$acc'"));
	
		return $QuerySUM->Book_Val;	
	
}

function Accx($acc){
	
	$QProd = DB::select(DB::raw("SELECT Description from branch_coa WHERE Acc_No='$acc'"));
	
	
	
		return $QProd->Description;	
	
}
$nomor=1;


$Querys = DB::select(DB::raw("SELECT * FROM branch_journal_temp WHERE Task_ID='$_GET[Task_ID]' ORDER BY Account_No"));

foreach ($Querys as $Fetchs){
	$coades = Accx($Fetchs->Account_No);
		
		$coa[$Fetchs->Account_No][$LSP]['Debet'] += $Fetchs->Debet+0;
		$coa[$Fetchs->Account_No][$LSP]['Credit'] += $Fetchs->Credit+0;
		$coa[$Fetchs->Account_No][$LSP]['Ending_Balance'] = ($Fetchs->Debet - $Fetchs->Credit)+0;
		$coa[$Fetchs->Account_No][$LSP]['Net_Mutation'] += ($Fetchs->Debet - $Fetchs->Credit)+0;
		$coa[$Fetchs->Account_No][$LSP]['Net_Mutation2'] += ($Fetchs->Credit - $Fetchs->Debet)+0;
	
}


echo "<rows>";
if ($_GET['objek'] == 'GROUP'){
 $wh = "WHERE r.Listed_OnSearch=0";	
}else{
 $wh = "WHERE r.Listed_OnSearch=1";
}
$Query = DB::select(DB::raw("SELECT r.*,(SELECT Display_Mode FROM branch_worksheet g where g.Acc_No=r.Acc_No LIMIT 1) as liatin from branch_coa r  $wh ORDER BY r.Acc_No"));

foreach ($Query as $Fetch){
	
	$cekbookVal = cekBookVal($Fetch->Acc_No);
	
	if ($cekbookVal != 1){
		if ($Fetch->Normal =='Debit'){
			$endingbalance = $coa[$Fetch->Acc_No][$LSP]['Debet'] - $coa[$Fetch->Acc_No][$LSP]['Credit'];
			$nets = $coa[$Fetch->Acc_No][$LSP]['Net_Mutation']+0;
		}else{
			$nets = $coa[$Fetch->Acc_No][$LSP]['Net_Mutation2']+0;
			$endingbalance = $coa[$Fetch->Acc_No][$LSP]['Credit'] - $coa[$Fetch->Acc_No][$LSP]['Debet'];

		}
	}else{
			$nets = $coa[$Fetch->Acc_No][$LSP]['Net_Mutation2']+0;
			$endingbalance = $coa[$Fetch->Acc_No][$LSP]['Debet'] - $coa[$Fetch->Acc_No][$LSP]['Credit'];
		
	}

		
	echo"
	<row id=\"$Fetch->Acc_No\">
		<cell>".$nomor."</cell>
		<cell style='padding-left:10px'>".htmlspecialchars($Fetch->Acc_No)."</cell>
		<cell style='padding-left:10px'>".htmlspecialchars($Fetch->Description)."</cell>
		<cell>".($coa[$Fetch->Acc_No][$LSP]['Debet']+0)."</cell>
		<cell>".($coa[$Fetch->Acc_No][$LSP]['Credit']+0)."</cell>
		<cell>".($nets)."</cell>
		<cell></cell>
	</row>";
	


$nomor++;
}





echo "</rows>";
?>
