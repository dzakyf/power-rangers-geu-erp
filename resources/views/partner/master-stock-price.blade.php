<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

$pic = DB::table('users')->where('Location', $LSP)->where('Priv_ID', 'SALES')->get();

?>

@extends('layout.mainlayout-bp')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Partner</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="#">Inventory</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Master Stock Price</span>
		</li>
	</ol>
</div>

<div class="row">
	<div id="MasterPriceProduct" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
	<div>
		<input type="button" id="ekselMasterPriceProductDet" name="ekselMasterPriceProductDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="MasterPriceProduct.toExcel('./export/generate.php');" />
		<input type="button" id="addRow" name="addRow" value="Add Row" class="btn btn-info mt-2 mr-2" onclick="addRow()" />
		<input type="button" id="deleteRow" name="deleteRow" value="Delete Row" class="btn btn-danger mt-2 mr-2" onclick="deleteRow()" />
		<input type="button" id="savePrice" name="savePrice" value="Save Price" class="btn btn-success mt-2 mr-2" onclick="savePrice()" />

		<input type="file" id="csvFileInput" accept=".csv" style="display: none;">
	</div>
</div>
<div id="divlistOpportunity"></div>
<div id="divlistProjectList"></div>
<script>
	function MasterPriceProductKlik() {

		MasterPriceProductID = MasterPriceProduct.getSelectedId();
		$("#divlistOpportunity").show();
		if (MasterPriceProductID != '') {
			$("#divlistProjectList").hide();
			LoadM('divlistOpportunity', 'partner-master-stock-price-detail?ID=' + MasterPriceProductID);
		}
		return true;
	}
	MasterPriceProduct = new dhtmlXGridObject('MasterPriceProduct');
	MasterPriceProduct.setImagePath("./dhtmlx/imgs/");
	MasterPriceProduct.setHeader("No, Tanggal Harga, Harga, Dibuat oleh, Dibuat tanggal");
	MasterPriceProduct.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#rspan");
	// MasterPriceProduct.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	MasterPriceProduct.setInitWidths("60,100,100,100,100");
	MasterPriceProduct.setColAlign("center,center,right,center,center");
	MasterPriceProduct.setColSorting("int,str,int,str,str");
	MasterPriceProduct.setColTypes("ron,dhxCalendar,edn,ro,ro");
	MasterPriceProduct.enableSmartRendering(true, 150);
	MasterPriceProduct.setNumberFormat("0,000", 2, ".", ",");
	MasterPriceProduct.setDateFormat("%Y-%m-%d");
	PMasterPriceProduct = new dataProcessor("partner-master-stock-price-update");
	PMasterPriceProduct.setTransactionMode({
		mode: "POST",
		total: true,
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},

	})

	PMasterPriceProduct.setTransactionMode("POST", true);
	PMasterPriceProduct.setUpdateMode("Off");
	PMasterPriceProduct.init(MasterPriceProduct);
	PMasterPriceProduct.attachEvent("onBeforeDataSending", function(id, state, data) {
		// Modify or add payload data here
		// console.log(id)
		return true; // Return true to continue processing
	});
	PMasterPriceProduct.attachEvent("onAfterUpdateFinish", function(id, state, data) {
		alert("Data Successfully Submitted!");
		setTimeout(function() {
			refreshdata();
		}, 500);
	});
	MasterPriceProduct.setSkin("dhx_skyblue");
	MasterPriceProduct.init();
	refreshdata();

	function refreshdata() {
		MasterPriceProduct.clearAndLoad('xml-partner-master-stock-price');
	}

	function deleteRow() {
		dhtmlx.confirm('Delete this Row?', 'Opportunity', function(r) {

			if (r == true) {
				MasterPriceProduct.deleteSelectedRows();
				PMasterPriceProduct.sendData();
				alert('Data Successfully Deleted!');
				setTimeout(function() {
					refreshdata();
				}, 1000);
			}
		})
	}

	function addRow() {
		let uid = MasterPriceProduct.uid();
		let num = MasterPriceProduct.getRowsNum() + 1;
		MasterPriceProduct.addRow(uid, [num, "{{ date('Y-m-d') }}", '0', '', '']);
		MasterPriceProduct.selectRow(uid);
	}

	function savePrice() {
		PMasterPriceProduct.sendData();
		refreshdata();
	}
</script>
@endsection