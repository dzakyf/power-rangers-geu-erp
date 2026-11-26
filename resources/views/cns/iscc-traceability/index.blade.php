@extends('layout.mainlayout-cns')

@push('head')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/plugins/data-tables/css/datatables.min.css') }}">
<style>
	.frag-row {
		cursor: pointer;
	}

	.frag-row.selected {
		background-color: #d9edf7 !important;
	}
</style>
@endpush

@section('content-wrapper')
@csrf
@include('cns.partials.page-header', [
    'title' => 'ISCC Traceability',
    'breadcrumbs' => [
        ['label' => 'ISCC Sustainability'],
        ['label' => 'Traceability'],
    ],
])

<div class="card cns-card">
	<div class="card-header">
		<h5 class="card-title">Traceability</h5>
		<div class="d-flex flex-wrap" style="gap: 8px;">
			<button type="button" id="editBtn" class="btn btn-warning btn-sm" onclick="editSelected()" disabled>
				<i class="fa fa-edit"></i> Edit Data
			</button>
			<button type="button" id="printBtn" class="btn btn-info btn-sm" onclick="printSelected()" disabled>
				<i class="fa fa-print"></i> Print Doc
			</button>
			<button type="button" id="exportBtn" class="btn btn-success btn-sm" onclick="exportExcel()">
				<i class="fa fa-file-excel-o"></i> Export Excel
			</button>
		</div>
	</div>
	<div class="card-body">
		<form id="filterForm" class="form-inline cns-filter-bar">
			<select id="fSource" name="source" class="text-black form-control mr-2">
				<option value="">All Sources</option>
				<option value="BDP">BDP</option>
				<option value="GREES">GREES</option>
			</select>
			<input type="text" id="fCity" name="city" placeholder="City / Regency" class="text-black form-control mr-2" />
			<select id="fSite" name="site" class="text-black form-control mr-2">
				<option value="">All Sites</option>
				@foreach(($sites ?? []) as $s)
				<option value="{{ $s }}">{{ $s }}</option>
				@endforeach
			</select>
			<input type="date" id="fDateFrom" name="date_from" class="text-black form-control mr-2" />
			<input type="date" id="fDateTo" name="date_to" class="text-black form-control mr-2" />
			<input type="button" value="Filter" class="btn btn-primary mr-2" onclick="applyFilter()" />
			<input type="button" value="Reset" class="btn btn-secondary" onclick="resetFilter()" />
		</form>
		<div class="table-responsive">
			<table id="fragTable" class="table table-striped table-bordered" style="width:100%">
				<thead>
					<tr>
						<th>No</th>
						<th>Source</th>
						<th>POO Name</th>
						<th>Type</th>
						<th>Site</th>
						<th>City</th>
						<th>Province</th>
						<th>Plat Nomor</th>
						<th>Driver Name</th>
						<th>Qty</th>
						<th>Unit</th>
						<th>Price</th>
						<th>Total Price</th>
						<th>WO Code</th>
						<th>WO Date</th>
						<th>WO Status</th>
						<th>PO Code</th>
						<th>PO Date</th>
						<th>PO Status</th>
						<th>Surat Jalan Code</th>
						<th>Surat Jalan Date</th>
						<th>Surat Jalan Status</th>
					</tr>
				</thead>
			</table>
		</div>
	</div>
</div>
<div id="divTraceDetail"></div>
@endsection

@push('scripts')
<script src="{{ asset('assets/plugins/data-tables/js/datatables.min.js') }}"></script>
<script>
	var fragTable;
	var selectedRow = null;

	$(function() {
		fragTable = $('#fragTable').DataTable({
			processing: true,
			serverSide: true,
			pageLength: 25,
			lengthMenu: [
				[10, 25, 50, 100],
				[10, 25, 50, 100]
			],
			ajax: {
				url: 'cns-iscc-traceability-fragment-data',
				data: function(d) {
					d.source = $('#fSource').val();
					d.city = $('#fCity').val();
					d.site = $('#fSite').val();
					d.date_from = $('#fDateFrom').val();
					d.date_to = $('#fDateTo').val();
				}
			},
			columns: [{
					data: 'no'
				},
				{
					data: 'source'
				},
				{
					data: 'poo_name'
				},
				{
					data: 'poo_type'
				},
				{
					data: 'site'
				},
				{
					data: 'city'
				},
				{
					data: 'province'
				},
				{
					data: 'plat_no'
				},
				{
					data: 'driver_name'
				},
				{
					data: 'quantity_kg',
					className: 'text-right'
				},
				{
					data: 'unit'
				},
				{
					data: 'price',
					className: 'text-right',
					visible: false
				},
				{
					data: 'total_price',
					className: 'text-right'
				},
				{
					data: 'wo_code'
				},
				{
					data: 'wo_date'
				},
				{
					data: 'wo_status',
					render: function(d) { return window.cnsStatusBadge(d); }
				},
				{
					data: 'po_code'
				},
				{
					data: 'po_date'
				},
				{
					data: 'po_status',
					render: function(d) { return window.cnsStatusBadge(d); }
				},
				{
					data: 'do_code'
				},
				{
					data: 'do_date'
				},
				{
					data: 'do_status',
					render: function(d) { return window.cnsStatusBadge(d); }
				}
			],
			order: [
				[2, 'asc']
			],
			rowCallback: function(row, data) {
				$(row).addClass('frag-row');
				$(row).on('click', function() {
					$('#fragTable tbody tr').removeClass('selected');
					$(this).addClass('selected');
					selectedRow = data;
					var ok = (data.source === 'GREES' && data.fragment_id);
					$('#editBtn').prop('disabled', !ok);
					$('#printBtn').prop('disabled', !ok);
				});
			},
			drawCallback: function() {
				selectedRow = null;
				$('#editBtn, #printBtn').prop('disabled', true);
			}
		});
	});

	function applyFilter() {
		fragTable.ajax.reload();
	}

	function resetFilter() {
		$('#fSource').val('');
		$('#fCity').val('');
		$('#fSite').val('');
		$('#fDateFrom').val('');
		$('#fDateTo').val('');
		fragTable.ajax.reload();
	}

	function editSelected() {
		if (selectedRow && selectedRow.fragment_id) {
			LoadM('divTraceDetail', 'cns-iscc-traceability-edit/' + selectedRow.fragment_id);
		}
	}

	function printSelected() {
		if (selectedRow && selectedRow.fragment_id) {
			window.open('cns-iscc-traceability-print/' + selectedRow.fragment_id, '_blank');
		}
	}

	function exportExcel() {
		var q = $.param({
			source: $('#fSource').val(),
			city: $('#fCity').val(),
			site: $('#fSite').val(),
			date_from: $('#fDateFrom').val(),
			date_to: $('#fDateTo').val(),
			search: fragTable.search()
		});
		window.open('cns-iscc-traceability-fragment-export?' + q, '_blank');
	}
</script>
@endpush