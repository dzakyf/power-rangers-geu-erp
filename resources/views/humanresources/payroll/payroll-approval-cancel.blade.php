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



$kueri = "DELETE FROM salary_main where Task_ID='$_GET[ID]'";
DB::delete(DB::raw($kueri));
$kueri2 = "DELETE FROM salary where Task_ID='$_GET[ID]'";
DB::delete(DB::raw($kueri2));
$kueri3 = "DELETE FROM salary_lists where Task_ID='$_GET[ID]'";
DB::delete(DB::raw($kueri3));
$update = "UPDATE overtime_lists set Salary_Period=null,Salary_ID=null where Salary_ID='$_GET[ID]'";
DB::update(DB::raw($update));
$update2 = "UPDATE kjk_lists set Salary_Period=null,Salary_ID=null where Salary_ID='$_GET[ID]'";
DB::update(DB::raw($update2));
echo "<script>alert('Cancelation Salary $_GET[ID] success!');location.href='payroll-approval';";
?>