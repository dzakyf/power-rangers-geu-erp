<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Budaya & Peraturan Perusahaan</title>
    <!-- PWA Settings -->
    <link rel="manifest" href="{{ asset('manifest-employee.json') }}">
    <meta name="theme-color" content="#064E3B">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Budaya & Peraturan">
    <link rel="apple-touch-icon" href="{{ asset('adminor/assets/img/brand/favicon.png') }}">
    <!-- Bootstrap 5 & FontAwesome -->
    <link rel="stylesheet" href="{{ asset('adminor/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            padding-bottom: 80px;
            color: #1e293b;
        }
        /* Segmented Button Group for Mobile */
        .category-nav-wrapper {
            background: #fff;
            position: sticky;
            top: 0;
            z-index: 99;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-bottom: 1px solid #e2e8f0;
            padding: 10px 14px;
        }
        .segmented-btn-group {
            display: flex;
            background: #f1f5f9;
            padding: 4px;
            border-radius: 12px;
            gap: 4px;
            overflow-x: auto;
            scrollbar-width: none;
        }
        .segmented-btn-group::-webkit-scrollbar {
            display: none;
        }
        .segmented-btn {
            flex: 1 0 auto;
            text-align: center;
            padding: 8px 12px;
            font-size: 0.8rem;
            font-weight: 600;
            border-radius: 9px;
            border: none;
            background: transparent;
            color: #64748b;
            transition: all 0.2s ease;
            white-space: nowrap;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
        }
        .segmented-btn.active {
            background: #047857;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(4, 120, 87, 0.25);
            font-weight: 700;
        }
        .segmented-btn i {
            font-size: 0.95rem;
        }
        .segmented-btn .badge-count {
            font-size: 0.65rem;
            padding: 2px 6px;
            border-radius: 10px;
            background: rgba(4, 120, 87, 0.12);
            color: #047857;
        }
        .segmented-btn.active .badge-count {
            background: rgba(255, 255, 255, 0.22);
            color: #ffffff;
        }
        .culture-card {
            background: #fff;
            border-radius: 18px;
            border: 1px solid #e2e8f0;
            padding: 18px;
            margin-bottom: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .culture-card:active {
            transform: scale(0.99);
        }
        .card-icon-wrapper {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1.15rem;
        }
        .icon-primary { background-color: #ECFDF5; color: #047857; }
        .icon-success { background-color: #f0fdf4; color: #16a34a; }
        .icon-warning { background-color: #fefce8; color: #ca8a04; }
        .icon-danger  { background-color: #fef2f2; color: #dc2626; }
        .icon-info    { background-color: #ecfeff; color: #0891b2; }
        .icon-dark    { background-color: #f1f5f9; color: #334155; }

        .rich-body {
            font-size: 0.9rem;
            line-height: 1.65;
            color: #334155;
        }
        .rich-body ul {
            padding-left: 20px;
            margin-bottom: 0;
        }
        .rich-body li {
            margin-bottom: 6px;
        }
        .doc-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background-color: #fef2f2;
            color: #dc2626;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid #fee2e2;
        }
        .doc-badge:hover {
            color: #991b1b;
            background-color: #fee2e2;
        }
        /* Bottom navigation bar */
        .nav-bottom-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #fff;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-around;
            padding: 8px 4px 10px;
            z-index: 1000;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.04);
        }
        .nav-bottom-item {
            color: #64748b;
            text-decoration: none;
            font-size: 0.72rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
            flex: 1;
            text-align: center;
        }
        .nav-bottom-item.active {
            color: #047857;
            font-weight: 700;
        }
        .nav-bottom-item i {
            font-size: 1.15rem;
            margin-bottom: 2px;
        }
        .hero-banner {
            background: linear-gradient(135deg, rgba(255,255,255,0.12) 0%, rgba(255,255,255,0.04) 100%);
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 14px;
            padding: 12px 14px;
            margin-top: 14px;
            backdrop-filter: blur(4px);
        }
    </style>
    @include('humanresources.mobile.partials.theme')
</head>
<body>

    @php
        $resolvedBackUrl = mobile_back_url(route('mobile.dashboard'));
        $fromParams = mobile_from_params();
    @endphp
    <div class="finora-shell pb-0">
        <div class="finora-topbar">
            <a href="{{ $resolvedBackUrl }}" class="finora-back-btn"><i class="fas fa-arrow-left"></i></a>
            <h1 class="fs-6 fw-bold m-0">Pedoman Perusahaan</h1>
            <a href="{{ route('mobile.dashboard') }}" class="finora-back-btn" title="Dashboard"><i class="fas fa-home"></i></a>
        </div>

        <div class="finora-hero mb-2">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge bg-white bg-opacity-25 text-white rounded-pill px-3 py-1" style="font-size: 11px;">
                    <i class="fas fa-compass me-1"></i> Budaya &amp; Tata Tertib
                </span>
                <span class="small opacity-75">
                    <i class="fa fa-user-circle me-1"></i> {{ $user->Name ?? $user->username }}
                </span>
            </div>
            <div class="fw-bold fs-6 mb-1">Visi, Misi &amp; Peraturan</div>
            <p class="small mb-2 opacity-75">Nilai-nilai utama (Core Values) dan pedoman kerja karyawan.</p>
            <div class="bg-white bg-opacity-10 rounded-3 px-3 py-2 d-flex align-items-center gap-2">
                <i class="fa fa-quote-left opacity-75"></i>
                <div class="small fw-semibold">
                    "Membangun integritas, profesionalisme, dan komitmen bersama."
                </div>
            </div>
        </div>
    </div>

    <!-- Segmented Navigation Bar: VISI | MISI | CORE VALUE | PERATURAN -->
    <div class="category-nav-wrapper">
        <div class="segmented-btn-group" role="tablist">
            <button class="segmented-btn {{ $activeTab === 'visi' ? 'active' : '' }}" onclick="switchTab('visi')">
                <i class="fa fa-eye"></i>
                <span>Visi</span>
                <span class="badge-count">{{ count($grouped['visi']) }}</span>
            </button>
            <button class="segmented-btn {{ $activeTab === 'misi' ? 'active' : '' }}" onclick="switchTab('misi')">
                <i class="fa fa-bullseye"></i>
                <span>Misi</span>
                <span class="badge-count">{{ count($grouped['misi']) }}</span>
            </button>
            <button class="segmented-btn {{ $activeTab === 'core_value' ? 'active' : '' }}" onclick="switchTab('core_value')">
                <i class="fa fa-award"></i>
                <span>Core Value</span>
                <span class="badge-count">{{ count($grouped['core_values']) }}</span>
            </button>
            <button class="segmented-btn {{ $activeTab === 'peraturan' ? 'active' : '' }}" onclick="switchTab('peraturan')">
                <i class="fa fa-book-open"></i>
                <span>Peraturan</span>
                <span class="badge-count">{{ count($grouped['peraturan']) }}</span>
            </button>
        </div>
    </div>

    <!-- Tab Contents Container -->
    <div class="container-fluid px-3 pt-3">
        <!-- 1. TAB VISI -->
        <div id="tab-content-visi" class="tab-pane-custom {{ $activeTab === 'visi' ? '' : 'd-none' }}">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="fw-bold text-dark mb-0"><i class="fa fa-eye text-primary me-2"></i>Visi Perusahaan</h6>
                <span class="small text-muted">Arah & Cita-Cita</span>
            </div>
            @forelse($grouped['visi'] as $item)
                <div class="culture-card">
                    <div class="d-flex align-items-start gap-3 mb-2">
                        <div class="card-icon-wrapper icon-{{ $item->badge_color ?? 'primary' }}">
                            <i class="{{ $item->icon ?? 'fa fa-eye' }}"></i>
                        </div>
                        <div class="flex-grow-1">
                            @if($item->code)
                                <span class="badge bg-light text-primary border mb-1 small">{{ $item->code }}</span>
                            @endif
                            <h6 class="fw-bold text-dark mb-1">{{ $item->title }}</h6>
                            @if($item->summary)
                                <p class="text-muted small mb-0 fst-italic">{{ $item->summary }}</p>
                            @endif
                        </div>
                    </div>
                    @if($item->content)
                        <div class="rich-body mt-2 pt-2 border-top">
                            {!! $item->content !!}
                        </div>
                    @endif
                    @if($item->attachment_file)
                        <div class="mt-3 pt-2 border-top">
                            <a href="{{ asset($item->attachment_file) }}" target="_blank" class="doc-badge">
                                <i class="fa fa-file-pdf"></i>
                                <span>{{ $item->attachment_name ?? 'Download Dokumen Visi' }}</span>
                            </a>
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    <i class="fa fa-compass fa-3x text-black-50 mb-3"></i>
                    <p class="small">Belum ada data Visi yang dipublikasikan.</p>
                </div>
            @endforelse
        </div>

        <!-- 2. TAB MISI -->
        <div id="tab-content-misi" class="tab-pane-custom {{ $activeTab === 'misi' ? '' : 'd-none' }}">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="fw-bold text-dark mb-0"><i class="fa fa-bullseye text-success me-2"></i>Misi Perusahaan</h6>
                <span class="small text-muted">Langkah Strategis</span>
            </div>
            @forelse($grouped['misi'] as $item)
                <div class="culture-card">
                    <div class="d-flex align-items-start gap-3 mb-2">
                        <div class="card-icon-wrapper icon-{{ $item->badge_color ?? 'success' }}">
                            <i class="{{ $item->icon ?? 'fa fa-bullseye' }}"></i>
                        </div>
                        <div class="flex-grow-1">
                            @if($item->code)
                                <span class="badge bg-light text-success border mb-1 small">{{ $item->code }}</span>
                            @endif
                            <h6 class="fw-bold text-dark mb-1">{{ $item->title }}</h6>
                            @if($item->summary)
                                <p class="text-muted small mb-0 fst-italic">{{ $item->summary }}</p>
                            @endif
                        </div>
                    </div>
                    @if($item->content)
                        <div class="rich-body mt-2 pt-2 border-top">
                            {!! $item->content !!}
                        </div>
                    @endif
                    @if($item->attachment_file)
                        <div class="mt-3 pt-2 border-top">
                            <a href="{{ asset($item->attachment_file) }}" target="_blank" class="doc-badge">
                                <i class="fa fa-file-pdf"></i>
                                <span>{{ $item->attachment_name ?? 'Download Dokumen Misi' }}</span>
                            </a>
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    <i class="fa fa-bullseye fa-3x text-black-50 mb-3"></i>
                    <p class="small">Belum ada data Misi yang dipublikasikan.</p>
                </div>
            @endforelse
        </div>

        <!-- 3. TAB CORE VALUE -->
        <div id="tab-content-core_value" class="tab-pane-custom {{ $activeTab === 'core_value' ? '' : 'd-none' }}">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="fw-bold text-dark mb-0"><i class="fa fa-award text-warning me-2"></i>Core Values (Tata Nilai)</h6>
                <span class="small text-muted">Prinsip & Budaya Kerja</span>
            </div>
            @forelse($grouped['core_values'] as $item)
                <div class="culture-card">
                    <div class="d-flex align-items-start gap-3 mb-2">
                        <div class="card-icon-wrapper icon-{{ $item->badge_color ?? 'primary' }}">
                            <i class="{{ $item->icon ?? 'fa fa-award' }}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-1 mb-1">
                                @if($item->code)
                                    <span class="badge bg-light text-dark border small">{{ $item->code }}</span>
                                @endif
                                <span class="badge bg-{{ $item->badge_color ?? 'primary' }} small">Nilai #{{ $item->sort_order }}</span>
                            </div>
                            <h6 class="fw-bold text-dark mb-1">{{ $item->title }}</h6>
                            @if($item->summary)
                                <p class="text-muted small mb-0">{{ $item->summary }}</p>
                            @endif
                        </div>
                    </div>
                    @if($item->content)
                        <div class="rich-body mt-2 pt-2 border-top">
                            {!! $item->content !!}
                        </div>
                    @endif
                    @if($item->attachment_file)
                        <div class="mt-3 pt-2 border-top">
                            <a href="{{ asset($item->attachment_file) }}" target="_blank" class="doc-badge">
                                <i class="fa fa-file-pdf"></i>
                                <span>{{ $item->attachment_name ?? 'Download Dokumen' }}</span>
                            </a>
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    <i class="fa fa-award fa-3x text-black-50 mb-3"></i>
                    <p class="small">Belum ada Core Value yang dipublikasikan.</p>
                </div>
            @endforelse
        </div>

        <!-- 4. TAB PERATURAN PERUSAHAAN -->
        <div id="tab-content-peraturan" class="tab-pane-custom {{ $activeTab === 'peraturan' ? '' : 'd-none' }}">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="fw-bold text-dark mb-0"><i class="fa fa-book-open text-danger me-2"></i>Peraturan Perusahaan</h6>
                <span class="small text-muted">Regulasi & Tata Tertib</span>
            </div>
            @forelse($grouped['peraturan'] as $item)
                <div class="culture-card">
                    <div class="d-flex align-items-start gap-3 mb-2">
                        <div class="card-icon-wrapper icon-{{ $item->badge_color ?? 'dark' }}">
                            <i class="{{ $item->icon ?? 'fa fa-book-open' }}"></i>
                        </div>
                        <div class="flex-grow-1">
                            @if($item->code)
                                <span class="badge bg-light text-dark border mb-1 small">{{ $item->code }}</span>
                            @endif
                            <h6 class="fw-bold text-dark mb-1">{{ $item->title }}</h6>
                            @if($item->summary)
                                <p class="text-muted small mb-0">{{ $item->summary }}</p>
                            @endif
                        </div>
                    </div>
                    @if($item->content)
                        <div class="rich-body mt-2 pt-2 border-top">
                            {!! $item->content !!}
                        </div>
                    @endif
                    @if($item->attachment_file)
                        <div class="mt-3 pt-2 border-top d-flex align-items-center justify-content-between">
                            <a href="{{ asset($item->attachment_file) }}" target="_blank" class="doc-badge">
                                <i class="fa fa-file-pdf fa-lg"></i>
                                <span>{{ $item->attachment_name ?? 'Unduh Buku Peraturan (PDF)' }}</span>
                            </a>
                            <span class="small text-muted"><i class="fa fa-check-circle text-success me-1"></i>Resmi</span>
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    <i class="fa fa-book fa-3x text-black-50 mb-3"></i>
                    <p class="small">Belum ada Peraturan Perusahaan yang dipublikasikan.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Bottom Navigation Bar -->
    <div class="nav-bottom-bar">
        <a href="{{ route('employee.mobile.my-profile') }}" class="nav-bottom-item">
            <i class="fa fa-id-card"></i>
            <span>Profil</span>
        </a>
        <a href="{{ route('announcement.mobile.index') }}" class="nav-bottom-item">
            <i class="fa fa-newspaper"></i>
            <span>Berita</span>
        </a>
        <a href="{{ route('company-guideline.mobile.index') }}" class="nav-bottom-item active">
            <i class="fa fa-compass"></i>
            <span>Budaya</span>
        </a>
        <a href="{{ route('attendance.mobile.index') }}" class="nav-bottom-item">
            <i class="fa fa-fingerprint"></i>
            <span>Absensi</span>
        </a>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('adminor/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        function switchTab(tabKey) {
            // Update segmented buttons
            $('.segmented-btn').removeClass('active');
            $('.segmented-btn[onclick="switchTab(\'' + tabKey + '\')"]').addClass('active');

            // Toggle panes
            $('.tab-pane-custom').addClass('d-none');
            $('#tab-content-' + tabKey).removeClass('d-none');

            // Scroll top slightly to view content
            window.scrollTo({ top: 120, behavior: 'smooth' });

            // Push state to URL without reloading
            if (window.history && window.history.replaceState) {
                var newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?tab=' + tabKey;
                window.history.replaceState({ path: newUrl }, '', newUrl);
            }
        }
    </script>
    @include('humanresources.mobile.partials.bottom-nav', ['active' => 'dashboard'])
</body>
</html>

