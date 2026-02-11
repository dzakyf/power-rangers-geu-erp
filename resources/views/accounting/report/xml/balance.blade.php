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


$caa = DB::table('branch_coa')->get();
	
	 foreach($caa as $cx){
		 $namacoa[$cx->Acc_No] = $cx->Description;
		 $normal[$cx->Acc_No] = $cx->Normal;
	 }
	
	$thnmundur =  $_GET['Tgl1'] - 1;
	$caax = DB::table('ho_sa_year')->where("Journal_Year",$thnmundur)->get();
	
	 foreach($caax as $cxx){
		 if ($cxx->Remark!=''){
			$beg[$cxx->Acc_No][$cxx->Remark] = $cxx->Journal_Value;
		 }else{
			 $beg[$cxx->Acc_No]['none'] = $cxx->Journal_Value;
		 }
	 }
	 
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
	 foreach($Qbudget as $fbudget){

			$QAgent = DB::table('budget_detail')->where("Task_ID","=",$fbudget->ID)->orderBy('COA','ASC')->get();

			  foreach($QAgent as $FGL){
				  	$coa = DB::table('branch_coa')->where("Acc_No",$FGL->COA)->first();
				
					if ($coa->Klass_Ref == 'BANK'){
						$data[$FGL->COA][1][$coa->Acc_No] += $FGL->January_Value;
						$data[$FGL->COA][2][$coa->Acc_No] += $FGL->February_Value;
						$data[$FGL->COA][3][$coa->Acc_No] += $FGL->March_Value;
						$data[$FGL->COA][4][$coa->Acc_No] += $FGL->April_Value;
						$data[$FGL->COA][5][$coa->Acc_No] += $FGL->May_Value;
						$data[$FGL->COA][6][$coa->Acc_No] += $FGL->June_Value;
						$data[$FGL->COA][7][$coa->Acc_No] += $FGL->July_Value;
						$data[$FGL->COA][8][$coa->Acc_No] += $FGL->August_Value;
						$data[$FGL->COA][9][$coa->Acc_No] += $FGL->September_Value;
						$data[$FGL->COA][10][$coa->Acc_No] += $FGL->October_Value;
						$data[$FGL->COA][11][$coa->Acc_No] += $FGL->November_Value;
						$data[$FGL->COA][12][$coa->Acc_No] += $FGL->December_Value;
						
					}else{
						$data[$FGL->COA][1]['none'] += $FGL->January_Value;
						$data[$FGL->COA][2]['none'] += $FGL->February_Value;
						$data[$FGL->COA][3]['none'] += $FGL->March_Value;
						$data[$FGL->COA][4]['none'] += $FGL->April_Value;
						$data[$FGL->COA][5]['none'] += $FGL->May_Value;
						$data[$FGL->COA][6]['none'] += $FGL->June_Value;
						$data[$FGL->COA][7]['none'] += $FGL->July_Value;
						$data[$FGL->COA][8]['none'] += $FGL->August_Value;
						$data[$FGL->COA][9]['none'] += $FGL->September_Value;
						$data[$FGL->COA][10]['none'] += $FGL->October_Value;
						$data[$FGL->COA][11]['none'] += $FGL->November_Value;
						$data[$FGL->COA][12]['none'] += $FGL->December_Value;
					}
			 }
		
	}
}else{
	if ($dept!=''){
		$Qbudget = DB::select(DB::raw(" SELECT DATE_FORMAT(t.Posting_Date,'%c') as tanggal, m1.Acc_No as Account_No,  IFNULL(sum(t.Debet), 0) as Debet, 
        IFNULL(sum(t.Credit), 0) as Credit 
        FROM branch_coa m1 
        JOIN branch_coa m2 
            ON m2.Acc_No like CONCAT(m1.Acc_No, '%') 
        left JOIN ho t 
            ON m2.Acc_No = t.Account_No 
        WHERE (m1.Acc_No LIKE '1%' OR m1.Acc_No LIKE '2%' OR m1.Acc_No LIKE '3%') AND t.Posting_Date LIKE '".$_GET['Tgl1']."%' AND t.Department LIKE '".$dept."'
        GROUP BY tanggal, m1.Acc_No
                "));
	}else{
		$Qbudget = DB::select(DB::raw(" SELECT   DATE_FORMAT(t.Posting_Date,'%c') as tanggal, m1.Acc_No as Account_No, IFNULL(sum(t.Debet), 0) as Debet, 
        IFNULL(sum(t.Credit), 0) as Credit 
        FROM branch_coa m1 
        JOIN branch_coa m2 
            ON m2.Acc_No like CONCAT(m1.Acc_No, '%') 
        left JOIN ho t 
            ON m2.Acc_No = t.Account_No 
        WHERE (m1.Acc_No LIKE '1%' OR m1.Acc_No LIKE '2%' OR m1.Acc_No LIKE '3%') AND t.Posting_Date LIKE '".$_GET['Tgl1']."%'
        GROUP BY tanggal, m1.Acc_No
                "));
	}
	 foreach($Qbudget as $FGL){

			$coa = DB::table('branch_coa')->where("Acc_No",$FGL->Account_No)->first();
				
				if ($coa->Klass_Ref == 'BANK'){
					if ($normal[$FGL->Account_No]  == 'Debet'){
						$data[$FGL->Account_No][$FGL->tanggal][$FGL->Reference_No] +=  $FGL->Debet - $FGL->Credit;
					}else{
						$data[$FGL->Account_No][$FGL->tanggal][$FGL->Reference_No] +=  $FGL->Credit - $FGL->Debet;
					}
				}else{
					
					if ($normal[$FGL->Account_No]  == 'Debet'){
						$data[$FGL->Account_No][$FGL->tanggal]['none'] +=  $FGL->Debet - $FGL->Credit;
					}else{
						$data[$FGL->Account_No][$FGL->tanggal]['none'] +=  $FGL->Credit - $FGL->Debet;
					}
				}
		
			 
		
	}
}

$bcoa = DB::table('branch_coa')->where("Acc_No","LIKE",'1%')->orWhere("Acc_No","LIKE",'2%')->orWhere("Acc_No","LIKE",'3%')->orderBy('IATA_Code','ASC')->get();
foreach($bcoa as $coa){

	
	if($coa->Listed_OnSearch < 1) {
		if(!empty($coa->Grouping_ID)) {
			$warna = "style='font-weight:bold;background-color:#d5f5e3;'";
		} else {
			$warna = "style='font-weight:bold;background-color:#aed6f1;'";
		}
	} else {
		$warna ='';
	}
	$beginning = $beg[$coa->Acc_No]['none'];
	$data[$coa->Acc_No][1]['none'] += $beginning;
	$data[$coa->Acc_No][2]['none'] += $data[$coa->Acc_No][1]['none'];
	$data[$coa->Acc_No][3]['none'] += $data[$coa->Acc_No][2]['none'];
	$data[$coa->Acc_No][4]['none'] += $data[$coa->Acc_No][3]['none'];
	$data[$coa->Acc_No][5]['none'] += $data[$coa->Acc_No][4]['none'];
	$data[$coa->Acc_No][6]['none'] += $data[$coa->Acc_No][5]['none'];
	$data[$coa->Acc_No][7]['none'] += $data[$coa->Acc_No][6]['none'];
	$data[$coa->Acc_No][8]['none'] += $data[$coa->Acc_No][7]['none'];
	$data[$coa->Acc_No][9]['none'] += $data[$coa->Acc_No][8]['none'];
	$data[$coa->Acc_No][10]['none'] += $data[$coa->Acc_No][9]['none'];
	$data[$coa->Acc_No][11]['none'] += $data[$coa->Acc_No][10]['none'];
	$data[$coa->Acc_No][12]['none'] += $data[$coa->Acc_No][11]['none'];
		echo "<row id=\"$coa->ID\">";
					echo "<cell $warna>".$coa->Grouping_ID."</cell>";#b
					echo "<cell $warna>".$coa->Acc_No."</cell>";#b
					echo "<cell $warna>".htmlspecialchars($coa->Description)."</cell>";#b
					echo "<cell $warna>".$beginning."</cell>";#b
					echo "<cell $warna>".$data[$coa->Acc_No][1]['none']."</cell>";#b
					echo "<cell $warna>".$data[$coa->Acc_No][2]['none']."</cell>";#b
					echo "<cell $warna>".$data[$coa->Acc_No][3]['none']."</cell>";#b
					echo "<cell $warna>".$data[$coa->Acc_No][4]['none']."</cell>";#b
					echo "<cell $warna>".$data[$coa->Acc_No][5]['none']."</cell>";#b
					echo "<cell $warna>".$data[$coa->Acc_No][6]['none']."</cell>";#b
					echo "<cell $warna>".$data[$coa->Acc_No][7]['none']."</cell>";#b
					echo "<cell $warna>".$data[$coa->Acc_No][8]['none']."</cell>";#b
					echo "<cell $warna>".$data[$coa->Acc_No][9]['none']."</cell>";#b
					echo "<cell $warna>".$data[$coa->Acc_No][10]['none']."</cell>";#b
					echo "<cell $warna>".$data[$coa->Acc_No][11]['none']."</cell>";#b
					echo "<cell $warna>".$data[$coa->Acc_No][12]['none']."</cell>";#b
					echo "<cell $warna>".($data[$coa->Acc_No][12]['none']/12)."</cell>";#b
					echo "<cell $warna>".$namacoa[$coa->Grouping_ID]."</cell>";#b

				echo "</row>";
				$nomor++;
				
				
	if ($coa->Klass_Ref == 'BANK'){
			
			
			$nox = 1;
			$qy = DB::table("bank_acc")->where("Bank_Acc",$coa->Acc_No)->get();
			
			if (count($qy)){
				foreach ($qy as $ax){
					$beginning = $beg[$coa->Acc_No][$ax->Acc_No];
					$data[$coa->Acc_No][1][$ax->Acc_No] += $beginning;
					$data[$coa->Acc_No][2][$ax->Acc_No] += $data[$coa->Acc_No][1][$ax->Acc_No];
					$data[$coa->Acc_No][3][$ax->Acc_No] += $data[$coa->Acc_No][2][$ax->Acc_No];
					$data[$coa->Acc_No][4][$ax->Acc_No] += $data[$coa->Acc_No][3][$ax->Acc_No];
					$data[$coa->Acc_No][5][$ax->Acc_No] += $data[$coa->Acc_No][4][$ax->Acc_No];
					$data[$coa->Acc_No][6][$ax->Acc_No] += $data[$coa->Acc_No][5][$ax->Acc_No];
					$data[$coa->Acc_No][7][$ax->Acc_No] += $data[$coa->Acc_No][6][$ax->Acc_No];
					$data[$coa->Acc_No][8][$ax->Acc_No] += $data[$coa->Acc_No][7][$ax->Acc_No];
					$data[$coa->Acc_No][9][$ax->Acc_No] += $data[$coa->Acc_No][8][$ax->Acc_No];
					$data[$coa->Acc_No][10][$ax->Acc_No] += $data[$coa->Acc_No][9][$ax->Acc_No];
					$data[$coa->Acc_No][11][$ax->Acc_No] += $data[$coa->Acc_No][10][$ax->Acc_No];
					$data[$coa->Acc_No][12][$ax->Acc_No] += $data[$coa->Acc_No][11][$ax->Acc_No];
					echo "<row id=\"$nomor\">";
						echo "<cell $warna>".$coa->Grouping_ID."</cell>";#b
						echo "<cell $warna>".$coa->Acc_No.".".$nox."</cell>";#b
						echo "<cell $warna>".htmlspecialchars($coa->Description. " # ".$ax->Acc_No)."</cell>";#b
						echo "<cell $warna>".$beginning."</cell>";#b
						echo "<cell $warna>".$data[$coa->Acc_No][1][$ax->Acc_No]."</cell>";#b
						echo "<cell $warna>".$data[$coa->Acc_No][2][$ax->Acc_No]."</cell>";#b
						echo "<cell $warna>".$data[$coa->Acc_No][3][$ax->Acc_No]."</cell>";#b
						echo "<cell $warna>".$data[$coa->Acc_No][4][$ax->Acc_No]."</cell>";#b
						echo "<cell $warna>".$data[$coa->Acc_No][5][$ax->Acc_No]."</cell>";#b
						echo "<cell $warna>".$data[$coa->Acc_No][6][$ax->Acc_No]."</cell>";#b
						echo "<cell $warna>".$data[$coa->Acc_No][7][$ax->Acc_No]."</cell>";#b
						echo "<cell $warna>".$data[$coa->Acc_No][8][$ax->Acc_No]."</cell>";#b
						echo "<cell $warna>".$data[$coa->Acc_No][9][$ax->Acc_No]."</cell>";#b
						echo "<cell $warna>".$data[$coa->Acc_No][10][$ax->Acc_No]."</cell>";#b
						echo "<cell $warna>".$data[$coa->Acc_No][11][$ax->Acc_No]."</cell>";#b
						echo "<cell $warna>".$data[$coa->Acc_No][12][$ax->Acc_No]."</cell>";#b
						echo "<cell $warna>".($data[$coa->Acc_No][12][$ax->Acc_No]/12)."</cell>";#b
					echo "<cell $warna>".$namacoa[$coa->Grouping_ID]."</cell>";#b

					echo "</row>";
					$nomor++;
					$nox++;
				
				}
			}
	}
		
		
	
}

echo "</rows>";
?>
