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
foreach ($_POST as $key => $value) {

	if ($key != 'ID' && $key != 'UpdateData' && $key != 'Recurring_Day' && $key != 'Recurring_Month') {
		$ars .= "$key = '$value',";
		$ax .= "$key,";
		$isi .= "'$value',";
	}
}
$Saya = Auth::User()->username;
$LSP = Auth::user()->Location;
$getpo = DB::table("purchase_order")->where("PO_No_KSPS", $_POST['PO_No'])->first();
$getpr = DB::table("purchase_request")->where("PR_No", $getpo->PR_No)->first();

$supplier = $_POST['Supplier_ID'];
$suppliername = $_POST['Supplier_Name'];

$ars = substr($ars, 0, -1);
$ax = substr($ax, 0, -1);
$isi = substr($isi, 0, -1);
// $_POST['Invoice_Val'] = str_replace(".", "", $_POST['Invoice_Val']);
// $_POST['Invoice_Val'] = str_replace(",", "", $_POST['Invoice_Val']);
// $_POST['Management_Fee'] = str_replace(".", "", $_POST['Management_Fee']);
// $_POST['Management_Fee'] = str_replace(",", "", $_POST['Management_Fee']);
// $_POST['Delivery_Costs'] = str_replace(".", "", $_POST['Delivery_Costs']);
// $_POST['Delivery_Costs'] = str_replace(",", "", $_POST['Delivery_Costs']);
// $_POST['DP'] = str_replace(".", "", $_POST['DP']);
// $_POST['DP'] = str_replace(",", "", $_POST['DP']);
// $_POST['PPN'] = str_replace(".", "", $_POST['PPN']);
// $_POST['PPN'] = str_replace(",", "", $_POST['PPN']);
// $_POST['PPH'] = str_replace(".", "", $_POST['PPH']);
// $_POST['PPH'] = str_replace(",", "", $_POST['PPH']);
// $_POST['Subtotal'] = str_replace(".", "", $_POST['Subtotal']);
// $_POST['Subtotal'] = str_replace(",", "", $_POST['Subtotal']);
// $_POST['materai'] = str_replace(",", "", $_POST['materai']);
// $_POST['materai'] = str_replace(".", "", $_POST['materai']);

//$_POST['Invoice_Val'] = str_replace(".", "", $_POST['Invoice_Val']);
$_POST['Invoice_Val'] = str_replace(",", "", $_POST['Invoice_Val']);
//$_POST['Management_Fee'] = str_replace(".", "", $_POST['Management_Fee']);
$_POST['Management_Fee'] = str_replace(",", "", $_POST['Management_Fee']);
//$_POST['Delivery_Costs'] = str_replace(".", "", $_POST['Delivery_Costs']);
$_POST['Delivery_Costs'] = str_replace(",", "", $_POST['Delivery_Costs']);
//$_POST['DP'] = str_replace(".", "", $_POST['DP']);
$_POST['DP'] = str_replace(",", "", $_POST['DP']);
//$_POST['PPN'] = str_replace(".", "", $_POST['PPN']);
$_POST['PPN'] = str_replace(",", "", $_POST['PPN']);
//$_POST['PPH'] = str_replace(".", "", $_POST['PPH']);
$_POST['PPH'] = str_replace(",", "", $_POST['PPH']);
//$_POST['Subtotal'] = str_replace(".", "", $_POST['Subtotal']);
$_POST['Subtotal'] = str_replace(",", "", $_POST['Subtotal']);
$_POST['materai'] = str_replace(",", "", $_POST['materai']);
//$_POST['materai'] = str_replace(".", "", $_POST['materai']);
DB::insert(DB::raw("update account_payable set Department='$_POST[Peruntukan]',PIC='$getpr->User_Request_Name',Purchase_Category='$getpr->Category',Purchase_Sub_Category='$getpr->Sub_Category',Purchase_Type='$getpr->Supplies_Type',Vendor_ID='$supplier',Vendor_Name='$suppliername',Reference='$_POST[PO_No]',Inv_Vendor='$_POST[Reference]',PO_References='$getpo->Remark',Description='" . addslashes($_POST['Note']) . "',Subtotal='$_POST[Subtotal]',Total='$_POST[Invoice_Val]',Remain_Val='$_POST[Invoice_Val]',Status='PROPOSED',Due_Date='$_POST[JatuhTempo]',Date_Received='$_POST[Invoice_Received_Date]',File_Document='$_POST[File_Document]',Inv_Vendor_Date='$_POST[Inv_Vendor_Date]',Bank_Name='$_POST[Bank_Name]',Bank_Acc='$_POST[Bank_Acc]',Bank_Acc_Name='$_POST[Bank_Acc_Name]',Bank_Type='$_POST[Bank_Type]',Subject_Payment='$_POST[Subject_Payment]',PPN='$_POST[PPN]',PPH='$_POST[PPH]',PPH_Type='$_POST[PPH_Type]',DP='$_POST[DP]',Delivery_Costs='$_POST[Delivery_Costs]',Management_Fee='$_POST[Management_Fee]',materai='$_POST[materai]', Inv_Type='$_POST[tipeInv]' WHERE ID=$_POST[ID]"));

// $getupper = DB::table("organization_structure")->where("Level3",1)->where("Department","GEA")->first();
// $linkkey = md5(date("Ym")."789213123122122");
// $subjek = "IMPORTANT : APPROVAL PERMINTAAN PEMBAYARAN PURCHASE ORDER $_POST[Reference] # , Request By : $Saya";
// 		$badan = "<div style=\"font-family:arial;font-size:12px;\">Kepada Yth.<br>Bapak/Ibu <b>$getupper->Name</b><Br><br>

// 					Mohon dilakukan approval untuk Permintaan pembayaran purchase order tersebut. persetujuan dapat dilakukan melalui system ERP pada menu Procurement -> Contract and Invoice Vendor -> Receive Invoice<br><br>

// 					No PO : $_POST[Reference]<br><br>
// 					Vendor : $supplier<br><br>
// 					Invoice Vendor : $_POST[Inv_Vendor]<br><br>
// 					Nilai : $_POST[Total]<br><br>
// 					Note : $_POST[Note]<br>

// 					<br><br>
// 					Anda dapat melakukan approval melalui link berikut ini :<a href=\'https://erp.ksps.co.id/erpksps/public/approvalreceiveinvoice?ID=$_POST[ID]&linkkey=$linkkey&saya=$Saya\' >KLIK DISINI</a><br><br>

// 					Terima kasih<br>
// 					GREEN ENERGI UTAMA

// 		</div>";	
// 	DB::insert(DB::raw("INSERT INTO mail_logs(Email_To,Subject,Contents,Status,Inserted_Date,Send_Date) VALUES('$getupper->Email','$subjek','$badan','UNSENT',NOW(),NOW())"));

echo "success";
