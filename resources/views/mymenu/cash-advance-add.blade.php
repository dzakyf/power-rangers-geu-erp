<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
//$cekakun = DB::table('cash_handler')->where('NIK_handler', $Saya)->first();
$nik =   Auth::user()->Emp_ID;
// if ($cekakun->Account_No == '') {

// 	echo "You Don't have access to this menu!";
// 	exit;
// }


$result = DB::table('pum_karyawan')->where("Cust_ID", $nik)->first();
$getadvance = DB::table('cash_advance')->where("User_Reference", $nik)->whereNotIn('Status', ["CLOSED", "CANCELED", "REJECT"])->get();
$getbadvance = DB::table('bank_advance')->where("User_Reference", $nik)->whereNotIn('Status', ["CLOSED", "CANCELED", "REJECT"])->get();
$batas = $result->Batasan;


if (!$result) {
	echo "No Privilege";
	exit;
}
if (((count($getadvance) + count($getbadvance)) > $batas) && $batas > 0) {
	echo "<Br><br>
	You cannot create Advance due to many Advances not closed yet
	";
	exit;
}




?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">UANG MUKA BARU</div>
<div id="AdvanceForm">
	<div class="FormInput_Form_Content">
		<div class="FormInput_left">

			Tanggal Permintaan

		</div>

		<div class="FormInput_right">

			<input type="text" id="Advance_Date" readonly="readonly" size="15" disabled="disabled" value="<?= date('Y-m-d') ?>" disabled="disabled" />

		</div>

		<div class="FormInput_left">

			Peminjam/Penanggung Jawab

		</div>
		<div class="FormInput_right">

			<input type="text" size="10" id="User_Responsibility" value="<?php echo $result->Cust_ID ?>" disabled="disabled" /> ::
			<input type="text" readonly="readonly" size="30" id="User_Responsibility_Name" value="<?php echo $result->Name ?>" disabled="disabled" />::
			<input type="text" readonly="readonly" size="30" id="User_Reference" disabled="disabled" value="<?php echo $result->Inv_No ?>" /> <input type="hidden" readonly="readonly" size="20" id="User_Responsibility_ID" value="<?php echo $result->Inv_No ?>" />:: <input type="hidden" readonly="readonly" size="20" id="Acc_No" value="<?php echo $result->PIC ?>" />

		</div>
		<div class="FormInput_left">

			Departemen

		</div>
		<div class="FormInput_right">

			<input type="text" id="Department" size="40" readonly="readonly" value="<?php echo $result->Department ?>"/>

		</div>
		<div class="FormInput_left">

			Nilai

		</div>
		<div class="FormInput_right">

			<input type="text" id="Advance_Val" size="20" value="0" />

		</div>


		<div class="FormInput_left">

			Referensi

		</div>
		<div class="FormInput_right">

			<input type="text" id="Reference" size="40" />

		</div>
		<div class="FormInput_left" style="height:80px;">

			Note

		</div>
		<div class="FormInput_right" style="height:80px;">
			<textarea id="Description" cols="40" rows="3"></textarea>

		</div>
		<div class="FormInput_right" style="height:80px;">

			<input type="button" value="Submit" id="SubmitAdvance" onclick="SubmitAdvance()" class="btn btn-success mt-2 mr-2" />

		</div>

	</div>
</div>
<script src="{{ asset('jquery.coolautosuggest.js') }}"></script>
<script src="{{ asset('jquery.coolfieldset.js') }}"></script>

<script>
	$("#Advance_Val").on("keyup", function(event) {

		// 1.
		var selection = window.getSelection().toString();
		if (selection !== '') {
			return;
		}

		// 2.
		if ($.inArray(event.keyCode, [38, 40, 37, 39]) !== -1) {
			return;
		}
		var $this = $(this);
		var input = $this.val();

		// 2
		var input = input.replace(/[\D\s\._\-]+/g, "");

		// 3
		input = input ? parseInt(input, 10) : 0;

		// 4
		$this.val(function() {
			return (input === 0) ? "" : input.toLocaleString("en-US");
		});

	});
	var myCalendar;
	myCalendar = new dhtmlXCalendarObject(["Advance_Date"]);
	myCalendar.setDateFormat("%Y-%m-%d");

	$("#User_Responsibility").coolautosuggest({
		url: "finance-cash-advance-search-user?chars=",
		showThumbnail: false,
		showDescription: true,
		idField: $("#User_Responsibility"),
		width: 500,
		minChars: 1,
		onSelected: function(result) {
			if (result != null) {

				var Description = result.description;
				Expr = Description.split('<br>');
				$("#Acc_No").val(result.thumbnail);
				$("#User_Responsibility_ID").val(result.id);
				$("#User_Responsibility").val(result.thumbnail);
				$("#User_Reference").val(Expr[2]);
				$("#User_Responsibility_Name").val(result.data);
				$("#Department").val(Expr[1]);
				$("#Counterpart_Code").focus();

			}
		}
	});


	function getAllValues() {
		var inputValues = [];
		$('#AdvanceForm input,select,textarea').each(function() {
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

	function SubmitAdvance() {

		Total = document.getElementById("Advance_Val").value;
		if (Total <= 0) {
			text = "Nilai Tidak boleh 0";
			alert(text);
			document.getElementById('Advance_Val').focus();
			return false;
		}

		Note = document.getElementById("Description").value;
		if (Note.length < 10) {
			text = "Note setidaknya memiliki 10 huruf";
			alert(text);
			document.getElementById('Description').focus();
			return false;
		}
		dapat = getAllValues();

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': "{{ csrf_token() }}"
			},

			url: "my-cash-advance-add-submit?_token={{ csrf_token() }}",
			type: "POST",
			data: dapat,
			success: function(data, textStatus, jqXHR) {
				if (data == 'success') {
					alert('Cash Advance Task Created!');

					setTimeout(function() {

						$("#DivCashDetail").html('');

						refreshdata();
					}, 1000);

				} else {
					alert(data);
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				alert("error");

			}
		});
	}

	$("#Reference").coolautosuggest({
		url: "reference-search?chars=",
		showThumbnail: false,
		showDescription: true,
		idField: $("#Reference"),
		width: 500,
		minChars: 1,
		onSelected: function(result) {
			if (result != null) {

				var Description = result.description;
				Expr = Description.split('<br>');
				$("#Reference").val(result.id);

			}
		}
	});
</script>