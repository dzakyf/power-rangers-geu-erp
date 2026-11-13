<?php
//error_reporting(E_ALL ^ E_NOTICE);
error_reporting(0);
ini_set('memory_limit', '-1');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
    header("Content-type: application/xhtml+xml"); 
} else {
    header("Content-type: text/xml");
}
echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

$LSP = Auth::user()->Location;
$Saya = Auth::user()->username;

echo "<rows>";

// Ambil data dari tabel rfq dan join dengan tabel supplier untuk mendapatkan Company_Name
$kueri = DB::select(DB::raw("
    SELECT r.*, s.Company_Name 
    FROM rfq r 
    LEFT JOIN supplier s ON r.Supplier_ID = s.Supplier_ID 
    ORDER BY r.ID DESC
"));

$nomor = 1;

foreach ($kueri as $Fetch) {
    $warna = "";
    // Status bisa diwarnai jika 'RESPONDED' atau 'SENT'
    if ($Fetch->Status == 'RESPONDED') {
        $warna = "style='background-color:#d4edda'"; // Hijau muda
    } elseif ($Fetch->Status == 'SENT') {
        $warna = "style='background-color:#fff3cd'"; // Kuning muda
    }

    // Nama Vendor (menggunakan Company_Name jika ada, atau fallback ke Supplier_ID)
    $namaVendor = $Fetch->Company_Name ?? $Fetch->Supplier_ID;

    echo "<row id=\"$Fetch->RFQ_No\" $warna>";
        echo "<cell $warna>$nomor</cell>";
        echo "<cell $warna>" . htmlspecialchars($Fetch->RFQ_No) . "</cell>";
        echo "<cell $warna>" . htmlspecialchars($Fetch->PR_No) . "</cell>";
        echo "<cell $warna>" . htmlspecialchars($namaVendor) . "</cell>";
        echo "<cell $warna>" . htmlspecialchars($Fetch->RFQ_Date) . "</cell>";
        echo "<cell $warna>" . htmlspecialchars($Fetch->Status) . "</cell>";
        echo "<cell $warna>" . htmlspecialchars($Fetch->Created_By) . "</cell>";
    echo "</row>";    

    $nomor++;
}

echo "</rows>";
?>