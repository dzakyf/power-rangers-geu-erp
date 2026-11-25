@extends('layout.mainlayout-cns')

@section('content-wrapper')
@include('cns.partials.page-header', [
	'title' => 'Inbound Adjustment',
	'breadcrumbs' => [
		['label' => 'Inventory'],
		['label' => 'Inbound Adjustment'],
	],
])

<div class="card cns-card">
	<div class="card-header">
		<h5 class="card-title">Transaksi Inbound Adjustment</h5>
		<div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
			<button type="button" class="btn btn-sm btn-primary" id="NewAdjustmentBtn" onclick="NewInboundAdjustment();">New Adjustment</button>
			<button type="button" class="btn btn-sm btn-info" id="DetailAdjustmentBtn" onclick="DetailInboundAdjustment();">Detail / Edit</button>
			<button type="button" class="btn btn-sm btn-success" id="ExportAdjustment" onclick="InboundAdjustmentGrid.toExcel('./export/generate.php');">Excel</button>
		</div>
	</div>
	<div class="card-body">
		<div class="cns-filter-bar">
			<div>
				<label class="d-block mb-1"><small>Dari Tanggal</small></label>
				<input type="date" id="adjFilterStartDate" class="form-control form-control-sm" style="width:140px;" />
			</div>
			<div>
				<label class="d-block mb-1"><small>Sampai Tanggal</small></label>
				<input type="date" id="adjFilterEndDate" class="form-control form-control-sm" style="width:140px;" />
			</div>
			<div>
				<label class="d-block mb-1"><small>Tangki Tujuan</small></label>
				<select id="adjFilterTank" class="form-control form-control-sm" style="width:160px;" onchange="refreshAdjData()">
					<option value="">Semua Tangki</option>
					@foreach($tanks as $t)
						<option value="{{ $t->id }}">{{ $t->name }}</option>
					@endforeach
				</select>
			</div>
			<div>
				<label class="d-block mb-1"><small>Status Approval</small></label>
				<select id="adjFilterStatus" class="form-control form-control-sm" style="width:140px;" onchange="refreshAdjData()">
					<option value="">Semua Status</option>
					<option value="PENDING">PENDING</option>
					<option value="APPROVED">APPROVED</option>
					<option value="REJECTED">REJECTED</option>
				</select>
			</div>
			<div>
				<label class="d-block mb-1"><small>Pencarian</small></label>
				<input type="text" id="adjFilterSearch" class="form-control form-control-sm" style="width:170px;" placeholder="Kode / Mov / Ket..." onkeyup="if(event.key==='Enter') refreshAdjData()" />
			</div>
			<div>
				<button type="button" class="btn btn-sm btn-primary" onclick="refreshAdjData()">Filter</button>
				<button type="button" class="btn btn-sm btn-danger ml-1" onclick="resetAdjFilters()">Reset</button>
			</div>
		</div>

		<div class="cns-grid-wrapper">
			<div id="InboundAdjustmentGrid" class="cns-grid" style="width:auto; height:420px;"></div>
		</div>
	</div>
</div>

<div id="InboundAdjustmentDet"></div>

<script>
	function NewInboundAdjustment() {
		var $btn = $('#NewAdjustmentBtn');
		$btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Memuat Form...');

		$('#InboundAdjustmentDet').html(
			'<div class="card my-3 border-info">' +
				'<div class="card-body text-center py-4 text-muted">' +
					'<i class="fa fa-spinner fa-spin fa-2x mb-2 text-primary"></i>' +
					'<div><strong class="text-dark">Memuat Data Movement & Formulir Inbound Adjustment...</strong></div>' +
					'<small>Sedang mengambil data movement dari APIPI, mohon tunggu sebentar...</small>' +
				'</div>' +
			'</div>'
		);

		LoadM('InboundAdjustmentDet', 'cns-inbound-adjustment-new');
	}

	function DetailInboundAdjustment() {
		var id = InboundAdjustmentGrid.getSelectedId();
		if (!id) {
			swal('Perhatian', 'Pilih baris data inbound adjustment terlebih dahulu.', 'warning');
			return;
		}

		var $btn = $('#DetailAdjustmentBtn');
		$btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Memuat Detail...');

		$('#InboundAdjustmentDet').html(
			'<div class="card my-3 border-info">' +
				'<div class="card-body text-center py-4 text-muted">' +
					'<i class="fa fa-spinner fa-spin fa-2x mb-2 text-primary"></i>' +
					'<div><strong class="text-dark">Memuat Detail Inbound Adjustment...</strong></div>' +
				'</div>' +
			'</div>'
		);

		LoadM('InboundAdjustmentDet', 'cns-inbound-adjustment-detail?ID=' + id);
	}

	var InboundAdjustmentPagination = null;
	function refreshAdjData() {
		if (InboundAdjustmentPagination) {
			InboundAdjustmentPagination.reload(1);
		}
	}

	function resetAdjFilters() {
		$('#adjFilterStartDate').val('');
		$('#adjFilterEndDate').val('');
		$('#adjFilterTank').val('');
		$('#adjFilterStatus').val('');
		$('#adjFilterSearch').val('');
		refreshAdjData();
	}

	InboundAdjustmentGrid = new dhtmlXGridObject('InboundAdjustmentGrid');
	InboundAdjustmentGrid.setImagePath("./dhtmlx/imgs/");
	InboundAdjustmentGrid.setHeader("No.,Kode Adjustment,Tanggal,No. Movement,Asal Gudang,Tangki Tujuan,Qty Asli (KG),Qty Adjustment (KG),Selisih (KG),Status,Keterangan,Dokumen,Dibuat Oleh,Tgl Dibuat");
	InboundAdjustmentGrid.setInitWidths("50,150,100,150,140,130,130,140,120,100,200,110,120,130");
	InboundAdjustmentGrid.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#select_filter,#select_filter,#text_filter,#text_filter,#text_filter,#select_filter,#text_filter,&nbsp;,#text_filter,#text_filter");
	InboundAdjustmentGrid.setColAlign("center,left,center,left,left,left,right,right,right,center,left,center,left,center");
	InboundAdjustmentGrid.setColTypes("dyn,ro,ro,ro,ro,ro,ro,ro,ro,cns_badge,ro,link,ro,ro");
	InboundAdjustmentGrid.attachEvent("onRowDblClicked", DetailInboundAdjustment);
	InboundAdjustmentGrid.setColSorting("int,str,date,str,str,str,int,int,int,str,str,str,str,str");
	InboundAdjustmentGrid.enableMultiline(true);
	InboundAdjustmentGrid.setSkin("dhx_skyblue");
	InboundAdjustmentGrid.init();

	InboundAdjustmentPagination = initCnsGridPagination(InboundAdjustmentGrid, {
		pageSize: 25,
		pageSizeOptions: [10, 25, 50, 100],
		serverFilter: true,
		getUrl: function (params) {
			var q = ['posStart=' + params.posStart, 'count=' + params.count];
			var startDate = $('#adjFilterStartDate').val();
			var endDate   = $('#adjFilterEndDate').val();
			var tankId    = $('#adjFilterTank').val();
			var status    = $('#adjFilterStatus').val();
			var search    = $('#adjFilterSearch').val();

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
			return 'xml-cns-inbound-adjustment?' + q.join('&');
		}
	});
	refreshAdjData();
</script>
@endsection
