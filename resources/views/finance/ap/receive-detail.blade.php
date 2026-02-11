<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
$NIK =  Auth::user()->Emp_ID;
$data = DB::table("account_payable")->where("ID", $_GET['ID'])->first();

?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DETAIL INVOICE RECEIVED FROM VENDOR</div>
<div id="ContentPages">
	<div class="FormInput_Form_Content">
		<div class="FormInput_left" style="font-weight:bold;">

			Task No

		</div>
		<div class="FormInput_right" style="font-weight:bold;">

			<input class='form-control' disabled="disabled" type="text" size="40" readonly="readonly" id="AP_No" value="<?php echo $data->Task_ID ?>" />

			<input type="hidden" size="40" readonly="readonly" id="ID" value="<?php echo $data->ID ?>" />
		</div>

		<div class="FormInput_left" style="font-weight:bold;">

			Invoice Type

		</div>
		<div class="FormInput_right" style="font-weight:bold;">

			<select id="tipeInv" class='form-control'>
				<option><?php echo $data->Inv_Type ?></option>
				<option value="SINGLE">SINGLE</option>
				<option value="PARTIAL">PARTIAL</option>
			</select>
			&nbsp;&nbsp;

		</div>

		<div class="FormInput_left">

			PO No

		</div>
		<div class="FormInput_right">

			<input class='form-control' disabled="disabled" type="text" id="PO_No" value="<?php echo $data->Reference ?>" size="50" />
		</div>
		<div class="FormInput_left" style="font-weight:bold;">

			Peruntukan

		</div>
		<div class="FormInput_right" style="font-weight:bold;">
			<input class='form-control-sm' type="text" disabled="disabled" id="Department" size="40" value="<?php echo $data->Department ?>" />


			&nbsp;&nbsp;

		</div>

		<div class="FormInput_left" style="font-weight:bold;height:150px">

			Vendor Bank Account

		</div>
		<div class="FormInput_right" style="background-color:#fff;font-weight:bold;height:150px">

			<input class='form-control-sm' type="text" disabled="disabled" id="Bank_Type" size="20" value="<?php echo $data->Bank_Type ?>" />
			<br>
			Bank Name <input disabled="disabled" class='form-control-sm' type="text" id="Bank_Name" value="<?php echo $data->Bank_Name ?>" size="30" /><Br>Acc No&nbsp;&nbsp;&nbsp;&nbsp; <input disabled="disabled" value="<?php echo $data->Bank_Acc ?>" class='form-control-sm' type="text" id="Bank_Acc" size="15" /><br>Acc Name <input class='form-control-sm' value="<?php echo $data->Bank_Acc_Name ?>" disabled="disabled" class='form-control' type="text" id="Bank_Acc_Name" size="30" />

		</div>

		<div class="FormInput_left">

			Subtotal

		</div>
		<div class="FormInput_right">

			<input class='form-control-sm' type="text" disabled="disabled" id="Subtotal" size="25" value="<?php echo $data->Subtotal ?>" />
		</div>
		<div class="FormInput_left">

			DP

		</div>
		<div class="FormInput_right">

			<input class='form-control-sm' type="text" disabled="disabled" id="DP" size="25" value="<?php echo $data->DP ?>" />
		</div>
		<div class="FormInput_left">

			Management_Fee

		</div>
		<div class="FormInput_right">

			<input class='form-control-sm' type="text" disabled="disabled" id="Management_Fee" size="25" value="<?php echo $data->Management_Fee ?>" />
		</div>
		<div class="FormInput_left">

			Delivery Costs

		</div>
		<div class="FormInput_right">

			<input class='form-control-sm' type="text" disabled="disabled" id="Delivery_Cost" size="25" value="<?php echo $data->Delivery_Costs ?>" />
		</div>
		<div class="FormInput_left">

			PPN

		</div>
		<div class="FormInput_right">

			<input class='form-control-sm' type="text" disabled="disabled" id="PPN" size="25" value="<?php echo $data->PPN ?>" />
		</div>
		<div class="FormInput_left">

			PPH

		</div>
		<div class="FormInput_right">
			<input class='form-control-sm' type="text" disabled="disabled" id="PPH_Type" size="25" value="<?php echo $data->PPH_Type ?>" /> ::
			<input class='form-control-sm' type="text" disabled="disabled" id="PPH" size="25" value="<?php echo $data->PPH ?>" />
		</div>
		<div class="FormInput_left">

				Materai

			</div>
			<div class="FormInput_right">

				<input class='form-control-sm' type="text" disabled="disabled" style="background-color:#fff" id="materai" size="25" value="<?php echo $data->materai ?>" />
			</div>

		<div class="FormInput_left">

			Total Amount

		</div>
		<div class="FormInput_right">

			<input class='form-control-sm' type="text" disabled="disabled" id="Invoice_Val" size="25" value="<?php echo $data->Total ?>" />
		</div>

		<div class="FormInput_left">

			Tanggal Jatuh Tempo

		</div>
		<div class="FormInput_right">

			<input class='form-control' disabled="disabled" type="text" id="JatuhTempo" size="25" value="<?php echo $data->Due_Date ?>" />

		</div>
		<div class="FormInput_left">

			Tanggal Terima Invoice

		</div>
		<div class="FormInput_right">

			<input class='form-control' disabled="disabled" type="text" id="Invoice_Received_Date" size="25" value="<?php echo $data->Date_Received ?>" />

		</div>


		<div class="FormInput_left">

			Reference/No Faktur/Invoice

		</div>
		<div class="FormInput_right">

			<input class='form-control' disabled="disabled" type="text" id="Reference" size="40" value="<?php echo $data->Inv_Vendor ?>" />

		</div>
		<div class="FormInput_left">

			Tanggal Invoice

		</div>
		<div class="FormInput_right">

			<input class='form-control' disabled="disabled" type="text" id="Inv_Vendor_Date" size="25" value="<?php echo $data->Inv_Vendor_Date ?>" />

		</div>
		<div class="FormInput_left" style="height:100px;">

			Invoice Subject

		</div>
		<div class="FormInput_right" style="height:100px;">

			<textarea id="Note" cols="80" disabled="disabled" rows="5"><?php echo $data->Description ?></textarea>


		</div>
		<div class="FormInput_left">

			Deskripsi Pembayaran

		</div>
		<div class="FormInput_right">

			<input class='form-control' disabled="disabled" type="text" id="Subject_Payment" size="50" value="<?php echo $data->Subject_Payment ?>" />

		</div>
		<div class="FormInput_left" style="height:100px;">

			Deskripsi Lampiran

		</div>
		<div class="FormInput_right" style="height:100px;">
			<textarea id="Note" cols="80" disabled="disabled" rows="5"><?php echo $data->Description ?></textarea>

		</div>

		<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">LAMPIRAN FILE</div>
		File : <input readonly="readonly" class='form-control' type="text" id="File_Document" size="50" value="<?php echo $data->File_Document ?>" onclick="window.open('./filevendor/' + this.value);" />




	</div>
</div>
<script src="{{ asset('jquery.coolautosuggest.js') }}"></script>
<script src="{{ asset('jquery.coolfieldset.js') }}"></script>

<script>
	$(function() {
		$('#DP,#Management_Fee,#Invoice_Val,#PPH,#PPN,#Delivery_Costs,#materai,#Subtotal').mask('#.##0', {
			reverse: true
		});
	});
	setTimeout(function() {
		$("input select,textarea").attr('disabled', 'disabled');
	}, 3000);
</script>