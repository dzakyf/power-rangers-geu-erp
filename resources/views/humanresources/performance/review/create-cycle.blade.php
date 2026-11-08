@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
<div class="main-content-header mb-3">
    <h1>Performance Management</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('hrd-performance-review-index') }}">Performance Review</a></li>
        <li class="breadcrumb-item active"><span class="active">Buat Siklus Evaluasi</span></li>
    </ol>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Form Siklus Evaluasi Kinerja (Performance Appraisal Cycle)</h5>
    </div>
    <div class="card-body">
        <form action="{{ url('hrd-performance-review-cycle-store') }}" method="POST">
            @csrf
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label font-weight-bold">Kode Siklus <span class="text-danger">*</span></label>
                    <input type="text" name="cycle_code" class="form-control text-uppercase" placeholder="Contoh: REV-2026-YE" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label font-weight-bold">Judul Siklus Evaluasi <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" placeholder="Contoh: End of Year Appraisal 2026" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label font-weight-bold">Tautkan ke Periode KPI <span class="text-danger">*</span></label>
                    <select name="period_id" class="form-select form-control" required>
                        <option value="">-- Pilih Periode KPI --</option>
                        @foreach($periods as $p)
                            <option value="{{ $p->id }}" {{ $p->status === 'ACTIVE' ? 'selected' : '' }}>
                                {{ $p->name }} ({{ $p->period_code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label font-weight-bold">Jenis Evaluasi</label>
                    <select name="review_type" class="form-select form-control">
                        <option value="ANNUAL">Tahunan (Annual Review)</option>
                        <option value="MID_YEAR">Tengah Tahun (Mid-Year)</option>
                        <option value="PROBATION">Evaluasi Masa Percobaan (Probation)</option>
                        <option value="PROJECT">Review Khusus Proyek</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label font-weight-bold">Bobot Skor KPI / OKR (%)</label>
                    <input type="number" step="1" name="weight_kpi" class="form-control" value="70" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label font-weight-bold">Bobot Skor Kompetensi (%)</label>
                    <input type="number" step="1" name="weight_competency" class="form-control" value="30" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label font-weight-bold">Komponen Penilai (360)</label>
                    <div class="mt-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="enable_self_review" id="enable_self_review" checked>
                            <label class="form-check-label" for="enable_self_review">Self</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="enable_manager_review" id="enable_manager_review" checked>
                            <label class="form-check-label" for="enable_manager_review">Manager</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="enable_peer_review" id="enable_peer_review">
                            <label class="form-check-label" for="enable_peer_review">Peer (Rekan)</label>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label font-weight-bold">Tanggal Mulai Evaluasi <span class="text-danger">*</span></label>
                    <input type="date" name="start_date" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label font-weight-bold">Batas Akhir Evaluasi (Deadline) <span class="text-danger">*</span></label>
                    <input type="date" name="end_date" class="form-control" required>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Deskripsi / Petunjuk Pengisian Bagi Karyawan & Atasan</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Tuliskan petunjuk kriteria penilaian..."></textarea>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <a href="{{ url('hrd-performance-review-index') }}" class="btn btn-secondary me-2">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i class="feather-check-circle me-1"></i> Simpan & Aktifkan Siklus
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

