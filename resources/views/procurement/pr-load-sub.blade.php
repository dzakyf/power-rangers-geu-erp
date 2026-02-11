<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Http\Custom_Function;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

	$qr =DB::select(DB::raw("SELECT Sub_Sub_Group from master_product_sub_sub_group where Product_Group='$_GET[Main_Category]' AND Sub_Group='$_GET[subval]' ORDER BY Sub_Sub_Group"));
	echo "    <select id='Supplies_Type' class='cs-corner-all'>";
	foreach($qr as $fa){
		echo "<option>$fa->Sub_Sub_Group</option>";	
		
	}
  echo " </select>";



?>