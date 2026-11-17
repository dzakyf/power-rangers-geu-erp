@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<div class="main-content-header">
    <h1>Coaching &amp; Training Refreshment</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">HRD</a></li>
        <li class="breadcrumb-item active"><span class="active">Coaching &amp; Refreshment</span></li>
    </ol>
</div>

@include('humanresources.training._nav', ['activeTab' => 'coaching'])

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Peserta Tidak Lulus Evaluasi (Perlu Coaching / Training Refreshment)</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm">
                <thead class="table-light">
                    <tr>
                        <th>Nama Karyawan</th>
                        <th>Departemen</th>
                        <th>Training</th>
                        <th>Skor Akhir / KKM</th>
                        <th>Riwayat Coaching</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($failedParticipants as $p)
                        <tr>
                            <td>{{ $p->employee_name }} ({{ $p->employee_username }})</td>
                            <td>{{ $p->department ?? '-' }}</td>
                            <td>{{ $p->training->Training_Name ?? '-' }} ({{ $p->training->Task_No ?? '' }})</td>
                            <td>
                                <span class="text-danger font-weight-bold">{{ number_format((float) $p->final_score, 2) }}</span>
                                / {{ number_format((float) ($p->training->passing_grade ?? 75), 2) }}
                            </td>
                            <td>{{ $p->coachings->count() }} sesi</td>
                            <td>
                                <a href="{{ url('hrd-training-coaching-form') }}?participant_id={{ $p->id }}" class="btn btn-sm btn-warning">
                                    Catat Coaching / Jadwalkan Refreshment
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Tidak ada peserta yang tidak lulus evaluasi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
