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
			<form id="filterForm" class="form-inline mb-2">
				<select id="fSource" name="source" class="form-control mr-2">
					<option value="">All Sources</option>
					<option value="NATURAL">Natural (MITRA)</option>
					<option value="EXTERNAL">External (Supplier)</option>
				</select>
				<input type="text" id="fCity" name="city" placeholder="City / Regency" class="form-control mr-2" />
				<input type="date" id="fDateFrom" name="date_from" class="form-control mr-2" />
				<input type="date" id="fDateTo" name="date_to" class="form-control mr-2" />
				<input type="button" value="Filter" class="btn btn-primary mr-2" onclick="applyFilter()" />
				<input type="button" value="Reset" class="btn btn-secondary" onclick="resetFilter()" />
			</form>
			<div id="TraceGrid" style="width:auto;height:400px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="editBtn" name="editBtn" value="Edit Data" class="btn btn-warning mt-2 mr-2" onclick="editSelected()" />
				<input type="button" id="exportButton" name="exportButton" value="Print Doc" class="btn btn-info mt-2 mr-2" onclick="printOutData()" />
			</div>
		</div>
	</div>
	<div id="divTraceDetail"></div>
</div>

<script>
	var TraceGrid;
	var selectedFragmentId = null;

	TraceGrid = new dhtmlXGridObject('TraceGrid');
	TraceGrid.setImagePath("./dhtmlx/imgs/");
	TraceGrid.setHeader("No,Source,POO Name,Type,City,Province,Qty (Kg),WO Code,WO Date,WO Status,PO Code,PO Date,PO Qty,PO Status,DO Code,DO Date,DO Status,");
	TraceGrid.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#cspan");
	TraceGrid.setInitWidths("40,70,200,80,120,100,80,140,90,80,140,90,80,80,140,90,80,0");
	TraceGrid.setColAlign("center,center,left,center,left,left,right,left,center,center,left,center,right,center,left,center,center,center");
	TraceGrid.setColSorting("int,str,str,str,str,str,num,str,str,str,str,str,num,str,str,str,str,str");
	TraceGrid.setColTypes("ron,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	TraceGrid.enableSmartRendering(true, 150);
	TraceGrid.attachEvent("onRowSelect", function(id) {
		var src = TraceGrid.cells(id, 1).getValue();
		var fid = TraceGrid.cells(id, 17).getValue();
		selectedFragmentId = (fid && fid !== '') ? fid : null;
		document.getElementById('editBtn').style.display = (src === 'EXTERNAL') ? '' : 'none';
	});
	TraceGrid.setSkin("dhx_skyblue");
	TraceGrid.init();
	TraceGrid.setColumnHidden(17, true);
	refreshdata();

	function refreshdata() {
		var params = [];
		var source = document.getElementById('fSource').value;
		var city = document.getElementById('fCity').value;
		var dateFrom = document.getElementById('fDateFrom').value;
		var dateTo = document.getElementById('fDateTo').value;
		if (source) params.push('source=' + encodeURIComponent(source));
		if (city) params.push('city=' + encodeURIComponent(city));
		if (dateFrom) params.push('date_from=' + encodeURIComponent(dateFrom));
		if (dateTo) params.push('date_to=' + encodeURIComponent(dateTo));
		var qs = params.length ? ('?' + params.join('&')) : '';
		TraceGrid.clearAndLoad('cns-iscc-traceability-data' + qs);
	}

	function applyFilter() {
		refreshdata();
	}

	function resetFilter() {
		document.getElementById('fSource').value = '';
		document.getElementById('fCity').value = '';
		document.getElementById('fDateFrom').value = '';
		document.getElementById('fDateTo').value = '';
		refreshdata();
	}

	function editSelected() {
		if (!selectedFragmentId) {
			alert('Select an EXTERNAL (Supplier) row to edit.');
			return;
		}
		LoadM('divTraceDetail', 'cns-iscc-traceability-edit/' + selectedFragmentId);
	}

	function printOutData() {
		if (!selectedFragmentId) {
			alert('Select row to print.');
			return;
		}
		window.open('template_iscc.docx','_blank');
	}
</script>
@endsection
