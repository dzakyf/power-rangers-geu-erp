@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<div class="main-content-header">
    <h1>Summary Hasil Training</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('hrd-training-summary-index') }}">Summary</a></li>
        <li class="breadcrumb-item active"><span class="active">{{ $training->Task_No }}</span></li>
    </ol>
</div>

@include('humanresources.training._nav', ['activeTab' => 'summary'])

<div class="row mb-3">
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body py-3">
                <small class="text-muted">Total Peserta</small>
                <h4 class="mb-0">{{ $total_participants }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center border-success">
            <div class="card-body py-3">
                <small class="text-muted">Lulus Evaluasi</small>
                <h4 class="mb-0 text-success">{{ $passed_count }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center border-danger">
            <div class="card-body py-3">
                <small class="text-muted">Tidak Lulus</small>
                <h4 class="mb-0 text-danger">{{ $failed_count }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body py-3">
                <small class="text-muted">Rata-rata Nilai</small>
                <h4 class="mb-0">{{ number_format($avg_final_score, 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body py-3">
                <small class="text-muted">Rating Pemateri</small>
                <h4 class="mb-0">{{ number_format($trainer_avg_rating, 2) }} / 5</h4>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body py-3">
                <small class="text-muted">Rating Materi</small>
                <h4 class="mb-0">{{ number_format($material_avg_rating, 2) }} / 5</h4>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">{{ $training->Training_Name }} ({{ $training->Task_No }})</h5>
            <small class="text-muted">KKM: {{ number_format($training->passing_grade ?? 75, 2) }} | Responden Feedback: {{ $feedback_respondents }}/{{ $total_participants }}</small>
        </div>
        <div>
            @if($passed_count > 0)
                <button type="button" class="btn btn-success btn-sm" onclick="issueCertificates({{ $training->ID }})">Terbitkan Sertifikat Digital (Lulus)</button>
            @endif
            @if($failed_count > 0)
                <a href="{{ url('hrd-training-coaching-index') }}?training_id={{ $training->ID }}" class="btn btn-warning btn-sm">Tindak Lanjut Coaching / Refreshment</a>
            @endif
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama Karyawan</th>
                        <th>Departemen</th>
                        <th>Pre-Test</th>
                        <th>Post-Test</th>
                        <th>Praktik</th>
                        <th>Skor Akhir</th>
                        <th>Hasil Evaluasi</th>
                        <th>Tindak Lanjut / Output</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($training->participants as $idx => $p)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>{{ $p->employee_name }} ({{ $p->employee_username }})</td>
                            <td>{{ $p->department ?? '-' }}</td>
                            <td>{{ $p->pre_test_score !== null ? number_format($p->pre_test_score, 2) : '-' }}</td>
                            <td>{{ $p->post_test_score !== null ? number_format($p->post_test_score, 2) : '-' }}</td>
                            <td>{{ $p->practice_score !== null ? number_format($p->practice_score, 2) : '-' }}</td>
                            <td><strong>{{ $p->final_score !== null ? number_format($p->final_score, 2) : '-' }}</strong></td>
                            <td>
                                @if($p->is_passed === true)
                                    <span class="badge bg-success text-white">LULUS</span>
                                @elseif($p->is_passed === false)
                                    <span class="badge bg-danger text-white">TIDAK LULUS</span>
                                @else
                                    <span class="badge bg-secondary text-white">BELUM DINILAI</span>
                                @endif
                            </td>
                            <td>
                                @if($p->is_passed === true)
                                    @if($p->certificate)
                                        <a href="{{ url('hrd-training-certificate-download/' . $p->certificate->id) }}" target="_blank" class="btn btn-sm btn-outline-success">
                                            Download Sertifikat ({{ $p->certificate->certificate_no }})
                                        </a>
                                    @else
                                        <span class="text-muted">Siap diterbitkan</span>
                                    @endif
                                @elseif($p->is_passed === false)
                                    <a href="{{ url('hrd-training-coaching-form') }}?participant_id={{ $p->id }}" class="btn btn-sm btn-outline-danger">
                                        Catat Coaching / Refreshment ({{ $p->coachings->count() }})
                                    </a>
                                @else
                                    <a href="{{ url('hrd-training-evaluation-score') }}?training_id={{ $training->ID }}" class="btn btn-sm btn-outline-primary">Input Nilai</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted">Belum ada peserta.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function issueCertificates(trainingId) {
        if (!confirm('Terbitkan Sertifikat Digital untuk semua peserta yang lulus evaluasi?')) return;
        $.post('{{ url("hrd-training-summary-issue-certificates") }}', {
            _token: '{{ csrf_token() }}',
            training_id: trainingId
        }, function(res) {
            alert(res.message);
            window.location.reload();
        }).fail(function(xhr) {
            alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menerbitkan sertifikat.');
        });
    }
</script>
@endsection
