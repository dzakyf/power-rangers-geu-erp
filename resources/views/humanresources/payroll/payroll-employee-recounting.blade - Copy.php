<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Http\Fungsi;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;


function caritax($idgroup){
	
$memp = DB::table("employment")->where("Status","=","ACTIVE")->orderBy("Name","ASC")->get();
foreach($memp as $faemp){
	$pegawai[$faemp->Salary_Group] = $faemp->Marital_Status;
	$adanpwp[$faemp->Salary_Group] = $faemp->NPWP;
	$pphallowance[$faemp->Salary_Group] = $faemp->PPH_Allowance;
	$masamasuk[$faemp->Salary_Group] = date("Ym",strtotime($faemp->Start_Date));
}

$master[] ="";
$kx = DB::table("payrol_master_lists")->orderBy("Payroll_ID","ASC")->get();
foreach($kx as $kf){
	$master[$kf->Payroll_ID]['Type_Payroll'] = $kf->Type_Payroll;
	$master[$kf->Payroll_ID]['Calculation_Method'] = $kf->Calculation_Method;
	$master[$kf->Payroll_ID]['Calculation_Value'] = $kf->Calculation_Value;
	$master[$kf->Payroll_ID]['Limit_Amount'] = $kf->Limit_Amount;
}
$pkpx = DB::table("payroll_tax_reduction")->orderBy("Marital_ID","ASC")->get();
foreach($pkpx as $pkpfa){
	$pkp[$pkpfa->Marital_ID] = $pkpfa->Reduction_Amount;
	

}

		 
		$dx = DB::table("payrol_group_lists")->where("Main_ID",$idgroup)->first();
		$maxs = DB::table("payrol_group_lists")->where("Main_ID",$idgroup)->where("Group_ID",$dx->Group_ID)->get();
		$whtgx = 0;
		$kostpajak = 0;
		$tunjangs = 0;
		$basics = 0;
		$kosts = 0;

		foreach($maxs as $mx){
								

			$potong = $master[$mx->Payroll_ID];
			if ($potong['Calculation_Method'] == 'VALUES'){
			
				if ($mx->Type_Payroll == 'ALLOWANCE'){
					$tunjangs += $mx->Amounts;
					
					
				}elseif ($mx->Type_Payroll == 'BASIC'){
					$basics += $mx->Amounts;
				}else{
					$costs -= $mx->Amounts;
				}
				
				if ($mx->Default_PPH == 1){
					$whtgx += $mx->Amounts;
				}
				 
			}else{
				
				$gethitung = DB::table("payrol_group_lists")->where("Type_Payroll" ,"!=","DEDUCTION")->where("Group_ID",$idgroup)->where("Kena_Potongan",1)->get();
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
					$tunjangs += $nilaipotong;

				}else{
					if ($potong['WP'] == 'COMPANY'){
						$tunjangs += $nilaipotong;
 						
					}else{
						$kosts += $nilaipotong;
						if ($mx->Default_PPH == 1){
							$kostpajak += $nilaipotong;
						}	
					}

				}
				
									
			}			
		}
		$masaeksekusi = date("Ym");
		$itungsetahun = $masaeksekusi - $masamasuk[$idgroup];
		if ($itungsetahun > 12){
			$tahunkali = 12;
		}else{
			$thunx = date("Ym");
			$tahunkali  = $thunx - $masamasuk[$idgroup];
		}

		$brutox = $basics + $tunjangs;
		$biayajabatan = round(($brutox * 5)/100,0);
		if ($biayajabatan > 500000) $biayajabatan = 500000;
		$brutox *= $tahunkali;
		$biayajabatan *= $tahunkali;
		$kostpajak *= $tahunkali;

		$brutox -= $biayajabatan;
		$brutox -= $kostpajak;

		$pegawaix = $pegawai[$idgroup];
		
		$getpkp = $pkp[$pegawaix];
		if ($getpkp > 0 ){
			$nilaipkp = $getpkp;
		}else{
			$nilaipkp  = 0;
		}
		$grosspkp = $brutox - $nilaipkp;
		
		if ($grosspkp < 50000001 && $grosspkp > 4500000){
			$pengali = 5;
		}elseif($grosspkp < 250000001 && $grosspkp > 50000000){
			$pengali = 15;
			
		}elseif($grosspkp < 500000001 && $grosspkp > 250000000){
			$pengali = 25;
		}elseif($grosspkp > 500000000){
			$pengali = 30;
		}else{
			$pengali  = 0;
		}

				
		$pajaksetahun = ceil(($pengali/100) * $grosspkp);

		$pajaknya = $pajaksetahun / $tahunkali;
		$npwp = $adanpwp[$idgroup];
		if ($npwp == '') $pajaknya *= 1.2;
		
		return $pajaknya;
		

}	
	
	$dx = "";
	$qx = DB::table("payrol_master_group")->get();
	foreach($qx as $dx){
		$notepajak = "";
		$grup[$dx->Main_ID] = $dx->Use_For;
		$qxx = DB::table("payrol_group_lists")->where("Main_ID",$dx->Main_ID)->get();
		$whtgx = 0;
		$tunjangan[] = 0;
		$basic[] = 0;
		$costs[] = 0;
		$kostpajak = 0;
		
		foreach($qxx as $mx){
			$potong = $master[$mx->Payroll_ID];
			
			if ($potong['Calculation_Method'] == 'VALUES'){
			
				if ($mx->Type_Payroll == 'ALLOWANCE'){
					$tunjangan[$dx->Group_ID] += $mx->Amounts;
					
				}elseif ($mx->Type_Payroll == 'BASIC'){
					$basic[$dx->Group_ID] += $mx->Amounts;
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
					$tunjangan[$dx->Group_ID] += $nilaipotong;
					DB::update(DB::raw("UPDATE payrol_group_lists set Amounts = $nilaipotong WHERE ID=$mx->ID"));

				}else{
					if ($potong['WP'] == 'COMPANY'){
						$tunjangan[$dx->Group_ID] += $nilaipotong;
 						DB::update(DB::raw("UPDATE payrol_group_lists set Amounts = $nilaipotong WHERE ID=$mx->ID"));

					}else{
						$costs[$dx->Group_ID] += $nilaipotong;
						DB::update(DB::raw("UPDATE payrol_group_lists set Amounts = -$nilaipotong WHERE ID=$mx->ID"));
						
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
		$itungsetahun = $masaeksekusi - $masamasuk[$dx->Group_ID];
		if ($itungsetahun > 12){
			$tahunkali = 12;
		}else{
			$thunx = date("Ym");
			$tahunkali  = $thunx - $masamasuk[$dx->Group_ID];
		}
		$notepajak .= "# Masa Pengali : $tahunkali ";
		$tunjanganpph = $pphallowance[$dx->Group_ID];
		if ($tunjanganpph  == null || $tunjanganpph == 1){
			$grossup = caritax($dx->Group_ID);
			
			$brutox = $basic[$dx->Group_ID] + $tunjangan[$dx->Group_ID]+ $grossup;
		}else{		
			$brutox = $basic[$dx->Group_ID] + $tunjangan[$dx->Group_ID];
		}
		$notepajak .= "# Bruto : $brutox ";
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

		$pegawaix = $pegawai[$dx->Group_ID];
		
		$getpkp = $pkp[$pegawaix];
		if ($getpkp > 0 ){
			$nilaipkp = $getpkp;
		}else{
			$nilaipkp  = 0;
		}
		$notepajak .= "# PTKP: $nilaipkp";

		$grosspkp = $brutox - $nilaipkp;
		
		$notepajak .= "# Gross PKP: $grosspkp";
		if ($grosspkp < 50000001 && $grosspkp > 4500000){
			$pengali = 5;
		}elseif($grosspkp < 250000001 && $grosspkp > 50000000){
			$pengali = 15;
			
		}elseif($grosspkp < 500000001 && $grosspkp > 250000000){
			$pengali = 25;
		}elseif($grosspkp > 500000000){
			$pengali = 30;
		}else{
			$pengali  = 0;
		}
		$notepajak .= " * $pengali %";

		

		
		$pajaksetahun = ceil(($pengali/100) * $grosspkp);
		$notepajak .= "# grosspkp: $pengali % *  $grosspkp =  $pajaksetahun";

		$pajaknya = $pajaksetahun / $tahunkali;
		$npwp = $adanpwp[$dx->Group_ID];
		if ($npwp == '') $pajaknya *= 1.2;
		
				
			$adapajak = DB::table("payrol_group_lists")->where("Group_ID",$dx->Group_ID)->first();
			if ($adapajak){
				DB::update(DB::raw("UPDATE payrol_group_lists set Note='$notepajak',Amounts = -$pajaknya WHERE ID=$adapajak->ID"));
				
				$tunjanganpph = $pphallowance[$dx->Group_ID];
				if ($tunjanganpph  == null || $tunjanganpph == 1){
					
					$adatunjanganpajak = DB::table("payrol_group_lists")->where("Group_ID",$dx->Group_ID)->where("Payroll_ID","PYH00000")->first();
						if($adatunjanganpajak){
							DB::update(DB::raw("UPDATE payrol_group_lists set Note='$notepajak',Amounts = $pajaknya WHERE ID=$adatunjanganpajak->ID"));
								
						}else{
							DB::insert(DB::raw("
			
							INSERT INTO payrol_group_lists (Main_ID,Group_ID, Payroll_ID, Name, Kena_Potongan, Default_PPH, Type_Payroll,  Created_Date, Created_By,Amounts,Note)
							
							VALUES('$dx->Main_ID','$dx->Group_ID', 'PYH00000', 'TUNJANGAN PAJAK PPH', 0, 0, 'ALLOWANCE',  NOW(), '$Saya','$pajaknya','$notepajak')"));
						}
					
				}else{
					
							$costs[$dx->Group_ID] += $pajaknya;

				}
			}else{
				$tunjanganpph = $pphallowance[$dx->Group_ID];

				if ($tunjanganpph  == 1){
					
					DB::insert(DB::raw("
			
					INSERT INTO payrol_group_lists (Main_ID,Group_ID, Payroll_ID, Name, Kena_Potongan, Default_PPH, Type_Payroll,  Created_Date, Created_By,Note)
					
					VALUES('$dx->Main_ID','$dx->Group_ID', 'PYH00000', 'TUNJANGAN PAJAK PPH', 0, 0, 'ALLOWANCE',  NOW(), '$Saya','$pajaknya')"));
				}else{
					$costs[$dx->Group_ID] += $pajaknya;
					
				}
				DB::insert(DB::raw("
		
				INSERT INTO payrol_group_lists (Main_ID,Group_ID, Payroll_ID, Name, Kena_Potongan, Default_PPH, Type_Payroll,  Created_Date, Created_By,Amounts,Note)
				
				VALUES('$dx->Main_ID','$dx->Group_ID', 'PY000000', 'PAJAK PPH', 0, 0, 'DEDUCTION',  NOW(), '$Saya','$pajaknya','$notepajak')"));
					
			}
			$whtx[$dx->Group_ID] = $pajaknya;
		
			
	}
echo "<script>$.unblockUI();</script>";

?>