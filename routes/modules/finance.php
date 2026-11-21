<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Finance\FinanceApprovalController;

    Route::get('finance-hr-approval-index', [FinanceApprovalController::class, 'index']);
    Route::get('finance-hr-approval-data', [FinanceApprovalController::class, 'data']);
    Route::get('finance-hr-approval-detail', [FinanceApprovalController::class, 'detail']);
    Route::get('finance-hr-approval-workflow-detail', [FinanceApprovalController::class, 'workflowDetail']);
    Route::post('finance-hr-approval-workflow-act', [FinanceApprovalController::class, 'actWorkflow']);
    Route::post('finance-hr-approval-maintenance-confirm', [FinanceApprovalController::class, 'confirmMaintenanceVoucher']);
    Route::post('finance-hr-approval-tax-pay', [FinanceApprovalController::class, 'payVehicleTaxVoucher']);

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
