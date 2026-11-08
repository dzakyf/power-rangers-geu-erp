<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $item->title }} - Mobile</title>
    <link rel="manifest" href="{{ asset('manifest-employee.json') }}">
    <meta name="theme-color" content="#0f172a">
    <link rel="stylesheet" href="{{ asset('adminor/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            padding-bottom: 75px;
            color: #1e293b;
        }
        .header-detail {
            position: sticky;
            top: 0;
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 12px 16px;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .banner-img {
            width: 100%;
            max-height: 260px;
            object-fit: cover;
        }
        .article-body {
            font-size: 0.95rem;
            line-height: 1.8;
            color: #334155;
            white-space: pre-line;
        }
        .attachment-box {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .category-pill {
            font-size: 0.72rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 20px;
        }
        .category-pill.news { background-color: #eff6ff; color: #2563eb; }
        .category-pill.announcement { background-color: #fef2f2; color: #dc2626; }
        .category-pill.policy { background-color: #f0fdf4; color: #16a34a; }
        .category-pill.event { background-color: #fdf4ff; color: #9333ea; }
    </style>
</head>
<body>

    <!-- Header Navigation -->
    <div class="header-detail shadow-sm">
        <a href="{{ route('announcement.mobile.index') }}" class="btn btn-sm btn-light rounded-circle px-2 py-1">
            <i class="fa fa-arrow-left"></i>
        </a>
        <h6 class="fw-bold mb-0 text-truncate px-2">{{ $item->category ?? 'Announcement' }}</h6>
        <div>
            @if($isHcga)
                <div class="dropdown">
                    <button class="btn btn-sm btn-light rounded-circle px-2 py-1" type="button" data-bs-toggle="dropdown">
                        <i class="fa fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        <li>
                            <a class="dropdown-item py-2" href="{{ route('announcement.mobile.edit', $item->id) }}">
                                <i class="fa fa-edit text-primary me-2"></i> Edit Pengumuman
                            </a>
                        </li>
                        <li>
                            <button class="dropdown-item py-2 text-danger" onclick="deleteItem({{ $item->id }})">
                                <i class="fa fa-trash me-2"></i> Hapus Pengumuman
                            </button>
                        </li>
                    </ul>
                </div>
            @else
                <button class="btn btn-sm btn-light rounded-circle px-2 py-1" onclick="shareArticle()">
                    <i class="fa fa-share-alt"></i>
                </button>
            @endif
        </div>
    </div>

    @if($item->thumbnail)
        <img src="{{ asset($item->thumbnail) }}" alt="{{ $item->title }}" class="banner-img">
    @endif

    <div class="container px-3 py-3">
        <!-- Badges & Meta -->
        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
            <span class="category-pill {{ strtolower($item->category ?? 'announcement') }}">
                {{ $item->category ?? 'ANNOUNCEMENT' }}
            </span>
            @if($item->is_pinned)
                <span class="badge bg-warning text-dark"><i class="fa fa-thumbtack me-1"></i>Pinned</span>
            @endif
            @if(!$item->is_final)
                <span class="badge bg-secondary text-white">Draft</span>
            @endif
            <span class="badge bg-light text-muted border ms-auto small">
                <i class="fa fa-eye me-1"></i>{{ $item->view_count ?? 0 }} views
            </span>
        </div>

        <!-- Title -->
        <h4 class="fw-bold text-dark mb-2" style="line-height: 1.35;">{{ $item->title }}</h4>

        <div class="d-flex align-items-center gap-2 text-muted small mb-3 pb-3 border-bottom">
            <span><i class="fa fa-user-circle me-1"></i>{{ $item->created_by }}</span>
            <span>&bull;</span>
            <span><i class="fa fa-calendar-alt me-1"></i>{{ date('d M Y, H:i', strtotime($item->created_date)) }}</span>
        </div>

        @if($item->summary)
            <div class="p-3 bg-light rounded-3 border-start border-4 border-primary text-secondary fst-italic mb-3 small">
                {{ $item->summary }}
            </div>
        @endif

        <!-- Article Content -->
        <div class="article-body mb-4">
            {{ $item->content }}
        </div>

        <!-- Attachment Download Box -->
        @if($item->attachment_file)
            <div class="attachment-box mb-4 shadow-sm">
                <div class="d-flex align-items-center gap-3 text-truncate">
                    <div class="bg-white p-2 rounded-3 border text-danger">
                        <i class="fa fa-file-alt fa-2x"></i>
                    </div>
                    <div class="text-truncate">
                        <div class="fw-bold text-dark text-truncate" style="font-size: 0.88rem;">
                            {{ $item->attachment_name ?: basename($item->attachment_file) }}
                        </div>
                        <div class="small text-muted">Lampiran Dokumen Resmi</div>
                    </div>
                </div>
                <a href="{{ asset($item->attachment_file) }}" download class="btn btn-primary btn-sm rounded-pill px-3 ms-2">
                    <i class="fa fa-download me-1"></i> Unduh
                </a>
            </div>
        @endif

        <!-- Back to list -->
        <div class="text-center pt-2">
            <a href="{{ route('announcement.mobile.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-4">
                <i class="fa fa-arrow-left me-1"></i> Kembali ke Berita
            </a>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="{{ asset('adminor/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        function shareArticle() {
            if (navigator.share) {
                navigator.share({
                    title: '{{ addslashes($item->title) }}',
                    text: '{{ addslashes($item->summary ?: Str::limit(strip_tags($item->content), 100)) }}',
                    url: window.location.href
                }).catch(function(err){});
            } else {
                navigator.clipboard.writeText(window.location.href);
                alert('Tautan berita berhasil disalin ke clipboard!');
            }
        }

        function deleteItem(id) {
            if (!confirm('Apakah Anda yakin ingin menghapus berita/pengumuman ini?')) {
                return;
            }

            fetch("{{ url('announcement/mobile/delete') }}/" + id, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(function(res) { return res.json(); })
            .then(function(res) {
                if (res.success) {
                    alert(res.message);
                    window.location.href = "{{ route('announcement.mobile.index') }}";
                } else {
                    alert(res.message || 'Gagal menghapus pengumuman.');
                }
            })
            .catch(function(err) {
                alert('Terjadi kesalahan jaringan.');
            });
        }
    </script>
</body>
</html>
