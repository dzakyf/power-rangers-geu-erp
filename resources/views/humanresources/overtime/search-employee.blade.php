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
$result = DB::select(DB::raw("SELECT * from employment WHERE Name LIKE '%$_GET[chars]%' OR  NIK LIKE '%$_GET[chars]%' LIMIT 20"));

if(count($result)>0){
    foreach($result as $data){
		
		$Description= "Name: ". $data->Name."<br>NIK: ".$data->NIK."<br>Gender : ".$data->Gender."<Br>Birth Date : ".$data->Birth_Date."<br>Position: ".$data->Position."<br>Category: ".$data->Category;
		$arr[]=array("id" => trim($data->NIK), "data" => $data->Name, "thumbnail" => $data->Category."|".$data->Sisa_Cuti."|".$data->Salary_Group, "description" => $Description);
    }
}

echo json_encode($arr);
?>