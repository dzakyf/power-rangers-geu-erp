<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\Settings;


$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

$data = DB::table("proforma")->where("Proforma_No", $_GET['ID'])->first();
$dfp = DB::table("proforma_detail")->where("Proforma_No", $_GET['ID'])->first();
$datahasil = DB::table("opportunity_detail")->where("PO_No", $dfp->PO_No)->first();
$oppor = DB::table("opportunity")->where("Task_ID", $datahasil->Task_ID)->first();

function penyebut($nilai)
{
  $nilai = abs($nilai);
  $huruf = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");
  $temp = "";
  if ($nilai < 12) {
    $temp = " " . $huruf[$nilai];
  } else if ($nilai < 20) {
    $temp = penyebut($nilai - 10) . " Belas";
  } else if ($nilai < 100) {
    $temp = penyebut($nilai / 10) . " Puluh" . penyebut($nilai % 10);
  } else if ($nilai < 200) {
    $temp = " seratus" . penyebut($nilai - 100);
  } else if ($nilai < 1000) {
    $temp = penyebut($nilai / 100) . " Ratus" . penyebut($nilai % 100);
  } else if ($nilai < 2000) {
    $temp = " seribu" . penyebut($nilai - 1000);
  } else if ($nilai < 1000000) {
    $temp = penyebut($nilai / 1000) . " Ribu" . penyebut($nilai % 1000);
  } else if ($nilai < 1000000000) {
    $temp = penyebut($nilai / 1000000) . " Juta" . penyebut($nilai % 1000000);
  } else if ($nilai < 1000000000000) {
    $temp = penyebut($nilai / 1000000000) . " Milyar" . penyebut(fmod($nilai, 1000000000));
  } else if ($nilai < 1000000000000000) {
    $temp = penyebut($nilai / 1000000000000) . " Trilyun" . penyebut(fmod($nilai, 1000000000000));
  }
  return $temp;
}

function terbilang($nilai)
{
  if ($nilai < 0) {
    $hasil = "minus " . trim(penyebut($nilai));
  } else {
    $hasil = trim(penyebut($nilai));
  }
  return $hasil;
}

//Fungsi ambil tanggal aja
function tgl_aja($tgl_a)
{
  $tanggal = substr($tgl_a, 8, 2);
  return $tanggal;
}

//Fungsi Ambil bulan aja
function bln_aja($bulan_a)
{
  $bulan = getBulan(substr($bulan_a, 5, 2));
  return $bulan;
}

//Fungsi Ambil tahun aja
function thn_aja($thn)
{
  $tahun = substr($thn, 0, 4);
  return $tahun;
}

//Fungsi konversi tanggal bulan dan tahun ke dalam bahasa indonesia
function tgl_indo($tgl)
{
  $tanggal = substr($tgl, 8, 2);
  $bulan = getBulan(substr($tgl, 5, 2));
  $tahun = substr($tgl, 0, 4);
  return $tanggal . ' ' . $bulan . ' ' . $tahun;
}
//Fungsi konversi nama bulan ke dalam bahasa indonesia
function getBulan($bln)
{
  switch ($bln) {
    case 1:
      return "Januari";
      break;
    case 2:
      return "Februari";
      break;
    case 3:
      return "Maret";
      break;
    case 4:
      return "April";
      break;
    case 5:
      return "Mei";
      break;
    case 6:
      return "Juni";
      break;
    case 7:
      return "Juli";
      break;
    case 8:
      return "Agustus";
      break;
    case 9:
      return "September";
      break;
    case 10:
      return "Oktober";
      break;
    case 11:
      return "November";
      break;
    case 12:
      return "Desember";
      break;
  }
}
function hari_ini($xd)
{
  $hari = date("D", strtotime($xd));

  switch ($hari) {
    case 'Sun':
      $hari_ini = "Minggu";
      break;

    case 'Mon':
      $hari_ini = "Senin";
      break;

    case 'Tue':
      $hari_ini = "Selasa";
      break;

    case 'Wed':
      $hari_ini = "Rabu";
      break;

    case 'Thu':
      $hari_ini = "Kamis";
      break;

    case 'Fri':
      $hari_ini = "Jumat";
      break;

    case 'Sat':
      $hari_ini = "Sabtu";
      break;

    default:
      $hari_ini = "Tidak di ketahui";
      break;
  }

  return $hari_ini;
}

date_default_timezone_set('UTC');
error_reporting(E_ALL);
define('CLI', (PHP_SAPI == 'cli') ? true : false);
define('EOL', CLI ? PHP_EOL : '<br />');
define('SCRIPT_FILENAME', basename($_SERVER['SCRIPT_FILENAME'], '.php'));
define('IS_INDEX', SCRIPT_FILENAME == 'index');

$tglba = hari_ini($data->BA_Date);
$tanggal = tgl_aja($data->BA_Date);
$bulan = bln_aja($data->BA_Date);
$tahun = thn_aja($data->BA_Date);
$tgl = tgl_indo($data->BA_Date);
$signed_by_position =htmlspecialchars( str_replace("gamp;", "&", $data->Signed_By_Position));

$pelanggan = DB::table("customerz")->where("Customer_No", $data->Account_No)->first();
$blnba = date("M", strtotime($data->BA_Date));
$thnba = date("Y", strtotime($data->BA_Date));
$tglx =  date("d-m-Y", strtotime($data->BA_Date));
$source = "formatba.docx";
$object = new \PhpOffice\PhpWord\TemplateProcessor($source);
$object->setValue('tgl', penyebut($tanggal));
$object->setValue('hari', $tglba);
$object->setValue('bln', $bulan);
$object->setValue('thn', penyebut($tahun));
$object->setValue('tgllengkap', $tglx);
$object->setValue('formatba', $data->BA_No);
$object->setValue('namapt', $pelanggan->company_ba_name);
$object->setValue('namaproject', strtoupper($data->Subject));
$object->setValue('jenis',  ucwords(strtolower($data->Subject)));
$object->setValue('periode', ucwords(strtolower($data->Periode)));
$object->setValue('nopo', $dfp->PO_No);
$object->setValue('total', number_format($data->Total, 0, ",", "."));
$object->setValue('nama2', ucwords(strtolower($data->Signed_By_KSPS)));
$object->setValue('jabatan2', ucwords(strtolower($data->Signed_By_Position_KSPS)));
$object->setValue('nama', $data->Signed_By);
$object->setValue('jabatan', $signed_by_position );
$object->setValue('ttduser', $data->Signed_By);
$object->setValue('jabatanuser', $signed_by_position);
$object->setValue('ttdksps', ucwords(strtolower($data->Signed_By_KSPS)));
$object->setValue('jabatanksps',  ucwords(strtolower($data->Signed_By_Position_KSPS)));

$namafile = "./tmpdata/BA-" . date("Ymdhis") . ".docx";
$namabaru = "./tmpdata/BA-" . date("Ymdhis") . ".pdf";
$object->saveAs($namafile);
$temp = \PhpOffice\PhpWord\IOFactory::load($namafile);
///$xmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($temp , 'PDF');
//$xmlWriter->save($namabaru, TRUE);

echo "<a href='$namafile'>Download File</a>";
