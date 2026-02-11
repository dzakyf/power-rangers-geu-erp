<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$NIK =  Auth::user()->Emp_ID;
	$Saya =  Auth::user()->username;
	
$ars = "";
$ax = "";
$isi = "";

foreach($_POST as $key=>$value)
{	
	
    	
			if ($key != 'Task_ID' && $key != 'Created_Date' && $key != 'Created_By' && $key != 'Location' && $key != 'Company_Name'){
				
			
				$ars 	.="$key,";
				
				$isi	.="'$_POST[$key]',";
				
				
				$MX .="$key = $_POST[$key],";
			
			}					
			

    

}

$ars = substr($ars,0,-1);
$isi = substr($isi,0,-1);

	
 
$kueri = "UPDATE stock_request set Status='PROPOSED' where SR_No='$_POST[SR_No]'";
DB::update(DB::raw($kueri));



echo "success";
?>