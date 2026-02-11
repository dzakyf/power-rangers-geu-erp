<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	
if (strlen(trim($_GET['chars'])) < 4) exit;

$arr=array();
$result = DB::select(DB::raw("SELECT a.PO_No_KSPS,a.Supplier,a.Total,a.Total_Paid,a.Currency, c.Company_Name  
FROM purchase_order a 
LEFT JOIN purchase_request b ON b.PR_No = a.PR_No 
LEFT JOIN supplier c ON a.Supplier = c.Supplier_ID  
WHERE a.Closed_Inv!='1' 
AND 
(
LOWER(a.PO_No_KSPS) LIKE LOWER('%".$_GET['chars']."%') 
OR LOWER(c.Company_Name) LIKE ('%".$_GET['chars']."%') 
) 
GROUP BY a.PO_No_KSPS,a.Supplier,a.Total,a.Total_Paid,a.Currency, c.Company_Name   ORDER BY a.Supplier ASC LIMIT 20"));

if(count($result)>0){
    foreach($result as $data){
		    $arr[]=array("id" => "$data->PO_No_KSPS", "data" => "$data->Total", "thumbnail" => "$data->Total_Paid", "description" => $data->PO_No_KSPS." , ". $data->Company_Name);
    }
}

echo json_encode($arr);
?>