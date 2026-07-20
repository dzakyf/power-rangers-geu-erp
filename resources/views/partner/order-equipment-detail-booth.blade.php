<div class="card my-3">
    <div class="card-body">
        <h5 class="">Order Equipment - Booth</h5>
        <div class="mb-3">
            <h6 class="">Inventory</h6>
            <div id="FacilityBenefitsBooth" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
            <div>
                <input type="button" id="ekselFacilityBenefitsBoothDet" name="ekselFacilityBenefitsBoothDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="FacilityBenefitsBooth.toExcel('./export/generate.php');" />
                <input type="button" id="submitFacilityBenefitsBoothDet" name="submitFacilityBenefitsBoothDet" class="btn btn-success mt-2 mr-2" value="Submit Data" onclick="submitDataBooth();" />
            </div>
        </div>
    </div>
</div>

<script>
	FacilityBenefitsBooth = new dhtmlXGridObject('FacilityBenefitsBooth');
	FacilityBenefitsBooth.setImagePath("./dhtmlx/imgs/");
	FacilityBenefitsBooth.setHeader("No, Deskripsi, Tipe Asset, Qty, Harga Satuan, Total Harga, Tanggal Order, Tanggal Penerimaan, Ceklist Penerimaan");
	FacilityBenefitsBooth.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#master_checkbox");
    FacilityBenefitsBooth.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#stat_total,,,");
	FacilityBenefitsBooth.setInitWidths("60,200,100,100,100,100,100,100,100");
	FacilityBenefitsBooth.setColAlign("center,left,center,right,right,right,center,center,center");
	FacilityBenefitsBooth.setColSorting("int,str,str,int,int,int,str,str,int");
	FacilityBenefitsBooth.setColTypes("ro,ro,ro,ron,ron,ron,dhxCalendar,dhxCalendar,ch");
    FacilityBenefitsBooth.attachEvent("onEditCell", function (stage, rowId, cellIndex, newValue, oldValue) {
        if (cellIndex == 3 || cellIndex == 4) {
            let qty = FacilityBenefitsBooth.cells(rowId, 3).getValue();
            let price = FacilityBenefitsBooth.cells(rowId, 4).getValue();
            let total = parseFloat(qty) * parseFloat(price);
            FacilityBenefitsBooth.cells(rowId, 5).setValue(total);
        }
        return true;
    });
	FacilityBenefitsBooth.enableSmartRendering(true, 150);
    FacilityBenefitsBooth.setDateFormat("%Y-%m-%d");
    FacilityBenefitsBooth.setNumberFormat("0,000", 3, ".", ",");
    FacilityBenefitsBooth.setNumberFormat("0,000", 4, ".", ",");
    FacilityBenefitsBooth.setNumberFormat("0,000", 5, ".", ",");
	PFacilityBenefitsBooth = new dataProcessor("partner-order-equipment-detail-update?Task={{ $partner->id }}&wh={{ $_GET['ID'] }}");
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

	function refreshdataboothinventory() {
		FacilityBenefitsBooth.clearAndLoad('xml-partner-order-equipment-detail-booth-inventory?Task={{ $_GET['ID'] }}');
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
</script>