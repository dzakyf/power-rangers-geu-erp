<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#064E3B">
    <title>Detail Payslip - {{ $payslip['period_label'] ?? $payslip['period'] }}</title>
    <link rel="stylesheet" href="{{ asset('adminor/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @include('humanresources.mobile.partials.theme')
    <style>
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
        .acc-card {
            background: #FFFFFF;
            border: 1px solid var(--border-soft);
            border-radius: 18px;
            overflow: hidden;
            margin-bottom: 12px;
            box-shadow: 0 2px 10px rgba(6, 78, 59, 0.04);
        }
        .acc-trigger {
            width: 100%;
            background: #FFFFFF;
            border: 0;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            text-align: left;
            color: var(--text-dark);
        }
        .acc-body {
            padding: 0 16px 14px;
            border-top: 1px solid #F1F5F4;
        }
        .line-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 10px;
            padding: 9px 0;
            border-bottom: 1px dashed #EDF2F0;
            font-size: 13px;
        }
        .line-item:last-child {
            border-bottom: 0;
        }
        .advance-subbox {
            background: #FEF2F2;
            border: 1px solid #FECACA;
            border-radius: 12px;
            padding: 10px 12px;
            margin-top: 8px;
        }
        .advance-subrow {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            font-size: 12px;
            padding: 5px 0;
            border-bottom: 1px dashed #FCA5A5;
        }
        .advance-subrow:last-child {
            border-bottom: 0;
        }
        @media print {
            .finora-topbar, .hcga-bottom-nav, .action-buttons-bar {
                display: none !important;
            }
            body {
                padding-bottom: 0 !important;
                background: #FFFFFF !important;
            }
            .collapse {
                display: block !important;
            }
        }
    </style>
</head>
<body>
    @include('humanresources.mobile.partials.resolve-back', ['defaultBack' => route('mobile.dashboard')])
    <div class="finora-shell">
        <div class="finora-topbar">
            <a href="{{ route('payslip.mobile.index', $fromParams) }}" class="finora-back-btn"><i class="fas fa-arrow-left"></i></a>
            <h1 class="fs-6 fw-bold m-0">{{ $payslip['period_label'] ?? $payslip['period'] }}</h1>
            <button type="button" class="finora-back-btn" onclick="togglePayslipMask()" title="Tampilkan / Sembunyikan Nominal">
                <i class="fas fa-eye" id="headerMaskIcon"></i>
            </button>
        </div>

        <!-- Hero Summary -->
        <div class="finora-hero">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge bg-white bg-opacity-25 text-white rounded-pill px-3 py-1" style="font-size: 11px;">
                    {{ !empty($payslip['is_final']) ? 'FINAL / OFFICIAL' : 'PREVIEW / DRAFT' }}
                </span>
                <button type="button" class="mask-toggle-btn" onclick="togglePayslipMask()">
                    <i class="fas fa-eye" id="heroMaskIcon"></i>
                    <span id="heroMaskLabel">Tampilkan</span>
                </button>
            </div>

            <div class="small opacity-75">Take Home Pay (Gaji Bersih)</div>
            <div class="fs-2 fw-bold mb-2 salary-maskable"
                 data-masked="Rp •••••••"
                 data-real="Rp {{ number_format($payslip['take_home_pay'] ?? 0, 0, ',', '.') }}">
                Rp •••••••
            </div>

            <div class="pt-2 border-top border-white border-opacity-25 small">
                <div class="fw-bold">{{ $payslip['name'] }} ({{ $payslip['nik'] }})</div>
                <div class="opacity-75">{{ $payslip['job_position'] }} &bull; {{ $payslip['organization'] }}</div>
                <div class="opacity-75 mt-1">
                    PTKP: {{ $payslip['ptkp'] }} &bull; NPWP: {{ $payslip['npwp'] }}
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-grid gap-2 d-flex mb-3 action-buttons-bar">
            <a href="{{ route('payslip.mobile.pdf', ['period' => $payslip['period'], 'task_id' => $payslip['is_final'] ? $payslip['task_id'] : null]) }}"
               class="btn btn-success flex-fill rounded-4 fw-bold py-2" style="background-color: #047857; border-color: #047857;">
                <i class="fas fa-file-pdf me-1"></i> Download PDF
            </a>
            <button type="button" onclick="window.print()" class="btn btn-outline-secondary rounded-4 fw-bold px-3 py-2 bg-white">
                <i class="fas fa-print me-1"></i> Cetak
            </button>
        </div>

        <!-- 1. Accordion Pendapatan -->
        <div class="acc-card">
            <button class="acc-trigger" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEarnings" aria-expanded="true">
                <div>
                    <div class="fw-bold" style="font-size: 14px;">
                        <i class="fas fa-plus-circle text-success me-2"></i>Pendapatan (Earnings)
                    </div>
                    <div class="small text-muted salary-maskable"
                         data-masked="Total: Rp •••••••"
                         data-real="Total: Rp {{ number_format($payslip['total_earnings'] ?? 0, 0, ',', '.') }}">
                        Total: Rp •••••••
                    </div>
                </div>
                <i class="fas fa-chevron-down text-muted small"></i>
            </button>
            <div id="collapseEarnings" class="collapse show">
                <div class="acc-body">
                    @foreach ($payslip['earnings'] ?? [] as $item)
                        <div class="line-item">
                            <span class="text-muted">{{ $item['name'] }}</span>
                            <span class="fw-bold text-dark salary-maskable"
                                  data-masked="Rp ••••••"
                                  data-real="Rp {{ number_format($item['amount'], 0, ',', '.') }}">
                                Rp ••••••
                            </span>
                        </div>
                    @endforeach
                    @foreach ($payslip['additional_earnings'] ?? [] as $item)
                        <div class="line-item">
                            <span class="text-muted">{{ $item['name'] }}</span>
                            <span class="fw-bold text-success salary-maskable"
                                  data-masked="Rp ••••••"
                                  data-real="Rp {{ number_format($item['amount'], 0, ',', '.') }}">
                                Rp ••••••
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- 2. Accordion Potongan & Pajak -->
        <div class="acc-card">
            <button class="acc-trigger" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDeductions" aria-expanded="true">
                <div>
                    <div class="fw-bold" style="font-size: 14px;">
                        <i class="fas fa-minus-circle text-danger me-2"></i>Potongan & Pajak
                    </div>
                    <div class="small text-muted salary-maskable"
                         data-masked="Total: Rp •••••••"
                         data-real="Total: Rp {{ number_format($payslip['total_deductions'] ?? 0, 0, ',', '.') }}">
                        Total: Rp •••••••
                    </div>
                </div>
                <i class="fas fa-chevron-down text-muted small"></i>
            </button>
            <div id="collapseDeductions" class="collapse show">
                <div class="acc-body">
                    @forelse (array_merge($payslip['deductions'] ?? [], $payslip['additional_deductions'] ?? []) as $item)
                        <div class="line-item">
                            <span class="text-muted">{{ $item['name'] }}</span>
                            <span class="fw-bold text-danger salary-maskable"
                                  data-masked="Rp ••••••"
                                  data-real="Rp {{ number_format($item['amount'], 0, ',', '.') }}">
                                Rp ••••••
                            </span>
                        </div>
                    @empty
                        <div class="py-2 small text-muted">Tidak ada potongan pada periode ini.</div>
                    @endforelse

                    @if (!empty($payslip['advance_items']))
                        <div class="advance-subbox">
                            <div class="fw-bold text-danger mb-1" style="font-size: 12px;">
                                <i class="fas fa-receipt me-1"></i> Rincian Potongan SPPD / Kasbon / Reimburse
                            </div>
                            @foreach ($payslip['advance_items'] as $adv)
                                <div class="advance-subrow">
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $adv->reference_no ?: $adv->deduction_type }}</div>
                                        <div class="text-muted" style="font-size: 11px;">{{ $adv->description }}</div>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold text-danger salary-maskable"
                                             data-masked="Rp ••••••"
                                             data-real="Rp {{ number_format($adv->deducted_amount, 0, ',', '.') }}">
                                            Rp ••••••
                                        </div>
                                        <span class="badge bg-light text-secondary border" style="font-size: 9px;">{{ $adv->status }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 3. Accordion Benefit Perusahaan -->
        <div class="acc-card">
            <button class="acc-trigger collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBenefits" aria-expanded="false">
                <div>
                    <div class="fw-bold" style="font-size: 14px;">
                        <i class="fas fa-shield-alt text-primary me-2"></i>Benefit Perusahaan (Non-THP)
                    </div>
                    <div class="small text-muted salary-maskable"
                         data-masked="Total: Rp •••••••"
                         data-real="Total: Rp {{ number_format($payslip['total_benefits'] ?? 0, 0, ',', '.') }}">
                        Total: Rp •••••••
                    </div>
                </div>
                <i class="fas fa-chevron-down text-muted small"></i>
            </button>
            <div id="collapseBenefits" class="collapse">
                <div class="acc-body">
                    @foreach ($payslip['benefits'] ?? [] as $item)
                        <div class="line-item">
                            <span class="text-muted">{{ $item['name'] }}</span>
                            <span class="fw-bold text-dark salary-maskable"
                                  data-masked="Rp ••••••"
                                  data-real="Rp {{ number_format($item['amount'], 0, ',', '.') }}">
                                Rp ••••••
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        @if (!empty($payslip['note']))
            <div class="bg-white border rounded-4 p-3 small text-muted mb-3">
                <i class="fas fa-info-circle text-success me-1"></i> {{ $payslip['note'] }}
            </div>
        @endif
    </div>

    @include('humanresources.mobile.partials.bottom-nav', ['active' => 'ess'])

    <script src="{{ asset('adminor/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
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
