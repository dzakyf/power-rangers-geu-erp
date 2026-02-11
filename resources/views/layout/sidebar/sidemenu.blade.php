<?php

use Illuminate\Support\Facades\Auth;

$Priv = Auth::user()->Priv_ID;
?>

<div class="sidemenu-area sidemenu-toggle default">
    <nav class="sidemenu navbar navbar-expand navbar-light hide-nav-title">
        <div class="navbar-collapse collapse">
            <div class="navbar-nav">
                <?php if ($Priv == 'ADM' || $Priv == 'MARSAL') { ?>
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="home-sales" role="button" aria-haspopup="true" aria-expanded="false">
                            <div class="dropdown-title">
                                <i data-feather="grid" class="icon"></i>
                                <span class="title">
                                    Sales
                                </span>
                            </div>
                        </a>

                    </div>
                <?php } ?>

                <?php if ($Priv == 'ADM' || $Priv == 'FIN_ACC' || $Priv == 'FIN' || $Priv == 'MARSAL' || $Priv == 'HRD' || $Priv == 'ACCOUNTING' || $Priv == 'GA' || $Priv == 'PURCHASING') { ?>
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="home-project" role="button" aria-haspopup="true" aria-expanded="false">
                            <div class="dropdown-title">
                                <i data-feather="book" class="icon"></i>
                                <span class="title">
                                    Project Management
                                </span>
                            </div>
                        </a>

                    </div>

                <?php } ?>

                <?php if ($Priv == 'ADM' || $Priv == 'FIN_ACC' || $Priv == 'FIN' || $Priv == 'ACCOUNTING') { ?>
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="home-finance" role="button" aria-haspopup="true" aria-expanded="false">
                            <div class="dropdown-title">
                                <i data-feather="bar-chart-2" class="icon"></i>
                                <span class="title">
                                    Finance
                                </span>
                            </div>
                        </a>

                    </div>
                <?php } ?>
                <?php if ($Priv == 'ADM' || $Priv == 'HRD') { ?>
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="home-hrd" role="button" aria-haspopup="true" aria-expanded="false">
                            <div class="dropdown-title">
                                <i data-feather="user" class="icon"></i>
                                <span class="title">
                                    Human Resources
                                </span>
                            </div>
                        </a>

                    </div>

                <?php } ?>

                <?php if ($Priv == 'ADM' || $Priv == 'FIN_ACC' || $Priv == 'ACCOUNTING') { ?>
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="home-accounting" role="button" aria-haspopup="true" aria-expanded="false">
                            <div class="dropdown-title">
                                <i data-feather="bar-chart" class="icon"></i>
                                <span class="title">
                                    Accounting
                                </span>
                            </div>
                        </a>

                    </div>
                <?php } ?>

                <?php if ($Priv == 'ADM' || $Priv == 'FIN_ACC' || $Priv == 'ACCOUNTING' || $Priv == 'GA' || $Priv == 'PURCHASING') { ?>
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="home-asset" role="button" aria-haspopup="true" aria-expanded="false">
                            <div class="dropdown-title">
                                <i data-feather="book" class="icon"></i>
                                <span class="title">
                                    Asset and Inventories
                                </span>
                            </div>
                        </a>

                    </div>
                <?php } ?>
                <?php if ($Priv == 'ADM' || $Priv == 'FIN_ACC' || $Priv == 'ACCOUNTING' || $Priv == 'GA' || $Priv == 'PURCHASING') { ?>
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="home-procurement" role="button" aria-haspopup="true" aria-expanded="false">
                            <div class="dropdown-title">
                                <i data-feather="book" class="icon"></i>
                                <span class="title">
                                    Procurement
                                </span>
                            </div>
                        </a>

                    </div>
                <?php } ?>
            </div>
        </div>
    </nav>
</div>