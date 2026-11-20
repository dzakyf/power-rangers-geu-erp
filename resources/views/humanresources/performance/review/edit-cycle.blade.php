@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    @csrf
    @include('humanresources.performance.partials.page-header', [
        'title' => 'Edit Siklus Evaluasi',
        'parentLabel' => 'Performance Review',
        'parentUrl' => url('hrd-performance-review-index'),
        'current' => 'Edit Siklus ' . $cycle->cycle_code,
        'icon' => 'fa-sliders',
        'badgeText' => 'Siklus Appraisal',
    ])

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Edit Siklus Evaluasi Kinerja ({{ $cycle->cycle_code }})</h5>
            <span class="badge bg-info text-dark">{{ $cycle->status }}</span>
        </div>
        <div class="card-body">
            <form action="{{ url('hrd-performance-review-cycle-update', $cycle->id) }}" method="POST">
                @csrf
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label font-weight-bold">Kode Siklus</label>
                        <input type="text" class="form-control bg-light" value="{{ $cycle->cycle_code }}" readonly>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label font-weight-bold">Judul Siklus Evaluasi <span
                                class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $cycle->title) }}"
                            required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label font-weight-bold">Tautkan ke Periode KPI <span
                                class="text-danger">*</span></label>
                        <select name="period_id" class="form-select form-control" required>
                            @foreach ($periods as $p)
                                <option value="{{ $p->id }}"
                                    {{ (int) old('period_id', $cycle->period_id) === (int) $p->id ? 'selected' : '' }}>
                                    {{ $p->name }} ({{ $p->period_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label font-weight-bold">Jenis Evaluasi</label>
                        <select name="review_type" class="form-select form-control">
                            @foreach (['SEMESTER_1' => 'Semester 1 (6 Bulan Pertama)', 'SEMESTER_2' => 'Semester 2 (6 Bulan Kedua)', 'MID_YEAR' => 'Mid-Year (6 Bulanan)', 'ANNUAL' => 'Annual Review', 'PROBATION' => 'Evaluasi Probation', 'PROJECT' => 'Review Khusus / Percepatan'] as $val => $lbl)
                                <option value="{{ $val }}"
                                    {{ old('review_type', $cycle->review_type) === $val ? 'selected' : '' }}>
                                    {{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label font-weight-bold">Bobot Skor KPI / OKR (%)</label>
                        <input type="number" step="0.01" name="weight_kpi" class="form-control"
                            value="{{ old('weight_kpi', $cycle->weight_kpi) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label font-weight-bold">Bobot Skor Soft Skill (%)</label>
                        <input type="number" step="0.01" name="weight_competency" class="form-control"
                            value="{{ old('weight_competency', $cycle->weight_competency) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label font-weight-bold">Status Siklus</label>
                        <select name="status" class="form-select form-control">
                            @foreach (['DRAFT', 'ONGOING', 'COMPLETED', 'ARCHIVED'] as $st)
                                <option value="{{ $st }}"
                                    {{ old('status', $cycle->status) === $st ? 'selected' : '' }}>{{ $st }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label font-weight-bold">Komponen Penilai (360)</label>
                        <div class="mt-2">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="enable_self_review"
                                    id="enable_self_review"
                                    {{ old('enable_self_review', $cycle->enable_self_review) ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable_self_review">Self</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="enable_manager_review"
                                    id="enable_manager_review"
                                    {{ old('enable_manager_review', $cycle->enable_manager_review) ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable_manager_review">Manager</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="enable_peer_review"
                                    id="enable_peer_review"
                                    {{ old('enable_peer_review', $cycle->enable_peer_review) ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable_peer_review">Peer</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label font-weight-bold">Tanggal Mulai Evaluasi <span
                                class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control"
                            value="{{ old('start_date', $cycle->start_date) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label font-weight-bold">Batas Akhir Evaluasi <span
                                class="text-danger">*</span></label>
                        <input type="date" name="end_date" class="form-control"
                            value="{{ old('end_date', $cycle->end_date) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label font-weight-bold">Ambang Batas Minimum PIP (&lt; Skor)</label>
                        <input type="number" step="0.01" min="0" max="100" name="pip_threshold_score"
                            class="form-control" value="{{ old('pip_threshold_score', $cycle->pip_threshold_score) }}"
                            placeholder="Default: 60">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label font-weight-bold">Catatan / Alasan Perubahan (Opsional)</label>
                        <input type="text" name="reschedule_reason" class="form-control"
                            value="{{ old('reschedule_reason', $cycle->reschedule_reason) }}"
                            placeholder="Misal: Percepatan evaluasi dari 6 bulan menjadi 3 bulan...">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Deskripsi / Petunjuk Pengisian</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $cycle->description) }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ url('hrd-performance-review-index') }}" class="btn btn-secondary me-2">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="feather-save me-1"></i> Simpan Perubahan Siklus
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
