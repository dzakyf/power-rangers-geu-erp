<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;


?>

@extends('layout.mainlayout-finance')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Finance</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="#">Account Payable</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Monitoring</span>
		</li>
	</ol>
</div>

<div class="row">
	<div id="APManagement" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
	<div>
		<input type="button" id="ekselAPManagementDet" name="ekselAPManagementDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="APManagement.toExcel('./export/generate.php');" />
	</div>
</div>
<div id="ARCareDetail"></div>

<script>
	function APManagementDetail() {

		APManagementID = encodeURI(APManagement.cells(APManagement.getSelectedId(), 1).getValue());
		remaininv = APManagement.cells(APManagement.getSelectedId(), 6).getValue();
		totalinv = APManagement.cells(APManagement.getSelectedId(), 4).getValue();
		LoadM('ARCareDetail', 'finance-ap-management-detail?CID=' + APManagementID + "&remain=" + remaininv + "&totalinv=" + totalinv);
	}
	APManagement = new dhtmlXGridObject('APManagement');
	APManagement.setImagePath("./dhtmlx/imgs/");
	APManagement.setHeader("No.,Vendor ID,Vendor Name,Open Invoice,Total,Paid,Remain");
	APManagement.attachHeader("#rspan,#text_filter,#text_filter,#rspan,#rspan,#rspan,#rspan");
	APManagement.attachFooter("Total,#cspan,#cspan,#cspan,#stat_total,#stat_total,#stat_total");
	APManagement.setInitWidths("60,100,300,100,120,120,120,120,120,100");
	APManagement.setColAlign("center,center,left,right,right,right,right");
	APManagement.setColSorting("int,str,str,str,int,int,int,str,str,str");
	APManagement.setColTypes("dyn,ro,ro,ro,ron,ron,ron");
	APManagement.enableSmartRendering(true, 350);
	APManagement.setNumberFormat("0,000.00", 4, ".", ",");
	APManagement.setNumberFormat("0,000.00", 5, ".", ",");
	APManagement.setNumberFormat("0,000.00", 6, ".", ",");
	APManagement.attachEvent("onRowDblClicked", APManagementDetail);
	APManagement.init();
	refreshdata();

	function refreshdata() {
		APManagement.clearAndLoad('xml-finance-ap-management');

	}
</script>
@endsection

@push('footer')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endpush
@push('head')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush