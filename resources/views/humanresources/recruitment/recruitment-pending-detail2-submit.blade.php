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
$vals = "";

foreach($_POST as $key=>$value)
{	
	
    	
			if ($key != 'ID' && $key != 'SubmitCustDetail'){
				
			
			
			
				$ars 	.="$key,";
				
				$vals	.="'$_POST[$key]',";
				
				
				$MX .="$key = '$_POST[$key]',";
			
			}					
			

    

}

$vals = substr($vals,0,-1);
$ars = substr($ars,0,-1);
$MX = substr($MX,0,-1);
if ($_POST['Status'] == 'ACCEPT'){
	
	if ($_POST['Recruitment_Type'] == 'PRODUCTION'){
		$statusnya ="BUSINESS_REVIEW";
	}else{
		$statusnya ="INTERVIEWED";
	}
}elseif ($_POST['Status'] == 'REJECT'){
	$statusnya = 'CANCELED';
}else{
	$statusnya = "PENDING";
}

DB::update(DB::raw("UPDATE recruitment set Requisition_ID='$_POST[Position]',Status='CREATED' WHERE ID=$_POST[ID]"));


echo "success";
?>