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

        <div class="mb-3">
            <h6 class="">Access</h6>
            <div id="FacilityBenefitsMobilAccess" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
            <div>
                <input type="button" id="ekselFacilityBenefitsMobilDetAccess" name="ekselFacilityBenefitsMobilDetAccess" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="FacilityBenefitsMobilAccess.toExcel('./export/generate.php');" />
                <input type="button" id="submitFacilityBenefitsMobilDetAccess" name="submitFacilityBenefitsMobilDetAccess" class="btn btn-success mt-2 mr-2" value="Submit Data" onclick="submitDataMobilAccess();" />
            </div>
        </div>

        <div class="mb-3">
            <h6 class="">Support</h6>
            <div id="FacilityBenefitsMobilSupport" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
            <div>
                <input type="button" id="ekselFacilityBenefitsMobilDetSupport" name="ekselFacilityBenefitsMobilDetSupport" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="FacilityBenefitsMobilSupport.toExcel('./export/generate.php');" />
                <input type="button" id="submitFacilityBenefitsMobilDetSupport" name="submitFacilityBenefitsMobilDetSupport" class="btn btn-success mt-2 mr-2" value="Submit Data" onclick="submitDataMobilSupport();" />
            </div>
        </div>
    </div>
</div>

<script>
	FacilityBenefitsMobil = new dhtmlXGridObject('FacilityBenefitsMobil');
	FacilityBenefitsMobil.setImagePath("./dhtmlx/imgs/");
	FacilityBenefitsMobil.setHeader("No, Deskripsi, Detail, Tipe Asset, Jumlah, Harga Satuan, Total, Ceklis Ketersediaan");
	FacilityBenefitsMobil.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#master_checkbox");
	FacilityBenefitsMobil.setInitWidths("60,200,200,100,100,100,100,100");
	FacilityBenefitsMobil.setColAlign("center,left,left,center,right,right,right,center");
	FacilityBenefitsMobil.setColSorting("int,str,str,str,int,int,int,str");
	FacilityBenefitsMobil.setColTypes("ro,ro,ro,ro,edn,edn,edn,ch");
	FacilityBenefitsMobil.enableSmartRendering(true, 150);
    FacilityBenefitsMobil.setDateFormat("%Y-%m-%d"); 
    FacilityBenefitsMobil.setNumberFormat("0,000", 4, ".", ",");
    FacilityBenefitsMobil.setNumberFormat("0,000", 5, ".", ",");
    FacilityBenefitsMobil.setNumberFormat("0,000", 6, ".", ",");
    FacilityBenefitsMobil.attachEvent("onEditCell", function (stage, rowId, cellIndex, newValue, oldValue) {
        if (cellIndex == 4 || cellIndex == 5) {
            let qty = FacilityBenefitsMobil.cells(rowId, 4).getValue();
            let price = FacilityBenefitsMobil.cells(rowId, 5).getValue();
            let total = parseFloat(qty) * parseFloat(price);
            FacilityBenefitsMobil.cells(rowId, 6).setValue(total);
        }
        return true;
    });
	PFacilityBenefitsMobil = new dataProcessor("partner-asset-management-detail-update?Task={{$partner->id}}&wh={{ $_GET['ID'] }}");
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

	FacilityBenefitsMobilAccess = new dhtmlXGridObject('FacilityBenefitsMobilAccess');
	FacilityBenefitsMobilAccess.setImagePath("./dhtmlx/imgs/");
	FacilityBenefitsMobilAccess.setHeader("No, Deskripsi, Detail, Ceklist Ketersediaan");
	FacilityBenefitsMobilAccess.attachHeader("#rspan,#text_filter,#text_filter,#master_checkbox");
	FacilityBenefitsMobilAccess.setInitWidths("60,200,300,100");
	FacilityBenefitsMobilAccess.setColAlign("center,left,left,center");
	FacilityBenefitsMobilAccess.setColSorting("int,str,str,int");
	FacilityBenefitsMobilAccess.setColTypes("ro,ro,ro,ch");
	FacilityBenefitsMobilAccess.enableSmartRendering(true, 150);
    FacilityBenefitsMobilAccess.setDateFormat("%Y-%m-%d"); 
	PFacilityBenefitsMobilAccess = new dataProcessor("partner-asset-management-detail-update?Task={{$partner->id}}&wh={{ $_GET['ID'] }}");
	PFacilityBenefitsMobilAccess.setTransactionMode({
		mode: "POST",
		total: true,
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},

	})

	PFacilityBenefitsMobilAccess.setTransactionMode("POST", true);
	PFacilityBenefitsMobilAccess.setUpdateMode("Off");
	PFacilityBenefitsMobilAccess.init(FacilityBenefitsMobilAccess);
    PFacilityBenefitsMobilAccess.attachEvent("onBeforeDataSending", function (id,state, data) {
        // Modify or add payload data here
        // console.log(id)
        return true; // Return true to continue processing
    });
    PFacilityBenefitsMobilAccess.attachEvent("onAfterUpdateFinish", function (id,state, data) {
        alert("Data Successfully Submitted!");
        setTimeout(function() {
            refreshdatamobilaccess();
        }, 500);
    });
	FacilityBenefitsMobilAccess.setSkin("dhx_skyblue");
	FacilityBenefitsMobilAccess.init();
	refreshdatamobilaccess();

	FacilityBenefitsMobilSupport = new dhtmlXGridObject('FacilityBenefitsMobilSupport');
	FacilityBenefitsMobilSupport.setImagePath("./dhtmlx/imgs/");
	FacilityBenefitsMobilSupport.setHeader("No, Deskripsi, Detail, Ceklist Ketersediaan");
	FacilityBenefitsMobilSupport.attachHeader("#rspan,#text_filter,#text_filter,#master_checkbox");
	FacilityBenefitsMobilSupport.setInitWidths("60,200,300,100");
	FacilityBenefitsMobilSupport.setColAlign("center,left,left,center");
	FacilityBenefitsMobilSupport.setColSorting("int,str,str,int");
	FacilityBenefitsMobilSupport.setColTypes("ro,ro,ro,ch");
	FacilityBenefitsMobilSupport.enableSmartRendering(true, 150);
    FacilityBenefitsMobilSupport.setDateFormat("%Y-%m-%d"); 
	PFacilityBenefitsMobilSupport = new dataProcessor("partner-asset-management-detail-update?Task={{$partner->id}}&wh={{ $_GET['ID'] }}");
	PFacilityBenefitsMobilSupport.setTransactionMode({
		mode: "POST",
		total: true,
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},

	})

	PFacilityBenefitsMobilSupport.setTransactionMode("POST", true);
	PFacilityBenefitsMobilSupport.setUpdateMode("Off");
	PFacilityBenefitsMobilSupport.init(FacilityBenefitsMobilSupport);
    PFacilityBenefitsMobilSupport.attachEvent("onBeforeDataSending", function (id,state, data) {
        // Modify or add payload data here
        // console.log(id)
        return true; // Return true to continue processing
    });
    PFacilityBenefitsMobilSupport.attachEvent("onAfterUpdateFinish", function (id,state, data) {
        alert("Data Successfully Submitted!");
        setTimeout(function() {
            refreshdatamobilsupport();
        }, 500);
    });
	FacilityBenefitsMobilSupport.setSkin("dhx_skyblue");
	FacilityBenefitsMobilSupport.init();
	refreshdatamobilsupport();

	function refreshdatamobilinventory() {
		FacilityBenefitsMobil.clearAndLoad('xml-partner-asset-management-detail-mobil-inventory?Task={{ $_GET['ID'] }}');
	}

    function refreshdatamobilaccess() {
		FacilityBenefitsMobilAccess.clearAndLoad('xml-partner-asset-management-detail-mobil-access?Task={{ $_GET['ID'] }}');
    }
    
    function refreshdatamobilsupport() {
		FacilityBenefitsMobilSupport.clearAndLoad('xml-partner-asset-management-detail-mobil-support?Task={{ $_GET['ID'] }}');
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
    
    function submitDataMobilAccess()
    {
        // let inventory = FacilityBenefitsMobilAccess.getChangedRows().split(',');
        // for (let i = 0; i < inventory.length; i++) {
        //     const ele = inventory[i];
        //     let a = FacilityBenefitsMobilAccess.cells(ele, 4).getValue();
        //     if(a == '1') {
        //         let b = FacilityBenefitsMobilAccess.cells(ele, 3).getValue();
        //         if (b == '') {
        //             alert('Please fill in the sent date for access ' + FacilityBenefitsMobilAccess.cells(ele, 1).getValue());
        //             return false;
        //         }
        //     }
        // }

        PFacilityBenefitsMobilAccess.sendData();
    }
    
    function submitDataMobilSupport()
    {
        // let inventory = FacilityBenefitsMobilSupport.getChangedRows().split(',');
        // for (let i = 0; i < inventory.length; i++) {
        //     const ele = inventory[i];
        //     let a = FacilityBenefitsMobilSupport.cells(ele, 4).getValue();
        //     if(a == '1') {
        //         let b = FacilityBenefitsMobilSupport.cells(ele, 3).getValue();
        //         if (b == '') {
        //             alert('Please fill in the sent date for support ' + FacilityBenefitsMobilSupport.cells(ele, 1).getValue());
        //             return false;
        //         }
        //     }
        // }

        PFacilityBenefitsMobilSupport.sendData();
    }
</script>