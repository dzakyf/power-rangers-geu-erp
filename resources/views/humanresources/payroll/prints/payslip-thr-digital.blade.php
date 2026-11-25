@php
    $isPdf = !empty($isPdfMode);
    $isEmail = !empty($isEmailMode);
    $logoSrc = asset('assets/images/geu-logo-color.webp');
    if ($isPdf || $isEmail) {
        $webpPath = public_path('assets/images/geu-logo-color.webp');
        if (file_exists($webpPath) && function_exists('imagecreatefromwebp')) {
            $img = @imagecreatefromwebp($webpPath);
            if ($img) {
                ob_start();
                imagepng($img);
                $pngData = ob_get_clean();
                imagedestroy($img);
                if ($pngData) {
                    $logoSrc = 'data:image/png;base64,' . base64_encode($pngData);
                }
            }
        }
    }
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip THR - {{ $payslip['name'] }} ({{ $payslip['nik'] }})</title>
    @if (!$isPdf && !$isEmail)
        <link href="{{ asset('adminor/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    @endif
    <style>
        body {
            background-color: {{ ($isPdf || $isEmail) ? '#ffffff' : '#f4f6f9' }};
            font-family: DejaVu Sans, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            color: #212529;
            margin: 0;
            padding: 0;
            font-size: 12px;
        }

        .payslip-container {
            max-width: 800px;
            margin: {{ ($isPdf || $isEmail) ? '0 auto' : '25px auto' }};
            background: #ffffff;
            border-radius: 8px;
            box-shadow: {{ ($isPdf || $isEmail) ? 'none' : '0 4px 20px rgba(0, 0, 0, 0.06)' }};
            padding: {{ $isPdf ? '18px 22px' : '32px 36px' }};
            border: {{ $isEmail ? '1px solid #dee2e6' : 'none' }};
        }

        .confidential-tag {
            color: #e53935;
            font-weight: 700;
            font-size: 11px;
            letter-spacing: 0.5px;
        }

        .badge-status {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 700;
        }

        .badge-final {
            background-color: #198754;
            color: #ffffff;
        }

        .badge-draft {
            background-color: #ffc107;
            color: #212529;
        }

        table.layout-table {
            width: 100%;
            border-collapse: collapse;
        }

        table.meta-table td {
            padding: 3px 0;
            font-size: 11.5px;
            vertical-align: top;
        }

        .meta-label {
            color: #495057;
            width: 125px;
        }

        .meta-sep {
            width: 12px;
            text-align: center;
        }

        .meta-val {
            font-weight: 600;
            color: #212529;
        }

        .section-header {
            font-size: 13px;
            font-weight: 700;
            color: #212529;
            border-bottom: 1.5px solid #dee2e6;
            padding-bottom: 5px;
            margin-bottom: 8px;
        }

        .sub-header {
            font-size: 10px;
            font-weight: 700;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 8px;
            margin-bottom: 4px;
        }

        table.items-table {
            width: 100%;
            border-collapse: collapse;
        }

        table.items-table td {
            padding: 3px 0;
            font-size: 11.5px;
            vertical-align: top;
            color: #495057;
        }

        table.items-table td.amt {
            text-align: right;
            font-weight: 600;
            color: #212529;
            white-space: nowrap;
        }

        table.items-table td.amt-danger {
            text-align: right;
            font-weight: 600;
            color: #dc3545;
            white-space: nowrap;
        }

        .total-line {
            border-top: 1px solid #dee2e6;
            padding-top: 7px !important;
            margin-top: 6px;
            font-weight: 700 !important;
            color: #212529 !important;
            font-size: 12.5px !important;
        }

        .thp-box {
            background-color: #ecfdf5;
            border: 1.5px solid #a7f3d0;
            border-radius: 6px;
            padding: 12px 16px;
            margin-top: 14px;
        }

        .thp-label {
            font-size: 14px;
            font-weight: 700;
            color: #064e3b;
        }

        .thp-amount {
            font-size: 18px;
            font-weight: 800;
            color: #047857;
            text-align: right;
        }

        .terbilang-box {
            margin-top: 8px;
            padding: 8px 12px;
            background-color: #f8f9fa;
            border-left: 3px solid #198754;
            font-size: 11px;
            color: #495057;
            font-style: italic;
        }

        .note-box {
            margin-top: 14px;
            padding: 9px 12px;
            background-color: #f8f9fa;
            border-radius: 4px;
            font-size: 11px;
            color: #495057;
        }

        .disclaimer-text {
            font-size: 10px;
            color: #6c757d;
            line-height: 1.45;
            margin-top: 16px;
            border-top: 1px solid #e9ecef;
            padding-top: 12px;
        }

        @media print {
            body {
                background: #ffffff;
            }
            .no-print {
                display: none !important;
            }
            .payslip-container {
                box-shadow: none;
                padding: 0;
                margin: 0;
                max-width: 100%;
            }
        }
    </style>
</head>

<body>

    @if (!$isPdf && !$isEmail)
        <!-- Top Action Bar -->
        <div class="container-fluid no-print py-2 bg-white border-bottom sticky-top shadow-sm">
            <div class="container d-flex justify-content-between align-items-center" style="max-width: 800px;">
                <button onclick="window.history.back()" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="fa fa-arrow-left me-1"></i> Kembali
                </button>
                <div class="fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                    <span>Payslip THR Detail</span>
                    @if (!empty($payslip['is_final']))
                        <span class="badge bg-success text-white fw-bold rounded-pill px-2 py-1" style="font-size: 0.72rem;">FINAL / APPROVED</span>
                    @else
                        <span class="badge bg-warning text-dark fw-bold rounded-pill px-2 py-1" style="font-size: 0.72rem;">{{ $payslip['status_badge'] }}</span>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    @if (in_array(strtoupper((string)($payslip['batch_status'] ?? '')), ['DRAFT', 'APPROVED'], true))
                        <button type="button" class="btn btn-sm {{ !empty($payslip['is_final']) ? 'btn-outline-success' : 'btn-outline-warning text-dark' }} rounded-pill px-3"
                            onclick="sendThrSlipEmail({{ (int)$payslip['batch_id'] }}, '{{ $payslip['nik'] }}', {{ !empty($payslip['is_final']) ? 'true' : 'false' }})">
                            <i class="fa fa-envelope me-1"></i> {{ !empty($payslip['is_final']) ? 'Kirim ke Email' : 'Kirim Preview (Draft)' }}
                        </button>
                    @endif
                    <a href="{{ url('payslip-thr/pdf') }}?batch_id={{ (int)$payslip['batch_id'] }}&nik={{ urlencode($payslip['nik']) }}"
                        class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        <i class="fa fa-file-pdf me-1"></i> Download PDF
                    </a>
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" onclick="window.print()">
                        <i class="fa fa-print me-1"></i> Print
                    </button>
                </div>
            </div>
        </div>
    @endif

    <div class="payslip-container">
        <!-- Header -->
        <table class="layout-table" style="margin-bottom: 12px;">
            <tr>
                <td style="vertical-align: middle;">
                    <table style="border-collapse: collapse;">
                        <tr>
                            <td style="padding-right: 10px; vertical-align: middle;">
                                <img src="{{ $logoSrc }}" alt="Logo" style="height: 44px;">
                            </td>
                            <td style="vertical-align: middle;">
                                <div style="font-size: 15px; font-weight: 700; color: #212529;">{{ $payslip['company_name'] }}</div>
                                <div style="font-size: 11px; color: #6c757d; font-weight: 600;">GREEN ENERGY UTAMA</div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="text-align: right; vertical-align: top;">
                    <div class="confidential-tag">*CONFIDENTIAL</div>
                    <div style="font-size: 17px; font-weight: 800; color: #064e3b; margin-top: 2px;">PAYSLIP THR</div>
                    <div style="margin-top: 3px;">
                        <span class="badge-status {{ !empty($payslip['is_final']) ? 'badge-final' : 'badge-draft' }}">
                            {{ $payslip['status_badge'] }}
                        </span>
                    </div>
                </td>
            </tr>
        </table>

        <hr style="border: 0; border-top: 1.5px solid #212529; margin: 10px 0 14px 0;">

        @if (empty($payslip['is_final']))
            <div style="background-color: #fff3cd; border: 1px solid #ffeeba; border-left: 4px solid #f59e0b; color: #664d03; padding: 9px 12px; border-radius: 4px; margin-bottom: 14px; font-size: 11px;">
                <strong>PERHATIAN — SLIP THR PREVIEW ({{ $payslip['status_badge'] }}):</strong>
                Dokumen ini dikirim untuk keperluan pengecekan data sebelum pengajuan approval resmi. Nominal masih dapat berubah apabila terdapat revisi sebelum disetujui.
            </div>
        @endif

        <!-- Metadata 2 Kolom -->
        <table class="layout-table" style="margin-bottom: 16px;">
            <tr>
                <td style="width: 50%; padding-right: 14px; vertical-align: top;">
                    <table class="layout-table meta-table">
                        <tr>
                            <td class="meta-label">Event THR</td>
                            <td class="meta-sep">:</td>
                            <td class="meta-val">{{ $payslip['event_name'] }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Periode / Tgl Acuan</td>
                            <td class="meta-sep">:</td>
                            <td class="meta-val">{{ $payslip['period_label'] }} ({{ $payslip['reference_date_formatted'] }})</td>
                        </tr>
                        <tr>
                            <td class="meta-label">ID / Nama</td>
                            <td class="meta-sep">:</td>
                            <td class="meta-val">{{ $payslip['id_name'] }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Departemen</td>
                            <td class="meta-sep">:</td>
                            <td class="meta-val">{{ $payslip['organization'] }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Jabatan</td>
                            <td class="meta-sep">:</td>
                            <td class="meta-val">{{ $payslip['job_position'] }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Grade / Level</td>
                            <td class="meta-sep">:</td>
                            <td class="meta-val">{{ $payslip['grade_level'] }}</td>
                        </tr>
                    </table>
                </td>
                <td style="width: 50%; padding-left: 14px; vertical-align: top;">
                    <table class="layout-table meta-table">
                        <tr>
                            <td class="meta-label">Join Date &amp; Tenure</td>
                            <td class="meta-sep">:</td>
                            <td class="meta-val">{{ $payslip['join_date'] }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Skema THR</td>
                            <td class="meta-sep">:</td>
                            <td class="meta-val">{{ $payslip['thr_scheme_label'] }} ({{ $payslip['prorate_percent'] }}%)</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Basis Upah THR</td>
                            <td class="meta-sep">:</td>
                            <td class="meta-val">Rp{{ number_format($payslip['base_salary_amount'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Status PTKP</td>
                            <td class="meta-sep">:</td>
                            <td class="meta-val">{{ $payslip['ptkp'] }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">NPWP</td>
                            <td class="meta-sep">:</td>
                            <td class="meta-val">{{ $payslip['npwp'] }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Email</td>
                            <td class="meta-sep">:</td>
                            <td class="meta-val">{{ $payslip['email'] ?: 'Belum diatur' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Penerimaan vs Potongan (2 Kolom) -->
        <table class="layout-table">
            <tr>
                <td style="width: 50%; padding-right: 14px; vertical-align: top;">
                    <div class="section-header">Penerimaan THR (Earnings)</div>
                    <table class="items-table">
                        @foreach ($payslip['earnings'] as $earn)
                            <tr>
                                <td>{{ $earn['name'] }}</td>
                                <td class="amt">Rp{{ number_format($earn['amount'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach

                        @if (!empty($payslip['additional_earnings']))
                            <tr>
                                <td colspan="2" class="sub-header">TUNJANGAN PAJAK</td>
                            </tr>
                            @foreach ($payslip['additional_earnings'] as $addEarn)
                                <tr>
                                    <td>{{ $addEarn['name'] }}</td>
                                    <td class="amt">Rp{{ number_format($addEarn['amount'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        @endif

                        <tr>
                            <td class="total-line">Total Penerimaan</td>
                            <td class="amt total-line">Rp{{ number_format($payslip['total_earnings'], 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </td>
                <td style="width: 50%; padding-left: 14px; vertical-align: top;">
                    <div class="section-header">Potongan Pajak (Deductions)</div>
                    <table class="items-table">
                        @forelse ($payslip['deductions'] as $ded)
                            <tr>
                                <td>{{ $ded['name'] }}</td>
                                <td class="amt-danger">Rp{{ number_format($ded['amount'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td style="color: #6c757d;">Tidak ada potongan PPh 21 (THR Utuh)</td>
                                <td class="amt" style="color: #6c757d;">Rp0</td>
                            </tr>
                        @endforelse
                        <tr>
                            <td class="total-line">Total Potongan</td>
                            <td class="amt-danger total-line">Rp{{ number_format($payslip['total_deductions'], 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Total THR Diterima -->
        <div class="thp-box">
            <table class="layout-table">
                <tr>
                    <td class="thp-label">Total THR Diterima (Take Home Pay)</td>
                    <td class="thp-amount">Rp{{ number_format($payslip['take_home_pay'], 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        @if (!empty($payslip['terbilang_id']))
            <div class="terbilang-box">
                <strong>Terbilang:</strong> {{ $payslip['terbilang_id'] }}
            </div>
        @endif

        <!-- Notes -->
        <div class="note-box">
            <strong>Note:</strong> {{ $payslip['note'] }}
        </div>

        <!-- Legal Disclaimer -->
        <div class="disclaimer-text">
            <p style="margin: 0 0 4px 0; font-weight: 700; color: #212529;">THIS IS A COMPUTER GENERATED PRINTOUT AND NO SIGNATURE IS REQUIRED</p>
            <p style="margin: 0;">
                PLEASE NOTE THAT THE CONTENTS OF THIS STATEMENT SHOULD BE TREATED WITH ABSOLUTE CONFIDENTIALITY.
                HARAP DIPERHATIKAN, ISI PERNYATAAN INI ADALAH RAHASIA KECUALI ANDA DIMINTA UNTUK MENGUNGKAPKANNYA UNTUK KEPERLUAN PAJAK ATAU HUKUM.
            </p>
            <table class="layout-table" style="margin-top: 10px; border-top: 1px solid #e9ecef; padding-top: 6px;">
                <tr>
                    <td style="color: #6c757d; font-size: 10px;">Generated by PT Green Energi Utama ERP (Standalone THR Module)</td>
                    <td style="color: #6c757d; font-size: 10px; text-align: right;">Ref: {{ $payslip['batch_no'] }}</td>
                </tr>
            </table>
        </div>
    </div>

    @if (!$isPdf && !$isEmail)
        <script src="{{ asset('adminor/plugins/jquery/jquery.min.js') }}"></script>
        <script>
            function sendThrSlipEmail(batchId, nik, isFinal) {
                var modeLabel = isFinal ? 'resmi (FINAL)' : 'PREVIEW (DRAFT)';
                if (!confirm('Kirim Payslip THR digital ' + modeLabel + ' ini ke email karyawan (' + nik + ')?')) return;

                $.ajax({
                    url: '{{ url('api/payroll/thr/send-payslip-email') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        batch_id: batchId,
                        nik: nik
                    },
                    success: function(res) {
                        if (res && res.success) {
                            alert('Payslip THR berhasil dikirimkan ke: ' + res.recipient);
                        } else {
                            alert('Gagal mengirim: ' + (res.message || 'Unknown error'));
                        }
                    },
                    error: function(xhr) {
                        var msg = (xhr.responseJSON && xhr.responseJSON.message)
                            ? xhr.responseJSON.message
                            : 'Terjadi kesalahan saat memproses pengiriman email.';
                        alert(msg);
                    }
                });
            }
        </script>
    @endif
</body>

</html>
