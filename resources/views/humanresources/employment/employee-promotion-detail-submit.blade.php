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
	
    	
			if ($key != 'gantifoto' && $key != 'SimpanNewPEg' && $key != 'ID' && $key != 'Location' && $key != 'Company_Name'){
				
			
				$ars 	.="$key,";
				
				$isi	.="'$_POST[$key]',";
				
				
				$MX .=" $key = '$value',";
			
			}					
			

    

}

$MX = substr($MX,0,-1);
$isi = substr($isi,0,-1);


$kueri = "UPDATE employment set $MX WHERE ID=$_POST[ID]";

DB::update(DB::raw($kueri));


echo "success";
?>