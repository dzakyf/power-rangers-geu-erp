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

@extends('layout.mainlayout-mitra')

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
			<span class="active">Open Case</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">OPEN TICKET CASE</div>
	<div class="card">
		<div class="card-body">
			<div id="TicketOpen" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="ekselTicketOpenDet" name="ekselTicketOpenDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="TicketOpen.toExcel('./export/generate.php');" />
				<input type="button" id="detailLeads" name="detailLeads" value="Detail Ticket And Response" class="btn btn-success mt-2 mr-2" onclick="detailLeads()" />
			</div>
		</div>
	</div>
	<div id="DivProcess"></div>

</div>

<script>
	function TicketOpenKlik() {

		TicketOpenID = TicketOpen.getSelectedId();
		$("#divlistOpportunity").show();
		if (TicketOpenID != '') {
			$("#divlistProjectList").hide();
			LoadM('divlistOpportunity', 'partner-ticket-open-detail?ID=' + TicketOpenID);
		}
		return true;
	}
	TicketOpen = new dhtmlXGridObject('TicketOpen');
	TicketOpen.setImagePath("./dhtmlx/imgs/");
	TicketOpen.setHeader("No,Ticket,Created Person,Status,Tanggal Pengajuan, Jenis Permasalahan, Kategori, Judul, Deskripsi, Lampiran");
	TicketOpen.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#rspan");
	// TicketOpen.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	TicketOpen.setInitWidths("60,150,100,200,100,120,120,200,300,100");
	TicketOpen.setColAlign("center,left,left,left,left,left,left,left,left,left");
	TicketOpen.setColSorting("int,str,str,str,str,str,str,str,str,str");
	TicketOpen.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	TicketOpen.enableSmartRendering(true, 150);
	// TicketOpen.setNumberFormat("0,000", 7, ".", ",");
	TicketOpen.attachEvent("onRowDblClicked", TicketOpenKlik);
	TicketOpen.setSkin("dhx_skyblue");
	TicketOpen.init();
	refreshdata();

	function refreshdata() {
		TicketOpen.clearAndLoad('xml-partner-ticket-open');

	}
</script>
@endsection