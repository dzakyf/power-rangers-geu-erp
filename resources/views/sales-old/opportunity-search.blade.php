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
$result = DB::select(DB::raw("SELECT * from opportunity WHERE Company_Name LIKE '%$_GET[chars]%' OR  Task_ID LIKE '%$_GET[chars]%' OR Oppor_Subject LIKE '%$_GET[chars]%' LIMIT 20"));

if(count($result)>0){
    foreach($result as $data){
		
				$exp = $data->Acc_No;
				$exps = $exp;
		
		$Description= "Name: ". $data->Company_Name."<br>Account No: ".$data->Account_No."<br>Subject : ".$data->Oppor_Subject."<Br>PIC : ".$data->PIC."<br>PIC Phone : ".$data->PIC_Phone."<br>PIC Function: ".$data->PIC_Function;
		$dt=  $data->Company_Name."|".$data->Account_No."|".$data->Oppor_Subject."|".$data->PIC."|".$data->PIC_Phone."|".$data->PIC_Function;
        $arr[]=array("id" => trim($data->Task_ID), "data" => $data->Oppor_Subject, "thumbnail" => $dt, "description" => $Description);
    }
}

echo json_encode($arr);
?>