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
	$cek = DB::table("purchase_request")->where("ID", $_POST['ID'])->first();
	$dept = DB::table("master_product_sub_sub_group")->where("Product_Group", $cek->Category)->where("Sub_Group", $cek->Sub_Category)->where("Sub_Sub_Group", $cek->Supplies_Type)->first();


	$ars = substr($ars, 0, -1);
	$ax = substr($ax, 0, -1);
	$isi = substr($isi, 0, -1);
	switch ($_POST['Status']) {
		case "CREATED":
			$nstatus = "PROPOSED";
			break;
		case "APPROVAL_UPPER":

			$nstatus = "APPROVAL_FINANCE";
			$getupper = DB::table("organization_structure")->where("Level3", 1)->where("Department", "FIN")->first();

			break;
		case "APPROVAL_FINANCE":

			$nstatus = "APPROVAL_GA";
			$getupper = DB::table("organization_structure")->where("Level3", 1)->where("Department", "GEA")->first();

			break;
		case "APPROVAL_GA":

			$nstatus = "APPROVED";

			break;
		default:
			$nstatus = 'CREATED';
			break;
	}
	DB::update(DB::raw("UPDATE purchase_request SET Note='$_POST[Note]',Reference='$_POST[Reference]',Status='$nstatus' WHERE ID=$_POST[ID]"));

	DB::insert(DB::raw("INSERT INTO purchase_log (Location, Task_ID, Category, Sub_Category, Purchase_Type, Transaction, Status, Remark, Date_Trx, User_Trx) VALUES ('$LSP', '$_POST[PR_No]', '$_POST[Category]', '$_POST[Sub_Category]', '$_POST[Supplies_Type]', 'PURCHASE-REQUEST', '$nstatus', '$_POST[Note]', NOW(), '$Saya')"));

	$subjek = "IMPORTANT : PURCHASE REQUEST APPROVAL REQUEST PR NO $_POST[PR_No] # , Request By : $Name";
	$badan = "<div style=\"font-family:arial;font-size:12px;\">Kepada Yth.<br><b>$getupper->Name</b><Br><br>
		
					Mohon dilakukan approval untuk Purchase Request tersebut. persetujuan dapat dilakukan melalui system ERP pada menu Procurement -> Request ->Approval<br><br>
					
					No PR : $_POST[PR_No]<br><br>
					Reference : $_POST[Reference]<br><br>
					Jenis Request : $_POST[Category] // $_POST[Sub_Category] // $_POST[Supplies_Type]<br><br>
					Note : $_POST[Note]<br>
					
					<br><br>
					Anda dapat melakukan approval melalui link berikut ini :<a href=\'https://erp.ksps.co.id/erpksps/public/approvalpr?ID=$_POST[PR_No]&linkkey=$linkkey&saya=$Saya\' >KLIK DISINI</a><br><br>
					
					Terima kasih<br>
					PT GREEN ENERGY UTAMA
					
		</div>";
	DB::insert(DB::raw("INSERT INTO mail_logs(Email_To,Subject,Contents,Status,Inserted_Date,Send_Date) VALUES('$getupper->Email','$subjek','$badan','UNSENT',NOW(),NOW())"));


	echo "success";

	?>