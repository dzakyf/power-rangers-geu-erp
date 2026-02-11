<?php

use Illuminate\Support\Facades\Auth;

$Priv = Auth::user()->Priv_ID ?? '';
$expired = "Wed, 18 Dec 2030 12:00:00 GMT;";
$dsess =  Auth::user()->Sub_Menu ?? '';
$dsess2 =  Auth::user()->Main_Menu ?? '';
?>

<div class="sidemenu-area sidemenu-toggle default">
    <nav class="sidemenu navbar navbar-expand navbar-light hide-nav-title">
        <div class="navbar-collapse collapse">
            <div class="navbar-nav">
                <?php if (strpos($dsess,"MS00001")!== false || $dsess == '') { ?>
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="home-sales" role="button" aria-haspopup="true" aria-expanded="false" onclick="document.cookie='topnav=sales;expires=<?php echo $expired?>'"  oncontextmenu="document.cookie='topnav=sales;expires=<?php echo $expired?>'">
                            <div class="dropdown-title">
                                <i data-feather="grid" class="icon"></i>
                                <span class="title">
                                    Sales
                                </span>
                            </div>
                        </a>

                    </div>
                <?php } 
				if (strpos($dsess,"MS00002")!== false) { ?>

                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="home-project" role="button" aria-haspopup="true" aria-expanded="false" onclick="document.cookie='topnav=project;expires=<?php echo $expired?>'">
                            <div class="dropdown-title">
                                <i data-feather="book" class="icon"></i>
                                <span class="title">
                                    Project Management
                                </span>
                            </div>
                        </a>

                    </div>

                <?php } 
				if (strpos($dsess,"MS00003")!== false) { ?>

                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="home-finance" role="button" aria-haspopup="true" aria-expanded="false" onclick="document.cookie='topnav=finance;expires=<?php echo $expired?>'">
                            <div class="dropdown-title">
                                <i data-feather="bar-chart-2" class="icon"></i>
                                <span class="title">
                                    Finance
                                </span>
                            </div>
                        </a>

                    </div>
                <?php } 
				if (strpos($dsess,"MS00004")!== false) { ?>
					<div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="home-hrd" role="button" aria-haspopup="true" aria-expanded="false" onclick="document.cookie='topnav=hrd;expires=<?php echo $expired?>'">
                            <div class="dropdown-title">
                                <i data-feather="user" class="icon"></i>
                                <span class="title">
                                    Human Resources
                                </span>
                            </div>
                        </a>

                    </div>

                  <?php } 
				if (strpos($dsess,"MS00005")!== false) { ?>
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="home-accounting" role="button" aria-haspopup="true" aria-expanded="false" onclick="document.cookie='topnav=accounting;expires=<?php echo $expired?>'">
                            <div class="dropdown-title">
                                <i data-feather="bar-chart" class="icon"></i>
                                <span class="title">
                                    Accounting
                                </span>
                            </div>
                        </a>

                    </div>
                  <?php } 
				if (strpos($dsess,"MS00006")!== false) { ?>
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="home-asset" role="button" aria-haspopup="true" aria-expanded="false" onclick="document.cookie='topnav=asset;expires=<?php echo $expired?>'">
                            <div class="dropdown-title">
                                <i data-feather="book" class="icon"></i>
                                <span class="title">
                                    Asset and Inventories
                                </span>
                            </div>
                        </a>

                    </div>
                  <?php } 
						if (strpos($dsess,"MS00007")!== false) { 
				?>
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="home-procurement" role="button" aria-haspopup="true" aria-expanded="false" onclick="document.cookie='topnav=procurement;expires=<?php echo $expired?>'">
                            <div class="dropdown-title">
                                <i data-feather="book" class="icon"></i>
                                <span class="title">
                                    Procurement
                                </span>
                            </div>
                        </a>

                    </div>
				
                <?php } 
					if (strpos($dsess,"MS00009")!== false) { 
				?>
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="home-dashboard" role="button" aria-haspopup="true" aria-expanded="false" onclick="document.cookie='topnav=dashboard;expires=<?php echo $expired?>'">
                            <div class="dropdown-title">
                                <i data-feather="book" class="icon"></i>
                                <span class="title">
                                    Dashboard
                                </span>
                            </div>
                        </a>

                    </div>
				<?php } 
					if (strpos($dsess,"MS00008")!== false) { 
				?>
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="home-mymenu" role="button" aria-haspopup="true" aria-expanded="false" onclick="document.cookie='topnav=mymenu;expires=<?php echo $expired?>'">
                            <div class="dropdown-title">
                                <i data-feather="book" class="icon"></i>
                                <span class="title">
                                    Business Partner
                                </span>
                            </div>
                        </a>

                    </div>	
                <?php } 
				
						if (strpos($dsess2,"CREDENTIAL")!== false) { 
				?>
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="home-credential" role="button" aria-haspopup="true" aria-expanded="false" onclick="document.cookie='topnav=credential;expires=<?php echo $expired?>'">
                            <div class="dropdown-title">
                                <i data-feather="settings" class="icon"></i>
                                <span class="title">
                                    Credentials
                                </span>
                            </div>
                        </a>

                    </div>
				<?php } ?>	
            </div>
        </div>
    </nav>
</div>