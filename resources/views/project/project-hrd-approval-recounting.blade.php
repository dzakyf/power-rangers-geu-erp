<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Http\Fungsi;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

$pkpx = DB::table("payroll_tax_reduction")->orderBy("Marital_ID","ASC")->get();
foreach($pkpx as $pkpfa){
	$pkp[$pkpfa->Marital_ID] = $pkpfa->Reduction_Amount;
	

}

function caritax($idgroup){

		return $pajaknya;
		

}	

$faemp = DB::table("recruitment")->where("Task_ID",$_GET['ID'])->first();
$requisition = DB::table("requisition")->where("Task_ID",$faemp->Requisition_ID)->first();

	$startdate[$faemp->Salary_Group][$faemp->Task_ID] = $faemp->Start_Date;
	$pegawai[$faemp->Salary_Group][$faemp->Task_ID] = $faemp->Marital_Status;
	$sudahprorate[$faemp->Salary_Group][$faemp->Task_ID] = $faemp->Prorate_Salary;
	$adanpwp[$faemp->Salary_Group][$faemp->Task_ID] = $faemp->NPWP;
	$pphallowance[$faemp->Salary_Group][$faemp->Task_ID] = $faemp->PPH_Allowance;
	$masamasuk[$faemp->Salary_Group][$faemp->Task_ID] = date("Ym",strtotime($faemp->Start_Date));


$master[] ="";
$kx = DB::table("payrol_master_lists")->orderBy("Payroll_ID","ASC")->get();
foreach($kx as $kf){
	$master[$kf->Payroll_ID]['Type_Payroll'] = $kf->Type_Payroll;
	$master[$kf->Payroll_ID]['Calculation_Method'] = $kf->Calculation_Method;
	$master[$kf->Payroll_ID]['Calculation_Value'] = $kf->Calculation_Value;
	$master[$kf->Payroll_ID]['Limit_Amount'] = $kf->Limit_Amount;	
	$master[$kf->Payroll_ID]['Formula_Method'] = $kf->Formula_Desc;

}
	
$axx = DB::table("payrol_main_group")->where("Main_ID",$faemp->Requisition_ID)->get();
foreach($axx as $mainx){
	
	$mx = "";
	$qx = DB::table("payrol_master_group")->where("Main_ID",$mainx->Main_ID)->where("Group_ID",$->get();
	foreach($qx as $dx){
				
			$tglmasuk = new DateTime(date("Y-m-d",strtotime($startdate[$dx->Main_ID][$dx->Group_ID])));
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
		
			
	

		$notepajak = "";
		$grup[$dx->Main_ID] = $dx->Use_For;
		
		$qxx = DB::table("payrol_group_lists")->where("Main_ID",$dx->Main_ID)->where("Group_ID",$dx->Group_ID)->get();
		$whtgx = 0;
		$tunjangan[] = 0;
		$basic[] = 0;
		$costs[] = 0;
		$kostpajak = 0;
		
		foreach($qxx as $mx){

			$potong = $master[$mx->Payroll_ID];
			
			if ($potong['Calculation_Method'] == 'VALUES'){
			if ($mx->Type_Payroll == 'ALLOWANCE'){
					$Jenis = $master[$mx->Payroll_ID]['Formula_Method'];	
					if ($Jenis == 'OVERTIME'){
						$ovx = DB::select(DB::raw("SELECT * FROM overtime_lists where NIK='$dx->Group_ID' and (Salary_ID IS NULL OR Salary_ID='TEMP99999')"));	
							
						$nilainya = 0;
						$jumov =0;
						$listovertime = "";
						foreach($ovx as $ovf){
							$nilainya += $ovf->Amounts;
							$jumov++;
							$listovertime .= $ovf->Task_ID.",";
							DB::update("UPDATE overtime_lists set Salary_ID='TEMP99999' WHERE ID=$ovf->ID");
						}
						$tunjangan[$dx->Group_ID] += $nilainya;

						DB::update(DB::raw("UPDATE payrol_group_lists set Amounts = $nilainya,Note='Number of Overtime: $jumov;Task LIST = $listovertime' WHERE ID=$mx->ID"));
						
					}elseif ($Jenis == 'KJK'){
						$ovx = DB::select(DB::raw("SELECT * FROM kjk_lists where NIK='$dx->Group_ID' and (Salary_ID IS NULL OR Salary_ID='TEMP99999')"));	
						
						$nilainya = 0;
						$jumov =0;
						$listkjk = "";
						foreach($ovx as $ovf){
							$nilainya += $ovf->Total;
							$jumov++;
							$listkjk .= $ovf->Task_ID.",";
							DB::update("UPDATE kjk_lists set Salary_ID='TEMP99999' WHERE ID=$ovf->ID");
						}
						$tunjangan[$dx->Group_ID] += $nilainya;

						DB::update(DB::raw("UPDATE payrol_group_lists set Amounts = $nilainya,Note='Number of Overtime: $jumov;Task LIST = $listkjk' WHERE ID=$mx->ID"));
						
					}else{
						if ($days != 0 && $days!=9999 ) $prorate = ceil(($mx->Amounts / 21 ) * $days) ; else $prorate = 0;
						
						if ($prorate > 0) { 
							$tunjangan[$dx->Group_ID] += $prorate;
							$notex = ",Note='Prorate : $days  val $prorate of $mx->Amounts'"; 
						} else{ 
							$tunjangan[$dx->Group_ID] += $mx->Amounts;
							$notex = "";
						}
						
						
						DB::update(DB::raw("UPDATE payrol_group_lists set Amounts = $mx->Amounts,Prorate='$prorate' $notex WHERE ID=$mx->ID"));
					}
					
				}elseif ($mx->Type_Payroll == 'BASIC'){
						if ($days != 0  && $days!=9999) $prorate = ceil(($mx->Amounts / 21 ) * $days) ; else $prorate = 0;
						
						if ($prorate > 0) { 
							$basic[$dx->Group_ID] += $prorate;
							$notex = ",Note='Prorate : $days  val $prorate of $mx->Amounts'"; 
						} else{ 
							$basic[$dx->Group_ID] += $mx->Amounts;
							$notex = "";
						}
					
					DB::update(DB::raw("UPDATE payrol_group_lists set Amounts = $mx->Amounts,Prorate='$prorate' $notex WHERE ID=$mx->ID"));
				}else{
					$costs[$dx->Group_ID] -= $mx->Amounts;
				}
				
				if ($mx->Default_PPH == 1){
					$whtgx += $mx->Amounts;
				}
				
			}else{
				$gethitung = DB::table("payrol_group_lists")->where("Type_Payroll" ,"!=","DEDUCTION")->where("Main_ID",$dx->Main_ID)->where("Group_ID",$dx->Group_ID)->where("Kena_Potongan",1)->get();
				$sumvalue = 0;
				
				foreach($gethitung as $mk){
					$sumvalue += $mk->Amounts;
				}
				
				$potong = $master[$mx->Payroll_ID];
				
				if ($potong['Calculation_Method'] == 'VALUES'){
					
					$nilaipotong = $sumvalue - $potong['Calculation_Value'];
				
				}else{
					$nilaipotong = ($potong['Calculation_Value']/100) * $sumvalue;
				}
				if ($mx->Type_Payroll == 'ALLOWANCE'){
												
						$limit = $master[$mx->Payroll_ID]['Limit_Amount'];
						if ($limit > 0){
								$MAKS = $master[$mx->Payroll_ID]['Formula_Method'];						
							if ($sumvalue > $limit) $nilaipotong = $MAKS;
						}
						$tunjangan[$dx->Group_ID] += $nilaipotong;
						DB::update(DB::raw("UPDATE payrol_group_lists set Amounts = $nilaipotong,Note='$limit#$MAKS' WHERE ID=$mx->ID"));
					

				}else{
					if ($potong['WP'] == 'COMPANY'){
						$limit = $master[$mx->Payroll_ID]['Limit_Amount'];
						if ($limit > 0){
							$MAKS = $master[$mx->Payroll_ID]['Formula_Method'];
							if ($sumvalue > $limit) $nilaipotong = $MAKS;
						}
				
						$tunjangan[$dx->Group_ID] += $nilaipotong;
 						DB::update(DB::raw("UPDATE payrol_group_lists set Amounts = $nilaipotong,Note='$limit#$MAKS' WHERE ID=$mx->ID"));

					}else{
						$limit = $master[$mx->Payroll_ID]['Limit_Amount'];
						if ($limit > 0){
							$MAKS = $master[$mx->Payroll_ID]['Formula_Method'];
							if ($sumvalue > $limit) $nilaipotong = $MAKS;
						}
				
						$costs[$dx->Group_ID] += $nilaipotong;
						DB::update(DB::raw("UPDATE payrol_group_lists set Amounts = -$nilaipotong,Note='$limit#$MAKS' WHERE ID=$mx->ID"));
						if ($mx->Default_PPH == 1){
							$kostpajak += $nilaipotong;
						}	
					}

				}
				

				if ($mx->Default_PPH == 1){
					$whtgx += $nilaipotong;
				}	
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

		$biayajabatan = round(( ($basic[$dx->Group_ID] + $tunjangan[$dx->Group_ID]) * 5)/100,0);
		
		if ($biayajabatan > 500000) $biayajabatan = 500000;
		$notepajak .= "#Biaya Jabatan : $biayajabatan";
		$brutox *= $tahunkali;
		$biayajabatan *= $tahunkali;
		$kostpajak *= $tahunkali;
		$notepajak .= "# Total Revenue Setahun : $brutox - $biayajabatan - $kostpajak";

		$brutox -= $biayajabatan;
		$brutox -= $kostpajak;

		$pegawaix = $pegawai[$dx->Main_ID][$dx->Group_ID];
		
		$getpkp = $pkp[$pegawaix];
		if ($getpkp > 0 ){
			$nilaipkp = $getpkp;
		}else{
			$nilaipkp  = 0;
		}
		$notepajak .= "# PTKP: $nilaipkp";

		$grosspkp = $brutox - $nilaipkp;
		
		$notepajak .= "# Gross PKP: $grosspkp #pegawai status $dx->Group_ID = $pegawaix";
		if ($grosspkp < 50000001 && $grosspkp > 1){
			if ($tunjanganpph  == null || $tunjanganpph == 1){
				$pajaksetahun = ceil((5/95) * $grosspkp);
				
			}else{
				
				$pengali = 5;
				$pajaksetahun = ceil(($pengali/100) * $grosspkp);

			}
			
		}elseif($grosspkp < 250000001 && $grosspkp > 50000000){
			if ($tunjanganpph  == null || $tunjanganpph == 1){
				$pajaksetahun = ceil((($grosspkp - 47500000) * (15/85))+2500000);
				
			}else{
				
				$pengali = 15;
				$pajaksetahun = ceil(($pengali/100) * $grosspkp);
			}
			
		}elseif($grosspkp < 500000001 && $grosspkp > 250000000){
			if ($tunjanganpph  == null || $tunjanganpph == 1){
				$pajaksetahun = ceil((($grosspkp - 217500000) * (25/75))+32500000);
				
			}else{
				
				$pengali = 25;
				$pajaksetahun = ceil(($pengali/100) * $grosspkp);
			}
		}elseif($grosspkp > 500000000){
			if ($tunjanganpph  == null || $tunjanganpph == 1){
				$pajaksetahun = ceil((($grosspkp - 405000000) * (30/70))+95000000);
			
			}else{
				
				$pengali = 30;
				$pajaksetahun = ceil(($pengali/100) * $grosspkp);

			}
		}else{
			$pengali  = 0;
			$pajaksetahun = ceil(($pengali/100) * $grosspkp);
		}
		
		$notepajak .= " * $pengali %";

		

		

		$notepajak .= "# grosspkp: $pengali % *  $grosspkp =  $pajaksetahun";

		$pajaknya = $pajaksetahun / $tahunkali;
		$npwp = $adanpwp[$dx->Main_ID][$dx->Group_ID];
		if ($npwp == '') $pajaknya *= 1.2;
		
		$notepajak .= "# final  = $pajaksetahun / $tahunkali";
		
			$adapajak = DB::table("payrol_group_lists")->where("Main_ID",$dx->Main_ID)->where("Group_ID",$dx->Group_ID)->where("Payroll_ID",'PY000000')->first();
			if ($adapajak){
				DB::update(DB::raw("UPDATE payrol_group_lists set Note='$notepajak',Amounts = -$pajaknya WHERE ID=$adapajak->ID"));
				
				$tunjanganpph = $pphallowance[$dx->Main_ID][$dx->Group_ID];
				if ($tunjanganpph  == null || $tunjanganpph == 1){
					
					$adatunjanganpajak = DB::table("payrol_group_lists")->where("Group_ID",$dx->Group_ID)->where("Payroll_ID","PYH00001")->first();
						if($adatunjanganpajak){
							DB::update(DB::raw("UPDATE payrol_group_lists set Note='$notepajak',Amounts = $pajaknya WHERE ID=$adatunjanganpajak->ID"));

						}else{
							DB::insert(DB::raw("
			
							INSERT INTO payrol_group_lists (Main_ID,Group_ID, Payroll_ID, Name, Kena_Potongan, Default_PPH, Type_Payroll,  Created_Date, Created_By,Amounts,Note)
							
							VALUES('$dx->Main_ID','$dx->Group_ID', 'PYH00001', 'TUNJANGAN PAJAK PPH', 0, 0, 'ALLOWANCE',  NOW(), '$Saya','$pajaknya','$notepajak')"));

						}
					
				}else{
					
					$costs[$dx->Group_ID] += $pajaknya;

				}
			}else{
				$tunjanganpph = $pphallowance[$dx->Main_ID][$dx->Group_ID];

				if ($tunjanganpph  == 1){
					
					DB::insert(DB::raw("
			
					INSERT INTO payrol_group_lists (Main_ID,Group_ID, Payroll_ID, Name, Kena_Potongan, Default_PPH, Type_Payroll,  Created_Date, Created_By,Note)
					
					VALUES('$dx->Main_ID','$dx->Group_ID', 'PYH00001', 'TUNJANGAN PAJAK PPH', 0, 0, 'ALLOWANCE',  NOW(), '$Saya','$pajaknya')"));
				}else{
					$costs[$dx->Group_ID] += $pajaknya;
					
				}
				DB::insert(DB::raw("
		
				INSERT INTO payrol_group_lists (Main_ID,Group_ID, Payroll_ID, Name, Kena_Potongan, Default_PPH, Type_Payroll,  Created_Date, Created_By,Amounts,Note)
				
				VALUES('$dx->Main_ID','$dx->Group_ID', 'PY000000', 'PAJAK PPH', 0, 0, 'DEDUCTION',  NOW(), '$Saya','$pajaknya','$notepajak')"));
					
			}
			$whtx[$dx->Group_ID] = $pajaknya;
		
	if ($days!= 9999) {
		
		DB::update(DB::raw("UPDATE payrol_master_group set Status_Salary = 'ACTIVE' WHERE ID=$dx->ID"));
	}else{
		DB::update(DB::raw("UPDATE payrol_master_group set Status_Salary = 'INACTIVE' WHERE ID=$dx->ID"));
		
	}
	
	}
}
echo "<script>refreshdata();$.unblockUI();</script>";

?>