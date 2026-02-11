<?php
require_once "../_libFunc.php";
ini_set('session.gc_maxlifetime', '86400');
$key = $_POST['rcheck'];
$uid =  $_POST['nama'];
$pwd =  $_POST['pass'];
$gkey =  $_POST['vals'];

$passtrue = md5($gkey."YYG01922901009910");
if (strcmp($passtrue,$key)!== 0){
	
		
		
		exit;
	
}else{
	cont();	
}

function cont(){
	global $uid,$pwd;
	if ($uid == '' || $pwd == ''){
		echo "<script>alert('Invalid Username or Password!');location.href='../grnApp/';</script>"; exit;
		
	}
	$pwd_Enc = _encrypt($pwd);
	$sqls = query_all("users","UserID='$uid'","","","");

	$f = f_a($sqls);
	//echo "$f[P_Enc],$pwd_Enc";exit;
	
	if (strcmp($f['P_Enc'],$pwd_Enc) != 0){
		
		echo "<script>alert('Invalid Username or Password!');location.href='../grnApp/';</script>"; exit;
		
	}else{
		
		if ($f['Status'] != 'ACTIVE'){
			
			echo "<script>alert('Error : User ID $uid was BLOCKED!');location.href='../grnApp/';</script>"; exit;
		
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
				$_SESSION['Area'] = $f['Area'];
				$_SESSION['Note'] = $f['Note'];
				
				$Hostname = php_uname('n'); 
				$ip = $_SERVER['REMOTE_ADDR'];
				$os = $_SERVER['HTTP_USER_AGENT'];
				update("users","LastLogin=NOW()","ID=$f[ID]");
				insert(TBL_USR_LOG,"App_Type,UserID,IP_Address,Comp_Name,Operating_System","'grn_SPMS','$f[UserID]','$ip','$Hostname','$os'");
				UserLog('Login',"Logged In from $ip",'');
				if ($f['Priv_ID'] == 'CUSTOMER'){
					to_index(5);
					
				}else{
					to_index(1);
				}
			}else{
				
				echo "<script>alert('Error : Your ID was Expired! Please contact Administrator!';location.href='../grnApp/';</script>"; exit;
				
			}
		}
	}

}
	

?>