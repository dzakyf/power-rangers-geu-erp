<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;



?>

@extends('layout.mainlayout')

@section('content-wrapper')
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Report</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="#">PROFORMA & PO</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">History Penagihan BA</span>
		</li>
	</ol>
</div>
<!-- End Main Content Header -->

<!-- Stats Card -->

<div class="row">
	<div style="font-size:12px;width:auto;height:50px;text-align:middle;vertical-align:middle" class='badan'>
		Year <input type="text" id="Tgl1" size="10" value="<?php echo date('Y') ?>" maxlength="4" />
		<input type="button" id="SubmitCheck" value="Submit" class="btn btn-success mt-2 mr-2" />
	</div>


	<div id="BRTBDIV" style="width:auto;height:450px; background-color:white;overflow:hidden"></div>
	<div id="coaWin"></div>
	<div>
		<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="BRTB.toExcel('./export/generate.php');" />
	</div>
</div>
<div>


	<script>
		BRTB = new dhtmlXGridObject('BRTBDIV');
		BRTB.setImagePath("./dhtmlx/imgs/");
		BRTB.setHeader("No,Subject,Start,End,Total,Jan,#cspan,Feb,#cspan,Mar,#cspan,Apr,#cspan,Mei,#cspan,Jun,#cspan,Jul,#cspan,Agt,#cspan,Sep,#cspan,Okt,#cspan,Nov,#cspan,Des,#cspan");
		BRTB.attachHeader("#rspan,#text_filter,#rspan,#rspan,#rspan,RUTIN,TIDAK RUTIN,RUTIN,TIDAK RUTIN,RUTIN,TIDAK RUTIN,RUTIN,TIDAK RUTIN,RUTIN,TIDAK RUTIN,RUTIN,TIDAK RUTIN,RUTIN,TIDAK RUTIN,RUTIN,TIDAK RUTIN,RUTIN,TIDAK RUTIN,RUTIN,TIDAK RUTIN,RUTIN,TIDAK RUTIN,RUTIN,TIDAK RUTIN");
		BRTB.attachFooter("Total,#cspan,#cspan,#cspan,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total");
		BRTB.setInitWidths("50,250,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100");
		BRTB.setColAlign("center,left,left,left,center,right,right,right,right,right,right,right,right,right,right,right,right,right,right,right,right,right,right,right,right,right,right");
		BRTB.setColSorting("int,str,str,str,str,int,int,int,int,int,int,int,int,int,int,int,int,int,int,int,int,int,int,int,int,int,int,int,int");
		BRTB.setColTypes("ro,ro,ro,ro,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron");
		BRTB.setNumberFormat("0,000", 4, ".", ",");
		BRTB.setNumberFormat("0,000", 5, ".", ",");
		BRTB.setNumberFormat("0,000", 6, ".", ",");
		BRTB.setNumberFormat("0,000", 7, ".", ",");
		BRTB.setNumberFormat("0,000", 8, ".", ",");
		BRTB.setNumberFormat("0,000", 9, ".", ",");
		BRTB.setNumberFormat("0,000", 10, ".", ",");
		BRTB.setNumberFormat("0,000", 11, ".", ",");
		BRTB.setNumberFormat("0,000", 12, ".", ",");
		BRTB.setNumberFormat("0,000", 13, ".", ",");
		BRTB.setNumberFormat("0,000", 14, ".", ",");
		BRTB.setNumberFormat("0,000", 15, ".", ",");
		BRTB.setNumberFormat("0,000", 16, ".", ",");
		BRTB.setNumberFormat("0,000", 17, ".", ",");
		BRTB.setNumberFormat("0,000", 18, ".", ",");
		BRTB.setNumberFormat("0,000", 19, ".", ",");
		BRTB.setNumberFormat("0,000", 20, ".", ",");
		BRTB.setNumberFormat("0,000", 21, ".", ",");
		BRTB.setNumberFormat("0,000", 22, ".", ",");
		BRTB.setNumberFormat("0,000", 23, ".", ",");
		BRTB.setNumberFormat("0,000", 24, ".", ",");
		BRTB.setNumberFormat("0,000", 25, ".", ",");
		BRTB.setNumberFormat("0,000", 26, ".", ",");
		BRTB.setNumberFormat("0,000", 27, ".", ",");
		BRTB.setNumberFormat("0,000", 28, ".", ",");
		BRTB.setNumberFormat("0,000", 29, ".", ",");
		BRTB.setNumberFormat("0,000", 30, ".", ",");
		BRTB.setEditable(false);
		BRTB.enableSmartRendering(true, 100);
		BRTB.setSkin("dhx_skyblue");
		BRTB.init();

		$("#SubmitCheck").click(function() {
			$.blockUI();
			BRTB.clearAndLoad('xml-sales-proforma-posted-history?Tgl1=' + $("#Tgl1").val(), itungx);
		});

		function itungx() {
			$.unblockUI();

			return true;
		}
	</script>



	@endsection


	@push('head')
	<style>
		div.gridbox_dhx_skyblue table.hdr div.hdrcell {
			text-align: center;
			vertical-align: middle !important;
		}
	</style>
	@endpush