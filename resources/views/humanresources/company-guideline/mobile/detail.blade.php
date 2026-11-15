<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $item->title }} - Mobile</title>
    <link rel="manifest" href="{{ asset('manifest-employee.json') }}">
    <meta name="theme-color" content="#064E3B">
    <link rel="stylesheet" href="{{ asset('adminor/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .doc-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background-color: #fef2f2;
            color: #dc2626;
            padding: 8px 14px;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid #fee2e2;
        }
    </style>
    @include('humanresources.mobile.partials.theme')
</head>
<body>
    <div class="finora-shell">
        <div class="finora-topbar">
            <a href="{{ route('company-guideline.mobile.index', ['tab' => strtolower($item->type)]) }}" class="finora-back-btn"><i class="fas fa-arrow-left"></i></a>
            <h1 class="fs-6 fw-bold m-0">Detail Pedoman</h1>
            <span style="width: 38px;"></span>
        </div>

        <div class="finora-hero">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge bg-white bg-opacity-25 text-white rounded-pill px-3 py-1" style="font-size: 11px;">{{ $item->type }}</span>
                @if($item->code)
                    <span class="badge bg-light text-success rounded-pill px-2 py-1" style="font-size: 11px;">{{ $item->code }}</span>
                @endif
            </div>
            <h5 class="fw-bold text-white mb-0">{{ $item->title }}</h5>
        </div>

        @if($item->summary)
            <div class="finora-card mb-3">
                <small class="fw-bold text-success text-uppercase d-block mb-1" style="font-size: 11px;">Ringkasan</small>
                <div class="fst-italic text-dark small">{{ $item->summary }}</div>
            </div>
        @endif

        <div class="finora-card mb-3">
            <h6 class="fw-bold text-muted small mb-2 text-uppercase">Isi Lengkap</h6>
            <div style="line-height: 1.7; color: #334155; font-size: 14px;">
                {!! $item->content !!}
            </div>
        </div>

        @if($item->attachment_file)
            <div class="finora-card mb-3 d-flex align-items-center justify-content-between gap-2">
                <div>
                    <div class="fw-bold small text-dark">{{ $item->attachment_name ?? 'Dokumen PDF Lengkap' }}</div>
                    <small class="text-muted">Format file PDF/Dokumen</small>
                </div>
                <a href="{{ asset($item->attachment_file) }}" target="_blank" class="doc-badge flex-shrink-0">
                    <i class="fa fa-download me-1"></i> Unduh
                </a>
            </div>
        @endif

        <div class="mt-3">
            <a href="{{ route('company-guideline.mobile.index', ['tab' => strtolower($item->type)]) }}" class="finora-btn-soft w-100">
                <i class="fa fa-chevron-left"></i> Kembali ke Daftar
            </a>
        </div>
    </div>

    @include('humanresources.mobile.partials.bottom-nav', ['active' => 'profile'])
</body>
</html>

