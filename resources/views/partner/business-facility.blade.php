<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
// $LSP = "GEU-HO";
// $Saya = "admin";


?>

@extends('layout.mainlayout-mitra')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Partner</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="#">Partner</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Business Lists</span>
		</li>
	</ol>
</div>

<div class="row">
	<div id="FacilityBenefits" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
	<div>
        <input type="button" id="ekselFacilityBenefitsDet" name="ekselFacilityBenefitsDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="FacilityBenefits.toExcel('./export/generate.php');" />
		<input type="button" id="detailBooth" name="detailBooth" value="Detail Booth" class="btn btn-success mt-2 mr-2" onclick="FacilityBenefitsKlikBooth()" />
		<input type="button" id="detailMobil" name="detailMobil" value="Detail Mobil" class="btn btn-success mt-2 mr-2" onclick="FacilityBenefitsKlikMobil()" />
	</div>
</div>
<div id="divlistOpportunity"></div>
<div id="divlistProjectList"></div>
<script>
	function FacilityBenefitsKlikBooth() {

		FacilityBenefitsID = FacilityBenefits.getSelectedId();
		if (FacilityBenefitsID != '') {
			LoadM('divlistOpportunity', 'partner-business-facility-detail-booth?ID=' + FacilityBenefitsID);
		}
		return true;
	}
	function FacilityBenefitsKlikMobil() {

		FacilityBenefitsID = FacilityBenefits.getSelectedId();
		if (FacilityBenefitsID != '') {
			LoadM('divlistOpportunity', 'partner-business-facility-detail-mobil?ID=' + FacilityBenefitsID);
		}
		return true;
	}
	FacilityBenefits = new dhtmlXGridObject('FacilityBenefits');
	FacilityBenefits.setImagePath("./dhtmlx/imgs/");
	FacilityBenefits.setHeader("No,Daftar Sebagai, Nama, Alamat, Jumlah Mitra/Bisnis Gudang, Motivasi Bergabung, Pertanyaan 1, Pertanyaan 2, Pertanyaan 3, Pertanyaan 4, Pertanyaan 5, Pertanyaan 6, Paket");
	FacilityBenefits.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// FacilityBenefits.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	FacilityBenefits.setInitWidths("60,100,150,300,100,120,120,100,100,100,100,100,100");
	FacilityBenefits.setColAlign("center,left,left,left,left,left,left,left,left,left,left,left,left,left,left");
	FacilityBenefits.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str");
	FacilityBenefits.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	FacilityBenefits.enableSmartRendering(true, 150);
	// FacilityBenefits.setNumberFormat("0,000", 7, ".", ",");
	FacilityBenefits.setSkin("dhx_skyblue");
	FacilityBenefits.init();
	refreshdata();

	function refreshdata() {
		FacilityBenefits.clearAndLoad('xml-partner-business-facility');

	}

	function delsOpportunity() {
		dhtmlx.confirm('Cancel this Opportunity?', 'Opportunity', function(r) {

			if (r == true) {
				FacilityBenefits.deleteSelectedRows();
				PFacilityBenefits.sendData();
				alert('Data Successfully Deleted!');
				setTimeout(function() {
					refreshdata();
				}, 1000);
			}
		})
	}


	function detailBP() {
		FacilityBenefitsKlik();
	}
</script>
@endsection