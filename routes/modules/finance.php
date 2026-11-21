<?php
use App\Http\Controllers\Finance\CostManagementController;
use App\Http\Controllers\OperationalController;
use App\Http\Controllers\ExpenditureController;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Finance\FinanceApprovalController;

    Route::get('finance-hr-approval-index', [FinanceApprovalController::class, 'index']);
    Route::get('finance-hr-approval-data', [FinanceApprovalController::class, 'data']);
    Route::get('finance-hr-approval-detail', [FinanceApprovalController::class, 'detail']);
    Route::get('finance-hr-approval-workflow-detail', [FinanceApprovalController::class, 'workflowDetail']);
    Route::post('finance-hr-approval-workflow-act', [FinanceApprovalController::class, 'actWorkflow']);
    













    Route::get('/home-finance', function () {
        return view('home-finance');
    });

    Route::get('/finance-invoice-approval', function () {
        return view('finance.approval-invoice');
    });

    Route::post('/finance-approval-invoice-update', function () {
        return view('finance.approval-invoice-update');
    });

    Route::post('/finance-approval-invoice-submit', function () {
        return view('finance.approval-invoice-submit');
    });

    Route::get('/finance-approval-invoice-detail', function () {
        return view('finance.approval-invoice-detail');
    });

    /** finance */
    $finance = "finance";

    Route::get("$finance-pickup-cost", [CostManagementController::class, 'pickupCostIndex']);

    Route::post("$finance-cost-approve-route", [CostManagementController::class, "approvePickupCost"]);

    /** */

    /** panduan operasional route */
    $panduanOpsPref = "panduan";

    Route::get("$panduanOpsPref-index", [OperationalController::class, 'index']);

    Route::get("$panduanOpsPref-create", [OperationalController::class, 'create']);

    Route::get("$panduanOpsPref-detail", [OperationalController::class, 'detail']);

    Route::post("$panduanOpsPref-submit", [OperationalController::class, 'submit']);

    Route::post("$panduanOpsPref-delete", [OperationalController::class, 'delete']);

    Route::get("$panduanOpsPref-load-data", [OperationalController::class, 'loadXML']);

    /** Capital expenditure  */
    $capexPrefs = "partner-capex";

    Route::get("$capexPrefs-index", [ExpenditureController::class, 'indexCapitalExpenditure']);

    Route::get("$capexPrefs-create", [ExpenditureController::class, 'createCapitalExpenditure']);

    Route::get("$capexPrefs-detail", [ExpenditureController::class, 'getCapexDetail']);

    Route::post("$capexPrefs-submit", [ExpenditureController::class, 'submitCapitalExpenditure']);

    Route::post("$capexPrefs-submit-edit", [ExpenditureController::class, 'editCapitalExpenditure']);

    Route::post("$capexPrefs-delete", [ExpenditureController::class, 'deleteCapitalExpenditure']);

    Route::get("$capexPrefs-data", [ExpenditureController::class, 'getCapitalExpenditureData']);

Route::post('finance-hr-approval-maintenance-confirm', [FinanceApprovalController::class, 'confirmMaintenanceVoucher']);
    Route::post('finance-hr-approval-tax-pay', [FinanceApprovalController::class, 'payVehicleTaxVoucher']);
