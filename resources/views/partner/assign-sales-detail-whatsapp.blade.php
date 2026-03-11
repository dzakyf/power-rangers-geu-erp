<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

$cekLeads = DB::table('opportunity')->where('ID', $_GET['rId'])->first();
if ($cekLeads && $cekLeads->Status == "Deal") {
	echo "https://wa.me/62" . $cekLeads->Phone_Number . "?text=Silahkan+akses+link+tersebut+untuk+registrasi%0D%0Ahttps%3A%2F%2Fdev.hexamatika.com%2Fgeu%2Fhcga%2Fpublic%2Fpartner-registration%3Fleads%3D".urlencode($cekLeads->Task_ID);
}else {
	echo "";
}
