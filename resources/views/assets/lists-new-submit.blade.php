<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$NIK =  Auth::user()->Emp_ID;
	$Saya =  Auth::user()->username;
	
$ars = "";
$ax = "";
$isi = "";
$arr = [];

foreach($_POST as $key=>$value)
{	
	
    	
			if ($key != 'Task_ID' && $key != 'Created_Date' && $key != 'Created_By' && $key != 'Location' && $key != 'Company_Name'){
				
			
				$ars 	.="$key,";
				
				$isi	.="'$_POST[$key]',";
				
				
				$MX .="$key = $_POST[$key],";
				$arr[$key] = $value;
			
			}					
			

    

}

$prod1 = DB::table("master_product_group")
	->where("Product_Group",$_POST['Asset_Category'])
	->first();
$prod02 = DB::table("master_product_sub_group")
	->where("Product_Group",$_POST['Asset_Category'])
	->where("Sub_Group",$_POST['Asset_Sub_Category'])
	->first();
$prod3 = DB::table("master_product_sub_sub_group")
	->where("Product_Group",$_POST['Asset_Category'])
	->where("Sub_Group",$_POST['Asset_Sub_Category'])
	->where("Sub_Sub_Group",$_POST['Asset_Type'])
	->first();

$P1 = $prod1->Initial;
$P2 = $prod2->Initial;
$P3 = $prod3->Initial;

	
if ($headno == '') $headno = "01";
if ($headno == '00') $headno = "01";	
$gab = "$P1-$P2-$P3-$headno-";
$jmlah = DB::table("asset_list")->where("Asset_No","LIKE","'$gab%'")->get();
$FAX = count($jmlah);
$next = $FAX['jumlah'] + 1;
$assetno = $gab .sprintf("%'04d", $next);
try {
	// $kueri = DB::insert(DB::raw("INSERT INTO asset_list($ars,Asset_No,Location,Created_By) VALUES($vals,'$assetno','$LSP','$Saya')"));
	$arr = array_merge($arr, [
		'Asset_No' => $assetno,
		'Location' => $LSP,
		'Created_By' => $Saya,
	]);
	$kueri = DB::table('asset_lists')->insert($arr);
	if ($kueri){
		echo "success";
	}else{
		echo "error";
	}
} catch (\Throwable $th) {
	Log::error($th);
	echo "error";
}

?>