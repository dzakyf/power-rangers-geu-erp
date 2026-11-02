<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Karir & Lowongan Kerja - GEU</title>
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon2.png') }}">
    <script src="{{ asset('/assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('/assets/js/popper.min.js') }}"></script>
    <script src="{{ asset('/assets/js/bootstrap.min.js') }}"></script>
    <style>
        :root {
            --primary-color: #198754;
            --primary-dark: #146c43;
            --primary-light: #e8f5e9;
            --primary-soft: #f0fdf4;
            --accent-color: #20c997;
            --text-main: #212529;
            --text-muted: #6c757d;
            --border-color: #dee2e6;
            --bg-body: #f8fafc;
        }

        body {
            background-color: var(--bg-body);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: var(--text-main);
            line-height: 1.6;
        }

        .main-card {
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.06);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05) !important;
        }

        .bg-portal-header {
            background: linear-gradient(135deg, #157347 0%, #198754 50%, #20c997 100%);
            border-bottom: 3px solid rgba(0,0,0,0.1);
        }

        /* Stepper bar */
        .recruitment-stepper {
            background: #ffffff;
            padding: 16px 20px;
            border-radius: 10px;
            border: 1px solid var(--border-color);
        }
        .step-label {
            font-size: 13px;
            font-weight: 500;
            color: #8898aa;
            transition: all 0.3s ease;
        }
        .step-label.active {
            color: var(--primary-color) !important;
            font-weight: 700;
        }

        /* Tab Navigation */
        .nav-tabs-recruitment {
            border-bottom: none;
            background-color: #edf2f7;
            border-radius: 10px;
            padding: 5px;
            gap: 4px;
        }
        .nav-tabs-recruitment .nav-link {
            border: none;
            color: #4a5568;
            padding: 12px 16px;
            font-weight: 600;
            font-size: 14px;
            border-radius: 8px;
            transition: all 0.25s ease;
            cursor: pointer;
            text-align: center;
        }
        .nav-tabs-recruitment .nav-link:hover {
            background-color: #e2e8f0;
            color: var(--primary-dark);
        }
        .nav-tabs-recruitment .nav-link.active {
            background-color: var(--primary-color) !important;
            color: #ffffff !important;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(25, 135, 84, 0.35);
        }

        /* Form Controls Styling */
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-group label {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 6px;
            font-size: 13.5px;
            display: inline-block;
        }
        .form-group label .required {
            color: #e53e3e;
            margin-left: 2px;
            font-weight: bold;
        }
        .form-control, .custom-select {
            border: 1.5px solid #cbd5e0;
            border-radius: 7px;
            padding: 9px 13px;
            font-size: 14px;
            color: #2d3748;
            background-color: #ffffff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .form-control:focus, .custom-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.2);
            outline: none;
        }
        .form-control[disabled], .form-control[readonly], .custom-select[disabled] {
            background-color: #f1f5f9;
            color: #64748b;
            cursor: not-allowed;
            border-color: #e2e8f0;
        }
        .form-text.text-muted, small.text-muted {
            font-size: 12px;
            color: #718096 !important;
            margin-top: 4px;
        }

        /* Section Title & Info Box */
        .section-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 18px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
        }
        .section-title i {
            margin-right: 10px;
            color: var(--primary-color);
        }
        .info-box {
            background-color: var(--primary-soft);
            border-left: 4px solid var(--primary-color);
            padding: 12px 18px;
            margin-bottom: 24px;
            border-radius: 6px;
            font-size: 13.5px;
            color: #274c3d;
        }
        .info-box i {
            color: var(--primary-color);
            margin-right: 6px;
        }

        /* Custom File Input */
        .custom-file-label {
            border: 1.5px solid #cbd5e0;
            border-radius: 7px;
            padding: 9px 13px;
            font-size: 14px;
            color: #718096;
            height: calc(1.5em + 1.25rem + 2px);
        }
        .custom-file-label::after {
            background-color: #edf2f7;
            color: #4a5568;
            font-weight: 600;
            border-left: 1.5px solid #cbd5e0;
            padding: 9px 14px;
            height: 100%;
        }

        /* Review Section Details */
        .review-section {
            background: #ffffff;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            padding: 20px;
            margin-bottom: 20px;
        }
        .review-section-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--primary-dark);
            border-bottom: 1px solid #edf2f7;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .review-section dt {
            color: #718096;
            font-weight: 600;
            font-size: 13.5px;
        }
        .review-section dd {
            color: #1a202c;
            font-weight: 600;
            font-size: 14px;
        }

        /* Buttons Consistency */
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            font-weight: 600;
        }
        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        .btn-success {
            background-color: #10b981;
            border-color: #10b981;
            font-weight: 600;
        }
        .btn-success:hover {
            background-color: #059669;
            border-color: #059669;
        }
        .btn-outline-secondary {
            border-color: #cbd5e0;
            color: #4a5568;
            font-weight: 600;
        }
        .btn-outline-secondary:hover {
            background-color: #f7fafc;
            color: #2d3748;
            border-color: #a0aec0;
        }

        .hp-field {
            position: absolute;
            left: -9999px;
            top: -9999px;
        }

        @media (max-width: 768px) {
            .nav-tabs-recruitment .nav-link {
                padding: 10px 8px;
                font-size: 0.8rem;
            }
            .section-title {
                font-size: 1.05rem;
            }
        }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="card shadow-sm main-card" id="recruitmentCard">
        <div class="card-header bg-portal-header text-white py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-white text-success d-flex align-items-center justify-content-center mr-3 shadow-sm" style="width: 44px; height: 44px; font-size: 20px;">
                    <i class="fas fa-briefcase"></i>
                </div>
                <div>
                    <h5 class="card-title mb-0 font-weight-bold text-white">Formulir Pendaftaran Rekrutmen</h5>
                    <small class="text-white" style="opacity: 0.9;">Portal Resmi Penerimaan Karyawan Baru PT Graha Energi Usahatama</small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
                <!-- Tombol Panduan & Checklist Berkas -->
                <button type="button" class="btn btn-light btn-sm text-success font-weight-bold px-3 shadow-sm mr-2" data-toggle="modal" data-target="#modalPreparationGuide">
                    <i class="fas fa-clipboard-list mr-1"></i> Panduan & Checklist Berkas
                </button>
                <span class="badge badge-light text-success px-3 py-2 font-weight-bold shadow-sm d-flex align-items-center">
                    <i class="fas fa-globe mr-1"></i> Public Portal
                </span>
            </div>
        </div>

        <div class="card-body p-4 bg-white">
            {{-- Stepper Progress Bar Visual --}}
            <div class="recruitment-stepper mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small font-weight-bold text-muted">Progress Pengisian Formulir</span>
                    <span class="small font-weight-bold text-success" id="progressPercentage">20% Selesai</span>
                </div>
                <div class="progress" style="height: 8px; border-radius: 10px; background-color: #edf2f7;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: 20%; transition: width 0.4s ease;" id="formProgress"></div>
                </div>
                <div class="d-flex justify-content-between text-muted small mt-2 px-1">
                    <span id="step-label-0" class="step-label active">1. Data Pribadi</span>
                    <span id="step-label-1" class="step-label">2. Kontak & Alamat</span>
                    <span id="step-label-2" class="step-label">3. Riwayat Pendidikan</span>
                    <span id="step-label-3" class="step-label">4. Unggah Dokumen</span>
                    <span id="step-label-4" class="step-label">5. Review & Kirim</span>
                </div>
            </div>

            {{-- Tab Navigation --}}
            <ul class="nav nav-pills nav-fill nav-tabs-recruitment mb-4" id="recruitmentTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="personal-tab" href="#personal" role="tab" aria-selected="true">
                        <i class="fas fa-user mr-1"></i> <span class="d-none d-md-inline">1. Data Pribadi</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="contact-tab" href="#contact" role="tab" aria-selected="false">
                        <i class="fas fa-address-book mr-1"></i> <span class="d-none d-md-inline">2. Kontak & Alamat</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="education-tab" href="#education" role="tab" aria-selected="false">
                        <i class="fas fa-graduation-cap mr-1"></i> <span class="d-none d-md-inline">3. Pendidikan</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="documents-tab" href="#documents" role="tab" aria-selected="false">
                        <i class="fas fa-file-upload mr-1"></i> <span class="d-none d-md-inline">4. Unggah Berkas</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="review-tab" href="#review" role="tab" aria-selected="false">
                        <i class="fas fa-check-circle mr-1"></i> <span class="d-none d-md-inline">5. Review Akhir</span>
                    </a>
                </li>
            </ul>

            <form id="recruitmentForm" novalidate enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="source" value="public">
                <input type="hidden" name="Recruitment_Type" value="EXTERNAL">
                <input type="text" name="website" value="" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">

                <div class="tab-content border rounded-lg p-4 bg-white shadow-none" id="recruitmentTabContent" style="border-color: #e2e8f0 !important;">
                    <div class="tab-pane fade show active" id="personal" role="tabpanel">
                        @include('humanresources.recruitment.partials._form-personal')
                    </div>
                    <div class="tab-pane fade" id="contact" role="tabpanel">
                        @include('humanresources.recruitment.partials._form-contact')
                    </div>
                    <div class="tab-pane fade" id="education" role="tabpanel">
                        @include('humanresources.recruitment.partials._form-education')
                    </div>
                    <div class="tab-pane fade" id="documents" role="tabpanel">
                        @include('humanresources.recruitment.partials._form-documents')
                    </div>
                    <div class="tab-pane fade" id="review" role="tabpanel">
                        @include('humanresources.recruitment.partials._form-review')
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="border-0 px-0 pt-4 mt-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <button type="button" class="btn btn-outline-secondary px-4 py-2 font-weight-bold" id="btnPrevious">
                                <i class="fas fa-arrow-left mr-1"></i> Sebelumnya
                            </button>
                        </div>
                        <div>
                            <button type="button" class="btn btn-outline-info" id="btnSaveDraft" style="display:none;">Save Draft</button>
                            <button type="button" class="btn btn-primary px-4 py-2 font-weight-bold shadow-sm" id="btnNext">
                                Selanjutnya <i class="fas fa-arrow-right ml-1"></i>
                            </button>
                            <button type="button" class="btn btn-success px-4 py-2 font-weight-bold shadow-sm" id="btnSubmit" style="display:none;">
                                <i class="fas fa-paper-plane mr-1"></i> Kirim Lamaran
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Alert Sukses Pengiriman Lamaran --}}
    <div class="card shadow main-card mt-3" id="publicSuccess" style="display:none;">
        <div class="card-body text-center py-5">
            <i class="fas fa-check-circle text-success" style="font-size:3.5rem;"></i>
            <h3 class="mt-3 font-weight-bold text-dark">Lamaran Berhasil Terkirim!</h3>
            <p class="text-muted mt-2">
                Terima kasih telah mendaftar. Tim Human Capital kami akan segera memverifikasi kelengkapan berkas Anda.<br>
                Nomor Registrasi / Task ID Anda: <strong class="text-success h5" id="successTaskId">-</strong>
            </p>
            <div class="mt-4">
                <a href="{{ url('open-recruitment-employee') }}" class="btn btn-outline-success px-4 font-weight-bold">
                    <i class="fas fa-redo mr-1"></i> Daftar Lowongan Lain
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Panduan & Checklist Berkas Persiapan Kandidat -->
<div class="modal fade" id="modalPreparationGuide" tabindex="-1" role="dialog" aria-labelledby="modalPreparationGuideLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content shadow border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-success text-white py-3 px-4">
                <h5 class="modal-title font-weight-bold" id="modalPreparationGuideLabel">
                    <i class="fas fa-clipboard-check mr-2"></i> Checklist Berkas & Panduan Pelamar
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" style="background-color: #f8fafc;">
                <div class="alert alert-info py-2 px-3 small mb-3 border-0 shadow-sm" style="background-color: #e0f2fe; color: #0369a1;">
                    <i class="fas fa-info-circle mr-1"></i> Siapkan dokumen dan informasi berikut sebelum mulai mengisi formulir:
                </div>

                <div class="row">
                    {{-- 1. Dokumen Berkas --}}
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 border-0 shadow-sm" style="border-radius: 8px;">
                            <div class="card-header bg-white py-2 px-3 font-weight-bold text-dark border-bottom">
                                <i class="fas fa-file-pdf text-danger mr-1"></i> 1. Berkas Lampiran (File)
                            </div>
                            <div class="card-body p-3 small">
                                <ul class="list-group list-group-flush mb-0">
                                    <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                        <span><strong>Pas Foto Formal</strong> (Format JPG/PNG, maks 2 MB)</span>
                                        <span class="badge badge-danger text-white">Wajib</span>
                                    </li>
                                    <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                        <span><strong>Curriculum Vitae (CV)</strong> (PDF/Word, maks 5 MB)</span>
                                        <span class="badge badge-danger text-white">Wajib</span>
                                    </li>
                                    <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                        <span><strong>KTP / Identitas</strong> (Scan/Foto jelas, maks 5 MB)</span>
                                        <span class="badge badge-danger text-white">Wajib</span>
                                    </li>
                                    <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                        <span><strong>Ijazah / SKL</strong> (PDF/Scan, maks 5 MB)</span>
                                        <span class="badge badge-danger text-white">Wajib</span>
                                    </li>
                                    <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                        <span><strong>Transkrip Nilai</strong> (PDF/Scan, maks 5 MB)</span>
                                        <span class="badge badge-secondary text-white">Opsional</span>
                                    </li>
                                    <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                        <span><strong>Sertifikat Pelatihan</strong></span>
                                        <span class="badge badge-secondary text-white">Opsional</span>
                                    </li>
                                    <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                        <span><strong>NPWP</strong></span>
                                        <span class="badge badge-secondary text-white">Opsional</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Data Teks Isian --}}
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 border-0 shadow-sm" style="border-radius: 8px;">
                            <div class="card-header bg-white py-2 px-3 font-weight-bold text-dark border-bottom">
                                <i class="fas fa-edit text-primary mr-1"></i> 2. Informasi yang Diperlukan
                            </div>
                            <div class="card-body p-3 small">
                                <h6 class="font-weight-bold text-success mb-1">A. Data Personal</h6>
                                <p class="text-muted mb-2">
                                    Posisi Jabatan Dilamar, Nama Lengkap (KTP), No. KTP (16 digit), Tempat & Tgl Lahir (min. 17 thn), Jenis Kelamin, Agama, dan Status Pernikahan.
                                </p>

                                <h6 class="font-weight-bold text-success mb-1">B. Kontak & Alamat</h6>
                                <p class="text-muted mb-2">
                                    No. Handphone / WhatsApp aktif (10-15 digit), Email aktif, Alamat KTP, Alamat Domisili saat ini, dan Kontak Darurat.
                                </p>

                                <h6 class="font-weight-bold text-success mb-1">C. Riwayat Pendidikan</h6>
                                <p class="text-muted mb-0">
                                    Jenjang Terakhir (SD s/d S3), Nama Institusi/Universitas, Jurusan/Program Studi, Tahun Lulus, dan Nilai Akhir / IPK.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-4">
                <button type="button" class="btn btn-success btn-sm px-4 font-weight-bold" data-dismiss="modal">
                    <i class="fas fa-check mr-1"></i> Mengerti & Mulai Pengisian
                </button>
            </div>
        </div>
    </div>
</div>

@include('humanresources.recruitment.partials._scripts-public')
</body>
</html>

    {{-- Alert Sukses Pengiriman Lamaran --}}
    <div class="card shadow main-card mt-3" id="publicSuccess" style="display:none;">
        <div class="card-body text-center py-5">
            <i class="fas fa-check-circle text-success" style="font-size:3.5rem;"></i>
            <h3 class="mt-3 font-weight-bold text-dark">Lamaran Berhasil Terkirim!</h3>
            <p class="text-muted mt-2">
                Terima kasih telah mendaftar. Tim Human Capital kami akan segera memverifikasi kelengkapan berkas Anda.<br>
                Nomor Registrasi / Task ID Anda: <strong class="text-success h5" id="successTaskId">-</strong>
            </p>
            <div class="mt-4">
                <a href="{{ url('open-recruitment-employee') }}" class="btn btn-outline-success px-4">
                    <i class="fas fa-redo mr-1"></i> Daftar Lowongan Lain
                </a>
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
                    <i class="fas fa-clipboard-check mr-2"></i> Checklist Berkas & Panduan Pelamar
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info py-2 px-3 small mb-3">
                    <i class="fas fa-info-circle mr-1"></i> Siapkan dokumen dan informasi berikut sebelum mulai mengisi formulir:
                </div>

                <div class="row">
                    {{-- 1. Dokumen Berkas --}}
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 border">
                            <div class="card-header bg-light py-2 px-3 font-weight-bold text-dark">
                                <i class="fas fa-file-pdf text-danger mr-1"></i> 1. Berkas Lampiran (File)
                            </div>
                            <div class="card-body p-3 small">
                                <ul class="list-group list-group-flush mb-0">
                                    <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                        <span><strong>Pas Foto Formal</strong> (Format JPG/PNG, maks 2 MB)</span>
                                        <span class="badge badge-danger text-white">Wajib</span>
                                    </li>
                                    <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                        <span><strong>Curriculum Vitae (CV)</strong> (PDF/Word, maks 5 MB)</span>
                                        <span class="badge badge-danger text-white">Wajib</span>
                                    </li>
                                    <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                        <span><strong>KTP / Identitas</strong> (Scan/Foto jelas, maks 5 MB)</span>
                                        <span class="badge badge-danger text-white">Wajib</span>
                                    </li>
                                    <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                        <span><strong>Ijazah / SKL</strong> (PDF/Scan, maks 5 MB)</span>
                                        <span class="badge badge-danger text-white">Wajib</span>
                                    </li>
                                    <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                        <span><strong>Transkrip Nilai</strong> (PDF/Scan, maks 5 MB)</span>
                                        <span class="badge badge-secondary text-white">Opsional</span>
                                    </li>
                                    <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                        <span><strong>Sertifikat Pelatihan</strong></span>
                                        <span class="badge badge-secondary text-white">Opsional</span>
                                    </li>
                                    <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                        <span><strong>NPWP</strong></span>
                                        <span class="badge badge-secondary text-white">Opsional</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Data Teks Isian --}}
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 border">
                            <div class="card-header bg-light py-2 px-3 font-weight-bold text-dark">
                                <i class="fas fa-edit text-primary mr-1"></i> 2. Informasi yang Diperlukan
                            </div>
                            <div class="card-body p-3 small">
                                <h6 class="font-weight-bold text-success mb-1">A. Data Personal</h6>
                                <p class="text-muted mb-2">
                                    Posisi Jabatan Dilamar, Nama Lengkap (KTP), No. KTP (16 digit), Tempat & Tgl Lahir (min. 17 thn), Jenis Kelamin, Agama, dan Status Pernikahan.
                                </p>

                                <h6 class="font-weight-bold text-success mb-1">B. Kontak & Alamat</h6>
                                <p class="text-muted mb-2">
                                    No. Handphone / WhatsApp aktif (10-15 digit), Email aktif, Alamat KTP, Alamat Domisili saat ini, dan Kontak Darurat.
                                </p>

                                <h6 class="font-weight-bold text-success mb-1">C. Riwayat Pendidikan</h6>
                                <p class="text-muted mb-0">
                                    Jenjang Terakhir (SD s/d S3), Nama Institusi/Universitas, Jurusan/Program Studi, Tahun Lulus, dan Nilai Akhir / IPK.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-4">
                <button type="button" class="btn btn-success btn-sm px-4 font-weight-bold" data-dismiss="modal">
                    <i class="fas fa-check mr-1"></i> Mengerti & Mulai Pengisian
                </button>
            </div>
        </div>
    </div>
</div>

@include('humanresources.recruitment.partials._scripts-public')
</body>
</html>
