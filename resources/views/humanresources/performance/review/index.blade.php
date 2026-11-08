@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
<div class="main-content-header mb-3">
    <h1>Performance Management</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('hrd-performance-kpi-index') }}">Performance</a></li>
        <li class="breadcrumb-item active"><span class="active">Performance Review & Appraisal</span></li>
    </ol>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Evaluasi & Penilaian Kinerja Karyawan</h5>
        <div>
            <a href="{{ url('hrd-performance-review-cycle-new') }}" class="btn btn-primary me-2">
                <i class="feather-plus me-1"></i> Buat Siklus Review Baru
            </a>
            <button type="button" class="btn btn-outline-success" onclick="triggerGenerateReviews()">
                <i class="feather-users me-1"></i> Inisiasi Lembar Evaluasi Karyawan
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label font-weight-bold">Filter Siklus Review</label>
                <select id="filterCycle" class="form-select form-control" onchange="reloadGrid()">
                    <option value="">-- Semua Siklus Review --</option>
                    @foreach($cycles as $c)
                        <option value="{{ $c->id }}" {{ $c->status === 'ONGOING' ? 'selected' : '' }}>
                            {{ $c->title }} ({{ $c->cycle_code }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label font-weight-bold">Filter Status</label>
                <select id="filterStatus" class="form-select form-control" onchange="reloadGrid()">
                    <option value="">-- Semua Status --</option>
                    <option value="SCHEDULED">SCHEDULED</option>
                    <option value="SELF_REVIEW">SELF_REVIEW</option>
                    <option value="MANAGER_REVIEW">MANAGER_REVIEW</option>
                    <option value="CALIBRATION">CALIBRATION</option>
                    <option value="FINALIZED">FINALIZED</option>
                </select>
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <button type="button" class="btn btn-secondary me-2" onclick="reloadGrid()">
                    <i class="feather-refresh-cw me-1"></i> Refresh
                </button>
                <button type="button" class="btn btn-info text-white" onclick="openSelectedReview()">
                    <i class="feather-edit me-1"></i> Nilai / Buka Lembar Appraisal
                </button>
            </div>
        </div>

        <div id="gridReviewContainer" style="width: 100%; height: 500px; background-color: white;"></div>
    </div>
</div>

<script>
    var myGrid;
    document.addEventListener("DOMContentLoaded", function () {
        myGrid = new dhtmlXGridObject('gridReviewContainer');
        myGrid.setImagePath("{{ asset('dhtmlx/skins/skyblue/imgs/') }}/");
        myGrid.setHeader("No Review,Siklus,Karyawan,Departemen,Atasan Penilai,Goal Tautan,KPI Skor,Self,Manager,Skor Akhir,Grade,Status");
        myGrid.setInitWidths("140,160,160,130,150,140,85,75,80,90,70,120");
        myGrid.setColAlign("left,left,left,left,left,center,right,right,right,right,center,center");
        myGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
        myGrid.setColSorting("str,str,str,str,str,str,int,int,int,int,str,str");
        myGrid.enableAutoWidth(true);
        myGrid.init();

        myGrid.attachEvent("onRowDblClicked", function(rId, cInd){
            window.location.href = "{{ url('hrd-performance-review-detail') }}/" + rId;
        });

        reloadGrid();
    });

    function reloadGrid() {
        var cycleId = document.getElementById('filterCycle').value;
        var status = document.getElementById('filterStatus').value;
        var url = "{{ url('hrd-performance-review-data') }}?cycle_id=" + cycleId + "&status=" + status;
        myGrid.clearAndLoad(url);
    }

    function openSelectedReview() {
        var selectedId = myGrid.getSelectedRowId();
        if (!selectedId) {
            Swal.fire('Perhatian', 'Silakan pilih salah satu baris lembar review terlebih dahulu.', 'warning');
            return;
        }
        window.location.href = "{{ url('hrd-performance-review-detail') }}/" + selectedId;
    }

    function triggerGenerateReviews() {
        var cycleId = document.getElementById('filterCycle').value;
        if (!cycleId) {
            Swal.fire('Perhatian', 'Pilih salah satu siklus review pada dropdown filter sebelum melakukan inisiasi massal.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Inisiasi Lembar Evaluasi?',
            text: 'Sistem akan membuatkan lembar appraisal untuk seluruh karyawan aktif dan otomatis menautkan goal KPI/OKR yang telah disetujui.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Inisiasi',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch("{{ url('hrd-performance-review-generate') }}/" + cycleId, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        Swal.fire('Berhasil', res.message, 'success').then(() => reloadGrid());
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                })
                .catch(err => Swal.fire('Error', err.message, 'error'));
            }
        });
    }
</script>
@endsection

