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
$nomor = 1;
echo "<rows>";
$tipe = $_GET['tipe'];
$dept = $_GET['dept'];

if ($tipe == 'Budget'){
	if ($dept!=''){
		$Qbudget = DB::table('budget')->where("Budget_Year","=",$_GET['Tgl1'])->where("Department_Code",$dept)->get();
	}else{
		$Qbudget = DB::table('budget')->where("Budget_Year","=",$_GET['Tgl1'])->orderBy('Department_Code','ASC')->get();
	}
	$saldoku = 0;

	
	


	 foreach($Qbudget as $fbudget){

		$QAgent = DB::table('budget_detail')->where("Task_ID","=",$fbudget->ID)->orderBy('COA','ASC')->get();

				  foreach($QAgent as $FGL){
					$data[$FGL->COA][1] += $FGL->January_Value;
					$data[$FGL->COA][2] += $FGL->February_Value;
					$data[$FGL->COA][3] += $FGL->March_Value;
					$data[$FGL->COA][4] += $FGL->April_Value;
					$data[$FGL->COA][5] += $FGL->May_Value;
					$data[$FGL->COA][6] += $FGL->June_Value;
					$data[$FGL->COA][7] += $FGL->July_Value;
					$data[$FGL->COA][8] += $FGL->August_Value;
					$data[$FGL->COA][9] += $FGL->September_Value;
					$data[$FGL->COA][10] += $FGL->October_Value;
					$data[$FGL->COA][11] += $FGL->November_Value;
					$data[$FGL->COA][12] += $FGL->December_Value;
					
				 }
			
		}


			echo "<row id=\"$nomor\">";
						echo "<cell>$nomor</cell>";#a
						echo "<cell>000000</cell>";#b
						echo "<cell>SALDO AWAL</cell>";#b
						echo "<cell>".$saldo[$coa->Acc_No][1]."</cell>";#b
						echo "<cell>".$saldo[$coa->Acc_No][2]."</cell>";#b
						echo "<cell>".$saldo[$coa->Acc_No][3]."</cell>";#b
						echo "<cell>".$saldo[$coa->Acc_No][4]."</cell>";#b
						echo "<cell>".$saldo[$coa->Acc_No][5]."</cell>";#b
						echo "<cell>".$saldo[$coa->Acc_No][6]."</cell>";#b
						echo "<cell>".$saldo[$coa->Acc_No][7]."</cell>";#b
						echo "<cell>".$saldo[$coa->Acc_No][8]."</cell>";#b
						echo "<cell>".$saldo[$coa->Acc_No][9]."</cell>";#b
						echo "<cell>".$saldo[$coa->Acc_No][10]."</cell>";#b
						echo "<cell>".$saldo[$coa->Acc_No][11]."</cell>";#b
						echo "<cell>".$saldo[$coa->Acc_No][12]."</cell>";#b

			echo "</row>";
			$nomor++;
			echo "<row id=\"$nomor\">";
						echo "<cell>$nomor</cell>";#a
						echo "<cell>100000</cell>";#b
						echo "<cell>JUMLAH PENERIMAAN</cell>";#b
						echo "<cell>".$pendapatan[$coa->Acc_No][1]."</cell>";#b
						echo "<cell>".$pendapatan[$coa->Acc_No][2]."</cell>";#b
						echo "<cell>".$pendapatan[$coa->Acc_No][3]."</cell>";#b
						echo "<cell>".$pendapatan[$coa->Acc_No][4]."</cell>";#b
						echo "<cell>".$pendapatan[$coa->Acc_No][5]."</cell>";#b
						echo "<cell>".$pendapatan[$coa->Acc_No][6]."</cell>";#b
						echo "<cell>".$pendapatan[$coa->Acc_No][7]."</cell>";#b
						echo "<cell>".$pendapatan[$coa->Acc_No][8]."</cell>";#b
						echo "<cell>".$pendapatan[$coa->Acc_No][9]."</cell>";#b
						echo "<cell>".$pendapatan[$coa->Acc_No][10]."</cell>";#b
						echo "<cell>".$pendapatan[$coa->Acc_No][11]."</cell>";#b
						echo "<cell>".$pendapatan[$coa->Acc_No][12]."</cell>";#b

			echo "</row>";
			$nomor++;
			
			echo "<row id=\"$nomor\">";
						echo "<cell>$nomor</cell>";#a
						echo "<cell>200000</cell>";#b
						echo "<cell>JUMLAH PENGELUARAN</cell>";#b
						echo "<cell>".$pendapatan[$coa->Acc_No][1]."</cell>";#b
						echo "<cell>".$pendapatan[$coa->Acc_No][2]."</cell>";#b
						echo "<cell>".$pendapatan[$coa->Acc_No][3]."</cell>";#b
						echo "<cell>".$pendapatan[$coa->Acc_No][4]."</cell>";#b
						echo "<cell>".$pendapatan[$coa->Acc_No][5]."</cell>";#b
						echo "<cell>".$pendapatan[$coa->Acc_No][6]."</cell>";#b
						echo "<cell>".$pendapatan[$coa->Acc_No][7]."</cell>";#b
						echo "<cell>".$pendapatan[$coa->Acc_No][8]."</cell>";#b
						echo "<cell>".$pendapatan[$coa->Acc_No][9]."</cell>";#b
						echo "<cell>".$pendapatan[$coa->Acc_No][10]."</cell>";#b
						echo "<cell>".$pendapatan[$coa->Acc_No][11]."</cell>";#b
						echo "<cell>".$pendapatan[$coa->Acc_No][12]."</cell>";#b

			echo "</row>";
			$nomor++;

			echo "<row id=\"$nomor\">";
						echo "<cell>$nomor</cell>";#a
						echo "<cell>300000</cell>";#b
						echo "<cell>SALDO AKHIR</cell>";#b
						echo "<cell>".$pendapatan[$coa->Acc_No][1]."</cell>";#b
						echo "<cell>".$pendapatan[$coa->Acc_No][2]."</cell>";#b
						echo "<cell>".$pendapatan[$coa->Acc_No][3]."</cell>";#b
						echo "<cell>".$pendapatan[$coa->Acc_No][4]."</cell>";#b
						echo "<cell>".$pendapatan[$coa->Acc_No][5]."</cell>";#b
						echo "<cell>".$pendapatan[$coa->Acc_No][6]."</cell>";#b
						echo "<cell>".$pendapatan[$coa->Acc_No][7]."</cell>";#b
						echo "<cell>".$pendapatan[$coa->Acc_No][8]."</cell>";#b
						echo "<cell>".$pendapatan[$coa->Acc_No][9]."</cell>";#b
						echo "<cell>".$pendapatan[$coa->Acc_No][10]."</cell>";#b
						echo "<cell>".$pendapatan[$coa->Acc_No][11]."</cell>";#b
						echo "<cell>".$pendapatan[$coa->Acc_No][12]."</cell>";#b

			echo "</row>";
			$nomor++;
}else{
			if ($dept!=''){
					$Qbudget = DB::table('ho')->where("Posting_Date","LIKE","%$_GET[Tgl1]%")->where("Account_No","LIKE","111%")->where("Department",$dept)->get();
				}else{
					$Qbudget = DB::table('ho')->where("Posting_Date","LIKE","%$_GET[Tgl1]%")->where("Account_No","LIKE","111%")->orderBy('Department','ASC')->get();
				}
				$saldoawal = DB::table("ho_sa_year")->where("Journal_Year","=",($_GET['Tgl1'] - 1))->where("Acc_No","LIKE","111%")
				->get();
				foreach($saldoawal as $saldoaw){
						$sal[1] += $saldoaw->Journal_Value;
					
				}
				$awalsaldo = $sal[1] - $sal[2];
				
				foreach($Qbudget as $fdata){
					$bulan = (int)date('m',strtotime($fdata->Posting_Date));
					$debet[$bulan] += $fdata->Debet;
					$kredit[$bulan] += $fdata->Credit;
				}
				$saldoku = $awalsaldo;
				echo "<row id=\"$nomor\">";
						echo "<cell>$nomor</cell>";#a
						echo "<cell>000000</cell>";#b
						echo "<cell>SALDO AWAL</cell>";#b
						echo "<cell>".$saldoku."</cell>";#b
						
						for ($ix = 1;$ix < 13;$ix++){
							$saldoku = ($saldoku + $debet[$ix]) - $kredit[$ix];
							$saldoakhir[$ix] = $saldoku;
						echo "<cell>".$saldoku."</cell>";#b
						
						}
						

				echo "</row>";
				$nomor++;
			echo "<row id=\"$nomor\">";
						echo "<cell>$nomor</cell>";#a
						echo "<cell>100000</cell>";#b
						echo "<cell>JUMLAH PENERIMAAN</cell>";#b
						for ($ix =1;$ix<13;$ix++){
						echo "<cell>".$debet[$ix]."</cell>";#b
						
						}

			echo "</row>";
			$nomor++;
			
			echo "<row id=\"$nomor\">";
						echo "<cell>$nomor</cell>";#a
						echo "<cell>200000</cell>";#b
						echo "<cell>JUMLAH PENGELUARAN</cell>";#b
						for ($ix =1;$ix<13;$ix++){
						echo "<cell>".$kredit[$ix]."</cell>";#b
						
						}

			echo "</row>";
			$nomor++;
			$sal1 = ($awalsaldo + $debet[1]) - $kredit[1];
			echo "<row id=\"$nomor\">";
						echo "<cell>$nomor</cell>";#a
						echo "<cell>300000</cell>";#b
						echo "<cell>SALDO AKHIR</cell>";#b
						echo "<cell>".$sal1."</cell>";#b

						for ($ix =2;$ix<13;$ix++){
						echo "<cell>".$saldoakhir[$ix]."</cell>";#b
						
						}
			echo "</row>";
			$nomor++;
				
}
	
echo "</rows>";
?>
