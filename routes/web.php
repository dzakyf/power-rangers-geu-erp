<?php

use App\Http\Controllers\ExpenditureController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Partner\ListsController as PartnerListsController;

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\BroadcastController;
use App\Http\Controllers\ComplianceController;
use App\Http\Controllers\HelperController;
use App\Http\Controllers\KPIController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\OperationalController;
use App\Http\Controllers\CNS\VesselScheduleController;
use App\Http\Controllers\CNS\ShipmentInstructionController;
use App\Http\Controllers\CNS\FinalShipmentInstructionController;
use App\Http\Controllers\CNS\MasterCompanyController;
use App\Http\Controllers\CNS\MasterSupplierController;
use App\Http\Controllers\CNS\MasterConsigneeController;
use App\Http\Controllers\CNS\StuffingController as CNSStuffingController;
use App\Http\Controllers\CNS\StuffingMonitoringController as CNSStuffingMonitoringController;
use App\Http\Controllers\CNS\PackingListController as CNSPackingListController;
use App\Http\Controllers\CNS\CommercialInvoiceController as CNSCommercialInvoiceController;
use App\Http\Controllers\CNS\WeightBridgeTicketController as CNSWeightBridgeTicketController;
use App\Http\Controllers\CNS\CertificateOfOriginController as CNSCertificateOfOriginController;
use App\Http\Controllers\CNS\DeliveryNoteController as CNSDeliveryNoteController;
use App\Http\Controllers\CNS\SustainabilityDeclarationController as CNSSustainabilityDeclarationController;
use App\Http\Controllers\CNS\SalesOrderController as CNSSalesOrderController;
use App\Http\Controllers\CNS\PurchaseRequestController as CNSPurchaseRequestController;
use App\Http\Controllers\CNS\PurchaseOrderController as CNSPurchaseOrderController;
use App\Http\Controllers\CNS\QualityController;
use App\Http\Controllers\CNS\AnnexVIIController as CNSAnnexVIIController;
use App\Http\Controllers\HCGA\MasterApprovalController as HCGAMasterApprovalController;
use App\Http\Controllers\Finance\CostManagementController;
use App\Http\Controllers\Partner\SalesController as PartnerSalesController;
use App\Http\Controllers\Partner\UCOController as UCOLogisticController;
use App\Http\Controllers\Partner\TrainingController as PartnerTrainingController;
use App\Http\Controllers\Partner\ChatListController as PartnerChatListController;
use App\Http\Controllers\Partner\MasterSOPController as PartnerMasterSOPController;
use App\Http\Controllers\Partner\MasterMaterialController as PartnerMasterMaterialController;
use App\Http\Controllers\Partner\MasterSatisficationController as PartnerMasterSatisficationController;
use App\Http\Controllers\Partner\MasterAuditController as PartnerMasterAuditController;
use App\Http\Controllers\Partner\MasterTrainingController as PartnerMasterTrainingController;
use App\Http\Controllers\Partner\TicketController as PartnerTicketController;
use App\Http\Controllers\Partner\AnnouncementController as PartnerAnnouncementController;
use App\Http\Controllers\Partner\BusinessFacilityController as PartnerBusinessFacilityController;
use App\Http\Controllers\Partner\FacilityController as PartnerFacilityController;
use App\Http\Controllers\Partner\AssetManagementController as PartnerAssetManagementController;
use App\Http\Controllers\Partner\LeadsController;
use App\Http\Controllers\Partner\MasterDataController;
use App\Http\Controllers\Partner\OrderEquipmentController as PartnerOrderEquipmentController;
use App\Http\Controllers\Partner\WarehouseController as PartnerWarehouseController;
use App\Http\Controllers\Partner\WarehouseOpenController as PartnerWarehouseOpenController;
use App\Http\Controllers\Partner\WarehouseMonitoringController as PartnerWarehouseMonitoringController;
use App\Http\Controllers\Partner\MasterStockPriceController as PartnerMasterStockPriceController;
use App\Http\Controllers\Partner\WarehouseStockRequestController as PartnerWarehouseStockRequestController;
use App\Http\Controllers\Partner\WarehouseJournalController as PartnerWarehouseJournalController;
use App\Http\Controllers\Partner\WarehouseStockOpnameController as PartnerWarehouseStockOpnameController;
use App\Http\Controllers\Partner\WarehouseJournalSummaryController as PartnerWarehouseJournalSummaryController;
use App\Http\Controllers\Partner\WarehouseAuditController as PartnerWarehouseAuditController;
use App\Http\Controllers\Partner\ProfitSharingController as PartnerProfitSharingController;
use App\Http\Controllers\Partner\ValuationController as PartnerValuationController;
use App\Http\Controllers\Partner\WarehouseCorrectiveController as PartnerWarehouseCorrectiveController;
use App\Http\Controllers\PerformanceController;
use App\Http\Controllers\ProjectionProfitController;
use App\Http\Controllers\RenewalController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\UploaderController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('partner-leads', function () {
    return view('partner.leads');
});

Route::get('partner-registration', function () {
    return view('partner.registration-public');
});

Route::get('partner-prints-invoice', [App\Http\Controllers\Finance\PartnerController::class, 'printInvoice']);
Route::post('province', [App\Http\Controllers\HelperController::class, 'provinceSearch']);
Route::post('city/{province}', [App\Http\Controllers\HelperController::class, 'citySearch']);
Route::post('district', [HelperController::class, 'districtSearch']);
Route::get('subdistrict', [HelperController::class, 'subdistrictSearch']);
// Route::get('postal-code', [HelperController::class, 'postalCodeSearch']);


Auth::routes();

Route::group(['middleware' => 'usersession'], function () {
    Route::get('/', function () {
        // Uses User Session Middleware
    });
});
Route::group(['middleware' => 'log.access'], function () {
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

    Route::get('/privilege-information', function () {
        return view('privilege-information');
    });
    Route::get('/change-password', function () {
        return view('change-password');
    });
    Route::post('/change-password-submit', function () {
        return view('change-password-submit');
    });
    //Route::get('/', 'AdminController@index');
    Route::get('/', [AdminController::class, 'index']);
    Route::get('/dashboard1', [AdminController::class, 'index']);
    Route::post('/logpost', function () {
        return view('/user/logproc');
    });
    Route::get('/home-dashboard', function () {
        return view('home-dashboard');
    });
    Route::get('/home-sales', function () {
        return view('home-sales');
    });
    Route::get('/home-credential', function () {
        return view('home-credential');
    });
    Route::get('/home-mymenu', function () {
        return view('home-mymenu');
    });

    Route::get('/home-finance', function () {
        return view('home-finance');
    });
    Route::get('/home-accounting', function () {
        return view('home-accounting');
    });
    Route::get('/home-procurement', function () {
        return view('home-procurement');
    });
    Route::get('/home-asset', function () {
        return view('home-asset');
    });
    Route::get('/home-hrd', function () {
        return view('home-hrd');
    });
    Route::get('/home-project', function () {
        return view('home-project');
    });
    Route::get('/home-cns', function () {
        return view('home-cns');
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

    Route::get('/asset-lists-sub-category', function () {
        return view('assets.lists-sub-category');
    });
    Route::get('/asset-lists-sub-sub-category', function () {
        return view('assets.lists-sub-sub-category');
    });

    Route::get('/asset-lists-new', function () {
        return view('assets.lists-new');
    });

    Route::post('/asset-lists-new-submit', function () {
        return view('assets.lists-new-submit');
    });

    Route::get('/asset-lists', function () {
        return view('assets.lists');
    });
    Route::get('/xml-asset-lists', function () {
        return view('assets.xml.lists');
    });

    Route::get('/credential-role', function () {
        return view('credential.role');
    });
    Route::get('/xml-credential-role', function () {
        return view('credential.xml.role');
    });
    Route::get('/credential-role-detail', function () {
        return view('credential.role-detail');
    });
    Route::post('/credential-role-detail-submit', function () {
        return view('credential.role-detail-submit');
    });
    Route::get('credential-structure', [App\Http\Controllers\Credential\StructureController::class, 'index']);
    Route::get('xml-credential-structure', [App\Http\Controllers\Credential\StructureController::class, 'indexXml']);
    Route::get('credential-structure-detail', [App\Http\Controllers\Credential\StructureController::class, 'detail']);
    Route::post('credential-structure-detail-submit', [App\Http\Controllers\Credential\StructureController::class, 'store']);

    Route::get('/accounting-project-posting', function () {
        return view('accounting.project-posting');
    });

    Route::post('/accounting-project-posting-update', function () {
        return view('accounting.project-posting-update');
    });
    Route::get('/accounting-project-posting-detail', function () {
        return view('accounting.project-posting-detail');
    });
    Route::get('/xml-accounting-project-posting', function () {
        return view('accounting.xml.project-posting');
    });
    Route::post('/accounting-project-posting-detail-submit', function () {
        return view('accounting.project-posting-detail-submit');
    });
    Route::post('/accounting-project-posting-detail-ignore', function () {
        return view('accounting.project-posting-detail-ignore');
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
    Route::post('/master-location-submit', function () {
        return view('accounting.master-location-submit');
    });
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
    Route::get('/finance-invoice-approval', function () {
        return view('finance.approval-invoice');
    });
    Route::get('/xml-finance-approval-invoice', function () {
        return view('finance.xml.approval-invoice');
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

    Route::post('/sales-opportunity-registration-submit', function () {
        return view('sales.opportunity-registration-submit');
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
    Route::get('/search-po', function () {
        return view('assets.search-po');
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
    Route::get('/inventory-stock-count-new', function () {
        return view('assets.inventory-stock-count-new');
    });
    Route::get('/inventory-stock-count', function () {
        return view('assets.inventory-stock-count');
    });
    Route::get('/xml-inventory-stock-count', function () {
        return view('assets.xml.inventory-stock-count');
    });
    Route::get('/inventory-stock-count-detail', function () {
        return view('assets.inventory-stock-count-detail');
    });
    Route::get('/xml-inventory-stock-count-detail', function () {
        return view('assets.xml.inventory-stock-count-detail');
    });
    Route::post('/inventory-stock-count-detail-update', function () {
        return view('assets.inventory-stock-count-detail-update');
    });
    Route::post('/inventory-stock-count-detail-submit', function () {
        return view('assets.inventory-stock-count-detail-submit');
    });
    Route::get('/xml-inventory-stock-count-final', function () {
        return view('assets.xml.inventory-stock-count-final');
    });
    Route::get('/search-produk', function () {
        return view('assets.search-produk');
    });
    Route::get('/inventory-adjustment', function () {
        return view('assets.inventory-adjustment');
    });
    Route::post('/inventory-adjustment-submit', function () {
        return view('assets.inventory-adjustment-submit');
    });
    Route::get('/xml-inventory-adjustment', function () {
        return view('assets.xml.inventory-adjustment');
    });
    Route::get('/inventory-incoming', function () {
        return view('assets.inventory-incoming');
    });
    Route::get('/xml-inventory-incoming', function () {
        return view('assets.xml.inventory-incoming');
    });
    Route::get('/inventory-incoming-detail', function () {
        return view('assets.inventory-incoming-detail');
    });
    Route::get('/inventory-incoming-detail2', function () {
        return view('assets.inventory-incoming-detail2');
    });
    Route::get('/xml-inventory-incoming-detail', function () {
        return view('assets.xml.inventory-incoming-detail');
    });
    Route::post('/inventory-incoming-detail-submit', function () {
        return view('assets.inventory-incoming-detail-submit');
    });
    Route::post('/inventory-incoming-detail-submit2', function () {
        return view('assets.inventory-incoming-detail-submit2');
    });
    Route::get('/inventory-incoming-new', function () {
        return view('assets.inventory-incoming-new');
    });;

    Route::post('/inventory-incoming-detail-update2', function () {
        return view('assets.inventory-incoming-detail-update2');
    });
    Route::post('/inventory-incoming-new-submit', function () {
        return view('assets.inventory-incoming-new-submit');
    });
    Route::post('/inventory-incoming-new-update', function () {
        return view('assets.inventory-incoming-new-update');
    });
    Route::get('/xml-inventory-incoming-new', function () {
        return view('assets.xml.inventory-incoming-new');
    });
    Route::get('/inventory-request', function () {
        return view('assets.inventory-request');
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

    Route::get('/inventory-request-management', function () {
        return view('assets.inventory-request-management');
    });
    Route::get('/xml-inventory-request-management', function () {
        return view('assets.xml.inventory-request-management');
    });
    Route::get('/inventory-request-management-detail', function () {
        return view('assets.inventory-request-management-detail');
    });
    Route::get('/inventory-request-management-detail2', function () {
        return view('assets.inventory-request-management-detail2');
    });
    Route::get('/xml-inventory-request-management-detail', function () {
        return view('assets.xml.inventory-request-management-detail');
    });
    Route::get('/xml-inventory-request-management-detail2', function () {
        return view('assets.xml.inventory-request-management-detail2');
    });
    Route::post('/inventory-request-management-detail2-update', function () {
        return view('assets.inventory-request-management-detail2-update');
    });
    Route::post('/inventory-request-management-detail2-submit', function () {
        return view('assets.inventory-request-management-detail2-submit');
    });
    Route::post('/inventory-request-management-submit', function () {
        return view('assets.inventory-request-management-submit');
    });
    Route::post('/inventory-request-management-detail-update', function () {
        return view('assets.inventory-request-management-detail-update');
    });
    Route::post('/inventory-request-management-detail-submit', function () {
        return view('assets.inventory-request-management-detail-submit');
    });
    Route::post('/inventory-request-update', function () {
        return view('assets.inventory-request-update');
    });
    Route::get('/inventory-request-product-list', function () {
        return view('assets.inventory-request-product-list');
    });
    Route::post('/inventory-request-submit', function () {
        return view('assets.inventory-request-submit');
    });
    Route::get('/xml-inventory-request-product-lists', function () {
        return view('assets.xml.inventory-request-product-list');
    });
    Route::get('/inventory-lists', function () {
        return view('assets.inventory-lists');
    });
    Route::get('/xml-inventory-lists', function () {
        return view('assets.xml.inventory-lists');
    });
    Route::get('/inventory-lists-detail', function () {
        return view('assets.inventory-lists-detail');
    });
    Route::get('/xml-inventory-lists-detail', function () {
        return view('assets.xml.inventory-lists-detail');
    });
    Route::get('/asset-lists', function () {
        return view('assets.lists');
    });
    Route::get('/asset-management-incoming', function () {
        return view('assets.management-incoming');
    });
    Route::get('/asset-lists-detail', function () {
        return view('assets.lists-detail');
    });
    Route::post('/asset-lists-detail-submit', function () {
        return view('assets.lists-detail-submit');
    });
    Route::get('/xml-asset-lists', function () {
        return view('assets.xml.asset-lists');
    });
    Route::get('/asset-master-bin', function () {
        return view('assets.master-bin');
    });
    Route::get('/xml-asset-master-bin', function () {
        return view('assets.xml.master-bin');
    });
    Route::post('/asset-master-bin-update', function () {
        return view('assets.master-bin-update');
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
    Route::get('/hr-report-tax', function () {
        return view('humanresources.report.tax');
    });
    Route::get('/xml-hr-report-tax', function () {
        return view('humanresources.report.xml.tax');
    });
    Route::get('/hr-report-bpjs', function () {
        return view('humanresources.report.bpjs');
    });
    Route::get('/xml-hr-report-bpjs', function () {
        return view('humanresources.report.xml.bpjs');
    });
    Route::get('/hr-report-outstanding-recruitment', function () {
        return view('humanresources.report.outstanding-recruitment');
    });
    Route::get('/xml-hr-report-outstanding-recruitment', function () {
        return view('humanresources.report.xml.outstanding-recruitment');
    });
    Route::get('/recruitment-requisition', function () {
        return view('humanresources.recruitment.requisition');
    });
    Route::post('/requisition-submit', function () {
        return view('humanresources.recruitment.requisition-submit');
    });
    Route::get('/xml-recruitment-requisition', function () {
        return view('humanresources.recruitment.xml.requisition');
    });
    Route::get('/xml-requisition-lists', function () {
        return view('humanresources.recruitment.xml.requisition');
    });
    Route::get('/requisition-new', function () {
        return view('humanresources.recruitment.requisition-new');
    });
    Route::post('/recruitment-requisition-new-submit', function () {
        return view('humanresources.recruitment.requisition-new-submit');
    });
    Route::get('/requisition-new-po', function () {
        return view('humanresources.recruitment.requisition-new-po');
    });
    Route::post('/requisition-new-po-submit', function () {
        return view('humanresources.recruitment.requisition-new-po-submit');
    });
    Route::get('/recruitment-requisition-approval', function () {
        return view('humanresources.recruitment.requisition-approval');
    });
    Route::get('/xml-recruitment-requisition-approval', function () {
        return view('humanresources.recruitment.xml.requisition-approval');
    });
    Route::get('/xml-sales-po-lists-detail', function () {
        return view('humanresources.recruitment.xml.sales-po-lists-detail');
    });
    Route::get('/hrd-master-template', function () {
        return view('humanresources.recruitment.hrd-master-template');
    });
    Route::post('/hrd-master-template-submit', function () {
        return view('humanresources.recruitment.hrd-master-template-submit');
    });
    Route::get('/hrd-master-template-detail', function () {
        return view('humanresources.recruitment.hrd-master-template-detail');
    });
    Route::post('/hrd-master-template-new-submit', function () {
        return view('humanresources.recruitment.hrd-master-template-new-submit');
    });
    Route::post('/hrd-master-template-detail-submit', function () {
        return view('humanresources.recruitment.hrd-master-template-detail-submit');
    });
    Route::get('/xml-recruitment-template', function () {
        return view('humanresources.recruitment.xml.recruitment-template');
    });
    Route::get('/hrd-master-template-new', function () {
        return view('humanresources.recruitment.hrd-master-template-new');
    });
    Route::get('/xml-hrd-master-template', function () {
        return view('humanresources.recruitment.xml.hrd-master-template');
    });
    Route::post('/sales-po-lists-update', function () {
        return view('humanresources.recruitment.sales-po-lists-update');
    });
    Route::get('/recruitment-new', function () {
        return view('humanresources.recruitment.recruitment-new');
    });
    Route::post('/recruitment-new-submit', function () {
        return view('humanresources.recruitment.recruitment-new-submit');
    });
    Route::get('/recruitment-test-result', function () {
        return view('humanresources.recruitment.recruitment-test-result');
    });
    Route::get('/xml-recruitment-test-result', function () {
        return view('humanresources.recruitment.xml.recruitment-test-result');
    });
    Route::get('/recruitment-test-result-detail', function () {
        return view('humanresources.recruitment.recruitment-test-result-detail');
    });
    Route::post('/recruitment-test-result-detail-submit', function () {
        return view('humanresources.recruitment.recruitment-test-result-detail-submit');
    });
    Route::get('/recruitment-data', function () {
        return view('humanresources.recruitment.recruitment-data');
    });
    Route::get('/xml-recruitment-data', function () {
        return view('humanresources.recruitment.xml.recruitment-data');
    });
    Route::get('/recruitment-pending', function () {
        return view('humanresources.recruitment.recruitment-pending');
    });
    Route::get('/recruitment-pending-detail', function () {
        return view('humanresources.recruitment.recruitment-pending-detail');
    });
    Route::get('/recruitment-outstanding', function () {
        return view('humanresources.recruitment.recruitment-outstanding');
    });
    Route::get('/xml-recruitment-outstanding', function () {
        return view('humanresources.recruitment.xml.recruitment-outstanding');
    });
    Route::get('/recruitment-pending-detail2', function () {
        return view('humanresources.recruitment.recruitment-pending-detail2');
    });
    Route::post('/recruitment-pending-detail2-submit', function () {
        return view('humanresources.recruitment.recruitment-pending-detail2-submit');
    });
    Route::get('/xml-recruitment-pending', function () {
        return view('humanresources.recruitment.xml.recruitment-pending');
    });
    Route::get('/recruitment-candidate-analisys', function () {
        return view('humanresources.recruitment.recruitment-candidate-analysis');
    });
    Route::get('/xml-recruitment-candidate-analysis', function () {
        return view('humanresources.recruitment.xml.recruitment-candidate-analysis');
    });
    Route::get('/recruitment-candidate-analysis-detail', function () {
        return view('humanresources.recruitment.recruitment-candidate-analysis-detail');
    });
    Route::post('/recruitment-candidate-analysis-detail-submit', function () {
        return view('humanresources.recruitment.recruitment-candidate-analysis-detail-submit');
    });
    Route::post('/recruitment-data-submit', function () {
        return view('humanresources.recruitment.recruitment-data-submit');
    });
    Route::get('/recruitment-lists-detail', function () {
        return view('humanresources.recruitment.recruitment-lists-detail');
    });
    Route::get('/recruitment-lists-detail-data', function () {
        return view('humanresources.recruitment.recruitment-lists-detail-data');
    });
    Route::get('/recruitment-document', function () {
        return view('humanresources.recruitment.recruitment-document');
    });
    Route::get('/xml-recruitment-document', function () {
        return view('humanresources.recruitment.xml.recruitment-document');
    });
    Route::get('/recruitment-document-detail', function () {
        return view('humanresources.recruitment.recruitment-document-detail');
    });
    Route::get('/recruitment-notification', function () {
        return view('humanresources.recruitment.recruitment-notification');
    });
    Route::post('/recruitment-notification-submit', function () {
        return view('humanresources.recruitment.recruitment-notification-submit');
    });

    Route::get('/xml-recruitment-notification', function () {
        return view('humanresources.recruitment.xml.recruitment-notification');
    });
    Route::get('/recruitment-interview', function () {
        return view('humanresources.recruitment.recruitment-interview');
    });
    Route::get('/xml-recruitment-interview', function () {
        return view('humanresources.recruitment.xml.recruitment-interview');
    });
    Route::get('/recruitment-interview-detail', function () {
        return view('humanresources.recruitment.recruitment-interview-detail');
    });
    Route::post('/recruitment-interview-detail-submit', function () {
        return view('humanresources.recruitment.recruitment-interview-detail-submit');
    });
    Route::get('/sales-proforma-ba', function () {
        return view('sales.proforma-ba');
    });
    Route::get('/xml-sales-proforma-ba', function () {
        return view('sales.xml.proforma-ba');
    });
    Route::get('/sales-proforma-ba-detail', function () {
        return view('sales.proforma-ba-detail');
    });
    Route::get('/sales-proforma-ba-detail2', function () {
        return view('sales.proforma-ba-detail2');
    });
    Route::get('/xml-sales-proforma-ba-detail', function () {
        return view('sales.xml.proforma-ba-detail');
    });
    Route::post('/sales-proforma-ba-detail-submit', function () {
        return view('sales.proforma-ba-detail-submit');
    });
    Route::post('/sales-proforma-ba-detail2-submit', function () {
        return view('sales.proforma-ba-detail2-submit');
    });
    Route::get('/badownload', function () {
        return view('sales.badownload');
    });
    Route::get('/baladownload', function () {
        return view('sales.baladownload');
    });
    Route::get('/bala2download', function () {
        return view('sales.bala2download');
    });



    // project
    Route::get('/opportunity-timeline-file', function () {
        return view('project.opportunity-timeline-file');
    });
    Route::get('/project-stock', function () {
        return view('project.project-stock');
    });
    Route::get('/project-stock-detail', function () {
        return view('project.project-stock-detail');
    });
    Route::get('/project-stock-add', function () {
        return view('project.project-stock-add');
    });

    Route::post('/project-stock-add-submit', function () {
        return view('project.project-stock-submit');
    });
    Route::get('/po-outstanding', function () {
        return view('procurement.po-outstanding');
    });
    Route::get('/po-outstanding-detail', function () {
        return view('procurement.po-outstanding-detail');
    });
    Route::get('/xml-po-outstanding', function () {
        return view('procurement.xml.po-outstanding');
    });
    Route::get('/po-vendor-summary', function () {
        return view('procurement.po-vendor-summary');
    });
    Route::get('/xml-po-vendor-summary', function () {
        return view('procurement.xml.po-vendor-summary');
    });
    Route::get('/po-vendor-summary-detail', function () {
        return view('procurement.po-vendor-summary-detail');
    });
    Route::get('/xml-po-vendor-summary-detail', function () {
        return view('procurement.xml.po-vendor-summary-detail');
    });
    Route::get('/po-payment-monitoring', function () {
        return view('procurement.po-payment-monitoring');
    });
    Route::get('/po-payment-monitoring-detail', function () {
        return view('procurement.po-payment-monitoring-detail');
    });
    Route::get('/xml-po-payment-monitoring', function () {
        return view('procurement.xml.po-payment-monitoring');
    });
    Route::get('/project-purchase', function () {
        return view('project.project-purchase');
    });
    Route::get('/project-purchase-add', function () {
        return view('project.project-purchase-add');
    });

    Route::get('/project-purchase-detail', function () {
        return view('project.project-purchase-detail');
    });
    Route::post('/project-purchase-submit', function () {
        return view('project.project-purchase-submit');
    });
    Route::get('/project-hrd-approval', function () {
        return view('project.project-hrd-approval');
    });
    Route::get('/project-hrd-approval-recounting', function () {
        return view('project.project-hrd-approval-recounting');
    });
    Route::get('/xml-project-hrd-approval', function () {
        return view('project.xml.project-hrd-approval');
    });
    Route::get('/xml-project-hrd-approval-detail', function () {
        return view('project.xml.project-hrd-approval-detail');
    });
    Route::get('/project-hrd-approval-detail', function () {
        return view('project.project-hrd-approval-detail');
    });
    Route::post('/project-hrd-approval-detail-submit', function () {
        return view('project.project-hrd-approval-detail-submit');
    });
    Route::post('/project-hrd-approval-detail-update', function () {
        return view('project.project-hrd-approval-detail-update');
    });
    Route::get('/xml-project-reimbursement-history', function () {
        return view('project.xml.project-reimbursement-history');
    });

    Route::post('/approval-reimbursement-update', function () {
        return view('project.approval-reimbursement-update');
    });
    Route::get('/approval-reimbursement', function () {
        return view('project.approval-reimbursement');
    });
    Route::get('/approval-reimbursement-detail', function () {
        return view('project.approval-reimbursement-detail');
    });

    Route::get('/xml-approval-reimbursement', function () {
        return view('project.xml.approval-reimbursement');
    });
    Route::get('/xml-approval-reimbursement-detail', function () {
        return view('project.xml.approval-reimbursement-detail');
    });


    Route::get('/project-reimbursement-history', function () {
        return view('project.project-reimbursement-history');
    });
    Route::get('/project-reimbursement', function () {
        return view('project.project-reimbursement');
    });
    Route::get('/project-reimbursement-detail', function () {
        return view('project.project-reimbursement-detail');
    });
    Route::get('/xml-project-reimbursement-detail', function () {
        return view('project.xml.project-reimbursement-detail');
    });
    Route::get('/project-reimbursement-detail-advance', function () {
        return view('project.project-reimbursement-detail-advance');
    });
    Route::get('/xml-project-reimbursement-detail-advance', function () {
        return view('project.xml.project-reimbursement-detail-advance');
    });
    Route::post('/project-reimbursement-detail-advance-update', function () {
        return view('project.project-reimbursement-detail-advance-update');
    });
    Route::get('/project-reimbursement-detail-reimbursement', function () {
        return view('project.project-reimbursement-detail-reimbursement');
    });
    Route::get('/xml-project-reimbursement-detail-reimbursement', function () {
        return view('project.xml.project-reimbursement-detail-reimbursement');
    });
    Route::post('/project-reimbursement-detail-reimbursement-update', function () {
        return view('project.project-reimbursement-detail-reimbursement-update');
    });
    Route::get('/xml-project-reimbursement-detail-reimbursement', function () {
        return view('project.xml.project-reimbursement-detail-reimbursement');
    });
    Route::post('/project-reimbursement-detail-update', function () {
        return view('project.project-reimbursement-detail-update');
    });
    Route::get('/project-reimbursement-out-add', function () {
        return view('project.project-reimbursement-out-add');
    });
    Route::get('/xml-project-reimbursement', function () {
        return view('project.xml.project-reimbursement');
    });
    Route::get('/project-organization', function () {
        return view('project.organization');
    });
    Route::get('/project-organization-detail', function () {
        return view('project.organization-detail');
    });

    Route::get('/xml-project-organization', function () {
        return view('project.xml.organization');
    });
    Route::get('/project-lists', function () {
        return view('project.project-lists');
    });
    Route::get('/project-lists-detail', function () {
        return view('project.project-detail');
    });
    Route::post('/project-detail-finish', function () {
        return view('project.project-detail-finish');
    });
    Route::post('/project-detail-final', function () {
        return view('project.project-detail-final');
    });
    Route::post('/project-detail-submit', function () {
        return view('project.project-detail-submit');
    });
    Route::post('/project-lists-submit', function () {
        return view('project.project-lists-submit');
    });
    Route::get('/xml-project-lists', function () {
        return view('project.xml.project-lists');
    });
    Route::get('/project-search-customer', function () {
        return view('project.search-customer');
    });
    Route::get('/search-customer', function () {
        return view('sales.search-customer');
    });
    Route::get('/project-search-vendor', function () {
        return view('project.search-vendor');
    });
    Route::get('/project-search-opportunity', function () {
        return view('project.search-opportunity');
    });
    Route::get('/project-new', function () {
        return view('project.new');
    });
    Route::post('/project-new-submit', function () {
        return view('project.new-submit');
    });

    Route::get('/project-new-opportunity', function () {
        return view('project.new-opportunity');
    });
    Route::post('/project-new-opportunity-submit', function () {
        return view('project.new-opportunity-submit');
    });

    Route::get('/project-activity', function () {
        return view('project.project-activity');
    });
    Route::get('/xml-project-activity', function () {
        return view('project.xml.project-activity');
    });
    Route::get('/project-activity2', function () {
        return view('project.project-activity2');
    });
    Route::get('/xml-project-activity2', function () {
        return view('project.xml.project-activity2');
    });
    Route::post('/project-activity2-update', function () {
        return view('project.project-activity2-update');
    });
    Route::get('/project-activity-add', function () {
        return view('project.project-activity-add');
    });
    Route::get('/project-activity-detail', function () {
        return view('project.project-activity-detail');
    });

    Route::get('/project-time', function () {
        return view('project.project-time');
    });

    Route::get('/project-times-detail', function () {
        return view('project.project-times-detail');
    });
    Route::get('/xml-project-times-costs', function () {
        return view('project.xml.project-times-costs');
    });
    Route::get('/xml-project-times-documents', function () {
        return view('project.xml.project-times-documents');
    });
    Route::get('/xml-project-times', function () {
        return view('project.xml.project-time');
    });
    Route::get('/project-time-detail', function () {
        return view('project.project-time-detail');
    });
    Route::post('/project-activity-detail-submit', function () {
        return view('project.project-activity-detail-submit');
    });
    Route::post('/project-activity-add-submit', function () {
        return view('project.project-activity-add-submit');
    });
    Route::get('/recruitment-outstanding-requisition', function () {
        return view('humanresources.recruitment.outstanding-requisition');
    });
    Route::get('/xml-outstanding-requisition', function () {
        return view('humanresources.recruitment.xml.outstanding-requisition');
    });

    Route::post('/outstanding-requisition-update', function () {
        return view('humanresources.recruitment.outstanding-requisition-update');
    });
    Route::get('/requisition-new-detail', function () {
        return view('humanresources.recruitment.requisition-new-project-detail');
    });
    Route::get('/xml-requisition-new-detail', function () {
        return view('humanresources.recruitment.xml.requisition-new-detail');
    });
    Route::get('/project-hrd', function () {
        return view('project.project-hrd');
    });
    Route::get('/project-hrd-detail', function () {
        return view('project.project-hrd-detail');
    });
    Route::get('/xml-project-hrd-detail', function () {
        return view('project.xml.project-hrd-detail');
    });
    Route::post('/project-hrd-detail-update', function () {
        return view('project.project-hrd-detail-update');
    });
    Route::get('/project-hrd-detail-detail', function () {
        return view('project.project-hrd-detail-detail');
    });
    Route::get('/xml-project-hrd-detail-detail', function () {
        return view('project.xml.project-hrd-detail-detail');
    });
    Route::get('/requisition-new-project', function () {
        return view('humanresources.recruitment.requisition-new-project');
    });
    Route::post('/requisition-new-project-submit', function () {
        return view('humanresources.recruitment.requisition-new-project-submit');
    });
    Route::get('/os-lists', function () {
        return view('humanresources.employment.os-lists');
    });
    Route::get('/xml-os-lists', function () {
        return view('humanresources.employment.xml.os-lists');
    });
    Route::get('/employee-lists', function () {
        return view('humanresources.employment.employee-lists');
    });
    Route::get('/xml-employee-lists', function () {
        return view('humanresources.employment.xml.employee-lists');
    });
    Route::get('/employee-lists-detail', function () {
        return view('humanresources.employment.employee-lists-detail');
    });
    Route::get('/employee-lists-detail-data', function () {
        return view('humanresources.employment.employee-lists-detail-data');
    });
    Route::post('/employee-lists-detail-data-submit', function () {
        return view('humanresources.employment.employee-lists-detail-data-submit');
    });
    Route::get('/employee-lists-detail-other', function () {
        return view('humanresources.employment.employee-lists-detail-other');
    });

    Route::get('/employee-lists-detail-insurance', function () {
        return view('humanresources.employment.employee-lists-detail-insurance');
    });
    Route::get('/xml-employee-lists-detail-document', function () {
        return view('humanresources.employment.xml.employee-lists-detail-document');
    });
    Route::get('/xml-employee-lists-detail-history', function () {
        return view('humanresources.employment.xml.employee-lists-detail-history');
    });
    Route::get('/recruitment-lists-detail-other', function () {
        return view('humanresources.recruitment.recruitment-lists-detail-other');
    });
    Route::get('/recruitment-lists-detail-family', function () {
        return view('humanresources.recruitment.recruitment-lists-detail-family');
    });
    Route::get('/recruitment-lists-detail-education', function () {
        return view('humanresources.recruitment.recruitment-lists-detail-education');
    });
    Route::get('/recruitment-lists-detail-skill', function () {
        return view('humanresources.recruitment.recruitment-lists-detail-skill');
    });
    Route::get('/xml-recruitment-lists-detail-other-skill', function () {
        return view('humanresources.recruitment.xml.recruitment-lists-detail-other-skill');
    });
    Route::get('/recruitment-lists-detail-document', function () {
        return view('humanresources.recruitment.recruitment-lists-detail-document');
    });
    Route::get('/xml-recruitment-lists-detail-document', function () {
        return view('humanresources.recruitment.xml.recruitment-lists-detail-document');
    });
    Route::get('/recruitment-lists-detail-insurance', function () {
        return view('humanresources.recruitment.recruitment-lists-detail-insurance');
    });
    Route::get('/xml-recruitment-lists-detail-insurance', function () {
        return view('humanresources.recruitment.xml.recruitment-lists-detail-insurance');
    });
    Route::post('/recruitment-lists-detail-insurance-update', function () {
        return view('humanresources.recruitment.recruitment-lists-detail-insurance-update');
    });
    Route::get('/xml-recruitment-lists-detail-organization', function () {
        return view('humanresources.recruitment.xml.recruitment-lists-detail-organization');
    });
    Route::post('/recruitment-lists-detail-organization-update', function () {
        return view('humanresources.recruitment.recruitment-lists-detail-organization-update');
    });
    Route::get('/xml-recruitment-lists-detail-family', function () {
        return view('humanresources.recruitment.xml.recruitment-lists-detail-family');
    });
    Route::get('/xml-recruitment-lists-detail-academic', function () {
        return view('humanresources.recruitment.xml.recruitment-lists-detail-academic');
    });
    Route::get('/xml-recruitment-lists-detail-contact-points', function () {
        return view('humanresources.recruitment.xml.recruitment-lists-detail-contact-points');
    });
    Route::post('/recruitment-lists-detail-contact-points-update', function () {
        return view('humanresources.recruitment.recruitment-lists-detail-contact-points-update');
    });
    Route::post('/recruitment-lists-detail-academic-update', function () {
        return view('humanresources.recruitment.recruitment-lists-detail-academic-update');
    });
    Route::get('/xml-recruitment-lists-detail-informal', function () {
        return view('humanresources.recruitment.xml.recruitment-lists-detail-informal');
    });
    Route::post('/recruitment-lists-detail-informal-update', function () {
        return view('humanresources.recruitment.recruitment-lists-detail-informal-update');
    });
    Route::get('/xml-recruitment-lists-detail-driving', function () {
        return view('humanresources.recruitment.xml.recruitment-lists-detail-driving');
    });
    Route::post('/recruitment-lists-detail-driving-update', function () {
        return view('humanresources.recruitment.recruitment-lists-detail-driving-update');
    });
    Route::get('/xml-recruitment-lists-detail-health', function () {
        return view('humanresources.recruitment.xml.recruitment-lists-detail-health');
    });
    Route::post('/recruitment-lists-detail-health-update', function () {
        return view('humanresources.recruitment.recruitment-lists-detail-health-update');
    });
    Route::get('/xml-recruitment-lists-detail-experience', function () {
        return view('humanresources.recruitment.xml.recruitment-lists-detail-experience');
    });
    Route::post('/recruitment-lists-detail-experience-update', function () {
        return view('humanresources.recruitment.recruitment-lists-detail-experience-update');
    });
    Route::get('/xml-recruitment-lists-detail-skill', function () {
        return view('humanresources.recruitment.xml.recruitment-lists-detail-skill');
    });
    Route::post('/recruitment-lists-detail-skill-update', function () {
        return view('humanresources.recruitment.recruitment-lists-detail-skill-update');
    });
    Route::get('/xml-recruitment-lists-detail-award', function () {
        return view('humanresources.recruitment.xml.recruitment-lists-detail-award');
    });
    Route::post('/recruitment-lists-detail-award-update', function () {
        return view('humanresources.recruitment.recruitment-lists-detail-award-update');
    });
    Route::get('/xml-recruitment-lists-detail-language', function () {
        return view('humanresources.recruitment.xml.recruitment-lists-detail-language');
    });
    Route::post('/recruitment-lists-detail-language-update', function () {
        return view('humanresources.recruitment.recruitment-lists-detail-language-update');
    });
    Route::get('/recruitment-lists-detail-recruitment', function () {
        return view('humanresources.recruitment.recruitment-lists-detail-recruitment');
    });

    Route::get('/xml-employee-lists-detail-insurance', function () {
        return view('humanresources.employment.xml.employee-lists-detail-insurance');
    });
    Route::post('/employee-lists-detail-insurance-update', function () {
        return view('humanresources.employment.employee-lists-detail-insurance-update');
    });
    Route::get('/xml-employee-lists-detail-organization', function () {
        return view('humanresources.employment.xml.employee-lists-detail-organization');
    });
    Route::post('/employee-lists-detail-organization-update', function () {
        return view('humanresources.employment.employee-lists-detail-organization-update');
    });
    Route::get('/xml-employee-lists-detail-family', function () {
        return view('humanresources.employment.xml.employee-lists-detail-family');
    });
    Route::get('/xml-employee-lists-detail-academic', function () {
        return view('humanresources.employment.xml.employee-lists-detail-academic');
    });
    Route::get('/xml-employee-lists-detail-contact-points', function () {
        return view('humanresources.employment.xml.employee-lists-detail-contact-points');
    });
    Route::post('/employee-lists-detail-contact-points-update', function () {
        return view('humanresources.employment.employee-lists-detail-contact-points-update');
    });
    Route::post('/employee-lists-detail-academic-update', function () {
        return view('humanresources.employment.employee-lists-detail-academic-update');
    });
    Route::get('/xml-employee-lists-detail-informal', function () {
        return view('humanresources.employment.xml.employee-lists-detail-informal');
    });
    Route::post('/employee-lists-detail-informal-update', function () {
        return view('humanresources.employment.employee-lists-detail-informal-update');
    });
    Route::get('/xml-employee-lists-detail-driving', function () {
        return view('humanresources.employment.xml.employee-lists-detail-driving');
    });
    Route::post('/employee-lists-detail-driving-update', function () {
        return view('humanresources.employment.employee-lists-detail-driving-update');
    });
    Route::get('/xml-employee-lists-detail-health', function () {
        return view('humanresources.employment.xml.employee-lists-detail-health');
    });
    Route::post('/employee-lists-detail-health-update', function () {
        return view('humanresources.employment.employee-lists-detail-health-update');
    });
    Route::get('/xml-employee-lists-detail-experience', function () {
        return view('humanresources.employment.xml.employee-lists-detail-experience');
    });
    Route::post('/employee-lists-detail-experience-update', function () {
        return view('humanresources.employment.employee-lists-detail-experience-update');
    });
    Route::get('/xml-employee-lists-detail-skill', function () {
        return view('humanresources.employment.xml.employee-lists-detail-skill');
    });
    Route::post('/employee-lists-detail-skill-update', function () {
        return view('humanresources.employment.employee-lists-detail-skill-update');
    });
    Route::get('/xml-employee-lists-detail-award', function () {
        return view('humanresources.employment.xml.employee-lists-detail-award');
    });
    Route::post('/employee-lists-detail-award-update', function () {
        return view('humanresources.employment.employee-lists-detail-award-update');
    });
    Route::get('/xml-employee-lists-detail-language', function () {
        return view('humanresources.employment.xml.employee-lists-detail-language');
    });
    Route::post('/employee-lists-detail-language-update', function () {
        return view('humanresources.employment.employee-lists-detail-language-update');
    });
    Route::get('/employee-lists-detail-recruitment', function () {
        return view('humanresources.employment.employee-lists-detail-recruitment');
    });

    Route::get('/employee-mutation', function () {
        return view('humanresources.employment.employee-mutation');
    });
    Route::get('/employee-mutation-detail', function () {
        return view('humanresources.employment.employee-mutation-detail');
    });
    Route::post('/employee-mutation-detail-submit', function () {
        return view('humanresources.employment.employee-mutation-detail-submit');
    });
    Route::get('/xml-employee-mutation', function () {
        return view('humanresources.employment.xml.employee-mutation');
    });
    Route::get('/employee-promotion', function () {
        return view('humanresources.employment.employee-promotion');
    });
    Route::get('/employee-promotion-detail', function () {
        return view('humanresources.employment.employee-promotion-detail');
    });
    Route::post('/employee-promotion-detail-submit', function () {
        return view('humanresources.employment.employee-promotion-detail-submit');
    });
    Route::get('/xml-employee-promotion', function () {
        return view('humanresources.employment.xml.employee-promotion');
    });
    Route::get('/os-conversion', function () {
        return view('humanresources.employment.os-conversion');
    });
    Route::get('/os-conversion-detail', function () {
        return view('humanresources.employment.os-conversion-detail');
    });
    Route::post('/os-conversion-detail-submit', function () {
        return view('humanresources.employment.os-conversion-detail-submit');
    });
    Route::get('/xml-os-conversion', function () {
        return view('humanresources.employment.xml.os-conversion');
    });

    Route::get('/employee-lists-detail-history', function () {
        return view('humanresources.employment.employee-lists-detail-history');
    });
    Route::get('/employee-lists-detail-document', function () {
        return view('humanresources.employment.employee-lists-detail-document');
    });
    Route::get('/employee-lists-detail-kjk', function () {
        return view('humanresources.employment.employee-lists-detail-kjk');
    });
    Route::get('/xml-employee-lists-detail-kjk', function () {
        return view('humanresources.employment.xml.employee-lists-detail-kjk');
    });
    Route::get('/xml-employee-lists-detail-overtime', function () {
        return view('humanresources.employment.xml.employee-lists-detail-overtime');
    });
    Route::get('/employee-lists-detail-family', function () {
        return view('humanresources.employment.employee-lists-detail-family');
    });
    Route::get('/employee-lists-detail-education', function () {
        return view('humanresources.employment.employee-lists-detail-education');
    });
    Route::get('/employee-lists-detail-skill', function () {
        return view('humanresources.employment.employee-lists-detail-skill');
    });
    Route::post('/employee-lists-detail-submit', function () {
        return view('humanresources.employment.employee-lists-detail-update');
    });
    Route::post('/os-contract-renew-submit', function () {
        return view('humanresources.employment.os-contract-renew-submit');
    });
    Route::get('/os-contract', function () {
        return view('humanresources.employment.os-contract');
    });
    Route::get('/xml-os-contract', function () {
        return view('humanresources.employment.xml.os-contract');
    });
    Route::get('/os-contract-detail', function () {
        return view('humanresources.employment.os-contract-detail');
    });
    Route::get('/close-contract', function () {
        return view('humanresources.employment.os-contract-close');
    });
    Route::post('/os-contract-close-submit', function () {
        return view('humanresources.employment.os-contract-close-submit');
    });
    Route::get('/os-contract-renew', function () {
        return view('humanresources.employment.os-contract-renew');
    });

    Route::get('/project-team', function () {
        return view('project.project-team');
    });

    Route::get('/xml-project-team', function () {
        return view('project.xml.project-team');
    });

    Route::get('/project-team-detail', function () {
        return view('project.project-team-detail');
    });
    Route::post('/project-team-detail-update', function () {
        return view('project.project-team-detail-update');
    });

    Route::get('/xml-project-team-detail', function () {
        return view('project.xml.project-team-detail');
    });
    Route::get('/project-team-detail-add', function () {
        return view('project.project-team-detail-add');
    });
    Route::get('/xml-project-team-detail-add', function () {
        return view('project.xml.project-team-detail-add');
    });
    Route::post('/project-team-detail-add-update', function () {
        return view('project.project-team-detail-add-update');
    });
    Route::post('/project-team-detail-add-submit', function () {
        return view('project.project-team-detail-add-submit');
    });


    Route::get('/project-issues', function () {
        return view('project.project-issues');
    });

    Route::get('/xml-project-issues', function () {
        return view('project.xml.project-issues');
    });

    Route::get('/project-issues-detail', function () {
        return view('project.project-issues-detail');
    });
    Route::get('/project-issues-detail-log', function () {
        return view('project.project-issues-detail-log');
    });
    Route::post('/project-issues-detail-update', function () {
        return view('project.project-issues-detail-update');
    });
    Route::get('/xml-project-issues-detail', function () {
        return view('project.xml.project-issues-detail');
    });
    Route::get('/xml-project-issues-detail-log', function () {
        return view('project.xml.project-issues-detail-log');
    });
    Route::get('/project-issues-detail-add', function () {
        return view('project.project-issues-detail-add');
    });
    Route::post('/project-issues-detail-add-submit', function () {
        return view('project.project-issues-detail-add-submit');
    });
    Route::get('/project-issues-detail-edit', function () {
        return view('project.project-issues-detail-edit');
    });
    Route::post('/project-issues-detail-edit-submit', function () {
        return view('project.project-issues-detail-edit-submit');
    });
    Route::get('/project-documents', function () {
        return view('project.project-documents');
    });

    Route::get('/xml-project-documents', function () {
        return view('project.xml.project-documents');
    });

    Route::get('/project-documents-detail', function () {
        return view('project.project-documents-detail');
    });
    Route::post('/project-documents-detail-update', function () {
        return view('project.project-documents-detail-update');
    });
    Route::get('/xml-project-documents-detail', function () {
        return view('project.xml.project-documents-detail');
    });
    Route::get('/project-documents-detail-add', function () {
        return view('project.project-documents-detail-add');
    });
    Route::post('/project-documents-detail-add-submit', function () {
        return view('project.project-documents-detail-add-submit');
    });
    Route::get('/project-documents-detail-edit', function () {
        return view('project.project-documents-detail-edit');
    });



    Route::get('/master-product-sub-group', function () {
        return view('procurement.master-product-sub-group');
    });
    Route::get('/xml-master-product-sub-group', function () {
        return view('procurement.xml.master-product-sub-group');
    });
    Route::post('/master-product-sub-group-update', function () {
        return view('procurement.master-product-sub-group-update');
    });

    Route::get('/master-product-sub-sub-group', function () {
        return view('procurement.master-product-sub-sub-group');
    });
    Route::get('/xml-master-product-sub-sub-group', function () {
        return view('procurement.xml.master-product-sub-sub-group');
    });
    Route::post('/master-product-sub-sub-group-update', function () {
        return view('procurement.master-product-sub-sub-group-update');
    });
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
    Route::get('/master-product-group', function () {
        return view('procurement.master-product-group');
    });
    Route::get('/xml-master-product-group', function () {
        return view('procurement.xml.master-product-group');
    });
    Route::post('/master-product-group-update', function () {
        return view('procurement.master-product-group-update');
    });
    Route::get('/xml-combo-product-group', function () {
        return view('procurement.xml.product-group');
    });
    Route::get('/xml-combo-product-sub-group', function () {
        return view('procurement.xml.product-sub-group');
    });
    Route::get('/xml-combo-product-approval', function () {
        return view('procurement.xml.product-approval');
    });
    Route::get('/permission-all', function () {
        return view('humanresources.permission.all');
    });

    Route::get('/xml-permission-all', function () {
        return view('humanresources.permission.xml.all');
    });
    Route::get('/attendant-data', function () {
        return view('humanresources.attendant.data');
    });

    Route::get('/xml-attendant-data', function () {
        return view('humanresources.attendant.xml.data');
    });
    Route::get('/permission-approval', function () {
        return view('humanresources.permission.approval');
    });
    Route::post('/permission-approval-update', function () {
        return view('humanresources.permission.approval-update');
    });
    Route::get('/xml-permission-approval', function () {
        return view('humanresources.permission.xml.approval');
    });
    Route::get('/xml-combo-directorat', function () {
        return view('humanresources.master.xml.combo-directorat');
    });
    Route::get('/xml-combo-department', function () {
        return view('humanresources.master.xml.combo-department');
    });
    Route::get('/xml-combo-section', function () {
        return view('humanresources.master.xml.combo-section');
    });
    Route::get('/master-holiday-detail', function () {
        return view('humanresources.permission.master-holiday-detail');
    });
    Route::get('/xml-master-holiday-detail', function () {
        return view('humanresources.permission.xml.holiday-detail');
    });
    Route::post('/master-section-update', function () {
        return view('humanresources.master.section-submit');
    });
    Route::get('/hrd-master-section', function () {
        return view('humanresources.master.section');
    });
    Route::get('/xml-hrd-master-section', function () {
        return view('humanresources.master.xml.section');
    });
    Route::post('/master-position-update', function () {
        return view('humanresources.master.position-submit');
    });
    Route::get('/hrd-master-position', function () {
        return view('humanresources.master.position');
    });
    Route::get('/xml-hrd-master-position', function () {
        return view('humanresources.master.xml.position');
    });


    Route::post('/hrd-master-department-submit', function () {
        return view('humanresources.master.department-update');
    });
    Route::get('/hrd-master-department', function () {
        return view('humanresources.master.department');
    });
    Route::get('/xml-hrd-master-department', function () {
        return view('humanresources.master.xml.department');
    });
    Route::post('/master-department-update', function () {
        return view('humanresources.master.department-submit');
    });
    Route::get('/hrd-master-directorat', function () {
        return view('humanresources.master.directorat');
    });
    Route::get('/xml-hrd-master-directorat', function () {
        return view('humanresources.master.xml.directorat');
    });
    Route::post('/master-directorat-update', function () {
        return view('humanresources.master.directorat-submit');
    });

    Route::post('/hrd-master-permission-submit', function () {
        return view('humanresources.permission.master-permission-update');
    });
    Route::get('/hrd-master-permission', function () {
        return view('humanresources.permission.master-permission');
    });
    Route::get('/xml-hrd-master-permission', function () {
        return view('humanresources.permission.xml.master-permission');
    });
    Route::get('/xml-combo-permission-group', function () {
        return view('humanresources.permission.xml.combo-permission-group');
    });
    Route::get('/hrd-master-holiday', function () {
        return view('humanresources.permission.master-holiday');
    });
    Route::post('/hrd-master-holiday-submit', function () {
        return view('humanresources.permission.master-holiday-update');
    });
    Route::get('/xml-hrd-master-holiday', function () {
        return view('humanresources.permission.xml.master-holiday');
    });
    Route::get('/permission-new', function () {
        return view('humanresources.permission.new');
    });
    Route::post('/permission-new-submit', function () {
        return view('humanresources.permission.new-permission-submit');
    });
    Route::get('/load-kategori-izin', function () {
        return view('humanresources.permission.load-kategori-izin');
    });

    Route::get('/print-bpjs', function () {
        return view('humanresources.payroll.prints.bpjs');
    });
    Route::get('/print-pph', function () {
        return view('humanresources.payroll.prints.tax');
    });
    Route::get('/print-salary', function () {
        return view('humanresources.payroll.prints.salary');
    });
    Route::get('/payroll-lists', function () {
        return view('humanresources.payroll.payroll-lists');
    });
    Route::post('/payroll-lists-update', function () {
        return view('humanresources.payroll.payroll-lists-update');
    });
    Route::get('/xml-payroll-lists', function () {
        return view('humanresources.payroll.xml.payroll-lists');
    });

    Route::get('/xml-payroll-ap-salary', function () {
        return view('humanresources.payroll.xml.payroll-ap-salary');
    });

    Route::get('/payroll-tax', function () {
        return view('humanresources.payroll.payroll-tax');
    });
    Route::post('/payroll-tax-update', function () {
        return view('humanresources.payroll.payroll-tax-update');
    });
    Route::get('/xml-payroll-tax', function () {
        return view('humanresources.payroll.xml.payroll-tax');
    });


    Route::get('/payroll-group', function () {
        return view('humanresources.payroll.payroll-group');
    });
    Route::post('/payroll-group-update', function () {
        return view('humanresources.payroll.payroll-group-update');
    });
    Route::get('/xml-payroll-group', function () {
        return view('humanresources.payroll.xml.payroll-group');
    });
    Route::get('/payroll-group-lists-none', function () {
        return view('humanresources.payroll.payroll-group-lists-none');
    });
    Route::get('/payroll-group-detail', function () {
        return view('humanresources.payroll.payroll-group-detail');
    });
    Route::post('/payroll-group-detail-update', function () {
        return view('humanresources.payroll.payroll-group-detail-update');
    });
    Route::get('/payroll-employee-detail', function () {
        return view('humanresources.payroll.payroll-employee-detail');
    });
    Route::get('/payroll-employee-detail-add', function () {
        return view('humanresources.payroll.payroll-employee-detail-add');
    });
    Route::post('/payroll-employee-detail-update', function () {
        return view('humanresources.payroll.payroll-employee-detail-update');
    });
    Route::get('/payroll-employee-recounting', function () {
        return view('humanresources.payroll.payroll-employee-recounting');
    });
    Route::post('/payroll-group-employee-update', function () {
        return view('humanresources.payroll.payroll-group-employee-update');
    });
    Route::get('/xml-payroll-group-detail', function () {
        return view('humanresources.payroll.xml.payroll-group-detail');
    });
    Route::get('/xml-payroll-employee-detail', function () {
        return view('humanresources.payroll.xml.payroll-employee-detail');
    });
    Route::get('/payroll-group-detail-add', function () {
        return view('humanresources.payroll.payroll-group-detail-add');
    });
    Route::post('/payroll-group-detail-add-update', function () {
        return view('humanresources.payroll.payroll-group-detail-add-update');
    });
    Route::get('/xml-payroll-group-detail-add', function () {
        return view('humanresources.payroll.xml.payroll-group-detail-add');
    });

    Route::get('/payroll-group-lists', function () {
        return view('humanresources.payroll.payroll-group-lists');
    });
    Route::post('/payroll-group-lists-update', function () {
        return view('humanresources.payroll.payroll-group-lists-update');
    });
    Route::get('/xml-payroll-group-lists', function () {
        return view('humanresources.payroll.xml.payroll-group-lists');
    });

    Route::get('/payroll-group-lists-add', function () {
        return view('humanresources.payroll.payroll-group-lists-add');
    });
    Route::post('/payroll-group-lists-add-update', function () {
        return view('humanresources.payroll.payroll-group-lists-add-update');
    });
    Route::get('/xml-payroll-group-lists-add', function () {
        return view('humanresources.payroll.xml.payroll-group-lists-add');
    });
    Route::get('/payroll-employee', function () {
        return view('humanresources.payroll.payroll-employee');
    });
    Route::post('/payroll-employee-update', function () {
        return view('humanresources.payroll.payroll-employee-update');
    });
    Route::get('/xml-payroll-employee', function () {
        return view('humanresources.payroll.xml.payroll-employee');
    });
    Route::get('/search-employee', function () {
        return view('humanresources.overtime.search-employee');
    });
    Route::get('/overtime-new', function () {
        return view('humanresources.overtime.new');
    });
    Route::get('/xml-overtime-new', function () {
        return view('humanresources.overtime.xml.overtime-new');
    });
    Route::post('/overtime-new-update', function () {
        return view('humanresources.overtime.new-update');
    });
    Route::get('/overtime-approval', function () {
        return view('humanresources.overtime.overtime-approval');
    });
    Route::get('/xml-overtime-approval', function () {
        return view('humanresources.overtime.xml.overtime-approval');
    });
    Route::post('/overtime-approval-update', function () {
        return view('humanresources.overtime.approval-update');
    });
    Route::get('/overtime-approval', function () {
        return view('humanresources.overtime.overtime-approval');
    });
    Route::get('/overtime-all', function () {
        return view('humanresources.overtime.all');
    });
    Route::get('/xml-overtime-all', function () {
        return view('humanresources.overtime.xml.overtime-all');
    });
    Route::get('/kjk-all', function () {
        return view('humanresources.overtime.kjk-all');
    });
    Route::get('/xml-kjk-all', function () {
        return view('humanresources.overtime.xml.kjk-all');
    });
    Route::get('/new-select-employee', function () {
        return view('humanresources.overtime.new-select-employee');
    });
    Route::get('/xml-select-employee', function () {
        return view('humanresources.overtime.xml.new-select-employee');
    });
    Route::get('/new-kjk-employee', function () {
        return view('humanresources.overtime.new-kjk-employee');
    });
    Route::get('/xml-kjk-employee', function () {
        return view('humanresources.overtime.xml.new-kjk-employee');
    });
    Route::get('/kjk-new', function () {
        return view('humanresources.overtime.new-kjk');
    });
    Route::get('/xml-kjk-new', function () {
        return view('humanresources.overtime.xml.kjk-new');
    });
    Route::post('/kjk-new-update', function () {
        return view('humanresources.overtime.new-kjk-update');
    });
    Route::post('/kjk-approval-update', function () {
        return view('humanresources.overtime.kjk-approval-update');
    });
    Route::get('/kjk-approval', function () {
        return view('humanresources.overtime.kjk-approval');
    });
    Route::get('/xml-kjk-approval', function () {
        return view('humanresources.overtime.xml.kjk-approval');
    });
    Route::get('/load-overtime-kategori', function () {
        return view('humanresources.overtime.load-overtime-kategori');
    });
    Route::get('/load-pegawai', function () {
        return view('humanresources.overtime.load-pegawai');
    });
    Route::get('/payroll-approval', function () {
        return view('humanresources.payroll.payroll-approval');
    });
    Route::get('/payroll-approval-cancel', function () {
        return view('humanresources.payroll.payroll-approval-cancel');
    });
    Route::get('/payroll-approval-approve', function () {
        return view('humanresources.payroll.payroll-approval-approve');
    });
    Route::get('/xml-payroll-approval', function () {
        return view('humanresources.payroll.xml.payroll-approval');
    });
    Route::get('/payroll-approval-detail', function () {
        return view('humanresources.payroll.payroll-approval-detail');
    });
    Route::get('/payroll-approval-detail-detail', function () {
        return view('humanresources.payroll.payroll-approval-detail-detail');
    });
    Route::get('/xml-payroll-approval-detail', function () {
        return view('humanresources.payroll.xml.payroll-approval-detail');
    });
    Route::get('/payroll-approved', function () {
        return view('humanresources.payroll.payroll-approved');
    });
    Route::get('/xml-payroll-approved', function () {
        return view('humanresources.payroll.xml.payroll-approved');
    });
    Route::get('/payroll-approved-detail', function () {
        return view('humanresources.payroll.payroll-approved-detail');
    });
    Route::get('/payroll-approved-detail-detail', function () {
        return view('humanresources.payroll.payroll-approved-detail-detail');
    });
    Route::get('/xml-payroll-approved-detail', function () {
        return view('humanresources.payroll.xml.payroll-approved-detail');
    });

    Route::get('/payroll-generator', function () {
        return view('humanresources.payroll.payroll-generator');
    });
    Route::post('/payroll-generator-submit', function () {
        return view('humanresources.payroll.payroll-generator-submit');
    });
    Route::get('/payroll-generator-detail', function () {
        return view('humanresources.payroll.payroll-generator-detail');
    });
    Route::get('/xml-payroll-generator-employee', function () {
        return view('humanresources.payroll.xml.payroll-generator-employee');
    });
    Route::get('/payroll-generator-upload', function () {
        return view('humanresources.payroll.payroll-generator-upload');
    });
    Route::get('/payroll-generator-upload-process', function () {
        return view('humanresources.payroll.payroll-generator-upload-process');
    });
    Route::post('/payroll-generator-detail-update', function () {
        return view('humanresources.payroll.payroll-generator-detail-update');
    });
    Route::get('/payroll-template-master-lists', function () {
        return view('humanresources.payroll.payroll-template-master-lists');
    });
    Route::post('/payroll-template-master-lists-update', function () {
        return view('humanresources.payroll.payroll-template-master-lists-update');
    });
    Route::get('/xml-payroll-template-master-lists', function () {
        return view('humanresources.payroll.xml.payroll-template-master-lists');
    });
    Route::get('/payroll-template-master', function () {
        return view('humanresources.payroll.payroll-template-master');
    });
    Route::post('/payroll-template-master-update', function () {
        return view('humanresources.payroll.payroll-template-master-update');
    });
    Route::get('/xml-payroll-template-master', function () {
        return view('humanresources.payroll.xml.payroll-template-master');
    });
    Route::get('/payroll-template-master-lists-add', function () {
        return view('humanresources.payroll.payroll-template-master-lists-add');
    });
    Route::post('/payroll-template-master-lists-add-update', function () {
        return view('humanresources.payroll.payroll-template-master-lists-add-update');
    });
    Route::get('/xml-payroll-template-master-lists-add', function () {
        return view('humanresources.payroll.xml.payroll-template-master-lists-add');
    });
    Route::get('/attendant-entry', function () {
        return view('humanresources.attendant.attendant-entry');
    });
    Route::post('/attendant-entry-update', function () {
        return view('humanresources.attendant.attendant-entry-update');
    });
    Route::get('/xml-attendant-entry', function () {
        return view('humanresources.attendant.xml.attendant-entry');
    });
    Route::get('/attendant-entry-lists', function () {
        return view('humanresources.attendant.attendant-entry-lists');
    });
    Route::post('/attendant-entry-lists-update', function () {
        return view('humanresources.attendant.attendant-entry-lists-update');
    });
    Route::get('/xml-attendant-entry-lists', function () {
        return view('humanresources.attendant.xml.attendant-entry-lists');
    });

    Route::get('/attendant-data-lists', function () {
        return view('humanresources.attendant.attendant-data-lists');
    });
    Route::get('/xml-attendant-data-lists', function () {
        return view('humanresources.attendant.xml.attendant-data-lists');
    });
    Route::get('/resigned-employee', function () {
        return view('humanresources.other.resigned-employee');
    });
    Route::get('/xml-resigned-employee', function () {
        return view('humanresources.other.xml.resigned-employee');
    });
    Route::get('/resigned-employee-detail', function () {
        return view('humanresources.other.resigned-employee-detail');
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


    Route::get('/credential-maillog', function () {
        return view('credential.maillog');
    });

    Route::post('/credential-maillog-update', function () {
        return view('credential.maillog-update');
    });

    Route::get('/xml-credential-maillog', function () {
        return view('credential.xml.maillog');
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

    Route::get('credential-organization', function () {
        return view('credential.organization');
    });
    Route::get('/xml-credential-organization', function () {
        return view('credential.xml.organization');
    });
    Route::post('/credential-organization-update', function () {
        return view('credential.organization-update');
    });

    Route::get('/home-businesspartner', function () {
        return view('home-businesspartner');
    });

    Route::get('partner-outstanding-leads', function () {
        return view('partner.outstanding-leads');
    });

    Route::get('xml-partner-outstanding-leads', function () {
        return view('partner.xml.outstanding-leads');
    });

    Route::get('partner-outstanding-leads-detail', function () {
        return view('partner.outstanding-leads-detail');
    });

    Route::post('partner-outstanding-leads-detail-submit', function () {
        return view('partner.outstanding-leads-detail-submit');
    });

    Route::get('partner-registration-business', function () {
        return view('partner.registration');
    });

    Route::get('partner-fresh-leads', function () {
        return view('partner.fresh-leads');
    });

    Route::get('xml-partner-fresh-leads', function () {
        return view('partner.xml.fresh-leads');
    });

    Route::get('partner-fresh-leads-detail', function () {
        return view('partner.fresh-leads-detail');
    });

    Route::post('partner-fresh-leads-update', function () {
        return view('partner.fresh-leads-update');
    });

    Route::get('partner-assign-sales', function () {
        return view('partner.assign-sales');
    });

    Route::get('xml-partner-assign-sales', function () {
        return view('partner.xml.assign-sales');
    });

    Route::post('partner-assign-sales-update', function () {
        return view('partner.assign-sales-update');
    });

    Route::get('partner-assign-sales-detail', function () {
        return view('partner.assign-sales-detail');
    });

    Route::get('partner-assign-sales-detail-whatsapp', function () {
        return view('partner.assign-sales-detail-whatsapp');
    });

    Route::get('xml-partner-assign-sales-detail', function () {
        return view('partner.xml.assign-sales-detail');
    });

    Route::get('partner-assign-sales-detail-leads', function () {
        return view('partner.assign-sales-detail-leads');
    });

    Route::get('partner-reassign-sales', function () {
        return view('partner.reassign-sales');
    });

    Route::get('xml-partner-reassign-sales', function () {
        return view('partner.xml.reassign-sales');
    });

    Route::get('partner-reassign-sales-detail', function () {
        return view('partner.reassign-sales-detail');
    });

    Route::get('xml-partner-reassign-sales-detail', function () {
        return view('partner.xml.reassign-sales-detail');
    });

    Route::post('partner-reassign-sales-update', function () {
        return view('partner.reassign-sales-update');
    });

    Route::get('partner-reassign-sales-detail-leads', function () {
        return view('partner.reassign-sales-detail-leads');
    });

    Route::post('partner-reassign-sales-detail-update', function () {
        return view('partner.reassign-sales-detail-update');
    });

    Route::get('partner-canceled-leads', function () {
        return view('partner.canceled-leads');
    });

    Route::get('xml-partner-canceled-leads', function () {
        return view('partner.xml.canceled-leads');
    });

    Route::get('partner-canceled-leads-detail', function () {
        return view('partner.canceled-leads-detail');
    });

    Route::post('partner-canceled-leads-update', function () {
        return view('partner.canceled-leads-update');
    });

    Route::get('partner-assign-history', function () {
        return view('partner.assign-history');
    });

    Route::get('xml-partner-assign-history', function () {
        return view('partner.xml.assign-history');
    });

    Route::get('partner-assign-history-log', function () {
        return view('partner.assign-history-log');
    });

    Route::get('xml-partner-assign-history-log', function () {
        return view('partner.xml.assign-history-log');
    });

    Route::get('partner-assign-history-detail', function () {
        return view('partner.assign-history-detail');
    });

    Route::get('xml-partner-assign-history-detail', function () {
        return view('partner.xml.assign-history-detail');
    });

    Route::get('partner-assign-history-detail-log', function () {
        return view('partner.assign-history-detail-log');
    });

    Route::get('xml-partner-assign-history-detail-log', function () {
        return view('partner.xml.assign-history-detail-log');
    });

    Route::get('partner-history-leads', function () {
        return view('partner.history-leads');
    });

    Route::get('xml-partner-history-leads', function () {
        return view('partner.xml.history-leads');
    });

    Route::get('partner-history-leads-detail', function () {
        return view('partner.history-leads-detail');
    });

    Route::get('partner-business-registration', function () {
        return view('partner.business-registration');
    });

    Route::get('xml-partner-business-registration', function () {
        return view('partner.xml.business-registration');
    });
    Route::get('partner-business-registration-detail', function () {
        return view('partner.business-registration-detail');
    });


    Route::get('partner-business-lists', function () {
        return view('partner.business-lists');
    });

    Route::get('partner-business-lists-whatsapp', function () {
        return view('partner.business-lists-whatsapp');
    });

    Route::get('xml-partner-business-lists', [PartnerListsController::class, 'indexXML']);

    Route::get('partner-business-lists-detail', [PartnerListsController::class, 'detail']);

    Route::post('upload-csv', [UploaderController::class, 'uploadAndInsertFromCsv']);
    Route::get('download-leads-template', [UploaderController::class, 'downloadLeadsTemplate']);

    /** Master Data */
    $masterData = "master";
    Route::get("$masterData-lead-qualification", [MasterDataController::class, 'getLeadQualification']);
    Route::get("$masterData-lead-classification", [MasterDataController::class, 'getLeadClassification']);
    route::get("$masterData-lead-status", [Masterdatacontroller::class, 'getLeadStatus']);
    route::get("$masterData-lead-response", [Masterdatacontroller::class, 'getLeadResponse']);
    route::get("$masterData-lead-cancel-reason", [Masterdatacontroller::class, 'getLeadCancelReason']);
    Route::get("$masterData-province", [MasterDataController::class, 'provinceSearch']);
    Route::get("$masterData-city", [MasterDataController::class, 'citySearch']);
    Route::get("$masterData-district", [MasterDataController::class, 'districtSearch']);
    Route::get("$masterData-subdistrict", [MasterDataController::class, 'subdistrictSearch']);
    Route::get("$masterData-users", [MasterDataController::class, "getAllUsers"]);
    Route::get("$masterData-postal-code", [MasterDataController::class, 'postalCodeSearch']);
    Route::get("$masterData-facility", [MasterDataController::class, 'indexWarehouseFacility']);
    Route::get("$masterData-facility-create", [MasterDataController::class, 'createWarehouseFacility']);
    Route::post("$masterData-facility-create-submit", [MasterDataController::class, 'submitWarehouseFacility']);
    Route::get("$masterData-facility-data", [MasterDataController::class, 'getWarehouseFacility']);
    Route::delete("$masterData-facility-delete", [MasterDataController::class, 'deleteWarehouseFacility']);
    Route::get("$masterData-fleet", [MasterDataController::class, 'indexMasterFleet']);
    Route::get("$masterData-fleet-data", [MasterDataController::class, 'getMasterFleet']);
    Route::get("$masterData-fleet-create", [MasterDataController::class, 'createMasterFleet']);
    Route::post("$masterData-fleet-create-submit", [MasterDataController::class, 'submitCreateMasterFleet']);

    // --- Master Packaging ---
    Route::get("$masterData-packaging", [MasterDataController::class, 'packagingIndex']);
    Route::get("$masterData-packaging-data",     [MasterDataController::class, 'packagingData']);
    Route::post("$masterData-packaging-submit",  [MasterDataController::class, 'packagingSubmit']);
    Route::post("$masterData-packaging-edit",    [MasterDataController::class, 'packagingEdit']);
    Route::delete("$masterData-packaging-delete", [MasterDataController::class, 'packagingDelete']);
    Route::get("$masterData-packaging-create", [MasterDataController::class, 'packagingCreate']);

    // --- Master Category ---
    Route::get("$masterData-category", [MasterDataController::class, 'categoryIndex']);
    Route::get("$masterData-category-data",      [MasterDataController::class, 'categoryData']);
    Route::post("$masterData-category-submit",   [MasterDataController::class, 'categorySubmit']);
    Route::post("$masterData-category-edit",     [MasterDataController::class, 'categoryEdit']);
    Route::delete("$masterData-category-delete", [MasterDataController::class, 'categoryDelete']);
    Route::get("$masterData-category-create", [MasterDataController::class, 'categoryCreate']);

    /** POO (Point of Origin UCO) */
    Route::get("$masterData-poo", [MasterDataController::class, 'POOPageIndex']);
    Route::get("$masterData-poo-data", [MasterDataController::class, 'getPOO']);
    Route::get("$masterData-poo-create", [MasterDataController::class, 'createPOOPage']);
    Route::post("$masterData-poo-create-submit", [MasterDataController::class, 'createPOO']);
    Route::get("$masterData-poo-update", [MasterDataController::class, 'updatePOOPage']);
    Route::post("$masterData-poo-update-submit", [MasterDataController::class, 'updatePOO']);



    /** finance */
    $finance = "finance";
    Route::get("$finance-pickup-cost", [CostManagementController::class, 'pickupCostIndex']);
    Route::post("$finance-cost-approve-route", [CostManagementController::class, "approvePickupCost"]);

    Route::get("finance-settlement-cost", [UCOLogisticController::class, 'indexSettlement']);
    Route::get("finance-acquisition-cost", [UCOLogisticController::class, 'indexAcquisition']);

    Route::get("finance-acquisition-cost-data",   [UCOLogisticController::class, 'getAcquisitionCostData']);
    Route::post("finance-acquisition-cost-submit", [UCOLogisticController::class, 'submitAcquisitionCost']);
    Route::post("finance-acquisition-cost-action", [UCOLogisticController::class, 'actionAcquisitionCost']);
    Route::post("finance-acquisition-cost-upload-receipt", [UCOLogisticController::class, 'uploadAcquisitionReceipt']);
    Route::get("finance-acquisition-cost-detail", [UCOLogisticController::class, 'getAcquisitionDetail']);

    /** Work Order */
    $workOrderPref = "partner-work-order";
    Route::get("$workOrderPref", [UCOLogisticController::class, 'indexWorkOrder']);
    Route::get("$workOrderPref-data", [UCOLogisticController::class, 'getWorkOrder']);
    Route::get("$workOrderPref-create", [UCOLogisticController::class, 'createWorkOrder']);
    Route::post("$workOrderPref-create-submit", [UCOLogisticController::class, 'submitWorkOrder']);

    /** Pickup */
    $pickupPref = "partner-pickup";
    Route::get("$pickupPref", [UCOLogisticController::class, 'indexPickupRequest']);
    Route::get("$pickupPref-data", [UCOLogisticController::class, 'getPickupListData']);
    Route::get("$pickupPref-create", [UCOLogisticController::class, 'createPickupRequest']);
    Route::post("$pickupPref-create-submit", [UCOLogisticController::class, 'submitCreatePickupRequest']);
    Route::post("$pickupPref-assign-fleet", [UCOLogisticController::class, 'assignFleetToPickupRequest']);
    Route::post("$pickupPref-finish-settlement", [UCOLogisticController::class, 'finishSettlement']);



    Route::get("$pickupPref-route", [UCOLogisticController::class, 'indexPickupRouting']);
    Route::get("$pickupPref-route-data", [UCOLogisticController::class, "getSavedRouteList"]);
    Route::get("$pickupPref-route-waypoints-data", [UCOLogisticController::class, "getSavedRouteWaypointsList"]);
    Route::get("$pickupPref-route-map-data",     [UCOLogisticController::class, 'getRouteMapData']);
    Route::get("$pickupPref-list-data", [UCOLogisticController::class, 'getPickupListData']);
    Route::post("$pickupPref-route-submit", [UCOLogisticController::class, "submitRoute"])->middleware('throttle:60,1');
    Route::post("$pickupPref-route-calculate", [UCOLogisticController::class, "calculateRoute"]);
    Route::get("$pickupPref-order-data", [UCOLogisticController::class, "getPickupOrderData"]);

    Route::get("fleet-data", [UCOLogisticController::class, "getFleetData"]);


    $deliveryOrderPref = "partner-delivery-order";
    Route::get("$deliveryOrderPref", [UCOLogisticController::class, 'indexDeliveryOrder']);
    Route::get("$deliveryOrderPref-data", [UCOLogisticController::class, 'getDeliveryOrderData']);
    Route::get("$deliveryOrderPref-create", [UCOLogisticController::class, 'createDeliveryOrder']);
    Route::post("$deliveryOrderPref-create-submit", [UCOLogisticController::class, 'submitCreateDeliveryOrder']);
    Route::get("$deliveryOrderPref-update", [UCOLogisticController::class, 'updateDeliveryOrderPage']);
    Route::post("$deliveryOrderPref-update-submit", [UCOLogisticController::class, 'submitUpdateDeliveryOrder']);
    Route::post("$deliveryOrderPref-update-receipt", [UCOLogisticController::class, 'updateDeliveryOrderReceipt']);
    Route::post("$deliveryOrderPref-update-note", [UCOLogisticController::class, 'updateDeliveryOrderNote']);


    /** Ticket */
    $ucoTicket = "partner-uco-ticket";
    Route::get("$ucoTicket", []);

    /** leads */
    $partnerLeads =  "partner-leads";
    Route::get("$partnerLeads", [LeadsController::class, 'getLeads']);
    Route::post("$partnerLeads-update", [LeadsController::class, 'updateLeads']);
    Route::delete("$partnerLeads", [LeadsController::class, 'deleteLeads']);

    /** partner registration approval */
    $partnerApprovalPrefix = "partner-approval";
    Route::get("$partnerApprovalPrefix-progress/generate-mou", [ApprovalController::class, 'generateMOU']);
    Route::get("$partnerApprovalPrefix-progress/generate-pks", [ApprovalController::class, 'generatePKS']);
    Route::post("$partnerApprovalPrefix-progress/submit", [ApprovalController::class, 'submit']);
    Route::get("$partnerApprovalPrefix-progress", [ApprovalController::class, 'showProgress']);
    Route::get("$partnerApprovalPrefix-index", [ApprovalController::class, 'index']);
    Route::get("$partnerApprovalPrefix-detail", [ApprovalController::class, 'detail']);
    Route::get("$partnerApprovalPrefix-data", [ApprovalController::class, 'getApprovalData']);
    Route::get("$partnerApprovalPrefix-data-log", [ApprovalController::class, 'getApprovalDataLog']);


    /** Benefit Facility */
    $facilityPref = 'partner-facility';
    Route::get("$facilityPref", [PartnerFacilityController::class, 'index']);
    Route::get("xml-$facilityPref", [PartnerFacilityController::class, 'indexXml']);
    Route::get("$facilityPref-detail-booth", [PartnerFacilityController::class, 'indexDetailBooth']);
    Route::get("xml-$facilityPref-detail-booth-inventory", [PartnerFacilityController::class, 'indexDetailBoothInventoryXml']);
    Route::get("xml-$facilityPref-detail-booth-access", [PartnerFacilityController::class, 'indexDetailBoothAccessXml']);
    Route::get("xml-$facilityPref-detail-booth-support", [PartnerFacilityController::class, 'indexDetailBoothSupportXml']);
    Route::get("$facilityPref-detail-mobil", [PartnerFacilityController::class, 'indexDetailMobil']);
    Route::get("xml-$facilityPref-detail-mobil-inventory", [PartnerFacilityController::class, 'indexDetailMobilInventoryXml']);
    Route::get("xml-$facilityPref-detail-mobil-access", [PartnerFacilityController::class, 'indexDetailMobilAccessXml']);
    Route::get("xml-$facilityPref-detail-mobil-support", [PartnerFacilityController::class, 'indexDetailMobilSupportXml']);
    Route::post("$facilityPref-detail-update", [PartnerFacilityController::class, 'detailFacilityUpdate']);

    /** Asset Management */
    $assetManagementPref = 'partner-asset-management';
    Route::get("$assetManagementPref", [PartnerAssetManagementController::class, 'index']);
    Route::get("xml-$assetManagementPref", [PartnerAssetManagementController::class, 'indexXml']);
    Route::get("$assetManagementPref-detail-booth", [PartnerAssetManagementController::class, 'indexDetailBooth']);
    Route::get("xml-$assetManagementPref-detail-booth-inventory", [PartnerAssetManagementController::class, 'indexDetailBoothInventoryXml']);
    Route::get("xml-$assetManagementPref-detail-booth-access", [PartnerAssetManagementController::class, 'indexDetailBoothAccessXml']);
    Route::get("xml-$assetManagementPref-detail-booth-support", [PartnerAssetManagementController::class, 'indexDetailBoothSupportXml']);
    Route::get("$assetManagementPref-detail-mobil", [PartnerAssetManagementController::class, 'indexDetailMobil']);
    Route::get("xml-$assetManagementPref-detail-mobil-inventory", [PartnerAssetManagementController::class, 'indexDetailMobilInventoryXml']);
    Route::get("xml-$assetManagementPref-detail-mobil-access", [PartnerAssetManagementController::class, 'indexDetailMobilAccessXml']);
    Route::get("xml-$assetManagementPref-detail-mobil-support", [PartnerAssetManagementController::class, 'indexDetailMobilSupportXml']);
    Route::post("$assetManagementPref-detail-update", [PartnerAssetManagementController::class, 'detailFacilityUpdate']);

    $orderEquipmentPref = "partner-order-equipment";
    Route::get("$orderEquipmentPref", [PartnerOrderEquipmentController::class, 'index']);
    Route::get("xml-$orderEquipmentPref", [PartnerOrderEquipmentController::class, 'indexXml']);
    Route::get("$orderEquipmentPref-detail-booth", [PartnerOrderEquipmentController::class, 'indexDetailBooth']);
    Route::get("xml-$orderEquipmentPref-detail-booth-inventory", [PartnerOrderEquipmentController::class, 'indexDetailBoothInventoryXml']);
    Route::get("$orderEquipmentPref-detail-mobil", [PartnerOrderEquipmentController::class, 'indexDetailMobil']);
    Route::get("xml-$orderEquipmentPref-detail-mobil-inventory", [PartnerOrderEquipmentController::class, 'indexDetailMobilInventoryXml']);
    Route::post("$orderEquipmentPref-detail-update", [PartnerOrderEquipmentController::class, 'detailFacilityUpdate']);

    /** Benefit Facility Mitra */
    $facilityPref = 'partner-business-facility';
    // Route::get("$facilityPref", [PartnerBusinessFacilityController::class, 'index']);
    // Route::get("xml-$facilityPref", [PartnerBusinessFacilityController::class, 'indexXml']);
    Route::get("$facilityPref-booth", [PartnerBusinessFacilityController::class, 'indexDetailBooth']);
    Route::get("xml-$facilityPref-booth-inventory", [PartnerBusinessFacilityController::class, 'indexDetailBoothInventoryXml']);
    Route::get("xml-$facilityPref-booth-access", [PartnerBusinessFacilityController::class, 'indexDetailBoothAccessXml']);
    Route::get("xml-$facilityPref-booth-support", [PartnerBusinessFacilityController::class, 'indexDetailBoothSupportXml']);
    Route::get("$facilityPref-mobil", [PartnerBusinessFacilityController::class, 'indexDetailMobil']);
    Route::get("xml-$facilityPref-mobil-inventory", [PartnerBusinessFacilityController::class, 'indexDetailMobilInventoryXml']);
    Route::get("xml-$facilityPref-mobil-access", [PartnerBusinessFacilityController::class, 'indexDetailMobilAccessXml']);
    Route::get("xml-$facilityPref-mobil-support", [PartnerBusinessFacilityController::class, 'indexDetailMobilSupportXml']);
    Route::post("$facilityPref-update", [PartnerBusinessFacilityController::class, 'detailFacilityUpdate']);

    /** Master Price Stock */
    $masterPriceStockPref = "partner-master-stock-price";
    Route::get("$masterPriceStockPref", [PartnerMasterStockPriceController::class, 'index']);
    Route::get("xml-$masterPriceStockPref", [PartnerMasterStockPriceController::class, 'indexXml']);
    Route::post("$masterPriceStockPref-update", [PartnerMasterStockPriceController::class, 'update']);

    /** Warehouse */
    $warehousePref = 'partner-warehouse';
    Route::get("$warehousePref-data", [PartnerWarehouseController::class, 'getWarehouses']);
    Route::get("$warehousePref-list", [PartnerWarehouseController::class, 'indexWarehouseList']);
    Route::get("$warehousePref-list-nonaktifkan", [PartnerWarehouseController::class, 'nonActiveWarehouseList']);
    Route::get("xml-$warehousePref-list", [PartnerWarehouseController::class, 'indexWarehouseListXml']);
    Route::get("$warehousePref-list-new", [PartnerWarehouseController::class, 'newWarehouseList']);
    Route::post("$warehousePref-list-new-submit", [PartnerWarehouseController::class, 'newWarehouseListSubmit']);
    Route::get("$warehousePref-list-detail", [PartnerWarehouseController::class, 'detailWarehouseList']);
    Route::post("$warehousePref-list-detail-update", [PartnerWarehouseController::class, 'detailWarehouseListUpdate']);
    Route::get("xml-$warehousePref-list-detail", [PartnerWarehouseController::class, 'detailWarehouseListXml']);
    Route::get("$warehousePref-list-detail-new", [PartnerWarehouseController::class, 'detailWarehouseListNew']);
    Route::post("$warehousePref-list-detail-new-submit", [PartnerWarehouseController::class, 'detailWarehouseListNewSubmit']);
    Route::get("$warehousePref-list-detail-detail", [PartnerWarehouseController::class, 'detailWarehouseListDetail']);
    Route::post("$warehousePref-list-detail-detail-submit", [PartnerWarehouseController::class, 'detailWarehouseListDetailSubmit']);

    Route::get("$warehousePref-open", [PartnerWarehouseOpenController::class, 'indexWarehouseOpen']);
    Route::get("xml-$warehousePref-open", [PartnerWarehouseOpenController::class, 'indexWarehouseOpenXml']);
    Route::get("$warehousePref-open-new", [PartnerWarehouseOpenController::class, 'newWarehouseOpen']);
    Route::post("$warehousePref-open-new-submit", [PartnerWarehouseOpenController::class, 'newWarehouseOpenSubmit']);
    Route::get("$warehousePref-open-detail", [PartnerWarehouseOpenController::class, 'detailWarehouseOpen']);
    Route::post("$warehousePref-open-detail-submit", [PartnerWarehouseOpenController::class, 'detailWarehouseOpenSubmit']);

    Route::get("$warehousePref-request", [PartnerWarehouseStockRequestController::class, 'indexWarehouseStockRequest']);
    Route::get("xml-$warehousePref-request", [PartnerWarehouseStockRequestController::class, 'indexWarehouseStockRequestXml']);
    Route::get("$warehousePref-request-detail", [PartnerWarehouseStockRequestController::class, 'detailWarehouseStockRequest']);
    Route::get("xml-$warehousePref-request-detail", [PartnerWarehouseStockRequestController::class, 'detailWarehouseStockRequestXml']);
    Route::get("$warehousePref-request-detail-new", [PartnerWarehouseStockRequestController::class, 'detailWarehouseStockRequestNew']);
    Route::post("$warehousePref-request-detail-new-submit", [PartnerWarehouseStockRequestController::class, 'detailWarehouseStockRequestNewSubmit']);

    Route::post("partner-get-master-price", [PartnerMasterStockPriceController::class, 'getCurrentPrice']);

    Route::get("$warehousePref-monitoring", [PartnerWarehouseMonitoringController::class, 'indexMonitoringWarehouse']);
    Route::get("xml-$warehousePref-monitoring", [PartnerWarehouseMonitoringController::class, 'indexMonitoringWarehouseXml']);
    Route::get("$warehousePref-monitoring-detail", [PartnerWarehouseMonitoringController::class, 'detailMonitoringWarehouse']);
    Route::get("xml-$warehousePref-monitoring-detail", [PartnerWarehouseMonitoringController::class, 'detailMonitoringWarehouseXml']);
    Route::get("$warehousePref-monitoring-detail-list", [PartnerWarehouseMonitoringController::class, 'detailMonitoringWarehouseList']);
    Route::get("xml-$warehousePref-monitoring-detail-supplies", [PartnerWarehouseMonitoringController::class, 'detailMonitoringWarehouseSuppliesXml']);
    Route::get("xml-$warehousePref-monitoring-detail-request", [PartnerWarehouseMonitoringController::class, 'detailMonitoringWarehouseRequestXml']);

    Route::get("$warehousePref-journal", [PartnerWarehouseJournalController::class, 'indexWarehouseJournal']);
    Route::get("xml-$warehousePref-journal", [PartnerWarehouseJournalController::class, 'indexWarehouseJournalXml']);
    Route::get("$warehousePref-journal-detail", [PartnerWarehouseJournalController::class, 'detailWarehouseJournal']);
    Route::get("xml-$warehousePref-journal-detail", [PartnerWarehouseJournalController::class, 'detailWarehouseJournalXml']);
    Route::get("$warehousePref-journal-detail-new", [PartnerWarehouseJournalController::class, 'detailWarehouseJournalNew']);
    Route::post("$warehousePref-journal-detail-new-submit", [PartnerWarehouseJournalController::class, 'detailWarehouseJournalNewSubmit']);
    Route::get("$warehousePref-journal-detail-detail", [PartnerWarehouseJournalController::class, 'detailWarehouseJournalDetail']);
    Route::post("$warehousePref-journal-detail-detail-submit", [PartnerWarehouseJournalController::class, 'detailWarehouseJournalDetailSubmit']);

    Route::get("$warehousePref-journal-monthly", [PartnerWarehouseJournalSummaryController::class, 'indexWarehouseJournalMonthly']);
    Route::get("xml-$warehousePref-journal-monthly", [PartnerWarehouseJournalSummaryController::class, 'indexWarehouseJournalMonthlyXml']);
    Route::get("$warehousePref-journal-monthly-detail", [PartnerWarehouseJournalSummaryController::class, 'detailWarehouseJournalMonthly']);
    Route::get("xml-$warehousePref-journal-monthly-detail", [PartnerWarehouseJournalSummaryController::class, 'detailWarehouseJournalMonthlyXml']);
    Route::get("$warehousePref-journal-monthly-detail-detail", [PartnerWarehouseJournalSummaryController::class, 'detailWarehouseJournalMonthlyDetail']);
    Route::get("xml-$warehousePref-journal-monthly-detail-detail", [PartnerWarehouseJournalSummaryController::class, 'detailWarehouseJournalMonthlyDetailXml']);
    Route::get("$warehousePref-journal-annually", [PartnerWarehouseJournalSummaryController::class, 'indexWarehouseJournalAnnually']);
    Route::get("xml-$warehousePref-journal-annually", [PartnerWarehouseJournalSummaryController::class, 'indexWarehouseJournalAnnuallyXml']);
    Route::get("$warehousePref-journal-annually-detail", [PartnerWarehouseJournalSummaryController::class, 'detailWarehouseJournalAnnually']);
    Route::get("xml-$warehousePref-journal-annually-detail", [PartnerWarehouseJournalSummaryController::class, 'detailWarehouseJournalAnnuallyXml']);
    Route::get("$warehousePref-journal-annually-detail-detail", [PartnerWarehouseJournalSummaryController::class, 'detailWarehouseJournalAnnuallyDetail']);
    Route::get("xml-$warehousePref-journal-annually-detail-detail", [PartnerWarehouseJournalSummaryController::class, 'detailWarehouseJournalAnnuallyDetailXml']);

    Route::get("$warehousePref-audit", [PartnerWarehouseAuditController::class, 'indexWarehouseAudit']);
    Route::get("xml-$warehousePref-audit", [PartnerWarehouseAuditController::class, 'indexWarehouseAuditXml']);
    Route::get("$warehousePref-audit-new", [PartnerWarehouseAuditController::class, 'newWarehouseAudit']);
    Route::post("$warehousePref-audit-new-submit", [PartnerWarehouseAuditController::class, 'newWarehouseAuditSubmit']);
    Route::get("$warehousePref-audit-detail", [PartnerWarehouseAuditController::class, 'detailWarehouseAudit']);
    Route::get("$warehousePref-audit-get-warehouse", [PartnerWarehouseAuditController::class, 'getGudangWarehouseAudit']);

    // --- Stock Opname ---
    Route::get("$warehousePref-stock-opname",              [PartnerWarehouseStockOpnameController::class, 'indexStockOpname']);
    Route::get("$warehousePref-stock-opname-data",         [PartnerWarehouseStockOpnameController::class, 'getStockOpnameData']);
    Route::get("$warehousePref-stock-opname-new",          [PartnerWarehouseStockOpnameController::class, 'createStockOpname']);
    Route::post("$warehousePref-stock-opname-submit",      [PartnerWarehouseStockOpnameController::class, 'submitStockOpname']);
    Route::get("$warehousePref-stock-opname-detail",       [PartnerWarehouseStockOpnameController::class, 'detailStockOpname']);
    Route::post("$warehousePref-stock-opname-update",      [PartnerWarehouseStockOpnameController::class, 'updateStockOpname']);
    Route::post("$warehousePref-stock-opname-finalize",    [PartnerWarehouseStockOpnameController::class, 'finalizeStockOpname']);
    Route::post("$warehousePref-stock-opname-action",     [PartnerWarehouseStockOpnameController::class, 'actionAdjustment']);
    Route::post("$warehousePref-audit-get-nomor", [PartnerWarehouseAuditController::class, 'getNomorWarehouseAudit']);
    Route::post("$warehousePref-stock-opname-add-adjustment", [PartnerWarehouseStockOpnameController::class, 'addAdjustment']);
    Route::get("$warehousePref-stock-opname-adjustment-data",  [PartnerWarehouseStockOpnameController::class, 'getOpnameAdjustments']);

    Route::post("$warehousePref-audit-get-temuan", [PartnerWarehouseAuditController::class, 'getTemuan']);

    $correctivePref = "$warehousePref-corrective-action";
    Route::get($correctivePref, [PartnerWarehouseCorrectiveController::class, 'indexCorrective']);
    Route::get("xml-$correctivePref", [PartnerWarehouseCorrectiveController::class, 'indexCorrectiveXml']);
    Route::get("$correctivePref-detail", [PartnerWarehouseCorrectiveController::class, 'detailCorrective']);
    Route::get("xml-$correctivePref-detail", [PartnerWarehouseCorrectiveController::class, 'detailCorrectiveXml']);
    Route::get("$correctivePref-detail-corrective", [PartnerWarehouseCorrectiveController::class, 'detailCorrectiveDetail']);
    Route::post("$correctivePref-detail-submit", [PartnerWarehouseCorrectiveController::class, 'detailCorrectiveDetailSubmit']);
    /** */

    /** audit category route */
    $masterAuditPref = "partner-master-audit";
    Route::get("$masterAuditPref-category", [PartnerMasterAuditController::class, 'indexMasterAuditCategory']);
    Route::get("$masterAuditPref-category-new", [PartnerMasterAuditController::class, 'newMasterAuditCategory']);
    Route::post("$masterAuditPref-category-new-submit", [PartnerMasterAuditController::class, 'newMasterAuditCategorySubmit']);
    Route::get("$masterAuditPref-category-detail", [PartnerMasterAuditController::class, 'detailMasterAuditCategory']);
    Route::post("$masterAuditPref-category-detail-submit", [PartnerMasterAuditController::class, 'detailMasterAuditCategorySubmit']);
    Route::get("xml-$masterAuditPref-category", [PartnerMasterAuditController::class, 'indexMasterAuditCategoryXml']);
    Route::get("$masterAuditPref-parameter", [PartnerMasterAuditController::class, 'indexMasterAuditParameter']);
    Route::get("xml-$masterAuditPref-parameter", [PartnerMasterAuditController::class, 'indexMasterAuditParameterXml']);
    Route::get("$masterAuditPref-parameter-new", [PartnerMasterAuditController::class, 'newMasterAuditParameter']);
    Route::post("$masterAuditPref-parameter-new-submit", [PartnerMasterAuditController::class, 'newMasterAuditParameterSubmit']);
    Route::get("$masterAuditPref-parameter-detail", [PartnerMasterAuditController::class, 'detailMasterAuditParameter']);
    Route::post("$masterAuditPref-parameter-detail-submit", [PartnerMasterAuditController::class, 'newMasterAuditParameterSubmit']);
    Route::post("$masterAuditPref-parameter-change-status", [PartnerMasterAuditController::class, 'changeStatusMasterAuditParamater']);
    Route::post("$masterAuditPref-parameter-delete", [PartnerMasterAuditController::class, 'deleteMasterAuditParameter']);

    /** sop compliance route */
    $sopPref = "partner-master-sop";
    Route::get($sopPref, [PartnerMasterSOPController::class, 'indexSOP']);

    /** chat route */
    $chatPref = "partner-chat";
    Route::get("$chatPref-list", [PartnerChatListController::class, 'chatList']);
    Route::get("$chatPref-messages-{conversation}", [PartnerChatListController::class, 'getMessages']);
    Route::post("$chatPref-send", [PartnerChatListController::class, 'sendMessage']);
    Route::post("$chatPref-fetch-new", [PartnerChatListController::class, 'fetchNewMessages']);
    Route::post("$chatPref-load-older", [PartnerChatListController::class, 'loadOlder']);
    Route::post("$chatPref-mark-read", [PartnerChatListController::class, 'markRead']);
    Route::post("$chatPref-check-read", [PartnerChatListController::class, 'checkRead']);

    /** chat route */
    $announcementPref = "partner-announcement";
    Route::get("$announcementPref", [PartnerAnnouncementController::class, 'partnerAnnouncement']);
    Route::get("xml-$announcementPref", [PartnerAnnouncementController::class, 'partnerAnnouncementXml']);
    Route::get("$announcementPref-new", [PartnerAnnouncementController::class, 'partnerAnnouncementNew']);
    Route::post("$announcementPref-new-submit", [PartnerAnnouncementController::class, 'partnerAnnouncementNewSubmit']);
    Route::get("$announcementPref-detail", [PartnerAnnouncementController::class, 'partnerAnnouncementDetail']);
    Route::post("$announcementPref-detail-submit", [PartnerAnnouncementController::class, 'partnerAnnouncementDetailSubmit']);

    /** ticket route */
    $ticketPref = "partner-ticket";
    Route::get("$ticketPref-new", [PartnerTicketController::class, 'ticketNew']);
    Route::post("$ticketPref-new-submit", [PartnerTicketController::class, 'ticketNewSubmit']);
    Route::get("$ticketPref-open", [PartnerTicketController::class, 'ticketOpen']);
    Route::get("xml-$ticketPref-open", [PartnerTicketController::class, 'ticketOpenXml']);
    Route::get("$ticketPref-open-detail", [PartnerTicketController::class, 'ticketOpenDetail']);
    Route::get("xml-$ticketPref-open-detail", [PartnerTicketController::class, 'ticketOpenDetailXml']);
    Route::post("$ticketPref-open-detail-submit", [PartnerTicketController::class, 'ticketOpenDetailSubmit']);
    Route::get("$ticketPref-history", [PartnerTicketController::class, 'ticketHistory']);
    Route::get("xml-$ticketPref-history", [PartnerTicketController::class, 'ticketHistoryXml']);
    Route::get("$ticketPref-history-detail", [PartnerTicketController::class, 'ticketHistoryDetail']);
    Route::get("xml-$ticketPref-history-detail", [PartnerTicketController::class, 'ticketHistoryDetailXml']);

    /** broadcast route */
    $broadcastPref = "broadcast";
    Route::get("$broadcastPref-index", [BroadcastController::class, 'index']);
    Route::get("$broadcastPref-detail", [BroadcastController::class, 'detail']);
    Route::get("$broadcastPref-create", [BroadcastController::class, 'create']);
    Route::post("$broadcastPref-submit", [BroadcastController::class, 'submit']);
    Route::get("$broadcastPref-load-data", [BroadcastController::class, 'indexXML']);
    Route::get("$broadcastPref-detail-data", [BroadcastController::class, 'detailData']);
    /** */

    /** panduan operasional route */
    $panduanOpsPref = "panduan";
    Route::get("$panduanOpsPref-index", [OperationalController::class, 'index']);
    Route::get("$panduanOpsPref-create", [OperationalController::class, 'create']);
    Route::get("$panduanOpsPref-detail", [OperationalController::class, 'detail']);
    Route::post("$panduanOpsPref-submit", [OperationalController::class, 'submit']);
    Route::get("$panduanOpsPref-load-data", [OperationalController::class, 'loadXML']);
    /** */

    /**  notification route */
    $notification = "notification";
    Route::get("$notification-getall", [NotificationController::class, 'getAll']);
    Route::post("$notification-read", [NotificationController::class, 'markAsRead']);
    Route::get("$notification-remind-contract-expiration", [NotificationController::class, 'reminderContractExpiration']);
    /** */

    /** projection route */
    $projection = "projection";
    $expenditure = "expenditure";
    Route::get("$projection-index", [PartnerProfitSharingController::class, 'indexProjection']);
    Route::get("$projection-create", [PartnerProfitSharingController::class, 'createProjection']);
    Route::get("$projection-data-projection", [PartnerProfitSharingController::class, 'projectionData']);
    Route::post("$projection-upload", [PartnerProfitSharingController::class, 'createMitraProjection']);
    Route::get("$projection-template", [PartnerProfitSharingController::class, 'downloadProjectionTemplate']);
    Route::get("$expenditure-data-expenditure", [PartnerProfitSharingController::class, 'expenditureData']);

    /** Capital expenditure  */
    $capexPrefs = "partner-capex";
    Route::get("$capexPrefs-index", [ExpenditureController::class, 'indexCapitalExpenditure']);
    Route::get("$capexPrefs-create", [ExpenditureController::class, 'createCapitalExpenditure']);
    Route::get("$capexPrefs-detail", [ExpenditureController::class, 'getCapexDetail']);
    Route::post("$capexPrefs-submit", [ExpenditureController::class, 'submitCapitalExpenditure']);
    Route::post("$capexPrefs-submit-edit", [ExpenditureController::class, 'editCapitalExpenditure']);
    Route::post("$capexPrefs-delete", [ExpenditureController::class, 'deleteCapitalExpenditure']);
    Route::get("$capexPrefs-data", [ExpenditureController::class, 'getCapitalExpenditureData']);
    /** */


    /** profit sharing */
    $profitSharingPref = 'partner-profit-sharing';
    Route::get("$profitSharingPref", [PartnerProfitSharingController::class, 'indexProfitSharing']);
    Route::get("xml-$profitSharingPref", [PartnerProfitSharingController::class, 'indexProfitSharingXml']);
    Route::get("$profitSharingPref-lk-monthly", [PartnerProfitSharingController::class, 'lkMonthlyProfitSharing']);
    Route::get("xml-$profitSharingPref-lk-monthly", [PartnerProfitSharingController::class, 'lkProfitSharingXml']);
    Route::get("test-xml-$profitSharingPref-lk-monthly", [PartnerProfitSharingController::class, 'monthlyFinancialStatementCollection']);
    Route::get("$profitSharingPref-lk-annually", [PartnerProfitSharingController::class, 'lkAnnuallyProfitSharing']);
    Route::get("xml-$profitSharingPref-lk-annually", [PartnerProfitSharingController::class, 'lkProfitSharingXml']);
    Route::get("$profitSharingPref-overview", [PartnerProfitSharingController::class, 'overviewProfitSharing']);
    Route::get("$profitSharingPref-overview-user", [PartnerProfitSharingController::class, 'overviewUserProfitSharing']);
    Route::get("xml-$profitSharingPref-overview", [PartnerProfitSharingController::class, 'overviewProfitSharingXml']);
    Route::get("$profitSharingPref-download", [PartnerProfitSharingController::class, 'downloadProfitSharingApplication']);
    Route::post("$profitSharingPref-submit-application", [PartnerProfitSharingController::class, 'partnerSubmitProfitSharingApplication']);

    Route::get("$profitSharingPref-overview2", [PartnerProfitSharingController::class, 'indexProfitSharingOverview2']);
    Route::get("xml-$profitSharingPref-overview2", [PartnerProfitSharingController::class, 'indexProfitSharingOverview2Xml']);
    Route::get("$profitSharingPref-management-fee", [PartnerProfitSharingController::class, 'indexProfitSharingManagementFee']);
    Route::get("xml-$profitSharingPref-management-fee", [PartnerProfitSharingController::class, 'indexProfitSharingManagementFeeXml']);

    Route::get("$profitSharingPref-mitra-monthly", [PartnerProfitSharingController::class, 'monthlyMitraprofitSharing']);
    Route::get("$profitSharingPref-mitra-annually", [PartnerProfitSharingController::class, 'annuallyMitraprofitSharing']);

    Route::get("$profitSharingPref-overview-mitra", [PartnerProfitSharingController::class, 'indexOverviewMitraProfitSharing']);
    Route::get("xml-$profitSharingPref-overview-mitra", [PartnerProfitSharingController::class, 'indexOverviewMitraProfitSharingXml']);
    Route::get("$profitSharingPref-overview-detail", [PartnerProfitSharingController::class, 'detailOverviewMitraProfitSharing']);

    Route::get("$profitSharingPref-dashboard", [PartnerProfitSharingController::class, 'dashboardProfitSharing']);
    Route::get("xml-$profitSharingPref-dashboard", [PartnerProfitSharingController::class, 'dashboardProfitSharingXml']);

    Route::get("$profitSharingPref-data", [PartnerProfitSharingController::class, 'getProfitSharingApprovalData']);
    Route::get("$profitSharingPref-data-log", [PartnerProfitSharingController::class, 'getProfitSharingApprovalDataLog']);

    // Route::post("$profitSharingPref-proceed", [ApprovalController::class, 'processProfitSharingToNextProgress']);
    Route::get("$profitSharingPref-approval-index", [ApprovalController::class, 'indexProfitSharing']);
    Route::get("$profitSharingPref-approval-create", [ApprovalController::class, 'createProfitSharingApplication']);
    Route::get("$profitSharingPref-approval-detail", [ApprovalController::class, 'getProfitSharingApprovalDetail']);
    Route::post("$profitSharingPref-approve", [ApprovalController::class, 'processProfitSharingToNextProgress']);
    Route::post("$profitSharingPref-preview-application", [PartnerProfitSharingController::class, 'previewProfitSharingApplication']);
    Route::post("$profitSharingPref-submit-application", [PartnerProfitSharingController::class, 'finalizeProfitSharingApplication']);

    Route::get("$profitSharingPref-royalty-index", [PartnerProfitSharingController::class, 'indexRoyaltyPayment']);
    Route::get("$profitSharingPref-royalty-data", [PartnerProfitSharingController::class, 'getRoyaltyPaymentData']);
    Route::post("$profitSharingPref-royalty-update", [PartnerProfitSharingController::class, 'updateRoyaltyPaymentData']);
    // Route::post("$profitSharingPref-royalty-submit", [PartnerProfitSharingController::class, 'submitRoyaltyPayment']);
    // Route::post("$profitSharingPref-send-notification", [NotificationController::class, 'sendRoyaltyNotification']);

    /**master data route */
    $partner = "partner";

    Route::get("$partner-data-active-mitra", [UserController::class, 'getPartner']);
    Route::get("$partner-data-sales", [UserController::class, 'getSalesperson']);
    Route::get("my-profile", [UserController::class, 'profilePage']);
    Route::get("$partner-profile-data", [UserController::class, 'getMitraData']);
    Route::post("$partner-profile-update", [UserController::class, 'updateMitraData']);

    $cns = "cns";
    Route::get("$cns-data-police-license", [UCOLogisticController::class, 'getPoliceLicenseNumber']);



    /** renewal  */
    $partnerRenewalPrefs = "partner-renewal";
    Route::get("$partnerRenewalPrefs-renewal-index", [RenewalController::class, 'indexRenewal']);
    Route::get("$partnerRenewalPrefs-data", [RenewalController::class, 'getRenewalData']);
    Route::get("$partnerRenewalPrefs-data-log", [PartnerProfitSharingController::class, 'getProfitSharingApprovalDataLog']);
    // Route::get("$partnerRenewalPrefs-termination-index", [RenewalController::class, 'indexTermination']);
    Route::get("$partnerRenewalPrefs-transfer-index", [RenewalController::class, 'indexTransferMitra']);
    Route::get("$partnerRenewalPrefs-approval-index", [RenewalController::class, 'indexApproval']);
    Route::get("$partnerRenewalPrefs-approval-detail", [RenewalController::class, 'getRenewalDetail']);
    Route::get("$partnerRenewalPrefs-approval-create", [RenewalController::class, 'createApproval']);
    Route::post("$partnerRenewalPrefs-approval-approve", [ApprovalController::class, 'processRenewalToNextProgress']);
    Route::get("$partnerRenewalPrefs-download", [RenewalController::class, 'downloadRenewalApplication']);
    Route::post("$partnerRenewalPrefs-submit-application", [RenewalController::class, 'uploadRenewalApplication']);

    /** exit/terminasi */
    $partnerTerminationPrefs = "partner-termination";
    Route::get("$partnerTerminationPrefs-index", [RenewalController::class, 'indexTermination']);
    Route::get("$partnerTerminationPrefs-data", [RenewalController::class, 'getTerminationData']);
    Route::get("$partnerTerminationPrefs-download", [RenewalController::class, 'downloadTerminationApplication']);
    Route::post("$partnerTerminationPrefs-submit-application", [RenewalController::class, 'uploadTerminationApplication']);
    Route::get("$partnerTerminationPrefs-approval-index", [RenewalController::class, 'indexTerminationApproval']);
    Route::get("$partnerTerminationPrefs-approval-create", [RenewalController::class, 'createTerminationApproval']);
    Route::get("$partnerTerminationPrefs-approval-data", [ApprovalController::class, 'getApprovalData']);
    Route::get("$partnerTerminationPrefs-approval-data-log", [ApprovalController::class, 'getApprovalDataLog']);
    Route::post("$partnerTerminationPrefs-approval-approve", [ApprovalController::class, 'processTerminationToNextProgress']);

    Route::get("$partnerTerminationPrefs-approval-detail", [RenewalController::class, 'getTerminationApprovalDetail']);
    /** transfer kepemilikan kemitraan */
    $partnerTransferPrefs = "partner-transfer-membership";
    Route::get("$partnerTransferPrefs-index", [RenewalController::class, 'indexTransferMembership']);
    Route::get("$partnerTransferPrefs-create-application", [RenewalController::class, 'createTransferMembershipApplication']);
    Route::get("$partnerTransferPrefs-data", [RenewalController::class, 'getTransferMembershipData']);
    Route::post("$partnerTransferPrefs-update-application", [RenewalController::class, 'updateTransferMembershipApplication']);
    Route::post("$partnerTransferPrefs-preview-application", [RenewalController::class, 'previewTransferMembershipApplication']);
    Route::post("$partnerTransferPrefs-finalize-application", [RenewalController::class, 'finalizeTransferMembershipApplication']);
    Route::get("$partnerTransferPrefs-approval-index", [ApprovalController::class, 'indexTransferMembershipApproval']);
    Route::get("$partnerTransferPrefs-approval-create", [ApprovalController::class, 'createTransferMembershipApproval']);
    Route::get("$partnerTransferPrefs-approval-data", [ApprovalController::class, 'getApprovalData']);
    Route::get("$partnerTransferPrefs-approval-data-log", [ApprovalController::class, 'getApprovalDataLog']);
    Route::post("$partnerTransferPrefs-approval-approve", [ApprovalController::class, 'processTransferMembershipToNextProgress']);
    Route::get("$partnerTransferPrefs-approval-detail", [ApprovalController::class, 'getTransferMembershipApprovalDetail']);


    /** OnboardingController */
    $onboardingPrefs = "partner-onboarding";
    Route::get("$onboardingPrefs-approval-data", [OnboardingController::class, 'getOnboardingApprovalData']);
    Route::post("$onboardingPrefs-approval-submit", [OnboardingController::class, 'processOnboardingApproval']);
    Route::get("$onboardingPrefs-approval-data-log", [OnboardingController::class, 'getOnboardingApprovalDataLog']);
    Route::post("$onboardingPrefs-preview-mou", [OnboardingController::class, 'previewMou']);
    Route::post("$onboardingPrefs-submit-mou", [OnboardingController::class, 'uploadMou']);
    Route::post("$onboardingPrefs-preview-pks", [OnboardingController::class, 'previewPks']);
    Route::post("$onboardingPrefs-submit-pks", [OnboardingController::class, 'uploadPks']);
    Route::post("$onboardingPrefs-force-approve", [OnboardingController::class, 'forceApprove']);

    /** Partner Sales */
    $salesPrefs = "partner-sales";
    Route::get("$salesPrefs-master-target", [PartnerSalesController::class, 'masterTarget']);
    Route::get("xml-$salesPrefs-master-target", [PartnerSalesController::class, 'masterTargetXml']);
    Route::post("$salesPrefs-master-target-update", [PartnerSalesController::class, 'masterTargetUpdate']);
    Route::get("$salesPrefs-tiering", [PartnerSalesController::class, 'tieringSales']);
    Route::get("xml-$salesPrefs-tiering", [PartnerSalesController::class, 'tieringSalesXml']);
    Route::get("$salesPrefs-team-performance", [PartnerSalesController::class, 'teamPerformanceSales']);
    Route::get("xml-$salesPrefs-team-performance", [PartnerSalesController::class, 'teamPerformanceSalesXml']);
    Route::get("$salesPrefs-pipeline-leads", [PartnerSalesController::class, 'pipelineLeads']);
    Route::get("xml-$salesPrefs-pipeline-leads", [PartnerSalesController::class, 'pipelineLeadsXml']);
    Route::get("$salesPrefs-ratio-pipeline-leads", [PartnerSalesController::class, 'ratioPipelineLeads']);
    Route::get("xml-$salesPrefs-ratio-pipeline-leads", [PartnerSalesController::class, 'ratioPipelineLeadsXml']);
    Route::get("$salesPrefs-ratio-pipeline-deal", [PartnerSalesController::class, 'ratioPipelineDeal']);
    Route::get("xml-$salesPrefs-ratio-pipeline-deal", [PartnerSalesController::class, 'ratioPipelineDealXml']);
    Route::get("$salesPrefs-aftersales-profit-mitra", [PartnerSalesController::class, 'afterSalesProfitMitra']);
    Route::get("xml-$salesPrefs-aftersales-profit-mitra", [PartnerSalesController::class, 'afterSalesProfitMitraXmlV2']);
    Route::get("test-xml-$salesPrefs-aftersales-profit-mitra", [PartnerSalesController::class, 'afterSalesProfitMitraCollection']);
    Route::get("$salesPrefs-aftersales-handling-complain", [PartnerSalesController::class, 'afterSalesHandlingComplain']);
    Route::get("xml-$salesPrefs-aftersales-handling-complain", [PartnerSalesController::class, 'afterSalesHandlingComplainXml']);
    Route::get("$salesPrefs-performance/monthly-update", [PartnerSalesController::class, 'updateAllSalesPerformance']);


    /** Survey controller */
    $surveyPrefs = "partner-survey";
    Route::get("$surveyPrefs-dashboard", [SurveyController::class, 'dashboardSurvey']);
    Route::get("$surveyPrefs-index", [SurveyController::class, 'indexSurvey']);
    Route::get("$surveyPrefs-create", [SurveyController::class, 'createMasterDataSurvey']);
    Route::get("$surveyPrefs-create-reply", [SurveyController::class, 'createReplyMasterDataSurvey']);
    Route::get("$surveyPrefs-data-history", [SurveyController::class, "getSurveyHistoryData"]);
    Route::get("$surveyPrefs-data", [SurveyController::class, 'getMasterDataSurvey']);
    Route::post("$surveyPrefs-submit", [SurveyController::class, 'submitMasterDataSurvey']);
    Route::post("$surveyPrefs-update", [SurveyController::class, "updateMasterDataSurvey"]);
    Route::post("$surveyPrefs-delete", [SurveyController::class, "deleteMasterDataSurvey"]);
    Route::get("$surveyPrefs-approval-index", [SurveyController::class, 'indexApprovalSurvey']);
    Route::get("$surveyPrefs-approval-data", [ApprovalController::class, 'getApprovalData']);
    Route::get("$surveyPrefs-approval-data-log", [ApprovalController::class, 'getApprovalDataLog']);
    Route::post("$surveyPrefs-approval-submit", [ApprovalController::class, 'processSurveyToNextProgress']);

    /** Perhitungan Valuasi */
    $valuationPrefs = "partner-valuation";
    Route::get("$valuationPrefs", [PartnerValuationController::class, 'indexValuation']);
    Route::get("xml-$valuationPrefs", [PartnerValuationController::class, 'indexValuationXml']);
    Route::get("$valuationPrefs-detail", [PartnerValuationController::class, 'detailValuation']);
    Route::get("xml-$valuationPrefs-detail", [PartnerValuationController::class, 'detailValuationXml']);
    Route::get("xml-$valuationPrefs-detail-aggregate", [PartnerValuationController::class, 'detailAggregateValuationXml']);
    Route::get("$valuationPrefs-summary", [PartnerValuationController::class, 'summaryValuation']);
    Route::get("xml-$valuationPrefs-summary", [PartnerValuationController::class, 'summaryValuationXml']);

    /** Training (LMS) */
    $masterTrainingPref = "partner-master-training";
    Route::get("$masterTrainingPref", [PartnerMasterTrainingController::class, 'indexTraining']);
    Route::get("xml-$masterTrainingPref", [PartnerMasterTrainingController::class, 'indexTrainingXml']);
    Route::post("$masterTrainingPref-update", [PartnerMasterTrainingController::class, 'updateTraining']);

    $masterMaterialPref = "partner-master-material";
    Route::get("$masterMaterialPref", [PartnerMasterMaterialController::class, 'indexMaterial']);
    Route::get("xml-$masterMaterialPref", [PartnerMasterMaterialController::class, 'indexMaterialXml']);
    Route::get("$masterMaterialPref-new", [PartnerMasterMaterialController::class, 'newMaterial']);
    Route::get("xml-$masterMaterialPref-user", [PartnerMasterMaterialController::class, 'userMaterialXml']);
    Route::post("$masterMaterialPref-new-submit", [PartnerMasterMaterialController::class, 'newMaterialSubmit']);
    Route::get("$masterMaterialPref-detail", [PartnerMasterMaterialController::class, 'detailMaterial']);
    Route::get("xml-$masterMaterialPref-detail-user", [PartnerMasterMaterialController::class, 'detailMaterialXml']);
    Route::post("$masterMaterialPref-detail-submit", [PartnerMasterMaterialController::class, 'detailMaterialSubmit']);
    Route::get("$masterMaterialPref-detail", [PartnerMasterMaterialController::class, 'detailMaterial']);
    Route::post("$masterMaterialPref-detail-submit", [PartnerMasterMaterialController::class, 'detailMaterialSubmit']);

    $masterSatisficationPref = "partner-master-satisfication";
    Route::get("$masterSatisficationPref", [PartnerMasterSatisficationController::class, 'indexSatisfication']);
    Route::get("xml-$masterSatisficationPref", [PartnerMasterSatisficationController::class, 'indexSatisficationXml']);
    Route::post("$masterSatisficationPref-update", [PartnerMasterSatisficationController::class, 'updateSatisfication']);

    $trainingPref = "partner-training";
    Route::get("$trainingPref", [PartnerTrainingController::class, 'indexTraining']);
    Route::get("xml-$trainingPref", [PartnerTrainingController::class, 'indexTrainingXml']);
    Route::get("$trainingPref-new", [PartnerTrainingController::class, 'newTraining']);
    Route::post("$trainingPref-new-submit", [PartnerTrainingController::class, 'newTraining']);
    Route::get("$trainingPref-detail", [PartnerTrainingController::class, 'detailTraining']);
    Route::get("$trainingPref-approval", [PartnerTrainingController::class, 'approvalTraining']);
    Route::post("$trainingPref-get-speaker", [PartnerTrainingController::class, 'getSpeaker']);
    Route::get("xml-$trainingPref-approval", [PartnerTrainingController::class, 'approvalTrainingXml']);
    Route::get("$trainingPref-approval-detail", [PartnerTrainingController::class, 'approvalTrainingDetail']);
    Route::post("$trainingPref-approval-detail-submit", [PartnerTrainingController::class, 'approvalTrainingDetailSubmit']);
    Route::get("$trainingPref-list", [PartnerTrainingController::class, 'listTraining']);
    Route::get("xml-$trainingPref-list", [PartnerTrainingController::class, 'listTrainingXml']);
    Route::get("$trainingPref-list-detail", [PartnerTrainingController::class, 'listTrainingDetail']);
    Route::get("xml-$trainingPref-list-detail-participant", [PartnerTrainingController::class, 'listTrainingDetailParticipantXml']);
    Route::get("xml-$trainingPref-list-detail-material", [PartnerTrainingController::class, 'listTrainingDetailMaterialXml']);
    Route::get("$trainingPref-attend", [PartnerTrainingController::class, 'attendTraining']);
    Route::get("xml-$trainingPref-attend", [PartnerTrainingController::class, 'attendTrainingXml']);
    Route::get("$trainingPref-attend-presence", [PartnerTrainingController::class, 'attendTrainingPrecense']);
    Route::get("$trainingPref-assignment", [PartnerTrainingController::class, 'assignmentTraining']);
    Route::get("xml-$trainingPref-assignment", [PartnerTrainingController::class, 'assignmentTrainingXml']);
    Route::get("$trainingPref-create", [PartnerTrainingController::class, 'createTraining']);
    Route::post("$trainingPref-create-submit", [PartnerTrainingController::class, 'createTrainingSubmit']);
    Route::get("$trainingPref-application", [PartnerTrainingController::class, 'applicationTraining']);
    Route::post("$trainingPref-application-submit", [PartnerTrainingController::class, 'applicationTrainingSubmit']);
    Route::get("$trainingPref-satisfication", [PartnerTrainingController::class, 'satisficationTraining']);
    Route::get("xml-$trainingPref-satisfication", [PartnerTrainingController::class, 'satisficationTrainingXml']);
    Route::get("$trainingPref-satisfication-new", [PartnerTrainingController::class, 'satisficationTrainingNew']);
    Route::post("$trainingPref-satisfication-new-submit", [PartnerTrainingController::class, 'satisficationTrainingNewSubmit']);
    Route::get("$trainingPref-satisfication-detail", [PartnerTrainingController::class, 'satisficationTrainingDetail']);
    Route::get("$trainingPref-satisfication-manager", [PartnerTrainingController::class, 'satisficationManagerTraining']);
    Route::get("xml-$trainingPref-satisfication-manager", [PartnerTrainingController::class, 'satisficationManagerTrainingXml']);
    Route::get("$trainingPref-satisfication-manager-new", [PartnerTrainingController::class, 'satisficationManagerTrainingNew']);
    Route::post("$trainingPref-satisfication-manager-new-submit", [PartnerTrainingController::class, 'satisficationManagerTrainingNewSubmit']);
    Route::get("$trainingPref-satisfication-manager-detail", [PartnerTrainingController::class, 'satisficationManagerTrainingDetail']);
    Route::get("$trainingPref-certificate-signature", [PartnerTrainingController::class, 'certificateTrainingSignature']);
    Route::post("$trainingPref-certificate-signature-submit", [PartnerTrainingController::class, 'certificateTrainingSignatureSubmit']);
    Route::get("$trainingPref-certificate", [PartnerTrainingController::class, 'certificateTraining']);
    Route::get("xml-$trainingPref-certificate", [PartnerTrainingController::class, 'certificateTrainingXml']);
    Route::get("$trainingPref-certificate-print", [PartnerTrainingController::class, 'certificateTrainingPrint']);
    Route::get("$trainingPref-certificate-mentor", [PartnerTrainingController::class, 'certificateMentorTraining']);
    Route::get("xml-$trainingPref-certificate-mentor", [PartnerTrainingController::class, 'certificateMentorTrainingXml']);
    Route::get("$trainingPref-certificate-mentor-print", [PartnerTrainingController::class, 'certificateMentorTrainingPrint']);
    Route::get("$trainingPref-library", [PartnerTrainingController::class, 'libraryTraining']);
    Route::get("xml-$trainingPref-library", [PartnerTrainingController::class, 'libraryTrainingXml']);
    Route::get("$trainingPref-report-mitra", [PartnerTrainingController::class, 'reportMitraTraining']);
    Route::get("xml-$trainingPref-report-mitra", [PartnerTrainingController::class, 'reportMitraTrainingXml']);
    Route::get("$trainingPref-mail", [PartnerTrainingController::class, 'mailTraining']);

    /** Marketing & Branding */
    $marketingPrefs = "partner-branding";
    Route::get("$marketingPrefs-marketing-index", [MarketingController::class, 'indexMarketing']);
    Route::get("$marketingPrefs-marketing-recap-data", [MarketingController::class, 'getRecapMarketingData']);
    Route::get("$marketingPrefs-marketing-recap-index", [MarketingController::class, 'indexMarketingRecap']);
    Route::get("$marketingPrefs-promotion-index", [MarketingController::class, 'indexPromotion']);
    Route::get("$marketingPrefs-marketing-data", [MarketingController::class, 'selectMarketingData']);
    Route::get("$marketingPrefs-promotion-data", [MarketingController::class, 'getPromotionData']);
    Route::get("$marketingPrefs-promotion-generate-rab", [MarketingController::class, 'generateVoucherRAB']);
    Route::get("$marketingPrefs-marketing-create", [MarketingController::class, 'createMarketingData']);
    Route::post("$marketingPrefs-marketing-submit", [MarketingController::class, 'submitMarketing']);
    Route::put("$marketingPrefs-marketing-update", [MarketingController::class, 'updateMarketingData']);
    Route::get("$marketingPrefs-promotion-create", [MarketingController::class, 'createPromotionData']);
    Route::post("$marketingPrefs-promotion-submit", [MarketingController::class, 'submitPromotion']);
    Route::get("$marketingPrefs-monitoring-index", [MarketingController::class, 'indexMonitoring']);
    Route::get("$marketingPrefs-monitoring-create", [MarketingController::class, 'createMonitoringData']);
    Route::get("$marketingPrefs-monitoring-data", [MarketingController::class, 'getMonitoringPromotionData']);
    Route::post("$marketingPrefs-monitoring-submit", [MarketingController::class, 'submitMonitoring']);
    Route::post("$marketingPrefs-followup-submit", [MarketingController::class, 'followupPromotion']);
    Route::get("$marketingPrefs-approval-index", [ApprovalController::class, 'indexMarketingApproval']);
    Route::get("$marketingPrefs-approval-index", [ApprovalController::class, 'indexMarketingApproval']);
    Route::post("$marketingPrefs-approval-submit", [ApprovalController::class, 'processPromotionApproval']);
    Route::get("$marketingPrefs-evaluation-index", [MarketingController::class, 'indexEvaluation']);
    Route::get("$marketingPrefs-evaluation-data", [MarketingController::class, 'getEvaluationData']);
    Route::post("$marketingPrefs-evaluation-submit", [MarketingController::class, 'submitPromotionEvaluation']);
    Route::get("$marketingPrefs-export", [MarketingController::class, 'exportMarketingData']);


    $partnerCompliance = "partner-compliance";
    Route::get("$partnerCompliance-tax-index", [ComplianceController::class, 'indexTax']);
    Route::post("$partnerCompliance-tax-update", [ComplianceController::class, 'updateTaxCompliance']);
    Route::get("$partnerCompliance-legal-index", [ComplianceController::class, 'indexLegal']);
    Route::get("$partnerCompliance-tax-data", [ComplianceController::class, 'selectTaxComplianceData']);
    Route::get("$partnerCompliance-legal-data", [ComplianceController::class, 'selectLegalComplianceData']);
    Route::post("$partnerCompliance-legal-submit", [ComplianceController::class, 'inputLegalCompliance']);
    Route::get("$partnerCompliance-legal-create", [ComplianceController::class, 'createLegalCompliance']);


    $partnerUCO = "partner-uco";
    Route::get("$partnerUCO-instock", [UCOLogisticController::class, 'indexInboundUCO']);
    Route::post("$partnerUCO-instock-update", [UCOLogisticController::class, 'updateInstockPhoto']);
    Route::get("$partnerUCO-inbound-timbang-create", [UCOLogisticController::class, 'createInboundUCO']);
    Route::post("$partnerUCO-inbound-timbang-submit", [UCOLogisticController::class, 'submitInboundUCO']);
    Route::get("$partnerUCO-inbound-timbang-data", [UCOLogisticController::class, 'getUCOInboundXml']);

    Route::get("partner-uco-inbound-quality-check", function () {
        return view('uco.inbound-quality-check');
    });

    Route::get("partner-uco-unit-category", [UCOLogisticController::class, 'indexUnitCategory']);

    Route::get("$partnerUCO-outbound", [UCOLogisticController::class, 'indexOutboundUCO']);
    Route::get("$partnerUCO-outstock", [UCOLogisticController::class, 'indexOutstockUCO']);
    Route::get("$partnerUCO-outbound-data", [UCOLogisticController::class, 'getUCOOutboundXml']);


    Route::get("partner-uco-outbound-pickup-request", function () {
        return "OK";
    });

    Route::get("partner-uco-outbound-pickup-parameter", function () {
        return "OK";
    });

    Route::get("partner-uco-outbound-report-inbound-source", [UCOLogisticController::class, 'indexInboundSource']);

    Route::get("partner-uco-outbound-report-stock-trace", function () {
        return "OK";
    });

    Route::get("partner-uco-outbound-report-stock-reconciliation", function () {
        return "OK";
    });

    Route::get("partner-uco-outbound-report-stock-opname-adjustment", function () {
        return "OK";
    });


    $partnerkpi = "partner-performance";

    Route::get("$partnerkpi-kpi-index", [PerformanceController::class, 'indexKPI']);
    Route::get("$partnerkpi-kpi-data", [PerformanceController::class, 'getMasterKPIData']);
    Route::get("$partnerkpi-kpi-create", [PerformanceController::class, 'createMasterKPIData']);
    Route::get("$partnerkpi-kpi-update", [PerformanceController::class, 'updateMasterKPIDataPage']);
    Route::post("$partnerkpi-kpi-submit", [PerformanceController::class, 'submitMasterKPIData']);
    Route::post("$partnerkpi-kpi-submit-edit", [PerformanceController::class, 'updateMasterKPIData']);

    $partnerDashboard = "partner-dashboard";

    Route::get("$partnerDashboard-membership", [PartnerSalesController::class, 'membershipDashboardPage']);
    Route::get("$partnerDashboard-membership-data", [PartnerSalesController::class, 'membershipDashboardData']);
    Route::get("$partnerDashboard-financial-insight", [PartnerSalesController::class, 'financialInsightDashboardPage']);
    Route::get("$partnerDashboard-financial-insight-data", [PartnerSalesController::class, 'financialInsightDashboardData']);
    Route::get("$partnerDashboard-aftersales", [PartnerSalesController::class, 'aftersalesDashboardPage']);
    Route::get("$partnerDashboard-aftersales-data", [PartnerSalesController::class, 'aftersalesDashboardData']);
    Route::get("$partnerDashboard-compliance", [PartnerWarehouseCorrectiveController::class, 'complianceDashboardPage']);
    Route::get("$partnerDashboard-compliance-data", [PartnerWarehouseCorrectiveController::class, 'complianceDashboardData']);


    $vesselPref = "cns-vessel-schedule";
    Route::get("$vesselPref", [VesselScheduleController::class, "indexVesselSchedule"]);
    Route::get("xml-$vesselPref", [VesselScheduleController::class, "indexVesselScheduleXml"]);
    Route::get("$vesselPref-new", [VesselScheduleController::class, "newVesselSchedule"]);
    Route::post("$vesselPref-new-submit", [VesselScheduleController::class, "newVesselScheduleSubmit"]);
    Route::get("$vesselPref-detail", [VesselScheduleController::class, "detailVesselSchedule"]);
    Route::post("$vesselPref-detail-submit", [VesselScheduleController::class, "detailVesselScheduleSubmit"]);
    Route::get("xml-$vesselPref-detail-log", [VesselScheduleController::class, "detailLogVesselScheduleXml"]);

    $stuffPref = "cns-stuffing";
    Route::get("$stuffPref", [CNSStuffingController::class, "indexStuffing"]);
    Route::get("xml-$stuffPref", [CNSStuffingController::class, "indexStuffingXml"]);
    Route::get("$stuffPref-detail", [CNSStuffingController::class, "detailStuffing"]);
    Route::get("xml-$stuffPref-detail", [CNSStuffingController::class, "detailStuffingXml"]);
    Route::post("$stuffPref-detail-update", [CNSStuffingController::class, "updateDetailStuffing"]);
    Route::get("xml-$stuffPref-combo-police-no", [CNSStuffingController::class, "comboPoliceNumber"]);

    $stuffMonitoringPref = "cns-export-stuffing-monitoring";
    Route::get("$stuffMonitoringPref", [CNSStuffingMonitoringController::class, 'indexStuffingMonitoring']);
    Route::get("xml-$stuffMonitoringPref", [CNSStuffingMonitoringController::class, 'indexStuffingMonitoringXml']);
    Route::post("$stuffMonitoringPref-update", [CNSStuffingMonitoringController::class, 'updateStuffingMonitoring']);

    $masterCompanyPref = "cns-master-company";
    Route::get("$masterCompanyPref", [MasterCompanyController::class, "indexMasterCompany"]);
    Route::get("$masterCompanyPref-supplier", [MasterCompanyController::class, "getSupplierCompany"]);
    Route::get("$masterCompanyPref-buyer", [MasterCompanyController::class, "getBuyerCompany"]);
    Route::get("xml-$masterCompanyPref", [MasterCompanyController::class, "indexMasterCompanyXml"]);
    Route::get("$masterCompanyPref-new", [MasterCompanyController::class, "newMasterCompany"]);
    Route::post("$masterCompanyPref-new-submit", [MasterCompanyController::class, "newMasterCompanySubmit"]);
    Route::get("$masterCompanyPref-detail", [MasterCompanyController::class, "detailMasterCompany"]);
    Route::post("$masterCompanyPref-detail-submit", [MasterCompanyController::class, "detailMasterCompanySubmit"]);

    $masterSupplierPref = "cns-master-supplier";
    Route::get("$masterSupplierPref", [MasterSupplierController::class, "indexMasterSupplier"]);
    Route::get("xml-$masterSupplierPref", [MasterSupplierController::class, "indexMasterSupplierXml"]);
    Route::get("$masterSupplierPref-new", [MasterSupplierController::class, "newMasterSupplier"]);
    Route::post("$masterSupplierPref-new-submit", [MasterSupplierController::class, "newMasterSupplierSubmit"]);
    Route::get("$masterSupplierPref-detail", [MasterSupplierController::class, "detailMasterSupplier"]);
    Route::post("$masterSupplierPref-detail-submit", [MasterSupplierController::class, "detailMasterSupplierSubmit"]);

    $masterConsigneePref = "cns-master-consignee";
    Route::get("$masterConsigneePref", [MasterConsigneeController::class, "indexMasterConsignee"]);
    Route::get("xml-$masterConsigneePref", [MasterConsigneeController::class, "indexMasterConsigneeXml"]);
    Route::get("$masterConsigneePref-new", [MasterConsigneeController::class, "newMasterConsignee"]);
    Route::post("$masterConsigneePref-new-submit", [MasterConsigneeController::class, "newMasterConsigneeSubmit"]);
    Route::get("$masterConsigneePref-detail", [MasterConsigneeController::class, "detailMasterConsignee"]);
    Route::post("$masterConsigneePref-detail-submit", [MasterConsigneeController::class, "detailMasterConsigneeSubmit"]);

    $siPref = "cns-export-shipment-instruction";
    Route::get("$siPref", [ShipmentInstructionController::class, 'indexShipmentInstruction']);
    Route::get("xml-$siPref", [ShipmentInstructionController::class, 'indexShipmentInstructionXml']);
    Route::get("$siPref-print", [ShipmentInstructionController::class, 'printShipmentInstruction']);
    Route::get("$siPref-new", [ShipmentInstructionController::class, 'newShipmentInstruction']);
    Route::post("$siPref-new-get-si-no", [ShipmentInstructionController::class, 'getSINo']);
    Route::post("$siPref-new-get-order", [ShipmentInstructionController::class, 'getOrderDetail']);
    Route::post("$siPref-new-submit", [ShipmentInstructionController::class, 'newShipmentInstructionSubmit']);
    Route::get("$siPref-generate-fsi", [ShipmentInstructionController::class, 'generateFinalShipmentInstruction']);
    Route::get("$siPref-detail", [ShipmentInstructionController::class, 'detailShipmentInstruction']);
    Route::post("$siPref-detail-submit", [ShipmentInstructionController::class, 'detailShipmentInstructionSubmit']);

    $fsiPref = "cns-export-final-shipment-instruction";
    Route::get("$fsiPref", [FinalShipmentInstructionController::class, 'indexFinalShipmentInstruction']);
    Route::get("$fsiPref-print", [FinalShipmentInstructionController::class, 'printFinalShipmentInstruction']);
    Route::get("xml-$fsiPref", [FinalShipmentInstructionController::class, 'indexFinalShipmentInstructionXml']);
    Route::get("$fsiPref-new", [FinalShipmentInstructionController::class, 'newFinalShipmentInstruction']);
    Route::post("$fsiPref-new-get-si", [FinalShipmentInstructionController::class, 'getSI']);
    Route::post("$fsiPref-new-submit", [FinalShipmentInstructionController::class, 'newFinalShipmentInstructionSubmit']);
    Route::get("$fsiPref-detail", [FinalShipmentInstructionController::class, 'detailFinalShipmentInstruction']);
    Route::get("$fsiPref-create-pl", [FinalShipmentInstructionController::class, 'generatePackingList']);
    Route::get("$fsiPref-create-ci", [FinalShipmentInstructionController::class, 'generateCommercialInvoice']);

    $packingListPref = "cns-export-packing-list";
    Route::get("$packingListPref", [CNSPackingListController::class, 'indexPackingList']);
    Route::get("xml-$packingListPref", [CNSPackingListController::class, 'indexPackingListXml']);
    Route::get("$packingListPref-print", [CNSPackingListController::class, 'printPackingList']);
    Route::get("$packingListPref-new", [CNSPackingListController::class, 'newPackingList']);
    Route::post("$packingListPref-new-get-si", [CNSPackingListController::class, 'getSI']);
    Route::post("$packingListPref-new-submit", [CNSPackingListController::class, 'newPackingListSubmit']);
    Route::get("$packingListPref-detail", [CNSPackingListController::class, 'detailPackingList']);
    Route::post("$packingListPref-detail-submit", [CNSPackingListController::class, 'detailPackingListSubmit']);



    $commercialInvoicePref = "cns-export-commercial-invoice";
    Route::get("$commercialInvoicePref", [CNSCommercialInvoiceController::class, 'indexCommercialInvoice']);
    Route::get("xml-$commercialInvoicePref", [CNSCommercialInvoiceController::class, 'indexCommercialInvoiceXml']);
    Route::get("$commercialInvoicePref-print", [CNSCommercialInvoiceController::class, 'printCommercialInvoice']);
    Route::get("$commercialInvoicePref-print-buyer", [CNSCommercialInvoiceController::class, 'printBuyerCommercialInvoice']);
    Route::get("$commercialInvoicePref-new", [CNSCommercialInvoiceController::class, 'newCommercialInvoice']);
    Route::post("$commercialInvoicePref-get-si", [CNSCommercialInvoiceController::class, 'getSIDetail']);
    Route::post("$commercialInvoicePref-new-submit", [CNSCommercialInvoiceController::class, 'newCommercialInvoiceSubmit']);
    Route::get("$commercialInvoicePref-detail", [CNSCommercialInvoiceController::class, 'detailCommercialInvoice']);
    Route::post("$commercialInvoicePref-detail-submit", [CNSCommercialInvoiceController::class, 'detailCommercialInvoiceSubmit']);

    $wbtPref = "cns-export-weight-bridge-ticket";
    Route::get("$wbtPref", [CNSWeightBridgeTicketController::class, 'indexWeightBridgeTicket']);
    Route::get("xml-$wbtPref", [CNSWeightBridgeTicketController::class, 'indexWeightBridgeTicketXml']);
    Route::get("$wbtPref-new", [CNSWeightBridgeTicketController::class, 'newWeightBridgeTicket']);
    Route::get("$wbtPref-print", [CNSWeightBridgeTicketController::class, 'printWeightBridgeTicket']);
    Route::post("$wbtPref-new-get-order", [CNSWeightBridgeTicketController::class, 'getSIDetail']);
    Route::post("$wbtPref-new-submit", [CNSWeightBridgeTicketController::class, 'newWeightBridgeTicketSubmit']);
    Route::get("$wbtPref-detail", [CNSWeightBridgeTicketController::class, 'detailWeightBridgeTicket']);
    Route::post("$wbtPref-detail-submit", [CNSWeightBridgeTicketController::class, 'detailWeightBridgeTicketSubmit']);

    $cooPref = "cns-export-certificate-origin";
    Route::get("$cooPref", [CNSCertificateOfOriginController::class, 'indexCertificateOfOrigin']);
    Route::get("xml-$cooPref", [CNSCertificateOfOriginController::class, 'indexCertificateOfOriginXml']);
    Route::get("$cooPref-print", [CNSCertificateOfOriginController::class, 'printCertificateOfOrigin']);
    Route::get("$cooPref-new", [CNSCertificateOfOriginController::class, 'newCertificateOfOrigin']);
    Route::post("$cooPref-new-get-wbt", [CNSCertificateOfOriginController::class, 'getCertificateOfOriginWBT']);
    Route::post("$cooPref-new-submit", [CNSCertificateOfOriginController::class, 'newCertificateOfOriginSubmit']);
    Route::get("$cooPref-detail", [CNSCertificateOfOriginController::class, 'detailCertificateOfOrigin']);
    Route::post("$cooPref-detail-submit", [CNSCertificateOfOriginController::class, 'detailCertificateOfOriginSubmit']);

    $sdPref = "cns-export-sustainability-declaration";
    Route::get("$sdPref", [CNSSustainabilityDeclarationController::class, 'indexSustainabilityDeclaration']);
    Route::get("xml-$sdPref", [CNSSustainabilityDeclarationController::class, 'indexSustainabilityDeclarationXml']);
    Route::get("$sdPref-print", [CNSSustainabilityDeclarationController::class, 'printSustainabilityDeclaration']);
    Route::get("$sdPref-new", [CNSSustainabilityDeclarationController::class, 'newSustainabilityDeclaration']);
    Route::post("$sdPref-new-submit", [CNSSustainabilityDeclarationController::class, 'newSustainabilityDeclarationSubmit']);
    Route::get("$sdPref-detail", [CNSSustainabilityDeclarationController::class, 'detailSustainabilityDeclaration']);
    Route::post("$sdPref-detail-submit", [CNSSustainabilityDeclarationController::class, 'detailSustainabilityDeclarationSubmit']);

    $dnPref = "cns-export-delivery-note";
    Route::get("$dnPref", [CNSDeliveryNoteController::class, 'indexDeliveryNote']);
    Route::get("xml-$dnPref", [CNSDeliveryNoteController::class, 'indexDeliveryNoteXml']);
    Route::get("$dnPref-print", [CNSDeliveryNoteController::class, 'printDeliveryNote']);
    Route::get("$dnPref-new", [CNSDeliveryNoteController::class, 'newDeliveryNote']);
    Route::post("$dnPref-new-submit", [CNSDeliveryNoteController::class, 'newDeliveryNoteSubmit']);
    Route::get("$dnPref-detail", [CNSDeliveryNoteController::class, 'detailDeliveryNote']);
    Route::post("$dnPref-detail-submit", [CNSDeliveryNoteController::class, 'detailDeliveryNoteSubmit']);

    $cnsInventoryPref = "cns-inventory-tracking";
    Route::get("$cnsInventoryPref-index", [UCOLogisticController::class, 'trackExportedStockIndex'])->name($cnsInventoryPref . '.index');
    Route::get("$cnsInventoryPref-create", [UCOLogisticController::class, 'trackExportedStockCreatePage'])->name($cnsInventoryPref . '.create');
    Route::get("$cnsInventoryPref-data", [UCOLogisticController::class, 'trackStoragePelindoData'])->name($cnsInventoryPref . '.data');
    Route::get("$cnsInventoryPref-data-json", [UCOLogisticController::class, 'trackExportedStockDataJSON'])->name($cnsInventoryPref . '.data-json');
    Route::post("$cnsInventoryPref-submit", [UCOLogisticController::class, 'submitStoragePelindo'])->name($cnsInventoryPref . '.submit');
    Route::post("$cnsInventoryPref-submit-edit", [UCOLogisticController::class, 'updateExportedStock'])->name($cnsInventoryPref . '.submit-edit');
    Route::delete("$cnsInventoryPref-delete", [UCOLogisticController::class, 'deleteExportedStock'])->name($cnsInventoryPref . '.delete');

    $cnsQualityPref = "cns-quality";
    Route::get("$cnsQualityPref-index", [QualityController::class, 'qualityControlIndexPage'])->name($cnsQualityPref . '.index');
    Route::get("$cnsQualityPref-create", [QualityController::class, 'insertQualityControlPage'])->name($cnsQualityPref . '.create');
    Route::get("$cnsQualityPref-data", [QualityController::class, 'getQualityControlData'])->name($cnsQualityPref . '.data');
    Route::post("$cnsQualityPref-submit", [QualityController::class, 'insertQualityControl'])->name($cnsQualityPref . '.submit');
    Route::post("$cnsQualityPref-submit-edit", [QualityController::class, 'editQualityControl'])->name($cnsQualityPref . '.submit-edit');
    Route::delete("$cnsQualityPref-delete", [QualityController::class, 'deleteQualityControl'])->name($cnsQualityPref . '.delete');
    Route::get("$cnsQualityPref-print", [QualityController::class, 'printQualityControl']);

    $salesOrderCNSPref = "cns-sales-order";
    Route::get("$salesOrderCNSPref", [CNSSalesOrderController::class, 'indexSalesOrder']);
    Route::get("xml-$salesOrderCNSPref", [CNSSalesOrderController::class, 'indexSalesOrderXml']);
    Route::get("$salesOrderCNSPref-new", [CNSSalesOrderController::class, 'newSalesOrder']);
    Route::post("$salesOrderCNSPref-new-submit", [CNSSalesOrderController::class, 'newSalesOrderSubmit']);
    Route::get("$salesOrderCNSPref-detail", [CNSSalesOrderController::class, 'detailSalesOrder']);
    Route::post("$salesOrderCNSPref-detail-submit", [CNSSalesOrderController::class, 'detailSalesOrderSubmit']);
    Route::get("$salesOrderCNSPref-print", [CNSSalesOrderController::class, 'printSalesOrder']);

    $procPref = "cns-procurement";

    $prPref = "$procPref-purchase-request";
    Route::get("$prPref", [CNSPurchaseRequestController::class, 'indexPurchaseRequest']);
    Route::get("xml-$prPref", [CNSPurchaseRequestController::class, 'indexPurchaseRequestXml']);
    Route::get("$prPref-print", [CNSPurchaseRequestController::class, 'printPurchaseRequest']);
    Route::get("$prPref-new", [CNSPurchaseRequestController::class, 'newPurchaseRequest']);
    Route::get("$prPref-news", [CNSPurchaseRequestController::class, 'newsPurchaseRequest']);
    Route::post("$prPref-new-submit", [CNSPurchaseRequestController::class, 'newPurchaseRequestSubmit']);
    Route::post("$prPref-new-update", [CNSPurchaseRequestController::class, 'newPurchaseRequestUpdate']);
    Route::get("$prPref-detail", [CNSPurchaseRequestController::class, 'detailPurchaseRequest']);
    Route::get("xml-$prPref-detail", [CNSPurchaseRequestController::class, 'detailPurchaseRequestXml']);
    Route::post("$prPref-detail-approve", [CNSPurchaseRequestController::class, 'detailPurchaseRequestApprove']);
    Route::post("$prPref-detail-reject", [CNSPurchaseRequestController::class, 'detailPurchaseRequestReject']);

    Route::get("$prPref-outstanding", [CNSPurchaseRequestController::class, 'outstandingPurchaseRequest']);
    Route::get("xml-$prPref-outstanding", [CNSPurchaseRequestController::class, 'outstandingPurchaseRequestXml']);
    Route::get("$prPref-outstanding-detail", [CNSPurchaseRequestController::class, 'outstandingPurchaseRequestDetail']);
    Route::get("xml-$prPref-outstanding-detail", [CNSPurchaseRequestController::class, 'outstandingPurchaseRequestDetailXml']);
    Route::post("$prPref-outstanding-detail-submit", [CNSPurchaseRequestController::class, 'outstandingPurchaseRequestDetailSubmit']);
    Route::post("$prPref-outstanding-detail-update", [CNSPurchaseRequestController::class, 'outstandingPurchaseRequestDetailUpdate']);

    $poPref = "$procPref-purchase-order";
    Route::get("$poPref", [CNSPurchaseOrderController::class, 'indexPurchaseOrder']);
    Route::get("xml-$poPref", [CNSPurchaseOrderController::class, 'indexPurchaseOrderXml']);
    Route::get("$poPref-detail", [CNSPurchaseOrderController::class, 'detailPurchaseOrder']);
    Route::get("xml-$poPref-detail", [CNSPurchaseOrderController::class, 'detailPurchaseOrderXml']);
    Route::get("$poPref-print", [CNSPurchaseOrderController::class, 'printPurchaseOrder']);

    $exportAnnexPref = "cns-export-annex";
    Route::get("$exportAnnexPref-index", [CNSAnnexVIIController::class, 'indexAnnex']);
    Route::get("xml-$exportAnnexPref", [CNSAnnexVIIController::class, 'indexAnnexXml']);
    Route::get("$exportAnnexPref-new", [CNSAnnexVIIController::class, 'newAnnex']);
    Route::post("$exportAnnexPref-new-submit", [CNSAnnexVIIController::class, 'store']);
    Route::get("$exportAnnexPref-data", [CNSAnnexVIIController::class, 'getAnnexData']);
    Route::post("$exportAnnexPref-get-si", [CNSAnnexVIIController::class, 'getSI']);
    Route::get("$exportAnnexPref-print", [CNSAnnexVIIController::class, 'printAnnex']);
    Route::get("$exportAnnexPref-print-sample", [CNSAnnexVIIController::class, 'printAnnexSample']);

    $hcPref = "hrd";
    Route::get("$hcPref-master-approval", [HCGAMasterApprovalController::class, 'index']);
    Route::get("xml-$hcPref-master-approval", [HCGAMasterApprovalController::class, 'indexXml']);
    Route::get("$hcPref-master-approval-detail", [HCGAMasterApprovalController::class, 'detail']);
    Route::get("$hcPref-master-approval-new", [HCGAMasterApprovalController::class, 'new']);
    Route::post("$hcPref-get-departments", [HCGAMasterApprovalController::class, 'getDepartmentsFromDirectorat']);
    Route::post("$hcPref-get-employee", [HCGAMasterApprovalController::class, 'getEmployeeFromDepartment']);
    Route::post("$hcPref-master-approval-new-submit", [HCGAMasterApprovalController::class, 'newSubmit']);
});
