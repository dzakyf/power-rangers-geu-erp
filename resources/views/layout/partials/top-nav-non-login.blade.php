<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth

?>
<nav class="navbar navbar-expand fixed-top top-menu">
    <a class="navbar-brand" href="/geu/hcga/public/">
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
        <!-- Mega Menu -->
		<ul class="left-nav d-none d-md-block navbar-nav">
			<li class="nav-item dropdown mega-menu">
				<a class="nav-link dropdown-toggle" href="$dm->Path_Loc" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<div class="mega-menu-btn">
						Sales
						<i data-feather="chevron-down" class="icon"></i>
					</div>
				</a>

				<div class="dropdown-menu">
					<div class="container">
						<div class="row">
							<div class="col-lg-3 col-md-6">
									
							<h5 class="title">Opportunity</h5>
							<a class="dropdown-item" href="sales-opportunity-new">New Leads</a>
							<a class="dropdown-item" href="sales-opportunity-fu-lists">Follow Up Lists</a>
							<a class="dropdown-item" href="sales-opportunity-qualified-lists">Qualified Lists</a>
							<a class="dropdown-item" href="sales-opportunity-closed-lists">Closed Lists</a>
							<a class="dropdown-item" href="sales-opportunity-registration">Registration</a>
							</div>
						</div>
					</div>
				</div>
			</li> 
		</ul>	
		  <ul class="navbar-nav right-nav ml-auto">
            


            <!-- Profile dropdown -->
            <li class="nav-item dropdown profile-nav-item">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <div class="menu-profile">
                         <span>admin</span> / <span>IT</span> / <span>   GEU-HO </span>&nbsp;&nbsp;
                        <img src="{{ asset('assets/images/profile.jpg') }}" alt="Profile Image" class="rounded-circle">
                    </div>
                </a>
                <div class="dropdown-menu">
                       <a class="dropdown-item" href="my-profile">
                        <i data-feather="user" class="icon"></i>
                        Profile
                    </a>
                     <a class="dropdown-item" href="change-password">
                        <i data-feather="key" class="icon"></i>
                        Change Password
                    </a>
                    <a class="dropdown-item" href="privilege-information">
                        <i data-feather="help-circle" class="icon"></i>
                        Privilege Information
                    </a>
                    <a class="dropdown-item" href="/">
                        <i data-feather="log-out" class="icon"></i>
                        Logout
                    </a>
                </div>
            </li>
        </ul>

        <!-- Right nav -->
      
    </div>
</nav>