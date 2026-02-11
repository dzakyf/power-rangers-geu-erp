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
		
		if ($key !='undefined' && $key !='UploadValues' && $key != 'SubmitCustDetail' && $key != 'DelCustDetail' && $key != ''){
			$ars .= "$key = '$value',";
			$ax .="$key,";
			$isi.="'$value',";
		}
}



$ax = substr($ax,0,-1);
$ars = substr($ars,0,-1);
$isi = substr($isi,0,-1);

$cek = DB::table("salary_main")->where("Task_ID",$_GET['ID'])->first();
$status = $cek->Status;
if ($status == 'CREATED'){
	$nstatus ='PROPOSED';
}elseif ($status == 'PROPOSED'){
	$nstatus ='APPROVAL_VP';
}elseif ($status == 'APPROVAL_VP'){
	$nstatus ='APPROVED';
}

$kueri = "UPDATE salary_main set Status='$nstatus' where Task_ID='$_GET[ID]'";
DB::update(DB::raw($kueri));

echo "<script>alert('Approval Salary $_GET[ID] success!');location.href='payroll-approval';";
?>