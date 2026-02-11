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
$result = DB::select(DB::raw("SELECT * FROM supplier WHERE (LOWER(Company_Name) LIKE LOWER('%".$_GET['chars']."%') 
OR LOWER(Company_Name) LIKE ('%".$_GET['chars']."%') 
) 
ORDER BY Company_Name ASC LIMIT 20"));

if(count($result)>0){
    foreach($result as $data){
		    $arr[]=array("id" => $data->Supplier_ID, "data" => $data->Company_Name, "thumbnail" => $data->Supplier_ID, "description" => $data->Supplier_ID." , " .$data->Company_Name);
    }
}

echo json_encode($arr);
?>