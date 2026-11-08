<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Antrean Approval SPPD Mobile</title>
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
            background: linear-gradient(135deg, #b45309 0%, #f59e0b 100%);
            color: #fff;
            padding: 22px 18px 24px;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
            box-shadow: 0 4px 15px rgba(180, 83, 9, 0.2);
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
            color: #b45309;
            font-weight: bold;
        }
        .nav-item-mobile i {
            font-size: 20px;
            margin-bottom: 3px;
        }
    </style>
</head>
<body>

    <!-- Header Mobile Approval -->
    <div class="header-card">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1 fw-bold"><i class="fa fa-stamp me-2"></i>Approval SPPD</h5>
                <small class="text-white-75">Antrean Verifikasi Perjalanan Dinas</small>
            </div>
            <span class="badge bg-white text-dark rounded-pill px-3 py-2 fw-bold shadow-sm">
                {{ count($queue) }} Menunggu
            </span>
        </div>
    </div>

    <div class="container px-3 mt-3">
        @if($queue->isEmpty())
            <div class="text-center py-5">
                <i class="fa fa-clipboard-check fa-3x text-muted mb-3 d-block opacity-50"></i>
                <h6 class="fw-bold text-muted">Tidak Ada Antrean Approval</h6>
                <small class="text-muted d-block">Semua pengajuan SPPD sudah Anda proses atau belum ada tugas baru.</small>
            </div>
        @else
            @foreach($queue as $item)
                <a href="{{ route('sppd.mobile.detail', ['id' => $item['id']]) }}" class="card sppd-item-card p-3 mb-2 bg-white border-start border-4 border-warning">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold text-primary" style="font-size: 13px;">{{ $item['sppd_no'] }}</span>
                        <span class="badge bg-warning text-dark rounded-pill">{{ $item['status'] }}</span>
                    </div>
                    <div class="fw-bold text-dark mb-0" style="font-size: 14px;">{{ $item['requester_name'] }}</div>
                    <small class="text-muted d-block mb-1">{{ $item['department'] }} &bull; {{ $item['type'] }}</small>
                    <div class="small text-secondary mb-2">
                        <i class="fa fa-route me-1 text-danger"></i> {{ $item['route'] }}
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                        <small class="text-muted"><i class="fa fa-calendar-alt me-1"></i> {{ $item['schedule_date'] }}</small>
                        <span class="fw-bold text-success" style="font-size: 13px;">{{ $item['estimated_cost_formatted'] }}</span>
                    </div>
                </a>
            @endforeach
        @endif
    </div>

    <!-- Bottom Nav Mobile Bar -->
    <div class="bottom-nav">
        <a href="{{ route('sppd.mobile.index') }}" class="nav-item-mobile">
            <i class="fa fa-list-check"></i>
            <span>Daftar SPPD</span>
        </a>
        <a href="{{ route('sppd.mobile.form') }}" class="nav-item-mobile">
            <i class="fa fa-circle-plus"></i>
            <span>Buat SPPD</span>
        </a>
        <a href="{{ route('sppd.mobile.approval') }}" class="nav-item-mobile active">
            <i class="fa fa-stamp"></i>
            <span>Approval</span>
        </a>
    </div>

</body>
</html>

