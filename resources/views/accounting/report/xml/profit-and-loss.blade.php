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

$color = ['#ffbf00','#FFB4B4','#CCF6C8','#F9C0C0','#D0AF84','#CDC733'];
$caaa = DB::table('branch_coa')->get();
	
	 foreach($caa as $cx){
		 $normal[$cx->Acc_No] = $cx->Normal;
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
}else{
	if ($dept!=''){
		$Qbudget = DB::select(DB::raw(" SELECT DATE_FORMAT(t.Posting_Date,'%c') as tanggal, m1.Acc_No as Account_No,  IFNULL(sum(t.Debet), 0) as Debet, 
        IFNULL(sum(t.Credit), 0) as Credit 
        FROM branch_coa m1 
        JOIN branch_coa m2 
            ON m2.Acc_No like CONCAT(m1.Acc_No, '%') 
        left JOIN ho t 
            ON m2.Acc_No = t.Account_No 
        WHERE (m1.Acc_No LIKE '4%' OR m1.Acc_No LIKE '5%' OR m1.Acc_No LIKE '6%') AND t.Posting_Date LIKE '".$_GET['Tgl1']."%' AND t.Department LIKE '".$dept."'
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
        WHERE (m1.Acc_No LIKE '4%' OR m1.Acc_No LIKE '5%' OR m1.Acc_No LIKE '6%') AND t.Posting_Date LIKE '".$_GET['Tgl1']."%'
        GROUP BY tanggal, m1.Acc_No
                "));
	}
	 foreach($Qbudget as $FGL){
		if ($normal[$FGL->Account_No]  == 'Debet'){
			$data[$FGL->Account_No][$FGL->tanggal] += $FGL->Debet - $FGL->Credit;
		}else{
			$data[$FGL->Account_No][$FGL->tanggal] += $FGL->Credit - $FGL->Debet;
		}
				
			 
		
	}
	
}

$bcoa = DB::table('branch_coa')->where("Acc_No","LIKE",'4%')->orWhere("Acc_No","LIKE",'5%')->orWhere("Acc_No","LIKE",'6%')->orderBy('IATA_Code','ASC')->get();
foreach($bcoa as $coa){

	if($coa->Listed_OnSearch < 1) {
		if(!empty($coa->Grouping_ID)) {

			$warna = "style='font-weight:bold;background-color:".$color[strlen($coa->Grouping_ID)-1].";'";

		} else {
			$warna = "style='font-weight:bold;background-color:#aed6f1;'";
		}
	} else {
		$warna ='';
	}
		
	
		echo "<row id=\"$coa->Acc_No\">";
					echo "<cell $warna>$nomor</cell>";#a
					echo "<cell $warna>".$coa->Acc_No."</cell>";#b
					echo "<cell $warna>".htmlspecialchars($coa->Description)."</cell>";#b
					echo "<cell $warna>".$data[$coa->Acc_No][1]."</cell>";#b
					echo "<cell $warna>".$data[$coa->Acc_No][2]."</cell>";#b
					echo "<cell $warna>".$data[$coa->Acc_No][3]."</cell>";#b
					echo "<cell $warna>".$data[$coa->Acc_No][4]."</cell>";#b
					echo "<cell $warna>".$data[$coa->Acc_No][5]."</cell>";#b
					echo "<cell $warna>".$data[$coa->Acc_No][6]."</cell>";#b
					echo "<cell $warna>".$data[$coa->Acc_No][7]."</cell>";#b
					echo "<cell $warna>".$data[$coa->Acc_No][8]."</cell>";#b
					echo "<cell $warna>".$data[$coa->Acc_No][9]."</cell>";#b
					echo "<cell $warna>".$data[$coa->Acc_No][10]."</cell>";#b
					echo "<cell $warna>".$data[$coa->Acc_No][11]."</cell>";#b
					echo "<cell $warna>".$data[$coa->Acc_No][12]."</cell>";#b
					echo "<cell $warna>".array_sum($data[$coa->Acc_No])."</cell>";#b
					//echo "<cell $warna>".($data[$coa->Acc_No][12]/12)."</cell>";#b
					//echo "<cell $warna>".$coa->Grouping_ID."</cell>";#b

				echo "</row>";
				$nomor++;
}

echo "</rows>";
?>
