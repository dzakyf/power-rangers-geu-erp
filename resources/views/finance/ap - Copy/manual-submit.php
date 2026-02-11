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
		
		if ($key !='ID' && $key !='UpdateData' && $key !='Recurring_Day' && $key !='Recurring_Month'){
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
$_POST['Invoice_Val'] = str_replace(".","",$_POST['Invoice_Val']);
$_POST['Invoice_Val'] = str_replace(",","",$_POST['Invoice_Val']);
$_POST['Management_Fee'] = str_replace(".","",$_POST['Management_Fee']);
$_POST['Management_Fee'] = str_replace(",","",$_POST['Management_Fee']);
$_POST['Delivery_Costs'] = str_replace(".","",$_POST['Delivery_Costs']);
$_POST['Delivery_Costs'] = str_replace(",","",$_POST['Delivery_Costs']);
$_POST['DP'] = str_replace(".","",$_POST['DP']);
$_POST['DP'] = str_replace(",","",$_POST['DP']);
$_POST['PPN'] = str_replace(".","",$_POST['PPN']);
$_POST['PPN'] = str_replace(",","",$_POST['PPN']);
$_POST['PPH'] = str_replace(".","",$_POST['PPH']);
$_POST['PPH'] = str_replace(",","",$_POST['PPH']);
$_POST['Subtotal'] = str_replace(".","",$_POST['Subtotal']);
$_POST['Subtotal'] = str_replace(",","",$_POST['Subtotal']);

DB::insert(DB::raw("INSERT INTO account_payable(Task_ID,Location,Department,PIC,Purchase_Category,Purchase_Sub_Category,Purchase_Type,Vendor_ID,Vendor_Name,Reference,Inv_Vendor,PO_References,Description,Subtotal,PPN,PPH,Total,Remain_Val,Status,Created_By,Inv_Type,Due_Date,Date_Received,File_Document,Inv_Vendor_Date,Bank_Name,Bank_Acc,Bank_Acc_Name,Bank_Type,Subject_Payment,PPH_Type,DP,Delivery_Costs,Management_Fee) 
		values('$_POST[AP_No]','$LSP','$_POST[Peruntukan]','$_POST[User_Request_Name]','$getpr->Category','MANUAL INVOICE','MANUAL','$_POST[Supplier_ID]','$_POST[Supplier_Name]','$_POST[Reference]','$_POST[Reference]','$_POST[Description]','".addslashes($_POST['Note'])."','$_POST[Subtotal]','$_POST[PPN]','$_POST[PPH]','$_POST[Invoice_Val]','$_POST[Invoice_Val]','NEED_APPROVE','$Saya','PURCHASE','$_POST[JatuhTempo]','$_POST[Invoice_Received_Date]','$_POST[File_Document]','$_POST[Inv_Vendor_Date]','$_POST[Bank_Name]','$_POST[Bank_Acc]','$_POST[Bank_Acc_Name]','$_POST[Bank_Type]','$_POST[Subject_Payment]','$_POST[PPH_Type]','$_POST[DP]','$_POST[Delivery_Costs]','$_POST[Management_Fee]')"));

echo "success";


?>