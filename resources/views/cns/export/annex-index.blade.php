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
			<a href="/">Annex VII</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Annex VII List</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">ANNEX VII LIST</div>
	<div class="card">
		<div class="card-body">
			<div class="mb-2">
				Periode : <input type="date" id="tgl1" name="tgl1" value="{{ date('Y-m-01', strtotime('-7 days')) }}" class="form-control-sm" />
				s/d <input type="date" id="tgl2" name="tgl2" value="{{ date('Y-m-d') }}" class="form-control-sm" />
				<button onclick="refreshdata()" class="btn btn-primary p-2">Refresh</button>
			</div>
			<div id="AnnexVii" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="ekselAnnexViiDet" name="ekselAnnexViiDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="AnnexVii.toExcel('./export/generate.php');" />
				<input type="button" id="newSchedule" name="newSchedule" value="New CI" class="btn btn-primary mt-2 mr-2" onclick="AnnexViiNewBtn()" />
				<input type="button" id="DetailSchedule" name="DetailSchedule" value="Detail CI" class="btn btn-info mt-2 mr-2" onclick="AnnexViiKlik()" />
				<input type="button" id="printCI" name="printCI" value="Print CI" class="btn btn-secondary mt-2 mr-2" onclick="PrintCI()" />
			</div>
		</div>
	</div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

</div>

<script>
	function PrintCI()
	 {
		AnnexViiID = AnnexVii.getSelectedId();
		if (AnnexViiID != null) {
			window.open('cns-export-commercial-invoice-print?Task=' + AnnexVii.cells(AnnexViiID, 2).getValue(), '_blank');
		}else {
			alert("Pilih baris data");
		}
		return true;
	 }
	function AnnexViiNewBtn() {

		LoadM('divlistOpportunity', 'cns-export-commercial-invoice-new');
		return true;
	}
	function AnnexViiKlik() {

		AnnexViiID = AnnexVii.getSelectedId();
		if (AnnexViiID != null) {
			LoadM('divlistOpportunity', 'cns-export-commercial-invoice-detail?Task=' + AnnexVii.cells(AnnexViiID, 2).getValue());
		}else {
			alert("Pilih baris data");
		}
		return true;
	}
	AnnexVii = new dhtmlXGridObject('AnnexVii');
	AnnexVii.setImagePath("./dhtmlx/imgs/");
	AnnexVii.setHeader("No,SI No, Invoice No, Invoice Date, Consignee, Description of Cargo, Qty (MT), Unit Price (MT), Amount, Subtotal, Advance Payment, Other, Total, Note");
	AnnexVii.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// AnnexVii.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	AnnexVii.setInitWidths("60,150,150,150,150,250,100,150,150,150,150,150,150,200");
	AnnexVii.setColAlign("center,left,left,left,left,left,left,left,left,left,left,left,left,left");
	AnnexVii.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
	AnnexVii.setColTypes("ron,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	AnnexVii.enableSmartRendering(true, 150);
	// AnnexVii.setNumberFormat("0,000", 7, ".", ",");
	AnnexVii.attachEvent("onRowDblClicked", AnnexViiKlik);
	AnnexVii.setSkin("dhx_skyblue");
	AnnexVii.init();
	refreshdata();

	function refreshdata() {
		AnnexVii.clearAndLoad('xml-cns-export-commercial-invoice?tgl1=' + $('#tgl1').val() + "&tgl2=" + $('#tgl2').val());

	}
</script>
@endsection