<header class="navbar pcoded-header navbar-expand-lg navbar-light">
    <div class="m-header">
        <a class="mobile-menu" id="mobile-collapse1" href="#!"><span></span></a>
        <a href="index.html" class="b-brand">
            <div class="b-bg">
                <i class="feather icon-trending-up"><img src="{{ asset('logo.png') }}"/></i>
            </div>
            <span class="b-title">{{ __('MKT') }}</span>
        </a>
    </div>
    <a class="mobile-menu" id="mobile-header" href="#!">
        <i class="feather icon-more-horizontal"></i>
    </a>
    <div class="collapse navbar-collapse">

        <ul class="navbar-nav ml-auto">

            <li>
                <div class="dropdown drp-user">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <img src="{{asset('Foto/'. Auth::user()->Foto)}}" class="img-radius" width="40">
                        <span>{{Auth::user()->username}}</span> <span> /  {{Auth::user()->Location}} </span>&nbsp;&nbsp;

                    </a>
                    <div class="dropdown-menu dropdown-menu-right profile-notification">
                        <div class="pro-head">
                            <img src="{{asset('Foto/'. Auth::user()->Foto)}}" class="img-radius" alt="User-Profile-Image">
                            <span>{{Auth::user()->username}}</span>
                            <a href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();" class="dud-logout" title="Logout">
                                <i class="feather icon-log-out"></i>
                            </a>
                        </div>
                        <ul class="pro-body">
                            <li><a href="#!" onclick=loadmodule("changePassword")  class="dropdown-item"><i class="feather icon-settings"></i>Change Password</a></li>
                            <li><a href="#!" class="dropdown-item"><i class="feather icon-user"></i>Profile</a></li>
                            <li><a href="#!" class="dropdown-item"><i class="feather icon-mail"></i> My Messages</a></li>

                        </ul>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</header>
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>
<!-- [ Header ] end -->
