<?php
error_reporting(0);
header('Content-Type: application/json');

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$out = ['status' => 'error', 'message' => 'unknown error'];
try {
	if (!Schema::hasTable('user_grid_prefs')) {
		throw new Exception('Tabel user_grid_prefs belum ada.');
	}
	if (!employee_grid_can_manage_columns()) {
		throw new Exception('Hanya user tertentu yang dapat menyimpan pengaturan kolom.');
	}
	$keys = array_values(array_intersect(explode(',', (string) request('cols', '')), array_keys(employee_grid_columns())));
	if (!count($keys)) {
		throw new Exception('Pilih minimal 1 kolom.');
	}
	DB::table('user_grid_prefs')->updateOrInsert(
		['username' => Auth::user()->username, 'view' => 'employee-lists'],
		['columns_json' => json_encode($keys), 'Updated_Date' => date('Y-m-d H:i:s')]
	);
	$out = ['status' => 'ok'];
} catch (Exception $e) {
	$out['message'] = $e->getMessage();
}
echo json_encode($out);
