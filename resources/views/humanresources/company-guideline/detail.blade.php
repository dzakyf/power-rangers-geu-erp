<div class="guideline-detail-card">
    <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
        <div class="me-3">
            <span class="badge bg-{{ $item->badge_color ?? 'primary' }} p-3 rounded-circle shadow-sm">
                <i class="{{ $item->icon ?? 'fa fa-bookmark' }} fa-lg text-white"></i>
            </span>
        </div>
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-secondary">{{ $item->type }}</span>
                @if($item->code)
                    <span class="badge bg-light text-dark border">{{ $item->code }}</span>
                @endif
                <span class="badge bg-{{ $item->is_active ? 'success' : 'danger' }}">
                    {{ $item->is_active ? 'ACTIVE' : 'INACTIVE' }}
                </span>
            </div>
            <h5 class="fw-bold mb-0 text-dark">{{ $item->title }}</h5>
        </div>
    </div>

    @if($item->summary)
        <div class="alert alert-light border p-2 mb-3">
            <small class="fw-bold text-muted d-block mb-1">Ringkasan:</small>
            <p class="mb-0 text-dark fst-italic">{{ $item->summary }}</p>
        </div>
    @endif

    <div class="content-box bg-white p-3 rounded border mb-3">
        <h6 class="fw-bold text-muted small mb-2 text-uppercase letter-spacing-1">Uraian Konten</h6>
        <div class="guideline-content" style="line-height: 1.7; color: #334155;">
            {!! $item->content !!}
        </div>
    </div>

    @if($item->attachment_file)
        <div class="p-3 bg-light rounded border mb-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <i class="fa fa-file-pdf text-danger fa-2x me-3"></i>
                <div>
                    <div class="fw-bold small text-dark">{{ $item->attachment_name ?? 'Dokumen Pendukung' }}</div>
                    <small class="text-muted">Klik untuk mengunduh / membaca file PDF peraturan lengkap.</small>
                </div>
            </div>
            <a href="{{ asset($item->attachment_file) }}" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                <i class="fa fa-download me-1"></i> Buka / Unduh
            </a>
        </div>
    @endif

    <div class="row g-2 text-muted small pt-2 border-top">
        <div class="col-6">
            <span>Dibuat: <b>{{ $item->created_by ?? '-' }}</b> ({{ $item->created_date ? date('d M Y H:i', strtotime($item->created_date)) : '-' }})</span>
        </div>
        <div class="col-6 text-end">
            <span>Urutan Tampil: <b>#{{ $item->sort_order }}</b></span>
        </div>
    </div>
</div>

