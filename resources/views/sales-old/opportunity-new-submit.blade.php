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
$Task_ID = 'OPPT/'.$LSP.'/'.date('Ymdhis').substr(md5($Saya),0,3);

$_POST['Estimated_Rev'] = str_replace(",","",$_POST['Estimated_Rev']);
foreach($_POST as $key=>$value)
{
		
		if ($key !='ID' && $key !='Task_ID' && $key != 'SubmitCustDetail' && $key != 'DelCustDetail' && $key != 'Created_Date' && $key != 'Created_Date' && $key != 'Location'&& $key != 'ID'){
			$ars .= "$key = '$value',";
			$ax .="$key,";
			$isi.="'$value',";
		}
}



$ax = substr($ax,0,-1);
$ars = substr($ars,0,-1);
$isi = substr($isi,0,-1);


$kueri = "INSERT INTO opportunity($ax,Location,Created_Date,Task_ID) values ($isi,'$LSP',NOW(),'$Task_ID')";
DB::insert(DB::raw($kueri));

echo "success";
?>