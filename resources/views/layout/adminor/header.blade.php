@php
    use Illuminate\Support\Facades\Auth;
    $bpAuth = Auth::user();
    $adminorHome = $adminorHome ?? 'home-businesspartner';
@endphp
<div class="main-header side-header sticky nav nav-item">
    <div class="container-fluid main-container">
        <div class="main-header-left">
            <div class="app-sidebar__toggle mobile-toggle" data-bs-toggle="sidebar">
                <a class="open-toggle" href="javascript:void(0);"><i class="header-icons" data-eva="menu-outline"></i></a>
                <a class="close-toggle" href="javascript:void(0);"><i class="header-icons" data-eva="close-outline"></i></a>
            </div>
            <div class="responsive-logo">
                <a href="{{ url($adminorHome) }}" class="header-logo"><img src="{{ asset('adminor/img/brand/logo.webp') }}" class="logo-11" alt="logo"></a>
                <a href="{{ url($adminorHome) }}" class="header-logo"><img src="{{ asset('adminor/img/brand/logo-white.webp') }}" class="logo-1" alt="logo"></a>
            </div>
        </div>
        <button class="navbar-toggler nav-link icon navresponsive-toggler vertical-icon ms-auto" type="button"
                data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent-4"
                aria-controls="navbarSupportedContent-4" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fe fe-more-vertical header-icons navbar-toggler-icon"></i>
        </button>
        <div class="mb-0 navbar navbar-expand-lg navbar-nav-right responsive-navbar navbar-dark p-0 mg-lg-s-auto">
            <div class="collapse navbar-collapse" id="navbarSupportedContent-4">
                <div class="main-header-right">
                    <div class="dropdown nav-item main-header-notification">
                        <a class="new nav-link" href="javascript:void(0);">
                            <i class="fe fe-bell"></i><span class="pulse-danger"></span>
                        </a>
                        <div class="dropdown-menu" id="bpNotifMenu" style="width: 360px; max-height: 420px; overflow-y: auto;">
                            <div class="menu-header-content bg-primary text-start d-flex rounded-top-5">
                                <h6 class="menu-header-title text-white mb-0">Notifications</h6>
                            </div>
                            <div id="bpNotifList"><div class="p-3 text-center text-muted">Loading…</div></div>
                        </div>
                    </div>

                    <div class="dropdown main-profile-menu nav nav-item nav-link">
                        <a class="profile-user d-flex" href="javascript:void(0);">
                            <img src="{{ asset('assets/images/profile.jpg') }}" alt="profile" class="rounded-circle" style="width:32px;height:32px;">
                            <span class="ms-2 d-none d-lg-inline text-white">{{ $bpAuth->username ?? '' }}</span>
                        </a>
                        <div class="dropdown-menu">
                            <div class="main-header-profile header-img bg-primary rounded-top-5 p-3">
                                <h6 class="mb-0">{{ $bpAuth->username ?? '' }}</h6>
                                <span class="text-muted text-sm">{{ $bpAuth->Department ?? '' }} / {{ $bpAuth->Location ?? '' }}</span>
                            </div>
                            <a class="dropdown-item" href="{{ url('my-profile') }}"><i class="fe fe-user me-2"></i> Profile</a>
                            <a class="dropdown-item" href="{{ url('change-password') }}"><i class="fe fe-key me-2"></i> Change Password</a>
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

@push('rightmenu-script')
<script>
    $(function () {
        function bpLoadNotifications() {
            var list = $('#bpNotifList');
            $.ajax({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                url: "{{ url('notification-getall') }}",
                type: 'GET',
                success: function (data) {
                    list.empty();
                    if (!data || !data.data || data.data.length < 1) {
                        list.append('<div class="p-3 text-center text-muted">No notifications</div>');
                        return;
                    }
                    data.data.forEach(function (n) {
                        list.append('<a class="dropdown-item text-wrap" onclick="bpMarkRead(' + n.ID + ')">' +
                            '<strong>' + (n.Title || 'Notification') + '</strong><br>' +
                            '<small class="text-muted">' + (n.Created_At || '') + '</small></a>');
                    });
                },
                error: function () { list.html('<div class="p-3 text-center text-danger">Failed to load</div>'); }
            });
        }
        window.bpMarkRead = function (id) {
            $.post("{{ url('notification-read') }}?_token={{ csrf_token() }}", { id: id });
        };
        bpLoadNotifications();
    });
</script>
@endpush
