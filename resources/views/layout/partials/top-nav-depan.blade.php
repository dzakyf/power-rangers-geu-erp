<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

$Saya = Auth::user()->username;
$LSP = Auth::user()->Location;
$Priv = Auth::user()->Priv_ID;
$DOP = Auth::user()->DOP_Class;
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
       
        <!-- Right nav -->
         @include("layout.partials.rightmenu");

    </div>
</nav>
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>