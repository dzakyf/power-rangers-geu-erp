<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	
$_POST['Subject'] = str_replace("gamp;", "&", $_POST['Subject']);
$_POST['Total'] = str_replace(".", "", $_POST['Total']);
$_POST['Subtotal_PO'] = str_replace(".", "", $_POST['Subtotal_PO']);
$_POST['Man_Fee'] = str_replace(".", "", $_POST['Man_Fee']);
	
DB::update(DB::raw("UPDATE proforma SET BA_File_Uploaded=NOW(),Last_Update=NOW(),BA_Status='FINISH',Status='PROPOSED',Bill_Type='$_POST[Bill_Type]',Total='$_POST[Total]',Man_Fee='$_POST[Man_Fee]',Subtotal_PO='$_POST[Subtotal_PO]',Periode='$_POST[Periode]',Periode_Tagihan='$_POST[Periode_Tagihan]',BA_No='$_POST[BA_No]',Subject='$_POST[Subject]',BA_Date='$_POST[BA_Date]',Invoice_To='$_POST[Invoice_To]' WHERE ID=$_POST[ID_Proforma]"));

DB::insert(DB::raw("INSERT INTO proforma_detail_file(Proforma_No, File_Category, Reference_No, Path_File, Created_Date) VALUES
('$_POST[Proforma_No]', 'BAST', '$_POST[BA_No]', '$_POST[file_lists]', NOW())"));
echo "success";
?>