<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

$pic = DB::table('users')->where('Location', $LSP)->where('Priv_ID', 'SALES')->get();

?>

@extends('layout.mainlayout-mitra')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Partner</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="#">Opportunity</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Letter of Assignment</span>
		</li>
	</ol>
</div>

<div class="row">
	<div id="AssignSales" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
	<div>
        <input type="button" id="ekselAssignSalesDet" name="ekselAssignSalesDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="AssignSales.toExcel('./export/generate.php');" />
		<input type="button" id="detailassign" name="detailassign" value="Detail Assignment Leads" class="btn btn-info mt-2 mr-2" onclick="detailassign()" />
		<input type="button" id="assignToSales" name="assignToSales" value="Re-Assign to Sales" class="btn btn-primary mt-2 mr-2" onclick="assignToSales()"/>
		<div id="divAssignToSales" style="display: none;">
			<select name="Sales" id="Sales" class="mr-2">
				<option value="">-- Pilih Sales --</option>
				@foreach($pic as $p)
					<option value="{{ $p->username }}">{{ $p->Name }}</option>
				@endforeach
			</select>
			<input type="button" id="assign" name="assign" value="Assign" class="btn btn-success mt-2 mr-2" onclick="assign()" />

		</div>
	</div>
	<div class="mt-3" id="divlistAssign"></div>
</div>
<script>
	function assignToSales() {
		if ($('#divAssignToSales').css('display') == 'none') {
			$('#divAssignToSales').css('display', 'inline-block');
		}else {
			$('#divAssignToSales').css('display', 'none');
			$('#Sales').val('');
		}
	}
	function assign() {
		let sales = $('#Sales').val()
		let ids = AssignSales.getChangedRows().split(',').filter(id => id != '');
		console.log(ids);

		let checker = 0;
		for (let i = 0; i < ids.length; i++) {
			const ele = ids[i];
			console.log(AssignSales.cells(ele, 1).getValue());
			if (AssignSales.cells(ele, 1).getValue() == 1) {
				checker++;
			}
		}

		if (checker == 0 || ids.length == 0) {
			alert('Tidak dapat assign ke sales, pastikan checklist ada dicentang!');
			return;
		}

		if (sales == '') {
			alert("Pilih sales terlebih dahulu!")
			return
		}
		PAssignSales.sendData();
	}
	function AssignSalesKlik() {

		AssignSalesID = AssignSales.getSelectedId();
		$("#divlistAssign").show();
		if (AssignSalesID != '') {
			$("#divlistProjectList").hide();
			LoadM('divlistAssign', 'partner-reassign-sales-detail?ID=' + AssignSales.cells(AssignSalesID, 2).getValue());
		}
		return true;
	}
	AssignSales = new dhtmlXGridObject('AssignSales');
	AssignSales.setImagePath("./dhtmlx/imgs/");
	AssignSales.setHeader("No, check list, Assignment ID, Status Assignment,Sales Name,Propose Date, Proposed By");
	AssignSales.attachHeader("#rspan,#master_checkbox,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// AssignSales.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	AssignSales.setInitWidths("60,80,180,100,150,150,150");
	AssignSales.setColAlign("center,center,center,center,left,center,center");
	AssignSales.setColSorting("int,int,str,str,str,str,str");
	AssignSales.setColTypes("ro,ch,ro,ro,ro,ro,ro");
	AssignSales.enableSmartRendering(true, 150);
	// AssignSales.setNumberFormat("0,000", 7, ".", ",");
	PAssignSales = new dataProcessor("partner-reassign-sales-update");
	PAssignSales.setTransactionMode({
		mode: "POST",
		total: true,
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},

	})

	PAssignSales.setTransactionMode("POST", true);
	PAssignSales.setUpdateMode("Off");
	PAssignSales.init(AssignSales);
    PAssignSales.attachEvent("onBeforeDataSending", function (id,state, data) {
        // Modify or add payload data here
		let sales = $('#Sales').val()
        // console.log(data)
        data.customPayload = {
            // Add your payload properties dynamically
            sales: sales,
            // ... and so on
        };
        // console.log(id)
        return true; // Return true to continue processing
    });
    PAssignSales.attachEvent("onAfterUpdateFinish", function (id,state, data) {
        alert("Data Successfully Submitted!");
        setTimeout(function() {
            refreshdata();
			assignToSales();
        }, 500);
    });
	AssignSales.attachEvent("onRowDblClicked", AssignSalesKlik);
	AssignSales.setSkin("dhx_skyblue");
	AssignSales.init();
	refreshdata();

	function refreshdata() {
		AssignSales.clearAndLoad('xml-partner-reassign-sales');

	}

	function detailassign() {
		AssignSalesKlik();
	}
</script>
@endsection