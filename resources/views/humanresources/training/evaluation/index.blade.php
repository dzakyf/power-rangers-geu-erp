@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<div class="main-content-header">
    <h1>Evaluasi Training</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">HRD</a></li>
        <li class="breadcrumb-item active"><span class="active">Evaluasi Training</span></li>
    </ol>
</div>

@include('humanresources.training._nav', ['activeTab' => 'evaluation'])

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Pilih Jadwal Training untuk Evaluasi</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Task No</th>
                        <th>Nama Training</th>
                        <th>Tanggal</th>
                        <th>KKM</th>
                        <th>Peserta</th>
                        <th>Status</th>
                        <th>Aksi Evaluasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($trainings as $t)
                        <tr>
                            <td>{{ $t->Task_No }}</td>
                            <td>{{ $t->Training_Name }}</td>
                            <td>{{ $t->Start_Date }} s/d {{ $t->End_Date }}</td>
                            <td>{{ number_format($t->passing_grade ?? 75, 2) }}</td>
                            <td>{{ $t->participants_count }}</td>
                            <td><span class="badge bg-info text-white">{{ $t->Status }}</span></td>
                            <td>
                                <a href="{{ url('hrd-training-evaluation-score') }}?training_id={{ $t->ID }}" class="btn btn-sm btn-primary">Penilaian Hasil Karyawan</a>
                                <a href="{{ url('hrd-training-evaluation-feedback') }}?training_id={{ $t->ID }}" class="btn btn-sm btn-outline-success">Evaluasi Pemateri &amp; Materi</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">Belum ada jadwal training.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
