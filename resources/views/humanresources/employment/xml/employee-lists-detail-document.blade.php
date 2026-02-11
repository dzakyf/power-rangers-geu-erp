<?php
	//error_reporting(E_ALL ^ E_NOTICE);
error_reporting(0);
ini_set('memory_limit', '-1');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
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

$Query = DB::table("employment_document")->where("Task_ID",$_GET['ID'])->get();

foreach($Query as $Fetch){

		if($Fetch->Foto != ''){
			echo "<row id=\"$nomor\">";
				echo "<cell>$nomor</cell>";
				echo "<cell>PHOTO</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Foto)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Foto_Desc)."</cell>";
				echo "<cell></cell>";
				echo "<cell></cell>";
				echo "<cell>".htmlspecialchars($Fetch->Last_Updated)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Created_By)."</cell>";
				echo "<cell>photo/".htmlspecialchars($Fetch->Foto)."</cell>";
			echo "</row>";
		$nomor++;
		}
		if($Fetch->CV != ''){
			
			echo "<row id=\"$nomor\">";
				echo "<cell>$nomor</cell>";
				echo "<cell>CV</cell>";
				echo "<cell>".htmlspecialchars($Fetch->CV)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->CV_Desc)."</cell>";
				echo "<cell></cell>";
				echo "<cell></cell>";
				echo "<cell>".htmlspecialchars($Fetch->Last_Updated)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Created_By)."</cell>";
				echo "<cell>cv/".htmlspecialchars($Fetch->CV)."</cell>";
			echo "</row>";
		$nomor++;
		}
		if($Fetch->Ijazah != ''){
		
			echo "<row id=\"$nomor\">";
				echo "<cell>$nomor</cell>";
				echo "<cell>IJAZAH</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Ijazah)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Ijazah_Desc)."</cell>";
				echo "<cell></cell>";
				echo "<cell></cell>";
				echo "<cell>".htmlspecialchars($Fetch->Last_Updated)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Created_By)."</cell>";
				echo "<cell>ijazah/".htmlspecialchars($Fetch->Ijazah)."</cell>";
			echo "</row>";
		$nomor++;
		}
		
		if($Fetch->Sertifikat != ''){
			echo "<row id=\"$nomor\">";
				echo "<cell>$nomor</cell>";
				echo "<cell>SERTIFIKAT</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Sertifikat)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Sertifikat_Desc)."</cell>";
				echo "<cell></cell>";
				echo "<cell></cell>";
				echo "<cell>".htmlspecialchars($Fetch->Last_Updated)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Created_By)."</cell>";
				echo "<cell>sertifikat/".htmlspecialchars($Fetch->Sertifikat)."</cell>";
			echo "</row>";
		$nomor++;
		}
		
		if($Fetch->KTP != ''){
		
			echo "<row id=\"$nomor\">";
				echo "<cell>$nomor</cell>";
				echo "<cell>KTP</cell>";
				echo "<cell>".htmlspecialchars($Fetch->KTP)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->KTP_Desc)."</cell>";
				echo "<cell></cell>";
				echo "<cell></cell>";
				echo "<cell>".htmlspecialchars($Fetch->Last_Updated)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Created_By)."</cell>";
				echo "<cell>ktp/".htmlspecialchars($Fetch->KTP)."</cell>";
			echo "</row>";
		$nomor++;
		}
		if($Fetch->NPWP != ''){
			echo "<row id=\"$nomor\">";
				echo "<cell>$nomor</cell>";
				echo "<cell>NPWP</cell>";
				echo "<cell>".htmlspecialchars($Fetch->NPWP)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->NPWP_Desc)."</cell>";
				echo "<cell></cell>";
				echo "<cell></cell>";
				echo "<cell>".htmlspecialchars($Fetch->Last_Updated)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Created_By)."</cell>";
				echo "<cell>npwp/".htmlspecialchars($Fetch->NPWP)."</cell>";
			echo "</row>";
		$nomor++;
		}
		if($Fetch->KK != ''){
		
			echo "<row id=\"$nomor\">";
				echo "<cell>$nomor</cell>";
				echo "<cell>KK</cell>";
				echo "<cell>".htmlspecialchars($Fetch->KK)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->KK_Desc)."</cell>";
				echo "<cell></cell>";
				echo "<cell></cell>";
				echo "<cell>".htmlspecialchars($Fetch->Last_Updated)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Created_By)."</cell>";
				echo "<cell>kk/".htmlspecialchars($Fetch->KK)."</cell>";
			echo "</row>";
		$nomor++;
		}
		if($Fetch->Akta != ''){
		
			echo "<row id=\"$nomor\">";
				echo "<cell>$nomor</cell>";
				echo "<cell>Akta</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Akta)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Akta_Desc)."</cell>";
				echo "<cell></cell>";
				echo "<cell></cell>";
				echo "<cell>".htmlspecialchars($Fetch->Last_Updated)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Created_By)."</cell>";
				echo "<cell>akta/".htmlspecialchars($Fetch->Akta)."</cell>";
			echo "</row>";
		$nomor++;
		}
		if($Fetch->SIM != ''){
		
			echo "<row id=\"$nomor\">";
				echo "<cell>$nomor</cell>";
				echo "<cell>SIM</cell>";
				echo "<cell>".htmlspecialchars($Fetch->SIM)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->SIM_Desc)."</cell>";
				echo "<cell></cell>";
				echo "<cell></cell>";
				echo "<cell>".htmlspecialchars($Fetch->Last_Updated)."</cell>";
				echo "<cell>".htmlspecialchars($Fetch->Created_By)."</cell>";
				echo "<cell>sim/".htmlspecialchars($Fetch->SIM)."</cell>";
			echo "</row>";
		$nomor++;
		}
				
		
}


echo "</rows>";
?>
