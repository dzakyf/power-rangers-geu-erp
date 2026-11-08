@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Auth;

    $adminorHome = $adminorHome ?? 'home-credential';

    $credUser = Auth::user();
    $credSubMenu = $credUser->Sub_Menu ?? '';
    $credCurrent = trim(request()->path(), '/');

    $credActive = function ($path) use ($credCurrent) {
        $p = trim((string) $path, '/');
        if ($p === '') {
            return false;
        }
        return $credCurrent === $p || strpos($credCurrent, $p) === 0;
    };

    $credUrl = function ($path) {
        $p = trim((string) $path);
        if ($p === '') {
            return 'javascript:void(0);';
        }
        if ($p === '#' || strpos($p, 'http') === 0) {
            return $p;
        }
        return url($p);
    };

    $credMenus = DB::table('menu_lists')
        ->where('Modules', 'CREDENTIAL')
        ->where('Position', '>=', 2)
        ->orderBy('Urutan', 'ASC')
        ->get();

    $credGroups = [];
    $credPaths = [];

    foreach ($credMenus as $menu) {
        if ($credSubMenu === '' || strpos($credSubMenu, $menu->Menu_ID) === false) {
            continue;
        }
        $credGroups[$menu->Parent_Menu][] = $menu;
        if (!empty($menu->Path_Loc) && $menu->Path_Loc !== '#') {
            $credPaths[$menu->Path_Loc] = true;
        }
    }

    $credTop = $credGroups['CR00001'] ?? [];

    $credFallback = [
        [
            'name' => 'User Management',
            'items' => [
                ['name' => 'Create User', 'path' => 'credential-create-user'],
                ['name' => 'User Management', 'path' => 'credential-user-management'],
                ['name' => 'Organization Management', 'path' => 'credential-organization'],
            ],
        ],
        [
            'name' => 'Role Management',
            'items' => [
                ['name' => 'Role Management', 'path' => 'credential-role'],
                ['name' => 'Privilege Mapping', 'path' => 'credential-privilege-mapping'],
            ],
        ],
    ];

    $credAdminItems = [];
    foreach ([
        ['name' => 'Mail Log Management', 'path' => 'credential-maillog'],
        ['name' => 'Email Settings', 'path' => 'credential-email-settings-index'],
    ] as $item) {
        if (!isset($credPaths[$item['path']])) {
            $credAdminItems[] = $item;
        }
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
                    <h6 class="mb-0 text-dark">{{ $credUser->username ?? '' }}</h6>
                    <span class="text-muted app-sidebar__user-name text-sm">{{ $credUser->Department ?? '' }}</span>
                </div>
            </div>
        </div>
    </div>

    <ul class="side-menu">
        @if (count($credTop) > 0)
            @foreach ($credTop as $credDm)
                @php
                    $credSecond = $credGroups[$credDm->Menu_ID] ?? [];
                @endphp
                <li class="slide {{ $credActive($credDm->Path_Loc) ? 'active is-expanded' : '' }}">
                    <a class="side-menu__item {{ $credActive($credDm->Path_Loc) ? 'active' : '' }}"
                       data-bs-toggle="slide"
                       href="{{ $credUrl($credDm->Path_Loc) }}">
                        <i class="side-menu__icon bx bx-cube-alt"></i>
                        <span class="side-menu__label">{{ $credDm->Name }}</span>
                        <i class="angle fe fe-chevron-down"></i>
                    </a>
                    @if (count($credSecond) > 0)
                        <ul class="slide-menu">
                            @foreach ($credSecond as $credSb)
                                @php
                                    $credThird = $credGroups[$credSb->Menu_ID] ?? [];
                                @endphp
                                <li class="slide">
                                    <a class="slide-item" data-bs-toggle="sub-slide" href="{{ $credUrl($credSb->Path_Loc) }}">
                                        <span class="sub-side-menu__label">{{ $credSb->Name }}</span>
                                        @if (count($credThird) > 0)
                                            <i class="sub-angle fe fe-chevron-down"></i>
                                        @endif
                                    </a>
                                    @if (count($credThird) > 0)
                                        <ul class="sub-slide-menu">
                                            @foreach ($credThird as $credSm)
                                                <li>
                                                    <a class="sub-side-menu__item {{ $credActive($credSm->Path_Loc) ? 'active' : '' }}"
                                                       href="{{ $credUrl($credSm->Path_Loc) }}">{{ $credSm->Name }}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        @else
            @foreach ($credFallback as $credGroup)
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0);">
                        <i class="side-menu__icon bx bx-cube-alt"></i>
                        <span class="side-menu__label">{{ $credGroup['name'] }}</span>
                        <i class="angle fe fe-chevron-down"></i>
                    </a>
                    <ul class="slide-menu">
                        @foreach ($credGroup['items'] as $credItem)
                            <li>
                                <a class="slide-item {{ $credActive($credItem['path']) ? 'active' : '' }}"
                                   href="{{ $credUrl($credItem['path']) }}">{{ $credItem['name'] }}</a>
                            </li>
                        @endforeach
                    </ul>
                </li>
            @endforeach
        @endif

        @if (count($credAdminItems) > 0)
            <li class="slide">
                <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0);">
                    <i class="side-menu__icon bx bx-cube-alt"></i>
                    <span class="side-menu__label">Admin Management</span>
                    <i class="angle fe fe-chevron-down"></i>
                </a>
                <ul class="slide-menu">
                    @foreach ($credAdminItems as $credItem)
                        <li>
                            <a class="slide-item {{ $credActive($credItem['path']) ? 'active' : '' }}"
                               href="{{ $credUrl($credItem['path']) }}">{{ $credItem['name'] }}</a>
                        </li>
                    @endforeach
                </ul>
            </li>
        @endif
    </ul>

    <div class="slide-right" id="slide-right">
        <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24"><path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"/></svg>
    </div>
</div>
