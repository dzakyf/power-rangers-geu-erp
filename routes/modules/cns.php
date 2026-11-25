<?php

use Illuminate\Support\Facades\Route;
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
use App\Http\Controllers\CNS\BuyerContractController;
use App\Http\Controllers\CNS\NonUcoSupplierController;
use App\Http\Controllers\CNS\AnnexVIIController as CNSAnnexVIIController;
use App\Http\Controllers\CNS\ISCCTraceabilityController;
use App\Http\Controllers\CNS\ExportVolumeDashboardController;
use App\Http\Controllers\CNS\DrainAdjustmentController;
use App\Http\Controllers\CNS\InboundAdjustmentController;
use App\Http\Controllers\CNS\CustomerKycController;
use App\Http\Controllers\CNS\OfferingLetterController;
use App\Http\Controllers\CNS\FinalShippingInstructionController;
use App\Http\Controllers\CNS\ShippingInstructionController;
use App\Http\Controllers\CNS\UpcomingInboundController;
use App\Http\Controllers\CNS\KemasanInventoryController;
use App\Http\Controllers\CNS\TankDrainingController;
use App\Http\Controllers\CNS\MasterKemasanController;
use App\Http\Controllers\CNS\MasterTankController;
use App\Http\Controllers\CNS\FlexibagInventoryController as CNSFlexibagInventoryController;
use App\Http\Controllers\CNS\GoodsReceiptController;
use App\Http\Controllers\CNS\NonUcoInventoryController;
use App\Http\Controllers\CNS\CnsRoleController;
use App\Http\Controllers\CNS\CnsUserController;
use App\Http\Controllers\CNS\ExportChecklistController as CNSExportChecklistController;
use App\Http\Controllers\CNS\ShipmentCostAllocationController as CNSShipmentCostAllocationController;
use App\Http\Controllers\CNS\ProductAllocationController;
use App\Http\Controllers\CNS\SalesForecastController;
use App\Http\Controllers\CNS\ExportInvoicingController;

    $vesselPref = "cns-vessel-schedule";
    Route::group(['middleware' => 'can:cns.vessel_schedule.view'], function () use ($vesselPref) {
        Route::get("$vesselPref", [VesselScheduleController::class, "indexVesselSchedule"]);
        Route::get("xml-$vesselPref", [VesselScheduleController::class, "indexVesselScheduleXml"]);
        Route::get("$vesselPref-detail", [VesselScheduleController::class, "detailVesselSchedule"]);
        Route::get("xml-$vesselPref-detail-log", [VesselScheduleController::class, "detailLogVesselScheduleXml"]);
        Route::get("$vesselPref-bookings", [VesselScheduleController::class, "bookings"]);
    });
    Route::group(['middleware' => 'can:cns.vessel_schedule.create'], function () use ($vesselPref) {
        Route::get("$vesselPref-new", [VesselScheduleController::class, "newVesselSchedule"]);
        Route::post("$vesselPref-new-submit", [VesselScheduleController::class, "newVesselScheduleSubmit"]);
    });
    Route::group(['middleware' => 'can:cns.vessel_schedule.edit'], function () use ($vesselPref) {
        Route::post("$vesselPref-detail-submit", [VesselScheduleController::class, "detailVesselScheduleSubmit"]);
    });

    $stuffPref = "cns-stuffing";
    Route::group(['middleware' => 'can:cns.stuffing.view'], function () use ($stuffPref) {
        Route::get("$stuffPref", [CNSStuffingController::class, "indexStuffing"]);
        Route::get("xml-$stuffPref", [CNSStuffingController::class, "indexStuffingXml"]);
        Route::get("$stuffPref-report", [CNSStuffingController::class, "generateStuffingReport"]);
        Route::get("$stuffPref-detail", [CNSStuffingController::class, "detailStuffing"]);
        Route::get("xml-$stuffPref-detail", [CNSStuffingController::class, "detailStuffingXml"]);
        Route::get("$stuffPref-detail-data", [CNSStuffingController::class, "detailStuffingData"]);
        Route::get("$stuffPref-detail-note-info", [CNSStuffingController::class, "detailNoteInfo"]);
        Route::get("$stuffPref-detail-note", [CNSStuffingController::class, "detailNote"]);
    });
    Route::group(['middleware' => 'can:cns.stuffing.edit'], function () use ($stuffPref) {
        Route::post("$stuffPref-detail-save", [CNSStuffingController::class, "saveDetailStuffing"]);
        Route::post("$stuffPref-detail-checklist", [CNSStuffingController::class, "saveDetailChecklist"]);
        Route::post("$stuffPref-detail-booking-confirmation", [CNSStuffingController::class, "saveDetailBookingConfirmation"]);
    });
    Route::group(['middleware' => 'can:cns.stuffing.delete'], function () use ($stuffPref) {
        Route::post("$stuffPref-detail-delete", [CNSStuffingController::class, "deleteDetailStuffing"]);
        Route::post("$stuffPref-detail-photo-delete", [CNSStuffingController::class, "deleteDetailPhoto"]);
    });

    $stuffMonitoringPref = "cns-export-stuffing-monitoring";
    Route::get("$stuffMonitoringPref", function () {
        return redirect()->to('cns-stuffing?tab=monitoring');
    })->middleware('can:cns.stuffing.view');
    Route::group(['middleware' => 'can:cns.stuffing.view'], function () use ($stuffMonitoringPref) {
        Route::get("xml-$stuffMonitoringPref", [CNSStuffingController::class, 'indexStuffingMonitoringXml']);
        Route::get("$stuffMonitoringPref-detail", [CNSStuffingMonitoringController::class, 'detailStuffingMonitoring']);
        Route::get("$stuffMonitoringPref-note", [CNSStuffingMonitoringController::class, 'showWeighbridgeNote']);
        Route::get("$stuffMonitoringPref-note-info", [CNSStuffingMonitoringController::class, 'weighbridgeNoteInfo']);
    });
    Route::group(['middleware' => 'can:cns.stuffing.edit'], function () use ($stuffMonitoringPref) {
        Route::post("$stuffMonitoringPref-save", [CNSStuffingMonitoringController::class, 'saveStuffingMonitoring']);
    });
    Route::group(['middleware' => 'can:cns.stuffing.delete'], function () use ($stuffMonitoringPref) {
        Route::post("$stuffMonitoringPref-delete", [CNSStuffingMonitoringController::class, 'deleteStuffingMonitoring']);
    });

    $masterCompanyPref = "cns-master-company";
    Route::get("$masterCompanyPref", [MasterCompanyController::class, "indexMasterCompany"]);
    Route::get("$masterCompanyPref-supplier", [MasterCompanyController::class, "getSupplierCompany"]);
    Route::get("$masterCompanyPref-buyer", [MasterCompanyController::class, "getBuyerCompany"]);
    Route::get("xml-$masterCompanyPref", [MasterCompanyController::class, "indexMasterCompanyXml"]);
    Route::get("$masterCompanyPref-new", [MasterCompanyController::class, "newMasterCompany"]);
    Route::post("$masterCompanyPref-new-submit", [MasterCompanyController::class, "newMasterCompanySubmit"]);
    Route::get("$masterCompanyPref-detail", [MasterCompanyController::class, "detailMasterCompany"]);
    Route::post("$masterCompanyPref-detail-submit", [MasterCompanyController::class, "detailMasterCompanySubmit"]);
    Route::get("$masterCompanyPref-import-template", [MasterCompanyController::class, "downloadImportTemplate"])->middleware('can:cns.master_company.edit');
    Route::post("$masterCompanyPref-import", [MasterCompanyController::class, "importMasterCompany"])->middleware('can:cns.master_company.edit');

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


    $masterTankPref = "cns-master-tank";
    Route::get("$masterTankPref", [MasterTankController::class, 'index'])->name($masterTankPref . '.index');
    Route::get("xml-$masterTankPref", [MasterTankController::class, 'xmlData'])->name($masterTankPref . '.xml');
    Route::get("$masterTankPref-new", [MasterTankController::class, 'newTank'])->name($masterTankPref . '.new');
    Route::get("$masterTankPref-detail", [MasterTankController::class, 'detailTank'])->name($masterTankPref . '.detail');
    Route::post("$masterTankPref-store", [MasterTankController::class, 'store'])->name($masterTankPref . '.store');
    Route::get("$masterTankPref-show/{id}", [MasterTankController::class, 'show'])->name($masterTankPref . '.show');
    Route::post("$masterTankPref-update/{id}", [MasterTankController::class, 'update'])->name($masterTankPref . '.update');
    Route::post("$masterTankPref-delete/{id}", [MasterTankController::class, 'destroy'])->name($masterTankPref . '.delete');
    Route::get("$masterTankPref-options", [MasterTankController::class, 'options'])->name($masterTankPref . '.options');
    Route::get("$masterTankPref-balances", [MasterTankController::class, 'tankBalances'])->name($masterTankPref . '.balances');
    Route::get("$masterTankPref-logs/{id}", [MasterTankController::class, 'logs'])->name($masterTankPref . '.logs');

    $masterKemasanPref = "cns-master-kemasan";
    Route::get("$masterKemasanPref", [MasterKemasanController::class, 'index'])->name($masterKemasanPref . '.index');
    Route::get("xml-$masterKemasanPref", [MasterKemasanController::class, 'xmlData'])->name($masterKemasanPref . '.xml');
    Route::post("$masterKemasanPref-store", [MasterKemasanController::class, 'store'])->name($masterKemasanPref . '.store');
    Route::post("$masterKemasanPref-update/{id}", [MasterKemasanController::class, 'update'])->name($masterKemasanPref . '.update');
    Route::post("$masterKemasanPref-delete/{id}", [MasterKemasanController::class, 'destroy'])->name($masterKemasanPref . '.delete');

    // Shipping Instructions
    $siPrefs = ["cns-export-shipping-instruction", "cns-export-shipment-instruction"];
    foreach ($siPrefs as $pref) {
        Route::group(['middleware' => 'can:cns.shipping_instruction.view'], function () use ($pref) {
            Route::get("$pref", [ShippingInstructionController::class, 'indexShippingInstruction']);
            Route::get("xml-$pref", [ShippingInstructionController::class, 'indexShippingInstructionXml']);
            Route::get("$pref-print", [ShippingInstructionController::class, 'printShippingInstruction']);
            Route::get("$pref-detail", [ShippingInstructionController::class, 'detailShippingInstruction']);
            Route::get("$pref-generate-fsi", [ShippingInstructionController::class, 'generateFinalShippingInstruction']);
            Route::post("$pref-new-get-si-no", [ShippingInstructionController::class, 'getSINo']);
            Route::post("$pref-new-get-order", [ShippingInstructionController::class, 'getOrderDetail']);
            Route::post("$pref-detail-get-order", [ShippingInstructionController::class, 'getOrderDetail']);
        });
        Route::group(['middleware' => 'can:cns.shipping_instruction.create'], function () use ($pref) {
            Route::get("$pref-new", [ShippingInstructionController::class, 'newShippingInstruction']);
            Route::post("$pref-new-submit", [ShippingInstructionController::class, 'newShippingInstructionSubmit']);
        });
        Route::group(['middleware' => 'can:cns.shipping_instruction.edit'], function () use ($pref) {
            Route::post("$pref-detail-submit", [ShippingInstructionController::class, 'detailShippingInstructionSubmit']);
        });
        Route::group(['middleware' => 'can:cns.shipping_instruction.delete'], function () use ($pref) {
            Route::post("$pref-delete", [ShippingInstructionController::class, 'deleteShippingInstruction']);
        });
        Route::group(['middleware' => 'can:cns.shipping_instruction.approve'], function () use ($pref) {
            Route::post("$pref-approve", [ShippingInstructionController::class, 'approveShippingInstruction']);
        });
    }

    // Final Shipping Instructions
    $fsiPrefs = ["cns-export-final-shipping-instruction", "cns-export-final-shipment-instruction"];
    foreach ($fsiPrefs as $pref) {
        Route::group(['middleware' => 'can:cns.final_shipping_instruction.view'], function () use ($pref) {
            Route::get("$pref", [FinalShippingInstructionController::class, 'indexFinalShippingInstruction']);
            Route::get("$pref-print", [FinalShippingInstructionController::class, 'printFinalShippingInstruction']);
            Route::get("xml-$pref", [FinalShippingInstructionController::class, 'indexFinalShippingInstructionXml']);
            Route::get("$pref-detail", [FinalShippingInstructionController::class, 'detailFinalShippingInstruction']);
            Route::post("$pref-new-get-si", [FinalShippingInstructionController::class, 'getSI']);
            Route::get("$pref-create-pl", [FinalShippingInstructionController::class, 'generatePackingList']);
            Route::get("$pref-create-ci", [FinalShippingInstructionController::class, 'generateCommercialInvoice']);
        });
        Route::group(['middleware' => 'can:cns.final_shipping_instruction.create'], function () use ($pref) {
            Route::get("$pref-new", [FinalShippingInstructionController::class, 'newFinalShippingInstruction']);
            Route::post("$pref-new-submit", [FinalShippingInstructionController::class, 'newFinalShippingInstructionSubmit']);
        });
        Route::group(['middleware' => 'can:cns.final_shipping_instruction.edit'], function () use ($pref) {
            Route::post("$pref-detail-submit", [FinalShippingInstructionController::class, 'detailFinalShippingInstructionSubmit']);
        });
        Route::group(['middleware' => 'can:cns.final_shipping_instruction.approve'], function () use ($pref) {
            Route::post("$pref-approve", [FinalShippingInstructionController::class, 'approveFinalShippingInstruction']);
        });
    }

    $packingListPref = "cns-export-packing-list";
    Route::get("$packingListPref", [CNSPackingListController::class, 'indexPackingList']);
    Route::get("xml-$packingListPref", [CNSPackingListController::class, 'indexPackingListXml']);
    Route::get("$packingListPref-print", [CNSPackingListController::class, 'printPackingList']);
    Route::get("$packingListPref-new", [CNSPackingListController::class, 'newPackingList']);
    Route::post("$packingListPref-new-get-si", [CNSPackingListController::class, 'getSI']);
    Route::post("$packingListPref-new-submit", [CNSPackingListController::class, 'newPackingListSubmit']);
    Route::get("$packingListPref-detail", [CNSPackingListController::class, 'detailPackingList']);
    Route::post("$packingListPref-detail-submit", [CNSPackingListController::class, 'detailPackingListSubmit']);
    Route::post("$packingListPref-approve", [CNSPackingListController::class, 'approvePackingList']);
    Route::post("$packingListPref-get-bc", [CNSPackingListController::class, 'getStuffingByBc'])->middleware('can:cns.packing_list.create');
    Route::get("$packingListPref-final-new", [CNSPackingListController::class, 'newFinalPackingList'])->middleware('can:cns.packing_list.create');



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
    Route::post("$dnPref-approve", [CNSDeliveryNoteController::class, 'approveDeliveryNote'])->middleware('can:cns.delivery_note.approve');

    Route::get("xml-cns-upcoming-inbound", [UpcomingInboundController::class, 'xmlData'])->name('cns-upcoming-inbound.xml');

    $cnsFlexiPref = "cns-inventory-flexibag";
    Route::group(['middleware' => 'can:cns.inventory_non_uco.view'], function () use ($cnsFlexiPref) {
        Route::get("$cnsFlexiPref", [CNSFlexibagInventoryController::class, 'index'])->name($cnsFlexiPref . '.index');
        Route::get("xml-$cnsFlexiPref", [CNSFlexibagInventoryController::class, 'xmlData'])->name($cnsFlexiPref . '.xml');
        Route::get("$cnsFlexiPref-balance", [CNSFlexibagInventoryController::class, 'balanceJson'])->name($cnsFlexiPref . '.balance');
    });

    $cnsNonUcoPref = "cns-inventory-non-uco";
    Route::group(['middleware' => 'can:cns.inventory_non_uco.view'], function () use ($cnsNonUcoPref) {
        Route::get("$cnsNonUcoPref", [NonUcoInventoryController::class, 'index'])->name($cnsNonUcoPref . '.index');
        Route::get("xml-$cnsNonUcoPref", [NonUcoInventoryController::class, 'xmlData'])->name($cnsNonUcoPref . '.xml');
        Route::get("$cnsNonUcoPref-balance", [NonUcoInventoryController::class, 'balanceJson'])->name($cnsNonUcoPref . '.balance');
    });
    Route::group(['middleware' => 'can:cns.inventory_non_uco.create'], function () use ($cnsNonUcoPref) {
        Route::post("$cnsNonUcoPref-manual-mutation", [NonUcoInventoryController::class, 'manualMutation'])->name($cnsNonUcoPref . '.manual-mutation');
    });

    $cnsKemasanPref = "cns-inventory-kemasan";
    Route::group(['middleware' => 'can:cns.inventory_kemasan.view'], function () use ($cnsKemasanPref) {
        Route::get("$cnsKemasanPref", [KemasanInventoryController::class, 'index'])->name($cnsKemasanPref . '.index');
        Route::get("xml-$cnsKemasanPref", [KemasanInventoryController::class, 'xmlData'])->name($cnsKemasanPref . '.xml');
        Route::get("$cnsKemasanPref-balance", [KemasanInventoryController::class, 'balanceJson'])->name($cnsKemasanPref . '.balance');
    });
    Route::group(['middleware' => 'can:cns.inventory_kemasan.edit'], function () use ($cnsKemasanPref) {
        Route::post("$cnsKemasanPref-manual-mutation", [KemasanInventoryController::class, 'manualMutation'])->name($cnsKemasanPref . '.manual-mutation');
        Route::post("$cnsKemasanPref-site-limits", [KemasanInventoryController::class, 'saveSiteLimits'])->name($cnsKemasanPref . '.site-limits');
    });

    $tankDrainingPref = "cns-tank-draining";
    Route::group(['middleware' => 'can:cns.tank_draining.view'], function () use ($tankDrainingPref) {
        Route::get("$tankDrainingPref", [TankDrainingController::class, 'index'])->name($tankDrainingPref . '.index');
        Route::get("xml-$tankDrainingPref", [TankDrainingController::class, 'xmlData'])->name($tankDrainingPref . '.xml');
        Route::get("$tankDrainingPref-sludge-stock", [TankDrainingController::class, 'sludgeStockJson'])->name($tankDrainingPref . '.sludge-stock');
        Route::get("xml-$tankDrainingPref-sludge", [TankDrainingController::class, 'sludgeStockXml'])->name($tankDrainingPref . '.sludge-xml');
        Route::get("$tankDrainingPref-detail", [TankDrainingController::class, 'detailDraining'])->name($tankDrainingPref . '.detail');
        Route::get("$tankDrainingPref-generate-code", [TankDrainingController::class, 'generateCode'])->name($tankDrainingPref . '.generate-code');
        Route::get("$tankDrainingPref-tank-balance/{id}", [TankDrainingController::class, 'getTankBalance'])->name($tankDrainingPref . '.tank-balance');
        Route::get("$tankDrainingPref-bast/{id}", [TankDrainingController::class, 'bastDocument'])->name($tankDrainingPref . '.bast');
    });
    Route::group(['middleware' => 'can:cns.tank_draining.create'], function () use ($tankDrainingPref) {
        Route::get("$tankDrainingPref-new", [TankDrainingController::class, 'newDraining'])->name($tankDrainingPref . '.new');
        Route::post("$tankDrainingPref-store", [TankDrainingController::class, 'store'])->name($tankDrainingPref . '.store');
        Route::post("$tankDrainingPref-sludge-moving", [TankDrainingController::class, 'storeSludgeMovement'])->name($tankDrainingPref . '.sludge-moving');
    });
    Route::group(['middleware' => 'can:cns.tank_draining.edit'], function () use ($tankDrainingPref) {
        Route::post("$tankDrainingPref-update/{id}", [TankDrainingController::class, 'update'])->name($tankDrainingPref . '.update');
    });
    Route::group(['middleware' => 'can:cns.tank_draining.delete'], function () use ($tankDrainingPref) {
        Route::post("$tankDrainingPref-delete/{id}", [TankDrainingController::class, 'destroy'])->name($tankDrainingPref . '.delete');
    });

    $inboundAdjustmentPref = "cns-inbound-adjustment";
    Route::group(['middleware' => 'can:cns.inbound_adjustment.view'], function () use ($inboundAdjustmentPref) {
        Route::get("$inboundAdjustmentPref", [InboundAdjustmentController::class, 'index'])->name($inboundAdjustmentPref . '.index');
        Route::get("xml-$inboundAdjustmentPref", [InboundAdjustmentController::class, 'xmlData'])->name($inboundAdjustmentPref . '.xml');
        Route::get("$inboundAdjustmentPref-detail", [InboundAdjustmentController::class, 'detailAdjustment'])->name($inboundAdjustmentPref . '.detail');
        Route::get("$inboundAdjustmentPref-generate-code", [InboundAdjustmentController::class, 'generateCode'])->name($inboundAdjustmentPref . '.generate-code');
        Route::get("$inboundAdjustmentPref-active/{kode}", [InboundAdjustmentController::class, 'getActiveAdjustmentByMovement'])->name($inboundAdjustmentPref . '.active-by-movement');
        Route::get("$inboundAdjustmentPref-document/{id}", [InboundAdjustmentController::class, 'document'])->name($inboundAdjustmentPref . '.document');
    });
    Route::group(['middleware' => 'can:cns.inbound_adjustment.create'], function () use ($inboundAdjustmentPref) {
        Route::get("$inboundAdjustmentPref-new", [InboundAdjustmentController::class, 'newAdjustment'])->name($inboundAdjustmentPref . '.new');
        Route::post("$inboundAdjustmentPref-store", [InboundAdjustmentController::class, 'store'])->name($inboundAdjustmentPref . '.store');
    });
    Route::group(['middleware' => 'can:cns.inbound_adjustment.edit'], function () use ($inboundAdjustmentPref) {
        Route::post("$inboundAdjustmentPref-update/{id}", [InboundAdjustmentController::class, 'update'])->name($inboundAdjustmentPref . '.update');
    });
    Route::group(['middleware' => 'can:cns.inbound_adjustment.delete'], function () use ($inboundAdjustmentPref) {
        Route::post("$inboundAdjustmentPref-delete/{id}", [InboundAdjustmentController::class, 'destroy'])->name($inboundAdjustmentPref . '.delete');
    });
    Route::group(['middleware' => 'can:cns.inbound_adjustment.approve'], function () use ($inboundAdjustmentPref) {
        Route::post("$inboundAdjustmentPref-approve/{id}", [InboundAdjustmentController::class, 'approve'])->name($inboundAdjustmentPref . '.approve');
        Route::post("$inboundAdjustmentPref-reject/{id}", [InboundAdjustmentController::class, 'reject'])->name($inboundAdjustmentPref . '.reject');
    });

    // Drain Adjustment
    $drainAdjustmentPref = "cns-drain-adjustment";
    Route::group(['middleware' => 'can:cns.drain_adjustment.view'], function () use ($drainAdjustmentPref) {
        Route::get("$drainAdjustmentPref", [DrainAdjustmentController::class, 'index'])->name($drainAdjustmentPref . '.index');
        Route::get("xml-$drainAdjustmentPref", [DrainAdjustmentController::class, 'xmlData'])->name($drainAdjustmentPref . '.xml');
        Route::get("$drainAdjustmentPref-detail", [DrainAdjustmentController::class, 'detailAdjustment'])->name($drainAdjustmentPref . '.detail');
        Route::get("$drainAdjustmentPref-generate-code", [DrainAdjustmentController::class, 'generateCode'])->name($drainAdjustmentPref . '.generate-code');
        Route::get("$drainAdjustmentPref-document/{id}", [DrainAdjustmentController::class, 'document'])->name($drainAdjustmentPref . '.document');
    });
    Route::group(['middleware' => 'can:cns.drain_adjustment.create'], function () use ($drainAdjustmentPref) {
        Route::get("$drainAdjustmentPref-new", [DrainAdjustmentController::class, 'newAdjustment'])->name($drainAdjustmentPref . '.new');
        Route::post("$drainAdjustmentPref-store", [DrainAdjustmentController::class, 'store'])->name($drainAdjustmentPref . '.store');
    });
    Route::group(['middleware' => 'can:cns.drain_adjustment.edit'], function () use ($drainAdjustmentPref) {
        Route::post("$drainAdjustmentPref-update/{id}", [DrainAdjustmentController::class, 'update'])->name($drainAdjustmentPref . '.update');
    });
    Route::group(['middleware' => 'can:cns.drain_adjustment.delete'], function () use ($drainAdjustmentPref) {
        Route::post("$drainAdjustmentPref-delete/{id}", [DrainAdjustmentController::class, 'destroy'])->name($drainAdjustmentPref . '.delete');
    });
    Route::group(['middleware' => 'can:cns.drain_adjustment.approve'], function () use ($drainAdjustmentPref) {
        Route::post("$drainAdjustmentPref-approve/{id}", [DrainAdjustmentController::class, 'approve'])->name($drainAdjustmentPref . '.approve');
        Route::post("$drainAdjustmentPref-reject/{id}", [DrainAdjustmentController::class, 'reject'])->name($drainAdjustmentPref . '.reject');
    });

    // Quality Control

    $cnsQualityPref = "cns-quality";
    Route::get("$cnsQualityPref-external-index", [QualityController::class, 'externalIndexPage'])->name($cnsQualityPref . '.external-index');
    Route::get("$cnsQualityPref-internal-index", [QualityController::class, 'internalIndexPage'])->name($cnsQualityPref . '.internal-index');
    Route::get("$cnsQualityPref-index", [QualityController::class, 'qualityControlIndexPage'])->name($cnsQualityPref . '.index');
    Route::get("$cnsQualityPref-create", [QualityController::class, 'insertQualityControlPage'])->name($cnsQualityPref . '.create');
    Route::get("$cnsQualityPref-data", [QualityController::class, 'getQualityControlData'])->name($cnsQualityPref . '.data');
    Route::post("$cnsQualityPref-submit", [QualityController::class, 'insertQualityControl'])->name($cnsQualityPref . '.submit');
    Route::post("$cnsQualityPref-submit-edit", [QualityController::class, 'editQualityControl'])->name($cnsQualityPref . '.submit-edit');
    Route::delete("$cnsQualityPref-delete", [QualityController::class, 'deleteQualityControl'])->name($cnsQualityPref . '.delete');
    Route::get("$cnsQualityPref-print", [QualityController::class, 'printQualityControl']);
    Route::get("$cnsQualityPref-bookings", [QualityController::class, 'bookings'])->name($cnsQualityPref . '.bookings');
    Route::get("$cnsQualityPref-monthly-stats", [QualityController::class, 'getMonthlyStats'])->name($cnsQualityPref . '.monthly-stats');

    $salesOrderCNSPref = "cns-sales-order";
    Route::get("$salesOrderCNSPref", [CNSSalesOrderController::class, 'indexSalesOrder']);
    Route::get("xml-$salesOrderCNSPref", [CNSSalesOrderController::class, 'indexSalesOrderXml']);
    Route::get("$salesOrderCNSPref-new", [CNSSalesOrderController::class, 'newSalesOrder']);
    Route::post("$salesOrderCNSPref-new-submit", [CNSSalesOrderController::class, 'newSalesOrderSubmit']);
    Route::get("$salesOrderCNSPref-detail", [CNSSalesOrderController::class, 'detailSalesOrder']);
    Route::post("$salesOrderCNSPref-detail-submit", [CNSSalesOrderController::class, 'detailSalesOrderSubmit']);
    Route::get("$salesOrderCNSPref-print", [CNSSalesOrderController::class, 'printSalesOrder']);
    Route::get("$salesOrderCNSPref-surat-jalan-print", [CNSSalesOrderController::class, 'printSuratJalan'])->middleware('can:cns.sales_order.view');
    Route::group(['middleware' => 'can:cns.sales_order.approve_gudang'], function () use ($salesOrderCNSPref) {
        Route::post("$salesOrderCNSPref-approve-gudang/{id}", [CNSSalesOrderController::class, 'approveGudang']);
    });
    Route::group(['middleware' => 'can:cns.sales_order.approve_buyer'], function () use ($salesOrderCNSPref) {
        Route::post("$salesOrderCNSPref-approve-buyer/{id}", [CNSSalesOrderController::class, 'approveBuyer']);
    });
    Route::group(['middleware' => 'can:cns.sales_order.approve_manager'], function () use ($salesOrderCNSPref) {
        Route::post("$salesOrderCNSPref-approve/{id}", [CNSSalesOrderController::class, 'approveSalesOrder']);
    });

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

    $grPref = "$procPref-goods-receipt";
    Route::get("$grPref", [GoodsReceiptController::class, 'index'])->name($grPref . '.index');
    Route::get("xml-$grPref", [GoodsReceiptController::class, 'xmlData'])->name($grPref . '.xml');
    Route::get("$grPref-create", [GoodsReceiptController::class, 'create'])->name($grPref . '.create');
    Route::post("$grPref-store", [GoodsReceiptController::class, 'store'])->name($grPref . '.store');
    Route::get("$grPref-po-items", [GoodsReceiptController::class, 'poItems'])->name($grPref . '.po-items');
    Route::get("$grPref-detail/{id}", [GoodsReceiptController::class, 'detail'])->name($grPref . '.detail');
    Route::post("$grPref-approve/{id}", [GoodsReceiptController::class, 'approve'])->name($grPref . '.approve');
    Route::post("$grPref-void/{id}", [GoodsReceiptController::class, 'void'])->name($grPref . '.void');
    Route::get("$grPref-print/{id}", [GoodsReceiptController::class, 'print'])->name($grPref . '.print');

    $buyerContractPref = "$procPref-buyer-contract";
    Route::get("$buyerContractPref", [BuyerContractController::class, 'indexBuyerContract']);
    Route::get("xml-$buyerContractPref", [BuyerContractController::class, 'indexBuyerContractXml']);
    Route::get("$buyerContractPref-new", [BuyerContractController::class, 'newBuyerContract']);
    Route::post("$buyerContractPref-new-submit", [BuyerContractController::class, 'newBuyerContractSubmit']);
    Route::get("$buyerContractPref-detail", [BuyerContractController::class, 'detailBuyerContract']);
    Route::post("$buyerContractPref-detail-submit", [BuyerContractController::class, 'detailBuyerContractSubmit']);
    Route::get("$buyerContractPref-file", [BuyerContractController::class, 'contractFile'])->name('cns-procurement-buyer-contract.file');
    Route::get("$buyerContractPref-booking-file", [BuyerContractController::class, 'bookingFile'])->name('cns-procurement-buyer-contract.booking-file');
    Route::post("$buyerContractPref-booking-submit", [BuyerContractController::class, 'addBooking']);
    Route::post("$buyerContractPref-booking-delete", [BuyerContractController::class, 'deleteBooking']);
    Route::get("$buyerContractPref-buyers", [BuyerContractController::class, 'buyers']);

    $nonUcoSupplierPref = "$procPref-non-uco-supplier";
    Route::get("$nonUcoSupplierPref-vendors", [NonUcoSupplierController::class, 'vendors']);
    Route::post("$nonUcoSupplierPref-new-submit", [NonUcoSupplierController::class, 'newNonUcoSupplierSubmit']);
    Route::get("$nonUcoSupplierPref-detail", [NonUcoSupplierController::class, 'detailNonUcoSupplier']);
    Route::post("$nonUcoSupplierPref-detail-submit", [NonUcoSupplierController::class, 'detailNonUcoSupplierSubmit']);

    $exportAnnexPref = "cns-export-annex";
    Route::get("$exportAnnexPref-index", [CNSAnnexVIIController::class, 'indexAnnex']);
    Route::get("xml-$exportAnnexPref", [CNSAnnexVIIController::class, 'indexAnnexXml']);
    Route::get("$exportAnnexPref-new", [CNSAnnexVIIController::class, 'newAnnex']);
    Route::post("$exportAnnexPref-new-submit", [CNSAnnexVIIController::class, 'store']);
    Route::get("$exportAnnexPref-data", [CNSAnnexVIIController::class, 'getAnnexData']);
    Route::post("$exportAnnexPref-get-si", [CNSAnnexVIIController::class, 'getSI']);
    Route::get("$exportAnnexPref-print", [CNSAnnexVIIController::class, 'printAnnex']);
    Route::get("$exportAnnexPref-print-sample", [CNSAnnexVIIController::class, 'printAnnexSample']);

    $isccTraceabilityPref = "cns-iscc-traceability";
    Route::get("$isccTraceabilityPref-index", [ISCCTraceabilityController::class, 'index'])->name($isccTraceabilityPref . '.index');
    Route::get("$isccTraceabilityPref-utama", [ISCCTraceabilityController::class, 'utama'])->name($isccTraceabilityPref . '.utama');
    Route::get("$isccTraceabilityPref-utama-detail", [ISCCTraceabilityController::class, 'utamaDetail'])->name($isccTraceabilityPref . '.utama-detail');
    Route::get("$isccTraceabilityPref-fragment-data", [ISCCTraceabilityController::class, 'fragmentData'])->name($isccTraceabilityPref . '.fragment-data');
    Route::get("$isccTraceabilityPref-delivery-data", [ISCCTraceabilityController::class, 'deliveryData'])->name($isccTraceabilityPref . '.delivery-data');
    Route::get("$isccTraceabilityPref-delivery-fragment-data", [ISCCTraceabilityController::class, 'deliveryFragmentData'])->name($isccTraceabilityPref . '.delivery-fragment-data');
    Route::get("$isccTraceabilityPref-fragment-export", [ISCCTraceabilityController::class, 'exportFragments'])->name($isccTraceabilityPref . '.fragment-export');
    Route::get("$isccTraceabilityPref-edit/{fragmentId}", [ISCCTraceabilityController::class, 'edit'])->name($isccTraceabilityPref . '.edit');
    Route::post("$isccTraceabilityPref-update", [ISCCTraceabilityController::class, 'update'])->name($isccTraceabilityPref . '.update');
    Route::post("$isccTraceabilityPref-photo-upload", [ISCCTraceabilityController::class, 'uploadPhoto'])->name($isccTraceabilityPref . '.photo-upload');
    Route::post("$isccTraceabilityPref-photo-delete", [ISCCTraceabilityController::class, 'deletePhoto'])->name($isccTraceabilityPref . '.photo-delete');
    Route::post("$isccTraceabilityPref-mark-audited", [ISCCTraceabilityController::class, 'markAudited'])->name($isccTraceabilityPref . '.mark-audited');
    Route::get("$isccTraceabilityPref-print/{fragmentId}", [ISCCTraceabilityController::class, 'printDoc'])->name($isccTraceabilityPref . '.print');

    // Offering Letter
    $offeringLetterPref = "cns-offering-letter";
    Route::group(['middleware' => 'can:cns.offering_letter.view'], function () use ($offeringLetterPref) {
        Route::get("$offeringLetterPref", [OfferingLetterController::class, 'index'])->name($offeringLetterPref . '.index');
        Route::get("xml-$offeringLetterPref", [OfferingLetterController::class, 'xmlData'])->name($offeringLetterPref . '.xml');
        Route::get("$offeringLetterPref-next-number", [OfferingLetterController::class, 'nextNumber'])->name($offeringLetterPref . '.next-number');
        Route::get("$offeringLetterPref-detail/{id}", [OfferingLetterController::class, 'detail'])->name($offeringLetterPref . '.detail');
        Route::get("$offeringLetterPref-print", [OfferingLetterController::class, 'print'])->name($offeringLetterPref . '.print-task');
        Route::get("$offeringLetterPref-print/{id}", [OfferingLetterController::class, 'print'])->name($offeringLetterPref . '.print');
    });
    Route::group(['middleware' => 'can:cns.offering_letter.create'], function () use ($offeringLetterPref) {
        Route::get("$offeringLetterPref-create", [OfferingLetterController::class, 'create'])->name($offeringLetterPref . '.create');
        Route::post("$offeringLetterPref-store", [OfferingLetterController::class, 'store'])->name($offeringLetterPref . '.store');
    });
    Route::group(['middleware' => 'can:cns.offering_letter.edit'], function () use ($offeringLetterPref) {
        Route::post("$offeringLetterPref-update/{id}", [OfferingLetterController::class, 'update'])->name($offeringLetterPref . '.update');
    });
    Route::group(['middleware' => 'can:cns.offering_letter.approve'], function () use ($offeringLetterPref) {
        Route::post("$offeringLetterPref-approve/{id}", [OfferingLetterController::class, 'approve'])->name($offeringLetterPref . '.approve');
    });

    // KYC
    $kycPref = "cns-customer-compliance-kyc";
    Route::group(['middleware' => 'can:cns.customer_compliance_kyc.view'], function () use ($kycPref) {
        Route::get("$kycPref", [CustomerKycController::class, 'index'])->name($kycPref . '.index');
        Route::get("xml-$kycPref", [CustomerKycController::class, 'xmlData'])->name($kycPref . '.xml');
        Route::get("$kycPref-detail/{id}", [CustomerKycController::class, 'detail'])->name($kycPref . '.detail');
        Route::get("$kycPref-download/{id}/{type}", [CustomerKycController::class, 'downloadDoc'])->name($kycPref . '.download');
        Route::get("$kycPref-print/{id}", [CustomerKycController::class, 'printPdf'])->name($kycPref . '.print');
    });
    Route::group(['middleware' => 'can:cns.customer_compliance_kyc.create'], function () use ($kycPref) {
        Route::get("$kycPref-create", [CustomerKycController::class, 'create'])->name($kycPref . '.create');
        Route::post("$kycPref-store", [CustomerKycController::class, 'store'])->name($kycPref . '.store');
    });
    Route::group(['middleware' => 'can:cns.customer_compliance_kyc.edit'], function () use ($kycPref) {
        Route::get("$kycPref-edit/{id}", [CustomerKycController::class, 'edit'])->name($kycPref . '.edit');
        Route::post("$kycPref-update/{id}", [CustomerKycController::class, 'update'])->name($kycPref . '.update');
    });
    Route::group(['middleware' => 'can:cns.customer_compliance_kyc.delete'], function () use ($kycPref) {
        Route::post("$kycPref-delete/{id}", [CustomerKycController::class, 'destroy'])->name($kycPref . '.delete');
    });
    Route::group(['middleware' => 'can:cns.customer_compliance_kyc.approve'], function () use ($kycPref) {
        Route::post("$kycPref-approve/{id}", [CustomerKycController::class, 'approve'])->name($kycPref . '.approve');
        Route::post("$kycPref-reject/{id}", [CustomerKycController::class, 'reject'])->name($kycPref . '.reject');
    });

    // CNS Access & User Management
    $cnsUserPref = "cns-user-management";
    Route::group(['middleware' => 'can:cns.user_management.view'], function () use ($cnsUserPref) {
        Route::get("$cnsUserPref", [CnsUserController::class, "index"])->name("$cnsUserPref.index");
        Route::get("$cnsUserPref-data", [CnsUserController::class, "userListData"])->name("$cnsUserPref.data");
    });
    Route::group(['middleware' => 'can:cns.user_management.create'], function () use ($cnsUserPref) {
        Route::post("$cnsUserPref-store", [CnsUserController::class, "createUserSubmit"])->name("$cnsUserPref.store");
    });
    Route::group(['middleware' => 'can:cns.user_management.edit'], function () use ($cnsUserPref) {
        Route::post("$cnsUserPref-update/{id?}", [CnsUserController::class, "updateUser"])->name("$cnsUserPref.update");
    });
    Route::group(['middleware' => 'can:cns.user_management.delete'], function () use ($cnsUserPref) {
        Route::post("$cnsUserPref-delete/{id?}", [CnsUserController::class, "deleteUser"])->name("$cnsUserPref.delete");
    });

    // CNS Role & RBAC Management
    $cnsRolePref = "cns-role-management";
    Route::group(['middleware' => 'can:cns.role_management.view'], function () use ($cnsRolePref) {
        Route::get("$cnsRolePref", [CnsRoleController::class, "index"])->name("$cnsRolePref.index");
        Route::get("$cnsRolePref-data", [CnsRoleController::class, "roleListData"])->name("$cnsRolePref.data");
        Route::get("$cnsRolePref-permissions", [CnsRoleController::class, "rolePermissionsData"])->name("$cnsRolePref.permissions");
        Route::post("$cnsRolePref-permissions-save", [CnsRoleController::class, "savePermissions"])->name("$cnsRolePref.permissions.save");
        Route::post("$cnsRolePref-store", [CnsRoleController::class, "createRole"])->name("$cnsRolePref.store");
        Route::post("$cnsRolePref-update/{id}", [CnsRoleController::class, "updateRole"])->name("$cnsRolePref.update");
        Route::post("$cnsRolePref-delete/{id}", [CnsRoleController::class, "deleteRole"])->name("$cnsRolePref.delete");
        Route::post("$cnsRolePref-grant-all/{id}", [CnsRoleController::class, "grantAllPermissions"])->name("$cnsRolePref.grant_all");
        Route::post("$cnsRolePref-revoke-all/{id}", [CnsRoleController::class, "revokeAllPermissions"])->name("$cnsRolePref.revoke_all");
    });

    // Export Checklist
    $exportChecklistPref = "cns-export-checklist";
    Route::group(['middleware' => 'can:cns.export_checklist.view'], function () use ($exportChecklistPref) {
        Route::get("$exportChecklistPref", [CNSExportChecklistController::class, 'index']);
        Route::get("xml-$exportChecklistPref", [CNSExportChecklistController::class, 'indexXml']);
        Route::get("$exportChecklistPref-detail", [CNSExportChecklistController::class, 'detail']);
    });
    Route::group(['middleware' => 'can:cns.export_checklist.edit'], function () use ($exportChecklistPref) {
        Route::post("$exportChecklistPref-detail-submit", [CNSExportChecklistController::class, 'saveChecklist']);
        Route::post("$exportChecklistPref-approve-payment", [CNSExportChecklistController::class, 'approvePayment']);
    });

    // Cost Allocation per Shipment
    $costAllocPref = "cns-export-cost-allocation";
    Route::group(['middleware' => 'can:cns.cost_allocation_shipment.view'], function () use ($costAllocPref) {
        Route::get("$costAllocPref", [CNSShipmentCostAllocationController::class, 'index'])->name("$costAllocPref.index");
        Route::get("$costAllocPref-edit/{id}", [CNSShipmentCostAllocationController::class, 'edit'])->name("$costAllocPref.edit");
        Route::get("$costAllocPref-export-csv", [CNSShipmentCostAllocationController::class, 'exportCsv'])->name("$costAllocPref.export-csv");
    });
    Route::group(['middleware' => 'can:cns.cost_allocation_shipment.create'], function () use ($costAllocPref) {
        Route::get("$costAllocPref-create", [CNSShipmentCostAllocationController::class, 'create'])->name("$costAllocPref.create");
        Route::post("$costAllocPref-store", [CNSShipmentCostAllocationController::class, 'store'])->name("$costAllocPref.store");
    });
    Route::group(['middleware' => 'can:cns.cost_allocation_shipment.edit'], function () use ($costAllocPref) {
        Route::post("$costAllocPref-update/{id}", [CNSShipmentCostAllocationController::class, 'update'])->name("$costAllocPref.update");
        Route::post("$costAllocPref-finalize/{id}", [CNSShipmentCostAllocationController::class, 'finalize'])->name("$costAllocPref.finalize");
        Route::post("$costAllocPref-reopen/{id}", [CNSShipmentCostAllocationController::class, 'reopen'])->name("$costAllocPref.reopen");
    });
    Route::group(['middleware' => 'can:cns.cost_allocation_shipment.delete'], function () use ($costAllocPref) {
        Route::delete("$costAllocPref-delete/{id}", [CNSShipmentCostAllocationController::class, 'destroy'])->name("$costAllocPref.delete");
    });

    // Product Allocation & Volume Planning
    $productAllocationPref = "cns-product-allocation";
    Route::group(['middleware' => 'can:cns.product_allocation.view'], function () use ($productAllocationPref) {
        Route::get("$productAllocationPref", [ProductAllocationController::class, 'index'])->name($productAllocationPref . '.index');
        Route::get("$productAllocationPref-data", [ProductAllocationController::class, 'dataAjax'])->name($productAllocationPref . '.data');
        Route::get("xml-$productAllocationPref", [ProductAllocationController::class, 'indexAllocationXml'])->name($productAllocationPref . '.xml');
        Route::get("xml-cns-upcoming-export", [ProductAllocationController::class, 'indexUpcomingXml'])->name('cns-upcoming-export.xml');
        Route::get("$productAllocationPref-excel", [ProductAllocationController::class, 'exportExcel'])->name($productAllocationPref . '.export-excel');
    });
    Route::group(['middleware' => 'can:cns.product_allocation.create'], function () use ($productAllocationPref) {
        Route::post("$productAllocationPref", [ProductAllocationController::class, 'store'])->name($productAllocationPref . '.store');
    });
    Route::group(['middleware' => 'can:cns.product_allocation.edit'], function () use ($productAllocationPref) {
        Route::post("$productAllocationPref/{id}/update", [ProductAllocationController::class, 'update'])->name($productAllocationPref . '.update');
    });
    Route::group(['middleware' => 'can:cns.product_allocation.delete'], function () use ($productAllocationPref) {
        Route::post("$productAllocationPref/{id}/delete", [ProductAllocationController::class, 'destroy'])->name($productAllocationPref . '.destroy');
    });

    // Sales Forecast & Pipeline Tracking
    $salesForecastPref = "cns-sales-forecast";
    Route::group(['middleware' => 'can:cns.sales_forecast.view'], function () use ($salesForecastPref) {
        Route::get("$salesForecastPref", [SalesForecastController::class, 'index'])->name($salesForecastPref . '.index');
        Route::get("$salesForecastPref-data", [SalesForecastController::class, 'dataAjax'])->name($salesForecastPref . '.data');
    });
    Route::group(['middleware' => 'can:cns.sales_forecast.create'], function () use ($salesForecastPref) {
        Route::post("$salesForecastPref", [SalesForecastController::class, 'store'])->name($salesForecastPref . '.store');
    });
    Route::group(['middleware' => 'can:cns.sales_forecast.edit'], function () use ($salesForecastPref) {
        Route::post("$salesForecastPref/{id}/update", [SalesForecastController::class, 'update'])->name($salesForecastPref . '.update');
    });
    Route::group(['middleware' => 'can:cns.sales_forecast.delete'], function () use ($salesForecastPref) {
        Route::post("$salesForecastPref/{id}/delete", [SalesForecastController::class, 'destroy'])->name($salesForecastPref . '.destroy');
    });

    // Export Invoicing (CNS-owned)
    $exportInvoicingPref = "finance-export-invoicing";
    Route::group(['middleware' => 'can:cns.export_invoicing.view'], function () use ($exportInvoicingPref) {
        Route::get("$exportInvoicingPref", [ExportInvoicingController::class, 'index'])->name($exportInvoicingPref);
        Route::get("$exportInvoicingPref-xml", [ExportInvoicingController::class, 'indexXml'])->name($exportInvoicingPref . '-xml');
        Route::get("$exportInvoicingPref-detail/{id}", [ExportInvoicingController::class, 'detail'])->name($exportInvoicingPref . '-detail');
        Route::get("$exportInvoicingPref-file/{id}/{type}", [ExportInvoicingController::class, 'downloadFile'])->name($exportInvoicingPref . '-file');
    });
    Route::group(['middleware' => 'can:cns.export_invoicing.create'], function () use ($exportInvoicingPref) {
        Route::post("$exportInvoicingPref-submit", [ExportInvoicingController::class, 'submit'])->name($exportInvoicingPref . '-submit');
    });
    Route::group(['middleware' => 'can:cns.export_invoicing.approve'], function () use ($exportInvoicingPref) {
        Route::post("$exportInvoicingPref-approve/{id}", [ExportInvoicingController::class, 'approve'])->name($exportInvoicingPref . '-approve');
        Route::post("$exportInvoicingPref-reject/{id}", [ExportInvoicingController::class, 'reject'])->name($exportInvoicingPref . '-reject');
    });

    // Dashboard Export Volume
    $dashboardExportPref = "cns-dashboard-export-volume";
    Route::group(['middleware' => 'can:cns.export_volume.view'], function () use ($dashboardExportPref) {
        Route::get("$dashboardExportPref", [ExportVolumeDashboardController::class, 'index'])->name($dashboardExportPref . '.index');
        Route::get("$dashboardExportPref-data", [ExportVolumeDashboardController::class, 'dataAjax'])->name($dashboardExportPref . '.data');
        Route::get("$dashboardExportPref-excel", [ExportVolumeDashboardController::class, 'exportExcel'])->name($dashboardExportPref . '.export-excel');
    });

    Route::get('/home-cns', function () {
        return view('home-cns');
    });;
