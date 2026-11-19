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
		<div class="card-header bg-white pb-0 border-bottom">
			<ul class="nav nav-tabs card-header-tabs m-0 border-bottom-0" id="tankDrainingTabs" role="tablist">
				<li class="nav-item" role="presentation">
					<a class="nav-link active font-weight-bold" id="tab-draining-list-link" href="#tab-draining-list" role="tab" aria-selected="true" onclick="switchDrainingTab('list'); return false;">
						Transaksi Tank Draining
					</a>
				</li>
				<li class="nav-item" role="presentation">
					<a class="nav-link font-weight-bold" id="tab-sludge-stock-link" href="#tab-sludge-stock" role="tab" aria-selected="false" onclick="switchDrainingTab('sludge'); return false;">
						Monitoring Stock Sludge
					</a>
				</li>
			</ul>
		</div>
		<div class="card-body">
			<div id="tab-draining-list">
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

			<div id="TankDrainingGrid" style="width:100%; height:420px; background-color:white; overflow:hidden;" class="mt-2 border"></div>
			<div class="mt-2">
				<button class="btn btn-primary" id="NewDrainingBtn" onclick="NewDraining();">New Draining</button>
				<button class="btn btn-info" id="DetailDrainingBtn" onclick="DetailDraining();">Detail / Edit</button>
				<button class="btn btn-success" id="ExportDraining" onclick="TankDrainingGrid.toExcel('./export/generate.php');">Excel</button>
			</div>
			</div><!-- /tab-draining-list -->

			<div id="tab-sludge-stock" style="display:none;">
				@php
					$sludgeRows = $sludgeStockRows ?? [];
					$sludgeSourceRows = $sludgeSources ?? [];
					$sludgeWarehouseRows = $sludgeWarehouses ?? [];
					$totals = $sludgeTotals ?? ['total_awal' => 0, 'total_used' => 0, 'total_moving' => 0, 'total_sisa' => 0, 'total_sisa_gudang' => 0];
				@endphp
				<div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
					<h6 class="mb-0 font-weight-bold text-uppercase" style="color: #2c3e50;">Ringkasan Stock Sludge per Tangki Asal</h6>
					<button type="button" class="btn btn-sm btn-warning font-weight-bold" onclick="toggleSludgeMovingForm()">
						+ Moving Sludge ke Gudang
					</button>
				</div>

				<div class="card border border-warning mb-3" id="sludgeMovingFormCard" style="display:none;">
					<div class="card-header bg-warning text-dark font-weight-bold py-2">
						Form Moving Sludge ke Gudang
					</div>
					<div class="card-body py-3">
						<div class="row">
							<div class="col-md-4 mb-2">
								<label class="small font-weight-bold mb-1">Kode Draining Sumber</label>
								<select id="movingSludgeDrainingId" class="form-control form-control-sm" onchange="onMovingSludgeDrainingChange()">
									<option value="">-- Pilih Kode Draining --</option>
									@foreach($sludgeSourceRows as $src)
										@if(($src['sisa_qty'] ?? 0) > 0)
											<option value="{{ $src['draining_id'] }}" data-sisa="{{ $src['sisa_qty'] }}">
												{{ $src['draining_code'] }} ({{ $src['tank_name'] }} - Sisa: {{ number_format((float) $src['sisa_qty'], 2, '.', ',') }} KG)
											</option>
										@endif
									@endforeach
								</select>
							</div>
							<div class="col-md-3 mb-2">
								<label class="small font-weight-bold mb-1">Gudang Tujuan</label>
								<select id="movingSludgeWarehouseId" class="form-control form-control-sm" style="width:100%;">
									<option value="">-- Pilih Gudang Tujuan --</option>
								</select>
							</div>
							<div class="col-md-2 mb-2">
								<label class="small font-weight-bold mb-1">Tanggal Moving</label>
								<input type="date" id="movingSludgeDate" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" />
							</div>
							<div class="col-md-3 mb-2">
								<label class="small font-weight-bold mb-1">Qty Moving (KG) <span id="movingSludgeMaxInfo" class="text-muted"></span></label>
								<input type="number" step="0.01" min="0.01" id="movingSludgeQty" class="form-control form-control-sm" placeholder="0.00" />
							</div>
							<div class="col-md-9 mb-2">
								<label class="small font-weight-bold mb-1">Keterangan</label>
								<input type="text" id="movingSludgeNotes" class="form-control form-control-sm" placeholder="Catatan moving sludge..." />
							</div>
							<div class="col-md-3 mb-2 d-flex align-items-end justify-content-end">
								<button type="button" class="btn btn-sm btn-secondary mr-2" onclick="toggleSludgeMovingForm(false)">Batal</button>
								<button type="button" class="btn btn-sm btn-success" onclick="submitSludgeMovement()">Simpan Moving</button>
							</div>
						</div>
					</div>
				</div>

				<div class="d-flex align-items-end flex-wrap gap-2 mb-3">
					<div class="m-1">
						<label class="d-block mb-1"><small>Dari Tanggal</small></label>
						<input type="date" id="sludgeFilterStartDate" class="form-control form-control-sm" style="width:140px;" />
					</div>
					<div class="m-1">
						<label class="d-block mb-1"><small>Sampai Tanggal</small></label>
						<input type="date" id="sludgeFilterEndDate" class="form-control form-control-sm" style="width:140px;" />
					</div>
					<div class="m-1">
						<label class="d-block mb-1"><small>Tangki Asal</small></label>
						<select id="sludgeFilterTank" class="form-control form-control-sm" style="width:160px;" onchange="loadSludgeStock()">
							<option value="">Semua Tangki</option>
							@foreach($tanks as $t)
								<option value="{{ $t->id }}">{{ $t->name }}</option>
							@endforeach
						</select>
					</div>
					<div class="m-1">
						<button type="button" class="btn btn-sm btn-primary" onclick="loadSludgeStock()">Filter</button>
						<button type="button" class="btn btn-sm btn-danger ml-1" onclick="resetSludgeFilters()">Reset</button>
					</div>
				</div>

				<div id="sludgeTankCardsSection" class="mb-3">
					<div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
						<span class="font-weight-bold text-secondary" style="font-size: 13px;">
							<i class="fa fa-database text-info mr-1"></i> MONITORING STOCK SLUDGE PER TANGKI &amp; GUDANG
						</span>
					</div>
					<div class="row" id="sludgeTankCardsContainer">
						@foreach($sludgeRows as $row)
							@php
								$awal = (float) ($row['total_sludge_awal'] ?? 0);
								$sisa = (float) ($row['sisa_stock_sludge'] ?? 0);
								$pct = $awal > 0 ? round(($sisa / $awal) * 100, 1) : 0;
								$barClass = $sisa > 0 ? 'bg-success' : 'bg-secondary';
							@endphp
							<div class="col-md-3 col-sm-6 mb-2">
								<div class="card border shadow-sm h-100" style="cursor: pointer;" onclick="filterSludgeByTank('{{ $row['tank_id'] }}')" title="Klik untuk filter tabel ke {{ $row['tank_name'] }}">
									<div class="card-body p-2">
										<div class="d-flex justify-content-between align-items-center mb-1">
											<span class="font-weight-bold text-dark" style="font-size: 13px;">
												<i class="fa fa-cube text-primary mr-1"></i>{{ $row['tank_name'] }}
											</span>
											<span class="badge {{ $sisa > 0 ? 'badge-success' : 'badge-secondary' }}">{{ $pct }}%</span>
										</div>
										<div class="progress mb-2" style="height: 8px;">
											<div class="progress-bar {{ $barClass }}" role="progressbar" style="width: {{ min($pct, 100) }}%;"></div>
										</div>
										<div class="d-flex justify-content-between text-muted" style="font-size: 11px;">
											<span>Sisa: <strong>{{ number_format($sisa, 2, '.', ',') }}</strong> KG</span>
											<span>Awal: {{ number_format($awal, 2, '.', ',') }} KG</span>
										</div>
										<div class="text-right text-muted mt-1" style="font-size: 10px;">
											SO: {{ number_format((float) ($row['total_sludge_used'] ?? 0), 2, '.', ',') }} KG | Moving: {{ number_format((float) ($row['total_sludge_moving'] ?? 0), 2, '.', ',') }} KG
										</div>
									</div>
								</div>
							</div>
						@endforeach
						@foreach($sludgeWarehouseRows as $wh)
							<div class="col-md-3 col-sm-6 mb-2">
								<div class="card border shadow-sm h-100 bg-light">
									<div class="card-body p-2">
										<div class="d-flex justify-content-between align-items-center mb-1">
											<span class="font-weight-bold text-dark" style="font-size: 13px;">
												<i class="fa fa-warehouse text-info mr-1"></i>{{ $wh['warehouse_name'] }}
											</span>
											<span class="badge badge-info">Gudang</span>
										</div>
										<div class="d-flex justify-content-between text-muted mt-2" style="font-size: 11px;">
											<span>Sisa: <strong>{{ number_format((float) $wh['sisa_stock_gudang'], 2, '.', ',') }}</strong> KG</span>
											<span>Masuk: {{ number_format((float) $wh['total_moving_in'], 2, '.', ',') }} KG</span>
										</div>
										<div class="text-right text-muted mt-1" style="font-size: 10px;">
											Terpakai SO: {{ number_format((float) $wh['total_so_used'], 2, '.', ',') }} KG
										</div>
									</div>
								</div>
							</div>
						@endforeach
					</div>
				</div>

				<div id="SludgeGrid" style="width:100%; height:420px; background-color:white; overflow:hidden;" class="mt-2 border"></div>
			</div><!-- /tab-sludge-stock -->
		</div>
	</div>
</div>

<div id="TankDrainingDet"></div>

<script>
	function NewDraining() {
		$('#TankDrainingDet').show();
		LoadM('TankDrainingDet', 'cns-tank-draining-new');
	}

	function DetailDraining() {
		var id = TankDrainingGrid.getSelectedId();
		if (!id) {
			alert('Pilih baris data tank draining terlebih dahulu.');
			return;
		}
		$('#TankDrainingDet').show();
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
	TankDrainingGrid.setHeader("No.,Kode Draining,Tanggal,Tangki Asal,Qty Draining (KG),Qty Sludge (KG),Tujuan,Detail Tujuan,Keterangan,Dokumen BAST,Dibuat Oleh,Tgl Dibuat");
	TankDrainingGrid.setInitWidths("45,140,95,115,120,115,95,165,*,105,110,125");
	TankDrainingGrid.attachHeader("&nbsp;,#text_filter,#text_filter,#select_filter,#text_filter,#text_filter,#select_filter,#text_filter,#text_filter,&nbsp;,#text_filter,#text_filter");
	TankDrainingGrid.setColAlign("center,left,center,left,right,right,center,left,left,center,left,center");
	TankDrainingGrid.setColTypes("dyn,ro,ro,ro,ro,ro,ro,ro,ro,link,ro,ro");
	TankDrainingGrid.attachEvent("onRowDblClicked", DetailDraining);
	TankDrainingGrid.setColSorting("int,str,date,str,int,int,str,str,str,str,str,str");
	TankDrainingGrid.enableSmartRendering(true, 50);
	TankDrainingGrid.enableMultiline(true);
	TankDrainingGrid.setSkin("dhx_skyblue");
	TankDrainingGrid.init();
	refreshdata();

	var SludgeGrid = null;
	function initSludgeGrid() {
		if (SludgeGrid) return;
		SludgeGrid = new dhtmlXGridObject('SludgeGrid');
		SludgeGrid.setImagePath("./dhtmlx/imgs/");
		SludgeGrid.setHeader("No.,Kode Draining,Tanggal,Tangki Asal,Qty Sludge (KG),Terpakai SO (KG),Moving Keluar (KG),Sisa (KG)");
		SludgeGrid.setInitWidths("50,180,120,*,150,150,150,150");
		SludgeGrid.attachHeader("&nbsp;,#text_filter,#text_filter,#select_filter,#text_filter,#text_filter,#text_filter,#text_filter");
		SludgeGrid.setColAlign("center,left,center,left,right,right,right,right");
		SludgeGrid.setColTypes("dyn,ro,ro,ro,ro,ro,ro,ro");
		SludgeGrid.setColSorting("int,str,date,str,int,int,int,int");
		SludgeGrid.enableSmartRendering(true, 50);
		SludgeGrid.enableMultiline(true);
		SludgeGrid.setSkin("dhx_skyblue");
		SludgeGrid.init();
	}

	function filterSludgeByTank(tankId) {
		var current = String($('#sludgeFilterTank').val() || '');
		var next = current === String(tankId) ? '' : String(tankId);
		$('#sludgeFilterTank').val(next);
		loadSludgeStock();
	}

	function switchDrainingTab(tab) {
		if (tab === 'sludge') {
			$('#tab-draining-list-link').removeClass('active').attr('aria-selected', 'false');
			$('#tab-sludge-stock-link').addClass('active').attr('aria-selected', 'true');
			$('#tab-draining-list').hide();
			$('#TankDrainingDet').hide();
			$('#tab-sludge-stock').show();
			initSludgeGrid();
			if (SludgeGrid) SludgeGrid.setSizes();
			loadSludgeStock();
		} else {
			$('#tab-sludge-stock-link').removeClass('active').attr('aria-selected', 'false');
			$('#tab-draining-list-link').addClass('active').attr('aria-selected', 'true');
			$('#tab-sludge-stock').hide();
			$('#tab-draining-list').show();
			$('#TankDrainingDet').show();
			if (TankDrainingGrid) TankDrainingGrid.setSizes();
		}
	}

	function loadSludgeStock() {
		var startDate = $('#sludgeFilterStartDate').val() || '';
		var endDate = $('#sludgeFilterEndDate').val() || '';
		var tankId = $('#sludgeFilterTank').val() || '';

		if (SludgeGrid) {
			var params = [];
			if (startDate) params.push('start_date=' + encodeURIComponent(startDate));
			if (endDate) params.push('end_date=' + encodeURIComponent(endDate));
			if (tankId) params.push('tank_id=' + encodeURIComponent(tankId));
			var xmlUrl = '{{ route("cns-tank-draining.sludge-xml", [], false) }}' + (params.length ? '?' + params.join('&') : '');
			SludgeGrid.clearAndLoad(xmlUrl);
		}

		$.ajax({
			url: '{{ route("cns-tank-draining.sludge-stock", [], false) }}',
			type: 'GET',
			data: {
				start_date: startDate,
				end_date: endDate
			},
			dataType: 'json',
			success: function(res) {
				if (!res || res.status !== 'success') return;
				var rows = res.data || [];
				var sources = res.sources || [];
				var warehouses = res.warehouses || [];
				var esc = function(v) { return $('<div>').text(v || '').html(); };
				var activeTankId = String($('#sludgeFilterTank').val() || '');

				var cardsHtml = '';
				rows.forEach(function(r) {
					var awal = Number(r.total_sludge_awal || 0);
					var sisa = Number(r.sisa_stock_sludge || 0);
					var pct = awal > 0 ? Math.round((sisa / awal) * 1000) / 10 : 0;
					var barCls = sisa > 0 ? 'bg-success' : 'bg-secondary';
					var badgeCls = sisa > 0 ? 'badge-success' : 'badge-secondary';
					var activeBorder = activeTankId !== '' && activeTankId === String(r.tank_id) ? 'border-primary' : '';
					cardsHtml += '<div class="col-md-3 col-sm-6 mb-2">' +
						'<div class="card border shadow-sm h-100 ' + activeBorder + '" style="cursor: pointer;" onclick="filterSludgeByTank(\'' + r.tank_id + '\')" title="Klik untuk filter tabel ke ' + esc(r.tank_name) + '">' +
							'<div class="card-body p-2">' +
								'<div class="d-flex justify-content-between align-items-center mb-1">' +
									'<span class="font-weight-bold text-dark" style="font-size: 13px;"><i class="fa fa-cube text-primary mr-1"></i>' + esc(r.tank_name) + '</span>' +
									'<span class="badge ' + badgeCls + '">' + pct + '%</span>' +
								'</div>' +
								'<div class="progress mb-2" style="height: 8px;">' +
									'<div class="progress-bar ' + barCls + '" role="progressbar" style="width: ' + Math.min(pct, 100) + '%;"></div>' +
								'</div>' +
								'<div class="d-flex justify-content-between text-muted" style="font-size: 11px;">' +
									'<span>Sisa: <strong>' + cnsFormatNumber(sisa, 2) + '</strong> KG</span>' +
									'<span>Awal: ' + cnsFormatNumber(awal, 2) + ' KG</span>' +
								'</div>' +
								'<div class="text-right text-muted mt-1" style="font-size: 10px;">' +
									'SO: ' + cnsFormatNumber(r.total_sludge_used || 0, 2) + ' KG | Moving: ' + cnsFormatNumber(r.total_sludge_moving || 0, 2) + ' KG' +
								'</div>' +
							'</div>' +
						'</div>' +
					'</div>';
				});

				warehouses.forEach(function(w) {
					cardsHtml += '<div class="col-md-3 col-sm-6 mb-2">' +
						'<div class="card border shadow-sm h-100 bg-light">' +
							'<div class="card-body p-2">' +
								'<div class="d-flex justify-content-between align-items-center mb-1">' +
									'<span class="font-weight-bold text-dark" style="font-size: 13px;"><i class="fa fa-warehouse text-info mr-1"></i>' + esc(w.warehouse_name) + '</span>' +
									'<span class="badge badge-info">Gudang</span>' +
								'</div>' +
								'<div class="d-flex justify-content-between text-muted mt-2" style="font-size: 11px;">' +
									'<span>Sisa: <strong>' + cnsFormatNumber(w.sisa_stock_gudang || 0, 2) + '</strong> KG</span>' +
									'<span>Masuk: ' + cnsFormatNumber(w.total_moving_in || 0, 2) + ' KG</span>' +
								'</div>' +
								'<div class="text-right text-muted mt-1" style="font-size: 10px;">' +
									'Terpakai SO: ' + cnsFormatNumber(w.total_so_used || 0, 2) + ' KG' +
								'</div>' +
							'</div>' +
						'</div>' +
					'</div>';
				});

				$('#sludgeTankCardsContainer').html(cardsHtml);

				var movOpts = '<option value="">-- Pilih Kode Draining --</option>';
				sources.forEach(function(s) {
					if (Number(s.sisa_qty || 0) > 0) {
						movOpts += '<option value="' + s.draining_id + '" data-sisa="' + s.sisa_qty + '">' +
							esc(s.draining_code) + ' (' + esc(s.tank_name) + ' - Sisa: ' + cnsFormatNumber(s.sisa_qty || 0, 2) + ' KG)' +
							'</option>';
					}
				});
				$('#movingSludgeDrainingId').html(movOpts);
			}
		});
	}

	function resetSludgeFilters() {
		$('#sludgeFilterStartDate').val('');
		$('#sludgeFilterEndDate').val('');
		$('#sludgeFilterTank').val('');
		loadSludgeStock();
	}

	function resetSludgeMovingForm() {
		$('#movingSludgeDrainingId').val('');
		$('#movingSludgeWarehouseId').val('').trigger('change');
		$('#movingSludgeDate').val('{{ date("Y-m-d") }}');
		$('#movingSludgeQty').removeAttr('max').val('');
		$('#movingSludgeNotes').val('');
		$('#movingSludgeMaxInfo').text('');
	}

	var sludgeWarehousesLoaded = false;
	function toggleSludgeMovingForm(forceShow) {
		var $card = $('#sludgeMovingFormCard');
		var show = typeof forceShow === 'boolean' ? forceShow : !$card.is(':visible');
		if (show) {
			$card.show();
			if (!sludgeWarehousesLoaded) {
				initSludgeMovingWarehouses();
			}
		} else {
			resetSludgeMovingForm();
			$card.hide();
		}
	}

	function initSludgeMovingWarehouses() {
		var $sel = $('#movingSludgeWarehouseId');
		if (!$sel.length) return;
		if (!window._cnsWarehousesReq) {
			window._cnsWarehousesReq = $.get('{{ url("cns-master-warehouses") }}').fail(function() {
				window._cnsWarehousesReq = null;
			});
		}
		window._cnsWarehousesReq.done(function(res) {
			var currentVal = $sel.val();
			$sel.empty().append($('<option>', { value: '', text: '-- Pilih Gudang Tujuan --' }));
			$.each((res && res.data) || [], function(_, w) {
				if (w && w.id && w.name) {
					$sel.append($('<option>', { value: w.id, text: w.name }));
				}
			});
			sludgeWarehousesLoaded = true;
			if (typeof $.fn.select2 === 'function') {
				if ($sel.hasClass('select2-hidden-accessible')) {
					$sel.select2('destroy');
				}
				$sel.select2({
					placeholder: '-- Pilih Gudang Tujuan --',
					allowClear: true,
					width: '100%'
				});
			}
			if (currentVal) {
				$sel.val(currentVal).trigger('change');
			}
		});
	}

	function onMovingSludgeDrainingChange() {
		var $opt = $('#movingSludgeDrainingId').find('option:selected');
		var sisa = Number($opt.attr('data-sisa') || 0);
		if ($opt.val()) {
			$('#movingSludgeMaxInfo').text('(Maks: ' + cnsFormatNumber(sisa, 2) + ' KG)');
			$('#movingSludgeQty').attr('max', sisa).val(sisa);
		} else {
			$('#movingSludgeMaxInfo').text('');
			$('#movingSludgeQty').removeAttr('max').val('');
		}
	}

	function submitSludgeMovement() {
		var drainingId = $('#movingSludgeDrainingId').val();
		var $whOpt = $('#movingSludgeWarehouseId').find('option:selected');
		var warehouseId = $whOpt.val();
		var warehouseName = $whOpt.text();
		var moveDate = $('#movingSludgeDate').val();
		var qty = Number($('#movingSludgeQty').val() || 0);
		var notes = $('#movingSludgeNotes').val() || '';

		if (!drainingId) { alert('Kode draining sumber wajib dipilih.'); return; }
		if (!warehouseId) { alert('Gudang tujuan wajib dipilih.'); return; }
		if (!moveDate) { alert('Tanggal moving wajib diisi.'); return; }
		if (qty <= 0) { alert('Qty moving harus lebih dari 0.'); return; }

		$.ajax({
			headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
			url: '{{ route("cns-tank-draining.sludge-moving", [], false) }}?_token={{ csrf_token() }}',
			type: 'POST',
			dataType: 'json',
			data: {
				draining_id: drainingId,
				target_warehouse_id: warehouseId,
				target_warehouse_name: warehouseName,
				movement_date: moveDate,
				qty: qty,
				notes: notes
			},
			success: function(res) {
				if (res && res.status === 'success') {
					alert(res.message || 'Moving sludge berhasil disimpan.');
					toggleSludgeMovingForm(false);
					loadSludgeStock();
				} else {
					alert((res && res.message) || 'Gagal menyimpan moving sludge.');
				}
			},
			error: function(xhr) {
				var msg = (xhr.responseJSON && xhr.responseJSON.message) || xhr.responseText || 'Terjadi kesalahan saat menyimpan moving sludge.';
				alert(msg);
			}
		});
	}
</script>
@endsection
