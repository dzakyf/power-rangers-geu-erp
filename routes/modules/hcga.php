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

    $hcPref = "hrd";
    Route::get("$hcPref-master-approval", [HCGAMasterApprovalController::class, 'index']);
    Route::get("xml-$hcPref-master-approval", [HCGAMasterApprovalController::class, 'indexXml']);
    Route::get("$hcPref-master-approval-detail", [HCGAMasterApprovalController::class, 'detail']);
    Route::get("$hcPref-master-approval-new", [HCGAMasterApprovalController::class, 'new']);
    Route::post("$hcPref-master-approval-detail-submit", [HCGAMasterApprovalController::class, 'detailSubmit']);
    Route::post("$hcPref-get-departments", [HCGAMasterApprovalController::class, 'getDepartmentsFromDirectorat']);
    Route::post("$hcPref-get-employee", [HCGAMasterApprovalController::class, 'getEmployeeFromDepartment']);
    Route::post("$hcPref-master-approval-new-submit", [HCGAMasterApprovalController::class, 'newSubmit']);

    Route::get("$hcPref-master-work-calendar", [HCGAMasterWorkCalendarController::class, 'index']);
    Route::get("xml-$hcPref-master-work-calendar", [HCGAMasterWorkCalendarController::class, 'indexXml']);
    Route::get("$hcPref-master-work-calendar-new", [HCGAMasterWorkCalendarController::class, 'create']);
    Route::post("$hcPref-master-work-calendar-new-submit", [HCGAMasterWorkCalendarController::class, 'store']);
    Route::get("$hcPref-master-work-calendar-edit", [HCGAMasterWorkCalendarController::class, 'edit']);
    Route::post("$hcPref-master-work-calendar-edit-submit", [HCGAMasterWorkCalendarController::class, 'update']);
    Route::get("$hcPref-master-work-calendar-detail", [HCGAMasterWorkCalendarController::class, 'detail']);
    Route::get("xml-$hcPref-master-work-calendar-detail", [HCGAMasterWorkCalendarController::class, 'detailXml']);
    Route::get("$hcPref-master-work-calendar-detail-shift", [HCGAMasterWorkCalendarController::class, 'shift']);
    Route::get("xml-$hcPref-master-work-calendar-shift", [HCGAMasterWorkCalendarController::class, 'shiftXml']);
    Route::post("master-work-calendar-detail-update", [HCGAMasterWorkCalendarController::class, 'detailUpdate']);
    Route::post("master-work-calendar-shift-update", [HCGAMasterWorkCalendarController::class, 'shiftUpdate']);
    
    // Master Shift & Roster Patterns
    Route::get("xml-$hcPref-master-shifts", [\App\Http\Controllers\HCGA\MasterShiftController::class, 'xmlShifts']);
    Route::get("xml-$hcPref-master-roster-patterns", [\App\Http\Controllers\HCGA\MasterShiftController::class, 'xmlRosterPatterns']);
    Route::post("$hcPref-master-shifts-store", [\App\Http\Controllers\HCGA\MasterShiftController::class, 'storeShift']);
    Route::post("$hcPref-master-roster-patterns-store", [\App\Http\Controllers\HCGA\MasterShiftController::class, 'storeRosterPattern']);

    // Roster Matrix Grid & Schedule Management
    Route::get("$hcPref-roster-schedule-matrix", [\App\Http\Controllers\HCGA\RosterScheduleMatrixController::class, 'index']);
    Route::post("$hcPref-roster-schedule-matrix-update", [\App\Http\Controllers\HCGA\RosterScheduleMatrixController::class, 'updateManualSchedule']);
    Route::post("$hcPref-roster-schedule-matrix-assign", [\App\Http\Controllers\HCGA\RosterScheduleMatrixController::class, 'assignPattern']);

    Route::get("$hcPref-recruitment-management", [HCGARecruitmentManagementController::class, 'index'])
        ->name('hrd-recruitment-management');
    Route::get("xml-$hcPref-recruitment-management", [HCGARecruitmentManagementController::class, 'indexXml'])
        ->name('xml-hrd-recruitment-management');
    Route::post("$hcPref-recruitment-management-columns-save", [HCGARecruitmentManagementController::class, 'saveColumns'])
        ->name('hrd-recruitment-management-columns-save');
    Route::get("xml-$hcPref-recruitment-pivot", [HCGARecruitmentManagementController::class, 'pivotXml'])
        ->name('xml-hrd-recruitment-pivot');

    Route::get("$hcPref-recruitment-dashboard", [HCGARequisitionController::class, 'dashboard'])
        ->name('hrd-recruitment-dashboard');

    Route::get("$hcPref-employee-form", function () {
        return view('humanresources.employment.employee-form');
    });
    Route::get("$hcPref-employee-form-print", [HCGAEmploymentFormController::class, 'printPerubahanStatus']);

    // === TNA (Training Need Analysis) ===
    Route::get("$hcPref-tna-index", [HCGATnaController::class, 'index']);
    Route::get("xml-$hcPref-tna-index", [HCGATnaController::class, 'indexXml']);
    Route::get("$hcPref-tna-create", [HCGATnaController::class, 'create']);
    Route::post("$hcPref-tna-store", [HCGATnaController::class, 'store']);
    Route::get("$hcPref-tna-detail", [HCGATnaController::class, 'detail']);
    Route::post("$hcPref-tna-submit-approval", [HCGATnaController::class, 'submitApproval']);

    // === Module & Program Training ===
    Route::get("$hcPref-training-program-index", [HCGATrainingProgramController::class, 'index']);
    Route::get("xml-$hcPref-training-program-index", [HCGATrainingProgramController::class, 'indexXml']);
    Route::get("$hcPref-training-program-form", [HCGATrainingProgramController::class, 'form']);
    Route::post("$hcPref-training-program-store", [HCGATrainingProgramController::class, 'store']);

    // === Master Pemateri / Trainer ===
    Route::get("$hcPref-training-trainer-index", [HCGATrainingTrainerController::class, 'index']);
    Route::get("xml-$hcPref-training-trainer-index", [HCGATrainingTrainerController::class, 'indexXml']);
    Route::get("$hcPref-training-trainer-form", [HCGATrainingTrainerController::class, 'form']);
    Route::post("$hcPref-training-trainer-store", [HCGATrainingTrainerController::class, 'store']);
    Route::post("$hcPref-training-trainer-toggle", [HCGATrainingTrainerController::class, 'toggleStatus']);

    // === Jadwal & Request Training ===
    $hcTrainingPref = "$hcPref-training";
    Route::get("$hcTrainingPref-index", [HCGATrainingController::class, 'index']);
    Route::get("xml-$hcTrainingPref-index", [HCGATrainingController::class, 'indexXml']);
    Route::get("xml-$hcTrainingPref-lists", [HCGATrainingController::class, 'indexXml']);
    Route::get("$hcTrainingPref-new", [HCGATrainingController::class, 'new']);
    Route::post("$hcTrainingPref-new-submit", [HCGATrainingController::class, 'newSubmit']);
    Route::get("$hcTrainingPref-detail", [HCGATrainingController::class, 'detail']);
    Route::post("$hcTrainingPref-detail-submit", [HCGATrainingController::class, 'detailSubmit']);
    Route::post("$hcTrainingPref-submit-approval", [HCGATrainingController::class, 'submitApproval']);
    Route::post("$hcTrainingPref-approve", [HCGATrainingController::class, 'approve']);
    Route::post("$hcTrainingPref-close", [HCGATrainingController::class, 'close']);
    Route::get("$hcTrainingPref-approval", [HCGATrainingController::class, 'approval']);

    // === Evaluasi Training (Hasil Karyawan, Pemateri, Materi) ===
    Route::get("$hcTrainingPref-evaluation-index", [HCGATrainingEvaluationController::class, 'index']);
    Route::get("$hcTrainingPref-evaluation-score", [HCGATrainingEvaluationController::class, 'scoreForm']);
    Route::post("$hcTrainingPref-evaluation-score-save", [HCGATrainingEvaluationController::class, 'saveScores']);
    Route::get("$hcTrainingPref-evaluation-feedback", [HCGATrainingEvaluationController::class, 'feedbackForm']);
    Route::post("$hcTrainingPref-evaluation-feedback-save", [HCGATrainingEvaluationController::class, 'saveFeedback']);

    // === Summary Training & Sertifikat Digital ===
    Route::get("$hcTrainingPref-summary-index", [HCGATrainingSummaryController::class, 'index']);
    Route::get("$hcTrainingPref-summary-detail", [HCGATrainingSummaryController::class, 'detail']);
    Route::post("$hcTrainingPref-summary-issue-certificates", [HCGATrainingSummaryController::class, 'issueCertificates']);
    Route::get("$hcTrainingPref-certificate-download/{certificateId}", [HCGATrainingSummaryController::class, 'downloadCertificate']);

    // === Coaching & Training Refreshment ===
    Route::get("$hcTrainingPref-coaching-index", [HCGATrainingCoachingController::class, 'index']);
    Route::get("$hcTrainingPref-coaching-form", [HCGATrainingCoachingController::class, 'form']);
    Route::post("$hcTrainingPref-coaching-store", [HCGATrainingCoachingController::class, 'store']);
    Route::get("$hcPref-onboarding-materials", [HrgaOnboardingController::class, 'index']);
    Route::get("xml-$hcPref-onboarding-materials", [HrgaOnboardingController::class, 'onboardingData']);
    Route::get("$hcPref-onboarding-materials-create", [HrgaOnboardingController::class, 'create']);
    Route::get("$hcPref-onboarding-materials-edit", [HrgaOnboardingController::class, 'edit']);
    Route::post("$hcPref-onboarding-materials-submit", [HrgaOnboardingController::class, 'store']);
    Route::post("$hcPref-onboarding-materials-update", [HrgaOnboardingController::class, 'update']);
    Route::post("$hcPref-onboarding-materials-delete", [HrgaOnboardingController::class, 'delete']);

    Route::post("$hcPref-recruitment-document-approval-detail-submit", [HCGARecruitmentController::class, 'recruitmentDocumentApproval']);


    // === Recruitment Stage Transition Routes ===
    // Verifikasi dokumen & transisi stage
    Route::get("$hcPref-recruitment-verify-document-list", [HCGARecruitmentStageController::class, 'verifyDocumentList'])
        ->name('hrd-recruitment-verify-document-list');
    Route::get("$hcPref-recruitment-verify-document/{id}", [HCGARecruitmentStageController::class, 'verifyDocumentPage'])
        ->name('hrd-recruitment-verify-document-page');
    Route::post("$hcPref-recruitment-verify-document", [HCGARecruitmentStageController::class, 'verifyDocument'])
        ->name('hrd-recruitment-verify-document');
    Route::post("$hcPref-recruitment-reject-document", [HCGARecruitmentStageController::class, 'rejectDocument']);
    
    // Psikotest Management Routes
    Route::get("$hcPref-recruitment-psikotest", [HCGAPsikotestController::class, 'index'])
        ->name('hrd-recruitment-psikotest');
    Route::get("xml-$hcPref-recruitment-psikotest", [HCGAPsikotestController::class, 'data'])
        ->name('xml-hrd-recruitment-psikotest');
    Route::get("$hcPref-recruitment-psikotest-detail", [HCGAPsikotestController::class, 'detail'])
        ->name('hrd-recruitment-psikotest-detail');
    Route::post("$hcPref-recruitment-psikotest-detail-submit", [HCGAPsikotestController::class, 'store'])
        ->name('hrd-recruitment-psikotest-detail-submit');

    // Technical Test Management Routes
    Route::get("$hcPref-recruitment-technical-test", [HCGATechnicalTestController::class, 'index'])
        ->name('hrd-recruitment-technical-test');
    Route::get("xml-$hcPref-recruitment-technical-test", [HCGATechnicalTestController::class, 'data'])
        ->name('xml-hrd-recruitment-technical-test');
    Route::get("$hcPref-recruitment-technical-test-detail", [HCGATechnicalTestController::class, 'detail'])
        ->name('hrd-recruitment-technical-test-detail');

    // MCU Management Routes
    Route::get("$hcPref-recruitment-mcu", [HCGAMcuController::class, 'index'])
        ->name('hrd-recruitment-mcu');
    Route::get("xml-$hcPref-recruitment-mcu", [HCGAMcuController::class, 'data'])
        ->name('xml-hrd-recruitment-mcu');
    Route::get("$hcPref-recruitment-mcu-detail", [HCGAMcuController::class, 'detail'])
        ->name('hrd-recruitment-mcu-detail');

    // Offering Letter Management Routes
    Route::get("$hcPref-recruitment-offering", [HCGAOfferingController::class, 'index'])
        ->name('hrd-recruitment-offering');
    Route::get("xml-$hcPref-recruitment-offering", [HCGAOfferingController::class, 'data'])
        ->name('xml-hrd-recruitment-offering');
    Route::get("$hcPref-recruitment-offering-detail", [HCGAOfferingController::class, 'detail'])
        ->name('hrd-recruitment-offering-detail');
    Route::get("$hcPref-recruitment-offering-letter-pdf", [HCGAOfferingController::class, 'printPdf'])
        ->name('hrd-recruitment-offering-letter-pdf');
    Route::post("$hcPref-recruitment-offering-send-email", [HCGAOfferingController::class, 'sendEmail'])
        ->name('hrd-recruitment-offering-send-email');
    Route::get("$hcPref-recruitment-offering-preview-email", [HCGAOfferingController::class, 'previewEmail'])
        ->name('hrd-recruitment-offering-preview-email');
    
    Route::post("$hcPref-recruitment-approve-psikotest", [HCGARecruitmentStageController::class, 'approvePsikotest']);
    Route::post("$hcPref-recruitment-reject-psikotest", [HCGARecruitmentStageController::class, 'rejectPsikotest']);
    Route::post("$hcPref-recruitment-approve-interview-hr", [HCGARecruitmentStageController::class, 'approveInterviewHR']);
    Route::post("$hcPref-recruitment-reject-interview-hr", [HCGARecruitmentStageController::class, 'rejectInterviewHR']);
    Route::post("$hcPref-recruitment-approve-interview-user", [HCGARecruitmentStageController::class, 'approveInterviewUser']);
    Route::post("$hcPref-recruitment-reject-interview-user", [HCGARecruitmentStageController::class, 'rejectInterviewUser']);
    Route::post("$hcPref-recruitment-approve-technical-test", [HCGARecruitmentStageController::class, 'approveTechnicalTest']);
    Route::post("$hcPref-recruitment-reject-technical-test", [HCGARecruitmentStageController::class, 'rejectTechnicalTest']);
    Route::post("$hcPref-recruitment-approve-mcu", [HCGARecruitmentStageController::class, 'approveMCU']);
    Route::post("$hcPref-recruitment-reject-mcu", [HCGARecruitmentStageController::class, 'rejectMCU']);
    Route::post("$hcPref-recruitment-issue-offering-letter", [HCGARecruitmentStageController::class, 'issueOfferingLetter']);
    Route::post("$hcPref-recruitment-save-draft-offering", [HCGARecruitmentStageController::class, 'saveDraftOffering']);
    Route::post("$hcPref-recruitment-decline-offering", [HCGARecruitmentStageController::class, 'declineOffering']);
    Route::post("$hcPref-recruitment-decline-invitation", [HCGARecruitmentStageController::class, 'declineInvitation']);
    // === End Recruitment Stage Transition Routes ===

    // === End Candidate Submission Route ===

    $hcRequisitionPref = "$hcPref-requisition";
    Route::get("$hcRequisitionPref-prints", [HCGARequisitionController::class, 'print']);

    $hcProcurementPref = "$hcPref-procurement";
    Route::get("$hcProcurementPref-index", [HCGAProcurementController::class, 'index']);
    Route::get("xml-$hcProcurementPref-index", [HCGAProcurementController::class, 'indexXml']);
    Route::get("$hcProcurementPref-new", [HCGAProcurementController::class, 'new']);
    Route::post("$hcProcurementPref-new-submit", [HCGAProcurementController::class, 'newSubmit']);
    Route::post("$hcProcurementPref-new-update", [HCGAProcurementController::class, 'newUpdate']);
    Route::get("$hcProcurementPref-product", [HCGAProcurementController::class, 'product']);
    Route::get("xml-$hcProcurementPref-product", [HCGAProcurementController::class, 'productXml']);
    Route::get("$hcProcurementPref-detail", [HCGAProcurementController::class, 'detail']);
    Route::post("$hcProcurementPref-detail-submit", [HCGAProcurementController::class, 'detailSubmit']);
    Route::get("xml-$hcProcurementPref-detail", [HCGAProcurementController::class, 'detailXml']);
    Route::get("$hcProcurementPref-log", [HCGAProcurementController::class, 'log']);
    Route::get("xml-$hcProcurementPref-log", [HCGAProcurementController::class, 'logData']);
    Route::get("$hcProcurementPref-print-pr", [HCGAProcurementController::class, 'printPR']);
    Route::get("$hcProcurementPref-print-po", [HCGAProcurementController::class, 'printPO']);

    $hcProcurementApprovalPref = "$hcProcurementPref-approval";
    Route::get("$hcProcurementApprovalPref-index", [HCGAProcurementApprovalController::class, 'index']);
    Route::get("$hcProcurementApprovalPref-data", [HCGAProcurementApprovalController::class, 'data']);
    Route::get("$hcProcurementApprovalPref-detail", [HCGAProcurementApprovalController::class, 'detail']);
    Route::post("$hcProcurementApprovalPref-detail-submit", [HCGAProcurementApprovalController::class, 'approval']);

    $hcAssetMovingPref = "$hcPref-asset-moving";
    Route::get("$hcAssetMovingPref-index", [HCGAAssetMovingController::class, 'index']);
    Route::get("$hcAssetMovingPref-data", [HCGAAssetMovingController::class, 'data']);
    Route::get("$hcAssetMovingPref-create", [HCGAAssetMovingController::class, 'create']);
    Route::post("$hcAssetMovingPref-submit", [HCGAAssetMovingController::class, 'store']);
    Route::post("$hcAssetMovingPref-update", [HCGAAssetMovingController::class, 'update']);
    Route::get("$hcAssetMovingPref-detail", [HCGAAssetMovingController::class, 'detail']);
    Route::get("$hcAssetMovingPref-log", [HCGAAssetMovingController::class, 'log']);

    $hcAssetMovingApprovalPref = "$hcAssetMovingPref-approval";
    Route::get("$hcAssetMovingApprovalPref-index", [HCGAAssetMovingApprovalController::class, 'index']);
    Route::get("$hcAssetMovingApprovalPref-data", [HCGAAssetMovingApprovalController::class, 'data']);
    Route::get("$hcAssetMovingApprovalPref-detail", [HCGAAssetMovingApprovalController::class, 'detail']);
    Route::post("$hcAssetMovingApprovalPref-approval", [HCGAAssetMovingApprovalController::class, 'approval']);

    $hcAssetIncomingPref = "$hcPref-asset-incoming";
    Route::get("$hcAssetIncomingPref-index", [HCGAAssetIncomingController::class, 'index']);
    Route::get("$hcAssetIncomingPref-data", [HCGAAssetIncomingController::class, 'data']);
    Route::get("$hcAssetIncomingPref-create", [HCGAAssetIncomingController::class, 'create']);
    Route::post("$hcAssetIncomingPref-submit", [HCGAAssetIncomingController::class, 'store']);
    Route::get("$hcAssetIncomingPref-detail", [HCGAAssetIncomingController::class, 'detail']);
    Route::get("$hcAssetIncomingPref-log", [HCGAAssetIncomingController::class, 'log']);

    $hcAssetIncomingApprovalPref = "$hcAssetIncomingPref-approval";
    Route::get("$hcAssetIncomingApprovalPref-index", [HCGAAssetIncomingApprovalController::class, 'index']);
    Route::get("$hcAssetIncomingApprovalPref-data", [HCGAAssetIncomingApprovalController::class, 'data']);
    Route::get("$hcAssetIncomingApprovalPref-detail", [HCGAAssetIncomingApprovalController::class, 'detail']);
    Route::post("$hcAssetIncomingApprovalPref-submit", [HCGAAssetIncomingApprovalController::class, 'approval']);

    $hcAssetHandoverPref = "$hcPref-asset-handover";
    Route::get("$hcAssetHandoverPref-index", [HCGAAssetHandoverController::class, 'index']);
    Route::get("$hcAssetHandoverPref-data", [HCGAAssetHandoverController::class, 'data']);
    Route::get("$hcAssetHandoverPref-product", [HCGAAssetHandoverController::class, 'product']);
    Route::get("$hcAssetHandoverPref-product-data", [HCGAAssetHandoverController::class, 'productData']);
    Route::get("$hcAssetHandoverPref-new", [HCGAAssetHandoverController::class, 'new']);
    Route::post("$hcAssetHandoverPref-new-submit", [HCGAAssetHandoverController::class, 'store']);
    Route::post("$hcAssetHandoverPref-new-update", [HCGAAssetHandoverController::class, 'storeDetail']);
    Route::get("$hcAssetHandoverPref-detail", [HCGAAssetHandoverController::class, 'detail']);
    Route::get("$hcAssetHandoverPref-detail-data", [HCGAAssetHandoverController::class, 'detailData']);
    Route::post("$hcAssetHandoverPref-detail-submit", [HCGAAssetHandoverController::class, 'update']);
    Route::get("$hcAssetHandoverPref-log", [HCGAAssetHandoverController::class, 'log']);

    $hcAssetHandoverConfirmPref = "$hcAssetHandoverPref-confirmation";
    Route::get("$hcAssetHandoverConfirmPref-index", [HCGAAssetHandoverApprovalController::class, 'index']);
    Route::get("$hcAssetHandoverConfirmPref-data", [HCGAAssetHandoverApprovalController::class, 'data']);
    Route::get("$hcAssetHandoverConfirmPref-detail", [HCGAAssetHandoverApprovalController::class, 'detail']);
    Route::get("$hcAssetHandoverConfirmPref-detail-data", [HCGAAssetHandoverApprovalController::class, 'detailData']);
    Route::post("$hcAssetHandoverConfirmPref-submit", [HCGAAssetHandoverApprovalController::class, 'store']);

    $hcAssetHandoverReturnPref = "$hcAssetHandoverPref-returned";
    Route::get("$hcAssetHandoverReturnPref-index", [HCGAAssetHandoverReturnedController::class, 'index']);
    Route::get("$hcAssetHandoverReturnPref-data", [HCGAAssetHandoverReturnedController::class, 'data']);
    Route::get("$hcAssetHandoverReturnPref-detail", [HCGAAssetHandoverReturnedController::class, 'detail']);
    Route::get("$hcAssetHandoverReturnPref-detail-data", [HCGAAssetHandoverReturnedController::class, 'detailData']);
    Route::post("$hcAssetHandoverReturnPref-detail-submit", [HCGAAssetHandoverReturnedController::class, 'storeDetail']);

    $hcAssetHandoverReturnApprovePref = "$hcAssetHandoverReturnPref-approval";
    Route::get("$hcAssetHandoverReturnApprovePref-index", [HCGAAssetHandoverReturnedApprovalController::class, 'index']);
    Route::get("$hcAssetHandoverReturnApprovePref-data", [HCGAAssetHandoverReturnedApprovalController::class, 'data']);
    Route::get("$hcAssetHandoverReturnApprovePref-detail", [HCGAAssetHandoverReturnedApprovalController::class, 'detail']);
    Route::get("$hcAssetHandoverReturnApprovePref-detail-data", [HCGAAssetHandoverReturnedApprovalController::class, 'detailData']);
    Route::post("$hcAssetHandoverReturnApprovePref-submit", [HCGAAssetHandoverReturnedApprovalController::class, 'store']);

    $hcAssetDispossalPref = "$hcPref-asset-dispossal";
    Route::get("$hcAssetDispossalPref-index", [HCGAAssetDispossalController::class, 'index']);
    Route::get("$hcAssetDispossalPref-data", [HCGAAssetDispossalController::class, 'data']);
    Route::get("$hcAssetDispossalPref-new", [HCGAAssetDispossalController::class, 'new']);
    Route::post("$hcAssetDispossalPref-new-submit", [HCGAAssetDispossalController::class, 'store']);
    Route::get("$hcAssetDispossalPref-detail", [HCGAAssetDispossalController::class, 'detail']);
    Route::post("$hcAssetDispossalPref-detail-submit", [HCGAAssetDispossalController::class, 'update']);
    Route::get("$hcAssetDispossalPref-log", [HCGAAssetDispossalController::class, 'log']);

    $hcAssetFixingPref = "$hcPref-asset-fixing";
    Route::get("$hcAssetFixingPref-index", [HCGAAssetFixingController::class, 'index']);
    Route::get("$hcAssetFixingPref-data", [HCGAAssetFixingController::class, 'data']);
    Route::get("$hcAssetFixingPref-create", [HCGAAssetFixingController::class, 'create']);
    Route::post("$hcAssetFixingPref-submit", [HCGAAssetFixingController::class, 'store']);
    Route::get("$hcAssetFixingPref-detail", [HCGAAssetFixingController::class, 'detail']);

    $hcAssetFixingApprovalPref = "$hcAssetFixingPref-approval";
    Route::get("$hcAssetFixingApprovalPref-index", [HCGAAssetFixingApprovalController::class, 'index']);
    Route::get("$hcAssetFixingApprovalPref-data", [HCGAAssetFixingApprovalController::class, 'data']);
    Route::get("$hcAssetFixingApprovalPref-detail", [HCGAAssetFixingApprovalController::class, 'detail']);
    Route::post("$hcAssetFixingApprovalPref-approval", [HCGAAssetFixingApprovalController::class, 'approval']);

    $hcVmPref = "$hcPref-vehicle-maintenance";
    Route::get("$hcVmPref-index", [HCGAVehicleMaintenanceController::class, 'index']);
    Route::get("$hcVmPref-data", [HCGAVehicleMaintenanceController::class, 'data']);
    Route::get("$hcVmPref-create", [HCGAVehicleMaintenanceController::class, 'create']);
    Route::post("$hcVmPref-submit", [HCGAVehicleMaintenanceController::class, 'store']);
    Route::get("$hcVmPref-detail", [HCGAVehicleMaintenanceController::class, 'detail']);

    // -- Vehicle Maintenance Approval --
    $hcVmAppPref = "$hcPref-vehicle-maintenance-approval";
    Route::get("$hcVmAppPref-index", [HCGAVehicleMaintenanceApprovalController::class, 'index']);
    Route::get("$hcVmAppPref-data", [HCGAVehicleMaintenanceApprovalController::class, 'data']);
    Route::get("$hcVmAppPref-detail", [HCGAVehicleMaintenanceApprovalController::class, 'detail']);
    Route::post("$hcVmAppPref-approve", [HCGAVehicleMaintenanceApprovalController::class, 'approve']);

    // -- Permit & License --
    $hcPermitLicensePref = "$hcPref-permit-license";
    Route::get("$hcPermitLicensePref-index", [HCGAPermitLicenseController::class, 'index']);
    Route::get("$hcPermitLicensePref-data", [HCGAPermitLicenseController::class, 'data']);
    Route::get("$hcPermitLicensePref-detail", [HCGAPermitLicenseController::class, 'detail']);
    Route::post("$hcPermitLicensePref-save", [HCGAPermitLicenseController::class, 'save']);
    Route::post("$hcPermitLicensePref-delete", [HCGAPermitLicenseController::class, 'delete']);
    Route::get("$hcPermitLicensePref-document", [HCGAPermitLicenseController::class, 'document']);
    Route::get("$hcPermitLicensePref-download", [HCGAPermitLicenseController::class, 'download']);
    Route::post("$hcPermitLicensePref-sync-reminder", [HCGAPermitLicenseController::class, 'syncReminder']);
    Route::get("$hcPermitLicensePref-mail-settings", [HCGAPermitLicenseController::class, 'mailSettings']);
    Route::post("$hcPermitLicensePref-mail-settings-save", [HCGAPermitLicenseController::class, 'mailSettingsSave']);
    Route::get("$hcPermitLicensePref-mail-preview", [HCGAPermitLicenseController::class, 'mailPreview']);

    // -- Position-based approval workflow --
    $hcApprovalPref = "$hcPref-approval";
    Route::get("$hcApprovalPref-inbox", [HCGAApprovalInboxController::class, 'index']);
    Route::get("$hcApprovalPref-inbox-data", [HCGAApprovalInboxController::class, 'data']);
    Route::get("$hcApprovalPref-mobile", [HCGAApprovalInboxController::class, 'mobileIndex'])->name('approval.mobile.index');
    Route::get("$hcApprovalPref-mobile-detail/{id}", [HCGAApprovalInboxController::class, 'mobileDetail'])->name('approval.mobile.detail');
    Route::get("$hcApprovalPref-detail", [HCGAApprovalInboxController::class, 'detail']);
    Route::post("$hcApprovalPref-claim", [HCGAApprovalInboxController::class, 'claim']);
    Route::post("$hcApprovalPref-act", [HCGAApprovalInboxController::class, 'act']);
    Route::post("$hcApprovalPref-release", [HCGAApprovalInboxController::class, 'release']);
    Route::post("$hcApprovalPref-self-exception", [HCGAApprovalInboxController::class, 'grantSelfException']);
    Route::post("$hcApprovalPref-add-candidate", [HCGAApprovalInboxController::class, 'addCandidate']);
    Route::get("$hcApprovalPref-candidate-options", [HCGAApprovalInboxController::class, 'candidateOptions']);
    Route::get("$hcApprovalPref-escalation", [HCGAApprovalInboxController::class, 'escalation']);
    Route::get("$hcApprovalPref-escalation-data", [HCGAApprovalInboxController::class, 'escalationData']);
    Route::post("$hcApprovalPref-force-approve", [HCGAApprovalInboxController::class, 'forceApprove']);
    Route::post("$hcApprovalPref-resync", [HCGAApprovalInboxController::class, 'resync']);
    Route::get("$hcApprovalPref-policy", [HCGAApprovalPolicyController::class, 'index']);
    Route::get("$hcApprovalPref-policy-data", [HCGAApprovalPolicyController::class, 'data']);
    Route::get("$hcApprovalPref-policy-detail", [HCGAApprovalPolicyController::class, 'detail']);
    Route::post("$hcApprovalPref-policy-save", [HCGAApprovalPolicyController::class, 'save']);
    Route::post("$hcApprovalPref-policy-step-save", [HCGAApprovalPolicyController::class, 'saveStep']);
    Route::post("$hcApprovalPref-policy-step-delete", [HCGAApprovalPolicyController::class, 'deleteStep']);
    Route::post("$hcApprovalPref-policy-toggle", [HCGAApprovalPolicyController::class, 'toggle']);
    Route::get("$hcApprovalPref-position", [HCGAApprovalPositionController::class, 'index']);
    Route::get("$hcApprovalPref-position-data", [HCGAApprovalPositionController::class, 'data']);
    Route::get("$hcApprovalPref-position-detail", [HCGAApprovalPositionController::class, 'detail']);
    Route::post("$hcApprovalPref-position-save", [HCGAApprovalPositionController::class, 'save']);
    Route::post("$hcApprovalPref-position-toggle", [HCGAApprovalPositionController::class, 'toggle']);
    Route::post("$hcApprovalPref-position-sync", [HCGAApprovalPositionController::class, 'sync']);
    Route::get("$hcApprovalPref-delegation", [HCGAApprovalDelegationController::class, 'index']);
    Route::get("$hcApprovalPref-delegation-data", [HCGAApprovalDelegationController::class, 'data']);
    Route::post("$hcApprovalPref-delegation-save", [HCGAApprovalDelegationController::class, 'save']);
    Route::post("$hcApprovalPref-delegation-revoke", [HCGAApprovalDelegationController::class, 'revoke']);

    // -- Bagan struktur organisasi --
    Route::get("$hcPref-organization-chart", [HCGAOrganizationChartController::class, 'index']);
    Route::get("$hcPref-organization-chart-data", [HCGAOrganizationChartController::class, 'data']);
    Route::post("$hcPref-organization-chart-save-parent", [HCGAOrganizationChartController::class, 'saveParent']);

    // -- Form Perubahan Status (dipakai bersama oleh menu Mutation, Promotion & Perubahan Status) --
    $hcStatusChangePref = "$hcPref-status-change";
    Route::get("$hcStatusChangePref-index", [HCGAStatusChangeController::class, 'index']);
    Route::get("$hcStatusChangePref-data", [HCGAStatusChangeController::class, 'data']);
    Route::get("$hcStatusChangePref-new", [HCGAStatusChangeController::class, 'create']);
    Route::post("$hcStatusChangePref-new-submit", [HCGAStatusChangeController::class, 'store']);
    Route::get("$hcStatusChangePref-detail", [HCGAStatusChangeController::class, 'detail']);
    Route::get("$hcStatusChangePref-edit", [HCGAStatusChangeController::class, 'edit']);
    Route::post("$hcStatusChangePref-edit-submit", [HCGAStatusChangeController::class, 'update']);
    Route::post("$hcStatusChangePref-delete", [HCGAStatusChangeController::class, 'delete']);
    Route::post("$hcStatusChangePref-submit-approval", [HCGAStatusChangeController::class, 'submitApproval']);
    Route::post("$hcStatusChangePref-action", [HCGAStatusChangeController::class, 'actApproval']);
    Route::get("$hcStatusChangePref-print", [HCGAStatusChangeController::class, 'printForm']);
    Route::get("$hcStatusChangePref-employee-search", [HCGAStatusChangeController::class, 'employeeSearch']);
    Route::get("$hcStatusChangePref-proposer-search", [HCGAStatusChangeController::class, 'proposerSearch']);

    $hcCarRentPref = "$hcPref-car-rent";
    Route::get("$hcCarRentPref-index", [HCGACarRentController::class, 'index']);
    Route::match(['GET', 'POST'], "$hcCarRentPref-data", [HCGACarRentController::class, 'data']);
    Route::get("$hcCarRentPref-new", [HCGACarRentController::class, 'create']);
    Route::post("$hcCarRentPref-new-submit", [HCGACarRentController::class, 'store']);
    Route::get("$hcCarRentPref-detail", [HCGACarRentController::class, 'detail']);
    Route::get("$hcCarRentPref-edit", [HCGACarRentController::class, 'edit']);
    Route::post("$hcCarRentPref-edit-submit", [HCGACarRentController::class, 'update']);

    $hcAssetDispossalConfirmPref = "$hcAssetDispossalPref-confirmation";
    Route::get("$hcAssetDispossalConfirmPref-index", [HCGAAssetDispossalApprovalController::class, 'index']);
    Route::get("$hcAssetDispossalConfirmPref-data", [HCGAAssetDispossalApprovalController::class, 'data']);
    Route::get("$hcAssetDispossalConfirmPref-detail", [HCGAAssetDispossalApprovalController::class, 'detail']);
    Route::post("$hcAssetDispossalConfirmPref-submit", [HCGAAssetDispossalApprovalController::class, 'store']);


    $hcSppdPref = "$hcPref-sppd";
    Route::get("$hcSppdPref-index", [HCGASppdController::class, 'index']);
    Route::get("$hcSppdPref-data", [HCGASppdController::class, 'data']);
    Route::get("$hcSppdPref-new", [HCGASppdController::class, 'create']);
    Route::post("$hcSppdPref-new-submit", [HCGASppdController::class, 'store']);
    Route::get("$hcSppdPref-matrix", [HCGASppdController::class, 'getMatrixAllowance']);
    Route::get("$hcSppdPref-route-info", [HCGASppdController::class, 'routeInfo']);
    Route::get("$hcSppdPref-detail", [HCGASppdController::class, 'detail']);
    Route::get("$hcSppdPref-print", [HCGASppdController::class, 'printPengajuan']);
    Route::get("$hcSppdPref-print-approved", [HCGASppdController::class, 'printPersetujuan']);
    Route::post("$hcSppdPref-close", [HCGASppdController::class, 'close']);
    Route::post("$hcSppdPref-create-car-rent", [HCGASppdController::class, 'createCarRent']);

    $hcSppdMasterPref = "$hcSppdPref-master";
    Route::get("$hcSppdMasterPref-index", [HCGAMasterSppdRegulationController::class, 'index']);
    Route::get("$hcSppdMasterPref-job-levels", [HCGAMasterSppdRegulationController::class, 'getJobLevels']);
    Route::post("$hcSppdMasterPref-job-level-store", [HCGAMasterSppdRegulationController::class, 'storeJobLevel']);
    Route::post("$hcSppdMasterPref-job-level-update/{id}", [HCGAMasterSppdRegulationController::class, 'updateJobLevel']);
    Route::delete("$hcSppdMasterPref-job-level-delete/{id}", [HCGAMasterSppdRegulationController::class, 'deleteJobLevel']);

    Route::get("$hcSppdMasterPref-trip-types", [HCGAMasterSppdRegulationController::class, 'getTripTypes']);
    Route::post("$hcSppdMasterPref-trip-type-store", [HCGAMasterSppdRegulationController::class, 'storeTripType']);
    Route::post("$hcSppdMasterPref-trip-type-update/{id}", [HCGAMasterSppdRegulationController::class, 'updateTripType']);
    Route::delete("$hcSppdMasterPref-trip-type-delete/{id}", [HCGAMasterSppdRegulationController::class, 'deleteTripType']);

    Route::get("$hcSppdMasterPref-allowance-components", [HCGAMasterSppdRegulationController::class, 'getAllowanceComponents']);
    Route::get("$hcSppdMasterPref-allowances", [HCGAMasterSppdRegulationController::class, 'getAllowances']);
    Route::post("$hcSppdMasterPref-allowance-save", [HCGAMasterSppdRegulationController::class, 'saveAllowance']);
    
    Route::post("$hcSppdMasterPref-component-store", [HCGAMasterSppdRegulationController::class, 'storeComponent']);
    Route::post("$hcSppdMasterPref-component-update/{id}", [HCGAMasterSppdRegulationController::class, 'updateComponent']);
    Route::delete("$hcSppdMasterPref-component-delete/{id}", [HCGAMasterSppdRegulationController::class, 'deleteComponent']);

    Route::post("$hcSppdMasterPref-sync-json", [HCGAMasterSppdRegulationController::class, 'syncFromJson']);

    $hcSppdApprovalPref = "$hcSppdPref-approval";
    Route::get("$hcSppdApprovalPref-index", [HCGASppdApprovalController::class, 'index']);
    Route::get("$hcSppdApprovalPref-data", [HCGASppdApprovalController::class, 'data']);
    Route::get("$hcSppdApprovalPref-detail", [HCGASppdApprovalController::class, 'detail']);
    Route::post("$hcSppdApprovalPref-submit", [HCGASppdApprovalController::class, 'approval']);

    // SPPD Mobile Webview & Mobile Actions (Legacy URL redirects to /mobile/sppd*)
    Route::redirect('/sppd/mobile', '/mobile/sppd');
    Route::redirect('/sppd/mobile/form', '/mobile/sppd/form');
    Route::redirect('/sppd/mobile/detail', '/mobile/sppd/detail');
    Route::redirect('/sppd/mobile/approval', '/mobile/sppd/approval');

    $hcSettlementPref = "$hcPref-settlement";
    Route::get("$hcSettlementPref-index", [HCGASettlementController::class, 'index']);
    Route::get("$hcSettlementPref-data", [HCGASettlementController::class, 'data']);
    Route::get("$hcSettlementPref-new", [HCGASettlementController::class, 'create']);
    Route::post("$hcSettlementPref-new-submit", [HCGASettlementController::class, 'store']);
    Route::get("$hcSettlementPref-detail", [HCGASettlementController::class, 'detail']);
    Route::get("$hcSettlementPref-print", [HCGASettlementController::class, 'printSettlement']);
    Route::get("$hcSettlementPref-invoice", [HCGASettlementController::class, 'getInvoice']);
    Route::post("$hcSettlementPref-close", [HCGASettlementController::class, 'close']);

    $hcSettlementApprovalPref = "$hcSettlementPref-approval";
    Route::get("$hcSettlementApprovalPref-index", [HCGASettlementApprovalController::class, 'index']);
    Route::get("$hcSettlementApprovalPref-data", [HCGASettlementApprovalController::class, 'data']);
    Route::get("$hcSettlementApprovalPref-detail", [HCGASettlementApprovalController::class, 'detail']);
    Route::post("$hcSettlementApprovalPref-submit", [HCGASettlementApprovalController::class, 'approval']);

    $hcReimbursementPref = "$hcPref-reimbursement";
    Route::get("$hcReimbursementPref-index", [HCGAReimbursementController::class, 'index']);
    Route::get("$hcReimbursementPref-data", [HCGAReimbursementController::class, 'data']);
    Route::get("$hcReimbursementPref-new", [HCGAReimbursementController::class, 'create']);
    Route::post("$hcReimbursementPref-new-submit", [HCGAReimbursementController::class, 'store']);
    Route::get("$hcReimbursementPref-detail", [HCGAReimbursementController::class, 'detail']);
    Route::get("$hcReimbursementPref-attachment", [HCGAReimbursementController::class, 'downloadAttachment']);

    $hcReimbursementApprovalPref = "$hcReimbursementPref-approval";
    Route::get("$hcReimbursementApprovalPref-index", [HCGAReimbursementApprovalController::class, 'index']);
    Route::get("$hcReimbursementApprovalPref-data", [HCGAReimbursementApprovalController::class, 'data']);
    Route::get("$hcReimbursementApprovalPref-detail", [HCGAReimbursementApprovalController::class, 'detail']);
    Route::post("$hcReimbursementApprovalPref-submit", [HCGAReimbursementApprovalController::class, 'approve']);

    // Mobile Webview Reimbursement (Legacy URL redirects to /mobile/reimbursement*)
    Route::redirect('/reimbursement/mobile', '/mobile/reimbursement');
    Route::redirect('/reimbursement/mobile/form', '/mobile/reimbursement/form');
    Route::redirect('/reimbursement/mobile/detail', '/mobile/reimbursement/detail');
    Route::redirect('/reimbursement/mobile/approval', '/mobile/reimbursement/approval');

    // Mobile Employee Self-Service & HCGA Directory (Legacy URL redirects to /mobile/profile*)
    Route::redirect('/employee/profile', '/mobile/profile');
    Route::redirect('/employee/directory', '/mobile/directory');

    // News & Announcement (CMS Web & Mobile Feed)
    $hcAnnouncementPref = "$hcPref-announcement";
    Route::get("$hcAnnouncementPref-index", [HCGAAnnouncementCmsController::class, 'index'])->name('hrd.announcement.index');
    Route::get("$hcAnnouncementPref-data", [HCGAAnnouncementCmsController::class, 'data'])->name('hrd.announcement.data');
    Route::get("$hcAnnouncementPref-create", [HCGAAnnouncementCmsController::class, 'create'])->name('hrd.announcement.create');
    Route::post("$hcAnnouncementPref-store", [HCGAAnnouncementCmsController::class, 'store'])->name('hrd.announcement.store');
    Route::get("$hcAnnouncementPref-edit", [HCGAAnnouncementCmsController::class, 'edit'])->name('hrd.announcement.edit');
    Route::post("$hcAnnouncementPref-update", [HCGAAnnouncementCmsController::class, 'update'])->name('hrd.announcement.update');
    Route::get("$hcAnnouncementPref-detail", [HCGAAnnouncementCmsController::class, 'detail'])->name('hrd.announcement.detail');
    Route::post("$hcAnnouncementPref-delete", [HCGAAnnouncementCmsController::class, 'destroy'])->name('hrd.announcement.delete');

    // Mobile News & Announcement (Legacy URL redirects to /mobile/announcement*)
    Route::redirect('/announcement/mobile', '/mobile/announcement');
    Route::redirect('/announcement/mobile/create', '/mobile/announcement/create');

    // Corporate Culture & Regulation (Visi Misi, Core Value, Peraturan Perusahaan)
    $hcGuidelinePref = "$hcPref-company-guideline";
    Route::get("$hcGuidelinePref-index", [HCGACompanyGuidelineCmsController::class, 'index'])->name('hrd.company-guideline.index');
    Route::get("$hcGuidelinePref-data", [HCGACompanyGuidelineCmsController::class, 'data'])->name('hrd.company-guideline.data');
    Route::get("$hcGuidelinePref-create", [HCGACompanyGuidelineCmsController::class, 'create'])->name('hrd.company-guideline.create');
    Route::post("$hcGuidelinePref-store", [HCGACompanyGuidelineCmsController::class, 'store'])->name('hrd.company-guideline.store');
    Route::get("$hcGuidelinePref-edit", [HCGACompanyGuidelineCmsController::class, 'edit'])->name('hrd.company-guideline.edit');
    Route::post("$hcGuidelinePref-update", [HCGACompanyGuidelineCmsController::class, 'update'])->name('hrd.company-guideline.update');
    Route::get("$hcGuidelinePref-detail", [HCGACompanyGuidelineCmsController::class, 'detail'])->name('hrd.company-guideline.detail');
    Route::post("$hcGuidelinePref-delete", [HCGACompanyGuidelineCmsController::class, 'destroy'])->name('hrd.company-guideline.delete');

    // Mobile Corporate Culture & Regulation (Legacy URL redirects to /mobile/guideline*)
    Route::redirect('/company-guideline/mobile', '/mobile/guideline');

    // Performance Management (KPI, OKR, Period, Review, Competency, PIP)
    $hcPerfPref = "$hcPref-performance";

    // 1. Master Periode & Pengaturan Kebijakan
    Route::get("$hcPerfPref-period-index", [HCGAPerformancePeriodController::class, 'index'])->name('hrd.performance.period.index');
    Route::post("$hcPerfPref-period-store", [HCGAPerformancePeriodController::class, 'store'])->name('hrd.performance.period.store');
    Route::post("$hcPerfPref-period-update-status/{id}", [HCGAPerformancePeriodController::class, 'updateStatus'])->name('hrd.performance.period.update-status');
    Route::post("$hcPerfPref-settings-update", [HCGAPerformancePeriodController::class, 'updateSettings'])->name('hrd.performance.settings.update');
    Route::post("$hcPerfPref-dept-pic-store", [HCGAPerformancePeriodController::class, 'storeDeptPic'])->name('hrd.performance.dept-pic.store');
    Route::delete("$hcPerfPref-dept-pic-delete/{id}", [HCGAPerformancePeriodController::class, 'deleteDeptPic'])->name('hrd.performance.dept-pic.delete');

    // 2. Goal Sheet (KPI & OKR)
    Route::get("$hcPerfPref-kpi-index", [HCGAPerformanceKpiController::class, 'index'])->name('hrd.performance.kpi.index');
    Route::get("$hcPerfPref-kpi-data", [HCGAPerformanceKpiController::class, 'data'])->name('hrd.performance.kpi.data');
    Route::get("$hcPerfPref-kpi-new", [HCGAPerformanceKpiController::class, 'create'])->name('hrd.performance.kpi.new');
    Route::post("$hcPerfPref-kpi-new-submit", [HCGAPerformanceKpiController::class, 'store'])->name('hrd.performance.kpi.new-submit');
    Route::get("$hcPerfPref-kpi-edit/{id}", [HCGAPerformanceKpiController::class, 'edit'])->name('hrd.performance.kpi.edit');
    Route::post("$hcPerfPref-kpi-update/{id}", [HCGAPerformanceKpiController::class, 'update'])->name('hrd.performance.kpi.update');
    Route::get("$hcPerfPref-kpi-detail/{id?}", [HCGAPerformanceKpiController::class, 'detail'])->name('hrd.performance.kpi.detail');
    Route::post("$hcPerfPref-kpi-submit-approval/{id}", [HCGAPerformanceKpiController::class, 'submitApproval'])->name('hrd.performance.kpi.submit-approval');
    Route::post("$hcPerfPref-kpi-record-progress/{id}", [HCGAPerformanceKpiController::class, 'recordProgress'])->name('hrd.performance.kpi.record-progress');
    Route::post("$hcPerfPref-kpi-reschedule/{id}", [HCGAPerformanceKpiController::class, 'rescheduleGoal'])->name('hrd.performance.kpi.reschedule');

    // 3. Performance Review & Appraisal Cycles
    Route::get("$hcPerfPref-review-index", [HCGAPerformanceReviewController::class, 'index'])->name('hrd.performance.review.index');
    Route::get("$hcPerfPref-review-data", [HCGAPerformanceReviewController::class, 'data'])->name('hrd.performance.review.data');
    Route::get("$hcPerfPref-review-cycle-new", [HCGAPerformanceReviewController::class, 'createCycle'])->name('hrd.performance.review.cycle-new');
    Route::post("$hcPerfPref-review-cycle-store", [HCGAPerformanceReviewController::class, 'storeCycle'])->name('hrd.performance.review.cycle-store');
    Route::get("$hcPerfPref-review-cycle-edit/{id}", [HCGAPerformanceReviewController::class, 'editCycle'])->name('hrd.performance.review.cycle-edit');
    Route::post("$hcPerfPref-review-cycle-update/{id}", [HCGAPerformanceReviewController::class, 'updateCycle'])->name('hrd.performance.review.cycle-update');
    Route::post("$hcPerfPref-review-cycle-reschedule/{id}", [HCGAPerformanceReviewController::class, 'rescheduleCycle'])->name('hrd.performance.review.cycle-reschedule');

    // 3b. Evaluasi Probation & Kontrak
    Route::get("$hcPerfPref-probation-index", [App\Http\Controllers\HCGA\Performance\PerformanceProbationController::class, 'index'])->name('hrd.performance.probation.index');
    Route::post("$hcPerfPref-probation-initiate/{userId}", [App\Http\Controllers\HCGA\Performance\PerformanceProbationController::class, 'initiateSingleReview'])->name('hrd.performance.probation.initiate');
    Route::post("$hcPerfPref-review-generate/{id}", [HCGAPerformanceReviewController::class, 'generateReviews'])->name('hrd.performance.review.generate');
    Route::get("$hcPerfPref-review-detail/{id?}", [HCGAPerformanceReviewController::class, 'detail'])->name('hrd.performance.review.detail');
    Route::post("$hcPerfPref-review-update-kpi/{id}", [HCGAPerformanceReviewController::class, 'updateKpiRealization'])->name('hrd.performance.review.update-kpi');
    Route::post("$hcPerfPref-review-feedback/{id}", [HCGAPerformanceReviewController::class, 'submitFeedback'])->name('hrd.performance.review.feedback');
    Route::post("$hcPerfPref-review-finalize/{id}", [HCGAPerformanceReviewController::class, 'finalize'])->name('hrd.performance.review.finalize');
    Route::get("$hcPerfPref-review-pdf/{id}", [HCGAPerformanceReviewController::class, 'exportPdf'])->name('hrd.performance.review.pdf');

    // 4. Performance Competency & Soft Skill Versioning
    Route::get("$hcPerfPref-competency-index", [HCGAPerformanceCompetencyController::class, 'index'])->name('hrd.performance.competency.index');
    Route::post("$hcPerfPref-competency-version-draft", [HCGAPerformanceCompetencyController::class, 'createDraftVersion'])->name('hrd.performance.competency.version-draft');
    Route::post("$hcPerfPref-competency-version-publish/{versionId}", [HCGAPerformanceCompetencyController::class, 'publishVersion'])->name('hrd.performance.competency.version-publish');
    Route::post("$hcPerfPref-competency-version-scale/{versionId}", [HCGAPerformanceCompetencyController::class, 'updateVersionScale'])->name('hrd.performance.competency.version-scale');
    Route::post("$hcPerfPref-competency-store", [HCGAPerformanceCompetencyController::class, 'store'])->name('hrd.performance.competency.store');
    Route::post("$hcPerfPref-competency-update/{id}", [HCGAPerformanceCompetencyController::class, 'update'])->name('hrd.performance.competency.update');
    Route::post("$hcPerfPref-competency-toggle/{id}", [HCGAPerformanceCompetencyController::class, 'toggleActive'])->name('hrd.performance.competency.toggle');
    Route::delete("$hcPerfPref-competency-delete/{id}", [HCGAPerformanceCompetencyController::class, 'destroy'])->name('hrd.performance.competency.delete');

    // 5. Performance Improvement Plan (PIP)
    Route::get("$hcPerfPref-pip-index", [HCGAPerformancePipController::class, 'index'])->name('hrd.performance.pip.index');
    Route::get("$hcPerfPref-pip-data", [HCGAPerformancePipController::class, 'data'])->name('hrd.performance.pip.data');
    Route::get("$hcPerfPref-pip-new", [HCGAPerformancePipController::class, 'create'])->name('hrd.performance.pip.new');
    Route::post("$hcPerfPref-pip-store", [HCGAPerformancePipController::class, 'store'])->name('hrd.performance.pip.store');
    Route::get("$hcPerfPref-pip-detail/{id?}", [HCGAPerformancePipController::class, 'detail'])->name('hrd.performance.pip.detail');
    Route::post("$hcPerfPref-pip-action-store/{id}", [HCGAPerformancePipController::class, 'storeAction'])->name('hrd.performance.pip.action-store');
    Route::post("$hcPerfPref-pip-action-status/{actionId}", [HCGAPerformancePipController::class, 'updateActionStatus'])->name('hrd.performance.pip.action-status');
    Route::post("$hcPerfPref-pip-checkin/{id}", [HCGAPerformancePipController::class, 'recordCheckin'])->name('hrd.performance.pip.checkin');
    Route::post("$hcPerfPref-pip-training/{id}", [HCGAPerformancePipController::class, 'assignTraining'])->name('hrd.performance.pip.training');
    Route::post("$hcPerfPref-pip-evaluate/{id}", [HCGAPerformancePipController::class, 'evaluate'])->name('hrd.performance.pip.evaluate');

    $hcAssetPref = "$hcPref-asset";
    Route::get("$hcAssetPref-lists", [HCGAAssetListController::class, 'index']);
    Route::get("$hcAssetPref-lists-data", [HCGAAssetListController::class, 'data']);
    Route::get("$hcAssetPref-lists-new", [HCGAAssetListController::class, 'create']);
    Route::post("$hcAssetPref-lists-new-submit", [HCGAAssetListController::class, 'store']);
    Route::get("$hcAssetPref-lists-detail", [HCGAAssetListController::class, 'detail']);
    Route::post("$hcAssetPref-lists-detail-submit", [HCGAAssetListController::class, 'update']);
    Route::post("$hcAssetPref-lists-stnk-update", [HCGAAssetListController::class, 'updateStnk']);
    Route::post("$hcAssetPref-lists-kir-update", [HCGAAssetListController::class, 'updateKeur']);
    Route::post("$hcAssetPref-lists-photo-upload", [HCGAAssetListController::class, 'uploadPhoto']);
    Route::post("$hcAssetPref-lists-photo-delete", [HCGAAssetListController::class, 'deletePhoto']);
    Route::get("$hcAssetPref-lists-sub-category", [HCGAAssetListController::class, 'subCategory']);
    Route::get("$hcAssetPref-lists-sub-sub-category", [HCGAAssetListController::class, 'subSubCategory']);
    Route::post("$hcAssetPref-lists-new-from-po", [HCGAAssetListController::class, 'getDataFromPO']);
    Route::post("$hcAssetPref-lists-new-get-wh", [HCGAAssetListController::class, 'getDataFromWH']);

    // Vehicle Management: Master Kendaraan + Reminder STNK & KEUR + Pajak
    $hcVehMgmtPref = "$hcPref-vehicle-management";
    Route::get("$hcVehMgmtPref-index", [HCGAVehicleManagementController::class, 'index']);
    Route::get("$hcVehMgmtPref-data", [HCGAVehicleManagementController::class, 'data']);
    Route::get("$hcVehMgmtPref-reminders", [HCGAVehicleManagementController::class, 'reminders']);
    Route::post("$hcVehMgmtPref-sync-reminders", [HCGAVehicleManagementController::class, 'syncReminders']);
    Route::get("$hcVehMgmtPref-email-preview", [HCGAVehicleManagementController::class, 'previewEmailReminder']);
    Route::post("$hcVehMgmtPref-send-email-reminders", [HCGAVehicleManagementController::class, 'sendEmailReminders']);
    Route::get("$hcVehMgmtPref-tax-requests", [HCGAVehicleManagementController::class, 'taxRequests']);
    Route::post("$hcVehMgmtPref-tax-store", [HCGAVehicleManagementController::class, 'storeTaxRequest']);
    Route::post("$hcVehMgmtPref-tax-approval", [HCGAVehicleManagementController::class, 'approveTaxRequest']);
    Route::post("$hcVehMgmtPref-tax-force-approve", [HCGAVehicleManagementController::class, 'forceApproveTaxRequest']);
    Route::post("$hcVehMgmtPref-tax-resubmit", [HCGAVehicleManagementController::class, 'resubmitTaxRequest']);
    Route::post("$hcVehMgmtPref-tax-pay", [HCGAVehicleManagementController::class, 'payTaxRequest']);
    Route::get("$hcVehMgmtPref-tax-voucher", [HCGAVehicleManagementController::class, 'printTaxVoucher']);
    Route::post("$hcVehMgmtPref-fleet-approval", [HCGAVehicleManagementController::class, 'approveFleetVehicle']);

    // Asset Maintenance Schedule (Perawatan Aset)
    $hcMaintSchedPref = "$hcPref-asset-fixing-schedule";
    Route::get("$hcMaintSchedPref", [HCGAAssetMaintenanceScheduleController::class, 'index']);
    Route::post("$hcMaintSchedPref-store", [HCGAAssetMaintenanceScheduleController::class, 'store']);
    Route::post("$hcMaintSchedPref-approval", [HCGAAssetMaintenanceScheduleController::class, 'approval']);
    Route::post("$hcMaintSchedPref-resubmit", [HCGAAssetMaintenanceScheduleController::class, 'resubmit']);
    Route::post("$hcMaintSchedPref-confirm", [HCGAAssetMaintenanceScheduleController::class, 'confirmSchedule']);
    Route::post("$hcMaintSchedPref-start", [HCGAAssetMaintenanceScheduleController::class, 'startExecution']);
    Route::post("$hcMaintSchedPref-complete", [HCGAAssetMaintenanceScheduleController::class, 'completeExecution']);

    // Unified Asset Ticketing & Lifecycle Hub (Kerusakan, Pengadaan, Pemindahan, Pemusnahan)
    $hcAssetTicketPref = "$hcPref-asset-ticketing";
    Route::get("$hcAssetTicketPref-index", [HCGAAssetTicketingController::class, 'index']);
    Route::get("$hcAssetTicketPref-data", [HCGAAssetTicketingController::class, 'data']);
    Route::post("$hcAssetTicketPref-store", [HCGAAssetTicketingController::class, 'store']);
    Route::post("$hcAssetTicketPref-escalate", [HCGAAssetTicketingController::class, 'escalate']);
    Route::get("$hcAssetTicketPref-detail", [HCGAAssetTicketingController::class, 'detail']);

    // Finance Approval & Voucher (Human Resources & GA)

    // Vehicle Operations: Logbook & Fuel Tracking (Controller based)
    $vehOpsPref = "$hcPref-vehicle-operations";
    Route::get("$vehOpsPref", [HCGAVehicleOperationsController::class, 'index']);
    Route::get("$vehOpsPref-logbook-data", [HCGAVehicleOperationsController::class, 'logbookData']);
    Route::get("$vehOpsPref-fuel-data", [HCGAVehicleOperationsController::class, 'fuelData']);
    Route::get("$vehOpsPref-kpi-data", [HCGAVehicleOperationsController::class, 'kpiData']);
    Route::get("$vehOpsPref-vehicle-info", [HCGAVehicleOperationsController::class, 'vehicleInfo']);
    Route::post("$vehOpsPref-logbook-submit", [HCGAVehicleOperationsController::class, 'storeLogbook']);
    Route::post("$vehOpsPref-logbook-finish", [HCGAVehicleOperationsController::class, 'finishLogbook']);
    Route::post("$vehOpsPref-logbook-approval", [HCGAVehicleOperationsController::class, 'approveLogbook']);
    Route::post("$vehOpsPref-logbook-delete", [HCGAVehicleOperationsController::class, 'deleteLogbook']);
    Route::post("$vehOpsPref-fuel-submit", [HCGAVehicleOperationsController::class, 'storeFuel']);
    Route::post("$vehOpsPref-fuel-delete", [HCGAVehicleOperationsController::class, 'deleteFuel']);
    Route::get("$vehOpsPref-export-logbook", [HCGAVehicleOperationsController::class, 'exportLogbook']);
    Route::get("$vehOpsPref-export-fuel", [HCGAVehicleOperationsController::class, 'exportFuel']);
    Route::get("/xml-vehicle-logbook", [HCGAVehicleOperationsController::class, 'logbookData']);
    Route::get("/xml-vehicle-fuel", [HCGAVehicleOperationsController::class, 'fuelData']);

    // -- General Affairs: Ticketing & Work Orders --
    $gaPref = "$hcPref-ga";
    Route::get("$gaPref-ticket-index", [HCGAGaTicketController::class, 'index']);
    Route::get("$gaPref-ticket-data", [HCGAGaTicketController::class, 'data']);
    Route::get("$gaPref-ticket-create", [HCGAGaTicketController::class, 'create']);
    Route::post("$gaPref-ticket-submit", [HCGAGaTicketController::class, 'store']);
    Route::get("$gaPref-ticket-detail", [HCGAGaTicketController::class, 'detail']);
    Route::post("$gaPref-ticket-triage", [HCGAGaTicketController::class, 'triage']);
    Route::post("$gaPref-ticket-assign", [HCGAGaTicketController::class, 'assign']);
    Route::post("$gaPref-ticket-status", [HCGAGaTicketController::class, 'transition']);
    Route::post("$gaPref-ticket-comment", [HCGAGaTicketController::class, 'comment']);
    Route::get("$gaPref-ticket-options", [HCGAGaTicketController::class, 'categoryOptionsJson']);
    Route::get("$gaPref-my-assignment-index", [HCGAGaTicketController::class, 'myAssignments']);
    Route::get("$gaPref-my-assignment-data", [HCGAGaTicketController::class, 'myAssignmentData']);
    Route::get("$gaPref-ticket-qr", [HCGAGaTicketController::class, 'qr']);
    Route::post("$gaPref-ticket-sla-scan", [HCGAGaTicketController::class, 'slaScan']);

    Route::get("$gaPref-work-order-index", [HCGAGaWorkOrderController::class, 'index']);
    Route::get("$gaPref-work-order-data", [HCGAGaWorkOrderController::class, 'data']);
    Route::get("$gaPref-work-order-create", [HCGAGaWorkOrderController::class, 'create']);
    Route::post("$gaPref-work-order-submit", [HCGAGaWorkOrderController::class, 'store']);
    Route::get("$gaPref-work-order-detail", [HCGAGaWorkOrderController::class, 'detail']);
    Route::post("$gaPref-work-order-status", [HCGAGaWorkOrderController::class, 'transition']);
    Route::post("$gaPref-work-order-approval-submit", [HCGAGaWorkOrderController::class, 'submitApproval']);
    Route::post("$gaPref-work-order-approval-sync", [HCGAGaWorkOrderController::class, 'syncApproval']);
    Route::post("$gaPref-work-order-cost-add", [HCGAGaWorkOrderController::class, 'addCost']);

    Route::get("$gaPref-ticket-master-index", [HCGAGaTicketMasterController::class, 'index']);
    Route::get("$gaPref-ticket-master-category-data", [HCGAGaTicketMasterController::class, 'categoryData']);
    Route::post("$gaPref-ticket-master-category-save", [HCGAGaTicketMasterController::class, 'saveCategory']);
    Route::post("$gaPref-ticket-master-category-delete", [HCGAGaTicketMasterController::class, 'deleteCategory']);
    Route::get("$gaPref-ticket-master-sla-data", [HCGAGaTicketMasterController::class, 'slaData']);
    Route::post("$gaPref-ticket-master-sla-save", [HCGAGaTicketMasterController::class, 'saveSla']);
    Route::get("$gaPref-ticket-master-team-data", [HCGAGaTicketMasterController::class, 'teamData']);
    Route::post("$gaPref-ticket-master-team-save", [HCGAGaTicketMasterController::class, 'saveTeam']);
    Route::get("$gaPref-ticket-master-member-data", [HCGAGaTicketMasterController::class, 'memberData']);
    Route::post("$gaPref-ticket-master-member-save", [HCGAGaTicketMasterController::class, 'saveMember']);
    Route::post("$gaPref-ticket-master-member-delete", [HCGAGaTicketMasterController::class, 'deleteMember']);
    Route::get("$gaPref-ticket-master-routing-data", [HCGAGaTicketMasterController::class, 'routingData']);
    Route::post("$gaPref-ticket-master-routing-save", [HCGAGaTicketMasterController::class, 'saveRouting']);
    Route::get("$gaPref-ticket-master-options", [HCGAGaTicketMasterController::class, 'options']);

    Route::get("$gaPref-qr-point-index", [HCGAGaQrPointController::class, 'index']);
    Route::get("$gaPref-qr-point-data", [HCGAGaQrPointController::class, 'data']);
    Route::post("$gaPref-qr-point-save", [HCGAGaQrPointController::class, 'save']);
    Route::post("$gaPref-qr-point-delete", [HCGAGaQrPointController::class, 'delete']);
    Route::get("$gaPref-qr-point-image", [HCGAGaQrPointController::class, 'image']);
    Route::get("$gaPref-qr-point-print", [HCGAGaQrPointController::class, 'printSheet']);

    // -- General Affairs: Inventory Phase 1 --
    Route::get("$gaPref-inventory-low-stock", [HCGAGaInventoryController::class, 'lowStockIndex']);
    Route::get("$gaPref-inventory-low-stock-data", [HCGAGaInventoryController::class, 'lowStockData']);
    Route::get("$gaPref-inventory-minimum-stock", [HCGAGaInventoryController::class, 'minimumStockIndex']);
    Route::get("$gaPref-inventory-minimum-stock-data", [HCGAGaInventoryController::class, 'minimumStockData']);
    Route::post("$gaPref-inventory-minimum-stock-save", [HCGAGaInventoryController::class, 'saveMinimumStock']);
    Route::get("$gaPref-inventory-ledger", [HCGAGaInventoryController::class, 'ledgerIndex']);
    Route::get("$gaPref-inventory-ledger-data", [HCGAGaInventoryController::class, 'ledgerData']);
    Route::post("$gaPref-inventory-opening-backfill", [HCGAGaInventoryController::class, 'backfillOpening']);
    Route::get("$gaPref-inventory-cutoff", [HCGAGaInventoryController::class, 'cutoffIndex']);
    Route::get("$gaPref-inventory-cutoff-data", [HCGAGaInventoryController::class, 'cutoffData']);
    Route::get("$gaPref-inventory-ytd", [HCGAGaInventoryController::class, 'ytdIndex']);
    Route::get("$gaPref-inventory-ytd-data", [HCGAGaInventoryController::class, 'ytdData']);
    Route::post("$gaPref-inventory-request-reversal", [HCGAGaInventoryController::class, 'reverseStockRequest']);
    Route::post("$gaPref-inventory-request-handover", [HCGAGaInventoryController::class, 'confirmHandover']);
    Route::get("$gaPref-inventory-transfer-stock", [HCGAGaInventoryController::class, 'transferStockIndex']);
    Route::post("$gaPref-inventory-transfer-stock-save", [HCGAGaInventoryController::class, 'transferStockSave']);
    Route::get("$gaPref-inventory-reusable", [HCGAGaInventoryController::class, 'reusableIndex']);
    Route::get("$gaPref-inventory-reusable-unit-data", [HCGAGaInventoryController::class, 'reusableUnitData']);
    Route::get("$gaPref-inventory-reusable-loan-data", [HCGAGaInventoryController::class, 'reusableLoanData']);
    Route::post("$gaPref-inventory-reusable-unit-save", [HCGAGaInventoryController::class, 'reusableSaveUnit']);
    Route::post("$gaPref-inventory-reusable-loan-save", [HCGAGaInventoryController::class, 'reusableSaveLoan']);
    Route::post("$gaPref-inventory-reusable-return-save", [HCGAGaInventoryController::class, 'reusableSaveReturn']);
    Route::get("$gaPref-inventory-adjustment", [HCGAGaInventoryController::class, 'adjustmentIndex']);
    Route::get("$gaPref-inventory-adjustment-data", [HCGAGaInventoryController::class, 'adjustmentData']);
    Route::post("$gaPref-inventory-adjustment-save", [HCGAGaInventoryController::class, 'saveAdjustment']);
    Route::post("$gaPref-inventory-generate-pr", [HCGAGaInventoryController::class, 'generatePurchaseRequestFromLowStock']);

    // -- General Affairs: Room Booking --
    Route::get("$gaPref-room-booking-index", [HCGAGaRoomBookingController::class, 'index']);
    Route::get("$gaPref-room-data", [HCGAGaRoomBookingController::class, 'roomData']);
    Route::get("$gaPref-room-booking-data", [HCGAGaRoomBookingController::class, 'bookingData']);
    Route::post("$gaPref-room-save", [HCGAGaRoomBookingController::class, 'saveRoom']);
    Route::post("$gaPref-room-booking-save", [HCGAGaRoomBookingController::class, 'saveBooking']);
    Route::post("$gaPref-room-booking-cancel", [HCGAGaRoomBookingController::class, 'cancelBooking']);

    // -- General Affairs: Security & Visitor Management --
    Route::get("$gaPref-security-index", [HCGAGaSecurityController::class, 'index']);
    Route::get("$gaPref-security-visitor-data", [HCGAGaSecurityController::class, 'visitorData']);
    Route::post("$gaPref-security-visitor-entry", [HCGAGaSecurityController::class, 'saveVisitorEntry']);
    Route::post("$gaPref-security-visitor-exit", [HCGAGaSecurityController::class, 'saveVisitorExit']);
    Route::get("$gaPref-security-incident-data", [HCGAGaSecurityController::class, 'incidentData']);
    Route::post("$gaPref-security-incident-save", [HCGAGaSecurityController::class, 'saveIncident']);
    Route::post("$gaPref-security-incident-resolve", [HCGAGaSecurityController::class, 'resolveIncident']);
    Route::post("$gaPref-security-incident-ticket", [HCGAGaSecurityController::class, 'createFollowUpTicket']);

    // -- General Affairs: Cleaning Management --
    Route::get("$gaPref-cleaning-index", [HCGAGaCleaningController::class, 'index']);
    Route::get("$gaPref-cleaning-area-data", [HCGAGaCleaningController::class, 'areaData']);
    Route::post("$gaPref-cleaning-area-save", [HCGAGaCleaningController::class, 'saveArea']);
    Route::get("$gaPref-cleaning-schedule-data", [HCGAGaCleaningController::class, 'scheduleData']);
    Route::post("$gaPref-cleaning-schedule-save", [HCGAGaCleaningController::class, 'saveSchedule']);
    Route::post("$gaPref-cleaning-generate-tasks", [HCGAGaCleaningController::class, 'generateTasks']);
    Route::get("$gaPref-cleaning-task-data", [HCGAGaCleaningController::class, 'taskData']);
    Route::get("$gaPref-cleaning-task-checklist-template", [HCGAGaCleaningController::class, 'taskChecklistTemplate']);
    Route::post("$gaPref-cleaning-task-start", [HCGAGaCleaningController::class, 'startTask']);
    Route::post("$gaPref-cleaning-task-complete", [HCGAGaCleaningController::class, 'completeTask']);
    Route::get("$gaPref-cleaning-inspection-data", [HCGAGaCleaningController::class, 'inspectionData']);
    Route::get("$gaPref-cleaning-inspection-detail", [HCGAGaCleaningController::class, 'inspectionDetail']);
    Route::post("$gaPref-cleaning-inspection-save", [HCGAGaCleaningController::class, 'saveInspection']);
    Route::post("$gaPref-cleaning-reinspection-save", [HCGAGaCleaningController::class, 'saveReinspection']);
    Route::get("$gaPref-cleaning-area-qr-image", [HCGAGaCleaningController::class, 'areaQrImage']);
    Route::get("$gaPref-cleaning-area-print-qr", [HCGAGaCleaningController::class, 'printAreaQr']);

    // -- General email settings (SMTP, sender, recipients, test, log) --
    $creEmailSettingsPref = "credential-email-settings";
    Route::get("$creEmailSettingsPref-index", [HCGAEmailSettingsController::class, 'index']);
    Route::post("$creEmailSettingsPref-save", [HCGAEmailSettingsController::class, 'save']);
    Route::post("$creEmailSettingsPref-recipient-save", [HCGAEmailSettingsController::class, 'recipientSave']);
    Route::post("$creEmailSettingsPref-recipient-toggle", [HCGAEmailSettingsController::class, 'recipientToggle']);
    Route::post("$creEmailSettingsPref-recipient-delete", [HCGAEmailSettingsController::class, 'recipientDelete']);
    Route::post("$creEmailSettingsPref-test", [HCGAEmailSettingsController::class, 'test']);

    // -- HCGA Exit Clearance & Resignation Workflow --
    Route::get("$hcPref-exit-clearance-index", [\App\Http\Controllers\HCGA\ExitClearanceController::class, 'index'])->name('hrd-exit-clearance-index');
    Route::get("$hcPref-exit-clearance-resignation-create", [\App\Http\Controllers\HCGA\ExitClearanceController::class, 'createResignation'])->name('hrd-exit-clearance-resignation-create');
    Route::post("$hcPref-exit-clearance-resignation-store", [\App\Http\Controllers\HCGA\ExitClearanceController::class, 'storeResignation'])->name('hrd-exit-clearance-resignation-store');
    Route::post("$hcPref-exit-clearance-resignation/{id}/approve", [\App\Http\Controllers\HCGA\ExitClearanceController::class, 'approveResignation'])->name('hrd-exit-clearance-resignation-approve');
    Route::post("$hcPref-exit-clearance-resignation/{id}/reject", [\App\Http\Controllers\HCGA\ExitClearanceController::class, 'rejectResignation'])->name('hrd-exit-clearance-resignation-reject');
    Route::get("$hcPref-exit-clearance-form/{id}", [\App\Http\Controllers\HCGA\ExitClearanceController::class, 'formClearance'])->name('hrd-exit-clearance-form');
    Route::post("$hcPref-exit-clearance-form/{id}/submit", [\App\Http\Controllers\HCGA\ExitClearanceController::class, 'submitClearance'])->name('hrd-exit-clearance-form-submit');
    Route::get("$hcPref-exit-clearance-view/{id}", [\App\Http\Controllers\HCGA\ExitClearanceController::class, 'viewClearance'])->name('hrd-exit-clearance-view');
    Route::post("$hcPref-exit-clearance-interview/{id}/save", [\App\Http\Controllers\HCGA\ExitClearanceController::class, 'recordInterview'])->name('hrd-exit-clearance-interview-save');
    Route::post("$hcPref-exit-clearance-manager/{id}/approve", [\App\Http\Controllers\HCGA\ExitClearanceController::class, 'approveByManager'])->name('hrd-exit-clearance-manager-approve');
    Route::post("$hcPref-exit-clearance-hcga/{id}/verify", [\App\Http\Controllers\HCGA\ExitClearanceController::class, 'verifyHcga'])->name('hrd-exit-clearance-hcga-verify');
    Route::get("$hcPref-exit-clearance-pdf/{id}", [\App\Http\Controllers\HCGA\ExitClearanceController::class, 'downloadPdf'])->name('hrd-exit-clearance-download-pdf');

    // ==========================================
    // MODUL SURAT PERINGATAN (DISCIPLINARY SANCTION - FR/HCGA/10-00)
    // ==========================================
    Route::get("$hcPref-disciplinary-sanction-index", [\App\Http\Controllers\HCGA\DisciplinarySanctionController::class, 'index'])->name('hrd-disciplinary-sanction-index');
    Route::get("$hcPref-disciplinary-sanction-create", [\App\Http\Controllers\HCGA\DisciplinarySanctionController::class, 'create'])->name('hrd-disciplinary-sanction-create');
    Route::post("$hcPref-disciplinary-sanction-store", [\App\Http\Controllers\HCGA\DisciplinarySanctionController::class, 'store'])->name('hrd-disciplinary-sanction-store');
    Route::get("$hcPref-disciplinary-sanction-detail/{id}", [\App\Http\Controllers\HCGA\DisciplinarySanctionController::class, 'detail'])->name('hrd-disciplinary-sanction-detail');
    Route::get("$hcPref-disciplinary-sanction-review/{id}", [\App\Http\Controllers\HCGA\DisciplinarySanctionController::class, 'review'])->name('hrd-disciplinary-sanction-review');
    Route::post("$hcPref-disciplinary-sanction-review/{id}/process", [\App\Http\Controllers\HCGA\DisciplinarySanctionController::class, 'processReview'])->name('hrd-disciplinary-sanction-review-process');
    Route::post("$hcPref-disciplinary-sanction-commitment/{id}/sign", [\App\Http\Controllers\HCGA\DisciplinarySanctionController::class, 'signCommitment'])->name('hrd-disciplinary-sanction-commitment-sign');
    Route::post("$hcPref-disciplinary-sanction-document/{id}/upload", [\App\Http\Controllers\HCGA\DisciplinarySanctionController::class, 'uploadDocument'])->name('hrd-disciplinary-sanction-document-upload');
    Route::get("$hcPref-disciplinary-sanction-pdf/{id}", [\App\Http\Controllers\HCGA\DisciplinarySanctionController::class, 'downloadPdf'])->name('hrd-disciplinary-sanction-download-pdf');

    // ==========================================
    // MOBILE PORTAL LANDING PAGE, DASHBOARD & SANCTUM SESSION MANAGER
    // ==========================================
    Route::prefix('mobile')->middleware(['active.user', 'mobile.security'])->group(function () {
        Route::get('/', [\App\Http\Controllers\HCGA\MobilePortalController::class, 'dashboard'])->name('mobile.home');
        Route::get('/dashboard', [\App\Http\Controllers\HCGA\MobilePortalController::class, 'dashboard'])->name('mobile.dashboard');
        Route::get('/ess', [\App\Http\Controllers\HCGA\MobilePortalController::class, 'ess'])->name('mobile.ess');
        Route::get('/ess/correction', [\App\Http\Controllers\HCGA\MobilePortalController::class, 'essCorrection'])->name('mobile.ess.correction');
        Route::get('/ess/overtime', [\App\Http\Controllers\HCGA\MobilePortalController::class, 'essOvertime'])->name('mobile.ess.overtime');
        Route::get('/ess/shift-exchange', [\App\Http\Controllers\HCGA\MobilePortalController::class, 'essShiftExchange'])->name('mobile.ess.shift-exchange');
        Route::get('/sessions', [\App\Http\Controllers\HCGA\MobilePortalController::class, 'sessions'])->name('mobile.sessions');
        Route::post('/sessions/{id}/revoke', [\App\Http\Controllers\HCGA\MobilePortalController::class, 'revokeSession'])->name('mobile.sessions.revoke');
        Route::post('/sessions/revoke-all', [\App\Http\Controllers\HCGA\MobilePortalController::class, 'revokeAllSessions'])->name('mobile.sessions.revoke-all');
        Route::post('/logout', [\App\Http\Controllers\HCGA\MobilePortalController::class, 'logout'])->name('mobile.logout');

        // Unified /mobile/* Module Routes
        Route::get('/approval', [HCGAApprovalInboxController::class, 'mobileIndex'])->name('approval.mobile.index');
        Route::get('/approval/{id}', [HCGAApprovalInboxController::class, 'mobileDetail'])
            ->where('id', '[0-9]+')
            ->name('approval.mobile.detail');

        Route::prefix('attendance')->group(function () {
            Route::get('/', [\App\Http\Controllers\HCGA\AttendancesController::class, 'mobileIndex'])->name('attendance.mobile.index');
            Route::get('/location', [\App\Http\Controllers\HCGA\AttendancesController::class, 'mobileLocation'])->name('attendance.mobile.location');
            Route::get('/selfie', [\App\Http\Controllers\HCGA\AttendancesController::class, 'mobileSelfie'])->name('attendance.mobile.selfie');
            Route::get('/timesheet', [\App\Http\Controllers\HCGA\AttendancesController::class, 'mobileTimesheet'])->name('attendance.mobile.timesheet');
            Route::get('/leave', [\App\Http\Controllers\HCGA\AttendancesController::class, 'mobileLeaveForm'])->name('attendance.mobile.leave');
            Route::post('/clock-in', [\App\Http\Controllers\HCGA\AttendancesController::class, 'apiClockIn'])->name('attendance.mobile.clockin');
            Route::post('/clock-out', [\App\Http\Controllers\HCGA\AttendancesController::class, 'apiClockOut'])->name('attendance.mobile.clockout');
            Route::post('/request/leave', [\App\Http\Controllers\HCGA\AttendancesController::class, 'apiSubmitLeave'])->name('attendance.mobile.submitleave');
            Route::post('/request/correction', [\App\Http\Controllers\HCGA\AttendancesController::class, 'apiSubmitCorrection'])->name('attendance.mobile.submitcorrection');
        });

        Route::prefix('sppd')->group(function () {
            Route::get('/', [\App\Http\Controllers\HCGA\SppdMobileController::class, 'mobileIndex'])->name('sppd.mobile.index');
            Route::get('/form', [\App\Http\Controllers\HCGA\SppdMobileController::class, 'mobileForm'])->name('sppd.mobile.form');
            Route::get('/detail', [\App\Http\Controllers\HCGA\SppdMobileController::class, 'mobileDetail'])->name('sppd.mobile.detail');
            Route::get('/approval', [\App\Http\Controllers\HCGA\SppdMobileController::class, 'mobileApproval'])->name('sppd.mobile.approval');
            Route::post('/submit', [\App\Http\Controllers\HCGA\SppdMobileController::class, 'apiSubmit'])->name('sppd.mobile.api.submit');
            Route::post('/approve', [\App\Http\Controllers\HCGA\SppdMobileController::class, 'apiProcessApproval'])->name('sppd.mobile.api.approval');
        });

        Route::prefix('reimbursement')->group(function () {
            Route::get('/', [\App\Http\Controllers\HCGA\ReimbursementMobileController::class, 'mobileIndex'])->name('reimbursement.mobile.index');
            Route::get('/form', [\App\Http\Controllers\HCGA\ReimbursementMobileController::class, 'mobileForm'])->name('reimbursement.mobile.form');
            Route::get('/detail', [\App\Http\Controllers\HCGA\ReimbursementMobileController::class, 'mobileDetail'])->name('reimbursement.mobile.detail');
            Route::get('/approval', [\App\Http\Controllers\HCGA\ReimbursementMobileController::class, 'mobileApproval'])->name('reimbursement.mobile.approval');
        });

        Route::get('/profile', [\App\Http\Controllers\HCGA\EmployeeMobileController::class, 'myProfile'])->name('employee.mobile.my-profile');
        Route::get('/directory', [\App\Http\Controllers\HCGA\EmployeeMobileController::class, 'directory'])->name('employee.mobile.directory');
        Route::get('/profile/tab-content', [\App\Http\Controllers\HCGA\EmployeeMobileController::class, 'loadTabContent'])->name('employee.mobile.tab-content');
        Route::post('/profile/submit-update', [\App\Http\Controllers\HCGA\EmployeeMobileController::class, 'submitUpdate'])->name('employee.mobile.submit-update');
        Route::post('/profile/request-approval/{id}', [\App\Http\Controllers\HCGA\EmployeeMobileController::class, 'approveRequest'])->name('employee.mobile.approve-request');

        Route::prefix('announcement')->group(function () {
            Route::get('/', [HCGAAnnouncementMobileController::class, 'mobileIndex'])->name('announcement.mobile.index');
            Route::get('/create', [HCGAAnnouncementMobileController::class, 'mobileForm'])->name('announcement.mobile.create');
            Route::get('/edit/{id}', [HCGAAnnouncementMobileController::class, 'mobileForm'])->name('announcement.mobile.edit');
            Route::get('/detail/{id}', [HCGAAnnouncementMobileController::class, 'mobileDetail'])->name('announcement.mobile.detail');
            Route::post('/submit', [HCGAAnnouncementMobileController::class, 'mobileSubmit'])->name('announcement.mobile.submit');
            Route::post('/delete/{id}', [HCGAAnnouncementMobileController::class, 'mobileDelete'])->name('announcement.mobile.delete');
        });

        Route::prefix('guideline')->group(function () {
            Route::get('/', [HCGACompanyGuidelineMobileController::class, 'mobileIndex'])->name('company-guideline.mobile.index');
            Route::get('/detail/{id}', [HCGACompanyGuidelineMobileController::class, 'mobileDetail'])->name('company-guideline.mobile.detail');
        });

        // Mobile Payslip (Slip Gaji Karyawan)
        Route::prefix('payslip')->group(function () {
            Route::get('/', [\App\Http\Controllers\HCGA\MobilePayslipController::class, 'index'])->name('payslip.mobile.index');
            Route::get('/detail', [\App\Http\Controllers\HCGA\MobilePayslipController::class, 'detail'])->name('payslip.mobile.detail');
            Route::get('/pdf', [\App\Http\Controllers\HCGA\MobilePayslipController::class, 'pdf'])->name('payslip.mobile.pdf');
        });
    });
