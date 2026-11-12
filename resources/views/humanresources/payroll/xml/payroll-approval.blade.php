<?php
/**
 * XML Endpoint Daftar Batch Payroll Menunggu Approval
 * Didelegasikan ke PayrollController::xmlPayrollApproval() yang menggunakan
 * ApprovalWorkflowService (via PayrollApprovalService) sesuai arsitektur Controller & Service Layer.
 */
return app(\App\Http\Controllers\HCGA\PayrollController::class)->xmlPayrollApproval(request());
