@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<style>
    .verify-card {
        border-radius: 8px;
        transition: all 0.2s ease-in-out;
    }
    .document-box {
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 8px;
        padding: 1rem;
        background: #fff;
        height: 100%;
        transition: all 0.2s;
        border-left: 4px solid #dee2e6;
    }
    .document-box.has-file {
        border-left-color: #0f9d58;
    }
    .document-box.no-file {
        border-left-color: #dc3545;
        background-color: #fffbfa;
    }
    .document-box:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }
    .recruitment-stepper {
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: relative;
    }
    .recruitment-stepper::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 40px;
        right: 40px;
        height: 3px;
        background: #e9ecef;
        z-index: 1;
    }
    .recruitment-step-item {
        position: relative;
        z-index: 2;
        background: #fff;
        text-align: center;
        padding: 0 10px;
    }
    .step-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 8px;
        font-weight: 700;
        font-size: 14px;
        border: 3px solid #dee2e6;
        background: #fff;
        color: #6c757d;
        transition: all 0.2s;
    }
    .step-circle.completed {
        background: #198754;
        border-color: #198754;
        color: #fff;
    }
    .step-circle.active {
        background: #0d6efd;
        border-color: #0d6efd;
        color: #fff;
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.2);
    }
</style>

{{-- Hero Header Adminor V3 --}}
<div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
    <div>
        <h4 class="content-title text-white fw-bold mb-1" style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
            <i class="fa fa-file-circle-check me-2"></i>Verifikasi Dokumen Kandidat
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Human Capital &amp; GA</a></li>
                <li class="breadcrumb-item"><a href="{{ url('hrd-recruitment-management') }}" style="color: rgba(255, 255, 255, 0.8) !important;">Recruitment</a></li>
                <li class="breadcrumb-item"><a href="{{ route('hrd-recruitment-verify-document-list') }}" style="color: rgba(255, 255, 255, 0.8) !important;">Antrean Verifikasi</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page" style="color: #ffffff !important; font-weight: 600;">{{ $recruitment->Name }}</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('hrd-recruitment-verify-document-list') }}" class="btn btn-sm text-white shadow-sm" style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); color: #ffffff !important;">
            <i class="fa fa-list me-1"></i> Antrean Verifikasi
        </a>
        <a href="{{ url('hrd-recruitment-management') }}" class="btn btn-sm text-white shadow-sm" style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); color: #ffffff !important;">
            <i class="fa fa-arrow-left me-1"></i> Dashboard Management
        </a>
    </div>
</div>

{{-- Stepper Progress --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body py-4 px-3">
        <div class="recruitment-stepper">
            <div class="recruitment-step-item">
                <div class="step-circle completed"><i class="fa fa-check"></i></div>
                <div class="fw-bold small text-dark">Tahap 1</div>
                <div class="text-muted" style="font-size: 11px;">Input Candidate</div>
            </div>
            <div class="recruitment-step-item">
                <div class="step-circle active"><i class="fa fa-file-circle-check"></i></div>
                <div class="fw-bold small text-primary">Tahap 2 (Current)</div>
                <div class="text-primary fw-semibold" style="font-size: 11px;">Verifikasi Dokumen</div>
            </div>
            <div class="recruitment-step-item">
                <div class="step-circle">3</div>
                <div class="fw-bold small text-muted">Tahap 3</div>
                <div class="text-muted" style="font-size: 11px;">Psikotest</div>
            </div>
            <div class="recruitment-step-item">
                <div class="step-circle">4</div>
                <div class="fw-bold small text-muted">Tahap 4</div>
                <div class="text-muted" style="font-size: 11px;">Interview HR &amp; User</div>
            </div>
            <div class="recruitment-step-item">
                <div class="step-circle">5</div>
                <div class="fw-bold small text-muted">Tahap 5+</div>
                <div class="text-muted" style="font-size: 11px;">MCU &amp; Offering</div>
            </div>
        </div>
    </div>
</div>

<div id="resultAlert" class="alert alert-dismissible fade show shadow-sm mb-4" style="display: none;"></div>

<div class="row g-4 mb-4">
    {{-- Left: Candidate Profile Card --}}
    <div class="col-lg-5">
        <div class="card shadow-sm border h-100 verify-card">
            <div class="card-header bg-white py-3 px-3 border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark fs-14">
                    <i class="fa fa-user-circle text-primary me-2"></i>Informasi Kandidat
                </span>
                <span class="badge bg-primary text-white px-2 py-1 fs-12">
                    {{ $recruitment->Status }}
                </span>
            </div>
            <div class="card-body p-3">
                <div class="text-center py-2 mb-3 border-bottom pb-3">
                    @php
                        $fotoPath = $recruitment->Foto ?? $documents->Foto ?? null;
                    @endphp
                    @if($fotoPath)
                        <img src="{{ asset($fotoPath) }}" alt="Foto {{ $recruitment->Name }}" 
                             class="rounded-circle shadow-sm border border-3 border-white mb-2" 
                             style="width: 100px; height: 100px; object-fit: cover;">
                    @else
                        <div class="avatar avatar-xxl bg-primary-transparent text-primary rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fa fa-user fs-36"></i>
                        </div>
                    @endif
                    <h5 class="fw-bold mb-0 text-dark">{{ $recruitment->Name }}</h5>
                    <span class="badge bg-light text-muted border mt-1">Task ID: {{ $recruitment->Task_ID }}</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-borderless mb-0 fs-13">
                        <tbody>
                            <tr>
                                <td class="text-muted ps-0" width="130"><i class="fa fa-briefcase text-secondary me-2"></i>Posisi / PTK</td>
                                <td class="fw-semibold text-dark">{{ $recruitment->Position ?? '-' }} <span class="text-muted fw-normal">({{ $recruitment->Requisition_ID ?? '-' }})</span></td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0"><i class="fa fa-venus-mars text-secondary me-2"></i>Gender</td>
                                <td>
                                    <span class="badge {{ $recruitment->Gender === 'MALE' ? 'bg-info text-white' : 'bg-danger text-white' }} px-2 py-1">
                                        {{ $recruitment->Gender ?? '-' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0"><i class="fa fa-calendar-day text-secondary me-2"></i>Lahir</td>
                                <td>{{ $recruitment->Birth_Place ?? '-' }}, {{ $recruitment->Birth_Date ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0"><i class="fa fa-envelope text-secondary me-2"></i>Email</td>
                                <td>
                                    @if($recruitment->Email)
                                        <a href="mailto:{{ $recruitment->Email }}" class="text-primary text-decoration-none">{{ $recruitment->Email }}</a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0"><i class="fa fa-phone text-secondary me-2"></i>No. Telepon</td>
                                <td>{{ $recruitment->Phone ?? $recruitment->Handphone ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0"><i class="fa fa-graduation-cap text-secondary me-2"></i>Pendidikan</td>
                                <td>{{ $recruitment->Last_Education ?? '-' }} <span class="text-muted">({{ $recruitment->Last_Education_From ?? '-' }})</span></td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0"><i class="fa fa-location-dot text-secondary me-2"></i>Alamat</td>
                                <td>{{ $recruitment->Address ?? '-' }}, {{ $recruitment->City ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0"><i class="fa fa-clock text-secondary me-2"></i>Tgl Dibuat</td>
                                <td>{{ !empty($recruitment->Created_Date) ? \Carbon\Carbon::parse($recruitment->Created_Date)->format('d M Y H:i') : '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Right: Documents & Decision Panel --}}
    <div class="col-lg-7">
        @php
            $cvPath = $recruitment->CV_File ?? $recruitment->CV ?? $documents->CV ?? null;
            $ijazahPath = $recruitment->Education_Certificate_File ?? $documents->Ijazah ?? null;
            $sertifikatPath = $recruitment->Certificate_File ?? $documents->Sertifikat ?? null;
            $ktpPath = $recruitment->KTP_File ?? $recruitment->KTP ?? $documents->KTP ?? null;
            $npwpPath = $recruitment->NPWP ?? $documents->NPWP ?? null;
        @endphp

        <div class="card shadow-sm border mb-4 verify-card">
            <div class="card-header bg-white py-3 px-3 border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark fs-14">
                    <i class="fa fa-folder-closed text-primary me-2"></i>Kelengkapan Dokumen Pelamar
                </span>
                <span class="badge bg-light text-muted border">Review Fisik / PDF</span>
            </div>
            <div class="card-body p-3">
                <div class="row g-3">
                    {{-- CV --}}
                    <div class="col-sm-6">
                        <div class="document-box {{ $cvPath ? 'has-file' : 'no-file' }}">
                            <div class="d-flex align-items-start justify-content-between mb-2">
                                <div class="d-flex align-items-center">
                                    <i class="fa fa-file-pdf text-danger fs-24 me-2"></i>
                                    <div>
                                        <h6 class="fw-bold mb-0 text-dark">CV / Resume</h6>
                                        <small class="text-muted">Riwayat Hidup</small>
                                    </div>
                                </div>
                                @if($cvPath)
                                    <span class="badge bg-success text-white"><i class="fa fa-check me-1"></i>Ada</span>
                                @else
                                    <span class="badge bg-danger text-white"><i class="fa fa-xmark me-1"></i>Kosong</span>
                                @endif
                            </div>
                            @if($cvPath)
                                <a href="{{ asset($cvPath) }}" target="_blank" class="btn btn-outline-primary btn-sm w-100 mt-2">
                                    <i class="fa fa-arrow-up-right-from-square me-1"></i> Buka CV
                                </a>
                            @else
                                <div class="small text-danger mt-2 fst-italic">Kandidat belum mengunggah file CV</div>
                            @endif
                        </div>
                    </div>

                    {{-- Ijazah --}}
                    <div class="col-sm-6">
                        <div class="document-box {{ $ijazahPath ? 'has-file' : 'no-file' }}">
                            <div class="d-flex align-items-start justify-content-between mb-2">
                                <div class="d-flex align-items-center">
                                    <i class="fa fa-graduation-cap text-primary fs-24 me-2"></i>
                                    <div>
                                        <h6 class="fw-bold mb-0 text-dark">Ijazah Terakhir</h6>
                                        <small class="text-muted">Bukti Kelulusan</small>
                                    </div>
                                </div>
                                @if($ijazahPath)
                                    <span class="badge bg-success text-white"><i class="fa fa-check me-1"></i>Ada</span>
                                @else
                                    <span class="badge bg-danger text-white"><i class="fa fa-xmark me-1"></i>Kosong</span>
                                @endif
                            </div>
                            @if($ijazahPath)
                                <a href="{{ asset($ijazahPath) }}" target="_blank" class="btn btn-outline-primary btn-sm w-100 mt-2">
                                    <i class="fa fa-arrow-up-right-from-square me-1"></i> Buka Ijazah
                                </a>
                            @else
                                <div class="small text-danger mt-2 fst-italic">Kandidat belum mengunggah Ijazah</div>
                            @endif
                        </div>
                    </div>

                    {{-- KTP --}}
                    <div class="col-sm-6">
                        <div class="document-box {{ $ktpPath ? 'has-file' : 'no-file' }}">
                            <div class="d-flex align-items-start justify-content-between mb-2">
                                <div class="d-flex align-items-center">
                                    <i class="fa fa-id-card text-info fs-24 me-2"></i>
                                    <div>
                                        <h6 class="fw-bold mb-0 text-dark">KTP / Identitas</h6>
                                        <small class="text-muted">Identitas Kependudukan</small>
                                    </div>
                                </div>
                                @if($ktpPath)
                                    <span class="badge bg-success text-white"><i class="fa fa-check me-1"></i>Ada</span>
                                @else
                                    <span class="badge bg-danger text-white"><i class="fa fa-xmark me-1"></i>Kosong</span>
                                @endif
                            </div>
                            @if($ktpPath)
                                <a href="{{ asset($ktpPath) }}" target="_blank" class="btn btn-outline-primary btn-sm w-100 mt-2">
                                    <i class="fa fa-arrow-up-right-from-square me-1"></i> Buka KTP
                                </a>
                            @else
                                <div class="small text-danger mt-2 fst-italic">Kandidat belum mengunggah KTP</div>
                            @endif
                        </div>
                    </div>

                    {{-- Sertifikat --}}
                    <div class="col-sm-6">
                        <div class="document-box {{ $sertifikatPath ? 'has-file' : 'no-file' }}">
                            <div class="d-flex align-items-start justify-content-between mb-2">
                                <div class="d-flex align-items-center">
                                    <i class="fa fa-certificate text-warning fs-24 me-2"></i>
                                    <div>
                                        <h6 class="fw-bold mb-0 text-dark">Sertifikat Pendukung</h6>
                                        <small class="text-muted">Pelatihan &amp; Kompetensi</small>
                                    </div>
                                </div>
                                @if($sertifikatPath)
                                    <span class="badge bg-success text-white"><i class="fa fa-check me-1"></i>Ada</span>
                                @else
                                    <span class="badge bg-secondary text-white">Opsional</span>
                                @endif
                            </div>
                            @if($sertifikatPath)
                                <a href="{{ asset($sertifikatPath) }}" target="_blank" class="btn btn-outline-primary btn-sm w-100 mt-2">
                                    <i class="fa fa-arrow-up-right-from-square me-1"></i> Buka Sertifikat
                                </a>
                            @else
                                <div class="small text-muted mt-2 fst-italic">Tidak ada file sertifikat (opsional)</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Verification Decision Form Card --}}
        <div class="card shadow-sm border verify-card">
            <div class="card-header bg-white py-3 px-3 border-bottom">
                <span class="fw-bold text-dark fs-14">
                    <i class="fa fa-gavel text-primary me-2"></i>Keputusan Verifikasi
                </span>
            </div>
            <div class="card-body p-3">
                <form id="verifyDocumentForm">
                    @csrf
                    <input type="hidden" id="candidate_id" name="id" value="{{ $recruitment->ID }}">
                    
                    <div class="mb-3">
                        <label for="note" class="form-label fw-semibold small text-muted">
                            Catatan Verifikasi (Opsional untuk Approve):
                        </label>
                        <textarea id="note" name="note" class="form-control" rows="3" 
                                  placeholder="Tuliskan catatan kelayakan dokumen, kekurangan yang perlu disusulkan, atau instruksi lain..."></textarea>
                        <div class="form-text small text-muted">
                            <i class="fa fa-circle-info me-1"></i> Catatan verifikasi dokumen kandidat.
                        </div>
                    </div>

                    <div class="row g-2 pt-2 border-top">
                        <div class="col-sm-6">
                            <button type="button" id="btnApprove" class="btn btn-success btn-lg w-100 text-white shadow-sm py-2">
                                <i class="fa fa-circle-check me-2"></i> Setujui &amp; Lanjut Psikotest
                            </button>
                            <small class="text-muted d-block mt-1 text-center" style="font-size: 11px;">
                                Meloloskan berkas ke antrean penjadwalan Psikotest
                            </small>
                        </div>
                        <div class="col-sm-6">
                            <button type="button" id="btnReject" class="btn btn-danger btn-lg w-100 text-white shadow-sm py-2">
                                <i class="fa fa-circle-xmark me-2"></i> Tolak Dokumen / Berkas
                            </button>
                            <small class="text-muted d-block mt-1 text-center" style="font-size: 11px;">
                                Menghentikan alur rekrutmen kandidat ini
                            </small>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Reject Confirmation Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <div class="modal-header py-3 bg-danger-transparent">
                <h5 class="modal-title fw-bold text-danger" id="rejectModalLabel">
                    <i class="fa fa-triangle-exclamation me-2"></i>Konfirmasi Penolakan Berkas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="fs-14 mb-3">
                    Apakah Anda yakin ingin menolak berkas kandidat <strong>{{ $recruitment->Name }}</strong>?
                </p>
                <div class="mb-3">
                    <label for="rejectReasonInput" class="form-label fw-semibold small text-danger">
                        Alasan Penolakan <span class="text-danger">*</span>:
                    </label>
                    <textarea class="form-control" id="rejectReasonInput" rows="3" placeholder="Tulis alasan penolakan secara jelas (misal: kualifikasi tidak cocok, dokumen tidak valid)..."></textarea>
                    <div class="invalid-feedback" id="rejectReasonFeedback">
                        Alasan penolakan wajib diisi.
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-3">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger btn-sm text-white shadow-sm" id="btnSubmitReject">
                    <i class="fa fa-trash me-1"></i> Ya, Tolak Kandidat
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Loading / Processing Modal --}}
<div id="processingModal" class="modal fade" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow text-center p-4 border-0">
            <div class="spinner-border text-primary mx-auto mb-3" role="status"></div>
            <h6 class="fw-bold mb-1">Memproses Data</h6>
            <p class="text-muted small mb-0">Mohon tunggu sebentar...</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    var bsRejectModal = null;
    var bsProcessingModal = null;

    function hideProcessingLoader() {
        if (bsProcessingModal) {
            try {
                bsProcessingModal.hide();
            } catch (e) {
                console.warn('Error hiding processing modal instance:', e);
            }
        }
        // Force cleanup modal-backdrop & styles in case bootstrap animation was in-flight
        setTimeout(function() {
            var el = document.getElementById('processingModal');
            if (el) {
                el.classList.remove('show');
                el.style.display = 'none';
                el.setAttribute('aria-hidden', 'true');
            }
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css({
                'overflow': '',
                'padding-right': ''
            });
        }, 150);
    }

    document.addEventListener('DOMContentLoaded', function () {
        var rejectEl = document.getElementById('rejectModal');
        if (rejectEl) {
            bsRejectModal = new bootstrap.Modal(rejectEl);
        }
        var procEl = document.getElementById('processingModal');
        if (procEl) {
            bsProcessingModal = new bootstrap.Modal(procEl);
        }

        // Handle Approve
        $('#btnApprove').click(function() {
            var id = $('#candidate_id').val();
            var note = $('#note').val();
            
            if (!id) {
                showAlert('danger', 'ID kandidat tidak ditemukan.');
                return;
            }

            if (!confirm('Apakah Anda yakin ingin menyetujui dokumen kandidat ini dan melanjutkan ke tahap Psikotest?')) {
                return;
            }
            
            if (bsProcessingModal) {
                bsProcessingModal.show();
            }
            
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ url('hrd-recruitment-verify-document') }}",
                type: "POST",
                data: { id: id, note: note },
                success: function(data) {
                    var response = (typeof data === 'string') ? JSON.parse(data) : data;
                    if (response.status === 'success') {
                        showAlert('success', response.message);
                        setTimeout(function() {
                            window.location.href = "{{ url('hrd-recruitment-psikotest') }}";
                        }, 1200);
                    } else {
                        hideProcessingLoader();
                        showAlert('danger', response.message || 'Gagal memverifikasi dokumen.');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    hideProcessingLoader();
                    var response = jqXHR.responseJSON;
                    if (response && response.message) {
                        showAlert('danger', response.message);
                    } else if (jqXHR.responseText) {
                        try {
                            var parsed = JSON.parse(jqXHR.responseText);
                            showAlert('danger', parsed.message || ('Terjadi kesalahan: ' + textStatus));
                        } catch (e) {
                            showAlert('danger', 'Terjadi kesalahan sistem (' + jqXHR.status + '): ' + errorThrown);
                        }
                    } else {
                        showAlert('danger', 'Terjadi kesalahan sistem: ' + textStatus);
                    }
                }
            });
        });
        
        // Handle Trigger Reject Modal
        $('#btnReject').click(function() {
            $('#rejectReasonInput').val('').removeClass('is-invalid');
            if (bsRejectModal) {
                bsRejectModal.show();
            }
        });

        // Submit Reject from Modal
        $('#btnSubmitReject').click(function() {
            var id = $('#candidate_id').val();
            var reasonInput = $('#rejectReasonInput');
            var reason = reasonInput.val().trim();

            if (reason === '') {
                reasonInput.addClass('is-invalid');
                reasonInput.focus();
                return;
            }
            reasonInput.removeClass('is-invalid');

            if (bsRejectModal) {
                bsRejectModal.hide();
            }
            if (bsProcessingModal) {
                bsProcessingModal.show();
            }
            
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ url('hrd-recruitment-reject-document') }}",
                type: "POST",
                data: { id: id, reason: reason },
                success: function(data) {
                    var response = (typeof data === 'string') ? JSON.parse(data) : data;
                    if (response.status === 'success') {
                        showAlert('warning', response.message);
                        setTimeout(function() {
                            window.location.href = "{{ route('hrd-recruitment-verify-document-list') }}";
                        }, 1200);
                    } else {
                        hideProcessingLoader();
                        showAlert('danger', response.message || 'Gagal menolak kandidat.');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    hideProcessingLoader();
                    var response = jqXHR.responseJSON;
                    if (response && response.message) {
                        showAlert('danger', response.message);
                    } else if (jqXHR.responseText) {
                        try {
                            var parsed = JSON.parse(jqXHR.responseText);
                            showAlert('danger', parsed.message || ('Terjadi kesalahan: ' + textStatus));
                        } catch (e) {
                            showAlert('danger', 'Terjadi kesalahan sistem (' + jqXHR.status + '): ' + errorThrown);
                        }
                    } else {
                        showAlert('danger', 'Terjadi kesalahan sistem: ' + textStatus);
                    }
                }
            });
        });
    });
    
    function showAlert(type, message) {
        var alertClass = (type === 'success') ? 'alert-success' : ((type === 'warning') ? 'alert-warning' : 'alert-danger');
        var icon = (type === 'success') ? 'fa-circle-check' : ((type === 'warning') ? 'fa-triangle-exclamation' : 'fa-circle-xmark');
        
        var alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show shadow-sm" role="alert">' +
                        '<i class="fa ' + icon + ' me-2"></i>' + message +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                        '</div>';
        
        $('#resultAlert').html(alertHtml).show();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
</script>
@endpush
@endsection
