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
if ($_POST['Interview_Status'] == 'ACCEPT'){

	$statusnya ="INTERVIEWED";
	
}elseif ($_POST['Interview_Status'] == 'PENDING'){
	$statusnya = 'PENDING';
}else{
	$statusnya = $_POST['Interview_Status'];
}
$statusnya ="INTERVIEWED";

DB::update(DB::raw("UPDATE recruitment set Interview_File='$_POST[Interview_File]',Interview_By='$Saya',Interview_Date='$_POST[Interview_Date]',Interview_Result='$_POST[Interview_Result]',Interview_Status='$_POST[Interview_Status]',Interview_Note='$_POST[Interview_Note]',Status='$statusnya' WHERE ID=$_POST[ID]"));


echo "success";
?>