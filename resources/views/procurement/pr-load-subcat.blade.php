<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Http\Custom_Function;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

	$qr =DB::select(DB::raw("SELECT Sub_Group from master_product_sub_group where Product_Group='$_GET[Category]' ORDER BY Sub_Group"));
	echo "    <select id='Sub_Category' class='cs-corner-all' onchange=\"gantiSub('$_GET[Category]',this.value);\" >";
	foreach($qr as $fa){
		if ($pilih =='') $pilih = $fa->Sub_Group;
		echo "<option>$fa->Sub_Group</option>";	
		
	}
  echo " </select>";



echo "<script>setTimeout(function(){ gantiSub('$_GET[Category]','$pilih'); },1000);</script>";
?>