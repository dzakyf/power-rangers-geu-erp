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
$result = DB::select(DB::raw("SELECT * from opportunity WHERE Source_Reference LIKE '%$_GET[chars]%' OR Company_Name LIKE '%$_GET[chars]%' OR  Task_ID LIKE '%$_GET[chars]%' OR Oppor_Subject LIKE '%$_GET[chars]%' AND  Status = 'CREATED' LIMIT 5"));

if(count($result)>0){
    foreach($result as $data){
		
		$Description= "Name: ". $data->Company_Name."<br>Account No: ".$data->Account_No."<br>Address : ".$data->Address."<Br>City : ".$data->City."<br>Task No : ".$data->Task_ID."<br>Subject: ".$data->Oppor_Subject."<br>Est Revenue: ".$data->Estimated_Rev;
		$DT= $data->Reference."|".number_format($data->Estimated_Rev,0,",",".")."|".$data->Company_Name."|".$data->Address."|".$data->City."|".$data->Expected_Closing_Date."|".$data->Oppor_Subject;
		$arr[]=array("id" => trim($data->Task_ID), "thumbnail" => $DT ,"data" => $data->Account_No, "description" => $Description);
    }
}

echo json_encode($arr);
