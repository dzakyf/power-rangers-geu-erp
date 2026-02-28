	<?php
	error_reporting(0);

	use Illuminate\Http\Request;
	use Illuminate\Http\Custom_Function;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Database\Eloquent\Builder;
	use Illuminate\Support\Facades\Auth;

	$ars = "";
	$ax = "";
	$isi = "";
	foreach ($_POST as $key => $value) {

		if ($key != 'DelPRNewDetail' && $key != 'SubmitPRNewDetail' && $key != 'AddPRNewDetail') {
			$ars .= "$key = '$value',";
			$ax .= "$key,";
			$isi .= "'$value',";
		}
	}
	$Saya = Auth::User()->username;
	$LSP = Auth::user()->Location;
	$Name = Auth::User()->Name;
	$ars = substr($ars, 0, -1);
	$ax = substr($ax, 0, -1);
	$isi = substr($isi, 0, -1);

	$getnik = Auth::user()->Emp_ID;
	$dept = Auth::user()->Department;
	$getlevelsx = DB::table("organization_structure")->where("NIK", $getnik)->first();
	if ($getlevelsx->Level1 == 1) $lepel = 1;
	else if ($getlevelsx->Level2 == 1) $lepel = 2;
	else if ($getlevelsx->Level3 == 1) $lepel = 3;
	else if ($getlevelsx->Level4 == 1) $lepel = 4;
	else if ($getlevelsx->Level5 == 1) $lepel = 5;
	else if ($getlevelsx->Level6 == 1) $lepel = 6;
	else if ($getlevelsx->Level7 == 1) $lepel = 7;
	else if ($getlevelsx->Level8 == 1) $lepel = 8;
	$upper = $lepel - 1;
	if($upper == 3) {
		$getupper = DB::table("organization_structure")->where("Level3", 1)->where("Department", $_POST['Department'])->where("Alerted", 1)->first();
		$status = "APPROVAL_UPPER";
		$link = 'approvalpr';
	} else{
		$getupper = DB::table("organization_structure")->where("Level2", 1)->where("Department", $getlevelsx->divisi)->where("Alerted", 1)->first();
		$status = "APPROVAL_UPPER";
		$link = 'approvalpr';
	}

	DB::insert(DB::raw("INSERT INTO purchase_request($ax,Status,Created_By,Location,Approval_Department) VALUES($isi,'APPROVAL_UPPER','$Saya','$LSP','BUS')"));
	DB::insert(DB::raw("INSERT INTO purchase_log (Location, Task_ID, Category, Sub_Category, Purchase_Type, Transaction, Status, Remark, Date_Trx, User_Trx) VALUES ('$LSP', '$_POST[PR_No]', '$_POST[Category]', '$_POST[Sub_Category]', '$_POST[Supplies_Type]', 'PURCHASE-REQUEST', 'APPROVED', '$_POST[Note]', NOW(), '$Saya')"));

	
	$subjek = "PURCHASE REQUEST APPROVAL REQUEST PR NO $_POST[PR_No] # , Request By : $Name";
	$badan = "<div style=\"font-family:arial;font-size:12px;\">Kepada Yth.<br><b>$getupper->Name</b><Br><br>
	
				Mohon dilakukan approval untuk Purchase Request tersebut. Berikut Detail PR yang telah dibuat<br>
				
				No PR : $_POST[PR_No]<br><br>
				Reference : $_POST[Reference]<br><br>
				Jenis Request : $_POST[Category] // $_POST[Sub_Category] // $_POST[Supplies_Type]<br><br>
				Note : $_POST[Note]<br>
				
				
				Mohon dilakukan approval untuk Permintaan Purchase Request.
				
				<br><br>
				Anda dapat melakukan approval melalui link berikut ini :<a href=\'https://erp.ksps.co.id/erpksps/public/approvalpr?ID=$_POST[PR_No]&linkkey=$linkkey\' >KLIK DISINI</a><br><br>
				
				
				<br><br>
				
				Terima kasih
				GREEN ENERGI UTAMA
				
	</div>";
	DB::insert(DB::raw("INSERT INTO mail_logs(Email_To,Subject,Contents,Status,Inserted_Date,Send_Date) VALUES('$getupper->Email','$subjek','$badan','UNSENT',NOW(),NOW())"));


	echo "success";

	?>