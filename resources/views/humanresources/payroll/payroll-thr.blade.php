@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    @csrf

    <style>
        .thr-kpi-card {
            border: 1px solid rgba(226, 232, 240, 0.85);
            border-radius: 14px;
            background: #ffffff;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .thr-kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06) !important;
        }

        .thr-kpi-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        .thr-form-label {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #475569;
            margin-bottom: 0.35rem;
        }

        #modalBlastThrEmail .thr-blast-check-cell {
            width: 42px;
            text-align: center;
            vertical-align: middle;
        }

        #modalBlastThrEmail .thr-blast-checkbox {
            position: static !important;
            margin: 0 !important;
            vertical-align: middle;
            cursor: pointer;
            width: 16px;
            height: 16px;
        }

        #blastThrRecipientList tr.thr-blast-row-clickable {
            cursor: pointer;
        }

        #blastThrRecipientList tr.thr-blast-row-disabled {
            background-color: #f8fafc;
            opacity: 0.78;
            cursor: not-allowed;
        }
    </style>

    <!-- Main Content Header -->
    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
        <div class="mb-2 mb-md-0">
            <h4 class="content-title text-white fw-bold mb-1"
                style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
                <i class="fe fe-gift me-2"></i>Perhitungan THR Mandiri (Tunjangan Hari Raya)
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ url('payroll-thr') }}" style="color: rgba(255, 255, 255, 0.8) !important;">
                            THR &amp; Bonus
                        </a>
                    </li>
                    <li class="breadcrumb-item active text-white" aria-current="page"
                        style="color: #ffffff !important; font-weight: 600;">
                        Perhitungan &amp; Payslip THR
                    </li>
                </ol>
            </nav>
        </div>
        <div class="d-flex align-items-center flex-wrap gap-2">
            <a href="{{ url('payroll-thr-approval') }}" class="btn btn-sm rounded-pill shadow-sm position-relative"
                style="background: rgba(255, 255, 255, 0.25); border: 1px solid rgba(255, 255, 255, 0.6); color: #ffffff !important;">
                <i class="fe fe-check-circle me-1"></i> Approval THR
                @if (!empty($pendingApprovalCount) && $pendingApprovalCount > 0)
                    <span class="badge bg-warning text-dark rounded-pill ms-1">{{ $pendingApprovalCount }}</span>
                @endif
            </a>
            <a href="{{ url('payroll-tax') }}" class="btn btn-sm rounded-pill shadow-sm"
                style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
                <i class="fe fe-percent me-1"></i> Master Tax &amp; TER
            </a>
            <a href="{{ url('payroll-employee') }}" class="btn btn-sm rounded-pill shadow-sm"
                style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
                <i class="fe fe-users me-1"></i> Setup Gaji Pegawai
            </a>
        </div>
    </div>

    <div class="alert alert-info border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center gap-2">
        <i class="fe fe-gift fs-5 text-info"></i>
        <div class="small">
            <strong>Modul THR Mandiri &amp; Payslip Terpisah:</strong> THR diproses dengan siklus batch, approval
            (<code>PAYROLL_THR</code>), dan Digital Payslip tersendiri (tidak digabungkan ke Payroll Gaji Bulanan). Setelah
            berstatus <span class="badge bg-success text-white">APPROVED</span>, Anda dapat menerbitkan Payslip THR Final,
            melakukan Blasting Email massal, dan Export Rekap Transfer Bank.
        </div>
    </div>

    <!-- Form Generate Draft THR -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-bold text-dark">
                <i class="fe fe-sliders me-2 text-primary"></i>Parameter Generate Draft THR
            </h6>
        </div>
        <div class="card-body">
            <form id="thrGenerateForm" class="row g-3">
                <input type="hidden" name="religion_filter" id="thr_religion_filter" value="ALL">

                <div class="col-md-2">
                    <label class="thr-form-label">Periode THR</label>
                    <input type="month" name="salary_period" id="thr_salary_period" class="form-control"
                        value="{{ $defaultPeriod }}" required>
                </div>
                <div class="col-md-3">
                    <label class="thr-form-label">Nama Event THR</label>
                    <input type="text" name="thr_event_name" id="thr_event_name" class="form-control"
                        placeholder="Misal: THR Idul Fitri 1447H" value="THR Tahun {{ date('Y') }}" required>
                </div>
                <div class="col-md-2">
                    <label class="thr-form-label">Tgl Acuan Hari Raya</label>
                    <input type="date" name="reference_date" id="thr_reference_date" class="form-control"
                        value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-5">
                    <label class="thr-form-label">Salary Group (Opsional)</label>
                    <select name="main_id_filter" id="thr_main_id_filter" class="form-select">
                        <option value="">-- Semua Salary Group --</option>
                        @foreach ($mainGroups as $mg)
                            <option value="{{ $mg->Main_ID }}">{{ $mg->Main_ID }} - {{ $mg->Group_Name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="thr-form-label">Skema Perhitungan THR</label>
                    <select name="prorate_method" id="thr_prorate_method" class="form-select">
                        <option value="FOLLOW_EMPLOYEE" selected>Ikuti Settingan Employee (Employee Lists)</option>
                        <option value="DEFAULT_PERMENAKER">Default (Standar Permenaker)</option>
                        @foreach ($thrSchemes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4" id="thrFallbackSchemeWrapper">
                    <label class="thr-form-label">Jika Employee Belum Disetting</label>
                    <select name="fallback_thr_scheme" id="thr_fallback_scheme" class="form-select">
                        <option value="DEFAULT_PERMENAKER" selected>Default (Standar Permenaker)</option>
                        @foreach ($thrSchemes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="thr-form-label">Mode PPh 21 THR</label>
                    <select name="tax_mode" id="thr_tax_mode" class="form-select">
                        <option value="NONE" selected>Tanpa Potongan PPh 21 (THR Utuh)</option>
                        <option value="TER_STANDALONE">Otomatis Hitung PPh 21 TER (PP 58/2023)</option>
                    </select>
                </div>

                <div class="col-md-9">
                    <label class="thr-form-label">Catatan Batch (Opsional)</label>
                    <input type="text" name="note" id="thr_note" class="form-control"
                        placeholder="Catatan internal HCGA...">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" id="btnGenerateThr" class="btn btn-primary w-100 fw-bold rounded-3">
                        <i class="fe fe-zap me-1"></i> Hitung &amp; Buat Draft THR
                    </button>
                </div>
            </form>

            <div id="thrGenerateStatusBox" class="d-none">
                <div id="thrGenerateStatusAlert" class="alert alert-info border-0 shadow-sm rounded-3 mb-0 mt-3"
                    role="alert">
                    <div class="d-flex align-items-start justify-content-between gap-2">
                        <div class="d-flex align-items-start gap-2 flex-grow-1">
                            <div id="thrGenerateStatusIcon" class="mt-1">
                                <span class="spinner-border spinner-border-sm text-info" role="status"
                                    aria-hidden="true"></span>
                            </div>
                            <div class="flex-grow-1">
                                <div id="thrGenerateStatusTitle" class="fw-bold small">Memproses Draft THR...</div>
                                <div id="thrGenerateStatusDetail" class="small mt-1"></div>
                                <div id="thrGenerateProgressWrap" class="progress mt-2"
                                    style="height: 6px; border-radius: 4px;">
                                    <div id="thrGenerateProgressBar"
                                        class="progress-bar progress-bar-striped progress-bar-animated bg-info"
                                        role="progressbar" style="width: 35%"></div>
                                </div>
                            </div>
                        </div>
                        <button type="button" id="thrGenerateStatusClose" class="btn-close btn-sm d-none"
                            aria-label="Close"
                            onclick="document.getElementById('thrGenerateStatusBox').classList.add('d-none');"></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Batch Selector & Tax Mode Card -->
    <div class="card thr-kpi-card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-lg-6">
                    <label class="thr-form-label">Pilih Batch THR</label>
                    <select id="activeBatchSelect" class="form-select">
                        <option value="">-- Pilih Batch THR --</option>
                        @foreach ($batches as $b)
                            <option value="{{ $b->id }}" data-status="{{ $b->status }}"
                                data-period="{{ $b->salary_period }}" data-event="{{ $b->thr_event_name }}"
                                data-emp="{{ $b->total_employees }}" data-amt="{{ $b->total_amount }}"
                                data-pph21="{{ $b->total_pph21 ?? 0 }}"
                                data-net="{{ ($b->total_net_amount ?? 0) > 0 ? $b->total_net_amount : $b->total_amount }}"
                                data-tax-mode="{{ strtoupper($b->tax_mode ?? 'NONE') }}"
                                data-reject-reason="{{ $b->rejection_reason ?? '' }}">
                                {{ $b->batch_no }} | {{ $b->salary_period }} | {{ $b->thr_event_name }}
                                [{{ $b->status }}]
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-4">
                    <label class="thr-form-label">Mode Potongan PPh 21 Batch Aktif</label>
                    <select id="activeBatchTaxMode" class="form-select" disabled>
                        <option value="NONE">Tanpa Potongan PPh 21 (THR Utuh)</option>
                        <option value="TER_STANDALONE">Otomatis PPh 21 TER (PP 58/2023)</option>
                    </select>
                </div>
                <div class="col-lg-2 d-flex flex-column align-items-lg-end justify-content-center">
                    <span class="small text-muted mb-1">Status Batch</span>
                    <span id="batchStatusBadge" class="badge bg-secondary text-white px-3 py-2">-</span>
                </div>
            </div>
            <div id="batchRejectAlert" class="alert alert-danger border-0 rounded-3 mt-3 mb-0 small d-none">
                <i class="fe fe-alert-triangle me-1"></i>
                <strong>Batch Ditolak:</strong> <span id="batchRejectReasonText"></span>
            </div>
        </div>
    </div>

    <!-- KPI Row (4 Cards) -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card thr-kpi-card h-100 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="thr-kpi-icon bg-primary text-white">
                        <i class="fe fe-users"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Karyawan Eligible</div>
                        <div id="kpiTotalEmp" class="fs-4 fw-bold text-dark">0</div>
                        <div id="kpiPeriodLabel" class="small text-muted">Periode: -</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card thr-kpi-card h-100 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="thr-kpi-icon bg-info text-white">
                        <i class="fe fe-box"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Total Bruto THR</div>
                        <div id="kpiTotalAmount" class="fs-4 fw-bold text-dark">Rp 0</div>
                        <div class="small text-muted">Sistem + Adjustment (+/-)</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card thr-kpi-card h-100 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="thr-kpi-icon bg-danger text-white">
                        <i class="fe fe-percent"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Total PPh 21 THR</div>
                        <div id="kpiTotalPph21" class="fs-4 fw-bold text-danger">Rp 0</div>
                        <div id="kpiTaxModeLabel" class="small text-muted">Mode: Tanpa Potongan PPh 21</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card thr-kpi-card h-100 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="thr-kpi-icon bg-success text-white">
                        <i class="fe fe-dollar-sign"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Total Nett THR (THP)</div>
                        <div id="kpiTotalNetAmount" class="fs-4 fw-bold text-success">Rp 0</div>
                        <div class="small text-muted">Total Transfer Bersih Karyawan</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grid Rincian Karyawan THR -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div
            class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="fe fe-list me-2 text-primary"></i>Rincian Perhitungan THR Karyawan
                </h6>
                <small class="text-muted">Pada status <strong>DRAFT</strong>, Anda dapat mengubah
                    <em>Eligible</em>, <em>Skema THR</em>, <em>Adjustment (+/-)</em>, dan <em>Keterangan</em> langsung di
                    tabel. <strong>Klik ganda (double-click)</strong> baris untuk membuka Digital Payslip THR.</small>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" id="btnSubmitApproval" class="btn btn-primary btn-sm fw-bold rounded-pill px-3"
                    disabled>
                    <i class="fe fe-send me-1"></i> Ajukan Approval
                </button>
                <button type="button" id="btnRevertDraft"
                    class="btn btn-warning btn-sm fw-bold rounded-pill px-3 text-dark" disabled>
                    <i class="fe fe-rotate-ccw me-1"></i> Kembalikan ke Draft
                </button>
                <button type="button" id="btnDeleteBatch"
                    class="btn btn-outline-danger btn-sm fw-bold rounded-pill px-3" disabled>
                    <i class="fe fe-trash-2 me-1"></i> Hapus Draft
                </button>
                <button type="button" id="btnOpenSlipThr"
                    class="btn btn-outline-primary btn-sm fw-bold rounded-pill px-3" disabled>
                    <i class="fe fe-file-text me-1"></i> Buka Slip THR
                </button>
                <button type="button" id="btnBlastThrEmail"
                    class="btn btn-success btn-sm fw-bold rounded-pill px-3 text-white" disabled>
                    <i class="fe fe-mail me-1"></i> Blasting Email Slip THR
                </button>
                <button type="button" id="btnExportThrExcel"
                    class="btn btn-outline-success btn-sm fw-bold rounded-pill px-3" disabled>
                    <i class="fa fa-file-excel me-1"></i> Export Excel Transfer Bank
                </button>
            </div>
        </div>
        <div class="card-body p-2 position-relative">
            <div id="thrGridLoadingOverlay"
                class="position-absolute top-0 start-0 w-100 h-100 align-items-center justify-content-center"
                style="display: none; background: rgba(255, 255, 255, 0.78); z-index: 20;">
                <div class="bg-white border shadow-sm rounded-3 px-4 py-3 d-flex align-items-center gap-2">
                    <span class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true"></span>
                    <span class="small fw-semibold text-dark">Memuat data rincian karyawan THR...</span>
                </div>
            </div>
            <div id="thrGridBox" style="width: 100%; height: 480px;"></div>
        </div>
    </div>

    <!-- Modal Blasting Email Payslip THR (Draft Preview & Final Approved) -->
    <div class="modal fade" id="modalBlastThrEmail" tabindex="-1" aria-labelledby="modalBlastThrEmailLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header bg-success text-white rounded-top-4" id="blastThrModalHeader">
                    <h5 class="modal-title fw-bold text-white" id="modalBlastThrEmailLabel">
                        <i class="fa fa-paper-plane me-2"></i>Blasting Email Payslip THR
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info small rounded-3 mb-3" id="blastThrModeAlert">
                        <i class="fa fa-info-circle me-1"></i>
                        Mengirimkan Payslip THR digital secara massal ke alamat email karyawan eligible.
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="p-2 border rounded-3 bg-light">
                                <small class="text-muted d-block fw-semibold">Batch THR:</small>
                                <strong id="blastThrBatchNo" class="text-dark">-</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-2 border rounded-3 bg-light">
                                <small class="text-muted d-block fw-semibold">Event THR:</small>
                                <strong id="blastThrEventName" class="text-dark">-</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-2 border rounded-3 bg-light">
                                <small class="text-muted d-block fw-semibold">Target Penerima:</small>
                                <strong id="blastThrTargetCount" class="text-success fs-6">0 Orang</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Seleksi Penerima -->
                    <div id="blastThrSelectorWrapper" class="border rounded-3 p-3 mb-3 bg-light">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                            <div class="small fw-bold text-dark">
                                <i class="fa fa-users me-1 text-primary"></i> Pilih Penerima Email
                                (<span id="blastThrSelectedCount" class="text-primary">0</span> terpilih)
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <input type="text" id="blastThrSearchInput" class="form-control form-control-sm"
                                    placeholder="Cari NIK / Nama / Dept..." style="width: 210px;">
                                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-2 py-1"
                                    id="btnBlastSelectAll">Pilih Semua</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-2 py-1"
                                    id="btnBlastUnselectAll">Batal Pilih</button>
                            </div>
                        </div>
                        <div class="table-responsive bg-white border rounded-3" style="max-height: 190px; overflow-y: auto; position: relative;">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light sticky-top small">
                                    <tr>
                                        <th class="thr-blast-check-cell">
                                            <input type="checkbox" class="thr-blast-checkbox" id="blastThrMasterCheck" checked>
                                        </th>
                                        <th>NIK</th>
                                        <th>Nama Karyawan</th>
                                        <th>Department</th>
                                        <th>Email</th>
                                        <th class="text-end">Nett THR</th>
                                    </tr>
                                </thead>
                                <tbody id="blastThrRecipientList" class="small"></tbody>
                            </table>
                        </div>
                    </div>

                    <div id="blastThrProgressWrapper" class="mb-3" style="display: none;">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span id="blastThrProgressText">Memulai pengiriman email...</span>
                            <span id="blastThrProgressPercent" class="fw-bold text-success">0%</span>
                        </div>
                        <div class="progress" style="height: 12px; border-radius: 6px;">
                            <div id="blastThrProgressBar"
                                class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                role="progressbar" style="width: 0%"></div>
                        </div>
                    </div>

                    <div class="table-responsive border rounded-3" style="max-height: 260px; overflow-y: auto;">
                        <table class="table table-sm table-hover align-middle mb-0" id="blastThrResultsTable">
                            <thead class="table-light sticky-top">
                                <tr class="small text-muted">
                                    <th style="width: 40px;" class="text-center">#</th>
                                    <th>NIK</th>
                                    <th>Nama Karyawan</th>
                                    <th>Email</th>
                                    <th style="width: 100px;" class="text-center">Status</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody id="blastThrResultsBody" class="small">
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        Klik tombol "Kirim Blasting THR Sekarang" untuk memulai pengiriman email.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal"
                        id="btnCloseBlastThr">Tutup</button>
                    <button type="button" class="btn btn-success rounded-pill px-4 fw-bold text-white shadow-sm"
                        id="btnStartBlastThr">
                        <i class="fa fa-paper-plane me-1"></i> Kirim Blasting THR Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function() {
            var thrGrid = null;
            var currentBatchId = 0;
            var currentBatchStatus = '';
            var thrSchemes = @json($thrSchemes);

            function formatRp(val) {
                var num = parseFloat(val) || 0;
                return 'Rp ' + Math.round(num).toLocaleString('id-ID');
            }

            function getCsrfToken() {
                var el = document.querySelector('input[name="_token"]');
                return el ? el.value : '';
            }

            function escapeHtml(str) {
                return String(str == null ? '' : str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            function initThrGrid() {
                thrGrid = new dhtmlXGridObject('thrGridBox');
                thrGrid.setImagePath("{{ asset('dhtmlx/imgs/') }}/");
                thrGrid.setHeader(
                    "Eligible,NIK,Nama Karyawan,Department,Agama,Tgl Masuk,Masa Kerja,Skema THR (PKWT/MoU),Prorata,Basis Upah,THR Sistem,Adjustment (+/-),THR Bruto,PTKP,PPh 21 THR,Nett THR (THP),Keterangan"
                );
                thrGrid.attachHeader(
                    "#master_checkbox,#text_filter,#text_filter,#select_filter,#select_filter,#text_filter,#text_filter,#select_filter,#text_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#select_filter,#numeric_filter,#numeric_filter,#text_filter"
                );
                thrGrid.setInitWidths("65,100,180,115,85,95,120,200,70,110,110,110,115,65,105,115,260");
                thrGrid.setColAlign(
                    "center,left,left,left,center,center,center,left,right,right,right,right,right,center,right,right,left"
                );
                thrGrid.setColTypes("ch,ro,ro,ro,ro,ro,ro,coro,ro,ron,ron,edn,ron,ro,ron,ron,ed");
                thrGrid.setColSorting("int,str,str,str,str,str,str,str,str,int,int,int,int,str,int,int,str");
                thrGrid.setNumberFormat("0,000", 9, ".", ",");
                thrGrid.setNumberFormat("0,000", 10, ".", ",");
                thrGrid.setNumberFormat("0,000", 11, ".", ",");
                thrGrid.setNumberFormat("0,000", 12, ".", ",");
                thrGrid.setNumberFormat("0,000", 14, ".", ",");
                thrGrid.setNumberFormat("0,000", 15, ".", ",");

                var schemeCombo = thrGrid.getCombo(7);
                schemeCombo.put("DEFAULT_PERMENAKER", "Default (Standar Permenaker)");
                Object.keys(thrSchemes || {}).forEach(function(k) {
                    schemeCombo.put(k, thrSchemes[k]);
                });

                thrGrid.setSkin("dhx_skyblue");
                thrGrid.init();

                thrGrid.attachEvent("onCheck", function(rId, cInd, state) {
                    if (cInd === 0) {
                        if (currentBatchStatus !== 'DRAFT') {
                            thrGrid.cells(rId, 0).setValue(state ? 0 : 1);
                            return;
                        }
                        saveRowItem(rId, {
                            is_eligible: state ? 1 : 0
                        });
                    }
                });

                thrGrid.attachEvent("onEditCell", function(stage, rId, cInd, nValue, oValue) {
                    if (currentBatchStatus !== 'DRAFT') {
                        return false;
                    }
                    if (stage === 2 && nValue != oValue) {
                        if (cInd === 7) {
                            saveRowItem(rId, {
                                thr_scheme: nValue
                            });
                        } else if (cInd === 11) {
                            var parsed = parseFloat(String(nValue).replace(/,/g, '').trim()) || 0;
                            saveRowItem(rId, {
                                thr_adjustment_amount: parsed
                            });
                        } else if (cInd === 16) {
                            saveRowItem(rId, {
                                note: nValue
                            });
                        }
                    }
                    return true;
                });

                thrGrid.attachEvent("onRowDblClicked", function(rId) {
                    openThrPayslipForRow(rId);
                    return true;
                });
            }

            function openThrPayslipForRow(rId) {
                if (!currentBatchId) {
                    alert('Pilih Batch THR terlebih dahulu.');
                    return;
                }
                var rowId = rId || thrGrid.getSelectedRowId();
                if (!rowId) {
                    alert('Pilih salah satu baris karyawan pada tabel untuk membuka Slip THR.');
                    return;
                }
                var nik = thrGrid.getUserData(rowId, 'nik') || thrGrid.cells(rowId, 1).getValue();
                if (!nik) {
                    alert('NIK karyawan tidak ditemukan.');
                    return;
                }
                window.open("{{ url('/payslip-thr') }}?batch_id=" + currentBatchId + "&nik=" + encodeURIComponent(nik),
                    '_blank');
            }

            function syncSelectedOptionSummary(data) {
                var sel = document.getElementById('activeBatchSelect');
                if (!sel || sel.selectedIndex < 0) return;
                var opt = sel.options[sel.selectedIndex];
                if (!opt || parseInt(opt.value, 10) !== currentBatchId) return;

                if (typeof data.total_employees !== 'undefined') {
                    opt.setAttribute('data-emp', data.total_employees);
                }
                if (typeof data.total_amount !== 'undefined') {
                    opt.setAttribute('data-amt', data.total_amount);
                }
                if (typeof data.total_pph21 !== 'undefined') {
                    opt.setAttribute('data-pph21', data.total_pph21);
                }
                if (typeof data.total_net_amount !== 'undefined') {
                    opt.setAttribute('data-net', data.total_net_amount);
                }
            }

            function saveRowItem(itemId, payload) {
                if (currentBatchStatus !== 'DRAFT') {
                    alert('Hanya batch berstatus DRAFT yang dapat diubah.');
                    loadBatchItems(currentBatchId);
                    return;
                }
                payload.item_id = itemId;
                payload._token = getCsrfToken();

                fetch("{{ url('/api/payroll/thr/item/update') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken()
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(function(r) {
                        return r.json();
                    })
                    .then(function(res) {
                        if (!res.success) {
                            alert(res.message || 'Gagal memperbarui item.');
                            loadBatchItems(currentBatchId);
                            return;
                        }
                        if (res.data) {
                            if (typeof res.data.is_eligible !== 'undefined') {
                                thrGrid.cells(itemId, 0).setValue(res.data.is_eligible ? 1 : 0);
                            }
                            if (typeof res.data.prorate_ratio !== 'undefined') {
                                var pct = Math.round(parseFloat(res.data.prorate_ratio) * 10000) / 100;
                                thrGrid.cells(itemId, 8).setValue(pct + '%');
                            }
                            if (typeof res.data.base_salary_amount !== 'undefined') {
                                thrGrid.cells(itemId, 9).setValue(res.data.base_salary_amount);
                            }
                            if (typeof res.data.thr_calculated_amount !== 'undefined') {
                                thrGrid.cells(itemId, 10).setValue(res.data.thr_calculated_amount);
                            }
                            thrGrid.cells(itemId, 12).setValue(res.data.thr_final_amount);
                            if (typeof res.data.ptkp_status !== 'undefined' && res.data.ptkp_status) {
                                thrGrid.cells(itemId, 13).setValue(res.data.ptkp_status);
                            }
                            if (typeof res.data.thr_pph21_amount !== 'undefined') {
                                thrGrid.cells(itemId, 14).setValue(res.data.thr_pph21_amount);
                            }
                            if (typeof res.data.thr_net_amount !== 'undefined') {
                                thrGrid.cells(itemId, 15).setValue(res.data.thr_net_amount);
                            }
                            updateKpi(
                                res.data.total_employees,
                                res.data.total_amount,
                                res.data.total_pph21,
                                res.data.total_net_amount
                            );
                            syncSelectedOptionSummary(res.data);
                        }
                    })
                    .catch(function() {
                        alert('Terjadi kesalahan jaringan saat menyimpan item.');
                    });
            }

            function updateKpi(empCount, totalAmt, totalPph21, totalNet, taxMode) {
                document.getElementById('kpiTotalEmp').textContent = empCount || 0;
                document.getElementById('kpiTotalAmount').textContent = formatRp(totalAmt || 0);
                document.getElementById('kpiTotalPph21').textContent = formatRp(totalPph21 || 0);
                var effectiveNet = (typeof totalNet !== 'undefined' && totalNet !== null) ? totalNet : totalAmt;
                document.getElementById('kpiTotalNetAmount').textContent = formatRp(effectiveNet || 0);

                if (taxMode) {
                    document.getElementById('kpiTaxModeLabel').textContent =
                        (taxMode === 'TER_STANDALONE') ? 'Mode: PPh 21 TER (PP 58/2023)' :
                        'Mode: Tanpa Potongan PPh 21';
                }
            }

            function updateActionButtons(status, taxMode, rejectReason) {
                currentBatchStatus = (status || '').toUpperCase();
                var badge = document.getElementById('batchStatusBadge');
                badge.textContent = currentBatchStatus || '-';

                var badgeCls = 'badge px-3 py-2 ';
                if (currentBatchStatus === 'APPROVED') {
                    badgeCls += 'bg-success text-white';
                } else if (currentBatchStatus === 'DRAFT') {
                    badgeCls += 'bg-warning text-dark';
                } else if (currentBatchStatus === 'REJECTED' || currentBatchStatus === 'CANCELLED' ||
                    currentBatchStatus === 'CANCELED') {
                    badgeCls += 'bg-danger text-white';
                } else if (currentBatchStatus !== '') {
                    badgeCls += 'bg-primary text-white';
                } else {
                    badgeCls += 'bg-secondary text-white';
                }
                badge.className = badgeCls;

                var hasBatch = (currentBatchId > 0);
                var isDraft = (currentBatchStatus === 'DRAFT');
                var isApproved = (currentBatchStatus === 'APPROVED');
                var canRevert = (currentBatchStatus === 'REJECTED' || currentBatchStatus === 'CANCELLED' ||
                    currentBatchStatus === 'CANCELED' || currentBatchStatus === 'LOCKED');

                document.getElementById('btnSubmitApproval').disabled = !isDraft;
                document.getElementById('btnRevertDraft').disabled = !canRevert;
                document.getElementById('btnDeleteBatch').disabled = !isDraft;
                document.getElementById('btnOpenSlipThr').disabled = !hasBatch;
                var btnBlast = document.getElementById('btnBlastThrEmail');
                btnBlast.disabled = !(isDraft || isApproved);
                if (isDraft) {
                    btnBlast.className = 'btn btn-warning btn-sm fw-bold rounded-pill px-3 text-dark';
                    btnBlast.innerHTML = '<i class="fe fe-mail me-1"></i> Blasting Preview Slip THR (Draft)';
                } else {
                    btnBlast.className = 'btn btn-success btn-sm fw-bold rounded-pill px-3 text-white';
                    btnBlast.innerHTML = '<i class="fe fe-mail me-1"></i> Blasting Email Slip THR';
                }
                document.getElementById('btnExportThrExcel').disabled = !hasBatch;

                var taxSelect = document.getElementById('activeBatchTaxMode');
                taxSelect.value = (taxMode === 'TER_STANDALONE') ? 'TER_STANDALONE' : 'NONE';
                taxSelect.disabled = !isDraft;

                var rejAlert = document.getElementById('batchRejectAlert');
                if (currentBatchStatus === 'REJECTED' && rejectReason) {
                    document.getElementById('batchRejectReasonText').textContent = rejectReason;
                    rejAlert.classList.remove('d-none');
                } else {
                    rejAlert.classList.add('d-none');
                }
            }

            function showGridLoading() {
                var overlay = document.getElementById('thrGridLoadingOverlay');
                if (overlay) {
                    overlay.style.display = 'flex';
                }
            }

            function hideGridLoading() {
                var overlay = document.getElementById('thrGridLoadingOverlay');
                if (overlay) {
                    overlay.style.display = 'none';
                }
            }

            function loadBatchItems(batchId) {
                currentBatchId = parseInt(batchId, 10) || 0;
                var sel = document.getElementById('activeBatchSelect');
                var opt = sel.options[sel.selectedIndex];

                if (currentBatchId <= 0 || !opt) {
                    hideGridLoading();
                    updateActionButtons('', 'NONE', '');
                    updateKpi(0, 0, 0, 0, 'NONE');
                    document.getElementById('kpiPeriodLabel').textContent = 'Periode: -';
                    if (thrGrid) thrGrid.clearAll();
                    return;
                }

                var taxMode = opt.getAttribute('data-tax-mode') || 'NONE';
                updateActionButtons(opt.getAttribute('data-status'), taxMode, opt.getAttribute('data-reject-reason'));
                updateKpi(
                    opt.getAttribute('data-emp'),
                    opt.getAttribute('data-amt'),
                    opt.getAttribute('data-pph21'),
                    opt.getAttribute('data-net'),
                    taxMode
                );
                document.getElementById('kpiPeriodLabel').textContent = 'Periode: ' + (opt.getAttribute(
                        'data-period') ||
                    '-');

                showGridLoading();
                thrGrid.clearAndLoad("{{ url('/xml-payroll-thr-items') }}?batch_id=" + currentBatchId, function() {
                    hideGridLoading();
                });
            }

            function refreshBatches(selectId) {
                fetch("{{ url('/api/payroll/thr/batches') }}", {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(function(r) {
                        return r.json();
                    })
                    .then(function(res) {
                        if (!res.success) return;
                        var sel = document.getElementById('activeBatchSelect');
                        sel.innerHTML = '<option value="">-- Pilih Batch THR --</option>';
                        (res.data || []).forEach(function(b) {
                            var o = document.createElement('option');
                            o.value = b.id;
                            o.textContent = b.batch_no + ' | ' + b.salary_period + ' | ' + b
                                .thr_event_name +
                                ' [' + b.status + ']';
                            o.setAttribute('data-status', b.status);
                            o.setAttribute('data-period', b.salary_period);
                            o.setAttribute('data-event', b.thr_event_name);
                            o.setAttribute('data-emp', b.total_employees);
                            o.setAttribute('data-amt', b.total_amount);
                            o.setAttribute('data-pph21', b.total_pph21 || 0);
                            o.setAttribute('data-net', (b.total_net_amount > 0 ? b.total_net_amount : b
                                .total_amount));
                            o.setAttribute('data-tax-mode', b.tax_mode || 'NONE');
                            o.setAttribute('data-reject-reason', b.rejection_reason || '');
                            if (selectId && parseInt(selectId, 10) === parseInt(b.id, 10)) {
                                o.selected = true;
                            }
                            sel.appendChild(o);
                        });
                        if (selectId) {
                            loadBatchItems(selectId);
                        } else {
                            loadBatchItems(0);
                        }
                    });
            }

            // Blasting Email State
            var thrBlastQueue = [];
            var thrBlastIndex = 0;
            var thrBlastSent = 0;
            var thrBlastFailed = 0;
            var thrBlastSkipped = 0;

            function isValidEmailFormat(email) {
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(email || '').trim());
            }

            function refreshBlastRecipientSelectionCount() {
                var enabledChecks = document.querySelectorAll('.blast-thr-check[data-has-email="1"]');
                var checkedEnabled = document.querySelectorAll('.blast-thr-check[data-has-email="1"]:checked');
                var count = checkedEnabled.length;
                document.getElementById('blastThrSelectedCount').textContent = count;
                document.getElementById('blastThrTargetCount').textContent = count + ' Orang';

                var masterCheck = document.getElementById('blastThrMasterCheck');
                if (masterCheck) {
                    masterCheck.disabled = (enabledChecks.length === 0);
                    masterCheck.checked = (enabledChecks.length > 0 && count === enabledChecks.length);
                }
            }

            function openThrBlastModal() {
                if (!currentBatchId || (currentBatchStatus !== 'DRAFT' && currentBatchStatus !== 'APPROVED')) {
                    alert('Blasting Email hanya dapat dilakukan untuk batch berstatus DRAFT (Preview) atau APPROVED (Final).');
                    return;
                }

                var eligibleRows = [];
                var rowCount = thrGrid.getRowsNum();
                for (var i = 0; i < rowCount; i++) {
                    var rId = thrGrid.getRowId(i);
                    var isElig = thrGrid.cells(rId, 0).getValue();
                    var netAmt = parseFloat(String(thrGrid.cells(rId, 15).getValue()).replace(/,/g, '')) || 0;
                    if ((isElig == 1 || isElig === '1' || isElig === true) && netAmt > 0) {
                        var nik = thrGrid.getUserData(rId, 'nik') || thrGrid.cells(rId, 1).getValue();
                        var empEmail = String(thrGrid.getUserData(rId, 'email') || '').trim();
                        var empName = thrGrid.cells(rId, 2).getValue() || '-';
                        var dept = thrGrid.cells(rId, 3).getValue() || '-';
                        if (nik) {
                            eligibleRows.push({
                                nik: nik,
                                email: empEmail,
                                hasValidEmail: isValidEmailFormat(empEmail),
                                name: empName,
                                dept: dept,
                                net: netAmt
                            });
                        }
                    }
                }

                if (!eligibleRows.length) {
                    alert('Tidak ada karyawan eligible dengan nominal THR > 0 untuk dikirimkan email.');
                    return;
                }

                var isDraftMode = (currentBatchStatus === 'DRAFT');
                var modalHeader = document.getElementById('blastThrModalHeader');
                var modalTitle = document.getElementById('modalBlastThrEmailLabel');
                var modeAlert = document.getElementById('blastThrModeAlert');
                var btnStart = document.getElementById('btnStartBlastThr');

                if (isDraftMode) {
                    modalHeader.className = 'modal-header bg-warning text-dark rounded-top-4';
                    modalTitle.className = 'modal-title fw-bold text-dark';
                    modalTitle.innerHTML = '<i class="fa fa-paper-plane me-2"></i>Blasting Preview Payslip THR (Status DRAFT)';
                    modeAlert.className = 'alert alert-warning small rounded-3 mb-3';
                    modeAlert.innerHTML = '<i class="fa fa-exclamation-triangle me-1"></i> <strong>Mode Preview (DRAFT):</strong> Email akan dikirim dengan penanda <strong>[PREVIEW / DRAFT]</strong> agar karyawan dapat mengecek nominal THR sebelum diajukan approval.';
                    btnStart.className = 'btn btn-warning rounded-pill px-4 fw-bold text-dark shadow-sm';
                    btnStart.innerHTML = '<i class="fa fa-paper-plane me-1"></i> Kirim Blasting Preview (Draft)';
                } else {
                    modalHeader.className = 'modal-header bg-success text-white rounded-top-4';
                    modalTitle.className = 'modal-title fw-bold text-white';
                    modalTitle.innerHTML = '<i class="fa fa-paper-plane me-2"></i>Blasting Email Payslip THR (Final / Approved)';
                    modeAlert.className = 'alert alert-info small rounded-3 mb-3';
                    modeAlert.innerHTML = '<i class="fa fa-info-circle me-1"></i> Mengirimkan Payslip THR digital resmi secara massal ke alamat email masing-masing karyawan eligible pada batch THR yang telah berstatus <strong>APPROVED</strong>.';
                    btnStart.className = 'btn btn-success rounded-pill px-4 fw-bold text-white shadow-sm';
                    btnStart.innerHTML = '<i class="fa fa-paper-plane me-1"></i> Kirim Blasting THR Sekarang';
                }

                var sel = document.getElementById('activeBatchSelect');
                var opt = sel.options[sel.selectedIndex];
                var batchLabel = opt ? opt.textContent.split('|')[0].trim() : ('Batch #' + currentBatchId);
                var eventLabel = opt ? (opt.getAttribute('data-event') || '-') : '-';

                document.getElementById('blastThrBatchNo').textContent = batchLabel;
                document.getElementById('blastThrEventName').textContent = eventLabel;

                var listBody = document.getElementById('blastThrRecipientList');
                listBody.innerHTML = '';
                var validEmailCount = 0;
                eligibleRows.forEach(function(item) {
                    if (item.hasValidEmail) validEmailCount++;
                    var tr = document.createElement('tr');
                    tr.className = item.hasValidEmail ? 'thr-blast-row-clickable' : 'thr-blast-row-disabled';
                    tr.setAttribute('data-search', (item.nik + ' ' + item.name + ' ' + item.dept + ' ' + item.email).toLowerCase());
                    var emailCellHtml = item.hasValidEmail
                        ? '<span class="text-muted">' + escapeHtml(item.email) + '</span>'
                        : '<span class="badge bg-danger text-white">Email Belum Diatur</span>';
                    tr.innerHTML =
                        '<td class="thr-blast-check-cell"><input type="checkbox" class="thr-blast-checkbox blast-thr-check" data-has-email="' + (item.hasValidEmail ? '1' : '0') + '" value="' + escapeHtml(item.nik) + '"' + (item.hasValidEmail ? ' checked' : ' disabled') + '></td>' +
                        '<td class="font-monospace fw-semibold">' + escapeHtml(item.nik) + '</td>' +
                        '<td>' + escapeHtml(item.name) + '</td>' +
                        '<td class="text-muted">' + escapeHtml(item.dept) + '</td>' +
                        '<td>' + emailCellHtml + '</td>' +
                        '<td class="text-end fw-semibold">' + formatRp(item.net) + '</td>';

                    if (item.hasValidEmail) {
                        tr.addEventListener('click', function(e) {
                            if (e.target && e.target.tagName === 'INPUT') return;
                            var cb = tr.querySelector('.blast-thr-check');
                            if (cb && !cb.disabled) {
                                cb.checked = !cb.checked;
                                refreshBlastRecipientSelectionCount();
                            }
                        });
                    }
                    listBody.appendChild(tr);
                });

                document.querySelectorAll('.blast-thr-check').forEach(function(cb) {
                    cb.addEventListener('change', refreshBlastRecipientSelectionCount);
                });
                document.getElementById('blastThrSearchInput').value = '';
                document.getElementById('blastThrSearchInput').disabled = false;
                document.getElementById('btnBlastSelectAll').disabled = false;
                document.getElementById('btnBlastUnselectAll').disabled = false;
                refreshBlastRecipientSelectionCount();

                document.getElementById('blastThrProgressWrapper').style.display = 'none';
                document.getElementById('blastThrProgressBar').style.width = '0%';
                document.getElementById('blastThrProgressBar').classList.add('progress-bar-animated');
                document.getElementById('blastThrProgressPercent').textContent = '0%';
                document.getElementById('blastThrProgressText').textContent = 'Menunggu konfirmasi...';
                document.getElementById('blastThrResultsBody').innerHTML =
                    '<tr><td colspan="6" class="text-center py-4 text-muted">Siap mengirim Payslip THR ' +
                    (isDraftMode ? 'Preview (Draft)' : 'Final') + ' ke ' +
                    validEmailCount + ' karyawan dengan email valid.</td></tr>';

                btnStart.disabled = false;
                document.getElementById('btnCloseBlastThr').disabled = false;

                var modalEl = document.getElementById('modalBlastThrEmail');
                var modalInstance = (window.bootstrap && bootstrap.Modal && bootstrap.Modal.getOrCreateInstance) ?
                    bootstrap.Modal.getOrCreateInstance(modalEl) :
                    new bootstrap.Modal(modalEl);
                modalInstance.show();
            }

            function processNextThrBlastChunk() {
                if (thrBlastIndex >= thrBlastQueue.length) {
                    var btnStart = document.getElementById('btnStartBlastThr');
                    btnStart.disabled = false;
                    btnStart.innerHTML = '<i class="fa fa-check me-1"></i> Kirim Lagi';
                    document.getElementById('btnCloseBlastThr').disabled = false;
                    document.getElementById('blastThrSearchInput').disabled = false;
                    document.getElementById('btnBlastSelectAll').disabled = false;
                    document.getElementById('btnBlastUnselectAll').disabled = false;
                    document.querySelectorAll('.blast-thr-check[data-has-email="1"]').forEach(function(cb) {
                        cb.disabled = false;
                    });
                    refreshBlastRecipientSelectionCount();
                    document.getElementById('blastThrProgressBar').classList.remove('progress-bar-animated');
                    document.getElementById('blastThrProgressText').innerHTML =
                        '<strong class="text-success">Blasting Selesai!</strong> Terkirim: ' + thrBlastSent +
                        ', Gagal: ' + thrBlastFailed + ', Dilewati: ' + thrBlastSkipped;
                    return;
                }

                var chunk = thrBlastQueue.slice(thrBlastIndex, thrBlastIndex + 3);
                fetch("{{ url('/api/payroll/thr/blast-payslip-emails') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken()
                        },
                        body: JSON.stringify({
                            _token: getCsrfToken(),
                            batch_id: currentBatchId,
                            niks: chunk
                        })
                    })
                    .then(function(r) {
                        return r.json();
                    })
                    .then(function(res) {
                        var tbody = document.getElementById('blastThrResultsBody');
                        if (res && res.results) {
                            res.results.forEach(function(item) {
                                thrBlastIndex++;
                                var badgeHtml = '';
                                if (item.status === 'SENT') {
                                    thrBlastSent++;
                                    badgeHtml = '<span class="badge bg-success text-white">Terkirim</span>';
                                } else if (item.status === 'SKIPPED') {
                                    thrBlastSkipped++;
                                    badgeHtml = '<span class="badge bg-warning text-dark">Dilewati</span>';
                                } else {
                                    thrBlastFailed++;
                                    badgeHtml = '<span class="badge bg-danger text-white">Gagal</span>';
                                }
                                var tr = document.createElement('tr');
                                tr.innerHTML =
                                    '<td class="text-center">' + thrBlastIndex + '</td>' +
                                    '<td class="font-monospace fw-semibold">' + escapeHtml(item.nik) +
                                    '</td>' +
                                    '<td>' + escapeHtml(item.name || '-') + '</td>' +
                                    '<td class="small text-muted">' + escapeHtml(item.email || '-') +
                                    '</td>' +
                                    '<td class="text-center">' + badgeHtml + '</td>' +
                                    '<td class="small">' + escapeHtml(item.message || '-') + '</td>';
                                tbody.appendChild(tr);
                            });
                        } else {
                            thrBlastIndex += chunk.length;
                            thrBlastFailed += chunk.length;
                        }

                        var pct = Math.min(100, Math.round((thrBlastIndex / thrBlastQueue.length) * 100));
                        document.getElementById('blastThrProgressBar').style.width = pct + '%';
                        document.getElementById('blastThrProgressPercent').textContent = pct + '%';
                        document.getElementById('blastThrProgressText').textContent = 'Mengirim ' + thrBlastIndex +
                            ' dari ' + thrBlastQueue.length + ' karyawan...';

                        setTimeout(processNextThrBlastChunk, 200);
                    })
                    .catch(function() {
                        thrBlastIndex += chunk.length;
                        thrBlastFailed += chunk.length;
                        setTimeout(processNextThrBlastChunk, 200);
                    });
            }

            document.addEventListener('DOMContentLoaded', function() {
                initThrGrid();

                if (thrGrid) {
                    thrGrid.attachEvent("onXLS", function() {
                        showGridLoading();
                    });
                    thrGrid.attachEvent("onXLE", function() {
                        hideGridLoading();
                    });
                }

                var schemeModeSelect = document.getElementById('thr_prorate_method');
                var fallbackWrapper = document.getElementById('thrFallbackSchemeWrapper');
                var fallbackSelect = document.getElementById('thr_fallback_scheme');

                function toggleFallbackScheme() {
                    var isFollow = (schemeModeSelect && schemeModeSelect.value === 'FOLLOW_EMPLOYEE');
                    if (fallbackWrapper) {
                        fallbackWrapper.style.display = isFollow ? '' : 'none';
                    }
                    if (fallbackSelect) {
                        fallbackSelect.disabled = !isFollow;
                    }
                }
                if (schemeModeSelect) {
                    schemeModeSelect.addEventListener('change', toggleFallbackScheme);
                    toggleFallbackScheme();
                }

                var sel = document.getElementById('activeBatchSelect');
                sel.addEventListener('change', function() {
                    loadBatchItems(this.value);
                });

                if (sel.options.length > 1) {
                    sel.selectedIndex = 1;
                    loadBatchItems(sel.value);
                }

                var thrGenTimer = null;

                function showGenerateStatus(type, title, detailHtml, progressPct) {
                    var box = document.getElementById('thrGenerateStatusBox');
                    var alertEl = document.getElementById('thrGenerateStatusAlert');
                    var iconEl = document.getElementById('thrGenerateStatusIcon');
                    var titleEl = document.getElementById('thrGenerateStatusTitle');
                    var detailEl = document.getElementById('thrGenerateStatusDetail');
                    var progressWrap = document.getElementById('thrGenerateProgressWrap');
                    var progressBar = document.getElementById('thrGenerateProgressBar');
                    var closeBtn = document.getElementById('thrGenerateStatusClose');

                    if (!box || !alertEl) return;
                    box.classList.remove('d-none');

                    alertEl.className = 'alert border-0 shadow-sm rounded-3 mb-0 mt-3 ';
                    if (type === 'loading') {
                        alertEl.className += 'alert-info';
                        iconEl.innerHTML =
                            '<span class="spinner-border spinner-border-sm text-info" role="status" aria-hidden="true"></span>';
                        progressWrap.classList.remove('d-none');
                        progressBar.className =
                            'progress-bar progress-bar-striped progress-bar-animated bg-info';
                        progressBar.style.width = (progressPct || 65) + '%';
                        closeBtn.classList.add('d-none');
                    } else if (type === 'success') {
                        alertEl.className += 'alert-success';
                        iconEl.innerHTML = '<i class="fe fe-check-circle fs-5 text-success"></i>';
                        progressWrap.classList.remove('d-none');
                        progressBar.className = 'progress-bar bg-success';
                        progressBar.style.width = '100%';
                        closeBtn.classList.remove('d-none');
                    } else {
                        alertEl.className += 'alert-danger';
                        iconEl.innerHTML = '<i class="fe fe-alert-circle fs-5 text-danger"></i>';
                        progressWrap.classList.add('d-none');
                        closeBtn.classList.remove('d-none');
                    }

                    titleEl.textContent = title;
                    detailEl.innerHTML = detailHtml;
                }

                document.getElementById('thrGenerateForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    var btn = document.getElementById('btnGenerateThr');
                    var originalBtnHtml = '<i class="fe fe-zap me-1"></i> Hitung &amp; Buat Draft THR';
                    var startTime = Date.now();
                    var fd = new FormData(this);
                    fd.append('_token', getCsrfToken());

                    btn.disabled = true;
                    btn.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Menghitung THR...';

                    if (thrGenTimer) {
                        clearInterval(thrGenTimer);
                        thrGenTimer = null;
                    }

                    var updateLoadingText = function() {
                        var elapsedNum = (Date.now() - startTime) / 1000;
                        var elapsed = elapsedNum.toFixed(1);
                        var phaseMsg = 'Mengambil data karyawan aktif &amp; komponen gaji...';
                        var pct = 35;
                        if (elapsedNum >= 1.5 && elapsedNum < 3.5) {
                            phaseMsg = 'Menghitung masa kerja, prorata skema THR, dan nominal tunjangan...';
                            pct = 65;
                        } else if (elapsedNum >= 3.5) {
                            phaseMsg = 'Menyusun draft batch THR &amp; kalkulasi pajak (jika aktif)...';
                            pct = 85;
                        }
                        showGenerateStatus(
                            'loading',
                            'Sedang memproses perhitungan Draft THR... (' + elapsed + ' dtk)',
                            phaseMsg,
                            pct
                        );
                    };

                    updateLoadingText();
                    thrGenTimer = setInterval(updateLoadingText, 250);

                    fetch("{{ url('/api/payroll/thr/generate') }}", {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': getCsrfToken()
                            },
                            body: fd
                        })
                        .then(function(r) {
                            return r.json().then(function(data) {
                                return {
                                    ok: r.ok,
                                    status: r.status,
                                    data: data
                                };
                            });
                        })
                        .then(function(resp) {
                            if (thrGenTimer) {
                                clearInterval(thrGenTimer);
                                thrGenTimer = null;
                            }
                            btn.disabled = false;
                            btn.innerHTML = originalBtnHtml;

                            var res = resp.data || {};
                            var totalElapsed = ((Date.now() - startTime) / 1000).toFixed(1);

                            if (resp.ok && res.success) {
                                var d = res.data || {};
                                var summaryHtml = 'Batch <strong>' + escapeHtml(d.batch_no || '-') +
                                    '</strong> siap direview: <strong>' + (d.total_employees || 0) +
                                    '</strong> karyawan eligible, Total Bruto <strong>' +
                                    formatRp(d.total_amount || 0) +
                                    '</strong>, Total Nett <strong>' + formatRp(d.total_net_amount || d.total_amount || 0) +
                                    '</strong>.';
                                showGenerateStatus(
                                    'success',
                                    'Draft THR berhasil dibuat dalam ' + totalElapsed + ' detik!',
                                    summaryHtml
                                );
                                if (d.batch_id) {
                                    refreshBatches(d.batch_id);
                                }
                                if (window.swal) {
                                    swal({
                                        title: 'Draft THR Berhasil Dibuat',
                                        text: res.message || ('Batch ' + (d.batch_no || '') + ' berhasil digenerate.'),
                                        icon: 'success',
                                        timer: 2600,
                                        buttons: false
                                    });
                                }
                            } else {
                                var errMsg = res.message || 'Gagal menghitung draft THR.';
                                if (res.errors) {
                                    var firstKey = Object.keys(res.errors)[0];
                                    if (firstKey && res.errors[firstKey] && res.errors[firstKey][0]) {
                                        errMsg = res.errors[firstKey][0];
                                    }
                                }
                                showGenerateStatus(
                                    'error',
                                    'Gagal Generate Draft THR (' + totalElapsed + ' dtk)',
                                    escapeHtml(errMsg)
                                );
                            }
                        })
                        .catch(function() {
                            if (thrGenTimer) {
                                clearInterval(thrGenTimer);
                                thrGenTimer = null;
                            }
                            btn.disabled = false;
                            btn.innerHTML = originalBtnHtml;
                            showGenerateStatus(
                                'error',
                                'Terjadi Kesalahan Jaringan',
                                'Tidak dapat terhubung ke server saat menghitung draft THR. Silakan coba lagi.'
                            );
                        });
                });

                document.getElementById('activeBatchTaxMode').addEventListener('change', function() {
                    if (!currentBatchId || currentBatchStatus !== 'DRAFT') {
                        loadBatchItems(currentBatchId);
                        return;
                    }
                    var newMode = this.value;
                    fetch("{{ url('/api/payroll/thr/tax-mode') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': getCsrfToken()
                            },
                            body: JSON.stringify({
                                batch_id: currentBatchId,
                                tax_mode: newMode,
                                _token: getCsrfToken()
                            })
                        })
                        .then(function(r) {
                            return r.json();
                        })
                        .then(function(res) {
                            if (!res.success) {
                                alert(res.message || 'Gagal mengubah mode pajak.');
                            }
                            refreshBatches(currentBatchId);
                        })
                        .catch(function() {
                            alert('Terjadi kesalahan saat memperbarui mode PPh 21.');
                        });
                });

                document.getElementById('btnSubmitApproval').addEventListener('click', function() {
                    if (!currentBatchId || !confirm(
                            'Ajukan batch THR ini ke alur persetujuan (Approval PAYROLL_THR)?')) return;
                    fetch("{{ url('/api/payroll/thr/submit-approval') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': getCsrfToken()
                            },
                            body: JSON.stringify({
                                batch_id: currentBatchId,
                                _token: getCsrfToken()
                            })
                        })
                        .then(function(r) {
                            return r.json();
                        })
                        .then(function(res) {
                            alert(res.message || 'Proses pengajuan approval selesai.');
                            if (res.success) {
                                refreshBatches(currentBatchId);
                            }
                        })
                        .catch(function() {
                            alert('Terjadi kesalahan saat mengajukan approval batch THR.');
                        });
                });

                document.getElementById('btnRevertDraft').addEventListener('click', function() {
                    if (!currentBatchId || !confirm(
                            'Kembalikan batch THR ini ke status DRAFT untuk direvisi?')) return;
                    fetch("{{ url('/api/payroll/thr/revert-draft') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': getCsrfToken()
                            },
                            body: JSON.stringify({
                                batch_id: currentBatchId,
                                _token: getCsrfToken()
                            })
                        })
                        .then(function(r) {
                            return r.json();
                        })
                        .then(function(res) {
                            alert(res.message || 'Proses selesai.');
                            if (res.success) {
                                refreshBatches(currentBatchId);
                            }
                        })
                        .catch(function() {
                            alert('Terjadi kesalahan saat mengembalikan batch ke DRAFT.');
                        });
                });

                document.getElementById('btnDeleteBatch').addEventListener('click', function() {
                    if (!currentBatchId || !confirm('Hapus draft batch THR ini?')) return;
                    fetch("{{ url('/api/payroll/thr/delete') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': getCsrfToken()
                            },
                            body: JSON.stringify({
                                batch_id: currentBatchId,
                                _token: getCsrfToken()
                            })
                        })
                        .then(function(r) {
                            return r.json();
                        })
                        .then(function(res) {
                            alert(res.message || 'Proses selesai.');
                            if (res.success) {
                                refreshBatches(null);
                            }
                        })
                        .catch(function() {
                            alert('Terjadi kesalahan saat menghapus draft batch THR.');
                        });
                });

                document.getElementById('btnOpenSlipThr').addEventListener('click', function() {
                    openThrPayslipForRow(null);
                });

                document.getElementById('btnBlastThrEmail').addEventListener('click', function() {
                    openThrBlastModal();
                });

                document.getElementById('blastThrSearchInput').addEventListener('input', function() {
                    var q = (this.value || '').toLowerCase().trim();
                    document.querySelectorAll('#blastThrRecipientList tr').forEach(function(tr) {
                        var hay = tr.getAttribute('data-search') || '';
                        tr.style.display = (!q || hay.indexOf(q) !== -1) ? '' : 'none';
                    });
                });

                function toggleVisibleBlastChecks(checked) {
                    document.querySelectorAll('#blastThrRecipientList tr').forEach(function(tr) {
                        if (tr.style.display === 'none') return;
                        var cb = tr.querySelector('.blast-thr-check[data-has-email="1"]');
                        if (cb && !cb.disabled) {
                            cb.checked = checked;
                        }
                    });
                    refreshBlastRecipientSelectionCount();
                }

                document.getElementById('btnBlastSelectAll').addEventListener('click', function() {
                    toggleVisibleBlastChecks(true);
                });

                document.getElementById('btnBlastUnselectAll').addEventListener('click', function() {
                    toggleVisibleBlastChecks(false);
                });

                document.getElementById('blastThrMasterCheck').addEventListener('change', function() {
                    toggleVisibleBlastChecks(this.checked);
                });

                document.getElementById('btnStartBlastThr').addEventListener('click', function() {
                    thrBlastQueue = [];
                    document.querySelectorAll('.blast-thr-check[data-has-email="1"]:checked').forEach(function(cb) {
                        if (cb.value) thrBlastQueue.push(cb.value);
                    });

                    if (!thrBlastQueue.length) {
                        alert('Pilih minimal 1 karyawan dengan alamat email valid pada daftar di atas.');
                        return;
                    }

                    var modeConfirmText = (currentBatchStatus === 'DRAFT') ? 'Preview (Draft)' : 'Final';
                    if (!confirm('Yakin mengirim email Payslip THR ' + modeConfirmText + ' ke ' +
                            thrBlastQueue.length + ' karyawan terpilih?')) return;

                    this.disabled = true;
                    this.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Sedang Mengirim...';
                    document.getElementById('btnCloseBlastThr').disabled = true;
                    document.getElementById('blastThrSearchInput').disabled = true;
                    document.getElementById('btnBlastSelectAll').disabled = true;
                    document.getElementById('btnBlastUnselectAll').disabled = true;
                    document.getElementById('blastThrMasterCheck').disabled = true;
                    document.querySelectorAll('.blast-thr-check').forEach(function(cb) {
                        cb.disabled = true;
                    });
                    document.getElementById('blastThrProgressWrapper').style.display = 'block';
                    document.getElementById('blastThrResultsBody').innerHTML = '';

                    thrBlastIndex = 0;
                    thrBlastSent = 0;
                    thrBlastFailed = 0;
                    thrBlastSkipped = 0;
                    processNextThrBlastChunk();
                });

                document.getElementById('btnExportThrExcel').addEventListener('click', function() {
                    if (!currentBatchId) return;
                    window.location.href = "{{ url('/payroll-thr-export-excel') }}?batch_id=" +
                        currentBatchId;
                });
            });
        })();
    </script>
@endsection
