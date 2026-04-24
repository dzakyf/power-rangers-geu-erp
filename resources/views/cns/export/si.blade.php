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
			<a href="/">Shipment Instruction</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Shipment Instruction List</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">SHIPMENT INSTRUCTION LIST</div>
	<div class="card">
		<div class="card-body">
			<div id="ShipmentInstruction" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="ekselShipmentInstructionDet" name="ekselShipmentInstructionDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="ShipmentInstruction.toExcel('./export/generate.php');" />
				<input type="button" id="newSchedule" name="newSchedule" value="New SI" class="btn btn-primary mt-2 mr-2" onclick="ShipmentInstructionNewBtn()" />
				<input type="button" id="DetailSchedule" name="DetailSchedule" value="Detail SI" class="btn btn-info mt-2 mr-2" onclick="ShipmentInstructionKlik()" />
				<input type="button" id="printSI" name="printSI" value="Print SI" class="btn btn-secondary mt-2 mr-2" onclick="PrintSI()" />
				<input type="button" id="createToFSI" name="createToFSI" value="Create Final SI" class="btn btn-success mt-2 mr-2" onclick="CreateToFSI()" />
			</div>
		</div>
	</div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

</div>

<script>
	function PrintSI()
	{
		ShipmentInstructionID = ShipmentInstruction.getSelectedId();
		if (ShipmentInstructionID != null) {
			window.open('cns-export-shipment-instruction-print?Task=' + ShipmentInstruction.cells(ShipmentInstructionID, 2).getValue(), '_blank');
			// window.open('assets/export-si.docx', '_blank');
		}else {
			alert("Pilih baris data");
		}
		return true;
	}
	function CreateToFSI()
	{
		ShipmentInstructionID = ShipmentInstruction.getSelectedId();
		if (ShipmentInstructionID != null) {
			LoadM('divlistOpportunity', 'cns-export-shipment-instruction-generate-fsi?Task=' + ShipmentInstruction.cells(ShipmentInstructionID, 2).getValue());
			refreshdata();
		}else {
			alert("Pilih baris data");
		}
		return true;
	}
	function ShipmentInstructionNewBtn() {

		LoadM('divlistOpportunity', 'cns-export-shipment-instruction-new');
		return true;
	}
	function ShipmentInstructionKlik() {

		ShipmentInstructionID = ShipmentInstruction.getSelectedId();
		if (ShipmentInstructionID != null) {
			LoadM('divlistOpportunity', 'cns-export-shipment-instruction-detail?Task=' + ShipmentInstructionID);
		}else {
			alert("Pilih baris data");
		}
		return true;
	}
	ShipmentInstruction = new dhtmlXGridObject('ShipmentInstruction');
	ShipmentInstruction.setImagePath("./dhtmlx/imgs/");
	ShipmentInstruction.setHeader("No,Order (DO)/Booking Confirmation (BC), SI No, Kontainer (Seal), Tare, Netto (Kg), Netto (MT), Gross, Gross (MT), Note");
	ShipmentInstruction.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// ShipmentInstruction.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	ShipmentInstruction.setInitWidths("60,150,150,150,150,100,100,100,100,100,auto");
	ShipmentInstruction.setColAlign("center,left,left,left,left,left,left,left,left,left,left,left,left");
	ShipmentInstruction.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str");
	ShipmentInstruction.setColTypes("ron,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	ShipmentInstruction.enableSmartRendering(true, 150);
	// ShipmentInstruction.setNumberFormat("0,000", 7, ".", ",");
	ShipmentInstruction.attachEvent("onRowDblClicked", ShipmentInstructionKlik);
	ShipmentInstruction.setSkin("dhx_skyblue");
	ShipmentInstruction.init();
	refreshdata();

	function refreshdata() {
		ShipmentInstruction.clearAndLoad('xml-cns-export-shipment-instruction');

	}
</script>
@endsection