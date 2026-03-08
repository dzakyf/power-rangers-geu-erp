<div class="card mt-3">
	<div class="card-body">
		<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">WAREHOUSE</div>
		<div id="WarehouseListDetail" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselWarehouseListDetailDet" name="ekselWarehouseListDetailDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="WarehouseListDetail.toExcel('./export/generate.php');" />
			<input type="button" id="newWarehouseListDetail" name="newWarehouseListDetail" value="Update Warehouse Detail" class="btn btn-info mt-2 mr-2" onclick="WarehouseListDetailKlik()" />
			<input type="button" id="detailWarehouseListDetail" name="newWarehouseListDetail" value="New Warehouse Detail" class="btn btn-success mt-2 mr-2" onclick="WarehouseListDetailNew()" />
		</div>
	</div>
</div>

<div id="divlistOpportunityDetail"></div>

<script>
	$('#SubmitCustDetail').click(function() {

		gettingVars = getAllValues();

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: "partner-warehouse-list-detail-update?_token={{ csrf_token() }}",
			type: "POST",
			data: gettingVars,
			success: function(data, textStatus, jqXHR) {
				var dataPos = data.indexOf("success");
				if (dataPos != '-1') {
					var cariAWB = data.split(":");
					alert('Data Warehouse Submitted!');

					location.href = "partner-warehouse-list";
				} else {
					alert('Warehouse Failed to Submit!');
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				alert("Error Internal! Please Try again!");

			}
		});
	})

	function getAllValues() {
		var inputValues = [];
		$('#ContentPages input,#ContentPages select,#ContentPages textarea').each(function() {
			var type = $(this).attr("type");
			if ((type == "checkbox" || type == "radio") && this.checked) {
				inputValues.push($(this).attr("name") + "=" + $(this).val());
			} else if (type != "button" && type != "submit" && type != "radio" && type != "checkbox") {
				inputValues.push($(this).attr("id") + "=" + $(this).val());
			} else if ($(this).is("textarea")) {
				inputValues.push($(this).attr("id") + "=" + $(this).val());
			}
		})
		return inputValues.join('&');
	}

	function WarehouseListDetailNew() {

		LoadM('divlistOpportunityDetail', 'partner-warehouse-list-detail-new?Task={{ $_GET["Task"] }}');
		return true;
	}
	function WarehouseListDetailKlik() {

		WarehouseListDetailID = WarehouseListDetail.getSelectedId();
		if (WarehouseListDetailID != null) {
			LoadM('divlistOpportunityDetail', 'partner-warehouse-list-detail-detail?Task=' + WarehouseListDetailID);
		}else {
			alert("Pilih baris data");
		}
		return true;
	}
	WarehouseListDetail = new dhtmlXGridObject('WarehouseListDetail');
	WarehouseListDetail.setImagePath("./dhtmlx/imgs/");
	WarehouseListDetail.setHeader("No,Tanggal Penerimaan, Tipe Penjemputan, Nama POO, Sumber, Coverage, Volume Diterima, Harga / Liter, Total Diterima, Volume Reject (Jika Ada), Bukti Timbangan / Barang");
	WarehouseListDetail.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// WarehouseListDetail.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	WarehouseListDetail.setInitWidths("60,100,150,200,100,120,100,150,100,100,100,100");
	WarehouseListDetail.setColAlign("center,left,left,left,left,left,right,right,right,right,left");
	WarehouseListDetail.setColSorting("int,str,str,str,str,str,int,int,int,int,str");
	WarehouseListDetail.setColTypes("ro,ro,ro,ro,ro,ron,ron,ron,ron,ro,ro");
	WarehouseListDetail.enableSmartRendering(true, 150);
	WarehouseListDetail.setNumberFormat("0,000", 6, ".", ",");
	WarehouseListDetail.setNumberFormat("0,000", 7, ".", ",");
	WarehouseListDetail.setNumberFormat("0,000", 8, ".", ",");
	WarehouseListDetail.setNumberFormat("0,000", 9, ".", ",");
	WarehouseListDetail.attachEvent("onRowDblClicked", WarehouseListDetailKlik);
	WarehouseListDetail.setSkin("dhx_skyblue");
	WarehouseListDetail.init();
	refreshdataDetail();

	function refreshdataDetail() {
		WarehouseListDetail.clearAndLoad('xml-partner-warehouse-list-detail?Task={{$_GET["Task"]}}');

	}
</script>