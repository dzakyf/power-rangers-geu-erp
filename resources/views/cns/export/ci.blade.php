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
	'title' => 'Commercial Invoice',
	'breadcrumbs' => [
		['label' => 'Export Management'],
		['label' => 'Commercial Invoice'],
	],
])

<div class="card cns-card">
	<div class="card-header">
		<h5 class="card-title mb-0">Commercial Invoice List</h5>
		<div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
			<input type="button" id="newSchedule" name="newSchedule" value="New CI" class="btn btn-sm btn-primary" onclick="ShipmentInstructionNewBtn()" />
			<input type="button" id="DetailSchedule" name="DetailSchedule" value="Detail CI" class="btn btn-sm btn-info" onclick="ShipmentInstructionKlik()" />
			<input type="button" id="printCI" name="printCI" value="Print CI for BC" class="btn btn-sm btn-secondary" onclick="PrintCI()" />
			<input type="button" id="printCIBuyer" name="printCIBuyer" value="Print CI for Buyer" class="btn btn-sm btn-secondary" onclick="PrintCIBuyer()" />
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
	function PrintCI()
	 {
		ShipmentInstructionID = ShipmentInstruction.getSelectedId();
		if (ShipmentInstructionID != null) {
			window.open('cns-export-commercial-invoice-print?Task=' + ShipmentInstruction.cells(ShipmentInstructionID, 2).getValue() + "&Buyer=0", '_blank');
		}else {
			swal('Perhatian', 'Pilih baris data', 'warning');
		}
		return true;
	 }
	function PrintCIBuyer()
	 {
		ShipmentInstructionID = ShipmentInstruction.getSelectedId();
		if (ShipmentInstructionID != null) {
			window.open('cns-export-commercial-invoice-print?Task=' + ShipmentInstruction.cells(ShipmentInstructionID, 2).getValue() + "&Buyer=1", '_blank');
		}else {
			swal('Perhatian', 'Pilih baris data', 'warning');
		}
		return true;
	 }
	function ShipmentInstructionNewBtn() {

		LoadM('divlistOpportunity', 'cns-export-commercial-invoice-new');
		return true;
	}
	function ShipmentInstructionKlik() {

		ShipmentInstructionID = ShipmentInstruction.getSelectedId();
		if (ShipmentInstructionID != null) {
			LoadM('divlistOpportunity', 'cns-export-commercial-invoice-detail?Task=' + ShipmentInstruction.cells(ShipmentInstructionID, 2).getValue());
		}else {
			swal('Perhatian', 'Pilih baris data', 'warning');
		}
		return true;
	}
	ShipmentInstruction = new dhtmlXGridObject('ShipmentInstruction');
	ShipmentInstruction.setImagePath("./dhtmlx/imgs/");
	ShipmentInstruction.setHeader("No,SI No, Invoice No, Invoice Date, Shipper, Consignee, Description of Cargo, Qty (MT), Unit Price (MT), Amount, Subtotal, Advance Payment, Other, Total, Note");
	ShipmentInstruction.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// ShipmentInstruction.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	ShipmentInstruction.setInitWidths("60,150,150,150,150,150,250,100,150,150,150,150,150,150,200");
	ShipmentInstruction.setColAlign("center,left,left,left,left,left,left,left,left,left,left,left,left,left,left");
	ShipmentInstruction.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
	ShipmentInstruction.setColTypes("ron,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	// ShipmentInstruction.setNumberFormat("0,000", 7, ".", ",");
	ShipmentInstruction.attachEvent("onRowDblClicked", ShipmentInstructionKlik);
	ShipmentInstruction.setSkin("dhx_skyblue");
	ShipmentInstruction.init();

	var CommercialInvoicePagination = initCnsGridPagination(ShipmentInstruction, {
		pageSize: 25,
		pageSizeOptions: [10, 25, 50, 100],
		serverFilter: false,
		getUrl: function (params) {
			var q = [
				'posStart=' + params.posStart,
				'count=' + params.count,
				'tgl1=' + encodeURIComponent($('#tgl1').val()),
				'tgl2=' + encodeURIComponent($('#tgl2').val())
			];
			if (params.sortCol !== null && params.sortCol !== undefined) {
				q.push('sort_col=' + params.sortCol);
				q.push('sort_dir=' + (params.sortDir || 'asc'));
			}
			return 'xml-cns-export-commercial-invoice?' + q.join('&');
		}
	});
	refreshdata();

	function refreshdata() {
		CommercialInvoicePagination.reload(1);
	}
</script>
@endsection