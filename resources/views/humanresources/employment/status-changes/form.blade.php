@extends('layout.mainlayout')

@section('content-wrapper')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>{{ $statusChange->exists ? 'Edit' : 'Buat' }} Form Perubahan Status</h3>
        <a href="{{ route('status-changes.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><strong>Periksa kembali input berikut:</strong>
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ $statusChange->exists ? route('status-changes.update', $statusChange->id) : route('status-changes.store') }}">
        @csrf
        @if($statusChange->exists) @method('PUT') @endif

        <div class="card mb-3">
            <div class="card-header font-weight-bold">Identitas Karyawan</div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Nama *</label>
                        <input name="employee_name" class="form-control" required value="{{ old('employee_name', $statusChange->employee_name) }}">
                    </div>
                    <div class="form-group col-md-3">
                        <label>NIK *</label>
                        <input name="nik" class="form-control" required value="{{ old('nik', $statusChange->nik) }}">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Tanggal Join</label>
                        <input type="date" name="join_date" class="form-control" value="{{ old('join_date', optional($statusChange->join_date)->format('Y-m-d')) }}">
                    </div>
                </div>
                @if($statusChange->form_number)
                    <div class="text-muted small">Nomor Form: {{ $statusChange->form_number }} (dibuat otomatis)</div>
                @else
                    <div class="text-muted small">Nomor form akan dibuat otomatis setelah data disimpan.</div>
                @endif
            </div>
        </div>

        @foreach(['current' => 'Saat Ini', 'proposed' => 'Usulan'] as $prefix => $heading)
        <div class="card mb-3">
            <div class="card-header font-weight-bold">{{ $heading }}</div>
            <div class="card-body">
                <div class="form-row">
                    @foreach(['position' => 'Jabatan', 'division' => 'Divisi', 'department' => 'Departemen', 'grade' => 'Grade', 'work_location' => 'Lokasi Kerja', 'employment_status' => 'Status Kekaryawanan'] as $field => $label)
                    <div class="form-group col-md-4">
                        <label>{{ $label }}</label>
                        <input class="form-control" name="{{ $prefix }}_{{ $field }}" value="{{ old($prefix.'_'.$field, $statusChange->{$prefix.'_'.$field}) }}">
                    </div>
                    @endforeach
                    <div class="form-group col-md-4">
                        <label>Status Perkawinan & Tanggungan</label>
                        <select class="form-control" name="{{ $prefix }}_marital_dependents">
                            <option value="">-- Pilih --</option>
                            @foreach($maritalOptions as $value => $label)
                                <option value="{{ $value }}" {{ old($prefix.'_marital_dependents', $statusChange->{$prefix.'_marital_dependents'}) == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Salary (Rp)</label>
                        <input type="number" min="0" step="0.01" class="form-control" name="{{ $prefix }}_salary" value="{{ old($prefix.'_salary', $statusChange->{$prefix.'_salary'} === null ? '0' : $statusChange->{$prefix.'_salary'}) }}">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Benefit</label>
                        <textarea class="form-control" rows="2" name="{{ $prefix }}_benefit">{{ old($prefix.'_benefit', $statusChange->{$prefix.'_benefit'}) }}</textarea>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Deskripsi</label>
                        <textarea class="form-control" rows="2" name="{{ $prefix }}_description">{{ old($prefix.'_description', $statusChange->{$prefix.'_description'}) }}</textarea>
                    </div>
                </div>
            </div>
        </div>
        @endforeach

        <div class="card mb-3">
            <div class="card-header font-weight-bold">Tanggal Efektif & Persetujuan</div>
            <div class="card-body">
                <div class="form-group">
                    <label>Perubahan status ini berlaku sejak tanggal</label>
                    <input type="date" name="effective_date" class="form-control" value="{{ old('effective_date', optional($statusChange->effective_date)->format('Y-m-d')) }}">
                </div>
                <div class="form-row">
                    @foreach(['proposed_by' => 'Diusulkan oleh', 'origin_department_head' => 'Dept. Head Departemen Asal', 'destination_department_head' => 'Dept. Head Departemen Tujuan', 'hcga_verifier' => 'Diverifikasi — Senior Manager HCGA', 'director_approver' => 'Mengetahui — Direktur'] as $field => $label)
                    <div class="form-group col-md-6">
                        <label>{{ $label }}</label>
                        <input class="form-control" name="{{ $field }}" value="{{ old($field, $statusChange->{$field}) }}">
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <button class="btn btn-primary">{{ $statusChange->exists ? 'Simpan Perubahan' : 'Simpan Form' }}</button>
        <a href="{{ route('status-changes.index') }}" class="btn btn-outline-secondary">Batal</a>
    </form>
</div>
@endsection