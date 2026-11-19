@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
<div class="main-content-header mb-3">
    <h1>Performance Management</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('hrd-performance-review-index') }}">Performance Review</a></li>
        <li class="breadcrumb-item active"><span class="active">Lembar Appraisal {{ $review->review_no }}</span></li>
    </ol>
</div>

<!-- Header Info Karyawan -->
<div class="card mb-3">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h4 class="mb-1">{{ $review->user->Name ?? '-' }} <span class="badge bg-primary text-white ms-2">{{ $review->review_no }}</span></h4>
                <p class="text-muted mb-0">
                    <strong>Departemen:</strong> {{ $review->user->Department ?? '-' }} | 
                    <strong>Jabatan:</strong> {{ $review->user->Position ?? '-' }} | 
                    <strong>Atasan Penilai:</strong> {{ $review->manager->Name ?? 'Belum Ditugaskan' }}
                </p>
                <p class="text-muted mb-0">
                    <strong>Siklus:</strong> {{ $review->cycle->title ?? '-' }} | 
                    <strong>Goal KPI/OKR Terhubung:</strong> 
                    @if($review->goal)
                        <a href="{{ url('hrd-performance-kpi-detail', $review->goal->id) }}" class="fw-bold text-decoration-underline" target="_blank">{{ $review->goal->goal_no }}</a>
                    @else
                        <span class="text-danger">Belum ada Goal Approved</span>
                    @endif
                </p>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0">
                <span class="badge bg-{{ $review->status === 'FINALIZED' ? 'success' : 'warning' }} p-2 fs-6">
                    Tahapan: {{ $review->status }}
                </span>
                <div class="mt-2">
                    <span class="h5 font-weight-bold text-primary">Skor Akhir: {{ $review->final_score !== null ? number_format($review->final_score, 1) : '-' }}</span>
                    @if($review->rating_grade)
                        <span class="badge bg-{{ $review->rating_grade === 'D' ? 'danger' : 'info' }} text-white fs-6 ms-1">Grade: {{ $review->rating_grade }}</span>
                    @endif
                </div>
                <div class="mt-2">
                    @if(!empty($canSelfReview))
                        <button type="button" class="btn btn-info text-white me-1" onclick="openFeedbackModal()">
                            <i class="feather-user-check me-1"></i> Isi Self Review
                        </button>
                    @endif
                    @if(!empty($canManagerReview))
                        <button type="button" class="btn btn-primary me-1" onclick="openFeedbackModal()">
                            <i class="feather-edit-3 me-1"></i> Nilai Kompetensi & Ulasan (Atasan)
                        </button>
                    @endif
                    @if(!empty($canFinalize) && $review->status !== 'FINALIZED')
                        <button type="button" class="btn btn-success me-1" onclick="finalizeReview({{ $review->id }})">
                            <i class="feather-check-circle me-1"></i> Finalisasi / Kalibrasi
                        </button>
                    @endif
                    <a href="{{ url('hrd-performance-review-index') }}" class="btn btn-secondary">
                        <i class="feather-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tab Pencapaian KPI/OKR & Catatan Review -->
<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">1. Realisasi KPI & OKR (Terhubung Otomatis)</h5>
                <span class="badge bg-success p-2">Skor Pencapaian: {{ number_format($review->kpi_score, 1) }}%</span>
            </div>
            <div class="card-body">
                @php
                    $kpiRows = ($review->kpiItems && $review->kpiItems->count() > 0)
                        ? $review->kpiItems
                        : ($review->goal ? $review->goal->items : collect());
                @endphp
                @if($kpiRows->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Indikator Sasaran</th>
                                    <th class="text-end" style="width: 80px;">Target</th>
                                    <th class="text-end" style="width: 80px;">Realisasi</th>
                                    <th class="text-end" style="width: 70px;">Bobot</th>
                                    <th class="text-end" style="width: 80px;">Capaian</th>
                                    @if(!empty($canEditKpiRealization))
                                        <th class="text-center" style="width: 90px;">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($kpiRows as $it)
                                    @php $goalItemId = $it->goal_item_id ?? $it->id; @endphp
                                    <tr>
                                        <td>
                                            <span class="badge bg-secondary me-1">{{ $it->item_type }}</span>
                                            <strong>{{ $it->title }}</strong>
                                        </td>
                                        <td class="text-end">{{ number_format($it->target_value, 1) }} {{ $it->kpi_unit }}</td>
                                        <td class="text-end font-weight-bold text-success">{{ number_format($it->actual_value, 1) }}</td>
                                        <td class="text-end">{{ $it->weight_percent > 0 ? number_format($it->weight_percent, 1) . '%' : '-' }}</td>
                                        <td class="text-end font-weight-bold">{{ number_format($it->progress_percent, 1) }}%</td>
                                        @if(!empty($canEditKpiRealization))
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="openKpiRealizationModal({{ $goalItemId }}, '{{ addslashes($it->title) }}', {{ $it->actual_value }}, '{{ $it->kpi_unit }}')">
                                                    <i class="feather-edit-3"></i> Input
                                                </button>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-warning mb-0">
                        Karyawan belum memiliki sasaran kerja KPI/OKR yang berstatus Approved pada periode ini.
                    </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">2. Rangkuman Umpan Balik & Evaluasi Kualitatif</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label font-weight-bold text-primary">Kelebihan & Kekuatan Karyawan (Strengths)</label>
                    <div class="p-3 bg-light rounded">{{ $review->strength_notes ?? 'Belum ada catatan kekuatan yang dimasukkan.' }}</div>
                </div>
                <div class="mb-3">
                    <label class="form-label font-weight-bold text-danger">Area Pengembangan & Perbaikan (Areas for Improvement)</label>
                    <div class="p-3 bg-light rounded">{{ $review->improvement_notes ?? 'Belum ada catatan area perbaikan yang dimasukkan.' }}</div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label font-weight-bold">Self Review Comment</label>
                        <div class="p-2 border rounded bg-white small">{{ $review->self_notes ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label font-weight-bold">Manager Review Comment</label>
                        <div class="p-2 border rounded bg-white small">{{ $review->manager_notes ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Ringkasan Komposisi Nilai -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Komposisi Bobot & Skor Akhir</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush mb-3">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        KPI / OKR (Bobot {{ $review->cycle->weight_kpi ?? 70 }}%)
                        <span class="font-weight-bold">{{ number_format($review->kpi_score, 1) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Competency Matrix (Bobot {{ $review->cycle->weight_competency ?? 30 }}%)
                        <span class="font-weight-bold text-info">{{ $review->competency_score > 0 ? number_format($review->competency_score, 1) : '-' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Self Review Score
                        <span class="font-weight-bold text-secondary">{{ $review->self_score !== null ? number_format($review->self_score, 1) : '-' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Manager Review Score
                        <span class="font-weight-bold text-info">{{ $review->manager_score !== null ? number_format($review->manager_score, 1) : '-' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-light">
                        <span class="h6 mb-0 font-weight-bold">Total Nilai Akhir</span>
                        <span class="h5 mb-0 font-weight-bold text-primary">{{ $review->final_score !== null ? number_format($review->final_score, 1) : '-' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-light">
                        <span class="h6 mb-0 font-weight-bold">Predikat / Rating Grade</span>
                        <span class="badge bg-{{ $review->rating_grade === 'D' ? 'danger' : 'success' }} fs-6">{{ $review->rating_grade ?? '-' }}</span>
                    </li>
                </ul>

                @php
                    $pipThreshold = ($review->cycle && $review->cycle->pip_threshold_score !== null)
                        ? (float) $review->cycle->pip_threshold_score
                        : (float) \App\PerformanceSetting::getValue('default_pip_threshold_score', 60);
                @endphp
                @if($review->rating_grade === 'D' || ($review->final_score !== null && $review->final_score < $pipThreshold))
                    <div class="alert alert-danger small mb-0">
                        <i class="feather-alert-triangle me-1"></i> <strong>Perhatian:</strong> Skor berada di bawah standar minimum (&lt; {{ $pipThreshold }} / Grade D). Dokumen PIP (Performance Improvement Plan) diterbitkan secara otomatis untuk perbaikan performa.
                        <div class="mt-2 text-end">
                            @if($review->pipPlan)
                                <a href="{{ url('hrd-performance-pip-detail', $review->pipPlan->id) }}" class="btn btn-sm btn-danger text-white">Buka PIP {{ $review->pipPlan->pip_no }}</a>
                            @else
                                <a href="{{ url('hrd-performance-pip-index') }}" class="btn btn-sm btn-danger text-white">Lihat PIP Plan</a>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="alert alert-info small mb-0">
                        <i class="feather-info me-1"></i> Nilai akhir menggabungkan pencapaian KPI/OKR aktual dan evaluasi baku Competency Matrix.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Input Penilaian Feedback & Competency Matrix -->
<div class="modal fade" id="modalFeedback" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Form Penilaian Evaluasi & Competency Matrix</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formFeedback">
                    <h6 class="font-weight-bold text-primary mb-2">A. Penilaian Matriks Kompetensi Baku (Skala 1 - 5)</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Kompetensi</th>
                                    <th>Tipe</th>
                                    <th style="width: 140px;" class="text-center">Skor (1 - 5)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($competencies as $comp)
                                    <tr>
                                        <td>
                                            <strong>{{ $comp->name }}</strong>
                                            <div class="text-muted small">{{ $comp->behavioral_indicators ?? $comp->description }}</div>
                                        </td>
                                        <td><span class="badge bg-secondary">{{ $comp->type }}</span></td>
                                        <td class="text-center">
                                            <select name="competencies[{{ $comp->id }}]" class="form-select form-control form-control-sm">
                                                <option value="5">5 - Exceptional</option>
                                                <option value="4" selected>4 - Exceeds Standard</option>
                                                <option value="3">3 - Meets Standard</option>
                                                <option value="2">2 - Needs Improvement</option>
                                                <option value="1">1 - Unsatisfactory</option>
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <h6 class="font-weight-bold text-primary mb-2">B. Ulasan & Umpan Balik Kualitatif</h6>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Kelebihan / Kekuatan Karyawan</label>
                        <textarea name="strength_notes" class="form-control" rows="2" placeholder="Sebutkan hal-hal positif dan pencapaian terbaik...">{{ $review->strength_notes }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Area Perbaikan / Pengembangan</label>
                        <textarea name="improvement_notes" class="form-control" rows="2" placeholder="Sebutkan kompetensi atau aspek kerja yang perlu ditingkatkan...">{{ $review->improvement_notes }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Ulasan / Umpan Balik Keseluruhan</label>
                        <textarea name="feedback" class="form-control" rows="2" placeholder="Ulasan umum untuk karyawan...">{{ $review->manager_notes ?? $review->self_notes }}</textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="saveFeedback()">
                    <i class="feather-save me-1"></i> Simpan Penilaian Kompetensi
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Input Realisasi KPI pada Review 6 Bulanan -->
<div class="modal fade" id="modalKpiRealization" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Input Realisasi KPI (Evaluasi 6 Bulanan)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formKpiRealization">
                    <input type="hidden" name="goal_item_id" id="revGoalItemId">
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Indikator KPI</label>
                        <p id="revGoalItemTitle" class="text-primary mb-0 font-weight-bold"></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nilai Realisasi Aktual (<span id="revGoalItemUnit"></span>) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="new_value" id="revGoalNewValue" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan Evaluasi Realisasi</label>
                        <textarea name="comment" class="form-control" rows="2" placeholder="Catatan pencapaian 6 bulanan..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="saveKpiRealization()">
                    <i class="feather-save me-1"></i> Simpan Realisasi &amp; Hitung Skor
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function openKpiRealizationModal(itemId, title, actualVal, unit) {
        document.getElementById('revGoalItemId').value = itemId;
        document.getElementById('revGoalItemTitle').innerText = title;
        document.getElementById('revGoalNewValue').value = actualVal;
        document.getElementById('revGoalItemUnit').innerText = unit || '%';
        var myModal = new bootstrap.Modal(document.getElementById('modalKpiRealization'));
        myModal.show();
    }

    function saveKpiRealization() {
        var form = document.getElementById('formKpiRealization');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        var formData = new FormData(form);
        fetch("{{ url('hrd-performance-review-update-kpi', $review->id) }}", {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                Swal.fire('Berhasil', res.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Gagal', res.message, 'error');
            }
        })
        .catch(err => Swal.fire('Error', err.message, 'error'));
    }

    function openFeedbackModal() {
        var myModal = new bootstrap.Modal(document.getElementById('modalFeedback'));
        myModal.show();
    }

    function saveFeedback() {
        var form = document.getElementById('formFeedback');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        var formData = new FormData(form);
        fetch("{{ url('hrd-performance-review-feedback', $review->id) }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                Swal.fire('Berhasil', res.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Gagal', res.message, 'error');
            }
        })
        .catch(err => Swal.fire('Error', err.message, 'error'));
    }

    function finalizeReview(reviewId) {
        Swal.fire({
            title: 'Finalisasi / Kalibrasi Review?',
            text: 'Proses ini akan mengunci skor akhir. Jika skor < 60 atau Grade D, sistem otomatis menerbitkan PIP Plan.',
            input: 'number',
            inputLabel: 'Skor Kalibrasi Akhir (Opsional, kosongkan jika tetap)',
            inputValue: '{{ $review->final_score ?? 80 }}',
            showCancelButton: true,
            confirmButtonText: 'Ya, Finalisasi',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                var payload = new FormData();
                payload.append('calibrated_score', result.value);

                fetch("{{ url('hrd-performance-review-finalize') }}/" + reviewId, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: payload
                })
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        Swal.fire('Berhasil', res.message, 'success').then(() => location.reload());
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
