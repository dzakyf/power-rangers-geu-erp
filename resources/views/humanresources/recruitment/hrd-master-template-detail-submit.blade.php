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

$kueri = "UPDATE recruitment_master_template set Message_Type='$_POST[Message_Type]',Message='$_POST[Tambahan]' where Template_ID='$_POST[Template_ID]'";

DB::insert(DB::raw($kueri));
echo "success";
?>