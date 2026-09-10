<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lowongan Kerja - GEU</title>
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/LineIcons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/viewer.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/icofont.min.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/calendar.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/styles.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/responsive.css') }}" rel="stylesheet">
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon2.png') }}">
    <script src="{{ asset('/assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('/assets/js/popper.min.js') }}"></script>
    <script src="{{ asset('/assets/js/bootstrap.min.js') }}"></script>
    <style>
        .bg-gradient-success { background: linear-gradient(135deg, #28a745 0%, #69b257 100%); }
        .nav-tabs-recruitment { border-bottom: 2px solid #dee2e6; }
        .nav-tabs-recruitment .nav-link { border: none; color: #6c757d; padding: 12px 20px; margin-right: 5px; }
        .nav-tabs-recruitment .nav-link.active { color: #28a745; border-bottom: 3px solid #28a745; font-weight: 600; }
        .form-group label { font-weight: 600; color: #495057; margin-bottom: 8px; }
        .form-group label .required { color: #dc3545; margin-left: 3px; }
        .section-title { font-size: 1.1rem; font-weight: 600; color: #28a745; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #e9ecef; }
        .info-box { background-color: #e7f5ff; border-left: 4px solid #17a2b8; padding: 12px 15px; margin-bottom: 20px; border-radius: 4px; }
        .hp-field { position: absolute; left: -9999px; top: -9999px; }
        @media (max-width: 768px) { .nav-tabs-recruitment .nav-link { padding: 10px 12px; font-size: .9rem; } }
    </style>
</head>
<body style="background-color:#f8f9fa;">
<div class="container py-4">
    <div class="card shadow-sm" id="recruitmentCard">
        <div class="card-header bg-gradient-success text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="fas fa-user-plus mr-2"></i> Formulir Lamaran Kerja</h5>
                <span class="badge badge-light text-success">Public</span>
            </div>
        </div>
        <div class="card-body">
            @if($requisitions->isEmpty())
                <div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Saat ini belum ada lowongan yang dibuka. Silakan kembali lagi nanti.</div>
            @else
                <div class="recruitment-progress mb-4">
                    <div class="progress" style="height:3px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width:0%;" id="formProgress"></div>
                    </div>
                    <div class="text-center mt-2"><small class="text-muted"><i class="fas fa-info-circle"></i> Lengkapi semua data untuk melanjutkan ke tahap berikutnya</small></div>
                </div>
                <ul class="nav nav-tabs nav-tabs-recruitment mb-4" id="recruitmentTabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" id="personal-tab" data-toggle="tab" href="#personal" role="tab"><i class="fas fa-user"></i><span class="d-none d-sm-inline"> Personal</span></a></li>
                    <li class="nav-item"><a class="nav-link" id="contact-tab" data-toggle="tab" href="#contact" role="tab"><i class="fas fa-address-book"></i><span class="d-none d-sm-inline"> Contact</span></a></li>
                    <li class="nav-item"><a class="nav-link" id="education-tab" data-toggle="tab" href="#education" role="tab"><i class="fas fa-graduation-cap"></i><span class="d-none d-sm-inline"> Education</span></a></li>
                    <li class="nav-item"><a class="nav-link" id="documents-tab" data-toggle="tab" href="#documents" role="tab"><i class="fas fa-file-upload"></i><span class="d-none d-sm-inline"> Documents</span></a></li>
                    <li class="nav-item"><a class="nav-link" id="review-tab" data-toggle="tab" href="#review" role="tab"><i class="fas fa-check-circle"></i><span class="d-none d-sm-inline"> Review</span></a></li>
                </ul>
                <form id="recruitmentForm" novalidate enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="source" value="public">
                    <input type="hidden" name="Recruitment_Type" value="EXTERNAL">
                    <input type="text" name="website" value="" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
                    <div class="tab-content" id="recruitmentTabContent">
                        <div class="tab-pane fade show active" id="personal" role="tabpanel">@include('humanresources.recruitment.partials._form-personal', ['publicRequisitions' => $requisitions])</div>
                        <div class="tab-pane fade" id="contact" role="tabpanel">@include('humanresources.recruitment.partials._form-contact')</div>
                        <div class="tab-pane fade" id="education" role="tabpanel">@include('humanresources.recruitment.partials._form-education')</div>
                        <div class="tab-pane fade" id="documents" role="tabpanel">@include('humanresources.recruitment.partials._form-documents')</div>
                        <div class="tab-pane fade" id="review" role="tabpanel">@include('humanresources.recruitment.partials._form-review')</div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary" id="btnPrevious"><i class="fas fa-arrow-left"></i> Previous</button>
                                <div>
                                    <button type="button" class="btn btn-outline-info" id="btnSaveDraft" style="display:none;">Save Draft</button>
                                    <button type="button" class="btn btn-primary" id="btnNext">Next <i class="fas fa-arrow-right"></i></button>
                                    <button type="button" class="btn btn-success" id="btnSubmit" style="display:none;"><i class="fas fa-paper-plane"></i> Kirim Lamaran</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>
    <div class="card shadow-sm mt-3" id="publicSuccess" style="display:none;">
        <div class="card-body text-center py-5">
            <i class="fas fa-check-circle text-success" style="font-size:3rem;"></i>
            <h4 class="mt-3">Lamaran Terkirim</h4>
            <p class="text-muted">Terima kasih. HR akan menghubungi Anda untuk tahap verifikasi dokumen.<br>Nomor lamaran: <strong id="successTaskId">-</strong></p>
        </div>
    </div>
</div>
@include('humanresources.recruitment.partials._scripts-public')
</body>
</html>
