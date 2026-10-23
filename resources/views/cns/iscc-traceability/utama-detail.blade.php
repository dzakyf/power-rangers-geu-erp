<style>
	.frag-row { cursor: pointer; }
	.frag-row.selected { background-color: #d9edf7 !important; }
</style>
<div class="mt-3">
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">TRACEABILITY DETAIL</div>
	<div class="card">
		<div class="card-body">
			<table id="deliveryFragTable" class="table table-striped table-bordered" style="width:100%">
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
						<th>Qty (Kg)</th>
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
			<div class="mt-2">
				<button type="button" id="editDetailBtn" class="btn btn-warning mr-2" onclick="editSelectedDetail()" disabled>Edit Data</button>
				<button type="button" id="printDetailBtn" class="btn btn-info" onclick="printSelectedDetail()" disabled>Print Doc</button>
			</div>
		</div>
	</div>
	<div id="divTraceDetailInner"></div>
</div>

<script>
	(function () {
		var id = {{ (int) ($id ?? 0) }};
		var selectedRow = null;

		if ($.fn.DataTable.isDataTable('#deliveryFragTable')) {
			$('#deliveryFragTable').DataTable().destroy();
		}

		var tbl = $('#deliveryFragTable').DataTable({
			processing: true,
			serverSide: true,
			pageLength: 25,
			lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
			ajax: {
				url: 'cns-iscc-traceability-delivery-fragment-data',
				data: function (d) { d.id = id; }
			},
			columns: [
				{ data: 'no' },
				{ data: 'source' },
				{ data: 'poo_name' },
				{ data: 'poo_type' },
				{ data: 'site' },
				{ data: 'city' },
				{ data: 'province' },
				{ data: 'plat_no' },
				{ data: 'driver_name' },
				{ data: 'quantity_kg', className: 'text-right' },
				{ data: 'price', className: 'text-right' },
				{ data: 'total_price', className: 'text-right' },
				{ data: 'wo_code' },
				{ data: 'wo_date' },
				{ data: 'wo_status' },
				{ data: 'po_code' },
				{ data: 'po_date' },
				{ data: 'po_status' },
				{ data: 'do_code' },
				{ data: 'do_date' },
				{ data: 'do_status' }
			],
			order: [[0, 'asc']],
			rowCallback: function (row, data) {
				$(row).addClass('frag-row');
				$(row).on('click', function () {
					$('#deliveryFragTable tbody tr').removeClass('selected');
					$(this).addClass('selected');
					selectedRow = data;
					var ok = !!data.fragment_id;
					$('#editDetailBtn').prop('disabled', !ok);
					$('#printDetailBtn').prop('disabled', !ok);
				});
			},
			drawCallback: function () {
				selectedRow = null;
				$('#editDetailBtn, #printDetailBtn').prop('disabled', true);
			}
		});

		window.editSelectedDetail = function () {
			if (selectedRow && selectedRow.fragment_id) {
				LoadM('divTraceDetailInner', 'cns-iscc-traceability-edit/' + selectedRow.fragment_id);
			}
		};
		window.printSelectedDetail = function () {
			if (selectedRow && selectedRow.fragment_id) {
				window.open('cns-iscc-traceability-print/' + selectedRow.fragment_id, '_blank');
			}
		};
	})();
</script>
