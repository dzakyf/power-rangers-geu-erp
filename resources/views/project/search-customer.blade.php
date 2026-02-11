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
$result = DB::select(DB::raw("SELECT * from customerz WHERE Company_Name LIKE '%$_GET[chars]%' OR  Customer_No LIKE '%$_GET[chars]%' OR Address LIKE '%$_GET[chars]%' LIMIT 20"));

if(count($result)>0){
    foreach($result as $data){
		
		$Description= "Name: ". $data->Company_Name."<br>Account No: ".$data->Customer_No."<br>Address : ".$data->Company_Name."<Br>City : ".$data->City."<br>PIC Phone : ".$data->Phone."<br>PIC Function: ".$data->PIC_Position;
		$arr[]=array("id" => trim($data->Customer_No), "data" => $data->Company_Name,"thumbnail" => $data->Address, "description" => $Description);
    }
}

echo json_encode($arr);
?>