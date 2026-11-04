@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Auth;

    $adminorMenuModule = $adminorMenuModule ?? 'MITRA';
    $adminorHome = $adminorHome ?? 'home-businesspartner';

    $bpUser = Auth::user();
    $bpSubMenu = $bpUser->Sub_Menu ?? '';
    $bpCurrent = trim(request()->path(), '/');

    // Helper untuk mengecek apakah path tertentu adalah menu yang sedang aktif
    $bpActive = function ($path) use ($bpCurrent) {
        $p = trim((string) $path, '/');
        if ($p === '' || $p === '#' || strpos($p, 'javascript:') === 0) {
            return false;
        }
        return $bpCurrent === $p || strpos($bpCurrent, $p . '/') === 0 || strpos($bpCurrent, $p) === 0;
    };

    // Build an absolute URL for a menu Path_Loc; keep '#' and full URLs as-is.
    $bpUrl = function ($path) {
        $p = trim((string) $path);
        if ($p === '') {
            return 'javascript:void(0);';
        }
        if ($p === '#' || strpos($p, 'http') === 0 || strpos($p, 'javascript:') === 0) {
            return $p;
        }
        return url($p);
    };

    $bpTop = DB::table('menu_lists')
        ->where('Modules', $adminorMenuModule)
        ->where('Position', 2)
        ->orderBy('Urutan', 'ASC')
        ->get();
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
                    $bpSecond = DB::table('menu_lists')
                        ->where('Modules', $adminorMenuModule)
                        ->where('Parent_Menu', $bpDm->Menu_ID)
                        ->where('Position', 3)
                        ->orderBy('Urutan', 'ASC')
                        ->get();

                    // Cek apakah ada menu turunan di Level 2 atau Level 3 yang aktif
                    $topHasActiveChild = false;
                    $level2Data = [];

                    foreach ($bpSecond as $bpSb) {
                        if (strpos($bpSubMenu, $bpSb->Menu_ID) === false) {
                            continue;
                        }

                        $bpThird = DB::table('menu_lists')
                            ->where('Modules', $adminorMenuModule)
                            ->where('Parent_Menu', $bpSb->Menu_ID)
                            ->where('Position', 4)
                            ->orderBy('Urutan', 'ASC')
                            ->get();

                        $secondIsDirectActive = $bpActive($bpSb->Path_Loc);
                        $secondHasActiveChild = false;

                        foreach ($bpThird as $bpSm) {
                            if (strpos($bpSubMenu, $bpSm->Menu_ID) !== false && $bpActive($bpSm->Path_Loc)) {
                                $secondHasActiveChild = true;
                                break;
                            }
                        }

                        $subExpanded = ($secondIsDirectActive || $secondHasActiveChild);
                        if ($subExpanded) {
                            $topHasActiveChild = true;
                        }

                        $level2Data[] = [
                            'item' => $bpSb,
                            'third' => $bpThird,
                            'isDirectActive' => $secondIsDirectActive,
                            'hasActiveChild' => $secondHasActiveChild,
                            'isExpanded' => $subExpanded,
                        ];
                    }

                    $topIsDirectActive = $bpActive($bpDm->Path_Loc);
                    $topExpanded = ($topIsDirectActive || $topHasActiveChild);
                @endphp
                <li class="slide {{ $topExpanded ? 'active is-expanded' : '' }}">
                    <a class="side-menu__item {{ $topExpanded ? 'active' : '' }}"
                       data-bs-toggle="slide"
                       href="{{ $bpUrl($bpDm->Path_Loc) }}">
                        <i class="side-menu__icon bx bx-cube-alt"></i>
                        <span class="side-menu__label">{{ $bpDm->Name }}</span>
                        <i class="angle fe fe-chevron-down"></i>
                    </a>
                    @if (count($level2Data) > 0)
                        <ul class="slide-menu {{ $topExpanded ? 'open' : '' }}" style="{{ $topExpanded ? 'display: block;' : '' }}">
                            @foreach ($level2Data as $l2)
                                @php
                                    $bpSb = $l2['item'];
                                    $bpThird = $l2['third'];
                                    $subExpanded = $l2['isExpanded'];
                                    $hasThird = count($bpThird) > 0;
                                @endphp
                                <li class="{{ $hasThird ? 'sub-slide' : 'slide' }} {{ $subExpanded ? 'is-expanded' : '' }}">
                                    <a class="{{ $hasThird ? 'sub-side-menu__item' : 'slide-item' }} {{ $subExpanded ? 'active' : '' }}"
                                       @if ($hasThird) data-bs-toggle="sub-slide" @endif
                                       href="{{ $bpUrl($bpSb->Path_Loc) }}">
                                        <span class="sub-side-menu__label">{{ $bpSb->Name }}</span>
                                        @if ($hasThird)
                                            <i class="sub-angle fe fe-chevron-down"></i>
                                        @endif
                                    </a>
                                    @if ($hasThird)
                                        <ul class="sub-slide-menu {{ $subExpanded ? 'open' : '' }}" style="{{ $subExpanded ? 'display: block;' : '' }}">
                                            @foreach ($bpThird as $bpSm)
                                                @if (strpos($bpSubMenu, $bpSm->Menu_ID) !== false)
                                                    @php
                                                        $thirdActive = $bpActive($bpSm->Path_Loc);
                                                    @endphp
                                                    <li class="{{ $thirdActive ? 'active' : '' }}">
                                                        <a class="sub-side-menu__item {{ $thirdActive ? 'active' : '' }}"
                                                           href="{{ $bpUrl($bpSm->Path_Loc) }}">{{ $bpSm->Name }}</a>
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
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

<script>
    // Memastikan menu aktif dan seluruh parent tree terbuka otomatis saat DOM siap
    document.addEventListener("DOMContentLoaded", function() {
        try {
            var activeItems = document.querySelectorAll(".app-sidebar .sub-side-menu__item.active, .app-sidebar .slide-item.active, .app-sidebar .side-menu__item.active");
            activeItems.forEach(function(el) {
                // Buka semua parent UL dan tambahkan kelas is-expanded pada LI
                var parent = el.parentElement;
                while (parent && !parent.classList.contains("side-menu")) {
                    if (parent.tagName === "UL" && (parent.classList.contains("slide-menu") || parent.classList.contains("sub-slide-menu"))) {
                        parent.style.display = "block";
                        parent.classList.add("open");
                    }
                    if (parent.tagName === "LI" && (parent.classList.contains("slide") || parent.classList.contains("sub-slide"))) {
                        parent.classList.add("is-expanded");
                        var link = parent.querySelector(":scope > a");
                        if (link) {
                            link.classList.add("active");
                        }
                    }
                    parent = parent.parentElement;
                }
            });
        } catch (e) {
            console.warn("Sidebar auto-expand handler:", e);
        }
    });
</script>
