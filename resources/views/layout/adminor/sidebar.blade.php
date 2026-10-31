@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Auth;

    $adminorMenuModule = $adminorMenuModule ?? 'MITRA';
    $adminorHome = $adminorHome ?? 'home-businesspartner';

    $bpUser = Auth::user();
    $bpSubMenu = $bpUser->Sub_Menu ?? '';
    $bpCurrent = trim(request()->path(), '/');

    $bpActive = function ($path) use ($bpCurrent) {
        $p = trim((string) $path, '/');
        if ($p === '') {
            return false;
        }
        return $bpCurrent === $p || strpos($bpCurrent, $p) === 0;
    };

    // Build an absolute URL for a menu Path_Loc; keep '#' and full URLs as-is.
    $bpUrl = function ($path) {
        $p = trim((string) $path);
        if ($p === '') {
            return 'javascript:void(0);';
        }
        if ($p === '#' || strpos($p, 'http') === 0) {
            return $p;
        }
        return url($p);
    };

    $bpTop = DB::table('menu_lists')->where('Modules', $adminorMenuModule)->where('Position', 2)->orderBy('Urutan', 'ASC')->get();
@endphp

<div class="main-sidebar-header active">
    <a class="desktop-logo logo-light active" href="{{ url($adminorHome) }}">
        <img src="{{ asset('adminor/img/brand/logo.webp') }}" class="main-logo" alt="logo">
    </a>
    <a class="desktop-logo logo-dark active" href="{{ url($adminorHome) }}">
        <img src="{{ asset('adminor/img/brand/logo-white.webp') }}" class="main-logo" alt="logo">
    </a>
    <a class="logo-icon mobile-logo icon-light active" href="{{ url($adminorHome) }}">
        <img src="{{ asset('adminor/img/brand/favicon.png') }}" alt="logo">
    </a>
    <a class="logo-icon mobile-logo icon-dark active" href="{{ url($adminorHome) }}">
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
                <div class="user-info mb-4">
                    <h6 class="mb-0 text-dark">{{ $bpUser->username ?? '' }}</h6>
                    <span class="text-muted app-sidebar__user-name text-sm">{{ $bpUser->Department ?? '' }}</span>
                </div>
            </div>
        </div>
    </div>

    <ul class="side-menu">
        @foreach ($bpTop as $bpDm)
            @if (strpos($bpSubMenu, $bpDm->Menu_ID) !== false)
                @php
                    $bpSecond = DB::table('menu_lists')->where('Modules', $adminorMenuModule)->where('Parent_Menu', $bpDm->Menu_ID)->where('Position', 3)->orderBy('Urutan', 'ASC')->get();
                @endphp
                <li class="slide {{ $bpActive($bpDm->Path_Loc) ? 'active is-expanded' : '' }}">
                    <a class="side-menu__item {{ $bpActive($bpDm->Path_Loc) ? 'active' : '' }}"
                       data-bs-toggle="slide"
                       href="{{ $bpUrl($bpDm->Path_Loc) }}">
                        <i class="side-menu__icon bx bx-cube-alt"></i>
                        <span class="side-menu__label">{{ $bpDm->Name }}</span>
                        <i class="angle fe fe-chevron-down"></i>
                    </a>
                    @if (count($bpSecond) > 0)
                        <ul class="slide-menu">
                            @foreach ($bpSecond as $bpSb)
                                @if (strpos($bpSubMenu, $bpSb->Menu_ID) !== false)
                                    @php
                                        $bpThird = DB::table('menu_lists')->where('Modules', $adminorMenuModule)->where('Parent_Menu', $bpSb->Menu_ID)->where('Position', 4)->orderBy('Urutan', 'ASC')->get();
                                    @endphp
                                    <li class="slide">
                                        <a class="slide-item" data-bs-toggle="sub-slide" href="{{ $bpUrl($bpSb->Path_Loc) }}">
                                            <span class="sub-side-menu__label">{{ $bpSb->Name }}</span>
                                            @if (count($bpThird) > 0)
                                                <i class="sub-angle fe fe-chevron-down"></i>
                                            @endif
                                        </a>
                                        @if (count($bpThird) > 0)
                                            <ul class="sub-slide-menu">
                                                @foreach ($bpThird as $bpSm)
                                                    @if (strpos($bpSubMenu, $bpSm->Menu_ID) !== false)
                                                        <li>
                                                            <a class="sub-side-menu__item {{ $bpActive($bpSm->Path_Loc) ? 'active' : '' }}"
                                                               href="{{ $bpUrl($bpSm->Path_Loc) }}">{{ $bpSm->Name }}</a>
                                                        </li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        @endif
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endif
        @endforeach
    </ul>

    <div class="slide-right" id="slide-right">
        <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24"><path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"/></svg>
    </div>
</div>
