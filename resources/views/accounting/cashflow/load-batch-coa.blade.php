<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Http\Fungsi;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

$nomors = 1;
$cekcoa = DB::table("branch_coa")->orderBy("Acc_No","ASC")->get();
foreach($cekcoa as $coa){
	
	echo "<script>
		var idbi =ProyeksiDetail.uid() + $nomors;
		ProyeksiDetail.addRow(idbi,[ProyeksiDetail.getRowsNum()+1,'$coa->Acc_No','$coa->Description','0','0','0','0','0','0','0','0','0','0','0','0']);
		</script>";
		
		$nomors++;
}

echo"<script>$.unblockUI();</script>";
?>