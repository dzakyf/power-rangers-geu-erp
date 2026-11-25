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

    Route::get('/hr-report-tax', function () {
        return view('humanresources.report.tax');
    });
    Route::get('/xml-hr-report-tax', function () {
        return view('humanresources.report.xml.tax');
    });
    Route::get('/hr-report-bpjs', function () {
        return view('humanresources.report.bpjs');
    });
    Route::get('/xml-hr-report-bpjs', function () {
        return view('humanresources.report.xml.bpjs');
    });
    Route::get('/hr-report-outstanding-recruitment', function () {
        return view('humanresources.report.outstanding-recruitment');
    });
    Route::get('/xml-hr-report-outstanding-recruitment', function () {
        return view('humanresources.report.xml.outstanding-recruitment');
    });
    Route::get('/recruitment-requisition', [HCGARequisitionController::class, 'index'])->name('hrd-recruitment-requisition');
    Route::get('/recruitment-requisition-detail', [HCGARequisitionController::class, 'detail'])->name('hrd-recruitment-requisition-detail');
    Route::get('/requisition-lists-detail', [HCGARequisitionController::class, 'detail']);
    Route::post('/requisition-submit', function () {
        return view('humanresources.recruitment.requisition-submit');
    });
    Route::get('/xml-recruitment-requisition', function () {
        return view('humanresources.recruitment.xml.requisition');
    });
    Route::get('/xml-requisition-lists', function () {
        return view('humanresources.recruitment.xml.requisition');
    });
    Route::get('/requisition-new', [HCGARequisitionController::class, 'create'])->name('hrd-requisition-new');
    Route::post('/recruitment-requisition-new-submit', [HCGARequisitionController::class, 'store']);
    Route::get('/requisition-new-po', function () {
        return view('humanresources.recruitment.requisition-new-po');
    });
    Route::post('/requisition-new-po-submit', function () {
        return view('humanresources.recruitment.requisition-new-po-submit');
    });
    Route::get('/recruitment-requisition-approval', [App\Http\Controllers\HCGA\RequisitionApprovalController::class, 'index'])->name('hrd-recruitment-requisition-approval');
    Route::get('/xml-recruitment-requisition-approval', [App\Http\Controllers\HCGA\RequisitionApprovalController::class, 'data']);
    Route::post('/requisition-approval-update', [App\Http\Controllers\HCGA\RequisitionApprovalController::class, 'update']);
    Route::get('/xml-sales-po-lists-detail', function () {
        return view('humanresources.recruitment.xml.sales-po-lists-detail');
    });
    Route::get('/hrd-master-template', function () {
        return view('humanresources.recruitment.hrd-master-template');
    });
    Route::post('/hrd-master-template-submit', function () {
        return view('humanresources.recruitment.hrd-master-template-submit');
    });
    Route::get('/hrd-master-template-detail', function () {
        return view('humanresources.recruitment.hrd-master-template-detail');
    });
    Route::post('/hrd-master-template-new-submit', function () {
        return view('humanresources.recruitment.hrd-master-template-new-submit');
    });
    Route::post('/hrd-master-template-detail-submit', function () {
        return view('humanresources.recruitment.hrd-master-template-detail-submit');
    });
    Route::get('/xml-recruitment-template', function () {
        return view('humanresources.recruitment.xml.recruitment-template');
    });
    Route::get('/hrd-master-template-new', function () {
        return view('humanresources.recruitment.hrd-master-template-new');
    });
    Route::get('/xml-hrd-master-template', function () {
        return view('humanresources.recruitment.xml.hrd-master-template');
    });
    Route::post('/sales-po-lists-update', function () {
        return view('humanresources.recruitment.sales-po-lists-update');
    });
    Route::get('/recruitment-new', function () {
        return view('humanresources.recruitment.recruitment-new');
    });
    Route::post('/recruitment-new-submit', [HCGARecruitmentNewSubmitController::class, 'store']);
    Route::get("/recruitment-new-inline", function () {
        return view("humanresources.recruitment.recruitment-new-inline");
    });
    Route::get('/recruitment-test-result', function () {
        return view('humanresources.recruitment.recruitment-test-result');
    });
    Route::get('/xml-recruitment-test-result', function () {
        return view('humanresources.recruitment.xml.recruitment-test-result');
    });
    Route::get('/recruitment-test-result-detail', function () {
        return view('humanresources.recruitment.recruitment-test-result-detail');
    });
    Route::post('/recruitment-test-result-detail-submit', function () {
        return view('humanresources.recruitment.recruitment-test-result-detail-submit');
    });
    Route::get('/recruitment-data', function () {
        return view('humanresources.recruitment.recruitment-data');
    });
    Route::post('/recruitment-data-columns-save', [HCGARecruitmentCandidateBatchController::class, 'saveColumns']);
    Route::get('/xml-recruitment-data', function () {
        return view('humanresources.recruitment.xml.recruitment-data');
    });

    // Batch Action: Create Candidate User Accounts
    Route::post('/recruitment-data-batch-create-user', [HCGARecruitmentCandidateBatchController::class, 'batchCreateUser']);

    // Batch Action: Update Position / Link Requisition PTK (Approved)
    Route::post('/recruitment-data-batch-update-position', [HCGARecruitmentCandidateBatchController::class, 'batchUpdatePosition']);
    Route::get('/recruitment-pending', function () {
        return view('humanresources.recruitment.recruitment-pending');
    });
    Route::get('/recruitment-pending-detail', function () {
        return view('humanresources.recruitment.recruitment-pending-detail');
    });
    Route::get('/recruitment-outstanding', function () {
        return view('humanresources.recruitment.recruitment-outstanding');
    });
    Route::get('/xml-recruitment-outstanding', function () {
        return view('humanresources.recruitment.xml.recruitment-outstanding');
    });
    Route::get('/recruitment-pending-detail2', function () {
        return view('humanresources.recruitment.recruitment-pending-detail2');
    });
    Route::post('/recruitment-pending-detail2-submit', function () {
        return view('humanresources.recruitment.recruitment-pending-detail2-submit');
    });
    Route::get('/xml-recruitment-pending', function () {
        return view('humanresources.recruitment.xml.recruitment-pending');
    });
    Route::get('/recruitment-candidate-analisys', [HCGACandidateAnalysisController::class, 'index']);
    Route::get('/recruitment-candidate-analysis', [HCGACandidateAnalysisController::class, 'index']);
    Route::get('/xml-recruitment-candidate-analysis', [HCGACandidateAnalysisController::class, 'data']);
    Route::get('/recruitment-candidate-analysis-detail', [HCGACandidateAnalysisController::class, 'detail']);
    Route::post('/recruitment-candidate-analysis-detail-submit', [HCGACandidateAnalysisController::class, 'store']);
    Route::post('/recruitment-data-submit', function () {
        return view('humanresources.recruitment.recruitment-data-submit');
    });
    Route::get('/recruitment-lists-detail', function () {
        return view('humanresources.recruitment.recruitment-lists-detail');
    });
    Route::get('/recruitment-lists-detail-data', function () {
        return view('humanresources.recruitment.recruitment-lists-detail-data');
    });
    Route::get('/recruitment-document', function () {
        return view('humanresources.recruitment.recruitment-document');
    });
    Route::get('/xml-recruitment-document', function () {
        return view('humanresources.recruitment.xml.recruitment-document');
    });
    Route::get('/recruitment-document-detail', function () {
        return view('humanresources.recruitment.recruitment-document-detail');
    });
    Route::post('/recruitment-document-detail-submit', function () {
        return view('humanresources.recruitment.recruitment-document-detail-submit');
    });
    Route::get('/recruitment-document-approval-detail', function () {
        return view('humanresources.recruitment.recruitment-document-approval-detail');
    });
    Route::get('/recruitment-notification', function () {
        return view('humanresources.recruitment.recruitment-notification');
    });
    Route::post('/recruitment-notification-submit', function () {
        return view('humanresources.recruitment.recruitment-notification-submit');
    });

    Route::get('/xml-recruitment-notification', function () {
        return view('humanresources.recruitment.xml.recruitment-notification');
    });
    Route::get('/recruitment-interview', [HCGAInterviewController::class, 'index']);
    Route::get('/xml-recruitment-interview', [HCGAInterviewController::class, 'data']);
    Route::get('/recruitment-interview-detail', [HCGAInterviewController::class, 'detail']);
    Route::post('/recruitment-interview-detail-submit', [HCGAInterviewController::class, 'store']);
    Route::get('/sales-proforma-ba', function () {
        return view('sales.proforma-ba');
    });
    Route::get('/xml-sales-proforma-ba', function () {
        return view('sales.xml.proforma-ba');
    });
    Route::get('/sales-proforma-ba-detail', function () {
        return view('sales.proforma-ba-detail');
    });
    Route::get('/sales-proforma-ba-detail2', function () {
        return view('sales.proforma-ba-detail2');
    });
    Route::get('/xml-sales-proforma-ba-detail', function () {
        return view('sales.xml.proforma-ba-detail');
    });
    Route::post('/sales-proforma-ba-detail-submit', function () {
        return view('sales.proforma-ba-detail-submit');
    });
    Route::post('/sales-proforma-ba-detail2-submit', function () {
        return view('sales.proforma-ba-detail2-submit');
    });
    Route::get('/badownload', function () {
        return view('sales.badownload');
    });
    Route::get('/baladownload', function () {
        return view('sales.baladownload');
    });
    Route::get('/bala2download', function () {
        return view('sales.bala2download');
    });



    // project
    Route::get('/opportunity-timeline-file', function () {
        return view('project.opportunity-timeline-file');
    });
    Route::get('/project-stock', function () {
        return view('project.project-stock');
    });
    Route::get('/project-stock-detail', function () {
        return view('project.project-stock-detail');
    });
    Route::get('/project-stock-add', function () {
        return view('project.project-stock-add');
    });

    Route::post('/project-stock-add-submit', function () {
        return view('project.project-stock-submit');
    });
    Route::get('/po-outstanding', function () {
        return view('procurement.po-outstanding');
    });
    Route::get('/po-outstanding-detail', function () {
        return view('procurement.po-outstanding-detail');
    });
    Route::get('/xml-po-outstanding', function () {
        return view('procurement.xml.po-outstanding');
    });
    Route::get('/po-vendor-summary', function () {
        return view('procurement.po-vendor-summary');
    });
    Route::get('/xml-po-vendor-summary', function () {
        return view('procurement.xml.po-vendor-summary');
    });
    Route::get('/po-vendor-summary-detail', function () {
        return view('procurement.po-vendor-summary-detail');
    });
    Route::get('/xml-po-vendor-summary-detail', function () {
        return view('procurement.xml.po-vendor-summary-detail');
    });
    Route::get('/po-payment-monitoring', function () {
        return view('procurement.po-payment-monitoring');
    });
    Route::get('/po-payment-monitoring-detail', function () {
        return view('procurement.po-payment-monitoring-detail');
    });
    Route::get('/xml-po-payment-monitoring', function () {
        return view('procurement.xml.po-payment-monitoring');
    });
    Route::get('/project-purchase', function () {
        return view('project.project-purchase');
    });
    Route::get('/project-purchase-add', function () {
        return view('project.project-purchase-add');
    });

    Route::get('/project-purchase-detail', function () {
        return view('project.project-purchase-detail');
    });
    Route::post('/project-purchase-submit', function () {
        return view('project.project-purchase-submit');
    });
    Route::get('/project-hrd-approval', function () {
        return view('project.project-hrd-approval');
    });
    Route::get('/project-hrd-approval-recounting', function () {
        return view('project.project-hrd-approval-recounting');
    });
    Route::get('/xml-project-hrd-approval', function () {
        return view('project.xml.project-hrd-approval');
    });
    Route::get('/xml-project-hrd-approval-detail', function () {
        return view('project.xml.project-hrd-approval-detail');
    });
    Route::get('/project-hrd-approval-detail', function () {
        return view('project.project-hrd-approval-detail');
    });
    Route::post('/project-hrd-approval-detail-submit', function () {
        return view('project.project-hrd-approval-detail-submit');
    });
    Route::post('/project-hrd-approval-detail-update', function () {
        return view('project.project-hrd-approval-detail-update');
    });
    Route::get('/xml-project-reimbursement-history', function () {
        return view('project.xml.project-reimbursement-history');
    });

    Route::post('/approval-reimbursement-update', function () {
        return view('project.approval-reimbursement-update');
    });
    Route::get('/approval-reimbursement', function () {
        return view('project.approval-reimbursement');
    });
    Route::get('/approval-reimbursement-detail', function () {
        return view('project.approval-reimbursement-detail');
    });

    Route::get('/xml-approval-reimbursement', function () {
        return view('project.xml.approval-reimbursement');
    });
    Route::get('/xml-approval-reimbursement-detail', function () {
        return view('project.xml.approval-reimbursement-detail');
    });


    Route::get('/project-reimbursement-history', function () {
        return view('project.project-reimbursement-history');
    });
    Route::get('/project-reimbursement', function () {
        return view('project.project-reimbursement');
    });
    Route::get('/project-reimbursement-detail', function () {
        return view('project.project-reimbursement-detail');
    });
    Route::get('/xml-project-reimbursement-detail', function () {
        return view('project.xml.project-reimbursement-detail');
    });
    Route::get('/project-reimbursement-detail-advance', function () {
        return view('project.project-reimbursement-detail-advance');
    });
    Route::get('/xml-project-reimbursement-detail-advance', function () {
        return view('project.xml.project-reimbursement-detail-advance');
    });
    Route::post('/project-reimbursement-detail-advance-update', function () {
        return view('project.project-reimbursement-detail-advance-update');
    });
    Route::get('/project-reimbursement-detail-reimbursement', function () {
        return view('project.project-reimbursement-detail-reimbursement');
    });
    Route::get('/xml-project-reimbursement-detail-reimbursement', function () {
        return view('project.xml.project-reimbursement-detail-reimbursement');
    });
    Route::post('/project-reimbursement-detail-reimbursement-update', function () {
        return view('project.project-reimbursement-detail-reimbursement-update');
    });
    Route::get('/xml-project-reimbursement-detail-reimbursement', function () {
        return view('project.xml.project-reimbursement-detail-reimbursement');
    });
    Route::post('/project-reimbursement-detail-update', function () {
        return view('project.project-reimbursement-detail-update');
    });
    Route::get('/project-reimbursement-out-add', function () {
        return view('project.project-reimbursement-out-add');
    });
    Route::get('/xml-project-reimbursement', function () {
        return view('project.xml.project-reimbursement');
    });
    Route::get('/project-organization', function () {
        return view('project.organization');
    });
    Route::get('/project-organization-detail', function () {
        return view('project.organization-detail');
    });

    Route::get('/xml-project-organization', function () {
        return view('project.xml.organization');
    });
    Route::get('/project-lists', function () {
        return view('project.project-lists');
    });
    Route::get('/project-lists-detail', function () {
        return view('project.project-detail');
    });
    Route::post('/project-detail-finish', function () {
        return view('project.project-detail-finish');
    });
    Route::post('/project-detail-final', function () {
        return view('project.project-detail-final');
    });
    Route::post('/project-detail-submit', function () {
        return view('project.project-detail-submit');
    });
    Route::post('/project-lists-submit', function () {
        return view('project.project-lists-submit');
    });
    Route::get('/xml-project-lists', function () {
        return view('project.xml.project-lists');
    });
    Route::get('/project-search-customer', function () {
        return view('project.search-customer');
    });
    Route::get('/search-customer', function () {
        return view('sales.search-customer');
    });
    Route::get('/project-search-vendor', function () {
        return view('project.search-vendor');
    });
    Route::get('/project-search-opportunity', function () {
        return view('project.search-opportunity');
    });
    Route::get('/project-new', function () {
        return view('project.new');
    });
    Route::post('/project-new-submit', function () {
        return view('project.new-submit');
    });

    Route::get('/project-new-opportunity', function () {
        return view('project.new-opportunity');
    });
    Route::post('/project-new-opportunity-submit', function () {
        return view('project.new-opportunity-submit');
    });

    Route::get('/project-activity', function () {
        return view('project.project-activity');
    });
    Route::get('/xml-project-activity', function () {
        return view('project.xml.project-activity');
    });
    Route::get('/project-activity2', function () {
        return view('project.project-activity2');
    });
    Route::get('/xml-project-activity2', function () {
        return view('project.xml.project-activity2');
    });
    Route::post('/project-activity2-update', function () {
        return view('project.project-activity2-update');
    });
    Route::get('/project-activity-add', function () {
        return view('project.project-activity-add');
    });
    Route::get('/project-activity-detail', function () {
        return view('project.project-activity-detail');
    });

    Route::get('/project-time', function () {
        return view('project.project-time');
    });

    Route::get('/project-times-detail', function () {
        return view('project.project-times-detail');
    });
    Route::get('/xml-project-times-costs', function () {
        return view('project.xml.project-times-costs');
    });
    Route::get('/xml-project-times-documents', function () {
        return view('project.xml.project-times-documents');
    });
    Route::get('/xml-project-times', function () {
        return view('project.xml.project-time');
    });
    Route::get('/project-time-detail', function () {
        return view('project.project-time-detail');
    });
    Route::post('/project-activity-detail-submit', function () {
        return view('project.project-activity-detail-submit');
    });
    Route::post('/project-activity-add-submit', function () {
        return view('project.project-activity-add-submit');
    });
    Route::get('/recruitment-outstanding-requisition', function () {
        return view('humanresources.recruitment.outstanding-requisition');
    });
    Route::get('/xml-outstanding-requisition', function () {
        return view('humanresources.recruitment.xml.outstanding-requisition');
    });

    Route::post('/outstanding-requisition-update', function () {
        return view('humanresources.recruitment.outstanding-requisition-update');
    });
    Route::get('/requisition-new-detail', function () {
        return view('humanresources.recruitment.requisition-new-project-detail');
    });
    Route::get('/xml-requisition-new-detail', function () {
        return view('humanresources.recruitment.xml.requisition-new-detail');
    });
    Route::get('/project-hrd', function () {
        return view('project.project-hrd');
    });
    Route::get('/project-hrd-detail', function () {
        return view('project.project-hrd-detail');
    });
    Route::get('/xml-project-hrd-detail', function () {
        return view('project.xml.project-hrd-detail');
    });
    Route::post('/project-hrd-detail-update', function () {
        return view('project.project-hrd-detail-update');
    });
    Route::get('/project-hrd-detail-detail', function () {
        return view('project.project-hrd-detail-detail');
    });
    Route::get('/xml-project-hrd-detail-detail', function () {
        return view('project.xml.project-hrd-detail-detail');
    });
    Route::get('/requisition-new-project', function () {
        return view('humanresources.recruitment.requisition-new-project');
    });
    Route::post('/requisition-new-project-submit', function () {
        return view('humanresources.recruitment.requisition-new-project-submit');
    });
    Route::get('/os-lists', function () {
        return view('humanresources.employment.os-lists');
    });
    Route::get('/xml-os-lists', function () {
        return view('humanresources.employment.xml.os-lists');
    });
    Route::get('/employee-lists', function () {
        return view('humanresources.employment.employee-lists');
    });
    Route::get('/hrd-employee-thr-scheme-modal', [\App\Http\Controllers\HCGA\EmployeeThrSchemeController::class, 'modal']);
    Route::post('/hrd-employee-thr-scheme-save', [\App\Http\Controllers\HCGA\EmployeeThrSchemeController::class, 'save']);
    Route::get('/xml-employee-lists', function () {
        return view('humanresources.employment.xml.employee-lists');
    });
    Route::post('/employee-lists-columns-save', function () {
        return view('humanresources.employment.employee-lists-columns-save');
    });
    Route::get('/xml-employee-pivot', function () {
        return view('humanresources.employment.xml.employee-pivot');
    });
    Route::get('/employee-lists-detail', function () {
        return view('humanresources.employment.employee-lists-detail');
    });
    Route::get('/employee-lists-detail-data', function () {
        return view('humanresources.employment.employee-lists-detail-data');
    });
    Route::post('/employee-lists-detail-data-submit', function () {
        return view('humanresources.employment.employee-lists-detail-data-submit');
    });
    Route::get('/employee-lists-detail-other', function () {
        return view('humanresources.employment.employee-lists-detail-other');
    });

    Route::get('/employee-lists-detail-insurance', function () {
        return view('humanresources.employment.employee-lists-detail-insurance');
    });
    Route::get('/xml-employee-lists-detail-document', function () {
        return view('humanresources.employment.xml.employee-lists-detail-document');
    });
    Route::get('/xml-employee-lists-detail-history', function () {
        return view('humanresources.employment.xml.employee-lists-detail-history');
    });
    Route::get('/recruitment-lists-detail-other', function () {
        return view('humanresources.recruitment.recruitment-lists-detail-other');
    });
    Route::get('/recruitment-lists-detail-family', function () {
        return view('humanresources.recruitment.recruitment-lists-detail-family');
    });
    Route::get('/recruitment-lists-detail-education', function () {
        return view('humanresources.recruitment.recruitment-lists-detail-education');
    });
    Route::get('/recruitment-lists-detail-skill', function () {
        return view('humanresources.recruitment.recruitment-lists-detail-skill');
    });
    Route::get('/xml-recruitment-lists-detail-other-skill', function () {
        return view('humanresources.recruitment.xml.recruitment-lists-detail-other-skill');
    });
    Route::get('/recruitment-lists-detail-document', function () {
        return view('humanresources.recruitment.recruitment-lists-detail-document');
    });

    Route::get('/recruitment-lists-detail-progress', [App\Http\Controllers\HCGA\RecruitmentProgressController::class, 'detail']);
    Route::post('/recruitment-lists-detail-progress-update', [App\Http\Controllers\HCGA\RecruitmentProgressController::class, 'update']);
    Route::post('/recruitment-lists-detail-progress-stage', [App\Http\Controllers\HCGA\RecruitmentProgressController::class, 'progress']);
    Route::post('/recruitment-lists-detail-data-submit', [App\Http\Controllers\HCGA\RecruitmentDetailController::class, 'updateDetailData']);
    Route::get('/xml-recruitment-lists-detail-document', function () {
        return view('humanresources.recruitment.xml.recruitment-lists-detail-document');
    });
    Route::get('/recruitment-lists-detail-insurance', function () {
        return view('humanresources.recruitment.recruitment-lists-detail-insurance');
    });
    Route::get('/xml-recruitment-lists-detail-insurance', function () {
        return view('humanresources.recruitment.xml.recruitment-lists-detail-insurance');
    });
    Route::post('/recruitment-lists-detail-insurance-update', function () {
        return view('humanresources.recruitment.recruitment-lists-detail-insurance-update');
    });
    Route::get('/xml-recruitment-lists-detail-organization', function () {
        return view('humanresources.recruitment.xml.recruitment-lists-detail-organization');
    });
    Route::post('/recruitment-lists-detail-organization-update', function () {
        return view('humanresources.recruitment.recruitment-lists-detail-organization-update');
    });
    Route::get('/xml-recruitment-lists-detail-family', function () {
        return view('humanresources.recruitment.xml.recruitment-lists-detail-family');
    });
    Route::get('/xml-recruitment-lists-detail-academic', function () {
        return view('humanresources.recruitment.xml.recruitment-lists-detail-academic');
    });
    Route::get('/xml-recruitment-lists-detail-contact-points', function () {
        return view('humanresources.recruitment.xml.recruitment-lists-detail-contact-points');
    });
    Route::post('/recruitment-lists-detail-contact-points-update', function () {
        return view('humanresources.recruitment.recruitment-lists-detail-contact-points-update');
    });
    Route::post('/recruitment-lists-detail-academic-update', function () {
        return view('humanresources.recruitment.recruitment-lists-detail-academic-update');
    });
    Route::get('/xml-recruitment-lists-detail-informal', function () {
        return view('humanresources.recruitment.xml.recruitment-lists-detail-informal');
    });
    Route::post('/recruitment-lists-detail-informal-update', function () {
        return view('humanresources.recruitment.recruitment-lists-detail-informal-update');
    });
    Route::get('/xml-recruitment-lists-detail-driving', function () {
        return view('humanresources.recruitment.xml.recruitment-lists-detail-driving');
    });
    Route::post('/recruitment-lists-detail-driving-update', function () {
        return view('humanresources.recruitment.recruitment-lists-detail-driving-update');
    });
    Route::get('/xml-recruitment-lists-detail-health', function () {
        return view('humanresources.recruitment.xml.recruitment-lists-detail-health');
    });
    Route::post('/recruitment-lists-detail-health-update', function () {
        return view('humanresources.recruitment.recruitment-lists-detail-health-update');
    });
    Route::get('/xml-recruitment-lists-detail-experience', function () {
        return view('humanresources.recruitment.xml.recruitment-lists-detail-experience');
    });
    Route::post('/recruitment-lists-detail-experience-update', function () {
        return view('humanresources.recruitment.recruitment-lists-detail-experience-update');
    });
    Route::get('/xml-recruitment-lists-detail-skill', function () {
        return view('humanresources.recruitment.xml.recruitment-lists-detail-skill');
    });
    Route::post('/recruitment-lists-detail-skill-update', function () {
        return view('humanresources.recruitment.recruitment-lists-detail-skill-update');
    });
    Route::get('/xml-recruitment-lists-detail-award', function () {
        return view('humanresources.recruitment.xml.recruitment-lists-detail-award');
    });
    Route::post('/recruitment-lists-detail-award-update', function () {
        return view('humanresources.recruitment.recruitment-lists-detail-award-update');
    });
    Route::get('/xml-recruitment-lists-detail-language', function () {
        return view('humanresources.recruitment.xml.recruitment-lists-detail-language');
    });
    Route::post('/recruitment-lists-detail-language-update', function () {
        return view('humanresources.recruitment.recruitment-lists-detail-language-update');
    });
    Route::get('/recruitment-lists-detail-recruitment', function () {
        return view('humanresources.recruitment.recruitment-lists-detail-recruitment');
    });

    Route::get('/xml-employee-lists-detail-insurance', function () {
        return view('humanresources.employment.xml.employee-lists-detail-insurance');
    });
    Route::post('/employee-lists-detail-insurance-update', function () {
        return view('humanresources.employment.employee-lists-detail-insurance-update');
    });
    Route::get('/xml-employee-lists-detail-organization', function () {
        return view('humanresources.employment.xml.employee-lists-detail-organization');
    });
    Route::post('/employee-lists-detail-organization-update', function () {
        return view('humanresources.employment.employee-lists-detail-organization-update');
    });
    Route::get('/xml-employee-lists-detail-family', function () {
        return view('humanresources.employment.xml.employee-lists-detail-family');
    });
    Route::get('/xml-employee-lists-detail-academic', function () {
        return view('humanresources.employment.xml.employee-lists-detail-academic');
    });
    Route::get('/xml-employee-lists-detail-contact-points', function () {
        return view('humanresources.employment.xml.employee-lists-detail-contact-points');
    });
    Route::post('/employee-lists-detail-contact-points-update', function () {
        return view('humanresources.employment.employee-lists-detail-contact-points-update');
    });
    Route::post('/employee-lists-detail-academic-update', function () {
        return view('humanresources.employment.employee-lists-detail-academic-update');
    });
    Route::get('/xml-employee-lists-detail-informal', function () {
        return view('humanresources.employment.xml.employee-lists-detail-informal');
    });
    Route::post('/employee-lists-detail-informal-update', function () {
        return view('humanresources.employment.employee-lists-detail-informal-update');
    });
    Route::get('/xml-employee-lists-detail-driving', function () {
        return view('humanresources.employment.xml.employee-lists-detail-driving');
    });
    Route::post('/employee-lists-detail-driving-update', function () {
        return view('humanresources.employment.employee-lists-detail-driving-update');
    });
    Route::get('/xml-employee-lists-detail-health', function () {
        return view('humanresources.employment.xml.employee-lists-detail-health');
    });
    Route::post('/employee-lists-detail-health-update', function () {
        return view('humanresources.employment.employee-lists-detail-health-update');
    });
    Route::get('/xml-employee-lists-detail-experience', function () {
        return view('humanresources.employment.xml.employee-lists-detail-experience');
    });
    Route::post('/employee-lists-detail-experience-update', function () {
        return view('humanresources.employment.employee-lists-detail-experience-update');
    });
    Route::get('/xml-employee-lists-detail-skill', function () {
        return view('humanresources.employment.xml.employee-lists-detail-skill');
    });
    Route::post('/employee-lists-detail-skill-update', function () {
        return view('humanresources.employment.employee-lists-detail-skill-update');
    });
    Route::get('/xml-employee-lists-detail-award', function () {
        return view('humanresources.employment.xml.employee-lists-detail-award');
    });
    Route::post('/employee-lists-detail-award-update', function () {
        return view('humanresources.employment.employee-lists-detail-award-update');
    });
    Route::get('/xml-employee-lists-detail-language', function () {
        return view('humanresources.employment.xml.employee-lists-detail-language');
    });
    Route::post('/employee-lists-detail-language-update', function () {
        return view('humanresources.employment.employee-lists-detail-language-update');
    });
    Route::get('/employee-lists-detail-recruitment', function () {
        return view('humanresources.employment.employee-lists-detail-recruitment');
    });

    Route::get('/employee-mutation', function () {
        return view('humanresources.employment.employee-mutation');
    });
    Route::get('/employee-mutation-detail', function () {
        return view('humanresources.employment.employee-mutation-detail');
    });
    Route::post('/employee-mutation-detail-submit', function () {
        return view('humanresources.employment.employee-mutation-detail-submit');
    });
    Route::get('/xml-employee-mutation', function () {
        return view('humanresources.employment.xml.employee-mutation');
    });
    Route::get('/employee-promotion', function () {
        return view('humanresources.employment.employee-promotion');
    });
    Route::get('/employee-promotion-detail', function () {
        return view('humanresources.employment.employee-promotion-detail');
    });
    Route::post('/employee-promotion-detail-submit', function () {
        return view('humanresources.employment.employee-promotion-detail-submit');
    });
    Route::get('/xml-employee-promotion', function () {
        return view('humanresources.employment.xml.employee-promotion');
    });
    Route::get('/os-conversion', function () {
        return view('humanresources.employment.os-conversion');
    });
    Route::get('/os-conversion-detail', function () {
        return view('humanresources.employment.os-conversion-detail');
    });
    Route::post('/os-conversion-detail-submit', function () {
        return view('humanresources.employment.os-conversion-detail-submit');
    });
    Route::get('/xml-os-conversion', function () {
        return view('humanresources.employment.xml.os-conversion');
    });

    Route::get('/employee-lists-detail-history', function () {
        return view('humanresources.employment.employee-lists-detail-history');
    });
    Route::get('/employee-lists-detail-document', function () {
        return view('humanresources.employment.employee-lists-detail-document');
    });
    Route::get('/employee-lists-detail-kjk', function () {
        return view('humanresources.employment.employee-lists-detail-kjk');
    });
    Route::get('/xml-employee-lists-detail-kjk', function () {
        return view('humanresources.employment.xml.employee-lists-detail-kjk');
    });
    Route::get('/xml-employee-lists-detail-overtime', function () {
        return view('humanresources.employment.xml.employee-lists-detail-overtime');
    });
    Route::get('/employee-lists-detail-family', function () {
        return view('humanresources.employment.employee-lists-detail-family');
    });
    Route::get('/employee-lists-detail-education', function () {
        return view('humanresources.employment.employee-lists-detail-education');
    });
    Route::get('/employee-lists-detail-skill', function () {
        return view('humanresources.employment.employee-lists-detail-skill');
    });
    Route::post('/employee-lists-detail-submit', function () {
        return view('humanresources.employment.employee-lists-detail-update');
    });
    Route::post('/os-contract-renew-submit', function () {
        return view('humanresources.employment.os-contract-renew-submit');
    });
    Route::get('/os-contract', function () {
        return view('humanresources.employment.os-contract');
    });
    Route::get('/xml-os-contract', function () {
        return view('humanresources.employment.xml.os-contract');
    });
    Route::get('/os-contract-detail', function () {
        return view('humanresources.employment.os-contract-detail');
    });
    Route::get('/close-contract', function () {
        return view('humanresources.employment.os-contract-close');
    });
    Route::post('/os-contract-close-submit', function () {
        return view('humanresources.employment.os-contract-close-submit');
    });
    Route::get('/os-contract-renew', function () {
        return view('humanresources.employment.os-contract-renew');
    });

    Route::get('/project-team', function () {
        return view('project.project-team');
    });

    Route::get('/xml-project-team', function () {
        return view('project.xml.project-team');
    });

    Route::get('/project-team-detail', function () {
        return view('project.project-team-detail');
    });
    Route::post('/project-team-detail-update', function () {
        return view('project.project-team-detail-update');
    });

    Route::get('/xml-project-team-detail', function () {
        return view('project.xml.project-team-detail');
    });
    Route::get('/project-team-detail-add', function () {
        return view('project.project-team-detail-add');
    });
    Route::get('/xml-project-team-detail-add', function () {
        return view('project.xml.project-team-detail-add');
    });
    Route::post('/project-team-detail-add-update', function () {
        return view('project.project-team-detail-add-update');
    });
    Route::post('/project-team-detail-add-submit', function () {
        return view('project.project-team-detail-add-submit');
    });


    Route::get('/project-issues', function () {
        return view('project.project-issues');
    });

    Route::get('/xml-project-issues', function () {
        return view('project.xml.project-issues');
    });

    Route::get('/project-issues-detail', function () {
        return view('project.project-issues-detail');
    });
    Route::get('/project-issues-detail-log', function () {
        return view('project.project-issues-detail-log');
    });
    Route::post('/project-issues-detail-update', function () {
        return view('project.project-issues-detail-update');
    });
    Route::get('/xml-project-issues-detail', function () {
        return view('project.xml.project-issues-detail');
    });
    Route::get('/xml-project-issues-detail-log', function () {
        return view('project.xml.project-issues-detail-log');
    });
    Route::get('/project-issues-detail-add', function () {
        return view('project.project-issues-detail-add');
    });
    Route::post('/project-issues-detail-add-submit', function () {
        return view('project.project-issues-detail-add-submit');
    });
    Route::get('/project-issues-detail-edit', function () {
        return view('project.project-issues-detail-edit');
    });
    Route::post('/project-issues-detail-edit-submit', function () {
        return view('project.project-issues-detail-edit-submit');
    });
    Route::get('/project-documents', function () {
        return view('project.project-documents');
    });

    Route::get('/xml-project-documents', function () {
        return view('project.xml.project-documents');
    });

    Route::get('/project-documents-detail', function () {
        return view('project.project-documents-detail');
    });
    Route::post('/project-documents-detail-update', function () {
        return view('project.project-documents-detail-update');
    });
    Route::get('/xml-project-documents-detail', function () {
        return view('project.xml.project-documents-detail');
    });
    Route::get('/project-documents-detail-add', function () {
        return view('project.project-documents-detail-add');
    });
    Route::post('/project-documents-detail-add-submit', function () {
        return view('project.project-documents-detail-add-submit');
    });
    Route::get('/project-documents-detail-edit', function () {
        return view('project.project-documents-detail-edit');
    });



    Route::get('/master-product-sub-group', function () {
        return view('procurement.master-product-sub-group');
    });
    Route::get('/xml-master-product-sub-group', function () {
        return view('procurement.xml.master-product-sub-group');
    });
    Route::post('/master-product-sub-group-update', function () {
        return view('procurement.master-product-sub-group-update');
    });

    Route::get('/master-product-sub-sub-group', function () {
        return view('procurement.master-product-sub-sub-group');
    });
    Route::get('/xml-master-product-sub-sub-group', function () {
        return view('procurement.xml.master-product-sub-sub-group');
    });
    Route::post('/master-product-sub-sub-group-update', function () {
        return view('procurement.master-product-sub-sub-group-update');
    });
    
    
    
    
    
    
    
    Route::get('/master-product-group', function () {
        return view('procurement.master-product-group');
    });
    Route::get('/xml-master-product-group', function () {
        return view('procurement.xml.master-product-group');
    });
    Route::post('/master-product-group-update', function () {
        return view('procurement.master-product-group-update');
    });
    Route::get('/xml-combo-product-group', function () {
        return view('procurement.xml.product-group');
    });
    Route::get('/xml-combo-product-sub-group', function () {
        return view('procurement.xml.product-sub-group');
    });
    Route::get('/xml-combo-product-approval', function () {
        return view('procurement.xml.product-approval');
    });
    Route::get('/permission-all', [\App\Http\Controllers\HCGA\PermissionLegacyController::class, 'allIndex']);
    Route::get('/xml-permission-all', [\App\Http\Controllers\HCGA\PermissionLegacyController::class, 'xmlAll']);
    Route::get('/permission-all-detail', [\App\Http\Controllers\HCGA\PermissionLegacyController::class, 'detail']);
    Route::get('/permission-approval-detail', [\App\Http\Controllers\HCGA\PermissionLegacyController::class, 'detail']);
    Route::get('/permission-approval-cancel', [\App\Http\Controllers\HCGA\PermissionLegacyController::class, 'cancelApproval']);
    Route::get('/attendant-data', [\App\Http\Controllers\HCGA\AttendancesController::class, 'index'])->name('attendance.data');
    Route::get('/attendant-data/ajax', [\App\Http\Controllers\HCGA\AttendancesController::class, 'getMonitoringDataAjax'])->name('attendance.data.ajax');
    Route::get('/attendant-data/export-excel', [\App\Http\Controllers\HCGA\AttendancesController::class, 'exportExcel'])->name('attendance.data.export');
    Route::get('/xml-attendant-data', [\App\Http\Controllers\HCGA\AttendancesController::class, 'indexXml']);

    // Web Desktop Portal Attendance, Leave & Approval Tracking
    Route::get('/attendance-portal', [\App\Http\Controllers\HCGA\AttendancesController::class, 'portalIndex'])->name('attendance.portal');
    Route::get('/attendance/portal', [\App\Http\Controllers\HCGA\AttendancesController::class, 'portalIndex']); // Alias fallback
    Route::get('/attendance/approval-progress/{id}', [\App\Http\Controllers\HCGA\AttendancesController::class, 'getApprovalProgress'])->name('attendance.approval.progress');
    Route::post('/attendance/admin/reset', [\App\Http\Controllers\HCGA\AttendancesController::class, 'resetAttendance'])->name('attendance.admin.reset');
    Route::post('/attendance/request/correction', [\App\Http\Controllers\HCGA\AttendancesController::class, 'apiSubmitCorrection'])->name('attendance.request.correction');

    // Master Attendance Cutoff & Period Cycles
    Route::get('/hrd-master-attendance-cutoff', [\App\Http\Controllers\HCGA\MasterAttendanceCutoffController::class, 'index'])->name('attendance.cutoff.index');
    Route::get('/xml-hrd-master-attendance-cutoff', [\App\Http\Controllers\HCGA\MasterAttendanceCutoffController::class, 'dataXml'])->name('attendance.cutoff.xml');
    Route::post('/hrd-master-attendance-cutoff-store', [\App\Http\Controllers\HCGA\MasterAttendanceCutoffController::class, 'store'])->name('attendance.cutoff.store');
    Route::post('/hrd-master-attendance-cutoff/{id}/update', [\App\Http\Controllers\HCGA\MasterAttendanceCutoffController::class, 'update'])->name('attendance.cutoff.update');
    Route::delete('/hrd-master-attendance-cutoff/{id}', [\App\Http\Controllers\HCGA\MasterAttendanceCutoffController::class, 'destroy'])->name('attendance.cutoff.destroy');
    Route::get('/hrd-master-attendance-cutoff-preview', [\App\Http\Controllers\HCGA\MasterAttendanceCutoffController::class, 'previewRange'])->name('attendance.cutoff.preview');

    // Setting Lokasi & Batas Maksimal Jarak Absensi (Geofence Radius)
    Route::get('/hrd-attendance-locations', [\App\Http\Controllers\HCGA\AttendancesController::class, 'locationsIndex'])->name('attendance.locations.index');
    Route::post('/hrd-attendance-locations/store', [\App\Http\Controllers\HCGA\AttendancesController::class, 'locationsStore'])->name('attendance.locations.store');
    Route::post('/hrd-attendance-locations/{id}/update', [\App\Http\Controllers\HCGA\AttendancesController::class, 'locationsUpdate'])->name('attendance.locations.update');
    Route::delete('/hrd-attendance-locations/{id}', [\App\Http\Controllers\HCGA\AttendancesController::class, 'locationsDestroy'])->name('attendance.locations.destroy');

    // HCGA Master Location (Duplikasi UI Modern dari accounting-master-location)
    Route::get('/hrd-master-location', [\App\Http\Controllers\HCGA\MasterLocationController::class, 'index'])->name('hrd.master-location.index');
    Route::post('/hrd-master-location/store', [\App\Http\Controllers\HCGA\MasterLocationController::class, 'store'])->name('hrd.master-location.store');
    Route::post('/hrd-master-location/{id}/update', [\App\Http\Controllers\HCGA\MasterLocationController::class, 'update'])->name('hrd.master-location.update');
    Route::delete('/hrd-master-location/{id}', [\App\Http\Controllers\HCGA\MasterLocationController::class, 'destroy'])->name('hrd.master-location.destroy');

    // Overtime (Lembur) Matrix & Requests
    Route::get('/hrd-overtime-matrix', [\App\Http\Controllers\HCGA\OvertimeRequestController::class, 'indexMatrix'])->name('attendance.overtime.matrix');
    Route::get('/hrd-overtime-matrix/{id}/detail', [\App\Http\Controllers\HCGA\OvertimeRequestController::class, 'getMatrixDetail'])->name('attendance.overtime.matrix.detail');
    Route::post('/hrd-overtime-matrix/store', [\App\Http\Controllers\HCGA\OvertimeRequestController::class, 'storeMatrix'])->name('attendance.overtime.matrix.store');
    Route::post('/hrd-overtime-matrix/{id}/update', [\App\Http\Controllers\HCGA\OvertimeRequestController::class, 'updateMatrix'])->name('attendance.overtime.matrix.update');
    Route::delete('/hrd-overtime-matrix/{id}', [\App\Http\Controllers\HCGA\OvertimeRequestController::class, 'destroyMatrix'])->name('attendance.overtime.matrix.destroy');

    // Overtime Regions (Master Wilayah Lembur Dinamis)
    Route::get('/hrd-overtime-regions', [\App\Http\Controllers\HCGA\OvertimeRequestController::class, 'indexRegions'])->name('attendance.overtime.regions');
    Route::get('/hrd-overtime-regions/{id}/detail', [\App\Http\Controllers\HCGA\OvertimeRequestController::class, 'getRegionDetail'])->name('attendance.overtime.region.detail');
    Route::post('/hrd-overtime-regions/store', [\App\Http\Controllers\HCGA\OvertimeRequestController::class, 'storeRegion'])->name('attendance.overtime.region.store');
    Route::post('/hrd-overtime-regions/{id}/update', [\App\Http\Controllers\HCGA\OvertimeRequestController::class, 'updateRegion'])->name('attendance.overtime.region.update');
    Route::delete('/hrd-overtime-regions/{id}', [\App\Http\Controllers\HCGA\OvertimeRequestController::class, 'destroyRegion'])->name('attendance.overtime.region.destroy');

    Route::post('/attendance/overtime/calculate-preview', [\App\Http\Controllers\HCGA\OvertimeRequestController::class, 'apiCalculatePreview'])->name('attendance.overtime.preview');
    Route::post('/attendance/request/overtime', [\App\Http\Controllers\HCGA\OvertimeRequestController::class, 'storeOvertimeRequest'])->name('attendance.request.overtime');

    // Change Shift / Tukar Shift Requests
    Route::get('/attendance/change-shift/schedule', [\App\Http\Controllers\HCGA\ShiftExchangeController::class, 'getScheduleByDate'])->name('attendance.changeshift.schedule');
    Route::post('/attendance/request/change-shift', [\App\Http\Controllers\HCGA\ShiftExchangeController::class, 'submitChangeShift'])->name('attendance.request.changeshift');

    // Mobile Webview Attendance & Timesheet (Legacy URL redirects to /mobile/attendance*)
    Route::redirect('/attendance/mobile', '/mobile/attendance');
    Route::redirect('/attendance/mobile/timesheet', '/mobile/attendance/timesheet');
    Route::redirect('/attendance/mobile/leave', '/mobile/attendance/leave');
    Route::post('/attendance/mobile/clock-in', [\App\Http\Controllers\HCGA\AttendancesController::class, 'apiClockIn']);
    Route::post('/attendance/mobile/clock-out', [\App\Http\Controllers\HCGA\AttendancesController::class, 'apiClockOut']);
    Route::post('/attendance/mobile/request/leave', [\App\Http\Controllers\HCGA\AttendancesController::class, 'apiSubmitLeave']);
    Route::post('/attendance/request/leave/cancel', [\App\Http\Controllers\HCGA\AttendancesController::class, 'apiCancelLeave'])->name('attendance.request.leave.cancel');
    Route::post('/attendance/mobile/request/correction', [\App\Http\Controllers\HCGA\AttendancesController::class, 'apiSubmitCorrection']);
    Route::get('/permission-approval', [\App\Http\Controllers\HCGA\PermissionLegacyController::class, 'approvalIndex']);
    Route::post('/permission-approval-update', [\App\Http\Controllers\HCGA\PermissionLegacyController::class, 'updateApproval']);
    Route::get('/xml-permission-approval', [\App\Http\Controllers\HCGA\PermissionLegacyController::class, 'xmlApproval']);
    Route::get('/xml-combo-directorat', function () {
        return view('humanresources.master.xml.combo-directorat');
    });
    Route::get('/xml-combo-department', function () {
        return view('humanresources.master.xml.combo-department');
    });
    Route::get('/xml-combo-section', function () {
        return view('humanresources.master.xml.combo-section');
    });
    Route::get('/xml-combo-parent-position', function () {
        return view('humanresources.master.xml.combo-parent-position');
    });
    Route::get('/master-holiday-detail', function () {
        return view('humanresources.permission.master-holiday-detail');
    });
    Route::get('/xml-master-holiday-detail', function () {
        return view('humanresources.permission.xml.holiday-detail');
    });
    Route::post('/master-section-update', function () {
        return view('humanresources.master.section-submit');
    });
    Route::get('/hrd-master-section', function () {
        return view('humanresources.master.section');
    });
    Route::get('/xml-hrd-master-section', function () {
        return view('humanresources.master.xml.section');
    });
    Route::post('/master-position-update', [\App\Http\Controllers\HCGA\PositionMasterController::class, 'update']);
    Route::get('/hrd-master-position', [\App\Http\Controllers\HCGA\PositionMasterController::class, 'index']);
    Route::get('/xml-hrd-master-position', [\App\Http\Controllers\HCGA\PositionMasterController::class, 'xmlData']);
    Route::post('/hrd-master-position/sync-existing', [\App\Http\Controllers\HCGA\PositionMasterController::class, 'syncExisting'])->name('hrd.master.position.sync-existing');


    Route::post('/hrd-master-department-submit', function () {
        return view('humanresources.master.department-update');
    });
    Route::get('/hrd-master-department', function () {
        return view('humanresources.master.department');
    });
    Route::get('/xml-hrd-master-department', function () {
        return view('humanresources.master.xml.department');
    });
    Route::post('/master-department-update', function () {
        return view('humanresources.master.department-submit');
    });
    Route::get('/hrd-master-directorat', function () {
        return view('humanresources.master.directorat');
    });
    Route::get('/xml-hrd-master-directorat', function () {
        return view('humanresources.master.xml.directorat');
    });
    Route::post('/master-directorat-update', function () {
        return view('humanresources.master.directorat-submit');
    });

    Route::post('/hrd-master-permission-submit', [\App\Http\Controllers\HCGA\MasterPermissionController::class, 'update']);
    Route::get('/hrd-master-permission', [\App\Http\Controllers\HCGA\MasterPermissionController::class, 'index']);
    Route::get('/xml-hrd-master-permission', [\App\Http\Controllers\HCGA\MasterPermissionController::class, 'xmlData']);
    Route::get('/xml-combo-permission-group', function () {
        return view('humanresources.permission.xml.combo-permission-group');
    });
    Route::get('/hrd-master-holiday', function () {
        return view('humanresources.permission.master-holiday');
    });
    Route::post('/hrd-master-holiday-submit', function () {
        return view('humanresources.permission.master-holiday-update');
    });
    Route::get('/xml-hrd-master-holiday', function () {
        return view('humanresources.permission.xml.master-holiday');
    });
    Route::get('/permission-new', [\App\Http\Controllers\HCGA\PermissionLegacyController::class, 'newIndex']);
    Route::post('/permission-new-submit', [\App\Http\Controllers\HCGA\PermissionLegacyController::class, 'submitNew']);
    Route::get('/load-kategori-izin', [\App\Http\Controllers\HCGA\PermissionLegacyController::class, 'loadKategoriIzin']);
    Route::get('/permission-collective-leave', [\App\Http\Controllers\HCGA\PermissionLegacyController::class, 'collectiveLeaveIndex'])->name('permission.collective.index');
    Route::post('/permission-collective-leave-submit', [\App\Http\Controllers\HCGA\PermissionLegacyController::class, 'submitCollectiveLeave'])->name('permission.collective.submit');

    Route::get('/print-bpjs', function () {
        return view('humanresources.payroll.prints.bpjs');
    });
    Route::get('/print-pph', function () {
        return view('humanresources.payroll.prints.tax');
    });
    Route::get('/print-salary', [\App\Http\Controllers\HCGA\PayrollController::class, 'showDigitalPayslip'])->name('payroll.payslip.digital');
    Route::get('/payslip-digital', [\App\Http\Controllers\HCGA\PayrollController::class, 'showDigitalPayslip'])->name('payroll.payslip.show');
    Route::post('/api/payroll/send-payslip-email', [\App\Http\Controllers\HCGA\PayrollController::class, 'apiSendPayslipEmail']);
    Route::get('/print-salary-legacy', function () {
        return view('humanresources.payroll.prints.salary');
    });
    Route::get('/payroll-lists', [\App\Http\Controllers\HCGA\PayrollController::class, 'payrollListsIndex'])->name('payroll.lists.index');
    Route::get('/payroll-list', [\App\Http\Controllers\HCGA\PayrollController::class, 'payrollListsIndex']);
    Route::post('/payroll-lists-update', function () {
        return view('humanresources.payroll.payroll-lists-update');
    });
    Route::get('/xml-payroll-lists', [\App\Http\Controllers\HCGA\PayrollController::class, 'xmlPayrollLists'])->name('payroll.lists.xml');
    Route::get('/xml-payroll-list', [\App\Http\Controllers\HCGA\PayrollController::class, 'xmlPayrollList'])->name('payroll.list.xml');

    Route::get('/xml-payroll-ap-salary', function () {
        return view('humanresources.payroll.xml.payroll-ap-salary');
    });

    Route::get('/payroll-tax', [\App\Http\Controllers\HCGA\PayrollController::class, 'payrollTaxIndex'])->name('payroll.tax.index');
    Route::post('/payroll-tax-update', [\App\Http\Controllers\HCGA\PayrollController::class, 'payrollTaxUpdate'])->name('payroll.tax.update');
    Route::get('/xml-payroll-tax', [\App\Http\Controllers\HCGA\PayrollController::class, 'xmlPayrollTax'])->name('payroll.tax.xml');

    // API Konfigurasi Tarif PPh 21 TER, Progresif UU HPP & Parameter Deductions Dinamis
    Route::get('/api/payroll/tax-ter-rates', [\App\Http\Controllers\HCGA\PayrollController::class, 'apiGetTaxTerRates']);
    Route::post('/api/payroll/tax-ter-rates/update', [\App\Http\Controllers\HCGA\PayrollController::class, 'apiUpdateTaxTerRate']);
    Route::get('/api/payroll/tax-brackets', [\App\Http\Controllers\HCGA\PayrollController::class, 'apiGetTaxBrackets']);
    Route::post('/api/payroll/tax-brackets/update', [\App\Http\Controllers\HCGA\PayrollController::class, 'apiUpdateTaxBracket']);
    Route::get('/api/payroll/deduction-configs', [\App\Http\Controllers\HCGA\PayrollController::class, 'apiGetDeductionConfigs']);
    Route::post('/api/payroll/deduction-configs/update', [\App\Http\Controllers\HCGA\PayrollController::class, 'apiUpdateDeductionConfig']);
    Route::get('/api/payroll/employee-salary-detail', [\App\Http\Controllers\HCGA\PayrollController::class, 'apiGetEmployeeSalarySlipDetail']);

    // Perhitungan & Manajemen THR (Tunjangan Hari Raya) terintegrasi ke Payslip Bulan Berjalan
    Route::get('/payroll-thr', [\App\Http\Controllers\HCGA\PayrollThrController::class, 'index'])->name('payroll.thr.index');
    Route::get('/api/payroll/thr/batches', [\App\Http\Controllers\HCGA\PayrollThrController::class, 'listBatches'])->name('payroll.thr.batches');
    Route::get('/xml-payroll-thr-items', [\App\Http\Controllers\HCGA\PayrollThrController::class, 'batchItemsXml'])->name('payroll.thr.items.xml');
    Route::post('/api/payroll/thr/generate', [\App\Http\Controllers\HCGA\PayrollThrController::class, 'generateDraft'])->name('payroll.thr.generate');
    Route::post('/api/payroll/thr/item/update', [\App\Http\Controllers\HCGA\PayrollThrController::class, 'updateItem'])->name('payroll.thr.item.update');
    Route::post('/api/payroll/thr/lock', [\App\Http\Controllers\HCGA\PayrollThrController::class, 'lockBatch'])->name('payroll.thr.lock');
    Route::post('/api/payroll/thr/unlock', [\App\Http\Controllers\HCGA\PayrollThrController::class, 'unlockBatch'])->name('payroll.thr.unlock');
    Route::post('/api/payroll/thr/delete', [\App\Http\Controllers\HCGA\PayrollThrController::class, 'deleteDraft'])->name('payroll.thr.delete');


    Route::get('/payroll-group', function () {
        return view('humanresources.payroll.payroll-group');
    });
    Route::post('/payroll-group-update', function () {
        return view('humanresources.payroll.payroll-group-update');
    });
    Route::get('/xml-payroll-group', function () {
        return view('humanresources.payroll.xml.payroll-group');
    });
    Route::get('/payroll-group-lists-none', function () {
        return view('humanresources.payroll.payroll-group-lists-none');
    });
    Route::get('/payroll-group-detail', function () {
        return view('humanresources.payroll.payroll-group-detail');
    });
    Route::post('/payroll-group-detail-update', function () {
        return view('humanresources.payroll.payroll-group-detail-update');
    });
    Route::get('/payroll-employee-detail', function () {
        return view('humanresources.payroll.payroll-employee-detail');
    });
    Route::get('/payroll-employee-detail-add', function () {
        return view('humanresources.payroll.payroll-employee-detail-add');
    });
    Route::post('/payroll-employee-detail-update', function () {
        return view('humanresources.payroll.payroll-employee-detail-update');
    });
    Route::get('/payroll-employee-recounting', function () {
        return view('humanresources.payroll.payroll-employee-recounting');
    });
    Route::post('/payroll-group-employee-update', function () {
        return view('humanresources.payroll.payroll-group-employee-update');
    });
    Route::get('/xml-payroll-group-detail', function () {
        return view('humanresources.payroll.xml.payroll-group-detail');
    });
    Route::get('/xml-payroll-employee-detail', function () {
        return view('humanresources.payroll.xml.payroll-employee-detail');
    });
    Route::get('/payroll-group-detail-add', function () {
        return view('humanresources.payroll.payroll-group-detail-add');
    });
    Route::post('/payroll-group-detail-add-update', function () {
        return view('humanresources.payroll.payroll-group-detail-add-update');
    });
    Route::get('/xml-payroll-group-detail-add', function () {
        return view('humanresources.payroll.xml.payroll-group-detail-add');
    });

    Route::get('/payroll-group-lists', function () {
        return view('humanresources.payroll.payroll-group-lists');
    });
    Route::post('/payroll-group-lists-update', function () {
        return view('humanresources.payroll.payroll-group-lists-update');
    });
    Route::get('/xml-payroll-group-lists', function () {
        return view('humanresources.payroll.xml.payroll-group-lists');
    });

    Route::get('/payroll-group-lists-add', function () {
        return view('humanresources.payroll.payroll-group-lists-add');
    });
    Route::post('/payroll-group-lists-add-update', function () {
        return view('humanresources.payroll.payroll-group-lists-add-update');
    });
    Route::get('/xml-payroll-group-lists-add', function () {
        return view('humanresources.payroll.xml.payroll-group-lists-add');
    });
    Route::get('/payroll-employee', [\App\Http\Controllers\HCGA\PayrollController::class, 'payrollEmployeeIndex'])->name('payroll.employee.index');
    Route::post('/payroll-employee-update', function () {
        return view('humanresources.payroll.payroll-employee-update');
    });
    Route::get('/xml-payroll-employee', [\App\Http\Controllers\HCGA\PayrollController::class, 'xmlPayrollEmployee'])->name('payroll.employee.xml');
    Route::post('/payroll-recounting-ajax', [\App\Http\Controllers\HCGA\PayrollController::class, 'recountingAjax'])->name('payroll.recounting.ajax');
    Route::get('/payroll-deductions/employees', [\App\Http\Controllers\HCGA\PayrollController::class, 'deductionsEmployees'])->name('payroll.deductions.employees');
    Route::get('/payroll-deductions/list', [\App\Http\Controllers\HCGA\PayrollController::class, 'deductionsList'])->name('payroll.deductions.list');
    Route::post('/payroll-deductions/auto-pull', [\App\Http\Controllers\HCGA\PayrollController::class, 'deductionsAutoPull'])->name('payroll.deductions.autopull');
    Route::post('/payroll-deductions/save', [\App\Http\Controllers\HCGA\PayrollController::class, 'deductionsSave'])->name('payroll.deductions.save');
    Route::delete('/payroll-deductions/{id}', [\App\Http\Controllers\HCGA\PayrollController::class, 'deductionsDelete'])->name('payroll.deductions.delete');
    Route::get('/search-employee', function () {
        return view('humanresources.overtime.search-employee');
    });
    Route::get('/overtime-new', function () {
        return view('humanresources.overtime.new');
    });
    Route::get('/overtime-criteria-options', [\App\Http\Controllers\HCGA\OvertimeLegacyController::class, 'getFilterCriteria']);
    Route::get('/overtime-employees-by-criteria', [\App\Http\Controllers\HCGA\OvertimeLegacyController::class, 'getEmployeesByCriteria']);
    Route::post('/overtime-calculate-realtime', [\App\Http\Controllers\HCGA\OvertimeLegacyController::class, 'calculateOvertimeRealtime']);
    Route::get('/xml-overtime-new', [\App\Http\Controllers\HCGA\OvertimeLegacyController::class, 'xmlOvertimeNew']);
    Route::post('/overtime-new-update', [\App\Http\Controllers\HCGA\OvertimeLegacyController::class, 'updateNew']);
    Route::get('/overtime-approval', function () {
        return view('humanresources.overtime.overtime-approval');
    });
    Route::get('/xml-overtime-approval', [\App\Http\Controllers\HCGA\OvertimeLegacyController::class, 'xmlOvertimeApproval']);
    Route::post('/overtime-approval-update', [\App\Http\Controllers\HCGA\OvertimeLegacyController::class, 'updateApproval']);
    Route::get('/overtime-all', function () {
        return view('humanresources.overtime.all');
    });
    Route::get('/xml-overtime-all', [\App\Http\Controllers\HCGA\OvertimeLegacyController::class, 'xmlOvertimeAll']);
    Route::get('/kjk-all', function () {
        return view('humanresources.overtime.kjk-all');
    });
    Route::get('/xml-kjk-all', function () {
        return view('humanresources.overtime.xml.kjk-all');
    });
    Route::get('/new-select-employee', function () {
        return view('humanresources.overtime.new-select-employee');
    });
    Route::get('/xml-select-employee', function () {
        return view('humanresources.overtime.xml.new-select-employee');
    });
    Route::get('/new-kjk-employee', function () {
        return view('humanresources.overtime.new-kjk-employee');
    });
    Route::get('/xml-kjk-employee', function () {
        return view('humanresources.overtime.xml.new-kjk-employee');
    });
    Route::get('/kjk-new', function () {
        return view('humanresources.overtime.new-kjk');
    });
    Route::get('/xml-kjk-new', function () {
        return view('humanresources.overtime.xml.kjk-new');
    });
    Route::post('/kjk-new-update', function () {
        return view('humanresources.overtime.new-kjk-update');
    });
    Route::post('/kjk-approval-update', function () {
        return view('humanresources.overtime.kjk-approval-update');
    });
    Route::get('/kjk-approval', function () {
        return view('humanresources.overtime.kjk-approval');
    });
    Route::get('/xml-kjk-approval', function () {
        return view('humanresources.overtime.xml.kjk-approval');
    });
    Route::get('/load-overtime-kategori', function () {
        return view('humanresources.overtime.load-overtime-kategori');
    });
    Route::get('/load-pegawai', function () {
        return view('humanresources.overtime.load-pegawai');
    });
    Route::get('/payroll-approval', [\App\Http\Controllers\HCGA\PayrollController::class, 'payrollApprovalIndex'])->name('payroll.approval.index');
    Route::get('/payroll-approval-cancel', [\App\Http\Controllers\HCGA\PayrollController::class, 'payrollApprovalCancel'])->name('payroll.approval.cancel');
    Route::get('/payroll-approval-reject', [\App\Http\Controllers\HCGA\PayrollController::class, 'payrollApprovalReject'])->name('payroll.approval.reject');
    Route::get('/payroll-approval-approve', [\App\Http\Controllers\HCGA\PayrollController::class, 'payrollApprovalApprove'])->name('payroll.approval.approve');
    Route::get('/xml-payroll-approval', [\App\Http\Controllers\HCGA\PayrollController::class, 'xmlPayrollApproval'])->name('payroll.approval.xml');
    Route::get('/payroll-approval-detail', [\App\Http\Controllers\HCGA\PayrollController::class, 'payrollApprovalDetail'])->name('payroll.approval.detail');
    Route::get('/xml-payroll-approval-detail', [\App\Http\Controllers\HCGA\PayrollController::class, 'xmlPayrollApprovalDetail'])->name('payroll.approval.detail.xml');
    Route::get('/payroll-approved', [\App\Http\Controllers\HCGA\PayrollController::class, 'payrollApprovedIndex'])->name('payroll.approved.index');
    Route::get('/approved-payroll', [\App\Http\Controllers\HCGA\PayrollController::class, 'payrollApprovedIndex']);
    Route::get('/payroll-report', [\App\Http\Controllers\HCGA\PayrollController::class, 'payrollReportIndex'])->name('payroll.report.index');
    Route::get('/api/payroll/report-data', [\App\Http\Controllers\HCGA\PayrollController::class, 'apiPayrollReportData'])->name('payroll.report.data');
    Route::get('/payroll-report-export-excel', [\App\Http\Controllers\HCGA\PayrollController::class, 'exportPayrollReportExcel'])->name('payroll.report.excel');
    Route::get('/xml-payroll-approved', function () {
        return view('humanresources.payroll.xml.payroll-approved');
    });
    Route::get('/xml-approved-payroll', function () {
        return view('humanresources.payroll.xml.payroll-approved');
    });
    Route::get('/payroll-approved-detail', function () {
        return view('humanresources.payroll.payroll-approved-detail');
    });
    Route::get('/payroll-approved-detail-detail', function () {
        return view('humanresources.payroll.payroll-approved-detail-detail');
    });
    Route::get('/xml-payroll-approved-detail', function () {
        return view('humanresources.payroll.xml.payroll-approved-detail');
    });

    Route::get('/payroll-generator', [\App\Http\Controllers\HCGA\PayrollController::class, 'payrollGeneratorIndex'])->name('payroll.generator.index');
    Route::post('/payroll-generator-submit', [\App\Http\Controllers\HCGA\PayrollController::class, 'payrollGeneratorSubmit'])->name('payroll.generator.submit');
    Route::get('/payroll-generator-detail', [\App\Http\Controllers\HCGA\PayrollController::class, 'payrollGeneratorDetail'])->name('payroll.generator.detail');
    Route::get('/xml-payroll-generator-employee', [\App\Http\Controllers\HCGA\PayrollController::class, 'xmlPayrollGeneratorEmployee'])->name('payroll.generator.employee.xml');
    Route::post('/payroll-generator-detail-update', [\App\Http\Controllers\HCGA\PayrollController::class, 'payrollGeneratorDetailUpdate'])->name('payroll.generator.detail.update');
    Route::get('/payroll-generator-template', [\App\Http\Controllers\HCGA\PayrollController::class, 'downloadGeneratorTemplate'])->name('payroll.generator.template');
    Route::post('/payroll-generator-upload-excel', [\App\Http\Controllers\HCGA\PayrollController::class, 'uploadGeneratorExcel'])->name('payroll.generator.upload.excel');
    Route::get('/payroll-generator-upload', function () {
        return view('humanresources.payroll.payroll-generator-upload');
    });
    Route::get('/payroll-generator-upload-process', function () {
        return view('humanresources.payroll.payroll-generator-upload-process');
    });
    Route::get('/payroll-template-master-lists', function () {
        return view('humanresources.payroll.payroll-template-master-lists');
    });
    Route::post('/payroll-template-master-lists-update', function () {
        return view('humanresources.payroll.payroll-template-master-lists-update');
    });
    Route::get('/xml-payroll-template-master-lists', function () {
        return view('humanresources.payroll.xml.payroll-template-master-lists');
    });
    Route::get('/payroll-template-master', function () {
        return view('humanresources.payroll.payroll-template-master');
    });
    Route::post('/payroll-template-master-update', function () {
        return view('humanresources.payroll.payroll-template-master-update');
    });
    Route::get('/xml-payroll-template-master', function () {
        return view('humanresources.payroll.xml.payroll-template-master');
    });
    Route::get('/payroll-template-master-lists-add', function () {
        return view('humanresources.payroll.payroll-template-master-lists-add');
    });
    Route::post('/payroll-template-master-lists-add-update', function () {
        return view('humanresources.payroll.payroll-template-master-lists-add-update');
    });
    Route::get('/xml-payroll-template-master-lists-add', function () {
        return view('humanresources.payroll.xml.payroll-template-master-lists-add');
    });
    Route::get('/attendant-entry', [\App\Http\Controllers\HCGA\AttendancesController::class, 'entryIndex']);
    Route::post('/attendant-entry-update', [\App\Http\Controllers\HCGA\AttendancesController::class, 'entryUpdate']);
    Route::get('/xml-attendant-entry', [\App\Http\Controllers\HCGA\AttendancesController::class, 'entryXml']);

    Route::get('/attendant-entry-lists', [\App\Http\Controllers\HCGA\AttendancesController::class, 'entryLists']);
    Route::post('/attendant-entry-lists-update', [\App\Http\Controllers\HCGA\AttendancesController::class, 'entryListsUpdate']);
    Route::get('/xml-attendant-entry-lists', [\App\Http\Controllers\HCGA\AttendancesController::class, 'entryListsXml']);

    Route::get('/attendant-data-lists', [\App\Http\Controllers\HCGA\AttendancesController::class, 'dataLists']);
    Route::get('/xml-attendant-data-lists', [\App\Http\Controllers\HCGA\AttendancesController::class, 'dataListsXml']);
    Route::get('/resigned-employee', function () {
        return view('humanresources.other.resigned-employee');
    });
    Route::get('/xml-resigned-employee', function () {
        return view('humanresources.other.xml.resigned-employee');
    });
    Route::get('/resigned-employee-detail', function () {
        return view('humanresources.other.resigned-employee-detail');
    });

    // Employee lists controller routes (theme sync)
    Route::get('/employee-lists', [\App\Http\Controllers\HCGA\EmployeeListsController::class, 'index']);
    Route::get('/hrd-employee-user-account-modal', [\App\Http\Controllers\HCGA\EmployeeUserAccountController::class, 'modal']);
    Route::get('/hrd-employee-user-account-candidates', [\App\Http\Controllers\HCGA\EmployeeUserAccountController::class, 'searchCandidates']);
    Route::post('/hrd-employee-user-account-save', [\App\Http\Controllers\HCGA\EmployeeUserAccountController::class, 'save']);
    Route::get('/xml-employee-lists', [\App\Http\Controllers\HCGA\EmployeeListsController::class, 'xmlData']);
    Route::post('/employee-lists-columns-save', [\App\Http\Controllers\HCGA\EmployeeListsController::class, 'saveColumns']);
    Route::get('/employee-lists-detail', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'index']);
    Route::get('/employee-lists-detail-data', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'data']);
    Route::post('/employee-lists-detail-data-submit', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'submitData']);
    Route::get('/employee-lists-detail-other', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'other']);
    Route::get('/employee-lists-detail-insurance', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'insurance']);
    Route::get('/xml-employee-lists-detail-document', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'xmlDocument']);
    Route::get('/xml-employee-lists-detail-history', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'xmlHistory']);
    Route::get('/xml-employee-lists-detail-insurance', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'xmlInsurance']);
    Route::post('/employee-lists-detail-insurance-update', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'updateInsurance']);
    Route::get('/xml-employee-lists-detail-organization', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'xmlOrganization']);
    Route::post('/employee-lists-detail-organization-update', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'updateOrganization']);
    Route::get('/xml-employee-lists-detail-family', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'xmlFamily']);
    Route::post('/employee-lists-detail-family-update', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'updateFamily']);
    Route::get('/xml-employee-lists-detail-academic', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'xmlAcademic']);
    Route::get('/xml-employee-lists-detail-contact-points', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'xmlContactPoints']);
    Route::post('/employee-lists-detail-contact-points-update', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'updateContactPoints']);
    Route::post('/employee-lists-detail-academic-update', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'updateAcademic']);
    Route::get('/xml-employee-lists-detail-informal', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'xmlInformal']);
    Route::post('/employee-lists-detail-informal-update', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'updateInformal']);
    Route::get('/xml-employee-lists-detail-driving', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'xmlDriving']);
    Route::post('/employee-lists-detail-driving-update', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'updateDriving']);
    Route::get('/xml-employee-lists-detail-health', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'xmlHealth']);
    Route::post('/employee-lists-detail-health-update', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'updateHealth']);
    Route::post('/employee-lists-detail-other-medical-save', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'saveOtherMedical']);
    Route::get('/xml-employee-lists-detail-experience', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'xmlExperience']);
    Route::post('/employee-lists-detail-experience-update', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'updateExperience']);
    Route::get('/xml-employee-lists-detail-skill', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'xmlOtherSkill']);
    Route::get('/xml-employee-lists-detail-other-skill', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'xmlOtherSkill']);
    Route::post('/employee-lists-detail-skill-update', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'updateOtherSkill']);
    Route::post('/employee-lists-detail-other-skill-update', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'updateOtherSkill']);
    Route::get('/xml-employee-lists-detail-award', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'xmlAward']);
    Route::post('/employee-lists-detail-award-update', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'updateAward']);
    Route::get('/xml-employee-lists-detail-language', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'xmlLanguage']);
    Route::post('/employee-lists-detail-language-update', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'updateLanguage']);
    Route::post('/employee-lists-detail-photo-upload', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'uploadPhoto']);
    Route::get('/employee-lists-detail-document-form', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'documentForm']);
    Route::post('/employee-lists-detail-document-upload', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'uploadDocument']);
    Route::post('/employee-lists-detail-document-delete', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'deleteDocument']);
    Route::get('/employee-lists-detail-recruitment', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'recruitment']);
    Route::get('/employee-lists-detail-history', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'history']);
    Route::get('/employee-lists-detail-document', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'document']);
    Route::get('/employee-lists-detail-kjk', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'kjk']);
    Route::get('/xml-employee-lists-detail-kjk', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'xmlKjk']);
    Route::get('/xml-employee-lists-detail-overtime', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'xmlOvertime']);
    Route::get('/employee-lists-detail-family', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'family']);
    Route::get('/employee-lists-detail-education', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'education']);
    Route::get('/employee-lists-detail-skill', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'skill']);
    Route::post('/employee-lists-detail-submit', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'submitData']);
    Route::get('/upload-foto', [\App\Http\Controllers\HCGA\EmployeeDetailController::class, 'photoModal']);

    // Attendance manual correction routes (theme sync)
    Route::get('/attendant-data/manual-correction/context', [\App\Http\Controllers\HCGA\AttendancesController::class, 'manualCorrectionContext'])->name('attendance.manual.context');
    Route::post('/attendant-data/manual-correction/store', [\App\Http\Controllers\HCGA\AttendancesController::class, 'manualCorrectionStore'])->name('attendance.manual.store');
    Route::get('/attendant-data/manual-correction/settings', [\App\Http\Controllers\HCGA\AttendancesController::class, 'manualCorrectionSettingsGet'])->name('attendance.manual.settings.get');
    Route::post('/attendant-data/manual-correction/settings', [\App\Http\Controllers\HCGA\AttendancesController::class, 'manualCorrectionSettingsSave'])->name('attendance.manual.settings.save');
    Route::get('/attendant-data/manual-correction/template', [\App\Http\Controllers\HCGA\AttendancesController::class, 'manualCorrectionTemplate'])->name('attendance.manual.template');
    Route::post('/attendant-data/manual-correction/bulk-upload', [\App\Http\Controllers\HCGA\AttendancesController::class, 'manualCorrectionBulkUpload'])->name('attendance.manual.bulk_upload');
    Route::get('/attendant-data/manual-correction/pending', [\App\Http\Controllers\HCGA\AttendancesController::class, 'manualCorrectionPendingList'])->name('attendance.manual.pending');
    Route::post('/attendant-data/manual-correction/bulk-approve', [\App\Http\Controllers\HCGA\AttendancesController::class, 'manualCorrectionBulkApprove'])->name('attendance.manual.bulk_approve');
    Route::get('/attendant-data/manual-correction/audit-logs', [\App\Http\Controllers\HCGA\AttendancesController::class, 'manualCorrectionAuditLogs'])->name('attendance.manual.audit');

    // Payroll THR approval and payslip routes (theme sync)
    Route::post('/api/payroll/thr/tax-mode', [\App\Http\Controllers\HCGA\PayrollThrController::class, 'updateTaxMode'])->name('payroll.thr.tax-mode');
    Route::post('/api/payroll/thr/submit-approval', [\App\Http\Controllers\HCGA\PayrollThrController::class, 'submitApproval'])->name('payroll.thr.submit-approval');
    Route::post('/api/payroll/thr/revert-draft', [\App\Http\Controllers\HCGA\PayrollThrController::class, 'revertToDraft'])->name('payroll.thr.revert-draft');
    Route::get('/payslip-thr', [\App\Http\Controllers\HCGA\PayrollThrController::class, 'showDigitalPayslip'])->name('payroll.thr.payslip.show');
    Route::get('/payslip-thr/pdf', [\App\Http\Controllers\HCGA\PayrollThrController::class, 'downloadPayslipPdf'])->name('payroll.thr.payslip.pdf');
    Route::get('/payroll-thr-export-excel', [\App\Http\Controllers\HCGA\PayrollThrController::class, 'exportExcel'])->name('payroll.thr.export.excel');
    Route::get('/payroll-thr-approval', [\App\Http\Controllers\HCGA\PayrollThrController::class, 'approvalIndex'])->name('payroll.thr.approval.index');
    Route::get('/xml-payroll-thr-approval', [\App\Http\Controllers\HCGA\PayrollThrController::class, 'xmlApprovalList'])->name('payroll.thr.approval.xml');
    Route::get('/payroll-thr-approval-detail', [\App\Http\Controllers\HCGA\PayrollThrController::class, 'approvalDetail'])->name('payroll.thr.approval.detail');
    Route::get('/xml-payroll-thr-approval-detail', [\App\Http\Controllers\HCGA\PayrollThrController::class, 'xmlApprovalDetail'])->name('payroll.thr.approval.detail.xml');
    Route::get('/payroll-thr-approval-approve', [\App\Http\Controllers\HCGA\PayrollThrController::class, 'approvalApprove'])->name('payroll.thr.approval.approve');
    Route::get('/payroll-thr-approval-reject', [\App\Http\Controllers\HCGA\PayrollThrController::class, 'approvalReject'])->name('payroll.thr.approval.reject');
    Route::get('/payroll-thr-approval-cancel', [\App\Http\Controllers\HCGA\PayrollThrController::class, 'approvalCancel'])->name('payroll.thr.approval.cancel');
