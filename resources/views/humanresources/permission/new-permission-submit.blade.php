<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	
$ars = "";
$ax = "";
$isi = "";

foreach($_POST as $key=>$value)
{
		
		if ($key !='ID' && $key !='Processdata' && $key != 'izin_tgl' && $key != 'izin_tgl2' && $key != 'Sisa_Cuti' && $key != 'Created_Date' && $key != 'Created_By' && $key != 'Location'&& $key != 'ID'){
			$ars .= "$key = '$value',";
			$ax .="$key,";
			$isi.="'$value',";
		}
}

$thnini = date("Y");
$prevyear = date("Y",strtotime("-1 year"));
$nextyear = date("Y",strtotime("+1 year"));
$tgllibur = "";
$hrilibur = DB::select(DB::raw("SELECT * FROM libur WHERE libur_tgl like '$thnini%' or libur_tgl like '$prevyear%' or libur_tgl LIKE '$nextyear%'"));
foreach($hrilibur as  $dx){
	$libur[$dx->libur_tgl] = 1;
}

$ax = substr($ax,0,-1);
$ars = substr($ars,0,-1);
$isi = substr($isi,0,-1);

$dates = array();
$current = strtotime($_POST['izin_tgl']);
$last = strtotime($_POST['izin_tgl2']);
$step = '+1 day';

while( $current <= $last ) {
		$tgl = date('Y-m-d', $current);

		if($libur[$tgl] != 1){
			
			$daynum = date("N", strtotime($tgl));

			if ($daynum != 7 && $daynum != 6){
			
				$kueri = "INSERT INTO izin($ax,izin_tgl,Created_By,Created_Date) values ($isi,'$tgl','$Saya',NOW())";
				DB::insert(DB::raw($kueri));
			}
		}
		$current = strtotime($step, $current);

}
echo "success";
?>