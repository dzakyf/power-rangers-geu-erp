<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

if (strlen(trim($_GET['chars'])) < 3) exit;

$arr = array();
$result = DB::select(DB::raw("SELECT nama as Signed_By, jabatan as Signed_By_Position from proforma_signed WHERE active=1 AND nama LIKE '%$_GET[chars]%' AND nama IS NOT NULL ORDER BY nama DESC LIMIT 5"));

if (count($result) > 0) {
	foreach ($result as $data) {

		$Description = $data->Signed_By_Position;


		$DT = $data->Signed_By . "|" . $data->Signed_By_Position;
		$arr[] = array("id" => trim($data->Signed_By), "thumbnail" => $DT, "data" => $data->Signed_By, "description" => $Description);
	}
}

echo json_encode($arr);
