@extends('layout.mainlayout')
@section('content-wrapper')
<div class="container-fluid">
    {{-- Main Content Header --}}
    <div class="main-content-header">
        <h1><i class="fas fa-user-plus"></i> Human Resources - Recruitment</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">Dashboard</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ url('recruitment') }}">Recruitment</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">New Candidate (Internal)</span>
            </li>
        </ol>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-gradient-success text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user-plus mr-2"></i> New Recruitment - Internal
                        </h5>
                        <div class="badge badge-light text-success">
                            <i class="fas fa-clock"></i> Draft Auto-save Enabled
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    {{-- Progress Indicator --}}
                    <div class="recruitment-progress mb-4">
                        <div class="progress" style="height: 3px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 0%;" 
                                 aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" id="formProgress"></div>
                        </div>
                        <div class="progress-text text-center mt-2">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> 
                                Lengkapi semua data untuk melanjutkan ke tahap berikutnya
                            </small>
                        </div>
                    </div>

                    {{-- Tab Navigation --}}
                    <ul class="nav nav-tabs nav-tabs-recruitment mb-4" id="recruitmentTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="personal-tab" data-toggle="tab" href="#personal" 
                               role="tab" aria-controls="personal" aria-selected="true">
                                <i class="fas fa-user"></i>
                                <span class="d-none d-sm-inline"> Personal</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="contact-tab" data-toggle="tab" href="#contact" 
                               role="tab" aria-controls="contact" aria-selected="false">
                                <i class="fas fa-address-book"></i>
                                <span class="d-none d-sm-inline"> Contact</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="education-tab" data-toggle="tab" href="#education" 
                               role="tab" aria-controls="education" aria-selected="false">
                                <i class="fas fa-graduation-cap"></i>
                                <span class="d-none d-sm-inline"> Education</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="documents-tab" data-toggle="tab" href="#documents" 
                               role="tab" aria-controls="documents" aria-selected="false">
                                <i class="fas fa-file-upload"></i>
                                <span class="d-none d-sm-inline"> Documents</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="review-tab" data-toggle="tab" href="#review" 
                               role="tab" aria-controls="review" aria-selected="false">
                                <i class="fas fa-check-circle"></i>
                                <span class="d-none d-sm-inline"> Review</span>
                            </a>
                        </li>
                    </ul>

                    {{-- Form Content --}}
                    <form id="recruitmentForm" novalidate>
                        @csrf
                        <input type="hidden" id="Task_ID" name="Task_ID" value="">
                        <input type="hidden" id="source" name="source" value="internal">

                        <div class="tab-content" id="recruitmentTabContent">
                            {{-- TAB 1: PERSONAL INFORMATION --}}
                            <div class="tab-pane fade show active" id="personal" role="tabpanel" 
                                 aria-labelledby="personal-tab">
                                @include('humanresources.recruitment.partials._form-personal')
                            </div>

                            {{-- TAB 2: CONTACT INFORMATION --}}
                            <div class="tab-pane fade" id="contact" role="tabpanel" 
                                 aria-labelledby="contact-tab">
                                @include('humanresources.recruitment.partials._form-contact')
                            </div>

                            {{-- TAB 3: EDUCATION --}}
                            <div class="tab-pane fade" id="education" role="tabpanel" 
                                 aria-labelledby="education-tab">
                                @include('humanresources.recruitment.partials._form-education')
                            </div>

                            {{-- TAB 4: DOCUMENTS --}}
                            <div class="tab-pane fade" id="documents" role="tabpanel" 
                                 aria-labelledby="documents-tab">
                                @include('humanresources.recruitment.partials._form-documents')
                            </div>

                            {{-- TAB 5: REVIEW & SUBMIT --}}
                            <div class="tab-pane fade" id="review" role="tabpanel" 
                                 aria-labelledby="review-tab">
                                @include('humanresources.recruitment.partials._form-review')
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <button type="button" class="btn btn-outline-secondary" id="btnPrevious">
                                            <i class="fas fa-arrow-left"></i> Previous
                                        </button>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-outline-info" id="btnSaveDraft">
                                            <i class="fas fa-save"></i> Save Draft
                                        </button>
                                        <button type="button" class="btn btn-primary" id="btnNext">
                                            Next <i class="fas fa-arrow-right"></i>
                                        </button>
                                        <button type="button" class="btn btn-success" id="btnSubmit" style="display: none;">
                                            <i class="fas fa-paper-plane"></i> Submit
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Custom Styles --}}
<style>
    .bg-gradient-success {
        background: linear-gradient(135deg, #28a745 0%, #69b257 100%);
    }
    
    .nav-tabs-recruitment {
        border-bottom: 2px solid #dee2e6;
    }
    
    .nav-tabs-recruitment .nav-link {
        border: none;
        color: #6c757d;
        padding: 12px 20px;
        margin-right: 5px;
        transition: all 0.3s ease;
    }
    
    .nav-tabs-recruitment .nav-link:hover {
        background-color: #f8f9fa;
        color: #28a745;
    }
    
    .nav-tabs-recruitment .nav-link.active {
        color: #28a745;
        border-bottom: 3px solid #28a745;
        font-weight: 600;
    }
    
    .nav-tabs-recruitment .nav-link i {
        font-size: 1.2em;
    }
    
    .form-group label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 8px;
    }
    
    .form-group label .required {
        color: #dc3545;
        margin-left: 3px;
    }
    
    .form-control:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
    }
    
    .section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #28a745;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e9ecef;
    }
    
    .section-title i {
        margin-right: 8px;
    }
    
    .info-box {
        background-color: #e7f5ff;
        border-left: 4px solid #17a2b8;
        padding: 12px 15px;
        margin-bottom: 20px;
        border-radius: 4px;
    }
    
    .info-box i {
        color: #17a2b8;
        margin-right: 8px;
    }
    
    .card {
        border-radius: 8px;
        border: none;
    }
    
    .shadow-sm {
        box-shadow: 0 .125rem .25rem rgba(0,0,0,.075)!important;
    }
    
    @media (max-width: 768px) {
        .nav-tabs-recruitment .nav-link {
            padding: 10px 12px;
            font-size: 0.9rem;
        }
    }
</style>

{{-- JavaScript --}}
@include('humanresources.recruitment.partials._scripts')

@endsection
