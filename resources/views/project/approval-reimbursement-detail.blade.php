<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	
$kursval =1;
?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">REIMBURSEMENT DETAIL</div>

	<div id="grid_kbterima" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
		


<script>
	
		 
		GridCash = new dhtmlXGridObject('grid_kbterima');
		GridCash.setImagePath("./dhtmlx/imgs/");
		GridCash.setHeader("No.,Account No,Date,Reference,Remark,Value,Currency Val");
		GridCash.attachFooter(["Total",'#cspan','#cspan','#cspan','#cspan',"#stat_total","",""]);
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
			GridCash.clearAndLoad("xml-project-reimbursement-detail-reimbursement?ID=<?php echo $_GET['ID'];?>");

}
		

		</script>
