<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

$Saya = Auth::user()->username;
$LSP = Auth::user()->Location;
$Priv = Auth::user()->Priv_ID;
$DOP = Auth::user()->DOP_Class;
echo $Priv;exit;
?>
<nav class="navbar navbar-expand fixed-top top-menu">
    <a class="navbar-brand" href="/">
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
        <?php if ($Priv == 'ADM' || $Priv == 'MARSAL') { ?> 
        <ul class="left-nav d-none d-md-block navbar-nav">
			<li class="nav-item dropdown mega-menu">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <div class="mega-menu-btn">
                        Sales
                        <i data-feather="chevron-down" class="icon"></i>
                    </div>
                </a>

                <div class="dropdown-menu">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-3 col-md-6">
                                <h5 class="title">Master Data</h5>
                                <a class="dropdown-item" href="sales-master-sales">Sales Person</a>
                                <a class="dropdown-item" href="sales-customer-lists">Customer Lists</a>
                                <a class="dropdown-item" href="sales-customer-tariff">Tariff Management</a>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <h5 class="title">Opportunity</h5>
                                <a class="dropdown-item" href="sales-opportunity-new">New Opportunity</a>
                                <a class="dropdown-item" href="sales-opportunity-lists">Opportunity Lists</a>
                                <a class="dropdown-item" href="sales-opportunity-activity">Activity</a>
                                <a class="dropdown-item" href="sales-opportunity-activity-online-document">Online Documents</a>
                                <a class="dropdown-item" href="sales-opportunity-activity-document-upload">Documents Upload</a>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <h5 class="title">Opportunity-Product</h5>
                                <a class="dropdown-item" href="sales-opportunity-outstanding">Outstanding Opportunity</a>
                                <a class="dropdown-item" href="sales-opportunity-job-requisition">Job Requisition</a>
                                <a class="dropdown-item" href="sales-opportunity-recruitment">Recruitment</a>
                                <a class="dropdown-item" href="sales-opportunity-candidate-analisys">Candidate Details</a>
                                <a class="dropdown-item" href="sales-opportunity-cost-allowance">Candidate Costs and Allowance</a>
                                <a class="dropdown-item" href="sales-opportunity-finalize-opportunity">Finalize Opportunity</a>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <h5 class="title">Pre Proformaned</h5>
                                <a class="dropdown-item" href="sales-proforma-initiate">PO Collection</a>
                                <a class="dropdown-item" href="sales-proforma-ba">BA Collection</a>
                                <a class="dropdown-item" href="sales-proforma-attachment">Pre Proforma and Attachments</a>
                                <a class="dropdown-item" href="sales-proforma-posting">Posting to Proforma</a>
                                <a class="dropdown-item" href="sales-proforma-approved">Approved Proforma</a>
                            </div>
                        </div>
                    </div>
                </div>
            </li> 
        </ul>
		<?php
		} 
		    
		if ($Priv == 'ADM' || $Priv == 'FIN_ACC' || $Priv == 'FIN') { ?> 

		<ul class="left-nav d-none d-md-block navbar-nav">
            <li class="nav-item dropdown mega-menu">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <div class="mega-menu-btn">
                        Finance
                        <i data-feather="chevron-down" class="icon"></i>
                    </div>
                </a>

                <div class="dropdown-menu">
                    <div class="container">
                        <div class="row">
						 <div class="col-lg-3 col-md-6">
                                <h5 class="title">Reimbursement</h5>
                                <a class="dropdown-item" href="finance-reimbursement-approval">Approval</a>
                          </div>
                            <div class="col-lg-3 col-md-6">
                                <h5 class="title">Cash</h5>
                                <a class="dropdown-item" href="finance-cash-in">Cash In</a>
                                <a class="dropdown-item" href="finance-cash-out">Cash Out</a>
                                <a class="dropdown-item" href="finance-cash-transfer">Cash Transfer</a>
                                <a class="dropdown-item" href="finance-cash-advance">Cash Advance</a>
                                <a class="dropdown-item" href="finance-cash-deposit">Cash Deposit</a>
								<a class="dropdown-item" href="finance-cash-handle">Cash Handler</a>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <h5 class="title">Bank</h5>
                                <a class="dropdown-item" href="finance-bank-in">Bank In</a>
                                <a class="dropdown-item" href="finance-bank-out">Bank Out</a>
                                <a class="dropdown-item" href="finance-bank-transfer">Bank Transfer</a>
                                <a class="dropdown-item" href="finance-bank-withdrawal">Withdrawal</a>
                                <a class="dropdown-item" href="finance-bank-advance">Bank Advance</a>

                            </div>
                            <div class="col-lg-3 col-md-6">
                                <h5 class="title">Proforma and Invoicing</h5>
                                <a class="dropdown-item" href="finance-proforma-outstanding">Outstanding Proforma</a>
                                <a class="dropdown-item" href="finance-proforma-create-invoice">Create Invoice</a>
                                <a class="dropdown-item" href="finance-invoice-approval">Approval</a>
                                <a class="dropdown-item" href="finance-invoice-approved">Approved Invoice</a>
                                <a class="dropdown-item" href="finance-invoice-submit-cc">Submit to Credit Control</a>
                                <a class="dropdown-item" href="finance-invoice-return">Return / Cancelation</a>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <h5 class="title">Account Receivable</h5>
                                <a class="dropdown-item" href="finance-ar-receive">Receive from Billing</a>
                                <a class="dropdown-item" href="finance-ar-delivery">Delivery to Customer</a>
                                <a class="dropdown-item" href="finance-ar-monitoring">AR Monitoring</a>
								<a class="dropdown-item" href="finance-ar-history">AR History</a>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
		<?php 
		
		} 
		    
		if ($Priv == 'ADM' || $Priv == 'ACCOUNTING') { ?> 		
		<ul class="left-nav d-none d-md-block navbar-nav">
            <li class="nav-item dropdown mega-menu">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <div class="mega-menu-btn">
                        Accounting
                        <i data-feather="chevron-down" class="icon"></i>
                    </div>
                </a>

                <div class="dropdown-menu">
                    <div class="container">
                        <div class="row">
							<div class="col-lg-3 col-md-6">
                                <h5 class="title">Finance Posting Approval</h5>
                                <a class="dropdown-item" href="accounting-cash-in">Cash In</a>
                                <a class="dropdown-item" href="accounting-cash-out">Cash Out</a>
                                <a class="dropdown-item" href="accounting-cash-transfer">Cash Transfer</a>
                                <a class="dropdown-item" href="accounting-cash-advance">Cash Advance</a>
                                <a class="dropdown-item" href="accounting-cash-deposit">Cash Deposit</a>
                                <a class="dropdown-item" href="accounting-bank-in">Bank In</a>
                                <a class="dropdown-item" href="accounting-bank-out">Bank Out</a>
                                <a class="dropdown-item" href="accounting-bank-transfer">Bank Transfer</a>
								<a class="dropdown-item" href="accounting-cash-advance">Bank Advance</a>

                                <a class="dropdown-item" href="accounting-bank-withdrawal">Withdrawal</a>
                            </div>
							
                            <div class="col-lg-3 col-md-6">
                                <h5 class="title">Master Data</h5>
                                <a class="dropdown-item" href="accounting-master-bank">Bank Master</a>
                                <a class="dropdown-item" href="accounting-master-location">Locations</a>
                                <a class="dropdown-item" href="accounting-master-coa">Chart of Account</a>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <h5 class="title">Journal Transaction</h5>
                                <a class="dropdown-item" href="accounting-beginning-balance">Beginning Balance</a>
                                <a class="dropdown-item" href="accounting-journal-transaction">Transactions</a>
                                <a class="dropdown-item" href="accounting-journal-posted">Posted Journal</a>
                                <a class="dropdown-item" href="accounting-journal-closing">Closing </a>
                            </div>
							<div class="col-lg-3 col-md-6">
                                <h5 class="title">Report</h5>
                                <a class="dropdown-item" href="accounting-report-general-ledgers">General Ledgers</a>
                                <a class="dropdown-item" href="accounting-report-trial-balance">Trial Balance</a>
                                <a class="dropdown-item" href="accounting-report-cash-flow">Cash Flow</a>
                                <a class="dropdown-item" href="accounting-report-profit-and-loss">Profit And Loss </a>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
		<?php 
		
		} 
		if ($Priv == 'ADM' || $Priv == 'GA' || $Priv == 'PURCHASING') { ?>
		
		<ul class="left-nav d-none d-md-block navbar-nav">
            <li class="nav-item dropdown mega-menu">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <div class="mega-menu-btn">
                        Project Management
                        <i data-feather="chevron-down" class="icon"></i>
                    </div>
                </a>

                <div class="dropdown-menu">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-3 col-md-6">
                                <h5 class="title">Create Data</h5>
                                <a class="dropdown-item" href="project-new">New Project</a>
                                <a class="dropdown-item" href="project-new-opportunity">From Opportunity</a>                                <a class="dropdown-item" href="project-lists">Data Project</a>

                                
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <h5 class="title">Project Management</h5>
                                <a class="dropdown-item" href="project-organization">Organization</a>
                                <a class="dropdown-item" href="project-team">Teams and People</a>
                                <a class="dropdown-item" href="project-issues">Issues</a>
                                <a class="dropdown-item" href="project-depreciation">Documents</a>
                                <a class="dropdown-item" href="project-activity">Activity Update</a>
                                <a class="dropdown-item" href="project-time">Time Tracking</a>
                            </div>
							<div class="col-lg-3 col-md-6">
                                <h5 class="title">Job Costing</h5>
                                <a class="dropdown-item" href="project-reimbursement">Reimburesement</a>
                                <a class="dropdown-item" href="project-purchase">Purchasing</a>
                                <a class="dropdown-item" href="project-stock">Stock Request</a>  
								<a class="dropdown-item" href="project-hrd">Human Resources</a>
                            </div>
							<div class="col-lg-3 col-md-6">
                                <h5 class="title">Report</h5>
                                <a class="dropdown-item" href="project-report-summary">Summary</a>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
		<?php 
		
		}     
		if ($Priv == 'ADM' || $Priv == 'GA' || $Priv == 'PURCHASING') { ?>
		
		<ul class="left-nav d-none d-md-block navbar-nav">
            <li class="nav-item dropdown mega-menu">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <div class="mega-menu-btn">
                        Asset and Inventories
                        <i data-feather="chevron-down" class="icon"></i>
                    </div>
                </a>

                <div class="dropdown-menu">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-3 col-md-6">
                                <h5 class="title">Master Data</h5>
                                <a class="dropdown-item" href="asset-master-product-code">Product Code</a>
                                <a class="dropdown-item" href="asset-master-bin">Bin Location</a>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <h5 class="title">Asset Management</h5>
                                <a class="dropdown-item" href="asset-management-lists">Asset Lists</a>
                                <a class="dropdown-item" href="asset-management-incoming">Incoming Asset</a>
                                <a class="dropdown-item" href="asset-management-depreciation">Depreciations</a>
                                <a class="dropdown-item" href="asset-management-moving">Asset Moving </a>
                            </div>
							<div class="col-lg-3 col-md-6">
                                <h5 class="title">Inventories</h5>
                                <a class="dropdown-item" href="inventory-lists">Inventories</a>
                                <a class="dropdown-item" href="inventory-incoming">Incoming Part</a>
                                <a class="dropdown-item" href="inventory-request">Stock Request</a>  
								<a class="dropdown-item" href="inventory-stock-count">Stock Count</a>
                                <a class="dropdown-item" href="inventory-adjustment">Adjustments</a>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
		<?php 
		
		} 
		    
		if ($Priv == 'ADM' || $Priv == 'PURCHASING' || || $Priv == 'HRD' ) { ?>
		
		<ul class="left-nav d-none d-md-block navbar-nav">
            <li class="nav-item dropdown mega-menu">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <div class="mega-menu-btn">
                        Procurement
                        <i data-feather="chevron-down" class="icon"></i>
                    </div>
                </a>

                <div class="dropdown-menu">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-3 col-md-6">
                                <h5 class="title">Master Data</h5>
                                <a class="dropdown-item" href="purchasing-master-vendor">Vendors</a>
                                <a class="dropdown-item" href="purchasing-master-product">Product Code</a>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <h5 class="title">Request</h5>
                                <a class="dropdown-item" href="purchasing-request-new">New Request</a>
                                <a class="dropdown-item" href="purchasing-request-approval">Approval</a>
                                <a class="dropdown-item" href="purchasing-request-outstanding">Outstanding Request</a>
                                <a class="dropdown-item" href="purchasing-request-cancelation">Cancelation</a>
                            </div>
							<div class="col-lg-3 col-md-6">
                                <h5 class="title">Purchase Orders</h5>
                                <a class="dropdown-item" href="purchasing-order-collection">Request Collections</a>
                                <a class="dropdown-item" href="purchasing-order-vendor-selection">Vendor Selection</a>
                                <a class="dropdown-item" href="purchasing-order-price-input">Price Input</a>  
								<a class="dropdown-item" href="purchasing-order-approval">Approval</a>
                                <a class="dropdown-item" href="purchasing-order-approved">Approved PO</a>
								<a class="dropdown-item" href="purchasing-order-cancelation">Cancelation</a>
                            </div>
							<div class="col-lg-3 col-md-6">
                                <h5 class="title">Monitoring</h5>
                                <a class="dropdown-item" href="po-oustanding">Outstanding</a>
                                <a class="dropdown-item" href="po-payment-monitoring">Payment Monitoring</a>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
		<?php 
		
		} 
		    
		if ($Priv == 'ADM' || $Priv == 'HRD') { ?>
		<ul class="left-nav d-none d-md-block navbar-nav">
				<li class="nav-item dropdown mega-menu">
					<a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<div class="mega-menu-btn">
							Human Resources
							<i data-feather="chevron-down" class="icon"></i>
						</div>
					</a>

					<div class="dropdown-menu">
						<div class="container">
							<div class="row">
								<div class="col-lg-3 col-md-6">
									<h5 class="title">Master Data</h5>
									<a class="dropdown-item" href="hrd-master-directorat">Directorat</a>
									<a class="dropdown-item" href="hrd-master-department">Department</a>
									<a class="dropdown-item" href="hrd-master-section">Section</a>
									<a class="dropdown-item" href="hrd-master-position">Position</a>
									<a class="dropdown-item" href="hrd-master-holiday">Holiday</a>
									<a class="dropdown-item" href="hrd-master-permission">Permission/Leave</a>
									<a class="dropdown-item" href="hrd-master-overtime">Overtime</a>
								</div>
								<div class="col-lg-3 col-md-6">
									<h5 class="title">Recruitment</h5>
									<a class="dropdown-item" href="recruitment-requisition">Job Requisition</a>
									<a class="dropdown-item" href="recruitment-new">New Recruitment</a>
									<a class="dropdown-item" href="recruitment-data">Data Management</a>
									<a class="dropdown-item" href="recruitment-document">Document Archiving</a>
									<a class="dropdown-item" href="recruitment-notification">Notification</a>
									<a class="dropdown-item" href="recruitment-interview">Interview</a>
									<a class="dropdown-item" href="recruitment-candidate-analisys">Candidate Analysis</a>
								</div>
								<div class="col-lg-3 col-md-6">
									<h5 class="title">Pre Employment</h5>
									<a class="dropdown-item" href="inventory-lists">Contract Management</a>
									<a class="dropdown-item" href="inventory-incoming">Employees Data</a>
									<a class="dropdown-item" href="inventory-request">Employee Conversion</a>
									<Br>									
									<h5 class="title">Personel Data</h5>
									<a class="dropdown-item" href="inventory-lists">Employee Lists</a>
									<a class="dropdown-item" href="inventory-incoming">Mutation</a>
									<a class="dropdown-item" href="inventory-incoming">Promotion</a>
								</div>
								
								<div class="col-lg-3 col-md-6">
									<h5 class="title">Permission/Leave</h5>
									<a class="dropdown-item" href="inventory-lists">New Request</a>
									<a class="dropdown-item" href="inventory-incoming">Approval</a>
									<a class="dropdown-item" href="inventory-incoming">All Data</a>
									<a class="dropdown-item" href="inventory-incoming">Leave Data Config</a>
								</div>
								<div class="col-lg-3 col-md-6">
									<h5 class="title">Overtime</h5>
									<a class="dropdown-item" href="inventory-lists">New Request</a>
									<a class="dropdown-item" href="inventory-incoming">Approval</a>
									<a class="dropdown-item" href="inventory-incoming">All Data</a>
								</div>
							</div>
						</div>
					</div>
				</li>
			</ul>
			
	<?php 
		
		} 
	?>
		
        <!-- Right nav -->
        <ul class="navbar-nav right-nav ml-auto">
            


            <!-- Profile dropdown -->
            <li class="nav-item dropdown profile-nav-item">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <div class="menu-profile">
                         <span>{{Auth::user()->username}}</span> / <span>{{Auth::user()->Department}}</span> <span> /  {{Auth::user()->Location}} </span>&nbsp;&nbsp;
                        <img src="{{ asset('assets/images/profile.jpg') }}" alt="Profile Image" class="rounded-circle">
                    </div>
                </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="/pages/profile">
                        <i data-feather="user" class="icon"></i>
                        Profile
                    </a>
                    <a class="dropdown-item" href="/apps/inbox">
                        <i data-feather="inbox" class="icon"></i>
                        Change Password
                    </a>
                    <a class="dropdown-item" href="/apps/chat">
                        <i data-feather="help-circle" class="icon"></i>
                        Privilege Information
                    </a>
                    <a class="dropdown-item" href="/pages/profile-settings">
                        <i data-feather="settings" class="icon"></i>
                        Settings
                    </a>
                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                        <i data-feather="log-out" class="icon"></i>
                        Logout
                    </a>
                </div>
            </li>
        </ul>
    </div>
</nav>
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>