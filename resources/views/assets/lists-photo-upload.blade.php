<?php
/**
 * POST /asset-lists-photo-upload
 * Tambah foto aset (bisa lebih dari satu). Append-only: tidak pernah menimpa
 * atau menghapus foto yang sudah ada. asset_list.Photo diisi otomatis dengan
 * foto pertama (cover) supaya grid + halaman QR tetap berjalan.
 */
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

$assetNo = trim($_POST['Asset_No'] ?? '');
$isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';

$balas = function ($ok, $msg, $extra = []) use ($isAjax) {
    if ($isAjax) {
        header('Content-type: application/json');
        echo json_encode(array_merge(['status' => $ok ? 'success' : 'error', 'message' => $msg], $extra));
        return;
    }
    echo ($ok ? '' : 'error: ') . $msg;
};

if ($assetNo === '') {
    $balas(false, 'Asset No tidak dikenali.');
    return;
}

$asset = DB::table('asset_list')->where('Asset_No', $assetNo)->first();
if (!$asset) {
    $balas(false, 'Asset tidak ditemukan.');
    return;
}

$files = $_FILES['photos'] ?? null;
if (!$files || !isset($files['name'])) {
    $balas(false, 'Tidak ada berkas yang dipilih.');
    return;
}

// Normalisasi $_FILES['photos'] (bisa single atau array) menjadi daftar berkas
$daftar = [];
if (is_array($files['name'])) {
    foreach ($files['name'] as $i => $nm) {
        $daftar[] = [
            'name'     => $nm,
            'tmp_name' => $files['tmp_name'][$i] ?? '',
            'error'    => $files['error'][$i] ?? UPLOAD_ERR_NO_FILE,
            'size'     => $files['size'][$i] ?? 0,
        ];
    }
} else {
    $daftar[] = $files;
}

$izin = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$maxByte = 5 * 1024 * 1024;
$berhasil = 0;
$ditolak = [];

foreach ($daftar as $fh) {
    if (($fh['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $ditolak[] = ($fh['name'] ?: 'berkas') . ' (gagal diunggah)';
        continue;
    }

    $ext = strtolower(pathinfo($fh['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $izin)) {
        $ditolak[] = $fh['name'] . ' (format ' . $ext . ' tidak diizinkan)';
        continue;
    }

    if (($fh['size'] ?? 0) > $maxByte) {
        $ditolak[] = $fh['name'] . ' (lebih dari 5 MB)';
        continue;
    }

    $year = date('Y');
    $month = date('m');
    $dirTujuan = public_path("filevendor/{$year}/{$month}");
    if (!is_dir($dirTujuan)) {
        mkdir($dirTujuan, 0777, true);
    }

    $namaBaru = 'asset_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $assetNo) . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (!@move_uploaded_file($fh['tmp_name'], $dirTujuan . '/' . $namaBaru)) {
        $ditolak[] = $fh['name'] . ' (gagal disimpan)';
        continue;
    }

    // Satu baris meta_files per foto -> riwayat foto tidak pernah hilang
    DB::table('meta_files')->insert([
        'Filename'    => $namaBaru,
        'Path'        => "{$year}/{$month}/{$namaBaru}",
        'Extension'   => $ext,
        'Description' => 'Asset Photo',
        'Topic'       => 'asset-photo',
        'Map_To'      => $assetNo,
        'Created_At'  => now(),
    ]);

    $berhasil++;
}

// Cover = foto pertama (paling lama). Bila asset belum punya cover, isi sekarang.
$pertama = DB::table('meta_files')
    ->where('Topic', 'asset-photo')
    ->where('Map_To', $assetNo)
    ->orderBy('ID', 'ASC')
    ->first();
if ($pertama) {
    DB::table('asset_list')->where('Asset_No', $assetNo)->update([
        'Photo'        => asset('filevendor/' . $pertama->Path),
        'Last_User'    => Auth::user()->username ?? '',
        'Last_Modified_Date' => now(),
    ]);
}

$pesan = "{$berhasil} foto berhasil diunggah.";
if ($ditolak) {
    $pesan .= ' Ditolak: ' . implode('; ', $ditolak);
}

$balas($berhasil > 0, $pesan, ['uploaded' => $berhasil, 'rejected' => $ditolak]);
?>