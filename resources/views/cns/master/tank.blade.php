@extends('layout.mainlayout-cns')

@section('content-wrapper')
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>CNS</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="#">Master Data</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Master Tangki</span>
		</li>
	</ol>
</div>
<!-- End Main Content Header -->

<div class="row m-1">
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">MASTER TANGKI UCO</div>

	<div class="card w-100">
		<div class="card-body">
			<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
				<div class="d-flex align-items-end flex-wrap gap-2">
					<!-- <div class="m-1">
						<label class="d-block mb-1"><small>Dari Tanggal</small></label>
						<input type="date" id="filterStartDate" class="form-control form-control-sm" style="width:140px;" />
					</div>
					<div class="m-1">
						<label class="d-block mb-1"><small>Sampai Tanggal</small></label>
						<input type="date" id="filterEndDate" class="form-control form-control-sm" style="width:140px;" />
					</div>
					<div class="m-1">
						<label class="d-block mb-1"><small>Status</small></label>
						<select id="filterStatus" class="form-control form-control-sm" style="width:140px;" onchange="refreshdata()">
							<option value="">Semua Status</option>
							<option value="ACTIVE" selected>ACTIVE</option>
							<option value="INACTIVE">INACTIVE</option>
						</select>
					</div> -->
					<div class="m-1">
						<label class="d-block mb-1"><small>Nama Tangki</small></label>
						<input type="text" id="filterTankName" class="form-control form-control-sm" style="width:170px;" placeholder="Semua Tangki" onkeyup="if(event.key==='Enter') refreshdata()" />
					</div>
					<div class="m-1">
						<button class="btn btn-sm btn-primary" onclick="refreshdata()">Filter</button>
						<button class="btn btn-sm btn-danger ml-1" onclick="resetFilters()">Reset</button>
					</div>
				</div>
			</div>

			<div class="m-2">
				<div id="MasterTank" style="width:auto; height:400px;"></div>
			</div>
			<div class="mt-2">
				<button class="btn btn-primary" id="NewTankBtn" onclick="NewTank();">New Tangki</button>
				<button class="btn btn-info" id="DetailTankBtn" onclick="DetailTank();">Detail Tangki</button>
				<button class="btn btn-success" id="ExportTank" onclick="MasterTank.toExcel('./export/generate.php');">Excel</button>
			</div>
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
			alert('Pilih data tangki terlebih dahulu.');
			return;
		}
		LoadM('MasterTankDet', 'cns-master-tank-detail?ID=' + MasterTankID);
	}

	function refreshdata() {
		var startDate = $('#filterStartDate').val();
		var endDate   = $('#filterEndDate').val();
		var status    = $('#filterStatus').val();
		var name      = $('#filterTankName').val();

		var params = [];
		if (startDate) params.push('start_date=' + encodeURIComponent(startDate));
		if (endDate)   params.push('end_date=' + encodeURIComponent(endDate));
		if (status)    params.push('status=' + encodeURIComponent(status));
		if (name)      params.push('name=' + encodeURIComponent(name));

		var url = 'xml-cns-master-tank' + (params.length ? '?' + params.join('&') : '');
		MasterTank.clearAndLoad(url);
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
	MasterTank.setColTypes("dyn,ro,ro,ro,ro,ro,ro,ro,ro");
	MasterTank.attachEvent("onRowDblClicked", DetailTank);
	MasterTank.setColSorting("int,str,int,int,int,str,str,str,str");
	MasterTank.enableSmartRendering(true, 50);
	MasterTank.enableMultiline(true);
	MasterTank.setSkin("dhx_skyblue");
	MasterTank.init();
	refreshdata();
</script>

@endsection
