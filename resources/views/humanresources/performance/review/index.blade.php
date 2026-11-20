@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
@include('humanresources.performance.partials.page-header', [
    'title'       => '5. Performance Review & Appraisal',
    'parentLabel' => 'Performance Management',
    'parentUrl'   => url('hrd-performance-review-index'),
    'current'     => 'Performance Review',
    'icon'        => 'fa-clipboard-check',
    'badgeText'   => 'Step 5: Siklus & Penilaian',
])

@include('humanresources.performance.partials.workflow-guide', ['activeStep' => 5])

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
                <button type="button" class="btn btn-info text-white me-2" onclick="openSelectedReview()">
                    <i class="feather-edit me-1"></i> Nilai / Buka Lembar Appraisal
                </button>
                <button type="button" class="btn btn-outline-primary me-2" onclick="openEditCyclePage()">
                    <i class="feather-sliders me-1"></i> Edit Siklus
                </button>
                <button type="button" class="btn btn-outline-warning" onclick="openRescheduleCycleModal()">
                    <i class="feather-calendar me-1"></i> Ubah Waktu Siklus
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

    function openEditCyclePage() {
        var cycleId = document.getElementById('filterCycle').value;
        if (!cycleId) {
            Swal.fire('Perhatian', 'Pilih salah satu siklus review pada dropdown Filter Siklus Review terlebih dahulu.', 'warning');
            return;
        }
        window.location.href = "{{ url('hrd-performance-review-cycle-edit') }}/" + cycleId;
    }

    function openRescheduleCycleModal() {
        var cycleId = document.getElementById('filterCycle').value;
        if (!cycleId) {
            Swal.fire('Perhatian', 'Pilih salah satu siklus review pada dropdown filter terlebih dahulu.', 'warning');
            return;
        }
        document.getElementById('rescheduleCycleId').value = cycleId;
        new bootstrap.Modal(document.getElementById('modalRescheduleCycle')).show();
    }

    function submitRescheduleCycle() {
        var cycleId = document.getElementById('rescheduleCycleId').value;
        var form = document.getElementById('formRescheduleCycle');
        if (!form.checkValidity()) { form.reportValidity(); return; }
        fetch("{{ url('hrd-performance-review-cycle-reschedule') }}/" + cycleId, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: new FormData(form)
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Berhasil', res.message, 'success').then(() => location.reload());
                } else {
                    alert(res.message);
                    location.reload();
                }
            } else {
                if (typeof Swal !== 'undefined') { Swal.fire('Gagal', res.message, 'error'); } else { alert(res.message); }
            }
        })
        .catch(err => {
            if (typeof Swal !== 'undefined') { Swal.fire('Error', err.message, 'error'); } else { alert(err.message); }
        });
    }
</script>

<!-- Modal Ubah Waktu Siklus Review -->
<div class="modal fade" id="modalRescheduleCycle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ubah Waktu / Durasi Siklus Penilaian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formRescheduleCycle">
                    <input type="hidden" id="rescheduleCycleId">
                    <div class="mb-2">
                        <label class="form-label fw-bold">Tipe Evaluasi</label>
                        <select name="review_type" class="form-select form-select-sm">
                            <option value="">-- Pertahankan Tipe Saat Ini --</option>
                            <option value="SEMESTER_1">Semester 1</option>
                            <option value="SEMESTER_2">Semester 2</option>
                            <option value="MID_YEAR">Mid-Year (6 Bulanan)</option>
                            <option value="ANNUAL">Annual</option>
                            <option value="PROBATION">Probation</option>
                            <option value="PROJECT">Project / Khusus (mis. 3 Bulan)</option>
                        </select>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label fw-bold">Tanggal Mulai Baru <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold">Tanggal Akhir Baru <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" class="form-control form-control-sm" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold">Alasan Perubahan Jadwal <span class="text-danger">*</span></label>
                        <textarea name="reschedule_reason" class="form-control form-control-sm" rows="2" placeholder="Misal: Percepatan evaluasi dari 6 bulan menjadi 3 bulan karena kebijakan manajemen..." required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="submitRescheduleCycle()">Simpan Perubahan Jadwal</button>
            </div>
        </div>
    </div>
</div>
@endsection

