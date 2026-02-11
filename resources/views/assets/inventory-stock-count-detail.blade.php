<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Http\Fungsi;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$NIK =  Auth::user()->NIK;
	

$fetch = DB::table("good_receive")->where("GR_No",$_GET['ID'])->first();
	$sobjf = DB::select(DB::raw("SHOW FIELDS FROM good_receive"));

	echo "<script>";

		foreach($sobjf as $objf){
		$idobj = $objf->Field;
		$isinya = $fetch->$idobj;
			if ($idobj !='Remark'){
				echo "$('#$idobj').val('$isinya').attr('disabled','disabled'); \n";
			}
		}
	echo "</script>";


?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">STOCK COUNT PROCESS</div>
 <div id="GRDetail2" style="width:auto;height:400px; background-color:white;overflow:hidden"></div>
        <div id="divtom" align="center">
        <input type="button" id="SaveDoc" NAME="SaveDoc" value="Save"  class="btn btn-info mt-2 mr-2" />                
        <input type="button" id="SubmitStockCDetail" NAME="SubmitStockCDetail" value="Final Submit" class="btn btn-success mt-2 mr-2" />                
        </div>
	<div id="loadsco"><div>

<script>
var myCalendar;
    myCalendar = new dhtmlXCalendarObject(["Incoming_Date"]);
	myCalendar.setDateFormat("%Y-%m-%d");


StockCDetail = new dhtmlXGridObject('GRDetail2');
StockCDetail.setImagePath("./dhtmlx/imgs/");
StockCDetail.setHeader("No,Category,Product Code,Description,Unit,Qty System,Qty Take,Counted,Dispute,Remark, Previous Note");
StockCDetail.attachHeader("&nbsp;,#combo_filter,#text_filter,#text_filter,#rspan,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#rspan,#rspan");
StockCDetail.setInitWidths("50,100,100,150,100,100,100,100,100,300,auto");
StockCDetail.setColTypes("ro,ro,ro,ro,ro,ron,edn,ron,ron[=c5-c6-c7],edtxt,ro");
StockCDetail.setColAlign("center,left,left,left,center,right,right,right,right,left,left");
StockCDetail.setColSorting("int,str,str,str,str,int,int,int,str,int,str,str");
StockCDetail.setEditable(true);
StockCDetail.setNumberFormat("0,000.00",5,".",",");
StockCDetail.setNumberFormat("0,000.00",6,".",",");
StockCDetail.setNumberFormat("0,000.00",7,".",",");
StockCDetail.enableKeyboardSupport(true);
PStockCDetail = new dataProcessor("inventory-stock-count-detail-update?");
PStockCDetail.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
		  payload: {"Task_ID":"<?php echo $_GET['ID'] ?>"}

    })
	
PStockCDetail.setTransactionMode("POST", true);
PStockCDetail.setUpdateMode("Off");
PStockCDetail.init(StockCDetail);
StockCDetail.setSkin("dhx_skyblue");
StockCDetail.init();
refreshdata2();

function refreshdata2(){
	$.blockUI();
	StockCDetail.clearAndLoad('xml-inventory-stock-count-detail?ID=<?php echo $_GET['ID']?>',itungdeh);

}
function itungdeh(){
	$.unblockUI();
}


$('#SaveDoc').click(function(){
	PStockCDetail.sendData();

	alert("Stock Count Save Only!");
	setTimeout(function(){
		refreshdata2();
	},2000);
			
})


$('#SubmitStockCDetail').click(function(){
	PStockCDetail.sendData();
	LoadM('loadsco','inventory-stock-count-final?Task_ID=<?php echo $_GET['ID']?>');
	
	setTimeout(function(){
		alert("Stock Count Final Closing!");
		location.href='inventory-stock-count';
	},2000);
	
			
})


</script>
