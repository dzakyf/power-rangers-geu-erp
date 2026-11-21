@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<div class="main-content-header">
    <h1>Penilaian Hasil Training Karyawan</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('hrd-training-evaluation-index') }}">Evaluasi</a></li>
        <li class="breadcrumb-item active"><span class="active">Penilaian Karyawan</span></li>
    </ol>
</div>

@include('humanresources.training._nav', ['activeTab' => 'evaluation'])

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">{{ $training->Training_Name }} ({{ $training->Task_No }})</h5>
            <small class="text-muted">Passing Grade (KKM): <strong>{{ number_format($training->passing_grade ?? 75, 2) }}</strong> | Rumus Skor Akhir (Auto): <strong>60% Post-Test + 40% Praktik</strong> (100% jika hanya salah satu terisi)</small>
        </div>
        <a href="{{ url('hrd-training-summary-detail') }}?training_id={{ $training->ID }}" class="btn btn-success btn-sm">Lihat Summary Training &rarr;</a>
    </div>
    <div class="card-body">
        <form id="formScoreEval">
            @csrf
            <input type="hidden" name="training_id" value="{{ $training->ID }}">
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Peserta</th>
                            <th>Kehadiran</th>
                            <th style="width:100px;">Pre-Test</th>
                            <th style="width:100px;">Post-Test</th>
                            <th style="width:100px;">Praktik</th>
                            <th style="width:100px;">Skor Akhir</th>
                            <th>Status</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($training->participants as $idx => $p)
                            <tr class="score-row">
                                <td>{{ $idx + 1 }}</td>
                                <td>
                                    <strong>{{ $p->employee_name }}</strong><br>
                                    <small class="text-muted">{{ $p->employee_username }} | {{ $p->department ?? '-' }}</small>
                                    <input type="hidden" name="scores[{{ $idx }}][participant_id]" value="{{ $p->id }}">
                                </td>
                                <td>
                                    <select name="scores[{{ $idx }}][attendance_status]" class="form-control form-control-sm score-attendance">
                                        @foreach(['REGISTERED', 'ATTENDED', 'ABSENT'] as $att)
                                            <option value="{{ $att }}" {{ $p->attendance_status == $att ? 'selected' : '' }}>{{ $att }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" step="0.01" min="0" max="100" name="scores[{{ $idx }}][pre_test_score]" class="form-control form-control-sm score-pre" value="{{ $p->pre_test_score }}"></td>
                                <td><input type="number" step="0.01" min="0" max="100" name="scores[{{ $idx }}][post_test_score]" class="form-control form-control-sm score-post" value="{{ $p->post_test_score }}"></td>
                                <td><input type="number" step="0.01" min="0" max="100" name="scores[{{ $idx }}][practice_score]" class="form-control form-control-sm score-practice" value="{{ $p->practice_score }}"></td>
                                <td><input type="number" step="0.01" min="0" max="100" name="scores[{{ $idx }}][final_score]" class="form-control form-control-sm bg-light score-final" value="{{ $p->final_score !== null ? number_format($p->final_score, 2, '.', '') : '' }}" placeholder="Auto" readonly tabindex="-1"></td>
                                <td class="score-status-cell">
                                    @if($p->is_passed === true)
                                        <span class="badge bg-success text-white">LULUS</span>
                                    @elseif($p->is_passed === false)
                                        <span class="badge bg-danger text-white">TIDAK LULUS</span>
                                    @else
                                        <span class="badge bg-secondary text-white">PENDING</span>
                                    @endif
                                </td>
                                <td><input type="text" name="scores[{{ $idx }}][evaluation_notes]" class="form-control form-control-sm" value="{{ $p->evaluation_notes }}"></td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted">Belum ada peserta terdaftar pada jadwal ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($training->participants->count() > 0)
                <button type="submit" class="btn btn-primary mt-2">Simpan Penilaian &amp; Hitung Kelulusan</button>
            @endif
        </form>
    </div>
</div>

<script>
    var passingGrade = {{ (float) ($training->passing_grade ?: 75) }};

    function parseScoreInput(val) {
        var str = $.trim(String(val === null || val === undefined ? '' : val));
        if (str === '') return null;
        var num = parseFloat(str);
        return isNaN(num) ? null : num;
    }

    function recalculateScoreRow($row) {
        var attendance = String($row.find('.score-attendance').val() || '').toUpperCase();
        var post = parseScoreInput($row.find('.score-post').val());
        var practice = parseScoreInput($row.find('.score-practice').val());

        var finalScore = null;
        if (post !== null && practice !== null) {
            finalScore = Math.round(((post * 0.6) + (practice * 0.4)) * 100) / 100;
        } else if (post !== null) {
            finalScore = Math.round(post * 100) / 100;
        } else if (practice !== null) {
            finalScore = Math.round(practice * 100) / 100;
        }

        $row.find('.score-final').val(finalScore !== null ? finalScore.toFixed(2) : '');

        var $statusCell = $row.find('.score-status-cell');
        if (attendance === 'ABSENT') {
            $statusCell.html('<span class="badge bg-danger text-white">TIDAK LULUS</span>');
        } else if (finalScore !== null) {
            if (finalScore >= passingGrade) {
                $statusCell.html('<span class="badge bg-success text-white">LULUS</span>');
            } else {
                $statusCell.html('<span class="badge bg-danger text-white">TIDAK LULUS</span>');
            }
        } else {
            $statusCell.html('<span class="badge bg-secondary text-white">PENDING</span>');
        }
    }

    $('#formScoreEval').on('input change', '.score-post, .score-practice, .score-attendance', function() {
        recalculateScoreRow($(this).closest('tr.score-row'));
    });

    $(function() {
        $('#formScoreEval tr.score-row').each(function() {
            var $row = $(this);
            if (parseScoreInput($row.find('.score-post').val()) !== null || parseScoreInput($row.find('.score-practice').val()) !== null) {
                recalculateScoreRow($row);
            }
        });
    });

    $('#formScoreEval').on('submit', function(e) {
        e.preventDefault();
        $('#formScoreEval tr.score-row').each(function() {
            recalculateScoreRow($(this));
        });
        $.ajax({
            url: '{{ url("hrd-training-evaluation-score-save") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                alert(res.message);
                window.location.reload();
            },
            error: function(xhr) {
                alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menyimpan penilaian.');
            }
        });
    });
</script>
@endsection
