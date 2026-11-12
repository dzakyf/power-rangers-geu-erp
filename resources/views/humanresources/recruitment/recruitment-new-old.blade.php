@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<div class="container-fluid py-3">
    {{-- Main Content Header --}}
    <div class="main-content-header mb-3">
        <h1 class="h3 font-weight-bold text-dark"><i class="fas fa-user-plus text-success me-2"></i> Human Resources - Recruitment</h1>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="/dashboard1">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ url('recruitment-data') }}">Recruitment</a></li>
            <li class="breadcrumb-item active"><span class="active">New Candidate (Internal)</span></li>
        </ol>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-white text-success d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-size: 18px;">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0 text-white font-weight-bold">Form Input Rekrutmen Kandidat Baru</h5>
                            <small class="text-white-50">Lengkapi data pribadi, kontak, pendidikan, dan dokumen lampiran</small>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <!-- Tombol Checklist Berkas & Panduan Input -->
                        <button type="button" class="btn btn-light btn-sm text-success fw-bold px-3 shadow-sm" data-toggle="modal" data-bs-toggle="modal" data-target="#modalPreparationGuide" data-bs-target="#modalPreparationGuide">
                            <i class="fas fa-clipboard-list me-1"></i> Panduan & Checklist Berkas
                        </button>
                        <span class="badge bg-white text-success px-3 py-2 fw-bold shadow-sm d-flex align-items-center">
                            <i class="fas fa-shield-alt me-1"></i> Internal HCGA
                        </span>
                    </div>
                </div>

                <div class="card-body p-4">
                    {{-- Stepper Progress Bar Visual --}}
                    <div class="recruitment-stepper mb-4">
                        <div class="progress" style="height: 6px; border-radius: 10px; background-color: #e9ecef;">
                            <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: 20%;" 
                                 aria-valuenow="1" aria-valuemin="1" aria-valuemax="5" id="formProgress"></div>
                        </div>
                        <div class="d-flex justify-content-between text-muted small mt-2 px-1">
                            <span id="step-label-0" class="step-label active fw-bold text-success">1. Personal</span>
                            <span id="step-label-1" class="step-label">2. Kontak</span>
                            <span id="step-label-2" class="step-label">3. Pendidikan</span>
                            <span id="step-label-3" class="step-label">4. Dokumen</span>
                            <span id="step-label-4" class="step-label">5. Review & Submit</span>
                        </div>
                    </div>

                    {{-- Tab Navigation (Divalidasi Sebelum Berpindah) --}}
                    <ul class="nav nav-pills nav-fill nav-tabs-recruitment mb-4 p-1 bg-light rounded-3" id="recruitmentTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active rounded-2" id="personal-tab" href="#personal" role="tab" aria-controls="personal" aria-selected="true">
                                <i class="fas fa-user me-1"></i> <span class="d-none d-md-inline">1. Data Pribadi</span>
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link rounded-2" id="contact-tab" href="#contact" role="tab" aria-controls="contact" aria-selected="false">
                                <i class="fas fa-address-book me-1"></i> <span class="d-none d-md-inline">2. Kontak & Alamat</span>
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link rounded-2" id="education-tab" href="#education" role="tab" aria-controls="education" aria-selected="false">
                                <i class="fas fa-graduation-cap me-1"></i> <span class="d-none d-md-inline">3. Riwayat Pendidikan</span>
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link rounded-2" id="documents-tab" href="#documents" role="tab" aria-controls="documents" aria-selected="false">
                                <i class="fas fa-file-upload me-1"></i> <span class="d-none d-md-inline">4. Unggah Dokumen</span>
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link rounded-2" id="review-tab" href="#review" role="tab" aria-controls="review" aria-selected="false">
                                <i class="fas fa-check-circle me-1"></i> <span class="d-none d-md-inline">5. Review Akhir</span>
                            </a>
                        </li>
                    </ul>

                    {{-- Form Content --}}
                    <form id="recruitmentForm" novalidate>
                        @csrf
                        <input type="hidden" id="Task_ID" name="Task_ID" value="">
                        <input type="hidden" id="source" name="source" value="internal">

                        <div class="tab-content border rounded-3 p-4 bg-white" id="recruitmentTabContent">
                            {{-- TAB 1: PERSONAL INFORMATION --}}
                            <div class="tab-pane fade show active" id="personal" role="tabpanel" aria-labelledby="personal-tab">
                                @include('humanresources.recruitment.partials._form-personal')
                            </div>

                            {{-- TAB 2: CONTACT INFORMATION --}}
                            <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                                @include('humanresources.recruitment.partials._form-contact')
                            </div>

                            {{-- TAB 3: EDUCATION --}}
                            <div class="tab-pane fade" id="education" role="tabpanel" aria-labelledby="education-tab">
                                @include('humanresources.recruitment.partials._form-education')
                            </div>

                            {{-- TAB 4: DOCUMENTS --}}
                            <div class="tab-pane fade" id="documents" role="tabpanel" aria-labelledby="documents-tab">
                                @include('humanresources.recruitment.partials._form-documents')
                            </div>

                            {{-- TAB 5: REVIEW & SUBMIT --}}
                            <div class="tab-pane fade" id="review" role="tabpanel" aria-labelledby="review-tab">
                                @include('humanresources.recruitment.partials._form-review')
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="card-footer bg-white border-0 px-0 pt-4 mt-3">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <button type="button" class="btn btn-outline-secondary px-4 py-2" id="btnPrevious">
                                        <i class="fas fa-arrow-left me-1"></i> Sebelumnya
                                    </button>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-info px-3 py-2 me-2" id="btnSaveDraft">
                                        <i class="fas fa-save me-1"></i> Simpan Draft
                                    </button>
                                    <button type="button" class="btn btn-primary px-4 py-2" id="btnNext">
                                        Selanjutnya <i class="fas fa-arrow-right ms-1"></i>
                                    </button>
                                    <button type="button" class="btn btn-success px-4 py-2 font-weight-bold" id="btnSubmit" style="display: none;">
                                        <i class="fas fa-paper-plane me-1"></i> Kirim Lamaran
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Panduan & Checklist Berkas Persiapan Kandidat -->
<div class="modal fade" id="modalPreparationGuide" tabindex="-1" role="dialog" aria-labelledby="modalPreparationGuideLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content shadow border-0">
            <div class="modal-header bg-success text-white py-3 px-4">
                <h5 class="modal-title font-weight-bold" id="modalPreparationGuideLabel">
                    <i class="fas fa-clipboard-check me-2"></i> Checklist Berkas & Panduan Input Kandidat
                </h5>
                <button type="button" class="close btn-close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info py-2 px-3 small mb-3">
                    <i class="fas fa-info-circle me-1"></i> Siapkan dokumen dan informasi berikut sebelum mulai menginput formulir rekrutmen:
                </div>

                <div class="row g-3">
                    {{-- 1. Dokumen Berkas --}}
                    <div class="col-md-6">
                        <div class="card h-100 border">
                            <div class="card-header bg-light py-2 px-3 font-weight-bold text-dark">
                                <i class="fas fa-file-pdf text-danger me-1"></i> 1. Berkas Lampiran (File)
                            </div>
                            <div class="card-body p-3 small">
                                <ul class="list-group list-group-flush mb-0">
                                    <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                        <span><strong>Pas Foto Formal</strong> (Format JPG/PNG, maks 2 MB)</span>
                                        <span class="badge bg-danger text-white">Wajib</span>
                                    </li>
                                    <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                        <span><strong>Curriculum Vitae (CV)</strong> (PDF/Word, maks 5 MB)</span>
                                        <span class="badge bg-danger text-white">Wajib</span>
                                    </li>
                                    <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                        <span><strong>KTP / Identitas</strong> (Scan/Foto jelas, maks 5 MB)</span>
                                        <span class="badge bg-danger text-white">Wajib</span>
                                    </li>
                                    <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                        <span><strong>Ijazah / SKL</strong> (PDF/Scan, maks 5 MB)</span>
                                        <span class="badge bg-danger text-white">Wajib</span>
                                    </li>
                                    <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                        <span><strong>Transkrip Nilai</strong> (PDF/Scan, maks 5 MB)</span>
                                        <span class="badge bg-secondary text-white">Opsional</span>
                                    </li>
                                    <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                        <span><strong>Sertifikat Pelatihan / Keahlian</strong></span>
                                        <span class="badge bg-secondary text-white">Opsional</span>
                                    </li>
                                    <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                        <span><strong>NPWP</strong></span>
                                        <span class="badge bg-secondary text-white">Opsional</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Data Teks Isian --}}
                    <div class="col-md-6">
                        <div class="card h-100 border">
                            <div class="card-header bg-light py-2 px-3 font-weight-bold text-dark">
                                <i class="fas fa-edit text-primary me-1"></i> 2. Informasi yang Diperlukan
                            </div>
                            <div class="card-body p-3 small">
                                <h6 class="fw-bold text-success mb-1">A. Data Personal & PTK</h6>
                                <p class="text-muted mb-2">
                                    Posisi PTK yang dilamar, Tipe Rekrutmen, Nama Lengkap (sesuai KTP), No. KTP (16 digit), Tempat & Tgl Lahir (min. 17 thn), Jenis Kelamin, Agama, dan Status Pernikahan.
                                </p>

                                <h6 class="fw-bold text-success mb-1">B. Kontak & Alamat</h6>
                                <p class="text-muted mb-2">
                                    No. Handphone / WhatsApp aktif (10-15 digit), Email aktif, Alamat KTP, Alamat Domisili saat ini, dan Kontak Darurat (Nama & No. Telp).
                                </p>

                                <h6 class="fw-bold text-success mb-1">C. Riwayat Pendidikan</h6>
                                <p class="text-muted mb-0">
                                    Jenjang Terakhir (SD s/d S3), Nama Institusi/Universitas, Jurusan/Program Studi, Tahun Lulus, dan Nilai Akhir / IPK.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-4">
                <button type="button" class="btn btn-success btn-sm px-4 fw-bold" data-dismiss="modal" data-bs-dismiss="modal">
                    <i class="fas fa-check me-1"></i> Mengerti & Mulai Pengisian
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Custom Styles & Responsive Fix --}}
<style>
    .nav-tabs-recruitment .nav-link {
        color: #495057;
        font-weight: 500;
        padding: 10px 16px;
        transition: all 0.2s ease-in-out;
        border: 1px solid transparent;
        cursor: pointer;
    }
    
    .nav-tabs-recruitment .nav-link:hover {
        background-color: #e9ecef;
        color: #0f5132;
    }
    
    .nav-tabs-recruitment .nav-link.active {
        background-color: #28a745 !important;
        color: #ffffff !important;
        font-weight: 600;
        box-shadow: 0 2px 6px rgba(40, 167, 69, 0.3);
    }
    
    .section-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #198754;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e9ecef;
    }
    
    .info-box {
        background-color: #e8f4fd;
        border-left: 4px solid #0d6efd;
        padding: 12px 16px;
        margin-bottom: 24px;
        border-radius: 6px;
        font-size: 14px;
    }
    
    .form-group label {
        font-weight: 600;
        color: #343a40;
        margin-bottom: 6px;
        font-size: 13.5px;
    }
    
    .form-group label .required {
        color: #dc3545;
        font-weight: bold;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.2);
    }
    
    .review-section-title {
        font-weight: 700;
        font-size: 1rem;
        color: #2c3e50;
        padding-bottom: 8px;
        border-bottom: 1px solid #dee2e6;
        margin-bottom: 15px;
    }
    
    .step-label {
        font-size: 12px;
        transition: color 0.3s;
    }
</style>

{{-- JavaScript Controller --}}
@include('humanresources.recruitment.partials._scripts')

@endsection
