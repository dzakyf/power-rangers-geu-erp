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
			<a href="/">Certificate of Origin</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Certificate of Origin List</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">CERTIFICATE OF ORIGIN LIST</div>
	<div class="card">
		<div class="card-body">
			<div id="CertificateOrigin" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="ekselCertificateOriginDet" name="ekselCertificateOriginDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="CertificateOrigin.toExcel('./export/generate.php');" />
				<input type="button" id="newSchedule" name="newSchedule" value="New COO" class="btn btn-primary mt-2 mr-2" onclick="CertificateOriginNewBtn()" />
				<input type="button" id="DetailSchedule" name="DetailSchedule" value="Detail COO" class="btn btn-info mt-2 mr-2" onclick="CertificateOriginKlik()" />
				<input type="button" id="printCOO" name="printCOO" value="Print COO" class="btn btn-secondary mt-2 mr-2" onclick="PrintCOO()" />
			</div>
		</div>
	</div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

</div>

<script>
	function PrintCOO()
	 {
		CertificateOriginID = CertificateOrigin.getSelectedId();
		if (CertificateOriginID != null) {
			window.open('cns-export-certificate-origin-print?Task=' + CertificateOrigin.cells(CertificateOriginID, 2).getValue(), '_blank');
		}else {
			alert("Pilih baris data");
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
			alert("Pilih baris data");
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
	CertificateOrigin.enableSmartRendering(true, 150);
	// CertificateOrigin.setNumberFormat("0,000", 7, ".", ",");
	CertificateOrigin.attachEvent("onRowDblClicked", CertificateOriginKlik);
	CertificateOrigin.setSkin("dhx_skyblue");
	CertificateOrigin.init();
	refreshdata();

	function refreshdata() {
		CertificateOrigin.clearAndLoad('xml-cns-export-certificate-origin');

	}
</script>
@endsection