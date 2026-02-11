<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	
$ars = "";
$ax = "";
$isi = "";
$_POST['Estimated_Revenue'] = str_replace(".","",$_POST['Estimated_Revenue']);
$_POST['Estimated_Cost'] = str_replace(".","",$_POST['Estimated_Cost']);
$_POST['Budget_Available'] = str_replace(".","",$_POST['Budget_Available']);

foreach($_POST as $key=>$value)
{
		
		if ($key !='ID' && $key !='Task_ID' && $key != 'SubmitCustDetail' && $key != 'DelCustDetail' && $key != 'Created_Date' && $key != 'Created_By' && $key != 'Location'&& $key != 'ID'){
			$ars .= "$key = '$value',";
			$ax .="$key,";
			$isi.="'$value',";
		}
}



$ax = substr($ax,0,-1);
$ars = substr($ars,0,-1);
$isi = substr($isi,0,-1);

$cek = DB::table("project_mgt")->where("Source_Reference", 'LIKE', "%$_POST[Source_Reference]%")->orderBy('ID', 'DESC')->first();


if($cek) {
	echo "PO/Opportunity Telah Pernah Di Input";
	exit;
}


$Taskid = 'PM/'.$LSP.'/'.date('Ymdhis').md5($Saya,0,3);
$kueri = "INSERT INTO project_mgt($ax,Created_By,Location,Task_ID) values ($isi,'$Saya','$LSP','$Taskid')";
DB::insert(DB::raw($kueri));

echo "success";
