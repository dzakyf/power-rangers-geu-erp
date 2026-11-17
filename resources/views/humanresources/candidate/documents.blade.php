@extends('layout.mainlayout-candidate')

@section('content-wrapper')
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto">Dokumen Lamaran</h4>
            <span class="text-muted mt-1 tx-13 ms-2 mb-0">/ Unggah & Perbarui Berkas</span>
        </div>
    </div>
</div>

@php
    $uploadedTotal = collect($docStatus)->where('uploaded', true)->count();
    $totalDocs = count($categories);
    $percent = $totalDocs > 0 ? round(($uploadedTotal / $totalDocs) * 100) : 0;
@endphp

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-bold text-dark">Kelengkapan Dokumen</span>
            <span class="badge bg-primary text-white">{{ $uploadedTotal }} / {{ $totalDocs }} Dokumen ({{ $percent }}%)</span>
        </div>
        <div class="progress" style="height: 10px;">
            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percent }}%;" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <small class="text-muted d-block mt-2">
            Format yang diizinkan: <strong>JPG, JPEG, PNG, PDF</strong> (maksimal <strong>5 MB</strong> per file).
        </small>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div id="docAlert" class="alert d-none" role="alert"></div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;" class="text-center">No</th>
                        <th>Jenis Dokumen</th>
                        <th style="width: 160px;" class="text-center">Status</th>
                        <th style="width: 260px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $idx => $cat)
                    <tr>
                        <td class="text-center fw-bold text-muted">{{ $idx + 1 }}</td>
                        <td class="fw-semibold">{{ $labels[$cat] ?? $cat }}</td>
                        <td class="text-center">
                            @if($docStatus[$cat]['uploaded'])
                                <span class="badge bg-success text-white"><i class="fa fa-check me-1"></i>Uploaded</span>
                            @else
                                <span class="badge bg-secondary text-white">Belum Diunggah</span>
                            @endif
                        </td>
                        <td>
                            <form class="upload-form d-inline" data-category="{{ strtolower($cat) }}">
                                @csrf
                                <input type="file" name="file" accept=".jpg,.jpeg,.png,.pdf,image/*,application/pdf" class="file-input d-none">
                                <button type="button" class="btn btn-sm btn-primary upload-btn">
                                    <i class="fa fa-upload me-1"></i> {{ $docStatus[$cat]['uploaded'] ? 'Ganti File' : 'Upload' }}
                                </button>
                            </form>
                            @if($docStatus[$cat]['uploaded'])
                                <a href="{{ asset($docStatus[$cat]['filePath']) }}" class="btn btn-sm btn-outline-success ms-1" target="_blank">
                                    <i class="fa fa-eye me-1"></i> Lihat
                                </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.upload-btn').on('click', function() {
        $(this).siblings('.file-input').click();
    });

    $('.file-input').on('change', function() {
        var form = $(this).closest('.upload-form');
        var category = form.data('category');
        var file = this.files[0];
        if (!file) return;

        var fd = new FormData();
        fd.append('file', file);
        fd.append('category', category);
        fd.append('_token', '{{ csrf_token() }}');

        var btn = form.find('.upload-btn');
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Uploading...');

        $.ajax({
            url: '{{ url("/candidate/document-upload") }}',
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res && res.status === 'ok') {
                    $('#docAlert').removeClass('d-none alert-danger').addClass('alert-success').text(res.message);
                    setTimeout(function() { location.reload(); }, 700);
                } else {
                    $('#docAlert').removeClass('d-none alert-success').addClass('alert-danger').text(res.message || 'Gagal mengunggah dokumen.');
                    btn.prop('disabled', false).html('<i class="fa fa-upload me-1"></i> Upload');
                }
            },
            error: function(xhr) {
                var msg = 'Upload gagal! Pastikan format file JPG/PNG/PDF dan ukuran maksimal 5 MB.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                }
                $('#docAlert').removeClass('d-none alert-success').addClass('alert-danger').text(msg);
                btn.prop('disabled', false).html('<i class="fa fa-upload me-1"></i> Upload');
            }
        });
    });
});
</script>
@endsection
