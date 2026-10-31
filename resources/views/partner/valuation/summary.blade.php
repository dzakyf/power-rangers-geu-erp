<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

?>

@extends('layout.mainlayout-bp')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Partner</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="/">Valuation</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">List</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">LIST WAREHOUSE</div>
	<div class="card">
		<div class="card-body">
			<div id="ValuationList" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="ekselValuationListDet" name="ekselValuationListDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="ValuationList.toExcel('./export/generate.php');" />
				{{-- <input type="button" id="detailValuationList" name="detailValuationList" value="Detail Warehouse List" class="btn btn-info mt-2 mr-2" onclick="ValuationListKlik()" /> --}}
			</div>
		</div>
	</div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

</div>

<script>
	function ValuationListKlik() {

		ValuationListID = ValuationList.getSelectedId();
		if (ValuationListID != null) {
			LoadM('divlistOpportunity', 'partner-valuation-summary-detail?Task=' + ValuationListID);
		}else {
			alert("Pilih baris data");
		}
		return true;
	}
	ValuationList = new dhtmlXGridObject('ValuationList');
	ValuationList.setImagePath("./dhtmlx/imgs/");
	ValuationList.setHeader("No,Gudang ID, Lokasi Gudang, Nama Mitra, Kapasitas, Status Gudang,Total Investasi, Total Nilai Aset, Total Penyusutan, Sisa Nilai Aset");
	ValuationList.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// ValuationList.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	ValuationList.setInitWidths("60,100,150,200,100,100,200,100,100,100");
	ValuationList.setColAlign("center,left,left,left,right,center,right,right,right,right");
	ValuationList.setColSorting("int,str,str,str,int,str,int,int,int,int");
	ValuationList.setColTypes("ron,ro,ro,ro,ron,ro,ron,ron,ron,ron");
	ValuationList.enableSmartRendering(true, 150);
	ValuationList.setNumberFormat("0,000", 4, ".", ",");
	ValuationList.setNumberFormat("0,000", 6, ".", ",");
	ValuationList.setNumberFormat("0,000", 7, ".", ",");
	ValuationList.setNumberFormat("0,000", 8, ".", ",");
	ValuationList.setNumberFormat("0,000", 9, ".", ",");
	// ValuationList.attachEvent("onRowDblClicked", ValuationListKlik);
	ValuationList.setSkin("dhx_skyblue");
	ValuationList.init();
	refreshdata();

	function refreshdata() {
		ValuationList.clearAndLoad('xml-partner-valuation-summary');

	}
</script>
@endsection