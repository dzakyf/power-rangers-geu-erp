@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<!-- Main Content Header -->
<div class="main-content-header mb-3">
	<h1 class="page-title fs-20 fw-semibold mb-1">Dashboard PTK</h1>
	<ol class="breadcrumb mb-0">
		<li class="breadcrumb-item">
			<a href="{{ route('hrd-recruitment-requisition') }}">Recruitment</a>
		</li>
		<li class="breadcrumb-item active" aria-current="page">Dashboard PTK</li>
	</ol>
</div>

<div class="card custom-card">
	<div class="card-header d-flex justify-content-between align-items-center">
		<div class="card-title mb-0">
			<i class="fe fe-pie-chart me-2"></i>Pivot Analisis Pengajuan PTK (Requisition)
		</div>
		<span class="badge bg-primary text-white">Total: {{ isset($getRecruit) ? count($getRecruit) : 0 }} Data</span>
	</div>
	<div class="card-body">
		<div class="row">
			<div class="col-12">
				<div class="table-responsive">
					<div class="w-100 min-h-300" id="pivottrial">
						<div class="text-center text-muted p-5">
							<div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div>
							<span>Memuat pivot table...</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pivottable/2.23.0/pivot.min.css">
<script src="{{ asset('assets/plugins/jquery-ui/js/jquery-ui.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pivottable/2.23.0/pivot.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pivottable/2.23.0/tips_data.min.js"></script>
<script>
	$(document).ready(function() {
		try {
			var rawData = {!! json_encode(isset($getRecruit) ? $getRecruit : []) !!};

			if (Array.isArray(rawData) && rawData.length > 0 && typeof $.fn.pivotUI === 'function') {
				$('#pivottrial').empty().pivotUI(rawData, {
					rows: ["Department", "Job_Position"],
					cols: ["Status"],
					rendererName: "Table",
					aggregatorName: "Count"
				});
			} else if (Array.isArray(rawData) && rawData.length === 0) {
				$('#pivottrial').html('<div class="text-center text-muted p-5"><i class="fe fe-info fs-30 d-block mb-2"></i>Tidak ada data PTK yang tersedia untuk ditampilkan di pivot table.</div>');
			} else {
				$('#pivottrial').html('<div class="text-center text-danger p-4"><i class="fe fe-alert-triangle fs-30 d-block mb-2"></i>Komponen Pivot Table tidak berhasil diinisialisasi.</div>');
			}
		} catch (e) {
			console.error("Pivot UI init error:", e);
			$('#pivottrial').html('<div class="text-center text-danger p-4"><i class="fe fe-alert-circle fs-30 d-block mb-2"></i>Terjadi kesalahan saat memproses data pivot table: ' + (e.message || e) + '</div>');
		}
	});
</script>
@endpush
