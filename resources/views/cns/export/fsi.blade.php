<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

?>

@extends('layout.mainlayout-cns')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Partner</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="/">Final Shipment Instruction</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Final Shipment Instruction List</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">FINAL SHIPMENT INSTRUCTION LIST</div>
	<div class="card">
		<div class="card-body">
			<div class="mb-2">
				Periode : <input type="date" id="tgl1" name="tgl1" value="{{ date('Y-m-01', strtotime('-7 days')) }}" class="form-control-sm" />
				s/d <input type="date" id="tgl2" name="tgl2" value="{{ date('Y-m-d') }}" class="form-control-sm" />
				<button onclick="refreshdata()" class="btn btn-primary p-2">Refresh</button>
			</div>
			<div id="ShipmentInstruction" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="newSchedule" name="newSchedule" value="New Final SI" class="btn btn-primary mt-2 mr-2" onclick="ShipmentInstructionNewBtn()" />
				<input type="button" id="DetailSchedule" name="DetailSchedule" value="Detail Final SI" class="btn btn-info mt-2 mr-2" onclick="ShipmentInstructionKlik()" />
				<input type="button" id="printSI" name="printSI" value="Print Final SI" class="btn btn-secondary mt-2 mr-2" onclick="PrintSI()" />
				<input type="text" name="PL_Date" id="PL_Date" value="{{ date("Y-m-d") }}" class="form-control w-25 mt-2 mr-2" placeholder="PL Date" style="display:none;" readonly />
				<input type="button" id="createPL" name="createPL" value="Create Packing List" class="btn btn-success mt-2 mr-2" onclick="CreatePL()" />
				<input type="text" name="CI_Date" id="CI_Date" value="{{ date("Y-m-d") }}" class="form-control w-25 mt-2 mr-2" placeholder="CI Date" style="display:none;" readonly />
				<input type="button" id="createCI" name="createCI" value="Create Commercial Invoice" class="btn btn-success mt-2 mr-2" onclick="CreateCI()" />
				<input type="button" id="ekselShipmentInstructionDet" name="ekselShipmentInstructionDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="ShipmentInstruction.toExcel('./export/generate.php');" />
			</div>
		</div>
	</div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

</div>

<script>
	var myCalendarMain;
	myCalendarMain = new dhtmlXCalendarObject(["PL_Date", 'CI_Date']);
	myCalendarMain.setDateFormat("%Y-%m-%d");
	function CreatePL() {
		ShipmentInstructionID = ShipmentInstruction.getSelectedId();
		if (ShipmentInstructionID != null) {
			let display = $('#PL_Date').css('display');
			if (display == 'none') {
				$('#PL_Date').css('display', 'inline-block');
			} else {
				$('#PL_Date').css('display', 'none');
				LoadM('DivProcess', 'cns-export-final-shipment-instruction-create-pl?Task=' + ShipmentInstruction.cells(ShipmentInstructionID, 1).getValue() + '&PL_Date=' + $('#PL_Date').val());

				setTimeout(() => {
					refreshdata();
				}, 500);
			}
		}else {
			alert("Pilih baris data");
		}
		return true;
	 }
	function CreateCI() {
		ShipmentInstructionID = ShipmentInstruction.getSelectedId();
		if (ShipmentInstructionID != null) {
			LoadM('DivProcess', 'cns-export-final-shipment-instruction-create-ci?Task=' + ShipmentInstructionID);
		}else {
			alert("Pilih baris data");
		}
		return true;
	 }
	function PrintSI()
	 {
		ShipmentInstructionID = ShipmentInstruction.getSelectedId();
		if (ShipmentInstructionID != null) {
			window.open('cns-export-final-shipment-instruction-print?Task=' + ShipmentInstruction.cells(ShipmentInstructionID, 1).getValue(), '_blank');
			// window.open('assets/export_fsi.docx', '_blank');
		}else {
			alert("Pilih baris data");
		}
		return true;
	 }
	function ShipmentInstructionNewBtn() {

		LoadM('divlistOpportunity', 'cns-export-final-shipment-instruction-new');
		return true;
	}
	function ShipmentInstructionKlik() {

		ShipmentInstructionID = ShipmentInstruction.getSelectedId();
		if (ShipmentInstructionID != null) {
			LoadM('divlistOpportunity', 'cns-export-final-shipment-instruction-detail?Task=' + ShipmentInstructionID);
		}else {
			alert("Pilih baris data");
		}
		return true;
	}
	ShipmentInstruction = new dhtmlXGridObject('ShipmentInstruction');
	ShipmentInstruction.setImagePath("./dhtmlx/imgs/");
	ShipmentInstruction.setHeader("No,Final SI No, Final SI Date, SI No, Kontainer (Seal), Tare, Netto (Kg), Netto (MT), Gross, Gross (MT), Note");
	ShipmentInstruction.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// ShipmentInstruction.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	ShipmentInstruction.setInitWidths("60,150,100,150,150,150,100,100,100,100,100,auto");
	ShipmentInstruction.setColAlign("center,left,center,left,left,left,left,left,left,left,left,left,left,left");
	ShipmentInstruction.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str");
	ShipmentInstruction.setColTypes("ron,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	ShipmentInstruction.enableSmartRendering(true, 150);
	// ShipmentInstruction.setNumberFormat("0,000", 7, ".", ",");
	ShipmentInstruction.attachEvent("onRowDblClicked", ShipmentInstructionKlik);
	ShipmentInstruction.setSkin("dhx_skyblue");
	ShipmentInstruction.init();
	refreshdata();

	function refreshdata() {
		ShipmentInstruction.clearAndLoad('xml-cns-export-final-shipment-instruction?tgl1=' + $('#tgl1').val() + "&tgl2=" + $('#tgl2').val());

	}
</script>
@endsection