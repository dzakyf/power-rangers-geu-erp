<style>
    .radio-lg {
        transform: scale(1.5);
    }
</style>

<div class="JudulContent mt-3" style="color:#699;background-image:url(hdr_back.gif);">CORRECTIVE ACTION</div>
<div class="card">
    <div class="card-body">
			<div id="WarehouseCorrectiveDtl" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="ekselWarehouseCorrectiveDtlDet" name="ekselWarehouseCorrectiveDtlDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="WarehouseCorrectiveDtl.toExcel('./export/generate.php');" />
				<input type="button" id="detailWarehouseCorrectiveDtl" name="detailWarehouseCorrectiveDtl" value="Detail Corrective" class="btn btn-info mt-2 mr-2" onclick="WarehouseCorrectiveDtlKlik()" />
			</div>
    </div>
</div>

<div id="divlistOpportunityDetail"></div>

<script>
	function WarehouseCorrectiveDtlKlik() {

		WarehouseCorrectiveDtlID = WarehouseCorrectiveDtl.getSelectedId();
		if (WarehouseCorrectiveDtlID != null) {
			LoadM('divlistOpportunityDetail', 'partner-warehouse-corrective-action-detail-corrective?Task=' + WarehouseCorrectiveDtlID);
		}else {
			alert("Pilih baris data");
		}
		return true;
	}
	WarehouseCorrectiveDtl = new dhtmlXGridObject('WarehouseCorrectiveDtl');
	WarehouseCorrectiveDtl.setImagePath("./dhtmlx/imgs/");
	WarehouseCorrectiveDtl.setHeader("No,Nomor Audit, Tanggal Audit, Due Date Corrective, No Gudang, Lokasi Gudang, Auditor, Jenis Audit, Kategori Temuan, Area Temuan, Parameter Temuan, Ringkasan Temuan, Catatan Audit, Created By, Corrective Action");
	WarehouseCorrectiveDtl.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// WarehouseCorrectiveDtl.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	WarehouseCorrectiveDtl.setInitWidths("60,150,100,100,150,200,100,100,100,100,100,100,100,100,auto");
	WarehouseCorrectiveDtl.setColAlign("center,left,left,left,left,left,left,left,left,left,left,left");
	WarehouseCorrectiveDtl.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str");
	WarehouseCorrectiveDtl.setColTypes("ron,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	WarehouseCorrectiveDtl.enableSmartRendering(true, 150);
	// WarehouseCorrectiveDtl.setNumberFormat("0,000", 7, ".", ",");
	WarehouseCorrectiveDtl.attachEvent("onRowDblClicked", WarehouseCorrectiveDtlKlik);
	WarehouseCorrectiveDtl.setSkin("dhx_skyblue");
	WarehouseCorrectiveDtl.init();
	refreshdata();

	function refreshdata() {
		WarehouseCorrectiveDtl.clearAndLoad('xml-partner-warehouse-corrective-action-detail?Task={{ request()->get("Task") }}');
	}
</script>