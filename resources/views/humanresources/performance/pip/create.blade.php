@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<div class="main-content-header mb-3">
    <h1>Performance Management</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('hrd-performance-pip-index') }}">PIP Plan</a></li>
        <li class="breadcrumb-item active"><span class="active">Buat PIP Manual</span></li>
    </ol>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Form Pembuatan Rencana Perbaikan Kinerja (PIP) Manual</h5>
    </div>
    <div class="card-body">
        <form action="{{ url('hrd-performance-pip-store') }}" method="POST">
            @csrf
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label font-weight-bold">Karyawan <span class="text-danger">*</span></label>
                    <select name="user_id" class="form-select form-control" required>
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->Name }} ({{ $u->Emp_ID ?? '-' }}) - {{ $u->Department ?? '-' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label font-weight-bold">Mentor / Atasan Pembimbing</label>
                    <select name="mentor_id" class="form-select form-control">
                        <option value="">-- Default: Pembuat / HCGA --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->Name }} ({{ $u->Position ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label font-weight-bold">Tanggal Mulai <span class="text-danger">*</span></label>
                    <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label font-weight-bold">Durasi (Hari) <span class="text-danger">*</span></label>
                    <input type="number" name="duration_days" class="form-control" value="60" min="7" max="180" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label font-weight-bold">Ringkasan Masalah Kinerja <span class="text-danger">*</span></label>
                    <textarea name="performance_issue_summary" class="form-control" rows="3" placeholder="Jelaskan indikator/target yang belum memenuhi standar perusahaan..." required></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label font-weight-bold">Target Hasil yang Diharapkan <span class="text-danger">*</span></label>
                    <textarea name="expected_outcomes" class="form-control" rows="3" placeholder="Jelaskan standar kelulusan PIP..." required></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Rencana Aksi Awal (Opsional)</label>
                    <input type="text" name="action_title" class="form-control" placeholder="Contoh: Coaching mingguan penyelesaian target KPI">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Standar Keberhasilan Aksi Awal</label>
                    <input type="text" name="expected_standard" class="form-control" placeholder="Contoh: Pencapaian >= 80% per minggu">
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <a href="{{ url('hrd-performance-pip-index') }}" class="btn btn-secondary me-2">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i class="feather-save me-1"></i> Terbitkan PIP Plan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
