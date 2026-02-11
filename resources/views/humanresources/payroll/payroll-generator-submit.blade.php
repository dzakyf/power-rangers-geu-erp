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



$kueri = "INSERT INTO salary_main (Task_ID, Salary_Period, Total, Category, Department, Use_For, Salary_Group, Note, Status, Created_By, Created_Date, Posting, Posting_By, Location)
SELECT '$_POST[Task_ID]',  '$_POST[Salary_Period]', '$_POST[Total]', '$_POST[Category]', Department, '$_POST[Use_For]', '$_POST[Main_ID]', '$_POST[Note]', 'CREATED', '$Saya', NOW(), 0, '', '$LSP' FROM payrol_main_group WHERE Main_ID='$_POST[Main_ID]'";

DB::insert(DB::raw($kueri));

echo "success";
?>