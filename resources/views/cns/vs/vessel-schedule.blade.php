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
	'title' => 'Vessel Schedule',
	'breadcrumbs' => [
		['label' => 'Vessel Schedule'],
		['label' => 'Vessel Schedule List'],
	],
])

<div class="card cns-card">
	<div class="card-header">
		<h5 class="card-title mb-0">Data Vessel Schedule</h5>
		<div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
			<input type="button" id="newSchedule" name="newSchedule" value="New Schedule" class="btn btn-sm btn-primary" onclick="VesselScheduleNew()" />
			<input type="button" id="DetailSchedule" name="DetailSchedule" value="Detail Schedule" class="btn btn-sm btn-info" onclick="VesselScheduleKlik()" />
			<input type="button" id="openShipmentLink" name="openShipmentLink" value="Open ShipmentLink" class="btn btn-sm btn-secondary" onclick="OpenShipmentLink()" />
			<input type="button" id="ekselVesselScheduleDet" name="ekselVesselScheduleDet" class="btn btn-sm btn-success" value="Export Excel" onclick="VesselSchedule.toExcel('./export/generate.php');" />
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
			<div id="VesselSchedule" class="cns-grid" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		</div>
	</div>
</div>
<div id="divlistOpportunity"></div>
<div id="DivProcess"></div>

<script>
	function OpenShipmentLink()
	 {
		window.open("https://www.shipmentlink.com","_blank");
	 }
	function VesselScheduleNew() {

		LoadM('divlistOpportunity', 'cns-vessel-schedule-new');
		return true;
	}
	function VesselScheduleKlik() {

		VesselScheduleID = VesselSchedule.getSelectedId();
		if (VesselScheduleID != null) {
			LoadM('divlistOpportunity', 'cns-vessel-schedule-detail?task=' + VesselScheduleID);
		}else {
			swal('Perhatian', 'Pilih baris data', 'warning');
		}
		return true;
	}
	VesselSchedule = new dhtmlXGridObject('VesselSchedule');
	VesselSchedule.setImagePath("./dhtmlx/imgs/");
	VesselSchedule.setHeader("No VS,Kontrak,Incoterms,Shipping Method,No Booking,Qty,Type & Size,Vessel/Truck,PoL,PoD,Stuffing Date,Closing CY,Laycan,ETD,ETA,Dokumen Q88");
	VesselSchedule.attachHeader("#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#rspan");
	// VesselSchedule.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	VesselSchedule.setInitWidths("160,150,100,120,150,100,120,180,150,150,130,120,150,120,120,100");
	VesselSchedule.setColAlign("left,left,left,left,left,right,left,left,left,left,center,center,center,center,center,center");
	VesselSchedule.setColSorting("str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
	VesselSchedule.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,link");
	// VesselSchedule.setNumberFormat("0,000", 7, ".", ",");
	VesselSchedule.attachEvent("onRowDblClicked", VesselScheduleKlik);
	VesselSchedule.setSkin("dhx_skyblue");
	VesselSchedule.init();

	var VesselSchedulePagination = initCnsGridPagination(VesselSchedule, {
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
			return 'xml-cns-vessel-schedule?' + q.join('&');
		}
	});
	refreshdata();

	function refreshdata() {
		VesselSchedulePagination.reload(1);
	}
</script>
@endsection