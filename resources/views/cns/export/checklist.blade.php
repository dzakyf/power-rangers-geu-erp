@extends('layout.mainlayout-cns')

@section('content-wrapper')
@csrf
<style>
	#ExportChecklistGrid table.obj tr {
		height: 44px;
	}
	#ExportChecklistGrid table.obj td {
		vertical-align: middle;
		padding-top: 6px;
		padding-bottom: 6px;
	}
</style>
@include('cns.partials.page-header', [
	'title' => 'Export Document Checklist',
	'breadcrumbs' => [
		['label' => 'Export Management'],
		['label' => 'Export Checklist'],
	],
])

<div class="card cns-card">
	<div class="card-header">
		<h5 class="card-title mb-0">Export Document Checklist</h5>
		<div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
			<input type="button" id="DetailChecklist" value="Detail / Verifikasi" class="btn btn-sm btn-info" onclick="ExportChecklistDetail()" />
			<input type="button" id="ExportExcelChecklist" class="btn btn-sm btn-warning" value="Export Excel" onclick="ExportChecklistGrid.toExcel('./export/generate.php');" />
			<button type="button" onclick="refreshdata()" class="btn btn-sm btn-outline-secondary">Refresh</button>
		</div>
	</div>
	<div class="card-body">
		<div class="cns-filter-bar">
			<div>
				<label for="tgl1" class="d-block mb-1"><small>Dari Tanggal</small></label>
				<input type="date" id="tgl1" name="tgl1" value="" class="form-control form-control-sm" style="width:auto;" />
			</div>
			<div>
				<label for="tgl2" class="d-block mb-1"><small>Sampai Tanggal</small></label>
				<input type="date" id="tgl2" name="tgl2" value="" class="form-control form-control-sm" style="width:auto;" />
			</div>
			<div>
				<button type="button" onclick="refreshdata()" class="btn btn-sm btn-primary">Filter</button>
			</div>
		</div>
		<div class="cns-grid-wrapper">
			<div id="ExportChecklistGrid" class="cns-grid" style="width:100%;max-width:100%;height:360px;background-color:white;overflow:hidden"></div>
		</div>
	</div>
</div>
<div id="divlistOpportunity"></div>

<script>
	function ExportChecklistDetail() {
		var selectedId = ExportChecklistGrid.getSelectedId();
		if (selectedId != null) {
			LoadM('divlistOpportunity', 'cns-export-checklist-detail?Task=' + encodeURIComponent(selectedId));
		} else {
			swal('Perhatian', 'Pilih baris data', 'warning');
		}
		return true;
	}

	ExportChecklistGrid = new dhtmlXGridObject('ExportChecklistGrid');
	ExportChecklistGrid.setImagePath("./dhtmlx/imgs/");
	ExportChecklistGrid.setHeader("No,Contract No,Booking Confirmation,Created Date,Timeline,Progress (%),Verified,Catatan");
	ExportChecklistGrid.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#rspan,#text_filter,#select_filter,#text_filter");
	ExportChecklistGrid.setInitWidths("45,150,160,110,600,95,80,*");
	ExportChecklistGrid.setColAlign("center,left,left,left,center,center,center,left");
	ExportChecklistGrid.setColSorting("int,str,str,str,na,str,str,str");
	ExportChecklistGrid.setColTypes("ron,ro,ro,ro,ro,ro,cns_badge,ro");
	ExportChecklistGrid.enableMultiline(true);
	ExportChecklistGrid.enableAutoHeight(true, 420);
	ExportChecklistGrid.attachEvent("onRowDblClicked", ExportChecklistDetail);
	ExportChecklistGrid.setSkin("dhx_skyblue");
	ExportChecklistGrid.init();

	var ExportChecklistPagination = initCnsGridPagination(ExportChecklistGrid, {
		pageSize: 25,
		pageSizeOptions: [10, 25, 50, 100],
		serverFilter: false,
		getUrl: function (params) {
			var q = [
				'posStart=' + params.posStart,
				'count=' + params.count,
				'tgl1=' + encodeURIComponent($('#tgl1').val()),
				'tgl2=' + encodeURIComponent($('#tgl2').val())
			];
			if (params.sortCol !== null && params.sortCol !== undefined) {
				q.push('sort_col=' + params.sortCol);
				q.push('sort_dir=' + (params.sortDir || 'asc'));
			}
			return 'xml-cns-export-checklist?' + q.join('&');
		},
		onLoaded: function () {
			ExportChecklistGrid.setSizes();
		}
	});
	refreshdata();

	$(window).on('resize', function () {
		ExportChecklistGrid.setSizes();
	});

	function refreshdata() {
		ExportChecklistPagination.reload(1);
	}
</script>
@endsection
