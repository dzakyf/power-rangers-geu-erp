@extends('layout.mainlayout-cns')

@section('content-wrapper')
@include('cns.partials.page-header', [
	'title' => 'Master Tangki',
	'breadcrumbs' => [
		['label' => 'Master Data'],
		['label' => 'Master Tangki'],
	],
])

<div class="card cns-card">
	<div class="card-header">
		<h5 class="card-title mb-0">Data Master Tangki UCO</h5>
		<div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
			<button type="button" class="btn btn-sm btn-primary" id="NewTankBtn" onclick="NewTank();">New Tangki</button>
			<button type="button" class="btn btn-sm btn-info" id="DetailTankBtn" onclick="DetailTank();">Detail Tangki</button>
			<button type="button" class="btn btn-sm btn-success" id="ExportTank" onclick="MasterTank.toExcel('./export/generate.php');">Excel</button>
			<button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshdata();">Refresh</button>
		</div>
	</div>
	<div class="card-body">
		<div class="cns-filter-bar">
			<!-- <div>
				<label class="d-block mb-1"><small>Dari Tanggal</small></label>
				<input type="date" id="filterStartDate" class="form-control form-control-sm" style="width:140px;" />
			</div>
			<div>
				<label class="d-block mb-1"><small>Sampai Tanggal</small></label>
				<input type="date" id="filterEndDate" class="form-control form-control-sm" style="width:140px;" />
			</div>
			<div>
				<label class="d-block mb-1"><small>Status</small></label>
				<select id="filterStatus" class="form-control form-control-sm" style="width:140px;" onchange="refreshdata()">
					<option value="">Semua Status</option>
					<option value="ACTIVE" selected>ACTIVE</option>
					<option value="INACTIVE">INACTIVE</option>
				</select>
			</div> -->
			<div>
				<label for="filterTankName" class="d-block mb-1"><small>Nama Tangki</small></label>
				<input type="text" id="filterTankName" class="form-control form-control-sm" style="width:170px;" placeholder="Semua Tangki" onkeyup="if(event.key==='Enter') refreshdata()" />
			</div>
			<div>
				<button type="button" class="btn btn-sm btn-primary" onclick="refreshdata()">Filter</button>
				<button type="button" class="btn btn-sm btn-danger ml-1" onclick="resetFilters()">Reset</button>
			</div>
		</div>

		<div class="cns-grid-wrapper">
			<div id="MasterTank" class="cns-grid" style="width:auto; height:400px;"></div>
		</div>
	</div>
</div>

<div id="MasterTankDet"></div>

<script>
	function NewTank() {
		LoadM('MasterTankDet', 'cns-master-tank-new');
	}

	function DetailTank() {
		var MasterTankID = MasterTank.getSelectedId();
		if (!MasterTankID) {
			swal('Perhatian', 'Pilih data tangki terlebih dahulu.', 'warning');
			return;
		}
		LoadM('MasterTankDet', 'cns-master-tank-detail?ID=' + MasterTankID);
	}

	var MasterTankPagination;

	function refreshdata() {
		if (MasterTankPagination) {
			MasterTankPagination.reload(true);
		}
	}

	function resetFilters() {
		$('#filterStartDate').val('');
		$('#filterEndDate').val('');
		$('#filterStatus').val('ACTIVE');
		$('#filterTankName').val('');
		refreshdata();
	}

	MasterTank = new dhtmlXGridObject('MasterTank');
	MasterTank.setImagePath("./dhtmlx/imgs/");
	MasterTank.setHeader("No.,Nama Tangki,Kapasitas (KG),Stok Terkini (KG),Utilisasi (%),Status,Keterangan,Dibuat Oleh,Tanggal Dibuat");
	MasterTank.setInitWidths("50,160,160,160,120,110,250,150,150");
	MasterTank.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#select_filter,#text_filter,#text_filter,#text_filter");
	MasterTank.setColAlign("center,left,right,right,center,center,left,left,center");
	MasterTank.setColTypes("dyn,ro,ro,ro,ro,cns_badge,ro,ro,ro");
	MasterTank.attachEvent("onRowDblClicked", DetailTank);
	MasterTank.setColSorting("int,str,int,int,int,str,str,str,str");
	MasterTank.enableMultiline(true);
	MasterTank.setSkin("dhx_skyblue");
	MasterTank.init();
	MasterTankPagination = initCnsGridPagination(MasterTank, {
		pageSize: 25,
		pageSizeOptions: [10, 25, 50, 100],
		getUrl: function (params) {
			var q = new URLSearchParams();
			q.append('posStart', params.posStart);
			q.append('count', params.count);
			if (params.sortCol !== null && params.sortCol !== undefined) {
				q.append('sort_col', params.sortCol);
				q.append('sort_dir', params.sortDir || 'asc');
			}
			if (params.filters) {
				Object.keys(params.filters).forEach(function (colIdx) {
					q.append('filter[' + colIdx + ']', params.filters[colIdx]);
				});
			}
			var startDate = $('#filterStartDate').val();
			var endDate   = $('#filterEndDate').val();
			var status    = $('#filterStatus').val();
			var name      = $('#filterTankName').val();
			if (startDate) q.append('start_date', startDate);
			if (endDate)   q.append('end_date', endDate);
			if (status)    q.append('status', status);
			if (name)      q.append('name', name);
			return 'xml-cns-master-tank?' + q.toString();
		}
	});
	refreshdata();
</script>

@endsection
