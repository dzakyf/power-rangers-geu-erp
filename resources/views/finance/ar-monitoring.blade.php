<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;


?>

@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Finance</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="#">Account Receive</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Monitoring</span>
		</li>
	</ol>
</div>

<div class="row">
	<div id="ARMonitoring" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
	<div>
		<input type="button" id="ekselARMonitoringDet" name="ekselARMonitoringDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="ARMonitoring.toExcel('./export/generate.php');" />
	</div>
</div>
<div id="ARCareDetail"></div>

<script>
	function ARMonitoringDetail() {

		ARMonitoringID = encodeURI(ARMonitoring.cells(ARMonitoring.getSelectedId(), 1).getValue());
		remaininv = ARMonitoring.cells(ARMonitoring.getSelectedId(), 6).getValue();
		totalinv = ARMonitoring.cells(ARMonitoring.getSelectedId(), 4).getValue();
		LoadM('ARCareDetail', 'ar-monitoring-detail?CID=' + ARMonitoringID + "&remain=" + remaininv + "&totalinv=" + totalinv);
	}
	ARMonitoring = new dhtmlXGridObject('ARMonitoring');
	ARMonitoring.setImagePath("./dhtmlx/imgs/");
	ARMonitoring.setHeader("No.,Account No,Account Name,Open Invoice,Total,Paid,Remain,CC User, PIC Invoice,Location");
	ARMonitoring.attachHeader("#rspan,#text_filter,#text_filter,#rspan,#rspan,#rspan,#rspan,#text_filter,#text_filter,#combo_filter");
	ARMonitoring.attachFooter("Total,#cspan,#cspan,#cspan,#stat_total,#stat_total,#stat_total,,,");
	ARMonitoring.setInitWidths("60,100,300,100,120,120,120,120,120,100");
	ARMonitoring.setColAlign("center,center,left,right,right,right,right,center,center,center");
	ARMonitoring.setColSorting("int,str,str,str,int,int,int,str,str,str");
	ARMonitoring.setColTypes("dyn,ro,ro,ro,ron,ron,ron,ro,ro,ro");
	ARMonitoring.enableSmartRendering(true, 350);
	ARMonitoring.setNumberFormat("0,000.00", 4, ".", ",");
	ARMonitoring.setNumberFormat("0,000.00", 5, ".", ",");
	ARMonitoring.setNumberFormat("0,000.00", 6, ".", ",");
	ARMonitoring.attachEvent("onRowDblClicked", ARMonitoringDetail);
	ARMonitoring.init();
	refreshdata();

	function refreshdata() {
		ARMonitoring.clearAndLoad('xml-finance-ar-monitoring');

	}
</script>
@endsection

@push('footer')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endpush
@push('head')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush