@php
    use Illuminate\Support\Facades\Auth;

    $candUser = Auth::user();
    $candCurrent = trim(request()->path(), '/');
    $activeTaskId = $activeTaskId ?? (session('candidate_preview_task_id') ?: ($candUser->username ?? ''));

    $candMenus = [
        [
            'label' => 'Dashboard',
            'path'  => 'candidate/dashboard',
            'icon'  => 'fa-home',
        ],
        [
            'label' => 'Data Diri & Profil',
            'path'  => 'candidate/profile',
            'icon'  => 'fa-user',
        ],
        [
            'label' => 'Dokumen Lamaran',
            'path'  => 'candidate/documents',
            'icon'  => 'fa-file-upload',
        ],
        [
            'label' => 'Progress & Konfirmasi',
            'path'  => 'candidate/progress',
            'icon'  => 'fa-tasks',
        ],
        [
            'label' => 'Asesmen & Hasil Tes',
            'path'  => 'candidate/assessment',
            'icon'  => 'fa-clipboard-check',
        ],
        [
            'label' => 'Ubah Password',
            'path'  => 'candidate/password',
            'icon'  => 'fa-key',
        ],
    ];
@endphp

<div class="main-sidebar-header active">
    <a class="desktop-logo logo-light active" href="{{ url('/candidate/dashboard') }}">
        <img src="{{ asset('adminor/img/brand/logo.webp') }}" class="main-logo" alt="logo">
    </a>
    <a class="desktop-logo logo-dark active" href="{{ url('/candidate/dashboard') }}">
        <img src="{{ asset('adminor/img/brand/logo-white.webp') }}" class="main-logo" alt="logo">
    </a>
    <a class="logo-icon mobile-logo icon-light active" href="{{ url('/candidate/dashboard') }}">
        <img src="{{ asset('adminor/img/brand/favicon.png') }}" alt="logo">
    </a>
    <a class="logo-icon mobile-logo icon-dark active" href="{{ url('/candidate/dashboard') }}">
        <img src="{{ asset('adminor/img/brand/favicon-white.png') }}" alt="logo">
    </a>
</div>

<div class="main-sidemenu">
    <div class="main-sidebar-loggedin">
        <div class="app-sidebar__user">
            <div class="dropdown user-pro-body text-center">
                <div class="user-pic">
                    <img src="{{ asset('assets/images/profile.jpg') }}" alt="user-img" class="rounded-circle mCS_img_loaded">
                </div>
                <div class="user-info mb-3">
                    <h6 class="mb-0 text-dark">{{ $candUser->Name ?? $candUser->username ?? 'Candidate' }}</h6>
                    <span class="text-muted app-sidebar__user-name text-sm">Task ID: {{ $activeTaskId }}</span>
                </div>
            </div>
        </div>
    </div>

    <ul class="side-menu">
        @foreach($candMenus as $menu)
            @php
                $isActive = ($candCurrent === $menu['path'] || strpos($candCurrent, $menu['path'] . '/') === 0);
            @endphp
            <li class="slide {{ $isActive ? 'active is-expanded' : '' }}">
                <a class="side-menu__item {{ $isActive ? 'active' : '' }}" href="{{ url($menu['path']) }}">
                    <i class="fa {{ $menu['icon'] }} side-menu__icon"></i>
                    <span class="side-menu__label">{{ $menu['label'] }}</span>
                </a>
            </li>
        @endforeach
    </ul>
</div>

<style>
    .app-sidebar {
        display: flex !important;
        flex-direction: column !important;
        height: 100vh !important;
        max-height: 100vh !important;
        overflow: hidden !important;
    }

    .main-sidebar-header {
        flex-shrink: 0 !important;
    }

    .main-sidemenu {
        flex: 1 1 auto !important;
        min-height: 0 !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
    }
</style>
