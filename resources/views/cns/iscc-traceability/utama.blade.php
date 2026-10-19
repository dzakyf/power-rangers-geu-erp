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
<div class="main-content-header">
	<h1>ISCC Sustainability</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item"><a href="/">ISCC Sustainability</a></li>
		<li class="breadcrumb-item active"><span class="active">Traceability</span></li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">TRACEABILITY</div>
	<div class="card">
		<div class="card-body">
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
			<div class="mt-2">
				<button type="button" id="detailBtn" class="btn btn-primary" onclick="detailSelected()" disabled>Detail</button>
			</div>
		</div>
	</div>
	<div id="divTraceDetail"></div>
</div>
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
				{ data: 'status' },
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
