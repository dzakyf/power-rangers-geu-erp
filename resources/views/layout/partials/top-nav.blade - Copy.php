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
		<?php
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
								</div>
								<div class="col-lg-3 col-md-6">
									<h5 class="title">Recruitment</h5>
									<a class="dropdown-item" href="recruitment-outstanding-requisition">Outstanding Requisition</a>
									<a class="dropdown-item" href="recruitment-requisition">Job Requisition</a>
									<a class="dropdown-item" href="recruitment-new">New Recruitment</a>
									<a class="dropdown-item" href="recruitment-data">Data Management</a>
									<a class="dropdown-item" href="recruitment-test-result">Test Results</a>
									<a class="dropdown-item" href="recruitment-notification">Notification</a>
									<a class="dropdown-item" href="recruitment-interview">Interview</a>
									<a class="dropdown-item" href="recruitment-candidate-analisys">Final User Interview</a>
									<a class="dropdown-item" href="recruitment-pending">Pending Lists</a>
									<a class="dropdown-item" href="recruitment-outstanding">Outstanding Report</a>
								</div>
								<div class="col-lg-3 col-md-6">
									<h5 class="title">Outsourcing</h5>
									<a class="dropdown-item" href="os-contract">Contract Management</a>
									<a class="dropdown-item" href="os-lists">OS/OT Lists</a>
									<a class="dropdown-item" href="os-conversion">Employee Conversion</a>
								</div>
								<div class="col-lg-3 col-md-6">
									<h5 class="title">Personel Data (Overhead)</h5>
									<a class="dropdown-item" href="employee-lists">Employee Lists</a>
									<a class="dropdown-item" href="employee-mutation">Mutation</a>
									<a class="dropdown-item" href="employee-promotion">Promotion</a>
								</div>
								<div class="col-lg-3 col-md-6">
									<h5 class="title">Other Data</h5>
									<a class="dropdown-item" href="resigned-employee">Resigned Employee</a>
								</div>
								<div class="col-lg-3 col-md-6">
									<h5 class="title">Permission/Leave</h5>
									<a class="dropdown-item" href="permission-new">New Request</a>
									<a class="dropdown-item" href="permission-approval">Approval</a>
									<a class="dropdown-item" href="permission-all">All Data</a>
								</div>
								<div class="col-lg-3 col-md-6">
									<h5 class="title">Attendant Log</h5>
									<a class="dropdown-item" href="attendant-entry">Entry Manual</a>
									<a class="dropdown-item" href="attendant-data">Data Management</a>
								</div>
								<div class="col-lg-3 col-md-6">
									<h5 class="title">Overtime </h5>
									<a class="dropdown-item" href="overtime-new">New Request</a>
									<a class="dropdown-item" href="overtime-approval">Approval</a>
									<a class="dropdown-item" href="overtime-all">All Data</a>
								</div>
								<div class="col-lg-3 col-md-6">
									<h5 class="title">KJK </h5>
									<a class="dropdown-item" href="kjk-new">New Request</a>
									<a class="dropdown-item" href="kjk-approval">Approval</a>
									<a class="dropdown-item" href="kjk-all">All Data</a>
								</div>
								<div class="col-lg-3 col-md-6">
									<h5 class="title">Payroll</h5>
									<a class="dropdown-item" href="payroll-tax">Master Tax Reduction</a>
									<a class="dropdown-item" href="payroll-lists">Master Benefit and Costs</a>
									<a class="dropdown-item" href="payroll-group">Master Group</a>
									<a class="dropdown-item" href="payroll-template-master">Payroll Master Template</a>
									<a class="dropdown-item" href="payroll-employee">Employee Salary Setup</a>
									<a class="dropdown-item" href="payroll-generator">Payroll Generator</a>
									<a class="dropdown-item" href="payroll-approval">Payroll Approval</a>
									<a class="dropdown-item" href="payroll-management-approval">Payroll Management Approval</a>
								</div>
								<div class="col-lg-3 col-md-6">
									<h5 class="title">Reports</h5>
									<a class="dropdown-item" href="hr-report-tax">Tax Report</a>
									<a class="dropdown-item" href="hr-report-bpjs">BPJS Report</a>
									<a class="dropdown-item" href="hr-report-outstanding-recruitment">Outstanding Recruitment</a>
								</div>
							</div>
						</div>
					</div>
				</li>
			</ul>
			
	<?php 
		
		} 
	?>
	
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
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <h5 class="title">Opportunity</h5>
                                <a class="dropdown-item" href="sales-opportunity-new">New Opportunity</a>
                                <a class="dropdown-item" href="sales-opportunity-lists">Opportunity Lists</a>
                                <a class="dropdown-item" href="sales-opportunity-addendum">PO Addendum</a>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <h5 class="title">Monitoring</h5>
                                <a class="dropdown-item" href="sales-opportunity-active">Active Opportunities</a>
                                <a class="dropdown-item" href="sales-opportunity-outstanding">Outstanding Opportunities</a>
                                <a class="dropdown-item" href="sales-opportunity-finalize-opportunity">Finished Opportunities</a>
                                <a class="dropdown-item" href="sales-opportunity-pipeline">Pipeline Summaries</a>
                                <a class="dropdown-item" href="sales-opportunity-pipeline-sales">Pipeline By Sales</a>
                                <a class="dropdown-item" href="sales-opportunity-pipeline-customer">Pipeline By Customers</a>
                                <a class="dropdown-item" href="sales-opportunity-pipeline-customer2">Pipeline By Customers Ver 2</a>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <h5 class="title">Pre Proforma</h5>
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
                                <a class="dropdown-item" href="finance-reimbursement-history">History</a>
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
                                <a class="dropdown-item" href="finance-invoice-return">Return / Cancelation</a>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <h5 class="title">Account Receivable</h5>
                                <!--<a class="dropdown-item" href="finance-ar-receive">Receive from Billing</a>-->
                                <a class="dropdown-item" href="finance-ar-delivery">Delivery to Customer</a>
                                <a class="dropdown-item" href="finance-ar-monitoring">AR Monitoring</a>
								<a class="dropdown-item" href="finance-ar-history">AR History</a>
                            </div>
							<div class="col-lg-3 col-md-6">
                                <h5 class="title">Account Payable</h5>
                                <!--<a class="dropdown-item" href="finance-ar-receive">Receive from Billing</a>-->
                                <a class="dropdown-item" href="finance-ap-receive">Receive From Purchasing</a>
                                <a class="dropdown-item" href="finance-ap-unapproved">Unapproved AP</a>
                                <a class="dropdown-item" href="finance-ap-management">AP Management</a>
								<a class="dropdown-item" href="finance-ap-history">AP History</a>
                            </div>
							<div class="col-lg-3 col-md-6">
                                <h5 class="title">Fast Reports</h5>
                                <a class="dropdown-item" href="finance-report-ar-aging">AR Aging</a>
                                <a class="dropdown-item" href="finance-report-invoice-position">Invoice Position</a>
                                <a class="dropdown-item" href="finance-report-unbilled">Unbilled Proforma</a>
                                <a class="dropdown-item" href="finance-report-unbilled-po">Unbilled PO</a>
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
                                <h5 class="title">Budgeting</h5>
                                <a class="dropdown-item" href="finance-budget-management">Budget Management</a>
                                <a class="dropdown-item" href="finance-budget-transaction">Transaction</a>
                                <a class="dropdown-item" href="finance-budget-history">History</a>
                                <a class="dropdown-item" href="finance-budget-summary">Summary</a>
                            </div>
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
                                <h5 class="title">Project Posting</h5>
                                <a class="dropdown-item" href="accounting-project-posting">PO Posting</a>
                                <a class="dropdown-item" href="accounting-project-posted">Posted PO</a>
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
							<div class="col-lg-3 col-md-6">
                                <h5 class="title">Recurring</h5>
                                <a class="dropdown-item" href="accounting-recurring-cash-out">From Cash Out</a>
                                <a class="dropdown-item" href="accounting-recurring-bank-out">From Bank Out</a>
                                <a class="dropdown-item" href="accounting-recurring-amortization">Amortization</a>
                                <a class="dropdown-item" href="accounting-recurring-data">Data</a>
                            </div>
							<div class="col-lg-3 col-md-6">
                                <h5 class="title">Payroll</h5>
                                <a class="dropdown-item" href="accounting-payroll-issued">Approval to Issued</a>
                                <a class="dropdown-item" href="accounting-payroll-journal">Posting to Journal</a>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
		<?php 
		
		} 
		if ($Priv == 'ADM' || $Priv == 'GA' || $Priv == 'PURCHASING' || $Priv == 'MARSAL') { ?>
		
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
                                <a class="dropdown-item" href="project-new-opportunity">From Opportunity</a> 
								<a class="dropdown-item" href="project-lists">Data Project</a>

                                
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <h5 class="title">Project Management</h5>
                                <a class="dropdown-item" href="project-team">Teams and People</a>
                                <a class="dropdown-item" href="project-organization">Organization</a>
                                <a class="dropdown-item" href="project-issues">Issues</a>
                                <a class="dropdown-item" href="project-documents">Documents</a>
                                <a class="dropdown-item" href="project-activity">Activity Update</a>
                                <a class="dropdown-item" href="project-time">Time Tracking</a>
                            </div>
							<div class="col-lg-3 col-md-6">
                                <h5 class="title">Project Costing</h5>
                                <a class="dropdown-item" href="project-reimbursement">Reimbursement</a>
                                <a class="dropdown-item" href="project-purchase">Purchasing</a>
                                <a class="dropdown-item" href="project-stock">Stock Request</a>  
								<a class="dropdown-item" href="project-hrd">Human Resources</a>
                            </div>
							<div class="col-lg-3 col-md-6">
                                <h5 class="title">Approval Costing</h5>
                                <a class="dropdown-item" href="approval-reimbursement">Reimbursement Approval</a>
                                <a class="dropdown-item" href="approval-purchase">Purchase Approval</a>
                                <a class="dropdown-item" href="approval-stock">Stock Request Approval</a>  
								<a class="dropdown-item" href="project-hrd-approval">Human Resources Approval</a>
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
								<a class="dropdown-item" href="inventory-request-management">Stock Request Management</a>  
                                <a class="dropdown-item" href="inventory-adjustment">Adjustments</a>
                                <a class="dropdown-item" href="inventory-stock-count">Stock Count</a>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
		<?php 
		
		} 
		    
		if ($Priv == 'ADM' || $Priv == 'PURCHASING' || $Priv == 'HRD') { 
		
		?>
		
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
                                <a class="dropdown-item" href="master-product-group">Product Group</a>
                                <a class="dropdown-item" href="master-product-sub-group">Product Sub Group</a>
                                <a class="dropdown-item" href="master-product-sub-sub-group">Product Sub Sub Group</a>
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
                                <a class="dropdown-item" href="po-outstanding">Outstanding</a>
                                <a class="dropdown-item" href="po-payment-monitoring">Payment Monitoring</a>
                                <a class="dropdown-item" href="po-vendor-summary">Vendor Summary</a>
                            </div>
							<div class="col-lg-3 col-md-6">
                                <h5 class="title">Contract &amp; Invoice Vendor</h5>
                                <a class="dropdown-item" href="po-contract-data">Contract Data</a>
								<a class="dropdown-item" href="finance-ap-manual">Manual Invoice</a>
                                <a class="dropdown-item" href="po-receive-invoice">Receive Invoice</a>
                                <a class="dropdown-item" href="po-invoice-history">Histories</a>
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
                         <span>{{Auth::user()->username}}</span> / <span>{{Auth::user()->Department}}</span> / <span>   {{Auth::user()->Location}} </span>&nbsp;&nbsp;
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