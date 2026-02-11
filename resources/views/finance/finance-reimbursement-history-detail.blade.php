<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$q = DB::table("project_reimbursement")->where("Task_ID",$_GET['ID'])->first();

$kursval =1;
?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">REIMBURSEMENT DETAIL</div>
<div id="grid_kbterima" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">HISTORY PAYMENT</div>
<div id="GRIDDETAIL" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>

<script>
 
		 
		GridCash = new dhtmlXGridObject('grid_kbterima');
		GridCash.setImagePath("./dhtmlx/imgs/");
		GridCash.setHeader("No.,Account No,Date,Reference,Remark,Value,Currency Val");
		GridCash.attachFooter(["Total",'#cspan','#cspan','#cspan','#cspan',"<div id='totalamount'>{#stat_total}</div>","",""]);
		GridCash.setInitWidths("70,0,100,150,300,100,100,0");
		GridCash.setColAlign("center,left,center,left,left,right,right,center");
		GridCash.setColTypes("ro,ro,ro,ed,ed,edn,ron,combo");
		GridCash.setColSorting("int,str,str,str,str,str,str");
		GridCash.setDateFormat("%Y-%m-%d"); 
		GridCash.setEditable(false);
		GridCash.setNumberFormat("0,000",5,".",",");
		GridCash.setNumberFormat("0,000",6,".",",");
		GridCash.enableKeyboardSupport(true);
		GridCash.setSkin("dhx_skyblue");
		GridCash.init();
		refreshdatadetail();

function refreshdatadetail(){
			GridCash.clearAndLoad("xml-project-reimbursement-detail-reimbursement?ID=<?php echo $_GET['Task_ID'];?>");

}
detailpayment = new dhtmlXGridObject('GRIDDETAIL');
		detailpayment.setImagePath("./dhtmlx/imgs/");
		detailpayment.setHeader("No.,Payment Type,Payment Task ID,Reference,Payment Acc No,Value,Note,Project ID,Created By,Date Created");
		detailpayment.attachFooter(["Total",'#cspan','#cspan','#cspan','#cspan',"<div id='totalamountx'>{#stat_total}</div>","","","",""]);
		detailpayment.setInitWidths("70,100,100,150,100,100,300,100,100,100");
		detailpayment.setColAlign("center,left,center,left,left,right,right,center,center,center");
		detailpayment.setColTypes("ro,ro,ro,ed,ed,edn,ro,ro,ro,ro");
		detailpayment.setColSorting("int,str,str,str,str,str,str");
		detailpayment.setDateFormat("%Y-%m-%d"); 
		detailpayment.setEditable(false);
		detailpayment.setNumberFormat("0,000",5,".",",");
		detailpayment.enableKeyboardSupport(true);
		detailpayment.setSkin("dhx_skyblue");
		detailpayment.init();
		refreshdatadetail2();

function refreshdatadetail2(){
			detailpayment.clearAndLoad("xml-finance-reimbursement-history-detail?ID=<?php echo $_GET['ID'];?>");

}
</script>
