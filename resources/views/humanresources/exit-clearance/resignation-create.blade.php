@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
@php
    // Inisialisasi Join Date otomatis dari tabel employment jika belum diset dari controller
    $resolvedJoinDate = old('Join_Date', $user->join_date ?? '');
    if (empty($resolvedJoinDate) && !empty($user->Emp_ID)) {
        $userEmpRecord = \Illuminate\Support\Facades\DB::table('employment')
            ->where('NIK', $user->Emp_ID)
            ->select('Date_in', 'Start_Date')
            ->orderBy('ID', 'desc')
            ->first();
        if ($userEmpRecord) {
            $rawDate = (!empty($userEmpRecord->Date_in) && $userEmpRecord->Date_in !== '0000-00-00')
                ? $userEmpRecord->Date_in
                : ((!empty($userEmpRecord->Start_Date) && $userEmpRecord->Start_Date !== '0000-00-00') ? $userEmpRecord->Start_Date : null);
            if ($rawDate) {
                $resolvedJoinDate = date('Y-m-d', strtotime($rawDate));
            }
        }
    }

    // Default tanggal pengajuan hari ini dan tanggal terakhir bekerja otomatis +1 bulan (dapat diedit)
    $defaultResignDate = old('Resign_Date', date('Y-m-d'));
    $defaultLastWorkingDate = old('Last_Working_Date', \Carbon\Carbon::parse($defaultResignDate)->addMonthsNoOverflow(1)->format('Y-m-d'));
@endphp
<style>
    .exit-page-wrapper {
        margin-bottom: 2rem;
    }
    .exit-card {
        border: none;
        border-radius: 14px;
        box-shadow: 0 2px 14px rgba(0, 0, 0, 0.05);
        background: #ffffff;
        overflow: hidden;
    }
    .exit-card-header {
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
        padding: 1.25rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .exit-card-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }
    .exit-btn {
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 0.5rem 1.15rem;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        transition: all 0.2s ease;
    }
    .exit-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    }
    .select2-container--default .select2-selection--single {
        height: 38px !important;
        border: 1px solid #ced4da !important;
        border-radius: 6px !important;
        display: flex !important;
        align-items: center !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        padding-left: 12px !important;
        font-size: 0.95rem;
        color: #1e293b;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
        right: 8px !important;
    }
</style>

{{-- Breadcrumb Header persis style Index SPPD --}}
<div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
    <div>
        <h4 class="content-title fw-bold mb-1" style="font-size: 1.35rem; letter-spacing: 0.3px;">
            Pengajuan Pengunduran Diri (Resignation)
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Human Capital</a></li>
                <li class="breadcrumb-item"><a href="{{ route('hrd-exit-clearance-index') }}">Exit Clearance</a></li>
                <li class="breadcrumb-item active fw-bold text-primary" aria-current="page">Form Resign</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('hrd-exit-clearance-index') }}" class="btn btn-outline-secondary exit-btn shadow-sm">
            <i class="fa fa-arrow-left"></i> Kembali ke Daftar
        </a>
    </div>
</div>

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="fa fa-exclamation-circle fa-lg me-2 text-danger"></i>
            <div>{{ session('error') }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="exit-page-wrapper">
    <div class="card exit-card">
        <div class="exit-card-header">
            <div>
                <h3 class="exit-card-title">
                    <i class="fa fa-file-text-o text-primary"></i> Formulir Pengajuan Karyawan
                </h3>
                <small class="text-muted"><i class="fa fa-info-circle me-1"></i> 
                    @if(!empty($canSelectEmployee))
                        Pilih nama karyawan yang mengajukan pengunduran diri, lalu lengkapi alasan dan tanggal efektifnya.
                    @else
                        Formulir pengajuan pengunduran diri pribadi Anda. Lengkapi alasan dan tanggal efektif pengunduran diri.
                    @endif
                </small>
            </div>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('hrd-exit-clearance-resignation-store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    {{-- Input Pemilihan Karyawan --}}
                    <div class="col-md-6 mb-3">
                        @if(!empty($canSelectEmployee))
                            <label class="form-label fw-bold">Pilih Nama Karyawan <span class="text-danger">*</span></label>
                            <select name="User_ID" id="employee_select" class="form-select select2" required>
                                <option value="">-- Ketik / Cari Nama Karyawan --</option>
                                @if(!empty($employees))
                                    @foreach($employees as $emp)
                                        @php
                                            $empName = $emp->Name ?? ($emp->name ?? ($emp->username ?? ''));
                                            $empId = $emp->id;
                                            $isSelected = (old('User_ID', $user->id ?? 0) == $empId);
                                            $empJoinDate = $emp->join_date ?? '';
                                        @endphp
                                        <option value="{{ $empId }}"
                                            data-name="{{ $empName }}"
                                            data-position="{{ $emp->Position ?? '' }}"
                                            data-division="{{ $emp->Area ?? '' }}"
                                            data-department="{{ $emp->Department ?? '' }}"
                                            data-nik="{{ $emp->Emp_ID ?? '' }}"
                                            data-join-date="{{ $empJoinDate }}"
                                            {{ $isSelected ? 'selected' : '' }}>
                                            {{ $empName }} - {{ $emp->Department ?? 'No Dept' }} ({{ $emp->Emp_ID ?? $emp->username }})
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <input type="hidden" name="Nama" id="input_nama" value="{{ old('Nama', $user->Name ?? ($user->name ?? ($user->username ?? ''))) }}">
                            <div class="mt-1 small text-muted d-flex align-items-center gap-2">
                                <span id="selected_nik_badge" class="badge bg-light text-dark border">
                                    NIK: {{ $user->Emp_ID ?? '-' }}
                                </span>
                                <span class="text-info small"><i class="fa fa-info-circle"></i> Mode Atasan / Administrator (Pilih Karyawan)</span>
                            </div>
                        @else
                            <label class="form-label fw-bold">Nama Karyawan</label>
                            <input type="text" class="form-control bg-light" value="{{ $user->Name ?? ($user->name ?? ($user->username ?? '')) }}" readonly>
                            <input type="hidden" name="User_ID" value="{{ $user->id }}">
                            <input type="hidden" name="Nama" id="input_nama" value="{{ $user->Name ?? ($user->name ?? ($user->username ?? '')) }}">
                            <div class="mt-1 small text-muted">
                                <span class="badge bg-light text-dark border">
                                    NIK: {{ $user->Emp_ID ?? '-' }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Jabatan</label>
                        <input type="text" name="Jabatan" id="input_jabatan" class="form-control" value="{{ old('Jabatan', $user->Position ?? '') }}" placeholder="Jabatan">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Divisi</label>
                        <input type="text" name="Divisi" id="input_divisi" class="form-control" value="{{ old('Divisi', $user->division ?? ($user->Area ?? '')) }}" placeholder="Divisi">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Departemen</label>
                        <input type="text" name="Departemen" id="input_departemen" class="form-control" value="{{ old('Departemen', $user->Department ?? '') }}" placeholder="Departemen">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Tanggal Masuk (Join Date)</label>
                        <input type="date" name="Join_Date" id="input_join_date" class="form-control" value="{{ old('Join_Date', $resolvedJoinDate) }}">
                        <small class="text-muted"><i class="fa fa-info-circle me-1"></i> Otomatis dari master employment</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Tanggal Pengajuan <span class="text-danger">*</span></label>
                        <input type="date" name="Resign_Date" id="input_resign_date" class="form-control" value="{{ $defaultResignDate }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Tanggal Terakhir Bekerja (Last Working Day) <span class="text-danger">*</span></label>
                        <input type="date" name="Last_Working_Date" id="input_last_working_date" class="form-control" value="{{ $defaultLastWorkingDate }}" required>
                        <small class="text-muted"><i class="fa fa-info-circle me-1"></i> Otomatis +1 bulan (notice period, dapat diedit)</small>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label fw-bold">Alasan Pengunduran Diri <span class="text-danger">*</span></label>
                        <textarea name="Reason" class="form-control" rows="4" required placeholder="Jelaskan alasan pengunduran diri secara detail...">{{ old('Reason') }}</textarea>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="form-label fw-bold">Lampiran Surat Pengunduran Diri (PDF/Gambar)</label>
                        <input type="file" name="Resignation_Letter_File" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        <small class="text-muted">Format berkas: PDF, JPG, PNG (Maks 5MB)</small>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('hrd-exit-clearance-index') }}" class="btn btn-outline-secondary exit-btn">
                        <i class="fa fa-arrow-left"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary exit-btn shadow-sm">
                        <i class="fa fa-paper-plane"></i> Ajukan ke Atasan Langsung
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // --- 1. Otomatisasi Tanggal Terakhir Bekerja (+1 Bulan dari Tanggal Pengajuan) ---
    function calculatePlusOneMonth(dateStr) {
        if (!dateStr) return '';
        var parts = dateStr.split('-');
        if (parts.length !== 3) return '';
        var year = parseInt(parts[0], 10);
        var month = parseInt(parts[1], 10); // 1 - 12
        var day = parseInt(parts[2], 10);

        var targetMonth = month + 1;
        var targetYear = year;
        if (targetMonth > 12) {
            targetMonth = 1;
            targetYear += 1;
        }

        // Tangani batas hari maksimal dalam bulan target (misal 31 Jan -> 28 Feb)
        var daysInTargetMonth = new Date(targetYear, targetMonth, 0).getDate();
        var targetDay = Math.min(day, daysInTargetMonth);

        var mm = String(targetMonth).padStart(2, '0');
        var dd = String(targetDay).padStart(2, '0');
        return targetYear + '-' + mm + '-' + dd;
    }

    var resignDateInput = document.getElementById('input_resign_date');
    var lastWorkingDateInput = document.getElementById('input_last_working_date');

    if (resignDateInput && lastWorkingDateInput) {
        resignDateInput.addEventListener('change', function () {
            var calculatedDate = calculatePlusOneMonth(this.value);
            if (calculatedDate) {
                lastWorkingDateInput.value = calculatedDate;
            }
        });
    }

    // --- 2. Pemilihan Karyawan (Mode Atasan / Administrator) ---
    var empSelect = document.getElementById('employee_select');
    if (empSelect) {
        // Inisialisasi Select2 jika tersedia
        if (typeof jQuery !== 'undefined' && jQuery.fn && jQuery.fn.select2) {
            $('#employee_select').select2({
                placeholder: '-- Ketik / Cari Nama Karyawan --',
                allowClear: false,
                width: '100%'
            });
        }

        function populateEmployeeFields() {
            var select = document.getElementById('employee_select');
            if (!select) return;
            var selectedOpt = select.options[select.selectedIndex];
            if (selectedOpt && selectedOpt.value) {
                var name = selectedOpt.getAttribute('data-name') || '';
                var pos = selectedOpt.getAttribute('data-position') || '';
                var div = selectedOpt.getAttribute('data-division') || '';
                var dept = selectedOpt.getAttribute('data-department') || '';
                var nik = selectedOpt.getAttribute('data-nik') || '-';
                var joinDate = selectedOpt.getAttribute('data-join-date') || '';

                var nameInput = document.getElementById('input_nama');
                var posInput = document.getElementById('input_jabatan');
                var divInput = document.getElementById('input_divisi');
                var deptInput = document.getElementById('input_departemen');
                var nikBadge = document.getElementById('selected_nik_badge');
                var joinDateInput = document.getElementById('input_join_date');

                if (nameInput) nameInput.value = name;
                if (posInput) posInput.value = pos;
                if (divInput) divInput.value = div;
                if (deptInput) deptInput.value = dept;
                if (nikBadge) nikBadge.innerHTML = 'NIK: ' + nik;
                if (joinDateInput && joinDate) {
                    joinDateInput.value = joinDate;
                }
            }
        }

        empSelect.addEventListener('change', populateEmployeeFields);
        if (typeof jQuery !== 'undefined') {
            $('#employee_select').on('select2:select', function () {
                populateEmployeeFields();
            });
        }

        @if(!old('Join_Date'))
            populateEmployeeFields();
        @endif
    }
});
</script>
@endsection
