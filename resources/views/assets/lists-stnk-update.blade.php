<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

// Endpoint UPDATE STNK: tahunan (perpanjang tiap tahun) & 5-tahunan (tiap 5 tahun).
// Setiap update selalu menulis baris BARU ke asset_stnk_log (history tidak pernah dihapus/ditimpa).
$assetNo = $_POST['Asset_No'] ?? '';
$jenis   = $_POST['Jenis'] ?? 'TAHUNAN';          // TAHUNAN | 5_TAHUNAN
$tanggal = $_POST['Tanggal_Berlaku'] ?? '';
$catatan = $_POST['Catatan'] ?? '';

if ($assetNo == '')  { echo "error: Asset tidak ditemukan"; exit; }
if ($tanggal == '')  { echo "error: Tanggal berlaku STNK wajib diisi"; exit; }
if (!in_array($jenis, ['TAHUNAN', '5_TAHUNAN'])) { echo "error: Jenis STNK tidak valid"; exit; }

$asset = DB::table('asset_list')->where('Asset_No', $assetNo)->first();
if (!$asset) { echo "error: Asset tidak ditemukan"; exit; }

$col = ($jenis == '5_TAHUNAN') ? 'STNK_5_Tahun' : 'STNK_Tahunan';
$sebelumnya = $asset->$col;
if ($sebelumnya == '0000-00-00') { $sebelumnya = ''; }

DB::table('asset_stnk_log')->insert([
    'Asset_No'          => $assetNo,
    'Jenis'             => $jenis,
    'Tanggal_Efektif'   => $tanggal,
    'Tanggal_Sebelumnya'=> ($sebelumnya != '' ? $sebelumnya : null),
    'Catatan'           => $catatan,
    'Updated_By'        => Auth::user()->username ?? 'system',
]);

// Nilai berlaku terbaru di tabel asset (tampil di grid), history lengkap di log
DB::table('asset_list')->where('Asset_No', $assetNo)->update([
    $col                => $tanggal,
    'Last_User'         => Auth::user()->username ?? 'system',
    'Last_Modified_Date'=> now(),
]);

echo "success";
