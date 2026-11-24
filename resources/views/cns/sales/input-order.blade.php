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
	'title' => 'Sales Order',
	'breadcrumbs' => [
		['label' => 'Sales Order'],
		['label' => 'Sales Order List'],
	],
])

<div class="card cns-card">
	<div class="card-header">
		<h5 class="card-title mb-0">Data Sales Order</h5>
		<div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
			<input type="button" id="NewSalesOrder" name="NewSalesOrder" value="New Sales Order" class="btn btn-sm btn-primary" onclick="SalesOrderNew()" />
			<input type="button" id="DetailSalesOrder" name="DetailSalesOrder" value="Detail Sales Order" class="btn btn-sm btn-info" onclick="SalesOrderKlik()" />
			<div class="btn-group">
				<button type="button" id="PrintDropdownBtn" class="btn btn-sm btn-secondary dropdown-toggle" data-toggle="dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					Print
				</button>
				<div class="dropdown-menu">
					<a class="dropdown-item" href="javascript:void(0)" id="PrintSalesOrder" onclick="SalesOrderPrint()">Print Sales Order</a>
					<a class="dropdown-item" href="javascript:void(0)" id="PrintSuratJalan" onclick="SalesOrderPrintSuratJalan()">Print Surat Jalan</a>
				</div>
			</div>
			<input type="button" id="ekselSalesOrderDet" name="ekselSalesOrderDet" class="btn btn-sm btn-success" value="Export Excel" onclick="SalesOrder.toExcel('./export/generate.php');" />
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
			<div id="SalesOrder" class="cns-grid" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		</div>
	</div>
</div>
<div id="divlistOpportunity"></div>
<div id="DivProcess"></div>

<script>
	function openShipmentLink() {
		window.open("https://www.shipmentlink.com", "_blank");
	}

	function SalesOrderNew() {
		LoadM('divlistOpportunity', 'cns-sales-order-new');
	}

	function SalesOrderPrint() {

		SalesOrderID = SalesOrder.getSelectedId();
		if (SalesOrderID != null) {
			window.open('cns-sales-order-print?task=' + SalesOrderID, '_blank');
		} else {
			swal('Perhatian', 'Pilih baris data', 'warning');
		}
		return true;
	}

	function SalesOrderPrintSuratJalan() {
		SalesOrderID = SalesOrder.getSelectedId();
		if (!SalesOrderID) {
			swal('Perhatian', 'Pilih baris data', 'warning');
			return false;
		}
		var statusText = (SalesOrder.cells(SalesOrderID, 7).getValue() || '').trim().toUpperCase();
		if (statusText !== 'APPROVED') {
			swal('Perhatian', 'Surat Jalan hanya tersedia setelah Final Approve Manager CNS.', 'warning');
			return false;
		}
		window.open('cns-sales-order-surat-jalan-print?task=' + SalesOrderID, '_blank');
		return true;
	}

	function SalesOrderKlik() {

		SalesOrderID = SalesOrder.getSelectedId();
		if (SalesOrderID != null) {
			LoadM('divlistOpportunity', 'cns-sales-order-detail?task=' + SalesOrderID);
		} else {
			swal('Perhatian', 'Pilih baris data', 'warning');
		}
		return true;
	}

	SalesOrder = new dhtmlXGridObject('SalesOrder');
	SalesOrder.setImagePath("./dhtmlx/imgs/");
	SalesOrder.setHeader("No,Tanggal Order, No Sales Order, No Surat Jalan, Site, Metode Pembayaran, Progress, Status");
	SalesOrder.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#rspan,#select_filter");
	SalesOrder.setInitWidths("60,115,125,125,130,120,185,210");
	SalesOrder.setColAlign("center,center,left,left,left,left,center,center");
	SalesOrder.setColSorting("int,str,str,str,str,str,str,str");
	SalesOrder.setColTypes("ron,ro,ro,ro,ro,ro,ro,cns_badge");
	SalesOrder.enableSmartRendering(true, 150);
	// SalesOrder.setNumberFormat("0,000", 7, ".", ",");
	SalesOrder.attachEvent("onRowDblClicked", SalesOrderKlik);
	SalesOrder.setSkin("dhx_skyblue");
	SalesOrder.init();
	refreshdata();

	function refreshdata() {
		SalesOrder.clearAndLoad('xml-cns-sales-order?tgl1=' + $('#tgl1').val() + "&tgl2=" + $('#tgl2').val());

	}
</script>
@endsection