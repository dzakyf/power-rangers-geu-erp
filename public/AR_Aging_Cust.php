<?php
//error_reporting(E_ALL ^ E_NOTICE);	
	
require_once "_libFunc.php";
require_once "./PHPExcel/IOFactory.php";

$No = 1;
 
  
$objTpl = PHPExcel_IOFactory::load("AR_Aging_Cust.xls");
$objTpl->setActiveSheetIndex(0);  //set first sheet as active


$qx = query_all("sip_finance.account_receive","Status!='CLOSED' AND Status!='CANCELED'","","ID DESC","") ;


 $No = 2;
 $Nomor = 1;
 $now = date("Y-m-d H:i:s");

while ($Data =  f_a($qx)){
 	
if ($Data['Due_Date'] =='0000-00-00' || $Data['Due_Date'] ==''  || $Data['Due_Date'] == null ){
	$invdate = strtotime($Data['Inv_Date'])+2592000;
	 $duedates = date('Y-m-d',$invdate);
	 
}else{
	$duedates = $Data['Due_Date'] ;
}
	$now = strtotime(date('c'));
	$datex = strtotime($duedates);
	$kurang = round(($now - $datex)/86400,0);

	$names = get_Customer($Data['Cust_ID']);
	$duedate = strtotime($duedates);
	
	$kurang30 =0;
	$kurang60 =0;
	$kurang90 =0;
	$kurang91 =0;
	
	if ($duedate < $now) {

		$payval = query_all("sip_finance.account_receive_log","ARNo='$Data[AR_No]' AND Action='Payment'","","ID DESC","") ;
		while ($detail = f_a($payval)){
			
			
			$over = $Data['AR_Remain']; 
			$notover = 0;
			$overdue = $kurang;
			$ptp = strtotime($detail['Processed_Date']);
			$haris = round(($ptp-$datex)/86400,0);
			
			if ($haris <= 30) {
				$kurang30 += $detail['Payment_Value'];	
			}elseif($haris >30 && $haris <=60){
				$kurang60 += $detail['Payment_Value'];
			}elseif($haris >=60 && $haris <=90){
				$kurang90 += $detail['Payment_Value'];
			}elseif($haris >90){
				$kurang91 += $detail['Payment_Value'];
					
			}
		}
			$total = $kurang30+$kurang60+$kurang90+$kurang91;
	}else{
		
		$overdue =0;

		$notover = $Data['AR_Remain']; 
		$over = 0;
		
	}
	$objTpl->getActiveSheet()->setCellValue("A$No", $Nomor);
	$objTpl->getActiveSheet()->setCellValue("B$No", stripslashes($Data["Inv_Date"]));
	$objTpl->getActiveSheet()->setCellValue("C$No", stripslashes($Data["Cust_ID"]));
	$objTpl->getActiveSheet()->setCellValue("D$No", stripslashes($names));
	$objTpl->getActiveSheet()->setCellValue("E$No", stripslashes($Data["Inv_No"]));
	$objTpl->getActiveSheet()->setCellValue("F$No", stripslashes($Data["Remark"]));
	$objTpl->getActiveSheet()->setCellValue("G$No", stripslashes($Data["AR_Remain"]));
	$objTpl->getActiveSheet()->setCellValue("H$No", stripslashes($Data["AR_Remain"]));
	$objTpl->getActiveSheet()->setCellValue("I$No", stripslashes($duedates));
	$objTpl->getActiveSheet()->setCellValue("J$No", ($kurang));
	$objTpl->getActiveSheet()->setCellValue("K$No", $Data['PTP']);
	$objTpl->getActiveSheet()->setCellValue("L$No", ($overdue));
	$objTpl->getActiveSheet()->setCellValue("M$No", ($notover));
	$objTpl->getActiveSheet()->setCellValue("N$No", ($kurang30));
	$objTpl->getActiveSheet()->setCellValue("O$No", ($kurang60));
	$objTpl->getActiveSheet()->setCellValue("P$No", ($kurang90));
	$objTpl->getActiveSheet()->setCellValue("Q$No", ($kurang91));
	$objTpl->getActiveSheet()->setCellValue("R$No", ($total));

			
//echo $accid;
	$Nomor++;
	$No += 1;
}
$filename="AgingCust_Detail".date('ymdhis').".xls"; //just some random filename
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="'.$filename.'"');
header('Cache-Control: max-age=0');
 
 
$objWriter = PHPExcel_IOFactory::createWriter($objTpl, 'Excel5');  //downloadable file is in Excel 2003 format (.xls)
$objWriter->save('php://output');  //send it to user, of course you can save it to disk also!

?>