@extends('layout.mainlayout-cns')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>ISCC Sustainability</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="/">ISCC Sustainability</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Traceability</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">TRACEABILITY</div>
	<div class="card">
		<div class="card-body">
			<div id="TraceGrid" style="width:auto;height:400px; background-color:white;overflow:hidden"></div>
		</div>
	</div>
	<div id="divTraceDetail"></div>
</div>

<script>
	var TraceGrid;
	var selectedFragmentId = null;

	TraceGrid = new dhtmlXGridObject('TraceGrid');
	TraceGrid.setImagePath("./dhtmlx/imgs/");
	TraceGrid.setHeader("No,Source,Supplier Name, Site, Provinsi, Surat Jalan Date, Total Qty, Status, Reference");
	TraceGrid.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	TraceGrid.setInitWidths("40,70,200,100,100,100,100,100,100,100,100");
	TraceGrid.setColAlign("center,center,left,left,left,left,left,left,left");
	TraceGrid.setColSorting("int,str,str,str,str,str,num,str,str,str,str,str,num,str,str,str,str,str");
	TraceGrid.setColTypes("ron,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	TraceGrid.enableSmartRendering(true, 150);
    TraceGrid.attachEvent('onRowDblClicked', TraceGridKlik);
	TraceGrid.setSkin("dhx_skyblue");
	TraceGrid.init();
	refreshdata();

	function refreshdata() {
		TraceGrid.clearAndLoad('cns-iscc-traceability-utama-data');
	}
	function TraceGridKlik() {
        console.log(1);
		TraceGridID = TraceGrid.getSelectedId();
		if (TraceGridID != null) {
			LoadM('divTraceDetail', 'cns-iscc-traceability-utama-detail?id=' + TraceGridID);
		}else {
			alert("Pilih baris data");
		}
		return true;
	}
</script>
@endsection
