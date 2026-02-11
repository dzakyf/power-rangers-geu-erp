<?php
session_start();
require_once "../_libFunc.php";
require_once 'excel_reader3.php';
$fileid = "journ-".md5(date('Ymdhi'));
$filex  = "djb_balance.xls";
//echo $filex;exit;
//error_reporting(0);
if (!file_exists($filex)) {
echo "<script>alert('Error.. Invalid File! $filex');</script>";
exit;
}
$nik = NIK();
$ID = get('ID');
$Saya = saya();

function batasin($isi){
	$X = trim($isi);
	$X2 = Bersih($X);
	$X2 = preg_replace('/[^(\x20-\x7F)]*/','', $X2);
	$X2 = str_replace("'","`",$X2);
	$X2 = str_replace("\"","`",$X2);
	$X2 = str_replace("\"","`",$X2);
	$X2 = str_replace("","",$X2);
	$X2 = str_replace("�"," ",$X2);
	$X2 = str_replace(""," ",$X2);
	$X2 = str_replace('"',"`",$X2);
	$X2 = str_replace('"',"",$X2);
	return $X2;

}

function cekAkunCOA($acc){
	$sql = query("SELECT * FROM mam_akunting.branch_coa WHERE Acc_No='$acc'");
	$row = f_a($sql);
	return $row;
}



function clearkan($isi){
	$hasilx = preg_replace('/[^(\x20-\x7F)]*/','', $isi);
	return $hasilx;

}

function replace_x($datx){
	$dx = trim(preg_replace("/'/", "", $datx));
	$clir=clearkan($dx);
	return ($clir);
}

$data = new Spreadsheet_Excel_Reader($filex);

$jumbaris=$data->rowcount($sheet_index=0) ;
$urut=1;
$baris=3;

for($urut=1;$urut<$jumbaris;$urut++){
	$baris++;
	
	$a=batasin(replace_x($data->val($baris,'A',$sheet_index=0)),0);//date
	$b=batasin(replace_x($data->val($baris,'B',$sheet_index=0)),0);//coa debet
	$c=batasin(replace_x($data->val($baris,'C',$sheet_index=0)),0);//coa debet
	$d=batasin(replace_x($data->val($baris,'D',$sheet_index=0)),0);//remark
	$e=($data->val($baris,'E',$sheet_index=0));//amount
	$f=batasin(replace_x($data->val($baris,'F',$sheet_index=0)),0); //Coa Credit 
	$g=batasin(replace_x($data->val($baris,'G',$sheet_index=0)),0);	//coa credit name
	$h=batasin(replace_x($data->val($baris,'H',$sheet_index=0)),0);	//norek
	$ix=batasin(replace_x($data->val($baris,'I',$sheet_index=0)),0);	//bank id
	$j=batasin(replace_x($data->val($baris,'J',$sheet_index=0)),0);	//bank id
	$k=batasin(replace_x($data->val($baris,'K',$sheet_index=0)),0);	//bank id
	$l=batasin(replace_x($data->val($baris,'L',$sheet_index=0)),0);	//bank id
	$m=batasin(replace_x($data->val($baris,'M',$sheet_index=0)),0);	//bank id
	$n=batasin(replace_x($data->val($baris,'N',$sheet_index=0)),0);	//bank id
	$o=batasin(replace_x($data->val($baris,'O',$sheet_index=0)),0);	//bank id
	
	echo "aa $b -> $e <br>";	
/*	if (trim($b) != '' && $b!=null){	
	query("INSERT INTO mam_akunting.branch_journal_sa_year 
		(Acc_No,Description,Location,Group_Bal,IATA_Code,Journal_Value,Journal_Year,Created_Date,Created_By) 
		VALUES
		('$b','$stat[Description]','$d','$stat[Group_Bal]','$d','$e','$f',NOW(),'$saya')");
	}
	*/
}
?>