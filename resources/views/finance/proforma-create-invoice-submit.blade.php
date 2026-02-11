<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Http\Custom_Function;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

$roman = [1 => "I", 2 => "II", 3 => "III", 4 => "IV", 5 => "V", 6 => "VI", 7 => "VII", 8 => "VIII", 9 => "IX", 10 => "X", 11 => "XI", 12 => 'XII'];
$ars = "";
$ax = "";
$isi = "";
foreach ($_POST as $key => $value) {

	if ($key != 'ID' && $key != 'SubmitTransfer') {
		$ars .= "$key = '$value',";
		$ax .= "\"$key\",";
		$isi .= "'$value',";
	}
}
$Saya = Auth::User()->username;
$LSP = Auth::user()->Location;

function hitungInv($tgl)
{

	$explod = explode("-", $tgl);
	$thn = substr($explod[0], -2);
	$bln = "00";

	$LSP = Auth::user()->Location;
	//$frcek = DB::select(DB::raw("SELECT Inv_Header  FROM registered_branch  where Location='$LSP'"));
	$frcek = DB::table('registered_branch')->select('Inv_Header')->where('Location', $LSP)->first();

	$headno = $frcek->Inv_Header;

	if ($headno == '') $headno = "99";
	$headinv = $thn . $bln . $headno;

	$jum = DB::table("invoice")->where("Inv_No", "LIKE", "$headinv%")->get();
	$nomors = count($jum) + 4;
	$nomor = $thn . $bln . $headno . sprintf("%'.05d", $nomors);

	$sqlcek = DB::select(DB::raw("SELECT Inv_No FROM invoice WHERE Inv_No='$nomor'"));
	if (count($sqlcek) > 0) {
		return $nomor;
	} else {
		return $nomor;
	}
}

function nomorInvKSPS($tgl)
{
	$jum = DB::table("invoice")->where("Inv_Date", "LIKE", date("Y", strtotime($tgl)) . "%")->orderBy('Inv_KSPS', 'DESC')->first();
	if ($jum) {
		$nomor = explode("/", $jum->Inv_KSPS);
		$no = intval($nomor[0]) + 1;
		return $no;
	} else {
		$no = 1;
		return $no;
	}
}

$proforma = DB::table("proforma")->where("Proforma_No", $_POST['Proforma_No'])->first();

// if ($_POST['Subtotal'] != $proforma->Subtotal_PO) {
// 	echo "Nominal Subtotal Invoice tidak sama dengan Subtotal BA";
// 	exit;
// }

$InvDate = $_POST['Inv_Date'];
$InvNo = hitungInv($InvDate);
$ksps = nomorInvKSPS($InvDate);
$invksps = sprintf('%04d', $ksps) . "/KSPS/INV/" . $roman[date("n", strtotime($InvDate))] . "/" . date("Y");
if ($_POST['PPN'] <= 0) {
	$_POST['Faktur'] = Null;
}
DB::insert(DB::raw("INSERT INTO invoice(Location,Task_ID,Inv_No,Inv_KSPS,Inv_Date,Account_No,Account_Name,Gross_Val,Subtotal,Num_Proforma,PPN,Total,Materai,Created_By,Currency_Symbol,Currency_Value,Currency_BI,Invoice_Category,Stamp_Int,Status,Invoice_Type,TTD_By,Discount,Discount_Val,Invoice_Subject,Faktur_No, periode_txt, mgm_fee,Note,jatuh_tempo) VALUES
('$LSP','$_POST[Proforma_No]','$InvNo','$invksps','$InvDate','$_POST[Account_No]','$_POST[Account_Name]','$_POST[Gross_Val]','$_POST[Subtotal]','1','$_POST[PPN]','$_POST[Total]','$_POST[Materai]','$Saya','IDR','1','1','GENERAL','$custopt[Materai]','UPDATED','GENERAL','$_POST[Signed_By]','$_POST[Discount]','$_POST[Discount_Val]','$_POST[Invoice_Subject]', '$_POST[Faktur]', '$_POST[periode_txt]', '$_POST[mgm_fee]','$_POST[Note]','$_POST[jatuh_tempo]')"));
if (isset($_POST['Note'])) {
	$batch = explode(",", $_POST['Note']);
	foreach ($batch as $key => $value) {
		DB::update(DB::raw("update proforma set Invoice_No='$InvNo' WHERE Proforma_No='$value'"));
	}
} else {
	DB::update(DB::raw("update proforma set Invoice_No='$InvNo' WHERE Proforma_No='$_POST[Proforma_No]'"));
}

if ($_POST['Management_Fee'] > 0) {
	$InvNo = $InvNo + 1;
	$ksps = $ksps + 1;
	$invksps = sprintf('%04d', $ksps) . "/KSPS/INV/" . $roman[date("n", strtotime($InvDate))] . "/" . date("Y");
	if ($_POST['PPN_Fee'] <= 0) {
		$_POST['Faktur_Fee'] = Null;
	}
	DB::insert(DB::raw("INSERT INTO invoice(Location,Task_ID,Inv_No,Inv_KSPS,Inv_Date,Account_No,Account_Name,Gross_Val,Subtotal,Num_Proforma,PPN,Total,Materai,Created_By,Currency_Symbol,Currency_Value,Currency_BI,Invoice_Category,Stamp_Int,Status,Invoice_Type,TTD_By,Invoice_Subject,Faktur_No, periode_txt) VALUES
('$LSP','$_POST[Proforma_No]','$InvNo','$invksps','$InvDate','$_POST[Account_No]','$_POST[Account_Name]','$_POST[Management_Fee]','$_POST[Management_Fee]','1','$_POST[PPN_Fee]','$_POST[Total_Fee]','$_POST[Materai_Fee]','$Saya','IDR','1','1','MANAGEMENT_FEE','$custopt[Materai]','UPDATED','MANAGEMENT_FEE','$_POST[Signed_By]','$_POST[Invoice_Subject]', '$_POST[Faktur_Fee]','$_POST[periode_txt]')"));

	DB::update(DB::raw("update proforma set Management_Fee='$InvNo' WHERE Proforma_No='$_POST[Proforma_No]'"));
}

echo "success";
