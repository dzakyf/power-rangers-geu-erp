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
			<a href="/">Vessel Schedule</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Vessel Schedule List</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">VESSEL SCHEDULE LIST</div>
	<div class="card">
		<div class="card-body">
			<div class="mb-2">
				Periode : <input type="date" id="tgl1" name="tgl1" value="{{ date('Y-m-01', strtotime('-7 days')) }}" class="form-control-sm" />
				s/d <input type="date" id="tgl2" name="tgl2" value="{{ date('Y-m-d') }}" class="form-control-sm" />
				<button onclick="refreshdata()" class="btn btn-primary p-2">Refresh</button>
			</div>
			<div id="VesselSchedule" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="newSchedule" name="newSchedule" value="New Schedule" class="btn btn-primary mt-2 mr-2" onclick="VesselScheduleNew()" />
				<input type="button" id="DetailSchedule" name="DetailSchedule" value="Detail Schedule" class="btn btn-info mt-2 mr-2" onclick="VesselScheduleKlik()" />
				<input type="button" id="openShipmentLink" name="openShipmentLink" value="Open ShipmentLink" class="btn btn-secondary mt-2 mr-2" onclick="OpenShipmentLink()" />
				<input type="button" id="ekselVesselScheduleDet" name="ekselVesselScheduleDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="VesselSchedule.toExcel('./export/generate.php');" />
			</div>
		</div>
	</div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

</div>

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
			alert("Pilih baris data");
		}
		return true;
	}
	VesselSchedule = new dhtmlXGridObject('VesselSchedule');
	VesselSchedule.setImagePath("./dhtmlx/imgs/");
	VesselSchedule.setHeader("Kontrak,Incoterms,Shipping Method,No Booking,Qty,Type & Size,Vessel/Truck,PoL,PoD,Stuffing Date,Closing CY,Laycan,ETD,ETA,Dokumen Q88");
	VesselSchedule.attachHeader("#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#rspan");
	// VesselSchedule.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	VesselSchedule.setInitWidths("150,100,120,150,100,120,180,150,150,130,120,150,120,120,100");
	VesselSchedule.setColAlign("left,left,left,left,right,left,left,left,left,center,center,center,center,center,center");
	VesselSchedule.setColSorting("str,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
	VesselSchedule.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,link");
	VesselSchedule.enableSmartRendering(true, 150);
	// VesselSchedule.setNumberFormat("0,000", 7, ".", ",");
	VesselSchedule.attachEvent("onRowDblClicked", VesselScheduleKlik);
	VesselSchedule.setSkin("dhx_skyblue");
	VesselSchedule.init();
	refreshdata();

	function refreshdata() {
		VesselSchedule.clearAndLoad('xml-cns-vessel-schedule?tgl1=' + $('#tgl1').val() + '&tgl2=' + $('#tgl2').val());

	}
</script>
@endsection