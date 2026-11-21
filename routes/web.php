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
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Credential\CredentialRoleController;
use App\Http\Controllers\Credential\CredentialUserController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\OperationalController;
use App\Http\Controller\Accounting\MasterOfficeController;


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
use App\Http\Controllers\Partner\PartnerRegistrationController;
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
use App\Http\Controllers\ProcurementController; //tambahan ricko
use App\Http\Controllers\SupplierController; //tambahan ricko

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

Route::get('partner-early-registration', function () {
    return view('partner.leads');
});

Route::get('open-recruitment-employee', function () {
    $requisitions = \Illuminate\Support\Facades\DB::table('requisition')
        ->where('Status', 'APPROVED')
        ->orderBy('ID', 'DESC')
        ->get();
    return view('humanresources.recruitment.recruitment-new-open', [
        'requisitions' => $requisitions,
        'publicLocation' => 'GEU-HO',
    ]);
});
Route::get('partner-registration', function () {
    // Validate the blast token up-front so leads with an expired/used link
    // see a clear notice instead of a form they cannot submit. The linked
    // opportunity is also passed in so the form prefills the data captured
    // earlier by the sales team.
    $token = request()->query('token');
    $lead = null;
    if ($token) {
        $lead = \Illuminate\Support\Facades\DB::table('opportunity')
            ->where('registration_token', $token)
            ->where(function ($q) {
                $q->whereNull('token_expires_at')
                    ->orWhere('token_expires_at', '>=', now());
            })
            ->first();
        if (!$lead) {
            return view('partner.registration-expired');
        }
    }
    return view('partner.registration-public', ['lead' => $lead]);
});
Route::post('partner-registration-submit', [PartnerRegistrationController::class, 'submit']);

Route::get('partner-prints-invoice', [App\Http\Controllers\Finance\PartnerController::class, 'printInvoice']);
Route::post('province', [App\Http\Controllers\HelperController::class, 'provinceSearch']);
Route::post('city/{province}', [App\Http\Controllers\HelperController::class, 'citySearch']);
Route::post('district', [HelperController::class, 'districtSearch']);
Route::get('subdistrict', [HelperController::class, 'subdistrictSearch']);

// Public address master data endpoints
Route::get('master-province', [MasterDataController::class, 'provinceSearch']);
Route::get('master-city', [MasterDataController::class, 'citySearch']);
Route::get('master-district', [MasterDataController::class, 'districtSearch']);
Route::get('master-subdistrict', [MasterDataController::class, 'subdistrictSearch']);
Route::get('master-postal-code', [MasterDataController::class, 'postalCodeSearch']);
// Route::get('postal-code', [HelperController::class, 'postalCodeSearch']);


Auth::routes();

Route::group(['middleware' => 'usersession'], function () {
    Route::get('/', function () {
        // Uses User Session Middleware
    });
});

$chatPref = "partner-chat";
Route::post("$chatPref-fetch-new", [PartnerChatListController::class, 'fetchNewMessages']);

if (\App\Support\Modules::isEnabled('hcga')) {
    (function () {
        require base_path('routes/modules/hcga-public.php');
    })();
}

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

    Route::get("/credential-role-old", function () {
        return view('credential.role-old');
    });

    Route::get("/credential-role-detail", function () {
        return view('credential.role-detail');
    });
    Route::get("/xml-credential-role-old", function () {
        return view('credential.xml.role');
    });

    Route::post("/credential-role-detail-submit", function () {
        return view('credential.role-detail-submit');
    });
    Route::get("/credential-role", [CredentialRoleController::class, "index"]);
    Route::get("/master-credential-role-list", [CredentialRoleController::class, "roleListData"])->name("master.credential.role.list");
    Route::get("/master-credential-role-permission", [CredentialRoleController::class, "rolePermissionsData"])->name("master.credential.role.permission");
    Route::post("/master-credential-role-permission-save", [CredentialRoleController::class, "savePermissions"])->name("master.credential.role.permission.save");
    Route::post("/master-credential-role-create", [CredentialRoleController::class, "createRole"]);
    Route::post("/master-credential-role-update/{id}", [CredentialRoleController::class, "updateRole"]);
    Route::post("/master-credential-role-delete/{id}", [CredentialRoleController::class, "deleteRole"]);
    Route::post("/master-credential-role-grant-all/{id}", [CredentialRoleController::class, "grantAllPermissions"]);
    Route::post("/master-credential-role-revoke-all/{id}", [CredentialRoleController::class, "revokeAllPermissions"]);
    Route::post("/master-credential-role-menu-create", [CredentialRoleController::class, "createMenuItem"]);
    Route::post("/master-credential-role-menu-update", [CredentialRoleController::class, "updateMenuItem"]);
    Route::post("/master-credential-role-menu-delete", [CredentialRoleController::class, "deleteMenuItem"]);
    Route::get("/master-credential-role-menu-tree", [CredentialRoleController::class, "menuTree"])->name("master.credential.role.menu.tree");
    Route::post("/master-credential-role-menu-create-cascade", [CredentialRoleController::class, "createMenuItemCascade"])->name("master.credential.role.menu.create.cascade");

    Route::get("/credential-user-management", [CredentialUserController::class, "index"]);
    Route::get("/credential-create-user", [CredentialUserController::class, "createUser"]);
    Route::get("/master-credential-user-list", [CredentialUserController::class, "userListData"]);
    Route::get("/master-credential-candidate-without-account", [CredentialUserController::class, "candidatesWithoutAccount"]);
    Route::post("/credential-create-candidate-account", [CredentialUserController::class, "createCandidateAccount"]);
    Route::post("/credential-create-user-submit", [CredentialUserController::class, "createUserSubmit"]);

    Route::get("/candidate/dashboard", [CandidateController::class, "dashboard"]);
    Route::get("/candidate/profile", [CandidateController::class, "profile"]);
    Route::post("/candidate/profile-update", [CandidateController::class, "profileUpdate"]);
    Route::get("/candidate/documents", [CandidateController::class, "documents"]);
    Route::post("/candidate/document-upload", [CandidateController::class, "documentUpload"]);
    Route::get("/candidate/assessment", [CandidateController::class, "assessment"]);
    Route::get("/candidate/progress", [CandidateController::class, "progress"]);
    Route::post("/candidate/progress-confirm", [CandidateController::class, "confirmSchedule"]);
    Route::post("/candidate/availability-update", [CandidateController::class, "updateAvailability"]);
    Route::get("/candidate/offering-download", [CandidateController::class, "downloadOfferingLetter"]);
    Route::post("/candidate/offering-upload", [CandidateController::class, "uploadSignedOffering"]);
    Route::get("/candidate/password", [CandidateController::class, "passwordForm"]);
    Route::post("/candidate/password-update", [CandidateController::class, "passwordUpdate"]);
    Route::post("/master-credential-user-update", [CredentialUserController::class, "updateUser"]);
    Route::post("/master-credential-user-delete", [CredentialUserController::class, "deleteUser"]);

    // Privilege & Role Access Mappings
    Route::get("/credential-privilege-mapping", [\App\Http\Controllers\Credential\PrivilegeMappingController::class, "index"]);
    Route::get("/credential-privilege-mapping-data", [\App\Http\Controllers\Credential\PrivilegeMappingController::class, "data"]);
    Route::post("/credential-privilege-mapping-store", [\App\Http\Controllers\Credential\PrivilegeMappingController::class, "store"]);
    Route::post("/credential-privilege-mapping-delete", [\App\Http\Controllers\Credential\PrivilegeMappingController::class, "destroy"]);

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
    Route::post('/sales-opportunity-registration-submit', [PartnerRegistrationController::class, 'submit']);
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
    Route::post('/asset-lists-stnk-update', function () {
        return view('assets.lists-stnk-update');
    });
    Route::post('/asset-lists-kir-update', function () {
        return view('assets.lists-kir-update');
    });
    Route::post('/asset-lists-photo-upload', function () {
        return view('assets.lists-photo-upload');
    });
    Route::post('/asset-lists-photo-delete', function () {
        return view('assets.lists-photo-delete');
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
    if (\App\Support\Modules::isEnabled('hcga')) {
        (function () {
            require base_path('routes/modules/hcga-hr.php');
        })();
    }

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
    Route::get("$masterData-users", [MasterDataController::class, "getAllUsers"]);
    Route::get("$masterData-facility", [MasterDataController::class, 'indexWarehouseFacility']);
    Route::get("$masterData-facility-create", [MasterDataController::class, 'createWarehouseFacility']);
    Route::post("$masterData-facility-create-submit", [MasterDataController::class, 'submitWarehouseFacility']);
    Route::get("$masterData-facility-data", [MasterDataController::class, 'getWarehouseFacility']);
    Route::post("$masterData-facility-edit", [MasterDataController::class, 'editWarehouseFacility']);
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

    /** Master Bank */
    Route::get("$masterData-bank-data", [MasterDataController::class, 'getBanks']);

    /** Master Warehouse */
    Route::get("$masterData-warehouse-data", [MasterDataController::class, 'getMasterWarehouses']);

    /** POO (Point of Origin UCO) */
    Route::get("$masterData-poo", [MasterDataController::class, 'POOPageIndex']);
    Route::get("$masterData-poo-data", [MasterDataController::class, 'getPOO']);
    Route::get("$masterData-poo-create", [MasterDataController::class, 'createPOOPage']);
    Route::post("$masterData-poo-create-submit", [MasterDataController::class, 'createPOO']);
    Route::get("$masterData-poo-update", [MasterDataController::class, 'updatePOOPage']);
    Route::post("$masterData-poo-update-submit", [MasterDataController::class, 'updatePOO']);
    Route::post("$masterData-poo-delete", [MasterDataController::class, 'deletePOO']);
    Route::get("$masterData-delivery-order-status-list", [UCOLogisticController::class, 'getDeliveryOrderStatusList']);


    // --- Master Work Order Status ---
    Route::get("$masterData-work-order-status", [MasterDataController::class, 'workOrderStatusIndex']);
    Route::get("$masterData-work-order-status-data", [MasterDataController::class, 'workOrderStatusData']);
    Route::get("$masterData-work-order-status-create", [MasterDataController::class, 'workOrderStatusCreate']);
    Route::post("$masterData-work-order-status-submit", [MasterDataController::class, 'workOrderStatusSubmit']);
    Route::post("$masterData-work-order-status-edit", [MasterDataController::class, 'workOrderStatusEdit']);
    Route::delete("$masterData-work-order-status-delete", [MasterDataController::class, 'workOrderStatusDelete']);

    // --- Master Container ---
    Route::get("$masterData-container", [MasterDataController::class, 'containerIndex']);
    Route::get("$masterData-container-data",     [MasterDataController::class, 'containerData']);
    Route::get("$masterData-container-get",     [MasterDataController::class, 'getContainer']);
    Route::post("$masterData-container-submit",  [MasterDataController::class, 'containerSubmit']);
    Route::post("$masterData-container-edit",    [MasterDataController::class, 'containerEdit']);
    Route::delete("$masterData-container-delete", [MasterDataController::class, 'containerDelete']);
    Route::get("$masterData-container-create", [MasterDataController::class, 'containerCreate']);

    // --- Master Config System ---
    Route::get("$masterData-config-system", [MasterDataController::class, 'configSystemIndex']);
    Route::get("$masterData-config-system-data", [MasterDataController::class, 'configSystemData']);
    Route::post("$masterData-config-system-submit", [MasterDataController::class, 'configSystemSubmit']);

    /** finance */
    $finance = "finance";
    Route::get("$finance-pickup-cost", [CostManagementController::class, 'pickupCostIndex']);
    Route::post("$finance-cost-approve-route", [CostManagementController::class, "approvePickupCost"]);
    Route::get("$finance-pickup-route-detail", [UCOLogisticController::class, "getRouteDetail"]);


    Route::get("finance-settlement-cost", [UCOLogisticController::class, 'indexSettlement']);
    Route::get("finance-settlement-approval", [UCOLogisticController::class, "indexSettlementApproval"]);
    Route::get("finance-settlement-detail", [UCOLogisticController::class, "getSettlementDetail"]);
    Route::post("finance-settlement-action", [UCOLogisticController::class, "actionSettlement"]);
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
    Route::post("$pickupPref-finish-settlement", [UCOLogisticController::class, 'actionSettlement']);



    Route::get("$pickupPref-route", [UCOLogisticController::class, 'indexPickupRouting']);
    Route::get("$pickupPref-route-data", [UCOLogisticController::class, "getSavedRouteList"]);
    Route::get("$pickupPref-do-route-data", [UCOLogisticController::class, "getDeliveryOrderRouteList"]);
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
    Route::post('partner-onboarding-log/update', [ApprovalController::class, 'updateOnboardingLog']);


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
    Route::get("$assetManagementPref-detail", [PartnerAssetManagementController::class, 'indexDetail']);
    Route::get("xml-$assetManagementPref-detail-booth-inventory", [PartnerAssetManagementController::class, 'indexDetailBoothInventoryXml']);
    Route::get("xml-$assetManagementPref-detail-booth-access", [PartnerAssetManagementController::class, 'indexDetailBoothAccessXml']);
    Route::get("xml-$assetManagementPref-detail-booth-support", [PartnerAssetManagementController::class, 'indexDetailBoothSupportXml']);
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
    Route::get("$facilityPref", [PartnerBusinessFacilityController::class, 'index']);
    Route::get("xml-$facilityPref", [PartnerBusinessFacilityController::class, 'indexXml']);
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
    Route::get("$warehousePref-stock-opname-systemqty",        [PartnerWarehouseStockOpnameController::class, 'getSystemQuantity']);

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
    Route::post("$masterAuditPref-category-change-status", [PartnerMasterAuditController::class, 'changeStatusMasterAuditCategory']);
    Route::post("$masterAuditPref-category-update", [PartnerMasterAuditController::class, 'updateMasterAuditCategory']);
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
    Route::get("$sopPref-new", [PartnerMasterSOPController::class, 'newSOP']);
    Route::get("$sopPref-detail", [PartnerMasterSOPController::class, 'detailSOP']);
    Route::post("$sopPref-new-submit", [PartnerMasterSOPController::class, 'storeSOP']);
    Route::post("$sopPref-detail-submit", [PartnerMasterSOPController::class, 'updateSOP']);
    Route::post("$sopPref-delete", [PartnerMasterSOPController::class, 'deleteSOP']);

    /** chat route */
    $chatPref = "partner-chat";
    Route::get("$chatPref-list", [PartnerChatListController::class, 'chatList']);
    Route::get("$chatPref-messages-{conversation}", [PartnerChatListController::class, 'getMessages']);
    Route::post("$chatPref-send", [PartnerChatListController::class, 'sendMessage']);
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
    Route::post("$panduanOpsPref-delete", [OperationalController::class, 'delete']);
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
    Route::get("xml-$profitSharingPref-lk-monthly", [PartnerProfitSharingController::class, 'lkProfitSharingXmlV2']);
    Route::get("$profitSharingPref-lk-annually", [PartnerProfitSharingController::class, 'lkAnnuallyProfitSharing']);
    Route::get("xml-$profitSharingPref-lk-annually", [PartnerProfitSharingController::class, 'lkProfitSharingXmlV2']);
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
    Route::post("$partner-profile-update-signature", [UserController::class, 'updateSignature']);

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
    Route::post("$partnerRenewalPrefs-preview-application", [RenewalController::class, 'previewRenewalApplication']);
    Route::post("$partnerRenewalPrefs-finalize-application", [RenewalController::class, 'finalizeRenewalApplication']);
    Route::post("$partnerRenewalPrefs-submit-application", [RenewalController::class, 'finalizeRenewalApplication']);

    /** exit/terminasi */
    $partnerTerminationPrefs = "partner-termination";
    Route::get("$partnerTerminationPrefs-index", [RenewalController::class, 'indexTermination']);
    Route::get("$partnerTerminationPrefs-data", [RenewalController::class, 'getTerminationData']);
    Route::get("$partnerTerminationPrefs-download", [RenewalController::class, 'downloadTerminationApplication']);
    Route::post("$partnerTerminationPrefs-preview-application", [RenewalController::class, 'previewTerminationApplication']);
    Route::post("$partnerTerminationPrefs-finalize-application", [RenewalController::class, 'finalizeTerminationApplication']);
    Route::post("$partnerTerminationPrefs-submit-application", [RenewalController::class, 'finalizeTerminationApplication']);
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
    Route::get("$partnerTransferPrefs-download", [RenewalController::class, 'downloadTransferMembershipApplication']);
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
    Route::get("$onboardingPrefs-download-doc", [OnboardingController::class, 'downloadGeneratedDoc']);
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
    Route::get("$partnerUCO-instock-po-data", [UCOLogisticController::class, 'getInstockPOData']);
    Route::get("$partnerUCO-instock-dregs-data", [UCOLogisticController::class, 'getInstockDregsData']);
    Route::post("$partnerUCO-instock-dregs-submit", [UCOLogisticController::class, 'submitInstockDregs']);
    Route::post("$partnerUCO-instock-recommendation", [UCOLogisticController::class, 'updateRecommendation']);
    Route::get("$partnerUCO-instock-supplies-data", [UCOLogisticController::class, 'getInstockSuppliesData']);
    Route::post("$partnerUCO-instock-supplies-submit", [UCOLogisticController::class, 'submitInstockSupply']);
    Route::post("$partnerUCO-instock-supplies-delete", [UCOLogisticController::class, 'deleteInstockSupply']);
    Route::get("$partnerUCO-instock-containers", [UCOLogisticController::class, 'getInstockContainers']);
    Route::post("$partnerUCO-instock-preview", [UCOLogisticController::class, 'previewInstockAggregation']);
    Route::post("$partnerUCO-instock-submit-batch", [UCOLogisticController::class, 'submitInstockBatch']);
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
    Route::get("partner-outstock", [UCOLogisticController::class, 'indexOutstock']);
    Route::get("partner-outstock-data", [UCOLogisticController::class, 'getOutstockData']);
    Route::get("partner-outstock-create", [UCOLogisticController::class, 'createOutstock']);
    Route::post("partner-outstock-create-submit", [UCOLogisticController::class, 'submitCreateOutstock']);
    Route::get("partner-outstock-detail", [UCOLogisticController::class, 'detailOutstock']);
    Route::post("partner-outstock-loading-submit", [UCOLogisticController::class, 'submitOutstockLoading']);
    Route::post("partner-outstock-loading-upload-photo", [UCOLogisticController::class, 'uploadOutstockLoadingPhoto']);
    Route::post("partner-outstock-handover-submit", [UCOLogisticController::class, 'submitOutstockHandover']);
    Route::post("partner-outstock-handover-upload-photo", [UCOLogisticController::class, 'uploadOutstockHandoverPhoto']);
    Route::get("partner-outstock-delivery-note", [UCOLogisticController::class, 'generateDeliveryNote']);


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


    if (\App\Support\Modules::isEnabled('bp')) {
        (function () {
            require base_path('routes/modules/bp.php');
        })();
    }

    if (\App\Support\Modules::isEnabled('cns')) {
        (function () {
            require base_path('routes/modules/cns.php');
        })();
    }

    if (\App\Support\Modules::isEnabled('finance')) {
        (function () {
            require base_path('routes/modules/finance.php');
        })();
    }

    if (\App\Support\Modules::isEnabled('hcga')) {
        (function () {
            require base_path('routes/modules/hcga.php');
        })();
    }

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




});
