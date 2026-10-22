<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

// Endpoint UPDATE KIR: KIR pertama jadi record (Ke=1), KIR berikutnya di-APPEND
// sebagai baris baru (tanpa menimpa nilai lama). Jadwal KIR berikutnya = Tanggal KIR + 1 tahun.
$assetNo    = $_POST['Asset_No'] ?? '';
$tanggalKIR = $_POST['Tanggal_KIR'] ?? '';
$berkas     = $_POST['Berkas_KIR'] ?? '';
$catatan    = $_POST['Catatan'] ?? '';

if ($assetNo == '')    { echo "error: Asset tidak ditemukan"; exit; }
if ($tanggalKIR == '') { echo "error: Tanggal KIR wajib diisi"; exit; }

$asset = DB::table('asset_list')->where('Asset_No', $assetNo)->first();
if (!$asset) { echo "error: Asset tidak ditemukan"; exit; }

$ke   = (int) DB::table('asset_kir_log')->where('Asset_No', $assetNo)->max('Ke') + 1;
$next = date('Y-m-d', strtotime($tanggalKIR . ' +6 months'));

DB::table('asset_kir_log')->insert([
    'Asset_No'             => $assetNo,
    'Ke'                   => $ke,
    'Tanggal_KIR'          => $tanggalKIR,
    'Jadwal_KIR_Berikutnya'=> $next,
    'Berkas'               => $berkas,
    'Catatan'              => $catatan,
    'Created_By'           => Auth::user()->username ?? 'system',
]);

// Jadwal KIR terbaru di asset_list untuk tampilan grid; seluruh riwayat tetap di asset_kir_log
DB::table('asset_list')->where('Asset_No', $assetNo)->update([
    'Jadwal_KIR'          => $next,
    'Berkas_KIR'          => ($berkas != '' ? $berkas : $asset->Berkas_KIR),
    'Last_User'           => Auth::user()->username ?? 'system',
    'Last_Modified_Date'  => now(),
]);

echo "success";
