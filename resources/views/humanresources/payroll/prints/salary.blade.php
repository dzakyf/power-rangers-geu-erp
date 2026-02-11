@extends('layout.plugins')
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$Position =  Auth::user()->Position;
	
	
	?>
<html> 
 <link type="text/css"  rel="stylesheet"  href="normalize.min.css"/>

  <!-- Load paper.css for happy printing -->
  <link  rel="stylesheet"   href="paper.css"/>

  <!-- Set page size here: A5, A4 or A3 -->
  <!-- Set also "landscape" if you need -->
  <style type="text/css">@page { size: A4 }</style>
<?php
error_reporting(0);
class moneyFormat {

	public function rupiah ($angka) {
		$rupiah = number_format($angka ,0, '' , '.' );
		return $rupiah;
	}

	
}

function Dot_Conversion($Nvalue) {
    $resval = number_format($Nvalue, 0, '','.');
	return ($resval);
}

function terbilang($n) {
  if ($n < 0) return 'minus ' . terbilang(-$n);
  else if ($n < 10) {
    switch ($n) {
      case 0: return 'zero';
      case 1: return 'one';
      case 2: return 'two';
      case 3: return 'three';
      case 4: return 'four';
      case 5: return 'five';
      case 6: return 'six';
      case 7: return 'seven';
      case 8: return 'eight';
      case 9: return 'nine';
    }
  }
  else if ($n < 100) {
    $kepala = floor($n/10);
    $sisa = $n % 10;
    if ($kepala == 1) {
      if ($sisa == 0) return 'ten';
      else if ($sisa == 1) return 'eleven';
      else if ($sisa == 2) return 'twelve';
      else if ($sisa == 3) return 'thirteen';
      else if ($sisa == 5) return 'fifteen';
      else if ($sisa == 8) return 'eighteen';
      else return terbilang($sisa) . 'teen';
    }
    else if ($kepala == 2) $hasil = 'twenty';
    else if ($kepala == 3) $hasil = 'thirty';
    else if ($kepala == 5) $hasil = 'fifty';
    else if ($kepala == 8) $hasil = 'eighty';
    else $hasil = terbilang($kepala) . 'ty';
  }
  else if ($n < 1000) {
    $kepala = floor($n/100);
    $sisa = $n % 100;
    $hasil = terbilang($kepala) . ' hundred';
  }
  else if ($n < 1000000) {
    $kepala = floor($n/1000);
    $sisa = $n % 1000;
    $hasil = terbilang($kepala) . ' thousand';
  }
  else if ($n < 1000000000) {
    $kepala = floor($n/1000000);
    $sisa = $n % 1000000;
    $hasil = terbilang($kepala) . ' million';
  }
  else return false;

  if ($sisa > 0) $hasil .= ' ' . terbilang($sisa);
  return $hasil;
}

function limit_text($text, $limit) {
      if (str_word_count($text, 0) > $limit) {
          $words = str_word_count($text, 2);
          $pos = array_keys($words);
          $text = substr($text, 0, $pos[$limit]) . '...';
      }
      return $text;
    }
	
$salary_main = DB::table("salary_main")->where("Task_ID",$_GET['Task_ID'])->first();
$salary = DB::table("salary")->where("Task_ID",$_GET['Task_ID'])->where("Group_ID",$_GET['NIK'])->first();
$emp = DB::table("employment")->where("NIK",$_GET['NIK'])->first();
$tgl = date("d M Y",strtotime($salary_main->Last_Update));
$nomor = 1;

$moneyFormat = new moneyFormat();
		
$basik = DB::table("salary_lists")->where("Task_ID",$_GET['Task_ID'])->where("Type_Payroll","=",'BASIC')->where("Group_ID",$_GET['NIK'])->first();
$isi['ALLOWANCE']['GAPOK']['Name'] = $basik->Name;
$isi['ALLOWANCE']['GAPOK']['Value'] = $basik->Amounts;
	
$salarylist = DB::table("salary_lists")->where("Task_ID",$_GET['Task_ID'])->where("Type_Payroll","!=",'BASIC')->where("Group_ID",$_GET['NIK'])->orderBy("Payroll_ID","asc")->get();
foreach($salarylist as $dtl){
	
	$isi[$dtl->Type_Payroll][$dtl->Payroll_ID]['Name'] = $dtl->Name;
	$isi[$dtl->Type_Payroll][$dtl->Payroll_ID]['Value'] = $dtl->Amounts;
	
}

?>
<style type="text/css">
.sheet {
  overflow: visible;
  height: auto !important;
}

html,table{
	font-family:arial;
	font-size:11px;
	
	
}
.header{
	
	border-bottom:1px solid #000;
	height:80px;
	width:auto;
	
}
.header-kanan{
	float:right;
	width:280px;
}
.header-kanan2{
	float:right;
	width:400px;
}
.header-kanan3{
	float:right;
	width:150px;
}
.subheader{
	padding-top:10px;
	height:130px;
	width:auto;
}
.barisjudulpurchase_order{
	padding-top:5px;
	height:20px;
	width:auto;
	vertical-align:middle;
	text-align:center;
	font-weight:bold;
	font-size:15px;
	float:none;
	border: 1px solid #000;
}
</style>
<body class="A4">
  <div class="sheet padding-10mm">

	<div class="header">
	<div style="float:left;width:130px;vertical-align:top;padding-top:-100px;margin:0;">
				<img src='kspslogo.png' height="70"/>
			</div>
			<div style="float:right;width:80%;text-align:center;font-weight:bold;font-size:16px;font-family:arial">
				PT. GREEN ENERGY UTAMA<br>
				SALARY SLIP<br>
				
				<img src="Barcode.php?id=<?php echo $salary->Task_ID; ?>" style="vertical-align:middle" height="40" />
			</div>
		
	</div>
		
    <div>
	
		
			
		<div style="padding-left:20px;padding-top:20px;padding-right:20px;width:95%;">
					<div style="padding-left:20px;padding-top:20px;padding-right:20px;width:95%;">
						<table width="100%" cellpadding="0" cellspacing="0">
							<tr>
								<td width="200" style="padding:10px;border-right:none;height:20px;text-align:left;">NIK</td>
								<td  width="6%" style="padding:10px;border-right:none;border-left:none;height:20px;text-align:left;">:</td>
								<td  width="80%" style="padding:10px;border-left:none;height:20px;text-align:left;"><?php echo $salary->Group_ID?></td>
								</td>
							</tr>	
							<tr>
								<td width="200" style="padding:10px;border-right:none;height:20px;text-align:left;">Nama</td>
								<td  width="6%" style="padding:10px;border-right:none;border-left:none;height:20px;text-align:left;">:</td>
								<td  width="80%" style="padding:10px;border-left:none;height:20px;text-align:left;"><?php echo $salary->Group_Name?></td>
								</td>
							</tr>	
							<tr>
								<td width="200" style="padding:10px;border-right:none;height:20px;text-align:left;">Division</td>
								<td  width="6%" style="padding:10px;border-right:none;border-left:none;height:20px;text-align:left;">:</td>
								<td  width="80%" style="padding:10px;border-left:none;height:20px;text-align:left;"><?php echo $emp->Department?></td>
								</td>
							</tr>			
							<tr>
								<td width="200" style="padding:10px;border-right:none;height:20px;text-align:left;">Functional</td>
								<td  width="6%" style="padding:10px;border-right:none;border-left:none;height:20px;text-align:left;">:</td>
								<td  width="80%" style="padding:10px;border-left:none;height:20px;text-align:left;"><?php echo $emp->Subsection?></td>
								</td>
							</tr>		
							<tr>
								<td width="200" style="padding:10px;border-right:none;height:20px;text-align:left;">Branch</td>
								<td  width="6%" style="padding:10px;border-right:none;border-left:none;height:20px;text-align:left;">:</td>
								<td  width="80%" style="padding:10px;border-left:none;height:20px;text-align:left;"><?php echo $emp->Work_Area?></td>
								</td>
							</tr>
							<tr>
								<td width="200" style="padding:10px;border-right:none;height:20px;text-align:left;">Joint Date</td>
								<td  width="6%" style="padding:10px;border-right:none;border-left:none;height:20px;text-align:left;">:</td>
								<td  width="80%" style="padding:10px;border-left:none;height:20px;text-align:left;"><?php echo $emp->Date_in?></td>
								</td>
							</tr>
							<tr>
								<td width="200" style="padding:10px;border-right:none;height:20px;text-align:left;">Salary Period</td>
								<td  width="6%" style="padding:10px;border-right:none;border-left:none;height:20px;text-align:left;">:</td>
								<td  width="80%" style="padding:10px;border-left:none;height:20px;text-align:left;"><?php echo $salary_main->Period?></td>
								</td>
							</tr>								
						</table>

					</div>
					
					
			
				<div style="padding-left:20px;padding-top:20px;padding-right:20px;width:95%">
						<table width="100%" cellpadding="0" cellspacing="0">
							<tr>
								
								<td width="50%" style="text-align:center;border:1px solid grey;padding:10px;height:30px;font-weight:bold;background-color:#eeeeee;">ALLOWANCE</td>
								<td  width="50%" style="text-align:center;border:1px solid grey;padding:10px;border-left:none;height:30px;font-weight:bold;background-color:#eeeeee;">DEDUCTION</td>
								
							</tr>	
							
							<tr>
								
								<td width="30%" style="border:1px solid grey;border-top:none;padding:10px;height:30px;text-align:left;">
									<table cellspacing="2">
									
									<?php foreach($isi['ALLOWANCE'] as $dxm){ ?>
										
										<tr>
											<td width="200">&nbsp; <?php echo $dxm['Name'];?>
											</td>
											<td width="50" align="right">Rp. 
											</td>
											<td width="100" align="right">
											&nbsp; <?php echo Dot_Conversion($dxm['Value']);?>
											</td>
										</tr>
									<?php
									$totalincome += $dxm['Value'];
									}

									?>
									
									</table>
								</td>
								<td  width="45%" style="border:1px solid grey;border-top:none;padding:10px;border-left:none;height:30px;text-align:left;">
								
									<table cellspacing="2">
									
									<?php foreach($isi['DEDUCTION'] as $dxm){ ?>
										
										<tr>
											<td width="200">&nbsp; <?php echo $dxm['Name'];?>
											</td>
											<td width="50" align="right">Rp. 
											</td>
											<td width="100" align="right">
											&nbsp; <?php echo Dot_Conversion($dxm['Value']);?>
											</td>
										</tr>
									<?php
										$totaldeduct += $dxm['Value'];

									}

									?>
									
									</table>
								</td>
								
							</tr>		
							<tr>
								
								<td width="30%" style="border:1px solid grey;border-top:none;padding:10px;height:30px;text-align:left;">
									<table cellspacing="2">
									<tr>
												<td width="200">&nbsp; TOTAL INCOME
												</td>
												<td width="50" align="right">Rp. 
												</td>
												<td width="100" align="right">
												&nbsp; <?php echo Dot_Conversion($totalincome);?>
												</td>
											</tr>
									</table>
								</td>
								<td  width="45%" style="border:1px solid grey;border-top:none;padding:10px;border-left:none;height:30px;text-align:left;">
									<table cellspacing="2">
									<tr>
											<td width="200">&nbsp; TOTAL DEDUCTION
											</td>
											<td width="50" align="right">Rp. 
											</td>
											<td width="100" align="right">
											&nbsp; <?php echo Dot_Conversion($totaldeduct);?>
											</td>
										</tr>
									</table>

								</td>
								
							</tr>
							<?php
							$thp =$totalincome - $totaldeduct;
							?>
							<tr>
								
								<td width="30%" style="border:1px solid grey;border-top:none;border-right:none;background-color:#eeeeee;padding:10px;height:30px;text-align:right;">&nbsp;</td>
								<td  width="45%" style="border:1px solid grey;border-top:none;padding:10px;border-left:none;background-color:#eeeeee;height:30px;text-align:right;">
								
								<table cellspacing="2" style="font-weight:bold;">
									<tr>
												<td width="200">&nbsp;TAKE HOME PAY
												</td>
												<td width="50" align="right">Rp. 
												</td>
												<td width="100" align="right">
												&nbsp; <?php echo Dot_Conversion($thp);?>
												</td>
											</tr>
											
									</table>
									
									
									
								
							</tr>
								
							
						</table>

			</div>
			
			
			<div style="padding-left:20px;padding-top:20px;padding-right:20px;width:95%">
						<table width="100%" cellpadding="0" cellspacing="0">
							<tr>
								<td width="30%" style="vertical-align:top;text-align:left;height:30px;">Human Resources;</td>
								
								
							</tr>
							<tr>
								<td colspan="3" style="vertical-align:top;text-align:left;height:30px;text-align:left;">&nbsp;</td>	
							</tr>						
							<tr>
								<td width="30%" style="vertical-align:bottom;text-align:left;height:30px;text-align:left;"><div style="height:60;text-align:left"><?php echo QrCode::size(60)->generate($Saya);?></div><Br>Nama: <?php echo $Saya?></td>
								
								
							</tr>
							<tr>
								<td width="30%" style="vertical-align:top;text-align:left;height:30px;padding-top:0px">Jabatan: <?php echo $Position?></td>
								
								
							</tr>
							
						</table>

			</div>	
		
		</div>
		
	</div>
	
  </div>

</body>
</html>

