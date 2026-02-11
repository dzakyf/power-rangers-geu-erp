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
$vals = "";

$kueri = "INSERT INTO recruitment_master_template(Template_ID,Message_Type,Message,Created_By,Created_Date) values ('$_POST[Template_ID]','$_POST[Message_Type]','$_POST[Tambahan]','$Saya',NOW())";

DB::insert(DB::raw($kueri));
echo "success";
?>