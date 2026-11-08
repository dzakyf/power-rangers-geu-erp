<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

$LSP = Auth::user()->Location ?? '';
$Saya = Auth::user()->username ?? 'SYSTEM';
$priv = Auth::user()->Priv_ID ?? '';

if (!isset($recruitment)) {
    $id = $_GET['ID'] ?? null;
    $recruitment = DB::table('recruitment')->where('ID', $id)->first();
}
if (!isset($requisition) && $recruitment) {
    $requisition = DB::table('requisition')->where('Task_ID', $recruitment->Requisition_ID)->first();
}

// Normalisasi kolom opsional yang mungkin belum ada di tabel recruitment
if ($recruitment) {
    foreach (['MCU_Result', 'MCU_Schedule', 'MCU_Note', 'MCU_File', 'Salary_Expectation', 'Salary_Accepted', 'Position', 'Start_Date', 'Offering_Note', 'Education_Major', 'Major', 'Phone', 'Handphone', 'Birth_Place', 'Birth_Date', 'Gender', 'Last_Education', 'Created_Date', 'Status', 'Offering_Number', 'Offering_File', 'Offering_Signed_Date', 'Grading', 'Work_Location', 'Contract_Type', 'Direct_Supervisor', 'Working_Schedule', 'Salary_Base', 'Salary_Cola', 'Salary_Allowance', 'Salary_Period_Note', 'Salary_Details_Json', 'Benefit_Description', 'Offering_Terms', 'NIK'] as $col) {
        if (!property_exists($recruitment, $col)) {
            $recruitment->$col = null;
        }
    }
}

// Dapatkan posisi default dari Requisition Job_Position yang dihubungkan ke master_position
$defaultPosition = $recruitment->Position ?? '';
if (empty($defaultPosition) && $requisition && !empty($requisition->Job_Position)) {
    // Requisition Job_Position bisa berupa nama Position atau ID master_position
    if (is_numeric($requisition->Job_Position)) {
        $mp = DB::table('master_position')->where('ID', (int) $requisition->Job_Position)->first();
        $defaultPosition = $mp ? $mp->Position : (string) $requisition->Job_Position;
    } else {
        $mp = DB::table('master_position')->where('Position', $requisition->Job_Position)->first();
        $defaultPosition = $mp ? $mp->Position : $requisition->Job_Position;
    }
}
if (empty($defaultPosition) && !empty($recruitment->Position)) {
    $defaultPosition = $recruitment->Position;
}

// Master daftar lokasi kerja dari tabel location
$locationList = DB::table('location')
    ->orderBy('Name', 'ASC')
    ->get(['Location_ID', 'Location_Code', 'Name', 'City']);

$defaultSalary = $recruitment->Salary_Accepted ?? ($recruitment->Salary_Expectation ?? ($detail->Salary_Expectation ?? ''));
$defaultStartDate = $recruitment->Start_Date ?? date('Y-m-d', strtotime('+7 days'));
$defaultNote = $recruitment->Offering_Note ?? '';

// Auto generate saran NIK, Hak Akses Default, dan Atasan Langsung
$onboardingService = app(\App\Services\HCGA\RecruitmentOnboardingService::class);
$suggestedNik = $recruitment->NIK ?? $onboardingService->generateNik($defaultStartDate);
$suggestedAccess = $onboardingService->resolvePrivilegeAndRole($defaultPosition, $requisition->Directorat ?? ($recruitment->Directorat ?? null), $requisition->Department ?? ($recruitment->Department ?? null));
$allRoles = \App\Role::orderBy('name', 'ASC')->pluck('name');

// Deteksi Atasan Langsung (Direct Supervisor) otomatis jika belum diisi manual di kolom Direct_Supervisor
$resolvedSupervisor = $onboardingService->resolveDirectSupervisor($defaultPosition, $requisition->Department ?? ($recruitment->Department ?? null));
$defaultDirectSupervisor = !empty($recruitment->Direct_Supervisor) ? $recruitment->Direct_Supervisor : $resolvedSupervisor['display'] ?? ($resolvedSupervisor['supervisor_title'] ?? 'Head');

// Default komponen penawaran mengikuti template resmi Offering Letter
$defaultGrading = $recruitment->Grading ?? '11';
$defaultWorkLocation = $recruitment->Work_Location ?? ($recruitment->Location ?? ($requisition->Location ?? 'Malang'));
$defaultContractType = $recruitment->Contract_Type ?? 'Kontrak 1 tahun dengan masa evaluasi kerja 3 bulan';

// Deteksi Hari dan Jam Kerja otomatis dari Requisition Work_Schedule -> work_calendars
$resolvedSchedule = $onboardingService->resolveWorkingSchedule($requisition->Work_Schedule ?? null);
$defaultWorkingSchedule = !empty($recruitment->Working_Schedule) ? $recruitment->Working_Schedule : $resolvedSchedule['schedule_text'] ?? "Ketentuan hari dan jam sebagai berikut:\nKaryawan akan bekerja 6 (enam) hari kerja dalam 1 (satu) minggu dengan sistem roster dan shift.\nWaktu dan jadwal kerja Karyawan ditetapkan oleh Departemen masing-masing sesuai kebutuhan operasional Perusahaan.\nApabila dibutuhkan, bersedia untuk masuk kerja pada hari libur nasional sesuai dengan kebutuhan Perusahaan (karena nature bisnis & operasional perusahaan yang mengharuskan demikian).";

$defaultSalaryBase = (float) ($recruitment->Salary_Base ?? ($defaultSalary ?: 1000000));
$defaultSalaryCola = (float) ($recruitment->Salary_Cola ?? 250000);
$defaultSalaryAllowance = (float) ($recruitment->Salary_Allowance ?? 250000);
if ($defaultSalary && $defaultSalaryBase == $defaultSalary && $defaultSalaryCola == 250000 && $defaultSalaryAllowance == 250000) {
    // Sesuaikan proporsi bila gaji total diisi
    if ($defaultSalary > 500000) {
        $defaultSalaryBase = $defaultSalary - 500000;
    }
}

$defaultSalaryPeriodNote = $recruitment->Salary_Period_Note ?? 'Periode Penggajian 26 sd 25 (bulan ke bulan) dibayarkan tgl 1-5 (bulan berjalan)';

// Komponen rincian dinamis awal jika ada disimpan di JSON
$savedSalaryDetails = [];
if (!empty($recruitment->Salary_Details_Json)) {
    $savedSalaryDetails = json_decode($recruitment->Salary_Details_Json, true) ?: [];
}
if (empty($savedSalaryDetails)) {
    $savedSalaryDetails = [['label' => 'Gaji Pokok', 'amount' => $defaultSalaryBase, 'is_removable' => false], ['label' => 'Cost Of Living Allowance (COLA)', 'amount' => $defaultSalaryCola, 'is_removable' => true], ['label' => 'Tunjangan Operasional / Jabatan', 'amount' => $defaultSalaryAllowance, 'is_removable' => true]];
}

$defaultBenefit = $recruitment->Benefit_Description ?? "Benefit :\nUang Lembur\nBPJS Kesehatan & Ketenagakerjaan sesuai Peraturan Pemerintah.\nTHR Setelah 12 bulan masa kerja";
$defaultTerms = $recruitment->Offering_Terms ?? "Dokumen Offering Letter ini bersifat sementara namun mengikat kedua belah pihak. Apabila membatalkan perjanjian maka yang bersangkutan akan dikenakan denda 1x total salary, sebagai kompensasi biaya proses rekrutmen.\nPT. Green Energi Utama berhak memperpanjang masa kontrak dan menghentikan masa kontrak sesuai dengan kebutuhan perusahaan.";
?>

@if (!$recruitment)
    <div class="alert alert-warning d-flex align-items-center shadow-sm">
        <i class="fa fa-triangle-exclamation me-2 fs-18"></i>
        <span>Data kandidat Offering tidak ditemukan atau telah diproses.</span>
    </div>
@else
    @php
        $statusUpper = strtoupper((string) ($recruitment->Status ?? ''));
        $statusBadgeClass = 'bg-info text-white';
        if (in_array($statusUpper, ['HIRED', 'ACCEPT', 'APPROVED', 'COMPLETED'])) {
            $statusBadgeClass = 'bg-success text-white';
        } elseif (in_array($statusUpper, ['REJECT', 'REJECTED', 'OFFER DECLINED', 'DECLINED'])) {
            $statusBadgeClass = 'bg-danger text-white';
        } elseif (in_array($statusUpper, ['OFFERING', 'PENDING', 'WAITING'])) {
            $statusBadgeClass = 'bg-warning text-dark';
        }

        $age = '-';
        if (!empty($recruitment->Birth_Date) && $recruitment->Birth_Date !== '0000-00-00') {
            $timeBirth = strtotime($recruitment->Birth_Date);
            if ($timeBirth > 0) {
                $age = (string) floor((time() - $timeBirth) / 31556926) . ' Tahun';
            }
        }
    @endphp

    <div class="card shadow-sm border mb-4" id="OfferingDetailCard" data-candidate-id="{{ $recruitment->ID }}"
        data-task-id="{{ $recruitment->Task_ID }}" data-candidate-name="{{ $recruitment->Name }}">
        {{-- Card Header --}}
        <div
            class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom">
            <div class="d-flex align-items-center">
                <span class="avatar avatar-md bg-success-transparent text-success rounded-circle me-3">
                    <i class="fa fa-handshake fs-18"></i>
                </span>
                <div>
                    <h5 class="mb-0 fw-bold text-dark">
                        Kandidat #{{ $recruitment->Task_ID }} &mdash; <span
                            class="text-primary">{{ $recruitment->Name }}</span>
                    </h5>
                    <small class="text-muted">
                        Posisi: <strong class="text-dark">{{ $defaultPosition ?: '-' }}</strong> &bull; Terdaftar:
                        {{ $recruitment->Created_Date ?: '-' }}
                    </small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge {{ $statusBadgeClass }} px-3 py-2 fs-12">
                    <i class="fa fa-file-contract me-1"></i> {{ $recruitment->Status ?: 'STAGE OFFERING' }}
                </span>
            </div>
        </div>

        {{-- Quick Action / Alert Bar --}}
        <div class="bg-light p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center">
                <i class="fa fa-file-signature text-success me-2 fs-16"></i>
                <span class="fw-semibold text-dark small">Penerbitan Surat Penawaran Kerja (Offering Letter):</span>
                <span class="badge bg-white text-dark border ms-2 small">
                    Ekspektasi Gaji Awal: <strong>Rp
                        {{ !empty($recruitment->Salary_Expectation) ? number_format((float) $recruitment->Salary_Expectation, 0, ',', '.') : '-' }}</strong>
                </span>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm quick-action-btn"
                    onclick="$('#divlistRecruitment').html('');">
                    <i class="fa fa-xmark me-1"></i> Tutup
                </button>
            </div>
        </div>

        <div class="card-body p-4">
            {{-- SECTION 1: Biodata & Riwayat Evaluasi Lengkap (2-Column Grid) --}}
            <div class="row g-3 mb-4">
                {{-- Kolom Kiri: Biodata --}}
                <div class="col-lg-6">
                    <div class="border rounded p-3 h-100 bg-white shadow-sm">
                        <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                            <i class="fa fa-id-card me-2"></i>Informasi Biodata Kandidat
                        </h6>
                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <th class="text-muted fw-semibold" style="width: 155px;">No. Task ID</th>
                                    <td class="fw-bold text-dark">: {{ $recruitment->Task_ID }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-semibold">Nama Lengkap</th>
                                    <td class="fw-bold text-primary">: {{ $recruitment->Name }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-semibold">Jenis Kelamin</th>
                                    <td>: <span
                                            class="badge bg-light text-dark border">{{ $recruitment->Gender ?: '-' }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-semibold">Tempat, Tgl Lahir</th>
                                    <td>: {{ $recruitment->Birth_Place ?: '-' }},
                                        {{ $recruitment->Birth_Date ?: '-' }} <small
                                            class="text-muted">({{ $age }})</small></td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-semibold">Pendidikan Terakhir</th>
                                    <td>: <span class="fw-semibold">{{ $recruitment->Last_Education ?: '-' }}</span>
                                        &bull; {{ $recruitment->Education_Major ?? ($recruitment->Major ?? '-') }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-semibold">Kontak HP / Telepon</th>
                                    <td>: <i
                                            class="fa fa-phone text-muted me-1"></i>{{ $recruitment->Handphone ?: ($recruitment->Phone ?: '-') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Kolom Kanan: Ringkasan Hasil Evaluasi & MCU --}}
                <div class="col-lg-6">
                    <div class="border rounded p-3 h-100 bg-white shadow-sm">
                        <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                            <i class="fa fa-clipboard-check me-2"></i>Ringkasan Hasil Seleksi &amp; Kesehatan
                        </h6>
                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <th class="text-muted fw-semibold" style="width: 155px;">Posisi Dilamar</th>
                                    <td class="fw-bold text-dark">: {{ $defaultPosition ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-semibold">Departemen</th>
                                    <td>: {{ $requisition->Department ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-semibold">Hasil MCU</th>
                                    <td>: <span class="badge bg-success text-white"><i
                                                class="fa fa-heart-pulse me-1"></i>{{ $recruitment->MCU_Result ?: 'Fit to Work' }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-semibold">Tanggal MCU</th>
                                    <td>: {{ $recruitment->MCU_Schedule ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-semibold">Catatan Medis MCU</th>
                                    <td>: <span
                                            class="text-dark">{{ $recruitment->MCU_Note ?: 'Tidak ada catatan khusus.' }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-semibold">Ekspektasi Gaji</th>
                                    <td class="fw-bold text-dark">: Rp
                                        {{ !empty($recruitment->Salary_Expectation) ? number_format((float) $recruitment->Salary_Expectation, 0, ',', '.') : '-' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- SECTION 2: Form Keputusan Penerbitan Offering Letter --}}
            <div class="card border border-primary shadow-sm mb-0" id="cardOfferingDecision">
                <div
                    class="card-header bg-primary text-white py-2 px-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold fs-13 text-white">
                        <i class="fa fa-handshake me-2"></i>Formulir Kompensasi &amp; Penerbitan Offering Letter
                    </span>
                    <span class="badge bg-white text-primary fw-bold">
                        Petugas PIC: {{ $Saya }}
                    </span>
                </div>
                <div class="card-body p-4 bg-white">
                    <form id="formOfferingLetter" onsubmit="return false;">
                        @csrf
                        <input type="hidden" name="id" value="{{ $recruitment->ID }}">

                        <div class="row g-3">
                            {{-- Baris 1: Posisi, Grading, Lokasi Kerja --}}
                            <div class="col-md-4">
                                <label for="offer_position" class="form-label small fw-semibold text-dark">
                                    Posisi Jabatan <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control form-control-sm" name="position"
                                    id="offer_position" value="{{ $defaultPosition }}" required
                                    placeholder="Contoh: Kru Gudang">
                                <div class="form-text fs-11">Nama posisi resmi pada surat penawaran.</div>
                            </div>

                            <div class="col-md-4">
                                <label for="offer_grading" class="form-label small fw-semibold text-dark">
                                    Grading Jabatan
                                </label>
                                <input type="text" class="form-control form-control-sm" name="grading"
                                    id="offer_grading" value="{{ $defaultGrading }}" placeholder="Contoh: 11" readonly>
                                <div class="form-text fs-11">Level grading internal perusahaan.</div>
                            </div>

                            <div class="col-md-4">
                                <label for="offer_work_location" class="form-label small fw-semibold text-dark">
                                    Lokasi Kerja <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-sm" name="work_location"
                                    id="offer_work_location" required>
                                    @php
                                        $locMatched = false;
                                    @endphp
                                    @foreach ($locationList as $loc)
                                        @php
                                            $locValue = $loc->Name ?: $loc->Location_ID;
                                            $isSelected =
                                                $defaultWorkLocation == $loc->Location_ID ||
                                                $defaultWorkLocation == $loc->Name ||
                                                $defaultWorkLocation == $loc->Location_Code;
                                            if ($isSelected) {
                                                $locMatched = true;
                                            }
                                        @endphp
                                        <option value="{{ $locValue }}" {{ $isSelected ? 'selected' : '' }}>
                                            {{ $loc->Location_ID }} - {{ $loc->Name }}
                                            {{ $loc->City ? "({$loc->City})" : '' }}
                                        </option>
                                    @endforeach
                                    @if (!$locMatched && !empty($defaultWorkLocation))
                                        <option value="{{ $defaultWorkLocation }}" selected>
                                            {{ $defaultWorkLocation }} (Custom)
                                        </option>
                                    @endif
                                </select>
                                <div class="form-text fs-11">Pilih penempatan unit / site kerja resmi dari master
                                    lokasi.</div>
                            </div>

                            {{-- Baris 2: Status Kontrak, Pelaporan Langsung, Join Date --}}
                            <div class="col-md-4">
                                <label for="offer_contract_type" class="form-label small fw-semibold text-dark">
                                    Status Kekaryawanan <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control form-control-sm" name="contract_type"
                                    id="offer_contract_type" value="{{ $defaultContractType }}" required
                                    placeholder="Contoh: Kontrak 1 tahun masa evaluasi 3 bulan">
                                <div class="form-text fs-11">Status ikatan kerja awal.</div>
                            </div>

                            <div class="col-md-4">
                                <label for="offer_direct_supervisor" class="form-label small fw-semibold text-dark">
                                    Pelaporan Langsung (Atasan) <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control form-control-sm" name="direct_supervisor"
                                    id="offer_direct_supervisor" value="{{ $defaultDirectSupervisor }}" required
                                    placeholder="Contoh: Warehouse Leader / Manager">
                                <div class="form-text fs-11">
                                    @if (empty($recruitment->Direct_Supervisor) &&
                                            !empty($resolvedSupervisor['source']) &&
                                            $resolvedSupervisor['source'] !== 'DEFAULT')
                                        <span class="text-success"><i class="fa fa-info-circle me-1"></i>Otomatis
                                            terdeteksi dari
                                            {{ $resolvedSupervisor['source'] === 'MASTER_POSITION' ? 'Bagan Jabatan (master_position)' : ($resolvedSupervisor['source'] === 'ORGANIZATION_STRUCTURE' ? 'Struktur Organisasi (organization_structure)' : 'Kepala Departemen') }}</span>
                                    @else
                                        Jabatan atasan langsung karyawan.
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label for="offer_start_date" class="form-label small fw-semibold text-dark">
                                    Tanggal Mulai Bekerja (Join Date) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light"><i
                                            class="fa fa-calendar-check text-muted"></i></span>
                                    <input type="date" class="form-control" name="start_date"
                                        id="offer_start_date" value="{{ $defaultStartDate }}" required>
                                </div>
                                <div class="form-text fs-11">Tanggal hari pertama bergabung (Onboarding).</div>
                            </div>

                            {{-- Baris 3: Komponen Penggajian Rinci Dinamis --}}
                            <div class="col-12">
                                <div class="p-3 bg-white border rounded shadow-xs">
                                    <div
                                        class="d-flex flex-wrap justify-content-between align-items-center mb-3 border-bottom pb-2 gap-2">
                                        <div>
                                            <h6 class="fw-bold text-dark mb-1 fs-14">
                                                <i class="fa fa-money-bill-wave text-success me-2"></i>Rincian
                                                Penggajian (Salary Breakdown)
                                            </h6>
                                            <div class="text-muted small">Kelola komponen gaji pokok, tunjangan, dan
                                                insentif secara fleksibel.</div>
                                        </div>
                                        <button type="button" class="btn btn-outline-success btn-sm px-3 shadow-sm"
                                            onclick="addSalaryRow()">
                                            <i class="fa fa-plus me-1"></i> Tambah Komponen Gaji
                                        </button>
                                    </div>

                                    {{-- Keterangan Periode Penggajian (Default & Dapat Diedit) --}}
                                    <div class="mb-3">
                                        <label for="offer_salary_period_note"
                                            class="form-label small fw-semibold text-dark">
                                            Keterangan Periode Penggajian &amp; Pembayaran (Bisa Diedit)
                                        </label>
                                        <input type="text" class="form-control form-control-sm"
                                            name="salary_period_note" id="offer_salary_period_note"
                                            value="{{ $defaultSalaryPeriodNote }}"
                                            placeholder="Contoh: Periode Penggajian 26 sd 25 (bulan ke bulan) dibayarkan tgl 1-5 (bulan berjalan)">
                                        <div class="form-text fs-11">Teks ini akan tercantum persis di bawah judul
                                            "Penggajian" pada cetakan surat offering.</div>
                                    </div>

                                    {{-- Container Baris-Baris Komponen Penggajian Dinamis --}}
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm align-middle mb-2"
                                            id="tableSalaryBreakdown">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 50%;">Nama Komponen Penggajian</th>
                                                    <th style="width: 38%;">Nominal (Rp)</th>
                                                    <th style="width: 12%; text-align: center;">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody id="salaryRowsContainer">
                                                @foreach ($savedSalaryDetails as $idx => $sRow)
                                                    <tr class="salary-item-row" data-index="{{ $idx }}">
                                                        <td>
                                                            <input type="text"
                                                                class="form-control form-control-sm salary-label-input"
                                                                name="salary_details[{{ $idx }}][label]"
                                                                value="{{ $sRow['label'] ?? '' }}"
                                                                placeholder="Contoh: Gaji Pokok / Tunjangan Makan"
                                                                required>
                                                        </td>
                                                        <td>
                                                            <div class="input-group input-group-sm">
                                                                <span class="input-group-text">Rp</span>
                                                                <input type="number" min="0" step="1000"
                                                                    class="form-control form-control-sm salary-amount-input text-end"
                                                                    name="salary_details[{{ $idx }}][amount]"
                                                                    value="{{ (float) ($sRow['amount'] ?? 0) }}"
                                                                    placeholder="0" oninput="calcTotalSalary()"
                                                                    required>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            @if (!empty($sRow['is_removable']))
                                                                <button type="button"
                                                                    class="btn btn-outline-danger btn-xs btn-sm"
                                                                    onclick="removeSalaryRow(this)"
                                                                    title="Hapus Baris">
                                                                    <i class="fa fa-trash"></i>
                                                                </button>
                                                            @else
                                                                <span
                                                                    class="badge bg-light text-muted border">Utama</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="table-light">
                                                <tr>
                                                    <td class="text-end fw-bold fs-13">Total Salary (Gross):</td>
                                                    <td>
                                                        <div class="input-group input-group-sm">
                                                            <span
                                                                class="input-group-text fw-bold text-primary">Rp</span>
                                                            <input type="number" min="0" step="1000"
                                                                class="form-control form-control-sm fw-bold bg-white text-primary text-end fs-14"
                                                                name="salary" id="offer_salary"
                                                                value="{{ $defaultSalary ?: $defaultSalaryBase + $defaultSalaryCola + $defaultSalaryAllowance }}"
                                                                readonly>
                                                        </div>
                                                    </td>
                                                    <td class="text-center text-muted small fw-semibold">Gross / bln
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    {{-- Hidden kompatibilitas dengan kolom lama --}}
                                    <input type="hidden" name="salary_base" id="offer_salary_base"
                                        value="{{ $defaultSalaryBase }}">
                                    <input type="hidden" name="salary_cola" id="offer_salary_cola"
                                        value="{{ $defaultSalaryCola }}">
                                    <input type="hidden" name="salary_allowance" id="offer_salary_allowance"
                                        value="{{ $defaultSalaryAllowance }}">
                                </div>
                            </div>

                            {{-- Baris 4: Hari dan Jam Kerja & Benefit (Free Text lapang) --}}
                            <div class="col-md-6">
                                <label for="offer_working_schedule" class="form-label small fw-semibold text-dark">
                                    Hari dan Jam Kerja
                                </label>
                                <textarea class="form-control" rows="5" name="working_schedule" id="offer_working_schedule"
                                    placeholder="Ketentuan jadwal shift, hari libur, atau waktu kerja...">{{ $defaultWorkingSchedule }}</textarea>
                                <div class="form-text fs-11">
                                    @if (empty($recruitment->Working_Schedule) && !empty($resolvedSchedule['calendar_name']))
                                        <span class="text-success"><i class="fa fa-calendar-check me-1"></i>Otomatis
                                            diambil dari Requisition Work Schedule:
                                            <strong>{{ $resolvedSchedule['calendar_name'] }}</strong></span>
                                    @else
                                        Bebas diedit sesuai ketentuan operasional / shift kerja departemen.
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="offer_benefit_description" class="form-label small fw-semibold text-dark">
                                    Kompensasi &amp; Benefit Lainnya
                                </label>
                                <textarea class="form-control" rows="5" name="benefit_description" id="offer_benefit_description"
                                    placeholder="Rincian benefit seperti lembur, BPJS Kesehatan & Ketenagakerjaan, THR...">{{ $defaultBenefit }}</textarea>
                                <div class="form-text fs-11">Rincian benefit dan fasilitas tambahan.</div>
                            </div>

                            {{-- Baris 5: Ketentuan & Denda Pembatalan (Free Text lapang) --}}
                            <div class="col-12">
                                <label for="offer_offering_terms" class="form-label small fw-semibold text-dark">
                                    Ketentuan Offering Letter &amp; Ikatan Perjanjian
                                </label>
                                <textarea class="form-control" rows="4" name="offering_terms" id="offer_offering_terms"
                                    placeholder="Ketentuan ikatan perjanjian, denda pembatalan (misal 1x total salary), atau kebijakan evaluasi kontrak...">{{ $defaultTerms }}</textarea>
                                <div class="form-text fs-11">Ketentuan hukum, sanksi pembatalan sepihak, dan masa
                                    evaluasi kerja.</div>
                            </div>

                            <div class="col-12">
                                <label for="offer_note" class="form-label small fw-semibold text-dark">
                                    Catatan Penawaran &amp; Fasilitas Tambahan (Internal HR)
                                </label>
                                <textarea class="form-control" rows="3" name="note" id="offer_note"
                                    placeholder="Tuliskan catatan internal HR, fasilitas seragam, akomodasi, masa probation...">{{ $defaultNote }}</textarea>
                            </div>

                            {{-- SECTION PENGARSIPAN DOKUMEN OFFERING BERTANDATANGAN --}}
                            <div class="col-12 mt-3">
                                <div class="p-3 bg-white border border-success rounded shadow-xs">
                                    <div
                                        class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                                        <h6 class="fw-bold text-success mb-0 fs-13">
                                            <i class="fa fa-file-signature me-2"></i>Pengarsipan Berkas Offering Letter
                                            yang Ditandatangani Kandidat <span class="text-danger">*</span>
                                        </h6>
                                        <span class="badge bg-success-transparent text-success small">Wajib Diunggah
                                            Sebelum HIRED</span>
                                    </div>
                                    <p class="text-muted small mb-3">
                                        Setelah surat penawaran dicetak &amp; ditandatangani basah bermaterai oleh
                                        kandidat, unggah dokumen hasil scan di bawah ini sebagai syarat verifikasi resmi
                                        pengangkatan menjadi karyawan.
                                    </p>

                                    <div class="row g-3 align-items-center">
                                        <div class="col-md-7">
                                            <div class="input-group input-group-sm">
                                                <input type="file" class="form-control"
                                                    id="signed_offering_upload" accept=".pdf,.jpg,.jpeg,.png,.zip"
                                                    onchange="uploadSignedOffering(this)">
                                                <button class="btn btn-outline-secondary" type="button"
                                                    onclick="$('#signed_offering_upload').val(''); $('#offer_signed_file').val(''); $('#fileSignedOfferingPreview').hide();">
                                                    <i class="fa fa-rotate-left"></i> Reset
                                                </button>
                                            </div>
                                            <div class="form-text fs-11">Format: PDF, JPG, PNG (Maks 10MB).</div>
                                            <input type="hidden" name="offering_file" id="offer_signed_file"
                                                value="{{ $recruitment->Offering_File }}">
                                        </div>
                                        <div class="col-md-5">
                                            <div id="fileSignedOfferingPreview"
                                                style="{{ empty($recruitment->Offering_File) ? 'display:none;' : '' }}">
                                                <span
                                                    class="badge bg-light text-dark border p-2 d-inline-flex align-items-center gap-2">
                                                    <i class="fa fa-circle-check text-success fs-14"></i>
                                                    <a href="{{ asset($recruitment->Offering_File ?: '') }}"
                                                        id="linkSignedOffering" target="_blank"
                                                        class="text-primary fw-bold text-truncate"
                                                        style="max-width: 200px;">
                                                        Lihat Dokumen Terunggah
                                                    </a>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- SECTION KHUSUS ONBOARDING & HAK AKSES KARYAWAN --}}
                            <div class="col-12 mt-3">
                                <div class="p-3 bg-light border rounded">
                                    <h6 class="fw-bold text-dark mb-2 fs-13">
                                        <i class="fa fa-user-plus text-primary me-2"></i>Konfigurasi Data Karyawan
                                        &amp; Hak Akses (Onboarding Otomatis)
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label for="offer_nik" class="form-label small fw-semibold text-dark">
                                                Nomor Induk Karyawan (NIK) <span class="text-danger">*</span>
                                            </label>
                                            <input type="text"
                                                class="form-control form-control-sm fw-bold text-primary"
                                                name="nik" id="offer_nik" value="{{ $suggestedNik }}" required
                                                placeholder="Contoh: 2026110001">
                                            <div class="form-text fs-11">NIK resmi untuk data Karyawan &amp; username
                                                akun login.</div>
                                        </div>

                                        <div class="col-md-4">
                                            <label for="offer_priv_id" class="form-label small fw-semibold text-dark">
                                                Level Akses (Priv_ID) <span class="text-danger">*</span>
                                            </label>
                                            <input type="text"
                                                class="form-control form-control-sm text-uppercase fw-semibold"
                                                name="priv_id" id="offer_priv_id"
                                                value="{{ $suggestedAccess['priv_id'] }}" required
                                                placeholder="STAFF, HEAD, MANAGER, VP">
                                            <div class="form-text fs-11">Otomatis dipetakan dari
                                                {{ $suggestedAccess['matched_by'] }}.</div>
                                        </div>

                                        <div class="col-md-4">
                                            <label for="offer_role" class="form-label small fw-semibold text-dark">
                                                Role Hak Akses Menu
                                            </label>
                                            <select class="form-select form-select-sm" name="role"
                                                id="offer_role">
                                                @foreach ($allRoles as $r)
                                                    <option value="{{ $r }}"
                                                        {{ $r === $suggestedAccess['default_role'] ? 'selected' : '' }}>
                                                        {{ $r }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="form-text fs-11">Role RBAC untuk mengontrol menu ERP karyawan.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="border-top pt-3 d-flex flex-wrap align-items-center justify-content-between gap-2 mt-4">
                            <span class="text-muted small">
                                <i class="fa fa-circle-info text-info me-1"></i> Menerbitkan Offering Letter akan
                                otomatis membuat nomor surat dan menetapkan status kandidat menjadi
                                <strong>HIRED</strong>.
                            </span>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm fw-semibold"
                                    onclick="saveDraftOffering()">
                                    <i class="fa fa-floppy-disk me-1"></i> Simpan Draft
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm px-3 shadow-sm"
                                    onclick="previewOfferingPdf()">
                                    <i class="fa fa-file-pdf me-1"></i> Preview PDF Live
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm px-3"
                                    onclick="$('#divlistRecruitment').html('');">
                                    <i class="fa fa-xmark me-1"></i> Batal
                                </button>
                                <button type="button" class="btn btn-danger btn-sm px-3 fw-bold shadow-sm"
                                    onclick="prosesDeclineOffering({{ $recruitment->ID }})">
                                    <i class="fa fa-xmark-circle me-1"></i> Kandidat Menolak Tawaran
                                </button>
                                <button type="button"
                                    class="btn btn-success btn-sm px-4 text-white fw-bold shadow-sm"
                                    onclick="prosesIssueOffering()">
                                    <i class="fa fa-check-double me-1"></i> Terbitkan Offering &amp; Nyatakan HIRED
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        var salaryRowCounter = {{ count($savedSalaryDetails) + 10 }};

        function addSalaryRow() {
            salaryRowCounter++;
            var html = '<tr class="salary-item-row" data-index="' + salaryRowCounter + '">' +
                '<td>' +
                '<input type="text" class="form-control form-control-sm salary-label-input" name="salary_details[' +
                salaryRowCounter + '][label]" placeholder="Nama Komponen (misal: Insentif Shift)" required>' +
                '</td>' +
                '<td>' +
                '<div class="input-group input-group-sm">' +
                '<span class="input-group-text">Rp</span>' +
                '<input type="number" min="0" step="1000" class="form-control form-control-sm salary-amount-input text-end" name="salary_details[' +
                salaryRowCounter + '][amount]" value="0" placeholder="0" oninput="calcTotalSalary()" required>' +
                '</div>' +
                '</td>' +
                '<td class="text-center">' +
                '<button type="button" class="btn btn-outline-danger btn-xs btn-sm" onclick="removeSalaryRow(this)" title="Hapus Baris">' +
                '<i class="fa fa-trash"></i>' +
                '</button>' +
                '</td>' +
                '</tr>';

            $('#salaryRowsContainer').append(html);
        }

        function removeSalaryRow(btn) {
            $(btn).closest('tr').remove();
            calcTotalSalary();
        }

        function calcTotalSalary() {
            var total = 0;
            var base = 0;
            var cola = 0;
            var allowance = 0;

            $('#salaryRowsContainer tr.salary-item-row').each(function(i) {
                var label = ($(this).find('.salary-label-input').val() || '').trim().toLowerCase();
                var amount = parseFloat($(this).find('.salary-amount-input').val()) || 0;
                total += amount;

                if (i === 0 || label.indexOf('pokok') !== -1) {
                    base = amount;
                } else if (label.indexOf('cola') !== -1 || label.indexOf('living') !== -1) {
                    cola += amount;
                } else {
                    allowance += amount;
                }
            });

            $('#offer_salary').val(total);
            $('#offer_salary_base').val(base);
            $('#offer_salary_cola').val(cola);
            $('#offer_salary_allowance').val(allowance);
        }

        function saveDraftOffering() {
            ensureSwalOffering(_saveDraftOffering);
        }

        function _saveDraftOffering() {
            calcTotalSalary();
            var formData = $('#formOfferingLetter').serialize();

            Swal.fire({
                title: 'Menyimpan Draft...',
                text: 'Menyimpan seluruh input form offering dan berkas terunggah...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "{{ url('hrd-recruitment-save-draft-offering') }}",
                type: "POST",
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val()
                },
                success: function(res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Draft Tersimpan!',
                        text: res.message ||
                            'Draft form offering letter berhasil disimpan. Anda dapat melanjutkan sewaktu-waktu atau langsung mencetak PDF.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menyimpan draft';
                    Swal.fire('Gagal Menyimpan', msg, 'error');
                }
            });
        }

        function previewOfferingPdf() {
            calcTotalSalary();
            var formData = $('#formOfferingLetter').serialize();
            var url = "{{ url('hrd-recruitment-offering-letter-pdf') }}?" + formData;
            window.open(url, '_blank');
        }

        function uploadSignedOffering(input) {
            if (!input.files || !input.files[0]) return;
            var file = input.files[0];

            if (file.size > 10 * 1024 * 1024) {
                alert('Ukuran file maksimal 10 MB!');
                input.value = '';
                return;
            }

            var formData = new FormData();
            formData.append('file', file);

            ensureSwalOffering(function() {
                Swal.fire({
                    title: 'Mengunggah Berkas...',
                    text: 'Sedang mengunggah berkas offering letter bertandatangan...',
                    allowOutsideClick: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: "{{ url('upload-rekrut-offering.php') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        Swal.close();
                        if (res.state && res.name) {
                            $('#offer_signed_file').val(res.name);
                            $('#linkSignedOffering').attr('href', "{{ asset('') }}" + res.name);
                            $('#fileSignedOfferingPreview').show();
                            Swal.fire('Berhasil',
                                'Berkas offering letter bertandatangan berhasil diunggah!',
                                'success');
                        } else {
                            Swal.fire('Gagal', res.message || 'Gagal mengunggah berkas.', 'error');
                            input.value = '';
                        }
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire('Gagal', 'Terjadi kesalahan saat mengunggah berkas.', 'error');
                        input.value = '';
                    }
                });
            });
        }

        // Pastikan SweetAlert2 tersedia (layout Adminor tidak memuatnya secara global)
        function ensureSwalOffering(cb) {
            if (typeof Swal !== 'undefined' && Swal.fire) {
                cb();
                return;
            }
            if (window.__swal2Loading) {
                window.__swal2Loading.push(cb);
                return;
            }
            window.__swal2Loading = [cb];
            var s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
            s.onload = function() {
                var q = window.__swal2Loading || [];
                window.__swal2Loading = null;
                q.forEach(function(f) {
                    f();
                });
            };
            s.onerror = function() {
                window.__swal2Loading = null;
                alert('Gagal memuat SweetAlert2. Periksa koneksi internet lalu coba lagi.');
            };
            document.head.appendChild(s);
        }

        function prosesIssueOffering() {
            ensureSwalOffering(_prosesIssueOffering);
        }

        function prosesDeclineOffering(id) {
            ensureSwalOffering(function() {
                _prosesDeclineOffering(id);
            });
        }

        function _prosesIssueOffering() {
            var salary = $('#offer_salary').val();
            var position = $('#offer_position').val();
            var startDate = $('#offer_start_date').val();
            var signedFile = $('#offer_signed_file').val();

            if (!signedFile) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Berkas Offering Belum Diunggah',
                        text: 'Harap unggah terlebih dahulu scan berkas Offering Letter yang telah disetujui & ditandatangani oleh kandidat sebelum melanjutkan pengangkatan menjadi karyawan (HIRED).'
                    });
                } else {
                    alert(
                        'Harap unggah terlebih dahulu scan berkas Offering Letter yang telah ditandatangani oleh kandidat!'
                    );
                }
                return;
            }

            if (!salary || !position || !startDate) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Perhatian', 'Harap lengkapi Posisi, Gaji yang disepakati, dan Tanggal Join Date!',
                        'warning');
                } else if (typeof showAppNotification === 'function') {
                    showAppNotification('Perhatian', 'Harap lengkapi Posisi, Gaji yang disepakati, dan Tanggal Join Date!',
                        'warning');
                } else {
                    alert('Harap lengkapi Posisi, Gaji yang disepakati, dan Tanggal Join Date!');
                }
                return;
            }

            Swal.fire({
                title: 'Terbitkan Offering Letter & Angkat Karyawan?',
                text: 'Kandidat akan resmi diterima (HIRED), didaftarkan ke data Karyawan (employment), Bagan Organisasi, serta akun login ERP langsung aktif.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Terbitkan & HIRED',
                cancelButtonText: 'Batal'
            }).then((res) => {
                if (res.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses Onboarding...',
                        text: 'Menerbitkan surat penawaran, mendaftarkan ke tabel employment & akun login...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: "{{ url('hrd-recruitment-issue-offering-letter') }}",
                        type: "POST",
                        data: $('#formOfferingLetter').serialize(),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $(
                                'input[name="_token"]').val()
                        },
                        success: function(response) {
                            var d = response.data || {};
                            var infoHtml = (response.message ||
                                    'Offering Letter berhasil diterbitkan!') +
                                '<hr class="my-2">' +
                                '<div class="text-start fs-13">' +
                                '<p class="mb-1"><strong>Nomor Surat:</strong> ' + (d
                                    .offering_letter_number || '-') + '</p>' +
                                '<p class="mb-1"><strong>NIK Karyawan:</strong> <span class="badge bg-primary text-white">' +
                                (d.nik || '-') + '</span></p>' +
                                '<p class="mb-1"><strong>Username Login:</strong> <code>' + (d
                                    .username || '-') +
                                '</code> (Password: <code>12345678</code>)</p>' +
                                '<p class="mb-0"><strong>Hak Akses:</strong> Priv_ID: <strong>' + (d
                                    .priv_id || '-') + '</strong> | Role: <strong>' + (d.role || '-') +
                                '</strong></p>' +
                                '</div>';

                            Swal.fire({
                                icon: 'success',
                                title: 'Onboarding Berhasil!',
                                html: infoHtml
                            }).then(() => {
                                $('#divlistRecruitment').html('');
                                if (typeof RecruitmentListsRefresh === 'function') {
                                    RecruitmentListsRefresh();
                                } else if (typeof refreshdata === 'function') {
                                    refreshdata();
                                }
                            });
                        },
                        error: function(xhr) {
                            var msg = xhr.responseJSON ? xhr.responseJSON.message :
                                'Terjadi kesalahan sistem';
                            Swal.fire('Gagal', msg, 'error');
                        }
                    });
                }
            });
        }

        function _prosesDeclineOffering(id) {
            Swal.fire({
                title: 'Kandidat Menolak Penawaran?',
                text: 'Masukkan alasan penolakan offering letter oleh kandidat:',
                input: 'textarea',
                inputPlaceholder: 'Tuliskan alasan penolakan (misal: negosiasi gaji tidak tercapai, memilih tempat lain)...',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Tandai Menolak',
                cancelButtonText: 'Batal',
                inputValidator: (value) => {
                    if (!value) {
                        return 'Alasan penolakan wajib diisi!';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Menyimpan status penolakan penawaran...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: "{{ url('hrd-recruitment-decline-offering') }}",
                        type: "POST",
                        data: {
                            _token: $('input[name="_token"]').val(),
                            id: id,
                            reason: result.value
                        },
                        success: function(res) {
                            Swal.fire('Offering Ditolak', res.message ||
                                'Status kandidat diubah menjadi OFFER DECLINED.', 'info').then(
                                () => {
                                    $('#divlistRecruitment').html('');
                                    if (typeof RecruitmentListsRefresh === 'function') {
                                        RecruitmentListsRefresh();
                                    } else if (typeof refreshdata === 'function') {
                                        refreshdata();
                                    }
                                });
                        },
                        error: function(xhr) {
                            var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan';
                            Swal.fire('Gagal', msg, 'error');
                        }
                    });
                }
            });
        }
    </script>
@endif
