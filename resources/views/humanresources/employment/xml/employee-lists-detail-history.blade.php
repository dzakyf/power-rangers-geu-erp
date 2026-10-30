<?php
	//error_reporting(E_ALL ^ E_NOTICE);
error_reporting(0);
ini_set('memory_limit', '-1');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Builder;
	
	if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
  		header("Content-type: application/xhtml+xml"); } else {
  		header("Content-type: text/xml");
	}
	echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");


$LSP = Auth::user()->Location;
$DOP = Auth::user()->DOP;
$priv = Auth::user()->Priv_ID;
$saya = Auth::user()->username;
$nomor = 1;

echo "<rows>";

$Query = DB::table("employment_history")->where("NIK",$_GET['ID'])->orderBy("ID","DESC")->get();

foreach($Query as $Fetch){
	
	
	echo "<row id=\"$Fetch->ID\">";
		echo "<cell>$nomor</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Task_ID)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->NIK)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Date_Process)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Activity)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Reference)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Notes)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Start_Date)."</cell>";
		$endDate = ($Fetch->End_Date === '9999-12-31') ? '' : $Fetch->End_Date;
		echo "<cell>".htmlspecialchars($endDate)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Remark)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->User_Trx)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Date_Trx)."</cell>";
	echo "</row>";
$nomor++;
}

// -- Pengajuan Perubahan Status (status_changes) karyawan yang sama --
// Sudah tercatat otomatis ke employment_history saat form disimpan;
// baris data lama yang belum tercatat ditampilkan di sini agar tidak hilang.
$scRows = [];

if (Schema::hasTable("status_changes")) {
    $scRows = DB::table("status_changes")->where("NIK", $_GET["ID"])->orderBy("id", "DESC")->get();
}

if (count($scRows)) {
    $tercatat = DB::table("employment_history")
        ->where("NIK", $_GET["ID"])
        ->whereNotNull("Reference")
        ->pluck("Reference")
        ->toArray();

    foreach ($scRows as $Fetch) {
        $formNo = $Fetch->form_number ?: ("PS-DRAFT-" . $Fetch->id);

        if (in_array($formNo, $tercatat)) {
            continue;
        }

        $catatan = "Jenis: " . ($Fetch->jenis ?: "MUTATION");
        if (!empty($Fetch->current_position) || !empty($Fetch->proposed_position)) {
            $catatan .= "; Jabatan: " . ($Fetch->current_position ?: "-") . " -> " . ($Fetch->proposed_position ?: "-");
        }
        if (!empty($Fetch->current_department) || !empty($Fetch->proposed_department)) {
            $catatan .= "; Departemen: " . ($Fetch->current_department ?: "-") . " -> " . ($Fetch->proposed_department ?: "-");
        }
        if (!empty($Fetch->current_work_location) || !empty($Fetch->proposed_work_location)) {
            $catatan .= "; Lokasi: " . ($Fetch->current_work_location ?: "-") . " -> " . ($Fetch->proposed_work_location ?: "-");
        }
        if ((float) $Fetch->current_salary != (float) $Fetch->proposed_salary) {
            $catatan .= "; Salary: " . number_format((float) $Fetch->current_salary, 0, ',', ".") . " -> " . number_format((float) $Fetch->proposed_salary, 0, ',', ".");
        }

        echo "<row id=\"SC" . $Fetch->id . "\">";
        echo "<cell>" . $nomor . "</cell>";
        echo "<cell></cell>";
        echo "<cell>" . htmlspecialchars($Fetch->nik) . "</cell>";
        echo "<cell>" . htmlspecialchars(substr((string) $Fetch->created_at, 0, 10)) . "</cell>";
        echo "<cell>PERUBAHAN STATUS</cell>";
        echo "<cell>" . htmlspecialchars($formNo) . "</cell>";
        echo "<cell>" . htmlspecialchars($catatan) . "</cell>";
        echo "<cell>" . htmlspecialchars((string) ($Fetch->effective_date ?: "")) . "</cell>";
        echo "<cell></cell>";
        echo "<cell>" . htmlspecialchars(((string) $Fetch->jenis === "PROMOTION") ? "Promosi" : "Mutasi") . "</cell>";
        echo "<cell>" . htmlspecialchars((string) ($Fetch->proposed_by ?: "")) . "</cell>";
        echo "<cell>" . htmlspecialchars(substr((string) $Fetch->created_at, 0, 10)) . "</cell>";
        echo "</row>";
        $nomor++;
    }
}

echo "</rows>";
?>
