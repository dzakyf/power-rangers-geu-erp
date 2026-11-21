<?php

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
