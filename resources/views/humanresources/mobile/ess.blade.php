<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#064E3B">
    <title>ESS - Pusat Pengajuan Mandiri</title>
    <link rel="stylesheet" href="{{ asset('adminor/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @include('humanresources.mobile.partials.theme')
    <style>
        .ess-item-card {
            background: #FFFFFF;
            border: 1px solid var(--border-soft);
            border-radius: 18px;
            padding: 14px 16px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            text-decoration: none;
            color: var(--text-dark);
            transition: transform 0.15s ease;
        }
        .ess-item-card:active {
            transform: scale(0.985);
        }
        .ess-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
    </style>
</head>
<body>
    <div class="finora-shell">
        <div class="finora-topbar">
            <a href="{{ route('mobile.dashboard') }}" class="finora-back-btn"><i class="fas fa-arrow-left"></i></a>
            <h1 class="fs-6 fw-bold m-0">Employee Self-Service (ESS)</h1>
            <a href="{{ route('employee.mobile.my-profile', ['from' => 'ess']) }}" class="finora-back-btn" title="Profil Saya"><i class="fas fa-user"></i></a>
        </div>

        <div class="finora-hero">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge bg-white bg-opacity-25 text-white rounded-pill px-3 py-1" style="font-size: 11px;">
                    <i class="fas fa-briefcase me-1"></i> 7 Layanan Pengajuan
                </span>
                @if (!empty($ess['leaveBalance']))
                    <span class="badge bg-light text-success rounded-pill px-3 py-1" style="font-size: 11px;">
                        Saldo Cuti: {{ $ess['leaveBalance']->remaining_total ?? 0 }} Hari
                    </span>
                @endif
            </div>
            <h2 class="fs-5 fw-bold mb-1">Pusat Pengajuan Karyawan</h2>
            <p class="small mb-0 opacity-75">Pilih jenis pengajuan di bawah ini. Seluruh data diproses ke alur persetujuan HCGA.</p>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-bold small text-uppercase text-muted">Daftar Layanan ESS</span>
            <span class="finora-pill">{{ count($ess['essItems']) }} Modul</span>
        </div>

        @foreach ($ess['essItems'] as $item)
            @php
                $essHref = $item['route'] . (strpos($item['route'], '?') !== false ? '&' : '?') . 'from=ess';
            @endphp
            <a href="{{ $essHref }}" class="ess-item-card">
                <div class="d-flex align-items-center gap-3">
                    <span class="ess-icon-box tone-{{ $item['tone'] }}">
                        <i class="fas {{ $item['icon'] }}"></i>
                    </span>
                    <div>
                        <div class="fw-bold" style="font-size: 14px;">{{ $item['title'] }}</div>
                        <div class="text-muted" style="font-size: 12px;">{{ $item['subtitle'] }}</div>
                    </div>
                </div>
                <i class="fas fa-chevron-right text-muted small"></i>
            </a>
        @endforeach
    </div>

    @include('humanresources.mobile.partials.bottom-nav', ['active' => 'ess'])
</body>
</html>
