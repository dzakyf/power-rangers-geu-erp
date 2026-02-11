<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	
if (strlen(trim($_GET['chars'])) < 3) exit;

$arr=array();
$result = DB::select(DB::raw("SELECT * from purchase_order WHERE (PO_No_KSPS LIKE '%$_GET[chars]%' OR  PO_NO LIKE '%$_GET[chars]%' OR PR_No LIKE '%$_GET[chars]%') AND Status_Receive='WAITING' LIMIT 20"));

if(count($result)>0){
    foreach($result as $data){
		
		$Description= "PO No: ". $data->PO_No_KSPS."<br>PO Date: ".$data->PO_Date."<br>Category : ".$data->Purchase_Sub_Category."<Br>Total : ".$data->Total."<br>Status PO : ".$data->Status."<br>Note: ".$data->Remark;
		$arr[]=array("id" => trim($data->PO_No), "data" => $data->PO_No_KSPS, "description" => $Description);
    }
}

echo json_encode($arr);
?>