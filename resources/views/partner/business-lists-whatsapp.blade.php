<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
// $LSP = "GEU-HO";
// $Saya = "admin";

$cekLeads = DB::table('opportunity_partner')->where('id', $_GET['rId'])->first();
if ($cekLeads && $cekLeads->status == "ACTIVE") {
	$getUser = DB::table('users')->where("Emp_ID", $cekLeads->lead_id)->first();
	$cekLead = DB::table('opportunity')->where('Task_ID', $cekLeads->lead_id)->first();
	echo "https://wa.me/62" . $cekLead->Phone_Number . "?text=Silahkan+akses+link+tersebut+untuk+melihat+akses+yang+anda+miliki%0D%0Alink%3A+".urlencode(url('login'))."%0D%0Ausername+%3A+".urlencode($getUser->username)."+%0D%0Apassword+%3A+geupartner123";
}else {
	echo "";
}
