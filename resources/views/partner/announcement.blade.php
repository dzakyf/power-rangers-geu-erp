<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

?>

@extends('layout.mainlayout-mitra')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Partner</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="/">Announcement</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Announcement List</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">ANNOUNCEMENT LIST</div>
	<div class="card">
		<div class="card-body">
			<div id="AnnouncementList" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="ekselAnnouncementListDet" name="ekselAnnouncementListDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="AnnouncementList.toExcel('./export/generate.php');" />
				<input type="button" id="detailLeads" name="detailLeads" value="Detail Ticket And Response" class="btn btn-success mt-2 mr-2" onclick="AnnouncementListKlik()" />
			</div>
		</div>
	</div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

</div>

<script>
	function AnnouncementListKlik() {

		AnnouncementListID = AnnouncementList.getSelectedId();
		if (AnnouncementListID != null) {
			LoadM('divlistOpportunity', 'partner-ticket-open-detail?Task_ID=' + AnnouncementList.cells(AnnouncementListID,1).getValue());
		}else {
			alert("Pilih baris data");
		}
		return true;
	}
	AnnouncementList = new dhtmlXGridObject('AnnouncementList');
	AnnouncementList.setImagePath("./dhtmlx/imgs/");
	AnnouncementList.setHeader("No,Tanggal, Judul, Isi Pengumuman, Status, Tanggal Pembuatan, Creator");
	AnnouncementList.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// AnnouncementList.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	AnnouncementList.setInitWidths("60,150,100,200,100,120,120");
	AnnouncementList.setColAlign("center,left,left,left,left,left,left");
	AnnouncementList.setColSorting("int,str,str,str,str,str,str");
	AnnouncementList.setColTypes("ro,ro,ro,ro,ro,ro,ro");
	AnnouncementList.enableSmartRendering(true, 150);
	// AnnouncementList.setNumberFormat("0,000", 7, ".", ",");
	AnnouncementList.attachEvent("onRowDblClicked", AnnouncementListKlik);
	AnnouncementList.setSkin("dhx_skyblue");
	AnnouncementList.init();
	refreshdata();

	function refreshdata() {
		AnnouncementList.clearAndLoad('xml-partner-ticket-open');

	}
</script>
@endsection