<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

// $LSP = Auth::user()->Location;
// $Saya =  Auth::user()->username;

$LSP = "GEU-HO";
$Saya = "admin";

// $leadID = "L/BP/".date('Y')."/".sprintf('%02d',date('m'))."/".strtoupper(substr(uniqid(), 0, 4));

$leadID = 'BP/'.$LSP.'/'.date('Ymdhis').'/'.substr(md5($Saya),0,3);

$masterCategory = DB::table('master_ticket_category')->get();
?>

@extends('layout.mainlayout-bp')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Partner</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="/">Ticket</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Create New</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">TICKET REVIEW</div>
	<div class="card">
		<div class="card-body">
			<div id="TicketReview" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="ekselTicketReviewDet" name="ekselTicketReviewDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="TicketReview.toExcel('./export/generate.php');" />
				<input type="button" id="detailLeads" name="detailLeads" value="Detail Ticket And Response" class="btn btn-success mt-2 mr-2" onclick="detailLeads()" />
			</div>
		</div>
	</div>
	<div id="DivProcess"></div>

</div>

<script>
	function TicketReviewKlik() {

		TicketReviewID = TicketReview.getSelectedId();
		$("#divlistOpportunity").show();
		if (TicketReviewID != '') {
			$("#divlistProjectList").hide();
			LoadM('divlistOpportunity', 'partner-ticket-open-detail?ID=' + TicketReviewID);
		}
		return true;
	}
	TicketReview = new dhtmlXGridObject('TicketReview');
	TicketReview.setImagePath("./dhtmlx/imgs/");
	TicketReview.setHeader("No,Ticket,Created Person,Status Review,Tanggal Pengajuan, Jenis Permasalahan, Kategori, Judul, Deskripsi, Lampiran, Catatan Penyelesaian, Tanggal Penyelesaian");
	TicketReview.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#rspan,#text_filter,#text_filter");
	// TicketReview.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	TicketReview.setInitWidths("60,150,100,200,100,120,120,200,300,100,200,100");
	TicketReview.setColAlign("center,left,left,left,left,left,left,left,left,left,left,left");
	TicketReview.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str");
	TicketReview.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	TicketReview.enableSmartRendering(true, 150);
	// TicketReview.setNumberFormat("0,000", 7, ".", ",");
	TicketReview.attachEvent("onRowDblClicked", TicketReviewKlik);
	TicketReview.setSkin("dhx_skyblue");
	TicketReview.init();
	refreshdata();

	function refreshdata() {
		TicketReview.clearAndLoad('xml-partner-ticket-open');

	}
</script>
@endsection