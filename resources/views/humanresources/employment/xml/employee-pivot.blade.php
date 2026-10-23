<?php
error_reporting(0);
ini_set('memory_limit', '-1');
header('Content-Type: application/json');

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

$out = [];
try {
	$map = employee_grid_columns();
	$reqCols = isset($_GET['cols']) ? explode(',', $_GET['cols']) : [];
	$keys = array_values(array_intersect($reqCols, array_keys($map)));
	if (!count($keys)) {
		$savedJson = DB::table('user_grid_prefs')
			->where('username', Auth::user()->username)
			->where('view', 'employee-lists')
			->value('columns_json');
		$saved = $savedJson ? json_decode($savedJson, true) : [];
		$keys = array_values(array_intersect((array) $saved, array_keys($map)));
		if (!count($keys)) $keys = employee_grid_default_columns();
	}
	$needAkad = in_array('Academic_Major', $keys, true);
	$akademis = [];
	if ($needAkad) {
		$aq = DB::table('employment_akademic')->get();
		foreach ($aq as $a) { $akademis[$a->Task_ID] = $a->Education_Major; }
	}
	$dbKeys = array_values(array_diff($keys, ['Age', 'Academic_Major']));
	if (in_array('Age', $keys, true) && !in_array('Birth_Date', $dbKeys, true)) $dbKeys[] = 'Birth_Date';
	if ($needAkad && !in_array('Task_ID', $dbKeys, true)) $dbKeys[] = 'Task_ID';
	$LSP = Auth::user()->Location;
	$data = DB::table('employment')
		->select($dbKeys)
		->where('Location', $LSP)
		->where('Status', 'ACTIVE')
		->where('Category', 'INTERNAL')
		->orderBy('Name', 'ASC')
		->get();
	$norm = function ($s) {
		return ($s === null || $s === '') ? '(Kosong)' : (string) $s;
	};
	foreach ($data as $d) {
		$row = [];
		foreach ($keys as $k) {
			if ($k === 'Age') {
				$b = isset($d->Birth_Date) ? $d->Birth_Date : '';
				$row[$map[$k][0]] = ($b !== '' && $b !== '0000-00-00') ? (int) floor((time() - strtotime($b)) / 31556926) : 0;
			} elseif ($k === 'Sisa_Cuti') {
				$row[$map[$k][0]] = (isset($d->Sisa_Cuti) && is_numeric($d->Sisa_Cuti)) ? (int) $d->Sisa_Cuti : 0;
			} else {
				$row[$map[$k][0]] = $norm(employee_grid_cell($k, $d, $akademis));
			}
		}
		$out[] = $row;
	}
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
	return;
}
echo json_encode($out);
