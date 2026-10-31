<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
$NIK =  Auth::user()->Emp_ID;
$APNo = "APM/$LSP/" . substr(md5($Saya . date("c")), 0, 3) . "/" . date('ymdhis');

?>

@extends('layout.mainlayout-finance')

@section('content-wrapper')
@csrf

<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Finance</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="#">Account Payable</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Manual Invoice</span>
		</li>
	</ol>
</div>
<div class="row">
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">ADD INVOICE MANUAL</div>
	<div id="ContentPages">
		<div class="FormInput_Form_Content">
			<div class="FormInput_left" style="font-weight:bold;">

				Task No

			</div>
			<div class="FormInput_right" style="font-weight:bold;">

				<input class='form-control' style="background-color:#fff" type="text" size="40" readonly="readonly" id="AP_No" value="<?php echo $APNo ?>" />
				<input class='form-control' style="background-color:#fff" type="hidden" size="40" readonly="readonly" id="File_Document" />

			</div>
			<div class="FormInput_left">

				User Request

			</div>
			<div class="FormInput_right">

				<input class='form-control-sm' type="text" style="background-color:#fff" id="User_Request_ID" size="15" value="" /> &nbsp;&nbsp;
				<input class='form-control-sm' type="text" style="background-color:#fff" id="User_Request_Name" size="40" disabled="disabled" readonly="readonly" />

			</div>
			<div class="FormInput_left">

				Vendor

			</div>
			<div class="FormInput_right">

				<input class='form-control-sm' type="text" style="background-color:#fff" id="Supplier_ID" size="15" value="" /> &nbsp;&nbsp;
				<input class='form-control-sm' type="text" style="background-color:#fff" id="Supplier_Name" size="40" readonly="readonly" />

			</div>
			<div class="FormInput_left">

				PIC Vendor

			</div>
			<div class="FormInput_right">

				<input class='form-control' style="background-color:#fff" type="text" id="PIC" size="50" />
			</div>
			<div class="FormInput_left" style="font-weight:bold;">

				Invoice Type

			</div>
			<div class="FormInput_right" style="font-weight:bold;">

				<select id="tipeInv" class='form-control'>
					<option value="SINGLE">SINGLE</option>
					<option value="PARTIAL">PARTIAL</option>
				</select>
				&nbsp;&nbsp;

			</div>

			<div class="FormInput_left">

				FPPB Manual

			</div>
			<div class="FormInput_right">

				<input class='form-control' style="background-color:#fff" type="text" id="PO_No" size="50" />
			</div>
			<div class="FormInput_left" style="font-weight:bold;">

				Department

			</div>
			<div class="FormInput_right" style="font-weight:bold;">

				<input class='form-control' style="background-color:#fff" type="text" id="Peruntukan" size="40" readonly="readonly" />

				&nbsp;&nbsp;

			</div>

			<div class="FormInput_left" style="font-weight:bold;height:150px">

				Vendor Bank Account

			</div>
			<div class="FormInput_right" style="background-color:#fff;font-weight:bold;height:150px">

				<select id="Bank_Type" class='form-control-sm'>
					<option>VIRTUAL ACCOUNT</option>
					<option>DIRECT TRANSFER</option>
				</select><br>
				Bank Name <input style="background-color:#fff" class='form-control-sm' type="text" id="Bank_Name" size="30" /><Br>Acc No&nbsp;&nbsp;&nbsp;&nbsp; <input style="background-color:#fff" class='form-control-sm' type="text" id="Bank_Acc" size="15" /><br>Acc Name <input class='form-control-sm' style="background-color:#fff" class='form-control' type="text" id="Bank_Acc_Name" size="30" />

			</div>

			<div class="FormInput_left">

				DP

			</div>
			<div class="FormInput_right">

				<input class='form-control-sm amount' type="text" style="background-color:#fff" id="DP" size="25" value="0" onblur="findTotal()" />
			</div>
			<div class="FormInput_left">

				Subtotal Invoice

			</div>
			<div class="FormInput_right">

				<input class='form-control-sm amount' type="text" style="background-color:#fff" id="Subtotal" size="25" value="0" onblur="findTotal()" />
			</div>
			<div class="FormInput_left">

				Management_Fee

			</div>
			<div class="FormInput_right">

				<input class='form-control-sm amount' type="text" style="background-color:#fff" id="Management_Fee" size="25" value="0" onblur="findTotal()" />
			</div>
			<div class="FormInput_left">

				Delivery Costs

			</div>
			<div class="FormInput_right">

				<input class='form-control-sm amount' type="text" style="background-color:#fff" id="Delivery_Costs" size="25" value="0" onblur="findTotal()" />
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
				<input class='form-control-sm amount' type="text" style="background-color:#fff" id="PPN" size="25" value="0" onblur="findTotal()" />
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
				<input class='form-control-sm' type="text" style="background-color:#fff" id="PPH" size="25" value="0" />
			</div>
			<div class="FormInput_left">

				Materai

			</div>
			<div class="FormInput_right">

				<input class='form-control-sm amount' type="text" style="background-color:#fff" id="materai" size="25" value="0" onblur="findTotal()" />
			</div>

			<div class="FormInput_left">

				Total Amount

			</div>
			<div class="FormInput_right">

				<input class='form-control-sm' type="text" style="background-color:#fff" id="Invoice_Val" size="25" value="0" />
			</div>

			<div class="FormInput_left">

				Tanggal Jatuh Tempo

			</div>
			<div class="FormInput_right">

				<input class='form-control' style="background-color:#fff" type="text" id="JatuhTempo" size="25" value="<?php echo date("Y-m-d"); ?>" />

			</div>
			<div class="FormInput_left">

				Tanggal Terima Invoice

			</div>
			<div class="FormInput_right">

				<input class='form-control' style="background-color:#fff" type="text" id="Invoice_Received_Date" size="25" value="<?php echo date("Y-m-d"); ?>" />

			</div>


			<div class="FormInput_left" id="LabelReference">

				Reference/No Faktur/Invoice

			</div>
			<div class="FormInput_right">

				<input class='form-control' required style="background-color:#fff" type="text" id="Reference" size="40" />

			</div>
			<div class="FormInput_left">

				Invoice Subject

			</div>
			<div class="FormInput_right">

				<input class='form-control' style="background-color:#fff" type="text" id="Description" size="60" />

			</div>
			<div class="FormInput_left">

				Tanggal Invoice

			</div>
			<div class="FormInput_right">

				<input class='form-control' style="background-color:#fff" type="text" id="Inv_Vendor_Date" size="25" value="<?php echo date("Y-m-d"); ?>" />

			</div>
			<div class="FormInput_left">

				Deskripsi Pembayaran

			</div>
			<div class="FormInput_right">

				<input class='form-control' style="background-color:#fff" type="text" id="Subject_Payment" size="50" />

			</div>
			<div class="FormInput_left" style="height:100px;">

				Deskripsi Lampiran

			</div>
			<div class="FormInput_right" style="height:100px;">
				<textarea id="Note" cols="80" style="background-color:#fff" rows="5"></textarea>

			</div>

			<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">UPLOAD FILE CONTRACT</div>

			<div id="File_Documents" class="with-header" style='height:200px;margin:0px;width:auto;'></div>


			<div id="divtom">
				<input class='form-control' type="button" id="ContractDataSubmitr" class="btn btn-success mt-2 mr-2" NAME="ContractDataSubmitr" value="Submit" />
			</div>


		</div>
	</div>
</div>
<script src="{{ asset('jquery.coolautosuggest.js') }}"></script>
<script src="{{ asset('jquery.coolfieldset.js') }}"></script>

<script>
	$(function() {
		$('#DP,#Management_Fee,#Invoice_Val,#PPH,#PPN,#Delivery_Costs,#Subtotal,#materai').mask('#,##0.00', {
			reverse: true
		});
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

		reference = document.getElementById("Reference").value;
		if (reference == "") {
			text = "Nomor Invoice Mohon Di Input (Reference/No Faktur/Invoice Kosong)";
			alert(text);
			document.getElementById('Reference').focus();
			return false;
		}
		$.blockUI();

		dapat = getAllValues();
		$.ajax({
			headers: {
				'X-CSRF-TOKEN': "{{ csrf_token() }}"
			},

			url: "finance-ap-manual-submit?_token={{ csrf_token() }}",
			type: "POST",
			data: dapat,
			success: function(data, textStatus, jqXHR) {
				if (data == 'success') {
					alert('Invoice Manual was Created!');

					setTimeout(function() {


						$.unblockUI();
						location.href = 'finance-ap-manual';
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

	$("#Supplier_ID").coolautosuggest({

		url: "search-supplier?Procode=1&chars=",
		showThumbnail: false,
		showDescription: true,
		idField: $("#Supplier_ID"),
		width: 700,
		minChars: 1,
		onSelected: function(result) {
			if (result != null) {
				$("#Supplier_ID").val(result.id);
				$("#Supplier_Name").val(result.data);


			}
		}
	});


	$("#User_Request_ID").coolautosuggest({
		url: "finance-ap-search-employee?chars=",
		showThumbnail: false,
		showDescription: true,
		idField: $("#User_Request_ID"),
		width: 500,
		minChars: 1,
		onSelected: function(result) {
			if (result != null) {
				$("#User_Request_ID").val(result.id);
				$("#User_Request_Name").val(result.data);
				$("#Peruntukan").val(result.thumbnail);

				var Description = result.description;
				Expr = Description.split('<br>');

			}
		}
	});

	function hitungPPN(e) {
		var subtotal = document.getElementById("Subtotal").value.replaceAll(",", "");
		var ppn = 0;
		if (e.target.value == 1) {
			ppn = parseFloat(subtotal) * 1 / 100;

			ppn = new Intl.NumberFormat('en-US').format(ppn.toFixed(3));
			document.getElementById("PPN").value = ppn;
			findTotal();
		} else if (e.target.value == 1.1) {

			ppn = parseFloat(subtotal) * 1.1 / 100;
			ppn = new Intl.NumberFormat('en-US').format(ppn.toFixed(3));
			document.getElementById("PPN").value = ppn;
			findTotal();
		} else if (e.target.value == 10) {

			ppn = parseFloat(subtotal) * 10 / 100;
			ppn = new Intl.NumberFormat('en-US').format(ppn.toFixed(3));
			document.getElementById("PPN").value = ppn;
			findTotal();
		} else if (e.target.value == 11) {

			ppn = parseFloat(subtotal) * 11 / 100;
			ppn = new Intl.NumberFormat('en-US').format(ppn.toFixed(3));
			document.getElementById("PPN").value = ppn;
			findTotal();
		} else {
			document.getElementById("PPN").value = ppn;
			findTotal()
		}
	}

	function hitungPPH(e) {
		var subtotal = document.getElementById("Subtotal").value.replaceAll(",", "");
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

@endsection