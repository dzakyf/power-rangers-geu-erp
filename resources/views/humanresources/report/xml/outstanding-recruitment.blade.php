<?php
	//error_reporting(E_ALL ^ E_NOTICE);
error_reporting(0);
ini_set('memory_limit', '-1');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	

	//error_reporting(E_ALL ^ E_NOTICE);
	
	if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
  		header("Content-type: application/xhtml+xml"); } else {
  		header("Content-type: text/xml");
	}
	echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

$femp = DB::table("employment")->where("Salary_Group","!=",'')->get();

 foreach($femp as $employes){
	$pegawai[$employes->NIK]['Task_ID'] = $employes->Task_ID;
	$pegawai[$employes->NIK]['KTP'] = $employes->KTP;
	$pegawai[$employes->NIK]['Salary_Group'] = $employes->Salary_Group;
	$pegawai[$employes->NIK]['Name'] = $employes->Name;
	$pegawai[$employes->NIK]['NPWP'] = $employes->NPWP;
	$pegawai[$employes->NIK]['Birth_Date'] = date("d/m/Y",strtotime($employes->Birth_Date));

 }
 $fkjpb = DB::table("employment_insurances")->where("Salary_Component","!=",'')->get();

 foreach($fkjpb as $finsurance){
	$asuransi[$finsurance->Task_ID][$finsurance->Salary_Component]['Nomor_Kartu'] = $finsurance->Card_No;
	$asuransi[$finsurance->Task_ID][$finsurance->Salary_Component]['Nomor_Polis'] = $finsurance->Polis_No;

 }
  $salgrup = DB::table("payrol_main_group")->get();

 foreach($salgrup as $groupgaji){
	$gaji[$groupgaji->Main_ID]['Name'] = $groupgaji->Group_Name;

 }
$Kota = $_GET["City"];
$Tgl1 = $_GET["Tgl1"];
$Tgl2 = $_GET["Tgl2"];

$nomor = 1;
echo "<rows>";

// $fa = DB::table("project_mgt")->where('Location', $LSP)->where("Status","=",'CREATED')->get();
//  foreach($fa as $fx){


// 	$fetch11 = DB::table("project_detail_requisition")->where('Location', $LSP)->where("Task_ID","=",$fx->Task_ID)->get();
	
// 	foreach($fetch11 as $fxy){
// 		if ($fxy->Req_ID != ''){
		
// 			$fetch = DB::table("requisition")->where('Location', $LSP)->where("Task_ID","=",$fxy->Req_ID)->get();
			
// 			foreach($fetch as $ambil){
			
// 				$fdata = DB::table("recruitment")->where("Requisition_ID","LIKE","'%$ambil->Task_ID%'")->where('Location', $LSP)->get();
// 				$jumlahregister = count($fdata);
				
// 				$fdata = DB::table("recruitment")->where("Requisition_ID","LIKE","'%$ambil->Task_ID%'")->where('Location', $LSP)->where("Status","=","CLOSED")->get();
// 				$jumlahfulfil = count($fdata);

// 					echo "<row id=\"$nomor\">";
// 							echo "<cell>$nomor</cell>";#a
// 							echo "<cell>".htmlspecialchars($ambil->PO_No)."</cell>";#c
// 							echo "<cell>".htmlspecialchars($ambil->Requisition_Type)."</cell>";#d
// 							echo "<cell>".htmlspecialchars($ambil->Task_ID)."</cell>";#d
// 							echo "<cell>".htmlspecialchars($ambil->Job_Position)."</cell>";#d
// 							echo "<cell>".htmlspecialchars($ambil->Qty)."</cell>";#i
// 							echo "<cell>".htmlspecialchars($jumlahregister)."</cell>";#i
// 							echo "<cell>".htmlspecialchars($jumlahfulfil)."</cell>";#i
						
// 						echo "</row>";
						
// 						$nomor++;
// 			}
// 		}else{
// 				echo "<row id=\"$nomor\">";
// 							echo "<cell>$nomor</cell>";#a
// 							echo "<cell>".htmlspecialchars($fx->Reference)."</cell>";#c
// 							echo "<cell>".htmlspecialchars($fx->Department)."</cell>";#d
// 							echo "<cell></cell>";#d
// 							echo "<cell>".htmlspecialchars($fxy->Position)."</cell>";#d
// 							echo "<cell>".htmlspecialchars($fxy->Qty)."</cell>";#i
// 							echo "<cell>".htmlspecialchars(0)."</cell>";#i
// 							echo "<cell>".htmlspecialchars(0)."</cell>";#i
						
// 						echo "</row>";
						
// 						$nomor++;
// 		}
	
// 	}
	
//  }

		
	$fetch = DB::table("requisition")->where('Location', $LSP)->get();
	
	foreach($fetch as $ambil){
	
		$fdata = DB::table("recruitment")->where("Requisition_ID","LIKE","'%$ambil->Task_ID%'")->where('Location', $LSP)->get();
		$jumlahregister = count($fdata);
		
		$fdata = DB::table("recruitment")->where("Requisition_ID","LIKE","'%$ambil->Task_ID%'")->where('Location', $LSP)->where("Status","=","CLOSED")->get();
		$jumlahfulfil = count($fdata);

			echo "<row id=\"$nomor\">";
					echo "<cell>$nomor</cell>";#a
					echo "<cell>".htmlspecialchars($ambil->PO_No)."</cell>";#c
					echo "<cell>".htmlspecialchars($ambil->Requisition_Type)."</cell>";#d
					echo "<cell>".htmlspecialchars($ambil->Task_ID)."</cell>";#d
					echo "<cell>".htmlspecialchars($ambil->Job_Position)."</cell>";#d
					echo "<cell>".htmlspecialchars($ambil->Qty)."</cell>";#i
					echo "<cell>".htmlspecialchars($jumlahregister)."</cell>";#i
					echo "<cell>".htmlspecialchars($jumlahfulfil)."</cell>";#i
				
				echo "</row>";
				
				$nomor++;
	}

echo "</rows>";


?>
