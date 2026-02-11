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
$result = DB::select(DB::raw("SELECT DISTINCT Invoice_To from proforma WHERE Invoice_To LIKE '%$_GET[chars]%' AND Invoice_To IS NOT NULL LIMIT 5"));

if (count($result) > 0) {
	foreach ($result as $data) {

		$Description = $data->Invoice_To;

		$DT = $data->Invoice_To;
		$arr[] = array("id" => trim($data->Invoice_To), "thumbnail" => $DT, "data" => $data->Invoice_To, "description" => $Description);
	}
}

echo json_encode($arr);
