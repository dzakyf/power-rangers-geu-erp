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

@extends('layout.mainlayout-mitra')

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
			<span class="active">Asset Management</span>
		</li>
	</ol>
</div>

<div class="row">
	<div id="FacilityBenefits" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
	<div>
        <input type="button" id="ekselFacilityBenefitsDet" name="ekselFacilityBenefitsDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="FacilityBenefits.toExcel('./export/generate.php');" />
		<input type="button" id="detailFacility" name="detailFacility" value="Detail Facility" class="btn btn-success mt-2 mr-2" onclick="FacilityBenefitsKlik()" />
		<input type="button" id="detailBooth" name="detailBooth" value="Detail Booth" class="btn btn-success mt-2 mr-2" onclick="FacilityBenefitsKlikBooth()" />
		<input type="button" id="detailMobil" name="detailMobil" value="Detail Mobil" class="btn btn-success mt-2 mr-2" onclick="FacilityBenefitsKlikMobil()" />
	</div>
</div>
<div id="divlistOpportunity"></div>
<div id="divlistProjectList"></div>
<script>
	function FacilityBenefitsKlikBooth() {

		FacilityBenefitsID = FacilityBenefits.getSelectedId();
		if (FacilityBenefitsID != '') {
			LoadM('divlistOpportunity', 'partner-asset-management-detail-booth?ID=' + FacilityBenefitsID);
		}
		return true;
	}
	function FacilityBenefitsKlikMobil() {

		FacilityBenefitsID = FacilityBenefits.getSelectedId();
		if (FacilityBenefitsID != '') {
			LoadM('divlistOpportunity', 'partner-asset-management-detail-mobil?ID=' + FacilityBenefitsID);
		}
		return true;
	}
	function FacilityBenefitsKlik() {

		let FacilityBenefitsID = FacilityBenefits.getSelectedId();
		let hub = FacilityBenefits.cells(FacilityBenefitsID, 5).getValue();
		if (FacilityBenefitsID != '') {
			if (hub == 'booth') {
				LoadM('divlistOpportunity', 'partner-asset-management-detail-booth?ID=' + FacilityBenefitsID);
			}else {
				LoadM('divlistOpportunity', 'partner-asset-management-detail-mobil?ID=' + FacilityBenefitsID);
			}
		}
		return true;
	}
	FacilityBenefits = new dhtmlXGridObject('FacilityBenefits');
	FacilityBenefits.setImagePath("./dhtmlx/imgs/");
	FacilityBenefits.setHeader("No,Gudang ID, Lokasi Gudang, Nama Mitra, Kapasitas, Tipe HUB, Status Gudang,Dibuat Oleh");
	FacilityBenefits.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// FacilityBenefits.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	FacilityBenefits.setInitWidths("60,100,150,200,100,100,100,200");
	FacilityBenefits.setColAlign("center,left,left,left,right,center,center,left");
	FacilityBenefits.setColSorting("int,str,str,str,int,str,str,str");
	FacilityBenefits.setColTypes("ron,ro,ro,ro,ron,ro,ro,ro");
	FacilityBenefits.enableSmartRendering(true, 150);
	FacilityBenefits.attachEvent("onRowDblClicked", FacilityBenefitsKlik);
	FacilityBenefits.setNumberFormat("0,000", 4, ".", ",");
	FacilityBenefits.setSkin("dhx_skyblue");
	FacilityBenefits.init();
	refreshdata();

	function refreshdata() {
		FacilityBenefits.clearAndLoad('xml-partner-asset-management');

	}

	function delsOpportunity() {
		dhtmlx.confirm('Cancel this Opportunity?', 'Opportunity', function(r) {

			if (r == true) {
				FacilityBenefits.deleteSelectedRows();
				PFacilityBenefits.sendData();
				alert('Data Successfully Deleted!');
				setTimeout(function() {
					refreshdata();
				}, 1000);
			}
		})
	}


	function detailBP() {
		FacilityBenefitsKlik();
		// dhtmlx.confirm('Submit this Opportunity FU?', 'Opportunity', function(r) {

		// 	if (r == true) {
		// 		PFacilityBenefits.sendData();
		// 		alert('Data Successfully Submitted!');
		// 		setTimeout(function() {
		// 			refreshdata();
		// 		}, 1000);
		// 	}
		// })
	}


	function UpdateGrid_Opportunity() {
		var out = 0;
		for (var i = 0; i < FacilityBenefits.getRowsNum(); i++) {
			rowIdMov = FacilityBenefits.getRowId(i);
			FacilityBenefitsProc.setUpdated(rowIdMov, true);
		}
		return out;
	}

	function SubmitGrid_Opportunity() {
		FacilityBenefitsProc.sendData();
		alert('Data Successfully Updated!');
		FacilityBenefits.clearAll();
		refreshdata();
	}

	function EditOpportunity() {
		taskid = FacilityBenefits.cells(FacilityBenefits.getSelectedId(), 1).getValue();
		LoadM('divlistOpportunity', 'sales-opportunity-lists-detail?ID=' + taskid);
	}

	function LihatDetailFacilityBenefits() {
		taskid = FacilityBenefits.cells(FacilityBenefits.getSelectedId(), 1).getValue();
		LoadM('divlistOpportunity', 'sales-opportunity-activity?taskid=' + taskid);
	}

	function listDoc() {

		DocumentListsDetailID = FacilityBenefits.cells(FacilityBenefits.getSelectedId(),12).getValue();
		
			$("#divlistProjectList").show();

		if (DocumentListsDetailID !=''){
			$("#divlistOpportunity").hide();
			LoadM('divlistProjectList','project-documents-detail?Task_ID=' + DocumentListsDetailID);
		}
		return true;
	}
</script>
@endsection