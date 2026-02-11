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
$result = DB::select(DB::raw("SELECT City,Province from master_kodepos WHERE City LIKE '%$_GET[chars]%' GROUP BY City,Province order by City,Province LIMIT 20"));

if(count($result)>0){
    foreach($result as $data){
		    $arr[]=array("id" => trim($data->City), "data" => $data->Province, "thumbnail" => $dt, "description" => $data->City." , " $data->Province);
    }
}

echo json_encode($arr);
?>