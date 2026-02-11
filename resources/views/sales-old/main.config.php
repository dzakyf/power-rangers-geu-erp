<?php
///----------Mysql Auth Config-----
$ServerHost	=	"localhost";
$MysqlU		=	"u8774782_confetti";
$MysqlP		=	"0hgitu";
$DBPort		=	"3306";
$Dbase		=	"u8774782_confetti";

function ConnectDbase($ServerHost,$MysqlU,$MysqlP,$Dbase,$DBPort){
	$Conn = mysqli_connect($ServerHost . ":" . $DBPort , $MysqlU, $MysqlP,$Dbase);
	if (!$Conn){
		echo mysqli_error($Conn);
	}		
	return $Conn;
}

$con= ConnectDbase($ServerHost,$MysqlU,$MysqlP,$Dbase,$DBPort);
	

define ('TBL_PRIVILEGE','user_privilege_list');
define ('TBL_USR','user_l');
define ('TBL_USR_LOG','user_l_log');
define ('TBL_PRODUCT','product_code');
define ('TBL_PRODUCT_GROUP','product_group');
define ('TBL_BIN','bin_list');




?>
