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
			<span class="active">History Case</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">HISTORY TICKET CASE</div>
	<div class="card">
		<div class="card-body">
			<div id="TicketHistory" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="ekselTicketHistoryDet" name="ekselTicketHistoryDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="TicketHistory.toExcel('./export/generate.php');" />
				<input type="button" id="detailLeads" name="detailLeads" value="Detail Ticket And Response" class="btn btn-success mt-2 mr-2" onclick="TicketHistoryKlik()" />
			</div>
		</div>
	</div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

</div>

<script>
	function TicketHistoryKlik() {

		TicketHistoryID = TicketHistory.getSelectedId();
		if (TicketHistoryID != null) {
			LoadM('divlistOpportunity', 'partner-ticket-history-detail?Task_ID=' + TicketHistory.cells(TicketHistoryID,1).getValue());
		}else {
			alert("Pilih baris data");
		}
		return true;
	}
	TicketHistory = new dhtmlXGridObject('TicketHistory');
	TicketHistory.setImagePath("./dhtmlx/imgs/");
	TicketHistory.setHeader("No,Ticket,Created Person,Status,Tanggal Pengajuan, Jenis Permasalahan, Kategori, Judul, Deskripsi, Lampiran");
	TicketHistory.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#rspan");
	// TicketHistory.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	TicketHistory.setInitWidths("60,150,100,200,100,120,120,200,300,100");
	TicketHistory.setColAlign("center,left,left,left,left,left,left,left,left,left");
	TicketHistory.setColSorting("int,str,str,str,str,str,str,str,str,str");
	TicketHistory.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	TicketHistory.enableSmartRendering(true, 150);
	// TicketHistory.setNumberFormat("0,000", 7, ".", ",");
	TicketHistory.attachEvent("onRowDblClicked", TicketHistoryKlik);
	TicketHistory.setSkin("dhx_skyblue");
	TicketHistory.init();
	refreshdata();

	function refreshdata() {
		TicketHistory.clearAndLoad('xml-partner-ticket-history');

	}
</script>
@endsection