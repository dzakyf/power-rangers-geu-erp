<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\MobileAuthApiController;
use App\Http\Controllers\Api\V1\MobileDashboardApiController;
use App\Http\Controllers\Api\V1\MobileModuleApiController;
use App\Http\Controllers\CNS\ExportChecklistController;
use App\Http\Controllers\CNS\ExportInvoicingController;
use App\Http\Controllers\UserController;
use App\Support\Modules;

if (!function_exists('hcga_api_controller')) {
    function hcga_api_controller(string $class): string
    {
        return '\\App\\Http\\Controllers\\'.'HCGA\\'.$class;
    }
}

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

/*
|--------------------------------------------------------------------------
| Legacy API Routes (Backward Compatibility - auth:api)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:api')->group(function () {

    Route::get('/user', [UserController::class, 'AuthRouteAPI']);

    if (Modules::isEnabled('hcga')) {
        // Attendance / Absensi API (mobile & self-service)
        Route::get('/attendance/list', [hcga_api_controller('AttendancesController'), 'apiList']);
        Route::get('/attendance/{periode}/entries', [hcga_api_controller('AttendancesController'), 'apiEntriesByPeriode']);
        Route::get('/attendance/scan/today', [hcga_api_controller('AttendancesController'), 'apiTodayScan']);
        Route::post('/attendance/scan', [hcga_api_controller('AttendancesController'), 'apiScan']);
        Route::put('/attendance/scan/{id}', [hcga_api_controller('AttendancesController'), 'apiUpdateScan']);
        Route::patch('/attendance/scan/{id}', [hcga_api_controller('AttendancesController'), 'apiUpdateScan']);

        // Extended Attendance & Timesheet APIs (GPS Geofence, Clock-in/out, Requests)
        Route::post('/attendance/clock-in', [hcga_api_controller('AttendancesController'), 'apiClockIn']);
        Route::post('/attendance/clock-out', [hcga_api_controller('AttendancesController'), 'apiClockOut']);
        Route::get('/attendance/timesheet', [hcga_api_controller('AttendancesController'), 'apiTimesheet']);
        Route::post('/attendance/request/leave', [hcga_api_controller('AttendancesController'), 'apiSubmitLeave']);
        Route::post('/attendance/request/correction', [hcga_api_controller('AttendancesController'), 'apiSubmitCorrection']);

        // SPPD Mobile REST APIs (Native / Webview mobile)
        Route::get('/sppd/list', [hcga_api_controller('SppdMobileController'), 'apiList']);
        Route::get('/sppd/approval/queue', [hcga_api_controller('SppdMobileController'), 'apiApprovalQueue']);
        Route::get('/sppd/{id}', [hcga_api_controller('SppdMobileController'), 'apiDetail']);
        Route::post('/sppd/submit', [hcga_api_controller('SppdMobileController'), 'apiSubmit']);
        Route::post('/sppd/approval/process', [hcga_api_controller('SppdMobileController'), 'apiProcessApproval']);
    }
});

/*
|--------------------------------------------------------------------------
| Unified Mobile REST APIs v1 (Laravel Sanctum - Dual Mode: Stateful SPA + Bearer Token)
|--------------------------------------------------------------------------
*/
Route::prefix('v1/mobile')->middleware(['mobile.security'])->group(function () {

    // Public Auth Endpoint (Rate-Limited 5 attempts/minute)
    Route::post('/auth/login', [MobileAuthApiController::class, 'login'])
        ->middleware('throttle:5,1,mobile_login')
        ->name('api.v1.mobile.auth.login');

    // Protected Sanctum Endpoints
    Route::middleware(['auth:sanctum', 'active.user'])->group(function () {

        // Auth & Device Token Management
        Route::get('/auth/me', [MobileAuthApiController::class, 'me'])->name('api.v1.mobile.auth.me');
        Route::get('/auth/tokens', [MobileAuthApiController::class, 'tokens'])->name('api.v1.mobile.auth.tokens');
        Route::delete('/auth/tokens/{id}', [MobileAuthApiController::class, 'revokeToken'])->name('api.v1.mobile.auth.tokens.revoke');
        Route::post('/auth/logout', [MobileAuthApiController::class, 'logout'])->name('api.v1.mobile.auth.logout');
        Route::post('/auth/logout-all', [MobileAuthApiController::class, 'logoutAll'])->name('api.v1.mobile.auth.logout-all');

        // Unified Landing Dashboard Summary
        Route::get('/dashboard', [MobileDashboardApiController::class, 'summary'])
            ->middleware('ability:mobile:read')
            ->name('api.v1.mobile.dashboard');

        // 1. Attendance & Timesheet
        Route::prefix('attendance')->group(function () {
            Route::get('/summary', [MobileModuleApiController::class, 'attendanceSummary'])->middleware('ability:mobile:read');
            if (Modules::isEnabled('hcga')) {
                Route::get('/timesheet', [hcga_api_controller('AttendancesController'), 'apiTimesheet'])->middleware('ability:mobile:read');
            }
            Route::post('/clock-in', [MobileModuleApiController::class, 'attendanceClockIn'])->middleware('ability:mobile:write');
            Route::post('/clock-out', [MobileModuleApiController::class, 'attendanceClockOut'])->middleware('ability:mobile:write');
            Route::post('/leave', [MobileModuleApiController::class, 'attendanceLeave'])->middleware('ability:mobile:write');
            if (Modules::isEnabled('hcga')) {
                Route::post('/leave/cancel', [hcga_api_controller('AttendancesController'), 'apiCancelLeave'])->middleware('ability:mobile:write');
            }
            Route::post('/correction', [MobileModuleApiController::class, 'attendanceCorrection'])->middleware('ability:mobile:write');

            if (Modules::isEnabled('hcga')) {
                Route::post('/overtime/preview', [hcga_api_controller('OvertimeRequestController'), 'apiCalculatePreview'])->middleware('ability:mobile:read');
                Route::post('/overtime', [hcga_api_controller('OvertimeRequestController'), 'storeOvertimeRequest'])->middleware('ability:mobile:write');
                Route::get('/change-shift/schedule', [hcga_api_controller('ShiftExchangeController'), 'getScheduleByDate'])->middleware('ability:mobile:read');
                Route::post('/change-shift', [hcga_api_controller('ShiftExchangeController'), 'submitChangeShift'])->middleware('ability:mobile:write');
            }
        });

        // 2. Approval Inbox
        Route::prefix('approvals')->group(function () {
            Route::get('/', [MobileModuleApiController::class, 'approvalInbox'])->middleware('ability:mobile:read');
            Route::post('/{taskId}/act', [MobileModuleApiController::class, 'approvalAct'])->middleware('ability:mobile:approve');
        });

        // 3. SPPD
        Route::prefix('sppd')->group(function () {
            Route::get('/', [MobileModuleApiController::class, 'sppdList'])->middleware('ability:mobile:read');
            Route::get('/approval/queue', [MobileModuleApiController::class, 'sppdApprovalQueue'])->middleware('ability:mobile:approve');
            Route::get('/{id}', [MobileModuleApiController::class, 'sppdDetail'])->middleware('ability:mobile:read');
            Route::post('/', [MobileModuleApiController::class, 'sppdSubmit'])->middleware('ability:mobile:write');
            Route::post('/approval/process', [MobileModuleApiController::class, 'sppdProcessApproval'])->middleware('ability:mobile:approve');
        });

        // 4. Reimbursement
        Route::prefix('reimbursement')->group(function () {
            Route::get('/', [MobileModuleApiController::class, 'reimbursementList'])->middleware('ability:mobile:read');
            Route::get('/approval/queue', [MobileModuleApiController::class, 'reimbursementApprovalQueue'])->middleware('ability:mobile:approve');
            Route::get('/{id}', [MobileModuleApiController::class, 'reimbursementDetail'])->middleware('ability:mobile:read');
        });

        // 5. ESS (Employee Self-Service & Directory)
        Route::prefix('ess')->group(function () {
            Route::get('/profile', [MobileModuleApiController::class, 'essProfile'])->middleware('ability:mobile:read');
            Route::get('/directory', [MobileModuleApiController::class, 'essDirectory'])->middleware('ability:mobile:read');
            Route::post('/profile/update-request', [MobileModuleApiController::class, 'essSubmitUpdate'])->middleware('ability:mobile:write');
        });

        // 6. Announcements
        Route::prefix('announcements')->group(function () {
            Route::get('/', [MobileModuleApiController::class, 'announcements'])->middleware('ability:mobile:read');
            Route::get('/{id}', [MobileModuleApiController::class, 'announcementDetail'])->middleware('ability:mobile:read');
        });

        // 7. Company Guidelines
        Route::prefix('guidelines')->group(function () {
            Route::get('/', [MobileModuleApiController::class, 'guidelines'])->middleware('ability:mobile:read');
            Route::get('/{id}', [MobileModuleApiController::class, 'guidelineDetail'])->middleware('ability:mobile:read');
        });
    });
});

if (Modules::isEnabled('cns')) {
    // External Finance webhook for Export Checklist Payment verification
    Route::post('/cns/export-payment-verified', [ExportChecklistController::class, 'receiveFinancePayment']);

    // External FAT application endpoints for Export Invoicing (X-API-Key auth)
    Route::prefix('finance/export-invoicing')->group(function () {
        Route::get('/', [ExportInvoicingController::class, 'apiIndex']);
        Route::get('/{id}', [ExportInvoicingController::class, 'apiShow']);
        Route::get('/{id}/file/{type}', [ExportInvoicingController::class, 'apiDownloadFile']);
        Route::post('/{id}/approve', [ExportInvoicingController::class, 'apiApprove']);
        Route::post('/{id}/reject', [ExportInvoicingController::class, 'apiReject']);
    });
}
