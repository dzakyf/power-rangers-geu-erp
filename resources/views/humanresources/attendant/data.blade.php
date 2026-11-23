@extends('layout.mainlayout-hrd')

@push('head')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<style>
.metric-card {
    border-radius: 14px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(0,0,0,0.06);
}
.badge-status {
    font-size: 11px;
    padding: 5px 10px;
    border-radius: 20px;
    font-weight: 600;
}
.selfie-thumb {
    width: 32px;
    height: 32px;
    object-fit: cover;
    border-radius: 8px;
    cursor: pointer;
    transition: transform 0.15s ease;
}
.selfie-thumb:hover {
    transform: scale(1.15);
}
.loading-overlay {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(255, 255, 255, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    border-radius: 1rem;
}
.att-modal-header {
    background: #ffffff;
    border-bottom: 1px solid #eef1f6;
    padding: 1.1rem 1.5rem;
    border-top-left-radius: 1rem;
    border-top-right-radius: 1rem;
}
.att-modal-icon {
    width: 40px;
    height: 40px;
    min-width: 40px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}
</style>
@endpush

@section('content-wrapper')
    <!-- Main Content Header (Adminor V3 Responsive Hero Header) -->
    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
        <div class="mb-2 mb-md-0">
            <h4 class="content-title text-white fw-bold mb-1" style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
                <i class="fe fe-users me-2"></i>Monitoring &amp; Laporan Presensi Karyawan
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="/dashboard1" style="color: rgba(255, 255, 255, 0.8) !important;">Human Resources</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('attendance.portal') }}" style="color: rgba(255, 255, 255, 0.8) !important;">Employee Portal</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page" style="color: #ffffff !important; font-weight: 600;">Rekap Presensi</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex align-items-center flex-wrap gap-2">
            <div class="dropdown">
                <button class="btn btn-sm rounded-pill shadow-sm dropdown-toggle px-3" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
                    <i class="fe fe-sliders me-1"></i> Pengaturan &amp; Portal
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 py-2">
                    <li>
                        <a class="dropdown-item py-2" href="{{ route('attendance.portal') }}">
                            <i class="fe fe-monitor me-2 text-primary"></i> Portal Absen Saya
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2" href="{{ route('attendance.locations.index') }}">
                            <i class="fe fe-map-pin me-2 text-info"></i> Setting Lokasi &amp; Radius
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2" href="{{ route('attendance.cutoff.index') }}">
                            <i class="fe fe-calendar me-2 text-secondary"></i> Kelola Master Cutoff
                        </a>
                    </li>
                </ul>
            </div>

            @if(can_manual_correct_attendance())
                <div class="dropdown">
                    <button class="btn btn-warning btn-sm rounded-pill shadow-sm dropdown-toggle px-3 text-dark fw-semibold" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fe fe-edit-3 me-1"></i> Koreksi Absen
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 py-2">
                        <li>
                            <button type="button" class="dropdown-item py-2" onclick="openManualCorrectionModal()">
                                <i class="fe fe-edit-2 me-2 text-primary"></i> Input Perbaikan Manual
                            </button>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item py-2" onclick="openBulkCorrectionModal()">
                                <i class="fe fe-upload-cloud me-2 text-info"></i> Bulk Upload &amp; Proses Request
                            </button>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <button type="button" class="dropdown-item py-2" onclick="openManualSettingsModal()">
                                <i class="fe fe-settings me-2 text-dark"></i> Setting Koreksi Manual
                            </button>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item py-2" onclick="openManualAuditModal()">
                                <i class="fe fe-shield me-2 text-secondary"></i> Riwayat Audit Koreksi
                            </button>
                        </li>
                    </ul>
                </div>
            @endif

            <button type="button" class="btn btn-success btn-sm rounded-pill shadow-sm px-3 fw-semibold" onclick="downloadExcelReport()">
                <i class="fe fe-download me-1"></i> Export Excel
            </button>
        </div>
    </div>
    <!-- End Main Content Header -->

    <!-- Kartu Statistik & KPI Harian -->
    <div class="row g-3 mb-4" id="statsRow">
        <div class="col-md-2 col-sm-4 col-6">
            <div class="card border-0 shadow-sm metric-card bg-white h-100">
                <div class="card-body p-3 text-center">
                    <small class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 10px;">Total Karyawan</small>
                    <h3 class="fw-bold text-dark mb-0" id="stat-total">{{ $data['metrics']['total_employees'] }}</h3>
                    <span class="badge bg-light text-muted border mt-2" style="font-size: 10px;">Terdaftar Aktif</span>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6">
            <div class="card border-0 shadow-sm metric-card bg-white h-100">
                <div class="card-body p-3 text-center">
                    <small class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 10px;">Total Hadir</small>
                    <h3 class="fw-bold text-success mb-0" id="stat-present">{{ $data['metrics']['present'] }}</h3>
                    <span class="badge bg-success-subtle text-success border border-success-subtle mt-2" id="stat-pct" style="font-size: 10px;">
                        {{ $data['metrics']['total_employees'] > 0 ? round(($data['metrics']['present'] / $data['metrics']['total_employees']) * 100) : 0 }}% Kehadiran
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6">
            <div class="card border-0 shadow-sm metric-card bg-white h-100">
                <div class="card-body p-3 text-center">
                    <small class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 10px;">Tepat Waktu</small>
                    <h3 class="fw-bold text-primary mb-0" id="stat-ontime">{{ $data['metrics']['on_time'] }}</h3>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle mt-2" style="font-size: 10px;">On Time</span>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6">
            <div class="card border-0 shadow-sm metric-card bg-white h-100">
                <div class="card-body p-3 text-center">
                    <small class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 10px;">Terlambat</small>
                    <h3 class="fw-bold text-warning mb-0" id="stat-late">{{ $data['metrics']['late'] }}</h3>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle mt-2" style="font-size: 10px;">Telat Clock-In</span>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6">
            <div class="card border-0 shadow-sm metric-card bg-white h-100">
                <div class="card-body p-3 text-center">
                    <small class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 10px;">Izin / Cuti</small>
                    <h3 class="fw-bold text-info mb-0" id="stat-leave">{{ $data['metrics']['leave'] }}</h3>
                    <span class="badge bg-info-subtle text-info border border-info-subtle mt-2" style="font-size: 10px;">Disetujui</span>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6">
            <div class="card border-0 shadow-sm metric-card bg-white h-100">
                <div class="card-body p-3 text-center">
                    <small class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 10px;">Lembur / OT</small>
                    <h3 class="fw-bold text-danger mb-0" id="stat-overtime">{{ $data['metrics']['overtime'] }}</h3>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle mt-2" style="font-size: 10px;">Ada Jam Lembur</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body py-3 px-4">
            <form id="filterForm" onsubmit="event.preventDefault(); applyFilterAjax();" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1"><i class="fe fe-calendar me-1"></i>Tanggal Harian</label>
                    <input type="date" id="filter_date" name="date" class="form-control form-control-sm rounded-pill" value="{{ $data['filterDate'] }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1"><i class="fe fe-briefcase me-1"></i>Departemen</label>
                    <select id="filter_department" name="department" class="form-select form-select-sm rounded-pill" onchange="onDepartmentFilterChange()" {{ !$data['isAdminOrHcga'] ? 'disabled' : '' }}>
                        @if($data['isAdminOrHcga'])
                            <option value="ALL" {{ $data['filterDept'] === 'ALL' ? 'selected' : '' }}>-- Seluruh Departemen --</option>
                        @endif
                        @foreach($data['departments'] as $dept)
                            <option value="{{ $dept }}" {{ $data['filterDept'] === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted mb-1"><i class="fe fe-clock me-1"></i>Periode Payroll</label>
                    <input type="month" id="filter_periode" name="periode" class="form-control form-control-sm rounded-pill" value="{{ $data['filterPeriode'] }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted mb-1"><i class="fe fe-sliders me-1"></i>Aturan Cutoff</label>
                    <select id="filter_cutoff_rule_id" name="cutoff_rule_id" class="form-select form-select-sm rounded-pill">
                        @foreach($data['allCutoffRules'] as $cr)
                            <option value="{{ $cr->id }}" {{ (isset($data['cutoffRule']) && $data['cutoffRule']->id == $cr->id) ? 'selected' : '' }}>
                                {{ $cr->name }} (Tgl {{ $cr->start_day }}-{{ $cr->end_day }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted mb-1"><i class="fe fe-user me-1"></i>Pilih Karyawan</label>
                    <select id="filter_employee_nik" name="employee_nik" class="form-select form-select-sm rounded-pill">
                        <option value="ALL" {{ $data['filterEmployeeNik'] === 'ALL' ? 'selected' : '' }}>-- Seluruh Karyawan --</option>
                        @foreach($data['allAccessibleEmployees'] as $emp)
                            <option value="{{ $emp->Emp_ID }}" data-dept="{{ $emp->Department }}" {{ $data['filterEmployeeNik'] === $emp->Emp_ID ? 'selected' : '' }}>
                                {{ $emp->Name ?: $emp->username }} ({{ $emp->Emp_ID }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2 pt-2 border-top">
                    <div class="small text-muted" id="activeCutoffNotice">
                        <i class="fe fe-info text-primary me-1"></i> Rentang Cutoff Aktif: <strong class="text-dark" id="noticeRangeLabel">{{ $data['cutoffRange']['formatted_label'] ?? '-' }}</strong> (Siklus Periode {{ date('F Y', strtotime($data['filterPeriode'] . '-01')) }})
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" id="btnApplyFilter" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm">
                            <i class="fe fe-filter me-1"></i> Terapkan Filter
                        </button>
                        <button type="button" class="btn btn-light btn-sm rounded-pill px-3" title="Reset Filter" onclick="resetFilters()">
                            <i class="fe fe-rotate-ccw me-1"></i> Reset
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Alert / Banner Info Karyawan Terpilih (Hanya muncul jika spesifik memfilter karyawan) -->
    <div id="employeeInfoBanner" class="alert alert-primary border-0 rounded-4 shadow-sm align-items-center mb-4 {{ $data['isEmployeeDetailMode'] && !empty($data['selectedEmployee']) ? 'd-flex' : 'd-none' }}" style="background: linear-gradient(135deg, #e8f0fe 0%, #f1f3f4 100%); border-left: 5px solid #1a73e8 !important;">
        <div class="avatar avatar-md rounded-circle bg-primary text-white me-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; min-width: 48px;">
            <i class="fe fe-user fs-4"></i>
        </div>
        <div class="flex-grow-1">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h6 class="fw-bold text-dark mb-0 fs-6" id="bannerEmpName">
                    {{ $data['selectedEmployee'] ? ($data['selectedEmployee']->Name ?: $data['selectedEmployee']->username) : '' }}
                </h6>
                <span class="badge bg-primary text-white" id="bannerEmpNik">
                    NIK: {{ $data['selectedEmployee'] ? $data['selectedEmployee']->Emp_ID : '' }}
                </span>
                <span class="badge bg-white text-dark border" id="bannerEmpDept">
                    Dept: {{ $data['selectedEmployee'] ? ($data['selectedEmployee']->Department ?: '-') : '' }}
                </span>
                <span class="badge bg-white text-muted border" id="bannerEmpPos">
                    Jabatan: {{ $data['selectedEmployee'] ? ($data['selectedEmployee']->Position ?: '-') : '' }}
                </span>
            </div>
            <small class="text-muted d-block mt-1">
                <i class="fe fe-info me-1"></i> Menampilkan rincian absensi harian per tanggal selama bulan <strong id="bannerPeriodeText">{{ date('F Y', strtotime($data['filterPeriode'] . '-01')) }}</strong>.
            </small>
        </div>
        <div>
            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="clearEmployeeFilter()">
                <i class="fe fe-x me-1"></i> Lihat Semua Karyawan
            </button>
        </div>
    </div>

    <!-- Nav Tabs & Konten Tabel (Di-update parsial tanpa refresh halaman) -->
    <div class="card border-0 shadow-sm rounded-4 position-relative" id="tableContainerCard">
        <!-- Spinner Overlay saat AJAX filter berjalan -->
        <div id="tableLoadingOverlay" class="loading-overlay" style="display: none;">
            <div class="text-center">
                <div class="spinner-border text-primary" role="status"></div>
                <div class="small fw-semibold text-primary mt-2">Memperbarui Data...</div>
            </div>
        </div>

        <div class="card-header bg-white py-3 px-4 border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <ul class="nav nav-pills card-header-pills" id="reportTab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active rounded-pill px-4" id="daily-tab" data-bs-toggle="tab" data-bs-target="#daily-pane" type="button">
                        <i class="fe fe-activity me-1"></i> <span id="labelTabDaily">Monitoring Harian ({{ date('d M Y', strtotime($data['filterDate'])) }})</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link rounded-pill px-4 ms-2" id="monthly-tab" data-bs-toggle="tab" data-bs-target="#monthly-pane" type="button">
                        <i class="fe fe-file-text me-1"></i> <span id="labelTabMonthly">Rekap Akumulasi Bulanan ({{ date('F Y', strtotime($data['filterPeriode'] . '-01')) }})</span>
                    </button>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <div class="input-group input-group-sm w-auto">
                    <span class="input-group-text bg-light border-end-0 rounded-start-pill"><i class="fe fe-search text-muted"></i></span>
                    <input type="text" id="tableQuickSearch" class="form-control bg-light border-start-0 rounded-end-pill" placeholder="Cari NIK / Nama..." style="min-width: 200px;" onkeyup="onSearchInput()">
                </div>
                <select id="pageSizeSelect" class="form-select form-select-sm rounded-pill" style="width: auto;" onchange="changePageSize()">
                    <option value="10">10 / halaman</option>
                    <option value="25" selected>25 / halaman</option>
                    <option value="50">50 / halaman</option>
                    <option value="100">100 / halaman</option>
                </select>
            </div>
        </div>

        <div class="tab-content" id="reportTabContent">
            <!-- TAB 1: MONITORING HARIAN -->
            <div class="tab-pane fade show active p-0" id="daily-pane" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tableDailyAttendance">
                        <thead class="table-light">
                            <tr class="text-muted small text-uppercase" style="font-size: 11px;">
                                <th class="text-center" style="width: 50px;">No</th>
                                <th id="thCol2">{{ $data['isEmployeeDetailMode'] ? 'Tanggal' : 'Karyawan' }}</th>
                                <th id="thCol3">{{ $data['isEmployeeDetailMode'] ? 'Hari' : 'Departemen' }}</th>
                                <th class="text-center">Jam Masuk</th>
                                <th class="text-center">Jam Pulang</th>
                                <th class="text-center">Bukti Foto</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Keterlambatan</th>
                                <th class="text-center">Lembur (OT)</th>
                                <th>Keterangan / Lokasi</th>
                                @if(can_manual_correct_attendance())
                                    <th class="text-center" style="width: 110px;">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody id="dailyTbody">
                            @forelse($data['dailyList'] as $idx => $row)
                                @php
                                    $badgeCls = 'bg-secondary text-white';
                                    if ($row['status_kehadiran'] === 'HADIR') $badgeCls = 'bg-success text-white';
                                    elseif ($row['status_kehadiran'] === 'TERLAMBAT') $badgeCls = 'bg-warning text-dark';
                                    elseif (strpos($row['status_kehadiran'], 'IZIN') !== false) $badgeCls = 'bg-info text-white';
                                    elseif ($row['status_kehadiran'] === 'ALPHA') $badgeCls = 'bg-danger text-white';
                                @endphp
                                <tr class="data-row-daily">
                                    <td class="text-center text-muted small row-num">{{ $idx + 1 }}</td>
                                    @if($data['isEmployeeDetailMode'])
                                        <td>
                                            <div class="fw-bold text-dark emp-name">{{ date('d M Y', strtotime($row['date'])) }}</div>
                                            <small class="text-muted emp-nik"><i class="fe fe-calendar me-1"></i>{{ $row['date'] }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-primary border">{{ $row['day_name'] }}</span>
                                        </td>
                                    @else
                                        <td>
                                            <div class="fw-bold text-dark emp-name">{{ $row['name'] }}</div>
                                            <small class="text-muted emp-nik"><i class="fe fe-tag me-1"></i>NIK: {{ $row['nik'] }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-primary border">{{ $row['department'] }}</span>
                                            <div class="small text-muted mt-1">{{ $row['position'] }}</div>
                                        </td>
                                    @endif
                                    <td class="text-center">
                                        <span class="fw-semibold {{ $row['clock_in'] !== '--:--' ? 'text-success' : 'text-muted' }}">
                                            {{ $row['clock_in'] }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-semibold {{ $row['clock_out'] !== '--:--' ? 'text-danger' : 'text-muted' }}">
                                            {{ $row['clock_out'] }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1 align-items-center justify-content-center">
                                            @if(!empty($row['clock_in_photo']))
                                                <img src="{{ asset($row['clock_in_photo']) }}" class="selfie-thumb border border-success" alt="In" title="Foto Masuk (Klik untuk perbesar)" onclick="showAuditPhoto('{{ asset($row['clock_in_photo']) }}', 'Foto Masuk: {{ addslashes($row['name']) }} ({{ $row['clock_in'] }})')">
                                            @endif
                                            @if(!empty($row['clock_out_photo']))
                                                <img src="{{ asset($row['clock_out_photo']) }}" class="selfie-thumb border border-danger" alt="Out" title="Foto Pulang (Klik untuk perbesar)" onclick="showAuditPhoto('{{ asset($row['clock_out_photo']) }}', 'Foto Pulang: {{ addslashes($row['name']) }} ({{ $row['clock_out'] }})')">
                                            @endif
                                            @if(empty($row['clock_in_photo']) && empty($row['clock_out_photo']))
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-status {{ $badgeCls }}">{{ $row['status_kehadiran'] }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($row['late_minutes'] > 0)
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle fw-bold">
                                                {{ $row['late_minutes'] }} mnt
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($row['overtime_minutes'] > 0)
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-bold">
                                                {{ floor($row['overtime_minutes']/60) }}j {{ $row['overtime_minutes']%60 }}m
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted d-block">{{ $row['notes'] ?: '-' }}</small>
                                        @if(!empty($row['clock_in_lat']) && !empty($row['clock_in_lng']))
                                            <a href="https://maps.google.com/?q={{ $row['clock_in_lat'] }},{{ $row['clock_in_lng'] }}" target="_blank" class="small text-primary text-decoration-none" title="Lihat Titik Koordinat GPS">
                                                <i class="fe fe-map-pin me-1"></i>{{ round($row['clock_in_lat'], 4) }}, {{ round($row['clock_in_lng'], 4) }}
                                            </a>
                                        @endif
                                    </td>
                                    @if(can_manual_correct_attendance())
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1"
                                                onclick="openManualCorrectionModal('{{ $row['nik'] }}', '{{ addslashes($row['name']) }}', '{{ $row['date'] }}', '{{ $row['clock_in'] }}', '{{ $row['clock_out'] }}')">
                                                <i class="fe fe-edit-2 me-1"></i>Perbaiki
                                            </button>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr id="emptyDailyRow">
                                    <td colspan="{{ can_manual_correct_attendance() ? 11 : 10 }}" class="text-center py-4 text-muted">
                                        <i class="fe fe-inbox fs-3 d-block mb-1"></i> Tidak ada data presensi karyawan pada kriteria filter ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer Daily -->
                <div class="card-footer bg-white py-3 px-4 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <small class="text-muted" id="dailyPaginationInfo">Menampilkan data...</small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0 rounded-pill" id="dailyPaginationNav"></ul>
                    </nav>
                </div>
            </div>

            <!-- TAB 2: REKAP BULANAN -->
            <div class="tab-pane fade p-0" id="monthly-pane" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tableMonthlyAttendance">
                        <thead class="table-light">
                            <tr class="text-muted small text-uppercase" style="font-size: 11px;">
                                <th class="text-center" style="width: 50px;">No</th>
                                <th>Karyawan</th>
                                <th>Departemen</th>
                                <th class="text-center">Total Hadir</th>
                                <th class="text-center">Total Keterlambatan</th>
                                <th class="text-center">Akumulasi Menit Telat</th>
                                <th class="text-center">Akumulasi Jam Lembur</th>
                            </tr>
                        </thead>
                        <tbody id="monthlyTbody">
                            @forelse($data['monthlyList'] as $idx => $mRow)
                                <tr class="data-row-monthly">
                                    <td class="text-center text-muted small row-num-m">{{ $idx + 1 }}</td>
                                    <td>
                                        <div class="fw-bold text-dark emp-name-m">{{ $mRow['name'] }}</div>
                                        <small class="text-muted emp-nik-m"><i class="fe fe-tag me-1"></i>NIK: {{ $mRow['nik'] }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-primary border">{{ $mRow['department'] }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 fs-6 fw-bold">
                                            {{ $mRow['total_present'] }} Hari
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($mRow['total_late'] > 0)
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1 fs-6 fw-bold">
                                                {{ $mRow['total_late'] }} Kali
                                            </span>
                                        @else
                                            <span class="badge bg-light text-muted border px-2 py-1">0</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-semibold {{ $mRow['total_late_minutes'] > 0 ? 'text-warning' : 'text-muted' }}">
                                            {{ $mRow['total_late_minutes'] }} Menit
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($mRow['total_overtime_minutes'] > 0)
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 fs-6 fw-bold">
                                                {{ floor($mRow['total_overtime_minutes']/60) }} Jam {{ $mRow['total_overtime_minutes']%60 }} Mnt
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr id="emptyMonthlyRow">
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="fe fe-inbox fs-3 d-block mb-1"></i> Tidak ada rekap data pada periode yang dipilih.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer Monthly -->
                <div class="card-footer bg-white py-3 px-4 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <small class="text-muted" id="monthlyPaginationInfo">Menampilkan data...</small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0 rounded-pill" id="monthlyPaginationNav"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL POPUP AUDIT FOTO SELFIE PRESENSI -->
    <div class="modal fade" id="auditPhotoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <div class="att-modal-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="att-modal-icon bg-primary-subtle text-primary">
                            <i class="fe fe-camera"></i>
                        </div>
                        <div>
                            <h6 class="modal-title fw-bold text-dark mb-0" id="auditPhotoTitle">Bukti Foto Presensi</h6>
                            <small class="text-muted">Dokumentasi selfie saat clock-in / clock-out</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-3 text-center">
                    <img id="auditPhotoImg" src="" class="img-fluid rounded-3 shadow border" alt="Selfie Audit" style="max-height: 480px; width: 100%; object-fit: contain;">
                </div>
            </div>
        </div>
    </div>

    @if(can_manual_correct_attendance())
    <!-- MODAL 1: PERBAIKAN ABSEN MANUAL (SINGLE) -->
    <div class="modal fade" id="manualCorrectionModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <div class="att-modal-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="att-modal-icon bg-primary-subtle text-primary">
                            <i class="fe fe-edit-3"></i>
                        </div>
                        <div>
                            <h6 class="modal-title fw-bold text-dark mb-0">Perbaikan Absen Manual Karyawan</h6>
                            <small class="text-muted">Ubah jam masuk/pulang melalui alur approval atau pembaruan langsung</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form id="manualCorrectionForm" onsubmit="submitManualCorrection(event)" enctype="multipart/form-data">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-7">
                                <label class="form-label small fw-semibold">Karyawan (NIK) <span class="text-danger">*</span></label>
                                <select id="mc_nik" name="nik" class="form-select rounded-3" required onchange="inspectManualContext()">
                                    <option value="">-- Pilih Karyawan --</option>
                                    @foreach($data['allAccessibleEmployees'] as $emp)
                                        <option value="{{ $emp->Emp_ID }}">{{ $emp->Name ?: $emp->username }} ({{ $emp->Emp_ID }} - {{ $emp->Department ?: '-' }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small fw-semibold">Tanggal Presensi <span class="text-danger">*</span></label>
                                <input type="date" id="mc_date" name="attendance_date" class="form-control rounded-3" required onchange="inspectManualContext()">
                            </div>
                        </div>

                        <div id="mc_context_box" class="mt-3 d-none">
                            <div id="mc_existing_log_info" class="alert alert-light border small mb-2"></div>
                            <div id="mc_pending_alert" class="alert alert-danger small mb-2 d-none"></div>
                            <div id="mc_self_alert" class="alert alert-warning small mb-2 d-none"></div>

                            <div id="mc_leave_alert" class="alert alert-info border-info small mb-2 d-none">
                                <div id="mc_leave_text" class="fw-semibold mb-1"></div>
                                <div id="mc_leave_cancel_wrap" class="form-check mt-1 d-none">
                                    <input class="form-check-input" type="checkbox" id="mc_cancel_approved_leave" name="cancel_approved_leave" value="1">
                                    <label class="form-check-label fw-bold text-dark" for="mc_cancel_approved_leave">
                                        Batalkan Cuti 1 Hari ini (CANCELLED) &amp; Kembalikan Kuota Cuti Karyawan
                                    </label>
                                </div>
                            </div>

                            <div id="mc_payroll_lock_alert" class="alert alert-warning border-warning small mb-2 d-none">
                                <div class="fw-bold text-dark mb-1"><i class="fe fe-lock me-1"></i>Periode Payroll Sudah Dikunci (LOCKED)</div>
                                <div id="mc_payroll_lock_text" class="mb-2"></div>
                                <label class="form-label small fw-semibold mb-1">Pilih Mekanisme Post-Cutoff <span class="text-danger">*</span></label>
                                <select id="mc_post_cutoff_action" name="post_cutoff_action" class="form-select form-select-sm mb-2">
                                    <option value="NONE">-- Pilih Aksi Post-Cutoff --</option>
                                    <option value="RAPEL_NEXT_PERIOD">Rapel di Periode Payroll Berikutnya (PENDING_RAPEL)</option>
                                    <option value="FINANCE_DIRECT">Teruskan ke Finance Langsung (FORWARDED_TO_FINANCE)</option>
                                </select>
                                <input type="text" id="mc_post_cutoff_notes" name="post_cutoff_notes" class="form-control form-control-sm" placeholder="Catatan tambahan untuk Payroll / Finance (opsional)...">
                            </div>
                        </div>

                        <hr class="my-3">

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Jenis Perbaikan</label>
                                <select id="mc_correction_type" name="correction_type" class="form-select rounded-3" onchange="toggleMcTimeInputs()">
                                    <option value="BOTH">Jam Masuk &amp; Pulang</option>
                                    <option value="IN">Jam Masuk Saja</option>
                                    <option value="OUT">Jam Pulang Saja</option>
                                </select>
                            </div>
                            <div class="col-md-4" id="mc_in_group">
                                <label class="form-label small fw-semibold">Jam Masuk Baru</label>
                                <input type="time" id="mc_clock_in" name="corrected_clock_in" class="form-control rounded-3">
                            </div>
                            <div class="col-md-4" id="mc_out_group">
                                <label class="form-label small fw-semibold">Jam Pulang Baru</label>
                                <input type="time" id="mc_clock_out" name="corrected_clock_out" class="form-control rounded-3">
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Mode Pembaruan <span class="text-danger">*</span></label>
                                <select id="mc_execution_mode" name="execution_mode" class="form-select rounded-3">
                                    <option value="APPROVAL_REQUIRED">Melalui Proses Approval (Atasan Karyawan)</option>
                                    <option value="DIRECT_UPDATE">Update Langsung ke Log Presensi</option>
                                </select>
                                <small id="mc_mode_hint" class="text-muted d-block mt-1"></small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Lampiran Bukti (Opsional / Sesuai Setting)</label>
                                <input type="file" id="mc_evidence_file" name="evidence_file" class="form-control rounded-3" accept=".jpg,.jpeg,.png,.pdf">
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label small fw-semibold">Alasan Perbaikan <span class="text-danger">*</span></label>
                            <textarea id="mc_reason" name="reason" rows="2" class="form-control rounded-3" placeholder="Jelaskan alasan perbaikan absen secara jelas untuk audit trail..."></textarea>
                        </div>

                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="mc_clear_gps_anomaly" name="clear_gps_anomaly" value="1">
                            <label class="form-check-label small text-muted" for="mc_clear_gps_anomaly">
                                Bersihkan flag <em>Suspect Fake GPS</em> (set ke CLEAN) jika ada
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer border-0 bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" id="btnSubmitManualCorrection" class="btn btn-primary rounded-pill px-4">
                            <i class="fe fe-save me-1"></i> Simpan Perbaikan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL 2: BULK UPLOAD EXCEL & BULK PROSES REQUEST -->
    <div class="modal fade" id="bulkCorrectionModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <div class="att-modal-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="att-modal-icon bg-info-subtle text-info">
                            <i class="fe fe-upload-cloud"></i>
                        </div>
                        <div>
                            <h6 class="modal-title fw-bold text-dark mb-0">Bulk Upload &amp; Proses Massal Koreksi Absen</h6>
                            <small class="text-muted">Unggah template Excel multi-karyawan atau setujui request SUBMITTED sekaligus</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-4">
                    <ul class="nav nav-pills mb-3" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active rounded-pill px-3" data-bs-toggle="tab" data-bs-target="#bulk-upload-pane" type="button">
                                <i class="fe fe-file-plus me-1"></i> Upload Excel Massal
                            </button>
                        </li>
                        <li class="nav-item ms-2">
                            <button class="nav-link rounded-pill px-3" data-bs-toggle="tab" data-bs-target="#bulk-pending-pane" type="button" onclick="loadPendingCorrections()">
                                <i class="fe fe-check-square me-1"></i> Proses Request SUBMITTED
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="bulk-upload-pane">
                            <div class="alert alert-light border small d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <strong>Format Terstandar:</strong> Gunakan template Excel resmi agar kolom NIK, Tanggal, Jam, Mode, Pembatalan Cuti 1 Hari, dan Post-Cutoff terbaca akurat.
                                </div>
                                <a href="{{ route('attendance.manual.template') }}" class="btn btn-sm btn-outline-success rounded-pill px-3">
                                    <i class="fe fe-download me-1"></i> Download Template (.xlsx)
                                </a>
                            </div>

                            <div class="row g-2 align-items-end mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Pilih File Excel (.xlsx / .csv)</label>
                                    <input type="file" id="bulkUploadFile" class="form-control form-control-sm" accept=".xlsx,.xls,.csv">
                                </div>
                                <div class="col-md-6 d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="runBulkUpload('preview')">
                                        <i class="fe fe-eye me-1"></i> Validasi / Preview
                                    </button>
                                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" onclick="runBulkUpload('execute')">
                                        <i class="fe fe-play me-1"></i> Proses &amp; Simpan Sekarang
                                    </button>
                                </div>
                            </div>

                            <div id="bulkUploadSummary" class="small fw-semibold mb-2 d-none"></div>
                            <div class="table-responsive" style="max-height: 320px;">
                                <table class="table table-sm table-bordered align-middle small">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Baris</th>
                                            <th>NIK</th>
                                            <th>Nama</th>
                                            <th>Tanggal</th>
                                            <th>Jam In / Out</th>
                                            <th>Mode</th>
                                            <th>Status</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody id="bulkUploadPreviewBody">
                                        <tr><td colspan="8" class="text-center text-muted py-3">Unggah file dan klik Validasi / Preview untuk melihat hasil per baris.</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="bulk-pending-pane">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted">Daftar pengajuan koreksi absensi yang masih berstatus <strong>SUBMITTED</strong>.</small>
                                <button type="button" class="btn btn-sm btn-success rounded-pill px-3" onclick="executeBulkApprove()">
                                    <i class="fe fe-check-circle me-1"></i> Setujui &amp; Terapkan Terpilih
                                </button>
                            </div>
                            <div class="table-responsive" style="max-height: 350px;">
                                <table class="table table-sm table-hover align-middle small">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 35px;"><input type="checkbox" id="chkAllPending" onclick="toggleAllPending(this)"></th>
                                            <th>No Koreksi</th>
                                            <th>Karyawan</th>
                                            <th>Tanggal</th>
                                            <th>Jam Masuk / Pulang</th>
                                            <th>Alasan</th>
                                            <th>Penginput</th>
                                        </tr>
                                    </thead>
                                    <tbody id="bulkPendingBody">
                                        <tr><td colspan="7" class="text-center text-muted py-3">Memuat data...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 3: SETTING PERBAIKAN ABSEN MANUAL -->
    <div class="modal fade" id="manualCorrectionSettingsModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <div class="att-modal-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="att-modal-icon bg-warning-subtle text-warning">
                            <i class="fe fe-settings"></i>
                        </div>
                        <div>
                            <h6 class="modal-title fw-bold text-dark mb-0">Pengaturan Perbaikan Absen Manual</h6>
                            <small class="text-muted">Atur hak akses username, mode default, dan batasan guard koreksi</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form id="manualSettingsForm" onsubmit="saveManualSettings(event)">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Daftar Username yang Diizinkan (pisahkan koma)</label>
                            <textarea id="set_allowed_usernames" name="allowed_usernames" rows="2" class="form-control rounded-3" required></textarea>
                            <small class="text-muted">Default mengacu pada <code>force_approve_allowed_usernames()</code>.</small>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Mode Default</label>
                                <select id="set_default_mode" name="default_mode" class="form-select rounded-3">
                                    <option value="APPROVAL_REQUIRED">Melalui Approval</option>
                                    <option value="DIRECT_UPDATE">Update Langsung</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Batas Hari Mundur (0 = Bebas)</label>
                                <input type="number" id="set_max_backdate_days" name="max_backdate_days" class="form-control rounded-3" min="0" placeholder="Contoh: 30">
                            </div>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="set_allow_mode_selection" name="allow_mode_selection" value="1">
                            <label class="form-check-label small" for="set_allow_mode_selection">Izinkan user memilih mode (Approval / Langsung) saat input</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="set_require_reason" name="require_reason" value="1">
                            <label class="form-check-label small" for="set_require_reason">Wajib mengisi alasan perbaikan</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="set_require_evidence" name="require_evidence" value="1">
                            <label class="form-check-label small" for="set_require_evidence">Wajib melampirkan file bukti</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="set_allow_self_direct_update" name="allow_self_direct_update" value="1">
                            <label class="form-check-label small text-danger fw-semibold" for="set_allow_self_direct_update">Izinkan Update Langsung untuk NIK diri sendiri (Tidak disarankan)</label>
                        </div>
                    </div>
                    <div class="modal-footer border-0 bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-dark rounded-pill px-4">Simpan Pengaturan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL 4: RIWAYAT AUDIT KOREKSI MANUAL -->
    <div class="modal fade" id="manualCorrectionAuditModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <div class="att-modal-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="att-modal-icon bg-secondary-subtle text-secondary">
                            <i class="fe fe-shield"></i>
                        </div>
                        <div>
                            <h6 class="modal-title fw-bold text-dark mb-0">Riwayat Audit Log Perbaikan Absen Manual</h6>
                            <small class="text-muted">Jejak perubahan data presensi, pembatalan cuti, dan status post-cutoff</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-3">
                    <div class="table-responsive" style="max-height: 450px;">
                        <table class="table table-sm table-hover align-middle small">
                            <thead class="table-light">
                                <tr>
                                    <th>Waktu</th>
                                    <th>Aksi</th>
                                    <th>Aktor</th>
                                    <th>NIK / Karyawan</th>
                                    <th>Tanggal</th>
                                    <th>Cuti / Post-Cutoff</th>
                                    <th>Alasan</th>
                                </tr>
                            </thead>
                            <tbody id="manualAuditLogBody">
                                <tr><td colspan="7" class="text-center text-muted py-3">Memuat riwayat audit...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

<script>
// State Pagination & Filter
let currentPageDaily = 1;
let currentPageMonthly = 1;
let pageSize = 25;
let isEmployeeDetailMode = {{ $data['isEmployeeDetailMode'] ? 'true' : 'false' }};

document.addEventListener('DOMContentLoaded', function() {
    renderPagination('daily');
    renderPagination('monthly');

    // Switch tab trigger pagination
    document.querySelectorAll('#reportTab button').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(e) {
            const target = e.target.getAttribute('data-bs-target');
            if (target === '#daily-pane') renderPagination('daily');
            else if (target === '#monthly-pane') renderPagination('monthly');
        });
    });
});

// Otomatis sesuaikan opsi karyawan berdasarkan departemen yang dipilih
function onDepartmentFilterChange() {
    const selectedDept = document.getElementById('filter_department').value;
    const empSelect = document.getElementById('filter_employee_nik');
    const options = empSelect.querySelectorAll('option');

    options.forEach(opt => {
        if (opt.value === 'ALL') return;
        const empDept = opt.getAttribute('data-dept');
        if (selectedDept === 'ALL' || empDept === selectedDept) {
            opt.style.display = '';
        } else {
            opt.style.display = 'none';
        }
    });

    // Jika karyawan terpilih tidak cocok dengan departemen yang dipilih, reset ke ALL
    const currentSelected = empSelect.options[empSelect.selectedIndex];
    if (currentSelected && currentSelected.value !== 'ALL' && selectedDept !== 'ALL') {
        if (currentSelected.getAttribute('data-dept') !== selectedDept) {
            empSelect.value = 'ALL';
        }
    }
}

function clearEmployeeFilter() {
    document.getElementById('filter_employee_nik').value = 'ALL';
    resetEmployeeInfoBanner();
    applyFilterAjax();
}

function resetEmployeeInfoBanner() {
    const banner = document.getElementById('employeeInfoBanner');
    banner.classList.remove('d-flex');
    banner.classList.add('d-none');
    document.getElementById('bannerEmpName').innerText = '';
    document.getElementById('bannerEmpNik').innerText = '';
    document.getElementById('bannerEmpDept').innerText = '';
    document.getElementById('bannerEmpPos').innerText = '';
    document.getElementById('bannerPeriodeText').innerText = '';
}

// TERAPKAN FILTER PARSIAL (AJAX ONLY CARD BAWAH & METRICS)
function applyFilterAjax() {
    const date = document.getElementById('filter_date').value;
    const dept = document.getElementById('filter_department').value;
    const periode = document.getElementById('filter_periode').value;
    const empNik = document.getElementById('filter_employee_nik').value;
    const cutoffRuleId = document.getElementById('filter_cutoff_rule_id') ? document.getElementById('filter_cutoff_rule_id').value : '';

    const overlay = document.getElementById('tableLoadingOverlay');
    overlay.style.display = 'flex';
    document.getElementById('btnApplyFilter').disabled = true;

    const params = new URLSearchParams({
        date: date,
        department: dept,
        periode: periode,
        employee_nik: empNik,
        cutoff_rule_id: cutoffRuleId
    });

    fetch("{{ route('attendance.data.ajax') }}?" + params.toString(), {
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(res => {
        overlay.style.display = 'none';
        document.getElementById('btnApplyFilter').disabled = false;

        if (res.status !== 'success') {
            alert('Gagal memuat data: ' + (res.message || 'Terjadi kesalahan sistem.'));
            return;
        }

        const data = res.data;
        isEmployeeDetailMode = data.isEmployeeDetailMode;

        // Update Notice Cutoff Range Label
        if (data.cutoffRange && document.getElementById('noticeRangeLabel')) {
            document.getElementById('noticeRangeLabel').innerText = data.cutoffRange.formatted_label;
        }

        // 1. Update Banner Info Karyawan
        const banner = document.getElementById('employeeInfoBanner');
        if (data.isEmployeeDetailMode && data.selectedEmployee) {
            banner.classList.remove('d-none');
            banner.classList.add('d-flex');
            const emp = data.selectedEmployee;
            document.getElementById('bannerEmpName').innerText = emp.Name || emp.username;
            document.getElementById('bannerEmpNik').innerText = 'NIK: ' + emp.Emp_ID;
            document.getElementById('bannerEmpDept').innerText = 'Dept: ' + (emp.Department || '-');
            document.getElementById('bannerEmpPos').innerText = 'Jabatan: ' + (emp.Position || '-');
            
            const rangeText = data.cutoffRange ? (' (Rentang: ' + data.cutoffRange.formatted_label + ')') : '';
            const mDt = new Date(data.filterPeriode + '-01T00:00:00');
            const mOptions = { month: 'long', year: 'numeric' };
            document.getElementById('bannerPeriodeText').innerText = mDt.toLocaleDateString('id-ID', mOptions) + rangeText;
        } else {
            resetEmployeeInfoBanner();
        }

        // 2. Update Metrik KPI di Card Atas
        const m = data.metrics;
        document.getElementById('stat-total').innerText = m.total_employees;
        document.getElementById('stat-present').innerText = m.present;
        const pct = m.total_employees > 0 ? Math.round((m.present / m.total_employees) * 100) : 0;
        document.getElementById('stat-pct').innerText = pct + '% Kehadiran';
        document.getElementById('stat-ontime').innerText = m.on_time;
        document.getElementById('stat-late').innerText = m.late;
        document.getElementById('stat-leave').innerText = m.leave;
        document.getElementById('stat-overtime').innerText = m.overtime;

        // 3. Update Label Header Tab & Table Header
        const th2 = document.getElementById('thCol2');
        const th3 = document.getElementById('thCol3');

        if (data.isEmployeeDetailMode && data.selectedEmployee) {
            const empName = data.selectedEmployee.Name || data.selectedEmployee.username;
            document.getElementById('labelTabDaily').innerText = 'Rincian Presensi ' + empName + ' (' + data.filterPeriode + ')';
            if (th2) th2.innerText = 'Tanggal';
            if (th3) th3.innerText = 'Hari';
        } else {
            if (data.filterDate) {
                const dt = new Date(data.filterDate + 'T00:00:00');
                const dOptions = { day: '2-digit', month: 'short', year: 'numeric' };
                document.getElementById('labelTabDaily').innerText = 'Monitoring Harian (' + dt.toLocaleDateString('id-ID', dOptions) + ')';
            }
            if (th2) th2.innerText = 'Karyawan';
            if (th3) th3.innerText = 'Departemen';
        }

        if (data.filterPeriode) {
            const mDt = new Date(data.filterPeriode + '-01T00:00:00');
            const mOptions = { month: 'long', year: 'numeric' };
            document.getElementById('labelTabMonthly').innerText = 'Rekap Akumulasi Bulanan (' + mDt.toLocaleDateString('id-ID', mOptions) + ')';
        }

        // 4. Re-render Tabel Harian & Bulanan
        renderDailyTableBody(data.dailyList, data.isEmployeeDetailMode);
        renderMonthlyTableBody(data.monthlyList);

        // 5. Reset Pagination ke Page 1
        currentPageDaily = 1;
        currentPageMonthly = 1;
        renderPagination('daily');
        renderPagination('monthly');
    })
    .catch(err => {
        overlay.style.display = 'none';
        document.getElementById('btnApplyFilter').disabled = false;
        alert('Terjadi kesalahan koneksi saat menerapkan filter.');
    });
}

function renderDailyTableBody(list, isDetailMode) {
    const canManualCorrect = {{ can_manual_correct_attendance() ? 'true' : 'false' }};
    const tbody = document.getElementById('dailyTbody');
    if (!list || list.length === 0) {
        tbody.innerHTML = `<tr><td colspan="${canManualCorrect ? 11 : 10}" class="text-center py-4 text-muted"><i class="fe fe-inbox fs-3 d-block mb-1"></i> Tidak ada data presensi karyawan pada kriteria filter ini.</td></tr>`;
        return;
    }

    let html = '';
    list.forEach((row, idx) => {
        let badgeCls = 'bg-secondary text-white';
        if (row.status_kehadiran === 'HADIR') badgeCls = 'bg-success text-white';
        else if (row.status_kehadiran === 'TERLAMBAT') badgeCls = 'bg-warning text-dark';
        else if (row.status_kehadiran.indexOf('IZIN') > -1) badgeCls = 'bg-info text-white';
        else if (row.status_kehadiran === 'ALPHA') badgeCls = 'bg-danger text-white';
        else if (row.status_kehadiran.indexOf('LIBUR') > -1) badgeCls = 'bg-light text-muted border';

        let photosHtml = '<span class="text-muted small">-</span>';
        let pItems = [];
        if (row.clock_in_photo) {
            pItems.push(`<img src="/${row.clock_in_photo}" class="selfie-thumb border border-success" alt="In" title="Foto Masuk" onclick="showAuditPhoto('/${row.clock_in_photo}', 'Foto Masuk: ${escapeHtml(row.name)} (${row.clock_in})')">`);
        }
        if (row.clock_out_photo) {
            pItems.push(`<img src="/${row.clock_out_photo}" class="selfie-thumb border border-danger" alt="Out" title="Foto Pulang" onclick="showAuditPhoto('/${row.clock_out_photo}', 'Foto Pulang: ${escapeHtml(row.name)} (${row.clock_out})')">`);
        }
        if (pItems.length > 0) {
            photosHtml = `<div class="d-inline-flex gap-1 align-items-center justify-content-center">${pItems.join('')}</div>`;
        }

        let lateBadge = row.late_minutes > 0 
            ? `<span class="badge bg-warning-subtle text-warning border border-warning-subtle fw-bold">${row.late_minutes} mnt</span>`
            : `<span class="text-muted">-</span>`;

        let otBadge = row.overtime_minutes > 0
            ? `<span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-bold">${Math.floor(row.overtime_minutes/60)}j ${row.overtime_minutes%60}m</span>`
            : `<span class="text-muted">-</span>`;

        let gpsFlagBadge = (row.gps_anomaly_flag === 'SUSPECTED_FAKE_GPS')
            ? `<span class="badge bg-danger text-white d-inline-block mt-1" title="${escapeHtml(row.gps_anomaly_reason || 'Anomali GPS terdeteksi')}"><i class="fe fe-alert-triangle me-1"></i>Suspect Fake GPS</span>`
            : '';

        let mapLink = (row.clock_in_lat && row.clock_in_lng)
            ? `<a href="https://maps.google.com/?q=${row.clock_in_lat},${row.clock_in_lng}" target="_blank" class="small text-primary text-decoration-none d-block mt-1" title="Lihat Titik Koordinat GPS"><i class="fe fe-map-pin me-1"></i>${Number(row.clock_in_lat).toFixed(4)}, ${Number(row.clock_in_lng).toFixed(4)}</a>${gpsFlagBadge}`
            : gpsFlagBadge;

        let col2Html = '';
        let col3Html = '';

        if (isDetailMode) {
            const dtObj = new Date(row.date + 'T00:00:00');
            const dStr = dtObj.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
            col2Html = `
                <div class="fw-bold text-dark emp-name">${dStr}</div>
                <small class="text-muted emp-nik"><i class="fe fe-calendar me-1"></i>${row.date}</small>
            `;
            col3Html = `<span class="badge bg-light text-primary border">${row.day_name}</span>`;
        } else {
            col2Html = `
                <div class="fw-bold text-dark emp-name">${escapeHtml(row.name)}</div>
                <small class="text-muted emp-nik"><i class="fe fe-tag me-1"></i>NIK: ${row.nik}</small>
            `;
            col3Html = `
                <span class="badge bg-light text-primary border">${row.department}</span>
                <div class="small text-muted mt-1">${row.position}</div>
            `;
        }

        html += `
            <tr class="data-row-daily">
                <td class="text-center text-muted small row-num">${idx + 1}</td>
                <td>${col2Html}</td>
                <td>${col3Html}</td>
                <td class="text-center">
                    <span class="fw-semibold ${row.clock_in !== '--:--' ? 'text-success' : 'text-muted'}">${row.clock_in}</span>
                </td>
                <td class="text-center">
                    <span class="fw-semibold ${row.clock_out !== '--:--' ? 'text-danger' : 'text-muted'}">${row.clock_out}</span>
                </td>
                <td class="text-center">${photosHtml}</td>
                <td class="text-center"><span class="badge-status ${badgeCls}">${row.status_kehadiran}</span></td>
                <td class="text-center">${lateBadge}</td>
                <td class="text-center">${otBadge}</td>
                <td>
                    <small class="text-muted d-block">${escapeHtml(row.notes || '-')}</small>
                    ${mapLink}
                </td>
                ${canManualCorrect ? `
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1"
                        onclick="openManualCorrectionModal('${escapeHtml(row.nik)}', '${escapeHtml(row.name)}', '${row.date}', '${row.clock_in}', '${row.clock_out}')">
                        <i class="fe fe-edit-2 me-1"></i>Perbaiki
                    </button>
                </td>` : ''}
            </tr>
        `;
    });
    tbody.innerHTML = html;
}

function renderMonthlyTableBody(list) {
    const tbody = document.getElementById('monthlyTbody');
    if (!list || list.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-muted"><i class="fe fe-inbox fs-3 d-block mb-1"></i> Tidak ada rekap data pada periode yang dipilih.</td></tr>`;
        return;
    }

    let html = '';
    list.forEach((mRow, idx) => {
        let lateBadge = mRow.total_late > 0
            ? `<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1 fs-6 fw-bold">${mRow.total_late} Kali</span>`
            : `<span class="badge bg-light text-muted border px-2 py-1">0</span>`;

        let otBadge = mRow.total_overtime_minutes > 0
            ? `<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 fs-6 fw-bold">${Math.floor(mRow.total_overtime_minutes/60)} Jam ${mRow.total_overtime_minutes%60} Mnt</span>`
            : `<span class="text-muted">-</span>`;

        html += `
            <tr class="data-row-monthly">
                <td class="text-center text-muted small row-num-m">${idx + 1}</td>
                <td>
                    <div class="fw-bold text-dark emp-name-m">${escapeHtml(mRow.name)}</div>
                    <small class="text-muted emp-nik-m"><i class="fe fe-tag me-1"></i>NIK: ${mRow.nik}</small>
                </td>
                <td><span class="badge bg-light text-primary border">${mRow.department}</span></td>
                <td class="text-center">
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 fs-6 fw-bold">${mRow.total_present} Hari</span>
                </td>
                <td class="text-center">${lateBadge}</td>
                <td class="text-center">
                    <span class="fw-semibold ${mRow.total_late_minutes > 0 ? 'text-warning' : 'text-muted'}">${mRow.total_late_minutes} Menit</span>
                </td>
                <td class="text-center">${otBadge}</td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
}

function resetFilters() {
    document.getElementById('filter_date').value = "{{ date('Y-m-d') }}";
    document.getElementById('filter_department').value = "ALL";
    document.getElementById('filter_periode').value = "{{ date('Y-m') }}";
    document.getElementById('filter_employee_nik').value = "ALL";
    resetEmployeeInfoBanner();
    onDepartmentFilterChange();
    applyFilterAjax();
}

function escapeHtml(text) {
    if (!text) return '';
    return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

function changePageSize() {
    pageSize = parseInt(document.getElementById('pageSizeSelect').value, 10);
    currentPageDaily = 1;
    currentPageMonthly = 1;
    renderPagination('daily');
    renderPagination('monthly');
}

function onSearchInput() {
    currentPageDaily = 1;
    currentPageMonthly = 1;
    renderPagination('daily');
    renderPagination('monthly');
}

function renderPagination(type) {
    const searchVal = document.getElementById('tableQuickSearch').value.toLowerCase().trim();
    const rows = type === 'daily' 
        ? Array.from(document.querySelectorAll('#dailyTbody tr.data-row-daily'))
        : Array.from(document.querySelectorAll('#monthlyTbody tr.data-row-monthly'));

    // Filter baris sesuai search
    const filteredRows = rows.filter(row => {
        const text = row.innerText.toLowerCase();
        return text.indexOf(searchVal) > -1;
    });

    const totalRows = filteredRows.length;
    const totalPages = Math.ceil(totalRows / pageSize) || 1;
    let curPage = type === 'daily' ? currentPageDaily : currentPageMonthly;

    if (curPage > totalPages) curPage = totalPages;
    if (type === 'daily') currentPageDaily = curPage;
    else currentPageMonthly = curPage;

    // Sembunyikan semua baris
    rows.forEach(r => r.style.display = 'none');

    // Tampilkan baris pada page yang aktif
    const startIdx = (curPage - 1) * pageSize;
    const endIdx = Math.min(startIdx + pageSize, totalRows);

    for (let i = startIdx; i < endIdx; i++) {
        filteredRows[i].style.display = '';
    }

    // Update info teks
    const infoEl = document.getElementById(type === 'daily' ? 'dailyPaginationInfo' : 'monthlyPaginationInfo');
    if (infoEl) {
        if (totalRows === 0) {
            infoEl.innerText = 'Tidak ada baris data yang cocok.';
        } else {
            infoEl.innerHTML = `Menampilkan <strong>${startIdx + 1}</strong> s/d <strong>${endIdx}</strong> dari total <strong>${totalRows}</strong> karyawan`;
        }
    }

    // Render Tombol Pagination
    const navEl = document.getElementById(type === 'daily' ? 'dailyPaginationNav' : 'monthlyPaginationNav');
    if (!navEl) return;

    let navHtml = '';
    navHtml += `<li class="page-item ${curPage <= 1 ? 'disabled' : ''}">
        <a class="page-link" href="javascript:void(0)" onclick="goToPage('${type}', ${curPage - 1})"><i class="fe fe-chevron-left"></i></a>
    </li>`;

    // Windowing 5 halaman
    let startPage = Math.max(1, curPage - 2);
    let endPage = Math.min(totalPages, startPage + 4);
    if (endPage - startPage < 4) {
        startPage = Math.max(1, endPage - 4);
    }

    for (let p = startPage; p <= endPage; p++) {
        navHtml += `<li class="page-item ${p === curPage ? 'active' : ''}">
            <a class="page-link" href="javascript:void(0)" onclick="goToPage('${type}', ${p})">${p}</a>
        </li>`;
    }

    navHtml += `<li class="page-item ${curPage >= totalPages ? 'disabled' : ''}">
        <a class="page-link" href="javascript:void(0)" onclick="goToPage('${type}', ${curPage + 1})"><i class="fe fe-chevron-right"></i></a>
    </li>`;

    navEl.innerHTML = navHtml;
}

function goToPage(type, page) {
    if (type === 'daily') currentPageDaily = page;
    else currentPageMonthly = page;
    renderPagination(type);
}

// Modal Audit Foto Presensi
function showAuditPhoto(url, title) {
    document.getElementById('auditPhotoTitle').innerText = title || 'Bukti Foto Presensi';
    document.getElementById('auditPhotoImg').src = url;
    const modal = new bootstrap.Modal(document.getElementById('auditPhotoModal'));
    modal.show();
}

// Unduh Dokumen Excel Resmi (.xlsx) dengan Hyperlink Foto & Google Maps
function downloadExcelReport() {
    const isMonthly = document.getElementById('monthly-pane').classList.contains('active');
    const type = isMonthly ? 'monthly' : 'daily';

    const date = document.getElementById('filter_date').value;
    const dept = document.getElementById('filter_department').value;
    const periode = document.getElementById('filter_periode').value;
    const empNik = document.getElementById('filter_employee_nik').value;
    const cutoffRuleId = document.getElementById('filter_cutoff_rule_id') ? document.getElementById('filter_cutoff_rule_id').value : '';

    const query = new URLSearchParams({
        type: type,
        date: date,
        department: dept,
        periode: periode,
        employee_nik: empNik,
        cutoff_rule_id: cutoffRuleId
    });

    window.location.href = "{{ route('attendance.data.export') }}?" + query.toString();
}

@if(can_manual_correct_attendance())
const CSRF_TOKEN_MANUAL = "{{ csrf_token() }}";

function openManualCorrectionModal(nik = '', name = '', date = '', clockIn = '', clockOut = '') {
    document.getElementById('manualCorrectionForm').reset();
    document.getElementById('mc_context_box').classList.add('d-none');

    const nikSel = document.getElementById('mc_nik');
    const dateEl = document.getElementById('mc_date');

    if (nik) {
        nikSel.value = nik;
    } else if (document.getElementById('filter_employee_nik').value !== 'ALL') {
        nikSel.value = document.getElementById('filter_employee_nik').value;
    }

    dateEl.value = date || document.getElementById('filter_date').value || "{{ date('Y-m-d') }}";

    if (clockIn && clockIn !== '--:--') {
        document.getElementById('mc_clock_in').value = clockIn.substring(0, 5);
    }
    if (clockOut && clockOut !== '--:--') {
        document.getElementById('mc_clock_out').value = clockOut.substring(0, 5);
    }

    toggleMcTimeInputs();
    const modal = new bootstrap.Modal(document.getElementById('manualCorrectionModal'));
    modal.show();

    if (nikSel.value && dateEl.value) {
        inspectManualContext();
    }
}

function toggleMcTimeInputs() {
    const type = document.getElementById('mc_correction_type').value;
    document.getElementById('mc_in_group').style.display = (type === 'OUT') ? 'none' : '';
    document.getElementById('mc_out_group').style.display = (type === 'IN') ? 'none' : '';
}

function inspectManualContext() {
    const nik = document.getElementById('mc_nik').value;
    const date = document.getElementById('mc_date').value;
    if (!nik || !date) return;

    const box = document.getElementById('mc_context_box');
    const logInfo = document.getElementById('mc_existing_log_info');
    const pendingAlert = document.getElementById('mc_pending_alert');
    const selfAlert = document.getElementById('mc_self_alert');
    const leaveAlert = document.getElementById('mc_leave_alert');
    const leaveCancelWrap = document.getElementById('mc_leave_cancel_wrap');
    const payrollAlert = document.getElementById('mc_payroll_lock_alert');
    const btnSubmit = document.getElementById('btnSubmitManualCorrection');
    const modeSel = document.getElementById('mc_execution_mode');
    const modeHint = document.getElementById('mc_mode_hint');

    box.classList.remove('d-none');
    logInfo.innerHTML = '<span class="text-muted"><i class="fe fe-loader me-1"></i>Memeriksa konteks presensi...</span>';
    pendingAlert.classList.add('d-none');
    selfAlert.classList.add('d-none');
    leaveAlert.classList.add('d-none');
    leaveCancelWrap.classList.add('d-none');
    payrollAlert.classList.add('d-none');
    document.getElementById('mc_cancel_approved_leave').checked = false;
    btnSubmit.disabled = false;

    const params = new URLSearchParams({ nik: nik, date: date });
    fetch("{{ route('attendance.manual.context') }}?" + params.toString(), {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(res => {
        if (res.status !== 'success') {
            logInfo.innerHTML = `<span class="text-danger">${escapeHtml(res.message || 'Gagal memeriksa konteks.')}</span>`;
            return;
        }
        const d = res.data;

        if (d.existing_log) {
            logInfo.innerHTML = `<strong>Log Eksisting (${d.date}):</strong> Masuk <code>${d.existing_log.clock_in || '--:--'}</code> | Pulang <code>${d.existing_log.clock_out || '--:--'}</code> | Status: <strong>${d.existing_log.clock_in_status || '-'}</strong> | Telat: ${d.existing_log.late_minutes} mnt`;
        } else {
            logInfo.innerHTML = `<strong>Log Eksisting (${d.date}):</strong> <span class="text-muted">Belum ada catatan absensi (ALPHA / Libur).</span>`;
        }

        if (d.has_pending_correction) {
            pendingAlert.classList.remove('d-none');
            pendingAlert.innerHTML = `<i class="fe fe-alert-circle me-1"></i><strong>Diblokir:</strong> Masih ada pengajuan koreksi berstatus <strong>SUBMITTED</strong> (${escapeHtml(d.pending_correction.correction_no)}) di tanggal ini.`;
            btnSubmit.disabled = true;
        }

        if (d.approved_leave) {
            leaveAlert.classList.remove('d-none');
            if (d.approved_leave.is_single_day) {
                document.getElementById('mc_leave_text').innerHTML = `<i class="fe fe-info me-1"></i>Bentrok dengan Cuti/Izin <strong>APPROVED</strong> (${escapeHtml(d.approved_leave.request_no)} - ${escapeHtml(d.approved_leave.leave_type)}, 1 Hari).`;
                leaveCancelWrap.classList.remove('d-none');
            } else {
                document.getElementById('mc_leave_text').innerHTML = `<i class="fe fe-alert-triangle me-1"></i>Bentrok Cuti Multi-Hari (${escapeHtml(d.approved_leave.request_no)}: ${d.approved_leave.start_date} s/d ${d.approved_leave.end_date}). Pembatalan otomatis hanya mendukung cuti 1 hari; silakan revisi cuti terlebih dahulu.`;
                btnSubmit.disabled = true;
            }
        }

        if (d.is_payroll_locked) {
            payrollAlert.classList.remove('d-none');
            document.getElementById('mc_payroll_lock_text').innerText = `Periode ${d.payroll_lock_detail.periode} (${d.payroll_lock_detail.cutoff_from} s/d ${d.payroll_lock_detail.cutoff_to}) sudah LOCKED. Perubahan historis akan dicatat sebagai Post-Cutoff Adjustment.`;
        }

        const settings = d.settings;
        modeSel.value = settings.default_mode || 'APPROVAL_REQUIRED';
        modeSel.disabled = !settings.allow_mode_selection;
        modeHint.innerText = !settings.allow_mode_selection ? 'Mode dikunci oleh pengaturan sistem.' : '';

        if (d.must_use_approval) {
            selfAlert.classList.remove('d-none');
            selfAlert.innerHTML = '<i class="fe fe-shield me-1"></i><strong>Guard Self-Approval:</strong> Perbaikan untuk NIK Anda sendiri wajib melalui Proses Approval.';
            modeSel.value = 'APPROVAL_REQUIRED';
            modeSel.disabled = true;
        }
    });
}

function submitManualCorrection(e) {
    e.preventDefault();
    const form = document.getElementById('manualCorrectionForm');
    const modeSel = document.getElementById('mc_execution_mode');
    const wasDisabled = modeSel.disabled;
    modeSel.disabled = false;

    const formData = new FormData(form);
    if (wasDisabled) modeSel.disabled = true;

    const btn = document.getElementById('btnSubmitManualCorrection');
    btn.disabled = true;

    fetch("{{ route('attendance.manual.store') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': CSRF_TOKEN_MANUAL,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        btn.disabled = false;
        if (res.status === 'success') {
            bootstrap.Modal.getInstance(document.getElementById('manualCorrectionModal')).hide();
            alert(res.message);
            applyFilterAjax();
        } else {
            alert('Gagal: ' + (res.message || 'Periksa kembali inputan Anda.'));
        }
    })
    .catch(() => {
        btn.disabled = false;
        alert('Terjadi kesalahan jaringan saat menyimpan perbaikan absen.');
    });
}

function openManualSettingsModal() {
    fetch("{{ route('attendance.manual.settings.get') }}", { headers: { 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            const s = res.data;
            document.getElementById('set_allowed_usernames').value = (s.allowed_usernames || []).join(', ');
            document.getElementById('set_default_mode').value = s.default_mode || 'APPROVAL_REQUIRED';
            document.getElementById('set_max_backdate_days').value = s.max_backdate_days || 0;
            document.getElementById('set_allow_mode_selection').checked = !!s.allow_mode_selection;
            document.getElementById('set_require_reason').checked = !!s.require_reason;
            document.getElementById('set_require_evidence').checked = !!s.require_evidence;
            document.getElementById('set_allow_self_direct_update').checked = !!s.allow_self_direct_update;
            new bootstrap.Modal(document.getElementById('manualCorrectionSettingsModal')).show();
        }
    });
}

function saveManualSettings(e) {
    e.preventDefault();
    const payload = {
        allowed_usernames: document.getElementById('set_allowed_usernames').value,
        default_mode: document.getElementById('set_default_mode').value,
        max_backdate_days: document.getElementById('set_max_backdate_days').value || 0,
        allow_mode_selection: document.getElementById('set_allow_mode_selection').checked ? 1 : 0,
        require_reason: document.getElementById('set_require_reason').checked ? 1 : 0,
        require_evidence: document.getElementById('set_require_evidence').checked ? 1 : 0,
        allow_self_direct_update: document.getElementById('set_allow_self_direct_update').checked ? 1 : 0,
    };

    fetch("{{ route('attendance.manual.settings.save') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN_MANUAL,
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            bootstrap.Modal.getInstance(document.getElementById('manualCorrectionSettingsModal')).hide();
            alert(res.message);
        } else {
            alert('Gagal: ' + (res.message || 'Tidak dapat menyimpan pengaturan.'));
        }
    });
}

function openBulkCorrectionModal() {
    new bootstrap.Modal(document.getElementById('bulkCorrectionModal')).show();
}

function runBulkUpload(actionMode) {
    const fileInput = document.getElementById('bulkUploadFile');
    if (!fileInput.files || !fileInput.files[0]) {
        alert('Pilih file Excel (.xlsx / .csv) terlebih dahulu.');
        return;
    }

    const fd = new FormData();
    fd.append('file', fileInput.files[0]);
    fd.append('action_mode', actionMode);

    fetch("{{ route('attendance.manual.bulk_upload') }}", {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF_TOKEN_MANUAL, 'Accept': 'application/json' },
        body: fd
    })
    .then(r => r.json())
    .then(res => {
        if (res.status !== 'success') {
            alert('Gagal: ' + (res.message || 'Format file tidak valid.'));
            return;
        }
        const d = res.data;
        const summaryEl = document.getElementById('bulkUploadSummary');
        summaryEl.classList.remove('d-none');
        summaryEl.innerHTML = `Total Baris: ${d.total_rows} | Valid: <span class="text-success">${d.valid_count}</span> | Error: <span class="text-danger">${d.error_count}</span> | Dieksekusi: <span class="text-primary">${d.executed_count}</span>`;

        const tbody = document.getElementById('bulkUploadPreviewBody');
        tbody.innerHTML = d.rows.map(r => {
            const cls = r.status === 'ERROR' ? 'table-danger' : (r.status === 'EXECUTED' ? 'table-success' : '');
            return `<tr class="${cls}">
                <td>${r.row}</td>
                <td>${escapeHtml(r.nik)}</td>
                <td>${escapeHtml(r.name)}</td>
                <td>${escapeHtml(r.date)}</td>
                <td>${escapeHtml(r.clock_in || '-')} / ${escapeHtml(r.clock_out || '-')}</td>
                <td>${escapeHtml(r.mode)}</td>
                <td><strong>${escapeHtml(r.status)}</strong></td>
                <td>${escapeHtml(r.message)}</td>
            </tr>`;
        }).join('');

        if (actionMode === 'execute' && d.executed_count > 0) {
            applyFilterAjax();
        }
    });
}

function loadPendingCorrections() {
    const tbody = document.getElementById('bulkPendingBody');
    tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3">Memuat data...</td></tr>';

    fetch("{{ route('attendance.manual.pending') }}", { headers: { 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(res => {
        if (res.status !== 'success' || !res.data.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3">Tidak ada pengajuan koreksi berstatus SUBMITTED.</td></tr>';
            return;
        }
        tbody.innerHTML = res.data.map(r => `
            <tr>
                <td><input type="checkbox" class="chk-pending-item" value="${r.id}"></td>
                <td><strong>${escapeHtml(r.correction_no)}</strong></td>
                <td>${escapeHtml(r.employee_name || r.nik)} <br><small class="text-muted">${escapeHtml(r.nik)}</small></td>
                <td>${escapeHtml(r.attendance_date)}</td>
                <td>${escapeHtml(r.corrected_clock_in || '--:--')} / ${escapeHtml(r.corrected_clock_out || '--:--')}</td>
                <td>${escapeHtml(r.reason)}</td>
                <td>${escapeHtml(r.created_by || '-')}</td>
            </tr>
        `).join('');
    });
}

function toggleAllPending(master) {
    document.querySelectorAll('.chk-pending-item').forEach(chk => {
        chk.checked = master.checked;
    });
}

function executeBulkApprove() {
    const ids = Array.from(document.querySelectorAll('.chk-pending-item:checked')).map(c => parseInt(c.value, 10));
    if (!ids.length) {
        alert('Pilih minimal 1 pengajuan koreksi.');
        return;
    }

    fetch("{{ route('attendance.manual.bulk_approve') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN_MANUAL,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ correction_ids: ids })
    })
    .then(r => r.json())
    .then(res => {
        alert(res.message || 'Selesai memproses.');
        loadPendingCorrections();
        applyFilterAjax();
    });
}

function openManualAuditModal() {
    const tbody = document.getElementById('manualAuditLogBody');
    tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3">Memuat riwayat audit...</td></tr>';
    new bootstrap.Modal(document.getElementById('manualCorrectionAuditModal')).show();

    fetch("{{ route('attendance.manual.audit') }}", { headers: { 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(res => {
        if (res.status !== 'success' || !res.data.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3">Belum ada riwayat audit perbaikan absen manual.</td></tr>';
            return;
        }
        tbody.innerHTML = res.data.map(r => `
            <tr>
                <td>${escapeHtml(r.created_at)}</td>
                <td><span class="badge bg-dark text-white">${escapeHtml(r.action)}</span></td>
                <td>${escapeHtml(r.actor_username)}</td>
                <td>${escapeHtml(r.target_name || r.target_nik || '-')} <br><small class="text-muted">${escapeHtml(r.target_nik || '')}</small></td>
                <td>${escapeHtml(r.scan_date || '-')}</td>
                <td>${escapeHtml(r.leave_action || '-')} ${r.post_cutoff_action && r.post_cutoff_action !== 'NONE' ? `<span class="badge bg-warning text-dark">${escapeHtml(r.post_cutoff_action)} (${escapeHtml(r.post_cutoff_status || '')})</span>` : ''}</td>
                <td>${escapeHtml(r.reason || '-')}</td>
            </tr>
        `).join('');
    });
}
@endif
</script>
@endsection
