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
	
    	
			if ($key != 'Task_ID' && $key != 'SubmitCustDetail' && $key != 'Created_By' && $key != 'Location' && $key != 'Company_Name'){
				
			
				$ars 	.="$key,";
				
				$isi	.="'$_POST[$key]',";
				
				
				$MX .="$key = $_POST[$key],";
			
			}					
			

    

}

$ars = substr($ars,0,-1);
$isi = substr($isi,0,-1);

if ($_POST['Category'] == 'KONTRAK AWAL') $_POST['Category_No'] = 0;
if ($_POST['Urutan'] == '') $_POST['Urutan'] = 1;
$kueri = "INSERT INTO contract_lists ($ars,Created_By,Created_Date)VALUES($isi,'$Saya',NOW())";
DB::insert(DB::raw($kueri));

$kuerix = "UPDATE employment set NIK_KSPS='$_POST[NIK_KSPS]',NIK_Client='$_POST[NIK_Client]',Recruitment_Status='',Start_Date='$_POST[Start_Date]',End_Date='$_POST[End_Date]',Contract_No='$_POST[Contract_No]' WHERE NIK='$_POST[NIK]'";
DB::update(DB::raw($kuerix));

echo "success";
?>