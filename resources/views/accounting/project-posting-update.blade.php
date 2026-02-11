<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Http\Fungsi;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;


header("Content-type: text/xml");
echo('<?xml version="1.0" encoding="iso-8859-1"?>'); 


function updatekan($rowId,$param){
	$a = $param[$rowId."_c1"]; // origin
	$b = $param[$rowId."_c2"]; // origin
	
$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;

$jno = "JPM-PO-$rowId-".date("ymdhis");
$oppor = DB::table("opportunity")->where("Task_ID",$a)->first();
$podata = DB::table("opportunity_detail")->where("Task_ID",$a)->first();
$getcust = DB::table("customerz")->where("Customer_No",$oppor->Account_No)->first();
$accrev = $getcust->Acc_No_MF;
$coax  = "112.4.".$accrev;

DB::select(DB::raw("INSERT INTO ho(Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
		Debet, Credit,  User_Resp, Created_By, Created_Date,Department) SELECT
		'$a', Location, '$b',Task_ID, '$podata->PO_No',  Customer_No,Customer_Name, '$jno', '$coax','Piutang Usaha Belum Tertagih', '','','PENGAKUAN PENDAPATAN PO BELUM TERTAGIH :: $podata->Subject',$podata->Total,0,Created_By,'$Saya',NOW(),'BUS' FROM project_mgt where Source_Reference='$a'"));	

$acc ="213.8";
$accname = "Hutang PO Penjualan";
DB::select(DB::raw("INSERT INTO ho(Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
		 Credit, Debet,  User_Resp, Created_By, Created_Date,Department) SELECT
		'$a', Location, '$b', Task_ID, '$podata->PO_No', Customer_No,Customer_Name, '$jno', '$acc','$accname', '','','PENGAKUAN PENDAPATAN PO BELUM TERTAGIH :: $podata->Subject',$podata->Total,0,Created_By,'$Saya',NOW(),'BUS' FROM project_mgt where Source_Reference='$a'"));	

$kueri = "UPDATE project_mgt set Posting=1,Posting_By='$Saya',Posting_Date=NOW() WHERE Source_Reference='$a'";
$kueri = "UPDATE opportunity set Posting=1,Posting_By='$Saya',Posting_Date=NOW() WHERE Task_ID='$a'";

DB::update(DB::raw($kueri));



	
			return "update";	
}


echo "<data>";

$request_body = urldecode(file_get_contents('php://input'));
$Saya = Auth::user()->username;
$LSP = Auth::user()->Location;

$ids = explode("&",$request_body);
foreach($ids as $datax){
	
	$idenya = explode("=",$datax);
	$param[$idenya[0]] = $idenya[1];
	
}
$checkids = explode(",",$param["ids"]);
$Task_ID = $_GET['Task_ID'];

for ($i=0; $i < sizeof($checkids); $i++) { 
	$rowId = $checkids[$i]; //id or row which was updated 
	$newId = $rowId; //will be used for insert operation	
	$mode = $param[$rowId."_!nativeeditor_status"]; 
	$d = $param[$rowId."_c4"]; // transport
	$a = $param[$rowId."_c1"]; // origin
	$nomorx = $param[$rowId."_c0"];

		switch($mode){
		case "updated":
				if ($nomorx == 1 || $nomorx == 'On'){
					$action = updatekan($rowId,$param);
				}else{
					$action = "update";
				}
					break;
		case "deleted":
				break;
		case "inserted":
				break;
		}
		
	echo "<action type='".$action."' sid='".$rowId."' tid='".$newId."'/>";	
}
echo "</data>";

?>