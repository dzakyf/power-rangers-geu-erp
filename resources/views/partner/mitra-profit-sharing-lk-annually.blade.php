@extends('layout.mainlayout-bp')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Partner</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="/">Warehouse</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Monitoring Monthly</span>
		</li>
	</ol>
</div>

<div class="card mt-3">
    <div class="card-body">
		<div>
			<input type="text" id="date_filter" name="date_filter" value="{{ date('Y') }}" size="10" readonly>
			<button class="btn btn-success" onclick="refreshdataDetail()">Refresh</button>
		</div>
        <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">LAPORAN KEUANGAN</div>
        <div id="ProfitSharingLkAnnually" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
        <div>
            <input type="button" id="ekselProfitSharingLkAnnuallyDet" name="ekselProfitSharingLkAnnuallyDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="ProfitSharingLkAnnually.toExcel('./export/generate.php');" />
            {{-- <input type="button" id="detailProfitSharingLkAnnually" name="detailProfitSharingLkAnnually" value="Detail Warehouse Profit Sharing LK Annually" class="btn btn-info mt-2 mr-2" onclick="ProfitSharingLkAnnuallyLKManually()" /> --}}
        </div>
    </div>
</div>
<div id="divlistOpportunityDetail"></div>

<script>
	var myCalendar;
	myCalendar = new dhtmlXCalendarObject(["date_filter"]);
	myCalendar.setDateFormat("%Y");
	// function ProfitSharingLkAnnuallyLKManually() {

	// 	ProfitSharingLkAnnuallyID = ProfitSharingLkAnnually.getSelectedId();
	// 	if (ProfitSharingLkAnnuallyID != null) {
	// 		LoadM('divlistOpportunity', 'partner-profit-sharing-lk-annually?Task=' + ProfitSharingLkAnnuallyID);
	// 	}else {
	// 		alert("Pilih baris data");
	// 	}
	// 	return true;
	// }
	ProfitSharingLkAnnually = new dhtmlXGridObject('ProfitSharingLkAnnually');
	ProfitSharingLkAnnually.setImagePath("./dhtmlx/imgs/");
	ProfitSharingLkAnnually.setHeader(",Description, Value, Satuan, Percentage");
	ProfitSharingLkAnnually.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter");
	// ProfitSharingLkAnnually.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	ProfitSharingLkAnnually.setInitWidths("60,200,150,100,80");
	ProfitSharingLkAnnually.setColAlign("center,left,right,center,right");
	ProfitSharingLkAnnually.setColSorting("int,str,int,str,str");
	ProfitSharingLkAnnually.setColTypes("ron,ro,ron,ro,ro,ro");
	ProfitSharingLkAnnually.enableSmartRendering(true, 150);
	ProfitSharingLkAnnually.setNumberFormat("0,000", 2, ".", ",");
	// ProfitSharingLkAnnually.attachEvent("onRowDblClicked", ProfitSharingLkAnnuallyLKManually);
	ProfitSharingLkAnnually.setSkin("dhx_skyblue");
	ProfitSharingLkAnnually.init();
	refreshdataDetail();

	function refreshdataDetail() {
		let type = "{{ $getMitra->warehouse_type == '' ? 'booth' : $getMitra->warehouse_type }}";
		ProfitSharingLkAnnually.clearAndLoad('xml-partner-profit-sharing-lk-annually?Task={{$getMitra->warehouse_task_id}}&date=' + $('#date_filter').val() + "&type=" + type);
	}
</script>
@endsection