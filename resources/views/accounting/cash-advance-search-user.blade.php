<?php

use Illuminate\Http\Request;
use Illuminate\Http\Fungsi;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;



header("Content-type: text/xml");
echo('<?xml version="1.0" encoding="iso-8859-1"?>');
echo "<complete>"; 
$Saya = Auth::user()->username;
$LSP = Auth::user()->Location;
$qy = DB::table('employment')->where("Name","LIKE","%$_GET[chars]%"->get();
foreach ($qy as $ax){
	
		echo "<option value='$ax->Acc_No'>".Filter("$ax->Acc_No - $ax->Description")."</option>";

	
}
echo "</complete>"; 

?>
