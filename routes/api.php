<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HCGA\AttendancesController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->group(function () {

    Route::get('/user', [UserController::class, 'AuthRouteAPI']);

    // Attendance / Absensi API (mobile & self-service)
    Route::get('/attendance/list', [AttendancesController::class, 'apiList']);
    Route::get('/attendance/{periode}/entries', [AttendancesController::class, 'apiEntriesByPeriode']);
    Route::get('/attendance/scan/today', [AttendancesController::class, 'apiTodayScan']);
    Route::post('/attendance/scan', [AttendancesController::class, 'apiScan']);
    Route::put('/attendance/scan/{id}', [AttendancesController::class, 'apiUpdateScan']);
    Route::patch('/attendance/scan/{id}', [AttendancesController::class, 'apiUpdateScan']);

    // Extended Attendance & Timesheet APIs (GPS Geofence, Clock-in/out, Requests)
    Route::post('/attendance/clock-in', [AttendancesController::class, 'apiClockIn']);
    Route::post('/attendance/clock-out', [AttendancesController::class, 'apiClockOut']);
    Route::get('/attendance/timesheet', [AttendancesController::class, 'apiTimesheet']);
    Route::post('/attendance/request/leave', [AttendancesController::class, 'apiSubmitLeave']);
    Route::post('/attendance/request/correction', [AttendancesController::class, 'apiSubmitCorrection']);

    // SPPD Mobile REST APIs (Native / Webview mobile)
    Route::get('/sppd/list', [\App\Http\Controllers\HCGA\SppdMobileController::class, 'apiList']);
    Route::get('/sppd/approval/queue', [\App\Http\Controllers\HCGA\SppdMobileController::class, 'apiApprovalQueue']);
    Route::get('/sppd/{id}', [\App\Http\Controllers\HCGA\SppdMobileController::class, 'apiDetail']);
    Route::post('/sppd/submit', [\App\Http\Controllers\HCGA\SppdMobileController::class, 'apiSubmit']);
    Route::post('/sppd/approval/process', [\App\Http\Controllers\HCGA\SppdMobileController::class, 'apiProcessApproval']);
});

