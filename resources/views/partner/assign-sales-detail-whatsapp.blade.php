<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

$cekLeads = DB::table('opportunity')->where('ID', $_GET['rId'])->first();
if ($cekLeads && $cekLeads->Status == "Deal") {
    // Reuse the still-valid token so re-sending the blast never invalidates
    // a link that was already shared with the lead.
    $token = $cekLeads->registration_token;
    $stillValid = $token
        && (!$cekLeads->token_expires_at || \Carbon\Carbon::parse($cekLeads->token_expires_at)->gte(now()));
    if (!$stillValid) {
        $token = md5($cekLeads->Task_ID . time() . config('app.key'));
        DB::table('opportunity')->where('ID', $cekLeads->ID)->update([
            'registration_token' => $token,
            'token_expires_at'   => now()->addDays(3),
        ]);
    }
    $link = "https://dev.hexamatika.com/geu/hcga/public/partner-registration?token=" . urlencode($token);
    echo "https://wa.me/62" . $cekLeads->Phone_Number . "?text=Silahkan+akses+link+tersebut+untuk+registrasi%0D%0A" . urlencode($link);
}else {
    echo "";
}
