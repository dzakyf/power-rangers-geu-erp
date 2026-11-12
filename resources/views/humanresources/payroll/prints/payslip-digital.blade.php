<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip Detail - {{ $payslip['name'] }} ({{ $payslip['nik'] }})</title>
    <!-- Bootstrap 5 CSS -->
    <link href="{{ asset('adminor/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #333;
        }
        .payslip-container {
            max-width: 820px;
            margin: 25px auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            padding: 35px 40px;
        }
        .confidential-tag {
            color: #e53935;
            font-weight: 700;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }
        .meta-table td {
            padding: 3px 0;
            font-size: 0.88rem;
            vertical-align: top;
        }
        .meta-label {
            color: #495057;
            width: 130px;
        }
        .meta-separator {
            width: 15px;
            text-align: center;
        }
        .meta-val {
            font-weight: 500;
            color: #212529;
        }
        .section-header {
            font-size: 0.95rem;
            font-weight: 700;
            color: #212529;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }
        .sub-header {
            font-size: 0.75rem;
            font-weight: 700;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 10px;
            margin-bottom: 4px;
        }
        .item-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.88rem;
            padding: 3px 0;
            color: #495057;
        }
        .item-row .amount {
            font-weight: 500;
            color: #212529;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.95rem;
            font-weight: 700;
            color: #212529;
            border-top: 1px solid #dee2e6;
            padding-top: 8px;
            margin-top: 10px;
        }
        .thp-box {
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 12px 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 12px;
            border: 1px solid #e9ecef;
        }
        .thp-label {
            font-size: 1.05rem;
            font-weight: 700;
            color: #212529;
        }
        .thp-amount {
            font-size: 1.25rem;
            font-weight: 800;
            color: #198754;
        }
        .disclaimer-text {
            font-size: 0.72rem;
            color: #8c98a4;
            line-height: 1.45;
            margin-top: 20px;
            border-top: 1px solid #f1f3f5;
            padding-top: 15px;
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

    <!-- Top Action Bar -->
    <div class="container-fluid no-print py-2 bg-white border-bottom sticky-top shadow-sm">
        <div class="container d-flex justify-content-between align-items-center" style="max-width: 820px;">
            <button onclick="window.history.back()" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                <i class="fa fa-arrow-left me-1"></i> Kembali
            </button>
            <div class="fw-bold text-dark fs-6">
                Payslip Detail
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="sendSlipEmail('{{ $payslip['nik'] }}', '{{ $payslip['period'] }}')">
                    <i class="fa fa-envelope me-1"></i> Kirim ke Email
                </button>
                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" onclick="window.print()">
                    <i class="fa fa-download me-1"></i> Download / Print
                </button>
            </div>
        </div>
    </div>

    <!-- Main Payslip Card -->
    <div class="payslip-container">
        <!-- Header: Logo, Company & Confidential -->
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="d-flex align-items-center gap-2">
                <img src="{{ asset('assets/images/geu-logo-color.webp') }}" alt="Logo" style="height: 48px; object-fit: contain;">
                <div>
                    <h5 class="fw-bold mb-0 text-dark" style="letter-spacing: -0.3px;">{{ $payslip['company_name'] }}</h5>
                    <small class="text-muted fw-semibold">GENERAL ENERGY UTAMA GROUP</small>
                </div>
            </div>
            <div class="text-end">
                <div class="confidential-tag mb-1">*CONFIDENTIAL</div>
                <div class="fw-bold text-dark fs-5" style="letter-spacing: 0.5px;">PAYSLIP</div>
            </div>
        </div>

        <hr class="my-3" style="border-top: 1.5px solid #212529; opacity: 0.85;">

        <!-- Metadata Section (2 Kolom) -->
        <div class="row g-2 mb-4">
            <div class="col-md-6">
                <table class="w-100 meta-table">
                    <tr>
                        <td class="meta-label">Payroll cut off</td>
                        <td class="meta-separator">:</td>
                        <td class="meta-val">{{ $payslip['payroll_cutoff'] }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">ID / Name</td>
                        <td class="meta-separator">:</td>
                        <td class="meta-val">{{ $payslip['id_name'] }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Organization</td>
                        <td class="meta-separator">:</td>
                        <td class="meta-val">{{ $payslip['organization'] }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Grade / Level</td>
                        <td class="meta-separator">:</td>
                        <td class="meta-val">{{ $payslip['grade_level'] }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="w-100 meta-table">
                    <tr>
                        <td class="meta-label">Job position</td>
                        <td class="meta-separator">:</td>
                        <td class="meta-val">{{ $payslip['job_position'] }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">PTKP</td>
                        <td class="meta-separator">:</td>
                        <td class="meta-val">{{ $payslip['ptkp'] }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">NPWP</td>
                        <td class="meta-separator">:</td>
                        <td class="meta-val">{{ $payslip['npwp'] }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Join date</td>
                        <td class="meta-separator">:</td>
                        <td class="meta-val">{{ $payslip['join_date'] }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Earnings & Deductions (2 Kolom Bersanding) -->
        <div class="row g-4 mb-3">
            <!-- Kolom Kiri: Earnings -->
            <div class="col-md-6">
                <div class="section-header border-bottom">Earnings</div>
                
                @foreach($payslip['earnings'] as $earn)
                    <div class="item-row">
                        <span>{{ $earn['name'] }}</span>
                        <span class="amount">{{ number_format($earn['amount'], 0, ',', '.') }}</span>
                    </div>
                @endforeach

                @if(!empty($payslip['additional_earnings']))
                    <div class="sub-header">ADDITIONAL EARNINGS</div>
                    @foreach($payslip['additional_earnings'] as $addEarn)
                        <div class="item-row">
                            <span>{{ $addEarn['name'] }}</span>
                            <span class="amount">{{ number_format($addEarn['amount'], 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                @endif

                <div class="total-row">
                    <span>Total earnings</span>
                    <span>Rp{{ number_format($payslip['total_earnings'], 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Kolom Kanan: Deductions -->
            <div class="col-md-6">
                <div class="section-header border-bottom">Deductions</div>

                @forelse($payslip['deductions'] as $deduct)
                    <div class="item-row">
                        <span>{{ $deduct['name'] }}</span>
                        <span class="amount text-danger">{{ number_format($deduct['amount'], 0, ',', '.') }}</span>
                    </div>
                @empty
                    <div class="item-row text-muted"><span>Tidak ada potongan standar</span><span class="amount">0</span></div>
                @endforelse

                @if(!empty($payslip['additional_deductions']))
                    <div class="sub-header">ADDITIONAL DEDUCTIONS</div>
                    @foreach($payslip['additional_deductions'] as $addDed)
                        <div class="item-row">
                            <span>{{ $addDed['name'] }}</span>
                            <span class="amount text-danger">{{ number_format($addDed['amount'], 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                @endif

                <div class="total-row">
                    <span>Total deductions</span>
                    <span class="text-danger">Rp{{ number_format($payslip['total_deductions'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Take Home Pay Box -->
        <div class="thp-box">
            <span class="thp-label">Take Home Pay</span>
            <span class="thp-amount">Rp{{ number_format($payslip['take_home_pay'], 0, ',', '.') }}</span>
        </div>

        <!-- Row Bawah: Benefits* & Attendance Summary -->
        <div class="row g-4 mt-2">
            <!-- Kolom Kiri: Benefits* (Porsi Perusahaan) -->
            <div class="col-md-6">
                <div class="section-header border-bottom">Benefits*</div>
                @foreach($payslip['benefits'] as $b)
                    <div class="item-row text-muted">
                        <span>{{ $b['name'] }}</span>
                        <span class="amount">{{ number_format($b['amount'], 0, ',', '.') }}</span>
                    </div>
                @endforeach
                <div class="total-row">
                    <span class="text-muted">Total benefits</span>
                    <span>Rp{{ number_format($payslip['total_benefits'], 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Kolom Kanan: Attendance Summary -->
            <div class="col-md-6">
                <div class="section-header border-bottom">Attendance Summary</div>
                <div class="item-row">
                    <span>Actual Working Day</span>
                    <span class="amount">{{ $payslip['attendance']['actual_working_days'] }}</span>
                </div>
                <div class="item-row">
                    <span>Schedule Working Day</span>
                    <span class="amount">{{ $payslip['attendance']['schedule_working_days'] }}</span>
                </div>
                <div class="item-row">
                    <span>Dayoff</span>
                    <span class="amount">{{ $payslip['attendance']['dayoff'] }}</span>
                </div>
                <div class="item-row">
                    <span>National Holiday</span>
                    <span class="amount">{{ $payslip['attendance']['national_holiday'] }}</span>
                </div>
                <div class="item-row">
                    <span>Attendance / Time Off Code</span>
                    <span class="amount badge bg-secondary text-white">{{ $payslip['attendance']['attendance_code'] }}</span>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="mt-3 p-2 bg-light rounded text-muted small">
            <strong>Note:</strong> {{ $payslip['note'] }}
        </div>

        <!-- Legal Disclaimer & Footer -->
        <div class="disclaimer-text">
            <p class="mb-1 text-muted">*These are the benefits you'll get from the company, but not included in your take-home pay (THP).</p>
            <p class="mb-1 fw-bold text-dark">THIS IS A COMPUTER GENERATED PRINTOUT AND NO SIGNATURE IS REQUIRED</p>
            <p class="mb-0 text-muted">
                PLEASE NOTE THAT THE CONTENTS OF THIS STATEMENT SHOULD BE TREATED WITH ABSOLUTE CONFIDENTIALITY.
                HARAP DIPERHATIKAN, ISI PERNYATAAN INI ADALAH RAHASIA KECUALI ANDA DIMINTA UNTUK MENGUNGKAPKANNYA UNTUK KEPERLUAN PAJAK ATAU HUKUM.
            </p>
            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                <span class="text-muted small">This payslip is generated by PT Green Energi Utama ERP</span>
                <span class="text-muted small">Ref: {{ $payslip['task_id'] }}</span>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('adminor/plugins/jquery/jquery.min.js') }}"></script>
    <script>
        function sendSlipEmail(nik, period) {
            if (!confirm('Kirim slip gaji digital ini ke email karyawan?')) return;
            
            var btn = event.target;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Mengirim...';

            $.ajax({
                url: '{{ url('api/payroll/send-payslip-email') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    nik: nik,
                    period: period
                },
                success: function(res) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa fa-envelope me-1"></i> Kirim ke Email';
                    if (res.success) {
                        alert('Slip gaji berhasil dikirimkan ke: ' + res.recipient);
                    } else {
                        alert('Gagal mengirim: ' + res.message);
                    }
                },
                error: function(xhr) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa fa-envelope me-1"></i> Kirim ke Email';
                    alert('Terjadi kesalahan saat memproses pengiriman email.');
                }
            });
        }
    </script>
</body>
</html>

