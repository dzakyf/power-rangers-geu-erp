<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Http\Fungsi;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;


header("Content-type: text/xml");
echo('<?xml version="1.0" encoding="iso-8859-1"?>'); 

function decimalHours($time)
{
    $hms = explode(":", $time);
    return ($hms[0] + ($hms[1]/60) + ($hms[2]/3600));
}

function updatekan($rowId,$param) {
	
	$ax = $param[$rowId."_c0"]; // origin
	$a = $param[$rowId."_c1"]; // origin
	$b = $param[$rowId."_c2"]; // destination
	$c = $param[$rowId."_c3"]; // destination name
	$d = $param[$rowId."_c4"]; // transport
	$e = $param[$rowId."_c5"]; // service
	$f = $param[$rowId."_c6"]; // treshold
	$g = $param[$rowId."_c7"]; // unit price
	$h = $param[$rowId."_c8"]; // additional
	$ix = $param[$rowId."_c9"]; // lead time
	$j = $param[$rowId."_c10"]; // lead time
	$k = $param[$rowId."_c11"]; // lead time
	$l = $param[$rowId."_c12"]; // lead time
	$m = $param[$rowId."_c13"]; // lead time
	$n = $param[$rowId."_c14"]; // lead time
	$o = $param[$rowId."_c15"]; // lead time
	$p = $param[$rowId."_c16"]; // lead time
	$q = $param[$rowId."_c17"]; // lead time
	$salgrup = $param[$rowId."_c18"]; // lead time
	
	$Saya = Auth::user()->username;
	$LSP = Auth::user()->Location;
	$holiday = 0;
	$dx = DB::table("employment")->where("NIK",$b)->first();
	$salgroup = $dx->Salary_Group;
	$jumlahuang = 0;
	$qxx = DB::table("payrol_group_lists")->where("Main_ID",$salgroup)->where("Group_ID",$b)->where("Kena_Potongan",1)->get();
	foreach($qxx as $mx){
		$jumlahuang += $mx->Amounts;
	}
	$rate = ceil($jumlahuang / 173);
	$hari = 0;
	$libur = DB::table("libur")->where("libur_tgl",$d)->first();
			$time = new DateTime($d." ".$f);
			$timeStop = new DateTime($e." ".$g);
			$diff = $timeStop->diff($time);
			if ($f != '00:00' && $g != '00:00'){
				$dec1 = ($diff->h) + ($diff->i/60)+ ($diff->s/3600) ;
				$hari = $diff->d * 24;
			}else{
				$dec1 = 0;
			}
			$time2 = new DateTime($d." ".$h);
			$timeStop2 = new DateTime($e." ".$ix);
			$diff2 = $timeStop2->diff($time2);
			if ($h != '00:00' && $ix != '00:00'){
				$dec2 = ($diff2->h) + ($diff2->i/60)+ ($diff2->s/3600) ;
				$hari = $diff2->d * 24;
			}else{
				$dec2 = 0;
			}
	//	echo json_encode($diff2);exit;		
		$decimalHours = $hari+$dec1+$dec2;
			
	$jam1 = 0;
	$jam2 = 0 ;
	$jam3 = 0;
	$jam4 = 0;
	
	if ($libur){
		$holiday = 1;
	}else{
		$nomorhari  = date("N",strtotime($d));
		if ($nomorhari == 6 || $nomorhari == 7){
			
			$holiday = 1;
			if ($decimalHours >= 10){
				
				$jam1 = 8;
				$jam2 = 1;
				$jam3 = $decimalHours - 9;
				$jumlahjam = ($jam1*2) + ($jam2*3) + ($jam3 * 4);
			}elseif ($decimalHours > 9 && $decimalHours < 10){
				
				$jam1 = 8;
				$jam2 = 1;
				$jam3 = $decimalHours - 9;
				$jumlahjam = ($jam1*2) + ($jam2*3) + ($jam3*4);
			}elseif ($decimalHours == 9){
				
				$jam1 = 8;
				$jam2 = 1;
				$jam3 = 0;
				$jumlahjam = ($jam1*2) + ($jam2*3) + ($jam3*4);
			}elseif ($decimalHours > 8 && $decimalHours < 9){
				
				$jam1 = 8;
				$jam2 = $decimalHours - 8;
				$jumlahjam = ($jam1*2) + ($jam2*3);
			
			}elseif ($decimalHours == 8){
				
				$jam1 = 8;
				$jam2 = 0;
				$jam3 = 0;
				$jumlahjam = ($jam1*2) + ($jam2*3) + ($jam3*4);
				
			 }else{
				 $jam1 = $decimalHours;
				 $jumlahjam = $jam1 * 8;
			 }			

		}else{
			
			 if ($decimalHours > 1){
				 $jam1 = 1;
				 $jam2 = $decimalHours - 1;
				 $jumlahjam = ($jam1 * 1.5) + ($jam2 * 2);
			 }else{
				 $jam1 = $decimalHours;
				 $jumlahjam = $jam1 * 1.5;
			 }
		}
	}
		$o = $rate * $jumlahjam;

		
			$SubmitApproval = 0;
		
	DB::update(DB::raw("UPDATE overtime_lists set Start_Date='$d',End_Date='$e',Early_IN='$f'
		,Early_OUT='$g'
		,After_IN='$h'
		,After_OUT='$ix'
		,Holiday = '$holiday'
		,Total_OT = '$decimalHours'
		,Calculated_OT = '$jumlahjam'
		,IS_Outdated = '$rate'
		,Salary_Period = '$n'
		,Amounts = '$o'
		,Reference = '$p'
		,Note = '$q'
		,Level1 = '$jam1'
		,Level2 = '$jam2'
		,Level3 = '$jam3'
		,Level4 = '$jam4'
		,Submit_Approval = $SubmitApproval
	where ID=$rowId"));
			


	
	return "update";
}
function deletekan($rowId,$param){
	DB::delete(DB::raw("DELETE FROM overtime_lists where ID=$rowId")); 	
}
function insertkan($rowId,$param){
	
	$ax = $param[$rowId."_c0"]; // origin
	$a = $param[$rowId."_c1"]; // origin
	$b = $param[$rowId."_c2"]; // destination
	$c = $param[$rowId."_c3"]; // destination name
	$d = $param[$rowId."_c4"]; // transport
	$e = $param[$rowId."_c5"]; // service
	$f = $param[$rowId."_c6"]; // treshold
	$g = $param[$rowId."_c7"]; // unit price
	$h = $param[$rowId."_c8"]; // additional
	$ix = $param[$rowId."_c9"]; // lead time
	$j = $param[$rowId."_c10"]; // lead time
	$k = $param[$rowId."_c11"]; // lead time
	$l = $param[$rowId."_c12"]; // lead time
	$m = $param[$rowId."_c13"]; // lead time
	$n = $param[$rowId."_c14"]; // lead time
	$o = $param[$rowId."_c15"]; // lead time
	$p = $param[$rowId."_c16"]; // lead time
	$q = $param[$rowId."_c17"]; // lead time
	$salgrup = $param[$rowId."_c18"]; // lead time

	$Saya = Auth::user()->username;
	$LSP = Auth::user()->Location;
	$holiday = 0;
	$dx = DB::table("employment")->where("NIK",$b)->first();
	$salgroup = $dx->Salary_Group;
	$jumlahuang = 0;
	$qxx = DB::table("payrol_group_lists")->where("Main_ID",$salgroup)->where("Group_ID",$b)->where("Kena_Potongan",1)->get();
	foreach($qxx as $mx){
		$jumlahuang += $mx->Amounts;
	}
	$rate = ceil($jumlahuang / 173);
	$hari = 0;
	$libur = DB::table("libur")->where("libur_tgl",$d)->first();
			$time = new DateTime($d." ".$f);
			$timeStop = new DateTime($e." ".$g);
			$diff = $timeStop->diff($time);
			if ($f != '00:00' && $g != '00:00'){
				$dec1 = ($diff->h) + ($diff->i/60)+ ($diff->s/3600) ;
				$hari = $diff->d * 24;
			}else{
				$dec1 = 0;
			}
			$time2 = new DateTime($d." ".$h);
			$timeStop2 = new DateTime($e." ".$ix);
			$diff2 = $timeStop2->diff($time2);
			if ($h != '00:00' && $ix != '00:00'){
				$dec2 = ($diff2->h) + ($diff2->i/60)+ ($diff2->s/3600) ;
				$hari = $diff2->d * 24;
			}else{
				$dec2 = 0;
			}
	//	echo json_encode($diff2);exit;		
		$decimalHours = $hari+$dec1+$dec2;
			
	$jam1 = 0;
	$jam2 = 0 ;
	$jam3 = 0;
	$jam4 = 0;
	
	if ($libur){
		$holiday = 1;
	}else{
		$nomorhari  = date("N",strtotime($d));
		if ($nomorhari == 6 || $nomorhari == 7){
			
			$holiday = 1;
			if ($decimalHours >= 10){
				
				$jam1 = 8;
				$jam2 = 1;
				$jam3 = $decimalHours - 9;
				$jumlahjam = ($jam1*2) + ($jam2*3) + ($jam3 * 4);
			}elseif ($decimalHours > 9 && $decimalHours < 10){
				
				$jam1 = 8;
				$jam2 = 1;
				$jam3 = $decimalHours - 9;
				$jumlahjam = ($jam1*2) + ($jam2*3) + ($jam3*4);
			}elseif ($decimalHours == 9){
				
				$jam1 = 8;
				$jam2 = 1;
				$jam3 = 0;
				$jumlahjam = ($jam1*2) + ($jam2*3) + ($jam3*4);
			}elseif ($decimalHours > 8 && $decimalHours < 9){
				
				$jam1 = 8;
				$jam2 = $decimalHours - 8;
				$jumlahjam = ($jam1*2) + ($jam2*3);
			
			}elseif ($decimalHours == 8){
				
				$jam1 = 8;
				$jam2 = 0;
				$jam3 = 0;
				$jumlahjam = ($jam1*2) + ($jam2*3) + ($jam3*4);
				
			 }else{
				 $jam1 = $decimalHours;
				 $jumlahjam = $jam1 * 8;
			 }			

		}else{
			
			 if ($decimalHours > 1){
				 $jam1 = 1;
				 $jam2 = $decimalHours - 1;
				 $jumlahjam = ($jam1 * 1.5) + ($jam2 * 2);
			 }else{
				 $jam1 = $decimalHours;
				 $jumlahjam = $jam1 * 1.5;
			 }
		}
	}
	
	$o = $rate * $jumlahjam;
	if ($ax !='SB'){
		$SubmitApproval = 2;
	}else{
		$SubmitApproval = 1;
	}
	DB::insert(DB::raw("INSERT INTO overtime_lists (Task_ID, NIK, Name,Start_Date, End_Date, Early_IN, Early_OUT, After_IN, After_OUT, Holiday, Total_OT, Calculated_OT, IS_Outdated, Salary_Period, Amounts, Reference,Created_Date, Created_By, Note,Level1,Level2,Level3,Level4,Submit_Approval,Salary_Group) VALUES('$a','$b','$c','$d','$e','$f','$g','$h','$ix','$holiday','$decimalHours','$jumlahjam','$rate','$n','$o','$p',NOW(),'$Saya','$q','$jam1','$jam2','$jam3','$jam4','$SubmitApproval','$salgroup')"));


	
	
	return "insert";
	
}



echo "<data>";

$request_body = urldecode(file_get_contents('php://input'));
$Saya = Auth::user()->username;
$LSP = Auth::user()->Location;

$ids = explode("&",$request_body);
foreach($ids as $datax){
	
	$idenya = explode("=",$datax);
	$param[$idenya[0]] = $idenya[1];
	
}
$checkids = explode(",",$param["ids"]);
$sbmit = "";
for ($i=0; $i < sizeof($checkids); $i++) { 
	$rowId = $checkids[$i]; //id or row which was updated 
	$newId = $rowId; //will be used for insert operation	
	$mode = $param[$rowId."_!nativeeditor_status"]; 
		$d = $param[$rowId."_c4"]; // transport
		$a = $param[$rowId."_c0"]; // origin
		
		switch($mode){
			case "updated":
				$action = updatekan($rowId,$param);break;
		case "deleted":
				$action = deletekan($rowId,$param);break;
		case "inserted":
				$action = insertkan($rowId,$param);break;
		}
		
	echo "<action type='".$action."' sid='".$rowId."' tid='".$newId."'/>";	
}
echo "</data>";



?>