@extends('layout.mainlayout-cns')

@section('content-wrapper')
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>CNS</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="#">Inventory</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Drain Adjustment</span>
		</li>
	</ol>
</div>
<!-- End Main Content Header -->

<div class="row m-1">
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">TRANSAKSI DRAIN ADJUSTMENT</div>

	<div class="card w-100">
		<div class="card-body">
			<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
				<div class="d-flex align-items-end flex-wrap gap-2">
					<div class="m-1">
						<label class="d-block mb-1"><small>Dari Tanggal</small></label>
						<input type="date" id="drainAdjFilterStartDate" class="form-control form-control-sm" style="width:140px;" />
					</div>
					<div class="m-1">
						<label class="d-block mb-1"><small>Sampai Tanggal</small></label>
						<input type="date" id="drainAdjFilterEndDate" class="form-control form-control-sm" style="width:140px;" />
					</div>
					<div class="m-1">
						<label class="d-block mb-1"><small>Tangki</small></label>
						<select id="drainAdjFilterTank" class="form-control form-control-sm" style="width:160px;" onchange="refreshDrainAdjData()">
							<option value="">Semua Tangki</option>
							@foreach($tanks as $t)
								<option value="{{ $t->id }}">{{ $t->name }}</option>
							@endforeach
						</select>
					</div>
					<div class="m-1">
						<label class="d-block mb-1"><small>Status Approval</small></label>
						<select id="drainAdjFilterStatus" class="form-control form-control-sm" style="width:140px;" onchange="refreshDrainAdjData()">
							<option value="">Semua Status</option>
							<option value="PENDING">PENDING</option>
							<option value="APPROVED">APPROVED</option>
							<option value="REJECTED">REJECTED</option>
						</select>
					</div>
					<div class="m-1">
						<label class="d-block mb-1"><small>Pencarian</small></label>
						<input type="text" id="drainAdjFilterSearch" class="form-control form-control-sm" style="width:170px;" placeholder="Kode / Tangki / Ket..." onkeyup="if(event.key==='Enter') refreshDrainAdjData()" />
					</div>
					<div class="m-1">
						<button class="btn btn-sm btn-primary" onclick="refreshDrainAdjData()">Filter</button>
						<button class="btn btn-sm btn-danger ml-1" onclick="resetDrainAdjFilters()">Reset</button>
					</div>
				</div>
			</div>

			<div class="m-2">
				<div id="DrainAdjustmentGrid" style="width:auto; height:420px;"></div>
			</div>
			<div class="mt-2">
				<button class="btn btn-primary" id="NewDrainAdjBtn" onclick="NewDrainAdjustment();">New Adjustment</button>
				<button class="btn btn-info" id="DetailDrainAdjBtn" onclick="DetailDrainAdjustment();">Detail / Edit</button>
				<button class="btn btn-success" id="ExportDrainAdj" onclick="DrainAdjustmentGrid.toExcel('./export/generate.php');">Excel</button>
			</div>
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
			alert('Pilih baris data drain adjustment terlebih dahulu.');
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

	function refreshDrainAdjData() {
		var startDate = $('#drainAdjFilterStartDate').val();
		var endDate   = $('#drainAdjFilterEndDate').val();
		var tankId    = $('#drainAdjFilterTank').val();
		var status    = $('#drainAdjFilterStatus').val();
		var search    = $('#drainAdjFilterSearch').val();

		var params = [];
		if (startDate) params.push('start_date=' + encodeURIComponent(startDate));
		if (endDate)   params.push('end_date=' + encodeURIComponent(endDate));
		if (tankId)    params.push('tank_id=' + encodeURIComponent(tankId));
		if (status)    params.push('approval_status=' + encodeURIComponent(status));
		if (search)    params.push('search=' + encodeURIComponent(search));

		var url = 'xml-cns-drain-adjustment' + (params.length ? '?' + params.join('&') : '');
		DrainAdjustmentGrid.clearAndLoad(url);
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
	DrainAdjustmentGrid.setColTypes("dyn,ro,ro,ro,ro,ro,ro,ro,ro,link,ro,ro");
	DrainAdjustmentGrid.attachEvent("onRowDblClicked", DetailDrainAdjustment);
	DrainAdjustmentGrid.setColSorting("int,str,date,str,int,int,int,str,str,str,str,str");
	DrainAdjustmentGrid.enableSmartRendering(true, 50);
	DrainAdjustmentGrid.enableMultiline(true);
	DrainAdjustmentGrid.setSkin("dhx_skyblue");
	DrainAdjustmentGrid.init();
	refreshDrainAdjData();
</script>
@endsection
