<div class="mt-3">
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">TRACEABILITY</div>
	<div class="card">
		<div class="card-body">
			<div id="TraceGridDetail" style="width:auto;height:400px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="editBtn" name="editBtn" value="Edit Data" class="btn btn-warning mt-2 mr-2" onclick="editSelected()" />
				<input type="button" id="exportButton" name="exportButton" value="Print Doc" class="btn btn-info mt-2 mr-2" onclick="printOutData()" />
			</div>
		</div>
	</div>
	<div id="divTraceDetail"></div>
</div>

<script>
	var TraceGridDetail;
	var selectedFragmentIdDetail = null;

	TraceGridDetail = new dhtmlXGridObject('TraceGridDetail');
	TraceGridDetail.setImagePath("./dhtmlx/imgs/");
	TraceGridDetail.setHeader("No,Source,POO Name,Type,City,Province,Qty (Kg),PO Qty,WO Code,WO Date,WO Status,PO Code,PO Date,PO Status,Surat Jalan Code,Surat Jalan Date,Surat Jalan Status,Split,");
	TraceGridDetail.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#cspan");
	TraceGridDetail.attachFooter(["Total", '#cspan', '#cspan', '#cspan', '#cspan', '#cspan', '#cspan', "#stat_total"]);
	TraceGridDetail.setInitWidths("40,70,200,80,120,100,80,80,140,90,80,140,90,80,140,90,80,60,0");
	TraceGridDetail.setColAlign("center,center,left,center,left,left,right,right,left,center,center,left,center,right,left,center,center,center,center");
	TraceGridDetail.setColSorting("int,str,str,str,str,str,num,num,str,str,str,str,str,str,str,str,str,str,str");
	TraceGridDetail.setColTypes("ron,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	TraceGridDetail.enableSmartRendering(true, 150);
	TraceGridDetail.attachEvent("onRowSelect", function(id) {
		var src = TraceGridDetail.cells(id, 1).getValue();
		var fid = TraceGridDetail.cells(id, 18).getValue();
		selectedFragmentIdDetail = (fid && fid !== '') ? fid : null;
		document.getElementById('editBtn').style.display = (src === 'EXTERNAL') ? '' : 'none';
	});
	TraceGridDetail.setSkin("dhx_skyblue");
	TraceGridDetail.init();
	TraceGridDetail.setColumnHidden(18, true);
	refreshdataDetail();

	function refreshdataDetail() {
		TraceGridDetail.clearAndLoad('cns-iscc-traceability-utama-detail-data?id={{$id}}');
	}

	function applyFilter() {
		refreshdataDetail();
	}

	function resetFilter() {
		document.getElementById('fSource').value = '';
		document.getElementById('fCity').value = '';
		document.getElementById('fDateFrom').value = '';
		document.getElementById('fDateTo').value = '';
		refreshdataDetail();
	}

	function editSelected() {
		if (!selectedFragmentIdDetail) {
			alert('Select an EXTERNAL (Supplier) row to edit.');
			return;
		}
		LoadM('divTraceDetail', 'cns-iscc-traceability-edit/' + selectedFragmentIdDetail);
	}

	function printOutData() {
		if (!selectedFragmentIdDetail) {
			alert('Select row to print.');
			return;
		}
		window.open('cns-iscc-traceability-print/' + selectedFragmentIdDetail, '_blank');
	}
</script>