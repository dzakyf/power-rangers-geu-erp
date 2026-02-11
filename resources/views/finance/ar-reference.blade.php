<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;


if ($_GET['Acc'] == '211.1') {
	$cekM = DB::table("account_payable")->where("Location", $LSP)->where("Status", 'RECEIVED')->orWhere("Status", 'PARTIAL')->orderBy('Vendor_ID', 'ASC')->orderBy('Inv_Vendor_Date', 'ASC')->get();
	$opt = "";
	echo "<select id='References'>";


	foreach ($cekM as $fetch) {
		$vendor = DB::table("supplier")->where("Supplier_ID", $fetch->Vendor_ID)->first();
		$opt .= "<option value='$fetch->Inv_Vendor'>$fetch->Vendor_ID - $vendor->Company_Name $fetch->Subject_Payment :: $fetch->Inv_Vendor :: Total Inv : Rp.$fetch->Total :: Remain : Rp. $fetch->Remain_Val </option>";
		// $opt .="<option onclick=\"ChangeTab(this.value,22);ChangeTab2(this.value,8);\" value='$fetch->Inv_Vendor'>$fetch->Vendor_ID - $vendor->Company_Name $fetch->Subject_Payment :: $fetch->Inv_Vendor :: Total Inv : Rp.$fetch->Total :: Remain : Rp. $fetch->Remain_Val </option>";	
	}
	$opt .= "</select>";
	echo $opt;
} elseif ($_GET['Acc'] == '213.4') {

	echo "<select id='References'><option value='213.4'>Titipan Lain-Lain - 213.4</option></select>";
} else {

	$cekM = DB::table("bank_acc")->where("Bank_Acc", $_GET['Acc'])->get();
	$opt = "";
	echo "<select id='References'><option value=''>Select</option>";


	foreach ($cekM as $fetch) {
		$opt .= "<option onclick=\"ChangeTab(this.value,22);ChangeTab2(this.value,8);\" value='$fetch->Acc_No'>$fetch->Acc_No - $fetch->Bank_Name $fetch->Bank_Branch</option>";
	}
	$opt .= "</select>";
	echo $opt;
}

?>
<script>
	$(document).ready(function() {
		$('#References').select2();
	});
</script>