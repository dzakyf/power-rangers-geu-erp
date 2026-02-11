<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
$LSP = Auth::user()->Location;
$NIK =  Auth::user()->Emp_ID;
$saya =  Auth::user()->username;
$whid =  Auth::user()->WH_ID;
$date = date('Y-m');
$query = DB::table("stock_count")->where("Location",$LSP)->where("Created_Date","LIKE", "$date%")->get();
$CQuery = count($query);
$NoR = $CQuery + 1;
if ( $NoR < 10 ) $NoR = "0$NoR";
$dateR = date('Ym');
$taskid = "STC/".$LSP."-$whid/$dateR-$NoR"; 


DB::insert(DB::raw("INSERT INTO stock_count_list
	 (Supplies_ID,Supplies_Type, Location, Product_Code, Product_Description, Specification, Barcode, SN, Unit, UnitPrice, PO_No, PR_No, GR_No, Status, Bin, Supplier, Remark, WH_ID,Task_ID,Qty,Real_Note) 
	
	SELECT  ID,Supplies_Type, Location, Product_Code, Product_Description, Specification, Barcode, SN, Unit, UnitPrice, PO_No, PR_No, GR_No, Status, Bin, Supplier, Remark, WH_ID,'$taskid',Qty,'CAPTURED'
	
	FROM supplies where WH_ID='$whid' AND Location='$LSP'  AND Status = 'GOOD' AND Qty > 0"));
		
		DB::insert(DB::raw("INSERT INTO stock_count(Location,WH_ID,Task_ID,Created_Date,Created_By) values ('$LSP','$whid','$taskid',NOW(),'$saya')"));
		

echo "<script>
	alert('Stock Count Success Created!');
	refreshdata();	

	$.unblockUI();
	</script>";

	
	
?>
