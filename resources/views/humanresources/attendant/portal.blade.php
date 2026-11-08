@extends('layout.mainlayout-hrd')

@push('head')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
@endpush

@section('content-wrapper')
    <!-- Main Content Header (Adminor V3 Responsive Hero Header) -->
    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
        <div class="mb-2 mb-md-0">
            <h4 class="content-title text-white fw-bold mb-1"
                style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
                <i class="fe fe-monitor me-2"></i>Portal Absensi &amp; Pengajuan Karyawan
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="/dashboard1"
                            style="color: rgba(255, 255, 255, 0.8) !important;">Human Resources</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page"
                        style="color: #ffffff !important; font-weight: 600;">Employee Portal</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex align-items-center flex-wrap gap-2">
            @if (isset($cutoffRange))
                <a href="{{ route('attendance.cutoff.index') }}"
                    class="badge rounded-pill px-3 py-2 text-white shadow-sm text-decoration-none"
                    style="background: rgba(255, 255, 255, 0.25); border: 1px solid rgba(255, 255, 255, 0.4); font-size: 11px;"
                    title="Klik untuk mengelola Aturan Cutoff">
                    <i class="fe fe-calendar me-1"></i> Cutoff Periode:
                    <strong>{{ $cutoffRange['formatted_label'] }}</strong>
                    ({{ date('F Y', strtotime($activePeriodeYm . '-01')) }}) <i class="fe fe-external-link ms-1"></i>
                </a>
            @endif
            <a href="{{ route('attendance.cutoff.index') }}" class="btn btn-sm rounded-pill shadow-sm"
                style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
                <i class="fe fe-sliders me-1"></i> Atur Cutoff
            </a>
            <a href="{{ url('/attendant-data') }}" class="btn btn-sm rounded-pill shadow-sm"
                style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
                <i class="fe fe-users me-1"></i> Rekap Semua Karyawan
            </a>
            <a href="{{ route('attendance.mobile.index') }}" target="_blank"
                class="btn btn-warning btn-sm rounded-pill shadow-sm px-3 fw-semibold">
                <i class="fe fe-smartphone me-1"></i> Tampilan Mobile
            </a>
        </div>
    </div>
    <!-- End Main Content Header -->

    <div class="row g-3">
        <!-- Kolom Kiri: Presensi Langsung dari Web Desktop -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fe fe-check-circle text-primary me-2"></i>Presensi Desktop
                        Hari Ini</h6>
                    <span class="badge bg-light text-primary border">{{ date('d M Y') }}</span>
                </div>
                <div class="card-body p-4 text-center">
                    <h2 class="fw-bold text-dark mb-1" id="desktop-clock">--:--:--</h2>
                    <small class="text-muted d-block mb-3">Jadwal Shift: {{ $schedule['default_start'] }} -
                        {{ $schedule['default_end'] }}</small>

                    <div class="row g-2 mb-4 text-center">
                        <div class="col-6">
                            <div class="p-2 border rounded-3 bg-light">
                                <small class="text-muted d-block">Jam Masuk</small>
                                <span class="fw-bold text-success fs-5">{{ $todayEntry->Start_Time ?? '--:--' }}</span>
                                @if ($todayEntry && $todayEntry->Clock_In_Status)
                                    <span class="badge bg-success d-block mt-1">{{ $todayEntry->Clock_In_Status }}</span>
                                @endif
                                @if ($todayEntry && !empty($todayEntry->Clock_In_Photo))
                                    <div class="mt-2 pt-1 border-top">
                                        <a href="javascript:void(0)"
                                            onclick="previewDesktopSelfie('{{ asset($todayEntry->Clock_In_Photo) }}', 'Foto Selfie Clock-In')"
                                            class="btn btn-sm btn-outline-primary py-0 px-2 rounded-pill"
                                            style="font-size: 10px;">
                                            <i class="fe fe-camera me-1"></i>Lihat Foto
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 border rounded-3 bg-light">
                                <small class="text-muted d-block">Jam Pulang</small>
                                <span class="fw-bold text-danger fs-5">{{ $todayEntry->End_Time ?? '--:--' }}</span>
                                @if ($todayEntry && $todayEntry->End_Time && $todayEntry->Clock_Out_Status)
                                    <span
                                        class="badge bg-secondary d-block mt-1">{{ $todayEntry->Clock_Out_Status }}</span>
                                @endif
                                @if ($todayEntry && !empty($todayEntry->Clock_Out_Photo))
                                    <div class="mt-2 pt-1 border-top">
                                        <a href="javascript:void(0)"
                                            onclick="previewDesktopSelfie('{{ asset($todayEntry->Clock_Out_Photo) }}', 'Foto Selfie Clock-Out')"
                                            class="btn btn-sm btn-outline-danger py-0 px-2 rounded-pill"
                                            style="font-size: 10px;">
                                            <i class="fe fe-camera me-1"></i>Lihat Foto
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Clock-In / Out Desktop -->
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-success btn-lg rounded-pill fw-bold shadow-sm"
                            onclick="doDesktopAttendance('clock-in')"
                            {{ $todayEntry && $todayEntry->Start_Time ? 'disabled' : '' }}>
                            <i class="fe fe-log-in me-2"></i> Clock In Sekarang
                        </button>
                        <button type="button" class="btn btn-danger btn-lg rounded-pill fw-bold shadow-sm"
                            onclick="doDesktopAttendance('clock-out')"
                            {{ !$todayEntry || !$todayEntry->Start_Time || $todayEntry->End_Time ? 'disabled' : '' }}>
                            <i class="fe fe-log-out me-2"></i> Clock Out Sekarang
                        </button>
                    </div>
                    <div id="desktop-geo-status" class="small text-muted mt-2">Mendeteksi koordinat browser...</div>

                    <!-- KHUSUS AKUN ADMIN: TOMBOL DEV RESET ATTENDANCE -->
                    @if (strtolower(Auth::user()->username) === 'admin')
                        <div class="border-top pt-3 mt-3 text-start">
                            <span class="badge bg-dark mb-2"><i class="fe fe-tool me-1"></i> Admin Dev Tool (Reset
                                Absensi)</span>
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" class="btn btn-outline-warning btn-sm rounded-pill"
                                    onclick="resetAttendanceDev('in')">
                                    <i class="fe fe-rotate-ccw me-1"></i> Reset Clock-In
                                </button>
                                <button type="button" class="btn btn-outline-warning btn-sm rounded-pill"
                                    onclick="resetAttendanceDev('out')">
                                    <i class="fe fe-rotate-ccw me-1"></i> Reset Clock-Out
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill"
                                    onclick="resetAttendanceDev('both')">
                                    <i class="fe fe-trash-2 me-1"></i> Reset Keduanya
                                </button>
                            </div>
                            <small class="text-muted d-block mt-1" style="font-size: 10px;">*Tombol ini hanya terlihat &
                                dapat digunakan oleh akun admin untuk kemudahan testing alur presensi.</small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Mini Widget Ringkasan Siklus Cutoff Payroll Berjalan -->
            @if (isset($cutoffRange) && isset($cutoffStats))
                <div class="card border-0 shadow-sm rounded-4 mb-3">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fe fe-pie-chart text-success me-2"></i>Akumulasi
                            Siklus Payroll Anda</h6>
                        <span
                            class="badge bg-success-subtle text-success border border-success-subtle">{{ date('F Y', strtotime($activePeriodeYm . '-01')) }}</span>
                    </div>
                    <div class="card-body p-3">
                        <small class="text-muted d-block mb-2" style="font-size: 11px;">
                            Rentang Cutoff: <strong>{{ $cutoffRange['formatted_label'] }}</strong>
                            ({{ $cutoffRule->name ?? 'Cutoff Standar' }})
                        </small>
                        <div class="row g-2 text-center">
                            <div class="col-4">
                                <div class="p-2 border rounded-3 bg-light">
                                    <small class="text-muted d-block" style="font-size: 10px;">Total Hadir</small>
                                    <span class="fw-bold text-success fs-6">{{ (int) ($cutoffStats->total_present ?? 0) }}
                                        Hari</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 border rounded-3 bg-light">
                                    <small class="text-muted d-block" style="font-size: 10px;">Terlambat</small>
                                    <span
                                        class="fw-bold {{ ($cutoffStats->total_late ?? 0) > 0 ? 'text-warning' : 'text-muted' }} fs-6">
                                        {{ (int) ($cutoffStats->total_late ?? 0) }}x
                                    </span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 border rounded-3 bg-light">
                                    <small class="text-muted d-block" style="font-size: 10px;">Jam Lembur</small>
                                    <span class="fw-bold text-danger fs-6">
                                        {{ round(($cutoffStats->total_overtime_minutes ?? 0) / 60, 1) }} Jam
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Kolom Kanan: Pengajuan Terpisah (Request Izin/Cuti & Koreksi Absensi) -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 border-0">
                    <ul class="nav nav-pills card-header-pills" id="requestTab" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active rounded-pill px-3" id="leave-tab" data-bs-toggle="tab"
                                data-bs-target="#leave-pane" type="button">
                                <i class="fe fe-calendar me-1"></i> Form Pengajuan Izin & Cuti
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link rounded-pill px-3 ms-2" id="corr-tab" data-bs-toggle="tab"
                                data-bs-target="#corr-pane" type="button">
                                <i class="fe fe-clock me-1"></i> Koreksi Jam Absensi
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link rounded-pill px-3 ms-2" id="overtime-tab" data-bs-toggle="tab"
                                data-bs-target="#overtime-pane" type="button">
                                <i class="fe fe-zap me-1"></i> Pengajuan Lembur (Overtime)
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link rounded-pill px-3 ms-2" id="changeshift-tab" data-bs-toggle="tab"
                                data-bs-target="#changeshift-pane" type="button">
                                <i class="fe fe-repeat me-1"></i> Tukar / Ganti Shift
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-4">
                    <div class="tab-content" id="requestTabContent">
                        <!-- TAB 1: FORM IZIN & CUTI (DILENGKAPI JAM MASUK/PULANG & ATTACHMENT MAKS 5MB) -->
                        <div class="tab-pane fade show active" id="leave-pane" role="tabpanel">
                            <!-- Banner Sisa Saldo Cuti Karyawan -->
                            @if (isset($leaveBalance))
                                <div class="alert alert-primary py-2 px-3 rounded-3 mb-3 d-flex justify-content-between align-items-center">
                                    <div class="small">
                                        <i class="fe fe-info me-1"></i> Sisa Saldo Cuti Tahunan Anda (Tahun {{ $leaveBalance->year ?? date('Y') }}):
                                        <strong class="fs-6 text-primary">{{ $leaveBalance->remaining_total ?? 0 }} Hari</strong>
                                        @if (($leaveBalance->carry_forward_quota ?? 0) > 0)
                                            <span class="text-muted ms-1">(Termasuk akumulasi tahun lalu: {{ $leaveBalance->carry_forward_quota - $leaveBalance->used_carry_forward }} hari)</span>
                                        @endif
                                    </div>
                                    <span class="badge bg-primary text-white">Sisa Kuota: {{ $leaveBalance->remaining_total ?? 0 }} Hari</span>
                                </div>
                            @endif

                            <form id="desktop-leave-form" enctype="multipart/form-data">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Kategori Cuti / Izin <span
                                                class="text-danger">*</span></label>
                                        <select name="leave_type" id="desk_leave_type" class="form-select rounded-3"
                                            required onchange="handleLeaveCategoryChange(this)">
                                            @foreach ($kategoriList as $kat)
                                                <option value="{{ $kat->kat_izin_code ?? $kat->kat_izin_nama }}"
                                                    data-deduct="{{ $kat->is_deduct_annual_leave }}"
                                                    data-attachment="{{ $kat->is_need_attachment }}"
                                                    data-max="{{ $kat->jatah_hari }}">
                                                    {{ $kat->kat_izin_nama }} {{ $kat->jatah_hari > 0 ? '(Maks ' . $kat->jatah_hari . ' hari)' : '' }} {{ $kat->is_deduct_annual_leave === 'YES' ? '[Potong Cuti]' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted d-block mt-1" id="leave_cat_hint" style="font-size: 11px;"></small>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-semibold">Tanggal Mulai <span
                                                class="text-danger">*</span></label>
                                        <input type="date" name="start_date" id="desk_start_date"
                                            class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-semibold">Tanggal Akhir <span
                                                class="text-danger">*</span></label>
                                        <input type="date" name="end_date" id="desk_end_date"
                                            class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Jam Clock In Parsial (Opsional)</label>
                                        <input type="time" name="clock_in_time" id="desk_clock_in_time"
                                            class="form-control rounded-3">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Jam Clock Out Parsial
                                            (Opsional)</label>
                                        <input type="time" name="clock_out_time" id="desk_clock_out_time"
                                            class="form-control rounded-3">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">Dokumen Pendukung / Surat Sakit (Maks
                                            5MB)</label>
                                        <input type="file" name="attachment_file" id="desk_attachment_file"
                                            class="form-control rounded-3" accept=".pdf,.jpg,.jpeg,.png">
                                        <small class="text-muted" style="font-size: 11px;">Format diizinkan: PDF, JPG,
                                            PNG. Gambar akan otomatis dikompresi agar hemat bandwidth.</small>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">Alasan / Keterangan <span
                                                class="text-danger">*</span></label>
                                        <textarea name="reason" id="desk_reason" class="form-control rounded-3" rows="2"
                                            placeholder="Jelaskan alasan izin / cuti..." required></textarea>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold"
                                            id="btn-submit-desk-leave">
                                            <i class="fe fe-send me-1"></i> Kirim Pengajuan Izin/Cuti
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- TAB 2: FORM KOREKSI JAM ABSENSI -->
                        <div class="tab-pane fade" id="corr-pane" role="tabpanel">
                            <form id="desktop-corr-form" enctype="multipart/form-data">
                                <div class="row g-3">
                                    <!-- Pilihan Bagian Jam yang Dikoreksi -->
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold d-block">Pilihan Jam yang Ingin
                                            Dikoreksi <span class="text-danger">*</span></label>
                                        <div class="d-flex flex-wrap gap-2">
                                            <label
                                                class="border border-primary bg-white shadow-sm rounded-3 px-3 py-2 d-inline-flex align-items-center gap-2 m-0 corr-type-card"
                                                for="corr_type_both" style="cursor: pointer; user-select: none;">
                                                <input class="form-check-input m-0" type="radio" name="correction_type"
                                                    id="corr_type_both" value="BOTH" checked
                                                    onchange="toggleCorrectionInputs()">
                                                <span class="small fw-semibold text-dark">
                                                    <i class="fe fe-repeat text-primary ms-4 me-1"></i> Jam Masuk & Pulang
                                                </span>
                                            </label>
                                            <label
                                                class="border bg-light rounded-3 px-3 py-2 d-inline-flex align-items-center gap-2 m-0 corr-type-card"
                                                for="corr_type_in" style="cursor: pointer; user-select: none;">
                                                <input class="form-check-input m-0" type="radio" name="correction_type"
                                                    id="corr_type_in" value="IN" onchange="toggleCorrectionInputs()">
                                                <span class="small fw-semibold text-dark">
                                                    <i class="fe fe-arrow-right-circle text-success ms-4 me-1"></i> Jam
                                                    Masuk Saja <span class="badge bg-success-subtle text-success ms-1">Lupa
                                                        Clock In</span>
                                                </span>
                                            </label>
                                            <label
                                                class="border bg-light rounded-3 px-3 py-2 d-inline-flex align-items-center gap-2 m-0 corr-type-card"
                                                for="corr_type_out" style="cursor: pointer; user-select: none;">
                                                <input class="form-check-input m-0" type="radio" name="correction_type"
                                                    id="corr_type_out" value="OUT"
                                                    onchange="toggleCorrectionInputs()">
                                                <span class="small fw-semibold text-dark">
                                                    <i class="fe fe-arrow-left-circle text-danger ms-4 me-1"></i> Jam
                                                    Pulang Saja <span class="badge bg-danger-subtle text-danger ms-1">Lupa
                                                        Clock Out</span>
                                                </span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold">Tanggal Absensi <span
                                                class="text-danger">*</span></label>
                                        <input type="date" name="attendance_date" id="corr_date"
                                            class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-md-4" id="corr_in_wrapper">
                                        <label class="form-label small fw-semibold" id="corr_in_label">Koreksi Jam Masuk
                                            <span class="text-danger" id="corr_in_req">*</span></label>
                                        <input type="time" name="corrected_clock_in" id="corr_in"
                                            class="form-control rounded-3" value="08:00" required>
                                        <small class="text-muted d-block mt-1" id="corr_in_help"
                                            style="font-size: 11px;">Isikan jam kehadiran masuk Anda.</small>
                                    </div>
                                    <div class="col-md-4" id="corr_out_wrapper">
                                        <label class="form-label small fw-semibold" id="corr_out_label">Koreksi Jam Pulang
                                            <span class="text-danger" id="corr_out_req">*</span></label>
                                        <input type="time" name="corrected_clock_out" id="corr_out"
                                            class="form-control rounded-3" value="17:00" required>
                                        <small class="text-muted d-block mt-1" id="corr_out_help"
                                            style="font-size: 11px;">Isikan jam kepulangan Anda.</small>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">Bukti Pendukung (Maks 5MB)</label>
                                        <input type="file" name="evidence_attachment" id="corr_evidence"
                                            class="form-control rounded-3" accept=".pdf,.jpg,.jpeg,.png">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">Alasan Koreksi (Lupa tap / Dinas) <span
                                                class="text-danger">*</span></label>
                                        <textarea name="reason" id="corr_reason" class="form-control rounded-3" rows="2"
                                            placeholder="Alasan mengapa lupa tap presensi..." required></textarea>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit"
                                            class="btn btn-warning text-dark rounded-pill px-4 fw-semibold"
                                            id="btn-submit-desk-corr">
                                            <i class="fe fe-check me-1"></i> Kirim Pengajuan Koreksi
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- TAB 3: FORM PENGAJUAN LEMBUR (OVERTIME) -->
                        <div class="tab-pane fade" id="overtime-pane" role="tabpanel">
                            <form id="desktop-overtime-form" enctype="multipart/form-data">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label small fw-semibold">Tanggal Lembur <span
                                                class="text-danger">*</span></label>
                                        <input type="date" name="overtime_date" id="ot_date"
                                            class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-semibold">Tipe Hari Lembur <span
                                                class="text-danger">*</span></label>
                                        <select name="day_type" id="ot_day_type" class="form-select rounded-3"
                                            required onchange="calculateOvertimePreview()">
                                            <option value="WORKDAY" selected>Hari Kerja Reguler</option>
                                            <option value="HOLIDAY">Hari Libur Mingguan / Libur Nasional</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-semibold">Jenis Waktu Lembur <span
                                                class="text-danger">*</span></label>
                                        <select name="overtime_type" id="ot_type" class="form-select rounded-3"
                                            required onchange="calculateOvertimePreview()">
                                            <option value="AFTER_SHIFT" selected>Lembur Setelah Shift (Pasca-Shift)</option>
                                            <option value="BEFORE_SHIFT">Lembur Sebelum Shift Masuk (Pra-Shift)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-semibold">Pilihan Kompensasi <span
                                                class="text-danger">*</span></label>
                                        <select name="compensation_type" id="ot_compensation"
                                            class="form-select rounded-3" required onchange="calculateOvertimePreview()">
                                            <option value="PAID" selected>Uang Lembur (Matriks Tarif / Depnaker)</option>
                                            <option value="TIME_OFF">Tukar Cuti / Hari Libur Pengganti (Day Off)</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Lokasi Kantor / Tempat Kerja <span
                                                class="text-danger">*</span></label>
                                        <select name="location_id" id="ot_location_id" class="form-select rounded-3"
                                            onchange="updateLocationZipcode()">
                                            <option value="">-- Pilih Lokasi Kerja --</option>
                                            @foreach ($locations as $loc)
                                                <option value="{{ $loc->id }}"
                                                    data-zip="{{ $loc->zipcode ?? '' }}">
                                                    {{ $loc->name }} ({{ $loc->code }})
                                                    {{ !empty($loc->zipcode) ? '- Kodepos: ' . $loc->zipcode : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Kode Pos Wilayah Kerja</label>
                                        <input type="text" name="zipcode" id="ot_zipcode"
                                            class="form-control rounded-3"
                                            placeholder="Contoh: 14240 (terhubung master kodepos)"
                                            oninput="calculateOvertimePreview()">
                                        <small class="text-muted d-block mt-1" style="font-size: 11px;">Otomatis memetakan
                                            Wilayah 1 (Jabodetabek) atau Wilayah 2 (Daerah).</small>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Jam Mulai Lembur <span
                                                class="text-danger">*</span></label>
                                        <input type="time" name="start_time" id="ot_start_time"
                                            class="form-control rounded-3" value="17:30" required
                                            onchange="calculateOvertimePreview()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Jam Selesai Lembur <span
                                                class="text-danger">*</span></label>
                                        <input type="time" name="end_time" id="ot_end_time"
                                            class="form-control rounded-3" value="21:30" required
                                            onchange="calculateOvertimePreview()">
                                    </div>

                                    <!-- REALTIME ESTIMATION CARD -->
                                    <div class="col-12">
                                        <div
                                            class="p-3 rounded-4 border bg-light d-flex flex-wrap justify-content-between align-items-center gap-2">
                                            <div>
                                                <small class="text-muted d-block fw-semibold" style="font-size: 11px;">ESTIMASI PERHITUNGAN MATRIKS TARIF / LEMBUR</small>
                                                <h5 class="fw-bold mb-0 text-primary" id="ot_preview_amount">Rp 0</h5>
                                                <small class="text-muted d-block" id="ot_preview_breakdown">Silakan sesuaikan jam lembur untuk kalkulasi estimasi.</small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-secondary text-white" id="ot_preview_region">Wilayah 1</span>
                                                <span class="badge bg-primary text-white ms-1" id="ot_preview_hours">0 Jam Lembur</span>
                                                <div>
                                                    <a href="{{ route('attendance.overtime.matrix') }}" target="_blank"
                                                        class="small text-decoration-underline text-muted"
                                                        style="font-size: 11px;">
                                                        <i class="fe fe-external-link me-1"></i>Lihat Tabel Matriks Tarif
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label small fw-semibold">Upload Hardcopy / Berkas Formulir Fisik
                                            Pengajuan Lembur <span class="text-danger">*</span></label>
                                        <input type="file" name="hardcopy_file" id="ot_hardcopy"
                                            class="form-control rounded-3"
                                            accept=".pdf,image/png,image/jpeg,image/jpg,.xlsx" required>
                                        <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                            <i class="fe fe-info me-1"></i> Wajib upload bukti fisik/hardcopy formulir
                                            lembur yang telah ditandatangani pemohon & atasan (PDF / Excel / Foto Maks 5MB).
                                        </small>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">Uraian Tugas / Rincian Pekerjaan Lembur
                                            <span class="text-danger">*</span></label>
                                        <textarea name="task_description" id="ot_task_description" rows="2" class="form-control rounded-3"
                                            placeholder="Jelaskan alasan dan rincian pekerjaan lembur yang dikerjakan..." required></textarea>
                                    </div>

                                    <div class="col-12">
                                        <button type="submit"
                                            class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm"
                                            id="btn-submit-desk-ot">
                                            <i class="fe fe-send me-1"></i> Kirim Pengajuan Lembur
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- TAB 4: FORM PENGAJUAN PERUBAHAN & TUKAR SHIFT (KARYAWAN SHIFTING) -->
                        <div class="tab-pane fade" id="changeshift-pane" role="tabpanel">
                            <form id="desktop-changeshift-form" enctype="multipart/form-data">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="alert alert-info py-2 px-3 rounded-3 mb-0" style="font-size: 12px;">
                                            <i class="fe fe-info me-1"></i>
                                            Gunakan form ini untuk mengajukan perubahan shift mandiri atau bertukar jadwal dinas dengan rekan kerja. Setelah diajukan, permohonan akan diproses melalui alur persetujuan Manager Departemen &amp; HCGA.
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Tipe Pengajuan Shift <span class="text-danger">*</span></label>
                                        <select name="request_mode" id="cs_request_mode" class="form-select rounded-3" required onchange="toggleChangeShiftMode(this.value)">
                                            <option value="CHANGE">Ganti Shift Mandiri (Ubah Jadwal Sendiri)</option>
                                            <option value="EXCHANGE">Tukar Jadwal Dinas dengan Rekan Kerja</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Tanggal Dinas yang Diubah <span class="text-danger">*</span></label>
                                        <input type="date" name="original_date" id="cs_original_date" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required onchange="fetchCurrentShiftInfo(this.value)">
                                        <small class="text-muted d-block mt-1" style="font-size: 11px;" id="cs_current_shift_info">
                                            <i class="fe fe-calendar me-1"></i> Shift saat ini: <strong class="text-primary">{{ $schedule['shift_name'] ?? 'Regular' }} ({{ $schedule['default_start'] }} - {{ $schedule['default_end'] }})</strong>
                                        </small>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Shift Pengganti (Tujuan) <span class="text-danger">*</span></label>
                                        <select name="target_shift_id" id="cs_target_shift_id" class="form-select rounded-3" required>
                                            <option value="">-- Pilih Shift Baru --</option>
                                            @foreach ($availableShifts as $sh)
                                                <option value="{{ $sh->id }}">
                                                    {{ $sh->shift_name }} ({{ substr($sh->start_time, 0, 5) }} - {{ substr($sh->end_time, 0, 5) }}) {{ $sh->is_off ? '[LIBUR/OFF]' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6 peer-field" style="display: none;">
                                        <label class="form-label small fw-semibold">Rekan Kerja Pengganti <span class="text-danger">*</span></label>
                                        <select name="peer_nik" id="cs_peer_nik" class="form-select rounded-3" onchange="fetchPeerShiftInfo()">
                                            <option value="">-- Pilih Rekan Satu Departemen --</option>
                                            @foreach ($colleagues as $col)
                                                <option value="{{ $col->Emp_ID }}">
                                                    {{ $col->Name }} ({{ $col->Emp_ID }}) - {{ $col->Position ?: $col->Department }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted d-block mt-1" style="font-size: 11px;" id="cs_peer_shift_info"></small>
                                    </div>

                                    <div class="col-md-6 peer-field" style="display: none;">
                                        <label class="form-label small fw-semibold">Tanggal Shift Rekan <span class="text-danger">*</span></label>
                                        <input type="date" name="target_date" id="cs_target_date" class="form-control rounded-3" value="{{ date('Y-m-d') }}" onchange="fetchPeerShiftInfo()">
                                    </div>

                                    <div class="col-md-6 peer-field" style="display: none;">
                                        <label class="form-label small fw-semibold">Shift Baru untuk Rekan <span class="text-danger">*</span></label>
                                        <select name="peer_target_shift_id" id="cs_peer_target_shift_id" class="form-select rounded-3">
                                            <option value="">-- Otomatis Mengisi Shift Pemohon --</option>
                                            @foreach ($availableShifts as $sh)
                                                <option value="{{ $sh->id }}">
                                                    {{ $sh->shift_name }} ({{ substr($sh->start_time, 0, 5) }} - {{ substr($sh->end_time, 0, 5) }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label small fw-semibold">Lampiran / Berkas Pendukung (Opsional)</label>
                                        <input type="file" name="attachment_file" id="cs_attachment" class="form-control rounded-3" accept=".pdf,image/png,image/jpeg,image/jpg">
                                        <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                            <i class="fe fe-paperclip me-1"></i> Form kesepakatan tukar shift / surat izin pendukung jika diperlukan (PDF / Foto Maks 5MB).
                                        </small>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">Alasan Perubahan / Tukar Shift <span class="text-danger">*</span></label>
                                        <textarea name="reason" id="cs_reason" rows="2" class="form-control rounded-3" placeholder="Jelaskan keperluan dan alasan penggantian jadwal shift..." required></textarea>
                                    </div>

                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm" id="btn-submit-desk-cs">
                                            <i class="fe fe-send me-1"></i> Kirim Pengajuan Perubahan Shift
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel Riwayat Pengajuan & Followup Approval Hierarchy -->
    <div class="card border-0 shadow-sm rounded-4 mt-4">
        <div class="card-header bg-white py-3 border-0">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <h6 class="fw-bold mb-0"><i class="fe fe-git-commit text-primary me-2"></i>Status & Progress Approval
                    Pengajuan Anda</h6>
                <small class="text-muted">Klik pada tombol <strong>Cek Approver</strong> untuk melihat siapa saja yang
                    wajib approval dan siapa yang belum approve</small>
            </div>
            <ul class="nav nav-pills" id="historyTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill px-3 py-1 small fw-semibold" id="hist-leave-tab"
                        data-bs-toggle="pill" data-bs-target="#hist-leave-pane" type="button" role="tab">
                        <i class="fe fe-calendar me-1"></i> Izin & Cuti <span
                            class="badge bg-secondary ms-1">{{ count($leaveRequests) }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-3 py-1 small fw-semibold" id="hist-corr-tab"
                        data-bs-toggle="pill" data-bs-target="#hist-corr-pane" type="button" role="tab">
                        <i class="fe fe-clock me-1"></i> Koreksi Jam Absensi <span
                            class="badge bg-secondary ms-1">{{ count($corrections) }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-3 py-1 small fw-semibold" id="hist-ot-tab"
                        data-bs-toggle="pill" data-bs-target="#hist-ot-pane" type="button" role="tab">
                        <i class="fe fe-zap me-1"></i> Pengajuan Lembur <span
                            class="badge bg-secondary ms-1">{{ count($overtimeRequests) }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-3 py-1 small fw-semibold" id="hist-cs-tab"
                        data-bs-toggle="pill" data-bs-target="#hist-cs-pane" type="button" role="tab">
                        <i class="fe fe-repeat me-1"></i> Tukar / Ganti Shift <span
                            class="badge bg-secondary ms-1">{{ count($changeShiftRequests) }}</span>
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-0">
            <div class="tab-content" id="historyTabContent">
                <!-- TAB RIWAYAT IZIN & CUTI -->
                <div class="tab-pane fade show active" id="hist-leave-pane" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">No Request</th>
                                    <th>Kategori</th>
                                    <th>Tanggal Diajukan</th>
                                    <th>Status Pengajuan</th>
                                    <th>Lampiran</th>
                                    <th class="text-end pe-3">Struktur Approval</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leaveRequests as $lv)
                                    <tr>
                                        <td class="ps-3 fw-bold">{{ $lv->request_no }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $lv->leave_type }}</span>
                                            <small class="d-block text-muted">{{ $lv->total_days }} Hari
                                                ({{ date('d/m/Y', strtotime($lv->start_date)) }} -
                                                {{ date('d/m/Y', strtotime($lv->end_date)) }})
                                            </small>
                                        </td>
                                        <td>{{ date('d M Y H:i', strtotime($lv->created_at)) }}</td>
                                        <td>
                                            <span
                                                class="badge {{ $lv->status == 'APPROVED' ? 'bg-success' : ($lv->status == 'REJECTED' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                                {{ $lv->status }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($lv->attachment_path)
                                                <a href="{{ asset($lv->attachment_path) }}" target="_blank"
                                                    class="btn btn-sm btn-outline-info rounded-pill py-0 px-2"
                                                    style="font-size: 11px;">
                                                    <i class="fe fe-download me-1"></i> Unduh File
                                                </a>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-3">
                                            <div class="d-flex justify-content-end align-items-center gap-1">
                                                @if ($lv->approval_request_id)
                                                    <button type="button"
                                                        class="btn btn-outline-primary btn-sm rounded-pill shadow-sm"
                                                        onclick="showApprovalProgressModal({{ $lv->approval_request_id }}, '{{ $lv->request_no }}')">
                                                        <i class="fe fe-users me-1"></i> Cek Approver
                                                    </button>
                                                @else
                                                    <span class="badge bg-secondary">Tanpa Approval</span>
                                                @endif

                                                @if (in_array($lv->status, ['SUBMITTED', 'IN_PROGRESS', 'WAITING_APPROVAL']))
                                                    <button type="button"
                                                        class="btn btn-outline-danger btn-sm rounded-pill shadow-sm ms-1"
                                                        title="Batalkan Permohonan Cuti/Izin"
                                                        onclick="cancelLeaveRequest({{ $lv->id }}, '{{ $lv->request_no }}')">
                                                        <i class="fe fe-x-circle me-1"></i> Batalkan
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">Belum ada riwayat pengajuan
                                            izin atau cuti.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB RIWAYAT KOREKSI JAM ABSENSI -->
                <div class="tab-pane fade" id="hist-corr-pane" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">No Koreksi</th>
                                    <th>Tipe Koreksi</th>
                                    <th>Tgl Absensi</th>
                                    <th>Jam Diajukan</th>
                                    <th>Alasan</th>
                                    <th>Lampiran</th>
                                    <th>Status</th>
                                    <th class="text-end pe-3">Struktur Approval & Followup</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($corrections as $corr)
                                    <tr>
                                        <td class="ps-3 fw-bold">{{ $corr->correction_no }}</td>
                                        <td>
                                            @if (($corr->correction_type ?? 'BOTH') === 'IN')
                                                <span
                                                    class="badge bg-success-subtle text-success border border-success-subtle"><i
                                                        class="fe fe-arrow-right-circle me-1"></i>Masuk Saja</span>
                                            @elseif(($corr->correction_type ?? 'BOTH') === 'OUT')
                                                <span
                                                    class="badge bg-danger-subtle text-danger border border-danger-subtle"><i
                                                        class="fe fe-arrow-left-circle me-1"></i>Pulang Saja</span>
                                            @else
                                                <span
                                                    class="badge bg-primary-subtle text-primary border border-primary-subtle"><i
                                                        class="fe fe-repeat me-1"></i>Masuk & Pulang</span>
                                            @endif
                                        </td>
                                        <td>{{ date('d/m/Y', strtotime($corr->attendance_date)) }}</td>
                                        <td>
                                            @if (($corr->correction_type ?? 'BOTH') === 'IN')
                                                <div><small class="text-muted">Masuk:</small> <span
                                                        class="fw-bold text-success">{{ $corr->corrected_clock_in ? substr($corr->corrected_clock_in, 0, 5) : '-' }}</span>
                                                </div>
                                                @if ($corr->original_clock_in)
                                                    <small class="text-muted d-block" style="font-size: 11px;">Semula:
                                                        {{ substr($corr->original_clock_in, 0, 5) }}</small>
                                                @endif
                                            @elseif(($corr->correction_type ?? 'BOTH') === 'OUT')
                                                <div><small class="text-muted">Pulang:</small> <span
                                                        class="fw-bold text-danger">{{ $corr->corrected_clock_out ? substr($corr->corrected_clock_out, 0, 5) : '-' }}</span>
                                                </div>
                                                @if ($corr->original_clock_out)
                                                    <small class="text-muted d-block" style="font-size: 11px;">Semula:
                                                        {{ substr($corr->original_clock_out, 0, 5) }}</small>
                                                @endif
                                            @else
                                                <div><span
                                                        class="fw-bold text-primary">{{ $corr->corrected_clock_in ? substr($corr->corrected_clock_in, 0, 5) : '-' }}</span>
                                                    <span class="text-muted">s/d</span> <span
                                                        class="fw-bold text-primary">{{ $corr->corrected_clock_out ? substr($corr->corrected_clock_out, 0, 5) : '-' }}</span>
                                                </div>
                                                @if ($corr->original_clock_in || $corr->original_clock_out)
                                                    <small class="d-block text-muted" style="font-size: 11px;">Semula:
                                                        {{ $corr->original_clock_in ? substr($corr->original_clock_in, 0, 5) : '-' }}
                                                        s/d
                                                        {{ $corr->original_clock_out ? substr($corr->original_clock_out, 0, 5) : '-' }}</small>
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            <span class="small text-truncate d-inline-block" style="max-width: 180px;"
                                                title="{{ $corr->reason }}">
                                                {{ $corr->reason }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($corr->attachment_path)
                                                <a href="{{ asset($corr->attachment_path) }}" target="_blank"
                                                    class="btn btn-sm btn-outline-info rounded-pill py-0 px-2"
                                                    style="font-size: 11px;">
                                                    <i class="fe fe-download me-1"></i> Unduh File
                                                </a>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span
                                                class="badge {{ $corr->status == 'APPROVED' ? 'bg-success' : ($corr->status == 'REJECTED' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                                {{ $corr->status }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-3">
                                            @if ($corr->approval_request_id)
                                                <button type="button"
                                                    class="btn btn-outline-primary btn-sm rounded-pill shadow-sm"
                                                    onclick="showApprovalProgressModal({{ $corr->approval_request_id }}, '{{ $corr->correction_no }}')">
                                                    <i class="fe fe-users me-1"></i> Cek Approver
                                                </button>
                                            @else
                                                <span class="badge bg-secondary">Tanpa Approval</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">Belum ada riwayat pengajuan
                                            koreksi jam absensi.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB RIWAYAT PENGAJUAN LEMBUR -->
                <div class="tab-pane fade" id="hist-ot-pane" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">No Overtime</th>
                                    <th>Tanggal & Jenis</th>
                                    <th>Jam Lembur</th>
                                    <th>Kompensasi</th>
                                    <th>Estimasi Tarif</th>
                                    <th>Hardcopy</th>
                                    <th>Status</th>
                                    <th class="text-end pe-3">Struktur Approval</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($overtimeRequests as $ot)
                                    <tr>
                                        <td class="ps-3 fw-bold">{{ $ot->overtime_no }}</td>
                                        <td>
                                            <div class="fw-semibold text-dark">
                                                {{ date('d M Y', strtotime($ot->overtime_date)) }}</div>
                                            <small class="badge bg-light text-muted border">
                                                {{ $ot->overtime_type === 'BEFORE_SHIFT' ? 'Pra-Shift (Sebelum Jam Masuk)' : 'Pasca-Shift (Setelah Pulang)' }}
                                            </small>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-primary">{{ substr($ot->start_time, 0, 5) }}</span>
                                            <span class="text-muted">s/d</span>
                                            <span class="fw-bold text-primary">{{ substr($ot->end_time, 0, 5) }}</span>
                                            <small class="d-block text-muted">({{ round($ot->duration_minutes / 60, 2) }}
                                                Jam)</small>
                                        </td>
                                        <td>
                                            @if ($ot->compensation_type === 'TIME_OFF')
                                                <span class="badge bg-info-subtle text-info border">Day Off / Cuti</span>
                                            @else
                                                <span class="badge bg-success-subtle text-success border">Uang
                                                    Lembur</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($ot->compensation_type === 'TIME_OFF')
                                                <span class="text-muted small">-</span>
                                            @else
                                                <span class="fw-bold text-success">Rp
                                                    {{ number_format($ot->calculated_amount, 0, ',', '.') }}</span>
                                                <small class="d-block text-muted"
                                                    style="font-size: 11px;">{{ $ot->matrix_name ?? 'Matriks Tarif' }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($ot->hardcopy_file)
                                                <a href="{{ asset($ot->hardcopy_file) }}" target="_blank"
                                                    class="btn btn-sm btn-outline-danger rounded-pill py-0 px-2"
                                                    style="font-size: 11px;">
                                                    <i class="fe fe-file-text me-1"></i> Hardcopy
                                                </a>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span
                                                class="badge {{ $ot->status == 'APPROVED' ? 'bg-success' : ($ot->status == 'REJECTED' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                                {{ $ot->status }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-3">
                                            @if ($ot->approval_request_id)
                                                <button type="button"
                                                    class="btn btn-outline-primary btn-sm rounded-pill shadow-sm"
                                                    onclick="showApprovalProgressModal({{ $ot->approval_request_id }}, '{{ $ot->overtime_no }}')">
                                                    <i class="fe fe-users me-1"></i> Cek Approver
                                                </button>
                                            @else
                                                <span class="badge bg-secondary">Tanpa Approval</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">Belum ada riwayat pengajuan
                                            lembur.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB RIWAYAT PERUBAHAN & TUKAR SHIFT -->
                <div class="tab-pane fade" id="hist-cs-pane" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">No Pengajuan</th>
                                    <th>Mode</th>
                                    <th>Tgl &amp; Shift Asal</th>
                                    <th>Tgl &amp; Shift Tujuan</th>
                                    <th>Rekan Kerja</th>
                                    <th>Alasan</th>
                                    <th>Lampiran</th>
                                    <th>Status</th>
                                    <th class="text-end pe-3">Struktur Approval</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($changeShiftRequests as $cs)
                                    <tr>
                                        <td class="ps-3 fw-bold">{{ $cs->change_shift_no }}</td>
                                        <td>
                                            @if ($cs->request_mode === 'EXCHANGE')
                                                <span class="badge bg-purple text-white"><i class="fe fe-repeat me-1"></i>Tukar Rekan</span>
                                            @else
                                                <span class="badge bg-primary-subtle text-primary border"><i class="fe fe-user me-1"></i>Ganti Mandiri</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ date('d M Y', strtotime($cs->original_date)) }}</div>
                                            <small class="badge bg-light text-muted border">
                                                {{ $cs->current_shift_name ?? 'Default' }}
                                                @if($cs->current_shift_start)
                                                    ({{ substr($cs->current_shift_start, 0, 5) }} - {{ substr($cs->current_shift_end, 0, 5) }})
                                                @endif
                                            </small>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ date('d M Y', strtotime($cs->target_date ?: $cs->original_date)) }}</div>
                                            <small class="badge bg-info-subtle text-info border">
                                                {{ $cs->target_shift_name }}
                                                @if($cs->target_shift_start)
                                                    ({{ substr($cs->target_shift_start, 0, 5) }} - {{ substr($cs->target_shift_end, 0, 5) }})
                                                @endif
                                            </small>
                                        </td>
                                        <td>
                                            @if ($cs->peer_nik)
                                                <span class="fw-semibold text-dark">{{ $cs->peer_name ?: $cs->peer_nik }}</span>
                                                <small class="d-block text-muted">NIK: {{ $cs->peer_nik }}</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted" title="{{ $cs->reason }}">{{ \Illuminate\Support\Str::limit($cs->reason, 35) }}</small>
                                        </td>
                                        <td>
                                            @if ($cs->attachment)
                                                <a href="{{ asset($cs->attachment) }}" target="_blank" class="btn btn-outline-secondary btn-sm py-0 px-2 rounded-pill" style="font-size: 11px;">
                                                    <i class="fe fe-paperclip me-1"></i>File
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $cs->status == 'APPROVED' ? 'bg-success' : ($cs->status == 'REJECTED' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                                {{ $cs->status }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-3">
                                            @if ($cs->approval_request_id)
                                                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill shadow-sm"
                                                    onclick="showApprovalProgressModal({{ $cs->approval_request_id }}, '{{ $cs->change_shift_no }}')">
                                                    <i class="fe fe-users me-1"></i> Cek Approver
                                                </button>
                                            @else
                                                <span class="badge bg-secondary">Tanpa Approval</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted">Belum ada riwayat permohonan perubahan / tukar shift.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL POPUP LIHAT APPROVAL HIERARCHY & APPROVER STATUS -->
    <div class="modal fade" id="approvalProgressModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="apprModalTitle">Alur Persetujuan & Approver</h5>
                        <small class="text-muted" id="apprModalSub">Cek siapa saja yang sudah dan belum melakukan
                            persetujuan</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="apprModalBody">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"><span
                                class="visually-hidden">Loading...</span></div>
                        <p class="mt-2 text-muted">Memuat data hierarki approval...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL POPUP PREVIEW FOTO SELFIE DESKTOP -->
    <div class="modal fade" id="previewDesktopSelfieModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-0 pb-1">
                    <h6 class="modal-title fw-bold" id="previewDesktopSelfieTitle">Foto Bukti Kehadiran</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3 text-center">
                    <img id="previewDesktopSelfieImg" src="" class="img-fluid rounded-3 shadow-sm border"
                        alt="Selfie" style="max-height: 380px; width: 100%; object-fit: cover;">
                </div>
            </div>
        </div>
    </div>

    <script>
        let deskLat = -6.1440;
        let deskLng = 106.8990;

        // Jam Desktop Realtime
        setInterval(() => {
            const now = new Date();
            document.getElementById('desktop-clock').innerText = now.toTimeString().split(' ')[0];
        }, 1000);

        // Browser Geolocation
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    deskLat = pos.coords.latitude;
                    deskLng = pos.coords.longitude;
                    document.getElementById('desktop-geo-status').innerHTML =
                        `<i class="fe fe-map-pin text-success me-1"></i>Lokasi terdeteksi: ${deskLat.toFixed(5)}, ${deskLng.toFixed(5)}`;
                },
                (err) => {
                    document.getElementById('desktop-geo-status').innerHTML =
                        `<i class="fe fe-info text-muted me-1"></i>Menggunakan koordinat default kantor.`;
                }
            );
        }

        // Modal Preview Foto Selfie Desktop
        function previewDesktopSelfie(url, title) {
            document.getElementById('previewDesktopSelfieTitle').innerText = title || 'Foto Bukti Kehadiran';
            document.getElementById('previewDesktopSelfieImg').src = url;
            const modal = new bootstrap.Modal(document.getElementById('previewDesktopSelfieModal'));
            modal.show();
        }

        // Eksekusi Presensi dari Web Desktop
        function doDesktopAttendance(action) {
            if (!confirm(`Apakah Anda yakin ingin melakukan ${action.toUpperCase()} sekarang?`)) return;

            const url = action === 'clock-in' ? "{{ route('attendance.mobile.clockin') }}" :
                "{{ route('attendance.mobile.clockout') }}";

            if (typeof $ !== 'undefined' && typeof $.blockUI === 'function') {
                $.blockUI({
                    message: '<h6><span class="spinner-border spinner-border-sm me-2"></span>Memproses Presensi Desktop...</h6>'
                });
            }

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        latitude: deskLat,
                        longitude: deskLng,
                        photo: null,
                        notes: 'Absen via Web Desktop Portal'
                    })
                })
                .then(res => res.json().then(data => ({
                    status: res.status,
                    ok: res.ok,
                    body: data
                })))
                .then(({
                    status,
                    ok,
                    body
                }) => {
                    if (typeof $ !== 'undefined' && typeof $.unblockUI === 'function') {
                        $.unblockUI();
                    }
                    if (ok && body.status === 'success') {
                        alert(body.message);
                        window.location.reload();
                    } else {
                        alert('Gagal Presensi (' + status + '):\n' + (body.message || 'Terjadi kesalahan sistem.'));
                    }
                })
                .catch(err => {
                    if (typeof $ !== 'undefined' && typeof $.unblockUI === 'function') {
                        $.unblockUI();
                    }
                    alert('Terjadi kesalahan koneksi.');
                });
        }

        // Event ganti kategori cuti / izin
        function handleLeaveCategoryChange(select) {
            const opt = select.options[select.selectedIndex];
            const isDeduct = opt.getAttribute('data-deduct') === 'YES';
            const isNeedDoc = opt.getAttribute('data-attachment') === 'YES';
            const maxDays = opt.getAttribute('data-max');

            let hint = '';
            if (isDeduct) {
                hint += '<span class="text-danger fw-semibold"><i class="fe fe-alert-triangle me-1"></i>Memotong kuota cuti tahunan.</span> ';
            } else {
                hint += '<span class="text-success fw-semibold"><i class="fe fe-check me-1"></i>Izin khusus berbayar (tidak memotong cuti).</span> ';
            }

            if (isNeedDoc) {
                hint += '<span class="text-warning fw-semibold ms-1"><i class="fe fe-paperclip me-1"></i>Wajib upload dokumen pendukung (Surat Dokter/Keterangan).</span>';
            }

            document.getElementById('leave_cat_hint').innerHTML = hint;
        }

        // Jalankan saat pertama kali halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            const leaveSelect = document.getElementById('desk_leave_type');
            if (leaveSelect) {
                handleLeaveCategoryChange(leaveSelect);
            }
        });

        // Submit Form Izin & Cuti Web Desktop
        document.getElementById('desktop-leave-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-submit-desk-leave');
            btn.disabled = true;

            const formData = new FormData(this);

            fetch("{{ route('attendance.mobile.submitleave') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(res => res.json().then(data => ({
                    status: res.status,
                    ok: res.ok,
                    body: data
                })))
                .then(({
                    status,
                    ok,
                    body
                }) => {
                    if (ok && body.status === 'success') {
                        alert(body.message || 'Pengajuan cuti/izin berhasil dikirim!');
                        window.location.reload();
                    } else {
                        let errorMsg = body.message || 'Terjadi kendala pengajuan izin/cuti.';
                        if (body.errors) {
                            const errList = Object.values(body.errors).flat().join('\n');
                            errorMsg += '\n' + errList;
                        }
                        alert('Gagal: ' + errorMsg);
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    alert('Terjadi kesalahan jaringan.');
                    btn.disabled = false;
                });
        });

        // Submit Form Koreksi Absensi Web Desktop
        document.getElementById('desktop-corr-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-submit-desk-corr');
            btn.disabled = true;

            const formData = new FormData(this);

            fetch("{{ route('attendance.request.correction') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(res => res.json().then(data => ({
                    status: res.status,
                    ok: res.ok,
                    body: data
                })))
                .then(({
                    status,
                    ok,
                    body
                }) => {
                    if (ok && body.status === 'success') {
                        alert(body.message || 'Pengajuan koreksi absensi berhasil dikirim!');
                        window.location.reload();
                    } else {
                        let errorMsg = body.message || 'Terjadi kendala pengajuan koreksi.';
                        if (body.errors) {
                            const errList = Object.values(body.errors).flat().join('\n');
                            errorMsg += '\n' + errList;
                        }
                        alert('Gagal: ' + errorMsg);
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    alert('Terjadi kesalahan jaringan.');
                    btn.disabled = false;
                });
        });

        // Update Kodepos saat pilih Lokasi Kerja
        function updateLocationZipcode() {
            const select = document.getElementById('ot_location_id');
            const selectedOpt = select.options[select.selectedIndex];
            const zip = selectedOpt ? selectedOpt.getAttribute('data-zip') : '';
            if (zip) {
                document.getElementById('ot_zipcode').value = zip;
            }
            calculateOvertimePreview();
        }

        // Kalkulasi Preview Matriks Lembur Realtime
        function calculateOvertimePreview() {
            const startTime = document.getElementById('ot_start_time').value;
            const endTime = document.getElementById('ot_end_time').value;
            const compType = document.getElementById('ot_compensation').value;
            const locId = document.getElementById('ot_location_id').value;
            const zipcode = document.getElementById('ot_zipcode').value;

            if (!startTime || !endTime) return;

            fetch("{{ route('attendance.overtime.preview') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        start_time: startTime,
                        end_time: endTime,
                        compensation_type: compType,
                        location_id: locId,
                        zipcode: zipcode
                    })
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success' && res.data) {
                        const d = res.data;
                        document.getElementById('ot_preview_amount').innerText = d.formatted_amount || 'Rp 0';
                        document.getElementById('ot_preview_breakdown').innerText = d.breakdown || '';
                        document.getElementById('ot_preview_hours').innerText = (d.duration_hours || 0) +
                            ' Jam Lembur (' + (d.duration_minutes || 0) + ' Mnt)';
                        document.getElementById('ot_preview_region').innerText = res.region === 'WILAYAH_2' ?
                            'Wilayah 2 (Daerah)' : 'Wilayah 1 (Jabodetabek)';
                    }
                })
                .catch(err => {
                    console.error('Error preview overtime', err);
                });
        }

        // Submit Form Pengajuan Lembur Web Desktop (Wajib Hardcopy)
        document.getElementById('desktop-overtime-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-submit-desk-ot');
            btn.disabled = true;

            const fileInput = document.getElementById('ot_hardcopy');
            if (!fileInput.files || fileInput.files.length === 0) {
                alert('Wajib melampirkan berkas fisik / hardcopy formulir pengajuan lembur.');
                btn.disabled = false;
                return;
            }

            const formData = new FormData(this);

            fetch("{{ route('attendance.request.overtime') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(res => res.json().then(data => ({
                    status: res.status,
                    ok: res.ok,
                    body: data
                })))
                .then(({
                    status,
                    ok,
                    body
                }) => {
                    if (ok && body.status === 'success') {
                        alert(body.message || 'Pengajuan lembur berhasil dikirimkan!');
                        window.location.reload();
                    } else {
                        let errorMsg = body.message || 'Terjadi kendala pengajuan lembur.';
                        if (body.errors) {
                            const errList = Object.values(body.errors).flat().join('\n');
                            errorMsg += '\n' + errList;
                        }
                        alert('Gagal: ' + errorMsg);
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    alert('Terjadi kesalahan jaringan.');
                    btn.disabled = false;
                });
        });

        // Toggle Input Koreksi Jam (Masuk Saja / Pulang Saja / Keduanya)
        function toggleCorrectionInputs() {
            const radio = document.querySelector('input[name="correction_type"]:checked');
            const type = radio ? radio.value : 'BOTH';

            // Highlight card yang dipilih
            document.querySelectorAll('.corr-type-card').forEach(el => {
                el.classList.remove('border-primary', 'bg-white', 'shadow-sm');
                el.classList.add('bg-light');
            });
            if (radio) {
                const parentCard = radio.closest('.corr-type-card');
                if (parentCard) {
                    parentCard.classList.remove('bg-light');
                    parentCard.classList.add('border-primary', 'bg-white', 'shadow-sm');
                }
            }

            const inWrap = document.getElementById('corr_in_wrapper');
            const outWrap = document.getElementById('corr_out_wrapper');
            const inInput = document.getElementById('corr_in');
            const outInput = document.getElementById('corr_out');
            const inReq = document.getElementById('corr_in_req');
            const outReq = document.getElementById('corr_out_req');
            const inHelp = document.getElementById('corr_in_help');
            const outHelp = document.getElementById('corr_out_help');

            if (type === 'IN') {
                inInput.disabled = false;
                inInput.required = true;
                inWrap.style.opacity = '1';
                if (inReq) inReq.style.display = 'inline';
                if (inHelp) inHelp.innerText = 'Mengajukan jam masuk kehadiran baru.';

                outInput.disabled = true;
                outInput.required = false;
                outWrap.style.opacity = '0.45';
                if (outReq) outReq.style.display = 'none';
                if (outHelp) outHelp.innerText = 'Jam pulang tidak diubah (menggunakan data semula).';
            } else if (type === 'OUT') {
                inInput.disabled = true;
                inInput.required = false;
                inWrap.style.opacity = '0.45';
                if (inReq) inReq.style.display = 'none';
                if (inHelp) inHelp.innerText = 'Jam masuk tidak diubah (menggunakan data semula).';

                outInput.disabled = false;
                outInput.required = true;
                outWrap.style.opacity = '1';
                if (outReq) outReq.style.display = 'inline';
                if (outHelp) outHelp.innerText = 'Mengajukan jam pulang kehadiran baru.';
            } else {
                // BOTH
                inInput.disabled = false;
                inInput.required = true;
                inWrap.style.opacity = '1';
                if (inReq) inReq.style.display = 'inline';
                if (inHelp) inHelp.innerText = 'Isikan jam kehadiran masuk Anda.';

                outInput.disabled = false;
                outInput.required = true;
                outWrap.style.opacity = '1';
                if (outReq) outReq.style.display = 'inline';
                if (outHelp) outHelp.innerText = 'Isikan jam kepulangan Anda.';
            }
        }

        // Modal Progress Alur Approval & Followup
        function showApprovalProgressModal(requestId, reqNo) {
            document.getElementById('apprModalTitle').innerText = 'Alur & Struktur Approval: ' + reqNo;
            document.getElementById('apprModalBody').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
            <p class="mt-2 text-muted">Memuat data struktur approver lengkap...</p>
        </div>
    `;

            const modal = new bootstrap.Modal(document.getElementById('approvalProgressModal'));
            modal.show();

            fetch(`/attendance/approval-progress/${requestId}`)
                .then(res => res.json())
                .then(res => {
                    if (res.status !== 'success') {
                        document.getElementById('apprModalBody').innerHTML =
                            `<div class="alert alert-danger">${res.message}</div>`;
                        return;
                    }

                    const stages = res.stages || [];
                    const req = res.request;

                    // Info Banner Pengajuan
                    let html = `
            <div class="mb-3 p-3 bg-light rounded-4 border">
                <div class="row g-2 small">
                    <div class="col-md-4"><strong>Tipe Pengajuan:</strong> <span class="badge bg-secondary-subtle text-dark border">${req.Request_Type}</span></div>
                    <div class="col-md-4"><strong>No Request:</strong> <span class="fw-bold">${req.Request_No || '-'}</span></div>
                    <div class="col-md-4"><strong>Status Akhir:</strong> 
                        <span class="badge ${req.Status === 'APPROVED' ? 'bg-success' : (req.Status === 'REJECTED' ? 'bg-danger' : 'bg-primary')}">${req.Status}</span>
                    </div>
                    <div class="col-md-12 mt-1"><strong>Deskripsi:</strong> ${req.Description || '-'}</div>
                </div>
            </div>
        `;

                    // Stepper Visual Horizontal
                    if (stages.length > 0) {
                        html += `
                <div class="mb-4 p-3 bg-white border rounded-4 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center position-relative">
            `;

                        stages.forEach((st, idx) => {
                            let circleClass = 'bg-secondary text-white';
                            let textClass = 'text-muted';
                            let icon = `${st.stage_order}`;

                            if (st.status === 'APPROVED') {
                                circleClass = 'bg-success text-white shadow-sm';
                                textClass = 'text-success fw-bold';
                                icon = '<i class="fe fe-check"></i>';
                            } else if (st.is_current) {
                                circleClass = 'bg-warning text-dark shadow-sm border border-2 border-dark';
                                textClass = 'text-warning-emphasis fw-bold';
                                icon = '<i class="fe fe-clock"></i>';
                            } else if (st.status === 'REJECTED') {
                                circleClass = 'bg-danger text-white shadow-sm';
                                textClass = 'text-danger fw-bold';
                                icon = '<i class="fe fe-x"></i>';
                            }

                            html += `
                    <div class="text-center flex-fill position-relative z-1">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center fw-bold ${circleClass}" style="width: 36px; height: 36px; font-size: 13px;">
                            ${icon}
                        </div>
                        <div class="small mt-1 ${textClass}" style="font-size: 11px;">
                            Stage ${st.stage_order}<br><span class="fw-semibold">${st.label}</span>
                        </div>
                    </div>
                `;
                        });

                        html += `
                    </div>
                </div>
            `;
                    }

                    html += `<h6 class="fw-bold mb-3 d-flex justify-content-between align-items-center">
                    <span><i class="fe fe-users text-primary me-2"></i>Rincian Tahapan & Petugas Approver:</span>
                    <span class="badge bg-light text-muted border">${stages.length} Tahap Persetujuan</span>
                 </h6>`;

                    html += `<div class="d-flex flex-column gap-3">`;

                    stages.forEach(st => {
                        const isApproved = st.status === 'APPROVED';
                        const isPending = st.status === 'PENDING' || st.status === 'IN_PROGRESS';
                        const isWaiting = st.status === 'WAITING';
                        const isRejected = st.status === 'REJECTED';

                        let cardBorder = 'border-secondary-subtle';
                        let badgeBg = 'bg-secondary';
                        let statusLabel = 'Menunggu Giliran';

                        if (isApproved) {
                            cardBorder = 'border-success';
                            badgeBg = 'bg-success';
                            statusLabel = 'Telah Disetujui';
                        } else if (isPending) {
                            cardBorder = 'border-warning shadow-sm';
                            badgeBg = 'bg-warning text-dark';
                            statusLabel = 'Sedang Diproses (Saat Ini)';
                        } else if (isRejected) {
                            cardBorder = 'border-danger';
                            badgeBg = 'bg-danger';
                            statusLabel = 'Ditolak';
                        }

                        let approverListHtml = '';
                        if (st.approvers && st.approvers.length > 0) {
                            approverListHtml = `<div class="mt-2 pt-2 border-top">`;
                            approverListHtml +=
                                `<div class="small text-muted fw-semibold mb-1">Daftar Pejabat Approver yang Berwenang:</div>`;
                            approverListHtml += `<div class="row g-2">`;
                            st.approvers.forEach(appr => {
                                const statusBadge = appr.status === 'APPROVED' ?
                                    `<span class="badge bg-success ms-auto"><i class="fe fe-check me-1"></i>Approved</span>` :
                                    (appr.status === 'ACTIVE' ?
                                        `<span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-auto">Belum Approve</span>` :
                                        `<span class="badge bg-light text-muted border ms-auto">${appr.status || 'Kandidat'}</span>`
                                    );

                                approverListHtml += `
                        <div class="col-md-12">
                            <div class="p-2 rounded-3 bg-white border d-flex align-items-center gap-2">
                                <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                    <i class="fe fe-user"></i>
                                </div>
                                <div>
                                    <div class="fw-bold small mb-0">${appr.name || appr.Approver_Name}</div>
                                    <div class="text-muted" style="font-size: 11px;">
                                        ${appr.position || appr.Approver_Dept || appr.department || 'Approver'} &bull; ${appr.email || appr.Approver_Email || appr.username || '-'}
                                    </div>
                                </div>
                                ${statusBadge}
                            </div>
                        </div>
                    `;
                            });
                            approverListHtml += `</div></div>`;
                        } else {
                            approverListHtml = `
                    <div class="mt-2 pt-2 border-top small text-muted">
                        <i class="fe fe-info me-1"></i>Petugas penanggung jawab otomatis dialokasikan saat tahap ${st.stage_order} aktif.
                    </div>
                `;
                        }

                        let notesHtml = '';
                        if (st.notes) {
                            notesHtml =
                                `<div class="small mt-2 p-2 bg-light rounded text-muted"><em>Catatan: ${st.notes}</em></div>`;
                        }

                        html += `
                <div class="card border ${cardBorder} rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-2 px-3 border-0 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary rounded-pill px-2 py-1" style="font-size: 11px;">Tahap ${st.stage_order}</span>
                            <span class="fw-bold text-dark">${st.label}</span>
                        </div>
                        <span class="badge ${badgeBg}">${statusLabel}</span>
                    </div>
                    <div class="card-body pt-0 px-3 pb-3">
                        <div class="small text-muted mb-1">
                            <strong>Level Posisi:</strong> Level ${st.level_number} ${st.department ? '(' + st.department + ')' : ''}
                        </div>
                        ${approverListHtml}
                        ${notesHtml}
                    </div>
                </div>
            `;
                    });

                    html += `</div>`;
                    document.getElementById('apprModalBody').innerHTML = html;
                })
                .catch(err => {
                    document.getElementById('apprModalBody').innerHTML =
                        `<div class="alert alert-danger">Gagal memuat struktur alur approval.</div>`;
                });
        }

        // Handler Khusus Admin: Reset Presensi
        function resetAttendanceDev(type) {
            let confirmMsg = 'Apakah Anda yakin ingin me-reset absensi hari ini?';
            if (type === 'in') confirmMsg = 'Reset Clock-In hari ini?';
            if (type === 'out') confirmMsg = 'Reset Clock-Out hari ini?';
            if (type === 'both') confirmMsg = 'Reset Clock-In DAN Clock-Out hari ini ke kondisi awal?';

            if (!confirm(confirmMsg)) return;

            if (typeof $ !== 'undefined' && typeof $.blockUI === 'function') {
                $.blockUI({
                    message: '<h6><span class="spinner-border spinner-border-sm me-2"></span>Mereset Log Absensi...</h6>'
                });
            }

            fetch("{{ route('attendance.admin.reset') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        type: type
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (typeof $ !== 'undefined' && typeof $.unblockUI === 'function') {
                        $.unblockUI();
                    }
                    if (data.status === 'success') {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert('Gagal Reset: ' + data.message);
                    }
                })
                .catch(err => {
                    if (typeof $ !== 'undefined' && typeof $.unblockUI === 'function') {
                        $.unblockUI();
                    }
                    alert('Terjadi kesalahan koneksi.');
                });
        }

        // Handler Toggle & Logika Pengajuan Change Shift
        function toggleChangeShiftMode(mode) {
            const peerFields = document.querySelectorAll('.peer-field');
            if (mode === 'EXCHANGE') {
                peerFields.forEach(el => el.style.display = 'block');
                const peerNikInput = document.getElementById('cs_peer_nik');
                if (peerNikInput) peerNikInput.setAttribute('required', 'required');
            } else {
                peerFields.forEach(el => el.style.display = 'none');
                const peerNikInput = document.getElementById('cs_peer_nik');
                if (peerNikInput) peerNikInput.removeAttribute('required');
            }
        }

        function fetchCurrentShiftInfo(date) {
            if (!date) return;
            const infoEl = document.getElementById('cs_current_shift_info');
            if (!infoEl) return;
            infoEl.innerHTML = '<i class="fe fe-loader fe-spin me-1"></i> Memuat jadwal shift...';

            fetch(`{{ route('attendance.changeshift.schedule') }}?date=${date}`)
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success' && res.data) {
                        const d = res.data;
                        infoEl.innerHTML = `<i class="fe fe-calendar me-1"></i> Shift saat ini: <strong class="text-primary">${d.shift_name || 'Regular'} (${d.default_start} - ${d.default_end})</strong> ${d.is_holiday ? '<span class="badge bg-danger ms-1">Libur Nasional</span>' : ''}`;
                    } else {
                        infoEl.innerHTML = '<i class="fe fe-info me-1"></i> Jadwal shift tidak ditemukan.';
                    }
                })
                .catch(() => {
                    infoEl.innerHTML = '<i class="fe fe-alert-circle text-danger me-1"></i> Gagal memeriksa jadwal.';
                });
        }

        function fetchPeerShiftInfo() {
            const peerNikEl = document.getElementById('cs_peer_nik');
            const targetDateEl = document.getElementById('cs_target_date');
            const origDateEl = document.getElementById('cs_original_date');
            const peerInfoEl = document.getElementById('cs_peer_shift_info');

            if (!peerNikEl || !peerInfoEl) return;
            const peerNik = peerNikEl.value;
            const targetDate = (targetDateEl && targetDateEl.value) ? targetDateEl.value : (origDateEl ? origDateEl.value : '');

            if (!peerNik || !targetDate) {
                peerInfoEl.innerHTML = '';
                return;
            }

            peerInfoEl.innerHTML = '<i class="fe fe-loader fe-spin me-1"></i> Memeriksa jadwal rekan...';
            fetch(`{{ route('attendance.changeshift.schedule') }}?date=${targetDate}&nik=${peerNik}`)
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success' && res.data) {
                        const d = res.data;
                        peerInfoEl.innerHTML = `<i class="fe fe-info me-1"></i> Shift rekan pada ${targetDate}: <strong class="text-purple">${d.shift_name || 'Regular'} (${d.default_start} - ${d.default_end})</strong>`;
                    }
                })
                .catch(() => {
                    peerInfoEl.innerHTML = '';
                });
        }

        // Submit Form Pengajuan Perubahan Shift
        const deskChangeShiftForm = document.getElementById('desktop-changeshift-form');
        if (deskChangeShiftForm) {
            deskChangeShiftForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const btn = document.getElementById('btn-submit-desk-cs');
                if (btn) btn.disabled = true;

                const formData = new FormData(this);

                fetch("{{ route('attendance.request.changeshift') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(res => res.json().then(data => ({
                        status: res.status,
                        ok: res.ok,
                        body: data
                    })))
                    .then(({ status, ok, body }) => {
                        if (ok && body.status === 'success') {
                            alert(body.message || 'Pengajuan perubahan shift berhasil dikirimkan!');
                            window.location.reload();
                        } else {
                            let errorMsg = body.message || 'Terjadi kendala pengajuan perubahan shift.';
                            if (body.errors) {
                                const errList = Object.values(body.errors).flat().join('\n');
                                errorMsg += '\n' + errList;
                            }
                            alert('Gagal: ' + errorMsg);
                            if (btn) btn.disabled = false;
                        }
                    })
                    .catch(err => {
                        alert('Terjadi kesalahan jaringan.');
                        if (btn) btn.disabled = false;
                    });
            });
        }

        // Fungsi Pembatalan Pengajuan Cuti / Izin oleh Karyawan
        function cancelLeaveRequest(leaveId, requestNo) {
            const reason = prompt(`Apakah Anda yakin ingin membatalkan pengajuan ${requestNo}?\nMasukkan alasan pembatalan (opsional):`);
            if (reason === null) return; // Klik Batal di prompt

            fetch("{{ route('attendance.request.leave.cancel') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    leave_id: leaveId,
                    cancel_reason: reason
                })
            })
            .then(res => res.json().then(data => ({ status: res.status, ok: res.ok, body: data })))
            .then(({ status, ok, body }) => {
                if (ok && body.status === 'success') {
                    alert(body.message || 'Pengajuan cuti berhasil dibatalkan.');
                    window.location.reload();
                } else {
                    alert('Gagal membatalkan: ' + (body.message || 'Terjadi kesalahan sistem.'));
                }
            })
            .catch(err => {
                alert('Terjadi kesalahan jaringan.');
            });
        }
    </script>
@endsection
