<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

$fadata = DB::table("customerz")->where("Customer_No", $_GET['CID'])->first();
$tgl = 1;
$CID = $_GET['CID'];
?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DETAIL DATA</div>

<div id="ARDetail" style=" width: 100%; height: 350px;"></div>
<div>
	<input type="button" id="ExcelDetailAR" name="ExcelDetailAR" value="Export Excel" onclick="ARDetail.toExcel('./export/generate.php');" />

</div>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">AR HISTORY</div>
<div id="ARDetailHistory" style=" width: auto; height: 350px;"></div>
<div>


	<input type="button" id="ExcelDetailAR" name="ExcelDetailAR" value="Export Excel" onclick="ARHistory.toExcel('./export/generate.php');" />

</div>

</div>


<script>
	var myCalendar;
	myCalendar = new dhtmlXCalendarObject(["PTP"]);
	myCalendar.setDateFormat("%Y-%m-%d");
	myCalendar.attachEvent("onClick", function(d) {
		ChangeTab(myCalendar.getFormatedDate("%Y-%m-%d", d), 16);
	});

	function lepasevent() {

		myCalendar.setSensitiveRange(null, null);
	}

	function pasangevent() {
		var modelnya = <?php echo $tgl; ?>;

		if (modelnya == '1') {
			var tgl1 = '<?php echo $tgl1; ?>';
			var tgl2 = '<?php echo $tgl2; ?>';
			myCalendar.setSensitiveRange(tgl1, tgl2);
		}

	}

	function ChangeTab(Isi, Cols) {
		for (i = 0; i < ARDetail.getRowsNum(); i++) {

			idAR = ARDetail.getRowId(i);

			ARDetail.cells(idAR, Cols).setValue(Isi);
			ARDetail.cells(idAR, 16).setValue($("#PTP").val());


		}
	}

	function BANKDetail(IDData) {
		LoadM('divRef', 'ar-reference?Acc=' + IDData);

	}


	function sumColumn(ind) {
		var out = 0;
		for (var i = 0; i < ARDetail.getRowsNum(); i++) {
			out += parseFloat(ARDetail.cells2(i, ind).getValue());
		}
		return out;
	}
	ARDetail = new dhtmlXGridObject('ARDetail');
	ARDetail.setImagePath("./dhtmlx/imgs/");
	ARDetail.setHeader("No.,Invoice No,Invoice Date,Invoice Type,Payment Date,Subtotal,PPN,Total AR,Remain,Paid,Confirm,After PPH,Action,SubAction,PaymentVal,DebtColl,PIC,PIC No,Aging(days),Note,Claim,Claim Type,Claim Note,Collector,Rek No,Customer Name,Remain,PIC Receive");
	ARDetail.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#rspan,#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	ARDetail.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,,,,,,,,,,,,");
	ARDetail.setInitWidths("70,100,100,100,150,100,100,110,110,110,100,100,0,0,0,0,0,100,100,120,180,200,140,280,100,140,120");
	ARDetail.setColAlign("center,center,center,center,center,right,right,right,center,right,center,center,left,left,center");
	ARDetail.setColTypes("ro,ro,ro,ro,ro,ron,ron,edn,ron,ron,ro,ro,ro,ro,ro,ro,ro,ro,edn,coro,txt,ro,ro,ro,ro,ron,ro,ro");
	ARDetail.setColSorting("int,str,str,str,int,int,int,int,str,int,str,str,str,str,str,str,str,str,str,str,str,str,str,str,int,str,str");
	ARDetail.setEditable(false);
	ARDetail.setSkin("dhx_skyblue");
	ARDetail.setNumberFormat("0,000", 9, ".", ",");
	ARDetail.setNumberFormat("0,000", 8, ".", ",");
	ARDetail.setNumberFormat("0,000", 7, ".", ",");
	ARDetail.setNumberFormat("0,000", 6, ".", ",");
	ARDetail.setNumberFormat("0,000", 5, ".", ",");
	ARDetail.setNumberFormat("0,000", 18, ".", ",");
	ARDetail.init();
	ARDetail.loadXML('xml-ar-history-detail?CID=<?php echo $CID ?>&Tgl1=' + $("#Tgl1").val() + "&Tgl2=" + $("#Tgl2").val());


	ARHistory = new dhtmlXGridObject('ARDetailHistory');
	ARHistory.setImagePath("./dhtmlx/imgs/");
	ARHistory.setHeader("No.,Customer,Name,Invoice No,Method,Debt Coll,Processed By,Processed Date,PTP,Action,Action Result,Result Output,PIC, PIC No,AR Value Before,Payment Value,Note,Created By, Created Date,Invoice No");
	ARHistory.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	ARHistory.setInitWidths("70,100,170,0,0,0,0,100,100,100,100,100,100,100,100,100,300,100,100,100");
	ARHistory.setColAlign("center,center,left,center,center,left,left,center,center,left,left,left,left,left,right,right,left,left,center,center");
	ARHistory.setColTypes("ro,ro,ro,ro,ro,ro,ro,ron,ron,ro,ro,ro,ro,ro,ron,ron,ro,ro,ro,ro");
	ARHistory.setEditable(true);
	ARHistory.setSkin("dhx_skyblue");
	ARHistory.setNumberFormat("0,000", 14, ".", ",");
	ARHistory.setNumberFormat("0,000", 15, ".", ",");
	ARHistory.init();
	ARHistory.loadXML('xml-ar-monitoring-detail-history?CID=<?php echo $CID ?>');




	function getAllValues() {
		var inputValues = [];
		$('#ProformaBADiv input,select,textarea').each(function() {
			var type = $(this).attr("type");
			if ((type == "checkbox" || type == "radio") && this.checked) {
				inputValues.push($(this).attr("id") + "=" + $(this).val());
			} else {
				if (type != "button" && type != "submit") {

					inputValues.push($(this).attr("id") + "=" + $(this).val());
				}
			}
		})

		return inputValues.join('&');
	}
</script>