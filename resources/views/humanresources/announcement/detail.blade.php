<div class="announcement-detail-view">
    @if($item->thumbnail)
        <div class="mb-3 text-center">
            <img src="{{ asset($item->thumbnail) }}" class="img-fluid rounded border shadow-sm" style="max-height: 280px; width: 100%; object-fit: cover;">
        </div>
    @endif

    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
        <span class="badge bg-primary text-white">{{ $item->category ?? 'ANNOUNCEMENT' }}</span>
        @if($item->is_pinned)
            <span class="badge bg-warning text-dark"><i class="fa fa-thumbtack me-1"></i>Pinned</span>
        @endif
        @if($item->is_final)
            <span class="badge bg-success text-white">Published</span>
        @else
            <span class="badge bg-secondary text-white">Draft</span>
        @endif
        <span class="badge bg-light text-muted border"><i class="fa fa-users me-1"></i>{{ $item->target_audience ?? 'ALL' }}</span>
        <span class="badge bg-light text-muted border ms-auto"><i class="fa fa-eye me-1"></i>{{ $item->view_count ?? 0 }} views</span>
    </div>

    <h4 class="fw-bold text-dark mb-2">{{ $item->title }}</h4>

    <div class="text-muted small mb-3 pb-2 border-bottom d-flex flex-wrap align-items-center gap-3">
        <span><i class="fa fa-user me-1"></i> Dibuat oleh: <strong>{{ $item->created_by }}</strong></span>
        <span><i class="fa fa-calendar-alt me-1"></i> {{ date('d M Y, H:i', strtotime($item->created_date)) }}</span>
        @if($item->final_date)
            <span><i class="fa fa-check-circle text-success me-1"></i> Rilis: {{ date('d M Y, H:i', strtotime($item->final_date)) }}</span>
        @endif
    </div>

    @if($item->summary)
        <div class="alert alert-light border p-3 fst-italic text-secondary mb-3">
            <strong>Ringkasan:</strong> {{ $item->summary }}
        </div>
    @endif

    <div class="announcement-content text-dark mb-4" style="line-height: 1.7; white-space: pre-line;">
        {{ $item->content }}
    </div>

    @if($item->attachment_file)
        <div class="p-3 bg-light rounded border mb-4 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <i class="fa fa-file-pdf fa-2x text-danger"></i>
                <div>
                    <div class="fw-semibold text-dark">{{ $item->attachment_name ?: basename($item->attachment_file) }}</div>
                    <div class="small text-muted">Lampiran Dokumen Pengumuman</div>
                </div>
            </div>
            <a href="{{ asset($item->attachment_file) }}" target="_blank" download class="btn btn-outline-primary btn-sm px-3">
                <i class="fa fa-download me-1"></i> Download
            </a>
        </div>
    @endif

    <hr class="my-3">

    <div class="d-flex justify-content-between align-items-center">
        <div>
            @if($isHcga)
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteAnnouncement({{ $item->id }})">
                    <i class="fa fa-trash me-1"></i> Hapus
                </button>
            @endif
        </div>
        <div class="d-flex gap-2">
            @if($isHcga)
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="openEditModal({{ $item->id }})">
                    <i class="fa fa-edit me-1"></i> Edit Konten
                </button>
            @endif
            <button type="button" class="btn btn-primary btn-sm px-3" data-bs-dismiss="modal">Tutup</button>
        </div>
    </div>
</div>
