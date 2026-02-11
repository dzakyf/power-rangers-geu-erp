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

foreach($_POST as $key=>$value)
{	
	
    	
			if ($key != 'AddGridDetailPO' && $key != 'DelGridDetailPO'  && $key != 'PPN' && $key != 'Termin'&& $key != 'UpdatePO'&& $key != 'Project_Type'){
				
			
			
			
				$ars 	.="$key,";
				
				$isi	.="'$_POST[$key]',";
				
				
				$MX .="$key = '$_POST[$key]',";
			
			}					
			

    

}

$ars = substr($ars,0,-1);
$MX = substr($MX,0,-1);

DB::update(DB::raw("UPDATE opportunity SET $MX WHERE Task_ID='$taskid'"));


echo "success";
?>