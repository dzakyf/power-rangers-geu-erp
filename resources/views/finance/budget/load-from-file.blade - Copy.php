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
		var idbi =BudgetDetail.uid() + $nomors;
		BudgetDetail.addRow(idbi,[BudgetDetail.getRowsNum()+1,'$coa->Acc_No','$coa->Description','0','0','0','0','0','0','0','0','0','0','0','0']);
		</script>";
		
		$nomors++;
}

echo"<script>$.unblockUI();</script>";
?>

<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">UPLOAD FILE BATCH</div>

<div id="vault" class="with-header" style='height:200px;margin:0px;width:500px;'></div>

<input type="hidden" id="file_lists">
<div id='loadexcelcoa'><div>

<script>

vault.events.on("uploadComplete", function(obj) {
	$.blockUI();
	LoadM('loadexcelcoa','load-excel-coa?filenya=' + obj[0].name);
	
});
vault.events.on("uploadBegin", function(obj) {
	$("#file_lists").val("");
	
});	
vault.events.on("beforeAdd", function(fileItem) {
	if (vault.data.getLength() >= 1) {
		alert('Only can upload 1 file per checkpoint. create in a excel file if you want upload multiple!');
		return false;
	}
});