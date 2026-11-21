<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HCGA\MasterApprovalController as HCGAMasterApprovalController;
use App\Http\Controllers\HCGA\MasterWorkCalendarController as HCGAMasterWorkCalendarController;
use App\Http\Controllers\HCGA\EmploymentFormController as HCGAEmploymentFormController;
use App\Http\Controllers\HCGA\TrainingController as HCGATrainingController;
use App\Http\Controllers\HCGA\TnaController as HCGATnaController;
use App\Http\Controllers\HCGA\TrainingProgramController as HCGATrainingProgramController;
use App\Http\Controllers\HCGA\TrainingTrainerController as HCGATrainingTrainerController;
use App\Http\Controllers\HCGA\TrainingEvaluationController as HCGATrainingEvaluationController;
use App\Http\Controllers\HCGA\TrainingSummaryController as HCGATrainingSummaryController;
use App\Http\Controllers\HCGA\TrainingCoachingController as HCGATrainingCoachingController;
use App\Http\Controllers\HCGA\RequisitionController as HCGARequisitionController;
use App\Http\Controllers\HCGA\RecruitmentController as HCGARecruitmentController;
use App\Http\Controllers\HCGA\RecruitmentStageController as HCGARecruitmentStageController;
use App\Http\Controllers\HCGA\PsikotestController as HCGAPsikotestController;
use App\Http\Controllers\HCGA\TechnicalTestController as HCGATechnicalTestController;
use App\Http\Controllers\HCGA\McuController as HCGAMcuController;
use App\Http\Controllers\HCGA\OfferingController as HCGAOfferingController;
use App\Http\Controllers\HCGA\InterviewController as HCGAInterviewController;
use App\Http\Controllers\HCGA\CandidateAnalysisController as HCGACandidateAnalysisController;
use App\Http\Controllers\HCGA\RecruitmentNewSubmitController as HCGARecruitmentNewSubmitController;
use App\Http\Controllers\HCGA\RecruitmentManagementController as HCGARecruitmentManagementController;
use App\Http\Controllers\HCGA\RecruitmentCandidateBatchController as HCGARecruitmentCandidateBatchController;
use App\Http\Controllers\HCGA\MasterKodeposController as HCGAMasterKodeposController;
use App\Http\Controllers\HCGA\OnboardingController as HrgaOnboardingController;
use App\Http\Controllers\HCGA\ProcurementController as HCGAProcurementController;
use App\Http\Controllers\HCGA\ProcurementApprovalController as HCGAProcurementApprovalController;
use App\Http\Controllers\HCGA\AssetFixingController as HCGAAssetFixingController;
use App\Http\Controllers\HCGA\AssetFixingApprovalController as HCGAAssetFixingApprovalController;
use App\Http\Controllers\HCGA\ApprovalInboxController as HCGAApprovalInboxController;
use App\Http\Controllers\HCGA\ApprovalPolicyController as HCGAApprovalPolicyController;
use App\Http\Controllers\HCGA\ApprovalPositionController as HCGAApprovalPositionController;
use App\Http\Controllers\HCGA\ApprovalDelegationController as HCGAApprovalDelegationController;
use App\Http\Controllers\HCGA\SppdController as HCGASppdController;
use App\Http\Controllers\HCGA\SppdApprovalController as HCGASppdApprovalController;
use App\Http\Controllers\HCGA\MasterSppdRegulationController as HCGAMasterSppdRegulationController;
use App\Http\Controllers\HCGA\StatusChangeController as HCGAStatusChangeController;
use App\Http\Controllers\HCGA\SettlementController as HCGASettlementController;
use App\Http\Controllers\HCGA\SettlementApprovalController as HCGASettlementApprovalController;
use App\Http\Controllers\HCGA\ReimbursementController as HCGAReimbursementController;
use App\Http\Controllers\HCGA\ReimbursementApprovalController as HCGAReimbursementApprovalController;
use App\Http\Controllers\HCGA\VehicleMaintenanceController as HCGAVehicleMaintenanceController;
use App\Http\Controllers\HCGA\VehicleMaintenanceApprovalController as HCGAVehicleMaintenanceApprovalController;
use App\Http\Controllers\HCGA\PermitLicenseController as HCGAPermitLicenseController;
use App\Http\Controllers\HCGA\EmailSettingsController as HCGAEmailSettingsController;
use App\Http\Controllers\HCGA\AnnouncementCmsController as HCGAAnnouncementCmsController;
use App\Http\Controllers\HCGA\AnnouncementMobileController as HCGAAnnouncementMobileController;
use App\Http\Controllers\HCGA\CompanyGuidelineCmsController as HCGACompanyGuidelineCmsController;
use App\Http\Controllers\HCGA\MobilePortalController as HCGAMobilePortalController;
use App\Http\Controllers\HCGA\MobilePayslipController as HCGAMobilePayslipController;
use App\Http\Controllers\HCGA\CompanyGuidelineMobileController as HCGACompanyGuidelineMobileController;
use App\Http\Controllers\HCGA\Performance\PerformanceKpiController as HCGAPerformanceKpiController;
use App\Http\Controllers\HCGA\Performance\PerformancePeriodController as HCGAPerformancePeriodController;
use App\Http\Controllers\HCGA\Performance\PerformanceReviewController as HCGAPerformanceReviewController;
use App\Http\Controllers\HCGA\Performance\PerformanceCompetencyController as HCGAPerformanceCompetencyController;
use App\Http\Controllers\HCGA\Performance\PerformancePipController as HCGAPerformancePipController;
use App\Http\Controllers\HCGA\GA\TicketController as HCGAGaTicketController;
use App\Http\Controllers\HCGA\GA\WorkOrderController as HCGAGaWorkOrderController;
use App\Http\Controllers\HCGA\GA\TicketMasterController as HCGAGaTicketMasterController;
use App\Http\Controllers\HCGA\GA\QrPointController as HCGAGaQrPointController;
use App\Http\Controllers\HCGA\GA\InventoryController as HCGAGaInventoryController;
use App\Http\Controllers\HCGA\GA\RoomBookingController as HCGAGaRoomBookingController;
use App\Http\Controllers\HCGA\GA\SecurityController as HCGAGaSecurityController;
use App\Http\Controllers\HCGA\GA\CleaningController as HCGAGaCleaningController;
use App\Http\Controllers\HCGA\AssetMovingController as HCGAAssetMovingController;
use App\Http\Controllers\HCGA\AssetMovingApprovalController as HCGAAssetMovingApprovalController;
use App\Http\Controllers\HCGA\AssetIncomingController as HCGAAssetIncomingController;
use App\Http\Controllers\HCGA\AssetIncomingApprovalController as HCGAAssetIncomingApprovalController;
use App\Http\Controllers\HCGA\AssetListController as HCGAAssetListController;
use App\Http\Controllers\HCGA\AssetHandoverController as HCGAAssetHandoverController;
use App\Http\Controllers\HCGA\AssetHandoverApprovalController as HCGAAssetHandoverApprovalController;
use App\Http\Controllers\HCGA\AssetHandoverReturnedController as HCGAAssetHandoverReturnedController;
use App\Http\Controllers\HCGA\AssetHandoverReturnedApprovalController as HCGAAssetHandoverReturnedApprovalController;
use App\Http\Controllers\HCGA\AssetDispossalController as HCGAAssetDispossalController;
use App\Http\Controllers\HCGA\AssetDispossalApprovalController as HCGAAssetDispossalApprovalController;
use App\Http\Controllers\HCGA\VehicleOperationsController as HCGAVehicleOperationsController;
use App\Http\Controllers\HCGA\VehicleManagementController as HCGAVehicleManagementController;
use App\Http\Controllers\HCGA\AssetMaintenanceScheduleController as HCGAAssetMaintenanceScheduleController;
use App\Http\Controllers\HCGA\AssetTicketingController as HCGAAssetTicketingController;
use App\Http\Controllers\HCGA\CarRentController as HCGACarRentController;
use App\Http\Controllers\HCGA\OrganizationChartController as HCGAOrganizationChartController;

// Mobile Portal Public Auth Routes (Hardened)
Route::prefix('mobile')->middleware(['mobile.security'])->group(function () {
    Route::get('/login', [\App\Http\Controllers\HCGA\MobilePortalController::class, 'showLogin'])->name('mobile.login');
    Route::post('/login', [\App\Http\Controllers\HCGA\MobilePortalController::class, 'login'])
        ->middleware('throttle:5,1,mobile_web_login')
        ->name('mobile.login.submit');
});

Route::get('hrd-training-certificate-verify/{token}', [HCGATrainingSummaryController::class, 'verifyCertificate'])
    ->name('hrd.training.certificate.verify');

Route::post('open-recruitment-submit', [HCGARecruitmentNewSubmitController::class, 'storePublic'])->middleware('throttle:10,1');
Route::get('api/master-kodepos/search', [HCGAMasterKodeposController::class, 'search']);
Route::get('api/master-kodepos/provinces', [HCGAMasterKodeposController::class, 'provinces']);
Route::get('api/master-kodepos/cities', [HCGAMasterKodeposController::class, 'cities']);
Route::get('api/master-kodepos/districts', [HCGAMasterKodeposController::class, 'districts']);
Route::get('api/master-kodepos/subdistricts', [HCGAMasterKodeposController::class, 'subDistricts']);
Route::get('api/master-kodepos/by-zipcode', [HCGAMasterKodeposController::class, 'byZipcode']);
