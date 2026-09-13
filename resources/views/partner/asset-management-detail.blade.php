@php
    // Unified Asset Management detail (booth + mobil in one view).
    // Internal users edit the availability checklist; MITRA partners
    // confirm receipt (Tanggal Order, Tanggal Diterima, Status Diterima).
    $isPartner = auth()->user()->Priv_ID === 'MITRA';
    $whTaskId = $wh->warehouse_task_id;
@endphp
<div class="card my-3">
    <div class="card-body">
        <h5 class="">Asset Management - {{ ucfirst($type) }}</h5>
        <div class="mb-3">
            <h6 class="">Inventory</h6>
            <div id="FacilityBenefitsInventory" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
            <div>
                <input type="button" id="ekselFacilityBenefitsInv" name="ekselFacilityBenefitsInv" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="FacilityBenefitsInventory.toExcel('./export/generate.php');" />
                <input type="button" id="submitFacilityBenefitsInv" name="submitFacilityBenefitsInv" class="btn btn-success mt-2 mr-2" value="Submit Data" onclick="submitDataInventory();" />
            </div>
        </div>

        <div class="mb-3">
            <h6 class="">Access</h6>
            <div id="FacilityBenefitsAccess" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
            <div>
                <input type="button" id="ekselFacilityBenefitsAcc" name="ekselFacilityBenefitsAcc" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="FacilityBenefitsAccess.toExcel('./export/generate.php');" />
                <input type="button" id="submitFacilityBenefitsAcc" name="submitFacilityBenefitsAcc" class="btn btn-success mt-2 mr-2" value="Submit Data" onclick="submitDataAccess();" />
            </div>
        </div>

        <div class="mb-3">
            <h6 class="">Support</h6>
            <div id="FacilityBenefitsSupport" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
            <div>
                <input type="button" id="ekselFacilityBenefitsSup" name="ekselFacilityBenefitsSup" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="FacilityBenefitsSupport.toExcel('./export/generate.php');" />
                <input type="button" id="submitFacilityBenefitsSup" name="submitFacilityBenefitsSup" class="btn btn-success mt-2 mr-2" value="Submit Data" onclick="submitDataSupport();" />
            </div>
        </div>
    </div>
</div>

<script>
	// Fixed unified layouts (keep in sync with AssetManagementController):
	//   inventory: No, Deskripsi, Detail, Tipe Asset, Qty, Harga Satuan,
	//     Total Harga, Ceklist Ketersediaan, Tanggal Order, Tanggal Diterima,
	//     Status Diterima
	//   access/support: No, Deskripsi, Detail, Ceklist Ketersediaan,
	//     Tanggal Diterima, Status Diterima
	FacilityBenefitsInventory = new dhtmlXGridObject('FacilityBenefitsInventory');
	FacilityBenefitsInventory.setImagePath("./dhtmlx/imgs/");
	FacilityBenefitsInventory.setHeader("No, Deskripsi, Detail, Tipe Asset, Qty, Harga Satuan, Total Harga, Ceklist Ketersediaan, Tanggal Order, Tanggal Diterima, Status Diterima");
	FacilityBenefitsInventory.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,{{ $isPartner ? '' : '#master_checkbox' }},#text_filter,#text_filter,#master_checkbox");
	FacilityBenefitsInventory.setInitWidths("60,200,200,100,100,110,110,120,110,110,110");
	FacilityBenefitsInventory.setColAlign("center,left,left,center,right,right,right,center,center,center,center");
	FacilityBenefitsInventory.setColSorting("int,str,str,str,int,int,int,int,str,str,int");
	FacilityBenefitsInventory.setColTypes("ro,ro,ro,ro,ron,ron,ron,ch,{{ $isPartner ? 'dhxCalendar' : 'ro' }},{{ $isPartner ? 'dhxCalendar' : 'ro' }},ch");
	FacilityBenefitsInventory.enableSmartRendering(true, 150);
	FacilityBenefitsInventory.setDateFormat("%Y-%m-%d");
	FacilityBenefitsInventory.setNumberFormat("0,000", 4, ".", ",");
	FacilityBenefitsInventory.setNumberFormat("0,000", 5, ".", ",");
	FacilityBenefitsInventory.setNumberFormat("0,000", 6, ".", ",");
	FacilityBenefitsInventory.attachEvent("onEditCell", function (stage, rowId, cellIndex, newValue, oldValue) {
		@if($isPartner)
		// Ceklist Ketersediaan is internal-only
		if (stage == 0 && cellIndex == 7) { return false; }
		@endif
		if (cellIndex == 4 || cellIndex == 5) {
			let qty = parseFloat(FacilityBenefitsInventory.cells(rowId, 4).getValue()) || 0;
			let price = parseFloat(FacilityBenefitsInventory.cells(rowId, 5).getValue()) || 0;
			FacilityBenefitsInventory.cells(rowId, 6).setValue(qty * price);
		}
		return true;
	});
@if($isPartner)
	// Auto-fill the order date with today when it is left blank
	FacilityBenefitsInventory.attachEvent("onXLE", function (grid) {
		var today = new Date().toISOString().slice(0, 10);
		for (var i = 0; i < grid.getRowsNum(); i++) {
			var rid = grid.getRowId(i);
			if (!grid.cells(rid, 8).getValue()) {
				grid.cells(rid, 8).setValue(today);
			}
		}
	});
@endif
	FacilityBenefitsInventory.setSkin("dhx_skyblue");
	FacilityBenefitsInventory.init();
	// Ceklist Ketersediaan is handled in the partner-facility module.
	FacilityBenefitsInventory.setColumnHidden(7, true);
	refreshInventory();

	PFacilityBenefitsInventory = new dataProcessor("partner-asset-management-detail-update?Task={{ $partner->id }}&wh={{ $whTaskId }}");
	PFacilityBenefitsInventory.setTransactionMode({
		mode: "POST",
		total: true,
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},
	})
	PFacilityBenefitsInventory.setUpdateMode("Off");
	PFacilityBenefitsInventory.init(FacilityBenefitsInventory);
	PFacilityBenefitsInventory.attachEvent("onAfterUpdateFinish", function (id, state, data) {
		alert("Data Successfully Submitted!");
		setTimeout(function() {
			refreshInventory();
		}, 500);
	});

	function refreshInventory() {
		FacilityBenefitsInventory.clearAndLoad('xml-partner-asset-management-detail-{{ $type }}-inventory?Task={{ $whTaskId }}');
	}

	function submitDataInventory() {
		PFacilityBenefitsInventory.sendData();
	}

	// ---- Access ----
	FacilityBenefitsAccess = new dhtmlXGridObject('FacilityBenefitsAccess');
	FacilityBenefitsAccess.setImagePath("./dhtmlx/imgs/");
	FacilityBenefitsAccess.setHeader("No, Deskripsi, Detail, Ceklist Ketersediaan, Tanggal Diterima, Status Diterima");
	FacilityBenefitsAccess.attachHeader("#rspan,#text_filter,#text_filter,{{ $isPartner ? '' : '#master_checkbox' }},#text_filter,#master_checkbox");
	FacilityBenefitsAccess.setInitWidths("60,200,300,120,110,110");
	FacilityBenefitsAccess.setColAlign("center,left,left,center,center,center");
	FacilityBenefitsAccess.setColSorting("int,str,str,int,str,int");
	FacilityBenefitsAccess.setColTypes("ro,ro,ro,ch,{{ $isPartner ? 'dhxCalendar' : 'ro' }},ch");
	FacilityBenefitsAccess.enableSmartRendering(true, 150);
	FacilityBenefitsAccess.setDateFormat("%Y-%m-%d");
	FacilityBenefitsAccess.setSkin("dhx_skyblue");
	FacilityBenefitsAccess.init();
	// Ceklist Ketersediaan is handled in the partner-facility module.
	FacilityBenefitsAccess.setColumnHidden(3, true);
	refreshAccess();

	PFacilityBenefitsAccess = new dataProcessor("partner-asset-management-detail-update?Task={{ $partner->id }}&wh={{ $whTaskId }}");
	PFacilityBenefitsAccess.setTransactionMode({
		mode: "POST",
		total: true,
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},
	})
	PFacilityBenefitsAccess.setUpdateMode("Off");
	PFacilityBenefitsAccess.init(FacilityBenefitsAccess);
	PFacilityBenefitsAccess.attachEvent("onAfterUpdateFinish", function (id, state, data) {
		alert("Data Successfully Submitted!");
		setTimeout(function() {
			refreshAccess();
		}, 500);
	});

	function refreshAccess() {
		FacilityBenefitsAccess.clearAndLoad('xml-partner-asset-management-detail-{{ $type }}-access?Task={{ $whTaskId }}');
	}

	function submitDataAccess() {
		PFacilityBenefitsAccess.sendData();
	}

	// ---- Support ----
	FacilityBenefitsSupport = new dhtmlXGridObject('FacilityBenefitsSupport');
	FacilityBenefitsSupport.setImagePath("./dhtmlx/imgs/");
	FacilityBenefitsSupport.setHeader("No, Deskripsi, Detail, Ceklist Ketersediaan, Tanggal Diterima, Status Diterima");
	FacilityBenefitsSupport.attachHeader("#rspan,#text_filter,#text_filter,{{ $isPartner ? '' : '#master_checkbox' }},#text_filter,#master_checkbox");
	FacilityBenefitsSupport.setInitWidths("60,200,300,120,110,110");
	FacilityBenefitsSupport.setColAlign("center,left,left,center,center,center");
	FacilityBenefitsSupport.setColSorting("int,str,str,int,str,int");
	FacilityBenefitsSupport.setColTypes("ro,ro,ro,ch,{{ $isPartner ? 'dhxCalendar' : 'ro' }},ch");
	FacilityBenefitsSupport.enableSmartRendering(true, 150);
	FacilityBenefitsSupport.setDateFormat("%Y-%m-%d");
	FacilityBenefitsSupport.setSkin("dhx_skyblue");
	FacilityBenefitsSupport.init();
	// Ceklist Ketersediaan is handled in the partner-facility module.
	FacilityBenefitsSupport.setColumnHidden(3, true);
	refreshSupport();

	PFacilityBenefitsSupport = new dataProcessor("partner-asset-management-detail-update?Task={{ $partner->id }}&wh={{ $whTaskId }}");
	PFacilityBenefitsSupport.setTransactionMode({
		mode: "POST",
		total: true,
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},
	})
	PFacilityBenefitsSupport.setUpdateMode("Off");
	PFacilityBenefitsSupport.init(FacilityBenefitsSupport);
	PFacilityBenefitsSupport.attachEvent("onAfterUpdateFinish", function (id, state, data) {
		alert("Data Successfully Submitted!");
		setTimeout(function() {
			refreshSupport();
		}, 500);
	});

	function refreshSupport() {
		FacilityBenefitsSupport.clearAndLoad('xml-partner-asset-management-detail-{{ $type }}-support?Task={{ $whTaskId }}');
	}

	function submitDataSupport() {
		PFacilityBenefitsSupport.sendData();
	}
</script>
