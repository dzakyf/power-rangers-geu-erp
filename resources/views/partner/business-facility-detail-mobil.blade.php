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
	FacilityBenefitsMobil.setHeader("No, Deskripsi, Detail,Tanggal Diterima, Status Diterima, Tipe Asset, Qty, Satuan Harga, Total, Tanggal Order");
	FacilityBenefitsMobil.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	FacilityBenefitsMobil.setInitWidths("60,200,200,100,100,100,100,100,100,100");
	FacilityBenefitsMobil.setColAlign("center,left,left,center,center,center,right,right,right,center");
	FacilityBenefitsMobil.setColSorting("int,str,str,str,int,str,int,int,int,str");
	FacilityBenefitsMobil.setColTypes("ro,ro,ro,dhxCalendar,ch,ro,ron,ron,ron,ro");
	FacilityBenefitsMobil.enableSmartRendering(true, 150);
    FacilityBenefitsMobil.setNumberFormat("0,000", 6, ".", ",");
    FacilityBenefitsMobil.setNumberFormat("0,000", 7, ".", ",");
    FacilityBenefitsMobil.setNumberFormat("0,000", 8, ".", ",");
    FacilityBenefitsMobil.attachEvent("onEditCell", function (stage, rowId, cellIndex, newValue, oldValue) {
        if (cellIndex == 6 || cellIndex == 7) {
            let qty = FacilityBenefitsMobil.cells(rowId, 6).getValue();
            let price = FacilityBenefitsMobil.cells(rowId, 7).getValue();
            let total = parseFloat(qty) * parseFloat(price);
            FacilityBenefitsMobil.cells(rowId, 8).setValue(total);
        }
        return true;
    });
    FacilityBenefitsMobil.setDateFormat("%Y-%m-%d"); 
	PFacilityBenefitsMobil = new dataProcessor("partner-business-facility-update?Task={{ $id }}&wh={{ $wh->warehouse_task_id }}");
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
	FacilityBenefitsMobilAccess.setHeader("No, Deskripsi, Detail,Tanggal Diterima, Status Diterima");
	FacilityBenefitsMobilAccess.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#rspan");
	FacilityBenefitsMobilAccess.setInitWidths("60,200,300,100,100");
	FacilityBenefitsMobilAccess.setColAlign("center,left,left,center,center");
	FacilityBenefitsMobilAccess.setColSorting("int,str,str,str,int");
	FacilityBenefitsMobilAccess.setColTypes("ro,ro,ro,dhxCalendar,ch");
	FacilityBenefitsMobilAccess.enableSmartRendering(true, 150);
    FacilityBenefitsMobilAccess.setDateFormat("%Y-%m-%d"); 
	PFacilityBenefitsMobilAccess = new dataProcessor("partner-business-facility-update?Task={{ $id }}&wh={{ $wh->warehouse_task_id }}");
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
	FacilityBenefitsMobilSupport.setHeader("No, Deskripsi, Detail,Tanggal Diterima, Status Diterima");
	FacilityBenefitsMobilSupport.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#rspan");
	FacilityBenefitsMobilSupport.setInitWidths("60,200,300,100,100");
	FacilityBenefitsMobilSupport.setColAlign("center,left,left,center,center");
	FacilityBenefitsMobilSupport.setColSorting("int,str,str,str,int");
	FacilityBenefitsMobilSupport.setColTypes("ro,ro,ro,dhxCalendar,ch");
	FacilityBenefitsMobilSupport.enableSmartRendering(true, 150);
    FacilityBenefitsMobilSupport.setDateFormat("%Y-%m-%d"); 
	PFacilityBenefitsMobilSupport = new dataProcessor("partner-business-facility-update?Task={{ $id }}&wh={{ $wh->warehouse_task_id }}");
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
		FacilityBenefitsMobil.clearAndLoad('xml-partner-business-facility-mobil-inventory?Task={{ $id }}&wh={{ $wh->warehouse_task_id }}');
	}

    function refreshdatamobilaccess() {
		FacilityBenefitsMobilAccess.clearAndLoad('xml-partner-business-facility-mobil-access?Task={{ $id }}&wh={{ $wh->warehouse_task_id }}');
    }
    
    function refreshdatamobilsupport() {
		FacilityBenefitsMobilSupport.clearAndLoad('xml-partner-business-facility-mobil-support?Task={{ $id }}&wh={{ $wh->warehouse_task_id }}');
    }
    
    function submitDataMobil()
    {
        let inventory = FacilityBenefitsMobil.getChangedRows().split(',');
        for (let i = 0; i < inventory.length; i++) {
            const ele = inventory[i];
            let a = FacilityBenefitsMobil.cells(ele, 4).getValue();
            if(a == '1') {
                let b = FacilityBenefitsMobil.cells(ele, 3).getValue();
                if (b == '') {
                    alert('Please fill in the sent date for inventory ' + FacilityBenefitsMobil.cells(ele, 1).getValue());
                    return false;
                }
            }
        }

        PFacilityBenefitsMobil.sendData();
    }
    
    function submitDataMobilAccess()
    {
        let inventory = FacilityBenefitsMobilAccess.getChangedRows().split(',');
        for (let i = 0; i < inventory.length; i++) {
            const ele = inventory[i];
            let a = FacilityBenefitsMobilAccess.cells(ele, 4).getValue();
            if(a == '1') {
                let b = FacilityBenefitsMobilAccess.cells(ele, 3).getValue();
                if (b == '') {
                    alert('Please fill in the sent date for access ' + FacilityBenefitsMobilAccess.cells(ele, 1).getValue());
                    return false;
                }
            }
        }

        PFacilityBenefitsMobilAccess.sendData();
    }
    
    function submitDataMobilSupport()
    {
        let inventory = FacilityBenefitsMobilSupport.getChangedRows().split(',');
        for (let i = 0; i < inventory.length; i++) {
            const ele = inventory[i];
            let a = FacilityBenefitsMobilSupport.cells(ele, 4).getValue();
            if(a == '1') {
                let b = FacilityBenefitsMobilSupport.cells(ele, 3).getValue();
                if (b == '') {
                    alert('Please fill in the sent date for support ' + FacilityBenefitsMobilSupport.cells(ele, 1).getValue());
                    return false;
                }
            }
        }

        PFacilityBenefitsMobilSupport.sendData();
    }
</script>
@endsection