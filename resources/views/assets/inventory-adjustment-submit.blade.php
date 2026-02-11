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
$Taskid = 'UI/'.$LSP.'/'.date('Ymdhis').md5($Saya,3);

foreach($_POST as $key=>$value)
{	
	
    	
			if ($key != 'Task_ID' && $key != 'Created_Date' && $key != 'Created_By' && $key != 'Location' && $key != 'SubmitCustDetail'){
				
			
				$ars 	.="$key,";
				
				$isi	.="'$_POST[$key]',";
				
				
			
			}					
			

    

}
$produk = $_POST['Product_Code'];
$ars = substr($ars,0,-1);
$isi = substr($isi,0,-1);
$data = DB::table("product_code")->where("Product_Code",$produk)->first();
	DB::insert(DB::raw("INSERT INTO supplies (Supplies_Type,Product_Code,Product_Description,Specification,Barcode,SN,Qty,Unit,UnitPrice,PO_No,GR_No,Date_Trx,User_Trx,Location,WH_ID)
			SELECT '$data->Category',Product_Code,Description,Specification,'$data->Barcode','$data->Barcode','$_POST[Qty]',Unit_Name,'$data->Price','$data->PO_No','$Taskid',NOW(),'$Saya','$LSP','$_POST[WH_ID]' FROM product_code where ID=$data->ID"));

			
			
$kueri = "INSERT INTO update_inventory($ars,Qty,Created_By,Location,Task_ID,Created_Date,Qty_Stock) values ($vals,'$_POST[Qty]','$Saya','$LSP','$Taskid',NOW(),'$_POST[Qty_Stock]')";
DB::insert(DB::raw($kueri));

$kueri = "UPDATE product_code SET Qty_Stock = Qty_Stock + $_POST[Qty] WHERE ID=$data->ID";

DB::update(DB::raw($kueri));
echo "success";
?>