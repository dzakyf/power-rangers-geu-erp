@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<div class="main-content-header">
    <h1>Form Coaching &amp; Training Refreshment</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('hrd-training-coaching-index') }}">Coaching</a></li>
        <li class="breadcrumb-item active"><span class="active">Form Tindak Lanjut</span></li>
    </ol>
</div>

@include('humanresources.training._nav', ['activeTab' => 'coaching'])

<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0">{{ $participant->employee_name }} — {{ $participant->training->Training_Name ?? '' }}</h5>
        <small class="text-muted">Skor Akhir: <strong class="text-danger">{{ number_format((float) $participant->final_score, 2) }}</strong> (KKM: {{ number_format((float) ($participant->training->passing_grade ?? 75), 2) }})</small>
    </div>
    <div class="card-body">
        <form id="formCoaching">
            @csrf
            <input type="hidden" name="participant_id" value="{{ $participant->id }}">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Mentor / Coach *</label>
                    <select name="mentor_username" class="form-control" onchange="$('#mentor_name').val($(this).find('option:selected').data('name') || '')" required>
                        <option value="">-- Pilih Mentor --</option>
                        @foreach($mentors as $m)
                            <option value="{{ $m->username }}" data-name="{{ $m->name }}">{{ $m->name }} ({{ $m->Department }})</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="mentor_name" id="mentor_name">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tanggal Coaching *</label>
                    <input type="date" name="coaching_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tindak Lanjut *</label>
                    <select name="follow_up_type" id="follow_up_type" class="form-control" onchange="toggleRefreshmentDates()">
                        <option value="COACHING_ONLY">Coaching Saja</option>
                        <option value="REFRESHMENT">Coaching + Jadwalkan Training Refreshment (Batch Ulang)</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Catatan Sesi Coaching *</label>
                    <textarea name="notes" class="form-control" rows="3" required placeholder="Poin evaluasi & pembahasan selama coaching..."></textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Action Plan</label>
                    <textarea name="action_plan" class="form-control" rows="3" placeholder="Target perbaikan yang disepakati..."></textarea>
                </div>
            </div>

            <div class="row border rounded p-3 mb-3 bg-light" id="refreshmentDatesBox" style="display:none;">
                <div class="col-12 mb-2"><strong>Jadwal Batch Training Refreshment (Otomatis Didaftarkan)</strong></div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal Mulai Refreshment</label>
                    <input type="date" name="refreshment_start_date" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal Selesai Refreshment</label>
                    <input type="date" name="refreshment_end_date" class="form-control">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Coaching / Refreshment</button>
            <a href="{{ url('hrd-training-coaching-index') }}" class="btn btn-outline-secondary">Kembali</a>
        </form>
    </div>
</div>

@if($participant->coachings->count() > 0)
<div class="card">
    <div class="card-header"><h6 class="mb-0">Riwayat Coaching</h6></div>
    <div class="card-body">
        <table class="table table-bordered table-sm">
            <thead class="table-light">
                <tr>
                    <th>Tanggal</th>
                    <th>Mentor</th>
                    <th>Tindak Lanjut</th>
                    <th>Catatan</th>
                    <th>Batch Refreshment</th>
                </tr>
            </thead>
            <tbody>
                @foreach($participant->coachings as $c)
                    <tr>
                        <td>{{ $c->coaching_date ? $c->coaching_date->format('d M Y') : '-' }}</td>
                        <td>{{ $c->mentor_name }}</td>
                        <td><span class="badge bg-info text-white">{{ $c->follow_up_type }}</span></td>
                        <td>{{ $c->notes }}</td>
                        <td>
                            @if($c->refreshment_training_id)
                                <a href="{{ url('hrd-training-index') }}" class="text-primary">Batch ID #{{ $c->refreshment_training_id }}</a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<script>
    function toggleRefreshmentDates() {
        if ($('#follow_up_type').val() === 'REFRESHMENT') {
            $('#refreshmentDatesBox').show();
        } else {
            $('#refreshmentDatesBox').hide();
        }
    }

    $('#formCoaching').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '{{ url("hrd-training-coaching-store") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                alert(res.message);
                window.location.href = '{{ url("hrd-training-coaching-index") }}';
            },
            error: function(xhr) {
                alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menyimpan coaching.');
            }
        });
    });
</script>
@endsection
