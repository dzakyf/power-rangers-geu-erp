<?php
error_reporting(0);
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\RecruitmentFlowService;

$LSP = Auth::user()->Location ?? '';
$Saya = Auth::user()->username ?? 'SYSTEM';

// Di employee detail, ID yang dilempar adalah ID dari tabel employment
$empId = $_GET['ID'] ?? request('ID');
$employee = DB::table('employment')->where('ID', $empId)->first();

$recruitment = null;
if ($employee) {
    // 1. Coba cari berdasarkan KTP jika ada
    if (!empty($employee->KTP)) {
        $recruitment = DB::table('recruitment')->where('KTP', $employee->KTP)->first();
    }
    // 2. Jika belum ketemu, coba cari berdasarkan Task_ID / Registration_Status
    if (!$recruitment && !empty($employee->Registration_Status)) {
        $recruitment = DB::table('recruitment')
            ->where('Task_ID', $employee->Registration_Status)
            ->orWhere('ID', $employee->Registration_Status)
            ->first();
    }
    // 3. Jika belum ketemu, coba cari berdasarkan nama yang sama persis
    if (!$recruitment && !empty($employee->Name)) {
        $recruitment = DB::table('recruitment')->where('Name', $employee->Name)->first();
    }
    // 4. Jika masih belum ketemu, coba cari dari Task_ID employment
    if (!$recruitment && !empty($employee->Task_ID)) {
        $recruitment = DB::table('recruitment')->where('Task_ID', $employee->Task_ID)->first();
    }
}

// Fallback jika ID yang masuk ternyata langsung ID recruitment
if (!$recruitment) {
    $recruitment = DB::table('recruitment')->where('ID', $empId)->first();
}

$id = $recruitment->ID ?? $empId;

if ($recruitment && !empty($recruitment->Requisition_ID)) {
    $requisition = DB::table('requisition')->where('Task_ID', $recruitment->Requisition_ID)->first();
} else {
    $requisition = null;
}

$recruitmentDetail = null;
if ($recruitment && !empty($recruitment->Task_ID)) {
    if (\Illuminate\Support\Facades\Schema::hasTable('recruitment_detail')) {
        $recruitmentDetail = DB::table('recruitment_detail')
            ->where('ID', $recruitment->ID)
            ->orWhere('Task_ID', $recruitment->Task_ID)
            ->first();
    }
}

// Data Normalization
$statusUpper = strtoupper((string) ($recruitment->Status ?? 'HIRED'));

// Stage Rank & Progression
$stageRank = [
    'CREATED'          => 1,
    'PENDING'          => 1,
    'UPLOADED'         => 1,
    'VERIFICATION'     => 1,
    'SEEK'             => 1,
    'PSIKOTEST'        => 2,
    'INTERVIEW_HR'     => 3,
    'INTERVIEWED'      => 4,
    'INTERVIEW_USER'   => 4,
    'BUSINESS_REVIEW'  => 5,
    'TECHNICAL_TEST'   => 5,
    'MCU'              => 6,
    'OFFERING'         => 7,
    'HIRED'            => 8,
    'CLOSED'           => 8,
];
$currRank = $stageRank[$statusUpper] ?? 8;

// Status Flags per Pipeline
$passAdm   = ($recruitment && $recruitment->Document_Status === 'ACCEPT');
$passPsi   = ($recruitment && $recruitment->Psikotest_Status === 'ACCEPT');
$passHr    = ($recruitment && $recruitment->Interview_Status === 'ACCEPT');
$passUser  = ($recruitment && $recruitment->User_Interview_Status === 'ACCEPT');
$passTech  = ($recruitment && $recruitment->Technical_Test_Status === 'ACCEPT');
$passMcu   = ($recruitment && $recruitment->MCU_Status === 'ACCEPT');
$passOffer = ($recruitment && ($recruitment->Offering_Status === 'ACCEPT' || in_array($statusUpper, ['HIRED', 'CLOSED'])));

// Sentuh (touch) tahapan
$touchAdm   = true;
$touchPsi   = $passAdm || !empty($recruitment->Psikotest_Status) || !empty($recruitment->Psikotest_Date) || $currRank >= 2;
$touchHr    = ($passAdm && $passPsi) || !empty($recruitment->Interview_Status) || !empty($recruitment->Interview_Date) || $currRank >= 3;
$touchUser  = ($passAdm && $passPsi && $passHr) || !empty($recruitment->User_Interview_Status) || !empty($recruitment->User_Interview_Date) || $currRank >= 4;
$touchTech  = ($passAdm && $passPsi && $passHr && $passUser) || !empty($recruitment->Technical_Test_Status) || !empty($recruitment->Technical_Date) || $currRank >= 5;
$touchMcu   = ($passAdm && $passPsi && $passHr && $passUser && $passTech) || !empty($recruitment->MCU_Status) || !empty($recruitment->MCU_Schedule) || $currRank >= 6;
$touchOffer = ($passAdm && $passPsi && $passHr && $passUser && $passTech && $passMcu) || !empty($recruitment->Offering_Status) || $passOffer || $currRank >= 7;

// Helpers Badge Function (hanya render jika belum ada)
if (!function_exists('renderEmpPipelineBadge')) {
    function renderEmpPipelineBadge($isPassed, $isTouch, $statusText = '') {
        if ($isPassed) {
            return '<span class="badge bg-success text-white px-2 py-1"><i class="fa fa-check-circle me-1"></i>' . ($statusText ?: 'Lolos') . '</span>';
        } elseif ($isTouch) {
            if (in_array(strtoupper((string)$statusText), ['REJECT', 'REJECTED', 'UNFIT', 'DECLINED', 'TIDAK LULUS'])) {
                return '<span class="badge bg-danger text-white px-2 py-1"><i class="fa fa-times-circle me-1"></i>' . $statusText . '</span>';
            }
            return '<span class="badge bg-warning text-dark px-2 py-1"><i class="fa fa-clock me-1"></i>' . ($statusText ?: 'Dalam Proses') . '</span>';
        } else {
            return '<span class="badge bg-light text-muted border px-2 py-1"><i class="fa fa-circle-pause me-1"></i>Menunggu</span>';
        }
    }
}
?>

@include('layout.plugins')
@include('layout.adminor.tab-head')
<script src="{{ asset('blockui.js') }}"></script>
<link rel="stylesheet" type="text/css" href="{{ asset('blockui.css') }}" />

<style>
.rec-flow-step {
    border-radius: 8px;
    padding: 10px 14px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    transition: all 0.2s ease;
    flex: 1 1 120px;
    min-width: 130px;
}
.rec-flow-step.active {
    background: #eef2ff;
    border-color: #6366f1;
    box-shadow: 0 2px 6px rgba(99, 102, 241, 0.15);
}
.rec-flow-step.passed {
    background: #f0fdf4;
    border-color: #86efac;
}
.stage-card {
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    border: 1px solid #e9ecef;
}
.stage-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}
.stage-header-icon {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
}
</style>

<div class="container-fluid py-3">
    @if(!$recruitment && !$employee)
        <div class="alert alert-danger shadow-sm">
            <i class="fa fa-exclamation-triangle me-2"></i> Data rekrutmen / karyawan tidak ditemukan.
        </div>
    @else

    {{-- Detail Subview Container: jika user membuka formulir detail evaluasi dari sini --}}
    <div id="progressDetailSubviewContainer" style="display: none;" class="mb-4">
        <div class="card shadow-sm border mb-3">
            <div class="card-header bg-light py-2 px-3 border-bottom d-flex align-items-center justify-content-between">
                <span class="fw-bold text-dark fs-13" id="progressDetailSubviewTitle">
                    <i class="fa fa-file-text text-primary me-2"></i>Detail Formulir Evaluasi Rekrutmen
                </span>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="closeProgressModuleDetail();" title="Kembali ke Ringkasan Arsip">
                    <i class="fa fa-arrow-left me-1"></i> Kembali ke Ringkasan
                </button>
            </div>
            <div class="card-body p-0" id="progressDetailSubviewContent"></div>
        </div>
    </div>

    {{-- Main Progress Overview Wrapper --}}
    <div id="progressMainOverviewWrapper">
    {{-- Header Banner & Pipeline Progress Flow Tracker --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-primary text-white py-2 px-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="fa fa-archive fs-16"></i>
                <h6 class="mb-0 fw-bold">Recruitment Archive &amp; Pipeline History</h6>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-white text-dark px-2 py-1">
                    Employee: <strong>{{ $employee->NIK ?? ($recruitment->NIK ?? '-') }} - {{ $employee->Name ?? ($recruitment->Name ?? '-') }}</strong>
                </span>
                <span class="badge bg-info text-white px-2 py-1">
                    Status: {{ $recruitment->Status ?? 'HIRED' }}
                </span>
            </div>
        </div>

        <div class="card-body p-3">
            {{-- Horizontal Pipeline Flow --}}
            <div class="d-flex flex-wrap align-items-stretch gap-2 mb-2">
                {{-- 1. Seleksi Berkas --}}
                <div class="rec-flow-step {{ $passAdm ? 'passed' : ($touchAdm ? 'active' : '') }}">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <small class="fw-bold text-muted">1. Berkas</small>
                        <i class="fa {{ $passAdm ? 'fa-check-circle text-success' : 'fa-folder-open text-primary' }}"></i>
                    </div>
                    <div class="fw-bold text-truncate fs-12 text-dark">{{ $recruitment->Document_Status ?? ($passAdm ? 'ACCEPT' : 'SUBMITTED') }}</div>
                    <div class="mt-1">{!! renderEmpPipelineBadge($passAdm, $touchAdm, $recruitment->Document_Status ?? '') !!}</div>
                </div>

                {{-- 2. Psikotest --}}
                <div class="rec-flow-step {{ $passPsi ? 'passed' : ($touchPsi ? 'active' : '') }}">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <small class="fw-bold text-muted">2. Psikotest</small>
                        <i class="fa {{ $passPsi ? 'fa-check-circle text-success' : 'fa-brain text-info' }}"></i>
                    </div>
                    <div class="fw-bold text-truncate fs-12 text-dark">{{ $recruitment->Psikotest_Result ?? ($recruitment->Psikotes_Result ?? 'Selesai') }}</div>
                    <div class="mt-1">{!! renderEmpPipelineBadge($passPsi, $touchPsi, $recruitment->Psikotest_Status ?? ($recruitment->Psikotest_Result ?? '')) !!}</div>
                </div>

                {{-- 3. Interview HR --}}
                <div class="rec-flow-step {{ $passHr ? 'passed' : ($touchHr ? 'active' : '') }}">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <small class="fw-bold text-muted">3. Int. HR</small>
                        <i class="fa {{ $passHr ? 'fa-check-circle text-success' : 'fa-comments text-primary' }}"></i>
                    </div>
                    <div class="fw-bold text-truncate fs-12 text-dark">
                        {{ isset($recruitment->Interview_Result) && $recruitment->Interview_Result !== null ? 'Skor: '.$recruitment->Interview_Result : ($recruitment->Interview_Status ?? 'Selesai') }}
                    </div>
                    <div class="mt-1">{!! renderEmpPipelineBadge($passHr, $touchHr, $recruitment->Interview_Status ?? ($recruitment->Hasil_Kategori ?? '')) !!}</div>
                </div>

                {{-- 4. Interview User --}}
                <div class="rec-flow-step {{ $passUser ? 'passed' : ($touchUser ? 'active' : '') }}">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <small class="fw-bold text-muted">4. Int. User</small>
                        <i class="fa {{ $passUser ? 'fa-check-circle text-success' : 'fa-user-check text-warning' }}"></i>
                    </div>
                    <div class="fw-bold text-truncate fs-12 text-dark">{{ $recruitment->User_Interview_Status ?? 'Lolos' }}</div>
                    <div class="mt-1">{!! renderEmpPipelineBadge($passUser, $touchUser, $recruitment->User_Interview_Status ?? '') !!}</div>
                </div>

                {{-- 5. Tes Teknik --}}
                <div class="rec-flow-step {{ $passTech ? 'passed' : ($touchTech ? 'active' : '') }}">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <small class="fw-bold text-muted">5. Tes Teknik</small>
                        <i class="fa {{ $passTech ? 'fa-check-circle text-success' : 'fa-laptop-code text-secondary' }}"></i>
                    </div>
                    <div class="fw-bold text-truncate fs-12 text-dark">
                        {{ isset($recruitment->Technical_Score) && $recruitment->Technical_Score !== null ? 'Skor: '.$recruitment->Technical_Score : ($recruitment->Technical_Test_Status ?? '-') }}
                    </div>
                    <div class="mt-1">{!! renderEmpPipelineBadge($passTech, $touchTech, $recruitment->Technical_Test_Status ?? '') !!}</div>
                </div>

                {{-- 6. MCU --}}
                <div class="rec-flow-step {{ $passMcu ? 'passed' : ($touchMcu ? 'active' : '') }}">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <small class="fw-bold text-muted">6. MCU</small>
                        <i class="fa {{ $passMcu ? 'fa-check-circle text-success' : 'fa-notes-medical text-danger' }}"></i>
                    </div>
                    <div class="fw-bold text-truncate fs-12 text-dark">{{ $recruitment->MCU_Result ?? 'FIT TO WORK' }}</div>
                    <div class="mt-1">{!! renderEmpPipelineBadge($passMcu, $touchMcu, $recruitment->MCU_Status ?? ($recruitment->MCU_Result ?? '')) !!}</div>
                </div>

                {{-- 7. Offering & Hired --}}
                <div class="rec-flow-step {{ $passOffer ? 'passed' : ($touchOffer ? 'active' : '') }}">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <small class="fw-bold text-muted">7. Offering</small>
                        <i class="fa {{ $passOffer ? 'fa-check-circle text-success' : 'fa-file-signature text-success' }}"></i>
                    </div>
                    <div class="fw-bold text-truncate fs-12 text-dark">{{ $recruitment->Offering_Status ?? 'HIRED' }}</div>
                    <div class="mt-1">{!! renderEmpPipelineBadge($passOffer, $touchOffer, $recruitment->Offering_Status ?? 'HIRED') !!}</div>
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center justify-content-between pt-2 border-top mt-2">
                <small class="text-muted">
                    <i class="fa fa-info-circle text-primary me-1"></i>
                    Posisi: <strong>{{ $employee->Position ?? ($requisition->Job_Position ?? ($recruitment->Position ?? '-')) }}</strong> 
                    &bull; Departemen: <strong>{{ $employee->Department ?? ($recruitment->Department ?? '-') }}</strong>
                    &bull; PO / Ref: <strong>{{ $employee->Contract_No ?? ($requisition->PO_No ?? ($recruitment->Reference_No ?? '-')) }}</strong>
                </small>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.location.reload();">
                        <i class="fa fa-refresh me-1"></i> Refresh Data
                    </button>
                    <button type="button" class="btn btn-sm btn-primary px-3" onclick="saveAllProgress();">
                        <i class="fa fa-save me-1"></i> Simpan Catatan Arsip
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Form Master Update Progress / Archive (Tanpa Tombol Action Approval) --}}
    <form id="progressForm">
        @csrf
        <input type="hidden" name="ID" value="{{ $id }}">

        <div class="row g-3">
            {{-- ========================================================== --}}
            {{-- STAGE 1: PSIKOTEST DETAIL CARD                            --}}
            {{-- ========================================================== --}}
            <div class="col-lg-6">
                <div class="card shadow-sm h-100 stage-card">
                    <div class="card-header bg-white py-2 px-3 border-bottom d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="stage-header-icon bg-info text-white">
                                <i class="fa fa-brain"></i>
                            </span>
                            <span class="fw-bold text-dark fs-13">2. Tahap Psikotest</span>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            {!! renderEmpPipelineBadge($passPsi, $touchPsi, $recruitment->Psikotest_Status ?? ($recruitment->Psikotest_Result ?? '')) !!}
                            <button type="button" class="btn btn-xs btn-outline-primary ms-1" onclick="openModuleDetail('psikotest', '{{ $id }}')" title="Buka Detail Form Psikotest">
                                <i class="fa fa-external-link-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-2 mb-2">
                            <div class="col-md-5">
                                <label class="form-label small fw-bold text-muted mb-1">Skor Psikotest (0-100)</label>
                                <input type="number" step="0.01" min="0" max="100" class="form-control form-control-sm" 
                                    name="Psikotest_Score" value="{{ $recruitment->Psikotest_Score ?? '' }}" placeholder="0 - 100">
                            </div>
                            <div class="col-md-7">
                                <label class="form-label small fw-bold text-muted mb-1">Hasil Evaluasi</label>
                                <input type="text" class="form-control form-control-sm" name="Psikotes_Result" 
                                    value="{{ $recruitment->Psikotes_Result ?? ($recruitment->Psikotest_Result ?? '') }}" 
                                    placeholder="Contoh: DISARANKAN / Lulus">
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-bold text-muted mb-1">Catatan / Keterangan Psikotes</label>
                            <textarea name="Psikotest_Note" rows="2" class="form-control form-control-sm" placeholder="Catatan evaluasi psikotes...">{{ $recruitment->Psikotest_Note ?? '' }}</textarea>
                        </div>

                        <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                            <div class="small text-truncate me-2">
                                <i class="fa fa-paperclip text-muted me-1"></i>
                                @if(!empty($recruitment->Psikotest_File))
                                    <a href="{{ asset($recruitment->Psikotest_File) }}" target="_blank" class="text-primary text-decoration-none fw-semibold">
                                        Lihat Lembar Psikotest
                                    </a>
                                @else
                                    <span class="text-muted">Belum ada berkas terunggah</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ========================================================== --}}
            {{-- STAGE 2: INTERVIEW HR DETAIL CARD                         --}}
            {{-- ========================================================== --}}
            <div class="col-lg-6">
                <div class="card shadow-sm h-100 stage-card">
                    <div class="card-header bg-white py-2 px-3 border-bottom d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="stage-header-icon bg-primary text-white">
                                <i class="fa fa-comments"></i>
                            </span>
                            <span class="fw-bold text-dark fs-13">3. Tahap Interview HR</span>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            {!! renderEmpPipelineBadge($passHr, $touchHr, $recruitment->Interview_Status ?? ($recruitment->Hasil_Kategori ?? '')) !!}
                            <button type="button" class="btn btn-xs btn-outline-primary ms-1" onclick="openModuleDetail('interview-hr', '{{ $id }}')" title="Buka Detail Form Interview HR">
                                <i class="fa fa-external-link-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted mb-1">Jadwal Interview HR</label>
                                <input type="datetime-local" name="Interview_Schedule" class="form-control form-control-sm"
                                    value="{{ !empty($recruitment->Interview_Schedule) ? date('Y-m-d\TH:i', strtotime($recruitment->Interview_Schedule)) : '' }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted mb-1">Skor (0-100)</label>
                                <input type="number" id="Interview_Result" name="Interview_Result" class="form-control form-control-sm"
                                    min="0" max="100" step="0.01" value="{{ $recruitment->Interview_Result ?? '' }}" placeholder="0 - 100">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted mb-1">Kategori</label>
                                <input type="text" id="Hasil_Kategori" class="form-control form-control-sm bg-white fw-bold" readonly
                                    value="{{ $recruitment->Hasil_Kategori ?? '' }}">
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-bold text-muted mb-1">Pewawancara / Catatan HR</label>
                            <input type="text" class="form-control form-control-sm" value="{{ !empty($recruitment->Interview_By) ? 'Pewawancara: '.$recruitment->Interview_By : 'Belum ditetapkan' }}" readonly>
                        </div>

                        <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                            <span class="small text-muted">
                                Status Keputusan: <strong>{{ $recruitment->Interview_Status ?? 'ACCEPT' }}</strong>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ========================================================== --}}
            {{-- STAGE 3: INTERVIEW USER (CANDIDATE ANALYSIS) CARD         --}}
            {{-- ========================================================== --}}
            <div class="col-lg-6">
                <div class="card shadow-sm h-100 stage-card">
                    <div class="card-header bg-white py-2 px-3 border-bottom d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="stage-header-icon bg-warning text-dark">
                                <i class="fa fa-user-check"></i>
                            </span>
                            <span class="fw-bold text-dark fs-13">4. Tahap Interview User</span>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            {!! renderEmpPipelineBadge($passUser, $touchUser, $recruitment->User_Interview_Status ?? '') !!}
                            <button type="button" class="btn btn-xs btn-outline-primary ms-1" onclick="openModuleDetail('interview-user', '{{ $id }}')" title="Buka Detail Form Interview User">
                                <i class="fa fa-external-link-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted mb-1">Jadwal Interview User</label>
                                <input type="datetime-local" name="User_Interview_Schedule" class="form-control form-control-sm"
                                    value="{{ !empty($recruitment->User_Interview_Schedule) ? date('Y-m-d\TH:i', strtotime($recruitment->User_Interview_Schedule)) : '' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted mb-1">Keputusan User</label>
                                <input type="text" class="form-control form-control-sm bg-light fw-semibold" 
                                    value="{{ $recruitment->User_Interview_Status ?? 'ACCEPT' }}" readonly>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-bold text-muted mb-1">Catatan Evaluasi User</label>
                            <textarea name="User_Interview_Note" rows="2" class="form-control form-control-sm" placeholder="Catatan atau masukan dari atasan/user...">{{ $recruitment->User_Interview_Note ?? '' }}</textarea>
                        </div>

                        <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                            <div class="small text-truncate me-2">
                                <i class="fa fa-user-tie text-muted me-1"></i>
                                User: <strong>{{ $recruitment->User_Interview_By ?? ($requisition->User_ID ?? 'Atasan Divisi') }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ========================================================== --}}
            {{-- STAGE 4: TES TEKNIK / PRAKTIK DETAIL CARD                 --}}
            {{-- ========================================================== --}}
            <div class="col-lg-6">
                <div class="card shadow-sm h-100 stage-card">
                    <div class="card-header bg-white py-2 px-3 border-bottom d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="stage-header-icon bg-secondary text-white">
                                <i class="fa fa-laptop-code"></i>
                            </span>
                            <span class="fw-bold text-dark fs-13">5. Tahap Tes Teknik</span>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            {!! renderEmpPipelineBadge($passTech, $touchTech, $recruitment->Technical_Test_Status ?? '') !!}
                            <button type="button" class="btn btn-xs btn-outline-primary ms-1" onclick="openModuleDetail('technical-test', '{{ $id }}')" title="Buka Detail Form Tes Teknik">
                                <i class="fa fa-external-link-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-2 mb-2">
                            <div class="col-md-5">
                                <label class="form-label small fw-bold text-muted mb-1">Skor Tes Teknik (0-100)</label>
                                <input type="number" step="0.01" min="0" max="100" class="form-control form-control-sm" 
                                    name="Technical_Score" value="{{ $recruitment->Technical_Score ?? ($recruitment->Test_Score ?? '') }}" placeholder="0 - 100">
                            </div>
                            <div class="col-md-7">
                                <label class="form-label small fw-bold text-muted mb-1">Status Tes Teknik</label>
                                <input type="text" class="form-control form-control-sm bg-light fw-semibold" 
                                    value="{{ $recruitment->Technical_Test_Status ?? ($recruitment->Test_Result ?? 'ACCEPT') }}" readonly>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-bold text-muted mb-1">Catatan / Komentar Penguji</label>
                            <textarea name="Technical_Note" rows="2" class="form-control form-control-sm" placeholder="Catatan keterampilan praktik/teknis...">{{ $recruitment->Technical_Note ?? ($recruitment->Test_Note ?? '') }}</textarea>
                        </div>

                        <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                            <div class="small text-truncate me-2">
                                <i class="fa fa-paperclip text-muted me-1"></i>
                                @php $techFile = $recruitment->Technical_File ?? ''; @endphp
                                @if(!empty($techFile))
                                    <a href="{{ asset($techFile) }}" target="_blank" class="text-primary text-decoration-none fw-semibold">
                                        Lihat Berkas Hasil Uji
                                    </a>
                                @else
                                    <span class="text-muted">Belum ada file uji diunggah</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ========================================================== --}}
            {{-- STAGE 5: MEDICAL CHECK UP (MCU) CARD                      --}}
            {{-- ========================================================== --}}
            <div class="col-lg-6">
                <div class="card shadow-sm h-100 stage-card">
                    <div class="card-header bg-white py-2 px-3 border-bottom d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="stage-header-icon bg-danger text-white">
                                <i class="fa fa-medkit"></i>
                            </span>
                            <span class="fw-bold text-dark fs-13">6. Tahap Medical Check-Up (MCU)</span>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            {!! renderEmpPipelineBadge($passMcu, $touchMcu, $recruitment->MCU_Status ?? ($recruitment->MCU_Result ?? '')) !!}
                            <button type="button" class="btn btn-xs btn-outline-primary ms-1" onclick="openModuleDetail('mcu', '{{ $id }}')" title="Buka Detail Form MCU">
                                <i class="fa fa-external-link-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted mb-1">Jadwal MCU</label>
                                <input type="datetime-local" name="MCU_Schedule" class="form-control form-control-sm"
                                    value="{{ !empty($recruitment->MCU_Schedule) ? date('Y-m-d\TH:i', strtotime($recruitment->MCU_Schedule)) : '' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted mb-1">Hasil MCU</label>
                                <input type="text" name="MCU_Result" class="form-control form-control-sm"
                                    placeholder="Contoh: FIT TO WORK / UNFIT"
                                    value="{{ $recruitment->MCU_Result ?? 'FIT TO WORK' }}">
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-bold text-muted mb-1">Catatan Kesehatan / Laboratorium</label>
                            <textarea name="MCU_Note" rows="2" class="form-control form-control-sm" placeholder="Catatan hasil lab / kelayakan medis...">{{ $recruitment->MCU_Note ?? '' }}</textarea>
                        </div>

                        <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                            <div class="small text-truncate me-2">
                                <i class="fa fa-paperclip text-muted me-1"></i>
                                @if(!empty($recruitment->MCU_File))
                                    <a href="{{ asset($recruitment->MCU_File) }}" target="_blank" class="text-primary text-decoration-none fw-semibold">
                                        Lihat Lembar Hasil MCU
                                    </a>
                                @else
                                    <span class="text-muted">Belum ada file MCU diunggah</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ========================================================== --}}
            {{-- STAGE 6: OFFERING & HIRED CARD                            --}}
            {{-- ========================================================== --}}
            <div class="col-lg-6">
                <div class="card shadow-sm h-100 stage-card">
                    <div class="card-header bg-white py-2 px-3 border-bottom d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="stage-header-icon bg-success text-white">
                                <i class="fa fa-file-text-o"></i>
                            </span>
                            <span class="fw-bold text-dark fs-13">7. Tahap Offering &amp; Onboarding</span>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            {!! renderEmpPipelineBadge($passOffer, $touchOffer, $recruitment->Offering_Status ?? 'HIRED') !!}
                            <button type="button" class="btn btn-xs btn-outline-primary ms-1" onclick="openModuleDetail('offering', '{{ $id }}')" title="Buka Detail Form Offering">
                                <i class="fa fa-external-link-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted mb-1">Gaji Disepakati (Rp)</label>
                                <input type="number" name="Offering_Salary" class="form-control form-control-sm"
                                    value="{{ $recruitment->Salary_Accepted ?? ($recruitment->Salary_Expectation ?? '') }}" placeholder="Contoh: 6500000">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted mb-1">Tanggal Mulai Bekerja</label>
                                <input type="date" name="Offering_Start_Date" class="form-control form-control-sm"
                                    value="{{ !empty($recruitment->Start_Date) ? date('Y-m-d', strtotime($recruitment->Start_Date)) : (!empty($employee->Start_Date) ? date('Y-m-d', strtotime($employee->Start_Date)) : '') }}">
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-bold text-muted mb-1">Catatan Penawaran / Offering</label>
                            <textarea name="Offering_Note" rows="2" class="form-control form-control-sm" placeholder="Benefit, masa percobaan, atau catatan offering...">{{ $recruitment->Offering_Note ?? '' }}</textarea>
                        </div>

                        <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                            <div class="small">
                                Status Offering: <strong>{{ $recruitment->Offering_Status ?? 'HIRED' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================== --}}
        {{-- SECTION KUESIONER PRA-WAWANCARA & ANALISIS PELAMAR        --}}
        {{-- ========================================================== --}}
        <div class="card shadow-sm border mt-3" id="cardKuesionerPelamar">
            <div class="card-header bg-white py-2 px-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="stage-header-icon bg-primary text-white">
                        <i class="fa fa-question-circle"></i>
                    </span>
                    <div>
                        <span class="fw-bold text-dark fs-13 d-block">
                            Kuesioner Pra-Wawancara &amp; Profil Mendalam Rekrutmen
                        </span>
                        <small class="text-muted">Data kuesioner pelamar dari formulir Interview HR &amp; Interview User (Candidate Analysis)</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-muted border px-2 py-1">
                        <i class="fa fa-database me-1"></i> Data Isian Pelamar
                    </span>
                    <button type="button" class="btn btn-xs btn-outline-secondary" onclick="$('#collapseKuesionerBody').slideToggle(200);">
                        <i class="fa fa-chevron-down"></i>
                    </button>
                </div>
            </div>

            <div class="card-body p-3" id="collapseKuesionerBody" style="display:none;">
                {{-- Nav Tabs Kuesioner --}}
                <ul class="nav nav-tabs nav-tabs-bordered mb-3" id="kuesionerTabProgress" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-semibold fs-12" id="prog-tab-motivasi" data-bs-toggle="tab" data-bs-target="#prog-content-motivasi" type="button" role="tab" aria-controls="prog-content-motivasi" aria-selected="true">
                            <i class="fa fa-compass me-1 text-primary"></i> Motivasi &amp; Pengalaman
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold fs-12" id="prog-tab-karakter" data-bs-toggle="tab" data-bs-target="#prog-content-karakter" type="button" role="tab" aria-controls="prog-content-karakter" aria-selected="false">
                            <i class="fa fa-cogs me-1 text-warning"></i> Karakteristik &amp; Keahlian
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold fs-12" id="prog-tab-preferensi" data-bs-toggle="tab" data-bs-target="#prog-content-preferensi" type="button" role="tab" aria-controls="prog-content-preferensi" aria-selected="false">
                            <i class="fa fa-sliders me-1 text-success"></i> Preferensi Kerja &amp; Ekspektasi
                        </button>
                    </li>
                </ul>

                {{-- Tab Contents --}}
                <div class="tab-content pt-1" id="kuesionerTabProgressContent">
                    {{-- Tab 1: Motivasi & Pengalaman --}}
                    <div class="tab-pane fade show active" id="prog-content-motivasi" role="tabpanel" aria-labelledby="prog-tab-motivasi">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="Describe_Self" class="form-label small fw-semibold text-dark">
                                    1. Ceritakan tentang diri Anda (Describe Yourself)
                                </label>
                                <textarea class="form-control form-control-sm bg-light" id="Describe_Self" name="Describe_Self" rows="3">{{ $recruitmentDetail->Describe_Self ?? '' }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="Left_Job" class="form-label small fw-semibold text-dark">
                                    2. Alasan meninggalkan pekerjaan terakhir (Why you left last job?)
                                </label>
                                <textarea class="form-control form-control-sm bg-light" id="Left_Job" name="Left_Job" rows="3">{{ $recruitmentDetail->Left_Job ?? '' }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="Previous_Boss" class="form-label small fw-semibold text-dark">
                                    3. Pandangan tentang atasan terdahulu (Think about previous boss?)
                                </label>
                                <textarea class="form-control form-control-sm bg-light" id="Previous_Boss" name="Previous_Boss" rows="3">{{ $recruitmentDetail->Previous_Boss ?? '' }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="Describe_Know" class="form-label small fw-semibold text-dark">
                                    4. Pengetahuan tentang perusahaan (What you know about our company?)
                                </label>
                                <textarea class="form-control form-control-sm bg-light" id="Describe_Know" name="Describe_Know" rows="3">{{ $recruitmentDetail->Describe_Know ?? '' }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="Describe_Improve" class="form-label small fw-semibold text-dark">
                                    5. Upaya pengembangan keahlian (What did you do to improve knowledge?)
                                </label>
                                <textarea class="form-control form-control-sm bg-light" id="Describe_Improve" name="Describe_Improve" rows="3">{{ $recruitmentDetail->Describe_Improve ?? '' }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="Describe_Looking" class="form-label small fw-semibold text-dark">
                                    6. Hal yang dicari pada posisi ini (What looking for in position?)
                                </label>
                                <textarea class="form-control form-control-sm bg-light" id="Describe_Looking" name="Describe_Looking" rows="3">{{ $recruitmentDetail->Describe_Looking ?? '' }}</textarea>
                            </div>
                            <div class="col-12">
                                <label for="Describe_Interest" class="form-label small fw-semibold text-dark">
                                    7. Alasan tertarik bergabung (Why interested to work here?)
                                </label>
                                <textarea class="form-control form-control-sm bg-light" id="Describe_Interest" name="Describe_Interest" rows="2">{{ $recruitmentDetail->Describe_Interest ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Tab 2: Karakteristik & Keahlian --}}
                    <div class="tab-pane fade" id="prog-content-karakter" role="tabpanel" aria-labelledby="prog-tab-karakter">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="Describe_Strong" class="form-label small fw-semibold text-dark">
                                    <i class="fa fa-trophy text-success me-1"></i> Kelebihan / Kekuatan Utama (Strengths)
                                </label>
                                <textarea class="form-control form-control-sm bg-light" id="Describe_Strong" name="Describe_Strong" rows="3">{{ $recruitmentDetail->Describe_Strong ?? '' }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="Describe_Weak" class="form-label small fw-semibold text-dark">
                                    <i class="fa fa-exclamation-triangle text-warning me-1"></i> Kelemahan Terbesar &amp; Solusi (Weaknesses)
                                </label>
                                <textarea class="form-control form-control-sm bg-light" id="Describe_Weak" name="Describe_Weak" rows="3">{{ $recruitmentDetail->Describe_Weak ?? '' }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="Computer" class="form-label small fw-semibold text-dark">
                                    <i class="fa fa-laptop me-1 text-primary"></i> Keahlian Komputer &amp; Software
                                </label>
                                <textarea class="form-control form-control-sm bg-light" id="Computer" name="Computer" rows="3">{{ $recruitmentDetail->Computer ?? '' }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="Free_Time" class="form-label small fw-semibold text-dark">
                                    <i class="fa fa-coffee text-info me-1"></i> Waktu Luang / Hobi (Leisure Time)
                                </label>
                                <textarea class="form-control form-control-sm bg-light" id="Free_Time" name="Free_Time" rows="3">{{ $recruitmentDetail->Free_Time ?? '' }}</textarea>
                            </div>
                            <div class="col-12">
                                <label for="Questions_Us" class="form-label small fw-semibold text-dark">
                                    <i class="fa fa-question-circle text-secondary me-1"></i> Pertanyaan Kandidat untuk Perusahaan (Do you have any questions?)
                                </label>
                                <textarea class="form-control form-control-sm bg-light" id="Questions_Us" name="Questions_Us" rows="2">{{ $recruitmentDetail->Questions_Us ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Tab 3: Preferensi Kerja & Ekspektasi --}}
                    <div class="tab-pane fade" id="prog-content-preferensi" role="tabpanel" aria-labelledby="prog-tab-preferensi">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="Salary_Expectation" class="form-label small fw-semibold text-dark">
                                    Ekspektasi Gaji Pelamar (Salary Expectation)
                                </label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light fw-bold">Rp</span>
                                    <input type="text" class="form-control" id="Salary_Expectation" name="Salary_Expectation" 
                                        value="{{ $recruitmentDetail->Salary_Expectation ?? ($recruitment->Salary_Expectation ?? '') }}" placeholder="Contoh: 5.000.000" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="Facility_Expectation" class="form-label small fw-semibold text-dark">
                                    Ekspektasi Fasilitas Lainnya
                                </label>
                                <input type="text" class="form-control form-control-sm" id="Facility_Expectation" name="Facility_Expectation" 
                                    value="{{ $recruitmentDetail->Facility_Expectation ?? '' }}" placeholder="Contoh: BPJS, Transport, Laptop" />
                            </div>
                            <div class="col-md-3">
                                <label for="Environment" class="form-label small fw-semibold text-dark">
                                    Lingkungan Kerja Favorit
                                </label>
                                <select class="form-select form-select-sm" id="Environment" name="Environment">
                                    @php $envVal = $recruitmentDetail->Environment ?? 'OFFICE'; @endphp
                                    <option value="OFFICE" {{ $envVal === 'OFFICE' ? 'selected' : '' }}>OFFICE</option>
                                    <option value="FIELD" {{ $envVal === 'FIELD' ? 'selected' : '' }}>FIELD</option>
                                    <option value="FACTORY" {{ $envVal === 'FACTORY' ? 'selected' : '' }}>FACTORY</option>
                                    <option value="WORKSHOP" {{ $envVal === 'WORKSHOP' ? 'selected' : '' }}>WORKSHOP</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="Overtime" class="form-label small fw-semibold text-dark">
                                    Bersedia Kerja Lembur?
                                </label>
                                <select class="form-select form-select-sm" id="Overtime" name="Overtime">
                                    @php $otVal = strtoupper((string) ($recruitmentDetail->Overtime ?? 'YES')); @endphp
                                    <option value="YES" {{ $otVal === 'YES' ? 'selected' : '' }}>YES (Bersedia)</option>
                                    <option value="NO" {{ $otVal === 'NO' ? 'selected' : '' }}>NO (Tidak)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="Holiday" class="form-label small fw-semibold text-dark">
                                    Bersedia Kerja Hari Libur?
                                </label>
                                <select class="form-select form-select-sm" id="Holiday" name="Holiday">
                                    @php $holVal = strtoupper((string) ($recruitmentDetail->Holiday ?? 'YES')); @endphp
                                    <option value="YES" {{ $holVal === 'YES' ? 'selected' : '' }}>YES (Bersedia)</option>
                                    <option value="NO" {{ $holVal === 'NO' ? 'selected' : '' }}>NO (Tidak)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="Shift_Work" class="form-label small fw-semibold text-dark">
                                    Nyaman Bekerja Shift?
                                </label>
                                <select class="form-select form-select-sm" id="Shift_Work" name="Shift_Work">
                                    @php $shiftVal = strtoupper((string) ($recruitmentDetail->Shift_Work ?? 'YES')); @endphp
                                    <option value="YES" {{ $shiftVal === 'YES' ? 'selected' : '' }}>YES (Bersedia)</option>
                                    <option value="NO" {{ $shiftVal === 'NO' ? 'selected' : '' }}>NO (Tidak)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="Out_Town" class="form-label small fw-semibold text-dark">
                                    Bersedia Dinas Luar Kota?
                                </label>
                                <select class="form-select form-select-sm" id="Out_Town" name="Out_Town">
                                    @php $outVal = strtoupper((string) ($recruitmentDetail->Out_Town ?? 'YES')); @endphp
                                    <option value="YES" {{ $outVal === 'YES' ? 'selected' : '' }}>YES (Bersedia)</option>
                                    <option value="NO" {{ $outVal === 'NO' ? 'selected' : '' }}>NO (Tidak)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="Describe_Long" class="form-label small fw-semibold text-dark">
                                    Target Lama Bekerja
                                </label>
                                <input type="text" class="form-control form-control-sm" id="Describe_Long" name="Describe_Long" 
                                    value="{{ $recruitmentDetail->Describe_Long ?? '' }}" placeholder="Contoh: > 3 Tahun" />
                            </div>
                            <div class="col-md-4">
                                <label for="Describe_Target" class="form-label small fw-semibold text-dark">
                                    Rencana Karir 5 Tahun Kedepan
                                </label>
                                <input type="text" class="form-control form-control-sm" id="Describe_Target" name="Describe_Target" 
                                    value="{{ $recruitmentDetail->Describe_Target ?? '' }}" placeholder="Contoh: Team Leader / Supervisor" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer Action Bar --}}
        <div class="card shadow-sm border-0 mt-3">
            <div class="card-body py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="text-muted small">
                    <i class="fa fa-clock text-secondary me-1"></i>
                    Terakhir dimodifikasi: <strong>{{ $recruitment->Modified_Date ?? ($recruitment->Created_Date ?? '-') }}</strong> 
                    oleh <strong>{{ $recruitment->Modified_By ?? ($recruitment->Created_By ?? 'System') }}</strong>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-primary px-4 shadow-sm" onclick="saveAllProgress();">
                        <i class="fa fa-save me-1"></i> Simpan Catatan Arsip
                    </button>
                </div>
            </div>
        </div>
    </form>
    </div>{{-- End #progressMainOverviewWrapper --}}
    @endif
</div>

<script>
function updateKategori() {
    var score = parseFloat($('#Interview_Result').val());
    if (!isNaN(score)) {
        $('#Hasil_Kategori').val(score < 70 ? 'TIDAK LULUS' : 'LULUS');
        if (score < 70) {
            $('#Hasil_Kategori').removeClass('text-success').addClass('text-danger');
        } else {
            $('#Hasil_Kategori').removeClass('text-danger').addClass('text-success');
        }
    } else {
        $('#Hasil_Kategori').val('').removeClass('text-success text-danger');
    }
}

$('#Interview_Result').on('input change', updateKategori);
updateKategori();

// Simpan form all progress via AJAX
function saveAllProgress() {
    if (typeof $.blockUI === 'function') {
        $.blockUI({ message: '<div class="p-2"><i class="fa fa-spinner fa-spin me-2"></i>Menyimpan catatan arsip rekrutmen...</div>' });
    }
    var formData = $('#progressForm').serialize();
    $.ajax({
        url: '{{ url("/recruitment-lists-detail-progress-update") }}',
        type: 'POST',
        data: formData,
        complete: function() { 
            if (typeof $.unblockUI === 'function') $.unblockUI(); 
        },
        success: function(res) {
            if (res.status === 'success') {
                alert('Seluruh catatan evaluasi dan kuesioner arsip berhasil diperbarui.');
                if (res.hasil_kategori) {
                    $('#Hasil_Kategori').val(res.hasil_kategori);
                    updateKategori();
                }
            } else {
                alert(res.message || 'Terjadi kesalahan saat menyimpan data progress.');
            }
        },
        error: function(xhr) {
            var msg = 'Gagal menyimpan data arsip.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            }
            alert(msg);
        }
    });
}

// Buka form evaluasi modul detail penuh (Psikotest, Interview HR, User, dsb.) di dalam tab
function openModuleDetail(moduleType, candidateId) {
    var urlMap = {
        'psikotest': '{{ url("hrd-recruitment-psikotest-detail") }}?ID=' + candidateId,
        'interview-hr': '{{ url("recruitment-interview-detail") }}?ID=' + candidateId,
        'interview-user': '{{ url("recruitment-candidate-analysis-detail") }}?ID=' + candidateId,
        'technical-test': '{{ url("hrd-recruitment-technical-test-detail") }}?ID=' + candidateId,
        'mcu': '{{ url("hrd-recruitment-mcu-detail") }}?ID=' + candidateId,
        'offering': '{{ url("hrd-recruitment-offering-detail") }}?ID=' + candidateId
    };

    var titleMap = {
        'psikotest': 'Formulir Hasil Psikotes',
        'interview-hr': 'Formulir Evaluasi Wawancara HR',
        'interview-user': 'Formulir Evaluasi Wawancara User (Candidate Analysis)',
        'technical-test': 'Formulir Hasil Uji Kompetensi / Tes Teknik',
        'mcu': 'Formulir Pemeriksaan Kesehatan (MCU)',
        'offering': 'Formulir Penawaran Kerja (Offering Letter)'
    };

    var targetUrl = urlMap[moduleType];
    if (!targetUrl) return;

    if (typeof $.blockUI === 'function') {
        $.blockUI({ message: '<div class="p-2"><i class="fa fa-spinner fa-spin me-2"></i>Memuat formulir detail...</div>' });
    }

    $.ajax({
        url: targetUrl,
        type: 'GET',
        complete: function() {
            if (typeof $.unblockUI === 'function') $.unblockUI();
        },
        success: function(htmlResponse) {
            // Sembunyikan overview progress & tampilkan subview detail form
            $('#progressMainOverviewWrapper').slideUp(200);
            $('#progressDetailSubviewTitle').html('<i class="fa fa-file-text text-primary me-2"></i>' + (titleMap[moduleType] || 'Detail Formulir Evaluasi Rekrutmen'));
            $('#progressDetailSubviewContent').html(htmlResponse);
            $('#progressDetailSubviewContainer').slideDown(250);

            // Intercept tombol close / batal pada form detail agar HANYA menutup subview dan kembali ke ringkasan
            $('#progressDetailSubviewContent').find('button[onclick*="divlistRecruitment"], button[onclick*="RecruitmentListsCloseDetail"], button.btn-close, .btn-outline-secondary:contains("Tutup"), .btn-outline-secondary:contains("Kembali")').each(function() {
                var btn = $(this);
                btn.removeAttr('onclick');
                btn.off('click').on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    closeProgressModuleDetail();
                });
            });

            $('html, body').animate({ scrollTop: $('#progressDetailSubviewContainer').offset().top - 20 }, 'fast');
        },
        error: function() {
            alert('Gagal memuat formulir detail. Membuka di tab baru...');
            window.open(targetUrl, '_blank');
        }
    });
}

// Tutup subview formulir detail dan kembali ke ringkasan progress
function closeProgressModuleDetail() {
    $('#progressDetailSubviewContainer').slideUp(200, function() {
        $('#progressDetailSubviewContent').empty();
        $('#progressMainOverviewWrapper').slideDown(250);
        $('html, body').animate({ scrollTop: 0 }, 'fast');
    });
}
</script>
