@extends('layout.mainlayout-cns')

@push('head')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/plugins/data-tables/css/datatables.min.css') }}">
<style>
	.del-row { cursor: pointer; }
	.del-row.selected { background-color: #d9edf7 !important; }
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
			<button type="button" id="detailBtn" class="btn btn-primary btn-sm" onclick="detailSelected()" disabled>
				<i class="fa fa-eye"></i> Detail
			</button>
		</div>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table id="deliveryTable" class="table table-striped table-bordered" style="width:100%">
				<thead>
					<tr>
						<th>No</th>
						<th>Source</th>
						<th>Supplier Name</th>
						<th>Site</th>
						<th>Provinsi</th>
						<th>Surat Jalan Date</th>
						<th>Total Qty</th>
						<th>Status</th>
						<th>Reference</th>
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
	var deliveryTable;
	var selectedDelivery = null;

	$(function () {
		deliveryTable = $('#deliveryTable').DataTable({
			processing: true,
			serverSide: true,
			pageLength: 25,
			lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
			ajax: { url: 'cns-iscc-traceability-delivery-data' },
			columns: [
				{ data: 'no' },
				{ data: 'source' },
				{ data: 'supplier_name' },
				{ data: 'city' },
				{ data: 'province' },
				{ data: 'delivery_date' },
				{ data: 'total_quantity_kg', className: 'text-right' },
				{ data: 'status', render: function (d) { return window.cnsStatusBadge(d); } },
				{ data: 'reference' }
			],
			order: [[5, 'desc']],
			rowCallback: function (row, data) {
				$(row).addClass('del-row');
				$(row).on('click', function () {
					$('#deliveryTable tbody tr').removeClass('selected');
					$(this).addClass('selected');
					selectedDelivery = data;
					$('#detailBtn').prop('disabled', !(data.delivery_id));
				});
			},
			drawCallback: function () {
				selectedDelivery = null;
				$('#detailBtn').prop('disabled', true);
			}
		});
	});

	function detailSelected() {
		if (selectedDelivery && selectedDelivery.delivery_id) {
			LoadM('divTraceDetail', 'cns-iscc-traceability-utama-detail?id=' + selectedDelivery.delivery_id);
		}
	}
</script>
@endpush
