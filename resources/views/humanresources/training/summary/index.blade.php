@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<div class="main-content-header">
    <h1>Summary Training &amp; Sertifikat Digital</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">HRD</a></li>
        <li class="breadcrumb-item active"><span class="active">Summary Training</span></li>
    </ol>
</div>

@include('humanresources.training._nav', ['activeTab' => 'summary'])

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Laporan Hasil Pelatihan</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Task No</th>
                        <th>Nama Training</th>
                        <th>Batch</th>
                        <th>Tanggal</th>
                        <th>Jml Peserta</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($trainings as $t)
                        <tr>
                            <td>{{ $t->Task_No }}</td>
                            <td>{{ $t->Training_Name }}</td>
                            <td><span class="badge bg-secondary text-white">{{ $t->batch_type ?? 'REGULAR' }}</span></td>
                            <td>{{ $t->Start_Date }} s/d {{ $t->End_Date }}</td>
                            <td>{{ $t->participants_count }}</td>
                            <td><span class="badge bg-info text-white">{{ $t->Status }}</span></td>
                            <td>
                                <a href="{{ url('hrd-training-summary-detail') }}?training_id={{ $t->ID }}" class="btn btn-sm btn-primary">Lihat Summary &amp; Terbitkan Sertifikat</a>
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
