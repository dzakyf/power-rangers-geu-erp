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
			<span class="active">Tank Draining</span>
		</li>
	</ol>
</div>
<!-- End Main Content Header -->

<div class="row m-1">
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">TRANSAKSI TANK DRAINING</div>

	<div class="card w-100">
		<div class="card-body">
			<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
				<div class="d-flex align-items-end flex-wrap gap-2">
					<div class="m-1">
						<label class="d-block mb-1"><small>Dari Tanggal</small></label>
						<input type="date" id="filterStartDate" class="form-control form-control-sm" style="width:140px;" />
					</div>
					<div class="m-1">
						<label class="d-block mb-1"><small>Sampai Tanggal</small></label>
						<input type="date" id="filterEndDate" class="form-control form-control-sm" style="width:140px;" />
					</div>
					<div class="m-1">
						<label class="d-block mb-1"><small>Tangki Asal</small></label>
						<select id="filterTank" class="form-control form-control-sm" style="width:160px;" onchange="refreshdata()">
							<option value="">Semua Tangki</option>
							@foreach($tanks as $t)
								<option value="{{ $t->id }}">{{ $t->name }}</option>
							@endforeach
						</select>
					</div>
					<div class="m-1">
						<label class="d-block mb-1"><small>Tujuan Draining</small></label>
						<select id="filterTargetType" class="form-control form-control-sm" style="width:140px;" onchange="refreshdata()">
							<option value="">Semua Tujuan</option>
							<option value="MOVING">MOVING</option>
							<option value="PENJUALAN">PENJUALAN</option>
						</select>
					</div>
					<div class="m-1">
						<label class="d-block mb-1"><small>Pencarian</small></label>
						<input type="text" id="filterSearch" class="form-control form-control-sm" style="width:170px;" placeholder="Kode / SO / Ket..." onkeyup="if(event.key==='Enter') refreshdata()" />
					</div>
					<div class="m-1">
						<button class="btn btn-sm btn-primary" onclick="refreshdata()">Filter</button>
						<button class="btn btn-sm btn-danger ml-1" onclick="resetFilters()">Reset</button>
					</div>
				</div>
			</div>

			<div class="m-2">
				<div id="TankDrainingGrid" style="width:auto; height:420px;"></div>
			</div>
			<div class="mt-2">
				<button class="btn btn-primary" id="NewDrainingBtn" onclick="NewDraining();">New Draining</button>
				<button class="btn btn-info" id="DetailDrainingBtn" onclick="DetailDraining();">Detail / Edit</button>
				<button class="btn btn-success" id="ExportDraining" onclick="TankDrainingGrid.toExcel('./export/generate.php');">Excel</button>
			</div>
		</div>
	</div>
</div>

<div id="TankDrainingDet"></div>

<script>
	function NewDraining() {
		LoadM('TankDrainingDet', 'cns-tank-draining-new');
	}

	function DetailDraining() {
		var id = TankDrainingGrid.getSelectedId();
		if (!id) {
			alert('Pilih baris data tank draining terlebih dahulu.');
			return;
		}
		LoadM('TankDrainingDet', 'cns-tank-draining-detail?ID=' + id);
	}

	function refreshdata() {
		var startDate  = $('#filterStartDate').val();
		var endDate    = $('#filterEndDate').val();
		var tankId     = $('#filterTank').val();
		var targetType = $('#filterTargetType').val();
		var search     = $('#filterSearch').val();

		var params = [];
		if (startDate)  params.push('start_date=' + encodeURIComponent(startDate));
		if (endDate)    params.push('end_date=' + encodeURIComponent(endDate));
		if (tankId)     params.push('tank_id=' + encodeURIComponent(tankId));
		if (targetType) params.push('target_type=' + encodeURIComponent(targetType));
		if (search)     params.push('search=' + encodeURIComponent(search));

		var url = 'xml-cns-tank-draining' + (params.length ? '?' + params.join('&') : '');
		TankDrainingGrid.clearAndLoad(url);
	}

	function resetFilters() {
		$('#filterStartDate').val('');
		$('#filterEndDate').val('');
		$('#filterTank').val('');
		$('#filterTargetType').val('');
		$('#filterSearch').val('');
		refreshdata();
	}

	TankDrainingGrid = new dhtmlXGridObject('TankDrainingGrid');
	TankDrainingGrid.setImagePath("./dhtmlx/imgs/");
	TankDrainingGrid.setHeader("No.,Kode Draining,Tanggal,Tangki Asal,Qty Draining (KG),Tujuan,Detail Tujuan,Keterangan,Dokumen BAST,Dibuat Oleh,Tgl Dibuat");
	TankDrainingGrid.setInitWidths("50,150,110,140,140,110,200,220,120,130,140");
	TankDrainingGrid.attachHeader("&nbsp;,#text_filter,#text_filter,#select_filter,#text_filter,#select_filter,#text_filter,#text_filter,&nbsp;,#text_filter,#text_filter");
	TankDrainingGrid.setColAlign("center,left,center,left,right,center,left,left,center,left,center");
	TankDrainingGrid.setColTypes("dyn,ro,ro,ro,ro,ro,ro,ro,link,ro,ro");
	TankDrainingGrid.attachEvent("onRowDblClicked", DetailDraining);
	TankDrainingGrid.setColSorting("int,str,date,str,int,str,str,str,str,str,str");
	TankDrainingGrid.enableSmartRendering(true, 50);
	TankDrainingGrid.enableMultiline(true);
	TankDrainingGrid.setSkin("dhx_skyblue");
	TankDrainingGrid.init();
	refreshdata();
</script>
@endsection
