<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\SurveyFormController;
use Illuminate\Support\Facades\Route;

$surveyPrefs = 'partner-survey';
Route::get("$surveyPrefs-dashboard", [SurveyController::class, 'dashboardSurvey']);
Route::get("$surveyPrefs-index", [SurveyController::class, 'indexSurvey']);
Route::get("$surveyPrefs-create", [SurveyController::class, 'createMasterDataSurvey']);
Route::get("$surveyPrefs-create-reply", [SurveyController::class, 'createReplyMasterDataSurvey']);
Route::get("$surveyPrefs-data-history", [SurveyController::class, 'getSurveyHistoryData']);
Route::get("$surveyPrefs-data", [SurveyController::class, 'getMasterDataSurvey']);
Route::post("$surveyPrefs-submit", [SurveyController::class, 'submitMasterDataSurvey']);
Route::post("$surveyPrefs-update", [SurveyController::class, 'updateMasterDataSurvey']);
Route::post("$surveyPrefs-delete", [SurveyController::class, 'deleteMasterDataSurvey']);
Route::get("$surveyPrefs-approval-index", [SurveyController::class, 'indexApprovalSurvey']);
Route::get("$surveyPrefs-approval-data", [ApprovalController::class, 'getApprovalData']);
Route::get("$surveyPrefs-approval-data-log", [ApprovalController::class, 'getApprovalDataLog']);
Route::post("$surveyPrefs-approval-submit", [ApprovalController::class, 'processSurveyToNextProgress']);

$surveyFormPrefs = 'partner-survey-form';
Route::get("$surveyFormPrefs-index", [SurveyFormController::class, 'index']);
Route::get("$surveyFormPrefs-builder", [SurveyFormController::class, 'builder']);
Route::post("$surveyFormPrefs-save", [SurveyFormController::class, 'save']);
Route::post("$surveyFormPrefs-publish", [SurveyFormController::class, 'publish']);
Route::post("$surveyFormPrefs-create-version", [SurveyFormController::class, 'createVersion']);
