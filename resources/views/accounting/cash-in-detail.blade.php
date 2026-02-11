<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

$getcur = DB::table("currency")->where("Currency_Symbol", $_GET['Currency'])->first();
$kursval = $getcur->Currency_Value;
?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">CASH IN DETAIL</div>

<div id="GridCash" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
<div id="divtom">
	<input type="button" id="SubmitGridCash" NAME="SubmitGridCash" value="POSTING NOW" />
</div>



<script>
	var winCOA;
	var WCOA;

	function WindoWCOA(text, url, width, height) {
		winCOA = new dhtmlXWindows();

		winCOA.attachViewportTo('PagesContent');
		WCOA = winCOA.createWindow("WCOA", 0, 0, width, height);
		WCOA.keepInViewport(false);
		WCOA.setText(text);
		WCOA.attachURL(url, true);
		WCOA.button("minmax1").hide();
		WCOA.button("park").hide();
		WCOA.centerOnScreen();
		WCOA.allowMove();
	}

	function editcekcashin(rId, cInd, stage, nValue, oValue) {

		if (cInd == 1) {
			WindoWCOA("Chart of Accounts", "finance-cash-out-detail-coa", 650, 350);
		}


		return true;
	}

	GridCash = new dhtmlXGridObject('GridCash');
	GridCash.setImagePath("./dhtmlx/imgs/");
	GridCash.setHeader("No.,Account No,Date,Reference,Remark,Value,Currency Val,Department");
	GridCash.attachFooter(["Total", '#cspan', '#cspan', '#cspan', '#cspan', "#stat_total", "", ""]);
	GridCash.setInitWidths("70,350,100,150,200,100,100,100");
	GridCash.setColAlign("center,left,center,left,left,right,right,center");
	GridCash.setColTypes("ro,combo,ro,ed,ed,edn,ron,combo");
	GridCash.setColSorting("int,str,str,str,str,str,str,str");
	GridCash.setDateFormat("%Y-%m-%d");
	GridCash.setEditable(true);
	GridCash.setNumberFormat("0,000", 5, ".", ",");
	GridCash.setNumberFormat("0,000", 6, ".", ",");
	GridCash.enableKeyboardSupport(true);
	GridCash.attachEvent("onRowDblClicked", editcekcashin);

	GridCash.setSkin("dhx_skyblue");
	PGridCash = new dataProcessor("accounting-cash-in-detail-update");
	PGridCash.setTransactionMode({
		mode: "POST",
		total: true,
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},
		payload: {
			"ID": "<?php echo $_GET['ID'] ?>"
		}

	})

	PGridCash.setUpdateMode("Off");
	PGridCash.init(GridCash);
	GridCash.init();
	combocurr = GridCash.getColumnCombo(7);
	combocurr.enableFilteringMode(true);
	combocurr.load("xml-finance-department");

	combocurrx = GridCash.getColumnCombo(1);
	combocurrx.enableFilteringMode(true);
	combocurrx.load("xml-finance-coa");


	refreshdatadetail();

	function refreshdatadetail() {
		GridCash.clearAndLoad("xml-accounting-cash-in-detail?ID=<?php echo $_GET['ID']; ?>", UpdateGrid);

	}
	$('#SubmitGridCash').click(function() {

		PGridCash.sendData();
		alert("Data Successfully Submitted!");
		setTimeout(function() {
			$("#DivCashDetail").html('');
			refreshdata();
		}, 1500);

	});

	function UpdateGrid() {
		var OKK = 0;
		for (var i = 0; i < GridCash.getRowsNum(); i++) {
			rowIdMov = GridCash.getRowId(i);
			PGridCash.setUpdated(rowIdMov, true);
		}

		return OKK;
	}
</script>