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
			<a href="/">Sales Order</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Sales Order List</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">SALES ORDER LIST</div>
	<div class="card">
		<div class="card-body">
			<div class="mb-2">
				Periode : <input type="date" id="tgl1" name="tgl1" value="{{ date('Y-m-01', strtotime('-7 days')) }}" class="form-control-sm" />
				s/d <input type="date" id="tgl2" name="tgl2" value="{{ date('Y-m-d') }}" class="form-control-sm" />
				<button onclick="refreshdata()" class="btn btn-primary p-2">Refresh</button>
			</div>
			<div id="SalesOrder" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="ekselSalesOrderDet" name="ekselSalesOrderDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="SalesOrder.toExcel('./export/generate.php');" />
				<input type="button" id="DetailSalesOrder" name="DetailSalesOrder" value="Detail Sales Order" class="btn btn-primary mt-2 mr-2" onclick="SalesOrderKlik()" />
				<input type="button" id="NewSalesOrder" name="NewSalesOrder" value="New Sales Order" class="btn btn-info mt-2 mr-2" onclick="SalesOrderNew()" />
				<input type="button" id="PrintSalesOrder" name="PrintSalesOrder" value="Print Sales Order" class="btn btn-info mt-2 mr-2" onclick="SalesOrderPrint()" />
				<input type="button" id="ApproveSalesOrder" name="ApproveSalesOrder" value="Approve Sales Order" class="btn btn-success mt-2 mr-2" onclick="SalesOrderApprove()" />
				<input type="button" id="RejectSalesOrder" name="RejectSalesOrder" value="Reject Sales Order" class="btn btn-danger mt-2 mr-2" onclick="SalesOrderReject()" />
			</div>
		</div>
	</div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

</div>

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
			alert("Pilih baris data");
		}
		return true;
	}

	function SalesOrderKlik() {

		SalesOrderID = SalesOrder.getSelectedId();
		if (SalesOrderID != null) {
			LoadM('divlistOpportunity', 'cns-sales-order-detail?task=' + SalesOrderID);
		} else {
			alert("Pilih baris data");
		}
		return true;
	}
	function SalesOrderApprove() {
		SalesOrderID = SalesOrder.getSelectedId();
		if (!SalesOrderID) {
			alert("Pilih baris data terlebih dahulu.");
			return;
		}

		if (!confirm("Apakah Anda yakin ingin meng-approve Sales Order ini? Stok kemasan akan dipotong.")) {
			return;
		}

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: 'cns-sales-order-approve/' + SalesOrderID + '?_token={{ csrf_token() }}',
			type: 'POST',
			dataType: 'json',
			success: function(res) {
				if (res && res.status === 'success') {
					alert(res.message || 'Sales Order berhasil di-approve!');
					refreshdata();
					$('#divlistOpportunity').html('');
				} else {
					alert(res.message || 'Gagal approve Sales Order.');
				}
			},
			error: function(xhr) {
				var msg = (xhr && xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : (xhr.responseText || 'Terjadi kesalahan sistem.');
				alert(msg);
			}
		});
	}

	function SalesOrderReject() {
		SalesOrderID = SalesOrder.getSelectedId();
		if (!SalesOrderID) {
			alert("Pilih baris data terlebih dahulu.");
			return;
		}

		var reason = prompt("Masukkan alasan penolakan (opsional):");
		if (reason === null) {
			return;
		}

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: 'cns-sales-order-reject/' + SalesOrderID + '?_token={{ csrf_token() }}',
			type: 'POST',
			data: { reason: reason },
			dataType: 'json',
			success: function(res) {
				if (res && res.status === 'success') {
					alert(res.message || 'Sales Order berhasil di-reject.');
					refreshdata();
					$('#divlistOpportunity').html('');
				} else {
					alert(res.message || 'Gagal reject Sales Order.');
				}
			},
			error: function(xhr) {
				var msg = (xhr && xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : (xhr.responseText || 'Terjadi kesalahan sistem.');
				alert(msg);
			}
		});
	}

	SalesOrder = new dhtmlXGridObject('SalesOrder');
	SalesOrder.setImagePath("./dhtmlx/imgs/");
	SalesOrder.setHeader("No,Tanggal Order, No Sales Order, Site, Metode Pembayaran, Status");
	SalesOrder.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#select_filter");
	SalesOrder.setInitWidths("60,130,130,180,140,110");
	SalesOrder.setColAlign("center,center,left,left,left,center");
	SalesOrder.setColSorting("int,str,str,str,str,str");
	SalesOrder.setColTypes("ron,ro,ro,ro,ro,ro");
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