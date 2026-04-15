<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

?>

@extends('layout.mainlayout-mitra')

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
			<div id="VesselSchedule" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="ekselVesselScheduleDet" name="ekselVesselScheduleDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="VesselSchedule.toExcel('./export/generate.php');" />
				<input type="button" id="newSchedule" name="newSchedule" value="New Schedule" class="btn btn-info mt-2 mr-2" onclick="VesselScheduleNew()" />
				<input type="button" id="DetailSchedule" name="DetailSchedule" value="Detail Schedule" class="btn btn-info mt-2 mr-2" onclick="VesselScheduleKlik()" />
				<input type="button" id="openShipmentLink" name="openShipmentLink" value="Open ShipmentLink" class="btn btn-info mt-2 mr-2" onclick="OpenShipmentLink()" />
			</div>
		</div>
	</div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

</div>

<script>
	function openShipmentLink()
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
			LoadM('divlistOpportunity', 'cns-vessel-schedule-detail?Task=' + VesselScheduleID);
		}else {
			alert("Pilih baris data");
		}
		return true;
	}
	VesselSchedule = new dhtmlXGridObject('VesselSchedule');
	VesselSchedule.setImagePath("./dhtmlx/imgs/");
	VesselSchedule.setHeader("No,Order (DO)/Booking Confirmation (BC),Qty,Kapal,POL,POD,Stuffing,Checking,Closing CY,ETD,ATD,ETA,ATD,Estimated Day(s), Actual Day(s), Note"); 
	VesselSchedule.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// VesselSchedule.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	VesselSchedule.setInitWidths("60,150,100,200,150,150,100,100,100,100,100,auto");
	VesselSchedule.setColAlign("center,left,left,left,left,left,left,left,left,left,left,left,left,left");
	VesselSchedule.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str");
	VesselSchedule.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	VesselSchedule.enableSmartRendering(true, 150);
	// VesselSchedule.setNumberFormat("0,000", 7, ".", ",");
	VesselSchedule.attachEvent("onRowDblClicked", VesselScheduleKlik);
	VesselSchedule.setSkin("dhx_skyblue");
	VesselSchedule.init();
	refreshdata();

	function refreshdata() {
		VesselSchedule.clearAndLoad('xml-cns-vessel-schedule');

	}
</script>
@endsection