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

			<input class='form-control' style="background-color:#fff" type="text" size="40" readonly="readonly" id="AP_No" value="<?php echo $data->Task_ID ?>" />

			<input type="hidden" size="40" readonly="readonly" disabled="disabled" id="ID" value="<?php echo $data->ID ?>" />
		</div>

		<div class="FormInput_left" style="font-weight:bold;">

			Invoice Type

		</div>
		<div class="FormInput_right" style="font-weight:bold;">

			<select id="tipeInv" class='form-control' disabled="disabled">
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

			<input class='form-control' disabled="disabled" style="background-color:#fff" type="text" id="PO_No" value="<?php echo $data->Reference ?>" size="50" />
		</div>
		<div class="FormInput_left" style="font-weight:bold;">

			Peruntukan

		</div>
		<div class="FormInput_right" style="font-weight:bold;">

			<select id="Peruntukan" disabled="disabled" class="form-control" style="background-color:#fff">
				<option><?php echo $data->Department ?></option>
				<option>OVERHEAD</option>
				<option>OPERATIONAL</option>
			</select>
			&nbsp;&nbsp;

		</div>

		<div class="FormInput_left" style="font-weight:bold;height:150px">

			Vendor Bank Account

		</div>
		<div class="FormInput_right" style="background-color:#fff;font-weight:bold;height:150px">

			<select disabled="disabled" id="Bank_Type" class='form-control-sm'>
				<option><?php echo $data->Bank_Type ?></option>
				<option>VIRTUAL ACCOUNT</option>
				<option>DIRECT TRANSFER</option>
			</select><br>
			Bank Name <input style="background-color:#fff" class='form-control-sm' type="text" id="Bank_Name" value="<?php echo $data->Bank_Name ?>" size="30" /><Br>Acc No&nbsp;&nbsp;&nbsp;&nbsp; <input style="background-color:#fff" value="<?php echo $data->Bank_Acc ?>" class='form-control-sm' type="text" id="Bank_Acc" size="15" /><br>Acc Name <input class='form-control-sm' value="<?php echo $data->Bank_Acc_Name ?>" style="background-color:#fff" class='form-control' type="text" id="Bank_Acc_Name" size="30" />

		</div>

		<div class="FormInput_left">

			Invoice Value

		</div>
		<div class="FormInput_right">

			<input disabled="disabled" class='form-control-sm' type="text" style="background-color:#fff" id="Invoice_Val" size="25" value="<?php echo $data->Total ?>" />
		</div>

		<div class="FormInput_left">

			Tanggal Jatuh Tempo

		</div>
		<div class="FormInput_right">

			<input disabled="disabled" class='form-control' style="background-color:#fff" type="text" id="JatuhTempo" size="25" value="<?php echo $data->Due_Date ?>" />

		</div>
		<div class="FormInput_left">

			Tanggal Terima Invoice

		</div>
		<div class="FormInput_right">

			<input disabled="disabled" class='form-control' style="background-color:#fff" type="text" id="Invoice_Received_Date" size="25" value="<?php echo $data->Date_Received ?>" />

		</div>


		<div class="FormInput_left">

			Reference/No Faktur/Invoice

		</div>
		<div class="FormInput_right">

			<input disabled="disabled" class='form-control' style="background-color:#fff" type="text" id="Reference" size="40" value="<?php echo $data->Inv_Vendor ?>" />

		</div>
		<div class="FormInput_left">

			Tanggal Invoice

		</div>
		<div class="FormInput_right">

			<input disabled="disabled" class='form-control' style="background-color:#fff" type="text" id="Inv_Vendor_Date" size="25" value="<?php echo $data->Inv_Vendor_Date ?>" />

		</div>
		<div class="FormInput_left">

			Deskripsi Pembayaran

		</div>
		<div class="FormInput_right">

			<input disabled="disabled" class='form-control' style="background-color:#fff" type="text" id="Subject_Payment" size="50" value="<?php echo $data->Subject_Payment ?>" />

		</div>
		<div class="FormInput_left" style="height:100px;">

			Deskripsi Lampiran

		</div>
		<div class="FormInput_right" style="height:100px;">
			<textarea disabled="disabled" id="Note" cols="80" style="background-color:#fff" rows="5"><?php echo $data->Description ?></textarea>

		</div>

		<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">UPLOAD FILE CONTRACT</div>
		File : <input readonly="readonly" type="text" id="File_Document" size="50" value="<?php echo $data->File_Document ?>" onclick="window.open('./filevendor/' + this.value);" />




	</div>
</div>