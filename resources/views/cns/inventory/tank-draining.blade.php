@extends('layout.mainlayout-cns')

@section('content-wrapper')
@include('cns.partials.page-header', [
	'title' => 'Tank Draining',
	'breadcrumbs' => [
		['label' => 'Inventory'],
		['label' => 'Tank Draining'],
	],
])

<style>
	.tank-metric-card {
		border-radius: 10px;
		padding: 18px 20px;
		color: #ffffff;
		position: relative;
		min-height: 125px;
		display: flex;
		flex-direction: column;
		justify-content: space-between;
		cursor: pointer;
		box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
		transition: transform 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
	}
	.tank-metric-card:hover {
		transform: translateY(-2px);
		box-shadow: 0 5px 12px rgba(0, 0, 0, 0.08);
	}
	.tank-metric-card.is-static {
		cursor: default;
	}
	.tank-metric-card.is-static:hover {
		transform: none;
		box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
	}
	.tank-metric-card.is-active {
		box-shadow: 0 0 0 3px #1e293b, 0 6px 14px rgba(0, 0, 0, 0.14);
	}
	.tank-metric-card .metric-header {
		font-size: 11px;
		font-weight: 600;
		letter-spacing: 0.5px;
		text-transform: uppercase;
		color: rgba(255, 255, 255, 0.85);
		display: flex;
		align-items: center;
		gap: 6px;
		padding-right: 64px;
	}
	.tank-metric-card .tank-pct-badge {
		background: rgba(255, 255, 255, 0.22);
		color: #ffffff;
		font-size: 10px;
		font-weight: 700;
		padding: 2px 6px;
		border-radius: 4px;
	}
	.tank-metric-card .metric-value {
		font-size: 22px;
		font-weight: 700;
		line-height: 1.2;
		color: #ffffff;
		margin-top: 6px;
		padding-right: 64px;
	}
	.tank-metric-card .metric-icon-box {
		width: 52px;
		height: 52px;
		border-radius: 6px;
		background: rgba(255, 255, 255, 0.18);
		display: flex;
		align-items: center;
		justify-content: center;
		position: absolute;
		right: 18px;
		top: 18px;
	}
	.tank-metric-card .metric-icon-box i,
	.tank-metric-card .metric-icon-box svg {
		font-size: 22px;
		color: #ffffff;
	}
	.tank-metric-card .tank-progress {
		height: 6px;
		background: rgba(255, 255, 255, 0.25);
		border-radius: 999px;
		overflow: hidden;
		margin: 10px 0 8px;
	}
	.tank-metric-card .tank-progress-bar {
		height: 100%;
		background: #ffffff;
		border-radius: 999px;
	}
	.tank-metric-card .tank-meta {
		display: flex;
		justify-content: space-between;
		font-size: 11px;
		color: rgba(255, 255, 255, 0.9);
		gap: 8px;
	}
	.bg-card-teal { background-color: #11a38f !important; }
	.bg-card-slate { background-color: #6c757d !important; }
	.bg-card-info { background-color: #2563eb !important; }
</style>

<div class="card cns-card">
	<div class="card-header">
		<h5 class="card-title">Transaksi Tank Draining</h5>
		<div id="drainingHeaderActions" class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
			<button type="button" class="btn btn-sm btn-primary" id="NewDrainingBtn" onclick="NewDraining();">New Draining</button>
			<button type="button" class="btn btn-sm btn-info" id="DetailDrainingBtn" onclick="DetailDraining();">Detail / Edit</button>
			<button type="button" class="btn btn-sm btn-success" id="ExportDraining" onclick="TankDrainingGrid.toExcel('./export/generate.php');">Excel</button>
		</div>
		<div id="sludgeHeaderActions" class="d-none align-items-center flex-wrap" style="gap: 0.5rem;">
			<button type="button" class="btn btn-sm btn-warning font-weight-bold" onclick="toggleSludgeMovingForm()">
				+ Moving Sludge ke Gudang
			</button>
		</div>
	</div>
	<div class="card-body">
		<ul class="nav nav-tabs mb-3" id="tankDrainingTabs" role="tablist">
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

		<div id="tab-draining-list">
			<div class="cns-filter-bar">
				<div>
					<label class="d-block mb-1"><small>Dari Tanggal</small></label>
					<input type="date" id="filterStartDate" class="form-control form-control-sm" style="width:140px;" />
				</div>
				<div>
					<label class="d-block mb-1"><small>Sampai Tanggal</small></label>
					<input type="date" id="filterEndDate" class="form-control form-control-sm" style="width:140px;" />
				</div>
				<div>
					<label class="d-block mb-1"><small>Tangki Asal</small></label>
					<select id="filterTank" class="form-control form-control-sm" style="width:160px;" onchange="refreshdata()">
						<option value="">Semua Tangki</option>
						@foreach($tanks as $t)
							<option value="{{ $t->id }}">{{ $t->name }}</option>
						@endforeach
					</select>
				</div>
				<div>
					<label class="d-block mb-1"><small>Tujuan Draining</small></label>
					<select id="filterTargetType" class="form-control form-control-sm" style="width:140px;" onchange="refreshdata()">
						<option value="">Semua Tujuan</option>
						<option value="MOVING">MOVING</option>
						<option value="PENJUALAN">PENJUALAN</option>
					</select>
				</div>
				<div>
					<label class="d-block mb-1"><small>Pencarian</small></label>
					<input type="text" id="filterSearch" class="form-control form-control-sm" style="width:170px;" placeholder="Kode / SO / Ket..." onkeyup="if(event.key==='Enter') refreshdata()" />
				</div>
				<div>
					<button type="button" class="btn btn-sm btn-primary" onclick="refreshdata()">Filter</button>
					<button type="button" class="btn btn-sm btn-danger ml-1" onclick="resetFilters()">Reset</button>
				</div>
			</div>

			<div class="cns-grid-wrapper">
				<div id="TankDrainingGrid" class="cns-grid" style="width:100%; height:420px; background-color:white; overflow:hidden;"></div>
			</div>
		</div><!-- /tab-draining-list -->

		<div id="tab-sludge-stock" style="display:none;">
			@php
				$sludgeRows = $sludgeStockRows ?? [];
				$sludgeSourceRows = $sludgeSources ?? [];
				$sludgeWarehouseRows = $sludgeWarehouses ?? [];
				$totals = $sludgeTotals ?? ['total_awal' => 0, 'total_used' => 0, 'total_moving' => 0, 'total_sisa' => 0, 'total_sisa_gudang' => 0];
			@endphp

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
						<div class="col-md-3 mb-2 d-flex align-items-end justify-content-end gap-2">
							<button type="button" class="btn btn-sm btn-secondary mr-2" onclick="toggleSludgeMovingForm(false)">Batal</button>
							<button type="button" class="btn btn-sm btn-success" onclick="submitSludgeMovement()">Simpan Moving</button>
						</div>
					</div>
				</div>
			</div>

			<div class="cns-filter-bar">
				<div>
					<label class="d-block mb-1"><small>Dari Tanggal</small></label>
					<input type="date" id="sludgeFilterStartDate" class="form-control form-control-sm" style="width:140px;" />
				</div>
				<div>
					<label class="d-block mb-1"><small>Sampai Tanggal</small></label>
					<input type="date" id="sludgeFilterEndDate" class="form-control form-control-sm" style="width:140px;" />
				</div>
				<div>
					<label class="d-block mb-1"><small>Tangki Asal</small></label>
					<select id="sludgeFilterTank" class="form-control form-control-sm" style="width:160px;" onchange="loadSludgeStock()">
						<option value="">Semua Tangki</option>
						@foreach($tanks as $t)
							<option value="{{ $t->id }}">{{ $t->name }}</option>
						@endforeach
					</select>
				</div>
				<div>
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
				<div class="row g-3" id="sludgeTankCardsContainer">
					@foreach($sludgeRows as $row)
						@php
							$awal = (float) ($row['total_sludge_awal'] ?? 0);
							$sisa = (float) ($row['sisa_stock_sludge'] ?? 0);
							$pct = $awal > 0 ? round(($sisa / $awal) * 100, 1) : 0;
							$cardColorClass = $sisa > 0 ? 'bg-card-teal' : 'bg-card-slate';
						@endphp
						<div class="col-xl-3 col-lg-6 col-md-6 col-12 mb-2">
							<div class="tank-metric-card {{ $cardColorClass }} h-100" onclick="filterSludgeByTank('{{ $row['tank_id'] }}')" title="Klik untuk filter tabel ke {{ $row['tank_name'] }}">
								<div>
									<div class="metric-header">
										<span>{{ $row['tank_name'] }}</span>
										<span class="tank-pct-badge">{{ $pct }}%</span>
									</div>
									<div class="metric-value">{{ number_format($sisa, 2, '.', ',') }} <span style="font-size:13px;font-weight:600;">KG</span></div>
								</div>
								<div class="metric-icon-box">
									<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
										<path d="M5 7c0-2.2 3.1-4 7-4s7 1.8 7 4v10c0 2.2-3.1 4-7 4s-7-1.8-7-4V7z"/>
										<ellipse cx="12" cy="7" rx="7" ry="2.2"/>
										<path d="M5 12c0 1.2 3.1 2.2 7 2.2s7-1 7-2.2"/>
									</svg>
								</div>
								<div>
									<div class="tank-progress">
										<div class="tank-progress-bar" style="width: {{ min($pct, 100) }}%;"></div>
									</div>
									<div class="tank-meta">
										<span>Awal: {{ number_format($awal, 2, '.', ',') }} KG</span>
										<span>SO: {{ number_format((float) ($row['total_sludge_used'] ?? 0), 2, '.', ',') }} | Mov: {{ number_format((float) ($row['total_sludge_moving'] ?? 0), 2, '.', ',') }}</span>
									</div>
								</div>
							</div>
						</div>
					@endforeach
					@foreach($sludgeWarehouseRows as $wh)
						@php
							$whMasuk = (float) ($wh['total_moving_in'] ?? 0);
							$whSisa = (float) ($wh['sisa_stock_gudang'] ?? 0);
							$whPct = $whMasuk > 0 ? round(($whSisa / $whMasuk) * 100, 1) : 0;
						@endphp
						<div class="col-xl-3 col-lg-6 col-md-6 col-12 mb-2">
							<div class="tank-metric-card bg-card-info is-static h-100">
								<div>
									<div class="metric-header">
										<span>{{ $wh['warehouse_name'] }}</span>
										<span class="tank-pct-badge">Gudang</span>
									</div>
									<div class="metric-value">{{ number_format($whSisa, 2, '.', ',') }} <span style="font-size:13px;font-weight:600;">KG</span></div>
								</div>
								<div class="metric-icon-box">
									<i class="fa fa-warehouse"></i>
								</div>
								<div>
									<div class="tank-progress">
										<div class="tank-progress-bar" style="width: {{ min($whPct, 100) }}%;"></div>
									</div>
									<div class="tank-meta">
										<span>Masuk: {{ number_format($whMasuk, 2, '.', ',') }} KG</span>
										<span>SO: {{ number_format((float) ($wh['total_so_used'] ?? 0), 2, '.', ',') }} KG</span>
									</div>
								</div>
							</div>
						</div>
					@endforeach
				</div>
			</div>

			<div class="cns-grid-wrapper">
				<div id="SludgeGrid" class="cns-grid" style="width:100%; height:420px; background-color:white; overflow:hidden;"></div>
			</div>
		</div><!-- /tab-sludge-stock -->
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
			swal('Perhatian', 'Pilih baris data tank draining terlebih dahulu.', 'warning');
			return;
		}
		$('#TankDrainingDet').show();
		LoadM('TankDrainingDet', 'cns-tank-draining-detail?ID=' + id);
	}

	var TankDrainingPagination = null;
	function refreshdata() {
		if (TankDrainingPagination) {
			TankDrainingPagination.reload(1);
		}
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
	TankDrainingGrid.setHeader("No.,Kode Draining,Tanggal,Tangki Asal,Qty Draining (KG),Qty Sludge (KG),Tujuan,Detail Tujuan,Keterangan,Dokumen BA,Dibuat Oleh,Tgl Dibuat");
	TankDrainingGrid.setInitWidths("45,140,95,115,120,115,95,165,*,105,110,125");
	TankDrainingGrid.attachHeader("&nbsp;,#text_filter,#text_filter,#select_filter,#text_filter,#text_filter,#select_filter,#text_filter,#text_filter,&nbsp;,#text_filter,#text_filter");
	TankDrainingGrid.setColAlign("center,left,center,left,right,right,center,left,left,center,left,center");
	TankDrainingGrid.setColTypes("dyn,ro,ro,ro,ro,ro,ro,ro,ro,link,ro,ro");
	TankDrainingGrid.attachEvent("onRowDblClicked", DetailDraining);
	TankDrainingGrid.setColSorting("int,str,date,str,int,int,str,str,str,str,str,str");
	TankDrainingGrid.enableMultiline(true);
	TankDrainingGrid.setSkin("dhx_skyblue");
	TankDrainingGrid.init();

	TankDrainingPagination = initCnsGridPagination(TankDrainingGrid, {
		pageSize: 25,
		pageSizeOptions: [10, 25, 50, 100],
		serverFilter: true,
		getUrl: function (params) {
			var q = ['posStart=' + params.posStart, 'count=' + params.count];
			var startDate  = $('#filterStartDate').val();
			var endDate    = $('#filterEndDate').val();
			var tankId     = $('#filterTank').val();
			var targetType = $('#filterTargetType').val();
			var search     = $('#filterSearch').val();

			if (startDate)  q.push('start_date=' + encodeURIComponent(startDate));
			if (endDate)    q.push('end_date=' + encodeURIComponent(endDate));
			if (tankId)     q.push('tank_id=' + encodeURIComponent(tankId));
			if (targetType) q.push('target_type=' + encodeURIComponent(targetType));
			if (search)     q.push('search=' + encodeURIComponent(search));

			if (params.sortCol !== null && params.sortCol !== undefined) {
				q.push('sort_col=' + params.sortCol);
				q.push('sort_dir=' + (params.sortDir || 'asc'));
			}
			if (params.filters) {
				for (var colIdx in params.filters) {
					q.push('filter[' + colIdx + ']=' + encodeURIComponent(params.filters[colIdx]));
				}
			}
			return 'xml-cns-tank-draining?' + q.join('&');
		}
	});
	refreshdata();

	var SludgeGrid = null;
	var SludgePagination = null;
	function initSludgeGrid() {
		if (SludgeGrid) return;
		SludgeGrid = new dhtmlXGridObject('SludgeGrid');
		SludgeGrid.setImagePath("./dhtmlx/imgs/");
		SludgeGrid.setHeader("No.,Kode Draining,Tanggal,Tangki Asal,Tujuan Draining,Qty Sludge (KG),Terpakai SO (KG),Moving Keluar (KG),Sisa (KG)");
		SludgeGrid.setInitWidths("50,170,110,*,190,140,140,140,140");
		SludgeGrid.attachHeader("&nbsp;,#text_filter,#text_filter,#select_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
		SludgeGrid.setColAlign("center,left,center,left,left,right,right,right,right");
		SludgeGrid.setColTypes("dyn,ro,ro,ro,ro,ro,ro,ro,ro");
		SludgeGrid.setColSorting("int,str,date,str,str,int,int,int,int");
		SludgeGrid.enableMultiline(true);
		SludgeGrid.setSkin("dhx_skyblue");
		SludgeGrid.init();

		SludgePagination = initCnsGridPagination(SludgeGrid, {
			pageSize: 25,
			pageSizeOptions: [10, 25, 50, 100],
			serverFilter: true,
			getUrl: function (params) {
				var q = ['posStart=' + params.posStart, 'count=' + params.count];
				var startDate = $('#sludgeFilterStartDate').val() || '';
				var endDate = $('#sludgeFilterEndDate').val() || '';
				var tankId = $('#sludgeFilterTank').val() || '';

				if (startDate) q.push('start_date=' + encodeURIComponent(startDate));
				if (endDate) q.push('end_date=' + encodeURIComponent(endDate));
				if (tankId) q.push('tank_id=' + encodeURIComponent(tankId));

				if (params.sortCol !== null && params.sortCol !== undefined) {
					q.push('sort_col=' + params.sortCol);
					q.push('sort_dir=' + (params.sortDir || 'asc'));
				}
				if (params.filters) {
					for (var colIdx in params.filters) {
						q.push('filter[' + colIdx + ']=' + encodeURIComponent(params.filters[colIdx]));
					}
				}
				return '{{ route("cns-tank-draining.sludge-xml", [], false) }}?' + q.join('&');
			}
		});
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
			$('#drainingHeaderActions').removeClass('d-flex').addClass('d-none');
			$('#sludgeHeaderActions').removeClass('d-none').addClass('d-flex');
			initSludgeGrid();
			if (SludgeGrid) SludgeGrid.setSizes();
			loadSludgeStock();
		} else {
			$('#tab-sludge-stock-link').removeClass('active').attr('aria-selected', 'false');
			$('#tab-draining-list-link').addClass('active').attr('aria-selected', 'true');
			$('#tab-sludge-stock').hide();
			$('#tab-draining-list').show();
			$('#TankDrainingDet').show();
			$('#sludgeHeaderActions').removeClass('d-flex').addClass('d-none');
			$('#drainingHeaderActions').removeClass('d-none').addClass('d-flex');
			if (TankDrainingGrid) TankDrainingGrid.setSizes();
		}
	}

	function loadSludgeStock() {
		var startDate = $('#sludgeFilterStartDate').val() || '';
		var endDate = $('#sludgeFilterEndDate').val() || '';
		var tankId = $('#sludgeFilterTank').val() || '';

		if (SludgePagination) {
			SludgePagination.reload(1);
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

				var tankIconSvg = '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' +
					'<path d="M5 7c0-2.2 3.1-4 7-4s7 1.8 7 4v10c0 2.2-3.1 4-7 4s-7-1.8-7-4V7z"/>' +
					'<ellipse cx="12" cy="7" rx="7" ry="2.2"/>' +
					'<path d="M5 12c0 1.2 3.1 2.2 7 2.2s7-1 7-2.2"/>' +
					'</svg>';

				var cardsHtml = '';
				rows.forEach(function(r) {
					var awal = Number(r.total_sludge_awal || 0);
					var sisa = Number(r.sisa_stock_sludge || 0);
					var pct = awal > 0 ? Math.round((sisa / awal) * 1000) / 10 : 0;
					var cardColorClass = sisa > 0 ? 'bg-card-teal' : 'bg-card-slate';
					var activeClass = activeTankId !== '' && activeTankId === String(r.tank_id) ? 'is-active' : '';
					cardsHtml += '<div class="col-xl-3 col-lg-6 col-md-6 col-12 mb-2">' +
						'<div class="tank-metric-card ' + cardColorClass + ' ' + activeClass + ' h-100" onclick="filterSludgeByTank(\'' + r.tank_id + '\')" title="Klik untuk filter tabel ke ' + esc(r.tank_name) + '">' +
							'<div>' +
								'<div class="metric-header">' +
									'<span>' + esc(r.tank_name) + '</span>' +
									'<span class="tank-pct-badge">' + pct + '%</span>' +
								'</div>' +
								'<div class="metric-value">' + cnsFormatNumber(sisa, 2) + ' <span style="font-size:13px;font-weight:600;">KG</span></div>' +
							'</div>' +
							'<div class="metric-icon-box">' + tankIconSvg + '</div>' +
							'<div>' +
								'<div class="tank-progress">' +
									'<div class="tank-progress-bar" style="width: ' + Math.min(pct, 100) + '%;"></div>' +
								'</div>' +
								'<div class="tank-meta">' +
									'<span>Awal: ' + cnsFormatNumber(awal, 2) + ' KG</span>' +
									'<span>SO: ' + cnsFormatNumber(r.total_sludge_used || 0, 2) + ' | Mov: ' + cnsFormatNumber(r.total_sludge_moving || 0, 2) + '</span>' +
								'</div>' +
							'</div>' +
						'</div>' +
					'</div>';
				});

				warehouses.forEach(function(w) {
					var whMasuk = Number(w.total_moving_in || 0);
					var whSisa = Number(w.sisa_stock_gudang || 0);
					var whPct = whMasuk > 0 ? Math.round((whSisa / whMasuk) * 1000) / 10 : 0;
					cardsHtml += '<div class="col-xl-3 col-lg-6 col-md-6 col-12 mb-2">' +
						'<div class="tank-metric-card bg-card-info is-static h-100">' +
							'<div>' +
								'<div class="metric-header">' +
									'<span>' + esc(w.warehouse_name) + '</span>' +
									'<span class="tank-pct-badge">Gudang</span>' +
								'</div>' +
								'<div class="metric-value">' + cnsFormatNumber(whSisa, 2) + ' <span style="font-size:13px;font-weight:600;">KG</span></div>' +
							'</div>' +
							'<div class="metric-icon-box"><i class="fa fa-warehouse"></i></div>' +
							'<div>' +
								'<div class="tank-progress">' +
									'<div class="tank-progress-bar" style="width: ' + Math.min(whPct, 100) + '%;"></div>' +
								'</div>' +
								'<div class="tank-meta">' +
									'<span>Masuk: ' + cnsFormatNumber(whMasuk, 2) + ' KG</span>' +
									'<span>SO: ' + cnsFormatNumber(w.total_so_used || 0, 2) + ' KG</span>' +
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

		if (!drainingId) { swal('Perhatian', 'Kode draining sumber wajib dipilih.', 'warning'); return; }
		if (!warehouseId) { swal('Perhatian', 'Gudang tujuan wajib dipilih.', 'warning'); return; }
		if (!moveDate) { swal('Perhatian', 'Tanggal moving wajib diisi.', 'warning'); return; }
		if (qty <= 0) { swal('Perhatian', 'Qty moving harus lebih dari 0.', 'warning'); return; }

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
					swal('Berhasil!', res.message || 'Moving sludge berhasil disimpan.', 'success').then(function () {
						toggleSludgeMovingForm(false);
						loadSludgeStock();
					});
				} else {
					swal('Error', (res && res.message) || 'Gagal menyimpan moving sludge.', 'error');
				}
			},
			error: function(xhr) {
				var msg = (xhr.responseJSON && xhr.responseJSON.message) || xhr.responseText || 'Terjadi kesalahan saat menyimpan moving sludge.';
				swal('Error', msg, 'error');
			}
		});
	}
</script>
@endsection
