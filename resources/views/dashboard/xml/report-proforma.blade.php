<?php
	//error_reporting(E_ALL ^ E_NOTICE);
error_reporting(0);
ini_set('memory_limit', '-1');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	

	//error_reporting(E_ALL ^ E_NOTICE);
	
	if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
  		header("Content-type: application/xhtml+xml"); } else {
  		header("Content-type: text/xml");
	}
	echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");
function Bersih($Data){
	$X2 = trim($Data);
	
	$X2 = preg_replace('/[^(\x20-\x7F)]*/','', $X2);
	$X2 = htmlspecialchars($X2);
	return $X2;
}
function Filter($C) {
	$Datahtmlspecialchars = preg_replace('/[^(\x20-\x7F)]*/','', $C);
	return Bersih($Datahtmlspecialchars);
}

$Kota = $_GET["City"];
$Tgl1 = $_GET["Tgl1"];
$Tgl2 = $_GET["Tgl2"];
$Acc = $_GET['Acc'];
if ($Acc){
	$wacc = "AND Account_No='$Acc'";	
}else{
	$wacc = "";	
}
$LSP = $Kota;

$nomor = 1;
echo "<rows>";

$QAgent = DB::select(DB::raw("SELECT * FROM proforma WHERE Dateinsert BETWEEN '$Tgl1 00:00:00' AND '$Tgl2 23:59:59' ORDER BY Dateinsert"));

		  foreach($QAgent as $FGL){
			  if ($FGL->Invoice_No != ''){
				  	$cekar = DB::table("account_receive")->where("Inv_No",$FGL->Invoice_No)->first();
					if ($cekar->Inv_No != ''){
							$totalinv = $cekar->AR_Val;
							if($cekar->Status == 'CLOSED'){
								$kirimm = 0;
								$blmbayar = 0;
								$bayar = $cekar->AR_Paid;
							}elseif($cekar->Status == 'PARTIAL'){
								$kirimm = 0;
								$blmbayar = $cekar->AR_Remain;
								$bayar = $cekar->AR_Paid;
							}elseif($cekar->Status != 'CANCELED'){	
								$kirimm = $cekar->AR_Val;
								$blmbayar = 0;
								$bayar = 0;
								
							}
							
					}else{
						$cekinv = DB::table("invoice")->where("Inv_No",$FGL->Invoice_No)->first();
							$totalinv = $cekinv->Total;
							$kirimm = 0;
							$blmbayar = 0;
							$bayar = 0;
					}

			  }else{
				  $totalinv = 0;
				  $kirimm = 0;
				  $blmbayar = 0;
				  $bayar = 0;
			  }
							

				echo "<row id=\"$FGL->ID\" $warna>";
					echo "<cell $warna>$nomor</cell>";#a
					echo "<cell $warna>".Filter($FGL->Proforma_No)."</cell>";#b
					echo "<cell $warna>".Filter($FGL->BA_No)."</cell>";#c
					echo "<cell $warna>".Filter($FGL->Subject)."</cell>";#d
					echo "<cell $warna>".Filter($FGL->Periode)."</cell>";#d
					echo "<cell $warna>".Filter($FGL->Subtotal_PO)."</cell>";#d
					echo "<cell $warna>".Filter($FGL->Man_Fee)."</cell>";#g
					echo "<cell $warna>".Filter($FGL->Total)."</cell>";#h
					echo "<cell $warna>".Filter($totalinv)."</cell>";#h
					echo "<cell $warna>".Filter($kirim)."</cell>";#h
					echo "<cell $warna>".Filter($blmbayar)."</cell>";#h
					echo "<cell $warna>".Filter($bayar)."</cell>";#h
				echo "</row>";
				
				$nomor++;
		}
	



echo "</rows>";


?>
