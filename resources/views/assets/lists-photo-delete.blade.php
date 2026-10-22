<?php
/**
 * POST /asset-lists-photo-delete
 * Hapus SATU foto aset (file + baris meta_files). Foto lain tidak tersentuh.
 * asset_list.Photo (cover) langsung disinkronkan ulang ke foto pertama yang tersisa.
 */
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

$photoId = $_POST['Photo_ID'] ?? $_POST['photo_id'] ?? '';
$assetNo = trim($_POST['Asset_No'] ?? '');
$isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';

$balas = function ($ok, $msg) use ($isAjax) {
    if ($isAjax) {
        header('Content-type: application/json');
        echo json_encode(['status' => $ok ? 'success' : 'error', 'message' => $msg]);
        return;
    }
    echo ($ok ? 'success' : 'error: ' . $msg);
};

$foto = DB::table('meta_files')->where('ID', $photoId)->where('Topic', 'asset-photo')->first();
if (!$foto) {
    $balas(false, 'Foto tidak ditemukan.');
    return;
}

$assetNo = $assetNo !== '' ? $assetNo : $foto->Map_To;

$berkas = public_path('filevendor/' . $foto->Path);
if ($foto->Path && file_exists($berkas)) {
    @unlink($berkas);
}

DB::table('meta_files')->where('ID', $foto->ID)->delete();

// Sinkronkan cover ke foto pertama yang masih ada (bisa jadi kosong)
$sisa = DB::table('meta_files')
    ->where('Topic', 'asset-photo')
    ->where('Map_To', $assetNo)
    ->orderBy('ID', 'ASC')
    ->first();

DB::table('asset_list')->where('Asset_No', $assetNo)->update([
    'Photo'              => $sisa ? asset('filevendor/' . $sisa->Path) : null,
    'Last_User'          => Auth::user()->username ?? '',
    'Last_Modified_Date' => now(),
]);

$balas(true, 'Foto dihapus.');
?>