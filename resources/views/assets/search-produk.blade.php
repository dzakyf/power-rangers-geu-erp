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
$result = DB::select(DB::raw("SELECT * from supplies WHERE Product_Code LIKE '%$_GET[chars]%' OR Product_Description LIKE '%$_GET[chars]%'   OR Barcode LIKE '%$_GET[chars]%'   LIMIT 20"));

if(count($result)>0){
    foreach($result as $data){
		
		$Product_Description= "Product Code: ". $data->Product_Code."<br>Name: ".htmlspecialchars($data->Product_Description)."<br>Unit : ".$data->Unit_Name."<Br>Price : ".$data->UnitPrice;
		$arr[]=array("id" => trim($data->Product_Code), "data" => htmlspecialchars($data->Product_Description),"thumbnail" => $data->Barcode, "Product_Description" => $Product_Description);
    }
}

echo json_encode($arr);
?>