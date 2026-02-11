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
$_POST['Estimated_Revenue'] = str_replace(".","",$_POST['Estimated_Revenue']);
$_POST['Estimated_Cost'] = str_replace(".","",$_POST['Estimated_Cost']);
$_POST['Budget_Available'] = str_replace(".","",$_POST['Budget_Available']);

foreach($_POST as $key=>$value)
{
		
		if ($key !='ID' && $key !='Task_ID' && $key != 'updatefinalclose' && $key != 'finishproject' && $key != 'SubmitCustDetail' && $key != 'DelCustDetail' && $key != 'Created_Date' && $key != 'Created_By' && $key != 'Location'&& $key != 'ID'){
			$ars .= "$key = '$value',";
			$ax .="$key,";
			$isi.="'$value',";
		}
}



$ax = substr($ax,0,-1);
$ars = substr($ars,0,-1);
$isi = substr($isi,0,-1);
$jno = "JPM-PO-".date("ymdhis");
$getcust = DB::table("customerz")->where("Customer_No",$_POST['Customer_No'])->first();
$accrev = $getcust->Acc_No_MF;
$coax  = "112.4.".$accrev;

DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
		Debet, Credit,  User_Resp, Created_By, Created_Date,Department) SELECT
		'$_POST[Source_Reference]', Location, '$_POST[PO_Date]', '$_POST[PO_No]', Task_ID, Customer_No,Customer_Name, '$jno', '$coax','Piutang Usaha Belum Tertagih', '','','PENGAKUAN PENDAPATAN PO BELUM TERTAGIH :: $_POST[Project_Name]',$_POST[PO_Value],0,Created_By,'$Saya',NOW(),'BUS' FROM project_mgt where ID = $_POST[ID]"));	

$acc ="213.8";
$accname = "Hutang PO Penjualan";

DB::select(DB::raw("INSERT INTO ho (Task_ID, Location, Posting_Date, Document_No, External_Doc, Partner_Code, Partner_Name, Journal_No, Account_No, Account_Name, Reference_No, Reference_Desc, Remark,
		 Credit, Debet, User_Resp, Created_By, Created_Date,Department) SELECT
		'$_POST[Source_Reference]', Location, '$_POST[PO_Date]', '$_POST[PO_No]', Task_ID, Customer_No,Customer_Name, '$jno', '$acc','$accname', '','','PENGAKUAN PENDAPATAN PO BELUM TERTAGIH :: $_POST[Project_Name]',$_POST[PO_Value],0,Created_By,'$Saya',NOW(),'BUS' FROM project_mgt where ID = $_POST[ID]"));
$kueri = "UPDATE project_mgt set Posting=1,Posting_By='$Saya',Posting_Date=NOW() WHERE ID=$_POST[ID]";
$kueri = "UPDATE opportunity set Posting=1,Posting_By='$Saya',Posting_Date=NOW() WHERE Task_ID='$_POST[Source_Reference]'";
DB::update(DB::raw($kueri));

echo "success";
?>