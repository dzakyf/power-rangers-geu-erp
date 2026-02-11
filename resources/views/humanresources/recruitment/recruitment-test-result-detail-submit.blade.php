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


DB::update(DB::raw("UPDATE recruitment set Psikotest_File='$_POST[Psikotest_File]',Psikotest_Note='$_POST[Psikotest_Note]',
Psikotest_Score='$_POST[Psikotest_Score]',
Test_Note='$_POST[Test_Note]',Test_Score='$_POST[Test_Score]',Test_File='$_POST[Test_File]',
Technical_Note='$_POST[Technical_Note]',Technical_Score='$_POST[Technical_Score]',Technical_File='$_POST[Technical_File]'


 WHERE ID=$_POST[ID]"));


echo "success";
?>