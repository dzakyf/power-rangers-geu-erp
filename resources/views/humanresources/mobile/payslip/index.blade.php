<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#064E3B">
    <title>Payslip - Slip Gaji Karyawan</title>
    <link rel="stylesheet" href="{{ asset('adminor/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @include('humanresources.mobile.partials.theme')
    <style>
        .payslip-list-card {
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
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .payslip-list-card:active {
            transform: scale(0.985);
        }
        .payslip-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            background: var(--emerald-soft);
            color: var(--emerald-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .mask-toggle-btn {
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.28);
            color: #FFFFFF;
            border-radius: 999px;
            padding: 5px 12px;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }
        .status-badge-final {
            background: #ECFDF5;
            color: #047857;
            border: 1px solid #A7F3D0;
            font-size: 10px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 999px;
        }
        .status-badge-preview {
            background: #FEF3C7;
            color: #B45309;
            border: 1px solid #FDE68A;
            font-size: 10px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 999px;
        }
    </style>
</head>
<body>
    <div class="finora-shell">
        <div class="finora-topbar">
            <a href="{{ route('mobile.dashboard') }}" class="finora-back-btn"><i class="fas fa-arrow-left"></i></a>
            <h1 class="fs-6 fw-bold m-0">Slip Gaji (Payslip)</h1>
            <button type="button" class="finora-back-btn" id="btnHeaderMaskToggle" onclick="togglePayslipMask()" title="Tampilkan / Sembunyikan Nominal">
                <i class="fas fa-eye" id="headerMaskIcon"></i>
            </button>
        </div>

        @if (session('error'))
            <div class="alert alert-warning rounded-4 small py-2 px-3 mb-3">
                <i class="fas fa-exclamation-triangle me-1"></i> {{ session('error') }}
            </div>
        @endif

        @if ($latest)
            <div class="finora-hero">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-white bg-opacity-25 text-white rounded-pill px-3 py-1" style="font-size: 11px;">
                        <i class="fas fa-calendar-check me-1"></i> {{ $latest['period_label'] }}
                    </span>
                    <button type="button" class="mask-toggle-btn" onclick="togglePayslipMask()">
                        <i class="fas fa-eye" id="heroMaskIcon"></i>
                        <span id="heroMaskLabel">Tampilkan</span>
                    </button>
                </div>

                <div class="small opacity-75 mb-1">Take Home Pay ({{ $latest['status_label'] }})</div>
                <div class="fs-2 fw-bold mb-2 salary-maskable"
                     data-masked="Rp •••••••"
                     data-real="Rp {{ number_format($latest['thp'], 0, ',', '.') }}">
                    Rp •••••••
                </div>

                <div class="d-flex justify-content-between align-items-center pt-2 border-top border-white border-opacity-25">
                    <div class="small opacity-75">
                        NIK: <strong class="text-white">{{ $nik }}</strong>
                    </div>
                    <a href="{{ route('payslip.mobile.detail', ['period' => $latest['period'], 'task_id' => $latest['task_id']]) }}"
                       class="btn btn-light btn-sm rounded-pill fw-bold text-success px-3">
                        Lihat Rincian <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        @else
            <div class="finora-hero">
                <div class="small opacity-75 mb-1">Informasi Slip Gaji</div>
                <h2 class="fs-5 fw-bold mb-1">Belum Ada Data Slip Gaji</h2>
                <p class="small mb-0 opacity-75">
                    @if ($nik === '')
                        Akun Anda belum terhubung dengan NIK karyawan. Hubungi tim HCGA.
                    @else
                        Slip gaji untuk NIK {{ $nik }} belum tersedia pada sistem payroll.
                    @endif
                </p>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-bold small text-uppercase text-muted">Riwayat Periode (12 Bulan)</span>
            <span class="finora-pill">{{ count($periods) }} Periode</span>
        </div>

        @forelse ($periods as $p)
            <a href="{{ route('payslip.mobile.detail', ['period' => $p['period'], 'task_id' => $p['task_id']]) }}" class="payslip-list-card">
                <div class="d-flex align-items-center gap-3">
                    <span class="payslip-icon-box">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </span>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="fw-bold" style="font-size: 14px;">{{ $p['period_label'] }}</span>
                            @if ($p['status'] === 'FINAL')
                                <span class="status-badge-final">FINAL</span>
                            @else
                                <span class="status-badge-preview">PREVIEW</span>
                            @endif
                        </div>
                        <div class="text-muted small salary-maskable"
                             style="font-size: 12px;"
                             data-masked="THP: Rp •••••••"
                             data-real="THP: Rp {{ number_format($p['thp'], 0, ',', '.') }}">
                            THP: Rp •••••••
                        </div>
                    </div>
                </div>
                <i class="fas fa-chevron-right text-muted small"></i>
            </a>
        @empty
            <div class="bg-white border rounded-4 p-4 text-center text-muted small">
                <i class="fas fa-folder-open fs-4 mb-2 d-block opacity-50"></i>
                Belum ada riwayat slip gaji yang tersedia.
            </div>
        @endforelse
    </div>

    @include('humanresources.mobile.partials.bottom-nav', ['active' => 'ess'])

    <script>
        var isPayslipMasked = true;
        function togglePayslipMask() {
            isPayslipMasked = !isPayslipMasked;
            var els = document.querySelectorAll('.salary-maskable');
            for (var i = 0; i < els.length; i++) {
                els[i].textContent = isPayslipMasked ? els[i].getAttribute('data-masked') : els[i].getAttribute('data-real');
            }
            var hIcon = document.getElementById('headerMaskIcon');
            var heroIcon = document.getElementById('heroMaskIcon');
            var heroLabel = document.getElementById('heroMaskLabel');
            if (hIcon) {
                hIcon.className = isPayslipMasked ? 'fas fa-eye' : 'fas fa-eye-slash';
            }
            if (heroIcon) {
                heroIcon.className = isPayslipMasked ? 'fas fa-eye' : 'fas fa-eye-slash';
            }
            if (heroLabel) {
                heroLabel.textContent = isPayslipMasked ? 'Tampilkan' : 'Sembunyikan';
            }
        }
    </script>
</body>
</html>
