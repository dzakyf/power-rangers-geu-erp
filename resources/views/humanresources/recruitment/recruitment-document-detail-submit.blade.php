<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

$ars = "";
$ax = "";
$vals = "";

foreach ($_POST as $key => $value) {


	if ($key != 'ID' && $key != 'SubmitCustDetail') {




		$ars 	.= "$key,";

		$vals	.= "'$_POST[$key]',";


		$MX .= "$key = '$_POST[$key]',";
	}
}

$vals = substr($vals, 0, -1);
$ars = substr($ars, 0, -1);
$MX = substr($MX, 0, -1);

$getRecr = DB::table('recruitment')->where("ID", $_POST['ID'])->first();

DB::update(DB::raw("UPDATE recruitment set Status='UPLOADED',Document_Date=NOW(),Document_Status='ACCEPT' WHERE ID=$_POST[ID]"));
DB::table('recruitment_document')->where("Task_ID", $getRecr->Task_ID)->update([
	'CV' => $_POST['File_CV'],
	'KTP' => $_POST['File_KTP'],
	'Foto' => $_POST['File_Foto'],
	'Ijazah' => $_POST['File_Ijazah'],
	'Sertifikat' => $_POST['File_Sertifikat'],
	'NPWP' => $_POST['File_NPWP']
]);


echo "success";
