<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	
	$fadata = DB::table("customerz")->where("Customer_No",$_GET['CID'])->first();
				$tgl = 1;
$CID = $_GET['CID'];
	?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">TRANSACTION DETAIL</div>

	<div id="ProyeksiHistoryDetail" style=" width: 100%; height: 350px;"></div>
	<div>
	  <input type="button" id="ExcelDetailAR" name="ExcelDetailAR" value="Export Excel" onclick="ProyeksiHistoryDetail.toExcel('./export/generate.php');"  />

	</div>

</div>
		

<script>

	ProyeksiHistoryDetail = new dhtmlXGridObject('ProyeksiHistoryDetail');
	ProyeksiHistoryDetail.setImagePath("./dhtmlx/imgs/");
	ProyeksiHistoryDetail.setHeader("No.,Invoice No,Invoice Date,Invoice Type,Aging(days),Total AR,Remain,Paid,Confirm,After PPH,Action,SubAction,PaymentVal,DebtColl,PIC,PIC No,PTP,Note,Claim,Claim Type,Claim Note,Collector,Rek No,Customer Name,Remain,PIC Receive,Delivered to Cust");
	ProyeksiHistoryDetail.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#rspan,#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	ProyeksiHistoryDetail.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#stat_total,#stat_total,#stat_total,,,,,,,,,,,,,,,,");
	ProyeksiHistoryDetail.setInitWidths("70,100,100,100,150,100,100,100,50,100,100,100,0,0,0,0,0,100,100,120,180,200,140,280,100,140,120");
	ProyeksiHistoryDetail.setColAlign("center,center,center,center,center,right,right,right,center,right,center,center,left,left,center");
	ProyeksiHistoryDetail.setColTypes("ro,ro,ro,ro,ro,ron,ron,edn,ro,ron,ro,ro,ro,ro,ro,ro,ro,ro,edn,coro,txt,ro,ro,ro,ro,ron,ro,ro");
	ProyeksiHistoryDetail.setColSorting("int,str,str,str,int,int,int,int,str,int,str,str,str,str,str,str,str,str,str,str,str,str,str,str,int,str,str");
	ProyeksiHistoryDetail.setEditable(false);
	ProyeksiHistoryDetail.setSkin("dhx_skyblue");
	ProyeksiHistoryDetail.setNumberFormat("0,000",9,".",",");
	ProyeksiHistoryDetail.setNumberFormat("0,000",7,".",",");
	ProyeksiHistoryDetail.setNumberFormat("0,000",6,".",",");
	ProyeksiHistoryDetail.setNumberFormat("0,000",5,".",",");
	ProyeksiHistoryDetail.setNumberFormat("0,000",18,".",",");
	ProyeksiHistoryDetail.init();
	ProyeksiHistoryDetail.loadXML('xml-ar-history-detail?CID=<?php echo $CID?>');

</script>
