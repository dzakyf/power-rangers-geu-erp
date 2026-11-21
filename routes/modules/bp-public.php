<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Partner\PartnerRegistrationController;
use App\Http\Controllers\Partner\ChatListController as PartnerChatListController;
use App\Http\Controllers\Partner\MasterDataController;

Route::get('partner-early-registration', function () {
    return view('partner.leads');
});
Route::get('partner-registration', function () {
    // Validate the blast token up-front so leads with an expired/used link
    // see a clear notice instead of a form they cannot submit. The linked
    // opportunity is also passed in so the form prefills the data captured
    // earlier by the sales team.
    $token = request()->query('token');
    $lead = null;
    if ($token) {
        $lead = \Illuminate\Support\Facades\DB::table('opportunity')
            ->where('registration_token', $token)
            ->where(function ($q) {
                $q->whereNull('token_expires_at')
                    ->orWhere('token_expires_at', '>=', now());
            })
            ->first();
        if (!$lead) {
            return view('partner.registration-expired');
        }
    }
    return view('partner.registration-public', ['lead' => $lead]);
});
Route::post('partner-registration-submit', [PartnerRegistrationController::class, 'submit']);
$chatPref = "partner-chat";
Route::post("$chatPref-fetch-new", [PartnerChatListController::class, 'fetchNewMessages']);
// Public address master data endpoints
Route::get('master-province', [MasterDataController::class, 'provinceSearch']);
Route::get('master-city', [MasterDataController::class, 'citySearch']);
Route::get('master-district', [MasterDataController::class, 'districtSearch']);
Route::get('master-subdistrict', [MasterDataController::class, 'subdistrictSearch']);
Route::get('master-postal-code', [MasterDataController::class, 'postalCodeSearch']);
