<?php
use App\Http\Controllers\Partner\MasterMaterialController as PartnerMasterMaterialController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\Partner\WarehouseStockOpnameController as PartnerWarehouseStockOpnameController;
use App\Http\Controllers\Partner\AssetManagementController as PartnerAssetManagementController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Partner\LeadsController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\Partner\TicketController as PartnerTicketController;
use App\Http\Controllers\Partner\WarehouseAuditController as PartnerWarehouseAuditController;
use App\Http\Controllers\Partner\ChatListController as PartnerChatListController;
use App\Http\Controllers\Partner\WarehouseJournalSummaryController as PartnerWarehouseJournalSummaryController;
use App\Http\Controllers\Partner\MasterSOPController as PartnerMasterSOPController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\Partner\OrderEquipmentController as PartnerOrderEquipmentController;
use App\Http\Controllers\Partner\ValuationController as PartnerValuationController;
use App\Http\Controllers\ComplianceController;
use App\Http\Controllers\Partner\WarehouseOpenController as PartnerWarehouseOpenController;
use App\Http\Controllers\Partner\WarehouseStockRequestController as PartnerWarehouseStockRequestController;
use App\Http\Controllers\Partner\WarehouseController as PartnerWarehouseController;
use App\Http\Controllers\Partner\FacilityController as PartnerFacilityController;
use App\Http\Controllers\Partner\WarehouseCorrectiveController as PartnerWarehouseCorrectiveController;
use App\Http\Controllers\Partner\BusinessFacilityController as PartnerBusinessFacilityController;
use App\Http\Controllers\Partner\MasterStockPriceController as PartnerMasterStockPriceController;
use App\Http\Controllers\Partner\AnnouncementController as PartnerAnnouncementController;
use App\Http\Controllers\Partner\PartnerRegistrationController;
use App\Http\Controllers\Partner\ListsController as PartnerListsController;
use App\Http\Controllers\Partner\ProfitSharingController as PartnerProfitSharingController;
use App\Http\Controllers\Partner\MasterTrainingController as PartnerMasterTrainingController;
use App\Http\Controllers\Partner\TrainingController as PartnerTrainingController;
use App\Http\Controllers\Partner\MasterAuditController as PartnerMasterAuditController;
use App\Http\Controllers\Partner\SalesController as PartnerSalesController;
use App\Http\Controllers\Partner\WarehouseJournalController as PartnerWarehouseJournalController;
use App\Http\Controllers\Partner\WarehouseMonitoringController as PartnerWarehouseMonitoringController;
use App\Http\Controllers\PerformanceController;
use App\Http\Controllers\Partner\MasterSatisficationController as PartnerMasterSatisficationController;
use App\Http\Controllers\RenewalController;
use App\Http\Controllers\MarketingController;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Partner\MasterDataController;
use App\Http\Controllers\Partner\UCOController as UCOLogisticController;

    Route::get("cns-master-warehouses", [MasterDataController::class, 'getCnsMasterWarehouses'])->name('cns-master-warehouses');

    $cnsInventoryPref = "cns-inventory-tracking";
    Route::get("$cnsInventoryPref-index", [UCOLogisticController::class, 'trackExportedStockIndex'])->name($cnsInventoryPref . '.index');
    Route::get("$cnsInventoryPref-create", [UCOLogisticController::class, 'trackExportedStockCreatePage'])->name($cnsInventoryPref . '.create');
    Route::get("$cnsInventoryPref-data", [UCOLogisticController::class, 'trackStoragePelindoData'])->name($cnsInventoryPref . '.data');
    Route::get("$cnsInventoryPref-data-json", [UCOLogisticController::class, 'trackExportedStockDataJSON'])->name($cnsInventoryPref . '.data-json');
    Route::get("$cnsInventoryPref-filter-options", [UCOLogisticController::class, 'getInventoryFilterOptions'])->name($cnsInventoryPref . '.filter-options');
    Route::get("$cnsInventoryPref-movements", [UCOLogisticController::class, 'getInboundMovementOptions'])->name($cnsInventoryPref . '.movements');
    Route::get("$cnsInventoryPref-movement-detail", [UCOLogisticController::class, 'getInboundMovementDetail'])->name($cnsInventoryPref . '.movement-detail');
    Route::post("$cnsInventoryPref-submit", [UCOLogisticController::class, 'submitStoragePelindo'])->name($cnsInventoryPref . '.submit');
    Route::post("$cnsInventoryPref-submit-edit", [UCOLogisticController::class, 'updateExportedStock'])->name($cnsInventoryPref . '.submit-edit');
    Route::delete("$cnsInventoryPref-delete", [UCOLogisticController::class, 'deleteExportedStock'])->name($cnsInventoryPref . '.delete');

    Route::post('/sales-opportunity-registration-submit', [PartnerRegistrationController::class, 'submit']);

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

    /** Master Data */
    $masterData = "master";

    Route::get("$masterData-lead-qualification", [MasterDataController::class, 'getLeadQualification']);

    Route::get("$masterData-lead-classification", [MasterDataController::class, 'getLeadClassification']);

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

    /** */

    /** projection route */
    $projection = "projection";

    Route::get("$projection-index", [PartnerProfitSharingController::class, 'indexProjection']);

    Route::get("$projection-create", [PartnerProfitSharingController::class, 'createProjection']);

    Route::get("$projection-data-projection", [PartnerProfitSharingController::class, 'projectionData']);

    Route::post("$projection-upload", [PartnerProfitSharingController::class, 'createMitraProjection']);

    Route::get("$projection-template", [PartnerProfitSharingController::class, 'downloadProjectionTemplate']);

    $expenditure = "expenditure";

    Route::get("$expenditure-data-expenditure", [PartnerProfitSharingController::class, 'expenditureData']);

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

    $masterData = "master";
    Route::get("$masterData-lead-status", [MasterDataController::class, 'getLeadStatus']);
    Route::get("$masterData-lead-response", [MasterDataController::class, 'getLeadResponse']);
    Route::get("$masterData-lead-cancel-reason", [MasterDataController::class, 'getLeadCancelReason']);
