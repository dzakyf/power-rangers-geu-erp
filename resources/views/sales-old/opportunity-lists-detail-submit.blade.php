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

foreach ($_POST as $key => $value) {


	if ($key != 'AddGridDetailPO' && $key != 'DelGridDetailPO' && $key != 'UpdatePO' && $key != 'ID') {




		$ars 	.= "$key,";

		$isi	.= "'$_POST[$key]',";


		$MX .= "$key = '$_POST[$key]',";
	}
}

$ars = substr($ars, 0, -1);
$isi = substr($isi, 0, -1);
$MX = substr($MX, 0, -1);

$_POST['Subtotal'] = str_replace(",", "", $_POST['Subtotal']);

$_POST['Total'] = str_replace(",", "", $_POST['Total']);

$_POST['Estimated_Rev'] = str_replace(",", "", $_POST['Estimated_Rev']);
$_POST['Uang_Muka'] = str_replace(",", "", $_POST['Uang_Muka']);

$fetch = DB::table("opportunity")->where("Task_ID", $_POST['Task_ID'])->first();
$subjek = $fetch->Oppor_Subject;
$companyname = $fetch->Company_Name;
$Account_No = $fetch->Account_No;
if ($_POST['ID'] == '') {

	$kueri = "INSERT INTO opportunity_detail (PO_Initial,Task_ID, PO_No, Project_Type, PO_Date, Contract_No, Reference_No, Customer_No, Description, Department, Alocation, Subject, Note, Subtotal, Discount, PPN, Total, PPH, Uang_Muka, Termin, PO_PIC, Email, Due_Days, Created_Date, Created_By, Start_Date, End_Date, Pencatatan) VALUES ('$_POST[PO_Initial]','$_POST[Task_ID]','$_POST[PO_No]','$_POST[Project_Type]','$_POST[PO_Date]','$_POST[Contract_No]','$_POST[Reference_No]','$_POST[Customer_No]','$_POST[Description]','$_POST[Department]','$_POST[Alocation]','$_POST[Subject]','$_POST[Note]','$_POST[Subtotal]',0,'$_POST[PPN]','$_POST[Total]',0,'$_POST[Uang_Muka]','$_POST[Termin]','$_POST[PO_PIC]','$_POST[Email]','$_POST[Due_Days]',NOW(),'$Saya','$_POST[Start_Date]','$_POST[End_Date]', '$_POST[Pencatatan]')";

	DB::insert(DB::raw($kueri));
	DB::update(DB::raw("UPDATE opportunity SET Grade_Status='2',Estimated_Rev='$_POST[Estimated_Rev]', Oppor_Subject='$_POST[Oppor_Subject]', Reference = '$_POST[Reference]' WHERE Task_ID='$_POST[Task_ID]'"));
} else {
	DB::update(DB::raw("UPDATE opportunity_detail SET PO_Initial='$_POST[PO_Initial]',PO_No='$_POST[PO_No]',
Project_Type='$_POST[Project_Type]', PO_Date='$_POST[PO_Date]', Contract_No='$_POST[Contract_No]', Reference_No='$_POST[Reference_No]', Customer_No='$_POST[Customer_No]', Description='$_POST[Description]', Department='$_POST[Department]', Alocation='$_POST[Alocation]', Subject='$_POST[Subject]', Note='$_POST[Note]', Subtotal='$_POST[Subtotal]', PPN='$_POST[PPN]', Total='$_POST[Total]', PPH='$_POST[PPH]', Uang_Muka='$_POST[Uang_Muka]', Termin='$_POST[Termin]', PO_PIC='$_POST[PO_PIC]', Email='$_POST[Email]', Due_Days='$_POST[Due_Days]', Start_Date='$_POST[Start_Date]', End_Date='$_POST[End_Date]', Pencatatan ='$_POST[Pencatatan]' WHERE ID=$_POST[ID]"));
	DB::update(DB::raw("UPDATE opportunity SET Grade_Status='2',Estimated_Rev='$_POST[Estimated_Rev]', Oppor_Subject='$_POST[Oppor_Subject]', Reference = '$_POST[Reference]' WHERE Task_ID='$_POST[Task_ID]'"));
}


echo "success";
