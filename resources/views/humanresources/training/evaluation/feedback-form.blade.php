@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<div class="main-content-header">
    <h1>Form Evaluasi Pemateri &amp; Materi</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('hrd-training-evaluation-index') }}">Evaluasi</a></li>
        <li class="breadcrumb-item active"><span class="active">Pemateri &amp; Materi</span></li>
    </ol>
</div>

@include('humanresources.training._nav', ['activeTab' => 'evaluation'])

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">{{ $training->Training_Name }} — Pemateri: {{ $training->trainer_name ?: '-' }}</h5>
    </div>
    <div class="card-body">
        <form id="formFeedbackEval">
            @csrf
            <input type="hidden" name="training_id" value="{{ $training->ID }}">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Pilih Peserta Pengisi Evaluasi *</label>
                    <select name="participant_id" class="form-control" required>
                        <option value="">-- Pilih Peserta --</option>
                        @foreach($training->participants as $p)
                            <option value="{{ $p->id }}" {{ ($selectedParticipant && $selectedParticipant->id == $p->id) ? 'selected' : '' }}>
                                {{ $p->employee_name }} ({{ $p->employee_username }}) {{ $p->feedback_submitted_at ? '✓ Sudah Mengisi' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="card border">
                        <div class="card-header bg-light"><strong>A. Evaluasi Pemateri (Skala 1 - 5)</strong></div>
                        <div class="card-body">
                            @foreach($trainerAspects as $aspect)
                                <div class="mb-2">
                                    <label class="form-label mb-1">{{ $aspect }}</label>
                                    <select name="trainer_ratings[{{ $aspect }}]" class="form-control form-control-sm" required>
                                        <option value="5">5 - Sangat Baik</option>
                                        <option value="4" selected>4 - Baik</option>
                                        <option value="3">3 - Cukup</option>
                                        <option value="2">2 - Kurang</option>
                                        <option value="1">1 - Sangat Kurang</option>
                                    </select>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card border">
                        <div class="card-header bg-light"><strong>B. Evaluasi Materi (Skala 1 - 5)</strong></div>
                        <div class="card-body">
                            @foreach($materialAspects as $aspect)
                                <div class="mb-2">
                                    <label class="form-label mb-1">{{ $aspect }}</label>
                                    <select name="material_ratings[{{ $aspect }}]" class="form-control form-control-sm" required>
                                        <option value="5">5 - Sangat Baik</option>
                                        <option value="4" selected>4 - Baik</option>
                                        <option value="3">3 - Cukup</option>
                                        <option value="2">2 - Kurang</option>
                                        <option value="1">1 - Sangat Kurang</option>
                                    </select>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Saran &amp; Masukan Tambahan</label>
                <textarea name="comments" class="form-control" rows="3" placeholder="Tuliskan masukan untuk pemateri maupun materi..."></textarea>
            </div>

            <button type="submit" class="btn btn-success">Submit Evaluasi</button>
        </form>
    </div>
</div>

<script>
    $('#formFeedbackEval').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '{{ url("hrd-training-evaluation-feedback-save") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                alert(res.message);
                window.location.href = '{{ url("hrd-training-summary-detail") }}?training_id={{ $training->ID }}';
            },
            error: function(xhr) {
                alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menyimpan feedback.');
            }
        });
    });
</script>
@endsection
