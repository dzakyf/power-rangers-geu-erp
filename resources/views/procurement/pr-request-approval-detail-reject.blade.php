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
		
		if ($key !='DelPRNewDetail' && $key !='SubmitPRNewDetail' && $key !='AddPRNewDetail'){
			$ars .= "$key = '$value',";
			$ax .="$key,";
			$isi.="'$value',";
		}
}
$Saya = Auth::User()->username;
$LSP = Auth::user()->Location;

$ars = substr($ars,0,-1);
$ax = substr($ax,0,-1);
$isi = substr($isi,0,-1);
switch($_POST['Status']){
	case "CREATED":
		$nstatus = "CANCELED";
		break;
	case "PROPOSED":
		$nstatus = "CREATED";
		break;
	default:
		$nstatus = 'CANCELED';
		break;
}
DB::update(DB::raw("UPDATE purchase_request SET Note='$_POST[Note]',Reference='$_POST[Reference]',Status='CANCELED' WHERE ID=$_POST[ID]"));

DB::insert(DB::raw("INSERT INTO purchase_log (Location, Task_ID, Category, Sub_Category, Purchase_Type, Transaction, Status, Remark, Date_Trx, User_Trx) VALUES ('$LSP', '$_POST[PR_No]', '$_POST[Category]', '$_POST[Sub_Category]', '$_POST[Supplies_Type]', 'PURCHASE-REQUEST', 'CANCELATION', '$_POST[Note]', NOW(), '$Saya')"));

echo "success";

?>