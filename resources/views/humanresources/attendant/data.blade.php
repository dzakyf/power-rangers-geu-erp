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
            <a href="{{ route('attendance.cutoff.index') }}" class="btn btn-sm rounded-pill shadow-sm" style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
                <i class="fe fe-sliders me-1"></i> Kelola Master Cutoff
            </a>
            <a href="{{ route('attendance.portal') }}" class="btn btn-sm rounded-pill shadow-sm" style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
                <i class="fe fe-monitor me-1"></i> Portal Absen Saya
            </a>
            <button type="button" class="btn btn-success btn-sm rounded-pill shadow-sm px-3" onclick="downloadExcelReport()">
                <i class="fe fe-download me-1"></i> Export Excel (.xlsx)
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
                                </tr>
                            @empty
                                <tr id="emptyDailyRow">
                                    <td colspan="10" class="text-center py-4 text-muted">
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
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-0 pb-1">
                    <h6 class="modal-title fw-bold" id="auditPhotoTitle">Bukti Foto Presensi</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3 text-center">
                    <img id="auditPhotoImg" src="" class="img-fluid rounded-3 shadow border" alt="Selfie Audit" style="max-height: 480px; width: 100%; object-fit: contain;">
                </div>
            </div>
        </div>
    </div>

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
    const tbody = document.getElementById('dailyTbody');
    if (!list || list.length === 0) {
        tbody.innerHTML = `<tr><td colspan="10" class="text-center py-4 text-muted"><i class="fe fe-inbox fs-3 d-block mb-1"></i> Tidak ada data presensi karyawan pada kriteria filter ini.</td></tr>`;
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

        let mapLink = (row.clock_in_lat && row.clock_in_lng)
            ? `<a href="https://maps.google.com/?q=${row.clock_in_lat},${row.clock_in_lng}" target="_blank" class="small text-primary text-decoration-none d-block mt-1" title="Lihat Titik Koordinat GPS"><i class="fe fe-map-pin me-1"></i>${Number(row.clock_in_lat).toFixed(4)}, ${Number(row.clock_in_lng).toFixed(4)}</a>`
            : '';

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
</script>
@endsection
