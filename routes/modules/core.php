<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\AdminController;

    Route::get('/privilege-information', function () {
        return view('privilege-information');
    });

    Route::get('/change-password', function () {
        return view('change-password');
    });

    Route::post('/change-password-submit', function () {
        return view('change-password-submit');
    });

    Route::get('/dashboard1', [AdminController::class, 'index']);

                                                        
                      
                                                               
             
     
    Route::get('/home-mymenu', function () {
        return view('home-mymenu');
    });

    Route::get("/candidate/dashboard", [CandidateController::class, "dashboard"]);

    Route::get("/candidate/profile", [CandidateController::class, "profile"]);

    Route::post("/candidate/profile-update", [CandidateController::class, "profileUpdate"]);

    Route::get("/candidate/documents", [CandidateController::class, "documents"]);

    Route::post("/candidate/document-upload", [CandidateController::class, "documentUpload"]);

    Route::get("/candidate/assessment", [CandidateController::class, "assessment"]);

    Route::get("/candidate/progress", [CandidateController::class, "progress"]);

    Route::post("/candidate/progress-confirm", [CandidateController::class, "confirmSchedule"]);

    Route::post("/candidate/availability-update", [CandidateController::class, "updateAvailability"]);

    Route::get("/candidate/offering-download", [CandidateController::class, "downloadOfferingLetter"]);

    Route::post("/candidate/offering-upload", [CandidateController::class, "uploadSignedOffering"]);

    Route::get("/candidate/password", [CandidateController::class, "passwordForm"]);

    Route::post("/candidate/password-update", [CandidateController::class, "passwordUpdate"]);
