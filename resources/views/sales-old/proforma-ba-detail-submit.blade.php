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
$_POST['Subject'] = str_replace("gamp;", "&", $_POST['Subject']);
$_POST['Total'] = str_replace(".", "", $_POST['Total']);
$_POST['Subtotal_PO'] = str_replace(".", "", $_POST['Subtotal_PO']);
$_POST['Man_Fee'] = str_replace(".", "", $_POST['Man_Fee']);

foreach ($_POST as $key => $value) {


	if ($key != 'AddGridDetailPO' && $key != 'DelGridDetailPO'  && $key != 'PPN' && $key != 'Termin' && $key != 'UpdatePO' && $key != 'Project_Type') {




		$ars 	.= "$key,";

		$isi	.= "'$_POST[$key]',";


		$MX .= "$key = '$_POST[$key]',";
	}
}

$ars = substr($ars, 0, -1);
$MX = substr($MX, 0, -1);

DB::update(DB::raw("UPDATE proforma SET Bill_Type='$_POST[Bill_Type]',Subject='$_POST[Subject]',Subtotal_PO='$_POST[Subtotal_PO]',Man_Fee='$_POST[Man_Fee]',Signed_By='$_POST[Signed_By]',Signed_By_Position='$_POST[Signed_By_Position]',
Signed_By2='$_POST[Signed_By2]',Signed_By_Position2='$_POST[Signed_By_Position2]',Signed_By3='$_POST[Signed_By3]',Signed_By_Position3='$_POST[Signed_By_Position3]',Signed_By_KSPS='$_POST[Signed_By_KSPS]',Signed_By_Position_KSPS='$_POST[Signed_By_Position_KSPS]',Periode='$_POST[Periode]',BA_No='$_POST[BA_No]',Total='$_POST[Total]',BA_Date='$_POST[BA_Date]',BA_Status='CREATED',Invoice_To='$_POST[Invoice_To]', Last_Update = NOW(),Periode_Tagihan='$_POST[Periode_Tagihan]' WHERE ID=$_POST[ID_Proforma]"));


echo "success";
