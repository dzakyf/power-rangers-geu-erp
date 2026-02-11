<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

$Task_ID = "PIT-" . date("Y-m-d") . "-" . $LSP . date("-his");

?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DETAIL DATA</div>

<div id="ProformaInitiate" style="width:auto;height:220px; background-color:white;overflow:hidden"></div>
<div>
	<input type="button" id="ekselProformaInitiateDet" name="ekselProformaInitiateDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="ProformaInitiate.toExcel('./export/generate.php');" />

</div>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">UPLOAD DATA</div>
<div class="row">
	<div id="OrderDoc" style="height:130px; background-color:white;overflow:hidden;width:85%;height:130px;float:left;"></div>
	<div  style="width:15%;height:130px;white-space: nowrap; overflow: hidden;text-overflow: ellipsis;">
		<a href="#" id="PicOrderDoc" onclick="window.open(this.href,'gbrx2','gbrx2',true);"> <img src="file.png" width="100" id="picN" />
		</a>
		<br> <span id="namafile" style="font-size:80%;overflow:hidden;"></span>

	</div>
</div>
<div class="row">
	<div class="col-12">
		<input type="button" id="ekselProformaInitiateDet" name="ekselProformaInitiateDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="ProformaInitiate.toExcel('./export/generate.php');" />

	</div>
</div>



<script>
	ProformaInitiate = new dhtmlXGridObject('ProformaInitiate');
	ProformaInitiate.setImagePath("./dhtmlx/imgs/");
	ProformaInitiate.setHeader("No,BA No,BA Date,PO No,Description,PO Date,Contract No,Amount,Customer No,Customer Name,PIC,PO End Date,Note");
	ProformaInitiate.setInitWidths("60,120,100,120,220,100,100,100,80,150,100,100,100,100");
	ProformaInitiate.setColAlign("center,center,center,center,left,center,center,right,center,center,center,center,left,right");
	ProformaInitiate.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,int");
	ProformaInitiate.setColTypes("ro,ro,ro,ro,ro,ro,ro,ron,ro,ro,ro,ro,ro,ro");
	ProformaInitiate.enableSmartRendering(true, 150);
	ProformaInitiate.setNumberFormat("0,000", 5, ".", ",");
	ProformaInitiate.setSkin("dhx_skyblue");
	ProformaInitiate.init();
	refreshdata();

	function refreshdata() {
		ProformaInitiate.clearAndLoad('xml-sales-proforma-attachment-detail?Task_ID=<?php echo $_GET['ID'] ?>');

	}


	function OrderDocDobelKlik(rId, cInd) {

		detgbr = OrderDoc.cells(OrderDoc.getSelectedId(), 4).getValue();
		var namafile = OrderDoc.cells(OrderDoc.getSelectedId(), 2).getValue();
		var category = OrderDoc.cells(OrderDoc.getSelectedId(), 1).getValue();
		if(category == 'BAST') {
			$("#PicOrderDoc").attr("href", 'proformadoc/' + detgbr);
			$("#picN").attr("src", 'download.png');
			document.getElementById("namafile").textContent = namafile;
			return true;
		} else {
			$("#PicOrderDoc").attr("href", 'project_file/' + detgbr);
			$("#picN").attr("src", 'download.png');
			document.getElementById("namafile").textContent = namafile;
			return true;
		}
	

	}

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


	function rowseldetail(rowId, cellInd) {
		nmfile = OrderDoc.cells(rowId, 4).getValue();
		$("#PicOrderDoc").attr("src", 'proformadoc/' + nmfile);
		return true;
	}

	OrderDoc = new dhtmlXGridObject('OrderDoc');
	OrderDoc.setImagePath("./dhtmlx/imgs/");
	OrderDoc.setHeader("No,Category,Reference No,Upload,pathgbr");
	OrderDoc.setInitWidths("60,190,300,100,0");
	OrderDoc.setColTypes("ro,ro,ro,img,ro");
	OrderDoc.setColAlign("center,left,left,center,center,center");
	OrderDoc.setEditable(true);
	OrderDoc.attachEvent("onRowDblClicked", OrderDocDobelKlik);

	OrderDoc.setSkin("dhx_skyblue");
	// pOrderDoc = new dataProcessor("sales-proforma-attachment-detail-update");
	// pOrderDoc.setTransactionMode({
	// 	mode: "POST",
	// 	total: true,
	// 	headers: {
	// 		'X-CSRF-TOKEN': "{{ csrf_token() }}"
	// 	},
	// 	payload: {
	// 		"Task_ID": "<?php echo $_GET['ID'] ?>"
	// 	}

	// })
	// pOrderDoc.setUpdateMode("Off");
	// pOrderDoc.init(OrderDoc);
	OrderDoc.init();
	OrderDoc.clearAndLoad('xml-sales-proforma-posting-detail2?Task_ID=<?php echo $_GET['ID'] ?>', setupdateOrderDoc);

	function setupdateOrderDoc() {
		var out = 0;
		for (var i = 0; i < OrderDoc.getRowsNum(); i++) {
			rowIdMov = OrderDoc.getRowId(i);
			pOrderDoc.setUpdated(rowIdMov, true);
		}

		return out;
	}


	function NewTask() {
		nomorurut = OrderDoc.getRowsNum();
		var udis = OrderDoc.uid();
		OrderDoc.addRow(udis, [nomorurut + 1, 'DOC_NAME', , 'edit.png'], 0);
		OrderDoc.selectRow(0);
		WindoWCOA('UPLOAD DOC', 'sales-proforma-attachment-upload?idrow=' + udis, 600, 300);


	}


	// $("#ProSubmt").click(function() {

	// 				pOrderDoc.sendData();
	// 				setTimeout(function(){
	// 				alert('Data PO Upload Submitted!');
	// 				location.href='sales-proforma-attachment';
	// 			},1000);
	// });
</script>