<?php
//error_reporting(E_ALL ^ E_NOTICE);
error_reporting(0);
ini_set('memory_limit', '-1');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
    header("Content-type: application/xhtml+xml");
} else {
    header("Content-type: text/xml");
}
echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");



$LSP = Auth::user()->Location;
$DOP = Auth::user()->DOP;
$priv = Auth::user()->Priv_ID;
$saya = Auth::user()->username;
$nomor = 1;

echo "<rows>";

$Query = DB::table("opportunity")->where("Location", $LSP)->orderBy("ID", "DESC")->get();

foreach ($Query as $Fetch) {
    switch ($Fetch->Status) {
        case 'CANCELED':
            $status = "PO DIBATALKAN";
            $warna = "style='background-color:red'";
            break;
        case 'CLOSED':
            $status = "PO SELESAI";
            $warna = "style='background-color:green'";
            break;
        case 'CREATED':
            $status = "PO SEDANG DI PROSES";
            $warna = "style='background-color:yellow'";
            break;
        case 'FINISH':
            $status = "PO DALAM PROSES PEMBAYARAN";
            $warna = "style='background-color:#bbb'";
            break;
        default:
    }

    $po = DB::table("opportunity_detail")->where("Task_ID", $Fetch->Task_ID)->first();
    if ($po != null) {

        if ($po->Subject == '') {
            $subject = $Fetch->Oppor_Subject;
        } else {
            $subject = $po->Subject;
        }
        $noPo = $po->PO_No;
    } else {
        $subject = $Fetch->Oppor_Subject;
        $noPo = "";
    }

    $taskid = DB::table("proforma_detail")->where("Task_ID", $po->Task_ID)->first();
    if ($taskid != null) {
        if ($taskid->TaskID == '') {
            $Task_ID = $po->Task_ID;
        } else {
            $Task_ID = $taskid->TaskID;
        }
    } else {
        $Task_ID = $po->Task_ID;
    }

    $ba = DB::table("proforma")->where("Proforma_No", $taskid->Proforma_No)->first();
    if ($ba != null) {
        if ($ba->BAno == '') {
            $Proforma_No = $taskid->Proforma_No;
        } else {
            $Proforma_No = $ba->BAno;
        }
        $noBA = $ba->BA_No;
    } else {
        $Proforma_No = $taskid->Proforma_No;
    }

    echo "<row id=\"$Fetch->ID\" $warna>";
    echo "<cell $warna>$nomor</cell>";
    echo "<cell $warna>" . htmlspecialchars($subject) . "</cell>";
    echo "<cell $warna>" . htmlspecialchars($noPo) . "</cell>";
    echo "<cell $warna>" . htmlspecialchars($noBA) . "</cell>";
    echo "<cell $warna>" . htmlspecialchars($Fetch->Created_Date) . "</cell>";
    echo "<cell $warna>" . htmlspecialchars($Fetch->Expected_Closing_Date) . "</cell>";
    echo "<cell $warna>" . htmlspecialchars($Fetch->Estimated_Rev) . "</cell>";
    echo "<cell $warna>" . htmlspecialchars($status) . "</cell>";

    echo "</row>";

    $nomor++;
}

echo "</rows>";
