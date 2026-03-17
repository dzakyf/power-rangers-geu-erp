<div class="mt-3">
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">LIST DETAIL WAREHOUSE</div>
	<div class="card">
		<div class="card-body">
			<div id="ValuationListDetail" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="ekselValuationListDetailDet" name="ekselValuationListDetailDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="ValuationListDetail.toExcel('./export/generate.php');" />
			</div>
		</div>
	</div>
</div>

<div id="divlistOpportunityDetail"></div>

<script>
	ValuationListDetail = new dhtmlXGridObject('ValuationListDetail');
	ValuationListDetail.setImagePath("./dhtmlx/imgs/");
	ValuationListDetail.setHeader("No,Tanggal Pembelian, Keterangan, Quantity, Harga, Jumlah, Ekonomis (Tahun), % Penyusutan Pertahun, Penyusutan perbulan, Status, Waktu Pakai (Bulan), NIlai Yang Terpakai, Sisa Waktu Penyusutan, Sisa Nilai Aset");
	ValuationListDetail.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// ValuationListDetail.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	ValuationListDetail.setInitWidths("60,100,150,200,100,100,200,100,100,100,100,100,100,100");
	ValuationListDetail.setColAlign("center,left,left,left,right,right,center,right,right,center,right,right,right,right");
	ValuationListDetail.setColSorting("int,str,str,str,int,str,str,int,int,int,int,int,int,int");
	ValuationListDetail.setColTypes("ron,ro,ro,ro,ron,ron,ron,ron,ron,ro,ron,ron,ron,ron,ron");
	ValuationListDetail.enableSmartRendering(true, 150);
	ValuationListDetail.setNumberFormat("0,000", 4, ".", ",");
	ValuationListDetail.setNumberFormat("0,000", 5, ".", ",");
	ValuationListDetail.setNumberFormat("0,000", 6, ".", ",");
	ValuationListDetail.setNumberFormat("0,000", 7, ".", ",");
	ValuationListDetail.setNumberFormat("0,000", 8, ".", ",");
	ValuationListDetail.setNumberFormat("0,000", 10, ".", ",");
	ValuationListDetail.setNumberFormat("0,000", 11, ".", ",");
	ValuationListDetail.setNumberFormat("0,000", 12, ".", ",");
	ValuationListDetail.setNumberFormat("0,000", 13, ".", ",");
	ValuationListDetail.setSkin("dhx_skyblue");
	ValuationListDetail.init();
	refreshdataDetail();

	function refreshdataDetail() {
		ValuationListDetail.clearAndLoad('xml-partner-valuation-detail?partner={{$partner->id}}&warehouse={{$warehouse->warehouse_task_id}}');

	}
</script>