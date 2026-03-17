<div class="card my-3">
    <div class="card-body">
        <h5 class="">Keuntungan Booth</h5>
        <div class="mb-3">
            <h6 class="">Inventory</h6>
            <div id="FacilityBenefitsBooth" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
            <div>
                <input type="button" id="ekselFacilityBenefitsBoothDet" name="ekselFacilityBenefitsBoothDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="FacilityBenefitsBooth.toExcel('./export/generate.php');" />
                <input type="button" id="submitFacilityBenefitsBoothDet" name="submitFacilityBenefitsBoothDet" class="btn btn-success mt-2 mr-2" value="Submit Data" onclick="submitDataBooth();" />
            </div>
        </div>

        <div class="mb-3">
            <h6 class="">Access</h6>
            <div id="FacilityBenefitsBoothAccess" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
            <div>
                <input type="button" id="ekselFacilityBenefitsBoothDetAccess" name="ekselFacilityBenefitsBoothDetAccess" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="FacilityBenefitsBoothAccess.toExcel('./export/generate.php');" />
                <input type="button" id="submitFacilityBenefitsBoothDetAccess" name="submitFacilityBenefitsBoothDetAccess" class="btn btn-success mt-2 mr-2" value="Submit Data" onclick="submitDataBoothAccess();" />
            </div>
        </div>

        <div class="mb-3">
            <h6 class="">Support</h6>
            <div id="FacilityBenefitsBoothSupport" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
            <div>
                <input type="button" id="ekselFacilityBenefitsBoothDetSupport" name="ekselFacilityBenefitsBoothDetSupport" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="FacilityBenefitsBoothSupport.toExcel('./export/generate.php');" />
                <input type="button" id="submitFacilityBenefitsBoothDetSupport" name="submitFacilityBenefitsBoothDetSupport" class="btn btn-success mt-2 mr-2" value="Submit Data" onclick="submitDataBoothSupport();" />
            </div>
        </div>
    </div>
</div>

<script>
	FacilityBenefitsBooth = new dhtmlXGridObject('FacilityBenefitsBooth');
	FacilityBenefitsBooth.setImagePath("./dhtmlx/imgs/");
	FacilityBenefitsBooth.setHeader("No, Deskripsi, Tipe Asset, Qty, Harga Satuan, Total Harga, Ceklist ketersediaan");
	FacilityBenefitsBooth.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#master_checkbox");
	FacilityBenefitsBooth.setInitWidths("60,200,100,100,100,100,100");
	FacilityBenefitsBooth.setColAlign("center,left,center,right,right,right,center");
	FacilityBenefitsBooth.setColSorting("int,str,str,int,int,int,int");
	FacilityBenefitsBooth.setColTypes("ro,ro,ro,edn,edn,edn,ch");
	FacilityBenefitsBooth.enableSmartRendering(true, 150);
    FacilityBenefitsBooth.setDateFormat("%Y-%m-%d"); 
    FacilityBenefitsBooth.setNumberFormat("0,000", 3, ".", ",");
    FacilityBenefitsBooth.setNumberFormat("0,000", 4, ".", ",");
    FacilityBenefitsBooth.setNumberFormat("0,000", 5, ".", ",");
    FacilityBenefitsBooth.attachEvent("onEditCell", function (stage, rowId, cellIndex, newValue, oldValue) {
        if (cellIndex == 3 || cellIndex == 4) {
            let qty = FacilityBenefitsBooth.cells(rowId, 3).getValue();
            let price = FacilityBenefitsBooth.cells(rowId, 4).getValue();
            let total = parseFloat(qty) * parseFloat(price);
            FacilityBenefitsBooth.cells(rowId, 5).setValue(total);
        }
        return true;
    });
	PFacilityBenefitsBooth = new dataProcessor("partner-asset-management-detail-update?Task={{ $partner->id }}&wh={{ $_GET['ID'] }}");
	PFacilityBenefitsBooth.setTransactionMode({
		mode: "POST",
		total: true,
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},

	})

	PFacilityBenefitsBooth.setTransactionMode("POST", true);
	PFacilityBenefitsBooth.setUpdateMode("Off");
	PFacilityBenefitsBooth.init(FacilityBenefitsBooth);
    PFacilityBenefitsBooth.attachEvent("onBeforeDataSending", function (id,state, data) {
        // Modify or add payload data here
        // console.log(data)
        return true; // Return true to continue processing
    });
    PFacilityBenefitsBooth.attachEvent("onAfterUpdateFinish", function (id,state, data) {
        alert("Data Successfully Submitted!");
        setTimeout(function() {
            refreshdataboothinventory();
        }, 500);
    });
	FacilityBenefitsBooth.setSkin("dhx_skyblue");
	FacilityBenefitsBooth.init();
	refreshdataboothinventory();

	FacilityBenefitsBoothAccess = new dhtmlXGridObject('FacilityBenefitsBoothAccess');
	FacilityBenefitsBoothAccess.setImagePath("./dhtmlx/imgs/");
	FacilityBenefitsBoothAccess.setHeader("No, Deskripsi, Detail, Ceklist Ketersediaan");
	FacilityBenefitsBoothAccess.attachHeader("#rspan,#text_filter,#text_filter,#master_checkbox");
	FacilityBenefitsBoothAccess.setInitWidths("60,200,300,100");
	FacilityBenefitsBoothAccess.setColAlign("center,left,left,center");
	FacilityBenefitsBoothAccess.setColSorting("int,str,str,int");
	FacilityBenefitsBoothAccess.setColTypes("ro,ro,ro,ch");
	FacilityBenefitsBoothAccess.enableSmartRendering(true, 150);
    FacilityBenefitsBoothAccess.setDateFormat("%Y-%m-%d"); 
	PFacilityBenefitsBoothAccess = new dataProcessor("partner-asset-management-detail-update?Task={{ $partner->id }}&wh={{ $_GET['ID'] }}");
	PFacilityBenefitsBoothAccess.setTransactionMode({
		mode: "POST",
		total: true,
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},

	})

	PFacilityBenefitsBoothAccess.setTransactionMode("POST", true);
	PFacilityBenefitsBoothAccess.setUpdateMode("Off");
	PFacilityBenefitsBoothAccess.init(FacilityBenefitsBoothAccess);
    PFacilityBenefitsBoothAccess.attachEvent("onBeforeDataSending", function (id,state, data) {
        // Modify or add payload data here
        // console.log(id)
        return true; // Return true to continue processing
    });
    PFacilityBenefitsBoothAccess.attachEvent("onAfterUpdateFinish", function (id,state, data) {
        alert("Data Successfully Submitted!");
        setTimeout(function() {
            refreshdataboothaccess();
        }, 500);
    });
	FacilityBenefitsBoothAccess.setSkin("dhx_skyblue");
	FacilityBenefitsBoothAccess.init();
	refreshdataboothaccess();

	FacilityBenefitsBoothSupport = new dhtmlXGridObject('FacilityBenefitsBoothSupport');
	FacilityBenefitsBoothSupport.setImagePath("./dhtmlx/imgs/");
	FacilityBenefitsBoothSupport.setHeader("No, Deskripsi, Detail, Ceklist Ketersediaan");
	FacilityBenefitsBoothSupport.attachHeader("#rspan,#text_filter,#text_filter,#master_checkbox");
	FacilityBenefitsBoothSupport.setInitWidths("60,200,300,100");
	FacilityBenefitsBoothSupport.setColAlign("center,left,left,center");
	FacilityBenefitsBoothSupport.setColSorting("int,str,str,int");
	FacilityBenefitsBoothSupport.setColTypes("ro,ro,ro,ch");
	FacilityBenefitsBoothSupport.enableSmartRendering(true, 150);
    FacilityBenefitsBoothSupport.setDateFormat("%Y-%m-%d"); 
	PFacilityBenefitsBoothSupport = new dataProcessor("partner-asset-management-detail-update?Task={{ $partner->id }}&wh={{ $_GET['ID'] }}");
	PFacilityBenefitsBoothSupport.setTransactionMode({
		mode: "POST",
		total: true,
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},

	})

	PFacilityBenefitsBoothSupport.setTransactionMode("POST", true);
	PFacilityBenefitsBoothSupport.setUpdateMode("Off");
	PFacilityBenefitsBoothSupport.init(FacilityBenefitsBoothSupport);
    PFacilityBenefitsBoothSupport.attachEvent("onBeforeDataSending", function (id,state, data) {
        // Modify or add payload data here
        // console.log(id)
        return true; // Return true to continue processing
    });
    PFacilityBenefitsBoothSupport.attachEvent("onAfterUpdateFinish", function (id,state, data) {
        alert("Data Successfully Submitted!");
        setTimeout(function() {
            refreshdataboothsupport();
        }, 500);
    });
	FacilityBenefitsBoothSupport.setSkin("dhx_skyblue");
	FacilityBenefitsBoothSupport.init();
	refreshdataboothsupport();

	function refreshdataboothinventory() {
		FacilityBenefitsBooth.clearAndLoad('xml-partner-asset-management-detail-booth-inventory?Task={{ $_GET['ID'] }}');
	}

    function refreshdataboothaccess() {
		FacilityBenefitsBoothAccess.clearAndLoad('xml-partner-asset-management-detail-booth-access?Task={{ $_GET['ID'] }}');
    }
    
    function refreshdataboothsupport() {
		FacilityBenefitsBoothSupport.clearAndLoad('xml-partner-asset-management-detail-booth-support?Task={{ $_GET['ID'] }}');
    }
    
    function submitDataBooth()
    {
        // let inventory = FacilityBenefitsBooth.getChangedRows().split(',');
        // for (let i = 0; i < inventory.length; i++) {
        //     const ele = inventory[i];
        //     let a = FacilityBenefitsBooth.cells(ele, 3).getValue();
        //     if(a == '1') {
        //         let b = FacilityBenefitsBooth.cells(ele, 2).getValue();
        //         if (b == '') {
        //             alert('Please fill in the sent date for inventory ' + FacilityBenefitsBooth.cells(ele, 1).getValue());
        //             return false;
        //         }
        //     }
        // }

        PFacilityBenefitsBooth.sendData();
    }
    
    function submitDataBoothAccess()
    {
        // let inventory = FacilityBenefitsBoothAccess.getChangedRows().split(',');
        // for (let i = 0; i < inventory.length; i++) {
        //     const ele = inventory[i];
        //     let a = FacilityBenefitsBoothAccess.cells(ele, 4).getValue();
        //     if(a == '1') {
        //         let b = FacilityBenefitsBoothAccess.cells(ele, 3).getValue();
        //         if (b == '') {
        //             alert('Please fill in the sent date for inventory ' + FacilityBenefitsBoothAccess.cells(ele, 1).getValue());
        //             return false;
        //         }
        //     }
        // }

        PFacilityBenefitsBoothAccess.sendData();
    }
    
    function submitDataBoothSupport()
    {
        // let inventory = FacilityBenefitsBoothSupport.getChangedRows().split(',');
        // for (let i = 0; i < inventory.length; i++) {
        //     const ele = inventory[i];
        //     let a = FacilityBenefitsBoothSupport.cells(ele, 4).getValue();
        //     if(a == '1') {
        //         let b = FacilityBenefitsBoothSupport.cells(ele, 3).getValue();
        //         if (b == '') {
        //             alert('Please fill in the sent date for support ' + FacilityBenefitsBoothSupport.cells(ele, 1).getValue());
        //             return false;
        //         }
        //     }
        // }

        PFacilityBenefitsBoothSupport.sendData();
    }
</script>