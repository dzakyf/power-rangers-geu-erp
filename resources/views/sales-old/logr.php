<?php
require_once "../_libFunc.php";
ini_set('session.gc_maxlifetime', '86400');
$key = get('key');
$uid = htmlspecialchars(get('uid'));
$pwd = htmlspecialchars(get('pwd'));
$gkey = get('gkey');

if (strcmp($gkey,$key)!= 0){
	$sisa = $gkey - $key;
	if ($sisa < 60000) {
		cont();	
	}else{
		echo "<script>apprise('Error : Your Session Expired! Please try again', {'animate':true});resetLogin();</script>"; exit;
	}
}else{
	cont();	
}

function cont(){
	global $uid,$pwd;
	$pwd_Enc = _encrypt($pwd);
	$sqls = query_all("user_l","UserID='$uid'","","","");
	$f = f_a($sqls);
//echo $pwd_Enc.",".$f['P_Enc'];exit;
	if (strcmp($f['P_Enc'],$pwd_Enc) != 0){
		
		echo "<script>ErrMsg();</script>"; exit;
		
	}else{
		
		if ($f['Status'] != 'ACTIVE'){
			
			echo "<script>apprise('Error : User ID $uid was BLOCKED!', {'animate':true});resetLogin();</script>"; exit;
		
		}else{
			
			$Exp = $f['Expired'];
			$MicroT = strtotime(date('c'));
			
			if ($Exp > $MicroT){
			
				$_SESSION['userid'] = $f['UserID'];
				$_SESSION['priv'] = $f['Priv_ID'];
				$_SESSION['name'] = $f['Name'];
				$_SESSION['location'] = $f['Location'];
				$_SESSION['kota'] = $f['City_ID'];
				$_SESSION['company'] = $f['Company_ID'];
				$_SESSION['email'] = $f['Email'];
				$_SESSION['position'] = $f['Position'];
				$_SESSION['class'] = $f['Class'];
				$_SESSION['timeout'] = time()+28800;
				$_SESSION['DOP'] =$f['DOP_Class'];
				$_SESSION['NIK'] =$f['Emp_ID'];
				$_SESSION['maincity'] =$f['Emp_ID'];
				$_SESSION['Department'] =$f['Department'];
				$_SESSION['Last_Order'] = "";
				$_SESSION['PEO_Branch'] = $f['PEO_Branch'];
				$Hostname = php_uname('n'); 
				$ip = $_SERVER['REMOTE_ADDR'];
				$os = $_SERVER['HTTP_USER_AGENT'];
				update(TBL_USR,"LastLogin=NOW()","ID=$f[ID]");
				insert(TBL_USR_LOG,"App_Type,UserID,IP_Address,Comp_Name,Operating_System","'WMS','$f[UserID]','$ip','$Hostname','$os'");
				UserLog('Login',"Logged In from $ip",'');
				
					to_index(1);
				
			}else{
				
				echo "<script>apprise('Error : Your ID was Expired! Please contact Administrator!', {'animate':true});resetLogin();</script>"; exit;
				
			}
		}
	}

}
	

?>