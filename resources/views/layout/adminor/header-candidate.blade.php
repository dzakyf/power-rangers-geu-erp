@php
    use Illuminate\Support\Facades\Auth;
    $candAuth = Auth::user();
    $isAdminControl = $candAuth && (
        strtolower((string) ($candAuth->username ?? '')) === 'admin'
        || strtoupper((string) ($candAuth->Priv_ID ?? '')) === 'ADM'
        || (method_exists($candAuth, 'hasRole') && $candAuth->hasRole('Admin'))
    );
    $activeTaskId = $activeTaskId ?? (session('candidate_preview_task_id') ?: ($candAuth->username ?? ''));
@endphp
<div class="main-header side-header sticky nav nav-item">
    <div class="container-fluid main-container">
        <div class="main-header-left">
            <div class="app-sidebar__toggle mobile-toggle" data-bs-toggle="sidebar">
                <a class="open-toggle" href="javascript:void(0);"><i class="header-icons" data-eva="menu-outline"></i></a>
                <a class="close-toggle" href="javascript:void(0);"><i class="header-icons" data-eva="close-outline"></i></a>
            </div>
            <div class="responsive-logo">
                <a href="{{ url('/candidate/dashboard') }}" class="header-logo"><img src="{{ asset('adminor/img/brand/logo.webp') }}" class="logo-11" alt="logo"></a>
                <a href="{{ url('/candidate/dashboard') }}" class="header-logo"><img src="{{ asset('adminor/img/brand/logo-white.webp') }}" class="logo-1" alt="logo"></a>
            </div>
            @if($isAdminControl)
                <span class="badge bg-warning text-dark ms-3 d-none d-md-inline-flex align-items-center">
                    <i class="fa fa-shield-alt me-1"></i> Admin Control ({{ $activeTaskId }})
                </span>
            @endif
        </div>
        <button class="navbar-toggler nav-link icon navresponsive-toggler vertical-icon ms-auto" type="button"
                data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent-4"
                aria-controls="navbarSupportedContent-4" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fe fe-more-vertical header-icons navbar-toggler-icon"></i>
        </button>
        <div class="mb-0 navbar navbar-expand-lg navbar-nav-right responsive-navbar navbar-dark p-0 mg-lg-s-auto">
            <div class="collapse navbar-collapse" id="navbarSupportedContent-4">
                <div class="main-header-right">
                    @if($isAdminControl)
                    <div class="nav-item me-2">
                        <a class="nav-link d-flex align-items-center" href="{{ url('/recruitment-data') }}" title="Kembali ke Recruitment Data">
                            <i class="fe fe-arrow-left-circle"></i>
                            <span class="d-none d-md-inline ms-1 small fw-semibold">HRD Recruitment</span>
                        </a>
                    </div>
                    @endif

                    <div class="dropdown main-profile-menu nav nav-item nav-link">
                        <a class="profile-user d-flex align-items-center" href="javascript:void(0);">
                            <img src="{{ asset('assets/images/profile.jpg') }}" alt="profile" class="rounded-circle" style="width:32px;height:32px;">
                            <span class="ms-2 d-none d-lg-inline text-white">{{ $candAuth->Name ?? $candAuth->username ?? 'Candidate' }}</span>
                        </a>
                        <div class="dropdown-menu">
                            <div class="main-header-profile header-img bg-primary rounded-top-5 p-3">
                                <h6 class="mb-0 text-white">{{ $candAuth->Name ?? $candAuth->username ?? 'Candidate' }}</h6>
                                <span class="text-white-50 text-sm">Task ID: {{ $activeTaskId }}</span>
                            </div>
                            <a class="dropdown-item" href="{{ url('/candidate/profile') }}"><i class="fe fe-user me-2"></i> Data Diri & Profil</a>
                            <a class="dropdown-item" href="{{ url('/candidate/documents') }}"><i class="fe fe-folder me-2"></i> Dokumen Lamaran</a>
                            <a class="dropdown-item" href="{{ url('/candidate/progress') }}"><i class="fe fe-activity me-2"></i> Progress Rekrutmen</a>
                            <a class="dropdown-item" href="{{ url('/candidate/password') }}"><i class="fe fe-key me-2"></i> Ubah Password</a>
                            <a class="dropdown-item" href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fe fe-log-out me-2"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
