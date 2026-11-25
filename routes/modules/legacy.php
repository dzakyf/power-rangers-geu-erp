<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controller\Accounting\MasterOfficeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UploaderController;
use App\Http\Controllers\ProcurementController;
use App\Http\Controllers\BroadcastController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\AdminController;

    Route::get('/dashboard-history-proforma', function () {
        return view('dashboard.history-proforma');
    });

    Route::get('/xml-dashboard-history-proforma', function () {
        return view('dashboard.xml.history-proforma');
    });

    Route::get('/dashboard-report-proforma', function () {
        return view('dashboard.report-proforma');
    });

    Route::get('/xml-dashboard-report-proforma', function () {
        return view('dashboard.xml.report-proforma');
    });

    Route::get('/dashboard-sales-po', function () {
        return view('dashboard.sales-po');
    });

    Route::get('/xml-dashboard-sales-po', function () {
        return view('dashboard.xml.sales-po');
    });

    //Route::get('/', 'AdminController@index');
    Route::get('/', [AdminController::class, 'index']);

    Route::post('/logpost', function () {
        return view('/user/logproc');
    });

    Route::get('/home-dashboard', function () {
        return view('home-dashboard');
    });

    Route::get('/home-sales', function () {
        return view('home-sales');
    });

    Route::get('/home-accounting', function () {
        return view('home-accounting');
    });

    Route::get('/home-procurement', function () {
        return view('home-procurement');
    });

    Route::get('/home-project', function () {
        return view('home-project');
    });

    Route::get('/mailingcheck', function () {
        return view('scheduler.mailer');
    });

    Route::get('/recurring', function () {
        return view('scheduler.recurring');
    });

    Route::get('/approvalpr', function () {
        return view('scheduler.approvepr');
    });

    Route::get('/approvepr', function () {
        return view('scheduler.approvepr');
    });

    Route::get('/approvalpo', function () {
        return view('scheduler.approvepo');
    });

    Route::get('/approveproforma', function () {
        return view('scheduler.approveproforma');
    });

    Route::get('/approvalreimbursement', function () {
        return view('scheduler.approvalreimbursement');
    });

    Route::get('/approvalreimbursement_approve', function () {
        return view('scheduler.approvalreimbursement_approve');
    });

    Route::get('/approvalreimbursement_reject', function () {
        return view('scheduler.approvalreimbursement_reject');
    });

    Route::get('/approvalreimbursement2', function () {
        return view('scheduler.approvalreimbursement2');
    });

    Route::get('/approvalreimbursement2_approve', function () {
        return view('scheduler.approvalreimbursement2_approve');
    });

    Route::get('/approvalreimbursement2_reject', function () {
        return view('scheduler.approvalreimbursement2_reject');
    });

    Route::get('/approvalreimbursement3', function () {
        return view('scheduler.approvalreimbursement3');
    });

    Route::get('/approvalreimbursement3_approve', function () {
        return view('scheduler.approvalreimbursement3_approve');
    });

    Route::get('/approvalreimbursement3_reject', function () {
        return view('scheduler.approvalreimbursement3_reject');
    });

    Route::get('/approvalreimbursement4', function () {
        return view('scheduler.approvalreimbursement4');
    });

    Route::get('/approvalreimbursement4_approve', function () {
        return view('scheduler.approvalreimbursement4_approve');
    });

    Route::get('/approvalreimbursement4_reject', function () {
        return view('scheduler.approvalreimbursement4_reject');
    });

    Route::get('/approvalbankadvance', function () {
        return view('scheduler.approvalbankadvance');
    });

    Route::get('/approvalbankadvance_approve', function () {
        return view('scheduler.approvalbankadvance_approve');
    });

    Route::get('/approvalbankadvance_reject', function () {
        return view('scheduler.approvalbankadvance_reject');
    });

    Route::get('/approvalbankadvance2', function () {
        return view('scheduler.approvalbankadvance2');
    });

    Route::get('/approvalbankadvance2_approve', function () {
        return view('scheduler.approvalbankadvance2_approve');
    });

    Route::get('/approvalbankadvance2_reject', function () {
        return view('scheduler.approvalbankadvance2_reject');
    });

    Route::get('/approvalbankadvance3', function () {
        return view('scheduler.approvalbankadvance3');
    });

    Route::get('/approvalbankadvance3_approve', function () {
        return view('scheduler.approvalbankadvance3_approve');
    });

    Route::get('/approvalbankadvance3_reject', function () {
        return view('scheduler.approvalbankadvance3_reject');
    });

    Route::get('/approvalbankadvance4', function () {
        return view('scheduler.approvalbankadvance4');
    });

    Route::get('/approvalbankadvance4_approve', function () {
        return view('scheduler.approvalbankadvance4_approve');
    });

    Route::get('/approvalbankadvance4_reject', function () {
        return view('scheduler.approvalbankadvance4_reject');
    });

    Route::get('/approvalapvendor', function () {
        return view('scheduler.approvalapvendor');
    });

    Route::get('/approvalapvendor_approve', function () {
        return view('scheduler.approvalapvendor_approve');
    });

    Route::get('/approvalapvendor_reject', function () {
        return view('scheduler.approvalapvendor_reject');
    });

    Route::get('/approvalapvendor2', function () {
        return view('scheduler.approvalapvendor2');
    });

    Route::get('/approvalapvendor2_approve', function () {
        return view('scheduler.approvalapvendor2_approve');
    });

    Route::get('/approvalapvendor2_reject', function () {
        return view('scheduler.approvalapvendor2_reject');
    });

    Route::get('/approvalapvendor3', function () {
        return view('scheduler.approvalapvendor3');
    });

    Route::get('/approvalapvendor3_approve', function () {
        return view('scheduler.approvalapvendor3_approve');
    });

    Route::get('/approvalapvendor3_reject', function () {
        return view('scheduler.approvalapvendor3_reject');
    });

    Route::get('/approvalpo_approve', function () {
        return view('scheduler.approvepo_approve');
    });

    Route::get('/approvalpo_reject', function () {
        return view('scheduler.approvepo_reject');
    });

    Route::get('/approveproformafinal', function () {
        return view('scheduler.approveproformafinal');
    });

    Route::get('/approvalreceiveinvoice', function () {
        return view('scheduler.approvalreceiveinvoice');
    });

    Route::get('/approveproformafinal_approve', function () {
        return view('scheduler.approveproformafinal_approve');
    });

    Route::get('/approveproforma_approve', function () {
        return view('scheduler.approveproforma_approve');
    });

    Route::get('/approveproforma_reject', function () {
        return view('scheduler.approveproforma_reject');
    });

    Route::get('/approvalpr_approve', function () {
        return view('scheduler.approvepr_approve');
    });

    Route::get('/approvalpr_reject', function () {
        return view('scheduler.approvepr_reject');
    });

    Route::get('/accounting-project-posted', function () {

        return view('accounting.project-posted');
    });

    Route::get('/xml-accounting-project-posted', function () {
        return view('accounting.xml.project-posted');
    });

    Route::get('/accounting-project-posted-detail', function () {
        return view('accounting.project-posted-detail');
    });

    Route::get('/accounting-master-location', function () {
        return view('accounting.master-location');
    });

    Route::get('/xml-accounting-master-location', function () {
        return view('accounting.xml.master-location');
    });

    Route::post('/master-location-submit', [MasterOfficeController::class, 'submit']);

    Route::get('/accounting-naster-vendor', function () {
        return view('accounting.master-vendor');
    });

    Route::post('/accounting-master-vendor-update', function () {
        return view('accounting.master-vendor-update');
    });

    Route::get('/xml-accounting-master-vendor', function () {
        return view('accounting.xml.master-vendor');
    });

    Route::get('/accounting-master-benefit', function () {
        return view('accounting.master-benefit');
    });

    Route::get('/xml-accounting-master-benefit', function () {
        return view('accounting.xml.master-benefit');
    });

    Route::post('/accounting-master-benefit-update', function () {
        return view('accounting.master-benefit-update');
    });

    Route::get('/xml-finance-coa', function () {
        return view('finance.xml.combo-coa');
    });

    Route::get('/accounting-master-bank', function () {
        return view('accounting.master-bank');
    });

    Route::post('/master-bank-update', function () {
        return view('accounting.master-bank-update');
    });

    Route::get('/accounting-master-customer', function () {
        return view('accounting.customer');
    });

    Route::get('/xml-accounting-master-customer', function () {
        return view('accounting.xml.customer');
    });

    Route::post('/accounting-master-customer-update', function () {
        return view('accounting.master-customer-update');
    });

    Route::get('/accounting-master-coa', function () {
        return view('accounting.master-coa');
    });

    Route::get('/xml-accounting-master-coa', function () {
        return view('accounting.xml.master-coa');
    });

    Route::get('/xml-accounting-master-bank', function () {
        return view('accounting.xml.master-bank');
    });

    Route::get('/accounting-journal-transaction', function () {
        return view('accounting.journal-transaction');
    });

    Route::get('/accounting-journal-transaction-process', function () {
        return view('accounting.journal-transaction-process');
    });

    Route::post('/journal-transaction-update', function () {
        return view('accounting.journal-transaction-update');
    });

    Route::get('/xml-accounting-journal-transaction', function () {
        return view('accounting.xml.journal-transaction');
    });

    Route::get('/xml-accounting-journal-transaction-posted', function () {
        return view('accounting.xml.journal-transaction-posted');
    });

    Route::get('/accounting-journal-transaction-upload', function () {
        return view('accounting.journal-transaction-upload');
    });

    Route::get('/accounting-journal-transaction-detail', function () {
        return view('accounting.journal-transaction-detail');
    });

    Route::get('/accounting-journal-transaction-detail-posted', function () {
        return view('accounting.journal-transaction-detail-posted');
    });

    Route::get('/xml-accounting-journal-transaction-detail', function () {
        return view('accounting.xml.journal-transaction-detail');
    });

    Route::get('/xml-accounting-journal-transaction-detail-tb', function () {
        return view('accounting.xml.journal-transaction-detail-tb');
    });

    Route::get('/xml-accounting-journal-transaction-detail-log', function () {
        return view('accounting.xml.journal-transaction-detail-log');
    });

    Route::post('/accounting-journal-transaction-detail-submit', function () {
        return view('accounting.journal-transaction-detail-submit');
    });

    Route::get('/accounting-journal-transaction-detail-final-submit', function () {
        return view('accounting.journal-transaction-detail-final-submit');
    });

    Route::get('/accounting-journal-transaction-detail-final-submit', function () {
        return view('accounting.journal-transaction-detail-final-submit');
    });

    Route::get('/accounting-journal-posted', function () {
        return view('accounting.journal-transaction-posted');
    });

    Route::get('/accounting-report-general-ledgers', function () {
        return view('accounting.report.general-ledgers');
    });

    Route::get('/xml-accounting-report-general-ledgers', function () {
        return view('accounting.report.xml.general-ledgers');
    });

    Route::get('/accounting-report-balance', function () {
        return view('accounting.report.balance');
    });

    Route::get('/xml-accounting-report-balance', function () {
        return view('accounting.report.xml.balance');
    });

    Route::get('/accounting-report-trial-balance', function () {
        return view('accounting.report.trial-balance');
    });

    Route::post('/export-excel-gen', function () {
        return view('export.generate');
    });

    Route::get('/xml-accounting-report-trial-balance', function () {
        return view('accounting.report.xml.trial-balance');
    });

    Route::get('/accounting-report-profit-and-loss', function () {
        return view('accounting.report.profit-and-loss');
    });

    Route::get('/xml-accounting-report-profit-and-loss', function () {
        return view('accounting.report.xml.profit-and-loss');
    });

    Route::get('/accounting-report-cash-flow', function () {
        return view('accounting.report.cash-flow');
    });

    Route::get('/xml-accounting-report-cash-flow', function () {
        return view('accounting.report.xml.cash-flow');
    });

    Route::get('/accounting-payroll-journal', function () {
        return view('accounting.payroll.journal');
    });

    Route::get('/xml-accounting-payroll-journal', function () {
        return view('accounting.payroll.xml.journal');
    });

    Route::get('/accounting-payroll-journal-detail', function () {
        return view('accounting.payroll.journal-detail');
    });

    Route::get('/xml-accounting-payroll-journal-detail', function () {
        return view('accounting.payroll.xml.journal-detail');
    });

    Route::get('/accounting-payroll-issued', function () {
        return view('accounting.payroll.issued');
    });

    Route::get('/xml-accounting-payroll-issued', function () {
        return view('accounting.payroll.xml.issued');
    });

    Route::get('/accounting-payroll-issued-detail', function () {
        return view('accounting.payroll.issued-detail');
    });

    Route::get('/accounting-payroll-issued-posting', function () {
        return view('accounting.payroll.issued-posting');
    });

    Route::get('/xml-accounting-payroll-issued-detail', function () {
        return view('accounting.payroll.xml.issued-detail');
    });

    Route::get('/beginning-balance-upload', function () {
        return view('accounting.beginning-balance-upload');
    });

    Route::get('/accounting-beginning-balance-upload-process', function () {
        return view('accounting.beginning-balance-upload-process');
    });

    Route::get('/accounting-beginning-balance', function () {
        return view('accounting.beginning-balance');
    });

    Route::get('/xml-beginning-balance', function () {
        return view('accounting.xml.beginning-balance');
    });

    Route::get('/user/forgot-password', function () {
        return view('/user/forgot-password');
    });

    Route::get('/sales-opportunity-addendum', function () {
        return view('sales.opportunity-addendum');
    });

    Route::get('/opportunity-addendum-detail', function () {
        return view('sales.opportunity-addendum-detail');
    });

    Route::post('/opportunity-addendum-detail-submit', function () {
        return view('sales.opportunity-addendum-detail-submit');
    });

    Route::get('/sales-opportunity-outstanding', function () {
        return view('sales.opportunity-outstanding');
    });

    Route::get('/sales-opportunity-active', function () {
        return view('sales.opportunity-active');
    });

    Route::get('/xml-sales-opportunity-active', function () {
        return view('sales.xml.opportunity-active');
    });

    Route::get('/sales-opportunity-pipeline', function () {
        return view('sales.opportunity-pipeline');
    });

    Route::get('/xml-sales-opportunity-pipeline', function () {
        return view('sales.xml.opportunity-pipeline');
    });

    Route::get('/sales-opportunity-pipeline-sales', function () {
        return view('sales.opportunity-pipeline-sales');
    });

    Route::get('/xml-sales-opportunity-pipeline-sales', function () {
        return view('sales.xml.opportunity-pipeline-sales');
    });

    Route::get('/sales-opportunity-pipeline-customer', function () {
        return view('sales.opportunity-pipeline-customer');
    });

    Route::get('/opportunity-pipeline-customer-chart', function () {
        return view('sales.opportunity-pipeline-customer-chart');
    });

    Route::get('/xml-sales-opportunity-pipeline-customer', function () {
        return view('sales.xml.opportunity-pipeline-customer');
    });

    Route::get('/sales-opportunity-pipeline-customer2', function () {
        return view('sales.opportunity-pipeline-customer2');
    });

    Route::get('/xml-sales-opportunity-pipeline-customer2', function () {
        return view('sales.xml.opportunity-pipeline-customer2');
    });

    Route::get('/sales-opportunity-finalize-opportunity', function () {
        return view('sales.opportunity-finalize');
    });

    Route::get('/xml-sales-opportunity-finalize', function () {
        return view('sales.xml.opportunity-finalize');
    });

    Route::get('/xml-opportunity-outstanding', function () {
        return view('sales.xml.opportunity-outstanding');
    });

    Route::get('/opportunity-outstanding-detail', function () {
        return view('sales.opportunity-outstanding-detail');
    });

    Route::get('/sales-opportunity-job-requisition', function () {
        return view('sales.opportunity-job-requisition');
    });

    Route::get('/xml-opportunity-job-requisition', function () {
        return view('sales.xml.opportunity-job-requisition');
    });

    Route::get('/sales-opportunity-search', function () {
        return view('sales.opportunity-search');
    });

    Route::get('/sales-opportunity-new', function () {
        return view('sales.opportunity-new');
    });

    Route::post('/sales-opportunity-new-submit', function () {
        return view('sales.opportunity-new-submit');
    });

    Route::get('/xml-sales-opportunity-new', function () {
        return view('sales.xml.opportunity-new');
    });

    Route::get('/sales-customer-lists', function () {
        return view('sales.customer-lists');
    });

    Route::get('/xml-sales-customer-lists', function () {
        return view('sales.xml.customer-lists');
    });

    Route::post('/sales-proforma-initiate-update', function () {
        return view('sales.proforma-initiate-update');
    });

    Route::get('/sales-proforma-initiate', function () {
        return view('sales.proforma-initiate');
    });

    Route::get('/xml-sales-proforma-initiate', function () {
        return view('sales.xml.proforma-initiate');
    });

    Route::get('/sales-proforma-approved', function () {
        return view('sales.proforma-approved');
    });

    Route::get('/xml-sales-proforma-approved', function () {
        return view('sales.xml.proforma-approved');
    });

    Route::get('/sales-proforma-posting', function () {
        return view('sales.proforma-posting');
    });

    Route::get('/xml-sales-proforma-posting', function () {
        return view('sales.xml.proforma-posting');
    });

    Route::get('/sales-proforma-posting-detail', function () {
        return view('sales.proforma-posting-detail');
    });

    Route::post('/sales-proforma-posting-detail-update', function () {
        return view('sales.proforma-posting-detail-update');
    });

    Route::get('/xml-sales-proforma-posting-detail', function () {
        return view('sales.xml.proforma-posting-detail');
    });

    Route::get('/xml-sales-proforma-posting-detail2', function () {
        return view('sales.xml.proforma-posting-detail2');
    });

    Route::get('/sales-proforma-attachment', function () {
        return view('sales.proforma-attachment');
    });

    Route::get('/xml-sales-proforma-attachment', function () {
        return view('sales.xml.proforma-attachment');
    });

    Route::get('/sales-proforma-attachment-detail', function () {
        return view('sales.proforma-attachment-detail');
    });

    Route::get('/sales-proforma-attachment-detail', function () {
        return view('sales.proforma-attachment-detail');
    });

    Route::get('/sales-proforma-attachment-upload', function () {
        return view('sales.proforma-attachment-upload');
    });

    Route::post('/sales-proforma-attachment-detail-update', function () {
        return view('sales.proforma-attachment-detail-update');
    });

    Route::get('/xml-sales-proforma-attachment-detail', function () {
        return view('sales.xml.proforma-attachment-detail');
    });

    Route::get('/xml-sales-proforma-attachment-detail2', function () {
        return view('sales.xml.proforma-attachment-detail');
    });

    Route::get('/fppdownload', function () {
        return view('sales.prints.fppdownload');
    });

    Route::get('/finance-proforma-create-invoice', function () {
        return view('finance.proforma-create-invoice');
    });

    Route::get('/xml-finance-proforma-create-invoice', function () {
        return view('finance.xml.proforma-create-invoice');
    });

    Route::get('/xml-proforma-create-invoice-detail', function () {
        return view('finance.xml.proforma-create-invoice-detail');
    });

    Route::get('/finance-proforma-create-invoice-detail', function () {
        return view('finance.proforma-create-invoice-detail');
    });

    Route::post('/proforma-create-invoice-detail-update', function () {
        return view('finance.proforma-create-invoice-detail-update');
    });

    Route::get('/xml-finance-proforma-create-invoice-detail', function () {
        return view('finance.xml.proforma-create-invoice-detail');
    });

    Route::post('/finance-proforma-create-invoice-submit', function () {
        return view('finance.proforma-create-invoice-submit');
    });

    Route::get('/finance-report-invoice-position', function () {
        return view('finance.report-invoice-position');
    });

    Route::get('/xml-finance-invoice-position', function () {
        return view('finance.xml.invoice-position-report');
    });

    Route::get('/finance-report-unbilled', function () {
        return view('finance.report-unbilled');
    });

    Route::get('/xml-finance-report-unbilled', function () {
        return view('finance.xml.report-unbilled');
    });

    Route::get('/finance-report-unbilled-po', function () {
        return view('finance.report-unbilled-po');
    });

    Route::get('/xml-finance-report-unbilled-po', function () {
        return view('finance.xml.report-unbilled-po');
    });

    Route::get('/finance-report-ar-aging', function () {
        return view('finance.ar-report-aging');
    });

    Route::get('/xml-finance-ar-report-aging', function () {
        return view('finance.xml.ar-aging-report');
    });

    Route::get('/finance-ar-history', function () {
        return view('finance.ar-history');
    });

    Route::get('/xml-finance-ar-history', function () {
        return view('finance.xml.ar-history');
    });

    Route::get('/ar-history-detail', function () {
        return view('finance.ar-history-detail');
    });

    Route::get('/finance-reimbursement-detail', function () {
        return view('finance.finance-reimbursement-detail');
    });

    Route::post('/my-reimbursement-update', function () {
        return view('mymenu.reimbursement-update');
    });

    Route::get('/my-reimbursement-new', function () {
        return view('mymenu.reimbursement-new');
    });

    Route::post('/my-reimbursement-new-update', function () {
        return view('mymenu.reimbursement-new-update');
    });

    Route::get('/xml-my-reimbursement', function () {
        return view('mymenu.xml.reimbursement');
    });

    Route::post('/my-reimbursement-new-submit', function () {
        return view('mymenu.reimbursement-new-submit');
    });

    Route::get('/my-reimbursement-detail', function () {
        return view('mymenu.reimbursement-detail');
    });

    Route::get('/xml-my-reimbursement-detail', function () {
        return view('mymenu.xml.reimbursement-detail');
    });

    Route::get('/my-reimbursement-detail-coa', function () {
        return view('mymenu.cash-out-detail-coa');
    });

    Route::get('/xml-my-reimbursement-detail-coa', function () {
        return view('mymenu.xml.cash-out-detail-coa');
    });

    Route::post('/my-reimbursement-detail-update', function () {
        return view('mymenu.cash-out-detail-update');
    });

    Route::get('/xml-finance-reimbursement-detail', function () {
        return view('finance.xml.finance-reimbursement-detail');
    });

    Route::get('/finance-reimbursement-detail-reimbursement', function () {
        return view('finance.finance-reimbursement-detail-reimbursement');
    });

    Route::get('/xml-finance-reimbursement-detail-reimbursement', function () {
        return view('finance.xml.finance-reimbursement-detail-reimbursement');
    });

    Route::post('/finance-reimbursement-detail-reimbursement-submit', function () {
        return view('finance.finance-reimbursement-detail-reimbursement-submit');
    });

    Route::get('/xml-finance-reimbursement-detail-reimbursement', function () {
        return view('finance.xml.finance-reimbursement-detail-reimbursement');
    });

    Route::post('/finance-reimbursement-detail-update', function () {
        return view('finance.finance-reimbursement-detail-update');
    });

    Route::get('/finance-reimbursement-out-add', function () {
        return view('finance.finance-reimbursement-out-add');
    });

    Route::get('/finance-reimbursement-approval', function () {
        return view('finance.finance-reimbursement-approval');
    });

    Route::get('/xml-finance-reimbursement-approval', function () {
        return view('finance.xml.finance-reimbursement-approval');
    });

    Route::get('/finance-reimbursement-history', function () {
        return view('finance.finance-reimbursement-history');
    });

    Route::get('/xml-finance-reimbursement-history', function () {
        return view('finance.xml.finance-reimbursement-history');
    });

    Route::get('/finance-reimbursement-history-detail', function () {
        return view('finance.finance-reimbursement-history-detail');
    });

    Route::get('/xml-finance-reimbursement-history-detail', function () {
        return view('finance.xml.finance-reimbursement-history-detail');
    });

    Route::get('/finance-reimbursement2-approval', function () {
        return view('finance.finance-reimbursement2-approval');
    });

    Route::get('/xml-finance-reimbursement2-approval', function () {
        return view('finance.xml.finance-reimbursement2-approval');
    });

    Route::get('/finance-reimbursement2-approval-detail', function () {
        return view('finance.finance-reimbursement2-detail-reimbursement');
    });

    Route::get('/xml-finance-reimbursement2-detail-reimbursement', function () {
        return view('finance.xml.finance-reimbursement2-detail-reimbursement');
    });

    Route::post('/finance-reimbursement2-detail-reimbursement-submit', function () {
        return view('finance.finance-reimbursement2-detail-reimbursement-submit');
    });

    Route::post('/finance-reimbursement2-detail-reimbursement-update', function () {
        return view('finance.finance-reimbursement2-detail-reimbursement-update');
    });

    Route::get('/finance-reimbursement-history', function () {
        return view('finance.finance-reimbursement-history');
    });

    Route::get('/xml-finance-reimbursement-history', function () {
        return view('finance.xml.finance-reimbursement-history');
    });

    Route::get('/finance-reimbursement-history-detail', function () {
        return view('finance.finance-reimbursement-history-detail');
    });

    Route::get('/xml-finance-reimbursement-history-detail', function () {
        return view('finance.xml.finance-reimbursement-history-detail');
    });

    Route::get('/finance-reimbursement2-history', function () {
        return view('finance.finance-reimbursement2-history');
    });

    Route::get('/xml-finance-reimbursement2-history', function () {
        return view('finance.xml.finance-reimbursement2-history');
    });

    Route::get('/finance-reimbursement2-history-detail', function () {
        return view('finance.finance-reimbursement2-detail-history');
    });

    Route::get('/xml-finance-reimbursement2-history-detail', function () {
        return view('finance.xml.finance-reimbursement2-history-detail');
    });

    Route::get('/xml-ar-history-detail', function () {
        return view('finance.xml.ar-history-detail');
    });

    Route::get('/xml-ar-history-detail-history', function () {
        return view('finance.xml.ar-history-detail-history');
    });

    Route::get('/ap-reference', function () {
        return view('finance.ap-reference');
    });

    Route::get('/ar-reference', function () {
        return view('finance.ar-reference');
    });

    Route::get('/ar-monitoring-detail', function () {
        return view('finance.ar-monitoring-detail');
    });

    Route::get('/ar-monitoring-detail-coa', function () {
        return view('finance.ar-monitoring-detail-coa');
    });

    Route::get('/xml-ar-monitoring-detail-coa', function () {
        return view('finance.xml.ar-monitoring-detail-coa');
    });

    Route::post('/ar-monitoring-detail-list-update', function () {
        return view('finance.ar-monitoring-detail-list-update');
    });

    Route::post('/ar-monitoring-detail-update', function () {
        return view('finance.ar-monitoring-detail-update');
    });

    Route::get('/xml-ar-monitoring-detail', function () {
        return view('finance.xml.ar-monitoring-detail');
    });

    Route::get('/xml-ar-monitoring-detail-history', function () {
        return view('finance.xml.ar-monitoring-detail-history');
    });

    Route::get('/finance-ar-monitoring', function () {
        return view('finance.ar-monitoring');
    });

    Route::get('/xml-finance-ar-monitoring', function () {
        return view('finance.xml.ar-monitoring');
    });

    Route::post('/finance-ar-delivery-update', function () {
        return view('finance.ar-delivery-update');
    });

    Route::get('/finance-ar-delivery', function () {
        return view('finance.ar-delivery');
    });

    Route::get('/xml-finance-ar-delivery', function () {
        return view('finance.xml.ar-delivery');
    });

    Route::post('/finance-ar-receive-update', function () {
        return view('finance.ar-receive-update');
    });

    Route::get('/finance-ar-receive', function () {
        return view('finance.ar-receive');
    });

    Route::get('/xml-finance-ar-receive', function () {
        return view('finance.xml.ar-receive');
    });

    Route::get('/print-invoice', function () {
        return view('finance.prints.print-invoice');
    });

    Route::get('/print-pajak', function () {
        return view('finance.prints.print-pajak');
    });

    Route::get('/finance-invoice-approved', function () {
        return view('finance.approved-invoice');
    });

    Route::get('/xml-finance-approved-invoice', function () {
        return view('finance.xml.approved-invoice');
    });

    Route::get('/finance-approved-invoice-detail', function () {
        return view('finance.approved-invoice-detail');
    });

    Route::get('/accounting-journal-transaction-manual', function () {
        return view('accounting.journal-transaction-manual');
    });

    Route::get('/finance-invoice-return', function () {
        return view('finance.return-invoice');
    });

    Route::get('/finance-invoice-return-detail', function () {
        return view('finance.return-invoice-detail');
    });

    Route::get('/xml-finance-return-invoice', function () {
        return view('finance.xml.return-invoice');
    });

    Route::get('/xml-finance-approval-invoice', function () {
        return view('finance.xml.approval-invoice');
    });

    Route::get('/xml-finance-approval-invoice-detail', function () {
        return view('finance.xml.approval-invoice-detail');
    });

    Route::get('/finance-proforma-outstanding', function () {
        return view('finance.proforma-outstanding');
    });

    Route::get('/xml-finance-proforma-outstanding', function () {
        return view('finance.xml.proforma-outstanding');
    });

    Route::post('/finance-proforma-outstanding-detail-update', function () {
        return view('finance.proforma-outstanding-detail-update');
    });

    Route::get('/finance-proforma-outstanding-detail', function () {
        return view('finance.proforma-outstanding-detail');
    });

    Route::get('/xml-finance-proforma-outstanding-detail', function () {
        return view('finance.xml.proforma-outstanding-detail');
    });

    Route::get('/budget-management-detail', function () {
        return view('finance.budget.budget-management-detail');
    });

    Route::post('/finance-budget-management-detail-update', function () {
        return view('finance.budget.budget-management-detail-update');
    });

    Route::get('/xml-finance-budget-management-detail', function () {
        return view('finance.budget.xml.budget-management-detail');
    });

    Route::get('/finance-budget-management', function () {
        return view('finance.budget.budget-management');
    });

    Route::post('/finance-budget-management-update', function () {
        return view('finance.budget.budget-management-update');
    });

    Route::get('/xml-finance-budget-management', function () {
        return view('finance.budget.xml.budget-management');
    });

    Route::get('/finance-budget-history', function () {
        return view('finance.budget.budget-history');
    });

    Route::get('/xml-finance-budget-history', function () {
        return view('finance.budget.xml.budget-history');
    });

    Route::get('/finance-budget-history-detail', function () {
        return view('finance.budget.budget-history-detail');
    });

    Route::get('/xml-finance-budget-history-detail', function () {
        return view('finance.budget.xml.budget-history-detail');
    });

    Route::get('/finance-budget-transaction', function () {
        return view('finance.budget.budget-transaction');
    });

    Route::get('/xml-finance-budget-transaction', function () {
        return view('finance.budget.xml.budget-transaction');
    });

    Route::get('/finance-budget-summary', function () {
        return view('finance.budget.budget-summary');
    });

    Route::get('/xml-finance-budget-summary', function () {
        return view('finance.budget.xml.budget-summary');
    });

    Route::get('/finance-ap-coa', function () {
        return view('finance.ap.coa');
    });

    Route::get('/finance-ap-manual', function () {
        return view('finance.ap.manual');
    });

    Route::get('/finance-ap-search-employee', function () {
        return view('finance.ap.search-employee');
    });

    Route::post('/finance-ap-manual-submit', function () {
        return view('finance.ap.manual-submit');
    });

    Route::get('/finance-ap-receive', function () {
        return view('finance.ap.receive');
    });

    Route::post('/finance-ap-receive-update', function () {
        return view('finance.ap.receive-update');
    });

    Route::get('/xml-finance-ap-receive', function () {
        return view('finance.ap.xml.receive');
    });

    Route::get('/finance-ap-receive-detail', function () {
        return view('finance.ap.receive-detail');
    });

    Route::get('/finance-ap-request', function () {
        return view('finance.ap.request');
    });

    Route::post('/finance-ap-request-update', function () {
        return view('finance.ap.request-update');
    });

    Route::get('/xml-finance-ap-request', function () {
        return view('finance.ap.xml.request');
    });

    Route::get('/finance-ap-request-detail', function () {
        return view('finance.ap.request-detail');
    });

    Route::get('/finance-ap-management', function () {
        return view('finance.ap.management');
    });

    Route::get('/xml-finance-ap-management', function () {
        return view('finance.ap.xml.management');
    });

    Route::get('/finance-ap-management-detail', function () {
        return view('finance.ap.management-detail');
    });

    Route::get('/finance-ap-management-detail-coa', function () {
        return view('finance.ap.management-detail-coa');
    });

    Route::get('/xml-finance-ap-management-detail-coa', function () {
        return view('finance.ap.xml.management-detail-coa');
    });

    Route::get('/xml-finance-ap-management-detail', function () {
        return view('finance.ap.xml.management-detail');
    });

    Route::get('/xml-finance-ap-management-detail-history', function () {
        return view('finance.ap.xml.management-detail-history');
    });

    Route::post('/finance-ap-management-detail-list-update', function () {
        return view('finance.ap.management-detail-list-update');
    });

    Route::post('/finance-ap-management-detail-submit', function () {
        return view('finance.ap.management-detail-submit');
    });

    Route::post('/finance-ap-management-detail-reject', function () {
        return view('finance.ap.management-detail-reject');
    });

    Route::get('/finance-ap-history', function () {
        return view('finance.ap.history');
    });

    Route::get('/xml-finance-ap-history', function () {
        return view('finance.ap.xml.history');
    });

    Route::get('/finance-ap-history-detail', function () {
        return view('finance.ap.history-detail');
    });

    Route::get('/xml-finance-ap-history-detail', function () {
        return view('finance.ap.xml.history-detail');
    });

    Route::post('/finance-ap-history-detail-submit', function () {
        return view('finance.ap.history-detail-submit');
    });

    Route::post('/finance-ap-history-detail-reject', function () {
        return view('finance.ap.history-detail-reject');
    });

    Route::post('/customer-lists-update', function () {
        return view('sales.customer-lists-update');
    });

    Route::get('/customer-lists-detail', function () {
        return view('sales.customer-lists-detail');
    });

    Route::post('/customer-lists-detail-submit', function () {
        return view('sales.customer-lists-detail-submit');
    });

    Route::post('/sales-lists-update', function () {
        return view('sales.sales-lists-update');
    });

    Route::get('/sales-lists-add', function () {
        return view('sales.sales-list-add');
    });

    Route::get('/xml-sales-lists', function () {
        return view('sales.xml.sales-lists');
    });

    Route::post('/sales-list-add-update', function () {
        return view('sales.sales-list-add-update');
    });

    Route::post('/customer-lists-add-submit', function () {
        return view('sales.customer-lists-add-submit');
    });

    Route::get('/sales-master-sales', function () {
        return view('sales.master-sales');
    });

    Route::get('/xml-master-sales', function () {
        return view('sales.xml.master-sales');
    });

    Route::get('/sales-opportunity-fu-lists', function () {
        return view('sales.opportunity-fu-lists');
    });

    Route::get('/xml-sales-opportunity-fu-lists', function () {
        return view('sales.xml.opportunity-fu-lists');
    });

    Route::post('/sales-opportunity-fu-lists-update', function () {
        return view('sales.opportunity-fu-lists-update');
    });

    Route::get('/sales-opportunity-qualified-lists', function () {
        return view('sales.opportunity-qualified-lists');
    });

    Route::get('/xml-sales-opportunity-qualified-lists', function () {
        return view('sales.xml.opportunity-qualified-lists');
    });

    Route::post('/sales-opportunity-qualified-lists-update', function () {
        return view('sales.opportunity-qualified-lists-update');
    });

    Route::get('/sales-opportunity-closed-lists', function () {
        return view('sales.opportunity-closed-lists');
    });

    Route::get('/xml-sales-opportunity-closed-lists', function () {
        return view('sales.xml.opportunity-closed-lists');
    });

    Route::post('/sales-opportunity-closed-lists-update', function () {
        return view('sales.opportunity-closed-lists-update');
    });

    Route::get('/sales-opportunity-warm-lists', function () {
        return view('sales.opportunity-warm-lists');
    });

    Route::get('/xml-sales-opportunity-warm-lists', function () {
        return view('sales.xml.opportunity-warm-lists');
    });

    Route::post('/sales-opportunity-warm-lists-update', function () {
        return view('sales.opportunity-warm-lists-update');
    });

    Route::get('/sales-opportunity-registration', function () {
        return view('sales.opportunity-registration');
    });

    Route::get('/sales-opportunity-lists', function () {
        return view('sales.opportunity-lists');
    });

    Route::get('/customer-lists-add', function () {
        return view('sales.customer-lists-add');
    });

    Route::get('/xml-sales-opportunity-lists', function () {
        return view('sales.xml.opportunity-lists');
    });

    Route::post('/opportunity-detail-submit', function () {
        return view('sales.opportunity-detail-submit');
    });

    Route::get('/opportunity-lists-detail', function () {
        return view('sales.opportunity-lists-detail');
    });

    Route::get('/xml-opportunity-lists-detail', function () {
        return view('sales.xml.opportunity-lists-detail');
    });

    Route::get('/xml-opportunity-lists-attachment', function () {
        return view('sales.xml.opportunity-lists-attachment');
    });

    Route::get('/opportunity-lists-detail2', function () {
        return view('sales.opportunity-lists-detail2');
    });

    Route::get('/xml-opportunity-lists-detail2', function () {
        return view('sales.xml.opportunity-lists-detail2');
    });

    Route::post('/opportunity-lists-detail-submit', function () {
        return view('sales.opportunity-lists-detail-submit');
    });

    Route::post('/opportunity-lists-detail-update', function () {
        return view('sales.opportunity-lists-detail-update');
    });

    Route::post('/opportunity-lists-detail-update2', function () {
        return view('sales.opportunity-lists-detail-update2');
    });

    Route::post('/opportunity-lists-attachment-update', function () {
        return view('sales.opportunity-lists-attachment-update');
    });

    Route::post('/opportunity-lists-submit', function () {
        return view('sales.opportunity-lists-submit');
    });

    Route::get('/sales-opportunity-activity', function () {
        return view('sales.opportunity-activity');
    });

    Route::get('/xml-sales-opportunity-activity', function () {
        return view('sales.xml.opportunity-activity');
    });

    Route::get('/sales-opportunity-activity-add', function () {
        return view('sales.opportunity-activity-add');
    });

    Route::get('/opportunity-activity-detail', function () {
        return view('sales.opportunity-activity-detail');
    });

    Route::get('/search-city', function () {
        return view('sales.search-city');
    });

    Route::post('/sales-opportunity-activity-detail-submit', function () {
        return view('sales.opportunity-activity-detail-submit');
    });

    Route::post('/sales-opportunity-activity-add-submit', function () {
        return view('sales.opportunity-activity-add-submit');
    });

    Route::get('/sales-opportunity-search', function () {
        return view('sales.opportunity-search');
    });

    Route::get('/sales-opportunity-activity-document-upload', function () {
        return view('sales.opportunity-activity-document-upload');
    });

    Route::post('/sales-opportunity-activity-document-upload-submit', function () {
        return view('sales.opportunity-activity-document-upload-submit');
    });

    Route::get('/sales-opportunity-activity-online-document', function () {
        return view('sales.opportunity-activity-online-document');
    });

    Route::post('/sales-opportunity-activity-online-document-submit', function () {
        return view('sales.opportunity-activity-online-document-submit');
    });

    Route::get('/finance-report-archive-cash-in', function () {
        return view('finance.archives.cash-in');
    });

    Route::get('/xml-finance-report-archive-cash-in', function () {
        return view('finance.archives.xml.cash-in');
    });

    Route::get('/finance-report-archive-cash-in-detail', function () {
        return view('finance.archives.cash-in-detail');
    });

    Route::get('/xml-finance-report-archive-cash-in-detail', function () {
        return view('finance.archives.xml.cash-in-detail');
    });

    Route::get('/finance-report-archive-cash-out', function () {
        return view('finance.archives.cash-out');
    });

    Route::get('/xml-finance-report-archive-cash-out', function () {
        return view('finance.archives.xml.cash-out');
    });

    Route::get('/finance-report-archive-cash-out-detail', function () {
        return view('finance.archives.cash-out-detail');
    });

    Route::get('/xml-finance-report-archive-cash-out-detail', function () {
        return view('finance.archives.xml.cash-out-detail');
    });

    Route::get('/finance-report-archive-bank-withdrawal', function () {
        return view('finance.archives.bank-withdrawal');
    });

    Route::get('/xml-finance-report-archive-bank-withdrawal', function () {
        return view('finance.archives.xml.bank-withdrawal');
    });

    Route::get('/finance-report-archive-bank-withdrawal-detail', function () {
        return view('finance.archives.bank-withdrawal-detail');
    });

    Route::get('/finance-report-archive-bank-in', function () {
        return view('finance.archives.bank-in');
    });

    Route::get('/xml-finance-report-archive-bank-in', function () {
        return view('finance.archives.xml.bank-in');
    });

    Route::get('/finance-report-archive-bank-in-detail', function () {
        return view('finance.archives.bank-in-detail');
    });

    Route::get('/xml-finance-report-archive-bank-in-detail', function () {
        return view('finance.archives.xml.bank-in-detail');
    });

    Route::get('/finance-report-archive-bank-advance', function () {
        return view('finance.archives.bank-advance');
    });

    Route::get('/xml-finance-report-archive-bank-advance', function () {
        return view('finance.archives.xml.bank-advance');
    });

    Route::get('/finance-report-archive-bank-advance-detail', function () {
        return view('finance.archives.bank-advance-detail');
    });

    Route::get('/xml-finance-report-archive-bank-advance-detail', function () {
        return view('finance.archives.xml.bank-advance-detail');
    });

    Route::get('/finance-report-archive-cash-advance', function () {
        return view('finance.archives.cash-advance');
    });

    Route::get('/xml-finance-report-archive-cash-advance', function () {
        return view('finance.archives.xml.cash-advance');
    });

    Route::get('/finance-report-archive-cash-advance-detail', function () {
        return view('finance.archives.cash-advance-detail');
    });

    Route::get('/xml-finance-report-archive-cash-advance-detail', function () {
        return view('finance.archives.xml.cash-advance-detail');
    });

    Route::get('/finance-report-archive-bank-out', function () {
        return view('finance.archives.bank-out');
    });

    Route::get('/xml-finance-report-archive-bank-out', function () {
        return view('finance.archives.xml.bank-out');
    });

    Route::get('/finance-report-archive-bank-out-detail', function () {
        return view('finance.archives.bank-out-detail');
    });

    Route::get('/xml-finance-report-archive-bank-out-detail', function () {
        return view('finance.archives.xml.bank-out-detail');
    });

    Route::get('/finance-report-archive-bank-transfer', function () {
        return view('finance.archives.bank-transfer');
    });

    Route::get('/xml-finance-report-archive-bank-transfer', function () {
        return view('finance.archives.xml.bank-transfer');
    });

    Route::get('/finance-cash-in', function () {
        return view('finance.cash-in');
    });

    Route::get('/xml-finance-cash-in', function () {
        return view('finance.xml.cash-in');
    });

    Route::post('/finance-cash-in-update', function () {
        return view('finance.cash-in-update');
    });

    Route::get('/finance-cash-in-detail', function () {
        return view('finance.cash-in-detail');
    });

    Route::get('/xml-finance-cash-in-detail', function () {
        return view('finance.xml.cash-in-detail');
    });

    Route::get('/finance-cash-in-detail-coa', function () {
        return view('finance.cash-in-detail-coa');
    });

    Route::get('/xml-finance-cash-in-detail-coa', function () {
        return view('finance.xml.cash-in-detail-coa');
    });

    Route::post('/finance-cash-in-detail-update', function () {
        return view('finance.cash-in-detail-update');
    });

    Route::get('/finance-cash-out', function () {
        return view('finance.cash-out');
    });

    Route::get('/xml-finance-cash-out', function () {
        return view('finance.xml.cash-out');
    });

    Route::post('/finance-cash-out-update', function () {
        return view('finance.cash-out-update');
    });

    Route::get('/finance-cash-out-detail', function () {
        return view('finance.cash-out-detail');
    });

    Route::get('/xml-finance-cash-out-detail', function () {
        return view('finance.xml.cash-out-detail');
    });

    Route::get('/finance-cash-out-detail-coa', function () {
        return view('finance.cash-out-detail-coa');
    });

    Route::get('/xml-finance-cash-out-detail-coa', function () {
        return view('finance.xml.cash-out-detail-coa');
    });

    Route::post('/finance-cash-out-detail-update', function () {
        return view('finance.cash-out-detail-update');
    });

    Route::get('/xml-finance-department', function () {
        return view('finance.xml.finance-department');
    });

    Route::get('/finance-cash-transfer-add', function () {
        return view('finance.cash-transfer-add');
    });

    Route::post('/finance-cash-transfer-add-submit', function () {
        return view('finance.cash-transfer-add-submit');
    });

    Route::post('/finance-cash-transfer-add-submit-receive', function () {
        return view('finance.cash-transfer-add-submit-receive');
    });

    Route::get('/finance-cash-transfer', function () {
        return view('finance.cash-transfer');
    });

    Route::get('/xml-finance-cash-transfer', function () {
        return view('finance.xml.cash-transfer');
    });

    Route::post('/finance-cash-transfer-update', function () {
        return view('finance.cash-transfer-update');
    });

    Route::get('/finance-cash-transfer-detail', function () {
        return view('finance.cash-transfer-detail');
    });

    Route::get('/my-reimbursement', function () {
        return view('mymenu.reimbursement');
    });

    Route::get('/xml-my-reimbursement', function () {
        return view('mymenu.xml.reimbursement');
    });

    Route::get('/my-cash-advance', function () {
        return view('mymenu.cash-advance');
    });

    Route::get('/xml-my-cash-advance', function () {
        return view('mymenu.xml.cash-advance');
    });

    Route::get('/finance-cash-advance', function () {
        return view('finance.cash-advance');
    });

    Route::get('/xml-finance-cash-advance', function () {
        return view('finance.xml.cash-advance');
    });

    Route::get('/finance-cash-advance-add', function () {
        return view('finance.cash-advance-add');
    });

    Route::get('/finance-cash-advance-search-user', function () {
        return view('finance.cash-advance-search-user');
    });

    Route::post('/finance-cash-advance-add-submit', function () {
        return view('finance.cash-advance-add-submit');
    });

    Route::post('/finance-cash-advance-update', function () {
        return view('finance.cash-advance-update');
    });

    Route::get('/finance-cash-advance-detail', function () {
        return view('finance.cash-advance-detail');
    });

    Route::get('/xml-finance-cash-advance-detail', function () {
        return view('finance.xml.cash-advance-detail');
    });

    Route::get('/finance-cash-advance-detail-coa', function () {
        return view('finance.cash-advance-detail-coa');
    });

    Route::get('/xml-finance-cash-advance-detail-coa', function () {
        return view('finance.xml.cash-advance-detail-coa');
    });

    Route::get('/finance-cash-advance-detail-coa2', function () {
        return view('finance.cash-advance-detail-coa2');
    });

    Route::get('/xml-finance-cash-advance-detail-coa2', function () {
        return view('finance.xml.cash-advance-detail-coa2');
    });

    Route::post('/finance-cash-advance-detail-update', function () {
        return view('finance.cash-advance-detail-update');
    });

    Route::get('/finance-cash-deposit-add', function () {
        return view('finance.cash-deposit-add');
    });

    Route::get('/finance-cash-deposit', function () {
        return view('finance.cash-deposit');
    });

    Route::get('/xml-finance-cash-deposit', function () {
        return view('finance.xml.cash-deposit');
    });

    Route::post('/finance-cash-deposit-update', function () {
        return view('finance.cash-deposit-update');
    });

    Route::post('/finance-cash-deposit-add-submit', function () {
        return view('finance.cash-deposit-add-submit');
    });

    Route::get('/finance-cash-deposit-detail', function () {
        return view('finance.cash-deposit-detail');
    });

    Route::post('/finance-cash-deposit-detail-submit', function () {
        return view('finance.cash-deposit-detail-submit');
    });

    Route::get('/finance-cash-handle', function () {
        return view('finance.cash-handler');
    });

    Route::post('/finance-cash-handle-update', function () {
        return view('finance.cash-handler-update');
    });

    Route::get('/xml-finance-cash-handler', function () {
        return view('finance.xml.cash-handler');
    });

    Route::get('/finance-cash-handle-add', function () {
        return view('finance.cash-handle-add');
    });

    Route::post('/finance-cash-handle-add-submit', function () {
        return view('finance.cash-handle-add-submit');
    });

    Route::get('/finance-cash-handler-detail', function () {
        return view('finance.cash-handler-detail');
    });

    Route::get('/finance-bankout-reference', function () {
        return view('finance.bankout-reference');
    });

    Route::get('/finance-bankin-reference', function () {
        return view('finance.bankin-reference');
    });

    Route::get('/finance-cashout-reference', function () {
        return view('finance.reference2');
    });

    Route::get('/finance-window-reference', function () {
        return view('finance.reference');
    });

    Route::get('/xml-finance-reference', function () {
        return view('finance.xml.reference');
    });

    //bank
    Route::get('/reference-search', function () {
        return view('finance.reference-search');
    });

    Route::get('/xml-finance-bank', function () {
        return view('finance.xml.finance-bank');
    });

    Route::get('/xml-finance-currency', function () {
        return view('finance.xml.finance-currency');
    });

    Route::get('/finance-bank-in', function () {
        return view('finance.bank-in');
    });

    Route::get('/xml-finance-bank-in', function () {
        return view('finance.xml.bank-in');
    });

    Route::post('/finance-bank-in-update', function () {
        return view('finance.bank-in-update');
    });

    Route::get('/finance-bank-in-detail', function () {
        return view('finance.bank-in-detail');
    });

    Route::get('/xml-finance-bank-in-detail', function () {
        return view('finance.xml.bank-in-detail');
    });

    Route::get('/finance-bank-in-detail-coa', function () {
        return view('finance.bank-in-detail-coa');
    });

    Route::get('/xml-finance-bank-in-detail-coa', function () {
        return view('finance.xml.bank-in-detail-coa');
    });

    Route::post('/finance-bank-in-detail-update', function () {
        return view('finance.bank-in-detail-update');
    });

    Route::get('/finance-bank-advance-detail-ap', function () {
        return view('finance.bank-advance-detail-ap');
    });

    Route::get('/xml-finance-bank-advance-detail-ap', function () {
        return view('finance.xml.bank-advance-detail-ap');
    });

    Route::get('/finance-bank-advance-detail-coa', function () {
        return view('finance.bank-advance-detail-coa');
    });

    Route::get('/xml-finance-bank-advance-detail-coa', function () {
        return view('finance.xml.bank-advance-detail-coa');
    });

    Route::get('/finance-bank-advance-detail-coa2', function () {
        return view('finance.bank-advance-detail-coa2');
    });

    Route::get('/xml-finance-bank-advance-detail-coa2', function () {
        return view('finance.xml.bank-advance-detail-coa2');
    });

    Route::get('/finance-bank-advance', function () {
        return view('finance.bank-advance');
    });

    Route::get('/xml-finance-bank-advance', function () {
        return view('finance.xml.bank-advance');
    });

    Route::get('/finance-bank-advance-outstanding', function () {
        return view('finance.bank-advance-outstanding');
    });

    Route::get('/finance-bank-advance-detail-outstanding', function () {
        return view('finance.bank-advance-detail-outstanding');
    });

    Route::post('/finance-bank-advance-detail-outstanding-submit', function () {
        return view('finance.bank-advance-detail-outstanding-submit');
    });

    Route::get('/xml-finance-bank-advance-outstanding', function () {
        return view('finance.xml.bank-advance-outstanding');
    });

    Route::get('/my-bank-advance-detail', function () {
        return view('mymenu.bank-advance-detail');
    });

    Route::get('/xml-my-bank-advance-detail', function () {
        return view('mymenu.xml.bank-advance-detail');
    });

    Route::get('/my-bank-advance', function () {
        return view('mymenu.bank-advance');
    });

    Route::get('/xml-my-bank-advance', function () {
        return view('mymenu.xml.bank-advance');
    });

    Route::post('/my-bank-advance-add-submit', function () {
        return view('mymenu.bank-advance-add-submit');
    });

    Route::get('/my-bank-advance-add', function () {
        return view('mymenu.bank-advance-add');
    });

    Route::post('/my-bank-advance-add-submit', function () {
        return view('mymenu.bank-advance-add-submit');
    });

    Route::get('/finance-bank-advance-add', function () {
        return view('finance.bank-advance-add');
    });

    Route::get('/finance-bank-advance-search-user', function () {
        return view('finance.bank-advance-search-user');
    });

    Route::post('/finance-bank-advance-add-submit', function () {
        return view('finance.bank-advance-add-submit');
    });

    Route::post('/finance-bank-advance-update', function () {
        return view('finance.bank-advance-update');
    });

    Route::get('/finance-bank-advance-detail', function () {
        return view('finance.bank-advance-detail');
    });

    Route::get('/xml-finance-bank-advance-detail', function () {
        return view('finance.xml.bank-advance-detail');
    });

    Route::post('/finance-bank-advance-detail-update', function () {
        return view('finance.bank-advance-detail-update');
    });

    Route::get('/finance-bank-out', function () {
        return view('finance.bank-out');
    });

    Route::get('/xml-finance-bank-out', function () {
        return view('finance.xml.bank-out');
    });

    Route::post('/finance-bank-out-update', function () {
        return view('finance.bank-out-update');
    });

    Route::get('/finance-bank-out-detail', function () {
        return view('finance.bank-out-detail');
    });

    Route::get('/xml-finance-bank-out-detail', function () {
        return view('finance.xml.bank-out-detail');
    });

    Route::get('/finance-bank-out-detail-coa', function () {
        return view('finance.bank-out-detail-coa');
    });

    Route::get('/xml-finance-bank-out-detail-coa', function () {
        return view('finance.xml.bank-out-detail-coa');
    });

    Route::post('/finance-bank-out-detail-update', function () {
        return view('finance.bank-out-detail-update');
    });

    Route::get('/finance-bank-transfer-add', function () {
        return view('finance.bank-transfer-add');
    });

    Route::post('/finance-bank-transfer-add-submit', function () {
        return view('finance.bank-transfer-add-submit');
    });

    Route::post('/finance-bank-transfer-add-submit-receive', function () {
        return view('finance.bank-transfer-add-submit-receive');
    });

    Route::get('/finance-bank-transfer', function () {
        return view('finance.bank-transfer');
    });

    Route::get('/xml-finance-bank-transfer', function () {
        return view('finance.xml.bank-transfer');
    });

    Route::post('/finance-bank-transfer-update', function () {
        return view('finance.bank-transfer-update');
    });

    Route::get('/finance-bank-transfer-detail', function () {
        return view('finance.bank-transfer-detail');
    });

    Route::get('/finance-bank-withdrawal-add', function () {
        return view('finance.bank-withdrawal-add');
    });

    Route::post('/finance-bank-withdrawal-add-submit', function () {
        return view('finance.bank-withdrawal-add-submit');
    });

    Route::get('/finance-bank-withdrawal', function () {
        return view('finance.bank-withdrawal');
    });

    Route::get('/xml-finance-bank-withdrawal', function () {
        return view('finance.xml.bank-withdrawal');
    });

    Route::post('/finance-bank-withdrawal-update', function () {
        return view('finance.bank-withdrawal-update');
    });

    Route::get('/finance-bank-withdrawal-detail', function () {
        return view('finance.bank-withdrawal-detail');
    });

    Route::post('/finance-bank-withdrawal-add-submit-confirm', function () {
        return view('finance.bank-withdrawal-add-submit-confirm');
    });

    //acounting approval

    Route::get('/accounting-master-pum', function () {
        return view('accounting.pum');
    });

    Route::get('/pum-add', function () {
        return view('accounting.pum-add');
    });

    Route::post('/pum-add-update', function () {
        return view('accounting.pum-add-update');
    });

    Route::get('/xml-pum-add', function () {
        return view('accounting.xml.pum-add');
    });

    Route::post('/pum-update', function () {
        return view('accounting.pum-update');
    });

    Route::get('/xml-accounting-pum', function () {
        return view('accounting.xml.pum');
    });

    Route::get('/accounting-recurring-data', function () {
        return view('accounting.recurring-data');
    });

    Route::get('/accounting-recurring-data-detail', function () {
        return view('accounting.recurring-data-detail');
    });

    Route::post('/accounting-recurring-data-detail-update', function () {
        return view('accounting.recurring-data-detail-update');
    });

    Route::get('/accounting-recurring-manual-exec', function () {
        return view('accounting.recurring-manual-exec');
    });

    Route::post('/accounting-recurring-data-update', function () {
        return view('accounting.recurring-data-update');
    });

    Route::get('/xml-accounting-recurring-data', function () {
        return view('accounting.xml.recurring-data');
    });

    Route::get('/xml-accounting-recurring-data-detail', function () {
        return view('accounting.xml.recurring-data-detail');
    });

    Route::get('/accounting-recurring-data-create-new', function () {
        return view('accounting.recurring-create-new');
    });

    Route::post('/accounting-recurring-create-new-submit', function () {
        return view('accounting.recurring-create-new-submit');
    });

    Route::post('/accounting-recurring-create-new-update', function () {
        return view('accounting.recurring-create-new-update');
    });

    Route::get('/xml-accounting-recurring-cash-out-detail', function () {
        return view('accounting.xml.recurring-cash-out-detail');
    });

    Route::get('/recurring-cash-out-detail-coa', function () {
        return view('accounting.recurring-cash-out-detail-coa');
    });

    Route::get('/xml-recurring-cash-out-detail-coa', function () {
        return view('accounting.xml.recurring-cash-out-detail-coa');
    });

    Route::get('/accounting-recurring-bank-out', function () {
        return view('accounting.recurring-bank-out');
    });

    Route::get('/xml-accounting-recurring-bank-out', function () {
        return view('accounting.xml.recurring-bank-out');
    });

    Route::get('/accounting-recurring-finished', function () {
        return view('accounting.recurring-finished');
    });

    Route::get('/xml-accounting-recurring-finished', function () {
        return view('accounting.xml.recurring-finished');
    });

    Route::get('/accounting-cash-in', function () {
        return view('accounting.cash-in');
    });

    Route::get('/xml-accounting-cash-in', function () {
        return view('accounting.xml.cash-in');
    });

    Route::post('/accounting-cash-in-update', function () {
        return view('accounting.cash-in-update');
    });

    Route::get('/accounting-cash-in-detail', function () {
        return view('accounting.cash-in-detail');
    });

    Route::get('/xml-accounting-cash-in-detail', function () {
        return view('accounting.xml.cash-in-detail');
    });

    Route::get('/accounting-cash-in-detail-coa', function () {
        return view('accounting.cash-in-detail-coa');
    });

    Route::get('/xml-accounting-cash-in-detail-coa', function () {
        return view('accounting.xml.cash-in-detail-coa');
    });

    Route::post('/accounting-cash-in-detail-update', function () {
        return view('accounting.cash-in-detail-update');
    });

    Route::get('/accounting-cash-out', function () {
        return view('accounting.cash-out');
    });

    Route::get('/xml-accounting-cash-out', function () {
        return view('accounting.xml.cash-out');
    });

    Route::post('/accounting-cash-out-update', function () {
        return view('accounting.cash-out-update');
    });

    Route::get('/accounting-cash-out-detail', function () {
        return view('accounting.cash-out-detail');
    });

    Route::get('/xml-accounting-cash-out-detail', function () {
        return view('accounting.xml.cash-out-detail');
    });

    Route::get('/accounting-cash-out-detail-coa', function () {
        return view('accounting.cash-out-detail-coa');
    });

    Route::get('/xml-accounting-cash-out-detail-coa', function () {
        return view('accounting.xml.cash-out-detail-coa');
    });

    Route::post('/accounting-cash-out-detail-update', function () {
        return view('accounting.cash-out-detail-update');
    });

    Route::get('/accounting-cash-transfer-add', function () {
        return view('accounting.cash-transfer-add');
    });

    Route::post('/accounting-cash-transfer-add-submit', function () {
        return view('accounting.cash-transfer-add-submit');
    });

    Route::post('/accounting-cash-transfer-add-submit-receive', function () {
        return view('accounting.cash-transfer-add-submit-receive');
    });

    Route::get('/accounting-cash-transfer', function () {
        return view('accounting.cash-transfer');
    });

    Route::get('/xml-accounting-cash-transfer', function () {
        return view('accounting.xml.cash-transfer');
    });

    Route::post('/accounting-cash-transfer-update', function () {
        return view('accounting.cash-transfer-update');
    });

    Route::get('/accounting-cash-transfer-detail', function () {
        return view('accounting.cash-transfer-detail');
    });

    Route::get('/accounting-cash-advance', function () {
        return view('accounting.cash-advance');
    });

    Route::get('/xml-accounting-cash-advance', function () {
        return view('accounting.xml.cash-advance');
    });

    Route::get('/accounting-cash-advance-add', function () {
        return view('accounting.cash-advance-add');
    });

    Route::get('/accounting-cash-advance-search-user', function () {
        return view('accounting.cash-advance-search-user');
    });

    Route::post('/accounting-cash-advance-add-submit', function () {
        return view('accounting.cash-advance-add-submit');
    });

    Route::post('/accounting-cash-advance-update', function () {
        return view('accounting.cash-advance-update');
    });

    Route::post('/accounting-cash-advance-detail-update', function () {
        return view('accounting.cash-advance-detail-update');
    });

    Route::get('/accounting-cash-advance-detail', function () {
        return view('accounting.cash-advance-detail');
    });

    Route::get('/accounting-cash-advance-detail-coa', function () {
        return view('accounting.cash-advance-detail-coa');
    });

    Route::get('/xml-accounting-cash-advance-detail-coa', function () {
        return view('accounting.xml.cash-advance-detail-coa');
    });

    Route::get('/xml-accounting-cash-advance-detail', function () {
        return view('accounting.xml.cash-advance-detail');
    });

    //accounting bank advanced posting

    Route::get('/accounting-bank-advance', function () {
        return view('accounting.bank-advance');
    });

    Route::get('/xml-accounting-bank-advance', function () {
        return view('accounting.xml.bank-advance');
    });

    Route::get('/accounting-bank-advance-add', function () {
        return view('accounting.bank-advance-add');
    });

    Route::get('/accounting-bank-advance-search-user', function () {
        return view('accounting.bank-advance-search-user');
    });

    Route::post('/accounting-bank-advance-add-submit', function () {
        return view('accounting.bank-advance-add-submit');
    });

    Route::post('/accounting-bank-advance-update', function () {
        return view('accounting.bank-advance-update');
    });

    Route::post('/accounting-bank-advance-detail-update', function () {
        return view('accounting.bank-advance-detail-update');
    });

    Route::get('/accounting-bank-advance-detail', function () {
        return view('accounting.bank-advance-detail');
    });

    Route::get('/accounting-bank-advance-detail-coa', function () {
        return view('accounting.bank-advance-detail-coa');
    });

    Route::get('/xml-accounting-bank-advance-detail-coa', function () {
        return view('accounting.xml.bank-advance-detail-coa');
    });

    Route::get('/xml-accounting-bank-advance-detail', function () {
        return view('accounting.xml.bank-advance-detail');
    });

    // Cash Deposit

    Route::get('/accounting-cash-deposit-add', function () {
        return view('accounting.cash-deposit-add');
    });

    Route::get('/accounting-cash-deposit', function () {
        return view('accounting.cash-deposit');
    });

    Route::get('/xml-accounting-cash-deposit', function () {
        return view('accounting.xml.cash-deposit');
    });

    Route::post('/accounting-cash-deposit-update', function () {
        return view('accounting.cash-deposit-update');
    });

    Route::post('/accounting-cash-deposit-detail-submit', function () {
        return view('accounting.cash-deposit-detail-submit');
    });

    Route::get('/accounting-cash-deposit-detail', function () {
        return view('accounting.cash-deposit-detail');
    });

    //bank

    Route::get('/xml-accounting-bank', function () {
        return view('accounting.xml.accounting-bank');
    });

    Route::get('/xml-accounting-currency', function () {
        return view('accounting.xml.accounting-currency');
    });

    Route::get('/accounting-bank-in', function () {
        return view('accounting.bank-in');
    });

    Route::get('/xml-accounting-bank-in', function () {
        return view('accounting.xml.bank-in');
    });

    Route::post('/accounting-bank-in-update', function () {
        return view('accounting.bank-in-update');
    });

    Route::get('/accounting-bank-in-detail', function () {
        return view('accounting.bank-in-detail');
    });

    Route::get('/xml-accounting-bank-in-detail', function () {
        return view('accounting.xml.bank-in-detail');
    });

    Route::get('/accounting-bank-in-detail-coa', function () {
        return view('accounting.bank-in-detail-coa');
    });

    Route::get('/xml-accounting-bank-in-detail-coa', function () {
        return view('accounting.xml.bank-in-detail-coa');
    });

    Route::post('/accounting-bank-in-detail-update', function () {
        return view('accounting.bank-in-detail-update');
    });

    Route::get('/accounting-bank-out', function () {
        return view('accounting.bank-out');
    });

    Route::get('/xml-accounting-bank-out', function () {
        return view('accounting.xml.bank-out');
    });

    Route::post('/accounting-bank-out-update', function () {
        return view('accounting.bank-out-update');
    });

    Route::get('/accounting-bank-out-detail', function () {
        return view('accounting.bank-out-detail');
    });

    Route::get('/xml-accounting-bank-out-detail', function () {
        return view('accounting.xml.bank-out-detail');
    });

    Route::get('/accounting-bank-out-detail-coa', function () {
        return view('accounting.bank-out-detail-coa');
    });

    Route::get('/xml-accounting-bank-out-detail-coa', function () {
        return view('accounting.xml.bank-out-detail-coa');
    });

    Route::post('/accounting-bank-out-detail-update', function () {
        return view('accounting.bank-out-detail-update');
    });

    Route::get('/accounting-bank-transfer-add', function () {
        return view('accounting.bank-transfer-add');
    });

    Route::post('/accounting-bank-transfer-add-submit', function () {
        return view('accounting.bank-transfer-add-submit');
    });

    Route::get('/accounting-bank-transfer', function () {
        return view('accounting.bank-transfer');
    });

    Route::get('/xml-accounting-bank-transfer', function () {
        return view('accounting.xml.bank-transfer');
    });

    Route::post('/accounting-bank-transfer-update', function () {
        return view('accounting.bank-transfer-update');
    });

    Route::get('/accounting-bank-transfer-detail', function () {
        return view('accounting.bank-transfer-detail');
    });

    Route::post('/accounting-bank-transfer-add-submit-receive', function () {
        return view('accounting.bank-transfer-add-submit-receive');
    });

    Route::get('/accounting-bank-withdrawal', function () {
        return view('accounting.bank-withdrawal');
    });

    Route::get('/xml-accounting-bank-withdrawal', function () {
        return view('accounting.xml.bank-withdrawal');
    });

    Route::post('/accounting-bank-withdrawal-update', function () {
        return view('accounting.bank-withdrawal-update');
    });

    Route::get('/accounting-bank-withdrawal-detail', function () {
        return view('accounting.bank-withdrawal-detail');
    });

    Route::post('/accounting-bank-withdrawal-add-submit-confirm', function () {
        return view('accounting.bank-withdrawal-add-submit-confirm');
    });

    ///producrement
    Route::get('/po-contract-data', function () {
        return view('procurement.contract-data');
    });

    Route::get('/add-contract-data', function () {
        return view('procurement.add-contract-data');
    });

    Route::post('/add-contract-data-submit', function () {
        return view('procurement.add-contract-data-submit');
    });

    Route::get('/xml-po-contract-data', function () {
        return view('procurement.xml.contract-data');
    });

    Route::get('/add-receive-invoice', function () {
        return view('procurement.add-receive-invoice');
    });

    Route::post('/add-receive-invoice-submit', function () {
        return view('procurement.add-receive-invoice-submit');
    });

    Route::get('/po-receive-invoice', function () {
        return view('procurement.po-receive-invoice');
    });

    Route::get('/search-po-manual', function () {
        return view('procurement.po-search-manual');
    });

    Route::get('/search-supplier', function () {
        return view('procurement.search-supplier');
    });

    Route::get('/po-receive-invoice-detail', function () {
        return view('procurement.po-receive-invoice-detail');
    });

    Route::post('/po-receive-invoice-detail-submit', function () {
        return view('procurement.po-receive-invoice-detail-submit');
    });

    Route::get('/printfpp_po', function () {
        return view('procurement.prints.printfpp_po');
    });

    Route::get('/po-receive-invoice-detail', function () {
        return view('procurement.po-receive-invoice-detail');
    });

    Route::get('/xml-po-receive-invoice', function () {
        return view('procurement.xml.po-receive-invoice');
    });

    Route::get('/po-invoice-management', function () {
        return view('procurement.po-invoice-management');
    });

    Route::get('/xml-po-invoice-management', function () {
        return view('procurement.xml.po-invoice-management');
    });

    Route::get('/po-invoice-history', function () {
        return view('procurement.po-receive-invoice-history');
    });

    Route::get('/xml-po-receive-invoice-history', function () {
        return view('procurement.xml.po-receive-invoice-history');
    });

    Route::get('/po-receive-invoice-history-detail', function () {
        return view('procurement.po-receive-invoice-history-detail');
    });

    Route::get('/purchasing-master-vendor', function () {
        return view('procurement.master-vendor');
    });

    Route::get('/xml-purchasing-master-vendor', function () {
        return view('procurement.xml.master-vendor');
    });

    Route::get('/purchasing-master-vendor-detail', function () {
        return view('procurement.master-vendor-detail');
    });

    Route::post('/master-vendor-detail-submit', function () {
        return view('procurement.master-vendor-detail-submit');
    });

    Route::get('/purchasing-master-vendor-new', function () {
        return view('procurement.master-vendor-new');
    });

    Route::post('/master-vendor-new-submit', function () {
        return view('procurement.master-vendor-new-submit');
    });

    Route::get('/approval-stock', function () {
        return view('project.approval-stock');
    });

    Route::get('/approval-stock-detail', function () {
        return view('project.approval-stock-detail');
    });

    Route::get('/xml-approval-stock', function () {
        return view('project.xml.approval-stock');
    });

    Route::get('/approval-purchase', function () {
        return view('project.approval-purchase');
    });

    Route::get('/xml-approval-purchase', function () {
        return view('project.xml.pr-request-approval');
    });

    Route::get('/approval-purchase-detail', function () {
        return view('project.approval-purchase-detail');
    });

    Route::get('/asset-master-product-code', function () {
        return view('procurement.master-product-code');
    });

    Route::get('/xml-master-product-code', function () {
        return view('procurement.xml.master-product-code');
    });

    Route::post('/master-product-code-update', function () {
        return view('procurement.master-product-code-update');
    });

    Route::get('/purchasing-master-product', function () {
        return view('procurement.master-product-code');
    });

    Route::get('/xml-master-product-code', function () {
        return view('procurement.xml.master-product-code');
    });

    Route::get('/master-product-code-detail', function () {
        return view('procurement.master-product-code-detail');
    });

    Route::post('/master-product-code-detail-submit', function () {
        return view('procurement.master-product-code-detail-submit');
    });

    Route::get('/master-product-code-new', function () {
        return view('procurement.master-product-code-new');
    });

    Route::post('/master-product-code-new-submit', function () {
        return view('procurement.master-product-code-new-submit');
    });

    Route::get('/purchasing-request-new', function () {
        return view('procurement.pr-request-new');
    });

    Route::post('/pr-request-new-update', function () {
        return view('procurement.pr-request-new-update');
    });

    Route::post('/pr-request-new-submit', function () {
        return view('procurement.pr-request-new-submit');
    });

    Route::get('/pr-load-sub', function () {
        return view('procurement.pr-load-sub');
    });

    Route::get('/pr-load-subcat', function () {
        return view('procurement.pr-load-subcat');
    });

    Route::get('/pr-load-subcat2', function () {
        return view('procurement.pr-load-subcat2');
    });

    Route::get('/purchasing-product-list', function () {
        return view('procurement.purchasing-product-list');
    });

    Route::get('/xml-purchasing-product-lists', function () {
        return view('procurement.xml.purchasing-product-list');
    });

    Route::get('/purchasing-request-approval', function () {
        return view('procurement.pr-request-approval');
    });

    Route::get('/xml-purchasing-request-approval', function () {
        return view('procurement.xml.pr-request-approval');
    });

    Route::get('/purchasing-request-approval-detail', function () {
        return view('procurement.pr-request-approval-detail');
    });

    Route::post('/purchasing-request-approval-detail-submit', function () {
        return view('procurement.pr-request-approval-detail-submit');
    });

    Route::post('/purchasing-request-approval-detail-update', function () {
        return view('procurement.pr-request-approval-detail-update');
    });

    Route::post('/purchasing-request-approval-detail-reject', function () {
        return view('procurement.pr-request-approval-detail-reject');
    });

    Route::get('/xml-purchasing-request-approval-detail', function () {
        return view('procurement.xml.pr-request-approval-detail');
    });

    Route::get('/purchasing-request-outstanding', function () {
        return view('procurement.pr-request-outstanding');
    });

    Route::get('/xml-pr-request-outstanding', function () {
        return view('procurement.xml.pr-request-outstanding');
    });

    Route::post('/purchasing-request-outstanding-update', function () {
        return view('procurement.pr-request-outstanding-update');
    });

    Route::get('/po-input-vendor', function () {
        return view('procurement.po-input-vendor');
    });

    Route::post('/po-input-vendor-submit', function () {
        return view('procurement.po-input-vendor-submit');
    });

    Route::get('/purchasing-order-approval', function () {
        return view('procurement.purchasing-order-approval');
    });

    Route::get('/xml-purchasing-order-approval', function () {
        return view('procurement.xml.purchasing-order-approval');
    });

    Route::get('/purchasing-order-collection', function () {
        return view('procurement.purchasing-order-collection');
    });

    Route::get('/xml-po-collection', function () {
        return view('procurement.xml.po-collection');
    });

    Route::get('/purchasing-order-collection-detail', function () {
        return view('procurement.purchasing-order-collection-detail');
    });

    Route::get('/purchasing-order-vendor-selection', function () {
        return view('procurement.purchasing-order-vendor-selection');
    });

    Route::get('/xml-purchasing-order-vendor-selection', function () {
        return view('procurement.xml.purchasing-order-vendor-selection');
    });

    Route::get('/purchasing-order-pending', function () {
        return view('procurement.purchasing-order-pending');
    });

    Route::get('/purchasing-order-cancel', function () {
        return view('procurement.purchasing-order-cancel');
    });

    Route::get('/purchasing-order-rfq', function () {
        return view('humanresources.procurement.purchasing-order-rfq');
    });

    Route::get('/xml-purchasing-order-rfq', function () {
        return view('humanresources.procurement.xml.purchasing-order-rfq');
    });

    // Route::get('/qtn-create-detail', function () {
    //     return view('procurement.qtn-create-detail');
    // });

    Route::get('/qtn-create-detail', [ProcurementController::class, 'createDetail']);

    

    Route::get('/purchasing-order-approved', function () {
        return view('procurement.purchasing-order-approved');
    });

    Route::get('/xml-purchasing-order-approved', function () {
        return view('procurement.xml.purchasing-order-approved');
    });

    Route::get('/purchasing-order-approved-detail', function () {
        return view('procurement.purchasing-order-approved-detail');
    });

    Route::get('/purchasing-request-cancelation', function () {
        return view('procurement.pr-request-cancelation');
    });

    Route::get('/purchasing-request-cancelation-detail', function () {
        return view('procurement.pr-request-cancelation-detail');
    });

    Route::get('/xml-pr-request-cancelation', function () {
        return view('procurement.xml.pr-request-cancelation');
    });

    Route::get('/purchasing-request-cancelation', function () {
        return view('procurement.pr-request-cancelation');
    });

    Route::get('/xml-pr-request-cancelation', function () {
        return view('procurement.xml.pr-request-cancelation');
    });

    Route::get('/purchasing-order-price-input-detail', function () {
        return view('procurement.purchasing-order-price-input-detail');
    });

    Route::get('/printout_po', function () {
        return view('procurement.prints.printout_po');
    });

    Route::get('/purchasing-order-approval-detail', function () {
        return view('procurement.purchasing-order-approval-detail');
    });

    Route::get('/xml-purchasing-order-approval-detail', function () {
        return view('procurement.xml.purchasing-order-approval-detail');
    });

    Route::post('/purchasing-order-price-input-detail-update', function () {
        return view('procurement.purchasing-order-price-input-detail-update');
    });

    Route::post('/purchasing-order-price-input-detail-submit', function () {
        return view('procurement.purchasing-order-price-input-detail-submit');
    });

    Route::post('/purchasing-order-price-input-detail-submit2', function () {
        return view('procurement.purchasing-order-price-input-detail-submit2');
    });

    Route::post('/purchasing-order-approval-detail-submit', function () {
        return view('procurement.purchasing-order-approval-detail-submit');
    });

    Route::post('/purchasing-order-approval-detail-submit2', function () {
        return view('procurement.purchasing-order-approval-detail-submit2');
    });

    Route::get('/xml-purchasing-order-input-price-detail', function () {
        return view('procurement.xml.purchasing-order-input-price-detail');
    });

    Route::get('/purchasing-order-approval-reject1', function () {
        return view('procurement.purchasing-order-approval-reject1');
    });

    Route::get('/xml-purchasing-order-approval', function () {
        return view('procurement.xml.purchasing-order-approval');
    });

    Route::get('/purchasing-order-price-input', function () {
        return view('procurement.purchasing-order-price-input');
    });

    Route::get('/xml-purchasing-order-price-input', function () {
        return view('procurement.xml.purchasing-order-price-input');
    });

    Route::post('/purchasing-cancelation-detail-submit', function () {
        return view('procurement.pr-request-cancelation-detail-submit');
    });

                                                  
                      
                                                            
             
     

    Route::get('/sales-proforma-monitoring', function () {
        return view('sales.proforma-monitoring');
    });

    Route::get('/xml-sales-proforma-monitoring', function () {
        return view('sales.xml.proforma-monitoring');
    });

    Route::get('/sales-proforma-monitoring-detail', function () {
        return view('sales.proforma-monitoring-detail');
    });

    // route untuk po outstanding

    Route::get('/my-po-outstanding', function () {
        return view('mymenu.po-outstanding');
    });

    Route::get('/my-po-outstanding-detail', function () {
        return view('mymenu.po-outstanding-detail');
    });

    Route::get('/xml-my-po-outstanding', function () {
        return view('mymenu.xml.po-outstanding');
    });

    Route::get('/finance-report-archive-general-ledgers', function () {
        return view('finance.archives.general-ledgers');
    });

    Route::get('/xml-finance-report-archive-general-ledgers', function () {
        return view('finance.archives.xml.general-ledgers');
    });

    Route::get('/my-cash-advance-add', function () {
        return view('mymenu.cash-advance-add');
    });

    Route::post('/my-cash-advance-add-submit', function () {
        return view('mymenu.cash-advance-add-submit');
    });

    Route::get('/approvalcashadvance', function () {
        return view('scheduler.approvalcashadvance');
    });

    Route::get('/approvalcashadvance_approve', function () {
        return view('scheduler.approvalcashadvance_approve');
    });

    Route::get('/approvalcashadvance_reject', function () {
        return view('scheduler.approvalcashadvance_reject');
    });

    Route::post('/accounting-report-general-ledgers-update', function () {
        return view('accounting.report.general-ledgers-update');
    });

    Route::get('/sales-search-proforma-invoice-to', function () {
        return view('sales.search-proforma-invoice-to');
    });

    Route::get('/sales-search-proforma-user-signed', function () {
        return view('sales.search-proforma-user-signed');
    });

    Route::get('/sales-search-proforma-user-signed-2', function () {
        return view('sales.search-proforma-user-signed2');
    });

    Route::post('/sales-proforma-ba-update', function () {
        return view('sales.proforma-ba-update');
    });

    Route::get('/finance-cash-advance-outstanding', function () {
        return view('finance.cash-advance-outstanding');
    });

    Route::get('/finance-cash-advance-detail-outstanding', function () {
        return view('finance.cash-advance-detail-outstanding');
    });

    Route::post('/finance-cash-advance-detail-outstanding-submit', function () {
        return view('finance.cash-advance-detail-outstanding-submit');
    });

    Route::get('/xml-finance-cash-advance-outstanding', function () {
        return view('finance.xml.cash-advance-outstanding');
    });

    Route::post('/accounting-master-coa-update', function () {
        return view('accounting.master-coa-update');
    });

    Route::get('/finance-finish-bank-advance', function () {
        return view('finance.finish.bank-advance');
    });

    Route::get('/finance-finish-bank-advance-detail', function () {
        return view('finance.finish.bank-advance-detail');
    });

    Route::get('/xml-finance-finish-bank-advance', function () {
        return view('finance.finish.xml.bank-advance');
    });

    Route::get('/xml-finance-finish-bank-advance-detail', function () {
        return view('finance.finish.xml.bank-advance-detail');
    });

    Route::get('/sales-master-proforma-signed', function () {
        return view('sales.master-proforma-signed');
    });

    Route::get('/xml-sales-master-proforma-signed', function () {
        return view('sales.xml.master-proforma-signed');
    });

    Route::post('/master-proforma-signed-update', function () {
        return view('sales.master-proforma-signed-update');
    });

    Route::get('/sales-master-proforma-signed-2', function () {
        return view('sales.master-proforma-signed2');
    });

    Route::get('/xml-sales-master-proforma-signed-2', function () {
        return view('sales.xml.master-proforma-signed2');
    });

    Route::post('/master-proforma-signed-button', function () {
        return view('sales.master-proforma-signed-button');
    });

    Route::get('/sales-proforma-posted-history', function () {
        return view('sales.proforma-posted-history');
    });

    Route::get('/xml-sales-proforma-posted-history', function () {
        return view('sales.xml.proforma-posted-history');
    });

    Route::get('/project-spj', function () {
        return view('project.project-spj');
    });

    Route::get('/project-spj-detail', function () {
        return view('project.project-spj-detail');
    });

    Route::get('/xml-project-spj-detail', function () {
        return view('project.xml.project-spj-detail');
    });

    Route::get('/project-spj-add', function () {
        return view('project.project-spj-add');
    });

    Route::post('/project-spj-add-submit', function () {
        return view('project.project-spj-add-submit');
    });

    Route::get('/approvalspj', function () {
        return view('scheduler.approvalspj');
    });

    Route::get('/approvalspj_approve', function () {
        return view('scheduler.approvalspj_approve');
    });

    Route::get('/approvalspj_reject', function () {
        return view('scheduler.approvalspj_reject');
    });

    Route::get('/submitted-spj', function () {
        return view('humanresources.other.submitted-spj');
    });

    Route::get('/xml-submitted-spj', function () {
        return view('humanresources.other.xml.submitted-spj');
    });

    Route::get('/new-reimbursment-spj', function () {
        return view('humanresources.other.new-reimbursment-spj');
    });

    Route::get('/new-reimbursment-spj', function () {
        return view('humanresources.other.new-reimbursment-spj');
    });

    Route::post('/reimbursement-spj-submit', function () {
        return view('humanresources.other.reimbursement-spj-submit');
    });

    Route::get('/finance-report-bank-advance', function () {
        return view('finance.report.bank-advance');
    });

    Route::get('/xml-finance-report-bank-advance', function () {
        return view('finance.report.xml.bank-advance');
    });

    Route::get('/finance-report-bank-advance-detail', function () {
        return view('finance.report.bank-advance-detail');
    });

    Route::get('/xml-finance-report-bank-advance-detail', function () {
        return view('finance.report.xml.bank-advance-detail');
    });

    Route::get('/proyeksi-cashflow-management-detail', function () {
        return view('accounting.cashflow.proyeksi-cashflow-management-detail');
    });

    Route::post('/finance-proyeksi-cashflow-management-detail-update', function () {
        return view('accounting.cashflow.proyeksi-cashflow-management-detail-update');
    });

    Route::get('/xml-finance-proyeksi-cashflow-management-detail', function () {
        return view('accounting.cashflow.xml.proyeksi-cashflow-management-detail');
    });

    Route::get('/finance-proyeksi-cashflow', function () {
        return view('accounting.cashflow.proyeksi-cashflow');
    });

    Route::get('/xml-finance-proyeksi-cashflow-management', function () {
        return view('accounting.cashflow.xml.proyeksi-cashflow-management');
    });

    Route::post('/finance-proyeksi-cashflow-management-update', function () {
        return view('accounting.cashflow.proyeksi-cashflow-management-update');
    });

    Route::get('/proyeksi-cashflow-load-from-file', function () {
        return view('accounting.cashflow.load-from-file');
    });

    Route::get('/proyeksi-load-excel-coa', function () {
        return view('accounting.cashflow.load-excel-coa');
    });

    Route::get('/finance-proyeksi-cashflow-history', function () {
        return view('accounting.cashflow.proyeksi-cashflow-history');
    });

    Route::get('/xml-finance-proyeksi-cashflow-history', function () {
        return view('accounting.cashflow.xml.proyeksi-cashflow-history');
    });

    Route::get('/finance-proyeksi-cashflow-history-detail', function () {
        return view('accounting.cashflow.proyeksi-cashflow-history-detail');
    });

    Route::get('/xml-finance-proyeksi-cashflow-history-detail', function () {
        return view('accounting.cashflow.xml.proyeksi-cashflow-history-detail');
    });

    Route::get('/finance-proyeksi-cashflow-transaction', function () {
        return view('accounting.cashflow.proyeksi-cashflow-transaction');
    });

    Route::get('/xml-finance-proyeksi-cashflow-transaction', function () {
        return view('accounting.cashflow.xml.proyeksi-cashflow-transaction');
    });

    Route::get('/finance-proyeksi-cashflow-summary', function () {
        return view('accounting.cashflow.proyeksi-cashflow-summary');
    });

    Route::get('/xml-finance-proyeksi-cashflow-summary', function () {
        return view('accounting.cashflow.xml.proyeksi-cashflow-summary');
    });

    Route::get('/xml-accounting-report-coa', function () {
        return view('accounting.report.xml.coa');
    });

    Route::get('/accounting-report-gl-coa', function () {
        return view('accounting.report.gl-coa');
    });

    Route::get('/sales-report-po', function () {
        return view('sales.report.po-report');
    });

    Route::get('/xml-sales-report-po', function () {
        return view('sales.report.xml.po-report');
    });

    Route::get('/sales-report-monitoring', function () {
        return view('sales.report.po-monitoring');
    });

    Route::get('/xml-sales-report-monitoring', function () {
        return view('sales.report.xml.monitoring');
    });

    Route::get('/upload-foto', function () {
        return view('humanresources.employment.uploadfoto');
    });

    Route::get('/finance-ar-history-invoice', function () {
        return view('finance.ar-history-invoice');
    });

    Route::get('/xml-ar-history-invoice', function () {
        return view('finance.xml.ar-history-invoice');
    });

    Route::get('/sales-opportunity-closed-opportunity', function () {
        return view('sales.opportunity-closed');
    });

    Route::get('/xml-sales-opportunity-closed', function () {
        return view('sales.xml.opportunity-closed');
    });

    Route::get('/xml-load-pic-production', function () {
        return view('humanresources.recruitment.xml.pic-production');
    });

    Route::get('/finance-print-pum-tanda-terima', function () {
        return view('finance.prints.print-pum-tanda-terima');
    });

    Route::get('/finance-print-pum-tanda-keluar', function () {
        return view('finance.prints.print-tanda-keluar');
    });

    Route::get('/finance-report-billing-monitoring', function () {
        return view('finance.report.billing-monitoring');
    });

    Route::get('/xml-finance-report-billing-monitoring', function () {
        return view('finance.report.xml.billing-monitoring');
    });

    Route::post('/finance-report-ar-locator-update', function () {
        return view('finance.report.ar.ar-locator-update');
    });

    Route::get('/finance-report-ar-locator', function () {
        return view('finance.report.ar.ar-locator');
    });

    Route::get('/xml-finance-report-ar-locator', function () {
        return view('finance.report.ar.xml.ar-locator');
    });

    Route::get('/finance-report-ar-locator-upload-file', function () {
        return view('finance.report.ar.ar-locator-upload-file');
    });

    Route::get('/finance-report-ar-locator-load-excel', function () {
        return view('finance.report.ar.ar-locator-load-excel');
    });

    Route::get('/accounting-report-keuangan-rasio', function () {
        return view('accounting.report.keuangan.rasio');
    });

    Route::get('/xml-purchasing-request', function () {
        return view('project.xml.purchasing-request');
    });

    Route::post('/finance-cancel-invoice-update', function () {
        return view('finance.invoice-cancel-update');
    });

    Route::post('upload-csv', [UploaderController::class, 'uploadAndInsertFromCsv']);

    Route::get('download-leads-template', [UploaderController::class, 'downloadLeadsTemplate']);

    /** broadcast route */
    $broadcastPref = "broadcast";

    Route::get("$broadcastPref-index", [BroadcastController::class, 'index']);

    Route::get("$broadcastPref-detail", [BroadcastController::class, 'detail']);

    Route::get("$broadcastPref-create", [BroadcastController::class, 'create']);

    Route::post("$broadcastPref-submit", [BroadcastController::class, 'submit']);

    Route::get("$broadcastPref-load-data", [BroadcastController::class, 'indexXML']);

    Route::get("$broadcastPref-detail-data", [BroadcastController::class, 'detailData']);

    /** */

    /**  notification route */
    $notification = "notification";

    Route::get("$notification-getall", [NotificationController::class, 'getAll']);

    Route::post("$notification-read", [NotificationController::class, 'markAsRead']);

    Route::get("$notification-remind-contract-expiration", [NotificationController::class, 'reminderContractExpiration']);

    /** */


    /** profit sharing */
    $profitSharingPref = 'partner-profit-sharing';

    // Route::post("$profitSharingPref-proceed", [ApprovalController::class, 'processProfitSharingToNextProgress']);
    Route::get("$profitSharingPref-approval-index", [ApprovalController::class, 'indexProfitSharing']);

    Route::get("$profitSharingPref-approval-create", [ApprovalController::class, 'createProfitSharingApplication']);

    Route::get("$profitSharingPref-approval-detail", [ApprovalController::class, 'getProfitSharingApprovalDetail']);

    Route::post("$profitSharingPref-approve", [ApprovalController::class, 'processProfitSharingToNextProgress']);

    Route::get("my-profile", [UserController::class, 'profilePage']);

    /** Marketing & Branding */
    $marketingPrefs = "partner-branding";

    Route::get("$marketingPrefs-approval-index", [ApprovalController::class, 'indexMarketingApproval']);

    Route::get("$marketingPrefs-approval-index", [ApprovalController::class, 'indexMarketingApproval']);

    Route::post("$marketingPrefs-approval-submit", [ApprovalController::class, 'processPromotionApproval']);

                                                
                      
                                                       
             
     

                                                 
                      
                                                        
             
     

                                                     
                      
                                                            
             
     

                                                  
                      
                                                         
             
     

//             // Tambahkan rute ini di routes/web.php
//         Route::get('/purchase-request-list', function () {
//             return view('procurement.purchase-request-list');
//         });

//     Route::get('/xml-purchase-request-list', function () {
//     return response()->view('procurement.xml.purchase-request-list-xml', [], 200)
//                      ->header('Content-Type', 'text/xml');
// });

Route::get('/procurement-dashboard', [ProcurementController::class, 'dashboardProcurement']);

// Halaman Utama Master Product List
Route::get('/product-list', [ProcurementController::class, 'productList']);

Route::post('procurement-generate-po', [ProcurementController::class, 'generatePO']);

// Endpoint XML untuk DHTMLX Grid Master Produk
Route::get('/xml-product-list', [ProcurementController::class, 'xmlProductList']);

Route::post('/product-store', [ProcurementController::class, 'storeProduct']);

Route::get('/product-export-excel', [ProcurementController::class, 'exportProductExcel']);

// Mengambil detail produk untuk form Edit (AJAX)
Route::get('/product-edit/{id}', [ProcurementController::class, 'editProduct']);

// Menyimpan perubahan data produk (Update)
Route::post('/product-update/{id}', [ProcurementController::class, 'updateProduct']);

// Route untuk memproses penyimpanan Quotation / Penawaran Vendor
Route::post('qtn-store', [ProcurementController::class, 'storeQuotation']);

Route::get('vendor-comparison-matrix', [ProcurementController::class, 'vendorComparisonMatrix']);

Route::get('rfq-print', [ProcurementController::class, 'printRFQ']);

// ==================== MASTER SUPPLIER ====================
Route::get('/supplier-list', [SupplierController::class, 'supplierList']);

Route::get('/xml-supplier-list', [SupplierController::class, 'xmlSupplierList']);

Route::post('/supplier-store', [SupplierController::class, 'storeSupplier']);

Route::get('/supplier-edit/{id}', [SupplierController::class, 'editSupplier']);

Route::post('/supplier-update/{id}', [SupplierController::class, 'updateSupplier']);

    // 1. Route List PR
Route::get('/purchase-request-list', function () {
    return view('humanresources.procurement.purchase-request-list');
});

// 2. Route XML List PR
Route::get('/xml-purchase-request-list', function () {
    return response()->view('humanresources.procurement.xml.purchase-request-list-xml', [], 200)
                     ->header('Content-Type', 'text/xml');
});

// Detail PR Pending (Dimuat via AJAX LoadM ke dalam PRDiv)
Route::get('/purchase-request-detail', [ProcurementController::class, 'detailPR']);

Route::get('/purchase-request-detail-pending', [ProcurementController::class, 'detailPRPending']);

Route::post('/purchase-request-update', [ProcurementController::class, 'updatePR']);

// Proses Update Status Approval (Approve / Reject)
Route::post('/purchase-request-approve', [ProcurementController::class, 'approvePR']);

// Detail PR Approved
Route::get('/purchase-request-detail-approved', [ProcurementController::class, 'detailPRApproved']);

// Proses Lanjut ke RFQ dari PR Approved

Route::post('purchase-request-to-rfq', [App\Http\Controllers\ProcurementController::class, 'convertToRFQ']);

// Menggunakan Controller untuk Form, AJAX, dan Store
Route::get('/purchase-request-create', [ProcurementController::class, 'createPR']);

Route::get('/get-material-detail', [ProcurementController::class, 'getMaterialDetail']);

Route::post('/purchase-request-store', [ProcurementController::class, 'storePR']);

// Halaman & Data Outstanding PO
Route::get('po-outstanding-list', [ProcurementController::class, 'outstandingPOIndex']);

Route::get('xml-outstanding-po-list', [ProcurementController::class, 'xmlOutstandingPO']);

Route::post('generate-split-po', [ProcurementController::class, 'generateSplitPO']);

Route::post('cancel-po', [ProcurementController::class, 'cancelPO']);

Route::post('po-approve', [ProcurementController::class, 'approvePO']);

Route::post('po-delivery', [ProcurementController::class, 'deliveryPO']);

Route::post('po-receive', [ProcurementController::class, 'receivePO']);

Route::get('po-detail', [ProcurementController::class, 'detailPO']);

Route::get('po-print', [ProcurementController::class, 'printPO']);

Route::get('print-pr', [ProcurementController::class, 'printPR']);


Route::get('/budget-load-batch-coa', function () {
        return view('finance.budget.load-batch-coa');
    });

Route::get('/budget-load-from-file', function () {
        return view('finance.budget.load-from-file');
    });

Route::get('/budget-finalize', function () {
        return view('finance.budget.finalize');
    });

Route::get('/budget-finalize-submit', function () {
        return view('finance.budget.finalize-submit');
    });

Route::get('/load-excel-coa', function () {
        return view('finance.budget.load-excel-coa');
    });

Route::get('/combo-coa', function () {
        return view('accounting.xml.combo-coa');
    });

Route::get('/combo-coa-all', function () {
        return view('accounting.xml.combo-coa-all');
    });

// Theme sync: procurement goods received views
Route::get('goods-received-list', [ProcurementController::class, 'goodsReceivedIndex']);
Route::get('xml-goods-received-list', [ProcurementController::class, 'xmlGoodsReceivedList']);
Route::get('goods-received-detail', [ProcurementController::class, 'detailGoodsReceived']);
