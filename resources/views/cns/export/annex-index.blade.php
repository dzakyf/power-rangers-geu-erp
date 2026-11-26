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
	'title' => 'Annex VII',
	'breadcrumbs' => [
		['label' => 'Export Management'],
		['label' => 'Annex VII'],
	],
])

<div class="card cns-card">
	<div class="card-header">
		<h5 class="card-title mb-0">Annex VII List</h5>
		<div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
			<input type="button" id="newSchedule" name="newSchedule" value="New Annex" class="btn btn-sm btn-primary" onclick="AnnexViiNewBtn()" />
			<input type="button" id="DetailSchedule" name="DetailSchedule" value="Detail Annex" class="btn btn-sm btn-info" onclick="AnnexViiKlik()" />
			<input type="button" id="printAnnex" name="printAnnex" value="Print Annex" class="btn btn-sm btn-secondary" onclick="PrintAnnex()" />
			<input type="button" id="printAnnexSample" name="printAnnexSample" value="Print Annex Sample" class="btn btn-sm btn-secondary" onclick="PrintAnnexSample()" />
			<input type="button" id="ekselAnnexViiDet" name="ekselAnnexViiDet" class="btn btn-sm btn-warning" value="Export Excel" onclick="AnnexVii.toExcel('./export/generate.php');" />
			<button type="button" onclick="refreshdata()" class="btn btn-sm btn-outline-secondary">Refresh</button>
		</div>
	</div>
	<div class="card-body">
		<div class="cns-filter-bar">
			<div>
				<label for="tgl1" class="d-block mb-1"><small>Dari Tanggal</small></label>
				<input type="date" id="tgl1" name="tgl1" value="{{ date('Y-m-01', strtotime('-7 days')) }}" class="text-black form-control form-control-sm" style="width:auto;" />
			</div>
			<div>
				<label for="tgl2" class="d-block mb-1"><small>Sampai Tanggal</small></label>
				<input type="date" id="tgl2" name="tgl2" value="{{ date('Y-m-d') }}" class="text-black form-control form-control-sm" style="width:auto;" />
			</div>
			<div>
				<button type="button" onclick="refreshdata()" class="btn btn-sm btn-primary">Filter</button>
			</div>
		</div>
		<div class="cns-grid-wrapper">
			<div id="AnnexVii" class="cns-grid" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		</div>
	</div>
</div>
<div id="divlistOpportunity"></div>
<div id="DivProcess"></div>

<script>
	function PrintAnnex()
	 {
		AnnexViiID = AnnexVii.getSelectedId();
		if (AnnexViiID != null) {
			window.open('cns-export-annex-print?Task=' + AnnexVii.cells(AnnexViiID, 2).getValue(), '_blank');
		}else {
			swal('Perhatian', 'Pilih baris data', 'warning');
		}
		return true;
	 }
	function PrintAnnexSample()
	 {
		window.open('cns-export-annex-print-sample?', '_blank');
	 }
	function AnnexViiNewBtn() {

		LoadM('divlistOpportunity', 'cns-export-annex-new');
		return true;
	}
	function AnnexViiKlik() {

		AnnexViiID = AnnexVii.getSelectedId();
		if (AnnexViiID != null) {
			LoadM('divlistOpportunity', 'cns-export-annex-detail?Task=' + AnnexVii.cells(AnnexViiID, 2).getValue());
		}else {
			swal('Perhatian', 'Pilih baris data', 'warning');
		}
		return true;
	}
	AnnexVii = new dhtmlXGridObject('AnnexVii');
	AnnexVii.setImagePath("./dhtmlx/imgs/");
	AnnexVii.setHeader(
		"No,Annex No,SI No,Order No,Shipper,Consignee,Shipment Date,Qty (Kg),POL,POD,BL No,HS Code,Status"
	);

	AnnexVii.attachHeader(
		"#rspan,#text_filter,#text_filter,#text_filter,#select_filter,#select_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#select_filter"
	);

	AnnexVii.setInitWidths(
		"80,150,150,120,200,200,120,120,120,120,150,120,*"
	);

	AnnexVii.setColAlign(
		"center,left,left,left,left,left,center,right,left,left,left,left,center"
	);

	AnnexVii.setColSorting(
		"str,str,str,str,str,str,date,int,str,str,str,str,str"
	);

	AnnexVii.setColTypes(
		"ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,cns_badge"
	);
	var AnnexPagination;
	AnnexVii.attachEvent("onRowDblClicked", AnnexViiKlik);
	AnnexVii.setSkin("dhx_skyblue");
	AnnexVii.init();

	AnnexPagination = initCnsGridPagination(AnnexVii, {
		pageSize: 25,
		pageSizeOptions: [10, 25, 50, 100],
		getUrl: function (params) {
			var q = [];
			q.push('posStart=' + params.posStart);
			q.push('count=' + params.count);
			q.push('tgl1=' + encodeURIComponent($('#tgl1').val()));
			q.push('tgl2=' + encodeURIComponent($('#tgl2').val()));

			if (params.sortCol !== null && params.sortCol !== undefined) {
				q.push('sort_col=' + params.sortCol);
				q.push('sort_dir=' + (params.sortDir || 'asc'));
			}

			if (params.filters) {
				for (var colIdx in params.filters) {
					q.push('filters[' + colIdx + ']=' + encodeURIComponent(params.filters[colIdx]));
				}
			}

			return 'xml-cns-export-annex?' + q.join('&');
		}
	});

	refreshdata();

	function refreshdata() {
		if (AnnexPagination) {
			AnnexPagination.reload(true);
		}
	}
</script>
@endsection