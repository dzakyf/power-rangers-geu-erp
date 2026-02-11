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

$statusnya ="INTERVIEWED";

DB::update(DB::raw("UPDATE recruitment SET Salary_Accepted='$_POST[NETT]',Employment_Status='$_POST[Employment_Status]',Status='$statusnya' WHERE ID=$_POST[ID]"));


echo "success";
?>