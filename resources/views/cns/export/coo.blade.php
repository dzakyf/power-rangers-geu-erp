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
	'title' => 'Certificate of Origin',
	'breadcrumbs' => [
		['label' => 'Export Management'],
		['label' => 'Certificate of Origin'],
	],
])

<div class="card cns-card">
	<div class="card-header">
		<h5 class="card-title mb-0">Certificate of Origin List</h5>
		<div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
			<input type="button" id="newSchedule" name="newSchedule" value="New COO" class="btn btn-sm btn-primary" onclick="CertificateOriginNewBtn()" />
			<input type="button" id="DetailSchedule" name="DetailSchedule" value="Detail COO" class="btn btn-sm btn-info" onclick="CertificateOriginKlik()" />
			<input type="button" id="printCOO" name="printCOO" value="Print COO" class="btn btn-sm btn-secondary" onclick="PrintCOO()" />
			<input type="button" id="ekselCertificateOriginDet" name="ekselCertificateOriginDet" class="btn btn-sm btn-warning" value="Export Excel" onclick="CertificateOrigin.toExcel('./export/generate.php');" />
			<button type="button" onclick="refreshdata()" class="btn btn-sm btn-outline-secondary">Refresh</button>
		</div>
	</div>
	<div class="card-body">
		<div class="cns-grid-wrapper">
			<div id="CertificateOrigin" class="cns-grid" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		</div>
	</div>
</div>
<div id="divlistOpportunity"></div>
<div id="DivProcess"></div>

<script>
	function PrintCOO()
	 {
		CertificateOriginID = CertificateOrigin.getSelectedId();
		if (CertificateOriginID != null) {
			window.open('cns-export-certificate-origin-print?Task=' + CertificateOrigin.cells(CertificateOriginID, 2).getValue(), '_blank');
		}else {
			swal('Perhatian', 'Pilih baris data', 'warning');
		}
		return true;
	 }
	function CertificateOriginNewBtn() {

		LoadM('divlistOpportunity', 'cns-export-certificate-origin-new');
		return true;
	}
	function CertificateOriginKlik() {

		CertificateOriginID = CertificateOrigin.getSelectedId();
		if (CertificateOriginID != null) {
			LoadM('divlistOpportunity', 'cns-export-certificate-origin-detail?Task=' + CertificateOrigin.cells(CertificateOriginID, 2).getValue());
		}else {
			swal('Perhatian', 'Pilih baris data', 'warning');
		}
		return true;
	}
	CertificateOrigin = new dhtmlXGridObject('CertificateOrigin');
	CertificateOrigin.setImagePath("./dhtmlx/imgs/");
	CertificateOrigin.setHeader("No,Export Invoice Number,Export Invoice Date,Contract Reference,Consignee,Destination Port,Method of Dispatch,Type of Shipment,HS Code,Description of Goods,Quantity,Note");
	CertificateOrigin.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// CertificateOrigin.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	CertificateOrigin.setInitWidths("60,150,150,150,150,250,100,150,150,150,150,150,150,200");
	CertificateOrigin.setColAlign("center,left,left,left,left,left,left,left,left,left,left,left,left,left");
	CertificateOrigin.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
	CertificateOrigin.setColTypes("ron,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	// CertificateOrigin.setNumberFormat("0,000", 7, ".", ",");
	CertificateOrigin.attachEvent("onRowDblClicked", CertificateOriginKlik);
	CertificateOrigin.setSkin("dhx_skyblue");
	CertificateOrigin.init();

	var CertificateOriginPagination = initCnsGridPagination(CertificateOrigin, {
		pageSize: 25,
		pageSizeOptions: [10, 25, 50, 100],
		serverFilter: false,
		getUrl: function (params) {
			var q = [
				'posStart=' + params.posStart,
				'count=' + params.count
			];
			if (params.sortCol !== null && params.sortCol !== undefined) {
				q.push('sort_col=' + params.sortCol);
				q.push('sort_dir=' + (params.sortDir || 'asc'));
			}
			return 'xml-cns-export-certificate-origin?' + q.join('&');
		}
	});
	refreshdata();

	function refreshdata() {
		CertificateOriginPagination.reload(1);
	}
</script>
@endsection