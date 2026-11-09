<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $item->title }} - Mobile</title>
    <link rel="manifest" href="{{ asset('manifest-employee.json') }}">
    <meta name="theme-color" content="#0f172a">
    <link rel="stylesheet" href="{{ asset('adminor/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            padding-bottom: 30px;
            color: #1e293b;
        }
        .header-detail {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
            color: #fff;
            padding: 20px 16px 22px;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
        }
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
</head>
<body>
    <div class="header-detail">
        <div class="d-flex align-items-center gap-2 mb-2">
            <a href="{{ route('company-guideline.mobile.index', ['tab' => strtolower($item->type)]) }}" class="btn btn-outline-light btn-sm rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                <i class="fa fa-arrow-left"></i>
            </a>
            <span class="badge bg-light text-primary fw-semibold px-2 py-1 rounded-pill small">{{ $item->type }}</span>
        </div>
        <h5 class="fw-bold text-white mb-1">{{ $item->title }}</h5>
        @if($item->code)
            <span class="badge bg-white-50 text-white small">{{ $item->code }}</span>
        @endif
    </div>

    <div class="container-fluid px-3 pt-3">
        @if($item->summary)
            <div class="alert alert-light border shadow-sm p-3 mb-3 rounded-3">
                <small class="fw-bold text-muted d-block mb-1">Ringkasan:</small>
                <div class="fst-italic text-dark">{{ $item->summary }}</div>
            </div>
        @endif

        <div class="bg-white p-3 rounded-3 border shadow-sm mb-3">
            <h6 class="fw-bold text-muted small mb-2 text-uppercase letter-spacing-1">Isi Lengkap</h6>
            <div style="line-height: 1.7; color: #334155;">
                {!! $item->content !!}
            </div>
        </div>

        @if($item->attachment_file)
            <div class="bg-white p-3 rounded-3 border shadow-sm mb-3 d-flex align-items-center justify-content-between">
                <div>
                    <div class="fw-bold small text-dark">{{ $item->attachment_name ?? 'Dokumen PDF Lengkap' }}</div>
                    <small class="text-muted">Format file PDF/Dokumen</small>
                </div>
                <a href="{{ asset($item->attachment_file) }}" target="_blank" class="doc-badge">
                    <i class="fa fa-download me-1"></i> Buka / Unduh
                </a>
            </div>
        @endif

        <div class="text-center mt-4">
            <a href="{{ route('company-guideline.mobile.index', ['tab' => strtolower($item->type)]) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-4">
                <i class="fa fa-chevron-left me-1"></i> Kembali ke Daftar
            </a>
        </div>
    </div>
</body>
</html>

