<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Http\Custom_Function;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

$ars = "";
$ax = "";
$isi = "";
foreach($_POST as $key=>$value)
{
		
		if ($key !='ID' && $key !='SubmitTransfer'){
			if ($key == 'Deposit_Val') $value = str_replace(",","",$value);

			$ars .= "$key = '$value',";
			$ax .="\"$key\",";
			$isi.="'$value',";
		}
}
$_POST['Deposit_Val'] = str_replace(",","",$_POST['Deposit_Val']);

$Saya = Auth::User()->username;
$LSP = Auth::user()->Location;
$dept = Auth::user()->Department;

$ars = substr($ars,0,-1);
$ax = substr($ax,0,-1);
$isi = substr($isi,0,-1);

	DB::insert(DB::raw("INSERT INTO recurring_master (Task_ID, Recurring_ID,Recurring_Type, Recurring_Description, Recurring_Date, Period_Times, Periode, Total, Acc_No, Normal, Created_By, Created_Date, Location) VALUES ('$_POST[Task_ID]', '$_POST[Recurring_ID]','CASH_OUT', '$_POST[Note]', '$_POST[Exec_Date]', '$_POST[Recurring_Times]', '$_POST[periode]', '$_POST[Total]', '$_POST[From_Acc]', 'CREDIT', '$Saya', CURRENT_TIMESTAMP, '$LSP')"));	
	DB::update(DB::raw("UPDATE cash SET Recurring_ID='$_POST[Recurring_ID]' WHERE Cash_No='$_POST[Task_ID]'"));

	
		
echo "success";

?>