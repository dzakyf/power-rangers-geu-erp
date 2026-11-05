@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Auth;

    $adminorMenuModule = $adminorMenuModule ?? 'MITRA';
    $adminorHome = $adminorHome ?? 'home-businesspartner';

    $bpUser = Auth::user();
    $bpSubMenu = $bpUser->Sub_Menu ?? '';
    $bpCurrent = trim(request()->path(), '/');

    // Build an absolute URL for a menu Path_Loc; keep '#' and full URLs as-is.
    $bpUrl = function ($path) {
        $p = trim((string) $path);
        if ($p === '' || $p === '#' || strpos($p, 'javascript:') === 0) {
            return 'javascript:void(0);';
        }
        if (strpos($p, 'http://') === 0 || strpos($p, 'https://') === 0) {
            return $p;
        }
        return url($p);
    };

    $normalizePath = function ($path) {
        $p = trim((string) $path);
        if ($p === '' || $p === '#' || strpos($p, 'javascript:') === 0) {
            return '';
        }
        if (strpos($p, 'http://') === 0 || strpos($p, 'https://') === 0) {
            $p = parse_url($p, PHP_URL_PATH);
        }
        return trim(explode('?', (string) $p)[0], '/');
    };

    $allMenus = DB::table('menu_lists')
        ->where('Modules', $adminorMenuModule)
        ->orderBy('Urutan', 'ASC')
        ->get();

    $exactMatchExists = false;
    $bestPrefixMatchPath = null;
    $bestPrefixMatchLen = 0;

    foreach ($allMenus as $m) {
        $norm = $normalizePath($m->Path_Loc);
        if ($norm === '') {
            continue;
        }
        if ($bpCurrent === $norm || request()->is($norm)) {
            $exactMatchExists = true;
            break;
        }
        if (strpos($bpCurrent, $norm . '-') === 0 || strpos($bpCurrent, $norm . '/') === 0 || request()->is($norm . '/*')) {
            $len = strlen($norm);
            if ($len > $bestPrefixMatchLen) {
                $bestPrefixMatchLen = $len;
                $bestPrefixMatchPath = $norm;
            }
        }
    }

    $bpActive = function ($path) use ($bpCurrent, $normalizePath, $exactMatchExists, $bestPrefixMatchPath) {
        $norm = $normalizePath($path);
        if ($norm === '') {
            return false;
        }
        if ($exactMatchExists) {
            return $bpCurrent === $norm || request()->is($norm);
        }
        return $norm === $bestPrefixMatchPath;
    };

    $canAccess = function ($menuId) use ($bpSubMenu) {
        if ($bpSubMenu === '' || $bpSubMenu === null) {
            return true;
        }
        return strpos($bpSubMenu, $menuId) !== false;
    };

    $bpTop = $allMenus->where('Position', 2);
    $menuTree = [];

    foreach ($bpTop as $bpDm) {
        if (!$canAccess($bpDm->Menu_ID)) {
            continue;
        }

        $bpSecond = $allMenus->where('Parent_Menu', $bpDm->Menu_ID)->where('Position', 3);
        $dmActive = $bpActive($bpDm->Path_Loc);
        $secondTree = [];

        foreach ($bpSecond as $bpSb) {
            if (!$canAccess($bpSb->Menu_ID)) {
                continue;
            }

            $bpThird = $allMenus->where('Parent_Menu', $bpSb->Menu_ID)->where('Position', 4);
            $sbActive = $bpActive($bpSb->Path_Loc);
            $thirdTree = [];

            foreach ($bpThird as $bpSm) {
                if ($canAccess($bpSm->Menu_ID)) {
                    $smActive = $bpActive($bpSm->Path_Loc);
                    if ($smActive) {
                        $sbActive = true;
                    }
                    $thirdTree[] = [
                        'item' => $bpSm,
                        'active' => $smActive,
                    ];
                }
            }

            if ($sbActive) {
                $dmActive = true;
            }

            $secondTree[] = [
                'item' => $bpSb,
                'active' => $sbActive,
                'children' => $thirdTree,
            ];
        }

        $menuTree[] = [
            'item' => $bpDm,
            'active' => $dmActive,
            'children' => $secondTree,
        ];
    }
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
        @foreach ($menuTree as $dm)
            @php
                $hasSecond = count($dm['children']) > 0;
            @endphp
            <li class="slide {{ $dm['active'] ? 'active is-expanded' : '' }}">
                <a class="side-menu__item {{ $dm['active'] ? 'active' : '' }}"
                   @if ($hasSecond) data-bs-toggle="slide" @endif
                   href="{{ $bpUrl($dm['item']->Path_Loc) }}">
                    <i class="side-menu__icon bx bx-cube-alt"></i>
                    <span class="side-menu__label">{{ $dm['item']->Name }}</span>
                    @if ($hasSecond)
                        <i class="angle fe fe-chevron-down"></i>
                    @endif
                </a>
                @if ($hasSecond)
                    <ul class="slide-menu {{ $dm['active'] ? 'open' : '' }}" style="{{ $dm['active'] ? 'display: block;' : '' }}">
                        @foreach ($dm['children'] as $sb)
                            @php
                                $hasThird = count($sb['children']) > 0;
                            @endphp
                            @if ($hasThird)
                                <li class="sub-slide {{ $sb['active'] ? 'active is-expanded' : '' }}">
                                    <a class="slide-item {{ $sb['active'] ? 'active' : '' }}"
                                       data-bs-toggle="sub-slide"
                                       href="{{ $bpUrl($sb['item']->Path_Loc) }}">
                                        <span class="sub-side-menu__label">{{ $sb['item']->Name }}</span>
                                        <i class="sub-angle fe fe-chevron-down"></i>
                                    </a>
                                    <ul class="sub-slide-menu {{ $sb['active'] ? 'open' : '' }}" style="{{ $sb['active'] ? 'display: block;' : '' }}">
                                        @foreach ($sb['children'] as $sm)
                                            <li>
                                                <a class="sub-side-menu__item {{ $sm['active'] ? 'active' : '' }}"
                                                   href="{{ $bpUrl($sm['item']->Path_Loc) }}">{{ $sm['item']->Name }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                            @else
                                <li class="slide {{ $sb['active'] ? 'active' : '' }}">
                                    <a class="slide-item {{ $sb['active'] ? 'active' : '' }}"
                                       href="{{ $bpUrl($sb['item']->Path_Loc) }}">
                                        <span class="sub-side-menu__label">{{ $sb['item']->Name }}</span>
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                @endif
            </li>
        @endforeach
    </ul>

    <div class="slide-right" id="slide-right">
        <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24"><path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"/></svg>
    </div>
</div>

<script>
    (function () {
        function keepActiveMenuOpen() {
            var activeItems = document.querySelectorAll('.main-sidemenu .sub-side-menu__item.active, .main-sidemenu .slide-item.active');
            activeItems.forEach(function (el) {
                var subSlide = el.closest('.sub-slide');
                if (subSlide) {
                    subSlide.classList.add('is-expanded', 'active');
                    var subSlideA = subSlide.querySelector('a.slide-item');
                    if (subSlideA) subSlideA.classList.add('active');
                    var subMenu = subSlide.querySelector('.sub-slide-menu');
                    if (subMenu) {
                        subMenu.classList.add('open');
                        subMenu.style.display = 'block';
                    }
                }
                var slide = el.closest('.slide');
                if (slide) {
                    slide.classList.add('is-expanded', 'active');
                    var slideA = slide.querySelector('a.side-menu__item');
                    if (slideA) slideA.classList.add('active');
                    var slideMenu = slide.querySelector('.slide-menu');
                    if (slideMenu) {
                        slideMenu.classList.add('open');
                        slideMenu.style.display = 'block';
                    }
                }
            });
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', keepActiveMenuOpen);
        } else {
            keepActiveMenuOpen();
        }
    })();
</script>
