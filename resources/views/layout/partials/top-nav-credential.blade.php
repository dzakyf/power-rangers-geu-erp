<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

$Saya = Auth::user()->username;
$LSP = Auth::user()->Location;
$Priv = Auth::user()->Priv_ID;
$DOP = Auth::user()->DOP_Class;

$dsess = Auth::user()->Sub_Menu ?? '';
$credMenus = DB::table('menu_lists')
    ->where('Modules', 'CREDENTIAL')
    ->where('Position', '>=', 2)
    ->orderBy('Urutan', 'ASC')
    ->get();
$credGroups = [];
foreach ($credMenus as $m) {
    if (strpos($dsess, $m->Menu_ID) === false) continue;
    $credGroups[$m->Parent_Menu][] = $m;
}
?>
<nav class="navbar navbar-expand fixed-top top-menu">
    <a class="navbar-brand" href="{{ url('/') }}">
        <!-- Large logo -->
        <img src="{{ asset('/assets/images/large-logo.png') }}" alt="Logo" class="large-logo" height="55" align="center">
        <!-- Small logo -->
        <img src="{{ asset('/assets/images/small-logo.png') }}" alt="Small Logo" class="small-logo">
    </a>

    <!-- Burger menu -->
    <div class="burger-menu toggle-menu">
        <span class="top-bar"></span>
        <span class="middle-bar"></span>
        <span class="bottom-bar"></span>
    </div>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
       
		
		<ul class="left-nav d-none d-md-block navbar-nav">
            <li class="nav-item dropdown mega-menu">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <div class="mega-menu-btn">
                        Credential Management
                        <i data-feather="chevron-down" class="icon"></i>
                    </div>
                </a>

                <div class="dropdown-menu">
                    <div class="container">
                        <div class="row">
						
						@php
                            $groups = $credGroups['CR00001'] ?? [];
                        @endphp
                        @forelse($groups as $group)
                            @php
                                $children = $credGroups[$group->Menu_ID] ?? [];
                            @endphp
                            @if(count($children) > 0)
                            <div class="col-lg-3 col-md-6">
                                <h5 class="title">{{ $group->Name }}</h5>
                                @foreach($children as $child)
                                    <a class="dropdown-item" href="{{ $child->Path_Loc ?: '#' }}">{{ $child->Name }}</a>
                                @endforeach
                            </div>
                            @else
                            <div class="col-lg-3 col-md-6">
                                <a class="dropdown-item" href="{{ $group->Path_Loc ?: '#' }}"><strong>{{ $group->Name }}</strong></a>
                            </div>
                            @endif
                        @empty
                            <div class="col-lg-3 col-md-6">
                                <h5 class="title">User Management</h5>
                                <a class="dropdown-item" href="credential-create-user">Create User</a>
                                <a class="dropdown-item" href="credential-user-management">User Management</a>
                                <a class="dropdown-item" href="credential-organization">Organization Management</a>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <h5 class="title">Role Management</h5>
                                <a class="dropdown-item" href="credential-role">Role Management</a>
                                <a class="dropdown-item" href="credential-structure">Approval Management Structures</a>
                            </div>
                        @endforelse
  
							  
                        </div>
                    </div>
                </div>
            </li>
            </ul>
            <ul class="left-nav d-none d-md-block navbar-nav">
            <li class="nav-item dropdown mega-menu">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <div class="mega-menu-btn">
                        Admin Management
                        <i data-feather="chevron-down" class="icon"></i>
                    </div>
                </a>

                <div class="dropdown-menu">
                    <div class="container">
                        <div class="row">
						
                        <div class="col-lg-3 col-md-6">
                                <h5 class="title">Backend Management</h5>
                                <a class="dropdown-item" href="credential-maillog">Mail log Management</a>
                          </div> 
							  
                        </div>
                    </div>
                </div>
            </li>
        </ul>
		 @include("layout.partials.rightmenu");

        <!-- Right nav -->
        
		
    </div>
</nav>
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>