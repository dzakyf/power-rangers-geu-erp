<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

// Update data asset dari halaman /asset-lists-detail
try {
    $id = $_POST['ID'] ?? '';
    if ($id == '') { echo "error: ID Asset kosong"; exit; }

    // Peta tipe kolom -> string kosong untuk kolom tanggal/numerik diubah jadi NULL
    $types = [];
    foreach (DB::select("SHOW COLUMNS FROM asset_list") as $c) {
        $types[$c->Field] = $c->Type;
    }

    // Kolom yang tidak boleh ikut di-update dari form
    $skip = ['_token', 'Task_ID', 'ID', 'Created_Date', 'Created_By', 'Location', 'Incoming_Type', 'PO_Item_Select', 'Item_Qty_Select'];

    $data = [];
    foreach ($_POST as $k => $v) {
        if (in_array($k, $skip)) { continue; }
        if (!isset($types[$k])) { continue; }
        if ($v === '' && preg_match('/^(date|datetime|timestamp|int|tinyint|smallint|mediumint|bigint|float|double|decimal)/', $types[$k])) {
            $data[$k] = null;
        } else {
            $data[$k] = $v;
        }
    }

    $data['Last_User'] = Auth::user()->username ?? '';
    $data['Last_Modified_Date'] = now();

    DB::table('asset_list')->where('ID', $id)->update($data);

    echo "success";
} catch (\Throwable $th) {
    Log::error($th);
    echo "error: " . $th->getMessage();
}
