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
@include('cns.partials.page-header', [
	'title' => 'Packing List (Final)',
	'breadcrumbs' => [
		['label' => 'Export Management'],
		['label' => 'Packing List (Final)'],
	],
])

<div class="card cns-card">
	<div class="card-header">
		<h5 class="card-title mb-0">Packing List (Final)</h5>
		<div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
			<input type="button" id="newSchedule" name="newSchedule" value="New PL" class="btn btn-sm btn-primary" onclick="ShipmentInstructionNewBtn()" />
			<input type="button" id="DetailSchedule" name="DetailSchedule" value="Detail PL" class="btn btn-sm btn-info" onclick="ShipmentInstructionKlik()" />
			<input type="button" id="printPL" name="printPL" value="Print PL" class="btn btn-sm btn-secondary" onclick="PrintPL()" />
			<input type="button" id="ekselShipmentInstructionDet" name="ekselShipmentInstructionDet" class="btn btn-sm btn-warning" value="Export Excel" onclick="ShipmentInstruction.toExcel('./export/generate.php');" />
			<button type="button" onclick="refreshdata()" class="btn btn-sm btn-outline-secondary">Refresh</button>
		</div>
	</div>
	<div class="card-body">
		<div class="cns-filter-bar">
			<div>
				<label for="tgl1" class="d-block mb-1"><small>Dari Tanggal</small></label>
				<input type="date" id="tgl1" name="tgl1" value="{{ date('Y-m-01', strtotime('-7 days')) }}" class="form-control form-control-sm" style="width:auto;" />
			</div>
			<div>
				<label for="tgl2" class="d-block mb-1"><small>Sampai Tanggal</small></label>
				<input type="date" id="tgl2" name="tgl2" value="{{ date('Y-m-d') }}" class="form-control form-control-sm" style="width:auto;" />
			</div>
			<div>
				<button type="button" onclick="refreshdata()" class="btn btn-sm btn-primary">Filter</button>
			</div>
		</div>
		<div class="cns-grid-wrapper">
			<div id="ShipmentInstruction" class="cns-grid" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		</div>
	</div>
</div>
<div id="divlistOpportunity"></div>
<div id="DivProcess"></div>

<script>
	function PrintPL()
	 {
		ShipmentInstructionID = ShipmentInstruction.getSelectedId();
		if (ShipmentInstructionID != null) {
			window.open('cns-export-packing-list-print?Task=' + ShipmentInstruction.cells(ShipmentInstructionID, 1).getValue(), '_blank');
			// window.open('assets/export_pl.docx', '_blank');
		}else {
			swal('Perhatian', 'Pilih baris data', 'warning');
		}
		return true;
	 }
	function ShipmentInstructionNewBtn() {

		LoadM('divlistOpportunity', 'cns-export-packing-list-new');
		return true;
	}
	function ShipmentInstructionKlik() {

		ShipmentInstructionID = ShipmentInstruction.getSelectedId();
		if (ShipmentInstructionID != null) {
			LoadM('divlistOpportunity', 'cns-export-packing-list-detail?Task=' + ShipmentInstructionID);
		}else {
			swal('Perhatian', 'Pilih baris data', 'warning');
		}
		return true;
	}
	ShipmentInstruction = new dhtmlXGridObject('ShipmentInstruction');
	ShipmentInstruction.setImagePath("./dhtmlx/imgs/");
	ShipmentInstruction.setHeader("No,Packing List No, Final SI No,Packing List Date, Kontainer (Seal), Tare, Netto (Kg), Netto (MT), Gross, Gross (MT), Note");
	ShipmentInstruction.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// ShipmentInstruction.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	ShipmentInstruction.setInitWidths("60,150,150,100,150,150,100,100,100,100,100,auto");
	ShipmentInstruction.setColAlign("center,left,left,center,left,left,left,left,left,left,left,left,left,left");
	ShipmentInstruction.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str");
	ShipmentInstruction.setColTypes("ron,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	ShipmentInstruction.enableSmartRendering(true, 150);
	// ShipmentInstruction.setNumberFormat("0,000", 7, ".", ",");
	ShipmentInstruction.attachEvent("onRowDblClicked", ShipmentInstructionKlik);
	ShipmentInstruction.setSkin("dhx_skyblue");
	ShipmentInstruction.init();
	refreshdata();

	function refreshdata() {
		ShipmentInstruction.clearAndLoad('xml-cns-export-packing-list?tgl1=' + $('#tgl1').val() + "&tgl2=" + $('#tgl2').val());
	}
</script>
@endsection