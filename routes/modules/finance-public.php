<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Finance\PartnerController;

Route::get('partner-prints-invoice', [App\Http\Controllers\Finance\PartnerController::class, 'printInvoice']);
