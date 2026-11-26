@extends('layout.mainlayout-cns')

@section('content-wrapper')
@include('cns.partials.page-header', [
	'title' => 'Drain Adjustment',
	'breadcrumbs' => [
		['label' => 'Inventory'],
		['label' => 'Drain Adjustment'],
	],
])

<div class="card cns-card">
	<div class="card-header">
		<h5 class="card-title">Transaksi Drain Adjustment</h5>
		<div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
			<button type="button" class="btn btn-sm btn-primary" id="NewDrainAdjBtn" onclick="NewDrainAdjustment();">New Adjustment</button>
			<button type="button" class="btn btn-sm btn-info" id="DetailDrainAdjBtn" onclick="DetailDrainAdjustment();">Detail / Edit</button>
			<button type="button" class="btn btn-sm btn-success" id="ExportDrainAdj" onclick="DrainAdjustmentGrid.toExcel('./export/generate.php');">Excel</button>
		</div>
	</div>
	<div class="card-body">
		<div class="cns-filter-bar">
			<div>
				<label class="d-block mb-1"><small>Dari Tanggal</small></label>
				<input type="date" id="drainAdjFilterStartDate" class="text-black form-control form-control-sm" style="width:140px;" />
			</div>
			<div>
				<label class="d-block mb-1"><small>Sampai Tanggal</small></label>
				<input type="date" id="drainAdjFilterEndDate" class="text-black form-control form-control-sm" style="width:140px;" />
			</div>
			<div>
				<label class="d-block mb-1"><small>Tangki</small></label>
				<select id="drainAdjFilterTank" class="text-black form-control form-control-sm" style="width:160px;" onchange="refreshDrainAdjData()">
					<option value="">Semua Tangki</option>
					@foreach($tanks as $t)
						<option value="{{ $t->id }}">{{ $t->name }}</option>
					@endforeach
				</select>
			</div>
			<div>
				<label class="d-block mb-1"><small>Status Approval</small></label>
				<select id="drainAdjFilterStatus" class="text-black form-control form-control-sm" style="width:140px;" onchange="refreshDrainAdjData()">
					<option value="">Semua Status</option>
					<option value="PENDING">PENDING</option>
					<option value="APPROVED">APPROVED</option>
					<option value="REJECTED">REJECTED</option>
				</select>
			</div>
			<div>
				<label class="d-block mb-1"><small>Pencarian</small></label>
				<input type="text" id="drainAdjFilterSearch" class="text-black form-control form-control-sm" style="width:170px;" placeholder="Kode / Tangki / Ket..." onkeyup="if(event.key==='Enter') refreshDrainAdjData()" />
			</div>
			<div>
				<button type="button" class="btn btn-sm btn-primary" onclick="refreshDrainAdjData()">Filter</button>
				<button type="button" class="btn btn-sm btn-danger ml-1" onclick="resetDrainAdjFilters()">Reset</button>
			</div>
		</div>

		<div class="cns-grid-wrapper">
			<div id="DrainAdjustmentGrid" class="cns-grid" style="width:auto; height:420px;"></div>
		</div>
	</div>
</div>

<div id="DrainAdjustmentDet"></div>

<script>
	function NewDrainAdjustment() {
		var $btn = $('#NewDrainAdjBtn');
		$btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Memuat Form...');

		$('#DrainAdjustmentDet').html(
			'<div class="card my-3 border-info">' +
				'<div class="card-body text-center py-4 text-muted">' +
					'<i class="fa fa-spinner fa-spin fa-2x mb-2 text-primary"></i>' +
					'<div><strong class="text-dark">Memuat Formulir Drain Adjustment...</strong></div>' +
				'</div>' +
			'</div>'
		);

		LoadM('DrainAdjustmentDet', 'cns-drain-adjustment-new');
	}

	function DetailDrainAdjustment() {
		var id = DrainAdjustmentGrid.getSelectedId();
		if (!id) {
			swal('Perhatian', 'Pilih baris data drain adjustment terlebih dahulu.', 'warning');
			return;
		}

		var $btn = $('#DetailDrainAdjBtn');
		$btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Memuat Detail...');

		$('#DrainAdjustmentDet').html(
			'<div class="card my-3 border-info">' +
				'<div class="card-body text-center py-4 text-muted">' +
					'<i class="fa fa-spinner fa-spin fa-2x mb-2 text-primary"></i>' +
					'<div><strong class="text-dark">Memuat Detail Drain Adjustment...</strong></div>' +
				'</div>' +
			'</div>'
		);

		LoadM('DrainAdjustmentDet', 'cns-drain-adjustment-detail?ID=' + id);
	}

	var DrainAdjustmentPagination = null;
	function refreshDrainAdjData() {
		if (DrainAdjustmentPagination) {
			DrainAdjustmentPagination.reload(1);
		}
	}

	function resetDrainAdjFilters() {
		$('#drainAdjFilterStartDate').val('');
		$('#drainAdjFilterEndDate').val('');
		$('#drainAdjFilterTank').val('');
		$('#drainAdjFilterStatus').val('');
		$('#drainAdjFilterSearch').val('');
		refreshDrainAdjData();
	}

	DrainAdjustmentGrid = new dhtmlXGridObject('DrainAdjustmentGrid');
	DrainAdjustmentGrid.setImagePath("./dhtmlx/imgs/");
	DrainAdjustmentGrid.setHeader("No.,Kode Adjustment,Tanggal,Tangki,Qty Sistem (KG),Qty Timbang (KG),Selisih (KG),Status,Keterangan,Dokumen,Dibuat Oleh,Tgl Dibuat");
	DrainAdjustmentGrid.setInitWidths("50,150,100,140,130,130,130,100,220,110,120,130");
	DrainAdjustmentGrid.attachHeader("&nbsp;,#text_filter,#text_filter,#select_filter,#text_filter,#text_filter,#text_filter,#select_filter,#text_filter,&nbsp;,#text_filter,#text_filter");
	DrainAdjustmentGrid.setColAlign("center,left,center,left,right,right,right,center,left,center,left,center");
	DrainAdjustmentGrid.setColTypes("dyn,ro,ro,ro,ro,ro,ro,cns_badge,ro,cns_doc,ro,ro");
	DrainAdjustmentGrid.attachEvent("onRowDblClicked", DetailDrainAdjustment);
	DrainAdjustmentGrid.setColSorting("int,str,date,str,int,int,int,str,str,str,str,str");
	DrainAdjustmentGrid.enableMultiline(true);
	DrainAdjustmentGrid.setSkin("dhx_skyblue");
	DrainAdjustmentGrid.init();

	DrainAdjustmentPagination = initCnsGridPagination(DrainAdjustmentGrid, {
		pageSize: 25,
		pageSizeOptions: [10, 25, 50, 100],
		serverFilter: true,
		getUrl: function (params) {
			var q = ['posStart=' + params.posStart, 'count=' + params.count];
			var startDate = $('#drainAdjFilterStartDate').val();
			var endDate   = $('#drainAdjFilterEndDate').val();
			var tankId    = $('#drainAdjFilterTank').val();
			var status    = $('#drainAdjFilterStatus').val();
			var search    = $('#drainAdjFilterSearch').val();

			if (startDate) q.push('start_date=' + encodeURIComponent(startDate));
			if (endDate)   q.push('end_date=' + encodeURIComponent(endDate));
			if (tankId)    q.push('tank_id=' + encodeURIComponent(tankId));
			if (status)    q.push('approval_status=' + encodeURIComponent(status));
			if (search)    q.push('search=' + encodeURIComponent(search));

			if (params.sortCol !== null && params.sortCol !== undefined) {
				q.push('sort_col=' + params.sortCol);
				q.push('sort_dir=' + (params.sortDir || 'asc'));
			}
			if (params.filters) {
				for (var colIdx in params.filters) {
					q.push('filter[' + colIdx + ']=' + encodeURIComponent(params.filters[colIdx]));
				}
			}
			return 'xml-cns-drain-adjustment?' + q.join('&');
		}
	});
	refreshDrainAdjData();
</script>
@endsection
