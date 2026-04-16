<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$nama =  Auth::user()->Name;
$expired = "Wed, 18 Dec 2030 12:00:00 GMT;";
$dsess =  Auth::user()->Sub_Menu;
$dsess2 =  Auth::user()->Main_Menu;
?>
@extends('layout.mainlayout_depan')

@section('content-wrapper')
<!-- Main Content Header -->
<div class="main-content-header">

</div>
<!-- End Main Content Header -->

<div class="row">
<?php if (strpos($dsess,"MS00001")!== false) { ?>
    <div class="col-md-2 text-center">
        <a
            href="home-sales"
            role="button"
            aria-haspopup="true"
            aria-expanded="false"
            onclick="document.cookie='topnav=sales;expires=<?php echo $expired?>'"
            oncontextmenu="document.cookie='topnav=sales;expires=<?php echo $expired?>'">
            <div class="shadow bg-white text-center">
                <span class="text-center" style="font-size: 120px; color: Dodgerblue;">
                    <i class="icofont-handshake-deal"></i>
                </span>
                <h3 class="text-center">SALES</h3>
            </div>
        </a>
    </div>
    <?php } 
				if (strpos($dsess,"MS00002")!== false) { ?>
    <div class="col-md-2 text-center">
        <a
            href="home-project"
            role="button"
            aria-haspopup="true"
            aria-expanded="false"
            onclick="document.cookie='topnav=project;expires=<?php echo $expired?>'"
            oncontextmenu="document.cookie='topnav=project;expires=<?php echo $expired?>'">
            <div class="shadow bg-white text-center">
                <span class="text-center" style="font-size: 120px; color: Dodgerblue;">
                <i class="icofont-calculations"></i>
                </span>
                <h4 class="text-center">Project Management</h4>
            </div>
        </a>
    </div>
    <?php } 
				if (strpos($dsess,"MS00003")!== false) { ?>

    <div class="col-md-2 text-center">
        <a
            href="home-finance"
            role="button"
            aria-haspopup="true"
            aria-expanded="false"
            onclick="document.cookie='topnav=finance;expires=<?php echo $expired?>'"
            oncontextmenu="document.cookie='topnav=finance;expires=<?php echo $expired?>'">
            <div class="shadow bg-white text-center">
                <span class="text-center" style="font-size: 120px; color: Dodgerblue;">
                <i class="icofont-spreadsheet"></i>
                </span>
                <h3 class="text-center">Finance</h3>
            </div>
        </a>
    </div>
    <?php } 
				if (strpos($dsess,"MS00004")!== false) { ?>
    <div class="col-md-2 text-center">
        <a
            href="home-hrd"
            role="button"
            aria-haspopup="true"
            aria-expanded="false"
            onclick="document.cookie='topnav=hrd;expires=<?php echo $expired?>'"
            oncontextmenu="document.cookie='topnav=hrd;expires=<?php echo $expired?>'">
            <div class="shadow bg-white text-center">
                <span class="text-center" style="font-size: 120px; color: Dodgerblue;">
                <i class="icofont-users-social"></i>
                </span>
                <h4 class="text-center">Human Resources</h4>
            </div>
        </a>
    </div>
    <?php } 
				if (strpos($dsess,"MS00005")!== false) { ?>
    <div class="col-md-2 text-center">
        <a
            href="home-accounting"
            role="button"
            aria-haspopup="true"
            aria-expanded="false"
            onclick="document.cookie='topnav=accounting;expires=<?php echo $expired?>'"
            oncontextmenu="document.cookie='topnav=accounting;expires=<?php echo $expired?>'">
            <div class="shadow bg-white text-center">
                <span class="text-center" style="font-size: 120px; color: Dodgerblue;">
                <i class="icofont-chart-growth"></i>
                </span>
                <h3 class="text-center">Accounting</h3>
            </div>
        </a>
    </div>
    <?php } 
				if (strpos($dsess,"MS00006")!== false) { ?>

<div class="col-md-2 text-center">
        <a
            href="home-asset"
            role="button"
            aria-haspopup="true"
            aria-expanded="false"
            onclick="document.cookie='topnav=asset;expires=<?php echo $expired?>'"
            oncontextmenu="document.cookie='topnav=asset;expires=<?php echo $expired?>'">
            <div class="shadow bg-white text-center">
                <span class="text-center" style="font-size: 120px; color: Dodgerblue;">
                <i class="icofont-coins"></i>
                </span>
                <h5 class="text-center">Asset and Inventories</h5>
            </div>
        </a>
    </div>
    <?php } 
						if (strpos($dsess,"MS00007")!== false) { 
				?>
    <div class="col-md-2 text-center">
        <a
            href="home-procurement"
            role="button"
            aria-haspopup="true"
            aria-expanded="false"
            onclick="document.cookie='topnav=procurement;expires=<?php echo $expired?>'"
            oncontextmenu="document.cookie='topnav=procurement;expires=<?php echo $expired?>'">
            <div class="shadow bg-white text-center">
                <span class="text-center" style="font-size: 120px; color: Dodgerblue;">
                <i class="icofont-cart-alt"></i>
                </span>
                <h3 class="text-center">Procurement</h3>
            </div>
        </a>
    </div>
    <?php } 
					if (strpos($dsess,"MS00008")!== false) { 
				?>
    <div class="col-md-2 text-center">
        <a
            href="home-mymenu"
            role="button"
            aria-haspopup="true"
            aria-expanded="false"
            onclick="document.cookie='topnav=mymenu;expires=<?php echo $expired?>'"
            oncontextmenu="document.cookie='topnav=mymenu;expires=<?php echo $expired?>'">
            <div class="shadow bg-white text-center">
                <span class="text-center" style="font-size: 120px; color: Dodgerblue;">
                <i class="icofont-user"></i>
                </span>
                <h3 class="text-center"> My Menu
                </h3>
            </div>
        </a>
    </div>
    <?php } ?>
    <?php if (strpos($dsess,"MS00010")!== false) { 
    ?>
    <div class="col-md-2 text-center">
        <a
            href="home-businesspartner"
            role="button"
            aria-haspopup="true"
            aria-expanded="false"
            onclick="document.cookie='topnav=mitra;expires=<?php echo $expired?>'"
            oncontextmenu="document.cookie='topnav=mitra;expires=<?php echo $expired?>'">
            <div class="shadow bg-white text-center">
                <span class="text-center" style="font-size: 120px; color: Dodgerblue;">
                <i class="icofont-briefcase"></i>
                </span>
                <h3 class="text-center">Business Partner
                </h3>
            </div>
        </a>
    </div>
    <?php 
    }
    ?>
    <?php if (strpos($dsess,"MS00011")!== false) { 
    ?>
    <div class="col-md-2 text-center">
        <a
            href="home-cns"
            role="button"
            aria-haspopup="true"
            aria-expanded="false"
            onclick="document.cookie='topnav=mitra;expires=<?php echo $expired?>'"
            oncontextmenu="document.cookie='topnav=mitra;expires=<?php echo $expired?>'">
            <div class="shadow bg-white text-center">
                <span class="text-center" style="font-size: 120px; color: Dodgerblue;">
                <i class="icofont-speed-boat"></i>
                </span>
                <h3 class="text-center">CNS
                </h3>
            </div>
        </a>
    </div>
    <?php 
    }
    ?>
</div>




@endsection