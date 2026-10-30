@extends('layout.mainlayout')

@section('content-wrapper')
<div class="container py-4">
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div><h3>Detail Form Perubahan Status</h3><div class="text-muted">{{ $statusChange->form_number }}</div></div>
        <div>
            <a href="{{ route('status-changes.index') }}" class="btn btn-outline-secondary">Daftar</a>
            <a href="{{ route('status-changes.edit', $statusChange->id) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('status-changes.print', $statusChange->id) }}" target="_blank" class="btn btn-primary">Printout</a>
        </div>
    </div>
    <div class="card"><div class="card-body">
        <h5>Identitas</h5>
        <dl class="row">
            <dt class="col-sm-3">Nama</dt><dd class="col-sm-9">{{ $statusChange->employee_name }}</dd>
            <dt class="col-sm-3">NIK</dt><dd class="col-sm-9">{{ $statusChange->nik }}</dd>
            <dt class="col-sm-3">Tanggal Join</dt><dd class="col-sm-9">{{ $statusChange->join_date ? $statusChange->join_date->format('d/m/Y') : '-' }}</dd>
            <dt class="col-sm-3">Tanggal Efektif</dt><dd class="col-sm-9">{{ $statusChange->effective_date ? $statusChange->effective_date->format('d/m/Y') : '-' }}</dd>
        </dl>
        <div class="table-responsive"><table class="table table-bordered">
            <thead class="thead-light"><tr><th>Item</th><th>Saat Ini</th><th>Usulan</th></tr></thead><tbody>
            @foreach(['position'=>'Jabatan','division'=>'Divisi','department'=>'Departemen','grade'=>'Grade','work_location'=>'Lokasi kerja','employment_status'=>'Status kekaryawanan','marital_dependents'=>'Status perkawinan/tanggungan','salary'=>'Salary','benefit'=>'Benefit','description'=>'Deskripsi'] as $field => $label)
                <tr><th>{{ $label }}</th><td>{{ $statusChange->{'current_'.$field} ?: '-' }}</td><td>{{ $statusChange->{'proposed_'.$field} ?: '-' }}</td></tr>
            @endforeach
            </tbody></table></div>
        <h5 class="mt-4">Persetujuan</h5>
        <div class="table-responsive"><table class="table table-bordered"><tr><th>Diusulkan oleh</th><th>Dept. Head Asal</th><th>Dept. Head Tujuan</th><th>Diverifikasi</th><th>Mengetahui</th></tr>
        <tr><td>{{ $statusChange->proposed_by ?: '-' }}</td><td>{{ $statusChange->origin_department_head ?: '-' }}</td><td>{{ $statusChange->destination_department_head ?: '-' }}</td><td>{{ $statusChange->hcga_verifier ?: '-' }}</td><td>{{ $statusChange->director_approver ?: '-' }}</td></tr></table></div>
    </div></div>
</div>
@endsection