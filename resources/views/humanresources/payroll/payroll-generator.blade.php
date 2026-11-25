@php
    $user = Auth::user();
    $lsp = $user ? ($user->Location ?: 'GEU-HO') : 'GEU-HO';
    $taskId = isset($taskId) ? $taskId : 'PG-' . $lsp . '-' . date('ymdhis');
    $defaultPeriod = isset($defaultPeriod) ? $defaultPeriod : date('Y-m');
    $mainGroups = isset($mainGroups)
        ? $mainGroups
        : \Illuminate\Support\Facades\DB::table('payrol_main_group')->orderBy('ID', 'ASC')->get();
    $pendingBatchCount = isset($pendingBatchCount) ? $pendingBatchCount : 0;
    $approvedBatchCount = isset($approvedBatchCount) ? $approvedBatchCount : 0;
@endphp

@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    @csrf

    <style>
        .pg-kpi-card {
            border: 1px solid rgba(226, 232, 240, 0.85);
            border-radius: 14px;
            background: #ffffff;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .pg-kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06) !important;
        }

        .pg-kpi-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        .pg-form-label {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #475569;
            margin-bottom: 0.35rem;
        }

        .pg-input-group .form-control,
        .pg-input-group .form-select {
            border-radius: 10px;
            border-color: #cbd5e1;
            font-size: 0.9rem;
            padding: 0.5rem 0.75rem;
        }

        .pg-input-group .form-control:focus,
        .pg-input-group .form-select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.15);
        }

        .pg-action-footer {
            background: linear-gradient(90deg, #f8fafc 0%, #eff6ff 100%);
            border: 1px solid #dbeafe;
            border-radius: 12px;
        }
    </style>

    <!-- Main Content Header (Adminor V3 Responsive Hero Header) -->
    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
        <div class="mb-2 mb-md-0">
            <h4 class="content-title text-white fw-bold mb-1"
                style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
                <i class="fe fe-cpu me-2"></i>Generator Penggajian (Payroll Generator)
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ url('payroll-tax') }}" style="color: rgba(255, 255, 255, 0.8) !important;">
                            Payroll
                        </a>
                    </li>
                    <li class="breadcrumb-item active text-white" aria-current="page"
                        style="color: #ffffff !important; font-weight: 600;">
                        Batch Generator
                    </li>
                </ol>
            </nav>
        </div>
        <div class="d-flex align-items-center flex-wrap gap-2">
            <a href="{{ url('payroll-tax') }}" class="btn btn-sm rounded-pill shadow-sm"
                style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
                <i class="fe fe-percent me-1"></i> Master Tax &amp; TER
            </a>
            <a href="{{ url('payroll-group') }}" class="btn btn-sm rounded-pill shadow-sm"
                style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
                <i class="fe fe-layers me-1"></i> Master Group
            </a>
            <a href="{{ url('payroll-employee') }}" class="btn btn-sm rounded-pill shadow-sm"
                style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
                <i class="fe fe-users me-1"></i> Setup Gaji Pegawai
            </a>
            <a href="{{ url('payroll-approval') }}" class="btn btn-sm rounded-pill shadow-sm position-relative"
                style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
                <i class="fe fe-check-circle me-1"></i> Approval Payroll
                @if ($pendingBatchCount > 0)
                    <span class="badge bg-warning text-dark rounded-pill ms-1">{{ $pendingBatchCount }}</span>
                @endif
            </a>
            <a href="{{ url('approved-payroll') }}" class="btn btn-sm rounded-pill shadow-sm"
                style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
                <i class="fe fe-archive me-1"></i> Arsip Approved
                @if ($approvedBatchCount > 0)
                    <span class="badge bg-success text-white rounded-pill ms-1">{{ $approvedBatchCount }}</span>
                @endif
            </a>
        </div>
    </div>
    <!-- End Main Content Header -->

    <!-- KPI Summary Row -->
    <div class="row g-3 mb-3">
        <div class="col-xl-3 col-md-6">
            <div class="pg-kpi-card shadow-sm p-3 h-100 d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-semibold mb-1">NOMOR BATCH (GEN ID)</div>
                    <div class="fw-bold text-dark fs-14 text-break" id="kpiTaskIdDisplay">{{ $taskId }}</div>
                    <small class="text-primary"><i class="fe fe-hash me-1"></i>Auto-generated Batch Code</small>
                </div>
                <div class="pg-kpi-icon bg-primary-transparent text-primary" style="background: rgba(79, 70, 229, 0.1);">
                    <i class="fa fa-barcode text-primary"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="pg-kpi-card shadow-sm p-3 h-100 d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-semibold mb-1">PERIODE PENGGAJIAN</div>
                    <div class="fw-bold text-dark fs-5" id="kpiPeriodDisplay">{{ $defaultPeriod }}</div>
                    <small class="text-muted"><i class="fa fa-calendar me-1"></i>Cutoff Presensi &amp; Pajak TER</small>
                </div>
                <div class="pg-kpi-icon" style="background: rgba(14, 165, 233, 0.12);">
                    <i class="fa fa-calendar-check-o text-info"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="pg-kpi-card shadow-sm p-3 h-100 d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-semibold mb-1">KARYAWAN TERPILIH</div>
                    <div class="fw-bold text-dark fs-5">
                        <span id="kpiSelectedCount" class="text-primary">0</span>
                        <span class="text-muted fw-normal fs-6">/ <span id="kpiTotalRows">0</span> Orang</span>
                    </div>
                    <small class="text-muted">Dicentang pada tabel di bawah</small>
                </div>
                <div class="pg-kpi-icon" style="background: rgba(245, 158, 11, 0.12);">
                    <i class="fa fa-users text-warning"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="pg-kpi-card shadow-sm p-3 h-100 d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-semibold mb-1">ESTIMASI TOTAL THP BATCH</div>
                    <div class="fw-bold text-success fs-5" id="kpiTotalThpDisplay">Rp 0</div>
                    <small class="text-muted">Take Home Pay Bersih Terpilih</small>
                </div>
                <div class="pg-kpi-icon" style="background: rgba(16, 185, 129, 0.12);">
                    <i class="fa fa-money text-success"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Konfigurasi Parameter Generator (Bootstrap 5 Card) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4" id="ContentPages">
        <div
            class="card-header bg-white border-bottom pt-3 pb-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="fw-bold mb-0 text-primary">
                    <i class="fe fe-sliders me-1"></i> Parameter Konfigurasi Batch Penggajian
                </h5>
                <small class="text-muted">Pilih periode gaji, template perhitungan, kategori penggunaan, dan kelompok gaji
                    (Salary Group) yang akan diproses.</small>
            </div>
            <div class="d-flex align-items-center flex-wrap gap-2">
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="regenerateTaskId()"
                    title="Buat Gen ID Baru">
                    <i class="fa fa-refresh me-1"></i> Buat Gen ID Baru
                </button>
                <button type="button" class="btn btn-sm btn-warning rounded-pill px-3 text-dark fw-semibold"
                    onclick="openAdvanceDeductionsModal()"
                    title="Kelola Potongan SPPD, Kasbon & Reimburse (Otomatis & Manual)">
                    <i class="fa fa-scissors me-1"></i> Potongan SPPD / Kasbon / Reimburse
                </button>
                <button type="button" class="btn btn-sm btn-success rounded-pill px-3 text-white"
                    onclick="recountingCurrentGroup()"
                    title="Kalkulasi ulang Presensi, Potongan & PPh 21 TER untuk grup ini">
                    <i class="fa fa-calculator me-1"></i> Recounting Grup (Sync Presensi &amp; TER)
                </button>
            </div>
        </div>

        <div class="card-body p-3 p-md-4">
            <div class="row g-3 pg-input-group">
                <!-- Baris 1: Gen ID, Period, Filter Kategori -->
                <div class="col-lg-4 col-md-6">
                    <label for="Task_ID" class="pg-form-label">Gen ID (Nomor Batch)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary border-end-0"
                            style="border-radius: 10px 0 0 10px;">
                            <i class="fa fa-hashtag"></i>
                        </span>
                        <input type="text" id="Task_ID" name="Task_ID"
                            class="form-control bg-light fw-semibold border-start-0" value="{{ $taskId }}" readonly
                            style="border-radius: 0 10px 10px 0;" />
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <label for="Salary_Period" class="pg-form-label">Periode Gaji</label>
                    <input type="hidden" id="Payroll_Date" name="Payroll_Date" value="{{ date('Y-m-d') }}" />
                    <input type="hidden" id="Category" name="Category" value="SALARY" />
                    <input type="month" id="Salary_Period" name="Salary_Period" class="form-control fw-semibold"
                        value="{{ $defaultPeriod }}" onchange="onPeriodChange()" />
                </div>

                <div class="col-lg-5 col-md-12">
                    <label for="Use_For" class="pg-form-label">Filter Kategori Grup</label>
                    <select id="Use_For" name="Use_For" class="form-select fw-semibold"
                        onchange="onUseForFilterChange()">
                        <option value="ALL">SEMUA KATEGORI (INTERNAL &amp; OS/OT/PROJECT)</option>
                        <option value="INTERNAL">INTERNAL (Karyawan Internal / HO)</option>
                        <option value="NON_INTERNAL">NON_INTERNAL (Semua OS / OT / Project)</option>
                        <option value="OUTSOURCE">OUTSOURCE (OS)</option>
                        <option value="OUTTASK">OUTTASK (OT)</option>
                        <option value="PROJECT">PROJECT</option>
                        <option value="CUSTOM">CUSTOM</option>
                    </select>
                </div>

                <!-- Baris 2: Apply to Group, Total THP (readonly), Note -->
                <div class="col-lg-5 col-md-12">
                    <label for="Main_ID" class="pg-form-label">Apply to Salary Group <span
                            class="text-danger">*</span></label>
                    @php
                        $defaultSelectedGroup = '';
                        foreach ($mainGroups as $gItem) {
                            if (!empty($gItem->comp_count) && $gItem->comp_count > 0) {
                                $defaultSelectedGroup = $gItem->Main_ID;
                                break;
                            }
                        }
                        if (!$defaultSelectedGroup && count($mainGroups) > 0) {
                            $defaultSelectedGroup = $mainGroups[0]->Main_ID;
                        }
                    @endphp
                    <select id="Main_ID" name="Main_ID" class="form-select fw-semibold border-primary"
                        onchange="refreshdata()">
                        <option value="">-- Pilih Salary Group --</option>
                        @foreach ($mainGroups as $grp)
                            @php
                                $empCnt = isset($grp->emp_count) ? (int) $grp->emp_count : 0;
                            @endphp
                            <option value="{{ $grp->Main_ID }}"
                                data-use-for="{{ strtoupper(trim($grp->Use_For ?? '')) }}"
                                data-dept="{{ $grp->Department ?? '' }}"
                                @if ($grp->Main_ID === $defaultSelectedGroup) selected @endif>
                                {{ $grp->Main_ID }} — {{ $grp->Group_Name }} ({{ $grp->Use_For ?: 'GENERAL' }})
                                [{{ $empCnt }} Pegawai]
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-4 col-md-7">
                    <label for="Note" class="pg-form-label">Catatan Batch (Note)</label>
                    <input type="text" id="Note" name="Note" class="form-control"
                        placeholder="Contoh: Pembayaran Gaji Reguler Periode {{ $defaultPeriod }}..." />
                </div>

                <div class="col-lg-3 col-md-5">
                    <label for="Total_Formatted" class="pg-form-label">Total THP Terpilih (Rp)</label>
                    <input type="hidden" id="Total" name="Total" value="0" />
                    <div class="input-group">
                        <span class="input-group-text bg-success text-white fw-bold"
                            style="border-radius: 10px 0 0 10px;">Rp</span>
                        <input type="text" id="Total_Formatted"
                            class="form-control bg-light fw-bold text-end text-success" value="0" readonly
                            style="border-radius: 0 10px 10px 0;" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Daftar Karyawan & Kalkulasi Gaji (dhtmlxGrid dalam Bootstrap 5 Card) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div
            class="card-header bg-white border-bottom pt-3 pb-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="fe fe-users me-1 text-primary"></i> Daftar Perhitungan Gaji Karyawan
                </h5>
                <small class="text-muted">
                    Centang karyawan yang akan diproses dalam batch ini. <strong>Klik ganda (double-click)</strong> pada
                    baris karyawan untuk melihat rincian komponen &amp; simulasi slip gaji.
                </small>
            </div>
            <div class="d-flex align-items-center flex-wrap gap-2">
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm"
                    onclick="openSelectedEmployeeSlip()" title="Lihat Payslip Digital untuk baris karyawan terpilih">
                    <i class="fa fa-file-text-o me-1"></i> Buka Slip Digital
                </button>
                <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3 shadow-sm"
                    onclick="openBlastModalGenerator()" title="Kirim email payslip preview ke karyawan terpilih untuk pengecekan">
                    <i class="fa fa-paper-plane me-1"></i> Blasting Email Slip (Uji Coba)
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3"
                    onclick="toggleSelectAllRows(true)">
                    <i class="fa fa-check-square-o me-1"></i> Pilih Semua
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3"
                    onclick="toggleSelectAllRows(false)">
                    <i class="fa fa-square-o me-1"></i> Batal Pilih
                </button>
                <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3" onclick="refreshdata()">
                    <i class="fa fa-sync me-1"></i> Muat Ulang Tabel
                </button>
                <button type="button" class="btn btn-sm btn-warning rounded-pill px-3 text-dark"
                    onclick="EmployeeSalary.toExcel('{{ asset('export/generate.php') }}');">
                    <i class="fa fa-file-excel me-1"></i> Export Tabel
                </button>
            </div>
        </div>

        <div class="card-body p-3">
            <div id="EmployeeSalary"
                style="width:100%; height:400px; background-color:white; overflow:hidden; border: 1px solid #e2e8f0; border-radius: 8px;">
            </div>

            <!-- Bottom Action Bar (Upload, Template & Process Submit) -->
            <div class="pg-action-footer p-3 mt-3 d-flex flex-wrap justify-content-between align-items-center gap-3"
                id="divtom">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <button type="button" id="UploadValues" name="UploadValues"
                        class="btn btn-warning rounded-pill px-3 text-dark fw-semibold shadow-sm" data-bs-toggle="modal"
                        data-bs-target="#modalUploadExcel">
                        <i class="fa fa-upload me-1"></i> Upload Override Excel
                    </button>
                    <button type="button" class="btn btn-outline-primary rounded-pill px-3 bg-white"
                        onclick="downloadTemplateExcel()">
                        <i class="fa fa-download me-1"></i> Download Format Xlsx
                    </button>
                    <span class="text-muted small ms-1 d-none d-md-inline">
                        <i class="fa fa-info-circle me-1 text-primary"></i>Gunakan fitur upload jika ingin menimpa nilai
                        Gaji Pokok/Lembur/Potongan secara massal dari Excel.
                    </span>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <button type="button" id="SubmitCustDetail" name="SubmitCustDetail"
                        class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow">
                        <i class="fa fa-paper-plane me-2"></i> Proses &amp; Generate Batch Payroll
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Container Detail Komponen & Simulasi Slip Gaji saat Baris Diklik Ganda -->
    <div id="divlistProjectOrg"></div>
    <div id="PagesContent"></div>

    <!-- Modal Upload Data Excel (Bootstrap 5 Modal) -->
    <div class="modal fade" id="modalUploadExcel" tabindex="-1" aria-labelledby="modalUploadExcelLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header bg-primary text-white rounded-top-4">
                    <h5 class="modal-title fw-bold text-white" id="modalUploadExcelLabel">
                        <i class="fa fa-file-excel-o me-2"></i>Upload Nilai Override Penggajian
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info small rounded-3 mb-3">
                        <strong>Petunjuk Upload:</strong>
                        <ol class="mb-0 ps-3 mt-1">
                            <li>Klik tombol <strong>Download Format Xlsx</strong> untuk mengunduh daftar karyawan dari
                                Salary Group yang aktif.</li>
                            <li>Ubah nilai pada kolom <em>Gaji Pokok, Tunjangan, Overtime, KJK,</em> atau <em>Potongan
                                    Lain</em>.</li>
                            <li>Pilih file Excel tersebut di bawah ini lalu klik <strong>Terapkan ke Tabel</strong>.</li>
                        </ol>
                    </div>
                    <div class="mb-3">
                        <label for="excelFileInput" class="form-label fw-semibold">Pilih File Excel (.xlsx / .xls /
                            .csv)</label>
                        <input type="file" id="excelFileInput" class="form-control" accept=".xlsx,.xls,.csv" />
                    </div>
                    <div id="uploadExcelStatus" class="small"></div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-outline-primary rounded-pill px-3 me-auto"
                        onclick="downloadTemplateExcel()">
                        <i class="fa fa-download me-1"></i> Download Format Xlsx
                    </button>
                    <button type="button" class="btn btn-secondary rounded-pill px-3"
                        data-bs-dismiss="modal">Tutup</button>
                    <button type="button" id="btnProcessUploadExcel"
                        class="btn btn-primary rounded-pill px-4 fw-semibold" onclick="submitExcelUpload()">
                        <i class="fa fa-check me-1"></i> Terapkan ke Tabel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Blasting Email Payslip (Preview Recounting) -->
    <div class="modal fade" id="modalBlastEmailGenerator" tabindex="-1" aria-labelledby="modalBlastEmailGeneratorLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header bg-primary text-white rounded-top-4">
                    <h5 class="modal-title fw-bold text-white" id="modalBlastEmailGeneratorLabel">
                        <i class="fa fa-paper-plane me-2"></i>Blasting Email Slip Gaji (Preview Recounting)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info small rounded-3 mb-3">
                        <i class="fa fa-info-circle me-1"></i>
                        Fitur ini mengirimkan email slip gaji digital (format preview hasil recounting) ke karyawan yang dicentang di tabel. Sangat berguna agar karyawan atau tim HR dapat memeriksa apakah data presensi, tunjangan, dan potongan sudah sesuai sebelum batch penggajian digenerate secara resmi.
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="p-2 border rounded-3 bg-light">
                                <small class="text-muted d-block fw-semibold">Salary Group:</small>
                                <strong id="blastGenMainId" class="text-dark">-</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-2 border rounded-3 bg-light">
                                <small class="text-muted d-block fw-semibold">Periode Penggajian:</small>
                                <strong id="blastGenPeriod" class="text-dark">-</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-2 border rounded-3 bg-light">
                                <small class="text-muted d-block fw-semibold">Penerima Tercentang:</small>
                                <strong id="blastGenCount" class="text-primary fs-6">0 Orang</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Bar Pengiriman -->
                    <div id="blastProgressBarWrapper" class="mb-3" style="display: none;">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span id="blastProgressText">Memulai pengiriman email...</span>
                            <span id="blastProgressPercent" class="fw-bold text-primary">0%</span>
                        </div>
                        <div class="progress" style="height: 12px; border-radius: 6px;">
                            <div id="blastProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%"></div>
                        </div>
                    </div>

                    <!-- Live Results Table -->
                    <div class="table-responsive border rounded-3" style="max-height: 250px; overflow-y: auto;">
                        <table class="table table-sm table-hover align-middle mb-0" id="blastResultsTable">
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
                            <tbody id="blastResultsBody" class="small">
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Klik tombol "Kirim Blasting Sekarang" untuk memulai proses pengiriman email slip.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal" id="btnCancelBlastGen">Tutup</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" id="btnStartBlastGen" onclick="startBlastingGenerator()">
                        <i class="fa fa-paper-plane me-1"></i> Kirim Blasting Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Penyimpanan override nilai per NIK jika user melakukan upload Excel
        window.gridOverrides = {};

        function formatRupiahNumber(num) {
            var n = parseFloat(num);
            if (isNaN(n)) n = 0;
            return Math.round(n).toLocaleString('id-ID');
        }

        function regenerateTaskId() {
            var now = new Date();
            var pad = function(n) {
                return n < 10 ? '0' + n : '' + n;
            };
            var stamp = String(now.getFullYear()).slice(-2) +
                pad(now.getMonth() + 1) +
                pad(now.getDate()) +
                pad(now.getHours()) +
                pad(now.getMinutes()) +
                pad(now.getSeconds());
            var newId = "PG-{{ $lsp }}-" + stamp;
            $("#Task_ID").val(newId);
            $("#kpiTaskIdDisplay").text(newId);
        }

        function onPeriodChange() {
            var per = $("#Salary_Period").val();
            $("#kpiPeriodDisplay").text(per || '-');
            if (per) {
                $("#Payroll_Date").val(per + "-01");
            }
            refreshdata();
        }

        function onUseForFilterChange() {
            var selectedFilter = ($("#Use_For").val() || 'ALL').toUpperCase();
            var $mainSelect = $("#Main_ID");
            var currentVal = $mainSelect.val();
            var firstVisibleVal = '';

            $mainSelect.find('option').each(function() {
                var val = $(this).val();
                if (!val) return;
                var grpUseFor = ($(this).data('use-for') || '').toString().toUpperCase();
                var show = true;

                if (selectedFilter === 'INTERNAL') {
                    show = (grpUseFor === 'INTERNAL' || grpUseFor === '');
                } else if (selectedFilter === 'NON_INTERNAL') {
                    show = (grpUseFor !== 'INTERNAL');
                } else if (selectedFilter !== 'ALL' && selectedFilter !== 'CUSTOM') {
                    show = (grpUseFor === selectedFilter);
                }

                if (show) {
                    $(this).show().prop('disabled', false);
                    if (!firstVisibleVal) firstVisibleVal = val;
                } else {
                    $(this).hide().prop('disabled', true);
                }
            });

            var $currentOpt = $mainSelect.find('option[value="' + currentVal + '"]');
            if ($currentOpt.length && $currentOpt.prop('disabled')) {
                $mainSelect.val(firstVisibleVal);
            }

            refreshdata();
        }

        function recalculateGridTotals() {
            var totalThp = 0;
            var selectedCount = 0;
            var totalRows = EmployeeSalary.getRowsNum();
            var catVal = $("#Category").val() || 'SALARY';
            var useForVal = $("#Use_For").val() || 'ALL';

            for (var i = 0; i < totalRows; i++) {
                var rowId = EmployeeSalary.getRowId(i);
                if (!rowId) continue;

                // Sinkronkan kolom Type Sal (col 16)
                if (EmployeeSalary.getColumnsNum() > 16) {
                    EmployeeSalary.cells(rowId, 16).setValue(catVal);
                }

                var isChecked = EmployeeSalary.cells(rowId, 0).getValue();
                if (isChecked == 1 || isChecked === '1' || isChecked === true || isChecked === 'true') {
                    selectedCount++;
                    var rowThp = parseFloat(EmployeeSalary.cells(rowId, 14).getValue()) || 0;
                    totalThp += rowThp;
                }
            }

            $("#Total").val(Math.round(totalThp));
            $("#Total_Formatted").val(formatRupiahNumber(totalThp));
            $("#kpiTotalThpDisplay").text('Rp ' + formatRupiahNumber(totalThp));
            $("#kpiSelectedCount").text(selectedCount);
            $("#kpiTotalRows").text(totalRows);
        }

        function loadsal() {
            setTimeout(function() {
                recalculateGridTotals();
                // if (typeof $.unblockUI === 'function') {
                //     $.unblockUI();
                // }
            }, 150);
        }

        function toggleSelectAllRows(checkState) {
            var val = checkState ? 1 : 0;
            var totalRows = EmployeeSalary.getRowsNum();
            for (var i = 0; i < totalRows; i++) {
                var rowId = EmployeeSalary.getRowId(i);
                EmployeeSalary.cells(rowId, 0).setValue(val);
            }
            recalculateGridTotals();
        }

        function EmployeeSalaryKlik() {
            var selectedId = EmployeeSalary.getSelectedId();
            if (!selectedId) return true;
            var employeeNik = EmployeeSalary.cells(selectedId, 2).getValue();

            if (employeeNik != '') {
                LoadM('divlistProjectOrg', '{{ url('payroll-generator-detail') }}?ID=' + encodeURIComponent(employeeNik));
                setTimeout(function() {
                    var el = document.getElementById('divlistProjectOrg');
                    if (el) el.scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest'
                    });
                }, 250);
            }
            return true;
        }

        function recountingCurrentGroup() {
            var mainId = $("#Main_ID").val();
            var period = $("#Salary_Period").val() || '{{ $defaultPeriod }}';

            if (!mainId) {
                alert('Pilih Salary Group terlebih dahulu sebelum melakukan Recounting.');
                return;
            }

            // if (typeof $.blockUI === 'function') {
            //     $.blockUI({
            //         message: '<div class="p-3"><h6><i class="fa fa-spinner fa-spin me-2 text-primary"></i>Menyinkronkan Presensi Cutoff, BPJS &amp; PPh 21 TER untuk grup ' +
            //             mainId + '...</h6></div>'
            //     });
            // }

            $.ajax({
                url: '{{ url('payroll-recounting-ajax') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    Main_ID: mainId,
                    periode: period
                },
                success: function(res) {
                    if (typeof $.unblockUI === 'function') {
                        $.unblockUI();
                    }
                    if (res && res.success) {
                        alert(
                            'Recounting Grup Berhasil!\n' +
                            '• Total Karyawan: ' + res.data.total_employees + ' orang\n' +
                            '• Estimasi Total THP: Rp ' + formatRupiahNumber(res.data.total_thp)
                        );
                        refreshdata();
                    } else {
                        alert('Gagal melakukan Recounting: ' + (res.message || 'Unknown error'));
                    }
                },
                error: function() {
                    if (typeof $.unblockUI === 'function') {
                        $.unblockUI();
                    }
                    alert('Terjadi kesalahan saat memproses Recounting grup.');
                }
            });
        }

        function downloadTemplateExcel() {
            var mainId = $("#Main_ID").val() || '';
            window.open('{{ url('payroll-generator-template') }}?Main_ID=' + encodeURIComponent(mainId), '_blank');
        }

        function submitExcelUpload() {
            var fileInput = document.getElementById('excelFileInput');
            if (!fileInput || !fileInput.files || !fileInput.files.length) {
                alert('Pilih file Excel (.xlsx / .xls / .csv) terlebih dahulu.');
                return;
            }

            var formData = new FormData();
            formData.append('file', fileInput.files[0]);
            formData.append('_token', '{{ csrf_token() }}');

            $("#btnProcessUploadExcel").prop('disabled', true).html(
                '<i class="fa fa-spinner fa-spin me-1"></i> Memproses...');
            $("#uploadExcelStatus").html(
                '<div class="text-primary"><i class="fa fa-spinner fa-spin me-1"></i> Membaca baris data dari file Excel...</div>'
            );

            $.ajax({
                url: '{{ url('payroll-generator-upload-excel') }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    $("#btnProcessUploadExcel").prop('disabled', false).html(
                        '<i class="fa fa-check me-1"></i> Terapkan ke Tabel');
                    if (res && res.success && Array.isArray(res.rows)) {
                        var updatedCount = 0;
                        res.rows.forEach(function(item) {
                            var nik = String(item.nik || '').trim();
                            if (!nik || !EmployeeSalary.doesRowExist(nik)) {
                                return;
                            }
                            window.gridOverrides[nik] = item;

                            if (item.pokok !== null && !isNaN(item.pokok)) {
                                EmployeeSalary.cells(nik, 7).setValue(item.pokok);
                            }
                            if (item.tunjangan !== null && !isNaN(item.tunjangan)) {
                                EmployeeSalary.cells(nik, 8).setValue(item.tunjangan);
                            }
                            if (item.overtime !== null && !isNaN(item.overtime)) {
                                EmployeeSalary.cells(nik, 9).setValue(item.overtime);
                            }
                            if (item.kjk !== null && !isNaN(item.kjk)) {
                                EmployeeSalary.cells(nik, 10).setValue(item.kjk);
                            }
                            if (item.deduction !== null && !isNaN(item.deduction)) {
                                EmployeeSalary.cells(nik, 12).setValue(item.deduction);
                            }
                            if (item.note) {
                                EmployeeSalary.cells(nik, 17).setValue(item.note);
                            }

                            // Hitung ulang THP baris tersebut
                            var pokok = parseFloat(EmployeeSalary.cells(nik, 7).getValue()) || 0;
                            var tunj = parseFloat(EmployeeSalary.cells(nik, 8).getValue()) || 0;
                            var ot = parseFloat(EmployeeSalary.cells(nik, 9).getValue()) || 0;
                            var kjk = parseFloat(EmployeeSalary.cells(nik, 10).getValue()) || 0;
                            var whtAllow = parseFloat(EmployeeSalary.cells(nik, 11).getValue()) || 0;
                            var ded = parseFloat(EmployeeSalary.cells(nik, 12).getValue()) || 0;
                            var pph = parseFloat(EmployeeSalary.cells(nik, 13).getValue()) || 0;
                            var newThp = Math.max(0, (pokok + tunj + ot + kjk + whtAllow) - (ded +
                                pph));
                            EmployeeSalary.cells(nik, 14).setValue(newThp);
                            EmployeeSalary.cells(nik, 0).setValue(1);
                            updatedCount++;
                        });

                        recalculateGridTotals();
                        $("#uploadExcelStatus").html(
                            '<div class="alert alert-success py-2 mb-0">' +
                            '<i class="fa fa-check-circle me-1"></i> Berhasil menerapkan <strong>' +
                            updatedCount + '</strong> dari ' + res.rows.length + ' baris Excel ke tabel.' +
                            '</div>'
                        );
                    } else {
                        $("#uploadExcelStatus").html(
                            '<div class="alert alert-danger py-2 mb-0">' + (res.message ||
                                'Gagal membaca file Excel.') + '</div>'
                        );
                    }
                },
                error: function(xhr) {
                    $("#btnProcessUploadExcel").prop('disabled', false).html(
                        '<i class="fa fa-check me-1"></i> Terapkan ke Tabel');
                    var errMsg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message :
                        'Gagal mengunggah file Excel.';
                    $("#uploadExcelStatus").html('<div class="alert alert-danger py-2 mb-0">' + errMsg +
                        '</div>');
                }
            });
        }

        // Inisialisasi dhtmlxGrid EmployeeSalary
        EmployeeSalary = new dhtmlXGridObject('EmployeeSalary');
        EmployeeSalary.setImagePath("{{ asset('dhtmlx/imgs/') }}/");
        EmployeeSalary.setHeader(
            "Select,Name,NIK,Gender,Group Category,Salary Group,Position,Pokok,Allowance,Overtime,KJK,WHT Allowance,Deduction,PPH,THP,Category,Type Sal,Note"
        );
        EmployeeSalary.attachHeader(
            "#master_checkbox,#text_filter,#text_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#combo_filter,,"
        );
        EmployeeSalary.attachFooter(
            "Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,,,"
        );
        EmployeeSalary.setInitWidths("65,170,105,80,110,130,130,115,115,110,100,115,115,110,125,105,100,220");
        EmployeeSalary.setColAlign(
            "center,left,center,center,center,center,left,right,right,right,right,right,right,right,right,center,center,left"
        );
        EmployeeSalary.setColTypes("ch,ro,ro,ro,ro,ro,ro,ron,ron,ron,ron,ron,ron,ron,ron,ro,ro,ro");
        EmployeeSalary.setColSorting("int,str,str,str,str,str,str,int,int,int,int,int,int,int,int,str,str,str");
        EmployeeSalary.setNumberFormat("0,000", 7, ".", ",");
        EmployeeSalary.setNumberFormat("0,000", 8, ".", ",");
        EmployeeSalary.setNumberFormat("0,000", 9, ".", ",");
        EmployeeSalary.setNumberFormat("0,000", 10, ".", ",");
        EmployeeSalary.setNumberFormat("0,000", 11, ".", ",");
        EmployeeSalary.setNumberFormat("0,000", 12, ".", ",");
        EmployeeSalary.setNumberFormat("0,000", 13, ".", ",");
        EmployeeSalary.setNumberFormat("0,000", 14, ".", ",");

        PEmployeeSalary = new dataProcessor("{{ url('payroll-generator-detail-update') }}");
        PEmployeeSalary.setTransactionMode({
            mode: "POST",
            total: true,
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            payload: {
                "ID": $("#Task_ID").val(),
                "Period": $("#Payroll_Date").val()
            }
        });
        PEmployeeSalary.setUpdateMode("Off");
        PEmployeeSalary.init(EmployeeSalary);

        EmployeeSalary.attachEvent("onRowDblClicked", EmployeeSalaryKlik);
        EmployeeSalary.attachEvent("onCheck", function(rId, cInd, state) {
            recalculateGridTotals();
        });
        EmployeeSalary.setSkin("dhx_skyblue");
        EmployeeSalary.setEditable(true);
        EmployeeSalary.init();

        function refreshdata() {
            var mainId = $("#Main_ID").val();
            var useFor = $("#Use_For").val() || 'ALL';
            var category = $("#Category").val() || 'SALARY';

            window.gridOverrides = {};
            $('#divlistProjectOrg').empty();

            if (!mainId) {
                EmployeeSalary.clearAll();
                recalculateGridTotals();
                return;
            }

            // if (typeof $.blockUI === 'function') {
            //     $.blockUI({
            //         message: '<div class="p-3"><i class="fa fa-spinner fa-spin me-2 text-primary"></i>Memuat daftar penggajian karyawan...</div>'
            //     });
            // }

            var salaryPeriod = $("#Salary_Period").val() || '{{ $defaultPeriod }}';
            var url = '{{ url('xml-payroll-generator-employee') }}?Use_For=' + encodeURIComponent(useFor) +
                '&Main_ID=' + encodeURIComponent(mainId) +
                '&Category=' + encodeURIComponent(category) +
                '&Salary_Period=' + encodeURIComponent(salaryPeriod);

            EmployeeSalary.clearAndLoad(url, loadsal);
        }

        function getCheckedNiks() {
            var niks = [];
            var totalRows = EmployeeSalary.getRowsNum();
            for (var i = 0; i < totalRows; i++) {
                var rowId = EmployeeSalary.getRowId(i);
                var isChecked = EmployeeSalary.cells(rowId, 0).getValue();
                if (isChecked == 1 || isChecked === '1' || isChecked === true || isChecked === 'true') {
                    niks.push(rowId);
                }
            }
            return niks;
        }

        $('#SubmitCustDetail').click(function() {
            var mainId = $("#Main_ID").val();
            if (!mainId) {
                alert('Pilih Salary Group (Apply to Group) terlebih dahulu!');
                $("#Main_ID").focus();
                return;
            }

            var checkedNiks = getCheckedNiks();
            if (checkedNiks.length === 0) {
                alert('Pilih (centang) minimal 1 karyawan pada tabel untuk digenerate!');
                return;
            }

            var taskId = $("#Task_ID").val();
            var period = $("#Salary_Period").val();
            var totalFormatted = $("#Total_Formatted").val();

            dhtmlx.confirm({
                title: 'Konfirmasi Generate Batch Payroll',
                text: 'Proses batch penggajian <strong>' + taskId + '</strong> untuk <strong>' + checkedNiks
                    .length + ' karyawan</strong> (Total THP: <strong>Rp ' + totalFormatted + '</strong>)?',
                callback: function(r) {
                    if (r === true) {
                        if (typeof $.blockUI === 'function') {
                            $.blockUI({
                                message: '<div class="p-3"><h6><i class="fa fa-spinner fa-spin me-2 text-primary"></i>Sedang memproses &amp; menyimpan Batch Payroll ' +
                                    taskId + '...</h6></div>'
                            });
                        }

                        var payloadData = {
                            _token: '{{ csrf_token() }}',
                            Task_ID: taskId,
                            Payroll_Date: $("#Payroll_Date").val(),
                            Salary_Period: period,
                            Total: $("#Total").val(),
                            Category: $("#Category").val(),
                            Use_For: $("#Use_For").val(),
                            Main_ID: mainId,
                            Note: $("#Note").val(),
                            selected_niks: checkedNiks.join(','),
                            grid_overrides: JSON.stringify(window.gridOverrides || {})
                        };

                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            url: "{{ url('payroll-generator-submit') }}",
                            type: "POST",
                            data: payloadData,
                            success: function(data) {
                                if (typeof $.unblockUI === 'function') {
                                    $.unblockUI();
                                }

                                var isSuccess = false;
                                var msg = "Batch Payroll " + taskId +
                                    " berhasil digenerate!";

                                if (typeof data === 'object' && data !== null) {
                                    isSuccess = (data.success === true);
                                    if (data.message) msg = data.message;
                                } else if (typeof data === 'string') {
                                    isSuccess = (data.indexOf("success") !== -1);
                                }

                                if (isSuccess) {
                                    alert(msg);
                                    location.href = '{{ url('payroll-approval') }}';
                                } else {
                                    alert('Gagal Generate Payroll: ' + (data.message ||
                                        data));
                                }
                            },
                            error: function(jqXHR) {
                                if (typeof $.unblockUI === 'function') {
                                    $.unblockUI();
                                }
                                var errMsg = (jqXHR.responseJSON && jqXHR.responseJSON
                                        .message) ?
                                    jqXHR.responseJSON.message :
                                    "Terjadi kesalahan internal! Silakan coba kembali.";
                                alert(errMsg);
                            }
                        });
                    }
                }
            });
        });

        // =========================================================================
        // FITUR PREVIEW PAYSLIP DIGITAL & BLASTING EMAIL (PREVIEW RECOUNTING)
        // =========================================================================

        function openSelectedEmployeeSlip() {
            var selectedRowId = EmployeeSalary.getSelectedRowId();
            if (!selectedRowId) {
                alert('Pilih salah satu baris karyawan pada tabel terlebih dahulu.');
                return;
            }
            var periodVal = $("#Salary_Period").val() || '{{ $defaultPeriod }}';
            var mainIdVal = $("#Main_ID").val() || '';
            var url = "{{ url('payslip-digital') }}?nik=" + encodeURIComponent(selectedRowId) +
                "&period=" + encodeURIComponent(periodVal) +
                "&main_id=" + encodeURIComponent(mainIdVal) +
                "&source=generator";
            window.open(url, '_blank');
        }

        var blastQueueGen = [];
        var blastIndexGen = 0;
        var blastSuccessGen = 0;
        var blastFailedGen = 0;
        var blastSkippedGen = 0;

        function openBlastModalGenerator() {
            var mainId = $("#Main_ID").val();
            if (!mainId) {
                alert('Pilih Salary Group terlebih dahulu.');
                return;
            }

            blastQueueGen = [];
            var allIds = EmployeeSalary.getAllRowIds();
            if (allIds) {
                var rowIdArr = allIds.split(',');
                for (var i = 0; i < rowIdArr.length; i++) {
                    var rId = rowIdArr[i].trim();
                    if (!rId) continue;
                    var isChecked = EmployeeSalary.cells(rId, 0).getValue();
                    if (isChecked == 1 || isChecked == '1' || isChecked === true) {
                        blastQueueGen.push(rId);
                    }
                }
            }

            if (blastQueueGen.length === 0) {
                alert('Centang minimal 1 karyawan pada tabel untuk melakukan blasting email slip.');
                return;
            }

            $("#blastGenMainId").text(mainId);
            $("#blastGenPeriod").text($("#Salary_Period").val() || '{{ $defaultPeriod }}');
            $("#blastGenCount").text(blastQueueGen.length + ' Orang');

            $("#blastProgressBarWrapper").hide();
            $("#blastProgressBar").css('width', '0%').removeClass('bg-success').addClass('bg-primary progress-bar-animated');
            $("#blastProgressPercent").text('0%');
            $("#blastProgressText").text('Menunggu konfirmasi...');
            $("#blastResultsBody").html('<tr><td colspan="6" class="text-center py-4 text-muted">Klik tombol "Kirim Blasting Sekarang" untuk memulai proses pengiriman ke ' + blastQueueGen.length + ' karyawan terpilih.</td></tr>');

            $("#btnStartBlastGen").prop('disabled', false).html('<i class="fa fa-paper-plane me-1"></i> Kirim Blasting Sekarang');
            $("#btnCancelBlastGen").prop('disabled', false);

            var modalEl = document.getElementById('modalBlastEmailGenerator');
            var bsModal = new bootstrap.Modal(modalEl);
            bsModal.show();
        }

        function startBlastingGenerator() {
            if (!blastQueueGen.length) return;
            if (!confirm('Yakin ingin mengirim email slip gaji preview ke ' + blastQueueGen.length + ' karyawan terpilih?')) return;

            $("#btnStartBlastGen").prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Sedang Mengirim...');
            $("#btnCancelBlastGen").prop('disabled', true);
            $("#blastProgressBarWrapper").show();
            $("#blastResultsBody").empty();

            blastIndexGen = 0;
            blastSuccessGen = 0;
            blastFailedGen = 0;
            blastSkippedGen = 0;

            processNextBlastChunkGen();
        }

        function processNextBlastChunkGen() {
            if (blastIndexGen >= blastQueueGen.length) {
                // Selesai
                $("#btnStartBlastGen").prop('disabled', false).html('<i class="fa fa-check me-1"></i> Selesai');
                $("#btnCancelBlastGen").prop('disabled', false);
                $("#blastProgressBar").removeClass('progress-bar-animated').addClass('bg-success');
                $("#blastProgressText").html('<strong class="text-success">Pengiriman Selesai!</strong> Berhasil: ' + blastSuccessGen + ', Gagal: ' + blastFailedGen + ', Dilewati: ' + blastSkippedGen);
                return;
            }

            var chunkSize = 3;
            var chunkNiks = blastQueueGen.slice(blastIndexGen, blastIndexGen + chunkSize);
            var periodVal = $("#Salary_Period").val() || '{{ $defaultPeriod }}';
            var mainIdVal = $("#Main_ID").val() || '';

            $.ajax({
                url: '{{ url('api/payroll/blast-payslip-emails') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    niks: chunkNiks,
                    period: periodVal,
                    main_id: mainIdVal,
                    source: 'generator'
                },
                success: function(res) {
                    if (res && res.results) {
                        res.results.forEach(function(item) {
                            blastIndexGen++;
                            var badgeHtml = '';
                            if (item.status === 'SENT') {
                                blastSuccessGen++;
                                badgeHtml = '<span class="badge bg-success text-white">Terkirim</span>';
                            } else if (item.status === 'SKIPPED') {
                                blastSkippedGen++;
                                badgeHtml = '<span class="badge bg-warning text-dark">Dilewati</span>';
                            } else {
                                blastFailedGen++;
                                badgeHtml = '<span class="badge bg-danger text-white">Gagal</span>';
                            }

                            var rowHtml = '<tr>' +
                                '<td class="text-center">' + blastIndexGen + '</td>' +
                                '<td class="font-monospace fw-semibold">' + item.nik + '</td>' +
                                '<td>' + (item.name || '-') + '</td>' +
                                '<td class="small text-muted">' + (item.email || '-') + '</td>' +
                                '<td class="text-center">' + badgeHtml + '</td>' +
                                '<td class="small text-truncate" style="max-width: 200px;" title="' + (item.message || '') + '">' + (item.message || '-') + '</td>' +
                            '</tr>';
                            $("#blastResultsBody").append(rowHtml);
                        });
                    } else {
                        blastIndexGen += chunkNiks.length;
                        blastFailedGen += chunkNiks.length;
                    }

                    var pct = Math.min(100, Math.round((blastIndexGen / blastQueueGen.length) * 100));
                    $("#blastProgressBar").css('width', pct + '%');
                    $("#blastProgressPercent").text(pct + '%');
                    $("#blastProgressText").text('Mengirim ' + blastIndexGen + ' dari ' + blastQueueGen.length + ' karyawan...');

                    var container = $("#blastResultsTable").parent();
                    container.scrollTop(container.prop('scrollHeight'));

                    setTimeout(processNextBlastChunkGen, 200);
                },
                error: function() {
                    blastIndexGen += chunkNiks.length;
                    blastFailedGen += chunkNiks.length;
                    var pct = Math.min(100, Math.round((blastIndexGen / blastQueueGen.length) * 100));
                    $("#blastProgressBar").css('width', pct + '%');
                    $("#blastProgressPercent").text(pct + '%');
                    setTimeout(processNextBlastChunkGen, 200);
                }
            });
        }

        // Muat data otomatis untuk Salary Group pertama saat halaman dibuka
        $(document).ready(function() {
            if ($("#Main_ID").val()) {
                refreshdata();
            }
        });
    </script>

    <!-- Modal Potongan SPPD, Kasbon & Reimburse (Auto-Pull + Manual Input) -->
    <div class="modal fade" id="advanceDeductionsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header bg-warning bg-opacity-25 border-bottom">
                    <div>
                        <h5 class="modal-title fw-bold text-dark">
                            <i class="fa fa-scissors me-2 text-warning"></i>Potongan SPPD, Kasbon &amp; Reimburse
                        </h5>
                        <small class="text-muted">Tarik otomatis item outstanding atau input/ubah nominal cicilan secara manual sebelum Submit Payroll.</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div>
                            <span class="badge bg-primary text-white me-2" id="advDedGroupBadge">Group: -</span>
                            <span class="badge bg-info text-white" id="advDedPeriodBadge">Periode: -</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" onclick="runAdvanceAutoPull()">
                            <i class="fa fa-download me-1"></i> Auto-Pull Outstanding (SPPD / Kasbon / Reimburse)
                        </button>
                    </div>

                    <!-- Form Tambah Manual -->
                    <div class="card bg-light border-0 rounded-3 mb-3">
                        <div class="card-body p-3">
                            <h6 class="fw-bold mb-2 small text-uppercase text-secondary">Tambah Potongan / Kasbon Manual</h6>
                            <div class="row g-2 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Karyawan (Employment)</label>
                                    <select id="advManNik" class="form-select form-select-sm">
                                        <option value="">-- Memuat karyawan... --</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small mb-1">Tipe</label>
                                    <select id="advManType" class="form-select form-select-sm">
                                        <option value="KASBON_MANUAL">Kasbon Manual</option>
                                        <option value="SPPD_UNSETTLED">SPPD Belum Settlement</option>
                                        <option value="KASBON_SPPD_EXCESS">Kasbon Selisih SPPD</option>
                                        <option value="REIMBURSE">Reimburse</option>
                                        <option value="OTHER">Lainnya</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small mb-1">Keterangan</label>
                                    <input type="text" id="advManDesc" class="form-control form-control-sm" placeholder="Deskripsi potongan / nomor referensi">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small mb-1">Nominal (Rp)</label>
                                    <input type="number" id="advManAmount" class="form-control form-control-sm" min="0" step="1000" placeholder="0">
                                </div>
                                <div class="col-md-1 d-grid">
                                    <button type="button" class="btn btn-sm btn-success text-white" onclick="addManualAdvanceDeduction()">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel Item -->
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width:55px;">Ikut</th>
                                    <th>NIK / Nama</th>
                                    <th>Tipe &amp; Sumber</th>
                                    <th>Keterangan</th>
                                    <th class="text-end">Sisa Saldo</th>
                                    <th class="text-end" style="width:160px;">Nominal Potong</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center" style="width:110px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="advDedTableBody">
                                <tr><td colspan="8" class="text-center text-muted py-3">Memuat data...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <div class="me-auto fw-bold text-dark">
                        Total Potongan Aktif (DRAFT): <span class="text-danger" id="advDedTotalText">Rp 0</span>
                    </div>
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal" onclick="refreshdata()">Tutup &amp; Refresh Grid</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openAdvanceDeductionsModal() {
            var mainId = $("#Main_ID").val() || '';
            var periode = $("#Salary_Period").val() || '{{ $defaultPeriod }}';
            $("#advDedGroupBadge").text('Group: ' + (mainId || 'SEMUA'));
            $("#advDedPeriodBadge").text('Periode: ' + periode);
            var modalEl = document.getElementById('advanceDeductionsModal');
            if (window.bootstrap && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            } else {
                $(modalEl).modal('show');
            }
            loadAdvanceEmploymentOptions();
            loadAdvanceDeductionsList();
        }

        function loadAdvanceEmploymentOptions() {
            var mainId = $("#Main_ID").val() || '';
            var $sel = $("#advManNik");
            $sel.html('<option value="">-- Memuat karyawan... --</option>');
            $.get("{{ url('payroll-deductions/employees') }}", { main_id: mainId }, function(res) {
                var opts = '<option value="">-- Pilih Karyawan --</option>';
                if (res && res.success && Array.isArray(res.data)) {
                    res.data.forEach(function(emp) {
                        var label = emp.NIK + ' - ' + (emp.Name || '-') + (emp.Position ? ' (' + emp.Position + ')' : '');
                        opts += '<option value="' + emp.NIK + '">' + label + '</option>';
                    });
                }
                $sel.html(opts);
                if ($.fn.select2) {
                    $sel.select2({
                        dropdownParent: $('#advanceDeductionsModal'),
                        width: '100%',
                        placeholder: '-- Pilih Karyawan --'
                    });
                }
            });
        }

        function loadAdvanceDeductionsList() {
            var mainId = $("#Main_ID").val() || '';
            var periode = $("#Salary_Period").val() || '{{ $defaultPeriod }}';
            $.get("{{ url('payroll-deductions/list') }}", { main_id: mainId, periode: periode }, function(res) {
                if (!res || !res.success) return;
                var rows = res.data.items || [];
                $("#advDedTotalText").text('Rp ' + Number(res.data.total_included || 0).toLocaleString('id-ID'));
                if (!rows.length) {
                    $("#advDedTableBody").html('<tr><td colspan="8" class="text-center text-muted py-3">Belum ada item potongan untuk grup &amp; periode ini. Klik <b>Auto-Pull Outstanding</b> atau tambah manual.</td></tr>');
                    return;
                }
                var html = '';
                rows.forEach(function(item) {
                    var isDraft = item.status === 'DRAFT';
                    var isIncluded = Number(item.is_included) === 1;
                    var isManual = Number(item.is_auto_pulled) === 0;
                    var checked = isIncluded ? 'checked' : '';
                    var srcBadge = !isManual ? '<span class="badge bg-info text-white">AUTO</span>' : '<span class="badge bg-secondary text-white">MANUAL</span>';
                    var stBadge = isDraft ? '<span class="badge bg-warning text-dark">DRAFT</span>' : '<span class="badge bg-success text-white">POSTED</span>';
                    var delDisabled = isIncluded ? ' disabled title="Uncheck (keluarkan dari potongan) terlebih dahulu sebelum menghapus"' : ' title="Hapus item manual"';
                    html += '<tr>' +
                        '<td class="text-center"><input type="checkbox" class="form-check-input" id="adv_inc_' + item.id + '" ' + checked + (isDraft ? ' onchange="toggleAdvanceInclude(' + item.id + ', this)"' : ' disabled') + '></td>' +
                        '<td><div class="fw-semibold font-monospace">' + item.nik + '</div><small class="text-muted">' + (item.employee_name || '-') + '</small></td>' +
                        '<td><div>' + item.deduction_type + '</div>' + srcBadge + ' <small class="text-muted">' + (item.reference_no || '') + '</small></td>' +
                        '<td>' + (item.description || '-') + '</td>' +
                        '<td class="text-end">Rp ' + Number(item.remaining_amount || 0).toLocaleString('id-ID') + '</td>' +
                        '<td><input type="number" class="form-control form-control-sm text-end" id="adv_amt_' + item.id + '" value="' + Number(item.deducted_amount || 0) + '" min="0" step="1000"' + (isDraft ? '' : ' disabled') + '></td>' +
                        '<td class="text-center">' + stBadge + '</td>' +
                        '<td class="text-center">' +
                            (isDraft ? '<button type="button" class="btn btn-xs btn-primary me-1" onclick="updateAdvanceDeductionRow(' + item.id + ')" title="Simpan Perubahan"><i class="fa fa-save"></i></button>' : '') +
                            (isDraft && isManual ? '<button type="button" class="btn btn-xs btn-danger" id="adv_del_' + item.id + '" onclick="deleteAdvanceDeductionRow(' + item.id + ')"' + delDisabled + '><i class="fa fa-trash"></i></button>' : '') +
                        '</td>' +
                    '</tr>';
                });
                $("#advDedTableBody").html(html);
            });
        }

        function runAdvanceAutoPull() {
            var mainId = $("#Main_ID").val() || '';
            var periode = $("#Salary_Period").val() || '{{ $defaultPeriod }}';
            $.post("{{ url('payroll-deductions/auto-pull') }}", {
                _token: "{{ csrf_token() }}",
                main_id: mainId,
                periode: periode
            }, function(res) {
                alert((res && res.message) ? res.message : 'Auto-pull selesai.');
                loadAdvanceDeductionsList();
            });
        }

        function addManualAdvanceDeduction() {
            var nik = $("#advManNik").val().trim();
            var amount = $("#advManAmount").val();
            if (!nik || !amount || Number(amount) <= 0) {
                alert('NIK dan Nominal wajib diisi lebih dari 0.');
                return;
            }
            $.post("{{ url('payroll-deductions/save') }}", {
                _token: "{{ csrf_token() }}",
                nik: nik,
                main_id: $("#Main_ID").val() || '',
                salary_period: $("#Salary_Period").val() || '{{ $defaultPeriod }}',
                deduction_type: $("#advManType").val(),
                description: $("#advManDesc").val().trim() || 'Potongan Kasbon Manual',
                deducted_amount: amount
            }, function(res) {
                if (res && res.success) {
                    $("#advManDesc").val('');
                    $("#advManAmount").val('');
                    loadAdvanceDeductionsList();
                } else {
                    alert((res && res.message) ? res.message : 'Gagal menyimpan.');
                }
            }).fail(function(xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menyimpan potongan.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'Melebihi Batas THP', text: msg });
                } else {
                    alert(msg);
                }
            });
        }

        function updateAdvanceDeductionRow(id) {
            $.post("{{ url('payroll-deductions/save') }}", {
                _token: "{{ csrf_token() }}",
                id: id,
                deducted_amount: $("#adv_amt_" + id).val(),
                is_included: $("#adv_inc_" + id).is(':checked') ? 1 : 0
            }, function(res) {
                if (res && res.success) {
                    loadAdvanceDeductionsList();
                } else {
                    alert((res && res.message) ? res.message : 'Gagal update.');
                    loadAdvanceDeductionsList();
                }
            }).fail(function(xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal update potongan.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'Melebihi Batas THP', text: msg });
                } else {
                    alert(msg);
                }
                loadAdvanceDeductionsList();
            });
        }

        function toggleAdvanceInclude(id, checkboxEl) {
            var isInc = $(checkboxEl).is(':checked') ? 1 : 0;
            $.post("{{ url('payroll-deductions/save') }}", {
                _token: "{{ csrf_token() }}",
                id: id,
                deducted_amount: $("#adv_amt_" + id).val(),
                is_included: isInc
            }, function(res) {
                if (res && res.success) {
                    loadAdvanceDeductionsList();
                } else {
                    alert((res && res.message) ? res.message : 'Gagal memperbarui status potongan.');
                    loadAdvanceDeductionsList();
                }
            }).fail(function(xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal memperbarui status potongan.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'Melebihi Batas THP', text: msg });
                } else {
                    alert(msg);
                }
                loadAdvanceDeductionsList();
            });
        }

        function deleteAdvanceDeductionRow(id) {
            if ($("#adv_inc_" + id).is(':checked')) {
                alert('Silakan uncheck (keluarkan dari potongan) terlebih dahulu sebelum menghapus item ini.');
                return;
            }
            var executeDelete = function() {
                $.ajax({
                    url: "{{ url('payroll-deductions') }}/" + id,
                    type: 'DELETE',
                    data: { _token: "{{ csrf_token() }}" },
                    success: function(res) {
                        if (res && res.message && typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'success', title: 'Terhapus', text: res.message, timer: 1500, showConfirmButton: false });
                        }
                        loadAdvanceDeductionsList();
                    },
                    error: function(xhr) {
                        var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menghapus item.';
                        alert(msg);
                    }
                });
            };
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Hapus Potongan Manual?',
                    text: 'Data potongan yang sudah dihapus tidak dapat dikembalikan.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        executeDelete();
                    }
                });
            } else if (confirm('Apakah Anda yakin ingin menghapus item potongan manual ini?')) {
                executeDelete();
            }
        }
    </script>
@endsection
