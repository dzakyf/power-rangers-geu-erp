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
			<span class="active">Inbound Adjustment</span>
		</li>
	</ol>
</div>
<!-- End Main Content Header -->

<div class="row m-1">
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">TRANSAKSI INBOUND ADJUSTMENT</div>

	<div class="card w-100">
		<div class="card-body">
			<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
				<div class="d-flex align-items-end flex-wrap gap-2">
					<div class="m-1">
						<label class="d-block mb-1"><small>Dari Tanggal</small></label>
						<input type="date" id="adjFilterStartDate" class="form-control form-control-sm" style="width:140px;" />
					</div>
					<div class="m-1">
						<label class="d-block mb-1"><small>Sampai Tanggal</small></label>
						<input type="date" id="adjFilterEndDate" class="form-control form-control-sm" style="width:140px;" />
					</div>
					<div class="m-1">
						<label class="d-block mb-1"><small>Tangki Tujuan</small></label>
						<select id="adjFilterTank" class="form-control form-control-sm" style="width:160px;" onchange="refreshAdjData()">
							<option value="">Semua Tangki</option>
							@foreach($tanks as $t)
								<option value="{{ $t->id }}">{{ $t->name }}</option>
							@endforeach
						</select>
					</div>
					<div class="m-1">
						<label class="d-block mb-1"><small>Status Approval</small></label>
						<select id="adjFilterStatus" class="form-control form-control-sm" style="width:140px;" onchange="refreshAdjData()">
							<option value="">Semua Status</option>
							<option value="PENDING">PENDING</option>
							<option value="APPROVED">APPROVED</option>
							<option value="REJECTED">REJECTED</option>
						</select>
					</div>
					<div class="m-1">
						<label class="d-block mb-1"><small>Pencarian</small></label>
						<input type="text" id="adjFilterSearch" class="form-control form-control-sm" style="width:170px;" placeholder="Kode / Mov / Ket..." onkeyup="if(event.key==='Enter') refreshAdjData()" />
					</div>
					<div class="m-1">
						<button class="btn btn-sm btn-primary" onclick="refreshAdjData()">Filter</button>
						<button class="btn btn-sm btn-danger ml-1" onclick="resetAdjFilters()">Reset</button>
					</div>
				</div>
			</div>

			<div class="m-2">
				<div id="InboundAdjustmentGrid" style="width:auto; height:420px;"></div>
			</div>
			<div class="mt-2">
				<button class="btn btn-primary" id="NewAdjustmentBtn" onclick="NewInboundAdjustment();">New Adjustment</button>
				<button class="btn btn-info" id="DetailAdjustmentBtn" onclick="DetailInboundAdjustment();">Detail / Edit</button>
				<button class="btn btn-success" id="ExportAdjustment" onclick="InboundAdjustmentGrid.toExcel('./export/generate.php');">Excel</button>
			</div>
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
			alert('Pilih baris data inbound adjustment terlebih dahulu.');
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

	function refreshAdjData() {
		var startDate = $('#adjFilterStartDate').val();
		var endDate   = $('#adjFilterEndDate').val();
		var tankId    = $('#adjFilterTank').val();
		var status    = $('#adjFilterStatus').val();
		var search    = $('#adjFilterSearch').val();

		var params = [];
		if (startDate) params.push('start_date=' + encodeURIComponent(startDate));
		if (endDate)   params.push('end_date=' + encodeURIComponent(endDate));
		if (tankId)    params.push('tank_id=' + encodeURIComponent(tankId));
		if (status)    params.push('approval_status=' + encodeURIComponent(status));
		if (search)    params.push('search=' + encodeURIComponent(search));

		var url = 'xml-cns-inbound-adjustment' + (params.length ? '?' + params.join('&') : '');
		InboundAdjustmentGrid.clearAndLoad(url);
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
	InboundAdjustmentGrid.setColTypes("dyn,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,link,ro,ro");
	InboundAdjustmentGrid.attachEvent("onRowDblClicked", DetailInboundAdjustment);
	InboundAdjustmentGrid.setColSorting("int,str,date,str,str,str,int,int,int,str,str,str,str,str");
	InboundAdjustmentGrid.enableSmartRendering(true, 50);
	InboundAdjustmentGrid.enableMultiline(true);
	InboundAdjustmentGrid.setSkin("dhx_skyblue");
	InboundAdjustmentGrid.init();
	refreshAdjData();
</script>
@endsection
