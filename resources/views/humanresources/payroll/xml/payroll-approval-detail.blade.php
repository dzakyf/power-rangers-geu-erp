<?php
/**
 * XML Endpoint Rincian Karyawan pada Batch Payroll Approval
 * Didelegasikan ke PayrollController::xmlPayrollApprovalDetail() sesuai arsitektur Controller & Service Layer.
 */
return app(\App\Http\Controllers\HCGA\PayrollController::class)->xmlPayrollApprovalDetail(request());
