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
$DOP = Auth::user()->DOP;
$priv = Auth::user()->Priv_ID;
$saya = Auth::user()->username;
$nomor = 1;

	$qmm = DB::table("payrol_group_lists")->where("Main_ID",$_GET['ID'])->orderBy("Group_ID","ASC")->orderBy("Payroll_ID","ASC")->get();
	foreach($qmm as $grupdata){
		$payroll[$grupdata->Group_ID][$grupdata->Payroll_ID]= $grupdata->Amounts;
	}
echo "<rows>";

$Queryx = DB::table("employment")->where("Salary_Group",$_GET['ID'])->orderBy("ID","DESC")->get();

foreach($Queryx as $employee){
	$isidata = "";
	$mdta = DB::table("payrol_main_group_lists")->where("Main_ID",$_GET['ID'])->orderBy("Urutan","ASC")->get();
	foreach($mdta as $hasil){
		$cek = $payroll[$employee->NIK][$hasil->Payroll_ID];
		if (count($cek) == 0){
			
			DB::insert(DB::raw("
	
			INSERT INTO payrol_group_lists (Main_ID,Group_ID, Payroll_ID, Name, Kena_Potongan, Default_PPH, Type_Payroll,  Created_Date, Created_By,Amounts)
			SELECT Main_ID,'$employee->NIK', Payroll_ID, Name, Kena_Potongan, Default_PPH, Type_Payroll, NOW(), '$Saya',0
			from payrol_main_group_lists where ID=$hasil->ID"));
			$isidata .= "<cell>0</cell>";
			
		}else{
			$getid = DB::table("payrol_group_lists")->where("Main_ID",$_GET['ID'])->where("Group_ID",$employee->NIK)->where("Payroll_ID",$hasil->Payroll_ID)->first();
			$adagak = $getid->Amounts+0;
			$isidata .= "<cell>$adagak</cell>";
			
		}
	}
	

	echo "<row id=\"$employee->ID\">";
		echo "<cell>$nomor</cell>";
		echo "<cell>".htmlspecialchars($employee->NIK)."</cell>";
		echo "<cell>".htmlspecialchars($employee->Name)."</cell>";
		echo "<cell>".htmlspecialchars($employee->Gender)."</cell>";
		echo "<cell>".htmlspecialchars($employee->Position)."</cell>";
		echo "<cell>".htmlspecialchars($employee->Work_Area)."</cell>";
		echo "<cell>".htmlspecialchars($employee->Payment_Method)."</cell>";
		echo "<cell>".htmlspecialchars($employee->Payment_Reference)."</cell>";
		echo "<cell>".htmlspecialchars($employee->Payment_Reference2)."</cell>";
		echo "<cell>".htmlspecialchars($employee->PPH_Allowance)."</cell>";
		echo $isidata;
	echo "</row>";
$nomor++;

}


echo "</rows>";
?>
