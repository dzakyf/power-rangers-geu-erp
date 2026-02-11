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
$Incoming_Date = $_POST['Incoming_Date'];
$Reference = $_POST['Reference'];
$User = $_POST['User'];
$PONO = $_POST['PO_No'];
$APNO = $_POST['GR_No'];
$Note = $_POST['Note'];
$ars = substr($ars,0,-1);
$isi = substr($isi,0,-1);

			DB::update(DB::raw("UPDATE good_receive SET Remark=CONCAT('$Note <br>',Remark),Status='PROPOSED' WHERE GR_No='$APNO'"));

echo "success";
?>