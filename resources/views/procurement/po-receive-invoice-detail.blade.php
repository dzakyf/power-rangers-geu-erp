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

			Vendor

		</div>
		<div class="FormInput_right">

			<input class='form-control-sm' type="text" style="background-color:#fff" id="Supplier_ID" size="15" value="<?php echo $data->Vendor_ID ?>" /> &nbsp;&nbsp;
			<input class='form-control-sm' type="text" style="background-color:#fff" id="Supplier_Name" size="40" value="<?php echo $data->Vendor_Name ?>" readonly="readonly" />

		</div>
		<div class="FormInput_left">

			PO No

		</div>
		<div class="FormInput_right">

			<input class='form-control' style="background-color:#fff" type="text" id="PO_No" value="<?php echo $data->Reference ?>" size="50" />
		</div>
		<div class="FormInput_left" style="font-weight:bold;">

			Peruntukan

		</div>
		<div class="FormInput_right" style="font-weight:bold;">

			<select id="Peruntukan" class="form-control" style="background-color:#fff">
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

			<select id="Bank_Type" class='form-control-sm'>
				<option><?php echo $data->Bank_Type ?></option>
				<option>VIRTUAL ACCOUNT</option>
				<option>DIRECT TRANSFER</option>
			</select><br>
			Bank Name <input style="background-color:#fff" class='form-control-sm' type="text" id="Bank_Name" value="<?php echo $data->Bank_Name ?>" size="30" /><Br>Acc No&nbsp;&nbsp;&nbsp;&nbsp; <input style="background-color:#fff" value="<?php echo $data->Bank_Acc ?>" class='form-control-sm' type="text" id="Bank_Acc" size="15" /><br>Acc Name <input class='form-control-sm' value="<?php echo $data->Bank_Acc_Name ?>" style="background-color:#fff" class='form-control' type="text" id="Bank_Acc_Name" size="30" />

		</div>

		<div class="FormInput_left">

			DP

		</div>
		<div class="FormInput_right">

			<input class='form-control-sm amount' type="text" style="background-color:#fff" id="DP" size="25" value="<?php echo $data->DP ?>" onblur="findTotal()" />
		</div>
		<div class="FormInput_left">

			Subtotal

		</div>
		<div class="FormInput_right">

			<input class='form-control-sm amount' type="text" style="background-color:#fff" id="Subtotal" size="25" value="<?php echo $data->Subtotal ?>" onblur="findTotal()" />
		</div>

		<div class="FormInput_left">

			Management_Fee

		</div>
		<div class="FormInput_right">

			<input class='form-control-sm amount' type="text" style="background-color:#fff" id="Management_Fee" size="25" value="<?php echo $data->Management_Fee ?>" onblur="findTotal()" />
		</div>
		<div class="FormInput_left">

			Delivery Costs

		</div>
		<div class="FormInput_right">

			<input class='form-control-sm amount' type="text" style="background-color:#fff" id="Delivery_Costs" size="25" value="<?php echo $data->Delivery_Costs ?>" onblur="findTotal()" />
		</div>
		<div class="FormInput_left">

			PPN

		</div>
		<div class="FormInput_right">
			<select id="PPN_Type" class='form-control-sm' onchange="hitungPPN(event)">
				<option value="0">No PPN</option>
				<option value="11">11 %</option>
				<option value="10">10 %</option>
				<option value="1.1">1.1 %</option>
				<option value="1">1 %</option>
			</select>
			<input class='form-control-sm amount' type="text" style="background-color:#fff" id="PPN" size="25" value="<?php echo $data->PPN ?>" onblur="findTotal()" />
		</div>
		<div class="FormInput_left">

			PPH

		</div>
		<div class="FormInput_right">
			<select id="PPH_Type" class='form-control-sm' onchange="hitungPPH(event)">
				<option value="0" onclick="$('#PPH').attr('disabled','disabled');">No PPH</option>
				<option value="PPH23_4" onclick="$('#PPH').attr('disabled',false);">PPH 23 (4%) Non NPWP</option>
				<option value="PPH23_2" onclick="$('#PPH').attr('disabled',false);">PPH 23 (2%)</option>
				<option value="PPH23_15" onclick="$('#PPH').attr('disabled',false);">PPH 23 (15%)</option>
				<option value="PPH42" onclick="$('#PPH').attr('disabled',false);">PPH Pasal 4(2) (2%)</option>
				<option value="PPH_21_5" onclick="$('#PPH').attr('disabled',false);">PPH 21 (5%)</option>
			</select> ::
			<input class='form-control-sm' type="text" style="background-color:#fff" id="PPH" size="25" value="<?php echo $data->PPH ?>" />
		</div>
		<div class="FormInput_left">

			Materai

		</div>
		<div class="FormInput_right">

			<input class='form-control-sm amount' type="text" style="background-color:#fff" id="materai" size="25" value="<?php echo $data->materai ?>" onblur="findTotal()" />
		</div>


		<div class="FormInput_left">

			Total Amount

		</div>
		<div class="FormInput_right">

			<input class='form-control-sm' type="text" style="background-color:#fff" id="Invoice_Val" size="25" value="<?php echo $data->Total ?>" />
		</div>

		<div class="FormInput_left">

			Tanggal Jatuh Tempo

		</div>
		<div class="FormInput_right">

			<input class='form-control' style="background-color:#fff" type="text" id="JatuhTempo" size="25" value="<?php echo $data->Due_Date ?>" />

		</div>
		<div class="FormInput_left">

			Tanggal Terima Invoice

		</div>
		<div class="FormInput_right">

			<input class='form-control' style="background-color:#fff" type="text" id="Invoice_Received_Date" size="25" value="<?php echo $data->Date_Received ?>" />

		</div>


		<div class="FormInput_left">

			Reference/No Faktur/Invoice

		</div>
		<div class="FormInput_right">

			<input class='form-control' style="background-color:#fff" type="text" id="Reference" size="40" value="<?php echo $data->Inv_Vendor ?>" />

		</div>
		<div class="FormInput_left">

			Tanggal Invoice

		</div>
		<div class="FormInput_right">

			<input class='form-control' style="background-color:#fff" type="text" id="Inv_Vendor_Date" size="25" value="<?php echo $data->Inv_Vendor_Date ?>" />

		</div>
		<div class="FormInput_left">

			Deskripsi Pembayaran

		</div>
		<div class="FormInput_right">

			<input class='form-control' style="background-color:#fff" type="text" id="Subject_Payment" size="50" value="<?php echo $data->Subject_Payment ?>" />

		</div>
		<div class="FormInput_left" style="height:100px;">

			Deskripsi Lampiran

		</div>
		<div class="FormInput_right" style="height:100px;">
			<textarea id="Note" cols="80" style="background-color:#fff" rows="5"><?php echo $data->Description ?></textarea>

		</div>

		<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">UPLOAD FILE CONTRACT</div>
		File : <input readonly="readonly" type="text" id="File_Document" size="50" value="<?php echo $data->File_Document ?>" onclick="window.open('./filevendor/' + this.value);" />
		<div id="File_Documents" class="with-header" style='height:200px;margin:0px;width:auto;'></div>


		<div id="divtom">
			<input class='form-control' type="button" id="ContractDataSubmitr" class="btn btn-success mt-2 mr-2" NAME="ContractDataSubmitr" value="Submit" />
		</div>


	</div>
</div>
<script src="{{ asset('jquery.coolautosuggest.js') }}"></script>
<script src="{{ asset('jquery.coolfieldset.js') }}"></script>

<script>
	$(function() {
		$('#DP,#Management_Fee,#Invoice_Val,#PPH,#PPN,#Delivery_Costs,#materai').mask('#,##0', {
			reverse: true
		});
	});
	var vault = new dhx.Vault("File_Documents", {
		uploader: {
			target: "upload-receiveinvoice.php"
		},
		filesLimit: 1

	});

	vault.events.on("uploadComplete", function(obj) {
		$("#File_Document").val(obj[0].name);
	});
	vault.events.on("uploadBegin", function(obj) {
		$("#File_Document").val("");

	});
	vault.events.on("beforeAdd", function(fileItem) {
		if (vault.data.getLength() >= 1) {
			alert('Only can upload 1 file per checkpoint.');
			return false;
		}
	});

	vault.events.on("beforeAdd", function(item) {
		var extension = item.file.name.split(".").pop();

		var predicate = extension === "pdf";
		if (!predicate) {
			dhtmlx.alert("Hanya Boleh mengupload tipe file PDF");
		}
		return predicate;
	});

	myCalendar = new dhtmlXCalendarObject(["JatuhTempo", "Invoice_Received_Date", "Inv_Vendor_Date"]);
	myCalendar.setDateFormat("%Y-%m-%d");



	function getAllValues() {
		var inputValues = [];
		$('#ContentPages input,select,textarea').each(function() {
			var type = $(this).attr("type");
			if ((type == "checkbox" || type == "radio") && this.checked) {
				inputValues.push($(this).attr("id") + "=" + $(this).val());
			} else if (type != "button" || type != "submit") {
				inputValues.push($(this).attr("id") + "=" + $(this).val());
			} else if ($this.is("textarea")) {
				inputValues.push($(this).attr("id") + "=" + $(this).val());
			}
		})

		return inputValues.join('&');
	}



	$("#ContractDataSubmitr").click(function() {

		$.blockUI();

		dapat = getAllValues();
		$.ajax({
			headers: {
				'X-CSRF-TOKEN': "{{ csrf_token() }}"
			},

			url: "po-receive-invoice-detail-submit?_token={{ csrf_token() }}",
			type: "POST",
			data: dapat,
			success: function(data, textStatus, jqXHR) {
				if (data == 'success') {
					alert('Invoice Receive was Proposed!');

					setTimeout(function() {


						$.unblockUI();
						location.href = 'po-receive-invoice';
					}, 1000);

				} else {
					$.unblockUI();

					alert(data);
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				$.unblockUI();

				alert("error");

			}
		});




	});

	$("#PO_No").coolautosuggest({

		url: "search-po-manual?Procode=1&chars=",
		showThumbnail: false,
		showDescription: true,
		idField: $("#PO_No"),
		width: 700,
		minChars: 1,
		onSelected: function(result) {
			if (result != null) {
				$("#PO_No").val(result.id);
				$("#sisabayar").val(result.thumbnail);
				var nilai = parseFloat(result.data) - parseFloat(result.thumbnail);
				$("#Invoice_Val").val(nilai);


			}
		}
	});

	function findTotal() {

		var arr = document.getElementsByClassName('amount');
		var tot = 0;
		for (var i = 0; i < arr.length; i++) {
			if (parseFloat(arr[i].value))
				tot += parseFloat(arr[i].value.replaceAll(",", ""));
		}
		tot = new Intl.NumberFormat('en-US').format(tot);
		document.getElementById('Invoice_Val').value = tot;
	}

	function hitungPPN(e) {
		var subtotal = document.getElementById("Subtotal").value.replaceAll(",", "");
		var ppn = 0;
		if (e.target.value == 1) {
			ppn = parseFloat(subtotal) * 1 / 100;

			ppn = new Intl.NumberFormat('en-US').format(ppn.toFixed(0));
			document.getElementById("PPN").value = ppn;
			findTotal();
		} else if (e.target.value == 1.1) {

			ppn = parseFloat(subtotal) * 1.1 / 100;
			ppn = new Intl.NumberFormat('en-US').format(ppn.toFixed(0));
			document.getElementById("PPN").value = ppn;
			findTotal();
		} else if (e.target.value == 10) {

			ppn = parseFloat(subtotal) * 10 / 100;
			ppn = new Intl.NumberFormat('en-US').format(ppn.toFixed(0));
			document.getElementById("PPN").value = ppn;
			findTotal();
		} else if (e.target.value == 11) {

			ppn = parseFloat(subtotal) * 11 / 100;
			ppn = new Intl.NumberFormat('en-US').format(ppn.toFixed(0));
			document.getElementById("PPN").value = ppn;
			findTotal();
		} else {
			document.getElementById("PPN").value = ppn;
			findTotal()
		}
	}

	function hitungPPH(e) {
		var subtotal = document.getElementById("Subtotal").value.replaceAll(".", "");
		var pph = 0;

		switch (e.target.value) {
			case 'PPH23_4':
				pph = parseFloat(subtotal) * 4 / 100;

				pph = new Intl.NumberFormat('en-US').format(Math.floor(pph));
				document.getElementById("PPH").value = pph;
				break;
			case 'PPH23_2':
				pph = parseFloat(subtotal) * 2 / 100;

				pph = new Intl.NumberFormat('en-US').format(Math.floor(pph));
				document.getElementById("PPH").value = pph;
				break;
			case 'PPH23_15':
				pph = parseFloat(subtotal) * 15 / 100;

				pph = new Intl.NumberFormat('en-US').format(Math.floor(pph));
				document.getElementById("PPH").value = pph;
				break;
			case 'PPH42':
				pph = parseFloat(subtotal) * 2 / 100;

				pph = new Intl.NumberFormat('en-US').format(Math.floor(pph));
				document.getElementById("PPH").value = pph;
				break;
			case 'PPH_21_5':
				pph = parseFloat(subtotal) * 5 / 100;

				pph = new Intl.NumberFormat('en-US').format(Math.floor(pph));
				document.getElementById("PPH").value = pph;
				break;
			default:
				document.getElementById("PPH").value = pph;
		}
	}
</script>