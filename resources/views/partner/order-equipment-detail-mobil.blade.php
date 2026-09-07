<div class="card my-3">
    <div class="card-body">
        <h5 class="">Keuntungan Mobil</h5>
        <div class="mb-3">
            <h6 class="">Inventory</h6>
            <div id="FacilityBenefitsMobil" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
            <div>
                <input type="button" id="ekselFacilityBenefitsMobilDet" name="ekselFacilityBenefitsMobilDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="FacilityBenefitsMobil.toExcel('./export/generate.php');" />
                <input type="button" id="submitFacilityBenefitsMobilDet" name="submitFacilityBenefitsMobilDet" class="btn btn-success mt-2 mr-2" value="Submit Data" onclick="submitDataMobil();" />
            </div>
        </div>
    </div>
</div>

<script>
	FacilityBenefitsMobil = new dhtmlXGridObject('FacilityBenefitsMobil');
	FacilityBenefitsMobil.setImagePath("./dhtmlx/imgs/");
	FacilityBenefitsMobil.setHeader("No, Deskripsi, Detail, Tipe Asset, Jumlah, Harga Satuan, Total, Tanggal Order, Tanggal Diterima, Ceklis Penerimaan");
	FacilityBenefitsMobil.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#master_checkbox");
	FacilityBenefitsMobil.setInitWidths("60,200,200,100,100,100,100,100,100,100,100");
	FacilityBenefitsMobil.setColAlign("center,left,left,center,right,right,right,center,center,center");
	FacilityBenefitsMobil.setColSorting("int,str,str,int,int,int,str,str,str");
	FacilityBenefitsMobil.setColTypes("ro,ro,ro,ro,edn,edn,edn,dhxCalendar,dhxCalendar,ch");
	FacilityBenefitsMobil.enableSmartRendering(true, 150);
    FacilityBenefitsMobil.setDateFormat("%Y-%m-%d"); 
    FacilityBenefitsMobil.setNumberFormat("0,000", 4, ".", ",");
    FacilityBenefitsMobil.setNumberFormat("0,000", 5, ".", ",");
    FacilityBenefitsMobil.setNumberFormat("0,000", 6, ".", ",");
    FacilityBenefitsMobil.attachEvent("onEditCell", function (stage, rowId, cellIndex, newValue, oldValue) {
        if (cellIndex == 4 || cellIndex == 5) {
            let qty = parseFloat(FacilityBenefitsMobil.cells(rowId, 4).getValue()) || 0;
            let price = parseFloat(FacilityBenefitsMobil.cells(rowId, 5).getValue()) || 0;
            FacilityBenefitsMobil.cells(rowId, 6).setValue(qty * price);
        }
        return true;
    });
    // Auto-fill the order date with today when it is left blank
    FacilityBenefitsMobil.attachEvent("onXLE", function (grid) {
        var today = new Date().toISOString().slice(0, 10);
        for (var i = 0; i < grid.getRowsNum(); i++) {
            var rid = grid.getRowId(i);
            if (!grid.cells(rid, 7).getValue()) {
                grid.cells(rid, 7).setValue(today);
            }
        }
    });
	PFacilityBenefitsMobil = new dataProcessor("partner-order-equipment-detail-update?Task={{ $partner->id }}&wh={{ $_GET['ID'] }}");
	PFacilityBenefitsMobil.setTransactionMode({
		mode: "POST",
		total: true,
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},

	})

	PFacilityBenefitsMobil.setTransactionMode("POST", true);
	PFacilityBenefitsMobil.setUpdateMode("Off");
	PFacilityBenefitsMobil.init(FacilityBenefitsMobil);
    PFacilityBenefitsMobil.attachEvent("onBeforeDataSending", function (id,state, data) {
        // Modify or add payload data here
        // console.log(data)
        return true; // Return true to continue processing
    });
    PFacilityBenefitsMobil.attachEvent("onAfterUpdateFinish", function (id,state, data) {
        alert("Data Successfully Submitted!");
        setTimeout(function() {
            refreshdatamobilinventory();
        }, 500);
    });
	FacilityBenefitsMobil.setSkin("dhx_skyblue");
	FacilityBenefitsMobil.init();
	refreshdatamobilinventory();

	function refreshdatamobilinventory() {
		FacilityBenefitsMobil.clearAndLoad('xml-partner-order-equipment-detail-mobil-inventory?Task={{ $_GET['ID'] }}');
	}
    
    function submitDataMobil()
    {
        // let inventory = FacilityBenefitsMobil.getChangedRows().split(',');
        // for (let i = 0; i < inventory.length; i++) {
        //     const ele = inventory[i];
        //     let a = FacilityBenefitsMobil.cells(ele, 4).getValue();
        //     if(a == '1') {
        //         let b = FacilityBenefitsMobil.cells(ele, 3).getValue();
        //         if (b == '') {
        //             alert('Please fill in the sent date for inventory ' + FacilityBenefitsMobil.cells(ele, 1).getValue());
        //             return false;
        //         }
        //     }
        // }

        PFacilityBenefitsMobil.sendData();
    }
</script>