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
$qy = DB::table('currency')->get();
foreach ($qy as $ax){
	
		echo "<option value='$ax->Currency_Symbol'>".Filter("$ax->Currency_Symbol - $ax->Currency_Name")."</option>";

	
}
echo "</complete>"; 

?>
