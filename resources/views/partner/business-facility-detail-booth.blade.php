<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
// $LSP = "GEU-HO";
// $Saya = "admin";


?>

@extends('layout.mainlayout-bp')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Partner</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="#">Partner</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Business Lists</span>
		</li>
	</ol>
</div>

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
	FacilityBenefitsBooth.setHeader("No, Deskripsi, Tanggal Diterima, Status Diterima,Tipe Asset, Qty, Satuan Harga, Total, Tanggal Order");
	FacilityBenefitsBooth.attachHeader("#rspan,#text_filter,#text_filter,#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	FacilityBenefitsBooth.setInitWidths("60,200,100,100,100,100,100,100,100");
	FacilityBenefitsBooth.setColAlign("center,left,center,center,center,right,right,right,center");
	FacilityBenefitsBooth.setColSorting("int,str,str,int,str,int,int,int,str");
	FacilityBenefitsBooth.setColTypes("ro,ro,dhxCalendar,ch,ro,ron,ron,ron,ro");
	FacilityBenefitsBooth.enableSmartRendering(true, 150);
    FacilityBenefitsBooth.setDateFormat("%Y-%m-%d"); 
    FacilityBenefitsBooth.setNumberFormat("0,000", 5, ".", ",");
    FacilityBenefitsBooth.setNumberFormat("0,000", 6, ".", ",");
    FacilityBenefitsBooth.setNumberFormat("0,000", 7, ".", ",");
    FacilityBenefitsBooth.attachEvent("onEditCell", function (stage, rowId, cellIndex, newValue, oldValue) {
        if (cellIndex == 5 || cellIndex == 6) {
            let qty = FacilityBenefitsBooth.cells(rowId, 5).getValue();
            let price = FacilityBenefitsBooth.cells(rowId, 6).getValue();
            let total = parseFloat(qty) * parseFloat(price);
            FacilityBenefitsBooth.cells(rowId, 7).setValue(total);
        }
        return true;
    });
	PFacilityBenefitsBooth = new dataProcessor("partner-business-facility-update?Task={{ $id }}&wh={{ $wh->warehouse_task_id }}");
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
	FacilityBenefitsBoothAccess.setHeader("No, Deskripsi, Detail, Tanggal Diterima, Status Diterima");
	FacilityBenefitsBoothAccess.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#rspan");
	FacilityBenefitsBoothAccess.setInitWidths("60,200,300,100,100");
	FacilityBenefitsBoothAccess.setColAlign("center,left,left,center,center");
	FacilityBenefitsBoothAccess.setColSorting("int,str,str,str,int");
	FacilityBenefitsBoothAccess.setColTypes("ro,ro,ro,dhxCalendar,ch");
	FacilityBenefitsBoothAccess.enableSmartRendering(true, 150);
    FacilityBenefitsBoothAccess.setDateFormat("%Y-%m-%d"); 
	PFacilityBenefitsBoothAccess = new dataProcessor("partner-business-facility-update?Task={{ $id }}&wh={{ $wh->warehouse_task_id }}");
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
	FacilityBenefitsBoothSupport.setHeader("No, Deskripsi, Detail, Tanggal Diterima, Status Diterima");
	FacilityBenefitsBoothSupport.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#rspan");
	FacilityBenefitsBoothSupport.setInitWidths("60,200,300,100,100");
	FacilityBenefitsBoothSupport.setColAlign("center,left,left,center,center");
	FacilityBenefitsBoothSupport.setColSorting("int,str,str,str,int");
	FacilityBenefitsBoothSupport.setColTypes("ro,ro,ro,dhxCalendar,ch");
	FacilityBenefitsBoothSupport.enableSmartRendering(true, 150);
    FacilityBenefitsBoothSupport.setDateFormat("%Y-%m-%d"); 
	PFacilityBenefitsBoothSupport = new dataProcessor("partner-business-facility-update?Task={{ $id }}&wh={{ $wh->warehouse_task_id }}");
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
		FacilityBenefitsBooth.clearAndLoad('xml-partner-business-facility-booth-inventory?Task={{ $id }}&wh={{ $wh->warehouse_task_id }}');
	}

    function refreshdataboothaccess() {
		FacilityBenefitsBoothAccess.clearAndLoad('xml-partner-business-facility-booth-access?Task={{ $id }}&wh={{ $wh->warehouse_task_id }}');
    }
    
    function refreshdataboothsupport() {
		FacilityBenefitsBoothSupport.clearAndLoad('xml-partner-business-facility-booth-support?Task={{ $id }}&wh={{ $wh->warehouse_task_id }}');
    }
    
    function submitDataBooth()
    {
        let inventory = FacilityBenefitsBooth.getChangedRows().split(',');
        for (let i = 0; i < inventory.length; i++) {
            const ele = inventory[i];
            let a = FacilityBenefitsBooth.cells(ele, 3).getValue();
            if(a == '1') {
                let b = FacilityBenefitsBooth.cells(ele, 2).getValue();
                if (b == '') {
                    alert('Please fill in the sent date for inventory ' + FacilityBenefitsBooth.cells(ele, 1).getValue());
                    return false;
                }
            }
        }

        PFacilityBenefitsBooth.sendData();
    }
    
    function submitDataBoothAccess()
    {
        let inventory = FacilityBenefitsBoothAccess.getChangedRows().split(',');
        for (let i = 0; i < inventory.length; i++) {
            const ele = inventory[i];
            let a = FacilityBenefitsBoothAccess.cells(ele, 4).getValue();
            if(a == '1') {
                let b = FacilityBenefitsBoothAccess.cells(ele, 3).getValue();
                if (b == '') {
                    alert('Please fill in the sent date for inventory ' + FacilityBenefitsBoothAccess.cells(ele, 1).getValue());
                    return false;
                }
            }
        }

        PFacilityBenefitsBoothAccess.sendData();
    }
    
    function submitDataBoothSupport()
    {
        let inventory = FacilityBenefitsBoothSupport.getChangedRows().split(',');
        for (let i = 0; i < inventory.length; i++) {
            const ele = inventory[i];
            let a = FacilityBenefitsBoothSupport.cells(ele, 4).getValue();
            if(a == '1') {
                let b = FacilityBenefitsBoothSupport.cells(ele, 3).getValue();
                if (b == '') {
                    alert('Please fill in the sent date for support ' + FacilityBenefitsBoothSupport.cells(ele, 1).getValue());
                    return false;
                }
            }
        }

        PFacilityBenefitsBoothSupport.sendData();
    }
</script>
@endsection