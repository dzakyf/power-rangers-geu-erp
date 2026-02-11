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
$PRApproval = Auth::user()->PR_Approval;
$Saya = Auth::user()->username;
echo "<rows>";


$kueri = DB::select(DB::raw("SELECT * FROM purchase_summary where Status='CLOSED'  ORDER BY ID DESC"));
$nomor = 1;

	foreach($kueri as $Fetch){
		
	$Row_Trx = DB::table("purchase_request")->where("PR_No",$Fetch->PR_No)->first();
	$Row_Pur = DB::table("purchase_order")->where("Task_ID",$Fetch->Task_ID)->first();
	$Row_Usr = DB::table("users")->where("username",$Fetch->User_Trx)->first();
		$namasup = DB::table("supplier")->where("Supplier_ID",$Row_Pur->Supplier)->first();

	
	$datadesc = "";
	$sql_detail = DB::table("purchase_request_dtl")->where("PR_No",$Fetch->PR_No)->get();
	foreach($sql_detail as $row_detail){
		$datadesc .= $row_detail->Product_Desc . ",";
	}
	
	$datadesc = rtrim($datadesc,",");
	
			echo "<row id=\"$Fetch->PO_No\">";
				echo "<cell>$nomor</cell>";
				echo "<cell>".htmlspecialchars(($Row_Pur->PO_No))."</cell>";
				echo "<cell>".htmlspecialchars(($Fetch->PR_No))."</cell>";
				echo "<cell>".htmlspecialchars(($Row_Pur->PO_No_KSPS))."</cell>";
				echo "<cell>".htmlspecialchars(($namasup->Company_Name))."</cell>";
				echo "<cell>".htmlspecialchars(((date("d-m-Y H:i", strtotime($Fetch->Created_Date)))))."</cell>";
				echo "<cell>".htmlspecialchars(($Fetch->Purchase_Category))."</cell>";
				echo "<cell>".htmlspecialchars(($Fetch->Purchase_Sub_Category))."</cell>"; 
				echo "<cell>".htmlspecialchars(($Fetch->Purchase_Type))."</cell>";
				echo "<cell>".htmlspecialchars(($Row_Trx->User_Request_Name))."</cell>";
				echo "<cell>".htmlspecialchars(($Row_Usr->Department))."</cell>";
				echo "<cell>".htmlspecialchars(($Fetch->Status))."</cell>";
				echo "<cell>".htmlspecialchars(($Fetch->Created_By))."</cell>";
				echo "<cell>".htmlspecialchars(($datadesc))."</cell>";
			echo "</row>";
			$nomor++;
	}

echo "</rows>";
?>
