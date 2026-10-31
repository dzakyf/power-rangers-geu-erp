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
			<span class="active">Order Equipment</span>
		</li>
	</ol>
</div>

<div class="row">
	<div id="OrderEquipment" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
	<div>
        <input type="button" id="ekselOrderEquipmentDet" name="ekselOrderEquipmentDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="OrderEquipment.toExcel('./export/generate.php');" />
		<input type="button" id="detailEqipment" name="detailEqipment" value="Detail Eqipment" class="btn btn-success mt-2 mr-2" onclick="OrderEquipmentKlik()" />
		{{-- <input type="button" id="detailBooth" name="detailBooth" value="Detail Booth" class="btn btn-success mt-2 mr-2" onclick="OrderEquipmentKlikBooth()" />
		<input type="button" id="detailMobil" name="detailMobil" value="Detail Mobil" class="btn btn-success mt-2 mr-2" onclick="OrderEquipmentKlikMobil()" /> --}}
	</div>
</div>
<div id="divlistOpportunity"></div>
<div id="divlistProjectList"></div>
<script>
	function OrderEquipmentKlikBooth() {

		OrderEquipmentID = OrderEquipment.getSelectedId();
		if (OrderEquipmentID != '') {
			LoadM('divlistOpportunity', 'partner-order-equipment-detail-booth?ID=' + OrderEquipmentID);
		}
		return true;
	}
	function OrderEquipmentKlikMobil() {

		OrderEquipmentID = OrderEquipment.getSelectedId();
		if (OrderEquipmentID != '') {
			LoadM('divlistOpportunity', 'partner-order-equipment-detail-mobil?ID=' + OrderEquipmentID);
		}
		return true;
	}
	function OrderEquipmentKlik() {

		let OrderEquipmentID = OrderEquipment.getSelectedId();
		let hub = OrderEquipment.cells(OrderEquipmentID, 5).getValue();
		if (OrderEquipmentID != '') {
			if (hub == 'booth') {
				LoadM('divlistOpportunity', 'partner-order-equipment-detail-booth?ID=' + OrderEquipmentID);
			}else {
				LoadM('divlistOpportunity', 'partner-order-equipment-detail-mobil?ID=' + OrderEquipmentID);
			}
		}
		return true;
	}
	OrderEquipment = new dhtmlXGridObject('OrderEquipment');
	OrderEquipment.setImagePath("./dhtmlx/imgs/");
	OrderEquipment.setHeader("No,Gudang ID, Lokasi Gudang, Nama Mitra, Kapasitas, Tipe HUB, Status Gudang,Dibuat Oleh");
	OrderEquipment.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// OrderEquipment.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	OrderEquipment.setInitWidths("60,100,150,200,100,100,100,200");
	OrderEquipment.setColAlign("center,left,left,left,right,center,center,left");
	OrderEquipment.setColSorting("int,str,str,str,int,str,str,str");
	OrderEquipment.setColTypes("ron,ro,ro,ro,ron,ro,ro,ro");
	OrderEquipment.enableSmartRendering(true, 150);
	OrderEquipment.attachEvent("onRowDblClicked", OrderEquipmentKlik);
	OrderEquipment.setNumberFormat("0,000", 4, ".", ",");
	OrderEquipment.setSkin("dhx_skyblue");
	OrderEquipment.init();
	refreshdata();

	function refreshdata() {
		OrderEquipment.clearAndLoad('xml-partner-asset-management');

	}

	function delsOpportunity() {
		dhtmlx.confirm('Cancel this Opportunity?', 'Opportunity', function(r) {

			if (r == true) {
				OrderEquipment.deleteSelectedRows();
				POrderEquipment.sendData();
				alert('Data Successfully Deleted!');
				setTimeout(function() {
					refreshdata();
				}, 1000);
			}
		})
	}


	function detailBP() {
		OrderEquipmentKlik();
		// dhtmlx.confirm('Submit this Opportunity FU?', 'Opportunity', function(r) {

		// 	if (r == true) {
		// 		POrderEquipment.sendData();
		// 		alert('Data Successfully Submitted!');
		// 		setTimeout(function() {
		// 			refreshdata();
		// 		}, 1000);
		// 	}
		// })
	}


	function UpdateGrid_Opportunity() {
		var out = 0;
		for (var i = 0; i < OrderEquipment.getRowsNum(); i++) {
			rowIdMov = OrderEquipment.getRowId(i);
			OrderEquipmentProc.setUpdated(rowIdMov, true);
		}
		return out;
	}

	function SubmitGrid_Opportunity() {
		OrderEquipmentProc.sendData();
		alert('Data Successfully Updated!');
		OrderEquipment.clearAll();
		refreshdata();
	}

	function EditOpportunity() {
		taskid = OrderEquipment.cells(OrderEquipment.getSelectedId(), 1).getValue();
		LoadM('divlistOpportunity', 'sales-opportunity-lists-detail?ID=' + taskid);
	}

	function LihatDetailOrderEquipment() {
		taskid = OrderEquipment.cells(OrderEquipment.getSelectedId(), 1).getValue();
		LoadM('divlistOpportunity', 'sales-opportunity-activity?taskid=' + taskid);
	}

	function listDoc() {

		DocumentListsDetailID = OrderEquipment.cells(OrderEquipment.getSelectedId(),12).getValue();
		
			$("#divlistProjectList").show();

		if (DocumentListsDetailID !=''){
			$("#divlistOpportunity").hide();
			LoadM('divlistProjectList','project-documents-detail?Task_ID=' + DocumentListsDetailID);
		}
		return true;
	}
</script>
@endsection