<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;


if ($_GET['Acc'] == 'ARVENDOR') {
	$cekM = DB::table("account_receive")->where("Location", $LSP)->where("Cust_ID", $_GET['Acc'])->where("Status", 'RECEIVED')->orWhere("Status", 'PARTIAL')->orderBy('Cust_ID', 'ASC')->orderBy('Inv_Date', 'ASC')->get();
	$opt = "";

	echo "<select id='References'>";

	$customer = DB::table("customerz")->where("Customer_No", $Fetch->Cust_ID)->first();

	foreach ($cekM as $fetch) {

		$opt .= "<option onclick=\"ChangeTab(this.value,22);ChangeTab2(this.value,8);\" value='$fetch->ID'>$fetch->Cust_ID - $customer->Company_Name :: $fetch->Inv_No :: Total Inv : Rp.$fetch->AR_Val :: Remain : Rp. $fetch->AR_Remain </option>";
	}
	$opt .= "</select>";
	echo $opt;
} elseif ($_GET['Acc'] == 'CASH_ADVANCE') {
	$cekM = DB::table("cash_advance")->where("Location", $LSP)->where("Status", "!=", 'CANCELED')->where("Status", "!=", 'CLOSED')->orderBy('Advance_Date', 'DESC')->get();
	$opt = "";

	echo "<select id='References'>";


	foreach ($cekM as $fetch) {

		$opt .= "<option onclick=\"ChangeTab(this.value,22);ChangeTab2(this.value,8);\" value='$fetch->Task_ID'>$fetch->User_Responsibility_Name - $fetch->Task_ID :: $fetch->Description :: Total Advance : Rp.$fetch->Cash_Val :: Remain : Rp. $fetch->Val_Remain :: Status : $fetch->Status </option>";
	}
	$opt .= "</select>";
	echo $opt;
} elseif ($_GET['Acc'] == 'BANK_ADVANCE') {
	$cekM = DB::table("bank_advance")->where("Location", $LSP)->where("Status", "!=", 'CANCELED')->where("Status", "!=", 'CLOSED')->orderBy('Advance_Date', 'DESC')->get();
	$opt = "";

	echo "<select id='References'>";


	foreach ($cekM as $fetch) {

		$opt .= "<option onclick=\"ChangeTab(this.value,22);ChangeTab2(this.value,8);\" value='$fetch->Task_ID'>$fetch->User_Responsibility_Name - $fetch->Task_ID :: $fetch->Description :: Total Advance : Rp.$fetch->Bank_Val :: Remain : Rp. $fetch->Val_Remain :: Status : $fetch->Status </option>";
	}
	$opt .= "</select>";
	echo $opt;
} elseif ($_GET['Acc'] == 'TITIPAN') {

	echo "<select id='References'><option value='213.4'>Titipan Lain-Lain - 213.4</option></select>";
} elseif ($_GET['Acc'] == 'PPN') {

	echo "<input type='text' size='20' id='References' value=''>";
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