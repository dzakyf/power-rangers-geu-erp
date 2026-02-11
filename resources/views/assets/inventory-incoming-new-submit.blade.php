<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$NIK =  Auth::user()->Emp_ID;
	$Saya =  Auth::user()->username;
	
$ars = "";
$ax = "";
$isi = "";

foreach($_POST as $key=>$value)
{	
	
    	
			if ($key != 'Task_ID' && $key != 'Created_Date' && $key != 'Created_By' && $key != 'Location' && $key != 'Company_Name'){
				
			
				$ars 	.="$key,";
				
				$isi	.="'$_POST[$key]',";
				
				
				$MX .="$key = $_POST[$key],";
			
			}					
			

    

}
$Incoming_Date = $_POST['Incoming_Date'];
$Reference = $_POST['Reference'];
$User = $_POST['User'];
$PONO = $_POST['PO_No'];
$APNO = $_POST['AP_No'];
$Note = $_POST['Note'];
$ars = substr($ars,0,-1);
$isi = substr($isi,0,-1);

	DB::insert(DB::raw("INSERT INTO good_receive(GR_No,PR_No,PO_No,PO_No_KSPS,Reference,Remark,Location,Status,Created_By,Purchase_Category,Purchase_Sub_Category,Purchase_Type,Incoming_Date)
			SELECT '$APNO',PR_No,PO_No,PO_No_KSPS,'$Reference','$Note','$LSP','CREATED','$Saya',Purchase_Category,Purchase_Sub_Category,Purchase_Type,'$Incoming_Date'  from purchase_order WHERE PO_No='$PONO'"));

			DB::update(DB::raw("UPDATE purchase_order SET GR_No='$APNO',GR_Date=NOW() WHERE PO_No='$PONO'"));
			DB::update(DB::raw("UPDATE purchase_order_dtl SET GR_No='$APNO' WHERE PO_No='$PONO'"));

echo "success";
?>