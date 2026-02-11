<?php
	//error_reporting(E_ALL ^ E_NOTICE);
error_reporting(0);
ini_set('memory_limit', '-1');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$DOP = Auth::user()->DOP;
$priv = Auth::user()->Priv_ID;
$Saya = Auth::user()->username;
	
	if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
  		header("Content-type: application/xhtml+xml"); } else {
  		header("Content-type: text/xml");
	}
	echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");


if ($_GET['Category'] == 'SALARY'){
	$tunjangan[] = "";
	$overtimex[] = "";
	$kjkx[] = "";
	$whtx[] = "";
	$basik[] = "";
	$costs[] = "";
	$nomor = 1;
	$mksx = DB::table("payrol_master_lists")->get();
	foreach($mksx as $jenis){
		$master[$jenis->Payroll_ID]['Formula'] =$jenis->Formula_Desc;
	}	
	$axx = DB::table("payrol_main_group")->where("Main_ID",$_GET['Main_ID'])->get();
	foreach($axx as $mainx){

		$dx = "";
		$qx = DB::table("payrol_master_group")->where("Main_ID",$mainx->Main_ID)->where("Category",$_GET['Category'])->get();
		foreach($qx as $dx){
			$notepajak = "";
			$grup[$dx->Main_ID] = $dx->Main_ID;
			
			$qxx = DB::table("payrol_group_lists")->where("Main_ID",$dx->Main_ID)->where("Group_ID",$dx->Group_ID)->get();
			foreach($qxx as $mx){
				if ($mx->Payroll_ID == 'PY000000'){
					$whtx[$dx->Main_ID][$dx->Group_ID] +=  $mx->Amounts;
				}elseif ($mx->Payroll_ID == 'PYH00001'){
					$tunjanganwhtx[$dx->Main_ID][$dx->Group_ID] +=  $mx->Amounts;
				}elseif ($mx->Type_Payroll == 'ALLOWANCE'){
					$formsx = $master[$mx->Payroll_ID]['Formula'];
					
					if ($formsx == 'OVERTIME'){
						$overtimex[$dx->Main_ID][$dx->Group_ID] += $mx->Amounts;
					}elseif($formsx == 'KJK'){
						$kjkx[$dx->Main_ID][$dx->Group_ID] += $mx->Amounts;

					}else{
						if ($mx->Prorate>0) $mx->Amounts = $mx->Prorate;
						$tunjanganx[$dx->Main_ID][$dx->Group_ID] +=  $mx->Amounts;
					}
				}elseif($mx->Type_Payroll == 'BASIC'){
					if ($mx->Prorate>0) $mx->Amounts = $mx->Prorate;
					$basik[$dx->Main_ID][$dx->Group_ID] +=  $mx->Amounts;
				}else{
					$costs[$dx->Main_ID][$dx->Group_ID] +=  $mx->Amounts;
				}
			}
		}
	}

	echo "<rows>";


	$Query = DB::select(DB::raw("SELECT * FROM employment where Location='$LSP' AND Salary_Group='$_GET[Main_ID]' and ((Status='ACTIVE' AND  Employment_Status!='RESIGNED') OR (Employment_Status='RESIGNED' AND Prorate_Resigned=0)) order by Name"));

	foreach($Query as $Fetch){
		$cekstatus = DB::table("payrol_master_group")->where("Main_ID",$Fetch->Salary_Group)->where("Category",$_GET['Category'])->where("Group_ID",$Fetch->NIK)->first();
		
		if ($cekstatus->Status_Salary == 'ACTIVE'){
					
				$kos = $costs[$Fetch->Salary_Group][$Fetch->NIK]+0;
				$tunjangan = $tunjanganx[$Fetch->Salary_Group][$Fetch->NIK]+0;
				$overtime = $overtimex[$Fetch->Salary_Group][$Fetch->NIK]+0;
				$kjk = $kjkx[$Fetch->Salary_Group][$Fetch->NIK]+0;
				$basic = $basik[$Fetch->Salary_Group][$Fetch->NIK]+0;
				$pajak = $whtx[$Fetch->Salary_Group][$Fetch->NIK]+0;
				$tunjanganpjk = $tunjanganwhtx[$Fetch->Salary_Group][$Fetch->NIK]+0;
				
				$thp = (($basic + $tunjangan + $kjk + $overtime)  + $kos) - ($pajak + $tunjanganpjk);
				
				echo "<row id=\"$Fetch->NIK\">";
					echo "<cell>$nomor</cell>";
					echo "<cell>".htmlspecialchars($Fetch->Name)."</cell>";
					echo "<cell>".htmlspecialchars($Fetch->NIK)."</cell>";
					echo "<cell>".htmlspecialchars($Fetch->Gender)."</cell>";
					echo "<cell>".htmlspecialchars($grup[$Fetch->Salary_Group])."</cell>";
					echo "<cell>".htmlspecialchars($Fetch->Salary_Group)."</cell>";
					echo "<cell>".htmlspecialchars($Fetch->Position)."</cell>";
					echo "<cell>$basic</cell>";
					echo "<cell>$tunjangan</cell>";
					echo "<cell>$overtime</cell>";
					echo "<cell>$kjk</cell>";
					echo "<cell>$tunjanganpjk</cell>";
					echo "<cell>$kos</cell>";
					echo "<cell>$pajak</cell>";
					echo "<cell>$thp</cell>";
					echo "<cell>".htmlspecialchars($Fetch->Category)."</cell>";

				echo "</row>";
			$nomor++;
		}
	}
	echo "</rows>";
	
	
}else{

	echo "<rows>";

$pkpx = DB::table("payroll_tax_reduction")->orderBy("Marital_ID","ASC")->get();
foreach($pkpx as $pkpfa){
	$pkp[$pkpfa->Marital_ID] = $pkpfa->Reduction_Amount;
	

}

function caritax($idgroup){

		return $pajaknya;
		

}	

$memp = DB::table("employment")->orderBy("Name","ASC")->get();
foreach($memp as $faemp){
	$gender[$faemp->NIK] = $faemp->Gender;
	$startdate[$faemp->NIK] = $faemp->Start_Date;
	$tanggalresign[$faemp->NIK] = $faemp->Resigned_Date;
	$statusresign[$faemp->NIK] = $faemp->Employment_Status;
	$namapegawai[$faemp->NIK] = $faemp->Name;
	$grup[$faemp->NIK] = $faemp->Salary_Group;
	$pegawai[$faemp->NIK] = $faemp->PTKP_Status;
	$sudahprorate[$faemp->NIK] = $faemp->Prorate_Salary;
	$prorateresign[$faemp->NIK] = $faemp->Prorate_Resigned;
	$adanpwp[$faemp->NIK] = $faemp->NPWP;
	$posisi[$faemp->NIK] = $faemp->Position;
	$pphallowance[$faemp->NIK] = $faemp->PPH_Allowance;
	$masamasuk[$faemp->NIK] = date("Ym",strtotime($faemp->Start_Date));
}

$master[] ="";
$kx = DB::table("payrol_master_lists")->orderBy("Payroll_ID","ASC")->get();
foreach($kx as $kf){
	$master[$kf->Payroll_ID]['Type_Payroll'] = $kf->Type_Payroll;
	$master[$kf->Payroll_ID]['Calculation_Method'] = $kf->Calculation_Method;
	$master[$kf->Payroll_ID]['Calculation_Value'] = $kf->Calculation_Value;
	$master[$kf->Payroll_ID]['Limit_Amount'] = $kf->Limit_Amount;	
	$master[$kf->Payroll_ID]['Formula_Method'] = $kf->Formula_Desc;

}
	
$axx = DB::table("payrol_master_template")->where("Payroll_ID",$_GET['Category'])->get();

foreach($axx as $mainx){
	
	$mx = "";
	$qx = DB::table("payrol_master_group")->where("Main_ID",$_GET['Main_ID'])->select("Group_ID")->groupBy(["Group_ID"])->get();
	foreach($qx as $dx){
		if ($mainx->Prorate == 1){
			
			$tglmasuk = new DateTime(date("Y-").date("m-d",strtotime($startdate[$dx->Main_ID][$dx->Group_ID])));
			$ends = new DateTime(date("Y-m-t"));
			$ends->modify('+1 day');
			$interval = $ends->diff($tglmasuk);

			$days = $interval->days;
			$period = new DatePeriod($tglmasuk, new DateInterval('P1D'), $ends);
			//$holidays = array('2012-09-07');

			foreach($period as $dt) {
				$curr = $dt->format('D');

				// substract if Saturday or Sunday
				if ($curr == 'Sat' || $curr == 'Sun') {
					$days--;
				}

				// (optional) for the updated question
				//elseif (in_array($dt->format('Y-m-d'), $holidays)) {
				//	$days--;
			//	}
			}
			$WARNA = "";
			if ($days < 21){
				
				if ($tglmasuk->format('d') > 20){
					$days = 9999;
					$WARNA = " style='background-color:red;color:white'";
				}else{
					$WARNA = " style='background-color:green;color:white'";
				}
				
			}else{
				
				$prorategak = $sudahprorate[$dx->Main_ID][$dx->Group_ID];
				if ($prorategak == 1){
					$days = 0;
					$WARN = "";
				}else{
					$WARNA = " style='background-color:green;color:white'";

				}
			}
		}
	
			
	

		$notepajak = "";
		$grup[$dx->Main_ID] = $dx->Use_For;
		
		$qxx = DB::table("payrol_template_master_lists")->where("Main_ID",$mainx->Payroll_ID)->get();

		$whtgx = 0;
		$tunjangan[] = 0;
		$basic[] = 0;
		$costs[] = 0;
		$kostpajak = 0;
		
		foreach($qxx as $kx){
		$mx = DB::table("payrol_group_lists")->where("Payroll_ID",$kx->Payroll_ID)->where("Group_ID",$dx->Group_ID)->where("Main_ID",$_GET['Main_ID'])->first();
			$potong = $master[$mx->Payroll_ID];
			
				if ($mx->Type_Payroll == 'ALLOWANCE'){
					$Jenis = $master[$mx->Payroll_ID]['Formula_Method'];	
				
					if ($days != 0 && $days!=9999 && $days < 21 ) $prorate = ceil(($mx->Amounts / 21 ) * $days) ; else $prorate = 0;
					
					if ($prorate > 0 && $mainx->Prorate == 1) { 
						$tunjangan[$dx->Group_ID] += floor($prorate);
						$notepajak .=" tunjangan $mx->Payroll_ID => $prorate \n";
						$notex = ",Note='Prorate : $days  val $prorate of $mx->Amounts'"; 
					} else{ 
						$tunjangan[$dx->Group_ID] += floor($mx->Amounts);
						$notepajak .=" tunjangan $mx->Payroll_ID => $mx->Amounts \n";
						$notex = "";
					}
						
						
					
					
				}elseif ($mx->Type_Payroll == 'BASIC'){
						if ($days != 0  && $days!=9999 && $days < 21 ) $prorate = ceil(($mx->Amounts / 21 ) * $days) ; else $prorate = 0;
						
						if ($prorate > 0 && $mainx->Prorate == 1) { 
							$basic[$dx->Group_ID] += floor($prorate);
							$notepajak .=" basic sal $mx->Payroll_ID => $prorate \n";
							$notex = ",Note='Prorate : $days  val $prorate of $mx->Amounts'"; 
						} else{ 
							$basic[$dx->Group_ID] += floor($mx->Amounts);
							$notex = "";
							$notepajak .=" basic sal $mx->Payroll_ID => $mx->Amounts \n";

						}
					
				}else{
					$costs[$dx->Group_ID] -= floor($mx->Amounts);
				}
				
				if ($mx->Default_PPH == 1){
					$whtgx += floor($mx->Amounts);
				}
				
					
		}
		
		$masaeksekusi = date("Ym");
		$itungsetahun = $masaeksekusi - $masamasuk[$dx->Main_ID][$dx->Group_ID];
		
		if ($itungsetahun > 12){
			$tahunkali = 12;
		}else{
			$thunx = date("Y")."12";
			$tahunkali  = ($thunx - $masamasuk[$dx->Main_ID][$dx->Group_ID])+1;
			
		}
		$notepajak .= "# Masa Pengali : $tahunkali ";
		$tunjanganpph = $pphallowance[$dx->Main_ID][$dx->Group_ID];	
		$brutox = $basic[$dx->Group_ID] + $tunjangan[$dx->Group_ID];
		
		$notepajak .= "# Bruto : " .$basic[$dx->Group_ID]."+". $tunjangan[$dx->Group_ID]."= $brutox ";
		$notepajak .= "# Costs : ". $kostpajak;

		$biayajabatan = floor(( ($basic[$dx->Group_ID] + $tunjangan[$dx->Group_ID]) * 5)/100);
		
		if ($biayajabatan > 500000) $biayajabatan = 500000;
		$notepajak .= "#Biaya Jabatan : $biayajabatan";
		$brutox *= $tahunkali;
		$biayajabatan *= floor($tahunkali);
		$kostpajak *= $tahunkali;
		$notepajak .= "# Total Revenue Setahun : $brutox - $biayajabatan - $kostpajak";

		$brutox -= $biayajabatan;
		$brutox -= $kostpajak;

		$pegawaix = $pegawai[$dx->Group_ID];
		
		$getpkp = floor($pkp[$pegawaix]);
		if ($getpkp > 0 ){
			$nilaipkp = $getpkp;
		}else{
			$nilaipkp  = 0;
		}
		$notepajak .= "# PTKP: $nilaipkp";

		$grosspkp = $brutox - $nilaipkp;
		
		$notepajak .= "# Gross PKP: $grosspkp #
		status $dx->Group_ID = $pegawaix";
		if ($grosspkp < 50000001 && $grosspkp > 1){
			if ($tunjanganpph  == null || $tunjanganpph == 1){
				$pajaksetahun = floor((5/95.25) * $grosspkp);
				
			}else{
				
				$pengali = 5;
				$pajaksetahun = floor(($pengali/100) * $grosspkp);

			}
			
		}elseif($grosspkp < 250000001 && $grosspkp > 50000000){
			if ($tunjanganpph  == null || $tunjanganpph == 1){
				$pajaksetahun = floor((($grosspkp - 47500000) * (15/85.75))+2500000);
				
			}else{
				
				$pengali = 15;
				$pajaksetahun = ceil(($pengali/100) * $grosspkp);
			}
			
		}elseif($grosspkp < 500000001 && $grosspkp > 250000000){
			if ($tunjanganpph  == null || $tunjanganpph == 1){
				$pajaksetahun = floor((($grosspkp - 217500000) * (25/75))+32500000);
				
			}else{
				
				$pengali = 25;
				$pajaksetahun = floor(($pengali/100) * $grosspkp);
			}
		}elseif($grosspkp > 500000000){
			if ($tunjanganpph  == null || $tunjanganpph == 1){
				$pajaksetahun = floor((($grosspkp - 405000000) * (30/70))+95000000);
			
			}else{
				
				$pengali = 30;
				$pajaksetahun = floor(($pengali/100) * $grosspkp);

			}
		}else{
			$pengali  = 0;
			$pajaksetahun = ceil(($pengali/100) * $grosspkp);
		}
		
		$notepajak .= " * $pengali %";

		

		

		$notepajak .= "# grosspkp: $pengali % *  $grosspkp =  $pajaksetahun";

		$pajaknya = $pajaksetahun / $tahunkali;
		$npwp = $adanpwp[$dx->Group_ID];
		if ($npwp == '') $pajaknya *= 1.2;
		
		$notepajak .= "  npwp => $npwp # final  = $pajaksetahun / $tahunkali = $pajaknya";
		
			$adapajak = DB::table("payrol_group_lists")->where("Main_ID",$dx->Main_ID)->where("Group_ID",$dx->Group_ID)->where("Payroll_ID",'PY000000')->first();
			if ($adapajak){
				
				$tunjanganpph = $pphallowance[$dx->Main_ID][$dx->Group_ID];
				if ($tunjanganpph  == null || $tunjanganpph == 1){
					
					$adatunjanganpajak = DB::table("payrol_group_lists")->where("Group_ID",$dx->Group_ID)->where("Payroll_ID","PYH00001")->first();
						
					
				}else{
					
					$costs[$dx->Group_ID] += $pajaknya;

				}
			}else{
				$tunjanganpph = $pphallowance[$dx->Main_ID][$dx->Group_ID];

				if ($tunjanganpph  == 1){
					
					
				}else{
					$costs[$dx->Group_ID] += $pajaknya;
					
				}
						
			}
			$whtx[$dx->Group_ID] = $pajaknya;
			$thp = ($basic[$dx->Group_ID] + $tunjangan[$dx->Group_ID]) - ($whtx[$dx->Group_ID] + $whtx[$dx->Group_ID]);

			
			echo "<row id=\"$dx->Group_ID\">";
					echo "<cell>$nomor</cell>";
					echo "<cell>".htmlspecialchars($namapegawai[$dx->Group_ID])."</cell>";
					echo "<cell>".htmlspecialchars($dx->Group_ID)."</cell>";
					echo "<cell>".htmlspecialchars($gender[$dx->Group_ID])."</cell>";
					echo "<cell>".htmlspecialchars($mainx->Category)."</cell>";
					echo "<cell>".htmlspecialchars($mainx->Main_ID)."</cell>";
					echo "<cell>".htmlspecialchars($posisi[$dx->Group_ID])."</cell>";
					echo "<cell>".$basic[$dx->Group_ID]."</cell>";
					echo "<cell>".$tunjangan[$dx->Group_ID]."</cell>";
					echo "<cell>0</cell>";
					echo "<cell>0</cell>";
					echo "<cell>".$whtx[$dx->Group_ID]."</cell>";
					echo "<cell>0</cell>";
					echo "<cell>".$whtx[$dx->Group_ID]."</cell>";
					echo "<cell>$thp</cell>";
					echo "<cell>".htmlspecialchars($_GET['Category'])."</cell>";
					echo "<cell></cell>";
					echo "<cell>".htmlspecialchars($notepajak)."</cell>";

				echo "</row>";	

		
		
	}
		echo "</rows>";

}
}


?>
