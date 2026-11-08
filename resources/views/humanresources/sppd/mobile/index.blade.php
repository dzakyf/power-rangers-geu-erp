<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SPPD Mobile - Perjalanan Dinas</title>
    <!-- Bootstrap 5 & FontAwesome -->
    <link rel="stylesheet" href="{{ asset('adminor/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            padding-bottom: 85px;
        }
        .header-card {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: #fff;
            padding: 22px 18px 24px;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
            box-shadow: 0 4px 15px rgba(30, 58, 138, 0.2);
        }
        .status-pill {
            font-size: 11px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 20px;
        }
        .filter-scroll {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            white-space: nowrap;
            padding-bottom: 4px;
        }
        .filter-btn {
            font-size: 12px;
            font-weight: 600;
            border-radius: 20px;
            padding: 5px 14px;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #64748b;
            text-decoration: none;
        }
        .filter-btn.active {
            background: #1e3a8a;
            color: #fff;
            border-color: #1e3a8a;
        }
        .sppd-item-card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            transition: transform 0.15s ease;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .sppd-item-card:active {
            transform: scale(0.98);
        }
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #fff;
            height: 65px;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-around;
            align-items: center;
            z-index: 1000;
        }
        .nav-item-mobile {
            text-align: center;
            color: #8e9aab;
            text-decoration: none;
            font-size: 11px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .nav-item-mobile.active {
            color: #1e3a8a;
            font-weight: bold;
        }
        .nav-item-mobile i {
            font-size: 20px;
            margin-bottom: 3px;
        }
    </style>
</head>
<body>

    <!-- Header Mobile SPPD -->
    <div class="header-card">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1 fw-bold"><i class="fa fa-briefcase me-2"></i>Perjalanan Dinas</h5>
                <small class="text-white-50">Pengajuan & Pelacakan SPPD Karyawan</small>
            </div>
            <a href="{{ route('sppd.mobile.form') }}" class="btn btn-light btn-sm rounded-pill fw-bold text-primary px-3 shadow-sm">
                <i class="fa fa-plus me-1"></i> Baru
            </a>
        </div>
    </div>

    <div class="container px-3 mt-3">
        <!-- Filter Tabs Horizontal -->
        <div class="filter-scroll mb-3">
            <a href="{{ route('sppd.mobile.index', ['status' => 'ALL']) }}" class="filter-btn {{ $status == 'ALL' ? 'active' : '' }}">Semua</a>
            <a href="{{ route('sppd.mobile.index', ['status' => 'SUBMITTED']) }}" class="filter-btn {{ $status == 'SUBMITTED' ? 'active' : '' }}">Diajukan</a>
            <a href="{{ route('sppd.mobile.index', ['status' => 'ACCEPTED']) }}" class="filter-btn {{ $status == 'ACCEPTED' ? 'active' : '' }}">Disetujui</a>
            <a href="{{ route('sppd.mobile.index', ['status' => 'REJECTED']) }}" class="filter-btn {{ $status == 'REJECTED' ? 'active' : '' }}">Ditolak</a>
        </div>

        <!-- Daftar Pengajuan SPPD -->
        @if($sppds->isEmpty())
            <div class="text-center py-5">
                <i class="fa fa-folder-open fa-3x text-muted mb-3 d-block opacity-50"></i>
                <h6 class="fw-bold text-muted">Belum ada data pengajuan SPPD</h6>
                <small class="text-muted d-block mb-3">Tekan tombol '+ Baru' di atas untuk mengajukan perjalanan dinas.</small>
                <a href="{{ route('sppd.mobile.form') }}" class="btn btn-primary btn-sm rounded-pill px-4">Buat Pengajuan</a>
            </div>
        @else
            @foreach($sppds as $item)
                @php
                    $badgeClass = 'bg-warning text-dark';
                    if (in_array($item['status'], ['ACCEPTED', 'APPROVED'])) {
                        $badgeClass = 'bg-success text-white';
                    } elseif ($item['status'] === 'REJECTED') {
                        $badgeClass = 'bg-danger text-white';
                    }
                @endphp
                <a href="{{ route('sppd.mobile.detail', ['id' => $item['id']]) }}" class="card sppd-item-card p-3 mb-2 bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold text-primary" style="font-size: 13px;">{{ $item['sppd_no'] }}</span>
                        <span class="status-pill {{ $badgeClass }}">{{ $item['status'] }}</span>
                    </div>
                    <div class="fw-semibold text-dark mb-1" style="font-size: 14px;">{{ $item['destination'] }}</div>
                    <div class="text-muted small mb-2">
                        <i class="fa fa-route text-secondary me-1"></i> {{ $item['route'] }}
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                        <small class="text-muted"><i class="fa fa-calendar-alt me-1"></i> {{ $item['schedule_date'] }}</small>
                        <span class="fw-bold text-dark" style="font-size: 13px;">{{ $item['estimated_cost_formatted'] }}</span>
                    </div>
                </a>
            @endforeach
        @endif
    </div>

    <!-- Bottom Nav Mobile Bar -->
    <div class="bottom-nav">
        <a href="{{ route('sppd.mobile.index') }}" class="nav-item-mobile active">
            <i class="fa fa-list-check"></i>
            <span>Daftar SPPD</span>
        </a>
        <a href="{{ route('sppd.mobile.form') }}" class="nav-item-mobile">
            <i class="fa fa-circle-plus"></i>
            <span>Buat SPPD</span>
        </a>
        <a href="{{ route('sppd.mobile.approval') }}" class="nav-item-mobile">
            <i class="fa fa-stamp"></i>
            <span>Approval</span>
        </a>
    </div>

</body>
</html>

