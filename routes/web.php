<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HelperController;


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

if (\App\Support\Modules::isEnabled('bp')) {
    require base_path('routes/modules/bp-public.php');
}

if (\App\Support\Modules::isEnabled('finance')) {
    require base_path('routes/modules/finance-public.php');
}

if (\App\Support\Modules::isEnabled('hcga')) {
    (function () {
        require base_path('routes/modules/hcga-public.php');
    })();
}

Route::group(['middleware' => 'log.access'], function () {

    /** Master Data */
    $masterData = "master";

    /** finance */
    $finance = "finance";

    /** Work Order */
    $workOrderPref = "partner-work-order";

    /** Pickup */
    $pickupPref = "partner-pickup";

    $deliveryOrderPref = "partner-delivery-order";

    /** Ticket */
    $ucoTicket = "partner-uco-ticket";

    /** leads */
    $partnerLeads =  "partner-leads";

    /** partner registration approval */
    $partnerApprovalPrefix = "partner-approval";

    /** Benefit Facility */
    $facilityPref = 'partner-facility';

    /** Asset Management */
    $assetManagementPref = 'partner-asset-management';

    $orderEquipmentPref = "partner-order-equipment";

    /** Benefit Facility Mitra */
    $facilityPref = 'partner-business-facility';

    /** Master Price Stock */
    $masterPriceStockPref = "partner-master-stock-price";

    /** Warehouse */
    $warehousePref = 'partner-warehouse';

    $correctivePref = "$warehousePref-corrective-action";
    /** */

    /** audit category route */
    $masterAuditPref = "partner-master-audit";

    /** sop compliance route */
    $sopPref = "partner-master-sop";

    /** chat route */
    $chatPref = "partner-chat";

    /** chat route */
    $announcementPref = "partner-announcement";

    /** ticket route */
    $ticketPref = "partner-ticket";

    /** broadcast route */
    $broadcastPref = "broadcast";
    /** */

    /** panduan operasional route */
    $panduanOpsPref = "panduan";
    /** */

    /**  notification route */
    $notification = "notification";
    /** */

    /** projection route */
    $projection = "projection";
    $expenditure = "expenditure";

    /** Capital expenditure  */
    $capexPrefs = "partner-capex";
    /** */

    /** profit sharing */
    $profitSharingPref = 'partner-profit-sharing';

    /**master data route */
    $partner = "partner";

    $cns = "cns";

    /** renewal  */
    $partnerRenewalPrefs = "partner-renewal";

    /** exit/terminasi */
    $partnerTerminationPrefs = "partner-termination";
    /** transfer kepemilikan kemitraan */
    $partnerTransferPrefs = "partner-transfer-membership";

    /** OnboardingController */
    $onboardingPrefs = "partner-onboarding";

    /** Partner Sales */
    $salesPrefs = "partner-sales";

    /** Survey controller */
    $surveyPrefs = "partner-survey";

    /** Perhitungan Valuasi */
    $valuationPrefs = "partner-valuation";

    /** Training (LMS) */
    $masterTrainingPref = "partner-master-training";

    $masterMaterialPref = "partner-master-material";

    $masterSatisficationPref = "partner-master-satisfication";

    $trainingPref = "partner-training";

    /** Marketing & Branding */
    $marketingPrefs = "partner-branding";

    $partnerCompliance = "partner-compliance";

    $partnerUCO = "partner-uco";

    $partnerkpi = "partner-performance";

    $partnerDashboard = "partner-dashboard";

    // ---- module route files ----
    require base_path('routes/modules/core.php');
    require base_path('routes/modules/credential.php');

    if (\App\Support\Modules::isEnabled('bp')) {
        require base_path('routes/modules/bp.php');
    }

    if (\App\Support\Modules::isEnabled('finance')) {
        require base_path('routes/modules/finance.php');
    }

    if (\App\Support\Modules::isEnabled('cns')) {
        require base_path('routes/modules/cns.php');
    }

    if (\App\Support\Modules::isEnabled('hcga')) {
        require base_path('routes/modules/hcga-hr.php');
        require base_path('routes/modules/hcga.php');
    }

    if (\App\Support\Modules::isEnabled('legacy')) {
        require base_path('routes/modules/legacy.php');
    }
});
