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
			<a href="/">Stuffing</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Stuffing List</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">STUFFING LIST</div>
	<div class="card">
		<div class="card-body">
			<div>
				<span style='background-color: #ffccccff' class="p-2 border"></span> : Stuffing Not Started<br>
				<span style='background-color: #feffccff' class="p-2 border"></span> : Stuffing Yet Complete<br>
				<span style='background-color: #ccffcfff' class="p-2 border"></span> : Stuffing Complete<br>
			</div>
			<div class="mb-2">
				Periode : <input type="date" id="tgl1" name="tgl1" value="{{ date('Y-m-01', strtotime('-7 days')) }}" class="form-control-sm" />
				s/d <input type="date" id="tgl2" name="tgl2" value="{{ date('Y-m-d') }}" class="form-control-sm" />
				<button onclick="refreshdata()" class="btn btn-primary p-2">Refresh</button>
			</div>
			<div id="StuffingVessel" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="ekselStuffingVesselDet" name="ekselStuffingVesselDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="StuffingVessel.toExcel('./export/generate.php');" />
				<input type="button" id="DetailVesselSchedule" name="DetailVesselSchedule" value="Detail Vessel Schedule" class="btn btn-primary mt-2 mr-2" onclick="StuffingVesselKlik()" />
				<input type="button" id="openShipmentLink" name="openShipmentLink" value="Open ShipmentLink" class="btn btn-info mt-2 mr-2" onclick="openShipmentLink()" />
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

	function StuffingVesselKlik() {

		StuffingVesselID = StuffingVessel.getSelectedId();
		if (StuffingVesselID != null) {
			LoadM('divlistOpportunity', 'cns-stuffing-detail?task=' + StuffingVesselID);
		}else {
			alert("Pilih baris data");
		}
		return true;
	}
	StuffingVessel = new dhtmlXGridObject('StuffingVessel');
	StuffingVessel.setImagePath("./dhtmlx/imgs/");
	StuffingVessel.setHeader("No,Order (DO)/Booking Confirmation (BC),Qty,Kapal,POL,POD,Stuffing,Checking,Closing CY,ETD,ATD,ETA,ATA,Estimated Day(s), Actual Day(s), Note, Container (Seal)"); 
	StuffingVessel.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	StuffingVessel.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total,#stat_total,#stat_total,#stat_total");
	StuffingVessel.setInitWidths("60,150,100,200,150,150,100,100,100,100,100,100,100,100,100,100,800");
	StuffingVessel.setColAlign("center,left,left,left,left,left,left,left,left,left,left,left,left,left,left,left,left");
	StuffingVessel.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
	StuffingVessel.setColTypes("ron,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	StuffingVessel.enableSmartRendering(true, 150);
	// StuffingVessel.setNumberFormat("0,000", 7, ".", ",");
	StuffingVessel.attachEvent("onRowDblClicked", StuffingVesselKlik);
	StuffingVessel.setSkin("dhx_skyblue");
	StuffingVessel.init();
	refreshdata();

	function refreshdata() {
		StuffingVessel.clearAndLoad('xml-cns-stuffing?tgl1=' + $('#tgl1').val() + "&tgl2=" + $('#tgl2').val());

	}
</script>
@endsection